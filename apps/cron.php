<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : cron.php  2012-7-29
 */
define('CURRENT_SCRIPT', 'cron');
$fileDir = dirname(dirname(__FILE__));
require $fileDir.'/src/inc/common.php';
$start_time = microtime(true);
$phpcom->init();

Application::initialize();
Application::set('page_start_time', $start_time);

Cron::runOutput();
?>