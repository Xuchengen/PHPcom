<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : delete.php    2011-12-18
 */
!defined('IN_PHPCOM') && exit('Access denied');

function delete_article_thread($tids = 0, $uids = 0) {
	$condition = '';
	if (!empty($tids)) {
		$condition = 'tid IN(' . implodeids($tids) . ')';
	} elseif (!empty($uids)) {
		$condition = 'uid IN(' . implodeids($uids) . ')';
	} else {
		return 0;
	}
	$tids = $data = array();
	//$catids = $rootids = array();
	$query = DB::query("SELECT tid, rootid, catid, tableindex FROM " . DB::table('threads') . " WHERE $condition");
	while ($result = DB::fetch_array($query)) {
		$tids[] = $result['tid'];
		$data[] = $result;
		//if(!isset($catids[$result['catid']])) $catids[$result['catid']] = 0;
		//$catids[$result['catid']]++;
		//$rootids[$result['catid']] = $result['rootid'];
	}
	if ($tids) {
		$condition = 'tid IN(' . implodeids($tids) . ')';
		DB::delete('threads', $condition);
		DB::delete('thread_field', $condition);
		DB::delete('article_thread', $condition);
		delete_threadimage($tids, 'article');
		DB::delete('special_data', $condition);
		DB::delete('thread_class_data', $condition);
		DB::delete('pollvotes', $condition);
		DB::delete('polloption', $condition);
		foreach ($data as $row) {
			DB::delete('article_content', "tid='{$row['tid']}'", $row['tableindex']);
		}
		delete_tagdata($tids);
		delete_comment($tids);
		delete_attachment($tids, null, null, 'article');
		/*foreach($catids as $catid => $count){
			$rootid = $rootids[$catid];
			DB::exec("UPDATE " . DB::table('category') . " SET counts=counts-'$count' WHERE catid='$catid'");
			if($rootid != $rootid){
				DB::exec("UPDATE " . DB::table('category') . " SET counts=counts-'$count' WHERE catid='$rootid'");
			}
		}*/
		return 1;
	} else {
		return 0;
	}
}

function delete_softinfo_thread($tids = 0, $uids = 0) {
	$condition = '';
	if (!empty($tids)) {
		$condition = 'tid IN(' . implodeids($tids) . ')';
	} elseif (!empty($uids)) {
		$condition = 'uid IN(' . implodeids($uids) . ')';
	} else {
		return 0;
	}
	$tids = array();
	$data = array();
	$query = DB::query("SELECT tid, tableindex FROM " . DB::table('threads') . " WHERE $condition");
	while ($result = DB::fetch_array($query)) {
		$tids[] = $result['tid'];
		$data[] = $result;
	}
	if ($tids) {
		$condition = 'tid IN(' . implodeids($tids) . ')';
		DB::delete('threads', $condition);
		DB::delete('thread_field', $condition);
		DB::delete('soft_thread', $condition);
		DB::delete('soft_download', $condition);
		delete_threadimage($tids, 'soft');
		DB::delete('special_data', $condition);
		DB::delete('thread_class_data', $condition);
		DB::delete('pollvotes', $condition);
		DB::delete('polloption', $condition);
		//DB::delete('moods', $condition);
		foreach ($data as $row) {
			DB::delete('soft_content', "tid='{$row['tid']}'", $row['tableindex']);
		}
		delete_tagdata($tids);
		delete_comment($tids);
		delete_attachment($tids, null, null, 'soft');
		return 1;
	} else {
		return 0;
	}
}

function delete_photo_thread($tids = 0, $uids = 0) {
	$condition = '';
	if (!empty($tids)) {
		$condition = 'tid IN(' . implodeids($tids) . ')';
	} elseif (!empty($uids)) {
		$condition = 'uid IN(' . implodeids($uids) . ')';
	} else {
		return 0;
	}
	$tids = array();
	$data = array();
	$query = DB::query("SELECT tid, tableindex FROM " . DB::table('threads') . " WHERE $condition");
	while ($result = DB::fetch_array($query)) {
		$tids[] = $result['tid'];
		$data[] = $result;
	}
	if ($tids) {
		$condition = 'tid IN(' . implodeids($tids) . ')';
		DB::delete('threads', $condition);
		DB::delete('thread_field', $condition);
		DB::delete('photo_thread', $condition);
		delete_threadimage($tids, 'photo');
		DB::delete('special_data', $condition);
		DB::delete('thread_class_data', $condition);
		foreach ($data as $row) {
			DB::delete('photo_content', "tid='{$row['tid']}'", $row['tableindex']);
		}
		delete_tagdata($tids);
		delete_persondata($tids);
		delete_comment($tids);
		delete_attachment($tids, null, null, 'photo');
		return 1;
	} else {
		return 0;
	}
}

