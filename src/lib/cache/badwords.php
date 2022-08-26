<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : badwords.php    2011-12-9
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_badwords($channel = 0) {
    unset($channel);
    $data = array();
    $query = DB::query("SELECT id, type, find, replacement, pattern FROM " . DB::table('badwords'));
    while ($row = DB::fetch_array($query)) {
        $k = $row['id'];
        if ($row['type'] == 1) {
            $data['verify'][] = $row['replacement'];
        }elseif ($row['type'] == 2) {
            $data['ban'][] = $row['replacement'];
        }else{
            $data['pattern'][$k] = $row['pattern'];
            $data['replace'][$k] = $row['replacement'];
        }
    }

    phpcom_cache::save('badwords', $data);
}

?>
