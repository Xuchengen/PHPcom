<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Message.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Message extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$this->title = $title = lang('member', 'member_message');
		$uid = $this->uid;
		$do = trim($this->request->query('do'));
		$currents = array('send' => '', 'announce' => '', 'private' => '');
		$datalist = array();
		$count = 0;
		$showpage = '';
		$paramextra = array('type' => 'alert', 'showdialog' => TRUE, 'location' => FALSE);

		if ($do == 'send') {
			$currents['send'] = ' class="current"';
			if (checksubmit(array('btnsubmit', 'pmsubmit'))) {
				if (!phpcom::$G['group']['message']) {
					showmessage('pm_send_permission_denied', NULL, NULL, $paramextra);
				}
				$pm_username = trim($this->request->post('username'));
				$messages = trim(strip_tags($this->request->post('subject')));
				$message_body = $this->request->post('message');
				if (empty($pm_username)) {
					showmessage('pm_send_username_undefined', NULL, NULL, $paramextra);
				}
				if (empty($messages)) {
					showmessage('pm_send_subject_undefined', NULL, NULL, $paramextra);
				}
				if (empty($message_body)) {
					showmessage('pm_send_message_undefined', NULL, NULL, $paramextra);
				}
				$messages = checkinput($messages);
				$message_body = checkinput($message_body);
				$uids = array();
				$pm_username = str_replace(array(' ', ';'), ',', $pm_username);
				$username_array = explode(',', $pm_username);
				$sql = "SELECT uid FROM " . DB::table('members') . " WHERE username IN(" . implodein($username_array) . ") LIMIT 10";
				$query = DB::query($sql);
				while ($row = DB::fetch_array($query)) {
					$uids[] = $row['uid'];
				}
				if (empty($uids)) {
					showmessage('pm_send_username_undefined', NULL, NULL, $paramextra);
				}
				$subjectdata = $messagedata = array();
				$subjectdata['senderid'] = phpcom::$G['uid'];
				$subjectdata['sender'] = phpcom::$G['username'];
				$subjectdata['subject'] = $messages;
				$subjectdata['dateline'] = time();
				$subjectdata['flag'] = 1;
				$messagedata['message'] = $message_body;
				$messagedata['dateline'] = time();
				$messagedata['authorid'] = phpcom::$G['uid'];
				foreach ($uids as $touid) {
					if ($touid != phpcom::$G['uid']) {
						$subjectdata['uid'] = $touid;
						$mid = DB::insert('messages', $subjectdata, TRUE);
						if ($mid) {
							$messagedata['mid'] = $mid;
							DB::insert('message_body', $messagedata);
						}
						DB::query("UPDATE " . DB::table('members') . " SET pmnew=pmnew+1 WHERE uid='$touid'");
					}
				}
				$paramextra['type'] = 'succeed';
				showmessage('pm_send_succeed', 'member.php?action=message', NULL, $paramextra);
				return 0;
			} else {

			}
		} elseif ($do == 'del') {
			$backurl = getreferer();
			if (checksubmit(array('btnsubmit', 'delsubmit'))) {
				$pmdelete = $this->request->post('delete');
				$deleteids = array();
				if ($pmdelete) {
					DB::$checkaction = FALSE;
					$table = DB::table('messages');
					$sql = "(SELECT * FROM $table WHERE senderid='$uid' UNION ALL SELECT * FROM $table WHERE uid='$uid') t1";
					$query = DB::query("SELECT * FROM $sql WHERE mid IN(" . implodeids($pmdelete) . ")");
					while ($row = DB::fetch_array($query)) {
						$deleteids[] = $row['mid'];
					}
					if ($deleteids) {
						$mids = implodeids($deleteids);
						DB::delete('messages', "mid IN($mids)");
						DB::delete('message_body', "mid IN($mids)");
					}
				}
			} else {
				$mid = intval($this->request->query('mid'));
				if (empty($mid)) {
					showmessage('undefined_action', NULL);
				}
				if (!$result = DB::fetch_first("SELECT mid, uid, senderid FROM " . DB::table('messages') . " WHERE mid='$mid'")) {
					showmessage('undefined_action', NULL);
				}
				if (phpcom::$G['group']['groupid'] != 1) {
					if ($result['uid'] != $uid && $result['senderid'] != $uid) {
						showmessage('undefined_action', NULL);
					}
				}
				$pmid = intval($this->request->query('pmid'));
				if (empty($pmid)) {
					DB::delete('message_body', "mid='$mid'");
				} else {
					DB::delete('message_body', "pmid='$pmid'");
				}
				$pmcount = DB::result_first("SELECT COUNT(*) FROM " . DB::table('message_body') . " WHERE mid='$mid'");
				if (empty($pmcount)) {
					DB::delete('messages', "mid='$mid'");
					$backurl = "member.php?action=message";
				} else {
					DB::update('messages', array('pmcount' => $pmcount), "mid='$mid'");
				}
			}
			$paramextra['type'] = 'succeed';
			showmessage('pm_delete_succeed', $backurl, NULL, $paramextra);
			return 0;
		} elseif ($do == 'announce') {
			$currents['announce'] = ' class="current"';
			$showpage = '';
			$table = DB::table('message_body');
			$count = DB::result_first("SELECT COUNT(*) FROM $table t1
					LEFT JOIN " . DB::table('messages') . " t2 ON t2.mid=t1.mid WHERE t1.authorid='0'");
			$pagesize = 10;
			$pagecount = @ceil($count / $pagesize);
			$pagenow = max(1, min($pagecount, intval($this->page)));
			$pagestart = floor(($pagenow - 1) * $pagesize);
			$sql = DB::buildlimit("SELECT t1.*, t2.subject FROM $table t1
					LEFT JOIN " . DB::table('messages') . " t2 ON t2.mid=t1.mid
					WHERE t2.uid='0' AND t1.authorid='0' ORDER BY t1.pmid ASC", $pagesize, $pagestart);
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				$row['message'] = bbcode::output($row['message']);
				$row['style'] = '';
				$datalist[] = $row;
			}
			
			$pageurl = "member.php?action=message&do=announce&page={%d}";
			$showpage = $this->paging($pagenow, $pagecount, $pagesize, $count, $pageurl);
		} elseif ($do == 'private') {
			$currents['private'] = ' class="current"';
			$mid = intval($this->request->query('mid'));
			if (empty($mid)) {
				showmessage('undefined_action', NULL);
			}
			if (!$result = DB::fetch_first("SELECT mid, uid, senderid FROM " . DB::table('messages') . " WHERE mid='$mid'")) {
				showmessage('undefined_action', NULL);
			}
			if ($result['uid'] != $uid && $result['senderid'] != $uid) {
				showmessage('undefined_action', NULL);
			}
			$touid = $result['uid'] == $uid ? $result['senderid'] : $result['uid'];
			$mid = $result['mid'];
			if (!$tousername = DB::result_first("SELECT username FROM " . DB::table('members') . " WHERE uid='$touid'")) {
				showmessage('undefined_action', NULL);
			}
			if (checksubmit(array('btnsubmit', 'pmsubmit'))) {
				$message_body = strip_tags($this->request->post('message'));
				if (empty($message_body)) {
					showmessage('pm_send_message_undefined', NULL, NULL, $paramextra);
				}
				$message_body = checkinput($message_body);
				$messages = removetags($message_body, 80);
				$messages = checkinput($messages);
				DB::insert('message_body', array(
				'mid' => $mid,
				'message' => $message_body,
				'dateline' => time(),
				'authorid' => phpcom::$G['uid']
				));
				$time = time();
				DB::query("UPDATE " . DB::table('messages') . " SET subject='$messages', flag=flag+1, pmcount=pmcount+1, dateline='$time' WHERE mid='$mid'");
				DB::query("UPDATE " . DB::table('members') . " SET pmnew=pmnew+1 WHERE uid='$touid'");
				$paramextra['type'] = 'succeed';
				showmessage('pm_send_succeed', "member.php?action=message&do=private&reply=1&mid=$mid", NULL, $paramextra);
				return 0;
			} else {
				if (!$this->request->query('reply')) {
					DB::update('messages', array('flag' => 0), "mid='$mid'");
				}
			}
			$announce = array();
			$showpage = '';
			$table = DB::table('message_body');
			$count = DB::result_first("SELECT COUNT(*) FROM $table WHERE mid='$mid'");
			$pagesize = 10;
			$pagecount = @ceil($count / $pagesize);
			$pagenow = max(1, min($pagecount, intval($this->page)));
			$pagestart = floor(($pagenow - 1) * $pagesize);
			$sql = DB::buildlimit("SELECT t1.*, t2.username FROM $table t1
					LEFT JOIN " . DB::table('members') . " t2 ON t2.uid=t1.authorid
					WHERE t1.mid='$mid' ORDER BY t1.pmid ASC", $pagesize, $pagestart);
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				$row['message'] = bbcode::output($row['message']);
				$row['style'] = '';
				$datalist[] = $row;
			}
			
			$reply = intval($this->request->query('reply'));
			$pageurl = "member.php?action=message&do=private&mid=$mid&reply=$reply&page={%d}";
			$showpage = $this->paging($pagenow, $pagecount, $pagesize, $count, $pageurl);
		} else {
			$currents['private'] = ' class="current"';
			$showpage = '';
			$announce = array();
			$page = $this->page;
			$table = DB::table('messages');
			if ($page == 1) {
				$announce = DB::fetch_first("SELECT * FROM $table WHERE uid='0'");
			}
			$sql = "(SELECT * FROM $table WHERE senderid='$uid' UNION ALL SELECT * FROM $table WHERE uid='$uid') t1";
			DB::$checkaction = FALSE;
			if (!$count = DB::result_first("SELECT COUNT(*) FROM $sql")) {
				if ($announce) {
					$count = 1;
				}
			}
			$pagesize = 20;
			$pagecount = @ceil($count / $pagesize);
			$pagenow = max(1, min($pagecount, intval($page)));
			$pagestart = floor(($pagenow - 1) * $pagesize);
			$sql = DB::buildlimit("SELECT t1.*,m.username FROM $sql LEFT JOIN " . DB::table('members') . " m ON m.uid=t1.uid ORDER BY dateline DESC", $pagesize, $pagestart);
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				$row['touid'] = $row['uid'] == $uid ? $row['senderid'] : $row['uid'];
				$row['style'] = '';
				if ($row['flag']) {
					$row['style'] = 'color:#444;font-weight:bold;';
				}
				$datalist[] = $row;
			}
			DB::$checkaction = TRUE;
			if (phpcom::$G['member']['pmnew']) {
				DB::query("UPDATE " . DB::table('members') . " SET pmnew='0' WHERE uid='$uid'");
			}
			
			$pageurl = "member.php?action=message&page={%d}";
			$showpage = $this->paging($pagenow, $pagecount, $pagesize, $count, $pageurl, 5, 1);
		}
		include template('member/message');
		return 1;
	}
}
?>