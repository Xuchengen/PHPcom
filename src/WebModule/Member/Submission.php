<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Submission.php  2012-12-15
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Submission extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$this->title = $title = lang('member', 'member_submission');
	
		$this->fetchSubmissionList();
		return 1;
	}
	
	public function fetchSubmissionList()
	{
		$datalist = array();
		$showpage = '';
		$uid = $this->uid;
		$condition = "t.uid='$uid'";
		$count = intval($this->request->query('count', 0));
		!$count && $count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('post_contents') . " t WHERE $condition");
		$pagesize = 30;
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($this->page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$sql = DB::buildlimit("SELECT t.*,c.catname,c.codename,c.color FROM " . DB::table('post_contents') . " t 
			LEFT JOIN " . DB::table('category') . " c USING(catid)
			WHERE $condition ORDER BY t.postid DESC", $pagesize, $pagestart);
		$query = DB::query($sql);
		$i = 0;
		$todaytime = strtotime(fmdate(TIMESTAMP, 'Ymd'));
		while ($row = DB::fetch_array($query)) {
			$i++;
			$row['index'] = $i;
			$row['alt'] = $i % 2 == 0 ? ' class="alt"' : '';
			$row['color'] = $row['color'] ? ' style="color: ' . $row['color'] . '"' : '';
			$row['date'] = fmdate($row['dateline'], 'dt', 'd');
			$row['icons'] = 'txt.gif';
			$row['istoday'] = $row['dateline'] > $todaytime ? 1 : 0;
			$row['modname'] = 'article';
			if(isset(phpcom::$G['channel'][$row['chanid']]['modules'])){
				$row['modname'] = trim(phpcom::$G['channel'][$row['chanid']]['modules']);
			}
			if(empty($row['url'])){
				$row['url'] = "member.php?action=post&do={$row['modname']}&postid={$row['postid']}&chanid={$row['chanid']}";
			}
			$datalist[] = $row;
		}
		$pageurl = 'member.php?action=submission&page={%d}';
		$showpage = $this->paging($pagenow, $pagecount, $pagesize, $count, $pageurl);
		include template('member/submission');
		return 1;
	}
}
?>