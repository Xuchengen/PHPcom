<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Pollvotes.php  2012-8-6
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Ajax_Pollvotes extends Controller_AjaxAbstract
{
	public function loadActionIndex()
	{
		$tid = intval($this->request->getPost('tid'));
		$pollid = intval($this->request->getPost('pollid'));
		$action = trim($this->request->query('action'));
		$type = trim($this->request->query('type'));
		$pollanswers = striptags($this->request->post('pollanswers'));
		$isvote = false;
		$polltitle = $polltype = $expires = $pollurl = '';
		$checkbox = $voters = $choices = $expiration = 0;
		$polloptions = array();
		$chanid = $this->chanid;
		if ($pollid) {
			$pollurl = geturl('vote', array('pid' => $pollid, 'tid' => $tid, 'vid' => $pollid), $this->domain, 'main');
			$pollkey = "pollvotes_$pollid";
			if(!empty(phpcom::$G['cookie'][$pollkey])){
				$isvote = true;
			}
			$sql = "SELECT * FROM " . DB::table('pollvotes') . " WHERE pollid='$pollid'";
			if($poll = DB::fetch_first($sql)){
				$pollid = $poll['pollid'];
				$polltitle = $poll['polltitle'];
				$checkbox = $poll['checkbox'];
				$choices = $checkbox ? $poll['choices'] : 1;
				$voters = $poll['voters'];
				if ($poll['checkbox']) {
					$polltype = lang('common', 'checkbox');
				} else {
					$polltype = lang('common', 'radio');
				}
				if ($poll['expiration']) {
					$expiration = fmdate($poll['expiration']);
					$expires = lang('common', 'pollexpiration') . $expiration;
				} else {
					$expiration = 0;
					$expires = '';
				}
				if ($pollanswers) {
					if(($voteids = implodeids($pollanswers, "','", $choices)) && !$isvote){
						$isvote = true;
						$voters++;
						DB::query("UPDATE " . DB::table('polloption') . " SET votes=votes+1 WHERE voteid IN ($voteids)", 'UNBUFFERED');
						DB::query("UPDATE " . DB::table('pollvotes') . " SET voters=voters+1 WHERE pollid='$pollid'", 'UNBUFFERED');
						phpcom::setcookie($pollkey, encryptstring(TIMESTAMP), 86400);
					}else{
						showmessage('pollvotes_have_complete', NULL, NULL, array('showdialog' => TRUE));
					}
				}
				$polloptions = $this->polloptions($pollid, $checkbox);
			}
		}
		include template('ajax/pollvotes');
		$this->loadAjaxFooter();
		return 0;
	}
}
?>