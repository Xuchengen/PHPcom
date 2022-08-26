<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : file.php    2011-7-5 23:44:28
 */
!defined('IN_PHPCOM') && exit('Access denied');

class cache_file implements cache_interface 
{

	private $config = array();
	public $enabled = true;

	public function __construct($config = null) 
	{
		$this->init($config);
	}

	public function init($config) 
	{
		if (is_array($config) && count($config)) {
			$this->config = $config;
		} else {
			$this->config = array('path' => PHPCOM_ROOT . '/data/cache');
		}
	}

	public function get($key) 
	{
		if ($this->cache_exists($key)) {
			$cache_data = $this->get_cache($key);
			return $cache_data['data'];
		}
		return false;
	}

	public function set($key, $value, $ttl = 0) 
	{
		$cache_data = array($key => array('data' => $value, 'life' => $ttl));
		$cache_file = $this->get_cache_file($key);
		$dir = dirname($cache_file);
		if ($dir && !is_dir($dir)) {
			@mkdir($dir);
			@touch($dir . '/index.htm');
			@chmod($dir . '/index.htm', 0777);
		}
		$cachedata = "\$cache_data = " . $this->export_array($cache_data) . ";\n";
		$fp = @fopen($cache_file, 'wb');
		if ($fp) {
			fwrite($fp, "<?php\n/**\n * PHPcom cache file, Do not modify me!" .
					"\n * Created: " . date("M j, Y, G:i") .
					"\n * Identify: " . md5($cache_file . $cachedata) . "\n */\n\n!defined('IN_PHPCOM') && exit('Access denied');\n\n$cachedata?>");
			fclose($fp);
		} else {
			exit('Can not write to cache files, please check directory ./data/ and ./data/cache/.');
		}
		return true;
	}

	public function del($key) 
	{
		$cache_file = $this->get_cache_file($key);
		if (file_exists($cache_file)) {
			return @unlink($cache_file);
		}
		return true;
	}
	
	public function clear()
	{
		$this->rmdirs();
		return true;
	}
	
	public function clean()
	{
		return $this->clear();
	}
	
	public function get_cache($key) 
	{
		static $cache_data = null;
		if (!isset($cache_data[$key])) {
			include $this->get_cache_file($key);
		}
		return $cache_data[$key];
	}

	public function cache_exists($key) 
	{
		$cache_file = $this->get_cache_file($key);
		if (!file_exists($cache_file)) {
			return false;
		}
		$cache_data = $this->get_cache($key);
		if ($cache_data['life'] && (filemtime($cache_file) < time() - $cache_data['life'])) {
			return false;
		}
		return true;
	}

	public function get_cache_file($key = null) 
	{
		static $cache_path = null;
		if(empty($key)){
			if(null !== $cache_path && is_array($cache_path)){
				return $cache_path;
			}
			return false;
		}
		if (!isset($cache_path[$key])) {
			//$dir = hexdec($key{0} . $key{1} . $key{2}) % 1000;
			$dir = md5($key);
			$key = str_replace(':', '_', $key);
			$cache_path[$key] = $this->config['path'] . '/' . $dir{0} . '/' . $key . '.php';
		}
		return $cache_path[$key];
	}
	
	public function export_array($array) 
	{
		if (!is_array($array)) {
			return "'" . $array . "'";
		}
		return var_export($array, true);
	}
	
	public function rmdirs($dir = null, $deleted = false) {
		$dir = $dir ? $dir : $this->config['path'];
		foreach(glob($dir . '/*') as $file) {
			if(is_dir($file))
				$this->rmdirs($file, true);
			else
				@unlink($file);
		}
		if (is_dir($dir) && $deleted) @rmdir( $dir );
	}
}
?>
