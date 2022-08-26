<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This specialware is the proprietary information of PHPcom.
 * This File   : special.php  2012-8-13
 */
!defined('IN_PHPCOM') && exit('Access denied');

phpcom::$G['lang']['admin'] = 'special';
$chanid = isset(phpcom::$G['gp_chanid']) ? intval(phpcom::$G['gp_chanid']) : 4;
$chanid = $chanid ? $chanid : 4;
phpcom::$G['channelid'] = $chanid;
if(!isset(phpcom::$G['channel'][$chanid])){
	admin_message('undefined_action');
}
$uid = phpcom::$G['uid'];
$myuid = empty(phpcom::$G['gp_uid']) ? 0 : intval(phpcom::$G['gp_uid']);
phpcom::$G['cache']['channel'] = phpcom::$G['channel'][$chanid];
$namevar = array('name' => phpcom::$G['cache']['channel']['subname'], 'chanid' => $chanid);
$deftable = intval(phpcom::$G['cache']['channel']['deftable']);
phpcom::$G['langvar'] = $namevar;
$status = isset(phpcom::$G['gp_status']) ? intval(phpcom::$G['gp_status']) : 1;
$current = '';
$active = 'first';
if ($action == 'add' || $action == 'edit') {
	$current = "menu_special_$action";
	$active = $action == 'add' ? 'add' : 'first';
}elseif ($action == 'list') {
	if($myuid){
		$current = 'menu_special_my';
		$active = 'my';
	}elseif ($status === 0) {
		$current = 'menu_special_audit';
		$active = 'audit';
	}
}

//array('<a href="javascript:void(0);" onclick="openDialog(\'?m=topical&specid=' . $topicid . '&classid=' . $classid . '\', \'specialtopics\', \'' . adminlang('special_topic_class_manage') . '\',820,560);">' . adminlang('special_topic_class_manage') . '</a>', true),

