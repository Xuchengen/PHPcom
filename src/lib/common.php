<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : common.php    2011-7-7 12:42:23
 */
!defined('IN_PHPCOM') && exit('Access denied');
define('PHPCOM_COMMON_LIB', TRUE);

function fmdate($timestamp, $format = 'dt', $type = '', $timeoffset = '') {
	static $dateformat, $timeformat, $dtformat, $offset, $datelang;
	if ($dateformat === NULL) {
		$dateformat = phpcom::$setting['dateformat'];
		$timeformat = phpcom::$setting['timeformat'];
		$dtformat = trim($dateformat . ' ' . $timeformat);
		$offset = phpcom::$setting['timeoffset'];
		$datelang = lang('common', 'date');
	}
	$timeoffset = $timeoffset === '' || $timeoffset == 9999 ? $offset : $timeoffset;
	$timestamp += $timeoffset * 3600;
	if($format === 'u'){
		$type = 'u';
		$format = $dtformat;
	}
	$format = empty($format) || $format === 'dt' ? $dtformat : ($format === 'd' ? $dateformat : ($format === 't' ? $timeformat : $format));
	if ($type === 'u') {
		$todaytimestamp = TIMESTAMP - (TIMESTAMP + $timeoffset * 3600) % 86400 + $timeoffset * 3600;
		$time = TIMESTAMP + $timeoffset * 3600 - $timestamp;
		$date = gmdate($format, $timestamp);
		if ($timestamp >= $todaytimestamp) {
			if ($time > 3600) {
				return '<span title="' . $date . '">' . intval($time / 3600) . ' ' . $datelang['hour'] . $datelang['before'] . '</span>';
			} elseif ($time > 1800) {
				return '<span title="' . $date . '">' . $datelang['half'] . $datelang['hour'] . $datelang['before'] . '</span>';
			} elseif ($time > 60) {
				return '<span title="' . $date . '">' . intval($time / 60) . ' ' . $datelang['min'] . $datelang['before'] . '</span>';
			} elseif ($time > 0) {
				return '<span title="' . $date . '">' . $time . ' ' . $datelang['sec'] . $datelang['before'] . '</span>';
			} elseif ($time == 0) {
				return '<span title="' . $date . '">' . $datelang['now'] . '</span>';
			} else {
				return $s;
			}
		} elseif (($days = intval(($todaytimestamp - $timestamp) / 86400)) >= 0 && $days < 7) {
			if ($days == 0) {
				return '<span title="' . $date . '">' . $datelang['yday'] . ' ' . gmdate($timeformat, $timestamp) . '</span>';
			} elseif ($days == 1) {
				return '<span title="' . $date . '">' . $datelang['byday'] . ' ' . gmdate($timeformat, $timestamp) . '</span>';
			} else {
				return '<span title="' . $date . '">' . ($days + 1) . ' ' . $datelang['day'] . $datelang['before'] . '</span>';
			}
		} else {
			return $date;
		}
	} elseif ($type === 'd') {
		$todaytimestamp = TIMESTAMP - (TIMESTAMP + $timeoffset * 3600) % 86400 + $timeoffset * 3600;
		$date = gmdate($format, $timestamp);
		if ($timestamp >= $todaytimestamp) {
			return "<em class=\"new\">$date</em>";
		} else {
			return "<em class=\"old\">$date</em>";
		}
	} else {
		return gmdate($format, $timestamp);
	}
}

function preprint($expression) {
	echo '<pre>';
	print_r($expression);
	echo '</pre>';
}


/**
 * 取得一个日期的 Unix 时间戳
 * @param string $format 时间格式
 * @return int 返回一个日期的 Unix 时间戳
 */
function maketime($format = '0') {
	$timestamp = '';
	switch ($format) {
		case 'd': $timestamp = mktime(0, 0, 0, date("m"), date("d"), date("Y")); break;
		case 'w': $timestamp = strtotime("last Sunday"); break;
		case 'm': $timestamp = mktime(0, 0, 0, date("m"), 1, date("Y")); break;
		case 'y': $timestamp = mktime(0, 0, 0, 1, 1, date("Y")); break;
		case 'D': $timestamp = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")); break;
		case 'W': $timestamp = strtotime("next Sunday"); break;
		case 'M': $timestamp = mktime(0, 0, 0, date("m") + 1, 1, date("Y")); break;
		case 'Y': $timestamp = mktime(0, 0, 0, 1, 1, date("Y") + 1); break;
		case '0': $timestamp = strtotime(0); break;
		default:
			$timedf = phpcom::$setting['timeoffset'];
			$time = $timedf ? $timedf * 3600 : 0;
			$timestamp = time() + $time;
			break;
	}
	return $timestamp;
}

function checkrobot($useragent = '') {
	static $needle_spiders = array('bot', 'crawl', 'spider', 'slurp', 'sohu-search', 'lycos', 'robozilla', 'ia_archiver');
	static $needle_browsers = array('msie', 'netscape', 'opera', 'konqueror', 'mozilla');
	$_SERVER['HTTP_USER_AGENT'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)';
	$useragent = strtolower(empty($useragent) ? $_SERVER['HTTP_USER_AGENT'] : $useragent);
	if (strpos($useragent, 'http://') === FALSE && str_exists($useragent, $needle_browsers)) return FALSE;
	return str_exists($useragent, $needle_spiders);
}

