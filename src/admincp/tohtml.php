<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : tohtml.php  2012-9-14
 */
!defined('IN_ADMINCP') && exit('Access denied');

phpcom::$G['lang']['admin'] = 'misc';
$chanid = isset(phpcom::$G['gp_chanid']) ? intval(phpcom::$G['gp_chanid']) : 0;
if ($action == 'index') {
	$type = isset(phpcom::$G['gp_type']) ? trim(phpcom::$G['gp_type']) : 'index';
	if(isset(phpcom::$G['gp_ok']) && phpcom::$G['gp_ok']){
		echo adminlang('tohtml_write_complete');
	}elseif(isset(phpcom::$G['gp_start']) && phpcom::$G['gp_start']){
		$channels = phpcom::$G['channel'];
		$key = md5(date('YmdH') . phpcom::$config['security']['key']);
		echo "<script src=\"apps/html.php?key=$key\"></script>";
		if($type == 'indexall'){
			foreach ($channels as $mod => $channel) {
				if (is_numeric($mod) && !$channel['closed'] && $channel['type'] != 'menu') {
					$url = 'apps/html.php?channel=' . $channel['codename'];
					echo "<script src=\"$url&key=$key\"></script>";
				}
			}
		}
		httpRefresh("m=tohtml&action=index&chanid=$chanid&ok=1&type=$type");
	}else{
		httpRefresh("m=tohtml&action=index&chanid=$chanid&start=1&type=$type");
		echo adminlang('tohtml_write_starting', array('page' => 0));
	}
}elseif($action == 'content'){
	if(phpcom::$setting['htmlstatus'] != 1){
		exit(adminlang('tohtml_nonsupport'));
	}
	$type = isset(phpcom::$G['gp_type']) ? trim(phpcom::$G['gp_type']) : 'all';
	$completed = true;
	if(isset(phpcom::$G['gp_ok']) && phpcom::$G['gp_ok']){
		echo adminlang('tohtml_write_complete');
	}elseif(isset(phpcom::$G['gp_start']) && phpcom::$G['gp_start']){
		$condition = $sql = "";
		$condition = $chanid ? " AND t.chanid='$chanid'" : " AND t.chanid>'0'";
		$count = isset(phpcom::$G['gp_count']) ? intval(phpcom::$G['gp_count']) : 0;
		if($type == 'update'){
			$count = $count ? $count : DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " t
					INNER JOIN " . DB::table('thread_field') . " f ON f.tid=t.tid
					WHERE t.status='1'$condition AND f.isupdate='1'");
			$sql = "SELECT t.tid, t.chanid, t.title, t.status FROM " . DB::table('threads') . " t
					INNER JOIN " . DB::table('thread_field') . " f ON f.tid=t.tid
					WHERE t.status='1'$condition AND f.isupdate='1' ORDER BY t.dateline DESC";
		}else{
			$timestamp = 0;
			if($type == 'today'){
				$timestamp = strtotime(date('Ymd'));
			}elseif($type == 'yesterday'){
				$timestamp = strtotime(date('Ymd')) - 86400;
			}elseif($type == 'week'){
				$timestamp = strtotime(date('Ymd')) - 604800;
			}elseif($type == 'month'){
				$timestamp = mktime(0, 0, 0, date('m'), 1, date('Y'));
			}elseif($type == 'year'){
				$timestamp = mktime(0, 0, 0, 1, 1, date('Y'));
			}else{
				DB::query("UPDATE " . DB::table('thread_field') . " SET isupdate='1' WHERE isupdate='0'");
			}
			$condition = $chanid ? " AND chanid='$chanid'" : " AND chanid>'0'";
			$condition .= $timestamp ? " AND dateline>'$timestamp'" : '';
			$count = $count ? $count : DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " WHERE status='1' $condition");
			$sql = "SELECT tid, chanid, title FROM " . DB::table('threads') . "
				WHERE status='1' $condition";
		}
		$pagesize = 50;
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($page)));
		$pagestart = $type == 'update' ? 0 : floor(($pagenow - 1) * $pagesize);
		$query = DB::query(DB::buildlimit($sql, $pagesize, $pagestart));
		$key = md5(date('YmdH') . phpcom::$config['security']['key']);
		$channel = phpcom::$G['channel'];
		echo adminlang('tohtml_write_starting', array('page' => $pagenow));
		flush();
		ob_flush();
		$tids = array();
		while ($row = DB::fetch_array($query)) {
			$completed = false;
			$tids[] = $row['tid'];
			$url = "apps/html.php?module={$channel[$row['chanid']]['modules']}&action=view&tid=" . $row['tid'];
			echo "<script src=\"$url&key=$key\"></script>";
		}
		if($tids && ($uptids = implodeids($tids))){
			DB::update('thread_field', array('isupdate' => 0), "tid IN($uptids) AND isupdate='1'");
		}
		if($pagenow >= $pagecount || $completed){
			if($chanid && !empty($channel[$chanid]['codename'])){
				$url = 'apps/html.php?channel=' . $channel[$chanid]['codename'];
				echo "<script src=\"$url&key=$key\"></script>";
			}
			httpRefresh("m=tohtml&action=content&chanid=$chanid&type=$type&ok=1&count=$count");
		}else{
			httpRefresh("m=tohtml&action=content&chanid=$chanid&type=$type&count=$count&start=1&page=" . ($pagenow + 1));
		}
	}else{
		httpRefresh("m=tohtml&action=content&chanid=$chanid&type=$type&start=1&page=1");
		echo adminlang('tohtml_write_starting', array('page' => 0));
	}
}elseif($action == 'special'){
	if(phpcom::$setting['htmlstatus'] != 1){
		exit(adminlang('tohtml_nonsupport'));
	}
	$completed = true;
	$type = isset(phpcom::$G['gp_type']) ? trim(phpcom::$G['gp_type']) : 'all';
	$chanid = $chanid ? $chanid : 4;
	if(isset(phpcom::$G['gp_ok']) && phpcom::$G['gp_ok']){
		echo adminlang('tohtml_write_complete');
	}elseif(isset(phpcom::$G['gp_start']) && phpcom::$G['gp_start']){
		$condition = $sql = "";
		$condition = " AND t.chanid='$chanid'";
		$count = isset(phpcom::$G['gp_count']) ? intval(phpcom::$G['gp_count']) : 0;
		if($type == 'update'){
			$count = $count ? $count : DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " t
					INNER JOIN " . DB::table('thread_field') . " f ON f.tid=t.tid
					WHERE t.status='1'$condition AND f.isupdate='1'");
			$sql = "SELECT t.tid, t.chanid, t.title, t.status FROM " . DB::table('threads') . " t
					INNER JOIN " . DB::table('thread_field') . " f ON f.tid=t.tid
					WHERE t.status='1'$condition AND f.isupdate='1'";
		}else{
			$timestamp = 0;
			if($type == 'today'){
				$timestamp = strtotime(date('Ymd'));
			}elseif($type == 'yesterday'){
				$timestamp = strtotime(date('Ymd')) - 86400;
			}elseif($type == 'week'){
				$timestamp = strtotime(date('Ymd')) - 604800;
			}elseif($type == 'month'){
				$timestamp = mktime(0, 0, 0, date('m'), 1, date('Y'));
			}elseif($type == 'year'){
				$timestamp = mktime(0, 0, 0, 1, 1, date('Y'));
			}else{
				DB::query("UPDATE " . DB::table('thread_field') . " SET isupdate='1' WHERE isupdate='0'");
			}
			$condition = $chanid ? " AND chanid='$chanid'" : " AND chanid='4'";
			$condition .= $timestamp ? " AND dateline>'$timestamp'" : '';
			$count = $count ? $count : DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " WHERE status='1' $condition");
			$sql = "SELECT tid, chanid, title FROM " . DB::table('threads') . "
			WHERE status='1' $condition";
		}
		$pagesize = 50;
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($page)));
		$pagestart = $type == 'update' ? 0 : floor(($pagenow - 1) * $pagesize);
		$query = DB::query(DB::buildlimit($sql, $pagesize, $pagestart));
		$key = md5(date('YmdH') . phpcom::$config['security']['key']);
		$channel = phpcom::$G['channel'][$chanid];
		echo adminlang('tohtml_write_starting', array('page' => $pagenow));
		flush();
		ob_flush();
		$tids = array();
		while ($row = DB::fetch_array($query)) {
			$completed = false;
			$tids[] = $row['tid'];
			$url = "apps/html.php?module=special&action=view&tid=" . $row['tid'];
			echo "<script src=\"$url&key=$key\"></script>";
			special_class_tohtml($row['tid'], $key);
		}
		
		if($tids && ($uptids = implodeids($tids))){
			DB::update('thread_field', array('isupdate' => 0), "tid IN($uptids) AND isupdate='1'");
		}
		if($pagenow >= $pagecount || $completed){
			$url = 'apps/html.php?channel=' . (empty($channel['codename']) ? 'special' : $channel['codename']);
			echo "<script src=\"$url&key=$key\"></script>";
			httpRefresh("m=tohtml&action=special&chanid=$chanid&type=$type&ok=1&count=$count");
		}else{
			httpRefresh("m=tohtml&action=special&chanid=$chanid&type=$type&count=$count&start=1&page=" . ($pagenow + 1));
		}
	}else{
		httpRefresh("m=tohtml&action=special&chanid=$chanid&type=$type&start=1&page=1");
		echo adminlang('tohtml_write_starting', array('page' => 0));
	}
}elseif($action == 'thread'){
	if(phpcom::$setting['htmlstatus'] != 1){
		exit(adminlang('tohtml_nonsupport'));
	}
	$completed = true;
	$catid = isset(phpcom::$G['gp_catid']) ? intval(phpcom::$G['gp_catid']) : 0;
	if(isset(phpcom::$G['gp_ok']) && phpcom::$G['gp_ok']){
		echo adminlang('tohtml_write_complete');
	}elseif(isset(phpcom::$G['gp_start']) && phpcom::$G['gp_start']){
		$condition = $sql = "";
		$depth = isset(phpcom::$G['gp_depth']) ? intval(phpcom::$G['gp_depth']) : 0;
		$basic = isset(phpcom::$G['gp_basic']) ? intval(phpcom::$G['gp_basic']) : 0;
		$count = isset(phpcom::$G['gp_count']) ? intval(phpcom::$G['gp_count']) : 0;
		$pagenum = isset(phpcom::$G['gp_pagesize']) ? intval(phpcom::$G['gp_pagesize']) : 0;
		$channelid = isset(phpcom::$G['gp_cid']) ? intval(phpcom::$G['gp_cid']) : 1;
		if($basic){
			$count = 1;
		}else{
			$count = $count ? $count : getThreadCount($catid, $depth, $channelid, $pagenum);
		}
		$pagesize = 50;
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$pageend = min($count, $pagestart + $pagesize);
		echo adminlang('tohtml_write_starting', array('page' => $catid));
		flush();
		ob_flush();
		$key = md5(date('YmdH') . phpcom::$config['security']['key']);
		$channel = phpcom::$G['channel'][$channelid];
		$modules = $channel['modules'];
		for($p = $pagestart; $p<$pageend; $p++){
			$completed = false;
			$url = "apps/html.php?module=$modules&action=list&catid=$catid&page=".($p + 1);
			echo "<script src=\"$url&key=$key\"></script>";
		}
		if($pagenow >= $pagecount || $completed){
			$url = 'apps/html.php?channel=' . (empty($channel['codename']) ? 'special' : $channel['codename']);
			echo "<script src=\"$url&key=$key\"></script>";
			httpRefresh("m=tohtml&action=thread&chanid=$chanid&catid=$catid");
		}else{
			httpRefresh("m=tohtml&action=thread&chanid=$chanid&cid=$channelid&catid=$catid&basic=$basic&depth=$depth&count=$count&start=1&pagesize=$pagenum&page=" . ($pagenow + 1));
		}
	}else{
		echo adminlang('tohtml_write_starting', array('page' => 0));
		$condition = $chanid ? "chanid='$chanid' AND" : "";
		if($category = DB::fetch_first("SELECT catid, rootid, chanid, depth, basic, pagesize FROM " . DB::table('category') . "
				WHERE $condition catid>'$catid' ORDER BY catid LIMIT 1")){
				phpcom_cache::load('category');
				if($category['depth'] && empty($category['pagesize']) && isset(phpcom::$G['cache']['category'][$category['rootid']])){
					$category['pagesize'] = phpcom::$G['cache']['category'][$category['rootid']]['pagesize'];
				}
			httpRefresh("m=tohtml&action=thread&chanid=$chanid&cid={$category['chanid']}&start=1&page=1&catid={$category['catid']}&basic={$category['basic']}&depth={$category['depth']}&pagesize={$category['pagesize']}");
		}else{
			httpRefresh("m=tohtml&action=thread&chanid=$chanid&ok=1");
		}
	}
	
}elseif($action == 'sitemap'){
	$completed = true;
	$type = isset(phpcom::$G['gp_type']) ? trim(phpcom::$G['gp_type']) : 'google';
	if(isset(phpcom::$G['gp_ok']) && phpcom::$G['gp_ok']){
		echo adminlang('tohtml_write_sitemap_complete');
	}elseif(isset(phpcom::$G['gp_start']) && phpcom::$G['gp_start']){
		$condition = "t.status='1' AND t.chanid>'0'";
		$count = isset(phpcom::$G['gp_count']) ? intval(phpcom::$G['gp_count']) : 0;
		$count = $count ? $count : DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " t WHERE $condition");
		$pagesize = $count > 36000 ? 36000 : $count;
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$sql = DB::buildlimit("SELECT t.tid,t.chanid,t.catid,t.rootid,t.htmlname,t.url,t.dateline,c.codename,c.prefix,c.prefixurl FROM " . DB::table('threads') . " t
				LEFT JOIN " . DB::table('category') . " c USING(catid)
				WHERE $condition ORDER BY t.dateline DESC", $pagesize, $pagestart);
		$query = DB::query($sql);
		$websiteurl = trim(phpcom::$setting['website'], " /\\") . '/';
		$urls = array($websiteurl);
		while ($row = DB::fetch_array($query)) {
			$completed = false;
			$urlargs = array('chanid' => $row['chanid'], 'catdir' => $row['codename'], 'tid' => $row['tid'],
					'date' => $row['dateline'], 'cid' => $row['catid'], 'catid' => $row['catid'], 'page' => 1);
			if (empty(phpcom::$G['channel'][$row['chanid']]['domain']) && empty($row['url']) && empty($row['prefixurl'])) {
				$row['domain'] = $websiteurl;
				$row['htmlname'] = empty($row['htmlname']) ? $row['tid'] : $row['htmlname'];
				if(phpcom::$setting['htmlstatus']){
					$urlargs['tid'] = $row['htmlname'];
				}
				$urlargs['name'] = $row['htmlname'];
				if(!empty($row['prefix'])){
					$urlargs['prefix'] = trim($row['prefix']);
				}
				$urls[] = geturl('threadview', $urlargs, $row['domain']);
			}
		}
		if($pagenow == 1){
			foreach (phpcom::$G['channel'] as $mod => $channel) {
				if (is_numeric($mod) && !$channel['closed'] && $channel['type'] != 'menu' && empty($channel['domain'])) {
					$domain = $channel['domain'] ? '' : $channel['codename'];
					$channel['domain'] = $channel['domain'] ? $channel['domain'] . '/' : $websiteurl;
					$codename = $channel['type'] == 'system' ? '' : $channel['codename'];
					$urls[] = geturl('index', array(
							'module' => $channel['modules'],
							'domain' => $domain,
							'action' => $codename,
							'channel' => $channel['codename'],
							'channelid' => $channel['channelid']
					),$channel['domain']);
				}
			}
			$urls[] = geturl('index', array('sid' => 0, 'name' => 'index', 'action' => ''), $websiteurl, 'special');
			$sql = "SELECT catid, chanid, rootid, basic, codename, child, caturl, prefix, prefixurl
			 FROM " . DB::table('category') . " WHERE chanid>'0' ORDER BY sortord, catid";
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				if (empty(phpcom::$G['channel'][$row['chanid']]['domain']) && empty($row['caturl']) && empty($row['prefixurl'])) {
					$row['domain'] = $websiteurl;
				
					$urlargs = array('chanid' => $row['chanid'], 'catdir' => $row['codename'],
						'name' => $row['codename'], 'catid' => $row['catid'], 'cid' => $row['catid'], 'page' => 1);
					if(!empty($row['prefix'])){
						$urlargs['prefix'] = trim($row['prefix']);
					}
					$urls[] = geturl($row['basic'] ? 'category' : 'threadlist', $urlargs, $row['domain']);
				}
			}
			$key = md5(date('YmdH') . phpcom::$config['security']['key']);
			echo "<script src=\"apps/html.php?action=sitemap&key=$key\"></script>";
		}
		if(!$completed){
			createGoogleSitemap($urls, $pagenow);
		}
		if($pagenow >= $pagecount || $completed){
			httpRefresh("m=tohtml&action=sitemap&chanid=$chanid&type=$type&ok=1&count=$count");
		}else{
			httpRefresh("m=tohtml&action=sitemap&chanid=$chanid&type=$type&count=$count&start=1&page=" . ($pagenow + 1));
		}
	}else{
		$count = isset(phpcom::$G['gp_count']) ? intval(phpcom::$G['gp_count']) : 0;
		httpRefresh("m=tohtml&action=sitemap&chanid=$chanid&type=$type&start=1&page=1&count=$count");
		echo adminlang('tohtml_write_sitemap_starting', array('page' => 0));
	}
}elseif($action){
	echo adminlang('nonsupport_features');
}else{
	admin_header('menu_tohtml');
	$adminhtml = phpcom_adminhtml::instance();
	$adminhtml->activetabs('topic');
	$adminhtml->form("m=tohtml&action=thread", null, 'name="formThread" target="_framethread"');
	$adminhtml->table_header('tohtml_thread_write');
	$adminhtml->table_td(array(
			array(getFrameBlank('_framethread'), TRUE, 'colspan="2"')
	));
	$selectArray = array(0 => adminlang('tohtml_channel_all'));
	$specialChannel = array();
	$sql = "SELECT channelid, channelname, modules FROM " . DB::table('channel') . " WHERE type IN('system','expand') ORDER BY sortord";
	$query = DB::query($sql);
	while ($row = DB::fetch_array($query)) {
		if($row['modules'] == 'special'){
			$specialChannel[$row['channelid']] = $row['channelname'];
		}
		$selectArray[$row['channelid']] = $row['channelname'];
	}
	$adminhtml->table_td(array(
			array($adminhtml->submit_button('tohtml_submit', 'htmlSubmit', 'button') .
					$adminhtml->button('tohtml_stop','B2',"_framethread.location.href='about:blank'"), TRUE, 'width="25%" noWrap="noWrap"'),
			array($adminhtml->select($selectArray, 'chanid', $chanid, 'class="select t50"'), TRUE)
	));
	$adminhtml->table_end('</form>');
	
	$adminhtml->form("m=tohtml&action=content", null, 'name="formConten" target="_framecontent"');
	$adminhtml->table_header('tohtml_content_write');
	$adminhtml->table_td(array(
			array(getFrameBlank('_framecontent'), TRUE, 'colspan="2"')
	));
	$adminhtml->table_td(array(
			array($adminhtml->submit_button('tohtml_submit', 'htmlSubmit', 'button') .
				$adminhtml->button('tohtml_stop','B2',"_framecontent.location.href='about:blank'"), TRUE, 'width="25%" noWrap="noWrap"'),
			array($adminhtml->radio(array('all' => 'tohtml_type_all', 'update' => 'tohtml_type_update',
					'today' => 'tohtml_type_today', 'yesterday' => 'tohtml_type_yesterday', 'week' => 'tohtml_type_week',
					'month' => 'tohtml_type_month', 'year' => 'tohtml_type_year'), 'type', 'update') . ' ' .
					$adminhtml->select($selectArray, 'chanid', $chanid, 'class="select t10"'), FALSE)
	));
	$adminhtml->table_end('</form>');
	
	$adminhtml->form("m=tohtml&action=index&chanid=$chanid", null, 'name="formIndex" target="_frameindex"');
	$adminhtml->table_header('tohtml_index_write');
	$adminhtml->table_td(array(
			array(getFrameBlank('_frameindex'), TRUE, 'colspan="2"')
	));
	$adminhtml->table_td(array(
			array($adminhtml->submit_button('tohtml_submit', 'htmlSubmit', 'button') .
					$adminhtml->button('tohtml_stop','B2',"_frameindex.location.href='about:blank'"), TRUE, 'width="25%" noWrap="noWrap"'),
			array($adminhtml->radio(array('index' => 'tohtml_type_index', 'indexall' => 'tohtml_type_indexall'), 'type', 'index'), FALSE)
	));
	$adminhtml->table_end('</form>');
	
	$adminhtml->form("m=tohtml&action=special&chanid=$chanid", null , 'name="formSpecial" target="_framespecial"');
	$adminhtml->table_header('tohtml_special_write');
	$adminhtml->table_td(array(
			array(getFrameBlank('_framespecial'), TRUE, 'colspan="2"')
	));
	$adminhtml->table_td(array(
			array($adminhtml->submit_button('tohtml_submit', 'htmlSubmit', 'button') .
					$adminhtml->button('tohtml_stop','B2',"_frametopical.location.href='about:blank'"), TRUE, 'width="25%" noWrap="noWrap"'),
			array($adminhtml->radio(array('all' => 'tohtml_type_all', 'today' => 'tohtml_type_today', 
					'yesterday' => 'tohtml_type_yesterday', 'week' => 'tohtml_type_week', 
					'month' => 'tohtml_type_month', 'year' => 'tohtml_type_year'), 'type', 'today') . ' ' .
					$adminhtml->select($specialChannel, 'chanid', $chanid, 'class="select t10"'), FALSE)
	));
	$adminhtml->table_end('</form>');
	
	$adminhtml->form("m=tohtml&action=sitemap&chanid=$chanid", null , 'name="formsitemap" target="_framesitemap"');
	$adminhtml->table_header('tohtml_sitemap_write');
	$adminhtml->table_td(array(
			array(getFrameBlank('_framesitemap'), TRUE, 'colspan="2"')
	));
	$adminhtml->table_td(array(
			array($adminhtml->submit_button('tohtml_sitemap_submit', 'htmlSubmit', 'button') .
					$adminhtml->button('tohtml_sitemap_stop','B2',"_frameother.location.href='about:blank'"), TRUE, 'width="25%" noWrap="noWrap"'),
			array($adminhtml->radio(array('google' => 'tohtml_sitemap_google'), 'type', 'google') .
					adminlang('tohtml_sitemap_count') .
					'<input class="input t10" size="1" name="count" type="text" value="36000" />', FALSE)
	));
	$adminhtml->table_end('</form>');
	admin_footer();
}

