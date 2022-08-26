<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : threadlog.php  2013-7-12
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';

admin_header('menu_threadlog');
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('topic');
$isdeletethreadlog = true;

if ($action == 'link') {
	$tid = isset(phpcom::$G['gp_tid']) ? intval(phpcom::$G['gp_tid']) : 0;
	$chanid = isset(phpcom::$G['gp_chanid']) ? intval(phpcom::$G['gp_chanid']) : 0;
	if($tid <= 0){
		admin_message('undefined_action');
	}
	if($chanid){
		if($thread = DB::fetch_first("SELECT t.tid, t.catid, t.chanid, t.dateline, t.url, c.basic, c.codename, c.prefixurl, c.prefix 
			FROM " . DB::table('threads') . " t 
			LEFT JOIN " . DB::table('category') . " c USING(catid)
			WHERE tid='$tid'")){
			if (empty(phpcom::$G['channel'][$thread['chanid']]['domain']) && empty($thread['prefixurl'])) {
				$thread['domain'] = phpcom::$G['instdir'];
			} elseif(empty($thread['prefixurl'])) {
				$thread['domain'] = phpcom::$G['channel'][$row['chanid']]['domain'] . '/';
			}else{
				$thread['domain'] = $thread['prefixurl'] . '/';
			}
			$urlargs = array('chanid' => $thread['chanid'], 'catdir' => $thread['codename'], 'tid' => $thread['tid'],
					'date' => $thread['dateline'], 'cid' => $thread['catid'], 'catid' => $thread['catid'], 'page' => 1);
			if(!empty($thread['prefix'])){
				$urlargs['prefix'] = trim($thread['prefix']);
			}
			if (empty($thread['url'])) {
				$urlargs['name'] = empty($thread['htmlname']) ? $thread['tid'] : trim($row['htmlname']);
				$thread['url'] = geturl('threadview', $urlargs, $thread['domain']);
			}
			
			@header('Location: ' . $thread['url']);
		}else{
			admin_message('threadlog_link_invalid');
		}
	}else{
		admin_message('threadlog_link_invalid');
	}
	
}elseif ($action == 'log') {
	$uid = isset(phpcom::$G['gp_uid']) ? intval(phpcom::$G['gp_uid']) : 0;
	if (!checksubmit(array('btnsubmit', 'submit'))) {
		$date = isset(phpcom::$G['gp_date']) ? trim(phpcom::$G['gp_date']) : 0;
		$username = getmemberuidbyname($uid);
		$adminhtml->table_header('threadlog_detail_header', array('username' => $username));
		$adminhtml->table_td(array(array('threadlog_detail_menu', array('uid' => $uid))), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end();
		$counts = array();
		$count = 0;
		$timestamp = strtotime(date('Ymd'));
		$condition = getDateCondition($date);
		if(empty($condition)) {
			$condition = "dateline>='$timestamp'";
		}else{
			if($uid > 0 && !empty($date) && $date != 'yester' && $date != 'anteayer'){
				$counts = getThreadCounts("$condition AND uid='$uid'");
				$counts['total'] = intval($counts['addedcount'] + $counts['updatecount']);
			}
		}
		if($uid > 0){
			$adminhtml->table_header();
			$adminhtml->table_th(array(
					array('threadlog_date', 'class="left"'),
					array('threadlog_week', 'class="left"'),
					array('threadlog_lasttime', 'class="left"'),
					array('threadlog_total'),
					array('threadlog_added'),
					array('threadlog_update'),
					array('threadlog_article_number'),
					array('threadlog_soft_number'),
					array('threadlog_photo_number'),
					array('threadlog_video_number'),
					array('threadlog_special_number'),
					array('threadlog_extra'),
					array('threadlog_score')
			));
			$weeks = adminlang('weeks');
			$week = adminlang('week');
			$sql = "SELECT * FROM " . DB::table('member_thread_count') . " WHERE $condition AND uid='$uid'";
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				$count += $row['addedcount'] + $row['updatecount'];
				$adminhtml->table_td(array(
						array(fmdate($row['dateline'], 'Y-m-d', 'd'), TRUE),
						array('<em class="c3">' . $week . $weeks[date('w', $row['dateline'])] . '</em>', TRUE),
						array('<em class="c1">' . fmdate($row['lasttime'], 'dt') . '</em>', TRUE),
						array('<em class="c1 fb">' . ($row['addedcount'] + $row['updatecount']) . '</em>', TRUE, 'align="center"'),
						array('<em class="c2 fb">' . $row['addedcount'] . '</em>', TRUE, 'align="center"'),
						array('<em class="c2 fb">' . $row['updatecount'] . '</em>', TRUE, 'align="center"'),
						array('<em class="fb">' . $row['articles'] . '</em>', TRUE, 'align="center"'),
						array('<em class="fb">' . $row['softs'] . '</em>', TRUE, 'align="center"'),
						array('<em class="fb">' . $row['photos'] . '</em>', TRUE, 'align="center"'),
						array('<em class="fb">' . $row['videos'] . '</em>', TRUE, 'align="center"'),
						array('<em class="fb">' . $row['specials'] . '</em>', TRUE, 'align="center"'),
						array('<em class="c2 fb">' . $row['extracount'] . '</em>', TRUE, 'align="center"'),
						array('<em class="c1 fb">' . $row['scores'] . '</em>', TRUE, 'align="center"')
				));
			}
		}
		if(!empty($counts) && $uid > 0){
			$count = $counts['total'];
			$adminhtml->table_td(array(
					array('threadlog_totalup', array('count' => $counts['count']), 'colspan="3" align="center"'),
					array('<em class="c1 fb">' . $counts['total'] . '</em>', TRUE, 'align="center"'),
					array('<em class="c2 fb">' . $counts['addedcount'] . '</em>', TRUE, 'align="center"'),
					array('<em class="c2 fb">' . $counts['updatecount'] . '</em>', TRUE, 'align="center"'),
					array('<em class="fb">' . $counts['articles'] . '</em>', TRUE, 'align="center"'),
					array('<em class="fb">' . $counts['softs'] . '</em>', TRUE, 'align="center"'),
					array('<em class="fb">' . $counts['photos'] . '</em>', TRUE, 'align="center"'),
					array('<em class="fb">' . $counts['videos'] . '</em>', TRUE, 'align="center"'),
					array('<em class="fb">' . $counts['specials'] . '</em>', TRUE, 'align="center"'),
					array('<em class="c2 fb">' . $counts['extracount'] . '</em>', TRUE, 'align="center"'),
					array('<em class="c1 fb">' . $counts['scores'] . '</em>', TRUE, 'align="center"')
			));
		}
		if($uid > 0){
			$adminhtml->table_end();
		}
		
		$adminhtml->table_header();
		$adminhtml->table_th(array(
				array('threadlog_username', 'class="left" noWrap="noWrap"'),
				array('threadlog_date', 'class="left"'),
				array('threadlog_lastdate', 'class="left"'),
				array('threadlog_status', 'class="left"'),
				array('emptychar')
		));
		$condition = getDateCondition($date, '>=', 'log');
		if(empty($condition)) $condition = "dateline>='$timestamp'";
		$logstatus = adminlang('threadlog_status_array');
		$page = phpcom::$G['page'];
		!$count && $count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('member_thread_log') . " WHERE uid='$uid' AND $condition");
		$pagesize = 50;
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		
		$sql = DB::buildlimit("SELECT * FROM " . DB::table('member_thread_log') . " WHERE uid='$uid' AND $condition", $pagesize, $pagestart);
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$adminhtml->table_td(array(
					array('<a href="?m=threadlog&action=log&uid=' . $row['authorid'] . '">' . $username . '</a>', TRUE),
					array(fmdate($row['dateline'], 'dt', 'd'), TRUE),
					array(fmdate($row['lastdate'], 'dt', 'd'), TRUE),
					array($logstatus[$row['status']], TRUE),
					array('<a target="_blank" class="c1 fb" href="?m=threadlog&action=link&tid=' . $row['tid'] . '&chanid=' . $row['chanid'] . '">' . $row['tid'] . '</a>', TRUE, 'align="center"'),
			));
		}
		$showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $count, ADMIN_SCRIPT . "?m=threadlog&action=log&uid=$uid&date=$date&count=$count") . '</var>';
		$adminhtml->table_td(array(
				array($showpage, TRUE, 'colspan="5" align="right" id="pagecode"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end();
		if($isdeletethreadlog && phpcom::$G['founders']){
			$adminhtml->form("m=threadlog&action=log");
			$adminhtml->table_header('threadlog_delete');
			$adminhtml->table_td(array(array('threadlog_delete_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
			$adminhtml->table_td(array(
					array($adminhtml->radio(adminlang('threadlog_delete_option'), 'date', '365', false) . ' ' .
							$adminhtml->textinput('username', $username, 15, null, null, 'threadlog_username_comments') .
							$adminhtml->submit_button('submit', 'btnsubmit', 'button'), TRUE, 'colspan="3"')
			), NULL, FALSE, NULL, NULL, FALSE);
			$adminhtml->table_end('</form>');
		}
	}else{
		if(!$isdeletethreadlog || !phpcom::$G['founders']){
			admin_message('threadlog_delete_invalid');
		}
		$username = isset(phpcom::$G['gp_username']) ? stripstring(phpcom::$G['gp_username']) : null;
		$uid = isset(phpcom::$G['gp_uid']) ? intval(phpcom::$G['gp_uid']) : 0;
		$operation = isset(phpcom::$G['gp_operation']) ? trim(phpcom::$G['gp_operation']) : null;
		$date = isset(phpcom::$G['gp_date']) ? trim(phpcom::$G['gp_date']) : 0;
		if(!empty($username) && empty($uid)) {
			$uid = DB::result_first("SELECT uid FROM " . DB::table('members') . " WHERE username='$username'");
		}
		$date = $date != 7 && $date != 'week' ? $date : null;
		$condition = getDateCondition($date, '<');
		if($condition && $operation === 'delete'){
			if($uid > 0) {
				$condition .= " AND uid='$uid'";
			}
			DB::delete('member_thread_count', $condition);
			admin_succeed('threadlog_delete_succeed', "m=threadlog");
		}elseif($condition){
			$extra = "<input type=\"hidden\" name=\"uid\" value=\"$uid\" />";
			$extra .= "<input type=\"hidden\" name=\"date\" value=\"$date\" />";
			$msgargs = array(
					'form' => TRUE,
					'submit' => TRUE,
					'cancel' => TRUE,
					'action' => '?m=threadlog&action=log&operation=delete&submit=yes'
			);
			admin_showmessage('threadlog_delete_message', null, $msgargs, $extra);
		}else{
			admin_message('threadlog_delete_invalid');
		}
	}
}else{
	if (!checksubmit(array('btnsubmit', 'submit'))) {
		$adminhtml->table_header('threadlog_header');
		$adminhtml->table_td(array(array('threadlog_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_td(array(array('threadlog_menu', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end();
		$adminhtml->table_header();
		$adminhtml->table_th(array(
				array('threadlog_username', 'class="left" noWrap="noWrap"'),
				array('threadlog_date', 'class="left"'),
				array('threadlog_week', 'class="left"'),
				array('threadlog_total'),
				array('threadlog_added'),
				array('threadlog_update'),
				array('threadlog_article_number'),
				array('threadlog_soft_number'),
				array('threadlog_photo_number'),
				array('threadlog_video_number'),
				array('threadlog_special_number'),
				array('threadlog_extra'),
				array('threadlog_score'),
				array('emptychar')
		));
		
		$date = isset(phpcom::$G['gp_date']) ? trim(phpcom::$G['gp_date']) : 0;
		$timestamp = strtotime(date('Ymd'));
		$condition = getDateCondition($date);
		$condition = $condition ? $condition : "dateline='$timestamp'";
		$page = phpcom::$G['page'];
		
		$counts = getThreadCounts($condition);
		$count = intval($counts['count']);
		$counts['total'] = intval($counts['addedcount'] + $counts['updatecount']);
		$pagesize = 50;
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		
		$weeks = adminlang('weeks');
		$week = adminlang('week');
		$sql = DB::buildlimit("SELECT * FROM " . DB::table('member_thread_count') . " WHERE $condition", $pagesize, $pagestart);
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$detail = $adminhtml->edit_word('threadlog_detail', "m=threadlog&action=log&uid={$row['uid']}&date=all");
			$adminhtml->table_td(array(
					array('<a href="?m=threadlog&action=log&uid=' . $row['uid'] . '">' . $row['username'] . '</a>', TRUE),
					array(fmdate($row['dateline'], 'Y-m-d', 'd'), TRUE),
					array('<em class="c3" title="'.fmdate($row['lasttime'], 'dt').'">' . $week . $weeks[date('w', $row['dateline'])] . '</em>', TRUE),
					array('<em class="c1 fb">' . ($row['addedcount'] + $row['updatecount']) . '</em>', TRUE, 'align="center"'),
					array('<em class="c2 fb">' . $row['addedcount'] . '</em>', TRUE, 'align="center"'),
					array('<em class="c2 fb">' . $row['updatecount'] . '</em>', TRUE, 'align="center"'),
					array('<em class="fb">' . $row['articles'] . '</em>', TRUE, 'align="center"'),
					array('<em class="fb">' . $row['softs'] . '</em>', TRUE, 'align="center"'),
					array('<em class="fb">' . $row['photos'] . '</em>', TRUE, 'align="center"'),
					array('<em class="fb">' . $row['videos'] . '</em>', TRUE, 'align="center"'),
					array('<em class="fb">' . $row['specials'] . '</em>', TRUE, 'align="center"'),
					array('<em class="c2 fb">' . $row['extracount'] . '</em>', TRUE, 'align="center"'),
					array('<em class="c1 fb">' . $row['scores'] . '</em>', TRUE, 'align="center"'),
					array($detail, TRUE),
			));
		}
		if($count){
			$adminhtml->table_td(array(
					array('threadlog_totalup', array('count' => $counts['count']), 'colspan="3" align="center"'),
					array('<em class="c1 fb">' . $counts['total'] . '</em>', TRUE, 'align="center"'),
					array('<em class="c2 fb">' . $counts['addedcount'] . '</em>', TRUE, 'align="center"'),
					array('<em class="c2 fb">' . $counts['updatecount'] . '</em>', TRUE, 'align="center"'),
					array('<em class="fb">' . $counts['articles'] . '</em>', TRUE, 'align="center"'),
					array('<em class="fb">' . $counts['softs'] . '</em>', TRUE, 'align="center"'),
					array('<em class="fb">' . $counts['photos'] . '</em>', TRUE, 'align="center"'),
					array('<em class="fb">' . $counts['videos'] . '</em>', TRUE, 'align="center"'),
					array('<em class="fb">' . $counts['specials'] . '</em>', TRUE, 'align="center"'),
					array('<em class="c2 fb">' . $counts['extracount'] . '</em>', TRUE, 'align="center"'),
					array('<em class="c1 fb">' . $counts['scores'] . '</em>', TRUE, 'align="center"'),
					array('', TRUE),
			));
		}
		$showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $count, ADMIN_SCRIPT . "?m=threadlog&date=$date&count=$count") . '</var>';
		$adminhtml->table_td(array(
				array($showpage, TRUE, 'colspan="14" align="right" id="pagecode"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end();
		if($isdeletethreadlog && phpcom::$G['founders']){
			$adminhtml->form("m=threadlog");
			$adminhtml->table_header('threadlog_delete');
			$adminhtml->table_td(array(array('threadlog_delete_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
			$adminhtml->table_td(array(
					array($adminhtml->radio(adminlang('threadlog_delete_option'), 'date', '365', false) . ' ' . 
							$adminhtml->textinput('username', '', 15, null, null, 'threadlog_username_comments') .
							$adminhtml->submit_button('submit', 'btnsubmit', 'button'), TRUE, 'colspan="3"')
			), NULL, FALSE, NULL, NULL, FALSE);
			$adminhtml->table_end('</form>');
		}
	}else{
		if(!$isdeletethreadlog || !phpcom::$G['founders']){
			admin_message('threadlog_delete_invalid');
		}
		$username = isset(phpcom::$G['gp_username']) ? stripstring(phpcom::$G['gp_username']) : null;
		$uid = isset(phpcom::$G['gp_uid']) ? intval(phpcom::$G['gp_uid']) : 0;
		$operation = isset(phpcom::$G['gp_operation']) ? trim(phpcom::$G['gp_operation']) : null;
		$date = isset(phpcom::$G['gp_date']) ? trim(phpcom::$G['gp_date']) : 0;
		if(!empty($username) && empty($uid)) {
			$uid = DB::result_first("SELECT uid FROM " . DB::table('members') . " WHERE username='$username'");
		}
		$date = $date != 7 && $date != 'week' ? $date : null;
		$condition = getDateCondition($date, '<');
		if($condition && $operation === 'delete'){
			if($uid > 0) {
				$condition .= " AND uid='$uid'";
			}
			DB::delete('member_thread_log', $condition);
			DB::delete('member_thread_count', $condition);
			admin_succeed('threadlog_delete_succeed', "m=threadlog");
		}elseif($condition){
			$extra = "<input type=\"hidden\" name=\"uid\" value=\"$uid\" />";
			$extra .= "<input type=\"hidden\" name=\"date\" value=\"$date\" />";
			$msgargs = array(
					'form' => TRUE,
					'submit' => TRUE,
					'cancel' => TRUE,
					'action' => '?m=threadlog&operation=delete&submit=yes'
			);
			admin_showmessage('threadlog_delete_message', null, $msgargs, $extra);
		}else{
			admin_message('threadlog_delete_invalid');
		}
	}
}

admin_footer();

function getDateCondition($date, $operator = '>=', $type = 'count') {
	$condition = null;
	$timestamp = strtotime(date('Ymd'));
	if(strcasecmp($date, 'yester') === 0){
		$yesterday = $timestamp - 86400;
		$condition = "dateline<'$timestamp' AND dateline>='$yesterday'";
		if(strcasecmp($type, 'count') === 0){
			$condition = "dateline='$yesterday'";
		}
	}elseif(strcasecmp($date, 'anteayer') === 0){
		$timestamp -= 86400;
		$yesterday = $timestamp - 86400;
		$condition = "dateline<'$timestamp' AND dateline>='$yesterday'";
		if(strcasecmp($type, 'count') === 0){
			$condition = "dateline='$yesterday'";
		}
	}elseif(strcasecmp($date, 'week') === 0){
		$timestamp = strtotime("last Sunday") + 86400;
		$condition = "dateline$operator'$timestamp'";
	}elseif(strcasecmp($date, 'lastweek') === 0){
		$timestamp = strtotime("last Sunday") + 86400;
		$lastweek = $timestamp - 86400 * 7;
		$condition = "dateline<'$timestamp' AND dateline>='$lastweek'";
	}elseif(strcasecmp($date, 'month') === 0){
		$timestamp = mktime(0, 0, 0, date("m"), 1, date("Y"));
		$condition = "dateline$operator'$timestamp'";
	}elseif(strcasecmp($date, 'lastmonth') === 0){
		$timestamp = mktime(0, 0, 0, date("m"), 1, date("Y"));
		$lastmonth = strtotime("last Month", $timestamp);
		$condition = "dateline<'$timestamp' AND dateline>='$lastmonth'";
	}elseif(strcasecmp($date, 'year') === 0){
		$timestamp = mktime(0, 0, 0, 1, 1, date("Y"));
		$condition = "dateline$operator'$timestamp'";
	}elseif($date == 7){
		$timestamp -= 86400 * 6;
		$condition = "dateline$operator'$timestamp'";
	}elseif($date == 30){
		$timestamp = strtotime("last month", $timestamp);
		$condition = "dateline$operator'$timestamp'";
	}elseif($date == 90){
		$timestamp = strtotime("-3 month", $timestamp);
		$condition = "dateline$operator'$timestamp'";
	}elseif($date == '180'){
		$timestamp = strtotime("-6 month", $timestamp);
		$condition = "dateline$operator'$timestamp'";
	}elseif($date == '365'){
		$timestamp = strtotime("-1 year", $timestamp);
		$condition = "dateline$operator'$timestamp'";
	}
	return $condition;
}

function getThreadCounts($condition){
	$counts = DB::fetch_first("SELECT COUNT(*) as count, SUM(addedcount) as addedcount,
				SUM(updatecount) as updatecount, SUM(extracount) as extracount, 
				SUM(articles) as articles, SUM(softs) as softs, SUM(photos) as photos,
				SUM(videos) as videos, SUM(specials) as specials , SUM(scores) as scores
				FROM " . DB::table('member_thread_count') . " WHERE $condition");
	return $counts;
}
