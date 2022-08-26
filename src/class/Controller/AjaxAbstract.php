<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : AjaxAbstract.php  2012-8-5
 */
!defined('IN_PHPCOM') && exit('Access denied');

abstract class Controller_AjaxAbstract extends Controller_MainAbstract
{
	public function __construct(Web_HttpRequest $request)
	{
		parent::__construct($request);
		define('DOMAIN_ENABLED', true);
		$this->initialize();
		$this->loadAjaxHeader();
	}
}
?>