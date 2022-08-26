<?php
/**
 * Copyright (c) 2010-2012 phpmain.com - All rights reserved.
 * Our Website : www.phpmain.com www.phpmain.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPMain.
 * This File   : mysql.php  2012-4-6
 */
!defined('IN_PHPCOM') && exit('Access denied');

class dbmysql{
	private $conn;
	private $tablepre;
	
	var $querynum = 0;
	var $history = array();
	var $time;

	function __construct($config = array()) {
		if (!empty($config)) {
			$this->connect($config['dbhost'], $config['dbuser'], $config['dbpass'], $config['dbname'], 
					$config['charset'], $config['pconnect'], $config['tablepre']);
		}
	}
	
	function __destruct() {
        if($this->conn){
        	mysql_close($this->conn);
        }
    }
    
	public function connect($dbhost, $dbuser, $dbpwd, $dbname = '', $dbcharset = 'gbk', $pconnect = 0, $tablepre = '', $time = 0) {
		$this->time = $time;
		$this->tablepre = $tablepre;
		if($pconnect == 0){
			$this->conn = mysql_connect($dbhost, $dbuser, $dbpwd, TRUE);
		}else{
			$this->conn = mysql_pconnect($dbhost, $dbuser, $dbpwd);
		}
		if(!$this->conn){
			$this->halt('Can not connect to MySQL server');
		}else{
			if (version_compare($this->version(), '4.1.0', '>=')) {
				if($dbcharset) {
					mysql_query("SET character_set_connection=".$dbcharset.", character_set_results=".$dbcharset.", character_set_client=binary", $this->conn);
				}
				if (version_compare($this->version(), '5.0.2', '>=')) {
					mysql_query("SET sql_mode=''", $this->conn);
				}
				if($dbname) {
					mysql_select_db($dbname, $this->conn);
					mysql_query("SET NAMES '$dbcharset';", $this->conn);
				}
			}
		}
	}

	public function query($sql, $type = '', $cachetime = FALSE) {
		$func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
		if(!($query = $func($sql, $this->conn)) && $type != 'SILENT') {
			$this->halt("SQL: $sql");
		}
		$this->querynum++;
		$this->history[] = $sql;
		return $query;
	}

	public function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	public function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
	}

	public function result_first($sql, &$data) {
		$query = $this->query($sql);
		$data = $this->result($query, 0);
	}

	public function result($query, $row) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	public function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}

	public function num_fields($query) {
		return mysql_num_fields($query);
	}

	public function free_result($query) {
		return mysql_free_result($query);
	}

	public function insert_id() {
		return ($id = mysql_insert_id($this->conn)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	public function fetch_row($query) {
		$query = mysql_fetch_row($query);
		return $query;
	}

	public function fetch_fields($query) {
		return mysql_fetch_field($query);
	}

	public function error() {
		return (($this->conn) ? mysql_error($this->conn) : mysql_error());
	}

	public function errno() {
		return intval(($this->conn) ? mysql_errno($this->conn) : mysql_errno());
	}

	public function version() {
		return mysql_get_server_info($this->conn);
	}

	public function close() {
		return mysql_close($this->conn);
	}

	function halt($message = '') {
		exit($message.'<br /> Error:'.$this->error().'<br />Errno:'.$this->errno());
	}
}
?>