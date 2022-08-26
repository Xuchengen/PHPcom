<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Misc.php  2013-1-13
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Video_Misc extends Controller_ThreadList
{
	public function loadActionIndex()
	{
		$datalist = array();
		$queries = $this->getMiscQueries(array('country', 'dialogue', 'years'), 'person');
		$result = $this->getMiscInfo($queries);
		$chanid = $this->chanid;
		$classid = 0;
		$catid = $this->catid;
		$rootid = $this->rootid;
		$showpage = $caturl = '';
		$pageurl = $result['pageurl'];
		$firsturl = $result['firsturl'];
		$name = $currname = $result['currname'];
		$jointable = '';
		$subqueries = false;
		$isperson = false;
		$condition = "";
		$order = "ORDER BY t.dateline DESC";
		$count = 1000;
		if(!empty($queries['person']) && $person = httpurl_decode($queries['person'])){
			$jointable = "INNER JOIN " . DB::table('persondata') . " d ON t.tid=d.tid";
			$condition .= " AND d.name='$person'";
			$order = "ORDER BY d.tid DESC";
			$currname = $person;
			$this->title .= " - $currname";
			$isperson = true;
			$queries['country'] = $queries['dialogue'] = $queries['years'] = 0;
			$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('persondata') . " WHERE name='$person'");
		}
		if(!empty($queries['country']) && $country = $this->getChannelSettingOfValue('country', $queries['country'])){
			$condition .= " AND s.country='$country'";
			$subqueries = true;
			$currname = $country;
			$this->title .= " - $currname";
		}
		if(!empty($queries['dialogue']) && $dialogue = $this->getChannelSettingOfValue('dialogue', $queries['dialogue'])){
			$condition .= " AND s.dialogue='$dialogue'";
			$subqueries = true;
			$currname = $dialogue;
			$this->title .= " - $currname";
		}
		if(!empty($queries['years']) && $years = intval($queries['years'])){
			$condition .= " AND s.years='$years'";
			$subqueries = true;
			$currname = lang('common', 'video_year', array('year' => $years));
			$this->title .= " - $currname";
		}
		if ($catid && $subqueries) {
			$condition .= $result['depth'] ? " AND s.catid='$catid'" : " AND s.rootid='$rootid'";
			$order = "ORDER BY s.dateline DESC";
			$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('video_thread') . " s WHERE 1 $condition");
		}elseif($subqueries){
			$condition .= " AND s.chanid='$chanid'";
			$order = "ORDER BY s.dateline DESC";
			$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('video_thread') . " s WHERE 1 $condition");
		}elseif(!$isperson){
			$condition .= $catid ? ($result['depth'] ? " AND t.catid='$catid'" : " AND t.rootid='$rootid'") : " AND t.chanid='$chanid'";
			$order = "ORDER BY t.dateline DESC";
			$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " t WHERE t.status='1' $condition");
		}
		
		$count = $count > 1000 ? 1000 : $count;
		
		$pagenum = intval(phpcom::$G['cache']['channel']['pagenum']);
		$pageinput = phpcom::$G['cache']['channel']['pageinput'];
		$pagestats = phpcom::$G['cache']['channel']['pagestats'];
		$pagesize = phpcom::$G['cache']['channel']['pagesize'];
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($this->page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$sql = DB::buildlimit("SELECT t.*,s.*,c.depth,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,c.color,
				ti.attachment,ti.remote,ti.thumb,ti.preview,ti.attachimg,f.voteup,f.votedown,f.voters,f.totalscore,f.credits
				FROM " . DB::table('threads') . " t
				INNER JOIN " . DB::table('video_thread') . " s USING(tid)
				LEFT JOIN " . DB::table('category') . " c ON c.catid=t.catid
				LEFT JOIN " . DB::table('thread_image') . " ti USING(tid)
				LEFT JOIN " . DB::table('thread_field') . " f USING(tid)
				$jointable WHERE t.status='1' $condition $order", $pagesize, $pagestart);
		$query = DB::query($sql);
		$i = 0;
		while ($row = DB::fetch_array($query)) {
			$i++;
			$row['index'] = $i;
			$row['alt'] = $i % 2 == 0 ? 2 : 1;
			$row['language'] = $row['dialogue'];
			$this->processThreadListData($row);

			$tableIndexs[$row['tableindex']] = $row['tableindex'];
			$row['purl'] = geturl('play', array(
					'chanid' => $row['chanid'],
					'catdir' => $row['codename'],
					'tid' => $row['tid'],
					'id' => $row['aid'],
					'page' => 1
			), $this->chandomain);
			$scores = $row['voters'] ? $row['totalscore']  / $row['voters'] : 0;
			$row['scores'] = $scores < 10 ? sprintf( "%01.1f ", $scores) : 10;
			$row['percent'] = $row['scores'] ? ($row['scores'] * 10) . '%' : '0%';
			$datalist[] = $row;
		}
		$showpage = $this->paging($pagenow, $pagecount, $pagesize, $count, $pageurl, $pagenum, $pagestats, $pageinput, $firsturl);
		$tplname = 'video/misc';
		$tplname = checktplname($tplname, $this->chanid);
		
		include template($tplname);
		return 1;
	}
}

?>