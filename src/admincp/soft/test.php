<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : test.php    2012-2-9
 */
!defined('IN_ADMINCP') && exit('Access denied');
if (!checksubmit(array('btnsubmit', 'submit_button'))) {
    $adminhtml->form("m=soft&action=softtest&chanid=$chanid", null, 'name="softform"');
    $adminhtml->table_header('soft_setting_softtest', 7);
    $adminhtml->table_td(array(
        array('delete', FALSE, 'width="5%" noWrap="noWrap"'),
        array('soft_softtest_sortord', FALSE, 'width="5%"'),
        array('soft_softtest_caption', FALSE, 'width="20%"'),
        array('soft_softtest_url', FALSE, 'width="25%"'),
        array('soft_softtest_color', FALSE, 'width="22%"'),
        array('soft_softtest_icons', FALSE, 'width="15%" align="left"'),
        array('soft_softtest_checked', FALSE, 'width="8%" align="center"')
            ), '', FALSE, ' tablerow');
    
    $sortord = intval(DB::result_first("SELECT MAX(sortord) FROM " . DB::table('soft_test')));
    $query = DB::query("SELECT testid,caption,url,color,icons,checked,sortord FROM " . DB::table('soft_test') . " ORDER BY sortord");
    while ($row = DB::fetch_array($query)) {
        $testid = $row['testid'];
        $checkbox = "name=\"delete[$testid]\" value=\"$testid\"";
        if ($testid < 6) {
        	$checkbox = 'name="delete[0]" disabled';
        }
        $adminhtml->table_td(array(
            array('<input type="checkbox" class="checkbox" ' . $checkbox . ' />', TRUE, 'align="left"'),
            array($adminhtml->textinput("sortord[$testid]", intval($row['sortord']), 'sortord'), TRUE),
            array($adminhtml->textinput("caption[$testid]", $row['caption'], '15'), TRUE),
            array($adminhtml->textinput("url[$testid]", $row['url'], '20'), TRUE),
            array($adminhtml->textcolor("color[$testid]", $row['color'], '10', "captioncolor_$testid"), TRUE),
            array($adminhtml->textinput("icons[$testid]", $row['icons'], '10'), TRUE),
            array('<input type="checkbox" class="checkbox" name="checked[' . $testid . ']" value="1"' . ($row['checked'] ? ' checked' : '') . ' />', TRUE, 'align="center"')
        ));
    }
    $adminhtml->table_td(array(
        array('add', FALSE, 'noWrap="noWrap"'),
        array('<input class="input sortord" name="sortordnew" type="text" value="' . ($sortord + 1) . '" />', TRUE),
        array('<input class="input t15" name="captionnew" type="text" />', TRUE),
        array('<input class="input t20" name="urlnew" type="text" />', TRUE),
        array($adminhtml->textcolor("colornew", '', '10', "captioncolor"), TRUE),
        array('<input class="input t10" name="iconsnew" type="text" />', TRUE),
        array('<input type="checkbox" class="checkbox" name="checkednew" value="1" />', TRUE, 'align="center"'),
            ), NULL, FALSE, NULL, NULL, FALSE);
    $adminhtml->table_td(array(
        array($adminhtml->checkall('del', 'chkall', 'delete'), TRUE, 'noWrap="noWrap"'),
        array($adminhtml->submit_button(), TRUE, 'align="center" colspan="6"')
            ), NULL, FALSE, NULL, NULL, FALSE);
    $adminhtml->table_end('</form>');
} else {
    $captions = phpcom::$G['gp_caption'];
    $delete = isset(phpcom::$G['gp_delete']) ? phpcom::$G['gp_delete'] : null;
    if ($delete) {
        $testids = implodeids($delete);
        DB::query("DELETE FROM " . DB::table('soft_test') . " WHERE testid IN ($testids)");
        foreach ($delete as $value) {
            unset($captions[$value]);
        }
        unset($delete);
    }
    $softtestnew = array();
    $captionnew = phpcom::$G['gp_captionnew'];
    if ($captionnew) {
        $softtestnew['caption'] = $captionnew;
        $softtestnew['url'] = checkurlhttp(phpcom::$G['gp_urlnew']);
        $softtestnew['color'] = trim(phpcom::$G['gp_colornew']);
        $softtestnew['icons'] = trim(phpcom::$G['gp_iconsnew']);
        $softtestnew['checked'] = isset(phpcom::$G['gp_checkednew']) ? intval(phpcom::$G['gp_checkednew']) : 0;
        $softtestnew['sortord'] = intval(phpcom::$G['gp_sortordnew']);
        DB::insert('soft_test', $softtestnew);
        unset($softtestnew);
    }
    if ($captions && is_array($captions)) {
        $softtests = array();
        foreach ($captions as $testid => $caption) {
            if ($caption && $testid) {
                $softtests['caption'] = $caption;
                $softtests['url'] = checkurlhttp(phpcom::$G['gp_url'][$testid]);
                $softtests['color'] = trim(phpcom::$G['gp_color'][$testid]);
                $softtests['icons'] = trim(phpcom::$G['gp_icons'][$testid]);
                $softtests['checked'] = isset(phpcom::$G['gp_checked'][$testid]) ? intval(phpcom::$G['gp_checked'][$testid]) : 0;
                $softtests['sortord'] = intval(phpcom::$G['gp_sortord'][$testid]);
                DB::update('soft_test', $softtests, array('testid' => $testid));
                unset($softtests);
            }
        }
    }
    phpcom_cache::updater('softtest');
    admin_succeed('soft_test_update_succeed', "m=soft&action=softtest&chanid=$chanid");
}
?>
