<?php
/** 
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : header.php    2011-8-6 1:20:54
 */
!defined('IN_PHPCOM') && exit('Access denied');
@ob_end_clean();
ob_start();
//@header('Access-Control-Allow-Origin: *');
@header('Content-Type: text/xml;charset=' . CHARSET);
@header('Expires: -1');
@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
@header('Pragma: no-cache');
echo '<?xml version="1.0" encoding="' . CHARSET . '"?>', "\r\n";
echo "<root><![CDATA[";
?>