admin_header('menu_special', $current, $namevar);
$navarray = array(
		array('title' => 'menu_special', 'url' => "?m=special&action=list&chanid=$chanid", 'name' => 'first'),
		array('title' => 'menu_special_my', 'url' => "?m=special&action=list&chanid=$chanid&uid=$uid", 'name' => 'my'),
		array('title' => 'menu_special_add', 'url' => "?m=special&action=add&chanid=$chanid", 'name' => 'add'),
		array('title' => 'menu_special_audit', 'url' => "?m=special&action=list&status=0&chanid=$chanid", 'name' => 'audit'),
		array('title' => 'menu_category', 'url' => "?m=category&nav=special&chanid=$chanid", 'name' => 'category'),
		array('title' => 'menu_channel', 'url' => "?m=channel&action=edit&chanid=$chanid", 'name' => 'channel')
);
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->setvars($namevar);
$adminhtml->activetabs('topic');
$adminhtml->navtabs($navarray, $active, 'nav_tabs', 'special');
if ($action == 'edit' || $action == 'add' || $action == 'quickedit') {
	include loadlibfile('edit', 'admincp/special');
}elseif($action == 'list' || $action == 'search'){
	include loadlibfile('list', 'admincp/special');
} elseif ($action == 'del') {
	if(!phpcom_admincp::permission('thread_delete')){
		admin_message('action_delete_denied');
	}
	$tid = intval(phpcom::$G['gp_tid']);
	include_once loadlibfile('delete');
	if (delete_special_thread($tid)) {
		phpcom_cache::updater('syscount', $chanid);
		admin_succeed('threads_delete_succeed', "m=special&action=list&chanid=$chanid");
	} else {
		admin_message('threads_delete_failed', "m=special&action=list&chanid=$chanid");
	}
} elseif ($action == 'upcount') {
	$catid = isset(phpcom::$G['gp_catid']) ? intval(phpcom::$G['gp_catid']) : 0;
	$rootid = isset(phpcom::$G['gp_rootid']) ? intval(phpcom::$G['gp_rootid']) : 0;
	if (!checksubmit(array('btnsubmit', 'submit'))) {
		$msgargs = array(
				'form' => true,
				'loading' => true,
				'autosubmit' => true,
				'action' => "?m=special&action=upcount&catid=$catid&rootid=$rootid&chanid=$chanid"
		);
		$extra = '<input type="hidden" name="btnsubmit" value="yes" />';
		admin_showmessage('update_category_count_now', null, $msgargs, $extra);
	}else{
		update_category_counts($chanid, $catid, $rootid);
		admin_succeed('update_category_count_succeed', "m=special&chanid=$chanid");
	}
}else{
	$adminhtml->table_header('special_admin');
	$adminhtml->table_td(array(array('special_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
	$adminhtml->table_th(array(
			array('special_category_all', 'class="left"'),
	));
	$cachename = "category_$chanid";
	phpcom_cache::load($cachename);
	if(isset(phpcom::$G['cache'][$cachename][0])) {
		foreach(phpcom::$G['cache'][$cachename][0] as $cid => $category){
			$count = isset($category['counts']) ? $category['counts'] : 0;
			$tmpstr = "<a href=\"?m=special&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
			$tmpstr .= "<a title=\"count: $count\" href=\"?m=special&action=list&rootid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
			$tmpstr .= "<a href=\"?m=category&action=edit&catid=$cid&chanid=$chanid\"><img src=\"misc/images/icons/option.gif\" /></a>";
			$tmpstr .= " <span class=\"btntxt\"><a href=\"?m=special&action=upcount&rootid=$cid&chanid=$chanid\" style=\"font-weight:400;color:#ff6600\">".adminlang('update_count')."</a></span>";
			$adminhtml->table_td(array(
					array($tmpstr, TRUE, 'colspan="6"')
			), '', FALSE, ' tablerow', NULL, FALSE);
			if(isset(phpcom::$G['cache'][$cachename][$cid])) {
				$tmpstr1 = $tmpstr2 = $tmpstr3 = "";
				foreach(phpcom::$G['cache'][$cachename][$cid] as $cid => $category){
					$count = isset($category['counts']) ? $category['counts'] : 0;
					$tmpstr1 .= "<li>";
					$tmpstr1 .= "<a href=\"?m=special&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
					$tmpstr1 .= "<a title=\"count: $count\" href=\"?m=special&action=list&catid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
					$tmpstr1 .= "<a href=\"?m=category&action=edit&catid=$cid&chanid=$chanid\"><img src=\"misc/images/icons/option.gif\" /></a>";
					$tmpstr1 .= "</li>";
					if(isset(phpcom::$G['cache'][$cachename][$cid])) {
						foreach(phpcom::$G['cache'][$cachename][$cid] as $cid => $category){
							$count = isset($category['counts']) ? $category['counts'] : 0;
							$tmpstr2 .= "<li>";
							$tmpstr2 .= "<a href=\"?m=special&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
							$tmpstr2 .= "<a title=\"count: $count\" href=\"?m=special&action=list&catid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
							$tmpstr2 .= "<a href=\"?m=category&action=edit&catid=$cid&chanid=$chanid\"><img src=\"misc/images/icons/option.gif\" /></a>";
							$tmpstr2 .= "</li>";
							if(isset(phpcom::$G['cache'][$cachename][$cid])) {
								foreach(phpcom::$G['cache'][$cachename][$cid] as $cid => $category){
									$count = isset($category['counts']) ? $category['counts'] : 0;
									$tmpstr3 .= "<li>";
									$tmpstr3 .= "<a href=\"?m=special&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
									$tmpstr3 .= "<a title=\"count: $count\" href=\"?m=special&action=list&catid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
									$tmpstr3 .= "<a href=\"?m=category&action=edit&catid=$cid&chanid=$chanid\"><img src=\"misc/images/icons/option.gif\" /></a>";
									$tmpstr3 .= "</li>";
								}
							}
						}
					}
				}
				if($tmpstr1){
					$adminhtml->table_td(array(
							array("<ul class=\"lstcat lst1\">$tmpstr1</ul>", TRUE)
					));
					if($tmpstr2){
						$adminhtml->table_td(array(
								array("<ul class=\"lstcat lst2\">$tmpstr2</ul>", TRUE)
						));
					}
					if($tmpstr3){
						$adminhtml->table_td(array(
								array("<ul class=\"lstcat lst3\">$tmpstr3</ul>", TRUE)
						));
					}
				}
			}
		}
	}else{
		$adminhtml->table_td(array(
				array('special_not_found_category', FALSE)
		));
	}
	$adminhtml->table_end();
}
admin_footer();
?>