<?php
/**
 * Copyright (c) 2010-2012 phpmain.com - All rights reserved.
 * Our Website : www.phpmain.com www.phpmain.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPMain.
 * This File   : index.php  2012-4-6
 */
error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_time_limit(1000);
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
	set_magic_quotes_runtime(0);
}
ini_set('magic_quotes_runtime', '0');
define('MAGIC_QUOTES_GPC', function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc());
define('IN_PHPCOM', TRUE);
define('INSTALL_PATH', dirname(__FILE__). DIRECTORY_SEPARATOR);
define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'). DIRECTORY_SEPARATOR);
define('CHARSET', 'utf-8');
define('DBCHARSET', 'utf8');
define('DBENGINE_TYPE', 'MyISAM');
define('DEFAULT_TABLEPRE', 'pc_');
define('CONFIG_FILE', 'data/config.php');
@header("Content-Type:text/html;charset=" . CHARSET);
$version = '1.5.0';
require ROOT_PATH.'install/inc/function.php';
require ROOT_PATH.'install/inc/language.php';
require ROOT_PATH.'install/inc/common.php';
require ROOT_PATH.'install/inc/mysql.php';
timezone_set();
ob_start();
if(!MAGIC_QUOTES_GPC){
	$_GET = add_slashes($_GET);
	$_POST = add_slashes($_POST);
}
$step = intval($_GET['step']);
$lockfile = ROOT_PATH . 'data/install.lock';
if(file_exists($lockfile)) {
	show_message('install_locked');
} elseif(!class_exists('dbmysql')) {
	show_message('database_nonexistent');
}

