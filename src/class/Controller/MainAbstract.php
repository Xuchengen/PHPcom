<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : MainAbstract.php  2012-7-25
 */
!defined('IN_PHPCOM') && exit('Access denied');

abstract class Controller_MainAbstract extends WebController
{
	public $regmodule, $registerurl, $loginurl, $memberurl, $attachurl, $specialurl;
	public $submissionurl, $postnewsurl, $postsofturl, $sitemapurl, $channelurl;
	public $weekdiff, $monthdiff;
	protected $catid = 0, $rootid = 0, $parentid = 0;
	protected $_initialized = false;
	protected $_tagArray = array();

	public function __construct(Web_HttpRequest $request)
	{
		parent::__construct($request);
		$this->regmodule = phpcom::$setting['register']['modname'];
		$this->regmodule = $this->regmodule ? $this->regmodule : 'register';
		$this->chandomain = $this->channelurl = $this->domain;
		$this->weekdiff = time() - (strtotime("last Sunday") + 86400);
		$this->monthdiff = time() - mktime(0, 0, 0, date("m"), 1, date("Y"));
	}

	protected function initialize()
	{
		if(!$this->_initialized){
			if (defined('DOMAIN_ENABLED') && empty(phpcom::$setting['absoluteurl'])) {
				$this->domain = $this->website . $this->instdir;
			}

			$this->registerurl = geturl('member', array('action' => $this->regmodule), $this->domain);
			$this->loginurl = geturl('member', array('action' => 'login'), $this->domain);
			$this->memberurl = geturl('member', array('action' => 'index'), $this->domain);
			$parse = parse_url(phpcom::$setting['attachurl']);
			$this->attachurl = !isset($parse['host']) ? $this->domain . phpcom::$setting['attachurl'] : phpcom::$setting['attachurl'];
			//$this->specialurl = geturl('index', array('sid' => 'index', 'name' => 'index', 'action' => ''), $this->domain, 'special');
			$this->specialurl = '';
			if($this->htmlstatus){
				$this->submissionurl = "{$this->domain}submission.html";
				$this->postnewsurl = "{$this->domain}submission-news.html";
				$this->postsofturl = "{$this->domain}submission-soft.html";
				$this->sitemapurl = "{$this->domain}sitemap.html";
			}else{
				$this->submissionurl = "{$this->domain}index.php?action=submission";
				$this->postnewsurl = "{$this->domain}index.php?action=submission&do=news";
				$this->postsofturl = "{$this->domain}index.php?action=submission&do=soft";
				$this->sitemapurl = "{$this->domain}index.php?action=sitemap";
			}
			$this->channelurl = $this->getChannelUrl($this->chanid);
		}
		$this->_initialized = true;
	}

	final protected function hotAndNewMenu()
	{
		$menus = array();
		$menus['new']['index'] = 1;
		$menus['new']['url'] = geturl('hotnew', array('action' => 'new', 'name' => 'new', 'page' => 1), $this->domain);
		$menus['new']['title'] = lang('common', 'menu_new');
		$menus['hot']['index'] = 2;
		$menus['hot']['url'] = geturl('hotnew', array('action' => 'hot', 'name' => 'hot', 'page' => 1), $this->domain);
		$menus['hot']['title'] = lang('common', 'menu_hot');
		return $menus;
	}

	final protected function hotSearchWord()
	{
		phpcom_cache::load('searchword');
		$searchwordlist = isset(phpcom::$G['cache']['searchword']) ? phpcom::$G['cache']['searchword'] : array();
		return $searchwordlist;
	}

	final protected function getSysCount(array $options = array())
	{
		$name = isset($options['name']) ? trim($options['name']) : null;
		if(!isset(phpcom::$G['cache']['syscount'])){
			phpcom_cache::load('syscount');
		}
		$data = array();
		if(!empty($name) && isset(phpcom::$G['cache']['syscount'][$name])){
			$data = phpcom::$G['cache']['syscount'][$name];
		}else{
			$data = phpcom::$G['cache']['syscount'];
		}
		return $data;
	}
	
	final protected function getChannelUrl($chanid)
	{
		if(isset(phpcom::$G['channel'][$chanid])){
			$channel = phpcom::$G['channel'][$chanid];
			$channel['chanid'] = $channel['channelid'];
			if ($channel['type'] == 'menu') {
				return $channel['domain'];
			}else{
				$domain = $channel['domain'] ? '' : $channel['codename'];
				$channel['domain'] = $channel['domain'] ? $channel['domain'] . '/' : $this->domain;
				$codename = $channel['type'] == 'system' ? '' : $channel['codename'];
				return geturl('index', array(
						'module' => $channel['modules'],
						'domain' => $domain,
						'action' => $codename,
						'channel' => $channel['codename'],
						'channelid' => $channel['chanid']
				),$channel['domain'], 'main');
			}
		}
		return $this->domain;
	}
	
	final protected function fetchChannel(array $options = array())
	{
		$chanid = isset($options['chanid']) ? intval($options['chanid']) : $this->chanid;
		$limit = isset($options['limit']) ? intval($options['limit']) : 0;
		$menu = isset($options['menu']) ? intval($options['menu']) : 1;
		$channels = phpcom::$G['channel'];
		$data = array();$i = 0;
		foreach ($channels as $key => $channel) {
			if (is_numeric($key) && !$channel['closed']) {
				if(!$menu && $channel['type'] == 'menu') continue;
				$i++;
				$channel['index'] = $i;
				$channel['alt'] = $i % 2 == 0 ? 2 : 1;
				$channel['name'] = $channel['channelname'];
				$channel['chanid'] = $channel['channelid'];
				if ($channel['type'] == 'menu') {
					$channel['url'] = $channel['domain'];
					$channel['current'] = '';
				} else {
					$domain = $channel['domain'] ? '' : $channel['codename'];
					$channel['domain'] = $channel['domain'] ? $channel['domain'] . '/' : $this->domain;
					$codename = $channel['type'] == 'system' ? '' : $channel['codename'];
					$channel['url'] = geturl('index', array(
							'module' => $channel['modules'],
							'domain' => $domain,
							'action' => $codename,
							'channel' => $channel['codename'],
							'channelid' => $channel['chanid']
					),$channel['domain'], 'main');
					if ($channel['chanid'] == $chanid) {
						$channel['current'] = 'current';
					} else {
						$channel['current'] = '';
					}
				}
				$data[$channel['chanid']] = $channel;
				if($limit && $i >= $limit){
					break;
				}
			}
		}
		unset($channels);
		return $data;
	}

	final protected function processCategoryRowData(&$category)
	{
		$category['name'] = trim($category['catname']);
		if(!empty($category['color']) && strpos($category['color'], 'style') === false){
			$category['color'] = ' style="color:' . $category['color'] . '"';
		}
		if ($category['caturl']) {
			$category['url'] = $category['caturl'];
		} else {
			if (empty(phpcom::$G['channel'][$category['chanid']]['domain']) && empty($category['prefixurl'])) {
				$category['domain'] = $this->domain;
			} elseif(empty($category['prefixurl'])) {
				$category['domain'] = phpcom::$G['channel'][$category['chanid']]['domain'] . '/';
			}else{
				$category['domain'] = $category['prefixurl'] . '/';
			}
			$urlargs = array('chanid' => $category['chanid'], 'catdir' => $category['codename'],
					'name' => $category['codename'], 'catid' => $category['catid'], 'page' => 1);
				
			if(!empty($category['prefix'])){
				$urlargs['prefix'] = $category['prefix'];
			}
			if(!empty($category['prefixurl']) && $category['basic']){
				$category['url'] = $category['prefixurl'];
			}else{
				$category['url'] = geturl($category['basic'] ? 'category' : 'threadlist', $urlargs, $category['domain']);
			}
			if(isset($category['toptype'])){
				$urlargs['type'] = $category['toptype'] + 1;
				$category['topurl'] = geturl('toplist', $urlargs, $category['domain']);
			}else{
				$category['topurl'] = $category['url'];
			}
			if(!empty($row['imageurl'])){
				if(empty($row['remote'])){
					$row['imageurl'] = $this->attachurl . 'image/' . $row['imageurl'];
				}else{
					$row['imageurl'] = phpcom::$setting['ftp']['attachurl'] . 'image/' . $row['imageurl'];
				}
			}else{
				$row['imageurl'] = '';
			}
		}
	}

	final protected function baseCategory(array $options = array())
	{
		$options += array('chanid' => 0, 'limit' => 10, 'catid' => '', 'default' => 0, 'linked' => 1);
		$chanid = $options['chanid'] ? intval($options['chanid']) : intval($options['default']);
		$catid = trim($options['catid']);
		$limit = intval($options['limit']);
		$linked = intval($options['linked']);
		phpcom_cache::load('category');
		if(!isset(phpcom::$G['cache']['category'])){
			return array();
		}
		$data = $categorys = array();
		$categorys = phpcom::$G['cache']['category'];
		$catids = trim(implodeids($catid, ','), "'");
		$catids = $catids ? explode(',', $catids) : null;
		$i = 0;
		foreach ($categorys as $catid => $category) {
			if(!$catids && (($chanid && $category['chanid'] != $chanid) || ($category['caturl'] && !$linked))){
				continue;
			}elseif($catids){
				if(!in_array($catid, $catids)) continue;
			}
			$this->processCategoryRowData($category);
			$i++;
			$category['index'] = $i;
			$category['alt'] = $i % 2 == 0 ? 2 : 1;
			$data[] = $category;
			if($limit && $i >= $limit){
				break;
			}
		}
		unset($categorys);
		return $data;
	}

	final protected function fullCategory(array $options = array())
	{
		$options += array('default' => 1, 'depth' => 3, 'catid' => '');
		$chanid = isset($options['chanid']) ? intval($options['chanid']) : $this->chanid;
		$chanid = $chanid < 1 ? intval($options['default']) : $chanid;
		$limit = isset($options['limit']) ? intval($options['limit']) : 0;
		$first = isset($options['first']) ? intval($options['first']) : 0;
		$depth = intval($options['depth']);
		$catid = trim($options['catid']);
		phpcom_cache::load("category_$chanid");
		if(!isset(phpcom::$G['cache']["category_$chanid"])){
			return array();
		}
		$data = $urlargs = array();
		$catearray = phpcom::$G['cache']['category_' . $chanid];
		$catids = trim(implodeids($catid, ','), "'");
		$catids = $catids ? explode(',', $catids) : null;
		if(isset($catearray[0])){
			$i = 0;
			foreach ($catearray[0] as $category) {
				if($catids && !in_array($category['catid'], $catids)) continue;
				$i++;
				$this->processCategoryRowData($category);
				$category['index'] = $i;
				$category['alt'] = $i % 2 == 0 ? 2 : 1;
				$data[] = $category;
				if($first && $i >= $first) break;
				if(isset($catearray[$category['catid']]) && $depth >= 1){
					$n = 0;
					foreach ($catearray[$category['catid']] as $category1) {
						$n++;
						$this->processCategoryRowData($category1);
						$category1['index'] = $n;
						$data[] = $category1;
						if($limit && $n >= $limit) break;
						if(isset($catearray[$category1['catid']]) && $depth >= 2){
							foreach ($catearray[$category1['catid']] as $category2) {
								$n++;
								$this->processCategoryRowData($category2);
								$category2['index'] = $n;
								$data[] = $category2;
								if($limit && $n >= $limit) break;
								if(isset($catearray[$category2['catid']]) && $depth >= 3){
									foreach ($catearray[$category2['catid']] as $category3) {
										$n++;
										$this->processCategoryRowData($category3);
										$category3['index'] = $n;
										$data[] = $category3;
										if($limit && $n >= $limit) break;
									}
								}
								if($limit && $n >= $limit) break;
							}
						}
						if($limit && $n >= $limit) break;
					}
				}
			}
		}
		unset($catearray);
		return $data;
	}

