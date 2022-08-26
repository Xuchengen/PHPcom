<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Index.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Index extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$this->title = $title = lang('member', 'member_index');
		$uid = $this->uid;
		
		$member = DB::fetch_first("SELECT m.*, mc.*, ms.*, mi.*, u.type, u.grouptitle, u.color FROM " . DB::table('members') . " m
		LEFT JOIN " . DB::table('usergroup') . " u ON u.groupid=m.groupid
		LEFT JOIN " . DB::table('member_count') . " mc ON mc.uid=m.uid
		LEFT JOIN " . DB::table('member_status') . " ms ON ms.uid=m.uid
		LEFT JOIN " . DB::table('member_info') . " mi ON mi.uid=m.uid
				WHERE m.uid='$uid'");
		$regdate = fmdate($member['regdate'], 'dt', 'u');
		$lastvisit = fmdate($member['lastvisit']);
		$lastactivity = fmdate($member['lastactivity']);
		if ($member['lastpost']) {
			$lastpost = fmdate($member['lastpost'], 'dt', 'u');
		} else {
			$lastpost = '0000-00-00 00:00';
		}
		$prompts = $member['prompts'];
		$pmnew = $member['pmnew'];
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
		$useragent = $_SERVER['HTTP_USER_AGENT'];
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
		$favorites = DB::result_first("SELECT COUNT(*) FROM " . DB::table('favorites') . " WHERE uid='$uid'");
		
		$tplname = 'member/index';
		include template($tplname);
		return 1;
	}
}
?>