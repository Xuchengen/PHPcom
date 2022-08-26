<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : db_mysqli.php    2011-12-8
 */
define('UC_RESULT_TYPE_ASSOC', MYSQLI_ASSOC);
define('UC_RESULT_TYPE_NUM', MYSQLI_NUM);
define('UC_RESULT_TYPE_BOTH', MYSQLI_BOTH);

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
        
        list($dbhost, $dbport) = explode(':', $dbhost . ':3306');
		!$dbport && $dbport = 3306;
		$this->dbconn = mysqli_init();
		mysqli_real_connect($this->dbconn, $dbhost, $dbuser, $dbpass, FALSE, $dbport);
		mysqli_errno($this->dbconn) != 0 && $this->halt('Can\'t connect to MySQL server');
        if(!$this->dbconn){
           $this->halt('Can\'t connect to MySQL server'); 
        }else{
           if (version_compare($this->version(), '4.1.0', '>=') && $dbcharset) {
                mysqli_query("SET character_set_connection=" . $dbcharset . ",character_set_results=" . $dbcharset . ",character_set_client=binary", $this->dbconn);
            }
            if (version_compare($this->version(), '5.0.2', '>=')) {
                mysqli_query("SET sql_mode=''", $this->dbconn);
            }
            if ($dbname && !@mysqli_select_db($this->dbconn, $dbname)) {
                $this->halt('Can\'t use database');
            }
        }
    }
    
    function query($sql, $method = '') {
		$query = @mysqli_query($this->dbconn, $sql, ($method ? MYSQLI_USE_RESULT : MYSQLI_STORE_RESULT));
		if (in_array($this->errno(), array(2006, 2013)) && empty($query) && $this->pconnect == 0 && !defined('SQLQUERY_RETRY')) {
			define('SQLQUERY_RETRY', TRUE);
			@mysqli_close($this->dbconn);
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
    
    function fetch_array($query, $result_type = MYSQLI_ASSOC) {
		return mysqli_fetch_array($query, $result_type);
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
		return mysqli_affected_rows($this->link);
	}
    
    function fetch_row($query) {
		return mysqli_fetch_row($query);
	}
    
    function result($query, $row = 0) {
		$rt = &$this->fetch_array($query, MYSQLI_NUM);
		return isset($rt[$row]) ? $rt[$row] : FALSE;
	}
    
    function num_rows($query) {
		$query = mysqli_num_rows($query);
		return $query;
	}
    
    function insert_id() {
		return ($id = mysqli_insert_id($this->dbconn)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
	}

	function fetch_fields($query) {
		return mysqli_fetch_field($query);
	}
    
    function error() {
		return (($this->dbconn) ? mysqli_error($this->dbconn) : mysqli_error());
	}

	function errno() {
		return intval(($this->dbconn) ? mysqli_errno($this->dbconn) : mysqli_errno());
	}
    
    function version() {
		return mysqli_get_server_info($this->dbconn);
	}

	function close() {
		return mysqli_close($this->dbconn);
	}
    
    function halt($message){
        $error = mysqli_error();
		$errorno = mysqli_errno();
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
