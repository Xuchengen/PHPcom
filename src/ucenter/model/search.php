<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : search.php    2011-12-9
 */
!defined('IN_UCENTER') && exit('Access Denied');

class searchmodel {

    var $db;
    var $base;

    function __construct(&$base) {
        $this->searchmodel($base);
    }

    function searchmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function getfield($fieldid = '') {
        static $fields = array(
            'uid' => 'member', 'username' => 'member', 'groupid' => 'member', 'email' => 'member', 'gender' => 'member', 
            'credits' => 'member', 'regdate' => 'member', 'regip' => 'status', 'status' => 'member', 'emailstatus' => 'member',
            'currency' => 'count', 'prestige' => 'count', 'money' => 'count', 'praise' => 'count','lastvisit' => 'status',
            'logins' => 'count', 'lastip' => 'status', 'threads' => 'count', 'todayattachs' => 'count',
            'realname' => 'info', 'idcard' => 'info', 'company' => 'info', 'address' => 'info',
            'homepage' => 'info', 'qq' => 'info', 'msn' => 'info', 'taobao' => 'info', 'zipcode' => 'info',
            'phone' => 'info', 'mobile' => 'info', 'fax' => 'info', 'usersign' => 'info', 'birthday' => 'info'
        );
        return $fieldid ? $fields[$fieldid] : $fields;
    }

    function getfieldtype($fieldid) {
        static $types = array(
            'uid' => 'int', 'groupid' => 'int', 'adminid' => 'int', 'credits' => 'int',
            'status' => 'int', 'emailstatus' => 'int', 'birthday' => 'string', 'gender' => 'int',
            'logins' => 'int', 'prestige' => 'int', 'currency' => 'int', 'money' => 'int', 'praise' => 'int'
        );
        return $types[$fieldid] ? $types[$fieldid] : 'string';
    }

    function search($condition, $maxrows = 100, $offset = 0) {
        $result = array();
        $sql = $this->bulidsql($condition);
        if ($maxrows) {
            $sql .= " LIMIT $offset, $maxrows";
        }
        $query = $this->db->query($sql);
        while ($value = $this->db->fetch_array($query)) {
            $result[] = intval($value['uid']);
        }
        return $result;
    }

    function search_result($arruid) {
        $result = array();
        $conditions = 'uid IN (' . uc_implodeids($arruid) . ')';
        $SQL = "SELECT uid,username,email,groupid,adminid,status,credits,regdate FROM " . UC_DB_TABLEPRE . "members WHERE $conditions ORDER BY uid DESC";
        $query = $this->db->query($SQL);
        while ($row = $this->db->fetch_array($query)) {
            $result[] = $row;
        }
        return $result;
    }
    
    function getmember($arruid){
        $result = array();
        $conditions = 'uid IN (' . uc_implodeids($arruid) . ')';
        $SQL = "SELECT uid,username,email,groupid,adminid,status,credits,regdate FROM " . UC_DB_TABLEPRE . "members WHERE $conditions AND adminid=0 AND groupid<>1 ORDER BY uid DESC";
        $query = $this->db->query($SQL);
        while ($row = $this->db->fetch_array($query)) {
            $result[] = $row;
        }
        return $result;
    }
    
    function getcount($condition) {
        $count = $this->db->result_first($this->bulidsql($condition, TRUE));
        return intval($count);
    }

    function makehash($condition) {
        return md5(serialize($condition));
    }

    function bulidsql($condition, $counted = FALSE) {
        $tables = $wheres = array();
        $fields = $this->getfield();
        foreach ($fields as $key => $value) {
            $result = array();
            if (isset($condition[$key])) {
                $result = $this->bulidset($key, $condition[$key], $this->getfieldtype($key));
            }
            if ($result) {
                $tables[$result['table']] = true;
                $wheres[] = $result['where'];
            }
        }
        if ($tables && $wheres) {
            $parts = array();
            $table1 = '';
            foreach ($tables as $key => $value) {
                $value = $this->gettable($key);
                $parts[] = "$value as $key";
                if (!$table1) {
                    $table1 = $key;
                } else {
                    $wheres[] = $table1 . '.uid = ' . $key . '.uid';
                }
            }

            $sql = $counted ? 'SELECT COUNT(DISTINCT ' . $table1 . '.uid) as cnt ' : 'SELECT DISTINCT ' . $table1 . '.uid';
            return $sql . ' FROM ' . implode(', ', $parts) . ' WHERE ' . implode(' AND ', $wheres);
        } else {
            $sql = $counted ? 'SELECT COUNT(uid) as cnt ' : 'SELECT uid';
            return $sql . ' FROM ' . UC_DB_TABLEPRE . "members WHERE 1";
        }
    }

    function bulidset($field, $condition, $type = 'string') {
        $result = $values = array();
        $result['table'] = $this->getfield($field);
        if (!$result['table']) {
            return array();
        }
        $field = $result['table'] . '.' . $field;
        $likesearched = $noempty = FALSE;
        if (!is_array($condition)) {
            $condition = explode(',', $condition);
        }
        foreach ($condition as $value) {
            $value = trim($value);
            if ($type == 'int') {
                $value = intval($value);
            } elseif ($type == 'noempty') {
                $noempty = TRUE;
            } elseif (!$likesearched && strpos($value, '*') !== FALSE) {
                $likesearched = TRUE;
            }
            if ($type != 'int')
                $value = addslashes(stripslashes($value));
            if ($value !== null) {
                $values[] = $value;
            }
        }

        if (!$values) {
            return array();
        }

        if ($likesearched) {
            $likes = array();
            foreach ($values as $value) {
                if (strpos($value, '*') !== FALSE) {
                    $value = str_replace('*', '%', $value);
                    $likes[] = "$field LIKE '$value'";
                } else {
                    $likes[] = "$field = '$value'";
                }
            }
            $result['where'] = '(' . implode(' OR ', $likes) . ')';
        } elseif ($noempty) {
            $result['where'] = "$field != ''";
        } elseif (count($values) > 1) {
            $result['where'] = "$field IN ('" . implode("','", $values) . "')";
        } else {
            $result['where'] = "$field = '$values[0]'";
        }
        return $result;
    }

    function gettable($alias) {
        static $mapping = array('member' => 'members', 'count' => 'member_count', 'info' => 'member_info', 'status' => 'member_status','session' => 'session');
        return UC_DB_TABLEPRE . $mapping[$alias];
    }

}

?>
