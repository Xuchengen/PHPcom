<?php

/**
 *
 *  Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 *  Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 *  Description : This software is the proprietary information of phpcom.cn.
 *  This File   : main.php
 *
 */
!defined('IN_ADMINCP') && exit('Access denied');
$serveros = get_serveros();
$phpversion = PHP_VERSION;
$servername = $_SERVER['SERVER_NAME'];
$serverip = getserverip();
$serversoft = $_SERVER['SERVER_SOFTWARE'];
$physicalpath = $_SERVER['DOCUMENT_ROOT'];
$currentdb = strtoupper(phpcom::$config['db']['database']);
$maxpostsize = getcfgvar("post_max_size");
$maxupsize = getcfgvar("upload_max_filesize");
$maxexectime = getcfgvar("max_execution_time") . 's';
$servertime = date('r');
$dbversion = DB::version();
$dbsize = '';
include_once PHPCOM_PATH . '/phpcom_version.php';
$version = PHPCOM_VERSION;
phpcom::$G['lang']['admin'] = 'main';
admin_header();
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('index');
$adminhtml->table_header('index_info_title', 2);
$adminhtml->table_td(array(array('index_info_license', array(
		'sitename' => phpcom::$setting['webname'],
		'version' => $version,
		'charset' => strtoupper(phpcom::$config['output']['charset'])), 'colspan="2"')), NULL, FALSE, NULL, NULL, FALSE);
$adminhtml->table_td(array(
		array('index_info_server', array('servername' => $servername, 'serverip' => $serverip), 'width="50%"'),
		array('index_info_database', array('currentdb' => $currentdb, 'dbversion' => $dbversion, 'dbsize' => $dbsize), 'width="50%"')
), NULL, FALSE, NULL, NULL, FALSE);
$adminhtml->table_td(array(
		array('index_info_serversoft', array('serversoft' => $serversoft)),
		array('index_info_phpversion', array('phpversion' => $phpversion))
), NULL, FALSE, NULL, NULL, FALSE);
$adminhtml->table_td(array(
		array('index_info_serveros', array('serveros' => $serveros)),
		array('index_info_physicalpath', array('physicalpath' => $physicalpath))
), NULL, FALSE, NULL, NULL, FALSE);
$adminhtml->table_td(array(
		array('index_info_maxpostsize', array('maxpostsize' => $maxpostsize)),
		array('index_info_maxupsize', array('maxupsize' => $maxupsize))
), NULL, FALSE, NULL, NULL, FALSE);
$adminhtml->table_td(array(
		array('index_info_servertime', array('servertime' => $servertime)),
		array('index_info_maxexectime', array('maxexectime' => $maxexectime))
), NULL, FALSE, NULL, NULL, FALSE);
$adminhtml->table_td(array(array('index_pending_matters', '', 'colspan="2"')), NULL, FALSE, NULL, NULL, FALSE);
$adminhtml->table_td(array(array('index_info_sponsors', '', 'colspan="2" id="index_sponsors"')), NULL, FALSE, NULL, NULL, FALSE);
$adminhtml->table_end();
$adminhtml->table_header('index_safety_tips');
$adminhtml->table_td(array(array('index_safety_tips_comments')), NULL, FALSE, NULL, NULL, FALSE);
$adminhtml->table_end();
$adminhtml->table_header('index_phpcom_info', 2);
$adminhtml->table_td(array(
		array('index_phpcom_copyright', '', 'width="10%" noWrap="noWrap"'),
		array('index_phpcom_copyright_comments', '', 'width="90%"')
), NULL, FALSE, NULL, NULL, FALSE);
$adminhtml->table_td(array(
		array('index_phpcom_licence'),
		array('index_phpcom_licence_comments')
), NULL, FALSE, NULL, NULL, FALSE);
$adminhtml->table_td(array(
		array('index_phpcom_contact'),
		array('index_phpcom_contact_comments')
), NULL, FALSE, NULL, NULL, FALSE);
$adminhtml->table_td(array(
		array('index_phpcom_home'),
		array('index_phpcom_home_comments')
), NULL, FALSE, NULL, NULL, FALSE);
$adminhtml->table_end();

admin_footer();

function short_ipv6($ip, $length = 9) {
	if ($length < 1) {
		return '';
	}
	// extend IPv6 addresses
	$blocks = substr_count($ip, ':') + 1;
	if ($blocks < 9) {
		$ip = str_replace('::', ':' . str_repeat('0000:', 9 - $blocks), $ip);
	}
	if ($ip[0] == ':') {
		$ip = '0000' . $ip;
	}
	if ($length < 4) {
		$ip = implode(':', array_slice(explode(':', $ip), 0, 1 + $length));
	}

	return $ip;
}

function get_serveros() {
	$systemversion = explode(" ", $phpuname = php_uname());
	$systeminfo = '';
	switch (PHP_OS) {
		case "Linux":
			$systeminfo = $systemversion[0] . '   ' . $systemversion[2];
			break;
		case "FreeBSD":
			$systeminfo = $systemversion[0] . '   ' . $systemversion[2];
			break;
		case "WINNT":
			if (version_compare(PHP_VERSION, '5.3.7', '<') || !strpos($phpuname, '(')) {
				$systeminfo = $systemversion[0] . '  ' . $systemversion[1] . ' ' . $systemversion[3] . ' ' . $systemversion[4] . ' ' . $systemversion[5];
			} else {
				unset($systemversion);
				$systemversion = preg_split("/[\(\)]+/", $phpuname);
				$systeminfo = str_replace('Service Pack ', 'SP', $systemversion[1]);
			}
			break;
		default:
			$systeminfo = $systemversion[0] . '  ' . $systemversion[1] . ' ' . $systemversion[3] . ' ' . $systemversion[4] . ' ' . $systemversion[5];
			break;
	}
	return $systeminfo;
}

function getcfgvar($varName) {
	switch ($res = get_cfg_var($varName)) {
		case 0:
			return 'NO';
			break;
		case 1:
			return 'YES';
			break;
		default:
			return $res;
			break;
	}
}

function getserverip() {
	if(isset($_SERVER['SERVER_ADDR'])){
		return $_SERVER['SERVER_ADDR'];
	}elseif(isset($_SERVER['LOCAL_ADDR'])){
		return $_SERVER['LOCAL_ADDR'];
	}else{
		return gethostbyname($_SERVER['SERVER_NAME']);
	}
}

function getdbsize(){
	$tablepre = phpcom::$config['db']['1']['tablepre'];
	$dbsize = DB::fetch_first("SELECT
			CONCAT(sum(ROUND(((DATA_LENGTH + INDEX_LENGTH - DATA_FREE) / 1024 / 1024),2)),'MB') AS size
			FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA like '%$tablepre%'");
	return "({$dbsize['size']})";
}
?>
