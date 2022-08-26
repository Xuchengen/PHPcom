<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : webrequest.php    2011-12-26
 */
!defined('IN_PHPCOM') && exit('Access denied');
error_reporting(0);

class WebRequestBase{
	protected $timers;
	protected $url;
	protected $uri;
	protected $scheme = 'http';
	protected $method = 'GET';
	protected $autoRedirects = 0;
	protected $error = NULL;
	protected $errno = 0;
	protected $httpStatusCode = 0;
	protected $contentLength = 0;
	protected $contentType = '';
	protected $transferEncodingChunked = false;
	protected $fileName = '';
	protected $destination = '';
	protected $responseHeaders = array();
	protected $postData;
	protected $attachDir;
	protected $attach = array();
	protected $attachInfo = array();
	protected $options = array();
	protected $isFtp = FALSE;

	public $keepAlive = FALSE;
	public $timeout = 30.0;
	public $userAgent = 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; phpspider)';
	public $referer = '';
	public $autoReferer = TRUE;
	public $allowAutoRedirect = FALSE;
	public $maxAutoRedirections = 20;
	public $downloaded = FALSE;
	public $headers = array();
	public $uriScheme = '';
	public $keepOriginal = FALSE;

	public function __construct(){
		$this->attachDir = phpcom::$setting['attachdir'];
		$this->attach['attachdir'] = '';
	}

	public function setUrl($url){
		$this->url = $url;
	}

	public function getUrl(){
		return $this->url;
	}

	public function getUri(){
		return $this->uri;
	}
	
	public function setOptions($options, $value = ''){
		if(is_array($options)){
			$this->options += $options;
		}else{
			$this->options[$options] = $value;
		}
	}
	
	public function setAttachDir($dir = '', $subdir = '', $attdir = TRUE){
		if($dir) {
			$this->attachDir = $dir . ($subdir ? $subdir . '/' : '');
		}
		if($attdir === TRUE) {
			$this->attach['attachdir'] = FileUtils::getAttachmentDir();
		}else{
			$this->attach['attachdir'] = trim($attdir, "\\ /\t\r\n");
		}
		FileUtils::rmkdir($this->attachDir . $this->attach['attachdir']);
	}
	
	public function setAttachs(){
		$this->attach['url'] = $this->url;
		$this->attach['name'] = htmlcharsencode($this->getFileName(), ENT_QUOTES);
		$this->attach['ext'] = $this->getExtension();
		$this->attach['type'] = $this->getContentType();
		$this->attach['size'] = $this->getContentLength();
		$this->attach['thumb'] = '';
		$this->attach['image'] = FileUtils::checkImageExt($this->attach['ext']) ? 1 : 0;
		
		if($this->keepOriginal) {
			$this->attach['attachment'] = $this->attach['name'];
		}else{
			$this->attach['attachment'] = $this->attach['attachdir'] . '/' . FileUtils::fileNameRand($this->attach['ext']);
		}
		$this->attach['filename'] = $this->attachDir . ($dir ? $dir . '/' : '') . $this->attach['attachment'];
	}
	
	public function getAttachs(){
		$this->attach['url'] = $this->url;
		$this->attach['size'] = $this->getContentLength();
		return $this->attach;
	}

	public function getContentLength(){
		if(isset($this->responseHeaders['Content-Length'])){
			return $this->responseHeaders['Content-Length'];
		}else{
			return $this->contentLength;
		}
	}
	
	public function getContentType(){
		if(isset($this->responseHeaders['Content-Type'])){
			return $this->responseHeaders['Content-Type'];
		}else{
			return $this->contentType;
		}
	}
	
	public function getFileName($url = ''){
		$filename = 'index';
		if(empty($url) && isset($this->responseHeaders['FileName'])){
			$filename = $this->responseHeaders['FileName'];
		}else{
			$url = empty($url) ? $this->url : $url;
			if($path = parse_url($url, PHP_URL_PATH)){
				$filename = basename($path);
			}
		}
		$ext = '';
		$fileWithoutExt = $filename;
		$type = $this->getContentType();
		if($pos = strrpos($filename, '.')){
			$fileWithoutExt = substr($filename, 0, $pos);
			$ext = strtolower(substr($fileName, $pos + 1, 10));
		}
		$this->responseHeaders['FileName'] = $fileWithoutExt . '.' . $this->parseTypeExt($type, $ext);
		return $this->responseHeaders['FileName'];
	}

	public function getExtension($fileName = ''){
		if(empty($fileName)){
			$fileName = $this->getFileName();
		}
		$extension = pathinfo($fileName, PATHINFO_EXTENSION);
		return strtolower($extension);
	}
	
	public function parseTypeExt($type, $ext)
	{
		if($imgext = $this->getImageTypeExt($type)){
			return $imgext;
		}
		if(empty($ext)){
			return $this->getMimeTypeExt($type, 'attach');
		}
		return $ext;
	}
	
	public function getImageTypeExt($type)
	{
		switch (strtolower($type)) {
			case "image":
			case "image/jpeg":
			case "image/jpg":
			case "image/pjpeg": return 'jpg';
			case "image/gif": return 'gif';
			case "image/png":
			case "image/x-png": return 'png';
			case "image/x-ms-bmp":
			case "image/bmp": return 'bmp';
			case "image/vnd.wap.wbmp": return 'wbmp';
			case "image/tiff": return 'tif';
		}
		return null;
	}
	
	public function getMimeTypeExt($type, $default = null)
	{
		switch (strtolower($type)) {
			case "image":
			case "image/jpeg":
			case "image/jpg":
			case "image/pjpeg": return 'jpg';
			case "image/gif": return 'gif';
			case "image/png":
			case "image/x-png": return 'png';
			case "image/x-ms-bmp":
			case "image/bmp": return 'bmp';
			case "image/vnd.wap.wbmp": return 'wbmp';
			case "image/tiff": return 'tif';
			case "image/ief": return 'ief';
			case "image/x-icon": return 'ico';
			case "text/plain": return 'txt';
			case "text/html": return 'html';
			case "text/css": return 'css';
			case "text/xml": return 'xml';
			case "text/x-component": return 'htc';
			case "video/x-flv": return 'flv';
			case "application/xhtml+xml": return 'xhtml';
			case "application/x-javascript": return 'js';
			case "application/atom+xml": return 'atom';
			case "application/rss+xml": return 'rss';
			case "application/msword": return 'doc';
			case "application/vnd.openxmlformats-officedocument.wordprocessingml.document": return 'docx';
			case "application/pdf": return 'pdf';
			case "application/vnd.ms-powerpoint": return 'ppt';
			case "application/x-shockwave-flash": return 'swf';
			case "application/java-archive": return 'jar';
			case "application/x-7z-compressed": return '7z';
			case "application/x-rar-compressed": return 'rar';
			case "application/x-zip-compressed":
			case "application/zip": return 'zip';
			case "application/x-gzip": return 'gz';
			case "application/x-tar": return 'tar';
			case "application/x-compressed": return 'tgz';
			case "application/x-compress": return 'z';
			case "application/vnd.android.package-archive": return 'apk';
			case "application/x-bittorrent": return 'torrent';
			case "application/x-iphone": return 'iii';
			case "application/x-internet-signup": return 'ins';
			case "application/x-msdownload": return 'dll';
			case "application/octet-stream": return 'exe';
		}
		return $default;
	}
	
	public function setReferer($value){
		if($value){
			$this->referer = $value;
			$this->headers['Referer'] = $value;
		}
	}

	public function setUserAgent($value){
		if($value){
			$this->userAgent = $value;
			$this->headers['User-Agent'] = $value;
		}
	}

	public function setHeaders($headers, $value = ''){
		if(is_array($headers)){
			$this->headers += $headers;
		}else{
			$this->headers[$headers] = $value;
			if($headers == 'User-Agent'){
				$this->userAgent = $value;
			}elseif($headers == 'Referer'){
				$this->referer = $value;
			}
		}
	}

	public function getHeaders($name = '') {
		if(empty($name)){
			return $this->headers;
		}
		return $this->headers[$name];
	}

	public function getResponseHeaders($name = ''){
		if(empty($name)){
			return $this->responseHeaders;
		}elseif(isset($this->responseHeaders[$name])){
			return $this->responseHeaders[$name];
		}else{
			return FALSE;
		}
	}

	public function setPostData($data){
		$this->postData = $data;
	}

	public function setResponseHeaders($header, $value = ''){
		$header = trim($header);
		$value = trim($value);
		if($header && $value !== ''){
			$this->responseHeaders[$header] = $value;
		}elseif($header){
			list($k, $v) = explode(' ', $header, 2);
			if(is_numeric($k)){
				$this->responseHeaders[$k] = $v;
				if($k == 213){
					$this->responseHeaders['Content-Length'] = intval($v);
				}elseif($k >= 222){
					$this->responseHeaders['HttpStatusCode'] = $k;
				}
			}elseif(is_string($k)){
				$k = str_replace(':', '', $k);
				if($k == 'HTTP/1.1' || $k == 'HTTP/1.0'){
					$this->responseHeaders['HttpStatusCode'] = intval($v);
				}elseif($k == 'Content-Disposition'){
					$this->responseHeaders['Content-Disposition'] = $v;
					if(preg_match('/([attachment|inline]*);*filename*=(.*)/i',$v,$matches)){
						$this->responseHeaders['FileName'] = trim($matches[2],"\r\n\"' ;\t");
					}
				}elseif($k == 'Content-Length'){
					$this->responseHeaders['Content-Length'] = intval($v);
				}elseif($k == 'Location' || $k == 'URI'){
					$this->responseHeaders['Location'] = $v;
					$this->responseHeaders['FileName'] = $this->getFileName($v);
				}elseif($k == 'Transfer-Encoding' && $v == 'chunked'){
					$this->responseHeaders['Transfer-Encoding'] = 'chunked';
					$this->transferEncodingChunked = true;
				}else{
					$this->responseHeaders[$k] = $v;
				}

			}
		}
	}
	
	public function setErrno($errno){
		$this->errno = $errno;
	}
	
	public function errno(){
		if($this->url && ($this->httpStatusCode == 200 || $this->httpStatusCode == 226 || $this->httpStatusCode == 301 || $this->httpStatusCode == 302)) {
			$this->errno = 0;
		}else{
			if($this->errno == 0) {
				$this->errno = -1;
			}
		}
		return $this->errno;
	}

	public function error(){
		return $this->error;
	}

	public function getRequestUri($url = ''){
		if(empty($url)){
			$url = $this->url;
		}
		$uri = @parse_url($url);
		$this->url = $uri['url'] = isset($uri['scheme']) ? $url : "http://$url";
		$uri['scheme'] = isset($uri['scheme']) ? $uri['scheme'] : 'http';
		$uri['path'] = isset($uri['path']) ? $uri['path'] : '/';
		if ($uri['scheme'] == 'http' || $uri['scheme'] == 'feed') {
			$uri['port'] = isset($uri['port']) ? $uri['port'] : 80;
		}elseif($uri['scheme'] == 'https'){
			$uri['port'] = isset($uri['port']) ? $uri['port'] : 443;
		}elseif($uri['scheme'] == 'ftp'){
			$uri['port'] = isset($uri['port']) ? $uri['port'] : 21;
		}
		return $uri;
	}

	public function markAbsoluteUrl($url, $baseurl = '', &$uri = array()){
		$uri = parse_url($url);
		if(isset($uri['scheme']) || empty($baseurl)) {
			$uri['url'] = $url;
			return $uri['url'];
		}
		$baseuri = parse_url($baseurl);
		if(!isset($baseuri['path'])){
			$baseuri['path'] = '/';
		}
		$url = $baseuri['scheme'].'://'.$baseuri['host'] . (isset($baseuri['port']) ? ':' . $baseuri['port'] : '');
		$uri['scheme'] = $baseuri['scheme'];
		$uri['host'] = $baseuri['host'];
		if(isset($baseuri['port'])){
			$uri['port'] = $baseuri['port'];
		}
		if(strpos($uri['path'], '/') === 0) {
			$path = $uri['path'];
			$uri['url'] = $url . $path . (isset($uri['query']) ? '?' . $uri['query'] : '');
			return $uri['url'] . (isset($uri['fragment']) ? '#' . $uri['fragment'] : '');
		}elseif(substr($baseuri['path'], -1) === '/'){
			$path = $baseuri['path'] . $uri['path'];
		}else{
			$path = dirname($baseuri['path']).'/'.$uri['path'];
		}
		$pathReset = array();
		$pathArray = explode('/', $path);
		foreach ($pathArray AS $key => $dir) {
			if($dir === '..'){
				count($pathReset) > 1 && array_pop($pathReset);
			}elseif($dir !== '.'){
				$pathReset[] = $dir;
			}
		}
		$uri['path'] = implode('/', $pathReset);
		$url .= $uri['path'] . (isset($uri['query']) ? '?' . $uri['query'] : '');
		$uri['url'] = str_replace('\\', '/', $url) . (isset($uri['fragment']) ? '#' . $uri['fragment'] : '');
		return $uri['url'];
	}

	protected function timerStart($name) {
		$this->timers[$name]['start'] = microtime(TRUE);
		$this->timers[$name]['count'] = isset($this->timers[$name]['count']) ? ++$this->timers[$name]['count'] : 1;
	}

	protected function timerRead($name) {
		if (isset($this->timers[$name]['start'])) {
			$stop = microtime(TRUE);
			$diff = round(($stop - $this->timers[$name]['start']) * 1000, 2);
			if (isset($this->timers[$name]['time'])) {
				$diff += $this->timers[$name]['time'];
			}
			return $diff;
		}
		return $this->timers[$name]['time'];
	}

	protected function timerStop($name) {
		if (isset($this->timers[$name]['start'])) {
			$stop = microtime(TRUE);
			$diff = round(($stop - $this->timers[$name]['start']) * 1000, 2);
			if (isset($this->timers[$name]['time'])) {
				$this->timers[$name]['time'] += $diff;
			} else {
				$this->timers[$name]['time'] = $diff;
			}
			unset($this->timers[$name]['start']);
		}
		return $this->timers[$name];
	}
}