function updatesession() {
	static $is_updated = FALSE;
	if (!$is_updated) {
		if (phpcom::$G['uid']) {
			if (isset(phpcom::$G['cookie']['lastactivitytime']) && phpcom::$G['cookie']['lastactivitytime']) {
				$lastactivitytime = decryptstring(phpcom::$G['cookie']['lastactivitytime']);
			} else {
				$lastactivitytime = getuserdata('lastactivity');
				phpcom::setcookie('lastactivitytime', encryptstring($lastactivitytime), 31536000);
			}
		}
		$uid = phpcom::$G['uid'];
		$phpcom = &phpcom_init::instance();
		$onlinetime = phpcom::$setting['onlinetime'];
		$lastupdated = $phpcom->session->var['lastupdated'];
		if (phpcom::$G['uid'] && $onlinetime && TIMESTAMP - ($lastupdated ? $lastupdated : $lastactivitytime) > $onlinetime * 60) {
			DB::query("UPDATE " . DB::table('onlinetime') . "
			SET totaltime=totaltime+'$onlinetime', thismonth=thismonth+'$onlinetime', lastupdate='" . TIMESTAMP . "'
			WHERE uid='$uid'");
			if (!DB::affected_rows()) {
				DB::insert('onlinetime', array(
				'uid' => $uid,
				'thismonth' => $onlinetime,
				'totaltime' => $onlinetime,
				'lastupdate' => TIMESTAMP,
				));
			}
			$phpcom->session->set('lastupdated', TIMESTAMP);
		}
		foreach ($phpcom->session->var as $k => $v) {
			if (isset(phpcom::$G['member'][$k]) && $k != 'lastactivity') {
				$phpcom->session->set($k, phpcom::$G['member'][$k]);
			}
		}
		$phpcom->session->update();
		$is_updated = TRUE;
		if (phpcom::$G['uid'] && (TIMESTAMP - $lastactivitytime > 21600 || $lastactivitytime > TIMESTAMP)) {
			if ($onlinetime && TIMESTAMP - $lastactivitytime > 7200) {
				$totaltime = DB::result_first("SELECT totaltime FROM " . DB::table('onlinetime') . " WHERE uid='$uid'");
				DB::update('member_count', array('onlinetime' => round(intval($totaltime) / 60)), "uid='$uid'", 0, 1);
			}
			$today = strtotime(fmdate(TIMESTAMP, 'Y-m-d'));
			if ($lastactivitytime < $today) {
				DB::query("UPDATE " . DB::table('member_count') . " SET logins=logins+1 WHERE uid='$uid'");
				phpcom::$G['member']['logins'] = phpcom::$G['member']['logins'] + 1;
			}
			phpcom::setcookie('lastactivitytime', encryptstring(TIMESTAMP), 31536000);
			DB::update('member_status', array('lastip' => phpcom::$G['clientip'], 'lastactivity' => TIMESTAMP, 'lastvisit' => TIMESTAMP), "uid='$uid'", 0, 1);
		}
	}
	return $is_updated;
}

function getuserdata($field) {
	if (isset(phpcom::$G['member'][$field])) {
		return phpcom::$G['member'][$field];
	}
	static $tablefields = array(
			'member_count' => array('money', 'prestige', 'currency', 'praise', 'digests', 'logins', 'threads', 'polls', 'friends', 'attachsize', 'todayattachs', 'todayattchsize', 'askings', 'answers'),
			'member_info' => array('gender', 'realname', 'idcard', 'company', 'address', 'homepage', 'qq', 'msn', 'taobao', 'zipcode', 'phone', 'mobile', 'fax', 'usersign', 'birthday'),
			'member_status' => array('regip', 'lastip', 'lastvisit', 'lastactivity', 'lastpost')
	);
	$membertable = '';
	foreach ($tablefields as $table => $fields) {
		if (in_array($field, $fields)) {
			$membertable = $table;
			break;
		}
	}
	if ($membertable && phpcom::$G['uid']) {
		$uid = phpcom::$G['uid'];
		$data = DB::fetch_first("SELECT " . implode(', ', $tablefields[$membertable]) . " FROM " . DB::table($membertable) . " WHERE uid='$uid'");
		if (!$data) return '';
		phpcom::$G['member'] = array_merge(is_array(phpcom::$G['member']) ? phpcom::$G['member'] : array(), $data);
		return phpcom::$G['member'][$field];
	} else {
		return '';
	}
}

function getmembernamebyuid($name) {
	$name = stripstring($name);
	if($member = DB::fetch_first("SELECT uid FROM " . DB::table('members') . " WHERE username='$name'")){
		return $member['uid'];
	}
	return false;
}

function getmemberuidbyname($uid) {
	if($member = DB::fetch_first("SELECT username FROM " . DB::table('members') . " WHERE uid='$uid'")){
		return $member['username'];
	}
	return null;
}

function cutstr($string, $length, $ellipsis = ' ...') {
	return strcut($string, $length, $ellipsis);
}

function strcut($string, $length, $ellipsis = ' ...') {
	if ($length && strlen($string) > $length) {
		$pre = chr(1);
		if(strpos($string , '&') !== false && strpos($string , ';', 2) !== false){
			if (strcasecmp(CHARSET, 'utf-8') === 0) {
				$string = str_replace(array('&ldquo;', '&rdquo;'), array(chr(0xE2) . chr(0x80) . chr(0x9C), chr(0xE2) . chr(0x80) . chr(0x9D)), $string);
				$string = str_replace(array('&lsquo;', '&rsquo;'), array(chr(0xE2) . chr(0x80) . chr(0x98), chr(0xE2) . chr(0x80) . chr(0x99)), $string);
				$string = str_replace(array('&mdash;', '&hellip;'), array(chr(0xE2) . chr(0x80) . chr(0x94), chr(0xE2) . chr(0x80) . chr(0xA6)), $string);
				$string = str_replace(array('&middot;', '&uml;'), array(chr(0xC2) . chr(0xB7), chr(0xC2) . chr(0xA8)), $string);
				$string = str_replace(array('&ndash;'), array(chr(0xe2) . chr(0x80) . chr(0x93)), $string);
			}else{
				$string = str_replace(array('&ldquo;', '&rdquo;'), array(chr(0xA1) . chr(0xB0), chr(0xA1) . chr(0xB1)), $string);
				$string = str_replace(array('&lsquo;', '&rsquo;'), array(chr(0xA1) . chr(0xAE), chr(0xA1) . chr(0xAF)), $string);
				$string = str_replace(array('&mdash;', '&hellip;'), array(chr(0xA1) . chr(0xAA), chr(0xA1) . chr(0xAD)), $string);
				$string = str_replace(array('&middot;', '&uml;'), array(chr(0xA1) . chr(0xA4), chr(0xA1) . chr(0xA7)), $string);
				$string = str_replace(array('&ndash;'), array(chr(0xa8) . chr(0x43)), $string);
			}
			$string = str_replace(array('&nbsp;', '&amp;', '&quot;', '&lt;', '&gt;'), array( $pre . ' ', $pre . '&', $pre . '"', $pre . '<', $pre . '>'), $string);
		}
		$strcut = '';
		if (strcasecmp(CHARSET, 'utf-8') === 0) {
			$i = $n = 0;
			while ($n < strlen($string)) {
				if ($i >= $length) break;
				switch (($b = ord($string{$n})) == $b) {
					case (($b & 0xF8) == 0x00): ++$n; break;
					case (($b & 0xE0) == 0xC0): $i += 2; $n += 2; break;
					case (($b & 0xF0) == 0xE0): $i += 2; $n += 3; break;
					case (($b & 0xF8) == 0xF0): $i += 2; $n += 4; break;
					case (($b & 0xFC) == 0xF8): $i += 2; $n += 5; break;
					case (($b & 0xFE) == 0xFC): $i += 2; $n += 6; break;
					default: ++$i; ++$n;
				}
			}
			$strcut = substr($string, 0, $n);
		}else{
			$i = $n = 0;
			while ($n < strlen($string)) {
				if ($i >= $length) break;
				switch (($b = ord($string{$n})) == $b) {
					case (($b & 0xF8) == 0x00): ++$n; break;
					case (($b & 0x80) == 0x80): $i += 2; $n += 2; break;
					default: ++$i; ++$n;
				}
			}
			$strcut = substr($string, 0, $n);
		}
		if(strpos($strcut , chr(1)) !== false){
			$strcut = str_replace(array($pre . ' ', $pre . '&', $pre . '"', $pre . '<', $pre . '>'), array('&nbsp;', '&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
		}
		return trim($strcut) . $ellipsis;
	}
	return trim($string);
}

function strlength($string) {
	if (strcasecmp(CHARSET, 'utf-8')) {
		return strlen($string);
	}else{
		$count = 0;
		$len = strlen($string);
		for ($i = 0; $i < $len; $i++) {
			$b = ord($string[$i]);
			if ($b > 127) {
				$count++;
				if (($b & 0xE0) == 0xC0) $i++;
				elseif (($b & 0xF0) == 0xE0) $i += 2;
				elseif (($b & 0xF8) == 0xF0) $i += 3;
			}
			$count++;
		}
		return $count;
	}
}

function showmessage($message, $url = '', $vars = array(), $extras = array()) {
	require_once loadlibfile('message');
	return phpcom_showmessage($message, $url, $vars, $extras);
}

function logwriter($file, $message, $halt = 0) {
	$url = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : ($_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);
	$log = fmdate(TIMESTAMP, 'Y-m-d H:i:s') . "\t" . phpcom::$G['clientip'] . "\t" . phpcom::$G['uid'] . "\t{$url}\t" . str_replace(array("\r", "\n"), array(' ', ' '), trim($message)) . "\n";
	writelog($file, $log);
	$halt && exit();
}

function writelog($file, $log) {
	$yearmonth = fmdate(TIMESTAMP, 'Ym');
	$logdir = PHPCOM_ROOT . '/data/log/';
	$logfile = $logdir . $yearmonth . '_' . $file . '.php';
	if (@filesize($logfile) > 2048000) {
		$dir = opendir($logdir);
		$length = strlen($file);
		$maxid = $id = 0;
		while ($entry = readdir($dir)) {
			if (strexists($entry, $yearmonth . '_' . $file)) {
				$id = intval(substr($entry, $length + 8, -4));
				$id > $maxid && $maxid = $id;
			}
		}
		closedir($dir);

		$logfilebak = $logdir . $yearmonth . '_' . $file . '_' . ($maxid + 1) . '.php';
		@rename($logfile, $logfilebak);
	}
	$fp = @fopen($logfile, 'a');
	if ($fp) {
		@flock($fp, 2);
		$log = is_array($log) ? $log : array($log);
		foreach ($log as $tmp) {
			fwrite($fp, "<?PHP exit;?>\t" . str_replace(array('<?', '?>'), '', $tmp) . "\n");
		}
		fclose($fp);
	}
}

function files_exists($files, $return = false) {
	if(is_array($files)){
		foreach ($files as $file){
			if(file_exists($file)){
				return $return ? $file : true;
			}
		}
	}else{
		if(file_exists($files)){
			return $return ? $files : true;
		}
	}
	return false;
}

function parser_tplname($name, $tplnames = null) {
	if(empty($tplnames)) return $name;
	$tpldir = PATH_TEMPLATE . '/' . trim(phpcom::$setting['templatedir'], '\\ . /') . '/';
	if(!is_array($tplnames)) $tplnames = array($tplnames);
	foreach ($tplnames as $tname){
		$tplname = is_numeric($tname) ? $name . "_$tname" : $tname;
		if(!empty($tname) && file_exists($tpldir . $tplname . '.htm')){
			return $tplname;
		}
	}
	return $name;
}

function checktplname($name, $tname = '', $dir = '') {
	if(empty($tname) && empty($dir)){
		return $name;
	}else{
		$tplname = is_numeric($tname) ? $name . "_$tname" : $tname;
		if ($tplname && tplfile_exists($tplname)) {
			return $tplname;
		} else {
			return $name;
		}
	}
}

function tplfile_exists($tplname) {
	$tpldir = phpcom::$setting['templatedir'];
	$filename = PATH_TEMPLATE . '/' . $tpldir . '/' . $tplname . '.htm';
	return file_exists($filename);
}

function template($name, $checkajax = 0) {
	$tpldir = phpcom::$setting['templatedir'];
	$htmfile = PATH_TEMPLATE . '/' . $tpldir . '/' . $name . '.htm';
	$tplname = strtr($name, '/', '_');
	$phpfile = PHPCOM_ROOT . '/data/template/tpl_' . $tplname . '.php';
	return checktplfile($htmfile, $phpfile);
}

function checktplfile($htmfile, $phpfile) {
	if(!file_exists($htmfile)){
 		die(sprintf("Sorry, The template file <b>%s/%s</b> does not exist.", 
 				basename(dirname($htmfile)), basename($htmfile)));
	}
	if (!file_exists($phpfile) || filemtime($htmfile) > @filemtime($phpfile)) {
		$tpl = new template();
		$tpl->parse_template($htmfile, $phpfile);
	}
	return $phpfile;
}

function lang($file, $langkey = null, $vars = '') {
	$path = '';
	if (strpos($file, '/')) {
		list($path, $file) = explode('/', $file);
	}

	$key = $path == '' ? $file : $path . '_' . $file;
	if (!isset(phpcom::$G['lang'][$key])) {
		if ($file == 'admincp') {
			$path = 'admin';
		}
		$filename = PHPCOM_PATH . '/lang/' . ($path == '' ? '' : $path . '/') . 'lang_' . $file . '.php';
		if (file_exists($filename)) {
			include $filename;
			phpcom::$G['lang'][$key] = $lang;
		} else {
			return $langkey;
		}
	}
	$languages = &phpcom::$G['lang'];
	$result = $langkey !== null ? (isset($languages[$key][$langkey]) ? $languages[$key][$langkey] : null) : $languages[$key];
	$result = $result === null ? $langkey : $result;
	if ($vars) {
		$searchs = $replaces = array();
		foreach ($vars as $k => $v) {
			$searchs[] = '{' . $k . '}';
			$replaces[] = $v;
		}
		$result = str_replace($searchs, $replaces, $result);
	}
	return $result;
}

function phpcom_exit($message = '') {
	echo $message;
	exit();
}

function handler_exit(){
	(ob_get_level() > 0) ? @ob_flush() : @flush();
	exit();
}

function debug($var = null) {
	echo '<pre>';
	if ($var === null) {
		print_r($GLOBALS);
	} else {
		print_r($var);
	}
	exit('</pre>');
}

function unserialized($string) {
	$string = stripslashes($string);
	$serialized = preg_replace_callback('#s:(\d+):"(.*?)";#', "reserialized", $string);
	return @unserialize($serialized);
}

function reserialized($matches){
	return 's:'.strlen($matches[2]).':"'.$matches[2].'";';
}

function http_get_contents($url, $timeout = 30.0) {
	$request = WebRequest::getInstance($url);
	$request->timeout = $timeout;
	$data = $request->getBody();
	$request->close();
	return $data;
}

function ftpcommand($cmd, $args = '') {
	static $ftp;
	$ftpon = phpcom::$setting['ftp']['on'];
	if (!$ftpon) {
		return $cmd == 'error' ? -101 : 0;
	} elseif ($ftp == null) {
		$ftp = &phpcom_ftp::instance();
	}
	if (!$ftp->enabled) {
		return 0;
	} elseif ($ftp->enabled && !$ftp->connectid) {
		$ftp->connect();
	}
	switch ($cmd) {
		case 'upload' : return $ftp->upload(phpcom::$setting['attachdir'] . './' . $args, $args); break;
		case 'delete' : return $ftp->ftp_delete($args); break;
		case 'close' : return $ftp->ftp_close(); break;
		case 'error' : return $ftp->error(); break;
		case 'object' : return $ftp; break;
		default : return FALSE;
	}
}

function encryptstring($string, $key = '', $expiry = 0) {
	$key = md5($key ? $key : phpcom::$config['security']['key']);
	$sufkey = substr($key, 16);
	$string = ($expiry ? $expiry + time() : 0) .'|'. substr(md5($string . $sufkey), 8, 16) .'|'. $string;
	return CryptUtils::encode($string, $key);
}

function decryptstring($string, $key = '') {
	if(empty($string)) return '';
	$key = md5($key ? $key : phpcom::$config['security']['key']);
	$sufkey = substr($key, 16);
	$decipher = CryptUtils::decode($string, $key);
	if(strpos($decipher, '|')){
		list($time, $auth, $string) = explode('|', $decipher . '|', 4);
		if (($time == 0 || $time - time() > 0) && $auth == substr(md5($string . $sufkey), 8, 16)) {
			return $string;
		} else {
			return '';
		}
	}else{
		return '';
	}
}

/**
 * 字节格式化
 * @param int $size 数值
 * @return string 返回格式化后的大小
 */
function formatbytes($size) {
	static $unit = array(' bytes', ' KB', ' MB', ' GB', ' TB', ' PB');
	$i = 0;
	while ($size > 1024 && ++$i < 6) {
		$size /= 1024;
	}
	$i && $size = round($size, 2);
	return $size . $unit[$i];
}

function formatsize($size){
	return formatbytes($size);
}

function sizetobytes($value) {
	$value = trim($value, " \t\n\r\0\x0B\"'Bb");
	$last = strtoupper(substr($value, -1));
	switch ($last) {
		case 'P': $value *= 1024;
		case 'T': $value *= 1024;
		case 'G': $value *= 1024;
		case 'M': $value *= 1024;
		case 'K': $value *= 1024;
	}
	return is_numeric($value) ? $value : intval($value);
}

function mkdirs($dir, $mode = 0777, $mkindex = true) {
	if ($dir && !is_dir($dir)) {
		if ($mkindex) {
			mkdirs(dirname($dir), $mode, $mkindex);
			@mkdir($dir, $mode);
			@touch($dir . '/index.htm');
			@chmod($dir . '/index.htm', 0777);
		}else{
			@mkdir($dir, $mode, true);
		}
	}
	return true;
}

/**
 * 得到随机字符串
 * @param int $length 随机字符串长度
 * @param bool $isnumeric TRUE 只返回数字，FALSE 返回字母和数字
 * @return string 返回一个随机字符串
 */
function random($length = 16, $isnumeric = FALSE, $tobase = 35) {
	return str_rand($length, $isnumeric, $tobase);
}

function str_rand($length = 16, $isnumeric = false, $tobase = 35)
{
	$seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $isnumeric ? 10 : $tobase);
	$seed = $isnumeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'ab' . strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for ($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}
	return $hash;
}

function rate_rand($weight = 1, $max = 500){
	if($max && $weight){
		$max = min(3000, max(30, $max));
		$num = mt_rand(30, $max);
		$min = $weight == 2 ? 4 : 3;
		$score = 0;
		for ($i = 0; $i < $num; $i++) {
			$score += mt_rand($min, 5) * 2;
		}
		$votedown = intval($num / 9);
		return array(
				'voter' => $num, 
				'total' => $score,
				'voteup' => $num - $votedown,
				'votedown' => $votedown
		);
	}
	return null;
}

/**
 * 检测字符串是否存在
 * @param string $haystack 原字符串
 * @param mixed $needle 要查找的字符串
 * @param int $offset 开始查找的位置
 * @return bool 如果存在返回 TRUE，否则返回 FALSE
 */
function strexists($haystack, $needle, $offset = 0) {
	return str_exists($haystack, $needle, $offset);
}

function str_exists($haystack, $needle, $offset = 0, $return = false) {
	if (is_array($needle)) {
		foreach ($needle as $string) {
			if (strpos($haystack, $string, $offset) !== false) return $return ? $string : true;
		}
		return false;
	}else {
		return !(strpos($haystack, $needle, $offset) === false);
	}
}
/**
 * Binary safe case-insensitive string comparison
 *
 * @param string $string
 * @param mixed $needle string|array
 * @return boolean
 */
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

function strpos_array($haystack, $needles, $offset = 0) {
	if (is_array($needles)) {
		foreach ($needles as $str) {
			if (is_array($str)) {
				$pos = strpos_array($haystack, $str, $offset);
			} else {
				$pos = strpos($haystack, $str, $offset);
			}
			if ($pos !== FALSE) {
				return $pos;
			}
		}
		return FALSE;
	} else {
		return strpos($haystack, $needles, $offset);
	}
}

function trimchars($string, $charlist = null) {
	if (is_array($string)) {
		foreach ($string as $key => $value) {
			$string[$key] = trimchars($value, $charlist);
		}
	}else{
		$string = $charlist === null ? trim($string) : trim($string, $charlist);
	}
	return $string;
}

function removeinvalidchars($string, $is_urlencode = true) {
	$patterns = array('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S');
	if($is_urlencode){
		$patterns[] = '/%0[0-8bcef]/';
		$patterns[] = '/%1[0-9a-f]/';
	}
	
	do{
		$string = preg_replace($patterns, '', $string, -1, $count);
	}while($count);
	
	return $string;
}

/**
 * 使用反斜线引用字符串
 * @param string $string 字符串
 * @return string 返回加反斜线字符串
 */
function addslashes_array($string) {
	if (is_array($string)) {
		foreach ($string as $key => $value) {
			unset($string[$key]);
			$string[addslashes($key)] = addslashes_array($value);
		}
	} else {
		$string = addslashes($string);
	}
	return $string;
}

/**
 * 反引用一个引用字符串
 * @param string $string 字符串
 * @return string 返回一个去除转义反斜线后的字符串
 */
function stripslashes_array($string) {
	if (is_array($string)) {
		foreach ($string as $key => $val) {
			$string[$key] = stripslashes_array($val);
		}
	} else {
		$string = stripslashes($string);
	}
	return $string;
}

/**
 * 检测是否有效的E-mail地址
 * @param string $email E-mail 地址
 * @return bool 返回布尔值 TRUE/FALSE
 */
function isemail($email) {
	return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
}

/**
 * 获取变量的布尔值
 * @param mixed $var
 * @param boolean $strict
 * @return boolean
 */
if(!function_exists('boolval')){
	function boolval($var, $strict = FALSE)
	{
		if (is_bool($var) || is_numeric($var)) {
			return (bool)$var;
		}elseif(is_string($var)){
			if($strict)
				return in_array(strtolower($var), array('true', 'yes', 'on', 'ok'));
			else
				return !in_array(strtolower($var), array('false', 'off', 'no', 'null', 'none', 'undefined'));
		}else{
			return !empty($var);
		}
	}
}

if(!function_exists('hex2bin')){
	function hex2bin($hex_string)
	{
		return pack("H*" , $hex_string);
	}
}
/**
 * 提问加密
 * @param int $questionid 提问ID
 * @param string $answer 回答
 * @return string 返回 MD5 加密后的字符串
 */
function questioncrypt($questionid, $answer) {
	return $questionid > 0 && $answer != '' ? substr(md5($answer . md5($questionid)), 16, 8) : '';
}

/**
 * 输出或返回一个变量的字符串
 * @param array $array 输入数组
 * @return string 返回一个变量的字符串
 */
function export_array($array) {
	if (!is_array($array)) {
		return "'" . $array . "'";
	}
	return var_export($array, true);
}

if(!function_exists('is_empty')){
	function is_empty($var){
		if(empty($var)) return true;
		if (is_array($var)) {
			foreach ($var as $value) {
				if(!is_empty($value)) {
					return false;
				}
			}
		}else{
			return trim($var) == '' ? true : false;
		}
		return true;
	}
}
function clearlogstring($str) {
	if (!empty($str)) {
		if (!is_array($str)) {
			$str = htmlcharsencode(trim($str));
			$str = str_replace(array("\t", "\r\n", "\n", "   ", "  "), ' ', $str);
		} else {
			foreach ($str as $key => $val) {
				$str[$key] = clearlogstring($val);
			}
		}
	}
	return $str;
}

/**
 * MD5加密函数
 * @param string $string 加密字符串
 * @param int $type 默认32位
 * @return string  返回加密后的字符串
 */
function md5string($string, $type = 32) {
	if (16 == $type) {
		return substr(md5($string), 8, 16);
	} else {
		return md5($string);
	}
}

function md5salt($string, $salt = '') {
	return md5(substr(md5($string), 8, 16) . $salt);
}

function crc32hex($string) {
	if(is_array($string)){
		return dechex(crc32(serialize($string)));
	}else{
		return dechex(crc32($string));
	}
}

function implodearray($array, $skip = array()) {
	$return = '';
	if (is_array($array) && !empty($array)) {
		foreach ($array as $key => $value) {
			if (empty($skip) || !in_array($key, $skip)) {
				if (is_array($value)) {
					$return .= "$key={" . implodearray($value, $skip) . "}; ";
				} else {
					$return .= "$key=$value; ";
				}
			}
		}
	}
	return $return;
}

function implodeids($array, $glue = "','", $limit = 0, &$data = array()) {
	if (!empty($array)) {
		$glue = $glue ? $glue : "','";
		$array = is_array($array) ? $array : explode(",", $array);
		$i = 0;
		foreach ($array as $val) {
			if (is_array($val)) {
				foreach($val as $v){
					if (is_numeric($v)){
						$i++;
						$data[] = intval($v);
						if($limit && $i >= $limit){
							break;
						}
					}
				}
			}elseif (is_numeric($val)){
				$i++;
				$data[] = intval($val);
				if($limit && $i >= $limit){
					break;
				}
			}
		}
		$data = array_unique($data);
		return $data ? "'" . implode($glue, $data) . "'" : '';
	} else {
		return '';
	}
}

function implodein($value, $strip = true){
	if(empty($value)) return null;
	$array = array_unique(is_array($value) ? $value : explode(",", $value));
	$data = array();
	foreach ($array as $val) {
		if($val = trim($val)){
			$data[] = addslashes($strip ? stripslashes($val) : $val);
		}
	}
	return $data ? "'" . implode("','", $data) . "'" : null;
}

function implodeurl($array, $first = '') {
	if (!empty($array)) {
		$data = array();
		foreach ($array as $key => $value) {
			if ($value !== '') {
				$data[] = "$key=$value";
			}
		}
		return $first . implode('&', $data);
	} else {
		return '';
	}
}

function implode_sql_value($array, $strip = true) {
	$key = $value = $comma = '';
	foreach ($array as $k => $v) {
		$key .= $comma . "`$k`";
		$v = addslashes($strip ? stripslashes($v) : $v);
		$value .= $comma . "'$v'";
		$comma = ',';
	}
	return "($key)VALUES($value)";
}

function implodevalue($value, $strip = true) {
	if (is_array($value)) {
		$data = array();
		foreach ($value as $v) {
			if ($v = trim($v)) {
				$data[] = addslashes($strip ? stripslashes($v) : $v);
			}
		}
		return "'" . implode("','", $data) . "'";
	} elseif (is_numeric($value)) {
		return "'$value'";
	} elseif ($value && is_string($value)) {
		return "'" . addslashes($strip ? stripslashes($value) : $value) . "'";
	}
	return '';
}

function buildlimit($rows, $offset = 0) {
	return ' LIMIT ' . ($offset <= 0 ? '' : (int) $offset . ',') . abs($rows);
}

function getstatus($status, $position) {
	$t = $status & pow(2, $position - 1) ? 1 : 0;
	return $t;
}

function setstatus($position, $value, $baseon = NULL) {
	$t = pow(2, $position - 1);
	if ($value) {
		$t = $baseon | $t;
	} elseif ($baseon !== NULL) {
		$t = $baseon & ~$t;
	} else {
		$t = ~$t;
	}
	return $t & 0xFFFF;
}

function checkurlhttp($url) {
	if(empty($url)) return '';
	if($url = trim(strip_tags($url))){
		if(parse_url($url, PHP_URL_SCHEME)){
			return $url;
		}
		return "http://$url";
	}
	return '';
}

/**
 * 把一些预定义的字符转换为 HTML 实体
 * @param string 参数
 * @return string 返回 string 值
 */
function htmlcharsencode($string, $flags = NULL) {
	if (is_array($string)) {
		foreach ($string as $key => $val) {
			$string[$key] = htmlcharsencode($val, $flags);
		}
	} else {
		if($flags === NULL) {
			$string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
			if (strpos($string, '&amp;#') !== FALSE) {
				$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
			}
		}else{
			if(version_compare(PHP_VERSION, '5.4.0', '<')){
				$string = htmlspecialchars($string, $flags);
			}else{
				$string = htmlspecialchars($string, $flags, strcasecmp(CHARSET, 'utf-8') ? 'ISO-8859-1' : 'UTF-8');
			}
		}
	}
	return $string;
}

/**
 * 从字符串中去除 HTML 和 PHP 标记
 * @param string $param string 类型参数
 * @return string 返回 $param 的 string 值
 */
function striptags($param) {
	if (is_array($param)) {
		foreach ($param as $key => $value) {
			$param[$key] = striptags($value);
		}
		return $param;
	} else {
		return trim(strip_tags($param));
	}
}

function striphtml($string)
{
	return htmlstrip($string);
}

function htmlstrip($html, $strip = true) {
	$html = trim(strip_tags($html));
	$html = $strip ? stripslashes($html) : $html;
	if (strcasecmp(CHARSET, 'utf-8') === 0) {
		$html = str_replace(array('&ldquo;', '&rdquo;'), array(chr(0xE2) . chr(0x80) . chr(0x9C), chr(0xE2) . chr(0x80) . chr(0x9D)), $html);
		$html = str_replace(array('&lsquo;', '&rsquo;'), array(chr(0xE2) . chr(0x80) . chr(0x98), chr(0xE2) . chr(0x80) . chr(0x99)), $html);
		$html = str_replace(array('&mdash;', '&hellip;'), array(chr(0xE2) . chr(0x80) . chr(0x94), chr(0xE2) . chr(0x80) . chr(0xA6)), $html);
		$html = str_replace(array('&middot;', '&uml;'), array(chr(0xC2) . chr(0xB7), chr(0xC2) . chr(0xA8)), $html);
		$html = str_replace(array('&ndash;'), array(chr(0xe2) . chr(0x80) . chr(0x93)), $html);
	}else{
		$html = str_replace(array('&ldquo;', '&rdquo;'), array(chr(0xA1) . chr(0xB0), chr(0xA1) . chr(0xB1)), $html);
		$html = str_replace(array('&lsquo;', '&rsquo;'), array(chr(0xA1) . chr(0xAE), chr(0xA1) . chr(0xAF)), $html);
		$html = str_replace(array('&mdash;', '&hellip;'), array(chr(0xA1) . chr(0xAA), chr(0xA1) . chr(0xAD)), $html);
		$html = str_replace(array('&middot;', '&uml;'), array(chr(0xA1) . chr(0xA4), chr(0xA1) . chr(0xA7)), $html);
		$html = str_replace(array('&ndash;'), array(chr(0xa8) . chr(0x43)), $html);
	}
	$html = str_replace(array("&nbsp;", '"', '`', "\r", "\n", "\t"), '', $html);
	$html = str_replace(array('<', '>', '%3C', '%3E', '%22', '%27', '%3c', '%3e'), '', $html);
	$html = str_replace('&amp;', '&', $html);
	$html = preg_replace("/\[attach\](\d+)\[\/attach\]/", '', $html);
	$html = preg_replace("/\[attachimg\](\d+)\[\/attachimg\]/", '', $html);
	$html = preg_replace("/\s+/", ' ', $html);
	$html = addslashes($html);
	return trim($html);
}

function stripstring($string, $strip = true) {
	$string = $strip ? stripslashes($string) : $string;
	$string = strip_tags($string);
	$string = str_replace(array('<', '>', '%3C', '%3E', '%22', '%27', '%3c', '%3e'), '', $string);
	$string = str_replace(array("'", '"', '--', '^', '=', '`', '$', '%', ';'), '', $string);
	$string = str_replace(array("(", ')', '[', ']', '!', '?', '*', "\r", "\n", "\t"), '', $string);
	$string = preg_replace("/\s+/", ' ', $string);
	$string = addslashes($string);
	return trim($string);
}

function stripempty($value) {
	if (is_array($value)) {
		$array = array();
		foreach ($value as $key => $val) {
			if (!empty($val)) {
				$array[$key] = trim($val);
			}
		}
		return $array;
	} else {
		return empty($value) ? NULL : trim($value);
	}
}

function stripchars($string, $chars = null) {
	$chars = empty($chars) ? null : preg_quote($chars, '/');
	return preg_replace("/[^0-9a-zA-Z_$chars]+/", '', $string);
}

function checkbanwords($string) {
	phpcom_cache::load('auditwords');
}

function checkinput(&$string, $params = array()) {
	$params = array_merge(array('strip' => 1, 'striptag' => 1, 'htmlencode' => 1, 'badword' => 1, 'checkword' => 1), $params);
	if (empty($string)) {
		return 0;
	}
	if ($params['strip']) {
		$string = stripslashes($string);
	}
	if ($params['striptag']) {
		$string = strip_tags($string);
	}
	if ($params['htmlencode']) {
		$string = htmlcharsencode($string);
	}
	if ($params['checkword']) {
		phpcom_cache::load('badwords');
		if (!empty(phpcom::$G['cache']['badwords']['pattern'])) {
			$patterns = phpcom::$G['cache']['badwords']['pattern'];
			foreach ($patterns as $pattern) {
				if (preg_match($pattern, $string)) {
					if ($params['checkword'] == 2) {
						return -1;
					} else {
						return showmessage('check_badword', NULL, NULL, array('showdialog' => TRUE));
					}
				}
			}
		}
	}
	if ($params['badword']) {
		phpcom_cache::load('badwords');
		if(!empty(phpcom::$G['cache']['badwords']['pattern'])){
			$pattern = phpcom::$G['cache']['badwords']['pattern'];
			$replace = phpcom::$G['cache']['badwords']['replace'];
			$string = preg_replace($pattern, $replace, $string);
		}
	}
	$string = str_replace('        ', "\t", $string);
	$string = addslashes($string);
	return $string;
}

function removetags($string, $length = 80) {
	$string = bbcode::output($string);
	$string = str_replace(array("\t", '&nbsp;'), '', $string);
	$string = trim(strip_tags($string));
	return cutstr($string, $length);
}

/**
 * 目录转换函数
 * @param string $dir string 类型参数
 * @return string 返回 $dir 的 string 值
 */
function checkdir($dir) {
	$dir = str_replace(array("'", '#', '=', '`', '$', '%', '&', ';'), '', $dir);
	return trim(preg_replace('/(\/){2,}|(\\\){1,}/', '/', $dir), '/');
}

function checksubmit($keys = '', $getmethod = FALSE, $checkcode = FALSE) {
	if (empty($keys)) {
		$keys = array('submit', 'btnsubmit', 'formsubmit');
	} else {
		!is_array($keys) && $keys = array($keys);
	}
	phpcom::$G['gp_formtoken'] = isset(phpcom::$G['gp_formtoken']) ? trim(phpcom::$G['gp_formtoken']) : '';
	foreach ($keys as $key) {
		if (isset(phpcom::$G["gp_$key"]) && phpcom::$G["gp_$key"]) {
			if ($getmethod || ($_SERVER['REQUEST_METHOD'] == 'POST' && phpcom::$G['gp_formtoken'] == formtoken() && empty($_SERVER['HTTP_X_FLASH_VERSION']) && (empty($_SERVER['HTTP_REFERER']) ||
					preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) == preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])))) {
				if ($checkcode && !check_captcha(phpcom::$G['gp_verifycode'])) {
					showmessage('captcha_verify_invalid', NULL, NULL, array('showdialog' => TRUE));
				}
				return TRUE;
			} else {
				showmessage('submit_invalid');
			}
		}
	}
	return FALSE;
}

