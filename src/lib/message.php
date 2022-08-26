<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : message.php    2011-8-26 12:23:04
 */
!defined('IN_PHPCOM') && exit('Access denied');

function phpcom_showmessage($message, $url = '', $values = array(), $paramextra = array()) {
    phpcom::$G['messageargs'] = func_get_args();
    $instdir = phpcom::$G['instdir'];
    $charset = CHARSET;
    $vars = explode(':', $message);
    if (strpos('!', $message) === 0) {
        $showmsg = substr($message, 1);
    } else {
        if (count($vars) == 2) {
            $showmsg = lang('plugin/' . $vars[0], $vars[1], $values);
        } else {
            $showmsg = lang('message', $message, $values);
        }
        if ($showmsg == '') {
            $showmsg = $message;
        }
    }
    $jumplink = '';
    $url = $url === NULL ? 'back' : $url;
    if ($url) {
        if ($url == 'back') {
            $jumplink = '<p>' . lang('message', 'browser_redirect_tips', array('url' => 'javascript:history.go(-1)')) . '</p>';
            $jumplink .= '<script type="text/javascript" reload="1">setTimeout("javascript:history.go(-1)", 5000);</script>';
        } else {
            $jumplink = '<p>' . lang('message', 'browser_redirect_tips', array('url' => $url)) . '</p>';
            $jumplink .= '<script type="text/javascript" reload="1">setTimeout("window.location.href=\'' . str_replace("'", "\'", $url) . '\'", 5000);</script>';
        }
    }
    $params = array(
        'type' => 'alert',
        'header' => FALSE,
        'extra' => NULL,
        'message' => NULL,
        'timeout' => NULL,
        'location' => FALSE,
        'javascript' => NULL,
        'striptags' => TRUE,
        'showmsg' => TRUE,
        'showdialog' => FALSE,
        'handle' => FALSE,
    );
    $handlekey = '';
    if (is_array($paramextra)) {
        $params = array_merge($params, $paramextra);
    }
    $params['header'] = $url && $params['header'] ? TRUE : FALSE;
    if ($params['header']) {
        header("HTTP/1.1 301 Moved Permanently");
        header("location: " . str_replace('&amp;', '&', $url));
    }
    $msgtype = in_array($params['type'], array('error', 'alert')) ? $params['type'] : 'info';
    $title = lang('message', 'message_' . $msgtype);
    $showmsg .= $params['message'] ? '<br/>' . lang('message', $params['message']) : '';
    $showmsg .= $params['javascript'];
    if ($params['location'] && phpcom::$G['inajax'] && $url) {
        include loadlibfile('header', 'inc/ajax');
        if ($url == 'back') {
            echo '<script type="text/javascript" reload="1">javascript:history.go(-1);</script>';
        } else {
            echo '<script type="text/javascript" reload="1">window.location.href=\'' . str_replace("'", "\'", $url) . '\';</script>';
        }
        include loadlibfile('footer', 'inc/ajax');
        exit();
    }
    phpcom::$G['gp_handlekey'] = !empty(phpcom::$G['gp_handlekey']) && preg_match('/^\w+$/', phpcom::$G['gp_handlekey']) ? phpcom::$G['gp_handlekey'] : '';
    if (!empty(phpcom::$G['inajax'])) {
        $handlekey = phpcom::$G['gp_handlekey'] = !empty(phpcom::$G['gp_handlekey']) ? htmlspecialchars(phpcom::$G['gp_handlekey']) : '';
        $params['handle'] = TRUE;
    }
    if (!empty(phpcom::$G['inajax'])) {
        include loadlibfile('header', 'inc/ajax');
        if ($params['showdialog']) {
        	$func = '';
            if ($url && $url != 'back') {
                $func = ', function(){window.location.href=\'' . str_replace("'", "\'", $url) . '\';}';
            }
            echo "<script reload=\"1\" type=\"text/javascript\">";
            if ($handlekey) {
                //echo "hideWindow('$handlekey');";
            }
            $showmsg = str_replace("'", "\'", $showmsg);
            echo "showMessage('$showmsg', '$title', '{$params['type']}'$func);</script>";
        } else {
            echo $showmsg;
        }
        if (!$params['showdialog'] && $url && $url != 'back') {
            echo '<script type="text/javascript" reload="1">setTimeout("window.location.href=\'' . str_replace("'", "\'", $url) . '\';", 5000);</script>';
        }
        include loadlibfile('footer', 'inc/ajax');
    } else {
    	@ob_end_clean();
        echo <<<EOT
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=$charset" />
<title>$title -- Powered by phpcom.net</title>
<style rel="stylesheet">
body, div, li, td, a {color: #222222;font-size: 14px;font-family: tahoma, arial, 'courier new', verdana, sans-serif;line-height: 19px;}
a {color: #2c78c5;text-decoration: none;}
a:hover {color: red;text-decoration: none;}
.messagewrap{margin:90px auto 50px auto;width:560px;border-right:2px solid #eee;border-bottom:2px solid #eee;overflow:hidden;}
.messagewrap{ -moz-border-radius:0 3px 3px 3px; -webkit-border-radius: 0 3px 3px 3px; border-radius: 0 3px 3px 3px;}
.messagebox{border:1px solid #d6d6d6;background:#fdfdfd;padding:2px 5px;}
.messagebox{ -moz-border-radius: 3px; -webkit-border-radius: 3px; border-radius: 3px;}
.message-box{border:1px solid #ccc;border-top:1px solid #e7e7e7;background:#fefefe;padding:5px 15px;}
.messagebox h2{margin:auto;border-bottom:1px solid #d6d6d6;font:normal 14px/1.7 Verdana, Arial;font-weight:700;text-align:left;text-indent:15px;}
.messagebox table td{margin:auto;background:#fdfdfd;overflow:hidden;}
</style>
<link href="{$instdir}misc/css/common.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<div class="messagewrap">
	<div class="messagebox">
		<h2>$title</h2>
		<table width="95%" height="100" align="center" cellpadding="0" cellspacing="0" border="0" style="margin:auto;overflow:hidden">
			<tr>
				<td width="60" valign="top" style="padding-top:13px;text-align:center;"><div class="alert48"></div></td>
				<td valign="top" style="padding-top:1px;text-align:left;">
					<p style="margin-bottom:10px">$showmsg</p>
					$jumplink
				</td>
			</tr>
		</table>
	</div>
</div>
</body>
</html>
EOT;
    }
    phpcom_exit();
}

?>
