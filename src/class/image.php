<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : image.php    2011-7-5 23:18:02
 */
!defined('IN_PHPCOM') && exit('Access denied');

class phpcom_image {

    private $settings = array();
    private $Original = '';
    private $Destination = '';
    private $ImageFunc = '';
    private $ImageCreateFromFunc = '';
    private $TempFile = '';
    private $AttachDir = '';
    private $ImageLibType = 0;
    private $ErrorCode = 0;
    public $ImageInfo = array('animated' => 0);
    public $imgresize = array('x' => 0, 'y' => 0, 'width' => 0, 'height' => 0, 'minwidth' => 0, 'minheight' => 0);

    public function __construct($setting = array()) {
        if (!$setting) {
            $setting = phpcom::$setting;
        }
        $this->AttachDir = $setting['attachdir'];
        $this->settings = array(
            'imagelib' => $setting['imagelib'],
            'imageimpath' => $setting['imageimpath'],
            'thumbquality' => $setting['thumbquality'],
            'watermark' => $setting['watermark']
        );
        if(!isset($this->settings['watermark']['gravity'])){
        	$this->settings['watermark']['gravity'] = 1;
        }
    }
	
    public function SetGravity($value) {
    	if($value && !empty($this->settings['watermark']['gravity'])){
    		$this->settings['watermark']['gravity'] = $value;
    	}
    }
    
    public function SetWatermarkFile($value){
    	if(!empty($value)){
    		$this->settings['watermark']['file'] = trim($value);
    	}
    }
    
    public function Thumbnail($filename, $destination, $width, $height, $type = 1, $suffixal = 0) {
        $return = $this->Init($filename, $destination, 'thumbnail', $suffixal);
        if ($return <= 0) {
            return $this->ReturnCode($return);
        }
        if (isset($this->ImageInfo['animated']) && $this->ImageInfo['animated']) {
            return $this->ReturnCode(0);
        }
        $this->settings['thumbwidth'] = $width;
        $this->settings['thumbheight'] = $height;
        $this->settings['thumbtype'] = $type;
        $return = !$this->ImageLibType ? $this->Thumbnail_GD() : $this->Thumbnail_IM();
        $return = !$suffixal ? $return : 0;
        return $this->Sleep($return);
    }

    public function Watermark($filename, $destination = '') {

        $return = $this->Init($filename, $destination, 'watermark');
        if ($return <= 0) {
            return $this->ReturnCode($return);
        }

        if (!$this->settings['watermark']['status'] || ($this->settings['watermark']['minwidth'] && $this->ImageInfo['width'] <= $this->settings['watermark']['minwidth'] && $this->settings['watermark']['minheight'] && $this->ImageInfo['height'] <= $this->settings['watermark']['minheight'])) {
            return $this->ReturnCode(0);
        }
        $this->settings['watermark']['fontpath'] = PHPCOM_ROOT . '/' . $this->settings['watermark']['fontpath'];
        if ($this->settings['watermark']['file']) {
            $ext = $this->FileExt($this->settings['watermark']['file']);
            if ($ext && $ext == $this->settings['watermark']['type']) {
                $this->settings['watermark']['file'] = PHPCOM_ROOT . '/misc/images/' . trim($this->settings['watermark']['file']);
            } else {
                $this->settings['watermark']['file'] = PHPCOM_ROOT . '/misc/images/' . ($this->settings['watermark']['type'] == 'png' ? 'watermark.png' : 'watermark.gif');
            }
        } else {
            $this->settings['watermark']['file'] = PHPCOM_ROOT . '/misc/images/' . ($this->settings['watermark']['type'] == 'png' ? 'watermark.png' : 'watermark.gif');
        }
        if (!is_readable($this->settings['watermark']['file']) || ($this->settings['watermark']['type'] == 'text' && (!file_exists($this->settings['watermark']['fontpath']) || !is_file($this->settings['watermark']['fontpath'])))) {
            return $this->ReturnCode(-3);
        }
        $return = !$this->ImageLibType ? $this->Watermark_GD() : $this->Watermark_IM();
        return $this->Sleep($return);
    }
	
    public function ImageResized($filename, $destination = '', $imgresize = array()){
    	if(empty($imgresize) || !$imgresize['status'] || array_sum($imgresize) == 0){
    		return $this->ReturnCode(0);
    	}
    	$return = $this->Init($filename, $destination, 'resized');
    	if ($return <= 0) {
    		return $this->ReturnCode($return);
    	}
    	if($imgresize){
    		$this->imgresize = $imgresize;
    	}
    	$return = !$this->ImageLibType ? $this->ImageResized_GD() : $this->ImageResized_IM();
    	return $this->Sleep($return);
    }
    
    public function Sleep($return) {
        if ($this->TempFile) {
            @unlink($this->TempFile);
        }
        $this->ImageInfo['size'] = @filesize($this->Destination);
        return $this->ReturnCode($return);
    }

