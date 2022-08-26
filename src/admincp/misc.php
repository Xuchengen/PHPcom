<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : misc.php    2012-1-29
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';
admin_header($admintitle);
$adminhtml = phpcom_adminhtml::instance();
if ($action == 'custommenu') {
    $adminhtml->activetabs('index');
    phpcom::$G['gp_do'] = isset(phpcom::$G['gp_do']) ? phpcom::$G['gp_do'] : '';
    if (phpcom::$G['gp_do'] !== 'add' && !checksubmit(array('submit', 'btnsubmit'))) {
        $adminhtml->form('m=misc', array(array('action', 'custommenu')));
        $adminhtml->table_header('misc_custommenu', 4);
        $adminhtml->table_td(array(
            array('deletecheckbox', FALSE, 'width="5%" noWrap="noWrap"'),
            array('misc_custommenu_sortord', FALSE),
            array('misc_custommenu_title', FALSE),
            array('misc_custommenu_url', FALSE)
                ), '', FALSE, ' tablerow');
        $uid = phpcom::$G['uid'];
        $query = DB::query("SELECT id, sortord, title, url, category FROM " . DB::table('adminmenu') . " WHERE uid='$uid' ORDER BY sortord");
        while ($row = DB::fetch_array($query)) {
            $menuid = $row['id'];
            $adminhtml->table_td(array(
                array("<input type=\"checkbox\" class=\"checkbox\" name=\"delete[]\" value=\"$menuid\" />", FALSE),
                array('<input class="input t5" size="5" name="menusortord[' . $menuid . ']" type="text" value="' . htmlcharsencode($row['sortord']) . '"/>', TRUE),
                array('<input class="input t20" size="20" name="menutitle[' . $menuid . ']" type="text" value="' . htmlcharsencode($row['title']) . '"/>', TRUE),
                array('<input class="input t70" size="70" name="menuurl[' . $menuid . ']" type="text" value="' . htmlcharsencode($row['url']) . '"/>', TRUE)
            ));
        }
        $sortord = intval(DB::result_first("SELECT MAX(sortord) FROM " . DB::table('adminmenu') . " WHERE uid='$uid'")) + 1;
        $adminhtml->table_td(array(
            array('add', FALSE),
            array('<input class="input t5" size="5" name="sortordnew" type="text" value="' . $sortord . '"/>', TRUE),
            array('<input class="input t20" size="20" name="titlenew" type="text" />', TRUE),
            array('<input class="input t70" size="70" name="urlnew" type="text" />', TRUE)
        ));
        $adminhtml->table_td(array(
            array($adminhtml->submit_button(), TRUE, 'colspan="4"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } elseif (phpcom::$G['gp_do'] == 'add') {
        $uid = intval(phpcom::$G['uid']);
        $menus = array('uid' => $uid, 'sortord' => 0, 'category' => 1);
        $menus['title'] = striphtml(phpcom::$G['gp_title']);
        $menus['url'] = trim(phpcom::$G['gp_url']);
        if ($menus['title'] && $menus['url']) {
            $sortord = intval(DB::result_first("SELECT MAX(sortord) FROM " . DB::table('adminmenu') . " WHERE uid='$uid'")) + 1;
            $menus['sortord'] = $sortord;
            DB::insert('adminmenu', $menus);
            $extra = update_customizemenu('index');
            admin_succeed('misc_custommenu_add_succeed', 'm=misc&action=custommenu', NULL, $extra);
        } else {
            admin_message('misc_custommenu_add_failed');
        }
    } else {
        $menutitle = phpcom::$G['gp_menutitle'];
        $delete = phpcom::$G['gp_delete'];
        if (@$delete) {
            $menuids = implodeids($delete);
            DB::query("DELETE FROM " . DB::table('adminmenu') . " WHERE id IN ($menuids)");
            foreach ($delete as $value) {
                unset($menutitle[$value]);
            }
            unset($delete);
        }
        $titlenew = striphtml(phpcom::$G['gp_titlenew']);
        $urlnew = trim(phpcom::$G['gp_urlnew']);
        if ($titlenew && $urlnew) {
            $menus = array('uid' => phpcom::$G['uid'], 'category' => 1);
            $menus['sortord'] = intval(phpcom::$G['gp_sortordnew']);
            $menus['title'] = $titlenew;
            $menus['url'] = $urlnew;
            DB::insert('adminmenu', $menus);
        }
        if ($menutitle && is_array($menutitle)) {
            foreach ($menutitle as $id => $title) {
                DB::update('adminmenu', array(
                    'sortord' => intval(phpcom::$G['gp_menusortord'][$id]),
                    'title' => striphtml($title),
                    'url' => trim(phpcom::$G['gp_menuurl'][$id])
                        ), "id='$id'");
            }
        }
        $extra = update_customizemenu('index');
        admin_succeed('misc_custommenu_update_succeed', 'm=misc&action=custommenu', NULL, $extra);
    }
} else {
    
}

admin_footer();
?>
