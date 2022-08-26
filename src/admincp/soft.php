<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : soft.php    2011-5-15 7:56:04
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'soft';

$chanid = isset(phpcom::$G['gp_chanid']) ? intval(phpcom::$G['gp_chanid']) : 2;
$chanid = $chanid ? $chanid : 2;
phpcom::$G['channelid'] = $chanid;
if(!isset(phpcom::$G['channel'][$chanid])){
	admin_message('undefined_action');
}
$uid = phpcom::$G['uid'];
phpcom::$G['cache']['channel'] = phpcom::$G['channel'][$chanid];
$namevar = array('name' => phpcom::$G['cache']['channel']['subname'], 'chanid' => $chanid);
$deftable = intval(phpcom::$G['cache']['channel']['deftable']);
phpcom::$G['langvar'] = $namevar;
$status = isset(phpcom::$G['gp_status']) ? intval(phpcom::$G['gp_status']) : 1;
$current = '';
$active = 'first';
if ($action == 'add' || $action == 'edit') {
	$current = "menu_soft_$action";
	$active = $action == 'add' ? 'add' : 'first';
}elseif ($action == 'softtest') {
	$current = 'menu_soft_softtest';
	$active = 'soft_setting_softtest';
}elseif ($action == 'list') {
	if ($status === 0) {
		$current = 'menu_soft_audit';
		$active = 'audit';
	}
}

