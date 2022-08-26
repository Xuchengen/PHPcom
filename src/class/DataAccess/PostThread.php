<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : PostThread.php  2012-12-8
 */
!defined('IN_PHPCOM') && exit('Access denied');

class DataAccess_PostThread
{
	protected $chanid = 0;
	protected $uid = 0;
	protected $channel = array();
	protected $tableindex = 0;
	protected $founders = false;
	private $module = 'article';

	public function __construct($chanid = 0) {
		$this->uid = intval(phpcom::$G['uid']);
		$this->chanid = $chanid;
		$this->founders = phpcom::$G['founders'];
		if($chanid > 0){
			phpcom::$G['channelid'] = $chanid;
			if(isset(phpcom::$G['channel'][$chanid])){
				$this->channel = phpcom::$G['channel'][$chanid];
				$this->tableindex = intval($this->channel['deftable']);
				$this->module = $this->channel['modules'];
			}
		}
	}

	public function insert(&$thread, $data, $fields = array(), $subjects = array(), $messages = array())
	{
		$data['chanid'] = $this->chanid;
		if(!empty($data['catid'])){
			$data['catid'] = intval($data['catid']);
			$data['rootid'] = $this->getRootID($data['catid']);
			if(empty($data['rootid'])) return 0;
			$subjects['catid'] = $data['catid'];
			$subjects['rootid'] = $data['rootid'];
		}else{
			return 0;
		}
		$data['lastdate'] = TIMESTAMP;
		$data['dateline'] = TIMESTAMP;
		$data['tableindex'] = $this->tableindex;
		$subjects['chanid'] = $data['chanid'];
		$subjects['dateline'] = $data['dateline'];
		if(!empty($data['url'])){
			$data['url'] = checkurlhttp($data['url']);
		}
		if(empty($data['uid'])){
			$data['uid'] = $this->uid;
		}
		$this->fieldTryParse(array('hits', 'bancomment', 'digest', 'status', 'locked', 'istop'), $data, 'int', 0);
		$this->fieldTryParse(array('voteup', 'votedown', 'credits'), $fields, 'int', 0);

		$thread['chanid'] = $this->chanid;
		$thread['catid'] = $data['catid'];
		$thread['tableindex'] = $this->tableindex;
		$thread['dateline'] = $data['dateline'];
		$thread['status'] = $data['status'];
		if(isset($data['tid'])) unset($data['tid']);
		DB::beginTransaction();
		if($tid = DB::insert('threads', $data, TRUE)){
			$fields['tid'] = $tid;
			$fields['isupdate'] = 1;
			DB::insert('thread_field', $fields);

			if(isset($subjects['summary'])){
				$content = isset($messages['content']) ? trim($messages['content']) : '';
				$subjects['summary'] = $this->formatSummary($subjects['summary'], $messages['content'], trim($data['title']));
			}
			if(isset($messages['content'])){
				$messages['content'] = $this->formatContent($messages['content']);
			}
			if(isset($this->channel['modules'])){
				$subjects['tid'] = $tid;
				if(empty($subjects['uid'])){
					$subjects['uid'] = $this->uid;
				}
				if(empty($subjects['editor'])){
					$subjects['editor'] = phpcom::$G['username'];
				}
				$subjects['tableindex'] = $this->tableindex;
				$subjects['demourl'] = empty($subjects['demourl']) ? '' : trim(strip_tags($subjects['demourl']));
				if(isset($subjects['checksum'])){
					$subjects['checksum'] = trim(strip_tags($subjects['checksum']));
				}
				$modules = $this->channel['modules'];
				if($modules == 'video' && isset($subjects['starring']) && isset($subjects['director'])){
					$starring = $this->formatSeparator($subjects['starring'], '/', 252);
					$director = $this->formatSeparator($subjects['director'] . '/', '/', 49);
					$subjects['starring'] = $starring;
					$subjects['director'] = $director;
					$this->insertPersons("$starring/$director", $tid, $this->chanid);
				}
				$tablename = $modules . '_thread';
				if(DB::insert($tablename, $subjects)){
					$messages['tid'] = $tid;
					if(isset($messages['tidlist']) && !empty($messages['tidlist'])){
						$messages['tidlist'] = trim(implodeids($messages['tidlist'], ','), "' ,");
					}
					if(isset($messages['tags']) && !empty($messages['tags'])){
						$messages['tags'] = $this->insertTags($messages['tags'], $tid, $this->chanid);
					}
					if($modules == 'special' && !isset($messages['message'])){
						$messages['message'] = '';
					}
					$tablename = $modules . '_content';
					DB::insert($tablename, $messages, FALSE, FALSE, FALSE, $this->tableindex);
					$threadlog = new MemberThreadLog($this->chanid);
					$threadlog->insert($tid);
				}else{
					DB::rollBack();
					DB::delete('threads', "tid='$tid'");
					DB::delete('thread_field', "tid='$tid'");
				}
			}else{
				DB::rollBack();
				DB::delete('threads', "tid='$tid'");
				DB::delete('thread_field', "tid='$tid'");
				return 0;
			}
			if(!empty($data['status'])){
				DB::exec("UPDATE " . DB::table('category') . " SET counts=counts+1 WHERE catid='{$data['catid']}'");
				if($data['catid'] != $data['rootid']){
					DB::exec("UPDATE " . DB::table('category') . " SET counts=counts+1 WHERE catid='{$data['rootid']}'");
				}
			}
			DB::commit();
		}else{
			DB::rollBack();
		}
		$thread['tid'] = $tid;
		return $tid;
	}
	
