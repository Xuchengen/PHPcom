<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : alipay.php    2012-1-5
 */
!defined('IN_PHPCOM') && exit('Access denied');

if (empty(phpcom::$setting['payonline'])) {
    showmessage(phpcom::$setting['payreadme']);
}
phpcom::$setting['pay_alipay']['key'] = decryptstring(phpcom::$setting['pay_alipay']['key']);
define('PHPCOM_ALIPAY_PARTNERID', phpcom::$setting['pay_alipay']['partnerid']);
define('PHPCOM_ALIPAY_TRADEKEY', phpcom::$setting['pay_alipay']['key']);
define('PHPCOM_ALIPAY_DIRECTPAY', phpcom::$setting['pay_alipay']['direct']);
define('PHPCOM_ALIPAY_ACCOUNT', phpcom::$setting['pay_alipay']['account']);

define('PAY_STATUS_SELLER_SEND', 4);
define('PAY_STATUS_WAIT_BUYER', 5);
define('PAY_STATUS_TRADE_SUCCESS', 7);
define('PAY_STATUS_REFUND_CLOSE', 17);

function getpayurl($price, $subject, $returl, &$orderid) {
	$trades = array(
			'body' => $subject,
			'return_url' => phpcom::$G['siteurl'] . 'api/' . $returl
	);
	$trades['subject'] = $trades['body'];
	$trades['show_url'] = phpcom::$G['siteurl'];
	return get_payrequesturl($price, $orderid, $trades);
}

function get_credit_payurl($price, &$orderid) {
    $trades = array(
        'subject' => phpcom::$setting['webname'] . ' - ' . phpcom::$G['member']['username'] . ' - ' . lang('misc', 'credit_payment'),
        'body' => lang('misc', 'credit_payment_readme') . ' ' . phpcom::$setting['creditstrans']['title'] . ' ' .
        intval($price * phpcom::$setting['pay_creditsratio']) . ' ' . phpcom::$setting['creditstrans']['unit'] . ' (' . phpcom::$G['clientip'] . ')',
        'return_url' => phpcom::$G['siteurl'] . 'api/receive_credit.php',
        'show_url' => phpcom::$G['siteurl']
    );
    $trades['show_url'] = $trades['return_url'];
    return get_payrequesturl($price, $orderid, $trades);
}

function get_invite_payurl($amount, $price, &$orderid) {
    $trades = array(
        'subject' => phpcom::$setting['webname'] . ' - ' . lang('misc', 'invite_payment'),
        'body' => lang('misc', 'invite_payment_readme') . '_' . intval($amount) . '_' . lang('misc', 'invite_payment_unit') . '_(' . phpcom::$G['clientip'] . ')',
        'return_url' => phpcom::$G['siteurl'] . 'api/receive_invite.php',
        'show_url' => phpcom::$G['siteurl']
    );
    $trades['notify_url'] = $trades['return_url'];
    return get_payrequesturl($price, $orderid, $trades);
}

function get_payrequesturl($price, &$orderid, array $trades = array()) {
    $orderid = fmdate(TIMESTAMP, 'YmdHis') . random(18, 1);
    $trades += array('subject' => '', 'body' => '', 'return_url' => '', 'show_url' => '',
        'mch_type' => 2, 'logistics_type' => '', 'logistics_fee' => 10, 'notify_url' => '',
        'direct' => PHPCOM_ALIPAY_DIRECTPAY
    );
    $args = array(
        'subject' => $trades['subject'],
        'body' => $trades['body'],
        'service' => 'trade_create_by_buyer',
        'partner' => PHPCOM_ALIPAY_PARTNERID,
        'notify_url' => $trades['notify_url'],
        'return_url' => $trades['return_url'],
        'show_url' => $trades['show_url'],
        '_input_charset' => CHARSET,
        'out_trade_no' => $orderid,
        'price' => $price,
        'total_fee' => $price,
        'quantity' => 1,
        'seller_email' => PHPCOM_ALIPAY_ACCOUNT,
    );
    if ($trades['direct']) {
        $args['service'] = 'create_direct_pay_by_user';
        $args['payment_type'] = '1';
    } else {
        $args['logistics_type'] = 'EXPRESS';
        $args['logistics_fee'] = 0;
        $args['logistics_payment'] = 'SELLER_PAY';
        $args['payment_type'] = 1;
    }
    return trade_gatewayurl($args);
}

