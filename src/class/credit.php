<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : credit.php    2012-1-12
 */
!defined('IN_PHPCOM') && exit('Access denied');

class credit {

    var $coef = 1;
    var $extrasql = array();

    public static function &instance() {
        static $instance;
        if (empty($instance)) {
            $instance = new credit();
        }
        return $instance;
    }

    public function executerule($action, $uid = 0, $coef = 1, $update = 1, $fid = 0) {
        $this->coef = $coef;
        $uid = intval($uid ? $uid : phpcom::$G['uid']);
        $fid = $fid ? $fid : (isset(phpcom::$G['fid']) && phpcom::$G['fid'] ? phpcom::$G['fid'] : 0);
        $rules = $this->getrule($action, $fid);
        $enabled = $updatecredit = FALSE;
        $timestamp = phpcom::$G['timestamp'];
        if ($rules) {
            if (!empty($rules['money']) || !empty($rules['prestige']) || !empty($rules['currency']) || !empty($rules['praise'])) {
                $enabled = TRUE;
            }
        }
        if ($enabled) {
            $rulelog = array();
            $fids = $rules['fids'] ? explode(',', $rules['fids']) : array();
            $fid = in_array($fid, $fids) ? $fid : 0;
            $rulelog = $this->getrulelog($rules['ruleid'], $uid, $fid);
            if ($rulelog && $rules['norepeat']) {
                $rulelog['norepeat'] = $rules['norepeat'];
            }
            if ($rules['rewnum'] && $rules['rewnum'] < $coef) {
                $coef = $rules['rewnum'];
            }
            if (empty($rulelog)) {
                $logarray = array(
                    'uid' => $uid,
                    'ruleid' => $rules['ruleid'],
                    'fid' => $fid,
                    'total' => $coef,
                    'cyclecount' => $coef,
                    'dateline' => $timestamp
                );
                if (in_array($rules['timecycle'], array(2, 3))) {
                    $logarray['starttime'] = $timestamp;
                }
                $logarray = $this->addlog_array($logarray, $rules, FALSE);
                
                if ($update) {
                    $logid = DB::insert('credit_rule_log', $logarray, 1);
                }
                $updatecredit = TRUE;
            } else {
                $newcycle = FALSE;
                $logarray = array();
                
                switch ($rules['timecycle']) {
                    case 0: break;
                    case 1:
                    case 4:
                        if ($rules['timecycle'] == 1) {
                            $today = strtotime(fmdate($timestamp, 'Y-m-d'));
                            if ($rulelog['dateline'] < $today && $rules['rewnum']) {
                                $rulelog['cyclecount'] = 0;
                                $newcycle = TRUE;
                            }
                        }
                        if (empty($rules['rewnum']) || $rulelog['cyclecount'] < $rules['rewnum']) {
                            if ($rules['norepeat']) {
                                if (!$newcycle) {
                                    //return FALSE;
                                }
                            }
                            
                            if ($rules['rewnum']) {
                                $remain = $rules['rewnum'] - $rulelog['cyclecount'];
                                if ($remain < $coef) {
                                    $coef = $remain;
                                }
                            }
                            $cyclecount = $newcycle ? $coef : "cyclecount+'$coef'";
                            $logarray = array(
                                'cyclecount' => "cyclecount=$cyclecount",
                                'total' => "total=total+'$coef'",
                                'dateline' => "dateline='$timestamp'"
                            );
                            $updatecredit = TRUE;
                        }
                        break;
                    case 2:
                    case 3:
                        $nextcycle = 0;
                        if ($rulelog['starttime']) {
                            if ($rules['timecycle'] == 2) {
                                $start = strtotime(fmdate($rulelog['starttime'], 'Y-m-d H:00:00'));
                                $nextcycle = $start + $rules['intervaltime'] * 3600;
                            } else {
                                $nextcycle = $rulelog['starttime'] + $rules['intervaltime'] * 60;
                            }
                        }
                        if ($timestamp <= $nextcycle && $rulelog['cyclecount'] < $rules['rewnum']) {
                            if ($rules['norepeat']) {
                                if (!$newcycle) {
                                    //return FALSE;
                                }
                            }
                            if ($rules['rewnum']) {
                                $remain = $rules['rewnum'] - $rulelog['cyclecount'];
                                if ($remain < $coef) {
                                    $coef = $remain;
                                }
                            }
                            $cyclecount = 'cyclecount+' . $coef;
                            $logarray = array(
                                'cyclecount' => "cyclecount=cyclecount+'$cyclecount'",
                                'total' => "total=total+'$coef'",
                                'dateline' => "dateline='$timestamp'"
                            );
                            $updatecredit = TRUE;
                        } elseif ($timestamp >= $nextcycle) {
                            $newcycle = TRUE;
                            $logarray = array(
                                'cyclecount' => "cyclecount=$coef",
                                'total' => "total=total+'$coef'",
                                'dateline' => "dateline='$timestamp'",
                                'starttime' => "starttime='$timestamp'",
                            );
                            $updatecredit = TRUE;
                        }
                        break;
                }
                if ($update) {
                    if ($logarray) {
                        $logarray = $this->addlog_array($logarray, $rules, TRUE);
                        $logid = $rulelog['logid'];
                        DB::query("UPDATE " . DB::table('credit_rule_log') . " SET " . implode(',', $logarray) . " WHERE logid='$logid'");
                    }
                }
            }
        }
        if ($update && ($updatecredit || $this->extrasql)) {
            if (!$updatecredit) {
                $array = array('money', 'prestige', 'currency', 'praise');
                foreach ($array as $value) {
                    if (isset(phpcom::$setting['credits'][$value])) {
                        $rules[$value] = 0;
                    }
                }
            }
            $this->update_creditbyrule($rules, $uid, $coef, $fid);
        }
        $rules['updatecredit'] = $updatecredit;

        return $rules;
    }

