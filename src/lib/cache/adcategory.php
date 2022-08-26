<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : adcategory.php  2012-10-21
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_adcategory($channel = 0) {
	unset($channel);
	$data = array();
	$query = DB::query("SELECT * FROM " . DB::table('adcategory') . " WHERE status='1'");
	while ($row = DB::fetch_array($query)) {
		$data[$row['name']] = $row;
	}
	
	phpcom_cache::save('adcategory', $data);
}
?>