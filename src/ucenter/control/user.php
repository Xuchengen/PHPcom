<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : user.php    2011-12-9
 */
!defined('IN_UCENTER') && exit('Access Denied');
define('UC_USER_CHECK_USERNAME_INVALID', -1);
define('UC_USER_CHECK_USERNAME_BADWORD', -2);
define('UC_USER_CHECK_USERNAME_EXISTED', -3);
define('UC_USER_CHECK_EMAIL_INVALID', -4);
define('UC_USER_CHECK_EMAIL_DENIED', -5);
define('UC_USER_CHECK_EMAIL_EXISTED', -6);
define('UC_USER_CHECK_PASSWORD_INVALID', -7);

define('UC_USER_LOGIN_USERNAME_INVALID', -1);
define('UC_USER_LOGIN_PASSWORD_INVALID', -2);
define('UC_USER_LOGIN_QUESTION_INVALID', -3);

class usercontrol extends ucenterbase {

    function __construct() {
        $this->usercontrol();
    }

    function usercontrol() {
        parent::__construct();
        $this->load('user');
    }

    function check_username($username, $minlen = 3, $maxlen = 15) {
        $username = addslashes(trim(stripslashes($username)));
        if (!$_ENV['user']->check_username($username, $minlen, $maxlen)) {
            return UC_USER_CHECK_USERNAME_INVALID;
        } elseif (!$_ENV['user']->check_usernamerestrict($username)) {
            return UC_USER_CHECK_USERNAME_BADWORD;
        } elseif ($_ENV['user']->check_usernameexists($username)) {
            return UC_USER_CHECK_USERNAME_EXISTED;
        }
        return 1;
    }

    function check_email($email, $username = '') {
        if (!$_ENV['user']->check_emailformat($email)) {
            return UC_USER_CHECK_EMAIL_INVALID;
        } elseif (!$_ENV['user']->check_emailrestrict($email)) {
            return UC_USER_CHECK_EMAIL_DENIED;
        } elseif (!$this->setting['repeatemail'] && $_ENV['user']->check_emailexists($email, $username)) {
            return UC_USER_CHECK_EMAIL_EXISTED;
        } else {
            return 1;
        }
    }

    function check_intervalregip($ip = '') {
        return $_ENV['user']->check_intervalregip($ip);
    }

    function check_limitcount($ip = '') {
        return $_ENV['user']->check_limitcount($ip);
    }

    function synlogin($uid) {
        return $_ENV['user']->user_synlogin($uid);
    }

    function synlogout() {
        return $_ENV['user']->user_synlogout();
    }

    function login($username, $password, $isuid = 0, $questionid = '', $answer = '', $qacheck = 0) {
        if ($isuid == 1) {
            $user = $_ENV['user']->get_user_by_uid($username);
        } elseif ($isuid == 2) {
            $user = $_ENV['user']->get_user_by_email($username);
        } else {
            $user = $_ENV['user']->get_user_by_username($username);
        }

        if (empty($user)) {
            $status = UC_USER_LOGIN_USERNAME_INVALID;
        } elseif ($user['password'] != uc_password_md5($password, $user['salt'])) {
            $status = UC_USER_LOGIN_PASSWORD_INVALID;
        } else if ($qacheck && $user['qacode'] != '' && $user['qacode'] != $_ENV['user']->qacrypt($questionid, $answer)) {
            $status = UC_USER_LOGIN_QUESTION_INVALID;
        } else {
            $status = $user['uid'];
        }
        return array(
            'status' => $status,
            'uid' => $status,
            'username' => $user['username'],
            'password' => $user['password'],
            'email' => $user['email'],
            'credits' => $user['credits'],
            'salt' => $user['salt']
        );
    }

    function register($username, $password, $email, $groupid = 11, $questionid = '', $answer = '', $regip = '', $special = 0) {
        if (empty($password)) {
            return UC_USER_CHECK_PASSWORD_INVALID;
        }
        if (($status = $this->check_username($username)) < 0) {
            return $status;
        }
        if (($status = $this->check_email($email)) < 0) {
            return $status;
        }
        $uid = $_ENV['user']->user_add($username, $password, $email, 0, $groupid, $questionid, $answer, $regip, $special);
        return $uid;
    }

    function add($username, $password, $email, $uid = 0, $groupid = 11, $questionid = '', $answer = '', $regip = '', $special = 0) {
        if (($status = $this->check_username($username)) < 0) {
            return $status;
        }
        if (($status = $this->check_email($email)) < 0) {
            return $status;
        }
        $uid = $_ENV['user']->user_add($username, $password, $email, $uid, $groupid, $questionid, $answer, $regip, $special);
        return $uid;
    }

    function edit($username, $oldpasswd, $newpasswd, $email, $ignoreold = 0, $questionid = '', $answer = '', $special = 0) {
        if (!$ignoreold && $email && ($status = $this->check_email($email, $username)) < 0) {
            return $status;
        }
        $status = $_ENV['user']->user_edit($username, $oldpasswd, $newpasswd, $email, $ignoreold, $questionid, $answer, $special);
        return $status;
    }

    function editmember($uid, $members = array(), $membercounts = array(), $memberinfos = array(), $memberstatus = array(), $password = '', $chkpasswd = FALSE) {
        $status = $_ENV['user']->user_edit_member($uid, $members, $membercounts, $memberinfos, $memberstatus, $password, $chkpasswd);
        return $status;
    }

    function delete($uid) {
        return $_ENV['user']->user_delete($uid);
    }

    function getuser($username, $uid = 0) {
        if ($uid) {
            $status = $_ENV['user']->get_user_by_uid($uid);
        } else {
            $status = $_ENV['user']->get_user_by_username($username);
        }
        if ($status) {
            return array($status['uid'], $status['username'], $status['email']);
        } else {
            return 0;
        }
    }

    function updateinvite($username, $invite = array()) {
        return $_ENV['user']->user_update_invite($username, $invite);
    }

}

?>
