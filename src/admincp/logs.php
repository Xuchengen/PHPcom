<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : logs.php  2012-10-15
 */
!defined('IN_PHPCOM') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';
admin_header('menu_logs');

$navarray = array(
		array('title' => 'logs_illegal', 'url' => '?m=logs&action=illegal', 'id' => 'logs_illegal'),
		array('title' => 'logs_error', 'url' => '?m=logs&action=error', 'id' => 'logs_error'),
		array('title' => 'logs_admin', 'url' => '?m=logs&action=admin', 'id' => 'logs_admin'),
		array('title' => 'logs_rate', 'url' => '?m=logs&action=rate', 'id' => 'logs_rate'),
		array('title' => 'logs_ban', 'url' => '?m=logs&action=ban', 'id' => 'logs_ban')
);

$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('tools');
$logdir = PHPCOM_ROOT . '/data/log/';
$action = in_array($action, array('error', 'admin', 'rate', 'ban', 'illegal')) ? $action : 'illegal';
$adminhtml->navtabs($navarray, "logs_$action");
$logfiles = getLogsFiles($logdir, $action .'log');
$logsdata = array();
$dropselect = '';
if($logfiles){
	$day = isset(phpcom::$G['gp_day']) ? phpcom::$G['gp_day'] : '';
	if($day){
		$logsdata = file($logdir.$day."_{$action}log.php");
	}else{
		$logsdata = file($logdir.$logfiles[0]);
	}
	$logsdata = array_reverse($logsdata);
	$dropselect = '<select class="select right" style="margin-right:10px;" onchange="location.href=\''.ADMIN_SCRIPT.'?m=logs&action='.$action.'&day=\'+this.value">';
	foreach($logfiles as $logfile) {
		$date = substr($logfile, 0, 6);
		$dropselect .= '<option value="'.$date.'"'.($date == $day ? ' selected="selected"' : '').'>'.$date.'</option>';
	}
	$dropselect .= "</select>";
}

