<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : WordSplitter.php  2013-3-15
 */
!defined('IN_PHPCOM') && exit('Access denied');

class WordSplitter
{
	private $hashMask			= 0x7FFF;
	private $inputCharset		= 'UTF-8';
	private $outputCharset		= 'UTF-8';
	private $dictHandle			= false;
	private $dictFile			= 'dict/dict.dic';
	private $dictWords			= array();
	private $dictAttachFile		= 'dict/attach.dic';
	private $dictAttach			= array();
	private $inputText, $baseDir;
	private $dictLoaded			= false;
	private $newWords			= array();
	public $foundWord			= '';
	private $simple				= array();
	private $result				= array();
	private $isUniteWord		= true;
	private $isMultiWord		= false;
	private $type				= 1;
	public $maxWordLength		= 14;
	public $limit				= 5;

	public function __construct($in_charset = 'UTF-8', $out_charset = 'UTF-8', $string = null)
	{
		$this->inputCharset = $in_charset;
		$this->outputCharset = $out_charset;
		if(!empty($string)){
			$this->input($string, $in_charset, $out_charset);
		}
	}

	public function __destruct()
	{
		if($this->dictHandle){
			@fclose($this->dictHandle);
		}
	}
	
	/**
	 * Get hash index
	 *
	 * @param string $key
	 * @return int
	 */
	private function getHashIndex($key)
	{
		$l = strlen($key);
		$h = 0x238f13af;
		while ($l--) {
			$h += ($h << 5);
			$h ^= ord($key[$l]);
			$h &= 0x7fffffff;
		}
		return ($h % $this->hashMask);
	}

	public function setBaseDir($dir)
	{
		$this->baseDir = rtrim($dir, "/\\ \r\n\t") . '/';
	}

	public function setUniteWord($value)
	{
		$this->isUniteWord = $value;
	}
	
	public function setMultiWord($value)
	{
		$this->isMultiWord = $value;
	}
	
	public function setType($type)
	{
		$this->type = $type;
	}

	public function loadDict($dictFile = null)
	{
		$startt = microtime(true);
		if($this->dictLoaded == false){
			if(empty($this->baseDir)){
				$this->baseDir = dirname(__FILE__) . '/';
			}
			if(empty($dictFile) || file_exists($dictFile)){
				$this->dictFile = $this->baseDir . $this->dictFile;
			}else{
				$this->dictFile = $this->baseDir . $dictFile;
			}
			if(!$this->dictHandle = @fopen($this->dictFile, 'r')){
				return false;
			}
			$key = '';
			$addonFile = $this->baseDir . $this->dictAttachFile;
			if($lines = @file($addonFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)){
				$separator = chr(0xFF) . chr(0xFE);
				$separator = iconv('UCS-2BE', 'UTF-8', $separator);
				foreach($lines as $line){
					if(($line = trim($line)) && substr($line, 1, 1) == ':'){
						$key = substr($line, 0, 1);
						$words = explode(',', substr($line, 2));
						$uword = iconv('UTF-8', 'UCS-2BE//TRANSLIT//IGNORE', implode($separator, $words));
						$words = explode(chr(0xFF) . chr(0xFE), $uword);
						foreach($words as $word){
							$this->dictAttach[$key][$word] = strlen($word);
						}
					}
				}
			}
		}
		$this->dictLoaded = true;
		return true;
	}

	public function getWords($key, $type = 'word')
	{
		if(!$this->dictHandle && !$this->dictHandle = @fopen($this->dictFile, 'r')){
			return false;
		}

		$k = $this->getHashIndex($key);
		if(isset($this->dictWords[$k])){
			$data = $this->dictWords[$k];
		}else{
			$offset = $k * 6;
			fseek($this->dictHandle, $offset, SEEK_SET);
			$arr = @unpack('I1s/n1l', fread($this->dictHandle, 6));
			if(empty($arr['l'])) return false;
			fseek($this->dictHandle, $arr['s'], SEEK_SET);
			$data = @unserialize(fread($this->dictHandle, $arr['l']));
			$this->dictWords[$k] = $data;
		}

		if(!is_array($data) || !isset($data[$key])){
			return false;
		}
		return ($type == 'word' ? $data[$key] : $data);
	}

