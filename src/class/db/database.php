<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : database.php    2011-7-5 23:50:19
 */
!defined('IN_PHPCOM') && exit('Access denied');

class DB {
    public static $checkaction = TRUE;
    /**
     * 删除数据
     * @param string $table 表名
     * @param mixed $condition 条件
     * @param int $limit 限制执行数
     * @param int $lp 优先级调度
     * @return int 返回受影响的行
     */
    public static function delete($table, $condition, $index = 0, $limit = 0, $unbuffered = TRUE) {
        if (empty($condition)) {
            $where = '1';
        } elseif (is_array($condition)) {
            $where = self::implode_field_value($condition, ' AND ');
        } else {
            $where = $condition;
        }
        $sql = "DELETE FROM " . self::table($table, $index) . " WHERE $where" . ($limit ? "LIMIT $limit" : '');
        return self::exec($sql, ($unbuffered ? 'UNBUFFERED' : ''));
    }

    public static function update($table, $data, $condition, $index = 0, $unbuffered = FALSE, $low_priority = FALSE) {
        $sql = self::implode_field_value($data);
        $cmd = "UPDATE" . ($low_priority ? ' LOW_PRIORITY' : '');
        $table = self::table($table, $index);
        $where = '';
        if (empty($condition)) {
            $where = '1';
        } elseif (is_array($condition)) {
            $where = self::implode_field_value($condition, ' AND ');
        } else {
            $where = $condition;
        }
        try {
        	return self::exec("$cmd $table SET $sql WHERE $where", ($unbuffered ? 'UNBUFFERED' : ''));
        } catch (Exception $e) {
        	return false;
        }
    }

    public static function insert($table, $data, $return_insert_id = FALSE, $replace = FALSE, $silent = FALSE, $index = 0) {
        $sql = self::implode_field_value($data);
        $cmd = $replace ? 'REPLACE INTO' : 'INSERT INTO';
        $table = self::table($table, $index);
        $silent = $silent ? 'SILENT' : '';
        $return = 0;
        try {
        	$return = self::exec("$cmd $table SET $sql", $silent);
        } catch (Exception $e) {
        	echo $e->getMessage();
        	return false;
        }
        
        return $return_insert_id ? self::insert_id() : $return;
    }

    public static function count($table, $condition, $index = 0) {
        if (empty($condition)) {
            $where = '1';
        } elseif (is_array($condition)) {
            $where = self::implode_field_value($condition, ' AND ');
        } else {
            $where = $condition;
        }
        $table = self::table($table, $index);
        $ret = intval(self::result_first("SELECT COUNT(*) AS num FROM $table WHERE $where"));
        return $ret;
    }

    public static function exists($table, $condition = '', $index = 0) {
        if (empty($condition)) {
            $where = '1';
        } elseif (is_array($condition)) {
            $where = self::implode_field_value($condition, ' AND ');
        } else {
            $where = $condition;
        }
        $table = self::table($table, $index);
        $query = self::query("SELECT * FROM $table WHERE $where LIMIT 1");
        $result = self::affected_rows();
        self::free_result($query);
        return $result ? TRUE : FALSE;
    }
	
    public static function table_exists($tableName, $index = 0) {
    	$table = self::table($tableName, $index);
    	$query = DB::query("SHOW TABLES LIKE '$table'");
    	if(DB::fetch_array($query)){
    		return true;
    	}
    	return false;
    }
    
    public static function column_exists($tableName, $columnName, $index = 0) {
    	$table = self::table($tableName, $index);
    	$columnName = addslashes($columnName);
    	try {
	    	$query = DB::query("DESCRIBE `$table` `$columnName`");
	    	if(DB::fetch_array($query)){
	    		return true;
	    	}
    	} catch (Exception $e) {
    		return false;
    	}
    	return false;
    }
    
    public static function index_exists($tableName, $indexName, $index = 0) {
    	$table = self::table($tableName, $index);
    	$indexName = addslashes($indexName);
    	try {
    		$query = DB::query("SELECT * FROM information_schema.statistics WHERE table_name='$table' AND index_name='$indexName'");
    		if(DB::num_rows($query)){
    			return true;
    		}
    	} catch (Exception $e) {
    		return false;
    	}
    	return false;
    }
    /**
     * 组合字段用于SQL查询操作
     * @param array $array 要组合的数组
     * @param string $separator 分隔符
     * @return string 返回组合后的SQL查询语句 impload_sql_query
     */
    public static function implode_field_value($array, $separator = ',') {
        $sql = $comma = '';
        if (is_array($array)) {
            foreach ($array as $k => $v) {
                $sql .= $comma . "`$k`='$v'";
                $comma = $separator;
            }
        } else {
            $sql = $array;
        }
        return $sql;
    }

    /**
     * 得到加前缀的表名
     * @param string $tablename 表名
     * @return string 返回加前缀的表名
     */
    public static function table($table, $index = 0) {
        if ($index) {
            $table .= "_$index";
        }
        return self::execute('table_name', $table);
    }