	public function update($tid, $data, $fields = array(), $subjects = array(), $messages = array(), $backgroundid = 0, $bannerid = 0)
	{
		if(isset($data['tableindex'])) unset($data['tableindex']);
		if(isset($data['chanelid'])) unset($data['chanelid']);
		if(isset($data['uid'])) unset($data['uid']);
		if(isset($subjects['uid'])) unset($subjects['uid']);

		if(!empty($data['url'])){
			$data['url'] = checkurlhttp($data['url']);
		}

		$this->fieldTryParse(array('hits', 'bancomment', 'digest', 'status', 'locked', 'istop'), $data, 'int');
		$this->fieldTryParse(array('voteup', 'votedown', 'credits'), $fields, 'int');

		if(isset($data['tid'])) unset($data['tid']);
		if($threads = DB::fetch_first("SELECT tid, chanid, catid, rootid, uid, title, polled, attached, dateline, tableindex, status, locked FROM " . DB::table('threads') . " WHERE tid='$tid'")){
			if(isset($data['catid']) && $data['catid'] != $threads['catid']){
				$data['catid'] = intval($data['catid']);
				$data['rootid'] = $this->getRootID($data['catid']);
				$subjects['catid'] = $data['catid'];
				$subjects['rootid'] = $data['rootid'];
				if(empty($data['rootid'])) return null;
			}else{
				if(isset($data['catid'])) unset($data['catid']);
				if(isset($subjects['catid'])) unset($subjects['catid']);
			}
			if(empty($threads['uid'])){
				$data['uid'] = $this->uid;
			}else{
				if(!$this->founders && $threads['locked']){
					if($threads['locked'] == 2 || $threads['uid'] != $this->uid){
						return $threads;
					}
				}
				if(!$this->founders && $threads['uid'] != $this->uid){
					$data['locked'] = 0;
				}
				$threads['locked'] = 0;
				$this->uid = $threads['uid'];
			}
			$isupdate = 0;
			$subjects['demourl'] = empty($subjects['demourl']) ? '' : trim(strip_tags($subjects['demourl']));
			if(isset($subjects['checksum'])){
				$subjects['checksum'] = trim(strip_tags($subjects['checksum']));
			}
			if(!empty($fields['isupdate'])){
				$data['dateline'] = TIMESTAMP;
				$subjects['dateline'] = $data['dateline'];
				$subjects['editor'] = phpcom::$G['username'];
				$isupdate = 1;
			}
			DB::beginTransaction();
			$fields['isupdate'] = 1;
			DB::update('threads', $data, array('tid' => $tid));

			if (DB::result_first("SELECT tid FROM " . DB::table('thread_field') . " WHERE tid='$tid'")) {
				DB::update('thread_field', $fields, array('tid' => $tid));
			} else {
				$fields['tid'] = $tid;
				DB::insert('thread_field', $fields);
			}
			
			if(isset($subjects['summary'])){
				$content = isset($messages['content']) ? trim($messages['content']) : '';
				$subjects['summary'] = $this->formatSummary($subjects['summary'], $messages['content'], trim($data['title']));
			}
			if(isset($messages['content'])){
				$messages['content'] = $this->formatContent($messages['content']);
			}
			if(isset($this->channel['modules']) && $subjects){
				$modules = $this->channel['modules'];
				if($modules == 'video' && isset($subjects['starring']) && isset($subjects['director'])){
					$starring = $this->formatSeparator($subjects['starring'], '/', 252);
					$director = $this->formatSeparator($subjects['director'] . '/', '/', 49);
					$subjects['starring'] = $starring;
					$subjects['director'] = $director;
					$this->updatePersons("$starring/$director", $tid, $this->chanid);
				}
				$tablename = $modules . '_thread';
				$fields = 'tid';
				if($modules == 'video') $fields .= ', aid';
				$updatethreads = true;
				if($tmp = DB::fetch_first("SELECT $fields FROM " . DB::table($tablename) . " WHERE tid='$tid' LIMIT 1")){
					$threads['aid'] = empty($tmp['aid']) ? 0 : $tmp['aid'];
					DB::update($tablename, $subjects, array('tid' => $tid));
				}else{
					$updatethreads = false;
					$subjects['tid'] = $tid;
					$subjects['chanid'] = $threads['chanid'];
					$subjects['rootid'] = $threads['rootid'];
					$subjects['catid'] = $threads['catid'];
					$subjects['uid'] = $threads['uid'];
					if(empty($subjects['editor'])){
						$subjects['editor'] = phpcom::$G['username'];
					}
					if(empty($subjects['dateline'])){
						$subjects['dateline'] = $threads['dateline'];
					}
					DB::insert($tablename, $subjects);
					$threads['aid'] = 0;
				}
				if($messages){
					$tablename = $modules . '_content';
					if(isset($messages['tidlist']) && !empty($messages['tidlist'])){
						$messages['tidlist'] = trim(implodeids($messages['tidlist'], ','), "' ,");
					}
					if(isset($messages['tags'])){
						if($tags = $this->updateTags($messages['tags'], $tid, $this->chanid)){
							$messages['tags'] = $tags;
						} else {
							$messages['tags'] = '';
						}
					}
					if($updatethreads){
						$this->unlinkBackgroundImage($tid, $backgroundid, $bannerid, $threads['tableindex']);
						DB::update($tablename, $messages, array('tid' => $tid), $threads['tableindex']);
					}else{
						$messages['tid'] = $tid;
						if($modules == 'special' && !isset($messages['message'])){
							$messages['message'] = '';
						}
						DB::insert($tablename, $messages, FALSE, FALSE, FALSE, $threads['tableindex']);
					}
				}
				if($modules == 'soft' || $modules == 'video'){
					if(strcasecmp($threads['title'], $data['title']) !== 0){
						$threadlog = new MemberThreadLog($this->chanid);
						$threadlog->update($tid, $threads['uid'], $threads['dateline']);
					}
				}
			}
			if(empty($data['status']) && $threads['status']){
				DB::exec("UPDATE " . DB::table('category') . " SET counts=counts-1 WHERE catid='{$threads['catid']}'");
				if($threads['catid'] != $threads['rootid']){
					DB::exec("UPDATE " . DB::table('category') . " SET counts=counts-1 WHERE catid='{$threads['rootid']}'");
				}
			}elseif(!empty($data['status']) && empty($threads['status']) && isset($data['catid'])){
				DB::exec("UPDATE " . DB::table('category') . " SET counts=counts+1 WHERE catid='{$data['catid']}'");
				if($data['catid'] != $data['rootid']){
					DB::exec("UPDATE " . DB::table('category') . " SET counts=counts+1 WHERE catid='{$data['rootid']}'");
				}
			}elseif(!empty($data['status']) && empty($threads['status']) && !isset($data['catid'])){
				DB::exec("UPDATE " . DB::table('category') . " SET counts=counts+1 WHERE catid='{$threads['catid']}'");
				if($threads['catid'] != $threads['rootid']){
					DB::exec("UPDATE " . DB::table('category') . " SET counts=counts+1 WHERE catid='{$threads['rootid']}'");
				}
			}
			DB::commit();
		}
		return $threads;
	}
	