    public function ReturnCode($return) {
        if ($return > 0 && file_exists($this->Destination)) {
            return 1;
        } else {
            $this->ErrorCode = $return;
            return 0;
        }
    }

    public function Error() {
        return $this->ErrorCode;
    }

    public function FileExt($filename) {
        return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
    }

    public function Init($filename, $destination, $type = 'thumbnail', $suffixal = TRUE) {
        $this->ErrorCode = 0;
        if (empty($filename)) {
            return -2;
        }
        $parser = parse_url($filename);
        if (isset($parser['host'])) {
            if (empty($destination)) {
                return -2;
            }
            $data = $this->FileSockOpen($filename);
            $this->TempFile = $filename = tempnam($this->AttachDir . './temp/', 'tmpimg_');
            file_put_contents($filename, $data);
            if (!$data || $filename === FALSE) {
                return -2;
            }
        }
        if ($type == 'thumbnail') {
            if ($destination == 'thumbnail') {
                $destination = !$suffixal ? $this->AddSuffix($filename) : $filename;
            } elseif ($destination == 'preview') {
                $destination = !$suffixal ? $this->AddSuffix($filename, '_small.jpg') : $filename;
            } else {
                $destination = empty($destination) ? (!$suffixal ? $this->AddSuffix($filename) : $filename) : $this->AttachDir . $destination;
            }
        } elseif ($type == 'watermark') {
            $destination = empty($destination) ? $filename : $this->AttachDir . './' . $destination;
        } elseif ($type == 'resized') {
           	$destination = empty($destination) ? $filename : $this->AttachDir . './' . $destination;
        }
        $destdir = dirname($destination);
        mkdirs($destdir);
        clearstatcache();
        if (!is_readable($filename) || !is_writable($destdir)) {
            return -2;
        }
        $imageinfo = @getimagesize($filename);
        if ($imageinfo === FALSE) {
            return -1;
        }
        $this->Original = $filename;
        $this->Destination = $destination;
        $this->ImageInfo['width'] = $imageinfo[0];
        $this->ImageInfo['height'] = $imageinfo[1];
        $this->ImageInfo['mime'] = $imageinfo['mime'];
        $this->ImageInfo['size'] = @filesize($filename);
        $this->ImageLibType = $this->settings['imagelib'] && $this->settings['imageimpath'];
        if (!$this->ImageLibType) {
            switch ($this->ImageInfo['mime']) {
                case 'image/pjpeg':
                case "image/jpg":
                case 'image/jpeg':
                    $this->ImageCreateFromFunc = function_exists('imagecreatefromjpeg') ? 'imagecreatefromjpeg' : '';
                    $this->ImageFunc = function_exists('imagejpeg') ? 'imagejpeg' : '';
                    break;
                case 'image/gif':
                    $this->ImageCreateFromFunc = function_exists('imagecreatefromgif') ? 'imagecreatefromgif' : '';
                    $this->ImageFunc = function_exists('imagegif') ? 'imagegif' : '';
                    break;
                case 'image/x-png':
                case 'image/png':
                    $this->ImageCreateFromFunc = function_exists('imagecreatefrompng') ? 'imagecreatefrompng' : '';
                    $this->ImageFunc = function_exists('imagepng') ? 'imagepng' : '';
                    break;
            }
        } else {
            $this->ImageCreateFromFunc = $this->ImageFunc = TRUE;
        }

        if (!$this->ImageLibType && $this->ImageInfo['mime'] == 'image/gif') {
            if (!$this->ImageCreateFromFunc) {
                return -4;
            }
            if (!($fp = @fopen($filename, 'rb'))) {
                return -2;
            }
            $content = fread($fp, $this->ImageInfo['size']);
            fclose($fp);
            $this->ImageInfo['animated'] = strpos($content, 'NETSCAPE2.0') === FALSE ? 0 : 1;
        }
        return $this->ImageCreateFromFunc ? 1 : 0;
    }

    //FileWithoutExtension
    public function RemoveExtension($filename) {
        $pos = strrpos($filename, '.');
        if ($pos) {
            return substr($filename, 0, $pos);
        } else {
            return $filename;
        }
    }

    public function GetFileName($filename) {
        return end(explode('/', str_replace('\\', '/', $filename)));
    }

    public function AddSuffix($filename, $extension = '_thumb.jpg') {
        return $this->RemoveExtension($filename) . $extension;
    }

    public function AddPrefix($filename, $prefix = 't', $ext = 'jpg') {
        $dir = dirname($filename);
        $filename = $dir . DIRECTORY_SEPARATOR . $prefix . $this->GetFileName($filename);
        $filename = $this->RemoveExtension($filename) . '.' . $ext;
        return $filename;
    }

    public function exec($command) {
        exec($command, $output, $returnvar);
        if (!empty($output) || !empty($returnvar)) {
            return -3;
        }
        return TRUE;
    }

