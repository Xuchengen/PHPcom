<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : banip.php    2011-7-25 2:36:20
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_banip($channel = 0) {
    unset($channel);
	DB::query("DELETE FROM " . DB::table('banned') . " WHERE expiration<'" . TIMESTAMP . "'");
	$data = array();
	$query = DB::query("SELECT ip, expiration FROM " . DB::table('banned'));
	if (DB::num_rows($query)) {
		$data['expiration'] = 0;
		$data['regexp'] = $separator = '';
	}
	while ($row = DB::fetch_array($query)) {
		$data['expiration'] = !$data['expiration'] || $row['expiration'] < $data['expiration'] ? $row['expiration'] : $data['expiration'];
		$data['regexp'] .= $separator . str_replace(array('*', '.', ' '), array('\\w+', '\\.', ''), $row['ip']);
		$separator = '|';
	}

	phpcom_cache::save('banip', $data);

}

?>
