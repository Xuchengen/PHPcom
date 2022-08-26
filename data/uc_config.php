<?php
/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : uc_config.php    2011-12-8
 */
define('UC_SERVER_APP', 1);
define('UC_CONNECT_TYPE', 'mysql');
define('UC_DB_HOSTNAME', phpcom::$config['db']['1']['dbhost']);
define('UC_DB_USERNAME', phpcom::$config['db']['1']['dbuser']);
define('UC_DB_PASSWORD', phpcom::$config['db']['1']['dbpass']);
define('UC_DB_DATABASE', phpcom::$config['db']['1']['dbname']);
define('UC_DB_PCONNECT', phpcom::$config['db']['1']['pconnect']);
define('UC_DB_CHARSET', phpcom::$config['db']['1']['charset']);
define('UC_DB_TABLEPRE', phpcom::$config['db']['1']['tablepre']);


define('UC_CHARSET', phpcom::$config['output']['charset']);
define('UC_API_KEY', '');
define('UC_API_URL','');
define('UC_API_APPID', 1);
define('UC_API_IP', '');
?>
