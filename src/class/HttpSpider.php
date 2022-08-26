<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : HttpSpider.php  2012-11-16
 */
!defined('IN_PHPCOM') && exit('Access denied');

class HttpSpider
{
	public static function robotContents($url, $pagecharset = null, $level = 1)
	{
		$basicsearch = array(
				"/\<(script|style|textarea)[^\>]*?\>.*?\<\/(\\1)\>/si",
				"/\<!*(--|doctype|html|head|meta|link|body)[^\>]*?\>/si",
				"/<\/(html|head|meta|link|body)\>/si",
				"/([\r\n])\s+/",
				"/\<(table|div)[^\>]*?\>/si",
				"/\<\/(table|div)\>/si"
		);
		$basicreplace = array(
				"",
				"",
				"",
				"\\1",
				"\n\n###table div explode###\n\n",
				"\n\n###table div explode###\n\n"
		);
		$detailsearch = array(
				"/\<(iframe)[^\>]*?\>.*?\<\/(\\1)\>/si",
				"/\<[\/\!]*?[^\<\>]*?\>/si",
				"/\t/",
				"/[\r\n]+/",
				"/(^[\r\n]|[\r\n]$)+/",
				"/&(quot|#34);/i",
				"/&(amp|#38);/i",
				"/&(lt|#60);/i",
				"/&(gt|#62);/i",
				"/&(nbsp|#160|\t);/i",
				"/&(iexcl|#161);/i",
				"/&(cent|#162);/i",
				"/&(pound|#163);/i",
				"/&(copy|#169);/i",
				"/&#(\d+);/e"
		);
		$detailreplace = array(
				"",
				"",
				"",
				"\n",
				"",
				"\"",
				"&",
				"<",
				">",
				" ",
				chr(161),
				chr(162),
				chr(163),
				chr(169),
				"chr(\\1)"
		);
		$robotarray = array();
		$contents = self::getHttpContents($url);
		$html = $contents['content'];
		$pagetitle = $pagecontent = $description = $keywords = '';
		if(empty($pagecharset) && preg_match("/\<meta[^\<\>]+charset=([^\<\>\"\'\s]+)[^\<\>]*\>/i", $html, $matches)){
			$pagecharset = trim($matches[1]);
		}
		if($pagecharset){
			$html = convert_encoding($html, $pagecharset);
		}
		if(preg_match("/<meta[^>]*?name=[\'\"]?description[\'\"]?[^>]*?>/is", $html, $matches)){
			$description = trim($matches[0]);
			if(preg_match("/content=[\'\"]+([^\<\>\"\']+)[\'\"]+/i", $matches[0], $desc)){
				$description = trim($desc[1]);
			}
		}
		if(preg_match("/<meta[^>]*?name=[\'\"]?keywords[\'\"]?[^>]*?>/is", $html, $matches)){
			$keywords = trim($matches[0]);
			if(preg_match("/content=[\'\"]+([^\<\>\"\']+)[\'\"]+/i", $matches[0], $words)){
				$keywords = trim($words[1]);
			}
		}
		$pagetext = self::parseImageUrl(preg_replace($basicsearch, $basicreplace, $html), $url);
		if($level){
			$pagearray = explode("\n\n###table div explode###\n\n", $pagetext);
			$cellarray = array();
			foreach($pagearray as $value) {
				$cell = array(
						'code'	=>	$value,
						'text'	=>	preg_replace("/[\n\r\s]*?/is", "", preg_replace ($detailsearch, $detailreplace, $value)),
						'pagerank'	=>	0,
						'title'	=>	'',
						'process'	=>''
				);
					
				if($cell['text'] != '') {
					$cellarray[] = self::getPageRank($cell, $detailsearch, $detailreplace);
				}
			}
			$subjectarray = $contentarray = array();
			$pagecontent = $pagetitle = '';
			foreach($cellarray as $value) {
				if($value['title'] == 'title') {
					$subjectarray[] = $value;
				} elseif($value['pagerank'] >= 0) {
					$contentarray[] = $value['code'];
				}
			}
			$pagerank = 0;
			foreach($subjectarray as $value) {
				if($pagerank < $value['pagerank'] || empty($pagerank)) {
					$pagetitle = $value['text'];
				}
				$pagerank = $value['pagerank'];
			}
				
			$pagecontent = preg_replace("/\<(p|br)[^\>]*?\>/si", "\n", implode("\n", $contentarray));
			$contentarray = explode("\n", preg_replace($detailsearch, $detailreplace, $pagecontent));
			$pagecontent = '';
			foreach($contentarray as $value) {
				if(trim($value) != '') {
					$pagecontent .= "<p>" . trim($value) . "</p>";
				}
			}
				
		}else{
			if(preg_match("/\<title[^\>]*?\>(.*)\<\/title\>/is", $pagetext, $matches)){
				$pagetitle = trim($matches[1]);
			}
			$pagetext = preg_replace("/\n\n###table div explode###\n\n/", '', $pagetext);
			$pagecontent = preg_replace("/[\r\n]+/", '<br />', preg_replace($detailsearch, $detailreplace, $pagetext));
		}
		$robotarray['charset'] = $pagecharset;
		$robotarray['title'] = $pagetitle;
		$robotarray['description'] = $description;
		$robotarray['keywords'] = $keywords;
		$robotarray['content'] = $pagecontent;
		return $robotarray;
	}

