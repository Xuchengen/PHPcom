<?php

/**
 *
 *  Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 *  Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 *  Description : This software is the proprietary information of phpcom.cn.
 *  This File   : phpinfo.php
 *
 */
!defined('IN_ADMINCP') && exit('Access denied');

ob_start();
phpinfo(INFO_GENERAL | INFO_CONFIGURATION | INFO_MODULES | INFO_VARIABLES);
$phpinfo = ob_get_clean();

$phpinfo = trim($phpinfo);
preg_match_all('#<body[^>]*>(.*)</body>#si', $phpinfo, $output);
if (empty($phpinfo) || empty($output)) {
	trigger_error('NO_PHPINFO_AVAILABLE', E_USER_WARNING);
}

$output = $output[1][0];
$output = preg_replace('#<table[^>]+>#i', '<table class="tableborder" cellspacing="1" cellpadding="3" align="center" border="0">', $output);
//$output = str_replace(array('class="e"', 'class="v"', 'class="h"', '<hr />', '<font', '</font>'), array('class="tablerow2 e"', 'class="tablerow1 v"', 'class="tablerow h"', '', '<span', '</span>'), $output);
$output = str_replace('<tr><td class="e">Features </td></tr>', '', $output);
admin_header('phpinfo');
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('tools');
echo '<div id="php-info">',$output,'</div>';
admin_footer();
?>
