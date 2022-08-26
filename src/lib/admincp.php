<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : admincp.php    2011-7-10 21:46:54
 */
!defined('IN_PHPCOM') && exit('Access denied');
@set_time_limit(0);

function isplugindir($dir) {
    return preg_match("/^[a-z]+[a-z0-9_]*\/$/", $dir);
}

function ispluginkey($key) {
    return preg_match("/^[a-z]+[a-z0-9_]*$/i", $key);
}

function adminlang($name, $vars = FALSE) {
    $result = '';
    $flag = FALSE;
    $vars['ADMINSCRIPT'] = ADMIN_SCRIPT;
    if (isset(phpcom::$G['lang']['admin'])) {
        $key = 'admin_' . phpcom::$G['lang']['admin'];
        if (!isset(phpcom::$G['lang'][$key])) {
            lang('admin/' . phpcom::$G['lang']['admin']);
        }
        if (isset(phpcom::$G['lang'][$key][$name])) {
            $result = phpcom::$G['lang'][$key][$name];
            $flag = TRUE;
        }
    }
    if (!$flag && !isset(phpcom::$G['lang']['admin_menu'])) {
        lang('admin/menu');
    }
    if (!$flag && isset(phpcom::$G['lang']['admin_menu'][$name])) {
        $result = phpcom::$G['lang']['admin_menu'][$name];
        $flag = TRUE;
    }
    if (!$flag && !isset(phpcom::$G['lang']['admincp'])) {
        lang('admincp');
    }
    if (!$flag && isset(phpcom::$G['lang']['admincp'][$name])) {
        $result = phpcom::$G['lang']['admincp'][$name];
        $flag = TRUE;
    }

    $result = $flag ? $result : $name;
    if ($vars) {
        $keys = $values = array();
        foreach ($vars as $k => $v) {
            $keys[] = '{' . $k . '}';
            $values[] = $v;
        }
        $result = str_replace($keys, $values, $result);
    }
    return $result;
}

function messagelang($name, $vars = '') {
    $result = '';
    $flag = FALSE;
    if (!isset(phpcom::$G['lang']['admin_message'])) {
        lang('admin/message');
    }
    if (isset(phpcom::$G['lang']['admin_message'][$name])) {
        $result = phpcom::$G['lang']['admin_message'][$name];
        $flag = TRUE;
    }
    if (!$flag && !isset(phpcom::$G['lang']['admincp'])) {
        lang('admincp');
    }
    if (!$flag && isset(phpcom::$G['lang']['admincp'][$name])) {
        $result = phpcom::$G['lang']['admincp'][$name];
        $flag = TRUE;
    }
    $result = $flag ? $result : $name;
    if ($vars) {
        $keys = $values = array();
        foreach ($vars as $k => $v) {
            $keys[] = '{' . $k . '}';
            $values[] = $v;
        }
        $result = str_replace($keys, $values, $result);
    }
    return $result;
}

function admin_error($message, $url = '', $vars = '', $extra = '') {
    return admin_message($message, $url, $vars, 'error', $extra);
}

function admin_succeed($message, $url = '', $vars = '', $extra = '') {
    return admin_message($message, $url, $vars, 'succeed', $extra);
}

function admin_alert($message, $url = '', $vars = '', $extra = '') {
    return admin_message($message, $url, $vars, 'alert', $extra);
}

