<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : cache.php    2011-7-5 23:38:25
 */
!defined('IN_PHPCOM') && exit('Access denied');

class phpcom_cache {

	static $isfilecache, $allowmemory;

	public static function updater($cacheName = '', $channel = 0) {
		$updatelist = empty($cacheName) ? array() : (is_array($cacheName) ? $cacheName : array($cacheName));
		if (!$updatelist) {
			@include_once loadlibfile('setting', 'lib/cache');
			build_cache_setting();
			$cachedir = PHPCOM_PATH . '/lib/cache';
			$cachedirhandle = dir($cachedir);
			while ($entry = $cachedirhandle->read()) {
				if (!in_array($entry, array('.', '..')) && preg_match("/^([\_\w]+)\.php$/", $entry, $entryr) && $entryr[1] != 'setting' && substr($entry, -4) == '.php' && is_file($cachedir . '/' . $entry)) {
					@include_once loadlibfile($entryr[1], 'lib/cache');
					call_user_func('build_cache_' . $entryr[1], $channel);
				}
			}
		} else {
			foreach ($updatelist as $entry) {
				@include_once loadlibfile($entry, 'lib/cache');
				call_user_func('build_cache_' . $entry, $channel);
			}
		}
	}

	/**
	 * 载入缓存数据
	 * @param array $cacheName 缓存名称
	 * @param bool $force 强制加载缓存
	 * @return bool 返回布尔值 TRUE
	 */
	public static function load($cacheName, $force = FALSE) {
		static $loadedcache = array();
		$cacheName = is_array($cacheName) ? $cacheName : array($cacheName);
		$cache_name = array();
		foreach ($cacheName as $k) {
			if (!isset($loadedcache[$k]) || $force) {
				$cache_name[] = $k;
				$loadedcache[$k] = TRUE;
			}
		}
		if (!empty($cache_name)) {
			$cachedata = self::getdata($cache_name);
			foreach ($cachedata as $name => $data) {
				if ($name == 'setting') {
					phpcom::$setting = $data;
				} elseif ($name == 'channel') {
					phpcom::$G['channel'] = $data;
				} elseif ($name == 'plugin') {
					phpcom::$plugin = $data;
				} elseif ($name == 'usergroup') {
					phpcom::$G['usergroup'] = $data;
				} elseif (str_exists($name, 'usergroup_' . phpcom::$G['groupid'])) {
					phpcom::$G['group'] = $data;
				} else {
					phpcom::$G['cache'][$name] = $data;
				}
			}
		}
		return TRUE;
	}

	public static function get($key) {
		static $cachedata = array();
		if (isset($cachedata[$key])) {
			return $cachedata[$key];
		}
		$data = self::getdata(array($key));
		$cachedata[$key] = $data[$key];
		return $cachedata[$key];
	}

	/**
	 * 获取缓存数据
	 * @param array $cacheName 缓存名
	 * @return string 返回缓存数据
	 */
	public static function getdata($cacheName) {
		self::checkiscache();
		$cache_data = array();
		$cacheName = is_array($cacheName) ? $cacheName : array($cacheName);
		if (self::$allowmemory) {
			$newarray = array();
			foreach ($cacheName as $name) {
				$cache_data[$name] = self::memory('get', $name);
				if ($cache_data[$name] === NULL) {
					$cache_data[$name] = NULL;
					$newarray[] = $name;
				}
			}

			if (empty($newarray)) {
				return $cache_data;
			} else {
				$cacheName = $newarray;
			}
		}
		if (self::$isfilecache) {
			$lostcaches = array();
			foreach ($cacheName as $name) {
				if (!isset($cache_data[$name])) {
					if (!@include(PHPCOM_ROOT . '/data/cache/cache_' . $name . '.php')) {
						$lostcaches[] = $name;
					}
				}
			}
			if (!$lostcaches) {
				return $cache_data;
			}
			$cacheName = $lostcaches;
			unset($lostcaches);
		}
		if(!DB::fetch_first("SELECT cachename FROM " . DB::table('systemcache') . " WHERE cachename='setting'")){
			self::updater(NULL);
		}
		$query = DB::query("SELECT * FROM " . DB::table('systemcache') . " WHERE cachename IN ('" . implode("','", $cacheName) . "')");
		while ($cacherow = DB::fetch_array($query)) {
			$cache_data[$cacherow['cachename']] = $cacherow['cachetype'] ? @unserialize($cacherow['cachevalue']) : $cacherow['cachevalue'];
			self::$allowmemory && (self::memory('set', $cacherow['cachename'], $cache_data[$cacherow['cachename']]));
			if (self::$isfilecache) {
				$cachedata = '$cache_data[\'' . $cacherow['cachename'] . '\'] = ' . var_export($cache_data[$cacherow['cachename']], TRUE) . ";\n\n";
				$fp = @fopen(PHPCOM_ROOT . '/data/cache/cache_' . $cacherow['cachename'] . '.php', 'wb');
				if ($fp) {
					fwrite($fp, "<?php\n/**\n * PHPcom cache file, Do not modify me!" .
							"\n * Created: " . date("M j, Y, G:i") .
							"\n * Identify: " . md5($cacherow['cachename'] . $cachedata . phpcom::$config['security']['key']) . "\n */\n\n!defined('IN_PHPCOM') && exit('Access denied');\n\n$cachedata?>");
					fclose($fp);
				}
			}
		}

		foreach ($cacheName as $name) {
			if (!isset($cache_data[$name]) || $cache_data[$name] === NULL) {
				$cache_data[$name] = NULL;
				self::$allowmemory && (self::memory('set', $name, array()));
			}
		}

		return $cache_data;
	}

