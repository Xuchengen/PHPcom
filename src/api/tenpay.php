<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : tenpay.php    2012-1-5
 */
!defined('IN_PHPCOM') && exit('Access denied');

if (empty(phpcom::$setting['payonline'])) {
    showmessage(phpcom::$setting['payreadme']);
}

phpcom::$setting['pay_tenpay']['key'] = decryptstring(phpcom::$setting['pay_tenpay']['key']);
phpcom::$setting['pay_tenpay']['escrow_key'] = decryptstring(phpcom::$setting['pay_tenpay']['escrow_key']);
define('PHPCOM_TENPAY_PARTNERID', phpcom::$setting['pay_tenpay']['partnerid']);
define('PHPCOM_TENPAY_TRADEKEY', phpcom::$setting['pay_tenpay']['key']);
define('PHPCOM_TENPAY_DIRECTPAY', phpcom::$setting['pay_tenpay']['direct']);
define('PHPCOM_TENPAY_ESCROW_CHNID', phpcom::$setting['pay_tenpay']['escrow_chnid']);
define('PHPCOM_TENPAY_ESCROW_KEY', phpcom::$setting['pay_tenpay']['escrow_key']);

define('PAY_STATUS_SELLER_SEND', 3);
define('PAY_STATUS_WAIT_BUYER', 4);
define('PAY_STATUS_TRADE_SUCCESS', 5);
define('PAY_STATUS_REFUND_CLOSE', 9);

class RequestHandler {

    var $gateUrl;
    var $key;
    var $parameters;
    var $debugInfo;

    function __construct() {
        $this->RequestHandler();
    }

    function RequestHandler() {
        $this->gateUrl = "http://service.tenpay.com/cgi-bin/v3.0/payservice.cgi";
        $this->key = "";
        $this->parameters = array();
        $this->debugInfo = "";
    }

    function init() {
        
    }

    function getGateURL() {
        return $this->gateUrl;
    }

    function setGateURL($gateUrl) {
        $this->gateUrl = $gateUrl;
    }

    function getKey() {
        return $this->key;
    }

    function setKey($key) {
        $this->key = $key;
    }

    function getParameter($parameter) {
        return $this->parameters[$parameter];
    }

    function setParameter($parameter, $parameterValue) {
        $this->parameters[$parameter] = $parameterValue;
    }

    function getAllParameters() {
        $this->createSign();
        return $this->parameters;
    }

    function getRequestURL() {
        $this->createSign();
        $reqPar = "";
        ksort($this->parameters);
        foreach ($this->parameters as $k => $v) {
            $reqPar .= $k . "=" . urlencode($v) . "&";
        }
        $reqPar = substr($reqPar, 0, strlen($reqPar) - 1);
        $requestURL = $this->getGateURL() . "?" . $reqPar;
        return $requestURL;
    }

    function getDebugInfo() {
        return $this->debugInfo;
    }

    function doSend() {
        header("Location:" . $this->getRequestURL());
        exit;
    }

