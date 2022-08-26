<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : fileupload.php    2011-7-5 22:57:03
 */
!defined('IN_PHPCOM') && exit('Access denied');

class FileUpload {

	private $uid = 0;
	private $attachid = 0;
	private $tmpid = 0;
	private $chanid = 0;
	private $module = 'image';
	private $simple;
	private $files = array();
	private $errsizelimit;
	private $fileMessage = array();
	private $initialized = false;
	private $thumbnone = false;

	public function __construct() {
		$this->fileMessage['status'] = -1;
		$this->fileMessage['type'] = 'image';
		$this->fileMessage['maxsize'] = 0;
		$this->fileMessage['size'] = 0;
		$this->fileMessage['image'] = 0;
	}
	
	public function initialize($mode = 'simple')
	{
		if($this->initialized) return false;
		$this->simple = isset(phpcom::$G['gp_simple']) ? trim(phpcom::$G['gp_simple']) : 0;
		if($mode != 'simple'){
			phpcom::$G['uid'] = $this->uid = isset(phpcom::$G['gp_uid']) ? intval(phpcom::$G['gp_uid']) : 0;
			$swfhash = md5(substr(md5(phpcom::$config['security']['key']), 8) . $this->uid);
			if(!isset(phpcom::$G['gp_hash']) || phpcom::$G['gp_hash'] != $swfhash){
				$this->message(10);
			}
		}else{
			$this->uid = phpcom::$G['uid'];
		}
		if(phpcom::$G['uid'] < 1){
			$this->message(10);
		}
		if (!phpcom::$G['member']['groupid'] = DB::result_first("SELECT groupid FROM " . DB::table('members') . " WHERE uid='" . $this->uid . "'")) {
			$this->message(10);
		}
		phpcom::$G['groupid'] = phpcom::$G['member']['groupid'];
		phpcom_cache::load('usergroup_' . phpcom::$G['groupid']);
		
		if (empty(phpcom::$setting['uploadstatus']) || empty(phpcom::$G['group']['allowupload'])) {
			$this->message(10);
		}
		$this->thumbnone = empty(phpcom::$G['gp_thumbnone']) ? false : true;
		phpcom::$G['gp_type'] = isset(phpcom::$G['gp_type']) ? trim(phpcom::$G['gp_type']) : 'image';
		phpcom::$G['gp_type'] = phpcom::$G['gp_type'] != 'file' && phpcom::$G['gp_type'] != 'attach' ? 'image' : 'file';
		$this->chanid = isset(phpcom::$G['gp_chanid']) ? intval(phpcom::$G['gp_chanid']) : 0;
		Attachment::setExtensionAndSize(phpcom::$G['gp_type'] ? trim(phpcom::$G['gp_type']) : 'image', $this->chanid);
		$this->initialized = true;
	}
	
	private function beginUpload($module = 'image', $istmp = false)
	{
		$upload = new phpcom_upload();
		$upload->uploadStatus = phpcom::$G['group']['allowupload'];
		$upload->allowExt = phpcom::$G['group']['attachext'];
		$upload->maxSize = phpcom::$G['group']['maxattachsize'];
		if(!isset($_FILES['Filedata'])){
			$this->message(4);
		}
		$upload->init($_FILES['Filedata'], $module, $istmp);
		$this->files = &$upload->PostFiles;
		if ($upload->error()) {
			$this->message(4);
		}
		$this->fileMessage['size'] = $this->files['size'];
		$this->fileMessage['maxsize'] = phpcom::$G['group']['maxattachsize'];
		$this->fileMessage['image'] = $this->files['image'];
		$this->fileMessage['ext'] = $this->files['ext'];
		$this->fileMessage['type'] = phpcom::$G['gp_type'] == 'image' ? 'image' : 'attach';
		if (empty($this->files['size'])) {
			$this->message(2);
		}
		if(phpcom::$G['group']['maxattachsize'] && $this->files['size'] > phpcom::$G['group']['maxattachsize']){
			$this->errsizelimit = phpcom::$G['group']['maxattachsize'];
			$this->message(3);
		}
		
		$upload->saveAs();
		if ($upload->error() == -103) {
			$this->message(7);
		} elseif ($upload->error()) {
			$this->message(8);
		}
	}
	
	public function normalUpload($module = 'image')
	{
		$this->initialize('simple');
		$this->simple = 1;
		$this->beginUpload($module, true);
	}
	
