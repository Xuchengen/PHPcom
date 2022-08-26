<?php
/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : ftp.php    2011-7-5 23:16:00
 */
!defined('IN_PHPCOM') && exit('Access denied');
if (!defined('FTP_ERR_SERVER_DISABLED')) {
	define('FTP_ERR_SERVER_DISABLED', -100);
	define('FTP_ERR_CONFIG_OFF', -101);
	define('FTP_ERR_CONNECT_TO_SERVER', -102);
	define('FTP_ERR_USER_NO_LOGGIN', -103);
	define('FTP_ERR_CHDIR', -104);
	define('FTP_ERR_MKDIR', -105);
	define('FTP_ERR_SOURCE_READ', -106);
	define('FTP_ERR_TARGET_WRITE', -107);
}

class phpcom_ftp {

	public $enabled = false;
	public $connectid;
	private $config = array();
	private $func;
	private $errcode = 0;
	private $attachdir = '.';

	/**
	 * 实例化 FTP 类
	 * @return phpcom_ftp 返回 FTP 类实例化对象
	 */
	public static function &instance() {
		static $_instance;
		if (empty($_instance)) {
			$_instance = new phpcom_ftp();
		}
		return $_instance;
	}

	/**
	 * FTP 类构造函数
	 */
	public function __construct() {
		$this->init();
	}
	
	public function setAttachDir($dir) {
		$this->attachdir = trim($dir);
	}
	
	/**
	 * 初始化 FTP 服务器设置
	 * @param array $config FTP 设置
	 */
	public function init($config = array()) {
		$this->set_error(0);
		if ($config) {
			$this->config = $config;
		} else {
			$this->config = phpcom::$setting['ftp'];
		}
		$this->enabled = FALSE;
		if (empty($this->config['on']) || empty($this->config['host'])) {
			$this->set_error(FTP_ERR_CONFIG_OFF);
		} else {
			$this->func = $this->config['ftpssl'] && function_exists('ftp_ssl_connect') ? 'ftp_ssl_connect' : 'ftp_connect';
			if ($this->func == 'ftp_connect' && !function_exists('ftp_connect')) {
				$this->set_error(FTP_ERR_SERVER_DISABLED);
			} else {
				$this->config['host'] = phpcom_ftp::clear($this->config['host']);
				$this->config['port'] = intval($this->config['port']);
				$this->config['ssl'] = intval($this->config['ssl']);
				$this->config['username'] = phpcom_ftp::clear($this->config['username']);
				$this->config['password'] = decryptstring($this->config['password'], md5(phpcom::$config['security']['key']));
				$this->config['timeout'] = intval($this->config['timeout']);
				$this->enabled = TRUE;
			}
		}
	}

	/**
	 * 上传一个文件到 FTP 服务器
	 * @param string $localfile 要上传的文件名
	 * @param string $destination 上传到 FTP 服务器的目标文件
	 * @return bool 如果成功返回 TRUE，失败则返回 FALSE
	 */
	public function upload($localfile, $destination) {
		if ($this->error()) {
			return 0;
		}
		$old_dir = $this->ftp_pwd();
		$dirname = dirname($destination);
		$filename = basename($destination);
		if (!$this->ftp_chdir($dirname)) {
			if ($this->ftp_mkdir($dirname)) {
				$this->ftp_chmod($dirname);
				if (!$this->ftp_chdir($dirname)) {
					$this->set_error(FTP_ERR_CHDIR);
				}
				$this->ftp_put('index.htm', phpcom::$setting['attachdir'] . '/index.htm', FTP_BINARY);
			} else {
				$this->set_error(FTP_ERR_MKDIR);
			}
		}

		$res = 0;
		if (!$this->error()) {
			if ($fp = @fopen($localfile, 'rb')) {
				$res = $this->ftp_fput($filename, $fp, FTP_BINARY);
				@fclose($fp);
				!$res && $this->set_error(FTP_ERR_TARGET_WRITE);
			} else {
				$this->set_error(FTP_ERR_SOURCE_READ);
			}
		}

		$this->ftp_chdir($old_dir);

		return $res ? 1 : 0;
	}

