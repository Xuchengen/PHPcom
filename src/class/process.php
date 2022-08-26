<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : process.php    2012-1-9
 */
!defined('IN_PHPCOM') && exit('Access denied');

class process {

    public static function locked($process, $ttl = 0) {
        $ttl = $ttl < 1 ? 600 : intval($ttl);
        if (process::status('get', $process)) {
            return true;
        } else {
            return process::find($process, $ttl);
        }
    }

    public static function unlock($process) {
        process::status('rm', $process);
        process::storage('rm', $process);
    }

    public static function status($action, $process) {
        static $processlist = array();
        switch ($action) {
            case 'set' : $processlist[$process] = TRUE;
                break;
            case 'get' : return !empty($processlist[$process]);
                break;
            case 'rm' : $processlist[$process] = NULL;
                break;
            case 'clear' : $processlist = array();
                break;
        }
        return TRUE;
    }

    public static function find($name, $ttl) {
        if (!process::storage('get', $name)) {
            process::storage('set', $name, $ttl);
            $ret = FALSE;
        } else {
            $ret = TRUE;
        }
        process::status('set', $name);
        return $ret;
    }

    public static function storage($cmd, $name, $ttl = 0) {
        static $allowmemory;
        if ($allowmemory === NULL) {
            $allowmemory = phpcom_cache::memory('check') == 'memcache';
        }
        if ($allowmemory) {
            return process::process_memory($cmd, $name, $ttl);
        } else {
            return process::process_dbcache($cmd, $name, $ttl);
        }
    }

    public static function process_memory($cmd, $name, $ttl = 0) {
        return phpcom_cache::memory($cmd, 'processes_lock_' . $name, time(), $ttl);
    }

    public static function process_dbcache($cmd, $name, $ttl = 0) {
        $ret = '';
        $name = addslashes($name);
        switch ($cmd) {
            case 'set': $ret = DB::insert('process', array('processid' => $name, 'expiration' => time() + $ttl), FALSE, TRUE);
                break;
            case 'get':
                $ret = DB::fetch_first("SELECT * FROM " . DB::table('process') . " WHERE processid='$name'");
                if (empty($ret) || $ret['expiration'] < time()) {
                    $ret = false;
                } else {
                    $ret = true;
                }
                break;
            case 'rm':
            case 'del': $ret = DB::delete('process', "processid='$name' OR expiration<" . time());
                break;
        }
        return $ret;
    }

}

?>
