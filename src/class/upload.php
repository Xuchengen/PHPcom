<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : upload.php    2011-7-5 23:19:34
 */
!defined('IN_PHPCOM') && exit('Access denied');
define('UPLOAD_HTTP_ERROR', -1);					//上传错误
define('UPLOAD_MAX_LIMIT_EXCEEDED', 1);				//文件大小超过 PHP 最大上传限制
define('UPLOAD_EXCEEDS_SIZE_LIMIT', 2);				//文件大小超出本站最大上传限制
define('UPLOAD_FILE_ONLY_PARTIALLY', 3);			//文件只有部分被上传
define('UPLOAD_FILE_NOT_FOUND', 4);					//没有文件被上传

define('UPLOAD_INVALID_FILETYPE', 5);				//无效的上传文件类型
define('UPLOAD_INVALID_IMAGE_MIME', 6);				//无效的图片类型
define('UPLOAD_SERVER_IO_ERROR', 7);				//写入文件错误
define('UPLOAD_FILE_DENIED', 8);					//非法操作，上传文件失败
define('UPLOAD_FUNCTION_CLOSED', 9);				//上传功能已关闭
define('UPLOAD_PERMISSION_DENIED', 10);				//没有上传权限

class phpcom_upload {

    private $ErrorCode = 0;
    private $Channel = '';
    private $AttachSubdir = 'Y/md';
    private $BaseDir = null;
    private $IsTemp = false;
    public $AttachDir = '';
    public $UploadStatus = 0;
    public $MaxSize = 0;
    public $AllowExt = array();
    public $AllowAttachExt = array();
    public $PostFiles = array();

    public function __construct() {
        $this->AttachDir = phpcom::$setting['attachdir'];
        $this->UploadStatus = phpcom::$setting['uploadstatus'];
        $this->MaxSize = phpcom::$setting['attachmaxsize'];
        $this->AllowExt = array('gif', 'jpg', 'png', 'rar', 'zip');
        $this->AllowAttachExt = phpcom::$setting['allowattachext'];
        if(empty(phpcom::$setting['attachsubdir'])){
        	$this->AttachSubdir = 'Y/md';
        }else{
        	$this->AttachSubdir = trim(phpcom::$setting['attachsubdir'], '/\ \t\r\n');
        }
        $this->BaseDir = PHPCOM_ROOT . DIRECTORY_SEPARATOR;
    }

    public function Init($files, $channel = 'misc', $istmp = false) {
        $this->PostFiles = $files;
        $this->IsTemp = $istmp;
        if ($this->UploadStatus != 1 && $this->UploadStatus != 2) {
            return $this->SetErrorCode(UPLOAD_FUNCTION_CLOSED);
        }
        if (!is_array($this->AllowExt)) {
            $this->AllowExt = explode(',', $this->AllowExt);
        }
        if (!is_array($this->AllowAttachExt)) {
            $this->AllowAttachExt = explode(',', $this->AllowAttachExt);
        }
        if (!is_array($files) || empty($files) || !phpcom_upload::IsUploadFile($files['tmp_name']) || trim($files['name']) == '' || $files['size'] == 0) {
            return $this->SetErrorCode(UPLOAD_FILE_NOT_FOUND);
        } else {
            if ($files['error'] != 0) {
                return $this->SetErrorCode($files['error']);
            }
            $files['name'] = trim($files['name']);
            $files['ext'] = phpcom_upload::FileExt($files['name']);
            if (!in_array($files['ext'], $this->AllowExt)) {
                return $this->SetErrorCode(UPLOAD_INVALID_FILETYPE);
            }
            if ($this->ForbidExtension($files['ext'])) {
                return $this->SetErrorCode(UPLOAD_INVALID_FILETYPE);
            }

            if ($this->UploadStatus == 2) {
                $files['image'] = $this->IsImageFile($files['tmp_name']);
            } else {
                $files['image'] = $this->IsImageMime($files['ext'], $files['type']);
            }
            if ($this->UploadStatus == 2 && !$files['image']) {
                return $this->SetErrorCode(UPLOAD_INVALID_IMAGE_MIME);
            }

            $this->Channel = phpcom_upload::CheckChannelDir($channel);
            $files['size'] = intval($files['size']);
            if ($files['size'] < 100) {
                return $this->SetErrorCode(UPLOAD_FILE_NOT_FOUND);
            }
            if ($this->MaxSize && $files['size'] > $this->MaxSize) {
                return $this->SetErrorCode(UPLOAD_EXCEEDS_SIZE_LIMIT);
            }
            $files['thumb'] = '';
            $files['name'] = htmlcharsencode($files['name'], ENT_QUOTES);
            if (strlen($files['name']) > 90) {
                $files['name'] = strcut($files['name'], 80, '') . '.' . $files['ext'];
            }
			$files['tmpname'] = null;
            $files['extension'] = $this->CheckExtension($files['ext']);
            $files['attachdir'] = trim($this->getAttachSubdir($channel), "/ \\\t");
            $files['attachment'] = $files['attachdir'] . '/' . $this->RandFileName($files['extension']);
            $files['destination'] = $this->AttachDir . './' . $this->Channel . '/' . $files['attachment'];
            $this->PostFiles = $files;
            $this->ErrorCode = 0;

            return TRUE;
        }
    }

