<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Login.php  2012-8-5
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Ajax_Login extends Controller_AjaxAbstract
{
	public function loadActionIndex()
	{
		$type = intval($this->request->query('type'));
		$uid = $this->uid;
		$username = $this->username;
		$credits = $this->credits;
		$usergroup = $this->usergroup;
		$adminscript = $this->adminscript;
		$allowadmin = $this->allowadmin;
		$chanid = $this->chanid;
		include template('ajax/login');
		$this->loadAjaxFooter();
		return 0;
	}
}
?>