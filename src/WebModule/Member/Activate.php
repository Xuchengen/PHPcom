<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Activate.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Activate extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		define('NOTROBOT', TRUE);
		$userid = intval($this->request->query('uid'));
		$userkey = trim($this->request->query('key'));
		if ($userid > 0 && $userkey) {
			$query = DB::query("SELECT m.uid, m.username, m.credits, s.attestation FROM " . DB::table('members') . " m, " . DB::table('member_status') . " s
					WHERE m.uid='$userid' AND s.uid=m.uid AND m.groupid='7'");
			if ($member = DB::fetch_array($query)) {
				list($attestkey, $dateline, $operation) = explode(",", $member['attestation']);
				if ($operation == 2 && $attestkey == $userkey) {
					$groupidnew = DB::result_first("SELECT groupid FROM " . DB::table('usergroup') . " WHERE type='member' AND mincredits<='$member[credits]' AND maxcredits>'$member[credits]'");
					DB::update('members',array('groupid' => $groupidnew, 'emailstatus' => 1), "uid='$member[uid]'");
					DB::update('member_status',array('attestation' => ''), "uid='$member[uid]'");
					showmessage('activate_succeed', $this->memberurl, array('username' => $member['username'], 'uid' => $member['uid']));
				}else{
					showmessage('activate_failed', $this->memberurl);
				}
			}else{
				showmessage('activate_failed', $this->memberurl);
			}
		}else{
			showmessage('activate_invalid', $this->memberurl);
		}
		return 0;
	}
}
?>