    /**
     * 数据库查询
     * @param string $sql
     * @param string $type
     * @return resource 返回查询资源ID
     */
    public static function query($sql, $type = '') {
        self::check_query($sql);
        return self::execute('query', $sql, $type);
    }

    /**
     * 执行一条SQL语句返回受影响的行
     * @param string $sql SQL语句
     * @param int $lp 优先级调度（低优先级）
     * @return int 返回受影响的行
     */
    public static function exec($sql, $lp = 1) {
        self::check_query($sql);
        return self::execute('exec', $sql, $lp);
    }

    /**
     * (读)返回记录数据集
     * @param resource $query SQL查询标识符
     * @return array 从结果集中取得一行作为关联数组
     */
    public static function fetch_array($query) {
        return self::execute('fetch_array', $query);
    }

    /**
     * (读)返回数据行数组
     * @param resource $query SQL查询标识符
     * @return array 从结果集中取得一行作为枚举数组
     */
    public static function fetch_row($query) {
        return self::execute('fetch_row', $query);
    }

    /**
     * (读)返回单条记录数据
     * @param string $sql SQL查询语句
     * @return string 返回第一条记录数据
     */
    public static function fetch_first($sql) {
        self::check_query($sql);
        return self::execute('fetch_first', $sql);
    }

    /**
     * 返回结果集中一个字段的值
     * @param resource $query SQL查询标识符
     * @param int $row 指定行号，行号从 0 开始。
     * @return mixed 返回字段值。如果失败，则返回 false
     */
    public static function result($query, $row = 0) {
        return self::execute('result', $query, $row);
    }

    /**
     * 取得第一条结果数据
     * @param string $sql SQL查询语句
     * @return mixed 返回结果集中第一个单元的内容
     */
    public static function result_first($sql) {
        self::check_query($sql);
        return self::execute('result_first', $sql);
    }

    /**
     * 取得结果集中行的数目
     * @param resource $query SQL查询标识符
     * @return int 返回结果集中行的数目
     */
    public static function num_rows($query) {
        return self::execute('num_rows', $query);
    }

    /**
     * 取得结果集中字段的数目
     * @param resource $query SQL查询标识符
     * @return int 返回结果集中字段的数目
     */
    public static function num_fields($query) {
        return self::execute('num_fields', $query);
    }

    /**
     * 从结果集中取得列信息并作为对象返回
     * @param resource $query SQL查询标识符
     * @return object 返回一个包含字段信息的对象
     */
    public static function fetch_fields($query) {
        return self::execute('fetch_fields', $query);
    }

    /**
     * 取得查询所影响的记录行数
     * @return int 返回受影响的行的数目，查询失败返回 -1
     */
    public static function affected_rows() {
        return self::execute('affected_rows');
    }

    /**
     * 释放结果内存
     * @param resource $query SQL查询标识符
     * @return bool 如果成功，则返回 true，如果失败，则返回 false
     */
    public static function free_result($query) {
        return self::execute('free_result', $query);
    }

    /**
     * 取得最后一次插入记录的ID值
     * @return int 返回最后一次插入记录的ID值
     */
    public static function insert_id() {
        return self::execute('insert_id');
    }
	
    public static function beginTransaction() {
    	return self::execute('beginTransaction');
    }
    
    public static function commit() {
    	return self::execute('commit');
    }
    
    public static function rollBack() {
    	return self::execute('rollBack');
    }
    
    /**
     * 获取当前数据库大小
     * @return int 返回数据库大小
     */
    public static function size() {
        return self::execute('size');
    }

    /**
     * 获取上一个数据库操作产生的文本错误信息
     * @return string 返回文本错误信息
     */
    public static function error() {
        return self::execute('error');
    }

    /**
     * 获取数据库服务器的错误代码
     * @return int 返回错误信息代码
     */
    public static function errno() {
        return self::execute('errno');
    }

    /**
     * 获取数据库服务器版本信息
     * @return string 返回数据库版本信息
     */
    public static function version() {
        return self::execute('version');
    }

    /**
     * 关闭数据库连接
     * @return bool 如果成功则返回 TRUE，失败则返回 FALSE
     */
    public static function close() {
        return self::execute('close');
    }

    /**
     * 转义一个字符串用于SQL查询
     * @param string $str 要转义的字符串
     * @return string 返回转义后的字符串
     */
    public static function escape_string($str) {
        return self::execute('escape_string', $str);
    }
    
    /**
	 * 停止SQL操作并返回消息
	 * @param string $message
	 * @param string $sql
	 */
	public static function halt($message = '', $sql = '') {
		return self::execute('halt', $message, $sql);
	}
    
