<?php
/**
 * Copyright (c) 2010-2012 phpmain.com - All rights reserved.
 * Our Website : www.phpmain.com www.phpmain.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPMain.
 * This File   : function.php  2012-4-7
 */
!defined('IN_PHPCOM') && exit('Access denied');

function md5salt($string, $salt = '') {
	return md5(substr(md5($string), 8, 16) . $salt);
}

function random($length = 16) {
	$hash = '';
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$max = strlen($chars) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}

function T($key){
	return isset($GLOBALS['lang'][$key]) ? $GLOBALS['lang'][$key] : $key;
}

function timezone_set($timeoffset = 8) {
	if(function_exists('date_default_timezone_set')) {
		@date_default_timezone_set('Etc/GMT'.($timeoffset > 0 ? '-' : '+').(abs($timeoffset)));
	}
}

function get_clientip() {
	$clientip = (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) ? htmlspecialchars((string) $_SERVER['HTTP_X_FORWARDED_FOR']) : '';
	$clientip = check_clientip($clientip);
	if (empty($clientip)) {
		$clientip = (!empty($_SERVER['REMOTE_ADDR'])) ? htmlspecialchars((string) $_SERVER['REMOTE_ADDR']) : '';
		$clientip = check_clientip($clientip);
	}
	return $clientip ? $clientip : 'unknown';
}

function getscriptname(){
	$filename = isset($_SERVER['SCRIPT_FILENAME']) ? basename($_SERVER['SCRIPT_FILENAME']) : '';
	$selfname = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$selfname = $_SERVER['PHP_SELF'];
	if (strcasecmp(basename($selfname), $filename)) {
		$selfname = substr($selfname, 0, strpos($selfname, $filename) + strlen($filename));
	}
	return $selfname;
}

function check_clientip($xip) {
	static $ipv4expression = '#^(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#';
	static $ipv6expression = '#^(?:(?:(?:[\dA-F]{1,4}:){1,6}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:::(?:[\dA-F]{1,4}:){0,5}(?:[\dA-F]{1,4}(?::[\dA-F]{1,4})?|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:):(?:[\dA-F]{1,4}:){4}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,2}:(?:[\dA-F]{1,4}:){3}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,3}:(?:[\dA-F]{1,4}:){2}(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,4}:(?:[\dA-F]{1,4}:)(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,5}:(?:[\dA-F]{1,4}:[\dA-F]{1,4}|(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])))|(?:(?:[\dA-F]{1,4}:){1,6}:[\dA-F]{1,4})|(?:(?:[\dA-F]{1,4}:){1,7}:)|(?:::))$#i';

	$clientip = '';
	if (!empty($xip) && strcasecmp($xip, 'unknown')) {
		$xip = preg_replace('# {2,}#', ' ', str_replace(array(',', ';', '%'), ' ', $xip));
		$ips = explode(' ', $xip);
		foreach ($ips as $ip) {
			if (preg_match($ipv4expression, $ip)) {
				$clientip = $ip;
			} else if (preg_match($ipv6expression, $ip)) {
				if (stripos($ip, '::ffff:') === 0) {
					$ipv4 = substr($ip, 7);
					if (preg_match($ipv4expression, $ipv4)) {
						$ip = $ipv4;
					}
				}
				$clientip = $ip;
			} else {
				break;
			}
		}
		return $clientip;
	} else {
		return '';
	}
}

function stricmp($string, $needle, $return = false, $default = false)
{
	if(is_array($needle)){
		foreach ($needle as $value){
			if (strcasecmp($string, $value) == 0) {
				return $return ? $value : true;
			}
		}
		return $default;
	}else{
		return (strcasecmp($string, $needle) == 0);
	}
}

function implodein($array) {
	if(!empty($array)) {
		if(is_array($array)){
			return "'".implode("','", $array)."'";
		}else{
			return "'$array'";
		}
	}else{
		return '';
	}
}

function implode_field_value($array, $glue = ',') {
	$sql = $comma = '';
	foreach ($array as $k => $v) {
		$sql .= $comma."`$k`='$v'";
		$comma = $glue;
	}
	return $sql;
}

function add_slashes($string, $force = 1) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = add_slashes($val, $force);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

