<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : ThreadView.php  2012-8-15
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Video_ThreadView extends Controller_VideoPlay
{
	public function loadActionIndex()
	{
		if($tid = intval($this->request->query('id'))){
			$tid = DB::result_first("SELECT tid FROM " . DB::table('video_thread') . " WHERE videoid='$tid'");
		}
		$thread = $this->loadThreadView();
		$tid = $this->tid;
		$chanid = $this->chanid;
		$sql = "SELECT t.*,c.* FROM " . DB::table('video_thread') . " t
				LEFT JOIN " . DB::table('video_content', $this->tableindex) . " c USING(tid) WHERE t.tid='$tid'";
		if(!$result = DB::fetch_first($sql)){
			$this->pageNotFound();
		}
		if ($result['summary']) {
			$this->description = htmlcharsencode(trim($result['summary']));
		}
		if ($result['keyword']) {
			$this->keyword = strip_tags(trim($result['keyword']));
		}
		$result['language'] = $result['dialogue'];
		$version = $result['version'];
		$this->title = trim($this->title . " $version");
		$attachids = array();
		if ($thread['attached']) {
			if (preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $result['content'], $matchaids)) {
				$attachids = $matchaids[1];
			}
		}
		$qualitystr = $this->getVideoQuality($result['quality']);

		$result['content'] = bbcode::bbcode2html($result['content']);
		if ($attachids) {
			$result['content'] = bbcode::parser_attach($attachids, $result['content'], phpcom::$G['cache']['channel']['imagemode'], 'video', $thread['title']);
		}
		if (strpos($result['content'], '[/download]') !== FALSE) {
			$result['content'] = preg_replace_callback("#\[download(=([^\]]+))?\]([0-9,\s]+?)\[\/download\]#is", array($this, 'parserContentDownload'), $result['content']);
		}
		if (strpos($result['content'], '[/thread]') !== FALSE) {
			$result['content'] = preg_replace_callback("#\[thread(=([^\]]+))?\]([0-9,\s]+?)\[\/thread\]#is", array($this, 'parserContentThread'), $result['content']);
		}
		$urlargs = array('chanid' => $this->chanid, 'catdir' => $thread['codename'], 'tid' => $thread['tid'],
				'date' => $thread['dateline'], 'catid' => $thread['catid'], 'page' => 1);
		$chandomain = $this->chandomain;
		$urlargs['prefix'] = empty($thread['prefix']) ? '' : trim($thread['prefix']);
		$this->baseUrlArgs = $urlargs;
		$this->baseUrlArgs['name'] = $thread['codename'];
		$urlargs['name'] = empty($thread['htmlname']) ? $tid : trim($thread['htmlname']);
		$this->htmlFile = $htmlfile = geturl('threadview', $urlargs);
		$currenturl = $chandomain . $htmlfile;
		$this->checkRequestUri($currenturl);
		if (isset($result['caturl']) && $result['caturl']) {
			$result['curl'] = $result['caturl'];
		}else{
			$urlargs['name'] = $thread['codename'];
			if(!empty($thread['prefixurl']) && $thread['basic']){
				$result['curl'] = $thread['prefixurl'];
			}else{
				$result['curl'] = geturl($thread['basic'] ? 'category' : 'threadlist', $urlargs, $chandomain);
			}
		}
		$urlargs['tid'] = $result['tid'];
		unset($urlargs['chanid']);
		$result['commenturl'] = geturl('comment', $urlargs, $this->domain);
		$result['playurl'] = geturl('play', array(
				'chanid' => $this->chanid,
				'catdir' => $thread['codename'],
				'name' => $thread['codename'],
				'id' => 0,
				'page' => 1,
				'tid' => $tid
		), $chandomain);
		$result['previewurl'] = $result['playurl'];
		if($thread['attached'] == 2){
			$result['previewurl'] = geturl('preview', array(
					'chanid' => $this->chanid,
					'catdir' => $thread['codename'],
					'tid' => $tid,
					'page' => 1
			), $chandomain);
		}

		$videoaddress = $this->fetchAllVideoAddress($tid, $chandomain, $thread['codename']);

		@extract($result + $thread, EXTR_SKIP);

		$tplname = 'video/threadview';
		if ($this->templateName) {
			$tplname = checktplname($tplname, $this->templateName);
		} else {
			$tplname = checktplname($tplname, $this->chanid);
		}
		include template($tplname);
		return 1;
	}
}
?>