class HttpWebRequest extends WebRequestBase {
	private $handle = NULL;
	private $body = NULL;

	function __construct($url = '', $request_method = 'GET') {
		parent::__construct();
		if (!empty($url)) {
			$this->url = $url;
		}
	}

	public function send($url = '', $method = 'GET'){
		$this->url = $url ? $url : $this->url;
		$method = strtoupper(trim($method));
		$options = array();
		$options['url'] = $this->url;
		$options['timeout'] = 30.0;
		$options['maxredirects'] = 8;
		//$options['headers']['Referer'] = '';
		$this->httpSocketConnection($options);
	}

	private function fSocketOpen($uri, &$errno, &$errstr, $timeout = 30.0){
		!is_array($uri) && $uri = @parse_url($uri);
		$host = $uri['host'];
		if ($uri['scheme'] == 'http' || $uri['scheme'] == 'feed') {
			$port = isset($uri['port']) ? $uri['port'] : 80;
			$socket = "tcp://$host:$port";
		} elseif ($uri['scheme'] == 'https') {
			$port = isset($uri['port']) ? $uri['port'] : 443;
			$socket = "ssl://$host:$port";
		}elseif($uri['scheme'] == 'ftp'){
			$port = isset($uri['port']) ? $uri['port'] : 21;
			$socket = "tcp://$host:$port";
		} else {
			return FALSE;
		}
		$fp = FALSE;
		if (function_exists('stream_socket_client')) {
			$fp = @stream_socket_client($socket, $errno, $errstr, $timeout);
		} elseif (function_exists('fsockopen')) {
			$fp = @fsockopen($uri['host'], $port, $errno, $errstr, $timeout);
		} elseif (function_exists('pfsockopen')) {
			$fp = @pfsockopen($uri['host'], $port, $errno, $errstr, $timeout);
		}
		stream_set_blocking($fp, 1);
		@stream_set_timeout($fp, $timeout);
		if($errno){
			return FALSE;
		}
		return $fp;
	}

