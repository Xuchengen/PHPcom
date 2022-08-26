<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : ajax.php    2012-2-5
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'main';
ob_end_clean();
ob_start();
//@header('Access-Control-Allow-Origin: *');
@header('Content-Type: text/xml;charset=' . CHARSET);
@header('Expires: -1');
@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
@header('Pragma: no-cache');
echo '<?xml version="1.0" encoding="' . CHARSET . '"?>', "\r\n";
echo "<root><![CDATA[";
if ($action == 'matters') {
	$pendingmembers = DB::result_first("SELECT COUNT(*) FROM " . DB::table('member_validate') . " WHERE status='0'");
	if ($pendingmembers) {
		echo adminlang('pending_audit_members', array('num' => $pendingmembers));
	}
	$recyclethreads = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " WHERE status='-1'");
	if ($recyclethreads) {
		echo adminlang('recycle_threads', array('num' => $recyclethreads));
	}
	echo '&nbsp;';
}elseif($action == 'dbsize'){
	$dbsize = DB::size();
	$dbsize = $dbsize ? formatbytes($dbsize) : 'unknown';
	echo $dbsize;
} elseif ($action == 'topicdata') {
	$tid = isset(phpcom::$G['gp_tid']) ? intval(phpcom::$G['gp_tid']) : 0;
	$data = array();
	if ($tid) {
		$query = DB::query("SELECT topicid FROM " . DB::table('topic_data') . " WHERE tid='$tid'");
		while ($row = DB::fetch_array($query)) {
			$data[] = $row['topicid'];
		}
	}
	$query = DB::query("SELECT topicid,title FROM " . DB::table('topical') . " WHERE hide='0'");
	$s = '<input type="hidden" name="topicstatus" value="1" />';
	$s .= '<ul onmouseover="alterStyle(this);" class="checkboxstyle">';
	while ($topic = DB::fetch_array($query)) {
		$topicid = $topic['topicid'];
		if ($tid && $data && in_array($topicid, $data)) {
			$checked = TRUE;
		} else {
			$checked = FALSE;
		}
		$extra = $checked ? 'checked="checked"' : '';
		$s .= '<li class="item' . ($checked ? ' checked' : '') . '"><input name="topicids[]" value="' . $topicid . '" class="checkbox" type="checkbox" ' . $extra . '/><label> ' . $topic['title'] . "</label></li>\r\n";
	}
	echo "$s</ul>";
}elseif($action == 'notice'){
	$result = array('level' => 0);
	$adminscript = ADMIN_SCRIPT;
	if(!empty(phpcom::$setting['upgrader'])){
		$result = phpcom::$setting['upgrader'];
	}
	if(empty(phpcom::$G['cookie']['checkupgradetime'])){
		$upgrade = new UpgradeService();
		$result = $upgrade->checkUpgrade();
		phpcom::setcookie('checkupgradetime', TIMESTAMP, 10800);
	}
	if(isset($result['level']) && $result['level'] == 1){
		$message = adminlang('upgrade_notice');
		echo "<p><a target=\"mainFrame\" href=\"$adminscript?m=upgrade\" hidefocus=\"true\">$message</a><em onclick=\"this.parentNode.parentNode.style.display='none';\">x</em></p>";
	}elseif(isset($result['level']) && $result['level'] == 2){
		$message = adminlang('patch_bugfix_notice');
		echo "<p><a target=\"mainFrame\" href=\"$adminscript?m=upgrade&action=patch\" hidefocus=\"true\">$message</a><em onclick=\"this.parentNode.parentNode.style.display='none';\">x</em></p>";
	}elseif(isset($result['level']) && $result['level'] == 3){
		$message = adminlang('patch_bugfix_right_now');
		echo "<p><a target=\"mainFrame\" href=\"$adminscript?m=upgrade&action=patch\" hidefocus=\"true\">$message</a><em onclick=\"this.parentNode.parentNode.style.display='none';\">x</em></p>";
	}elseif(isset($result['level']) && $result['level'] == -1 && !empty($result['notice']) && !empty($result['url'])){
		$message = $result['notice'];
		$url = $result['url'];
		echo "<p><a target=\"_blank\" href=\"$url\" hidefocus=\"true\">$message</a><em onclick=\"this.parentNode.parentNode.style.display='none';\">x</em></p>";
	}
}elseif($action == 'softlist') {
	$key = isset(phpcom::$G['gp_key']) ? trim(phpcom::$G['gp_key']) : '';
	if(strlen($key) >= 2) {
		$sql = "SELECT tid, chanid, softname, softversion\n";
		$sql .= "FROM " . DB::table('soft_thread') . "\n";
		$sql .= "WHERE softname LIKE '%$key%' OR subtitle LIKE '%$key%'";
		$sql .= "LIMIT 10";
		$SQL = DB::query($sql);
		$result = '';
		while($row = DB::fetch_array($SQL)) {
			$result .= "<li>";
			$result .= "<a href=\"?m=soft&action=edit&chanid={$row['chanid']}&tid={$row['tid']}\">{$row['softname']} {$row['softversion']}</a>";
			$result .= "</li>";
		}
		echo $result;
	}
}
$contents = ob_get_contents();
ob_end_clean();
$contents = preg_replace("/([\\x01-\\x08\\x0b-\\x0c\\x0e-\\x1f])+/", ' ', $contents);
$contents = str_replace(array(chr(0), ']]>'), array(' ', ']]&gt;'), $contents);
echo $contents;
echo "]]></root>";
?>
