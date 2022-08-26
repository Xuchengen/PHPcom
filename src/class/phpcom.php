<?php
/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : phpcom.php    2011-7-23 18:12:46
 */
@ini_set('display_errors', 'On');
@ini_set('display_startup_errors', 'On');
class phpcom_init {

    var $initated = FALSE;
    var $initmemory = TRUE;
    var $initsetting = TRUE;
    var $inituser = TRUE;
    var $initsession = TRUE;
    var $initmisc = TRUE;
    var $db = NULL;
    var $session = NULL;
    var $config = array();
    var $cachelist = array();
    var $superglobal = array('GLOBALS' => 1, '_GET' => 1, '_POST' => 1, '_REQUEST' => 1,
        '_COOKIE' => 1, '_SERVER' => 1, '_ENV' => 1, '_FILES' => 1,
    );

    public static function &instance() {
		static $_instance = null;
		if (empty($_instance)) {
			$_instance = new phpcom_init();
		}
		return $_instance;
	}

    public function __construct() {
        $this->initialize();
    }

    public function __destruct() {
        DB::close();
    }

    public function init() {
        if (!$this->initated) {
            $this->init_db();
            $this->init_memory();
            $this->init_user();
            $this->init_session();
            $this->init_setting();
            $this->init_misc();
        }
        $this->initated = TRUE;
    }

    public function initialize() {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            set_magic_quotes_runtime(0);
        }
        ini_set('magic_quotes_runtime', '0');
        define('MAGIC_QUOTES_GPC', function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc());
        define('TIMESTAMP', time());
        define('ICONV_ENABLE', function_exists('iconv'));
        define('MB_ENABLE', function_exists('mb_convert_encoding'));
        define('OBGZIP_ENABLE', function_exists('ob_gzhandler'));
        $this->timezone_set();
        if (!defined('PHPCOM_COMMON_LIB') && !@include(PHPCOM_PATH . '/lib/common.php')) {
            exit('File "common.php" does not exist');
        }
        define('IS_ROBOT', checkrobot());
        if (function_exists('ini_get')) {
            $memorylimit = @ini_get('memory_limit');
            if ($memorylimit && sizetobytes($memorylimit) < 33554432 && function_exists('ini_set')) {
                ini_set('memory_limit', '128m');
            }
            if(! @ini_get('date.timezone')){
            	ini_set('date.timezone','Asia/Shanghai');
            }
        }
        foreach ($GLOBALS as $key => $value) {
            if (!isset($this->superglobal[$key])) {
                $GLOBALS[$key] = NULL;
                unset($GLOBALS[$key]);
            }
        }

        phpcom::$G = new ArrayObject(array(
            'uid' => 0,
            'username' => '',
            'founders' => false,
            'adminid' => 0,
            'special' => 0,
            'groupid' => 6,
            'sessionid' => 0,
            'channelid' => 0,
            'channel' => array(),
            'formtoken' => '',
            'timestamp' => TIMESTAMP,
            'starttime' => microtime(true),
            'clientip' => $this->get_clientip(),
            'referer' => '',
            'charset' => 'gbk',
            'gzipcompress' => '',
            'authkey' => '',
            'PHP_SELF' => $this->getScriptName(),
            'siteurl' => '',
            'memory' => '',
            'instdir' => '/',
            'member' => array(),
            'group' => array(),
            'usergroup' => array(),
            'cookie' => array('userauth' => '', 'sessionid' => '', 'adminsession' => ''),
            'cache' => array('channel' => array()),
            'session' => array(),
            'lang' => array()
        ));
        
