<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : ThreadView.php  2013-11-7
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Special_ThreadView extends Controller_ThreadView
{
	
	public function loadActionIndex()
	{
		$thread = $this->loadThreadView(0);
		$tid = $this->tid;
		$channel = phpcom::$G['cache']['channel'];
		$sql = "SELECT t.*,c.* FROM " . DB::table('special_thread') . " t
				LEFT JOIN " . DB::table('special_content', $this->tableindex) . " c USING(tid) WHERE t.tid='$tid'";
		if(!$result = DB::fetch_first($sql)){
			$this->pageNotFound();
		}
		$domain = $this->chandomain;
		if(!empty($result['domain'])){
			$domain = trim($result['domain'], '/ ') . '/';
		}
		$tidlists = array(0);
		$tplname = trim($result['tplname']);
		if($tidlist = trim($result['tidlist'])){
			$tidlists = explode(',', $tidlist);
		}
		
		if (!empty($result['subject'])) {
			$this->title = strip_tags(trim($result['subject']));
		}
		if ($result['summary']) {
			$this->description = htmlcharsencode(trim($result['summary']));
		}
		if ($result['keyword']) {
			$this->keyword = strip_tags(trim($result['keyword']));
		}
		$attachids = array();
		if ($thread['attached']) {
			if (preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $result['content'], $matchaids)) {
				$attachids = $matchaids[1];
			}
		}
		$result['content'] = bbcode::bbcode2html($result['content']);
		if ($attachids) {
			$result['content'] = bbcode::parser_attach($attachids, $result['content'], phpcom::$G['cache']['channel']['imagemode'], 'special');
		}
		if (strpos($result['content'], '[/download]') !== FALSE) {
			$result['content'] = preg_replace_callback("#\[download(=([^\]]+))?\]([0-9,\s]+?)\[\/download\]#is", array($this, 'parserContentDownload'), $result['content']);
		}
		if (strpos($result['content'], '[/thread]') !== FALSE) {
			$result['content'] = preg_replace_callback("#\[thread(=([^\]]+))?\]([0-9,\s]+?)\[\/thread\]#is", array($this, 'parserContentThread'), $result['content']);
		}
		
		$urlargs = array('chanid' => $this->chanid, 'catdir' => $thread['codename'], 'tid' => $result['tid'],
				'date' => $thread['dateline'], 'catid' => $result['catid'], 'page' => 1);
		
		$urlargs['prefix'] = empty($thread['prefix']) ? '' : trim($thread['prefix']);
		$urlargs['name'] = empty($thread['htmlname']) ? $result['tid'] : trim($thread['htmlname']);
		$this->baseUrlArgs = $urlargs;
		
		$this->htmlFile = $htmlfile = geturl('threadview', $urlargs);
		$homeurl = $currenturl = $this->chandomain . $htmlfile;
		if(empty($result['domain'])){
			$this->checkRequestUri($currenturl);
		}else{
			$homeurl = $currenturl = $domain;
		}
		
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
		$urlargs['sid'] = 0;
		unset($urlargs['chanid']);
		$result['commenturl'] = geturl('comment', $urlargs, $this->domain);
		$result['previewurl'] = '#';
		if($thread['attached'] == 2){
			$result['previewurl'] = geturl('preview', array(
					'chanid' => $this->chanid,
					'catdir' => $thread['codename'],
					'tid' => $tid,
					'page' => 1
			), $this->chandomain);
		}
		$defurlargs = array(
				'domain' => $this->chandomain,
				'tid' => $tid,
				'catdir' => $thread['codename'],
				'name' => empty($thread['htmlname']) ? $result['tid'] : trim($thread['htmlname']),
				'prefix' => $thread['prefix'],
				'page' => 1
		);
		@extract($result + $thread, EXTR_SKIP);
		$tplnames = array();
		if ($tplname) {
			$tplname = 'special/' . trim($tplname, './\\ ');
			$tplnames[] = $tplname . "_view";
			$tplnames[] = $tplname;
		}
		$tplnames[] = $this->templateName;
		$tplname = parser_tplname('special/threadview', $tplnames);
		
		include template($tplname);
		return 1;
	}
}
?>