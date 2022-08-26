<?php

/**
 *
 *  Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 *  Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 *  Description : This software is the proprietary information of phpcom.cn.
 *  This File   : index.php
 *
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'main';
$title = adminlang('admincp_title');
require loadlibfile('menu', 'inc/admincp');
$showmenubar = adminlang('showmenubar');
$hidemenubar = adminlang('hidemenubar');
$showallmenu = adminlang('showallmenu');
$admininfo = adminlang('index_show_admininfo', array('admin' => phpcom::$G['username'], 'group' => phpcom::$G['admingroup']['groupname']));
echo <<<EOT
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=$charset" />
<title>$title - Powered by PHPcom</title>
<link href="misc/admin/style.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="misc/js/common.js"></script>
<script type="text/javascript" src="misc/admin/js/index.js"></script>
</head>
<body>
<div id="admin_header">
	<div id="admin_logo"><img src="misc/admin/images/admin-logo.png" height="50" width="155"/></div>
	<div id="admin_topmenu">
		<div id=""></div>
		<div id="navbox">
			<div id="tabs">
				<ul id="topmenu">
EOT;
$i = 0;
foreach ($topmenu as $k => $v) {
    $first = $i ? '' : ' class="first"';
    $home = $i ? '' : ' class="home"';
    $url = strpos($v, 'http://') === FALSE ? ADMIN_SCRIPT . '?m=' . $v : $v;
    echo "<li$first><a target=\"mainFrame\" href=\"$url\" id=\"tabs_$k\" onclick=\"toggleMenuTabs(this, '$k')\" onmouseout=\"previewMenu()\" onmouseover=\"previewMenu('$k')\" hidefocus=\"true\"$home>" . adminlang('header_' . $k) . "</a></li>";
    $i++;
}
//echo '<li class="last"></li>';
echo <<<EOT
				</ul>
			</div>
		</div>
        <div id="admin_info">$admininfo</div>
		<div style="clear:both"></div>
	</div>
</div>
<div id="admin_menubar">
EOT;
foreach ($menu as $k => $v) {
    showmenubar($k, $v);
}
unset($menu);
$adminscript = ADMIN_SCRIPT;
if(empty(phpcom::$setting['upgrader']['close'])){
	echo "<script type=\"text/JavaScript\">ajaxget('$adminscript?m=ajax&action=notice','notice')</script>";
}
echo <<<EOT
<script type="text/JavaScript">initAdmincpMenus();</script>
</div>
<div id="admin_switchbar"></div>
<div id="admin_contont">
	<div id="x-content" style="height:100%;width:100%;">
		<div id="mainContainer" style="height:100%;float:left;width:100%;">
			<iframe src="?m=main" id="mainFrame" name="mainFrame" style="height:100%;visibility:inherit;width:100%;z-index:1;overflow:visible;" scrolling="yes" frameborder="no"></iframe>
		</div>
	</div>
</div>
<div id="admin_bottom">
    <div id="menu-switch"><div class="menutoggle-1" id="menutoggle">
        <a class="mt1" href="javascript:void(0)" onclick="toggleMenubar(1)" title="$hidemenubar"></a><a class="ms1" href="javascript:void(0)" onclick="admincpMenuScroll(1)"></a>
        <a class="sam"  href="javascript:void(0)" onclick="showAllMenus()" title="$showallmenu"></a>
        <a class="mt2" href="javascript:void(0)" onclick="toggleMenubar(2)" title="$showmenubar"></a><a class="ms2" href="javascript:void(0)" onclick="admincpMenuScroll(2)"></a>
    </div></div>
    <div id="taskbar">
	</div>
	<div style="clear:both"></div>
</div>
<div style="clear:both"></div>
<div style="display:none;" class="notice" id="notice"></div>
</body>
</html>
EOT;
?>
