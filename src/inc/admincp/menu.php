<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : menu.php    2012-2-7
 */
!defined('IN_ADMINCP') && exit('Access denied');
$topmenu = $menu = array();
$topmenu = array (
	'index' => 'main',
	'global' => 'setting&action=basic',
    'topic' => 'article&chanid=1',
	'user' => 'members&action=search',
	'tools' => 'cron',
    'help' => 'http://www.phpcom.net/',
//	'game' => 'game',
//	'kfbPkg' => 'kfbPkg',
    'logout' => 'logout'
);

$menu['index'] = array(
	array('menu_home', 'main'),
	array('menu_misc_custommenu', 'misc_custommenu'),
);
$customizemenu = generate_customizemenu();
$menu['index'] = array_merge($menu['index'], $customizemenu);

$menu['global'] = array(
    array('menu_setting_basic', 'setting_basic'),
    array('menu_config', 'config'),
    array('menu_channel', 'channel'),
	array('menu_template_setting', 'template_setting'),
	array('menu_urlrules', 'urlrules'),
    array('menu_setting_register', 'setting_register'),
    array('menu_invite' , 'invite'),
    array('menu_setting_attach', 'setting_attach'),
    array('menu_setting_imgwmk', 'setting_imgwmk'),
    array('menu_setting_remote', 'setting_remote'),
    array('menu_setting_mail', 'setting_mail'),
	array('menu_setting_search', 'setting_search'),
	array('menu_setting_searchword', 'setting_searchword'),
    array('menu_setting_security' , 'setting_security'),
    array('menu_credits', 'credits'),
    array('menu_card', 'card'),
    array('menu_payonline', 'payonline'),
    array('menu_banip', 'banip'),
    array('menu_badword', 'badword'),
    array('menu_tags', 'tags')
);

$menu['topic'] = array(
	array('menu_threads', 'threads'),
	array('menu_submission', 'submission'),
    array('menu_comment', 'comment'),
	array('menu_robots', 'robots'),
	array('menu_tohtml', 'tohtml')
);
if(defined('IN_PHPCOM_BUSINESS') && IN_PHPCOM_BUSINESS){
	$menu['topic'][] = array('menu_threadlog', 'threadlog');
	$menu['topic'][] = array('menu_softUpdate', 'softUpdate');
}
$channelmenu = generate_channelmenu();
$menu['topic'] = array_merge($channelmenu, $menu['topic']);

$menu['user'] = array(
    array('menu_members', 'members_manage'),
    array('menu_members_add', 'members_add'),
    array('menu_members_search', 'members_search'),
    array('menu_members_audit', 'members_audit'),
    array('menu_members_ban', 'members_ban'),
    array('menu_members_clean', 'members_clean'),
    array('menu_usergroup', 'usergroup_member'),
    array('menu_usergroup_admingroup', 'usergroup_admingroup'),
    //array('menu_ucapps', 'ucapps')
);

$menu['tools'] = array(
    array('menu_cron', 'cron'),
	array('menu_template', 'template'),
	array('menu_adverts', 'adverts'),
	array('menu_announce', 'announce'),
	array('menu_links', 'links'),
    array('menu_admingroup', 'admingroup'),
    array('menu_db', 'db'),
    array('menu_subtable', 'subtable'),
	array('menu_runquery', 'db_runquery'),
	array('menu_upgrade', 'upgrade_check'),
    array('menu_tools_updatecache', 'tools_updatecache'),
	array('menu_logs', 'logs'),
    array('menu_adminfav', 'adminfav'),
    array('menu_phpinfo', 'phpinfo')
);
/*
$menu['game'] = array (
		array('menu_game_add', 'game&action=add'),
		array('menu_game_list', 'game&action=list'),
		// array('menu_game_modify', 'game&action=modify'),
		// array('menu_game_audit_server', 'game'),
		// array('menu_game_audit_gift', 'game'),
		array('menu_game_option', 'game&action=option'),
		array('menu_game_firm', 'game&action=firm'),
		array('menu_game_server', 'game&action=server'),
		array('menu_game_gift', 'game&action=gift'),
		array('menu_game_count', 'game&action=count'),
		array('menu_game_log', 'game&action=log'),
);

$menu['kfbPkg'] = array(
		array('menu_kfbPkg_list', 'kfbPkg&action=list'),
		array('menu_kfbPkg_firmAmount', 'kfbPkg&action=firmAmount'),
);
*/
?>
