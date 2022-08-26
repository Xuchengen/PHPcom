<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : cache.php  2012-10-9
 */
$fileDir = realpath(dirname(__FILE__) . '/..');
require $fileDir.'/src/inc/common.php';
$phpcom->init();

$lockfile = PHPCOM_ROOT . '/data/install.lock';
if(!file_exists($lockfile)) {
	@set_time_limit(1000);
	@ignore_user_abort(TRUE);
	update_syscache();
}

function update_syscache(){
	phpcom_cache::updater('setting');
	phpcom_cache::updater('channel');
	phpcom_cache::updater('usergroup');
	
	$cacheName = array('adcategory', 'urlrules', 'creditrules', 'softtest', 'player', 'searchword', 
			'syscount', 'downserver');
	foreach ($cacheName as $name){
		phpcom_cache::updater($name);
	}
	
	$data = array();
	$query = DB::query("SELECT * FROM " . DB::table('admingroup') . " ORDER BY admingid");
	while ($group = DB::fetch_array($query)) {
		$group['permission'] = @unserialize($group['permission']);
		$data[$group['admingid']] = $group;
	}
	phpcom_cache::save('admingroup', $data);
}
?>