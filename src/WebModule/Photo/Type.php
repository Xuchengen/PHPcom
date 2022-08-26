<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Type.php  2013-1-8
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Photo_Type extends Controller_ThreadList
{
	public function loadActionIndex()
	{
		$queries = $this->getListQueries('catid', 'classid');
		$classid = $queries['classid'];
		if($classid && !isset(phpcom::$G['cache']['thread_class'])){
			phpcom_cache::load('thread_class');
		}
		$result = $this->getThreadClass($queries);
		$chanid = &$this->chanid;
		$catid = &$this->catid;
		$rootid = &$this->rootid;
		$pageurl = $result['pageurl'];
		$firsturl = $result['firsturl'];
		$caturl = $result['caturl'];
		$imageurl = $result['imageurl'];
		$banner = $result['banner'];
		$about = $result['about'];
		$catname = $result['catname'];
		$catdir = $result['codename'];
		$prefixurl = $result['prefixurl'];
		$currname = $name = $result['name'];
		$currenturl = $result['currenturl'];
		$datalist = $tableIndexs = array();
		$showpage = $countcond = '';
		$condition = "t.status='1' AND t.chanid='$chanid'";
		if($catid){
			$condition .= " AND d.catid='$catid'";
			$countcond = "catid='$catid' AND";
		}
		$order = "ORDER BY d.dateline DESC";
		if(!empty($queries['type']) && $queries['type'] == 'hot'){
			$order = "ORDER BY t.hits DESC,t.dateline DESC";
			$currname = lang('common', 'video_hot', array('name' => phpcom::$G['channel'][$chanid]['subname']));
			$this->title .= " - $currname";
		}
		$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('thread_class_data') . " 
				WHERE $countcond classid='$classid'");
		$pagenum = intval(phpcom::$G['channel'][$chanid]['pagenum']);
		$pageinput = phpcom::$G['channel'][$chanid]['pageinput'];
		$pagestats = phpcom::$G['channel'][$chanid]['pagestats'];
		$pagesize = $result['pagesize'];
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($this->page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$sql = DB::buildlimit("SELECT t.*,a.*,c.depth,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,c.color,
				ti.attachment,ti.remote,ti.thumb,ti.preview,ti.attachimg,f.voteup,f.votedown,f.voters,f.totalscore,f.credits,d.classid
				FROM " . DB::table('threads') . " t
		 		LEFT JOIN " . DB::table('photo_thread') . " a USING(tid)
         		LEFT JOIN " . DB::table('category') . " c ON c.catid=t.catid
				LEFT JOIN " . DB::table('thread_image') . " ti USING(tid)
				INNER JOIN " . DB::table('thread_class_data') . " d ON t.tid=d.tid
				WHERE $condition AND d.classid='$classid' $order", $pagesize, $pagestart);
		$query = DB::query($sql);
		$i = 0;
		while ($row = DB::fetch_array($query)) {
			$i++;
			$row['index'] = $i;
			$row['alt'] = $i % 2 == 0 ? 2 : 1;
			$this->processThreadListData($row);

			$tableIndexs[$row['tableindex']] = $row['tableindex'];
				
			$scores = $row['voters'] ? $row['totalscore']  / $row['voters'] : 0;
			$row['scores'] = $scores < 10 ? sprintf( "%01.1f ", $scores) : 10;
			$row['percent'] = $row['scores'] ? ($row['scores'] * 10) . '%' : '0%';

			$datalist[] = $row;
		}
		$showpage = $this->paging($pagenow, $pagecount, $pagesize, $count, $pageurl, $pagenum, $pagestats, $pageinput, $firsturl);

		$tplname = 'photo/type';
		if ($this->templateName) {
			$tplname = checktplname($tplname, $this->templateName);
		} else {
			$tplname = checktplname($tplname, $this->chanid);
		}
		include template($tplname);
		return 1;
	}
}
?>