<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : downserver.php    2011-5-24 5:11:47
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'soft';

$chanid = isset(phpcom::$G['gp_chanid']) ? intval(phpcom::$G['gp_chanid']) : 2;
$chanid = $chanid ? $chanid : 2;
phpcom::$G['channelid'] = $chanid;
phpcom::$G['cache']['channel'] = &phpcom::$G['channel'][$chanid];
$namevar = array('name' => phpcom::$G['cache']['channel']['subname']);
phpcom::$G['langvar'] = $namevar;

if (!checksubmit(array('btnsubmit', 'submit_button'))) {
	$current = '';
	$active = 'first';
	if ($action == 'add' || $action == 'edit') {
		$current = 'menu_downserver_' . $action;
		if ($action == 'add') {
			$active = 'add';
		}
	}
	admin_header('menu_downserver', $current, $namevar);
	$navarray = array(
		array('title' => 'menu_downserver', 'url' => "?m=downserver&chanid=$chanid", 'name' => 'first'),
		array('title' => 'menu_downserver_add', 'url' => "?m=downserver&action=add&chanid=$chanid", 'name' => 'add'),
		array('title' => 'menu_soft', 'url' => "?m=soft&chanid=$chanid", 'name' => 'soft'),
		array('title' => 'menu_category', 'url' => "?m=category&nav=soft&chanid=$chanid", 'name' => 'category')
	);
	$adminhtml = phpcom_adminhtml::instance();
	$adminhtml->setvars($namevar);
	$adminhtml->activetabs('topic');
	$adminhtml->navtabs($navarray, $active);
	//批量添加分类
	if ($action == 'addsub') {
		$servid = isset(phpcom::$G['gp_servid']) ? intval(phpcom::$G['gp_servid']) : 0;
		$adminhtml->table_header('downserver_addsub', 4);
		$adminhtml->table_td(array(
			array('downserver_comments', FALSE, ' colspan="4"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end();
		$adminhtml->count = 0;
		$adminhtml->form("m=downserver&action=addsub&chanid=$chanid&servid=$servid");
		$adminhtml->table_header();
		$adminhtml->table_th(array(
			array('order', 'width="5%" align="center" noWrap="noWrap"'),
			array('downserver_servname', 'width="25%" class="left"'),
			array('downserver_servurl', 'width="40%" class="left"'),
			array('downserver_color', 'width="30%" class="left"')
		));
		$newservid = makenewservid() - 1;
		$sortord = (int) DB::result_first("SELECT MAX(sortord) FROM " . DB::table('downserver') . " WHERE chanid=$chanid AND parentid=$servid");
		for ($i = 1; $i < 11; $i++) {
			$codename = $newservid + $i;
			$adminhtml->table_td(array(
				array('<input class="input sortord" size="1" name="sortord[]" type="text" value="' . ($sortord + $i) . '" />', TRUE, 'align="center"'),
				array('<input class="input" size="30" name="servname[]" type="text" />', TRUE),
				array('<input class="input" size="60" name="servurl[]" type="text" />', TRUE),
				array($adminhtml->textcolor("color[]", '', 20), TRUE)
			));
		}
		$btnsubmit = $adminhtml->submit_button();
		$adminhtml->table_td(array(
			array($btnsubmit, TRUE, 'align="center" colspan="6"')
				), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
	} elseif ($action == 'add' || $action == 'edit') {
		//添加编辑下载服务器信息
		$servid = isset(phpcom::$G['gp_servid']) ? intval(phpcom::$G['gp_servid']) : 0;
		$downserver = array('servid' => 0, 'child' => 0, 'parentid' => 0, 'servname' => '', 'color' => '',
				'servurl' => '', 'downmode' => 0, 'icons' => '', 'sortord' => 0);
		$active = 'add';
		if ($action == 'edit' && $servid) {
			$active = 'edit';
			$downserver = DB::fetch_first("SELECT * FROM " . DB::table('downserver') . " WHERE servid='$servid'");
			if($downserver['expires']){
				$downserver['expires'] = str_replace(' 00:00', '', fmdate($downserver['expires'],'Y-m-d'));
			}else{
				$downserver['expires'] = '';
			}
		} else {
			$downserver['groupid'] = 0;
			$downserver['expires'] = '';
			$downserver['redirect'] = 0;
		}
		echo '<script src="misc/js/calendar.js" type="text/javascript"></script>';
		$adminhtml->form("m=downserver&action=$active&chanid=$chanid&servid=$servid", array(array('child', intval($downserver['child']))));
		$adminhtml->table_header('tips', 3);
		$adminhtml->table_td(array(
			array('downserver_comments', FALSE, 'colspan="3"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_th(array(
			array('downserver_' . $active, 'class="left" colspan="3"')
		));
		$adminhtml->table_td(array(
			array('downserver_servname', FALSE, 'width="15%"', '', TRUE),
			array('<input class="input" size="60" name="downserver[servname]" type="text" value="' . htmlcharsencode($downserver['servname']) . '" />', TRUE, 'width="35%"'),
			array($adminhtml->textcolor('downserver[color]', $downserver['color']), TRUE, 'width="60%"')
		));
		$adminhtml->table_td(array(
			array('downserver_servurl', FALSE, '', '', TRUE),
			array('<input class="input" size="60" name="downserver[servurl]" type="text" value="' . htmlcharsencode($downserver['servurl']) . '" />', TRUE),
			array('downserver_servurl_tips', FALSE, '', '', 'tips')
		));
		$adminhtml->table_td(array(
			array('downserver_downmode', FALSE, '', '', TRUE),
			array($adminhtml->radio(adminlang('downserver_downmode_option'), 'downserver[downmode]', intval($downserver['downmode'])), TRUE),
			array('downserver_downmode_tips', FALSE, '', '', 'tips')
		));
		$adminhtml->table_td(array(
			array('downserver_groups', FALSE, '', '', TRUE),
			array(select_usergrouplevel('downserver[groupid]', $downserver['groupid']), TRUE),
			array('downserver_groups_tips', FALSE, '', '', 'tips')
		));
		$parentid_select = '<select name="downserver[parentid]" class="select" style="width:322px">';
		$parentid_select .= '<option value="0" style="color: red;">' . adminlang('downserver_select_default') . '</option>';
		$parentid_select .= downserver_select_option($chanid, intval($downserver['parentid']));
		$parentid_select .= '</select>';
		$adminhtml->table_td(array(
			array('downserver_parent', FALSE, '', '', TRUE),
			array($parentid_select, TRUE),
			array('downserver_parent_tips', FALSE, '', '', 'tips')
		));
		$adminhtml->table_td(array(
			array('downserver_redirect', FALSE, '', '', TRUE),
			array($adminhtml->radio(adminlang('downserver_redirect_option'), 'downserver[redirect]', intval($downserver['redirect'])), TRUE),
			array('downserver_redirect_tips', FALSE, '', '', 'tips')
		));
		$adminhtml->table_td(array(
			array('downserver_expires', FALSE, '', '', TRUE),
			array('<input class="input" size="30" id="down_expires" onclick="showcalendar(this.id)" name="downserver[expires]" type="text" value="' . htmlcharsencode($downserver['expires']) . '" />', TRUE),
			array('downserver_expires_tips', FALSE, '', '', 'tips')
		));
		$adminhtml->table_td(array(
			array('downserver_icons', FALSE, '', '', TRUE),
			array('<input class="input" size="30" name="downserver[icons]" type="text" value="' . htmlcharsencode($downserver['icons']) . '" />', TRUE),
			array('downserver_icons_tips', FALSE, '', '', 'tips')
		));
		$adminhtml->table_td(array(
			array('downserver_sortord', FALSE, '', '', TRUE),
			array('<input class="input sortord" size="30" name="downserver[sortord]" type="text" value="' . intval($downserver['sortord']) . '" />', TRUE),
			array('downserver_sortord_tips', FALSE, '', '', 'tips')
		));
		$btnsubmit = $adminhtml->submit_button();
		$adminhtml->table_td(array(
			array($btnsubmit, TRUE, 'align="center" colspan="6"')
				), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
	} elseif ($action == 'del') {
		//删除分类
		$servid = intval(phpcom::$G['gp_servid']);
		delete_downserver($servid, $chanid);
	} else {
		$adminhtml->form("m=downserver&action=category&chanid=$chanid", null, 'name="downservform"');
		$adminhtml->table_header('downserver_category_add');
		$adminhtml->table_td(array(
			array('downserver_tips', FALSE, '')
		), NULL, FALSE, NULL, NULL, FALSE);
		$s = '<b>' . adminlang('downserver_category') . '</b> ';
		$s .= adminlang('ordinal');
		$s .= ' <input class="input sortord" size="1" name="downserver[sortord]" type="text" value="0" /> ';
		$s .= adminlang('downserver_servname');
		$s .= ' <input class="input" size="30" name="downserver[servname]" type="text" /> ';
		$s .=$adminhtml->submit_button(null, null, 'button');
		$adminhtml->table_td(array(
			array($s, TRUE, '')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');

		$adminhtml->form("m=downserver&action=editall&chanid=$chanid");
		$adminhtml->table_header('downserver_toggle', 4);
		$adminhtml->table_td(array(
			array('downserver_servid', FALSE, 'width="5%" align="center" noWrap="noWrap"'),
			array('downserver_sortord_name_url', FALSE, 'width="77%"'),
			array('adminoption', FALSE, 'width="18%" align="center"')
				), '', FALSE, ' tablerow');
		$depth = '';
		$count = 0;
		$category = $subserver1 = $subserver2 = array();
		$addsubserver = adminlang('downserver_subserver_add');
		$sql = "SELECT * FROM " . DB::table('downserver') . " WHERE chanid='$chanid' ORDER BY sortord";
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			if ($row['depth'] == 0) {
				$category[$row['servid']] = $row;
			} elseif ($row['depth'] == 1) {
				$subserver1[$row['parentid']][$row['servid']] = $row;
			} else {
				$subserver2[$row['parentid']][$row['servid']] = $row;
			}
			$count++;
		}
		$lastrow = 0;
		foreach ($category as $key => $row) {
			showdownserver($adminhtml, $row, $chanid, $addsubserver, $count);
			echo "<tbody id=\"groupbody_{$row['servid']}\">\r\n";
			if(isset($subserver1[$row['servid']])){
				foreach ($subserver1[$row['servid']] as $key => $row) {
					showdownserver($adminhtml, $row, $chanid, $addsubserver, $count);
					if(isset($subserver2[$row['servid']])){
						foreach ($subserver2[$row['servid']] as $key => $row) {
							showdownserver($adminhtml, $row, $chanid, '', $count);
						}
					}
				}
			}
			echo '</tbody>';
		}
		$btnsubmit = $adminhtml->submit_button();
		$adminhtml->table_td(array(
			array($btnsubmit, TRUE, 'align="center" colspan="3"')
				), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
	}
} else {
	if ($action == 'category') {
		$downserver = phpcom::$G['gp_downserver'];
		if (empty($downserver['servname'])) {
			admin_message('downserver_name_invalid');
		}
		$downserver = striptags($downserver);
		$downserver['servid'] = makenewservid();
		//计算排序
		if ($downserver['sortord'] < 1) {
			$sortord = (int) DB::result_first("SELECT MAX(sortord) FROM " . DB::table('downserver') . " WHERE chanid='$chanid' AND depth='0'");
			$downserver['sortord'] = $sortord + 1;
		}
		$downserver['chanid'] = $chanid;
		$downserver['depth'] = 0;
		$downserver['rootid'] = $downserver['servid'];
		$downserver['parentid'] = 0;
		$downserver['child'] = 0;
		$downserver['expires'] = 0;
		$downserver['lastdate'] = phpcom::$G['timestamp'];
		DB::insert('downserver', $downserver);
		update_downserver_cache($chanid);
		$url = ADMIN_SCRIPT . '?m=downserver&chanid=' . $chanid;
		phpcom::header('Location: ' . $url);
	} elseif ($action == 'editall') {
		$data = array();
		$servid = phpcom::$G['gp_servid'];
		$servname = phpcom::$G['gp_servname'];
		$servurl = phpcom::$G['gp_servurl'];
		$sortord = phpcom::$G['gp_sortord'];
		$icons = phpcom::$G['gp_icons'];

		foreach ($servid as $value) {
			$data['servname'] = $servname[$value];
			$data['servurl'] = $servurl[$value];
			$data['sortord'] = $sortord[$value];
			$data['icons'] = $icons[$value];
			DB::update('downserver', striptags($data), "servid='$value'");
			unset($data);
		}
		update_downserver_cache($chanid);
		admin_succeed('update_succeed', 'm=downserver&chanid=' . $chanid);
	} elseif ($action == 'addsub') {
		$downserver = array();
		$servid = intval(phpcom::$G['gp_servid']);
		$sortords = phpcom::$G['gp_sortord'];
		$servnames = phpcom::$G['gp_servname'];
		$servurls = phpcom::$G['gp_servurl'];
		$servcolors = phpcom::$G['gp_color'];

		$depth = 0;
		$parentid = 0;
		$child = 0;
		$rootid = 0;
		if ($servid) {
			$sql = "SELECT servid,depth,rootid FROM " . DB::table('downserver') . " WHERE servid='$servid'";
			$row = DB::fetch_first($sql);
			if ($row) {
				$depth = $row['depth'] + 1;
				$parentid = $row['servid'];
				$rootid = $row['rootid'];
			}
		} else {
			admin_message('downserver_add_failed');
		}
		if ($depth > 2) {
			admin_message('downserver_depth_denied');
		}
		$newservid = makenewservid();
		$i = 0;
		$lastdate = phpcom::$G['timestamp'];
		foreach ($servnames as $value) {
			if ($value) {
				$downserver[$i]['servid'] = $newservid + $i;
				$downserver[$i]['chanid'] = $chanid;
				$downserver[$i]['servname'] = $value;
				$downserver[$i]['servurl'] = $servurls[$i];
				$downserver[$i]['color'] = $servcolors[$i];
				$downserver[$i]['sortord'] = $sortords[$i];
				$downserver[$i]['parentid'] = $parentid;
				$downserver[$i]['child'] = $child;
				$downserver[$i]['depth'] = $depth;
				$downserver[$i]['rootid'] = $rootid;
				$downserver[$i]['expires'] = 0;
				$downserver[$i]['lastdate'] = $lastdate;
				$i++;
			}
		}
		$data = array();
		//批量增加子服务器
		foreach ($downserver as $key => $value) {
			if (is_array($value)) {
				$data = striptags($value);
				DB::insert('downserver', $data);
				unset($data);
			}
		}
		//更新所属服务器信息
		if ($servid) {
			$child = (int) DB::result_first("SELECT COUNT(*) FROM " . DB::table('downserver') . " WHERE parentid='$servid'");
			DB::update('downserver', array('child' => $child), "servid='$servid'");
		}
		update_downserver_cache($chanid);
		$url = ADMIN_SCRIPT . '?m=downserver&chanid=' . $chanid;
		phpcom::header('Location: ' . $url);
	} elseif ($action == 'add' || $action == 'edit') {
		$row = array();
		$downserver = striptags(phpcom::$G['gp_downserver']);
		$servid = (int) phpcom::$G['gp_servid'];
		$downserver['chanid'] = $chanid;
		//下载服务器名不能为空
		if (empty($downserver['servname'])) {
			admin_message('downserver_name_invalid');
		}
		$downserver['groupid'] = intval($downserver['groupid']);
		$downserver['lastdate'] = phpcom::$G['timestamp'];
		if($downserver['expires']){
			$downserver['expires'] = strtotime($downserver['expires']);
		}else{
			$downserver['expires'] = 0;
		}
		$rootid = 0;
		//更新下载服务器
		if ($action == 'edit') {
			if ($downserver['parentid'] == $servid) {
				admin_message('downserver_update_failed');
			}
			//查询当前服务器信息
			$row = DB::fetch_first("SELECT servid,depth,rootid,parentid,child FROM " . DB::table('downserver') . " WHERE servid='$servid'");
			//如果要移动服务器，检测服务器能否被移动
			if ($row['parentid'] != $downserver['parentid']) {
				//此服务器包含子服务器，抛出错误
				if ($row['child']) {
					admin_message('downserver_moved_failed');
				}
				//如果是服务器分类，抛出错误
				if ($row['depth'] == 0) {
					admin_message('downserver_update_depth_failed');
				}
				//获取目标服务器信息
				if ($downserver['parentid']) {
					$parentrow = DB::fetch_first("SELECT servid,depth,rootid FROM " . DB::table('downserver') . " WHERE servid='{$downserver['parentid']}'");
					if ($parentrow) {
						if ($parentrow['depth'] > 2) {
							admin_error('errors');
						}
						$downserver['depth'] = $parentrow['depth'] + 1;
						$downserver['rootid'] = $parentrow['rootid'];
					} else {
						//如果获取不到目标服务器信息，抛出错误
						admin_error('errors');
					}
				} else {
					//如果移动为服务器分类，设置分类 depth=0
					$downserver['depth'] = 0;
					$downserver['rootid'] = $servid;
					$downserver['child'] = 0;
				}
			}
			//获取当前服务器的所属服务器ID
			$parentid = (int) DB::result_first("SELECT parentid FROM " . DB::table('downserver') . " WHERE servid='$servid'");
			//更新分类信息
			DB::update('downserver', $downserver, array('servid' => $servid));
			if ($parentid) {
				//更新当前服务器的子服务器信息
				$child = (int) DB::result_first("SELECT COUNT(*) FROM " . DB::table('downserver') . " WHERE parentid='$parentid'");
				DB::update('downserver', array('child' => $child), 'servid=' . $parentid);
			}
			$parentid = (int) $downserver['parentid'];
			//判断是否更新所属服务器；如果移动的是服务器分类，无需更新操作
			if ($parentid && $row['parentid'] != $parentid) {
				$child = (int) DB::result_first("SELECT COUNT(*) FROM " . DB::table('downserver') . " WHERE parentid='$parentid'");
				DB::update('downserver', array('child' => $child), 'servid=' . $parentid);
			}
			update_downserver_cache($chanid);
			admin_succeed('downserver_succeed', 'action=edit&m=downserver&chanid=' . $chanid . '&servid=' . $servid);
		} elseif ($action == 'add') {
			//增加新的下载服务器
			$parentid = intval($downserver['parentid']);
			$downserver['servid'] = makenewservid();
			//查询所属服务器信息
			if ($parentid) {
				$row = DB::fetch_first("SELECT servid,depth,rootid,child FROM " . DB::table('downserver') . " WHERE servid='$parentid'");
				$downserver['depth'] = $row['depth'] + 1;
				$downserver['rootid'] = $row['rootid'];
				//如果选择的所属服务器超过限制2，抛出错误信息
				if ($row['depth'] > 1) {
					admin_message('downserver_insert_failed');
				}
			} else {
				//如果新增的是服务器分类，设置默认信息
				$downserver['depth'] = 0;
				$downserver['rootid'] = $downserver['servid'];
				$downserver['parentid'] = 0;
				$downserver['child'] = 0;
			}
			//计算排序
			if ($downserver['sortord'] < 1) {
				$sortord = (int) DB::result_first("SELECT MAX(sortord) FROM " . DB::table('downserver') . " WHERE parentid='$parentid'");
				$downserver['sortord'] = $sortord + 1;
			}
			//新增分类数据
			DB::insert('downserver', $downserver);
			//更新所属服务器信息
			if ($parentid) {
				$child = (int) DB::result_first("SELECT COUNT(*) FROM " . DB::table('downserver') . " WHERE parentid='$parentid'");
				DB::update('downserver', array('child' => $child), 'servid=' . $parentid);
			}
		}
		update_downserver_cache($chanid);
		admin_succeed('downserver_succeed', 'm=downserver&chanid=' . $chanid);
	}
}

admin_footer();

function showdownserver($adminhtml, $row, $chanid, $addsubserver = '', $max=0) {
	static $index = 0;
	$index++;
	$edit = '<input type="hidden" name="servid[]" value="' . $row['servid'] . '" />';
	$edit .= $adminhtml->edit_word('edit', 'action=edit&m=downserver&servid=' . $row['servid'] . '&chanid=' . $chanid, ' | ');
	$edit .= $adminhtml->del_word('delete', 'action=del&m=downserver&servid=' . $row['servid'] . '&chanid=' . $chanid);
	$add_subserver = $addsubserver ? '&nbsp;<i class="add-link"><b>+</b> <a href="?action=addsub&m=downserver&servid=' . $row['servid'] . '&chanid=' . $chanid . '" id="addlinks_' . $row['servid'] . '" style="display:none;">' . $addsubserver . '</a></i>' : '';
	if ($row['color']) {
		$row['color'] = ' style="color:' . $row['color'] . '"';
	} else {
		$row['color'] = '';
	}
	if ($row['depth'] == 1) {
		if ($index >= $max) {
			$depth = '<i class="tdline1 tdlast"></i>';
		} else {
			$depth = '<i class="tdline1"></i>';
		}
	} elseif ($row['depth'] == 2) {
		if ($index >= $max) {
			$depth = '<i class="tdline2 tdlast"></i>';
		} else {
			$depth = '<i class="tdline2"></i>';
		}
	} else {
		$depth = "<i id=\"toggle_{$row['servid']}\" class=\"toggle hide-icon\" onclick=\"toggle_display('{$row['servid']}')\"></i>";
	}
	if ($row['depth'] == 0 || $row['child'] > 0) {
		$servurlinput = '<input  type="hidden" name="servurl[' . $row['servid'] . ']" value="" />';
	} else {
		$servurlinput = '<input class="input" size="40" name="servurl[' . $row['servid'] . ']" type="text" value="' . htmlcharsencode($row['servurl']) . '" />';
	}
	$adminhtml->table_td(array(
		array('<em class="tiny"><span' . $row['color'] . '>' . $row['servid'] . '</span></em>', TRUE, 'align="center"'),
		array($depth . '<span class="span-input"><input class="input sortord" size="1" name="sortord[' . $row['servid'] . ']" type="text" value="' . $row['sortord'] . '" />
			<input class="input" size="20" name="servname[' . $row['servid'] . ']" type="text" value="' . htmlcharsencode($row['servname']) . '" /> ' . $servurlinput . '
			<input class="input" size="15" name="icons[' . $row['servid'] . ']" type="text" value="' . htmlcharsencode($row['icons']) . '" /></span>' . $add_subserver, TRUE),
		array($edit, TRUE,'align="center"')
			), '', FALSE, ' tdborder', $row['depth'] == 2 ? '' : ' onmouseover="toggleDisplay(\'addlinks_' . $row['servid'] . '\',\'show\')" onmouseout="toggleDisplay(\'addlinks_' . $row['servid'] . '\',\'hide\')"');
}

function delete_downserver($servid, $chanid=0) {
	//默认下载服务器不能删除
	if ($servid == 1) {
		admin_message('downserver_delete_default');
	}
	
	//检测父级服务器是否存在，0=不存在
	$parentid = (int) DB::result_first("SELECT COUNT(*) FROM " . DB::table('downserver') . " WHERE parentid='$servid'");
	//如果存在父级服务器，抛出错误信息
	if ($parentid) {
		admin_message('downserver_delete_failed');
	}else{
		//判断服务器是否已使用
		$down = (int) DB::result_first("SELECT COUNT(servid) FROM " . DB::table('soft_download') . " WHERE servid='$servid'");
		if($down){
			admin_message('downserver_delete_failed');
		}
	}
	//获取要删除服务器的父级服务器ID
	$parentid = (int) DB::result_first("SELECT parentid FROM " . DB::table('downserver') . " WHERE servid='$servid'");
	//开始删除操作
	if ($servid) {
		DB::delete('downserver', array('servid' => $servid));
	}
	//如果 $parentid=0，删除的是服务器分类，就不用更新
	//如果 $parentid>0，更新父级服务器数据
	if ($parentid) {
		$child = (int) DB::result_first("SELECT COUNT(*) FROM " . DB::table('downserver') . " WHERE parentid='$parentid'");
		DB::update('downserver', array('child' => $child), 'servid=' . $parentid);
	}
	update_downserver_cache($chanid);
	admin_succeed('delete_succeed', 'm=downserver&chanid=' . $chanid);
}

function update_downserver_cache($chanid) {
	phpcom_cache::updater('downserver', $chanid);
}

function makenewservid() {
	$servid = (int) DB::result_first("SELECT MAX(servid) FROM " . DB::table('downserver') . " WHERE 1=1");
	return $servid + 1;
}

function downserver_select_option($chanid, $parentid = 0) {
	$category = $subserver = $child = array();

	$sql = "SELECT * FROM " . DB::table('downserver') . " WHERE chanid='$chanid' ORDER BY sortord";
	$query = DB::query($sql);
	while ($row = DB::fetch_array($query)) {
		if ($row['depth'] == 0) {
			$category[$row['servid']] = $row;
		} elseif ($row['depth'] == 1) {
			$subserver[$row['parentid']][$row['servid']] = $row;
		} else {
			$child[$row['parentid']][$row['servid']] = $row;
		}
	}

	$option = '';
	foreach ($category as $key => $row) {
		$option .= '<option value="' . $row['servid'] . '"';
		$option .= ( $row['servid'] == $parentid) ? ' SELECTED' : '';
		$option .= ">{$row['servname']}</option>";
		if(isset($subserver[$row['servid']])){
			foreach ($subserver[$row['servid']] as $key => $row) {
				$option .= '<option value="' . $row['servid'] . '"';
				$option .= ( $row['servid'] == $parentid) ? ' SELECTED' : '';
				$option .= "> &nbsp; &nbsp;|- {$row['servname']}</option>";
			}
		}
	}
	return $option;
}

?>
