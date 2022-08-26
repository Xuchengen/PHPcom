<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : thread_class.php  2012-12-17
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_thread_class($channel = 0) {
	unset($channel);
	$data = $classdata = array();
	$query = DB::query("SELECT t.*,c.codename,c.prefixurl FROM " . DB::table('thread_class') . " t
			LEFT JOIN " . DB::table('category') . " c ON c.catid=t.catid
			ORDER BY ordinal, classid");
	while ($row = DB::fetch_array($query)) {
		unset($row['ordinal']);
		$row['name'] = trim($row['name']);
		$row['icon'] = trim($row['icon']);
		$row['prefixurl'] = trim($row['prefixurl']);
		$row['codename'] = trim($row['codename']);
		if(empty($row['catid'])){
			$data[0][$row['chanid']][$row['classid']] = $row;
		}else{
			$data[$row['catid']][$row['classid']] = $row;
		}
		$classdata[$row['classid']] = $row;
	}

	phpcom_cache::save("thread_class", $data);
	phpcom_cache::save("threadclass", $classdata);
}
?>