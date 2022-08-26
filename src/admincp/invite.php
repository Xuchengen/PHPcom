<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : invite.php    2011-10-8 1:09:44
 */
!defined('IN_ADMINCP') && exit('Access denied');
$invites = array();
$row = DB::fetch_first("SELECT * FROM " . DB::table('setting') . " WHERE skey='invite'");
if ($row) {
	$invites = unserialize($row['svalue']);
}
phpcom::$G['lang']['admin'] = 'setting';
if (checksubmit(array('btnsubmit', 'submit_button'))) {
	switch ($action) {
		case "generator": generator_invitecode();
			break;
		case "delete": delete_invitecode();
			break;
		case "save": save_invite_setting();
			break;
		default: break;
	}
} else {
	$current = '';
	$active = 'first';
	if ($action == 'admincode' || $action == 'generator') {
		$current = 'menu_invite_' . $action;
		$active = $action;
	}
	admin_header('menu_invite', $current);
	$navarray = array(
		array(
			'title' => 'menu_invite_basic',
			'url' => '?action=basic&m=invite',
			'name' => 'first',
			'onclick' => ''
		),
		array(
			'title' => 'menu_invite_admincode',
			'url' => '?action=admincode&m=invite',
			'name' => 'admincode',
			'onclick' => ''
		),
		array(
			'title' => 'menu_invite_generator',
			'url' => '?action=generator&m=invite',
			'name' => 'generator',
			'onclick' => ''
		)
	);
    phpcom_cache::load('usergroup');
	$adminhtml = phpcom_adminhtml::instance();
    $adminhtml->activetabs('global');
	$adminhtml->navtabs($navarray, $active);
	if ($action == 'generator') {
        $adminhtml->tablesetmode = FALSE;
		$adminhtml->form('m=invite', array(array('action', 'generator')));
		$adminhtml->table_header('setting_invite_generator', 3);
		$adminhtml->table_setting('setting_invite_generator_number', 'num', 10, 'text');
        $adminhtml->table_td(array(
            array('setting_invite_groupid', FALSE),
            array(select_usergroup('groupid', FALSE, FALSE), TRUE),
            array('setting_invite_groupid_comments', FALSE, '', '', 'tips'),
        ), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
	} else if ($action == 'admincode') {
		$type = isset(phpcom::$G['gp_type']) ? intval(phpcom::$G['gp_type']) : 0;
		$inviter = isset(phpcom::$G['gp_inviter']) ? phpcom::$G['gp_inviter'] : '';
		$invitee = isset(phpcom::$G['gp_invitee']) ? phpcom::$G['gp_invitee'] : '';
        $ordirid = isset(phpcom::$G['gp_orderid']) ? phpcom::$G['gp_orderid'] : '';
		$ordinal_delete = 'ordinal';
		$i = 1;
		$validtime = intval($invites['validtime']) * 86400;
		$timediff = intval(TIMESTAMP - $validtime);
		if ($type == 1 || $type == 3)
			$ordinal_delete = 'delete';
		$adminhtml->form('', array(array('m', 'invite'), array('action', 'admincode')), ' method="get" name="findform"');
		$adminhtml->table_header('find', 7);
		$adminhtml->table_td(array(
			array('find', FALSE),
			array($adminhtml->select('setting_invite_type_selects', 'type', $type,'style="width:120px;"'), TRUE),
			array('inviter', FALSE),
			array('<input type="text" class="input" size="22" name="inviter" value="' . htmlcharsencode($inviter) . '" />', TRUE),
			array('invitee', FALSE),
			array('<input type="text" class="input" size="22" name="invitee" value="' . htmlcharsencode($invitee) . '" />', TRUE),
			array($adminhtml->submit_button('submit', 'btnfind', 'button'), TRUE)
		));
		$adminhtml->table_end('</form>');
		$adminhtml->form('m=invite', array(array('action', 'delete')));
		$adminhtml->table_header();
		$adminhtml->table_th(array(
			array($ordinal_delete, 'width="5%" noWrap="noWrap"'),
			array('invitecode', 'width="15%" noWrap="noWrap"'),
			array('inviter', 'width="16%"'),
            array('usergroup', 'width="9" noWrap="noWrap"'),
			array('maketime', 'width="13%"'),
			array('deadline', 'width="13%"'),
			array('status', 'width="8%"'),
			array('type', 'width="9%"'),
			array('invitee', 'width="12%"')
		));
		$condition = ' 1=1';
		$queryurl = "&type=$type";
		if ($type == 1) {
			$condition = " status='1'";
		} elseif ($type == 2) {
			$condition = " status='0' AND dateline>='$timediff'";
		} elseif ($type == 3) {
			$condition = " status='0' AND dateline<'$timediff'";
		}
		if ($inviter) {
			$condition .= " AND inviter='$inviter'";
			$queryurl .= "&inviter=$inviter";
		}
		if ($invitee) {
			$condition .= " AND invitee='$invitee'";
			$queryurl .= "&invitee=$invitee";
		}
        if($ordirid){
            $condition .= " ordirid='$ordirid'";
			$queryurl .= "&ordirid=$ordirid";
        }
		// 获取总记录数
		$totalrec = DB::result_first("SELECT COUNT(*) FROM " . DB::table('invitecode') . " WHERE $condition");
		$pagenow = $page;  // 当前页
		$pagesize = 50;  // 每页大小
		$pagecount = @ceil($totalrec / $pagesize);  //计算总页数
		$pagenow > $pagecount && $pagenow = 1;
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$sql = DB::buildlimit("SELECT * FROM " . DB::table('invitecode') . " WHERE $condition ORDER BY id DESC", $pagesize, $pagestart);
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			if ($type == 1 || $type == 3) {
				$ordinal_delete = '<input type="checkbox" class="checkbox" name="deleter[]" value="' . $row['id'] . '" />';
			} else {
				$ordinal_delete = ($pagenow - 1) * $pagesize + $i++;
			}
			$invite_types = adminlang('setting_invite_types');
			$row['type'] = $invite_types[$row['type']];
            $daedtime = $row['dateline'] + $validtime;
			$invite_status = adminlang('setting_invite_status');
			if ($row['status'] == 0 && $row['dateline'] < $timediff) {
				$row['status'] = $invite_status[2];
                $daedtime = '<em class="f10 c4">' . fmdate($daedtime) . '</em>';
			} else if ($row['status'] == 0 && $row['dateline'] >= $timediff) {
				$row['status'] = $invite_status[0];
                $daedtime = '<em class="f10">' . fmdate($daedtime) . '</em>';
			} else {
				$row['status'] = $invite_status[1];
                $daedtime = '<em class="f10 c1">' . fmdate($row['usedate']) . '</em>';
			}
			if($row['groupid'] > 7){
                $row['usergroup'] = phpcom::$G['usergroup'][$row['groupid']]['grouptitle'];
            }else{
                $row['usergroup'] = '<span class="c4">'.adminlang('defaultgroup').'</span>';
            }
			$adminhtml->table_td(array(
				array($ordinal_delete, TRUE, 'align="center" noWrap="noWrap"'),
				array($row['code'], TRUE, 'align="center"'),
				array($row['inviter'], TRUE, 'align="center" noWrap="noWrap"'),
                array($row['usergroup'], TRUE, 'align="center" noWrap="noWrap"'),
				array('<em class="f10">' . fmdate($row['dateline']) . '</em>', TRUE, 'align="center"'),
				array($daedtime, TRUE, 'align="center"'),
				array($row['status'], TRUE, 'align="center"'),
				array($row['type'], TRUE, 'align="center"'),
				array('<span class="c2">'.$row['invitee'].'</span>', TRUE, 'align="center"')
			));
		}
		if ($type == 1 || $type == 3) {
			$adminhtml->table_td(array(
				array($adminhtml->checkall() . ' ' . $adminhtml->del_submit(), TRUE, 'colspan="9"')
			));
		}
		$showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=invite&action=admincode$queryurl") . '</var>';
		$adminhtml->table_td(array(
			array($showpage, TRUE, 'colspan="9" align="right" id="pagecode"')
				), NULL, FALSE, NULL, NULL, FALSE);
	} else {
		$adminhtml->form('m=invite', array(array('action', 'save')));
		$adminhtml->table_header('setting_invite_basic', 3);
		$adminhtml->table_setting('setting_invite_validtime', 'inviteset[validtime]', intval($invites['validtime']), 'text');
		$adminhtml->table_setting('setting_invite_invitercredit', 'inviteset[invitercredit]', intval($invites['invitercredit']), 'text');
		$adminhtml->table_setting('setting_invite_inviteecredit', 'inviteset[inviteecredit]', intval($invites['inviteecredit']), 'text');
        $adminhtml->table_setting('setting_invite_credit_type', 'inviteset[creditfield]', $invites['creditfield'], 'select');
        $adminhtml->table_setting('setting_invite_default_groupid', 'inviteset[groupid]', intval($invites['groupid']), 'radios');
		$adminhtml->table_setting('setting_invite_sendemail', 'inviteset[sendemail]', intval($invites['sendemail']), 'radio');
		$adminhtml->table_setting('setting_invite_emaintext', 'inviteset[emaintext]', $invites['emaintext'], 'textarea');
		$adminhtml->table_setting('setting_invite_paystatus', 'inviteset[paystatus]', intval($invites['paystatus']), 'radio');
		$adminhtml->table_setting('setting_invite_money', 'inviteset[money]', intval($invites['money']), 'text');
		$adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
	}
	$adminhtml->table_end('</form>');
	admin_footer();
}

