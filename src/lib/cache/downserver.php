<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : downserver.php  2012-12-16
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_downserver($channel = 0) {
	unset($channel);
	$data = $baseserver = $subserver1 = $subserver1 = array();
	$sql = "SELECT * FROM " . DB::table('downserver') . " WHERE chanid>'0' ORDER BY sortord";
	$query = DB::query($sql);
	while ($row = DB::fetch_array($query)) {
		unset($row['lastdate'], $row['todaydown'], $row['downcount']);
		$row['color'] = $row['color'] ? ' style="color: ' . $row['color'] . '"' : '';

		if ($row['depth'] == 0) {
			$baseserver[$row['servid']] = $row;
		} elseif ($row['depth'] == 1) {
			$subserver1[$row['parentid']][$row['servid']] = $row;
		} else {
			$subserver2[$row['parentid']][$row['servid']] = $row;
		}
	}
	
	foreach ($baseserver as $key => $row) {
		$rootid = $row['servid'];
		$data[$rootid][] = $row;
		if(isset($subserver1[$row['servid']])){
			foreach ($subserver1[$row['servid']] as $key => $row) {
				$data[$rootid][] = $row;
				if(isset($subserver2[$row['servid']])){
					foreach ($subserver2[$row['servid']] as $key => $row) {
						$data[$rootid][] = $row;
					}
				}
			}
		}
	}
	phpcom_cache::save('downserver', $data);
}
?>