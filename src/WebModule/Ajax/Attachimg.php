<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Attachimg.php  2013-1-5
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Ajax_Attachimg extends Controller_AjaxAbstract
{
	public function loadActionIndex()
	{
		$this->initialize();
		$this->chanid = $chanid = intval($this->request->query('chanid'));
		$limit = intval($this->request->query('num'));
		$type = trim($this->request->query('type'));
		if($tid = intval($this->request->query('tid'))){
			include template('ajax/attachimg');
		}
		$this->loadAjaxFooter();
		return 0;
	}
}
?>