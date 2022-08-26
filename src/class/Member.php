<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : member.php    2011-12-23
 */
!defined('IN_PHPCOM') && exit('Access denied');
define('USER_CHECK_INVITECODE_INVALID', 0);
define('USER_CHECK_INVITECODE_EXPIRES', -1);

class Member
{
	public static function checkAllowRegip($ip = '') {
		if($allowsregip = trim(phpcom::$setting['allowsregip'])){
			$ip = $ip ? $ip : phpcom::$G['clientip'];
			$allowsregip = str_replace("\r", '', $allowsregip);
			$allowsexp = '/^(' . str_replace("\n", '|', preg_quote($allowsregip, '/')) . ')$/i';
			$allowsexp = str_replace('\*', '\w+', $allowsexp);
			return preg_match($allowsexp, $ip);
		}
		return -1;
	}
	
	public static function checkUsername($username, $minlen = 3, $maxlen = 20) {
		$regexp = '\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';
		$len = strlength($username);
		if ($len > $maxlen || $len < $minlen || preg_match("/\s+|^c:\\con\\con|[%,\*\"\s\<\>\&]|$regexp/is", $username)) {
			return false;
		} else {
			return true;
		}
	}
	
	public static function checkInviteCode($invitecode) {
		$validtime = intval(phpcom::$setting['invite']['validtime']) * 86400;
		$timediff = intval(TIMESTAMP - $validtime);
		$result = DB::fetch_first("SELECT id,uid,inviter,groupid,dateline,type FROM " . DB::table('invitecode') . " WHERE  status='0' AND code='$invitecode' LIMIT 1"); // AND dateline>='$timediff'
		if ($result) {
			if ($result['dateline'] >= $timediff) {
				return $result;
			} else {
				return USER_CHECK_INVITECODE_EXPIRES;
			}
		}
		return USER_CHECK_INVITECODE_INVALID;
	}
	
	public static function updateInviteCode($username, $invite = array()) {
		if ($username && $invite && is_array($invite)) {
			$reward = intval(phpcom::$setting['invite']['invitercredit']);
			$groupid = intval($invite['groupid']);
			if ($groupid < 8) {
				$groupid = intval(phpcom::$setting['invite']['groupid']);
				if ($groupid < 8 || $groupid > 11) {
					$groupid = 0;
				}
			}
			$allowfield = array('money', 'prestige', 'currency', 'praise');
			$uid = intval($invite['uid']);
			$field = phpcom::$setting['invite']['creditfield'];
			$field = in_array($field, $allowfield) ? $field : 'money';
			if ($invite['type'] == 1 && $reward && $uid) {
				DB::query("UPDATE " . DB::table('member_count') . " SET $field=$field+'$reward' WHERE uid='$uid'", 'UNBUFFERED');
			}
			$reward = intval(phpcom::$setting['invite']['inviteecredit']);
			if ($reward) {
				if($uid = DB::free_result("SELECT uid FROM " . DB::table('members') . " WHERE username='$username'")){
					DB::query("UPDATE " . DB::table('member_count') . " SET $field=$field+'$reward' WHERE username='$username'", 'UNBUFFERED');
				}
			}
			if ($groupid > 7) {
				DB::query("UPDATE " . DB::table('members') . " SET groupid=$groupid WHERE username='$username'", 'UNBUFFERED');
			}
			$time = time();
			$id = intval($invite['id']);
			DB::query("UPDATE " . DB::table('invitecode') . " SET invitee='$username', usedate='$time', status='1' WHERE id='$id'", 'UNBUFFERED');
			return $id;
		} else {
			return 0;
		}
	}
	
	public static function removeCookies() {
		foreach (phpcom::$G['cookie'] as $k => $v) {
			phpcom::setcookie($k);
		}
		phpcom::setcookie('userauth');
		phpcom::$G['uid'] = phpcom::$G['adminid'] = 0;
		phpcom::$G['username'] = phpcom::$G['member']['password'] = '';
	}
	
