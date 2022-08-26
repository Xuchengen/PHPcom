<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : userstats.php    2012-1-14
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_userstats($channel = 0) {
    unset($channel);
    $total = DB::result_first("SELECT COUNT(*) FROM " . DB::table('members'));
    $username = DB::result_first("SELECT username FROM " . DB::table('members') . " ORDER BY regdate DESC LIMIT 1");
    phpcom_cache::save('userstats', array('total' => $total, 'username' => $username));
}

?>