	public function setWords($word, $data)
	{
		if(strlen($word) < 4) return;
		if(isset($this->dictWords[$word])){
			$this->newWords[$word]++;
			$this->dictWords[$word]['c']++;
		}else{
			$this->newWords[$word] = 1;
			$this->dictWords[$word] = $data;
		}
	}

	private function isWord($word)
	{
		$words = $this->getWords($word);
		return ($words !== false);
	}

	private function getWordAttribute($word)
	{
		if( strlen($word)<4 ){
			return '/s';
		}
		$words = $this->getWords($word);
		if(!is_array($words)) $words = explode(' ', $words);
		return isset($words[1]) ? "/{$words[1]} {$words[0]}" : "/s";
	}

	public function input($string, $in_charset = null, $out_charset = null)
	{
		$this->inputCharset = empty($in_charset) ? $this->inputCharset : $in_charset;
		$this->outputCharset = empty($out_charset) ? $this->inputCharset : $out_charset;
		$this->simple = array();
		$this->result = array();

		if(empty($string)){
			return false;
		}else{
			$in_charset = $this->inputCharset;
			if(strncasecmp($in_charset, 'GBK', 2) === 0){
				$string = iconv('GB18030', 'UTF-8//TRANSLIT//IGNORE', $string);
			}elseif(strcasecmp($in_charset, 'BIG5') === 0){
				$string = iconv('BIG5', 'UTF-8//TRANSLIT//IGNORE', $string);
			}
			$this->inputText = iconv('UTF-8', 'UCS-2BE//TRANSLIT//IGNORE', $string);
		}
		return true;
	}

