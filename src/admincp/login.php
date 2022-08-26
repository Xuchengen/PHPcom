<?php

/**
 *
 *  Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 *  Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 *  Description : This software is the proprietary information of phpcom.cn.
 *  This File   : login.php
 *
 */
!defined('IN_ADMINCP') && exit('Access denied');

phpcom::$G['lang']['admin'] = 'login';

if ($this->adminaccess == -3) {
    html_loginheader(FALSE);
} else {
    html_loginheader();
}

if ($this->adminaccess == -3) {
    echo '<p class="logintips">' . adminlang('login_admincp_access_denied') . '</p>';
}elseif($this->adminaccess == -1){
    $timeout = $this->sessiontimeout - (TIMESTAMP - $this->adminsession['dateline']);
    echo '<p class="logintips">' . adminlang('login_admincp_locked', array('timeout' => $timeout)) . '</p>';
}elseif($this->adminaccess == -2){
    echo '<p class="logintips">' . adminlang('admincp_access_denied') . '</p>';
}elseif($this->adminaccess == -4){
    echo '<p class="logintips">' . adminlang('login_admin_locked') . '</p>';
}else{
    html_loginform();
}
html_loginfooter();

function html_loginheader($framed = TRUE) {
    $loginTitle = adminlang('login_title');
    $charset = CHARSET;
    $adminTips = adminlang('login_tips');
    $parentframes = '';
    if ($framed) {
        $parentframes = <<<EOT
<script language="JavaScript">
if(self.parent.frames.length != 0) {
	self.parent.location=document.location;
}
</script>
EOT;
    }
    echo <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=$charset" />
<title>$loginTitle - Powered by PHPcom</title>
<meta name="robots" content="noindex,nofollow" />
<link href="misc/admin/login.css" rel="stylesheet" type="text/css"/>
$parentframes
</head>
<body>
<div id="login-header"></div>
<div id="login-main">
	<div class="leftbox"></div>
	<div class="logobox">
		<div id="login-logo"><a href="http://www.phpcom.cn/" target="_blank"></a></div>
		<div id="login-text">$adminTips</div>
	</div>
	<div class="linebox"></div>
	<div class="loginbox">
		<div id="login-box">
EOT;
}

function html_loginfooter() {
	include_once PHPCOM_PATH . '/phpcom_version.php';
    $version = PHPCOM_VERSION;
    $toyear = date('Y');
    echo <<<EOT
		</div>
	</div>
	<div class="rightbox"></div>
</div>
<div id="login-footer">
	<div class="copyright">
		<p>Powered by <a href="http://www.phpcom.net/" target="_blank">PHPcom</a> v$version
		&copy; 2010-$toyear, <a href="http://www.phpcom.cn/" target="_blank">phpcom.cn</a> All Rights Reserved.</p>
	</div>
</div>
</body>
</html>
EOT;
    exit();
}

function html_loginform() {
    $adminName = adminlang('login_username');
    $adminPassword = adminlang('login_password');
    $adminQuestion = adminlang('login_question');
    $adminAnswer = adminlang('login_answer');
    $adminscript = ADMIN_SCRIPT;
    $questionOption = '';
    $admin_username = '';
    $focusname = 'admin_username';
    $disabled = '';
    if (phpcom::$G['uid']) {
        $admin_username = phpcom::$G['username'];
        $disabled = ' disabled';
        $focusname = 'admin_password';
    }
    for ($i = 0; $i < 10; $i++) {
        $question = adminlang('login_question_' . $i);
        $questionOption .= "<option value=\"$i\">$question</option>";
    }
    echo <<<EOT
        <form method="post" id="login_form" name="login_form" action ="$adminscript">
            <table cellpadding="3" cellspacing="0" width="100%">
                <tr><th>$adminName</th><td><div class="login-icon"><div class="name"></div><input class="input" name="admin_username" type="text" tabindex="1" value="$admin_username"$disabled /></div></td></tr>
                <tr><th>$adminPassword</th><td><div class="login-icon"><div class="pwd"></div><input class="input" type="password" name="admin_password" tabindex="2" /></div></td></tr>
                <tr><th>$adminQuestion</th><td><div class="login-select"><select name="admin_questionid" tabindex="3">
                $questionOption
                </select></div></td></tr>
                <tr><th>$adminAnswer</th><td><div class="login-icon"><div class="answer"></div><input class="input" type="text" name="admin_answer" tabindex="4" /></div></td></tr>
                <tr><th></th><td><input class="submit-button" name="btnsubmit" value="ok" type="image" src="misc/admin/images/login.gif" /></td></tr>
            </table>
        </form>
        <script type="text/JavaScript">document.getElementById('login_form').$focusname.focus();</script>
EOT;
}

?>
