<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : WebPage.php  2012-7-18
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Dependencies_WebPage extends Dependencies_Abstract
{
	
	public function route(Web_HttpRequest $request, $module)
	{
		$module = $request->getModule();
		$chanModule = strtolower($module);
		if(strcasecmp($module, 'Main') == 0){
			$module = $request->query('module') ? trim($request->query('module')) : $module;
			$chanModule = strtolower($module);
			if($request->query('channel')){
				$chanModule = strtolower(stripstring($request->query('channel')));
			}else{
				$chanModule = strtolower($request->getAction());
			}
		}
		
		if(stricmp($module, array('Admin', 'Member'))){
			return null;
		}
		if(isset(phpcom::$G['channel']['module'][$chanModule]) && $chanModule != 'main'){
			$chanid = intval(phpcom::$G['channel']['module'][$chanModule]);
			$module = phpcom::$G['channel'][$chanid]['modules'];
			phpcom::$G['channelid'] = $chanid;
		}
		$this->_module = $module ? ucwords($module) : 'Main';
		if(!$action = $request->getParam('action')){
			$action = $request->query('action');
		}
		if($request->getAction()){
			$action = $request->getAction();
		}
		$this->_action = $action ? trim($action) : 'Index';
		if(stricmp($this->_action, array('Hot', 'New'))){
			$this->_controllerName = $this->_module . '_HotAndNew';
		}elseif(strcasecmp($this->_action, 'list') === 0){
			$this->_controllerName = $this->_module . '_ThreadList';
		}elseif(strcasecmp($this->_action, 'view') === 0){
			$this->_controllerName = $this->_module . '_ThreadView';
		}else{
			$this->_controllerName = $this->_module . '_' . ucwords($this->_action);
		}
		return $this;
	}
	
	public function allowAssigned($controller)
	{
		return ($controller instanceof Controller_MainAbstract);
	}
	
	public function getControllerName()
	{
		return $this->_controllerName ? $this->_controllerName : 'Main_Index';
	}
	
	public function getModule()
	{
		return $this->_module ? $this->_module : 'Main';
	}
	
	public function getAction()
	{
		return $this->_action ? $this->_action : 'Index';
	}
	
}

?>