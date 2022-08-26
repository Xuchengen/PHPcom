<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : validate.php    2012-2-5
 */
!defined('IN_ADMINCP') && exit('Access denied');
if (!checksubmit(array('submit', 'btnsubmit'))) {
    $remarkvalue1 = adminlang('members_validate_remark_1');
    $remarkvalue2 = adminlang('members_validate_remark_2');
    echo <<<EOT
<script type="text/JavaScript">
    function setvalidatebg(act, uid) {
        if(act == 'validate') {
            $('validate_uid_' + uid).className = "bg1";
            $('adminremark[' + uid + ']').value = '$remarkvalue1';
        }else if(act == 'invalidate') {
            $('validate_uid_' + uid).className = "bg2";
            $('adminremark[' + uid + ']').value = '$remarkvalue2';
        }else if(act == 'delete'){
            $('validate_uid_' + uid).className = "bg3";
            $('adminremark[' + uid + ']').value = '';
        }
    }
    function setvalidateall(act) {
        var trs = $('form_validate').getElementsByTagName('TR');
        for(var i in trs){
            if(trs[i].id && trs[i].id.substr(0, 13) == 'validate_uid_') {
                uid = trs[i].id.substr(13);
                if(act == 'validate') {
                    $('validate_uid_' + uid).className = "bg1";
                    $('adminremark[' + uid + ']').value = '$remarkvalue1';
                }else if(act == 'invalidate') {
                    $('validate_uid_' + uid).className = "bg2";
                    $('adminremark[' + uid + ']').value = '$remarkvalue2';
                }else if(act == 'delete'){
                    $('validate_uid_' + uid).className = "bg3";
                    $('adminremark[' + uid + ']').value = '';
                }else if(act == 'cancel'){
                    $('validate_uid_' + uid).className = "";
                    $('adminremark[' + uid + ']').value = '';
                }
            }
        }
    }
    function checkall_cancel(){
        var form = $('form_validate');
        var checkall = 'chkall';
        for(var i = 0; i < form.elements.length; i++) {
            var e = form.elements[i];
            if(e.type == 'radio') e.checked = '';
        }
    }
</script>
EOT;
    $adminhtml->form('m=members', array(array('action', 'audit')), 'name="form_validate" id="form_validate"');
    $adminhtml->table_header("menu_members_audit", 5);
    $adminhtml->table_td(array(array('members_validate_tips', FALSE, 'colspan="5"')), NULL, FALSE, NULL, NULL, FALSE);
    $adminhtml->table_td(array(
        array('members_validate_operation', FALSE),
        array('members_validate_userdata', FALSE),
        array('members_validate_regmessage', FALSE),
        array('members_validate_auditinfo', FALSE),
        array('members_validate_adminremarks', FALSE)
            ), '', FALSE, ' tablerow');
    $validatenum = DB::result_first("SELECT COUNT(*) FROM " . DB::table('member_validate') . " WHERE status='0'");
    $pagesize = intval(phpcom::$config['admincp']['pagesize']);
    $pagecount = @ceil($validatenum / $pagesize);  //¼ÆËã×ÜÒ³Êý
    $pagenow = max(1, min($pagecount, intval($page)));
    $pagestart = floor(($pagenow - 1) * $pagesize);
    $sql = DB::buildlimit("SELECT m.uid, m.username, m.groupid, m.email, m.regdate, s.regip, v.submitdate, v.auditdate, v.auditor, v.submitnum, v.message, v.remarks, v.uid as vuid 
        FROM " . DB::table('member_validate') . " v 
        LEFT JOIN " . DB::table('members') . " m ON v.uid=m.uid 
        LEFT JOIN " . DB::table('member_status') . " s ON m.uid=s.uid
        WHERE v.status='0' ORDER BY v.submitdate DESC", $pagesize, $pagestart);
    $query = DB::query($sql);
    $vuids = array();
    while ($member = DB::fetch_array($query)) {
        if ($member['groupid'] != 7) {
            $vuids[] = $member['vuid'];
            continue;
        }
        if ($member['auditdate']) {
            $auditdate = fmdate($member['auditdate']);
        } else {
            $auditdate = '';
        }
        $adminhtml->table_td(array(
            array('members_validate_radios', array('uid' => $member['uid'])),
            array('members_validate_userinfo', array('uid' => $member['uid'], 'username' => $member['username'],
                    'regdate' => fmdate($member['regdate']), 'regip' => $member['regip'], 'email' => $member['email'])),
            array('members_validate_message_textarea', array('uid' => $member['uid'], 'message' => htmlcharsencode($member['message']))),
            array('members_validate_useraudit', array('auditor' => $member['auditor'], 'submitnum' => $member['submitnum'],
                    'submitdate' => fmdate($member['submitdate']),
                    'auditdate' => $auditdate)),
            array('members_validate_remarks_textarea', array('uid' => $member['uid'], 'remarks' => htmlcharsencode($member['remarks'])))
                ), "validate_uid_$member[uid]", FALSE, '', '', FALSE);
    }
    if ($vuids) {
        $vuidstr = implodeids($vuids);
        DB::query("DELETE FROM " . DB::table('member_validate') . " WHERE uid IN ($vuidstr)", 'UNBUFFERED');
    }
    $adminhtml->table_td(array(
        array('members_validate_option', FALSE, 'colspan="2"'),
        array($adminhtml->submit_button(), TRUE),
        array('members_validate_sendemail', FALSE, 'colspan="2"'),
            ), NULL, FALSE, NULL, NULL, FALSE);
    if ($pagecount > 1) {
        $showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $validatenum, ADMIN_SCRIPT . "?m=members&action=audit") . '</var>';
        $adminhtml->table_td(array(
            array($showpage, TRUE, 'colspan="5" align="right" id="pagecode"')
                ), NULL, FALSE, NULL, NULL, FALSE);
    }
    $adminhtml->table_end('</form>');
} else {
    $validation = array('invalidate' => array(), 'validate' => array(), 'delete' => array());
    $uids = 0;
    $sqluid = '';
    $validatetype = phpcom::$G['gp_validatetype'];
    if (is_array($validatetype)) {
        foreach ($validatetype as $uid => $act) {
            $uid = intval($uid);
            $uids .= ',' . $uid;
            $validation[$act][] = $uid;
        }
        $sqluid = "v.uid IN ($uids) AND";
    }
    $members = array();
    $uidarray = array(0);
    $query = DB::query("SELECT v.*, m.uid, m.username, m.email, m.regdate 
    		FROM " . DB::table('member_validate') . " v, " . DB::table('members') . " m
			WHERE $sqluid m.uid=v.uid AND m.groupid='7'");
    while ($member = DB::fetch_array($query)) {
        $members[$member['uid']] = $member;
        $uidarray[] = $member['uid'];
    }
    if (is_array($uidarray) && !empty($uidarray)) {
        $uids = implode(',', $uidarray);
        $numdeleted = $numinvalidated = $numvalidated = 0;
        if (!empty($validation['delete']) && is_array($validation['delete'])) {
            include_once loadlibfile('delete');
            $numdeleted = delete_member($validation['delete']);
        } else {
            $validation['delete'] = array();
        }
        if (!empty($validation['validate']) && is_array($validation['validate'])) {
            $validateuids = implodeids($validation['validate']);
            $newgroupid = DB::result_first("SELECT groupid FROM " . DB::table('usergroup') . " WHERE mincredits<=0 AND 0<maxcredits LIMIT 1");
            DB::query("UPDATE " . DB::table('members') . " SET adminid='0', groupid='$newgroupid' WHERE uid IN ($validateuids) AND uid IN ($uids)");
            $numvalidated = DB::affected_rows();
            DB::query("DELETE FROM " . DB::table('member_validate') . " WHERE uid IN ($validateuids) AND uid IN ($uids)");
        } else {
            $validation['validate'] = array();
        }
        $adminremark = phpcom::$G['gp_adminremark'];
        if (!empty($validation['invalidate']) && is_array($validation['invalidate'])) {
            foreach ($validation['invalidate'] as $uid) {
                $numinvalidated++;
                DB::update('member_validate', array('auditdate' => phpcom::$G['timestamp'],
                    'auditor' => phpcom::$G['username'], 'status' => 1,
                    'remarks' => htmlcharsencode($adminremark[$uid])), "uid='$uid' AND uid IN ($uids)");
            }
        } else {
            $validation['invalidate'] = array();
        }
        foreach (array('validate', 'invalidate') as $o) {
            foreach ($validation[$o] as $uid) {
                if ($adminremark[$uid]) {
                    switch ($o) {
                        case 'validate':
                            addnotification($uid, 'audit_member', 'member_audit_validate', array('remark' => $adminremark[$uid]));
                            break;
                        case 'invalidate':
                            addnotification($uid, 'audit_member', 'member_audit_invalidate', array('remark' => $adminremark[$uid]));
                            break;
                    }
                } else {
                    switch ($o) {
                        case 'validate':
                            addnotification($uid, 'audit_member', 'member_audit_validate_no_remark');
                            break;
                        case 'invalidate':
                            addnotification($uid, 'audit_member', 'member_audit_invalidate_no_remark');
                            break;
                    }
                }
            }
        }
        if (phpcom::$G['gp_sendemail']) {
            if (!function_exists('sendmail')) {
                include loadlibfile('mail');
            }
            $adminremark = phpcom::$G['gp_adminremark'];
            foreach (array('delete', 'validate', 'invalidate') as $o) {
                foreach ($validation[$o] as $uid) {
                    if (isset($members[$uid])) {
                        $member = $members[$uid];
                        $member['regdate'] = fmdate($member['regdate']);
                        $member['submitdate'] = fmdate($member['submitdate']);
                        $member['auditdate'] = fmdate(TIMESTAMP);
                        $member['operation'] = $o;
                        $member['remarks'] = $adminremark[$uid] ? htmlcharsencode($adminremark[$uid]) : adminlang('none');
                        $validation_member_message = lang('email', 'validation_member_message', array(
                            'username' => $member['username'],
                            'webname' => phpcom::$setting['webname'],
                            'regdate' => $member['regdate'],
                            'submitdate' => $member['submitdate'],
                            'submitnum' => $member['submitnum'],
                            'message' => $member['message'],
                            'auditresult' => lang('email', 'audit_member_' . $member['operation']),
                            'auditdate' => $member['auditdate'],
                            'auditor' => phpcom::$G['member']['username'],
                            'remark' => $member['remarks'],
                            'siteurl' => phpcom::$G['siteurl'],
                                ));
                        sendmail("$member[username] <$member[email]>", lang('email', 'validation_member_subject'), $validation_member_message);
                    }
                }
            }
        }
    }
    admin_succeed('members_validate_succeed', 'm=members&action=audit', array('numvalidated' => $numvalidated, 'numinvalidated' => $numinvalidated, 'numdeleted' => $numdeleted));
}
?>
