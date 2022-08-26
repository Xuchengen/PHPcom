<?php
/**
 * Copyright (c) 2010-2012 phpmain.com - All rights reserved.
 * Our Website : www.phpmain.com www.phpmain.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPMain.
 * This File   : hook.php  2012-3-25
 */
!defined('IN_PHPCOM') && exit('Access denied');

class phpmain_hook {
	var $hooks = array();
	var $hookresult = array();
	var $currenthook = NULL;

	function __construct($validhooks) {
		$this->phpmain_hook($validhooks);
    }
    
	function phpmain_hook($validhooks){
		foreach ($validhooks as $_null => $method) {
			$this->add($method);
		}
		
		if (function_exists('phpmain_hook_register')) {
			phpmain_hook_register($this);
		}
	}

	function register($definition, $hook, $mode = 'normal'){
		$class = (!is_array($definition)) ? '__global' : $definition[0];
		$function = (!is_array($definition)) ? $definition : $definition[1];

		if (isset($this->hooks[$class][$function])) {
			switch ($mode) {
				case 'standalone':
					if (!isset($this->hooks[$class][$function]['standalone'])) {
						$this->hooks[$class][$function] = array('standalone' => $hook);
					}else{
						trigger_error('Hook not able to be called standalone, previous hook already standalone.', E_NOTICE);
					}
					break;
				case 'first':
				case 'last':
					$this->hooks[$class][$function][$mode][] = $hook;
					break;
				case 'normal':
				default:
					$this->hooks[$class][$function]['normal'][] = $hook;
					break;
			}
		}
	}

	function callhook($definition) {
		$class = (!is_array($definition)) ? '__global' : $definition[0];
		$function = (!is_array($definition)) ? $definition : $definition[1];

		if (!empty($this->hooks[$class][$function])){
			if ($this->currenthook !== NULL && $this->currenthook['class'] === $class && $this->currenthook['function'] === $function) {
				return false;
			}

			$arguments = func_get_args();
			$this->currenthook = array('class' => $class, 'function' => $function);
			$arguments[0] = &$this;

			if (isset($this->hooks[$class][$function]['standalone'])) {
				$this->hookresult[$class][$function] = call_user_func_array($this->hooks[$class][$function]['standalone'], $arguments);
			} else {
				foreach (array('first', 'normal', 'last') as $mode) {
					if (!isset($this->hooks[$class][$function][$mode])) {
						continue;
					}

					foreach ($this->hooks[$class][$function][$mode] as $hook) {
						$this->hookresult[$class][$function] = call_user_func_array($hook, $arguments);
					}
				}
			}

			$this->currenthook = NULL;
			return TRUE;
		}

		$this->currenthook = NULL;
		return FALSE;
	}
	
	function previous_result($definition){
		$class = (!is_array($definition)) ? '__global' : $definition[0];
		$function = (!is_array($definition)) ? $definition : $definition[1];

		if (!empty($this->hooks[$class][$function]) && isset($this->hookresult[$class][$function])){
			return array('result' => $this->hookresult[$class][$function]);
		}

		return FALSE;
	}

	function hookreturn($definition){
		$class = (!is_array($definition)) ? '__global' : $definition[0];
		$function = (!is_array($definition)) ? $definition : $definition[1];
		
		if (!empty($this->hooks[$class][$function]) && isset($this->hookresult[$class][$function])) {
			return TRUE;
		}
		
		return FALSE;
	}

	function result($definition){
		$class = (!is_array($definition)) ? '__global' : $definition[0];
		$function = (!is_array($definition)) ? $definition : $definition[1];

		if (!empty($this->hooks[$class][$function]) && isset($this->hookresult[$class][$function])){
			$result = $this->hookresult[$class][$function];
			unset($this->hookresult[$class][$function]);
			return $result;
		}
		return;
	}

	function add($definition) {
		if (!is_array($definition)) {
			$definition = array('__global', $definition);
		}
		
		$this->hooks[$definition[0]][$definition[1]] = array();
	}

	function remove($definition) {
		$class = (!is_array($definition)) ? '__global' : $definition[0];
		$function = (!is_array($definition)) ? $definition : $definition[1];

		if (isset($this->hooks[$class][$function])) {
			unset($this->hooks[$class][$function]);
			if (isset($this->hookresult[$class][$function])) {
				unset($this->hookresult[$class][$function]);
			}
		}
	}
}


?>