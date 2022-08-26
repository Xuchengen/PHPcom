<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : perm.php    2012-2-7
 */
!defined('IN_ADMINCP') && exit('Access denied');
array_splice($menu['global'], 12, 0, array(
    array('menu_userorder', 'userorder')
));
unset($menu['global'][1]);
unset($menu['user'][8]);
array_splice($menu['user'], 3, 0, array(
    array('menu_members_edit', 'members_edit'),
    array('menu_members_group', 'members_group'),
    array('menu_members_credit', 'members_credit'),
    array('menu_members_delete', 'members_delete')
));
array_splice($menu['user'], 11, 0, array(
    array('menu_usergroup_edit', 'usergroup_edit'),
    array('menu_usergroup_system', 'usergroup_system'),
    array('menu_usergroup_special', 'usergroup_special')
));
array_splice($menu['topic'], -2, 0, array(
    array('menu_category', 'category'),
    array('menu_thread_delete', 'thread_delete'),
    array('menu_category_delete', 'category_delete'),
    array('menu_special_delete', 'special_delete'),
    array('menu_comment_delete', 'comment_delete'),
    array('menu_robots_delete', 'robots_delete'),
    array('menu_topical', 'topical'),
    array('menu_attachment', 'attachment')
));
unset($menu['tools'][1], $menu['tools'][3]);
?>
