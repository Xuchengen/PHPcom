<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : usergroup.php    2011-5-7 0:49:33
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'user';
@set_time_limit(1000);
admin_header($admintitle);
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('user');
if ($action == 'edit') {
    $groupid = intval(phpcom::$G['gp_groupid']);
    $groupsetting = array();
    if (!$group = DB::fetch_first("SELECT * FROM " . DB::table('usergroup') . " WHERE groupid=$groupid")) {
        admin_message('usergroup_action_error');
    }
    $groupsetting = unserialized($group['setting']);
    $navarray = array(
        array('title' => 'menu_usergroup', 'url' => '?m=usergroup', 'id' => 'groupindex', 'name' => 'index'),
        array('title' => 'usergroup_basic_setting', 'id' => 'basicsetting', 'name' => 'first', 'onclick' => 'toggle_anchor(this)'),
        array('title' => 'usergroup_post_setting', 'id' => 'postsetting', 'name' => 'post', 'onclick' => 'toggle_anchor(this)'),
        array('title' => 'usergroup_attach_setting', 'id' => 'attachsetting', 'name' => 'attach', 'onclick' => 'toggle_anchor(this)'),
        array('title' => 'usergroup_other_setting', 'id' => 'othersetting', 'name' => 'special', 'onclick' => 'toggle_anchor(this)')
    );
    $adminhtml->navtabs($navarray, 'first');
    if (!checksubmit(array('btnsubmit', 'submit'))) {
        $adminhtml->form('m=usergroup', array(array('action', 'edit'), array('groupid', $groupid)), 'name="adminform" id="adminform"');
        $adminhtml->table_header($group['grouptitle'] . adminlang('groupsetting'), 3);
        $adminhtml->table_setting('usergroup_setting_grouptitle', array('grouptitle', 'usertitle'), array($group['grouptitle'], $group['usertitle']), 'text2');
        $adminhtml->table_setting('usergroup_setting_color', 'color', $group['color'], 'textcolor');
        $adminhtml->table_end();
        if ($group['type'] == 'special') {
            $adminhtml->count = 0;
            $adminhtml->table_header('usergroup_setting_pay_title', 3);
            $adminhtml->table_setting('usergroup_setting_buyable', 'buyable', intval($group['buyable']), 'radio');
            $adminhtml->table_setting('usergroup_setting_price', 'price', intval($group['price']), 'text');
            $adminhtml->table_setting('usergroup_setting_mindays', 'mindays', intval($group['mindays']), 'text');
            $adminhtml->table_end();
        }
        $adminhtml->count = 0;
        $adminhtml->table_header('usergroup_basic_setting', 4, 'basicsetting', 'tableborder', FALSE);
        $adminhtml->table_setting('usergroup_setting_access', 'groupsetting[access]', intval($groupsetting['access']), 'radios', '', '', 'access');
        $adminhtml->table_setting('usergroup_setting_login', 'groupsetting[login]', intval($groupsetting['login']), 'radios', '', '', 'login');
        $adminhtml->table_setting('usergroup_setting_allowsearch', 'groupsetting[allowsearch]', intval($groupsetting['allowsearch']), 'radio', '', '', 'allowsearch');
        $adminhtml->table_setting('usergroup_setting_viewmember', 'groupsetting[viewmember]', intval($groupsetting['viewmember']), 'radio', '', '', 'viewmember');
        $adminhtml->table_setting('usergroup_setting_viewuserip', 'groupsetting[viewuserip]', intval($groupsetting['viewuserip']), 'radio', '', '', 'viewuserip');
        $adminhtml->table_setting('usergroup_setting_usersign', 'groupsetting[usersign]', intval($groupsetting['usersign']), 'radio', '', '', 'usersign');
        $adminhtml->table_setting('usergroup_setting_message', 'groupsetting[message]', intval($groupsetting['message']), 'radio', '', '', 'message');
        $adminhtml->table_setting('usergroup_setting_score', 'groupsetting[score]', intval($groupsetting['score']), 'radio', '', '', 'score');
        $adminhtml->table_setting('usergroup_setting_vote', 'groupsetting[vote]', intval($groupsetting['vote']), 'radio', '', '', 'vote');
        $adminhtml->table_setting('usergroup_setting_feedback', 'groupsetting[feedback]', intval($groupsetting['feedback']), 'radio', '', '', 'feedback');
        $adminhtml->table_setting('usergroup_setting_favorites', 'groupsetting[favorites]', intval($groupsetting['favorites']), 'radio', '', '', 'favorites');
        $adminhtml->table_setting('usergroup_setting_favmax', 'groupsetting[favmax]', intval($groupsetting['favmax']), 'text', '', '', 'favmax');
        $adminhtml->table_setting('usergroup_setting_friend', 'groupsetting[friend]', intval($groupsetting['friend']), 'radio', '', '', 'friend');
        $adminhtml->table_setting('usergroup_setting_friendmax', 'groupsetting[friendmax]', intval($groupsetting['friendmax']), 'text', '', '', 'friendmax');
        $adminhtml->table_setting('usergroup_setting_allowdown', 'groupsetting[allowdown]', intval($groupsetting['allowdown']), 'radio', '', '', 'allowdown');
        //$adminhtml->table_setting('usergroup_setting_downcredits', 'groupsetting[downcredits]', intval($groupsetting['downcredits']), 'text', '', '', 'downcredits');
        //$adminhtml->table_setting('usergroup_setting_downtool', 'groupsetting[downtool]', intval($groupsetting['downtool']), 'radio', '', '', 'downtool');
        $adminhtml->table_setting('usergroup_setting_captchastatus', 'groupsetting[captchastatus]', intval($groupsetting['captchastatus']), 'radio', '', '', 'captchastatus');
        $adminhtml->table_setting('usergroup_setting_questionstatus', 'groupsetting[questionstatus]', intval($groupsetting['questionstatus']), 'radio', '', '', 'questionstatus');
        $adminhtml->table_end();
        //发表设置
        $adminhtml->count = 0;
        $adminhtml->table_header('usergroup_post_setting', 4, 'postsetting', 'tableborder', TRUE);
        $adminhtml->table_setting('usergroup_setting_admin', 'groupsetting[admin]', intval($groupsetting['admin']), 'radio', '', '', 'admin');
        $adminhtml->table_setting('usergroup_setting_edit', 'groupsetting[edit]', intval($groupsetting['edit']), 'radio', '', '', 'edit');
        $adminhtml->table_setting('usergroup_setting_delete', 'groupsetting[delete]', intval($groupsetting['delete']), 'radio', '', '', 'delete');
        $adminhtml->table_setting('usergroup_setting_comment', 'groupsetting[comment]', intval($groupsetting['comment']), 'radio', '', '', 'comment');
        $adminhtml->table_setting('usergroup_setting_commentnoaudit', 'groupsetting[commentnoaudit]', intval($groupsetting['commentnoaudit']), 'radio', '', '', 'commentnoaudit');
        $adminhtml->table_setting('usergroup_setting_delcomment', 'groupsetting[delcomment]', intval($groupsetting['delcomment']), 'radio', '', '', 'delcomment');
        $adminhtml->table_setting('usergroup_setting_commentlen', 'groupsetting[commentlen]', intval($groupsetting['commentlen']), 'text', '', '', 'commentlen');
        $adminhtml->table_setting('usergroup_setting_questions', 'groupsetting[questions]', intval($groupsetting['questions']), 'radio', '', '', 'questions');
        $adminhtml->table_setting('usergroup_setting_answers', 'groupsetting[answers]', intval($groupsetting['answers']), 'radio', '', '', 'answers');
        $adminhtml->table_setting('usergroup_setting_article', 'groupsetting[article]', intval($groupsetting['article']), 'radio', '', '', 'article');
        $adminhtml->table_setting('usergroup_setting_softwore', 'groupsetting[softwore]', intval($groupsetting['softwore']), 'radio', '', '', 'softwore');
        $adminhtml->table_setting('usergroup_setting_video', 'groupsetting[video]', intval($groupsetting['video']), 'radio', '', '', 'video');
        $adminhtml->table_setting('usergroup_setting_photo', 'groupsetting[photo]', intval($groupsetting['photo']), 'radio', '', '', 'photo');
        $adminhtml->table_setting('usergroup_setting_postnoaudit', 'groupsetting[postnoaudit]', intval($groupsetting['postnoaudit']), 'radio', '', '', 'postnoaudit');
        $adminhtml->table_end();
        //附加设置 
        $adminhtml->table_header('usergroup_attach_setting', 4, 'attachsetting', 'tableborder', TRUE);
        $adminhtml->table_setting('usergroup_attach_allowdownattach', 'groupsetting[allowdownattach]', intval($groupsetting['allowdownattach']), 'radio', '', '', 'allowdown');
        $adminhtml->table_setting('usergroup_attach_allowupload', 'groupsetting[allowupload]', intval($groupsetting['allowupload']), 'radio', '', '', 'allowupload');
        //$adminhtml->table_setting('usergroup_attach_allowimage', 'groupsetting[allowimage]', intval($groupsetting['allowimage']), 'radio', '', '', 'allowimage');
        $adminhtml->table_setting('usergroup_attach_remoteimage', 'groupsetting[remoteimage]', intval($groupsetting['remoteimage']), 'radio', '', '', 'remoteimage');
        $adminhtml->table_setting('usergroup_attach_maxattachsize', 'groupsetting[maxattachsize]', round(intval($groupsetting['maxattachsize']) / 1024), 'text', '', '', 'maxattachsize');
        $adminhtml->table_setting('usergroup_attach_dayattachsize', 'groupsetting[dayattachsize]', round(intval($groupsetting['dayattachsize']) / 1024), 'text', '', '', 'dayattachsize');
        $adminhtml->table_setting('usergroup_attach_dayattachnum', 'groupsetting[dayattachnum]', intval($groupsetting['dayattachnum']), 'text', '', '', 'dayattachnum');
        $adminhtml->table_setting('usergroup_attach_attachext', 'groupsetting[attachext]', trim($groupsetting['attachext']), 'text', '', '', 'attachext');
        $adminhtml->table_end();
        //其他设置
        $adminhtml->count = 0;
        $adminhtml->table_header('usergroup_other_setting', 4, 'othersetting', 'tableborder', TRUE);
        $adminhtml->table_setting('usergroup_other_buyinvited', 'groupsetting[buyinvited]', intval($groupsetting['buyinvited']), 'radio', '', '', 'buyinvited');
        $adminhtml->table_setting('usergroup_other_sendinvited', 'groupsetting[sendinvited]', intval($groupsetting['sendinvited']), 'radio', '', '', 'sendinvited');
        $adminhtml->table_setting('usergroup_other_invitedmoney', 'groupsetting[invitedmoney]', intval($groupsetting['invitedmoney']), 'text', '', '', 'invitedmoney');
        $adminhtml->table_setting('usergroup_other_invitedcredits', 'groupsetting[invitedcredits]', intval($groupsetting['invitedcredits']), 'text', '', '', 'invitedcredits');
        $adminhtml->table_setting('usergroup_other_dayinvitedsum', 'groupsetting[dayinvitedsum]', intval($groupsetting['dayinvitedsum']), 'text', '', '', 'dayinvitedsum');
        $adminhtml->table_setting('usergroup_other_downnocredits', 'groupsetting[downnocredits]', intval($groupsetting['downnocredits']), 'radio', '', '', 'downnocredits');
        $adminhtml->table_setting('usergroup_other_readnocredits', 'groupsetting[readnocredits]', intval($groupsetting['readnocredits']), 'radio', '', '', 'readnocredits');
        $adminhtml->table_setting('usergroup_other_watchnoredits', 'groupsetting[watchnoredits]', intval($groupsetting['watchnoredits']), 'radio', '', '', 'watchnoredits');
        $adminhtml->table_setting('usergroup_other_browsenoredits', 'groupsetting[browsenoredits]', intval($groupsetting['browsenoredits']), 'radio', '', '', 'browsenoredits');
        $adminhtml->table_setting('usergroup_other_noadverts', 'groupsetting[noadverts]', intval($groupsetting['noadverts']), 'radio', '', '', 'noadverts');
        
        $adminhtml->table_end();
        //批量选择设置 
        $adminhtml->count = 0;
        $adminhtml->table_header('usergroup_select_multiple', 2);
        $adminhtml->table_td(array(
            array(group_select_multiple(), FALSE, 'width="50%"'),
            array('usergroup_select_multiple_comments', FALSE, 'width="50%"', '', 'tips')
        ));
        $btnsubmit = $adminhtml->submit_button();
        $adminhtml->table_td(array(
            array($btnsubmit, TRUE, 'align="center" colspan="2"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        $data = array();
        $groupid = phpcom::$G['gp_groupid'];
        $grouptitle = phpcom::$G['gp_grouptitle'];
        $usertitle = phpcom::$G['gp_usertitle'];
        $color = phpcom::$G['gp_color'];
        $groupsetting = phpcom::$G['gp_groupsetting'];
        if (intval($groupsetting['maxattachsize'])) {
            $groupsetting['maxattachsize'] = intval($groupsetting['maxattachsize']) * 1024;
        } else {
            $groupsetting['maxattachsize'] = 0;
        }
        if (intval($groupsetting['dayattachsize'])) {
            $groupsetting['dayattachsize'] = intval($groupsetting['dayattachsize']) * 1024;
        } else {
            $groupsetting['dayattachsize'] = 0;
        }
        if ($groupid == 1) {
            $groupsetting['access'] = 2;
        }
        $checkboxgroup = isset(phpcom::$G['gp_checkboxgroup']) ? phpcom::$G['gp_checkboxgroup'] : array();
        $targetgroup = isset(phpcom::$G['gp_targetgroup']) ? phpcom::$G['gp_targetgroup'] : array();
        $data['grouptitle'] = $grouptitle;
        $data['usertitle'] = $usertitle;
        $data['color'] = $color;
        $data['buyable'] = isset(phpcom::$G['gp_buyable']) ? intval(phpcom::$G['gp_buyable']) : 0;
        $data['price'] = isset(phpcom::$G['gp_price']) ? intval(phpcom::$G['gp_price']) : 0;
        $data['mindays'] = isset(phpcom::$G['gp_mindays']) ? intval(phpcom::$G['gp_mindays']) : 0;
        $data['setting'] = serialize($groupsetting);
        DB::update('usergroup', $data, 'groupid=' . $groupid);
        //phpcom_cache::updater('usergroup', $groupid);
        unset($data);
        if ($checkboxgroup) {
            $usergroup = array();
            $setting = array();
            foreach ($checkboxgroup as $key => $value) {
                $usergroup[$value] = $groupsetting[$value];
            }
            foreach ($targetgroup as $k => $id) {
                if ($usergroup && $id && $id != $groupid) {
                    $setting = get_group_setting($id);
                    $setting = array_merge($setting, $usergroup);
                    if ($id == 1) {
                        $setting['access'] = 2;
                    }
                    $data['setting'] = serialize($setting);
                    DB::update('usergroup', $data, 'groupid=' . $id);
                    //phpcom_cache::updater('usergroup', $id);
                    unset($data);
                    unset($setting);
                }
            }
        }
        phpcom_cache::updater('usergroup');
        admin_succeed('usergroup_edit_succeed', 'action=edit&m=usergroup&groupid=' . $groupid);
    }
} elseif ($action == 'admingroup') {
    if (!checksubmit(array('btnsubmit', 'submit'))) {
        $adminhtml->table_header('tips');
        $adminhtml->table_td(array(array('admingroup_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end();
        $adminhtml->count = 1;
        $adminhtml->form('m=usergroup&action=admingroup', null, 'name="systemform" id="systemform"');
        $adminhtml->table_header('admingroup', 5, 'systemgroup', 'tableborder', FALSE);
        $adminhtml->table_td(array(
            array('usergroup_grouptitle', FALSE, 'width="28%"'),
            array('usergroup_groupid', FALSE, 'width="10%"'),
            array('usergroup_stars', FALSE, 'width="22%"'),
            array('usergroup_color', FALSE, 'width="25%"'),
            array('usergroup_setedit', FALSE, 'width="15%" noWrap="noWrap"')
                ), '', FALSE, ' tablerow', NULL, FALSE);
        $sql = "SELECT * FROM " . DB::table('usergroup') . " WHERE type='system' ORDER BY groupid";
        $query = DB::query($sql);
        while ($row = DB::fetch_array($query)) {
            $edit = $adminhtml->edit_word('edit', 'action=edit&m=usergroup&groupid=' . $row['groupid'], '');
            $adminhtml->table_td(array(
                array('<input type="text" class="input" size="15" name="group_grouptitle[' . $row['groupid'] . ']" value="' . htmlcharsencode($row['grouptitle']) . '" />
					<input type="text" class="input" size="15" name="group_usertitle[' . $row['groupid'] . ']" value="' . htmlcharsencode($row['usertitle']) . '" />', TRUE, 'noWrap="noWrap"'),
                array('(groupid:' . $row['groupid'] . ')', TRUE, 'noWrap="noWrap"', '', 'gray'),
                array('<input type="text" class="input" size="5" name="group_stars[' . $row['groupid'] . ']" value="' . intval($row['stars']) . '" />', TRUE),
                array('<input type="text" class="input" size="15" name="group_color[' . $row['groupid'] . ']" value="' . htmlcharsencode($row['color']) . '" />', TRUE),
                array($edit, TRUE)
            ));
        }
        $btnsubmit = $adminhtml->submit_button();
        $adminhtml->table_td(array(
            array($btnsubmit, TRUE, 'align="center" colspan="5"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
        echo '<span id="groupindex" style="display: none"></span>';
    } else {
        if($group_grouptitle = phpcom::$G['gp_group_grouptitle']){
	        foreach ($group_grouptitle as $groupid => $value) {
	            DB::update('usergroup', array(
	                'grouptitle' => htmlstrip($value),
	                'usertitle' => htmlstrip(phpcom::$G['gp_group_usertitle'][$groupid]),
	                'stars' => intval(phpcom::$G['gp_group_stars'][$groupid]),
	                'color' => htmlstrip(phpcom::$G['gp_group_color'][$groupid]),
	                'mincredits' => isset(phpcom::$G['gp_group_mincredits'][$groupid]) ? intval(phpcom::$G['gp_group_mincredits'][$groupid]) : 0,
	                'maxcredits' => isset(phpcom::$G['gp_group_maxcredits'][$groupid]) ? intval(phpcom::$G['gp_group_maxcredits'][$groupid]) : 0,
	                    ), "groupid='$groupid'");
	            phpcom_cache::updater('usergroup', $groupid);
	        }
        }
        phpcom_cache::updater('usergroup');
        admin_succeed('usergroup_update_succeed', 'action=admingroup&m=usergroup');
    }
} else {
    $navarray = array(
        array('title' => 'menu_usergroup_member', 'id' => 'membergroup', 'name' => 'first', 'onclick' => 'toggle_anchor(this)'),
        array('title' => 'menu_usergroup_special', 'id' => 'specialgroup', 'name' => 'special', 'onclick' => 'toggle_anchor(this)'),
        array('title' => 'menu_usergroup_system', 'id' => 'systemgroup', 'name' => 'system', 'onclick' => 'toggle_anchor(this)')
    );
    $adminhtml->navtabs($navarray, 'first');
    if (!checksubmit(array('btnsubmit', 'submit'))) {
        $adminhtml->table_header('tips');
        $adminhtml->table_td(array(array('usergroup_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end();
        $membergroup = $specialgroup = $systemgroup = array();
        $sql = "SELECT * FROM " . DB::table('usergroup') . " WHERE 1 ORDER BY groupid";
        $query = DB::query($sql);
        while ($row = DB::fetch_array($query)) {
            if ($row['type'] == 'member') {
                $membergroup[] = $row;
            } elseif ($row['type'] == 'special') {
                $specialgroup[] = $row;
            } else {
                $systemgroup[] = $row;
            }
        }
        $btnsubmit = $adminhtml->submit_button();
        $adminhtml->count = 1;
        $adminhtml->form('m=usergroup&action=member', array(array('grouptypenew', 'member')), 'name="memberform" id="memberform"');
        $adminhtml->table_header('usergroup_member', 7, 'membergroup', 'tableborder', FALSE);
        $adminhtml->table_td(array(
            array('deletecheckbox', FALSE, 'width="5%" noWrap="noWrap"'),
            array('usergroup_grouptitle', FALSE, 'width="23%"'),
            array('usergroup_groupid', FALSE, 'width="10%"'),
            array('usergroup_credits', FALSE, 'width="22%"'),
            array('usergroup_stars', FALSE, 'width="10%"'),
            array('usergroup_color', FALSE, 'width="15%"'),
            array('usergroup_setedit', FALSE, 'width="15%" noWrap="noWrap"')
                ), '', FALSE, ' tablerow', NULL, FALSE);
        foreach ($membergroup as $row) {
            $edit = $adminhtml->edit_word('edit', 'action=edit&m=usergroup&groupid=' . $row['groupid'], '');
            $checkbox = 'name="delete[]" value="' . $row['groupid'] . '"';
            if ($row['groupid'] < 19) {
                $checkbox = 'name="delete[]" disabled';
            }
            $adminhtml->table_td(array(
                array('<input class="checkbox" type="checkbox" ' . $checkbox . ' />', TRUE),
                array('<input type="text" class="input" size="15" name="group_grouptitle[' . $row['groupid'] . ']" value="' . htmlcharsencode($row['grouptitle']) . '" />
					<input type="text" class="input" size="15" name="group_usertitle[' . $row['groupid'] . ']" value="' . htmlcharsencode($row['usertitle']) . '" />', TRUE, 'noWrap="noWrap"'),
                array('(groupid:' . $row['groupid'] . ')', TRUE, 'noWrap="noWrap"', '', 'gray'),
                array('<input type="text" class="input" size="10" name="group_mincredits[' . $row['groupid'] . ']" value="' . intval($row['mincredits']) . '" /> ~ 
					<input type="text" class="input" size="10" name="group_maxcredits[' . $row['groupid'] . ']" value="' . intval($row['maxcredits']) . '" />', TRUE, 'noWrap="noWrap"'),
                array('<input type="text" class="input" size="5" name="group_stars[' . $row['groupid'] . ']" value="' . intval($row['stars']) . '" />', TRUE),
                array('<input type="text" class="input" size="15" name="group_color[' . $row['groupid'] . ']" value="' . htmlcharsencode($row['color']) . '" />', TRUE),
                array($edit, TRUE)
            ));
        }
        echo '<script type="text/javascript">';
        echo 'var rowtypedata = [\'&nbsp;\',\'<input name="grouptitlenew[]" type="text" size="20" class="input t15">\',\'&nbsp;\',\'&nbsp;\',\'&nbsp;\',\'&nbsp;\',\'&nbsp;\'];';
        echo '</script>';
        $adminhtml->table_td(array(
            array('&nbsp;', TRUE),
            array('usergroup_addnew_usergroup', FALSE),
            array($btnsubmit, TRUE, 'align="center" colspan="5"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
        $adminhtml->count = 1;
        $adminhtml->form('m=usergroup', array(array('action', 'special'), array('grouptypenew', 'special')), 'name="specialform" id="specialform"');
        $adminhtml->table_header('usergroup_special', 6, 'specialgroup', 'tableborder', TRUE);
        $adminhtml->table_td(array(
            array('deletecheckbox', FALSE, 'width="5%" noWrap="noWrap"'),
            array('usergroup_grouptitle', FALSE, 'width="23%"'),
            array('usergroup_groupid', FALSE, 'width="10%"'),
            array('usergroup_stars', FALSE, 'width="22%"'),
            array('usergroup_color', FALSE, 'width="25%"'),
            array('usergroup_setedit', FALSE, 'width="15%" noWrap="noWrap"')
                ), '', FALSE, ' tablerow', NULL, FALSE);
        foreach ($specialgroup as $key => $row) {
            $edit = $adminhtml->edit_word('edit', 'action=edit&m=usergroup&groupid=' . $row['groupid'], '');
            $checkbox = 'name="delete[]" value="' . $row['groupid'] . '"';
            if ($row['groupid'] < 19) {
                $checkbox = 'name="delete[]" disabled';
            }
            $adminhtml->table_td(array(
                array('<input class="checkbox" type="checkbox" ' . $checkbox . ' />', TRUE),
                array('<input type="text" class="input" size="15" name="group_grouptitle[' . $row['groupid'] . ']" value="' . htmlcharsencode($row['grouptitle']) . '" />
					<input type="text" class="input" size="15" name="group_usertitle[' . $row['groupid'] . ']" value="' . htmlcharsencode($row['usertitle']) . '" />', TRUE, 'noWrap="noWrap"'),
                array('(groupid:' . $row['groupid'] . ')', TRUE, 'noWrap="noWrap"', '', 'gray'),
                array('<input type="text" class="input" size="5" name="group_stars[' . $row['groupid'] . ']" value="' . intval($row['stars']) . '" />', TRUE),
                array('<input type="text" class="input" size="15" name="group_color[' . $row['groupid'] . ']" value="' . htmlcharsencode($row['color']) . '" />', TRUE),
                array($edit, TRUE)
            ));
        }
        echo '<script type="text/javascript">';
        echo 'var rowtypedata = [\'&nbsp;\',\'<input name="grouptitlenew[]" type="text" size="20" class="input t15">\',\'&nbsp;\',\'&nbsp;\',\'&nbsp;\',\'&nbsp;\',\'&nbsp;\'];';
        echo '</script>';
        $adminhtml->table_td(array(
            array('&nbsp;', TRUE),
            array('usergroup_addnew_usergroup', FALSE),
            array($btnsubmit, TRUE, 'align="center" colspan="4"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');

        $adminhtml->count = 1;
        $adminhtml->form('m=usergroup', array(array('action', 'system')), 'name="systemform" id="systemform"');
        $adminhtml->table_header('usergroup_system', 5, 'systemgroup', 'tableborder', TRUE);
        $adminhtml->table_td(array(
            array('usergroup_grouptitle', FALSE, 'width="28%"'),
            array('usergroup_groupid', FALSE, 'width="10%"'),
            array('usergroup_stars', FALSE, 'width="22%"'),
            array('usergroup_color', FALSE, 'width="25%"'),
            array('usergroup_setedit', FALSE, 'width="15%" noWrap="noWrap"')
                ), '', FALSE, ' tablerow', NULL, FALSE);
        foreach ($systemgroup as $key => $row) {
            $edit = $adminhtml->edit_word('edit', 'action=edit&m=usergroup&groupid=' . $row['groupid'], '');
            $adminhtml->table_td(array(
                array('<input type="text" class="input" size="15" name="group_grouptitle[' . $row['groupid'] . ']" value="' . htmlcharsencode($row['grouptitle']) . '" />
					<input type="text" class="input" size="15" name="group_usertitle[' . $row['groupid'] . ']" value="' . htmlcharsencode($row['usertitle']) . '" />', TRUE, 'noWrap="noWrap"'),
                array('(groupid:' . $row['groupid'] . ')', TRUE, 'noWrap="noWrap"', '', 'gray'),
                array('<input type="text" class="input" size="5" name="group_stars[' . $row['groupid'] . ']" value="' . intval($row['stars']) . '" />', TRUE),
                array('<input type="text" class="input" size="15" name="group_color[' . $row['groupid'] . ']" value="' . htmlcharsencode($row['color']) . '" />', TRUE),
                array($edit, TRUE)
            ));
        }
        $adminhtml->table_td(array(
            array($btnsubmit, TRUE, 'align="center" colspan="5"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        $deleteids = array();
        $group_grouptitle = phpcom::$G['gp_group_grouptitle'];
        $delete = isset(phpcom::$G['gp_delete']) ? phpcom::$G['gp_delete'] : null;
        if ($delete) {
            foreach ($delete as $groupedid) {
                if ($groupedid > 18) {
                    $deleteids[] = $groupedid;
                    $groupcache[] = 'usergroup_' . $groupedid;
                }
            }
            if ($deleteids) {
                $groupids = implodeids($deleteids);
                DB::delete('usergroup', "groupid in($groupids)");
                deletecache($groupcache, TRUE);
            }
            foreach ($deleteids as $value) {
                unset($group_grouptitle[$value]);
            }
            unset($delete, $deleteids);
        }
        foreach ($group_grouptitle as $groupid => $value) {
            DB::update('usergroup', array(
                'grouptitle' => htmlstrip($value),
                'usertitle' => htmlstrip(phpcom::$G['gp_group_usertitle'][$groupid]),
                'stars' => intval(phpcom::$G['gp_group_stars'][$groupid]),
                'color' => htmlstrip(phpcom::$G['gp_group_color'][$groupid]),
                'mincredits' => isset(phpcom::$G['gp_group_mincredits'][$groupid]) ? intval(phpcom::$G['gp_group_mincredits'][$groupid]) : 0,
                'maxcredits' => isset(phpcom::$G['gp_group_maxcredits'][$groupid]) ? intval(phpcom::$G['gp_group_maxcredits'][$groupid]) : 0,
                    ), "groupid='$groupid'");
        }
        if(isset(phpcom::$G['gp_grouptitlenew']) && !empty(phpcom::$G['gp_grouptitlenew'])){
	        $grouptitlenew = phpcom::$G['gp_grouptitlenew'];
	        $grouptypenew = htmlstrip(phpcom::$G['gp_grouptypenew']);
	        if ($grouptitlenew && in_array($grouptypenew, array('special', 'member'))) {
	            if ($data = DB::fetch_first("SELECT * FROM " . DB::table('usergroup') . " WHERE type='$grouptypenew' LIMIT 1")) {
	                unset($data['groupid']);
	                foreach ($grouptitlenew as $value) {
	                    if ($value) {
	                        $data['grouptitle'] = htmlstrip($value);
	                        $data['usertitle'] = htmlstrip($value);
	                        DB::insert('usergroup', $data);
	                    }
	                }
	            }
	        }
        }
        phpcom_cache::updater('usergroup');
        admin_succeed('usergroup_update_succeed', 'action=member&m=usergroup');
    }
}
admin_footer();

function get_group_setting($groupid) {
    $groupsetting = array();
    if ($groupid) {
        $row = DB::fetch_first("SELECT groupid,setting FROM " . DB::table('usergroup') . " WHERE groupid=$groupid");
        $groupsetting = unserialized($row['setting']);
    }
    return $groupsetting;
}

function group_select_multiple() {
    $membergroup = $specialgroup = $systemgroup = array();
    $sql = "SELECT groupid,type,grouptitle FROM " . DB::table('usergroup') . " WHERE 1 ORDER BY groupid";
    $query = DB::query($sql);
    while ($row = DB::fetch_array($query)) {
        if ($row['type'] == 'member') {
            $membergroup[] = $row;
        } elseif ($row['type'] == 'special') {
            $specialgroup[] = $row;
        } else {
            $systemgroup[] = $row;
        }
    }
    unset($row);
    $s = '<select name="targetgroup[]" size="12" multiple="multiple" style="width:220px">';
    $s .= '<optgroup label="' . adminlang('usergroup_system') . '">';
    foreach ($systemgroup as $key => $row) {
        $s .= '<option value="' . $row['groupid'] . '">' . $row['grouptitle'] . '</option>';
    }
    $s .= "</optgroup>\r\n";
    $s .= '<optgroup label="' . adminlang('usergroup_special') . '">';
    foreach ($specialgroup as $key => $row) {
        $s .= '<option value="' . $row['groupid'] . '">' . $row['grouptitle'] . '</option>';
    }
    $s .= "</optgroup>\r\n";
    $s .= '<optgroup label="' . adminlang('usergroup_member') . '">';
    foreach ($membergroup as $key => $row) {
        $s .= '<option value="' . $row['groupid'] . '">' . $row['grouptitle'] . '</option>';
    }
    $s .= "</optgroup>\r\n";
    $s .= "</select>\r\n";
    return $s;
}

?>
