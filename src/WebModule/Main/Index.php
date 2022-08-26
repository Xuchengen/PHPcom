<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Index.php  2012-7-25
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Main_Index extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$this->initialize();
		$this->title = $title = isset(phpcom::$setting['title']) ? phpcom::$setting['title'] : 'PHPcom CMS';
		include template('index');
		return 1;
	}
	
	public function writeToHtml($content = '')
	{
		if($this->checkHtmlKey()){
			$filename = PHPCOM_ROOT . '/index.html';
			if(!empty(phpcom::$setting['defaultindex']) && strcasecmp(phpcom::$setting['defaultindex'], 'index.php')){
				$filename = PHPCOM_ROOT . '/' . phpcom::$setting['defaultindex'];
			}
			fwrite_content($filename, $content);
		}
	}
}
?>