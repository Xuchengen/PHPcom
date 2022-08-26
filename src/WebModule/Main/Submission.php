<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Submission.php  2012-12-14
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Main_Submission extends Controller_MainAbstract
{
	public $channel = array();
	protected $paramextra = array('type' => 'alert', 'showdialog' => TRUE, 'location' => FALSE);
	
	public function loadActionIndex()
	{
		$this->initialize();
		$doarray = array('article', 'soft', 'news', 'code');
		$do = trim($this->request->query('do', 'article'));
		$do = in_array($do, $doarray) ? $do : 'article';
		$this->title = $title = lang('common', "submission_$do");
		
		$postarticleurl = "{$this->domain}index.php?action=submission";
		$postnewsurl = "{$this->domain}index.php?action=submission&do=news";
		$postsofturl = "{$this->domain}index.php?action=submission&do=soft";
		$postcodeurl = "{$this->domain}index.php?action=submission&do=code";
		if($this->htmlstatus){
			$postarticleurl = "{$this->domain}submission.html";
			$postnewsurl = "{$this->domain}submission-news.html";
			$postsofturl = "{$this->domain}submission-soft.html";
			$postcodeurl = "{$this->domain}submission-code.html";
		}
		
		if(empty(phpcom::$setting['posted'])){
			showmessage('submission_denied', NULL, NULL, $this->paramextra);
		}
		
		$posted = intval(phpcom::$setting['posted']);
		$chanid = 1;
		if($do == "soft" || $do == "code"){
			$this->chanid = intval($this->request->query('chanid', 2));
			$chanid = $this->chanid ? $this->chanid : 2;
			$this->chanid = $chanid;
			if(isset(phpcom::$G['channel'][$chanid])){
				phpcom::$G['channelid'] = $chanid;
				$this->channel = phpcom::$G['channel'][$chanid];
			}else{
				showmessage('channel_undefined', NULL, NULL, $this->paramextra);
			}
			if (checksubmit(array('submit', 'btnsubmit', 'formsubmit'))) {
				$this->submissionSoftware($chanid);
			}else{
				$select_category = ThreadUtils::selectCategory($chanid, 0);
				include template('submission');
			}
		}else{
			$this->chanid = intval($this->request->query('chanid', 1));
			$chanid = $this->chanid ? $this->chanid : 1;
			$this->chanid = $chanid;
			if(isset(phpcom::$G['channel'][$chanid])){
				phpcom::$G['channelid'] = $chanid;
				$this->channel = &phpcom::$G['channel'][$chanid];
			}else{
				showmessage('channel_undefined', NULL, NULL, $this->paramextra);
			}
			if (checksubmit(array('submit', 'btnsubmit', 'formsubmit'))) {
				$this->submissionArticle($chanid);
			}else{
				$select_category = ThreadUtils::selectCategory($chanid, 0);
				include template('submission');
			}
		}
		
		return 1;
	}
	
	protected function submissionArticle($chanid = 1)
	{
		if(empty(phpcom::$G['uid']) && phpcom::$setting['posted'] == 2){
			showmessage('submission_free_denied', NULL, NULL, $this->paramextra);
		}
		$title = trim(strip_tags($this->request->post('title')));
		$content = trim($this->request->post('content'));
		$catid = intval($this->request->post('catid', 0));
		if (empty($title)) {
			showmessage('post_article_title_invalid', NULL, NULL, $this->paramextra);
		}
		if (empty($content)) {
			showmessage('post_content_invalid', NULL, NULL, $this->paramextra);
		}
		if (empty($catid)) {
			showmessage('post_category_invalid', NULL, NULL, $this->paramextra);
		}
		if (!$this->checkCaptcha($this->request->post('verifycode'))) {
			showmessage('captcha_verify_invalid', NULL, NULL, $this->paramextra);
		}
		$content = ThreadUtils::formatInputContents($content);
		$author = trim(strip_tags($this->request->post('author')));
		$source = trim(strip_tags($this->request->post('source')));
		$trackback = checkurlhttp(trim(strip_tags($this->request->post('trackback'))));
		$tags = trim(strip_tags($this->request->post('tags')));
		
		$thread_data = array('chanid' => $chanid, 'catid' => $catid, 'title' => $title, 'content' => $content, 'status' => 0);
		$subjects = array('author' => $author, 'source' => $source);
		$messages = array('trackback' => $trackback, 'tags' => $tags, 'keyword' => '');
		$thread_data['uid'] = phpcom::$G['uid'];
		$thread_data['author'] = empty(phpcom::$G['username']) ? 'guest' : phpcom::$G['username'];
		$thread_data['dateline'] = TIMESTAMP;
		$thread_data['imageurl'] = checkurlhttp(trim(strip_tags($this->request->post('imageurl', ''))));
		$thread_data['url'] = checkurlhttp(trim(strip_tags($this->request->post('url', ''))));
		$extras = array('subject' => $subjects, 'message' => $messages);
		$thread_data['extras'] = serialize($extras);
		DB::insert('post_contents', $thread_data);
		
		$this->paramextra['type'] = 'succeed';
		showmessage('submission_article_succeed', '/', NULL, $this->paramextra);
	}
	
	protected function submissionSoftware($chanid = 2)
	{
		if(empty(phpcom::$G['uid']) && phpcom::$setting['posted'] == 2){
			showmessage('submission_free_denied', NULL, NULL, $this->paramextra);
		}
		$title = trim(strip_tags($this->request->post('title')));
		$content = trim($this->request->post('content'));
		$catid = intval($this->request->post('catid', 0));
		if (empty($title)) {
			showmessage('post_article_title_invalid', NULL, NULL, $this->paramextra);
		}
		if (empty($content)) {
			showmessage('post_content_invalid', NULL, NULL, $this->paramextra);
		}
		if (empty($catid)) {
			showmessage('post_category_invalid', NULL, NULL, $this->paramextra);
		}
		if (!$this->checkCaptcha($this->request->post('verifycode'))) {
			showmessage('captcha_verify_invalid', NULL, NULL, $this->paramextra);
		}
		$content = ThreadUtils::formatInputContents($content);
		$runsystem = trim(strip_tags($this->request->post('runsystem')));
		$softlang = trim(strip_tags($this->request->post('softlang')));
		$softtype = trim(strip_tags($this->request->post('softtype')));
		$license = trim(strip_tags($this->request->post('license')));
		$checksum = trim(strip_tags($this->request->post('checksum')));
		$homepage = checkurlhttp(trim(strip_tags($this->request->post('homepage'))));
		$star = max(1, min(5, intval($this->request->post('star', 3))));
		$tags = trim(strip_tags($this->request->post('tags')));
		$softsize = floatval($this->request->post('softsize'));
		$sizeunit = intval($this->request->post('sizeunit'));
		if ($sizeunit === 1) {
			$softsize *= 1024;
		} elseif ($sizeunit === 2) {
			$softsize *= 1048576;
		}
		$softsize = intval($softsize);
		$testsoft = ThreadUtils::formatTestsoft(striptags($this->request->post('testsoft')));
		
		$downloads = $this->request->post('download');
		$downloadnew = array();
		$i = 0;
		foreach ($downloads as $key => $down){
			if(!empty($down['downurl'])){
				$i++;
				$downloadnew[] = array(
						'id' => 0, 'tid' => 0, 'servid' => 0,
						'downurl' => trim(strip_tags($down['downurl'])),
						'dname' => trim(strip_tags($down['dname'])),
						'index' => $i
				);
				if($i >= 5) break;
			}
		}
		
		$thread_data = array('chanid' => $chanid, 'catid' => $catid, 'title' => $title, 'content' => $content, 'status' => 0);
		$subjects = array('runsystem' => $runsystem, 'softlang' => $softlang, 'softtype' => $softtype, 'star' => $star,
				'license' => $license, 'homepage' => $homepage, 'softsize' => $softsize, 'testsoft' => $testsoft, 'checksum' => $checksum);
		$messages = array('tags' => $tags, 'keyword' => '');
		$subjects['softname'] = $title;
		
		$thread_data['topicids'] = '';
		$thread_data['uid'] = phpcom::$G['uid'];
		$thread_data['author'] = empty(phpcom::$G['username']) ? 'guest' : phpcom::$G['username'];
		$thread_data['dateline'] = TIMESTAMP;
		$thread_data['imageurl'] = checkurlhttp(trim(strip_tags($this->request->post('imageurl', ''))));
		$thread_data['url'] = checkurlhttp(trim(strip_tags($this->request->post('url', ''))));
		$extras = array('subject' => $subjects, 'message' => $messages, 'download' => $downloadnew);
		
		$thread_data['extras'] = serialize($extras);
		DB::insert('post_contents', $thread_data);
		
		$this->paramextra['type'] = 'succeed';
		showmessage('submission_software_succeed', '/', NULL, $this->paramextra);
	}
}
?>