function getThreadCount($catid, $depth = 0, $chanid = 0, $pagesize = 0){
	$condition = "status='1' AND chanid='$chanid'";
	$condition .= $depth ? " AND catid='$catid'" : " AND rootid='$catid'";
	if($count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " WHERE $condition")){
		$pagesize = $pagesize ? $pagesize : intval(phpcom::$G['channel'][$chanid]['pagesize']);
		$pagecount = @ceil($count / $pagesize);
		return $pagecount;
	}
	return 0;
}

function httpRefresh($url, $sec = 0.3)
{
	$url = ADMIN_SCRIPT . "?$url";
	echo "<meta http-equiv=\"refresh\" content=\"$sec;url=$url\" />";
}

function getFrameBlank($name = '_frameblank', $heigth = '30')
{
	return "<iframe style=\"display:;width:100%;height:{$heigth}px;\" name=\"$name\" scrolling=\"no\" frameborder=\"0\" src=\"about:blank\" allowTransparency=\"true\"></iframe>";
}

function special_class_tohtml($tid, $key) {
	$query = DB::query("SELECT classid, pagesize FROM " . DB::table('special_class') . " WHERE tid='$tid'");
	while ($row = DB::fetch_array($query)) {
		$url = "apps/html.php?module=special&action=topiclist&tid=$tid&classid={$row['classid']}&page=1";
		echo "<script src=\"$url&key=$key\"></script>";
		special_class_page_tohtml($row['classid'], $row['pagesize'], $tid, $key);
	}
}

