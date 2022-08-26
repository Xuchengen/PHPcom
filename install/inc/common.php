<?php
/**
 * Copyright (c) 2010-2012 phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPMain.
 * This File   : common.php  2012-4-6
 */
!defined('IN_PHPCOM') && exit('Access denied');
$func_items = array('mysql_connect', 'mysqli_connect', 'iconv', 'file_get_contents', 'xml_parser_create', 'mb_strlen');

function show_message($message, $errmsg = '', $showerrmsg = TRUE){
	global $lang, $step;
	show_header();
	show_nav();
	$title = $comment = '';
	if(isset($lang[$message])){
		$title = $lang[$message];
	}
	if(isset($lang[$message.'_comment'])){
		$comment = '<li>'.$lang[$message.'_comment'].'</li>';
	}
	if($errmsg && !empty($errmsg)){
		foreach ((array)$errmsg as $k => $v) {
			$comment .= '<li class="red">'.$lang[$v].'</li>';
		}
	}
	$s = "<div class=\"msg\"><h2>$title</h2><ol>$comment</ol><br/>";
	if($showerrmsg){
		$s .= '<span class="red">'.$lang['error_msg'].'</span>';
	}
	$s .= '</div>';
	show_body($s, 1);
	show_button(1);
	show_footer();
	exit(0);
}

function show_error($message){
	
}

if(!function_exists('file_put_contents')) {
	function file_put_contents($filename, $s) {
		$fp = @fopen($filename, 'w');
		@fwrite($fp, $s);
		@fclose($fp);
		return TRUE;
	}
}

function executesql($sql, $dbcharset = 'gbk', $dbengine = 'MyISAM') {
	global $lang, $tablepre, $db;

	if(!isset($sql) || empty($sql)) return;

	$sql = str_replace("\r", "\n", str_replace(' '.DEFAULT_TABLEPRE, ' '.$tablepre, $sql));
	$sql = str_replace("\r", "\n", str_replace(' `'.DEFAULT_TABLEPRE, ' `'.$tablepre, $sql));
	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$ret[$num] = '';
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		if($query = trim($query)){
			if(substr($query, 0, 12) == 'CREATE TABLE') {
				$name = preg_replace("/CREATE TABLE ([a-z0-9_`]+) .*/is", "\\1", $query);
				$name = str_replace('`', '', $name);
				showjsmessage($lang['create_table'].' '.$name.' ... '.$lang['succeed']);
				$db->query(createtable($query, $dbcharset, $dbengine));
			} else {
				$db->query($query);
			}

		}
	}
}

function createtable($sql, $dbcharset = 'gbk', $dbengine = 'MyISAM') {
	$type = preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql);
	//$type = in_array($type, array('MYISAM', 'HEAP', 'MEMORY', 'INNODB')) ? $type : 'MYISAM';
	$type = stricmp($type, array('MyISAM', 'InnoDB', 'Aria', 'MEMORY', 'HEAP'), true, $dbengine);
	if(stricmp($type, array('MyISAM', 'InnoDB', 'Aria'))){
		$type = $dbengine;
	}
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	" ENGINE=$type DEFAULT CHARSET=$dbcharset";
}

