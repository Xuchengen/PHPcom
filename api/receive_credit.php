<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : receive_credit.php    2012-1-7
 */
define('CURRENT_SCRIPT', 'api');
require '../src/inc/common.php';

$paymentapi = empty(phpcom::$G['gp_attach']) || !preg_match('/^[a-z0-9]+$/i', phpcom::$G['gp_attach']) ? 'alipay' : 'tenpay';
if ($paymentapi == 'alipay') {
    $paymentapi = empty(phpcom::$G['gp_v_pstatus']) || !preg_match('/^[a-z0-9]+$/i', phpcom::$G['gp_v_pstatus']) ? 'alipay' : 'chinabank';
}

include loadlibfile($paymentapi, 'api');
$notifydata = trade_notifycheck('credit');
if ($notifydata['validator']) {
    $orderid = $notifydata['order_no'];
    $postprice = $notifydata['price'];
    $tradeno = $notifydata['trade_no'];
    $userorder = DB::fetch_first("SELECT o.*, m.username FROM " . DB::table('userorder') . " o LEFT JOIN " . DB::table('members') . " m USING (uid) WHERE o.orderid='$orderid'");
    if ($userorder && floatval($postprice) == floatval($userorder['price'])) {
        if ($userorder['status'] == 1) {
            $times = phpcom::$G['timestamp'];
            DB::query("UPDATE " . DB::table('userorder') . " SET status='2', tradeno='$tradeno', payapi='$paymentapi', ordertime='$times' WHERE orderid='$orderid'");
            DB::query("DELETE FROM " . DB::table('userorder') . " WHERE status='1' AND ordertime<'$times'-60*86400");
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
}

if ($notifydata['location']) {
    $url = phpcom::$G['siteurl'] . 'apps/misc.php?action=paysucceed';
    $url = phpcom::$G['siteurl'] . 'member.php?action=credit';
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
