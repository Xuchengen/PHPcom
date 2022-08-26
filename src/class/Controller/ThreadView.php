<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : ThreadView.php  2012-8-6
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Controller_ThreadView extends Controller_MainAbstract
{
	protected $tid = 0;
	protected $tableindex = 0;
	protected $iscaptcha = 0;
	protected $templateName;
	protected $attachimg = array();
	protected $previewpage = 2;

	public function __construct(Web_HttpRequest $request)
	{
		parent::__construct($request);

		$this->iscaptcha = isset(phpcom::$setting['captchastatus'][4]) ? intval(phpcom::$setting['captchastatus'][4]) : 0;
	}

	protected function loadThreadView($tid = 0, $moduleName = null)
	{
		$this->tid = $tid ? intval($tid) : trim($this->request->query('tid', $this->request->getQuery(0)));
		if (!is_numeric($this->tid)) {
			if($name = stripstring($this->request->query('name', $this->request->getQuery(0)))){
				$this->tid = DB::result_first("SELECT tid FROM " . DB::table('threads') . " WHERE htmlname='$name' LIMIT 1");
			}else{
				$this->tid = intval($this->tid);
			}
		}else{
			$this->tid = intval($this->tid);
		}

		$sql = "SELECT t.*,c.depth,c.basic,c.parentid,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,
			c.remote,c.imageurl,c.banner,c.template,f.voteup,f.votedown,f.voters,f.totalscore,f.credits
			FROM " . DB::table('threads') . " t
			LEFT JOIN " . DB::table('category') . " c USING(catid)
			LEFT JOIN " . DB::table('thread_field') . " f USING(tid)
			WHERE t.status='1' AND t.tid='$this->tid'";
		if(!$thread = DB::fetch_first($sql)){
			$this->pageNotFound();
		}
		if(!empty($thread['url'])){
			@header('HTTP/1.1 301 Moved Permanently');
			$_SERVER["REDIRECT_STATUS"] = 301;
			exit(header("location: " . trim($thread['url'])));
		}
		$this->chanid = $thread['chanid'];
		phpcom::$G['channelid'] = $this->chanid;
		phpcom::$G['cache']['channel'] = $channel = phpcom::$G['channel'][$this->chanid];
		$modules = $channel['modules'];
		if ($channel['domain'] || !empty($thread['prefixurl'])) {
			!defined('DOMAIN_ENABLED') && define('DOMAIN_ENABLED', TRUE);
		}
		if (trim($thread['prefixurl'])) {
			$this->chandomain = trim($thread['prefixurl'], ' /') . '/';
		}elseif (trim($channel['domain'])) {
			$this->chandomain = trim($channel['domain'], ' /') . '/';
		}
		$this->channelname = $channel['channelname'];
		$this->previewpage = $channel['previewpage'];
		$this->initialize();
		$this->tableindex = $thread['tableindex'];
		$this->title = htmlcharsencode($thread['title']);
		if(!empty($channel['sitename'])){
			$this->webname = $channel['sitename'];
		}
		if (isset($thread['keyword']) && $thread['keyword']) {
			$this->keyword = htmlcharsencode($thread['keyword']);
		} else {
			$this->keyword = strip_tags($channel['keyword'] ? $channel['keyword'] : phpcom::$setting['keyword']);
		}
		if (isset($thread['summary']) && $thread['summary']) {
			$this->description = htmlcharsencode($thread['summary']);
		} else {
			$this->description = strip_tags($channel['description'] ? $channel['description'] : phpcom::$setting['description']);
		}
		$thread['navcatname'] = '';
		foreach ($this->fetchCategoryNav() as $nav){
			$thread['navcatname'] .= $nav['name'] . ' - ';
		}
		$thread['navcatname'] = trim($thread['navcatname'], '- ');
		$this->rootid = $thread['rootid'];
		$this->catid = $thread['catid'];
		$this->parentid = $thread['parentid'];
		if(!empty($thread['template'])){
			$this->templateName = $modules . '/' . $thread['template'] . '_view';
		}
		$thread['catdir'] = trim($thread['codename']);
		$thread['prefix'] = empty($thread['prefix']) ? '' : trim($thread['prefix']);
		$thread['htmlname'] = empty($thread['htmlname']) ? '' : trim($thread['htmlname']);
		$thread['date'] = fmdate($thread['dateline'], 'd');
		$thread['datetime'] = fmdate($thread['dateline']);
		$thread['lastdate'] = fmdate($thread['lastdate']);
		$thread['commentnum'] = $thread['comments'];
		$thread['isvote'] = intval($thread['polled']);
		if(isset($thread['voteup'])){
			$voteup = intval($thread['voteup']);
			$votedown = intval($thread['votedown']);
			$total = $voteup + $votedown;
			$thread['totalvotes'] = $total;
			$thread['percentup'] = ($voteup ? round(($voteup / $total) * 100, 2) : '0.00') . '%';
			$thread['percentdown'] = ($votedown ? round(($votedown / $total) * 100, 2) : '0.00') . '%';
			$scores = $thread['voters'] ? $thread['totalscore']  / $thread['voters'] : 0;
			$thread['scores'] = $scores < 10 ? sprintf( "%01.1f ", $scores) : 10;
			$thread['percent'] = $thread['scores'] ? ($thread['scores'] * 10) . '%' : '0%';
		}else{
			$thread['voteup'] = $thread['votedown'] = $thread['totalvotes'] = 0;
			$thread['percentup'] = $thread['percentdown'] = '0.0%';
			$thread['voters'] = $thread['totalscore'] = $thread['credits'] = 0;
			$thread['percent'] = '0.0%';
		}
		$thread['module'] = $channel['modules'];
		if(!empty($thread['imageurl'])){
			if(empty($thread['remote'])){
				$thread['imageurl'] = $this->attachurl . 'image/' . $thread['imageurl'];
			}else{
				$thread['imageurl'] = phpcom::$setting['ftp']['attachurl'] . 'image/' . $thread['imageurl'];
			}
		}else{
			$thread['imageurl'] = '';
		}
		$thread['picurl'] = $this->domain . 'misc/images/noimage.jpg';
		if($thread['image'] == 1){
			$thread['threadimage'] = $this->getThreadImageUrl($this->tid, $channel['previewmode'], $channel['modules'], $this->attachimg);
			$thread['picurl'] = $thread['threadimage']['src'];
		}
		return $thread;
	}
	
	protected function getThreadBySpecialID($tid, $limit = 10)
	{
		static $threadSpecids = null;
		if($threadSpecids === null){
			$threadSpecids = '';
			$sql = DB::buildlimit("SELECT specid FROM " . DB::table('special_data') . " WHERE tid='$tid'", $limit);
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				$threadSpecids .= $row['specid'] . ',';
			}
		}
		return trim($threadSpecids, ', ');
	}
	
	protected function threadComments($tid, $type = '', $limit = 10, &$commentids = array()) 
	{
		$limit = $limit ? $limit : 10;
		$condition = $type;
		if (empty($type)) {
			$condition = 'ORDER BY t1.lastdate DESC';
		} elseif ($type == 1) {
			$condition = 'ORDER BY t1.commentid ASC';
		}
		$data = array();
		$sql = DB::buildlimit("SELECT t1.*, t2.* FROM " . DB::table('comments') . " t1
			INNER JOIN " . DB::table('comment_body') . " t2 ON t2.commentid=t1.commentid
			WHERE t1.tid='$tid' AND t2.first='1' AND t2.status='1' $condition", $limit, 0);
		$query = DB::query($sql);
		$i = 0;
		while ($row = DB::fetch_array($query)) {
			$i++;
			$row['index'] = $i;
			$row['alt'] = $i % 2;
			$row['content'] = bbcode::output($row['content']);
			$row['date'] = fmdate($row['lastdate']);
			$row['datetime'] = fmdate($row['dateline']);
			if ($row['username'] == 'guest') {
				$row['username'] = lang('common', 'guest');
			}
			if ($row['author'] == 'guest') {
				$row['author'] = lang('common', 'guest');
			}
			$row['id'] = $row['bodyid'];
			$data[$row['commentid']] = $row;
			$commentids[] = $row['commentid'];
		}
		return $data;
	}

	protected function threadCommentReply($cids) {
		$data = array();
		if($replyids = implodeids($cids)){
			$sql = "SELECT * FROM " . DB::table('comment_body') . "
			WHERE first='0' AND status='1' AND commentid IN($replyids) ORDER BY bodyid ASC";
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				$row['content'] = bbcode::output($row['content']);
				$row['date'] = fmdate($row['dateline']);
				if ($row['author'] == 'guest') {
					$row['author'] = lang('common', 'guest');
				}
				$row['id'] = $row['bodyid'];
				$data[$row['commentid']][$row['bodyid']] = $row;
			}
		}
		return $data;
	}
	
	public function nextThreadUrl($tid = 0, $catid = 0, $chanid = 0) {
		$tid = $tid ? intval($tid) : $this->tid;
		$catid = $catid ? intval($catid) : $this->catid;
		$chanid = $chanid ? intval($chanid) : $this->chanid;
		$condition = $catid ? " AND t.catid='$catid' " : '';
		$sql = "SELECT t.*,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl FROM " . DB::table('threads') . " t
	 	 LEFT JOIN " . DB::table('category') . " c USING(catid)
			 	 WHERE t.status='1' AND t.chanid='$chanid' $condition AND t.tid<'$tid' ORDER BY t.tid DESC LIMIT 0,1";
		if($row = DB::fetch_first($sql)){
			$domain = $this->domain;
			if (!empty($row['prefixurl'])) {
				$domain = trim($row['prefixurl'], ' /') . '/';
			}elseif(!empty(phpcom::$G['channel'][$chanid]['domain'])) {
				$domain = trim(phpcom::$G['channel'][$chanid]['domain'], ' /') . '/';
			}
			$row['url'] = empty($row['url']) ? geturl('threadview', array(
					'chanid' => $row['chanid'],
					'catdir' => $row['codename'],
					'name' => empty($row['htmlname']) ? $row['tid'] : trim($row['htmlname']),
					'prefix' => trim($row['prefix']),
					'date' => $row['dateline'],
					'tid' => $row['tid'],
					'catid' => $row['catid'],
					'page' => 1
			), $domain) : trim($row['rul']);
			return $row['url'];
		}
		return null;
	}
	
	protected function nextThread(array $options = array()) {
		$options += array('tid' => 0, 'catid' => 0, 'length' => 0, 'ellipsis' => '...');
		$s = $condition = $chancond = '';
		$tid = $options['tid'] ? intval($options['tid']) : $this->tid;
		$chanid = isset($options['chanid']) ? intval($options['chanid']) : $this->chanid;
		$catid = intval($options['catid']);
		$length = intval($options['length']);
		$ellipsis = rtrim($options['ellipsis']);

		$condition = $catid ? " AND t.catid='$catid' " : '';
		$chancond = $chanid ? " AND t.chanid='$chanid' " : '';
		$sql = "SELECT t.*,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl FROM " . DB::table('threads') . " t
	 	 LEFT JOIN " . DB::table('category') . " c USING(catid)
		 WHERE t.status='1'$chancond $condition AND t.tid<'$tid' ORDER BY t.tid DESC LIMIT 0,1";
		if($row = DB::fetch_first($sql)){
			$domain = $this->domain;
			if (!empty($row['prefixurl'])) {
				$domain = trim($row['prefixurl'], ' /') . '/';
			}elseif(!empty(phpcom::$G['channel'][$chanid]['domain'])) {
				$domain = trim(phpcom::$G['channel'][$chanid]['domain'], ' /') . '/';
			}
			
			$title = $length ? strcut($row['title'], $length, '...') : $row['title'];
			$row['url'] = empty($row['url']) ? geturl('threadview', array(
					'chanid' => $row['chanid'],
					'catdir' => $row['codename'],
					'name' => empty($row['htmlname']) ? $row['tid'] : trim($row['htmlname']),
					'prefix' => trim($row['prefix']),
					'date' => $row['dateline'],
					'tid' => $row['tid'],
					'catid' => $row['catid'],
					'page' => 1
			), $domain) : trim($row['rul']);
			$s = '<a href="' . $row['url'] . '">' . $title . '</a>';

		} else {
			$s = lang('common', 'nothread');
		}
		return $s;
	}

	protected function prevThread(array $options = array()) {
		$options += array('tid' => 0, 'catid' => 0, 'length' => 0, 'ellipsis' => '...');
		$s = $condition = $chancond = '';
		$tid = $options['tid'] ? intval($options['tid']) : $this->tid;
		$chanid = isset($options['chanid']) ? intval($options['chanid']) : $this->chanid;
		$catid = intval($options['catid']);
		$length = intval($options['length']);
		$ellipsis = rtrim($options['ellipsis']);
		$condition = $catid ? " AND t.catid='$catid' " : '';
		$chancond = $chanid ? " AND t.chanid='$chanid' " : '';
		
		$sql = "SELECT t.*,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl FROM " . DB::table('threads') . " t
	 	 LEFT JOIN " . DB::table('category') . " c USING(catid)
		 WHERE t.status='1'$chancond $condition AND t.tid>'$tid' ORDER BY t.tid ASC LIMIT 0,1";
		if($row = DB::fetch_first($sql)){
			$domain = $this->domain;
			if (!empty($row['prefixurl'])) {
				$domain = trim($row['prefixurl'], ' /') . '/';
			}elseif(!empty(phpcom::$G['channel'][$chanid]['domain'])) {
				$domain = trim(phpcom::$G['channel'][$chanid]['domain'], ' /') . '/';
			}
			
			$title = $length ? strcut($row['title'], $length, '...') : $row['title'];
			$row['url'] = empty($row['url']) ? geturl('threadview', array(
					'chanid' => $row['chanid'],
					'catdir' => $row['codename'],
					'name' => empty($row['htmlname']) ? $row['tid'] : trim($row['htmlname']),
					'prefix' => trim($row['prefix']),
					'date' => $row['dateline'],
					'tid' => $row['tid'],
					'catid' => $row['catid'],
					'page' => 1
			), $domain) : trim($row['rul']);
			$s = '<a href="' . $row['url'] . '">' . $title . '</a>';
		} else {
			$s = lang('common', 'nothread');
		}
		return $s;
	}

	protected function getSoftTest($testsoft) {
		phpcom_cache::load('softtest');
		$testarray = phpcom::$G['cache']['softtest'];
		$returnarray = array();
		if ($testsoft) {
			$testlist = explode(',', $testsoft);
			$count = count($testlist);
			$i = 0;
			foreach ($testlist as $value) {
				$i++;
				$testarray[$value]['index'] = $i;
				$testarray[$value]['count'] = $count;
				if ($testarray[$value]['color']) {
					$testarray[$value]['colors'] = ' style="color:' . $testarray[$value]['color'] . '"';
				} else {
					$testarray[$value]['colors'] = '';
				}
				$returnarray[$value] = $testarray[$value];
			}
		}
		return $returnarray;
	}

	protected function downloadAddress(array $options = array()) {
		$options += array('tid' => 0, 'sid' => 0, 'display' => 0);
		$tid = $options['tid'] ? intval($options['tid']) : $this->tid;
		$sid = intval($options['sid']);
		$showdown = intval($options['display']);
		$downarray = array();
		$data = array();
		$urlargs = array('chanid' => $this->chanid, 'tid' => $tid);
		if(!isset(phpcom::$G['cache']['downserver'])){
			phpcom_cache::load('downserver');
		}
		$sql = "SELECT * FROM " . DB::table('soft_download') . " WHERE tid='$tid' ORDER BY id";
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$id = $row['id'];
			$row['dname'] = trim($row['dname']);
			$row['downurl'] = trim($row['downurl']);
			if($row['servid'] && isset(phpcom::$G['cache']['downserver'][$row['servid']])){
				foreach(phpcom::$G['cache']['downserver'][$row['servid']] as $servers){
					if($sid && $row['servid'] != $sid && $servers['parentid'] != $sid && $servers['depth']){
						continue;
					}
					$urlargs['sid'] = $servers['servid'];
					if ($servers['depth']) {
						$servers['dname'] = $servers['servname'];
						if ($servers['child']) {
							$servers['downurl'] = $servers['servurl'] ? $servers['servurl'] : geturl('down', $urlargs, $this->chandomain);
						}else{
							if($servers['expires'] && $servers['expires'] < $this->todaytime) continue;
							if ($servers['redirect']) {
								$servers['downurl'] = $servers['servurl'];
							} else {
								if ($servers['downmode'] || $showdown) {
									$servers['downurl'] = $servers['servurl'] . $row['downurl'];
								} else {
									$servers['downurl'] = $this->domain . "apps/down.php?tid=$tid&id=$id&sid={$servers['servid']}";
								}
							}
						}
					}else{
						$servers['dname'] = $row['dname'] ? $row['dname'] : $servers['servname'];
						if ($servers['servurl']) {
							$servers['downurl'] = $servers['servurl'];
						} else {
							$servers['downurl'] =  geturl('down', $urlargs, $this->chandomain);
						}
					}
					$servers['name'] = $servers['dname'];
					$servers['url'] = $servers['downurl'];
					$data[] = $servers;
				}
			}else{
				if (!$showdown) {
					$row['downurl'] = "{$this->domain}apps/down.php?tid=$tid&id=$id";
				}
				$row['icons'] = '';
				$row['depth'] = 1;
				$row['child'] = 0;
				$row['name'] = $row['dname'];
				$row['url'] = $row['downurl'];
				$data[] = $row;
			}
		}
		return $data;
	}
	
	public function getAttachImage($tid = 0, $chanid = 0, $domain = '', $pageurl = null)
	{
		$data = array();
		$tid = $tid ? intval($tid) : $this->tid;
		$domain = $domain ? $domain : $this->chandomain;
		$chanid = $chanid ? $chanid : $this->chanid;
		if(empty(phpcom::$G['channel'][$chanid]['modules'])){
			return $data;
		}
		$module = phpcom::$G['channel'][$chanid]['modules'];
		$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table("attachment_$module") . " WHERE tid='$tid' AND image='1'");
		$pagesize = 1;
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($this->page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$urlargs = array('chanid' => $chanid, 'tid' => $tid, 'page' => $pagenow);
		$sql = DB::buildlimit("SELECT attachid, tid, sortord, attachment, description, dateline, thumb, preview, remote
				FROM " . DB::table("attachment_$module") . "
				WHERE tid='$tid' AND image='1' ORDER BY sortord", $pagesize, $pagestart);
		$query = DB::query($sql);
		while ($attach = DB::fetch_array($query)) {
			$urlargs['aid'] = $attach['attachid'];
			$attach['i'] = 1;
			$attach['count'] = $count;
			$attach['pagecount'] = $pagecount;
			$attach['pagenow'] = $pagenow;
			$attach['index'] = str_pad($pagenow, 2 , '0', STR_PAD_LEFT);
			if($attach['remote']){
				$attach['attachurl'] = phpcom::$setting['ftp']['attachurl'] . $module . '/';
			}else{
				$attach['attachurl'] = $this->attachurl . $module . '/';
			}
			$attach['date'] = fmdate($attach['dateline']);
			$attach['imageurl'] = $attach['attachurl'] . $attach['attachment'];
			$attach['thumburl'] = $attach['thumb'] ? generatethumbname($attach['imageurl']) : $attach['imageurl'];
			$urlargs['page'] = '{%d}';
			$pageurl = $pageurl ? $pageurl : geturl('preview', $urlargs, $domain);
			$firsturl = $this->formatPageUrl($pageurl);
			$attach['pageurl'] = $pageurl;
			$attach['firsturl'] = $firsturl;
			if($pagenow >= 2){
				$attach['url'] = str_replace('{%d}', $pagenow, $pageurl);
			}else{
				$attach['url'] = str_replace('{%d}', 1, $firsturl);
			}
			if(max(1, $pagenow - 1) == 1){
				$attach['prevurl'] = str_replace('{%d}', 1, $firsturl);
			}else{
				$attach['prevurl'] = str_replace('{%d}', max(1, $pagenow -1), $pageurl);
			}
			if($count == $pagenow){
				$attach['nexturl'] = $this->nextThreadUrl();
			}else{
				$attach['nexturl'] = str_replace('{%d}', min($count, $pagenow + 1), $pageurl);
			}
			$data = $attach;
		}
	
		return $data;
	}
	
	protected function getAttachList($tid, $module = 'article', $image = 1, $chanid = 0, $domain = '')
	{
		$domain = $domain ? $domain : $this->chandomain;
		$chanid = $chanid ? $chanid : $this->chanid;
		$obj = Controller_Preview::getInstance($this->request);
		$obj->attachurl = $this->attachurl;
		return $obj->getAttachList($tid, $module, $image, $chanid, $domain);
	}
}
?>