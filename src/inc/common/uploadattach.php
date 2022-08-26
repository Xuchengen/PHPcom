<?php
/**
 * Copyright (c) 2010-2012 phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : uploadattach.php  2012-3-14
 */
 !defined('IN_PHPCOM') && exit('Access denied');
$chanid = intval(phpcom::$G['channelid']);
$type = 'image';
$uid = phpcom::$G['uid'];
$channel = phpcom::$G['channel'][$chanid];
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
$datalist = $attachs;
include template('common/uploadattach');
?>