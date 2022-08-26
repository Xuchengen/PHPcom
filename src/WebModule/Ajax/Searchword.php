<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Searchword.php  2012-8-5
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Ajax_Searchword extends Controller_AjaxAbstract
{
	public function loadActionIndex()
	{
		phpcom_cache::load('searchword');
		$datalist = $searchWordList = isset(phpcom::$G['cache']['searchword']) ? phpcom::$G['cache']['searchword'] : array();
		include template('ajax/searchword');
		$this->loadAjaxFooter();
		return 0;
	}
}
?>