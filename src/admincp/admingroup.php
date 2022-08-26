<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : admingroup.php    2012-1-31
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'main';
admin_header('menu_admingroup', $action ? $admintitle : '');
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('tools');
$navarray = array(
    array(
        'title' => 'menu_admingroup',
        'url' => '?m=admingroup',
        'id' => 'admingroup_member',
        'name' => 'admingroup_member'
    ),
    array(
        'title' => 'menu_admingroup_group',
        'url' => '?m=admingroup&action=group',
        'id' => 'admingroup_group',
        'name' => 'admingroup_group'
    )
);
if ($action == 'group') {
    $adminhtml->navtabs($navarray, 'admingroup_group');
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        $adminhtml->form('m=admingroup', array(array('action', 'group')));
        $adminhtml->table_header('menu_admingroup_group', 3);
        $adminhtml->table_td(array(
            array('deletecheckbox', FALSE, 'width="5%" align="center" noWrap="noWrap"'),
            array('admingroup_usergroup', FALSE, 'width="20%"'),
            array('emptychar', FALSE)
                ), '', FALSE, ' tablerow', NULL, FALSE);
        $query = DB::query("SELECT * FROM " . DB::table('admingroup') . " ORDER BY admingid ASC");
        while ($row = DB::fetch_array($query)) {
            $admingid = $row['admingid'];
            $checkbox = 'name="delete[]" value="' . $admingid . '"';
            if ($admingid < 3) {
                $checkbox = 'name="disabled[]" value="" disabled';
            }
            $edit = $adminhtml->edit_word('edit', "action=perms&m=admingroup&admingid=$admingid");
            if ($admingid == 0) {
                $edit = '&nbsp;';
            }
            $adminhtml->table_td(array(
                array('<input type="checkbox" class="checkbox" ' . $checkbox . ' />', TRUE),
                array($adminhtml->inputedit("groupname[$admingid]", $row['groupname'], 15, 'left'), TRUE),
                array($edit, TRUE)
            ));
        }
        $adminhtml->table_td(array(
            array('newadd', FALSE, 'noWrap="noWrap"'),
            array('<input class="input t15" name="groupnamenew" type="text" />', TRUE, 'colspan="2"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_td(array(
            array($adminhtml->submit_button(), TRUE, 'colspan="3"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        $groupnames = phpcom::$G['gp_groupname'];
        $delete = isset(phpcom::$G['gp_delete']) ? stripempty(phpcom::$G['gp_delete']) : null;
        if ($delete) {
            $admingids = implodeids($delete);
            DB::query("DELETE FROM " . DB::table('admingroup') . " WHERE admingid IN ($admingids)");
            DB::query("DELETE FROM " . DB::table('adminmember') . " WHERE admingid IN ($admingids)");
            foreach ($delete as $value) {
                unset($groupnames[$value]);
            }
            unset($delete);
        }
        $groupnamenew = striphtml(phpcom::$G['gp_groupnamenew']);
        if ($groupnamenew) {
            $maxadmingid = intval(DB::result_first("SELECT MAX(admingid) FROM " . DB::table('admingroup'))) + 1;
            DB::insert('admingroup', array('admingid' => $maxadmingid, 'groupname' => $groupnamenew, 'permission' => 'a:0:{}'));
        }
        if ($groupnames) {
            foreach ($groupnames as $admingid => $value) {
                $groupname = striphtml($value);
                DB::update('admingroup', array('groupname' => $groupname), "admingid='$admingid'");
            }
        }
        phpcom_cache::updater('admingroup');
        admin_succeed('admingroup_succeed', 'm=admingroup&action=group');
    }
} elseif ($action == 'perms') {
    $adminhtml->navtabs($navarray, 'admingroup_group');
    $admingid = intval(phpcom::$G['gp_admingid']);
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        $group = DB::fetch_first("SELECT admingid, groupname, permission FROM " . DB::table('admingroup') . " WHERE admingid='$admingid'");
        $perms = unserialize($group['permission']);
        $groupname = $group['groupname'];
        $data = getactiondata();
        permcheckall();
        $adminhtml->vars = array('name' => $groupname);
        $adminhtml->form('m=admingroup', array(array('action', 'perms'), array('admingid', $admingid)));
        $adminhtml->table_header('admingroup_group_perms', array('name' => $groupname));
        foreach ($data['cats'] as $topkey) {
            if (!$data['actions'][$topkey]) {
                continue;
            }
            $checkedall = true;
            $row = '<tr class="perms"><td class="tablerow1" id="perms_' . $topkey . '">';
            foreach ($data['actions'][$topkey] as $k => $item) {
                if (!$item) {
                    continue;
                }
                $checked = @in_array($item[1], $perms);
                if (!$checked) {
                    $checkedall = FALSE;
                }
                $row .= $item[1] ? '<div class="item' . ($checked ? ' checked' : '') . '"><label class="txt"><input name="permnew[]" value="' . $item[1] . '" class="checkbox" type="checkbox" ' . ($checked ? 'checked="checked" ' : '') . ' onclick="checkclk(this)" /> ' . adminlang($item[0]) . "</label></div>\r\n" : '';
            }
            $row .= "</td></tr>\r\n";
            if ($topkey != 'setting') {
                $s = '<label><input class="checkbox" type="checkbox" onclick="permcheckall(this, \'perms_' . $topkey . '\')" ' . ($checkedall ? 'checked="checked" ' : '') . '/> ' . adminlang('header_' . $topkey) . '</label>';
            } else {
                $s = adminlang('admingroup_perm_setting');
            }
            $adminhtml->table_td(array(array($s, TRUE)), '', FALSE, ' tablerow', NULL, FALSE);
            echo $row;
        }
        $adminhtml->count = 1;
        $adminhtml->table_td(array(
            array($adminhtml->submit_button(), TRUE)
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        $permnew = !empty(phpcom::$G['gp_permnew']) ? phpcom::$G['gp_permnew'] : array();
        $permnew = serialize($permnew);
        DB::update('admingroup', array('permission' => $permnew), "admingid='$admingid'");
        phpcom_cache::updater('admingroup');
        admin_succeed('admingroup_perms_succeed', "m=admingroup&action=perms&admingid=$admingid");
    }
} elseif ($action == 'member') {
    $adminhtml->navtabs($navarray, 'admingroup_member');
    $id = intval(phpcom::$G['gp_id']);
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        $member = DB::fetch_first("SELECT * FROM " . DB::table('adminmember') . " WHERE uid='$id'");
        if (!$member) {
            admin_message('admingroup_perm_member_noexists');
        }
        $username = DB::result_first("SELECT username FROM " . DB::table("members") . " WHERE uid='$id'");
        $admingid = empty(phpcom::$G['gp_admingid']) ? $member['admingid'] : phpcom::$G['gp_admingid'];
        $member['permcustom'] = empty(phpcom::$G['gp_admingid']) || phpcom::$G['gp_admingid'] == $member['admingid'] ? unserialize($member['permcustom']) : array();
        $query = DB::query("SELECT * FROM " . DB::table('admingroup') . " WHERE admingid>'0' ORDER BY admingid");
        $perms = array();
        $groupselect = '<select name="admingidnew" class="select t20" onchange="location.href=\'' . ADMIN_SCRIPT . '?m=admingroup&action=member&id=' . $id . '&admingid=\' + this.value">';
        while ($group = DB::fetch_array($query)) {
            if ($group['admingid'] == $admingid) {
                $perms = @unserialize($group['permission']);
            }
            $groupselect .= '<option value="' . $group['admingid'] . '"' . ($group['admingid'] == $admingid ? ' selected="selected"' : '') . '>' . $group['groupname'] . '</option>';
        }
        $groupselect .= '</select> ';
        $groupselect .= adminlang('admingroup_fullname_input', array('name' => htmlcharsencode($member['fullname'])));
        permcheckall();
        $data = getactiondata();
        $adminhtml->vars = array('name' => $username);
        $adminhtml->form('m=admingroup', array(array('action', 'member'), array('id', $id)));
        $adminhtml->table_header('admingroup_member_perms', array('name' => $username));
        $adminhtml->table_td(array(
            array($groupselect, TRUE)
        ));
        foreach ($data['cats'] as $topkey) {
            if (!$data['actions'][$topkey]) {
                continue;
            }
            $checkedall = TRUE;
            $row = '<tr class="perms"><td class="tablerow1" id="perms_' . $topkey . '">';
            foreach ($data['actions'][$topkey] as $item) {
                if (!$item) {
                    continue;
                }
                $checked = @in_array($item[1], $perms);
                $customchecked = @in_array($item[1], $member['permcustom']);
                $extra = $checked ? ($customchecked ? '' : 'checked="checked" ') . ' onclick="checkclk(this)"' : 'disabled="disabled" ';
                if (!$checked || $customchecked) {
                    $checkedall = FALSE;
                }
                $row .= '<div class="item' . ($checked && !$customchecked ? ' checked' : '') . '"><label class="txt"><input name="permnew[]" value="' . $item[1] . '" class="checkbox" type="checkbox" ' . $extra . '/> ' . adminlang($item[0]) . "</label></div>\r\n";
            }
            $row .= "</td></tr>\r\n";
            if ($topkey != 'setting') {
                $s = '<label><input class="checkbox" type="checkbox" onclick="permcheckall(this, \'perms_' . $topkey . '\')" ' . ($checkedall ? 'checked="checked" ' : '') . '/> ' . adminlang('header_' . $topkey) . '</label>';
            } else {
                $s = adminlang('admingroup_perm_setting');
            }
            $adminhtml->table_td(array(array($s, TRUE)), '', FALSE, ' tablerow', NULL, FALSE);
            echo $row;
        }
        $adminhtml->count = 1;
        $adminhtml->table_td(array(
            array($adminhtml->submit_button(), TRUE)
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
    	$fullname = isset(phpcom::$G['gp_fullname']) ? stripstring(phpcom::$G['gp_fullname']) : '';
        $permnew = !empty(phpcom::$G['gp_permnew']) ? phpcom::$G['gp_permnew'] : array();
        $admingidnew = max(1, intval(phpcom::$G['gp_admingidnew']));
        $group = DB::fetch_first("SELECT permission FROM " . DB::table('admingroup') . " WHERE admingid='$admingidnew'");
        $perms = unserialize($group['permission']);
        $permcustom = addslashes(serialize(array_diff($perms, $permnew)));
        DB::update('adminmember', array(
        'admingid' => $admingidnew, 
        'fullname' => $fullname,
        'permcustom' => $permcustom
        ), "uid='$id'");
        admin_succeed('admingroup_perms_succeed', "m=admingroup&action=member&id=$id");
    }
} else {
    $adminhtml->navtabs($navarray, 'admingroup_member');
    $founderdates = array();
    $founders = phpcom::$config['admincp']['founder'] !== '' ? explode(',', str_replace(' ', '', addslashes(phpcom::$config['admincp']['founder']))) : array();
    if ($founders) {
        $founderexists = TRUE;
        $fuid = $fname = array();
        foreach ($founders as $founder) {
            if (is_numeric($founder)) {
                $fuid[] = $founder;
            } else {
                $fname[] = $founder;
            }
        }
        $query = DB::query("SELECT uid, username, regdate FROM " . DB::table('members') . " WHERE " . ($fuid ? "uid IN (" . implodeids($fuid) . ")" : '0') . " OR " . ($fname ? "username IN (" . implodevalue($fname) . ")" : '0'));
        $founders = array();
        while ($founder = DB::fetch_array($query)) {
            $founders[$founder['uid']] = $founder['username'];
            $founderdates[$founder['uid']] = $founder['regdate'];
        }
    } else {
        $founderexists = FALSE;
        $query = DB::query("SELECT uid, username, regdate FROM " . DB::table('members') . " WHERE adminid='1'");
        $founders = array();
        while ($founder = DB::fetch_array($query)) {
            $founders[$founder['uid']] = $founder['username'];
            $founderdates[$founder['uid']] = $founder['regdate'];
        }
    }

    if (!checksubmit(array('submit', 'btnsubmit'))) {
        $adminhtml->form('m=admingroup');
        $adminhtml->table_header('menu_admingroup');
        $adminhtml->table_td(array(array('admingroup_tips', FALSE, 'colspan="5"')), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_td(array(
            array('deletecheckbox', FALSE, 'width="5%" align="center" noWrap="noWrap"'),
            array('admingroup_username', FALSE, 'width="20%"'),
            array('admingroup_usergroup', FALSE, 'width="20%"'),
            array('emptychar', FALSE),
        	array('admingroup_dateline', FALSE)
                ), '', FALSE, ' tablerow', NULL, FALSE);
        $query = DB::query("SELECT * FROM " . DB::table('admingroup') . " ORDER BY admingid");
        $groupselect = '<select name="admingid" class="select t15">';
        $groups = array();
        while ($group = DB::fetch_array($query)) {
            if ($group['admingid']) {
                $groupselect .= '<option value="' . $group['admingid'] . '">' . $group['groupname'] . '</option>';
            }
            $groups[$group['admingid']] = $group['groupname'];
        }
        $groupselect .= '</select>';
        $query = DB::query("SELECT * FROM " . DB::table('adminmember'));
        $members = $adminmembers = array();
        while ($adminmember = DB::fetch_array($query)) {
            $adminmembers[$adminmember['uid']] = $adminmember;
        }
        foreach ($founders as $uid => $founder) {
            $members[$uid] = array('uid' => $uid, 'username' => $founder, 'dateline' => $founderdates[$uid], 'fullname' => '', 'groupname' => $groups[0]);
        }
        if ($adminmembers) {
            $query = DB::query("SELECT uid, username FROM " . DB::table('members') . " WHERE uid IN (" . implodeids(array_keys($adminmembers)) . ")");
            while ($member = DB::fetch_array($query)) {
                if (isset($members[$member['uid']])) {
                    DB::delete('adminmember', array('uid' => $member['uid']));
                    DB::delete('adminsession', array('uid' => $member['uid']));
                    continue;
                }
                $member['dateline'] = $adminmembers[$member['uid']]['dateline'];
                $member['fullname'] = $adminmembers[$member['uid']]['fullname'];
                $member['groupname'] = $groups[$adminmembers[$member['uid']]['admingid']];
                if (!$founderexists && in_array($member['uid'], array_keys($founders))) {
                    $member['groupname'] = $groups[0];
                }
                $members[$member['uid']] = $member;
            }
        }
        
        foreach ($members as $id => $member) {
            $isfounder = array_key_exists($id, $founders);
            $checkbox = 'name="delete[]" value="' . $id . '"';
            $edit = $adminhtml->edit_word('edit', "action=member&m=admingroup&id=$id", ' &nbsp; ');
            if ($isfounder) {
                $checkbox = 'name="disabled[]" value="" disabled';
                $edit = '&nbsp;';
                $member['username'] = '<span class="c6">' . $member['username'] . '</span>';
            }
            $edit .= $adminhtml->edit_word('threads_browse', "m=threads&uid=$id");
            $fullname = empty($member['fullname']) ? '' : " (<span class=\"c2 fb\">{$member['fullname']}</span>)";
            $adminhtml->table_td(array(
                array('<input type="checkbox" class="checkbox" ' . $checkbox . ' />', TRUE),
                array($member['username'] . $fullname, TRUE),
                array($member['groupname'], TRUE),
                array($edit, TRUE),
            	array('<em class="c1">' . fmdate($member['dateline'], 'dt') . '</em>', TRUE)
            ));
        }
        $adminhtml->table_td(array(
            array('newadd', FALSE, 'noWrap="noWrap"'),
            array('<input class="input t15" name="adminusername" type="text" />', TRUE),
            array($groupselect, TRUE, 'colspan="3"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_td(array(
            array($adminhtml->submit_button(), TRUE, 'colspan="5"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        if (!empty(phpcom::$G['gp_adminusername'])) {
        	$adminusername = trim(phpcom::$G['gp_adminusername']);
            $newadminuid = DB::result_first("SELECT uid FROM " . DB::table("members") . " WHERE username='$adminusername'");
            if (!$newadminuid) {
                admin_message('admingroup_member_noexists', '', array('name' => $adminusername));
            }
            if (DB::result_first("SELECT count(*) FROM " . DB::table('adminmember') . " WHERE uid='$newadminuid'") || array_key_exists($newadminuid, $founders)) {
                admin_message('admingroup_member_duplicate', '', array('name' => $adminusername));
            }
            $admingid = max(1, phpcom::$G['gp_admingid']);
            DB::insert('adminmember', array(
	            'uid' => $newadminuid, 
	            'admingid' => $admingid,
	            'dateline' => time(),
	            'fullname' => empty(phpcom::$G['gp_fullname']) ? '' : stripstring(phpcom::$G['gp_fullname']), 
	            'permcustom' => 'a:0:{}'
            ));
            DB::update('members', array('adminid' => 1, 'groupid' => 1), "uid='$newadminuid'");
        }
        $delete = isset(phpcom::$G['gp_delete']) ? phpcom::$G['gp_delete'] : null;
        if ($delete) {
            if($uids = implodeids($delete)){
	            DB::query("DELETE FROM " . DB::table('adminmember') . " WHERE uid IN ($uids)");
	            DB::query("DELETE FROM " . DB::table('adminsession') . " WHERE uid IN ($uids)");
	            DB::query("DELETE FROM " . DB::table('adminmenu') . " WHERE uid IN ($uids)");
	            DB::query("DELETE FROM " . DB::table('adminfav') . " WHERE uid IN ($uids)");
	            DB::update('members', array('adminid' => 0, 'groupid' => 11), "uid IN ($uids)");
            }
        }
        phpcom_cache::updater('admingroup');
        admin_succeed('admingroup_succeed', 'm=admingroup');
    }
}

admin_footer();

function select_admingroups($founders = 0) {
    $result = array();
    $option = '<select name="admingid" class="select t15">';
    $sql = "SELECT admingid,groupname FROM " . DB::table('admingroup') . " ORDER BY admingid";
    $query = DB::query($sql);
    while ($row = DB::fetch_array($query)) {
        if ($row['admingid'] || $founders) {
            $result[$row['admingid']] = $row;
            $option .= '<option value="' . $row['admingid'] . '"';
            $option .= $row['admingid'] == 1 ? ' SELECTED' : '';
            $option .= ">{$row['groupname']}</option>";
        }
    }
    $option .= "</select>\r\n";
    return $option;
}

function getactiondata() {
    require loadlibfile('menu', 'inc/admincp');
    require loadlibfile('perm', 'inc/admincp');
    unset($topmenu['index'], $menu['index'], $topmenu['help'], $topmenu['logout']);
    $actioncat = $actionarray = array();
    $actioncat[] = 'setting';
    $actioncat = array_merge($actioncat, array_keys($topmenu));
    $actionarray['setting'][] = array('admingroup_perm_allowpost', '_allowpost');
    foreach ($menu as $tkey => $items) {
        foreach ($items as $item) {
            $actionarray[$tkey][] = $item;
        }
    }
    return array('actions' => $actionarray, 'cats' => $actioncat);
}

function permcheckall() {
    echo <<<EOT
<script type="text/JavaScript">
function permcheckall(obj, perms, t) {
    var t = !t ? 0 : t;
    var checkboxs = $(perms).getElementsByTagName('INPUT');
    for(var i = 0; i < checkboxs.length; i++) {
        var e = checkboxs[i];
        if(e.type == 'checkbox') {
            if(!t) {
                if(!e.disabled) {
                    e.checked = obj.checked;
                }
            } else {
                if(obj != e) {
                    e.style.visibility = obj.checked ? 'hidden' : 'visible';
                }
            }
            e.parentNode.parentNode.className = e.checked ? 'item checked' : 'item';
        }
    }
}
function checkclk(obj) {
    var obj = obj.parentNode.parentNode;
    obj.className = obj.className == 'item' ? 'item checked' : 'item';
}
</script>    
EOT;
}

?>