function admin_showmessage($message, $vars = NULL, $args = array(), $extra = '', $halt = FALSE) {
    if (is_array($message)) {
        $msg = '';
        foreach ($message as $value) {
            $msg .= messagelang($value, $vars);
        }
    } else {
        $msg = messagelang($message, $vars);
    }
    $params = array(
        'url' => '',
        'form' => FALSE,
        'action' => '',
        'loading' => FALSE,
        'autosubmit' => FALSE,
        'submit' => FALSE,
        'cancel' => FALSE,
        'jump' => FALSE,
        'jumpurl' => '',
        'anchor' => FALSE,
        'timeout' => FALSE,
        'backurl' => 'history.go(-1)',
    );
    if (is_array($args)) {
        $params = array_merge($params, $args);
    }
    if ($params['submit'] || $params['autosubmit']) {
        $params['form'] = TRUE;
    }
    echo '<div class="info-mask"><table class="info-table" align="center" cellspacing="0" cellpadding="0"><tr>';
    echo '<td class="info-icons">&nbsp;</td>';
    echo '<td class="message">', "\r\n";
    if ($params['form']) {
        echo '<form action="', $params['action'], '" method="post" name="loading_form" id="loading_form">', "\r\n";
        echo '<input type="hidden" name="formtoken" value="' . formtoken() . '">';
    }
    if ($params['loading']) {
        echo '<p style=\"padding:5px;text-align:center;\"><img src="misc/admin/images/loading-bar.gif" /></p>';
    }
    echo '<p>', $msg, "</p>\r\n";
    echo $extra;
    if (!$params['submit'] && $params['form']) {
        echo '<p><a herf="#" onclick="$(\'loading_form\').submit();">', messagelang('message_redirect'), '</a></p>';
        $params['jump'] = FALSE;
    }
    if ($params['jump']) {
        echo '<p><a herf="', $params['jumpurl'], '">', messagelang('message_redirect'), '</a></p>';
    }
    if ($params['submit'] || $params['cancel']) {
        echo '<p class="btnmsg">';
        if ($params['submit']) {
            echo '<button type="submit" class="button" name="btnsubmit" value="yes">',adminlang('submit'),'</button> ';
        }
        if ($params['cancel']) {
            echo '<button type="button" class="button" onclick="', $params['backurl'], '">',adminlang('cancel'),'</button>';
        }
        echo '</p>';
    }
    if ($params['form']) {
        echo '</form>';
    }
    echo "</td>\r\n";
    echo '</tr></table></div><div style="clear:both"></div>', "\r\n";
    if ($params['autosubmit'] && $params['form']) {
        echo '<script type="text/JavaScript">setTimeout("$(\'loading_form\').submit();", 2000);</script>';
    }
    if ($halt) exit();
}

function admin_message($message, $url = '', $vars = '', $type = 'alert', $extra = '', $halt = TRUE) {
    $charset = CHARSET;
    if (isset(phpcom::$G['langvar']) && !$vars) {
        $vars = phpcom::$G['langvar'];
    }
    $vars['ADMINSCRIPT'] = ADMIN_SCRIPT;
    $msgtitle = messagelang($type);
    $message = messagelang($message, $vars);
    $btnok = messagelang('ok');
    $onclick = 'history.go(-1);';
    $redirect = '';
    if (!empty($url)) {
        $url = substr($url, 0, 5) == 'http:' ? $url : ADMIN_SCRIPT . '?' . $url;
        $onclick = "location.href='$url'";
        if (phpcom::$config['admincp']['autoback'] && phpcom::$config['admincp']['backtime']) {
            $milliseconds = max(2000, intval(phpcom::$config['admincp']['backtime']));
            $redirect = "<script type=\"text/JavaScript\">setTimeout(\"$onclick\", $milliseconds);</script>";
        }
    }
    $inajax = phpcom::$G['inajax'];
    if ($inajax) {
        header('Content-Type: text/xml;charset=' . CHARSET);
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Cache-Control: no-cache');
        header('Pragma: no-cache');
        echo '<?xml version="1.0" encoding="' . CHARSET . '"?><ajax><![CDATA[';
    }
    if (!defined('PHPCOM_ADMINCP_HEAD_OUTPUT') && !$inajax) {
        echo <<<EOT
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset={$charset}" />
<meta http-equiv="Content-Language" content="zh-CN" />
<title>{$msgtitle} - Powered by PHPcom</title>
<meta http-equiv="expires" content="0" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="pragma" content="no-cache" />
<link rel="stylesheet" type="text/css" href="misc/admin/admincp.css" />
<link rel="stylesheet" type="text/css" href="misc/css/common.css?v=201202" />
</head>
<body>
EOT;
    }
    echo <<<EOT
<div id="message_window" class="message-mask">
<table class="message-table" cellspacing="0" cellpadding="0">
<tr>
<td class="message-wrap">
<div class="message-main">
EOT;
    if ($type == 'alert' || $type == 'succeed' || $type == 'error' || $type == 'completed') {
        echo <<<EOT
	<dl>
		<dt class="$type-icons">&nbsp;</dt>
		<dd>
			<h3>$msgtitle</h3>
			<p>$message</p>
		</dd>
	</dl>
	<div class="message-button"><button id="message_submit_button" title="$btnok" onClick="$onclick">&nbsp;$btnok&nbsp;</button></div>
     $extra
	$redirect
EOT;
    } else {
        if ($type == 'formloading') {
            echo "<form method=\"post\" action=\"$url&submit=yes\" id=\"loadingform\">";
            echo '<input type="hidden" name="formtoken" value="' . formtoken() . '">';
            echo "<dl><dt class=\"info-icons\">&nbsp;</dt><dd>";
            echo "<div class=\"message-loading\" style=\"margin:5px auto;\"><p style=\"padding:5px 0;\">$message $extra</p>";
            echo '<p style=\"padding:5px 0;\"><img src="misc/admin/images/loading-bar.gif" /></p>';
            echo '<p style="padding:5px 0;"><a herf="javascript:void(0)" onclick="$(\'loadingform\').submit();">', messagelang('message_redirect'), "</a></p></div>";
            echo "</dd></dl></form>";
            echo '<script type="text/JavaScript">setTimeout("$(\'loadingform\').submit();", 2000);</script>';
        } elseif ($type == 'form') {
            echo "<form method=\"post\" action=\"$url\">";
            echo '<input type="hidden" name="formtoken" value="' . formtoken() . '">';
            echo "<dl><dt class=\"info-icons\">&nbsp;</dt><dd><h3>$msgtitle</h3><p>$message $extra</p></dd></dl>";
            echo "<div class=\"message-button\"><button type=\"submit\" name=\"submit\" value=\"yes\">&nbsp;$btnok&nbsp;</button>&nbsp;";
            echo "<script type=\"text/javascript\">" .
            "if(history.length > (phpcom.isIE ? 0 : 1)) document.write('<button type=\"button\" onClick=\"history.go(-1);\">" . adminlang('cancel') . "</button>');" .
            "</script>";
            echo "</div></form>";
        } else {
            echo "<dl><dt class=\"info-icons\">&nbsp;</dt><dd>";
            echo "<div class=\"message-loading\" style=\"margin:5px auto;\"><p style=\"padding:5px 0;\">$message</p>";
            echo '<p style=\"padding:5px 0;\"><img src="misc/admin/images/loading-bar.gif" /></p>';
            echo "<p style=\"padding:5px 0;\"><a herf=\"$url\">", messagelang('message_redirect'), "</a></p></div>";
            echo "</dd></dl>";
            echo "<script type=\"text/JavaScript\">setTimeout(\"toRedirect('$url');\", 2000);</script>";
        }
    }
    echo '</div></td></tr></table></div>';
    if ($inajax) {
        echo ']]></ajax>';
    } else {
        if ($halt || !defined('PHPCOM_ADMINCP_HEAD_OUTPUT')) {
            echo '</body></html>';
        }
    }
    if ($halt) exit();
}

