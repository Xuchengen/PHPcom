<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This photoware is the proprietary information of PHPcom.
 * This File   : photo.php  2012-8-15
 */
!defined('IN_PHPCOM') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'photo';

$chanid = isset(phpcom::$G['gp_chanid']) ? intval(phpcom::$G['gp_chanid']) : 3;
$chanid = $chanid ? $chanid : 3;
phpcom::$G['channelid'] = $chanid;
$uid = phpcom::$G['uid'];
if(!isset(phpcom::$G['channel'][$chanid])){
	admin_message('undefined_action');
}
phpcom::$G['cache']['channel'] = phpcom::$G['channel'][$chanid];
$namevar = array('name' => phpcom::$G['cache']['channel']['subname'], 'chanid' => $chanid);

phpcom::$G['langvar'] = $namevar;
$status = isset(phpcom::$G['gp_status']) ? intval(phpcom::$G['gp_status']) : 1;
$current = '';
$active = 'first';
if ($action == 'add' || $action == 'edit') {
	$current = "menu_photo_$action";
	$active = $action == 'add' ? 'add' : 'first';
}elseif($status == 'list'){
	if ($status === 0) {
		$current = 'menu_photo_audit';
		$active = 'audit';
	}
}

admin_header('menu_photo', $current, $namevar);
//$firsturl = $action ? "chanid=$chanid" : "action=list&chanid=$chanid";
$navarray = array(
		array('title' => 'menu_photo', 'url' => "?m=photo&action=list&chanid=$chanid", 'name' => 'first'),
		array('title' => 'menu_photo_add', 'url' => "?m=photo&action=add&chanid=$chanid", 'name' => 'add'),
		array('title' => 'menu_photo_audit', 'url' => "?m=photo&action=list&status=0&chanid=$chanid", 'name' => 'audit'),
		array('title' => 'menu_category', 'url' => "?m=category&nav=photo&chanid=$chanid", 'name' => 'category'),
		array('title' => 'menu_channel', 'url' => "?m=channel&action=edit&chanid=$chanid", 'name' => 'channel')
);
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->setvars($namevar);
$adminhtml->activetabs('topic');
$adminhtml->navtabs($navarray, $active, 'nav_tabs', 'photo');
if ($action == 'edit' || $action == 'add') {
	$tid = isset(phpcom::$G['gp_tid']) ? intval(phpcom::$G['gp_tid']) : 0;
	include loadlibfile('edit', 'admincp/photo');
} elseif ($action == 'del') {
	if(!phpcom_admincp::permission('thread_delete')){
		admin_message('action_delete_denied');
	}
	$tid = intval(phpcom::$G['gp_tid']);
	include_once loadlibfile('delete');
	if (delete_photo_thread($tid)) {
		phpcom_cache::updater('syscount', $chanid);
		admin_succeed('threads_delete_succeed', "m=photo&action=list&chanid=$chanid");
	} else {
		admin_message('threads_delete_failed', "m=photo&action=list&chanid=$chanid");
	}
} elseif ($action == 'audit') {
	if($tid = intval(phpcom::$G['gp_tid'])){
		DB::update('threads', array('status' => 1), "tid=$tid");
	}
	phpcom::header('Location: ' . $_SERVER['HTTP_REFERER']);
}elseif($action == 'list' || $action == 'search'){
	include loadlibfile('list', 'admincp/photo');
} elseif ($action == 'upcount') {
	$catid = isset(phpcom::$G['gp_catid']) ? intval(phpcom::$G['gp_catid']) : 0;
	$rootid = isset(phpcom::$G['gp_rootid']) ? intval(phpcom::$G['gp_rootid']) : 0;
	if (!checksubmit(array('btnsubmit', 'submit'))) {
		$msgargs = array(
				'form' => true,
				'loading' => true,
				'autosubmit' => true,
				'action' => "?m=photo&action=upcount&catid=$catid&rootid=$rootid&chanid=$chanid"
		);
		$extra = '<input type="hidden" name="btnsubmit" value="yes" />';
		admin_showmessage('update_category_count_now', null, $msgargs, $extra);
	}else{
		update_category_counts($chanid, $catid, $rootid);
		admin_succeed('update_category_count_succeed', "m=photo&chanid=$chanid");
	}
} else {
	$adminhtml->table_header('photo_admin');
	$adminhtml->table_td(array(array('photo_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
	$adminhtml->table_th(array(
			array('photo_category_all', 'class="left"'),
	));
	$cachename = "category_$chanid";
	phpcom_cache::load($cachename);
	if(isset(phpcom::$G['cache'][$cachename][0])) {
		foreach(phpcom::$G['cache'][$cachename][0] as $cid => $category){
			$count = isset($category['counts']) ? $category['counts'] : 0;
			$tmpstr = "<a href=\"?m=photo&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
			$tmpstr .= "<a title=\"count: $count\" href=\"?m=photo&action=list&rootid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
			$tmpstr .= "<a href=\"?m=category&action=edit&catid=$cid&chanid=$chanid\"><img src=\"misc/images/icons/option.gif\" /></a>";
			$tmpstr .= " <span class=\"btntxt\"><a href=\"?m=photo&action=upcount&rootid=$cid&chanid=$chanid\" style=\"font-weight:400;color:#ff6600\">".adminlang('update_count')."</a></span>";
			$adminhtml->table_td(array(
					array($tmpstr, TRUE, 'colspan="6"')
			), '', FALSE, ' tablerow', NULL, FALSE);
			if(isset(phpcom::$G['cache'][$cachename][$cid])) {
				$tmpstr1 = $tmpstr2 = $tmpstr3 = "";
				foreach(phpcom::$G['cache'][$cachename][$cid] as $cid => $category){
					$count = isset($category['counts']) ? $category['counts'] : 0;
					$tmpstr1 .= "<li>";
					$tmpstr1 .= "<a href=\"?m=photo&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
					$tmpstr1 .= "<a title=\"count: $count\" href=\"?m=photo&action=list&catid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
					$tmpstr1 .= "<a href=\"?m=category&action=edit&catid=$cid&chanid=$chanid\"><img src=\"misc/images/icons/option.gif\" /></a>";
					$tmpstr1 .= "</li>";
					if(isset(phpcom::$G['cache'][$cachename][$cid])) {
						foreach(phpcom::$G['cache'][$cachename][$cid] as $cid => $category){
							$count = isset($category['counts']) ? $category['counts'] : 0;
							$tmpstr2 .= "<li>";
							$tmpstr2 .= "<a href=\"?m=photo&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
							$tmpstr2 .= "<a title=\"count: $count\" href=\"?m=photo&action=list&catid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
							$tmpstr2 .= "<a href=\"?m=category&action=edit&catid=$cid&chanid=$chanid\"><img src=\"misc/images/icons/option.gif\" /></a>";
							$tmpstr2 .= "</li>";
							if(isset(phpcom::$G['cache'][$cachename][$cid])) {
								foreach(phpcom::$G['cache'][$cachename][$cid] as $cid => $category){
									$count = isset($category['counts']) ? $category['counts'] : 0;
									$tmpstr3 .= "<li>";
									$tmpstr3 .= "<a href=\"?m=photo&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
									$tmpstr3 .= "<a title=\"count: $count\" href=\"?m=photo&action=list&catid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
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
				array('photo_not_found_category', FALSE)
		));
	}
	$adminhtml->table_end();
}
admin_footer();

function select_author_source($varname, $id = 'authortext', $value = '') {
	$options = adminlang('photo_select_' . $varname, phpcom::$G['langvar']) . ',';
	$options = explode(',', $options . phpcom::$G['cache']['channel'][$varname]);
	$value = htmlcharsencode($value);
	$s = ' <select class="select" name="sel' . $id . '" onChange="' . $id . '.value=this.value;">';
	if (is_array($options)) {
		foreach ($options as $key => $val) {
			$s .= '<option value="' . $val . '"';
			$s .= $val == $value ? ' SELECTED' : '';
			$s .= '>' . $val;
			$s .= "</option>\r\n";
		}
	}
	$s .= "</select>\r\n";
	return $s;
}

?>