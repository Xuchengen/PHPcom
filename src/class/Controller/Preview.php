<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Preview.php  2012-8-7
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Controller_Preview extends Controller_MainAbstract
{
	protected $tid = 0;
	protected $attached = 0;
	protected $templateName;
	protected $catname;
	protected $dateline;
	protected $thread;

	public function __construct(Web_HttpRequest $request)
	{
		parent::__construct($request);
		$this->iscaptcha = isset(phpcom::$setting['captchastatus'][4]) ? intval(phpcom::$setting['captchastatus'][4]) : 0;
		$this->page = intval($this->request->query('page', $this->request->getQuery('page')));
	}

	public function loadPreviewData($module = null)
	{
		$tid = intval($this->request->query('tid', $this->request->getQuery(0)));
		$table2 = $field2 = "";
		if(!empty($module)){
			$table2 = "LEFT JOIN " . DB::table($module . '_thread') . " t2 USING(tid) ";
			$field2 = ",t2.* ";
		}
		$sql = "SELECT t.*,c.depth,c.basic,c.parentid,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl,
			c.remote,c.imageurl,c.banner,c.template $field2 
			FROM " . DB::table('threads') . " t 
			LEFT JOIN " . DB::table('category') . " c USING(catid)  $table2 
			WHERE t.status='1' AND t.tid='$tid'";
		if(!$thread = DB::fetch_first($sql)){
			$this->pageNotFound();
		}
		if($thread['attached'] != 2) $this->pageNotFound();
		$this->tid = $thread['tid'];
		$this->chanid = $thread['chanid'];
		phpcom::$G['channelid'] = $this->chanid;
		phpcom::$G['cache']['channel'] = phpcom::$G['channel'][$this->chanid];
		$this->title = htmlcharsencode($thread['title']);
		$this->rootid = $thread['rootid'];
		$this->catid = $thread['catid'];
		$this->parentid = $thread['parentid'];
		if(!empty($thread['template'])){
			$this->templateName = phpcom::$G['cache']['channel']['modules'] . '/' . $thread['template'] . '_preview';
		}
		$thread['date'] = fmdate($thread['dateline'], 'Y-m-d');
		$this->catname = trim($thread['catname']);
		$this->dateline = fmdate($thread['dateline']);
		$this->attached = $thread['attached'];
		$this->channelname = phpcom::$G['cache']['channel']['channelname'];
		$this->keyword = phpcom::$G['cache']['channel']['keyword'] ? phpcom::$G['cache']['channel']['keyword'] : phpcom::$setting['keyword'];
		$this->keyword = $this->keyword ? $this->keyword : $this->title;
		if (!empty($thread['summary'])) {
			$this->description = htmlcharsencode(trim($thread['summary']));
		}else{
			$this->description = phpcom::$G['cache']['channel']['description'] ? phpcom::$G['cache']['channel']['description'] : phpcom::$setting['description'];
		}
		$this->description = $this->description ? $this->description : $this->title;
		$thread['module'] = phpcom::$G['cache']['channel']['modules'];
		
		if (phpcom::$G['cache']['channel']['domain'] || !empty($thread['prefixurl'])) {
			!defined('DOMAIN_ENABLED') && define('DOMAIN_ENABLED', TRUE);
		}
		if (trim($thread['prefixurl'])) {
			$this->chandomain = trim($thread['prefixurl'], ' /') . '/';
		}elseif (trim(phpcom::$G['cache']['channel']['domain'])) {
			$this->chandomain = trim(phpcom::$G['cache']['channel']['domain'], ' /') . '/';
		}
		$this->initialize();
		if(!empty(phpcom::$G['cache']['channel']['sitename'])){
			$this->webname = phpcom::$G['cache']['channel']['sitename'];
		}
		return $thread;
	}

	public static function &getInstance($request)
	{
		static $_instance = null;
		if (empty($_instance)) {
			$_instance = new Controller_Preview($request);
		}
		return $_instance;
	}
	
	public function getAttachList($tid, $module = 'article', $image = 1, $chanid = 0, $domain = '')
	{
		static $moduleArray = array('article', 'soft', 'photo', 'video');
		$module = in_array($module, $moduleArray) ? $module : 'article';
		$domain = $domain ? $domain : $this->chandomain;
		$chanid = $chanid ? $chanid : $this->chanid;

		$data = array();
		$i = 0;
		$urlargs = array('chanid' => $chanid, 'tid' => $tid, 'page' => 1);
		$query = DB::query("SELECT attachid, tid, sortord, attachment, description, dateline, thumb, preview, remote
				FROM " . DB::table("attachment_$module") . " 
				WHERE tid='$tid' AND image='$image' ORDER BY sortord");
		$count = DB::num_rows($query);
		while ($attach = DB::fetch_array($query)) {
			++$i;
			$attach['i'] = $i;
			$attach['count'] = $count;
			$attach['index'] = str_pad($i, 2 , '0', STR_PAD_LEFT);
			$urlargs['aid'] = $attach['attachid'];
			if($attach['remote']){
				$attach['attachurl'] = phpcom::$setting['ftp']['attachurl'] . $module . '/';
			}else{
				$attach['attachurl'] = $this->attachurl . $module . '/';
			}
			$attach['date'] = fmdate($attach['dateline']);
			$attach['imageurl'] = $attach['attachurl'] . $attach['attachment'];
			//$attach['previewurl'] = $attach['preview'] ? generatethumbname($attach['imageurl'], '_small.jpg') : $attach['imageurl'];
			$attach['thumburl'] = $attach['thumb'] ? generatethumbname($attach['imageurl']) : $attach['imageurl'];
			$urlargs['page'] = '{%d}';
			$pageurl = geturl('preview', $urlargs, $domain);
			$firsturl = $this->formatPageUrl($pageurl);
			if($i == 1){
				$attach['url'] = str_replace('{%d}', 1, $firsturl);
			}else{
				$attach['url'] = str_replace('{%d}', $i, $pageurl);
			}
			if(min($count, $i + 1) == 1){
				$attach['nexturl'] = str_replace('{%d}', 1, $firsturl);
			}else{
				$attach['nexturl'] = str_replace('{%d}', min($count, $i + 1), $pageurl);
			}
			if(max(1, $i - 1) == 1){
				$attach['prevurl'] = str_replace('{%d}', 1, $firsturl);
			}else{
				$attach['prevurl'] = str_replace('{%d}', max(1, $i -1), $pageurl);
			}
			$data[$i] = $attach;
		}
		return $data;
	}

	public function nextImages($tid = 0, $chanid = 0)
	{
		$tid = $tid ? intval($tid) : $this->tid;
		$chanid = $chanid ? intval($chanid) : $this->chanid;
		$images = array('title' => '', 'url' => '', 'previewurl' => '', 'thumburl' => '', 'imageurl' => '');
		$parse = parse_url(phpcom::$setting['attachurl']);
		$attachurl = !isset($parse['host']) ? $this->domain . phpcom::$setting['attachurl'] : phpcom::$setting['attachurl'];
		$chancond = $chanid ? " AND t.chanid='$chanid' " : '';
		$sql = "SELECT ti.*,t.chanid, t.catid, t.title, c.basic, c.codename, c.prefixurl, c.prefix FROM " . DB::table('thread_image') . " ti
	 	 LEFT JOIN " . DB::table('threads') . " t USING(tid)
	 	 LEFT JOIN " . DB::table('category') . " c USING(catid)
		 WHERE t.status='1'$chancond AND t.attached='2' AND ti.tid<'$tid' ORDER BY ti.tid DESC LIMIT 0,1";
		if($image = DB::fetch_first($sql)){
			$chanid = $image['chanid'];
			$tid = $image['tid'];
			$module = phpcom::$G['channel'][$chanid]['modules'];
			$domain = $this->domain;
			if (!empty($image['prefixurl'])) {
				$domain = trim($image['prefixurl'], ' /') . '/';
			}elseif(!empty(phpcom::$G['channel'][$chanid]['domain'])) {
				$domain = trim(phpcom::$G['channel'][$chanid]['domain'], ' /') . '/';
			}
			$this->processImageRowData($image, $module);
			
			$images['title'] = $image['title'];
			
			$images['url'] = geturl('preview', array('chanid' => $chanid, 'tid' => $tid, 'page' => 1), $domain);
		}
		return $images;
	}

	public function prevImages($tid = 0, $chanid = 0)
	{
		$tid = $tid ? intval($tid) : $this->tid;
		$chanid = $chanid ? intval($chanid) : $this->chanid;
		$images = array('title' => '', 'url' => '', 'previewurl' => '', 'thumburl' => '', 'imageurl' => '');
		$parse = parse_url(phpcom::$setting['attachurl']);
		$attachurl = !isset($parse['host']) ? $this->domain . phpcom::$setting['attachurl'] : phpcom::$setting['attachurl'];
		$chancond = $chanid ? " AND t.chanid='$chanid' " : '';
		$sql = "SELECT ti.*,t.chanid, t.catid, t.title, c.basic, c.codename, c.prefixurl, c.prefix FROM " . DB::table('thread_image') . " ti
	 	 LEFT JOIN " . DB::table('threads') . " t USING(tid)
	 	 LEFT JOIN " . DB::table('category') . " c USING(catid)
		 WHERE t.status='1'$chancond AND t.attached='2' AND ti.tid>'$tid' ORDER BY ti.tid ASC LIMIT 0,1";
		if($image = DB::fetch_first($sql)){
			$chanid = $image['chanid'];
			$tid = $image['tid'];
			$module = phpcom::$G['channel'][$chanid]['modules'];
			$domain = $this->domain;
			if (!empty($image['prefixurl'])) {
				$domain = trim($image['prefixurl'], ' /') . '/';
			}elseif(!empty(phpcom::$G['channel'][$chanid]['domain'])) {
				$domain = trim(phpcom::$G['channel'][$chanid]['domain'], ' /') . '/';
			}
			
			$this->processImageRowData($image, $module);
			
			$images['title'] = $image['title'];
			
			$images['url'] = geturl('preview', array('chanid' => $chanid, 'tid' => $tid, 'page' => 1), $domain);
		}
		return $images;
	}
	
	protected function threadComments($tid, $type = '', $limit = 10, &$commentids = array())
	{
		$limit = $limit ? $limit : 10;
		$condition = $type;
		if (empty($type)) {
			$condition = 'ORDER BY t1.lastdate DESC';
		} elseif ($type == 1) {
			$condition = 'ORDER BY t1.commentid ASC';
		}
		$data = array();
		$sql = DB::buildlimit("SELECT t1.*, t2.* FROM " . DB::table('comments') . " t1
			INNER JOIN " . DB::table('comment_body') . " t2 ON t2.commentid=t1.commentid
			WHERE t1.tid='$tid' AND t2.first='1' AND t2.status='1' $condition", $limit, 0);
		$query = DB::query($sql);
		$i = 0;
		while ($row = DB::fetch_array($query)) {
			$i++;
			$row['index'] = $i;
			$row['alt'] = $i % 2;
			$row['content'] = bbcode::output($row['content']);
			$row['date'] = fmdate($row['lastdate']);
			$row['datetime'] = fmdate($row['dateline']);
			if ($row['username'] == 'guest') {
				$row['username'] = lang('common', 'guest');
			}
			if ($row['author'] == 'guest') {
				$row['author'] = lang('common', 'guest');
			}
			$row['id'] = $row['bodyid'];
			$data[$row['commentid']] = $row;
			$commentids[] = $row['commentid'];
		}
		return $data;
	}
	
	protected function threadCommentReply($cids) {
		$data = array();
		if($replyids = implodeids($cids)){
			$sql = "SELECT * FROM " . DB::table('comment_body') . "
			WHERE first='0' AND status='1' AND commentid IN($replyids) ORDER BY bodyid ASC";
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				$row['content'] = bbcode::output($row['content']);
				$row['date'] = fmdate($row['dateline']);
				if ($row['author'] == 'guest') {
					$row['author'] = lang('common', 'guest');
				}
				$row['id'] = $row['bodyid'];
				$data[$row['commentid']][$row['bodyid']] = $row;
			}
		}
		return $data;
	}
}
?>