function formtoken() {
	$hashadd = defined('IN_ADMINCP') ? 'PHPMain Management Center' : '';
	return substr(md5(substr(phpcom::$G['timestamp'], 0, -7) . phpcom::$G['username'] . phpcom::$G['uid'] . phpcom::$G['authkey'] . $hashadd), 8, 16);
}

function check_vercode($value) {
	if (!phpcom::$setting['checkcodestatus']) {
		return TRUE;
	}
}

function loadlibfile($name, $folder = 'lib') {
	$path = PHPCOM_PATH . '/' . $folder;
	$filename = "$path/$name.php";
	if(!file_exists($filename)){
		throw new phpcomException("File \"$filename\" does not exist.");
	}
	return realpath("$path/$name.php");
	if (strstr($folder, '/')) {
		$prefix = substr(strrchr($folder, '/'), 1);
		return realpath("{$path}/{$prefix}_{$name}.php");
	} else {
		if ($folder == 'lib' || $folder == 'inc' || $folder == 'class') {
			return realpath("{$path}/{$name}.{$folder}.php");
		} else {
			return realpath("{$path}/{$folder}_{$name}.php");
		}
	}
}

function getsubtable($tableid = 0, $tabletype = 'article', $prefix = FALSE) {
	static $table_types = array('article', 'soft', 'photo', 'special', 'video');
	$tableid = intval($tableid);
	$tabletype = in_array($tabletype, $table_types) ? $tabletype : 'article';
	if ($tableid) {
		phpcom_cache::load('subtableids');
		$tableid = phpcom::$G['cache']['subtableids'][$tabletype] && in_array($tableid, phpcom::$G['cache']['subtableids'][$tabletype]) ? $tableid : 0;
		$tablename = $tabletype . '_content' . ($tableid ? "_$tableid" : '');
	} else {
		$tablename = $tabletype . '_content';
	}
	if ($prefix) {
		$tablename = DB::table($tablename);
	}
	return $tablename;
}