    private function ImageSizeValue($type = 0) {
        $x = $y = $w = $h = 0;
        if ($type > 0) {
            $imgratio = $this->ImageInfo['width'] / $this->ImageInfo['height'];
            $thumbratio = $this->settings['thumbwidth'] / $this->settings['thumbheight'];
            if ($imgratio >= 1 && $imgratio >= $thumbratio || $imgratio < 1 && $imgratio > $thumbratio) {
                $h = $this->ImageInfo['height'];
                $w = $h * $thumbratio;
                $x = ($this->ImageInfo['width'] - $thumbratio * $this->ImageInfo['height']) / 2;
            } elseif ($imgratio >= 1 && $imgratio <= $thumbratio || $imgratio < 1 && $imgratio < $thumbratio) {
                $w = $this->ImageInfo['width'];
                $h = $w / $thumbratio;
            }
        } else {
            $x_ratio = $this->settings['thumbwidth'] / $this->ImageInfo['width'];
            $y_ratio = $this->settings['thumbheight'] / $this->ImageInfo['height'];
            if (($x_ratio * $this->ImageInfo['height']) < $this->settings['thumbheight']) {
                $h = ceil($x_ratio * $this->ImageInfo['height']);
                $w = $this->settings['thumbwidth'];
            } else {
                $w = ceil($y_ratio * $this->ImageInfo['width']);
                $h = $this->settings['thumbheight'];
            }
        }
        return array($x, $y, $w, $h);
    }

    private function LoadSource() {
        $imagecreatefunc = $this->ImageCreateFromFunc;
        $im = @$imagecreatefunc($this->Original);
        if (!$im) {
            if (!function_exists('imagecreatefromstring')) {
                return -4;
            }
            $fp = @fopen($this->Original, 'rb');
            $contents = @fread($fp, filesize($this->Original));
            fclose($fp);
            $im = @imagecreatefromstring($contents);
            if ($im == FALSE) {
                return -1;
            }
        }
        return $im;
    }

    private function Thumbnail_GD() {
        if (!function_exists('imagecreatetruecolor') || !function_exists('imagecopyresampled') || !function_exists('imagecopymerge')) {
            return -4;
        }

        $imagefunc = &$this->ImageFunc;
        $srcim = $this->LoadSource();
        if ($srcim < 0) {
            return $srcim;
        }
        $width = $this->ImageInfo['width'];
        $height = $this->ImageInfo['height'];
        $dstim = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($dstim, 255, 255, 255);
        imagefill($dstim, 0, 0, $bg);
        imagecopy($dstim, $srcim, 0, 0, 0, 0, $width, $height);
        $srcim = $dstim;

        switch ($this->settings['thumbtype']) {
            case 'geom':
            case 1:
                if ($width >= $this->settings['thumbwidth'] || $height >= $this->settings['thumbheight']) {
                    $thumb = array();
                    list(,, $thumb['width'], $thumb['height']) = $this->ImageSizeValue(0);
                    $cx = $this->ImageInfo['width'];
                    $cy = $this->ImageInfo['height'];
                    $thumbim = imagecreatetruecolor($thumb['width'], $thumb['height']);
                    imagecopyresampled($thumbim, $srcim, 0, 0, 0, 0, $thumb['width'], $thumb['height'], $cx, $cy);
                }
                break;
            case 'crop':
            case 2:
                if (!($this->ImageInfo['width'] < $this->settings['thumbwidth'] || $this->ImageInfo['height'] < $this->settings['thumbheight'])) {
                    list($startx, $starty, $cutw, $cuth) = $this->ImageSizeValue(1);
                    $dst_img = imagecreatetruecolor($cutw, $cuth);
                    imagecopymerge($dst_img, $srcim, 0, 0, $startx, $starty, $cutw, $cuth, 100);
                    $thumbim = imagecreatetruecolor($this->settings['thumbwidth'], $this->settings['thumbheight']);
                    imagecopyresampled($thumbim, $dst_img, 0, 0, 0, 0, $this->settings['thumbwidth'], $this->settings['thumbheight'], $cutw, $cuth);
                } else {
                    $thumbim = imagecreatetruecolor($this->settings['thumbwidth'], $this->settings['thumbheight']);
                    $bgcolor = imagecolorallocate($thumbim, 255, 255, 255);
                    imagefill($thumbim, 0, 0, $bgcolor);
                    $startx = ($this->settings['thumbwidth'] - $this->ImageInfo['width']) / 2;
                    $starty = ($this->settings['thumbheight'] - $this->ImageInfo['height']) / 2;
                    imagecopymerge($thumbim, $srcim, $startx, $starty, 0, 0, $this->ImageInfo['width'], $this->ImageInfo['height'], 100);
                }
                break;
            case 'fixed':
            case 3:
                if ($width >= $this->settings['thumbwidth'] || $height >= $this->settings['thumbheight']) {
                    $thumb = array('width' => $this->settings['thumbwidth'], 'height' => $this->settings['thumbheight']);
                    $cx = $this->ImageInfo['width'];
                    $cy = $this->ImageInfo['height'];
                    $thumbim = imagecreatetruecolor($thumb['width'], $thumb['height']);
                    imagecopyresampled($thumbim, $srcim, 0, 0, 0, 0, $thumb['width'], $thumb['height'], $cx, $cy);
                }
                break;
        }
        clearstatcache();
        if ($this->ImageInfo['mime'] == 'image/jpeg') {
            @$imagefunc($thumbim, $this->Destination, $this->settings['thumbquality']);
        } else {
            @$imagefunc($thumbim, $this->Destination);
        }
        return 1;
    }
	
