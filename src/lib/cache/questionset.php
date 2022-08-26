<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : questionset.php    2011-8-10 2:26:14
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_questionset($channel = 0) {
    unset($channel);
	$data = $tmp = array();
	$questionnum = DB::result_first("SELECT COUNT(*) FROM " . DB::table('questionset'));
    
	$start_limit = $questionnum <= 50 ? 0 : mt_rand(0, $questionnum - 50);
	$query = DB::query("SELECT question, answer, type FROM " . DB::table('questionset') . " LIMIT $start_limit, 50");
	$i = 1;
	while($row = DB::fetch_array($query)) {
		if(!$row['type'])  {
			$row['answer'] = md5($row['answer']);
		}
		$tmp[$i] = $row;
		$i++;
	}
	if($questionnum && $tmp){
		while(($num = count($data)) < 49) {
			$data[$num + 1] = $tmp[array_rand($tmp)];
		}
	}
	phpcom_cache::save('questionset', $data);
}

?>
