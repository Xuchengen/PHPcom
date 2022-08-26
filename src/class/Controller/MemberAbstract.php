<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : MemberAbstract.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

abstract class Controller_MemberAbstract extends WebController
{
	
	protected $regmodule, $registerurl, $loginurl, $memberurl;
	protected $todaytime, $attachurl;
	protected $usercredits = 0, $groupname = '';
	
	public function __construct(Web_HttpRequest $request)
	{
		parent::__construct($request);
		
		$this->regmodule = phpcom::$setting['register']['modname'];
		$this->regmodule = $this->regmodule ? $this->regmodule : 'register';
		$this->registerurl = geturl('member', array('action' => $this->regmodule), $this->domain);
		$this->loginurl = geturl('member', array('action' => 'login'), $this->domain);
		$this->memberurl = geturl('member', array('action' => 'index'), $this->domain);
		
		if ($this->uid) {
			$this->groupname = phpcom::$G['group']['grouptitle'];
		}
		
		$this->todaytime = strtotime(fmdate(TIMESTAMP, 'Ymd'));
		$this->attachurl = !isset($parse['host']) ? $this->domain . phpcom::$setting['attachurl'] : phpcom::$setting['attachurl'];
	}
}
?>