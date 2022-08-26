<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : paging.php    2012-2-26
 */
!defined('IN_PHPCOM') && exit('Access denied');

function paging($pagenow, $pagecount, $pagesize, $totalrec = 0, $pageurl = '', $pagenum = 7, $pagestats = FALSE, $pageinput = FALSE) {
    $pagenum = $pagenum ? $pagenum : 5;
    $total = lang('common', 'pagetotal');
    $pageback = lang('common', 'pageback');
    $pagenext = lang('common', 'pagenext');
    $inputcaption = lang('common', 'pageinput');
    $s = '';
    if ($pagestats) {
        $s = "<b>$total$totalrec/$pagesize</b>";
    }
    if ($pagenow == 1) {
        $s .= '<a href="javascript:void(0)" class="disable">' . $pageback . '</a>';
    } else {
        $s .= '<a href="' . str_replace('{%d}', $pagenow - 1, $pageurl) . '">' . $pageback . '</a>';
    }
    //如果有分页，开始计算起始和结束页
    if ($pagecount > 0) {
        $start = max(1, $pagenow - intval($pagenum / 2));
        $end = min($start + $pagenum - 1, $pagecount);
        $start = max(1, $end - $pagenum + 1);
        if ($start > 1) {
            $s .= '<a href="' . str_replace('{%d}', 1, $pageurl) . '" class="first">1...</a>';
        }
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $pagenow) {
                $s .= '<a href="javascript:void(0)" class="active">' . $i . '</a>';
            } else {
                $s .= '<a href="' . str_replace('{%d}', $i, $pageurl) . '">' . $i . '</a>';
            }
            if ($i >= $pagecount) break;
        }
        if ($end < $pagecount) {
            $s .= '<a href="' . str_replace('{%d}', $pagecount, $pageurl) . '" class="last">...' . $pagecount . '</a>';
        }
    }
    if ($pagenow >= $pagecount) {
        $s .= '<a href="javascript:void(0)" class="disable">' . $pagenext . '</a>';
    } else {
        $s .= '<a href="' . str_replace('{%d}', $pagenow + 1, $pageurl) . '">' . $pagenext . '</a>';
    }
    if ($pageinput) {
        $s .= "<span><input type=\"text\" class=\"pageinput\" title=\"$inputcaption\" size=\"3\" onkeydown=\"if (13==event.keyCode) document.location.href='" . str_replace('{%d}', "'+this.value+'", $pageurl) . "'\" value=\"$pagenow\" /></span>";
    }
    return $s;
}

?>
