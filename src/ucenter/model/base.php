<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : base.class.php    2011-12-8
 */
!defined('IN_UCENTER') && exit('Access Denied');

class ucenterbase {

    var $time;
    var $clientip;
    var $db;
    var $key;
    var $app = array();
    var $user = array();
    var $setting = array();

    function __construct() {
        $this->ucenterbase();
    }

    function ucenterbase() {
        $this->time = time();
        $this->clientip = $this->get_clientip();
        $this->init_db();
    }

    function init_db() {
        $this->db = new UCenterDB();
        $this->db->connect(UC_DB_HOSTNAME, UC_DB_USERNAME, UC_DB_PASSWORD, UC_DB_DATABASE, UC_DB_PCONNECT, UC_DB_CHARSET, UC_DB_TABLEPRE);
    }

    function load($model, $base = NULL) {
        $base = $base ? $base : $this;
        if (empty($_ENV[$model])) {
            require_once UCENTER_ROOT . "/model/$model.php";
            eval('$_ENV[$model] = new ' . $model . 'model($base);');
        }
        return $_ENV[$model];
    }

    function get_limit_offset($page, $pagesize, $totalnum) {
        $pagecount = ceil($totalnum / $pagesize);
        $page = max(1, min($pagecount, intval($page)));
        return floor(($page - 1) * $pagesize);
    }

    function get_clientip() {
        $clientip = (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) ? htmlspecialchars((string) $_SERVER['HTTP_X_FORWARDED_FOR']) : '';
        $clientip = $this->check_clientip($clientip);
        if (empty($clientip)) {
            $clientip = (!empty($_SERVER['REMOTE_ADDR'])) ? htmlspecialchars((string) $_SERVER['REMOTE_ADDR']) : '';
            $clientip = $this->check_clientip($clientip);
        }
        return $clientip ? $clientip : 'unknown';
    }

    function check_clientip($xip) {
        static $ipv4expression = '#^(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#';
        static $ipv6expression = '#^(?:(?:(?:[\dA-F]{1,4}:){1,6}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:::(?:[\dA-F]{1,4}:){0,5}(?:[\dA-F]{1,4}(?::[\dA-F]{1,4})?|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:):(?:[\dA-F]{1,4}:){4}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,2}:(?:[\dA-F]{1,4}:){3}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,3}:(?:[\dA-F]{1,4}:){2}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,4}:(?:[\dA-F]{1,4}:)(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,5}:(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,6}:[\dA-F]{1,4})|(?:(?:[\dA-F]{1,4}:){1,7}:)|(?:::))$#i';

        $clientip = '';
        if (!empty($xip) && strcasecmp($xip, 'unknown')) {
            $xip = preg_replace('# {2,}#', ' ', str_replace(array(',', ';', '%'), ' ', $xip));
            $ips = explode(' ', $xip);
            foreach ($ips as $ip) {
                if (preg_match($ipv4expression, $ip)) {
                    $clientip = $ip;
                } else if (preg_match($ipv6expression, $ip)) {
                    if (stripos($ip, '::ffff:') === 0) {
                        $ipv4 = substr($ip, 7);
                        if (preg_match($ipv4expression, $ipv4)) {
                            $ip = $ipv4;
                        }
                    }
                    $clientip = $ip;
                } else {
                    break;
                }
            }
            return $clientip;
        } else {
            return '';
        }
    }

}

?>
