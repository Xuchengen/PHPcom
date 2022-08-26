<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Swfupload.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Misc_Swfupload extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		if((phpcom::$G['uid'] &&  !stricmp(phpcom::$G['groupid'], array(0, 4, 5, 6, 7))) || 
				(empty(phpcom::$G['uid']) && $this->request->post('uid') && $this->request->post('hash'))){
			$upload = new FileUpload();
			$upload->swfUpload();
		}
		return 0;
	}
}
?>