	private function httpSocketConnection(array $options = array()){
		$url = $options['url'];
		$uri = @parse_url($url);
		$options['timeout'] = (float)$options['timeout'];
		$path = isset($uri['path']) ? $uri['path'] . (isset($uri['query']) ? '?' . $uri['query'] : '') : '/';
		$host = $uri['host'];
		$options['headers']['Host'] = $uri['host'] . (isset($uri['port']) ? ":{$uri['port']}" : '');
		$options['maxredirects'] = isset($options['maxredirects']) ? intval($options['maxredirects']) : 1;
		if($uri['scheme'] == 'ftp'){
			$this->isFtp = TRUE;
			$this->handle = $this->ftpWebRequest($uri, $options['timeout']);
			return TRUE;
		}else{
			if (!$fp = $this->fSocketOpen($uri, $errno, $errstr, $options['timeout'])) {
				return FALSE;
			}
			$options['method'] = $options['method'] ? $options['method'] : 'GET';
			$options['headers']['Accept'] = '*/*';
			$options['headers']['Connection'] = 'Close';
			if($this->referer && !isset($options['headers']['Referer'])) $options['headers']['Referer'] = $this->referer;
			empty($options['headers']['User-Agent']) && $options['headers']['User-Agent'] = $this->userAgent;
			$content_length = strlen($options['data']);
			if ($content_length > 0 || $options['method'] == 'POST' || $options['method'] == 'PUT') {
				$options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
				$options['headers']['Content-Length'] = $content_length;
				$options['headers']['Cache-Control'] = 'no-cache';
			}
			if (isset($uri['user'])) {
				$options['headers']['Authorization'] = 'Basic ' . base64_encode($uri['user'] . (!empty($uri['pass']) ? ":" . $uri['pass'] : ''));
			}else{
				if(isset($options['headers']['Authorization'])){
					usset($options['headers']['Authorization']);
				}
			}
			$request = $options['method'] . ' ' . $path . " HTTP/1.0\r\n";
			ksort($options['headers']);
			foreach ($options['headers'] as $name => $value) {
				if ($value !== '') {
					$request .= $name . ': ' . trim($value) . "\r\n";
				}
			}
			$request .= "\r\n\r\n" . $options['data'];
		}
		$timeout = $options['timeout'] - $this->timerRead(__CLASS__) / 1000;
		if ($timeout > 0) {
			stream_set_timeout($fp, $timeout);
			@fwrite($fp, $request);
		}
		$meta = stream_get_meta_data($fp);
		if (!$meta['timed_out']) {
			while (!feof($fp)) {
				if (($buffer = @fgets($fp)) && ($buffer == "\r\n" || $buffer == "\n")) {
					break;
				}
				$this->setResponseHeaders($buffer);
			}
			$this->httpStatusCode = $this->responseHeaders['HttpStatusCode'];
			if($options['maxredirects'] > 1 && ($this->httpStatusCode == 301 || $this->httpStatusCode == 302)){
				$this->url = $this->responseHeaders['Location'];
				$options['url'] = $this->url;
				$options['method'] = 'GET';
				$options['maxredirects']--;
				if(isset($this->responseHeaders['Set-Cookie'])){
					$options['headers']['Set-Cookie'] = $this->responseHeaders['Set-Cookie'];
				}
				fclose($fp);
				return $this->httpSocketConnection($options);
			}
		}
		$this->handle = $fp;
		return TRUE;
	}