	public static function setUserLogin($member, $cookietime = 0) {
		phpcom::$G['uid'] = $member['uid'];
		phpcom::$G['username'] = addslashes($member['username']);
		phpcom::$G['groupid'] = $member['groupid'];
		phpcom::$G['member'] = $member;
		phpcom_cache::load('usergroup_' . phpcom::$G['groupid']);
		phpcom::setcookie('userauth', encryptstring("{$member['password']}\t{$member['uid']}"), $cookietime);
	}
	
	public static function replaceSitevar($string, $replaces = array()) {
		$sitevars = array(
				'{sitename}' => phpcom::$setting['webname'],
				'{siteurl}' => phpcom::$G['siteurl'],
				'{time}' => fmdate(TIMESTAMP, 'Y-n-j H:i'),
				'{adminemail}' => phpcom::$setting['adminmail'],
				'{adminmail}' => phpcom::$setting['adminmail'],
				'{username}' => phpcom::$G['member']['username']
		);
		$replaces = array_merge($sitevars, $replaces);
		return str_replace(array_keys($replaces), array_values($replaces), $string);
	}
	
	public static function userLogin($username, $password, $questionid = '', $answer = '', $loginmode = 'username') {
		$result = array();
		if ($loginmode == 'uid' || $loginmode == '1') {
			$isuid = 1;
		} elseif ($loginmode == 'email' || $loginmode == '2') {
			$isuid = 2;
		} elseif ($loginmode == 'auto' || $loginmode == '3') {
			$isuid = 3;
		} else {
			$isuid = 0;
		}
		if (!function_exists('uc_user_login')) {
			loaducenter();
		}
		if ($isuid == 3) {
			if (preg_match('/^[1-9]\d*$/', $username)) {
				$result['ucenter'] = uc_user_login($username, $password, 1, $questionid, $answer, 1);
			} elseif (isemail($username)) {
				$result['ucenter'] = uc_user_login($username, $password, 2, $questionid, $answer, 1);
			}
			if ($result['ucenter']['status'] == UC_USER_LOGIN_USERNAME_INVALID || $result['ucenter']['status'] == UC_USER_LOGIN_PASSWORD_INVALID) {
				$result['ucenter'] = uc_user_login($username, $password, 0, $questionid, $answer, 1);
			}
		} else {
			$result['ucenter'] = uc_user_login($username, $password, $isuid, $questionid, $answer, 1);
		}
		$result['ucenter'] = addslashes_array($result['ucenter']);
		if ($result['ucenter']['uid'] > 0) {
			if (!UC_SERVER_APP) {
				if ($olduid = DB::result_first("SELECT uid FROM " . DB::table('members') . " WHERE username='" . $result['ucenter']['username'] . "'")) {
					if ($olduid != $result['ucenter']['uid']) {
						self::delete($olduid);
					}
				}
			}
		} else {
			$result['status'] = 0;
			$password = preg_replace("/^(.{".round(strlen($password) / 4)."})(.+?)(.{".round(strlen($password) / 6)."})$/s", "\\1***\\3", $password);
			writelog('illegallog', htmlcharsencode(
					TIMESTAMP."\t".stripslashes($username)."\t".
					$password."\t".
					"Ques #".intval($questionid)."\t".
					phpcom::$G['clientip']));
			self::loginFailed($username);
			return $result;
		}
		$member = DB::fetch_first("SELECT * FROM " . DB::table('members') . " WHERE uid='" . $result['ucenter']['uid'] . "'");
		if (!$member) {
			$result['status'] = -1;
			return $result;
		}
		$result['member'] = $member;
		$result['status'] = 1;
		if (!UC_SERVER_APP) {
			if (addslashes($member['username']) != $result['ucenter']['username']) {
				DB::query("UPDATE " . DB::table('members') . " SET username='" . $result['ucenter']['username'] . "' WHERE uid='" . $result['ucenter']['uid'] . "'");
				$result['member']['username'] = $result['ucenter']['username'];
			}
			if (addslashes($member['email']) != $result['ucenter']['email']) {
				DB::query("UPDATE " . DB::table('members') . " SET email='" . $result['ucenter']['email'] . "' WHERE uid='" . $result['ucenter']['uid'] . "'");
				$result['member']['email'] = $result['ucenter']['email'];
			}
			if (addslashes($member['password']) != $result['ucenter']['password']) {
				DB::query("UPDATE " . DB::table('members') . " SET password='" . $result['ucenter']['password'] . "',salt='" . $result['ucenter']['salt'] . "' WHERE uid='" . $result['ucenter']['uid'] . "'");
				$result['member']['password'] = $result['ucenter']['password'];
			}
		}
		return $result;
	}
	
