<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : ThreadList.php  2012-8-6
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Controller_ThreadList extends Controller_MainAbstract
{
	protected $templateName;

	public function __construct(Web_HttpRequest $request)
	{
		parent::__construct($request);
		$this->page = max(1, intval($this->request->query('page', $this->request->getQuery('page'))));
		$this->chanid = phpcom::$G['channelid'];
		if($this->chanid && isset(phpcom::$G['channel'][$this->chanid])){
			phpcom::$G['cache']['channel'] = phpcom::$G['channel'][$this->chanid];
			if (!empty(phpcom::$G['cache']['channel']['domain'])) {
				!defined('DOMAIN_ENABLED') && define('DOMAIN_ENABLED', true);
				$this->chandomain = trim(phpcom::$G['cache']['channel']['domain'], ' /') . '/';
			}
		}
	}
	
	protected function getListQueries($keys = array('softtype', 'license', 'softlang'), $primary = 'catid')
	{
		static $queriesnew = null;
		if($queriesnew !== null) return $queriesnew;
		
		
		$queriesnew = array('page' => intval($this->request->query('page', $this->request->getQuery('page'))));
		$queriesnew['type'] = '';
		$queriesnew['query'] = '';
		if($primary){
			$queriesnew[$primary] = $this->request->query($primary, $this->request->getQuery(0));
		}
		if(!empty($keys) && !is_array($keys)){
			$queriesnew[$keys] = $this->request->query($keys, $this->request->getQuery(1));
		}elseif(!empty($keys)){
			foreach($keys as $k){
				if($v = $this->request->getQuery($k, false)){
					$queriesnew[$k] = intval($v);
					$queriesnew['query'] = "-$k-" . intval($v);
					break;
				}
			}
		}
		foreach(array('hot', 'asc', 'desc', 'voteup', 'digest', 'best', 'topline', 'focus', 'top', 'type') as $k){
			if($v = $this->request->getQuery($k)){
				$queriesnew['type'] = $v;
				$queriesnew['query'] .= "-$v";
				break;
			}
		}
		return $queriesnew;
	}
	
	protected function threadCategory($queries = null, $type = null)
	{
		if(empty($queries)){
			$catid = stripstring($this->request->query('catid', $this->request->getQuery(0)));
		}else{
			$catid = is_array($queries) ? $queries['catid'] : intval($queries);
		}
		if(empty($catid)){
			$name = stripstring($this->request->query('name', $this->request->getQuery(0)));
			$condition = phpcom::$G['channelid'] ? "chanid='".intval(phpcom::$G['channelid'])."' AND" : "";
			$sql = "SELECT * FROM " . DB::table('category') . " WHERE $condition codename='$name' LIMIT 1";
		}else{
			$sql = "SELECT * FROM " . DB::table('category') . " WHERE catid='$catid' LIMIT 1";
		}
		if(!$category = DB::fetch_first($sql)){
			$this->pageNotFound();
		}
		$this->chanid = $chanid = $category['chanid'];
		phpcom::$G['channelid'] = $this->chanid;
		phpcom::$G['cache']['channel'] = phpcom::$G['channel'][$this->chanid];
		$modules = phpcom::$G['channel'][$chanid]['modules'];
		if(!empty(phpcom::$G['channel'][$chanid]['domain']) || !empty($category['prefixurl'])){
			!defined('DOMAIN_ENABLED') && define('DOMAIN_ENABLED', true);
		}
		if (!empty($category['prefixurl'])) {
			$this->chandomain = trim($category['prefixurl'], ' /') . '/';
		}elseif (trim(phpcom::$G['cache']['channel']['domain'])) {
			$this->chandomain = trim(phpcom::$G['cache']['channel']['domain'], ' /') . '/';
		}
		$this->channelname = phpcom::$G['cache']['channel']['channelname'];
		if(!isset(phpcom::$G['cache']['category'])){
			phpcom_cache::load('category');
		}
		$this->initialize();
		if(!empty(phpcom::$G['cache']['channel']['sitename'])){
			$this->webname = phpcom::$G['cache']['channel']['sitename'];
		}
		if ($category['keyword']) {
			$this->keyword = htmlcharsencode($category['keyword']);
		} else {
			$this->keyword = phpcom::$G['cache']['channel']['keyword'] ? phpcom::$G['cache']['channel']['keyword'] : phpcom::$setting['keyword'];
		}
		if ($category['description']) {
			$this->description = htmlcharsencode($category['description']);
		} else {
			$this->description = phpcom::$G['cache']['channel']['description'] ? phpcom::$G['cache']['channel']['description'] : phpcom::$setting['description'];
		}
		$this->rootid = $category['rootid'];
		$this->catid = $category['catid'];
		$this->parentid = $category['parentid'];
		if(empty($category['title'])){
			$this->title = '';
			foreach ($this->fetchCategoryNav() as $nav){
				$this->title .= $nav['name'] . ' - ';
			}
			$this->title = trim($this->title, "- ");
		}else{
			$this->title = $category['title'];
		}
		
		if(!empty($category['template'])){
			$this->templateName = $modules . '/' . $category['template'];
		}
		$category['module'] = $modules;
		if(empty($category['pagesize'])){
			$category['pagesize'] = phpcom::$G['cache']['category'][$this->rootid]['pagesize'];
		}
		$category['topmode'] = (empty($category['topmode']) && $category['child']) ? 0 : 1;
		$category['pagesize'] = empty($category['pagesize']) ? intval(phpcom::$G['cache']['channel']['pagesize']) : $category['pagesize'];
		$category['banner'] = empty($category['banner']) ? '' : trim($category['banner']);
		$urlargs = array('chanid' => $this->chanid, 'catdir' => $category['codename'], 'name' => $category['codename'],
				'catid' => $category['catid'], 'page' => '{%d}');
		if(!empty($category['prefix'])){
			$urlargs['prefix'] = trim($category['prefix']);
		}
		$this->baseUrlArgs = $urlargs;
		$this->baseUrlArgs['page'] = 1;
		
		if(isset($queries['query']) && !empty($queries['query'])){
			$urlargs['catid'] .= $queries['query'];
		}
		$pageurl = geturl($category['basic'] ? 'category' : 'threadlist', $urlargs, $this->chandomain);
		
		$firsturl = $this->formatPageUrl($pageurl, trim($category['prefix']));
		$category['pageurl'] = $pageurl;
		$category['firsturl'] = $firsturl;
		if($this->page > 1){
			$htmlfile = str_replace('{%d}', $this->page, substr($pageurl, strlen($this->chandomain)));
		}else{
			$firsturl = str_replace('{%d}', 1, $firsturl);
			$htmlfile = substr($firsturl, strlen($this->chandomain));
		}
		$this->htmlFile = $htmlfile;
		
		$category['currenturl'] = $this->chandomain . $htmlfile;
		

		$urlargs['catid'] = $category['catid'];
		$urlargs['page'] = 1;
		$category['caturl'] = geturl(empty($category['basic']) ? 'threadlist' : 'category', $urlargs, $this->chandomain);
		$urlargs['type'] = "%s";
		$topurl = geturl('toplist', $urlargs, $this->chandomain);
		$category['topurl'] = sprintf($topurl, $category['toptype'] + 1);
		$category['topweekurl'] = sprintf($topurl, 1);
		$category['topmonthurl'] = sprintf($topurl, 2);
		$category['topstarurl'] = sprintf($topurl, 3);
		if($type != 'toplist'){
			$this->checkRequestUri($category['currenturl']);
		}
		if(!empty($category['imageurl'])){
			if(empty($category['remote'])){
				$category['imageurl'] = $this->attachurl . 'image/' . $category['imageurl'];
			}else{
				$category['imageurl'] = phpcom::$setting['ftp']['attachurl'] . 'image/' . $category['imageurl'];
			}
		}else{
			$category['imageurl'] = '';
		}
		
		return $category;
	}

	protected function getThreadClass($queries = null)
	{
		if(empty($queries)){
			$classid = stripstring($this->request->query('classid', $this->request->getQuery(0)));
		}else{
			$classid = is_array($queries) ? $queries['classid'] : intval($queries);
		}
		$catid = $queries['catid'];
		$cond = empty($queries['catid']) ? 't.catid' : "'$catid'";
		$sql = "SELECT t.*,c.catid,c.rootid,c.parentid,c.catname,c.subname,c.codename,c.prefixurl,c.pagesize,
				c.remote,c.imageurl,c.banner,c.caturl,c.template,c.keyword,c.description
			FROM " . DB::table('thread_class') . " t
			LEFT JOIN " . DB::table('category') . " c ON c.catid=$cond
			WHERE t.classid='$classid'";
		if(!$result = DB::fetch_first($sql)){
			$this->pageNotFound();
		}
		$this->chanid  = $chanid = $result['chanid'];
		phpcom::$G['channelid'] = $chanid;
		phpcom::$G['cache']['channel'] = phpcom::$G['channel'][$chanid];
		$modules = phpcom::$G['channel'][$chanid]['modules'];
		if (phpcom::$G['channel'][$chanid]['domain'] || !empty($result['prefixurl'])) {
			!defined('DOMAIN_ENABLED') && define('DOMAIN_ENABLED', true);
		}
		if (!empty($result['prefixurl'])) {
			$this->chandomain = trim($result['prefixurl'], ' /') . '/';
		}elseif (trim(phpcom::$G['channel'][$chanid]['domain'])) {
			$this->chandomain = trim(phpcom::$G['channel'][$chanid]['domain'], ' /') . '/';
		}
		$this->channelname = phpcom::$G['cache']['channel']['channelname'];
		if(!isset(phpcom::$G['cache']['category'])){
			phpcom_cache::load('category');
		}
		
		$this->initialize();
		$this->title = trim($result['name']);
		$result['catid'] = intval($result['catid']);
		if(empty($result['catname'])){
			$result['rootid'] = $result['catid'];
			$result['catname'] = $result['name'];
		}
		$this->rootid = intval($result['rootid']);
		$this->catid = $result['catid'];
		$this->parentid = $result['parentid'];
		$result['pagesize'] = empty($result['pagesize']) ? intval(phpcom::$G['channel'][$chanid]['pagesize']) : $result['pagesize'];
		if(!empty(phpcom::$G['cache']['channel']['sitename'])){
			$this->webname = phpcom::$G['cache']['channel']['sitename'];
		}
		if (empty($result['keyword'])) {
			$this->keyword = phpcom::$G['channel'][$chanid]['keyword'] ? phpcom::$G['channel'][$chanid]['keyword'] : phpcom::$setting['keyword'];
		} else {
			$this->keyword = htmlcharsencode($result['keyword']);
		}
		if (empty($result['about'])) {
			if (empty($result['description'])) {
				$this->description = phpcom::$G['channel'][$chanid]['description'] ? phpcom::$G['channel'][$chanid]['description'] : phpcom::$setting['description'];
			}else{
				$this->description = htmlcharsencode($result['description']);
			}
		} else {
			$this->description = htmlcharsencode(strip_tags($result['about']));
		}
		if(!empty($result['template'])){
			$this->templateName = $modules . '/' . $result['template'] . '_type';
		}
		$result['banner'] = empty($result['banner']) ? '' : trim($result['banner']);
		$result['module'] = $modules;
		$urlargs = array('chanid' => $this->chanid, 'type' => $result['classid'], 'name' => $result['alias'],
				'catid' => $result['catid'], 'catdir' => $result['codename'], 'page' => '{%d}');
		
		if(isset($queries['query']) && !empty($queries['query'])){
			$urlargs['type'] .= $queries['query'];
		}
		$this->baseUrlArgs = $urlargs;
		$this->baseUrlArgs['page'] = 1;
		
		$pageurl = geturl('type', $urlargs, $this->chandomain);
		$firsturl = $this->formatPageUrl($pageurl);
		$result['pageurl'] = $pageurl;
		$result['firsturl'] = $firsturl;
		
		if($this->page > 1){
			$result['url'] = str_replace('{%d}', $this->page, $pageurl);
			$htmlfile = str_replace('{%d}', $this->page, substr($pageurl, strlen($this->chandomain)));
		}else{
			$firsturl = str_replace('{%d}', 1, $firsturl);
			$result['url'] = $firsturl;
			$htmlfile = substr($firsturl, strlen($this->chandomain));
		}
		$this->htmlFile = $htmlfile;
		$result['currenturl'] = $this->chandomain . $htmlfile;
		$this->checkRequestUri($result['currenturl']);
		
		if(!empty($result['imageurl'])){
			if(empty($result['remote'])){
				$result['imageurl'] = $this->attachurl . 'image/' . $result['imageurl'];
			}else{
				$result['imageurl'] = phpcom::$setting['ftp']['attachurl'] . 'image/' . $result['imageurl'];
			}
		}else{
			$result['imageurl'] = '';
		}
		return $result;
	}
	
	protected function getMiscQueries($keys = array('softtype', 'license', 'softlang'), $type = 'desc')
	{
		$queriesnew = array();
		$queriesnew['chanid'] = strtoint($this->request->getQuery(0));
		$queriesnew['catid'] = strtoint($this->request->getQuery(1));
		$queriesnew['query'] = array($queriesnew['chanid'], $queriesnew['catid']);
		$k = 2;
		foreach($keys as $v){
			$queriesnew[$v] = strtoint($this->request->getQuery($k));
			$queriesnew['query'][] = $queriesnew[$v];
			$k++;
		}
		$v = $this->request->getQuery($k);
		if($v && is_numeric($v)){
			$queriesnew['page'] = max(1, intval($v));
			$queriesnew['type'] = '';
		}elseif($v && is_string($v)){
			$queriesnew['page'] = max(1, intval($this->request->getQuery('page')));
			$queriesnew[$type] = stripstring($v);
			$queriesnew['query'][] = $queriesnew[$type];
		}else{
			$queriesnew['page'] = 1;
		}
		return $queriesnew;
	}
	
	protected function getMiscInfo($queries)
	{
		$this->page = $queries['page'];
		$result = array();
		phpcom::$G['channelid'] = $this->chanid = $chanid = $queries['chanid'];
		if($chanid && isset(phpcom::$G['channel'][$chanid])){
			phpcom::$G['cache']['channel'] = phpcom::$G['channel'][$chanid];
			if (!empty(phpcom::$G['channel'][$chanid]['domain'])) {
				define('DOMAIN_ENABLED', TRUE);
				$this->chandomain = trim(phpcom::$G['channel'][$chanid]['domain'], ' /') . '/';
			}
		}else{
			$this->pageNotFound();
		}
		$this->initialize();
		if(!empty(phpcom::$G['cache']['channel']['sitename'])){
			$this->webname = phpcom::$G['cache']['channel']['sitename'];
		}
		$this->catid = $catid = $queries['catid'];
		$this->rootid = 0;
		$this->title = lang('common', 'misc_list_index', array('name' => phpcom::$G['channel'][$chanid]['subname']));
		$sql = "SELECT * FROM " . DB::table('category') . " WHERE catid='$catid' LIMIT 1";
		if($catid && $category = DB::fetch_first($sql)){
			$this->title .= " - " . $category['catname'];
			$this->catid = $category['catid'];
			$this->rootid = $category['rootid'];
			$this->parentid = $category['parentid'];
			$result['depth'] = $category['depth'];
			$result['currname'] = $category['catname'];
		}else{
			$result['chanid'] = $chanid;
			$result['catid'] = 0;
			$result['rootid'] = 0;
			$result['depth'] = 0;
			$result['currname'] = phpcom::$G['channel'][$chanid]['channelname'];
			$this->title .= " - " . $result['currname'];
		}
		$urlargs = array('chanid' => $chanid, 'page' => '{%d}', 'query' => $queries['query']);
		$pageurl = geturl('misc', $urlargs, $this->chandomain);
		$firsturl = $this->formatPageUrl($pageurl);
		$result['pageurl'] = $pageurl;
		$result['firsturl'] = $firsturl;
		
		if($this->page > 1){
			$result['currenturl'] = str_replace('{%d}', $this->page, $pageurl);
			$htmlfile = str_replace('{%d}', $this->page, substr($pageurl, strlen($this->chandomain)));
		}else{
			$firsturl = str_replace('{%d}', 1, $firsturl);
			$result['currenturl'] = $firsturl;
			$htmlfile = substr($firsturl, strlen($this->chandomain));
		}
		$this->htmlFile = $htmlfile;
		$this->checkRequestUri($result['currenturl']);
		
		return $result;
	}
	
	protected function getFirstCategory($rootid, $topnum = 0)
	{
		$datalist = array();
		$index = 0;
		$channel = phpcom::$G['cache']['channel'];
		$sql = "SELECT * FROM " . DB::table('category') . " WHERE rootid='$rootid' ORDER BY sortord";
		$query = DB::query($sql);
		while ($caterow = DB::fetch_array($query)) {
			if ($caterow['depth']) {
				$caterow['index'] = ++$index;
				$caterow['alt'] = $index % 2 == 0 ? 2 : 1;
				$caterow['color'] = $caterow['color'] ? ' style="color: ' . $caterow['color'] . '"' : '';
				$caterow['target'] = $caterow['target'] ? ' target="_blank"' : '';
				$caterow['pagesize'] = empty($caterow['pagesize']) ? 0 : $caterow['pagesize'];
				if (!$caterow['caturl']) {
					if (empty($channel['domain']) && empty($caterow['prefixurl'])) {
						$caterow['domain'] = $this->domain;
					} elseif(empty($caterow['prefixurl'])) {
						$caterow['domain'] = $channel['domain'] . '/';
					}else{
						$caterow['domain'] = $caterow['prefixurl'] . '/';
					}
					$caterow['curl'] = geturl('threadlist', array(
							'chanid' => $caterow['chanid'],
							'catdir' => $caterow['codename'],
							'name' => $caterow['codename'],
							'prefix' => trim($caterow['prefix']),
							'catid' => $caterow['catid'],
							'page' => 1
					), $caterow['domain']);
					$caterow['topurl'] = geturl('toplist', array(
							'chanid' => $caterow['chanid'],
							'catdir' => $caterow['codename'],
							'name' => $caterow['codename'],
							'prefix' => trim($caterow['prefix']),
							'catid' => $caterow['catid'],
							'type' => $caterow['toptype'] + 1,
							'page' => 1
					), $caterow['domain']);
				} else {
					$caterow['curl'] = $caterow['caturl'];
					$caterow['topurl'] = $caterow['caturl'];
				}
				$datalist[$caterow['catid']] = $caterow;
				if($topnum && $index >= $topnum){
					break;
				}
			}
		}
		return $datalist;
	}

	protected function processThreadListData(&$row)
	{
		$row['alt'] = $row['index'] % 2;
		$row['highlight'] = $this->threadHighlight($row['highlight']);
		$row['colors'] = $row['highlight'];
		$row['color'] = empty($row['color']) ? '' : ' style="color: ' . $row['color'] . '"';
		$urlargs = array('chanid' => $row['chanid'], 'catdir' => $row['codename'], 'tid' => $row['tid'],
				'catid' => $row['catid'], 'page' => 1, 'date' => $row['dateline']);
		$urlargs['prefix'] = empty($row['prefix']) ? '' : trim($row['prefix']);
		if(!empty($row['prefixurl'])){
			$domain = $row['prefixurl'] . '/';
		}elseif(!empty(phpcom::$G['cache']['channel']['domain'])){
			$domain = phpcom::$G['cache']['channel']['domain'] . '/';
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
			$this->processImageRowData($row, phpcom::$G['cache']['channel']['modules']);
			if(phpcom::$G['cache']['channel']['thumbmode'] == 1){
				$row['imageurl'] = $row['thumburl'];
			}elseif(phpcom::$G['cache']['channel']['thumbmode'] == 2){
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