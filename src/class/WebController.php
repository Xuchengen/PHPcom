<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : WebController.php  2012-7-15
 */
!defined('IN_PHPCOM') && exit('Access denied');

abstract class WebController
{
	public $httpStatusCode = 200;
	protected $request;
	protected $instdir = '';
	protected $htmlstatus = 0;
	protected $domain = '';
	protected $chanid = 0;
	protected $title, $webname, $website, $charset, $templatedir, $tpldir;
	protected $keyword, $description, $htmlext, $channelname;
	protected $uid = 0;
	protected $page = 1;
	protected $username = '';
	protected $groupid = 6;
	protected $credits, $usergroup, $adminscript, $allowadmin;
	protected $time, $todaytime, $timeoffset;
	protected $action, $version;
	protected $baseUrlArgs = array();
	protected $ishtml = false;
	protected $htmlFile;
	protected $chandomain;
	protected $formtoken = '';
	protected $moduleTables = array('article' => 'article_thread', 'soft' => 'soft_thread', 
			'special' => 'special_thread', 'photo' => 'photo_thread', 'video' => 'video_thread');

	const USER_VISIT_PERMISSION = 1;

	public function __construct(Web_HttpRequest $request)
	{
		$this->request = $request;
		$this->chanid = intval($this->request->getPost('chanid', 0));
		$this->time = time();
		$this->action = $request->getParam('action');
		$this->instdir = phpcom::$G['instdir'];
		$this->htmlstatus = phpcom::$setting['htmlstatus'];
		$this->htmlext = phpcom::$setting['htmlext'];
		$this->webname = phpcom::$setting['webname'];
		$this->website = phpcom::$setting['website'];
		$this->channelname = $this->webname;
		$this->domain = empty(phpcom::$setting['absoluteurl']) ? $this->instdir : $this->website . $this->instdir;
		$this->charset = CHARSET;
		$this->templatedir = phpcom::$setting['templatedir'];
		$this->tpldir = TEMPLATE_DIR . '/' . $this->templatedir;
		$this->keyword = phpcom::$setting['keyword'];
		$this->description = phpcom::$setting['description'];
		$this->title = phpcom::$setting['webname'];
		$this->version = 'Powered by <a href="http://www.phpcom.net" target="_blank"><strong>PHPcom</strong></a>&trade; ' . phpcom::$setting['version'];
		$this->version .= ' &copy;2010 - '.date('Y').' <a href="http://www.cnxinyun.com" target="_blank">cnxinyun.com</a>';
		$this->uid = phpcom::$G['uid'];
		$this->username = phpcom::$G['username'];
		$this->groupid = phpcom::$G['group']['groupid'];
		$this->credits = phpcom::$G['member']['credits'];
		$this->usergroup = phpcom::$G['group']['grouptitle'];
		$this->adminscript = phpcom::$config['admincp']['script'];
		$this->allowadmin = phpcom::$G['member']['allowadmin'];
		$this->page = max(1, intval($this->request->query('page', 1)));
		$this->timeoffset = phpcom::$setting['timeoffset'] * 3600;

		$this->todaytime = TIMESTAMP - (TIMESTAMP + $this->timeoffset) % 86400 + $this->timeoffset;
		$this->formtoken = formtoken();
		$this->attachurl = phpcom::$setting['attachurl'];
	}

	public function setHtmlMethod($value)
	{
		$this->ishtml = $value;
		if($value){
			$this->htmlstatus = 1;
		}
	}

	public function writeToHtml($content = '')
	{
		if($this->htmlstatus && $this->checkHtmlKey() && $this->htmlFile && $this->httpStatusCode == 200){
			$htmlfile = $this->htmlFile;
			if(!strpos(basename($htmlfile), '.')){
				$htmlfile = trim($htmlfile, ' /') . '/index.html';
			}
			$filename = PHPCOM_ROOT . '/' . $htmlfile;
			fwrite_content($filename, $content);
		}
	}
	