    private function Thumbnail_Imagick() {
    	if(!extension_loaded('imagick')) return 0;
    	$thumbwidth = $this->settings['thumbwidth'];
    	$thumbheight = $this->settings['thumbheight'];
    	$return = 0;
    	$image = new Imagick($this->Original);
    	$image->setImageCompression(Imagick::COMPRESSION_JPEG);
    	$image->setImageCompressionQuality(intval($this->settings['thumbquality']));
    	$image->stripImage();
    	switch ($this->settings['thumbtype']) {
    		case 'geom':
    		case 1:
    			if ($this->ImageInfo['width'] >= $thumbwidth || $this->ImageInfo['height'] >= $thumbheight) {
    				if(@$image->resizeImage($thumbwidth, $thumbheight, imagick::FILTER_LANCZOS, 1, true)){
    					$image->writeImage($this->Destination);
    				}else{
    					if(@$image->thumbnailimage($thumbwidth, $thumbheight, true)){
    						$image->writeImage($this->Destination);
    					}
    				}
    				if(file_exists($this->Destination))  $return = 1;
    			}
    			break;
    		case 'crop':
    		case 2:
    			if (!($this->ImageInfo['width'] < $thumbwidth || $this->ImageInfo['height'] < $thumbheight)) {
    				list($startx, $starty, $cutw, $cuth) = $this->ImageSizeValue(1);
    				if(@$image->cropImage($cutw, $cuth, $startx, $starty)){
    					if(@$image->cropThumbnailImage($thumbwidth, $thumbheight)){
    						$image->writeImage($this->Destination);
    					}
    				}
    			}else{
    				$startx = -($thumbwidth - $this->ImageInfo['width']) / 2;
    				$starty = -($thumbheight - $this->ImageInfo['height']) / 2;
    				if(@$image->cropImage($thumbwidth, $thumbheight, $startx, $starty)){
    					if(@$image->cropThumbnailImage($thumbwidth, $thumbheight)){
    						$image->writeImage($this->Destination);
    					}
    				}
    			}
    			if(file_exists($this->Destination))  $return = 1;
    			break;
    		case 'fixed':
    		case 3:
    			if ($this->ImageInfo['width'] >= $thumbwidth || $this->ImageInfo['height'] >= $thumbheight) {
    				if(@$image->resizeImage($thumbwidth, $thumbheight, imagick::FILTER_LANCZOS, 1)){
    					$image->writeImage($this->Destination);
    				}else{
    					if(@$image->thumbnailimage($thumbwidth, $thumbheight)){
    						$image->writeImage($this->Destination);
    					}
    				}
    				if(file_exists($this->Destination))  $return = 1;
    			}
    			break;
    	}
    	$image->destroy();
    	return $return;
    }
    