    public function update_creditbyrule($rules, $uids = 0, $coef = 1, $fid = 0) {
        $this->coef = intval($coef);
        $fid = $fid ? $fid : (isset(phpcom::$G['fid']) && phpcom::$G['fid'] ? phpcom::$G['fid'] : 0);
        $uids = $uids ? $uids : intval(phpcom::$G['uid']);
        $rules = is_array($rules) ? $rules : $this->getrule($rules, $fid);
        $creditarray = array();
        $updatecredit = false;
        $array = array('money', 'prestige', 'currency', 'praise');
        foreach ($array as $value) {
            if (isset(phpcom::$setting['credits'][$value])) {
                $creditarray[$value] = intval($rules[$value]) * $this->coef;
                $updatecredit = TRUE;
            }
        }
        if ($updatecredit || $this->extrasql) {
            $this->update_membercount($creditarray, $uids, is_array($uids) ? FALSE : TRUE);
        }
    }

    public function update_membercount($arrcredit, $uids = 0, $checkgroup = TRUE) {
        if (!$uids)
            $uids = intval(phpcom::$G['uid']);
        $uids = is_array($uids) ? $uids : array($uids);
        if ($uids && ($arrcredit || $this->extrasql)) {
            if ($this->extrasql)
                $arrcredit = array_merge($arrcredit, $this->extrasql);
            $sql = array();
            $allowkey = array('money', 'prestige', 'currency', 'praise', 'threads', 'digests', 'logins', 'polls', 'friends', 'attachsize', 'todayattachs', 'todayattachsize');
            foreach ($arrcredit as $key => $value) {
                if (!empty($key) && in_array($key, $allowkey)) {
                    $value = intval($value);
                    $sql[] = "$key=$key+'$value'";
                }
            }
            if ($sql) {
                DB::query("UPDATE " . DB::table('member_count') . " SET " . implode(',', $sql) . " WHERE uid IN (" . implodeids($uids) . ")", 'UNBUFFERED');
            }
            if ($checkgroup && count($uids) == 1)
                $this->check_usergroup($uids[0]);
            $this->extrasql = array();
        }
    }

    public function credit_count($uid, $update = TRUE) {
        $credits = 0;
        if ($uid && !empty(phpcom::$setting['creditsformula'])) {
            $member = DB::fetch_first("SELECT * FROM " . DB::table('member_count') . " WHERE uid='$uid'");
            eval("\$credits = round(" . phpcom::$setting['creditsformula'] . ");");
            if ($uid != phpcom::$G['uid']) {
                if ($update && phpcom::$G['member']['credits'] != $credits) {
                    DB::update('members', array('credits' => intval($credits)), array('uid' => $uid));
                    phpcom::$G['member']['credits'] = $credits;
                }
            } elseif ($update) {
                DB::update('members', array('credits' => intval($credits)), array('uid' => $uid));
            }
        }
        return $credits;
    }