	public function unlinkBackgroundImage($tid, $backgroundid, $bannerid = 0, $tableindex = 0)
	{
		if (!empty($backgroundid) || !empty($bannerid)) {
			if($cont = DB::fetch_first("SELECT banner, background FROM " . DB::table('special_content', $tableindex) . " WHERE tid='$tid'")){
				$attachdir = rtrim(phpcom::$setting['attachdir'], '/\ ');
				if(!empty($backgroundid) && !empty($cont['background'])){
					if(!parse_url($cont['background'], PHP_URL_SCHEME)){
						$filename = $attachdir . '/image/' . $cont['background'];
						if(is_file($filename)){
							@unlink($filename);
						}
					}
				}
				if(!empty($bannerid) && !empty($cont['banner'])){
					if(!parse_url($cont['banner'], PHP_URL_SCHEME)){
						$filename = $attachdir . '/image/' . $cont['banner'];
						if(is_file($filename)){
							@unlink($filename);
						}
					}
				}
			}
		}
	}
	public function formatStringToTime($time)
	{
		if($time = strtotime($time)){
			return $time;
		}
		return 0;
	}
	
	public function formatContent($content)
	{
		if($content = trim($content)){
			$content = str_replace(array('[attachimg]', '[/attachimg]', "\r\n"), array('[attach]', '[/attach]', "\n"), $content);
			$content = str_replace(array('&lt;ignore_js_op&gt;', '&lt;/ignore_js_op&gt;'), '', $content);
			$content = str_replace(array('<!-- pagebreak -->', '[NextPage]', '[page_break]'), '[pagebreak]', $content);
			$content = preg_replace("/<(p|div)(\s+[^>]*)?>\s*\[pagebreak\]\s*<\/(p|div)>/i", '[pagebreak]', $content);
			$content = preg_replace("/\[pagebreak\]\s*<\/(p|div)>/i", "</\\1>\n[pagebreak]", $content);
			$content = preg_replace("/<(p|div)(\s+[^>]*)?>\s*\[pagebreak\]/i", "[pagebreak]<\\1\\2>", $content);
			if (strpos($content, '[/download]</p>') !== FALSE) {
				$content = preg_replace("#<p>(\[download(.+?)\[\/download\])<\/p>#is", '\\1', $content);
			}
			if (strpos($content, '[/thread]</p>') !== FALSE) {
				$content = preg_replace("#<p>(\[thread(.+?)\[\/thread\])<\/p>#is", '\\1', $content);
			}
			$content = preg_replace("/^<p>\s*<\/p>|<p>\s*<\/p>$/i","", $content);
			$content = preg_replace("/^<div>\s*<\/div>|<div>\s*<\/div>$/i","", $content);
		}
		return trim($content);
	}

	public function formatSummary($summary, $content, $defvalue = "")
	{
		if(empty($summary) && $content){
			$length = intval(phpcom::$setting['summarys']);
			$length = $length && $length < 255 ? $length : 100;
			$content = htmlstrip(bbcode::bbcode2html($content));
			$content = str_replace('[pagebreak]', '', $content);
			$content = str_replace('&nbsp;', '', $content);
			$content = str_replace(array('&lt;ignore_js_op&gt;', '&lt;/ignore_js_op&gt;'), '', $content);
			$content = preg_replace("/&(quot|#34);/i", '"', $content);
			$content = preg_replace("/&(amp|#38);/i", "&", $content);
			$content = preg_replace("/\[attachimg\](\d*)\[\/attachimg\]/i", "", $content);
			$content = preg_replace("/\[attach\](\d*)\[\/attach\]/i", "", $content);
			$content = preg_replace("/\[download(=([^\]]+))?\]([0-9,\s]+?)\[\/download\]/i", "", $content);
			$content = preg_replace("/\[thread(=([^\]]+))?\]([0-9,\s]+?)\[\/thread\]/i", "", $content);
			if($content = trim($content)){
				$summary = mb_substr($content, 0, $length, CHARSET);
			}
		}else{
			$summary = trim(strip_tags($summary));
			$summary = str_replace('&nbsp;', '', $summary);
			$summary = preg_replace("/\[attachimg\](\d*)\[\/attachimg\]/i", "", $summary);
			$summary = preg_replace("/\[attach\](\d*)\[\/attach\]/i", "", $summary);
			$summary = preg_replace("/\[download(=([^\]]+))?\]([0-9,\s]+?)\[\/download\]/i", "", $summary);
			$summary = preg_replace("/\[thread(=([^\]]+))?\]([0-9,\s]+?)\[\/thread\]/i", "", $summary);

			if(mb_strlen($summary, CHARSET) > 200){
				$summary = mb_substr($summary, 0, 200, CHARSET);
			}
		}
		$summary = $summary ? $summary : $defvalue;
		$summary = str_replace(array("\r", "\n", "\t"), '', $summary);
		if (strcasecmp(CHARSET, 'utf-8') === 0) {
			$summary = str_replace(chr(0xe3) . chr(0x80) . chr(0x80), '', $summary);
		}else{
			$summary = str_replace(chr(0xa1) . chr(0xa1), '', $summary);
		}
		return str_replace('"', '&quot;', $summary);
	}

	public function fieldTryParse($fields, &$data, $type = 'varchar', $defvalue = null)
	{
		if(!empty($fields)){
			if(!is_array($fields)) $fields = array($fields);
			foreach($fields as $key){
				if(!isset($data[$key]) && $defvalue !== null){
					$data[$key] = $defvalue;
				}elseif(isset($data[$key])){
					if($type == 'int'){
						$data[$key] = intval(trim($data[$key]));
					}elseif($type == 'varchar'){
						$data[$key] = trim(strip_tags($data[$key]));
					}else{
						$data[$key] = trim($data[$key]);
					}
				}
			}
			return true;
		}
		return false;
	}

