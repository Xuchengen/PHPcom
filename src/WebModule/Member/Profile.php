<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Profile.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Profile extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$this->title = lang('member', 'member_profile');
		$uid = $this->uid;
		$profiles = DB::fetch_first("SELECT * FROM " . DB::table('members') . " m 
				LEFT JOIN " . DB::table('member_info') . " mi USING(uid) WHERE m.uid='$uid'");
		if (checksubmit(array('formsubmit', 'onsubmit', 'btnsubmit'))) {
			return $this->submitProfile($profiles);
		}else{
			return $this->displayProfile($profiles);
		}
		
	}
	
	protected function displayProfile(&$profiles)
	{
		$title = $this->title;
		$genderarray = lang('member', 'member_genders');
		$genderoption = '';
		foreach ($genderarray as $key => $value) {
			$genderoption .= "<option value=\"$key\"" . ($key == $profiles['gender'] ? ' SELECTED' : '') . "> $value </option>";
		}
		$birthdaytime = strtotime($profiles['birthday']);
		$yearword = lang('common', 'year');
		$yearoption = "<option value=\"0000\">$yearword</option>";
		$toyear = date('Y');
		$years = date('Y', $birthdaytime);
		for ($index = $toyear; $index > $toyear - 100; $index--) {
			$yearoption .= "<option value=\"$index\"" . ($index == $years ? ' SELECTED' : '') . ">$index</option>";
		}
		$monthword = lang('common', 'month');
		$monthoption = "<option value=\"00\">$monthword</option>";
		$months = date('n', $birthdaytime);
		for ($index = 1; $index < 13; $index++) {
			$monthoption .= "<option value=\"$index\"" . ($index == $months ? ' SELECTED' : '') . ">$index</option>";
		}
		$dayword = lang('common', 'day');
		$dayoption = "<option value=\"00\">$dayword</option>";
		$days = date('j', $birthdaytime);
		for ($index = 1; $index < 32; $index++) {
			$dayoption .= "<option value=\"$index\"" . ($index == $days ? ' SELECTED' : '') . ">$index</option>";
		}
		$timeoffset = phpcom::$G['member']['timeoffset'];
		$timenow = fmdate(TIMESTAMP, 'dt');
		$timeoffsetarray = lang('member', 'member_timeoffset_option');
		$timeoffsetoption = '';
		foreach ($timeoffsetarray as $key => $value) {
			$timeoffsetoption .= "<option value=\"$key\"" . ($key == $timeoffset ? ' SELECTED' : '') . ">$value</option>";
		}
		include template('member/profile');
		return 1;
	}
	
	protected function submitProfile(&$profiles)
	{
		$fieldarray = array('realname', 'idcard', 'homepage', 'company',
				'address', 'zipcode', 'usersign', 'phone', 'mobile', 'fax', 'qq', 'msn', 'taobao');
		$memberinfonew = striptags($this->request->post('memberinfonew'));
		if (isset($memberinfonew['uid'])) {
			unset($memberinfonew['uid']);
		}
		if (phpcom::$G['group']['usersign']) {
			if (strlen($memberinfonew['usersign']) >= 220) {
				$memberinfonew['usersign'] = substr($memberinfonew['usersign'], 0, 220);
			}
		} else {
			if (isset($memberinfonew['usersign'])) {
				unset($memberinfonew['usersign']);
			}
		}
		if ($memberinfonew['homepage'] && strpos($memberinfonew['homepage'], '://') !== FALSE) {
			$memberinfonew['homepage'] = substr($memberinfonew['homepage'], strpos($memberinfonew['homepage'], '://') + 3);
		}
		
		foreach ($memberinfonew as $key => $value) {
			if (!in_array($key, $fieldarray)) {
				unset($memberinfonew[$key]);
			}
		}
		$toyear = date('Y');
		$yearnew = intval($this->request->post('yearnew'));
		$monthnew = intval($this->request->post('monthnew'));
		$daynew = intval($this->request->post('daynew'));
		$timeoffsetnew = trim($this->request->post('timeoffsetnew'));
		$gendernew = intval($this->request->post('gendernew'));
		if ($timeoffsetnew > 12 || $timeoffsetnew < -12) {
			$timeoffsetnew = 9999;
		}
		$yearnew = ($yearnew > $toyear || $yearnew < 1900) ? '0000' : $yearnew;
		$monthnew = ($monthnew > 12 || $monthnew < 1) ? '00' : $monthnew;
		$daynew = ($daynew > 31 || $daynew < 1) ? '00' : $daynew;
		$memberinfonew['birthday'] = "$yearnew-$monthnew-$daynew";
		Member::edit($this->uid, array('gender' => $gendernew,'timeoffset' => $timeoffsetnew), array(), $memberinfonew, array());
		showmessage('profile_update_succeed', 'member.php?action=profile', NULL, array('type' => 'succeed', 'showdialog' => TRUE));
		return 0;
	}

}
?>