function get_trade_payurl($pays, $trades, $tradelog) {
    $args = array(
        'subject' => $trades['subject'],
        'body' => $trades['subject'],
        'service' => 'trade_create_by_buyer',
        'partner' => PHPCOM_ALIPAY_PARTNERID,
        'notify_url' => phpcom::$G['siteurl'] . 'api/receive_trade.php',
        'return_url' => phpcom::$G['siteurl'] . 'api/receive_trade.php',
        'show_url' => phpcom::$G['siteurl'],
        '_input_charset' => CHARSET,
        'out_trade_no' => $tradelog['orderid'],
        'price' => $tradelog['baseprice'],
        'quantity' => $tradelog['number'],
        'logistics_type' => $pays['logistics_type'],
        'logistics_fee' => $tradelog['transportfee'],
        'logistics_payment' => $pays['transport'],
        'payment_type' => $trades['itemtype'],
        'seller_email' => $trades['account'],
    );
    if ($pays['logistics_type'] == 'VIRTUAL') {
        if (PHPCOM_ALIPAY_DIRECTPAY) {
            $args['service'] = 'create_direct_pay_by_user';
            $args['payment_type'] = '1';
            unset($args['logistics_type'], $args['logistics_fee'], $args['logistics_payment']);
        } else {
            $args['logistics_type'] = 'EXPRESS';
            $args['logistics_payment'] = 'SELLER_PAY';
            $args['payment_type'] = '1';
        }
    }
    return trade_gatewayurl($args);
}

function trade_gatewayurl($args) {
    ksort($args);
    $urladd = $sign = '';
    foreach ($args as $key => $val) {
        $sign .= '&' . $key . '=' . $val;
        $urladd .= $key . '=' . rawurlencode($val) . '&';
    }
    $sign = substr($sign, 1);
    $sign = md5($sign . PHPCOM_ALIPAY_TRADEKEY);
    //https://mapi.alipay.com/gateway.do? https://www.alipay.com/cooperate/gateway.do
    //return phpcom::$G['siteurl'] . 'pay/alipay.php?' . $urladd . 'sign=' . $sign . '&sign_type=MD5';
    return 'https://www.alipay.com/cooperate/gateway.do?' . $urladd . 'sign=' . $sign . '&sign_type=MD5';
}

function trade_detailurl($orderid) {
    return 'https://www.alipay.com/trade/query_trade_detail.htm?trade_no=' . $orderid;
}

function trade_notifycheck($type) {
    if (!empty($_POST)) {
        $notify = $_POST;
        $location = FALSE;
    } elseif (!empty($_GET)) {
        $notify = $_GET;
        $location = TRUE;
    } else {
        exit('Access Denied');
    }
    unset($notify['diy']);
    if (fsocketopen("http://notify.alipay.com/trade/notify_query.do?partner=" . PHPCOM_ALIPAY_PARTNERID . "&notify_id=" . $notify['notify_id']) !== 'true') {
        exit('Access Denied');
    }

    if ($type == 'trade') {
        $urlstr = '';
        foreach ($notify as $key => $val) {
            MAGIC_QUOTES_GPC && $val = stripslashes($val);
            $urlstr .= $key . '=' . rawurlencode(stripslashes($val)) . '&';
        }
    } else {
        ksort($notify);
        $sign = '';
        foreach ($notify as $key => $val) {
            $val = stripslashes($val);
            if ($key != 'sign' && $key != 'sign_type') $sign .= "&$key=$val";
        }
        if ($notify['sign'] != md5(substr($sign, 1) . PHPCOM_ALIPAY_TRADEKEY)) {
            exit('Access Denied');
        }
    }

    if (($type == 'credit' || $type == 'invite') && (!PHPCOM_ALIPAY_DIRECTPAY && $notify['notify_type'] == 'trade_status_sync' && ($notify['trade_status'] == 'WAIT_SELLER_SEND_GOODS' || $notify['trade_status'] == 'TRADE_FINISHED') || PHPCOM_ALIPAY_DIRECTPAY && ($notify['trade_status'] == 'TRADE_FINISHED' || $notify['trade_status'] == 'TRADE_SUCCESS'))
            || $type == 'trade' && $notify['notify_type'] == 'trade_status_sync') {
        return array(
            'validator' => TRUE,
            'status' => trade_getstatus(!empty($notify['refund_status']) ? $notify['refund_status'] : $notify['trade_status'], 1),
            'order_no' => $notify['out_trade_no'],
            'price' => !PHPCOM_ALIPAY_DIRECTPAY && $notify['price'] ? $notify['price'] : $notify['total_fee'],
            'trade_no' => $notify['trade_no'],
            'notify' => 'success',
            'location' => $location
        );
    } else {
        return array(
            'validator' => FALSE,
            'notify' => 'fail',
            'location' => $location
        );
    }
}

