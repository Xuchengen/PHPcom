<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : usergroup.php    2011-5-8 22:26:16
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_usergroup($groupid = 0) {
    $data = array();
    $groupdata = array();
    $condition = '1=1';
    if ($groupid > 0) $condition = "groupid=$groupid";
    $sql = "SELECT * FROM " . DB::table('usergroup') . " WHERE $condition ORDER BY groupid";
    $query = DB::query($sql);
    while ($row = DB::fetch_array($query)) {
        if ($groupid <= 0) {
            $groupdata[$row['groupid']] = $row;
            unset($groupdata[$row['groupid']]['setting']);
        }
        $data = $row;
        $data['setting'] = '';
        $data = array_merge($data, unserialized($row['setting']));
        $data['attachext'] = $data['attachext'] ? trim(str_replace(array(';', '|', ' '), array(',', ',', ''), $data['attachext']), ',') : '';
        $data['attachext'] = strtolower($data['attachext']);
        $data['maxattachsize'] = max(0, $data['maxattachsize']);
        phpcom_cache::save('usergroup_' . $row['groupid'], $data);
        unset($data);
    }
    unset($query);
    if ($groupdata && $groupid <= 0) {
        phpcom_cache::save('usergroup', $groupdata);
        unset($groupdata);
    }
}

?>
