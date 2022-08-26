<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : pdomysql.php    2011-7-5 23:52:43
 */
!defined('IN_PHPCOM') && exit('Access denied');
define('RESULT_TYPE_ASSOC', PDO::FETCH_ASSOC);
define('RESULT_TYPE_NUM', PDO::FETCH_NUM);
define('RESULT_TYPE_BOTH', PDO::FETCH_BOTH);

class db_pdomysql {
	var $version = '';
	var $querycount = 0;
	var $curconn;
	var $query = false;
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
	public function __construct($config = null) {
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

		$pdodsn = "mysql:host={$this->dbconfig[$connid]['dbhost']};dbname={$this->dbconfig[$connid]['dbname']}";
		$this->conn[$connid] = $this->connection(
						$pdodsn,
						$this->dbconfig[$connid]['dbuser'],
						$this->dbconfig[$connid]['dbpass'],
						$this->dbconfig[$connid]['pconnect'],
						$this->dbconfig[$connid]['charset']
		);
		$this->curconn = $this->conn[$connid];
	}

	/**
	 * ��һ�����ݿ������������
	 * @param string $pdodsn DSN������Դ��
	 * @param string $dbuser �û���
	 * @param string $dbpass �û�����
	 * @param int $pconnect �򿪣��־ã�����
	 * @param string $dbcharset ���ݿ��ַ���
	 * @return PDO ����һ��PDO���ӱ�ʶ
	 */
	public function connection($pdodsn, $dbuser, $dbpass, $pconnect, $dbcharset = 'gbk') {
        $this->pconnect = $pconnect;
		try {
			$persistent = $pconnect == '0' ? FALSE : TRUE;
			$conn = new PDO($pdodsn, $dbuser, $dbpass, array(PDO::ATTR_PERSISTENT => $persistent)); //PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '$dbcharset';",
			$conn->exec("SET NAMES '$dbcharset';");
			$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			//$conn->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->curconn = $conn;
			return $conn;
		} catch (PDOException $e) {
			$this->halt("db_connect_failed");
		}
	}

