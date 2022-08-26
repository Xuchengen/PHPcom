<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : mysql.php    2011-7-5 23:57:02
 */
!defined('IN_PHPCOM') && exit('Access denied');
define('RESULT_TYPE_ASSOC', MYSQL_ASSOC);
define('RESULT_TYPE_NUM', MYSQL_NUM);
define('RESULT_TYPE_BOTH', MYSQL_BOTH);

class db_mysql {

	var $version = '';
	var $querycount = 0;
	var $curconn;
	var $conn = array();
    var $pconnect = 0;
	var $dbconfig = array();
	var $tablepre = '';
	var $map = array();
	var $dbsize = 0;
	var $transactions = false;

	/**
	 * ���캯��
	 * @param array $config ���ݿ�����
	 */
	public function __construct($config = array()) {
		if (!empty($config)) {
			$this->set_config($config);
		}
	}

	/**
	 * �������ݿ�����
	 * @param array $config ���ݿ�����
	 */
	public function set_config($config) {
		$this->dbconfig = &$config;
		$this->tablepre = $config['1']['tablepre'];
		if (!empty($this->dbconfig['map'])) {
			$this->map = $this->dbconfig['map'];
		}
	}

	/**
	 * �õ���ǰ׺�ı���
	 * @param string $tablename ����
	 * @return string ���ؼ�ǰ׺�ı���
	 */
	public function table_name($tablename) {
		if (!empty($this->map) && !empty($this->map[$tablename])) {
			$id = $this->map[$tablename];
			if (empty($this->conn[$id])) {
				$this->connect($id);
			}
			$this->curconn = $this->conn[$id];
		} else {
			$this->curconn = $this->conn[1];
		}
		return $this->tablepre . $tablename;
	}

	/**
	 * �������ݿ�
	 * @param int $connid ���ݿ�����ID
	 */
	public function connect($connid = 1) {
		if (empty($this->dbconfig) || empty($this->dbconfig[$connid])) {
			$this->halt('db_config_notfound');
		}
		$this->conn[$connid] = $this->connection(
				$this->dbconfig[$connid]['dbhost'], $this->dbconfig[$connid]['dbuser'], $this->dbconfig[$connid]['dbpass'], $this->dbconfig[$connid]['dbname'], $this->dbconfig[$connid]['pconnect'], $this->dbconfig[$connid]['charset']
		);
		$this->curconn = $this->conn[$connid];
	}

	/**
	 * ��һ�����ݿ������������
	 * @param string $dbhost ������
	 * @param string $dbuser �û���
	 * @param string $dbpass �û�����
	 * @param string $dbname ���ݿ���
	 * @param int $pconnect �򿪣��־ã�����
	 * @param string $dbcharset ���ݿ��ַ���
	 * @return int ����һ�� MySQL ���ӱ�ʶ��ʧ���򷵻� FALSE
	 */
	public function connection($dbhost, $dbuser, $dbpass, $dbname, $pconnect, $dbcharset = 'gbk') {
        $this->pconnect = $pconnect;
		$conn = $pconnect == 0 ? @mysql_connect($dbhost, $dbuser, $dbpass, true) : @mysql_pconnect($dbhost, $dbuser, $dbpass);
		if (!$conn) {
			$this->halt('db_connect_failed');
		} else {
			$this->curconn = $conn;
			mysql_errno($conn) != 0 && $this->halt('db_connect_failed');
			$dbversion = mysql_get_server_info($conn);
			if (version_compare($dbversion, '4.1.0', '>=')) {
				$dbcharset = $dbcharset ? $dbcharset : $this->dbconfig[1]['charset'];
				$serverset = $dbcharset ? 'character_set_connection=' . $dbcharset . ', character_set_results=' . $dbcharset . ', character_set_client=binary' : '';
				$serverset .= version_compare($dbversion, '5.0.2', '>=') ? ((empty($serverset) ? '' : ',') . 'sql_mode=\'\'') : '';
				$serverset && mysql_query("SET $serverset", $conn);
			}

			if ($dbname && !@mysql_select_db($dbname, $conn)) {
				$this->halt("db_not_connect");
			}
		}
		return $conn;
	}