	public function insertTags($tags, $tid = 0, $chanid = 0)
	{
		if(!($tags = stripstring($tags))) return '';
		$count = $tagid = 0;
		$tagids = $tagsdata = array();
		if($tagarray = $this->getTagsList($tags)){
			foreach ($tagarray as $tagname) {
				$tagname = trim($tagname);
				if (preg_match('/^(.+){2,50}$/s', $tagname)) {
					$result = DB::fetch_first("SELECT tagid FROM " . DB::table('tags') . " WHERE tagname='$tagname'");
					if ($result['tagid']) {
						$tagid = $result['tagid'];
					} else {
						DB::query("INSERT INTO " . DB::table('tags') . " (tagname, tagnum, ishot) VALUES ('$tagname', '0', '0')");
						$tagid = DB::insert_id();
					}
					if ($tagid && $tid) {
						$tagids[] = $tagid;
						DB::query("INSERT INTO " . DB::table('tagdata') . " (tagid, tagname, tid, chanid) VALUES ('$tagid', '$tagname', '$tid', '$chanid')");
						$count++;
						$tagsdata[] = "$tagid,$tagname";
					}
					if ($count > 4) {
						unset($tagarray);
						break;
					}
				}
			}
			if ($updateids = implodeids($tagids)) {
				DB::update('tags', 'tagnum=tagnum+1', "tagid IN($updateids)");
			}
		}
		return empty($tagsdata) ? '' : addslashes(implode("\t", $tagsdata));
	}
	
	public function getTagsList($string)
	{
		if($string = trim(strip_tags($string))){
			$tmparray = array();
			$string = str_replace(array("&nbsp;", '"', "'",'`', "\r", "\n", "\\"), '', $string);
			$string = str_replace(array("(", ')', "[", ']', "{", "}", "<", ">"), '', $string);
			$string = str_replace(array("~", '!', "?", '*', "^", "=", "@", "/"), '', $string);
			$string = str_replace(array("|", "$", ";"), ',', $string);
			$string = str_replace("\t", ' ', $string);
			if (strpos($string, ',') !== false) {
				$tmparray = array_unique(explode(',', $string));
			}else{
				$tmparray = array_unique(explode(' ', $string));
			}
			return $tmparray;
		}
		return false;
	}
	
	public function updateTags($tags, $tid = 0, $chanid = 0)
	{
		if(empty($tid)) return false;
		$tags = stripstring($tags);
		$tagnewarray = $tagids = $tagsrcarray = $tagsdata = array();
		$query = DB::query("SELECT tagid, tagname FROM " . DB::table('tagdata') . " WHERE tid='$tid'");
		while ($row = DB::fetch_array($query)) {
			$tagsrcarray[$row['tagid']] = $row['tagname'];
			$tagsdata[$row['tagid']] = $row['tagid'] . ',' . $row['tagname'];
		}
		if($tagarray = $this->getTagsList($tags)){
			$count = 0;
			foreach ($tagarray as $tagname) {
				$tagname = trim($tagname);
				if (preg_match('/^(.+){2,30}$/s', $tagname)) {
					$tagnewarray[] = $tagname;
					if (!in_array($tagname, $tagsrcarray)) {
						$result = DB::fetch_first("SELECT tagid FROM " . DB::table('tags') . " WHERE tagname='$tagname'");
						if ($result['tagid']) {
							$tagid = $result['tagid'];
						} else {
							DB::query("INSERT INTO " . DB::table('tags') . " (tagname, tagnum, ishot) VALUES ('$tagname', '0', '0')");
							$tagid = DB::insert_id();
						}
						if ($tagid && $tid) {
							DB::query("INSERT INTO " . DB::table('tagdata') . " (tagid, tagname, tid, chanid) VALUES ('$tagid', '$tagname', '$tid', '$chanid')");
							$tagsdata[$tagid] = "$tagid,$tagname";
							$tagids[] = $tagid;
						}
					}
				}
				$count++;
				if ($count > 4) {
					unset($tagarray);
					break;
				}
			}
			if ($updateids = implodeids($tagids)) {
				DB::update('tags', 'tagnum=tagnum+1', "tagid IN($updateids)");
			}
		}
		foreach ($tagsrcarray as $tagid => $tagname) {
			if (!in_array($tagname, $tagnewarray)) {
				DB::query("DELETE FROM	" . DB::table('tagdata') . " WHERE tid='$tid' AND tagname='$tagname'");
				DB::update('tags', 'tagnum=tagnum-1', array('tagid' => $tagid));
				if(isset($tagsdata[$tagid])){
					unset($tagsdata[$tagid]);
				}
			}
		}
		@sort($tagsdata);
		return empty($tagsdata) ? '' : addslashes(implode("\t", $tagsdata));
	}
	
	public function shiftThreadClass($classids, $tid, $catid = 0, $dateline = 0)
	{
		if (empty($classids) || empty($tid)) return;
		if (!is_array($classids)) $classids = explode(',', trim($classids, ','));
		if (!array_sum($classids)) return;
		$dateline = empty($dateline) ? time() : $dateline;
		$classids = array_unique($classids);
		
	}
	
	public function insertThreadClass($classids, $tid, $catid = 0, $dateline = 0, $status = 1)
	{
		if (empty($classids) || empty($tid) || !$status) return;
		if (!is_array($classids)) $classids = explode(',', trim($classids, ','));
		if (!array_sum($classids)) return;
		$dateline = empty($dateline) ? time() : $dateline;
		$classids = array_unique($classids);
		foreach ($classids as $classid) {
			if ($classid = intval($classid)) {
				DB::insert('thread_class_data', array(
				'tid' => $tid,
				'classid' => $classid,
				'catid' => $catid,
				'dateline' => $dateline
				), FALSE, TRUE);
			}
		}
	}

