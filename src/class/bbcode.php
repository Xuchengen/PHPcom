<?php
/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : bbcode.php    2011-7-5 23:07:06
 */
!defined('IN_PHPCOM') && exit('Access denied');
function highlightcode($text){
	return '';
}

class bbcode {

    public static function &instance() {
        static $instance;
        if (empty($instance)) {
            $instance = new bbcode();
        }
        return $instance;
    }

    private static $blockcodes = array();
    private static $htmlcodes = array();
    private static $highcodes = array();
    private static $codecount = 0;
    private static $htmlcount = 0;
    private static $highcount = 0;

    public static function bbcode2html($content) {
        $textlower = strtolower($content);
        /* $content = preg_replace("/<[^<>]+?>/eis", "self::htmlnbsp('\\0')", $content); */
        if (strpos($textlower, "[code]") !== false && strpos($content, "[/code]") !== false) {
            $content = preg_replace_callback("#\[code\](.+?)\[\/code\]#is", 'bbcode::highlightcode', $content);
        }
        if (strpos($textlower, "[code=") !== false && strpos($content, "[/code]") !== false) {
            $content = preg_replace_callback("#\[code\=(\w+)\](.+?)\[\/code\]#is", 'bbcode::highlightcode', $content);
        }
        if (strpos($textlower, "[html]") !== false && strpos($content, "[/html]") !== false) {
            $content = preg_replace_callback("#\[html\](.*?)\[\/html\]#is", 'bbcode::runcode', $content);
        }
        //$content = preg_replace("/<p>(\xa1\xa1|\xe3\x80\x80)+([\s\S]+?)<\/p>/is","<p>\\2</p>", $content);
        //$content = preg_replace("/<p>\s+([\s\S]+?)<\/p>/is","<p>\\1</p>", $content);
        $content = str_replace(array("\t", '  '), array(' &nbsp; &nbsp; &nbsp; &nbsp;', ' &nbsp;'), $content);
        if (strpos($textlower, '[/url]') !== FALSE) {
            $content = preg_replace_callback("#\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.|mailto:)?([^\r\n\[\"']+?))?\](.+?)\[\/url\]#is", 'bbcode::parser_url', $content);
        }
        if (strpos($textlower, '[/email]') !== FALSE) {
            $content = preg_replace_callback("#\[email(=([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+))?\](.+?)\[\/email\]#is", 'bbcode::parser_email', $content);
        }
        if (strpos($textlower, '[/flash]') !== FALSE) {
        	$content = preg_replace_callback("#\[flash(=(\d{1,4}+)([x|,](\d{1,4}))*)?\](.+?)\[\/flash\]#is", 'bbcode::parser_flash', $content);
        }
        if (strpos($textlower, '[/download]</p>') !== FALSE) {
        	$content = preg_replace("#<p>(\[download(.+?)\[\/download\])<\/p>#is", '\\1', $content);
        }
        if (strpos($textlower, '[/thread]</p>') !== FALSE) {
        	$content = preg_replace("#<p>(\[thread(.+?)\[\/thread\])<\/p>#is", '\\1', $content);
        }
        
        $content = preg_replace(array(
            "/\[color=([#\w]+?)\]/i",
            "/\[color=(rgb\([\d\s,]+?\))\]/i",
            "/\[bgcolor=([#\w]+?)\]/i",
            "/\[size=(\d{1,2}?)\]/i",
            "/\[size=(\d{1,2}(\.\d{1,2}+)?(px|pt)+?)\]/i",
            "/\[font=([^\[\<]+?)\]/i",
            "/\[align=(left|center|right|justify)\]/i",
            "/\[p=(\d{1,2}|null), (\d{1,2}), (left|center|right)\]/i",
            "/\[float=(left|right)\]/i"
                ), array(
            "<font color=\"\\1\">",
            "<font style=\"color:\\1\">",
            "<span style=\"background-color:\\1;\">",
            "<font size=\"\\1\">",
            "<font style=\"font-size: \\1\">",
            "<font face=\"\\1 \">",
            "<p align=\"\\1\">",
            "<p style=\"line-height: \\1px; text-indent: \\2em; text-align: \\3;\">",
            "<span style=\"float: \\1;\">"
                ), $content);
        $content = str_replace(array(
            '[/color]', '[/size]', '[/font]', '[/align]', '[/bgcolor]', '[b]', '[/b]', '[s]', '[/s]', '[hr]', '[/p]',
            '[i]', '[/i]', '[u]', '[/u]', '[list]', "[li]", '[/li]', '[/list]', '[indent]', '[/indent]', '[/float]', '\\\\[', '\\\\]'
                ), array(
            '</font>', '</font>', '</font>', '</p>', '</span>', '<strong>', '</strong>', '<strike>', '</strike>', '<hr class="l" />', '</p>',
            '<i>', '</i>', '<u>', '</u>', '<ul>', '<li>', '</li>', '</ul>', '<blockquote>', '</blockquote>', '</span>', '<span>[</span>', '<span>]</span>'
                ), $content);

        if (strpos($textlower, '[/img]') !== FALSE) {
        	$content = preg_replace_callback("#\[img(=(\d{1,4}+)([x|,](\d{1,4}))*)?\]\s*([^\[\<\r\n]+?)\s*\[\/img\]#is", 'bbcode::parser_img', $content);
        }

        if (strpos($textlower, "[quote]") !== FALSE && strpos($textlower, "[/quote]") !== false) {
            $content = preg_replace_callback("#\[quote\](.*?)\[\/quote\]#is", 'bbcode::blockquote', $content);
        }
        $content = preg_replace("/^<p>\s*<\/p>|<p>\s*<\/p>$/i","", $content);
        $content = preg_replace("/^<div>\s*<\/div>|<div>\s*<\/div>$/i","", $content);
        //$content = str_replace('<p> </p>', '<p>&nbsp;</p>', $content);

        if (self::$htmlcodes) {
            foreach (self::$htmlcodes as $key => $value) {
                $content = str_replace("<___PHPCOM_HTMLCODE_{$key}___>", $value, $content);
            }
            self::$htmlcodes = array();
            self::$htmlcount = 0;
        }
        if (self::$highcodes) {
            foreach (self::$highcodes as $key => $value) {
                $content = str_replace("<___PHPCOM_HIGHLIGHTCODE_{$key}___>", $value, $content);
            }
            self::$highcodes = array();
            self::$highcount = 0;
        }
        if (self::$blockcodes) {
            foreach (self::$blockcodes as $key => $value) {
                $content = str_replace("<___PHPCOM_BLOCKCODE_{$key}___>", $value, $content);
            }
            self::$blockcodes = array();
            self::$codecount = 0;
        }
        return $content;
    }