        $this->init_config();
        $this->init_input();
        $this->init_output();
    }
	
	public function getScriptName()
	{
		$filename = isset($_SERVER['SCRIPT_FILENAME']) ? basename($_SERVER['SCRIPT_FILENAME']) : '';
		$selfname = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
		$selfname = $_SERVER['PHP_SELF'];
		if (strcasecmp(basename($selfname), $filename)) {
			$selfname = substr($selfname, 0, strpos($selfname, $filename) + strlen($filename));
		}
		return $selfname;
	}
	
    public function init_config() {
        $_config = array();
        @include PHPCOM_ROOT . '/data/config.php';
        if (empty($_config)) {
            if (!file_exists(PHPCOM_ROOT . '/data/install.lock')) {
            	@exit(header('location: install/index.php'));
            } else {
                phpcom::throw_error('config_notfound');
            }
        }

        if (empty($_config['security']['key'])) {
            $_config['security']['key'] = md5($_config['cookie']['prefix'] . $_config['db'][1]['dbname']);
        }

        if (empty($_config['debug'])) {
        	error_reporting(0);
            define('PHPCOM_DEBUG', FALSE);
        } elseif ($_config['debug'] === 1 || $_config['debug'] === 2 || !empty($_REQUEST['debug']) && $_REQUEST['debug'] === $_config['debug']) {
            define('PHPCOM_DEBUG', TRUE);
            if ($_config['debug'] == 2) {
                error_reporting(E_ALL);
            }else{
            	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
            }
            @ini_set('display_errors', 'On');
            @ini_set('display_startup_errors', 'On');
        } else {
            define('PHPCOM_DEBUG', FALSE);
        }
        phpcom::$config = &$_config;
        if (substr($_config['cookie']['path'], 0, 1) != '/') {
            phpcom::$config['cookie']['path'] = '/' . phpcom::$config['cookie']['path'];
        }
        $_SERVER['HTTP_USER_AGENT'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)';
        phpcom::$config['cookie']['prefix'] = phpcom::$config['cookie']['prefix'] . substr(md5(phpcom::$config['cookie']['path'] . '|' . phpcom::$config['cookie']['domain']), 0, 4) . '_';
        phpcom::$G['authkey'] = md5($_config['security']['key'] . $_SERVER['HTTP_USER_AGENT']);
    }

    public function init_input() {
        if (isset($_GET['GLOBALS']) || isset($_POST['GLOBALS']) || isset($_COOKIE['GLOBALS']) || isset($_FILES['GLOBALS'])) {
            phpcom::throw_error('request_tainting');
        }

        if (!MAGIC_QUOTES_GPC) {
            $_GET = phpcom::addslashes($_GET);
            $_POST = phpcom::addslashes($_POST);
            $_COOKIE = phpcom::addslashes($_COOKIE);
            $_FILES = phpcom::addslashes($_FILES);
        }

        $prefixlength = strlen(phpcom::$config['cookie']['prefix']);
        foreach ($_COOKIE as $key => $val) {
            if (substr($key, 0, $prefixlength) == phpcom::$config['cookie']['prefix']) {
                phpcom::$G['cookie'][substr($key, $prefixlength)] = $val;
            }
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST)) {
            $_GET = array_merge($_GET, $_POST);
        }
        foreach ($_GET as $k => $v) {
            phpcom::$G['gp_' . $k] = $v;
        }
        phpcom::$G['mod'] = empty(phpcom::$G['gp_m']) ? '' : htmlspecialchars(phpcom::$G['gp_m']);
        phpcom::$G['channelid'] = empty(phpcom::$G['gp_channelid']) ? 0 : intval(phpcom::$G['gp_channelid']);
        if(isset(phpcom::$G['gp_chanid'])){
        	phpcom::$G['channelid'] = intval(phpcom::$G['gp_chanid']);
        }
        $_SERVER['HTTP_X_REQUESTED_WITH'] = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : null;
        phpcom::$G['inajax'] = empty(phpcom::$G['gp_inajax']) ? 0 : (empty(phpcom::$config['output']['ajaxvalidate']) ? 1 : ($_SERVER['REQUEST_METHOD'] == 'GET' && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' || $_SERVER['REQUEST_METHOD'] == 'POST' ? 1 : 0));
        phpcom::$G['page'] = isset(phpcom::$G['gp_page']) ? max(1, intval(phpcom::$G['gp_page'])) : 1;
    }

    public function init_output() {

        if (!empty($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === FALSE) {
            phpcom::$config['output']['gzip'] = FALSE;
        }
        @ob_end_clean();
        $allowgzip = phpcom::$config['output']['gzip'] && empty(phpcom::$G['inajax']) && phpcom::$G['mod'] != 'attachment' && OBGZIP_ENABLE;
        if(phpcom::$G['gzipcompress'] = $allowgzip){
        	ob_start('ob_gzhandler');
        }else{
        	ob_start();
        }

        phpcom::$G['charset'] = phpcom::$config['output']['charset'];
        define('CHARSET', phpcom::$config['output']['charset']);
        if (phpcom::$config['output']['forceheader'] && empty(phpcom::$G['inajax'])) {
            header('Content-Type: text/html; charset=' . CHARSET);
        }
        @ini_set('mbstring.internal_encoding', CHARSET);
        @ini_set('iconv.internal_encoding', CHARSET);
        if (phpcom::$config['security']['urlxssdefend'] && $_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_SERVER['REQUEST_URI'])) {
        	$this->check_urlxss();
        }

        //if(!isset(phpcom::$G['cookie']['firstvisit']) || empty(phpcom::$G['cookie']['firstvisit'])){
        //	phpcom::setcookie('firstvisit', encryptstring('phpcom', null, 86460), 86400);
        //}
    }

    public function init_db() {
        $this->db = DB::instance();
        $this->db->set_config(phpcom::$config['db']);
        $this->db->connect();
    }

    public function init_memory() {
        phpcom::$memory = new phpcom_memory();
        if ($this->initmemory) {
            phpcom::$memory->init(phpcom::$config['cache']);
        }
        phpcom::$G['memory'] = phpcom::$memory->type;
    }

    public function init_user() {
        if ($this->inituser) {
            if ($userauth = phpcom::$G['cookie']['userauth']) {
                $userauth = phpcom::addslashes(explode("\t", decryptstring($userauth)));
            }

            list($pwd, $uid) = empty($userauth) || count($userauth) < 2 ? array('', '') : $userauth;
            if ($uid) {
                $user = getuserinfo($uid);
            }
            if (!empty($user) && $user['password'] == $pwd) {
                phpcom::$G['member'] = $user;
            } else {
                $user = array();
                $this->init_guest();
            }
            if ($user && $user['groupexpiry'] > 0 && $user['groupexpiry'] < TIMESTAMP && ($user['groupid'] == 4 || $user['groupid'] == 5)) {
                $groupidnew = DB::result_first("SELECT groupid FROM " . DB::table('usergroup') . " WHERE type='member' AND mincredits<='$user[credits]' AND maxcredits>'$user[credits]'");
                if ($groupidnew) {
                    DB::update('members', array('groupid' => $groupidnew, 'adminid' => 0, 'groupexpiry' => 0), "uid='$user[uid]'");
                    $user['groupid'] = phpcom::$G['groupid'] = $groupidnew;
                    $user['groupexpiry'] = 0;
                    phpcom::$G['member'] = $user;
                }
            }
            if ($user && $user['groupexpiry'] > 0 && $user['groupexpiry'] < TIMESTAMP && phpcom::$G['gp_action'] != 'usergroup' && phpcom::$G['gp_do'] != 'expiry' && CURRENT_SCRIPT != 'member') {
                if (!phpcom::$G['inajax']) {
                    @header('location: ' . phpcom::$G['siteurl'] . 'member.php?action=usergroup&do=apply');
                }
            }
            $this->cachelist[] = 'usergroup_' . phpcom::$G['member']['groupid'];
        } else {
            $this->init_guest();
        }
        if (empty(phpcom::$G['cookie']['lastvisit'])) {
            phpcom::$G['member']['lastvisit'] = TIMESTAMP - 3600;
            phpcom::setcookie('lastvisit', TIMESTAMP - 3600, 86400 * 30);
        } else {
            phpcom::$G['member']['lastvisit'] = phpcom::$G['cookie']['lastvisit'];
        }
        phpcom::$G['uid'] = intval(phpcom::$G['member']['uid']);
        phpcom::$G['username'] = addslashes(phpcom::$G['member']['username']);
        phpcom::$G['adminid'] = intval(phpcom::$G['member']['adminid']);
        phpcom::$G['groupid'] = intval(phpcom::$G['member']['groupid']);
        phpcom::$G['special'] = intval(phpcom::$G['member']['special']);
        phpcom::$G['formtoken'] = formtoken();
    }

    public function init_guest() {
        phpcom::$G['member'] = array('uid' => 0, 'username' => '', 'status' => 0, 'adminid' => 0, 'special' => 0,
        'groupid' => 6, 'allowadmin' => 0, 'credits' => 0, 'timeoffset' => 9999);
    }

    public function init_session() {
        $this->session = new phpcom_session();
        if ($this->initsession) {
            $this->session->init(phpcom::$G['cookie']['sessionid'], phpcom::$G['clientip'], phpcom::$G['uid']);
            phpcom::$G['sessionid'] = $this->session->sessionid;
            phpcom::$G['session'] = $this->session->var;

            if (phpcom::$G['sessionid'] != phpcom::$G['cookie']['sessionid']) {
                phpcom::setcookie('sessionid', phpcom::$G['sessionid'], 86400);
            }
            if ($this->session->isnewguest) {
                if (checkipbanned(phpcom::$G['clientip'])) {
                    $this->session->set('groupid', 5);
                    phpcom::$G['member']['groupid'] = 5;
                    showmessage('user_access_denied');
                }
            }

            if (phpcom::$G['uid'] && ($this->session->isnewguest || ($this->session->get('lastactivity') + 600) < TIMESTAMP)) {
                $this->session->set('lastactivity', TIMESTAMP);
                if ($this->session->isnewguest) {
                    DB::update('member_status', array('lastip' => phpcom::$G['clientip'], 'lastvisit' => TIMESTAMP), "uid='" . phpcom::$G['uid'] . "'");
                }
            }
        }
    }

    public function init_setting($inits = 0) {
        if ($this->initsetting) {
            if (empty(phpcom::$setting)) {
                $this->cachelist[] = 'setting';
            }
            if (empty(phpcom::$G['channel'])) {
                $this->cachelist[] = 'channel';
            }
            if (empty(phpcom::$G['usergroup'])) {
                $this->cachelist[] = 'usergroup';
            }
            if (empty(phpcom::$G['urlrules'])) {
            	$this->cachelist[] = 'urlrules';
            }
            !empty($this->cachelist) && phpcom_cache::load($this->cachelist);
            if (!is_array(phpcom::$setting) && !$inits) {
            	$this->initsetting = true;
            	return $this->init_setting(1);
            }
            $this->initsetting = false;
        }

        $this->timezone_set(phpcom::$setting['timeoffset']);
        if(!empty(phpcom::$setting['instdir'])){
        	phpcom::$G['instdir'] = phpcom::$setting['instdir'];
        }
        phpcom::$G['siteurl'] = 'http://' . $_SERVER['HTTP_HOST'] . phpcom::$G['instdir'];
        
    }
    
    public function init_misc() {
        if (!$this->initmisc) return FALSE;
        lang('common');
        if ($this->initsetting && $this->inituser) {
            if (!isset(phpcom::$G['member']['timeoffset']) || phpcom::$G['member']['timeoffset'] === '' || phpcom::$G['member']['timeoffset'] == '9999') {
                phpcom::$G['member']['timeoffset'] = phpcom::$setting['timeoffset'];
            }
        }
        $timeoffset = $this->initsetting ? phpcom::$G['member']['timeoffset'] : phpcom::$setting['timeoffset'];
        phpcom::$G['now'] = array(
            'time' => fmdate(TIMESTAMP),
            'offset' => $timeoffset >= 0 ? ($timeoffset == 0 ? '' : '+' . $timeoffset) : $timeoffset
        );
        //$this->timezone_set($timeoffset);
        phpcom::$G['formtoken'] = formtoken();
        if ($this->inituser) {
            if (phpcom::$G['group'] && !phpcom::$G['group']['access']) {
                if (phpcom::$G['uid']) {
                    showmessage('user_access_denied');
                } elseif ((!defined('ALLOW_GUESTS') || !ALLOW_GUESTS) && !phpcom::$G['inajax']) {
                    if (!defined('CURRENT_SCRIPT') || !in_array(CURRENT_SCRIPT, array('member', 'api', 'ajax', 'captcha'))) {
                        @header('location: ' . phpcom::$G['siteurl'] . 'member.php?action=login');
                    }
                }
            }
            if (phpcom::$G['member']['status'] == -1) {
                showmessage('user_access_denied');
            }
        }
        if (phpcom::$setting['siteclosed'] && !defined('IN_ADMINCP')) {
            if (phpcom::$G['uid'] && (phpcom::$G['groupid'] == 1 || phpcom::$G['group']['access'] == 2)) {

            } elseif (in_array(CURRENT_SCRIPT, array('admin', 'member', 'api', 'ajax')) || defined('ALLOW_GUESTS') && ALLOW_GUESTS) {

            } else {
                $closedreason = DB::result_first("SELECT svalue FROM " . DB::table('setting') . " WHERE skey='closedreason'");
                $closedreason = str_replace(':', '&#58;', $closedreason);
                showmessage($closedreason ? $closedreason : 'website_closed', NULL, array(
                    'email' => phpcom::$setting['adminmail'],
                    'webname' => phpcom::$setting['webname'],
                    'domain' => $_SERVER['SERVER_NAME']
                ));
            }
        }
        if (!defined('IN_ADMINCP')) {
            if (!check_allowipaccess()) {
                showmessage('website_allow_ipaccess');
            }
        }
        if ($this->session->isnewguest && phpcom::$G['uid']) {
            update_creditbyaction('daylogin', phpcom::$G['uid']);
        }
    }

    public function denied_robot() {
        if (IS_ROBOT) {
        	header("HTTP/1.1 403 Forbidden");
            exit(header("Status: 403 Forbidden"));
        }
    }

    public function check_urlxss() {
        $uri = strtoupper(urldecode(urldecode($_SERVER['REQUEST_URI'])));
        if (strpos($uri, '<') !== FALSE || strpos($uri, '"') !== FALSE || strpos($uri, 'CONTENT-TRANSFER-ENCODING') !== FALSE) {
            showmessage('request_tainting');
        }
        return TRUE;
    }

    public function get_clientip() {
    	$clientip = '';
    	switch (true) {
    		case !empty($_SERVER['HTTP_CLIENT_IP']):
    			$clientip = htmlspecialchars((string)$_SERVER['HTTP_CLIENT_IP']);
    			if($clientip = $this->check_clientip($clientip)){
    				break;
    			}
    		case !empty($_SERVER['HTTP_X_REAL_IP']):
    			$clientip = htmlspecialchars((string)$_SERVER['HTTP_X_REAL_IP']);
    			if($clientip = $this->check_clientip($clientip)){
    				break;
    			}
    		case !empty($_SERVER['HTTP_X_FORWARDED_FOR']):
    			$clientip = htmlspecialchars((string)$_SERVER['HTTP_X_FORWARDED_FOR']);
    			if($clientip = $this->check_clientip($clientip)){
    				break;
    			}
    		case !empty($_SERVER['REMOTE_ADDR']):
    			$clientip = htmlspecialchars((string) $_SERVER['REMOTE_ADDR']);
    			$clientip = $this->check_clientip($clientip);
    			break;
    	}
        return $clientip ? $clientip : 'unknown';
    }

    public function check_clientip($xip) {
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
            return null;
        }
    }
	
    public function timezone_set($timeoffset = 0) {
        if (function_exists('date_default_timezone_set')) {
            @date_default_timezone_set('Etc/GMT' . ($timeoffset > 0 ? '-' : '+') . (abs($timeoffset)));
        }
    }

}

