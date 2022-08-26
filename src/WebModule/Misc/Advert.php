<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Advert.php  2012-11-1
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Misc_Advert extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$this->initialize();
		$name = $this->request->query('name', 'NoAdvert');
		$adcategory = array();
		if(isset(phpcom::$G['group']['noadverts']) && phpcom::$G['group']['noadverts']){
			echo "document.write('');";
			return 0;
		}
		if(!isset(phpcom::$G['cache']['adcategory'])){
			phpcom_cache::load('adcategory');
		}
		if(isset(phpcom::$G['cache']['adcategory'][$name])){
			$adcategory = &phpcom::$G['cache']['adcategory'][$name];
			if(empty($adcategory['status'])){
				echo "document.write('');";
				return 0;
			}
			$cid = intval($adcategory['cid']);
			$ctype = intval($adcategory['ctype']);
			$display = intval($adcategory['display']);
			$limit = $display == 1 ? 1 : intval($adcategory['maxads']);
			$limit = $limit ? $limit : 50;
			$content = $start = $end = '';
			if($limit > 1){
				$start = '<li>';
				$end = '</li>';
			}
			include template('misc/advert');
		}
		return 0;
	}
}
?>