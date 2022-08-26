<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Saveimage.php  2012-8-11
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Misc_Saveimage extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		if(!phpcom::$G['uid'] && !phpcom::$G['group']['remoteimage']){
			exit('Access denied');
		}
		set_time_limit(3600);
		@header('Content-Type: text/xml');
		@header('Expires: -1');
		@header("Cache-Control: private");
		@header('Pragma: no-cache');
		
		$doc = new DOMDocument('1.0');
		$doc->formatOutput = TRUE;
		$doc->async = TRUE;
		$result = file_get_contents("php://input");
		$doc->loadXML($result);
		$status = $doc->getElementsByTagName('status')->item(0);
		$curr = $status->getAttribute('curr');
		$currfile = $status->getAttribute('currfile');
		$chanid = intval($status->getAttribute('chanid'));
		$uid = intval($status->getAttribute('uid'));
		$items = $doc->getElementsByTagName('img');
		if(!isset(phpcom::$G['channel'][$chanid])){
			echo $doc->saveXML();
			exit();
		}
		phpcom::$G['channelid'] = $chanid;
		$channel = &phpcom::$G['channel'][$chanid];
		$module = $channel['modules'];
		if(!$channel['downimage'] || !phpcom::$G['group']['remoteimage']) {
			$status->setAttribute('errno', 1);
			echo $doc->saveXML();
			exit();
		}
		if($uid != phpcom::$G['uid'] && phpcom::$G['groupid'] > 3) {
			$status->setAttribute('errno', 1);
			echo $doc->saveXML();
			exit();
		}
		$i = 0;
		$request = new WebRequest();
		$http = $request->getInstance();
		$http->keepOriginal = false;
		$http->downloaded = true;
		if(empty(phpcom::$setting['attachsubdir'])) phpcom::$setting['attachsubdir'] = 'Y/md';
		$attdir = FileUtils::getAttachmentDir(null, phpcom::$setting['attachsubdir']);
		$http->setAttachDir(phpcom::$setting['attachdir'], $module, $attdir);
		
		foreach ($items as $item) {
			$i++;
			$src = $item->getAttribute('src');
			$aid = $doc->createAttribute('aid');
			$aid->value = 0;
			$size = $doc->createAttribute('size');
			$size->value = 0;
			$http->send($src);
			if($http->errno()) {
				$http->close();
				continue;
			}else{
				if($http->download()){
					$attachs = $http->getAttachs();
					$thumb = $remote = $width = 0;
					if($attachs['image'] && !empty($channel)){
						$image = new phpcom_image();
						$image->SetWatermarkFile($channel['waterimage']);
						if(isset($channel['gravity']) && $channel['autogravity']){
							$gravity = $channel['gravity'];
							$keys = array_rand($gravity);
							$image->SetGravity($gravity[$keys]);
						}
						if ($channel['resizeimg']['status'] == 2) {
							if($image->ImageResized($attachs['filename'], '', $channel['resizeimg']) == 1){
								$attachs['size'] = $image->ImageInfo['size'];
							}
						}
						if ($channel['thumbstatus'] && $channel['thumbauto'] >= 2) {
							$thumb = $image->Thumbnail($attachs['filename'], '', $channel['thumbwidth'], $channel['thumbheight'], $channel['thumbstatus'], 0);
						}
						if (phpcom::$setting['watermark']['status'] && $channel['watermark']) {
							$image->Watermark($attachs['filename']);
						}
						list($width) = @getimagesize($attachs['filename']);
					}
					$data = array(
							'uid' => $uid,
							'chanid' => $chanid,
							'filesize' => $attachs['size'],
							'attachment' => $attachs['attachment'],
							'dateline' => TIMESTAMP,
							'image' => $attachs['image'],
							'thumb' => $thumb,
							'remote' => $remote,
							'width' => $width
					);
					$attachid = Attachment::getAttachId($uid, $chanid);
					$data['attachid'] = $attachid;
					$data['module'] = $module;
					DB::insert('attachment_temp', $data);
					if($uid == phpcom::$G['uid']){
						update_membercount(phpcom::$G['uid'], array('todayattachs' => 1, 'todayattachsize' => $attachs['size']));
					}
					$aid->value = $attachid;
					$size->value = $attachs['size'];
				}
			}
		
			$item->setAttributeNode($aid);
			$item->setAttributeNode($size);
			$status->setAttribute('curr', $i);
			$status->setAttribute('currfile', $src);
			$status->setAttribute('errno', 0);
		}
		echo $doc->saveXML();
		flush();
		ob_flush();
		return 0;
	}
}
?>