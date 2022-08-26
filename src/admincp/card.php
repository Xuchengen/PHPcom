<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : card.php    2012-2-15
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';
admin_header('menu_card', $action ? $admintitle : '');
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('global');
$navarray = array(
    array(
        'title' => 'menu_card_setting',
        'url' => '?m=card',
        'name' => 'card_setting'
    ),
    array(
        'title' => 'menu_card_manage',
        'url' => '?m=card&action=manage',
        'name' => 'card_manage'
    ),
    array(
        'title' => 'menu_card_type',
        'url' => '?m=card&action=type',
        'name' => 'card_type'
    ),
    array(
        'title' => 'menu_card_make',
        'url' => '?m=card&action=make',
        'name' => 'card_make'
    )
);
$adminhtml->navtabs($navarray, $action ? "card_$action" : 'card_setting');
if ($action == 'manage') {
    if (checksubmit(array('delsubmit', 'btnsubmit'))) {
        $delete = isset(phpcom::$G['gp_delete']) ? phpcom::$G['gp_delete'] : null;
        if ($delete) {
            $cardids = implodevalue($delete);
            DB::query("DELETE FROM " . DB::table('card') . " WHERE cardid IN ($cardids)");
            unset($delete);
        }
    }
    $userlist = $cardlist = $members = $card_type = $grouplist = $groups = array();
    $sqladd = generate_cardsql();
    foreach ($_GET as $key => $val) {
        if (strpos($key, 'sch_') !== FALSE && $val) {
            if (in_array($key, array('sch_username'))) {
                $val = rawurlencode($val);
            }
            $export_url[] = $key . '=' . $val;
        }
    }
    $nolimit_option = '<option value="">' . adminlang('nolimit') . '</option>';
    $pagesize = max(10, empty(phpcom::$G['gp_pagesize']) ? 20 : intval(phpcom::$G['gp_pagesize']));
    phpcom::$G['gp_page'] = isset(phpcom::$G['gp_page']) ? phpcom::$G['gp_page'] : 1;
    $page = max(1, phpcom::$G['gp_page']);
    echo '<script type="text/javascript" src="misc/js/calendar.js"></script>';
    $default_cardtypes = array('typeid' => 0, 'typename' => adminlang('card_type_default'));
    $query = DB::query("SELECT typeid, typename FROM " . DB::table('card_type') . " ORDER BY typeid ASC");
    $cardtype_option = '<select name="sch_typeid" class="select t20">' . $nolimit_option;
    while (($row = $default_cardtypes) || ($row = DB::fetch_array($query))) {
        $card_type[$row['typeid']] = $row;
        $cardtype_option .= "<option value=\"{$row['typeid']}\">{$row['typename']}</option>";
        $default_cardtypes = array();
    }
    $cardtype_option .= '</select>';
    $date = '';
    $adminhtml->form('', array(array('m', 'card'), array('action', 'manage')), 'name="searchform" method="get"');
    $adminhtml->table_header('card_manage');
    $adminhtml->table_td(array(array('card_manage_tips', array('date' => $date), 'colspan="4"')), NULL, FALSE, NULL, NULL, FALSE);
    $adminhtml->table_td(array(
        array('card_number', FALSE),
        array('<input type="text" name="sch_cardid" class="input t30" value="' . $_GET['sch_cardid'] . '" />', TRUE),
        array('card_price_between', FALSE),
        array('<input type="text" name="sch_pricemin" class="input t10" value="' . ($_GET['sch_pricemin'] ? $_GET['sch_pricemin'] : '') . '" /> - 
            <input type="text" name="sch_pricemax" class="input t10" value="' . ($_GET['sch_pricemax'] ? $_GET['sch_pricemax'] : '') . '" />', TRUE)
    ));
    $credits_option = '<select name="sch_credits" class="select t10">' . $nolimit_option;
    foreach (phpcom::$setting['credits'] as $key => $val) {
        $credits_option .= "<option value='$key' " . ($_GET['sch_credits'] == $key ? 'selected' : '') . ">{$val['title']}</option>";
    }
    $credits_option .= '</select>';
    $cardstatus_option = '<select name="sch_status" class="select t20">' . $nolimit_option;
    foreach (array('1' => adminlang('card_manage_status_1'), '2' => adminlang('card_manage_status_2'), '9' => adminlang('card_manage_status_9')) as $key => $val) {
        $cardstatus_option .= "<option value='{$key}' " . ($_GET['sch_status'] == $key ? "selected" : '') . ">{$val}</option>";
    }
    $cardstatus_option .= '</select>';
    $adminhtml->table_td(array(
        array('card_creditsval', FALSE),
        array('<input type="text" name="sch_creditsval" class="input t10" value="' . $_GET['sch_creditsval'] . '" /> &nbsp;' . $credits_option, TRUE),
        array('card_status', FALSE),
        array($cardstatus_option, TRUE)
    ));
    $_GET['sch_usedatee_start'] = isset($_GET['sch_usedatee_start']) ? $_GET['sch_usedatee_start'] : '';
    $adminhtml->table_td(array(
        array('card_used_user', FALSE),
        array('<input type="text" name="sch_username" class="input t30" value="' . $_GET['sch_username'] . '" />', TRUE),
        array('card_used_dateline', FALSE),
        array('<input type="text" name="sch_usedate_start" id="sch_usedate_start" class="input t10" value="' . $_GET['sch_usedatee_start'] . '" onclick="showcalendar(this.id);" /> - 
            <input type="text" name="sch_usedate_end" id="sch_usedate_end" class="input t10" value="' . $_GET['sch_usedate_end'] . '" onclick="showcalendar(this.id)" />', TRUE)
    ));
    $pagesize_selected = array(10 => '', 20 => '', 30 => '', 50 => '', 100 => '');
    $pagesize_selected[$pagesize] = " selected=selected";
    $pagesize_option = '<select name="pagesize" class="select t20" onchange="this.form.submit();">';
    foreach (array(10, 20, 30, 50, 100) as $num) {
        $pagesize_option .= "<option value=\"$num\"$pagesize_selected[$num]>" . adminlang('prepagesize_num', array('num' => $num)) . "</option>";
    }
    $pagesize_option .= '</select>';
    $adminhtml->table_td(array(
        array('card_type', FALSE),
        array($cardtype_option, TRUE),
        array('prepagesize', FALSE),
        array($pagesize_option, TRUE)
    ));
    $adminhtml->table_td(array(
        array($adminhtml->submit_button('search', 'schsubmit', 'button'), TRUE, 'colspan="4"')
            ), NULL, FALSE, NULL, NULL, FALSE);
    $adminhtml->table_end('</form>');
    $adminhtml->form('m=card&action=manage');
    $adminhtml->table_header('card_manage_list'); //
    $tdarray = array(
        array('emptychar', FALSE),
        array('card_number', FALSE),
        array('card_password', FALSE),
        array('card_price', FALSE),
        array('card_creditsval', FALSE),
        array('card_groupextid', FALSE),
        array('card_groupdays', FALSE),
        array('card_typeid', FALSE),
        array('card_status', FALSE),
        array('card_used_user', FALSE),
        array('card_used_dateline', FALSE),
        array('card_cleardate', FALSE),
        array('card_maker', FALSE)
    );
    if (empty(phpcom::$setting['card']['grouped'])) {
        unset($tdarray[5], $tdarray[6]);
    }
    $adminhtml->table_td($tdarray, '', FALSE, ' tablerow', NULL, FALSE);
    $start_limit = ($page - 1) * $pagesize;
    $export_url[] = 'start=' . $start_limit;
    $queryurl = "&pagesize=$pagesize";
    foreach ($_GET as $key => $val) {
        if (strpos($key, 'sch_') !== FALSE && $val !== '') {
            $queryurl .= '&' . $key . '=' . $val;
        }
    }

    $count = isset(phpcom::$G['gp_count']) ? intval(phpcom::$G['gp_count']) : 0;
    !$count && $count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('card') . " WHERE 1 $sqladd");
    if ($count) {
        $pagecount = @ceil($count / $pagesize);  //¼ÆËã×ÜÒ³Êý
        $pagenow = max(1, min($pagecount, intval($page)));
        $pagestart = floor(($pagenow - 1) * $pagesize);
        $sql = DB::buildlimit("SELECT * FROM " . DB::table('card') . " WHERE 1 $sqladd ORDER BY dateline DESC", $pagesize, $pagestart);
        $query = DB::query($sql);
        while ($row = DB::fetch_array($query)) {
            $userlist[$row['uid']] = $row['uid'];
            if ($row['groupextid']) {
                $grouplist[$row['groupextid']] = $row['groupextid'];
            }
            $cardlist[] = $row;
        }
        $members[0]['username'] = '--';
        if ($userlist) {
            $query = DB::query("SELECT uid, username FROM " . DB::table('members') . " WHERE uid IN (" . implodeids($userlist) . ")");
            while ($member = DB::fetch_array($query)) {
                $members[$member['uid']] = $member;
            }
            unset($userlist);
        }
        $groups[0]['grouptitle'] = adminlang('card_groupextid_default');
        if ($grouplist) {
            $query = DB::query("SELECT groupid, grouptitle FROM " . DB::table('usergroup') . " WHERE groupid IN (" . implodeids($grouplist) . ")");
            while ($group = DB::fetch_array($query)) {
                $groups[$group['groupid']] = $group;
            }
            unset($grouplist);
        }
        $todaytime = strtotime(fmdate(TIMESTAMP, 'Ymd'));
        foreach ($cardlist as $row) {
            if ($row['cleardate'] < $todaytime) {
                $cleardate = '<em class="c1 f10">' . fmdate($row['cleardate'], 'd') . '</em>';
            } else {
                $cleardate = '<em class="c3 f10">' . fmdate($row['cleardate'], 'd') . '</em>';
            }
            if ($row['groupdays'] && $row['groupextid']) {
                $groupdays = $row['groupdays'] . adminlang('card_groupdays_unit');
            } else {
                $groupdays = adminlang('nolimit');
            }
            $cardid = trim(chunk_split($row['cardid'], 4, ' '), " \0\t\r\n-");
            $cardid = $row['usedate'] ? "<span class=\"c1\">$cardid</span>" : $cardid;
            $tdarray = array(
                array('<input type="checkbox" class="checkbox" name="delete[]" value="' . $row['cardid'] . '" />', TRUE),
                array($cardid, TRUE),
                array($row['password'], TRUE),
                array('card_list_price', array('price' => $row['price'])),
                array($row['creditsval'] . phpcom::$setting['credits'][$row['creditskey']]['title'], TRUE),
                array($groups[$row['groupextid']]['grouptitle'], TRUE),
                array($groupdays, TRUE),
                array($card_type[$row['typeid']]['typename'], TRUE),
                array('card_manage_status_' . $row['status'], FALSE),
                array('<a target="_blank" href="member.php?action=home&uid=' . $row['uid'] . '">' . $members[$row['uid']]['username'] . '</a>', TRUE),
                array($row['usedate'] ? '<em class="f10">' . fmdate($row['usedate'], 'dt') . '</em>' : '--', TRUE),
                array($cleardate, TRUE),
                array('<span class="c2">' . $row['maker'] . '</span>', TRUE)
            );
            if (!phpcom::$setting['card']['grouped']) {
                unset($tdarray[5], $tdarray[6]);
            }
            $adminhtml->table_td($tdarray);
        }
    }
    $card_export_csv = adminlang('card_export_csv', array('queryurl' => implode('&', $export_url)));
    $adminhtml->table_td(array(
        array($adminhtml->checkall() . ' ' . $adminhtml->submit_button('delete', 'btnsubmit', 'button') . $card_export_csv, TRUE, 'colspan="13"')
            ), NULL, FALSE, NULL, NULL, FALSE);
    if ($count && $pagecount > 1) {
        $showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $count, ADMIN_SCRIPT . "?m=card&action=manage$queryurl") . '</var>';
        $adminhtml->table_td(array(
            array($showpage, TRUE, 'colspan="13" align="right" id="pagecode"')
                ), NULL, FALSE, NULL, NULL, FALSE);
    }
    $adminhtml->table_end('</form>');
} elseif ($action == 'make') {
    if (!checksubmit(array('submit', 'btnsubmit'), 1)) {
        $date = date('Y');
        echo '<script type="text/javascript" src="misc/js/calendar.js"></script>';
        $adminhtml->form('m=card&action=make');
        $adminhtml->table_header('card_make');
        $adminhtml->table_td(array(array('card_make_tips', array('date' => $date), 'colspan="3"')), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_setting('card_make_rule', 'rule', "$date############", 'text');
        $default_cardtypes = array('typeid' => 0, 'typename' => adminlang('card_type_default'));
        $query = DB::query("SELECT typeid, typename FROM " . DB::table('card_type') . " ORDER BY typeid ASC");
        $cardtype_option = '<select name="typeid" class="select t50">';
        while (($row = $default_cardtypes) || ($row = DB::fetch_array($query))) {
            $cardtype_option .= "<option value=\"{$row['typeid']}\">{$row['typename']}</option>";
            $default_cardtypes = array();
        }
        $cardtype_option .= '</select>';
        $adminhtml->table_setting('card_make_type', 'typeid', $cardtype_option, 'value');
        $adminhtml->table_setting('card_make_num', 'num', 50, 'text');
        $adminhtml->table_setting('card_make_price', 'price', 20, 'text');
        $tradecreditsfield = phpcom::$setting['creditstrans']['field'];
        $credits_option = '<select name="creditskey" class="select" style="width:120px;">';
        foreach (phpcom::$setting['credits'] as $key => $val) {
            $credits_option .= "<option value=\"$key\"" . ($tradecreditsfield == $key ? ' selected' : '') . ">{$val['title']}</option>";
        }
        $credits_option .= '</select> <input type="text" name="creditsval" value="200" class="input" style="width:80px;">';
        $adminhtml->table_setting('card_make_credits_type', 'type', $credits_option, 'value');
        $adminhtml->table_setting('card_make_cleardate', 'cleardate', date("Y-m-d", phpcom::$G['timestamp'] + 31536000), 'text', 'showcalendar(this.id)', 'card_cleardate');
        $query = DB::query("SELECT groupid, grouptitle FROM " . DB::table('usergroup') . " WHERE type='special' ORDER BY groupid ASC");
        $groupext_option = '<select name="groupextid" class="select t50">';
        $groupext_option .= '<option value="0" selected>' . adminlang('card_make_no_groupextid') . '</option>';
        while ($row = DB::fetch_array($query)) {
            $groupext_option .= "<option value=\"{$row['groupid']}\">{$row['grouptitle']}</option>";
        }
        $groupext_option .= '</select>';
        if (phpcom::$setting['card']['grouped']) {
            $adminhtml->table_setting('card_make_groupextid', 'groupextid', $groupext_option, 'value');
            $adminhtml->table_setting('card_make_groupdays', 'groupdays', '30', 'text');
        }
        $adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
        $adminhtml->table_end('</form>');
    } else {
        phpcom::$G['gp_rule'] = isset(phpcom::$G['gp_rule']) ? rawurldecode(trim(phpcom::$G['gp_rule'])) : null;
        phpcom::$G['gp_num'] = isset(phpcom::$G['gp_num']) ? intval(phpcom::$G['gp_num']) : 0;
        phpcom::$G['gp_succeed_num'] = isset(phpcom::$G['gp_succeed_num']) ? phpcom::$G['gp_succeed_num'] : 0;
        phpcom::$G['gp_fail_num'] = isset(phpcom::$G['gp_fail_num']) ? phpcom::$G['gp_fail_num'] : 0;
        list($y, $m, $d) = explode("-", phpcom::$G['gp_cleardate']);
        phpcom::$G['gp_step'] = empty(phpcom::$G['gp_step']) ? 1 : phpcom::$G['gp_step'];
        $cleardate = phpcom::$G['gp_cleardate'] && $y && $m ? mktime(23, 59, 59, $m, $d, $y) : 0;
        if ($cleardate < TIMESTAMP) {
            admin_message('card_make_cleardate_early');
        }
        if (empty(phpcom::$G['gp_rule'])) {
            admin_message('card_make_rule_empty', '', 'error');
        }
        if (phpcom::$G['gp_num'] < 1) {
            admin_message('card_make_num_error', '', 'error');
        }
        if (empty(phpcom::$G['gp_groupextid'])) {
            phpcom::$G['gp_groupdays'] = 0;
        }
        $card = new phpcom_card();
        $checkrule = $card->checkrule(phpcom::$G['gp_rule'], 1);

        if ($checkrule === -2) {
            admin_message('card_make_rule_error');
        }
        $nextstep = 0;
        $onepage_make = 500;
        if (phpcom::$G['gp_num'] > $onepage_make) {
            $step_num = ceil(phpcom::$G['gp_num'] / $onepage_make);
            if ($step_num > 1) {
                if (phpcom::$G['gp_step'] == $step_num) {
                    if (phpcom::$G['gp_num'] % $onepage_make == 0) {
                        $makenum = $onepage_make;
                    } else {
                        $makenum = phpcom::$G['gp_num'] % $onepage_make;
                    }
                } else {
                    $makenum = $onepage_make;
                    $nextstep = phpcom::$G['gp_step'] + 1;
                }
            }
        } else {
            $makenum = phpcom::$G['gp_num'];
        }
        $cardval = array(
            'typeid' => phpcom::$G['gp_typeid'],
            'price' => phpcom::$G['gp_price'],
            'creditskey' => phpcom::$G['gp_creditskey'],
            'creditsval' => phpcom::$G['gp_creditsval'],
            'cleardate' => $cleardate,
            'groupextid' => isset(phpcom::$G['gp_groupextid']) ? intval(phpcom::$G['gp_groupextid']) : 0,
            'groupdays' => isset(phpcom::$G['gp_groupdays']) ? intval(phpcom::$G['gp_groupdays']) : 0
        );
        $card->make(phpcom::$G['gp_rule'], $makenum, $cardval);
        phpcom::$G['gp_succeed_num'] += $card->succeed;
        phpcom::$G['gp_fail_num'] += $card->fail;
        if ($nextstep) {
            phpcom::$G['gp_rule'] = rawurlencode(phpcom::$G['gp_rule']);
            $nextlink = implodeurl(array(
                'm' => 'card', 'action' => 'make', 'submit' => 'yes',
                'rule' => phpcom::$G['gp_rule'], 'num' => phpcom::$G['gp_num'],
                'price' => phpcom::$G['gp_price'], 'typeid' => phpcom::$G['gp_typeid'],
                'creditskey' => phpcom::$G['gp_creditskey'], 'creditsval' => phpcom::$G['gp_creditsval'],
                'cleardate' => phpcom::$G['gp_cleardate'], 'groupextid' => intval(phpcom::$G['gp_groupextid']),
                'groupdays' => intval(phpcom::$G['gp_groupdays']), 'step' => $nextstep,
                'succeed_num' => phpcom::$G['gp_succeed_num'], 'fail_num' => phpcom::$G['gp_fail_num']
                    ));
            admin_message('card_make_step', $nextlink, array('step' => $nextstep - 1, 'step_num' => $step_num, 'succeed_num' => $card->succeed, 'fail_num' => $card->fail), 'loading');
        } else {
            if (ceil(phpcom::$G['gp_num'] * 0.6) > phpcom::$G['gp_succeed_num']) {
                admin_succeed('card_make_rate_succeed', 'm=card&action=manage', array('succeed_num' => phpcom::$G['gp_succeed_num'], 'fail_num' => phpcom::$G['gp_fail_num']));
            }
            admin_succeed('card_make_succeed', 'm=card&action=manage', array('succeed_num' => phpcom::$G['gp_succeed_num'], 'fail_num' => phpcom::$G['gp_fail_num']));
        }
    }
} elseif ($action == 'type') {
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        $adminhtml->form('m=card&action=type');
        $adminhtml->table_header('card_type');
        $adminhtml->table_td(array(array('card_type_tips', FALSE, 'colspan="3"')), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_td(array(
            array('deletecheckbox', FALSE),
            array('card_type_name', FALSE),
            array('card_type_summary', FALSE),
                ), NULL, FALSE, ' tablerow', NULL, FALSE);
        $default_cardtypes = array('typeid' => 0, 'typename' => adminlang('card_type_default'), 'summary' => adminlang('card_type_default_summary'));
        $query = DB::query("SELECT typeid, typename, summary FROM " . DB::table('card_type') . " ORDER BY typeid ASC");
        while (($row = $default_cardtypes) || ($row = DB::fetch_array($query))) {
            $typeid = $row['typeid'];
            $checkbox = 'name="delete[]" value="' . $typeid . '"';
            if (empty($typeid)) {
                $checkbox = 'name="disabled[]" disabled';
                $cardtypename = $row['typename'];
                $cardsummary = $row['summary'];
            } else {
                $cardtypename = $adminhtml->inputedit("cardtypename[$typeid]", $row['typename'], 15, 'left');
                $cardsummary = $adminhtml->inputedit("cardsummary[$typeid]", $row['summary'], 60, 'left');
            }
            $adminhtml->table_td(array(
                array('<input type="checkbox" class="checkbox" ' . $checkbox . ' />', TRUE),
                array($cardtypename, TRUE),
                array($cardsummary, TRUE)
            ));
            $default_cardtypes = array();
        }
        $adminhtml->table_td(array(
            array('newadd', FALSE, 'noWrap="noWrap"'),
            array('<input class="input t15" name="typenamenew" type="text" />', TRUE),
            array('<input class="input t60" name="summarynew" type="text" />', TRUE)
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_td(array(
            array('&nbsp;', TRUE),
            array($adminhtml->submit_button(), TRUE, 'colspan="2"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        $cardtypename = isset(phpcom::$G['gp_cardtypename']) ? phpcom::$G['gp_cardtypename'] : null;
        $delete = isset(phpcom::$G['gp_delete']) ? phpcom::$G['gp_delete'] : null;
        if ($delete) {
            $typeids = implodeids($delete);
            DB::query("DELETE FROM " . DB::table('card_type') . " WHERE typeid IN ($typeids)");
            DB::query("UPDATE " . DB::table('card') . " SET typeid='0' WHERE typeid IN ($typeids)");
            foreach ($delete as $value) {
                unset($cardtypename[$value]);
            }
            unset($delete);
        }
        $typenamenew = isset(phpcom::$G['gp_typenamenew']) ? htmlstrip(phpcom::$G['gp_typenamenew']) : null;
        if ($typenamenew) {
            $summarynew = htmlstrip(phpcom::$G['gp_summarynew']);
            DB::insert('card_type', array('typename' => $typenamenew, 'summary' => $summarynew));
        }
        if ($cardtypename) {
            $cardsummary = phpcom::$G['gp_cardsummary'];
            foreach ($cardtypename as $typeid => $value) {
                $typename = htmlstrip($value);
                $summary = htmlstrip($cardsummary[$typeid]);
                DB::update('card_type', array('typename' => $typename, 'summary' => $summary), "typeid='$typeid'");
            }
        }
        admin_succeed('card_type_update_succeed', 'm=card&action=type');
    }
} elseif ($action == 'export') {
    $sqladd = generate_cardsql();
    $_GET['start'] = isset(phpcom::$G['gp_start']) ? intval(phpcom::$G['gp_start']) : 0;
    $userlist = $grouplist = $members = $groups = array();
    $count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('card') . " WHERE 1 $sqladd");
    if ($count) {
        $query = DB::query("SELECT * FROM " . DB::table('card_type'));
        while ($result = DB::fetch_array($query)) {
            $cardtype[$result['id']] = $result;
        }
        $count = min(10000, $count);
        $query = DB::query("SELECT cardid, password, typeid, price, creditskey, creditsval, groupextid, groupdays, status, uid, usedate, cleardate, dateline, maker FROM " . DB::table('card') . " WHERE 1 $sqladd ORDER BY dateline DESC LIMIT " . ($_GET['start'] ? "{$_GET['start']}, " : '') . " $count");
        while ($result = DB::fetch_array($query)) {
            $userlist[$result['uid']] = $result['uid'];
            if ($result['groupextid']) {
                $grouplist[$result['groupextid']] = $result['groupextid'];
            }
            $result['creditsval'] = $result['creditsval'] . phpcom::$setting['credits'][$result['creditskey']]['title'];
            unset($result['creditskey']);
            $cardlist[] = $result;
        }
        if ($userlist) {
            $query = DB::query("SELECT uid, username, email FROM " . DB::table('members') . " WHERE uid IN (" . implodeids($userlist) . ")");
            while ($member = DB::fetch_array($query)) {
                $members[$member['uid']] = $member;
            }
            unset($userlist);
        }
        $groups[0]['grouptitle'] = adminlang('card_groupextid_default');
        if ($grouplist) {
            $query = DB::query("SELECT groupid, grouptitle FROM " . DB::table('usergroup') . " WHERE groupid IN (" . implodeids($grouplist) . ")");
            while ($group = DB::fetch_array($query)) {
                $groups[$group['groupid']] = $group;
            }
            unset($grouplist);
        }
        foreach ($cardlist as $key => $val) {
            foreach ($val as $skey => $sval) {
                $sval = preg_replace('/\s+/', ' ', $sval);
                if ($skey == 'cardid' && !$title['cardid']) {
                    $title['cardid'] = adminlang('card_number');
                }
                if ($skey == 'password' && !$title['password']) {
                    $title['password'] = adminlang('card_password');
                    $sval = strval($sval);
                }
                if ($skey == 'typeid') {
                    if (!$title['typeid']) {
                        $title['typeid'] = adminlang("card_type");
                    }
                    $sval = $sval != 0 ? $cardtype[$sval]['typename'] : adminlang('card_type_default');
                }
                if ($skey == 'maker' && !$title['maker']) {
                    $title['maker'] = adminlang("card_maker");
                }
                if ($skey == 'uid') {
                    if ($skey == 'uid' && !$title['uid']) {
                        $title['uid'] = adminlang("card_used_user");
                    }
                    $sval = $members[$sval]['username'];
                }
                if ($skey == 'price') {
                    if (!$title['price']) {
                        $title['price'] = adminlang('card_price');
                    }
                    $sval = $sval . adminlang("card_make_price_unit");
                }
                if ($skey == 'creditsval') {
                    if (!$title['creditsval']) {
                        $title['creditsval'] = adminlang('card_creditsval');
                    }
                }
                if ($skey == 'groupdays') {
                    if (!$title['groupdays']) {
                        $title['groupdays'] = adminlang("card_groupdays");
                    }
                    $sval = $sval ? $sval . adminlang("card_groupdays_unit") : adminlang('nolimit');
                }
                if ($skey == 'groupextid') {
                    if ($skey == 'groupextid' && !$title['groupextid']) {
                        $title['groupextid'] = adminlang("card_groupextid");
                    }
                    $sval = $groups[$sval]['grouptitle'];
                }
                if ($skey == 'status') {
                    if (!$title['status']) {
                        $title['status'] = adminlang('card_status');
                    }
                    $sval = adminlang("card_manage_status_" . $sval);
                }
                if (in_array($skey, array('dateline', 'cleardate', 'usedate'))) {
                    if ($skey == 'dateline' && !$title['dateline']) {
                        $title['dateline'] = adminlang('card_maketime');
                    }
                    if ($skey == 'cleardate' && !$title['cleardate']) {
                        $title['cleardate'] = adminlang('card_make_cleardate');
                    }
                    if ($skey == 'usedate' && !$title['usedate']) {
                        $title['usedate'] = adminlang('card_used_dateline');
                    }

                    $sval = $sval ? date("Y-m-d", $sval) : '';
                }
                if (!phpcom::$setting['card']['grouped'] && ($skey == 'groupextid' || $skey == 'groupdays')) {
                    if (isset($title['groupextid'])) unset($title['groupextid']);
                    if (isset($title['groupdays'])) unset($title['groupdays']);
                } else {
                    $detail .= strlen($sval) > 11 && is_numeric($sval) ? '[' . $sval . '],' : $sval . ',';
                }
            }
            $detail = $detail . "\n";
        }
    }
    $detail = implode(',', $title) . "\n" . $detail;
    $filename = 'card_' . date('Ymd', TIMESTAMP) . '.csv';

    ob_end_clean();
    header('Content-Encoding: none');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Pragma: no-cache');
    header('Expires: 0');
    if (phpcom::$G['charset'] != 'gbk') {
        $detail = phpcom::iconv($detail, phpcom::$G['charset'], 'GBK');
    }
    echo $detail;
    exit();
} else {
    $setting = array();
    if ($card = DB::fetch_first("SELECT * FROM " . DB::table('setting') . " WHERE skey='card'")) {
        $setting['card'] = unserialize($card['svalue']);
    }
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        $adminhtml->form('m=card');
        $adminhtml->table_header('card_setting');
        $adminhtml->table_td(array(array('card_tips', FALSE, 'colspan="3"')), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_setting('card_setting_enabled', 'cardsetting[enabled]', intval($setting['card']['enabled']), 'radio');
        $adminhtml->table_setting('card_setting_buy', 'cardsetting[buy]', intval($setting['card']['buy']), 'radio');
        $adminhtml->table_setting('card_setting_cipher', 'cardsetting[cipher]', intval($setting['card']['cipher']), 'radio');
        $adminhtml->table_setting('card_setting_grouped', 'cardsetting[grouped]', intval($setting['card']['grouped']), 'radio');
        $adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
        $adminhtml->table_end('</form>');
    } else {
        $cardsetting = isset(phpcom::$G['gp_cardsetting']) ? phpcom::$G['gp_cardsetting'] : null;
        if ($cardsetting) {
            $value = serialize($cardsetting);
            DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES ('card', '$value', 'array')");
            phpcom_cache::updater('setting');
        }
        admin_succeed('card_setting_succeed', 'm=card');
    }
}
admin_footer();

function truncate_card() {
    DB::query("TRUNCATE TABLE " . DB::table('card'));
}

function generate_cardsql() {
	$sqladd = '';
    $_GET['sch_cardid'] = isset(phpcom::$G['gp_sch_cardid']) ? trim(phpcom::$G['gp_sch_cardid']) : '';
    $_GET['sch_pricemax'] = isset(phpcom::$G['gp_sch_pricemax']) ? intval(phpcom::$G['gp_sch_pricemax']) : 0;
    $_GET['sch_pricemin'] = isset(phpcom::$G['gp_sch_pricemin']) ? intval(phpcom::$G['gp_sch_pricemin']) : 0;
    $_GET['sch_usedate'] = isset(phpcom::$G['gp_sch_usedate']) ? trim(phpcom::$G['gp_sch_usedate']) : '';
    $_GET['sch_username'] = isset(phpcom::$G['gp_sch_username']) ? trim(phpcom::$G['gp_sch_username']) : '';
    $_GET['sch_credits'] = isset(phpcom::$G['gp_sch_credits']) ? trim(phpcom::$G['gp_sch_credits']) : '';
    phpcom::$G['gp_sch_creditsval'] = isset(phpcom::$G['gp_sch_creditsval']) ? phpcom::$G['gp_sch_creditsval'] : 0;
    $_GET['sch_creditsval'] = intval(phpcom::$G['gp_sch_creditsval']) > 0 ? intval(phpcom::$G['gp_sch_creditsval']) : '';
    $_GET['sch_usedate_start'] = isset(phpcom::$G['gp_sch_usedate_start']) ? trim(phpcom::$G['gp_sch_usedate_start']) : '';
    $_GET['sch_usedate_end'] = isset(phpcom::$G['gp_sch_usedate_end']) ? trim(phpcom::$G['gp_sch_usedate_end']) : '';
    $_GET['sch_cardtype'] = isset(phpcom::$G['gp_sch_cardtype']) ? trim(phpcom::$G['gp_sch_cardtype']) : '';
    $_GET['sch_status'] = isset(phpcom::$G['gp_sch_status']) ? trim(phpcom::$G['gp_sch_status']) : '';
    if ($_GET['sch_cardid']) {
        $sqladd .= " AND cardid LIKE '%{$_GET['sch_cardid']}%' ";
    }
    if ($_GET['sch_cardtype'] != '') {
        $sqladd .= " AND typeid = '{$_GET['sch_cardtype']}'";
    }
    if ($_GET['sch_pricemin'] == 0 || $_GET['sch_pricemax'] == 0) {
        if ($_GET['sch_pricemax'] == 0 && $_GET['sch_pricemin']) {
            $sqladd .= " AND price = '{$_GET['sch_pricemin']}'";
        } elseif ($_GET['sch_pricemin'] == 0 && $_GET['sch_pricemax']) {
            $sqladd .= " AND price = '{$_GET['sch_pricemax']}'";
        }
    } elseif ($_GET['sch_pricemin'] && $_GET['sch_pricemax']) {
        $sqladd .= " AND price between '{$_GET['sch_pricemin']}' AND '{$_GET['sch_pricemax']}'";
    }
    if ($_GET['sch_credits']) {
        $sqladd .= " AND creditskey = '{$_GET['sch_credits']}'";
    }
    if ($_GET['sch_creditsval']) {
        $sqladd .= " AND creditsval = '{$_GET['sch_creditsval']}'";
    }
    if ($_GET['sch_username']) {
        $uid = DB::result_first("SELECT uid FROM " . DB::table('members') . " WHERE username='{$_GET['sch_username']}'");
        $sqladd .= " AND uid = '$uid'";
    }
    if ($_GET['sch_status'] !== '') {
        $sqladd .= " AND status = '{$_GET['sch_status']}'";
    }
    if ($_GET['sch_usedate_start'] || $_GET['sch_usedate_end']) {
        if ($_GET['sch_usedate_start']) {
            list($y, $m, $d) = explode("-", $_GET['sch_usedate_start']);
            $sqladd .= " AND usedate >= '" . mktime('0', '0', '0', $m, $d, $y) . "' ";
        }
        if ($_GET['sch_usedate_end']) {
            list($y, $m, $d) = explode("-", $_GET['sch_usedate_end']);
            $sqladd .= " AND usedate <= '" . mktime('23', '59', '59', $m, $d, $y) . "' AND usedate<>0 ";
        }
    }
    return $sqladd ? $sqladd : '';
}

?>
