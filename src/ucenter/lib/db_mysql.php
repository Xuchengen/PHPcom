<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : db_mysql.php    2011-12-8
 */
define('UC_RESULT_TYPE_ASSOC', MYSQL_ASSOC);
define('UC_RESULT_TYPE_NUM', MYSQL_NUM);
define('UC_RESULT_TYPE_BOTH', MYSQL_BOTH);

class UCenterDB {
    var $dbconn = 0;
    var $querynum = 0;
    
    var $dbhost;
	var $dbuser;
	var $dbpassword;
	var $dbcharset;
	var $pconnect;
	var $tablepre;
    
    function connect($dbhost, $dbuser, $dbpassword, $dbname, $pconnect = 0, $dbcharset = 'gbk', $tablepre = ''){
        $this->dbhost = $dbhost;
        $this->dbuser = $dbuser;
        $this->dbpassword = $dbpassword;
        $this->dbcharset = $dbcharset;
        $this->pconnect = $pconnect;
        $this->tablepre = $tablepre;
        $this->dbconn = $pconnect == 0 ? @mysql_connect($dbhost, $dbuser, $dbpassword, TRUE) : @mysql_pconnect($dbhost, $dbuser, $dbpassword);
		if (!$this->dbconn || mysql_errno($this->dbconn) != 0) {
		    $this->halt('Can\'t connect to MySQL server');
		}
		if (version_compare($this->version(), '4.1.0', '>=') && $dbcharset) {
			mysql_query("SET character_set_connection=" . $dbcharset . ",character_set_results=" . $dbcharset . ",character_set_client=binary", $this->dbconn);
		}
		if (version_compare($this->version(), '5.0.2', '>=')) {
			mysql_query("SET sql_mode=''", $this->dbconn);
		}
		if ($dbname && !@mysql_select_db($dbname, $this->dbconn)) {
			$this->halt('Can\'t use database');
		}
    }
    
    function query($sql, $method = '') {
        if ($method == 'UNBUFFERED' && function_exists('mysql_unbuffered_query')) {
			$query = @mysql_unbuffered_query($sql, $this->dbconn);
		} else {
			$query = @mysql_query($sql, $this->dbconn);
		}
        if (in_array($this->errno(), array(2006, 2013)) && empty($query) && $this->pconnect == 0 && !defined('SQLQUERY_RETRY')) {
			define('SQLQUERY_RETRY', TRUE);
			@mysql_close($this->dbconn);
			sleep(2);
			$this->connect($this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname, $this->pconnect, $this->dbcharset, $this->tablepre);
			$query = $this->query($sql, $method);
		}
		if(!$query && $method != 'SILENT') {
			$this->halt('MySQL Query Error');
		}
		$this->querynum++;
		return $query;
	}
    
    function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function result_first($sql) {
        return $this->result($this->query($sql), 0);
	}

	function fetch_first($sql) {
		return $this->fetch_array($this->query($sql));
	}
    
    function fetch_all($sql, $id = '') {
		$arr = array();
		$query = $this->query($sql);
		while($data = $this->fetch_array($query)) {
			$id ? $arr[$data[$id]] = $data : $arr[] = $data;
		}
		return $arr;
	}
    
    function affected_rows() {
		return mysql_affected_rows($this->link);
	}
    
    function fetch_row($query) {
		return mysql_fetch_row($query);
	}
    
    function result($query, $row = 0) {
		$query = @mysql_result($query, $row);
		return $query;
	}

	function num_rows($query) {
		$query = mysql_num_rows($query);
		return $query;
	}
    
    function insert_id() {
		return ($id = mysql_insert_id($this->dbconn)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_fields($query) {
		return mysql_fetch_field($query);
	}
    
    function error() {
		return (($this->dbconn) ? mysql_error($this->dbconn) : mysql_error());
	}

	function errno() {
		return intval(($this->dbconn) ? mysql_errno($this->dbconn) : mysql_errno());
	}
    
    function version() {
		return mysql_get_server_info($this->dbconn);
	}

	function close() {
		return mysql_close($this->dbconn);
	}
    
    function halt($message){
        $error = mysql_error();
		$errorno = mysql_errno();
        $s = '<h2> <i>MySQL database server fatal error</i> </h2>';
        if($message) {
            $s = "<b>Info:</b> $message<br /><br />";
        }
        $s .= '<b>Error:</b>'.$error.'<br />';
        $s .= '<b>Errno:</b>'.$errorno.'<br />';
        exit($s);
    }
}
?>