	/**
	 * 连接 FTP 服务器
	 * @return resource 如果成功返回一个连接标识，失败则返回 FALSE
	 */
	public function connect() {
		if (!$this->enabled || empty($this->config)) {
			return 0;
		} else {
			return $this->ftp_connect(
					$this->config['host'], $this->config['username'], $this->config['password'], $this->config['attachdir'], $this->config['port'], $this->config['timeout'], $this->config['pasv']
			);
		}
	}

	/**
	 * 建立一个新的 FTP 连接
	 * @param string $ftphost 要连接的服务器
	 * @param string $username FTP 用户名
	 * @param string $password FTP 用户密码
	 * @param string $ftpdir FTP 目标目录
	 * @param int $ftpport FTP 服务器端口号
	 * @param int $timeout 网络传输的超时时间限制
	 * @param bool $ftppasv 如果参数为 TRUE，打开被动模式传输
	 * @return resource 如果成功返回一个连接标识，失败则返回 FALSE
	 */
	public function ftp_connect($ftphost, $username, $password, $ftpdir, $ftpport = 21, $timeout = 30, $ftppasv = 0) {
		$res = 0;
		$fun = $this->func;
		if ($this->connectid = $fun($ftphost, $ftpport, 20)) {

			$timeout && $this->set_option(FTP_TIMEOUT_SEC, $timeout);
			if ($this->ftp_login($username, $password)) {
				$this->ftp_pasv($ftppasv);
				if ($this->ftp_chdir($ftpdir)) {
					$res = $this->connectid;
				} else {
					$this->set_error(FTP_ERR_CHDIR);
				}
			} else {
				$this->set_error(FTP_ERR_USER_NO_LOGGIN);
			}
		} else {
			$this->set_error(FTP_ERR_CONNECT_TO_SERVER);
		}

		if ($res > 0) {
			$this->set_error();
			$this->enabled = 1;
		} else {
			$this->enabled = 0;
			$this->ftp_close();
		}

		return $res;
	}

	/**
	 * 设置错误代码
	 * @param int $code 错误代码
	 */
	public function set_error($code = 0) {
		$this->errcode = $code;
	}

	/**
	 * 错误代码
	 * @return int 返回一个错误代码
	 */
	public function error() {
		return $this->errcode;
	}

	/**
	 * 清除特殊字符
	 * @param string $str 要清理的字符
	 * @return string 返回清理后的字符
	 */
	public static function clear($str) {
		return str_replace(array("\n", "\r", '..'), '', $str);
	}

	/**
	 * 设置各种 FTP 运行时选项
	 * @param int $option 选项. 默认=FTP_AUTOSEEK；改变网络传输的超时时间=FTP_TIMEOUT_SEC
	 * @param mixed $value 网络传输超时时间
	 * @return bool 如果选项能够被设置，返回 TRUE，否则返回 FALSE
	 */
	public function set_option($option, $value) {
		if (function_exists('ftp_set_option')) {
			return @ftp_set_option($this->connectid, $option, $value);
		}
	}

	/**
	 * 在 FTP 服务器上建立一个目录
	 * @param string $directory 新建的目录
	 * @return bool 如果成功返回新建的目录名，否则返回 FALSE
	 */
	public function ftp_mkdir($directory) {
		$return = $directory = phpcom_ftp::clear($directory);
		$epath = explode('/', $directory);
		$dir = $comma = '';
		foreach ($epath as $path) {
			$dir .= $comma . $path;
			$comma = '/';
			$return = @ftp_mkdir($this->connectid, $dir);
			$this->ftp_chmod($dir);
		}
		return $return;
	}

	/**
	 * 删除 FTP 服务器上的一个目录
	 * @param string $directory 要删除的目录，必须是一个空目录的绝对路径或相对路径
	 * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE
	 */
	public function ftp_rmdir($directory) {
		$directory = phpcom_ftp::clear($directory);
		return @ftp_rmdir($this->connectid, $directory);
	}

	/**
	 * 上传文件到 FTP 服务器
	 * @param string $remote_file 远程文件路径
	 * @param string $local_file 本地文件路径
	 * @param int $mode 传输模式. 文本模式=FTP_ASCII;二进制模式=FTP_BINARY
	 * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE
	 */
	public function ftp_put($remote_file, $local_file, $mode = FTP_BINARY) {
		$remote_file = phpcom_ftp::clear($remote_file);
		$local_file = phpcom_ftp::clear($local_file);
		$mode = intval($mode);
		return @ftp_put($this->connectid, $remote_file, $local_file, $mode);
	}

