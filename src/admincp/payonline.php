<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : payonline.php    2012-1-5
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'setting';

$navarray = array(
    array(
        'title' => 'menu_payonline',
        'url' => '?m=payonline',
        'id' => 'payonline',
        'name' => 'payonline'
    ),
    array(
        'title' => 'menu_userorder',
        'url' => '?m=userorder',
        'id' => 'userorder',
        'name' => 'userorder'
    )
);
if ($action == 'alipay') {
    include loadlibfile('alipay', 'api');
    ob_end_clean();
    phpcom::header('location: ' . get_credit_payurl(1, $orderid));
} elseif ($action == 'tenpay') {
    include loadlibfile('tenpay', 'api');
    phpcom::header('location: ' . get_credit_payurl(1, $orderid));
} elseif ($action == 'chinabank') {
    include loadlibfile('chinabank', 'api');
    phpcom::header('location: ' . get_credit_payurl(1, $orderid));
} else {
    admin_header('menu_payonline');
    $adminhtml = phpcom_adminhtml::instance();
    $adminhtml->activetabs('global');
    $adminhtml->navtabs($navarray, 'payonline');
    $setting = array();
    $query = DB::query("SELECT * FROM " . DB::table('setting') . " WHERE skey IN('payonline','payreadme','pay_creditsratio','pay_mincredits','pay_maxcredits','pay_alipay','pay_tenpay','pay_paypal','pay_chinabank')");
    while ($row = DB::fetch_array($query)) {
        if ($row['stype'] == 'array') {
            $setting[$row['skey']] = unserialized($row['svalue']);
        } else {
            $setting[$row['skey']] = $row['svalue'];
        }
    }
    $pay_alipay_key = decryptstring($setting['pay_alipay']['key']);
    $pay_tenpay_key = decryptstring($setting['pay_tenpay']['key']);
    $pay_tenpay_escrow_key = decryptstring($setting['pay_tenpay']['escrow_key']);
    $pay_chinabank_key = decryptstring($setting['pay_chinabank']['key']);
    if (!checksubmit(array('submit', 'btnsubmit'))) {
        $adminhtml->form('m=payonline', array(array('action', 'ok')));
        $adminhtml->table_header('setting_payonline', 3);
        $adminhtml->table_td(array(array('setting_payonline_tips', FALSE, 'colspan="3"')), NULL, FALSE, NULL, NULL, FALSE);
        $adminhtml->table_setting('setting_payonline_status', 'setting[payonline]', intval($setting['payonline']), 'radio');
        $adminhtml->table_setting('setting_payonline_readme', 'setting[payreadme]', $setting['payreadme'], 'text');
        $adminhtml->table_setting('setting_payonline_pay_creditsratio', 'setting[pay_creditsratio]', intval($setting['pay_creditsratio']), 'text');
        $adminhtml->table_setting('setting_payonline_pay_mincredits', 'setting[pay_mincredits]', intval($setting['pay_mincredits']), 'text');
        $adminhtml->table_setting('setting_payonline_pay_maxcredits', 'setting[pay_maxcredits]', intval($setting['pay_maxcredits']), 'text');
        $adminhtml->table_end();
        if (isset($pay_alipay_key) && $pay_alipay_key) {
            $pay_alipay_key = cutstr($pay_alipay_key, 2, '********');
        }
        $adminhtml->table_header('setting_payonline_alipay', 3);
        $adminhtml->table_setting('setting_payonline_alipay_account', 'pay_alipay[account]', $setting['pay_alipay']['account'], 'text');
        $adminhtml->table_setting('setting_payonline_alipay_partnerid', 'pay_alipay[partnerid]', $setting['pay_alipay']['partnerid'], 'text');
        $adminhtml->table_setting('setting_payonline_alipay_key', 'pay_alipay[key]', $pay_alipay_key, 'text');
        $adminhtml->table_setting('setting_payonline_alipay_direct', 'pay_alipay[direct]', intval($setting['pay_alipay']['direct']), 'radio');
        $adminhtml->table_setting('setting_payonline_alipay_test', 'setting_payonline_alipay_test_pay', '', '0');
        $adminhtml->table_end();
        if ($pay_tenpay_key) {
            $pay_tenpay_key = cutstr($pay_tenpay_key, 2, '********');
        }
        if ($pay_tenpay_escrow_key) {
            $pay_tenpay_escrow_key = cutstr($pay_tenpay_escrow_key, 2, '********');
        }
        $adminhtml->table_header('setting_payonline_tenpay', 3);
        $adminhtml->table_setting('setting_payonline_tenpay_partnerid', 'pay_tenpay[partnerid]', $setting['pay_tenpay']['partnerid'], 'text');
        $adminhtml->table_setting('setting_payonline_tenpay_key', 'pay_tenpay[key]', $pay_tenpay_key, 'text');
        $adminhtml->table_setting('setting_payonline_tenpay_direct', 'pay_tenpay[direct]', intval($setting['pay_tenpay']['direct']), 'radio');
        $adminhtml->table_setting('setting_payonline_tenpay_escrow_chnid', 'pay_tenpay[escrow_chnid]', $setting['pay_tenpay']['escrow_chnid'], 'text');
        $adminhtml->table_setting('setting_payonline_tenpay_escrow_key', 'pay_tenpay[escrow_key]', $pay_tenpay_escrow_key, 'text');
        $adminhtml->table_setting('setting_payonline_tenpay_test', 'setting_payonline_tenpay_test_pay', '', '0');
        $adminhtml->table_end();
        if ($pay_chinabank_key) {
            $pay_chinabank_key = cutstr($pay_chinabank_key, 2, '********');
        }
        $adminhtml->vars = array('siteurl' => phpcom::$G['siteurl']);
        $adminhtml->table_header('setting_payonline_chinabank', 3);
        $adminhtml->table_setting('setting_payonline_chinabank_partnerid', 'pay_chinabank[partnerid]', $setting['pay_chinabank']['partnerid'], 'text');
        $adminhtml->table_setting('setting_payonline_chinabank_key', 'pay_chinabank[key]', $pay_chinabank_key, 'text');
        $adminhtml->table_setting('setting_payonline_chinabank_test', 'setting_payonline_chinabank_test_pay', '', '0');
        $adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
        $adminhtml->table_end('</form>');
    } else {
        $settings = phpcom::$G['gp_setting'];
        $settings['payonline'] = intval($settings['payonline']);
        $settings['pay_creditsratio'] = intval($settings['pay_creditsratio']);
        $settings['pay_mincredits'] = intval($settings['pay_mincredits']);
        $settings['pay_maxcredits'] = intval($settings['pay_maxcredits']);
        $pay_alipay = striptags(phpcom::$G['gp_pay_alipay']);
        $pay_tenpay = striptags(phpcom::$G['gp_pay_tenpay']);
        $pay_chinabank = striptags(phpcom::$G['gp_pay_chinabank']);
        if (empty($pay_alipay['account'])) {
            $settings['pay_alipay'] = 'a:0:{}';
        } else {
            if ($pay_alipay['key'] && strpos($pay_alipay['key'], '********')) {
                $pay_alipay['key'] = encryptstring($pay_alipay_key);
            } else {
                $pay_alipay['key'] = encryptstring(trim($pay_alipay['key']));
            }
            $settings['pay_alipay'] = serialize($pay_alipay);
        }
        if (empty($pay_tenpay['partnerid']) && empty($pay_tenpay['escrow_chnid'])) {
            $settings['pay_tenpay'] = 'a:0:{}';
        } else {
            if ($pay_tenpay['key'] && strpos($pay_tenpay['key'], '********')) {
                $pay_tenpay['key'] = encryptstring($pay_tenpay_key);
            } else {
                $pay_tenpay['key'] = encryptstring(trim($pay_tenpay['key']));
            }
            if ($pay_tenpay['escrow_key'] && strpos($pay_tenpay['escrow_key'], '********')) {
                $pay_tenpay['escrow_key'] = encryptstring($pay_tenpay_escrow_key);
            } else {
                $pay_tenpay['escrow_key'] = encryptstring(trim($pay_tenpay['escrow_key']));
            }
            $settings['pay_tenpay'] = serialize($pay_tenpay);
        }
        if (empty($pay_chinabank['partnerid'])) {
            $settings['pay_chinabank'] = 'a:0:{}';
        } else {
            if ($pay_chinabank['key'] && strpos($pay_chinabank['key'], '********')) {
                $pay_chinabank['key'] = encryptstring($pay_chinabank_key);
            } else {
                $pay_chinabank['key'] = encryptstring(trim($pay_chinabank['key']));
            }
            $settings['pay_chinabank'] = serialize($pay_chinabank);
        }
        $savesetting = array();
        foreach ($settings as $key => $value) {
            if ($key !== 'pay_alipay' && $key !== 'pay_tenpay' && $key !== 'pay_chinabank') {
                $savesetting[] = "('$key', '$value', 'string')";
            } else {
                $savesetting[] = "('$key', '$value', 'array')";
            }
        }
        if ($savesetting) {
            DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES " . implode(',', $savesetting));
        }
        phpcom_cache::updater('setting');
        admin_succeed('setting_succeed', 'm=payonline');
    }

    admin_footer();
}
?>