	public static function getContents($url, $charset = null)
	{
		$message = self::getHttpContents($url);
		$content = $message['content'];
		if(strcasecmp($charset , 'auto') == 0){
			if(preg_match("/\<meta[^\<\>]+charset\s*=([^\<\>\"\'\s]+)[^\<\>]*\>/i", $content, $matches)){
				return convert_encoding($content, trim($matches[1]));
			}
		}elseif(!empty($charset)){
			return convert_encoding($content, trim($charset));
		}
		return $content;
	}

	public static function getHttpContents($url, $redirects = 0)
	{
		if($redirects > 9) return '';
		$uri = HttpSpider::parseUri($url);
		$fp = false;
		$timeout = 30.0;
		$contents = array('content' => '', 'url' => $uri['url'], 'StatusCode' => 0,
				'Content-Length' => 0, 'Content-Type' => 'text/plain', 'charset' => '');
		if (function_exists('stream_socket_client')) {
			$fp = @stream_socket_client($uri['socket'], $errno, $errstr, $timeout);
		} elseif (function_exists('fsockopen')) {
			$fp = @fsockopen($uri['host'], $uri['port'], $errno, $errstr, $timeout);
		} elseif (function_exists('pfsockopen')) {
			$fp = @pfsockopen($uri['host'], $uri['port'], $errno, $errstr, $timeout);
		}
		if($fp){
			stream_set_blocking($fp, 1);
			stream_set_timeout($fp, $timeout);
			$path = $uri['path'] . (isset($uri['query']) ? '?' . $uri['query'] : '');
			fputs($fp, "GET $path HTTP/1.0\r\n");
			fputs($fp, "Host: {$uri['host']}\r\n");
			fputs($fp, "Accept: */*\r\n");
			fputs($fp, "Referer: {$uri['url']}\r\n");
			fputs($fp, "User-Agent: Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; phpspider)\r\n");
			fputs($fp, "Pragma: no-cache\r\n");
			fputs($fp, "Cache-Control: no-cache\r\n");
			fputs($fp, "Connection: Close\r\n\r\n");
			$meta = stream_get_meta_data($fp);
			if(empty($meta['timed_out'])){
				$chunked = false;
				while (!feof($fp)) {
					if (($buffer = @fgets($fp)) && ($buffer == "\r\n" || $buffer == "\n")) {
						break;
					}
					if(strpos($buffer, 'HTTP/') === 0){
						list(,$contents['StatusCode']) = explode(" ", $buffer);
					}elseif(strpos($buffer, 'location: ') === 0){
						$contents['location'] = trim(substr($buffer, 10));
					}elseif(strpos($buffer, 'Content-Length: ') === 0){
						$contents['Content-Length'] = trim(substr($buffer, 16));
					}elseif(strpos($buffer, 'Content-Type: ') === 0){
						$contents['Content-Type'] = trim(substr($buffer, 14));
						if(($c = strpos($contents['Content-Type'], 'charset=')) !== false){
							$contents['charset'] = substr($contents['Content-Type'], $c + 8);
						}
					}elseif(strpos($buffer, 'Transfer-Encoding: chunked') === 0){
						$chunked = true;
					}
				}

				if(($contents['StatusCode'] == 302 || $contents['StatusCode'] == 301) && isset($contents['location'])) {
					fclose($fp);
					$contents['url'] = $contents['location'];
					return HttpSpider::getHttpContents($contents['location'], ++$redirects);
				}elseif($contents['StatusCode'] != 200){
					fclose($fp);
					return $contents;
				}
				if($chunked){
					while ($chunk_length = hexdec(trim(fgets($fp)))){
						$buffer = '';
						$read_length = 0;
						while ($read_length < $chunk_length){
							$buffer .= fread($fp, $chunk_length - $read_length);
							$read_length = strlen($buffer);
						}
						$contents['content'] .= $buffer;
						@fgets($fp);
					}
				}else{
					while (!feof($fp)) $contents['content'] .= fread($fp, 4096);
				}
				fclose($fp);
			}
			return $contents;
		}elseif(function_exists('curl_init') && ($ch = curl_init())){
			curl_setopt($ch, CURLOPT_URL,                $uri['url']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,     true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION,     true);
			curl_setopt($ch, CURLOPT_MAXREDIRS,          10);
			curl_setopt($ch, CURLOPT_TIMEOUT,            30);
			curl_setopt($ch, CURLOPT_AUTOREFERER,        TRUE);
			curl_setopt($ch, CURLOPT_USERAGENT,          'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; phpspider)');
			curl_setopt($ch, CURLOPT_REFERER,        	 $uri['url']);
			$contents['content'] = curl_exec($ch);
			$errno = curl_errno($ch);
			$info = curl_getinfo($ch);
			$contents['StatusCode'] = $info['http_code'];
			$contents['Content-Length'] = $info['download_content_length'];
			if(isset($info['content_type'])){
				$contents['Content-Type'] = $info['content_type'];
				if(($c = strpos($contents['Content-Type'], 'charset=')) !== false){
					$contents['charset'] = substr($contents['Content-Type'], $c + 8);
				}
			}
			curl_close($ch);
			return $contents;
		}
		return $contents;
	}

