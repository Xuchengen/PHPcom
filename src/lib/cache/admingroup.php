<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : admingroup.php    2012-2-1
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_admingroup($channel = 0) {
    unset($channel);
    $allowadmincp = $adminstatus1 = $adminstatus2 = array();
    $founders = phpcom::$config['admincp']['founder'] !== '' ? explode(',', str_replace(' ', '', addslashes(phpcom::$config['admincp']['founder']))) : array();
    if ($founders) {
        $fuid = $fname = array();
        foreach ($founders as $founder) {
            if (is_numeric($founder)) {
                $fuid[] = $founder;
            } else {
                $fname[] = $founder;
            }
        }
        $query = DB::query("SELECT uid, username FROM " . DB::table('members') . " WHERE " . ($fuid ? "uid IN (" . implodeids($fuid) . ")" : '0') . " OR " . ($fname ? "username IN (" . implodevalue($fname) . ")" : '0'));
        while ($founder = DB::fetch_array($query)) {
            $allowadmincp[$founder['uid']] = $founder['username'];
        }
    }
    $query = DB::query('SELECT uid FROM ' . DB::table('adminmember'));
    while ($member = DB::fetch_array($query)) {
        $allowadmincp[$member['uid']] = $member['uid'];
    }
    if($uids = implodeids($allowadmincp)){
	    $query = DB::query('SELECT uid, allowadmin FROM ' . DB::table('members') . " WHERE allowadmin > '0' OR uid IN ($uids)");
	    while ($user = DB::fetch_array($query)) {
	        if (isset($allowadmincp[$user['uid']]) && !getstatus($user['allowadmin'], 1)) {
	            $adminstatus2[$user['uid']] = $user['uid'];
	        } elseif (!isset($allowadmincp[$user['uid']]) && getstatus($user['allowadmin'], 1)) {
	            $adminstatus1[$user['uid']] = $user['uid'];
	        }
	    }
	    if (!empty($adminstatus1)) {
	        DB::query('UPDATE ' . DB::table('members') . ' SET allowadmin=allowadmin & 0xFE WHERE uid IN (' . implodeids($adminstatus1) . ')');
	    }
	    if (!empty($adminstatus2)) {
	        DB::query('UPDATE ' . DB::table('members') . ' SET allowadmin=allowadmin | 1 WHERE uid IN (' . implodeids($adminstatus2) . ')');
	    }
    }
    $data = array();
    $query = DB::query("SELECT * FROM " . DB::table('admingroup') . " ORDER BY admingid");
    while ($group = DB::fetch_array($query)) {
        $group['permission'] = @unserialize($group['permission']);
        $data[$group['admingid']] = $group;
    }
    phpcom_cache::save('admingroup', $data);
}

?>
