<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Admin.php  2012-10-16
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Admin extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		if($this->uid && $this->allowadmin){
			header('location: ' . $this->adminscript);
		}else{
			header('location: member.php');
		}
	}
}
?>