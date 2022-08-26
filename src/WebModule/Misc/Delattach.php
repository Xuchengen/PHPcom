<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Delattach.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Misc_Delattach extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$attachids = trim($this->request->query('aid'));
		$attachkey = trim($this->request->query('key'));
		$count = 0;
		if($attachids && phpcom::$G['uid'] && $attachkey){
			if(!is_array($attachids)){
				$attachids = array(intval($attachids) => $attachkey);
			}
			
			foreach($attachids as $attachid => $akey){
				$this->deleteAttach($attachid, $akey, $count);
			}
			
			$this->loadAjaxHeader();
			echo $count;
			$this->loadAjaxFooter();
		}else{
			showmessage('delete_attachment_permission_denied', NULL, NULL, array('showdialog' => true));
		}
		return 0;
	}
	
	protected function deleteAttach($attachid, $attachkey, &$count)
	{
		$attachid = intval($attachid);
		if($attachid && $attachkey){
			$attachid = intval($attachid);
			$table = Attachment::getAttachTableByaid($attachid);
			$attach = DB::fetch_first("SELECT attachid, uid, chanid, attachment, thumb, preview, remote FROM ".DB::table($table)." WHERE attachid='$attachid'");
			if($attach && (phpcom::$G['member']['groupid'] == 1 || $attach['uid'] == phpcom::$G['uid'])){
				$key = md5($attach['attachid'] . substr(md5(phpcom::$config['security']['key']), 8) . $attach['uid']);
				if($attachkey == $key){
					DB::delete('attachment', "attachid='{$attach['attachid']}'");
					DB::delete($table, "attachid='{$attach['attachid']}'");
					$attach['module'] = phpcom::$G['channel'][$attach['chanid']]['modules'];
					Attachment::unlinks($attach);
					$count++;
				}
			}
		}
	}
}
?>