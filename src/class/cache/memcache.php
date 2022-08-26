<?php
/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : memcache.php    2011-7-5 23:43:49
 */
!defined('IN_PHPCOM') && exit('Access denied');

class cache_memcache implements cache_interface 
{

	private $instance;
	public $enabled = false;

	public function __construct($config = null)
	 {
		if (is_array($config) && count($config))
			$this->init($config);
	}

	public function init($config) 
	{
		if (!empty($config['host'])) {
			$this->instance = new Memcache();
			if ($config['pconnect']) {
				$conn = @$this->instance->pconnect($config['host'], $config['port']);
			} else {
				$conn = @$this->instance->connect($config['host'], $config['port']);
			}
			$this->enabled = $conn ? true : false;
		}
	}

	public function get($key) 
	{
		return $this->instance->get($key);
	}
	
	public function getMulti($keys) {
		return $this->instance->get($keys);
	}
	
	public function set($key, $value, $ttl = 0) 
	{
		return $this->instance->set($key, $value, MEMCACHE_COMPRESSED, $ttl);
	}

	public function del($key) 
	{
		return $this->instance->delete($key, 0);
	}
	
	public function clear() 
	{
		return $this->clean();
	}
	
	public function clean()
	{
		return $this->instance->flush();
	}
	
	public function inc($key, $step = 1) {
		return $this->instance->increment($key, $step);
	}
	
	public function dec($key, $step = 1) {
		return $this->instance->decrement($key, $step);
	}
}