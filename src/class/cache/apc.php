<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : apc.php    2011-7-5 23:41:21
 */
!defined('IN_PHPCOM') && exit('Access denied');

class cache_apc implements cache_interface 
{

    public function __construct($config = null)
    {
        if (is_array($config) && count($config))
            $this->init($config);
    }

    public function init($config) 
    {
        unset($config);
    }

    public function get($key) 
    {
        return apc_fetch($key);
    }

    public function getMulti($keys) {
    	return apc_fetch($keys);
    }
    
    public function set($key, $value, $ttl = 2592000) {
        return apc_store($key, $value, $ttl);
    }

    public function del($key) 
    {
        return apc_delete($key);
    }

    public function clear() 
    {
        return apc_clear_cache('user');
    }
    
    public function clean()
    {
    	return $this->clear();
    }

    public function inc($key, $step = 1) {
    	return apc_inc($key, $step) !== false ? apc_fetch($key) : false;
    }
    
    public function dec($key, $step = 1) {
    	return apc_dec($key, $step) !== false ? apc_fetch($key) : false;
    }
}