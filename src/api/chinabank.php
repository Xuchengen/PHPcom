<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : chinabank.php    2012-1-5
 */
!defined('IN_PHPCOM') && exit('Access denied');

if (empty(phpcom::$setting['payonline'])) {
    showmessage(phpcom::$setting['payreadme']);
}

phpcom::$setting['pay_chinabank']['key'] = decryptstring(phpcom::$setting['pay_chinabank']['key']);
define('PHPCOM_CHINABANK_PARTNERID', phpcom::$setting['pay_chinabank']['partnerid']);
define('PHPCOM_CHINABANK_TRADEKEY', phpcom::$setting['pay_chinabank']['key']);
define('PHPCOM_CHINABANK_DIRECTPAY', 1);

define('PAY_STATUS_SELLER_SEND', 3);
define('PAY_STATUS_WAIT_BUYER', 4);
define('PAY_STATUS_TRADE_SUCCESS', 5);
define('PAY_STATUS_REFUND_CLOSE', 9);

function getpayurl($price, $subject, $returl, &$orderid) {
	$trades = array(
			'body' => $subject,
			'return_url' => phpcom::$G['siteurl'] . 'api/' . $returl
	);
	$trades['subject'] = $trades['body'];
	return get_payrequesturl($price, $orderid, $trades);
}

function get_credit_payurl($price, &$orderid) {
    $trades = array(
        'subject' => phpcom::$setting['webname'] . ' - ' . phpcom::$G['member']['username'] . ' - ' . lang('misc', 'credit_payment'),
        'body' => 'credit',
        'return_url' => phpcom::$G['siteurl'] . 'api/receive_credit.php'
    );
    return get_payrequesturl($price, $orderid, $trades);
}

function get_invite_payurl($amount, $price, &$orderid) {
    $trades = array(
        'subject' => phpcom::$setting['webname'] . ' - ' . lang('misc', 'invite_payment'),
        'body' => 'invite',
        'return_url' => phpcom::$G['siteurl'] . 'api/receive_invite.php'
    );
    return get_payrequesturl($price, $orderid, $trades);
}

function get_payrequesturl($price, &$orderid, array $trades = array()) {
    $date = fmdate(TIMESTAMP, 'Ymd');
    $suffix = fmdate(TIMESTAMP, 'His') . rand(1000, 9999);
    $orderid = "$date-" . PHPCOM_CHINABANK_PARTNERID . "-$suffix";
    $trades += array('subject' => '', 'body' => '', 'return_url' => '', 'show_url' => '');
    $args = array(
        'v_mid' => PHPCOM_CHINABANK_PARTNERID,
        'v_oid' => $orderid,
        'v_amount' => $price,
        'v_moneytype' => 'CNY',
        'v_url' => $trades['return_url'],
        'remark1' => $trades['body']
    );
    $trades['subject'] && $args['remark2'] = $trades['subject'];
    return trade_gatewayurl($args);
}

function get_trade_payurl($pays, $trades, $tradelog) {
    $args = array(
        'v_mid' => PHPCOM_CHINABANK_PARTNERID,
        'v_oid' => $tradelog['orderid'],
        'v_amount' => $tradelog['baseprice'],
        'v_moneytype' => 'CNY',
        'v_url' => phpcom::$G['siteurl'] . 'api/receive_trade.php',
        'remark1' => 'trade'
    );
    $trades['subject'] && $args['remark2'] = $trades['subject'];
    @isset($pays, $tradelog);
    return trade_gatewayurl($args);
}

function trade_gatewayurl($args) {
    $urladd = $sign = '';
    $sign = $args['v_amount'] . $args['v_moneytype'] . $args['v_oid'] . $args['v_mid'] . $args['v_url'] . PHPCOM_CHINABANK_TRADEKEY;        //md5加密拼凑串,注意顺序不能变
    foreach ($args as $key => $val) {
        $urladd .= $key . '=' . rawurlencode($val) . '&';
    }
    $v_md5info = strtoupper(md5($sign));
    //return phpcom::$G['siteurl'] . 'pay/chinabank.php?' . $urladd . 'v_md5info=' . $v_md5info;
    return 'https://pay3.chinabank.com.cn/PayGate?' . $urladd . 'v_md5info=' . $v_md5info;
}

function trade_getdetailurl($orderid) {
    return "$orderid";
}

function trade_notifycheck($type) {
    if (empty($_POST)) {
        exit('error');
    }
    $v_oid = trim(phpcom::$G['gp_v_oid']);
    $v_pstatus = trim(phpcom::$G['gp_v_pstatus']);
    $v_amount = trim(phpcom::$G['gp_v_amount']);
    $v_moneytype = trim(phpcom::$G['gp_v_moneytype']);
    $remark1 = trim(phpcom::$G['gp_remark1']);
    $v_md5str = trim(phpcom::$G['gp_v_md5str']);
    $md5string = strtoupper(md5($v_oid . $v_pstatus . $v_amount . $v_moneytype . PHPCOM_CHINABANK_TRADEKEY));
    if ($v_md5str == $md5string) {
        if ($v_pstatus == "20") {
            return array(
                'validator' => TRUE,
                'status' => $v_pstatus,
                'order_no' => $v_oid,
                'price' => $v_amount,
                'trade_no' => $v_oid,
                'notify' => 'ok',
                'trade_type' => $remark1,
                'type' => $type,
                'location' => TRUE
            );
        } else {
            return array(
                'validator' => FALSE,
                'notify' => 'ok',
                'location' => phpcom::$G['siteurl']
            );
        }
    } else {
        exit('error');
    }
}

function trade_getstatus($key, $method = 2) {
    $language = lang('misc');
    $status[1] = array(
        'TRADE_FINISHED' => 20,
        'TRADE_CLOSED' => 30
    );
    $status[2] = array(
        0 => $language['trade_unstart'],
        20 => $language['trade_finished'],
        30 => $language['trade_closed']
    );
    return $method == -1 ? $status[2] : $status[$method][$key];
}

?>
