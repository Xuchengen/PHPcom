<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Clearcookies.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Clearcookies extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		if (is_array($_COOKIE) && empty(phpcom::$G['uid'])) {
			foreach (phpcom::$G['cookie'] as $key => $value) {
				phpcom::setcookie($key, '', -1, 0);
			}
			foreach ($_COOKIE as $key => $value) {
				setcookie($key, '', -1, phpcom::$config['cookie']['path'], '');
			}
		}
		showmessage('login_clear_cookies', '/');
		return 0;
	}
}
?>