	private function ftpWebRequest($uri, $timeout = 30.0) {
		!is_array($uri) && $uri = @parse_url($uri);
		$host = $uri['host'];
		$port = $uri['port'] = isset($uri['port']) ? $uri['port'] : 21;
		$fp = @ftp_connect($host, $port, 30);
		if (!fp) {
			return FALSE;
		}
		if ($timeout > 0) {
			@ftp_set_option($fp, FTP_TIMEOUT_SEC, $timeout);
		}
		//ftp://anonymous:password@www.phpmain.com/file/name.rar
		if(!isset($uri['user']) && !isset($uri['pass'])){
			$uri['user'] = 'anonymous';
			$uri['pass'] = 'anonymous@anonymous.com';
		}
		$ftp_user = isset($uri['user']) ? $uri['user'] : 'anonymous';
		$ftp_pass = isset($uri['user']) ? $uri['pass'] : '';
		if (!@ftp_login($fp, $ftp_user, $ftp_pass)) {
			return FALSE;
		}
		@ftp_pasv($fp, TRUE);
		$path = isset($uri['path']) ? $uri['path'] : '/index.html';
		$ftp_path = dirname($path) . '/';
		$ftp_file = basename($path);
		if (@ftp_chdir($fp, $ftp_path)) {
			$this->httpStatusCode = 200;
			$this->responseHeaders['HttpStatusCode'] = 200;
			$this->responseHeaders['Content-Length'] = @ftp_size($fp, $ftp_file);
			return $fp;
		}
		return FALSE;
	}

