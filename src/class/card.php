<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : card.php    2012-2-16
 */
!defined('IN_PHPCOM') && exit('Access denied');

class phpcom_card {

    var $setting = array();
    var $rulekey = array("str" => "\@", "num" => "\#", "all" => "\*");
    var $pattern = '';
    var $rule = '';
    var $rulemap_str = "ABCDEFGHIJKLMNPQRSTUVWXYZ";
    var $rulemap_num = "123456789";
    var $rulereturn = array();
    var $cardlist = array();
    var $succeed = 0;
    var $fail = 0;
    var $failmin = 1;
    var $failrate = '0.1';

    public function __construct() {
        $this->setting = &phpcom::$setting['card'];
        $this->pattern = "^[A-Z0-9" . implode('|', $this->rulekey) . "]+$";
    }

    public function make($rule = '', $num = 1, $cardval = array()) {
        $this->rule = empty($rule) ? $this->setting['rule'] : trim($rule);
        if (empty($this->rule)) {
            return -1;
        }
        $this->fail($num);
        $sqlkey = $sqlval = '';
        if (is_array($cardval)) {
            foreach ($cardval as $key => $val) {
                $sqlkey .= ", $key";
                $sqlval .= ", '{$val}'";
            }
        }
        for ($i = 0; $i < $num; $i++) {
            if ($this->checkrule($this->rule)) {
                $card = $this->rule;
                foreach ($this->rulereturn as $key => $val) {
                    $search = array();
                    foreach ($val as $skey => $sval) {
                        $search[] = '/' . $this->rulekey[$key] . '/';
                    }
                    $card = preg_replace($search, $val, $card, 1);
                }
            } else {
                return 0;
            }
            $maker = phpcom::$G['username'];
            $timestamp = phpcom::$G['timestamp'];
            $password = '8' . str_rand(7, 1);
            $sql = "INSERT INTO " . DB::table('card') . " (cardid, password, maker, dateline $sqlkey)VALUES('$card', '$password','$maker', '$timestamp' $sqlval)";
            DB::query($sql, 'SILENT');
            if ($errormsg = DB::error()) {
                if (DB::errno() == 1062) {
                    $this->fail++;
                    if ($this->failmin > $this->fail) {
                        $num++;
                    } else {
                        $num = $i - 1;
                    }
                } else {
                    DB::halt($errormsg, $sql);
                }
            } else {
                $this->succeed += intval(DB::affected_rows());
                $this->cardlist[] = $card;
            }
        }
        return TRUE;
    }

    public function checkrule($rule, $type = '0') {
        if (!preg_match("/($this->pattern)/i", $rule)) {
            return -2;
        }
        if ($type == 0) {
            foreach ($this->rulekey as $key => $val) {
                $match = array();
                preg_match_all("/($val){1}/i", $rule, $match);
                $number[$key] = count($match[0]);
                if ($number[$key] > 0) {
                    for ($i = 0; $i < $number[$key]; $i++) {
                        switch ($key) {
                            case 'str':
                                $rand = mt_rand(0, (strlen($this->rulemap_str) - 1));
                                $this->rulereturn[$key][$i] = $this->rulemap_str[$rand];
                                break;
                            case 'num':
                                $rand = mt_rand(0, (strlen($this->rulemap_num) - 1));
                                $this->rulereturn[$key][$i] = $this->rulemap_num[$rand];
                                break;
                            case 'all':
                                $allstr = $this->rulemap_str . $this->rulemap_num;
                                $rand = mt_rand(0, (strlen($allstr) - 1));
                                $this->rulereturn[$key][$i] = $allstr[$rand];
                                break;
                        }
                    }
                }
            }
        }
        return TRUE;
    }

    public function fail($num = 1) {
        $failrate = $this->failrate ? (float)$this->failrate : '0.1';
        $this->failmin = ceil($num * $failrate);
        $this->failmin = $this->failmin > 100 ? 100 : $this->failmin;
    }

}

?>