    function createSign() {
        $signPars = "";
        ksort($this->parameters);
        foreach ($this->parameters as $k => $v) {
            if ("" !== $v && "sign" !== $k) {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars .= "key=" . $this->getKey();
        $sign = strtolower(md5($signPars));
        $this->setParameter("sign", $sign);
        $this->_setDebugInfo($signPars . " => sign:" . $sign);
    }

    function _setDebugInfo($debugInfo) {
        $this->debugInfo = $debugInfo;
    }

}

class ResponseHandler {

    var $key;
    var $parameters;
    var $debugInfo;

    function __construct() {
        $this->ResponseHandler();
    }

    function ResponseHandler() {
        $this->key = "";
        $this->parameters = array();
        $this->debugInfo = "";

        foreach ($_GET as $k => $v) {
            $this->setParameter($k, $v);
        }
        foreach ($_POST as $k => $v) {
            $this->setParameter($k, $v);
        }
    }

    function getKey() {
        return $this->key;
    }

    function setKey($key) {
        $this->key = $key;
    }

    function getParameter($parameter) {
        return $this->parameters[$parameter];
    }

    function setParameter($parameter, $parameterValue) {
        $this->parameters[$parameter] = $parameterValue;
    }

    function getAllParameters() {
        return $this->parameters;
    }

    function isTenpaySign() {
        $signPars = "";

        ksort($this->parameters);
        foreach ($this->parameters as $k => $v) {
            if ("sign" !== $k && "" !== $v) {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars .= "key=" . $this->getKey();
        $sign = strtolower(md5($signPars));
        $tenpaySign = strtolower($this->getParameter("sign"));
        $this->_setDebugInfo($signPars . " => sign:" . $sign .
                " tenpaySign:" . $this->getParameter("sign"));
        return $sign == $tenpaySign;
    }

    function getDebugInfo() {
        return $this->debugInfo;
    }

    function _setDebugInfo($debugInfo) {
        $this->debugInfo = $debugInfo;
    }

}

class PayRequestHandler extends RequestHandler {

    function __construct() {
        $this->PayRequestHandler();
    }

    function PayRequestHandler() {
        $this->setGateURL("http://service.tenpay.com/cgi-bin/v3.0/payservice.cgi");
    }

    function init() {
        $this->setParameter("cmdno", "1");
        $this->setParameter("date", date("Ymd"));
        $this->setParameter("bargainor_id", "");
        $this->setParameter("transaction_id", "");
        $this->setParameter("sp_billno", "");
        $this->setParameter("total_fee", "");
        $this->setParameter("fee_type", "1");
        $this->setParameter("return_url", "");
        $this->setParameter("attach", "");
        $this->setParameter("spbill_create_ip", "");
        $this->setParameter("desc", "");
        $this->setParameter("bank_type", "0");
        $this->setParameter("cs", "gbk");
        $this->setParameter("sign", "");
    }

    function createSign() {
        $signPars = '';
        $signarray = array('cmdno', 'date', 'bargainor_id', 'transaction_id', 'sp_billno', 'total_fee', 'fee_type', 'return_url', 'attach');
        foreach ($signarray as $k) {
            $signPars .= $k . "=" . $this->getParameter($k) . "&";
        }
        $spbill_create_ip = $this->getParameter("spbill_create_ip");
        if ($spbill_create_ip != "") {
            $signPars .= "spbill_create_ip=" . $spbill_create_ip . "&";
        }
        $signPars .= "key=" . $this->getKey();
        $sign = strtolower(md5($signPars));
        $this->setParameter("sign", $sign);
        $this->_setDebugInfo($signPars . " => sign:" . $sign);
    }

}

class PayResponseHandler extends ResponseHandler {

    function isTenpaySign() {
        $signPars = "";
        $signarray = array('cmdno', 'pay_result', 'date', 'transaction_id', 'sp_billno', 'total_fee', 'fee_type', 'attach');
        foreach ($signarray as $k) {
            $signPars .= $k . "=" . $this->getParameter($k) . "&";
        }
        $signPars .= "key=" . $this->getKey();
        $sign = strtolower(md5($signPars));
        $tenpaySign = strtolower($this->getParameter("sign"));
        $this->_setDebugInfo($signPars . " => sign:" . $sign .
                " tenpaySign:" . $this->getParameter("sign"));
        return $sign == $tenpaySign;
    }

}

class MediPayRequestHandler extends RequestHandler {

    function __construct() {
        $this->MediPayRequestHandler();
    }

    function MediPayRequestHandler() {
        $this->setGateURL("http://service.tenpay.com/cgi-bin/v3.0/payservice.cgi");
    }

    function init() {
        $this->setParameter("attach", "1");
        $this->setParameter("chnid", "");
        $this->setParameter("cmdno", "12");
        $this->setParameter("encode_type", "1");
        $this->setParameter("mch_desc", "");
        $this->setParameter("mch_name", "");
        $this->setParameter("mch_price", "");
        $this->setParameter("mch_returl", "");
        $this->setParameter("mch_type", "");
        $this->setParameter("mch_vno", "");
        $this->setParameter("need_buyerinfo", "");
        $this->setParameter("seller", "");
        $this->setParameter("show_url", "");
        $this->setParameter("transport_desc", "");
        $this->setParameter("transport_fee", "");
        $this->setParameter("version", "2");
        $this->setParameter("sign", "");
    }

}

class MediPayResponseHandler extends ResponseHandler {

    function doShow() {
        $strHtml = "<html><head>\r\n" .
                "<meta name=\"TENCENT_ONLINE_PAYMENT\" content=\"China TENCENT\">" .
                "</head><body></body></html>";
        echo $strHtml;
        exit;
    }

    function isTenpaySign() {
        $signParameterArray = array(
            'attach',
            'buyer_id',
            'cft_tid',
            'chnid',
            'cmdno',
            'mch_vno',
            'retcode',
            'seller',
            'status',
            'total_fee',
            'trade_price',
            'transport_fee',
            'version'
        );
        ksort($signParameterArray);
        foreach ($signParameterArray as $k) {
            $v = $this->getParameter($k);
            if (isset($v)) {
                $signPars .= $k . "=" . urldecode($v) . "&";
            }
        }
        $signPars .= "key=" . $this->getKey();
        $sign = strtolower(md5($signPars));
        $tenpaySign = strtolower($this->getParameter("sign"));
        $this->_setDebugInfo($signPars . " => sign:" . $sign .
                " tenpaySign:" . $this->getParameter("sign"));
        return $sign == $tenpaySign;
    }

}

function getpayurl($price, $subject, $returl, &$orderid) {
	$trades = array(
			'body' => $subject,
			'return_url' => phpcom::$G['siteurl'] . 'api/' . $returl
	);
	$trades['subject'] = $trades['body'];
	$trades['show_url'] = $trades['return_url'];
	return get_payrequesturl($price, $orderid, $trades);
}

function get_credit_payurl($price, &$orderid) {
    $trades = array(
        'body' => lang('misc', 'credit_payment_readme') . ' ' . phpcom::$setting['creditstrans']['title'] . ' ' .
        intval($price * phpcom::$setting['pay_creditsratio']) . ' ' . phpcom::$setting['creditstrans']['unit'],
        'return_url' => phpcom::$G['siteurl'] . 'api/receive_credit.php'
    );
    $trades['subject'] = $trades['body'];
    $trades['show_url'] = $trades['return_url'];
    return get_payrequesturl($price, $orderid, $trades);
}

function get_invite_payurl($amount, $price, &$orderid) {
    $trades = array(
        'body' => lang('misc', 'invite_payment_readme') . '_' . intval($amount) . '_' . lang('misc', 'invite_payment_unit') . '_(' . phpcom::$G['clientip'] . ')',
        'return_url' => phpcom::$G['siteurl'] . 'api/receive_invite.php'
    );
    $trades['subject'] = $trades['body'];
    $trades['show_url'] = $trades['return_url'];
    return get_payrequesturl($price, $orderid, $trades);
}

function get_payrequesturl($price, &$orderid, array $trades = array()) {
    $date = fmdate(TIMESTAMP, 'Ymd');
    $suffix = fmdate(TIMESTAMP, 'His') . rand(1000, 9999);
    $transaction_id = PHPCOM_TENPAY_PARTNERID . $date . $suffix;
    $orderid = fmdate(TIMESTAMP, 'YmdHis') . random(14, 1);
    $need_buyerinfo = $mch_type = '1';
    $trades += array('subject' => '', 'body' => '', 'return_url' => '', 'show_url' => '',
        'mch_type' => 2, 'logistics_type' => '', 'logistics_fee' => 10, 'notify_url' => '',
        'direct' => PHPCOM_TENPAY_DIRECTPAY
    );
    if (empty($trades['logistics_type']) || $trades['logistics_type'] == 'VIRTUAL') {
        $mch_type = '2';
        $need_buyerinfo = '2';
    }
    if (!$trades['direct']) {
        $reqHandler = new MediPayRequestHandler();
        $reqHandler->init();
        $reqHandler->setKey(PHPCOM_TENPAY_ESCROW_KEY);
        $encode_type = '1';
        if (strtolower(CHARSET) == 'utf-8') {
            $encode_type = '2';
        }
        $reqHandler->setParameter("chnid", PHPCOM_TENPAY_ESCROW_CHNID);
        $reqHandler->setParameter("encode_type", $encode_type);
        $reqHandler->setParameter("mch_desc", $trades['body']);
        $reqHandler->setParameter("mch_name", $trades['subject']);
        $reqHandler->setParameter("mch_price", $price * 100);
        $reqHandler->setParameter("mch_returl", $trades['return_url']);
        $reqHandler->setParameter("mch_type", $mch_type);
        $reqHandler->setParameter("mch_vno", $orderid);
        $reqHandler->setParameter("need_buyerinfo", $need_buyerinfo);
        $reqHandler->setParameter("seller", PHPCOM_TENPAY_ESCROW_CHNID);
        $reqHandler->setParameter("show_url", $trades['show_url']);
        $reqHandler->setParameter("transport_desc", $trades['logistics_type']);
        $reqHandler->setParameter("transport_fee", $trades['logistics_fee'] * 100);
        $reqHandler->setParameter('attach', 'tenpay');
        $reqUrl = $reqHandler->getRequestURL();
        return $reqUrl;
    }
    $reqHandler = new PayRequestHandler();
    $reqHandler->setGateURL("https://www.tenpay.com/cgi-bin/v1.0/pay_gate.cgi");
    //$reqHandler->setGateURL("http://www.phpcom.cm:81/pay/tenpay.php");
    $reqHandler->init();
    $reqHandler->setKey(PHPCOM_TENPAY_TRADEKEY);
    $reqHandler->setParameter("bargainor_id", PHPCOM_TENPAY_PARTNERID);   //商户号
    $reqHandler->setParameter("sp_billno", $orderid);     //商户订单号
    $reqHandler->setParameter("transaction_id", $transaction_id);  //财付通交易单号
    $reqHandler->setParameter("total_fee", $price * 100);     //商品总金额,以分为单位
    $reqHandler->setParameter("return_url", $trades['return_url']);    //返回处理地址
    $chinese = new Chinese(strtoupper(CHARSET), 'GBK');
    $reqHandler->setParameter("desc", $chinese->Convert($trades['body']));
    $reqHandler->setParameter("date", $date);
    $reqHandler->setParameter("spbill_create_ip", phpcom::$G['clientip']);
    $reqHandler->setParameter("attach", "tenpay");
    $reqHandler->setParameter("bank_type", "0");
    $reqHandler->setParameter("cmdno", "1");
    $reqHandler->setParameter("fee_type", "1");
    $reqUrl = $reqHandler->getRequestURL();
    return $reqUrl;
}

function get_trade_payurl($pays, $trades, $tradelog) {
    $key = PHPCOM_TENPAY_ESCROW_KEY;
    $chnid = PHPCOM_TENPAY_ESCROW_CHNID;
    $seller = $trades['tenpayaccount'] ? $trades['tenpayaccount'] : PHPCOM_TENPAY_ESCROW_CHNID;
    $mch_desc = $trades['subject'];
    $mch_name = $trades['subject'];
    $mch_price = $tradelog['baseprice'] * $tradelog['number'] * 100;
    $mch_returl = phpcom::$G['siteurl'] . 'api/receive_trade.php';
    $mch_vno = $tradelog['orderid'];
    $show_url = phpcom::$G['siteurl'] . 'api/receive_trade.php';
    $transport_desc = $pays['logistics_type'];
    $transport_fee = $tradelog['transportfee'] * 100;

    if (strtolower(CHARSET) == 'gbk') {
        $encode_type = '1';
    } else {
        $encode_type = '2';
    }

    $mch_type = '1';
    $need_buyerinfo = '1';
    if ($pays['logistics_type'] == 'VIRTUAL') {
        $mch_type = '2';
        $need_buyerinfo = '2';
    }

    $reqHandler = new MediPayRequestHandler();
    $reqHandler->init();
    $reqHandler->setKey($key);
    $reqHandler->setParameter("chnid", $chnid);
    $reqHandler->setParameter("encode_type", $encode_type);
    $reqHandler->setParameter("mch_desc", $mch_desc);
    $reqHandler->setParameter("mch_name", $mch_name);
    $reqHandler->setParameter("mch_price", $mch_price);
    $reqHandler->setParameter("mch_returl", $mch_returl);
    $reqHandler->setParameter("mch_type", $mch_type);
    $reqHandler->setParameter("mch_vno", $mch_vno);
    $reqHandler->setParameter("need_buyerinfo", $need_buyerinfo);
    $reqHandler->setParameter("seller", $seller);
    $reqHandler->setParameter("show_url", $show_url);
    $reqHandler->setParameter("transport_desc", $transport_desc);
    $reqHandler->setParameter("transport_fee", $transport_fee);
    $reqHandler->setParameter('attach', 'tenpay');
    $reqUrl = $reqHandler->getRequestURL();
    return $reqUrl;
}

function trade_detailurl($orderid) {
    return "https://www.tenpay.com/med/tradeDetail.shtml?b=1&trans_id=$orderid";
}

function trade_notifycheck($type) {
    if (PHPCOM_TENPAY_DIRECTPAY && ($type == 'credit' || $type == 'invite')) {
        $resHandler = new PayResponseHandler();
        $resHandler->setKey(PHPCOM_TENPAY_TRADEKEY);
        $resHandler->setParameter("pay_time", "");
    } else {
        $resHandler = new MediPayResponseHandler();
        $resHandler->setKey(PHPCOM_TENPAY_ESCROW_KEY);
    }
    if ($type == 'credit' || $type == 'invite') {
        if (PHPCOM_TENPAY_DIRECTPAY && $resHandler->isTenpaySign() && PHPCOM_TENPAY_PARTNERID == phpcom::$G['gp_bargainor_id']) {
            return array(
                'validator' => !phpcom::$G['gp_pay_result'],
                'order_no' => phpcom::$G['gp_sp_billno'],
                'trade_no' => phpcom::$G['gp_transaction_id'],
                'price' => phpcom::$G['gp_total_fee'] / 100,
                'bargainor_id' => phpcom::$G['gp_bargainor_id'],
                'location' => TRUE,
            );
        } elseif (!PHPCOM_TENPAY_DIRECTPAY && $resHandler->isTenpaySign()) {
            return array(
                'validator' => $resHandler->getParameter('retcode') == '0',
                'order_no' => $resHandler->getParameter('mch_vno'),
                'trade_no' => $resHandler->getParameter('cft_tid'),
                'price' => $resHandler->getParameter('total_fee') / 100.0,
                'status' => $resHandler->getParameter('status'),
                'location' => TRUE,
            );
        }
    } elseif ($type == 'trade') {
        if ($resHandler->isTenpaySign()) {
            return array(
                'validator' => $resHandler->getParameter('retcode') == '0',
                'order_no' => $resHandler->getParameter('mch_vno'),
                'trade_no' => $resHandler->getParameter('cft_tid'),
                'price' => $resHandler->getParameter('total_fee') / 100.0,
                'status' => $resHandler->getParameter('status'),
                'location' => TRUE,
            );
        }
    } else {
        return array(
            'validator' => FALSE,
            'location' => phpcom::$G['siteurl']
        );
    }
}

function trade_setprice($data, &$price, &$pay, &$transportfee) {
    if ($data['transport'] == 3) {
        $pay['logistics_type'] = 'VIRTUAL';
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

function trade_typestatus($method, $status = -1) {
    switch ($method) {
        case 'buytrades' : $methodvalue = array(1, 3);
            break;
        case 'selltrades' : $methodvalue = array(2, 4);
            break;
        case 'successtrades' : $methodvalue = array(5);
            break;
        case 'tradingtrades' : $methodvalue = array(1, 2, 3, 4);
            break;
        case 'closedtrades' : $methodvalue = array(6, 10);
            break;
        case 'refundsuccess' : $methodvalue = array(9);
            break;
        case 'refundtrades' : $methodvalue = array(9, 10);
            break;
        case 'unstarttrades' : $methodvalue = array(0);
            break;
    }
    return $status != -1 ? in_array($status, $methodvalue) : implode('\',\'', $methodvalue);
}

function trade_getstatus($key, $method = 2) {
    $language = lang('misc');
    $status[1] = array(
        'WAIT_BUYER_PAY' => 1,
        'WAIT_SELLER_CONFIRM_TRADE' => 2,
        'WAIT_SELLER_SEND_GOODS' => 3,
        'WAIT_BUYER_CONFIRM_GOODS' => 4,
        'TRADE_FINISHED' => 5,
        'TRADE_CLOSED' => 6,
        'REFUND_SUCCESS' => 9,
        'REFUND_CLOSED' => 10,
    );
    $status[2] = array(
        0 => $language['trade_unstart'],
        1 => $language['trade_waitbuyerpay'],
        2 => $language['trade_waitsellerconfirm'],
        3 => $language['trade_waitsellersend'],
        4 => $language['trade_waitbuyerconfirm'],
        5 => $language['trade_finished'],
        6 => $language['trade_closed'],
        9 => $language['trade_refundsuccess'],
        10 => $language['trade_refundclosed']
    );
    return $method == -1 ? $status[2] : $status[$method][$key];
}

?>
