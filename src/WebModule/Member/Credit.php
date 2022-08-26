<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Credit.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Credit extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$doarray = array('basic', 'buy', 'rule', 'exchange', 'transfer', 'log');
		$do = trim($this->request->query('do', 'basic'));
		if($do) {
			$do = in_array($do, $doarray) ? $do : 'basic';
		}
		$this->title = lang('member', "member_credit_$do");
		
		$submodule = in_array($do, array('basic', 'buy', 'rule', 'exchange', 'transfer')) ? 'basic' : 'log';
		
		if($submodule == 'log'){
			return $this->creditLog();
		}else{
			return $this->creditBasic($do);
		}
	}
	
	protected function creditBasic($do)
	{
		$docurrents = array('basic' => '', 'buy' => '', 'rule' => '');
		$docurrents[$do] = ' class="current"';
		$uid = $this->uid;
		$usercredits = $this->usercredits;
		$subject = lang('member', 'member_credit');
		$creditstrans_field = phpcom::$setting['creditstrans']['field'];
		$tracredits = phpcom::$G['member'][$creditstrans_field];
		$creditstrans_unit = phpcom::$setting['creditstrans']['unit'];
		$creditstrans_title = phpcom::$setting['creditstrans']['title'];
		
		$creditarray = array('money', 'prestige', 'currency', 'praise');
		$creditdata = array();
		foreach ($creditarray as $field) {
			if ($do != 'basic' || $field != $creditstrans_field && phpcom::$setting['credits'][$field]['enabled']) {
				$creditdata[$field]['value'] = phpcom::$G['member'][$field];
				$creditdata[$field]['unit'] = phpcom::$setting['credits'][$field]['unit'];
				$creditdata[$field]['title'] = phpcom::$setting['credits'][$field]['title'];
			}
		}
		
		if ($do == 'basic') {
			$creditlogs = array();
			$count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('credit_log') . " WHERE uid='$uid'");
			if ($count) {
				$alt = 0;
				$query = DB::query("SELECT * FROM " . DB::table('credit_log') . " WHERE uid='$uid' ORDER BY dateline DESC LIMIT 0,20");
				while ($log = DB::fetch_array($query)) {
					$credits = array();
					$credited = FALSE;
					foreach (phpcom::$setting['credits'] as $field => $credit) {
						if (is_array($credit) && $log[$field]) {
							$credited = TRUE;
							$credits[] = $credit['title'] . ' <span class="c1">' . ($log[$field] > 0 ? '+' : '') . $log[$field] . '</span>';
							if ($log['operation'] == 'CEX' && !empty($log[$field])) {
								if ($log[$field] > 0) {
									$log['incredit'] = $field;
								} elseif ($log[$field] < 0) {
									$log['oncredit'] = $field;
								}
							}
						}
					}
					if (!$credited) {
						continue;
					}
					$log['credit'] = implode('<br/>', $credits);
					$log['dateline'] = fmdate($log['dateline']);
					$log['operate'] = lang('member', 'creditlogs_update_' . $log['operation']);
					$log['detail'] = lang('member', 'creditlogs_detail_' . $log['operation']);
					$log['alt'] = $alt ? ' class="alt"' : '';
					$alt = $alt ? 0 : 1;
					$creditlogs[] = $log;
				}
			}
		} elseif ($do == 'buy') {
			$paramextra = array('type' => 'alert', 'showdialog' => TRUE, 'location' => FALSE);
			if (!phpcom::$setting['payonline'] && !phpcom::$setting['card']['enabled']) {
				showmessage(phpcom::$setting['payreadme'], NULL, NULL, $paramextra);
			} elseif (!phpcom::$setting['pay_creditsratio'] || (!phpcom::$setting['pay_chinabank']['partnerid']
					&& !phpcom::$setting['pay_alipay']['account'] && !phpcom::$setting['pay_alipay']['partnerid'])
					&& !phpcom::$setting['pay_tenpay']['partnerid'] && !phpcom::$setting['pay_tenpay']['escrow_chnid']) {
				showmessage('action_closed', NULL, NULL, $paramextra);
			}
			if (checksubmit(array('buysubmit', 'btnsubmit', 'formsubmit'))) {
				$payapi = trim($this->request->getPost('payapi'));
				if (!in_array($payapi, array('alipay', 'tenpay', 'chinabank', 'card'))) {
					showmessage('action_undefined', NULL, NULL, $paramextra);
				}
				if ($payapi == 'card') {
					if (phpcom::$setting['captchastatus'][5]) {
						if (!$this->checkCaptcha($this->request->post('verifycode'))) {
							showmessage('captcha_verify_invalid', NULL, NULL, $paramextra);
						}
					}
					$cardid = trim($this->request->post('cardid'));
					if (empty($cardid)) {
						showmessage('credits_card_cardid_undefined', NULL, array(), $paramextra);
					}
					if (phpcom::$setting['card']['cipher'] && !$this->request->post('password')) {
						showmessage('credits_card_password_undefined', NULL, array(), $paramextra);
					}
					
					$cardid = preg_replace('~[^A-Z0-9]~i', '', $cardid);
					if (!$card = DB::fetch_first("SELECT * FROM " . DB::table('card') . " WHERE cardid= '$cardid'")) {
						showmessage('credits_card_unfound', NULL, array(), $paramextra);
					} else {
						if ($card['status'] == 2) {
							showmessage('credits_card_invalid', NULL, array(), $paramextra);
						}
						if ($card['cleardate'] < TIMESTAMP) {
							showmessage('credits_card_expired', NULL, array(), $paramextra);
						}
						if (phpcom::$setting['card']['cipher'] && $card['password'] != trim($this->request->post('password'))) {
							showmessage('credits_card_password_worong', NULL, array(), $paramextra);
						}
						$uid = phpcom::$G['uid'];
						$timestamp = phpcom::$G['timestamp'];
						if (phpcom::$setting['card']['grouped'] && $card['groupextid'] > 7) {
							$data = array();
							$member = DB::fetch_first("SELECT * FROM " . DB::table('members') . " WHERE uid= '$uid'");
							$grouptime = $timestamp + $card['groupdays'] * 86400;
							if ($member['groupextid']) {
								$data['groupexpiry'] = $member['groupexpiry'] + $grouptime;
							} else {
								$data['groupextid'] = $card['groupextid'];
								$data['groupexpiry'] = $grouptime;
							}
							if ($card['groupdays'] < 1) {
								$data['groupexpiry'] = 0;
							}
							if($data){
								DB::update('members', $data, "uid='$uid'");
							}
						}
						DB::query("UPDATE " . DB::table('card') . " SET status='2', uid='$uid', usedate = '$timestamp' WHERE cardid='{$card['cardid']}'");
						update_membercount($uid, array($card['creditskey'] => $card['creditsval']), TRUE, 'TUC', 1);
						$paramextra['type'] = 'succeed';
						showmessage('credits_card_succeed', 'member.php?action=credit&do=basic', array(
						'title' => phpcom::$setting['credits'][$card['creditskey']]['title'],
						'value' => $card['creditsval']), $paramextra);
					}
				} else {
					$price = intval($this->request->getPost('price'));
					$timestamp = phpcom::$G['timestamp'];
					if (!$price) {
						showmessage('credits_pay_amount_undefined', NULL, array(), $paramextra);
					}
					$pay_creditsratio = intval(phpcom::$setting['pay_creditsratio']);
					$pay_mincredits = intval(phpcom::$setting['pay_mincredits']);
					$pay_maxcredits = intval(phpcom::$setting['pay_maxcredits']);
					if ($pay_creditsratio > 0) {
						$amount = $price * $pay_creditsratio;
					} else {
						$amount = $price;
					}
					if (($pay_mincredits && $amount < $pay_mincredits) || ($pay_maxcredits && $amount > $pay_maxcredits)) {
						showmessage('credits_pay_amount_invalid', NULL, array('maxcredits' => $pay_maxcredits, 'mincredits' => $pay_mincredits), $paramextra);
					}
					if ($payapi != 'card' && DB::result_first("SELECT COUNT(*) FROM " . DB::table('userorder') . " WHERE uid='$uid' AND ordertime>='$timestamp'-120 LIMIT 1")) {
						showmessage('credits_pay_interval_ctrl', NULL, array(), $paramextra);
					}
					$orderid = '';
					require_once loadlibfile($payapi, 'api');
					$requesturl = get_credit_payurl($price, $orderid);
					$query = DB::query("SELECT orderid FROM " . DB::table('userorder') . " WHERE orderid='$orderid'");
					if (DB::num_rows($query)) {
						showmessage('credits_pay_order_invalid', '', array(), $paramextra);
					}
					DB::insert('userorder', array(
						'orderid' => $orderid,
						'status' => 1,
						'uid' => $uid,
						'subject' => 'credit',
						'buyer' => phpcom::$G['username'],
						'payapi' => $payapi,
						'amount' => $amount,
						'price' => $price,
						'email' => phpcom::$G['member']['email'],
						'ordertime' => phpcom::$G['timestamp'],
						'ip' => phpcom::$G['clientip']
					));
					if (phpcom::$G['inajax']) {
						$this->loadAjaxHeader();
						echo '<form id="payonlienform" name="payonlienform" action="' . $requesturl . '" method="post"></form>';
						echo '<script type="text/javascript" reload="1">setTimeout("document.getElementById(\'payonlienform\').submit();",500);</script>';
						$this->loadAjaxFooter();
					} else {
						header("location: " . str_replace('&amp;', '&', $requesturl));
					}
					return 0;
				}
			} else {
				$creditsratio = intval(phpcom::$setting['pay_creditsratio']);
				$mincredits = intval(phpcom::$setting['pay_mincredits']);
				$maxcredits = intval(phpcom::$setting['pay_maxcredits']);
				$creditstitle = phpcom::$setting['creditstrans']['title'];
				$creditsunit = phpcom::$setting['creditstrans']['unit'];
				$cardchecked = $this->request->query('card') && phpcom::$setting['card']['enabled'];
				$payapilist = array();
				if (phpcom::$setting['payonline'] && phpcom::$setting['pay_creditsratio']) {
					if (phpcom::$setting['pay_alipay']['account'] && phpcom::$setting['pay_alipay']['partnerid'] && phpcom::$setting['pay_alipay']['key']) {
						$payapilist['alipay']['key'] = 'alipay';
						$payapilist['alipay']['name'] = lang('common', 'pay_alipay');
						if ($cardchecked) {
							$payapilist['alipay']['checked'] = '';
						} else {
							$payapilist['alipay']['checked'] = ' checked="checked"';
						}
					}
					if ((phpcom::$setting['pay_tenpay']['partnerid'] || phpcom::$setting['pay_tenpay']['escrow_chnid'])
							|| (phpcom::$setting['pay_tenpay']['key'] || phpcom::$setting['pay_tenpay']['escrow_key'])) {
						$payapilist['tenpay']['key'] = 'tenpay';
						$payapilist['tenpay']['name'] = lang('common', 'pay_tenpay');
						if ($cardchecked || $payapilist['alipay']['checked']) {
							$payapilist['tenpay']['checked'] = '';
						} else {
							$payapilist['tenpay']['checked'] = ' checked="checked"';
						}
					}
					if (phpcom::$setting['pay_chinabank']['partnerid'] && phpcom::$setting['pay_chinabank']['key']) {
						$payapilist['chinabank']['key'] = 'chinabank';
						$payapilist['chinabank']['name'] = lang('common', 'pay_chinabank');
						if ($cardchecked || $payapilist['alipay']['checked'] || $payapilist['tenpay']['checked']) {
							$payapilist['chinabank']['checked'] = '';
						} else {
							$payapilist['chinabank']['checked'] = ' checked="checked"';
						}
					}
				}else{
					$cardchecked = TRUE;
				}
				if (phpcom::$setting['card']['enabled']) {
					$payapilist['card']['key'] = 'card';
					$payapilist['card']['name'] = lang('common', 'pay_card');
					if ($cardchecked) {
						$payapilist['card']['checked'] = ' checked="checked"';
					} else {
						$payapilist['card']['checked'] = '';
					}
				}
			}
		} else {
			$usablecredits = $rulelist = array();
			foreach (phpcom::$setting['credits'] as $key => $credit) {
				if ($credit['enabled']) {
					$usablecredits[$key] = $credit;
				}
			}
			$keys = array_keys(phpcom::$setting['credits']);
			$timecycles = lang('member', 'credits_rule_timecycles');
			$alt = 0;
			$condition = '1';
			if ($this->request->query('rid')) {
				$ruleid = intval($this->request->query('rid'));
				$condition = "ruleid='$ruleid'";
			}
			$query = DB::query("SELECT * FROM " . DB::table('credit_rules') . " WHERE $condition ORDER BY ruleid DESC");
			while ($row = DB::fetch_array($query)) {
				$row['timecycle'] = $timecycles[$row['timecycle']];
				if (empty($row['rewnum'])) {
					$row['rewnum'] = lang('member', 'credits_rule_unlimited_time');
				}
				if ($this->checkRuleValue($row, $keys)) {
					$row['alt'] = $alt ? ' class="alt"' : '';
					$alt = $alt ? 0 : 1;
					$rulelist[$row['operation']] = $row;
				}
			}
		}
		include template('member/credit_basic');
		return 1;
	}
	
	protected function creditLog()
	{
		include template('member/credit_log');
		return 1;
	}
	
	protected function checkRuleValue($value, $creditkeys) {
		$havevalue = FALSE;
		foreach ($creditkeys as $key) {
			if ($value[$key]) {
				$havevalue = TRUE;
				break;
			}
		}
		return $havevalue;
	}
}
?>