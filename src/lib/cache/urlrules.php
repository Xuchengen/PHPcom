<?php
/**
 * Copyright (c) 2010-2012 phpmain.com - All rights reserved.
 * Our Website : www.phpmain.com www.phpmain.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPMain.
 * This File   : urlrules.php  2012-3-27
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_urlrules($channel = 0) {
	unset($channel);
	$data = array();
	$setting = DB::fetch_first("SELECT skey, svalue FROM " . DB::table('setting') . " WHERE skey='htmlstatus'");
	$query = DB::query("SELECT modules, rulename, matchurl, actionurl, staticize FROM " . DB::table('urlrules'));
	while ($row = DB::fetch_array($query)) {
		if(stricmp($row['rulename'], array('category', 'threadlist'))){
			$row['matchurl'] = trim(str_replace('{cid}', '{catid}', $row['matchurl']));
			$row['actionurl'] = trim(str_replace('{cid}', '{catid}', $row['actionurl']));
		}
		if($setting['svalue'] && $row['staticize']){
			$data[$row['modules']][$row['rulename']] = parser_urlrules($row['matchurl']);
		}else{
			$data[$row['modules']][$row['rulename']] = parser_urlrules($row['actionurl']);
		}
	}

	phpcom_cache::save('urlrules', $data);
}

function parser_urlrules($rule){
	$data = array('rule' => $rule);
	preg_match_all("/\{([\w-\/:%]+?)\}/", $rule, $matchs);
	foreach ($matchs[1] as $value) {
		$key = $value;
		$val = '{' . $value . '}';
		if(strpos($value, 'date:') === 0){
			$key = 'date';
			$val = substr($value, 5);
			$data['rule'] = str_replace('{'.$value.'}', '{date}', $data['rule']);
		}elseif(strpos($value, 'query:') === 0){
			$key = 'query';
			$val = substr($value, 6);
			$data['rule'] = str_replace('{'.$value.'}', '{query}', $data['rule']);
		}elseif(strpos($value, '-') === 0 || strpos($value, '_') === 0 || strpos($value, '0') === 0) {
			$key = substr($value, 1);
			$val = substr($value, 0, 1) .'{'. $key . '}';
			$data['rule'] = str_replace('{'.$value.'}', $val, $data['rule']);
		}
		$data['match'][$key] = $val;
	}
	return $data;
}

?>