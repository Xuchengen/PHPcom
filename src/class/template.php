<?php
/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : template.php    2011-7-5 22:52:01
 */
!defined('IN_PHPCOM') && exit('Access denied');

class template {

	var $template_name = 'default';
	var $template_dir = 'templates';
	var $cache_dir = 'cache';
	var $file_ext = '.htm';

	function __construct() {
		$this->template_name = 'default';
		$this->template_dir = PATH_TEMPLATE . '/' . $this->template_name;
		$this->cache_dir = PHPCOM_ROOT . '/date/template';
		$this->file_ext = '.htm';
	}

	public function parse_template($filename, $cachefile = '') {
		$template = '';
		$basefile = $file = basename($filename, $this->file_ext);
		if (empty($cachefile)) {
			$cachefile = $this->cache_dir . '/ct_' . $file . '.tpl.php';
		}
		if (!is_file($filename)) {
			die('Sorry, The file <b>' . basename($filename) . '</b> does not exist.');
		}
		$template = $this->reader($filename);
		$template = "<?php\nif(!defined('IN_PHPCOM')) exit('Access Denied');\nprint <<<EOT\n$template";
		$template = preg_replace_callback("#\<\!\-\-\{include\s+file\=\s*([a-zA-Z0-9_\.\/]{1,100})\s*([^\}])\}\-\-\>#", array($this, 'include_file'), $template);
		$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
		$template = preg_replace("/[\n\r\t]*\<\!\-\-\#(.+?)\#\-\-\>[\n\r\t]*/s", '', $template);
		$template = preg_replace_callback("#\{template=\s*([a-zA-Z0-9_\.\/]{1,100})\s*([^\}])*\}#", array(&$this, 'include_template'), $template);
		$template = preg_replace("/[\n\r\t]*\{date\((.+?)\)\}[\n\r\t]*/is", "\r\nEOT;\r\necho fmdate(\\1);\nprint <<<EOT\n", $template);

		$template = preg_replace("/[\n\r\t]*\{(end|\/if|\/for|end\s+if)\}/is", "\r\nEOT;\r\n}\nprint <<<EOT\n", $template);
		$template = preg_replace("/\{elseif\s+(.+?)\s*\}[\n\r\t]*/is", "\r\nEOT;\r\n} elseif(\\1) {\nprint <<<EOT\n", $template);
		$template = preg_replace("/\{if\s+(.+?)\s*\}[\n\r\t]*/is", "\r\nEOT;\r\nif(\\1){\nprint <<<EOT\n", $template);
		$template = preg_replace("/\{else\}[\n\r\t]*/is", "\r\nEOT;\r\n} else {\nprint <<<EOT\n", $template);
		$template = preg_replace("/\{for\s+([a-zA-Z0-9_\$]+)\s*\=\s*([a-zA-Z0-9_\$\'\"]+) \s*to \s*([a-zA-Z0-9_\$]+)\s*\}[\n\r\t]*/is", "\r\nEOT;\r\nfor(\\1=\\2;\\1<\\3;\\1++){\nprint <<<EOT\n", $template);
		$template = preg_replace("/\{for\s+\((.+?)\s*\)\s*\}[\n\r\t]*/is", "\r\nEOT;\r\nfor(\\1){\nprint <<<EOT\n", $template);
		$template = preg_replace("/\{phpcom::(.+?)\}/is", "\r\nEOT;\r\necho phpcom::\\1;\nprint <<<EOT\n", $template);
		$template = preg_replace("/\{(eval|php)\s+(.+?)\}[\n\r\t]*/is", "\r\nEOT;\r\n\\2\nprint <<<EOT\n", $template);
		$template = preg_replace("/\{echo\s+(.+?)\s*\}[\n\r\t]*/is", "\r\nEOT;\r\necho \\1;\nprint <<<EOT\n", $template);
		$template = preg_replace("/\{(loop|foreach)\s+(\S+)\s+(\S+)\}[\n\r\t]*/is", "\r\nEOT;\r\nforeach(\\2 as \\3) {\nprint <<<EOT\n", $template);
		$template = preg_replace("/\{(loop|foreach)\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*/is", "\r\nEOT;\r\nforeach(\\2 as \\3 => \\4) {\nprint <<<EOT\n", $template);
		$template = preg_replace("/[\n\r\t]*\{(\/foreach|\/loop|end\s+loop)\}/is", "\r\nEOT;\r\n}\nprint <<<EOT\n", $template);
		//eXtensible Template Markup Language
		$template = preg_replace("/\{\@([a-zA-Z0-9_]{1,50})\}/s", '{$this->\\1}', $template);
		$template = preg_replace_callback("#\{\@([a-zA-Z0-9_]{1,50})\.([a-zA-Z0-9_\.]+)\}#s", array(&$this, 'parser_key'), $template);
		$template = preg_replace_callback("#(\\\$)([a-zA-Z0-9_]{1,50})\.([a-zA-Z0-9_\.]{1,150})#s", array(&$this, 'parse_key'), $template);
		$template = preg_replace_callback("#\{phpcom:(loop|for-each)\s+([^\}]+)\}[\n\r\t]*#", array(&$this, 'parser_foreach'), $template);
		$template = preg_replace_callback("#\<phpcom:(loop|for-each)\s+([^>]+)\>[\n\r\t]*#", array(&$this, 'parser_foreach'), $template);
		$template = preg_replace("/\{phpcom:for\s*\((.+?)\s*\)\}[\n\r\t]*/is", "\r\nEOT;\r\nfor(\\1){\nprint <<<EOT\n", $template);
		$template = preg_replace("/\{phpcom:if\s*\((.+?)\s*\)\}[\n\r\t]*/is", "\r\nEOT;\r\nif(\\1){\nprint <<<EOT\n", $template);
		$template = preg_replace("/\{phpcom:elseif\s*\((.+?)\s*\)\}[\n\r\t]*/is", "\r\nEOT;\r\n} elseif(\\1) {\nprint <<<EOT\n", $template);
		$template = preg_replace_callback("#\{phpcom:(if|elseif)\s*([^\}]+)\}[\n\r\t]*#", array(&$this, 'parser_if_test'), $template);
		$template = preg_replace_callback("#\<phpcom:(if|elseif)\s*([^>]+)\>[\n\r\t]*#", array(&$this, 'parser_if_test'), $template);
		$template = preg_replace_callback("#\{phpcom:value-of\s+([^\}]+)\}[\n\r\t]*#", array(&$this, 'parser_valueof'), $template);
		$template = preg_replace_callback("#\<phpcom:value-of\s+([^\/>|^>]+)(\/>|>)[\n\r\t]*(<\/phpcom:value-of>)*[\n\r\t]*#", array(&$this, 'parser_valueof'), $template);
		$callback = array(&$this, 'include_template');
		$template = preg_replace_callback("#\{phpcom:template\s+include\s*=\s*\"([a-zA-Z0-9_\.\/]+)\"([^\}]*)\}[\n\r\t]*#", $callback, $template);
		$template = preg_replace_callback("#\<phpcom:template\s+include\s*=\s*\"([a-zA-Z0-9_\.\/]+)\"([^\/>|^>]+)(\/>|>)[\n\r\t]*(<\/phpcom:template>)*[\n\r\t]*#", $callback, $template);

		$template = preg_replace("/(\{|<)phpcom:ajaxheader\s*(\}|>|\/>)[\n\r\t]*/is", "\r\nEOT;\r\n\$this->loadAjaxHeader();\nprint <<<EOT\n", $template);
		$template = preg_replace("/(\{|<)phpcom:ajaxfooter\s*(\}|>|\/>)[\n\r\t]*/is", "\r\nEOT;\r\n\$this->loadAjaxFooter();\nprint <<<EOT\n", $template);
		$template = preg_replace("/[\n\r\t]*(\{|<)\/phpcom:(for-each|loop)(\}|>)/is", "\r\nEOT;\r\n}\nprint <<<EOT\n", $template);
		$template = preg_replace("/(\{|<)phpcom:else\s*(\}|>|\/>)[\n\r\t]*/is", "\r\nEOT;\r\n} else {\nprint <<<EOT\n", $template);
		$template = preg_replace("/(\{|<)phpcom:(eval|php)(\}|>)[\n\r]*(.+?)(\{|<)\/phpcom:(eval|php)(\}|>)[\n\r\t]*/is", "\r\nEOT;\r\n\\4\nprint <<<EOT\n", $template);

		$template = preg_replace_callback("#\{phpcom:echo\s+(.+?)\s*\}[\n\r\t]*#", array(&$this, 'parser_echo'), $template);
		$template = preg_replace_callback("#\<phpcom:echo\s+(.+?)\s*(\/>|>)[\n\r\t]*#", array(&$this, 'parser_echo'), $template);
		$template = preg_replace_callback("#\{phpcom:([a-zA-Z]{2,20})\s+([^\}]+)\}[\n\r\t]*#", array(&$this, 'parser_markup'), $template);
		$template = preg_replace_callback("#\<phpcom:([a-zA-Z]{2,20})\s+([^\/>|^>]+)(\/>|>)[\n\r\t]*#", array(&$this, 'parser_markup'), $template);

		$template = preg_replace("/[\n\r\t]*(\{|<)\/phpcom:([\w-]+)(\}|>)/is", "\r\nEOT;\r\n}\nprint <<<EOT\n", $template);
		$template = preg_replace("/\{\\\$\\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/s", "\r\nEOT;\r\necho \\1;\nprint <<<EOT\n", $template);
		$template = "$template\r\nEOT;\r\n?>";
		$template = preg_replace("/print <<<EOT*[\f\n\r\t\v]*EOT;[\n\r]*/is", "", $template);
		$template = str_replace("\r\n", "\n", $template);
		
		$this->writer($cachefile, $template);
	}