admin_header('menu_soft', $current, $namevar);
//$firsturl = $action ? "chanid=$chanid" : "action=list&chanid=$chanid";
$navarray = array(
		array('title' => 'menu_soft', 'url' => "?m=soft&action=list&chanid=$chanid", 'name' => 'first'),
		array('title' => 'menu_soft_add', 'url' => "?m=soft&action=add&chanid=$chanid", 'name' => 'add'),
		array('title' => 'menu_soft_audit', 'url' => "?m=soft&action=list&status=0&chanid=$chanid", 'name' => 'audit'),
		array('title' => 'menu_downserver', 'url' => "?m=downserver&chanid=$chanid", 'name' => 'downserver'),
		array('title' => 'menu_category', 'url' => "?m=category&nav=soft&chanid=$chanid", 'name' => 'category'),
		array('title' => 'menu_soft_softtest', 'url' => "?m=soft&action=softtest&chanid=$chanid", 'name' => 'soft_setting_softtest'),
		array('title' => 'menu_channel', 'url' => "?m=channel&action=edit&chanid=$chanid", 'name' => 'channel')
);
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->setvars($namevar);
$adminhtml->activetabs('topic');
$adminhtml->navtabs($navarray, $active, 'nav_tabs', 'soft');
if ($action == 'edit' || $action == 'add' || $action == 'quickedit') {
	include loadlibfile('edit', 'admincp/soft');
}elseif($action == 'savequickedit'){
	$tid = isset(phpcom::$G['gp_tid']) ? intval(phpcom::$G['gp_tid']) : 0;
	$threads = striptags(phpcom::$G['gp_threads']);
	$softinfo = striptags(phpcom::$G['gp_softinfo']);
	$contents = isset(phpcom::$G['gp_contents']) ? striptags(phpcom::$G['gp_contents']) : null;
	$uid = intval(phpcom::$G['uid']);
	$threads['title'] = trim($softinfo['softname'] . ' ' . $softinfo['softversion']);
	
	foreach(array('topline', 'focus', 'bancomment', 'digest', 'status', 'locked', 'istop') as $key){
		if(isset($threads[$key])){
			$threads[$key] = intval($threads[$key]);
		}else{
			$threads[$key] = 0;
		}
	}
	
	$highlights = phpcom::$G['gp_highlights'];
	$threads['highlight'] = intval($highlights['font'] . $highlights['color']);
	$softinfo['homepage'] = checkurlhttp($softinfo['homepage']);
	$softsize = floatval($softinfo['softsize']);
	$sizeunit = intval(phpcom::$G['gp_sizeunit']);
	if ($sizeunit === 1) {
		$softsize *= 1024;
	} elseif ($sizeunit === 2) {
		$softsize *= 1048576;
	}
	$softinfo['softsize'] = intval($softsize);
	$post = new DataAccess_PostThread($chanid);
	$softinfo['runsystem'] = $post->formatRunSystem($softinfo['runsystem']);
	$softinfo['star'] = max(1, min(5, $softinfo['star']));
	$isupdate = 0;
	$retstatus = false;
	if (isset(phpcom::$G['gp_updatenow']) && phpcom::$G['gp_updatenow']) {
		$isupdate = $threadfields['isupdate'] = 1;
	}
	$threads = convert_encoding($threads, 'UTF-8');
	$softinfo = convert_encoding($softinfo, 'UTF-8');
	$contents = convert_encoding($contents, 'UTF-8');

	if($thread = $post->update($tid, $threads, null, $softinfo, $contents)){
		$retstatus = true;
	}else{
		$retstatus = false;
	}
	ob_end_clean();
	ob_start();
	//@header('Content-Type: text/xml;charset=' . CHARSET);
	@header('Expires: -1');
	@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
	@header('Pragma: no-cache');
	echo json_encode(array('status' => $retstatus));
	$contents = ob_get_contents();
	ob_end_clean();
	echo $contents;
	exit();
	
} elseif ($action == 'del') {
	if(!phpcom_admincp::permission('thread_delete')){
		admin_message('action_delete_denied');
	}
	$tid = intval(phpcom::$G['gp_tid']);
	include_once loadlibfile('delete');
	if (delete_softinfo_thread($tid)) {
		phpcom_cache::updater('syscount', $chanid);
		admin_succeed('threads_delete_succeed', "m=soft&action=list&chanid=$chanid");
	} else {
		admin_message('threads_delete_failed', "m=soft&action=list&chanid=$chanid");
	}
} elseif ($action == 'audit') {
	if($tid = intval(phpcom::$G['gp_tid'])){
		DB::update('threads', array('status' => 1), "tid=$tid");
	}
	header('Location: ' . $_SERVER['HTTP_REFERER']);
} elseif ($action == 'softtest') {
	include loadlibfile('test', 'admincp/soft');
} elseif ($action == 'view') {
	$tid = isset(phpcom::$G['gp_tid']) ? intval(phpcom::$G['gp_tid']) : 0;
	$threads = array();
	$contents = array();
	if ($tid) {
		$threads = DB::fetch_first("SELECT t.*,s.* FROM " . DB::table('threads') . " t
				LEFT JOIN " . DB::table('soft_thread') . " s USING(tid)
				WHERE t.tid='$tid'");
		$tableindex = $threads['tableindex'];
		$contents = DB::fetch_first("SELECT * FROM " . DB::table('soft_content', $tableindex) . " WHERE tid='$tid'");
		$attachids = array();
		if ($threads['attached']) {
			if (preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $contents['content'], $matchaids)) {
				$attachids = $matchaids[1];
			}
		}
	}
	echo '<script type="text/javascript">loadscript("misc/js/shCore.js","' . $charset . '");';
	echo 'loadscript("misc/js/shLang.js","' . $charset . '");loadcss("misc/css/shCoreDefault.css");</script>';
	$adminhtml->table_header('soft_view', 4);
	$title = '<a href="?action=edit&m=soft&tid=' . $threads['tid'] . '&chanid=' . $chanid . '"><font size="4">' . $threads['title'] . '</font></a>';
	$adminhtml->table_td(array(
			array($title, TRUE, 'align="center" colspan="4"')
	), '', FALSE, '', '', FALSE);
	$dateline = adminlang('date') . ' ' . fmdate($threads['dateline'], 'Y-m-d H:i:s');
	$hits = adminlang('hits') . ' ' . $threads['hits'];
	$adminhtml->table_td(array(
			array($dateline . ' ' . $hits, TRUE, 'align="center" colspan="4"')
	), '', FALSE, '', '', FALSE);
	$threads['softsize'] = formatbytes(intval($threads['softsize']) * 1024);
	$star = intval($threads['star']);
	$threads['star'] = adminlang("star_$star");
	$adminhtml->table_td(array(
			array('soft_softsize', FALSE, 'width="10%"', '', TRUE),
			array($threads['softsize'] . ' ' . $threads['star'], TRUE, 'width="40%"'),
			array('soft_softlang', FALSE, 'width="10%"', '', TRUE),
			array($threads['softlang'], TRUE, 'width="40%"')
	), '', FALSE, '', '', FALSE);
	$adminhtml->table_td(array(
			array('soft_runsystem', FALSE, '', '', TRUE),
			array($threads['runsystem'], TRUE),
			array('soft_softtype', FALSE, '', '', TRUE),
			array($threads['softtype'] . ' / ' . $threads['license'], TRUE)
	), '', FALSE, '', '', FALSE);
	$adminhtml->table_td(array(
			array('soft_homepage', FALSE, '', '', TRUE),
			array($threads['homepage'], TRUE),
			array('soft_contact', FALSE, '', '', TRUE),
			array($threads['contact'], TRUE)
	), '', FALSE, '', '', FALSE);

	$content = bbcode::bbcode2html($contents['content']);
	$content = bbcode::parser_attach($attachids, $content, phpcom::$G['cache']['channel']['imagemode']);
	$adminhtml->table_td(array(
			array($content, TRUE, 'colspan="4"', '', 'textcontent')
	), '', FALSE, '', '', FALSE);
	$adminhtml->table_td(array(
			array('soft_softdown', FALSE, 'align="left" colspan="4"')
	), '', FALSE, '', '', FALSE);
	$adminhtml->table_td(array(
			array(showdown_address($tid), TRUE, 'align="left" colspan="4"')
	), '', FALSE, '', '', FALSE);
	$adminhtml->table_end();
}elseif($action == 'list' || $action == 'search'){
	include loadlibfile('list', 'admincp/soft');
} elseif ($action == 'upcount') {
	$catid = isset(phpcom::$G['gp_catid']) ? intval(phpcom::$G['gp_catid']) : 0;
	$rootid = isset(phpcom::$G['gp_rootid']) ? intval(phpcom::$G['gp_rootid']) : 0;
	if (!checksubmit(array('btnsubmit', 'submit'))) {
		$msgargs = array(
				'form' => true,
				'loading' => true,
				'autosubmit' => true,
				'action' => "?m=soft&action=upcount&catid=$catid&rootid=$rootid&chanid=$chanid"
		);
		$extra = '<input type="hidden" name="btnsubmit" value="yes" />';
		admin_showmessage('update_category_count_now', null, $msgargs, $extra);
	}else{
		update_category_counts($chanid, $catid, $rootid);
		admin_succeed('update_category_count_succeed', "m=soft&chanid=$chanid");
	}
} else {
	$adminhtml->table_header('soft_admin');
	$adminhtml->table_td(array(array('soft_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
	$adminhtml->table_th(array(
			array('soft_category_all', 'class="left"'),
	));
	$cachename = "category_$chanid";
	phpcom_cache::load($cachename);
	if(isset(phpcom::$G['cache'][$cachename][0])) {
		foreach(phpcom::$G['cache'][$cachename][0] as $cid => $category){
			$count = isset($category['counts']) ? $category['counts'] : 0;
			$tmpstr = "<a href=\"?m=soft&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
			$tmpstr .= "<a title=\"count: $count\" href=\"?m=soft&action=list&rootid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
			$tmpstr .= "<a href=\"?m=category&action=edit&catid=$cid&chanid=$chanid\"><img src=\"misc/images/icons/option.gif\" /></a>";
			$tmpstr .= " <span class=\"btntxt\"><a href=\"?m=soft&action=upcount&rootid=$cid&chanid=$chanid\" style=\"font-weight:400;color:#ff6600\">".adminlang('update_count')."</a></span>";
			$adminhtml->table_td(array(
					array($tmpstr, TRUE, 'colspan="6"')
			), '', FALSE, ' tablerow', NULL, FALSE);
			if(isset(phpcom::$G['cache'][$cachename][$cid])) {
				$tmpstr1 = $tmpstr2 = $tmpstr3 = "";
				foreach(phpcom::$G['cache'][$cachename][$cid] as $cid => $category){
					$count = isset($category['counts']) ? $category['counts'] : 0;
					$tmpstr1 .= "<li>";
					$tmpstr1 .= "<a href=\"?m=soft&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
					$tmpstr1 .= "<a title=\"count: $count\" href=\"?m=soft&action=list&catid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
					$tmpstr1 .= "<a href=\"?m=category&action=edit&catid=$cid&chanid=$chanid\"><img src=\"misc/images/icons/option.gif\" /></a>";
					$tmpstr1 .= "</li>";
					if(isset(phpcom::$G['cache'][$cachename][$cid])) {
						foreach(phpcom::$G['cache'][$cachename][$cid] as $cid => $category){
							$count = isset($category['counts']) ? $category['counts'] : 0;
							$tmpstr2 .= "<li>";
							$tmpstr2 .= "<a href=\"?m=soft&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
							$tmpstr2 .= "<a title=\"count: $count\" href=\"?m=soft&action=list&catid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
							$tmpstr2 .= "<a href=\"?m=category&action=edit&catid=$cid&chanid=$chanid\"><img src=\"misc/images/icons/option.gif\" /></a>";
							$tmpstr2 .= "</li>";
							if(isset(phpcom::$G['cache'][$cachename][$cid])) {
								foreach(phpcom::$G['cache'][$cachename][$cid] as $cid => $category){
									$count = isset($category['counts']) ? $category['counts'] : 0;
									$tmpstr3 .= "<li>";
									$tmpstr3 .= "<a href=\"?m=soft&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
									$tmpstr3 .= "<a title=\"count: $count\" href=\"?m=soft&action=list&catid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
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
				array('soft_not_found_category', FALSE)
		));
	}
	$adminhtml->table_end();
}
admin_footer();

function select_softinfo_option($varname, $id = 'runsystem', $value = '') {
	$options = adminlang('soft_select_' . $varname) . ',' . phpcom::$G['cache']['channel'][$varname];
	$options = explode(',', $options);
	$value = htmlcharsencode($value);
	if ($id == 'runsystem') {
		$onchange = 'changeRunSystem(this.value)';
	} else {
		$onchange = $id . 'text.value=this.value;';
	}
	$s = ' <select class="select" name="sel' . $id . '" onChange="' . $onchange . '">';
	if (is_array($options)) {
		foreach ($options as $key => $val) {
			$s .= '<option value="' . ($key ? $val : '') . '"';
			$s .= $val == $value ? ' SELECTED' : '';
			$s .= '>' . $val;
			$s .= "</option>\r\n";
		}
	}
	$s .= "</select>\r\n";
	return $s;
}

function select_properties($name = 'softlang', $value = '') {
	$options = explode(',', phpcom::$G['cache']['channel'][$name]);
	$label = adminlang('soft_select_' . $name, phpcom::$G['langvar']);
	$varname = "softinfo[$name]";
	$s = ' <select class="select" name="' . $varname . '" title="' . $label . '">';
	$s .= '<optgroup label="' . $label . '">';
	if (is_array($options)) {
		foreach ($options as $key => $val) {
			$s .= '<option value="' . $val . '"';
			if ($value) {
				$s .= $val == $value ? ' SELECTED' : '';
			} else {
				$s .= $key == 0 ? ' SELECTED' : '';
			}
			$s .= '>' . $val;
			$s .= "</option>\r\n";
		}
	}
	$s .= "</optgroup></select>\r\n";
	return $s;
}

function select_softauth($name = 'softauth', $value = 0) {
	$options = explode(',', phpcom::$G['cache']['channel'][$name]);
	$label = adminlang('soft_select_' . $name, phpcom::$G['langvar']);
	$varname = "softinfo[$name]";
	$s = ' <select class="select" name="' . $varname . '" title="' . $label . '">';
	$s .= '<optgroup label="' . $label . '">';
	if (is_array($options)) {
		foreach ($options as $key => $val) {
			$s .= '<option value="' . $key . '"';
			if ($value) {
				$s .= $key == $value ? ' SELECTED' : '';
			} else {
				$s .= $key == 0 ? ' SELECTED' : '';
			}
			$s .= '>' . $val;
			$s .= "</option>\r\n";
		}
	}
	$s .= "</optgroup></select>\r\n";
	return $s;
}

function select_softstar($name = 'star', $value = 0) {
	$options = adminlang('soft_' . $name . '_option');
	$defvalue = $name == 'star' ? 3 : 0;
	$label = adminlang('soft_' . $name);
	$varname = "softinfo[$name]";
	$s = ' <select class="select" name="' . $varname . '" title="' . $label . '">';
	$s .= '<optgroup label="' . $label . '">';
	if (is_array($options)) {
		foreach ($options as $key => $val) {
			$s .= '<option value="' . $key . '"';
			if ($value) {
				$s .= $key == $value ? ' SELECTED' : '';
			} else {
				$s .= $key == $defvalue ? ' SELECTED' : '';
			}
			$s .= '>' . $val;
			$s .= "</option>\r\n";
		}
	}
	$s .= "</optgroup></select>\r\n";
	return $s;
}

function get_softdown_edit($adminhtml, $chanid = 2, $tid = 0, $action = 'add', $downserver = array()) {
	if ($action != 'edit') {
		return '';
	}
	$s = '';
	$sql = "SELECT * FROM " . DB::table('soft_download') . " WHERE tid='$tid' ORDER BY id";
	$query = DB::query($sql);
	while ($row = DB::fetch_array($query)) {
		$s .= '<input type="hidden" name="softdownedit[softdownid][]" value="' . intval($row['id']) . '" />';
		$s .= '<div><p class="downin">' . select_downserver($downserver, intval($row['servid']), 'edit', 'softdownedit[servid][]');
		$s .= $adminhtml->textinput('softdownedit[downurl][]', $row['downurl'], 60, '', '', 'soft_downurl_title') . " ";
		$s .= $adminhtml->textinput('softdownedit[dname][]', $row['dname'], 20, '', '', 'soft_dname_title');
		$s .= "</p></div>\r\n";
	}
	return $s;
}

function get_downserver($chanid = 2) {
	$results = array();
	$sql = "SELECT * FROM " . DB::table('downserver') . " WHERE depth='0' ORDER BY sortord";
	$query = DB::query($sql);
	while ($row = DB::fetch_array($query)) {
		$results[] = $row;
	}
	return $results;
}

function select_downserver($array, $servid = 0, $action = 'add', $name = 'softdown[servid][]') {
	$name = $name ? $name : 'softdown[servid][]';
	$option = '<select name="' . $name . '" class="select">';
	$i = 0;
	foreach ($array as $key => $row) {
		$option .= '<option value="' . $row['servid'] . '"';
		$option .= ( $row['servid'] == $servid || ($i == 0 && $action == 'add' && $servid == 0)) ? ' SELECTED' : '';
		$option .= ">{$row['servname']}</option>";
		$i++;
	}
	$selected = '';
	if (!$i || ($action == 'edit' && $servid == 0)) {
		$selected = ' SELECTED';
	}
	$option .= '<option value="0" style="color: red;"' . $selected . '>' . adminlang('soft_select_downserver') . '</option>';
	$option .= "</select>\r\n";
	return $option;
}

function select_down_num() {
	$option = '<select name="down_num" class="select" onChange="showdownload(this.value)">';
	$option .= '<option value="0">' . adminlang('soft_select_downnum') . '</option>';
	for ($index = 1; $index < 16; $index++) {
		$option .= '<option value="' . $index . '">' . $index . '</option>';
	}
	$option .= "</select>\r\n";
	return $option;
}

function fetchdownserver($servid) {
	$subserver1 = $subserver2 = $data = array();
	$sql = "SELECT * FROM " . DB::table('downserver') . " WHERE rootid='$servid' ORDER BY sortord";
	$query = DB::query($sql);
	while ($row = DB::fetch_array($query)) {
		if ($row['depth'] == 0) {
			$data[] = $row;
		} elseif ($row['depth'] == 1) {
			$subserver1[$row['parentid']][] = $row;
		} else {
			$subserver2[$row['parentid']][] = $row;
		}
	}
	DB::free_result($query);
	if(isset($subserver1[$servid])) {
		foreach ($subserver1[$servid] as $key => $row) {
			$data[] = $row;
			if(isset($subserver2[$row['servid']])) {
				foreach ($subserver2[$row['servid']] as $key => $row) {
					$data[] = $row;
				}
			}
		}
	}
	unset($subserver1);
	unset($subserver2);
	return $data;
}

function showdown_address($tid) {
	$downarray = array();
	$subserver1 = $subserver2 = array();
	$sql = "SELECT * FROM " . DB::table('soft_download') . " WHERE tid='$tid' ORDER BY id";
	$query = DB::query($sql);
	while ($row = DB::fetch_array($query)) {
		$downarray[] = $row;
	}
	DB::free_result($query);
	$servid = 0;
	$todaytime = strtotime(fmdate(TIMESTAMP, 'YmdHis'));
	$s = '<dl class="downlist">';
	foreach ($downarray as $key => $downrow) {
		$servid = $downrow['servid'];
		if ($servid) {
			foreach (fetchdownserver($servid) as $row) {
				if ($row['depth']) {
					if ($row['expires']) {
						$row['isclosed'] = $row['expires'] > $todaytime ? 0 : 1;
					} else {
						$row['isclosed'] = 0;
					}
					if ($row['child']) {
						$s .= '<dt>' . $row['servname'] . '</dt>';
					} else {
						if ($row['redirect']) {
							$row['downurl'] = $row['servurl'];
						} else {
							$row['downurl'] = $row['servurl'] . $downrow['downurl'];
						}
						if ($row['isclosed']) {
							$row['downurl'] = adminlang('soft_closed_tops');
							$s .= '<dd><em>' . $row['servname'] . '</em> ' . $row['downurl'] . '</dd>';
						} else {
							$s .= '<dd><em>' . $row['servname'] . '</em> <a href="' . $row['downurl'] . '">' . $row['downurl'] . '</a></dd>';
						}
					}
				}
			}
		} else {
			$s .= '<dt>' . $downrow['dname'] . '</dt>';
			$s .= '<dd><a href="' . $downrow['downurl'] . '">' . $downrow['downurl'] . '</a></dd>';
		}
	}
	$s .= "</dl>\r\n";
	return $s;
}

?>
