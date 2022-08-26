<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : db.php    2011-8-14 23:24:45
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';
@set_time_limit(1000);
$db = DB::instance();
$tabletype = $db->version() > '4.1' ? 'Engine' : 'Type';
$tablepre = phpcom::$config['db'][1]['tablepre'];
$dbcharset = phpcom::$config['db'][1]['charset'];
$dbengine = empty(phpcom::$config['db']['engine']) ? 'MyISAM' : trim(phpcom::$config['db']['engine']);
$dbengine = stricmp($dbengine, array('MyISAM', 'InnoDB', 'Aria'), true, 'MyISAM');
$excludetable = array($tablepre . 'phpcom_systemcache', $tablepre . 'phpcom_session');
$query = DB::query("SELECT skey, svalue,stype FROM " . DB::table('setting') . " WHERE skey IN ('backupdir', 'backuptable')");
while ($var = DB::fetch_array($query)) {
    ${$var['skey']} = $var['svalue'];
}

if (!isset($backupdir) || !$backupdir) {
    $backupdir = random(6);
    @mkdir('./data/backup_' . $backupdir, 0777);
    DB::query("REPLACE INTO " . DB::table('setting') . " (skey, svalue, stype) values ('backupdir', '$backupdir', 'string')");
}
$backupdir = 'backup_' . $backupdir;
$current = 'menu_db_backup';
$active = 'first';
if(in_array($action, array('restore', 'optimize', 'dbcheck', 'repair', 'runquery'))){
	$current = 'menu_db_' . $action;
	$active = $action;
}