	private function include_template($matches) {
		$s = "\r\nEOT;\r\ninclude template('{$matches[1]}');\nprint <<<EOT\n";
		return $s;
	}

	private function include_file($matches) {
		try {
			$filename = $this->template_dir . '/include/' . $matches[1];
			if (!is_file($filename))
				$filename = $this->template_dir . '/' . $matches[1];
			$s = $this->reader($filename);
			return $s;
		} catch (Exception $e) {
			return '';
		}
	}

	/**
	 * 文件读取函数
	 * @param  string $filename 文件名
	 * @return string 返回文件内容
	 */
	private function reader($filename) {
		return @file_get_contents($filename);
	}

	/**
	 * 文件写入函数
	 * @param string $filename 文件名
	 * @param string $data 写入内容
	 * @param string $mode 写入模式
	 * @return void
	 */
	private function writer($filename, $data = '', $mode='w') {
		if (trim($filename)) {
			if(empty(phpcom::$config['template']['encoding']) && strcasecmp(CHARSET, 'utf-8') == 0){
				$data = iconv('GBK', 'UTF-8//TRANSLIT//IGNORE', $data);
			}
			$file = @fopen($filename, $mode);
			@fwrite($file, $data);
			@fclose($file);
		}
		if (!is_file($filename)) {
			die('Sorry,' . basename($filename) . ' file write in failed!');
		}
	}

