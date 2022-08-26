<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Misc.php  2012-12-25
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Soft_Misc extends Controller_ThreadList
{
	public function loadActionIndex()
	{
		$datalist = array();
		$queries = $this->getMiscQueries(array('softtype', 'license', 'softlang'));
		$result = $this->getMiscInfo($queries);
		$chanid = $this->chanid;
		$classid = 0;
		$catid = $this->catid;
		$rootid = $this->rootid;
		$showpage = $caturl = '';
		$pageurl = $result['pageurl'];
		$firsturl = $result['firsturl'];
		$name = $currname = $result['currname'];
		$subqueries = false;
		$condition = "";
		$order = "ORDER BY t.dateline DESC";
		$count = 1000;
		if(!empty($queries['softtype']) && $softtype = $this->getChannelSettingOfValue('softtype', $queries['softtype'])){
			$condition .= " AND s.softtype='$softtype'";
			$subqueries = true;
			$currname = $softtype;
			$this->title .= " - $currname";
		}
		if(!empty($queries['license']) && $license = $this->getChannelSettingOfValue('license', $queries['license'])){
			$condition .= " AND s.license='$license'";
			$subqueries = true;
			$currname = $license;
			$this->title .= " - $currname";
		}
		if(!empty($queries['softlang']) && $softlang = $this->getChannelSettingOfValue('softlang', $queries['softlang'])){
			$condition .= " AND s.softlang='$softlang'";
			$subqueries = true;
			$currname = $softlang;
			$this->title .= " - $currname";
		}
		if ($catid && $subqueries) {
			$condition .= $result['depth'] ? " AND s.catid='$catid'" : " AND s.rootid='$rootid'";
			$order = "ORDER BY s.dateline DESC";
			$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('soft_thread') . " s WHERE 1 $condition");
		}elseif($subqueries){
			$condition .= " AND s.chanid='$chanid'";
			$order = "ORDER BY s.dateline DESC";
			$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('soft_thread') . " s WHERE 1 $condition");
		}else{
			$condition .= $catid ? ($result['depth'] ? " AND t.catid='$catid'" : " AND t.rootid='$rootid'") : " AND t.chanid='$chanid'";
			$order = "ORDER BY t.dateline DESC";
			$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " t WHERE t.status='1' $condition");
		}

		$count = $count > 1000 ? 1000 : $count;
		
		$pagenum = intval(phpcom::$G['channel'][$chanid]['pagenum']);
		$pageinput = phpcom::$G['channel'][$chanid]['pageinput'];
		$pagestats = phpcom::$G['channel'][$chanid]['pagestats'];
		$pagesize = phpcom::$G['channel'][$chanid]['pagesize'];
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($this->page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$sql = DB::buildlimit("SELECT t.*,s.*,c.depth,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,c.color,
				ti.attachment,ti.remote,ti.thumb,ti.preview,ti.attachimg,f.voteup,f.votedown,f.voters,f.totalscore,f.credits
				FROM " . DB::table('threads') . " t
				INNER JOIN " . DB::table('soft_thread') . " s USING(tid)
				LEFT JOIN " . DB::table('category') . " c ON c.catid=t.catid
				LEFT JOIN " . DB::table('thread_image') . " ti USING(tid)
				LEFT JOIN " . DB::table('thread_field') . " f USING(tid)
				WHERE t.status='1' $condition $order", $pagesize, $pagestart);
		$query = DB::query($sql);
		$i = 0;
		while ($row = DB::fetch_array($query)) {
			$i++;
			$row['index'] = $i;
			$row['alt'] = $i % 2 == 0 ? 2 : 1;
			$this->processThreadListData($row);
			
			$row['size'] = formatbytes(intval($row['softsize']) * 1024);
			$tableIndexs[$row['tableindex']] = $row['tableindex'];
			
			$scores = $row['voters'] ? $row['totalscore']  / $row['voters'] : 0;
			$row['scores'] = $scores < 10 ? sprintf( "%01.1f ", $scores) : 10;
			$row['percent'] = $row['scores'] ? ($row['scores'] * 10) . '%' : '0%';
			$datalist[] = $row;
		}
		$showpage = $this->paging($pagenow, $pagecount, $pagesize, $count, $pageurl, $pagenum, $pagestats, $pageinput, $firsturl);

		$tplname = 'soft/misc';
		$tplname = checktplname($tplname, $this->chanid);
		include template($tplname);
		return 1;
	}
}
?>