	public function swfUpload()
	{
		$this->initialize('swfupload');
		$this->attachid = isset(phpcom::$G['gp_aid']) ? intval(phpcom::$G['gp_aid']) : 0;
		$this->fileMessage['queueid'] = isset(phpcom::$G['gp_queueid']) ? intval(phpcom::$G['gp_queueid']) : 0;
		if(!isset(phpcom::$G['cache']['channel']['channelid'])){
			$this->message(0);
		}
		$chanid = 0;
		$module = 'tmp';
		$channel = &phpcom::$G['cache']['channel'];
		if(isset($channel['channelid']) && isset($channel['modules'])){
			$chanid = $channel['channelid'];
			$module = $channel['modules'];
		}else{
			$this->message(0);
		}
		
		if ($channel && $channel['type'] != 'system' && $channel['type'] != 'expand') {
			$this->message(0);
		}
		$allowupload = !phpcom::$G['group']['dayattachnum'] || phpcom::$G['group']['dayattachnum'] && phpcom::$G['group']['dayattachnum'] > getuserdata('todayattachs');
		if (!$allowupload) {
			$this->message(5);
		}
		$this->beginUpload($module);
		$thumb = $preview = $remote = $width = 0;
		if (phpcom::$G['gp_type'] == 'image' && !$this->files['image']) {
			$this->message(6);
		}
		$filesize = $this->files['size'];
		if (!$this->thumbnone && $this->files['image'] && !empty($channel)) {
			$image = new phpcom_image();
			$image->SetWatermarkFile($channel['waterimage']);
			if(isset($channel['gravity']) && $channel['autogravity']){
				$gravity = $channel['gravity'];
				$keys = array_rand($gravity);
				$image->SetGravity($gravity[$keys]);
			}
			if ($channel['resizeimg']['status']) {
				if($image->ImageResized($this->files['destination'], '', $channel['resizeimg']) == 1){
					$filesize = $image->ImageInfo['size'];
				}
			}
			if ($channel['thumbstatus'] && $channel['thumbauto'] >= 2) {
				$thumb = $image->Thumbnail($this->files['destination'], '', $channel['thumbwidth'], $channel['thumbheight'], $channel['thumbstatus'], 0);
			}
			/*phpcom::$G['gp_preview'] = isset(phpcom::$G['gp_preview']) ? phpcom::$G['gp_preview'] : 0;
			if ($channel['previewstatus'] && ($channel['previewauto'] >= 2 || !empty(phpcom::$G['gp_preview']))) {
				$preview = $image->Thumbnail($this->files['destination'], 'preview', $channel['previewwidth'], $channel['previewheight'], $channel['previewstatus'], $channel['previewzoom']);
				$preview = $preview ? 1 : 0;
			}*/
			if (phpcom::$setting['watermark']['status'] && $channel['watermark']) {
				$image->Watermark($this->files['destination']);
			}
			list($width) = @getimagesize($this->files['destination']);
		}
		if (phpcom::$G['gp_type'] != 'image' && $this->files['image']) {
			$this->files['image'] = 1;
		}
		$this->fileMessage['attachment'] = $this->files['attachment'];
		$data = array(
				'uid' => $this->uid,
				'chanid' => $chanid,
				'filesize' => $filesize,
				'attachment' => $this->files['attachment'],
				'dateline' => TIMESTAMP,
				'image' => $this->files['image'],
				'thumb' => $thumb,
				'remote' => $remote,
				'width' => $width
		);
		$isupdate = FALSE;
		if($this->simple == 2 && $this->attachid){
			$isupdate = $this->updateUploadAttach($this->attachid, $this->uid, $chanid, $data, $module);
		}
		if(!$isupdate){
			//$this->attachid = DB::insert("attachment_$module", $data, TRUE);
			$this->attachid = Attachment::getAttachId($this->uid, $chanid);
			$data['attachid'] = $this->attachid;
			$data['module'] = $module;
			DB::insert('attachment_temp', $data);
			if($this->uid == phpcom::$G['uid']){
				update_membercount(phpcom::$G['uid'], array('todayattachs' => 1, 'todayattachsize' => $this->files['size']));
			}
		}
		$this->message(0);
	}
	
	public function threadImageUpload()
	{
		phpcom::$G['gp_type'] = 'image';
		$this->initialize('simple');
		$this->simple = 1;
		$chanid = 0;
		$module = 'tmp';
		$do = empty(phpcom::$G['gp_do']) ? 'thumb' : trim(phpcom::$G['gp_do']);
		$channel = &phpcom::$G['cache']['channel'];
		if(isset($channel['channelid']) && isset($channel['modules'])){
			$chanid = $channel['channelid'];
			$module = $channel['modules'];
		}else{
			$this->message(0);
		}
		
		if ($channel && $channel['type'] != 'system' && $channel['type'] != 'expand') {
			$this->message(0);
		}
		$allowupload = !phpcom::$G['group']['dayattachnum'] || phpcom::$G['group']['dayattachnum'] && phpcom::$G['group']['dayattachnum'] > getuserdata('todayattachs');
		if (!$allowupload) {
			$this->message(5);
		}
		$thumb = $width = 0;
		$this->beginUpload($module);
		$filesize = $this->files['size'];
		if (!$this->thumbnone && $this->files['image']) {
			$image = new phpcom_image();
			$nozoom = isset(phpcom::$G['gp_nozoom']) ? phpcom::$G['gp_nozoom'] : 0;
			if ($do == 'thumb' && $channel['thumbstatus'] && ($channel['thumbauto'] == 1 || $channel['thumbauto'] == 3)) {
				$thumb = $image->Thumbnail($this->files['destination'], '', $channel['thumbwidth'], $channel['thumbheight'], $channel['thumbstatus'], $channel['thumbzoom']);
			}elseif ($do == 'preview' && $channel['previewstatus'] && $channel['previewzoom'] && !$nozoom) {
				$image->Thumbnail($this->files['destination'], '', $channel['previewwidth'], $channel['previewheight'], $channel['previewstatus'], 1);
			}
		}
		
		$data = array(
				'uid' => $this->uid,
				'dirname' => $module,
				'filename' => $this->files['attachment'],
				'filesize' => $filesize,
				'dateline' => TIMESTAMP,
				'image' => $this->files['image'],
				'thumb' => $thumb,
				'width' => $width
		);
		$this->attachid = DB::insert('upload_temp', $data, true);
		if (!$this->files['image']) {
			$this->message(6);
		}
		$this->fileMessage['module'] = $module;
		$this->fileMessage['dirname'] = $module;
		$this->fileMessage['attachment'] = $this->files['attachment'];
		$this->fileMessage['do'] = $do;
		$this->message(0);
	}
	
