<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Thread.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Thread extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$this->title = $title = lang('member', 'member_thread');
		
		$this->fetchThreadList();
		return 1;
	}
	
	public function fetchThreadList($chanid = 0, $catid = 0)
	{
		$datalist = array();
		$showpage = '';
		$uid = $this->uid;
		$condition = "t.uid='$uid' AND t.status='1'";
		$condition .= $chanid ? " AND t.chanid='$chanid'" : "";
		$condition .= $catid ? " AND t.catid=$catid" : "";
		$count = intval($this->request->query('count', 0));
		!$count && $count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " t WHERE $condition");
		$pagesize = 30;
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($this->page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$sql = DB::buildlimit("SELECT t.*,c.basic,c.catname,c.codename,c.prefixurl,c.prefix,c.color FROM " . DB::table('threads') . " t
			LEFT JOIN " . DB::table('category') . " c USING(catid)
			WHERE $condition ORDER BY t.dateline DESC", $pagesize, $pagestart);
		$query = DB::query($sql);
		$i = 0;
		$todaytime = strtotime(fmdate(TIMESTAMP, 'Ymd'));
		while ($row = DB::fetch_array($query)) {
			$i++;
			$row['index'] = $i;
			$row['alt'] = $i % 2 == 0 ? ' class="alt"' : '';
			$row['color'] = $row['color'] ? ' style="color: ' . $row['color'] . '"' : '';
			$row['highlight'] = $this->threadHighlight($row['highlight']);
			$row['date'] = fmdate($row['dateline'], 'dt', 'd');
			$row['icons'] = 'txt.gif';
			$row['modname'] = trim(phpcom::$G['channel'][$row['chanid']]['modules']);
			$urlargs = array('chanid' => $row['chanid'], 'catdir' => $row['codename'], 'tid' => $row['tid'],
					'date' => $row['dateline'], 'cid' => $row['catid'], 'catid' => $row['catid'], 'page' => 1);
			
			if (empty(phpcom::$G['channel'][$row['chanid']]['domain']) && empty($row['prefixurl'])) {
				$row['domain'] = phpcom::$G['instdir'];
			} elseif(empty($row['prefixurl'])) {
				$row['domain'] = phpcom::$G['channel'][$row['chanid']]['domain'] . '/';
			}else{
				$row['domain'] = $row['prefixurl'] . '/';
			}
			$urlargs['prefix'] = empty($row['prefix']) ? '' : trim($row['prefix']);
			if (empty($row['url'])) {
				$urlargs['name'] = empty($row['htmlname']) ? '' : trim($row['htmlname']);
				$row['url'] = geturl('threadview', $urlargs, $row['domain']);
			}else{
				$row['icons'] = 'link.gif';
			}
			if (empty($row['caturl'])) {
				$urlargs['name'] = $row['codename'];
				$row['caturl'] = geturl($row['basic'] ? 'category' : 'threadlist', $urlargs, $row['domain']);
			}
			$row['weeknew'] = TIMESTAMP - 604800 <= $row['dateline'];
			$row['istoday'] = $row['dateline'] > $todaytime ? 1 : 0;
			$row['recommend'] = '';
        	$row['focus'] = $row['focus'] ? '<img src="misc/images/icons/focus.gif" />' : '';
        	$row['topline'] = $row['topline'] ? '<img src="misc/images/icons/topline.gif" />' : '';
        	switch ($row['digest']) {
        		case 1: $row['digest'] = '<img src="misc/images/icons/digest.gif" />'; break;
        		case 2: 
        			$row['digest'] = '<img src="misc/images/icons/recommend.gif" />'; 
        			$row['recommend'] = '<img src="misc/images/icons/recommend.gif" />';
        			break;
        		case 3: $row['digest'] = '<img src="misc/images/icons/very.gif" />'; break;
        		case 4: $row['digest'] = '<img src="misc/images/icons/cool.gif" />'; break;
        		case 5: $row['digest'] = '<img src="misc/images/icons/green.gif" />'; break;
        		default: $row['digest'] = ''; break;
        	}
        	$row['icons'] = $row['istop'] ? 'pin.gif' : $row['icons'];
			
			$datalist[] = $row;
		}
		$pageurl = 'member.php?action=thread';
		$pageurl .= $chanid ? "&chanid=$chanid" : "";
		$pageurl .= $catid ? "&catid=$catid" : "";
		$pageurl .= "&count=$count&page={%d}";
		$showpage = $this->paging($pagenow, $pagecount, $pagesize, $count, $pageurl);
		
		include template('member/thread');
		return 1;
	}
}
?>