	protected function checkRequestUri($url = null)
	{
		if($this->ishtml || empty(phpcom::$setting['uricheck'])) return;
		$host = $_SERVER['HTTP_HOST'];
		if(($requesturi = $_SERVER['REQUEST_URI']) && $url) {
			$uri = @parse_url($url);
			if(!empty($uri['host']) && strcasecmp($host, $uri['host'])){
				@header('HTTP/1.1 301 Moved Permanently');
				$_SERVER["REDIRECT_STATUS"] = 301;
				exit(header("location: $url"));
			}
			if(!empty($uri['path'])){
				$path = $uri['path'] . (empty($uri['query']) ? '': '?' . $uri['query']);
				if(strcasecmp($requesturi, $path)){
					@header('HTTP/1.1 301 Moved Permanently');
					$_SERVER["REDIRECT_STATUS"] = 301;
					exit(header("location: $url"));
				}
			}
		}
	}
	
	protected function pageNotFound()
	{
		@header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
		@header("Status: 404 Not Found");
		$_SERVER["REDIRECT_STATUS"] = 404;
		$servername = $this->request->server('SERVER_NAME');
		$hostname = $this->request->server('HTTP_HOST');;
		$serversoft = $this->request->server('SERVER_SOFTWARE');
		$requesturi = $this->request->server('REQUEST_URI');
		$url = "http://{$hostname}{$requesturi}";
		$phpversion = PHP_VERSION;
		$this->httpStatusCode = 404;
		include template('http404');
		exit(0);
	}
	
	protected function formatSize($size)
	{
		static $unit = array(' bytes', ' KB', ' MB', ' GB', ' TB', ' PB');
		$i = 0;
		while ($size > 1024 && ++$i < 6) {
			$size /= 1024;
		}
		$i && $size = round($size, 2);
		return $size . $unit[$i];
	}
	
	protected function processedTime()
	{
		$timer = number_format((microtime(true) - phpcom::$G['starttime']), 6);
		$queries = DB::instance()->querycount;
		$gzipcompress = '';
		if (phpcom::$G['gzipcompress']) {
			$gzipcompress = ', Gzip enabled';
		}
		$appmemory = '';
		if (phpcom::$G['memory']) {
			$appmemory = ', ' . ucwords(phpcom::$G['memory']) . ' On';
		}
		return "Processed in $timer(s), $queries queries$gzipcompress$appmemory";
	}

	public function robotDenied() {
		if (IS_ROBOT) {
			header("HTTP/1.1 403 Forbidden");
			exit(header("Status: 403 Forbidden"));
		}
	}

	protected function loadAjaxHeader()
	{
		@ob_end_clean();
		ob_start();
		//@header('Access-Control-Allow-Origin: *');
		header('Content-Type: text/xml;charset=' . CHARSET);
		header('Expires: -1');
		header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", false);
		header('Pragma: no-cache');
		echo '<?xml version="1.0" encoding="' . CHARSET . '"?>', "\r\n";
		echo "<root><![CDATA[";
	}

	protected function loadAjaxFooter()
	{
		$contents = ob_get_contents();
		ob_end_clean();
		$contents = preg_replace("/([\\x01-\\x08\\x0b-\\x0c\\x0e-\\x1f])+/", ' ', $contents);
		$contents = str_replace(array(chr(0), ']]>'), array(' ', ']]&gt;'), $contents);
		echo $contents;
		echo "]]></root>";
		exit();
	}

