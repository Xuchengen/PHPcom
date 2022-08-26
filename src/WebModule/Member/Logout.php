<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Logout.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Logout extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$member = new MemberModel_Logging($this->request);
		$member->logout();
		return 0;
	}
}
?>