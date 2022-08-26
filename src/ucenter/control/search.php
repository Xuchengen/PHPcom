<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : search.php    2011-12-9
 */
!defined('IN_UCENTER') && exit('Access Denied');
class searchcontrol extends ucenterbase {
    function __construct() {
        $this->searchcontrol();
    }

    function searchcontrol() {
        parent::__construct();
        $this->load('search');
    }
    
    function getcount($condition) {
        return $_ENV['search']->getcount($condition);
    }
    
    function searchresult($condition, $pagesize, $offset){
        $arruid = $_ENV['search']->search($condition, $pagesize, $offset);
        return $_ENV['search']->search_result($arruid);
    }
    
    function getuid($condition, $limit = 2000, $offset = 0){
        return $_ENV['search']->search($condition, $limit, $offset);
    }
    
    function getresult($arruid){
        return $_ENV['search']->search_result($arruid);
    }
    
    function getmember($arruid){
        return $_ENV['search']->getmember($arruid);
    }
}
?>
