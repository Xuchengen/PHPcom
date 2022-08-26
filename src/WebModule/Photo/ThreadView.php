<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : ThreadView.php  2012-8-15
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Photo_ThreadView extends Controller_ThreadView
{
	public function loadActionIndex()
	{
		if($tid = intval($this->request->query('id'))){
			$tid = DB::result_first("SELECT tid FROM " . DB::table('photo_thread') . " WHERE photoid='$tid'");
		}
		$this->page = max(1, intval($this->request->query('page', $this->request->getQuery('page'))));
		$thread = $this->loadThreadView($tid);
		$tid = $this->tid;
		$channel = &phpcom::$G['cache']['channel'];
		$sql = "SELECT t.*,c.* FROM " . DB::table('photo_thread') . " t
				LEFT JOIN " . DB::table('photo_content', $this->tableindex) . " c USING(tid) WHERE t.tid='$tid'";
		if(!$result = DB::fetch_first($sql)){
			$this->pageNotFound();
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
			$result['content'] = bbcode::parser_attach($attachids, $result['content'], phpcom::$G['cache']['channel']['imagemode'], 'photo', $thread['title']);
		}
		if (strpos($result['content'], '[/download]') !== FALSE) {
			$result['content'] = preg_replace_callback("#\[download(=([^\]]+))?\]([0-9,\s]+?)\[\/download\]#is", array($this, 'parserContentDownload'), $result['content']);
		}
		if (strpos($result['content'], '[/thread]') !== FALSE) {
			$result['content'] = preg_replace_callback("#\[thread(=([^\]]+))?\]([0-9,\s]+?)\[\/thread\]#is", array($this, 'parserContentThread'), $result['content']);
		}
		$result['from'] = $result['trackback'] ? "<a href=\"{$result['trackback']}\" target=\"_blank\">{$result['source']}</a>" : $result['source'];

		$urlargs = array('chanid' => $this->chanid, 'catdir' => $thread['codename'], 'tid' => $thread['tid'],
				'date' => $thread['dateline'], 'catid' => $thread['catid'], 'page' => $this->page);

		$urlargs['prefix'] = empty($thread['prefix']) ? '' : trim($thread['prefix']);
		$urlargs['name'] = empty($thread['htmlname']) ? $tid : trim($thread['htmlname']);
		
		$datalist = array();
		$count = 0;
		$imagemode = 0;
		$pagecount = $pagenow = 1;
		$showpage = '';
		$pageurl = $htmlfile = geturl('threadview', $urlargs, $this->chandomain);
		$firsturl = $this->formatPageUrl($pageurl);
		if(empty(phpcom::$G['cache']['channel']['imagemode'])){
			$datalist[0] = $this->getAttachImage($tid, $this->chanid, $this->chandomain, $pageurl);
			if(isset($datalist[0]['count'])){
				$count = $datalist[0]['count'];
				$pagecount = $datalist[0]['pagecount'];
				$pagenow = $datalist[0]['pagenow'];
			}
		}else{
			$imagemode = phpcom::$G['cache']['channel']['imagemode'];
			$datalist = $this->getAttachList($tid, 'photo', 1, $this->chanid, $this->chandomain);
			$count = count($datalist);
		}
		$this->pageCount = $pagecount;
		if($pagenow > 1){
			$result['url'] = str_replace('{%d}', $this->page , $firsturl);
			$htmlfile = str_replace('{%d}', $this->page , substr($firsturl, strlen($this->chandomain)));
		}else{
			$firsturl = str_replace('{%d}', 1 , $firsturl);
			$result['url'] = $firsturl;
			$htmlfile = substr($firsturl, strlen($this->chandomain));
		}
		$this->htmlFile = $htmlfile;
		$currenturl = $this->chandomain . $htmlfile;
		$this->checkRequestUri($currenturl);
		$showpage = $this->paging($pagenow, $pagecount, 1, $count, $pageurl, 10, 0, 0, $firsturl);
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
		$result['previewurl'] = $currenturl;
		if($thread['attached'] == 2){
			$result['previewurl'] = geturl('preview', array(
					'chanid' => $this->chanid,
					'catdir' => $thread['codename'],
					'tid' => $tid,
					'page' => 1
			), $this->chandomain);
		}
		
		@extract($result + $thread, EXTR_SKIP);
		$tplname = 'photo/threadview';
		if ($this->templateName) {
			$tplname = checktplname($tplname, $this->templateName);
		} else {
			$tplname = checktplname($tplname, $this->chanid);
		}
		include template($tplname);
		return 1;
	}
	
	public function writeToHtml($content = '')
	{
		parent::writeToHtml($content);
		if($this->pageCount > 1 && $this->page < 2){
			$key = md5(date('YmdH') . phpcom::$config['security']['key']);
			for ($p = 2; $p<=$this->pageCount; $p++){
				$url = $this->domain . "apps/html.php?module=article&action=view&tid={$this->tid}&page=$p&key=$key";
				echo "document.writeln('<script type=\"text\\/javascript\" src=\"$url\"><\\/script>');\r\n";
			}
			}
		}
}
?>