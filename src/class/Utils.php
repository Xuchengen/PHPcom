<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : utils.php    2011-7-5 23:22:10
 */
/**
 * Utils ������
 */
class Utils {
	/**
	 * ���ַ�������ת�������ͱ���
	 * @param mixed $param �κα������Ͳ���
	 * @return int ���� $param �� integer ��ֵ
	 */
	public static function toInt($param) {
		return intval($param);
	}

	/**
	 * ��ȡ�������ַ���ֵ
	 * @param mixed $param �κα������Ͳ���
	 * @return string ���� $param �� string ֵ
	 */
	public static function toStr($param) {
		return strval($param);
	}

	/**
	 * �������Ƿ���һ������
	 * @param mixed $param �������
	 * @return bool ��� $param ��һ�� object �򷵻� TRUE�����򷵻� FALSE
	 */
	public static function isObj($param) {
		return is_object($param) ? true : false;
	}

	/**
	 * �������Ƿ�������
	 * @param mixed $params �������
	 * @return bool ��� $params ��һ�� array �򷵻� TRUE�����򷵻� FALSE
	 */
	public static function isArray($params) {
		return (!is_array($params) || !count($params)) ? false : true;
	}

	/**
	 * �ж�ָ���ַ����Ƿ�����ָ���ַ��������е�һ��Ԫ��
	 * @param mixed $param ����һ
	 * @param array $params ������
	 * @return type �� $params ������ $param������ҵ��򷵻� TRUE�����򷵻� FALSE��
	 */
	public static function inArray($param, $params) {
		return (!in_array((string) $param, (array) $params)) ? false : true;
	}

	/**
	 * �������Ƿ��ǲ�����
	 * @param mixed $param �κα������Ͳ���
	 * @return bool ���ز���ֵ TRUE/FALSE
	 */
	public static function isBool($param) {
		return is_bool($param) ? true : false;
	}

	/**
	 * �������Ƿ�Ϊ���ֻ������ַ���
	 * @param mixed $param �κα������Ͳ���
	 * @return bool ���ز���ֵ TRUE/FALSE
	 */
	public static function isNum($param) {
		return is_numeric($param) ? true : false;
	}

	/**
	 * �������Ƿ�������
	 * @param mixed $param �κα������Ͳ���
	 * @return bool  ���ز���ֵ TRUE/FALSE
	 */
	public static function isInt($param) {
		return is_int($param) ? true : false;
	}

	/**
	 * �������Ƿ��Ǹ�����
	 * @param mixed $param �κα������Ͳ���
	 * @return bool ���ز���ֵ TRUE/FALSE
	 */
	public static function isFloat($param) {
		return is_float($param) ? true : false;
	}

	/**
	 * �ļ�·���ϲ�
	 * @param string $path Ŀ¼·��
	 * @param string $file �ļ���
	 * @return string ���غϲ�����ļ�·��
	 */
	public static function pathCombine($path, $file) {
		if (empty($path) || empty($file)) return false;
		if (!strlen($file)) return $path;
		if (!strlen($path)) return $file;
		if (strpos($file, ':')) return $file;
		return (rtrim($path, '/\\') . DIRECTORY_SEPARATOR . trim($file, './\\'));
	}

	/**
	 * �ַ�����ʽ������
	 * @param mixed arguments in one array
	 * @return mixed ���ݲ����������ظ�ʽ�����ַ���sprintf("%s", );
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
	 * ��ȡָ���ַ���
	 * @param string $string ԭʼ�ַ���
	 * @param string $start ��ʼ�ַ���
	 * @param string $last �����ַ���
	 * @param int $type ��ȡ����
	 * @return string ���ؽ�ȡ����ַ���
	 */
	public static function substring($string, $start, $last = null, $type = 0) {
		$len_begin = strlen($start);
		$len_last = strlen($last);
		$pos_begin = $type > 1 ? strrpos($string, $start) : strpos($string, $start);
		$pos_last = $len_last ? strpos($string, $last, $pos_begin + $len_begin) : strlen($string);

		if ($pos_begin > -1 && $pos_last > 0 && $pos_begin < $pos_last) {
			switch ($type) {
				case 1:  //���Ҷ���ȡ�������ؼ��֣�
					return substr($string, $pos_begin, $pos_last + $len_last - $pos_begin);
				case 2:  //���򶼽�ȡ��ȥ���ؼ��֣�
					return substr($string, $pos_begin + $len_begin, $pos_last - ($pos_begin + $len_begin));
				case 3:  //���򶼽�ȡ�������ؼ��֣�
					return substr($string, $pos_begin, $pos_last + $len_last - $pos_begin);
				default: //���Ҷ���ȡ��ȥ���ؼ��֣�
					return substr($string, $pos_begin + $len_begin, $pos_last - ($pos_begin + $len_begin));
			}
		}
		
		return "";
	}

	/**
	 * ��ȡ�����õ��ַ���
	 * @param string $string ԭʼ�ַ���
	 * @param string $start ��ʼ�ַ���
	 * @param string $last �����ַ���
	 * @param bool $type ��ȡ����
	 * @return array ���ؽ�ȡ���ַ�������
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
