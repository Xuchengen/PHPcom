<?php
/**
 * Copyright (c) 2010-2012 phpmain.com - All rights reserved.
 * Our Website : www.phpmain.com www.phpmain.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPMain.
 * This File   : urlrules.php  2012-3-27
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'setting';
admin_header('menu_urlrules');
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('global');
$navarray = array(
	array('title' => 'menu_urlrules', 'url' => '?m=urlrules', 'id' => 'urlrules'),
	array('title' => 'setting_basic', 'url' => '?m=setting', 'id' => 'setting_basic')
);
$adminhtml->navtabs($navarray, 'urlrules');
if (!checksubmit(array('submit', 'btnsubmit'))) {
	if(isset(phpcom::$G['cache']['urlrules']['soft']['type'])){
		urlrules_upgrade();
	}
	$adminhtml->form('m=urlrules');
	$adminhtml->table_header('setting_urlrules', 4);
	$adminhtml->table_td(array(array('setting_urlrules_tips', FALSE, 'colspan="4"')), NULL, FALSE, NULL, NULL, FALSE);
	$adminhtml->table_setting('setting_basic_htmlstatus', 'htmlstatus', intval(phpcom::$setting['htmlstatus']), 'radios');
	$adminhtml->table_end();
	$adminhtml->table_header();
	$adminhtml->table_th(array(
			array('setting_urlrules_description', 'class="left"'),
			array('setting_urlrules_matchurl', 'class="left"'),
			array('setting_urlrules_actionurl', 'class="left"'),
			array('setting_urlrules_staticize', 'class="left"')
	));
	$query = DB::query("SELECT * FROM " . DB::table('urlrules') . " ORDER BY ruleid ASC");
	while ($row = DB::fetch_array($query)) {
		$ruleid = $row['ruleid'];
		$adminhtml->table_td(array(
				array($adminhtml->inputedit("description[$ruleid]", $row['description'], 20, 'left'), TRUE),
				array($adminhtml->textinput("matchurl[$ruleid]", $row['matchurl'], 60), TRUE),
				array($adminhtml->textinput("actionurl[$ruleid]", $row['actionurl'], 60), TRUE),
				array('<input class="checkbox" type="checkbox" name="staticize[' . $ruleid . ']" value="1"'.($row['staticize'] ? ' checked' : '').'/>', TRUE, '')
		));
	}
	$adminhtml->table_td(array(
			array('&nbsp;', TRUE, 'align="center"'),
			array($adminhtml->submit_button(), TRUE, 'colspan="3"')
	), NULL, FALSE, NULL, NULL, FALSE);
	$adminhtml->table_end('</form>');
}else{
	$descriptions = phpcom::$G['gp_description'];
	$matchurls = phpcom::$G['gp_matchurl'];
	$actionurls = phpcom::$G['gp_actionurl'];
	$staticizes = isset(phpcom::$G['gp_staticize']) ? phpcom::$G['gp_staticize'] : array();
	if($matchurls && $actionurls){
		foreach ($matchurls as $ruleid => $matchurl){
			DB::update('urlrules',array(
					'description' => htmlstrip($descriptions[$ruleid]),
					'matchurl' => $matchurl,
					'actionurl' => $actionurls[$ruleid],
					'staticize' => isset($staticizes[$ruleid]) ? intval($staticizes[$ruleid]) : 0
			), "ruleid='$ruleid'");
		}
	}
	$htmlstatus = intval(phpcom::$G['gp_htmlstatus']);
	DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES ('htmlstatus', '$htmlstatus', 'string')");
	phpcom_cache::updater('setting');
	phpcom_cache::updater('urlrules');
	admin_succeed('urlrules_update_succeed', 'm=urlrules');
}

function urlrules_upgrade()
{
	$data = array();
	if($rules = DB::fetch_first("SELECT ruleid, modules, rulename FROM " . DB::table('urlrules') . " WHERE ruleid='35' AND rulename='type'")){
		if($rules['rulename'] != 'type') return;
		if($rules['modules'] != 'soft') return;
		$i = 0;
		$types = array();
		$query = DB::query("SELECT * FROM " . DB::table('urlrules') . " WHERE 1=1 ORDER BY ruleid ASC");
		while ($row = DB::fetch_array($query)) {
			$i++;
			if($row['rulename'] == 'preview'){
				$types[$row['modules']]['type'] = $i;
				$i++;
			}
			if($row['rulename'] == 'type'){
				if(isset($types[$row['modules']]['type'])){
					$i--;
					$data[$types[$row['modules']]['type']] = $row;
				}else{
					$data[$i] = $row;
				}
			}else{
				$data[$i] = $row;
			}
		}
		ksort($data);
		foreach ($data as $ruleid => $value){
			$value['ruleid'] = $ruleid;
			$value = addslashes_array($value);
			DB::insert('urlrules', $value, false, true);
		}
		phpcom_cache::updater('urlrules');
	}
}
admin_footer();
?>