    /**
     * 构建 LIMIT 查询语句
     * @param string $sql SQL 查询语句
     * @param int $offset 限制行数
     * @param int $start 开始数
     * @return string 返回LIMIT 查询语句
     */
    public static function buildlimit($sql, $offset, $start = 0) {
    	if(empty($offset) && $start == 0) return $sql;
    	if(strpos($offset, ',')){
    		list($start, $offset) =  explode(',', $offset);
    	}
        $sql .=' LIMIT ' . ($start <= 0 ? '' : (int)$start . ',') . abs($offset);
    	
        return $sql;
    }

    /**
     * 执行数据库操作函数
     * @param string $command 方法命令
     * @param mixed $arg1 参数一
     * @param mixed $arg2 参数二
     * @return mixed
     */
    private static function execute($command, $arg1 = '', $arg2 = '') {
        static $db;
        if (empty($db)) $db = self::instance();
        $res = $db->$command($arg1, $arg2);
        return $res;
    }

    /**
     * 实例化数据库对象
     * @return object 返回数据库对象
     */
    public static function &instance() {
        static $db;
        if (empty($db)) {
            $dbclass = 'db_' . phpcom::$config['db']['type'];
            $db = new $dbclass();
        }
        return $db;
    }

    public static function check_query($sql) {
        static $status = NULL, $checkcmd = array('SELECT', 'UPDATE', 'INSERT', 'REPLACE', 'DELETE');
        if ($status === NULL) $status = phpcom::$config['security']['query']['status'];
        if ($status) {
            $cmd = trim(strtoupper(substr($sql, 0, strpos($sql, ' '))));
            if (in_array($cmd, $checkcmd)) {
                $test = self::do_query_safe($sql, $cmd);
                if ($test < 1) self::execute('halt', 'db_security_error', $sql);
            }
        }
    }

    private static function do_query_safe($sql, $cmd = '') {
        static $safeconfig = null;
        if ($safeconfig === null) {
            $safeconfig = array(
                'function' => array('load_file', 'hex', 'substring', 'if', 'ord', 'char'),
                'action' => array('unionall', 'uniondistinct', 'unionselect', 'intooutfile', 'intodumpfile'),
                'note' => array('/*', '*/', '#', '--', '"'),
                'afullnote' => phpcom::$config['security']['query']['afullnote'],
                'likehex' => phpcom::$config['security']['query']['likehex']
            );
        }
        $sql = str_replace(array('\\\\', '\\\'', '\\"', '\'\''), '', $sql);
        $mark = $clean = '';
        if (strpos($sql, '/') === FALSE && strpos($sql, '#') === FALSE && strpos($sql, '-- ') === FALSE) {
            $clean = preg_replace("/'(.+?)'/s", '', $sql);
        } else {
            $len = strlen($sql);
            $mark = $clean = '';
            for ($i = 0; $i < $len; $i++) {
                $str = $sql[$i];
                switch ($str) {
                    case '\'':
                        if (!$mark) {
                            $mark = '\'';
                            $clean .= $str;
                        } elseif ($mark == '\'') {
                            $mark = '';
                        }
                        break;
                    case '/':
                        if (empty($mark) && $sql[$i + 1] == '*') {
                            $mark = '/*';
                            $clean .= $mark;
                            $i++;
                        } elseif ($mark == '/*' && $sql[$i - 1] == '*') {
                            $mark = '';
                            $clean .= '*';
                        }
                        break;
                    case '#':
                        if (empty($mark)) {
                            $mark = $str;
                            $clean .= $str;
                        }
                        break;
                    case "\n":
                        if ($mark == '#' || $mark == '--') {
                            $mark = '';
                        }
                        break;
                    case '-':
                        if (empty($mark) && substr($sql, $i, 3) == '-- ') {
                            $mark = '-- ';
                            $clean .= $mark;
                        }
                        break;

                    default:

                        break;
                }
                $clean .= $mark ? '' : $str;
            }
        }

        $clean = preg_replace("/[^a-z0-9_\-\(\)#\*\/\"]+/is", "", strtolower($clean));

        if ($safeconfig['afullnote']) {
            $clean = str_replace('/**/', '', $clean);
        }

        if (is_array($safeconfig['function'])) {
            foreach ($safeconfig['function'] as $fun) {
                if (strpos($clean, $fun . '(') !== FALSE) return '-1';
            }
        }

        if (is_array($safeconfig['action'])) {
            $actconfig = $safeconfig['action'];
            $offset = 0;
            $cmd == 'SELECT' && $offset = strpos($clean, 'where');
            //$offset === FALSE && array_shift($actconfig);
            if($cmd == 'SELECT' && !self::$checkaction){
                unset($actconfig[0], $actconfig[1]);
            }
            foreach ($actconfig as $action) {
                if (strpos($clean, $action, $offset) !== FALSE) return '-3';
            }
        }

        if ($safeconfig['likehex'] && strpos($clean, 'like0x')) {
            return '-2';
        }

        if (is_array($safeconfig['note'])) {
            foreach ($safeconfig['note'] as $note) {
                if (strpos($clean, $note) !== FALSE) return '-4';
            }
        }


        return 1;
    }

}

?>
