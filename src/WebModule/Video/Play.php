<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Play.php  2012-8-20
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Video_Play extends Controller_VideoPlay
{
	public function loadActionIndex()
	{
		$id = $this->request->getQuery(2) ? $this->request->getQuery(1) : 0;
		$id = intval($this->request->query('id', $id));
		$tid = intval($this->request->query('tid', $this->request->getQuery(0)));
		$this->page = intval($this->request->query('page', $this->request->getQuery('page')));
		$condition = $id ? "id='$id'" : "tid='$tid'";
		if(!$address = DB::fetch_first("SELECT * FROM " . DB::table('video_address') . " WHERE $condition LIMIT 1")){
			$this->pageNotFound();
		}
		$tid = $address['tid'];
		$thread = $this->loadThreadView($tid);
		$chanid = $this->chanid;
		$chandomain = $this->chandomain;
		$videoaddress = $this->fetchPlayAddress(0, $chandomain, $address, $thread['codename']);
		$playaddress = &$videoaddress;
		$count = count($playaddress);
		$page = max(1, min($count, intval($this->page)));
		$player = $this->player[$address['playerid']];
		$addresses = $playaddress[$page];
		$this->title .= " " . $addresses['title'];
		
		$sql = "SELECT * FROM " . DB::table('video_thread') . " t
			LEFT JOIN " . DB::table('video_content', $this->tableindex) . " c USING(tid)
			WHERE t.tid='$tid'";
		if(!$video = DB::fetch_first($sql)){
			exit(header("HTTP/1.1 403 Forbidden"));
		}
		if ($video['summary']) {
			$this->description = htmlcharsencode(trim($video['summary']));
		}
		if ($video['keyword']) {
			$this->keyword = strip_tags(trim($video['keyword']));
		}
		$video['language'] = $video['dialogue'];
		$qualitystr = $this->getVideoQuality($video['quality']);
		$video['content'] = preg_replace("/\[attach\](\d+)\[\/attach\]/i", '', $video['content']);
		$video['content'] = bbcode::bbcode2html($video['content']);
		
		$urlargs = array('chanid' => $this->chanid, 'catdir' => $thread['codename'], 'tid' => $thread['tid'],
				'date' => $thread['dateline'], 'catid' => $thread['catid'], 'page' => '{%d}');
		
		$urlargs['id'] = $address['id'];
		$video['pageurl'] = geturl('play', $urlargs, $chandomain);
		$video['purl'] = str_replace('{%d}', $page, $video['pageurl']);
		$this->htmlFile = substr($video['purl'], strlen($chandomain));
		$currenturl = $chandomain . $this->htmlFile;
		$this->checkRequestUri($currenturl);
		unset($urlargs['id']);
		$urlargs['prefix'] = empty($thread['prefix']) ? '' : trim($thread['prefix']);
		$urlargs['name'] = empty($thread['htmlname']) ? $tid : trim($thread['htmlname']);
		$urlargs['page'] = 1;
		$this->baseUrlArgs = $urlargs;
		$this->baseUrlArgs['name'] = $thread['codename'];
		$video['url'] = geturl('threadview', $urlargs, $chandomain);
		if (isset($video['caturl']) && $video['caturl']) {
			$video['curl'] = $video['caturl'];
		}else{
			$urlargs['name'] = $thread['codename'];
			if(!empty($thread['prefixurl']) && $thread['basic']){
				$video['curl'] = $thread['prefixurl'];
			}else{
				$video['curl'] = geturl($thread['basic'] ? 'category' : 'threadlist', $urlargs, $chandomain);
			}
		}
		$urlargs['tid'] = $video['tid'];
		unset($urlargs['chanid']);
		$video['commenturl'] = geturl('comment', $urlargs, $this->domain);
		
		@extract($video + $thread, EXTR_SKIP);

		$tplname = checktplname('video/play', 'video/play_' . $player['name']);
		$tplname = checktplname($tplname, $this->chanid);
		include template($tplname);
		return 1;
	}
}
?>