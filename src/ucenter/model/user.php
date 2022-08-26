<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : user.php    2011-12-9
 */
!defined('IN_UCENTER') && exit('Access Denied');

class usermodel {

    var $db;
    var $base;

    function __construct(&$base) {
        $this->usermodel($base);
    }

    function usermodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_user_by_uid($uid) {
        $arr = $this->db->fetch_first("SELECT * FROM " . UC_DB_TABLEPRE . "members WHERE uid='$uid'");
        return $arr;
    }

    function get_user_by_username($username) {
        $arr = $this->db->fetch_first("SELECT * FROM " . UC_DB_TABLEPRE . "members WHERE username='$username'");
        return $arr;
    }

    function get_user_by_email($email) {
        $arr = $this->db->fetch_first("SELECT * FROM " . UC_DB_TABLEPRE . "members WHERE email='$email'");
        return $arr;
    }

    function check_username($username, $minlen = 3, $maxlen = 15) {
        $regexp = '\xA1\xA1|\xAC\xA3|^Guest|^\xD3\xCE\xBF\xCD|\xB9\x43\xAB\xC8';
        $len = $this->strlength($username);
        if ($len > $maxlen || $len < $minlen || preg_match("/\s+|^c:\\con\\con|[%,\*\"\s\<\>\&]|$regexp/is", $username)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function check_usernamerestrict($username) {
        $banusername = str_replace("\r", '', trim($this->base->setting['banusername']));
        $usernameexp = '/^(' . str_replace(array('\\*', "\n", ' '), array('.*', '|', ''), preg_quote($banusername, '/')) . ')$/i';
        if ($banusername && preg_match($usernameexp, $username)) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function check_usernameexists($username) {
        return $this->db->result_first("SELECT username FROM " . UC_DB_TABLEPRE . "members WHERE username='$username'");
    }

    function check_emailformat($email) {
        return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
    }

    function check_emailrestrict($email) {
        $allowsemail = str_replace("\r", '', trim($this->base->setting['allowsemail']));
        $bannedemail = str_replace("\r", '', trim($this->base->setting['bannedemail']));
        $allowsexp = '/(' . str_replace("\n", '|', preg_quote($allowsemail, '/')) . ')$/i';
        $bannedexp = '/(' . str_replace("\n", '|', preg_quote($bannedemail, '/')) . ')$/i';
        if ($allowsemail || $bannedemail) {
            if (($allowsemail && !preg_match($allowsexp, $email)) || ($bannedemail && preg_match($bannedexp, $email))) {
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            return TRUE;
        }
    }

    function check_emailexists($email, $username = '') {
        $condition = $username !== '' ? "AND username<>'$username'" : '';
        return $this->db->result_first("SELECT email FROM " . UC_DB_TABLEPRE . "members WHERE email='$email' $condition");
    }

    function strlength($str) {
        if (strtolower(UC_CHARSET) != 'utf-8') {
            return strlen($str);
        }
        $count = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $value = ord($str[$i]);
            if ($value > 127) {
                $count++;
                if ($value >= 192 && $value <= 223) $i++;
                elseif ($value >= 224 && $value <= 239) $i = $i + 2;
                elseif ($value >= 240 && $value <= 247) $i = $i + 3;
            }
            $count++;
        }
        return $count;
    }

    function check_intervalregip($ip = '') {
        $regip = empty($ip) ? $this->base->clientip : $ip;
        if (strpos($regip, '%')) {
            $condition = "s.regip LIKE '$regip'";
        } else {
            $condition = "s.regip='$regip'";
        }
        if ($this->base->setting['register']['interval']) {
            $time = $this->base->time;
            $query = $this->db->query("SELECT s.regip FROM " . UC_DB_TABLEPRE . "members m 
                    LEFT JOIN " . UC_DB_TABLEPRE . "member_status s USING(uid) 
                    WHERE $condition AND m.regdate>$time-'" . $this->base->setting['register']['interval'] . "'*3600 LIMIT 1");
            return $this->db->num_rows($query);
        } else {
            return 0;
        }
    }

    function check_limitcount($ip = '') {
        $regip = empty($ip) ? $this->base->clientip : $ip;
        if ($this->base->setting['register']['limitnum']) {
            $time = $this->base->time;
            return $this->db->result_first("SELECT COUNT(*) FROM " . UC_DB_TABLEPRE . "members m 
                    LEFT JOIN " . UC_DB_TABLEPRE . "member_status s USING(uid) 
                    WHERE s.regip='$regip' AND m.regdate>'$time'-86400");
        } else {
            return 0;
        }
    }

    function check_login($username, $password, &$user) {
        $user = $this->get_user_by_username($username);
        if (empty($user['username'])) {
            return -1;
        } elseif ($user['password'] != uc_password_md5($password, $user['salt'])) {
            return -2;
        }
        return $user['uid'];
    }

    function user_synlogin($uid) {
        return '';
    }

    function user_synlogout() {
        return '';
    }

    function get_user_count($sql = '') {
        $data = $this->db->result_first("SELECT COUNT(*) FROM " . UC_DB_TABLEPRE . "members $sql");
        return $data;
    }

    function user_add($username, $password, $email, $uid = 0, $groupid = 11, $questionid = '', $answer = '', $regip = '', $special = 0) {
        $salt = substr(uniqid(rand()), -6);
        $regip = empty($regip) ? $this->base->clientip : $regip;
        $groupid = $groupid > 0 ? $groupid : 11;
        $members = array();
        if ($uid) {
            $members['uid'] = $uid;
        }
        $members['username'] = $username;
        $members['password'] = uc_password_md5($password, $salt);
        $members['email'] = $email;
        $members['adminid'] = $groupid > 3 ? 0 : $groupid;
        $members['special'] = 0;
        $members['groupid'] = $groupid;
        $members['gender'] = 0;
        $members['face'] = '';
        $members['status'] = 0;
        $members['emailstatus'] = 0;
        $members['credits'] = 0;
        $members['timeoffset'] = $this->base->setting['timeoffset'];
        $members['salt'] = $salt;
        $members['regdate'] = $this->base->time;
        $members['qacode'] = $this->qacrypt($questionid, $answer);
        if(!empty($special) && is_numeric($special) && ($special >= -1 && $special <= 255)) {
        	$members['special'] = $special;
        }
        $this->db->query("INSERT INTO " . UC_DB_TABLEPRE . "members SET " . uc_implode_field_value($members));
        $uid = $this->db->insert_id();
        if ($uid) {
            $credits = $this->base->setting['credits'];
            $memberdata = array(
                'uid' => $uid,
                'money' => intval($credits['money']['initcredits']),
                'prestige' => intval($credits['prestige']['initcredits']),
                'currency' => intval($credits['currency']['initcredits']),
                'praise' => intval($credits['praise']['initcredits'])
            );
            $this->db->query("INSERT INTO " . UC_DB_TABLEPRE . "member_count SET " . uc_implode_field_value($memberdata));
            $this->db->query("INSERT INTO " . UC_DB_TABLEPRE . "member_info SET uid='$uid', birthday='0000-00-00'");
            $memberdata = array('uid' => $uid, 'regip' => $regip, 'lastip' => $regip,
                'lastvisit' => $this->base->time, 'lastactivity' => $this->base->time
            );
            $this->db->query("INSERT INTO " . UC_DB_TABLEPRE . "member_status SET " . uc_implode_field_value($memberdata));
        }
        return $uid;
    }

    function user_edit($username, $oldpasswd, $newpasswd, $email, $ignoreold = 0, $questionid = '', $answer = '', $special = 0) {
        $data = $this->db->fetch_first("SELECT uid, username, password, email, salt FROM " . UC_DB_TABLEPRE . "members WHERE username='$username'");

        if (!$ignoreold && $data['password'] != uc_password_md5($oldpasswd, $data['salt'])) {
            return array('status' => -1);
        }
        $sqldata = array();
        $newpasswd && $sqldata['password'] = uc_password_md5($newpasswd, $data['salt']);
        if (!empty($email) && strtolower($data['email']) != strtolower($email)) {
            $sqldata['email'] = $email;
            $sqldata['emailstatus'] = 0;
        }
        if ($questionid !== '') {
            if ($questionid > 0) {
                $sqldata['qacode'] = $this->qacrypt($questionid, $answer);
            } else {
                $sqldata['qacode'] = '';
            }
        }
        if(!empty($special) && is_numeric($special) && ($special >= -1 && $special <= 255)) {
        	$sqldata['special'] = $special;
        }
        if ($sqldata) {
            $sql = uc_implode_field_value($sqldata);
            $this->db->query("UPDATE " . UC_DB_TABLEPRE . "members SET $sql WHERE username='$username'");
            return array('status' => $this->db->affected_rows(), 'password' => $sqldata['password'], 'salt' => $data['salt']);
        } else {
            return array('status' => -7);
        }
    }

    function user_edit_member($uid, $members = array(), $membercounts = array(), $memberinfos = array(), $memberstatus = array(), $password = '', $chkpasswd = FALSE) {
        if (!$uid || (empty($members) && empty($membercounts) && empty($memberinfos))) {
            return 0;
        }
        $data = $this->db->fetch_first("SELECT uid, username, password, email, salt FROM " . UC_DB_TABLEPRE . "members WHERE uid='$uid'");
        if ($data) {
            $uid = $data['uid'];
            if ($chkpasswd && $data['password'] != uc_password_md5($password, $data['salt'])) {
                return -1;
            }
            if (!empty($members)) {
                if (isset($members['uid'])) {
                    unset($members['uid']);
                }
                if (isset($members['username'])) {
                    unset($members['username']);
                }
                if (isset($members['email'])) {
                    if (strtolower($data['email']) != strtolower($members['email'])) {
                        if (!isset($members['emailstatus'])) {
                            $members['emailstatus'] = 0;
                        }
                    } else {
                        unset($members['email']);
                    }
                }
                if (isset($members['salt'])) {
                    unset($members['salt']);
                }
                if (isset($members['groupid'])) {
                    $members['groupid'] = intval($members['groupid']);
                    $members['adminid'] = $members['groupid'] > 3 ? 0 : $members['groupid'];
                    if ($members['groupid'] === 1) {
                        $members['allowadmin'] = 1;
                    }
                }
                if (isset($members['password'])) {
                    if (empty($members['password'])) {
                        unset($members['password']);
                    } else {
                        $members['password'] = uc_password_md5($members['password'], $data['salt']);
                    }
                }
                $sqldata = uc_implode_field_value($members);
                $this->db->query("UPDATE " . UC_DB_TABLEPRE . "members SET $sqldata WHERE uid='$uid'");
            }
            if (!empty($membercounts)) {
                if (isset($membercounts['uid'])) {
                    unset($membercounts['uid']);
                }
                $sqldata = uc_implode_field_value($membercounts);
                $this->db->query("UPDATE " . UC_DB_TABLEPRE . "member_count SET $sqldata WHERE uid='$uid'");
            }
            if (!empty($memberinfos)) {
                if (isset($memberinfos['uid'])) {
                    unset($memberinfos['uid']);
                }
                $sqldata = uc_implode_field_value($memberinfos);
                $this->db->query("UPDATE " . UC_DB_TABLEPRE . "member_info SET $sqldata WHERE uid='$uid'");
            }
            if (!empty($memberstatus)) {
                if (isset($memberstatus['uid'])) {
                    unset($memberstatus['uid']);
                }
                $sqldata = uc_implode_field_value($memberstatus);
                $this->db->query("UPDATE " . UC_DB_TABLEPRE . "member_status SET $sqldata WHERE uid='$uid'");
            }
            return 1;
        } else {
            return 0;
        }
    }

    function user_delete($arruid) {
        $condition = '';
        if (is_array($arruid)) {
            $condition = 'uid IN(' . uc_implodeids($arruid) . ')';
        } else {
            $condition = "uid='$arruid'";
        }
        if ($arruid) {
            foreach (array('members', 'member_count', 'member_info', 'member_status', 'member_validate',
                'onlinetime', 'favorites', 'friends', 'friendrequest', 'notification', 'credit_log') as $table) {
                $this->db->query("DELETE FROM " . UC_DB_TABLEPRE . "$table WHERE $condition");
            }
            if (DB::result_first("SELECT pmsid FROM " . UC_DB_TABLEPRE . "pm_subject WHERE $condition")) {
                DB::query("DELETE t1, t2 FROM " . UC_DB_TABLEPRE . "pm_subject as t1 
                    LEFT JOIN " . UC_DB_TABLEPRE . "pm_message as t2 ON t1.pmsid=t2.pmsid 
                    WHERE t1.$condition");
            }
            $condition = str_replace('uid', 'fuid', $condition);
            $this->db->query("DELETE FROM " . UC_DB_TABLEPRE . "friends WHERE $condition");
            $this->db->query("DELETE FROM " . UC_DB_TABLEPRE . "friendrequest WHERE $condition");
            return $this->db->affected_rows();
        } else {
            return 0;
        }
    }

    function get_user_list($page, $maxrows, $totalnum, $sql) {
        $offset = $this->base->get_limit_offset($page, $maxrows, $totalnum);
        $data = $this->db->fetch_all("SELECT * FROM " . UC_DB_TABLEPRE . "members $sql LIMIT $offset, $maxrows");
        return $data;
    }

    function username2id($arrusernames) {
        $usernames = uc_implodevalue($arrusernames);
        $query = $this->db->query("SELECT uid FROM " . UC_DB_TABLEPRE . "members WHERE username IN($usernames)");
        $arr = array();
        while ($user = $this->db->fetch_array($query)) {
            $arr[] = $user['uid'];
        }
        return $arr;
    }

    function id2username($arruid) {
        $arr = array();
        $uids = uc_implodeids($arruid);
        $query = $this->db->query("SELECT uid, username FROM " . UC_DB_TABLEPRE . "members WHERE uid IN ($uids)");
        while ($user = $this->db->fetch_array($query)) {
            $arr[$user['uid']] = $user['username'];
        }
        return $arr;
    }

    function user_rewardcredit($username, $reward) {
        $username = addslashes(trim(stripslashes($username)));
        $reward = intval($reward);
        if ($username && $reward) {
            $this->db->query("UPDATE " . UC_DB_TABLEPRE . "members SET credits=credits+$reward WHERE username='$username'", 'UNBUFFERED');
        }
    }

    function check_invitecode($invitecode) {
        $validtime = intval($this->base->setting['invite']['validtime']) * 86400;
        $timediff = intval($this->base->time - $validtime);
        $result = $this->db->fetch_first("SELECT id,uid,inviter,groupid,dateline,type FROM " . UC_DB_TABLEPRE . "invitecode WHERE  status='0' AND code='$invitecode' LIMIT 1"); // AND dateline>='$timediff'
        if ($result) {
            if ($result['dateline'] >= $timediff) {
                return $result;
            } else {
                return UC_USER_CHECK_INVITECODE_EXPIRES;
            }
        }
        return UC_USER_INVITECODE_INVALID;
    }

    function qacrypt($questionid, $answer) {
        return $questionid > 0 && $answer != '' ? substr(md5($answer . md5($questionid)), 16, 8) : '';
    }

    function user_update_invite($username, $invite = array()) {
        if ($username && $invite && is_array($invite)) {
            $reward = intval($this->base->setting['invite']['invitercredit']);
            $groupid = intval($invite['groupid']);
            if ($groupid < 8) {
                $groupid = intval($this->base->setting['invite']['groupid']);
                if ($groupid < 8 || $groupid > 11) {
                    $groupid = 0;
                }
            }
            $uid = intval($invite['uid']);
            if ($invite['type'] == 1 && $reward && $uid) {
                $this->db->query("UPDATE " . UC_DB_TABLEPRE . "members SET credits=credits+$reward WHERE uid='$uid'", 'UNBUFFERED');
            }
            $reward = intval($this->base->setting['invite']['inviteecredit']);
            if ($reward) {
                $this->db->query("UPDATE " . UC_DB_TABLEPRE . "members SET credits=credits+$reward WHERE username='$username'", 'UNBUFFERED');
            }
            if ($groupid > 7) {
                $this->db->query("UPDATE " . UC_DB_TABLEPRE . "members SET groupid=$groupid WHERE username='$username'", 'UNBUFFERED');
            }
            //$time = $this->base->time;
            $id = intval($invite['id']);
            //$this->db->query("UPDATE " . UC_DB_TABLEPRE . "invitecode SET invitee='$username', usedate='$time', status='1' WHERE id='$id'", 'UNBUFFERED');
            return $id;
        } else {
            return 0;
        }
    }

}

?>
