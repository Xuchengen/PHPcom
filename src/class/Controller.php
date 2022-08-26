<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Controller.php  2012-7-18
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Controller
{
	protected $dependencies;
	protected $request;
	protected $_module;
	protected $_action;
	
	public function __construct(Dependencies_Abstract $dependencies, $module = null, $action = null)
	{
		$this->dependencies = $dependencies;
		$this->_module = $module ? $module : 'Main';
		$this->_action = $action;
	}
	
	public function setRequest(Web_HttpRequest $request)
	{
		$this->request = $request;
	}
	
	public function getRequest()
	{
		return $this->request;
	}
	
	public function run($ishtml = false)
	{
		ob_start();
		if (!$this->request){
			$this->request = new Web_HttpRequest($this->_module, $this->_action);
		}
		if($this->_action){
			$this->request->setParam('action', $this->_action);
		}
		$router = $this->route();
		$this->request->setParam('action', $router->getAction());
		$this->request->setParam('module', $router->getModule());
		$this->_module = $this->request->getParam('module');
		$controllerName = $router->getControllerName();
		$controller = $this->getValidatedController($controllerName, $this->_module);
		if($controller){
			$controller->setHtmlMethod($ishtml);
			if($controller->loadActionIndex() && $controller->httpStatusCode == 200){
				$bufferedContents = ob_get_contents();
				if(!empty(phpcom::$config['output']['htmlcompress'])){
					$bufferedContents = $this->htmlCompress($bufferedContents);
				}
				ob_end_clean();
				if($ishtml && phpcom::$setting['htmlstatus']){
					if(method_exists($controller, 'writeToHtml')){
						$controller->writeToHtml($bufferedContents);
					}
				}else{
					if(OBGZIP_ENABLE && phpcom::$config['output']['gzip']){
						$encodings = (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) ? strtolower($_SERVER['HTTP_ACCEPT_ENCODING']) : "";
						$encoding = preg_match( '/\b(x-gzip|gzip)\b/', $encodings, $match) ? $match[1] : "";
						if (isset($_SERVER['---------------'])) $encoding = "x-gzip";
						$supportsgzip = !empty($encoding) && function_exists('gzencode');
						header("Vary: Accept-Encoding");
						if($supportsgzip){
							header("Content-Encoding: $encoding");
							$bufferedContents = gzencode($bufferedContents, 9, FORCE_GZIP);
						}
						header("Content-Length: " . strlen($bufferedContents));
					}
					echo $bufferedContents;
				}
			}
		}else{
			$this->request->setNotFound(true);
		}
	}
	
	private function htmlCompress($string) {
		$string = str_replace("\r\n", '', $string);
		$string = str_replace("\n", '', $string);
		$string = str_replace("\t", '', $string);
		$pattern = array (
				"/> *([^ ]*) *</",
				"/[\s]+/",
				"/<!--[^!]*-->/",
				"/\" /",
				"/ \"/",
				"'/\*[^*]*\*/'"
		);
		$replace = array (
				">\\1<",
				" ",
				"",
				"\"",
				"\"",
				""
		);
		return preg_replace($pattern, $replace, $string);
	}
	
	public function route()
	{
		$return = $this->dependencies->route($this->request, $this->_module);
		if($return){
			
		}
		return $return;
	}
	
	protected function getValidatedController($controllerName, $module)
	{
		$dirs = array('WebModule');
		if($module) $dirs[] = $module;
 		if(phpcomAutoload::loadClass($controllerName, $dirs)){
			$controller = new $controllerName($this->request);
			if (method_exists($controller, 'loadActionIndex') && $this->dependencies->allowAssigned($controller)) {
				return $controller;
			}
		}
		$controllerName = 'Main_Http404';
		$dirs = array('WebModule', 'Main');
		phpcomAutoload::loadClass($controllerName, $dirs);
		$controller = new $controllerName($this->request);
		if (method_exists($controller, 'loadActionIndex') && $this->dependencies->allowAssigned($controller)) {
			return $controller;
		}
		return null;
	}
	
}
?>