function generator_invitecode() {
	$num = isset(phpcom::$G['gp_num']) ? intval(phpcom::$G['gp_num']) : 0;
    $groupid = isset(phpcom::$G['gp_groupid']) ? intval(phpcom::$G['gp_groupid']) : 0;
	$num = $num > 100 ? 100 : $num;
    $groupid = $groupid > 7 ? $groupid : 0;
    $uid = phpcom::$G['uid'];
    $username = phpcom::$G['username'];
    $invitecodes = array();
	if ($num > 0) {
		$timestamp = phpcom::$G['timestamp'];
		for ($index = 0; $index < $num; $index++) {
			$random = strtolower(str_rand(16, FALSE, 15));
			$invitecodes[] = "('$uid', '$random', '$username', '$groupid', '$timestamp', '0', '2')";
		}
        if($invitecodes){
            DB::query("INSERT INTO " . DB::table('invitecode') . " (`uid`, `code`, `inviter`, `groupid`, `dateline`, `usedate`, `type`) VALUES " . implode(',', $invitecodes));
        }
	}
	admin_succeed('invitecode_generator_succeed', 'action=admincode&m=invite');
}

function delete_invitecode() {
	$deleteid = phpcom::$G['gp_deleter'];
	if ($deleteid) {
		$condition = 'id IN(' . implodeids($deleteid) . ')';
		DB::delete('invitecode', $condition);
	}
	admin_succeed('invitecode_delete_succeed', 'action=admincode&m=invite');
}

function save_invite_setting() {
    $allowfield = array('money', 'prestige', 'currency', 'praise');
	$inviteset = phpcom::$G['gp_inviteset'];
    $inviteset['creditfield'] = in_array($inviteset['creditfield'],$allowfield) ? $inviteset['creditfield'] : 'money';
	$invitevalue = '';
	if ($inviteset) {
		$key = 'invite';
		$value = serialize($inviteset);
		$invitevalue = "('$key', '$value', 'array')";
	}
	if ($invitevalue) {
		DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES $invitevalue");
		phpcom_cache::updater('setting');
	}

	admin_succeed('setting_succeed', 'm=invite');
}
?>
