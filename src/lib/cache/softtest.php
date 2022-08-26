<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : softtest.php    2011-12-14
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_softtest($channel = 0) {
    unset($channel);
	$data = array();
	$query = DB::query("SELECT testid,caption,url,color,icons,checked FROM " . DB::table('soft_test') . ' ORDER BY sortord');
	while ($row = DB::fetch_array($query)) {
        $data[$row['testid']] = $row;
	}

	phpcom_cache::save('softtest', $data);
}
?>
