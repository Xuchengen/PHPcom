<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : announce.php    2011-4-4 18:11:23
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';
if (!checksubmit('btnsubmit')) {
    $current = '';
    if ($action == 'add' || $action == 'edit') {
        $current = 'menu_announce_' . $action;
    }
    admin_header('menu_announce', $current);
    $navarray = array(
        array(
            'title' => 'menu_announce',
            'url' => '?m=announce',
            'name' => 'first',
            'onclick' => ''
        ),
        array(
            'title' => 'menu_announce_add',
            'url' => '?action=add&m=announce',
            'name' => 'add',
            'onclick' => ''
        )
    );
    if ($action == 'add' || $action == 'edit') {
        //添加编辑
        $result = array('aid' => 0, 'title' => '', 'content' => '', 'author' => '', 'highlight' => 0);
        $announceid = isset(phpcom::$G['gp_announceid']) ? intval(phpcom::$G['gp_announceid']) : 0;
        if ($announceid) {
            $result = DB::fetch_first("SELECT aid,title,content,author,highlight FROM " . DB::table('announce') . " WHERE aid=$announceid");
        }
        $active = 'add';
        if ($action == 'edit') {
            $navarray[] = array(
                'title' => 'menu_announce_edit',
                'url' => '?action=edit&m=announce&announceid=' . $announceid,
                'name' => 'edit',
                'onclick' => ''
            );
            $navarray[] = array(
                'title' => 'menu_announce_browse',
                'url' => '?action=browse&m=announce&announceid=' . $announceid,
                'name' => 'browse',
                'onclick' => ''
            );
            $active = 'edit';
        }
        $adminhtml = phpcom_adminhtml::instance();
        $adminhtml->activetabs('tools');
        $adminhtml->navtabs($navarray, $active);
        $adminhtml->editor_scritp('announce');
        $adminhtml->form('m=announce', array(array('action', $active), array('announceid', $announceid)), 'onsubmit="return checkPost(this)"');
        $adminhtml->table_header('announce_' . $active, 2);
        $adminhtml->table_td(array(
            array('announce_title', '', 'width="20%"', '', TRUE),
            array('<input id="post_title" class="input" size="60" name="announce[title]" type="text" value="' . htmlcharsencode($result['title']) . '" /> ', TRUE, 'width="80%"')
        ));
        $adminhtml->table_td(array(
            array('highlight', '', '', '', TRUE),
            array($adminhtml->highlight_select($result['highlight']), TRUE)
        ));
        $adminhtml->editor_content('announce_content', $result['content'], 'announce[content]');
        $adminhtml->table_td(array(
            array('announce_author', '', '', '', TRUE),
            array('<input id="post_author" class="input" size="60" name="announce[author]" type="text" value="' . htmlcharsencode($result['author']) . '" />', TRUE)
        ));
        $btnsubmit = $adminhtml->submit_button();
        $adminhtml->table_td(array(
            array('', TRUE),
            array($btnsubmit, TRUE)
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } elseif ($action == 'del') {
        $announceid = intval(phpcom::$G['gp_announceid']);
        delete_announce($announceid);
    } elseif ($action == 'browse') {
        $announceid = intval(phpcom::$G['gp_announceid']);
        browse_announce($announceid, $navarray);
    } else {
        $adminhtml = phpcom_adminhtml::instance();
        $adminhtml->navtabs($navarray);
        $adminhtml->form('m=announce', array(array('action', 'del')), 'onkeydown="return formdown()"');
        $adminhtml->table_header();
        $adminhtml->table_th(array(
            array('title', 'width="60%"'),
            array('operation', 'width="15%"'),
            array('author', 'width="15%"'),
            array('date', 'width="10%"')
        ));
        $adminhtml->table_td(array(
            array(' ', TRUE, 'colspan="4" align="left" id="showpage"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        // 获取总记录数
        $totalrec = isset(phpcom::$G['gp_count']) ? intval(phpcom::$G['gp_count']) : 0;
        !$totalrec && $totalrec = DB::result_first("SELECT COUNT(*) FROM " . DB::table('announce'));
        $pagesize = (int) phpcom::$config['admincp']['pagesize'];  // 每页大小
        $pagecount = @ceil($totalrec / $pagesize);  //计算总页数
        $pagenow = max(1, min($pagecount, intval($page)));
        $pagestart = floor(($pagenow - 1) * $pagesize);
        $limit = buildlimit($pagesize, $pagestart);
        $table = DB::table('announce');
        $sql = "SELECT aid,title,dateline,author,highlight,hits FROM $table INNER JOIN (SELECT aid FROM $table WHERE 1=1 ORDER BY aid DESC $limit) as t using(aid)";
        $query = DB::query($sql);
        while ($row = DB::fetch_array($query)) {
            $announceid = $row['aid'];
            $edit = $adminhtml->edit_word('edit', 'action=edit&m=announce&announceid=' . $row['aid'], ' | ');
            $edit .= $adminhtml->del_word('delete', 'action=del&m=announce&announceid=' . $row['aid']);
            if ($row['highlight']) {
                $string = sprintf('%02d', $row['highlight']);
                $row['highlight'] = ' style="';
                $row['highlight'] .= $string[0] ? phpcom::$setting['fontvalue'][$string[0]] : '';
                $row['highlight'] .= $string[1] ? 'color: ' . phpcom::$setting['colorvalue'][$string[1]] : '';
                $row['highlight'] .= '"';
            } else {
                $row['highlight'] = '';
            }
            $adminhtml->table_td(array(
                array('<input type="checkbox" class="checkbox" name="checkboxid[]" value="' . $row['aid'] . '" /><label> <a class="lst" href="?action=browse&m=announce&announceid=' . $row['aid'] . '"' . $row['highlight'] . '>' . $row['title'] . '</a></label>', TRUE),
                array($edit, TRUE, 'align="center" noWrap="noWrap"'),
                array($row['author'], TRUE, 'align="center" noWrap="noWrap"'),
                array(fmdate($row['dateline'], 'd', 'd'), TRUE, 'align="center" noWrap="noWrap"')
            ));
        }
        $showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=announce") . '</var>';
        $adminhtml->table_td(array(
            array($adminhtml->checkall() . ' ' . $adminhtml->del_submit(), TRUE, 'colspan="4"')
        ));
        $adminhtml->table_td(array(
            array($showpage, TRUE, 'colspan="4" align="right" id="pagecode"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
        $adminhtml->showpagescript();
    }
    admin_footer();
} else {
    if ($action == 'del') {
        $checkboxid = phpcom::$G['gp_checkboxid'];
        $condition = 'aid in(' . implodeids($checkboxid) . ')';
        if ($checkboxid) {
            DB::delete('announce', $condition);
        }
        admin_succeed('delete_succeed', 'm=announce');
    } else {
        $announce = phpcom::$G['gp_announce'];
        $announceid = (int) phpcom::$G['gp_announceid'];
        if (!$announce['title']) {
            admin_message('announce_title');
        }
        if (!$announce['content']) {
            admin_message('announce_content');
        }
        if (!$announce['author']) {
            admin_message('announce_author');
        }
        $highlights = phpcom::$G['gp_highlights'];
        $announce['highlight'] = intval($highlights['font'] . $highlights['color']);
        if ($action == 'edit' && $announceid) {
            DB::update('announce', $announce, array('aid' => $announceid));
        } elseif ($action == 'add') {
            $announce['dateline'] = phpcom::$G['timestamp'];
            $announce['hits'] = 1;
            DB::insert('announce', $announce);
        }
        admin_succeed('announce_succeed', 'm=announce');
    }
}

function browse_announce($announceid, $navarray) {
    $result = array();
    if ($announceid) {
        $result = DB::fetch_first("SELECT aid,title,content,author,dateline,hits FROM " . DB::table('announce') . " WHERE aid=$announceid");
    }
    $adminhtml = phpcom_adminhtml::instance();
    $navarray[] = array(
        'title' => 'announce_edit',
        'url' => '?action=edit&m=announce&announceid=' . $announceid,
        'name' => 'edit',
        'onclick' => ''
    );
    $navarray[] = array(
        'title' => 'announce_browse',
        'url' => '?action=browse&m=announce&announceid=' . $announceid,
        'name' => 'browse',
        'onclick' => ''
    );

    $adminhtml->navtabs($navarray, 'browse');
    $adminhtml->table_header('announce_browse');
    $title = '<a href="?action=edit&m=announce&announceid=' . $result['aid'] . '"><font size="4">' . $result['title'] . '</font></a>';
    $adminhtml->table_td(array(
        array($title, TRUE, 'align="center"')
            ), '', FALSE, '', '', FALSE);
    $author = $result['author'];
    $dateline = adminlang('date') . ' ' . fmdate($result['dateline'], 'dt', 'u');
    $hits = adminlang('hits') . ' ' . $result['hits'];
    $adminhtml->table_td(array(
        array($dateline . ' ' . $hits, TRUE, 'align="center"')
            ), '', FALSE, '', '', FALSE);
    $content = bbcode::bbcode2html($result['content']);
    $adminhtml->table_td(array(
        array($content, TRUE, '', '', 'textcontent')
            ), '', FALSE, '', '', FALSE);
    $adminhtml->table_td(array(
        array($author, TRUE, 'align="right"')
            ), '', FALSE, '', '', FALSE);
    $adminhtml->table_end();
}

function delete_announce($announceid) {
    if ($announceid) {
        DB::delete('announce', array('aid' => $announceid));
    }
    admin_succeed('delete_succeed', 'm=announce');
}

?>
