<?php

/**
 *
 *  Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 *  Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 *  Description : This software is the proprietary information of phpcom.cn.
 *  This File   : admin.php
 *
 */
define('IN_ADMINCP', TRUE);
define('ADMIN_SCRIPT', basename(__FILE__));
define('CURRENT_SCRIPT', 'admin');
require dirname(__FILE__) . '/src/inc/common.php';
require PHPCOM_PATH . '/lib/admincp.php';
$phpcom->init();
if (!check_adminipaccess()) {
    showmessage('admincp_allow_ipaccess');
}
$admincp = phpcom_admincp::instance();
$admincp->init();
$module = htmlspecialchars(phpcom::$G['mod']);
$action = isset(phpcom::$G['gp_action']) ? htmlcharsencode(phpcom::$G['gp_action']) : '';
$do = isset(phpcom::$G['gp_do']) ? htmlcharsencode(phpcom::$G['gp_do']) : '';
$page = phpcom::$G['page'];
$channelid = phpcom::$G['channelid'];
$charset = CHARSET;

$admincp_modules_founder = array('config', 'template', 'ucapps', 'admingroup', 'subtable', 'runquery', 'db', 'upgrade', 'patch');
$admincp_modules_normal = array('main', 'index', 'ajax', 'setting', 'channel', 'invite', 'members', 'credits', 'tags', 'robots',
    'article', 'soft', 'photo', 'ask', 'video', 'downserver', 'special', 'topical', 'comment', 'category', 'tohtml', 'spider', 'monitor',
    'softtest', 'banip', 'badword', 'usergroup', 'cron', 'announce', 'adminfav', 'phpinfo', 'links', 'misc', 'tools', 'submission',
    'payonline', 'userorder', 'card', 'urlrules', 'logs', 'adverts', 'session', 'online', 'messages', 'notify', 'threads', 'threadlog',
	'topics', 'attachment', 'softUpdate', 'softupdate', 'game', 'kfbPkg'
);

if (phpcom::$config['admincp']['forbidpost'] && $_SERVER['REQUEST_METHOD'] == 'POST') {
    admin_message('forbidpost');
}

$module = empty($module) ? $action : $module;
$admintitle = "menu_$module" . ($action ? "_$action" : '');
!empty($do) && $admintitle .= "_$do";
if (empty($module)) {
    require $admincp->file('index');
} elseif ($module == 'logout' || $action == 'logout') {
    $admincp->adminlogout();
    @header("Location: " . ADMIN_SCRIPT);
} elseif (in_array($module, $admincp_modules_normal) || ($admincp->isfounder && in_array($module, $admincp_modules_founder))) {
    if ($module == 'ajax' || $admincp->permscheck($module, $action, $do) || $module == 'index' || $module == 'main') {
        require $admincp->file($module);
    } else {
        admin_message('action_access_denied');
    }
} else {
    admin_message('action_access_denied');
}

?>
