<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Comment.php  2012-8-5
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Main_Comment extends Controller_MainAbstract
{
	protected $thread = array(), $channel = array();
	protected $paramextra = array('type' => 'alert', 'showdialog' => true, 'location' => false);
	protected $tid = 0, $cmntmaxlen = 0, $iscaptcha = 0;
	protected $cmntminlen = 10;
	protected $permission = false;

	public function loadActionIndex()
	{
		$this->initialize();
		$tid = intval($this->request->getPost('tid', $this->request->getQuery(0)));
		$this->page = intval($this->request->query('page', $this->request->getQuery('page')));
		if (!$this->thread = DB::fetch_first("SELECT t.*,c.depth,c.rootid,c.parentid,c.basic,c.catname,c.subname,c.codename,c.prefixurl,c.prefix,c.caturl
				FROM " . DB::table('threads') . " t
				LEFT JOIN " . DB::table('category') . " c USING(catid)
				WHERE t.status='1' AND t.tid='$tid'")) {
				showmessage('undefined_action', NULL, NULL, $this->paramextra);
		}
		$this->tid = $this->thread['tid'];
		$this->chanid = $this->thread['chanid'];
		$this->catid = $this->thread['catid'];
		$this->parentid = $this->thread['parentid'];
		$this->rootid = $this->thread['rootid'];
		phpcom::$G['channelid'] = $this->chanid;
		$this->channel = phpcom::$G['channel'][$this->chanid];
		$this->gorupid = phpcom::$G['group']['groupid'];
		$this->permission = (phpcom::$G['group']['delcomment'] && phpcom::$G['group']['type'] == 'system');

		$this->cmntmaxlen = intval(phpcom::$G['group']['commentlen']);
		$this->iscaptcha = isset(phpcom::$setting['captchastatus'][4]) ? intval(phpcom::$setting['captchastatus'][4]) : 0;
		$operation = strtolower(trim($this->request->getPost('operation', $this->request->getPost('do'))));
		$this->keyword = strip_tags($this->channel['keyword'] ? $this->channel['keyword'] : phpcom::$setting['keyword']);
		$this->description = strip_tags($this->channel['description'] ? $this->channel['description'] : phpcom::$setting['description']);

		if ($this->request->isPost() && $operation == 'post') {
			return $this->postComments();
		}elseif($this->request->isGet() && $operation == 'del'){
			return $this->deleteComments();
		}elseif($this->request->isGet() && in_array($operation, array('voteup', 'votedown'))){
			return $this->voteComments($operation);
		}elseif($operation == 'reply'){
			return $this->replyComments();
		}else{
			return $this->loadCommentsResult();
		}
	}

	protected function loadCommentsResult()
	{
		$formtoken = formtoken();
		$gorupid = $this->groupid;
		$tid = $this->thread['tid'];
		$catid = $this->thread['catid'];
		$commentnum = $this->thread['comments'];
		$iscaptcha = $this->iscaptcha;
		$urlargs = array('chanid' => $this->chanid, 'catdir' => $this->thread['codename'], 'date' => $this->thread['dateline'],
				'tid' => $this->thread['tid'], 'catid' => $this->thread['catid'], 'page' => 1);
		if(isset($this->thread['prefix'])){
			$urlargs['prefix'] = trim($this->thread['prefix']);
		}
		if (empty($this->channel['domain']) && empty($this->thread['prefixurl'])) {
			$this->thread['domain'] = $this->domain;
		} elseif(empty($this->thread['prefixurl'])) {
			$this->thread['domain'] = $this->channel['domain'] . '/';
		}else{
			$this->thread['domain'] = $this->thread['prefixurl'] . '/';
		}

		$urlargs['name'] = empty($this->thread['htmlname']) ? $this->thread['tid'] : trim($this->thread['htmlname']);
		$this->thread['url'] = geturl('threadview', $urlargs, $this->thread['domain']);
		$urlargs['name'] = $this->thread['codename'];
		if(!empty($this->thread['prefixurl']) && $this->thread['basic']){
			$this->thread['curl'] = $this->thread['prefixurl'];
		}else{
			$this->thread['curl'] = geturl($this->thread['basic'] ? 'category' : 'threadlist',$urlargs, $this->thread['domain']);
		}

		$urlargs['tid'] = $this->thread['tid'];
		$title = $this->thread['title'];
		$this->title = lang('common', 'comment') . ':' . $this->thread['title'];
		$this->keyword = $this->description = $title;
		$showpage = '';
		$replycount = 0;
		$datalist = $replydata = $commentids = array();
		$type = $this->request->query('t');
		$condition = $type ? 't1.commentid ASC' : 't1.lastdate DESC';
		$table = DB::table('comments');
		$count = DB::result_first("SELECT COUNT(*) FROM $table WHERE tid='$tid'");
		$pagesize = 15;
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($this->page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);

		$sql = DB::buildlimit("SELECT t1.*, t2.* FROM $table t1
				LEFT JOIN " . DB::table('comment_body') . " t2 ON t2.commentid=t1.commentid
				WHERE t1.tid='$tid' AND t2.status='1' AND t2.first='1' ORDER BY $condition", $pagesize, $pagestart);
		$query = DB::query($sql);
		$i = 0;
		while ($row = DB::fetch_array($query)) {
			$i++;
			$row['content'] = bbcode::output($row['content']);
			$row['date'] = fmdate($row['lastdate']);
			$row['datetime'] = fmdate($row['dateline']);
			$row['index'] = $i;
			$row['alt'] = $i % 2;
			if ($row['username'] == 'guest') {
				$row['username'] = lang('common', 'guest');
			}
			if ($row['author'] == 'guest') {
				$row['author'] = lang('common', 'guest');
			}
			$row['id'] = $row['bodyid'];
			$datalist[$row['commentid']] = $row;
			$commentids[] = $row['commentid'];
		}
		if($replyids = implodeids($commentids)){
			$sql = "SELECT * FROM " . DB::table('comment_body') . "
			WHERE first='0' AND status='1' AND commentid IN($replyids)";
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				$row['content'] = bbcode::output($row['content']);
				$row['date'] = fmdate($row['dateline']);
				if ($row['author'] == 'guest') {
					$row['author'] = lang('common', 'guest');
				}
				$row['id'] = $row['bodyid'];
				$row['index'] = $replycount;
				$replydata[$row['commentid']][$row['bodyid']] = $row;
				++$replycount;
			}
		}
		$urlargs['tid'] = $tid;
		$urlargs['page'] = '{%d}';
		unset($urlargs['chanid']);
		$pageurl = geturl('comment', $urlargs, $this->domain);
		$firsturl = $this->formatPageUrl($pageurl);
		$showpage = $this->paging($pagenow, $pagecount, $pagesize, $count, $pageurl, 7, 0, 0, $firsturl);

		$currenturl =  $pagenow > 1 ? str_replace('{%d}', $pagenow, $pageurl) : str_replace('{%d}', 1, $firsturl);
		$this->checkRequestUri($currenturl);
		$tplname = checktplname('comment',  '', $this->request->getParam('module'));
		include template($tplname);
		return 1;
	}

	protected function deleteComments()
	{
		$commentid = intval($this->request->query('commentid', $this->request->query('cid')));
		$bodyid = intval($this->request->query('bodyid', $this->request->query('id')));

		if(!$this->permission){
			showmessage('comment_permission_delete_denied', NULL, NULL, $this->paramextra);
		}
		if ($result = DB::fetch_first("SELECT commentid,tid FROM " . DB::table('comments') . " WHERE commentid='$commentid'")) {
			$commentid = $result['commentid'];
			$tid = $result['tid'];
			if ($commentid && !$bodyid) {
				$deleteids = array();
				$query = DB::query("SELECT bodyid FROM " . DB::table('comment_body') . " WHERE commentid='$commentid'");
				while ($row = DB::fetch_array($query)) {
					$deleteids[] = $row['bodyid'];
				}
				foreach ($deleteids as $bodyid) {
					DB::delete('comment_body', "bodyid='$bodyid'");
					DB::query("UPDATE " . DB::table('threads') . " SET comments=comments-1 WHERE tid='$tid'");
				}
				DB::delete('comments', "commentid='$commentid'");
			} elseif ($bodyid) {
				if ($row = DB::fetch_first("SELECT bodyid,first FROM " . DB::table('comment_body') . " WHERE bodyid='$bodyid'")) {
					if (!$row['first']) {
						DB::delete('comment_body', "bodyid='$bodyid'");
						DB::query("UPDATE " . DB::table('comments') . " SET num=num-1 WHERE commentid='$commentid'");
						DB::query("UPDATE " . DB::table('threads') . " SET comments=comments-1 WHERE tid='$tid'");
					}
				}
			}
		}
		$backurl = $this->getReferer();
		$this->paramextra['type'] = 'succeed';
		showmessage('comment_delete_succeed', $backurl, NULL, $this->paramextra);
		return 0;
	}

	protected function voteComments($action = 'voteup')
	{
		$commentid = intval($this->request->query('commentid', $this->request->query('cid')));
		$bodyid = intval($this->request->query('bodyid', $this->request->query('id')));
		$key = $action == 'votedown' ? 'votedown' : 'voteup';
		$votekey = 'comment_'.$key . '_' . $this->tid . '_' . $bodyid;
		if (isset(phpcom::$G['cookie'][$votekey]) && phpcom::$G['cookie'][$votekey]) {
			showmessage('comment_have_to_vote', NULL, NULL, $this->paramextra);
		} else {
			phpcom::setcookie($votekey, encryptstring(TIMESTAMP), 86400);
		}

		if ($vote = DB::fetch_first("SELECT bodyid, voteup, votedown FROM " . DB::table('comment_body') . " WHERE bodyid='$bodyid'")) {
			DB::query("UPDATE " . DB::table('comment_body') . " SET $key=$key+1 WHERE bodyid='$bodyid'");
			$this->loadAjaxHeader();
			echo $vote[$key] + 1;
			$this->loadAjaxFooter();
		} else {
			showmessage('undefined_action', NULL, NULL, $this->paramextra);
		}
		return 0;
	}

	protected function replyComments()
	{
		$type = strtolower(trim($this->request->getPost('type')));
		$iscaptcha = $this->iscaptcha;
		$domain = $this->domain;
		$tid = $this->tid;
		$commentid = intval($this->request->query('commentid', $this->request->query('cid')));
		$do = trim($this->request->query('do'));
		$key = $tid . '_' . $commentid;
		$uid = phpcom::$G['uid'];
		$this->loadAjaxHeader();
		include template('reply');
		$this->loadAjaxFooter();
		return 0;
	}

	protected function postComments()
	{
		$tid = $this->tid;
		$uid = phpcom::$G['uid'];
		if (!phpcom::$G['group']['comment'] || $this->thread['bancomment']) {
			showmessage('comment_permission_denied', NULL, NULL, $this->paramextra);
		}
		$lifetime = TIMESTAMP - 10;
		if ($dateline = DB::result_first("SELECT dateline FROM " . DB::table('comment_body') . " WHERE authorid='$uid' AND dateline>'$lifetime' LIMIT 1")) {
			showmessage('comment_post_lifetime_denied', NULL, NULL, $this->paramextra);
		}
		if (checksubmit(array('postsubmit', 'btnsubmit', 'submit'), 1, $this->iscaptcha)) {
			$commentid = intval($this->request->getPost('commentid', $this->request->getPost('cid')));
			$content = $this->request->post('content');
			if (strlength($content) > $this->cmntmaxlen) {
				showmessage('comment_string_length_limit', NULL, array('len' => $this->cmntmaxlen), $this->paramextra);
			}
			if (mb_strlen($content, CHARSET) < $this->cmntminlen) {
				showmessage('comment_string_minimum_limit', NULL, array('len' => $this->cmntminlen), $this->paramextra);
			}
			if (empty($content)) {
				showmessage('comment_post_undefined', NULL, NULL, $this->paramextra);
			}
			$backurl = $this->getReferer();
			$username = stripstring($this->request->post('username'));
			$author = phpcom::$G['username'] ? phpcom::$G['username'] : $username;
			$author = empty($author) ? 'guest' : $author;
			$author = strcut($author, 28, '');
			$content = checkinput($content);
			$data = array(
					'username' => $author,
					'lastdate' => TIMESTAMP,
					'ip' => phpcom::$G['clientip']
			);
			$status = phpcom::$G['group']['commentnoaudit'] ? 1 : 0;
			$body = array(
					'authorid' => phpcom::$G['uid'],
					'author' => $author,
					'content' => $content,
					'dateline' => TIMESTAMP,
					'userip' => phpcom::$G['clientip'],
					'status' => $status,
			);
				
			if ($row = DB::fetch_first("SELECT commentid,num FROM " . DB::table('comments') . " WHERE commentid='$commentid'")) {
				$body['commentid'] = $commentid;
				$body['first'] = 0;
				if ($row['num'] > 50) {
					showmessage('comment_reply_limit', NULL, NULL, $this->paramextra);
				}
				DB::insert('comment_body', $body);
				$data['num'] = $row['num'] + 1;
				DB::update('comments', $data, "commentid='$commentid'");
			} else {

				$data['tid'] = $tid;
				$commentid = DB::insert('comments', $data, TRUE);
				$body['commentid'] = $commentid;
				$body['first'] = 1;
				DB::insert('comment_body', $body);
			}
				
			DB::query("UPDATE " . DB::table('threads') . " SET comments=comments+1 WHERE tid='$tid'");
			if (phpcom::$G['uid']) {
				update_creditbyaction('comment', phpcom::$G['uid']);
			}
			$digging = trim($this->request->getPost('digging'));
			if (in_array($digging, array('up', 'good', 'digg'))) {
				DB::query("UPDATE " . DB::table('thread_field') . " SET voteup=voteup+1 WHERE tid='$tid'");
			} elseif (in_array($digging, array('down', 'bad', 'bury'))) {
				DB::query("UPDATE " . DB::table('thread_field') . " SET votedown=votedown+1 WHERE tid='$tid'");
			}
			$this->paramextra['type'] = 'succeed';
			showmessage('comment_post_succeed', $backurl, NULL, $this->paramextra);
		}
		return 0;
	}
}
?>