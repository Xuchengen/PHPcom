<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : badword.php    2011-12-9
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';
admin_header($admintitle);
$administer = phpcom::$G['username'];
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('global');
$navarray = array(
    array(
        'title' => 'menu_badword',
        'url' => '?m=badword',
        'id' => 'badword_admin',
        'name' => 'badword_admin'
    ),
    array(
        'title' => 'menu_badword_batchadd',
        'url' => '?m=badword&action=batchadd',
        'id' => 'badword_batchadd',
        'name' => 'badword_batchadd'
    )
);
if ($action == 'batchadd') {
    $adminhtml->navtabs($navarray, 'badword_batchadd');
    if (!checksubmit(array('submit', 'btnsubmit'), 1)) {
        $adminhtml->form('m=badword&action=batchadd', null, 'name="addwordform"');
        $adminhtml->table_header('badword_batchadd', 3);
        $adminhtml->table_setting('badword_batchadd_badwords', 'badwords', '', 'textarea');
        $adminhtml->table_setting('badword_batchadd_type', 'type', '2', 'radios', '', '', '', 'chkbox2');
        $adminhtml->table_td(array(
            array($adminhtml->submit_button(), TRUE, 'align="center" colspan="3"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        $badwords = trim(phpcom::$G['gp_badwords']);
		$type = intval(phpcom::$G['gp_type']);
        if($type === 0){
            truncate_badword();
            $type = 1;
        }
        if(!empty($badwords)){
	        $arr = explode("\n", str_replace(array("\r", "\n\n", '|'), array("\r", "\n", "\n"), $badwords));
	        foreach($arr as $k => $v) {
	            $arr2 = explode("=", $v);
	            add_badword($arr2[0], $arr2[1], $administer, $type);
	        }
        }
        phpcom_cache::updater('badwords');
        phpcom::header('Location: ' . ADMIN_SCRIPT . '?m=badword');
    }
} else if ($action == 'add') {
    $adminhtml->navtabs($navarray, 'badword_admin');
    if (!checksubmit(array('submit', 'btnsubmit'), 1)) {
        phpcom::header('Location: ' . ADMIN_SCRIPT . '?m=badword');
    } else {
        $find = trim(phpcom::$G['gp_find']);
        $replacement = trim(phpcom::$G['gp_replacement']);
        add_badword($find, $replacement, $administer, 1);
        phpcom_cache::updater('badwords');
        phpcom::header('Location: ' . ADMIN_SCRIPT . '?m=badword');
    }
} else {
    $adminhtml->navtabs($navarray, 'badword_admin');
    if (!checksubmit(array('submit', 'btnsubmit'), 1)) {
        $adminhtml->form('m=badword&action=add', null, 'name="addwordform"');
        $adminhtml->table_header('badword_add');
        $adminhtml->table_td(array(array('badword_tips', FALSE, '')), NULL, FALSE, NULL, NULL, FALSE);
        $s = '<b>' . adminlang('badword') . '</b> ';
        $s .= ' <input class="input t20" name="find" type="text" /> ';
        $s .= '<b>' . adminlang('replacement') . '</b>';
        $s .= ' <input class="input t20" name="replacement" type="text" /> ';
        $s .=$adminhtml->submit_button(null, null, 'button');
        $adminhtml->table_td(array(
            array($s, TRUE, '')
        ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
        $adminhtml->form('m=badword', array(array('action', 'badword')), 'name="deleteform"');
        $adminhtml->table_header('badword_admin', 5);
        $adminhtml->table_td(array(
            array('delete', FALSE, 'width="5%" noWrap="noWrap"'),
            array('badword', FALSE, 'width="30%" align="right"'),
            array('', TRUE, 'width="5%"'),
            array('replacement', FALSE, 'width="30%" align="left"'),
            array('operator', FALSE, 'width="30%"')
                ), '', FALSE, ' tablerow', NULL, FALSE);
        $totalrec = DB::result_first("SELECT COUNT(*) FROM " . DB::table('badwords'));
        $pagenow = $page;  // 当前页
        $pagesize = intval(phpcom::$config['admincp']['pagesize']);
        $pagecount = @ceil($totalrec / $pagesize);  //计算总页数
        $pagenow = max(1, min($pagecount, intval($pagenow)));
        $limit_offset = floor(($pagenow - 1) * $pagesize);
        if ($totalrec) {
            $sql = DB::buildlimit("SELECT id, admin, find, replacement, pattern FROM " . DB::table('badwords') . " ORDER BY id DESC", $pagesize, $limit_offset);
            $query = DB::query($sql);
            while ($row = DB::fetch_array($query)) {
                $id = $row['id'];
                $adminhtml->table_td(array(
                    array('<input type="checkbox" class="checkbox" name="delete[]" value="' . $row['id'] . '" />', TRUE, 'align="left"'),
                    array($adminhtml->inputedit("find[$id]", $row['find'], 35, 'right'), TRUE, 'align="right"'),
                    array('<em class="f10">&gt;&gt;</em>', TRUE, ' align="center" noWrap="noWrap"'),
                    array($adminhtml->inputedit("replacement[$id]", $row['replacement'], 35, 'left'), TRUE),
                    array($row['admin'], TRUE)
                ));
            }
            $adminhtml->table_td(array(
                array($adminhtml->checkall('del'), TRUE, 'noWrap="noWrap"'),
                array($adminhtml->submit_button(), TRUE, 'align="center" colspan="4"')
                    ), NULL, FALSE, NULL, NULL, FALSE);
            $showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=badword") . '</var>';
            $adminhtml->table_td(array(
                array($showpage, TRUE, 'colspan="5" align="right" id="pagecode"')
                    ), NULL, FALSE, NULL, NULL, FALSE);
        }
        $adminhtml->table_end('</form>');
    } else {
        $delete = isset(phpcom::$G['gp_delete']) ? phpcom::$G['gp_delete'] : null;
        $find = phpcom::$G['gp_find'];
        $replacement = phpcom::$G['gp_replacement'];
        if ($find) {
            foreach ($find as $id => $value) {
                update_badword($value, $replacement[$id], $id);
            }
        }
        if (!empty($delete)) {
            delete_badword($delete);
        }
        phpcom_cache::updater('badwords');
        phpcom::header('Location: ' . ADMIN_SCRIPT . '?m=badword');
    }
}
admin_footer();

function add_badword($find, $replacement, $admin, $type = 1) {
    if ($find) {
        $find = trim($find);
        $replacement = trim($replacement);
        $findpattern = find_pattern($find);
        if ($type == 1) {
            DB::query("REPLACE INTO " . DB::table('badwords') . " SET find='$find', replacement='$replacement', admin='$admin', pattern='$findpattern'");
        } elseif ($type == 2) {
            DB::query("INSERT INTO " . DB::table('badwords') . " SET find='$find', replacement='$replacement', admin='$admin', pattern='$findpattern'", 'SILENT');
        }
    }
    return DB::insert_id();
}

function find_pattern($find) {
    $find = preg_quote($find, "/'");
    $find = str_replace("\\", "\\\\", $find);
    $find = str_replace("'", "\\'", $find);
    return '/' . preg_replace("/\\\{(\d+)\\\}/", ".{0,\\1}", $find) . '/is';
}

function delete_badword($array) {
    $badwordids = implodeids($array);
    DB::query("DELETE FROM " . DB::table('badwords') . " WHERE id IN ($badwordids)");
    return DB::affected_rows();
}

function truncate_badword() {
    DB::query("TRUNCATE TABLE " . DB::table('badwords'));
}

function update_badword($find, $replacement, $id) {
    $pattern = find_pattern($find);
    DB::query("UPDATE " . DB::table('badwords') . " SET find='$find', replacement='$replacement', pattern='$pattern' WHERE id='$id'");
    return DB::affected_rows();
}

?>