	public function error($message, $tplname='') {
		die($tplname . ' Error:' . $message);
	}

	private function parser_echo($matches)
	{
		$string = trim($matches[1], "\t\r\n ;");
		if($string === '' || $string == '@' || $string == '$'){
			return '';
		}
		if($string{0} == '@'){
			$string = '$this->' . substr($string, 1);
		}
		return "\r\nEOT;\r\necho $string;\nprint <<<EOT\n";
	}

	private function parser_markup($matches)
	{
		$string = $matches[2];
		switch (strtolower($matches[1])) {
			case "function": return $this->parser_function($string);
			case "loop":
			case "foreach":
			case "for-each": return $this->parser_foreach($string);
			case "for": return $this->parser_for($string);
			case "while": return $this->parser_while($string);
			case "hotandnewmenu": return $this->parser_common_function('hotAndNewMenu', $string, 'foreach', true);
			case "hotsearchword": return $this->parser_common_function('hotSearchWord', $string, 'foreach', true);
			case "channel": return $this->parser_common_function('fetchChannel', $string, 'foreach');
			case "category": return $this->parser_common_function('fetchCategory', $string, 'foreach');
			case "basecategory": return $this->parser_common_function('baseCategory', $string, 'foreach');
			case "fullcategory": return $this->parser_common_function('fullCategory', $string, 'foreach');
			case "categorynav": return $this->parser_common_function('fetchCategoryNav', $string, 'foreach');
			case "threadclass": return $this->parser_common_function('fetchThreadClass', $string, 'foreach');
			case "member": return $this->parser_common_function('fetchMember', $string, 'foreach');
			case "threadlist": //return $this->parser_common_function('threadList', $string, 'db');
			case "fetchthread": return $this->parser_common_function('fetchThreadArray', $string, 'foreach');
			case "fetchvideo": return $this->parser_common_function('fetchVideo', $string, 'foreach');
			case "formatthread": return $this->parser_common_function('formatThread', $string, 'foreach');
			case "friendlink": return $this->parser_common_function('friendLink', $string, 'foreach');
			case "prevthread": return $this->parser_common_function('prevThread', $string, 'echo');
			case "nextthread": return $this->parser_common_function('nextThread', $string, 'echo');
			case "download": return $this->parser_common_function('downloadAddress', $string, 'foreach');
			case "relatedtags": return $this->parser_common_function('relatedTags', $string, 'foreach');
			case "topical":
			case "specialclass": return $this->parser_common_function('fetchSpecialClass', $string, 'foreach');
			case "special":
			case "specialdata": return $this->parser_common_function('fetchSpecialData', $string, 'foreach');
			case "pollvote": return $this->parser_common_function('fetchPollVote', $string, 'foreach');
			case "announce": return $this->parser_common_function('fetchAnnounce', $string, 'foreach');
			case "adverts": return $this->parser_common_function('fetchAadverts', $string, 'foreach');
			case "advert": return $this->parser_common_function('getAdvertise', $string, 'echo');
			case "attachimg": return $this->parser_common_function('fetchAttachment', $string, 'foreach');
			case "syscount": return $this->parser_common_function('getSysCount', $string, 'if');
			case "threadcomments":
			case "comments": return $this->parser_common_function('fetchComments', $string, 'foreach');
			case "topicalcomments": return $this->parser_common_function('topicalComments', $string, 'foreach');
			default:
				break;
		}
		return null;
	}
	
