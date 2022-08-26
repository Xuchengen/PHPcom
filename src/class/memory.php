<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : memory.php    2011-7-5 23:39:21
 */
!defined('IN_PHPCOM') && exit('Access denied');

class phpcom_memory {

    private $config = array();
    private $extension = array();
    private $instance = null;
    private $prefix, $keys;
    public $enabled = false;
    public $type = null;

    public function __construct($config = false) 
    {
        $this->extension['apc'] = function_exists('apc_cache_info') && @apc_cache_info();
        $this->extension['eaccelerator'] = function_exists('eaccelerator_get');
        $this->extension['xcache'] = function_exists('xcache_get');
        $this->extension['memcache'] = extension_loaded('memcache');
        $this->extension['redis'] = extension_loaded('redis');
        $this->extension['wincache'] = function_exists('wincache_ucache_meminfo') && wincache_ucache_meminfo();

        if (is_array($config) && count($config)) {
            $this->init($config);
        }
    }

    public function init($config) 
    {
        $this->config = $config;
        $this->prefix = empty($config['prefix']) ? substr(md5($_SERVER['HTTP_HOST']), 0, 6) . '_' : $config['prefix'];
        $this->keys = array();
		
        if ($this->extension['redis'] && !empty($config['redis']['host'])) {
        	$this->instance = new cache_redis();
        	$this->instance->init($this->config['redis']);
        	if (!$this->instance->enabled) {
        		$this->instance = NULL;
        	}
        }
        if ($this->instance === NULL && $this->extension['memcache'] && !empty($config['memcache']['host'])) {
            $this->instance = new cache_memcache();
            $this->instance->init($this->config['memcache']);
            if (!$this->instance->enabled) {
                $this->instance = NULL;
            }
        }
		
        foreach(array('apc', 'eaccelerator', 'xcache', 'wincache') as $cache) {
        	if(!is_object($this->instance) && $this->extension[$cache] && !empty($config[$cache])) {
        		if($cache == 'xcache' && !ini_get('xcache.var_size')) continue;
        		if($cache == 'apc' && !ini_get('apc.enabled')) continue;
        		$class_name = 'cache_'.$cache;
        		$this->instance = new $class_name();
        		$this->instance->init(null);
        	}
        }

        if (is_object($this->instance)) {
            $this->enabled = TRUE;
            $this->type = str_replace('cache_', '', get_class($this->instance));
            $this->keys = $this->get('cached_system_keys');
            $this->keys = !is_array($this->keys) ? array() : $this->keys;
        }
    }

    public function get($key) 
    {
        $ret = NULL;
        if ($this->enabled) {
            $ret = $this->instance->get($this->_key($key));
            if (!is_array($ret)) {
                $ret = NULL;
                if (array_key_exists($key, $this->keys)) {
                    unset($this->keys[$key]);
                    $this->instance->set($this->_key('cached_system_keys'), array($this->keys));
                }
            } else {
                return $ret[0];
            }
        }
        return $ret;
    }

    public function set($key, $value, $ttl = 0) 
    {
        $ret = NULL;
        if ($this->enabled) {
            $ret = $this->instance->set($this->_key($key), array($value), $ttl);
            if ($ret) {
                $this->keys[$key] = true;
                $this->instance->set($this->_key('cached_system_keys'), array($this->keys));
            }
        }
        return $ret;
    }

    public function del($key) 
    {
        $ret = NULL;
        if ($this->enabled) {
            $ret = $this->instance->del($this->_key($key));
            unset($this->keys[$key]);
            $this->instance->set($this->_key('cached_system_keys'), array($this->keys));
        }
        return $ret;
    }

    public function clear() 
    {
        if ($this->enabled && is_array($this->keys)) {
            $this->keys['cached_system_keys'] = true;
            foreach ($this->keys as $k => $v) {
                $this->instance->del($this->_key($k));
            }
        }
        $this->keys = array();
        return true;
    }
	
    public function inc($key, $step = 1) {
    	static $hasinc = null;
    	$ret = false;
    	if($this->enabled) {
    		if(!isset($hasinc)) $hasinc = method_exists($this->instance, 'inc');
    		if($hasinc) {
    			$ret = $this->instance->inc($this->_key($key), $step);
    		} else {
    			if(($data = $this->instance->get($key)) !== false) {
    				$ret = ($this->instance->set($key, $data + ($step)) !== false ? $this->instance->get($key) : false);
    			}
    		}
    	}
    	return $ret;
    }
    
    public function dec($key, $step = 1) {
    	static $hasdec = null;
    	$ret = false;
    	if($this->enabled) {
    		if(!isset($hasdec)) $hasdec = method_exists($this->instance, 'dec');
    		if($hasdec) {
    			$ret = $this->instance->dec($this->_key($key), $step);
    		} else {
    			if(($data = $this->instance->get($key)) !== false) {
    				$ret = ($this->instance->set($key, $data - ($step)) !== false ? $this->instance->get($key) : false);
    			}
    		}
    	}
    	return $ret;
    }
    
    private function _key($str) 
    {
        return ($this->prefix) . $str;
    }

}

?>
