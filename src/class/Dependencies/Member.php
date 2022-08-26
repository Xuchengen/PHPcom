<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Member.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Dependencies_Member extends Dependencies_Abstract
{
	public function route(Web_HttpRequest $request, $module)
	{
		if(strcasecmp($module, 'Member') == 0){
			$module = 'Member';
		}
		if(!stricmp($module, 'Member')){
			return null;
		}
		$this->_module = $module ? ucwords($module) : 'Member';
		if(!$action = $request->getParam('action')){
			$action = $request->query('action') ? $request->query('action') : $request->query('act');
			if(stricmp($action, 'register')){
				$action = stricmp($action, phpcom::$setting['register']['modname']) ? 'Register' : 'Login';
			}
		}
		$this->_action = $action ? trim($action) : 'Index';
		$this->_action = stricmp($this->_action, phpcom::$setting['register']['modname']) ? 'Register' : $this->_action;
		
		if(phpcom::$G['uid'] && stricmp($this->_action, array('Login', 'Register', 'getpasswd'))){
			$this->_action = 'Index';
		}elseif(empty(phpcom::$G['uid']) && !stricmp($this->_action, array('Home', 'Register', 'getpasswd', 'misc', 'clearcookies', 'Activate'))){
			$this->_action = 'Login';
		}
		
		$this->_controllerName = $this->_module . '_' . ucwords($this->_action);
		
		return $this;
	}
	
	public function allowAssigned($controller)
	{
		return ($controller instanceof Controller_MemberAbstract);
	}
	
	public function getControllerName()
	{
		return $this->_controllerName ? $this->_controllerName : 'Member_Index';
	}
	
	public function getModule()
	{
		return $this->_module ? $this->_module : 'Member';
	}
	
	public function getAction()
	{
		return $this->_action ? $this->_action : 'Index';
	}
}
?>