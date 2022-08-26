<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Sitemap.php  2012-12-23
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Main_Sitemap extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$this->initialize();
		$title = lang('common', 'category_sitemap');
		$this->chanid = $chanid = intval($this->request->query('chanid', $this->request->getQuery(0)));
		$chanlist = $this->fetchChannel(array('menu' => 0));
		if($chanid && isset(phpcom::$G['channel'][$chanid])){
			$datalist[0] = &$chanlist[$chanid];
			$title = phpcom::$G['channel'][$chanid]['subname'] . $title;
		}else{
			$datalist = &$chanlist;
		}
		$this->title = $title;
		include template('sitemap');
		return 1;
	}

	public function writeToHtml($content = '')
	{
		if($this->checkHtmlKey()){
			$filename = PHPCOM_ROOT . '/sitemap.html';
			fwrite_content($filename, $content);
		}
	}
}
?>