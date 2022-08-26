<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Sendpasswd.php  2012-8-9
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Ajax_Sendpasswd extends Controller_AjaxAbstract
{
	public function loadActionIndex()
	{
		include template('ajax/sendpasswd');
		$this->loadAjaxFooter();
		return 0;
	}
}
?>