	private function varexport($array)
	{
		if(empty($array) && !is_array($array)){
			return 'array()';
		}
		$stripkey = array('select','test' ,'value', 'key', 'var', 'in', 'caption', 'extract', 'extract-type', 'extract-prefix');
		$output = 'array(';
		foreach($array as $key => $val){
			if(!is_string($key) || in_array($key, $stripkey)){
				continue;
			}
			if(is_numeric($val) || strpos(trim($val), '$') === 0){
				$output .= "'$key' => $val,\n\t";
			}else{
				$output .= "'$key' => '$val',\n\t";
			}
		}
		return rtrim($output, ", \r\n\t") . ')';
	}

	private function parseArguments($string)
	{
		$output = array();
		if(!empty($string)){
			$string = stripslashes($string);
			$string = str_replace(array('&lt;', '&gt;'), array('<', '>'), $string);
			if(preg_match_all('/([a-zA-Z0-9\-]+)="([^"]+)"/', $string, $matchs)){
				foreach($matchs[2] as $key => $v){
					$k = strtolower(trim($matchs[1][$key]));
					if($k != 'caption'){
						$v = trim($v, "\t\r\n");
						if(strpos($v, '@') !== false && strpos($v, "'") === false){
							$v = str_replace('@', '$this->', $v);
						}
						if($v{0} == '@'){
							$v = '$this' . str_replace('@', '->', $v);
						}
						$output[$k] = str_replace('&quot;', '"', $v);
					}
				}
			}
		}
		return $output;
	}

