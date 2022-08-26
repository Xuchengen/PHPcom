<?php
/**
 * Copyright (c) 2010-2012 phpmain.com - All rights reserved.
 * Our Website : www.phpmain.com www.phpmain.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPMain.
 * This File   : cryptutils.php  2012-5-8
 */
!defined('IN_PHPCOM') && exit('Access denied');

define('MCRYPT_EXISTS', function_exists('mcrypt_encrypt'));

class CryptUtils {

	public static function encode($string, $key = ''){
		$key = md5($key);
		if(MCRYPT_EXISTS){
			$cipher = self::mcrypt_encode($string, $key);
		}else{
			$cipher = self::xor_encode($string, $key);
		}
		return base64_encode($cipher);
	}

	public static function decode($string, $key = ''){
		$key = md5($key);
		$decipher = base64_decode((string)$string);
		if(MCRYPT_EXISTS){
			$decipher = self::mcrypt_decode($decipher, $key);
		}else{
			$decipher = self::xor_decode($decipher, $key);
		}
		return $decipher;
	}

	private static function mcrypt_encode($data, $key){
		$init_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
		$init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);
		$data = $init_vect . mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $init_vect);
		return self::add_cipher_noise($data, $key);
	}

	private static function mcrypt_decode($data, $key){
		$data = self::remove_cipher_noise($data, $key);
		$init_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
		if ($init_size > strlen($data)){
			return FALSE;
		}
		$init_vect = substr($data, 0, $init_size);
		$data = substr($data, $init_size);
		return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $init_vect), "\0");
	}

	private static function xor_encode($string, $key){
		$rand = '';
		while (strlen($rand) < 32){
			$rand .= mt_rand(0, mt_getrandmax());
		}
		$rand = sha1($rand);
		$cipher = '';
		for ($i = 0, $len = strlen($string); $i < $len; $i++){
			$cipher .= substr($rand, ($i % strlen($rand)), 1);
			$cipher .= (substr($rand, ($i % strlen($rand)), 1) ^ substr($string, $i, 1));
		}

		return self::xor_merge($cipher, $key);
	}

	private static function xor_decode($string, $key){
		$string = self::xor_merge($string, $key);
		$decipher = '';
		for ($i = 0, $len = strlen($string); $i < $len; $i++){
			$decipher .= (substr($string, $i++, 1) ^ substr($string, $i, 1));
		}
		return $decipher;
	}

	private static function xor_merge($string, $key){
		$hash = sha1($key);
		$str = '';
		for ($i = 0, $len = strlen($string); $i < $len; $i++){
			$str .= substr($string, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
		}
		return $str;
	}

	private static function add_cipher_noise($data, $key){
		$keyhash = sha1($key);
		$keylen = strlen($keyhash);
		$str = '';
		for ($i = 0, $j = 0, $len = strlen($data); $i < $len; ++$i, ++$j){
			if ($j >= $keylen){
				$j = 0;
			}
			$str .= chr((ord($data[$i]) + ord($keyhash[$j])) % 256);
		}
		return $str;
	}

	private static function remove_cipher_noise($data, $key){
		$keyhash = sha1($key);
		$keylen = strlen($keyhash);
		$str = '';
		for ($i = 0, $j = 0, $len = strlen($data); $i < $len; ++$i, ++$j){
			if ($j >= $keylen){
				$j = 0;
			}
			$temp = ord($data[$i]) - ord($keyhash[$j]);
			if ($temp < 0){
				$temp = $temp + 256;
			}
			$str .= chr($temp);
		}
		return $str;
	}
}
?>