function trade_typestatus($method, $status = -1) {
    switch ($method) {
        case 'buytrades' : $methodvalue = array(1, 5, 11, 12);
            break;
        case 'selltrades' : $methodvalue = array(2, 4, 10, 13);
            break;
        case 'successtrades' : $methodvalue = array(7);
            break;
        case 'tradingtrades' : $methodvalue = array(1, 2, 3, 4, 5, 6, 10, 11, 12, 13, 14, 15, 16);
            break;
        case 'closedtrades' : $methodvalue = array(8, 17);
            break;
        case 'refundsuccess' : $methodvalue = array(17);
            break;
        case 'refundtrades' : $methodvalue = array(14, 15, 16, 17, 18);
            break;
        case 'unstarttrades' : $methodvalue = array(0);
            break;
        case 'eccredittrades' : $methodvalue = array(7, 17);
            break;
    }
    return $status != -1 ? in_array($status, $methodvalue) : implode('\',\'', $methodvalue);
}

function trade_getstatus($key, $method = 2) {
    $language = lang('misc');
    $status[1] = array(
        'WAIT_BUYER_PAY' => 1,
        'WAIT_SELLER_CONFIRM_TRADE' => 2,
        'WAIT_SYS_CONFIRM_PAY' => 3,
        'WAIT_SELLER_SEND_GOODS' => 4,
        'WAIT_BUYER_CONFIRM_GOODS' => 5,
        'WAIT_SYS_PAY_SELLER' => 6,
        'TRADE_FINISHED' => 7,
        'TRADE_CLOSED' => 8,
        'WAIT_SELLER_AGREE' => 10,
        'SELLER_REFUSE_BUYER' => 11,
        'WAIT_BUYER_RETURN_GOODS' => 12,
        'WAIT_SELLER_CONFIRM_GOODS' => 13,
        'WAIT_ALIPAY_REFUND' => 14,
        'ALIPAY_CHECK' => 15,
        'OVERED_REFUND' => 16,
        'REFUND_SUCCESS' => 17,
        'REFUND_CLOSED' => 18
    );
    $status[2] = array(
        0 => $language['trade_unstart'],
        1 => $language['trade_waitbuyerpay'],
        2 => $language['trade_waitsellerconfirm'],
        3 => $language['trade_sysconfirmpay'],
        4 => $language['trade_waitsellersend'],
        5 => $language['trade_waitbuyerconfirm'],
        6 => $language['trade_syspayseller'],
        7 => $language['trade_finished'],
        8 => $language['trade_closed'],
        10 => $language['trade_waitselleragree'],
        11 => $language['trade_sellerrefusebuyer'],
        12 => $language['trade_waitbuyerreturn'],
        13 => $language['trade_waitsellerconfirmgoods'],
        14 => $language['trade_waitalipayrefund'],
        15 => $language['trade_alipaycheck'],
        16 => $language['trade_overedrefund'],
        17 => $language['trade_refundsuccess'],
        18 => $language['trade_refundclosed']
    );
    return $method == -1 ? $status[2] : $status[$method][$key];
}

function trade_setprice($data, &$price, &$pay, &$transportfee) {
    if ($data['transport'] == 1) {
        $pay['transport'] = 'SELLER_PAY';
    } elseif ($data['transport'] == 2) {
        $pay['transport'] = 'BUYER_PAY';
    } elseif ($data['transport'] == 3) {
        $pay['logistics_type'] = 'VIRTUAL';
    } else {
        $pay['transport'] = 'BUYER_PAY_AFTER_RECEIVE';
    }

    if ($data['transport'] != 3) {
        if ($data['fee'] == 1) {
            $pay['logistics_type'] = 'POST';
            $pay['logistics_fee'] = $data['trade']['ordinaryfee'];
            if ($data['transport'] == 2) {
                $price = $price + $data['trade']['ordinaryfee'];
                $transportfee = $data['trade']['ordinaryfee'];
            }
        } elseif ($data['fee'] == 2) {
            $pay['logistics_type'] = 'EMS';
            $pay['logistics_fee'] = $data['trade']['emsfee'];
            if ($data['transport'] == 2) {
                $price = $price + $data['trade']['emsfee'];
                $transportfee = $data['trade']['emsfee'];
            }
        } else {
            $pay['logistics_type'] = 'EXPRESS';
            $pay['logistics_fee'] = $data['trade']['expressfee'];
            if ($data['transport'] == 2) {
                $price = $price + $data['trade']['expressfee'];
                $transportfee = $data['trade']['expressfee'];
            }
        }
    }
}

?>