	/**
	 * ���ݿ��ѯ
	 * @param string $sql
	 * @param string $method
	 * @return resource ���ز�ѯ��ԴID
	 */
	public function query($sql, $method = '') {
		try {
			$func = $method == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
			if (!($query = $func($sql, $this->curconn))) {
				if (in_array($this->errno(), array(2006, 2013)) && substr($method, 0, 5) != 'RETRY') {
	                $this->close();
					$this->connect();
					return $this->query($sql, 'RETRY' . $method);
				}
	
				if ($method != 'SILENT' && substr($method, 5) != 'SILENT') {
					$this->halt('db_query_error', $sql);
				}
			}
			$this->querycount++;
		} catch (Exception $e) {
			$this->halt("db_query_error", $sql);
			return false;
		}
		return $query;
	}

	/**
	 * ִ��һ��SQL��䷵����Ӱ�����
	 * @param string $sql SQL���
	 * @param int $lp ���ȼ����ȣ������ȼ���
	 * @return int ������Ӱ�����
	 */
	public function exec($sql, $lp = 1) {
		if ($lp) {
			$tmpsql6 = substr($sql, 0, 6);
			if (strtoupper($tmpsql6 . 'E') == 'REPLACE') {
				$sql = 'REPLACE LOW_PRIORITY' . substr($sql, 7);
			} else {
				$sql = $tmpsql6 . ' LOW_PRIORITY' . substr($sql, 6);
			}
			$this->query($sql, 'UNBUFFERED');
		}else{
			$this->query($sql, 'UNBUFFERED');
		}
		return @mysql_affected_rows($this->curconn);
	}

	/**
	 * ѡ�� MySQL ���ݿ�
	 * @param string $dbname ���ݿ���
	 * @return bool �ɹ�ʱ���� TRUE��ʧ��ʱ���� FALSE
	 */
	public function select_db($dbname) {
		return mysql_select_db($dbname, $this->curconn);
	}

