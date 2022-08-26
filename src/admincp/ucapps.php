<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : ucapps.php    2011-12-30
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'user';
if (phpcom::$G['inajax']) {
    test_appconnection();
    exit();
}
admin_header($admintitle);
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('user');
$navarray = array(
    array(
        'title' => 'menu_ucapps',
        'url' => '?m=ucapps',
        'id' => 'ucapps',
        'name' => 'ucapps'
    ),
    array(
        'title' => 'menu_ucapps_add',
        'url' => '?m=ucapps&action=add',
        'id' => 'ucapps_add',
        'name' => 'ucapps_add'
    )
);
if ($action == 'add' || $action == 'edit') {
    if ($action == 'edit') {
        $navarray[] = array(
            'title' => 'menu_ucapps_edit',
            'id' => 'ucapps_edit',
            'name' => 'ucapps_edit'
        );
    }
    $adminhtml->navtabs($navarray, "ucapps_$action");
    $appid = isset(phpcom::$G['gp_appid']) ? intval(phpcom::$G['gp_appid']) : 0;
    if (!checksubmit()) {
        $ucapps = array('app_type' => 'phpcom', 'authkey' => random(32), 'synlogin' => 1, 'syncredits' => 1,
        		'app_name' => '', 'app_url' => '', 'app_ip' => '', 'api_file' => '');
        if ($action == 'edit' && $appid) {
            $ucapps = DB::fetch_first("SELECT * FROM " . DB::table('ucapps') . " WHERE appid='$appid'");
        }
        $adminhtml->tablesetmode = TRUE;
        $adminhtml->form('m=ucapps', array(array('action', $action), array('appid', $appid)));
        $adminhtml->table_header('ucapps_add', 3);
        $adminhtml->table_setting('ucapps_app_type', 'ucapps[app_type]', $ucapps['app_type'], 'select');
        $adminhtml->table_setting('ucapps_app_name', 'ucapps[app_name]', $ucapps['app_name'], 'text');
        $adminhtml->table_setting('ucapps_app_url', 'ucapps[app_url]', $ucapps['app_url'], 'text');
        $adminhtml->table_setting('ucapps_app_ip', 'ucapps[app_ip]', $ucapps['app_ip'], 'text');
        $adminhtml->table_setting('ucapps_authkey', 'ucapps[authkey]', $ucapps['authkey'], 'text', 'make_authkey(this.id,32)', 'appauthkey');
        $adminhtml->table_setting('ucapps_api_file', 'ucapps[api_file]', $ucapps['api_file'], 'text');
        $adminhtml->table_setting('ucapps_synlogin', 'ucapps[synlogin]', intval($ucapps['synlogin']), 'radio');
        $adminhtml->table_setting('ucapps_syncredits', 'ucapps[syncredits]', intval($ucapps['syncredits']), 'radio');
        $adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
        $adminhtml->table_end('</form>');
    } else {
        $ucapps = striptags(phpcom::$G['gp_ucapps']);
        $appname = $ucapps['app_name'];
        if (empty($ucapps['authkey'])) {
            $ucapps['authkey'] = random(32);
        }
        if ($action == 'edit' && $appid) {
            DB::update('ucapps', $ucapps, array('appid' => $appid));
            admin_succeed('ucapps_edit_succeed', "m=ucapps&action=edit&appid=$appid", array('name' => $appname));
        } elseif ($action == 'add' && empty($appid)) {
            DB::insert('ucapps', $ucapps);
        }
        admin_succeed('ucapps_add_succeed', 'm=ucapps', array('name' => $appname));
    }
} else {
    $adminhtml->navtabs($navarray, 'ucapps');
    if (!checksubmit(array('submit', 'btnsubmit'), 1)) {
        $connecting = adminlang('ucapps_connecting');
        echo <<<ETO
<script type="text/javascript">
var apps = new Array();
var run = 0;
function test_connection() {//alert(apps[run]);
	if(apps[run]) {
		\$('connect_status_' + apps[run]).innerHTML = '$connecting';
		\$('connect_' + apps[run]).src = \$('connect_' + apps[run]).getAttribute('connection');
        
	}
	run++;
}
window.onload = test_connection;
</script>
ETO;
        $adminhtml->form('m=ucapps');
        $adminhtml->table_header('ucapps', 7);
        $adminhtml->table_td(array(
            array('deletecheckbox', FALSE, 'width="5%" noWrap="noWrap"'),
            array('ucapps_appid', FALSE, 'width="10%" noWrap="noWrap"'),
            array('ucapps_app_name', FALSE, 'width="15%"'),
            array('ucapps_app_url', FALSE, 'width="30%"'),
            array('ucapps_app_type', FALSE, 'width="10%"'),
            array('ucapps_pingstatus', FALSE, 'width="15%"'),
            array('detail', FALSE, 'width="15%" align="center" noWrap="noWrap"')
                ), '', FALSE, ' tablerow');
        $query = DB::query("SELECT * FROM " . DB::table('ucapps') . " ORDER BY appid");
        $i = 0;
        while ($row = DB::fetch_array($query)) {
            $appid = $row['appid'];
            $edit = $adminhtml->edit_word('edit', "action=edit&m=ucapps&appid=" . $row['appid']);
            $urls = array(
                'm' => 'ucapps',
                'inajax' => 1,
                'appid' => $appid,
                't' => time(),
            );
            $url = ADMIN_SCRIPT . implodeurl($urls, '?');
            $adminhtml->table_td(array(
                array('<input type="checkbox" class="checkbox" name="delete[]" value="' . $row['appid'] . '" />', TRUE),
                array($row['appid'], TRUE),
                array($row['app_name'], TRUE),
                array('<a href="' . $row['app_url'] . '" target="_blank"">' . $row['app_url'] . '</a>', TRUE),
                array($row['app_type'], TRUE),
                array("<div id=\"connect_status_$appid\"></div><script id=\"connect_$appid\" connection=\"$url\"></script><script>apps[$i] = '$appid';</script>", TRUE),
                array($edit, TRUE, 'align="center"')
            ));
            $i++;
        }
        $adminhtml->table_td(array(
            array($adminhtml->del_submit(), TRUE, 'colspan="7"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        $delete = isset(phpcom::$G['gp_delete']) ? phpcom::$G['gp_delete'] : null;
        if ($delete) {
            $appids = implodeids($delete);
            DB::query("DELETE FROM " . DB::table('ucapps') . " WHERE appid IN ($appids)");
        }
        admin_succeed('delete_succeed', 'm=ucapps');
    }
}
admin_footer();

function test_appconnection() {
    $appid = isset(phpcom::$G['gp_appid']) ? intval(phpcom::$G['gp_appid']) : 0;
    $status = 0;
    $result = DB::fetch_first("SELECT * FROM " . DB::table('ucapps') . " WHERE appid='$appid'");
    if ($result) {
        $authkey = $result['authkey'];
        $url = $result['app_url'];
        $code = encryptstring('action=test&time=' . time(), $authkey);
        $api_file = $result['api_file'] ? $result['api_file'] : 'api/uc.php';
        $url = $url . '/' . $api_file . '?code=' . $code;
        loaducenter();
        $status = uc_fsocketopen($url);
    }
    if ($status == '1') {
        echo 'document.getElementById(\'connect_status_' . $appid . '\').innerHTML = "<span class=\'correct green\'>' . adminlang('ucapps_connent_succeed') . '</span>";test_connection();';
    } else {
        echo 'document.getElementById(\'connect_status_' . $appid . '\').innerHTML = "<span class=\'wrong red\'>' . adminlang('ucapps_connent_failed') . '</span>";test_connection();';
    }
}

?>