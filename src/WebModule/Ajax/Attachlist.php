<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Attachlist.php  2012-8-5
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Ajax_Attachlist extends Controller_AjaxAbstract
{
	public function loadActionIndex()
	{
		$this->chanid = $chanid = intval($this->request->query('chanid', 0));
		$type = $this->request->query('type') == 'image' ? 'image' : 'attach';
		$posttime =intval($this->request->query('posttime', 0));
		$uid = intval($this->request->query('uid', 0));
		$uid = $uid ? $uid : phpcom::$G['uid'];
		$module = 'temp';
		$channel = array('modules' => $module);
		if(isset(phpcom::$G['channel'][$chanid])){
			$channel = &phpcom::$G['channel'][$chanid];
			$module = $channel['modules'];
		}
		$aids = $this->request->query('aids');
		$aids = $aids ? explode('|', $aids) : '';
		Attachment::setExtensionAndSize($type, $chanid);
		if (empty(phpcom::$G['group']['attachext'])) {
			$attachextensions = '*.*';
		} else {
			$attachextensions = '*.' . implode(';*.', phpcom::$G['group']['attachext']);
		}
		$hash = md5(substr(md5(phpcom::$config['security']['key']), 8) . $uid);
		$extendtype = '';
		$depiction = $type == 'image' ? 'Image Files' : 'All Files';
		$maxszie = phpcom::$G['group']['maxattachsize'];
		$filesizelimit = formatbytes($maxszie);
		$siteurl = phpcom::$G['siteurl'];
		$instdir = $siteurl;
		$datalist = Attachment::getAttachtemp($posttime, $uid, $chanid, $type);
		include template('ajax/attachlist');
		$this->loadAjaxFooter();
		return 0;
	}
}
?>