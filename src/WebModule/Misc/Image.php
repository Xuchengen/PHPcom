<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Image.php  2012-8-10
 */
!defined('IN_PHPCOM') && header('location: ' . phpcom::$G['siteurl'] . 'misc/images/none.gif');

class Misc_Image extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$noneimg = phpcom::$G['siteurl'] . 'misc/images/none.gif';
		
		if (!$this->request->query('aid') || !$this->request->query('size') || !$this->request->query('key')) {
			exit(header("location: $noneimg"));
		}
		
		$nocache = !$this->request->query('nocache') || $this->request->query('nocache') == 'no' ? 0 : 1;
		$type = $this->request->query('type') ? trim($this->request->query('type')) : 'geom';
		$attachid = intval($this->request->query('aid'));
		
		list($w, $h) = explode('x', $this->request->query('size') . 'x');
		$width = intval($w);
		$height = intval($h);
		if ($width < 1 && $height < 1) {
			exit(header("location: $noneimg"));
		}
		
		$thumbfile = 'image/' . $attachid . '_' . $width . '_' . $height . '.jpg';
		$parse = parse_url(phpcom::$setting['attachurl']);
		$attachurl = !isset($parse['host']) ? phpcom::$G['siteurl'] . phpcom::$setting['attachurl'] : phpcom::$setting['attachurl'];
		if (!$nocache) {
			if (file_exists(phpcom::$setting['attachdir'] . $thumbfile)) {
				exit(header('location: ' . $attachurl . $thumbfile));
			}
		}
		
		$this->robotDenied();
		
		if (md5($attachid . '|' . $width . '|' . $height) != trim($this->request->query('key'))) {
			exit(header("location: $noneimg"));
		}
		$table = Attachment::getAttachTableByaid($attachid);
		
		//$chanid = intval($this->request->query('chanid'));
		if (!isset(phpcom::$G['channel']) || empty(phpcom::$G['channel'])) {
			phpcom_cache::load('channel');
		}
		
		$tmp = $this->request->query('tmp');
		$thumb = $this->request->query('thumb') ? 1 : 0;
		$nocache = $tmp ? 1 : $nocache;
		
		if ($attach = DB::fetch_first("SELECT * FROM " . DB::table($table) . " WHERE attachid='$attachid' AND image='1'")) {
			empty(phpcom::$config['output']['gzip']) && @ob_end_clean();
			header('Expires: ' . gmdate('D, d M Y H:i:s', TIMESTAMP + 3600) . ' GMT');
			//header('Expires: -1');
			//header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
			//header('Pragma: no-cache');
			header('Content-Type: image/jpeg');
			$dirname = phpcom::$G['channel'][$attach['chanid']]['modules'];
			if($thumb && $attach['thumb']){
				$attachment = generatethumbname($attach['attachment']);
			}else{
				$attachment = $attach['attachment'];
			}
			if(empty($attach['filename'])){
				$attach['filename'] = basename($attach['attachment']);
			}
			@header('Content-Disposition: inline; filename=' . $attach['filename']);
			if ($attach['remote']) {
				$filename = phpcom::$setting['ftp']['attachurl'] . "$dirname/" . $attachment;
			} else {
				$filename = phpcom::$setting['attachdir'] . "$dirname/" . $attachment;
			}
			
			$image = new phpcom_image();
			if ($image->Thumbnail($filename, $thumbfile, $width, $height, $type, 0)) {
				if ($nocache) {
					@readfile(phpcom::$setting['attachdir'] . $thumbfile);
					@unlink(phpcom::$setting['attachdir'] . $thumbfile);
				} else {
					@header('location: ' . $attachurl . $thumbfile);
				}
			} else {
				@readfile($filename);
			}
		}else{
			header("location: $noneimg");
		}
		return 0;
	}
}
?>