function show_header(){
	global $step, $lang;
	static $charset;
	if($charset !== NULL) return;
	$charset = CHARSET;
	$install_title = $lang['install_title'];
	$install_version = $lang['install_version'];
	$install_logo = $lang['install_logo'];
	echo <<<EOT
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=$charset" />
<title>$install_title</title>
<meta content="phpcom.net" name="Copyright" />
<style type="text/css">
html{color:#444;background:#f8fbff;}
body{font:13px/1.231 Verdana,Arial,tahoma,sans-serif;*font-size:small;*font:x-small;margin:0 auto;padding:0;}
table{border-collapse:collapse;border-spacing:0;}
fieldset,img{border:0;}
caption,th{text-align:left;}
h1,h2,h3,h4,h5,h6{font-size:100%;font-weight:normal;margin:0;padding:0;}
q:before,q:after{content:'';}
input,button,textarea,select,optgroup,option{font-family:inherit;font-size:inherit;font-style:inherit;font-weight:inherit;}
input,button,textarea,select{*font-size:100%;}
select,input,button,textarea,button{font:99% Verdana,Arial,helvetica,clean,sans-serif;}
table{font-size:inherit;font:100%;}
a{text-decoration:none;color:#178eb9;font-size:12px;}
a:hover{text-decoration:underline;}
.radic{font-size:12px;color:#0cb1fe;font-weight:700;font-family:Verdana,tahoma,Arial;}
.times{font-size:12px;color:#ff6600;font-weight:700;font-family:Verdana,tahoma,Arial;}
.red{color:#ff3300;}
.fb{font-weight:700;}
#header{margin:0 auto;background:#f3f9fd;border-bottom:1px solid #fdfdfd;overflow:hidden;}
#header{background:-moz-linear-gradient(top, #fefefe, #efefef 100%);
	background:-moz-linear-gradient(top, #f3f9fd, #e3e9ed 100%);
	background:-o-linear-gradient(top, #f3f9fd, #e3e9ed 100%);
	background:-webkit-gradient(linear, left top, left 100%, from(#f3f9fd), to(#e3e9ed));
	background:-ms-linear-gradient(top, #f3f9fd, #e3e9ed 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#f3f9fd,endColorstr=#e3e9ed,gradientType=0);}
.header{height:55px;width:910px;margin:0 auto;overflow:hidden;}
.header h1 {font:normal 22px/1 Verdana, Arial;font-weight:700;color:#209fd2;padding-top:15px;}
.header h1 span{color:#ff9000;}
.header h1 em{color:#007EB1;font-weight:400;}
.header p{text-align:right;padding:0;margin:0;font-size:12px;font-family:Verdana,Arial,tahoma,sans-serif;color:#777;}
#nav{padding:10px;border-top:1px solid #b5c5d5;}
.stepbar{width:912px;margin:0 auto;display:;}
	.stepbar ul{height:31px;padding:0;margin:0;}
	.stepbar li{list-style:none;float:left;height:31px;width:228px;line-height:31px;text-align:center;font-weight:700;}
	.stepbar li a{line-height:31px;height:31px;display:block;text-decoration:none;cursor:pointer;filter:Alpha(Opacity=60);Opacity:0.6;}
	.stepbar li a:hover{filter:Alpha(Opacity=100);Opacity:1;}
	.stepbar #step_1 a{background-image:url(images/step_1.png);}
	.stepbar #step_2 a, .stepbar #step_3 a{background-image:url(images/step_2.png);}
	.stepbar #step_4 a{background-image:url(images/step_3.png);}
	.stepbar .current a{background-position:left bottom;color:#fff;filter:Alpha(Opacity=80);Opacity:0.8;}
	.stepbar .normal a{color:#555;background-position:left top;}
	.stepbar .past a{color:#fff;background-position: left bottom;}

#wrap{margin:0 auto;width:900px;border:2px solid #efefef;background:#cbe8f8;padding:5px;box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);}
.wrap{border:1px solid #8cd2f2;background:#fff url(images/foot_bg.jpg) no-repeat bottom;}

#content{overflow:hidden;}
#sbtn{height:82px;text-align:center;overflow:hidden;}
#sbtn button{margin:0 5px;}
button{line-height:1.5em;padding:0 8px;}
#footer{line-height:1.8em;}
.powered{text-align:right;padding-right:10px;font-size:11px;color:#808080;}

.msg{padding-top:20px;}
.msg h2{font-weight:700;}
.contentX {float:left; padding:80px 40px;}
.contentX a{display:block;filter:Alpha(Opacity=60);Opacity:0.6;}
.contentX a:hover{filter:Alpha(Opacity=100);Opacity:1;}
.contentY {float:left;width:670px;padding:10px;}
.licenseblock{ margin-bottom:15px; padding:8px; height:380px; border:1px solid #bee8fe; background:#fff; overflow:scroll; overflow-x:hidden; }
.license{color:#777;}
	.license h1{ padding-bottom:10px; font-size:14px; text-align:center; font-weight:700;}
	.license h3{ margin:0; color:#666; font-weight:700;}
	.license p{ line-height:150%; margin:10px 0; text-indent:25px; }
	.license li{ line-height:150%; margin:5px 0; }
#notice {border:1px solid #bee8fe;width:98%;padding:5px;height:300px;text-align:left;overflow:scroll;overflow-x:hidden;}

.tb{width:98%;border:1px solid #bee8fe;border-bottom:2px solid #00AAFF;margin-bottom:10px;}
.tb caption{color:#fff;line-height:1.5em;padding:3px;font-weight:700;
	background:-moz-linear-gradient(top, #6DC4F1, #2DA5F2 100%);
	background:-o-linear-gradient(top, #6DC4F1, #2DA5F2 100%);
	background:-webkit-gradient(linear, left top, left 100%, from(#6DC4F1), to(#2DA5F2));
	background:-ms-linear-gradient(top, #6DC4F1, #2DA5F2 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#6DC4F1,endColorstr=#2DA5F2,gradientType=0);
}
.tb th{color:#3A84C9;padding:3px;line-height:1.5em;border-bottom:1px solid #7BC4E9;font-weight:400;
	background:-moz-linear-gradient(top, #6DC4F1, #2DA5F2 100%);
	background:-moz-linear-gradient(top, #fbfefe, #D9F5FD 100%);
	background:-o-linear-gradient(top, #fbfefe, #D9F5FD 100%);
	background:-webkit-gradient(linear, left top, left 100%, from(#fbfefe), to(#D9F5FD));
	background:-ms-linear-gradient(top, #fbfefe, #D9F5FD 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=#fbfefe,endColorstr=#D9F5FD,gradientType=0);
}
.tb td{border-bottom:1px solid #f1f1f1;padding:3px;line-height:1.5em;}
.tb .tit{width:100px;white-space:nowrap;}

</style>
<script type="text/javascript">
function showmessage(message) {
	document.getElementById('notice').innerHTML += message + '<br />';
	document.getElementById('notice').scrollTop = 100000000;
}
</script>
</head>
<body>
	<div id="header">
		<div class="header">
			<h1>$install_logo</h1>
			<p>$install_version</p></div>
	</div>
EOT;

}

function show_nav(){
	global $step, $lang;
	static $shownav;
	if($shownav === NULL){
		echo '<div id="nav"><div class="stepbar">';
		if($step){
			echo '<ul>';
			for ($i = 1; $i <= 4; ++$i){
				$clsname = 'normal';
				$href = 'javascript:void(0)';
				if($step == $i){
					$clsname = 'current';
				}elseif($i < $step){
					$clsname = 'past';
					$href = "?step=$i";
				}
				echo "<li id=\"step_{$i}\" class=\"$clsname\"><a href=\"$href\">".$lang['step_title_'.$i]."</a></li>";
			}
			echo '</ul>';
		}
		echo "</div></div>\r\n";
	}
	$shownav = TRUE;
}

function show_body($content, $msgno = 0){
	global $step, $lang;
	$nstep = $step + 1;
	$form = '';
	if($msgno){
		$src = "images/icon_info.jpg";
	}else{
		$src = "images/icon_0$step.jpg";
		$form = '<form action="index.php?step='.$nstep.'" method="post">';
	}
	echo <<<EOT
	<div id="wrap">
		<div class="wrap">
			$form
			<div id="content">
				<div class="contentX"><a href="javascript:void(0)"><img src="$src"/></a></div>
				<div class="contentY">
				$content
				</div>
			</div>
EOT;
}

function show_button($msgno = 0){
	static $showbtn;
	global $step, $lang;
	if($msgno){
		echo '<div id="sbtn">';
		echo "<button type=\"button\" onclick=\"history.back();\"><span>$lang[click_to_back]</span></button>";
		echo '</div>';
	}
	if($showbtn === NULL && $msgno === 0){
		echo '<div id="sbtn">';
		if(!$step){
			echo "<button type=\"button\" onclick=\"window.location.replace('http://www.phpcom.cn');\"><span>$lang[disagree]</span></button> 
			<button type=\"submit\"><span>$lang[agreement]</span></button>";
		}elseif($step == 3){
			echo "<button type=\"button\"><span>$lang[install_processed]</span></button>";
		}elseif($step == 4){
			echo "<button type=\"button\" onclick=\"window.location.replace('../');\"><span>$lang[install_complete]</span></button>";
		}else{
			echo "<button type=\"button\" onclick=\"history.back();\"><span>$lang[back_step]</span></button> 
			<button type=\"submit\"><span>$lang[next_step]</span></button>";
		}
		echo '</div></form>';
	}
	$showbtn = TRUE;
}

function show_footer(){
	static $year;
	if($year === NULL){
		$year = gmdate('Y');
		echo <<<EOT
			<div id="footer">
				<div class="powered">Powered By PHPcom&trade; &copy;2010 - $year All
					Rights Reserved.</div>
			</div>
		</div>
	</div>
	<p>&nbsp;</p>
</body>
</html>
EOT;
	}
}

function showhtmlsetting($key, $name, $value = '', $type = 'text', $error = '', $return = TRUE){
	global $lang;
	if($key == 'form'){
		$value = $value ? $value : 'index.php';
		$name = $name ? $name : 'form1';
		return "<form name=\"$name\" method=\"post\" action=\"$value\">\n";
	}elseif($key == 'end'){
		return "\n</table>\n</form>\n";
	}elseif($key == 'hidden'){
		return "<input type=\"hidden\" name=\"$name\" value=\"$value\" />\n";
	}elseif($name == 'start'){
		$s = '<table class="tb">';
		if($key){
			$caption = isset($lang[$key]) ? $lang[$key] : $key;
			$s .= "<caption>$caption</caption>\n";
		}
		return $s;
	}
	$title = isset($lang[$key]) ? $lang[$key] : $key;
	$s = "<tr>\n<td class=\"tit".($error ? ' fb' : '')."\">$title</td>\n<td>";
	if($type == 'text' || $type == 'password') {
		$value = htmlcharsdecode($value);
		$s .= "<input type=\"$type\" name=\"$name\" value=\"$value\" size=\"35\" class=\"txt\" />";
	} elseif($type == 'checkbox') {
		if(!is_array($name) && !is_array($value)) {
			$s .= "<label><input class=\"checkbox\" type=\"checkbox\" name=\"$name\" value=\"1\"".($value ? 'checked="checked"' : '')." />".$lang[$key.'_check_label']."</label>\n";
		}
	} elseif($type == 'radio') {
		if(is_array($value)){
			foreach($value as $k => $v){
				$s .= "<label><input class=\"checkbox\" type=\"radio\" name=\"$name\" value=\"$k\"".(strcasecmp($k, $v) ? '' : 'checked="checked"')." />".$lang[$key.'_'.$k.'_label']."</label>\n";
			}
		}
	}else{
		$s .= $value;
	}
	$s .= "</td>\n<td class=\"tips\">";
	if($error){
		if(is_string($error)){
			$errmsg = isset($lang[$error]) ? $lang[$error] : $error;
		}else{
			$errmsg = isset($lang[$key.'_error']) ? $lang[$key.'_error'] : '';
		}
		$tips = "<span class=\"red\">$errmsg</span>";
	}else{
		$tips = isset($lang[$key.'_tips']) ? $lang[$key.'_tips'] : '';
	}
	$s .= "$tips</td>\n</tr>\n";
	if($return){
		return $s;
	}else{
		echo $s;
	}
}

function showjsmessage($message) {
	echo '<b><script type="text/javascript">showmessage(\''.addslashes($message).' \');</script></b>'."\r\n";
	flush();
	ob_flush();
}

function redirect($url) {
	echo "<script type=\"text/javascript\">".
			"function redirect() {window.location.replace('$url');}\r\n".
			"setTimeout('redirect();', 100);\r\n".
			"</script>";
	exit();
}

function save_webconfig($filename, $config, $default = NULL) {
	$config = setconfigarray($config, $default);
	$content = "<?php\r\n!defined('IN_PHPCOM') && exit('Access denied');\r\n\r\n\$_config = array();\r\n";
	$content .= exportconfigarray($config);
	$content .= "\r\n/* " . str_pad('  THE END  ', 70, '-', STR_PAD_BOTH) . " */\r\n\r\n?>";
	file_put_contents($filename, $content);
}

function getwebconfig() {
	static $webconfig;
	if($webconfig === NULL){
		$_config = array();
		if(file_exists(ROOT_PATH . CONFIG_FILE)){
			$defconfigfile = ROOT_PATH . CONFIG_FILE;
		}else{
			$defconfigfile = INSTALL_PATH . 'data/config.php';
		}
		if(!file_exists($defconfigfile)){
			exit('config.php was lost, please to upload this file.');
		}else{
			@include_once $defconfigfile;
			$webconfig = $_config;
		}
	}
	return $webconfig;
}
?>