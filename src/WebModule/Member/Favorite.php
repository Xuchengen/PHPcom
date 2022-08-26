<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Favorite.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Favorite extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$this->title = $title = lang('member', 'member_favorite');

		include template('member/favorite');
		return 1;
	}
}
?>