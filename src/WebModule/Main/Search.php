<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Search.php  2012-8-4
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Main_Search extends Controller_MainAbstract
{
	protected $words = array();
	public function loadActionIndex()
	{
		$this->initialize();
		if(phpcom::$setting['search']['closed'] || !phpcom::$G['group']['allowsearch']){
			showmessage('search_permission_denied');
		}
		$this->keyword = phpcom::$setting['keyword'];
		$this->description = phpcom::$setting['description'];
		$this->title = lang('common', 'search');
		if($this->request->getPost('word') !== null || $this->request->getPost('q') !== null){
			$this->loadSearchResult();
		}else{
			$this->loadSearchIndex();
		}
		return 1;
	}

	protected function loadSearchIndex()
	{
		include template('search_index');
	}
	
	protected function loadSearchResult()
	{
		$datalist = array();
		$srchword = $this->request->getPost('word') ? trim($this->request->getPost('word')) : trim($this->request->getPost('q'));
		$this->title = $title = htmlcharsencode(stripslashes($srchword));
		$maxsearchresult = max(50, min(2000, phpcom::$setting['search']['maxresult']));
		$searchid = intval($this->request->getPost('searchid', 0));
		$isimage = phpcom::$setting['search']['image'] == 1 ? intval($this->request->getPost('image')) : 0;
		$isimage = phpcom::$setting['search']['image'] == 2 ? 1 : $isimage;
		$stype = 0;
		$tn = trim($this->request->getPost('tn', 'all'));
		switch ($tn) {
			case 1:
			case "article": $stype = 1; break;
			case 2:
			case "soft": $stype = 2; break;
			case 3:
			case "photo": $stype = 3; break;
			case 4:
			case "special": $stype = 4; break;
			case 5:
			case "video": $stype = 5; break;
			default: $stype = 0; break;
		}
		$word = rawurlencode(stripslashes($srchword));
		$maxwords = max(5, min(100, phpcom::$setting['search']['maxwords']));
		$keywords = trim(addslashes(cutstr(stripslashes($srchword), $maxwords, '')));
		$showpage = '';
		$pagecount = $pagenow = $pagestart = 0;
		$pagesize = max(10, phpcom::$setting['search']['pagesize']);
		$searchtids = array();
		if($searchid){
			if($shindex = DB::fetch_first("SELECT searchid, tids FROM " . DB::table('searchindex') . " WHERE searchid='$searchid'")){
				$searchid = $shindex['searchid'];
				$searchtids = explode(',', $shindex['tids']);
			}else{
				showmessage('search_id_invalid', 'search.php');
			}
		}else{
			$cachelifetime = phpcom::$setting['search']['lifetime'];
			if($shindex = DB::fetch_first("SELECT searchid, dateline, tids FROM " . DB::table('searchindex') . " WHERE stype='$stype' AND keyword='$keywords' LIMIT 1")){
				$searchid = $shindex['searchid'];
				if($cachelifetime && trim($shindex['tids']) && $shindex['dateline'] > TIMESTAMP - $cachelifetime){
					$searchtids = explode(',', $shindex['tids']);
				}else{
					$searchtids = $this->getSearchIds($keywords, $stype, $maxsearchresult);
					DB::update('searchindex', array(
					'dateline' => TIMESTAMP,
					'ip' => phpcom::$G['clientip'],
					'tids' => implode(',', $searchtids)), "searchid='$searchid'");
				}
			}else{
				$timestamp = phpcom::$G['timestamp'];
				$clientip = phpcom::$G['clientip'];
				$timeout = phpcom::$setting['search']['timeout'];
				$sql = "SELECT searchid, dateline,($timeout<>'0' AND ip='$clientip' AND $timestamp-dateline<'$timeout') AS flood
				FROM ".DB::table('searchindex')."
				WHERE stype='$stype' AND ($timeout<>'0' AND ip='$clientip' AND $timestamp-dateline<'$timeout')
				ORDER BY flood";
				$query = DB::query($sql);
				while($shindex = DB::fetch_array($query)) {
					if(phpcom::$G['adminid'] == '1' && $shindex['flood']){
						showmessage('search_timeout', 'search.php', array('timeout' => $timeout));
					}
				}
				$searchtids = $this->getSearchIds($keywords, $stype, $maxsearchresult);
				if($searchtids && !empty($keywords)){
					$searchid = DB::insert('searchindex', array(
							'stype' => $stype,
							'keyword' => $keywords,
							'dateline' => TIMESTAMP,
							'ip' => $clientip,
							'tids' => implode(',', $searchtids)), TRUE);
				}
			}
		}
		
		if($count = count($searchtids)){
			$pagecount = @ceil($count / $pagesize);
			$pagenow = max(1, min($pagecount, intval($this->request->getPost('page', 1))));
			$pagestart = floor(($pagenow - 1) * $pagesize);
			$tidarray = array_slice($searchtids, $pagestart, $pagesize);
			$tids = implodeids($tidarray);
			$columname = 't.*, c.depth, c.basic, c.catname, c.codename, c.prefixurl, c.prefix, c.caturl';
			$sql = "FROM " . DB::table('threads') . " t LEFT JOIN " . DB::table('category') . " c USING(catid) ";
			if($stype == 1){
				$sql .= "INNER JOIN " . DB::table('article_thread') . " a USING(tid) ";
				$columname .= ', a.summary';
			}elseif($stype == 2){
				$sql .= "INNER JOIN " . DB::table('soft_thread') . " s USING(tid) ";
				$columname .= ', s.summary';
			}elseif($stype == 3){
				$sql .= "INNER JOIN " . DB::table('photo_thread') . " s USING(tid) ";
				$columname .= ', s.summary';
			}elseif($stype == 4){
				$sql .= "INNER JOIN " . DB::table('special_thread') . " s USING(tid) ";
				$columname .= ', s.summary';
			}elseif($stype == 5){
				$sql .= "INNER JOIN " . DB::table('video_thread') . " s USING(tid) ";
				$columname .= ', s.summary';
			}else{
				$sql .= "LEFT JOIN " . DB::table('article_thread') . " a USING(tid)
						LEFT JOIN " . DB::table('soft_thread') . " s USING(tid)
						LEFT JOIN " . DB::table('photo_thread') . " p USING(tid)
						LEFT JOIN " . DB::table('video_thread') . " v USING(tid)
						LEFT JOIN " . DB::table('special_thread') . " sp USING(tid)";
				$columname .= ', a.summary, s.summary as smry, p.summary as pmry, v.summary as vmry, sp.summary as spmry';
			}
			if($isimage){
				$sql .= "LEFT JOIN " . DB::table('thread_image') . " ti USING(tid) ";
				$columname .= ', ti.attachment, ti.remote, ti.thumb, ti.preview, ti.attachimg';
			}
			$i = 0;
			$sql = "SELECT $columname $sql WHERE t.`status`='1' AND t.tid IN($tids) ORDER BY t.dateline DESC";
			$query = DB::query($sql);
			while($thread = DB::fetch_array($query)) {
				$i++;
				$thread['index'] = $i;
				$thread['alt'] = $i % 2 == 0 ? 2 : 1;
				$channel = phpcom::$G['channel'][$thread['chanid']];
				$urlargs = array('chanid' => $thread['chanid'], 'catdir' => $thread['codename'], 'date' => $thread['dateline'],
						'tid' => $thread['tid'], 'catid' => $thread['catid'], 'page' => 1);
				if (empty($channel['domain']) && empty($thread['prefixurl'])) {
					$thread['domain'] = phpcom::$G['siteurl'];
				} elseif(empty($thread['prefixurl'])) {
					$thread['domain'] = $channel['domain'] . '/';
				}else{
					$thread['domain'] = $thread['prefixurl'] . '/';
				}
				
				if(!empty($thread['prefix'])){
					$urlargs['prefix'] = trim($thread['prefix']);
				}
				if (empty($thread['url'])) {
					$urlargs['name'] = empty($thread['htmlname']) ? '' : trim($thread['htmlname']);
					$thread['url'] = geturl('threadview', $urlargs, $thread['domain']);
				}
				if(empty($thread['caturl'])){
					$urlargs['name'] = $thread['codename'];
					if(!empty($thread['prefixurl']) && $thread['basic']){
						$thread['caturl'] = $thread['prefixurl'];
					}else{
						$thread['caturl'] = geturl($thread['basic'] ? 'category' : 'threadlist', $urlargs, $thread['domain']);
					}
				}
				$thread['date'] = fmdate($thread['dateline']);
				if(isset($thread['smry'])){
					$thread['summary'] = $thread['summary'] ? $thread['summary'] : $thread['smry'];
					unset($thread['smry']);
				}
				if(isset($thread['pmry'])){
					$thread['summary'] = $thread['summary'] ? $thread['summary'] : $thread['pmry'];
					unset($thread['pmry']);
				}
				if(isset($thread['vmry'])){
					$thread['summary'] = $thread['summary'] ? $thread['summary'] : $thread['vmry'];
					unset($thread['vmry']);
				}
				if(isset($thread['spmry'])){
					$thread['summary'] = $thread['summary'] ? $thread['summary'] : $thread['spmry'];
					unset($thread['spmry']);
				}
				$thread['title'] = $this->highlightWord($thread['title'],$srchword);
				$thread['summary'] = $this->highlightWord($thread['summary'],$srchword);
				if(isset($thread['attachment']) && $thread['image'] == 1){
					$this->processImageRowData($thread, phpcom::$G['channel'][$thread['chanid']]['modules']);
				}else{
					$thread['image'] = 0;
					$thread['thumburl'] = $thread['previewurl'] = $thread['imageurl'] = '';
					$thread['pixurl'] = $thread['url'];
				}
				if(isset($thread['attached']) && $thread['attached'] == 2){
					$thread['pixurl'] = geturl('preview', array(
							'chanid' => $thread['chanid'],
							'catdir' => $thread['codename'],
							'tid' => $thread['tid'],
							'page' => 1
					), $thread['domain']);
				}
				$datalist[] = $thread;
			}
			
			$pageurl = "search.php?word=$word&searchid=$searchid&page={%d}".($tn == 'all' ? '' : "&tn=$tn").($isimage ? '&image=1' : '');
			$showpage = $this->paging($pagenow, $pagecount, $pagesize, $count, $pageurl, 10);
		}
		$timer = number_format((microtime(true) - phpcom::$G['starttime']), 2);
		$tplname = checktplname('search', $stype);
		include template($tplname);
	}
	
	protected function getSearchLike($keyword, $field, $returnsrchtxt = 0)
	{
		$srchtxt = '';
		if($field && $keyword) {
			if(preg_match("(AND|\+|&|_|\-|%|\s)", $keyword) && !preg_match("(OR|\|)", $keyword)) {
				$andor = ' AND ';
				$keywordsrch = '1';
				$keyword = preg_replace("/( AND |&| |_|\-|%)/is", "+", $keyword);
			} else {
				$andor = ' OR ';
				$keywordsrch = '0';
				$keyword = preg_replace("/( OR |\|)/is", "+", $keyword);
			}
			$keyword = str_replace('*', '%', addcslashes($keyword, '%_'));
			$srchtxt = $returnsrchtxt ? $keyword : '';
			foreach(explode('+', $keyword) as $text) {
				$text = trim($text);
				if($text) {
					$keywordsrch .= $andor;
					$keywordsrch .= str_replace('{text}', $text, $field);
				}
			}
			$keyword = " AND ($keywordsrch)";
		}
		return $returnsrchtxt ? array($srchtxt, $keyword) : $keyword;
	}
	
	protected function highlight($text, $words, $prepend)
	{
		$text = str_replace('\"', '"', $text);
		foreach($words as $key => $replaceword) {
			if(!empty($replaceword)){
				$text = preg_replace("/(".preg_quote($replaceword, '/').")/i", "<em>\\1</em>", $text);
			}
		}
		return "$prepend$text";
	}
	
	protected function highlightCallback($matches){
		return $this->highlight($matches[2], $this->words, $matches[1]);
	}
	
	protected function highlightWord($message, $words)
	{
		if(!empty($words)) {
			$this->words = preg_split('/[(\s|\||\+|&|\-|_|%)]/', $words);
			$sppos = strrpos($message, chr(0).chr(0).chr(0));
			if($sppos !== false) {
				$specialextra = substr($message, $sppos + 3);
				$message = substr($message, 0, $sppos);
			}
			$message = preg_replace_callback("/(^|>)([^<]+)(?=<|$)/sU", array(&$this, 'highlightCallback'), $message);
			/*if (version_compare(PHP_VERSION, '5.4.0', '<')) {
				$message = preg_replace("/(^|>)([^<]+)(?=<|$)/sUe", "\$this->highlight('\\2', \$highlightarray, '\\1')", $message);
			}else{
				$message = preg_replace_callback("/(^|>)([^<]+)(?=<|$)/sU", function($matches) use ($highlightarray) {
					return $this->highlight($matches[2], $highlightarray, $matches[1]);
				}, $message);
			}*/
			if($sppos !== false) {
				$message = $message.chr(0).chr(0).chr(0).$specialextra;
			}
		}
		return $message;
	}
	
	protected function getSearchIds($word, $stype, $maxsearchresult = 500)
	{
		if(empty($word)){
			return array();
		}
		$fulltext = phpcom::$setting['search']['fulltext'];
		$likefield = "t.title LIKE '%{text}%'";
		$sql = "FROM " . DB::table('threads') . " t ";
		if($stype == 1){
			$sql .= "INNER JOIN " . DB::table('article_thread') . " a USING(tid) ";
			$fulltext && $likefield .= " OR a.summary LIKE '%{text}%' OR a.subtitle LIKE '%{text}%'";
		}elseif($stype == 2){
			$sql .= "INNER JOIN " . DB::table('soft_thread') . " s USING(tid) ";
			$fulltext && $likefield .= " OR s.summary LIKE '%{text}%' OR s.subtitle LIKE '%{text}%'";
		}elseif($stype == 3){
			$sql .= "INNER JOIN " . DB::table('photo_thread') . " p USING(tid) ";
			$fulltext && $likefield .= " OR p.summary LIKE '%{text}%' OR p.subtitle LIKE '%{text}%'";
		}elseif($stype == 4){
			$sql .= "INNER JOIN " . DB::table('special_thread') . " p USING(tid) ";
			$fulltext && $likefield .= " OR sp.summary LIKE '%{text}%' OR sp.subject LIKE '%{text}%'";
		}elseif($stype == 5){
			$sql .= "INNER JOIN " . DB::table('video_thread') . " v USING(tid) ";
			$fulltext && $likefield .= " OR v.summary LIKE '%{text}%' OR v.subtitle LIKE '%{text}%'";
		}else{
			$fulltext && $sql .= "LEFT JOIN " . DB::table('article_thread') . " a USING(tid) 
					LEFT JOIN " . DB::table('soft_thread') . " s USING(tid)
					LEFT JOIN " . DB::table('photo_thread') . " p USING(tid) 
					LEFT JOIN " . DB::table('video_thread') . " v USING(tid) 
					LEFT JOIN " . DB::table('special_thread') . " sp USING(tid) ";
			$fulltext && $likefield .= " OR a.summary LIKE '%{text}%' OR s.summary LIKE '%{text}%' 
					OR p.summary LIKE '%{text}%' OR v.summary LIKE '%{text}%' 
					OR s.subtitle LIKE '%{text}%' OR v.subtitle LIKE '%{text}%' OR sp.subject LIKE '%{text}%'";
		}
		$sql .= "WHERE t.status='1' ";
		$sql .= $this->getSearchLike($word, "($likefield)");
		$searchtids = array();
		$query = DB::query("SELECT t.tid $sql  ORDER BY t.dateline DESC LIMIT $maxsearchresult");
		while($thread = DB::fetch_array($query)) {
			$searchtids[] = $thread['tid'];
		}
		return $searchtids;
	}
}
?>