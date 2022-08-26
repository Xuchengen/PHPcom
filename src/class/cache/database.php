<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : database.php  2012-12-16
 */
!defined('IN_PHPCOM') && exit('Access denied');

class cache_database extends cache_interface
{

	public function __construct($config = null)
	{
		if(!empty($config)){
			$this->init($config);
		}
	}
	
	public function init($config)
	{
		unset($config);
	}
	
	public function get($key)
	{
		static $data = null;
		if(!isset($data[$key])) {
			if($cache = DB::fetch_first("SELECT * FROM ".DB::table('cache_data')."WHERE cacheid='$key'")){
				$data[$key] = $cache['serialized'] ? unserialize($cache['data']) : trim($cache['data']);
				if($cache['expire'] && $cache['expire'] < time()){
					return false;
				}
			}else{
				return false;
			}
		}
		return $data[$key];
	}
	
	public function set($key, $value, $ttl = 0)
	{
		$serialized = 0;
		if (is_array($data)) {
			$serialized = 1;
			$value = addslashes(serialize($value));
		}
		return DB::insert('common_cache', array(
				'cacheid' => $key,
				'data' => $value,
				'expire' => time() + $ttl,
				'dateline' => time(),
				'serialized' => $serialized
		));
	}
	
	public function del($key)
	{
		return DB::delete('cache_data', array('cacheid' => $key));
	}
	
	public function clear()
	{
		return $this->clean();
	}
	
	public function clean()
	{
		DB::exec("TRUNCATE TABLE " . DB::table('cache_data'));
	}
}
?>