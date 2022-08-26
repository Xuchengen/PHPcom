<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : client.php    2011-12-8
 */
error_reporting(0);
define('IN_UCENTER', TRUE);
define('UCENTER_ROOT', dirname(__FILE__));
define('UCENTER_VERSION', '1.0.0');
define('UCENTER_RELEASE', '20111208');
$GLOBALS['uc_controls'] = array();

function uc_addslashes($string, $force = 0, $strip = FALSE) {
    if (!get_magic_quotes_gpc() || $force) {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = uc_addslashes($val, $force, $strip);
            }
        } else {
            $string = addslashes($strip ? stripslashes($string) : $string);
        }
    }
    return $string;
}

function uc_stripslashes($string) {
    if (get_magic_quotes_gpc()) {
        return stripslashes($string);
    } else {
        return $string;
    }
}

function uc_implodeids($array) {
    if (!empty($array)) {
        $glue = "','";
        $data = array();
        $array = is_array($array) ? $array : array($array);
        $array = array_unique($array);
        foreach ($array as $k => $v) {
            if (is_numeric($v)) {
                $data[$k] = $v;
            }
        }
        return "'" . implode($glue, $data) . "'";
    } else {
        return '';
    }
}

function uc_implodevalue($value, $strip = TRUE) {
    if (is_array($value)) {
        foreach ($value as $k => $v) {
            $value[$k] = uc_implodevalue($v, $strip);
        }
        return implode(', ', $value);
    } elseif (is_numeric($value)) {
        $value = "'$value'";
    } else {
        $value = "'" . addslashes($strip ? stripslashes($avalue) : $value) . "'";
    }
    return $value;
}

function uc_implode_field_value($array, $separator = ',', $strip = TRUE) {
    $sql = $comma = '';
    if (is_array($array)) {
        foreach ($array as $k => $v) {
            $v = addslashes($strip ? stripslashes($v) : $v);
            $sql .= $comma . "`$k`='$v'";
            $comma = $separator;
        }
    } else {
        $sql = $array;
    }
    return $sql;
}

function uc_implode_sql_value($array, $strip = TRUE) {
    $key = $value = $comma = '';
    foreach ($array as $k => $v) {
        $key .= $comma . "`$k`";
        $v = addslashes($strip ? stripslashes($v) : $v);
        $value .= $comma . "'$v'";
        $comma = ',';
    }
    return "($key)VALUES($value)";
}

function uc_md5salt($string, $salt = '') {
    return md5(substr(md5($string), 8, 16) . $salt);
}

function uc_password_md5($string, $salt = '') {
    $string = preg_match('/^\w{32}$/', $string) ? $string : md5($string);
    return md5(substr($string, 8, 16) . $salt);
}

function uc_http_build_query($query_data, $numeric_prefix = NULL) {
    if (is_array($query_data)) {
        return http_build_query($query_data, $numeric_prefix);
    }
    return $query_data;
}

function uc_api_request($model, $method, $args = array()) {
    global $uc_controls;

    if (empty($uc_controls[$model])) {
        include_once UCENTER_ROOT . '/lib/db_mysql.php';
        include_once UCENTER_ROOT . '/model/base.php';
        include_once UCENTER_ROOT . "/control/$model.php";
        eval("\$uc_controls['$model'] = new {$model}control();");
    }
    $class = $uc_controls[$model];
    $class->setting = &phpcom::$setting;
    if (method_exists($class, $method)) {
        $args = uc_addslashes($args, 1, TRUE);
        return call_user_func_array(array(&$class, $method), $args);
    } else {
        return '';
    }
}

function uc_api_post($module, $action, $args = array()) {
    $params = array();
    foreach ($args as $k => $v) {
        $k = urlencode($k);
        if (is_array($v)) {
            foreach ($v as $k2 => $v2) {
                $k2 = urlencode($k2);
                $params[] = "{$k}[$k2]=" . urlencode(uc_stripslashes($v2));
            }
        } else {
            $params[] = "$k=" . urlencode(uc_stripslashes($v));
        }
    }
    $data = uc_api_cipherdata(implode('&', $params));
    $postdata = "m=$module&a=$action&inajax=2&release=" . UCENTER_RELEASE . "&data=$data&appid=" . UC_API_APPID;
    return uc_postrequest(UC_API_URL, $postdata, UC_API_IP);
}