    public function check_usergroup($uid) {
        phpcom_cache::load('usergroup');
        $uid = intval($uid ? $uid : phpcom::$G['uid']);
        $groupid = 0;
        if (!$uid) {
            return $groupid;
        }
        if ($uid != phpcom::$G['uid']) {
            $member = DB::fetch_first("SELECT * FROM " . DB::table('members') . " WHERE uid='$uid'");
        } else {
            $member = phpcom::$G['member'];
        }
        if (empty($member)) {
            return $groupid;
        }
        $credits = $this->credit_count($uid, FALSE);
        $updatearray = array();
        $groupid = $member['groupid'];
        $group = phpcom::$G['usergroup'][$member['groupid']];
        if ($member['credits'] != $credits) {
            $updatearray['credits'] = $credits;
            $member['credits'] = $credits;
        }
        $member['credits'] = $member['credits'] == '' ? 0 : $member['credits'];
        $sendnotify = FALSE;
        if (empty($group) || $group['type'] == 'member' && !($member['credits'] >= $group['mincredits'] && $member['credits'] < $group['maxcredits'])) {
            $newgroup = DB::fetch_first("SELECT grouptitle, groupid FROM " . DB::table('usergroup') . " WHERE type='member' AND $member[credits]>=mincredits AND $member[credits]<maxcredits LIMIT 1");
            if (!empty($newgroup)) {
                if ($member['groupid'] != $newgroup['groupid']) {
                    $updatearray['groupid'] = $groupid = $newgroup['groupid'];
                    $sendnotify = TRUE;
                }
            }
        }
        if ($updatearray) {
            DB::update('members', $updatearray, array('uid' => $uid));
        }
        if ($sendnotify) {
            addnotification($uid, 'system', 'user_usergroup', array('usergroup' => '<a href="member.php?action=usergroup">' . $newgroup['grouptitle'] . '</a>'), 1);
        }

        return $groupid;
    }

    public function delete_rulelogbyfid($ruleid, $fid) {
        $fid = intval($fid);
        if ($ruleid && $fid) {
            $logids = array();
            $query = DB::query("SELECT * FROM " . DB::table('credit_rule_log') . " WHERE ruleid='$ruleid' AND fid='$fid'");
            while ($value = DB::fetch_array($query)) {
                $logids[$value['logid']] = $value['logid'];
            }
            if ($logids) {
                DB::query("DELETE FROM " . DB::table('credit_rule_log') . " WHERE logid IN (" . implodeids($logids) . ")");
            }
        }
    }

    public function getrulelog($ruleid, $uid = 0, $fid = 0) {
        $log = array();
        $uid = $uid ? $uid : phpcom::$G['uid'];
        if ($ruleid && $uid) {
            $query = DB::query("SELECT * FROM " . DB::table('credit_rule_log') . " 
                    WHERE uid='$uid' AND ruleid='$ruleid'  AND fid='$fid'");
            $log = DB::fetch_array($query);
        }
        return $log;
    }

    public function addlog_array($logarray, $rules, $issql = 0) {
        $array = array('money', 'prestige', 'currency', 'praise');
        foreach ($array as $value) {
            if (phpcom::$setting['credits'][$value]) {
                $creditnew = intval($rules[$value]) * $this->coef;
                if ($issql) {
                    $logarray[$value] = $value . "='$creditnew'";
                } else {
                    $logarray[$value] = $creditnew;
                }
            }
        }
        return $logarray;
    }

    public function getrule($action, $fid = 0) {
        if (empty($action)) {
            return FALSE;
        }
        $fid = $fid ? $fid : (isset(phpcom::$G['fid']) && phpcom::$G['fid'] ? phpcom::$G['fid'] : 0);
        phpcom_cache::load('creditrules');
        $rules = FALSE;
        if (is_array(phpcom::$G['cache']['creditrules'][$action])) {
            $rules = phpcom::$G['cache']['creditrules'][$action];
            $array = array('money', 'prestige', 'currency', 'praise');
            foreach ($array as $value) {
                if (empty(phpcom::$setting['credits'][$value])) {
                    unset($rules[$value]);
                    continue;
                }
                $rules[$value] = intval($rules[$value]);
                
            }
        }
        return $rules;
    }

}

?>
