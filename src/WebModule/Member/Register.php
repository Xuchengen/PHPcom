<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Register.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Register extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$this->title = $title = lang('member', 'member_register');
		if (phpcom::$G['uid']) {
			showmessage('login_succeed', $this->getReferer(), array(
			'username' => phpcom::$G['username'],
			'usergroup' => phpcom::$G['group']['grouptitle']
			));
		}
		if (!phpcom::$setting['register']['status']) {
			showmessage(phpcom::$setting['register']['closemessage']);
		}
		$ip = phpcom::$G['clientip'];
		if (!Member::checkAllowRegip($ip)) {
			showmessage('register_allows_regip', '/', array('ip' => $ip));
		}
		
		$usernameminlen = intval(phpcom::$setting['register']['minname']);
		$usernameminlen = $usernameminlen < 2 ? 3 : $usernameminlen;
		$usernamemaxlen = intval(phpcom::$setting['register']['maxname']);
		$usernamemaxlen = $usernamemaxlen < $usernameminlen ? 15 : $usernamemaxlen;
		$usernamemaxlen = $usernamemaxlen > 25 ? 15 : $usernamemaxlen;
		
		if (checksubmit(array('btnsubmit', 'submit', 'ok', 'register'))) {
			$member = new MemberModel_Register($this->request);
			$member->register();
		} else {
			$invitestatus = phpcom::$setting['register']['status'];
			$invitestatus = ($invitestatus == 2 || $invitestatus == 3) ? $invitestatus : 0;
			$regverify = phpcom::$setting['register']['verify'];
			$tplname = 'member/register';
			include template($tplname);
			return 1;
		}
		return 0;
	}
}
?>