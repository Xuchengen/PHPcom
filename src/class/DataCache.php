<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : DataCache.php  2013-8-5
 */
!defined('IN_PHPCOM') && exit('Access denied');

class DataCache
{
	private static $enabled = true;
	
	public static function &getCacheObject() {
		static $object = null;
		if($object !== null) return $object;
		if(extension_loaded('redis') 
			&& !empty(phpcom::$config['cache']['redis']['host'])
			&& strcmp(phpcom::$config['cache']['enabled'], 'memcache')){
			$object = new cache_redis();
			$object->init(phpcom::$config['cache']['redis']);
			if (!$object->enabled) {
				$object = false;
			}
		}elseif(extension_loaded('memcache') && !empty(phpcom::$config['cache']['memcache']['host'])){
			$object = new cache_memcache();
			$object->init(phpcom::$config['cache']['memcache']);
			if (!$object->enabled) {
				$object = false;
			}
		}else{
			$object = new cache_file();
			$config = empty(phpcom::$config['cache']['file']) ? null : phpcom::$config['cache']['file'];
			$object->init($config);
		}
		self::$enabled = false;
		if (is_object($object)) {
			self::$enabled = true;
		}
		if(empty(phpcom::$config['cache']['enabled'])) {
			self::$enabled = false;
		}
		return $object;
	}
	
	public static function set($key, $value, $ttl = 0) {
		if(self::$enabled) {
			if( $ret = self::getCacheObject()->set(self::_key($key), array($value), $ttl)){
				return $ret;
			}
		}
		return false;
	}
	
	public static function get($key) {
		if(self::$enabled) {
			$ret = self::getCacheObject()->get(self::_key($key));
			return is_array($ret) ? $ret[0] : $ret;
		}
		return false;
	}
	
	public static function del($key) {
		if(self::$enabled) {
			self::getCacheObject()->del(self::_key($key));
		}
		return true;
	}
	
	public static function clear() {
		if(self::$enabled) {
			self::getCacheObject()->clear();
		}
		return true;
	}
	
	public static function getData($key) {
		return self::get($key);
	}
	
	public static function setData($key, $value, $ttl = 0) {
		return self::set($key, $value, $ttl);
	}
	
	public static function getBlock($key) {
		return self::get($key);
	}
	
	public static function setBlock($key, $value, $ttl = 300) {
		return self::set($key, $value, $ttl);
	}
	
	public static function getBody($key) {//setData  setThread  setList  Data:1  body:1  data:1  src 15002725617
		return self::get($key);
	}
	
	public static function setBody($key, $value, $ttl = 300) {
		return self::set($key, $value, $ttl);
	}
	
	private static function _key($key) {
		return phpcom::$config['cache']['prefix'] . $key;
	}
}

?>