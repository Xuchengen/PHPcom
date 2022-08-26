<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : syscount.php  2012-11-14
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_syscount($channel = 0) {
	$syscount = $channels = array();
	$todaytime = strtotime(gmdate('Ymd'));
	phpcom_cache::load('syscount');
	if(($chanid = intval($channel)) > 0 && isset(phpcom::$G['cache']['syscount'])){
		$syscount = phpcom::$G['cache']['syscount'];
		$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " WHERE status='1' AND chanid='$chanid'");
		if(isset($syscount['thread']["count_$chanid"]) && $count){
			if(($diff = $count - $syscount['thread']["count_$chanid"])){
				$syscount['thread']['count'] = $syscount['thread']['count'] + $diff;
				$syscount['thread']['days'] = $syscount['thread']['days'] + $diff;
				$syscount['thread']["days_$chanid"] = $syscount['thread']["days_$chanid"] + $diff;
			}
		}else{
			$syscount['thread']['count'] = $syscount['thread']['count'] + $count;
			$syscount['thread']['days'] = $syscount['thread']['days'] + $count;
			$syscount['thread']["days_$chanid"] = $syscount['thread']["days_$chanid"] + $count;
		}
		$syscount['thread']["count_$chanid"] = $count;
	}elseif($channel == -1){
		$syscount['member']['count'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('members'));
		$syscount['member']['days'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('members') . " WHERE regdate>'$todaytime'");
	}else{
		$syscount['thread']['count'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " WHERE status='1'");
		$syscount['thread']['days'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " WHERE status='1' AND dateline>'$todaytime'");
		$syscount['member']['count'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('members'));
		$syscount['member']['days'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('members') . " WHERE regdate>'$todaytime'");
		
		$sql = "SELECT channelid FROM " . DB::table('channel') . " WHERE type IN('system','expand')";
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$channels[] = $row['channelid'];
		}
		foreach ($channels as $chanid) {
			$syscount['thread']["count_$chanid"] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " WHERE status='1' AND chanid='$chanid'");
			$syscount['thread']["days_$chanid"] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " WHERE status='1' AND chanid='$chanid' AND dateline>'$todaytime'");
		}
		$syscount['soft']['count'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('soft_thread'));
		$syscount['soft']['days'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('soft_thread') . " WHERE dateline>'$todaytime'");
		$syscount['article']['count'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('article_thread'));
		$syscount['article']['days'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('article_thread') . " WHERE dateline>'$todaytime'");
		$syscount['photo']['count'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('photo_thread'));
		$syscount['photo']['days'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('photo_thread') . " WHERE dateline>'$todaytime'");
		$syscount['video']['count'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('video_thread'));
		$syscount['video']['days'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('video_thread') . " WHERE dateline>'$todaytime'");
		$syscount['special']['count'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('special_thread'));
		$syscount['special']['days'] = DB::result_first("SELECT COUNT(*) FROM " . DB::table('special_thread') . " WHERE dateline>'$todaytime'");
	}
	
	phpcom_cache::save('syscount', $syscount);
}
?>