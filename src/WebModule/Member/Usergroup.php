<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Usergroup.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Usergroup extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$this->title = $title = lang('member', 'member_usergroup');
		$uid = $this->uid;
		$do = trim($this->request->query('do'));
		$inajax = phpcom::$G['inajax'];
		$docurrents = array('basic' => '', 'buy' => '', 'apply' => '');
		if ($do == 'apply') {
			$docurrents['apply'] = ' class="current"';
		} else {
			$docurrents['basic'] = ' class="current"';
		}
		$permlang = lang('misc');
		$basicarray = array('access', 'allowsearch', 'viewmember', 'viewuserip', 'usersign', 'message', 'vote', 'feedback', 'favorites', 'favmax', 'friend', 'friendmax', 'allowdown');
		$postarray = array('comment', 'article', 'softwore', 'video', 'photo', 'downnocredits', 'readnocredits', 'postnoaudit', 'commentnoaudit');
		$attacharray = array('allowdownattach', 'allowupload', 'remoteimage', 'maxattachsize', 'dayattachsize', 'dayattachnum', 'attachext');
		$permarray = array_merge($basicarray, $postarray, $attacharray);
		$permlist = $sidegroup = array();
		$groupexpirynew = $alt = 0;
		$usermaxdays = 0;
		$maingroupid = phpcom::$G['member']['groupid'];
		$sidegroupid = intval($this->request->query('groupid'));
		$sidegroupid = $sidegroupid == $maingroupid ? 0 : $sidegroupid;
		$groupextids = phpcom::$G['member']['groupextids'] ? explode("\t", phpcom::$G['member']['groupextids']) : array();
		if (!empty($groupextids)) {
			$sidegroupid = $sidegroupid ? $sidegroupid : intval($groupextids[0]);
		}
		$sidegroupid = $sidegroupid ? $sidegroupid : 6;
		if (!DB::fetch_first("SELECT * FROM " . DB::table('usergroup') . " WHERE groupid='$sidegroupid'")) {
			$sidegroupid = 6;
		}

		$creditstrans_title = phpcom::$setting['creditstrans']['title'];
		$creditstrans_unit = phpcom::$setting['creditstrans']['unit'];
		$creditstransunit = $creditstrans_unit . $creditstrans_title;
		$tracredits = phpcom::$G['member'][phpcom::$setting['creditstrans']['field']];
		$usermoney = phpcom::$G['member'][phpcom::$setting['creditstrans']['field']];
		$paramextra = array('type' => 'alert', 'showdialog' => TRUE, 'location' => FALSE);
		
		$sidegroup = phpcom_cache::get("usergroup_$sidegroupid");
		
		if (in_array($do, array('buy', 'exit'))) {
			$groupid = intval($this->request->query('groupid'));
			$group = DB::fetch_first("SELECT groupid, adminrid, type, grouptitle, buyable, price, mindays FROM " . DB::table('usergroup') . " WHERE groupid='$groupid' AND type='special' AND buyable='1' AND adminrid='0'");
			if (empty($group)) {
				showmessage('usergroup_not_found', NULL, NULL, $paramextra);
			}
			if (!isset(phpcom::$setting['creditstrans']) || empty(phpcom::$setting['creditstrans'])) {
				showmessage('credits_transaction_disabled', NULL, NULL, $paramextra);
			}
			if ($group['price'] > -1 && $group['mindays'] < 1) {
				$group['mindays'] = 1;
			}
			$creditsfield = phpcom::$setting['creditstrans']['field'];
			if (!checksubmit(array('buysubmit'))) {
				$usermaxdays = $group['price'] > 0 ? intval($usermoney / $group['price']) : 0;
				$group['minamount'] = $group['price'] * $group['mindays'];
			} else {
				$groupterms = unserialize(DB::result_first("SELECT groupterms FROM " . DB::table('member_status') . " WHERE uid='$uid'"));
				if ($do == 'buy') {
					$groupextidsarray = array();
					foreach (array_unique(array_merge($groupextids, array($groupid))) as $groupextid) {
						if ($groupextid) {
							$groupextidsarray[] = $groupextid;
						}
					}
					$groupextidsnew = implode("\t", $groupextidsarray);
					if ($group['price']) {
						if (($days = intval($this->request->query('days'))) < $group['mindays']) {
							showmessage('usergroups_mindays_invalid', NULL, array('mindays' => $group['mindays']), $paramextra);
						}
						if ($usermoney - ($amount = $days * $group['price']) < ($minbalance = 0)) {
							showmessage('credits_balance_insufficient', NULL, array('title' => $creditstrans_title, 'minbalance' => $minbalance), $paramextra);
						}
						$groupterms['ext'][$groupid] = ($groupterms['ext'][$groupid] > TIMESTAMP ? $groupterms['ext'][$groupid] : TIMESTAMP) + $days * 86400;
						DB::query("UPDATE " . DB::table('members') . " SET groupextids='$groupextidsnew' WHERE uid='$uid'");
						update_membercount($uid, array($creditsfield => "-$amount"), TRUE, 'UGP', $groupid);

						DB::query("UPDATE " . DB::table('member_status') . " SET groupterms='" . addslashes(serialize($groupterms)) . "' WHERE uid='$uid'");
					} else {
						DB::query("UPDATE " . DB::table('members') . " SET groupextids='$groupextidsnew' WHERE uid='$uid'");
					}
					$paramextra['type'] = 'succeed';
					showmessage('usergroups_buy_succeed', "member.php?action=usergroup&do=apply", array('group' => $group['grouptitle']), $paramextra);
					return 0;
				} else {
					if (!isset($groupterms['ext']) || !array_key_exists($groupid, $groupterms['ext'])) {
						showmessage('usergroups_exit_wrong', NULL, NULL, $paramextra);
					}
					if ($groupid != phpcom::$G['groupid']) {
						if (isset($groupterms['ext'][$groupid])) {
							unset($groupterms['ext'][$groupid]);
						}
						DB::query("UPDATE " . DB::table('member_status') . " SET groupterms='" . addslashes(serialize($groupterms)) . "' WHERE uid='$uid'");
					} else {
						showmessage('usergroups_exit_failed', NULL, array('group' => $group['grouptitle']), $paramextra);
					}
					$groupextidsarray = array();
					foreach ($groupextids as $groupextid) {
						if ($groupextid && $groupextid != $groupid) {
							$groupextidsarray[] = $groupextid;
						}
					}
					$groupextidsnew = implode("\t", array_unique($groupextidsarray));
					DB::query("UPDATE " . DB::table('members') . " SET groupextids='$groupextidsnew' WHERE uid='$uid'");
					$paramextra['type'] = 'succeed';
					showmessage('usergroups_exit_succeed', "member.php?action=usergroup&do=apply", array('group' => $group['grouptitle']), $paramextra);
					return 0;
				}
			}
		} elseif ($do == 'switch') {
			$groupid = intval($this->request->query('groupid'));
			if (!in_array($groupid, $groupextids)) {
				showmessage('usergroup_not_found', NULL, NULL, $paramextra);
			}
			if (in_array(phpcom::$G['groupid'], array(4, 5)) && phpcom::$G['member']['groupexpiry'] > 0 && phpcom::$G['member']['groupexpiry'] > TIMESTAMP) {
				showmessage('usergroup_switch_not_allow', NULL, NULL, $paramextra);
			}
			$group = DB::fetch_first("SELECT * FROM " . DB::table('usergroup') . " WHERE groupid='$groupid'");
			if (checksubmit(array('groupsubmit'))) {
				$groupterms = unserialize(DB::result_first("SELECT groupterms FROM " . DB::table('member_status') . " WHERE uid='$uid'"));
				$groupextidsnew = phpcom::$G['groupid'];
				$groupexpirynew = isset($groupterms['ext'][$groupid]) ? intval($groupterms['ext'][$groupid]) : 0;
				foreach ($groupextids as $groupextid) {
					if ($groupextid && $groupextid != $groupid) {
						$groupextidsnew .= "\t" . $groupextid;
					}
				}
				DB::query("UPDATE " . DB::table('members') . " SET groupid='$groupid', adminid='$group[adminrid]', groupexpiry='$groupexpirynew', groupextids='$groupextidsnew' WHERE uid='$uid'");
				$paramextra['type'] = 'succeed';
				showmessage('usergroups_switch_succeed', "member.php?action=usergroup&do=apply", array('group' => $group['grouptitle']), $paramextra);
				return 0;
			}
		} else {
			$usergroups = array();
			foreach (phpcom::$G['usergroup'] as $group) {
				$usergroups[$group['type']][$group['groupid']] = $group['grouptitle'];
			}
			$sidegroup = phpcom_cache::get("usergroup_$sidegroupid");
			if (!$this->request->query('groupid')) {
				$currentgroup = phpcom::$G['group']['type'];
			} else {
				$currentgroup = $maingroupid == $this->request->query('groupid') ? phpcom::$G['group']['type'] : $sidegroup['type'];
			}

		}
		
		if ($do == 'apply') {
			$expgrouparray = $expirylist = $termsarray = array();
			$groupterms = @unserialize(DB::result_first("SELECT groupterms FROM " . DB::table('member_status') . " WHERE uid='$uid'"));
			if (!empty($groupterms['ext']) && is_array($groupterms['ext'])) {
				$termsarray = $groupterms['ext'];
			}
			if (!empty($groupterms['main']['time']) && (empty($termsarray[phpcom::$G['groupid']]) || $termsarray[phpcom::$G['groupid']] > $groupterms['main']['time'])) {
				$termsarray[phpcom::$G['groupid']] = $groupterms['main']['time'];
			}

			foreach ($termsarray as $expgroupid => $expiry) {
				if ($expiry <= TIMESTAMP) {
					$expgrouparray[] = $expgroupid;
				}
			}
			if (!empty($groupterms['ext'])) {
				foreach ($groupterms['ext'] as $groupextid => $time) {
					$expirylist[$groupextid] = array('time' => fmdate($time, 'd'), 'type' => 'ext');
				}
			}
			if (!empty($groupterms['main'])) {
				$expirylist[phpcom::$G['groupid']] = array('time' => fmdate($groupterms['main']['time'], 'd'), 'type' => 'main');
			}
			$groupids = array();
			foreach (phpcom::$G['usergroup'] as $groupid => $usergroup) {
				if (!empty($usergroup['buyable'])) {
					$groupids[] = $groupid;
				}
			}
			
			$expiryids = array_keys($expirylist);
			if (!$expiryids && phpcom::$G['member']['groupexpiry']) {
				DB::query("UPDATE " . DB::table('members') . " SET groupexpiry='0' WHERE uid='$uid'");
			}

			$groupids = array_merge($groupextids, $expiryids, $groupids);
			if ($groupids) {
				$query = DB::query("SELECT groupid, adminrid, type, grouptitle, buyable, price, mindays FROM " . DB::table('usergroup') . " WHERE groupid IN (" . implodeids($groupids) . ")");
				while ($group = DB::fetch_array($query)) {
					if ($group['buyable']) {
						$expirylist[$group['groupid']]['dailyprice'] = $group['price'];
						$expirylist[$group['groupid']]['usermaxdays'] = $group['price'] > 0 ? intval($usermoney / $group['price']) : 0;
					} else {
						$expirylist[$group['groupid']]['dailyprice'] = 0;
						$expirylist[$group['groupid']]['usermaxdays'] = 0;
					}
					$expirylist[$group['groupid']]['maingroup'] = $group['type'] != 'special' || $group['buyable'] == 0 || $group['adminrid'] > 0;
					$expirylist[$group['groupid']]['grouptitle'] = in_array($group['groupid'], $expgrouparray) ? '<s>' . $group['grouptitle'] . '</s>' : $group['grouptitle'];
					if(!isset($expirylist[$group['groupid']]['time'])){
						$expirylist[$group['groupid']]['time'] = '';
					}
				}
			}
			
			$memcredits = phpcom::$G['member']['credits'];
			if ($expgrouparray) {
				$groupextidarray = array();
				foreach ($groupextids as $groupextid) {
					if (($groupextid = intval($groupextid)) && !in_array($groupextid, $expgrouparray)) {
						$groupextidarray[$groupextid] = $groupextid;
					}
				}
				$groupidnew = phpcom::$G['groupid'];
				$adminidnew = phpcom::$G['adminid'];
				
				foreach ($expgrouparray as $expgroupid) {
					if ($expgroupid == phpcom::$G['groupid']) {
						if (!empty($groupterms['main']['groupid'])) {
							$groupidnew = $groupterms['main']['groupid'];
							$adminidnew = $groupterms['main']['adminid'];
							$groupexpirynew = intval($groupterms['main']['time']);
						} else {
							$groupidnew = DB::result_first("SELECT groupid FROM " . DB::table('usergroup') . " WHERE type='member' AND '$memcredits'>=mincredits AND '$memcredits'<maxcredits LIMIT 1");
							if(empty($groupidnew)){
								$groupidnew = phpcom::$G['groupid'];
							}
							if (!empty($groupextidarray) && in_array(phpcom::$G['adminid'], array(1, 2, 3))) {
								$query = DB::query("SELECT groupid FROM " . DB::table('usergroup') . " WHERE groupid IN (" . implodeids($groupextidarray) . ") AND adminrid='" . phpcom::$G['adminid'] . "' LIMIT 1");
								$adminidnew = (DB::num_rows($query)) ? phpcom::$G['adminid'] : 0;
							} else {
								$adminidnew = 0;
							}
							$groupexpirynew = 0;
						}
						unset($groupterms['main']);
					}
					unset($groupterms['ext'][$expgroupid]);
				}
				if (isset($groupextidarray[$groupidnew])) {
					unset($groupextidarray[$groupidnew]);
				}
				if ($this->checkisFounder(phpcom::$G['member'])) {
					$groupidnew = $adminidnew = 1;
					$groupexpirynew = 0;
					if (isset($groupterms['ext'][1])) {
						unset($groupterms['ext'][1]);
					}
					if (isset($groupextidarray[1])) {
						unset($groupextidarray[1]);
					}
				}
				$groupextidsnew = '';
				if ($groupextidarray) {
					$groupextidsnew = implode("\t", $groupextidarray);
				}
				$grouptermsnew = addslashes(serialize($groupterms));
				DB::query("UPDATE " . DB::table('members') . " SET adminid='$adminidnew', groupid='$groupidnew', groupexpiry='$groupexpirynew', groupextids='$groupextidsnew' WHERE uid='$uid'");
				DB::query("UPDATE " . DB::table('member_status') . " SET groupterms='$grouptermsnew' WHERE uid='$uid'");
			}
		} else {
			$maingroup = phpcom::$G['group'];
			foreach ($permarray as $value) {
				$permlist[$value]['alt'] = $alt ? ' class="alt"' : '';
				$alt = $alt ? 0 : 1;
				$permlist[$value]['title'] = $permlang["permissions_$value"];
				$permlist[$value]['main'] = isset($maingroup[$value]) ? $maingroup[$value] : '';
				$permlist[$value]['side'] = $sidegroup[$value];
				if ($value == 'access') {
					$permlist[$value]['main'] = $maingroup[$value] ? '1' : '0';
					$permlist[$value]['side'] = $sidegroup[$value] ? '1' : '0';
				}
				if ($value == 'maxattachsize' || $value == 'dayattachsize') {
					if ($maingroup[$value]) {
						$permlist[$value]['main'] = formatbytes($maingroup[$value]);
					} else {
						$permlist[$value]['main'] = $permlang['permissions_nolimit'];
					}
					if ($sidegroup[$value]) {
						$permlist[$value]['side'] = formatbytes($sidegroup[$value]);
					} else {
						$permlist[$value]['side'] = $permlang['permissions_nolimit'];
					}
				}
				if ($value == 'dayattachnum' || $value == 'friendmax' || $value == 'favmax') {
					if (empty($maingroup[$value])) {
						$permlist[$value]['main'] = $permlang['permissions_nolimit'];
					}
					if (empty($sidegroup[$value])) {
						$permlist[$value]['side'] = $permlang['permissions_nolimit'];
					}
				}
			}

			$groupterms = unserialize(DB::result_first("SELECT groupterms FROM " . DB::table('member_status') . " WHERE uid='$uid'"));
			if (in_array($sidegroup['groupid'], $groupextids)) {
				if ($groupterms && isset($groupterms['ext'][$sidegroup['groupid']])) {
					$groupexpiry = fmdate($groupterms['ext'][$sidegroup['groupid']], 'd');
				} else {
					$groupexpiry = $permlang['permissions_nolimit'];
				}
			}
		}
		include template('member/usergroup');
		return 1;
	}

	protected function checkisFounder($user) {
		$founders = str_replace(' ', '', phpcom::$config['admincp']['founder']);
		if (!$user['uid']) {
			return false;
		} elseif (strexists(",$founders,", ",$user[uid],")) {
			return true;
		} elseif (!is_numeric($user['username']) && strexists(",$founders,", ",$user[username],")) {
			return true;
		} else {
			return false;
		}
	}
}
?>