function admin_header($title = '', $indextitle = '', $vars = FALSE) {
    if (!defined('PHPCOM_ADMINCP_HEAD_OUTPUT')) {
        define('PHPCOM_ADMINCP_HEAD_OUTPUT', TRUE);
    } else {
        return TRUE;
    }
    $charset = CHARSET;
    $admin_title = adminlang($title ? $title : 'admin_title', $vars);
    $admin_index = adminlang('admin_index');
    $admin_current = adminlang('admin_current');
    $refresh = adminlang('refresh');
    //$fullscreen = adminlang('fullscreen');
    $back = adminlang('back');
    $home = adminlang('home');
    $logout = adminlang('logout');
    $channelid = phpcom::$G['channelid'];
    $menutitle = rawurlencode($admin_title);
    if (empty($title)) {
        $current_title = '<span>' . $admin_title . '</span>';
    } else {
        $index_title = '<span class="separator"> &raquo; </span><span>' . $admin_title . '</span>';
        if ($indextitle) {
            $indextitle = adminlang($indextitle, $vars);
            global $module;
            $index_title = '<span class="separator"> &raquo; </span><span><a href="?m=' . $module . '&action=list&chanid=' . $channelid . '">' . $admin_title . '</a></span>';
            $index_title .= '<span class="separator"> &raquo; </span><span>' . $indextitle . '</span>';
            $menutitle = rawurldecode($indextitle);
        }
        $current_title = '<span><a href="?m=main">' . $admin_index . '</a></span>' . $index_title;
    }
    $url = $_SERVER["REQUEST_URI"];
    if ($url) {
        $url = substr($url, strpos($url, '?') + 1);
        if (strpos($url, '&m=')) {
            parse_str($url, $output);
            $url = 'm=' . $output['m'] . '&' . str_replace('&m=' . $output['m'], '', $url);
        }
        $url = rawurlencode($url);
    } else {
        $url = '';
    }
    $instdir = phpcom::$G['instdir'];
    echo <<<EOT
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset={$charset}" />
<meta http-equiv="Content-Language" content="zh-CN" />
<title>{$admin_title} - Powered by PHPcom</title>
<meta name="robots" content="noindex" />
<link rel="stylesheet" type="text/css" href="misc/admin/admincp.css?v=20131011" />
<link rel="stylesheet" type="text/css" href="misc/css/common.css" />
<script type="text/javascript" src="misc/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="misc/js/common.js?v=20131011"></script>
<script type="text/javascript" src="misc/admin/js/admincp.js?v=20131011"></script>
</head>
<body class="indexbody">
<table id="crumbnav" class="table1" cellspacing="1" cellpadding="3" align="center" border="0">
	<tr>
		<td class="tableline linetitle" width="*" align="left"><span class="nav-home">$admin_current</span><span class="separator"> &raquo; </span>$current_title <a href="?m=misc&action=custommenu&do=add&title=$menutitle&url=$url">[+]</a></td>
		<td class="tableline" width="320" align="right" id="showadmininfo"><div><a class="admin-back" title="$back" href="javascript:history.go(-1)" hidefocus="true"><em>$back</em></a> <a class="admin-refresh" onclick="toRefresh()" title="$refresh" href="javascript:void(0)" hidefocus="true"><em>$refresh</em></a>
<a class="admin-home" title="$home" href="$instdir" target="_blank" hidefocus="true"><em>$home</em></a> <a class="admin-logout" title="$logout" href="?action=logout&session=all" hidefocus="true"><em>$logout</em></a></div></td>
	</tr>
</table>
EOT;
}