	private function parser_key($matches)
	{
		$tmp = '';
		$array = explode('.', $matches[2]);
		foreach($array as $key){
			if(is_numeric($key)){
				$tmp .= "[$key]";
			}elseif(trim($key) !== ''){
				$tmp .= "['$key']";
			}else{
				$tmp .= ".";
			}
		}
		return '{$this->' . $matches[1] . $tmp . '}';
	}

	private function parse_key($matches)
	{
		$tmp = '';
		$array = explode('.', $matches[3]);
		foreach($array as $key){
			if(is_numeric($key)){
				$tmp .= "[$key]";
			}elseif(trim($key) !== ''){
				$tmp .= "['$key']";
			}else{
				$tmp .= ".";
			}
		}
		return '$' . $matches[2] . $tmp;
	}

	private function parser_foreach($matches)
	{
		$params = $this->parseArguments($matches[2]);
		if(!isset($params['select'])){
			return "Fatal error: (foreach)template language is not legal\r\nEOT;\n{\nprint <<<EOT\n";
		}
		$s = "\r\nEOT;\r\n";
		if(isset($params['var']) && $params['var']){
			$s .= '$'.trim($params['var'], '$;') . ";\r\n";
		}
		if(isset($params['in']) && $params['in']){
			$s .= '$'.trim($params['in'], '$;+') . "=0;\r\n";
		}
		if(strpos($params['select'], '(')){
			$variablename = empty($params['variable']) ? '$array_expression' : '$'. trim($params['variable'], "\$ \t\r\n;");
			$s .= "$variablename = " . $params['select'] . ";\r\n";
			if(isset($params['count']) && $params['count']){
				$s .= '$'.trim($params['count'], '$;') . "=count($variablename);\r\n";
			}
			$s .= "foreach($variablename";
		}else{
			if(isset($params['count']) && $params['count']){
				$s .= '$'.trim($params['count'], '$;') . "=count(".trim($params['select']).");\r\n";
			}
			$s .= "foreach(" . $params['select'];
		}
		if(isset($params['value']) && $params['value']){
			$s .= ' as ';
			if(isset($params['key'])){
				if($params['key']{0} != '$'){
						$s .= '$';
					}
					$s .= $params['key'] . ' => ';
			}
			if($params['value']{0} != '$'){
					$s .= '$';
				}
				$s .= $params['value'];
		}else{
			$s .= ' as $key => $value';
		}
		$s .= ") {\r\n";
		if(isset($params['in']) && $params['in']){
			$s .= '$'.trim($params['in'], "\$;+\r\n") . "++;\r\n";
		}
		$s .= "print <<<EOT\r\n";
		return $s;
	}

	private function parser_for($arguments)
	{
		$params = $this->parseArguments($arguments);
		if(!isset($params['expr']) && !isset($params['init']) && !isset($params['select'])){
			return "Fatal error: (foreach)template language is not legal\r\nEOT;\n{\nprint <<<EOT\n";
		}
		$s = "\r\nEOT;\r\nfor(";
		if(isset($params['select'])){
			$s .= $params['select'];
		}elseif(isset($params['expr'])){
			$s .= $params['expr'];
		}else{
			$s .= $params['init'] . '; ';
			$s .= $params['test'] . '; ';
			$s .= $params['incr'];
		}
		$s .= ") {\nprint <<<EOT\r\n";
		return $s;
	}

	private function parser_while($arguments)
	{
		$params = $this->parseArguments($arguments);
		if(!isset($params['test']) && !isset($params['select'])){
			return "Fatal error: (while)template language is not legal\r\nEOT;\n{\nprint <<<EOT\n";
		}
		$s = "\r\nEOT;\r\n";
		if(isset($params['var']) && $params['var']){
			$s .= '$'.trim($params['var'], '$;') . ";\r\n";
		}
		if(isset($params['in']) && $params['in']){
			$s .= '$'.trim($params['in'], '$;+') . "=0;\r\n";
		}
		$s .= "while(";
		if(isset($params['select'])){
			$s .= $params['select'];
		}elseif(isset($params['test'])){
			$s .= $params['test'];
		}else{
			return "Fatal error: (while)template language is not legal\r\nEOT;\n{\nprint <<<EOT\n";
		}
		$s .= ") {\r\n";
		if(isset($params['in']) && $params['in']){
			$s .= '$'.trim($params['in'], '$;+') . "++;\r\n";
		}
		$s .= "print <<<EOT\r\n";
		return $s;
	}