admin_header('menu_db', $current);
$navarray = array(
    array('title' => 'menu_db_backup', 'url' => '?m=db&action=backup', 'name' => 'first'),
    array('title' => 'menu_db_restore', 'url' => '?m=db&action=restore', 'name' => 'restore'),
    array('title' => 'menu_db_optimize', 'url' => '?m=db&action=optimize', 'name' => 'optimize'),
    array('title' => 'menu_db_repair', 'url' => '?m=db&action=repair', 'name' => 'repair'),
	array('title' => 'menu_db_runquery', 'url' => '?m=db&action=runquery', 'name' => 'runquery')
);
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('tools');
$adminhtml->navtabs($navarray, $active);
if ($action == 'runquery') {
	include_once(dirname(__FILE__) . '/dbupgrade.php');
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$adminhtml->form("m=db&action=runquery");
		$adminhtml->table_header("menu_runquery");
		$adminhtml->table_td(array(array('db_runquery_tips', FALSE, 'colspan="0"')), NULL, FALSE, NULL, NULL, FALSE);
		$runqueryselect = '';
		foreach($upgradeQueries as $key => $query) {
			if(empty($query['action'])) {
				$runqueryselect .= "<optgroup label=\"{$query['comment']}\">";
			} else {
				$runqueryselect .= '<option value="'.$key.'">'.$query['comment'].'</option>';
			}
		}
		if($runqueryselect) {
			$runqueryselect = '<select name="queryselect" style="width:420px">'.$runqueryselect.'</select> ' . adminlang('db_query_upgrade_checkbox');
		}
		$adminhtml->table_td(array(
				array($runqueryselect, TRUE, 'colspan="0"')
		));
		$adminhtml->table_td(array(
				array('<textarea wrap="OFF" rows="15" name="querysql" cols="100" class="textarea" style="width:680px;"></textarea>', TRUE, 'colspan="0"')
		));
		if(!phpcom::$config['admincp']['runquery']){
			$adminhtml->table_td(array(
					array('db_runquery_message', FALSE)
			));
		}else{
			$adminhtml->table_td(array(
					array('db_runquery_compatible', FALSE)
			));
		}
		$adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
		$adminhtml->table_end('</form>');
	}else{
		$upgradeable = empty(phpcom::$G['gp_upgradeable']) ? false : true;
		$queryselect = empty(phpcom::$G['gp_queryselect']) ? false : intval(phpcom::$G['gp_queryselect']);
		$affected_rows = 0;
		if($upgradeable && $queryselect){
			if(($rows = execUpgradeQueries($queryselect)) !== false){
				$affected_rows = $rows;
				$upgradeable = true;
			}else{
				$upgradeable = false;
			}
		}
		
		if(!empty(phpcom::$config['admincp']['runquery'])){
			$querysql = isset(phpcom::$G['gp_querysql']) ? trim(phpcom::$G['gp_querysql']) : null;
			if($querysql){
				$querysql = stripcslashes($querysql);
				$sqlerror = '';
				$sqlquery = sqlsplit(str_replace(array(' {tablepre}', ' pc_', ' `pc_', ' pre_', ' `pre_'),
						array(' '.$tablepre, ' '.$tablepre, ' `'.$tablepre, ' '.$tablepre, ' `'.$tablepre), $querysql));
				
				if($db->version() > '4.1') DB::query("SET NAMES '$dbcharset';\n\n");
				foreach($sqlquery as $sql) {
					if(trim($sql) != '') {
						$sql = !empty(phpcom::$G['gp_compatible']) ? synchtablestruct(trim($sql), $dbcharset, $dbengine) : $sql;
						try {
							DB::query($sql, 'SILENT');
							if($sqlerror = DB::error()) {
								break;
							} else {
								$affected_rows += intval(DB::affected_rows());
							}
						} catch (Exception $e) {
							continue;
						}
					}
				}
				if($sqlerror){
					admin_message('db_runquery_error', "m=db&action=runquery", array('error' => $sqlerror));
				}else{
					admin_succeed('db_runquery_succeed', "m=db&action=runquery", array('rows' => $affected_rows));
				}
			}else{
				if($upgradeable){
					admin_succeed('db_runquery_succeed', "m=db&action=runquery", array('rows' => $affected_rows));
				}else{
					admin_message('db_runquery_invalid');
				}
			}
		}else{
			if($upgradeable){
				admin_succeed('db_runquery_succeed', "m=db&action=runquery", array('rows' => $affected_rows));
			}else{
				admin_message('db_runquery_denied');
			}
		}
	}
} elseif ($action == 'restore') {
    $backuplog = $backupsize = $backupziplog = array();
    $adminhtml->table_header('tips');
    $adminhtml->table_td(array(array('db_restore_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
    $adminhtml->table_end();
    if (isset(phpcom::$G['gp_restore']) && phpcom::$G['gp_restore'] == 'ok') {
        admin_message('db_backup_restore_invalid', 'action=restore&m=db');
    }
    if (!checksubmit('btnsubmit')) {
        $bakdir = PHPCOM_ROOT . '/data/' . $backupdir;
        if (is_dir($bakdir)) {
            $dir = dir($bakdir);
            while ($entry = $dir->read()) {
                $entry = './data/' . $backupdir . '/' . $entry;
                if (is_file($entry)) {
                    if (preg_match("/\.sql$/i", $entry)) {
                        $filesize = filesize($entry);
                        $fp = fopen($entry, 'rb');
                        $identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
                        fclose($fp);
                        $key = preg_replace('/^(.+?)(\-\d+)\.sql$/i', '\\1', basename($entry));
                        $backuplog[$key][$identify[3]] = array(
                            'version' => $identify[1],
                            'type' => $identify[2],
                            'method' => $identify[2],
                            'volume' => $identify[3],
                            'filename' => $entry,
                            'dateline' => filemtime($entry),
                            'size' => $filesize
                        );
                        if(isset($backupsize[$key])){
                        	$backupsize[$key] += $filesize;
                        }else{
                        	$backupsize[$key] = $filesize;
                        }
                    } elseif (preg_match("/\.zip$/i", $entry)) {
                        $filesize = filesize($entry);
                        $backupziplog[] = array(
                            'version' => 'unknown',
                            'type' => 'zip',
                            'method' => '',
                            'volume' => 1,
                            'filename' => $entry,
                            'size' => filesize($entry),
                            'dateline' => filemtime($entry)
                        );
                    }
                }
            }
            $dir->close();
        } else {
            admin_error('db_backup_directory_invalid');
        }
        $adminhtml->form('m=db&step=1', array(array('action', 'restore')), 'name="restoreform" id="restoreform"');
        $adminhtml->table_header('db_backup_file_list', 8, '', 'tableborder', FALSE);
        $adminhtml->table_td(array(
            array('delete', FALSE, 'align="center"'),
            array('filename', FALSE, 'align="left"'),
            array('version', FALSE, 'align="left"'),
            array('time', FALSE, 'align="center"'),
            array('size', FALSE, 'align="center"'),
            array('method', FALSE, 'align="center"'),
            array('volnum', FALSE, 'align="center"'),
            array('operation', FALSE, 'align="center"')
                ), '', FALSE, ' tablerow');
        foreach ($backuplog as $key => $val) {
            $info = $val[1];
            $info['dateline'] = is_int($info['dateline']) ? fmdate($info['dateline'], 'dt') : adminlang('unknown');
            $info['size'] = formatbytes($backupsize[$key]);
            $info['volume'] = count($val);
            $info['method'] = $info['type'] != 'zip' ? (!$info['method'] ? adminlang('db_multivol') : adminlang('db_shell')) : '';
            $datafile_server = '.' . $info['filename'];
            $adminhtml->table_td(array(
                array('<input type="checkbox" class="checkbox" name="delete[]" value="' . $key . '" />', TRUE, 'align="center"'),
                array($key, TRUE, 'align="left"'),
                array($info['version'], TRUE, 'align="left"'),
                array($info['dateline'], TRUE, 'align="center"'),
                array($info['size'], TRUE, 'align="center"'),
                array($info['method'], TRUE, 'align="center"'),
                array($info['volume'], TRUE, 'align="center"'),
                array('<a href="?action=restore&m=db&restore=ok">restore</a>', TRUE, 'align="center"')
            ));
        }
        foreach ($backupziplog as $info) {
            $info['dateline'] = is_int($info['dateline']) ? fmdate($info['dateline'], 'dt') : adminlang('unknown');
            $info['size'] = formatbytes($info['size']);
            $info['volume'] = '';
            $info['method'] = $info['method'] == 'multivol' ? adminlang('db_multivol') : adminlang('db_zip');
            $datafile_server = '.' . $info['filename'];
            $adminhtml->table_td(array(
                array('<input type="checkbox" class="checkbox" name="delete[]" value="' . basename($info['filename']) . '" />', TRUE, 'align="center"'),
                array('<a href="' . $info['filename'] . '">' . substr(strrchr($info['filename'], "/"), 1) . '</a>', TRUE, 'align="left"'),
                array($info['version'], TRUE, 'align="left"'),
                array($info['dateline'], TRUE, 'align="center"'),
                array($info['size'], TRUE, 'align="center"'),
                array($info['method'], TRUE, 'align="center"'),
                array('1', TRUE, 'align="center"'),
                array('<a href="?action=restore&m=db&restore=ok">restore</a>', TRUE, 'align="center"')
            ));
        }

        $adminhtml->table_td(array(
            array($adminhtml->checkall(), TRUE, 'colspan="8"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $btnsubmit = $adminhtml->submit_button('delete');
        $adminhtml->table_td(array(
            array($btnsubmit, TRUE, 'align="center" colspan="8"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end();
    } else {
        if (is_array(phpcom::$G['gp_delete'])) {
            foreach (phpcom::$G['gp_delete'] as $filename) {
                $file_path = './data/' . $backupdir . '/' . str_replace(array('/', '\\'), '', $filename);
                if (is_file($file_path)) {
                    @unlink($file_path);
                } else {
                    $i = 1;
                    while (1) {
                        $file_path = './data/' . $backupdir . '/' . str_replace(array('/', '\\'), '', $filename . '-' . $i . '.sql');
                        if (is_file($file_path)) {
                            @unlink($file_path);
                            $i++;
                        } else {
                            break;
                        }
                    }
                }
            }
            admin_succeed('db_backup_file_delete_succeed', 'action=restore&m=db');
        } else {
            admin_error('db_backup_file_delete_invalid');
        }
    }
} elseif ($action == 'optimize') {
    $totalsize = 0;
    $adminhtml->table_header('tips');
    $adminhtml->table_td(array(array('db_optimize_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
    $adminhtml->table_end();
    $adminhtml->form('m=db', array(array('action', 'optimize')), 'name="optimizeform" id="optimizeform"');
    $adminhtml->table_header('db_optimize_data_table', 7, 'systemgroup', 'tableborder', FALSE);
    $adminhtml->table_td(array(
        array(' ', FALSE, 'align="center"'),
        array('data_table', FALSE, 'align="center"'),
        array('type', FALSE, 'align="center"'),
        array('rows', FALSE, 'align="center"'),
        array('data', FALSE, 'align="center"'),
        array('index', FALSE, 'align="center"'),
        array('frag', FALSE, 'align="center"')
            ), '', FALSE, ' tablerow');
    if (!checksubmit('btnsubmit')) {
        $query = DB::query("SHOW TABLE STATUS LIKE '$tablepre%'", 'SILENT');
        while ($table = DB::fetch_array($query)) {
            if ($table['Data_free'] && $table[$tabletype] == 'MyISAM') {
                $checked = $table[$tabletype] == 'MyISAM' ? 'checked' : 'disabled';
                $adminhtml->table_td(array(
                    array('<input type="checkbox" class="checkbox" name="optimizetables[]" value="' . $table['Name'] . '" ' . $checked . '/>', TRUE, 'align="center"'),
                    array($table['Name'], TRUE, 'align="center"'),
                    array($table[$tabletype], TRUE, 'align="center"'),
                    array($table['Rows'], TRUE, 'align="center"'),
                    array(formatbytes($table['Data_length']), TRUE, 'align="center"'),
                    array(formatbytes($table['Index_length']), TRUE, 'align="center"'),
                    array($table['Data_free'], TRUE, 'align="center"')
                ));
                $totalsize += $table['Data_length'] + $table['Index_length'];
            }
        }
        if (empty($totalsize)) {
            $adminhtml->table_td(array(
                array('db_optimize_done', FALSE, 'colspan="7"')
                    ), NULL, FALSE, NULL, NULL, FALSE);
        } else {
            $adminhtml->table_td(array(
                array($adminhtml->checkall() . ' ' . adminlang('db_optimize_used') . ' ' . formatbytes($totalsize), TRUE, 'colspan="7"')
                    ), NULL, FALSE, NULL, NULL, FALSE);
            $btnsubmit = $adminhtml->submit_button();
            $adminhtml->table_td(array(
                array($btnsubmit, TRUE, 'align="center" colspan="7"')
                    ), NULL, FALSE, NULL, NULL, FALSE);
        }
        
    } else {
    	$tablearray = array();
        $query = DB::query("SHOW TABLE STATUS LIKE '$tablepre%'", 'SILENT');
        while ($table = DB::fetch_array($query)) {
        	$tablearray[] = $table;
        }
        foreach($tablearray as $table){
            if (is_array(phpcom::$G['gp_optimizetables']) && in_array($table['Name'], phpcom::$G['gp_optimizetables'])) {
                DB::query("OPTIMIZE TABLE " . $table['Name']);
                $table['Data_free'] = 0;
            }
            
            $adminhtml->table_td(array(
                array(' ', TRUE, 'align="center"'),
                array($table['Name'], TRUE, 'align="center"'),
                array($db->version() > '4.1' ? $table['Engine'] : $table['Type'], TRUE, 'align="center"'),
                array($table['Rows'], TRUE, 'align="center"'),
                array(formatbytes($table['Data_length']), TRUE, 'align="center"'),
                array(formatbytes($table['Index_length']), TRUE, 'align="center"'),
                array($table['Data_free'], TRUE, 'align="center"')
            ));
            $totalsize += $table['Data_length'] + $table['Index_length'];
        }
        $adminhtml->table_td(array(
            array(adminlang('db_optimize_used') . ' ' . formatbytes($totalsize), TRUE, 'colspan="7"')
                ), NULL, FALSE, NULL, NULL, FALSE);
    }
    $adminhtml->table_end('</form>');
} elseif ($action == 'repair') {
    if (!DB::query("SHOW FIELDS FROM " . DB::table('setting'), 'SILENT')) {
        admin_message('dbcheck_permissions_invalid');
    }
    $adminhtml->table_header('tips');
    $adminhtml->table_td(array(array('db_repair_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
    $adminhtml->table_end();
    $step = isset(phpcom::$G['gp_step']) ? max(1, intval(phpcom::$G['gp_step'])) : 1;
    if (!checksubmit('btnsubmit')) {
        if ($step == 3) {
            $adminhtml->table_header();
            $adminhtml->table_th(array(
                array('data_table', 'class="left"'),
                array('operation', 'class="left"'),
                array('type', 'class="left"'),
                array('size', 'class="left"'),
                array('status', 'class="left"')
            ));
            $tables = fetchtablearray($tablepre);
            foreach ($tables as $table) {
                if ($table[$tabletype] == 'MyISAM' || $table[$tabletype] == 'ARCHIVE') {
                    $row = DB::fetch_first("REPAIR TABLE {$table['Name']} EXTENDED");
                    $adminhtml->table_td(array(
                        array($table['Name'], TRUE),
                        array($row['Op'], TRUE),
                        array($table[$tabletype], TRUE),
                        array($table['Data_length'], TRUE),
                        array($row['Msg_type'] . ': ' . $row['Msg_text'], TRUE)
                    ));
                }
            }
            $adminhtml->table_end();
        } else {
            $adminhtml->form('m=db', array(array('action', 'repair')), 'name="repairform" id="repairform"');
            $adminhtml->table_header('db_repair_data_table');
            $adminhtml->table_td(array(
                array('db_repair_table_ready', FALSE, 'align="center"')
            ));
            $btnsubmit = $adminhtml->submit_button('', '', '', "{if(confirm('" . adminlang('db_repair_submit_tips') . "')){return true;}return false;}");
            $adminhtml->table_td(array(
                array($btnsubmit, TRUE, 'align="center"')
                    ), NULL, FALSE, NULL, NULL, FALSE);
            $adminhtml->table_end('</form>');
        }
    } else {
        admin_message('dbcheck_repair_table', 'action=repair&m=db&step=3', '', 'loading');
    }
} else {
    $adminhtml->table_header('tips');
    $adminhtml->table_td(array(array('db_backup_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
    $adminhtml->table_end();
    $shelldisabled = function_exists('shell_exec') ? '' : 'disabled';
    if (!checksubmit(array('submit', 'btnsubmit'), 1)) {
        $adminhtml->form('m=db&step=1', array(array('action', 'backup')), 'name="backupform" id="backupform"');
        $adminhtml->table_header('db_backup_toggle', 6, '', 'tableborder', FALSE);
        $adminhtml->table_td(array(
            array('check_all', FALSE, 'align="center"'),
            array('data_table', FALSE, 'align="left"'),
            array('type', FALSE, 'align="center"'),
            array('rows', FALSE, 'align="center"'),
            array('data', FALSE, 'align="center"'),
            array('index', FALSE, 'align="center"')
                ), '', FALSE, ' tablerow');
        echo '<tbody id="datatable_tbody" style="display:none">';
        $totalsize = 0;
        $table_array = fetchtablearray($tablepre);
        foreach ($table_array as $table) {
            $adminhtml->table_td(array(
                array('<input type="checkbox" class="checkbox" name="backuptable[]" value="' . $table['Name'] . '" checked="checked" />', TRUE, 'align="center"'),
                array($table['Name'], TRUE, 'align="left"'),
                array($table[$tabletype], TRUE, 'align="center"'),
                array($table['Rows'], TRUE, 'align="center"'),
                array($table['Data_length'], TRUE, 'align="center"'),
                array($table['Index_length'], TRUE, 'align="center"')
            ));
            $totalsize += $table['Data_length'] + $table['Index_length'];
        }
        echo '</tbody>';
        $adminhtml->table_td(array(
            array($adminhtml->checkall('', '', TRUE) . ' ' . adminlang('db_optimize_used') . ' ' . formatbytes($totalsize), TRUE, 'colspan="7"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end();
        $adminhtml->table_header('db_backup_setting', 3);
        $adminhtml->table_setting('db_backup_method', 'method', 0, 'radios');
        $adminhtml->table_setting('db_backup_volumesize', 'volumesize', 2048, 'text');
        $adminhtml->table_setting('db_backup_charset', 'charset', '0', 'radios');
        //$adminhtml->table_setting('db_backup_file_encoding', 'encoding', CHARSET, 'radios');
        $adminhtml->table_setting('db_backup_ifhex', 'hex', 1, 'radio');
        $adminhtml->table_setting('db_backup_compression', 'zip', 0, 'radios');
        $adminhtml->table_setting('db_backup_filename', 'filename', date('Ymd') . '_' . random(8), 'text');
        $btnsubmit = $adminhtml->submit_button();
        $adminhtml->table_td(array(
            array($btnsubmit, TRUE, 'align="center" colspan="3"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        DB::query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');
        $backuptables = array();
        $method = isset(phpcom::$G['gp_method']) ? phpcom::$G['gp_method'] : '';
        $filename = isset(phpcom::$G['gp_filename']) ? phpcom::$G['gp_filename'] : '';
        $sqlcharset = isset(phpcom::$G['gp_charset']) ? phpcom::$G['gp_charset'] : null;
        $fileencoding = isset(phpcom::$G['gp_encoding']) ? phpcom::$G['gp_encoding'] : null;
        $fileencoding = $fileencoding ? $fileencoding : 'utf-8';
        $time = fmdate(TIMESTAMP, 'Y-m-d H:i:s');
        if (!$filename || preg_match("/(\.)(exe|jsp|asp|aspx|php|cgi|fcgi|pl)(\.|$)/i", $filename)) {
            admin_error('db_backup_filename_invalid');
        }
        if (empty(phpcom::$G['gp_step'])) {
            if ($backuptables = DB::fetch_first("SELECT svalue FROM " . DB::table('setting') . " WHERE skey='backuptable'")) {
                $backuptables = unserialize($backuptables['svalue']);
            }
        } else {
            $backuptablesnew = empty(phpcom::$G['gp_backuptable']) ? '' : addslashes(serialize(phpcom::$G['gp_backuptable']));
            DB::query("REPLACE INTO " . DB::table('setting') . " (skey, svalue, stype) VALUES ('backuptable', '$backuptablesnew','array')");
            $backuptables = & phpcom::$G['gp_backuptable'];
        }
        if (!is_array($backuptables) || empty($backuptables)) {
            admin_error('db_backup_tables_invalid');
        }
        $dumpcharset = $sqlcharset ? $sqlcharset : phpcom::$config['db']['1']['charset'];
        $dumpcharset = $dumpcharset ? $dumpcharset : CHARSET;
        if ($db->version() > '4.1') {
            $dbcharset = strtoupper($dbcharset) == 'UTF-8' ? 'UTF8' : strtoupper($dbcharset);
            $dumpcharset = strtolower($dumpcharset) == 'utf-8' ? 'utf8' : strtolower($dumpcharset);
            if ($sqlcharset) {
                DB::query("SET NAMES '$dumpcharset';\n\n");
            }
        }
        if (!is_dir('./data/' . $backupdir)) {
            mkdir('./data/' . $backupdir, 0777);
        }
        $setnames = $db->version() > '4.1' ? "SET NAMES '$dumpcharset';\n\n" : ''; //SET FOREIGN_KEY_CHECKS=0;\n\n
        phpcom::$G['gp_volume'] = isset(phpcom::$G['gp_volume']) ? phpcom::$G['gp_volume'] : 0;
        $volume = intval(phpcom::$G['gp_volume']) + 1;
        $idstring = '# Identify: ' . base64_encode(TIMESTAMP . "," . phpcom::$setting['version'] . ",{$method},{$volume},{$tablepre},{$dbcharset}") . "\n";
        $backupfilename = './data/' . $backupdir . '/' . str_replace(array('/', '\\', '.', "'"), '', $filename);

        if (isset(phpcom::$G['gp_method']) && phpcom::$G['gp_method']) {
            $strtable = '';
            foreach ($backuptables as $value) {
                $strtable .= '"' . $value . '" ';
            }
            $dbconfig = phpcom::$config['db']['1'];
            $dbuser = $dbconfig['dbuser'];
            $dbpass = $dbconfig['dbpass'];
            $dbhost = $dbconfig['dbhost'];
            $dbname = $dbconfig['dbname'];
            list($dbhost, $dbport) = explode(':', "$dbhost:3306");
            $query = DB::query("SHOW VARIABLES LIKE 'basedir'");
            list(, $mysql_basedir) = DB::fetch_row($query);
            $dumpfile = $backupfilename . '.sql';
            @unlink($dumpfile);

            $mysqlbin = $mysql_basedir == '/' ? '' : addslashes($mysql_basedir) . 'bin/';
            $dumpcmd = 'mysqldump --force --quick ' . ($db->version() > '4.1' ? '--skip-opt --create-options' : '-all') .
                    ' --add-drop-table --host="' . $dbhost . '"' . ($dbport ? (is_numeric($dbport) ? ' --port=' . $dbport : ' --socket="' . $dbport . '"') : '') .
                    ' --user="' . $dbuser . '" --password="' . $dbpass . '" "' . $dbname . '" ' . $strtable . ' > ' . $dumpfile; // --default-character-set="' . $dumpcharset . '"
            @shell_exec($mysqlbin . $dumpcmd);

            if (@file_exists($dumpfile)) {
                if (phpcom::$G['gp_zip']) {
                    $zip = new zipfile();
                    $zipfilename = $backupfilename . '.zip';
                    $fp = fopen($dumpfile, "r");
                    $content = @fread($fp, filesize($dumpfile));
                    fclose($fp);
                    $zip->addFile($idstring . "# <?php exit();?>\n" . $setnames . "\n#" . $content, basename($dumpfile));
                    $fp = fopen($zipfilename, 'w');
                    @fwrite($fp, $zip->file());
                    fclose($fp);
                    @unlink($dumpfile);
                    @touch('./data/' . $backupdir . '/index.htm');
                    $filename = $backupfilename . '.zip';
                    unset($zip, $content);
                    admin_succeed('db_databackup_zip_succeed', 'action=backup&m=db', array('filename' => $filename));
                } else {
                    if (@is_writeable($dumpfile)) {
                        $fp = fopen($dumpfile, 'r+b');
                        @fwrite($fp, $idstring . "# <?php exit();?>\n" . $setnames . "\n#");
                        fclose($fp);
                    }
                    @touch('./data/' . $backupdir . '/index.htm');
                    $filename = $backupfilename . '.sql';
                    admin_succeed('db_databackup_succeed', 'action=backup&m=db', array('filename' => $filename));
                }
            } else {
                admin_error('da_mysqldump_shell_failed');
            }
        } else {
            $sqldump = '';
            $tableid = isset(phpcom::$G['gp_tableid']) ? intval(phpcom::$G['gp_tableid']) : 0;
            $startfrom = isset(phpcom::$G['gp_startfrom']) ? intval(phpcom::$G['gp_startfrom']) : 0;
            $volumesize = isset(phpcom::$G['gp_volumesize']) ? intval(phpcom::$G['gp_volumesize']) : 0;
            if ($volumesize < 100) {
                $volumesize = 2048;
            }
            
            if (!$tableid && $volume == 1) {
                foreach ($backuptables as $table) {
                    $sqldump .= dumptablestruct($table);
                }
            }

            $complete = TRUE;
            for (; $complete && $tableid < count($backuptables) && strlen($sqldump) + 500 < $volumesize * 1000; $tableid++) {
                $sqldump .= sqldumptable($backuptables[$tableid], $startfrom, strlen($sqldump), $volumesize);
                if ($complete) {
                    $startfrom = 0;
                }
            }
            $phpcom_verion = phpcom::$setting['version'];
            $dumpfile = $backupfilename . "-%s" . '.sql';
            !$complete && $tableid--;
            if (trim($sqldump)) {
                $sqldump = "$idstring" .
                        "# <?php exit();?>\n" .
                        "# PHPcom Multi-Volume Data Dump Vol.$volume\n" .
                        "# Version: PHPcom $phpcom_verion\n" .
                        "# Time: $time\n" .
                        "# Table Prefix: $tablepre\n" .
                        "#\n" .
                        "# PHPcom Home: http://www.phpcom.cn\n" .
                        "# --------------------------------------------------------\n\n\n" .
                        "$setnames" .
                        $sqldump;
                
                if($sqlcharset && strcasecmp($sqlcharset, CHARSET) && function_exists('mb_convert_encoding')){
                	$sqldump = mb_convert_encoding($sqldump, $sqlcharset, $sqlcharset);
                }
                $dumpfilename = sprintf($dumpfile, $volume);
                @$fp = fopen($dumpfilename, 'wb');
                @flock($fp, 2);
                if (@!fwrite($fp, $sqldump)) {
                    @fclose($fp);
                    admin_error('db_backup_file_invalid');
                } else {
                    fclose($fp);
                    if (phpcom::$G['gp_zip'] == 2) {
                        $fp = fopen($dumpfilename, "r");
                        $content = @fread($fp, filesize($dumpfilename));
                        fclose($fp);
                        $zip = new zipfile();
                        $zip->addFile($content, basename($dumpfilename));
                        $fp = fopen(sprintf($backupfilename . "-%s" . '.zip', $volume), 'w');
                        if (@fwrite($fp, $zip->file()) !== FALSE) {
                            @unlink($dumpfilename);
                        }
                        fclose($fp);
                    }
                    unset($sqldump, $zip, $content);
                    $url = implodeurl(array(
                        'method' => 0,
                        'volumesize' => phpcom::$G['gp_volumesize'],
                        'hex' => phpcom::$G['gp_hex'],
                        'zip' => phpcom::$G['gp_zip'],
                        'filename' => rawurlencode(phpcom::$G['gp_filename']),
                        'volume' => $volume,
                        'tableid' => $tableid,
                        'startfrom' => $startrow,
                    	'charset' => $sqlcharset,
                        'submit' => 'yes'
                    ));
                    admin_message('db_backup_multivolume_redirect', "action=backup&m=db&$url", array('volume' => $volume), 'loading');
                }
            } else {
                $volume--;
                $filelist = '<ul>';
                if (phpcom::$G['gp_zip'] == 1) {
                    $zip = new zipfile();
                    $zipfilename = $backupfilename . '.zip';
                    $unlinks = array();
                    for ($i = 1; $i <= $volume; $i++) {
                        $filename = sprintf($dumpfile, $i);
                        $fp = fopen($filename, "r");
                        $content = @fread($fp, filesize($filename));
                        fclose($fp);
                        $zip->addFile($content, basename($filename));
                        $unlinks[] = $filename;
                        $filelist .= "<li><a href=\"$filename\">$filename</a></li>\n";
                    }
                    $fp = fopen($zipfilename, 'w');
                    if (@fwrite($fp, $zip->file()) !== FALSE) {
                        foreach ($unlinks as $link) {
                            @unlink($link);
                        }
                    } else {
                        $filelist .= '</ul>';
                        admin_succeed('db_backup_multivolume_succeed', 'action=backup&m=db', array('volume' => $volume, 'filelist' => $filelist));
                    }
                    unset($sqldump, $zip, $content);
                    fclose($fp);
                    @touch('./data/' . $backupdir . '/index.htm');
                    $filename = $zipfilename;
                    admin_succeed('db_databackup_zip_succeed', 'action=backup&m=db', array('filename' => $filename));
                } else {
                    @touch('./data/' . $backupdir . '/index.htm');
                    for ($i = 1; $i <= $volume; $i++) {
                        $filename = sprintf(phpcom::$G['gp_zip'] == 2 ? $backupfilename . "-%s" . '.zip' : $dumpfile, $i);
                        $filelist .= "<li><a href=\"$filename\">$filename</a></li>\n";
                    }
                    $filelist .= '</ul>';
                    admin_succeed('db_backup_multivolume_succeed', 'action=backup&m=db', array('volume' => $volume, 'filelist' => $filelist));
                }
                admin_succeed('db_databackup_succeed', 'action=backup&m=db', array('filename' => $filename));
            }
        }
    }
}

admin_footer();

function fetchtablearray($tablepre = '', $key = '') {
    $tables = array();
    $tablepre = str_replace('_', '\_', $tablepre);
    $sql = "LIKE '$tablepre%'";
    $query = DB::query("SHOW TABLE STATUS $sql");
    while ($row = DB::fetch_array($query)) {
        $tables[] = $key ? $row[$key] : $row;
    }
    return $tables;
}

function createtablesql($sql, $dbcharset) {
    $type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
    $type = in_array($type, array('MYISAM', 'HEAP', 'MEMORY', 'INNODB')) ? $type : 'MYISAM';
    $type = str_replace(array('MYISAM', 'INNODB'), array('MyISAM', 'InnoDB'), $type);
    $sql = preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql);
    return $sql . (DB::version() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

function dumptablestruct($table) {
    global $excludetable, $db;
    if (in_array($table, $excludetable)) {
        return;
    }

    $query = DB::query("SHOW CREATE TABLE $table", 'SILENT');
    if (!DB::error()) {
        $tabledump = "DROP TABLE IF EXISTS $table;\n";
    } else {
        return '';
    }
    $results = $db->fetch_row($query);
    DB::free_result($query);
    if (strpos($table, '.') !== FALSE) {
        $tablename = substr($table, strpos($table, '.') + 1);
        $results[1] = str_replace("CREATE TABLE $tablename", 'CREATE TABLE ' . $table, $results[1]);
    }
    $tabledump .= $results[1];
    if ($db->version() > '4.1' && isset(phpcom::$G['gp_charset']) && phpcom::$G['gp_charset']) {
        $tabledump = preg_replace("/(DEFAULT)*\s*CHARSET=.+/", "DEFAULT CHARSET=" . phpcom::$G['gp_charset'], $tabledump);
    }
    $tablestatus = DB::fetch_first("SHOW TABLE STATUS LIKE '$table'");
    $auto_increment = $tablestatus['Auto_increment'];
    $tabledump .= ($auto_increment ? " AUTO_INCREMENT=$auto_increment" : '') . ";\n\n";
    return $tabledump;
}

function synchtablestructs($sql, $version, $dbcharset, $dbengine = 'MyISAM') {
	if(strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) {
		return $sql;
	}
	$sqlversion = strpos($sql, 'ENGINE=') === FALSE ? FALSE : TRUE;
	if($sqlversion === $version) {
		return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', "/DEFAULT CHARSET=\w+/is"), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	}
	if($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);
	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}

function synchtablestruct($sql, $dbcharset, $dbengine = 'MyISAM') {
	if(stripos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) {
		return $sql;
	}
	$type = preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql);
	$type = stricmp($type, array('MyISAM', 'InnoDB', 'Aria', 'MEMORY', 'HEAP'), true, $dbengine);
	if(stricmp($type, array('MyISAM', 'InnoDB', 'Aria'))){
		$type = $dbengine;
	}
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	" ENGINE=$type DEFAULT CHARSET=$dbcharset";
}

function sqldumptable($table, $startfrom = 0, $currsize = 0, $sizelimit = 2048) {
    global $excludetable, $startrow, $complete, $db;
    $offset = 300;
    $tabledump = '';
    $tablefields = array();
    $query = DB::query("SHOW FULL COLUMNS FROM $table", 'SILENT');
    if (str_exists($table, 'admin_session')) {
        return;
    } elseif (!$query && DB::errno() == 1146) {
        return;
    } elseif (!$query) {
        phpcom::$G['gp_hex'] = FALSE;
    } else {
        while ($fieldrow = DB::fetch_array($query)) {
            $tablefields[] = $fieldrow;
        }
    }
    $ifhex = phpcom::$G['gp_hex'];
    if (!in_array($table, $excludetable)) {
        $tabledumped = 0;
        $numrows = $offset;
        $firstfield = $tablefields[0];
        while ($currsize + strlen($tabledump) + 500 < $sizelimit * 1000 && $numrows == $offset) {
            if ($firstfield['Extra'] == 'auto_increment') {
                $selectsql = "SELECT * FROM $table WHERE {$firstfield['Field']} > $startfrom ORDER BY {$firstfield['Field']} LIMIT $offset";
            } else {
                $selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
            }
            $tabledumped = 1;
            $rows = DB::query($selectsql);
            $numfields = $db->num_fields($rows);
            $numrows = DB::num_rows($rows);
            while ($row = $db->fetch_row($rows)) {
                $comma = $t = '';
                for ($i = 0; $i < $numfields; $i++) {
                    $t .= $comma . ($ifhex && !empty($row[$i]) && (str_exists($tablefields[$i]['Type'], 'char') || str_exists($tablefields[$i]['Type'], 'text')) ? '0x' . bin2hex($row[$i]) : '\'' . DB::escape_string($row[$i]) . '\'');
                    $comma = ',';
                }
                if (strlen($t) + $currsize + strlen($tabledump) + 500 < $sizelimit * 1000) {
                    if ($firstfield['Extra'] == 'auto_increment') {
                        $startfrom = $row[0];
                    } else {
                        $startfrom++;
                    }
                    $tabledump .= "INSERT INTO $table VALUES ($t);\n";
                } else {
                    $complete = FALSE;
                    break 2;
                }
            }
        }
        $startrow = $startfrom;
        $tabledump .= "\n";
    }
    return $tabledump;
}

function sqlsplit($sql) {
    $sql = str_replace("\r", "\n", $sql);
    $ret = array();
    $num = 0;
    $queriesarray = explode(";\n", trim($sql));
    unset($sql);
    foreach ($queriesarray as $query) {
        $queries = explode("\n", trim($query));
        foreach ($queries as $querys) {
        	if(trim($querys) !== ''){
	        	if(isset($ret[$num])){
	        		$ret[$num] .= $querys{0} == "#" ? NULL : $querys;
	        	}else{
	        		$ret[$num] = $querys{0} == "#" ? NULL : $querys;
	        	}
        	}
        }
        $num++;
    }
    return($ret);
}

function checkslow($type1, $type2) {
    $t1 = explode(' ', $type1);
    $t1 = $t1[0];
    $t2 = explode(' ', $type2);
    $t2 = $t2[0];
    $arr = array($t1, $t2);
    sort($arr);
    if ($arr == array('mediumtext', 'text')) {
        return TRUE;
    } elseif (substr($arr[0], 0, 4) == 'char' && substr($arr[1], 0, 7) == 'varchar') {
        return TRUE;
    }
    return FALSE;
}

function convert_string($incharset, $outcharset, $string) {
    if (stripos($incharset, $outcharset) !== FALSE) {
        return $string;
    }
    if (function_exists('mb_convert_encoding')) {
    	return mb_convert_encoding($string, $outcharset, $incharset);
    }elseif (function_exists('iconv')) {
        if ((stristr(PHP_OS, 'AIX')) && (strcasecmp(ICONV_IMPL, 'unknown') == 0) && (strcasecmp(ICONV_VERSION, 'unknown') == 0)) {
            return aix_iconv_wrapper($incharset, $outcharset, $string);
        } else {
            return iconv($incharset, $outcharset . '//TRANSLIT', $string); //IGNORE
        }
    } else {
        return $string;
    }
}

function aix_iconv_wrapper($in_charset, $out_charset, $str) {
    static $gnu_iconv_to_aix_iconv_encoding_map = array(
	'iso-8859-1' => 'ISO8859-1',
	 'big5' => 'IBM-eucTW',
	 'euc-jp' => 'IBM-eucJP',
	 'koi8-r' => 'IBM-eucKR',
	 'gbk' => 'GBK',
	 'gb2312' => 'GB2312',
	 'utf8' => 'UTF-8',
	 'utf-8' => 'UTF-8'
    );

    $translit_search = strpos(strtolower($out_charset), '//translit');
    $using_translit = (!($translit_search === FALSE));
    $out_charset_plain = ($using_translit ? substr($out_charset, 0, $translit_search) : $out_charset);
    if (array_key_exists(strtolower($in_charset), $gnu_iconv_to_aix_iconv_encoding_map)) {
        $in_charset = $gnu_iconv_to_aix_iconv_encoding_map[strtolower($in_charset)];
    }
    if (array_key_exists(strtolower($out_charset_plain), $gnu_iconv_to_aix_iconv_encoding_map)) {
        $out_charset_plain = $gnu_iconv_to_aix_iconv_encoding_map[strtolower($out_charset_plain)];
    }
    $out_charset = ($using_translit ? $out_charset_plain . substr($out_charset, $translit_search) : $out_charset_plain);
    $out_charset = $out_charset_plain;

    return iconv($in_charset, $out_charset, $str);
}

?>
