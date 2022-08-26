<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : membersearch.php    2011-12-29
 */
!defined('IN_PHPCOM') && exit('Access denied');

class MemberSearch {

    public static function getfield($fieldid = '') {
        static $fields = array(
            'uid' => 'member', 'username' => 'member', 'groupid' => 'member', 'email' => 'member', 'groupexpiry' => 'member',
            'credits' => 'member', 'regdate' => 'member', 'regip' => 'status', 'status' => 'member', 'emailstatus' => 'member',
            'currency' => 'count', 'prestige' => 'count', 'money' => 'count', 'praise' => 'count','lastvisit' => 'status',
            'logins' => 'count', 'lastip' => 'status', 'threads' => 'count', 'todayattachs' => 'count',
            'gender' => 'member', 'realname' => 'info', 'idcard' => 'info', 'company' => 'info', 'address' => 'info',
            'homepage' => 'info', 'qq' => 'info', 'msn' => 'info', 'taobao' => 'info', 'zipcode' => 'info',
            'phone' => 'info', 'mobile' => 'info', 'fax' => 'info', 'usersign' => 'info', 'birthday' => 'info'
        );
        return $fieldid ? $fields[$fieldid] : $fields;
    }

    public static function getfieldtype($fieldid) {
        static $types = array(
            'uid' => 'int', 'groupid' => 'int', 'adminid' => 'int', 'credits' => 'int','groupexpiry' => 'int',
            'status' => 'int', 'emailstatus' => 'int', 'birthday' => 'string', 'gender' => 'int',
            'logins' => 'int', 'prestige' => 'int', 'currency' => 'int', 'money' => 'int', 'praise' => 'int'
        );
        return empty($types[$fieldid]) ? 'string' : $types[$fieldid];
    }

    public static function search($condition, $maxrows = 100, $offset = 0) {
        $result = array();
        $sql = MemberSearch::bulidsql($condition);
        if ($maxrows) {
            $sql .= " LIMIT $offset, $maxrows";
        }
        $query = DB::query($sql);
        while ($value = DB::fetch_array($query)) {
            $result[] = intval($value['uid']);
        }
        return $result;
    }

    public static function searchresult($arruid) {
        $result = array();
        $conditions = 't1.uid IN (' . implodeids($arruid) . ')';
        $SQL = "SELECT t1.uid, t1.username, t1.email, t1.groupid, t1.status, t1.credits, t1.groupexpiry, t1.regdate, t2.logins 
        	FROM " . DB::table('members') . " t1 
        	LEFT JOIN " . DB::table('member_count') . " t2 USING(uid) 
        	WHERE $conditions  ORDER BY t1.uid DESC";
        $query = DB::query($SQL);
        while ($row = DB::fetch_array($query)) {
            $result[] = $row;
        }
        return $result;
    }

    public static function getmember($arruid) {
        $result = array();
        $conditions = 'uid IN (' . implodeids($arruid) . ')';
        $SQL = "SELECT uid,username,email,groupid,status,credits,groupexpiry,regdate FROM " . DB::table('members') . " WHERE $conditions AND groupid>='2' ORDER BY uid DESC";
        $query = DB::query($SQL);
        while ($row = DB::fetch_array($query)) {
            $result[] = $row;
        }
        return $result;
    }

    public static function getcount($condition) {
        $count = DB::result_first(MemberSearch::bulidsql($condition, TRUE));
        return intval($count);
    }

    public static function getuid($condition, $limit = 2000, $offset = 0) {
        return MemberSearch::search($condition, $limit, $offset);
    }

    public static function makehash($condition) {
        return md5(serialize($condition));
    }

    public static function bulidsql($condition, $counted = FALSE) {
        $tables = $wheres = array();
        $fields = MemberSearch::getfield();
        foreach ($fields as $key => $value) {
            $result = array();
            if (isset($condition[$key])) {
                $result = MemberSearch::bulidset($key, $condition[$key], MemberSearch::getfieldtype($key));
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
                $value = MemberSearch::gettable($key);
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
            return $sql . ' FROM ' . DB::table('members') . " WHERE 1 ORDER BY uid DESC";
        }
    }

    public static function bulidset($field, $condition, $type = 'string') {
        $result = $values = array();
        $result['table'] = MemberSearch::getfield($field);
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

    public static function gettable($alias) {
        static $mapping = array('member' => 'members', 'count' => 'member_count', 'info' => 'member_info', 'status' => 'member_status','session' => 'session');
        return DB::table($mapping[$alias]);
    }
    
}

?>
