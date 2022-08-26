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
     * ɾ������
     * @param string $table ����
     * @param mixed $condition ����
     * @param int $limit ����ִ����
     * @param int $lp ���ȼ�����
     * @return int ������Ӱ�����
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
     * ����ֶ�����SQL��ѯ����
     * @param array $array Ҫ��ϵ�����
     * @param string $separator �ָ���
     * @return string ������Ϻ��SQL��ѯ��� impload_sql_query
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
     * �õ���ǰ׺�ı���
     * @param string $tablename ����
     * @return string ���ؼ�ǰ׺�ı���
     */
    public static function table($table, $index = 0) {
        if ($index) {
            $table .= "_$index";
        }
        return self::execute('table_name', $table);
    }

    /**
     * ���ݿ��ѯ
     * @param string $sql
     * @param string $type
     * @return resource ���ز�ѯ��ԴID
     */
    public static function query($sql, $type = '') {
        self::check_query($sql);
        return self::execute('query', $sql, $type);
    }

    /**
     * ִ��һ��SQL��䷵����Ӱ�����
     * @param string $sql SQL���
     * @param int $lp ���ȼ����ȣ������ȼ���
     * @return int ������Ӱ�����
     */
    public static function exec($sql, $lp = 1) {
        self::check_query($sql);
        return self::execute('exec', $sql, $lp);
    }

    /**
     * (��)���ؼ�¼���ݼ�
     * @param resource $query SQL��ѯ��ʶ��
     * @return array �ӽ������ȡ��һ����Ϊ��������
     */
    public static function fetch_array($query) {
        return self::execute('fetch_array', $query);
    }

    /**
     * (��)��������������
     * @param resource $query SQL��ѯ��ʶ��
     * @return array �ӽ������ȡ��һ����Ϊö������
     */
    public static function fetch_row($query) {
        return self::execute('fetch_row', $query);
    }

    /**
     * (��)���ص�����¼����
     * @param string $sql SQL��ѯ���
     * @return string ���ص�һ����¼����
     */
    public static function fetch_first($sql) {
        self::check_query($sql);
        return self::execute('fetch_first', $sql);
    }

    /**
     * ���ؽ������һ���ֶε�ֵ
     * @param resource $query SQL��ѯ��ʶ��
     * @param int $row ָ���кţ��кŴ� 0 ��ʼ��
     * @return mixed �����ֶ�ֵ�����ʧ�ܣ��򷵻� false
     */
    public static function result($query, $row = 0) {
        return self::execute('result', $query, $row);
    }

    /**
     * ȡ�õ�һ���������
     * @param string $sql SQL��ѯ���
     * @return mixed ���ؽ�����е�һ����Ԫ������
     */
    public static function result_first($sql) {
        self::check_query($sql);
        return self::execute('result_first', $sql);
    }

    /**
     * ȡ�ý�������е���Ŀ
     * @param resource $query SQL��ѯ��ʶ��
     * @return int ���ؽ�������е���Ŀ
     */
    public static function num_rows($query) {
        return self::execute('num_rows', $query);
    }

    /**
     * ȡ�ý�������ֶε���Ŀ
     * @param resource $query SQL��ѯ��ʶ��
     * @return int ���ؽ�������ֶε���Ŀ
     */
    public static function num_fields($query) {
        return self::execute('num_fields', $query);
    }

    /**
     * �ӽ������ȡ������Ϣ����Ϊ���󷵻�
     * @param resource $query SQL��ѯ��ʶ��
     * @return object ����һ�������ֶ���Ϣ�Ķ���
     */
    public static function fetch_fields($query) {
        return self::execute('fetch_fields', $query);
    }

    /**
     * ȡ�ò�ѯ��Ӱ��ļ�¼����
     * @return int ������Ӱ����е���Ŀ����ѯʧ�ܷ��� -1
     */
    public static function affected_rows() {
        return self::execute('affected_rows');
    }

    /**
     * �ͷŽ���ڴ�
     * @param resource $query SQL��ѯ��ʶ��
     * @return bool ����ɹ����򷵻� true�����ʧ�ܣ��򷵻� false
     */
    public static function free_result($query) {
        return self::execute('free_result', $query);
    }

    /**
     * ȡ�����һ�β����¼��IDֵ
     * @return int �������һ�β����¼��IDֵ
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
     * ��ȡ��ǰ���ݿ��С
     * @return int �������ݿ��С
     */
    public static function size() {
        return self::execute('size');
    }

    /**
     * ��ȡ��һ�����ݿ�����������ı�������Ϣ
     * @return string �����ı�������Ϣ
     */
    public static function error() {
        return self::execute('error');
    }

    /**
     * ��ȡ���ݿ�������Ĵ������
     * @return int ���ش�����Ϣ����
     */
    public static function errno() {
        return self::execute('errno');
    }

    /**
     * ��ȡ���ݿ�������汾��Ϣ
     * @return string �������ݿ�汾��Ϣ
     */
    public static function version() {
        return self::execute('version');
    }

    /**
     * �ر����ݿ�����
     * @return bool ����ɹ��򷵻� TRUE��ʧ���򷵻� FALSE
     */
    public static function close() {
        return self::execute('close');
    }

    /**
     * ת��һ���ַ�������SQL��ѯ
     * @param string $str Ҫת����ַ���
     * @return string ����ת�����ַ���
     */
    public static function escape_string($str) {
        return self::execute('escape_string', $str);
    }
    
    /**
	 * ֹͣSQL������������Ϣ
	 * @param string $message
	 * @param string $sql
	 */
	public static function halt($message = '', $sql = '') {
		return self::execute('halt', $message, $sql);
	}
    
    /**
     * ���� LIMIT ��ѯ���
     * @param string $sql SQL ��ѯ���
     * @param int $offset ��������
     * @param int $start ��ʼ��
     * @return string ����LIMIT ��ѯ���
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
     * ִ�����ݿ��������
     * @param string $command ��������
     * @param mixed $arg1 ����һ
     * @param mixed $arg2 ������
     * @return mixed
     */
    private static function execute($command, $arg1 = '', $arg2 = '') {
        static $db;
        if (empty($db)) $db = self::instance();
        $res = $db->$command($arg1, $arg2);
        return $res;
    }

    /**
     * ʵ�������ݿ����
     * @return object �������ݿ����
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
