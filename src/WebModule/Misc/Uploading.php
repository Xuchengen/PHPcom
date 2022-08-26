<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Uploading.php  2012-11-1
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Misc_Uploading extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$type = trim($this->request->query('type', 'image'));
		if(phpcom::$G['uid'] &&  !stricmp(phpcom::$G['groupid'], array(0, 4, 5, 6, 7))){
			$upload = new FileUpload();
			if($type == 'threadimage'){
				$upload->threadImageUpload();
			}elseif($type == 'topicimage'){
				$upload->topicImageUpload();
			}elseif($type == 'normal'){
				$upload->normalUpload();
			}else{
				$upload->simpleUpload();
			}
		}
		return 0;
	}
}
?>