    private function Thumbnail_IM() {
    	if(!function_exists('exec') || empty($this->settings['imageimpath'])){
    		return $this->Thumbnail_Imagick();
    	}
    	$return = 0;
        $sizestring = $this->settings['thumbwidth'] . 'x' . $this->settings['thumbheight'];
        $quality = intval($this->settings['thumbquality']);
        $imageimpath = $this->settings['imageimpath'] . '/convert';
        switch ($this->settings['thumbtype']) {
            case 'geom':
            case 1:
                if ($this->ImageInfo['width'] >= $this->settings['thumbwidth'] || $this->ImageInfo['height'] >= $this->settings['thumbheight']) {
                    $command = "$imageimpath -quality $quality -geometry $sizestring {$this->Original} {$this->Destination}";
                    exec($command);
                    if(!file_exists($this->Destination)){
	                    $command = "$imageimpath -quality $quality -resize $sizestring {$this->Original} {$this->Destination}";
	                    exec($command);
                    }
                    if(file_exists($this->Destination))  $return = 1;
                }
                break;
            case 'crop':
            case 2:
                if (!($this->ImageInfo['width'] < $this->settings['thumbwidth'] || $this->ImageInfo['height'] < $this->settings['thumbheight'])) {
                    list($startx, $starty, $cutw, $cuth) = $this->ImageSizeValue(1);
                    $command = $imageimpath . ' -quality ' . $quality . ' -crop ' . $cutw . 'x' . $cuth . '+' . $startx . '+' . $starty . ' ' . $this->Original . ' ' . $this->Destination;
                    exec($command);
                    if(!file_exists($this->Destination))  return 0;
                    $command = $imageimpath . ' -quality ' . $quality . ' -thumbnail ' . $sizestring . ' -resize ' . $sizestring . ' -gravity center -extent ' . $sizestring . ' ' . $this->Destination . ' ' . $this->Destination;
                    exec($command);
                    if(file_exists($this->Destination))  $return = 1;
                } else {
                    $startx = -($this->settings['thumbwidth'] - $this->ImageInfo['width']) / 2;
                    $starty = -($this->settings['thumbheight'] - $this->ImageInfo['height']) / 2;
                    $command = $imageimpath . ' -quality ' . $quality . ' -crop ' . $sizestring . '+' . $startx . '+' . $starty . ' ' . $this->Original . ' ' . $this->Destination;
                    exec($command);
                	if(!file_exists($this->Destination))  return 0;
                    $command = $imageimpath . ' -quality ' . $quality . ' -thumbnail ' . $sizestring . ' -gravity center -extent ' . $sizestring . ' ' . $this->Destination . ' ' . $this->Destination;
                    exec($command);
                    if(file_exists($this->Destination))  $return = 1;
                }
                break;
            case 'fixed':
            case 3:
                if ($this->ImageInfo['width'] >= $this->settings['thumbwidth'] || $this->ImageInfo['height'] >= $this->settings['thumbheight']) {
                    $command = "$imageimpath -quality $quality -resize $sizestring! {$this->Original} {$this->Destination}";
                    exec($command);
                    if(file_exists($this->Destination))  $return = 1;
                }
                break;
        }
        return $return;
    }

    private function Watermark_GD() {
        if (!function_exists('imagecreatetruecolor')) {
            return -4;
        }
        $logowidth = $logoheight = 0;
        $imagefunc = &$this->ImageFunc;
        if ($this->settings['watermark']['type'] != 'text') {
            if (!function_exists('imagecopy') || !function_exists('imagecreatefrompng') || !function_exists('imagecreatefromgif') || !function_exists('imagealphablending') || !function_exists('imagecopymerge')) {
                return -4;
            }
            $watermarkinfo = @getimagesize($this->settings['watermark']['file']);
            if ($watermarkinfo === FALSE) {
                return -3;
            }
            $watermarklogo = $this->settings['watermark']['type'] == 'png' ? @imageCreateFromPNG($this->settings['watermark']['file']) : @imageCreateFromGIF($this->settings['watermark']['file']);
            if (!$watermarklogo) {
                return 0;
            }

            list($logowidth, $logoheight) = $watermarkinfo;
        } else {
            if (!function_exists('imagettfbbox') || !function_exists('imagettftext') || !function_exists('imagecolorallocate')) {
                return -4;
            }
            $watermarktextcvt = pack("H*", $this->settings['watermark']['text']);
            $box = imagettfbbox($this->settings['watermark']['fontsize'], $this->settings['watermark']['angle'], $this->settings['watermark']['fontpath'], $watermarktextcvt);
            $logoheight = max($box[1], $box[3]) - min($box[5], $box[7]);
            $logowidth = max($box[2], $box[4]) - min($box[0], $box[6]);
            $ax = min($box[0], $box[6]) * -1;
            $ay = min($box[5], $box[7]) * -1;
        }
        $wmkwidth = $this->ImageInfo['width'] - $logowidth;
        $wmkheight = $this->ImageInfo['height'] - $logoheight;
        if ($wmkwidth > 10 && $wmkheight > 10 && !$this->ImageInfo['animated']) {
            switch ($this->settings['watermark']['gravity']) {
                case 1: //顶部居左
                    $x = 5;
                    $y = 5;
                    break;
                case 2: //顶部居中
                    $x = ($this->ImageInfo['width'] - $logowidth) / 2;
                    $y = 5;
                    break;
                case 3: //顶部居右
                    $x = $this->ImageInfo['width'] - $logowidth - 5;
                    $y = 5;
                    break;
                case 4: //中部居左
                    $x = 5;
                    $y = ($this->ImageInfo['height'] - $logoheight) / 2;
                    break;
                case 5: //中部居中
                    $x = ($this->ImageInfo['width'] - $logowidth) / 2;
                    $y = ($this->ImageInfo['height'] - $logoheight) / 2;
                    break;
                case 6: //中部居右
                    $x = $this->ImageInfo['width'] - $logowidth;
                    $y = ($this->ImageInfo['height'] - $logoheight) / 2;
                    break;
                case 7: //底部居左
                    $x = 5;
                    $y = $this->ImageInfo['height'] - $logoheight - 5;
                    break;
                case 8: //底部居中
                    $x = ($this->ImageInfo['width'] - $logowidth) / 2;
                    $y = $this->ImageInfo['height'] - $logoheight - 5;
                    break;
                case 9: //底部居右
                    $x = $this->ImageInfo['width'] - $logowidth - 5;
                    $y = $this->ImageInfo['height'] - $logoheight - 5;
                    break;
            }
            if ($this->ImageInfo['mime'] != 'image/png') {
                $imcreatecolor = imagecreatetruecolor($this->ImageInfo['width'], $this->ImageInfo['height']);
            }
            $dstimload = $this->LoadSource();
            if ($dstimload < 0) {
                return $dstimload;
            }
            imagealphablending($dstimload, TRUE);
            imagesavealpha($dstimload, TRUE);
            if ($this->ImageInfo['mime'] != 'image/png') {
                imageCopy($imcreatecolor, $dstimload, 0, 0, 0, 0, $this->ImageInfo['width'], $this->ImageInfo['height']);
                $dstimload = $imcreatecolor;
            }
            if ($this->settings['watermark']['type'] == 'png') {
                imagecopy($dstimload, $watermarklogo, $x, $y, 0, 0, $logowidth, $logoheight);
            } elseif ($this->settings['watermark']['type'] == 'text') {
                if (($this->settings['watermark']['shadowx'] || $this->settings['watermark']['shadowy']) && $this->settings['watermark']['shadowcolor']) {
                    $shadowcolorrgb = $this->rgbToArray($this->settings['watermark']['shadowcolor']);
                    $shadowcolor = imagecolorallocate($dstimload, $shadowcolorrgb[0], $shadowcolorrgb[1], $shadowcolorrgb[2]);
                    imagettftext($dstimload, $this->settings['watermark']['fontsize'], $this->settings['watermark']['angle'], $x + $ax + $this->settings['watermark']['shadowx'], $y + $ay + $this->settings['watermark']['shadowy'], $shadowcolor, $this->settings['watermark']['fontpath'], $watermarktextcvt);
                }

                $colorrgb = $this->rgbToArray($this->settings['watermark']['fontcolor']);
                $color = imagecolorallocate($dstimload, $colorrgb[0], $colorrgb[1], $colorrgb[2]);
                imagettftext($dstimload, $this->settings['watermark']['fontsize'], $this->settings['watermark']['angle'], $x + $ax, $y + $ay, $color, $this->settings['watermark']['fontpath'], $watermarktextcvt);
            } else {
                imagealphablending($watermarklogo, true);
                imagecopymerge($dstimload, $watermarklogo, $x, $y, 0, 0, $logowidth, $logoheight, $this->settings['watermark']['composite']);
            }

            clearstatcache();
            if ($this->ImageInfo['mime'] == 'image/jpeg') {
                @$imagefunc($dstimload, $this->Destination, $this->settings['watermark']['quality']);
            } else {
                @$imagefunc($dstimload, $this->Destination);
            }
        }
        return 1;
    }
	
