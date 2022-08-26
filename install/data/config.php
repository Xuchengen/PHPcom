<?php
!defined('IN_PHPCOM') && exit('Access denied');

$_config = array();

/* ---------------------------  CONFIG DEBUG  --------------------------- */
$_config['debug'] = '0';

/* --------------------------  CONFIG SERVER  --------------------------- */
$_config['server'] = 1;

/* ----------------------------  CONFIG DB  ----------------------------- */
$_config['db']['database'] = 'mysql';
$_config['db']['type'] = 'mysql';
$_config['db']['engine'] = 'MyISAM';
$_config['db']['1']['dbhost'] = 'localhost';
$_config['db']['1']['dbuser'] = 'root';
$_config['db']['1']['dbpass'] = '';
$_config['db']['1']['dbname'] = 'phpcom';
$_config['db']['1']['pconnect'] = '0';
$_config['db']['1']['charset'] = 'utf8';
$_config['db']['1']['tablepre'] = 'pc_';

/* ---------------------------  CONFIG CACHE  --------------------------- */
$_config['cache']['type'] = 'sql';
$_config['cache']['prefix'] = 'OAVa3w_';
$_config['cache']['eaccelerator'] = '1';
$_config['cache']['xcache'] = '0';
$_config['cache']['apc'] = '1';
$_config['cache']['memcache']['host'] = '';
$_config['cache']['memcache']['port'] = 11211;
$_config['cache']['memcache']['pconnect'] = 1;
$_config['cache']['memcache']['timeout'] = 1;
$_config['cache']['redis']['host'] = '';
$_config['cache']['redis']['port'] = 6379;
$_config['cache']['redis']['auth'] = '';
$_config['cache']['redis']['pconnect'] = 1;
$_config['cache']['redis']['timeout'] = '0';
$_config['cache']['redis']['serialized'] = 1;
$_config['cache']['enabled'] = '0';
$_config['cache']['ttl'] = '300';

/* -------------------------  CONFIG DOWNLOAD  -------------------------- */
$_config['download']['readmode'] = 2;
$_config['download']['xsendfile']['type'] = '0';
$_config['download']['xsendfile']['dir'] = '/attachment/';

/* --------------------------  CONFIG COOKIE  --------------------------- */
$_config['cookie']['prefix'] = 'sKQYlYPW_';
$_config['cookie']['domain'] = '';
$_config['cookie']['path'] = '/';

/* --------------------------  CONFIG OUTPUT  --------------------------- */
$_config['output']['charset'] = 'utf-8';
$_config['output']['forceheader'] = '1';
$_config['output']['gzip'] = '0';
$_config['output']['language'] = 'zh-CN';
$_config['output']['ajaxvalidate'] = '0';

/* -------------------------  CONFIG SECURITY  -------------------------- */
$_config['security']['key'] = 'Bi415UlPoA5imJaL';
$_config['security']['urlxssdefend'] = '1';
$_config['security']['attackevasive'] = '0';
$_config['security']['query']['status'] = 1;
$_config['security']['query']['likehex'] = 1;
$_config['security']['query']['afullnote'] = 1;

/* --------------------------  CONFIG ADMINCP  -------------------------- */
$_config['template']['encoding'] = '';

/* --------------------------  CONFIG ADMINCP  -------------------------- */
$_config['admincp']['founder'] = '1';
$_config['admincp']['timeout'] = 18000;
$_config['admincp']['checkip'] = 0;
$_config['admincp']['closed'] = '0';
$_config['admincp']['autoback'] = '0';
$_config['admincp']['backtime'] = 3000;
$_config['admincp']['forbidpost'] = '0';
$_config['admincp']['pagesize'] = 30;
$_config['admincp']['pagenum'] = 10;
$_config['admincp']['pageinput'] = 1;
$_config['admincp']['pagestats'] = 1;
$_config['admincp']['forceanswer'] = '0';
$_config['admincp']['template'] = '1';
$_config['admincp']['runquery'] = '1';
$_config['admincp']['script'] = 'admin.php';

/* -----------------------------  THE END  ------------------------------ */

?>