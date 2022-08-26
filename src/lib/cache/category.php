<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : category.php    2011-5-31 18:04:31
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_category($channel = 0) {
	$data = $categorys = $basedata = array();
	$channelid = intval($channel);
	
	$sql = "SELECT catid, chanid, rootid, basic, catname, subname, codename, prefixurl, prefix, color,
			 icons, imageurl, remote, template, num, pagesize, child, target, caturl, counts,
			 toptype, topmode, topnum
			 FROM " . DB::table('category') . " WHERE depth='0' ORDER BY sortord, catid";
	$query = DB::query($sql);
	while ($row = DB::fetch_array($query)) {
		$row['color'] = $row['color'] ? ' style="color: ' . $row['color'] . '"' : '';
		$row['target'] = $row['target'] ? ' target="_blank"' : '';
		$row['codename'] = trim($row['codename']);
		$row['prefixurl'] = trim($row['prefixurl']);
		$row['rootname'] = $row['codename'];
		$row['prefix'] = trim($row['prefix']);
		$basedata[$row['catid']] = $row;
	}
	
	if($channelid){
		$sql = "SELECT * FROM " . DB::table('category') . " WHERE chanid='$channelid' ORDER BY sortord";
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$row['color'] = $row['color'] ? ' style="color: ' . $row['color'] . '"' : '';
			$row['target'] = $row['target'] ? ' target="_blank"' : '';
			$row['prefixurl'] = trim($row['prefixurl']);
			$row['prefix'] = trim($row['prefix']);
			$row['codename'] = trim($row['codename']);
			$row['rootname'] = isset($basedata[$row['rootid']]['rootname']) ? $basedata[$row['rootid']]['rootname'] : $row['codename'];
			unset($row['sortord'], $row['banner'], $row['setting']);
			unset($row['subject'], $row['description'], $row['keyword']);
			$categorys[$row['catid']] = $row;
		}
		foreach($categorys as $catid => $row){
			if($row['parentid'] == 0){
				$row['parentkey'] = -1;
			}else{
				$row['parentkey'] = $categorys[$row['parentid']]['parentid'];
			}
			$data[$row['parentid']][$catid] = $row;
		}
		
		phpcom_cache::save("category_$channelid", $data);
	}
	phpcom_cache::save('category', $basedata);
}

?>
