<?php
/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : xcache.php    2011-7-5 23:43:11
 */
!defined('IN_PHPCOM') && exit('Access denied');

class cache_xcache implements cache_interface 
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
		return xcache_get($key);
	}

	public function getMulti($keys)
	{
		return xcache_get($keys);
	}
	
	public function set($key, $value, $ttl = 0) 
	{
		return xcache_set($key, $value, $ttl);
	}

	public function del($key) 
	{
		return xcache_unset($key);
	}
	
	public function clear() 
	{
		return $this->clean();
	}
	
	public function clean()
	{
		$count = xcache_count(XC_TYPE_VAR);
		for ($i=0; $i < $count; $i++) {
			xcache_clear_cache(XC_TYPE_VAR, $i);
		}
		return true;
	}
	
	public function inc($key, $step = 1) {
		return xcache_inc($key, $step);
	}
	
	public function dec($key, $step = 1) {
		return xcache_dec($key, $step);
	}
}