	public function close(){
		if($this->handle){
			if($this->isFtp)
				@ftp_close($this->handle);
			else
				@fclose($this->handle);
		}
		$this->handle = NULL;
	}

	public function getBody(){
		$body = '';
		if($this->handle && ($this->httpStatusCode == 200 || $this->httpStatusCode == 226)){
			if($this->isFtp){
				$fp = fopen('php://temp', 'r+');
				$filename = $this->getFileName();
				if (@ftp_fget($this->handle, $fp, $filename, FTP_ASCII, 0)) {
					rewind($fp);
					$body = stream_get_contents($fp);
				}
				@fclose($fp);
				@ftp_close($this->handle);
			}else{
				while (!feof($this->handle)) {
					$body .= fread($this->handle, 4096);
				}
				@fclose($this->handle);
			}
		}
		return $body;
	}

	public function download($fileName = ''){
		$this->setAttachs();
		if($this->handle && ($this->httpStatusCode == 200 || $this->httpStatusCode == 226)){
			if(empty($fileName)){
				$fileName = $this->attach['filename'];
			}
			if($this->isFtp){
				$remoteFile = $this->getFileName();
				$ret = ftp_nb_get($this->handle, $fileName, $remoteFile, FTP_BINARY);
				while ($ret == FTP_MOREDATA) {
					$ret = ftp_nb_continue($this->handle);
				}
				if ($ret != FTP_FINISHED) {
					@ftp_close($this->handle);
					return FALSE;
				}else{
					@ftp_close($this->handle);
					return TRUE;
				}
			}else{
				$file = $fileName;
				if($fp = fopen($file, "w")){
					@flock($fp, LOCK_EX);
					while (!feof($this->handle)) {
						$data = fread($this->handle, 4096);
						@fwrite($fp, $data);
					}
					@fclose($fp);
					@fclose($this->handle);
					return TRUE;
				}
				@fclose($this->handle);
			}
		}
		return FALSE;
	}
}

