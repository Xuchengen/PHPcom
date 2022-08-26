<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : count.php  2012-10-8
 */
define('CURRENT_SCRIPT', 'count');
$fileDir = dirname(dirname(__FILE__));
require $fileDir.'/src/inc/common.php';
$start_time = microtime(true);
$phpcom->init();

Application::initialize();
Application::set('page_start_time', $start_time);

Counts::runOutput();
?>