    private function Watermark_Imagick() {
    	if(!extension_loaded('imagick')) return 0;
    	$composite = intval($this->settings['watermark']['composite']) / 100;
    	$image = new Imagick($this->Original);
    	$image->setImageCompressionQuality($this->settings['watermark']['quality']);
    	$draw = new ImagickDraw();
    	$draw->setGravity($this->settings['watermark']['gravity']);
    	if ($this->settings['watermark']['type'] != 'text') {
    		$water = new Imagick($this->settings['watermark']['file']);
    		if($this->settings['watermark']['type'] != 'png' && $composite < 1){
    			$water->setImageOpacity($composite);
    		}
    		$draw->composite($water->getImageCompose(),0,0,0,0,$water);
    		$image->drawImage($draw);
    		$draw->destroy();
    	}else{
    		$watermarktextcvt = str_replace(array("\n", "\r", "'"), array('', '', '\''), pack("H*", $this->settings['watermark']['text']));
    		$draw->setFont($this->settings['watermark']['fontpath']);
    		$draw->setFontSize($this->settings['watermark']['fontsize']);
    		if($this->settings['watermark']['fontcolor']){
    			$draw->setFillColor(new ImagickPixel($this->settings['watermark']['fontcolor']));
    		}
    		if($this->settings['watermark']['shadowcolor']){
    			$draw->setStrokeColor(new ImagickPixel($this->settings['watermark']['shadowcolor']));
    		}
    		if($composite < 1){
    			$draw->setFillOpacity($composite);
    		}
    		if($this->settings['watermark']['translatex'] || $this->settings['watermark']['translatey']){
    			$draw->translate($this->settings['watermark']['translatex'], $this->settings['watermark']['translatey']);
    		}
    		if($this->settings['watermark']['angle']){
    			$draw->rotate($this->settings['watermark']['angle']);
    		}
    		if($this->settings['watermark']['skewx']) $draw->skewX($this->settings['watermark']['skewx']);
    		if($this->settings['watermark']['skewy']) $draw->skewY($this->settings['watermark']['skewy']);
    		$image->annotateImage($draw, 5, 5, 1, $watermarktextcvt);
    	}
    	$image->writeImage($this->Destination);
    	$image->destroy();
    	return 1;
    }
    