	private function parser_if_test($matches)
	{
		$params = $this->parseArguments($matches[2]);
		if(!isset($params['test'])){
			return "Fatal error: ({$matches[1]})template language is not legal\r\nEOT;\n{\nprint <<<EOT\n";
		}
		$s = "\r\nEOT;\r\nif(";
		if(strcasecmp($matches[1], 'elseif') == 0){
			$s = "\r\nEOT;\r\n} elseif(";
		}
		$s .= $params['test'];
		$s .= ") {\nprint <<<EOT\r\n";
		return $s;
	}

	private function parser_valueof($matches)
	{
		$params = $this->parseArguments($matches[1]);
		if(!isset($params['select'])){
			return "Fatal error: (value-of)template language is not legal\r\nEOT;\n{\nprint <<<EOT\n";
		}
		$params['disable-output-escaping'] = isset($params['disable-output-escaping']) ? $params['disable-output-escaping'] : false;
		$outvar = isset($params['output-variable']) ? boolval($params['output-variable']) : false;
		$s = $outvar ? '' : "\r\nEOT;\r\necho ";
		$var = '';
		if($params['select']{0} == '@') {
			if(strpos($params['select'], '.')){
				list($name, $string) = explode('.', $params['select'], 2);
				$var = '$this' . str_replace('@', '->', $name) . $this->parse_key($string);
			}else{
				$var = '$this' . str_replace('@', '->', $params['select']);
			}
		}else{
			if(isset($params['this']) && boolval($params['this'])){
				$var = '$this->';
			}
			$var .= $params['select'];
		}

		if(isset($params['output-int-value']) && boolval($params['output-int-value'])){
			$s .= "intval($var)";
		}elseif(isset($params['output-js-document']) && boolval($params['output-js-document'])){
			$s .= "output_js_document($var)";
		}elseif(isset($params['format-javascript']) && boolval($params['format-javascript'])){
			$s .= "addslashes($var)";
		}elseif(isset($params['output-format-size']) && boolval($params['output-format-size'])){
			$s .= "formatbytes($var)";
		}elseif(isset($params['output-date-value']) && boolval($params['output-date-value'])){
			$format = isset($params['format']) ? $params['format'] : 'dt';
			$type = isset($params['type']) ? $params['type'] : '';
			$s .= "fmdate($var, '$format', '$type')";
		}else{
			if(isset($params['output-strip-tags']) && boolval($params['output-strip-tags'])){
				$var = "strip_tags($var)";
			}
			$params['length'] = isset($params['length']) ? intval($params['length']) : 0;
			if($params['length'] > 0){
				$params['ellipsis'] = isset($params['ellipsis']) ? strip_tags($params['ellipsis']) : '';
				$var = "cutstr($var, {$params['length']}, '{$params['ellipsis']}')";
			}
			if(!boolval($params['disable-output-escaping'])){
				$flags = isset($params['flags']) ? $params['flags'] : null;
				if(empty($flags)){
					$s .= "htmlcharsencode($var)";
				}else{
					$s .= "htmlcharsencode($var, '$flags')";
				}
			}else{
				$s .= $var;
			}
		}
		if(!$outvar) {
			$s .= ";\nprint <<<EOT\n";
		}
		return $s;
	}

