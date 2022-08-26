<?php
/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : ajax.php    2011-6-21 12:03:39
 */
define('CURRENT_SCRIPT', 'ajax');
$fileDir = dirname(dirname(__FILE__));
require $fileDir . '/src/inc/common.php';
$start_time = microtime(true);
$phpcom->init();

Application::initialize();
Application::set('page_start_time', $start_time);

$control = new Controller(new Dependencies_WebPage(), 'Ajax');
$control->run();
?>
