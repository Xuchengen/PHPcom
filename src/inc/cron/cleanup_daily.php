<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : cleanup_daily.php    2012-1-19
 */
!defined('IN_PHPCOM') && exit('Access denied');

DB::query("UPDATE " . DB::table('member_count') . " SET todayattachs='0', todayattachsize='0'");

$timestamp = phpcom::$G['timestamp'];
DB::query("DELETE FROM " . DB::table('notification') . " WHERE uid>'0' AND flag='0' AND dateline<'$timestamp'-172800", 'UNBUFFERED');
$delattachids = array();
$query = DB::query("SELECT attachid, module, attachment, remote, thumb, preview FROM " . DB::table('attachment_temp') . " WHERE dateline<'$timestamp'-36000");
while ($attach = DB::fetch_array($query)) {
    Attachment::unlinks($attach);
    $delattachids[] = $attach['attachid'];
}
if (!empty($delattachids)) {
	$deleteids = implodeids($delattachids);
	if($deleteids){
	    DB::query("DELETE FROM " . DB::table('attachment_temp') . " WHERE attachid IN($deleteids)", 'UNBUFFERED');
	    DB::query("DELETE FROM " . DB::table("attachment") . " WHERE attachid IN($deleteids)", 'UNBUFFERED');
	    /*foreach ($delattachids as $module => $value) {
	    	$deleteids = implodeids($value);
	    	if($deleteids){
	    		DB::query("DELETE FROM " . DB::table("attachment_$module") . " WHERE attachid IN($deleteids)", 'UNBUFFERED');
	    	}
	    }*/
	}
}

$deletetmpids = array();
$query = DB::query("SELECT tmpid, dirname, filename, remote, thumb FROM " . DB::table('upload_temp') . " WHERE dateline<'$timestamp'-36000");
while ($tmp = DB::fetch_array($query)) {
	Attachment::uploadUnlink($tmp);
	$deletetmpids[] = $tmp['tmpid'];
}
if (!empty($deletetmpids)) {
	if($deleteids = implodeids($deletetmpids)){
		DB::query("DELETE FROM " . DB::table('upload_temp') . " WHERE tmpid IN($deleteids)", 'UNBUFFERED');
	}
}
$uids = $members = array();
$query = DB::query("SELECT uid, groupid, credits FROM " . DB::table('members') . " WHERE groupid IN ('4', '5') AND groupexpiry>'0' AND groupexpiry<'$timestamp'");
while ($row = DB::fetch_array($query)) {
    $uids[] = $row['uid'];
    $members[$row['uid']] = $row;
}
if ($uids) {
    $query = DB::query("SELECT uid, groupterms FROM " . DB::table('member_status') . " WHERE uid IN (" . implodeids($uids) . ")");
    while ($member = DB::fetch_array($query)) {
        $sql = 'uid=uid';
        $member['groupterms'] = unserialize($member['groupterms']);
        $member['groupid'] = $members[$member['uid']]['groupid'];
        $member['credits'] = $members[$member['uid']]['credits'];
        if (!empty($member['groupterms']['main']['groupid'])) {
            $groupidnew = $member['groupterms']['main']['groupid'];
            $adminidnew = $member['groupterms']['main']['adminid'];
            unset($member['groupterms']['main']);
            unset($member['groupterms']['ext'][$member['groupid']]);
            $sql .= ", groupexpiry='0'";
        } else {
            $groupidnew = DB::result_first("SELECT groupid FROM " . DB::table('usergroup') . " WHERE type='member' AND mincredits<='$member[credits]' AND maxcredits>'$member[credits]'");
            $adminidnew = 0;
            $sql .= ", groupexpiry='0'";
        }
        $sql .= ", adminid='$adminidnew', groupid='$groupidnew'";
        DB::query("UPDATE " . DB::table('members') . " SET $sql WHERE uid='$member[uid]'");
        DB::query("UPDATE " . DB::table('member_status') . " SET groupterms='" . ($member['groupterms'] ? addslashes(serialize($member['groupterms'])) : '') . "' WHERE uid='$member[uid]'");
    }
}

DB::query("UPDATE " . DB::table('card') . " SET status='9' WHERE status='1' AND cleardate<='$timestamp'");

function removedirs($dirname, $keepdir = FALSE) {
    $dirname = str_replace(array("\n", "\r", '..'), array('', '', ''), $dirname);

    if (!is_dir($dirname)) {
        return FALSE;
    }
    $handle = opendir($dirname);
    while (($file = readdir($handle)) !== FALSE) {
        if ($file != '.' && $file != '..') {
            $dir = $dirname . DIRECTORY_SEPARATOR . $file;
            is_dir($dir) ? removedirs($dir) : unlink($dir);
        }
    }
    closedir($handle);
    return !$keepdir ? (@rmdir($dirname) ? TRUE : FALSE) : TRUE;
}

?>
