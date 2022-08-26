<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : admincp.php    2011-7-5 23:04:55
 */
!defined('IN_PHPCOM') && exit('Access denied');

class phpcom_admincp {

    var $adminsetting = array();
    var $adminusers = array();
    var $adminsession = array();
    var $perms = NULL;
    var $isfounder = FALSE;
    var $adminaccess = 0;
    var $sessiontimeout = 1800;
    var $sessiontime = 0;
    var $clientip;

    public static function &instance() {
        static $_instance;
        if (empty($_instance)) {
            $_instance = new phpcom_admincp();
        }
        return $_instance;
    }

    public function init() {
        phpcom_cache::load('admingroup');
        $this->adminsetting = phpcom::$config['admincp'];
        $this->adminusers = phpcom::$G['member'];
        $this->clientip = phpcom::$G['clientip'];
        if ($this->adminsetting['closed']) {
            showmessage('admincp_closed', NULL, array(
                'email' => phpcom::$setting['adminmail'],
                'webname' => phpcom::$setting['webname'],
                'domain' => $_SERVER['SERVER_NAME']
            ));
        }
        $this->sessiontimeout = intval($this->adminsetting['timeout']);
        $this->sessiontime = TIMESTAMP - $this->sessiontimeout;
        $this->isfounder = $this->checkfounder($this->adminusers);
        $this->check_adminaccess();
        phpcom::$G['founders'] = $this->isfounder;
    }

    public function writeadminlog() {
        $extralog = implodearray(array('GET' => $_GET, 'POST' => $_POST), array('formtoken', 'submit', 'btnsubmit', 'admin_password', 'action'));
        writelog('adminlog', implode("\t", clearlogstring(array(phpcom::$G['timestamp'], phpcom::$G['username'], phpcom::$G['clientip'], $extralog))));
    }

