<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : redis.php  2012-12-16
 */
!defined('IN_PHPCOM') && exit('Access denied');

class cache_redis implements cache_interface
{
	private $instance;
	public $enabled = false;
	
	public function __construct($config = null)
	{
		if (is_array($config) && count($config))
			$this->init($config);
	}
	
	public static function getInstance($config = null)
	{
		static $instance;
		if(empty($instance)){
			$instance = new cache_redis($config);
		}
		return $instance;
	}
	
	public function init($config)
	{
		if (!empty($config['host'])) {
			$conn = false;
			try {
				$this->instance = new Redis();
				if ($config['pconnect']) {
					$conn = @$this->instance->pconnect($config['host'], $config['port']);
				} else {
					$conn = @$this->instance->connect($config['host'], $config['port']);
				}
			}catch (RedisException $e) {}
			$this->enabled = $conn ? true : false;
			if($this->enabled){
				@$this->instance->setOption(Redis::OPT_SERIALIZER, $config['serialized']);
				if(!empty($config['auth'])){
					$this->instance->auth($config['auth']);
				}
			}
		}
	}
	
	public function get($key)
	{
		return $this->instance->get($key);
	}
	
	public function getMulti($keys) {
		$result = $this->instance->getMultiple($keys);
		$newresult = array();
		$index = 0;
		foreach($keys as $key) {
			if($result[$index] !== false) {
				$newresult[$key] = $result[$index];
			}
			$index++;
		}
		unset($result);
		return $newresult;
	}
	
	public function select($db = 0)
	{
		return $this->instance->select($db);
	}
	
	public function set($key, $value, $ttl = 0)
	{
		if($ttl) {
			return $this->instance->setex($key, $ttl, $value);
		} else {
			return $this->instance->set($key, $value);
		}
	}
	
	public function del($key)
	{
		return $this->instance->delete($key);
	}
	
	public function clear()
	{
		return $this->clean();
	}
	
	public function clean()
	{
		return $this->instance->flushAll();
	}
	
	public function inc($key, $step = 1)
	{
		return $this->instance->incr($key, $step);
	}
	
	public function dec($key, $step = 1)
	{
		return $this->instance->decr($key, $step);
	}
	
	public function getSet($key, $value)
	{
		return $this->instance->getSet($key, $value);
	}
	
	public function sADD($key, $value)
	{
		return $this->instance->sADD($key, $value);
	}
	
	public function sRemove($key, $value)
	{
		return $this->instance->sRemove($key, $value);
	}
	
	public function sMembers($key)
	{
		return $this->instance->sMembers($key);
	}
	
	public function sIsMember($key, $member)
	{
		return $this->instance->sismember($key, $member);
	}
	
	public function keys($key)
	{
		return $this->instance->keys($key);
	}
	
	public function expire($key, $second)
	{
		return $this->instance->expire($key, $second);
	}
	
	public function sCard($key)
	{
		return $this->instance->sCard($key);
	}
	
	public function hSet($key, $field, $value)
	{
		return $this->instance->hSet($key, $field, $value);
	}
	
	public function hDel($key, $field)
	{
		return $this->instance->hDel($key, $field);
	}
	
	public function hLen($key)
	{
		return $this->instance->hLen($key);
	}
	
	public function hVals($key)
	{
		return $this->instance->hVals($key);
	}
	
	public function hIncrBy($key, $field, $incr)
	{
		return $this->instance->hIncrBy($key, $field, $incr);
	}
	
	public function hGetAll($key)
	{
		return $this->instance->hGetAll($key);
	}
	
	public function sort($key, $opt)
	{
		return $this->instance->sort($key, $opt);
	}
	
	public function exists($key)
	{
		return $this->instance->exists($key);
	}
}