	/**
	 * 保存缓存数据
	 * @param string $cacheName 缓存名称
	 * @param array $data 缓存数据
	 */
	public static function save($cacheName, $data) {
		self::checkiscache();
		$cachetype = 0;
		if (is_array($data)) {
			$cachetype = 1;
			$data = addslashes(serialize($data));
		}
		DB::query("REPLACE INTO " . DB::table('systemcache') . " (cachename, cachevalue, cachetime, cachetype) VALUES ('$cacheName', '$data', '" . TIMESTAMP . "', '$cachetype')");

		self::$allowmemory && self::memory('del', $cacheName);
		self::$isfilecache && @unlink(PHPCOM_ROOT . '/data/cache/cache_' . $cacheName . '.php');
	}

	public static function delete($cacheName, $delsql = FALSE) {
		self::checkiscache();
		$cacheName = is_array($cacheName) ? $cacheName : array($cacheName);
		$cache_name = array();
		foreach ($cacheName as $value) {
			$cache_name[] = "'$value'";
			self::$allowmemory && self::memory('del', $value);
			self::$isfilecache && @unlink(PHPCOM_ROOT . '/data/cache/cache_' . $value . '.php');
		}
		if ($delsql && $cache_name) {
			DB::delete('systemcache', 'cachename IN(' . implode(',', $cache_name) . ')');
		}
	}

	public static function memory($command = 'check', $key = '', $value = '', $ttl = 0) {
		if ($command == 'check') {
			return phpcom::$memory->enabled ? phpcom::$memory->type : '';
		} elseif (phpcom::$memory->enabled && in_array($command, array('set', 'get', 'del', 'rm'))) {
			switch ($command) {
				case 'set': return phpcom::$memory->set($key, $value, $ttl);break;
				case 'get': return phpcom::$memory->get($key);break;
				case 'rm':
				case 'del': return phpcom::$memory->del($key);break;
			}
		}
		return NULL;
	}

	public static function checkiscache() {
		if (self::$isfilecache === NULL) {
			self::$isfilecache = phpcom::$config['cache']['type'] == 'file';
			self::$allowmemory = self::memory('check');
		}
	}

	public static function writer($name, $cacheData, $prefix = 'cache_') {
		$cachedir = PHPCOM_ROOT . '/data/cache/';
		if (!is_dir($cachedir)) {
			@mkdir($cachedir, 0777);
		}
		$filename = $prefix . $name . '.php';
		$fp = @fopen($cachedir . $filename, 'wb');
		if ($fp) {
			fwrite($fp, "<?php\n/**\n * PHPcom cache file, Do not modify me!" .
					"\n * Created: " . date("M j, Y, G:i") .
					"\n * Identify: " . md5($filename . $cacheData . phpcom::$config['security']['key']) . "\n */\n\n!defined('IN_PHPCOM') && exit('Access denied');\n\n$cacheData?>");
			fclose($fp);
		} else {
			throw new phpcomException('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
		}
	}

	public static function getvars($data, $type = 'VAR') {
		$cachevalue = '';
		foreach ($data as $key => $value) {
			if (!preg_match("/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/", $key)) {
				continue;
			}
			if (is_array($val)) {
				$cachevalue .= "\$$key = " . self::arrayexport($value) . ";\n";
			} else {
				$val = addcslashes($value, '\'\\');
				$cachevalue .= $type == 'VAR' ? "\$$key = '$value';\n" : "define('" . strtoupper($key) . "', '$value');\n";
			}
		}
		return $cachevalue;
	}

	public static function arrayexport($array) {
		if (!is_array($array)) {
			return "'" . $array . "'";
		}
		return var_export($array, true);
	}
	
	public static function savesetting($setting, $value = null)
	{
		$setting_values = array();
		if(is_array($setting)){
			unset($value);
			foreach ($setting as $key => $value){
				if(is_array($value)){
					$value = serialize($value);
					$setting_values[] = "('$key', '$value', 'array')";
				}else{
					$setting_values[] = "('$key', '$value', 'string')";
				}
			}
		}else{
			if(is_array($value)){
				$value = serialize($value);
				$setting_values[] = "('$setting', '$value', 'array')";
			}else{
				$setting_values[] = "('$setting', '$value', 'string')";
			}
		}
		if($values = implode(',', $setting_values)){
			DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES $values");
			return 1;
		}
		return 0;
	}
}

?>