function loaducenter() {
	if (!defined('UC_CONNECT_TYPE')) {
		require PHPCOM_ROOT . '/data/uc_config.php';
		require PHPCOM_ROOT . '/src/ucenter/client.php';
	}
}

function getuserinfo($uid) {
	static $users = array();
	if (empty($users[$uid])) {
		$users[$uid] = DB::fetch_first("SELECT d.*,u.* FROM " . DB::table('members') . " u
				LEFT JOIN " . DB::table('member_count') . " d USING(uid) WHERE u.uid='$uid'");
	}
	return $users[$uid];
}

function strtoint($value, $base = 0) {
	if(preg_match("#[^\d]+(\d+)#", $value, $matchs)){
		return intval($matchs[1]);
	}
	return intval($value, $base);
}

function urlrewriter($name, $args, $domain = '', $module = 'main'){
	return geturl($name, $args, $domain, $module);
}

function geturl($name, $args, $domain = '', $module = 'main'){
	if(isset($args['chanid']) && $args['chanid']){
		if(isset(phpcom::$G['channel'][$args['chanid']])){
			$module = phpcom::$G['channel'][$args['chanid']]['modules'];
			$args['chandir'] = phpcom::$G['channel'][$args['chanid']]['codename'];
			$args['module'] = phpcom::$G['channel'][$args['chanid']]['modules'];
		}
	}
	if(!isset(phpcom::$G['cache']['urlrules'][$module][$name]['rule'])){
		return false;
	}
	$rule = phpcom::$G['cache']['urlrules'][$module][$name]['rule'];
	if(isset(phpcom::$G['cache']['urlrules'][$module][$name]['match'])){
		foreach(phpcom::$G['cache']['urlrules'][$module][$name]['match'] as $key => $value){
			if(!isset($args[$key]) || (empty($args[$key]) && $value[0] != '{')){
				$rule = str_replace($value, '', $rule);
				$rule = str_replace('{'.$key.'}', '', $rule);
			}elseif(isset($args['date']) && $key == 'date'){
				$rule = str_replace('{date}', date($value, $args['date']), $rule);
			}elseif(isset($args['query']) && $key == 'query'){
				if(is_array($args['query'])){
					if($c = substr_count($value, '%')){
						for ($i = 0; $i < $c - count($args['query']); $i++){
							$value = substr($value, 0, strrpos($value, '-'));
						}
						$rule = str_replace('{query}', vsprintf($value, $args['query']), $rule);
					}else{
						$rule = str_replace('{query}', implode('-', $args['query']), $rule);
					}
				}else{
					$rule = str_replace('{query}', $args['query'], $rule);
				}
			}elseif(is_array($args[$key])){
				$rule = str_replace('{'.$key.'}', implode('-', $args[$key]), $rule);
			}else{
				$rule = str_replace('{'.$key.'}', $args[$key], $rule);
			}
		}
	}
	return $domain . str_replace('//', '/', $rule);
}

function checkipaccess($ip, $iplist) {
	return preg_match("/^(" . str_replace(array("\r\n", ' '), array('|', ''), preg_quote($iplist, '/')) . ")/", $ip);
}

function checkipbanned($ip) {
	if (isset(phpcom::$G['allowipaccess']) && phpcom::$G['allowipaccess'] && !checkipaccess($ip, phpcom::$G['allowipaccess'])) {
		return TRUE;
	}
	phpcom_cache::load('banip');
	if (empty(phpcom::$G['cache']['banip'])) {
		return FALSE;
	} else {
		if (phpcom::$G['cache']['banip']['expiration'] < TIMESTAMP) {
			phpcom_cache::updater('banip');
		}
		return preg_match("/^(" . phpcom::$G['cache']['banip']['regexp'] . ")$/", $ip);
	}
}

function check_allowipaccess($ip = '') {
	$allowip = trim(phpcom::$setting['allowipaccess']);
	if (empty($allowip)) {
		return -1;
	}
	$ip = $ip ? $ip : phpcom::$G['clientip'];
	$allowip = str_replace("\r", '', $allowip);
	$allowexp = '/^(' . str_replace("\n", '|', preg_quote($allowip, '/')) . ')$/i';
	$allowexp = str_replace('\*', '\w+', $allowexp);
	return preg_match($allowexp, $ip);
}

function check_adminipaccess($ip = '') {
	$allowip = trim(phpcom::$setting['adminipaccess']);
	if (empty($allowip)) {
		return -1;
	}
	$ip = $ip ? $ip : phpcom::$G['clientip'];
	$allowip = str_replace("\r", '', $allowip);
	$allowexp = '/^(' . str_replace("\n", '|', preg_quote($allowip, '/')) . ')$/i';
	$allowexp = str_replace('\*', '\w+', $allowexp);
	return preg_match($allowexp, $ip);
}

function get_clientip() {
	$clientip = '';
	switch (true) {
		case !empty($_SERVER['HTTP_CLIENT_IP']):
			$clientip = htmlspecialchars((string)$_SERVER['HTTP_CLIENT_IP']);
			if($clientip = check_clientip($clientip)){
				break;
			}
		case !empty($_SERVER['HTTP_X_REAL_IP']):
			$clientip = htmlspecialchars((string)$_SERVER['HTTP_X_REAL_IP']);
			if($clientip = check_clientip($clientip)){
				break;
			}
		case !empty($_SERVER['HTTP_X_FORWARDED_FOR']):
			$clientip = htmlspecialchars((string)$_SERVER['HTTP_X_FORWARDED_FOR']);
			if($clientip = check_clientip($clientip)){
				break;
			}
		case !empty($_SERVER['REMOTE_ADDR']):
			$clientip = htmlspecialchars((string) $_SERVER['REMOTE_ADDR']);
			$clientip = check_clientip($clientip);
			break;
	}
	return $clientip ? $clientip : 'unknown';
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
		return null;
	}
}

