<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Comment.php  2012-8-6
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Ajax_Comment extends Controller_AjaxAbstract
{
	public function loadActionIndex()
	{
		$tid = intval($this->request->query('tid'));
		$commentid = intval($this->request->query('commentid', $this->request->query('cid')));
		$type = trim($this->request->query('type'));
		$do = trim($this->request->query('do'));
		$key = $tid . '_' . $commentid;
		$iscaptcha = intval(phpcom::$setting['captchastatus'][4]);
		include template('ajax/comment');
		$this->loadAjaxFooter();
		return 0;
	}
}
?>