class phpcom {

    public static $G;
    public static $config = array();
    public static $setting = array();
    public static $session = array();
    public static $plugin = array();
    public static $memory;

    /**
     * 抛出错误消息
     * @param string $message 错误消息
     */
    public static function throw_error($message, $code = 1) {
        throw new phpcomException($message, $code);
    }

    /**
     * 获取当前时间戳和微秒数
     * @return int 返回当前 Unix 时间戳和微秒数
     */
    public static function microtime() {
        return array_sum(explode(' ', microtime()));
    }

    /**
     * 发送一个 HTTP 报头
     * @param string $string 规定要发送的报头字符串
     * @param bool $replace 指示该报头是否替换之前的报头,默认是 true
     * @param int $http_response_code 把 HTTP 响应代码强制为指定的值
     * @return void
     */
    public static function header($string, $replace = true, $http_response_code = 0) {
        $string = str_replace(array("\r", "\n"), array('', ''), $string);
        @header($string, $replace, $http_response_code);
        if (preg_match('/^\s*location:/is', $string)) {
            exit();
        }
    }

    public static function query($key = null, $default = null) {
    	if (null === $key) return $_GET;
    	if(is_array($key)){
    		$data = array();
    		foreach ($key as $name){
    			if(is_string($name)){
    				$data[$name] = (isset($_GET[$name])) ? $_GET[$name] : $default;
    			}
    		}
    		return $data;
    	}
    	return (isset($_GET[$key])) ? $_GET[$key] : $default;
    }

