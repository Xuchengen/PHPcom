<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : ThreadView.php  2012-8-6
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Soft_ThreadView extends Controller_ThreadView
{
	public function loadActionIndex()
	{
		if($tid = intval($this->request->query('id'))){
			$tid = DB::result_first("SELECT tid FROM " . DB::table('soft_thread') . " WHERE softid='$tid'");
		}
		$thread = $this->loadThreadView($tid);
		$tid = $this->tid;
		$chanid = $this->chanid;
		
		$sql = "SELECT t.*,c.* FROM " . DB::table('soft_thread') . " t
				LEFT JOIN " . DB::table('soft_content', $this->tableindex) . " c USING(tid) WHERE t.tid='$tid'";
		if(!$result = DB::fetch_first($sql)){
			$this->pageNotFound();
		}
		if ($result['summary']) {
			$this->description = htmlcharsencode(trim($result['summary']));
		}
		if ($result['keyword']) {
			$this->keyword = strip_tags(trim($result['keyword']));
		}
		if(!empty($result['homepage'])){
			$result['homepage'] = checkurlhttp($result['homepage']);
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
		$pollid = $checkbox = $choices = $totalvoters = 0;
		$pollurl = '';
		if ($thread['isvote']) {
			$sql = "SELECT * FROM " . DB::table('pollvotes') . " WHERE tid='$tid'";
			if($pollrow = DB::fetch_first($sql)){
				$pollid = $pollrow['pollid'];
				$polltitle = $pollrow['polltitle'];
				$checkbox = $pollrow['checkbox'];
				$choices = $pollrow['choices'];
				$totalvoters = $pollrow['voters'];
				if ($pollrow['checkbox']) {
					$polltype = lang('common', 'checkbox');
				} else {
					$polltype = lang('common', 'radio');
				}
				if ($pollrow['expiration']) {
					$expiration = fmdate($pollrow['expiration']);
					$expires = lang('common', 'pollexpiration') . $expiration;
				} else {
					$expiration = $pollrow['expiration'];
					$expires = '';
				}
				$pollurl = geturl('vote', array('pid' => $pollid, 'tid' => $tid, 'vid' => $pollid), $this->domain, 'main');
			}else{
				$thread['isvote'] = $thread['polled'] = 0;
			}
		}

		$urlargs = array('chanid' => $this->chanid, 'catdir' => $thread['codename'], 'tid' => $result['tid'],
				'date' => $thread['dateline'], 'catid' => $result['catid'], 'page' => 1);

		$urlargs['prefix'] = empty($thread['prefix']) ? '' : trim($thread['prefix']);
		$this->baseUrlArgs = $urlargs;
		$this->baseUrlArgs['name'] = $thread['codename'];
		$urlargs['name'] = empty($thread['htmlname']) ? $tid : trim($thread['htmlname']);
		$this->htmlFile = $htmlfile = geturl('threadview', $urlargs);
		$currenturl = $this->chandomain . $htmlfile;
		$this->checkRequestUri($currenturl);

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
		$result['downurl'] =  geturl('down', $urlargs, $this->chandomain);
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
		$result['md5sums'] = $result['shasums'] = trim($result['checksum']);
		@extract($result + $thread, EXTR_SKIP);

		$tplname = 'soft/threadview';
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