	final protected function fetchCategory(array $options = array())
	{
		$options += array('default' => 1, 'rootid' => 0);
		$chanid = isset($options['chanid']) ? intval($options['chanid']) : $this->chanid;
		$chanid = $chanid < 1 ? intval($options['default']) : $chanid;
		$limit = isset($options['limit']) ? intval($options['limit']) : 0;
		$catid = isset($options['catid']) ? intval($options['catid']) : $this->catid;
		$parentid = isset($options['parentid']) ? intval($options['parentid']) : $this->parentid;
		$index = isset($options['index']) ? intval($options['index']) : 0;
		phpcom_cache::load("category_$chanid");
		if(!isset(phpcom::$G['cache']["category_$chanid"])){
			return array();
		}
		$data = $urlargs = $catearray = array();
		$cachecategory = phpcom::$G['cache']["category_$chanid"];
		if(isset($cachecategory[$catid])){
			$catearray = $cachecategory[$catid];
		}elseif(!isset($cachecategory[$catid]) && isset($cachecategory[$parentid])){
			$catearray = $cachecategory[$parentid];
		}else{
			return array();
		}
		$i = 0;
		foreach ($catearray as $key => $category) {
			$this->processCategoryRowData($category);
			$i++;
			$index++;
			$category['index'] = $index;
			$category['alt'] = $index % 2 == 0 ? 2 : 1;
			$data[] = $category;
			if($limit && $i >= $limit){
				break;
			}
		}
		unset($catearray);
		return $data;
	}

	final function fetchCategoryNav(array $options = array())
	{
		static $navData = null;
		if(!empty($navData)) return $navData;
		$chanid = isset($options['chanid']) ? intval($options['chanid']) : $this->chanid;
		$catid = isset($options['catid']) ? intval($options['catid']) : $this->catid;
		$parentid = isset($options['parentid']) ? intval($options['parentid']) : $this->parentid;
		$classid = isset($options['classid']) ? intval($options['classid']) : 0;
		phpcom_cache::load('category_' . $chanid);
		if(!isset(phpcom::$G['cache']['category_' . $chanid])){
			return array();
		}
		$data = $urlargs = array();
		if($catid > 0){
			$cid = $catid;
			$catearray = phpcom::$G['cache']['category_' . $chanid];
			if(isset($catearray[$parentid][$catid]['depth'])){
				$depth = $catearray[$parentid][$catid]['depth'];
				$parentkey = $parentid;
				for($i = $depth; $i >= 0; --$i){
					if(!isset($catearray[$parentkey][$cid])) break;
					$category = $catearray[$parentkey][$cid];
					$parentkey = $category['parentkey'];
					$cid = $category['parentid'];
					$this->processCategoryRowData($category);
					$data = array_merge(array($category['catid'] => $category), $data);
				}
			}
		}
		$name = '';
		if($classid && !isset(phpcom::$G['cache']['thread_class'])){
			phpcom_cache::load('thread_class');
		}
		if($classid && isset(phpcom::$G['cache']['thread_class'][0][$chanid][$classid])){
			$name = phpcom::$G['cache']['thread_class'][0][$chanid][$classid]['name'];
		}
		if($catid && isset(phpcom::$G['cache']['thread_class'][$catid][$classid])){
			$name = phpcom::$G['cache']['thread_class'][$catid][$classid]['name'];
		}
		if($classid && $name){
			$data = array_merge($data, array(array('name' => $name, 'url' => '#')));
		}
		$navData = $data;
		return $navData;
	}

