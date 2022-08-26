<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Abstract.php  2012-7-18
 */
!defined('IN_PHPCOM') && exit('Access denied');

abstract class Dependencies_Abstract
{
	protected $_controllerName;
	protected $_module;
	protected $_action;
	
	abstract public function route(Web_HttpRequest $request, $module);
	abstract public function allowAssigned($controller);
	abstract public function getControllerName();
	abstract public function getModule();
	abstract public function getAction();

}
?>