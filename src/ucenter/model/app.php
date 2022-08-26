<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : app.php    2011-12-30
 */
!defined('IN_UCENTER') && exit('Access Denied');

class appmodel{
    var $db;
    var $base;

    function __construct(&$base) {
        $this->appmodel($base);
    }

    function appmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }
    
    function app_list($appid = NULL){
        static $list = null;
        if (!isset($list)) {
			$list = array();
			$query = $this->db->query("SELECT * FROM " . UC_DB_TABLEPRE . "ucapps");
			while ($row = $this->db->fetch_array($query)) {
				$list[$row['id']] = $row;
			}
		}
		return isset($appid) ? $list[$appid] : $list;
    }
}
?>
