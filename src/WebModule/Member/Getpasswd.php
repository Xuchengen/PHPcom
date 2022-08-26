<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Getpasswd.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Getpasswd extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$userid = intval($this->request->getPost('uid'));
		$hashkey = trim($this->request->getPost('key'));
		if ($userid > 0 && $hashkey) {
			$query = DB::query("SELECT m.uid, m.username, m.credits, s.attestation, g.grouptitle
        		FROM " . DB::table('members') . " m, " . DB::table('member_status') . " s, " . DB::table('usergroup') . " g
				WHERE m.uid='$userid' AND s.uid=m.uid AND m.groupid=g.groupid");
			if ($member = DB::fetch_array($query)) {
				$uid = $member['uid'];
				$username = $member['username'];
				$credits = $member['credits'];
				$usergroup = $member['grouptitle'];
				list($attestkey, $dateline, $operation) = explode(",", $member['attestation']);
				if ($dateline < TIMESTAMP - 86400 * 3 || $operation != 1 || $attestkey != $hashkey) {
					showmessage('getpasswd_failed', $this->memberurl);
				}
				$password1 = trim($this->request->query('password1'));
				$password2 = trim($this->request->query('password2'));
				if (!checksubmit(array('formsubmit', 'ok', 'btnsubmit')) || $password1 != $password2) {
					$this->title = $title = lang('member', 'member_getpasswd');
					$tplname = 'member/getpasswd';
					include template($tplname);
					return 1;
				} else {
					if ($password1 != addslashes($password1)) {
						showmessage('profile_password_invalid', $this->memberurl, NULL, array('type' => 'alert', 'showdialog' => TRUE));
					}
					loaducenter();
					$userdata = uc_user_edit($member['username'], $password1, $password1, $member['email'], 1, 0);
					DB::update('member_status', array('attestation' => ''), "uid='$uid'");
					unset($userdata['status']);
					if ($userdata) {
						DB::update('members', $userdata, "uid='$uid'");
					}
					showmessage('getpasswd_succeed', $this->memberurl, NULL, array('type' => 'succeed', 'showdialog' => TRUE));
				}
			} else {
				showmessage('profile_password_invalid', $this->memberurl);
			}
		} else {
			showmessage('profile_password_invalid', $this->memberurl);
		}
		return 0;
	}
}
?>