function uc_api_cipherdata($data) {
    return urlencode(uc_encryptstring($data . '&time=' . time(), UC_API_KEY));
}

function uc_encryptstring($string, $key = '', $expiry = 0) {
    return encryptstring($string, $key, $expiry);
}

function uc_decryptstring($string, $key = '') {
    return decryptstring($string, $key);
}

function uc_postrequest($url, $data = NULL, $ip = NULL) {
    $options = array('data' => $data, 'ip' => $ip);
    return uc_fsocketopen($url, $options);
}

function uc_fsocketopen($url, array $options = array()) {
    $uri = @parse_url($url);
    if ($uri === FALSE || !isset($uri['scheme'])) {
        return '';
    }
    $options += array('headers' => array(), 'method' => 'GET', 'data' => NULL, 'limit' => 0,
        'maxredirects' => 3, 'timeout' => 30.0, 'ip' => NULL, 'blocking' => TRUE
    );
    $options['timeout'] = (float) $options['timeout'];
    $options['limit'] = (int) $options['limit'];
    !empty($options['data']) && $options['method'] = 'POST';

    $path = isset($uri['path']) ? $uri['path'] . (isset($uri['query']) ? '?' . $uri['query'] : '') : '/';
    $hots = $options['ip'] ? $options['ip'] : gethostbyname($uri['host']);
    if ($uri['scheme'] == 'http' || $uri['scheme'] == 'feed') {
        $port = isset($uri['port']) ? $uri['port'] : 80;
        $socket = "tcp://$hots:$port";
        $options['headers']['Host'] = $uri['host'] . ($port != 80 ? ':' . $port : '');
    } elseif ($uri['scheme'] == 'https') {
        $port = isset($uri['port']) ? $uri['port'] : 443;
        $socket = "ssl://$hots:$port";
        $options['headers']['Host'] = $uri['host'] . ($port != 443 ? ':' . $port : '');
    }
    $fp = NULL;
    if (function_exists('stream_socket_client')) {
        $fp = @stream_socket_client($socket, $errno, $errstr, $options['timeout']);
    } elseif (function_exists('fsockopen')) {
        $fp = @fsockopen($host, $port, $errno, $errstr, $options['timeout']);
    } elseif (function_exists('pfsockopen')) {
        $fp = @pfsockopen($host, $port, $errno, $errstr, $options['timeout']);
    }
    if (!$fp) {
        return '';
    }
    $options['method'] = $options['method'] ? $options['method'] : 'GET';
    $options['headers']['Accept'] = '*/*';
    $options['headers']['Accept-Language'] = 'zh-cn';
    $options['headers']['Connection'] = 'Close';
    $options['headers']['Referer'] = ($_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    $options['headers']['User-Agent'] = $_SERVER['HTTP_USER_AGENT'];
    $content_length = strlen($options['data']);
    if ($content_length > 0 || $options['method'] == 'POST' || $options['method'] == 'PUT') {
        $options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
        $options['headers']['Content-Length'] = $content_length;
    }
    if (isset($uri['user'])) {
        $options['headers']['Authorization'] = 'Basic ' . base64_encode($uri['user'] . (!empty($uri['pass']) ? ":" . $uri['pass'] : ''));
    }
    $request = $options['method'] . ' ' . $path . " HTTP/1.0\r\n";
    ksort($options['headers']);
    foreach ($options['headers'] as $name => $value) {
        if ($value !== '') {
            $request .= $name . ': ' . trim($value) . "\r\n";
        }
    }
    $request .= "\r\n\r\n" . $options['data'];
    $result = '';
    $headers = array();
    stream_set_blocking($fp, $options['blocking']);
    stream_set_timeout($fp, $options['timeout']);
    @fwrite($fp, $request);
    $meta = stream_get_meta_data($fp);
    if (!$meta['timed_out']) {
        while (!feof($fp)) {
            if (($buffer = @fgets($fp)) && ($buffer == "\r\n" || $buffer == "\n")) {
                break;
            }
            if (!isset($headers['status'])) {
                list($protocol, $statuscode, $status) = explode(' ', trim($buffer), 3);
                $headers['protocol'] = trim($protocol);
                $headers['statuscode'] = floor($statuscode);
                $headers['status'] = trim($status);
            } else {
                list($name, $value) = explode(':', $buffer, 2);
                $name = strtolower($name);
                if (isset($headers[$name]) && $name == 'set-cookie') {
                    $headers[$name] .= ',' . trim($value);
                } else {
                    $headers[$name] = trim($value);
                }
            }
        }
        $statuscode = $headers['statuscode'];
        if (($statuscode == 301 || $statuscode == 302 || $statuscode == 307) && $options['maxredirects']) {
            @fclose($fp);
            $options['maxredirects']--;
            $options['method'] = $options['method'] == 'POST' ? 'GET' : $options['method'];
            $options['data'] = NULL;
            return uc_fsocketopen($headers['location'], $options);
        }
        if (($statuscode == 200 || $statuscode == 304) && $options['method'] != 'HEAD') {
            $stop = FALSE;
            $limit = (int) $options['limit'];
            while (!feof($fp) && !$stop) {
                $fcontent = fread($fp, 1024);
                $result .= $fcontent;
                if ($limit) {
                    $limit -= strlen($fcontent);
                    $stop = $limit <= 0;
                }
            }
        }
    }
    @fclose($fp);
    return $result;
}

function uc_user_synlogin($uid) {
    return uc_api_request('user', 'synlogin', array('uid' => $uid));
}

function uc_user_synlogout() {
    return uc_api_request('user', 'synlogout', array());
}

function uc_user_checkname($username, $minlen = 3, $maxlen = 15) {
    return uc_api_request('user', 'check_username', array('username' => $username, 'minlen' => $minlen, 'maxlen' => $maxlen));
}

function uc_user_checkemail($email) {
    return uc_api_request('user', 'check_email', array('email' => $email));
}

function uc_user_checkintervalregip($ip = '') {
    return uc_api_request('user', 'check_intervalregip', array('ip' => $ip));
}

function uc_user_checklimitcount($ip = '') {
    return uc_api_request('user', 'check_limitcount', array('ip' => $ip));
}

function uc_user_register($username, $password, $email, $groupid = 11, $questionid = '', $answer = '', $regip = '', $special = 0) {
    return uc_api_request('user', 'register', array($username, $password, $email, $groupid, $questionid, $answer, $regip, $special));
}

function uc_user_login($username, $password, $isuid = 0, $questionid = '', $answer = '', $qacheck = 0) {
    $isuid = intval($isuid);
    $return = uc_api_request('user', 'login', array($username, $password, $isuid, $questionid, $answer, $qacheck));
    return $return;
}

function uc_user_add($username, $password, $email, $uid = 0, $groupid = 11, $questionid = '', $answer = '', $regip = '', $special = 0) {
    return uc_api_request('user', 'add', array($username, $password, $email, $uid, $groupid, $questionid, $answer, $regip, $special));
}

function uc_user_edit($username, $oldpasswd, $newpasswd, $email, $ignoreold = 0, $questionid = '', $answer = '', $special) {
    return uc_api_request('user', 'edit', array($username, $oldpasswd, $newpasswd, $email, $ignoreold, $questionid, $answer, $special));
}

function uc_user_editmember($uid, $members = array(), $membercounts = array(), $memberinfos = array(), $memberstatus = array(), $password = '', $chkpasswd = FALSE) {
    return uc_api_request('user', 'editmember', array($uid, $members, $membercounts, $memberinfos, $memberstatus, $password, $chkpasswd));
}

function uc_user_delete($uid) {
    return uc_api_request('user', 'delete', array('uid' => $uid));
}

function uc_get_user($username, $uid = 0) {
    return uc_api_request('user', 'getuser', array('username' => $username, 'uid' => $uid));
}

function uc_user_updateinvite($username, $invite = array()) {
    return uc_api_request('user', 'updateinvite', array('username' => $username, 'invite' => $invite));
}

function uc_get_search_count($condition) {
    return uc_api_request('search', 'getcount', array('condition' => $condition));
}

function uc_search_result($condition, $pagesize, $offset) {
    return uc_api_request('search', 'searchresult', array('condition' => $condition, 'pagesize' => $pagesize, 'offset' => $offset));
}

function uc_search_getuid($condition, $limit = 2000, $offset = 0) {
    return uc_api_request('search', 'getuid', array('condition' => $condition, 'limit' => $limit, 'offset' => $offset));
}

function uc_search_getresult($arruid) {
    return uc_api_request('search', 'getresult', array('arruid' => $arruid));
}

function uc_search_getmember($arruid) {
    return uc_api_request('search', 'getmember', array('arruid' => $arruid));
}

?>
