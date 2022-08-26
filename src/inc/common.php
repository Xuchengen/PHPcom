<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : common.php    2011-7-6 0:04:34
 */
define('PHPCOM_PATH', realpath(dirname(__FILE__) . '/..'));
define('PHPCOM_ROOT', dirname(PHPCOM_PATH));
define('TEMPLATE_DIR', 'templates');
define('PATH_TEMPLATE', PHPCOM_ROOT . '/' . TEMPLATE_DIR);
define('HOOK_PATH', PHPCOM_PATH . '/hook/');
define('IN_PHPCOM', TRUE);

require PHPCOM_PATH . '/class/autoload.php';
require PHPCOM_PATH . '/class/phpcom.php';
coreAutoload::registerAutoload();
phpcomAutoload::registerAutoload();
$phpcom = phpcom_init::instance();
?>
