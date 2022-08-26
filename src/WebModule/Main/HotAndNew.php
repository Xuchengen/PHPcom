<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : HotAndNew.php  2012-8-4
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Main_HotAndNew extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$this->initialize();
		if(!isset(phpcom::$G['cache']['syscount'])){
			phpcom_cache::load('syscount');
		}
		$this->page = intval($this->request->query('page', $this->request->getQuery(0)));
		$this->chanid = $chanid = intval($this->request->query('chanid', $this->request->getQuery(1)));
		$hotminimum = phpcom::$setting['hotminimum'];
		$condition = "t.status='1'";
		$count = 500;
		$orderby = 'ORDER BY t.dateline DESC';
		
		if($this->chanid){
			if(isset(phpcom::$G['cache']['syscount']['thread']["count_$chanid"]) && phpcom::$G['cache']['syscount']['thread']["count_$chanid"] < 500){
				$count = phpcom::$G['cache']['syscount']['thread']["count_$chanid"];
			}
			$condition .= "AND t.chanid='$this->chanid'";
		}else{
			if(isset(phpcom::$G['cache']['syscount']['thread']['count']) && phpcom::$G['cache']['syscount']['thread']['count'] < 500){
				$count = phpcom::$G['cache']['syscount']['thread']['count'];
			}
		}
		if($this->action == 'hot'){
			$orderby = 'ORDER BY t.hits DESC, t.dateline DESC';
			$condition .= " AND t.hits>'$hotminimum'";
		}elseif($this->action == 'digest'){
			$condition .= " AND t.digest>='1'";
		}else{
			$this->action = 'new';
		}
		$datalist = array();
		
		$pagenum = intval(phpcom::$setting['pagenum']);
		$pagestats = phpcom::$setting['pagestats'];
		$pagesize = intval(phpcom::$setting['pagesize']);
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($this->page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$sql = DB::buildlimit("SELECT t.*,c.depth,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,
			ti.attachment,ti.remote,ti.thumb,ti.preview,ti.attachimg
			FROM " . DB::table('threads') . " t
			LEFT JOIN " . DB::table('category') . " c ON c.catid=t.catid
			LEFT JOIN " . DB::table('thread_image') . " ti USING(tid)
			WHERE $condition $orderby", $pagesize, $pagestart);
		$query = DB::query($sql);
		$urlargs = array('page' => 1);
		$i = 0;
		while ($row = DB::fetch_array($query)) {
			$i++;
			$row['highlight'] = $this->threadHighlight($row['highlight']);
			$row['colors'] = $row['highlight'];
			$row['index'] = $i;
			$row['alt'] = $i % 2 == 0 ? 2 : 1;
			
			$urlargs['chanid'] = $row['chanid'];
			$urlargs['tid'] = $row['tid'];
			$urlargs['catdir'] = $row['codename'];
			$urlargs['date'] = $row['dateline'];
			$urlargs['catid'] = $row['catid'];
			if(!empty($row['prefix'])){
				$urlargs['prefix'] = trim($row['prefix']);
			}
			if (empty(phpcom::$G['channel'][$row['chanid']]['domain']) && empty($row['prefixurl'])) {
				$row['domain'] = $this->domain;
			} elseif(empty($row['prefixurl'])) {
				$row['domain'] = phpcom::$G['channel'][$row['chanid']]['domain'] . '/';
			}else{
				$row['domain'] = $row['prefixurl'] . '/';
			}
			
			if (empty($row['url'])) {
				$urlargs['name'] = empty($row['htmlname']) ? '' : trim($row['htmlname']);
				$row['url'] = geturl('threadview',$urlargs, $row['domain']);
			}
		
			if (empty($row['caturl'])) {
				$urlargs['name'] = $row['codename'];
				if(!empty($row['prefixurl']) && $row['basic']){
					$row['curl'] = $row['prefixurl'];
				}else{
					$row['curl'] = geturl($row['basic'] ? 'category' : 'threadlist',$urlargs, $row['domain']);
				}
			}else{
				$row['curl'] = $row['caturl'];
			}
			$row['topic'] = "<a href=\"{$row['url']}\"{$row['highlight']}>{$row['title']}</a>";
			$row['istoday'] = $row['dateline'] + $this->timeoffset >= $this->todaytime ? 1 : 0;
			if ($row['istoday']) {
				$row['datestyle'] = 'new';
				$row['date'] = '<em class="new">'. fmdate($row['dateline']) . '</em>';
			} else {
				$row['datestyle'] = 'old';
				$row['date'] = '<em class="old">'. fmdate($row['dateline']) . '</em>';
			}
			$row['purl'] = $row['url'];
			if(isset($row['attachment']) && $row['image'] == 1){
				$this->processImageRowData($row, phpcom::$G['channel'][$row['chanid']]['modules']);
			}else{
				$row['thumburl'] = $row['previewurl'] = $row['imageurl'] = $this->domain . 'misc/images/noimage.jpg';
				$row['pixurl'] = $row['url'];
			}
			if(isset($row['attached']) && $row['attached'] == 2){
				$row['pixurl'] = geturl('preview', array(
						'chanid' => $row['chanid'],
						'catdir' => $row['codename'],
						'tid' => $row['tid'],
						'page' => 1
				), $this->domain);
			}
			$datalist[] = $row;
		}
		$act = $this->action == 'hot' ? 'hot' : 'new';
		$reaction = $act == 'hot' ? 'new' : 'hot';
		$reactiontitle = lang('common', "menu_$reaction");
		$reurl = geturl('hotnew', array('action' => $reaction, 'name' => $reaction, 'page' => 1), $this->domain);
		$this->title = $title = lang('common', "menu_$act");
		$urlargs = array('cid' => $chanid, 'action' => $act, 'name' => $act, 'page' => '{%d}');
		$pageurl = geturl('hotnew', $urlargs, $this->domain);
		$firsturl = $this->formatPageUrl($pageurl);
		$urlargs['page'] = $pagenow;
		$htmlfile = geturl('hotnew', $urlargs);
		$showpage = $this->paging($pagenow, $pagecount, $pagesize, $count, $pageurl, $pagenum, $pagestats, 0, $firsturl);
		$currenturl = str_replace('{%d}', $pagenow, $pageurl);
		$this->checkRequestUri($currenturl);
		$tplname = checktplname('hotnew',  '', $this->request->getParam('module'));
		include template($tplname);
		return 1;
	}

}
?>