<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : HttpAbstract.php  2012-7-11
 */
!defined('IN_PHPCOM') && exit('Access denied');

abstract class Web_HttpAbstract
{
	protected $_assigned = false;
	protected $_module;
	protected $_moduleKey = 'module';
	protected $_action;
	protected $_actionKey = 'action';
	protected $_params = array();
	
	/**
	 * Set the module name to use
	 *
	 * @param string $value
	 * @return Web_HttpAbstract
	 */
	public function setModuleName($value)
	{
		$this->_module = $value;
		return $this;
	}
	
	/**
	 * Retrieve the module name
	 *
	 * @return string
	 */
	public function getModuleName()
	{
		if (null === $this->_module) {
			$this->_module = $this->getParam($this->getModuleKey());
		}
	
		return $this->_module;
	}
	
	/**
	 * Set the module key
	 *
	 * @param string $key
	 * @return Web_HttpAbstract
	 */
	public function setModuleKey($key)
	{
		$this->_moduleKey = (string)$key;
		return $this;
	}
	
	/**
	 * Retrieve the module key
	 *
	 * @return string
	 */
	public function getModuleKey()
	{
		return $this->_moduleKey;
	}
	
	/**
	 * Retrieve the action name
	 *
	 * @return string
	 */
	public function getActionName()
	{
		if (null === $this->_action) {
			$this->_action = $this->getParam($this->getActionKey());
		}
	
		return $this->_action;
	}
	
	/**
	 * Set the action name
	 *
	 * @param string $value
	 * @return Web_HttpAbstract
	 */
	public function setActionName($value)
	{
		$this->_action = $value;
		
		if (null === $value) {
			$this->setParam($this->getActionKey(), $value);
		}
		return $this;
	}
	
	/**
	 * Retrieve the action key
	 *
	 * @return string
	 */
	public function getActionKey()
	{
		return $this->_actionKey;
	}
	
	/**
	 * Set the action key
	 *
	 * @param string $key
	 * @return Web_HttpAbstract
	 */
	public function setActionKey($key)
	{
		$this->_actionKey = (string)$key;
		return $this;
	}
	
	/**
	 * Get an action parameter
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed
	 */
	public function getParam($key, $default = null)
	{
		$key = (string)$key;
		if (isset($this->_params[$key])) {
			return $this->_params[$key];
		}
	
		return $default;
	}
	
	/**
	 * Retrieve only user params (i.e, any param specific to the object and not the environment)
	 *
	 * @return array
	 */
	public function getUserParams()
	{
		return $this->_params;
	}
	
	/**
	 * Retrieve a single user param (i.e, a param specific to the object and not the environment)
	 *
	 * @param string $key
	 * @param string $default Default value to use if key not found
	 * @return mixed
	 */
	public function getUserParam($key, $default = null)
	{
		if (isset($this->_params[$key])) {
			return $this->_params[$key];
		}
	
		return $default;
	}
	
	/**
	 * Set an action parameter
	 *
	 * A $value of null will unset the $key if it exists
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return Web_HttpAbstract
	 */
	public function setParam($key, $value)
	{
		$key = (string) $key;
	
		if ((null === $value) && isset($this->_params[$key])) {
			unset($this->_params[$key]);
		} elseif (null !== $value) {
			$this->_params[$key] = $value;
		}
	
		return $this;
	}
	
	/**
	 * Get all action parameters
	 *
	 * @return array
	 */
	public function getParams()
	{
		return $this->_params;
	}
	
	/**
	 * Set action parameters en masse; does not overwrite
	 *
	 * Null values will unset the associated key.
	 *
	 * @param array $array
	 * @return Web_HttpAbstract
	 */
	public function setParams(array $array)
	{
		$this->_params = $this->_params + (array)$array;
	
		foreach ($this->_params as $key => $value) {
			if (null === $value) {
				unset($this->_params[$key]);
			}
		}
	
		return $this;
	}
	
	/**
	 * Unset all user parameters
	 *
	 * @return Web_HttpAbstract
	 */
	public function clearParams()
	{
		$this->_params = array();
		return $this;
	}
	
	/**
	 * Set flag indicating whether or not request has been assigned
	 *
	 * @param boolean $flag
	 * @return Web_HttpAbstract
	 */
	public function setAssigned($flag = true)
	{
		$this->_assigned = $flag ? true : false;
		return $this;
	}
	
	/**
	 * Determine if the request has been assigned
	 *
	 * @return boolean
	 */
	public function isAssigned()
	{
		return $this->_assigned;
	}
}
?>