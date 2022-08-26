<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : subtable.php    2012-2-8
 */
!defined('IN_ADMINCP') && exit('Access denied');
@set_time_limit(0);
define('MAX_POSTS_MOVE', 100000);
phpcom::$G['lang']['admin'] = 'misc';
admin_header('menu_subtable');
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('tools');
$navarray = array(
    array(
        'title' => 'menu_subtable',
        'url' => '?m=subtable',
        'id' => 'subtable_manage'
    )
);
$adminhtml->navtabs($navarray, 'subtable_manage');
$subtableids = $subtableinfo = array();
$query = DB::query("SELECT skey, svalue FROM " . DB::table('setting') . " WHERE skey IN ('subtableinfo', 'subtableids')");
while ($var = DB::fetch_array($query)) {
    switch ($var['skey']) {
        case 'subtableinfo':
            $subtableinfo = $var['svalue'];
            break;
        case 'subtableids':
            $subtableids = $var['svalue'];
            break;
    }
}
$deftableinfo = array(
    'article' => array('0' => array('type' => 'primary')),
    'soft' => array('0' => array('type' => 'primary')),
    'shop' => array('0' => array('type' => 'primary')),
    'photo' => array('0' => array('type' => 'primary')),
    'video' => array('0' => array('type' => 'primary'))
);
if (empty($subtableinfo)) {
    $subtableinfo = $deftableinfo;
} else {
    $subtableinfo = unserialize($subtableinfo);
    if (!$subtableinfo) {
        $subtableinfo = $deftableinfo;
    }
}
if (empty($subtableids)) {
    $subtableids = array();
} else {
    $subtableids = unserialize($subtableids);
}
if ($action == 'optimize') {
    check_websiteclosed();
    $fromtableid = intval(phpcom::$G['gp_tableid']);
    $fromtype = checktabletype(phpcom::$G['gp_type']);
    $optimized = TRUE;
    $tablename = getsubtable($fromtableid, $fromtype);
    $deftable = getsubtable(0, $fromtype);
    if ($fromtableid && $tablename != $deftable) {
        $count = DB::result_first("SELECT COUNT(*) FROM " . DB::table($tablename));
        if (!$count) {
            DB::query("DROP TABLE " . DB::table($tablename));
            unset($subtableinfo[$fromtype][$fromtableid]);
            update_subtableinfo($subtableinfo);
            update_subtableids();
            $optimized = FALSE;
        }
    }
    if ($optimized) {
        DB::query("OPTIMIZE TABLE " . DB::table($tablename), 'SILENT');
    }
    admin_succeed('subtable_do_succeed', 'm=subtable');
} elseif ($action == 'move') {
    check_websiteclosed();
    list($tableid, $movesize, $destntableid, $sourcesize, $tabletype) = explode("\t", urldecode(decryptstring(phpcom::$G['gp_hash'])));
    $hash = urlencode(phpcom::$G['gp_hash']);
    if ($tableid == phpcom::$G['gp_fromtable'] && $movesize == phpcom::$G['gp_movesize'] && $destntableid == phpcom::$G['gp_destntable'] && $tabletype == phpcom::$G['gp_type']) {
        $fromtableid = intval(phpcom::$G['gp_fromtable']);
        $movesize = intval(phpcom::$G['gp_movesize']);
        $destntableid = intval(phpcom::$G['gp_destntable']);
        $tabletype = checktabletype(phpcom::$G['gp_type']);
        $destntable = gettablefields(getsubtable($destntableid, $tabletype));
        $fieldstr = '`' . implode('`, `', array_keys($destntable)) . '`';
        if (!$fromtableid) {
            $table = getprimarytable($tabletype);
            $count = DB::result_first("SELECT count(*) FROM " . DB::table($table) . " WHERE  tableindex='0'");
            if ($count) {
                $query = DB::query("SELECT tid FROM " . DB::table($table) . " WHERE tableindex='0' ORDER BY tid LIMIT 0, 1000");
                transferdata($query);
            } else {
                admin_message('subtable_done', "m=subtable&action=optimize&tableid=$fromtableid&type=$tabletype", NULL, 'form');
            }
        } else {
            $count = DB::result_first("SELECT count(*) FROM " . DB::table(getsubtable($fromtableid, $tabletype)));
            if ($count) {
                $query = DB::query("SELECT tid FROM " . DB::table(getsubtable($fromtableid, $tabletype)) . " LIMIT 0, 1000");
                transferdata($query);
            } else {
                admin_message('subtable_done', "m=subtable&action=optimize&tableid=$fromtableid&type=$tabletype", NULL, 'form');
            }
        }
    } else {
        admin_message('subtable_abnormal', 'm=subtable');
    }
} elseif ($action == 'split') {
    check_websiteclosed();
    $tableid = intval(phpcom::$G['gp_tableid']);
    $tabletype = checktabletype(phpcom::$G['gp_type']);
    $tablename = getsubtable($tableid, $tabletype);
    $deftable = getsubtable(0, $tabletype);
    if ($tableid && $tablename != $deftable || !$tableid) {
        $status = gettablestatus(DB::table($tablename), FALSE);
        $allowsubtable = FALSE;
        if ($status && ((!$tableid && $status['Data_length'] > 400 * 1048576) || ($tableid && $status['Data_length']))) {
            if (!checksubmit(array('submit', 'btnsubmit'))) {
                $adminhtml->form('m=subtable', array(array('action', 'split'), array('type', $tabletype), array('tableid', $tableid)));
                $adminhtml->table_header("menu_subtable_$tabletype", 2);
                $adminhtml->table_td(array(array('subtable_tips', FALSE, 'colspan="2"')), NULL, FALSE, NULL, NULL, FALSE);
                $adminhtml->table_td(array(
                    array('subtable_source_table', FALSE, 'width="100" align="right"'),
                    array('subtable_source_table_comments', array('table' => $tablename))
                ));
                $tablelist = '<option value="-1">' . adminlang('subtable_create_new_table') . '</option>';
                foreach ($subtableinfo[$tabletype] as $tid => $info) {
                    if ($tableid != $tid) {
                        $tablestatus = gettablestatus(getsubtable($tid, $tabletype, TRUE));
                        $tablelist .= '<option value="' . $tid . '">' . ($info['memo'] ? $info['memo'] : $tabletype . '_content' . ($tid ? '_' . $tid : '')) . '(' . $tablestatus['Data_length'] . ')' . '</option>';
                    }
                }
                $adminhtml->table_td(array(
                    array('subtable_destination_table', FALSE, 'align="right"'),
                    array('<select class="select t20" onchange="if(this.value >= 0) {$(\'table_memo\').style.display = \'none\';} else {$(\'table_memo\').style.display = \'\';}" name="destntable">' . $tablelist . '</select>', TRUE)
                ));
                $adminhtml->table_td(array(
                    array('subtable_table_memo', FALSE, 'align="right"'),
                    array('<input type="text" class="input t20" id="table_memo" name="memo" />', TRUE)
                ));
                $datasize = round($status['Data_length'] / 1048576);
                $maxsize = round(($datasize - ($tableid ? 0 : 300)) / 100);
                $maxi = $maxsize > 10 ? 10 : ($maxsize < 1 ? 1 : $maxsize);
                $maxsizestr = '';
                for ($i = 1; $i <= $maxi; $i++) {
                    $movesize = $i == 10 ? 1024 : $i * 100;
                    $maxsizestr .= '<option value="' . $movesize . '">' . ($i == 10 ? formatbytes($movesize * 1048576) : $movesize . 'MB') . '</option>';
                }
                $adminhtml->table_td(array(
                    array('subtable_move_datasize', FALSE, 'align="right"'),
                    array('<select class="select t20" name="movesize">' . $maxsizestr . '</select>', TRUE)
                ));
                $adminhtml->table_td(array(
                    array($adminhtml->submit_button('subtable_submit', 'btnsubmit', 'button'), TRUE, 'colspan="2"')
                        ), NULL, FALSE, NULL, NULL, FALSE);
                $adminhtml->table_end('</form>');
            } else {
                $destntable = intval(phpcom::$G['gp_destntable']);
                $createtable = FALSE;
                if ($destntable == -1) {
                    $maxtableid = getmaxsubtableid($tabletype . '_content');
                    $deftablename = getsubtable(0, $tabletype);
                    DB::query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');
                    $tableinfo = DB::fetch_first("SHOW CREATE TABLE " . DB::table($deftablename));
                    $createsql = $tableinfo['Create Table'];
                    $destntable = $maxtableid + 1;
                    $newtable = $tabletype . '_content_' . $destntable;
                    $createsql = str_replace($deftablename, $newtable, $createsql);
                    DB::query($createsql);
                    $subtableinfo[$tabletype][$destntable]['memo'] = phpcom::$G['gp_memo'];
                    update_subtableinfo($subtableinfo);
                    update_subtableids();
                    $createtable = TRUE;
                }
                $sourcetablearr = gettablefields(getsubtable($tableid, $tabletype));
                $destntablearr = gettablefields(getsubtable($destntable, $tabletype));
                $fields = array_diff(array_keys($sourcetablearr), array_keys($destntablearr));
                if (!empty($fields)) {
                    admin_message('subtable_do_error', '', array('tableid' => DB::table(getsubtable($destntable, $tabletype)), 'fields' => implode(',', $fields)));
                }
                $movesize = intval(phpcom::$G['gp_movesize']);
                $movesize = $movesize >= 100 && $movesize <= 1024 ? $movesize : 100;
                $destnstatus = gettablestatus(getsubtable($destntable, $tabletype, TRUE), FALSE);
                $hash = urlencode(encryptstring("$tableid\t$movesize\t$destntable\t$destnstatus[Data_length]\t$tabletype"));
                $url = "m=subtable&action=move&fromtable=$tableid&movesize=$movesize&destntable=$destntable&type=$tabletype&hash=$hash";
                if ($createtable) {
                    admin_message('subtable_create_succeed', $url, '', 'loading');
                } else {
                    admin_message('subtable_finish', $url, '', 'loading');
                }
            }
        } else {
            admin_message('subtable_unallow', 'm=subtable');
        }
    }
} else {
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        $adminhtml->form('m=subtable');
        $adminhtml->table_header('subtable_manage', 4);
        $adminhtml->table_td(array(array('subtable_tips', FALSE, 'colspan="4"')), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_td(array(
            array('subtable_article_tablename', FALSE),
            array('subtable_datasize', FALSE),
            array('subtable_tablememo', FALSE),
            array('emptychar', FALSE)
                ), '', FALSE, ' tablerow', NULL, FALSE);
        $subtables = array('article' => array(), 'soft' => array());
        $query = DB::query("SHOW TABLES LIKE '" . DB::table('%_content') . "\_%'");
        while ($table = DB::fetch_array($query)) {
            list($tempkey, $tablename) = each($table);
            $tableid = getsubtableid($tablename);
            $tablekey = getsubtablekey($tablename);
            if (!preg_match('/^\d+$/', $tableid)) {
                continue;
            }
            $subtables[$tablekey][$tableid] = $tablename;
        }
        $tablename = DB::table('article_content');
        $subtables['article'] = array_merge(array('0' => $tablename), (array)$subtables['article']);
        foreach ($subtables['article'] as $tableid => $tablename) {
            $tablestatus = gettablestatus($tablename);
            showtableinfo($adminhtml, $tablestatus, $tablename, $tableid, 'article', $subtableinfo['article'][$tableid]['memo']);
        }
        $adminhtml->table_td(array(array('subtable_soft_tablename', FALSE, 'colspan="4"')), '', FALSE, ' tablerow', NULL, FALSE);
        $tablename = DB::table('soft_content');
        $subtables['soft'] = array_merge(array('0' => $tablename), (array)$subtables['soft']);
        foreach ($subtables['soft'] as $tableid => $tablename) {
            $tablestatus = gettablestatus($tablename);
            showtableinfo($adminhtml, $tablestatus, $tablename, $tableid, 'soft', $subtableinfo['soft'][$tableid]['memo']);
        }
        $adminhtml->table_td(array(
            array($adminhtml->submit_button('subtable_update_info', 'btnsubmit', 'button'), TRUE, 'colspan="4"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        $subtable_info = array();
        $tablememos = phpcom::$G['gp_memo'];
        foreach ($tablememos as $key => $tables) {
            $key = checktabletype($key);
            foreach ($tables as $tableid => $value) {
                $tableid = intval($tableid);
                $subtable_info[$key][$tableid]['memo'] = $value;
            }
        }
        update_subtableinfo($subtable_info);
        update_subtableids();
        admin_succeed('subtable_memo_update_succeed', 'm=subtable');
    }
}
admin_footer();

function check_websiteclosed() {
    if (!phpcom::$setting['siteclosed']) {
        admin_message('subtable_website_must_be_closed');
    }
}

function showtableinfo($adminhtml, $tablestatus, $tablename, $tableid = 0, $tablekey = 'article', $memo = '') {
    $adminhtml->table_td(array(
        array($tablename, TRUE),
        array($tablestatus['Data_length'], TRUE),
        array("<input type=\"text\" class=\"input t20\" name=\"memo[$tablekey][$tableid]\" value=\"{$memo}\" />", TRUE),
        array('<a href="' . ADMIN_SCRIPT . "?m=subtable&action=split&type=$tablekey&tableid=$tableid\">" . adminlang('subtable_name') . '</a>', TRUE)
    ));
}

function getsubtablekey($tablename) {
    list(, $tablekey) = explode('_', $tablename);
    return $tablekey;
}

function getsubtableid($tablename) {
    $tableid = substr($tablename, strrpos($tablename, '_') + 1);
    return $tableid;
}

function getprimarytable($type) {
    static $table_array = array('article' => 'article_thread', 'soft' => 'soft_thread', 'special' => 'special_thread', 'photo' => 'photo_thread', 'video' => 'video_thread');
    return isset($table_array[$type]) ? $table_array[$type] : 'article';
}

function checktabletype($type) {
    return in_array($type, array('article', 'soft', 'special', 'photo', 'video')) ? $type : 'article';
}

function getmaxsubtableid($tablename = 'article_content') {
    $query = DB::query("SHOW TABLES LIKE '" . DB::table($tablename) . "\_%'");
    $maxtableid = 0;
    while ($table = DB::fetch_array($query)) {
        list($tempkey, $tablename) = each($table);
        $tableid = intval(getsubtableid($tablename));
        if ($tableid > $maxtableid) {
            $maxtableid = $tableid;
        }
    }
    return $maxtableid;
}

function update_subtableinfo($data) {
    DB::insert('setting', array(
        'skey' => 'subtableinfo',
        'svalue' => addslashes_array(serialize($data)),
        'stype' => 'array'
            ), FALSE, TRUE);
    phpcom_cache::save('subtableinfo', $data);
}

function update_subtableids() {
    $tableids = get_subtableids();
    DB::insert('setting', array(
        'skey' => 'subtableids',
        'svalue' => serialize($tableids),
        'stype' => 'array'
            ), FALSE, TRUE);
    phpcom_cache::save('subtableids', $tableids);
}

function get_subtableids() {
    $tableids = array('article' => array(0), 'soft' => array(0), 'special' => array(0), 'photo' => array(0), 'video' => array(0));
    $query = DB::query("SHOW TABLES LIKE '" . DB::table('%_content') . "\_%'");
    while ($table = DB::fetch_array($query)) {
        list($tempkey, $tablename) = each($table);
        $tableid = getsubtableid($tablename);
        if (!preg_match('/^\d+$/', $tableid)) {
            continue;
        }
        $tableid = intval($tableid);
        if (!$tableid) {
            continue;
        }
        $tablekey = getsubtablekey($tablename);
        $tableids[$tablekey][] = $tableid;
    }
    return $tableids;
}

function gettablestatus($tablename, $formatsize = TRUE) {
    $status = DB::fetch_first("SHOW TABLE STATUS LIKE '" . str_replace('_', '\_', $tablename) . "'");
    if ($formatsize) {
        $status['Data_length'] = formatbytes($status['Data_length']);
        $status['Index_length'] = formatbytes($status['Index_length']);
    }
    return $status;
}

function gettablefields($table) {
    static $tables = array();
    if (!isset($tables[$table])) {
        $tables[$table] = array();
        $db = DB::instance();
        if ($db->version() > '4.1') {
            $query = $db->query("SHOW FULL COLUMNS FROM " . DB::table($table), 'SILENT');
        } else {
            $query = $db->query("SHOW COLUMNS FROM " . DB::table($table), 'SILENT');
        }
        while ($field = @DB::fetch_array($query)) {
            $tables[$table][$field['Field']] = $field;
        }
    }
    return $tables[$table];
}

function transferdata($query) {
    global $sourcesize, $tableid, $tabletype, $movesize, $destntableid, $hash, $fieldstr, $fromtableid, $subtableinfo;
    $tids = array();
    while ($value = DB::fetch_array($query)) {
        $tids[$value['tid']] = $value['tid'];
    }
    ksort($tids);
    $fromtable = getsubtable($fromtableid, $tabletype, TRUE);
    $condition = " tid IN(" . implodeids($tids) . ")";
    DB::query("INSERT INTO " . DB::table(getsubtable($destntableid, $tabletype)) . " ($fieldstr) SELECT $fieldstr FROM $fromtable WHERE $condition", 'SILENT');
    if (DB::errno()) {
        DB::delete(getsubtable($destntableid, $tabletype), $condition);
    } else {
        $table = getprimarytable($tabletype);
        DB::update($table, array('tableindex' => $destntableid), $condition);
        DB::update('threads', array('tableindex' => $destntableid), $condition);
        DB::delete(getsubtable($fromtableid, $tabletype), $condition);
    }
    $status = gettablestatus(DB::table(getsubtable($destntableid, $tabletype)), FALSE);
    $destnsize = $sourcesize + $movesize * 1048576;
    $nowdatasize = $destnsize - $status['Data_length'];
    if ($status['Data_length'] >= $destnsize) {
        admin_message('subtable_done', "m=subtable&action=optimize&tableid=$fromtableid&type=$tabletype", '', 'form');
    }
    admin_message('subtable_doing', "m=subtable&action=move&fromtable=$tableid&movesize=$movesize&destntable=$destntableid&type=$tabletype&hash=$hash", array('datalength' => formatbytes($status['Data_length']), 'nowdatalength' => formatbytes($nowdatasize)), 'loadingform');
}

?>
