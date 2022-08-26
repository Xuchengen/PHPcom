<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Notice.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Notice extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$this->title = $title = lang('member', 'member_notice');
		$uid = $this->uid;
		$do = trim($this->request->query('do'));
		$id = intval($this->request->query('id'));
		
		if ($do == 'del' && $id) {
			DB::delete('notification', "uid='$uid' AND id='$id'");
		}
		$flag = (trim($this->request->query('flag'))) ? 0 : 1;
		$notetype = trim($this->request->query('type'));
		$condition = "AND flag='$flag' ";
		if ($notetype) {
			$condition .= "AND notetype='$notetype' ";
		}
		$datalist = array();
		$showpage = '';
		$newnotify = FALSE;
		$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('notification') . " WHERE uid='$uid' $condition");
		$pagesize = 20;
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($this->page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		if ($count) {
			$sql = DB::buildlimit("SELECT * FROM " . DB::table('notification') . " WHERE uid='$uid' $condition ORDER BY flag DESC, dateline DESC", $pagesize, $pagestart);
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				if ($row['flag']) {
					$newnotify = TRUE;
					$row['style'] = 'color:#444;font-weight:bold;';
				} else {
					$row['style'] = '';
				}
				$datalist[$row['id']] = $row;
			}
			
			$pageurl = "member.php?action=notice&flag=" . $this->request->query('flag') . "&page={%d}";
			$showpage = $this->paging($pagenow, $pagecount, $pagesize, $count, $pageurl, 5, 1, 1);
		}
		if ($newnotify) {
			DB::query("UPDATE " . DB::table('notification') . " SET flag='0' WHERE uid='$uid' AND flag='1'");
		}
		if (phpcom::$G['member']['prompts']) {
			DB::update('members', array('prompts' => 0), "uid='$uid'");
		}
		$currents = array('', '');
		$currents[$flag] = ' class="current"';
		include template('member/notice');
		return 1;
	}
}
?>