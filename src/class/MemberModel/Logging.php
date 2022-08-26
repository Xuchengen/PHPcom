<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Logging.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class MemberModel_Logging extends MemberModel_Abstract
{
	public function __construct(Web_HttpRequest $request)
	{
		parent::__construct($request);
		loaducenter();
	}
	
	public function login() {
		phpcom::$G['uid'] = phpcom::$G['member']['uid'] = 0;
		phpcom::$G['username'] = phpcom::$G['member']['username'] = phpcom::$G['member']['password'] = '';
		$password = trim($this->request->post('password'));
		$username = trim($this->request->post('username'));
		$cookietime = intval($this->request->post('cookietime'));
		if (empty($password) || $password != addslashes($password)) {
			showmessage('profile_password_invalid', getreferer());
		}
		if(!$logins = Member::loginCheck($username)) {
			showmessage('login_strike', getreferer());
		}
		$result = Member::userLogin($username, $password, $this->request->post('questionid'), $this->request->post('answer'), $this->request->post('loginmode'));
		$uid = intval($result['ucenter']['uid']);
	
		if (!UC_SERVER_APP && $result['status'] == -1) {
			DB::insert('members', array(
				'uid' => $uid,
				'username' => $result['ucenter']['username'],
				'password' => $result['ucenter']['password'],
				'email' => $result['ucenter']['email'],
				'status' => 0,
				'emailstatus' => 1,
				'groupid' => 11,
				'credits' => $result['ucenter']['credits'],
				'regdate' => TIMESTAMP,
				'timeoffset' => phpcom::$setting['timeoffset'],
				'salt' => $result['ucenter']['salt'],
			));
			$credits = phpcom::$setting['credits'];
			DB::insert('member_count', array(
				'uid' => $uid,
				'money' => intval($credits['money']['initcredits']),
				'prestige' => intval($credits['prestige']['initcredits']),
				'currency' => intval($credits['currency']['initcredits']),
				'praise' => intval($credits['praise']['initcredits'])
			));
			DB::insert('member_info', array('uid' => $uid, 'gender' => 0, 'birthday' => '0000-00-00'));
			DB::insert('member_status', array(
			'uid' => $uid, 'regip' => $regip, 'lastip' => phpcom::$G['clientip'],
			'lastvisit' => TIMESTAMP, 'lastactivity' => TIMESTAMP
			));
			$result['member'] = DB::fetch_first("SELECT * FROM " . DB::table('members') . " WHERE uid='$uid'");
			$result['status'] = 1;
		}
		
		if ($result['status'] > 0) {
			Member::setUserLogin($result['member'], $cookietime ? 2592000 : 0);
			showmessage('login_succeed', getreferer(), array('username' => $result['member']['username'], 'usergroup' => phpcom::$G['group']['grouptitle']), array('type' => 'succeed'));
		} else {
			showmessage('login_failed', getreferer(), array('logins' => $logins));
		}
	}
	
	public function logout() {
		Member::removeCookies();
		phpcom::$G['groupid'] = phpcom::$G['member']['groupid'] = 6;
		phpcom::$G['uid'] = phpcom::$G['member']['uid'] = 0;
		phpcom::$G['username'] = phpcom::$G['member']['username'] = phpcom::$G['member']['password'] = '';
		$ucsynlogout = uc_user_synlogout();
		showmessage('logout_succeed', getreferer(), array('formtoken' => phpcom::$G['formtoken'], 'ucsynlogout' => $ucsynlogout), array('type' => 'succeed'));
	}
}
?>