	public static function parseUri($url)
	{
		static $portscheme = array('http' => 80, 'feed' => 80, 'https' => 433, 'ftp' => 21);
		if(($uri = parse_url($url)) && isset($uri['scheme'])){
			if(empty($uri['path'])) $uri['path'] = '/';
			$uri['scheme'] = strtolower($uri['scheme']);
			$urlnew = empty($uri['user'])?'':$uri['user'];
			$urlnew .= empty($uri['pass'])?'':':'.$uri['pass'];
			$urlnew .= empty($uri['host'])?'':((!empty($uri['user']) || !empty($uri['pass']))?'@':'').$uri['host'];
			$urlnew .= empty($uri['port'])?'':':'.$uri['port'];
			$urlnew .= empty($uri['path'])?'':$uri['path'];
			$urlnew .= empty($uri['query'])?'':'?'.$uri['query'];
			$urlnew .= empty($uri['fragment'])?'':'#'.$uri['fragment'];
			if(empty($uri['port'])){
				$uri['port'] = isset($portscheme[$uri['scheme']]) ? $portscheme[$uri['scheme']] : '80';
			}
			$uri['socket'] = ($uri['scheme'] == 'https' ? 'ssl' : 'tcp') . "://{$uri['host']}:{$uri['port']}";
			$uri['url'] = $uri['scheme'] . "://$urlnew";
			return $uri;
		}
		return array('url' => '');
	}

	public static function getPageRank($cellarray, $pattern, $replacement)
	{
		$htmltags = array('title' => 5, 'a' => -1, 'iframe' => -2, 'p' => 1, 'li' => -1, 'input' => -0.1, 'select' => -3, 'form' => -0.1);
		if(strlen($cellarray['text']) > 10) {
			if(strlen($cellarray['text']) > 200) {
				$cellarray['pagerank'] += 2;
			}
			foreach($htmltags as $tags => $rank) {
				if(preg_match_all("/\<{$tags}[^\>]*?\>/is", $cellarray['code'], $temp, PREG_SET_ORDER)){
					$tagsnum = count($temp);
					if($tags == 'title' && $tagsnum > 0) {
						$cellarray['title'] = 'title';
					}elseif($tags == 'a' && $tagsnum > 0) {
						if(preg_match_all("/\<a[^\>]*?\>(.*?)\<\/a>/is", $cellarray['code'], $matches)){
							$matches[2] = preg_replace("/[\n\r\s]*?/is", '', preg_replace($pattern, $replacement, implode('', $matches[1])));
							$anchornum = strlen($matches[2]) / strlen($cellarray['text']);
							$tagsnum *= $anchornum * 10;
						}
					}
					$cellarray['pagerank'] += $tagsnum * $rank;
				}
			}
		}else{
			$cellarray['pagerank'] -= 10;
		}
		if($cellarray['pagerank'] >= 0) {
			$code = preg_replace("/\<(p|br)[^\>]*?\>/si", "\n\n###p br explode###\n\n", $cellarray['code']);
			$codearray = explode("\n\n###p br explode###\n\n", $code);
			if(preg_match_all("/\n\n###p br explode###\n\n/is", $code, $matches, PREG_SET_ORDER) && count($matches) > 2){
				$rank = 0;
				foreach($codearray as $value) {
					$string = preg_replace("/[\n\r\s]*?/is", "", preg_replace ($pattern, $replacement, $value));
					if($string != '') {
						$num = strlen($string);
						if($num <= 25) {
							$rank--;
						} elseif($num > 70 ) {
							$rank = 10;
							continue;
						}
						else {
							$rank++;
						}
					}
				}
				if($rank < 0) {
					$cellarray['pagerank'] += $rank;
				}
			}
		}
		return $cellarray;
	}
	
