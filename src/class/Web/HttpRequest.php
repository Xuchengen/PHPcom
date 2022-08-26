<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Request.php  2012-7-12
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Web_HttpRequest extends Web_HttpAbstract
{
	const SCHEME_HTTP  = 'http';
	const SCHEME_HTTPS = 'https';
	protected $_paramSources = array('_GET', '_POST');
	protected $_rawBody;
	protected $_params = array();
	protected $_aliases = array();
	protected $_action = null;
	protected $_module = null;
	protected $_query = null;
	protected $_queryArray = array();
	protected $_queryString = null;
	
	public function __construct($module = null, $action = null)
	{
		$this->_action = $action;
		$this->_module = $module;
		$queryString = $this->getQueryString();
		
		if(!empty($queryString)){
			if($pos = strrpos($queryString, '.htm')){
				$queryString = substr($queryString, 0, $pos);
			}
			$arrAction = array();
			if(strcasecmp($action, 'search') && strcasecmp($module, 'admin') && strcasecmp($module, 'member')){
				if(strpos($queryString, '-')){
					list($actionstr, $querystr) = explode('-', $queryString, 2);
					$arrAction = explode('/', $actionstr);
					$this->_query = $querystr;
				}elseif(!strpos($queryString, '=')){
					$arrAction = explode('/', $queryString);
				}
			}
			if($arrAction){
				if(isset($arrAction[1]) && $arrAction[1]){
					$this->_action = $arrAction[1];
					$this->_module = $arrAction[0];
				}else{
					$this->_module = $module;
					$this->_action = $arrAction[0];
				}
			}
		}
	}
	
	public function setAction($value)
	{
		$this->_action = $value;
		return $this;
	}
	
	public function getAction()
	{
		return $this->_action;
	}
	
	public function setModule($value)
	{
		$this->_module = $value;
		return $this;
	}
	
	public function getModule()
	{
		return $this->_module;
	}
	
	public function setQuery($value)
	{
		$this->_query = $value;
	}
	
	public function getQuery($index = null, $default = null)
	{
		if($index !== null && $index !== ''){
			$queries = $this->getQueryArray();
			if(isset($queries[$index])){
				return addslashes($queries[$index]);
			}
			if(is_string($index)){
				if(false !== ($k = array_search($index, $queries))){
					unset($this->_queryArray[$k]);
					if($default === false && isset($queries[$k + 1])){
						unset($this->_queryArray[$k + 1]);
						$this->_queryArray[$index] = addslashes($queries[$k + 1]);
					}else{
						$this->_queryArray[$index] = addslashes($index);
					}
					return $this->_queryArray[$index];
				}
			}
			return $default;
		}
		return $this->_query;
	}
	
	public function getQueryArray()
	{
		if($this->_query && !$this->_queryArray){
			$this->_queryArray = explode('-', $this->_query);
			$count = count($this->_queryArray);
			if($count > 1 && ($page = $this->_queryArray[$count - 1]) && is_numeric($page)){
				$this->_queryArray['page'] = intval($page);
			}else{
				$this->_queryArray['page'] = 1;
			}
		}
		return $this->_queryArray;
	}
	/**
	 * Retrieve a member of the $_GET superglobal
	 *
	 * @todo How to retrieve from nested arrays
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function query($key = null, $default = null)
	{
		if (null === $key) {
			return $_GET;
		}
		
		if(is_array($key)){
			$getarray = array();
			foreach ($key as $name){
				if(is_string($name)){
					$getarray[$name] = (isset($_GET[$name])) ? $_GET[$name] : $default;
				}
			}
			return $getarray;
		}
		
		return (isset($_GET[$key])) ? $_GET[$key] : $default;
	}
	
	/**
	 * Set POST values
	 *
	 * @param  string|array $spec
	 * @param  null|mixed $value
	 * @return Web_HttpRequest
	 */
	public function setPost($spec, $value = null)
	{
		if ((null === $value) && !is_array($spec)) {
			throw new Exception('Invalid value passed to setPost(); must be either array of values or key/value pair');
		}
		if ((null === $value) && is_array($spec)) {
			foreach ($spec as $key => $value) {
				$this->setPost($key, $value);
			}
			return $this;
		}
		$_POST[(string)$spec] = $value;
		return $this;
	}
	
	/**
	 * Retrieve a member of the $_POST superglobal
	 *
	 * @todo How to retrieve from nested arrays
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function post($key = null, $default = null)
	{
		if (null === $key) {
			return $_POST;
		}
		
		if(is_array($key)){
			$postarray = array();
			foreach ($key as $name){
				if(is_string($name)){
					$postarray[$name] = (isset($_POST[$name])) ? $_POST[$name] : $default;
				}
			}
			return $postarray;
		}
		
		return (isset($_POST[$key])) ? $_POST[$key] : $default;
	}
	
	public function getPost($key = null, $default = null)
	{
		if (null === $key) {
			return $_POST + $_GET;
		}
	
		return (isset($_GET[$key])) ? $_GET[$key] : (isset($_POST[$key]) ? $_POST[$key] : $default);
	}
	/**
	 * Retrieve a member of the $_COOKIE superglobal
	 *
	 * @todo How to retrieve from nested arrays
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function cookie($key = null, $default = null)
	{
		if (null === $key) {
			return $_COOKIE;
		}
	
		return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default;
	}
	
	/**
	 * Retrieve a member of the $_SERVER superglobal
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function server($key = null, $default = null)
	{
		if (null === $key) {
			return $_SERVER;
		}
	
		return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
	}
	
	/**
	 * Retrieve a member of the $_ENV superglobal
	 *
	 * @param string $key
	 * @param mixed $default Default value to use if key not found
	 * @return mixed Returns null if key does not exist
	 */
	public function env($key = null, $default = null)
	{
		if (null === $key) {
			return $_ENV;
		}
	
		return (isset($_ENV[$key])) ? $_ENV[$key] : $default;
	}
	
	/**
	 * Set allowed parameter sources
	 *
	 * @param  array $paramSoures
	 * @return Web_HttpRequest
	 */
	public function setParamSources(array $paramSources = array())
	{
		$this->_paramSources = $paramSources;
		return $this;
	}
	
	/**
	 * Get list of allowed parameter sources
	 *
	 * @return array
	 */
	public function getParamSources()
	{
		return $this->_paramSources;
	}
	
	/**
	 * Set a userland parameter
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return Web_HttpRequest
	 */
	public function setParam($key, $value)
	{
		$key = (null !== ($alias = $this->getAlias($key))) ? $alias : $key;
		parent::setParam($key, $value);
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
	 * Set parameters
	 *
	 * @param array $params
	 * @return Web_HttpRequest
	 */
	public function setParams(array $params)
	{
		foreach ($params as $key => $value) {
			$this->setParam($key, $value);
		}
		return $this;
	}
	
	/**
	 * Set a key alias
	 *
	 * @param string $name
	 * @param string $target
	 * @return Web_HttpRequest
	 */
	public function setAlias($name, $target)
	{
		$this->_aliases[$name] = $target;
		return $this;
	}
	
	/**
	 * Retrieve an alias
	 *
	 * @param string $name
	 * @return string|null Returns null when no alias exists
	 */
	public function getAlias($name)
	{
		if (isset($this->_aliases[$name])) {
			return $this->_aliases[$name];
		}
	
		return null;
	}
	
	/**
	 * Retrieve the list of all aliases
	 *
	 * @return array
	 */
	public function getAliases()
	{
		return $this->_aliases;
	}
	
	/**
	 * Return the method by which the request was made
	 *
	 * @return string
	 */
	public function httpMethod()
	{
		return $this->server('REQUEST_METHOD');
	}
	
	/**
	 * Is this an OPTIONS method request?
	 *
	 * @return bool
	 */
	public function isOptions()
	{
		return ('OPTIONS' == $this->httpMethod());
	}
	
	/**
	 * Was the request made by HEAD?
	 *
	 * @return boolean
	 */
	public function isHead()
	{
		return ('HEAD' == $this->httpMethod());
	}
	
	/**
	 * Was the request made by POST?
	 *
	 * @return boolean
	 */
	public function isPost()
	{
		return ('POST' == $this->httpMethod());
	}
	
	/**
	 * Was the request made by GET?
	 *
	 * @return boolean
	 */
	public function isGet()
	{
		return ('GET' == $this->httpMethod());
	}
	
	/**
	 * Was the request made by PUT?
	 *
	 * @return boolean
	 */
	public function isPut()
	{
		return ('PUT' == $this->httpMethod());
	}
	
	/**
	 * Is the request a Javascript XMLHttpRequest?
	 *
	 * Should work with Prototype/Script.aculo.us, possibly others.
	 *
	 * @return boolean
	 */
	public function isXmlHttpRequest()
	{
		return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
	}
	
	/**
	 * Is this a Flash request?
	 *
	 * @return boolean
	 */
	public function isFlashRequest()
	{
		$header = strtolower($this->getHeader('USER_AGENT'));
		return (strstr($header, ' flash')) ? true : false;
	}
	
	/**
	 * Return the raw body of the request, if present
	 *
	 * @return string|false Raw body, or false if not present
	 */
	public function getRawBody()
	{
		if (null === $this->_rawBody) {
			$body = file_get_contents('php://input');
	
			if (strlen(trim($body)) > 0) {
				$this->_rawBody = $body;
			} else {
				$this->_rawBody = false;
			}
		}
		return $this->_rawBody;
	}
	
	/**
	 * Normalize a header name
	 *
	 * Normalizes a header name to X-Capitalized-Names
	 *
	 * @param  string $name
	 * @return string
	 */
	protected function _normalizeHeader($name)
	{
		$filtered = str_replace(array('-', '_'), ' ', (string)$name);
		$filtered = ucwords(strtolower($filtered));
		$filtered = str_replace(' ', '-', $filtered);
		return $filtered;
	}
	
	/**
	 * Set a request header
	 *
	 * @param string $name
	 * @param string $value
	 * @param boolean $replace
	 * @return Web_HttpRequest
	 */
	public function setHeader($name, $value = null, $replace = true)
	{
		if($value){
			$name  = $this->_normalizeHeader($name);
			$value = trim($value);
			header("$name: $value", $replace);
		}else{
			header("$name", $replace);
		}
		return $this;
	}
	
	/**
	 * Return the value of the given HTTP header. Pass the header name as the
	 * plain, HTTP-specified header name. Ex.: Ask for 'Accept' to get the
	 * Accept header, 'Accept-Encoding' to get the Accept-Encoding header.
	 *
	 * @param string $header HTTP header name
	 * @return string|false HTTP header value, or false if not found
	 */
	public function getHeader($header)
	{
		if (empty($header)) {
			return false;
		}
	
		// Try to get it from the $_SERVER array first
		$temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
		if (isset($_SERVER[$temp])) {
			return $_SERVER[$temp];
		}

		if (function_exists('apache_request_headers')) {
			$headers = apache_request_headers();
			if (isset($headers[$header])) {
				return $headers[$header];
			}
			$header = strtolower($header);
			foreach ($headers as $key => $value) {
				if (strtolower($key) == $header) {
					return $value;
				}
			}
		}
	
		return false;
	}
	
	/**
	 * Set redirect URL
	 *
	 * @param string $url
	 * @param int $response_code
	 * @return Web_HttpRequest
	 */
	public function redirect($url, $response_code = 302)
	{
		header("Location: $url", true, $response_code);
		return $this;
	}
	
	/**
	 * Set HTTP status HTTP/1.1 404 Not Found
	 *
	 * @param boolean $exited
	 * @return Web_HttpRequest
	 */
	public function setNotFound($exited = false)
	{
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
		header("Status: 404 Not Found");
		$_SERVER["REDIRECT_STATUS"] = 404;
		if($exited) exit('404 Not Found');
		return $this;
	}
	
	/**
	 * Set HTTP status HTTP/1.1 403 Forbidden
	 *
	 * @param boolean $exited
	 * @return Web_HttpRequest
	 */
	public function setForbidden($exited = false)
	{
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		$_SERVER["REDIRECT_STATUS"] = 403;
		if($exited) exit('403 Forbidden');
		return $this;
	}
	
	/**
	 * Set HTTP status HTTP/1.1 503 Service Temporarily Unavailable
	 *
	 * @param boolean $exited
	 * @return Web_HttpRequest
	 */
	public function setUnavailable($exited = false)
	{
		header("HTTP/1.1 503 Service Temporarily Unavailable");
		header("Status: 503 Service Temporarily Unavailable");
		header("Retry-After: 120");
		header("Connection: Close");
		$_SERVER["REDIRECT_STATUS"] = 503;
		if($exited) exit('503 Service Temporarily Unavailable');
		return $this;
	}
	
	/**
	 * Get the request URI scheme
	 *
	 * @return string
	 */
	public function scheme()
	{
		return ($this->server('HTTPS') == 'on') ? self::SCHEME_HTTPS : self::SCHEME_HTTP;
	}
	
	public function httpHost()
	{
		$host = $this->server('HTTP_HOST');
		if (!empty($host)) {
			return $host;
		}
	
		$scheme = $this->scheme();
		$name   = $this->server('SERVER_NAME');
		$port   = $this->server('SERVER_PORT');
	
		if(null === $name) {
			return '';
		}
		elseif (($scheme == self::SCHEME_HTTP && $port == 80) || ($scheme == self::SCHEME_HTTPS && $port == 443)) {
			return $name;
		} else {
			return $name . ':' . $port;
		}
	}
	
	public function getScriptName()
	{
		$filename = isset($_SERVER['SCRIPT_FILENAME']) ? basename($_SERVER['SCRIPT_FILENAME']) : '';
		$selfname = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : $_SERVER['PHP_SELF'];
		if (strcasecmp(basename($selfname), $filename)) {
			$selfname = substr($selfname, 0, strpos($selfname, $filename) + strlen($filename));
		}
		return $selfname;
	}
	
	public function getQueryString()
	{
		if($this->_queryString === null){
			if(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
				$this->_queryString = $_SERVER['QUERY_STRING'];
			}elseif(isset($_SERVER['REQUEST_URI'])) {
				$requestUri = $_SERVER['REQUEST_URI'];
				$schemeAndHttpHost = $this->scheme() . '://' . $this->httpHost();
				if (strpos($requestUri, $schemeAndHttpHost) === 0) {
					$requestUri = substr($requestUri, strlen($schemeAndHttpHost));
				}
				$this->_queryString = '';
				if($pos = strpos($requestUri, '?')){
					$this->_queryString = substr($requestUri, $pos + 1);
				}elseif(($start = strlen($this->getScriptName())) < strlen($requestUri)){
					$this->_queryString = substr($requestUri, $start + 1);
				}
			}
		}
		return $this->_queryString;
	}
}

?>