function admin_footer() {
    $timer = number_format((phpcom::microtime() - phpcom::$G['starttime']), 6);
    $queries = DB::instance()->querycount;
    $version = phpcom::$setting['version'];
    //$timeoffset = phpcom::$setting['timeoffset'];
    //$offset = $timeoffset >= 0 ? ($timeoffset == 0 ? '' : '+' . $timeoffset) : $timeoffset;
    //$time = fmdate(TIMESTAMP, 'Y-n-j G:i:s');
    //$time_now = "GMT$offset, $time";
    $gzipcompress = '';
    if (phpcom::$G['gzipcompress']) {
        $gzipcompress = ', Gzip enabled';
    }
    $appmemory = '';
    if (phpcom::$G['memory']) {
        $appmemory = ', ' . ucwords(phpcom::$G['memory']) . ' On';
    }
    updatesession();
    $memoryusage = ', memory ' . formatbytes(memory_get_usage());
    $toyear = date('Y');
    echo <<<EOT
<div style="clear:both"></div>
<table align="center" id="bottomtable">
<tr><td align="center" class="copyright">
Powered by <a href="http://www.phpcom.net" target="_blank">PHPcom</a> $version Licensed &copy; 2010-$toyear <a href="http://www.phpcom.cn" target="_blank"><font face="Verdana, Arial, Helvetica, sans-serif"><strong>phpcom<font color="#cc0000">.cn</font></strong></font></a>. All Rights Reserved .
<br/>Processed in $timer(s), $queries queries$gzipcompress$appmemory$memoryusage
</td>
</tr>
</table>
<div style="clear:both"></div>
</body>
</html>
EOT;
}

function setconfigarray($array, $default = null) {
    if (is_array($default)) {
        foreach ($default as $k => $v) {
            if (!isset($array[$k])) {
                $array[$k] = $default[$k];
            } elseif (is_array($v)) {
                $array[$k] = setconfigarray($array[$k], $default[$k]);
            }
        }
    }
    return $array;
}

function exportconfigarray($array, $level = 0, $keyname = null, $varname = '$_config') {
    $result = null;
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            if ($level == 0) {
                $tokens = str_pad('  CONFIG ' . strtoupper($key) . '  ', 70, '-', STR_PAD_BOTH);
                $result .= "\r\n/* $tokens */\r\n";
            }
            if (is_array($value)) {
                $kname = $keyname . "['$key']";
                $result .= exportconfigarray($value, $level + 1, $kname, $varname);
            } else {
                $value = is_string($value) || strlen($value) > 12 || !preg_match("/^\-?[1-9]\d*$/", $value) ? '\'' . addcslashes($value, '\'\\') . '\'' : $value;
                $result .= $varname . $keyname . "['$key'] = $value;\r\n";
            }
        }
    }
    return $result;
}

/**
 * 分页函数
 * @param int $pagenow 当前页
 * @param int $pagecount 统计总页数
 * @param int $pagesize 每页数
 * @param int $totalrec 总记录数
 * @param string $pageurl 分页URL
 * @param int $pagenum 控制显示的页数
 * @return string 返回分页后的内容
 */