	public static function parseImageUrl($subject, $baseurl, $bbcoded = true)
	{
		if(preg_match_all("/<img.+src=('|\"|)?(.*)(\\1)([\s].*)?>/ismUe", $subject, $matches, PREG_SET_ORDER)){
			$imagereplace = array();
			foreach($matches as $matche) {
				$url = trim($matche[2], "\r\n\t \"'\0\x0B#");
				$imagereplace['imageold'][] = $matche[0];
				if($bbcoded){
					$imagereplace['imagenew'][] = self::convertAbsoluteUrl($url, $baseurl, '[img]', '[/img]');
				}else{
					$imagereplace['imagenew'][] = self::convertAbsoluteUrl($url, $baseurl, '<img src="', '" />');
				}
			}
			return str_replace($imagereplace['imageold'], $imagereplace['imagenew'], $subject);
		}
		return $subject;
	}
	
	public static function convertAbsoluteUrl($urls, $baseurl, $start = '', $end = '')
	{
		if(empty($urls)) return $urls;
		if(!is_array($urls)) $urls = array('url' => $urls);
		$baseuri = parse_url($baseurl);
		$baseuri['path'] = empty($baseuri['path']) ? '/' : $baseuri['path'];
		$domain = $baseuri['scheme'] . "://" . $baseuri['host'] . (isset($baseuri['port']) ? ':' . $baseuri['port'] : '');
		$basepath = (string)substr($baseuri['path'], 0, strrpos($baseuri['path'], '/')) . '/';
		$newurls = array();
		foreach($urls as $key => $url){
			if(($url = trim($url, "\r\n\t \"'\0\x0B")) === '') continue;
			if(!parse_url($url, PHP_URL_SCHEME)){
				$path = $url[0] == '/' ? $url : $basepath . $url;
				if(strpos($path, './') != false){
					$ret = array();
					$patharray = explode('/', $path);
					foreach($patharray as $dir) {
						if($dir == '..') {
							array_pop($ret);
						}elseif($dir != '.'){
							$ret[] = $dir;
						}
					}
					if($ret[0] !== '' || !isset($ret[1])) array_unshift($ret, '');
					$newurls[$key] = $start . $domain . implode('/', $ret) . $end;
				}else{
					$newurls[$key] = $start . $domain . $path . $end;
				}
			}else{
				$newurls[$key] = $start . $url . $end;
			}
		}
		return isset($newurls['url']) ? $newurls['url'] : $newurls;
	}
	
	public static function convertSize($value) {
		$value = str_replace(array("&nbsp;", "\r", "\n", "\t", ","), '', $value);
		$value = trim($value, " \t\n\r\0\x0B\"'Bb");
		if(!empty($value)){
			switch (strtoupper(substr($value, -1))) {
				case 'P': $value *= 1024;
				case 'T': $value *= 1024;
				case 'G': $value *= 1024;
				case 'M': $value *= 1024;
			}
		}
		return is_numeric($value) ? intval($value) : intval($value);
	}
	
	public static function convertListUrl($urls, $baseurl, $urladd = '')
	{
		$urlnew = array();
		if(is_array($urls)){
			foreach($urls as $url){
				if($url = trim($url, " '\"\r\n\t")){
					if(strpos($url, '#') !== 0 && stripos($url, 'javascript:') !== 0 && stripos($url, 'mailto:') !== 0){
						if(!empty($urladd) && strpos($urladd, '[url]') !== false){
							$url = str_replace('[url]', $url, $urladd);
						}
						$urlnew[] = HttpSpider::convertAbsoluteUrl($url, $baseurl);
					}
				}
			}
			return array_unique($urlnew);
		}
		return $urlnew;
	}
	
	public static function parsePageUrl($url)
	{
		$urls = array();
		if(($url = trim($url)) && parse_url($url, PHP_URL_SCHEME)){
			if (preg_match("/\[(\d+)\-(\d+)\]/i", $url, $matchs)) {
				if($matchs[1] > $matchs[2]){
					for($i = $matchs[1]; $i >= $matchs[2]; $i--){
						$urls[] = str_replace($matchs[0], $i, $url);
					}
				}else{
					for($i = $matchs[1]; $i <= $matchs[2]; $i++){
						$urls[] = str_replace($matchs[0], $i, $url);
					}
				}
			}else{
				$urls[0] = $url;
			}
		}
		return $urls;
	}
	
