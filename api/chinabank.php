<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : chinabank.php    2012-1-7
 */
define('CURRENT_SCRIPT', 'api');
require '../src/inc/common.php';
include loadlibfile('chinabank', 'api');
$notifydata = trade_notifycheck('credit');
if ($notifydata['validator']) {
    $orderid = $notifydata['order_no'];
    $postprice = $notifydata['price'];
    $tradeno = $notifydata['trade_no'];
    $tradetype = $notifydata['trade_type'];
    $times = phpcom::$G['timestamp'];
    if ($tradetype == 'credit') {
        $userorder = DB::fetch_first("SELECT o.*, m.username FROM " . DB::table('userorder') . " o LEFT JOIN " . DB::table('members') . " m USING (uid) WHERE o.orderid='$orderid'");
        if ($userorder && floatval($postprice) == floatval($userorder['price'])) {
            if ($userorder['status'] == 1) {
                DB::query("UPDATE " . DB::table('userorder') . " SET status='2', tradeno='$tradeno', payapi='chinabank', ordertime='$times' WHERE orderid='$orderid'");
                DB::query("DELETE FROM " . DB::table('userorder') . " WHERE status=1 AND ordertime<'$times'-120*86400");
                update_membercount($userorder['uid'], array(phpcom::$setting['creditstrans']['field'] => $userorder['amount']), 1, 'ATU', $userorder['uid']);
                update_creditbyaction('tradesuccess', $userorder['uid']);
                $ordertime = fmdate($userorder['ordertime'], 'Y-m-d H:i:s');
                addnotification($userorder['uid'], 'credit', 'add_funds_complete', array(
                    'orderid' => $userorder['orderid'],
                    'price' => $userorder['price'],
                    'details' => phpcom::$setting['creditstrans']['title'] . ' ' . $userorder['amount'] . ' ' . phpcom::$setting['creditstrans']['unit']
                ));
            }
        }
    } elseif ($tradetype == 'invite') {
        $userorder = DB::fetch_first("SELECT * FROM " . DB::table('userorder') . "  WHERE orderid='$orderid'");
        if ($userorder && floatval($postprice) == floatval($userorder['price'])) {
            if ($userorder['status'] == 1) {
                DB::query("UPDATE " . DB::table('userorder') . " SET status='2', tradeno='$tradeno', payapi='chinabank', ordertime='$times' WHERE orderid='$orderid'");
                $invitecodes = $codelist = array();
                $dateline = TIMESTAMP;
                $type = 1;
                $uid = $userorder['uid'];
                if (empty($uid)) {
                    $type = 0;
                }
                $username = $userorder['buyer'];
                for ($index = 0; $index < $userorder['amount']; $index++) {
                    $code = strtolower(random(16, FALSE, 15));
                    $codelist[] = $code;
                    $invitecodes[] = "('$uid','$code','$username','0','$dateline', '$type', '$orderid')";
                }
                if ($invitecodes) {
                    DB::query("INSERT INTO " . DB::table('invitecode') . " (`uid`, `code`, `inviter`, `groupid`, `dateline`, `type`, `orderid`) VALUES " . implode(',', $invitecodes));
                }
                DB::query("DELETE FROM " . DB::table('userorder') . " WHERE status=1 AND ordertime<'$times'-120*86400");
                if (!function_exists('sendmail')) {
                    include loadlibfile('mail', 'lib');
                }
                $mail_subject = phpcom::$setting['webname'] . ' - ' . lang('misc', 'invite_payment');
                $mail_message = phpcom::$setting['invite']['emaintext'];
                $mail_message = str_replace('{orderid}', $userorder['orderid'], $mail_message);
                $mail_message = str_replace('{invitecode}', implode('<br />', $codelist), $mail_message);
                $mail_message = str_replace('{siteurl}', phpcom::$G['siteurl'], $mail_message);
                $mail_message = str_replace('{webname}', phpcom::$setting['webname'], $mail_message);
                $mail_message = str_replace('{username}', $userorder['buyer'], $mail_message);
                $mail_message = str_replace('{email}', $userorder['email'], $mail_message);
                sendmail($userorder['email'], $mail_subject, $mail_message);
            }
        }
    } elseif ($tradetype == 'trade') {
        
    }
}
echo 'ok';
?>
