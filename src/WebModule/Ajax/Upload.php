<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Upload.php  2012-8-5
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Ajax_Upload extends Controller_AjaxAbstract
{
	public function loadActionIndex()
	{
		if (empty(phpcom::$G['uid'])) {
			showmessage('action_undefined', NULL, NULL, array('showdialog' => TRUE));
		}
		if (empty(phpcom::$setting['uploadstatus']) || empty(phpcom::$G['group']['allowupload'])) {
			showmessage('upload_attachment_permission_denied', NULL, NULL, array('showdialog' => TRUE));
		}
		
		$tid = intval($this->request->query('tid', 0));
		$aid = intval($this->request->query('aid', 0));
		$chanid = intval($this->request->query('chanid', 0));
		$type = trim($this->request->query('type', 'image'));
		$type = $type ? $type : 'image';
		$uid = phpcom::$G['uid'];
		$hash = md5(substr(md5(phpcom::$config['security']['key']), 8) . $uid);
		if (empty(phpcom::$G['channel'])) {
			phpcom_cache::load('channel');
		}
		$channel = array();
		if(isset(phpcom::$G['channel'][$chanid])){
			$channel = &phpcom::$G['channel'][$chanid];
		}
		Attachment::setExtensionAndSize($type, $chanid);
		if (empty(phpcom::$G['group']['allowupload'])) {
			showmessage('upload_attachment_permission_denied', NULL, NULL, array('showdialog' => TRUE));
		}
		if (empty(phpcom::$G['group']['attachext'])) {
			$attachextensions = '*.*';
		} else {
			$attachextensions = '*.' . implode(';*.', phpcom::$G['group']['attachext']);
		}
		$extendtype = '';
		$depiction = $type == 'image' ? 'Image Files' : 'All Files';
		$maxszie = phpcom::$G['group']['maxattachsize'];
		$filesizelimit = formatbytes($maxszie);
		include template('ajax/upload');
		$this->loadAjaxFooter();
		return 0;
	}
}
?>