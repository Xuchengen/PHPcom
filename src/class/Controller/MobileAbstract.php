<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : MobileAbstract.php  2013Äê7ÔÂ15ÈÕ
 */
!defined('IN_PHPCOM') && exit('Access denied');

abstract class Controller_MobileAbstract extends Controller_MainAbstract
{
	public function __construct(Web_HttpRequest $request)
	{
		parent::__construct($request);
		$this->iscaptcha = intval(phpcom::$setting['captchastatus'][4]);
	}
}
?>