if ($action == 'illegal') {
	$adminhtml->table_header('logs_illegal', null, '', 'tableborder', 0, '', $dropselect);
	$adminhtml->table_td(array(array('logs_tips', FALSE, 'colspan="5"')), NULL, FALSE, NULL, NULL, FALSE);
	$adminhtml->table_td(array(
			array('time', FALSE, 'width="15%"'),
			array('ip', FALSE),
			array('logs_try_username', FALSE),
			array('logs_try_password', FALSE),
			array('logs_try_question', FALSE)
	), '', FALSE, ' tablerow', NULL, FALSE);
	foreach($logsdata as $datarow) {
		$log = explode("\t", $datarow);
		if(!isset($log[4])){
			continue;
		}
		$adminhtml->table_td(array(
				array(fmdate($log[1] , 'dt'), TRUE),
				array($log[5], TRUE),
				array('<a href="member.php?action=home&username='.rawurlencode($log[2]).'" target="_blank">'.$log[2].'</a>', TRUE),
				array($log[3], TRUE),
				array($log[4], TRUE)
		));
	}
	$adminhtml->table_end();
}elseif ($action == 'error') {
	$adminhtml->table_header('logs_error', null, '', 'tableborder', 0, '', $dropselect);
	$adminhtml->table_td(array(array('logs_tips', FALSE, 'colspan="3"')), NULL, FALSE, NULL, NULL, FALSE);
	$adminhtml->table_td(array(
			array('time', FALSE, 'width="15%"'),
			array('detail', FALSE),
			array('request', FALSE)
	), '', FALSE, ' tablerow', NULL, FALSE);
	foreach($logsdata as $datarow) {
		$log = explode("\t", $datarow);
		if(!isset($log[4])){
			continue;
		}
		$adminhtml->table_td(array(
				array(fmdate($log[1] , 'dt'), TRUE),
				array($log[2], TRUE),
				array(htmlcharsencode($log[4]), TRUE)
		));
	}
	$adminhtml->table_end();
	
}elseif ($action == 'admin') {
	$adminhtml->table_header('logs_admin', null, '', 'tableborder', 0, '', $dropselect);
	$adminhtml->table_td(array(
			array('username', FALSE, 'width="12%"'),
			array('ip', FALSE, 'width="12%"'),
			array('time', FALSE, 'width="15%"'),
			array('detail', FALSE)
	), '', FALSE, ' tablerow', NULL, FALSE);
	foreach($logsdata as $datarow) {
		$log = explode("\t", $datarow);
		if(!isset($log[4])){
			continue;
		}
		$adminhtml->table_td(array(
				array('<a href="member.php?action=home&username='.rawurlencode($log[2]).'" target="_blank">'.$log[2].'</a>', TRUE),
				array($log[3], TRUE),
				array(fmdate($log[1] , 'dt'), TRUE),
				array(htmlcharsencode($log[4]), TRUE)
		));
	}
	$adminhtml->table_end();
}elseif ($action == 'rate') {
	$adminhtml->table_header('logs_rate', null, '', 'tableborder', 0, '', $dropselect);
	$adminhtml->table_td(array(
			array('username', FALSE, 'width="12%"'),
			array('time', FALSE, 'width="12%"'),
			array('logs_by_rater', FALSE, 'width="15%"'),
			array('logs_rate_credits', FALSE),
			array('operation', FALSE),
			array('detail', FALSE)
	), '', FALSE, ' tablerow', NULL, FALSE);
	foreach($logsdata as $datarow) {
		$log = explode("\t", $datarow);
		if(!isset($log[9])){
			continue;
		}
		$adminhtml->table_td(array(
				array('<a href="member.php?action=home&username='.rawurlencode($log[2]).'" target="_blank">'.$log[2].'</a>', TRUE),
				array(fmdate($log[1] , 'dt'), TRUE),
				array('<a href="member.php?action=home&username='.rawurlencode($log[4]).'" target="_blank">'.$log[4].'</a>', TRUE),
				array(phpcom::$setting['credits'][$log[5]]['title'] . ($log[6] < 0 ? "<strong>$log[6]</strong>" : "+$log[6]"), TRUE),
				array($log[3] ? adminlang('logs_rating_manual') : '', TRUE),
				array(htmlcharsencode($log[9]), TRUE)
		));
	}
	$adminhtml->table_end();
}elseif ($action == 'ban') {
	$adminhtml->table_header('logs_ban', null, '', 'tableborder', 0, '', $dropselect);
	$adminhtml->table_td(array(
			array('operator', FALSE, 'width="12%"'),
			array('ip', FALSE, 'width="12%"'),
			array('time', FALSE, 'width="15%"'),
			array('username', FALSE, 'width="12%"'),
			array('operation', FALSE),
			array('logs_ban_group', FALSE),
			array('detail', FALSE)
	), '', FALSE, ' tablerow', NULL, FALSE);
	$operates = array(1 => '<strong>'.adminlang('logs_lock').'</strong>', 
			2 => '<strong>'.adminlang('logs_unlock').'</strong>', 
			3 => '<i>'.adminlang('logs_banned_unban').'</i>', 
			4 => '<strong>'.adminlang('logs_banned_ban').'</strong>');
	$operate = 1;
	foreach($logsdata as $datarow) {
		$log = explode("\t", $datarow);
		if(!isset($log[4])){
			continue;
		}
		if($log[10] == -1) {
			$operate = 1;
		}else{
			if($log[6] == $log[7]) {
				$operate = 2;
			}else{
				$operate = (in_array($log[6], array(4, 5)) && !in_array($log[7], array(4, 5))) ? 3 : 4;
			}
		}
		$adminhtml->table_td(array(
				array('<a href="member.php?action=home&username='.rawurlencode($log[2]).'" target="_blank">'.$log[2].'</a>', TRUE),
				array($log[4], TRUE),
				array(fmdate($log[1] , 'dt'), TRUE),
				array('<a href="member.php?action=home&username='.rawurlencode($log[5]).'" target="_blank">'.$log[5].'</a>', TRUE),
				array($operates[$operate], TRUE),
				array('<span class="c2">'.phpcom::$G['usergroup'][$log[6]]['grouptitle'] .'</span> &raquo; <span class="c1">'.
						phpcom::$G['usergroup'][$log[7]]['grouptitle'] .'</span>', TRUE),
				array(htmlcharsencode($log[9]), TRUE)
		));
	}
	$adminhtml->table_end();
}
admin_footer();

function getLogsFiles($logdir, $action = 'error'){
	$logdir = rtrim($logdir, '/\ ');
	$files = glob("$logdir/*_{$action}.php");
	sort($files);
	$logfiles = array();
	foreach ($files as $file){
		$logfiles[] = basename($file);
	}
	rsort($logfiles);
	return $logfiles;
}
?>