	public function updateThreadClass($classids, $tid, $catid = 0, $dateline = 0, $isupdate = 0, $status = 1)
	{
		if (empty($tid) || !$status) return;
		if (!is_array($classids)) $classids = explode(',', trim($classids, ", \t\r\n"));
		$classids = array_unique($classids);
		$data = $deletes = array();
		$query = DB::query("SELECT classid, catid FROM " . DB::table('thread_class_data') . " WHERE tid='$tid'");
		while ($row = DB::fetch_array($query)) {
			if (empty($classids) || ($classids && !in_array($row['classid'], $classids))) {
				$deletes[] = $row['classid'];
			} else {
				$data[] = $row['classid'];
			}
		}
		if (empty($classids) && empty($deletes)) return;
		if ($deletes) {
			$deleteids = implodeids($deletes);
			DB::delete('thread_class_data', "tid='$tid' AND classid IN($deleteids)");
		}
		$dateline = empty($dateline) ? time() : $dateline;
		if($data && $catid > 0){
			$dataclass = array('catid' => $catid);
			if($isupdate && $dateline > 1){
				$dataclass['dateline'] = $dateline;
			}
			DB::update('thread_class_data', array('dateline' => $dateline), "tid='$tid'");
		}
		if (empty($classids)) return;
		foreach ($classids as $classid) {
			if ($classid && (empty($data) || ($data && !in_array($classid, $data)))) {
				DB::insert('thread_class_data', array('tid' => $tid, 'classid' => $classid, 'catid' => $catid, 'dateline' => $dateline), FALSE, TRUE);
			}
		}
	}
	
	public function updateSpecialData($tid, $special, $dateline = 0, $isupdate = 1)
	{
		if (empty($special['specid']) || empty($tid) || !isset($special['classid'])) return false;
		$specid = $special['specid'];
		$classid = $special['classid'];
		$dateline = empty($dateline) ? time() : $dateline;
		$condition = "tid='$tid' AND specid='$specid'";
		$query = DB::query("SELECT tid, specid, classid FROM " . DB::table('special_data') . " WHERE $condition LIMIT 1");
		if($data = DB::fetch_array($query)){
			if($classid >= 0){
				if($classid != $data['classid'] || $isupdate){
					DB::update('special_data', array(
						'classid' => $classid,
						'dateline' => $dateline
					), $condition);
				}
			}else{
				DB::delete('special_data', $condition);
			}
		}elseif($classid >= 0){
			DB::insert('special_data', array(
				'tid' => $tid,
				'specid' => $specid,
				'classid' => $classid,
				'dateline' => $dateline
			));
		}
		return true;
	}
	
	public function formatSeparator($string, $glue = '/', $length = 252)
	{
		if(empty($string)) return '';
		if (strcasecmp(CHARSET, 'utf-8') === 0) {
			$string = str_replace(array(chr(0xef) . chr(0xbc) . chr(0xbc), chr(0xef) . chr(0xbc) . chr(0x8f)), '/', $string);
			$string = str_replace(array(chr(0xe3) . chr(0x80) . chr(0x80), chr(0xe3) . chr(0x80) . chr(0x81)), '/', $string);
			$string = str_replace(array(chr(0xef) . chr(0xbc) . chr(0x9b), chr(0xef) . chr(0xbc) . chr(0x8c)), '/', $string);
		}else{
			$string = str_replace(array(chr(0xa3) . chr(0xdc), chr(0xa3) . chr(0xaf), chr(0xa1) . chr(0xa2)), '/', $string);
			$string = str_replace(array(chr(0xa1) . chr(0xa1), chr(0xa3) . chr(0xac), chr(0xa3) . chr(0xbb)), '/', $string);
		}
		$string = str_replace(array('"', "\r", "\n"), '', $string);
		$string = str_replace(array(',', '\\', '|'), '/', $string);
		$tmparray = $tmpnew = array();
		if (strpos($string, '/') !== false) {
			$string = str_replace(array("\t", "&nbsp;"), '/', $string);
			$tmparray = array_unique(explode('/', $string));
		}else{
			$string = str_replace(array("\t", "&nbsp;"), ' ', $string);
			$tmparray = array_unique(explode(' ', $string));
		}
		foreach ($tmparray as $value) {
			$value = trim($value);
			if (preg_match('/^([\x7f-\xff_-]|\w|\.|&|\s){2,50}$/', $value)) {
				$tmpnew[] = $value;
			}
		}
		if(empty($tmpnew)) return '';
		$tmpstr = $glue === false ? $tmpnew : implode($glue, $tmpnew);
		return $length > 1 && mb_strlen($tmpstr, CHARSET) > $length ? mb_substr($tmpstr, 0, $length, CHARSET) : $tmpstr;
		
	}

	public function insertPersons($persons, $tid = 0, $chanid = 0)
	{
		if(empty($persons)) return null;
		$personarray = array_unique(explode('/', $persons));
		$count = $personid = 0;
		$personids = $personarr = array();
		foreach ($personarray as $name) {
			$name = trim($name);
			if (preg_match('/^([\x7f-\xff_-]|\w|\.|&|\s){2,50}$/', $name)) {
				if($person = DB::fetch_first("SELECT personid FROM " . DB::table('persons') . " WHERE name='$name'")){
					$personid = $person['personid'];
				}else{
					DB::query("INSERT INTO " . DB::table('persons') . " (name, num) VALUES ('$name', '0')");
					$personid = DB::insert_id();
				}
				if ($personid && $tid) {
					$personids[] = $personid;
					$personarr[] = $name;
					DB::query("INSERT INTO " . DB::table('persondata') . " (personid, name, tid, chanid) VALUES ('$personid', '$name', '$tid', '$chanid')");
					$count++;
				}
				if ($count > 20) {
					unset($personarray);
					break;
				}
			}
		}
		if ($updateids = implodeids($personids)) {
			DB::update('persons', 'num=num+1', "personid IN($updateids)");
		}
		return $personarr ? implode('/', $personarr) : '';
	}