	protected function paging($pagenow, $pagecount, $pagesize, $totalrec = 0, $pageurl = '', $pagenum = 7, $pagestats = false, $pageinput = false, $firsturl = '')
	{
		$pagenum = $pagenum ? $pagenum : 5;
		$total = lang('common', 'pagetotal');
		$pageback = lang('common', 'pageback');
		$pagenext = lang('common', 'pagenext');
		$inputcaption = lang('common', 'pageinput');
		function getPageUrl($page, $pageurl, $firsturl){
			if($page > 1){
				return str_replace('{%d}', $page, $pageurl);
			}else{
				return $firsturl ? str_replace('{%d}', '1' , $firsturl) : str_replace('{%d}', '1' , $pageurl);
			}
		}
		$s = '';
		if ($pagestats) {
			$s = "<b>$total$totalrec/$pagesize</b>";
		}
		if ($pagenow == 1) {
			$s .= '<a href="javascript:void(0)" class="prev disable"><em>' . $pageback . '</em></a>';
		} else {
			$s .= '<a href="' . getPageUrl($pagenow - 1, $pageurl, $firsturl) . '" class="prev"><em>' . $pageback . '</em></a>';
		}

		if ($pagecount > 0) {
			$start = max(1, $pagenow - intval($pagenum / 2));
			$end = min($start + $pagenum - 1, $pagecount);
			$start = max(1, $end - $pagenum + 1);
			if ($start > 1) {
				$s .= '<a href="' . getPageUrl(1, $pageurl, $firsturl) . '" class="first">1...</a>';
			}
			for ($i = $start; $i <= $end; $i++) {
				if ($i == $pagenow) {
					$s .= '<a href="javascript:void(0)" class="active">' . $i . '</a>';
				} else {
					$s .= '<a href="' . getPageUrl($i, $pageurl, $firsturl) . '">' . $i . '</a>';
				}
				if ($i >= $pagecount)
					break;
			}
			if ($end < $pagecount) {
				$s .= '<a href="' . getPageUrl($pagecount, $pageurl, $firsturl) . '" class="last">...' . $pagecount . '</a>';
			}
		}
		if ($pagenow >= $pagecount) {
			$s .= '<a href="javascript:void(0)" class="next disable"><em>' . $pagenext . '</em></a>';
		} else {
			$s .= '<a href="' . getPageUrl($pagenow + 1, $pageurl, $firsturl) . '" class="next"><em>' . $pagenext . '</em></a>';
		}
		if ($pageinput) {
			$s .= "<span><input type=\"text\" class=\"pageinput\" title=\"$inputcaption\" size=\"3\" onkeydown=\"if (13==event.keyCode) document.location.href='" . str_replace('{%d}', "'+this.value+'", $pageurl) . "'\" value=\"$pagenow\" /></span>";
		}
		return $s;
	}

	public function formatPageUrl(&$pageurl, $prefix = null)
	{
		if(strpos($pageurl, '{%d}') !== false) return $pageurl;
		$firsturl = $pageurl;
		if (strrpos($pageurl, '/') !== false && strrpos($pageurl, '?') === false){
			$name = substr($pageurl, strrpos($pageurl, '/') + 1);
			if(empty($name)){
				$name = (!empty($prefix) && strrpos($pageurl, $prefix) === false) ? $prefix : 'index.html';
				$pageurl .= $name;
			}
			if(strrpos($name, '.')){
				$pageurl = substr_replace($pageurl, '-{%d}', strrpos($pageurl, '.'), 0);
			}else{
				$pageurl .= "-{%d}";
			}
		}else{
			if(strrpos($pageurl, '.', strpos($pageurl, '?'))){
				$pageurl = substr_replace($pageurl, '-{%d}', strrpos($pageurl, '.'), 0);
			}elseif(strpos($pageurl, '?') && strpos($pageurl, '/') === false){
				$pageurl .= '&page={%d}';
			}else{
				$pageurl .= '-{%d}';
			}
		}
		return $firsturl;
	}

	protected function getReferer($url = '') {
		$referer = $this->request->getPost('referer') ? $this->request->getPost('referer') : $_SERVER['HTTP_REFERER'];
		$referer = substr($referer, -1) == '?' ? substr($referer, 0, -1) : $referer;
		if (strpos($referer, 'member.php?action=login') || strpos($referer, 'login.html')) {
			$referer = $url;
		}
		if (strpos($referer, 'member.php?action=register') || strpos($referer, 'register.html')) {
			$referer = $url;
		}
		$referer = str_replace('&amp;', '&', htmlcharsencode($referer));
		phpcom::$G['referer'] = $referer ? strip_tags($referer) : '/';
		return phpcom::$G['referer'];
	}