	public static function loginCheck($username) {
		$clientip = phpcom::$G['clientip'];
		$timestamp = phpcom::$G['timestamp'];
		$return = 0;
		$username = trim(stripslashes($username));
		$username = addslashes(strcut($username, 50, null));
		$login = DB::fetch_first("SELECT logincount, lastupdate FROM " . DB::table('failedlogin') . " WHERE ip='$clientip' AND username='$username'");
		$return = (!$login || (TIMESTAMP - $login['lastupdate'] > 1800)) ? 5 : max(0, 5 - $login['logincount']);
	
		if (!$login) {
			DB::query("REPLACE INTO " . DB::table('failedlogin') . " (ip, username, logincount, lastupdate) VALUES ('$clientip', '$username', '0', '$timestamp')");
		} elseif (TIMESTAMP - $login['lastupdate'] > 1800) {
			DB::query("REPLACE INTO " . DB::table('failedlogin') . " (ip, username, logincount, lastupdate) VALUES ('$clientip', '$username', '0', '$timestamp')");
			DB::query("DELETE FROM " . DB::table('failedlogin') . " WHERE lastupdate<$timestamp-1801", 'UNBUFFERED');
		}
		return $return;
	}
	
	public static function loginFailed($username) {
		$clientip = phpcom::$G['clientip'];
		$timestamp = phpcom::$G['timestamp'];
		$username = trim(stripslashes($username));
		$username = addslashes(strcut($username, 50, null));
		DB::query("UPDATE " . DB::table('failedlogin') . " SET logincount=logincount+1, lastupdate='$timestamp' WHERE ip='$clientip' AND username='$username'");
	}
	
	public static function add($username, $password, $email, $uid = 0, $groupid = 11, $questionid = '', $answer = '', $regip = '', $salt = '') {
		$salt = $salt ? $salt : substr(uniqid(rand()), -6);
		$regip = empty($regip) ? phpcom::$G['clientip'] : $regip;
		$groupid = $groupid > 0 ? $groupid : 11;
		$members = array();
		if ($uid) {
			$members['uid'] = $uid;
		}
		$members['username'] = $username;
		$members['password'] = md5salt($password, $salt);
		$members['email'] = $email;
		$members['adminid'] = $groupid > 3 ? 0 : $groupid;
		$members['groupid'] = $groupid;
		$members['gender'] = 0;
		$members['face'] = '';
		$members['timeoffset'] = phpcom::$setting['timeoffset'];
		$members['salt'] = $salt;
		$members['regdate'] = phpcom::$G['timestamp'];
		$members['qacode'] = questioncrypt($questionid, $answer);
		$uid = DB::insert('members', $members, TRUE);
		if ($uid) {
			$credits = phpcom::$setting['credits'];
			$memberdata = array();
			$memberdata['uid'] = $uid;
			$memberdata['money'] = intval($credits['money']['initcredits']);
			$memberdata['prestige'] = intval($credits['prestige']['initcredits']);
			$memberdata['currency'] = intval($credits['currency']['initcredits']);
			$memberdata['praise'] = intval($credits['praise']['initcredits']);
			DB::insert('member_count', $memberdata);
			DB::insert('member_info', array('uid' => $uid, 'birthday' => '0000-00-00'));
			DB::insert('member_status', array('uid' => $uid, 'regip' => $regip, 'lastip' => $regip,
			'lastvisit' => $this->base->time, 'lastactivity' => $this->base->time));
		}
		return $uid;
	}
	
