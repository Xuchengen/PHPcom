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
	 * ʵ���� FTP ��
	 * @return phpcom_ftp ���� FTP ��ʵ��������
	 */
	public static function &instance() {
		static $_instance;
		if (empty($_instance)) {
			$_instance = new phpcom_ftp();
		}
		return $_instance;
	}

	/**
	 * FTP �๹�캯��
	 */
	public function __construct() {
		$this->init();
	}
	
	public function setAttachDir($dir) {
		$this->attachdir = trim($dir);
	}
	
	/**
	 * ��ʼ�� FTP ����������
	 * @param array $config FTP ����
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
	 * �ϴ�һ���ļ��� FTP ������
	 * @param string $localfile Ҫ�ϴ����ļ���
	 * @param string $destination �ϴ��� FTP ��������Ŀ���ļ�
	 * @return bool ����ɹ����� TRUE��ʧ���򷵻� FALSE
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
	 * ���� FTP ������
	 * @return resource ����ɹ�����һ�����ӱ�ʶ��ʧ���򷵻� FALSE
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
	 * ����һ���µ� FTP ����
	 * @param string $ftphost Ҫ���ӵķ�����
	 * @param string $username FTP �û���
	 * @param string $password FTP �û�����
	 * @param string $ftpdir FTP Ŀ��Ŀ¼
	 * @param int $ftpport FTP �������˿ں�
	 * @param int $timeout ���紫��ĳ�ʱʱ������
	 * @param bool $ftppasv �������Ϊ TRUE���򿪱���ģʽ����
	 * @return resource ����ɹ�����һ�����ӱ�ʶ��ʧ���򷵻� FALSE
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
	 * ���ô������
	 * @param int $code �������
	 */
	public function set_error($code = 0) {
		$this->errcode = $code;
	}

	/**
	 * �������
	 * @return int ����һ���������
	 */
	public function error() {
		return $this->errcode;
	}

	/**
	 * ��������ַ�
	 * @param string $str Ҫ������ַ�
	 * @return string �����������ַ�
	 */
	public static function clear($str) {
		return str_replace(array("\n", "\r", '..'), '', $str);
	}

	/**
	 * ���ø��� FTP ����ʱѡ��
	 * @param int $option ѡ��. Ĭ��=FTP_AUTOSEEK���ı����紫��ĳ�ʱʱ��=FTP_TIMEOUT_SEC
	 * @param mixed $value ���紫�䳬ʱʱ��
	 * @return bool ���ѡ���ܹ������ã����� TRUE�����򷵻� FALSE
	 */
	public function set_option($option, $value) {
		if (function_exists('ftp_set_option')) {
			return @ftp_set_option($this->connectid, $option, $value);
		}
	}

	/**
	 * �� FTP �������Ͻ���һ��Ŀ¼
	 * @param string $directory �½���Ŀ¼
	 * @return bool ����ɹ������½���Ŀ¼�������򷵻� FALSE
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
	 * ɾ�� FTP �������ϵ�һ��Ŀ¼
	 * @param string $directory Ҫɾ����Ŀ¼��������һ����Ŀ¼�ľ���·�������·��
	 * @return bool �ɹ�ʱ���� TRUE�� ������ʧ��ʱ���� FALSE
	 */
	public function ftp_rmdir($directory) {
		$directory = phpcom_ftp::clear($directory);
		return @ftp_rmdir($this->connectid, $directory);
	}

	/**
	 * �ϴ��ļ��� FTP ������
	 * @param string $remote_file Զ���ļ�·��
	 * @param string $local_file �����ļ�·��
	 * @param int $mode ����ģʽ. �ı�ģʽ=FTP_ASCII;������ģʽ=FTP_BINARY
	 * @return bool �ɹ�ʱ���� TRUE�� ������ʧ��ʱ���� FALSE
	 */
	public function ftp_put($remote_file, $local_file, $mode = FTP_BINARY) {
		$remote_file = phpcom_ftp::clear($remote_file);
		$local_file = phpcom_ftp::clear($local_file);
		$mode = intval($mode);
		return @ftp_put($this->connectid, $remote_file, $local_file, $mode);
	}

	/**
	 * �ϴ�һ���Ѿ��򿪵��ļ��� FTP ������
	 * @param string $remote_file �ϴ����������ϵ��ļ���
	 * @param resource $handle �����ļ��ľ��
	 * @param int $mode ����ģʽ. �ı�ģʽ=FTP_ASCII;������ģʽ=FTP_BINARY
	 * @return bool �ɹ�ʱ���� TRUE�� ������ʧ��ʱ���� FALSE
	 */
	public function ftp_fput($remote_file, $handle, $mode = FTP_BINARY) {
		$remote_file = phpcom_ftp::clear($remote_file);
		$mode = intval($mode);
		return @ftp_fput($this->connectid, $remote_file, $handle, $mode);
	}

	/**
	 * ����ָ���ļ��Ĵ�С
	 * @param string $remote_file Զ���ļ�
	 * @return int ��ȡ�ɹ������ļ���С�����򷵻� -1
	 */
	public function ftp_size($remote_file) {
		$remote_file = phpcom_ftp::clear($remote_file);
		return @ftp_size($this->connectid, $remote_file);
	}

	/**
	 * �ر�һ�� FTP ����
	 * @return bool �ɹ�ʱ���� TRUE�� ������ʧ��ʱ���� FALSE
	 */
	public function ftp_close() {
		return @ftp_close($this->connectid);
	}

	/**
	 * ɾ�� FTP �������ϵ�һ���ļ�
	 * @param string $path ָ���ļ�·��
	 * @return bool �ɹ�ʱ���� TRUE�� ������ʧ��ʱ���� FALSE
	 */
	public function ftp_delete($path) {
		$path = phpcom_ftp::clear($path);
		return @ftp_delete($this->connectid, $path);
	}

	/**
	 * �� FTP ������������һ���ļ�
	 * @param string $local_file �����ļ�
	 * @param string $remote_file Զ���ļ�
	 * @param int $mode ����ģʽ. �ı�ģʽ=FTP_ASCII;������ģʽ=FTP_BINARY
	 * @param int $resumepos �涨��Զ���ļ��еĺδ���ʼ������Ĭ���� 0
	 * @return bool �ɹ�ʱ���� TRUE�� ������ʧ��ʱ���� FALSE
	 */
	public function ftp_get($local_file, $remote_file, $mode = FTP_BINARY, $resumepos = 0) {
		$remote_file = phpcom_ftp::clear($remote_file);
		$local_file = phpcom_ftp::clear($local_file);
		$mode = intval($mode);
		$resumepos = intval($resumepos);
		return @ftp_get($this->connectid, $local_file, $remote_file, $mode, $resumepos);
	}

	/**
	 * ��¼ FTP ������
	 * @param string $username �û���
	 * @param string $password ����
	 * @return bool �ɹ�ʱ���� TRUE�� ������ʧ��ʱ���� FALSE
	 */
	public function ftp_login($username, $password) {
		$username = phpcom_ftp::clear($username);
		$password = str_replace(array("\n", "\r"), array('', ''), $password);
		return @ftp_login($this->connectid, $username, $password);
	}

	/**
	 * ���ص�ǰ FTP ����ģʽ�Ƿ��
	 * @param bool $pasv ������� pasv Ϊ TRUE���򿪱���ģʽ����
	 * @return bool �ɹ�ʱ���� TRUE�� ������ʧ��ʱ���� FALSE
	 */
	public function ftp_pasv($pasv) {
		return @ftp_pasv($this->connectid, $pasv ? TRUE : FALSE);
	}

	/**
	 * �� FTP �������ϸı䵱ǰĿ¼
	 * @param string $directory Ŀ��Ŀ¼
	 * @return bool �ɹ�ʱ���� TRUE�� ������ʧ��ʱ���� FALSE
	 */
	public function ftp_chdir($directory) {
		$directory = phpcom_ftp::clear($directory);
		return @ftp_chdir($this->connectid, $directory);
	}

	/**
	 * ��FTP���������� SITE ����
	 * @param string $command SITE ����
	 * @return bool �ɹ�ʱ���� TRUE�� ������ʧ��ʱ���� FALSE
	 */
	public function ftp_site($command) {
		$command = phpcom_ftp::clear($command);
		return @ftp_site($this->connectid, $command);
	}

	/**
	 * �趨Ȩ��,ָ��Զ���ļ�ģʽ
	 * @param string $filename Զ���ļ���
	 * @param int $mode ����Ȩ��,����һ���˽��Ƽ�ֵ
	 * @return int ������һ���µ��ļ�Ȩ��,���������򷵻� FALSE
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
	 * ���ص�ǰĿ¼��
	 * @return string ���ص�ǰĿ¼���ƣ����������򷵻� FALSE
	 */
	public function ftp_pwd() {
		return @ftp_pwd($this->connectid);
	}

}
?>
