<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This videoware is the proprietary information of PHPcom.
 * This File   : video.php  2012-8-15
 */
!defined('IN_PHPCOM') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'video';

$chanid = isset(phpcom::$G['gp_chanid']) ? intval(phpcom::$G['gp_chanid']) : 5;
$chanid = $chanid ? $chanid : 5;
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
	$current = "menu_video_$action";
	$active = $action == 'add' ? 'add' : 'first';
}elseif ($action == 'list') {
	if ($status === 0) {
		$current = 'menu_video_audit';
		$active = 'audit';
	}
}elseif($action == 'player'){
	$current = 'menu_video_player';
	$active = 'player';
}
admin_header('menu_video', $current, $namevar);
//$firsturl = $action ? "chanid=$chanid" : "action=list&chanid=$chanid";
$navarray = array(
		array('title' => 'menu_video', 'url' => "?m=video&action=list&chanid=$chanid", 'name' => 'first'),
		array('title' => 'menu_video_add', 'url' => "?m=video&action=add&chanid=$chanid", 'name' => 'add'),
		array('title' => 'menu_video_audit', 'url' => "?m=video&action=list&status=0&chanid=$chanid", 'name' => 'audit'),
		array('title' => 'menu_video_player', 'url' => "?m=video&action=player&chanid=$chanid", 'name' => 'player'),
		array('title' => 'menu_category', 'url' => "?m=category&nav=video&chanid=$chanid", 'name' => 'category'),
		array('title' => 'menu_channel', 'url' => "?m=channel&action=edit&chanid=$chanid", 'name' => 'channel')
);
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->setvars($namevar);
$adminhtml->activetabs('topic');
$adminhtml->navtabs($navarray, $active, 'nav_tabs', 'video');
if ($action == 'edit' || $action == 'add') {
	$tid = isset(phpcom::$G['gp_tid']) ? intval(phpcom::$G['gp_tid']) : 0;
	include loadlibfile('edit', 'admincp/video');
} elseif ($action == 'del') {
	if(!phpcom_admincp::permission('thread_delete')){
		admin_message('action_delete_denied');
	}
	$tid = intval(phpcom::$G['gp_tid']);
	include_once loadlibfile('delete');
	if (delete_video_thread($tid)) {
		phpcom_cache::updater('syscount', $chanid);
		admin_succeed('threads_delete_succeed', "m=video&action=list&chanid=$chanid");
	} else {
		admin_message('threads_delete_failed', "m=video&action=list&chanid=$chanid");
	}
} elseif ($action == 'audit') {
	if($tid = intval(phpcom::$G['gp_tid'])){
		DB::update('threads', array('status' => 1), "tid=$tid");
	}
	phpcom::header('Location: ' . $_SERVER['HTTP_REFERER']);
} elseif ($action == 'player') {
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$adminhtml->form("m=video&action=player&chanid=$chanid");
		$adminhtml->table_header('video_player');
		$adminhtml->table_td(array(array('video_player_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end();
		$adminhtml->table_header();
		$adminhtml->table_th(array(
				array('deletecheckbox', 'class="left"'),
				array('video_player_subject', 'class="left"'),
				array('video_player_name', 'class="left"'),
				array('video_player_caption', 'class="left"'),
				array('video_player_url', 'class="left"'),
				array('video_player_status')
		));
		$query = DB::query("SELECT * FROM " . DB::table('video_player') . " ORDER BY playerid ASC");
		while ($row = DB::fetch_array($query)) {
			$playerid = $row['playerid'];
			$checkbox = "name=\"delete[$playerid]\" value=\"$playerid\"";
			if ($playerid <= 10) {
				$checkbox = 'name="delete[0]" disabled';
			}
			$adminhtml->table_td(array(
					array('<input class="checkbox" type="checkbox" title="ID: '.$playerid.'" ' . $checkbox . ' />', TRUE),
					array($adminhtml->textinput("players[$playerid][subject]", $row['subject'], 15, 'left'), TRUE),
					array($adminhtml->textinput("players[$playerid][name]", $row['name'], 10), TRUE),
					array($adminhtml->textinput("players[$playerid][caption]", $row['caption'], 30), TRUE),
					array($adminhtml->textinput("players[$playerid][url]", $row['url'], 30), TRUE, ''),
					array('<input class="checkbox" type="checkbox" name="players[' . $playerid . '][status]" value="1"'.($row['status'] ? ' checked' : '').'/>', TRUE, 'align="center"')
			));
		}
		$adminhtml->table_td(array(
				array('new', FALSE, 'align="center"'),
				array($adminhtml->textinput("playernew[subject]", '', 15, 'left'), TRUE),
				array($adminhtml->textinput("playernew[name]", '', 10), TRUE),
				array($adminhtml->textinput("playernew[caption]", '', 35), TRUE),
				array($adminhtml->textinput("playernew[url]", '', 35), TRUE, 'colspan="2"')
		));
		$adminhtml->table_td(array(
				array('&nbsp;', TRUE, 'align="center"'),
				array($adminhtml->submit_button(), TRUE, 'colspan="5"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
	}else{
		if(isset(phpcom::$G['gp_players'])){
			$players = phpcom::$G['gp_players'];
			foreach ($players as $playerid => $player){
				if(empty($player['subject'])){
					unset($player['subject']);
				}
				if(empty($player['name'])){
					unset($player['name']);
				}
				$player['status'] = isset($player['status']) ? 1 : 0;
				DB::update('video_player', $player, "playerid='$playerid'");
			}
		}
		if(isset(phpcom::$G['gp_delete'])){
			if($delete = phpcom::$G['gp_delete']){
				$deleteids = array();
				foreach ($delete as $playerid) {
					if ($playerid > 10) {
						$deleteids[] = $playerid;
					}
				}
				if ($deleteids) {
					$playerids = implodeids($deleteids);
					DB::delete('video_player', "playerid in($playerids)");
				}
			}
		}
		$playernew = isset(phpcom::$G['gp_playernew']) ? phpcom::$G['gp_playernew'] : '';
		if($playernew['name'] && $playernew['subject']){
			$playernew['playerid'] = intval(DB::result_first("SELECT MAX(playerid) FROM " . DB::table('video_player'))) + 1;
			$playernew['status'] = 1;
			DB::insert('video_player', $playernew);
		}
		phpcom_cache::updater('player');
		admin_succeed('video_player_update_succeed', "m=video&action=player&chanid=$chanid");
	}
}elseif($action == 'list' || $action == 'search'){
	include loadlibfile('list', 'admincp/video');
} elseif ($action == 'upcount') {
	$catid = isset(phpcom::$G['gp_catid']) ? intval(phpcom::$G['gp_catid']) : 0;
	$rootid = isset(phpcom::$G['gp_rootid']) ? intval(phpcom::$G['gp_rootid']) : 0;
	if (!checksubmit(array('btnsubmit', 'submit'))) {
		$msgargs = array(
				'form' => true,
				'loading' => true,
				'autosubmit' => true,
				'action' => "?m=video&action=upcount&catid=$catid&rootid=$rootid&chanid=$chanid"
		);
		$extra = '<input type="hidden" name="btnsubmit" value="yes" />';
		admin_showmessage('update_category_count_now', null, $msgargs, $extra);
	}else{
		update_category_counts($chanid, $catid, $rootid);
		admin_succeed('update_category_count_succeed', "m=video&chanid=$chanid");
	}
} else {
	$adminhtml->table_header('video_admin');
	$adminhtml->table_td(array(array('video_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
	$adminhtml->table_th(array(
			array('video_category_all', 'class="left"'),
	));
	$cachename = "category_$chanid";
	phpcom_cache::load($cachename);
	if(isset(phpcom::$G['cache'][$cachename][0])) {
		foreach(phpcom::$G['cache'][$cachename][0] as $cid => $category){
			$count = isset($category['counts']) ? $category['counts'] : 0;
			$tmpstr = "<a href=\"?m=video&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
			$tmpstr .= "<a title=\"count: $count\" href=\"?m=video&action=list&rootid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
			$tmpstr .= "<a href=\"?m=category&action=edit&catid=$cid&chanid=$chanid\"><img src=\"misc/images/icons/option.gif\" /></a>";
			$tmpstr .= " <span class=\"btntxt\"><a href=\"?m=video&action=upcount&rootid=$cid&chanid=$chanid\" style=\"font-weight:400;color:#ff6600\">".adminlang('update_count')."</a></span>";
			$adminhtml->table_td(array(
					array($tmpstr, TRUE, 'colspan="6"')
			), '', FALSE, ' tablerow', NULL, FALSE);
			if(isset(phpcom::$G['cache'][$cachename][$cid])) {
				$tmpstr1 = $tmpstr2 = $tmpstr3 = "";
				foreach(phpcom::$G['cache'][$cachename][$cid] as $cid => $category){
					$count = isset($category['counts']) ? $category['counts'] : 0;
					$tmpstr1 .= "<li>";
					$tmpstr1 .= "<a href=\"?m=video&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
					$tmpstr1 .= "<a title=\"count: $count\" href=\"?m=video&action=list&catid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
					$tmpstr1 .= "<a href=\"?m=category&action=edit&catid=$cid&chanid=$chanid\"><img src=\"misc/images/icons/option.gif\" /></a>";
					$tmpstr1 .= "</li>";
					if(isset(phpcom::$G['cache'][$cachename][$cid])) {
						foreach(phpcom::$G['cache'][$cachename][$cid] as $cid => $category){
							$count = isset($category['counts']) ? $category['counts'] : 0;
							$tmpstr2 .= "<li>";
							$tmpstr2 .= "<a href=\"?m=video&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
							$tmpstr2 .= "<a title=\"count: $count\" href=\"?m=video&action=list&catid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
							$tmpstr2 .= "<a href=\"?m=category&action=edit&catid=$cid&chanid=$chanid\"><img src=\"misc/images/icons/option.gif\" /></a>";
							$tmpstr2 .= "</li>";
							if(isset(phpcom::$G['cache'][$cachename][$cid])) {
								foreach(phpcom::$G['cache'][$cachename][$cid] as $cid => $category){
									$count = isset($category['counts']) ? $category['counts'] : 0;
									$tmpstr3 .= "<li>";
									$tmpstr3 .= "<a href=\"?m=video&action=add&catid=$cid&chanid=$chanid\">{$category['catname']}</a> ";
									$tmpstr3 .= "<a title=\"count: $count\" href=\"?m=video&action=list&catid=$cid&chanid=$chanid&count=$count\"><img src=\"misc/images/icons/list.gif\" /></a>";
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
				array('video_not_found_category', FALSE)
		));
	}
	$adminhtml->table_end();
}
admin_footer();

function select_video_year($value = 0){
	$value = intval($value);
	$optionlang = adminlang('video_select_years', phpcom::$G['langvar']);
	$yearlang = adminlang('year');
	$s = ' <select class="select" name="videos[years]" title="' . $optionlang . '">';
	$s .= "<option value=\"0\">$optionlang</option>\r\n";
	$toyear = date('Y') + 3;
	for ($index = $toyear; $index > $toyear - 50; $index--) {
		$s .= "<option value=\"$index\"" . ($index == $value ? ' SELECTED' : '') . ">$index {$yearlang}</option>";
	}
	$s .= "</select>\r\n";
	return $s;
}

function select_video_quality($value = -1, $varname = 'videos[quality]'){
	$value = intval($value);
	$options = phpcom::$G['cache']['channel']['quality'];
	$options = $options ? $options : array('unknown', '480P', '720P', '1080P');
	$label = adminlang('video_select_quality', phpcom::$G['langvar']);
	$s = ' <select class="select" name="' . $varname . '" title="' . $label . '">';
	$s .= '<optgroup label="' . $label . '">';
	$i = 0;
	foreach ($options as $key => $val){
		$s .= '<option value="' . $key . '"';
		if($value == -1){
			$s .= $i == 0 ? ' SELECTED' : '';
		}else{
			$s .= $key == $value ? ' SELECTED' : '';
		}
		$s .= ">$val</option>\r\n";
		$i++;
	}
	$s .= "</optgroup></select>\r\n";
	return $s;
}

function select_video_version($varname, $id = 'versiontext', $value = '') {
	$default = adminlang('video_select_' . $varname, phpcom::$G['langvar']);
	$options = explode(',', trim(phpcom::$G['cache']['channel'][$varname]));
	$value = htmlcharsencode($value);
	$s = ' <select class="select" name="sel' . $id . '" onChange="' . $id . '.value=this.value;">';
	$s .= "<option value=\"\">$default</option>";
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

function select_video_player($varname = 'playernew[]', $value = ''){
	if(!isset(phpcom::$G['cache']['player'])){
		phpcom_cache::load('player');
	}
	$s = ' <select class="select" name="' . $varname . '">';
	$players = phpcom::$G['cache']['player'];
	foreach ($players as $pid => $player){
		if($player['status']){
			$s .= '<option value="' . $pid . '"';
			$s .= $pid == $value ? ' SELECTED' : '';
			$s .= ">".sprintf("%02d", $pid).".{$player['name']}.{$player['subject']}</option>\r\n";
		}
	}
	$s .= "</select>\r\n";
	return $s;
}

function select_country_dialogue($name = 'country', $value = '') {
	$options = explode(',', trim(phpcom::$G['cache']['channel'][$name]));
	$label = adminlang('video_select_' . $name, phpcom::$G['langvar']);
	$varname = "videos[$name]";
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
			$s .= ">$val</option>\r\n";
		}
	}
	$s .= "</optgroup></select>\r\n";
	return $s;
}

function select_author_source($varname, $id = 'authortext', $value = '') {
	$options = adminlang('video_select_' . $varname, phpcom::$G['langvar']) . ',';
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