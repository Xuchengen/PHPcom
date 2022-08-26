<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Tag.php  2012-8-5
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Main_Tag extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$this->initialize();
		$this->page = intval($this->request->query('page', $this->request->getQuery('page')));
		$tagid = intval($this->request->query('tagid', $this->request->getQuery(0)));
		$condition = "tagid='$tagid'";
		if(!$tagid){
			if($tagname = stripstring(rawurldecode($this->request->query('name', $this->request->getQuery(0))))){
				$condition = "tagname='$tagname'";
			}else{
				exit(header("HTTP/1.1 403 Forbidden"));
			}
		}
		$tids = array();
		$query = DB::query("SELECT tid, tagname FROM " . DB::table('tagdata') . " WHERE $condition");
		while ($row = DB::fetch_array($query)) {
			$tids[$row['tid']] = $row['tid'];
			$tagname = $row['tagname'];
		}
		if(!$tids){
			exit(header("HTTP/1.1 403 Forbidden"));
		}
		$this->title = $title = $tagname;
		$datalist = array();
		$count = count($tids);
		$pagenum = intval(phpcom::$setting['pagenum']);
		$pagestats = phpcom::$setting['pagestats'];
		$pagesize = intval(phpcom::$setting['pagesize']);
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($this->page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$tidstr = implodeids($tids);
		$sql = DB::buildlimit("SELECT t.*,c.depth,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,
			ti.attachment,ti.remote,ti.thumb,ti.preview,ti.attachimg
			FROM " . DB::table('threads') . " t
			LEFT JOIN " . DB::table('category') . " c ON c.catid=t.catid
			LEFT JOIN " . DB::table('thread_image') . " ti USING(tid)
			WHERE t.status='1' AND t.tid IN($tidstr) ORDER BY t.dateline DESC", $pagesize, $pagestart);
		$query = DB::query($sql);
		$urlargs = array('page' => $pagenow);
		$i = 0;
		while ($row = DB::fetch_array($query)) {
			$i++;
			$row['index'] = $i;
			$row['alt'] = $i % 2 == 0 ? 2 : 1;
			$row['highlight'] = $this->threadHighlight($row['highlight']);
			$row['colors'] = $row['highlight'];
			$urlargs['chanid'] = $row['chanid'];
			$urlargs['tid'] = $row['tid'];
			$urlargs['catdir'] = $row['codename'];
			$urlargs['date'] = $row['dateline'];
			$urlargs['catid'] = $row['catid'];
			if(!empty($row['prefix'])){
				$urlargs['prefix'] = trim($row['prefix']);
			}
			if(empty(phpcom::$G['channel'][$row['chanid']]['domain']) && empty($row['prefixurl'])){
				$row['domain'] = phpcom::$G['channel'][$row['chanid']]['domain'] . '/';
			}elseif(empty($row['prefixurl'])){
				$row['domain'] = $this->domain;
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
				$row['image'] = 0;
				$row['thumburl'] = $row['previewurl'] = $row['imageurl'] = $this->domain . 'misc/images/noimage.jpg';
				$row['pixurl'] = $row['url'];
			}
			if(isset($row['attached']) && $row['attached'] == 2){
				$row['pixurl'] = geturl('preview', array(
						'chanid' => $row['chanid'],
						'catdir' => $row['codename'],
						'tid' => $row['tid'],
						'page' => 1
				), $this->chandomain);
			}
			$datalist[] = $row;
		}
		
		$urlargs = array('name' => rawurlencode($tagname),'tagid' => $tagid, 'page' => '{%d}');
		$pageurl = geturl('tag', $urlargs, $this->domain);
		$firsturl = $this->formatPageUrl($pageurl);
		$showpage = $this->paging($pagenow, $pagecount, $pagesize, $count, $pageurl, $pagenum, $pagestats, 0, $firsturl);
		$currenturl = str_replace('{%d}', $pagenow, $pageurl);
		$this->checkRequestUri($currenturl);
		include template('tag');
		return 1;
	}
}
?>