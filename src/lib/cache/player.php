<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : player.php  2012-8-20
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_player($channel = 0) {
	unset($channel);
	$data = array();
	$query = DB::query("SELECT * FROM " . DB::table('video_player') . " ORDER BY playerid ASC");
	while ($row = DB::fetch_array($query)) {
		$data[$row['playerid']] = $row;
	}
	phpcom_cache::save('player', $data);
}
?>