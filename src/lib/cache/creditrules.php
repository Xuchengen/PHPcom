<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : creditrules.php    2012-1-12
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_creditrules($channel = 0) {
    unset($channel);
    $data = array();
    $query = DB::query("SELECT * FROM " . DB::table('credit_rules'));

    while ($row = DB::fetch_array($query)) {
        if (strtoupper(CHARSET) != 'UTF-8') {
            $row['rulenameuni'] = urlencode(phpcom::iconv($row['rulename'], CHARSET, 'UTF-8', TRUE));
        } else {
            $row['rulenameuni'] = $row['rulename'];
        }
        $data[$row['operation']] = $row;
    }
    phpcom_cache::save('creditrules', $data);
}

?>