    public static function post($key = null, $default = null) {
    	if (null === $key) return $_POST;
    	if(is_array($key)){
    		$data = array();
    		foreach ($key as $name){
    			if(is_string($name)){
    				$data[$name] = (isset($_POST[$name])) ? $_POST[$name] : $default;
    			}
    		}
    		return $data;
    	}
    	return (isset($_POST[$key])) ? $_POST[$key] : $default;
    }

    public static function postQuery($key = null, $default = null) {
    	if (null === $key) return $_POST + $_GET;
    	return (isset($_GET[$key])) ? $_GET[$key] : (isset($_POST[$key]) ? $_POST[$key] : $default);
    }

    /**
     * 递归生成目录
     * @param string $dir 目录名
     * @param int $mode 目录访问权限，0777=最大访问权限
     * @param bool $makeindex 是否生成 index.html 文件
     * @return bool 返回布尔值 TRUE
     */
    public static function mkdir($dir, $mode = 0777, $makeindex = TRUE) {
        if ($dir && !is_dir($dir)) {
            self::mkdir(dirname($dir));
            @mkdir($dir, $mode);
            if ($makeindex) {
                @touch($dir . '/index.htm');
                @chmod($dir . '/index.htm', 0777);
            }
        }

        return TRUE;
    }

    public static function mkdir_recursive($pathname, $mode) {
        is_dir(dirname($pathname)) || mkdir_recursive(dirname($pathname), $mode);
        return is_dir($pathname) || @mkdir($pathname, $mode);
    }

