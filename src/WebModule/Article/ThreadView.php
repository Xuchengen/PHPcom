<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : ThreadView.php  2012-8-6
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Article_ThreadView extends Controller_ThreadView
{
	protected $pageCount = 0;

	public function loadActionIndex()
	{
		if($tid = intval($this->request->query('id'))){
			$tid = DB::result_first("SELECT tid FROM " . DB::table('article_thread') . " WHERE articleid='$tid'");
		}
		$this->page = max(1, intval($this->request->query('page', $this->request->getQuery('page'))));
		$thread = $this->loadThreadView($tid);
		$tid = $this->tid;
		$channel = phpcom::$G['cache']['channel'];
		$sql = "SELECT t.*,c.* FROM " . DB::table('article_thread') . " t
				LEFT JOIN " . DB::table('article_content', $this->tableindex) . " c USING(tid) WHERE t.tid='$tid'";
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
			$result['content'] = bbcode::parser_attach($attachids, $result['content'], phpcom::$G['cache']['channel']['imagemode'], 'article', $thread['title']);
		}
		if (strpos($result['content'], '[/download]') !== FALSE) {
			$result['content'] = preg_replace_callback("#\[download(=([^\]]+))?\]([0-9,\s]+?)\[\/download\]#is", array($this, 'parserContentDownload'), $result['content']);
		}
		if (strpos($result['content'], '[/thread]') !== FALSE) {
			$result['content'] = preg_replace_callback("#\[thread(=([^\]]+))?\]([0-9,\s]+?)\[\/thread\]#is", array($this, 'parserContentThread'), $result['content']);
		}
		$result['from'] = $result['trackback'] ? "<a href=\"{$result['trackback']}\" target=\"_blank\">{$result['source']}</a>" : $result['source'];
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

		$urlargs = array('chanid' => $this->chanid, 'catdir' => $thread['codename'], 'tid' => $thread['tid'],
				'date' => $thread['dateline'], 'catid' => $thread['catid'], 'page' => $this->page);

		$urlargs['prefix'] = empty($thread['prefix']) ? '' : trim($thread['prefix']);
		$urlargs['name'] = empty($thread['htmlname']) ? $tid : trim($thread['htmlname']);
		$pageurl = $htmlfile = geturl('threadview', $urlargs, $this->chandomain);

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
		$urlargs['page'] = 1;
		$result['commenturl'] = geturl('comment', $urlargs, $this->domain);
		$result['previewurl'] = $pageurl;
		if($thread['attached'] == 2){
			$result['previewurl'] = geturl('preview', array(
					'chanid' => $this->chanid,
					'catdir' => $thread['codename'],
					'tid' => $tid,
					'page' => 1
			), $this->chandomain);
		}
		$result['content'] = bbcode::insertPagebreak($result['content'], $result['pagesize']);
		$contents = $this->pageContents($result['content'], $pageurl);
		$content = $contents['content'];
		$this->pageCount = $count = $contents['count'];
		$showpage = $contents['showpage'];
		if($this->page > 1){
			$result['url'] = str_replace('{%d}', $this->page , $contents['pageurl']);
			$htmlfile = str_replace('{%d}', $this->page , substr($contents['pageurl'], strlen($this->chandomain)));
		}else{
			$firsturl = str_replace('{%d}', 1 , $contents['firsturl']);
			$result['url'] = $firsturl;
			$htmlfile = substr($firsturl, strlen($this->chandomain));
		}
		$this->htmlFile = $htmlfile;
		$currenturl = $this->chandomain . $htmlfile;
		$this->checkRequestUri($currenturl);
		@extract($result + $thread, EXTR_SKIP);

		$tplname = 'article/threadview';
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

	public function highlightWords($sString, $aWords) {
		if (!is_array($aWords) || empty($aWords) || !is_string($sString)) {
			return FALSE;
		}

		$sWords = implode('|', $aWords);
		return preg_replace('@\b(' . $sWords . ')\b@si', '<strong style="background-color:yellow">$1</strong>', $sString);
	}

	protected function pageContents($content, $pageurl = '')
	{
		$content = str_replace(array('<!-- pagebreak -->', '[page_break]'), '[pagebreak]', $content);
		if(strpos($content, '[pagebreak]') !== false){
			$content = preg_replace("/<(p|div)(\s+[^>]*)?>\s*\[pagebreak\]\s*<\/(p|div)>/i", '[pagebreak]', $content);
			$content = preg_replace("/\[pagebreak\]\s*<\/(p|div)>/i", "</\\1>\n[pagebreak]", $content);
			$content = preg_replace("/<(p|div)(\s+[^>]*)?>\s*\[pagebreak\]/i", "[pagebreak]<\\1\\2>", $content);

			$arrContent = explode('[pagebreak]', $content);
			$count = count($arrContent);
			$pagenow = max(1, min($count, $this->page));
			$content = $arrContent[$pagenow - 1];
			$divBeginCount = substr_count($content, '<div');
			$divEndCount = substr_count($content, '</div>');
			if($divBeginCount != $divEndCount){
				if($divBeginCount > $divEndCount){
					$content .= str_pad('', 6 * ($divBeginCount - $divEndCount), '</div>', STR_PAD_LEFT);
				}else{
					$content = str_pad('', 5 * ($divEndCount - $divBeginCount), '<div>', STR_PAD_LEFT) . $content;
				}
			}
			$tagBeginCount = substr_count(preg_replace("/<p\s+([^>]*)>/i", "<p>", $content), '<p>');
			$tagEndCount = substr_count($content, '</p>');
			if($tagBeginCount != $tagEndCount){
				if($tagBeginCount > $tagEndCount){
					$content .= str_pad('', 4 * ($tagBeginCount - $tagEndCount), '</p>', STR_PAD_LEFT);
				}else{
					$content = str_pad('', 3 * ($tagEndCount - $tagBeginCount), '<p>', STR_PAD_LEFT) . $content;
				}
			}
			if(($divBeginCount + $divEndCount + $tagBeginCount + $tagEndCount) == 0){
				$content = "<p>$content</p>";
			}
			$content = preg_replace("/<p(\s+[^>]*)?>\s*<br\s*\/>/i", "<p\\1>", $content);
			$firsturl = $this->formatPageUrl($pageurl);
			$showpage = $this->paging($pagenow, $count, 1, $count, $pageurl, 10, 0, 0, $firsturl);
			return array('content' => $content, 'count' => $count, 'pagenow' => 0, 'showpage' => $showpage, 'pageurl' => $pageurl, 'firsturl' => $firsturl);
		}else{
			$content = str_replace('[pagebreak]', '', $content);
			return array('content' => $content, 'count' => 0, 'pagenow' => 0, 'showpage' => '', 'pageurl' => $pageurl, 'firsturl' => $pageurl);
		}
	}

}
?>