	public function topicImageUpload()
	{
		phpcom::$G['gp_type'] = 'image';
		$this->initialize('simple');
		$this->simple = 1;
		$this->simpleUpload('special');
	}
	
	public function simpleUpload($module = null)
	{
		$this->initialize('simple');
		$this->simple = 1;
		if(empty($module)){
			$module = isset(phpcom::$G['gp_dirname']) ? trim(phpcom::$G['gp_dirname']) : 'image';
		}
		if(!preg_match("/^[\w]{2,32}$/", $module)){
			$this->message(0);
		}
		
		$thumb = $remote = $width = 0;
		$this->beginUpload($module);
		
		$filesize = $this->files['size'];
		if (!$this->thumbnone && $this->files['image']) {
			if(isset(phpcom::$G['gp_thumboriginal']) || isset(phpcom::$G['gp_thumbnail'])){
				$image = new phpcom_image();
				$thumboriginal = isset(phpcom::$G['gp_thumboriginal']) ? phpcom::$G['gp_thumboriginal'] : array();
				if(isset($thumboriginal['ok']) && $thumboriginal['ok'] && $thumboriginal['width']){
					$image->Thumbnail($this->files['destination'], '', $thumboriginal['width'], $thumboriginal['height'], 1, 1);
				}
				$thumbnail = isset(phpcom::$G['gp_thumbnail']) ? phpcom::$G['gp_thumbnail'] : array();
				if(isset($thumbnail['ok']) && $thumbnail['ok'] && $thumbnail['width']){
					$thumb = $image->Thumbnail($this->files['destination'], '', $thumbnail['width'], $thumbnail['height'], 3, 0);
				}
			}
			list($width) = @getimagesize($this->files['destination']);
		}
		if (phpcom::$G['gp_type'] != 'image' && $this->files['image']) {
			$this->files['image'] = 1;
		}
		$data = array(
				'uid' => $this->uid,
				'dirname' => $module,
				'filename' => $this->files['attachment'],
				'filesize' => $filesize,
				'dateline' => TIMESTAMP,
				'image' => $this->files['image'],
				'thumb' => $thumb,
				'remote' => $remote,
				'width' => $width
		);
		$this->attachid = DB::insert('upload_temp', $data, true);
		if (phpcom::$G['gp_type'] == 'image' && !$this->files['image']) {
			$this->message(6);
		}
		$this->fileMessage['module'] = $module;
		$this->fileMessage['dirname'] = $module;
		$this->fileMessage['attachment'] = $this->files['attachment'];
		$this->message(0);
	}
	
	private function message($statusid) {
		$this->errsizelimit = !empty($this->errsizelimit) ? $this->errsizelimit : 0;
		if($this->simple){
			$this->fileMessage['attachid'] = $this->attachid;
			$this->fileMessage['status'] = $statusid;
			$this->fileMessage['maxsize'] = $this->errsizelimit;
			echo json_encode($this->fileMessage);
		}else{
			echo $statusid ? 'error' : 0;
		}
		exit;
	}
	
	public function updateUploadAttach($attachid, $uid, $chanid, $data, $module){
		if($attach = DB::fetch_first("SELECT attachid, chanid, attachment, thumb, preview, remote FROM " . DB::table("attachment_$module") . " WHERE attachid='$attachid'")){
			$attach['module'] = $module;
			$attachid = $attach['attachid'];
			Attachment::unlinks($attach);
			$this->fileMessage['module'] = $attach['module'];
			$this->fileMessage['dirname'] = $attach['module'];
			$this->fileMessage['data'] = $data;
			unset($data['uid'], $data['chanid']);
			DB::update("attachment_$module", $data, "attachid='$attachid'");
			Attachment::ftpUpload(array($attachid), $uid, $chanid, $module);
			return TRUE;
		}
		return FALSE;
	}
}

?>