    /**
     * 使用反斜线引用字符串
     * @param string $string 字符串
     * @return string 返回加反斜线字符串
     */
    public static function addslashes($string) {
        if (is_array($string)) {
            foreach ($string as $key => $value) {
                unset($string[$key]);
                $string[addslashes($key)] = self::addslashes($value);
            }
        } else {
            $string = addslashes($string);
        }
        return $string;
    }

    /**
     * 反引用一个引用字符串
     * @param string $string 字符串
     * @return string 返回一个去除转义反斜线后的字符串
     */
    public static function stripslashes($string) {
        if (is_array($string)) {
            foreach ($string as $key => $val) {
                $string[$key] = self::stripslashes($val);
            }
        } else {
            $string = stripslashes($string);
        }
        return $string;
    }

    /**
     * 设置 COOKIE
     * @param string $name Cookie 名称
     * @param string $value 设置 Cookie 值
     * @param int $expire 设置过期时间
     * @param bool $httponly 设置HttpOnly属性
     */
    public static function setcookie($name, $value = '', $expire = 0, $prefixed = 1, $httponly = FALSE) {
        $cookies = phpcom::$config['cookie'];
        phpcom::$G['cookei'][$name] = $value;
        $name = $prefixed ? $cookies['prefix'] . $name : $name;
        $_COOKIE[$name] = $name;
        if ($value == '' || $expire < 0) {
            $value = '';
            $expire = -1;
        }
        $expire = $expire > 0 ? time() + $expire : ($expire < 0 ? time() - 31536000 : 0);
        $path = $httponly && version_compare(PHP_VERSION, '5.2.0', '<') ? $cookies['path'] . '; HttpOnly' : $cookies['path'];
        $domain = $cookies['domain'];
        $secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
        if (version_compare(PHP_VERSION, '5.2.0', '<')) {
            setcookie($name, $value, $expire, $path, $domain, $secure);
        } else {
            setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
        }
    }