	public static function pregMatchCut($pattern, $subject, $return = false)
	{
		return self::substring($pattern, $subject, $return);
	}
	
	public static function substring($pattern, $subject, $return = false)
	{
		$result = array(0 => '');
		$rules = explode("\n", trim($pattern, "\r\n"));
		$subrule = '';
		foreach ($rules as $k => $v){
			if($k == 0) continue;
			if($v = trim($v, "\r")){
				$subrule = $v;
				break;
			}
		}
		$pattern = preg_quote(trim($rules[0], "\r\n"), "/");
		$pattern = str_replace(array('\*', '\|'), array('.*?', '|'), $pattern);
		if(strpos($pattern, '\[text\]') !== false){
			if(substr($pattern, -8) == '\[text\]'){
				$pattern = str_replace('\[text\]', '\s*(?P<text>.*?)$', $pattern);
			}else{
				$pattern = str_replace('\[text\]', '\s*(?P<text>.*?)\s*', $pattern);
			}
			if(preg_match("/$pattern/is", $subject, $matches)){
				$result[0] = trim(strip_tags($matches['text']));
			}
			if(!empty($result[0]) && $subrule){
				return self::substring($subrule, $result[0], $return);
			}
		}elseif(strpos($pattern, '\[list\]') !== false){
			$pattern = str_replace('\[list\]', '\s*(?P<list>.*?)\s*', $pattern);
			if(preg_match_all("/$pattern/is", $subject, $matches)){
				$result = $matches['list'];
			}
		}elseif(strpos($pattern, '\[number\]') !== false){
			if(substr($pattern, -10) == '\[number\]'){
				$pattern = str_replace('\[number\]', '\s*(?P<number>.+?)$', $pattern);
			}else{
				$pattern = str_replace('\[number\]', '\s*(?P<number>.+?)\s*', $pattern);
			}
			if(preg_match("/$pattern/is", $subject, $matches)){
				$result[0] = intval(trim(strip_tags($matches['number'])));
			}else{
				$result[0] = 0;
			}
		}elseif(strpos($pattern, '\[d\]') !== false){
			if(substr($pattern, -5) == '\[d\]'){
				$pattern = str_replace('\[d\]', '\s*(?P<value>\d+)', $pattern);
			}else{
				$pattern = str_replace('\[d\]', '\s*(?P<value>\d+)\s*', $pattern);
			}
			if(preg_match("/$pattern/is", $subject, $matches)){
				$result[0] = trim($matches['value']);
			}else{
				$result[0] = 0;
			}
		}elseif(strpos($pattern, '\[D\]') !== false){
			if(substr($pattern, -5) == '\[D\]'){
				$pattern = str_replace('\[D\]', '\s*(?P<value>\D+)', $pattern);
			}else{
				$pattern = str_replace('\[D\]', '\s*(?P<value>\D+)\s*', $pattern);
			}
			if(preg_match("/$pattern/is", $subject, $matches)){
				$result[0] = trim($matches['value']);
			}
		}elseif(strpos($pattern, '\[dd\]') !== false){
			if(substr($pattern, -6) == '\[dd\]'){
				$pattern = str_replace('\[dd\]', '\s*(?P<value>[\d\.]+)', $pattern);
			}else{
				$pattern = str_replace('\[dd\]', '\s*(?P<value>[\d\.]+)\s*', $pattern);
			}
			if(preg_match("/$pattern/is", $subject, $matches)){
				$result[0] = trim($matches['value']);
			}else{
				$result[0] = 0;
			}
		}elseif(strpos($pattern, '\[ddd\]') !== false){
			if(substr($pattern, -7) == '\[ddd\]'){
				$pattern = str_replace('\[ddd\]', '\s*(?P<value>[\d\.\-]+)', $pattern);
			}else{
				$pattern = str_replace('\[ddd\]', '\s*(?P<value>[\d\.\-]+)\s*', $pattern);
			}
			if(preg_match("/$pattern/is", $subject, $matches)){
				$result[0] = trim($matches['value']);
			}else{
				$result[0] = 0;
			}
		}elseif(strpos($pattern, '\[w\]') !== false){
			if(substr($pattern, -5) == '\[w\]'){
				$pattern = str_replace('\[w\]', '\s*(?P<value>\w+)', $pattern);
			}else{
				$pattern = str_replace('\[w\]', '\s*(?P<value>\w+)\s*', $pattern);
			}
			if(preg_match("/$pattern/is", $subject, $matches)){
				$result[0] = trim($matches['value']);
			}
		}elseif(strpos($pattern, '\[ww\]') !== false){
			if(substr($pattern, -6) == '\[ww\]'){
				$pattern = str_replace('\[ww\]', '\s*(?P<value>[\w\.\-\\/\:]+)', $pattern);
			}else{
				$pattern = str_replace('\[ww\]', '\s*(?P<value>[\w\.\-\\/\:]+)\s*', $pattern);
			}
			if(preg_match("/$pattern/is", $subject, $matches)){
				$result[0] = trim($matches['value']);
			}
		}elseif(strpos($pattern, '\[W\]') !== false){
			if(substr($pattern, -5) == '\[W\]'){
				$pattern = str_replace('\[W\]', '\s*(?P<value>\W+)', $pattern);
			}else{
				$pattern = str_replace('\[W\]', '\s*(?P<value>\W+)\s*', $pattern);
			}
			if(preg_match("/$pattern/is", $subject, $matches)){
				$result[0] = trim($matches['value']);
			}
		}elseif(strpos($pattern, '\[size\]') !== false){
			if(substr($pattern, -8) == '\[size\]'){
				$pattern = str_replace('\[size\]', '\s*(?P<size>.+?)$', $pattern);
			}else{
				$pattern = str_replace('\[size\]', '\s*(?P<size>.+?)\s*', $pattern);
			}
			if(preg_match("/$pattern/is", $subject, $matches)){
				$result[0] = self::convertSize(strip_tags($matches['size']));
			}else{
				$result[0] = 0;
			}
		}elseif(strpos($pattern, '\[time\]') !== false){
			if(substr($pattern, -8) == '\[time\]'){
				$pattern = str_replace('\[time\]', '\s*(?P<time>.*?)$', $pattern);
			}else{
				$pattern = str_replace('\[time\]', '\s*(?P<time>.*?)\s*', $pattern);
			}
			if(preg_match("/$pattern/is", $subject, $matches)){
				$time = trim(strip_tags($matches['time']));
				$time = trim(str_replace(array("&nbsp;", "\r", "\n", "\t"), ' ', $time));
				if(empty($time) || ($time = strtotime($time)) == false) $time = time();
				$result[0] = $time;
			}else{
				$result[0] = time();
			}
		}elseif(strpos($pattern, '\[code\]') !== false || strpos($pattern, '\[html\]') !== false){
			if(substr($pattern, -8) == '\[code\]' || substr($pattern, -8) == '\[html\]'){
				$pattern = str_replace(array('\[code\]', '\[html\]'), '\s*(?P<code>.*?)$', $pattern);
			}else{
				$pattern = str_replace(array('\[code\]', '\[html\]'), '\s*(?P<code>.*?)\s*', $pattern);
			}
			if(preg_match("/$pattern/is", $subject, $matches)){
				$result[0] = $matches['code'];
			}
			if(!empty($result[0]) && $subrule){
				return self::substring($subrule, $result[0], $return);
			}
		}elseif(strpos($pattern, '@') === 0){
			$result[0] = trim($pattern, "\r\n\t @");
		}
		return $return ? $result[0] : $result;
	}
	
