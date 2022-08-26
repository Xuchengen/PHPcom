<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : credits.php    2012-1-11
 */
!defined('IN_ADMINCP') && exit('Access denied');

phpcom::$G['lang']['admin'] = 'misc';
$navarray = array(
    array(
        'title' => 'menu_credits',
        'url' => '?m=credits',
        'name' => 'credits_setting',
    ),
    array(
        'title' => 'menu_credits_policy',
        'url' => '?m=credits&action=policy',
        'name' => 'credits_policy',
    )
);
admin_header($admintitle);
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('global');
$adminhtml->navtabs($navarray, $action ? 'credits_policy' : 'credits_setting');
if ($action == 'edit') {
    $adminhtml->tablesetmode = TRUE;
    $ruleid = intval(phpcom::$G['gp_ruleid']);
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        $creditrules = DB::fetch_first("SELECT * FROM " . DB::table('credit_rules') . " WHERE ruleid='$ruleid'");
        $adminhtml->form('m=credits&action=edit', array(array('ruleid', $creditrules['ruleid'])));
        $adminhtml->vars = array('name' => $creditrules['rulename']);
        $adminhtml->table_header('credits_edit_title', 2);
        $adminhtml->table_td(array(array('credits_tips', FALSE, 'colspan="2"')), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_setting('credits_rulename', 'creditrules[rulename]', $creditrules['rulename'], 'text');
        $adminhtml->table_setting('credits_timecycle', 'creditrules[timecycle]', $creditrules['timecycle'], 'radios');
        $adminhtml->table_setting('credits_intervaltime', 'creditrules[intervaltime]', $creditrules['intervaltime'], 'text');
        $adminhtml->table_setting('credits_rewnum', 'creditrules[rewnum]', $creditrules['rewnum'], 'text');
        $adminhtml->table_setting('credits_money', 'creditrules[money]', $creditrules['money'], 'text');
        $adminhtml->table_setting('credits_prestige', 'creditrules[prestige]', $creditrules['prestige'], 'text');
        $adminhtml->table_setting('credits_praise', 'creditrules[praise]', $creditrules['praise'], 'text');
        $adminhtml->table_setting('credits_currency', 'creditrules[currency]', $creditrules['currency'], 'text');
        $adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
        $adminhtml->table_end('</form>');
    } else {
        $creditrules = phpcom::$G['gp_creditrules'];
        $creditrules['timecycle'] = intval($creditrules['timecycle']);
        $creditrules['rewnum'] = intval($creditrules['rewnum']);
        $creditrules['intervaltime'] = intval($creditrules['intervaltime']);
        $creditrules['money'] = intval($creditrules['money']);
        $creditrules['prestige'] = intval($creditrules['prestige']);
        $creditrules['praise'] = intval($creditrules['praise']);
        $creditrules['currency'] = intval($creditrules['currency']);
        if ($creditrules['timecycle'] == 2 || $creditrules['timecycle'] == 3) {
            $creditrules['intervaltime'] = 0;
        }
        if ($creditrules) {
            DB::update('credit_rules', $creditrules, "ruleid='$ruleid'");
            phpcom_cache::updater('creditrules');
        }
        admin_succeed('credits_policy_succeed', 'm=credits&action=policy');
    }
} elseif ($action == 'policy') {
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        $adminhtml->form('m=credits', array(array('action', 'policy')));
        $adminhtml->table_header('credits_policy', 8);
        $adminhtml->table_td(array(array('credits_tips', FALSE, 'colspan="8"')), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_td(array(
            array('credits_rulename', FALSE),
            array('credits_timecycle', FALSE),
            array('credits_rewnum', FALSE),
            array('credits_money', FALSE),
            array('credits_prestige', FALSE),
            array('credits_praise', FALSE,),
            array('credits_currency', FALSE,),
            array('emptychar', FALSE, 'align="center" noWrap="noWrap"')
                ), NULL, FALSE, ' tablerow', NULL, FALSE);
        $query = DB::query("SELECT * FROM " . DB::table('credit_rules') . " ORDER BY ruleid ASC");
        while ($row = DB::fetch_array($query)) {
            $ruleid = $row['ruleid'];
            $detail = $adminhtml->edit_word('detail', "action=edit&m=credits&ruleid=$ruleid");
            $row['rewnum'] = $row['timecycle'] ? $row['rewnum'] : 'N/A';
            $row['rewnum'] = $row['rewnum'] ? $row['rewnum'] : adminlang('credits_no_limit');
            $adminhtml->table_td(array(
                array($row['rulename'], TRUE),
                array('credits_timecycle_' . $row['timecycle'], FALSE),
                array($row['rewnum'], TRUE),
                array('<input class="input t5" size="5" name="money[' . $ruleid . ']" type="text" value="' . intval($row['money']) . '" />', TRUE),
                array('<input class="input t5" size="5" name="prestige[' . $ruleid . ']" type="text" value="' . intval($row['prestige']) . '" />', TRUE),
                array('<input class="input t5" size="5" name="praise[' . $ruleid . ']" type="text" value="' . intval($row['praise']) . '" />', TRUE),
                array('<input class="input t5" size="5" name="currency[' . $ruleid . ']" type="text" value="' . intval($row['currency']) . '" />', TRUE),
                array($detail, TRUE, 'align="center"')
            ));
        }
        $adminhtml->table_td(array(
            array($adminhtml->submit_button(), TRUE, 'colspan="8"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        $moneys = phpcom::$G['gp_money'];
        if ($moneys) {
            foreach ($moneys as $ruleid => $money) {
                DB::update('credit_rules', array(
                    'money' => intval($money),
                    'prestige' => intval(phpcom::$G['gp_prestige'][$ruleid]),
                    'praise' => intval(phpcom::$G['gp_praise'][$ruleid]),
                    'currency' => intval(phpcom::$G['gp_currency'][$ruleid])
                        ), "ruleid='$ruleid'");
            }
            $data = array();
            $query = DB::query("SELECT * FROM " . DB::table('credit_rules'));
            while ($rules = DB::fetch_array($query)) {
                $data[$rules['operation']] = array();
            }
            $value = serialize($data);
            DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES ('creditspolicy', '$value', 'array')");
            phpcom_cache::updater('creditrules');
        }
        admin_succeed('credits_policy_succeed', 'm=credits&action=policy');
    }
} else {
    $setting = array();
    $query = DB::query("SELECT * FROM " . DB::table('setting') . " WHERE skey IN('credits','creditstrans','creditsformula','creditnotice','creditsnotify')");
    while ($row = DB::fetch_array($query)) {
        if ($row['stype'] == 'array') {
            $setting[$row['skey']] = unserialized($row['svalue']);
        } else {
            $setting[$row['skey']] = $row['svalue'];
        }
    }
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        $adminhtml->form('m=credits');
        $adminhtml->table_header('credits_setting', 6);
        $adminhtml->table_td(array(
            array('credits_setting_docs_0', FALSE),
            array('credits_setting_docs_1', FALSE),
            array('credits_setting_docs_2', FALSE),
            array('credits_setting_docs_3', FALSE),
            array('credits_setting_docs_4', FALSE),
            array('credits_transaction', FALSE),
                ), NULL, FALSE, ' tablerow', NULL, FALSE);
        $array = array('money', 'prestige', 'currency', 'praise');
        $credits = $setting['credits'];
        $creditstrans = $setting['creditstrans'];
        $creditstrans['field'] = empty($creditstrans['field']) ? 'money' : $creditstrans['field'];
        foreach ($array as $value) {
            $checked = ($value == $creditstrans['field']) ? ' checked' : '';
            $adminhtml->table_td(array(
                array('<label><input type="checkbox" class="checkbox" name="credits[' . $value . '][enabled]" value="1"' . ($credits[$value]['enabled'] ? ' checked' : '') . ' /> ' . $value . '</label>', TRUE),
                array('<input class="input t10" size="10" name="credits[' . $value . '][title]" type="text" value="' . htmlcharsencode($credits[$value]['title']) . '" />', TRUE),
                array('<input class="input t10" size="10" name="credits[' . $value . '][icon]" type="text" value="' . htmlcharsencode($credits[$value]['icon']) . '" />', TRUE),
                array('<input class="input t10" size="10" name="credits[' . $value . '][unit]" type="text" value="' . htmlcharsencode($credits[$value]['unit']) . '" />', TRUE),
                array('<input class="input t10" size="10" name="credits[' . $value . '][initcredits]" type="text" value="' . intval($credits[$value]['initcredits']) . '" />', TRUE),
                array('<label><input class="radio" type="radio" name="field" value="' . htmlcharsencode($value) . '"' . $checked . ' /> &nbsp; &nbsp; &nbsp; <label>', TRUE),
            ));
        }
        $adminhtml->table_td(array(
            array('credits_formula', FALSE),
            array('<input class="input t70" size="70" name="creditsformula" type="text" value="' . htmlcharsencode($setting['creditsformula']) . '" />', TRUE, 'colspan="2"'),
            array('credits_formula_comments', FALSE, 'colspan="3"')
        ));
        $adminhtml->table_td(array(array('credits_formula_tips', FALSE, 'colspan="6"')), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_td(array(
            array($adminhtml->submit_button(), TRUE, 'colspan="6"')
                ), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_end('</form>');
    } else {
        $credits = striptags(phpcom::$G['gp_credits']);
        $creditsformula = phpcom::$G['gp_creditsformula'];
        $field = isset(phpcom::$G['gp_field']) ? trim(phpcom::$G['gp_field']) : 'money';
        $field = str_exists($field, array('money', 'prestige', 'currency', 'praise')) ? $field : 'money';
        if (!$credits[$field]['enabled']) {
            admin_message('credits_update_error');
        }
        $credits['money']['enabled'] = isset($credits['money']['enabled']) ? intval($credits['money']['enabled']) : 0;
        $credits['prestige']['enabled'] = isset($credits['prestige']['enabled']) ? intval($credits['prestige']['enabled']) : 0;
        $credits['currency']['enabled'] = isset($credits['currency']['enabled']) ? intval($credits['currency']['enabled']) : 0;
        $credits['praise']['enabled'] = isset($credits['praise']['enabled']) ? intval($credits['praise']['enabled']) : 0;
        $savesetting = array();
        $value = serialize($credits);
        $creditstrans = serialize(array_merge(array('field' => $field), $credits[$field]));
        $savesetting[] = "('credits', '$value', 'array')";
        $savesetting[] = "('creditstrans', '$creditstrans', 'array')";
        $savesetting[] = "('creditsformula', '$creditsformula', 'string')";
        if ($savesetting) {
            DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES " . implode(',', $savesetting));
        }
        phpcom_cache::updater('setting');
        admin_succeed('credits_update_succeed', 'm=credits');
    }
}

admin_footer();
?>