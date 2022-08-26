<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Abstract.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

abstract class MemberModel_Abstract
{
	public $request;
	
	public function __construct(Web_HttpRequest $request)
	{
		$this->request = $request;
	}
	
	public function setMemberLogin($member, $cookietime = 0) {
		phpcom::$G['uid'] = $member['uid'];
		phpcom::$G['username'] = addslashes($member['username']);
		phpcom::$G['groupid'] = $member['groupid'];
		phpcom::$G['member'] = $member;
		phpcom_cache::load('usergroup_' . phpcom::$G['groupid']);
		phpcom::setcookie('userauth', encryptstring("{$member['password']}\t{$member['uid']}"), $cookietime);
	}
}
?>