    /**
     * 获取COOKIE
     * @param string $key 键名
     * @return string 返回 COOKIE 值
     */
    public static function getcookie($key) {
        return isset(phpcom::$G['cookie'][$key]) ? phpcom::$G['cookie'][$key] : '';
    }

    public static function iconv($string, $in_charset, $out_charset = CHARSET, $forcedtable = FALSE) {
        $in_charset = strtoupper($in_charset);
        $out_charset = strtoupper($out_charset);
        if ($in_charset != $out_charset) {
            $chinese = new Chinese($in_charset, $out_charset, $forcedtable);
            $strnew = $chinese->Convert($string);
            if (!$forcedtable && !$strnew && $string) {
                $chinese = new Chinese($in_charset, $out_charset, 1);
                $strnew = $chinese->Convert($string);
            }
            return $strnew;
        } else {
            return $string;
        }
    }

}

class phpcom_session {

    var $sessionid = NULL;
    var $var;
    var $isnewguest = FALSE;
    var $guests = array('sessionid' => 0, 'groupid' => 6, 'username' => 'guest',
        'browser' => '', 'ip' => '', 'lastactivity' => 0, 'lastupdated' => 0);

    function __construct($sessionid = 0, $ip = '', $uid = 0) {
        $this->phpcom_session($sessionid, $ip, $uid);
    }

