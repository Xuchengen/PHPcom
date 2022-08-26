<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Uploading.php  2012-11-1
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Ajax_Uploading extends Controller_AjaxAbstract
{
	public function loadActionIndex()
	{
		if (empty(phpcom::$G['uid'])) {
			showmessage('action_undefined', NULL, NULL, array('showdialog' => TRUE));
		}
		if (empty(phpcom::$setting['uploadstatus']) || empty(phpcom::$G['group']['allowupload'])) {
			showmessage('upload_attachment_permission_denied', NULL, NULL, array('showdialog' => TRUE));
		}
		$chanid = intval($this->request->query('chanid', 0));
		$tid = intval($this->request->query('tid', 0));
		$type = trim($this->request->query('type', 'image'));
		$type = $type ? $type : 'image';
		$uptype = $type != 'file' && $type != 'attach' ? 'image' : 'file';
		$dirname = trim($this->request->query('dirname', 'image'));
		if(!preg_match("/^[\w]{2,32}$/", $dirname)){
			$dirname = 'image';
		}
		$uid = phpcom::$G['uid'];
		$hash = md5(substr(md5(phpcom::$config['security']['key']), 8) . $uid);
		Attachment::setExtensionAndSize($uptype, $chanid);
		if (empty(phpcom::$G['group']['allowupload'])) {
			showmessage('upload_attachment_permission_denied', NULL, NULL, array('showdialog' => TRUE));
		}
		if (empty(phpcom::$G['group']['attachext'])) {
			$attachextensions = '*.*';
		} else {
			$attachextensions = '*.' . implode(';*.', phpcom::$G['group']['attachext']);
		}
		$extendtype = '';
		$depiction = $uptype == 'image' ? 'Image Files' : 'All Files';
		$maxszie = phpcom::$G['group']['maxattachsize'];
		$filesizelimit = formatbytes($maxszie);
		$thumbimage = $imagezoom = 0;
		$thumbwidth = $thumbheight = 0;
		$imagewidth = $imageheight = 0;
		$thumbchecked = '';
		$imagechecked = '';
		$imageurl = $attachment = $attachimg = '';
		if($tid && $type == 'threadimage'){
			if($img = DB::fetch_first("SELECT * FROM " . DB::table('thread_image') . " WHERE tid='$tid' LIMIT 1")){
				$attachment = trim($img['attachment']);
				$attachimg = trim($img['attachimg']);
			}
		}
		if($tid && $type == 'topicimage'){
			if($img = DB::fetch_first("SELECT imageurl FROM " . DB::table('topical') . " WHERE topicid='$tid' LIMIT 1")){
				$imageurl = trim($img['imageurl']);
			}
		}
		if($tid && $type == 'catimage'){
			if($img = DB::fetch_first("SELECT imageurl FROM " . DB::table('category') . " WHERE catid='$tid' LIMIT 1")){
				$imageurl = trim($img['imageurl']);
			}
		}
		include template('ajax/uploading');
		$this->loadAjaxFooter();
		return 0;
	}
}
?>