    private function Watermark_IM() {
    	if(!function_exists('exec') || empty($this->settings['imageimpath'])){
    		return $this->Watermark_Imagick();
    	}
        switch ($this->settings['watermark']['gravity']) {
            case 1: $gravity = 'NorthWest'; break;
            case 2: $gravity = 'North'; break;
            case 3: $gravity = 'NorthEast'; break;
            case 4: $gravity = 'West'; break;
            case 5: $gravity = 'Center'; break;
            case 6: $gravity = 'East'; break;
            case 7: $gravity = 'SouthWest'; break;
            case 8: $gravity = 'South'; break;
            case 9: $gravity = 'SouthEast'; break;
        }
        if ($this->settings['watermark']['type'] != 'text') {
            $command = $this->settings['imageimpath'] . '/composite' .
                    ($this->settings['watermark']['type'] != 'png' && $this->settings['watermark']['composite'] != '100' ? ' -watermark ' . $this->settings['watermark']['composite'] : '') .
                    ' -quality ' . $this->settings['watermark']['quality'] .
                    ' -gravity ' . $gravity .
                    ' ' . $this->settings['watermark']['file'] . ' ' . $this->Original . ' ' . $this->Destination;
        } else {
            $watermarktextcvt = str_replace(array("\n", "\r", "'"), array('', '', '\''), pack("H*", $this->settings['watermark']['text']));
            $angle = -$this->settings['watermark']['angle'];
            $translate = $this->settings['watermark']['translatex'] || $this->settings['watermark']['translatey'] ? ' translate ' . $this->settings['watermark']['translatex'] . ',' . $this->settings['watermark']['translatey'] : '';
            $skewX = $this->settings['watermark']['skewx'] ? ' skewX ' . $this->settings['watermark']['skewx'] : '';
            $skewY = $this->settings['watermark']['skewy'] ? ' skewY ' . $this->settings['watermark']['skewy'] : '';
            $command = $this->settings['imageimpath'] . '/convert' .
                    ' -quality ' . $this->settings['watermark']['quality'] .
                    ' -font "' . $this->settings['watermark']['fontpath'] . '"' .
                    ' -pointsize ' . $this->settings['watermark']['fontsize'] .
                    (($this->settings['watermark']['shadowx'] || $this->settings['watermark']['shadowy']) && $this->settings['watermark']['shadowcolor'] ?
                            ' -fill "' . $this->settings['watermark']['shadowcolor'] . '"' .
                            ' -draw "' .
                            ' gravity ' . $gravity . $translate . $skewX . $skewY .
                            ' rotate ' . $angle .
                            ' text ' . $this->settings['watermark']['shadowx'] . ',' . $this->settings['watermark']['shadowy'] . ' \'' . $watermarktextcvt . '\'"' : '') .
                    ' -fill "' . $this->settings['watermark']['fontcolor'] . '"' .
                    ' -draw "' .
                    ' gravity ' . $gravity . $translate . $skewX . $skewY .
                    ' rotate ' . $angle .
                    ' text 0,0 \'' . $watermarktextcvt . '\'"' .
                    ' ' . $this->Original . ' ' . $this->Destination;
        }
        return $this->exec($command);
    }
	
    private function ImageResizeValue(){
    	$resize = $this->imgresize;
    	$w = $this->ImageInfo['width'];
    	$h = $this->ImageInfo['height'];
    	$resize['x'] = min($w, $resize['x']);
    	$resize['y'] = min($h, $resize['y']);
    	$x = $resize['x'];
    	$y = $resize['y'];
    
    	if($resize['x'] > 0){
    		$w -= $resize['x'];
    	}elseif($resize['x'] < 0){
    		$w += $resize['x'];
    		$x = 0;
    	}
    	if($resize['y'] > 0){
    		$h -= $resize['y'];
    	}elseif($resize['y'] < 0){
    		$h += $resize['y'];
    		$y = 0;
    	}
    	if($resize['width'] < 0){
    		$w += $resize['width'];
    	}elseif($resize['width'] > 0){
    		$w = min($w, $resize['width']);
    	}
    	if($resize['height'] < 0){
    		$h += $resize['height'];
    	}elseif($resize['height'] > 0){
    		$h = min($h, $resize['height']);
    	}
    	if($w < 10){
    		$w = $this->ImageInfo['width'];
    		$x = 0;
    	}
    	if($h < 10){
    		$h = $this->ImageInfo['height'];
    		$y = 0;
    	}
    	return array($x, $y, $w, $h);
    }
    
