<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Captcha.php  2012-8-9
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Misc_Captcha extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		if($this->request->query('update') == 'yes'){
			
		}elseif($this->request->query('check') == 'yes'){
			$this->loadAjaxHeader();
			echo $this->checkCaptcha($this->request->query('verifycode')) ? 'succeed' : 'invalid';
			$this->loadAjaxFooter();
		}else{
			$chkcode = mt_rand(100000, 999999);
			$s = sprintf('%04s', base_convert($chkcode, 10, 24));
			$chkcodeunits = 'BCEFGHJKMPQRTVWXY2346789';
			if ($chkcodeunits) {
				$chkcode = '';
				for ($i = 0; $i < 4; $i++) {
					$unit = ord($s{$i});
					$chkcode .= ( $unit >= 0x30 && $unit <= 0x39) ? $chkcodeunits[$unit - 0x30] : $chkcodeunits[$unit - 0x57];
				}
			}
			
			phpcom::setcookie('captcha', encryptstring(strtoupper($chkcode) . "\t" . (TIMESTAMP - 180)), 0, TRUE);
			@header("Expires: -1");
			@header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
			@header("Pragma: no-cache");
			$captcha = new Captcha();
			$captcha->code = $chkcode;
			$captcha->type = phpcom::$setting['captcha']['type'];
			$captcha->width = phpcom::$setting['captcha']['width'];
			$captcha->height = phpcom::$setting['captcha']['height'];
			$captcha->background = phpcom::$setting['captcha']['background'];
			$captcha->randline = phpcom::$setting['captcha']['randline'];
			$captcha->blurtext = phpcom::$setting['captcha']['blurtext'];
			$captcha->usegiffont = phpcom::$setting['captcha']['giffont'];
			$captcha->output();
			return 0;
		}
	}
}
?>