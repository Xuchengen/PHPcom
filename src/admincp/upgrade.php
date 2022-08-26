<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : upgrade.php  2012-10-16
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';
admin_header('menu_upgrade');
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('tools');
$navarray = array(
		array('title' => 'menu_upgrade', 'url' => '?m=upgrade', 'id' => 'upgrade')
);
$adminhtml->navtabs($navarray, 'upgrade');
if ($action == 'check') {
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$msgargs = array(
				'form' => true,
				'loading' => true,
				'autosubmit' => true,
				'action' => '?m=upgrade&action=check&submit=yes'
		);
		admin_showmessage('upgrade_now_check_message', null, $msgargs);
	}else{
		$url = ADMIN_SCRIPT . '?m=upgrade';
		$upgrade = new UpgradeService();
		$upgrade->checkUpgrade();
		sleep(1);
		@header("Location: $url");
	}
}else{
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$adminhtml->form("m=upgrade");
		$adminhtml->table_header("menu_upgrade");
		$upgrader = array();
		$message = 'no_upgrade_tips';
		if(!empty(phpcom::$setting['upgrader'])){
			$upgrader = phpcom::$setting['upgrader'];
			if(isset($upgrader['level']) && $upgrader['level'] == 1){
				$message = 'have_upgrade_tips';
			}elseif(isset($upgrader['level']) && ($upgrader['level'] == 2 || $upgrader['level'] == 3)){
				$message = 'have_update_tips';
			}
		}
		$adminhtml->table_td(array(array($message, array('date' => fmdate(time(), 'dt', 'd')), 'colspan="3"')), NULL, FALSE, NULL, NULL, FALSE);
		$charset = str_replace('-', '', strtoupper(phpcom::$config['output']['charset']));
		$locale = 'SC';
		if($charset == 'BIG5') {
			$locale = 'TC';
		}elseif($charset == 'UTF8'){
			if(stricmp(phpcom::$config['output']['language'], array('zh-CN', 'zh_CN'))){
				$locale = 'SC';
			}elseif(stricmp(phpcom::$config['output']['language'], array('zh-TW', 'zh_TW'))){
				$locale = 'TC';
			}
		}
		if(isset($upgrader['level']) && $upgrader['level'] == 1){
			$release = $upgrader['release'];
			$adminhtml->table_td(array(
					array('phpcom'.$upgrader['version']."_{$locale}_{$charset} [release $release]", TRUE),
					array($upgrader['description'], TRUE),
					array('right_now_upgrade', array('url' => $upgrader['official']), 'width="15%"')
			));
		}elseif((isset($upgrader['level'])) && ($upgrader['level'] == 2 || $upgrader['level'] == 3)){
			$release = $upgrader['release'];
			$adminhtml->table_td(array(
					array("[release $release]", TRUE),
					array($upgrader['description'], TRUE),
					array('right_now_update', array('url' => $upgrader['official']), 'width="15%"')
			));
		}
		$adminhtml->table_end('</form>');
	}else{
		echo '';
	}
	
}
admin_footer();

?>