    private function ImageResized_GD(){
    	if (!function_exists('imagecreatetruecolor') || !function_exists('imagecopyresampled') || !function_exists('imagecopymerge')) {
    		return -4;
    	}
    	$imagefunc = &$this->ImageFunc;
    	$srcim = $this->LoadSource();
    	if ($srcim < 0) {
    		return $srcim;
    	}
    	$width = $this->ImageInfo['width'];
    	$height = $this->ImageInfo['height'];
    	$resizeimage = $this->ImageResizeValue();
    	if ($width > $this->imgresize['minwidth'] && $height > $this->imgresize['minheight']) {
    		$thumbim = imagecreatetruecolor($resizeimage[2], $resizeimage[3]);
    		imagecopymerge($thumbim, $srcim, 0, 0, $resizeimage[0], $resizeimage[1], $resizeimage[2], $resizeimage[3], 100);
    	}else{
    		return 0;
    	}
    	clearstatcache();
    	if ($this->ImageInfo['mime'] == 'image/jpeg') {
    		@$imagefunc($thumbim, $this->Destination, 100);
    	} else {
    		@$imagefunc($thumbim, $this->Destination);
    	}
    	return 1;
    }
    
    private function ImageResized_Imagick(){
    	if(!extension_loaded('imagick')) return 0;
    	$width = $this->ImageInfo['width'];
    	$height = $this->ImageInfo['height'];
    	
    	if ($width > $this->imgresize['minwidth'] && $height > $this->imgresize['minheight']) {
    		list($x, $y, $w, $h) = $this->ImageResizeValue();
    		$image = new Imagick($this->Original);
    		if(@$image->cropImage($w, $h, $x, $y)){
    			$image->writeImage($this->Destination);
    		}
    		$image->destroy();
    		if(file_exists($this->Destination)) return 1;
    	}
    	return 0;
    }
    
    private function ImageResized_IM(){
    	if(!function_exists('exec') || !$this->settings['imageimpath']){
    		return $this->ImageResized_Imagick();
    	}
    	$width = $this->ImageInfo['width'];
    	$height = $this->ImageInfo['height'];
    	
    	$imageimpath = $this->settings['imageimpath'] . '/convert';
    	if ($width > $this->imgresize['minwidth'] && $height > $this->imgresize['minheight']) {
	    	list($x, $y, $w, $h) = $this->ImageResizeValue();
	    	$sizestring = $w . 'x' . $h;
	    	$command = $imageimpath . ' -quality 100 -crop ' . $w . 'x' . $h . '+' . $x . '+' . $y . ' ' . $this->Original . ' ' . $this->Destination;
	    	exec($command);
	    	if(file_exists($this->Destination)) return 1;
    	}
    	return 0;
    }
    
    public function rgbToArray($color, $defvalue = array(255, 255, 255))
    {
    	$color = trim($color, "\t\r\n ;rgbRGB()'\"\$");
    	if(preg_match('/#[0-9a-fA-F]{6}$/', $color)){
    		return sscanf($color, '#%2x%2x%2x');
    	}
    	if(preg_match("#(?:(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\s*,\s*){2}(?:\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$#", $color, $matchs)){
    		return explode(',', $matchs[0]);
    	}
    	return $defvalue;
    }
    
    private function FileSockOpen($url) {
        $returndata = '';
        $timeout = 30;
        $block = TRUE;
        $limit = 0;
        $parser = parse_url($url);
        $host = $parser['host'];
        $path = $parser['path'] ? $parser['path'] . (empty($parser['query']) ? '' : '?' . $parser['query']) : '/';
        $port = !empty($parser['port']) ? $parser['port'] : 80;
        $out = "GET $path HTTP/1.0\r\n";
        $out .= "Accept: */*\r\n";
        $out .= "Accept-Language: zh-cn\r\n";
        $out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
        $out .= "Host: $host\r\n";
        $out .= "Connection: Close\r\n\r\n";
        $fp = '';
        if (function_exists('fsockopen')) {
            $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
        } elseif (function_exists('pfsockopen')) {
            $fp = @pfsockopen($host, $port, $errno, $errstr, $timeout);
        }elseif (function_exists('stream_socket_client')) {
			$fp = @stream_socket_client("tcp://$host:$port", $errno, $errstr, $timeout);
        }
        if ($fp) {
            stream_set_blocking($fp, $block);
            stream_set_timeout($fp, $timeout);
            @fwrite($fp, $out);
            $status = stream_get_meta_data($fp);
            if (!$status['timed_out']) {
                while (!feof($fp)) {
                    if (($header = @fgets($fp)) && ($header == "\r\n" || $header == "\n")) {
                        break;
                    }
                }

                $stop = false;
                while (!feof($fp) && !$stop) {
                    $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                    $returndata .= $data;
                    if ($limit) {
                        $limit -= strlen($data);
                        $stop = $limit <= 0;
                    }
                }
            }
            @fclose($fp);
        }
        return $returndata;
    }

}

?>
