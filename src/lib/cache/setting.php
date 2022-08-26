<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : setting.php    2011-4-3 17:45:31
 */
!defined('IN_PHPCOM') && exit('Access denied');

function build_cache_setting($channelid = 0) {
    unset($channelid);
    $data = array();
    $skipkeys = array('backupdir', 'backuptable');
    $query = DB::query('SELECT skey,svalue,stype FROM ' . DB::table('setting') . ' WHERE skey NOT IN(' . implodevalue($skipkeys) . ')');
    while ($setting = DB::fetch_array($query)) {
        if ($setting['stype'] == 'array') {
            $setting['svalue'] = unserialized($setting['svalue']);
        } elseif ($setting['skey'] == 'creditsformula') {
            if ($setting['svalue']) {
                $setting['svalue'] = preg_replace("/(money|prestige|currency|praise|polls|friends|digests|posts|threads)/", "\$member['\\1']", $setting['svalue']);
            }
        } elseif ($setting['skey'] == 'onlinehold') {
            $setting['svalue'] = $setting['svalue'] * 60;
        } elseif ($setting['skey'] == 'attachdir') {
            $setting['svalue'] = preg_replace("/\.asp|\\0/i", '0', $setting['svalue']);
            $setting['svalue'] = str_replace('\\', '/', substr($setting['svalue'], 0, 2) == './' ? PHPCOM_ROOT . '/' . $setting['svalue'] : $setting['svalue']);
            $setting['svalue'] = str_replace(array('/./', '//'), '/', rtrim($setting['svalue'], '/\ ') . '/');
        } elseif ($setting['skey'] == 'attachurl') {
            $setting['svalue'] = str_replace('/./', '/', trim($setting['svalue'], '/\ ') . '/');
        } elseif ($setting['skey'] == 'allowattachext') {
            $setting['svalue'] = $setting['svalue'] ? trim(str_replace(array(';', '|', ' '), array(',', ',', ''), $setting['svalue']), ',') : 'jpg,jpeg,gif,png,zip,rar';
            $setting['svalue'] = strtolower($setting['svalue']);
        } elseif ($setting['skey'] == 'attachmaxsize') {
            $setting['svalue'] = max(0, $setting['svalue']);
        }
        phpcom::$setting[$setting['skey']] = $data[$setting['skey']] = $setting['svalue'];
    }
    DB::free_result($query);
    if(empty($data['attachsubdir'])){
    	$data['attachsubdir'] = 'Y/md';
    }
    $data['creditspolicy'] = array_merge($data['creditspolicy'], getcach_setting_creditspolicy());
    $data['website'] = trim($data['website'], '/ \\');
    if(!isset($data['watermark']['gravity'])) $data['watermark']['gravity'] = 1;
    if ($data['watermark']['type'] == 'text' && $data['watermark']['text']) {
        if ($data['watermark']['text'] && strtoupper(CHARSET) != 'UTF-8') {
            $data['watermark']['text'] = convert_encoding($data['watermark']['text'], CHARSET, 'UTF-8', TRUE);
        }
        $data['watermark']['text'] = bin2hex($data['watermark']['text']);
        if (file_exists(PHPCOM_ROOT . '/misc/images/font/en/' . $data['watermark']['fontpath'])) {
            $data['watermark']['fontpath'] = 'misc/images/font/en/' . $data['watermark']['fontpath'];
        } elseif (file_exists(PHPCOM_ROOT . '/misc/images/font/cn/' . $data['watermark']['fontpath'])) {
            $data['watermark']['fontpath'] = 'misc/images/font/cn/' . $data['watermark']['fontpath'];
        } else {
            $data['watermark']['fontpath'] = 'misc/images/font/' . $data['watermark']['fontpath'];
        }
        $data['watermark']['fontcolor'] = hexrgbcolor($data['watermark']['fontcolor'], false);
        $data['watermark']['shadowcolor'] = hexrgbcolor($data['watermark']['shadowcolor'], false);
    } else {
        $data['watermark']['text'] = '';
        $data['watermark']['fontpath'] = '';
        $data['watermark']['fontcolor'] = '';
        $data['watermark']['shadowcolor'] = '';
    }
    if ($data['ftp']['password']) {
        $data['ftp']['password'] = $data['ftp']['password'] = encryptstring($data['ftp']['password'], md5(phpcom::$config['security']['key']));
    }
    if ($data['ftp']['attachurl']) {
        $data['ftp']['attachurl'] = str_replace('/./', '/', trim($data['ftp']['attachurl'], '/\ ') . '/');
    }
    if ($data['ftp']['allowext']) {
        $data['ftp']['allowext'] = explode(',', $data['ftp']['allowext']);
    } else {
        $data['ftp']['allowext'] = array();
    }
    if ($data['ftp']['disallowext']) {
        $data['ftp']['disallowext'] = explode(',', $data['ftp']['disallowext']);
    } else {
        $data['ftp']['disallowext'] = array();
    }
    $string = ',' . $data['colorvalue'];
    $data['colorvalue'] = explode(',', strtolower($string));
    $data['fontvalue'] = array(
        0 => '',
        1 => 'font-weight:bold;',
        2 => 'font-style:italic;',
        3 => 'text-decoration:underline;',
        4 => 'font-weight:bold;font-style:italic;',
        5 => 'font-weight:bold;text-decoration:underline;',
        6 => 'font-style:italic;text-decoration:underline;',
        7 => 'font-weight:bold;font-style:italic;text-decoration:underline;'
    );
    if (isset($data['subtableids'])) {
        unset($data['subtableids']);
    }
    if (isset($data['subtableinfo'])) {
        unset($data['subtableinfo']);
    }
    if(empty($data['captchastatus'])){
    	$data['captchastatus'] = array(0,0,1,0,0,1,0,0,0);
    }
    if(empty($data['questionstatus'])){
    	$data['questionstatus'] = array(0,0,0,0,0,0,0,0,0);
    }
    if(empty($data['instdir'])){
    	$data['instdir'] = '/';
    }
    
    $data['absoluteurl'] = empty($data['absoluteurl']) ? 0 : intval($data['absoluteurl']);
    $data['uricheck'] = empty($data['uricheck']) ? 0 : intval($data['uricheck']);
    $data['hotminimum'] = empty($data['hotminimum']) ? 100 : intval($data['hotminimum']);
    $data['latestdays'] = empty($data['latestdays']) ? 7 : intval($data['latestdays']);

    phpcom_cache::save('setting', $data);
    phpcom::$setting = $data;
}

function getcach_setting_creditspolicy() {
    $data = array();
    $query = DB::query("SELECT * FROM " . DB::table('credit_rules') . " WHERE operation IN ('promotion', 'download')");
    while ($rules = DB::fetch_array($query)) {
        $existrule = FALSE;
        if ($rules['money'] || $rules['prestige'] || $rules['currency'] || $rules['praise']) {
            $existrule = TRUE;
        }
        $data[$rules['operation']] = $existrule;
    }
    return $data;
}

?>