	public function getThreadImageUrl($tid, $type = 0, $modules = 'article', &$image = array()){
		$tid = intval($tid);
		$urls = array('thumb' => '', 'preview' => '', 'src' => '');
		if(empty($image) && $tid){
			$image = DB::fetch_first("SELECT tid, attachment, thumb, preview, remote, attachimg FROM " . DB::table('thread_image') . " WHERE tid='$tid'");
		}
		if($image){
			$parse = parse_url(phpcom::$setting['attachurl']);
			$attachurl = !isset($parse['host']) ? phpcom::$G['siteurl'] . phpcom::$setting['attachurl'] : phpcom::$setting['attachurl'];
			if(!empty($image['attachment'])){
				if(substr($image['attachment'], 0, 1) == '/'){
					$urls['thumb'] = $image['attachment'];
				}else{
					$urls['thumb'] = ($image['remote'] ? phpcom::$setting['ftp']['attachurl'] : $attachurl) . $modules . '/' . $image['attachment'];
				}
			}
			
			if(!empty($image['attachimg'])){
				if(substr($image['attachimg'], 0, 1) == '/'){
					$urls['preview'] = $image['attachimg'];
				}else{
					$urls['preview'] = ($image['preview'] ? phpcom::$setting['ftp']['attachurl'] : $attachurl) . $modules . '/' . $image['attachimg'];
				}
			}else{
				$urls['preview'] = $urls['thumb'];
			}
			if(empty($urls['thumb'])){
				$urls['thumb'] = $urls['preview'];
			}
			
			if($type == 1 && $image['thumb']){
				$urls['src'] = generatethumbname($urls['thumb']);
			}elseif($type == 2){
				$urls['src'] = $urls['preview'];
			}else{
				$urls['src'] = $urls['thumb'];
			}
		}
		return $urls;
	}

	public function threadHighlight($highlight)
	{
		$return = '';
		if ($highlight) {
			$string = sprintf('%02d', $highlight);
			$return = ' style="' . ($string[0] ? phpcom::$setting['fontvalue'][$string[0]] : '');
			$return .= $string[1] ? 'color: ' . phpcom::$setting['colorvalue'][$string[1]] : '';
			$return .= '"';
		}
		return $return;
	}

	public function checkVisitPermission($groupids, $credits = 0)
	{
		$groupids = ",$groupids,";

		return false;
	}

	public function checkGroupLevel($level)
	{
		if(empty($level)) return true;
		$groupid = phpcom::$G['groupid'];
		if(in_array($groupid, array(4, 5, 6, 7))){
			return false;
		}
		if(phpcom::$G['usergroup'][$level]['type'] == 'member'){
			return true;
		}
		if($groupid <= 3){
			return $groupid <= $level;
		}
		return $groupid == $level;
	}

	public function getVideoQuality($key = 0, $value = '')
	{
		if(isset(phpcom::$G['channel'][$this->chanid]['quality'])){
			if(is_array($value) && isset($value[$key])){
				return $value[$key];
			}else{
				$qualityArray = phpcom::$G['channel'][$this->chanid]['quality'];
				$qualityArray[0] = 'unknown';
				if(isset($qualityArray[$key])){
					if($key == 0 && $value){
						return $value;
					}else{
						return $qualityArray[$key];
					}
				}
			}
		}
		return 'N/A';
	}

	public function checkCaptcha($value)
	{
		if (!phpcom::$setting['captchastatus']) {
			return TRUE;
		}
		if (!isset(phpcom::$G['cookie']['captcha'])) {
			return FALSE;
		}
		list($code, $time) = explode("\t", decryptstring(phpcom::$G['cookie']['captcha']));
		return $code == strtoupper($value) && TIMESTAMP - 180 > $time;
	}

	public function checkQuestionSet($value) {
		if (!isset(phpcom::$setting['questionstatus'])) {
			return TRUE;
		}
		if (!isset(phpcom::$G['cookie']['questionset'])) {
			return FALSE;
		}
		list($code, $time) = explode("\t", decryptstring(phpcom::$G['cookie']['questionset']));
		return $code == md5($value) && TIMESTAMP - 180 > $time;
	}

	public function checkHtmlKey()
	{
		$key = trim($this->request->query('key'));
		$time = date('YmdH');
		if($this->htmlstatus && $key == md5($time . phpcom::$config['security']['key'])){
			return true;
		}
		return false;
	}
	
	protected function getMiscUrl($var)
	{
		$urlargs = array('page' => 1);
		
		if(is_array($var)){
			$urlargs['chanid'] = intval($var[0]);
			$urlargs['query'] = $var;
		}elseif($arg_list = func_get_args()){
			$urlargs['chanid'] = intval($arg_list[0]);
			$urlargs['query'] = $arg_list;
		}
		return geturl('misc', $urlargs, $this->domain);
	}
	