	public function updatePersons($persons, $tid = 0, $chanid = 0, $tablename = 'video')
	{
		if(empty($tid) || empty($tablename)) return false;
		$personnewarray = $personids = $personsrcarray = $personidsarray = array();
		$query = DB::query("SELECT personid, name FROM " . DB::table('persondata') . " WHERE tid='$tid'");
		while ($row = DB::fetch_array($query)) {
			$personsrcarray[] = $row['name'];
			$personidsarray[] = $row['personid'];
		}
		if(!empty($persons)){
			$personarray = array_unique(explode('/', $persons));
			$count = $personid = 0;
			foreach ($personarray as $name) {
				$name = trim($name);
				if (preg_match('/^([\x7f-\xff_-]|\w|\.|&|\s){2,50}$/', $name)) {
					$personnewarray[] = $name;
					if (!in_array($name, $personsrcarray)) {
						if($person = DB::fetch_first("SELECT personid FROM " . DB::table('persons') . " WHERE name='$name'")){
							$personid = $person['personid'];
						}else{
							DB::query("INSERT INTO " . DB::table('persons') . " (name, num) VALUES ('$name', '0')");
							$personid = DB::insert_id();
						}
						if ($personid && $tid) {
							DB::query("INSERT INTO " . DB::table('persondata') . " (personid, name, tid, chanid) VALUES ('$personid', '$name', '$tid', '$chanid')");
							$personids[] = $personid;
						}
					}
				}
				$count++;
				if ($count > 20) {
					unset($personarray);
					break;
				}
			}
			if ($updateids = implodeids($personids)) {
				DB::update('persons', 'num=num+1', "personid IN($updateids)");
			}
		}
		foreach ($personsrcarray as $key => $name) {
			if (!in_array($name, $personnewarray)) {
				DB::query("DELETE FROM	" . DB::table('persondata') . " WHERE tid='$tid' AND name='$name'");
				$personid = $personidsarray[$key];
				DB::update('persons', 'num=num-1', array('personid' => $personid));
			}
		}
		@sort($personnewarray);
		return $personnewarray ? implode('/', $personnewarray) : '';
	}

	public function getRootID($catid)
	{
		$rootid = (int)DB::result_first("SELECT rootid FROM " . DB::table('category') . " WHERE catid='$catid'");
		return $rootid;
	}

	public function sortord()
	{
		$sortord = (int)DB::result_first("SELECT MAX(sortord) FROM " . DB::table('threads') . " WHERE 1=1");
		return $sortord + 1;
	}

	public function formatRunSystem($runsystem)
	{
		if(empty($runsystem)){
			return 'WinXP, Win7, Win8';
		}
		$runsystem = stripslashes($runsystem);
		$runsystem = str_replace(array("'", '"'), '', $runsystem);
		$runsystem = str_replace(array(chr(0xa3) . chr(0xdc), chr(0xa3) . chr(0xaf), chr(0xa1) . chr(0xa2), '/', '&nbsp;'), ',', $runsystem);
		$runsystem = str_replace(array(chr(0xa3) . chr(0xac), chr(0xa1) . chr(0x41), chr(0xef) . chr(0xbc) . chr(0x8c)), ',', $runsystem);
		$runsysarray = array_unique(explode(',', $runsystem));
		$systemnew = array();
		$count = 0;
		foreach ($runsysarray as $platform) {
			if (preg_match('/^([\x7f-\xff_-]|\.|\w|\s){2,30}$/', $platform)) {
				$systemnew[] = trim($platform);
				$count++;
				if($count >=10){
					break;
				}
			}
		}
		return $systemnew ? implode(', ', $systemnew) : 'WinXP, Win7, Win8';
	}

	public function insertPollvotes($pollvotes, $voteoption, $votes, $tid) {
		if (is_empty($voteoption)) return;

		$votersum = array();
		$updatevotes = array();
		$pollvotes['voters'] = 0;
		$pollvotes['tid'] = $tid;
		$pollvotes['choices'] = intval($pollvotes['choices']);
		$pollvotes['expiration'] = intval($pollvotes['expiration']);
		if ($pollvotes['choices'] > 1) {
			$pollvotes['checkbox'] = 1;
		} else {
			$pollvotes['checkbox'] = 0;
			$pollvotes['choices'] = 1;
		}
		if ($pollvotes['expiration'] > 0) {
			$days = $pollvotes['expiration'];
			$pollvotes['expiration'] = strtotime("+$days day");
		} else {
			$pollvotes['expiration'] = 0;
		}
		$pollid = DB::insert('pollvotes', $pollvotes, TRUE);
		if ($pollid) {
			foreach ($voteoption as $key => $value) {
				if (!empty($value)) {
					$votersum[] = intval($votes[$key]);
					DB::insert('polloption', array(
					'pollid' => $pollid,
					'tid' => $tid,
					'voteoption' => $value,
					'votes' => intval($votes[$key])
					));
				}
			}
			$optioncount = count($votersum);
			$voter_sum = array_sum($votersum);
			if ($pollvotes['choices'] > $optioncount) {
				$updatevotes['choices'] = $optioncount;
			}
			unset($pollvotes);
			$updatevotes['voters'] = $voter_sum;
			DB::update('pollvotes', $updatevotes, array('pollid' => $pollid));
			DB::update('threads', array('polled' => 1), array('tid' => $tid));
		}
	}

