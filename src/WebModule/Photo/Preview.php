<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Preview.php  2012-8-15
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Photo_Preview extends Controller_Preview
{

	public function loadActionIndex()
	{
		$thread = $this->loadPreviewData('photo');
		$tid = $this->tid;
		$chanid = $this->chanid;
		$dateline = $this->dateline;
		$attached = $this->attached;
		$title = trim($thread['title']);
		$catname = trim($thread['catname']);
		$commentnum = isset($thread['comments']) ? $thread['comments'] : 0;
		$urlargs = array('chanid' => $this->chanid, 'catdir' => $thread['codename'], 'tid' => $tid,
				'date' => $thread['dateline'], 'catid' => $thread['catid'], 'page' => 1);
		$chandomain = $this->chandomain;
		$urlargs['prefix'] = empty($thread['prefix']) ? '' : trim($thread['prefix']);
		$urlargs['name'] = empty($thread['htmlname']) ? $tid : trim($thread['htmlname']);
		$url = geturl('threadview', $urlargs, $chandomain);
		$urlargs['name'] = $thread['codename'];
		if(!empty($thread['prefixurl']) && $thread['basic']){
			$curl = $thread['prefixurl'];
		}else{
			$curl = geturl($thread['basic'] ? 'category' : 'threadlist', $urlargs, $chandomain);
		}
		$urlargs['page'] = '{%d}';
		$pageurl = geturl('preview', $urlargs, $chandomain);
		$currenturl = str_replace('{%d}', 1, $pageurl);
		unset($urlargs['chanid']);
		$urlargs['page'] = 1;
		$commenturl = geturl('comment', $urlargs);
		$previmage = $this->prevImages();
		$nextimage = $this->nextImages();
		$datalist = $this->getAttachList($tid, 'photo');
		$count = count($datalist);
		$pagenow = $page = intval($this->request->query('page', $this->request->getQuery('page')));
		$showpage = '';
		$nexturl = $nextimage['url'];
		$prevurl = $previmage['url'];
		if(phpcom::$G['channel'][$chanid]['previewpage'] == 1){
			$pagenow = $page = max(1, $pagenow);
		}
		if(empty(phpcom::$G['channel'][$chanid]['previewpage'])){
			$pagenow = $page = 0;
		}
		if($page > 0 && phpcom::$G['channel'][$chanid]['previewpage']){
			$pagenow = max(1, min($count, $page));
			foreach ($datalist as $k => $v){
				if($k != $pagenow){
					unset($datalist[$k]);
				}
			}
			
			$nexturl = $pagenow == $count ? $nextimage['url'] : str_replace('{%d}', min($count, $pagenow + 1), $pageurl);
			$prevurl = $pagenow == 1 ? $previmage['url'] : str_replace('{%d}', max(1, $pagenow - 1), $pageurl);

			$firsturl = $this->formatPageUrl($pageurl);
			$showpage = $this->paging($pagenow, $count, 1, $count, $pageurl, 10, 0, 0, $firsturl);
			
			$currenturl = $pagenow > 1 ? str_replace('{%d}',$pagenow, $pageurl) : str_replace('{%d}',$pagenow, $firsturl);
		}
		$this->checkRequestUri($currenturl);
		
		$tplname = 'photo/preview';
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