	public function startAnalysis($optimize = true)
	{
		if(!$this->loadDict()){
			return false;
		}
		$string = $this->inputText . chr(0) . chr(32);
		$len = strlen($string);
		$sbcArray = $this->getSBCcaseList();
		$tmpstr = '';
		$latest = 1;
		$offset = 0;
		for($i = 0; $i < $len; $i++){
			$b = $string[$i] . $string[++$i];
			$a = hexdec(bin2hex($b));
			$a = isset($sbcArray[$a]) ? $sbcArray[$a] : $a;
			if($a < 0x80){
				if(preg_match('/[0-9a-z@#%\+\.-]/i', chr($a))){
					if($latest != 2 && $tmpstr != '') {
						$this->simple[$offset]['w'] = $tmpstr;
						$this->simple[$offset]['t'] = $latest;
						$this->depthAnalysis($tmpstr, $latest, $offset, $optimize);
						$offset++;
						$tmpstr = '';
					}
					$latest = 2;
					$tmpstr .= chr(0) . chr($a);
				}else{
					if($tmpstr != ''){
						$this->simple[$offset]['w'] = $tmpstr;
						if($latest == 2){
							if(!preg_match('/[a-z@#%\+]/i', iconv('UCS-2BE', 'UTF-8//TRANSLIT//IGNORE', $tmpstr))) $latest = 4;
						}
						$this->simple[$offset]['t'] = $latest;
						if($latest != 4) $this->depthAnalysis($tmpstr, $latest, $offset, $optimize);
						$offset++;
					}
					$tmpstr = '';
					$latest = 3;
						
					if($a < 31){
						continue;
					}else{
						$this->simple[$offset]['w'] = chr(0) . chr($a);
						$this->simple[$offset]['t'] = 3;
						$offset++;
					}
				}
			}else{
				if(($a > 0x3FFF && $a < 0x9FA6) || ($a > 0xF8FF && $a < 0xFA2D)
				|| ($a > 0xABFF && $a < 0xD7A4) || ($a > 0x3040 && $a < 0x312B)){
					if($latest != 1 && $tmpstr != ''){
						$this->simple[$offset]['w'] = $tmpstr;
						if($latest == 2){
							if( !preg_match('/[a-z@#%\+]/i', iconv('UCS-2BE', 'UTF-8//TRANSLIT//IGNORE', $tmpstr))) $latest = 4;
						}
						$this->simple[$offset]['t'] = $latest;
						if($latest != 4) $this->depthAnalysis($tmpstr, $latest, $offset, $optimize);
						$offset++;
						$tmpstr = '';
					}
					$latest = 1;
					$tmpstr .= $b;
				}else{
					if($tmpstr != ''){
						$this->simple[$offset]['w'] = $tmpstr;
						if($latest == 2){
							if(!preg_match('/[a-z@#%\+]/i', iconv('UCS-2BE', 'UTF-8//TRANSLIT//IGNORE', $tmpstr))) $latest = 4;
						}
						$this->simple[$offset]['t'] = $latest;
						if($latest != 4) $this->depthAnalysis($tmpstr, $latest, $offset, $optimize);
						$offset++;
					}
						
					if($a == 0x300A || $a == 0x3010){
						$tmpw = '';
						$isok = false;
						$n = 1;
						$ew = $a == 0x300A ? chr(0x30) . chr(0x0B) : chr(0x30) . chr(0x11);
						while(true){
							$w = $this->inputText[$i + $n] . $this->inputText[$i + $n + 1];
							if($w == $ew){
								$this->simple[$offset]['w'] = $tmpw;
								$this->newWords[$tmpw] = 1;
								if(!isset($this->newWords[$tmpw])){
									$this->foundWord .= $this->convertOutput($tmpw) . '/nb, ';
									$this->setWords($tmpw, array('c' => 1, 'm' => 'nb'));
								}
								$this->simple[$offset]['t'] = 13;
								$offset++;

								if($this->isMultiWord){
									$this->simple[$offset]['w'] = $tmpw;
									$this->simple[$offset]['t'] = 21;
									$this->depthAnalysis($tmpw, $latest, $offset, $optimize);
									$offset++;
								}

								$i = $i + $n + 1;
								$isok = true;
								$tmpstr = '';
								$latest = 5;
								break;
							}else{
								$n += 2;
								$tmpw .= $w;
								if(strlen($tmpw) > 60) break;
							}
						}
						if(!$isok){
							$this->simple[$offset]['w'] = $b;
							$this->simple[$offset]['t'] = 5;
							$offset++;
							$tmpstr = '';
							$latest = 5;
						}
						continue;
					}
					$tmpstr = '';
					$latest = 5;
					if($a == 0x3000){
						continue;
					}else{
						$this->simple[$offset]['w'] = $b;
						$this->simple[$offset]['t'] = 5;
						$offset++;
					}
				}
			}
		}
		$this->outputResult();
	}

	private function depthAnalysis(&$string, $type, $offset, $optimize = true)
	{
		if($type == 1){
			$length = strlen($string);
			if($length < $this->limit){
				$lastType = 0;
				if( $offset > 0 ) $lastType = $this->simple[$offset - 1]['t'];
				if($length < 5){
					if( $lastType == 4
					&& (isset($this->dictAttach['u'][$string]) || isset($this->dictAttach['u'][substr($string, 0, 2)]))){
						$tmpstr = '';
						if(!isset($this->dictAttach['u'][$string]) && isset($this->dictAttach['s'][substr($string, 2, 2)])){
							$tmpstr = substr($string, 2, 2);
							$string  = substr($string, 0, 2);
						}
						$word = $this->simple[$offset - 1]['w'] . $string;
						$this->simple[$offset - 1]['w'] = $word;
						$this->simple[$offset - 1]['t'] = 4;
						if(!isset($this->newWords[$this->simple[$offset - 1]['w']])){
							$this->foundWord .= $this->convertOutput($word) . '/mu, ';
							$this->setWords($word, array('c' => 1, 'm' => 'mu'));
						}
						$this->simple[$offset]['w'] = '';
						if($tmpstr != ''){
							$this->result[$offset - 1][] = $word;
							$this->result[$offset - 1][] = $tmpstr;
						}
					}else{
						$this->result[$offset][] = $string;
					}
				}else{
					$this->chineseAnalysis($string, $offset, $length, $optimize);
				}
			}else{
				$this->chineseAnalysis($string, $offset, $length, $optimize);
			}
		}else{
			$this->result[$offset][] = $string;
		}
	}

