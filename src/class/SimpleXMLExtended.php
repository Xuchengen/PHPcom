<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : SimpleXMLExtended.php  2012-9-22
 */
!defined('IN_PHPCOM') && exit('Access denied');

class SimpleXMLExtended extends SimpleXMLIterator
{
	
	public function addCData($string)
	{
		$dom = dom_import_simplexml($this);
		$cdata = $dom->ownerDocument->createCDATASection($string);
		$dom->appendChild($cdata);
	}

	public function save($filename)
	{
		$xmlText = $this->asXML();
		$doc = new DOMDocument('1.0', 'utf-8');
		$doc->preserveWhiteSpace = false;
		$doc->formatOutput = true;
		$doc->loadXML($xmlText);
		$doc->save($filename);
		return $xmlText;
	}

	public function toArray()
	{
		return $this->sxiToArray($this);
	}
	
	public function toJson()
	{
		return json_encode($this->sxiToArray($this, false));
	}
	
	public function sxiToArray($sxi, $isconv = true){
		$a = array();
		$i = 0;
		for($sxi->rewind(); $sxi->valid(); $sxi->next()) {
			$key = $sxi->key();
			if(!isset($a[$key])){
				$i = 0;$a[$key] = array();
			}
			$keyid = $i;
			if($sxi->hasChildren()){
				$a[$key] = $this->sxiToArray($sxi->current(), $isconv);
				foreach($sxi->current()->attributes() as $k => $v) {
					$a[$key]['attributes'][$k] = $this->strconv($v);
				}
			}else{
				if($attr = $sxi->current()->attributes()){
					if($attr->id){
						$keyid = (string)$attr->id;
					}
					
					foreach($attr as $k => $v) {
						if(isset($a[$key]) && !is_array($a[$key])){
							$a[$key] = array($a[$key]);
						}
						$a[$key][$keyid]['attributes'][$k] = $this->strconv($v, $isconv);
					}
					$a[$key][$keyid]['text'] = $this->strconv($sxi->current(), $isconv);
				}else{
					$a[$key] = $this->strconv($sxi->current(), $isconv);
				}
			}
			$i++;
		}
		return $a;
	}
	
	public function strconv($string, $isconv = true)
	{
		$string = strval($string);
		if($isconv && strcasecmp(CHARSET, 'utf-8') && !preg_match("/^([\x1-\x7f])+$/", $string)){
			if(function_exists('iconv')){
				$string = iconv('UTF-8', CHARSET . '//TRANSLIT//IGNORE', $string);
			}elseif(function_exists('mb_convert_encoding')){
				$string = mb_convert_encoding($string, CHARSET, 'UTF-8');
			}
		}
		return $string;
	}
	
	public static function encoding($string, $to_encoding = 'utf-8', $from_encoding = null)
	{
		$from_encoding = $from_encoding ? $from_encoding : CHARSET;
		if(strcasecmp($to_encoding, $from_encoding)){
			if(is_array($string)){
				$string = eval('return '.iconv($from_encoding, "$to_encoding//TRANSLIT//IGNORE", var_export($string, true)).';');
			}else{
				$string = iconv($from_encoding, "$to_encoding//TRANSLIT//IGNORE", $string);
			}
		}
		return $string;
	}
	
}
?>