class CurlWebRequest extends WebRequestBase {
	private $curlHandle = NULL;
	private $curlBody = NULL;

	public function __construct($url = '', $request_method = 'GET') {
		parent::__construct();
		if (!empty($url)) {
			$this->url = $url;
		}
	}

	public function send($url = '', $method = 'GET'){
		$this->url = $url ? $url : $this->url;
		$this->curlHandle = curl_init();
		$method = strtoupper(trim($method));
		curl_setopt($this->curlHandle, CURLOPT_URL,                $url);
		if($this->downloaded){
			curl_setopt($this->curlHandle, CURLOPT_NOBODY,         TRUE);
			curl_setopt($this->curlHandle, CURLOPT_HEADERFUNCTION,     array($this, 'headerCallback'));
		}
		if($method == 'POST'){
			curl_setopt($this->curlHandle, CURLOPT_POST,           1);
			if($this->postData){
				curl_setopt($this->curlHandle, CURLOPT_POSTFIELDS, $this->postData);
			}
		}
		curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER,     TRUE);
		curl_setopt($this->curlHandle, CURLOPT_FOLLOWLOCATION,     $this->maxAutoRedirections > 0);
		curl_setopt($this->curlHandle, CURLOPT_MAXREDIRS,          $this->maxAutoRedirections);
		curl_setopt($this->curlHandle, CURLOPT_TIMEOUT,            30);
		curl_setopt($this->curlHandle, CURLOPT_AUTOREFERER,        TRUE);
		curl_setopt($this->curlHandle, CURLOPT_USERAGENT,          $this->userAgent);
		if($this->referer){
			curl_setopt($this->curlHandle, CURLOPT_REFERER,        $this->referer);
		}
		$this->curlBody = curl_exec($this->curlHandle);
		$this->errno = curl_errno($this->curlHandle);
		$info = curl_getinfo($this->curlHandle);
		$this->httpStatusCode = $info['http_code'];
		$this->contentLength = $info['download_content_length'];
		if($this->contentLength < 1 && isset($info['size_download'])){
			$this->contentLength = $info['size_download'];
		}
		$this->contentType = trim($info['content_type']);
		$this->url = $info['url'];
		$this->attach['name'] = $this->getFileName();
		$this->attach['ext'] = $this->getExtension();
		return $this;
	}

	private function headerCallback($ch, $buffer){
		$this->setResponseHeaders($buffer);
		return strlen($buffer);
	}

	public function getResponse(){
		return $this;
	}

	public function close(){
		$this->curlBody = NULL;
		if($this->curlHandle){
			@curl_close($this->curlHandle);
		}
		$this->curlHandle = NULL;
	}

	public function getBody(){
		if($this->httpStatusCode == 200 || $this->httpStatusCode == 226){
			return $this->curlBody;
		}else{
			return '';
		}
	}

	public function download($fileName = ''){
		$this->setAttachs();
		if($this->errno == 0 && $this->url){
			$file = $fileName ? $fileName : $this->attach['filename'];
			if($fp = fopen($file, "w")){
				curl_setopt($this->curlHandle, CURLOPT_URL,         $this->url);
				curl_setopt($this->curlHandle, CURLOPT_FILE,        $fp);
				curl_setopt($this->curlHandle, CURLOPT_HEADER,      0);
				curl_setopt($this->curlHandle, CURLOPT_NOBODY,      0);
				curl_exec($this->curlHandle);
				$this->errno = curl_errno($this->curlHandle);
				$this->httpStatusCode = curl_getinfo($this->curlHandle, CURLINFO_HTTP_CODE);
				curl_close($this->curlHandle);
				fclose($fp);
				if($this->errno){
					@unlink($file);
					return FALSE;
				}
				return TRUE;
			}
		}
		return FALSE;
	}

	public function __destruct() {
		$this->close();
	}
}

class WebRequest {
	
	public static function getInstance($url = '', $request_method = 'GET'){
		static $_instance;
		if (empty( $_instance )) {
			if(function_exists('curl_init')) {
				$_instance = new CurlWebRequest();
			}else{
				$_instance = new HttpWebRequest();
			}
		}
		if(!empty($url)){
			$_instance->send($url, $request_method);
		}
		return $_instance;
	}
}

?>