	public static function substrings($pattern, $subject, $return = true)
	{
		
		$rules = str_replace("\r", "", $pattern);
		if(empty($pattern)) return null;
		$patterns = explode("\n", trim($pattern, "\r\n"));
		$return = true;
		$string = $subject;
		foreach($patterns as $rule){
			if(!empty($rule) && !empty($string)){
				$string = self::substring($rule, $string, $return);
			}
		}
		return $string;
	}
	
	public static function pregQuote($pattern)
	{
		$pattern = preg_quote($pattern, "/");
		$pattern = str_replace(array('\*', '\|'), array('.*?', '|'), $pattern);
		return $pattern;
	}
	
	public static function replace($subject, $rule = '')
	{
		if(empty($rule)) return $subject;
		$rules = explode("\n", trim($rule, "\r\n"));
		foreach($rules as $rule){
			if(empty($rule)) continue;
			if(strpos($rule, '@@@')){
				list($pattern, $replacement, $limit) = explode('@@@', $rule . '@@@');
				$pattern = str_replace(array("'", '"', '/', '\\\\/'), array("\'", '\"', '\/', '\/'), $pattern);
				$limit = intval($limit) > 0 ? intval($limit) : -1;
				$subject = @preg_replace("/$pattern/i", $replacement, $subject, $limit);
			}elseif(strpos($rule, '|||')){
				list($pattern, $replacement) = explode('|||', $rule, 2);
				$pattern = HttpSpider::pregQuote($pattern);
				$subject = preg_replace("/($pattern)/s", $replacement, $subject);
			}elseif(strpos($rule, '###')){
				list($pattern, $replacement) = explode('###', $rule, 2);
				$pattern = HttpSpider::pregQuote($pattern);
				$subject = preg_replace("/($pattern)/is", $replacement, $subject);
			}else{
				$rule = HttpSpider::pregQuote($rule);
				$subject = preg_replace("/($rule)/s", '', $subject);
			}
		}
		return $subject;
	}
	
