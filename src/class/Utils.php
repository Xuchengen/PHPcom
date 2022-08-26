<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : utils.php    2011-7-5 23:22:10
 */
/**
 * Utils 工具类
 */
class Utils {
	/**
	 * 将字符串变量转换成整型变量
	 * @param mixed $param 任何标量类型参数
	 * @return int 返回 $param 的 integer 数值
	 */
	public static function toInt($param) {
		return intval($param);
	}

	/**
	 * 获取变量的字符串值
	 * @param mixed $param 任何标量类型参数
	 * @return string 返回 $param 的 string 值
	 */
	public static function toStr($param) {
		return strval($param);
	}

	/**
	 * 检测变量是否是一个对象
	 * @param mixed $param 对象参数
	 * @return bool 如果 $param 是一个 object 则返回 TRUE，否则返回 FALSE
	 */
	public static function isObj($param) {
		return is_object($param) ? true : false;
	}

	/**
	 * 检测变量是否是数组
	 * @param mixed $params 数组参数
	 * @return bool 如果 $params 是一个 array 则返回 TRUE，否则返回 FALSE
	 */
	public static function isArray($params) {
		return (!is_array($params) || !count($params)) ? false : true;
	}

	/**
	 * 判断指定字符串是否属于指定字符串数组中的一个元素
	 * @param mixed $param 参数一
	 * @param array $params 参数二
	 * @return type 在 $params 中搜索 $param，如果找到则返回 TRUE，否则返回 FALSE。
	 */
	public static function inArray($param, $params) {
		return (!in_array((string) $param, (array) $params)) ? false : true;
	}

	/**
	 * 检测变量是否是布尔型
	 * @param mixed $param 任何标量类型参数
	 * @return bool 返回布尔值 TRUE/FALSE
	 */
	public static function isBool($param) {
		return is_bool($param) ? true : false;
	}

	/**
	 * 检测变量是否为数字或数字字符串
	 * @param mixed $param 任何标量类型参数
	 * @return bool 返回布尔值 TRUE/FALSE
	 */
	public static function isNum($param) {
		return is_numeric($param) ? true : false;
	}

	/**
	 * 检测变量是否是整数
	 * @param mixed $param 任何标量类型参数
	 * @return bool  返回布尔值 TRUE/FALSE
	 */
	public static function isInt($param) {
		return is_int($param) ? true : false;
	}

	/**
	 * 检测变量是否是浮点型
	 * @param mixed $param 任何标量类型参数
	 * @return bool 返回布尔值 TRUE/FALSE
	 */
	public static function isFloat($param) {
		return is_float($param) ? true : false;
	}

	/**
	 * 文件路径合并
	 * @param string $path 目录路径
	 * @param string $file 文件名
	 * @return string 返回合并后的文件路径
	 */
	public static function pathCombine($path, $file) {
		if (empty($path) || empty($file)) return false;
		if (!strlen($file)) return $path;
		if (!strlen($path)) return $file;
		if (strpos($file, ':')) return $file;
		return (rtrim($path, '/\\') . DIRECTORY_SEPARATOR . trim($file, './\\'));
	}

	/**
	 * 字符串格式化函数
	 * @param mixed arguments in one array
	 * @return mixed 根据参数个数返回格式化后字符串sprintf("%s", );
	 */
	public static function stringFormat($text) {
		$args = func_get_args();
		array_shift($args);
		if (isset($args[0]) and is_array($args[0])) {
			$args = $args[0];
		}
		return preg_replace("/\{([\d]+?)(?::([^}]+?))?\}/ie", "\$args['$1']", $text);
	}

	/**
	 * 截取指定字符串
	 * @param string $string 原始字符串
	 * @param string $start 开始字符串
	 * @param string $last 结束字符串
	 * @param int $type 截取类型
	 * @return string 返回截取后的字符串
	 */
	public static function substring($string, $start, $last = null, $type = 0) {
		$len_begin = strlen($start);
		$len_last = strlen($last);
		$pos_begin = $type > 1 ? strrpos($string, $start) : strpos($string, $start);
		$pos_last = $len_last ? strpos($string, $last, $pos_begin + $len_begin) : strlen($string);

		if ($pos_begin > -1 && $pos_last > 0 && $pos_begin < $pos_last) {
			switch ($type) {
				case 1:  //左右都截取（保留关键字）
					return substr($string, $pos_begin, $pos_last + $len_last - $pos_begin);
				case 2:  //反向都截取（去掉关键字）
					return substr($string, $pos_begin + $len_begin, $pos_last - ($pos_begin + $len_begin));
				case 3:  //反向都截取（保留关键字）
					return substr($string, $pos_begin, $pos_last + $len_last - $pos_begin);
				default: //左右都截取（去掉关键字）
					return substr($string, $pos_begin + $len_begin, $pos_last - ($pos_begin + $len_begin));
			}
		}
		
		return "";
	}

	/**
	 * 截取相配置的字符串
	 * @param string $string 原始字符串
	 * @param string $start 开始字符串
	 * @param string $last 结束字符串
	 * @param bool $type 截取类型
	 * @return array 返回截取的字符串数组
	 */
	public static function substrArray($string, $start, $last = null, $type = FALSE) {
		$arr_data = array();
		$pos_last = $pos_curr = 0;
		$len_begin = strlen($start);
		$len_last = strlen($last);
		
		while (TRUE) {
			$pos_curr = strpos($string, $start, $pos_last);
			if (FALSE !== $pos_curr) {
				$pos_last = strpos($string, $last, $pos_curr + $len_begin);
				if (FALSE === $pos_last) break;
				if ($type) {
					$arr_data[] = substr($string, $pos_curr, $pos_last + $len_last - $pos_curr);
				} else {
					$arr_data[] = substr($string, $pos_curr + $len_begin, $pos_last - ($pos_curr + $len_begin));
				}
				$pos_last = $pos_last + $len_last;
			}else break;
		}
		
		return $arr_data;
	}

	public static function parseString($string) {
		$pre = $end = chr(1);
		$strfind = FALSE;
		try {
			if (strpos($string, '&')) {
				$string = str_replace('&', $pre . '#' . $end, $string);
				$strfind = TRUE;
			}
			$string = preg_replace("/(\w*?)\s*\=\s*(\'|\")\s*(.*?)\s*(\'|\")\s*/ies", "self::setkeyvalue('\\1','\\3')", $string);
			$string = str_replace(array("'", '"'), array('', ''), $string);
			parse_str($string, $array);
			if ($strfind) {
				foreach ($array as $key => $val) {
					$array[$key] = str_replace($pre . '#' . $end, '&', $val);
				}
			}
			return $array;
		} catch (Exception $e) {
			return FALSE;
		}
	}

	protected static function setkeyvalue($key, $value) {
		return strtolower($key) . "=$value&";
	}

}
?>
