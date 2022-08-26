<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : createindex_html.php    2012-1-10
 */
!defined('IN_PHPCOM') && exit('Access denied');
$win_nginx = PHP_OS == 'WINNT' && strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== FALSE;
if (!$win_nginx) {
	$filename = PHPCOM_ROOT . '/index.html';
	if(!empty(phpcom::$setting['defaultindex']) && strcasecmp(phpcom::$setting['defaultindex'], 'index.php')){
		$filename = PHPCOM_ROOT . '/' . phpcom::$setting['defaultindex'];
	}
    if ($content = http_get_contents(phpcom::$G['siteurl'] . 'apps/index.php')){
	    if (@$fp = fopen($filename, 'w')) {
	        @fwrite($fp, $content);
	        fclose($fp);
	    }
    }
}
?>
