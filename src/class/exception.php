<?php
/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : exception.php    2011-7-5 19:47:15
 */
class phpcomException extends Exception {

	function __construct($message = null, $code = 0, $previous = null) {
		$this->show_error($message, $code);
		exit();
	}

	function show_error($message, $code = 0, $save = true) {
		if ($code == 1) {
			$message = lang('error', $message);
		}

		list($showtrace, $logtrace) = $this->debug_backtrace();

		if ($save) {
			$messagesave = '<b>' . $message . '</b><br><b>PHP:</b>' . $logtrace;
			$this->write_error_log($messagesave);
		}
		$this->error_content($message, $showtrace);
	}

	function debug_backtrace() {
		$skipfunc[] = 'phpcom_exception->error_content';
		$skipfunc[] = 'phpcom_exception->debug_backtrace';
		$skipfunc[] = 'phpcom_exception->phpcom_error';
		$skipfunc[] = 'db_exception->db_error';
		$skipfunc[] = 'db_mysqli->halt';
		$skipfunc[] = 'db_mysqli->query';
		$skipfunc[] = 'db_mysql->halt';
		$skipfunc[] = 'db_mysql->query';
		$skipfunc[] = 'DB::execute';

		$show = $log = '';
		$debug_backtrace = debug_backtrace();
		krsort($debug_backtrace);
		foreach ($debug_backtrace as $k => $error) {
			if (empty($error['file']))
				continue;
			$file = str_replace(PHPCOM_ROOT, '', $error['file']);
			$func = isset($error['class']) ? $error['class'] : '';
			$func .= isset($error['type']) ? $error['type'] : '';
			$func .= isset($error['function']) ? $error['function'] : '';
			if (in_array($func, $skipfunc)) {
				break;
			}
			$error['line'] = sprintf('%04d', intval($error['line']));

			$show .= '<li>[Line: ' . $error['line'] . ']' . $file . "($func)</li>";
			$log .= ! empty($log) ? ' -> ' : '';
			$file . ':' . $error['line'];
			$log .= $file . ':' . $error['line'];
		}
		return array($show, $log);
	}

	function error_content($message='', $phpmsg='') {
		ob_end_clean();
		$gzip = phpcom::$G['gzipcompress'];
		ob_start($gzip ? 'ob_gzhandler' : null);

		$host = $_SERVER['HTTP_HOST'];
		$charset = phpcom::$config['output']['charset'];
		$phpversion = PHP_VERSION;
		$serversoft = $_SERVER['SERVER_SOFTWARE'];
		$messagenoted = lang('error', 'message_noted');
		$backtracenoted = lang('error', 'backtrace_noted');
		$requirenoted = lang('error', 'require_noted');
		$errortitle = lang('error', 'error_title');
		$versioninfo = lang('error', 'sys_version_info', array('serversoft' => $serversoft, 'phpversion' => $phpversion));

		if (!empty($phpmsg)) {
			$phpmsg = "<b> $backtracenoted </b><ul>" . trim($phpmsg) . '</ul>';
		}
		if (!phpcom::$config['debug']) {
			$phpmsg = '';
		}
		echo <<<EOT
<html>
    <head>
        <title>$host - Application Errors</title>
		<meta http-equiv="Content-Type" content="text/html; charset={$charset}" />
		<meta name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE" />
        <style>
         body {font-family:"Verdana";font-weight:normal;font-size: .7em;color:black;}
         p {font-family:"Verdana";font-weight:normal;color:black;margin-top: -5px}
         b {font-family:"Verdana";font-weight:bold;color:black;margin-top: -5px}
         H1 { font-family:"Verdana";font-weight:normal;font-size:18pt;color:red }
         H2 { font-family:"Verdana";font-weight:normal;font-size:14pt;color:maroon }
         pre {font-family:"Lucida Console";font-size: .9em}
         .marker {font-weight: bold; color: black;text-decoration: none;}
         .version {color: gray;}
         .error {margin-bottom: 10px;}
         .expandable { text-decoration:underline; font-weight:bold; color:navy; cursor:hand; }
        </style>
    </head>
    <body bgcolor="white">
            <span><H1>$errortitle<hr width="100%" size="1" color="silver"></H1>
            <h2> <i>phpcom application server errors</i> </h2></span>
            <font face="Arial, Helvetica, Geneva, SunSans-Regular, sans-serif ">
            <b> $messagenoted </b>
			$message
            <br><br>
            <b> $requirenoted </b>{$_SERVER['SCRIPT_NAME']}<br><br>
			$phpmsg
			The application has encountered a problem. <a href="http://www.phpcom.cn" target="_blank"><span class="red">Need Help?</span></a>
            <hr width="100%" size="1" color="silver">
            $versioninfo
            </font>
    </body>
</html>
EOT;
	}

	function clear($message) {
		return str_replace(array("\t", "\r", "\n"), " ", $message);
	}

	function write_error_log($message) {

		$message = $this->clear($message);
		$time = time();
		$file = PHPCOM_ROOT . './data/log/' . date("Ym") . '_errorlog.php';
		$hash = md5($message);

		$ip = phpcom::$G['clientip'];

		$user = 'IP:' . $ip . '; RIP:' . $_SERVER['REMOTE_ADDR'];
		$uri = 'Request: ' . htmlspecialchars($this->clear($_SERVER['REQUEST_URI']));
		$message = "<?PHP exit;?>\t{$time}\t$message\t$hash\t$user $uri\n";
		$fp = @fopen($file, 'rb');
		if ($fp) {
			$lastlen = 10000;
			$maxtime = 60 * 10;
			$offset = filesize($file) - $lastlen;
			if ($offset > 0) {
				fseek($fp, $offset);
			}
			$data = fread($fp, $lastlen);
			if ($data) {
				$array = explode("\n", $data);
				if (is_array($array))
					foreach ($array as $key => $val) {
						$row = explode("\t", $val);
						if ($row[0] != '<?PHP exit;?>')
							continue;
						if ($row[3] == $hash && ($row[1] > $time - $maxtime)) {
							return;
						}
					}
			}
		}
		error_log($message, 3, $file);
	}

}

class dbException extends phpcomException {

	function __construct($message = null, $sql = null, $code = 0) {
		$this->db_error($message, $sql);
		exit();
	}

	function db_error($message = null, $sql = null) {
		list($showtrace, $logtrace) = parent::debug_backtrace();
		$title = lang('error', $message);
		$db = &DB::instance();
		$dberrno = $db->errno();
		$dberror = str_replace($db->tablepre, '', $db->error());
		$sql = htmlspecialchars(str_replace($db->tablepre, '', $sql));

		$msg = '<li>[Type] ' . $title . '</li>';
		$msg .= $dberrno ? '<li>[' . $dberrno . '] ' . $dberror . '</li>' : '';
		$msg .= ( $sql && phpcom::$config['debug']) ? '<li>[Query] ' . $sql . '</li>' : '';

		parent::error_content($msg, $showtrace);
		unset($msg, $phperror);

		$errormsg = '<b>' . $title . '</b>';
		$errormsg .= "[$dberrno]<br /><b>ERR:</b> $dberror<br />";
		if ($sql) {
			$errormsg .= '<b>SQL:</b> ' . $sql;
		}
		$errormsg .= "<br />";
		$errormsg .= '<b>PHP:</b> ' . $logtrace;

		parent::write_error_log($errormsg);
	}

}
?>
