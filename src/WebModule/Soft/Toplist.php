<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Toplist.php  2013-11-12
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Soft_Toplist extends Controller_ThreadList
{
	public function loadActionIndex()
	{
		$datalist = $tableIndexs = array();
		$tplname = 'soft/toplist';
		$category = $this->threadCategory(null, 'toplist');
		$chanid = $this->chanid;
		$caturl = $category['caturl'];
		$topurl = $category['topurl'];
		$topweekurl = $category['topweekurl'];
		$topmonthurl = $category['topmonthurl'];
		$imageurl = $category['imageurl'];
		$banner = $category['banner'];
		$catname = $category['catname'];
		$catdir = $category['codename'];
		$prefixurl = $category['prefixurl'];
		$topnum = empty($category['topnum']) ? 50 : $category['topnum'];
		$toptype = empty($category['toptype']) ? 0 : $category['toptype'];
		$topmode = empty($category['topmode']) ? 0 : $category['topmode'];
		$name = $category['subname'];
		$rootid = $this->rootid;
		$catid = $this->catid;
		$depth = $category['depth'];
		$classid = 0;
		$condition = "t.status='1' ";
		$condition .= $depth ? " AND t.catid='$catid'" : " AND t.rootid='$rootid'";
		$order = "ORDER BY t.weekcount DESC";
		$typemap = array('W', 'M', 'S');
		$typeid = intval($this->request->query('type', $this->request->getQuery(1)));
		$type = $typename = 'W';
		if($typeid > 0){
			$typeid--;
			$type = isset($typemap[$typeid]) ? $typemap[$typeid] : 'W';
		}else{
			$type = isset($typemap[$toptype]) ? $typemap[$toptype] : 'W';
		}
		
		if($type == 'M'){
			$order = "ORDER BY t.monthcount DESC";
			$typename = lang('common', 'thread_toplist_month', array('name' => ''));
		}elseif($type == 'S'){
			$condition .= " AND s.star='5'";
			$order = "ORDER BY t.monthcount DESC";
			$typename = lang('common', 'thread_toplist_star', array('name' => ''));
		}else{
			$typename = lang('common', 'thread_toplist_week', array('name' => ''));
		}
		$currname = $catname . $typename;
		if(empty($category['toptitle'])){
			$this->title = $currname;
		}else{
			$this->title = str_replace(array('{type}', '{year}', '{name}'), array($typename, date('Y'), $catname), $category['toptitle']);
		}
		
		if($topmode){
			$sql = "SELECT t.*,s.*,c.depth,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,c.color,
					ti.attachment,ti.remote,ti.thumb,ti.preview,ti.attachimg,
					f.voteup,f.votedown,f.voters,f.totalscore,f.credits
					FROM " . DB::table('threads') . " t
			 		INNER JOIN " . DB::table('soft_thread') . " s USING(tid)
					LEFT JOIN " . DB::table('category') . " c ON c.catid=t.catid
					LEFT JOIN " . DB::table('thread_image') . " ti USING(tid)
					LEFT JOIN " . DB::table('thread_field') . " f USING(tid)
					WHERE $condition $order";
			$sql = DB::buildlimit($sql, $topnum);
			//echo "explain $sql";
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
				$row['trend'] = 'fair';
				if($type != 'W'){
					$row['count'] = $row['monthcount'];
					if($row['lastmonth'] <= 0 && $row['monthcount'] <= 0){
						$row['trend'] = 'fair';
					}else{
						$monthavg = round($row['lastmonth'] * ($this->monthdiff / 2592000));
						if($row['monthcount'] > $monthavg){
							$row['trend'] = 'rise';
						}elseif($row['monthcount'] < $monthavg){
							$row['trend'] = 'fall';
						}else{
							$row['trend'] = 'fair';
						}
					}
				}else{
					$row['count'] = $row['weekcount'];
					if($row['lastweek'] <= 0 && $row['weekcount'] <= 0){
						$row['trend'] = 'fair';
					}else{
						$weekavg = round($row['lastweek'] * ($this->weekdiff / 604800));
						if($row['weekcount'] > $weekavg){
							$row['trend'] = 'rise';
						}elseif($row['weekcount'] < $weekavg){
							$row['trend'] = 'fall';
						}else{
							$row['trend'] = 'fair';
						}
					}
				}
				$datalist[] = $row;
			}
		}else{
			$datalist = $this->getFirstCategory($category['rootid'], $topnum);
		}
		$showpage = '';
		if ($this->templateName) {
			$tplname = checktplname($tplname, $this->templateName . '_top');
		} else {
			$tplname = checktplname($tplname, $this->chanid);
		}
		include template($tplname);
		return 1;
	}
}
?>