	public function updatePollvotes($pollvotes, $voteoption, $votes, $tid, $pollid, $voteids, $polled = 0) {
		if (is_empty($voteoption) || $pollid == 0 || empty($tid)) {
			if($polled && $tid){
				DB::delete('pollvotes', "tid='$tid'");
				DB::delete('polloption', "tid='$tid'");
				DB::update('threads', array('polled' => 0), "tid='$tid'");
			}
			return;
		}

		$votersum = array();
		$voteid = 0;
		$pollvotes['tid'] = $tid;
		$pollvotes['choices'] = intval($pollvotes['choices']);
		$pollvotes['expiration'] = intval($pollvotes['expiration']);
		if ($pollvotes['choices'] > 1) {
			$pollvotes['checkbox'] = 1;
		} else {
			$pollvotes['checkbox'] = 0;
			$pollvotes['choices'] = 1;
		}
		if ($pollvotes['expiration'] > 0) {
			$days = $pollvotes['expiration'];
			$pollvotes['expiration'] = strtotime("+$days day");
		} else {
			if ($pollvotes['expiration'] == -1) {
				$pollvotes['expiration'] = 0;
			} else {
				unset($pollvotes['expiration']);
			}
		}
		$deleted = true;
		foreach ($voteoption as $key => $value) {
			$voteid = intval($voteids[$key]);
			if (!empty($value)) {
				$deleted = false;
				$data = array('voteoption' => $value, 'votes' => intval($votes[$key]));
				if ($voteid) {
					DB::update('polloption', $data, array('voteid' => $voteid));
				} else {
					$data['pollid'] = $pollid;
					$data['tid'] = $tid;
					DB::insert('polloption', $data);
				}
				$votersum[] = intval($votes[$key]);
			}else{
				DB::delete('polloption', "voteid='$voteid'");
			}
		}
		if(!$deleted){
			$optioncount = count($votersum);
			$voter_sum = array_sum($votersum);
			if ($pollvotes['choices'] > $optioncount) {
				$pollvotes['choices'] = $optioncount;
			}
			$pollvotes['voters'] = $voter_sum;
			DB::update('pollvotes', $pollvotes, array('pollid' => $pollid));
			DB::update('threads', array('polled' => 1), array('tid' => $tid));
		}else{
			DB::delete('pollvotes', "pollid='$pollid'");
			DB::delete('polloption', "pollid='$pollid'");
			DB::update('threads', array('polled' => 0), array('tid' => $tid));
		}
	}
	public function deleteUploadTemp($tmpid1, $tmpid2 = 0, $uid = 0)
	{
		$uid = $uid ? $uid : phpcom::$G['uid'];
		if($tmpid1 = intval($tmpid1)){
			DB::delete('upload_temp', "uid='$uid' AND tmpid='$tmpid1'");
		}
		if($tmpid2 = intval($tmpid2)){
			DB::delete('upload_temp', "uid='$uid' AND tmpid='$tmpid2'");
		}
	}
	
	public function updateThreadImage($tid, $thumbtmpid, $previewtmpid, $module = 'article', $action = 'add')
	{
		if(empty($tid) && !$thumbtmpid && !$previewtmpid){
			return false;
		}
		$images = array();
		$thumbflag = $previewflag = false;
		if($thumbtmpid > 0 && ($tmp = Attachment::getUploadTemp($thumbtmpid))){
			if(!empty($tmp['attachment']) && phpcom::$G['uid'] == $tmp['uid']){
				$images['attachment'] = $tmp['attachment'];
				$images['thumb'] = $tmp['thumb'];
				$images['remote'] = Attachment::ftpOneUpload($tmp, $this->chanid);
				DB::delete('upload_temp', "tmpid='{$tmp['tmpid']}'");
				$thumbflag = true;
			}
		}else{
			$thumbflag = ($thumbtmpid == -1);
		}

		if($previewtmpid > 0 && ($tmp = Attachment::getUploadTemp($previewtmpid))){
			if(!empty($tmp['attachment']) && phpcom::$G['uid'] == $tmp['uid']){
				$images['attachimg'] = $tmp['attachment'];
				$images['preview'] = Attachment::ftpOneUpload($tmp, $this->chanid);
				DB::delete('upload_temp', "tmpid='{$tmp['tmpid']}'");
				$previewflag = true;
			}
		}else{
			$previewflag = ($previewtmpid == -1);
		}

		if(empty($images) && !$thumbflag && !$previewflag) return false;
		if($img = DB::fetch_first("SELECT * FROM " . DB::table('thread_image') . " WHERE tid='$tid' LIMIT 1")){
			if(!empty($images)){
				DB::update('thread_image', $images, "tid='$tid'");
				DB::update('threads', array('image' => 1), "tid='$tid'");
			}
			$unlinks = array('dirname' => $module);
			if($thumbflag && !empty($img['attachment'])){
				$unlinks['attachment'] = trim($img['attachment']);
				$unlinks['thumb'] = $img['thumb'];
				$unlinks['remote'] = $img['remote'];
				Attachment::uploadUnlink($unlinks);
			}
				
			if($previewflag && !empty($img['attachimg'])){
				$unlinks['attachment'] = trim($img['attachimg']);
				$unlinks['thumb'] = 0;
				$unlinks['remote'] = $img['preview'];
				Attachment::uploadUnlink($unlinks);
			}
				
			// If the thumbnail and the preview is empty to update, and delete
			if(empty($images) && $thumbflag && $previewflag){
				DB::update('threads', array('image' => 0), "tid='$tid'");
				DB::delete('thread_image', "tid='$tid'");
			}
				
		}elseif(!empty($images)){
			$images['tid'] = $tid;
			if(empty($images['attachment'])) $images['attachment'] = '';
			if(empty($images['attachimg'])) $images['attachimg'] = '';
			DB::insert('thread_image', $images);
			DB::query("UPDATE ".DB::table('threads')." SET image='1' WHERE tid='$tid'", 'UNBUFFERED');
		}
	}

	public function updateThreadAttachImage($tid, $imageaid = 0, $module = 'article')
	{
		return false;
		if(!$tid || !$imageaid){
			return false;
		}
		$threadimage = array();
		if($imageaid == -1){
			$threadimage = DB::fetch_first("SELECT attachid, attachment, remote, thumb, preview FROM ".DB::table("attachment_$module")." WHERE tid='$tid' AND image='1' ORDER BY width DESC LIMIT 1");
			$imageaid = $threadimage['attachid'];
		}
		if($imageaid > 0){
			if(!$threadimage){
				$threadimage = DB::fetch_first("SELECT attachid, attachment, remote, thumb, preview FROM ".DB::table("attachment_$module")." WHERE tid='$tid' AND image='1' AND attachid='$imageaid'");
			}
			if($threadimage){
				$threadimage = addslashes_array($threadimage);
				unset($threadimage['attachid']);
				if(DB::result_first("SELECT tid FROM " . DB::table('thread_image') . " WHERE tid='$tid'")){
					//DB::update('thread_image', $threadimage, "tid='$tid'");
				}else{
					$threadimage['tid'] = $tid;
					DB::insert('thread_image', $threadimage);
				}
			}
		}
		return true;
	}

