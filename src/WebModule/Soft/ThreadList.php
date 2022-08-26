<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : ThreadList.php  2012-8-6
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Soft_ThreadList extends Controller_ThreadList
{
	public function loadActionIndex()
	{
		$datalist = $tableIndexs = array();
		$tplname = 'soft/category';
		$queries = $this->getListQueries(array('softtype', 'license', 'softlang'), 'catid');
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
		$currname = lang('common', 'soft_list', array('name' => phpcom::$G['channel'][$chanid]['subname']));

		if ($category['basic']) {
			$datalist = $this->getFirstCategory($category['rootid']);
		}else{
			$count = $category['counts'];
			$order = "ORDER BY t.dateline DESC";
			$condition = "t.status='1' AND t.chanid='$chanid'";
			if(!empty($queries['softtype']) && $softtype = $this->getChannelSettingOfValue('softtype', $queries['softtype'])){
				$condition .= " AND s.softtype='$softtype' AND " . ($depth ? "s.catid='$catid'" : "s.rootid='$rootid'");
				$order = "ORDER BY s.dateline DESC";
				$currname = $softtype;
				$this->title .= " - $currname";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('soft_thread') . " 
						WHERE softtype='$softtype' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
			}elseif(!empty($queries['license']) && $license = $this->getChannelSettingOfValue('license', $queries['license'])){
				$condition .= " AND s.license='$license' AND " . ($depth ? "s.catid='$catid'" : "s.rootid='$rootid'");
				$order = "ORDER BY s.dateline DESC";
				$currname = $license;
				$this->title .= " - $currname";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('soft_thread') . "
						WHERE license='$license' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
			}elseif(!empty($queries['softlang']) && $softlang = $this->getChannelSettingOfValue('softlang', $queries['softlang'])){
				$condition .= " AND s.softlang='$softlang' AND " . ($depth ? "s.catid='$catid'" : "s.rootid='$rootid'");
				$order = "ORDER BY s.dateline DESC";
				$currname = $softlang;
				$this->title .= " - $currname";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('soft_thread') . "
						WHERE softlang='$softlang' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
			}elseif($queries['type'] == 'hot'){
				$condition .= $depth ? " AND t.catid='$catid'" : " AND t.rootid='$rootid'";
				$order = "ORDER BY t.hits DESC,t.dateline DESC";
				$currname = lang('common', 'soft_hot', array('name' => phpcom::$G['channel'][$chanid]['subname']));
				$this->title .= " - $currname";
			}elseif($queries['type'] == 'digest'){
				$condition .= " AND t.digest='1' AND " . ($depth ? "t.catid='$catid'" : "t.rootid='$rootid'");
				$order = "ORDER BY t.dateline DESC";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . "
						WHERE digest='1' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
				$currname = lang('common', 'soft_digest', array('name' => phpcom::$G['channel'][$chanid]['subname']));
				$this->title .= " - $currname";
			}elseif($queries['type'] == 'best'){
				$condition .= " AND t.digest='2' AND " . ($depth ? "t.catid='$catid'" : "t.rootid='$rootid'");
				$order = "ORDER BY t.dateline DESC";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . "
					WHERE digest='2' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
				$currname = lang('common', 'soft_best', array('name' => phpcom::$G['channel'][$chanid]['subname']));
				$this->title .= " - $currname";
			}elseif($queries['type'] == 'topline'){
				$condition .= " AND t.topline='1' AND " . ($depth ? "t.catid='$catid'" : "t.rootid='$rootid'");
				$order = "ORDER BY t.dateline DESC";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . "
				WHERE topline='1' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
				$currname = lang('common', 'soft_topline', array('name' => phpcom::$G['channel'][$chanid]['subname']));
				$this->title .= " - $currname";
			}elseif($queries['type'] == 'focus'){
				$condition .= " AND t.focus='1' AND " . ($depth ? "t.catid='$catid'" : "t.rootid='$rootid'");
				$order = "ORDER BY t.dateline DESC";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . "
				WHERE focus='1' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
				$currname = lang('common', 'soft_focus', array('name' => phpcom::$G['channel'][$chanid]['subname']));
				$this->title .= " - $currname";
			}elseif($queries['type'] == 'voteup'){
				$condition .= $depth ? " AND t.catid='$catid'" : " AND t.rootid='$rootid'";
				$order = 'ORDER BY f.voteup DESC';
				$currname = lang('common', 'soft_voteup', array('name' => phpcom::$G['channel'][$chanid]['subname']));
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
			//$pagesql = "INNER JOIN (" . DB::buildlimit("SELECT tid FROM " . DB::table('threads') . " WHERE $condition ORDER BY dateline DESC", $pagesize, $pagestart) . ") AS p USING(tid)";
			$sql = "SELECT t.*,s.*,c.depth,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,c.color,
					ti.attachment,ti.remote,ti.thumb,ti.preview,ti.attachimg,f.voteup,f.votedown,f.voters,f.totalscore,f.credits
					FROM " . DB::table('threads') . " t
		 			INNER JOIN " . DB::table('soft_thread') . " s USING(tid)
					LEFT JOIN " . DB::table('category') . " c ON c.catid=t.catid
					LEFT JOIN " . DB::table('thread_image') . " ti USING(tid)
					LEFT JOIN " . DB::table('thread_field') . " f USING(tid)
					WHERE $condition";
			$query = DB::query(DB::buildlimit("$sql $order", $pagesize, $pagestart));
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
			$tplname = 'soft/threadlist';
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