<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : searchword.php  2012-7-5
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_searchword($channel = 0) {
	unset($channel);
	$data = array();
	$query = DB::query("SELECT id, word, tn, highlight, url, target FROM " . DB::table('searchword') . " ORDER BY sortord");
	while ($row = DB::fetch_array($query)) {
		if(empty($row['url'])) {
			$row['url'] = phpcom::$setting['website'].phpcom::$G['instdir'].'search.php?word='.rawurlencode($row['word']) . ($row['tn'] ? '&tn='.$row['tn'] : '');
		}
		if($row['target']){
			$row['target'] = ' target="_blank"';
		}else{
			$row['target'] = '';
		}
		if ($row['highlight']) {
			$string = sprintf('%02d', $row['highlight']);
			$row['highlight'] = ' style="';
			$row['highlight'] .= $string[0] ? phpcom::$setting['fontvalue'][$string[0]] : '';
			$row['highlight'] .= $string[1] ? 'color: ' . phpcom::$setting['colorvalue'][$string[1]] : '';
			$row['highlight'] .= '"';
		} else {
			$row['highlight'] = '';
		}
		$data[] = $row;
	}
	phpcom_cache::save('searchword', $data);
}
?>