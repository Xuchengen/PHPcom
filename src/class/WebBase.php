<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : webbase.php  2012-7-8
 */
!defined('IN_PHPCOM') && exit('Access denied');

class WebBase
{
	var $htmlstatus, $domain;
	var $instdir, $webname, $website;
	var $charset, $templatedir, $tpldir;
	var $regmodule, $registerurl, $loginurl;
	var $memberurl, $keyword, $description;
	
	public function __construct() {
		$this->instdir = phpcom::$G['instdir'];
		$this->domain = $this->instdir;
		$this->htmlstatus = phpcom::$setting['htmlstatus'];
		$this->webname = phpcom::$setting['webname'];
		$this->website = phpcom::$setting['website'];
		$this->charset = CHARSET;
		$this->templatedir = phpcom::$setting['templatedir'];
		$this->tpldir = TEMPLATE_DIR . '/' . $this->templatedir;
		if (defined('DOMAIN_ENABLED')) {
			$this->domain = $this->website . $this->instdir;
		}
		$this->regmodule = phpcom::$setting['register']['modname'];
		$this->regmodule = $this->regmodule ? $this->regmodule : 'register';
		$this->registerurl = geturl('member', array('action' => $this->regmodule), $this->domain);
		$this->loginurl = geturl('member', array('action' => 'login'), $this->domain);
		$this->memberurl = geturl('member', array('action' => 'index'), $this->domain);
		$this->keyword = phpcom::$setting['keyword'];
		$this->description = phpcom::$setting['description'];
    }
	
	public function getChannelMenu($key, $channel)
	{
		if ($key != 'mod' && !$channel['closed']) {
			$channel['name'] = $channel['channelname'];
			if ($channel['type'] == 'menu') {
				$channel['url'] = $channel['domain'];
				$channel['current'] = '';
			} else {
				if ($channel['domain']) {
					$channel['domain'] = $channel['domain'] . '/';
				} else {
					$channel['domain'] = $domain;
				}
				$channel['url'] = geturl('index', array(
						'module' => $channel['modules'],
						'action' => $channel['codename'],
						'channel' => $channel['codename'],
						'channelid' => $channel['channelid']
				),$channel['domain']);
				if ($channel['channelid'] == phpcom::$G['channelid']) {
					$channel['current'] = 'current';
				} else {
					$channel['current'] = '';
				}
			}
		}
		return $channel;
	}
}
?>