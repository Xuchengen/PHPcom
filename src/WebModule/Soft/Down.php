<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Down.php  2012-8-8
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Soft_Down extends Controller_ThreadView
{
	protected $downid = 0;
	protected $servid = 0;
	protected $downmode = 0;
	protected $servname = '';
	protected $icons = '';
	protected $isDownload = false;
	
	public function loadActionIndex()
	{
		$this->servid = intval($this->request->query('sid', $this->request->getQuery(1)));
		$this->downid = intval($this->request->query('id', $this->request->getQuery(2)));
		if($this->downid > 0){
			return $this->beginDownload();
		}else{
			return $this->loadSoftDownload();
		}
	}
	
	protected function beginDownload()
	{
		$servid = $this->servid;
		$downid = $this->downid;
		$tid = intval($this->request->query('tid', $this->request->getQuery(0)));
		$tid < 1 && exit(header("HTTP/1.1 403 Forbidden"));
		if(!$threads = DB::fetch_first("SELECT * FROM " . DB::table('threads') . " WHERE status='1' AND tid='$tid'")){
			exit(header("HTTP/1.1 403 Forbidden"));
		}
		if(!$download = DB::fetch_first("SELECT * FROM " . DB::table('soft_download') . " WHERE tid='$tid' AND id='$this->downid'")){
			exit(header("HTTP/1.1 403 Forbidden"));
		}
		$downurl = $download['downurl'];
		if($servid){
			if($downserv = DB::fetch_first("SELECT * FROM " . DB::table('downserver') . " WHERE servid='$this->servid'")){
				if(!$downserv['redirect']){
					$parseurl = parse_url($downurl);
					if(isset($parseurl['host'])){
						$downserv['servurl'] = $downurl;
					}else{
						$downserv['servurl'] .= $downurl;
					}
				}
				$downurl = $downserv['servurl'];
				$servname = $downserv['servname'];
				if($downserv['expires'] && $downserv['expires'] < $this->todaytime){
					showmessage('download_expires', NULL);
				}
				if(!$this->checkGroupLevel($downserv['groupid'])){
					showmessage('usergroup_level_denied', NULL);
				}
			}else{
				exit(header("HTTP/1.1 403 Forbidden"));
			}
		}
		if(!phpcom::$G['group']['allowdown']){
			showmessage('usergroup_download_denied', NULL);
		}
		
		if(in_array(intval(phpcom::$setting['statclosed']), array(0, 2), true)){
			Counts::getInstance()->thread($tid);
		}
		@header('HTTP/1.1 301 Moved Permanently');
		@header("location: $downurl");
		return 0;
	}
	
	protected function loadSoftDownload()
	{
		$thread = $this->loadThreadView();
		$tid = $this->tid;
		$servid = $this->servid;
		$chanid = $this->chanid;
		$catid = $this->catid;
		
		$sql = "SELECT t.*,c.* FROM " . DB::table('soft_thread') . " t LEFT JOIN " . DB::table('soft_content', $this->tableindex) . " c USING(tid) WHERE t.tid='$tid'";
		if(!$result = DB::fetch_first($sql)){
			$this->pageNotFound();
		}
		if ($result['summary']) {
			$this->description = htmlcharsencode(trim($result['summary']));
		}
		if ($result['keyword']) {
			$this->keyword = strip_tags(trim($result['keyword']));
		}
		$downserv = array('servid' => 0, 'servname' => '', 'icons' => '', 'parentid' => 0, 'child' => 0, 'downmode' => 0);
		if($servid && ($downserv = DB::fetch_first("SELECT servid,servname,icons,parentid,child,downmode FROM " . DB::table('downserver') . " WHERE servid='$servid'"))){
			$this->servname = trim($downserv['servname']);
			$this->icons = trim($downserv['icons']);
			$this->downmode = trim($downserv['downmode']);
		}
		
		$attachids = array();
		if ($thread['attached']) {
			if (preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $result['content'], $matchaids)) {
				$attachids = $matchaids[1];
			}
		}
		$result['content'] = bbcode::bbcode2html($result['content']);
		if ($attachids) {
			$result['content'] = bbcode::parser_attach($attachids, $result['content'], phpcom::$G['cache']['channel']['imagemode'], 'soft', $thread['title']);
		}
		if (strpos($result['content'], '[/download]') !== FALSE) {
			$result['content'] = preg_replace_callback("#\[download(=([^\]]+))?\]([0-9,\s]+?)\[\/download\]#is", array($this, 'parserContentDownload'), $result['content']);
		}
		if (strpos($result['content'], '[/thread]') !== FALSE) {
			$result['content'] = preg_replace_callback("#\[thread(=([^\]]+))?\]([0-9,\s]+?)\[\/thread\]#is", array($this, 'parserContentThread'), $result['content']);
		}
		$testlist = $this->getSoftTest($result['testsoft']);
		$result['softsize'] = intval($result['softsize']) * 1024;
		$size = formatbytes($result['softsize']);
		$developers = empty($result['company']) ? 'Home Page' : trim($result['company']);
		if (!empty($result['homepage'])) {
			$developers = "<a target=\"_blank\" href=\"{$result['homepage']}\">$developers</a>";
		}
		
		$urlargs = array('chanid' => $this->chanid, 'catdir' => $thread['codename'], 'tid' => $thread['tid'],
				'sid' => $servid, 'date' => $thread['dateline'], 'catid' => $thread['catid'], 'page' => 1);
		
		$urlargs['prefix'] = empty($thread['prefix']) ? '' : trim($thread['prefix']);
		$this->baseUrlArgs = $urlargs;
		$this->baseUrlArgs = $thread['codename'];
		$urlargs['name'] = empty($thread['htmlname']) ? $tid : trim($thread['htmlname']);
		$this->htmlFile = $htmlfile = geturl('down', $urlargs);
		$currenturl = $this->chandomain . $htmlfile;
		$this->checkRequestUri($currenturl);
		$result['url'] = geturl('threadview', $urlargs, $this->chandomain);
		if (isset($result['caturl']) && $result['caturl']) {
			$result['curl'] = $result['caturl'];
		}else{
			$urlargs['name'] = $thread['codename'];
			if(!empty($thread['prefixurl']) && $thread['basic']){
				$result['curl'] = $thread['prefixurl'];
			}else{
				$result['curl'] = geturl($thread['basic'] ? 'category' : 'threadlist', $urlargs, $this->chandomain);
			}
		}
		$urlargs['tid'] = $result['tid'];
		unset($urlargs['chanid']);
		$result['commenturl'] = geturl('comment', $urlargs, $this->domain);
		$result['previewurl'] = geturl('preview', array(
				'chanid' => $this->chanid,
				'catdir' => $thread['codename'],
				'tid' => $tid,
				'page' => 1
		), $this->chandomain);
		$result['md5sums'] = $result['shasums'] = trim($result['checksum']);
		@extract($result + $thread, EXTR_SKIP);

		$tplname = 'soft/down';
		if ($this->templateName) {
			$this->templateName = substr($this->templateName, 0, -4) . 'down';
			$tplname = checktplname($tplname, $this->templateName);
		} else {
			$tplname = checktplname($tplname, $this->chanid);
		}
		include template($tplname);
		return 1;
	}
}
?>