    public function SaveAs($ignored = FALSE) {
        if ($this->ErrorCode) {
            @unlink($this->PostFiles['tmp_name']);
            return FALSE;
        }
        $this->PostFiles['tmpname'] = null;
        $destination = $this->PostFiles['destination'];
        if($this->IsTemp){
        	$destination = $this->BaseDir . 'data/tmp/' . basename($destination);
        	$this->PostFiles['tmpname'] = $destination;
        }
        if ($ignored) {
            if (!$this->SaveToFile($this->PostFiles['tmp_name'], $destination)) {
                $this->ErrorCode = UPLOAD_SERVER_IO_ERROR;
                return FALSE;
            } else {
                $this->ErrorCode = 0;
                return TRUE;
            }
        }
        if (empty($this->PostFiles) || empty($this->PostFiles['tmp_name']) || empty($destination)) {
            $this->ErrorCode = UPLOAD_FILE_NOT_FOUND;
        } elseif (in_array($this->Channel, array('common', 'users')) && !$this->PostFiles['image']) {
            $this->ErrorCode = UPLOAD_INVALID_IMAGE_MIME;
        } elseif (!$this->SaveToFile($this->PostFiles['tmp_name'], $destination)) {
            $this->ErrorCode = UPLOAD_SERVER_IO_ERROR;
        } elseif (($this->PostFiles['image'] || $this->PostFiles['ext'] == 'swf') && (!$this->PostFiles['imageinfo'] = $this->GetImageInfo($destination, TRUE))) {
            $this->ErrorCode = UPLOAD_INVALID_IMAGE_MIME;
            @unlink($destination);
        } else {
            $this->ErrorCode = 0;
            return TRUE;
        }
        return FALSE;
    }

    public function Error() {
        return $this->ErrorCode;
    }

    public function SetErrorCode($code) {
        @unlink($this->PostFiles['tmp_name']);
        $this->PostFiles = array();
        $this->ErrorCode = $code;
        return FALSE;
    }

    public function ErrorMessage() {
        return lang('error', 'upload_error_' . $this->ErrorCode);
    }

    public function CheckExtension($ext) {
        if (empty($this->AllowAttachExt)) {
            $this->AllowAttachExt = array('attach', 'jpg', 'jpeg', 'gif', 'png', 'jpe', 'swf', 'pdf', 'bmp', 'txt', 'zip', 'rar', 'mp3');
        }
        return strtolower(!in_array(strtolower($ext), $this->AllowAttachExt) ? 'attach' : $ext);
    }

    public function ForbidExtension($ext) {
        if (!empty($ext)) {
            static $forbidexts = array('php', 'do', 'asp', 'asa', 'aspx', 'asax', 'jsp', 'cer', 'cdx', 'htr', 'shtml', 'shtm');
            return in_array(strtolower($ext), $forbidexts);
        }
        return TRUE;
    }
	
    public function checkAttachSubdir($subdir = null) {
    	return $subdir;
    }
    
    public function getAttachSubdir($channel) {
    	$subdir = '';
    	if ($channel != 'tmp' && $channel != 'temp') {
    		if(!($subdir = @date($this->AttachSubdir))){
    			$subdir = date('Y/md');
    		}
    	}
    	$this->createAttachSubdir($channel, $subdir);
    	return $subdir;
    }
    
    private function createAttachSubdir($channel = '', $subdir = '') {
    	$channel = phpcom_upload::CheckChannelDir($channel);
    	$basedir = $this->AttachDir . './' . $channel;
    	if(($flag = phpcom_upload::MakeDir($basedir)) && !empty($subdir)){
    		$subdirs = explode('/', $subdir);
    		$directory = $basedir;
    		foreach($subdirs as $dir){
    			if($dir !== ''){
    				$directory .= "/$dir";
    				phpcom_upload::MakeDir($directory);
    			}
    		}
    	}
    	return $flag;
    }
    
    public static function CheckChannelDir($channel) {
        return $channel ? $channel : 'misc';
    }