function special_class_page_tohtml($classid, $pagesize, $tid, $key) {
	$pagesize = $pagesize >= 0 ? $pagesize : 50;
	if($pagesize == -1){
		return true;
	}
	$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('special_data') . " WHERE classid='$classid'");
	$pagecount = @ceil($count / $pagesize);
	if($pagecount >= 2){
		for($i = 2; $i <= $pagecount; $i++){
			$url = "apps/html.php?module=special&action=topiclist&tid=$tid&classid=$classid&page=$i";
			echo "<script src=\"$url&key=$key\"></script>";
		}
	}
	
}

function createGoogleSitemap($urls, $page = 1)
{
	$xml = new SimpleXMLExtended('<?xml version="1.0" encoding="UTF-8"?><urlset></urlset>', null, false);
	$xml->addAttribute("xmlns", "http://www.sitemaps.org/schemas/sitemap/0.9");
	$filename = $page == 1 ? 'sitemap.xml' : "sitemap_$page.xml";
	$date = date('Y-m-d');
	foreach($urls as $key => $url){
		$node = $xml->addChild('url');
		$node->addChild('loc', $url);
		if($key == 0){
			$node->addChild('priority', '0.8');
			$node->addChild('lastmod', $date);
			$node->addChild('changefreq', 'always');
		}else{
			$node->addChild('priority', '0.7');
			$node->addChild('lastmod', $date);
			$node->addChild('changefreq', 'daily');
		}
	}
	$xml->save($filename);
}
function createGoogleSitemap2($urls, $page = 1)
{
	$s = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
	$s .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
	$filename = $page == 1 ? 'sitemap.xml' : "sitemap_$page.xml";
	$date = date('Y-m-d');
	if (@$fp = fopen($filename, 'w')) {
		fwrite($fp, $s);
		foreach($urls as $key => $url){
			if($key == 0){
				fwrite($fp, "<url>\n\t<loc>$url</loc>\n\t<priority>0.8</priority>\n\t<lastmod>$date</lastmod>\n\t<changefreq>always</changefreq>\n</url>\n");
			}else{
				fwrite($fp, "<url>\n\t<loc>$url</loc>\n\t<priority>0.7</priority>\n\t<lastmod>$date</lastmod>\n\t<changefreq>daily</changefreq>\n</url>\n");
			}
		}
		fwrite($fp, '</urlset>');
		fclose($fp);
	}
}
?>