	public function updateAttach($tid, $attachnew, $uid = 0, $chanid = 0, $module = null)
	{
		$chanid = $chanid ? $chanid : $this->chanid;
		$module = empty($module) ? $this->module : $module;
		$uid = $uid ? $uid : $this->uid;
		$uidcond = " AND chanid='$chanid'";//" AND uid=$uid";
		$newattach = $attachids = array();
		if ($attachnew) {
			$newattachids = array_keys($attachnew);
			$query = DB::query("SELECT * FROM " . DB::table('attachment_temp') . " WHERE attachid IN (" . implodeids($newattachids) . ")$uidcond");
			while ($attach = DB::fetch_array($query)) {
				$newattach[$attach['attachid']] = addslashes_array($attach);
				$attachids[] = $attach['attachid'];
			}

			$tableid = Attachment::getAttachTableId($chanid, $module);
			foreach ($attachnew as $attachid => $attach) {
				if(isset($newattach[$attachid])){
					$updata = $newattach[$attachid];
					$isupdate = false;
					unset($updata['module']);
				}else{
					$updata = array();
					$isupdate = true;
				}
				$updata['tid'] = $tid;
				$updata['uid'] = $uid;
				$updata['sortord'] = isset($attach['sortord']) ? intval($attach['sortord']) : 0;
				$updata['description'] = strcut(htmlcharsencode($attach['description']), 100);
				if(isset($attach['url'])){
					$updata['url'] = trim($attach['url']);
				}
				if($isupdate){
					DB::update("attachment_$module", $updata, "attachid='$attachid'");
				}else{
					DB::insert("attachment_$module", $updata);
					DB::update("attachment", array('tid' => $tid, 'uid' => $uid, 'tableid' => $tableid), "attachid='$attachid'");
				}
				if (!isset($newattach[$attachid]) || !$newattach[$attachid]) {
					continue;
				}
				DB::delete('attachment_temp', "attachid='$attachid'");
			}
			if($newattach){
				Attachment::ftpUpload($attachids, $chanid, $module);
			}
			if($newattach && $uid == phpcom::$G['uid']) {
				update_creditbyaction('postattach', $uid, array(), count($newattach), 1);
			}
		}

		$attachcount = DB::result_first("SELECT COUNT(*) FROM ".DB::table('attachment')." WHERE tid='$tid'");
		$attachment = $attachcount ? (DB::result_first("SELECT COUNT(*) FROM ".DB::table("attachment_$module")." WHERE tid='$tid' AND image>'0'") ? 2 : 1) : 0;

		DB::query("UPDATE ".DB::table('threads')." SET attached='$attachment' WHERE tid='$tid'", 'UNBUFFERED');
		//if(!$attachment) {
		//DB::delete('thread_image', "tid='$tid'");
		//}
	}

	public function downloadContentImage($content, $tid = 0, $tableindex = 0, $chanid = 0)
	{
		if(stripos($content, '[/img]') && phpcom::$G['group']['remoteimage']){
			$tableArrayIndex = array('article' => 1, 'soft' => 2, 'photo' => 3, 'video' => 5, 'temp' => 127);
			$channel = $this->channel;
			if($chanid){
				$channel = &phpcom::$G['channel'][$chanid];
			}else{
				$chanid = $this->chanid;
			}
			$module = $channel['modules'];
			$tableid = isset($tableArrayIndex[$module]) && $tid ? $tableArrayIndex[$module] : 127;
			$uid = $this->uid;
			$attachids = array();
			if(preg_match_all("/\[img.*?\](.*?)\[\/img\]/is", $content, $matches, PREG_SET_ORDER)){
				$request = new WebRequest();
				$http = $request->getInstance();
				$http->keepOriginal = FALSE;
				if(empty(phpcom::$setting['attachsubdir'])) phpcom::$setting['attachsubdir'] = 'Y/md';
				$attdir = FileUtils::getAttachmentDir(null, phpcom::$setting['attachsubdir']);
				$http->setAttachDir(phpcom::$setting['attachdir'], $module, $attdir);
				foreach($matches as $key => $images){
					$http->send($images[1]);
					if($http->errno()) continue;
					if($http->download()){
						$attachs = $http->getAttachs();
						$thumb = $remote = $width = 0;
						if($attachs['image'] && !empty($channel)){
							$image = new phpcom_image();
							$image->SetWatermarkFile($channel['waterimage']);
							if(isset($channel['gravity']) && $channel['autogravity']){
								$gravity = $channel['gravity'];
								$keys = array_rand($gravity);
								$image->SetGravity($gravity[$keys]);
							}
							if (phpcom::$setting['watermark']['status'] && $channel['watermark']) {
								$image->Watermark($attachs['filename']);
							}
							list($width) = @getimagesize($attachs['filename']);
							if ($channel['thumbstatus'] && $channel['thumbauto'] >= 2) {
								$thumb = $image->Thumbnail($attachs['filename'], '', $channel['thumbwidth'], $channel['thumbheight'], $channel['thumbstatus'], 0);
							}
						}

						$data = array('uid' => $uid, 'chanid' => $chanid, 'filesize' => $attachs['size'], 
								'attachment' => $attachs['attachment'], 'dateline' => TIMESTAMP, 
								'image' => $attachs['image'], 'thumb' => $thumb,
								'remote' => $remote, 'width' => $width
						);
						$attachid = Attachment::getAttachId($uid, $chanid, $tid, $tableid);
						$data['attachid'] = $attachid;
						$attachids[] = $attachid;
						if(empty($tid) && $tableid == 127){
							$data['module'] = $module;
							DB::insert('attachment_temp', $data);
						}else{
							$data['tid'] = $tid;
							$data['sortord'] = intval($key) + 1;
							$data['description'] = '';
							DB::insert("attachment_$module", $data);
						}
						$content = str_replace($images[0], "[attach]{$attachid}[/attach]", $content);
					}
				}
				if($attachids && $tid){
					Attachment::ftpUpload($attachids, $chanid, $module);
					$this->updateThreadImage($tid, $attachids[0], $module);
					DB::query("UPDATE ".DB::table('threads')." SET attached='2' WHERE tid='$tid'", 'UNBUFFERED');
					$tablename = $module . "_content";
					$contents = addslashes($content);
					DB::query("UPDATE ".DB::table($tablename, $tableindex)." SET `content`='$contents' WHERE tid='$tid'", 'UNBUFFERED');
				}
			}
		}
		return $content;
	}
}
?>