function showpage($pagenow, $pagecount, $pagesize, $totalrec = 0, $pageurl = '', $pagenum = 0, $pageinput = null) {
    $pageurl .= strpos($pageurl, '?') !== FALSE ? '&' : '?';
    $pageurl .= "count=$totalrec&";
    $pagenum = $pagenum ? $pagenum : intval(phpcom::$config['admincp']['pagenum']);
    $pagenum = $pagenum ? $pagenum : 10;
    $pageinput = $pageinput === null ? phpcom::$config['admincp']['pageinput'] : $pageinput;
    $pagestats = phpcom::$config['admincp']['pagestats'];
    $total = adminlang('pagetotal');
    $pageback = adminlang('pageback');
    $pagenext = adminlang('pagenext');
    $inputcaption = adminlang('pageinput');
    $s = '';
    if ($pagestats) {
        $s = "<b>$total$totalrec</b><b>$pagesize</b>";
    }
    if ($pagenow == 1) {
        $s .= '<kbd class="disable"><a href="javascript:void(0)">' . $pageback . '</a></kbd><code>';
    } else {
        $s .= '<kbd><a href="' . $pageurl . 'page=' . ($pagenow - 1) . '">' . $pageback . '</a></kbd><code>';
    }
    //如果有分页，开始计算起始和结束页
    if ($pagecount > 0) {
        $start = max(1, $pagenow - intval($pagenum / 2));
        $end = min($start + $pagenum - 1, $pagecount);
        $start = max(1, $end - $pagenum + 1);
        if ($start > 1) {
            $s .= '<a href="' . $pageurl . 'page=1" class="first">1...</a>';
        }
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $pagenow) {
                $s .= '<a href="javascript:void(0)" class="active">' . $i . '</a>';
            } else {
                $s .= '<a href="' . $pageurl . 'page=' . $i . '">' . $i . '</a>';
            }
            if ($i >= $pagecount) break;
        }
        if ($end < $pagecount) {
            $s .= '<a href="' . $pageurl . 'page=' . $pagecount . '" class="last">...' . $pagecount . '</a>';
        }
    }
    if ($pagenow >= $pagecount) {
        $s .= '</code><dfn class="disable"><a href="javascript:void(0)">' . $pagenext . '</a></dfn>';
    } else {
        $s .= '</code><dfn><a href="' . $pageurl . 'page=' . ($pagenow + 1) . '">' . $pagenext . '</a></dfn>';
    }
    if ($pageinput) {
        $s .= "<span class=\"pageinput\"><input type=\"text\" title=\"$inputcaption\" size=\"3\" onkeydown=\"if (13==event.keyCode) document.location.href='{$pageurl}page='+this.value\" value=\"$pagenow\" /><span>";
    }
    return $s;
}

function select_usergrouplevel($name = 'groupid', $value = 0, $width = 322){
	static $usergroups = NULL;
	if ($usergroups === NULL) {
		$sql = "SELECT groupid,type,grouptitle FROM " . DB::table('usergroup') . " WHERE type IN('system', 'special') ORDER BY groupid";
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			if ($row['type'] == 'member') {
				$usergroups['member'][$row['groupid']] = $row;
			} elseif ($row['type'] == 'special') {
				$usergroups['special'][$row['groupid']] = $row;
			} else {
				$usergroups['system'][$row['groupid']] = $row;
			}
		}
	}
	$s = "<select class=\"select\" name=\"$name\" size=\"1\" style=\"width:{$width}px\">";
	$s .= '<option value="0">' . adminlang('noselect') . '</option>';
	$s .= '<optgroup label="' . adminlang('systemgroup') . '">';
	$grouparray['optgroup1'] = adminlang('systemgroup');
	foreach ($usergroups['system'] as $key => $row) {
		$s .= '<option value="' . $row['groupid'] . '"' . ($row['groupid'] == $value ? ' SELECTED' : '') . '>' . $row['grouptitle'] . '</option>';
	}
	$s .= "</optgroup>\r\n";
	$s .= '<optgroup label="' . adminlang('specialgroup') . '">';
	$grouparray['optgroup2'] = adminlang('specialgroup');
	foreach ($usergroups['special'] as $key => $row) {
		$s .= '<option value="' . $row['groupid'] . '"' . ($row['groupid'] == $value ? ' SELECTED' : '') . '>' . $row['grouptitle'] . '</option>';
	}
	$s .= "</optgroup>\r\n";
	$s .= '<optgroup label="' . adminlang('membergroup') . '">';
	$s .= '<option value="10"' . (10 == $value ? ' SELECTED' : '') . '>' . adminlang('defaultmember') . '</option>';
	$grouparray['/optgroup3'] = '';
	$s .= "</optgroup>\r\n";
	$s .= "</select>\r\n";
	return $s;
}