	private function chineseAnalysis(&$string, $offset, $length, $optimize = true)
	{
		$quotes = chr(0x20) . chr(0x1C);
		$tmpArray = array();
		if($offset > 0 && $length < 11 && $this->simple[$offset - 1]['w'] == $quotes){
			$tmpArray[] = $string;
			if(!isset($this->newWords[$string])){
				$this->foundWord .= $this->convertOutput($string) . '/nq, ';
				$this->setWords($string, array('c' => 1, 'm' => 'nq'));
			}
			if(!$this->isMultiWord){
				$this->result[$offset][] = $string;
				return false;
			}
		}

		for($i = $length - 1; $i > 0; $i -= 2){
			$word = $string[$i - 1] . $string[$i];
			if($i <= 2){
				$tmpArray[] = $word;
				$i = 0;
				break;
			}
			$i++;
			$flag = false;
			for($k = $this->maxWordLength; $k > 1; $k -= 2){
				if($i < $k) continue;
				$w = substr($string, $i - $k, $k);
				if(strlen($w) <= 2){
					$i--;
					break;
				}

				if($this->isWord($w)){
					$tmpArray[] = $w;
					$i = $i - $k + 1;
					$flag = true;
					break;
				}

			}
			if(!$flag) $tmpArray[] = $word;
		}
		if($count = count($tmpArray)){
			$this->result[$offset] = array_reverse($tmpArray);
			if($optimize){
				$this->optimizeResult($this->result[$offset], $offset, $count);
			}
			return true;
		}
		return false;
	}

