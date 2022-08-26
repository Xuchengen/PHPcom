<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : receive_invite.php    2012-1-7
 */
define('CURRENT_SCRIPT', 'api');
require '../src/inc/common.php';
$paymentapi = empty(phpcom::$G['gp_attach']) || !preg_match('/^[a-z0-9]+$/i', phpcom::$G['gp_attach']) ? 'alipay' : 'tenpay';
if ($paymentapi == 'alipay') {
    $paymentapi = empty(phpcom::$G['gp_v_pstatus']) || !preg_match('/^[a-z0-9]+$/i', phpcom::$G['gp_v_pstatus']) ? 'alipay' : 'chinabank';
}
include loadlibfile($paymentapi, 'api');
$notifydata = trade_notifycheck('invite');
if ($notifydata['validator']) {
    $orderid = $notifydata['order_no'];
    $postprice = $notifydata['price'];
    $tradeno = $notifydata['trade_no'];
    $times = phpcom::$G['timestamp'];
    $userorder = DB::fetch_first("SELECT * FROM " . DB::table('userorder') . "  WHERE orderid='$orderid'");
    if ($userorder && floatval($postprice) == floatval($userorder['price']) &&
            ($paymentapi == 'tenpay' || $paymentapi == 'chinabank' || phpcom::$setting['pay_alipay']['account'] == $_REQUEST['seller_email'])) {
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
}
if ($notifydata['location']) {
    $url = phpcom::$G['siteurl'] . "apps/misc.php?mod=buyinvite&action=paysucceed&orderid=$orderid";
    if ($paymentapi == 'tenpay') {
        echo <<<EOT
<meta name="TENCENT_ONLINE_PAYMENT" content="China TENCENT">
<html>
<body>
<script language="javascript" type="text/javascript">
window.location.href='$url';
</script>
</body>
</html>
EOT;
    } else {
        phpcom::header("location: $url");
    }
} else {
    exit($notifydata['notify']);
}
?>