	/**
	 * ���ݿ��ѯ
	 * @param string $sql
	 * @param string $method
	 * @return PDO ���ز�ѯPDO����
	 */
	public function query($sql, $method = '') {
		try {
			$this->query = $this->curconn->query($sql);
			$this->querycount++;
			return $this->query;
		} catch (PDOException $e) {
			$this->halt("db_query_error", $sql);
			return false;
		}
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
			return $this->curconn->exec($sql);
		}else{
			return $this->curconn->exec($sql);
		}
	}

	/**
	 * (��)���ؼ�¼����
	 * @deprecated   PDO::FETCH_ASSOC==2 PDO::FETCH_NUM==3 PDO::FETCH_BOTH==4 PDO::FETCH_OBJ==5
	 * @param PDO $query PDO��ѯ����
	 * @param int $result_type ���ؽ��������
	 * @return array �ӽ������ȡ��һ����Ϊ��������
	 */
	public function fetch_array($query, $result_type = PDO::FETCH_ASSOC) {
		$query->setFetchMode(($result_type) ? $result_type : PDO::FETCH_ASSOC);
		return $query->fetch();
	}
	
	public function fetchColumn($query, $column_number = 0) {
		return $query->fetchColumn($column_number);
	}
	
	/**
	 * (��)��������������
	 * @param resource $query SQL��ѯ��ʶ��
	 * @return array �ӽ������ȡ��һ����Ϊö������
	 */
	public function fetch_row($query) {
		$query->setFetchMode(PDO::FETCH_NUM);
		return $query->fetch();
	}
	
	/**
	 * (��)���ص�����¼����
	 * @param string $sql SQL��ѯ���
	 * @return string ���ص�һ����¼����
	 */
	public function fetch_first($sql, $result_type = PDO::FETCH_ASSOC) {
		$query = $this->query($sql);
		return $this->fetch_array($query, ($result_type) ? $result_type : PDO::FETCH_ASSOC);
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
		return $this->query == null ? 0 : $this->query->rowCount();
	}

	/**
	 * ���ؽ������һ���ֶε�ֵ
	 * @param PDO $query PDO��ѯ����
	 * @param int $row ָ���кţ��кŴ� 0 ��ʼ��
	 * @return mixed �����ֶ�ֵ��
	 */
	public function result($query, $row = 0) {
		return $query->fetchColumn($row);
	}

	/**
	 * ȡ�ý�������е���Ŀ
	 * @param PDO $query PDO��ѯ����
	 * @return int ���ؽ�������е���Ŀ
	 */
	public function num_rows($query) {
		try {
			return $query->rowCount();
		} catch (PDOException $e) {
			return 0;
		}
	}

	/**
	 * ȡ�ý�������ֶε���Ŀ
	 * @param PDO $query PDO��ѯ����
	 * @return int ���ؽ�������ֶε���Ŀ
	 */
	public function num_fields($query) {
		try {
			$query = $query->columnCount();
			return $query;
		} catch (PDOException $e) {
			return 0;
		}
	}
    
    /**
     * �ӽ������ȡ������Ϣ����Ϊ���󷵻�
     * @param resource $query SQL��ѯ��ʶ��
     * @return object ����һ�������ֶ���Ϣ�Ķ���
     */
    public function fetch_fields($query) {
        return NULL;
	}
    
	/**
	 * �ͷŽ���ڴ�
	 * @param PDO $query PDO��ѯ����
	 * @return bool ����ɹ����򷵻� true�����ʧ�ܣ��򷵻� false
	 */
	public function free_result($query) {
        $query->closeCursor();
		$this->query = null;
	}

	/**
	 * ȡ�����һ�β����¼��IDֵ
	 * @return int �������һ�β����¼��IDֵ
	 */
	public function insert_id() {
		$insertid = $this->curconn == null ? 0 : $this->curconn->lastInsertId();
		return $insertid >= 0 ? $insertid : $this->result($this->query('SELECT LAST_INSERT_ID()'));
	}
	
	public function beginTransaction() {
		$this->transactions = $this->curconn->beginTransaction();
		return true;
	}
	
	public function commit() {
		if($this->transactions){
			$this->transactions = false;
			return $this->curconn->commit();
		}
		return true;
	}
	
	public function rollBack() {
		if($this->transactions){
			$this->transactions = false;
			return $this->curconn->rollBack();
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
		}
		return intval($this->dbsize);
	}
	
	/**
	 * ��ȡ��һ�����ݿ�����������ı�������Ϣ
	 * @return string �����ı�������Ϣ
	 */
	public function error() {
		$err = $this->curconn->errorInfo();
		$msg = '';
		if (is_array($err)) {
			if ($err['0'] != '00000') {
				$msg = 'SQLSTATE error code: ' . $err['0'];
				$msg .= '<br/>error code: ' . $err['1'];
				$msg .= '<br/>error message: ' . $err['2'];
			}
		}
		return $msg;
	}

	/**
	 * ��ȡ���ݿ�������Ĵ������
	 * @return int ���ش�����Ϣ����
	 */
	public function errno() {
		return intval($this->curconn->errorCode());
	}

	/**
	 * ��ȡ���ݿ�������汾��Ϣ
	 * @return string �������ݿ�汾��Ϣ
	 */
	public function version() {
		if (empty($this->version)) {
			list($version) = explode('-', $this->curconn->getAttribute(constant("PDO::ATTR_SERVER_VERSION")));
			$this->version = $version;
		}
		return $this->version;
	}

	/**
	 * �ر����ݿ�����
	 * @return bool ����ɹ��򷵻� TRUE��ʧ���򷵻� FALSE
	 */
	public function close() {
		$this->query = null;
		$this->conn = null;
		return true;
	}

	/**
	 * ת��һ���ַ�������  query
	 * @param string $str Ҫת����ַ���
	 * @return string ����ת�����ַ���
	 */
	public function escape_string($str) {
		$str = $this->curconn->quote($str);
		if (strpos($str, "'") === 0){
			$str = substr($str, 1, -1);
		}
		return $str;
	}

	/**
	 * ֹͣSQL������������Ϣ
	 * @param <type> $message
	 * @param <type> $sql
	 */
	public function halt($message = '', $sql = '') {
		throw new dbException($message, $sql);
	}
	
	public function __destruct() {
		if ($this->curconn) {
			$this->query = null;
			$this->conn = null;
			$this->curconn = null;
		}
	}
}
?>