function processedtime() {
	$timer = number_format((microtime(true) - phpcom::$G['starttime']), 6);
	$queries = DB::instance()->querycount;
	$gzipcompress = '';
	if (phpcom::$G['gzipcompress']) {
		$gzipcompress = ', Gzip enabled';
	}
	$appmemory = '';
	if (phpcom::$G['memory']) {
		$appmemory = ', ' . ucwords(phpcom::$G['memory']) . ' On';
	}

	$memoryusage = ', memory ' . formatbytes(memory_get_usage());
	return "Processed in $timer(s), $queries queries$gzipcompress$appmemory$memoryusage";
}

function getcount($tablename, $condition) {
	if (empty($condition)) {
		$where = '1';
	} elseif (is_array($condition)) {
		$where = DB::implode_field_value($condition, ' AND ');
	} else {
		$where = $condition;
	}
	$ret = intval(DB::result_first("SELECT COUNT(*) AS num FROM " . DB::table($tablename) . " WHERE $where"));
	return $ret;
}

function check_captcha($value) {
	if (!phpcom::$setting['captchastatus']) {
		return TRUE;
	}
	if (!isset(phpcom::$G['cookie']['captcha'])) {
		return FALSE;
	}
	list($code, $time) = explode("\t", decryptstring(phpcom::$G['cookie']['captcha']));
	return $code == strtoupper($value) && TIMESTAMP - 180 > $time;
}

