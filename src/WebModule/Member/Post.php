<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Post.php  2012-12-13
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Post extends Controller_MemberAbstract
{
	public $channel = array();
	public $currents = array('article' => '', 'soft' => '', 'photo' => '', 'video' => '');
	public $postid = 0;
	protected $paramextra = array('type' => 'alert', 'showdialog' => TRUE, 'location' => FALSE);
	
	public function loadActionIndex()
	{
		$this->title = lang('member', 'member_post');
		$doarray = array('article', 'soft', 'photo', 'video');
		$do = trim($this->request->query('do', 'article'));
		$do = in_array($do, $doarray) ? $do : 'article';
		$tid = intval($this->request->query('tid', 0));
		$postid = intval($this->request->query('postid', 0));
		
		if($tid && empty(phpcom::$G['group']['edit']) && empty(phpcom::$G['member']['adminid'])){
			showmessage('post_edit_thread_denied', NULL, NULL, $this->paramextra);
		}
		$thread = array('tid' => 0, 'chanid' => 0, 'rootid' => 0, 'catid' => 0, 'title' => '', 'imageurl' => '',
				'polled' => 0, 'attached' => 0, 'tableindex' => 0, 'uid' => 0, 'status' => 0);
		if($tid && (phpcom::$G['group']['edit'] || phpcom::$G['member']['adminid'])){
			if($thread = DB::fetch_first("SELECT tid, catid, title, uid, polled, attached, tableindex FROM " . DB::table('threads') . " WHERE tid='$tid'")){
				if($thread['uid'] != phpcom::$G['uid'] && empty(phpcom::$G['member']['adminid'])){
					showmessage('post_edit_thread_denied', NULL, NULL, $this->paramextra);
				}
			}else{
				showmessage('threads_undefined', NULL, NULL, $this->paramextra);
			}
		}elseif($postid){
			$uid = intval(phpcom::$G['uid']);
			if($thread = DB::fetch_first("SELECT * FROM " . DB::table('post_contents') . " WHERE status='0' AND postid='$postid'")){
				if($thread['uid'] != phpcom::$G['uid'] && empty(phpcom::$G['member']['adminid'])){
					showmessage('post_edit_thread_denied', NULL, NULL, $this->paramextra);
				}
			}else{
				showmessage('submission_edit_denied', NULL, NULL, $this->paramextra);
			}
		}
		$this->postid = $postid;
		if($do == "soft"){
			$this->chanid = intval($this->request->query('chanid', 2));
			if(isset(phpcom::$G['channel'][$this->chanid])){
				phpcom::$G['channelid'] = $this->chanid;
				$this->channel = &phpcom::$G['channel'][$this->chanid];
				$this->postSoft($tid, $thread);
			}else{
				showmessage('channel_undefined', NULL, NULL, $this->paramextra);
			}
		}elseif($do == 'photo'){
			showmessage('post_thread_denied', NULL, NULL, $this->paramextra);
			$this->chanid = intval($this->request->query('chanid', 3));
			if(isset(phpcom::$G['channel'][$this->chanid])){
				phpcom::$G['channelid'] = $this->chanid;
				$this->channel = &phpcom::$G['channel'][$this->chanid];
				$this->postPhoto($tid, $thread);
			}else{
				showmessage('channel_undefined', NULL, NULL, $this->paramextra);
			}
		}elseif($do == 'video'){
			showmessage('post_thread_denied', NULL, NULL, $this->paramextra);
			$this->chanid = intval($this->request->query('chanid', 5));
			if(isset(phpcom::$G['channel'][$this->chanid])){
				phpcom::$G['channelid'] = $this->chanid;
				$this->channel = &phpcom::$G['channel'][$this->chanid];
				$this->postVideo($tid, $thread);
			}else{
				showmessage('channel_undefined', NULL, NULL, $this->paramextra);
			}
		}else{
			$this->chanid = intval($this->request->query('chanid', 1));
			if(isset(phpcom::$G['channel'][$this->chanid])){
				phpcom::$G['channelid'] = $this->chanid;
				$this->channel = &phpcom::$G['channel'][$this->chanid];
				$this->postArticle($tid, $thread);
			}else{
				showmessage('channel_undefined', NULL, NULL, $this->paramextra);
			}
		}
		return 1;
	}
	
	protected function postArticle($tid, $thread)
	{
		$do = 'article';
		$title = lang('member', 'member_post_article');
		$this->currents['article'] = ' class="current"';
		$chanid = $this->chanid;
		$postid = $this->postid;
		$catid = intval($this->request->post('catid', 0));
		
		if (checksubmit(array('submit', 'btnsubmit', 'formsubmit'))) {
			if (empty(phpcom::$G['group']['article'])) {
				showmessage('post_article_denied', NULL, NULL, $this->paramextra);
			}
			$title = trim(strip_tags($this->request->post('title')));
			$content = trim($this->request->post('content'));
			
			if (empty($title)) {
				showmessage('post_article_title_invalid', NULL, NULL, $this->paramextra);
			}
			if (empty($content)) {
				showmessage('post_content_invalid', NULL, NULL, $this->paramextra);
			}
			if (empty($catid)) {
				showmessage('post_category_invalid', NULL, NULL, $this->paramextra);
			}
			if (!empty(phpcom::$setting['captchastatus'][3])) {
				if (!$this->checkCaptcha($this->request->post('verifycode'))) {
					showmessage('captcha_verify_invalid', NULL, NULL, $this->paramextra);
				}
			}
			$content = ThreadUtils::formatInputContents($content);
			$author = trim(strip_tags($this->request->post('author')));
			$source = trim(strip_tags($this->request->post('source')));
			$trackback = checkurlhttp(trim(strip_tags($this->request->post('trackback'))));
			$tags = trim(strip_tags($this->request->post('tags')));
			
			$thread_data = array('chanid' => $chanid, 'catid' => $catid, 'title' => $title, 'status' => 1);
			$subjects = array('author' => $author, 'source' => $source);
			$messages = array('content' => $content, 'trackback' => $trackback, 'tags' => $tags);
			
			if (!empty(phpcom::$G['group']['postnoaudit']) && empty($postid)) {
				$post = new DataAccess_PostThread($chanid);
				if($tid){
					if(!$post->update($tid, $thread_data, array(), $subjects, $messages)){
						showmessage('post_category_invalid', NULL, NULL, $this->paramextra);
					}
				}else{
					$thread = array();
					if($tid = $post->insert($thread, $thread_data, array(), $subjects, $messages)){
						update_memberlastpost('threads');
					}else{
						showmessage('post_category_invalid', NULL, NULL, $this->paramextra);
					}
				}
			}elseif (!empty(phpcom::$G['group']['edit']) && empty($postid) && $tid) {
				$post = new DataAccess_PostThread($chanid);
				$thread_data['status'] = 0;
				if(!$post->update($tid, $thread_data, array(), $subjects, $messages)){
					showmessage('post_category_invalid', NULL, NULL, $this->paramextra);
				}
				$this->paramextra['type'] = 'succeed';
				showmessage('post_edit_thread_audit', 'member.php?action=thread', NULL, $this->paramextra);
			}elseif(empty($tid)){
				unset($thread_data['chanid']);
				
				$thread_data['chanid'] = $chanid;
				$thread_data['uid'] = phpcom::$G['uid'];
				$thread_data['author'] = empty(phpcom::$G['username']) ? 'guest' : phpcom::$G['username'];
				$thread_data['dateline'] = TIMESTAMP;
				$thread_data['status'] = 0;
				$thread_data['content'] = $content;
				$thread_data['imageurl'] = checkurlhttp(trim(strip_tags($this->request->post('imageurl'))));
				$thread_data['url'] = checkurlhttp(trim(strip_tags($this->request->post('url'))));
				$extras = array('subject' => $subjects, 'message' => array('trackback' => $trackback, 'tags' => $tags));
				$thread_data['extras'] = serialize($extras);
				
				if(empty($postid)){
					DB::insert('post_contents', $thread_data);
				}else{
					DB::update('post_contents', $thread_data, "postid='$postid'");
				}
				$this->paramextra['type'] = 'succeed';
				showmessage('post_thread_succeed', 'member.php?action=submission', NULL, $this->paramextra);
			}else{
				showmessage('post_edit_thread_denied', NULL, NULL, $this->paramextra);
			}
			$this->paramextra['type'] = 'succeed';
			showmessage('post_thread_succeed', 'member.php?action=thread', NULL, $this->paramextra);
		}else{
			$downloads = array();
			$select_category = ThreadUtils::selectCategory($chanid, $thread['catid']);
			$messages = array('subtitle' => '', 'summary' => '', 'author' => '', 'source' => '',
					'keyword' => '', 'content' => '', 'trackback' => '', 'tags' => '');
			if($tid){
				$tableindex = $thread['tableindex'];
				
				$messages = DB::fetch_first("SELECT t1.subtitle,t1.summary,t1.author,t1.source, t2.keyword,t2.content,t2.trackback,t2.tags
						FROM " . DB::table('article_thread') . " t1
						LEFT JOIN " . DB::table('article_content', $tableindex) . " t2 USING(tid)
						WHERE t1.tid=$tid");
				$messages['tags'] = ThreadUtils::getTagstr($messages['tags']);
			}elseif(empty($tid) && $postid){
				if(!empty($thread['extras']) && ($extras = @unserialize($thread['extras']))){
					$messages = $extras['subject'] + $extras['message'];
				}
			}
			$thread = array_merge($thread, $messages);
			if(!isset($thread['imageurl'])) $thread['imageurl'] = '';
			
			include template('member/post');
		}
		return 1;
	}
	
	protected function postSoft($tid, $thread)
	{
		$do = 'soft';
		$title = lang('member', 'member_post_soft');
		$this->currents['soft'] = ' class="current"';
		$chanid = $this->chanid;
		$postid = $this->postid;
		$catid = intval($this->request->post('catid', 0));
		
		if (checksubmit(array('submit', 'btnsubmit', 'formsubmit'))) {
			if (empty(phpcom::$G['group']['softwore'])) {
				showmessage('post_soft_denied', NULL, NULL, $this->paramextra);
			}
			
			$title = trim(strip_tags($this->request->post('title')));
			$content = trim($this->request->post('content'));
			
			if (empty($title)) {
				showmessage('post_soft_title_invalid', NULL, NULL, $this->paramextra);
			}
			if (empty($content)) {
				showmessage('post_content_invalid', NULL, NULL, $this->paramextra);
			}
			if (empty($catid)) {
				showmessage('post_category_invalid', NULL, NULL, $this->paramextra);
			}
			if (!empty(phpcom::$setting['captchastatus'][3])) {
				if (!$this->checkCaptcha($this->request->post('verifycode'))) {
					showmessage('captcha_verify_invalid', NULL, NULL, $this->paramextra);
				}
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
			
			$thread_data = array('chanid' => $chanid, 'catid' => $catid, 'title' => $title, 'status' => 1);
			$subjects = array('runsystem' => $runsystem, 'softlang' => $softlang, 'softtype' => $softtype, 'star' => $star,
					'license' => $license, 'homepage' => $homepage, 'softsize' => $softsize, 'testsoft' => $testsoft);
			$subjects['softname'] = $title;
			$subjects['softversion'] = '';
			if(!empty($checksum)){
				$subjects['checksum'] = $checksum;
			}
			$messages = array('content' => $content, 'tags' => $tags);
			
			$downloads = $this->request->post('download');
			if (!empty(phpcom::$G['group']['postnoaudit']) && empty($postid)) {
				$post = new DataAccess_PostThread($chanid);
				$thread = array('softid' => 0);
				if($tid){
					if(!($thread = $post->update($tid, $thread_data, array(), $subjects, $messages))){
						showmessage('post_category_invalid', NULL, NULL, $this->paramextra);
					}
				}else{
					if($tid = $post->insert($thread, $thread_data, array(), $subjects, $messages)){
						update_memberlastpost('threads');
					}else{
						showmessage('post_category_invalid', NULL, NULL, $this->paramextra);
					}
				}
				
				if($softid = $thread['softid']){
					$i = 0;
					foreach ($downloads as $key => $down){
						if(!empty($down['downurl']) && ($downurl = trim(strip_tags($down['downurl'])))){
							$i++;
							$data = array(
							'dname' => trim(strip_tags($down['dname'])),
							'downurl' => trim($downurl)
							);
							if(isset($down['servid']) && !empty($down['servid'])){
								$data['servid'] = intval($down['servid']);
							}
							if(empty($down['id'])){
								$data['softid'] = $softid;
								$data['tid'] = $tid;
								DB::insert('soft_download', $data);
							}else{
								$id = intval($down['id']);
								if($tmp = DB::fetch_first("SELECT id FROM " . DB::table('soft_download') . " WHERE tid='$tid' AND id='$id'")){
									DB::update('soft_download', $data, array('id' => $id));
								}
							}
							
							if($i >= 10) break;
						}
					}
				}
			}elseif (!empty(phpcom::$G['group']['edit']) && empty($postid) && $tid) {
				$post = new DataAccess_PostThread($chanid);
				$thread_data['status'] = 0;
				if(!$post->update($tid, $thread_data, array(), $subjects, $messages)){
					showmessage('post_category_invalid', NULL, NULL, $this->paramextra);
				}
				$this->paramextra['type'] = 'succeed';
				showmessage('post_edit_thread_audit', 'member.php?action=thread', NULL, $this->paramextra);
			}elseif(empty($tid)){
				unset($thread_data['chanid']);
				
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
				
				$thread_data['chanid'] = $chanid;
				$thread_data['uid'] = phpcom::$G['uid'];
				$thread_data['author'] = empty(phpcom::$G['username']) ? 'guest' : phpcom::$G['username'];
				$thread_data['dateline'] = TIMESTAMP;
				$thread_data['status'] = 0;
				$thread_data['content'] = $content;
				$thread_data['imageurl'] = checkurlhttp(trim(strip_tags($this->request->post('imageurl'))));
				$thread_data['url'] = checkurlhttp(trim(strip_tags($this->request->post('url'))));
				$extras = array('subject' => $subjects, 'message' => array('tags' => $tags), 'download' => $downloadnew);
				$thread_data['extras'] = serialize($extras);
				
				if(empty($postid)){
					DB::insert('post_contents', $thread_data);
				}else{
					DB::update('post_contents', $thread_data, "postid='$postid'");
				}
				$this->paramextra['type'] = 'succeed';
				showmessage('post_thread_succeed', 'member.php?action=submission', NULL, $this->paramextra);
			}else{
				showmessage('post_edit_thread_denied', NULL, NULL, $this->paramextra);
			}
			$this->paramextra['type'] = 'succeed';
			showmessage('post_thread_succeed', 'member.php?action=thread', NULL, $this->paramextra);
		}else{
			$select_category = ThreadUtils::selectCategory($chanid, $thread['catid']);
			$download_caption = lang('common', 'download_caption');
			$download_now = lang('common', 'download_now');
			$downloads = array(
					array('id' => 0, 'tid' => 0, 'servid' => 0, 'downurl' => '', 'dname' => $download_now, 'index' => 1),
					array('id' => 0, 'tid' => 0, 'servid' => 0, 'downurl' => '', 'dname' => $download_now, 'index' => 2)
					);
			$messages = array('softname' => '', 'softversion' => '', 'subtitle' => '', 'summary' => '', 'softlang' => '',
					'softtype' => '', 'runsystem' => 'WinXP, Win7, Win8', 'license' => '', 'homepage' => '', 'contact' => '', 'star' => 3,
					'softsize' => 0, 'md5sums' => '','checksum' => '', 'testsoft' => '', 'keyword' => '', 'content' => '', 'tags' => '');
			if($tid){
				$tableindex = $thread['tableindex'];
				
				$messages = DB::fetch_first("SELECT t1.softname,t1.softversion,t1.subtitle,t1.summary,t1.softlang,t1.softtype,
						t1.runsystem,t1.license,t1.homepage,t1.contact,t1.star,t1.softsize,t1.checksum,t1.checksum as md5sums,t1.testsoft,
						t2.keyword,t2.content,t2.tags
						FROM " . DB::table('soft_thread') . " t1
						LEFT JOIN " . DB::table('soft_content', $tableindex) . " t2 USING(tid)
						WHERE t1.tid=$tid");
				$messages['tags'] = ThreadUtils::getTagstr($messages['tags']);
				if($tmp = ThreadUtils::getDownloads($tid)){
					$downloads = $tmp;
					$index = count($downloads) + 1;
					$downloads[] = array('id' => 0, 'tid' => 0, 'servid' => 0, 'downurl' => '', 'dname' => $download_now, 'index' => $index);
				}
			}elseif(empty($tid) && $postid){
				if(!empty($thread['extras']) && ($extras = @unserialize($thread['extras']))){
					$messages = $extras['subject'] + $extras['message'];
					if(isset($extras['download']) && !empty($extras['download'])){
						$downloads = $extras['download'];
						$index = count($downloads) + 1;
						$downloads[] = array('id' => 0, 'tid' => 0, 'servid' => 0, 'downurl' => '', 'dname' => $download_now, 'index' => $index);
					}
				}
			}
			$thread = array_merge($thread, $messages);
			if(!isset($thread['imageurl'])) $thread['imageurl'] = '';
			
			include template('member/post');
		}
		return 1;
	}
	
	protected function postPhoto($tid, $thread)
	{
		$do = 'photo';
		$title = lang('member', 'member_post_photo');
		$this->currents['photo'] = ' class="current"';
		$chanid = $this->chanid;
		$postid = $this->postid;
		$catid = intval($this->request->post('catid', 0));
		
		if (checksubmit(array('submit', 'btnsubmit', 'formsubmit'))) {
			if (empty(phpcom::$G['group']['photo'])) {
				showmessage('post_photo_denied', NULL, NULL, $this->paramextra);
			}
			$title = trim(strip_tags($this->request->post('title')));
			$content = trim($this->request->post('content'));
			
			if (empty($title)) {
				showmessage('post_photo_title_invalid', NULL, NULL, $this->paramextra);
			}
			if (empty($content)) {
				showmessage('post_content_invalid', NULL, NULL, $this->paramextra);
			}
			if (empty($catid)) {
				showmessage('post_category_invalid', NULL, NULL, $this->paramextra);
			}
			if (!empty(phpcom::$setting['captchastatus'][3])) {
				if (!$this->checkCaptcha($this->request->post('verifycode'))) {
					showmessage('captcha_verify_invalid', NULL, NULL, $this->paramextra);
				}
			}
		}else{
			$downloads = array();
			$select_category = ThreadUtils::selectCategory($chanid, $catid);
			include template('member/post');
		}
		return 1;
	}
	
	protected function postVideo($tid, $thread)
	{
		$do = 'video';
		$title = lang('member', 'member_post_video');
		$this->currents['photo'] = ' class="current"';
		$chanid = $this->chanid;
		$postid = $this->postid;
		$catid = intval($this->request->post('catid', 0));
	
		if (checksubmit(array('submit', 'btnsubmit', 'formsubmit'))) {
			if (empty(phpcom::$G['group']['video'])) {
				showmessage('post_video_denied', NULL, NULL, $this->paramextra);
			}
			$title = trim(strip_tags($this->request->post('title')));
			$content = trim($this->request->post('content'));
			
			if (empty($title)) {
				showmessage('post_video_title_invalid', NULL, NULL, $this->paramextra);
			}
			if (empty($content)) {
				showmessage('post_content_invalid', NULL, NULL, $this->paramextra);
			}
			if (empty($catid)) {
				showmessage('post_category_invalid', NULL, NULL, $this->paramextra);
			}
			if (!empty(phpcom::$setting['captchastatus'][3])) {
				if (!$this->checkCaptcha($this->request->post('verifycode'))) {
					showmessage('captcha_verify_invalid', NULL, NULL, $this->paramextra);
				}
			}
		}else{
			$downloads = array();
			$select_category = ThreadUtils::selectCategory($chanid, $catid);
			include template('member/post');
		}
		return 1;
	}
}
?>