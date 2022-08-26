<?php
/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : misc.php    2011-6-28 11:35:45
 */
define('CURRENT_SCRIPT', 'misc');

$fileDir = dirname(dirname(__FILE__));
require $fileDir . '/src/inc/common.php';
$start_time = microtime(true);
$phpcom->init();

Application::initialize();
Application::set('page_start_time', $start_time);

$control = new Controller(new Dependencies_WebPage(), 'Misc');
$control->run();
?>