function check_questionset($value) {
	if (!isset(phpcom::$setting['questionstatus'])) {
		return TRUE;
	}
	if (!isset(phpcom::$G['cookie']['questionset'])) {
		return FALSE;
	}
	list($code, $time) = explode("\t", decryptstring(phpcom::$G['cookie']['questionset']));
	return $code == md5($value) && TIMESTAMP - 180 > $time;
}

function getreferer($url = '') {
	phpcom::$G['referer'] = !empty(phpcom::$G['gp_referer']) ? phpcom::$G['gp_referer'] : $_SERVER['HTTP_REFERER'];
	phpcom::$G['referer'] = substr(phpcom::$G['referer'], -1) == '?' ? substr(phpcom::$G['referer'], 0, -1) : phpcom::$G['referer'];
	if (strpos(phpcom::$G['referer'], 'member.php?action=login') || strpos(phpcom::$G['referer'], 'login.html')) {
		phpcom::$G['referer'] = $url;
	}
	if (strpos(phpcom::$G['referer'], 'member.php?action=register') || strpos(phpcom::$G['referer'], 'register.html')) {
		phpcom::$G['referer'] = $url;
	}
	phpcom::$G['referer'] = htmlcharsencode(phpcom::$G['referer']);
	phpcom::$G['referer'] = str_replace('&amp;', '&', phpcom::$G['referer']);
	phpcom::$G['referer'] = phpcom::$G['referer'] ? phpcom::$G['referer'] : '/';
	return strip_tags(phpcom::$G['referer']);
}