	/**
	 * (��)���ؼ�¼���ݼ�
	 * @deprecated   MYSQL_ASSOC==1 MYSQL_NUM==2 MYSQL_BOTH==3
	 * @param resource $query SQL��ѯ��ʶ��
	 * @param int $result_type ���ؽ��������
	 * @return array �ӽ������ȡ��һ����Ϊ��������
	 */
	public function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, ($result_type) ? $result_type : MYSQL_ASSOC);
		//return array_change_key_case(mysql_fetch_array($query, $result_type),CASE_LOWER);	//����������е� KEY ��ת��ΪСд
	}

	/**
	 * (��)��������������
	 * @param resource $query SQL��ѯ��ʶ��
	 * @return array �ӽ������ȡ��һ����Ϊö������
	 */
	public function fetch_row($query) {
		return mysql_fetch_row($query);
	}

	/**
	 * (��)���ص�����¼����
	 * @param string $sql SQL��ѯ���
	 * @return string ���ص�һ����¼����
	 */
	public function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
	}

	/**
	 * ȡ�õ�һ���������
	 * @param string $sql SQL��ѯ���
	 * @return mixed ���ؽ�����е�һ����Ԫ������
	 */
	public function result_first($sql) {
		return $this->result($this->query($sql), 0);
	}

	/**
	 * ȡ�ò�ѯ��Ӱ��ļ�¼����
	 * @return int ������Ӱ����е���Ŀ����ѯʧ�ܷ��� -1
	 */
	public function affected_rows() {
		return mysql_affected_rows($this->curconn);
	}

	/**
	 * ���ؽ������һ���ֶε�ֵ
	 * @param resource $query SQL��ѯ��ʶ��
	 * @param int $row ָ���кţ��кŴ� 0 ��ʼ��
	 * @return mixed �����ֶ�ֵ�����ʧ�ܣ��򷵻� false
	 */
	public function result($query, $row = 0) {
		$rt = mysql_fetch_row($query);
		return isset($rt[$row]) ? $rt[$row] : false;
	}

	/**
	 * ȡ�ý�������е���Ŀ
	 * @param resource $query SQL��ѯ��ʶ��
	 * @return int ���ؽ�������е���Ŀ
	 */
	public function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	/**
	 * ȡ�ý�������ֶε���Ŀ
	 * @param resource $query SQL��ѯ��ʶ��
	 * @return int ���ؽ�������ֶε���Ŀ
	 */
	public function num_fields($query) {
		return mysql_num_fields($query);
	}
    
    /**
     * �ӽ������ȡ������Ϣ����Ϊ���󷵻�
     * @param resource $query SQL��ѯ��ʶ��
     * @return object ����һ�������ֶ���Ϣ�Ķ���
     */
    public function fetch_fields($query) {
		return mysql_fetch_field($query);
	}
    
	/**
	 * �ͷŽ���ڴ�
	 * @param resource $query SQL��ѯ��ʶ��
	 * @return bool ����ɹ����򷵻� true�����ʧ�ܣ��򷵻� false
	 */
	public function free_result($query) {
		return mysql_free_result($query);
	}

	/**
	 * ȡ�����һ�β����¼��IDֵ
	 * @return int �������һ�β����¼��IDֵ
	 */
	public function insert_id() {
		return ($id = mysql_insert_id($this->curconn)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}
	
	public function beginTransaction() {
		if($this->transactions === false){
			//$this->query("BEGIN");
			$this->query("SET AUTOCOMMIT=0");
			$this->transactions = true;
		}
		return true;
	}
	
	public function commit() {
		if($this->transactions){
			$this->query("COMMIT");
			$this->query("SET AUTOCOMMIT=1");
			$this->transactions = false;
		}
		return true;
	}
	
	public function rollBack() {
		if($this->transactions){
			$this->query("ROLLBACK");
			$this->query("SET AUTOCOMMIT=1");
			$this->transactions = false;
		}
		return true;
	}
	
	/**
	 * ��ȡ��ǰ���ݿ��С
	 * @return int �������ݿ��С
	 */
	public function size() {
		if (!$this->dbsize) {
			$dbname = $this->dbconfig['1']['dbname'];
			if(version_compare($this->version(), '5.0.0', '>=')){
				$query = $this->query("SELECT sum(DATA_LENGTH + INDEX_LENGTH) AS dbsize 
						FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA='$dbname'");
				$this->dbsize = $this->result($query);
			}else{
				$query = $this->query("SHOW TABLE STATUS FROM $dbname");
				while ($table = $this->fetch_array($query)) {
					$this->dbsize += $table['Data_length'] + $table['Index_length'];
				}
			}
			$this->free_result($query);
		}
		return intval($this->dbsize);
	}

	/**
	 * ��ȡ��һ�����ݿ�����������ı�������Ϣ
	 * @return string �����ı�������Ϣ
	 */
	public function error() {
		return (($this->curconn) ? mysql_error($this->curconn) : mysql_error());
	}

	/**
	 * ��ȡ���ݿ�������Ĵ������
	 * @return int ���ش�����Ϣ����
	 */
	public function errno() {
		return intval(($this->curconn) ? mysql_errno($this->curconn) : mysql_errno());
	}

	/**
	 * ��ȡ���ݿ�������汾��Ϣ
	 * @return string �������ݿ�汾��Ϣ
	 */
	public function version() {
		if (empty($this->version)) {
			list($version) = explode('-', mysql_get_server_info($this->curconn));
			$this->version = $version;
		}
		return $this->version;
	}

	/**
	 * �ر����ݿ�����
	 * @return bool ����ɹ��򷵻� TRUE��ʧ���򷵻� FALSE
	 */
	public function close() {
		if($this->curconn){
			return mysql_close($this->curconn);
		}
		return false;
	}

	/**
	 * ת��һ���ַ������� mysql_query
	 * @param string $str Ҫת����ַ���
	 * @return string ����ת�����ַ���
	 */
	public function escape_string($str) {
		if (function_exists('mysql_real_escape_string') && is_resource($this->curconn)){
			return mysql_real_escape_string($str, $this->curconn);
		}elseif (function_exists('mysql_escape_string')){
			return mysql_escape_string($str);
		}
		return addslashes($str);
	}

	/**
	 * ֹͣSQL������������Ϣ
	 * @param string $message
	 * @param string $sql
	 */
	public function halt($message = '', $sql = '') {
		throw new dbException($message, $sql);
	}

}

?>
