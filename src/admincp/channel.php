<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : channel.php    2011-4-2 14:21:19
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'channel';

admin_header($admintitle);
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('global');

if ($action == 'edit'){
	$channelid = isset(phpcom::$G['gp_channelid']) ? intval(phpcom::$G['gp_channelid']) : intval(phpcom::$G['gp_chanid']);
	$setting = array();
	if(!$row = DB::fetch_first("SELECT * FROM " . DB::table('channel') . " WHERE channelid='$channelid'")){
		admin_message('undefined_action');
	}
	$chantype = $row['type'];
	if ($row['type'] == 'system' || $row['type'] == 'expand') {
		$setting = unserialized($row['setting']);
	}
	$parentid = intval($row['parentid']);
	if(!isset($setting['resizeimg'])){
		$setting['resizeimg'] = array('status' => 0,'x' => 0, 'y' => 0, 'width' => 0, 'height' => 0, 'minwidth' => 0, 'minheight' => 0);
	}
	if(empty($setting['previewpage'])) $setting['previewpage'] = 0;
	if(empty($setting['previewshow'])) $setting['previewshow'] = 0;
	if(empty($setting['thumbauto'])) $setting['thumbauto'] = 0;
	if(empty($setting['thumbzoom'])) $setting['thumbzoom'] = 0;
	if(empty($setting['previewzoom'])) $setting['previewzoom'] = 0;
	if(empty($setting['dialogue']) && !empty($setting['language'])){
		$setting['dialogue'] = $setting['language'];
	}
	$navarray = array(
			array('title' => 'menu_channel', 'url' => '?m=channel', 'id' => 'channelindex', 'name' => 'index'),
			array('title' => 'channel_basic_setting','id' => 'basicsetting', 'name' => 'first','onclick' => 'toggle_anchor(this)')
	);
	if ($chantype != 'menu') {
		$navarray = array_merge($navarray, array(
				array('title' => 'channel_advanced_setting','id' => 'advancedsetting', 'name' => 'advanced','onclick' => 'toggle_anchor(this)'),
				array('title' => 'channel_attach_setting', 'id' => 'attachsetting', 'name' => 'more', 'onclick' => 'toggle_anchor(this)')

		));
	}
	$adminhtml->navtabs($navarray, 'first');
	if (!checksubmit(array('btnsubmit', 'submit'))) {
		$adminhtml->form("m=channel&action=edit&channelid=$channelid");
		$adminhtml->table_header($row['channelname'] . adminlang('channelsetting'), 3, 'basicsetting');
		$adminhtml->table_setting('channel_channelname', array('channel[channelname]', 'channel[color]'), array($row['channelname'], $row['color']), 'textcolor');
		//$adminhtml->table_setting('channel_channelname', array('channel[channelname]', 'channel[subname]'), array($row['channelname'], $row['subname']), 'text2');
		$adminhtml->table_setting('channel_codename', 'channel[codename]', $row['codename'], 'text');
		$adminhtml->table_setting('channel_sitename', 'channel[sitename]', $row['sitename'], 'text');
		$adminhtml->table_setting('channel_subject', 'channel[subject]', $row['subject'], 'text');
		$adminhtml->table_setting('channel_description', 'channel[description]', $row['description'], 'textarea');
		$adminhtml->table_setting('channel_keyword', 'channel[keyword]', $row['keyword'], 'text');
		$adminhtml->table_setting('channel_icons', 'channel[icons]', $row['icons'], 'text');
		$adminhtml->table_setting('channel_closed', 'channel[closed]', intval($row['closed']), 'radio');
		$adminhtml->table_setting('channel_target', 'channel[target]', intval($row['target']), 'select');
		$adminhtml->table_setting('channel_htmlout', 'channel[htmlout]', intval($row['htmlout']), 'radio');
		$adminhtml->table_setting('channel_domain', 'channel[domain]', $row['domain'], 'text');
		$adminhtml->table_setting('channel_chanroot', 'channel[chanroot]', $row['chanroot'], 'text');
		$adminhtml->table_end();
		if ($chantype != 'menu') {
			$adminhtml->count = 0;
			$adminhtml->table_header('channel_advanced_setting', 4, 'advancedsetting', 'tableborder', TRUE);
			if ($parentid == 2) {
				$adminhtml->table_setting('channel_setting_runsystem', 'setting[runsystem]', $setting['runsystem'], 'text');
				$adminhtml->table_setting('channel_setting_defrunsystem', 'setting[defrunsystem]', $setting['defrunsystem'], 'text');
				$adminhtml->table_setting('channel_setting_softlang', 'setting[softlang]', $setting['softlang'], 'text');
				$adminhtml->table_setting('channel_setting_softtype', 'setting[softtype]', $setting['softtype'], 'text');
				$adminhtml->table_setting('channel_setting_license', 'setting[license]', $setting['license'], 'text');
				$adminhtml->table_setting('channel_setting_softauth', 'setting[softauth]', $setting['softauth'], 'text');
			} elseif ($parentid == 5) {
				$adminhtml->table_setting('channel_setting_dialogue', 'setting[dialogue]', $setting['dialogue'], 'textarea');
				$adminhtml->table_setting('channel_setting_country', 'setting[country]', $setting['country'], 'textarea');
				$adminhtml->table_setting('channel_setting_version', 'setting[version]', $setting['version'], 'text');
				$adminhtml->table_setting('channel_setting_quality', 'setting[quality]', $setting['quality'], 'text');
				$adminhtml->table_setting('channel_setting_defaultplayer', 'setting[defaultplayer]', intval($setting['defaultplayer']), 'text');
			} else {
				$adminhtml->table_setting('channel_setting_author', 'setting[author]', $setting['author'], 'text');
				$adminhtml->table_setting('channel_setting_source', 'setting[source]', $setting['source'], 'text');
			}
			$adminhtml->table_setting('channel_setting_defaultgroupids', 'setting[defaultgroupids]', $setting['defaultgroupids'], 'text');
			$adminhtml->table_setting('channel_setting_defaultcredits', 'setting[defaultcredits]', intval($setting['defaultcredits']), 'text');
			$adminhtml->table_setting('channel_setting_previewshow', 'setting[previewshow]', intval($setting['previewshow']), 'radios');
			$adminhtml->table_setting('channel_setting_previewpage', 'setting[previewpage]', intval($setting['previewpage']), 'radios');
			//$adminhtml->table_setting('channel_setting_listmode', 'setting[listmode]', intval($setting['listmode']), 'radios');
			$adminhtml->table_setting('channel_setting_pagesize', 'setting[pagesize]', intval($setting['pagesize']), 'text');
			$adminhtml->table_setting('channel_setting_pagenum', 'setting[pagenum]', intval($setting['pagenum']), 'text');
			$adminhtml->table_setting('channel_setting_pagestats', 'setting[pagestats]', intval($setting['pagestats']), 'radio');
			$adminhtml->table_setting('channel_setting_pageinput', 'setting[pageinput]', intval($setting['pageinput']), 'radio');
			$adminhtml->table_setting('channel_setting_summarys', 'setting[summarys]', intval($setting['summarys']), 'text');

			$adminhtml->table_end();
			echo '<div id="attachsetting" style="display: none">';
			$adminhtml->count = 0;
			$adminhtml->table_header('channel_attach_setting');
			$adminhtml->table_setting('channel_setting_uploadstatus', 'setting[uploadstatus]', intval($setting['uploadstatus']), 'select');
			$uploadmodes = array();
			if ($setting['uploadmode'] > 1) {
				$uploadmodes[0] = 0;
				$uploadmodes[1] = 1;
			} else {
				$uploadmodes[0] = intval($setting['uploadmode']);
			}
			$adminhtml->table_setting('channel_setting_uploadmode', 'setting[uploadmode]', $uploadmodes, 'checkbox');
			$adminhtml->table_setting('channel_setting_remoteon', 'setting[remoteon]', intval($setting['remoteon']), 'radio');
			$adminhtml->table_setting('channel_setting_downimage', 'setting[downimage]', intval($setting['downimage']), 'radio');
			$adminhtml->table_setting('channel_setting_watermark', 'setting[watermark]', intval($setting['watermark']), 'radio');
			$adminhtml->table_setting('channel_setting_remotewmk', 'setting[remotewmk]', intval($setting['remotewmk']), 'radio');
			$adminhtml->table_setting('channel_setting_autogravity', 'setting[autogravity]', intval($setting['autogravity']), 'radios');
			$adminhtml->table_setting('channel_setting_waterimage', 'setting[waterimage]', trim($setting['waterimage']), 'text');
			$adminhtml->table_setting('channel_setting_resizeimg_status', 'setting[resizeimg][status]', intval($setting['resizeimg']['status']), 'radios');
			$adminhtml->table_setting('channel_setting_resizeimg_x_y', array('setting[resizeimg][x]', 'setting[resizeimg][y]'), array(intval($setting['resizeimg']['x']), intval($setting['resizeimg']['y'])), 'text2');
			$adminhtml->table_setting('channel_setting_resizeimg_w_h', array('setting[resizeimg][width]', 'setting[resizeimg][height]'), array(intval($setting['resizeimg']['width']), intval($setting['resizeimg']['height'])), 'text2');
			$adminhtml->table_setting('channel_setting_resizeimg_min_w_h', array('setting[resizeimg][minwidth]', 'setting[resizeimg][minheight]'), array(intval($setting['resizeimg']['minwidth']), intval($setting['resizeimg']['minheight'])), 'text2');
			$adminhtml->table_end();
			$adminhtml->table_header('channel_setting_attach_thumb');
			$adminhtml->table_setting('channel_setting_thumbstatus', 'setting[thumbstatus]', intval($setting['thumbstatus']), 'radios');
			$adminhtml->table_setting('channel_setting_thumbauto', 'setting[thumbauto]', intval($setting['thumbauto']), 'radios');
			$adminhtml->table_setting('channel_setting_thumbzoom', 'setting[thumbzoom]', intval($setting['thumbzoom']), 'radio');
			$adminhtml->table_setting('channel_setting_thumbsize', array('setting[thumbwidth]', 'setting[thumbheight]'), array(intval($setting['thumbwidth']), intval($setting['thumbheight'])), 'text2');
			$adminhtml->table_end();
			$adminhtml->table_header('channel_setting_attach_preview');
			$adminhtml->table_setting('channel_setting_previewstatus', 'setting[previewstatus]', intval($setting['previewstatus']), 'radios');
			//$adminhtml->table_setting('channel_setting_previewauto', 'setting[previewauto]', intval($setting['previewauto']), 'radios');
			$adminhtml->table_setting('channel_setting_previewzoom', 'setting[previewzoom]', intval($setting['previewzoom']), 'radio');
			$adminhtml->table_setting('channel_setting_previewsize', array('setting[previewwidth]', 'setting[previewheight]'), array(intval($setting['previewwidth']), intval($setting['previewheight'])), 'text2');
			$adminhtml->table_setting('channel_setting_imagemode', 'setting[imagemode]', intval($setting['imagemode']), 'radios');
			$adminhtml->table_setting('channel_setting_thumbmode', 'setting[thumbmode]', intval($setting['thumbmode']), 'radios');
			$adminhtml->table_setting('channel_setting_previewmode', 'setting[previewmode]', intval($setting['previewmode']), 'radios');
			$adminhtml->table_end();
			echo '</div>';
		}
		$adminhtml->count = 0;
		$adminhtml->table_header();
		//$adminhtml->table_header('channel_select_multiple', 2);
		//$adminhtml->table_td(array(
		//    array(channel_select_multiple(), FALSE, 'width="50%"'),
		//    array('channel_select_multiple_comments', FALSE, 'width="50%"')
		//));
		$btnsubmit = $adminhtml->submit_button();
		$adminhtml->table_td(array(
				array($btnsubmit, TRUE, 'align="center" colspan="2"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
	}else{
		if(empty(phpcom::$G['gp_channel'])){
			admin_message('undefined_action');
		}
		$channel = phpcom::$G['gp_channel'];

		if ($chantype == 'menu') {
			$setting = array();
		} else {
			$setting = phpcom::$G['gp_setting'];
			if(isset($setting['country'])){
				$setting['country'] = str_replace(array("\r", "\n", ' '), '', trim($setting['country']));
			}
			if(isset($setting['language'])){
				$setting['language'] = str_replace(array("\r", "\n", ' '), '', trim($setting['language']));
			}
			if(isset($setting['quality'])){
				$setting['quality'] = str_replace(array("\r", "\n", ' '), '', trim($setting['quality']));
				if(empty($setting['quality'])){
					$setting['quality'] = 'unknown=0,480P=1,720P=2,1080P=3';
				}else{
					$qualitys = explode(',', $setting['quality']);
					$items = array();
					foreach ($qualitys as $key => $item){
						if($item && strpos($item, '=')){
							$items[] = trim($item);
						}elseif($item && strpos($item, '=') === false){
							$items[] = trim($item) . "=$key";
						}
					}
					$setting['quality'] = $items ? implode(',', $items) : 'unknown=0,480P=1,720P=2,1080P=3';
				}
			}
			if(isset($setting['autogravity']) && $setting['autogravity']){
				if($setting['autogravity'] == 1){
					$setting['gravity'] = array(1, 7);
				}elseif($setting['autogravity'] == 2){
					$setting['gravity'] = array(1, 3, 7, 9);
				}elseif($setting['autogravity'] == 3){
					$setting['gravity'] = array(1, 2, 3, 4, 5, 6, 7, 8, 9);
				}else{
					$setting['gravity'] = array(0);
				}
			}else{
				$setting['gravity'] = array(0);
			}
			if(isset($setting['defaultgroupids']) && $setting['defaultgroupids']){
				$setting['defaultgroupids'] = trim($setting['defaultgroupids'], "\t\r\n ,'");
			}
			if(isset($setting['defaultcredits'])){
				$setting['defaultcredits'] = intval($setting['defaultcredits']);
			}
			if(isset($setting['defaultplayer'])){
				$setting['defaultplayer'] = intval($setting['defaultplayer']);
			}
		}
		if(isset($setting['resizeimg'])){
			foreach($setting['resizeimg'] as $k => $v){
				$setting['resizeimg'][$k] = intval($v);
			}
		}
		$codename = $channel['codename'];
		$channel['sitename'] = str_replace(array("\r", "\n"), array('', ''), strip_tags($channel['sitename']));
		$channel['subject'] = str_replace(array("\r", "\n"), array('', ''), $channel['subject']);
		$channel['subject'] = trim(strip_tags($channel['subject']));
		$channel['description'] = str_replace(array("\r", "\n"), array('', ''), $channel['description']);
		$channel['description'] = trim(strip_tags($channel['description']));
		$channel['keyword'] = str_replace(array("\r", "\n"), array('', ''), $channel['keyword']);
		$channel['keyword'] = trim(strip_tags($channel['keyword']));
		if (!$setting) {
			$setting = array();
		}
		if (isset($setting['uploadmode']) && count($setting['uploadmode']) == 1) {
			$setting['uploadmode'] = $setting['uploadmode'][0];
		} elseif(isset($setting['uploadmode'])) {
			$setting['uploadmode'] = count($setting['uploadmode']);
		}
		$channel['setting'] = serialize($setting);
		$count = (int) DB::result_first("SELECT COUNT(*) FROM " . DB::table('channel') . " WHERE channelid<>'$channelid' AND type IN('system','expand') AND codename='$codename'");
		if ($count) {
			admin_message('channel_codename_denied');
		} else {
			$domain = trim($channel['domain'], ' /\\');
			if($domain && !parse_url($domain, PHP_URL_SCHEME)){
				$domain = "http://$domain";
			}
			$channel['domain'] = $domain;
			DB::update('channel', $channel, 'channelid=' . $channelid);
			phpcom_cache::updater('channel');
			admin_succeed('channel_update_succeed', "m=channel&action=edit&channelid=$channelid");
		}
	}
}elseif($action == 'savenew'){
	if (checksubmit(array('btnsubmit', 'submit'))) {
		if(empty(phpcom::$G['gp_channel'])){
			admin_message('undefined_action');
		}
		$channel = phpcom::$G['gp_channel'];
		$modules = $channel['modules'];
		$sortord = intval($channel['sortord']);
		$codename = trim($channel['codename']);
		$channelname = trim($channel['channelname']);
		$channel['channelname'] = $channelname;
		$channel['subname'] = $channelname;
		if (empty($channel['channelname'])) {
			admin_message('channel_name_denied');
		}
		if ($sortord === 0) {
			$sortord = (int) DB::result_first("SELECT MAX(sortord) FROM " . DB::table('channel') . " WHERE 1=1");
			$channel['sortord'] = $sortord + 1;
		}
		$parentid = 0;
		if ($modules == 'menu') {
			$channel['type'] = 'menu';
			$channelid = (int) DB::result_first("SELECT MAX(channelid) FROM " . DB::table('channel') . " WHERE type='menu'");
			$channelid = $channelid < 100 ? 100 : $channelid;
			$channel['setting'] = serialize(array());
		} else {
			$channel['type'] = 'expand';
			$channelid = (int) DB::result_first("SELECT MAX(channelid) FROM " . DB::table('channel') . " WHERE type='expand'");
			$channelid = $channelid < 10 ? 10 : $channelid;
			$modarray = array('menu' => 0, 'article' => 1, 'soft' => 2, 'photo' => 3, 'video' => 5);
			if(!isset($modarray[$modules])){
				admin_message('undefined_action');
			}
			$parentid = intval($modarray[$modules]);
			$channel['setting'] = DB::result_first("SELECT setting FROM " . DB::table('channel') . " WHERE channelid='$parentid'");
		}
		$channel['parentid'] = $parentid;
		$channel['channelid'] = $channelid + 1;
		$count = (int)DB::result_first("SELECT COUNT(*) FROM " . DB::table('channel') . " WHERE codename='$codename'");
		if ($count) {
			admin_message('channel_codename_denied');
		} else {
			DB::insert('channel', $channel);
			phpcom_cache::updater('channel', 0);
		}
		admin_succeed('channel_new_succeed', "m=channel");
	}
}elseif($action == 'del'){
	delete_channel();
}else{
	if (!checksubmit(array('btnsubmit', 'submit'))) {
		$adminhtml->form("m=channel&action=savenew", null, 'name="savenewform"');
		$adminhtml->table_header('channeltips');
		$adminhtml->table_td(array(array('channel_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
		$s = '<b>' . adminlang('channel_add') . '</b> ';
		$s .= adminlang('ordinal');
		$s .= ' <input class="input sortord" size="1" name="channel[sortord]" type="text" value="0" /> ';
		$s .= adminlang('channel_name');
		$s .= ' <input class="input" size="15" name="channel[channelname]" type="text" /> ';
		$s .= adminlang('channel_codename');
		$s .= ' <input class="input" size="15" name="channel[codename]" type="text" value="channel001" /> ';
		$s .= adminlang('channel_sitename');
		$s .= ' <input class="input" size="15" name="channel[sitename]" type="text" value="" /> ';
		$s .= adminlang('channel_module');
		$modules = adminlang('channel_module_option');
		$s .= '<select class="select" name="channel[modules]">';
		foreach ($modules as $key => $value) {
			$s .= "<option value=\"$key\">$value</option>";
		}
		$s .= '</select>';
		$s .= ' <input type="hidden" name="channel[parentid]" value="0" /> ';
		$s .=$adminhtml->submit_button(null, null, 'button');
		$adminhtml->table_td(array(array($s, TRUE, '')), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
		$adminhtml->count = 1;
		$adminhtml->form("m=channel&action=save", null, 'name="channelform" id="channelform"');
		$adminhtml->table_header();
		$adminhtml->table_th(array(
				array('channel_id', 'width="6%" align="center" noWrap="noWrap"'),
				array('channel_sortord_name_short', 'width="27%" class="left" noWrap="noWrap"'),
				array('channel_sitename', 'width="17%" class="left"'),
				array('channel_description', 'width="17%" class="left"'),
				array('channel_keyword', 'width="17%" class="left"'),
				array('channel_admin', 'width="16%" class="left"')
		));
		$sql = "SELECT * FROM " . DB::table('channel') . " WHERE 1 ORDER BY sortord";
		$query = DB::query($sql);
		$chanid = 0;
		while ($row = DB::fetch_array($query)) {
			$chanid = intval($row['channelid']);
			$modules = $row['modules'];
			$edit = $adminhtml->edit_word('channel_setting', "action=edit&m=channel&channelid=$chanid", '');
			if ($row['type'] == 'system' || $row['type'] == 'expand') {
				$edit .= ' | ' . $adminhtml->edit_word('channel_category', "m=category&nav=$modules&chanid=$chanid", '');
			}
			if ($row['type'] != 'system') {
				$edit .= ' | ' . $adminhtml->del_word('delete', "action=del&m=channel&channelid=$chanid", '');
			}
			if ($row['type'] == 'menu') {
				$channelidstr = 'menu';
			} else {
				$channelidstr = $chanid;
			}
			$color = $row['closed'] ? 'red' : 'gray';
			$adminhtml->table_td(array(
					array('<input type="hidden" name="channel_channelid[' . $chanid . ']" value="' . $chanid . '" />' . $channelidstr, TRUE, 'align="center" noWrap="noWrap"', '', $color),
					array('<input type="text" class="input sortord" size="1" name="channel_sortord[' . $chanid . ']" value="' . intval($row['sortord']) . '" />
							<input type="text" class="input" size="12" name="channel_channelname[' . $chanid . ']" value="' . htmlcharsencode($row['channelname']) . '" />
							<input type="text" class="input" size="12" name="channel_subname[' . $chanid . ']" value="' . htmlcharsencode($row['subname']) . '" />', TRUE, 'noWrap="noWrap"'),
					array('<input type="text" class="input" size="22" name="channel_sitename[' . $chanid . ']" value="' . htmlcharsencode($row['sitename']) . '" />', TRUE),
					array('<input type="text" class="input" size="22" name="channel_description[' . $chanid . ']" value="' . htmlcharsencode($row['description']) . '" />', TRUE),
					array('<input type="text" class="input" size="22" name="channel_keyword[' . $chanid . ']" value="' . htmlcharsencode($row['keyword']) . '" />', TRUE),
					array($edit, TRUE, 'noWrap="noWrap"')
			));
		}
		$btnsubmit = $adminhtml->submit_button();
		$adminhtml->table_td(array(
				array($btnsubmit, TRUE, 'align="center" colspan="6"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
	}else{
		$channel_channelid = phpcom::$G['gp_channel_channelid'];
		$channel_sortord = phpcom::$G['gp_channel_sortord'];
		$channel_channelname = phpcom::$G['gp_channel_channelname'];
		$channel_subname = phpcom::$G['gp_channel_subname'];
		$channel_sitename = phpcom::$G['gp_channel_sitename'];
		$channel_description = phpcom::$G['gp_channel_description'];
		$channel_keyword = phpcom::$G['gp_channel_keyword'];

		foreach ($channel_channelid as $key => $value) {
			$data = array();
			$data['sortord'] = $channel_sortord[$value];
			$data['channelname'] = $channel_channelname[$value];
			$data['subname'] = $channel_subname[$value];
			$data['sitename'] = strip_tags($channel_sitename[$value]);
			$data['description'] = $channel_description[$value];
			$data['keyword'] = $channel_keyword[$value];
			DB::update('channel', $data, 'channelid=' . $value);
			phpcom_cache::updater("channel", $value);
			unset($data);
		}
		phpcom_cache::updater('channel');
		admin_succeed('channel_update_succeed', "m=channel");
	}
}
admin_footer();

function ImageResizeValue(){
	$resize = array('x' => '350', 'y' => '200', 'width' => 300, 'height' => 200);
	$images = array('width' => 600, 'height' => 396);
	$w = $images['width'];
	$h = $images['height'];
	$resize['x'] = min($resize['x'], $w);
	$resize['y'] = min($resize['y'], $h);
	$x = $resize['x'];
	$y = $resize['y'];

	if($resize['x'] > 0){
		$w = $images['width'] - $resize['x'];
	}elseif($resize['x'] < 0){
		$w = $images['width'] + $resize['x'];
		$x = 0;
	}
	if($resize['y'] > 0){
		$h = $images['height'] - $resize['y'];
	}elseif($resize['y'] < 0){
		$h = $images['height'] + $resize['y'];
		$y = 0;
	}
	if($resize['width'] < 0){
		$w += $resize['width'];
	}elseif($resize['width'] > 0){
		$w = min($resize['width'], $w);
	}
	if($resize['height'] < 0){
		$h += $resize['height'];
	}elseif($resize['height'] > 0){
		$h = min($resize['height'], $h);
	}

	return array($x, $y, $w, $y,'x' => $x, 'y' => $y, 'w' => $w, 'h' => $h);
}

function delete_channel() {
	$channelid = intval(phpcom::$G['gp_channelid']);
	$row = DB::fetch_first("SELECT channelid,type,counter FROM " . DB::table('channel') . " WHERE channelid='$channelid'");
	if ($row['type'] == 'system') {
		admin_message('channel_delete_failed');
	} else {
		$channelid = intval($row['channelid']);
		if ($row['type'] == 'menu') {
			DB::delete('channel', array('channelid' => $channelid));
			phpcom_cache::updater('channel', 0);
			admin_succeed('channel_delete_succeed', 'm=channel');
		} elseif ($row['type'] == 'expand') {
			$tid = (int) DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " WHERE chanid='$channelid'");
			$cid = (int) DB::result_first("SELECT COUNT(*) FROM " . DB::table('category') . " WHERE chanid='$channelid'");
			if (!$tid && !$cid) {
				DB::delete('channel', array('channelid' => $channelid));
				phpcom_cache::delete("channel_$channelid", TRUE);
			} else {
				admin_message('channel_delete_failed');
			}
		}
		phpcom_cache::updater('channel');
		admin_succeed('channel_delete_succeed', 'm=channel');
	}
}

function channel_select_multiple() {
	$s = '<select name="targetchannel[]" size="8" multiple="multiple" style="width:220px">';
	$s .= '<optgroup label="' . adminlang('channel_setting') . '">';
	$sql = "SELECT channelid,type,channelname FROM " . DB::table('channel') . " WHERE type IN('system','expand') ORDER BY sortord";
	$query = DB::query($sql);
	while ($row = DB::fetch_array($query)) {
		if ($row['type'] == 'system' || $row['type'] == 'expand') {
			$s .= '<option value="' . $row['channelid'] . '">' . $row['channelname'] . '</option>';
		}
	}
	unset($query);
	$s .= "</optgroup>\r\n";
	$s .= "</select>\r\n";
	return $s;
}
?>