	public static function getListPageUrls($pageurl, $charset, $listarea, $listurl, $listurladd = '', $descend = 0, $one = false)
	{
		$urlarray = array();
		if($pageurls = HttpSpider::parsePageUrl($pageurl)){
			foreach ($pageurls as $url){
				if($html = HttpSpider::getContents($url, trim($charset))){
					$listhtml = HttpSpider::substring($listarea, $html, true);
					if($listurls = HttpSpider::substring($listurl, $listhtml)){
						$listurls = HttpSpider::convertListUrl($listurls, $url, $listurladd);
						$urlarray = array_merge($urlarray, $listurls);
					}
				}
				if($one) break;
			}
		}
		$urlarray = array_unique($urlarray);
		if($descend) $urlarray = array_reverse($urlarray);
		return $urlarray;
	}
	
	public static function formatRunSystem($string)
	{
		if(empty($string)) return 'WinXP, Win7, Win8';
		$string = stripslashes($string);
		$string = str_replace(array("'", '"'), '', $string);
		$string = str_replace(array(chr(0xa3) . chr(0xdc), chr(0xa3) . chr(0xaf), chr(0xa1) . chr(0xa2), '/', '&nbsp;'), ',', $string);
		$string = str_replace(array(chr(0xa3) . chr(0xac), chr(0xa1) . chr(0x41), chr(0xef) . chr(0xbc) . chr(0x8c)), ',', $string);
		$runsysarray = array_unique(explode(',', $string));
		$runsystems = array();
		$count = 0;
		foreach($runsysarray as $platform) {
			if(preg_match('/^([\x7f-\xff_-]|\.|\w|\s){2,30}$/', $platform)){
				$runsystems[] = trim($platform);
				$count++;
				if($count >=10){
					break;
				}
			}
		}
		return $runsystems ? implode(', ', $runsystems) : 'WinXP, Win7, Win8';
	}
	