    public static function htmlnbsp($content) {
    	$content = str_replace(array('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', '&nbsp;&nbsp;'), array("\t", ' &nbsp;'), $content);
        $content = str_replace(array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp;'), array("\t", ' '), $content);
        return $content;
    }

    public static function html2bbcode($content) {
        $content = str_replace(
                array('<b>', '</b>', '<i>', '</i>', '<u>', '</u>', '<strong>', '</strong>'), array('[b]', '[/b]', '[i]', '[/i]', '[u]', '[/u]', '[b]', '[/b]'), $content);
        return $content;
    }

    public static function bbimg($url) {
        $url = addslashes(str_replace('&amp;', '&', $url));
        return "<img src=\"$url\" />";
    }

    public static function blockcode($matches) {
        self::$codecount++;
        $codeid = self::$codecount;
        $code = $matches[1];
        $code = str_replace(array("\t", '  '), array(' &nbsp; &nbsp; &nbsp; &nbsp;', ' &nbsp;'), $code);
        $code = str_replace(array("\r\n", "\n", "\r"), array('<br />', '<br />', '<br />'), $code);
        $code = str_replace('\\"', '"', $code);
        $code = preg_replace('/^(<br \/>)?(.+?)(<br \/>)$/', '\\2', $code);
        $code = str_replace("<br />", "</li><li>", $code);
        self::$blockcodes[$codeid] = "<div class=\"copycodeStyle\"><em onclick=\"copyBlockCode('blockcode_$codeid');\">" . lang('common', 'copycode') . "</em></div><div class=\"blockcodeStyle\" id=\"blockcode_$codeid\"><ol><li>" . preg_replace("/^(\<br \/\>)?(.*)/is", "\\2", $code) . "</li></ol></div>";
        return "<___PHPCOM_BLOCKCODE_{$codeid}___>";
    }

    public static function highlightcode($matches) {
        self::$highcount++;
        $codeid = self::$highcount;
        $attr = 'text';
        $code = $matches[1];
        if(isset($matches[2])){
        	$attr = trim($matches[1]);
        	$code = $matches[2];
        }
        
        $code = str_replace(array(' &nbsp; &nbsp; &nbsp; &nbsp;', '&nbsp;'), array("\t", ' '), $code);
        $code = str_replace('\\"', '"', $code);
        $code = preg_replace('/<br\s*?\/?>/is', "\r\n", $code);
        $code = str_replace("\r\n", "\n", $code);
        $code = preg_replace('/<[^<>]+?>/s', '', $code);
        self::$highcodes[$codeid] = "<div class=\"syntax\"><pre class=\"brush: $attr;toolbar:false;\">$code</pre></div>";
        return "<___PHPCOM_HIGHLIGHTCODE_{$codeid}___>";
    }
    
    public static function runcode($matches) {
        self::$htmlcount++;
        $codeid = self::$htmlcount;
        $code = $matches[1];
        $code = str_replace(array(' &nbsp; &nbsp; &nbsp; &nbsp;', '&nbsp;'), array("\t", ' '), $code);
        $code = str_replace('&nbsp;', ' ', $code);
        $code = str_replace('\\"', '"', $code);
        $code = preg_replace('/<br\s*?\/?>/is', "\r\n", $code);
        $code = preg_replace('/<[^<>]+?>/s', '', $code);
        $runcodeid = 'runcode' . $codeid;
        $s = '<div class="htmlcodeStyle"><div class="title">' . lang('common', 'codetitle') . '</div><div class="content">';
        $s .= '<textarea rows="10" id="' . $runcodeid . '" wrap="off" class="UBBText">' . str_replace('\\"', '"', $code) . '</textarea><br />';
        $s .= '<button onclick="runCodeEx(\'' . $runcodeid . '\')"> ' . lang('common', 'runcode') . ' </button> ';
        $s .= '<button onclick="doCopyEx(\'' . $runcodeid . '\')"> ' . lang('common', 'copycode') . ' </button> ';
        $s .= '<button onclick="saveAsCodeEx(\'' . $runcodeid . '\')"> ' . lang('common', 'saveascode') . ' </button> ';
        $s .= lang('common', 'codetips') . '</div></div>';
        self::$htmlcodes[$codeid] = $s;
        return "<___PHPCOM_HTMLCODE_{$codeid}___>";
    }

    public static function blockquote($matches) {
        return "<blockquote class=\"blockquoteStyle\">" . str_replace('\\"', '"', $matches[1]) . "</blockquote>";
    }

    public static function parser_ed2k($url) {
        list(, $type, $name, $size, ) = explode('|', $url);
        $url = 'ed2k://' . $url . '/';
        $name = addslashes($name);
        if ($type == 'file') {
            $ed2kid = 'ed2k_' . random(3);
            return '<a id="' . $ed2kid . '" href="' . $url . '" target="_blank"></a><script language="javascript">$(\'' . $ed2kid . '\').innerHTML=htmlspecialchars(unescape(decodeURIComponent(\'' . $name . '\')))+\' (' . formatbytes($size) . ')\';</script>';
        } else {
            return '<a href="' . $url . '" target="_blank">' . $url . '</a>';
        }
    }

    public static function parser_email($matches) {
    	$email = $matches[1];
    	$text = $matches[4];
        if (!$email && preg_match("/\s*([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\s*/i", $text, $matche)) {
            $email = trim($matche[0]);
            return '<a href="mailto:' . $email . '">' . $email . '</a>';
        } else {
            return '<a href="mailto:' . substr($email, 1) . '">' . $text . '</a>';
        }
    }

    public static function parser_attach($attchids, &$content, $thumb = 0, $module = 'article', $subject = null) {
        if ($attchids) {
            $parse = parse_url(phpcom::$setting['attachurl']);
            $attachurl = !isset($parse['host']) ? phpcom::$G['siteurl'] . phpcom::$setting['attachurl'] : phpcom::$setting['attachurl'];
            $attchids = implodeids($attchids);
            $query = DB::query("SELECT attachid, chanid, attachment, description, url, image, thumb, preview, remote FROM " . DB::table("attachment_$module") . " WHERE attachid IN ($attchids)");
            $findimage = $replaceimage = array();
            while ($attach = DB::fetch_array($query)) {
                if ($attach['remote']) {
                    $url = phpcom::$setting['ftp']['attachurl'] . phpcom::$G['channel'][$attach['chanid']]['modules'] . '/';
                } else {
                    $url = $attachurl . phpcom::$G['channel'][$attach['chanid']]['modules'] . '/';
                }
                if ($attach['image']) {
                    $src = $url . $attach['attachment'];
                    $href = empty($attach['url']) ? $src : trim($attach['url']);
                    if ($thumb == 1 && $attach['thumb']) {
                        $src = $url . generatethumbname($attach['attachment']);
                    }elseif ($thumb == 2 && $attach['preview']) {
                        $src = $url . generatethumbname($attach['attachment'], '_small.jpg');
                    }
                    if(defined('IN_PHPCOM_BUSINESS') && IN_PHPCOM_BUSINESS){
                    	$attach['description'] = empty($attach['description']) ? htmlcharsencode($subject) : htmlcharsencode($attach['description']);
                    }else{
                    	$attach['description'] = htmlcharsencode($attach['description']);
                    }
                    $desc = $attach['description'] ? ' alt="' . $attach['description'] . '"' : '';
                    $title = $attach['description'] ? ' title="' . $attach['description'] . '"' : '';
                    $findimage[] = "/\[attach\]{$attach['attachid']}\[\/attach\]/i";
                    $replaceimage[] = "<a class=\"image\" href=\"$href\"$title target=\"_blank\"><img src=\"$src\"$desc /></a>";
                }
            }
            $findimage && $content = preg_replace($findimage, $replaceimage, $content);
        }

        return $content;
    }
    
    public static function parser_url($matches) {
    	$url = $matches[1];
    	$text = $matches[5];
    	$scheme = $matches[2];
        if (!$url && preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.)[^\[\"']+/i", trim($text), $matche)) {
            $url = str_replace('&amp;', '&', $matche[0]);
            $length = 65;
            if (strlen($url) > $length) {
                $text = substr($url, 0, intval($length * 0.5)) . ' ... ' . substr($url, - intval($length * 0.3));
            }
            return '<a href="' . (substr(strtolower($url), 0, 4) == 'www.' ? 'http://' . $url : $url) . '" target="_blank">' . $text . '</a>';
        } else {
        	$url = str_replace('&amp;', '&', $url);
            $url = substr($url, 1);
            if (substr(strtolower($url), 0, 4) == 'www.') {
                $url = 'http://' . $url;
            }
            $url = !$scheme ? phpcom::$G['siteurl'] . $url : $url;
            return '<a href="' . $url . '" target="_blank">' . $text . '</a>';
        }
    }

    public static function parser_flash($matches) {
        $w = !$matches[2] ? 550 : $matches[2];
        $h = !$matches[4] ? 420 : $matches[4];
        $url = str_replace('&amp;', '&', trim($matches[5]));
        return "<object width=\"$w\" height=\"$h\" data=\"$url\" type=\"application/x-shockwave-flash\">\n<param name=\"src\" value=\"$url\" />\n</object>";
    }

    public static function bbcodeurl($url, $tags) {
    	$url = str_replace('&amp;', '&', $url);
        if (!preg_match("/<.+?>/s", $url)) {
            if (!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://')) && !preg_match('/^static\//', $url) && !preg_match('/^data\//', $url)) {
                $url = 'http://' . $url;
            }
            return str_replace(array('submit', 'member.php?action=loging'), array('', ''), str_replace('{url}', addslashes($url), $tags));
        } else {
            return '&nbsp;' . $url;
        }
    }

    public static function highlightword($text, $words, $prepend) {
        $text = str_replace('\"', '"', $text);
        foreach ($words as $key => $replaceword) {
            $text = str_replace($replaceword, '<highlight>' . $replaceword . '</highlight>', $text);
        }
        return "$prepend$text";
    }

    public static function parser_img($matches) {
        $extra = '';
        $width = intval($matches[2]);
        $height = intval($matches[4]);
        $src = str_replace('&amp;', '&', trim($matches[5]));
        $maxwidth = phpcom::$setting['imagemaxwidth'];
        if ($width > $maxwidth) {
            $height = intval($maxwidth * $height / $width);
            $width = $maxwidth;
            $extra = ' onclick="zoomimg(this)" style="cursor:pointer"';
        }else{
        	$extra = ' onclick="thumbimg(this)" style="cursor:pointer"';
        }
        return self::bbcodeurl($src, '<img' . ($width > 0 ? ' width="' . $width . '"' : '') . ($height > 0 ? ' height="' . $height . '"' : '') . ' src="' . $src . '"' . $extra . ' border="0" alt="" />');
    }
	
    public static function insertPagebreak($content, $pagesize = 0) {
    	$pagebreak = "[pagebreak]";
    	if($pagesize < 100){
    		return $content;
    	}
    	$content = str_replace(array('<!-- pagebreak -->', '[page_break]', '[pagebreak]'), '', $content);
    	$contlen = strlen($content);
    	$tmpArray = array();
    	$isPage = false;
    	if($contlen > 100 && $contlen > $pagesize){
    		$iPosCurr = $iPosLast = $iStart = 0;
    		$iCount = $iCurrSize = 0;
    		while($iPosCurr = strpos($content, '>', $iPosLast)){
    			$tmpstr = substr($content, $iPosLast, $iPosCurr - $iPosLast + 1);
    			$iCurrSize = strlen(strip_tags($tmpstr));
    			$iCount += $iCurrSize;
    			if($iCount >= $pagesize){
    				$tmpstr = substr($content, $iStart , $iPosCurr + 1);
    				if(self::checkPageTag($tmpstr, array('table', 'a', 'b>', 'i>', 'strong', 'div', 'span', 'ul', 'ol', 'dl', 'pre', 'object', 'code', 'blockquote'))){
    					$tmpArray[] = substr($content, $iStart , $iPosCurr - $iStart + 1);
    					$iCount = 0;
    					$isPage = true;
    					$iStart = $iPosCurr + 1;
    				}
    			}
    			$iPosLast = $iPosCurr + 1;
    		}
    			
    		if($isPage){
    			if($contlen > $iStart){
    				return implode($pagebreak, $tmpArray) .($contlen - $iStart > $pagesize / 2 ? $pagebreak : ''). substr($content, $iStart);
    			}
    			return implode($pagebreak, $tmpArray);
    		}else{
    			return $content;
    		}
    	}
    }
    
    protected static function checkPageTag($string, $tags = array())
    {
    	$flag = true;
    	$string = strtolower($string);
    	$begin_count = $end_count = 0;
    	if($string !== '' && $string !== null){
    		foreach($tags as $tag){
    			$begin = "<$tag"; $end = "</$tag";
    			if(strpos($string, $begin) === false && strpos($string, $end) === false) continue;
    			$begin_count = substr_count($string, $begin);
    			$end_count = substr_count($string, $end);
    			//$n = -1; while(($n = strpos($string, $begin, $n + 1)) !== false) $begin_count++;
    			//$n = -1; while(($n = strpos($string, $end, $n + 1)) !== false) $end_count++;
    			if($begin_count != $end_count){
    				$flag = false;
    				break;
    			}
    		}
    		return $flag;
    	}
    	return false;
    }
    
    public static function input($content) {
    	$content = preg_replace('/((&nbsp;){8,8}|( &nbsp;){4,4})/', "\t", $content);
    	$content = str_replace('&nbsp;', " ", $content);
    	$content = preg_replace('/(\s){8,8}/', "\t", $content);
    	$content = str_replace(array('<strong>', '</strong>'), array('[b]', '[/b]'), $content);
    	$content = preg_replace("/<(\/?)(b|u|i|s)(\s+[^>]+)?>/i", "[\\1\\2]", $content);
    	$content = preg_replace("/<(\/?)(b|u|i|s)(\s+[^>]+)?>/i", "[\\1\\2]", $content);
    	$content = preg_replace('/\<br(\s*)?\/?\>[\n\r]*/i', "\n", $content);
        return $content;
    }

    public static function output($content) {
        $textlower = strtolower($content);
        $content = str_replace(array("\t", '  '), array(' &nbsp; &nbsp; &nbsp; &nbsp;', ' &nbsp;'), $content);
        if (strpos($textlower, '[/url]') !== FALSE) {
        	$content = preg_replace_callback("#\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.|mailto:)?([^\r\n\[\"']+?))?\](.+?)\[\/url\]#i", 'bbcode::parser_url', $content);
        }
        $content = preg_replace(array(
            "/\[color=([#\w]+?)\]/",
            "/\[color=(rgb\([\d\s,]+?\))\]/",
            "/\[bgcolor=([#\w]+?)\]/",
            "/\[size=(\d{1,2}?)\]/",
            "/\[size=(\d{1,2}(\.\d{1,2}+)?(px|pt)+?)\]/",
            "/\[font=([^\[\<]+?)\]/",
            "/\[align=(left|center|right|justify)\]/"
                ), array(
            "<font color=\"\\1\">",
            "<font style=\"color:\\1\">",
            "<span style=\"background-color:\\1;\">",
            "<font size=\"\\1\">",
            "<font style=\"font-size: \\1\">",
            "<font face=\"\\1 \">",
            "<p align=\"\\1\">"
                ), $content);
        $content = str_replace(array('[/size]', '[/font]', '[/color]'), '</font>', $content);
        $content = str_replace(array('[/bgcolor]', '[/align]'), array('</span>', '</p>'), $content);
        $content = preg_replace("/\[(\/?)(b|u|i|s|em)\]/i", "<\\1\\2>", $content);
        if (strpos($textlower, '[/img]') !== FALSE) {
        	$content = preg_replace_callback("#\[img(=(\d{1,4}+)([x|,](\d{1,4}))*)?\]\s*([^\[\<\r\n]+?)\s*\[\/img\]#is", 'bbcode::parser_img', $content);
        }

        if (strpos($content, "[quote]") !== FALSE && strpos($content, "[/quote]") !== false) {
            $content = preg_replace_callback("#\[quote\](.*?)\[\/quote\]#is", 'bbcode::blockquote', $content);
        }
        return nl2br($content);
    }
}

?>
