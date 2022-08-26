<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : config.php    2011-4-4 6:43:26
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'setting';
$webconfig = array();
if ($action == 'sendmail') {
    if (!checksubmit('btnsubmit')) {
        $navarray = array(
            array('title' => 'menu_config_sendmail', 'url' => '?action=sendmail&m=config', 'name' => 'setting_mail_sendmail'),
            array('title' => 'menu_setting_mail', 'url' => '?action=mail&m=setting', 'name' => 'mail')
        );

        admin_header('menu_config_sendmail');
        $adminhtml = phpcom_adminhtml::instance();
        $adminhtml->activetabs('global');
        $adminhtml->navtabs($navarray, 'setting_mail_sendmail');
        $adminhtml->editor_scritp('editor_small');
        $adminhtml->form('m=config', array(array('action', 'sendmail')));
        $adminhtml->table_header('setting_mail_sendmail', 3);
        $adminhtml->table_setting('setting_mail_sendmail_from', 'from', phpcom::$setting['mail']['defaultfrom'], 'text');
        $adminhtml->table_setting('setting_mail_sendmail_mailto', 'mailto', '', 'text');
        $adminhtml->table_setting('setting_mail_sendmail_subject', 'subject', phpcom::$setting['webname'].' Mail test', 'text');
        $adminhtml->table_setting('setting_mail_sendmail_content', 'content', phpcom::$setting['webname'] . ' ' . phpcom::$G['siteurl'], 'editor');
        $adminhtml->table_setting('submitmail', 'btnsubmit', '', 'submit');
        $adminhtml->table_end('</form>');
        admin_footer();
    } else {
        if (!function_exists('sendmail')) {
            include loadlibfile('mail', 'lib');
        }
        $subject = phpcom::$G['gp_subject'];
        $message = bbcode::bbcode2html(phpcom::$G['gp_content']);
        $mailto = phpcom::$G['gp_mailto'];
        $flag = sendmail($mailto, $subject, $message, phpcom::$G['gp_from']);
        if ($flag) {
            admin_succeed('sendmail_succeed', 'm=config&action=sendmail');
        } else {
            admin_error('sendmail_failed', 'm=config&action=sendmail');
        }
    }
} else {
    if (!checksubmit('btnsubmit')) {
        $webconfig = getwebconfig();
        admin_header('menu_config');

        $adminhtml = phpcom_adminhtml::instance();
        $adminhtml->form('m=config', array(array('action', 'save')));
        $adminhtml->table_header('setting_config', 3);
        $adminhtml->table_setting('setting_config_charset', 'webconfig[charset]', strtolower($webconfig['output']['charset']), 'select');
        $adminhtml->table_setting('setting_config_forceheader', 'webconfig[forceheader]', $webconfig['output']['forceheader'], 'radio');
        $adminhtml->table_setting('setting_config_debug', 'webconfig[debug]', $webconfig['debug'], 'radio2');
        $adminhtml->table_setting('setting_config_gzip', 'webconfig[gzip]', $webconfig['output']['gzip'], 'radio');
        $adminhtml->table_setting('setting_config_ajaxvalidate', 'webconfig[ajaxvalidate]', $webconfig['output']['ajaxvalidate'], 'radio');
        $adminhtml->table_setting('setting_config_urlxssdefend', 'webconfig[urlxssdefend]', $webconfig['security']['urlxssdefend'], 'radio');
        $adminhtml->table_setting('setting_config_cookieprefix', 'webconfig[cookieprefix]', $webconfig['cookie']['prefix'], 'text');
        $adminhtml->table_setting('setting_config_cookiedomain', 'webconfig[cookiedomain]', $webconfig['cookie']['domain'], 'text');
        $adminhtml->table_setting('setting_config_cookiepath', 'webconfig[cookiepath]', $webconfig['cookie']['path'], 'text');
        $adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
        $adminhtml->table_end('</form>');
        admin_footer();
    } else {
        $webconfig = phpcom::$G['gp_webconfig'];
        $config = getwebconfig();
        if(!isset($config['db']['engine']) || empty($config['db']['engine'])){
        	$config['db']['engine'] = 'MyISAM';
        }
        if(!isset($config['cache']['redis'])){
        	$config['cache']['redis']['host'] = '';
        	$config['cache']['redis']['port'] = 6379;
        	$config['cache']['redis']['pconnect'] = 1;
        	$config['cache']['redis']['timeout'] = '0';
        	$config['cache']['redis']['serialized'] = 1;
        }
        if(!isset($config['cache']['enabled'])){
        	$config['cache']['enabled'] = '0';
        	$config['cache']['ttl'] = '300';
        }
        $config['debug'] = $webconfig['debug'];
        $config['output']['charset'] = $webconfig['charset'];
        $config['output']['forceheader'] = $webconfig['forceheader'];
        $config['output']['gzip'] = $webconfig['gzip'];
        $config['output']['ajaxvalidate'] = $webconfig['ajaxvalidate'];
        $config['security']['urlxssdefend'] = $webconfig['urlxssdefend'];
        $config['cookie']['prefix'] = $webconfig['cookieprefix'];
        $config['cookie']['domain'] = $webconfig['cookiedomain'];
        $config['cookie']['path'] = $webconfig['cookiepath'];

        $filename = PHPCOM_ROOT . '/data/config.php';
        save_webconfig($filename, $config);
        admin_succeed('config_succeed', 'm=config');
    }
}

function save_webconfig($filename, $config, $default = NULL) {
    $config = setconfigarray($config, $default);
    $content = "<?php\r\n!defined('IN_PHPCOM') && exit('Access denied');\r\n\r\n\$_config = array();\r\n";
    $content .= exportconfigarray($config);
    $content .= "\r\n/* " . str_pad('  THE END  ', 70, '-', STR_PAD_BOTH) . " */\r\n\r\n?>";
    file_put_contents($filename, $content);
}

function getwebconfig() {
    $_config = array();
    include PHPCOM_ROOT . '/data/config.php';
    return $_config;
}

?>
