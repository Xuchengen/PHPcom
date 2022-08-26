<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : cron.php    2012-1-10
 */
!defined('IN_ADMINCP') && exit('Access denied');

phpcom::$G['lang']['admin'] = 'misc';
$navarray = array(
    array(
        'title' => 'menu_cron',
        'url' => '?m=cron',
        'name' => 'cron',
    )
);
admin_header($admintitle);
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('tools');
$adminhtml->navtabs($navarray, 'cron');
if ($action == 'edit') {
    $adminhtml->tablesetmode = TRUE;
    $cronid = isset(phpcom::$G['gp_cronid']) ? intval(phpcom::$G['gp_cronid']) : 0;
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        $cron = DB::fetch_first("SELECT * FROM " . DB::table('cron_entry') . " WHERE cronid='$cronid'");
        $adminhtml->form('m=cron&action=edit', array(array('cronid', $cron['cronid'])));
        $adminhtml->vars = array('name' => $cron['subject']);
        $adminhtml->table_header('cron_edit_title', 2);
        $adminhtml->table_setting('cron_edit_subject', 'cron[subject]', $cron['subject'], 'text');
        $adminhtml->table_setting('cron_edit_weekday', 'cron[weekday]', $cron['weekday'], 'select');
        $days = array('-1' => adminlang('cron_no_limit'));
        for ($i = 1; $i <= 31; $i++) {
            $days["$i"] = $i . ' ' . adminlang('cron_day');
        }
        $dayselect = $adminhtml->select($days, 'cron[day]', $cron['day'], 'class="select t50"');
        $adminhtml->table_setting('cron_edit_day', $dayselect, '', '0');
        $adminhtml->table_setting('cron_edit_hour', 'cron[hour]', $cron['hour'], 'select');
        $adminhtml->table_setting('cron_edit_minute', 'cron[minute]', str_replace("\t",",", trim($cron['minute'], " \t,")), 'text');
        $adminhtml->table_setting('cron_edit_filename', 'cron[filename]', $cron['filename'], 'text');
        $adminhtml->table_setting('cron_update_exec', 'exectask', '0', 'radio');
        $adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
        $adminhtml->table_end('</form>');
    } else {
        $cron = phpcom::$G['gp_cron'];
        if ($cronid && $cron) {
            $daynew = $cron['weekday'] != -1 ? -1 : $cron['day'];
            if (strpos($cron['minute'], ',') !== FALSE) {
                $minutenew = explode(',', $cron['minute']);
                foreach ($minutenew as $key => $val) {
                    $minutenew[$key] = $val = intval($val);
                    if ($val < 0 || $val > 59) {
                        unset($minutenew[$key]);
                    }
                }
                $minutenew = array_slice(array_unique($minutenew), 0, 12);
                $minutenew = implode("\t", $minutenew);
            } else {
                $minutenew = intval($cron['minute']);
                $minutenew = $minutenew >= 0 && $minutenew < 60 ? $minutenew : '';
            }
            if (preg_match("/[\\\\\/\:\*\?\"\<\>\|]+/", $cron['filename'])) {
                admin_message('cron_filename_illegal');
            } elseif (!file_exists(PHPCOM_PATH . ($taskfile = "/inc/cron/{$cron['filename']}"))) {
                admin_message('cron_filename_invalid', '', array('file' => $taskfile));
            } elseif ($cron['weekday'] == -1 && $daynew == -1 && $cron['hour'] == -1 && $minutenew === '') {
                admin_message('cron_time_invalid');
            }
            $cron['minute'] = $minutenew;
            $cron['day'] = $daynew;
            DB::update('cron_entry', $cron, "cronid='$cronid'");
            if (phpcom::$G['gp_exectask']) {
                Cron::run($cronid);
            }
        }
        admin_succeed('cron_succeed', 'm=cron');
    }
} elseif ($action == 'exec') {
    $cronid = intval(phpcom::$G['gp_cronid']);
    $cron = DB::fetch_first("SELECT * FROM " . DB::table('cron_entry') . " WHERE cronid='$cronid'");
    if (!file_exists(PHPCOM_PATH . ($taskfile = "/inc/cron/{$cron['filename']}"))) {
        admin_message('cron_runing_invalid', 'm=cron', array('file' => $taskfile));
    } else {
        Cron::run($cronid);
        admin_succeed('cron_runing_succeed', 'm=cron');
    }
} else {
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        $adminhtml->form('m=cron');
        $adminhtml->table_header('cron_title', 8);
        $adminhtml->table_td(array(array('cron_tips', FALSE, 'colspan="8"')), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_td(array(
            array('deletecheckbox', FALSE, 'width="5%" noWrap="noWrap"'),
            array('cron_subject', FALSE, 'width="18%"'),
            array('cron_status', FALSE),
            array('cron_type', FALSE),
            array('cron_runtime', FALSE),
            array('cron_lastruntime', FALSE,),
            array('cron_nextruntime', FALSE,),
            array('emptychar', FALSE, 'align="center" noWrap="noWrap"')
                ), '', FALSE, ' tablerow', NULL, FALSE);
        $query = DB::query("SELECT * FROM " . DB::table('cron_entry') . " ORDER BY cronid ASC");
        while ($row = DB::fetch_array($query)) {
            $cronid = $row['cronid'];
            $checkbox = 'name="delete[]" value="' . $cronid . '"';
            if ($row['type'] == 'system') {
                $checkbox = 'name="disabled[]" value="" disabled';
            }
            $detail = $adminhtml->edit_word('detail', "action=edit&m=cron&cronid=$cronid");
            $detail .= ' | ' . $adminhtml->edit_word('execute', "action=exec&m=cron&cronid=$cronid");
            $disabled = $row['weekday'] == -1 && $row['day'] == -1 && $row['hour'] == -1 && $row['minute'] == '' ? 'disabled' : '';
            if ($row['day'] > 0 && $row['day'] < 32) {
                $row['time'] = adminlang('cron_permonth') . $row['day'] . adminlang('cron_day');
            } elseif ($row['weekday'] >= 0 && $row['weekday'] < 7) {
                $row['time'] = adminlang('cron_perweek') . adminlang('cron_weekday_' . $row['weekday']);
            } elseif ($row['hour'] >= 0 && $row['hour'] < 24) {
                $row['time'] = adminlang('cron_perday');
            } else {
                $row['time'] = adminlang('cron_perhour');
            }

            $row['time'] .= $row['hour'] >= 0 && $row['hour'] < 24 ? sprintf('%02d', $row['hour']) . adminlang('cron_hour') : '';

            if (!in_array($row['minute'], array(-1, ''))) {
                foreach ($row['minute'] = explode("\t", $row['minute']) as $k => $v) {
                    $row['minute'][$k] = sprintf('%02d', $v);
                }
                $row['minute'] = implode(',', $row['minute']);
                $row['time'] .= $row['minute'] . adminlang('cron_minute');
            } else {
                $row['time'] .= '00' . adminlang('cron_minute');
            }
            $row['lastruntime'] = $row['lastruntime'] ? fmdate($row['lastruntime']) : '<b>N/A</b>';
            $row['nextcolor'] = $row['nextruntime'] && $row['nextruntime'] + phpcom::$setting['timeoffset'] * 3600 < TIMESTAMP ? 'c1' : 'c3';
            $row['nextruntime'] = $row['nextruntime'] ? fmdate($row['nextruntime']) : '<b>N/A</b>';
            $adminhtml->table_td(array(
                array('<input type="checkbox" class="checkbox" ' . $checkbox . ' />', TRUE),
                array($adminhtml->inputedit("subject[$cronid]", $row['subject'], 20, 'left'), TRUE),
                array('cron_status_' . $row['status'], array('cronid' => $cronid, 'disabled' => $disabled)),
                array('cron_' . $row['type'], FALSE),
                array($row['time'], TRUE),
                array($row['lastruntime'], TRUE),
                array('<span class="' . $row['nextcolor'] . '">' . $row['nextruntime'] . '</span>', TRUE),
                array($detail, TRUE, 'align="center"')
            ));
        }
        $adminhtml->table_td(array(
            array('newadd', FALSE, 'noWrap="noWrap"'),
            array('<input class="input t20" name="subjectnew" type="text" />', TRUE, 'colspan="7"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_td(array(
            array($adminhtml->submit_button(), TRUE, 'colspan="8"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        $subjects = phpcom::$G['gp_subject'];
        $delete = isset(phpcom::$G['gp_delete']) ? stripempty(phpcom::$G['gp_delete']) : '';
        if (@$delete) {
            $cronids = implodeids($delete);
            DB::query("DELETE FROM " . DB::table('cron_entry') . " WHERE cronid IN ($cronids)");
            foreach ($delete as $value) {
                unset($subjects[$value]);
            }
            unset($delete);
        }
        $subjectnew = trim(phpcom::$G['gp_subjectnew']);
        if ($subjectnew) {
            $data = array('status' => 0, 'type' => 'user', 'filename' => '',
                'lastruntime' => 0, 'nextruntime' => TIMESTAMP,
                'weekday' => -1, 'day' => -1, 'hour' => -1, 'minute' => ''
            );
            $data['subject'] = htmlcharsencode($subjectnew);
            DB::insert('cron_entry', $data);
        }
        if ($subjects) {
            $cronstatus = isset(phpcom::$G['gp_status']) ? phpcom::$G['gp_status'] : array();
            foreach ($subjects as $cronid => $value) {
                $status = isset($cronstatus[$cronid]) && $cronstatus[$cronid] ? 1 : 0;
                $data = array('subject' => $value, 'status' => $status);
                if (!$status) {
                    $data['nextruntime'] = 0;
                }
                DB::update('cron_entry', $data, "cronid='$cronid'");
            }
        }
        $query = DB::query("SELECT cronid, filename FROM " . DB::table('cron_entry'));
        while ($row = DB::fetch_array($query)) {
            if (!file_exists(PHPCOM_PATH . '/inc/cron/' . $row['filename'])) {
                DB::update('cron_entry', array(
                    'status' => '0',
                    'nextruntime' => '0',
                        ), "cronid='$row[cronid]'");
            }
        }
        admin_succeed('cron_succeed', 'm=cron');
    }
}

admin_footer();
?>