    public static function FileExt($filename) {
        return trim(addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10))));
    }

    public static function IsImageExt($extension) {
        static $imgextension = array('jpg', 'jpeg', 'gif', 'png', 'bmp', 'jpe', 'tif', 'tiff');
        return in_array($extension, $imgextension);
    }

    public function IsImageMime($extension, $mime) {
        static $imagemimes = array('image/jpeg', 'image/jpg', 'image/pjpeg', 'image/gif', 'image/png', 'image/x-png', 'image/bmp', 'image/x-ms-bmp', 'image/tiff', 'application/octet-stream'); //, 'application/octet-stream'
        $flag = phpcom_upload::IsImageExt($extension);
        if ($flag) {
            $flag = in_array($mime, $imagemimes);
        }
        return $flag;
    }

    public function IsImageFile($filename) {
        if ($im = getimagesize($filename)) {
            if (isset($im[2]) && isset($im['mime'])) {
                if ($im[0] && !in_array($im[2], array(4, 5, 13))) {
                    return 1;
                }
            }
        }
        return 0;
    }

    public static function IsUploadFile($filename) {
        return $filename && ($filename != 'none') && (is_uploaded_file($filename) || is_uploaded_file(str_replace('\\\\', '\\', $filename)));
    }

    public function RandFileName($ext) {
        $filename = date('His') . '_' . str_pad(mt_rand(10000000, 99999999), 8 ,'0') . '.' . str_replace('.', '', $ext);
        return $filename;
    }

    public function GetImageInfo($filename, $allowswf = FALSE) {
        $extension = phpcom_upload::FileExt($filename);
        $isimage = phpcom_upload::IsImageExt($extension);
        if (!$isimage && ($extension != 'swf' || !$allowswf)) {
            return FALSE;
        } elseif (!is_readable($filename)) {
            return FALSE;
        } elseif ($imageinfo = @getimagesize($filename)) {
            list($width, $height, $type) = !empty($imageinfo) ? $imageinfo : array('', '', '');
            $size = $width * $height;
            if ($size > 16777216 || $size < 16) {
                return FALSE;
            } elseif ($extension == 'swf' && $type != 4 && $type != 13) {
                return FALSE;
            } elseif ($isimage && !in_array($type, array(1, 2, 3, 6, 7, 13))) {
                return FALSE;
            }
            return $imageinfo;
        } else {
            return FALSE;
        }
    }
	
    public function SaveToFile($filename, $destination) {
        $uploadflag = FALSE;
        if (!phpcom_upload::IsUploadFile($filename)) {
            $uploadflag = FALSE;
        } elseif (@copy($filename, $destination)) {
            $uploadflag = TRUE;
        } elseif (function_exists('move_uploaded_file') && @move_uploaded_file($filename, $destination)) {
            $uploadflag = TRUE;
        } elseif (@is_readable($filename) && (@$fp_file = fopen($filename, 'rb')) && (@$fp_dest = fopen($destination, 'wb'))) {
            while (!feof($fp_file)) {
                $read = @fread($fp_file, 1024 * 512);
                @fwrite($fp_dest, $read);
            }
            fclose($fp_file);
            fclose($fp_dest);
            $uploadflag = TRUE;
        }
        $this->ErrorCode = 0;
        if ($uploadflag) {
            @chmod($destination, 0644);
        }
        @unlink($filename);
        return $uploadflag;
    }
	
    public function MoveToFile($filename, $destination) {
    	$flag = false;
    	if(file_exists($filename)){
    		if(@rename($filename, $destination)){
    			$flag = true;
    		}elseif (@copy($filename, $destination)) {
    			$flag = true;
    		} elseif (@is_readable($filename) && (@$fp_file = fopen($filename, 'rb')) && (@$fp_dest = fopen($destination, 'wb'))) {
	            while (!feof($fp_file)) {
	                $read = @fread($fp_file, 1024 * 512);
	                @fwrite($fp_dest, $read);
	            }
	            fclose($fp_file);
	            fclose($fp_dest);
	            $flag = true;
	        }
	        if ($flag) {
	        	@chmod($destination, 0644);
	        }
	        if(file_exists($filename)){
	        	@unlink($filename);
	        }
    	}
    	return $flag;
    }
    
    public static function mkdirs($dir, $index = true) {
    	$flag = true;
    	if ($dir && !is_dir($dir)) {
    		self::mkdirs(dirname($dir), $index);
    		$flag = @mkdir($dir, 0777);
    		$index && @touch($dir . '/index.html');
    	}
    	return $flag;
    }
    
    public static function MakeDir($dir, $index = TRUE) {
        $flag = TRUE;
        if (!is_dir($dir)) {
            $flag = @mkdir($dir, 0777);
            $index && @touch($dir . '/index.html');
        }
        return $flag;
    }

}

?>