	/**
	 * 上传一个已经打开的文件到 FTP 服务器
	 * @param string $remote_file 上传到服务器上的文件名
	 * @param resource $handle 所打开文件的句柄
	 * @param int $mode 传输模式. 文本模式=FTP_ASCII;二进制模式=FTP_BINARY
	 * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE
	 */
	public function ftp_fput($remote_file, $handle, $mode = FTP_BINARY) {
		$remote_file = phpcom_ftp::clear($remote_file);
		$mode = intval($mode);
		return @ftp_fput($this->connectid, $remote_file, $handle, $mode);
	}

	/**
	 * 返回指定文件的大小
	 * @param string $remote_file 远程文件
	 * @return int 获取成功返回文件大小，否则返回 -1
	 */
	public function ftp_size($remote_file) {
		$remote_file = phpcom_ftp::clear($remote_file);
		return @ftp_size($this->connectid, $remote_file);
	}

	/**
	 * 关闭一个 FTP 连接
	 * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE
	 */
	public function ftp_close() {
		return @ftp_close($this->connectid);
	}

	/**
	 * 删除 FTP 服务器上的一个文件
	 * @param string $path 指定文件路径
	 * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE
	 */
	public function ftp_delete($path) {
		$path = phpcom_ftp::clear($path);
		return @ftp_delete($this->connectid, $path);
	}

	/**
	 * 从 FTP 服务器上下载一个文件
	 * @param string $local_file 本地文件
	 * @param string $remote_file 远程文件
	 * @param int $mode 传送模式. 文本模式=FTP_ASCII;二进制模式=FTP_BINARY
	 * @param int $resumepos 规定在远程文件中的何处开始拷贝。默认是 0
	 * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE
	 */
	public function ftp_get($local_file, $remote_file, $mode = FTP_BINARY, $resumepos = 0) {
		$remote_file = phpcom_ftp::clear($remote_file);
		$local_file = phpcom_ftp::clear($local_file);
		$mode = intval($mode);
		$resumepos = intval($resumepos);
		return @ftp_get($this->connectid, $local_file, $remote_file, $mode, $resumepos);
	}

	/**
	 * 登录 FTP 服务器
	 * @param string $username 用户名
	 * @param string $password 密码
	 * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE
	 */
	public function ftp_login($username, $password) {
		$username = phpcom_ftp::clear($username);
		$password = str_replace(array("\n", "\r"), array('', ''), $password);
		return @ftp_login($this->connectid, $username, $password);
	}

	/**
	 * 返回当前 FTP 被动模式是否打开
	 * @param bool $pasv 如果参数 pasv 为 TRUE，打开被动模式传输
	 * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE
	 */
	public function ftp_pasv($pasv) {
		return @ftp_pasv($this->connectid, $pasv ? TRUE : FALSE);
	}

	/**
	 * 在 FTP 服务器上改变当前目录
	 * @param string $directory 目标目录
	 * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE
	 */
	public function ftp_chdir($directory) {
		$directory = phpcom_ftp::clear($directory);
		return @ftp_chdir($this->connectid, $directory);
	}

	/**
	 * 向FTP服务器发送 SITE 命令
	 * @param string $command SITE 命令
	 * @return bool 成功时返回 TRUE， 或者在失败时返回 FALSE
	 */
	public function ftp_site($command) {
		$command = phpcom_ftp::clear($command);
		return @ftp_site($this->connectid, $command);
	}

	/**
	 * 设定权限,指定远程文件模式
	 * @param string $filename 远程文件名
	 * @param int $mode 设置权限,给出一个八进制价值
	 * @return int 返回了一个新的文件权限,发生错误则返回 FALSE
	 */
	public function ftp_chmod($filename, $mode = 0777) {
		$filename = phpcom_ftp::clear($filename);
		if (function_exists('ftp_chmod')) {
			return @ftp_chmod($this->connectid, $mode, $filename);
		} else {
			return @ftp_site($this->connectid, 'CHMOD ' . $mode . ' ' . $filename);
		}
	}

	/**
	 * 返回当前目录名
	 * @return string 返回当前目录名称，发生错误则返回 FALSE
	 */
	public function ftp_pwd() {
		return @ftp_pwd($this->connectid);
	}

}
?>
