<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Http404.php  2012-8-11
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Main_Http404 extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$this->initialize();
		@header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
		@header("Status: 404 Not Found");
		$_SERVER["REDIRECT_STATUS"] = 404;
		
		$servername = $this->request->server('SERVER_NAME');
		$hostname = $this->request->server('HTTP_HOST');;
		$serversoft = $this->request->server('SERVER_SOFTWARE');
		$requesturi = $this->request->server('REQUEST_URI');
		$url = "http://{$hostname}" . htmlcharsencode(@urldecode($requesturi));
		$phpversion = PHP_VERSION;
		
		include template('http404');
		return 1;
	}

}
?>