	protected function getMiscOfUrl($name, $key = 'softtype', $index = 0, $chanid = 0, $catid = 0)
	{
		$chanid = $chanid ? $chanid : $this->chanid;
		$k = $this->getChannelSettingOfIndex($key, $name, $chanid);
		$tmpvar = array($chanid, $catid, 0, 0, 0);
		if(isset($tmpvar[$index])){
			$tmpvar[$index] = $k;
		}
		return $this->getMiscUrl($tmpvar);
	}
	
	protected function getListAnchor($name, $key = 'softtype', $target = 0, $chanid = 0, $domain = null)
	{
		$chanid = $chanid ? $chanid : $this->chanid;
		$domain = $domain ? $domain : $this->chandomain;
		$querystr = '';
		$target = $target ? ' target="_blank"' : '';
		$urlargs = $this->baseUrlArgs;
		if(is_numeric($name)){
			if(isset($urlargs['type']) && !empty($urlargs['type'])){
				$urlargs['type'] .= "-$key-$name";
				return geturl('type', $urlargs, $domain);
			}else{
				$urlargs['catid'] .= "-$key-$name";
				return geturl('threadlist', $urlargs, $domain);
			}
		}else{
			if($index = $this->getChannelSettingOfIndex($key, $name, $chanid)){
				$querystr = "-$key-$index";
			}else{
				$querystr = "-$key";
			}
		}
		if(isset($urlargs['type']) && !empty($urlargs['type'])){
			$urlargs['type'] .= $querystr;
			$url = geturl('type', $urlargs, $domain);
		}else{
			$urlargs['catid'] .= $querystr;
			$url = geturl('threadlist', $urlargs, $domain);
		}
		return "<a$target href=\"$url\">$name</a>";
	}
	
	protected function getChannelSettingOfArray($key, $chanid = 0, $delimiter = ',')
	{
		if(empty($key)) return false;
		$chanid = $chanid ? $chanid : $this->chanid;
		if(isset(phpcom::$G['channel'][$chanid][$key])){
			$tmpArray = explode($delimiter, $delimiter . phpcom::$G['cache']['channel'][$key]);
			unset($tmpArray[0]);
			return $tmpArray;
		}
		return false;
	}
	
	protected function getChannelSettingOfIndex($key, $name, $chanid = 0, $delimiter = ',')
	{
		if(empty($key) || empty($name)) return 0;
		$chanid = $chanid ? $chanid : $this->chanid;
		if(isset(phpcom::$G['channel'][$chanid][$key])){
			$tmpArray = explode($delimiter, $delimiter . phpcom::$G['cache']['channel'][$key]);
			return array_search($name, $tmpArray);
		}
		return 0;
	}
	
	protected function getChannelSettingOfValue($key, $index, $chanid = 0, $delimiter = ',')
	{
		if(empty($key) || empty($index) || !is_numeric($index)) return null;
		$chanid = $chanid ? $chanid : $this->chanid;
		if(isset(phpcom::$G['channel'][$chanid][$key])){
			$tmpArray = explode($delimiter, $delimiter . phpcom::$G['cache']['channel'][$key]);
			if(isset($tmpArray[$index])){
				return addslashes(trim($tmpArray[$index]));
			}
		}
		return null;
	}
	
	protected function getPersonAnchor($persons, $separator = ' ', $limit = 0, $target = 0, $chanid = 0)
	{
		if(empty($persons)) return null;
		$chanid = $chanid ? $chanid : $this->chanid;
		$persons = str_replace(array(chr(0xa3) . chr(0xdc), chr(0xa3) . chr(0xaf), chr(0xa1) . chr(0xa2), ','), '/', $persons);
		$persons = str_replace(array(chr(0xa3) . chr(0xac), chr(0xa1) . chr(0x41), chr(0xef) . chr(0xbc) . chr(0x8c)), '/', $persons);
		$personarray = array_unique(explode('/', $persons));
		$target = $target ? ' target="_blank"' : '';
		$person = '';
		foreach ($personarray as $key => $name) {
			if($limit && $key >= $limit) break;
			$name = trim($name);
			if (preg_match('/^([\x7f-\xff_-]|\w|\.|\s){2,50}$/', $name)) {
				$url = $this->getMiscUrl($chanid, 0, 0, 0, 0, httpurl_encode($name));
				$person .= "$separator<a$target href=\"$url\">$name</a>";
			}
		}
		return $person ? substr($person, strlen($separator)) : '';
	}
}
?>