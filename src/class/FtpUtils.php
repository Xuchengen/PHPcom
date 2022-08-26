<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : FtpUtils.php  2013-2-7
 */
!defined('IN_PHPCOM') && exit('Access denied');

class FtpUtils
{
	public $enabled = false;
	public $handle;
	private $errcode = 0;
	private $config = array();
	private $ftpssl = false;
	
	public static function &instance()
	{
		static $_instance;
		if (empty($_instance)) {
			$_instance = new FtpUtils();
		}
		return $_instance;
	}
	
	public static function clear($string)
	{
		return str_replace(array("\n", "\r", '..'), '', $string);
	}
	
	public function __construct($config = array())
	{
		if (count($config) > 0){
			$this->initialize($config);
		}
	}
	
	public function initialize($config = array())
	{
		$this->enabled = false;
		if (!empty($this->config['on']) && !empty($this->config['host'])) {
			$this->enabled = true;
		}
	}
	
	public function open()
	{
		
	}
	
	public function connect()
	{
		
	}
	
	public function upload($local_file, $remote_file)
	{
		
	}
	
	public function delete($filename)
	{
		return @ftp_delete($this->handle, FtpUtils::clear($filename));
	}
	
	public function close()
	{
		return @ftp_close($this->handle);
	}
	
	private function setError($code)
	{
		$this->errcode = $code;
	}
	
	public function errorCode()
	{
		return $this->errcode;
	}
	
	public function setOption($option, $value)
	{
		if (function_exists('ftp_set_option')) {
			return @ftp_set_option($this->handle, $option, $value);
		}
	}
	
	public function mkdir($directory)
	{
		$return = true;
		$directory = FtpUtils::clear($directory);
		$dirs = explode('/', $directory);
		$dir = $comma = '';
		foreach ($dirs as $part) {
			if(empty($part)) continue;
			$dir .= $comma . $part;
			$comma = '/';
			if(@ftp_mkdir($this->handle, $dir)){
				$this->ftp_chmod($dir);
			}else{
				$return = false;
			}
		}
		return $return;
	}
	
	public function rmdir($directory)
	{
		return @ftp_rmdir($this->handle, FtpUtils::clear($directory));
	}
}
?>