	public static function htmlToCode($html, $flag = true)
	{
		$html = preg_replace(array("/<style.*?>.*?<\/style>[\n\r\t]*/is", "/<script[^>]*?>.*?<\/script>[\n\r\t]*/is"), "", $html);
		if($flag){
			$html = preg_replace("/<(\/?)(b|u|i|s)(\s+[^>]+)?>/is", "[\\1\\2]", $html);
			$html = preg_replace("/<(\/?)strong(\s+[^>]+)?>/is", "[\\1b]", $html);
			$html = preg_replace("/<(\/?)em(\s+[^>]+)?>/is", "[\\1i]", $html);
			$html = str_replace(array('[B]', '[/B]'), array('[b]', '[/b]'), $html);
			$html = str_replace(array('[U]', '[/U]'), array('[u]', '[/u]'), $html);
			$html = str_replace(array('[I]', '[/I]'), array('[i]', '[/i]'), $html);
			$html = str_replace(array('[S]', '[/S]'), array('[s]', '[/s]'), $html);
			$html = preg_replace("/<font\s+color=[\'\"]?([#\w]+?)[\'\"]?[^>]*?>(.*?)<\/font>/i", "[color=\\1]\\2[/color]", $html);
			$html = preg_replace("/<font\s+size=[\'\"]?(\d{1,2}?)[\'\"]?[^>]*?>(.*?)<\/font>/i", "[size=\\1]\\2[/size]", $html);
			$html = preg_replace_callback("/<span(?:\s+[^>]+)?\s+style=[\'\"]+([^\"\<]+)[\'\"]?[^>]*?>(.*?)<\/span>/i", "HttpSpider::parserSpanTags", $html);
			$html = preg_replace("/<center>([\s\S]+?)<\/center>/i", "[align=center]\\1[/align]", $html);
			$html = preg_replace("/\[size=2\]([\s\S]+?)\[\/size\]/i", "\\1", $html);
			$html = preg_replace("/<p\s+align=[\'\"]?(left|center|right|justify)[\'\"]?[^>]*?>(.*?)<\/p>/i", "[align=\\1]\\2[/align]", $html);
			$html = preg_replace("/<div\s+align=[\'\"]?(left|center|right|justify)[\'\"]?[^>]*?>(.*?)<\/div>/i", "[align=\\1]\\2[/align]", $html);
			$html = preg_replace("/<p(?:\s+[^>]+)?\s+style=[\'\"]?text-align\s*:\s*(left|center|right|justify);?[\'\"]?[^>]*?>(.*?)<\/p>/is", "[align=\\1]\\2[/align]", $html);
			$html = preg_replace("/<div(?:\s+[^>]+)?\s+style=[\'\"]?text-align\s*:\s*(left|center|right|justify);?[\'\"]?[^>]*?>(.*?)<\/div>/is", "[align=\\1]\\2[/align]", $html);
			$html = preg_replace("/\s*<blockquote[^>]*>([\s\S]+?)<\/blockquote>/i", "[quote]\\1[/quote]", $html);
			$html = preg_replace("/\s*<(span|em|div)\s+class=\"(codeStyle|quoteStyle|blockcodeStyle)\">([\s\S]+?)<\/(span|em|div)>/i", "[quote]\\3[/quote]", $html);
			$html = preg_replace("/<div class=\"syntax\">([\s\S]+?)<\/div>/i", "\\1", $html);
			$html = preg_replace("/\s*<pre\s+class=\"brush:\s*([A-Za-z0-9_#]+);.*?>([\s\S]+?)<\/pre>/is", "\n[code=\\1]\\2[/code]", $html);
			$html = preg_replace("/<img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?>/i", "[img]\\2[/img]", $html);
			$html = preg_replace("/<img\s+[^>]*?src=\s*([^>]+)?\/?>/i", "[img]\\1[/img]", $html);
			$html = preg_replace("/<span.*?>([\s\S]+?)<\/span>/is", "\\1", $html);
			$html = preg_replace("/<font.*?>([\s\S]+?)<\/font>/is", "\\1", $html);
			$html = preg_replace("/<p[^>]*?>\s*([\s\S]+?)<\/p>/is", "[___P___]\\1[/___P___]", $html);
			$html = preg_replace("/<div[^>]*?>\s*([\s\S]+?)<\/div>/is", "[___P___]\\1[/___P___]", $html);
			$html = preg_replace("/(<(p|div)[^>]*?>|<\/(p|div)\>)/i", "", $html);
			$html = str_replace(array('[___P___]', '[/___P___]'), array('<p>', '</p>'), $html);
			$html = preg_replace("/((&nbsp;){8,8}|( &nbsp;){4,4}|(&nbsp; ){4,4})/", "\\t", $html);
			$html = str_replace("&nbsp;", " ", $html);
		}
		$html = preg_replace("/<a.*?>(.*?)<\/a>/is", "\\1", $html);
		$html = preg_replace("/(<iframe.*?>(.*?)<\/iframe>|<iframe.*?>)[\n\r\t]*/is", "", $html);
		$html = preg_replace("/<form.*?>(.*?)<\/form>[\n\r\t]*/is", "\\1", $html);
		$html = preg_replace("/<br.*?>/is", "<br/>", $html);
		$html = preg_replace("/&(quot|#34);/i", '"', $html);
		$html = preg_replace("/&(amp|#38);/i", "&", $html);
		$html = str_replace("\r\n", "\n", $html);
		$html = str_replace("</object></object>", "</object>", $html);
		return $html;
	}
	
	public static function parserSpanTags($matches)
	{
		$text = $matches[2];
		$attr = $matches[1];
		if(preg_match("/background-color\s*:\s*(.*?);/is", $attr, $tmp)){
			if($val = trim($tmp[1])){
				$text = "[bgcolor=$val]{$text}[/bgcolor]";
				$attr = preg_replace("/background-color\s*:\s*(.*?);/is", "", $attr);
			}
		}
		if(preg_match("/color\s*:\s*(.*?);/is", $attr, $tmp)){
			if($val = trim($tmp[1])) $text = "[color=$val]{$text}[/color]";
		}
		if(preg_match("/font-style\s*:\s*italic/i", $attr)) $text = "[i]{$text}[/i]";
		if(preg_match("/text-decoration\s*:\s*underline/i", $attr)) $text = "[i]{$text}[/i]";
		if(preg_match("/text-decoration\s*:\s*line-through/i", $attr)) $text = "[u]{$text}[/u]";
		if(preg_match("/font-weight\s*:\s*(bold|700)/i", $attr)) $text = "[b]{$text}[/b]";
		return $text;
	}
	
	public static function formatContent($content)
	{
		return trim($content);
	}
}
?>