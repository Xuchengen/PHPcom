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
	 * �Ƴ��ļ����в�ѯ����
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
	 * ��ȡ·���е��ļ���
	 * @param string $path
	 * @return string
	 */
	public static function getFileName($path){
		$path = self::removeQuery($path);
		return basename($path);
	}
	
	/**
	 * ��ȡ�ļ���չ��
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
	 * ��ȡ·��������չ���Ĳ���
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
	 * ��ȡ·���е�Ŀ¼����
	 * @param string $path
	 * @return string
	 */
	public static function getDirectoryName($path){
		return dirname($path);
	}
	
	/**
	 * ����Ƿ�ͼƬ��չ
	 * @param string $extension
	 * @return boolean
	 */
	public static function checkImageExt($extension) {
		static $imgextension = array('jpg', 'jpeg', 'gif', 'png', 'bmp', 'jpe', 'tif', 'tiff');
		return in_array($extension, $imgextension) ? 1 : 0;
	}
	
	/**
	 * ����Ƿ�ͼƬ�ļ�
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
	 * ����Ƿ�ͼƬ MIME ����
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
	 * ����һ������ļ���
	 * @param string $ext �ļ���չ��
	 * @param string $prefix ǰ׺
	 * @return string
	 */
	public static function fileNameRand($ext, $prefix = '') {
		$ext = $ext ? ".$ext" : '';
		return ($prefix ? $prefix : date('His')) . '_' . mt_rand(10000000, 99999999) . $ext;
	}
	
	/**
	 * ��ȡ����Ŀ¼
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
	 * �ݹ鴴��Ŀ¼
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