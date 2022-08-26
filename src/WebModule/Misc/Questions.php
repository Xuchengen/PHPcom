<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Questions.php  2012-8-9
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Misc_Questions extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		if($this->request->query('check') == 'yes'){
			$this->loadAjaxHeader();
			echo $this->checkQuestionSet($this->request->query('answer')) ? 'succeed' : 'invalid';
			$this->loadAjaxFooter();
		}else{
			$referer = $this->request->server('HTTP_REFERER');
			$refhost = array('host' => '');
			if($referer){
				$refhost = parse_url($referer);
				$refhost['host'] .= isset($refhost['port']) ? (':' . $refhost['port']) : '';
			}
			if ($refhost['host'] != $this->request->server('HTTP_HOST')) {
				$this->loadAjaxHeader();
				echo 'Access Denied';
				$this->loadAjaxFooter();
				exit(0);
			}
			
			phpcom_cache::load('questionset');
			$questionkey = mt_rand(1, 49);
			$question = $answer = '';
			if(isset(phpcom::$G['cache']['questionset'][$questionkey]['question'])){
				$question = phpcom::$G['cache']['questionset'][$questionkey]['question'];
				$answer = phpcom::$G['cache']['questionset'][$questionkey]['answer'];
				if(phpcom::$G['cache']['questionset'][$questionkey]['type']){
					$answer = md5($this->makeQuestion($question));
				}
				phpcom::setcookie('questionset', encryptstring($answer . "\t" . (TIMESTAMP - 180)), 0, TRUE);
			}
			$this->loadAjaxHeader();
			echo $question;
			$this->loadAjaxFooter();
		}
		return 0;
	}
	
	public function makeQuestion(&$question) {
		$a = rand(1, 90);
		$b = rand(1, 10);
		if (rand(0, 1)) {
			$question = $a . ' + ' . $b . ' = ?';
			$answer = $a + $b;
		} else {
			$question = $a . ' - ' . $b . ' = ?';
			$answer = $a - $b;
		}
		return $answer;
	}
}
?>