function select_usergroup($name = 'targetgroup[]', $value = '', $multiple = TRUE, $sysgroup = TRUE, $size = 12, $width = 220, $retarray = FALSE, $maxcredit = FALSE) {
    static $usergroups = NULL;
    if ($usergroups === NULL) {
        $sql = "SELECT groupid,type,grouptitle,mincredits,maxcredits FROM " . DB::table('usergroup') . " WHERE 1 ORDER BY groupid";
        $query = DB::query($sql);
        while ($row = DB::fetch_array($query)) {
            if ($row['type'] == 'member') {
                $usergroups['member'][$row['groupid']] = $row;
            } elseif ($row['type'] == 'special') {
                $usergroups['special'][$row['groupid']] = $row;
            } else {
                $usergroups['system'][$row['groupid']] = $row;
            }
        }
        unset($row);
    }
    $name = $name ? $name : 'targetgroup[]';
    $s_multiple = $multiple ? ' multiple="multiple"' : '';
    $width = $width ? $width : 220;
    $grouparray = array();
    $s = "<select name=\"$name\" size=\"$size\"$s_multiple style=\"width:{$width}px\">";
    if (!$multiple && !$value) {
        $s .= '<option value="0">' . adminlang('noselect') . '</option>';
    }
    if ($sysgroup) {
        $s .= '<optgroup label="' . adminlang('systemgroup') . '">';
        $grouparray['optgroup1'] = adminlang('systemgroup');
        foreach ($usergroups['system'] as $key => $row) {
            $grouparray[$row['groupid']] = $row['grouptitle'];
            $s .= '<option value="' . $row['groupid'] . '"' . ($row['groupid'] == $value ? ' SELECTED' : '') . '>' . $row['grouptitle'] . '</option>';
        }
        $grouparray['/optgroup1'] = '';
        $s .= "</optgroup>\r\n";
    }
    $s .= '<optgroup label="' . adminlang('specialgroup') . '">';
    $grouparray['optgroup2'] = adminlang('specialgroup');
    foreach ($usergroups['special'] as $key => $row) {
        $grouparray[$row['groupid']] = $row['grouptitle'];
        $s .= '<option value="' . $row['groupid'] . '"' . ($row['groupid'] == $value ? ' SELECTED' : '') . '>' . $row['grouptitle'] . '</option>';
    }
    $grouparray['/optgroup2'] = '';
    $s .= "</optgroup>\r\n";
    $s .= '<optgroup label="' . adminlang('membergroup') . '">';
    $grouparray['optgroup3'] = adminlang('membergroup');
    foreach ($usergroups['member'] as $key => $row) {
        if ($maxcredit === FALSE) {
            $grouparray[$row['groupid']] = $row['grouptitle'];
            $s .= '<option value="' . $row['groupid'] . '"' . ($row['groupid'] == $value ? ' SELECTED' : '') . '>' . $row['grouptitle'] . '</option>';
        } else {
            if ($maxcredit >= $row['mincredits'] && $maxcredit < $row['maxcredits']) $grouparray[$row['groupid']] = $row['grouptitle'];
            $s .= '<option value="' . $row['groupid'] . '"' . ($row['groupid'] == $value ? ' SELECTED' : '') . '>' . $row['grouptitle'] . '</option>';
        }
    }
    $grouparray['/optgroup3'] = '';
    $s .= "</optgroup>\r\n";
    $s .= "</select>\r\n";
    if ($retarray) {
        return $grouparray;
    } else {
        return $s;
    }
}

function usergroup_checkbox($groupids = '', $credits = 0, $israte = false){
	$gidarray = $groupids ? explode(',', $groupids) : array();
	$s = '<ul onmouseover="alterStyle(this);" class="checkboxstyle">';
	foreach(phpcom::$G['usergroup'] as $groupid => $group){
		if($groupid == 1) continue;
		if(strcmp($group['type'], 'system') == 0 || strcmp($group['type'], 'special') == 0 || $groupid == 10){
			$checked = in_array($groupid, $gidarray) ? ' class="checked"' : null;
			$s .= '<li'.$checked.'><input class="checkbox" type="checkbox" name="usergroupids[]" value="'.$groupid.'"';
			$s .= $checked ? ' checked="checked"' : '';
			$s .= " /><label>&nbsp;{$group['grouptitle']}</label></li>";
		}
	}
	$s .= '<li>&nbsp;<strong>'.adminlang('need_credits') . ':</strong> <input type="text" class="input t5" name="threadfields[credits]" value="' . intval($credits) . '" /></li>';
	$s .= $israte ? '<li><input class="checkbox" type="checkbox" name="randisrate" value="1"><label>&nbsp;' . adminlang('add_rand_rate') . '</label></li>' : '';
	$s .= "</ul>\r\n";
	return $s;
}

function checkisfounder($user = '') {
    $user = empty($user) ? phpcom::$G('member') : $user;
    return $GLOBALS['admincp']->checkfounder($user);
}