function delete_video_thread($tids = 0, $uids = 0) {
	$condition = '';
	if (!empty($tids)) {
		$condition = 'tid IN(' . implodeids($tids) . ')';
	} elseif (!empty($uids)) {
		$condition = 'uid IN(' . implodeids($uids) . ')';
	} else {
		return 0;
	}
	$tids = array();
	$data = array();
	DB::delete('threads', $condition);
	$query = DB::query("SELECT tid, tableindex FROM " . DB::table('threads') . " WHERE $condition");
	while ($result = DB::fetch_array($query)) {
		$tids[] = $result['tid'];
		$data[] = $result;
	}
	if ($tids) {
		$condition = 'tid IN(' . implodeids($tids) . ')';
		DB::delete('thread_field', $condition);
		DB::delete('video_thread', $condition);
		delete_threadimage($tids, 'video');
		DB::delete('special_data', $condition);
		DB::delete('thread_class_data', $condition);
		foreach ($data as $row) {
			DB::delete('video_content', "tid='{$row['tid']}'", $row['tableindex']);
		}
		delete_tagdata($tids);
		delete_persondata($tids);
		delete_comment($tids);
		delete_attachment($tids, null, null, 'video');
		return 1;
	} else {
		return 0;
	}
}

function delete_special_thread($tids = 0, $uids = 0) {
	$condition = '';
	if (!empty($tids)) {
		$condition = 'tid IN(' . implodeids($tids) . ')';
	} elseif (!empty($uids)) {
		$condition = 'uid IN(' . implodeids($uids) . ')';
	} else {
		return 0;
	}
	$tids = $data = array();
	$query = DB::query("SELECT tid, rootid, catid, tableindex FROM " . DB::table('threads') . " WHERE $condition");
	while ($result = DB::fetch_array($query)) {
		$tids[] = $result['tid'];
		$data[] = $result;
	}
	if ($tids) {
		$condition = 'tid IN(' . implodeids($tids) . ')';
		DB::delete('threads', $condition);
		DB::delete('thread_field', $condition);
		DB::delete('special_thread', $condition);
		delete_threadimage($tids, 'special');
		if($specids = implodeids($tids)){
			DB::delete('special_data', "specid IN($specids)");
		}
		DB::delete('special_class', $condition);
		DB::delete('thread_class_data', $condition);
		foreach ($data as $row) {
			DB::delete('special_content', "tid='{$row['tid']}'", $row['tableindex']);
		}
		delete_tagdata($tids);
		delete_comment($tids);
		delete_attachment($tids, null, null, 'special');
		return 1;
	} else {
		return 0;
	}
}