function getusergroups($type = '') {
	static $usergroups = NULL;
	if ($usergroups === NULL) {
		foreach (phpcom::$G['usergroup'] as $data) {
			if ($data['type'] == 'member') {
				$usergroups['member'][$data['groupid']] = $data;
			} elseif ($data['type'] == 'special') {
				$usergroups['special'][$data['groupid']] = $data;
			} else {
				$usergroups['system'][$data['groupid']] = $data;
			}
		}
	}
	if (str_exists($type, array('member', 'special', 'system'))) {
		return $usergroups[$type];
	}
	return $usergroups;
}

function admin_addnotify($type, $fromnum = 0, $langvar = array()) {
	return $type . $fromnum . serialize($langvar);
}

function addnotification($uid, $type, $message, $notevars = array(), $system = 0) {
	if (!is_numeric($type)) {
		$vars = explode(':', $message);
		if (count($vars) == 2) {
			$notecontent = lang('plugin/' . $vars[0], $vars[1], $notevars);
		} else {
			$notecontent = lang('notification', $message, $notevars);
		}
	} else {
		$notecontent = $message;
	}
	$noteold = array();
	if ($notevars['fromid']) {
		$noteold = DB::fetch_first("SELECT * FROM " . DB::table('notification') . " WHERE fromid='{$notevars['fromid']}' AND uid='$uid'");
	}
	if (empty($noteold['fromnum'])) $noteold['fromnum'] = 0;
	$notevars['fromnum'] = $notevars['fromnum'] ? $notevars['fromnum'] : 1;
	$data = array(
			'uid' => $uid, 'flag' => 1, 'notetype' => $type,
			'authorid' => phpcom::$G['uid'],
			'author' => phpcom::$G['username'],
			'message' => $notecontent,
			'dateline' => TIMESTAMP,
			'fromid' => intval($notevars['fromid']),
			'fromnum' => intval($noteold['fromnum'] + $notevars['fromnum'])
	);
	if ($system) {
		$data['authorid'] = 0;
		$data['author'] = '';
	}
	if ($noteold['noteid']) {
		DB::update('notification', $data, array('noteid' => $noteold['noteid']));
	} else {
		$noteold['flag'] = 0;
		DB::insert('notification', $data);
	}
	if (empty($noteold['flag'])) {
		DB::query("UPDATE " . DB::table('members') . " SET prompts=prompts+1 WHERE uid='$uid'");
	}
	if (!$system && phpcom::$G['uid'] && $uid != phpcom::$G['uid']) {
		$fuid = $uid;
		$uid = phpcom::$G['uid'];
		DB::query("UPDATE " . DB::table('friends') . " SET num=num+1 WHERE uid='$uid' AND fuid='$fuid'");
	}
}

function update_membercount($uids, $arrdata = array(), $checkgroup = TRUE, $operation = '', $relateid = 0) {
	if (!empty($uids) && (is_array($arrdata) && $arrdata)) {
		require_once loadlibfile('credit');
		credit_updatemembercount($uids, $arrdata, $checkgroup, $operation, $relateid);
	}
	return TRUE;
}

function update_creditbyaction($action, $uid = 0, $extrasql = array(), $coef = 1, $update = 1, $fid = 0) {

	$credit = & credit::instance();
	if ($extrasql) {
		$credit->extrasql = $extrasql;
	}
	return $credit->executerule($action, $uid, $coef, $update, $fid);
}

function update_memberlastpost($field = '') {
	$uid = phpcom::$G['uid'];
	if (!$uid) return;
	if ($field) {
		$field = "$field=$field+'1'";
		DB::query("UPDATE " . DB::table('member_count') . " SET $field WHERE uid='$uid'");
	}
	DB::update('member_status', array('lastpost' => TIMESTAMP), "uid='$uid'");
}

