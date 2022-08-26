<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Topiclist.php  2013-11-17
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Special_Topiclist extends Controller_ThreadView
{
	public function loadActionIndex()
	{
		$thread = $this->loadThreadView(0);
		$tid = $this->tid;
		$chanid = $this->chanid;
		$datalist = array();
		$showpage = '';
		$type = 0;
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
		
		$urlargs = array('chanid' => $this->chanid, 'catdir' => $thread['codename'], 'tid' => $result['tid'],
				'date' => $thread['dateline'], 'catid' => $result['catid'], 'page' => 1);
		$urlargs['prefix'] = empty($thread['prefix']) ? '' : trim($thread['prefix']);
		$urlargs['name'] = empty($thread['htmlname']) ? $result['tid'] : trim($thread['htmlname']);
		$classes = $this->getSpecialClassData($tid, $result['specname'], $urlargs);
		$classid = $classes['classid'];
		$tplname = trim($result['tplname']);
		$this->baseUrlArgs = $urlargs;
		
		$currenturl = $classes['currenturl'];
		if(empty($result['domain'])){
			$homeurl = geturl('threadview', $urlargs, $this->chandomain);
			$this->checkRequestUri($currenturl);
		}else{
			$homeurl = $domain;
		}
		if(empty($classes['about']) && !empty($result['summary'])){
			$this->description = htmlcharsencode(trim($result['summary']));
		}
		$tidlists = array(0);
		if($tidlist = trim($result['tidlist'])){
			$tidlists = explode(',', $tidlist);
		}
		$name = $title = $classes['name'];
		$alias = $classes['alias'];
		$defurlargs = array(
				'domain' => $this->chandomain,
				'tid' => $tid,
				'catdir' => $thread['codename'],
				'name' => empty($thread['htmlname']) ? $result['tid'] : trim($thread['htmlname']),
				'prefix' => $thread['prefix'],
				'page' => 1
		);
		
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
		unset($urlargs['chanid']);
		$result['commenturl'] = geturl('comment', $urlargs, $this->domain);
		$pageurl = $classes['pageurl'];
		$firsturl = $classes['firsturl'];
		$pagenum = intval(phpcom::$G['channel'][$chanid]['pagenum']);
		$pageinput = phpcom::$G['channel'][$chanid]['pageinput'];
		$pagestats = phpcom::$G['channel'][$chanid]['pagestats'];
		$pagesize = $classes['pagesize'];
		$count = $classes['count'];
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($this->page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$pagesql = DB::buildlimit("INNER JOIN (SELECT tid FROM " . DB::table('special_data') . " WHERE classid='$classid' ORDER BY dateline DESC) AS t2 USING(tid)", $pagesize, $pagestart);
		$sql = "SELECT t.*,c.depth,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,c.target,c.color,
			ti.attachment,ti.remote,ti.thumb,ti.preview,ti.attachimg,f.voteup,f.votedown,f.voters,f.totalscore,f.credits
			FROM " . DB::table('threads') . " t
			LEFT JOIN " . DB::table('category') . " c ON c.catid=t.catid
			LEFT JOIN " . DB::table('thread_image') . " ti USING(tid)
			LEFT JOIN " . DB::table('thread_field') . " f USING(tid)
			$pagesql";
		$query = DB::query($sql);
		$index = 0;
		while ($row = DB::fetch_array($query)) {
			$row['index'] = $index;
			$row['alt'] = $index % 2 == 0 ? 2 : 1;
			$this->processThreadListData($row);
			$datalist[] = $row;
		}
		if($pagecount >= 2){
			$showpage = $this->paging($pagenow, $pagecount, $pagesize, $count, $pageurl, $pagenum, $pagestats, $pageinput, $firsturl);
		}
		@extract($result + $thread, EXTR_SKIP);
		$tplnames = array();
		if ($tplname) {
			$tplname = 'special/' . trim($tplname, './\\ ');
			$tplnames[] = $tplname . "_class";
		}
		$tplnames[] = str_replace("_view", "_class", $this->templateName);
		$tplname = parser_tplname('special/topiclist', $tplnames);
		include template($tplname);
		return 1;
	}
	
	public function getSpecialClassData($tid, $specname, $urlargs = array())
	{
		$aliasname = stripstring($this->request->query('name', $this->request->getQuery(1)));
		$this->page = max(1, intval($this->request->query('page', $this->request->getQuery('page'))));
		$sql = "SELECT * FROM " . DB::table('special_class') . " WHERE ";
		if(is_numeric($aliasname)){
			$classid = intval($this->request->query('classid', $this->request->getQuery(1)));
			$sql .= "classid='$classid' LIMIT 1";
		}elseif(!empty($aliasname)){
			$sql .= "tid='$tid' AND alias='$aliasname' LIMIT 1";
		}else{
			$classid = intval($this->request->query('classid', $this->request->getQuery(1)));
			$sql .= "classid='$classid' LIMIT 1";
		}
		if(!$classes = DB::fetch_first($sql)){
			$this->pageNotFound();
		}
		if(!empty($classes['about']) && parse_url($classes['about'], PHP_URL_SCHEME)){
			@header('HTTP/1.1 301 Moved Permanently');
			$_SERVER["REDIRECT_STATUS"] = 301;
			exit(header("location: " . trim($classes['about'])));
		}
		$classes['name'] = strip_tags(trim($classes['name']));
		$classes['about'] = strip_tags(trim($classes['about']));
		if(empty($classes['title'])){
			$this->title = $specname . $classes['name'];
		}else{
			$this->title = $classes['title'];
		}
		if(!empty($classes['about'])){
			$this->description = htmlcharsencode($classes['about']);
		}
		$urlargs['page'] = '{%d}';
		$urlargs['classid'] = $classes['classid'];
		$urlargs['alias'] = $classes['alias'];
		$urlargs['byname'] = $classes['alias'];
		
		$pageurl = geturl('topiclist', $urlargs, $this->chandomain);
		$firsturl = $this->formatPageUrl($pageurl);
		$classes['pageurl'] = $pageurl;
		$classes['firsturl'] = $firsturl;
		if($this->page > 1){
			$htmlfile = str_replace('{%d}', $this->page, substr($pageurl, strlen($this->chandomain)));
		}else{
			$firsturl = str_replace('{%d}', 1, $firsturl);
			$htmlfile = substr($firsturl, strlen($this->chandomain));
		}
		$this->htmlFile = $htmlfile;
		$classes['pagesize'] = $classes['pagesize'] > 0 ? $classes['pagesize'] : 50;
		$classes['currenturl'] = $this->chandomain . $htmlfile;
		$classes['count'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('special_data') . " WHERE classid='{$classes['classid']}'");
		return $classes;
	}
	
	protected function processThreadListData(&$row)
	{
		$row['highlight'] = $this->threadHighlight($row['highlight']);
		$row['colors'] = $row['highlight'];
		$row['color'] = empty($row['color']) ? '' : ' style="color: ' . $row['color'] . '"';
		$urlargs = array('chanid' => $row['chanid'], 'catdir' => $row['codename'], 'tid' => $row['tid'],
				'catid' => $row['catid'], 'page' => 1, 'date' => $row['dateline']);
		$urlargs['prefix'] = empty($row['prefix']) ? '' : trim($row['prefix']);
		$channel = phpcom::$G['channel'][$row['chanid']];
		if(!empty($row['prefixurl'])){
			$domain = $row['prefixurl'] . '/';
		}elseif(!empty($channel['domain'])){
			$domain = $channel['domain'] . '/';
		}else{
			$domain = $this->domain;
		}
		if(empty($row['domain'])){
			$row['domain'] = $domain;
			if (empty($row['url'])) {
				$urlargs['name'] = empty($row['htmlname']) ? $row['tid'] : trim($row['htmlname']);
				$row['url'] = geturl('threadview', $urlargs, $row['domain']);
			}
		}else{
			$row['domain'] = trim($row['domain'], '/ ') . '/';
			if (empty($row['url'])) {
				$row['url'] = $row['domain'];
			}
		}
		
		if (empty($row['caturl'])) {
			$urlargs['name'] = $row['codename'];
			if(!empty($row['prefixurl']) && $row['basic']){
				$row['curl'] = $row['prefixurl'];
			}else{
				$row['curl'] = geturl($row['basic'] ? 'category' : 'threadlist', $urlargs, $domain);
			}
		}else{
			$row['curl'] = $row['caturl'];
		}
		$row['topic'] = "<a href=\"{$row['url']}\"{$row['highlight']}>{$row['title']}</a>";
		$row['istoday'] = $row['dateline'] + $this->timeoffset >= $this->todaytime ? 1 : 0;
		if ($row['istoday']) {
			$row['datestyle'] = 'new';
			$row['date'] = '<em class="new">'. fmdate($row['dateline'], 'd') . '</em>';
		} else {
			$row['datestyle'] = 'old';
			$row['date'] = '<em class="old">'. fmdate($row['dateline'], 'd') . '</em>';
		}
		$row['pixurl'] = $row['url'];
		if(isset($row['attachment']) && $row['image'] == 1){
			$this->processImageRowData($row, $channel['modules']);
			if($channel['thumbmode'] == 1){
				$row['imageurl'] = $row['thumburl'];
			}elseif($channel['thumbmode'] == 2){
				$row['imageurl'] = $row['previewurl'];
			}
		}else{
			$row['image'] = 0;
			$row['thumburl'] = $row['previewurl'] = $row['imageurl'] = $this->domain . 'misc/images/noimage.jpg';
		}
		if(isset($row['attached']) && $row['attached'] == 2){
			$row['pixurl'] = geturl('preview', array(
					'chanid' => $row['chanid'],
					'catdir' => $row['codename'],
					'tid' => $row['tid'],
					'page' => 1
			), $domain);
		}
	}
}
?>