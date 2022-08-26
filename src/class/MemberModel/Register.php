<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Register.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class MemberModel_Register extends MemberModel_Abstract
{
	protected $setting = array();
	
	public function __construct(Web_HttpRequest $request)
	{
		parent::__construct($request);
		$this->setting = &phpcom::$setting;
		
		if (!function_exists('sendmail')) {
			include loadlibfile('mail');
		}
		
		loaducenter();
	}
	
	public function register()
	{
		$usernameminlen = intval($this->setting['register']['minname']);
		$usernameminlen = $usernameminlen < 2 ? 3 : $usernameminlen;
		$usernamemaxlen = intval($this->setting['register']['maxname']);
		$usernamemaxlen = $usernamemaxlen < $usernameminlen ? 15 : $usernamemaxlen;
		$usernamemaxlen = $usernamemaxlen > 25 ? 15 : $usernamemaxlen;
		
		$username = trim($this->request->post($this->setting['formset']['username']));
		$password = trim($this->request->post($this->setting['formset']['password']));
		$password2 = trim($this->request->post($this->setting['formset']['password2']));
		$email = trim($this->request->post($this->setting['formset']['email']));
		$special = intval($this->request->post('special', 0));
		$usernamelen = strlength($username);
		if ($usernamelen < $usernameminlen) {
			showmessage('profile_username_minlimit', '', array('num' => $usernameminlen));
		} elseif ($usernamelen > $usernamemaxlen) {
			showmessage('profile_username_maxlimit', '', array('num' => $usernamemaxlen));
		}
		$chkstatus = uc_user_checkname($username, $usernameminlen, $usernamemaxlen);
		if ($chkstatus == UC_USER_CHECK_USERNAME_INVALID) {
			showmessage('profile_username_invalid', '', array('username' => $username));
		} elseif ($chkstatus == UC_USER_CHECK_USERNAME_BADWORD) {
			showmessage('profile_username_protect', '', array('username' => $username));
		} elseif ($chkstatus == UC_USER_CHECK_USERNAME_EXISTED) {
			if (uc_get_user($username)) {
				showmessage('profile_username_repeated', '', array('username' => $username));
			} else {
				showmessage('register_activation', '', array());
			}
		}
		
		$chkstatus = uc_user_checkemail($email);
		if ($chkstatus == UC_USER_CHECK_EMAIL_INVALID) {
			showmessage('profile_email_format_invalid');
		} elseif ($chkstatus == UC_USER_CHECK_EMAIL_DENIED) {
			showmessage('profile_email_domain_denied');
		} elseif ($chkstatus == UC_USER_CHECK_EMAIL_EXISTED) {
			showmessage('profile_email_repeated');
		}
		
		if (empty($password)) {
			showmessage('profile_password_invalid');
		}
		if (strcmp($password, $password2) !== 0) {
			showmessage('profile_password_notmatch');
		}
		
		$inviteresult = array();
		$invitecode = trim($this->request->post('invitecode'));
		$invited = ($this->setting['register']['status'] == 2);
		$invited = $invited ? $invited : ($this->setting['register']['status'] == 3 && !empty($invitecode));
		if ($invited) {
			if (!$invitecode) {
				showmessage('invitation_code_empty');
			}
			$inviteresult = Member::checkInviteCode($invitecode);
			if ($inviteresult == USER_CHECK_INVITECODE_INVALID) {
				showmessage('invitation_code_invalid');
			}
			if ($inviteresult == USER_CHECK_INVITECODE_EXPIRES) {
				showmessage('invitation_code_expires');
			}
		}
		
		if ($this->setting['questionstatus'][0]) {
			if (!check_questionset(trim($this->request->post('questionanswer')))) {
				showmessage('question_answer_invalid');
			}
		}
		if ($this->setting['captchastatus'][1]) {
			if (!check_captcha($this->request->post('verifycode'))) {
				showmessage('captcha_verify_invalid');
			}
		}
		if ($this->setting['register']['showterms']) {
			if (!$this->request->post('terms')) {
				showmessage('profile_register_terms');
			}
		}
		
		$clientip = phpcom::$G['clientip'];
		
		$reginterval = intval($this->setting['register']['interval']);
		if ($reginterval) {
			if (uc_user_checkintervalregip($clientip)) {
				showmessage('register_interval_regip', '', array('interval' => $reginterval));
			}
		}
		
		$reglimitnum = intval($this->setting['register']['limitnum']);
		if ($reglimitnum) {
			if ($regcounts = uc_user_checklimitcount($clientip)) {
				if ($regcounts >= $reglimitnum) {
					showmessage('register_limitnum_regip', '', array('num' => $reglimitnum));
				}
			}
		}
		if ($this->setting['register']['verify'] == 2 && !$this->request->post('regreason')) {
			showmessage('profile_regreason_invalid');
		}
		
		$groupid = $this->setting['register']['verify'] ? 7 : 11;
		
		$uid = uc_user_register($username, $password, $email, $groupid, '', '', '', 0);
		if ($uid <= 0) {
			switch ($uid) {
				case UC_USER_CHECK_USERNAME_INVALID:
					showmessage('profile_username_invalid', '', array('username' => $username));
					break;
				case UC_USER_CHECK_USERNAME_BADWORD:
					showmessage('profile_username_protect', '', array('username' => $username));
					break;
				case UC_USER_CHECK_USERNAME_EXISTED:
					showmessage('profile_username_repeated', '', array('username' => $username));
					break;
				case UC_USER_CHECK_EMAIL_INVALID:
					showmessage('profile_email_format_invalid');
					break;
				case UC_USER_CHECK_EMAIL_DENIED:
					showmessage('profile_email_domain_denied');
					break;
				case UC_USER_CHECK_EMAIL_EXISTED:
					showmessage('profile_email_repeated');
					break;
				case UC_USER_CHECK_PASSWORD_INVALID:
					showmessage('profile_password_invalid');
					break;
				default:
					showmessage('undefined_action');
					break;
			}
		}
		
		phpcom::$G['username'] = $username;
		$member = array();
		
		if (!UC_SERVER_APP && $uid > 0) {
			if ($olduid = DB::result_first("SELECT uid FROM " . DB::table('members') . " WHERE username='$username'")) {
				if ($olduid != $uid) {
					Member::delete($olduid);
				}
			}
			$member = DB::fetch_first("SELECT * FROM " . DB::table('members') . " WHERE uid='$uid'");
			if ($member) {
				if (addslashes($member['username']) != $username) {
					DB::query("UPDATE " . DB::table('members') . " SET username='$username' WHERE uid='$uid'");
					$member['username'] = stripslashes($username);
				}
				if (addslashes($member['email']) != $email) {
					DB::query("UPDATE " . DB::table('members') . " SET email='$email' WHERE uid='$uid'");
					$member['email'] = stripslashes($email);
				}
			} else {
				$uid = Member::add($username, $password, $email, $uid, $groupid);
				$member = DB::fetch_first("SELECT * FROM " . DB::table('members') . " WHERE uid='$uid'");
			}
		} else {
			$member = DB::fetch_first("SELECT * FROM " . DB::table('members') . " WHERE uid='$uid'");
		}
		if ($invited) {
			Member::updateInviteCode($username, $inviteresult);
		}
		
		$total = DB::result_first("SELECT COUNT(*) FROM " . DB::table('members'));
		phpcom_cache::save('userstats', array('total' => $total, 'username' => stripslashes($username)));
		$timestamp = phpcom::$G['timestamp'];
		$attestkey = '';
		if ($this->setting['register']['verify'] == 2) {
			$regreason = htmlcharsencode($this->request->post('regreason'));
			DB::query("REPLACE INTO " . DB::table('member_validate') . " (uid, status, submitdate, auditdate, auditor, submitnum, message, remarks)
			VALUES ('$uid', '0', '$timestamp', '0', '', '1', '$regreason', '')");
		} elseif ($this->setting['register']['verify'] == 1) {
			$attestkey = $idstring = random(8);
			$attestation = "$attestkey,$timestamp,2";
			DB::update('member_status', array('attestation' => $attestation), "uid='$uid'");
		}
		
		Member::setUserLogin($member);
		phpcom::setcookie('captcha', '');
		phpcom::setcookie('questionset', '');
		$welcomesend = phpcom::$setting['register']['welcomesend'];
		$welcometitle = phpcom::$setting['register']['welcometitle'];
		$welcometext = phpcom::$setting['register']['welcometext'];
		
		if ($welcomesend && !empty($welcometext)) {
			$welcometitle = addslashes(Member::replaceSitevar($welcometitle));
			$welcometext = addslashes(Member::replaceSitevar($welcometext));
			$welcometext = nl2br(str_replace(':', '&#58;', $welcometext));
			if ($welcomesend == 1) {
				sendmail($email, $welcometitle, $welcometext);
			} elseif ($welcomesend == 2) {
				addnotification($uid, 'system', $welcometext, array(), 1);
			} elseif ($welcomesend == 3) {
				addnotification($uid, 'system', $welcometext, array(), 1);
				sendmail($email, $welcometitle, $welcometext);
			}
		}
		
		switch ($this->setting['register']['verify']) {
			case 1:
				$uid = phpcom::$G['uid'];
				$verifyurl = phpcom::$G['siteurl'] . "member.php?action=misc&do=activate&amp;uid=$uid&amp;key=$attestkey";
				$email_verify_message = lang('email', 'email_verify_message', array(
						'username' => $username,
						'webname' => phpcom::$setting['webname'],
						'siteurl' => phpcom::$G['siteurl'],
						'url' => $verifyurl
				));
				sendmail("$username <$email>", lang('email', 'email_verify_subject'), $email_verify_message);
				$message = 'register_email_verify';
				$locationmessage = 'register_email_verify_location';
				$forwardurl = getreferer();
				break;
			case 2:
				$message = 'register_manual_verify';
				$locationmessage = 'register_manual_verify_location';
				$forwardurl = getreferer();
				break;
			default:
				$message = 'register_succeed';
				$locationmessage = 'register_succeed_location';
				$forwardurl = 'member.php';
				break;
		}
		$extras = array('message' => $locationmessage, 'type' => 'succeed');
		showmessage($message, $forwardurl, array('username' => $username, 'usergroup' => phpcom::$G['group']['grouptitle'],
		'webname' => phpcom::$setting['webname'], 'email' => $email), $extras);
		
	}
}
?>