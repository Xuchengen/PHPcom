<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : member.php    2011-5-7 0:49:00
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'user';
@set_time_limit(1000);
admin_header('menu_members', $action ? $admintitle : '');

$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('user');
$navarray = array(
    array(
        'title' => 'menu_members',
        'url' => '?m=members&action=search&submit=yes',
        'id' => 'members_manage',
        'name' => 'members_manage'
    ),
    array(
        'title' => 'menu_members_search',
        'url' => '?m=members',
        'id' => 'members_search',
        'name' => 'members_search'
    ),
    array(
        'title' => 'menu_members_clean',
        'url' => '?m=members&action=clean',
        'id' => 'members_clean',
        'name' => 'members_clean'
    ),
    array(
        'title' => 'menu_members_add',
        'url' => '?m=members&action=add',
        'id' => 'members_add',
        'name' => 'members_add'
    )
);

$search_condition = array_merge($_GET, $_POST);
foreach ($search_condition as $k => $v) {
    if (in_array($k, array('m', 'action', 'submit_button', 'formhash', 'submit', 'page')) || $v === '') {
        unset($search_condition[$k]);
    }
}

if ($action == 'search') {
    $adminhtml->navtabs($navarray, 'members_manage');
    if (!checksubmit(array('submit', 'btnsubmit'), 1)) {
        $adminhtml->form('m=members', array(array('action', 'search')), 'name="membersearchform" id="membersearchform"');
        $adminhtml->table_header('members_search', 3);
        $adminhtml->table_td(array(array('members_search_tips', FALSE, 'colspan="3"')), NULL, FALSE, NULL, NULL, FALSE);
        member_search_form($adminhtml, $action);
    } else {
        $totalrec = member_count($search_condition, $urladd);
        $adminhtml->form('m=members', array(array('action', 'clean')), 'name="memberdeleteform" id="memberdeleteform"');
        $adminhtml->vars = array('num' => $totalrec);
        $adminhtml->table_header('members_search_title', array('num' => $totalrec));
        $adminhtml->vars = null;

        $adminhtml->table_td(array(
            array('delete', FALSE, 'width="5%" align="center" noWrap="noWrap"'),
            array('username', FALSE, 'width="15%"'),
            array('email', FALSE, 'width="20%"'),
            array('credits', FALSE, 'width="10%"'),
        	array('login', FALSE, 'width="5%" noWrap="noWrap"'),
            array('usergroup', FALSE, 'width="10%"'),
            array('members_regdate', FALSE, 'width="15%"'),
            array('operation', FALSE, 'width="20%" noWrap="noWrap"')
                ), '', FALSE, ' tablerow', NULL, FALSE);

        if ($totalrec > 0) {
            $pagesize = intval(phpcom::$config['admincp']['pagesize']);
            $pagecount = @ceil($totalrec / $pagesize);  //¼ÆËã×ÜÒ³Êý
            $pagenow = max(1, min($pagecount, intval($page)));
            $limit_offset = floor(($pagenow - 1) * $pagesize);
            $usergroups = array();
            foreach (phpcom::$G['usergroup'] as $key => $group) {
                switch ($group['type']) {
                    case 'system': $group['grouptitle'] = '<b>' . $group['grouptitle'] . '</b>';
                        break;
                    case 'default': $group['grouptitle'] = '<b>' . $group['grouptitle'] . '</b>';
                        break;
                    case 'special': $group['grouptitle'] = '<i>' . $group['grouptitle'] . '</i>';
                        break;
                }
                $usergroups[$key] = $group;
            }
            $arruid = MemberSearch::search($search_condition, $pagesize, $limit_offset);
            if($result = MemberSearch::searchresult($arruid)){
                $members_operation = adminlang('members_operation_group');
                $members_operation .= adminlang('members_operation_credit');
                $members_operation .= adminlang('members_operation_edit');
                $members_operation .= adminlang('members_operation_ban');
                $members_operation .= adminlang('members_operation_thread');
                foreach ($result as $row) {
                    $uid = $row['uid'];
                    $adminhtml->table_td(array(
                        array('<input type="checkbox" class="checkbox" name="delete[]" value="' . $row['uid'] . '" />', TRUE, 'align="center"'),
                        array('<a target="_blank" href="member.php?action=home&uid=' . $row['uid'] . '">' . $row['username'] . '</a>', TRUE),
                        array($row['email'], TRUE),
                        array($row['credits'], TRUE),
                    	array($row['logins'], TRUE),
                        array($usergroups[$row['groupid']]['grouptitle'], TRUE),
                        array(fmdate($row['regdate'], 'dt', 'd'), TRUE),
                        array(str_replace('{uid}', $uid, $members_operation), TRUE)
                    ));
                }
                $adminhtml->table_td(array(
                    array($adminhtml->checkall() . ' ' . $adminhtml->del_submit(), TRUE, 'colspan="8"')
                        ), NULL, FALSE, NULL, NULL, FALSE);
                $showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=members&action=search&submit=yes$urladd") . '</var>';
                $adminhtml->table_td(array(
                    array($showpage, TRUE, 'colspan="8" align="right" id="pagecode"')
                        ), NULL, FALSE, NULL, NULL, FALSE);
            }
        }
    }
    $adminhtml->table_end('</form>');
} else if ($action == 'clean') {
    $adminhtml->navtabs($navarray, 'members_clean');
    if (!checksubmit(array('submit', 'btnsubmit'), 1)) {
        $adminhtml->form('m=members', array(array('action', 'clean')), 'name="membersearchform" id="membersearchform"');
        $adminhtml->table_header('members_clean', 3);
        member_search_form($adminhtml, $action);
        $adminhtml->table_end('</form>');
    } else {
        if (!$search_condition) {
            admin_message('members_not_find_deleteuser');
        }
        $extra = '';
        $membernum = 0;
        $uidarray = array();
        if (isset(phpcom::$G['gp_delete']) && !empty(phpcom::$G['gp_delete'])) {
            $uidarray = phpcom::$G['gp_delete'];
            $result = MemberSearch::getmember($uidarray);
            $uidarray = array();
            foreach ($result as $row) {
                if ($membernum < 2000 || !empty(phpcom::$G['gp_delete'])) {
                    $extra .= '<input type="hidden" name="delete[]" value="' . $row['uid'] . '" />';
                }
                $uidarray[] = $row['uid'];
            }
            $membernum = count($uidarray);
        } else {
            $membernum = member_count($search_condition, $urladd);
            $uidarray = MemberSearch::getuid($search_condition);
            foreach ($uidarray as $uid) {
                $extra .= '<input type="hidden" name="delete[]" value="' . $uid . '" />';
            }
        }
        if ((empty($membernum) || empty($uidarray))) {
            admin_message('members_not_found_deleteuser');
        }
        $msgargs = array(
            'form' => TRUE,
            'submit' => TRUE,
            'cancel' => TRUE,
            'action' => '?m=members&action=delete'
        );
        admin_showmessage('members_clean_delete_message', array('num' => $membernum), $msgargs, $extra);
    }
} else if ($action == 'delete') {
    $adminhtml->navtabs($navarray, 'members_clean');
    if (!checksubmit(array('submit', 'btnsubmit'), 1)) {
        phpcom::header('Location: ' . ADMIN_SCRIPT . '?m=members&action=clean');
    } else {
        $membernum = 0;
        $extra = '';
        $uidarray = array();
        if (!empty(phpcom::$G['gp_delete'])) {
            $uidarray = phpcom::$G['gp_delete'];
            $result = MemberSearch::getmember($uidarray);
            $uidarray = array();
            foreach ($result as $row) {
                if ($membernum < 2000 || !empty(phpcom::$G['gp_delete'])) {
                    $extra .= '<input type="hidden" name="delete[]" value="' . $row['uid'] . '" />';
                }
                $uidarray[] = $row['uid'];
            }
            $membernum = count($uidarray);
        }
        if ((empty($membernum) || empty($uidarray))) {
            admin_message('members_not_found_deleteuser');
        }

        if (empty(phpcom::$G['gp_alldata'])) {
            include_once loadlibfile('delete');
            $uidarray = phpcom::$G['gp_delete'];
            $deletenum = count($uidarray);
            delete_member($uidarray);
            admin_succeed('members_delete_succeed', 'm=members&action=clean', array('deletenum' => $deletenum));
        } else {
            $uidarray = isset(phpcom::$G['gp_delete']) ? phpcom::$G['gp_delete'] : null;
            $deletenum = count($uidarray);
            $deleteitem = isset(phpcom::$G['gp_deleteitem']) ? trim(phpcom::$G['gp_deleteitem']) : null;
            $msgargs = array(
                'form' => TRUE,
                'autosubmit' => TRUE,
                'action' => '?m=members&action=delete&alldata=1&submit=yes'
            );
            if (empty($deleteitem)) {
                $msgargs['action'] = $msgargs['action'] . '&deleteitem=thread';
                admin_showmessage('members_deleting_thread', null, $msgargs, $extra);
            }
            if ($deleteitem == 'thread') {
                include_once loadlibfile('delete');
                delete_article_thread(0, $uidarray);
                delete_softinfo_thread(0, $uidarray);
                delete_video_thread(0, $uidarray);
                delete_photo_thread(0, $uidarray);
                $msgargs['action'] = $msgargs['action'] . '&deleteitem=comment';
                admin_showmessage('members_deleting_comment', null, $msgargs, $extra);
            }
            if ($deleteitem == 'comment') {
                include_once loadlibfile('delete');
                $msgargs['action'] = $msgargs['action'] . '&deleteitem=allitem';
                delete_comment(0, $uidarray);
                admin_showmessage('members_deleting_allitem', null, $msgargs, $extra);
            }
            if ($deleteitem == 'allitem') {
                include_once loadlibfile('delete');
                delete_member($uidarray);
                admin_succeed('members_delete_succeed', 'm=members&action=clean', array('deletenum' => $deletenum));
            }
        }
    }
} else if ($action == 'add') {
    $adminhtml->navtabs($navarray, 'members_add');
    if (!checksubmit(array('submit', 'btnsubmit'), 1)) {
        $adminhtml->tablesetmode = TRUE;
        $adminhtml->form('m=members', array(array('action', 'add')));
        $adminhtml->table_header('members_add', 2);
        $adminhtml->table_setting('!members_username', 'username', '', 'text');
        $adminhtml->table_setting('!members_password', 'password', '', 'text');
        $adminhtml->table_setting('!members_email', 'email', '', 'text');
        $groupoptions = array();
        $groupoptions['optgroup1'] = adminlang('membergroup');
        $groupoptions['10'] = phpcom::$G['usergroup'][10]['grouptitle'];
        $groupoptions['11'] = phpcom::$G['usergroup'][11]['grouptitle'];
        $groupoptions['/optgroup1'] = '';
        $groupoptions['optgroup2'] = adminlang('specialgroup');
        foreach (getusergroups('special') as $key => $value) {
            $groupoptions[$key] = $value['grouptitle'];
        }
        $groupoptions['/optgroup2'] = '';
        $groupoptions['optgroup3'] = adminlang('systemgroup');
        $groupoptions['1'] = phpcom::$G['usergroup'][1]['grouptitle'];
        $groupoptions['2'] = phpcom::$G['usergroup'][2]['grouptitle'];
        $groupoptions['3'] = phpcom::$G['usergroup'][3]['grouptitle'];
        $groupoptions['4'] = phpcom::$G['usergroup'][4]['grouptitle'];
        $groupoptions['7'] = phpcom::$G['usergroup'][7]['grouptitle'];
        $groupoptions['/optgroup3'] = '';
        $adminhtml->table_setting('!usergroup', 'groupid', '11', 'select', '', $groupoptions);
        $adminhtml->table_setting('submit', 'submit', '', 'submit');
        $adminhtml->table_end('</form>');
    } else {
        $username = trim(phpcom::$G['gp_username']);
        $password = trim(phpcom::$G['gp_password']);
        $email = trim(phpcom::$G['gp_email']);
        $groupid = intval(phpcom::$G['gp_groupid']);
        if (empty($username)) {
            admin_message('members_username_emptyof');
        }
        if (empty($password)) {
            admin_message('members_password_emptyof');
        }
        if (empty($email)) {
            admin_message('members_email_emptyof');
        }
        loaducenter();
        $status = uc_user_add($username, $password, $email, 0, $groupid);
        switch ($status) {
            case UC_USER_CHECK_USERNAME_INVALID:
                admin_message('members_username_invalid');
                break;
            case UC_USER_CHECK_USERNAME_BADWORD:
                admin_message('members_username_badword');
                break;
            case UC_USER_CHECK_USERNAME_EXISTED:
                admin_message('members_username_existed');
                break;
            case UC_USER_CHECK_EMAIL_INVALID:
                admin_message('members_email_format_invalid');
                break;
            case UC_USER_CHECK_EMAIL_DENIED:
                admin_message('members_email_domain_denied');
                break;
            case UC_USER_CHECK_EMAIL_EXISTED:
                admin_message('members_email_existed');
                break;
            default: break;
        }
        $uid = $status;
        if (!UC_SERVER_APP && $uid > 0) {
            include_once loadlibfile('member');
            if ($olduid = DB::result_first("SELECT uid FROM " . DB::table('members') . " WHERE username='$username'")) {
                if ($olduid != $uid) {
                    member_delete($olduid);
                }
            }
            $member = DB::fetch_first("SELECT * FROM " . DB::table('members') . " WHERE uid='$uid'");
            if ($member) {
                if (addslashes($member['username']) != $username) {
                    DB::query("UPDATE " . DB::table('members') . " SET username='$username' WHERE uid='$uid'");
                    $member['username'] = stripslashes($username);
                }
                if (addslashes($member['email']) != $email) {
                    DB::query("UPDATE " . DB::table('members') . " SET email='$email' WHERE uid='$uid'");
                    $member['email'] = stripslashes($email);
                }
            } else {
                $uid = member_add($username, $password, $email, $uid, $groupid);
            }
        }
        admin_succeed('members_add_succeed', "m=members&action=search&uid=$uid&submit=yes", array('username' => $username, 'uid' => $uid));
    }
} else if ($action == 'audit') {
    $navarray = array(
        array(
            'title' => 'menu_members',
            'url' => '?m=members&action=search&submit=yes',
            'id' => 'members_manage',
            'name' => 'members_manage'
        ),
        array(
            'title' => 'menu_members_audit',
            'url' => '?m=members&action=audit',
            'id' => 'members_audit',
            'name' => 'members_audit'
        )
    );
    $adminhtml->navtabs($navarray, 'members_audit');
    include loadlibfile('validate', 'admincp/member');
} else if ($action == 'profile') {
    $adminhtml->navtabs($navarray, 'members_manage');
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        
    } else {
        
    }
} else if ($action == 'edit') {
    $adminhtml->navtabs($navarray, 'members_manage');
    $uid = isset(phpcom::$G['gp_uid']) ? intval(phpcom::$G['gp_uid']) : 0;
    $username = isset(phpcom::$G['gp_username']) ? trim(phpcom::$G['gp_username']) : '';
    if (empty($uid) && empty($username)) {
        admin_error('undefined_action');
    } else {
        $condition = !empty($uid) ? "m.uid='$uid'" : "m.username='$username'";
    }
    $member = DB::fetch_first("SELECT m.*, mc.*, ms.*, mi.*, u.type, u.grouptitle FROM " . DB::table('members') . " m
		LEFT JOIN " . DB::table('usergroup') . " u ON u.groupid=m.groupid
		LEFT JOIN " . DB::table('member_count') . " mc ON mc.uid=m.uid
		LEFT JOIN " . DB::table('member_status') . " ms ON ms.uid=m.uid
		LEFT JOIN " . DB::table('member_info') . " mi ON mi.uid=m.uid
		WHERE $condition");
    if (!$member) {
        admin_message('members_edit_nonexistence');
    }
    $uid = $member['uid'];
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        $adminhtml->form('m=members', array(array('action', 'edit'), array('uid', $uid)));
        $adminhtml->table_header("menu_members_edit - {$member['username']}({$member['grouptitle']})", 2);
        $adminhtml->table_setting('members_edit_password', 'membernew[password]', '', 'text');
        $adminhtml->table_setting('members_edit_email', 'membernew[email]', $member['email'], 'text');
        $adminhtml->table_setting('members_edit_emailstatus', 'membernew[emailstatus]', $member['emailstatus'], 'radio');
        $adminhtml->table_setting('members_edit_status', 'membernew[status]', $member['status'], 'radios');
        $adminhtml->table_setting('members_edit_clearqacode', 'clearqacode', 0, 'radio');
        $adminhtml->table_setting('members_edit_threads', 'membercountnew[threads]', $member['threads'], 'text');
        $adminhtml->table_setting('members_edit_digests', 'membercountnew[digests]', $member['digests'], 'text');
        $adminhtml->table_setting('members_edit_regdate', 'membernew[regdate]', fmdate($member['regdate'], 'Y-m-d H:i:s'), 'text');
        $adminhtml->table_setting('members_edit_regip', 'memberstatusnew[regip]', $member['regip'], 'text');
        $adminhtml->table_setting('members_edit_lastvisit', 'memberstatusnew[lastvisit]', fmdate($member['lastvisit'], 'Y-m-d H:i:s'), 'text');
        $adminhtml->table_setting('members_edit_lastip', 'memberstatusnew[lastip]', $member['lastip'], 'text');
        $adminhtml->table_setting('members_edit_usersign', 'memberinfonew[usersign]', $member['usersign'], 'textarea');
        $adminhtml->table_setting('members_edit_realname', 'memberinfonew[realname]', $member['realname'], 'text');
        $adminhtml->table_setting('members_edit_gender', 'membernew[gender]', $member['gender'], 'radios');
        $adminhtml->table_setting('members_edit_birthday', 'memberinfonew[birthday]', $member['birthday'], 'text');
        $adminhtml->table_setting('members_edit_idcard', 'memberinfonew[idcard]', $member['idcard'], 'text');
        $adminhtml->table_setting('members_edit_company', 'memberinfonew[company]', $member['company'], 'text');
        $adminhtml->table_setting('members_edit_address', 'memberinfonew[address]', $member['address'], 'text');
        $adminhtml->table_setting('members_edit_homepage', 'memberinfonew[homepage]', $member['homepage'], 'text');
        $adminhtml->table_setting('members_edit_zipcode', 'memberinfonew[zipcode]', $member['zipcode'], 'text');
        $adminhtml->table_setting('members_edit_phone', 'memberinfonew[phone]', $member['phone'], 'text');
        $adminhtml->table_setting('members_edit_mobile', 'memberinfonew[mobile]', $member['mobile'], 'text');
        $adminhtml->table_setting('members_edit_fax', 'memberinfonew[fax]', $member['fax'], 'text');
        $adminhtml->table_setting('members_edit_qq', 'memberinfonew[qq]', $member['qq'], 'text');
        $adminhtml->table_setting('members_edit_msn', 'memberinfonew[msn]', $member['msn'], 'text');
        $adminhtml->table_setting('members_edit_taobao', 'memberinfonew[taobao]', $member['taobao'], 'text');
        $adminhtml->table_setting('submit', 'submit', '', 'submit');
        $adminhtml->table_end('</form>');
    } else {
        $membernew = phpcom::$G['gp_membernew'];
        $memberinfonew = phpcom::$G['gp_memberinfonew'];
        $membercountnew = phpcom::$G['gp_membercountnew'];
        $memberstatusnew = phpcom::$G['gp_memberstatusnew'];
        $membernew['regdate'] = intval(strtotime($membernew['regdate']));
        $memberstatusnew['lastvisit'] = intval(strtotime($memberstatusnew['lastvisit']));
        if ($uid == phpcom::$G['uid']) {
            $membernew['status'] = 0;
        }
        if (isset(phpcom::$G['gp_clearqacode']) && phpcom::$G['gp_clearqacode']) {
            $membernew['qacode'] = '';
        }
        include_once loadlibfile('member');
        member_edit($uid, $membernew, $membercountnew, $memberinfonew, $memberstatusnew);
        admin_succeed('members_edit_succeed', "m=members&action=edit&uid=$uid", array('username' => $member['username'], 'uid' => $uid));
    }
} else if ($action == 'group') {
    $adminhtml->navtabs($navarray, 'members_manage');
    $uid = isset(phpcom::$G['gp_uid']) ? intval(phpcom::$G['gp_uid']) : 0;
    if (!$uid) {
        admin_error('undefined_action');
    }
    $member = DB::fetch_first("SELECT m.uid, m.username, m.adminid, m.groupid, m.groupexpiry, m.groupextids, m.allowadmin, m.credits,
         s.groupterms, u.type AS grouptype, u.grouptitle, u.adminrid 
         FROM " . DB::table('members') . " m 
         LEFT JOIN " . DB::table('member_status') . " s ON s.uid=m.uid 
         LEFT JOIN " . DB::table('usergroup') . " u ON u.groupid=m.groupid
         WHERE m.uid='$uid'");
    if (!$member) {
        admin_message('members_edit_nonexistence');
    }
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        echo '<script src="misc/js/calendar.js" type="text/javascript"></script>';
        $member['groupterms'] = unserialize($member['groupterms']);
        $groupextarray = explode("\t", $member['groupextids']);
        $groupexpiry = $member['groupexpiry'];
        if ($groupexpiry) {
            $groupexpiry = fmdate($groupexpiry, 'Y-m-d');
        } else {
            $groupexpiry = '';
        }
        $adminhtml->tablesetmode = TRUE;
        $adminhtml->form('m=members', array(array('action', 'group'), array('uid', $uid)));
        $adminhtml->table_header('menu_members_group - ' . $member['username'], 2);
        $groupoptions = select_usergroup('groupnew', 11, FALSE, TRUE, 0, 0, TRUE, $member['credits']);
        $adminhtml->table_setting('members_usergroup_belong', 'groupidnew', $member['groupid'], 'select', '', $groupoptions);
        $adminhtml->table_setting('members_usergroup_expiry', 'groupexpirynew', $groupexpiry, 'text', 'showcalendar(this.id)', 'usergroup_expiry');
        $adminhtml->table_end();
        $adminhtml->table_header('members_usergroup_groupext', 2);
        $adminhtml->table_td(array(
            array('members_usergroup_groupext_name', FALSE, 'width="336"'),
            array('members_usergroup_groupext_expiry', FALSE)
                ), NULL, FALSE, ' tablerow', NULL, FALSE);
        $query = DB::query("SELECT groupid, type, grouptitle FROM " . DB::table('usergroup') . " WHERE type IN('system','special') ORDER BY groupid ASC");
        while ($group = DB::fetch_array($query)) {
            $s = in_array($group['groupid'], $groupextarray) && !empty($member['groupterms']['ext'][$group['groupid']]) ? fmdate($member['groupterms']['ext'][$group['groupid']], 'Y-n-j') : '';
            $adminhtml->table_td(array(
                array('<input' . (in_array($group['groupid'], $groupextarray) ? ' checked="checked"' : '') . ' class="checkbox" type="checkbox" name="groupextidsnew[]" value="' . $group['groupid'] . '"  id="groupextid_' . $group['groupid'] . '" /><label for="groupextid_' . $group['groupid'] . '"> ' . $group['grouptitle'] . '</label>', TRUE, 'width="336"', NULL, 'formrow'),
                array('<input type="text" class="input t20" size="10" name="extgroupexpirynew[' . $group['groupid'] . ']" value="' . $s . '" onclick="showcalendar(this)" />', TRUE, '')
            ));
        }
        $adminhtml->table_td(array(
            array('members_usergroup_groupext_comments', FALSE, 'colspan="2"', NULL, 'tips')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end();
        $adminhtml->table_header('members_operation_reason', 2);
        $adminhtml->table_setting('members_usergroup_reason', 'reason', '', 'textarea');
        $adminhtml->table_setting('submit', 'submit', '', 'submit');
        $adminhtml->table_end('</form>');
    } else {
        $groupidnew = isset(phpcom::$G['gp_groupidnew']) ? intval(phpcom::$G['gp_groupidnew']) : 0;
        $group = DB::fetch_first("SELECT groupid, adminrid, type FROM " . DB::table('usergroup') . " WHERE groupid='$groupidnew'");
        if (!$group) {
            admin_error('undefined_action');
        }
        if ($member['groupid'] != $groupidnew && checkisfounder($member)) {
            admin_message('members_edit_groups_isfounder');
        }
        if(isset(phpcom::$G['gp_groupextidsnew'])){
	        if (strlen(is_array(phpcom::$G['gp_groupextidsnew']) ? implode("\t", phpcom::$G['gp_groupextidsnew']) : '') > 30) {
	            admin_message('members_edit_groups_toomany');
	        }
        }
        if ($group['type'] == 'system' || $group['type'] == 'special' || in_array($group['groupid'], array(4, 5))) {
            $groupexpirynew = intval(strtotime(phpcom::$G['gp_groupexpirynew']));
        } else {
            $groupexpirynew = 0;
        }
        $adminidnew = in_array($groupidnew, array(1, 2, 3)) ? $groupidnew : 0;
        $allowadmin = $member['allowadmin'];
        if ($groupidnew === 1) {
            $allowadmin = 1;
        }
        $groupterms = $groupextidsarray = array();
        if (isset(phpcom::$G['gp_groupextidsnew']) && phpcom::$G['gp_groupextidsnew']) {
            foreach (phpcom::$G['gp_groupextidsnew'] as $groupextid) {
                if ($groupextid) {
                    $groupextidsarray[] = $groupextid;
                    $extgroupexpiry = trim(phpcom::$G['gp_extgroupexpirynew'][$groupextid]);
                    if ($extgroupexpiry = intval(strtotime($extgroupexpiry))) {
                        $groupterms['ext'][$groupextid] = $extgroupexpiry;
                    }
                }
            }
        }
        $groupextidsnew = '';
        if($groupextidsarray){
            $groupextidsnew = implode("\t", $groupextidsarray);
        }
        if ($groupexpirynew && ($group['type'] == 'system' || $group['type'] == 'special')) {
            $groupterms['ext'][$groupidnew] = $groupexpirynew;
        }
        if ($groupterms) {
            $grouptermsnew = addslashes(serialize($groupterms));
        } else {
            $grouptermsnew = '';
        }
        DB::query("UPDATE " . DB::table('member_status') . " SET groupterms='$grouptermsnew' WHERE uid='$uid'");
        DB::query("UPDATE " . DB::table('members') . " SET groupid='$groupidnew', adminid='$adminidnew', groupextids='$groupextidsnew', groupexpiry='$groupexpirynew', allowadmin='$allowadmin' WHERE uid='$uid'");
        if ($groupidnew !== $member['groupid'] && (in_array($groupidnew, array(4, 5)) || in_array($member['groupid'], array(4, 5)))) {
            memberbanlog($member['username'], $member['groupid'], $groupidnew, $groupexpirynew, phpcom::$G['gp_reason']);
        }
        admin_succeed('members_edit_groups_succeed', "m=members&action=group&uid=$uid", array('username' => $member['username'], 'uid' => $uid));
    }
} else if ($action == 'credit') {
    $adminhtml->navtabs($navarray, 'members_manage');
    $uid = isset(phpcom::$G['gp_uid']) ? intval(phpcom::$G['gp_uid']) : 0;
    $username = isset(phpcom::$G['gp_username']) ? trim(phpcom::$G['gp_username']) : '';
    if (empty($uid) && empty($username)) {
        admin_error('undefined_action');
    } else {
        $condition = !empty($uid) ? "m.uid='$uid'" : "m.username='$username'";
    }
    $member = DB::fetch_first("SELECT m.*, mc.*, u.grouptitle, u.type AS grouptype, u.mincredits, u.maxcredits
		FROM " . DB::table('members') . " m
		LEFT JOIN " . DB::table('member_count') . " mc ON m.uid=mc.uid
		LEFT JOIN " . DB::table('usergroup') . " u ON u.groupid=m.groupid
		WHERE $condition");
    if (!$member) {
        admin_message('members_edit_nonexistence');
    }
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        $adminhtml->form('m=members', array(array('action', 'credit'), array('uid', $uid)));
        $adminhtml->table_header("menu_members_credit - {$member['username']}({$member['grouptitle']})", 6);
        $adminhtml->table_td(array(array('members_credit_tips', FALSE, 'colspan="6"')), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_td(array(
            array('members_credit_limits', FALSE),
            array('credits', FALSE),
            array(phpcom::$setting['credits']['money']['title'], TRUE),
            array(phpcom::$setting['credits']['prestige']['title'], TRUE),
            array(phpcom::$setting['credits']['praise']['title'], TRUE),
            array(phpcom::$setting['credits']['currency']['title'], TRUE)
                ), '', FALSE, ' tablerow', NULL, FALSE);
        $adminhtml->table_td(array(
            array('members_credit_ranges', FALSE),
            array($member['credits'], TRUE),
            array('<input class="input t10" name="creditsnew[money]" type="text" value="' . intval($member['money']) . '" />', TRUE),
            array('<input class="input t10" name="creditsnew[prestige]" type="text" value="' . intval($member['prestige']) . '" />', TRUE),
            array('<input class="input t10" name="creditsnew[praise]" type="text" value="' . intval($member['praise']) . '" />', TRUE),
            array('<input class="input t10" name="creditsnew[currency]" type="text" value="' . intval($member['currency']) . '" />', TRUE),
        ));
        $adminhtml->table_td(array(
            array('members_credit_reason', FALSE, 'colspan="6"'),
                ), NULL, FALSE, ' tablerow', NULL, FALSE);
        $adminhtml->table_td(array(
            array('<textarea rows="6" name="reason" id="reason" cols="40"></textarea>', TRUE, 'colspan="2"'),
            array('members_credit_reason_comments', FALSE, 'colspan="4"', '', 'tips'),
        ));
        $adminhtml->table_td(array(
            array($adminhtml->submit_button(), TRUE, 'colspan="6"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        $diffdata = array();
        $creditsnew = phpcom::$G['gp_creditsnew'];
        if (is_array($creditsnew)) {
            foreach ($creditsnew as $key => $value) {
                if ($member[$key] != ($value = intval($value))) {
                    $diffdata[$key] = $value - $member[$key];
                }
            }
        }
        if ($diffdata) {
            foreach ($diffdata as $id => $diff) {
                $logarray = array(phpcom::$G['timestamp'], phpcom::$G['member']['username'], phpcom::$G['adminid'],
                    $member['username'], $id, $diff, 0, '', phpcom::$G['gp_reason']);
                $logs[] = htmlcharsencode(implode("\t", $logarray));
            }
            update_membercount($uid, $diffdata);
            writelog('ratelog', $logs);
        }
        admin_succeed('members_edit_credits_succeed', "m=members&action=credit&uid=$uid", array('username' => $member['username'], 'group' => $member['grouptitle']));
    }
} else if ($action == 'perms') {
    $adminhtml->navtabs($navarray, 'members_manage');
    $uid = isset(phpcom::$G['gp_uid']) ? intval(phpcom::$G['gp_uid']) : 0;
    if (!$uid) {
        admin_error('undefined_action');
    }
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        
    } else {
        
    }
} else if ($action == 'ban') {
    $adminhtml->navtabs($navarray, 'members_manage');
    $member = array('uid' => 0, 'username' => '');
    $uid = isset(phpcom::$G['gp_uid']) ? intval(phpcom::$G['gp_uid']) : 0;
    $username = isset(phpcom::$G['gp_username']) ? trim(phpcom::$G['gp_username']) : '';
    if (!empty($username) || !empty($uid)) {
        $member = DB::fetch_first("SELECT m.*, u.grouptitle, u.type AS grouptype, u.setting FROM " . DB::table('members') . " m
			LEFT JOIN " . DB::table('usergroup') . " u ON u.groupid=m.groupid
			WHERE " . ($uid ? "m.uid='$uid'" : "m.username='$username'"));
        if (!$member) {
            admin_message('members_edit_nonexistence');
        } elseif (($member['grouptype'] == 'system' && in_array($member['groupid'], array(1, 2, 3, 6, 7))) || $member['grouptype'] == 'special') {
            admin_message('members_edit_ban_illegal', '', array('grouptitle' => $member['grouptitle'], 'uid' => $member['uid']));
        }
    }
    $uid = isset($member['uid']) ? intval($member['uid']) : 0;
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        echo '<script src="misc/js/calendar.js" type="text/javascript"></script>';
        $groupexpiry = isset($member['groupexpiry']) ? $member['groupexpiry'] : 0;
        $banstatus = 0;
        if ($groupexpiry) {
            $groupexpiry = fmdate($groupexpiry, 'd');
        } else {
            $groupexpiry = '';
        }
        if (isset($member['groupid']) && isset($member['status'])) {
            if ($member['groupid'] == 4 || $member['groupid'] == 5) {
                $banstatus = $member['groupid'];
            }
            if ($member['status'] == -1) {
                $banstatus = -1;
            }
        }

        $adminhtml->tablesetmode = TRUE;
        $adminhtml->form('m=members', array(array('action', 'ban')));
        $adminhtml->table_header('menu_members_ban - ' . $member['username'], 2);
        $adminhtml->table_setting('members_ban_username', 'username', $member['username'], 'text');
        $adminhtml->table_setting('members_ban_type', 'banstatusnew', $banstatus, 'radios');
        $adminhtml->table_setting('members_ban_expiry', 'groupexpirynew', $groupexpiry, 'text', 'showcalendar(this.id)', 'usergroup_expiry');
        $adminhtml->table_setting('members_ban_reason', 'reason', '', 'textarea');
        $adminhtml->table_setting('submit', 'submit', '', 'submit');
        $adminhtml->table_end('</form>');
    } else {
        if (empty($member)) {
            admin_message('members_edit_nonexistence');
        }
        $data = array();
        $banstatusnew = isset(phpcom::$G['gp_banstatusnew']) ? intval(phpcom::$G['gp_banstatusnew']) : 0;
        $reason = isset(phpcom::$G['gp_reason']) ? phpcom::$G['gp_reason'] : '';
        $banexpirynew = intval(strtotime(phpcom::$G['gp_groupexpirynew']));
        if (in_array($banstatusnew, array(4, 5))) {
            $groupidnew = $banstatusnew;
            $adminidnew = -1;
            $data['groupexpiry'] = $banexpirynew;
        } elseif ($member['groupid'] == 4 || $member['groupid'] == 5) {
            $groupidnew = DB::result_first("SELECT groupid FROM " . DB::table('usergroup') . " WHERE type='member' AND mincredits<='$member[credits]' AND maxcredits>'$member[credits]'");
            $adminidnew = 0;
            if ($banexpirynew) {
                $data['groupexpiry'] = $banexpirynew;
            }
        } else {
            $groupidnew = $member['groupid'];
            $adminidnew = $member['adminid'];
        }
        $data['status'] = $banstatusnew == -1 ? -1 : 0;
        $data['adminid'] = $adminidnew;
        $data['groupid'] = $groupidnew;
        DB::update('members', $data, "uid='$uid'");
        if (DB::affected_rows()) {
            memberbanlog($member['username'], $member['groupid'], $groupidnew, $banexpirynew, $reason, $banstatusnew == -1 ? -1 : 0);
        }
        admin_succeed('members_edit_bans_succeed', "m=members&action=ban&uid=$uid", array('username' => $member['username'], 'uid' => $uid));
    }
} else {
    $adminhtml->navtabs($navarray, 'members_search');
    $adminhtml->form('m=members', array(array('action', 'search')), 'name="membersearchform" id="membersearchform"');
    $adminhtml->table_header('members_search', 3);
    $adminhtml->table_td(array(array('members_search_tips', FALSE, 'colspan="3"')), NULL, FALSE, NULL, NULL, FALSE);
    member_search_form($adminhtml, $action);
    $adminhtml->table_end('</form>');
}

admin_footer();

function member_search_form(&$adminhtml, $action = '') {
    $adminhtml->tablesetmode = FALSE;
    $adminhtml->table_setting('members_username', 'username', '', 'text');
    $adminhtml->table_setting('members_uid', 'uid', '', 'text');
    $adminhtml->table_td(array(
        array('members_usergroup', FALSE, 'align="right"'),
        array(select_usergroup('groupid[]'), TRUE),
        array('members_usergroup_comments', FALSE, '', '', 'tips'),
            ), NULL, FALSE, NULL, NULL, FALSE);
    $adminhtml->table_setting('submit', 'submit', '', 'submit');
    $action = NULL;
}

function member_count($condition, &$urladd) {
    $urladd = '';
    foreach ($condition as $key => $value) {
        if (in_array($key, array('formhash', 'submit', 'page', 'submit_button')) || $value === '') {
            continue;
        }
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                if ($v === '') {
                    continue;
                }
                $urladd .= '&' . $key . '[' . $k . ']=' . rawurlencode($v);
            }
        } else {
            $urladd .= '&' . $key . '=' . rawurlencode($value);
        }
    }
    return MemberSearch::getcount($condition);
}

function memberbanlog($username, $origgroupid, $newgroupid, $expiration, $reason, $status = 0) {
    $timestamp = phpcom::$G['timestamp'];
    $musername = phpcom::$G['member']['username'];
    $groupid = phpcom::$G['groupid'];
    $clientip = phpcom::$G['clientip'];
    writelog('banlog', htmlcharsencode("$timestamp\t$musername\t$groupid\t$clientip\t$username\t$origgroupid\t$newgroupid\t$expiration\t$reason\t$status"));
}

?>
