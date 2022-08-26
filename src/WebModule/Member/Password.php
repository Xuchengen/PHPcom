<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Password.php  2012-8-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Member_Password extends Controller_MemberAbstract
{
	public function loadActionIndex()
	{
		$this->title = lang('member', 'member_password');
		$paramextra = array('type' => 'alert', 'showdialog' => TRUE, 'location' => FALSE);
		if (checksubmit(array('formsubmit', 'btnsubmit'))) {
			return $this->submitPassword($paramextra);
		}else{
			return $this->displayPassword($paramextra);
		}
		
	}
	
	protected function displayPassword($paramextra)
	{
		$title = $this->title;
		$emailactivatemsg = '';
		if ($this->request->query('resend') == 1) {
			$resend = phpcom::getcookie('emailresend');
			$resend = empty($resend) ? TRUE : (TIMESTAMP - $resend) > 300;
			if ($resend) {
				$toemail = phpcom::$G['member']['email'];
				$this->checkSendEmail(phpcom::$G['member']['uid'], $toemail);
				phpcom::setcookie('emailresend', TIMESTAMP, 300);
				showmessage('send_activate_mail_succeed', NULL, array('email' => $toemail), array('type' => 'succeed', 'showdialog' => TRUE));
			} else {
				showmessage('send_activate_mail_error', NULL, NULL, $paramextra);
			}
		} else {
			$emailstatus = phpcom::$G['member']['emailstatus'];
			$email = phpcom::$G['member']['email'];
			if ($emailstatus) {
				$emailactivatemsg = lang('member', 'member_email_onactivate_message', array('email' => $email));
			} else {
				$emailactivatemsg = lang('member', 'member_email_inactivate_message', array('email' => $email));
			}
			$questionoption = '<option value="" selected>' . lang('member', 'member_question_keep') . '</option>';
			$questionarray = lang('member', 'member_question_array');
			foreach ($questionarray as $key => $value) {
				$questionoption .= "<option value=\"$key\">$value</option>";
			}
			include template('member/password');
			return 1;
		}
		return 0;
	}
	
	protected function submitPassword($paramextra)
	{
		$oldpasswd = trim($this->request->post('password'));
		if (empty($oldpasswd)) {
			showmessage('profile_password_invalid', NULL, NULL, $paramextra);
		} else {
		
		}
		$newpasswd = trim($this->request->post('password1'));
		$password2 = trim($this->request->post('password2'));
		if (!empty($newpasswd) && $newpasswd != $password2) {
			showmessage('profile_password_notmatch', NULL, NULL, $paramextra);
		}
		if (phpcom::$setting['captchastatus'][2]) {
			if (!$this->checkCaptcha($this->request->post('verifycode'))) {
				showmessage('captcha_verify_invalid', NULL, NULL, $paramextra);
			}
		}
		$email = trim($this->request->post('email'));
		if ($email && strtolower($email) == strtolower(phpcom::$G['member']['email'])) {
			$email = '';
		}
		$questionid = trim($this->request->post('questionid'));
		$answer = trim($this->request->post('answer'));
		loaducenter();
		$status = uc_user_edit(phpcom::$G['username'], $oldpasswd, $newpasswd, $email, 1, $questionid, $answer);
		switch ($status) {
			case -1:
				showmessage('profile_password_error', NULL, NULL, $paramextra);
				break;
			case UC_USER_CHECK_EMAIL_INVALID:
				showmessage('profile_email_format_invalid', NULL, NULL, $paramextra);
				break;
			case UC_USER_CHECK_EMAIL_DENIED:
				showmessage('profile_email_domain_denied', NULL, NULL, $paramextra);
				break;
			case UC_USER_CHECK_EMAIL_EXISTED:
				showmessage('profile_email_repeated', NULL, NULL, $paramextra);
				break;
			case -7:
				showmessage('profile_password_error', NULL, NULL, $paramextra);
				break;
			default: break;
		}
		if (!UC_SERVER_APP) {
			$data = array();
			if ($newpasswd && $status['password']) {
				$data['password'] = $status['password'];
			}
			if ($email) {
				$data['email'] = $email;
			}
			if ($questionid !== '') {
				if ($questionid > 0) {
					$data['qacode'] = questioncrypt($questionid, $answer);
				} else {
					$data['qacode'] = '';
				}
			}
			if ($data) {
				DB::update('members', $data, "uid='$uid'");
			}
		}
		if ($email) {
			$this->checkSendEmail($this->uid, $email);
		}
		showmessage('profile_update_succeed', 'member.php?action=password', NULL, array('type' => 'succeed', 'showdialog' => TRUE));
		return 0;
	}
	
	protected function checkSendEmail($uid, $email) {
		if ($uid && $email) {
			$hash = encryptstring("$uid\t$email\t" . TIMESTAMP, md5(substr(md5(phpcom::$config['security']['key']), 0, 16)));
			$verifyurl = phpcom::$G['siteurl'] . 'member.php?action=misc&amp;do=emailcheck&amp;hash=' . urlencode($hash);
			$mailsubject = lang('email', 'email_verify_subject');
			$mailmessage = lang('email', 'email_verify_message', array(
					'username' => phpcom::$G['member']['username'],
					'webname' => phpcom::$setting['webname'],
					'siteurl' => phpcom::$G['siteurl'],
					'url' => $verifyurl
			));
			if (!function_exists('sendmail')) {
				include loadlibfile('mail');
			}
			sendmail($email, $mailsubject, $mailmessage);
		}
	}
}
?>