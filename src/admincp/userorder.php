<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : userorder.php    2012-1-5
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'user';
admin_header($admintitle);
$navarray = array(
    array(
        'title' => 'menu_payonline',
        'url' => '?m=payonline',
        'id' => 'payonline',
        'name' => 'payonline'
    ),
    array(
        'title' => 'menu_userorder',
        'url' => '?m=userorder',
        'id' => 'userorder',
        'name' => 'userorder'
    )
);
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('user');
$active = $action == 'tradeorder' || $action == 'paytrade' ? 'tradeorder' : 'userorder';
$adminhtml->navtabs($navarray, $active);
if ($action == 'tradeorder') {
    if (!checksubmit(array('submit', 'btnsubmit'), 1)) {
        $adminhtml->form('m=userorder', array(array('action', 'tradeorder')));
        $adminhtml->table_header('tradeorder_search', 9);
        $adminhtml->table_td(array(
            array('deletecheckbox', FALSE, 'width="5%" noWrap="noWrap"'),
            array('userorder_orderid', FALSE),
            array('userorder_type', FALSE),
            array('userorder_buyer', FALSE),
            array('userorder_price', FALSE, 'align="right"'),
            array('userorder_status', FALSE,),
            array('userorder_payapi', FALSE,),
            array('userorder_ordertime', FALSE,),
            array('emptychar', FALSE, 'align="center" noWrap="noWrap"')
                ), '', FALSE, ' tablerow');
        $status = isset(phpcom::$G['gp_status']) ? intval(phpcom::$G['gp_status']) : 0;
        $orderid = isset(phpcom::$G['gp_orderid']) ? trim(phpcom::$G['gp_orderid']) : null;
        $buyer = isset(phpcom::$G['gp_buyer']) ? trim(phpcom::$G['gp_buyer']) : null;
        $conditions = array();
        $condition = $queryurl = '';
        if ($status > 0) {
            $conditions[] = "status='$status'";
            $queryurl = "&status=$status";
        }
        if (!empty($orderid)) {
            $conditions[] = "orderid LIKE '$orderid%'";
            $queryurl .= "&orderid=$orderid";
        }
        if (!empty($buyer)) {
            $conditions[] = "buyer='$buyer'";
            $queryurl .= "&buyer=$buyer";
        }
        if (!empty($conditions)) {
            $condition = ' WHERE ' . implode(' AND ', $conditions);
        }
        $todaytime = strtotime(fmdate(TIMESTAMP, 'Ymd'));
        $totalrec = DB::result_first("SELECT COUNT(*) FROM " . DB::table('userorder') . "$condition");
        $pagenow = $page;
        $pagesize = 50;
        $pagecount = @ceil($totalrec / $pagesize);
        $pagenow > $pagecount && $pagenow = 1;
        $pagestart = floor(($pagenow - 1) * $pagesize);
        $sql = DB::buildlimit("SELECT * FROM " . DB::table('userorder') . "$condition ORDER BY ordertime DESC", $pagesize, $pagestart);
        $query = DB::query($sql);
        while ($row = DB::fetch_array($query)) {
            $detail = $adminhtml->edit_word('detail', "action=paytrade&m=userorder&orderid=" . $row['orderid']);
            $row['istoday'] = $row['ordertime'] > $todaytime ? 1 : 0;
            if ($row['istoday']) {
                $row['ordertime'] = '<em class="new f10">' . fmdate($row['ordertime'], 'dt') . '</em>';
            } else {
                $row['ordertime'] = '<em class="c0 f10">' . fmdate($row['ordertime'], 'dt') . '</em>';
            }
            $adminhtml->table_td(array(
                array('<input type="checkbox" class="checkbox" name="delete[]" value="' . $row['orderid'] . '" />', TRUE),
                array($row['orderid'], TRUE),
                array('userorder_' . $row['subject'], FALSE),
                array($row['buyer'], TRUE),
                array(sprintf('%.2f', $row['price']) . ' ' . adminlang('currency_unit'), TRUE, 'align="right"'),
                array(intval($row['status']), FALSE),
                array($row['payapi'], FALSE),
                array($row['ordertime'], TRUE),
                array($detail, TRUE, 'align="center"')
            ));
        }
        $adminhtml->table_td(array(
            array($adminhtml->del_submit(), TRUE, 'colspan="9"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=userorder$queryurl") . '</var>';
        $adminhtml->table_td(array(
            array($showpage, TRUE, 'colspan="9" align="right" id="pagecode"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        $delete = phpcom::$G['gp_delete'];
        if (@$delete) {
            $orderids = implodevalue($delete);
            DB::query("DELETE FROM " . DB::table('tradeorder') . " WHERE orderid IN ($orderids)");
        }
        admin_succeed('userorder_delete_succeed', 'm=userorder&action=tradeorder');
    }
} elseif ($action == 'paytrade') {
    $adminhtml->tablesetmode = FALSE;
    $adminhtml->disabledtips = TRUE;
    $orderid = trim(phpcom::$G['gp_orderid']);
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        include loadlibfile('transip');
        $tradeorder = DB::fetch_first("SELECT * FROM " . DB::table('tradeorder') . " WHERE orderid='$orderid'");
        $adminhtml->form('m=userorder&action=paytrade', array(array('orderid', $tradeorder['orderid'])));
        $adminhtml->table_header('userorder_paytrade', 2);
        $adminhtml->table_setting('userorder_orderno', 'userorder_orderno_comments', array('orderno' => $tradeorder['orderid']), '0');
        $adminhtml->table_setting('userorder_tradeno', 'userorder_tradeno_comments', array('tradeno' => $tradeorder['tradeno']), '0');
        if ($tradeorder['status'] != 2) {
            $adminhtml->table_setting('paysubmit', 'btnsubmit', '', 'submit');
        }
        $adminhtml->table_end('</form>');
    } else {
        if ($orderid) {
            $data = array();
            $data['status'] = 2;
            $data['admin'] = phpcom::$G['username'];
            if (phpcom::$G['gp_buyer']) {
                $data['buyer'] = striptags(phpcom::$G['gp_buyer']);
            }
            DB::update('tradeorder', $data, "orderid='$orderid'");
        }
        admin_succeed('tradeorder_succeed', 'm=userorder&action=tradeorder');
    }
} elseif ($action == 'payorder') {
    $adminhtml->tablesetmode = FALSE;
    $adminhtml->disabledtips = TRUE;
    $orderid = trim(phpcom::$G['gp_orderid']);
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        include loadlibfile('transip');
        $userorder = DB::fetch_first("SELECT * FROM " . DB::table('userorder') . " WHERE orderid='$orderid'");
        $adminhtml->form('m=userorder&action=payorder', array(array('orderid', $userorder['orderid'])));
        $adminhtml->table_header('userorder_payorder', 2);
        $adminhtml->table_setting('userorder_orderno', 'userorder_orderno_comments', array('orderno' => $userorder['orderid']), '0');
        $adminhtml->table_setting('userorder_tradeno', 'userorder_tradeno_comments', array('tradeno' => $userorder['tradeno']), '0');
        $adminhtml->table_setting('userorder_buyer', 'userorder_buyer_comments', array('buyer' => $userorder['buyer']), '0');
        $adminhtml->table_setting('userorder_email', 'userorder_email_comments', array('email' => $userorder['email']), '0');
        $adminhtml->table_setting('userorder_admin', 'userorder_admin_comments', array('admin' => $userorder['admin']), '0');
        $adminhtml->table_setting('userorder_amount', 'userorder_amount_comments', array('amount' => $userorder['amount']), '0');
        $adminhtml->table_setting('userorder_price', 'userorder_price_comments', array('price' => formatmoney($userorder['price'])), '0');
        $adminhtml->table_setting('userorder_status', 'userorder_status_' . $userorder['status'], '', '0');
        $adminhtml->table_setting('userorder_payapi', $userorder['payapi'], '', '0');
        $adminhtml->table_setting('userorder_type', 'userorder_' . $userorder['subject'], '', '0');
        $adminhtml->table_setting('userorder_ordertime', 'userorder_ordertime_comments', array('ordertime' => fmdate($userorder['ordertime'], 'Y-m-d H:i:s')), '0');
        $adminhtml->table_setting('userorder_ip', 'userorder_ip_comments', array('ip' => $userorder['ip'], 'location' => translateip($userorder['ip'])), '0');
        if ($userorder['status'] != 2) {
            $adminhtml->table_setting('paysubmit', 'btnsubmit', '', 'submit');
        }
        $adminhtml->table_end('</form>');
    } else {
        if ($orderid) {
            $data = array();
            $data['status'] = 2;
            $data['admin'] = phpcom::$G['username'];
            if (phpcom::$G['gp_buyer']) {
                $data['buyer'] = striptags(phpcom::$G['gp_buyer']);
            }
            DB::update('userorder', $data, "orderid='$orderid'");
        }
        admin_succeed('userorder_succeed', 'm=userorder');
    }
} else {
    if (!checksubmit(array('submit', 'btnsubmit'), 1)) {
        $adminhtml->form('m=userorder');
        $adminhtml->table_header('userorder_search', 9);
        $adminhtml->table_td(array(
            array('deletecheckbox', FALSE, 'width="5%" noWrap="noWrap"'),
            array('userorder_orderid', FALSE),
            array('userorder_type', FALSE),
            array('userorder_buyer', FALSE),
            array('userorder_price', FALSE, 'align="right"'),
            array('userorder_status', FALSE,),
            array('userorder_payapi', FALSE,),
            array('userorder_ordertime', FALSE,),
            array('emptychar', FALSE, 'align="center" noWrap="noWrap"')
                ), '', FALSE, ' tablerow');
        $status = isset(phpcom::$G['gp_status']) ? intval(phpcom::$G['gp_status']) : 0;
        $orderid = isset(phpcom::$G['gp_orderid']) ? trim(phpcom::$G['gp_orderid']) : '';
        $buyer = isset(phpcom::$G['gp_buyer']) ? trim(phpcom::$G['gp_buyer']) : '';
        $conditions = array();
        $condition = $queryurl = '';
        if ($status > 0) {
            $conditions[] = "status='$status'";
            $queryurl = "&status=$status";
        }
        if (!empty($orderid)) {
            $conditions[] = "orderid LIKE '$orderid%'";
            $queryurl .= "&orderid=$orderid";
        }
        if (!empty($buyer)) {
            $conditions[] = "buyer='$buyer'";
            $queryurl .= "&buyer=$buyer";
        }
        if (!empty($conditions)) {
            $condition = ' WHERE ' . implode(' AND ', $conditions);
        }
        $todaytime = strtotime(fmdate(TIMESTAMP, 'Ymd'));
        $totalrec = DB::result_first("SELECT COUNT(*) FROM " . DB::table('userorder') . "$condition");
        $pagenow = $page;
        $pagesize = 50;
        $pagecount = @ceil($totalrec / $pagesize);
        $pagenow > $pagecount && $pagenow = 1;
        $pagestart = floor(($pagenow - 1) * $pagesize);
        $sql = DB::buildlimit("SELECT * FROM " . DB::table('userorder') . "$condition ORDER BY ordertime DESC", $pagesize, $pagestart);
        $query = DB::query($sql);
        while ($row = DB::fetch_array($query)) {
            $detail = $adminhtml->edit_word('detail', "action=payorder&m=userorder&orderid=" . $row['orderid']);
            $row['istoday'] = $row['ordertime'] > $todaytime ? 1 : 0;
            if ($row['istoday']) {
                $row['ordertime'] = '<em class="new f10">' . fmdate($row['ordertime']) . '</em>';
            } else {
                $row['ordertime'] = '<em class="c0 f10">' . fmdate($row['ordertime']) . '</em>';
            }
            $adminhtml->table_td(array(
                array('<input type="checkbox" class="checkbox" name="delete[]" value="' . $row['orderid'] . '" />', TRUE),
                array($row['orderid'], TRUE),
                array('userorder_' . $row['subject'], FALSE),
                array($row['buyer'], TRUE),
                array(sprintf('%.2f', $row['price']) . ' ' . adminlang('currency_unit'), TRUE, 'align="right"'),
                array('userorder_status_' . intval($row['status']), FALSE),
                array($row['payapi'], FALSE),
                array($row['ordertime'], TRUE),
                array($detail, TRUE, 'align="center"')
            ));
        }
        $adminhtml->table_td(array(
            array($adminhtml->del_submit(), TRUE, 'colspan="9"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=userorder$queryurl") . '</var>';
        $adminhtml->table_td(array(
            array($showpage, TRUE, 'colspan="9" align="right" id="pagecode"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        $delete = phpcom::$G['gp_delete'];
        if (@$delete) {
            $orderids = implodevalue($delete);
            DB::query("DELETE FROM " . DB::table('userorder') . " WHERE orderid IN ($orderids)");
        }
        admin_succeed('userorder_delete_succeed', 'm=userorder');
    }
}
admin_footer();

function formatmoney($number, $fractional = TRUE) {
    if ($fractional) {
        $number = sprintf('%.2f', $number);
    }
    while (TRUE) {
        $replaced = preg_replace('/(-?\d+)(\d\d\d)/', '$1,$2', $number);
        if ($replaced != $number) {
            $number = $replaced;
        } else {
            break;
        }
    }
    return $number;
}

?>