function generate_customizemenu($category = 1) {
    $uid = phpcom::$G['uid'];
    $customizemenus = array();
    $query = DB::query("SELECT title, url, category FROM " . DB::table('adminmenu') . " WHERE uid='$uid' AND category='$category' ORDER BY sortord");
    while ($row = DB::fetch_array($query)) {
        $url = rawurldecode(checkcustomizeurl($row['url']));
        $row['url'] = substr($url, strlen(ADMIN_SCRIPT) + 3);
        $customizemenus[] = array($row['title'], $row['url']);
    }
    return $customizemenus;
}

function update_customizemenu($key = 'index') {
    @include loadlibfile('menu', 'inc/admincp');
    $key = $key ? $key : 'index';
    $s = showmenubar('index', $menu[$key], 1);
    return '<script type="text/JavaScript">parent.$(\'menu_' . $key . '\').innerHTML = \'' . str_replace("'", "\'", $s) . '\';parent.initAdmincpMenus();</script>';
}

function checkcustomizeurl($url) {
    if ($url) {
        $url = rawurldecode($url);
        if ($url{0} == '?') {
            $url = ADMIN_SCRIPT . $url;
        } else {
            if (strpos($url, '?')) {
                $url = ADMIN_SCRIPT . substr($url, strpos($url, '?'));
            } else {
                $url = ADMIN_SCRIPT . "?$url";
            }
        }
    }
    return $url;
}

function generate_channelmenu() {
    $channelmenus = array();
    $sql = "SELECT channelid, modules, subname, closed FROM " . DB::table('channel') . " WHERE type IN('system','expand') AND modules IN('article', 'soft', 'special', 'video', 'photo') ORDER BY sortord";
    $query = DB::query($sql);
    while ($row = DB::fetch_array($query)) {
        if (!$row['closed']) {
            $channelmenus[] = array($row['subname'] . adminlang('admin'), $row['modules'], $row['channelid'], 'add');
            //if ($row['modules'] == 'soft') {
            //    $channelmenus[] = array('menu_downserver', 'downserver', $row['channelid']);
            //}
        }
    }
    return $channelmenus;
}

function showmenubar($key, $menus, $returned = 0) {
    $s = "<dt class=\"menu_title\" id=\"menutitle_$key\" onclick=\"showsubmenu('$key')\">" . adminlang('header_' . $key) . "</dt><dd id=\"submenu_$key\">";
    if (is_array($menus)) {
        $s .= '<ul>';
        foreach ($menus as $menu) {
            if (isset($menu[1]) && $menu[0] && $menu[1]) {
                if (isset($menu[2]) && $menu[2]) {
                    $channeladd = '&chanid=' . $menu[2];
                } else {
                    $channeladd = '';
                }
                list($m, $a) = explode('_', $menu[1] . '_');
                $menu[1] = $m . ($a ? '&action=' . $a : '') . $channeladd;

                $url = substr($menu[1], 0, 4) == 'http' ? $menu[1] : ADMIN_SCRIPT . '?m=' . $menu[1];
                if (isset($menu[3]) && $menu[3] == 'add') {
                    $addurl = ADMIN_SCRIPT . "?m=$m&action=add$channeladd";
                    $add = " <span class=\"x\"><a target=\"mainFrame\" href=\"$addurl\" hidefocus=\"true\">" . adminlang('add') . "</a></span>";
                } else {
                    $add = '';
                }
                $s .= "<li><a style=\"display:block\" target=\"mainFrame\" href=\"$url\" hidefocus=\"true\">" . adminlang($menu[0]) . "</a>$add</li>";
            }
        }
        $s .= '</ul>';
    }
    $s .= '</dd>';
    if (!$returned) {
        echo '<dl id="menu_' . $key . '" style="display: ">' . $s . '</dl>';
    } else {
        return $s;
    }
}

function get_rootid($catid, $chanid = 0){
	$condition = empty($chanid) ? '' : "chanid='$chanid' AND ";
	return (int)DB::result_first("SELECT rootid FROM " . DB::table('category') . " WHERE $condition catid='$catid'");
}

function category_select_option($chanid, $parentid = 0) {
	phpcom_cache::load('category_' . $chanid);
	$data = phpcom::$G['cache']['category_' . $chanid];
	$option = '';
	if(isset($data[0])){
		foreach ($data[0] as $key => $row) {
			$option .= '<option value="' . $row['catid'] . '"';
			$option .= ( $row['catid'] == $parentid) ? ' SELECTED' : '';
			$option .= ">{$row['catname']}</option>";
			if (isset($data[$row['catid']])){
				foreach ($data[$row['catid']] as $key => $row) {
					$option .= '<option value="' . $row['catid'] . '"';
					$option .= ( $row['catid'] == $parentid) ? ' SELECTED' : '';
					$option .= "> &nbsp; &nbsp;|- {$row['catname']}</option>";
					if(isset($data[$row['catid']])){
						foreach ($data[$row['catid']] as $key => $row) {
							$option .= '<option value="' . $row['catid'] . '"';
							$option .= ( $row['catid'] == $parentid) ? ' SELECTED' : '';
							$option .= "> &nbsp; &nbsp; &nbsp; &nbsp;|- {$row['catname']}</option>";
						}
					}
				}
			}
		}
	}
	return $option;
}

