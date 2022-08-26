<?php
/**
 * Copyright (c) 2010-2012 phpmain.com - All rights reserved.
 * Our Website : www.phpmain.com www.phpmain.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPMain.
 * This File   : utils.php  2012-5-9
 */
!defined('IN_PHPCOM') && exit('Access denied');

class FileUtils {
	
	/**
	 * 移除文件名中查询部分
	 * @param string $fileName
	 * @return string
	 */
	public static function removeQuery($fileName){
		if($pos = strpos($fileName, '?')){
			$fileName = substr($fileName, 0, $pos);
		}
		if($pos = strpos($fileName, '#')){
			$fileName = substr($fileName, 0, $pos);
		}
		return $fileName;
	}
	
	/**
	 * 获取路径中的文件名
	 * @param string $path
	 * @return string
	 */
	public static function getFileName($path){
		$path = self::removeQuery($path);
		return basename($path);
	}
	
	/**
	 * 获取文件扩展名
	 * @param string $fileName
	 * @return string
	 */
	public static function getExtension($fileName){
		$fileName = self::removeQuery($fileName);
		if($pos = strrpos($fileName, '.')){
			return trim(addslashes(strtolower(substr($fileName, $pos + 1, 10))));
		}else{
			return '';
		}
	}
	
	/**
	 * 获取路径中无扩展名的部分
	 * @param string $path
	 * @return string
	 */
	public static function getWithoutExtension($path){
		$path = self::removeQuery($path);
		if($pos = strrpos($path, '.')){
			return substr($path, 0, $pos);
		}else{
			return rtrim($path, '/\ ');
		}
	}
	
	/**
	 * 获取路径中的目录部分
	 * @param string $path
	 * @return string
	 */
	public static function getDirectoryName($path){
		return dirname($path);
	}
	
	/**
	 * 检测是否图片扩展
	 * @param string $extension
	 * @return boolean
	 */
	public static function checkImageExt($extension) {
		static $imgextension = array('jpg', 'jpeg', 'gif', 'png', 'bmp', 'jpe', 'tif', 'tiff');
		return in_array($extension, $imgextension) ? 1 : 0;
	}
	
	/**
	 * 检测是否图片文件
	 * @param string $filename
	 * @return boolean
	 */
	public static function checkImageFile($filename) {
		if ($im = getimagesize($filename)) {
			if (isset($im[2]) && isset($im['mime'])) {
				if ($im[0] && !in_array($im[2], array(4, 5, 13))) {
					return TRUE;
				}
			}
		}
		return FALSE;
	}
	
	/**
	 * 检测是否图片 MIME 类型
	 * @param string $extension
	 * @param string $mime
	 * @return boolean
	 */
	public static function checkImageMime($extension, $mime) {
		static $imagemimes = array('image/jpeg', 'image/pjpeg', 'image/gif', 'image/png', 'image/x-png', 'image/bmp', 'image/x-ms-bmp', 'image/tiff', 'application/octet-stream'); //, 'application/octet-stream'
		$flag = self::checkImageExt($extension);
		if ($flag) {
			$flag = in_array($mime, $imagemimes);
		}
		return $flag;
	}
	/**
	 * 生成一个随机文件名
	 * @param string $ext 文件扩展名
	 * @param string $prefix 前缀
	 * @return string
	 */
	public static function fileNameRand($ext, $prefix = '') {
		$ext = $ext ? ".$ext" : '';
		return ($prefix ? $prefix : date('His')) . '_' . mt_rand(10000000, 99999999) . $ext;
	}
	
	/**
	 * 获取附件目录
	 * @param string $tmp
	 * @return string
	 */
	public static function getAttachmentDir($tmp = '', $subdir = 'Y/md'){
		$dir = '';
		if ($tmp != 'tmp' && $tmp != 'temp') {
			if(empty($subdir)) $subdir = 'Y/md';
			if(!($dir = @date($subdir))){
				$dir = date('Y/md');
			}
		}
		return $dir;
	}
	
	/**
	 * 递归创建目录
	 * @param string $dir
	 * @param string $index
	 * @return boolean
	 */
	public static function rmkdir($dir, $index = TRUE) {
		$flag = TRUE;
		if ($dir && !is_dir($dir)) {
			self::rmkdir(dirname($dir), $index);
			@mkdir($dir, 0777);
			$index && @touch($dir . '/index.html');
		}
		return TRUE;
	}

}
?>