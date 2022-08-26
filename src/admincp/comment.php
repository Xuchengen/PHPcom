<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : comment.php    2011-5-19 5:53:44
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';
admin_header('menu_comment');
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('topic');
$navarray = array(
		array('title' => 'menu_comment', 'url' => '?m=comment', 'id' => 'comment'),
		array('title' => 'menu_comment_audit_main', 'url' => '?m=comment&action=audit', 'id' => 'audit'),
		array('title' => 'menu_comment_audit_reply', 'url' => '?m=comment&action=reply', 'id' => 'reply')
);
$do = isset(phpcom::$G['gp_do']) ? trim(phpcom::$G['gp_do']) : '';
$activation = stricmp($action, array('comment', 'audit', 'reply'), true, 'comment');
if($action == 'special'){
	$activation = 'special_' . stricmp($do, array('audit', 'reply'), true, 'comment');
}
$adminhtml->navtabs($navarray, $activation, 'nav_tabs', 'comment');
include loadlibfile('transip');
if ($action == 'edit') {
	$commentid = isset(phpcom::$G['gp_cid']) ? intval(phpcom::$G['gp_cid']) : 0;
	$bodyid = isset(phpcom::$G['gp_id']) ? intval(phpcom::$G['gp_id']) : 0;
	if (!$comment = DB::fetch_first("SELECT b.*, c.tid, c.lastdate FROM " . DB::table('comment_body') . " b
			LEFT JOIN " . DB::table('comments') . " c ON c.commentid=b.commentid
			WHERE b.bodyid='$bodyid'")) {
			admin_message('undefined_action');
	}
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$url = geturl('comment', array(
				'tid' => $comment['tid'],
				'page' => 1
		), phpcom::$G['instdir']);
		$adminhtml->form("m=comment&action=edit&cid=$commentid&id=$bodyid");
		$adminhtml->table_header('comment_edit');
		$adminhtml->table_td(array(
				array('comment_author', FALSE, '', '', TRUE),
				array('<input class="input t60" size="60" name="comments[author]" type="text" value="' . htmlcharsencode($comment['author']) . '" />', TRUE)
		));
		$adminhtml->table_td(array(
				array('comment_userip', FALSE, '', '', TRUE),
				array('<input class="input t30" size="30" name="comments[userip]" type="text" value="' . htmlcharsencode($comment['userip']) . '" /> (<span class="c2">' . translateip($comment['userip']) . "</span>)", TRUE)
		));
		$adminhtml->table_td(array(
				array('comment_dateline', FALSE, '', '', TRUE),
				array('<input class="input t30" size="30" name="comments[dateline]" type="text" value="' . fmdate($comment['dateline'], 'Y-m-d H:i:s') . '" />', TRUE)
		));
		$adminhtml->table_td(array(
				array('comment_content', FALSE, '', '', TRUE),
				array('<textarea class="textarea t100" rows="10" cols="100" name="comments[content]">' . htmlcharsencode($comment['content']) . '</textarea>', TRUE)
		));
		$adminhtml->table_td(array(
				array('comment_vote_up_down', FALSE, '', '', TRUE),
				array('<input class="input t10" name="comments[voteup]" type="text" value="' . intval($comment['voteup']) . '" />
						<input class="input t10" name="comments[votedown]" type="text" value="' . intval($comment['votedown']) . '" />', TRUE)
		));
		$adminhtml->table_td(array(
				array('comment_status', FALSE, '', '', TRUE),
				array($adminhtml->radio(adminlang('comment_status_option'), 'comments[status]', intval($comment['status'])), TRUE)
		));
		$btnsubmit = $adminhtml->submit_button();
		$adminhtml->table_td(array(
				array($btnsubmit, TRUE, 'align="center" colspan="2"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
	}else{
		$comments = isset(phpcom::$G['gp_comments']) ? phpcom::$G['gp_comments'] : null;
		if($comments && $bodyid){
			$comments['content'] = checkinput($comments['content']);
			if(empty($comments['userip']) || !checkUserIpv4($comments['userip'])){
				unset($comments['userip']);
			}
			if(isset($comments['dateline'])){
				if(empty($comments['dateline'])){
					$comments['dateline'] = time();
				}else{
					$comments['dateline'] = strtotime($comments['dateline']);
				}
				if($comment['dateline'] == $comment['lastdate']){
					DB::update('comments', array('lastdate' => $comments['dateline']), "commentid='$commentid'");
				}
			}
			DB::update('comment_body', $comments, "bodyid='$bodyid'");
		}
		admin_succeed('comment_edit_succeed', "m=comment&action=edit&cid=$commentid&id=$bodyid");
	}
}elseif ($action == 'del') {
	$commentid = isset(phpcom::$G['gp_cid']) ? intval(phpcom::$G['gp_cid']) : 0;
	$bodyid = isset(phpcom::$G['gp_id']) ? intval(phpcom::$G['gp_id']) : 0;
	deleteComment($commentid, $bodyid);
	admin_succeed('comment_delete_succeed', "m=comment");
}elseif ($action == 'audit') {
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$adminhtml->form("m=comment&action=audit");
		$adminhtml->table_header();
		$adminhtml->table_th(array(
				array('choosecheckbox', 'width="3%" align="center" noWrap="noWrap"'),
				array('comment_audit_main', 'width="87%" class="left" noWrap="noWrap"'),
				array('detail', 'width="10%"')
		));
		$adminhtml->table_td(array(
				array(' ', TRUE, 'colspan="3" align="left" id="showpage"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$queryurl = '&action=audit';
		$condition = "t2.first='1' AND t2.status='0' ";
		$table = DB::table('comments');
		$count = DB::result_first("SELECT COUNT(*) FROM $table");
		$pagesize = 30;
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$sql = DB::buildlimit("SELECT t1.*, t2.*, t.title FROM $table t1
				LEFT JOIN " . DB::table('comment_body') . " t2 ON t2.commentid=t1.commentid
				LEFT JOIN " . DB::table('threads') . " t ON t.tid=t1.tid
				WHERE $condition ORDER BY t1.lastdate DESC", $pagesize, $pagestart);
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$row['content'] = bbcode::output($row['content']);
			$row['date'] = fmdate($row['lastdate']);
			$row['datetime'] = fmdate($row['dateline']);
			if ($row['username'] == 'guest') {
				$row['username'] = lang('common', 'guest');
			}
			if ($row['author'] == 'guest') {
				$row['author'] = lang('common', 'guest');
			}
			$cid = $row['commentid'];
			$row['url'] = geturl('comment', array(
					'tid' => $row['tid'],
					'page' => 1
			), phpcom::$G['instdir']);
			$edit = $adminhtml->edit_word('audit', "m=comment&action=edit&cid=$cid&id={$row['bodyid']}", ' | ');
			$edit .= $adminhtml->del_word('delete', "m=comment&action=del&cid=$cid");
			$adminhtml->table_td(array(
					array('<input type="checkbox" class="checkbox" name="choose[' . $row['bodyid'] . ']" value="' . $cid . '" />', TRUE),
					array('<a href="'.$row['url'].'" target="_blank">'.$row['title'].'</a>', TRUE),
					array($edit, TRUE)
			), '', FALSE, ' tablerow', NULL, FALSE);
			$s = '<div class="quoting first"><div class="ct">';
			$s .= "{$row['datetime']} &nbsp; <a href=\"member.php?action=home&uid={$row['authorid']}\" target=\"_blank\">{$row['author']}</a> &nbsp; {$row['userip']} (<span class=\"c2\">" . translateip($row['userip']). "</span>)</div>";
			$s .= "<p class=\"cc\">{$row['content']}</p></div>";
			$adminhtml->table_td(array(
					array($s, TRUE, 'colspan="3"')
			));
		}
		$adminhtml->table_td(array(
				array($adminhtml->checkall('checkall', 'chkall', 'choose') . ' ' .
						$adminhtml->radio(adminlang('comment_operation_option'), 'operation', 'audit', false) . ' ' .
						$adminhtml->submit_button('submit', 'btnsubmit', 'button'), TRUE, 'colspan="3"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $count, ADMIN_SCRIPT . "?m=comment$queryurl") . '</var>';
		$adminhtml->table_td(array(
				array($showpage, TRUE, 'colspan="3" align="right" id="pagecode"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
		$adminhtml->showpagescript();
	}else{
		$choose = isset(phpcom::$G['gp_choose']) ? phpcom::$G['gp_choose'] : null;
		$operation = isset(phpcom::$G['gp_operation']) ? trim(phpcom::$G['gp_operation']) : null;
		if(!empty($choose) && $operation == 'delete'){
			if($choose && is_array($choose)){
				foreach ($choose as $commentid){
					deleteComment($commentid, 0);
				}
			}
			admin_succeed('comment_delete_succeed', "m=comment&action=audit");
		}elseif(!empty($choose) && $operation == 'clean'){
			$extra = '';
			foreach ($choose as $bid => $cid) {
				$extra .= "<input type=\"hidden\" name=\"choose[$bid]\" value=\"$cid\" />";
			}
			$msgargs = array(
					'form' => TRUE,
					'submit' => TRUE,
					'cancel' => TRUE,
					'action' => '?m=comment&action=audit&operation=delete&submit=yes'
			);
			admin_showmessage('comment_delete_message', null, $msgargs, $extra);
		}elseif(!empty($choose) && $operation == 'audit'){
			auditComment($choose);
			admin_succeed('comment_audit_succeed', "m=comment&action=audit");
		}else{
			admin_message('comment_operation_invalid');
		}
	}
}elseif ($action == 'reply') {
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$adminhtml->form("m=comment&action=reply");
		$adminhtml->table_header();
		$adminhtml->table_th(array(
				array('choosecheckbox', 'width="3%" align="center" noWrap="noWrap"'),
				array('comment_audit_reply', 'width="87%" class="left" noWrap="noWrap"'),
				array('detail', 'width="10%"')
		));
		$adminhtml->table_td(array(
				array(' ', TRUE, 'colspan="3" align="left" id="showpage"')
		), NULL, FALSE, NULL, NULL, FALSE);

		$queryurl = '&action=audit';
		$condition = "t2.first='0' AND t2.status='0' ";
		$table = DB::table('comments');
		$count = DB::result_first("SELECT COUNT(*) FROM $table");
		$pagesize = 30;
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$sql = DB::buildlimit("SELECT t1.*, t2.*, t.title FROM $table t1
				LEFT JOIN " . DB::table('comment_body') . " t2 ON t2.commentid=t1.commentid
				LEFT JOIN " . DB::table('threads') . " t ON t.tid=t1.tid
				WHERE $condition ORDER BY t1.lastdate DESC", $pagesize, $pagestart);
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$row['content'] = bbcode::output($row['content']);
			$row['date'] = fmdate($row['lastdate']);
			$row['datetime'] = fmdate($row['dateline']);
			if ($row['username'] == 'guest') {
				$row['username'] = lang('common', 'guest');
			}
			if ($row['author'] == 'guest') {
				$row['author'] = lang('common', 'guest');
			}
			$cid = $row['commentid'];
			$row['url'] = geturl('comment', array(
					'tid' => $row['tid'],
					'page' => 1
			), phpcom::$G['instdir']);
			$edit = $adminhtml->edit_word('audit', "m=comment&action=edit&cid=$cid&id={$row['bodyid']}", ' | ');
			$edit .= $adminhtml->del_word('delete', "m=comment&action=del&cid=$cid&id={$row['bodyid']}");
			$adminhtml->table_td(array(
					array('<input type="checkbox" class="checkbox" name="choose[' . $row['bodyid'] . ']" value="' . $cid . '" />', TRUE),
					array('<a href="'.$row['url'].'" target="_blank">'.$row['title'].'</a>', TRUE),
					array($edit, TRUE)
			), '', FALSE, ' tablerow', NULL, FALSE);
			$s = '<div class="quoting first"><div class="ct">';
			$s .= "{$row['datetime']} &nbsp; <a href=\"member.php?action=home&uid={$row['authorid']}\" target=\"_blank\">{$row['author']}</a> &nbsp; {$row['userip']} (<span class=\"c2\">" . translateip($row['userip']). "</span>)</div>";
			$s .= "<p class=\"cc\">{$row['content']}</p></div>";
			$adminhtml->table_td(array(
					array($s, TRUE, 'colspan="3"')
			));
		}
		$adminhtml->table_td(array(
				array($adminhtml->checkall('checkall', 'chkall', 'choose') . ' ' .
						$adminhtml->radio(adminlang('comment_operation_option'), 'operation', 'audit', false) . ' ' .
						$adminhtml->submit_button('submit', 'btnsubmit', 'button'), TRUE, 'colspan="3"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $count, ADMIN_SCRIPT . "?m=comment$queryurl") . '</var>';
		$adminhtml->table_td(array(
				array($showpage, TRUE, 'colspan="3" align="right" id="pagecode"')
		), NULL, FALSE, NULL, NULL, FALSE);
		
		$adminhtml->table_end('</form>');
		$adminhtml->showpagescript();
	}else{
		$choose = isset(phpcom::$G['gp_choose']) ? phpcom::$G['gp_choose'] : null;
		$operation = isset(phpcom::$G['gp_operation']) ? trim(phpcom::$G['gp_operation']) : null;
		if(!empty($choose) && $operation == 'delete'){
			if($choose && is_array($choose)){
				foreach ($choose as $bodyid => $commentid){
					deleteComment($commentid, $bodyid);
				}
			}
			admin_succeed('comment_delete_succeed', "m=comment&action=reply");
		}elseif(!empty($choose) && $operation == 'clean'){
			$extra = '';
			foreach ($choose as $bid => $cid) {
				$extra .= "<input type=\"hidden\" name=\"choose[$bid]\" value=\"$cid\" />";
			}
			$msgargs = array(
					'form' => TRUE,
					'submit' => TRUE,
					'cancel' => TRUE,
					'action' => '?m=comment&action=reply&operation=delete&submit=yes'
			);
			admin_showmessage('comment_delete_message', null, $msgargs, $extra);
		}elseif(!empty($choose) && $operation == 'audit'){
			auditComment($choose);
			admin_succeed('comment_audit_succeed', "m=comment&action=reply");
		}else{
			admin_message('comment_operation_invalid');
		}
	}
}else{
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$adminhtml->form('m=comment');
		$adminhtml->table_header();
		$adminhtml->table_th(array(
				array('deletecheckbox', 'width="3%" align="center" noWrap="noWrap"'),
				array('&nbsp;', 'width="87%" class="left" noWrap="noWrap"'),
				array('detail', 'width="10%"')
		));
		$adminhtml->table_td(array(
				array(' ', TRUE, 'colspan="3" align="left" id="showpage"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$tid = isset(phpcom::$G['gp_tid']) ? intval(phpcom::$G['gp_tid']) : 0;
		$word = isset(phpcom::$G['gp_word']) ? trim(phpcom::$G['gp_word']) : '';
		$replycount = 0; $queryurl = '';
		$datalist = $replydata = $commentids = array();
		$condition = "t2.first='1' AND t2.status='1' ";
		if ($action == 'search' && $word) {
			$word = str_replace('_', '\_', $word);
			if(preg_match('#^([0-9]+)(\.html)?$#i', $word, $matches)){
				$condition .= " AND t.tid='$matches[1]'";
			}elseif(preg_match('#^(http:|https:|www\.)([0-9A-Za-z_\-\.\/]+)\/([0-9]+)(\.html|\/)?$#i', $word, $matches)){
				$condition .= " AND t.tid='$matches[3]'";
			}elseif($word{0} == '!' || $word{0} == '^'){
				$condition .= " AND t.title LIKE '%". substr($word, 1) ."%'";
			}else{
				$condition .= " AND t.title LIKE '%$word%'";
			}
			$queryurl = implodeurl(array('action' => 'search', 'word' => $word), '&');
		}elseif($tid) {
			$condition .= " AND t.tid='$tid'";
			$queryurl = implodeurl(array('tid' => $tid), '&');
		}
		$table = DB::table('comments');
		$count = DB::result_first("SELECT COUNT(*) FROM $table");
		$pagesize = 20;
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$sql = DB::buildlimit("SELECT t1.*, t2.*, t.title FROM $table t1
				LEFT JOIN " . DB::table('comment_body') . " t2 ON t2.commentid=t1.commentid
				INNER JOIN " . DB::table('threads') . " t ON t.tid=t1.tid
				WHERE $condition ORDER BY t1.lastdate DESC", $pagesize, $pagestart);
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$row['content'] = bbcode::output($row['content']);
			$row['date'] = fmdate($row['lastdate']);
			$row['datetime'] = fmdate($row['dateline']);
			if ($row['username'] == 'guest') {
				$row['username'] = lang('common', 'guest');
			}
			if ($row['author'] == 'guest') {
				$row['author'] = lang('common', 'guest');
			}
			$row['url'] = geturl('comment', array(
					'tid' => $row['tid'],
					'page' => 1
			), phpcom::$G['instdir']);
			$datalist[$row['commentid']] = $row;
			$commentids[] = $row['commentid'];
		}
		if($replyids = implodeids($commentids)){
			$sql = "SELECT * FROM " . DB::table('comment_body') . "
			WHERE first='0' AND status>='0' AND commentid IN($replyids) ORDER BY bodyid ASC";
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				$row['content'] = bbcode::output($row['content']);
				$row['date'] = fmdate($row['dateline']);
				if ($row['author'] == 'guest') {
					$row['author'] = lang('common', 'guest');
				}
				$replydata[$row['commentid']][$row['bodyid']] = $row;
				++$replycount;
			}
		}
		foreach ($datalist as $cid => $comment){
			$status = $comment['status'] ? 'edit' : 'audit';
			$edit = $adminhtml->edit_word($status, "m=comment&action=edit&cid=$cid&id={$comment['bodyid']}", ' | ');
			$edit .= $adminhtml->del_word('delete', "m=comment&action=del&cid=$cid");
			$adminhtml->table_td(array(
					array('<input type="checkbox" class="checkbox" name="delete[]" value="' . $cid . '" />', TRUE),
					array('<a href="'.$comment['url'].'" target="_blank">'.$comment['title'].'</a>', TRUE),
					array($edit, TRUE)
			), '', FALSE, ' tablerow', NULL, FALSE);
				
			$s = '<div class="quoting first"><div class="ct"><span class="num">1</span>';
			$s .= "{$comment['datetime']} &nbsp; <a href=\"member.php?action=home&uid={$comment['authorid']}\" target=\"_blank\">{$comment['author']}</a> &nbsp; {$comment['userip']} (<span class=\"c2\">" . translateip($comment['userip']). "</span>)</div>";
			$s .= "<p class=\"cc\">{$comment['content']}</p></div>";
				
			if(isset($replydata[$cid])){
				$i = 1;
				foreach ($replydata[$cid] as $id => $reply){
					$i++;
					$s = showComment($reply, $s, $i, $adminhtml);
				}
			}
			$adminhtml->table_td(array(
					array($s, TRUE, 'colspan="3"')
			));
		}

		$showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $count, ADMIN_SCRIPT . "?m=comment$queryurl") . '</var>';
		$adminhtml->table_td(array(
				array($showpage, TRUE, 'colspan="3" align="right" id="pagecode"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_td(array(
				array('&nbsp;', ''),
				array($adminhtml->submit_button('delete'), TRUE, 'colspan="2"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
		$adminhtml->showpagescript();
	}else{
		$delete = isset(phpcom::$G['gp_delete']) ? phpcom::$G['gp_delete'] : null;
		if($delete && is_array($delete)){
			foreach ($delete as $commentid){
				deleteComment($commentid, 0);
			}
		}
		admin_succeed('comment_delete_succeed', "m=comment");
	}
}
admin_footer();

function checkUserIpv4($ip) {
	return  preg_match('#^[0-9\*]{1,3}\.[0-9\*]{1,3}\.[0-9\*]{1,3}\.[0-9\*]{1,3}$#', trim($ip));
}

function showComment($comment, $string, $index = 2, &$adminhtml){
	$status = $comment['status'] ? 'edit' : 'audit';
	$edit = $adminhtml->edit_word($status, "m=comment&action=edit&cid={$comment['commentid']}&id={$comment['bodyid']}", ' | ');
	$edit .= $adminhtml->del_word('delete', "m=comment&action=del&cid={$comment['commentid']}&id={$comment['bodyid']}");
	$s = '<div class="quoting">';
	$s .= $string;
	$s .= "<div class=\"ct\"><span class=\"num\">$index</span>{$comment['date']} &nbsp; ";
	$s .= "<a href=\"member.php?action=home&uid={$comment['authorid']}\" target=\"_blank\">{$comment['author']}</a> &nbsp; {$comment['userip']} (<span class=\"c2\">" . translateip($comment['userip']). "</span>)</div>\r\n";
	$s .= "<p class=\"cc\">{$comment['content']}</p>";
	$s .= "<p class=\"cb\">$edit</p>";
	$s .= "</div>\r\n";
	return $s;
}

function showTopicComment($comment, $string, $index = 2, &$adminhtml){
	$status = $comment['status'] ? 'edit' : 'audit';
	$edit = $adminhtml->edit_word($status, "m=comment&action=special&do=edit&cid={$comment['commentid']}", ' | ');
	$edit .= $adminhtml->del_word('delete', "m=comment&action=special&do=del&cid={$comment['commentid']}");
	$s = '<div class="quoting">';
	$s .= $string;
	$s .= "<div class=\"ct\"><span class=\"num\">$index</span>{$comment['date']} &nbsp; ";
	$s .= "<a href=\"member.php?action=home&uid={$comment['authorid']}\" target=\"_blank\">{$comment['author']}</a> &nbsp; {$comment['userip']} (<span class=\"c2\">" . translateip($comment['userip']). "</span>)</div>\r\n";
	$s .= "<p class=\"cc\">{$comment['content']}</p>";
	$s .= "<p class=\"cb\">$edit</p>";
	$s .= "</div>\r\n";
	return $s;
}

function auditComment($commentids){
	if($commentids && is_array($commentids)){
		foreach ($commentids as $bodyid => $commentid){
			DB::update('comment_body', array('status' => 1), "bodyid='$bodyid'");
		}
	}
}

function auditTopicComment($commentids){
	if($commentids && is_array($commentids)){
		foreach ($commentids as $commentid){
			DB::update('topic_comment', array('status' => 1), "commentid='$commentid'");
		}
	}
}

function deleteComment($commentid, $bodyid = 0){
	if(!phpcom_admincp::permission('comment_delete')){
		admin_message('action_delete_denied');
	}
	if ($result = DB::fetch_first("SELECT commentid,tid FROM " . DB::table('comments') . " WHERE commentid='$commentid'")) {
		$commentid = $result['commentid'];
		$tid = $result['tid'];
		if ($bodyid) {
			if ($row = DB::fetch_first("SELECT bodyid,first FROM " . DB::table('comment_body') . " WHERE first='0' AND bodyid='$bodyid'")) {
				if (!$row['first']) {
					DB::delete('comment_body', "bodyid='$bodyid'");
					DB::query("UPDATE " . DB::table('comments') . " SET num=num-1 WHERE commentid='$commentid'");
					DB::query("UPDATE " . DB::table('threads') . " SET comments=comments-1 WHERE tid='$tid'");
				}
			}
		}else{
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
		}
		return true;
	}
	return false;
}

function deleteTopicComment($commentid){
	if(!phpcom_admincp::permission('comment_delete')){
		admin_message('action_delete_denied');
	}
	if ($result = DB::fetch_first("SELECT commentid,tid,fid,first FROM " . DB::table('topic_comment') . " WHERE commentid='$commentid'")) {
		$commentid = $result['commentid'];
		$tid = $result['tid'];
		$fid = $result['fid'];
		if($result['first']){
			$deleteids = array();
			$query = DB::query("SELECT commentid FROM " . DB::table('topic_comment') . " WHERE first>='0' AND status>='0' AND fid='$commentid'");
			while ($row = DB::fetch_array($query)) {
				$deleteids[] = $row['commentid'];
			}
			foreach ($deleteids as $cid) {
				DB::delete('topic_comment', "commentid='$cid'");
				DB::query("UPDATE " . DB::table('topical') . " SET comments=comments-1 WHERE topicid='$tid'");
			}
		}else{
			DB::delete('topic_comment', "commentid='$commentid'");
			DB::query("UPDATE " . DB::table('topic_comment') . " SET num=num-1 WHERE commentid='$fid'");
			DB::query("UPDATE " . DB::table('topical') . " SET comments=comments-1 WHERE topicid='$tid'");
		}
	}
}

?>
