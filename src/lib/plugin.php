<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : plugin.php    2012-1-19
 */
!defined('IN_PHPCOM') && exit('Access denied');

function plugin_install($pluginarray, $installtype = '') {
    if (!$pluginarray || !$pluginarray['plugin']['keyname']) {
        return FALSE;
    }
    $plugins = DB::fetch_first("SELECT pluginid,longname FROM " . DB::table('plugins') . " WHERE keyname='{$pluginarray[plugins][keyname]}' LIMIT 1");
    if ($plugins) {
        return FALSE;
    }
    $pluginarray['plugins']['modules'] = unserialize(phpcom::stripslashes($pluginarray['plugins']['modules']));
    $pluginarray['plugins']['modules']['extra']['installtype'] = $installtype;
    if (plugin_languageupdate($pluginarray)) {
        $pluginarray['plugins']['modules']['extra']['langexists'] = 1;
    }
}

function plugin_languageupdate($pluginarray) {
    if (!$pluginarray['language']) {
        return FALSE;
    }
    $pluginarray['language'] = phpcom::stripslashes($pluginarray['language']);
    foreach (array('script', 'template', 'install') as $type) {
        loadcache('pluginlang_' . $type, 1);
        if (!empty($pluginarray['language'][$type . 'lang'])) {
            phpcom::$G['cache']['pluginlang_' . $type][$pluginarray['plugins']['keyname']] = $pluginarray['language'][$type . 'lang'];
        }
        savesyscache('pluginlang_' . $type, phpcom::$G['cache']['pluginlang_' . $type]);
    }
    return TRUE;
}

function plugin_executesql($sql) {
    $tablepre = phpcom::$config['db'][1]['tablepre'];
    $dbcharset = phpcom::$config['db'][1]['charset'];
    $sql = str_replace(array(' pc_', ' `pc_', ' pre_', ' `pre_', "\r"), array(" $tablepre", " `$tablepre", " $tablepre", " `$tablepre", "\n"), $sql);
    $result = array();
    $num = 0;
    foreach (explode(";\n", trim($sql)) as $query) {
        $queries = explode("\n", trim($query));
        foreach ($queries as $query) {
            $result[$num] .= $query[0] == '#' || $query[0] . $query[1] == '--' ? '' : $query;
        }
        $num++;
    }
    unset($sql);
    foreach ($result as $query) {
        $query = trim($query);
        if ($query) {
            if (substr($query, 0, 12) == 'CREATE TABLE') {
                $name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
                DB::query(plugin_createtable($query, $dbcharset));
            } else {
                DB::query($query);
            }
        }
    }
}

function plugin_createtable($sql, $dbcharset) {
    $type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
    $type = in_array($type, array('MYISAM', 'HEAP', 'MEMORY', 'INNODB')) ? $type : 'MYISAM';
    $type = str_replace(array('MYISAM', 'INNODB'), array('MyISAM', 'InnoDB'), $type);
    return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql) .
            (DB::version() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

?>