show_header();
if($step == 1){
	show_nav();
	$s = show_server_info();
	$s .= show_php_function();
	$s .= show_dirfile_info();
	show_body($s);
}elseif($step == 2){
	show_nav();
	$warnfunc = array();
	foreach($func_items as $funcname){
		if($funcname != 'mysqli_connect' && !function_exists($funcname)){
			$warnfunc[] = "warning_$funcname";
		}
	}
	if($warnfunc){
		show_message('undefine_function', $warnfunc);
	}
	$defconfig = getwebconfig();
	$db_items = array('dbhost' => array('value' => $defconfig['db']['1']['dbhost'], 'error' => 0),
			'dbname' => array('value' => $defconfig['db']['1']['dbname'], 'error' => 0),
			'dbuser' => array('value' => $defconfig['db']['1']['dbuser'], 'error' => 0),
			'dbpass' => array('value' => $defconfig['db']['1']['dbpass'], 'error' => 0),
			'tablepre' => array('value' => $defconfig['db']['1']['tablepre'], 'error' => 0),
			/*'dbcharset' => array('value' => array(
					'gbk' => $defconfig['db']['1']['charset'],
					'utf8' => $defconfig['db']['1']['charset']
			), 'error' => 0, 'type' => 'radio'),*/
			'dbengine' => array('value' => array(
					'myisam' => empty($defconfig['db']['engine']) ? 'MyISAM' : trim($defconfig['db']['engine']),
					//'innodb' => 'MyISAM'
			), 'error' => 0, 'type' => 'radio')
		);
	$s = show_database_info($db_items);
	$scriptname = dirname(getscriptname());
	$instdir = substr($scriptname, 0, strrpos($scriptname, '/') + 1);
	
	$website = trim('http://' . $_SERVER['HTTP_HOST'], '/\ ');
	$admin_items = array(
			'username' => array('value' => 'admin', 'error' => 0),
			'password' => array('value' => '', 'error' => 0),
			'password2' => array('value' => '', 'error' => 0),
			'email' => array('value' => 'admin@domain.com', 'error' => 0),
			'webname' => array('value' => 'PHPcom', 'error' => 0),
			'website' => array('value' => $website, 'error' => 0)
		);
	$s .= show_admin_info($admin_items);
	show_body($s);
}elseif($step == 3){
	$submitted = TRUE;
	if($_SERVER['REQUEST_METHOD'] == 'POST'){
		$dbconfig = $_POST['dbconfig'];
		$tablepre = trim($dbconfig['tablepre'], "`-_ \r\n\t"). '_';
		$db_items = array('dbhost' => array(), 'dbname' => array(), 'dbuser' => array(), 'dbpass' => array(), 'tablepre' => array());
		foreach(array('dbhost', 'dbname', 'dbuser', 'tablepre') as $key){
			$db_items[$key]['value'] = $dbconfig[$key];
			$db_items[$key]['error'] = $dbconfig[$key] ? 0 : 'config_'.$key.'_invalid';
			if(empty($dbconfig[$key])){
				$submitted = FALSE;
			}
		}
		$adminuser = $_POST['adminuser'];
		$username = trim($adminuser['username']);
		$password = trim($adminuser['password']);
		$email = trim($adminuser['email']);
		$webname = trim($adminuser['webname']);
		$website = trim($adminuser['website'], " \r\n\t\\/");
		$admin_items = array('username' => array(), 'password' => array(), 'password2' => array(), 'email' => array());
		foreach(array('username', 'password', 'password2', 'email', 'webname', 'website') as $key){
			$admin_items[$key]['value'] = $adminuser[$key];
			$admin_items[$key]['error'] = $adminuser[$key] ? 0 : 'config_'.$key.'_invalid';
			if($key == 'password2'){
				if($adminuser['password2'] != $adminuser['password']){
					$admin_items['password2']['error'] = 'config_password2_invalid';
				}
			}
			if(empty($adminuser[$key])){
				$submitted = FALSE;
			}
		}
		if($submitted){
			$authkey = random();
			$config = getwebconfig();
			if(extension_loaded('pdo_mysql')){
				$config['db']['type'] = 'pdomysql';
			}elseif(function_exists('mysqli_connect')){
				$config['db']['type'] = 'mysqli';
			}else{
				$config['db']['type'] = 'mysql';
			}
			$dbconfig['dbcharset'] = empty($dbconfig['dbcharset']) ? DBCHARSET : trim($dbconfig['dbcharset']);
			$dbconfig['dbengine'] = empty($dbconfig['dbengine']) ? DBENGINE_TYPE : trim($dbconfig['dbengine']);
			$dbconfig['dbengine'] = stricmp(trim($dbconfig['dbengine']), array('MyISAM', 'InnoDB', 'Aria'), true, DBENGINE_TYPE);
			$config['db']['engine'] = str_replace(array('myisam', 'innodb', 'aria'), array('MyISAM', 'InnoDB', 'Aria'), $dbconfig['dbengine']);
			$config['db']['1']['dbhost'] = $dbconfig['dbhost'];
			$config['db']['1']['dbuser'] = $dbconfig['dbuser'];
			$config['db']['1']['dbpass'] = $dbconfig['dbpass'];
			$config['db']['1']['dbname'] = $dbconfig['dbname'];
			$config['db']['1']['charset'] = $dbconfig['dbcharset'];
			$config['db']['1']['tablepre'] = $tablepre;
			$config['db']['1']['pconnect'] = '0';
			$config['cache']['prefix'] = random(6).'_';
			$config['cookie']['prefix'] = random(8).'_';
			$config['security']['key'] = $authkey;
			$config['output']['charset'] = strcasecmp($dbconfig['dbcharset'], 'gbk') ? 'utf-8' : 'gbk';
			$config['admincp']['founder'] = '1';
			$filename = ROOT_PATH . CONFIG_FILE;
			save_webconfig($filename, $config);
			
			$dbname = $dbconfig['dbname'];
			$tablepre = $dbconfig['tablepre'];
			$dbcharset = $dbconfig['dbcharset'];
			$dbengine = $dbconfig['dbengine'];
			$link = @mysql_connect($dbconfig['dbhost'], $dbconfig['dbuser'], $dbconfig['dbpass']);
			if(!$link) {
				$errno = @mysql_errno($link);
				$error = @mysql_error($link);
				if($errno == 1045) {
					show_message('database_errno_1045');
				} elseif($errno == 2003) {
					show_message('database_errno_2003');
				} else {
					show_message('database_connect_error');
				}
			}
			mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET $dbcharset", $link);
	
			if(mysql_errno()) {
				show_message('database_errno_1044');
			}
			mysql_close($link);
			show_nav();
			show_body('<div id="notice"></div>');
			show_button();
			flush();
			ob_flush();
			$db = new dbmysql($config['db']['1']);
			$sqlfile = ROOT_PATH. './install/data/install.sql';
			$installsql = file_get_contents($sqlfile);
			$installsql = str_replace("\r\n", "\n", $installsql);
			executesql($installsql, $dbcharset, $dbengine);
			$sqlfile = ROOT_PATH. './install/data/install_data.sql';
			$installsql = file_get_contents($sqlfile);
			$installsql = str_replace("\r\n", "\n", $installsql);
			executesql($installsql, $dbcharset, $dbengine);
			$scriptname = dirname(getscriptname());
			$instdir = substr($scriptname, 0, strrpos($scriptname, '/') + 1);
			//$website = trim('http://' . $_SERVER['HTTP_HOST'] . $instdir, '/\ ');
			$db->query("REPLACE INTO {$tablepre}setting (skey, svalue, stype) VALUES ('authkey', '$authkey', 'string')");
			$db->query("REPLACE INTO {$tablepre}setting (skey, svalue, stype) VALUES ('version', '$version', 'string')");
			$db->query("REPLACE INTO {$tablepre}setting (skey, svalue, stype) VALUES ('instdir', '$instdir', 'string')");
			$db->query("REPLACE INTO {$tablepre}setting (skey, svalue, stype) VALUES ('website', '$website', 'string')");
			$db->query("REPLACE INTO {$tablepre}setting (skey, svalue, stype) VALUES ('webname', '$webname', 'string')");
			$db->query("REPLACE INTO {$tablepre}setting (skey, svalue, stype) VALUES ('adminmail', '$email', 'string')");
			$db->query("REPLACE INTO {$tablepre}setting (skey, svalue, stype) VALUES ('absoluteurl', '0', 'string')");
			$uid = 1;
			$salt = substr(uniqid(rand()), -6);
			$password = md5salt($password, $salt);
			$clientip = get_clientip();
			$db->query("REPLACE INTO {$tablepre}members (uid, username, password, adminid, groupid, email, salt, regdate, allowadmin, pmnew) VALUES
				('$uid', '$username', '$password', '1', '1', '$email', '$salt', '".time()."', '1', '0');");
			$db->query("REPLACE INTO {$tablepre}member_count (uid, money) VALUES ('$uid', '0');");
			$db->query("REPLACE INTO {$tablepre}member_info (uid, birthday) VALUES ('$uid', '0000-00-00');");
			$db->query("REPLACE INTO {$tablepre}member_status (uid, regip, lastip, groupterms) VALUES ('$uid', '$clientip', '$clientip', '');");
			dir_clear(ROOT_PATH.'./data/template');
			dir_clear(ROOT_PATH.'./data/cache');
			echo '<script type="text/javascript" src="cache.php"></script>';
			flush();
			ob_flush();
			sleep(1);
			redirect("index.php?step=4");
		}else{
			$step = 2;
			show_nav();
			$s = show_database_info($db_items);
			$s .= show_admin_info($admin_items);
			show_body($s);
			show_button();
		}
	}else{
		header("Location: index.php?step=2");
		exit;
	}

}elseif($step == 4){
	show_nav();
	$s = '<div class="msg"><p>'.$lang['install_succeed'].'</p></div>';
	show_body($s);
	@touch($lockfile);
}else{
	show_nav();
	$license = '<div class="licenseblock"><div class="license">'.$lang['license']. '</div></div>';
	show_body($license);
}
show_button();
show_footer();

function show_server_info(){
	global $lang;
	$server_items = array(
			'server_name' => $_SERVER['SERVER_NAME'],
			'server_phpos' => PHP_OS,
			'server_soft' => $_SERVER['SERVER_SOFTWARE'],
			'server_phpversion' => PHP_VERSION,
			'server_uploadsize' =>  @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow',
			'server_time' => date('r'),
			'server_instdir' => ROOT_PATH);
	$s = '<table class="tb"><caption>'.$lang['server_caption'].'</caption>';
	$s .= "<tr><th>$lang[item]</th><th>$lang[value]</th><th>$lang[status]</th></tr>";
	foreach($server_items as $key => $value){
		$s .= '<tr><td>'.$lang[$key].'</td>';
		$s .= "<td>$value</td><td>$lang[status_radic]</td></tr>";
	}
	$s .= '</table>';
	return $s;
}

function show_php_function(){
	global $lang, $func_items;
	$s = "<table class=\"tb\"><tr><th>$lang[function_name]</th><th>$lang[test_results]</th><th>$lang[suggestion]</th></tr>";
	foreach($func_items as $funcname){
		$s .= '<tr><td>'.$funcname.'()</td><td>';
		$s .= function_exists($funcname) ? $lang['status_radic'] : $lang['status_times'];
		$s .= "</td><td>$lang[none]</td></tr>";
	}
	$s .= '</table>';
	return $s;
}

function show_dirfile_info(){
	global $lang;
	$dirfile_items = array('./data/config.php' => 'file', './data/uc_config.php' => 'file', './data' => 'dir',
			'./data/cache' => 'dir', './data/template' => 'dir', './data/log' => 'dir', './attachment' => 'dir',
			'./templates' => 'dir');
	$s = '<table class="tb"><caption>'.$lang['dirfile_caption'].'</caption>';
	$s .= "<tr><th>$lang[directory_file]</th><th>$lang[current_status]</th><th>$lang[need_status]</th></tr>";
	foreach($dirfile_items as $path => $type){
		$s .= '<tr><td>'.$path.'</td><td>';
		$pathcheck = check_dirfile($path, $type);
		if($pathcheck === 1){
			$s .= $lang['dirfile_status_radic'];
		}elseif($pathcheck === 0){
			$s .= $lang['dirfile_status_times'];
		}else{
			$s .= $lang[$type.'_not_exist'];
		}
		$s .= "</td><td>$lang[dirfile_need_status]</td></tr>";
	}
	$s .= '</table>';
	return $s;
}

function show_config_form(){
	
}

function show_database_info($db_items = array()){
	global $lang;
	$s = showhtmlsetting('config_db_caption', 'start');
	foreach($db_items as $key => $result){
		$type = empty($result['type']) ? 'text' : trim($result['type']);
		$s .= showhtmlsetting("config_$key", "dbconfig[$key]", $result['value'], $type, $result['error']);
	}
	$s .= '</table>';
	return $s;
}

function show_admin_info($admin_items = array()){
	global $lang;
	$s = showhtmlsetting('config_admin_caption', 'start');
	foreach($admin_items as $key => $result){
		if($key == 'password' || $key == 'password2'){
			$type = 'password';
		}else{
			$type = 'text';
		}
		$s .= showhtmlsetting("config_admin_$key", "adminuser[$key]", $result['value'], $type, $result['error']);
	}
	$s .= '</table>';
	return $s;
}

?>