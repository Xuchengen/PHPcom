<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : channel.php    2011-5-9 21:55:47
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_channel($channelid = 0) {
	$data = $channel = array();
    $tablearray = array('article' => 'article_thread', 'soft' => 'soft_thread', 'special' => 'special_thread',
    		'photo' => 'photo_thread', 'video' => 'video_thread', 'ask' => 'ask_thread');
	$sql = "SELECT * FROM " . DB::table('channel') . " ORDER BY sortord";
	$query = DB::query($sql);
	while ($row = DB::fetch_array($query)) {
		$row['setting'] = unserialized($row['setting']);
		$row['tablename'] = isset($tablearray[$row['modules']]) ? $tablearray[$row['modules']] : '';
		if ($row['type'] != 'menu') {
			$channel['module'][$row['codename']] = $row['channelid'];
		}
		$row['color'] = $row['color'] ? ' style="color: ' . $row['color'] . '"' : '';
		$row['target'] = $row['target'] ? ' target="_blank"' : '';
		
		if ($row['type'] == 'system' || $row['type'] == 'expand') {
			$data = $row;
			$data['setting'] = '';
			$data = array_merge($data, $row['setting']);
			if(isset($data['quality'])){
				if(empty($data['quality'])){
					$data['quality'] = array('unknown', '480P', '720P', '1080P');
				}else{
					$qualityArray = explode(',', trim($data['quality']));
					$items = array();
					foreach ($qualityArray as $quality){
						if(strpos($quality, '=')){
							list($val, $key) = explode('=', $quality);
							if(is_numeric($key)){
								$items[$key] = trim($val);
							}
						}
					}
					$data['quality'] = $items ? $items : array('unknown', '480P', '720P', '1080P');
				}
			}
			$data['pagesize'] = isset($data['pagesize']) ? $data['pagesize'] : 20;
			$data['remoteon'] = isset($data['remoteon']) ? $data['remoteon'] : 1;
			if(empty($data['waterimage'])) $data['waterimage'] = '';
			if(empty($data['previewshow'])) $data['previewshow'] = 0;
			if(empty($data['thumbauto'])) $data['thumbauto'] = 0;
			if(empty($data['thumbzoom'])) $data['thumbzoom'] = 0;
			if(empty($data['previewzoom'])) $data['previewzoom'] = 0;
			if(empty($data['dialogue']) && !empty($data['language'])){
				$data['dialogue'] = $data['language'];
			}
			unset($data['parentid'], $data['sortord'], $data['setting'], $data['counter']);
			$channel[$row['channelid']] = $data;
		}else{
			$channel[$row['channelid']]['channelid'] = $row['channelid'];
			$channel[$row['channelid']]['parentid'] = $row['parentid'];
			$channel[$row['channelid']]['sortord'] = $row['sortord'];
			$channel[$row['channelid']]['type'] = $row['type'];
			$channel[$row['channelid']]['modules'] = $row['modules'];
			$channel[$row['channelid']]['tablename'] = $row['tablename'];
			$channel[$row['channelid']]['channelname'] = $row['channelname'];
			$channel[$row['channelid']]['subname'] = $row['subname'];
			$channel[$row['channelid']]['codename'] = $row['codename'];
			$channel[$row['channelid']]['color'] = $row['color'] ? ' style="color: ' . $row['color'] . '"' : '';
			$channel[$row['channelid']]['icons'] = $row['icons'];
			$channel[$row['channelid']]['domain'] = $row['domain'];
			$channel[$row['channelid']]['chanroot'] = $row['chanroot'];
			$channel[$row['channelid']]['htmlout'] = $row['htmlout'];
			$channel[$row['channelid']]['closed'] = $row['closed'];
			$channel[$row['channelid']]['pagesize'] = isset($row['setting']['pagesize']) ? $row['setting']['pagesize'] : 20;
			$channel[$row['channelid']]['target'] = $row['target'] ? ' target="_blank"' : '';
			$channel[$row['channelid']]['remoteon'] = isset($row['setting']['remoteon']) ? $row['setting']['remoteon'] : 1;
		}
	}
	DB::free_result($query);
	$channel['module']['main'] = 0;
	phpcom_cache::save('channel', $channel);
}

?>
