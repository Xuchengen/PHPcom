<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : application.php  2012-7-8
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Application extends ArrayObject
{
	protected $initialized = false;
	protected static $_errorHandler = true;
	private static $_instance = null;
	private $keepGlobals = array('GLOBALS' => 1, '_GET' => 1, '_POST' => 1, '_REQUEST' => 1,
        '_COOKIE' => 1, '_SERVER' => 1, '_ENV' => 1, '_FILES' => 1,
    );
	
	public static function getInstance()
	{
		if (self::$_instance === null) {
			self::$_instance = new Application();
		}
	
		return self::$_instance;
	}
	
	public function beginInitialize()
	{
		if ($this->initialized){
			return;
		}
		ignore_user_abort(true);
		@ini_set('register_globals',false);
		@ini_set('output_buffering', false);
		if (!@ini_get('output_handler')) while (@ob_end_clean());
		//error_reporting(E_ALL | E_STRICT & ~8192);
		//set_error_handler(array('Application', 'errorHandler'));
		//set_exception_handler(array('Application', 'exceptionHandler'));
		$this->initialized = true;
	}
	
	public static function initialize()
	{
		self::getInstance()->beginInitialize();
	}
	
	public static function setTimezone($timeoffset = 0) {
		if (function_exists('date_default_timezone_set')) {
			@date_default_timezone_set('Etc/GMT' . ($timeoffset > 0 ? '-' : '+') . (abs($timeoffset)));
		}
	}
	
	public static function setErrorHandler($value = true)
	{
		self::$_errorHandler = $value;
	}
	
	public static function errorHandler($errorType, $errorString, $file, $line)
	{
		if(!self::$_errorHandler){
			return false;
		}
		
		if ($errorType & error_reporting()){
			throw new ErrorException($errorString, 0, $errorType, $file, $line);
		}
	}
	
	public static function exceptionHandler(Exception $e)
	{
		throw new Exception($e->getMessage());
	}
	
	public static function get($index)
	{
		$instance = self::getInstance();
		if (!$instance->offsetExists($index)) {
			throw new Exception("No entry is registered for key '$index'");
		}
	
		return $instance->offsetGet($index);
	}
	
	public static function set($index, $value)
	{
		$instance = self::getInstance();
		$instance->offsetSet($index, $value);
	}
	
	public function offsetExists($index)
	{
		return array_key_exists($index, $this);
	}
	
	public function __construct($array = array(), $flags = parent::ARRAY_AS_PROPS)
	{
		parent::__construct($array, $flags);
	}
}
?>