function strip_slashes($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = strip_slashes($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}

function htmlcharsencode($string){
	if (is_array($string)) {
		foreach($string as $k => $v){
			$string[$k] = htmlcharsencode($v);
		}
	}else{
		$string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
		if (strpos($string, '&amp;#') !== FALSE) {
			$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
		}
	}
	return $string;
}

function htmlcharsdecode($string){
	if (is_array($string)) {
		foreach($string as $k => $v){
			$string[$k] = htmlcharsdecode($v);
		}
	}else{
		$string = str_replace(array('&apos;', '&#039;', '&quot;', '&lt;', '&gt;', '&amp;'), array("'", "'", '"', '<', '>', '&'), $string);
	}
	return $string;
}

function rmkdir($dir, $mode = 0777){
	if(!is_dir($dir)) {
		rmkdir(dirname($dir));
		@mkdir($dir, $mode);
		@touch($dir.'/index.htm');
		@chmod($dir.'/index.htm', 0777);
	}
	return true;
}

function dirwriteable($dir) {
	$writeable = 0;
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = @fopen("$dir/test.txt", 'w')) {
			@fclose($fp);
			@unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
}

function check_dirfile($path, $type){
	$status = 0;
	if($type == 'dir'){
		if(!dirwriteable(ROOT_PATH.$path)) {
			if(is_dir(ROOT_PATH.$path)) {
				$status = 0;
			} else {
				$status = -1;
			}
		} else {
			$status = 1;
		}
	}else{
		if(file_exists(ROOT_PATH.$path)) {
			if(is_writable(ROOT_PATH.$path)) {
				$status = 1;
			} else {
				$status = 0;
			}
		} else {
			if(dirwriteable(dirname(ROOT_PATH.$path))) {
				$status = 1;
			} else {
				$status = -1;
			}
		}
	}
	return $status;
}

function dir_clear($dir) {
	global $lang;
	showjsmessage($lang['clear_dir'].' '.str_replace(ROOT_PATH, '', $dir));
	if($d = @dir($dir)) {
		$dir = rtrim($dir, '/\ ');
		while($entry = $d->read()) {
			if ($entry !== '.' && $entry !== '..') {
				$filename = $dir.'/'.$entry;
				if(is_file($filename)) {
					@unlink($filename);
				}
			}
		}
		$d->close();
		@touch($dir.'/index.htm');
	}
}

function removedir($directory, $onlyfile = FALSE, $subdir = TRUE){
	if (!file_exists($directory)) return TRUE;
	if(is_dir($directory) && $handle = @opendir($directory)) {
		$directory = rtrim($directory, '/\ ') . DIRECTORY_SEPARATOR;
		while(FALSE !== ($file = readdir($handle))){
			if ($file != "." && $file != "..") {
				@chmod($directory . $file, 0777);
				if(is_dir($directory . $file)){
					if(!$onlyfile || ($onlyfile && $subdir)){
						if(!removedir($directory . $file, $onlyfile, $subdir)){
							continue;
						}
					}
				}else{
					@unlink($directory . $file);
				}
			}
		}
		closedir($handle);
		if($onlyfile){
			return TRUE;
		}else{
			return @rmdir($directory);
		}
	}else{
		return @unlink($directory);
	}
}

function setconfigarray($array, $default = NULL) {
	if (is_array($default)) {
		foreach ($default as $k => $v) {
			if (!isset($array[$k])) {
				$array[$k] = $default[$k];
			} elseif (is_array($v)) {
				$array[$k] = setconfigarray($array[$k], $default[$k]);
			}
		}
	}
	return $array;
}

function exportconfigarray($array, $level = 0, $keyname = null, $varname = '$_config') {
	$result = null;
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			if ($level == 0) {
				$tokens = str_pad('  CONFIG ' . strtoupper($key) . '  ', 70, '-', STR_PAD_BOTH);
				$result .= "\r\n/* $tokens */\r\n";
			}
			if (is_array($value)) {
				$kname = $keyname . "['$key']";
				$result .= exportconfigarray($value, $level + 1, $kname, $varname);
			} else {
				$value = is_string($value) || strlen($value) > 12 || !preg_match("/^\-?[1-9]\d*$/", $value) ? '\'' . addcslashes($value, '\'\\') . '\'' : $value;
				$result .= $varname . $keyname . "['$key'] = $value;\r\n";
			}
		}
	}
	return $result;
}

function check_db($dbhost, $dbuser, $dbpwd, $dbname, $tablepre) {
	if(!function_exists('mysql_connect')) {
		show_message('undefine_function', 'mysql_connect', 0);
	}
	if(!@mysql_connect($dbhost, $dbuser, $dbpwd)) {
		$errno = mysql_errno();
		$error = mysql_error();
		if($errno == 1045) {
			show_message('database_errno_1045');
		} elseif($errno == 2003) {
			show_message('database_errno_2003');
		} else {
			show_message('database_connect_error');
		}
	} else {
		if($query = @mysql_query("SHOW TABLES FROM $dbname")) {
			while($row = mysql_fetch_row($query)) {
				if(preg_match("/^$tablepre/", $row[0])) {
					return false;
				}
			}
		}
	}
	return true;
}

function array2object($arr) {
	if (is_array($arr)) {
		return (object) array_map(__FUNCTION__, $arr);
	} else {
		return $arr;
	}
}

function object2array($obj) {
	if (is_object($obj)) {
		$obj = get_object_vars($obj);
	}
	if (is_array($obj)) {
		return array_map(__FUNCTION__, $obj);
	} else {
		return $obj;
	}
}
?>