<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Digest.php  2013-9-27
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Main_Digest extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$this->initialize();
		$name = stripstring($this->request->query('name', $this->request->getQuery(0)));
		
		include template('digest');
		return 1;
	}
	
	public function writeToHtml($content = '')
	{
		if($this->checkHtmlKey()){
			$filename = PHPCOM_ROOT . '/digest.html';
			if (@$fp = fopen($filename, 'w')) {
				@fwrite($fp, $content);
				fclose($fp);
			}
		}
	}
}
?>