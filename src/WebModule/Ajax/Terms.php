<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Terms.php  2012-8-6
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Ajax_Terms extends Controller_AjaxAbstract
{
	public function loadActionIndex()
	{
		$termstext = phpcom::$setting['register']['termstext'];
		$termstext = str_replace(array('{sitename}','{webname}'), $this->webname, $termstext);
		$termstext = nl2br($termstext);
		include template('ajax/terms');
		$this->loadAjaxFooter();
		return 0;
	}
}
?>