<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : adminfav.php    2011-4-16 21:04:44
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';

if (!checksubmit('btnsubmit')) {
    $current = '';
    if ($action == 'add' || $action == 'edit') {
        $current = 'adminfav_' . $action;
    }
    admin_header('menu_adminfav', $current);
    $navarray = array(
        array(
            'title' => 'adminfav',
            'url' => '?m=adminfav',
            'name' => 'first',
            'onclick' => ''
        ),
        array(
            'title' => 'adminfav_add',
            'url' => '?action=add&m=adminfav',
            'name' => 'add',
            'onclick' => ''
        )
    );
    $adminhtml = phpcom_adminhtml::instance();
    $adminhtml->activetabs('tools');
    if ($action == 'add' || $action == 'edit') {
        $favid = isset(phpcom::$G['gp_favid']) ? intval(phpcom::$G['gp_favid']) : 0;
        $active = 'add';
        if ($action == 'edit') {
            $navarray[] = array(
                'title' => 'adminfav_edit',
                'url' => '?action=edit&m=adminfav&favid=' . $favid,
                'name' => 'edit',
                'onclick' => ''
            );
            $active = 'edit';
        }
        $adminhtml->navtabs($navarray, $active);
        adminfav_edit($adminhtml, $favid, $active);
    } elseif ($action == 'del') {
        $favid = intval(phpcom::$G['gp_favid']);
        delete_adminfav($favid);
    } else {
        $adminhtml->navtabs($navarray);
        adminfav_main($adminhtml, $page);
    }
    admin_footer();
} else {
    $uid = phpcom::$G['uid'];
    if ($action == 'del') {
        $favidlist = isset(phpcom::$G['gp_favidlist']) ? phpcom::$G['gp_favidlist'] : null;
        $condition = "favid in(" . implodeids($favidlist) . ") AND uid='$uid'";
        if ($favidlist) {
            DB::delete('adminfav', $condition);
        }
        admin_succeed('delete_succeed', 'm=adminfav');
    } else {
        $adminfav = isset(phpcom::$G['gp_adminfav']) ? phpcom::$G['gp_adminfav'] : '';
        $favid = (int) phpcom::$G['gp_favid'];
        if (!$adminfav['title']) {
            admin_message('adminfav_title');
        }
        if ($action == 'edit' && $favid) {
            DB::update('adminfav', $adminfav, array('uid' => $uid, 'favid' => $favid));
        } elseif ($action == 'add') {
            $adminfav['dateline'] = phpcom::$G['timestamp'];
            $adminfav['uid'] = $uid;
            DB::insert('adminfav', $adminfav);
        }
        admin_succeed('adminfav_succeed', 'm=adminfav');
    }
}

function delete_adminfav($favid) {
    if ($favid) {
        DB::delete('adminfav', array('uid' => phpcom::$G['uid'], 'favid' => $favid));
    }
    admin_succeed('delete_succeed', 'm=adminfav');
}

function adminfav_main($adminhtml, $page = 1) {
    $uid = phpcom::$G['uid'];
    $adminhtml->form('m=adminfav', array(array('action', 'del')), 'onkeydown="return formdown()"');
    $adminhtml->table_header();
    $adminhtml->table_th(array(
        array('title', 'width="30%"'),
        array('description', 'width="46%"'),
        array('operation', 'width="12%"'),
        array('date', 'width="12%"')
    ));
    $adminhtml->table_td(array(
        array(' ', TRUE, 'colspan="4" align="left" id="showpage"')
            ), NULL, FALSE, NULL, NULL, FALSE);
    // 获取总记录数
    $totalrec = DB::result_first("SELECT COUNT(*) FROM " . DB::table('adminfav') . " WHERE uid in('0','$uid')");
    $pagenow = $page;  // 当前页
    $pagesize = (int) phpcom::$config['admincp']['pagesize'];  // 每页大小
    $pagecount = @ceil($totalrec / $pagesize);  //计算总页数
    $pagenow > $pagecount && $pagenow = 1;
    $pagestart = floor(($pagenow - 1) * $pagesize);
    $sql = DB::buildlimit("SELECT * FROM " . DB::table('adminfav') . " WHERE uid in('0','$uid')", $pagesize, $pagestart);
    $query = DB::query($sql);
    while ($row = DB::fetch_array($query)) {
        $edit = $adminhtml->edit_word('edit', 'action=edit&m=adminfav&favid=' . $row['favid'], ' | ');
        $edit .= $adminhtml->del_word('delete', 'action=del&m=adminfav&favid=' . $row['favid']);
        $adminhtml->table_td(array(
            array('<input type="checkbox" class="checkbox" name="favidlist[]" value="' . $row['favid'] . '" /> <a class="mid" href="javascript:void(0)" onclick="window.open(\'' . $row['url'] . '\')">' . $row['title'] . '</a>&nbsp;', TRUE),
            array($row['description'], TRUE),
            array($edit, TRUE, 'align="center"'),
            array(date('Y-m-d', $row['dateline']), TRUE, 'align="center"')
        ));
    }
    $showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . '?m=announce') . '</var>';
    $adminhtml->table_td(array(
        array($adminhtml->checkall() . ' ' . $adminhtml->del_submit(), TRUE, 'colspan="4"')
    ));
    $adminhtml->table_td(array(
        array($showpage, TRUE, 'colspan="4" align="right" id="pagecode"')
            ), NULL, FALSE, NULL, NULL, FALSE);
    $adminhtml->table_end('</form>');
    $adminhtml->showpagescript();
}

function adminfav_edit($adminhtml, $favid = 0, $active = 'add') {
    $result = array('favid' => 0, 'uid' => 0, 'title' => '', 'description' => '', 'dateline' => 0);
    $result['shared'] = 0;
    $result['url'] = 'http://';
    $uid = phpcom::$G['uid'];
    if ($favid) {
        $result = DB::fetch_first("SELECT favid,uid,title,description,url,dateline,shared FROM " . DB::table('adminfav') . " WHERE uid='$uid' AND favid=$favid");
    }
    $adminhtml->form('m=adminfav', array(array('action', $active), array('favid', $favid)), 'onsubmit="return checkPost(this)"');
    $adminhtml->table_header('adminfav_' . $active, 2);
    $adminhtml->table_td(array(
        array('adminfav_title', '', 'width="20%"', '', TRUE),
        array('<input id="post_title" class="input" size="60" name="adminfav[title]" type="text" value="' . htmlcharsencode($result['title']) . '" />', TRUE, 'width="80%"')
    ));
    $adminhtml->textarea('adminfav_description', $result['description'], "adminfav[description]");
    $adminhtml->table_td(array(
        array('adminfav_url', '', '', '', TRUE),
        array('<input id="post_url" class="input" size="60" name="adminfav[url]" type="text" value="' . htmlcharsencode($result['url']) . '" />', TRUE)
    ));
    $sharedradio = $adminhtml->radio(array('no', 'yes'), 'adminfav[shared]', $result['shared']);
    $adminhtml->table_td(array(
        array('adminfav_shared', '', '', '', TRUE),
        array($sharedradio, TRUE)
    ));
    $btnsubmit = $adminhtml->submit_button();
    $adminhtml->table_td(array(
        array('', TRUE),
        array($btnsubmit, TRUE)
            ), NULL, FALSE, NULL, NULL, FALSE);
    $adminhtml->table_end('</form>');
}

?>
