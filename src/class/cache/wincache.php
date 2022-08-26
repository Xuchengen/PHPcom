<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : wincache.php  2013Äê7ÔÂ3ÈÕ
 */
!defined('IN_PHPCOM') && exit('Access denied');

class cache_wincache implements cache_interface
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
		return wincache_ucache_get($key);
	}
	
	public function set($key, $value, $ttl = 0)
	{
		return wincache_ucache_set($key, $value, $ttl);
	}
	
	public function del($key)
	{
		return wincache_ucache_delete($key);
	}
	
	public function clear()
	{
		return wincache_ucache_clear();
	}
	
	public function clean()
	{
		return wincache_ucache_clear();
	}
	
	public function inc($key, $step = 1) {
		return wincache_ucache_inc($key, $step);
	}
	
	public function dec($key, $step = 1) {
		return wincache_ucache_dec($key, $step);
	}
}