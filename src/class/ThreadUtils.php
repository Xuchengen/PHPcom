<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : ThreadUtils.php  2012-12-13
 */
!defined('IN_PHPCOM') && exit('Access denied');

class ThreadUtils
{
	public static function selectChildCategory(&$array, $catid, $categorys = array())
	{
		$option = '';
		foreach($categorys as $cid => $category){
			$option .= '<option value="' . $category['catid'] . '"';
			$option .= ( $category['catid'] == $catid) ? ' SELECTED' : '';
			$option .= ">";
			if($category['depth'] > 0){
				$option .= str_pad('', 14 * $category['depth'], " &nbsp; &nbsp;", STR_PAD_LEFT). '|- ';
			}
			$option .= "{$category['catname']}</option>";
			if(isset($array[$cid])) {
				$option .= ThreadUtils::selectChildCategory($array, $catid, $array[$cid]);
			}
		}
		
		return $option;
	}
	
	public static function selectCategory($chanid = 0, $catid = 0)
	{
		phpcom_cache::load('category_' . $chanid);
		if(!isset(phpcom::$G['cache']['category_' . $chanid][0])) {
			return '';
		}
		$catArray = phpcom::$G['cache']['category_' . $chanid];
		return ThreadUtils::selectChildCategory($catArray, $catid, $catArray[0]);
	}
	
	public static function getTagstr($tags) 
	{
		$tagstr = '';
		if ($tags) {
			$tagarray = array_unique(explode("\t", $tags));
			$tagsnew = array();
			if (strpos($tags, ',')) {
				foreach ($tagarray as $key => $value) {
					if ($value) $tagsnew[] = substr($value, strpos($value, ',') + 1);
				}
				$tagstr = implode(',', $tagsnew);
			} else {
				foreach ($tagarray as $key => $value) {
					if ($value) $tagsnew[] = substr($value, strpos($value, ' ') + 1);
				}
				$tagstr = implode(' ', $tagsnew);
			}
		}
		return $tagstr;
	}
	
	public static function formatTestsoft($array)
	{
		if(!isset(phpcom::$G['cache']['softtest'])){
			phpcom_cache::load('softtest');
		}
		$testarray = &phpcom::$G['cache']['softtest'];
		$result = '';
		if(empty($array)){
			foreach ($testarray as $key => $value) {
				if(!empty($value['checked'])){
					$result .= "$key,";
				}
			}
		}else{
			if(!is_array($array)) $array = explode(',', $array);
			foreach ($array as $value){
				if(isset($testarray[$value])){
					$result .= "$value,";
				}
			}
		}
		return trim($result, ', ');
	}
	
	public static function getTestsoftCheckbox($values = null, $onmouseover = true)
	{
		if(!isset(phpcom::$G['cache']['softtest'])){
			phpcom_cache::load('softtest');
		}
		$testarray = &phpcom::$G['cache']['softtest'];
		$testlist = array();
		$result = '';
		if(empty($values)){
			foreach ($testarray as $key => $value) {
				if(!empty($value['checked'])){
					$testlist[] = $key;
				}
			}
		}else{
			$testlist = is_array($values) ? $values : explode(',', $values);
		}
		
		$result = $onmouseover ? '<ul onmouseover="alterStyle(this);" class="checkboxstyle">' : '<ul class="checkboxstyle">';
		foreach ($testarray as $key => $value) {
			$value['color'] = empty($value['color']) ? '' : ' style="color:' . $value['color'] . '"';
			$checked = array_search($key, $testlist) === FALSE ? 0 : 1;
			$result .= '<li';
			$result .= $onmouseover && $checked ? ' class="checked"' : '';
			$result .= '><input class="checkbox" type="checkbox" name="testsoft[' . $key . ']" value="' . $key . '"';
			$result .= $checked ? ' checked' : '';
			$result .= ' /><label' . $value['color'] . '>&nbsp;' . $value['caption'] . '</label></li>';
		}
		$result .= "</ul>\n";
		return $result;
	}
	
