<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : ThreadList.php  2012-8-15
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Video_ThreadList extends Controller_ThreadList
{
	public function loadActionIndex()
	{
		$datalist = $tableIndexs = array();
		$tplname = 'video/category';
		$queries = $this->getListQueries(array('country', 'dialogue', 'years'), 'catid');
		$category = $this->threadCategory($queries);
		$chanid = $this->chanid;
		$pageurl = $category['pageurl'];
		$firsturl = $category['firsturl'];
		$caturl = $category['caturl'];
		$topurl = $category['topurl'];
		$imageurl = $category['imageurl'];
		$banner = $category['banner'];
		$catname = $title = $category['catname'];
		$catdir = $category['codename'];
		$prefixurl = $category['prefixurl'];
		$name = $category['subname'];
		$rootid = $this->rootid;
		$catid = $this->catid;
		$depth = $category['depth'];
		$classid = 0;
		$currname = lang('common', 'video_list', array('name' => phpcom::$G['channel'][$chanid]['subname']));
		if ($category['basic']) {
			$datalist = $this->getFirstCategory($category['rootid']);
		}else{
			$count = $category['counts'];
			$order = "ORDER BY t.dateline DESC";
			$condition = "t.status='1' AND t.chanid='$chanid'";
			if(!empty($queries['country']) && $country = $this->getChannelSettingOfValue('country', $queries['country'])){
				$condition .= " AND s.country='$country' AND " . ($depth ? "s.catid='$catid'" : "s.rootid='$rootid'");
				$order = "ORDER BY s.dateline DESC";
				$currname = $country;
				$this->title .= " - $currname";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('video_thread') . "
						WHERE country='$country' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
			}elseif(!empty($queries['dialogue']) && $dialogue = $this->getChannelSettingOfValue('dialogue', $queries['dialogue'])){
				$condition .= " AND s.dialogue='$dialogue' AND " . ($depth ? "s.catid='$catid'" : "s.rootid='$rootid'");
				$order = "ORDER BY s.dateline DESC";
				$currname = $dialogue;
				$this->title .= " - $currname";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('video_thread') . "
						WHERE dialogue='$dialogue' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
			}elseif(!empty($queries['years']) && $years = intval($queries['years'])){
				$condition .= " AND s.years='$years' AND " . ($depth ? "s.catid='$catid'" : "s.rootid='$rootid'");
				$order = "ORDER BY s.dateline DESC";
				$currname = lang('common', 'video_year', array('year' => $years));
				$this->title .= " - $currname";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('video_thread') . "
						WHERE years='$years' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
			}elseif($queries['type'] == 'hot'){
				$order = "ORDER BY t.hits DESC,t.dateline DESC";
				$currname = lang('common', 'video_hot', array('name' => phpcom::$G['channel'][$chanid]['subname']));
				$this->title .= " - $currname";
			}elseif($queries['type'] == 'digest'){
				$condition .= " AND t.digest='1' AND " . ($depth ? "t.catid='$catid'" : "t.rootid='$rootid'");
				$order = "ORDER BY t.dateline DESC";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . "
					WHERE digest='1' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
				$currname = lang('common', 'video_digest', array('name' => phpcom::$G['channel'][$chanid]['subname']));
				$this->title .= " - $currname";
			}elseif($queries['type'] == 'best'){
				$condition .= " AND t.digest='2' AND " . ($depth ? "t.catid='$catid'" : "t.rootid='$rootid'");
				$order = "ORDER BY t.dateline DESC";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . "
				WHERE digest='2' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
				$currname = lang('common', 'video_best', array('name' => phpcom::$G['channel'][$chanid]['subname']));
				$this->title .= " - $currname";
			}elseif($queries['type'] == 'topline'){
				$condition .= " AND t.topline='1' AND " . ($depth ? "t.catid='$catid'" : "t.rootid='$rootid'");
				$order = "ORDER BY t.dateline DESC";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . "
			WHERE topline='1' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
				$currname = lang('common', 'video_topline', array('name' => phpcom::$G['channel'][$chanid]['subname']));
				$this->title .= " - $currname";
			}elseif($queries['type'] == 'focus'){
				$condition .= " AND t.focus='1' AND " . ($depth ? "t.catid='$catid'" : "t.rootid='$rootid'");
				$order = "ORDER BY t.dateline DESC";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . "
			WHERE focus='1' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
				$currname = lang('common', 'video_focus', array('name' => phpcom::$G['channel'][$chanid]['subname']));
				$this->title .= " - $currname";
			}else{
				$condition .= $depth ? " AND t.catid='$catid'" : " AND t.rootid='$rootid'";
			}
			
			$pagenum = intval(phpcom::$G['channel'][$chanid]['pagenum']);
			$pageinput = phpcom::$G['channel'][$chanid]['pageinput'];
			$pagestats = phpcom::$G['channel'][$chanid]['pagestats'];
			$pagesize = $category['pagesize'];
			$pagecount = @ceil($count / $pagesize);
			$pagenow = max(1, min($pagecount, intval($this->page)));
			$pagestart = floor(($pagenow - 1) * $pagesize);
			$sql = DB::buildlimit("SELECT t.*,s.*,c.depth,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,c.color,
				ti.attachment,ti.remote,ti.thumb,ti.preview,ti.attachimg,f.voteup,f.votedown,f.voters,f.totalscore,f.credits
				FROM " . DB::table('threads') . " t
		 		LEFT JOIN " . DB::table('video_thread') . " s USING(tid)
         		LEFT JOIN " . DB::table('category') . " c ON c.catid=t.catid
				LEFT JOIN " . DB::table('thread_image') . " ti USING(tid)
				LEFT JOIN " . DB::table('thread_field') . " f USING(tid)
				WHERE $condition $order", $pagesize, $pagestart);
			$query = DB::query($sql);
			$i = 0;
			while ($row = DB::fetch_array($query)) {
				$i++;
				$row['index'] = $i;
				$row['alt'] = $i % 2 == 0 ? 2 : 1;
				$this->processThreadListData($row);

				$tableIndexs[$row['tableindex']] = $row['tableindex'];

				$row['purl'] = geturl('play', array(
						'chanid' => $row['chanid'],
						'catdir' => $row['codename'],
						'name' => $row['codename'],
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
			$tplname = 'video/threadlist';
		}
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