	final function fetchTypeName($tid, $limit = 10, $catid = 0)
	{
		if(!isset(phpcom::$G['cache']['threadclass'])){
			phpcom_cache::load('threadclass');
		}
		$data = array();
		$sql = DB::buildlimit("SELECT classid, catid FROM " . DB::table('thread_class_data') . " WHERE tid='$tid'", $limit);
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			if(!isset(phpcom::$G['cache']['threadclass'][$row['classid']])) continue;
			$types = phpcom::$G['cache']['threadclass'][$row['classid']];
			if (empty(phpcom::$G['channel'][$types['chanid']]['domain']) && empty($types['prefixurl'])) {
				$types['domain'] = $this->domain;
			} elseif(empty($types['prefixurl'])) {
				$types['domain'] = phpcom::$G['channel'][$types['chanid']]['domain'] . '/';
			}else{
				$types['domain'] = $types['prefixurl'] . '/';
			}
				
			$types['url'] = geturl('type', array(
					'chanid' => $types['chanid'],
					'type' => $types['classid'],
					'catid' => $row['catid'],
					'name' => $types['alias'],
					'catdir' => trim($types['codename']),
					'page' => 1
			), $types['domain']);
			$data[] = $types;
		}
		return $data;
	}

	final function fetchThreadClass(array $options = array())
	{
		$options += array('catid' => 0, 'rootid' => 0, 'limit' => 0, 'type' => '',
				'assign' => '0', 'catdir' => '', 'prefix' => '');
		$chanid = isset($options['chanid']) ? intval($options['chanid']) : $this->chanid;
		$catid = intval($options['catid']);
		$rootid = intval($options['rootid']);
		$limit = intval($options['limit']);
		$type = trim($options['type']);
		$assignid = intval($options['assign']);
		$catdir = trim($options['catdir']);
		$prefixurl = trim($options['prefix']);
		$index = isset($options['index']) ? intval($options['index']) : 0;
		if(!isset(phpcom::$G['cache']['thread_class'])){
			phpcom_cache::load('thread_class');
		}
		$data = array();
		$thread_class = array();
		if($chanid && isset(phpcom::$G['cache']['thread_class'][0][$chanid])){
			$thread_class = phpcom::$G['cache']['thread_class'][0][$chanid];
		}
		if($catid && isset(phpcom::$G['cache']['thread_class'][$catid])){
			if($type == '1'){
				$thread_class = array_merge(phpcom::$G['cache']['thread_class'][$catid], $thread_class);
			}else{
				$thread_class = phpcom::$G['cache']['thread_class'][$catid];
			}
		}

		$i = 0;
		foreach($thread_class as $class){
			$i++;
			$index++;
			if($limit && $i > $limit) break;
			$class['index'] = $index;
			$class['alt'] = $index % 2 == 0 ? 2 : 1;
			if(empty($class['catid']) && $assignid){
				$class['catid'] = $assignid;
				$class['codename'] = $catdir;
				$class['prefixurl'] = $prefixurl;
			}
			if (empty(phpcom::$G['channel'][$class['chanid']]['domain']) && empty($class['prefixurl'])) {
				$class['domain'] = $this->domain;
			} elseif(empty($class['prefixurl'])) {
				$class['domain'] = phpcom::$G['channel'][$class['chanid']]['domain'] . '/';
			}else{
				$class['domain'] = $class['prefixurl'] . '/';
			}
				
			$class['url'] = geturl('type', array(
					'chanid' => $class['chanid'],
					'type' => $class['classid'],
					'catid' => $class['catid'],
					'name' => $class['alias'],
					'catdir' => trim($class['codename']),
					'page' => 1
			), $class['domain']);
			$data[] = $class;
		}
		return $data;
	}

	protected function fetchMember(array $options = array())
	{
		$options += array('uid' => 0, 'limit' => 10, 'type' => 0, 'groupid' => 0, 'adminid' => 1,'format' => 'Y-m-d');
		$uid = intval($options['uid']);
		$groupid = trim($options['groupid']);
		$adminid = trim($options['adminid']);
		$limit = empty($options['limit']) ? 10 : trim($options['limit']);
		$format = trim($options['format']);
		$type = trim($options['type']);
		$index = isset($options['index']) ? intval($options['index']) : 0;
		$memberid = isset($options['uid']) ? trim($options['uid']) : null;
		$condition = '1';

		if($groupid && strpos($groupid, ',') && ($groupids = implodeids($groupid))){
			$condition = " m.groupid IN($groupids)";
		}elseif(is_numeric($groupid) && ($groupid = intval($groupid))){
			$condition = " m.groupid='$groupid'";
		}elseif($memberid && ($uids = implodeids($memberid))){
			$condition = " m.uid IN($uids)";
		}
		if(isset($options['status'])){
			$status = intval($options['status']);
			$condition .= " AND m.status='$status'";
		}

		$condition = $uid ? "m.uid='$uid'" : $condition;
		$condition .= $adminid ? " AND m.adminid='0'" : '';

		$orderField = ''; 
		$data = array();
		$typeArray = array('money', 'prestige', 'currency', 'praise', 'threads', 'friends', 'onlinetime', 'answers', 'askings');
		if($type == 'credits'){
			$orderField = "m.credits DESC,";
		}
		if($type && in_array($type, $typeArray)){
			$orderField = "c.$type DESC,";
		}
		$fields = "m.uid, m.username, m.email, m.adminid, m.groupid, m.face, m.gender, m.status, m.emailstatus, m.credits, m.allowadmin, m.regdate,";
		$fields .= "c.money, c.prestige, c.currency, c.praise, c.threads, c.onlinetime, c.friends, c.answers, c.askings";
		$sql = DB::buildlimit("SELECT * FROM " . DB::table('members') . " m
				LEFT JOIN " . DB::table('member_count') . " c USING(uid)
				WHERE $condition ORDER BY $orderField m.uid DESC", $limit);
		$key = empty($options['cache']) ? crc32hex($sql) : $options['cache'];
		$ttl = isset($options['ttl']) ? intval($options['ttl']) : 300;
		if($data = DataCache::get($key)){
			return $data;
		}
		$data = array();
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$index++;
			$row['date'] = fmdate($row['regdate'], $format);
			$row['url'] = $this->domain . "member.php?action=home&uid=" . $row['uid'];
			$row['title'] = phpcom::$G['usergroup'][$row['groupid']]['grouptitle'];
			$row['stars'] = phpcom::$G['usergroup'][$row['groupid']]['stars'];
			$row['index'] = $index;
			$row['alt'] = $index % 2 == 0 ? 2 : 1;
			$data[] = $row;
		}
		DataCache::set($key, $data, $ttl);
		return $data;
	}
	
	protected function getThreadByTopicId($tid)
	{
		$specids = array();
		if($tid = intval($tid)){
			$query = DB::query("SELECT specid FROM " . DB::table('special_data') . " WHERE tid='$tid'");
			while ($row = DB::fetch_array($query)) {
				$specids[] = $row['specid'];
			}
		}
		return $specids ? implode(',', $specids) : null;
	}
	
	protected function fetchSpecialClass(array $options = array())
	{
		$options += array('specid' => 0, 'limit' => 0, 'urlargs' => array(), 
				'domain' => '', 'tid' => 0, 'index' => 0);
		$limit = trim($options['limit']);
		$specid = intval($options['specid']);
		$tid = intval($options['tid']);
		$domain = trim($options['domain']);
		$index = isset($options['index']) ? intval($options['index']) : 0;
		$data = array();
		$urlargs = $options['urlargs'];
		if(empty($domain)){
			$domain = $urlargs['domain'];
		}
		$sql = DB::buildlimit("SELECT * FROM " . DB::table('special_class') . " WHERE tid='$tid' ORDER BY classid", $limit);
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$row['url'] = '';
			if(!empty($row['about']) && parse_url($row['about'], PHP_URL_SCHEME)){
				$row['url'] = $row['about'];
				$row['about'] = '';
				$row['target'] = '_blank';
			}else{
				$urlargs['classid'] = $row['classid'];
				$urlargs['chanid'] = $row['chanid'];
				$urlargs['tid'] = $row['tid'];
				$urlargs['alias'] = $row['alias'];
				$urlargs['byname'] = $row['alias'];
				$row['url'] = geturl('topiclist', $urlargs, $domain, 'special');
				$row['target'] = '_self';
			}
			$index++;
			$row['index'] = $index;
			$row['alt'] = $index % 2 == 0 ? 2 : 1;
			$data[$row['alias']] = $row;
		}
		return $data;
	}
	
	protected function fetchSpecialData(array $options = array())
	{
		$options += array('specid' => 0, 'limit' => 0, 'chanid' => '0', 'ellipsis' => '',
				'classid' => '', 'tid' => 0, 'index' => 0, 'length' => 0);
		$limit = empty($options['limit']) ? 10 : trim($options['limit'], ' ,');
		$classid = trim($options['classid'], ", \r\n\t");
		$specid = empty($options['specid']) ? intval($options['tid']) : intval($options['specid']);
		$module = isset($options['module']) ? trim($options['module']) : null;
		$chanid = isset($options['chanid']) ? intval($options['chanid']) : 0;
		$length = intval($options['length']);
		$format = isset($options['format']) ? trim($options['format']) : '';
		$ellipsis = rtrim($options['ellipsis']);
		$index = intval($options['index']);
		$condition = "WHERE specid='$specid'";
		if(strpos($classid, ',') && ($classids = implodeids($classid))){
			$condition = "WHERE classid IN($classids)";
		}elseif($classid = intval($classid)){
			$condition = "WHERE classid='$classid'";
		}elseif($classid === 0){
			$condition = "WHERE classid='0'";
		}
		$field = $joinTable = '';
		$tableName = $module && isset($this->moduleTables[$module]) ? $this->moduleTables[$module] : null;
		if(empty($tableName)){
			$tableName = $chanid && isset(phpcom::$G['channel'][$chanid]['tablename']) ? phpcom::$G['channel'][$chanid]['tablename'] : $tableName;
		}
		if($tableName){
			$field .= ',m.*';
			$joinTable = "INNER JOIN " . DB::table($tableName) . " m USING(tid) ";
		}
		$pagesql = DB::buildlimit("INNER JOIN (SELECT tid FROM " . DB::table('special_data') . " $condition ORDER BY dateline DESC) AS t2 USING(tid)", $limit);
		$sql = "SELECT t.*,c.depth,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,c.target,c.color,
			ti.attachment,ti.remote,ti.thumb,ti.preview,ti.attachimg,f.voteup,f.votedown,f.voters,f.totalscore,f.credits
			$field
			FROM " . DB::table('threads') . " t
			LEFT JOIN " . DB::table('category') . " c ON c.catid=t.catid
			LEFT JOIN " . DB::table('thread_image') . " ti USING(tid)
			LEFT JOIN " . DB::table('thread_field') . " f USING(tid)
			$joinTable  $pagesql";
		$key = empty($options['cache']) ? crc32hex($sql) : $options['cache'];
		$ttl = isset($options['ttl']) ? intval($options['ttl']) : 300;
		if($key && ($data = DataCache::get($key))){
			return $data;
		}
		$data = array();
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$row['index'] = ++$index;
			$row['alt'] = $index % 2 == 0 ? 2 : 1;
			$this->processThreadRowData($row, $length, $format, $ellipsis);
			$data[] = $row;
		}
		DataCache::set($key, $data, $ttl);
		return $data;
	}
	
	protected function fetchAnnounce(array $options = array())
	{
		$options += array('limit' => 10, 'length' => 40, 'ellipsis' => '');
		$limit = trim($options['limit']);
		$length = intval($options['length']);
		$ellipsis = trim($options['ellipsis']);
		$format = isset($options['format']) ? trim($options['format']) : '';
		$condition = '1=1';
		if(isset($options['type'])){
			$type = intval($options['type']);
			$condition = "type='$type'";
		}
		$data = array();
		$sql = DB::buildlimit("SELECT aid,title,dateline,author,type,highlight,hits
				FROM " . DB::table('announce') . "
				WHERE $condition ORDER BY aid DESC", $limit);
		$key = empty($options['cache']) ? crc32hex($sql) : $options['cache'];
		$ttl = isset($options['ttl']) ? intval($options['ttl']) : 300;
		if($data = DataCache::get($key)){
			return $data;
		}
		$data = array();
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$row['highlight'] = $this->threadHighlight($row['highlight']);
			$row['istoday'] = ($row['dateline'] + $this->timeoffset >= $this->todaytime) ? 1 : 0;
			if($format){
				$row['dateline'] = fmdate($row['dateline'], $format);
				$row['date'] = $row['dateline'];
				if ($row['istoday']) {
					$row['date'] = '<em class="new">' . $row['dateline'] . '</em>';
				} else {
					$row['date'] = '<em class="old">' . $row['dateline'] . '</em>';
				}
			}else{
				$row['date'] = '';
			}
			$row['subject'] = htmlcharsencode($row['title']);
			if ($length) {
				$row['title'] = strcut($row['title'], $length, $ellipsis);
			}
			$row['url'] = geturl('hotnew', array('action' => 'announce', 'name' => 'announce', 'page' => $row['aid'], 'aid' => $row['aid']), $this->domain);;
			$data[] = $row;
		}
		DataCache::set($key, $data, $ttl);
		return $data;
	}

	protected function formatThread(array $options = array())
	{
		$options += array('limit' => 10, 'length' => 40, 'ellipsis' => '', 'rows' => 5, 'first' => 0,
				'target' => 0, 'start' => '', 'end' => ' ', 'type' => 0);
		$limit = intval($options['limit']);
		$length = intval($options['length']);
		$length = $length > 10 ? $length : 40;
		$rows = intval($options['rows']);
		$first = trim($options['first']);
		$type = intval($options['type']);
		unset($options['type']);
		$start = $options['start'];
		$end = $options['end'] ? $options['end'] : ' ';
		$target = $options['target'] ? ' target="_blank"' : '';
		$format = $ellipsis = '';
		$data = array();
		$i = $n = 0;
		$len = intval($length / 2);
		$strlen = $len;
		$s = '';
		$key = empty($options['cache']) ? false : $options['cache'];
		$ttl = isset($options['ttl']) ? intval($options['ttl']) : 300;
		if($key && ($data = DataCache::get($key))){
			return $data;
		}
		$data = array();
		$query = $this->threadList($options, $length, $format, $ellipsis);
		while ($row = DB::fetch_array($query)) {
			$this->processThreadRowData($row, $first && $length && $i == 0 ? $length - 6 : 0);
			$row['highlight'] = str_replace('font-weight:bold;', '', $row['highlight']);
			$l = strlength($row['title']);
			if($first && $i == 0){
				if($n == 0){
					$strlen = $l >= $length - 14 ? $length - 14 : $l;
					if($strlen > $length - 22){
						$first = $n = 0;
					}else{
						$n += $strlen;
					}
					$t = strcut($row['title'], $strlen, '');
					$data[$i]['title'] = "<a$target href=\"{$row['url']}\"{$row['highlight']}>$t</a> ";
					$data[$i]['index'] = 0;
					$data[$i]['alt'] = '';
				}else{
					$strlen = $l >= $length - $n - 16 ? $length - $n - 16 : $l;
					$first = $n = 0;
					$t = strcut($row['title'], $strlen, '');
					$data[$i]['title'] .= "<a$target href=\"{$row['url']}\"{$row['highlight']}>$t</a>";
				}
			}else{
				if($n == 0){
					$i++;
					$strlen = $type ? ($l >= $length ? $length : $l) : ($l >= $len ? $len : $l);
					$data[$i]['title'] = '';
					$data[$i]['index'] = $i;
					$data[$i]['alt'] = $i % 2 == 0 ? 2 : 1;
				}else{
					$strlen = $l >= $length - $n ? $length - $n: $l;
				}
				$class = 'a'.$row['chanid'];
				$t = strcut(trim($row['title']), $strlen, '');
				$data[$i]['title'] .= "$start<a$target href=\"{$row['url']}\"{$row['highlight']} class=\"$class\">$t</a>$end";
				$n += $strlen;
				$n += strlen($start.$end);
				if($n >= $length){
					$n = 0;
					$data[$i]['title'] = trim($data[$i]['title']);
				}

			}
			$data[$i]['name'] = $row['subname'];
			$data[$i]['curl'] = $row['curl'];
			if($n == 0 && $i >= $rows){
				break;
			}
		}
		if($key){
			DataCache::set($key, $data, $ttl);
		}
		return $data;
	}

	protected function fetchVideo(array $options = array())
	{
		$options += array('catid' => 0, 'rootid' => 0, 'limit' => 10, 'length' => 0, 'ellipsis' => '',
				'top' => 0, 'image' => 0, 'specid' => '', 'address' => 0, 'where' => '', 'tid' => '');
		$chanid = isset($options['chanid']) ? intval($options['chanid']) : 5;
		$limit = empty($options['limit']) ? 10 : trim($options['limit'], ' ,');
		$rootid = trim($options['rootid'], ", \r\n\t");
		$catid = trim($options['catid'], ", \r\n\t");
		$tid = trim($options['tid'], ", \r\n\t");
		$specid = trim($options['specid'], ", \r\n\t");
		if(empty($specid) && isset($options['topicid'])){
			$specid = trim($options['topicid'], ", \r\n\t");
		}
		$classid = isset($options['classid']) ? trim($options['classid'], ", \r\n\t") : 0;
		$length = intval($options['length']);
		$format = isset($options['format']) ? trim($options['format']) : '';
		$ellipsis = rtrim($options['ellipsis']);
		$isimage = boolval($options['image']);
		$isaddress = boolval($options['address']);
		$where = trim($options['where']);
		if(isset($options['years']) && $options['years']){
			$years = intval($options['years']);
			$where .= " AND m.years='$years'";
		}
		if(isset($options['country']) && $options['country']){
			$country = addslashes($options['country']);
			$where .= " AND m.country='$country'";
		}
		if(isset($options['quality']) && $options['quality']){
			$quality = intval($options['quality']);
			$where .= " AND m.quality='$quality'";
		}
		if(isset($options['type']) && !$options['top']){
			$options['top'] = trim($options['type']);
		}
		$data = $player = array();
		$tablenames = array('tablename' => 'video_thread', 'thread_image' => 'thread_image', 'thread_field' => 'thread_field');
		if($isaddress){
			if(!isset(phpcom::$G['cache']['player'])){
				phpcom_cache::load('player');
			}
			$player = &phpcom::$G['cache']['player'];
			$tablenames['video_address'] = 'video_address';
		}
		$sql = DB::buildlimit($this->getThreadSql($tid, $chanid, $rootid, $catid, $specid, $classid, $options, $isimage, $tablenames, $where), $limit);
		$key = empty($options['cache']) ? crc32hex($sql) : $options['cache'];
		$ttl = isset($options['ttl']) ? intval($options['ttl']) : 300;
		if($data = DataCache::get($key)){
			return $data;
		}
		$data = array();
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$this->processThreadRowData($row, $length, $format, $ellipsis);
			if(isset($row['address']) && !empty($row['address'])){
				list($address) = explode("\n", $row['address']);
				if(($pos = strpos($address, '$$')) !== false){
					$row['playurl'] = substr($address, $pos + 2);
				}else{
					$row['playurl'] = $address;
				}
				$row['player'] = isset($player[$row['playerid']]) ? $player[$row['playerid']]['name'] : '';
			}
			$data[] = $row;
		}
		DataCache::set($key, $data, $ttl);
		return $data;
	}

	protected function getThreadSql($tid, $chanid = 0, $rootid = 0, $catid = 0, $specid = 0, $classid = 0, $options = array(), $isimage = 0, $tables = array(), $where = '', $limit = 10, $order = null, $cond = null)
	{
		$field = $joinTable = '';
		$orderField = "ORDER BY t.dateline DESC";
		$condition = empty($options['audit']) ? "WHERE t.status='1'" : "WHERE t.status='0'";
		//$condition .= $chanid ? " AND t.chanid='$chanid'" : " AND t.chanid>='0'";
		if(!empty($rootid)){
			if(strpos($rootid, ',') && ($rootids = implodeids($rootid))){
				$condition .= " AND t.rootid IN($rootids)";
			}elseif($rootid = intval($rootid)){
				$condition .= " AND t.rootid='$rootid'";
			}
		}elseif(!empty($catid)){
			if(strpos($catid, ',') && ($catids = implodeids($catid))){
				$condition .= " AND t.catid IN($catids)";
			}elseif($catid = intval($catid)){
				$condition .= " AND t.catid='$catid'";
			}
		}else{
			$condition .= $chanid ? " AND t.chanid='$chanid'" : " AND t.chanid>='0'";
		}
		
		if(empty($tid)){
			if(!empty($classid)){
				$orderField = "ORDER BY t2.dateline DESC";
			}
			switch (trim($options['top'])) {
				case "d": case "w": case "m": case "y":
					$time = maketime($options['top']);
					$condition .= " AND t.dateline>='$time'";
					$orderField = 'ORDER BY t.hits DESC, t.dateline DESC';
					break;
				case "D": case "Y":
					$time = maketime(strtolower($options['top']));
					$condition .= " AND t.lastdate>='$time'";
					$orderField = 'ORDER BY t.hits DESC, t.dateline DESC';
					break;
				case "W":
					$orderField = 'ORDER BY t.weekcount DESC';
					break;
				case "M":
					$orderField = 'ORDER BY t.monthcount DESC';
					break;
				case "S":
					$condition .= " AND m.star='5'";
					$orderField = 'ORDER BY t.monthcount DESC';
					break;
				case "s":
					$condition .= " AND m.star='5'";
					$orderField = 'ORDER BY t.weekcount DESC';
					break;
				case "hot":
					$hotminimum = phpcom::$setting['hotminimum'];
					$orderField = 'ORDER BY t.hits DESC, t.dateline DESC';
					$condition .= " AND t.hits>'$hotminimum'";
					break;
				case "recommend": case "rcmd": $condition .= " AND t.digest='2'";
					break;
				case "topline": case "top": $condition .= " AND t.topline='1'";
					break;
				case "focus": $condition .= " AND t.focus='1'";
					break;
				case "digest": $condition .= " AND t.digest='1'";
					break;
				case "comment": $orderField = 'ORDER BY t.comments DESC';
					break;
				case "vote": $condition .= " AND t.polled='1'";
					break;
				case "voteup": $orderField = '';
					break;
				case "img": $isimage = true;
					break;
				case "yes": case "1":
					$condition .= " AND t.istop='1'";
					$orderField = "ORDER BY t.dateline DESC";
					break;
				default:
					break;
			}
		
			if(isset($options['topline']) && trim($options['topline']) === '1'){
				$condition .= " AND t.topline='1'";
			}elseif(isset($options['topline']) && trim($options['topline']) === '0'){
				$condition .= " AND t.topline='0'";
			}
			if(isset($options['focus']) && trim($options['focus']) === '1'){
				$condition .= " AND t.focus='1'";
			}elseif(isset($options['focus']) && trim($options['focus']) === '0'){
				$condition .= " AND t.focus='0'";
			}
			if(isset($options['digest']) && ($digest = intval($options['digest'])) && $digest !== -1){
				$condition .= " AND t.digest='$digest'";
			}elseif(isset($options['digest']) && trim($options['digest']) === '0'){
				$condition .= " AND t.digest='0'";
			}else{
				if(isset($options['recommend']) && trim($options['recommend']) === '1'){
					$condition .= " AND t.digest='2'";
				}elseif(isset($options['recommend']) && trim($options['recommend']) === '0'){
					$condition .= " AND t.digest='0'";
				}
			}
		}
		$condition .= $isimage ? " AND t.image='1'" : '';
		if(!empty($options['notid'])){
			$notid = trim($options['notid']);
			if(strpos($notid, ',') && ($notids = implodeids($notid))){
				$condition .= " AND t.tid NOT IN($notids)";
			}elseif($notid = intval($notid)){
				$condition .= " AND t.tid<>'$notid'";
			}
		}
		if($isimage || isset($tables['thread_image'])){
			$field .= ',ti.attachment,ti.remote,ti.thumb,ti.preview,ti.attachimg';
			$joinTable = "LEFT JOIN " . DB::table('thread_image') . " ti USING(tid) ";
		}
		if(trim($options['top']) === 'voteup' || isset($tables['thread_field'])){
			$field .= ',f.voteup,f.votedown,f.voters,f.totalscore,f.credits';
			$joinTable .= "LEFT JOIN " . DB::table('thread_field') . " f USING(tid) ";
			if(trim($options['top']) === 'voteup'){
				$joinTable .= "INNER JOIN (SELECT tid FROM " . DB::table('thread_field') . " ORDER BY voteup DESC LIMIT $limit) AS f2 USING(tid) ";
				$condition = '';
			}
		}
		if(isset($tables['tablename']) && $tables['tablename']){
			$field .= ',m.*';
			$joinTable .= "INNER JOIN " . DB::table($tables['tablename']) . " m ON t.tid=m.tid ";
		}
		if(isset($tables['video_address']) && $tables['video_address']){
			$field .= ',va.playerid,va.caption,va.address';
			$joinTable .= "INNER JOIN " . DB::table('video_address') . " va ON va.tid=t.tid ";
		}
		if(empty($tid)){
			if(strpos($specid, ',') && ($specids = implodeids($specid))){
				$field .= ',d.specid';
				$condition .= " AND d.specid IN($specids)";
				$joinTable .= "INNER JOIN " . DB::table('special_data') . " d ON t.tid=d.tid ";
			}elseif(is_numeric($specid) && $specid){
				$field .= ',d.specid';
				$condition .= " AND d.specid='$specid'";
				$joinTable .= "INNER JOIN " . DB::table('special_data') . " d ON t.tid=d.tid ";
			}
			if(!empty($classid)){
				$field .= ',t2.classid';
				if(strpos($classid, ',') && ($classids = implodeids($classid))){
					$condition .= " AND t2.classid IN($classids)";
				}elseif($classid = intval($classid)){
					$condition .= " AND t2.classid='$classid'";
				}
				$joinTable .= "INNER JOIN " . DB::table('thread_class_data') . " t2 ON t.tid=t2.tid ";
			}
		}
		$orderField = $order ? $order : $orderField;
		if($order){
			$condition .= " AND m.chanid='$chanid'";
		}
		if(!empty($tid)){
			if(strpos($tid, ',') && ($tids = implodeids($tid))){
				$condition = "WHERE t.status='1' AND t.tid IN($tids)";
			}elseif($tid = intval($tid)){
				$condition = "WHERE t.status='1' AND t.tid='$tid'";
			}
			$orderField = $where = '';
		}
		$sql = "SELECT t.*,c.depth,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,c.target,c.color $field
			FROM " . DB::table('threads') . " t
			LEFT JOIN " . DB::table('category') . " c USING(catid) $joinTable
			$condition $where $orderField";
		return $sql;
	}

	protected function fetchThreadArray(array $options = array())
	{
		$options += array('catid' => 0, 'rootid' => 0, 'limit' => 10, 'length' => 0, 'ellipsis' => '',
				'top' => 0, 'image' => 0, 'specid' => '', 'where' => '', 'fulltext' => 0, 'tid' => '');
		$chanid = isset($options['chanid']) ? intval($options['chanid']) : $this->chanid;
		$limit = empty($options['limit']) ? 10 : trim($options['limit'], ' ,');
		$rootid = trim($options['rootid'], ", \r\n\t");
		$catid = trim($options['catid'], ", \r\n\t");
		$specid = trim($options['specid'], ", \r\n\t");
		if(empty($specid) && isset($options['topicid'])){
			$specid = trim($options['topicid'], ", \r\n\t");
		}
		$tid = trim($options['tid'], ", \r\n\t");
		$classid = isset($options['classid']) ? trim($options['classid'], ", \r\n\t") : 0;
		$length = intval($options['length']);
		$format = isset($options['format']) ? trim($options['format']) : '';
		$ellipsis = rtrim($options['ellipsis']);
		$isimage = boolval($options['image']);
		$fulltext = boolval($options['fulltext']);
		$module = isset($options['module']) ? trim($options['module']) : null;
		$index = isset($options['index']) ? intval($options['index']) : 0;
		if(isset($options['type']) && !$options['top']){
			$options['top'] = trim($options['type']);
		}
		$where = trim($options['where']);
		$order = $cond = '';
		$data = array();
		$tableName = $module && isset($this->moduleTables[$module]) ? $this->moduleTables[$module] : null;
		if(empty($tableName)){
			$tableName = $chanid && isset(phpcom::$G['channel'][$chanid]['tablename']) ? phpcom::$G['channel'][$chanid]['tablename'] : $tableName;
		}
		$tablenames = array('tablename' => $tableName, 'thread_image' => 'thread_image', 'thread_field' => 'thread_field');
		if($tableName === 'video_thread' && isset($options['years']) && $options['years']){
			$years = intval($options['years']);
			$where .= " AND m.years='$years'";
			$order = 'ORDER BY m.dateline DESC';
		}
		if($tableName === 'video_thread' && isset($options['country']) && $options['country']){
			$country = addslashes(trim($options['country']));
			$where .= " AND m.country='$country'";
			$order = 'ORDER BY m.dateline DESC';
		}
		if($tableName === 'video_thread' && isset($options['quality']) && $options['quality']){
			$quality = intval($options['quality']);
			$where .= " AND m.quality='$quality'";
			$order = 'ORDER BY m.dateline DESC';
		}
		if($tableName === 'soft_thread' && !empty($options['softtype'])){
			$softtype = addslashes(trim($options['softtype']));
			if(strpos($softtype, ',') && ($softtype = implodevalue($softtype))){
				$where .= " AND m.softtype IN($softtype)";
			}else{
				$where .= " AND m.softtype='$softtype'";
			}
			$order = 'ORDER BY m.dateline DESC';
		}
		if($tableName === 'soft_thread' && !empty($options['license'])){
			$license = addslashes(trim($options['license']));
			if(strpos($license, ',') && ($license = implodevalue($license))){
				$where .= " AND m.license IN($license)";
			}else{
				$where .= " AND m.license='$license'";
			}
			$order = 'ORDER BY m.dateline DESC';
		}
		if($tableName === 'soft_thread' && !empty($options['softlang'])){
			$softlang = addslashes(trim($options['softlang']));
			if(strpos($softlang, ',') && ($softlang = implodevalue($softlang))){
				$where .= " AND m.softlang IN($softlang)";
			}else{
				$where .= " AND m.softlang='$softlang'";
			}
			$order = 'ORDER BY m.dateline DESC';
		}
		if(isset($options['like']) && $options['like']){
			$where .= $this->getRelatedLike($options['like'], $fulltext);
		}
		$sql = DB::buildlimit($this->getThreadSql($tid, $chanid, $rootid, $catid, $specid, $classid, $options, $isimage, $tablenames, $where, $limit, $order, $cond), $limit);
		$key = empty($options['cache']) ? crc32hex($sql) : $options['cache'];
		$ttl = isset($options['ttl']) ? intval($options['ttl']) : 300;
		if($data = DataCache::get($key)){
			return $data;
		}
		$data = array();
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$row['index'] = ++$index;
			$row['alt'] = $index % 2 == 0 ? 2 : 1;
			$this->processThreadRowData($row, $length, $format, $ellipsis, $options['top']);
			$data[] = $row;
		}
		DataCache::set($key, $data, $ttl);
		return $data;
	}

	protected function threadList(array $options = array(), &$length, &$format, &$ellipsis)
	{
		$options += array('catid' => 0, 'rootid' => 0, 'limit' => 10, 'length' => 0, 'ellipsis' => '',
				'top' => 0, 'module' => '', 'image' => 0, 'field' => 0, 'specid' => '', 'where' => '', 'tid' => '');

		$chanid = isset($options['chanid']) ? intval($options['chanid']) : $this->chanid;
		$limit = empty($options['limit']) ? 10 : trim($options['limit'], ' ,');
		$rootid = trim($options['rootid'], ", \r\n\t");
		$catid = trim($options['catid'], ", \r\n\t");
		$specid = trim($options['specid'], ", \r\n\t");
		if(empty($specid) && isset($options['topicid'])){
			$specid = trim($options['topicid'], ", \r\n\t");
		}
		$tid = trim($options['tid'], ", \r\n\t");
		$classid = isset($options['classid']) ? trim($options['classid'], ", \r\n\t") : 0;
		$length = intval($options['length']);
		$format = isset($options['format']) ? trim($options['format']) : '';
		$ellipsis = rtrim($options['ellipsis']);
		$module = trim($options['module']);
		$isimage = boolval($options['image']);
		$isfield = boolval($options['field']);
		if(isset($options['type']) && !$options['top']){
			$options['top'] = trim($options['type']);
		}
		$where = trim($options['where']);
		$tableName = $module && isset($this->moduleTables[$module]) ? $this->moduleTables[$module] : '';
		$tablenames = array('tablename' => $tableName);
		if($tableName === 'video_thread' && isset($options['years']) && $options['years']){
			$years = intval($options['years']);
			$where .= " AND m.years='$years'";
		}
		if($tableName === 'video_thread' && isset($options['country']) && $options['country']){
			$country = addslashes(trim($options['country']));
			$where .= " AND m.country='$country'";
		}
		if($tableName === 'video_thread' && isset($options['quality']) && $options['quality']){
			$quality = intval($options['quality']);
			$where .= " AND m.quality='$quality'";
		}
		if($tableName === 'soft_thread' && isset($options['softtype']) && $options['softtype']){
			$softtype = addslashes(trim($options['softtype']));
			$where .= " AND m.softtype='$softtype'";
		}
		if($tableName === 'soft_thread' && isset($options['license']) && $options['license']){
			$license = addslashes(trim($options['license']));
			$where .= " AND m.license='$license'";
		}
		if(isset($options['like']) && $options['like']){
			$where .= $this->getRelatedLike($options['like']);
		}
		$sql = DB::buildlimit($this->getThreadSql($tid, $chanid, $rootid, $catid, $specid, $classid, $options, $isimage, $tablenames, $where), $limit);
		return DB::query($sql);
	}

	protected function processThreadRowData(&$row, $length = 0, $format = '', $ellipsis = '', $topname = '')
	{
		$row['color'] = $row['color'] ? ' style="color: ' . $row['color'] . '"' : '';
		$row['target'] = $row['target'] ? ' target="_blank"' : '';
		$row['highlight'] = $this->threadHighlight($row['highlight']);
		$urlargs = array('chanid' => $row['chanid'], 'catdir' => $row['codename'], 'tid' => $row['tid'],
				'catid' => $row['catid'], 'page' => 1, 'date' => $row['dateline']);
		if (empty(phpcom::$G['channel'][$row['chanid']]['domain']) && empty($row['prefixurl'])) {
			$domain = $this->domain;
		} elseif(empty($row['prefixurl'])) {
			$domain = phpcom::$G['channel'][$row['chanid']]['domain'] . '/';
		}else{
			$domain = $row['prefixurl'] . '/';
		}
		if(!empty($row['prefix'])){
			$urlargs['prefix'] = trim($row['prefix']);
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
				$row['caturl'] = geturl($row['basic']? 'category' : 'threadlist', $urlargs, $domain);
			}
		}
		$row['demourl'] = empty($row['demourl']) ? '' : trim($row['demourl']);
		$row['curl'] = $row['caturl'];
		$row['istoday'] = ($row['dateline'] + $this->timeoffset >= $this->todaytime) ? 1 : 0;
		$row['time'] = $row['dateline'];
		if($format){
			$row['dateline'] = fmdate($row['dateline'], $format);
			$row['date'] = $row['dateline'];
			if ($row['istoday']) {
				$row['date'] = '<em class="new">' . $row['dateline'] . '</em>';
			} else {
				$row['date'] = '<em class="old">' . $row['dateline'] . '</em>';
			}
		}else{
			$row['date'] = '';
		}
		$row['subject'] = htmlcharsencode($row['title']);
		if ($length) {
			$row['title'] = strcut($row['title'], $length, $ellipsis);
		}
		$row['subtitle'] = empty($row['subtitle']) ? trim($row['title']) : trim($row['subtitle']);
		$row['summary'] = isset($row['summary']) ? trim($row['summary']) : '';
		$row['percentup'] = '0.00%';
		$row['scores'] = 0;
		if(isset($row['voteup'])){
			$voteup = intval($row['voteup']);
			$total = $voteup + $row['votedown'];
			$row['percentup'] = ($voteup ? round(($voteup / $total) * 100, 2) : '0.00') . '%';
			$scores = $row['voters'] ? $row['totalscore']  / $row['voters'] : 0;
			$row['scores'] = $scores < 10 ? sprintf( "%01.1f ", $scores) : 10;
		}
		if(($topname == 'M' || $topname == 'S') && isset($row['monthcount'])){
			$row['count'] = $row['monthcount'];
			if($row['lastmonth'] <= 0 && $row['monthcount'] <= 0){
				$row['trend'] = 'fair';
			}else{
				$monthavg = round($row['lastmonth'] * ($this->monthdiff / 2592000));
				if($row['monthcount'] > $monthavg){
					$row['trend'] = 'rise';
				}elseif($row['monthcount'] < $monthavg){
					$row['trend'] = 'fall';
				}else{
					$row['trend'] = 'fair';
				}
			}
		}elseif($topname == 'W' && isset($row['weekcount'])){
			$row['count'] = $row['weekcount'];
			if($row['lastweek'] <= 0 && $row['weekcount'] <= 0){
				$row['trend'] = 'fair';
			}else{
				$weekavg = round($row['lastweek'] * ($this->weekdiff / 604800));
				if($row['weekcount'] > $weekavg){
					$row['trend'] = 'rise';
				}elseif($row['weekcount'] < $weekavg){
					$row['trend'] = 'fall';
				}else{
					$row['trend'] = 'fair';
				}
			}
		}else{
			$row['trend'] = '';
		}
		$row['size'] = '';
		if(isset($row['softsize'])){
			$row['size'] = formatbytes(intval($row['softsize']) * 1024);
		}
		$modules = empty(phpcom::$G['channel'][$row['chanid']]['modules']) ? 'article' : phpcom::$G['channel'][$row['chanid']]['modules'];
		if($modules == 'video'){
			$row['purl'] = geturl('play', array(
					'chanid' => $row['chanid'],
					'catdir' => $row['codename'],
					'name' => $row['codename'],
					'tid' => $row['tid'],
					'id' => empty($row['aid']) ? '' : $row['aid'],
					'page' => 1
			), $domain);
		}elseif($row['attached'] == 2){
			$row['purl'] = geturl('preview', array(
					'chanid' => $row['chanid'],
					'catdir' => $row['codename'],
					'name' => $row['codename'],
					'tid' => $row['tid'],
					'page' => 1
			), $domain);
		}else{
			$row['purl'] = $row['url'];
		}
		if(isset($row['attachment']) && $row['image'] == 1){
			$this->processImageRowData($row, $modules);
		}else{
			$row['thumburl'] = $row['previewurl'] = $row['imageurl'] = $this->domain . 'misc/images/noimage.jpg';
		}
	}

	final protected function processImageRowData(&$row, $modules)
	{
		if(empty($row['attachment'])) $row['attachment'] = $row['attachimg'];
		if($row['remote']){
			$row['imageurl'] = phpcom::$setting['ftp']['attachurl'] . $modules . '/' . $row['attachment'];
		}else{
			if(parse_url($row['attachment'], PHP_URL_SCHEME) || substr($row['attachment'], 0, 1) == '/'){
				$row['imageurl'] = $row['attachment'];
			}else{
				$row['imageurl'] = $this->attachurl . $modules . '/' . $row['attachment'];
			}
		}
		$row['thumburl'] = $row['thumb'] ? generatethumbname($row['imageurl']) : $row['imageurl'];
		if(empty($row['attachimg'])){
			$row['previewurl'] = $row['imageurl'];
		}else{
			if($row['preview']){
				$row['previewurl'] = phpcom::$setting['ftp']['attachurl'] . $modules . '/' . $row['attachimg'];
			}else{
				if(parse_url($row['attachimg'], PHP_URL_SCHEME) || substr($row['attachimg'], 0, 1) == '/'){
					$row['previewurl'] = $row['attachimg'];
				}else{
					$row['previewurl'] = $this->attachurl . $modules . '/' . $row['attachimg'];
				}
			}
			if(empty($row['thumburl'])){
				$row['thumburl'] = $row['previewurl'];
			}
		}
	}

	final protected function fetchPersons(array $options = array())
	{
		$options += array('chanid' => 0, 'tid' => 0, 'pid' => 0, 'name' => '', 'limit' => 10, 'type' => 0);
		$limit = intval($options['limit']);
		$chanid = isset($options['chanid']) ? intval($options['chanid']) : $this->chanid;
		$personid = trim($options['pid']);
		$pername = trim($options['name']);
	}
	final protected function fetchComments(array $options = array())
	{
		$options += array('chanid' => 0, 'tid' => 0, 'uid' => 0, 'limit' => 1, 'length' => 0, 'ellipsis' => '',
				'type' => 0, 'rootid' => '', 'catid' => '', 'strlen' => 0);
		$limit = empty($options['limit']) ? 10 : trim($options['limit']);
		$chanid = intval($options['chanid']);
		$tid = intval($options['tid']);
		$uid = intval($options['uid']);
		$rootid = trim($options['rootid'], ", \r\n\t");
		$catid = trim($options['catid'], ", \r\n\t");
		$format = isset($options['format']) ? trim($options['format']) : '';
		$length = intval($options['length']);
		$ellipsis = rtrim($options['ellipsis']);
		$strlength = intval($options['strlen']);
		$index = isset($options['index']) ? intval($options['index']) : 0;
		$data = array();
		$condition = $tid ? "t1.tid='$tid'" : "t1.tid>'0'";
		$condition .= $chanid ? " AND t3.chanid='$chanid'" : '';
		$condition .= $uid ? " AND t1.uid='$uid'" : '';
		if(!empty($rootid)){
			if(strpos($rootid, ',') && ($rootids = implodeids($rootid))){
				$condition .= " AND t3.rootid IN($rootids)";
			}elseif($rootid = intval($rootid)){
				$condition .= " AND t3.rootid='$rootid'";
			}
		}elseif(!empty($catid)){
			if(strpos($catid, ',') && ($catids = implodeids($catid))){
				$condition .= " AND t3.catid IN($catids)";
			}elseif($catid = intval($catid)){
				$condition .= " AND t3.catid='$catid'";
			}
		}
		$orderby = boolval($options['type']) ? 'ORDER BY t1.commentid ASC' : 'ORDER BY t1.lastdate DESC';
		$sql = DB::buildlimit("SELECT t1.*, t2.*, t3.chanid, t3.title, t3.highlight FROM " . DB::table('comments') . " t1
				INNER JOIN " . DB::table('comment_body') . " t2 ON t2.commentid=t1.commentid
				INNER JOIN " . DB::table('threads') . " t3 ON t3.tid=t1.tid
				WHERE $condition AND t2.first='1' AND t2.status='1' $orderby", $limit, 0);
		$key = empty($options['cache']) ? crc32hex($sql) : $options['cache'];
		$ttl = isset($options['ttl']) ? intval($options['ttl']) : 300;
		if($data = DataCache::get($key)){
			return $data;
		}
		$data = array();
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$row['highlight'] = $this->threadHighlight($row['highlight']);
			$row['content'] = bbcode::output($row['content']);
			if($strlength){
				$row['content'] = strcut(strip_tags($row['content']), $strlength, $ellipsis);
			}
			if ($length) {
				$row['title'] = strcut($row['title'], $length, $ellipsis);
			}
			if($format){
				$row['lastdate'] = fmdate($row['lastdate'], $format);
				$row['dateline'] = fmdate($row['dateline'], $format);
			}
			$row['date'] = $row['lastdate'];
			if ($row['username'] == 'guest') {
				$row['username'] = lang('common', 'guest');
			}
			if ($row['author'] == 'guest') {
				$row['author'] = lang('common', 'guest');
			}
			$row['homeurl'] = $this->domain . "member.php?action=home&uid=" . $row['authorid'];
			$row['url'] = geturl('comment', array('tid' => $row['tid'], 'page' => 1), $this->domain);
			$row['id'] = $row['bodyid'];
			$row['index'] = ++$index;
			$row['alt'] = $index % 2 == 0 ? 2 : 1;
			$data[] = $row;
		}
		DataCache::set($key, $data, $ttl);
		return $data;
	}

	protected function fetchPollVote(array $options = array()){
		$options += array('tid' => 0, 'limit' => 1, 'length' => 0, 'ellipsis' => '', 'strlen' => 0);
		$limit = empty($options['limit']) ? 1 : trim($options['limit']);
		$tid = intval($options['tid']);
		$length = intval($options['length']);
		$optlength = intval($options['strlen']);
		$ellipsis = rtrim($options['ellipsis']);
		$index = isset($options['index']) ? intval($options['index']) : 0;
		$condition = $tid ? " WHERE tid='$tid'" : '';
		$data = array();
		$sql = DB::buildlimit("SELECT * FROM " . DB::table('pollvotes') . $condition, $limit);
		$key = empty($options['cache']) ? crc32hex($sql) : $options['cache'];
		$ttl = isset($options['ttl']) ? intval($options['ttl']) : 300;
		if($data = DataCache::get($key)){
			return $data;
		}
		$data = array();
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$row['index'] = ++$index;
			$row['alt'] = $index % 2 == 0 ? 2 : 1;
			$row['title'] = $length ? strcut($row['polltitle'], $length, $ellipsis) : $row['polltitle'];
			$row['pollurl'] = $row['url'] = geturl('vote', array('pid' => $row['pollid'], 'tid' => $row['tid']), $this->domain, 'main');
			$row['ajaxurl'] = $this->domain . "apps/ajax.php?action=pollvotes&tid={$row['tid']}&pollid={$row['pollid']}";
			$row['option'] = $this->polloptions($row['pollid'], $row['checkbox'], $optlength);
			$data[] = $row;
		}
		DataCache::set($key, $data, $ttl);
		return $data;
	}

	final protected function showTags($tags) {
		$tagstr = '';
		if ($tags) {
			$tagarray = array_unique(explode("\t", $tags));
			foreach ($tagarray as $value) {
				$tagid = substr($value, 0, strpos($value, ','));
				$tagname = substr($value, strpos($value, ',') + 1);
				$url = geturl('tag', array('tagid' => $tagid, 'name' => rawurlencode($tagname), 'page' => 1), $this->domain);
				$tagstr .= '<a href="' . $url . '">' . $tagname . '</a> ';
			}
		}
		return $tagstr;
	}

	protected function relatedTags(array $options = array())
	{
		$options += array('catid' => 0, 'rootid' => 0, 'limit' => 10, 'length' => 0, 'ellipsis' => '',
				'format' => 'm-d', 'image' => 0, 'tags' => '', 'module' => '');
		$tags = trim($options['tags']);
		//$chanid = isset($options['chanid']) ? intval($options['chanid']) : 0;
		$chanid = trim($options['chanid'], ", \r\n\t");
		$limit = empty($options['limit']) ? 10 : trim($options['limit']);
		$rootid = intval($options['rootid']);
		$catid = intval($options['catid']);
		$length = intval($options['length']);
		$format = trim($options['format']);
		$ellipsis = rtrim($options['ellipsis']);
		$module = trim($options['module']);
		$isimage = boolval($options['image']);
		$index = isset($options['index']) ? intval($options['index']) : 0;
		$tid = isset($options['tid']) ? intval($options['tid']) : 0;
		$tableName = $module && isset($this->moduleTables[$module]) ? $this->moduleTables[$module] : null;
		if(!empty($chanid) && intval($chanid) > 0) {
			$tableName = isset(phpcom::$G['channel'][$chanid]['tablename']) ? phpcom::$G['channel'][$chanid]['tablename'] : $tableName;
		}
		$conditions = "t.status='1'";
		
		$data = $tids = array();
		$leftTable = '';
		$field = ',c.depth,c.basic,c.catname,c.subname,c.codename,c.prefix,c.caturl,c.target,c.color';
		if($rootid > 0){
			$conditions .= " AND t.rootid='$rootid'";
		}else{
			$conditions .= $catid ? " AND t.catid='$catid'" : '';
		}
		if($isimage){
			$field .= ',ti.attachment,ti.remote,ti.thumb,ti.preview';
			$conditions .= " AND t.attached='2'";
			$leftTable = "LEFT JOIN " . DB::table('thread_image') . " ti USING(tid) ";
		}
		if($tableName){
			$field .= ',m.*';
			$leftTable .= "INNER JOIN " . DB::table($tableName) . " m ON m.tid=t.tid ";
		}
		$key = empty($options['cache']) ? crc32hex("tags:$chanid:$tags:$tid") : $options['cache'];
		$ttl = isset($options['ttl']) ? intval($options['ttl']) : 300;
		if($data = DataCache::get($key)){
			return $data;
		}
		$data = array();
		$tagmd5 = md5($tags.'_'.$chanid);
		if(isset($this->_tagArray[$tagmd5])){
			$tids = $this->_tagArray[$tagmd5];
		}else{
			$tids = $this->getTagByThreadIds($tags, $chanid, $limit, $tid);
		}
		if($tids){
			$conditions .= " AND t.tid IN(".implodeids($tids).")";
			$sql = DB::buildlimit("SELECT t.*$field FROM " . DB::table('threads') . " t
					LEFT JOIN " . DB::table('category') . " c USING(catid) $leftTable
					WHERE $conditions", $limit);
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				$row['index'] = ++$index;
				$row['alt'] = $index % 2 == 0 ? 2 : 1;
				$this->processThreadRowData($row, $length, $format, $ellipsis);
				$data[] = $row;
			}
			DataCache::set($key, $data, $ttl);
		}
		return $data;
	}

	protected function getRelatedLike($keyword, $fulltext = 0)
	{
		$likefield = "t.title LIKE '%{text}%'";
		$fulltext && $likefield .= " OR m.summary LIKE '%{text}%'";
		$field = "($likefield)";
		if($keyword){
			if(preg_match("(AND|\+|&)", $keyword) && !preg_match("(OR|\||,)", $keyword)) {
				$andor = ' AND ';
				$keywordsrch = '1';
				$keyword = preg_replace("/( AND |&| )/is", "+", $keyword);
			} else {
				$andor = ' OR ';
				$keywordsrch = '0';
				$keyword = preg_replace("/( OR |\||,| )/is", "+", $keyword);
			}
			$keyword = str_replace('*', '%', addcslashes($keyword, '%_'));
			foreach(explode('+', $keyword) as $text) {
				$text = trim($text);
				if($text) {
					$keywordsrch .= $andor;
					$keywordsrch .= str_replace('{text}', $text, $field);
				}
			}
			$keyword = " AND ($keywordsrch)";
		}
		return $keyword;
	}

	protected function getTagByThreadIds($tags, $chanid = 0, $limit = 10, $tid = 0)
	{
		$tids = $tagArray = array();
		$condition = '0';
		if(strpos($tags, "\t")){
			$array = array_unique(explode("\t", trim($tags)));
			foreach ($array as $tag) {
				$tagArray[] = substr($tag, 0, strpos($tag, ','));
			}
			if($instr = implodeids($tagArray)){
				$condition = "tagid IN($instr)";
			}
		}else{
			$tagArray = explode(",", trim($tags));
			if($instr = implodein($tagArray)){
				$condition = "tagname IN($instr)";
			}
		}
		if(!empty($chanid)){
			if(strpos($chanid, ',') && ($chanids = implodeids($chanid))){
				$condition .= " AND chanid IN($chanids)";
			}elseif($chanid = intval($chanid)){
				$condition .= " AND chanid='$chanid'";
			}
		}
		$sql = DB::buildlimit("SELECT DISTINCT tid FROM " . DB::table('tagdata') . "
				WHERE $condition ORDER BY tid DESC", $limit + 2);
		$query = DB::query($sql);
		$i = 0;
		while ($row = DB::fetch_array($query)) {
			if($tid && $tid == $row['tid']) continue;
			$tids[] = $row['tid'];
			$i++;
			if($i >= $limit) break;
		}
		return $tids;
	}

	final protected function friendLink($options = array())
	{
		$options += array('limit' => 10, 'type' => 0, 'category' => 0);
		$data = array();
		$chanid = isset($options['chanid']) ? intval($options['chanid']) : -1;
		$limit = empty($options['limit']) ? 10 : trim($options['limit']);
		$type = intval($options['type']);
		$category = intval($options['category']);
		$index = isset($options['index']) ? intval($options['index']) : 0;
		$conditions = " closed='0' AND type='$type' AND category='$category'";
		$conditions .= $chanid === -1 ? '' : " AND chanid='$chanid'";
		$sql = DB::buildlimit("SELECT * FROM " . DB::table('friendlinks') . " WHERE $conditions ORDER BY sortord ASC", $limit);
		$key = empty($options['cache']) ? crc32hex($sql) : $options['cache'];
		$ttl = isset($options['ttl']) ? intval($options['ttl']) : 300;
		if($data = DataCache::get($key)){
			return $data;
		}
		$data = array();
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$row['color'] = $row['color'] ? ' style="color:' . $row['color'] . '"' : '';
			if ($row['expires']) {
				$row['expired'] = $row['expires'] > $this->todaytime ? 0 : 1;
			} else {
				$row['expired'] = 0;
			}
			if (!$row['expired']) {
				$row['index'] = ++$index;
				$row['alt'] = $index % 2 == 0 ? 2 : 1;
				$data[] = $row;
			} else {
				DB::update('friendlinks', array('closed' => 1), "linkid='{$row['linkid']}'");
			}
		}
		DataCache::set($key, $data, $ttl);
		return $data;
	}

	final protected function fetchAttachment($options = array())
	{
		$options += array('tid' => '', 'chanid' => 0, 'image' => 1, 'limit' => 0);
		$tid = intval($options['tid']);
		$chanid = $options['chanid'] ? intval($options['chanid']) : $this->chanid;
		$image = intval($options['image']);
		$limit = trim($options['limit']);
		$preview = isset($options['preview']) ? intval($options['preview']) : false;
		$pagemode = isset($options['pagemode']) ? intval($options['pagemode']) : 2;
		$limit = $limit ? " LIMIT $limit" : '';
		if(empty($chanid)){
			$chanid = DB::result_first("SELECT chanid FROM ".DB::table('attachment')." WHERE tid='$tid' LIMIT 1");
		}
		if(empty($chanid) && !isset(phpcom::$G['channel'][$chanid])) return array();
		$domain = $this->chandomain;
		$module = phpcom::$G['channel'][$chanid]['modules'];
		$urlargs = array('chanid' => $chanid, 'tid' => $tid, 'page' => 1);
		$data = array();
		$i = 0;
		$sql = "SELECT attachid, tid, sortord, attachment, description, url, dateline, thumb, preview, image, remote
				FROM " . DB::table("attachment_$module") . " WHERE tid='$tid' AND image='$image' ORDER BY sortord$limit";
		$key = empty($options['cache']) ? crc32hex($sql) : $options['cache'];
		$ttl = isset($options['ttl']) ? intval($options['ttl']) : 300;
		if($data = DataCache::get($key)){
			return $data;
		}
		$data = array();
		$query = DB::query($sql);
		$count = DB::num_rows($query);
		while ($attach = DB::fetch_array($query)) {
			if($preview !== false && $preview !== $attach['preview']){
				continue;
			}
			++$i;
			$attach['i'] = $i;
			$attach['count'] = $count;
			$attach['index'] = str_pad($i, 2 , '0', STR_PAD_LEFT);
			$attach['module'] = $module;
			$attach['attachurl'] = ($attach['remote'] ? phpcom::$setting['ftp']['attachurl'] : $this->attachurl) . "$module/";
			$attach['date'] = fmdate($attach['dateline']);
			$attach['src'] = $attach['attachurl'] . $attach['attachment'];
			$urlargs['aid'] = $attach['attachid'];
			if(empty($attach['image'])){
				$attach['url'] = empty($attach['url']) ? $attach['src'] : trim($attach['url']);
				$attach['pageurl'] = $attach['url'];
				$attach['previewurl'] = $attach['thumburl'] = '';
			}else{
				$urlargs['page'] = 1;
				$attach['url'] = empty($attach['url']) ? geturl('preview', $urlargs, $domain) : trim($attach['url']);
				if(empty($pagemode)){
					$attach['pageurl'] = $attach['url'];
				}else{
					$urlargs['page'] = '{%d}';
					$pageurl = geturl('preview', $urlargs, $domain);
					$firsturl = $this->formatPageUrl($pageurl);
					if($i == 1){
						$attach['pageurl'] = str_replace('{%d}', 1, $firsturl);
					}else{
						$attach['pageurl'] = str_replace('{%d}', $i, $pageurl);
					}
				}
				$attach['previewurl'] = $attach['preview'] ? generatethumbname($attach['src'], '_small.jpg') : $attach['src'];
				$attach['thumburl'] = $attach['thumb'] ? generatethumbname($attach['src']) : $attach['previewurl'];
			}
			$data[$i] = $attach;
		}
		!empty($data) &&  DataCache::set($key, $data, $ttl);
		return $data;
	}

	final protected function fetchAadverts($options = array())
	{
		$options += array('name' => '', 'cid' => 0);
		$image = isset($options['image']) ? boolval($options['image']) : false;
		$name = trim($options['name']);
		$index = isset($options['index']) ? intval($options['index']) : 0;
		$data = array();
		if(!isset(phpcom::$G['cache']['adcategory'])){
			phpcom_cache::load('adcategory');
		}
		if(isset(phpcom::$G['cache']['adcategory'][$name])){
			$adcategory = phpcom::$G['cache']['adcategory'][$name];
			$cid = intval($adcategory['cid']);
			$display = intval($adcategory['display']);
			$limit = $display == 1 ? 1 : intval($adcategory['maxads']);
			$limit = $limit ? $limit : 50;
			$width = trim($adcategory['width']);
			$height = trim($adcategory['height']);
			$time = TIMESTAMP;
			$condition = "status='1'";
			$condition .= $image ? " AND type='1'" : '';
			$condition .= " AND (expires='0' OR expires>'$time') AND cid='$cid'";
			$displayorder = $display == 2 && $limit == 1 ? "rand()" : "displayorder ASC";
			$sql = DB::buildlimit("SELECT * FROM " . DB::table('adverts') . " WHERE $condition ORDER BY $displayorder", $limit);
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				$row['adname'] = $name;
				$row['highlight'] = $this->threadHighlight($row['highlight']);
				$row['color'] = $row['highlight'];
				$row['stylewidth'] = $row['width'] ? 'width:'. $row['width']. (strpos($row['width'], '%') ? '' : 'px').';' : '';
				$row['styleheight'] = $row['height'] ? 'height:'. $row['height']. (strpos($row['height'], '%') ? '' : 'px').';' : '';
				$row['style'] = !$row['stylewidth'] && !$row['styleheight'] ? '' : " style=\"{$row['stylewidth']}{$row['styleheight']}\"";

				if(!empty($row['attached'])){
					$row['attachurl'] = empty($row['remote']) ? $this->attachurl : phpcom::$setting['ftp']['attachurl'];
					$parseurl = parse_url($row['src']);
					$row['src'] = isset($parseurl['host']) ? $row['src'] : $row['attachurl'] .'image/'. $row['src'];
				}else{
					if(!parse_url($row['src'], PHP_URL_SCHEME)){
						$row['src'] = $this->domain . ltrim($row['src'], '/ ');
					}
				}

				$row['thumb'] = $row['src'];
				if($row['type'] == 1 && $row['src']){
					$row['thumb'] = generatethumbname($row['src']);
				}

				$row['index'] = ++$index;
				$row['alt'] = $index % 2 == 0 ? 2 : 1;
				$data[] = $row;
			}
			if($display == 2 && $limit > 1 && $data){
				shuffle($data);
			}
		}
		return $data;
	}

	final protected function getAdvertise($options = array())
	{
		$name = isset($options['name']) ? trim($options['name']) : 'NoAdvert';
		$ajax = isset($options['ajax']) ? boolval($options['ajax']) : 0;
		$issrc = isset($options['src']) ? boolval($options['src']) : 0;
		$querystring = isset($options['query']) ? '&query='.trim($options['query']) : '';
		if(!isset(phpcom::$G['cache']['adcategory'])){
			phpcom_cache::load('adcategory');
		}
		if(isset(phpcom::$G['cache']['adcategory'][$name])){
			$adcategory = &phpcom::$G['cache']['adcategory'][$name];
			$cid = intval($adcategory['cid']);
			$ctype = intval($adcategory['ctype']);
			if(empty($adcategory['status'])){
				return '';
			}
			if($issrc || $ctype === 3){
				return $this->domain . "apps/misc.php?action=advert&name=$name";
			}
			if(!$ajax && $ctype == 0){
				$adurl = $this->domain . "apps/misc.php?action=advert&name=$name";
				return "<script type=\"text/javascript\" src=\"$adurl$querystring\"></script>\n";
			}elseif($ctype == 1 || $ajax){
				$ajaxid = "a_$name";
				$ajaxdiv = "<span id=\"$ajaxid\"></span>\n";
				if(isset($options['id']) && $options['id']){
					$ajaxid = trim($options['id']);
					$ajaxdiv = '';
				}
				$adurl = $this->domain . "apps/ajax.php?action=advert&name=$name$querystring";
				return "$ajaxdiv<script type=\"text/javascript\">ajaxget('$adurl', '$ajaxid');</script>\n";
			}else{
				if(isset(phpcom::$G['group']['noadverts']) && phpcom::$G['group']['noadverts']){
					return '';
				}
				$display = intval(phpcom::$G['cache']['adcategory'][$name]['display']);
				$limit = $display == 1 ? 1 : intval(phpcom::$G['cache']['adcategory'][$name]['maxads']);
				//$limit = $limit ? $limit : 50;
				$content = $start = $end = '';
				if($limit > 1){
					$start = '<li>';
					$end = '</li>';
				}
				$adverts = $this->fetchAadverts(array('name' => $name));
				foreach ($adverts as $advert) {
					$s = $advert['content'];
					if($advert['type'] == 0){
						$s = "$start<a href=\"{$advert['url']}\"{$advert['highlight']} target=\"_blank\">{$advert['word']}</a>$end";
					}elseif($advert['type'] == 1){
						$s = "$start<a href=\"{$advert['url']}\" target=\"_blank\"><img src=\"{$advert['src']}\"{$advert['style']} /></a>$end";
					}elseif($advert['type'] == 2){
						$s = "$start<object{$advert['style']} data=\"{$advert['src']}\" type=\"application/x-shockwave-flash\">";
						$s .= "<param name=\"src\" value=\"{$advert['src']}\" /></object>$end";
					}elseif($advert['type'] == 4){
						$s = "$start<iframe marginwidth=\"0\" marginheight=\"0\" frameborder=\"0\" scrolling=\"no\" src=\"{$advert['url']}\"{$advert['style']}></iframe>$end";
					}else{
						$s = $start.$advert['content'].$end;
					}
					if($name === 'FixedBottomLeft'){
						$html = str_replace(array("'", "\r", "\n"), array("\'", "", ""), $s);
						$content .= "<script type=\"text/javascript\">displayFixedAdLayer('$html', '{$advert['width']}', '{$advert['height']}', 0);</script>";
					}elseif($name === 'FixedBottomRight'){
						$html = str_replace(array("'", "\r", "\n"), array("\'", "", ""), $s);
						$content .= "<script type=\"text/javascript\">displayFixedAdLayer('$html', '{$advert['width']}', '{$advert['height']}', 1);</script>";
					}elseif($name === 'CoupletAdLeft'){
						$html = str_replace(array("'", "\r", "\n"), array("\'", "", ""), $s);
						$content .= "<script type=\"text/javascript\">displayFixedAdLayer('$html', '{$advert['width']}', '{$advert['height']}', 2);</script>";
					}elseif($name === 'CoupletAdRight'){
						$html = str_replace(array("'", "\r", "\n"), array("\'", "", ""), $s);
						$content .= "<script type=\"text/javascript\">displayFixedAdLayer('$html', '{$advert['width']}', '{$advert['height']}', 3);</script>";
					}else{
						$content .= $s;
					}
				}
				return $limit > 1 ? "<ul>$content</ul>" : $content;
			}
		}
		return '';
	}

	public function polloptions($pollid, $checkbox = 0, $length = 0) {
		$pollid = intval($pollid);
		$data = array();
		$i = 0;
		$total = DB::result_first("SELECT SUM(votes) AS total FROM " . DB::table('polloption') . " WHERE pollid='$pollid'");
		$sql = "SELECT * FROM " . DB::table('polloption') . " WHERE pollid='$pollid'";
		$key = empty($options['cache']) ? crc32hex($sql) : $options['cache'];
		$ttl = isset($options['ttl']) ? intval($options['ttl']) : 300;
		if($data = DataCache::get($key)){
			return $data;
		}
		$data = array();
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$i++;
			if ($checkbox) {
				$row['input'] = '<input type="checkbox" id="polloption_' . $i . '" name="pollanswers[]" value="'.$row['voteid'].'" />';
			} else {
				$row['input'] = '<input type="radio" id="polloption_' . $i . '" name="pollanswers[]" value="'.$row['voteid'].'"' . (($i === 1) ? ' checked="checked"' : '') . '/>';
			}
			$row['index'] = $i;
			$row['alt'] = $i % 2 == 0 ? 2 : 1;
			$row['total'] = $total;
			$row['title'] = $length ? strcut($row['voteoption'], $length, '') : $row['voteoption'];
			$row['width'] = $row['votes'] > 0 ? (@round($row['votes'] * 100 / $total, 2)) . '%' : '1px';
			$row['percent'] = @sprintf("%01.2f", $row['votes'] * 100 / $total);
			$data[] = $row;
		}
		!empty($data) && DataCache::set($key, $data, $ttl);
		return $data;
	}
	
	public function parserContentDownload($matches) {
		$tids = trim($matches[3]);
		if(empty($tids)) return '';
		$caption = trim($matches[2]);
		$condition = "WHERE t.status='1'";
		$string = '<div class="textdownload">';
		$string .= empty($caption) ? '' : "<div class=\"caption\">$caption</div>";
		$tmparray = array();
		if(strpos($tids, ',') && ($tids = implodeids($tids, null, 0, $tmparray))){
			$condition .= " AND t.tid IN($tids)";
			$tmparray = array_flip($tmparray);
		}elseif($tid = intval($tids)){
			$condition .= " AND t.tid='$tid'";
			$tmparray[$tid] = '';
		}else{
			return '';
		}
		$softname = lang('common', 'download_softname');
		$softsize = lang('common', 'download_softsize');
		$license = lang('common', 'download_license');
		$softlang = lang('common', 'download_softlang');
		$softtype = lang('common', 'download_softtype');
		$runsystem = lang('common', 'download_runsystem');
		$dateline = lang('common', 'download_dateline');
		$downaddr = lang('common', 'download_address');
		$content = <<< EOT
<table width="100%" class="down-{alt}">
<tr><td align="center" rowspan="2" class="dimg"><a target="_blank" href="{purl}"><img src="{thumburl}" width="110" height="75" /></a></td>
<td colspan="2" class="dtitle"><a target="_blank" href="{url}">{title}</a></td></tr>
<tr><td class="dinfo"><ul><li><strong>$license</strong>{license}</li>
<li><strong>$softtype</strong>{softtype}</li>
<li><strong>$softlang</strong>{softlang}</li>
<li><strong>$softsize</strong>{size}</li>
<li><strong>$dateline</strong>{date}</li>
<li><strong>$runsystem</strong>{runsystem}</li>
</ul></td>
<td align="center" class="dlink"><a target="_blank" href="{url}">$downaddr</a></td></tr>
</table>
EOT;
		$sql = "SELECT t.*,c.depth,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,c.target,c.color,
			ti.attachment,ti.remote,ti.thumb,ti.preview,ti.attachimg,
			f.voteup,f.votedown,f.voters,f.totalscore,f.credits,m.*
			FROM " . DB::table('threads') . " t
			LEFT JOIN " . DB::table('category') . " c USING(catid)
            LEFT JOIN " . DB::table('thread_image') . " ti USING(tid)
            LEFT JOIN " . DB::table('thread_field') . " f USING(tid)
            INNER JOIN " . DB::table('soft_thread') . " m ON t.tid=m.tid
		            $condition";
		$i = 0;
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$this->processThreadRowData($row, 0, 'Y-m-d');
			$row['index'] = ++$i;
			$row['alt'] = $i % 2 == 0 ? 2 : 1;
			$tmpstr = str_replace('{title}', $row['title'], $content);
			$tmpstr = str_replace(array('{index}', '{alt}'), array($row['index'], $row['alt']), $tmpstr);
			$tmpstr = str_replace('{softname}', $row['softname'], $tmpstr);
			$tmpstr = str_replace('{softversion}', $row['softversion'], $tmpstr);
			$tmpstr = str_replace('{thumburl}', $row['thumburl'], $tmpstr);
			$tmpstr = str_replace('{previewurl}', $row['previewurl'], $tmpstr);
			$tmpstr = str_replace(array('{url}', '{purl}'), array($row['url'], $row['purl']), $tmpstr);
			$tmpstr = str_replace(array('{size}', '{softtype}'), array($row['size'], $row['softtype']), $tmpstr);
			$tmpstr = str_replace(array('{license}', '{softlang}'), array($row['license'], $row['softlang']), $tmpstr);
			$tmpstr = str_replace(array('{date}', '{star}'), array($row['date'], $row['star']), $tmpstr);
			$tmpstr = str_replace(array('{hits}', '{runsystem}'), array($row['hits'], $row['runsystem']), $tmpstr);
			if(isset($tmparray[$row['tid']])){
				$tmparray[$row['tid']] = $tmpstr;
			}
		}
		if($i == 0) {
			return '';
		}else{
			foreach ($tmparray as $value) {
				if (!is_numeric($value) && !empty($value)) {
					$string .= $value;
				}
			}
			return $string . "</div>";
		}
	}
	
	public function parserContentThread($matches) {
		$tids = trim($matches[3]);
		if(empty($tids)) return '';
		$tmparray = array();
		$caption = trim($matches[2]);
		$condition = "WHERE t.status='1'";
		$string = '<div class="text-thread">';
		$string .= empty($caption) ? '' : "<div class=\"caption\">$caption</div>";
		if(strpos($tids, ',') && ($tids = implodeids($tids, null, 0, $tmparray))){
			$condition .= " AND t.tid IN($tids)";
			$tmparray = array_flip($tmparray);
		}elseif($tid = intval($tids)){
			$condition .= " AND t.tid='$tid'";
			$tmparray[$tid] = '';
		}else{
			return '';
		}
		$sql = "SELECT t.*,c.depth,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,c.target,c.color,
			ti.attachment,ti.remote,ti.thumb,ti.preview,ti.attachimg,
			f.voteup,f.votedown,f.voters,f.totalscore,f.credits
			FROM " . DB::table('threads') . " t
			LEFT JOIN " . DB::table('category') . " c USING(catid)
            LEFT JOIN " . DB::table('thread_image') . " ti USING(tid)
            LEFT JOIN " . DB::table('thread_field') . " f USING(tid)
            $condition";
        $i = 0;
        $string .= '<ul>';
        $query = DB::query($sql);
        while ($row = DB::fetch_array($query)) {
        	$this->processThreadRowData($row, 0, 'Y-m-d');
        	$row['index'] = ++$i;
        	$row['alt'] = $i % 2 == 0 ? 2 : 1;
        	$alt = $i % 2 == 0 ? ' class="alt"' : '';
        	if(isset($tmparray[$row['tid']])){
        		$tmparray[$row['tid']] = "<li$alt><em>{$row['date']}</em><a target=\"_blank\" href=\"{$row['url']}\">{$row['title']}</a></li>";
        	}
        }
		if($i == 0) {
			return '';
		}else{
			foreach ($tmparray as $value) {
				if (!is_numeric($value) && !empty($value)) {
					$string .= $value;
				}
			}
			return $string . "</ul></div>";
		}
	}
}
?>