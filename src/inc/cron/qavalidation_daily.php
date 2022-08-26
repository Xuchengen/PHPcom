<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : qavalidation_daily.php    2012-1-19
 */
!defined('IN_PHPCOM') && exit('Access denied');

if(array_sum(phpcom::$setting['questionstatus'])){
    phpcom_cache::updater('questionset');
}
?>
