<?php
/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : autoload.php    2011-7-5 18:52:20
 */
!defined('IN_PHPCOM') && exit('Access denied');
class coreAutoload {
	static protected $_registered = false, $_instance = null;
	protected $baseDir = null;
	
	protected function __construct() {
		$this->baseDir = realpath(dirname(__FILE__) . '/..');
	}
	
	/**
	 * 得到这个类的实例
	 *
	 * @return coreAutoload
	 */
	public static function instance() {
		if (!isset(self::$_instance)) {
			self::$_instance = new coreAutoload();
		}

		return self::$_instance;
	}

	/**
	 * register coreAutoload
	 *
	 * @return void
	 */
	public static function registerAutoload() {
		if (self::$_registered) {
			return;
		}

		ini_set('unserialize_callback_func', 'spl_autoload_call');
		if (false === spl_autoload_register(array(self::instance(), 'autoload'))) {
			throw new Exception(sprintf('Unable to register %s::autoload as an autoloading method.', get_class(self::instance())));
		}

		self::$_registered = true;
	}
	
	/**
	 * unregister coreAutoload
	 *
	 * @return void
	 */
	public static function unregisterAutoload() {
		spl_autoload_unregister(array(self::instance(), 'autoload'));
		self::$_registered = false;
	}

	/**
	 * Registered autoload the class
	 *
	 * @param string $class
	 * @return bool
	 */
	public function autoload($class) {
		if ($path = $this->getClassPath($class)) {
			require $path;
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Get class of file page
	 *
	 * @param string $class
	 * @return string
	 */
	public function getClassPath($class) {
		if (class_exists($class, FALSE) || interface_exists($class, FALSE) || !isset($this->classes[$class])) {
			return FALSE;
		}
		return $this->baseDir . '/class/' . $this->classes[$class];
	}

	/**
	 * Get the base directory
	 *
	 * @return string
	 */
	public function getBaseDir() {
		return $this->baseDir;
	}
	
	protected $classes = array(
		'DB' => 'db/database.php',
		'Captcha' => 'captcha.php',
		'Chinese' => 'chinese.php',
		'phpcom_admincp' => 'admincp.php',
		'phpcom_error' => 'error.php',
		'phpcomException' => 'exception.php',
		'dbException' => 'exception.php',
		'phpcom_adminhtml' => 'adminhtml.php',
		'phpcom_admincp' => 'admincp.php',
		'phpcom_cache' => 'cache.php',
		'phpcom_memory' => 'memory.php',
		'phpcom_init' => 'phpcom.php',
		'phpcom' => 'phpcom.php',
		'phpcomAutoload' => 'autoload.php',
		'phpcom_ftp' => 'ftp.php',
		'phpcom_upload' => 'upload.php',
		'phpcom_image' => 'image.php',
        'phpcom_card' => 'card.php',
		'zipfile' => 'zip.php',
		'SimpleUnzip' => 'zip.php',
		'SphinxClient' => 'sphinx.php',
        'WebRequestBase' => 'WebRequest.php',
		'phpmain_hook' => 'hook.php'
	);
}

class phpcomAutoload {
	protected static $classCache = array();
	protected static $classDirs = array('Admin' => 'admincp', 'Api' => 'api', 'Install' => 'install', 
			'Plugin' => 'plugin', 'Hook' => 'hook', 'WebModule' => 'WebModule', 'Model' => 'model', 'Zend' => 'Zend');
	
	public static function loadClass($class, $dirs = NULL) {
		if (class_exists($class, false) || interface_exists($class, false)) {
			return true;
		}
		$fileArray = explode('_', $class);
		$dir = 'class';
		if(isset(self::$classDirs[$fileArray[0]])){
			$dir = self::$classDirs[$fileArray[0]];
			array_shift($fileArray);
		}
		$file = implode(DIRECTORY_SEPARATOR, $fileArray) . '.php';
		//$file = strtoupper(str_replace('_', DIRECTORY_SEPARATOR, $class)) . '.php';
		if (!empty($dirs)) {
			$file = basename($file);
			self::loadFile($file, $dirs, false);
		} else {
			$filename = PHPCOM_PATH . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $file;
			if(!file_exists($filename)){
				//throw new Exception("File \"$file\" does not exist or class \"$class\" was not found in the file");
				return false;
			}
			require $filename;
		}

		if (!class_exists($class, false) && !interface_exists($class, false)) {
			//throw new Exception("File \"$file\" does not exist or class \"$class\" was not found in the file");
			return false;
		}
		return true;
	}
	
	public static function loadFile($fileName, $dirs = NULL, $isOnce = TRUE) {
		if (!empty($dirs) && (is_array($dirs) || is_string($dirs))) {
			if (is_array($dirs)) {
				$dirs = implode(DIRECTORY_SEPARATOR, $dirs);
			}
			$dirs = PHPCOM_PATH . DIRECTORY_SEPARATOR . trim($dirs, '\\/') . DIRECTORY_SEPARATOR;
		} else {
			$dirs = PHPCOM_PATH . DIRECTORY_SEPARATOR;
		}
		$fileName = $dirs . $fileName;
		if(!file_exists($fileName)){
			return false;
		}
		if ($isOnce) {
			include_once $fileName;
		} else {
			include $fileName;
		}
		return TRUE;
	}
	
	/**
	 * 自动加载机制
	 * @param string $class 类名
	 * @return mixed
	 */
	public static function autoload($class) {
		try {
			self::loadClass($class);
			return $class;
		} catch (Exception $e) {
			return FALSE;
		}
	}

	/**
	 * 注册自动加载机制
	 * @param string $class
	 * @param bool $enabled
	 */
	public static function registerAutoload($class = 'phpcomAutoload', $enabled = TRUE) {
		if (!function_exists('spl_autoload_register')) {
			throw new Exception('spl_autoload does not exist in this PHP installation');
		}

		self::loadClass($class);
		$methods = get_class_methods($class);
		if (!in_array('autoload', (array)$methods)) {
			throw new Exception("The class \"$class\" does not have an autoload() method");
		}

		if ($enabled === TRUE) {
			spl_autoload_register(array($class, 'autoload'));
		} else {
			spl_autoload_unregister(array($class, 'autoload'));
		}
	}
}

?>
