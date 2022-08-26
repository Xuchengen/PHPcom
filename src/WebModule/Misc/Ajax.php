<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Ajax.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Misc_Ajax extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$checkAction = trim($this->request->query('check'));
		if ($checkAction == 'username') {
			loaducenter();
			$usernameminlen = intval(phpcom::$setting['register']['minname']);
			$usernameminlen = $usernameminlen < 2 ? 3 : $usernameminlen;
			$usernamemaxlen = intval(phpcom::$setting['register']['maxname']);
			$usernamemaxlen = $usernamemaxlen < $usernameminlen ? 15 : $usernamemaxlen;
			$usernamemaxlen = $usernamemaxlen > 25 ? 15 : $usernamemaxlen;
			$username = trim($this->request->query('username'));
			$usernamelen = strlength($username);
			if ($usernamelen < $usernameminlen) {
				showmessage('profile_username_minlimit', '', array('num' => $usernameminlen));
			} elseif ($usernamelen > $usernamemaxlen) {
				showmessage('profile_username_maxlimit', '', array('num' => $usernamemaxlen));
			}
			$chkresult = uc_user_checkname($username);
			if ($chkresult == UC_USER_CHECK_USERNAME_INVALID) {
				showmessage('profile_username_invalid', '', array('username' => $username));
			} elseif ($chkresult == UC_USER_CHECK_USERNAME_BADWORD) {
				showmessage('profile_username_protect', '', array('username' => $username));
			} elseif ($chkresult == UC_USER_CHECK_USERNAME_EXISTED) {
				if (uc_get_user($username)) {
					showmessage('profile_username_repeated', '', array('username' => $username));
				} else {
					showmessage('register_activation', '', array());
				}
			}
		} elseif ($checkAction == 'email') {
			loaducenter();
			$email = trim($this->request->query('email'));
			$chkresult = uc_user_checkemail($email);
			if ($chkresult == UC_USER_CHECK_EMAIL_INVALID) {
				showmessage('profile_email_format_invalid');
			} elseif ($chkresult == UC_USER_CHECK_EMAIL_DENIED) {
				showmessage('profile_email_domain_denied');
			} elseif ($chkresult == UC_USER_CHECK_EMAIL_EXISTED) {
				showmessage('profile_email_repeated');
			}
		} elseif ($checkAction == 'invitecode') {
			include_once loadlibfile('member');
			$invitecode = trim($this->request->query('invitecode'));
			if (!$invitecode) {
				showmessage('invitation_code_empty');
			}
			$result = check_invitecode($invitecode);
			if ($result == USER_INVITECODE_INVALID) {
				showmessage('invitation_code_invalid');
			}
			if ($result == USER_INVITECODE_EXPIRES) {
				showmessage('invitation_code_expires');
			}
		} elseif ($checkAction == 'attach') {
		
		}
		showmessage('succeed');
		return 0;
	}
}
?>