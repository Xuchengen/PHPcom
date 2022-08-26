<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Login.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Login extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$this->title = $title = lang('member', 'member_login');
		if (phpcom::$G['uid']) {
			showmessage('login_succeed', $this->getReferer(), array(
			'username' => phpcom::$G['username'],
			'usergroup' => phpcom::$G['group']['grouptitle']
			));
		}
		
		if (checksubmit(array('submit', 'btnlogin', 'ok', 'btnsubmit'), 1)) {
			$member = new MemberModel_Logging($this->request);
			$member->login();
		} else {
			$questionoption = '';
			$questionarray = lang('member', 'member_question_array');
			foreach ($questionarray as $key => $value) {
				$questionoption .= "<option value=\"$key\">$value</option>";
			}
			$tplname = 'member/login';
			include template($tplname);
			return 1;
		}
		return 0;
	}
}
?>