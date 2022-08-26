<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : credit.php    2012-1-13
 */
!defined('IN_PHPCOM') && exit('Access denied');

function credit_updatemembercount($uids, $arrdata = array(), $checkgroup = TRUE, $operation = '', $relateid = 0) {
    if (empty($uids) || !is_array($arrdata) || empty($arrdata)) {
        return;
    }
    if ($operation && $relateid) {
        $writelog = TRUE;
        $log = array(
            'uid' => $uids,
            'operation' => $operation,
            'relateid' => $relateid,
            'dateline' => time(),
        );
    } else {
        $writelog = FALSE;
    }
    $data = array();
    foreach ($arrdata as $key => $val) {
        if (empty($val)) {
            continue;
        }
        $val = intval($val);
        if (strexists($key, array('money', 'prestige', 'currency', 'praise'))) {
            $data[$key] = $val;
            if ($writelog) {
                $log[$key] = $val;
            }
        } else {
            $data[$key] = $val;
        }
    }
    if ($writelog) {
        DB::insert('credit_log', $log);
    }
    if ($data) {
        $credit = & credit::instance();
        $credit->update_membercount($data, $uids, $checkgroup);
    }
}

?>