	public static function getDownloads($tid = 0)
	{
		$downarray = array();
		$i = 0;
		$sql = "SELECT * FROM " . DB::table('soft_download') . " WHERE tid='$tid' ORDER BY id";
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$i++;
			$row['index'] = $i;
			$downarray[] = $row;
		}
		return $downarray;
	}
	
	public static function formatInputContents($content, $strip = true)
	{
		if($strip) $content = stripslashes($content);
		$content = preg_replace(array("/<style.*?>.*?<\/style>[\n\r\t]*/is", "/<script[^>]*?>.*?<\/script>[\n\r\t]*/is"), "", $content);
		$content = preg_replace("/<(\/?)(b|u|i|s)(\s+[^>]+)?>/is", "[\\1\\2]", $content);
		$content = preg_replace("/<(\/?)strong(\s+[^>]+)?>/is", "[\\1b]", $content);
		$content = preg_replace("/<(\/?)em(\s+[^>]+)?>/is", "[\\1i]", $content);
		$content = preg_replace("/<font\s+color=[\'\"]?([#\w]+?)[\'\"]?[^>]*?>(.*?)<\/font>/i", "[color=\\1]\\2[/color]", $content);
		$content = preg_replace("/<font\s+size=[\'\"]?(\d{1,2}?)[\'\"]?[^>]*?>(.*?)<\/font>/i", "[size=\\1]\\2[/size]", $content);
		$content = preg_replace_callback("/<span(?:\s+[^>]+)?\s+style=[\'\"]+([^\"\<]+)[\'\"]?[^>]*?>(.*?)<\/span>/i", "ThreadUtils::parserSpanTags", $content);
		$content = preg_replace("/<center>([\s\S]+?)<\/center>/i", "[align=center]\\1[/align]", $content);
		$content = preg_replace("/\[size=2\]([\s\S]+?)\[\/size\]/i", "\\1", $content);
		$content = preg_replace("/<p\s+align=[\'\"]?(left|center|right|justify)[\'\"]?[^>]*?>(.*?)<\/p>/i", "[align=\\1]\\2[/align]", $content);
		$content = preg_replace("/<div\s+align=[\'\"]?(left|center|right|justify)[\'\"]?[^>]*?>(.*?)<\/div>/i", "[align=\\1]\\2[/align]", $content);
		$content = preg_replace("/<p(?:\s+[^>]+)?\s+style=[\'\"]?text-align\s*:\s*(left|center|right|justify);?[\'\"]?[^>]*?>(.*?)<\/p>/is", "[align=\\1]\\2[/align]", $content);
		$content = preg_replace("/<div(?:\s+[^>]+)?\s+style=[\'\"]?text-align\s*:\s*(left|center|right|justify);?[\'\"]?[^>]*?>(.*?)<\/div>/is", "[align=\\1]\\2[/align]", $content);
		$content = preg_replace("/\s*<blockquote[^>]*>([\s\S]+?)<\/blockquote>/i", "[quote]\\1[/quote]", $content);
		$content = preg_replace("/<div class=\"syntax\">([\s\S]+?)<\/div>/i", "\\1", $content);
		$content = preg_replace("/\s*<pre\s+class=\"brush:\s*([A-Za-z0-9_#]+);.*?>([\s\S]+?)<\/pre>/is", "\n[code=\\1]\\2[/code]", $content);
		$content = preg_replace("/<img\s+[^>]*?src\s*=\"\s*(\'|\")(.*?)\\1[^>]*?\/?>/i", "[img]\\2[/img]", $content);
		$content = preg_replace("/<a(?:\s+[^>]+)?\s+href=\"\s*(https|http|ftp)([^\"]+?)\s*\"[^>]*>([\s\S]+?)<\/a>/i", "[url=\\3]\\4[/url]", $content);
		$content = preg_replace("/<(p|div)[^>]*?>\s*([\s\S]+?)<\/(p|div)>/is", "[___P___]\\2[/___P___]", $content);
		$content = preg_replace("/\<br[^\>]*?\>/si", "[___BR___]", $content);
		$content = preg_replace("/((&nbsp;){8,8}|( &nbsp;){4,4}|(&nbsp; ){4,4})/", "\\t", $content);
		$content = str_replace("&nbsp;", " ", $content);
		$content = preg_replace("/&(quot|#34);/i", '"', $content);
		$content = preg_replace("/&(amp|#38);/i", "&", $content);
		$content = str_replace("\r\n", "\n", $content);
		$content = strip_tags($content);
		$content = str_replace(array('[___P___]', '[/___P___]', '[___BR___]'), array('<p>', '</p>', '<br/>'), $content);
		$content = addslashes($content);
		return trim($content);
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
}
?>