    public function check_adminaccess() {
        $session = array();
        if (!$this->adminusers['uid']) {
            $this->adminaccess = 0;
        } else {
            if (!$this->isfounder) {
                $session = DB::fetch_first("SELECT m.admingid, m.permcustom, s.*
					FROM " . DB::table('adminmember') . " m
					LEFT JOIN " . DB::table('adminsession') . " s ON s.uid=m.uid
					WHERE m.uid='{$this->adminusers['uid']}'");
            } else {
                $session = DB::fetch_first("SELECT * FROM " . DB::table('adminsession') . "
					WHERE uid='{$this->adminusers['uid']}'");
            }
            if (empty($session)) {
                $this->adminaccess = $this->isfounder ? 1 : -2;
            } elseif ($session && empty($session['uid'])) {
                $this->adminaccess = 1;
            } elseif ($session['dateline'] < $this->sessiontime) {
                $this->adminaccess = 1;
            } elseif ($this->adminsetting['checkip'] && ($session['ip'] != $this->clientip)) {
                $this->adminaccess = 1;
            } elseif ($session['errcount'] == -1 && !$this->check_adminsession($session['uid'])) {
                $this->adminaccess = 2;
            } elseif ($session['errcount'] >= 0 && $session['errcount'] <= 3) {
                $this->adminaccess = 2;
            } elseif ($session['errcount'] == -1) {
                $this->adminaccess = 3;
            } else {
                $this->adminaccess = -1;
            }
        }
        if ($this->adminaccess == 2 || $this->adminaccess == 3) {
            if (!empty($session['permcustom'])) {
                $session['permcustom'] = @unserialize($session['permcustom']);
            }
        }

        $this->adminsession = $session;
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_password'])) {
        	if(isset($_POST['admin_username']) && !phpcom::$G['username']){
        		phpcom::$G['username'] = $_POST['admin_username'];
        	}
            $this->writeadminlog();
            if ($this->adminaccess == 2) {
                $this->check_adminlogin();
            } elseif ($this->adminaccess == 0) {
                $this->check_userlogin();
            }
        }

        if ($this->adminaccess == 1) {
            DB::delete('adminsession', "uid='{$this->adminusers['uid']}' OR dateline<'$this->sessiontime'");
            DB::query("INSERT INTO " . DB::table('adminsession') . " (uid, adminid, ip, dateline, errcount)
			VALUES ('{$this->adminusers['uid']}', '{$this->adminusers['adminid']}', '$this->clientip', '" . TIMESTAMP . "', '0')");
        } elseif ($this->adminaccess == 3) {
            $this->load_adminperms();
            DB::update('adminsession', array('dateline' => TIMESTAMP, 'ip' => $this->clientip, 'errcount' => -1), "uid={$this->adminusers['uid']}");
        }

        if ($this->adminaccess != 3) {
            $this->adminlogin();
        }
    }

    public function check_adminsession($uid) {
        $hash = phpcom::$G['cookie']['adminsession'];
        if ($hash && $hash == md5($uid . phpcom::$config['security']['key'])) {
            return TRUE;
        }
        return FALSE;
    }

    public function check_adminlogin() {
        if ((empty($_POST['admin_questionid']) || empty($_POST['admin_answer'])) && $this->adminsetting['forceanswer']) {
            $this->adminlogin();
        }
        loaducenter();
        $ucresult = uc_user_login($this->adminusers['uid'], phpcom::$G['gp_admin_password'], 1, phpcom::$G['gp_admin_questionid'], phpcom::$G['gp_admin_answer'], 1);
        if ($ucresult['uid'] > 0) {
            DB::update('adminsession', array('dateline' => TIMESTAMP, 'ip' => $this->clientip, 'errcount' => -1), "uid={$this->adminusers['uid']}");
            phpcom::setcookie('adminsession', md5($this->adminusers['uid'] . phpcom::$config['security']['key']));
            @header('Location: ' . ADMIN_SCRIPT);
        } else {
            $errcount = $this->adminsession['errcount'] + 1;
            DB::update('adminsession', array('dateline' => TIMESTAMP, 'ip' => $this->clientip, 'errcount' => $errcount), "uid={$this->adminusers['uid']}");
        }
    }

    public function check_userlogin() {
        $admin_username = isset($_POST['admin_username']) ? trim($_POST['admin_username']) : '';
        if ($admin_username != '') {
            if ($logins = Member::loginCheck($admin_username)) {
                $result = Member::userLogin($admin_username, phpcom::$G['gp_admin_password'], phpcom::$G['gp_admin_questionid'], phpcom::$G['gp_admin_answer']);
                if ($result['status'] == 1) {
                    $adminmember = DB::result_first("SELECT uid FROM " . DB::table('adminmember') . " WHERE uid='{$result['member']['uid']}'");
                    if ($adminmember || $this->checkfounder($result['member'])) {
                        DB::insert('adminsession', array(
                            'uid' => $result['member']['uid'],
                            'adminid' => $result['member']['adminid'],
                            'dateline' => TIMESTAMP,
                            'ip' => $this->clientip,
                            'errcount' => -1), FALSE, TRUE);
                        Member::setUserLogin($result['member'], 0);
                        phpcom::setcookie('adminsession', md5($result['member']['uid'] . phpcom::$config['security']['key']));
                        @header('Location: ' . ADMIN_SCRIPT);
                    } else {
                        $this->adminaccess = -2;
                    }
                } else {
                	$this->adminaccess = -2;
                }
            } else {
                $this->adminaccess = -4;
            }
        }
    }

    public function checkfounder($user) {
        $founders = str_replace(' ', '', $this->adminsetting['founder']);
        if (!$user['uid'] || $user['groupid'] != 1 || $user['adminid'] != 1) {
            return FALSE;
        } elseif (empty($founders)) {
            return TRUE;
        } elseif (strexists(",$founders,", ",$user[uid],")) {
            return TRUE;
        } elseif (!is_numeric($user['username']) && strexists(",$founders,", ",$user[username],")) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function adminlogin() {
        require $this->file('login');
    }

    public function adminlogout() {
        phpcom::setcookie('adminsession', '');
        DB::delete('adminsession', "uid='{$this->adminusers['uid']}' OR dateline<'$this->sessiontime'");
        if(isset($_GET['session']) && $_GET['session'] == 'all'){
        	Member::removeCookies();
        }
    }

    public function file($module) {
        $file = 'admincp' . DIRECTORY_SEPARATOR . 'index.php';
        if (!empty($module)) {
            if (strpos($module, '_') === FALSE) {
                $file = 'admincp' . DIRECTORY_SEPARATOR . $module . '.php';
            } else {
                list($dir, $name) = explode('_', $module, 2);
                $file = 'admincp' . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $name . '.php';
            }
        }
        $filename = PHPCOM_PATH . DIRECTORY_SEPARATOR . $file;
        if (!file_exists($filename)) {
            throw new phpcomException("File \"$file\" does not exist");
        }
        return $filename;
    }
	
    public static function permission($key) {
    	$admincp = phpcom_admincp::instance();
    	if ($admincp->perms === null) {
    		$admincp->load_adminperms();
    	}
    	if (isset($admincp->perms['all'])) {
    		return $admincp->perms['all'];
    	}
    	if (!empty($_POST) && !array_key_exists('_allowpost', $admincp->perms) && $key != 'misc_custommenu') {
    		return false;
    	}
    	if (isset($admincp->perms[$key])) {
    		return $admincp->perms[$key];
    	}
    	return false;
    }

    public function permscheck($module, $action = '', $do = '') {
        if ($this->perms === NULL) {
            $this->load_adminperms();
        }
        if (isset($this->perms['all'])) {
            return $this->perms['all'];
        }
        if (!empty($_POST) && !array_key_exists('_allowpost', $this->perms) && $module . '_' . $action != 'misc_custommenu') {
            return FALSE;
        }
        $this->perms['misc_custommenu'] = 1;
        $this->perms['main'] = 1;
        $this->perms['index'] = 1;
        $key = $module;
        if (isset($this->perms[$key])) {
            return $this->perms[$key];
        }
        $key = $module . '_' . $action;
        if (isset($this->perms[$key])) {
            return $this->perms[$key];
        }
        $key = $module . '_' . $action . '_' . $do;
        if (isset($this->perms[$key])) {
            return $this->perms[$key];
        }
        return FALSE;
    }

    public function load_adminperms() {
        $this->perms = array();
        phpcom::$G['admingroup'] = phpcom::$G['cache']['admingroup'][0];
        if (!$this->isfounder) {
            if ($this->adminsession['admingid']) {
                phpcom::$G['admingroup'] = phpcom::$G['cache']['admingroup'][$this->adminsession['admingid']];
                $permissions = phpcom::$G['admingroup']['permission'];
                foreach ($permissions as $value) {
                    if ($value) {
                        if (empty($this->adminsession['permcustom'])) {
                            $this->perms[$value] = TRUE;
                        } elseif (!in_array($value, (array) $this->adminsession['permcustom'])) {
                            $this->perms[$value] = TRUE;
                        }
                    }
                }
            } else {
                $this->perms['all'] = TRUE;
            }
        } else {
            $this->perms['all'] = TRUE;
        }
    }

}

?>
