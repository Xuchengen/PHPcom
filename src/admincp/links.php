<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : links.php    2011-6-15 18:03:58
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';
if (!checksubmit('btnsubmit')) {
	$order = isset(phpcom::$G['gp_order']) ? strtolower(phpcom::$G['gp_order']) : '';
	$current = '';
	$active = 'first';
	if ($action == 'add' || $action == 'edit') {
		$current = 'menu_links_' . $action;
		if ($action == 'add') {
			$active = 'add';
		}
	} elseif ($action == 'logo') {
		$current = 'menu_links_logo';
		$active = 'logo';
	} elseif ($action == 'text') {
		$current = 'menu_links_text';
		$active = 'text';
	}
	if ($order == 'desc') {
		$current = 'menu_links_desc';
		$active = 'desc';
	}
	admin_header('menu_links', $current);
	$navarray = array(
		array(
			'title' => 'menu_links',
			'url' => '?m=links',
			'name' => 'first',
			'onclick' => ''
		),
		array(
			'title' => 'menu_links_add',
			'url' => '?action=add&m=links',
			'name' => 'add',
			'onclick' => ''
		),
		array(
			'title' => 'menu_links_logo',
			'url' => '?action=logo&m=links',
			'name' => 'logo',
			'onclick' => ''
		),
		array(
			'title' => 'menu_links_text',
			'url' => '?action=text&m=links',
			'name' => 'text',
			'onclick' => ''
		),
		array(
			'title' => 'menu_links_desc',
			'url' => '?m=links&order=desc',
			'name' => 'desc',
			'onclick' => ''
		)
	);
	$adminhtml = phpcom_adminhtml::instance();
    $adminhtml->activetabs('tools');
	$adminhtml->navtabs($navarray, $active, 'nav_tabs', 'links');
	if ($action == 'add' || $action == 'edit') {
		$linkid = isset(phpcom::$G['gp_linkid']) ? intval(phpcom::$G['gp_linkid']) : 0;
		$friendlinks = array('name' => '', 'color' => '', 'url' => '', 'logo' => '', 'description' => '',
				'type' => 0, 'category' => 0, 'closed' => 0, 'sortord' => 0);
		$active = 'add';
		if ($action == 'edit' && $linkid) {
			$friendlinks = DB::fetch_first("SELECT * FROM " . DB::table('friendlinks') . " WHERE linkid='$linkid'");
			$active = 'edit';
			if ($friendlinks['expires']) {
				$friendlinks['expires'] = str_replace(' 00:00', '', fmdate($friendlinks['expires'], 'Y-m-d'));
			} else {
				$friendlinks['expires'] = '';
			}
		} else {
			$friendlinks['expires'] = '';
			$friendlinks['url'] = 'http://';
		}
		echo '<script src="misc/js/calendar.js" type="text/javascript"></script>';
		$adminhtml->form('m=links', array(array('action', $active), array('linkid', $linkid)));
		$adminhtml->table_header('friendlinks_' . $active, 3);
        $adminhtml->table_setting('friendlinks_name', array('friendlinks[name]', 'friendlinks[color]'), array($friendlinks['name'], $friendlinks['color']), 'textcolor');
		$adminhtml->table_setting('friendlinks_url', 'friendlinks[url]', $friendlinks['url'], 'text');
		$adminhtml->table_setting('friendlinks_logo', 'friendlinks[logo]', $friendlinks['logo'], 'text');
		$adminhtml->table_setting('friendlinks_description', 'friendlinks[description]', $friendlinks['description'], 'textarea');
		$adminhtml->table_setting('friendlinks_type', 'friendlinks[type]', $friendlinks['type'], 'radios');
		$adminhtml->table_setting('friendlinks_category', 'friendlinks[category]', $friendlinks['category'], 'radios');
		$adminhtml->table_setting('friendlinks_closed', 'friendlinks[closed]', $friendlinks['closed'], 'radio');
        $adminhtml->table_setting('friendlinks_expires', 'friendlinks[expires]', $friendlinks['expires'], 'text', 'showcalendar(this.id)', 'friendlinks_expires');
        $adminhtml->table_setting('friendlinks_sortord', 'friendlinks[sortord]', intval($friendlinks['sortord']), 'text', NULL, NULL, NULL, 't10');
		$adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
		$adminhtml->table_end('</form>');
	} elseif ($action == 'del') {
		$linkid = intval(phpcom::$G['gp_linkid']);
		if ($linkid > 2) {
			DB::delete('friendlinks', "linkid='$linkid'");
		}
		admin_succeed('friendlinks_delete_succeed', 'm=links');
	} elseif ($action == 'locked') {
		$linkid = intval(phpcom::$G['gp_linkid']);
		if ($linkid) {
			DB::update('friendlinks', array('closed' => 1), "linkid=$linkid");
		}
		phpcom::header('Location: ' . $_SERVER['HTTP_REFERER']);
	} elseif ($action == 'unlock') {
		$linkid = intval(phpcom::$G['gp_linkid']);
		if ($linkid) {
			DB::update('friendlinks', array('closed' => 0), "linkid=$linkid");
		}
		phpcom::header('Location: ' . $_SERVER['HTTP_REFERER']);
	} else {
		$adminhtml->table_header();
		$adminhtml->form('m=links', array(array('action', 'reorder')), 'name="reorderform"');
		$adminhtml->table_th(array(
			array('order', 'width="5%" noWrap="noWrap"'),
			array('friendlinks_name', 'width="25%" noWrap="noWrap"'),
			array('friendlinks_type', 'width="10%"'),
			array('adminoption', 'width="20%"'),
			array('status', 'width="10%"'),
			array('friendlinks_dataline', 'width="15%"'),
			array('friendlinks_expires', 'width="15%"')
		));
		$adminhtml->table_td(array(
			array(' ', TRUE, 'colspan="7" align="left" id="showpage"')
				), NULL, FALSE, NULL, NULL, FALSE);
		$word = isset(phpcom::$G['gp_word']) ? phpcom::$G['gp_word'] : '';
		$condition = ' 1=1';
		$queryurl = '';
		if ($action == 'logo') {
			$condition = " type='1'";
			$queryurl = '&action=logo';
		} elseif ($action == 'text') {
			$condition = " type='0'";
			$queryurl = '&action=text';
		}
		$orderby = 'ASC';
		if ($order == 'desc') {
			$orderby = 'DESC';
			$queryurl .= '&order=desc';
		}

		if ($action == 'search' && $word) {
			$word = str_replace('_', '\_', $word);
			$condition = " (`name` LIKE '%$word%' OR `url` LIKE '%$word%')";
			$queryurl = implodeurl(array('action' => 'search', 'word' => $word), '&');
		}
		$todaytime = strtotime(fmdate(TIMESTAMP, 'YmdHis'));
		// 获取总记录数
		$totalrec = DB::result_first("SELECT COUNT(*) FROM " . DB::table('friendlinks') . " WHERE $condition");
		$pagenow = $page;  // 当前页
		$pagesize = 50; //intval(phpcom::$config['admincp']['pagesize']);  // 每页大小
		$pagecount = @ceil($totalrec / $pagesize);  //计算总页数
		$pagenow > $pagecount && $pagenow = 1;
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$sql = DB::buildlimit("SELECT * FROM " . DB::table('friendlinks') . " WHERE $condition ORDER BY sortord $orderby", $pagesize, $pagestart);
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$linkid = $row['linkid'];
			$edit = '<input type="hidden" name="linkid[]" value="' . $linkid . '" />';
			$edit .= $adminhtml->edit_word('edit', "action=edit&m=links&linkid=$linkid", ' | ');
			$edit .= $adminhtml->del_word('delete', "action=del&m=links&linkid=$linkid", ' | ');
			if ($row['color']) {
				$row['color'] = 'style="color:' . $row['color'] . '"';
			} else {
				$row['color'] = '';
			}
			if ($row['type']) {
				if ($row['logo']) {
					$row['logolink'] = '<img src="' . $row['logo'] . '" width="88" height="31" border="0" />';
				} else {
					$row['logolink'] = adminlang('friendlinks_logolink');
				}
			} else {
				$row['logolink'] = adminlang('friendlinks_textlink');
			}
			if ($row['closed']) {
				$row['status'] = '<span class="red">&times;</span>';
				$row['name'] = '<span style="text-decoration:line-through;font-weight:700;">' . $row['name'] . '</span>';
				$edit .= $adminhtml->edit_word('unlock', "action=unlock&m=links&linkid=$linkid");
			} else {
				$row['status'] = '<span class="blue">&radic;</span>';
				$edit .= $adminhtml->edit_word('locked', "action=locked&m=links&linkid=$linkid");
			}
			if ($row['expires']) {
				if ($row['expires'] > $todaytime) {
					$row['expires'] = '<span class="red">' . fmdate($row['expires'], 'd') . '</span>';
				} else {
					$row['expires'] = '<span class="red">Expires</span>';
				}
			} else {
				$row['expires'] = '<span class="blue">Permanent</span>';
			}
			$adminhtml->table_td(array(
				array('<input class="input sortord" size="1" name="sortord[' . $linkid . ']" type="text" value="' . $row['sortord'] . '" />', TRUE, 'align="center" noWrap="noWrap"'),
				array('<a href="javascript:" onclick="window.open(\'' . $row['url'] . '\')"' . $row['color'] . '>' . $row['name'] . '</a>', TRUE, 'align="center"'),
				array($row['logolink'], TRUE, 'align="center" noWrap="noWrap"'),
				array($edit, TRUE, 'align="center" noWrap="noWrap"'),
				array($row['status'], TRUE, 'align="center" noWrap="noWrap"'),
				array('<em class="tiny">' . fmdate($row['dateline'], 'd') . '</em>', TRUE, 'align="center" noWrap="noWrap"'),
				array('<em class="tiny">' . $row['expires'] . '</em>', TRUE, 'align="center" noWrap="noWrap"')
			));
		}
		$showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=links$queryurl") . '</var>';
		$adminhtml->table_td(array(
			array($showpage, TRUE, 'colspan="7" align="right" id="pagecode"')
				), NULL, FALSE, NULL, NULL, FALSE);
		$btnsubmit = $adminhtml->submit_button();
		$adminhtml->table_td(array(
			array($btnsubmit, TRUE, 'align="center" colspan="7"')
				), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
		$adminhtml->showpagescript();
	}
	admin_footer();
} else {
	if ($action == 'add' || $action == 'edit') {
		$linkid = intval(phpcom::$G['gp_linkid']);
		$friendlinks = striptags(phpcom::$G['gp_friendlinks']);
		if (empty($friendlinks['name'])) {
			admin_message('friendlinks_name_invalid');
		}
		if (empty($friendlinks['url'])) {
			admin_message('friendlinks_url_invalid');
		}
		if ($friendlinks['type']) {
			if (empty($friendlinks['logo']))
				admin_message('friendlinks_logo_invalid');
		}
		if ($friendlinks['expires']) {
			$friendlinks['expires'] = strtotime($friendlinks['expires']);
		} else {
			$friendlinks['expires'] = 0;
		}
		if ($friendlinks['sortord'] < 1) {
			$sortord = (int) DB::result_first("SELECT MAX(sortord) FROM " . DB::table('friendlinks') . " WHERE 1=1");
			$friendlinks['sortord'] = $sortord + 1;
		}
		$friendlinks['url'] = str_replace('http://http://', 'http://', $friendlinks['url']);
		if ($action == 'edit') {
			DB::update('friendlinks', $friendlinks, array('linkid' => $linkid));
			admin_succeed('friendlinks_edit_succeed', "m=links&action=edit&linkid=$linkid");
		} else {
			$friendlinks['dateline'] = phpcom::$G['timestamp'];
			DB::insert('friendlinks', $friendlinks);
			admin_succeed('friendlinks_add_succeed', 'm=links');
		}
	} elseif ($action == 'reorder') {
		$data = array();
		$linkid = phpcom::$G['gp_linkid'];
		$sortord = phpcom::$G['gp_sortord'];
		foreach ($linkid as $value) {
			$value = intval($value);
			$data['sortord'] = intval($sortord[$value]);
			DB::update('friendlinks', $data, "linkid='$value'");
			unset($data);
		}
		admin_succeed('update_succeed', 'm=links');
	}
}
?>
