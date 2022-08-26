<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : banip.php    2011-12-10
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';
admin_header($admintitle);
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('global');
if (!checksubmit(array('submit', 'btnsubmit'), 1)) {
    include loadlibfile('transip');
    $adminhtml->form('m=banip', array(array('action', 'edit')), 'name="banipform"');
    $adminhtml->table_header('banned_title', 6);
    $adminhtml->table_td(array(array('banned_tips', FALSE, 'colspan="6"')), NULL, FALSE, NULL, NULL, FALSE);
    $adminhtml->table_td(array(
        array('delete', FALSE, 'width="5%" noWrap="noWrap"'),
        array('ip', FALSE, 'width="20%"'),
        array('iplocation', FALSE, 'width="25%"'),
        array('operator', FALSE, 'width="15%"'),
        array('starttime', FALSE, 'width="15%" align="left"'),
        array('stoptime', FALSE, 'width="20%"')
            ), '', FALSE, ' tablerow');
    $totalrec = count_banned();
    $pagenow = $page;
    $pagesize = intval(phpcom::$config['admincp']['pagesize']);
    $pagecount = @ceil($totalrec / $pagesize);
    $pagenow = max(1, min($pagecount, intval($pagenow)));
    $limit_offset = floor(($pagenow - 1) * $pagesize);
    $sql = DB::buildlimit("SELECT banid, ip, admin, dateline, expiration FROM " . DB::table('banned') . " ORDER BY banid DESC", $pagesize, $limit_offset);
    $query = DB::query($sql);
    while ($row = DB::fetch_array($query)) {
        $banid = $row['banid'];
        $adminhtml->table_td(array(
            array('<input type="checkbox" class="checkbox" name="delete[]" value="' . $banid . '" />', TRUE, 'align="left"'),
            array($adminhtml->inputedit("ipupdate[$banid]", $row['ip'], 20, 'left'), TRUE),
            array(translateip($row['ip']), TRUE),
            array($row['admin'], TRUE),
            array(fmdate($row['dateline'], 'd', 'd'), TRUE),
            array($adminhtml->inputedit("expiration[$banid]", fmdate($row['expiration'], 'Y-m-d'), 20, 'left'), TRUE)
        ));
    }
    $adminhtml->table_td(array(
        array('add', FALSE, 'noWrap="noWrap"'),
        array('<input class="input t20" name="ipnew" type="text" />', TRUE, 'colspan="3"'),
        array(adminlang('period') . ': <input class="input t5" name="period" type="text" value="30" /> ' . adminlang('day'), TRUE, 'colspan="2"')
            ), NULL, FALSE, NULL, NULL, FALSE);
    $adminhtml->table_td(array(
        array($adminhtml->checkall('del'), TRUE, 'noWrap="noWrap"'),
        array($adminhtml->submit_button(), TRUE, 'align="center" colspan="5"')
            ), NULL, FALSE, NULL, NULL, FALSE);
    if ($pagecount > 1) {
        $showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=banip") . '</var>';
        $adminhtml->table_td(array(
            array($showpage, TRUE, 'colspan="6" align="right" id="pagecode"')
                ), NULL, FALSE, NULL, NULL, FALSE);
    }
    $adminhtml->table_end('</form>');
} else {
    $expirations = isset(phpcom::$G['gp_expiration']) ? phpcom::$G['gp_expiration'] : null;
    $ipupdates = isset(phpcom::$G['gp_ipupdate']) ? phpcom::$G['gp_ipupdate'] : null;
    $delete = isset(phpcom::$G['gp_delete']) ? phpcom::$G['gp_delete'] : null;
    if (@$delete) {
        $banids = implodeids($delete);
        DB::query("DELETE FROM " . DB::table('banned') . " WHERE banid IN ($banids)");
        foreach ($delete as $value) {
            unset($expirations[$value]);
        }
        unset($delete);
        if (empty($expirations)) {
            $count = count_banned();
            if(!$count){
                truncate_banned();
            }
        }
    }
    $banneds = array();
    $ipnew = trim(phpcom::$G['gp_ipnew']);
    $period = intval(phpcom::$G['gp_period']);
    if ($ipnew) {
        if (check_banipv4($ipnew)) {
            $banneds['ip'] = $ipnew;
            $banneds['admin'] = phpcom::$G['username'];
            $banneds['dateline'] = TIMESTAMP;
            $banneds['expiration'] = TIMESTAMP + $period * 86400;
            DB::insert('banned', $banneds);
        } else {
            admin_message('banned_banip_invalid');
        }
        unset($banneds);
    }
    if ($expirations && is_array($expirations)) {
        foreach ($expirations as $banid => $expiration) {
            $ip = trim($ipupdates[$banid]);
            if (check_banipv4($ip)) {
                DB::query("UPDATE " . DB::table('banned') . " SET ip='$ip', expiration='" . strtotime($expiration) . "' WHERE banid='$banid'");
            }
        }
    }
    phpcom_cache::updater('banip');
    phpcom::header('Location: ' . ADMIN_SCRIPT . '?m=banip');
}

admin_footer();

function check_banipv4($ip) {
    return preg_match('#^[0-9\*]{1,3}\.[0-9\*]{1,3}\.[0-9\*]{1,3}\.[0-9\*]{1,3}$#', $ip);
}

function truncate_banned() {
    DB::query("TRUNCATE TABLE " . DB::table('banned'));
}

function count_banned() {
    return (int) DB::result_first("SELECT COUNT(*) FROM " . DB::table('banned'));
}

?>
