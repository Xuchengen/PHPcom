<?php
/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : eaccelerator.php    2011-7-5 23:42:23
 */
!defined('IN_PHPCOM') && exit('Access denied');

class cache_eaccelerator implements cache_interface 
{

	public function __construct($config = null) 
	{
		if (is_array($config) && count($config))
			$this->init($config);
	}

	public function init($config) 
	{
		unset($config);
	}

	public function get($key) 
	{
		return eaccelerator_get($key);
	}

	public function set($key, $value, $ttl = 0) 
	{
		return eaccelerator_put($key, $value, $ttl);
	}

	public function del($key) 
	{
		return eaccelerator_rm($key);
	}
	
	public function clear() 
	{
		return $this->clean();
	}
	
	public function clean()
	{
		return eaccelerator_gc();
	}
}
?>
