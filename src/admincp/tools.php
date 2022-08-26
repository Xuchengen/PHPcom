<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : tools.php  2012-10-15
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';
admin_header('menu_tools' . ($action ? "_$action" : ''));
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('tools');
if ($action == 'updatecache') {
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$adminhtml->form('m=tools&action=updatecache', 0, 'name="formupdatecache"');
		$adminhtml->table_header('tools_updatecache', 2);
		$adminhtml->table_td(array(array('tools_updatecache_tips', FALSE, 'colspan="2"')), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_td(array(
				array($adminhtml->checkbox(array('tools_updatecache_system','tools_updatecache_template', 'tools_updatecache_dataclear'), array('system', 'template', 'dataclear'), array('1', '1', '0')), FALSE, 'width="40%"'),
				array('<button class="button" type="submit" name="submit" value="yes">' . adminlang('tools_updatecache_button') . '</button>', FALSE)
		));
		$adminhtml->table_end('</form>');
		$adminhtml->form('m=tools&action=updatecount', 0, 'name="formupdatecount"');
		$adminhtml->table_header('tools_updatecount', 2);
		$adminhtml->table_td(array(
				array($adminhtml->radio(array('all' => 'tools_updatecount_all', 'member' => 'tools_updatecount_member', 'topical' => 'tools_updatecount_topical'), 'updatetype', 'all'), FALSE, 'width="40%"'),
				array('<button class="button" type="submit" name="submit" value="yes">' . adminlang('tools_updatecount_button') . '</button>', FALSE)
		));
		$adminhtml->table_end('</form>');
	}else{
		include_once PHPCOM_PATH . '/phpcom_version.php';
		$version = PHPCOM_VERSION;
		if(strcasecmp($version, phpcom::$setting['version']) != 0 ){
			phpcom_cache::savesetting('version', addslashes($version));
		}

		$start = isset(phpcom::$G['gp_start']) ? phpcom::$G['gp_start'] : null;
		$template_cache = isset(phpcom::$G['gp_template']) ? phpcom::$G['gp_template'] : null;
		$system_cache = isset(phpcom::$G['gp_system']) ? phpcom::$G['gp_system'] : null;
		$clear_data_cache = isset(phpcom::$G['gp_dataclear']) ? phpcom::$G['gp_dataclear'] : null;
		if($start == 'yes'){
			@set_time_limit(1000);
			if($template_cache){
				clearTemplateCache();
			}
			if(isset(phpcom::$G['cache']['urlrules']['main']['search'])){
				urlrules_upgrade();
			}
			if($system_cache){
				phpcom_cache::savesetting('upgrader', array());
				$cacheName = array('setting', 'channel', 'usergroup', 'urlrules', 'creditrules', 'softtest',
						'player', 'searchword', 'admingroup', 'badwords', 'banip', 'category',
						'adcategory', 'syscount', 'downserver', 'thread_class');
				foreach ($cacheName as $name){
					phpcom_cache::updater($name);
				}
				$channels = array();
				$sql = "SELECT channelid FROM " . DB::table('channel') . " WHERE type IN('system','expand') ORDER BY sortord";
				$query = DB::query($sql);
				while ($row = DB::fetch_array($query)) {
					$channels[] = $row['channelid'];
				}
				foreach ($channels as $chanid){
					phpcom_cache::updater('category', $chanid);
				}
			}
			if(!empty($clear_data_cache)){
				DataCache::clear();
				clearDataCacheDir();
			}
			admin_succeed('updatecache_succeed', "m=tools&action=updatecache");
		}else{
			$extra = '<input type="hidden" name="btnsubmit" value="yes" />';
			if($template_cache){
				$extra .= '<input type="hidden" name="template" value="1" />';
			}
			if($system_cache){
				$extra .= '<input type="hidden" name="system" value="1" />';
			}
			if($clear_data_cache){
				$extra .= '<input type="hidden" name="dataclear" value="1" />';
			}
			$msgargs = array(
					'form' => TRUE,
					'autosubmit' => TRUE,
					'loading' => TRUE,
					'action' => '?m=tools&action=updatecache&start=yes'
			);
			admin_showmessage('updatecache_message', null, $msgargs, $extra);
		}
	}
}elseif ($action == 'updatecount') {
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		admin_message('undefined_action');
	}else{
		if(isset(phpcom::$G['gp_updatetype']) && ($updatetype = phpcom::$G['gp_updatetype'])){
			if($updatetype == 'member'){
				phpcom_cache::updater('syscount', -1);
			}elseif($updatetype == 'topical'){
				phpcom_cache::updater('syscount', -2);
			}else{
				phpcom_cache::updater('syscount');
			}
		}
		admin_succeed('updatecount_succeed', "m=tools&action=updatecache");
	}
}elseif ($action == 'filecheck') {
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$adminhtml->form('m=tools&action=filecheck');
		$adminhtml->table_header('tools_filecheck');
		$adminhtml->table_td(array(array('tools_filecheck_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_td(array(
				array('<button class="button" type="submit" name="submit" value="yes" />' . adminlang('tools_filecheck_button') . '</button>', FALSE)
		));
		$adminhtml->table_end('</form>');
	}else{
		$start = isset(phpcom::$G['gp_start']) ? phpcom::$G['gp_start'] : null;
		if($start == 'yes'){
			@set_time_limit(1000);
			admin_succeed('filecheck_succeed', "m=tools&action=filecheck");
		}else{
			$extra = '<input type="hidden" name="btnsubmit" value="yes" />';
			$msgargs = array(
					'form' => TRUE,
					'autosubmit' => TRUE,
					'loading' => TRUE,
					'action' => '?m=tools&action=filecheck&start=yes'
			);
			admin_showmessage('filecheck_message', null, $msgargs, $extra);
		}
	}
}

