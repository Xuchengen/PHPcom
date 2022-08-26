<?php
/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : interface.php    2011-7-5 23:45:42
 */
!defined('IN_PHPCOM') && exit('Access denied');
interface cache_interface {

	function get($key);

	function set($key, $value, $ttl = 0);

	function del($key);
}
?>
