<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : ThreadList.php  2012-8-15
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Photo_ThreadList extends Controller_ThreadList
{
	public function loadActionIndex()
	{
		$datalist = $tableIndexs = array();
		$tplname = 'photo/category';
		$queries = $this->getListQueries(null, 'catid');
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
		$currname = lang('common', 'photo_list', array('name' => phpcom::$G['channel'][$chanid]['subname']));
		if ($category['basic']) {
			$datalist = $this->getFirstCategory($category['rootid']);
		}else{
			$count = $category['counts'];
			$order = "ORDER BY t.dateline DESC";
			$condition = "t.status='1' AND t.chanid='$chanid'";
			if($queries['type'] == 'hot'){
				$order = "ORDER BY t.hits DESC,t.dateline DESC";
				$currname = lang('common', 'photo_hot', array('name' => phpcom::$G['channel'][$chanid]['subname']));
				$this->title .= " - $currname";
			}elseif($queries['type'] == 'digest'){
				$condition .= " AND t.digest='1' AND " . ($depth ? "t.catid='$catid'" : "t.rootid='$rootid'");
				$order = "ORDER BY t.dateline DESC";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . "
					WHERE digest='1' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
				$currname = lang('common', 'photo_digest', array('name' => phpcom::$G['channel'][$chanid]['subname']));
				$this->title .= " - $currname";
			}elseif($queries['type'] == 'best'){
				$condition .= " AND t.digest='2' AND " . ($depth ? "t.catid='$catid'" : "t.rootid='$rootid'");
				$order = "ORDER BY t.dateline DESC";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . "
				WHERE digest='2' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
				$currname = lang('common', 'photo_best', array('name' => phpcom::$G['channel'][$chanid]['subname']));
				$this->title .= " - $currname";
			}elseif($queries['type'] == 'topline'){
				$condition .= " AND t.topline='1' AND " . ($depth ? "t.catid='$catid'" : "t.rootid='$rootid'");
				$order = "ORDER BY t.dateline DESC";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . "
			WHERE topline='1' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
				$currname = lang('common', 'photo_topline', array('name' => phpcom::$G['channel'][$chanid]['subname']));
				$this->title .= " - $currname";
			}elseif($queries['type'] == 'focus'){
				$condition .= " AND t.focus='1' AND " . ($depth ? "t.catid='$catid'" : "t.rootid='$rootid'");
				$order = "ORDER BY t.dateline DESC";
				$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . "
			WHERE focus='1' AND " . ($depth ? "catid='$catid'" : "rootid='$rootid'"));
				$currname = lang('common', 'photo_focus', array('name' => phpcom::$G['channel'][$chanid]['subname']));
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
			$urlargs = array('chanid' => $this->chanid, 'page' => 1);
			$sql = DB::buildlimit("SELECT t.*,a.*,c.depth,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,c.color,
				ti.attachment,ti.remote,ti.thumb,ti.preview,ti.attachimg
				FROM " . DB::table('threads') . " t
		 		LEFT JOIN " . DB::table('photo_thread') . " a USING(tid)
         		LEFT JOIN " . DB::table('category') . " c ON c.catid=t.catid
				LEFT JOIN " . DB::table('thread_image') . " ti USING(tid)
				WHERE $condition $order", $pagesize, $pagestart);
			$query = DB::query($sql);
			$i = 0;
			while ($row = DB::fetch_array($query)) {
				$i++;
				$row['index'] = $i;
				$row['alt'] = $i % 2 == 0 ? 2 : 1;
				$this->processThreadListData($row);
				
				$tableIndexs[$row['tableindex']] = $row['tableindex'];
				$row['purl'] = $row['pixurl'];
				
				$datalist[] = $row;
			}
			$showpage = $this->paging($pagenow, $pagecount, $pagesize, $count, $pageurl, $pagenum, $pagestats, $pageinput, $firsturl);
			$tplname = 'photo/threadlist';
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