admin_footer();

function clearTemplateCache() {
	$dir = PHPCOM_ROOT . '/data/template';
	if($d = @dir($dir)) {
		$dir = rtrim($dir, '/\ ');
		while($entry = $d->read()) {
			if ($entry !== '.' && $entry !== '..') {
				$filename = $dir.'/'.$entry;
				if(is_file($filename)) {
					@unlink($filename);
				}
			}
		}
		$d->close();
		@touch($dir.'/index.htm');
	}
}

function clearDataCacheDir($dir = null, $deleted = false) {
	$dir = $dir ? $dir : PHPCOM_ROOT . '/data/cache';
	foreach(glob($dir . '/*') as $file) {
		if(is_dir($file))
			clearDataCacheDir($file, true);
		else
			@unlink($file);
	}
	if (is_dir($dir) && $deleted) @rmdir( $dir );
}

function urlrules_upgrade()
{
	$data = array();
	if($rules = DB::fetch_first("SELECT ruleid, modules, rulename FROM " . DB::table('urlrules') . " WHERE ruleid='35' AND rulename='type'")){
		if($rules['rulename'] != 'type') return;
		if($rules['modules'] != 'soft') return;
		$i = 0;
		$types = array();
		$query = DB::query("SELECT * FROM " . DB::table('urlrules') . " WHERE 1=1 ORDER BY ruleid ASC");
		while ($row = DB::fetch_array($query)) {
			$i++;
			if($row['rulename'] == 'preview'){
				$types[$row['modules']]['type'] = $i;
				$i++;
			}
			if($row['rulename'] == 'type'){
				if(isset($types[$row['modules']]['type'])){
					$i--;
					$data[$types[$row['modules']]['type']] = $row;
				}else{
					$data[$i] = $row;
				}
			}else{
				$data[$i] = $row;
			}
		}
		ksort($data);
		foreach ($data as $ruleid => $value){
			$value['ruleid'] = $ruleid;
			$value = addslashes_array($value);
			DB::insert('urlrules', $value, false, true);
		}
	}
}
?>