    function phpcom_session($sessionid = 0, $ip = '', $uid = 0) {
        $this->var = $this->guests;
        if (!empty($ip)) {
            $this->init($sessionid, $ip, $uid);
        }
    }

    function init($sessionid = 0, $ip = '', $uid = 0) {
        $session = array();
        if ($sessionid) {
            $session = DB::fetch_first("SELECT * FROM " . DB::table('session') . " WHERE sessionid='$sessionid' AND ip='$ip'");
        }

        if (empty($session) || $session['uid'] != $uid) {
            $this->isnewguest = TRUE;
            $this->guests['sessionid'] = '1' . random(9, TRUE);
            $this->guests['uid'] = $uid;
            $this->guests['ip'] = $ip;
            $this->guests['lastactivity'] = time();
            $this->guests['lastupdated'] = time();
            $this->sessionid = $sessionid;
            $session = $this->guests;
        }

        $this->var = $session;
        $this->sessionid = $session['sessionid'];
    }

    function set($key, $value) {
        if (isset($this->guests[$key])) {
            $this->var[$key] = $value;
        }
    }

    function get($key) {
        if (isset($this->guests[$key])) {
            return $this->var[$key];
        }
    }

    function delete() {
        $onlinehold = time() - intval(phpcom::$setting['onlinehold']);
        $guestspan = 60;
        $condition = " sessionid='{$this->sessionid}' ";
        $condition .= " OR lastactivity<$onlinehold ";
        $condition .= " OR (uid='0' AND ip='{$this->var['ip']}' AND lastactivity>$guestspan) ";
        $condition .= $this->var['uid'] ? " OR (uid='{$this->var['uid']}') " : '';
        DB::delete('session', $condition);
    }

    function update() {
        if ($this->sessionid !== NULL) {
            $data = phpcom::addslashes($this->var);
            if ($this->isnewguest) {
                $this->delete();
                DB::insert('session', $data, FALSE, FALSE, TRUE);
            } else {
                DB::update('session', $data, array('sessionid' => $data['sessionid']));
            }
            phpcom::$session = $data;
            phpcom::setcookie('sessionid', $this->sessionid, 86400);
        }
    }

    function onlinecount($type = 0) {
        $condition = $type == 1 ? ' WHERE uid>0' : ($type == 2 ? ' WHERE uid=0' : '');
        return DB::result_first("SELECT count(*) FROM " . DB::table('session') . $condition);
    }

}
?>