<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Misc.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Misc extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$doarray = array('activate', 'emailcheck', 'sendpasswd', 'getpasswd');
		$do = strtolower(trim($this->request->query('do')));
		if (empty($do) || !in_array($do, $doarray)) {
			showmessage('enter_the_member_center', 'member.php');
		}
		$theurl = 'member.php?action=misc&do=' . $do;
		if($do == 'sendpasswd'){
			$this->sendPassword();
		}elseif($do == 'emailcheck'){
			$this->emailCheck();
		}elseif($do == 'activate'){
			$this->activate();
		}
		return 0;
	}
	
	protected function sendPassword()
	{
		define('NOTROBOT', TRUE);
		if($this->uid){
			showmessage('getpasswd_account_notmatch', phpcom::$G['siteurl']);
		}
		if (checksubmit(array('ok', 'username', 'btnsubmit'))) {
			loaducenter();
			$email = strtolower(trim($this->request->getPost('email')));
			$username = trim($this->request->getPost('username'));
			$minusername = intval(phpcom::$setting['register']['minname']);
			$maxusername = intval(phpcom::$setting['register']['maxname']);
			if (!isemail($email) || !Member::checkUsername($username, $minusername, $maxusername)) {
				showmessage('getpasswd_account_notmatch', 'back');
			}
			list($tmp['uid'],, $tmp['email']) = uc_get_user($username);
			if ($email != $tmp['email']) {
				showmessage('getpasswd_account_notmatch', 'back');
			}
			$member = DB::fetch_first("SELECT m.uid, m.username, m.adminid, m.email, s.attestation FROM " . DB::table('members') . " m
			LEFT JOIN " . DB::table('member_status') . " s USING(uid) WHERE m.uid='" . addslashes($tmp['uid']) . "'");
			if (!$member) {
				showmessage('getpasswd_account_notmatch', 'back');
			} elseif ($member['adminid'] == 1 || $member['adminid'] == 2) {
				showmessage('getpasswd_account_invalid', 'back');
			}
			if ($member['email'] != $tmp['email']) {
				DB::update('members', array('email' => $tmp['email']), "uid='{$member['uid']}'");
			}
			$attestnum = 0;
			$timestamp = phpcom::$G['timestamp'];
			$attestkey = random(8);
			if ($member['attestation']) {
				list($attestkey, $dateline, $operation, $attestnum) = explode(",", $member['attestation'] . ',0');
				if ($operation == 1 && $attestnum >= 5 && $dateline > TIMESTAMP - 86400) {
					showmessage('getpasswd_today_upperlimit', 'back');
				} elseif($operation != 1 || $dateline < TIMESTAMP - 86400) {
					$attestnum = 0;
					$attestkey = random(8);
				}
			}
			$attestnum = $attestnum + 1;
			$attestation = "$attestkey,$timestamp,1,$attestnum";
			DB::update('member_status', array('attestation' => $attestation), "uid='{$member['uid']}'");
			if (!function_exists('sendmail')) {
				include loadlibfile('mail');
			}
			$getpasswd_subject = lang('email', 'get_password_subject');
			$getpasswd_message = lang('email', 'get_password_message', array(
					'username' => $member['username'],
					'webname' => phpcom::$setting['webname'],
					'siteurl' => phpcom::$G['siteurl'],
					'url' => phpcom::$G['siteurl'],
					'uid' => $member['uid'],
					'key' => $attestkey,
					'clientip' => phpcom::$G['clientip'],
			)
			);
			sendmail("$member[username] <$tmp[email]>", $getpasswd_subject, $getpasswd_message);
			showmessage('getpasswd_send_succeed', phpcom::$G['siteurl']);
		}else{
			showmessage('getpasswd_account_notmatch', phpcom::$G['siteurl']);
		}
	}
	
	protected function emailCheck()
	{
		$uid = $time = 0;
		$email = '';
		$hash = trim($this->request->getPost('hash'));
		if ($hash) {
			if($hashArray = explode("\t", decryptstring($hash, md5(substr(md5(phpcom::$config['security']['key']), 0, 16))))){
				$uid = intval($hashArray[0]);
				$email = isset($hashArray[1]) ? addslashes(trim($hashArray[1])) : null;
				$time = isset($hashArray[2]) ? intval($hashArray[2]) : 0;
			}
		}
		if ($uid && isemail($email) && $time > TIMESTAMP - 86400) {
			if($memberarr = DB::fetch_first("SELECT * FROM " . DB::table('members') . " WHERE uid='$uid'")){
				if($memberarr['emailstatus'] == 0){
					$setdata = array('email' => addslashes($email), 'emailstatus' => '1');
					if (phpcom::$setting['register']['verify'] == 1 && $memberarr['groupid'] == 7) {
						$groupid = DB::result(DB::query("SELECT groupid FROM " . DB::table('usergroup') . " WHERE type='member' AND $memberarr[credits]>=maxcredits AND $memberarr[credits]<mincredits LIMIT 1"), 0);
						$setdata['groupid'] = $groupid;
					}
					update_creditbyaction('emailauth', $uid);
					DB::update('members', $setdata, array('uid' => $uid));
					showmessage('email_check_sucess', 'member.php?action=password', array('email' => $email));
					exit();
				}
			}
			showmessage('email_check_complete', 'member.php?action=password', array('email' => $email));
		} else {
			showmessage('email_check_error', 'member.php');
		}
	}
	
	protected function activate()
	{
		define('NOTROBOT', TRUE);
		$userid = intval($this->request->query('uid'));
		$userkey = trim($this->request->query('key'));
		if ($userid > 0 && $userkey) {
			$query = DB::query("SELECT m.uid, m.username, m.credits, s.attestation FROM " . DB::table('members') . " m, " . DB::table('member_status') . " s
					WHERE m.uid='$userid' AND s.uid=m.uid AND m.groupid='7'");
			if ($member = DB::fetch_array($query)) {
				if($member['attestation']){
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
					showmessage('activate_invalid', $this->memberurl);
				}
			}else{
				showmessage('activate_failed', $this->memberurl);
			}
		}else{
			showmessage('activate_invalid', $this->memberurl);
		}
	}
}
?>