	private function parser_common_function($funcName, $arguments = null, $loop = 'foreach', $noArgs = false)
	{
		$params = $this->parseArguments($arguments);
		$resultvar = isset($params['value']) && $params['value'] ? '$'.trim($params['value'], '$ ;') : '$row';
		$var = isset($params['var']) && $params['var'] ? '$'.trim($params['var'], '$ ;') . ";\r\n" : '';
		$key = isset($params['key']) && $params['key'] ? '$'.ltrim($params['key'], '$ ') . ' => ' : '';
		$invar = isset($params['in']) && $params['in'] ? '$'.trim($params['in'], '$;+ ') . "++;\r\n" : '';
		$extract = isset($params['extract']) ? $params['extract'] : '';
		if(!empty($extract)){
			$extractPrefix = isset($params['extract-prefix']) ? "EXTR_PREFIX_ALL, '".$params['extract-prefix']."'" : "EXTR_PREFIX_SAME, 'pre'";
			if(substr($extract, 0, 1) == '$'){
				$extract = "\textract($extract, $extractPrefix);\r\n";
			}elseif(boolval($extract, true)){
				$extract = "\textract($resultvar, $extractPrefix);\r\n";
			}else{
				$extract = '';
			}
		}else{
			$extract = '';
		}
		$args = '';
		if(!$noArgs) {
			$args = $this->varexport($params);
		}
		if($invar){
			$var .= trim($invar, ";+\r\n") . "=0;\r\n";
		}
		$s = "\r\nEOT;\r\n$var";
		if($loop == 'foreach' || $loop == 'yes'){
			$variablename = empty($params['variable']) ? '$array_expression' : '$'. trim($params['variable'], "\$ \t\r\n;");
			$s .= "$variablename=\$this->$funcName($args);\r\n";
			if(isset($params['count']) && ($varcount = trim($params['count']))){
				if(is_numeric($varcount)){
					$s .= "\$rowcount = count($variablename);\r\n";
				}else{
					$s .= '$'.trim($varcount, '$;') . " = count($variablename);\r\n";
				}
			}
			$s .= "foreach($variablename as {$key}{$resultvar}) {\r\n$invar";
		}elseif($loop == 'while' || $loop == 'db'){
			$s .= "\$query = \$this->$funcName($args, \$length, \$format, \$ellipsis);\r\n";
			if(isset($params['count']) && ($varcount = trim($params['count']))){
				if(is_numeric($varcount)){
					$s .= "\$rowcount = DB::num_rows(\$query);\r\n";
				}else{
					$s .= '$'.trim($varcount, '$;') . " = DB::num_rows(\$query);\r\n";
				}
			}
			$s .= "while($resultvar = DB::fetch_array(\$query)) {\r\n$invar";
			$s .= "\t\$this->processThreadRowData($resultvar, \$length, \$format, \$ellipsis);\r\n";
		}elseif($loop == 'echo' || $loop == 'print'){
			$s .= "echo \$this->$funcName($args);";
		}else{
			$s .= "if($resultvar = \$this->$funcName($args)) {\r\n{$extract}";
		}
		$s .= "\nprint <<<EOT\n";
		return $s;
	}

	private function parser_function($arguments)
	{
		$params = $this->parseArguments($arguments);
		if(!isset($params['select'])){
			return "Fatal error: (function)template language is not legal\r\nEOT;\n{\nprint <<<EOT\n";
		}
		$loop = isset($params['loop']) ? strtolower($params['loop']) : false;
		$func = $params['select'];
		$s = "\r\nEOT;\r\n";
		if($func{0} == '@') {
			$func = '$this' . str_replace('@', '->', $func);
		}
		if($loop == 'while' || $loop == 'db'){

		}elseif($loop == 'foreach' || $loop == 'yes'){
			$s .= "foreach($func()";
			if(isset($params['value'])){
				$s .= ' as ';
				if(isset($params['key'])){
					if($params['key']{0} != '$'){
						$s .= '$';
					}
					$s .= $params['key'] . ' => ';
				}
				if($params['value']{0} != '$'){
					$s .= '$';
				}
				$s .= $params['value'];
			}else{
				$s .= ' as $key => $value';
			}
			$s .= ") {\r\n";
		}
		$s .= "\nprint <<<EOT\n";
		return $s;
	}
}
?>