function delete_comment($tids = array(), $uids = array(), $commentids = array()) {
	$condition = '';
	if (!empty($tids)) {
		$condition = 'tid IN(' . implodeids($tids) . ')';
	} elseif (!empty($uids)) {
		$condition = 'uid IN(' . implodeids($uids) . ')';
	} elseif (!empty($commentids)) {
		$condition = 'commentid IN(' . implodeids($commentids) . ')';
	} else {
		return 0;
	}
	DB::query("DELETE t1, t2 FROM " . DB::table('comments') . " as t1
			LEFT JOIN " . DB::table('comment_body') . " as t2 ON t1.commentid=t2.commentid
			WHERE t1.$condition");
	return DB::affected_rows();
}

function delete_comment_body($bodyids, $commentid){
	if(empty($bodyids) || empty($commentid)){
		return 0;
	}
	if($deleteids = implodeids($bodyids)){
		DB::delete('comment_body', "bodyid IN($deleteids)");
	}
	if(!DB::result_first("SELECT COUNT(*) FROM " . DB::table('comment_body') . " WHERE commentid='$commentid'")){
		DB::delete('comments', "commentid='$commentid'");
	}
}

function delete_threadimage($tids = array(), $module = 'article') {
	$moduleArray = array('article', 'soft', 'photo', 'video');
	if(!in_array($module, $moduleArray)){
		return 0;
	}
	$condition = 'tid IN(' . implodeids($tids) . ')';
	$query = DB::query("SELECT attachment, thumb, preview, remote, attachimg FROM " . DB::table("thread_image") . " WHERE $condition");
	while($image = DB::fetch_array($query)) {
		if(!empty($image['attachment'])){
			Attachment::uploadUnlink(array(
			'dirname' => $module,
			'attachment' => $image['attachment'],
			'thumb' => $image['thumb'],
			'remote' => $image['remote']
			));
		}
		if(!empty($image['attachment'])){
			Attachment::uploadUnlink(array(
			'dirname' => $module,
			'attachment' => $image['attachimg'],
			'thumb' => 0,
			'remote' => $image['preview']
			));
		}
	}
	DB::delete('thread_image', $condition);
}

function delete_attachment($tids = array(), $uids = array(), $attachids = array(), $module = 'article') {
	$moduleArray = array('article', 'soft', 'photo', 'video');
	if(!in_array($module, $moduleArray)){
		return 0;
	}
	$condition = '';
	if (!empty($tids)) {
		$condition = 'tid IN(' . implodeids($tids) . ')';
	} elseif (!empty($uids)) {
		$condition = 'uid IN(' . implodeids($uids) . ')';
	} elseif (!empty($attachids)) {
		$condition = 'attachid IN(' . implodeids($attachids) . ')';
	} else {
		return 0;
	}
	$attachids = array();
	$query = DB::query("SELECT attachid, attachment, thumb, preview, remote FROM ".DB::table("attachment_$module")." WHERE $condition");
	while($attach = DB::fetch_array($query)) {
		$attach['module'] = $module;
		Attachment::unlinks($attach);
		$attachids[] = $attach['attachid'];
	}
	if($attachids){
		DB::delete("attachment_$module", 'attachid IN(' . implodeids($attachids) . ')');
	}
	return DB::affected_rows();
}

function delete_persondata($tid){
	if (is_array($tid)) {
		foreach ($tid as $value) {
			delete_persondata($value);
		}
	} else {
		$personidsarray = array();
		$tid = intval($tid);
		if ($tid) {
			$query = DB::query("SELECT personid FROM " . DB::table('persondata') . " WHERE tid='$tid'");
			while ($row = DB::fetch_array($query)) {
				$personidsarray[] = $row['personid'];
			}
			DB::delete('persondata', "tid=$tid");
			$personids = implodeids($personidsarray);
			if ($personids) {
				DB::update('persons', 'num=num-1', "personid IN($personids)");
			}
		}
	}
}

function delete_persons($personid){
	if (is_array($personid)) {
		$personids = implodeids($personid);
		if ($personids) {
			DB::delete('persons', "personid IN($personids)");
			DB::delete('persondata', "personid IN($personids)");
		}
	} else {
		$personid = intval($personid);
		if ($personid) {
			DB::delete('persons', "personid=$personid");
			DB::delete('persondata', "personid=$personid");
		}
	}
}

function delete_tags($tagid) {
	if (is_array($tagid)) {
		$tagids = implodeids($tagid);
		if ($tagids) {
			DB::delete('tags', "tagid IN($tagids)");
			DB::delete('tagdata', "tagid IN($tagids)");
		}
	} else {
		$tagid = intval($tagid);
		if ($tagid) {
			DB::delete('tags', "tagid=$tagid");
			DB::delete('tagdata', "tagid=$tagid");
		}
	}
}

function delete_tagdata($tid) {
	if (is_array($tid)) {
		foreach ($tid as $value) {
			delete_tagdata($value);
		}
	} else {
		$tagidsarray = array();
		$tid = intval($tid);
		if ($tid) {
			$query = DB::query("SELECT tagid FROM " . DB::table('tagdata') . " WHERE tid='$tid'");
			while ($row = DB::fetch_array($query)) {
				$tagidsarray[] = $row['tagid'];
			}
			DB::delete('tagdata', "tid=$tid");
			$tagids = implodeids($tagidsarray);
			if ($tagids) {
				DB::update('tags', 'tagnum=tagnum-1', "tagid IN($tagids)");
			}
		}
	}
}

function delete_platforms($pid) {
	if (is_array($pid)) {
		$pids = implodeids($pid);
		if ($pids) {
			DB::delete('platforms', "pid IN($pids)");
			DB::delete('platformdata', "pid IN($pids)");
		}
	} else {
		$pid = intval($pid);
		if ($pid) {
			DB::delete('platforms', "pid=$pid");
			DB::delete('platformdata', "pid=$pid");
		}
	}
}

function delete_platformdata($tid) {
	if (is_array($tid)) {
		foreach ($tid as $value) {
			delete_platformdata($value);
		}
	} else {
		$pidsarray = array();
		$tid = intval($tid);
		if ($tid) {
			$query = DB::query("SELECT pid FROM " . DB::table('platformdata') . " WHERE tid='$tid'");
			while ($row = DB::fetch_array($query)) {
				$pidsarray[] = $row['pid'];
			}
			DB::delete('platformdata', "tid=$tid");
			$pids = implodeids($pidsarray);
			if ($pids) {
				DB::update('platforms', 'pcount=pcount-1', "pid IN($pids)");
			}
		}
	}
}

function delete_member($arruid) {
	$condition = '';
	if (is_array($arruid)) {
		$condition = 'uid IN(' . implodeids($arruid) . ')';
	} else {
		$condition = "uid='$arruid'";
	}
	if ($arruid) {
		foreach (array('members', 'member_count', 'member_info', 'member_status', 'member_validate',
				'onlinetime', 'favorites', 'friends', 'friendrequest', 'notification', 'credit_log') as $table) {
				DB::delete($table, $condition);
		}
		if (DB::result_first("SELECT mid FROM " . DB::table('messages') . " WHERE $condition")) {
			DB::query("DELETE t1, t2 FROM " . DB::table('messages') . " as t1
					LEFT JOIN " . DB::table('message_body') . " as t2 ON t1.mid=t2.mid
					WHERE t1.$condition");
		}
		$condition = str_replace('uid', 'fuid', $condition);
		DB::query("DELETE FROM " . DB::table('friends') . " WHERE $condition");
		DB::query("DELETE FROM " . DB::table('friendrequest') . " WHERE $condition");
		return DB::affected_rows();
	} else {
		return 0;
	}
}

?>
