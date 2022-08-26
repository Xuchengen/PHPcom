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
	 * 构造函数
	 * @param array $config 数据库配置
	 */
	public function __construct($config = array()) {
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
		$this->conn[$connid] = $this->connection(
				$this->dbconfig[$connid]['dbhost'], $this->dbconfig[$connid]['dbuser'], $this->dbconfig[$connid]['dbpass'], $this->dbconfig[$connid]['dbname'], $this->dbconfig[$connid]['pconnect'], $this->dbconfig[$connid]['charset']
		);
		$this->curconn = $this->conn[$connid];
	}

	/**
	 * 打开一个数据库服务器的连接
	 * @param string $dbhost 主机名
	 * @param string $dbuser 用户名
	 * @param string $dbpass 用户密码
	 * @param string $dbname 数据库名
	 * @param int $pconnect 打开（持久）连接
	 * @param string $dbcharset 数据库字符集
	 * @return int 返回一个 MySQL 连接标识，失败则返回 FALSE
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
	 * 数据库查询
	 * @param string $sql
	 * @param string $method
	 * @return resource 返回查询资源ID
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
			$this->query($sql, 'UNBUFFERED');
		}else{
			$this->query($sql, 'UNBUFFERED');
		}
		return @mysql_affected_rows($this->curconn);
	}

	/**
	 * 选择 MySQL 数据库
	 * @param string $dbname 数据库名
	 * @return bool 成功时返回 TRUE，失败时返回 FALSE
	 */
	public function select_db($dbname) {
		return mysql_select_db($dbname, $this->curconn);
	}

	/**
	 * (读)返回记录数据集
	 * @deprecated   MYSQL_ASSOC==1 MYSQL_NUM==2 MYSQL_BOTH==3
	 * @param resource $query SQL查询标识符
	 * @param int $result_type 返回结果集类型
	 * @return array 从结果集中取得一行作为关联数组
	 */
	public function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, ($result_type) ? $result_type : MYSQL_ASSOC);
		//return array_change_key_case(mysql_fetch_array($query, $result_type),CASE_LOWER);	//将数组的所有的 KEY 都转换为小写
	}

	/**
	 * (读)返回数据行数组
	 * @param resource $query SQL查询标识符
	 * @return array 从结果集中取得一行作为枚举数组
	 */
	public function fetch_row($query) {
		return mysql_fetch_row($query);
	}

	/**
	 * (读)返回单条记录数据
	 * @param string $sql SQL查询语句
	 * @return string 返回第一条记录数据
	 */
	public function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
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
		return mysql_affected_rows($this->curconn);
	}

	/**
	 * 返回结果集中一个字段的值
	 * @param resource $query SQL查询标识符
	 * @param int $row 指定行号，行号从 0 开始。
	 * @return mixed 返回字段值。如果失败，则返回 false
	 */
	public function result($query, $row = 0) {
		$rt = mysql_fetch_row($query);
		return isset($rt[$row]) ? $rt[$row] : false;
	}

	/**
	 * 取得结果集中行的数目
	 * @param resource $query SQL查询标识符
	 * @return int 返回结果集中行的数目
	 */
	public function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	/**
	 * 取得结果集中字段的数目
	 * @param resource $query SQL查询标识符
	 * @return int 返回结果集中字段的数目
	 */
	public function num_fields($query) {
		return mysql_num_fields($query);
	}
    
    /**
     * 从结果集中取得列信息并作为对象返回
     * @param resource $query SQL查询标识符
     * @return object 返回一个包含字段信息的对象
     */
    public function fetch_fields($query) {
		return mysql_fetch_field($query);
	}
    
	/**
	 * 释放结果内存
	 * @param resource $query SQL查询标识符
	 * @return bool 如果成功，则返回 true，如果失败，则返回 false
	 */
	public function free_result($query) {
		return mysql_free_result($query);
	}

	/**
	 * 取得最后一次插入记录的ID值
	 * @return int 返回最后一次插入记录的ID值
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
			$this->free_result($query);
		}
		return intval($this->dbsize);
	}

	/**
	 * 获取上一个数据库操作产生的文本错误信息
	 * @return string 返回文本错误信息
	 */
	public function error() {
		return (($this->curconn) ? mysql_error($this->curconn) : mysql_error());
	}

	/**
	 * 获取数据库服务器的错误代码
	 * @return int 返回错误信息代码
	 */
	public function errno() {
		return intval(($this->curconn) ? mysql_errno($this->curconn) : mysql_errno());
	}

	/**
	 * 获取数据库服务器版本信息
	 * @return string 返回数据库版本信息
	 */
	public function version() {
		if (empty($this->version)) {
			list($version) = explode('-', mysql_get_server_info($this->curconn));
			$this->version = $version;
		}
		return $this->version;
	}

	/**
	 * 关闭数据库连接
	 * @return bool 如果成功则返回 TRUE，失败则返回 FALSE
	 */
	public function close() {
		if($this->curconn){
			return mysql_close($this->curconn);
		}
		return false;
	}

	/**
	 * 转义一个字符串用于 mysql_query
	 * @param string $str 要转义的字符串
	 * @return string 返回转义后的字符串
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
	 * 停止SQL操作并返回消息
	 * @param string $message
	 * @param string $sql
	 */
	public function halt($message = '', $sql = '') {
		throw new dbException($message, $sql);
	}

}

?>