	public static function edit($uid, $members = array(), $membercounts = array(), $memberinfos = array(), $memberstatus = array(), $password = '', $chkpasswd = FALSE) {
		if (!$uid || (empty($members) && empty($membercounts) && empty($memberinfos) && empty($memberstatus))) {
			return 0;
		}
		$data = DB::fetch_first("SELECT uid, username, password, email, salt FROM " . DB::table('members') . " WHERE uid='$uid'");
		if ($data) {
			if ($chkpasswd && $data['password'] != md5salt($password, $data['salt'])) {
				return -1;
			}
			if (!empty($members)) {
				if (isset($members['uid'])) {
					unset($members['uid']);
				}
				if (isset($members['username'])) {
					unset($members['username']);
				}
				if (isset($members['email'])) {
					if (strtolower($data['email']) != strtolower($members['email'])) {
						if (!isset($members['emailstatus'])) {
							$members['emailstatus'] = 0;
						}
					} else {
						unset($members['email']);
					}
				}
				if (isset($members['salt'])) {
					unset($members['salt']);
				}
				if (isset($members['groupid'])) {
					$members['groupid'] = intval($members['groupid']);
					$members['adminid'] = $members['groupid'] > 3 ? 0 : $members['groupid'];
					if ($members['groupid'] === 1) {
						$members['allowadmin'] = 1;
					}
				}
				if (isset($members['password'])) {
					if (empty($members['password'])) {
						unset($members['password']);
					} else {
						$members['password'] = md5salt($members['password'], $data['salt']);
					}
				}
				DB::update('members', $members, "uid='$uid'");
			}
			if (!empty($membercounts)) {
				if (isset($membercounts['uid'])) {
					unset($membercounts['uid']);
				}
				DB::update('member_count', $membercounts, "uid='$uid'");
			}
			if (!empty($memberinfos)) {
				if (isset($memberinfos['uid'])) {
					unset($memberinfos['uid']);
				}
				DB::update('member_info', $memberinfos, "uid='$uid'");
			}
			if (!empty($memberstatus)) {
				if (isset($memberstatus['uid'])) {
					unset($memberstatus['uid']);
				}
				DB::update('member_status', $memberstatus, "uid='$uid'");
			}
			if (!function_exists('uc_user_editmember')) {
				loaducenter();
			}
			if (!UC_SERVER_APP) {
				if (isset($members['groupid'])) {
					if ($members['groupid'] !== 1) {
						unset($members['groupid'], $members['adminid']);
					}
				}
				uc_user_editmember($uid, $members, array(), $memberinfos, $memberstatus);
			}
			return 1;
		}
		return 0;
	}
	
	public static function delete($uid) {
		if ($uid) {
			foreach (array('members', 'member_count', 'member_info', 'member_status', 'member_validate',
					'onlinetime', 'favorites', 'friends', 'friendrequest', 'notification', 'credit_log') as $table) {
					DB::delete($table, "uid='$uid'");
			}
			if (DB::result_first("SELECT mid FROM " . DB::table('messages') . " WHERE uid='$uid'")) {
				DB::query("DELETE t1, t2 FROM " . DB::table('messages') . " as t1
                LEFT JOIN " . DB::table('message_body') . " as t2 ON t1.mid=t2.mid
	                WHERE t1.uid='$uid'");
			}
			DB::query("DELETE FROM " . DB::table('friends') . " WHERE fuid='$uid'");
			DB::query("DELETE FROM " . DB::table('friendrequest') . " WHERE fuid='$uid'");
		}
	}
}

?>