function generatethumbname($filename, $extension = '_thumb.jpg', $retainext = FALSE) {
	if (empty($filename)) {
		return '';
	}
	if (!$retainext) {
		$filename = substr($filename, 0, strrpos($filename, '.'));
	}
	$extension = strstr($extension, '.') ? $extension : '.' . $extension;
	return $filename . $extension;
}

function getextension($filename) {
	return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
}

function getattachimgurl($aid, $w = 135, $h = 135, $nocache = 0, $type = 'geom', $thumb = 0, $chanid = 0){
	$key = md5($aid.'|'.$w.'|'.$h);
	return "apps/misc.php?action=image&chanid=$chanid&aid=$aid&size={$w}x$h&key=".rawurlencode($key).($nocache ? '&nocache=yes' : '').($type ? '&type='.$type : '').($thumb ? '&thumb=yes' : '');
}

function getattachment($tid){
	$attachs = $images = array();
	if($tid = intval($tid)){
		$query = DB::query("SELECT * FROM " . DB::table('attachment') . " WHERE tid='$tid'");
		while ($attach = DB::fetch_array($query)) {
			$attach['key'] = md5($attach['attachid'] . substr(md5(phpcom::$config['security']['key']), 8) . $attach['uid']);
			if($attach['image']){
				$images[] = $attach;
			}else{
				$attachs[] = $attach;
			}
		}
	}
	return array('attach' => $attachs, 'image' => $images);
}

function threadimageurl($tid, $type = 0, $modules = 'article', &$image = array()){
	if(!$tid = intval($tid)){
		return '';
	}
	$url = '';
	if(empty($image)){
		$image = DB::fetch_first("SELECT tid, attachment, thumb, preview, remote FROM " . DB::table('thread_image') . " WHERE tid='$tid'");
	}
	if($image){
		$parse = parse_url(phpcom::$setting['attachurl']);
		$attachurl = !isset($parse['host']) ? phpcom::$G['siteurl'] . phpcom::$setting['attachurl'] : phpcom::$setting['attachurl'];
		if($image['remote']){
			$attachurl = phpcom::$setting['ftp']['attachurl'] . $modules . '/';
		}else{
			$attachurl = $attachurl . $modules . '/';
		}
		if($type == 1 && $image['thumb']){
			$url = $attachurl . generatethumbname($image['attachment']);
		}elseif($type == 2 && $image['preview']){
			$url = $attachurl . generatethumbname($image['attachment'], '_small.jpg');
		}else{
			$url = $attachurl . $image['attachment'];
		}
	}
	return $url;
}

function checkgrouplevel($level){
	if(!$level){
		return TRUE;
	}
	$groupid = phpcom::$G['groupid'];
	if(in_array($groupid, array(4, 5, 6, 7))){
		return FALSE;
	}
	if(phpcom::$G['usergroup'][$level]['type'] == 'member'){
		return TRUE;
	}
	if($groupid <= 3){
		return $groupid <= $level;
	}
	return $groupid == $level;
}

function checkUploadPermis($uid = 0){
	if(phpcom::$G['uid'] && phpcom::$setting['uploadstatus']){
	}
	return -1;
}

function httpurl_encode($data) {
	if (!is_string($data)) return null;
	return bin2hex($data);
}

function httpurl_decode($data) {
	if($data = trim($data)){
		if(preg_match('/[^0-9a-fA-F]/', $data)){
			return null;
		}
		if($string = @hex2bin($data)){
			$string = str_replace(array('"', "'"), '', $string);
			return addslashes($string);
		}
	}
	return null;
}

function output_js_document($html){
	$html = preg_replace_callback("/<script([^>]*)?>(.*?)<\/script>/is", "parser_jscript", $html);
	$content = '';
	foreach (explode("\0", $html) as $value) {
		if($value && strpos($value, '__NL__') === 0){
			$content .= str_replace('__NL__', "\n", $value);
		}elseif(trim($value) !== ''){
			$value = trim(str_replace(array("'", "\r", "\n", "/"), array("\'", "", "", "\\/"), $value));
			$content .= "document.writeln('$value');\n";
		}
	}
	return $content;
}

function parser_jscript($matches){
	if(preg_match("#src\s*=#", $matches[1])){
		$attr = trim(str_replace(array("\r", "\n"), '', $matches[1]));
		return "\0__NL__document.writeln('<s'+'cript $attr><\/s'+'cript>');\n\0";
	}
	return "\0__NL__".trim(str_replace(array("\r", "\n"), array("\r", "\n"), $matches[2]))."\n\0";
}

function convert_encoding($string, $incharset, $outcharset = null){
	$outcharset = empty($outcharset) ? CHARSET : $outcharset;
	if(strcasecmp($incharset, $outcharset) == 0 || empty($string)){
		return $string;
	}
	
	if(is_array($string)){
		foreach ($string as $key => $value) {
			unset($string[$key]);
			$string[addslashes($key)] = convert_encoding($value, $incharset, $outcharset);
		}
	}else{
		if (function_exists('iconv') && ($outstring = @iconv("$incharset", "$outcharset//TRANSLIT//IGNORE", $string))) {
			$string = $outstring;
		}elseif (function_exists('mb_convert_encoding') && ($outstring = @mb_convert_encoding($string, $outcharset, $incharset))) {
			$string = $outstring;
		}
	}
	return $string;
}

function fwrite_content($filename, $content) {
	if (!is_dir(dirname($filename))) {
		@mkdir(dirname($filename), 0777, true);
	}
	if ($fp = @fopen($filename, 'w')) {
		if (strcasecmp(CHARSET, 'utf-8') === 0 && substr($content, 0, 3) != "\xEF\xBB\xBF") {
			$content = "\xEF\xBB\xBF" . $content;
		}
		@fwrite($fp, $content);
		fclose($fp);
	}
}

function file_serialize($filename, $data = ''){
	clearstatcache();
	if($fp = @fopen($filename, 'ab+')){
		flock($fp, LOCK_EX);
		fseek($fp, 0);
		ftruncate($fp, 0);
		@fwrite($fp, "<?PHP\nreturn ");
		if(is_array($data)) $data = var_export($data, true);
		@fwrite($fp, $data . ";\n?>");
		fclose($fp);
	}
	return true;
}

function file_unserialize($filename){
	if(file_exists($filename)){
		return @include $filename;
	}
	return false;
}

function hexrgbcolor($color, $defvalue = null) {
	if(is_array($color)) $color = implode(', ', $color);
	$color = trim($color, "\t\r\n ;rgbRGB()'\"\$");
	if(preg_match("/#[0-9a-fA-F]{6}/", $color)){
		$color = substr($color, 0, 7);
		if($defvalue === false){
			sscanf($color, '#%2x%2x%2x', $r, $g, $b);
			return "rgb($r, $g, $b)";
		}
		return $color;
	}
	if(preg_match("#(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\s*,\s*){2}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#", $color, $matchs)){
		return "rgb($matchs[0])";
	}
	return $defvalue;
}

function rgb2array($color, $defvalue = array(255, 255, 255)) {
	$color = trim($color, "\t\r\n ;rgbRGB()'\"\$");
	if(preg_match('/#[0-9a-fA-F]{6}$/', $color)){
		return sscanf($color, '#%2x%2x%2x');
	}
	if(preg_match("#(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\s*,\s*){2}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#", $color, $matchs)){
		return explode(',', $matchs[0]);
	}
	return $defvalue;
}

function threadhighlight($highlight){
	$return = '';
	if ($highlight) {
		$string = sprintf('%02d', $highlight);
		$return = ' style="' . ($string[0] ? phpcom::$setting['fontvalue'][$string[0]] : '');
		$return .= $string[1] ? 'color: ' . phpcom::$setting['colorvalue'][$string[1]] : '';
		$return .= '"';
	}
	return $return;
}
?>