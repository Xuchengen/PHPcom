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
	 * 构造函数
	 * @param array $config 数据库配置
	 */
	public function __construct($config = null) {
		if (!empty($config)) {
			$this->set_config($config);
		}
	}

	/**
	 * 设置数据库配置
	 * @param array $config 数据库配置
	 */
	public function set_config($config) {
		$this->dbconfig = &$config;
		$this->tablepre = $config['1']['tablepre'];
		if (!empty($this->dbconfig['map'])) {
			$this->map = $this->dbconfig['map'];
		}
	}

	/**
	 * 得到加前缀的表名
	 * @param string $tablename 表名
	 * @return string 返回加前缀的表名
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
	 * 连接数据库
	 * @param int $connid 数据库连接ID
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
	 * 打开一个数据库服务器的连接
	 * @param string $pdodsn DSN（数据源）
	 * @param string $dbuser 用户名
	 * @param string $dbpass 用户密码
	 * @param int $pconnect 打开（持久）连接
	 * @param string $dbcharset 数据库字符集
	 * @return PDO 返回一个PDO连接标识
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
	 * 数据库查询
	 * @param string $sql
	 * @param string $method
	 * @return PDO 返回查询PDO对象
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
	 * 执行一条SQL语句返回受影响的行
	 * @param string $sql SQL语句
	 * @param int $lp 优先级调度（低优先级）
	 * @return int 返回受影响的行
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
	 * (读)返回记录数据
	 * @deprecated   PDO::FETCH_ASSOC==2 PDO::FETCH_NUM==3 PDO::FETCH_BOTH==4 PDO::FETCH_OBJ==5
	 * @param PDO $query PDO查询对象
	 * @param int $result_type 返回结果集类型
	 * @return array 从结果集中取得一行作为关联数组
	 */
	public function fetch_array($query, $result_type = PDO::FETCH_ASSOC) {
		$query->setFetchMode(($result_type) ? $result_type : PDO::FETCH_ASSOC);
		return $query->fetch();
	}
	
	public function fetchColumn($query, $column_number = 0) {
		return $query->fetchColumn($column_number);
	}
	
	/**
	 * (读)返回数据行数组
	 * @param resource $query SQL查询标识符
	 * @return array 从结果集中取得一行作为枚举数组
	 */
	public function fetch_row($query) {
		$query->setFetchMode(PDO::FETCH_NUM);
		return $query->fetch();
	}
	
	/**
	 * (读)返回单条记录数据
	 * @param string $sql SQL查询语句
	 * @return string 返回第一条记录数据
	 */
	public function fetch_first($sql, $result_type = PDO::FETCH_ASSOC) {
		$query = $this->query($sql);
		return $this->fetch_array($query, ($result_type) ? $result_type : PDO::FETCH_ASSOC);
	}

	/**
	 * 取得第一条结果数据
	 * @param string $sql SQL查询语句
	 * @return mixed 返回结果集中第一个单元的内容
	 */
	public function result_first($sql) {
		return $this->result($this->query($sql), 0);
	}

	/**
	 * 取得查询所影响的记录行数
	 * @return int 返回受影响的行的数目，查询失败返回 -1
	 */
	public function affected_rows() {
		return $this->query == null ? 0 : $this->query->rowCount();
	}

	/**
	 * 返回结果集中一个字段的值
	 * @param PDO $query PDO查询对象
	 * @param int $row 指定行号，行号从 0 开始。
	 * @return mixed 返回字段值。
	 */
	public function result($query, $row = 0) {
		return $query->fetchColumn($row);
	}

	/**
	 * 取得结果集中行的数目
	 * @param PDO $query PDO查询对象
	 * @return int 返回结果集中行的数目
	 */
	public function num_rows($query) {
		try {
			return $query->rowCount();
		} catch (PDOException $e) {
			return 0;
		}
	}

	/**
	 * 取得结果集中字段的数目
	 * @param PDO $query PDO查询对象
	 * @return int 返回结果集中字段的数目
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
     * 从结果集中取得列信息并作为对象返回
     * @param resource $query SQL查询标识符
     * @return object 返回一个包含字段信息的对象
     */
    public function fetch_fields($query) {
        return NULL;
	}
    
	/**
	 * 释放结果内存
	 * @param PDO $query PDO查询对象
	 * @return bool 如果成功，则返回 true，如果失败，则返回 false
	 */
	public function free_result($query) {
        $query->closeCursor();
		$this->query = null;
	}

	/**
	 * 取得最后一次插入记录的ID值
	 * @return int 返回最后一次插入记录的ID值
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
	 * 获取当前数据库大小
	 * @return int 返回数据库大小
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
	 * 获取上一个数据库操作产生的文本错误信息
	 * @return string 返回文本错误信息
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
	 * 获取数据库服务器的错误代码
	 * @return int 返回错误信息代码
	 */
	public function errno() {
		return intval($this->curconn->errorCode());
	}

	/**
	 * 获取数据库服务器版本信息
	 * @return string 返回数据库版本信息
	 */
	public function version() {
		if (empty($this->version)) {
			list($version) = explode('-', $this->curconn->getAttribute(constant("PDO::ATTR_SERVER_VERSION")));
			$this->version = $version;
		}
		return $this->version;
	}

	/**
	 * 关闭数据库连接
	 * @return bool 如果成功则返回 TRUE，失败则返回 FALSE
	 */
	public function close() {
		$this->query = null;
		$this->conn = null;
		return true;
	}

	/**
	 * 转义一个字符串用于  query
	 * @param string $str 要转义的字符串
	 * @return string 返回转义后的字符串
	 */
	public function escape_string($str) {
		$str = $this->curconn->quote($str);
		if (strpos($str, "'") === 0){
			$str = substr($str, 1, -1);
		}
		return $str;
	}

	/**
	 * 停止SQL操作并返回消息
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