	private function optimizeResult(&$array, $offset, $count = 0)
	{
		$newArray = array();
		$pos = $offset - 1;
		$i = $j = 0;
		if($pos > -1 && !isset($this->result[$pos])){
			$lastw = $this->simple[$pos]['w'];
			$lastt = $this->simple[$pos]['t'];
			if(($lastt == 4 || isset($this->dictAttach['c'][$lastw])) && isset($this->dictAttach['u'][$array[0]])){
				$this->simple[$pos]['w'] = $lastw . $array[0];
				$this->simple[$pos]['t'] = 4;
				if(!isset($this->newWords[$this->simple[$pos]['w']])){
					$this->foundWord .= $this->convertOutput($this->simple[$pos]['w']) . '/mu, ';
					$this->setWords($this->simple[$pos]['w'], array('c' => 1, 'm' => 'mu'));
				}
				$array[0] = '';
				$i++;
			}
		}
		for(; $i < $count; $i++){
			if(!isset($array[$i + 1])){
				$newArray[$j] = $array[$i];
				break;
			}
			$cw = $array[$i];
			$nw = $array[$i + 1];
			$ischeck = false;
			if(isset($this->dictAttach['c'][$cw]) && isset($this->dictAttach['u'][$nw])){
				if($this->isMultiWord){
					$newArray[$j] = chr(0) . chr(0x28);
					$newArray[++$j] = $cw;
					$newArray[++$j] = $nw;
					$newArray[++$j] = chr(0) . chr(0x29);
					++$j;
				}
				$newArray[$j] = $cw . $nw;
				if(!isset($this->newWords[$newArray[$j]])){
					$this->foundWord .= $this->convertOutput( $newArray[$j] ) . '/mu, ';
					$this->setWords($newArray[$j], array('c' => 1, 'm' => 'mu'));
				}
				++$j; ++$i; $ischeck = true;
			}elseif(isset($this->dictAttach['n'][$array[$i]])){
				$flag = false;
				if(strlen($nw) == 4){
					$words = $this->getWords($nw);
					if(isset($words['m']) && ($words['m'] == 'r' || $words['m'] == 'c' || $words['c'] > 500) ){
						$flag = true;
					}
				}
				if(!isset($this->dictAttach['s'][$nw]) && strlen($nw) < 5 && !$flag){
					$newArray[$j] = $cw . $nw;
					if(strlen($nw) == 2 && isset($array[$i + 2]) && strlen($array[$i + 2])==2
					&& !isset($this->dictAttach['s'][$array[$i + 2]])){
						$newArray[$j] .= $array[$i + 2];
						++$i;
					}
					if(!isset($this->newWords[$newArray[$j]])){
						$this->setWords($newArray[$j], array('c' => 1, 'm' => 'nr'));
						$this->foundWord .= $this->convertOutput($newArray[$j]) . '/nr, ';
					}
					if(strlen($nw)==4){
						$newArray[++$j] = chr(0) . chr(0x28);
						$newArray[++$j] = $cw;
						$newArray[++$j] = $nw;
						$newArray[++$j] = chr(0) . chr(0x29);
					}
					++$j; ++$i; $ischeck = true;
				}
			}elseif(isset($this->dictAttach['a'][$nw])){
				$flag = false;
				if(strlen($cw)>2){
					$words = $this->getWords($cw);
					if(isset($words['m']) && ($words['m']=='a' || $words['m'] == 'r' || $words['m'] == 'c' || $words['c'] > 500)){
						$flag = true;
					}
				}
				if(!isset($this->dictAttach['s'][$cw]) && !$flag){
					$newArray[$j] = $cw . $nw;
					if(!isset($this->newWords[$newArray[$j]])){
						$this->foundWord .= $this->convertOutput($newArray[$j]).'/na, ';
						$this->setWords($newArray[$j], array('c' => 1, 'm' => 'na'));
					}
					++$j; ++$i; $ischeck = true;
				}
			}elseif($this->isUniteWord){
				if(strlen($cw) == 2 && strlen($nw) == 2 && !isset($this->dictAttach['s'][$cw])
				&& !isset($this->dictAttach['t'][$cw]) && !isset($this->dictAttach['a'][$cw])
				&& !isset($this->dictAttach['s'][$nw]) && !isset($this->dictAttach['c'][$nw])){
					$newArray[$j] = $cw . $nw;
					if(isset($array[$i + 2]) && strlen($array[$i + 2]) == 2
					&& (isset($this->dictAttach['a'][$array[$i + 2]]) || isset($this->dictAttach['u'][$array[$i + 2]])) ){
						$newArray[$j] .= $array[$i+2];
						$i++;
					}
					if(!isset($this->newWords[$newArray[$j]])){
						$this->foundWord .= $this->convertOutput($newArray[$j]) . '/ms, ';
						$this->setWords($newArray[$j], array('c' => 1, 'm' => 'ms'));
					}
					++$j; ++$i; $ischeck = true;
				}
			}
			if(!$ischeck){
				$newArray[$j] = $cw;
				if( $this->isMultiWord && !isset($this->dictAttach['s'][$cw]) && strlen($cw) < 5 && strlen($nw) < 7){
					$slen = strlen($nw);
					$hasDiff = false;
					for($y = 2; $y <= $slen - 2; $y = $y + 2){
						$nhead = substr($nw, $y - 2, 2);
						$nfont = $cw . substr($nw, 0, $y - 2);
						if($this->isWord($nfont . $nhead)){
							if( strlen($cw) > 2 ) $j++;
							$hasDiff = true;
							$newArray[$j] = $nfont . $nhead;
						}
					}
				}
				++$j;
			}
		}
		$array =  $newArray;
	}

	private function outputResult()
	{
		$result = array();
		$i = 0;
		foreach($this->simple as $k => $v){
			if(empty($v['w'])) continue;
			if(isset($this->result[$k]) && count($this->result[$k]) > 0){
				foreach($this->result[$k] as $w){
					if(!empty($w)){
						$result[$i]['w'] = $w;
						$result[$i]['t'] = 20;
						$i++;
					}
				}
			}elseif($v['t'] != 21){
				$result[$i]['w'] = $v['w'];
				$result[$i]['t'] = $v['t'];
				$i++;
			}
		}
		$this->result = $result;
		unset($result);
	}

