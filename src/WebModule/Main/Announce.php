<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Announce.php  2012-10-7
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Main_Announce extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$this->initialize();
		$this->page = intval($this->request->query('page', $this->request->getQuery(0)));
		$announceid = intval($this->request->query('aid', $this->request->getQuery(0)));
		if(!$announce = DB::fetch_first("SELECT aid,title,content,author,dateline,type,hits FROM " . DB::table('announce') . " WHERE aid='$announceid'")){
			$this->pageNotFound();
		}
		$aid = $announce['aid'];
		DB::query("UPDATE " . DB::table('announce') . " SET hits=hits+1 WHERE aid='$aid'");
		
		$dateline = fmdate($announce['dateline'], 'dt', 'u');
		$this->title = $title = htmlcharsencode($announce['title']);
		$content = bbcode::bbcode2html($announce['content']);
		$hits = $announce['hits'];
		include template('announce');
		return 1;
	}
}
?>