function threadmove($tid, $catid, $rootid, $chanid = 0, $module = 'article'){
	$data = array('catid' => $catid);
	if($thread = DB::fetch_first("SELECT tid, chanid, rootid, catid FROM " . DB::table('threads') . " WHERE tid='$tid'")){
		if($thread['catid'] == $catid) return false;
		if($thread['rootid'] != $rootid){
			$data['rootid'] = $rootid;
			DB::delete('thread_class_data', "tid='$tid'");
		}
		if($thread['chanid'] != $chanid){
			$data['chanid'] = $chanid;
			DB::update('tagdata', array('chanid' => $chanid), "tid='$tid'");
			DB::update('attachment', array('chanid' => $chanid), "tid='$tid'");
			DB::update("attachment_{$module}", array('chanid' => $chanid), "tid='$tid'");
			DB::update('persondata', array('chanid' => $chanid), "tid='$tid'");
		}
		DB::update('threads', $data, "tid='$tid'");
		DB::update("{$module}_thread", $data, "tid='$tid'");
		DB::update('topic_data', array('catid' => $catid), "tid='$tid'");
		return true;
	}
	return false;
}

function update_category_counts($chanid, $catid = 0, $rootid = 0) {
	$catids = $rootids = $parentids = array();
	if($rootid){
		$condition = "WHERE rootid='$rootid'";
	}else{
		$condition = $catid ? "WHERE catid='$catid'" : '';
	}
	$query = DB::query("SELECT catid, rootid, depth, parentid FROM " . DB::table('category') . " $condition");
	while ($row = DB::fetch_array($query)) {
		if($row['depth'] > 0){
			$catids[$row['catid']] = $row['rootid'];
		}else{
			$rootids[$row['catid']] = $row['rootid'];
		}
	}
	
	foreach($catids as $catid => $rootid){
		$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " WHERE status='1' AND catid='$catid'");
		DB::exec("UPDATE " . DB::table('category') . " SET counts='$count' WHERE catid='$catid'");
	}
	foreach($rootids as $catid => $rootid){
		$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " WHERE status='1' AND rootid='$catid'");
		DB::exec("UPDATE " . DB::table('category') . " SET counts='$count' WHERE catid='$catid'");
	}
	phpcom_cache::updater('category', $chanid);
	return true;
}

function get_specialclass_radio($tid, $specid = 0) {
	if($tid <= 0 && $specid <= 0) return null;
	$classid = 0;
	$query = DB::query("SELECT tid, specid, classid FROM " . DB::table('special_data') . " WHERE tid='$tid' LIMIT 1");
	if($data = DB::fetch_array($query)){
		if($specid <= 0){
			$specid = $data['specid'];
		}
		$classid = $data['classid'];
	}
	$existclass = false;
	if(!$specid && !$classid) return null;
	$i = 0;
	$s = "<input type=\"hidden\" name=\"special[specid]\" value=\"$specid\" />";
	//$s .= "<input type=\"hidden\" name=\"oldclassid\" value=\"$classid\" />";
	$s .= '<ul onmouseover="alterStyle(this);" class="checkboxstyle">';
	$query = DB::query("SELECT classid, tid, name FROM " . DB::table('special_class') . " WHERE tid='$specid' ORDER BY classid");
	while ($row = DB::fetch_array($query)) {
		$existclass = true;
		$s .= '<li';
		if($classid == 0 && $i == 0){
			$s .= ' class="checked"><input checked="checked" ';
		}else{
			$s .= ($classid == $row['classid']) ? ' class="checked"><input checked="checked"' : '><input ';
		}
		$s .= ' class="checkbox" type="radio" name="special[classid]" value="' . $row['classid'] . '" />';
		$s .= "<label>&nbsp;{$row['name']}</label></li>";
		$i++;
	}
	if(!$existclass){
		$s .= '<li class="checked"><input checked="checked" class="checkbox" type="radio" name="special[classid]" value="0" />';
		$s .= "<label>&nbsp;" . adminlang('normal_special') . "</label></li>";
	}
	$s .= '<li><input class="checkbox" type="radio" name="special[classid]" value="-1" />';
	$s .= "<label>&nbsp;" . adminlang('cancel_special') . "</label></li>";
	$s .= "</ul>";
	return $s;
}
?>
