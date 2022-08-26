<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Home.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Home extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$this->title = $title = lang('member', 'member_home');
		$userid = intval($this->request->query('uid'));
		if (!phpcom::$G['group']['viewmember'] && $userid != phpcom::$G['uid']) {
			showmessage('member_home_permission_denied', NULL);
		}
		$condition = "m.uid='$userid'";
		if(empty($userid)){
			$username = stripstring($this->request->query('username'));
			$condition = "m.username='$username'";
		}
		$member = DB::fetch_first("SELECT m.*, mc.*, ms.*, mi.*, u.type, u.grouptitle, u.color FROM " . DB::table('members') . " m
		LEFT JOIN " . DB::table('usergroup') . " u ON u.groupid=m.groupid
		LEFT JOIN " . DB::table('member_count') . " mc ON mc.uid=m.uid
		LEFT JOIN " . DB::table('member_status') . " ms ON ms.uid=m.uid
		LEFT JOIN " . DB::table('member_info') . " mi ON mi.uid=m.uid
				WHERE $condition");
		if (!$member) {
			showmessage('member_home_worong', NULL);
		}
		if($member['groupid'] == 1 && phpcom::$G['group']['groupid'] > 3){
			showmessage('member_home_permission_denied', NULL);
		}
		
		$regdate = fmdate($member['regdate'], 'dt', 'u');
		$lastvisit = fmdate($member['lastvisit']);
		$lastactivity = fmdate($member['lastactivity']);
		if ($member['lastpost']) {
			$lastpost = fmdate($member['lastpost'], 'dt', 'u');
		} else {
			$lastpost = '0000-00-00 00:00';
		}
		$prompts = $member['prompts'];
		$usersign = $member['usersign'] ? $member['usersign'] : lang('member', 'member_nosign');
		$genders = lang('member', 'member_genders');
		$gender = isset($genders[$member['gender']]) ? $genders[$member['gender']] : $genders[0];
		$moneyunit = phpcom::$setting['credits']['money']['unit'];
		$prestigeunit = phpcom::$setting['credits']['prestige']['unit'];
		$currencyunit = phpcom::$setting['credits']['currency']['unit'];
		$praiseunit = phpcom::$setting['credits']['praise']['unit'];
		$moneytitle = phpcom::$setting['credits']['money']['title'];
		$prestigetitle = phpcom::$setting['credits']['prestige']['title'];
		$currencytitle = phpcom::$setting['credits']['currency']['title'];
		$praisetitle = phpcom::$setting['credits']['praise']['title'];
		$allowadmin = phpcom::$G['member']['allowadmin'];
		$useragent = $_SERVER["HTTP_USER_AGENT"];
		$useragent = preg_replace("/\.NET(.+?);/s", '', $useragent);
		if (strtotime($member['regdate']) + $member['onlinetime'] * 3600 > TIMESTAMP) {
			$member['onlinetime'] = 0;
		}
		$timeoffset_array = lang('member', 'member_timeoffset_option');
		if ($member['timeoffset'] >= -12 && $member['timeoffset'] <= 12) {
			$timeoffset = $timeoffset_array[$member['timeoffset']];
		} else {
			$timeoffset = $timeoffset_array[phpcom::$setting['timeoffset']];
		}
		$regip = $member['regip'];
		$lastip = $member['lastip'];
		if (!phpcom::$G['group']['viewuserip'] && $member['uid'] != phpcom::$G['uid']) {
			$regip = substr($regip, 0, 2) . '***.***.***';
			$lastip = substr($lastip, 0, 2) . '***.***.***';
		}
		$uid = phpcom::$G['uid'];
		if (!phpcom::$G['uid']) {
			$this->uid = $uid = $member['uid'];
			$this->username = $username = $member['username'];
			$this->credits = $credits = $member['credits'];
			$this->usergroup = $usergroup = $member['grouptitle'];
		}
		$favorites = DB::result_first("SELECT COUNT(*) FROM " . DB::table('favorites') . " WHERE uid='$userid'");
		
		$tplname = 'member/home';
		include template($tplname);
		return 1;
	}
}
?>