	public function convertOutput($string, $encoding = '')
	{
		$encoding = empty($encoding) ? $this->outputCharset : $encoding;
		if(strcasecmp($encoding, 'UTF-8') === 0){
			return iconv('UCS-2BE', 'UTF-8//TRANSLIT//IGNORE', $string);
		}elseif(strcasecmp($encoding, 'BIG5') === 0){
			return iconv('UTF-8', 'BIG5//TRANSLIT//IGNORE', iconv('UCS-2BE', 'UTF-8//TRANSLIT//IGNORE', $string));
		}else{
			return iconv('UTF-8', 'GB18030//TRANSLIT//IGNORE', iconv('UCS-2BE', 'UTF-8//TRANSLIT//IGNORE', $string));
		}
	}

	public function getSBCcaseList()
	{
		static $sbcArray = array();
		if(empty($sbcArray)){
			for($i = 0xFF00; $i < 0xFF5F; $i++){
				$sbcArray[$i] = $i - 0xFEE0;
			}
		}
		return $sbcArray;
	}

	public function fetchResult($delimiter = ' ', $meanings = false)
	{
		$result = '';
		foreach($this->result as $value){
			if($this->type == 2 && ($value['t'] == 3 || $value['t'] == 5)){
				continue;
			}
			if(!isset($value['w']) || strlen($value['w']) <= 2) continue;
			$m = $meanings ? $this->getWordAttribute($value['w']) : '';
			if(($w = $this->convertOutput($value['w'])) != ' '){
				$result .= $meanings ? $delimiter . $w . $m : $delimiter . $w;
			}
		}
		return $result;
	}

	public function fetchSimple()
	{
		$result = array();
		foreach($this->simple as $value){
			if(empty($value['w'])) continue;
			if(($w = $this->convertOutput($value['w'])) != ' '){
				$result[] = $w;
			}
		}
		return $result;
	}

	private function getResultIndex()
	{
		$return = array();
		foreach($this->result as $value){
			if($this->type == 2 && ($value['t'] == 3 || $value['t'] == 5)){
				continue;
			}
			if($w = $this->convertOutput($value['w'])){
				if(isset($return[$w])){
					$return[$w]++;
				}else{
					$return[$w] = 1;
				}
			}else{
				continue;
			}
		}
		return $return;
	}
	
	public function createDict($filename, $target = null)
	{
		$target = empty($target) ? $this->dictFile : $target;
		$tmpArray = array();
		if($fp = @fopen($filename, 'r')){
			while($line = fgets($fp, 512)){
				if(empty($line) || $line[0]=='@') continue;
				list($w, $a) = explode(' ', $line, 2);
				$w = iconv('UTF-8', 'UCS-2BE//TRANSLIT//IGNORE', $w);
				$k = $this->getHashIndex($w);
				$tmpArray[$k][$w] = trim($a);
			}
		}
		@fclose($fp);
		if($fp = @fopen($target, 'w')){
			$indexs = array();
			$offset = $this->hashMask * 6;
			$data = '';
			foreach($tmpArray as $k => $v){
				$d  = serialize($v);
				$l = strlen($d);
				$indexs[$k][0] = $offset;
				$indexs[$k][1] = $l;
				
				$data .= $d;
				$offset += $l;
			}
			unset($tmpArray);
			for($i = 0; $i < $this->hashMask; $i++){
				if(!isset($indexs[$i])){
					$indexs[$i] = array(0, 0);
				}
				fwrite($fp, pack("In", $indexs[$i][0], $indexs[$i][1]));
			}
			fwrite($fp, $data);
		}
		@fclose($fp);
	}
	
	public function exportDict($filename, $dictfile = null)
	{
		$handle = $dictfile ? fopen($dictfile, 'r') : $this->dictHandle;
		if($fp = @fopen($filename, 'w')){
			for($i = 0; $i <= $this->hashMask; $i++){
				$offset = $i * 6;
				fseek($handle, $offset, SEEK_SET);
				$array = unpack('I1s/n1l', fread($handle, 6));
				if($array['l'] == 0) continue;
				fseek($handle, $array['s'], SEEK_SET);
				if($data = @unserialize(fread($handle, $array['l']))){
					foreach($data as $k => $v){
						$w = iconv('UCS-2BE', 'UTF-8//TRANSLIT//IGNORE', $k);
						fwrite($fp, "{$w} {$v}\n");
					}
				}
			}
		}
		@fclose($fp);
		@fclose($handle);
	}
}
?>