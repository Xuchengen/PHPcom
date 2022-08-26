<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : UpgradeService.php  2012-12-18
 */
!defined('IN_PHPCOM') && exit('Access denied');

if(!defined('PHPCOM_VERSION')) {
	include_once PHPCOM_PATH . '/phpcom_version.php';
}

class UpgradeService
{
	private $upgradeurl = 'http://upgrade.cnxinyun.com/phpcom/';
	public $phpcomrelease;
	
	public function __construct() {
		$this->phpcomrelease = str_replace('/', '', PHPCOM_RELEASE);
	}
	
	public function checkUpgrade()
	{
		$response = array();
		
		$url = $this->upgradeurl . $this->getPathVersion() . $this->phpcomrelease . "/upgrade.xml";
		try {
			if($string = http_get_contents($url, 15.0)){
				if($xml = @new SimpleXMLExtended($string)){
					if($xml->level){
						$response = $xml->toArray();
					}
				}
			}
		} catch (Exception $e) {}
		phpcom_cache::savesetting('upgrader', $response);
		phpcom_cache::updater('setting');
		return $response;
	}
	
	public function getPathVersion()
	{
		$versions = explode(' ', trim(PHPCOM_VERSION));
		return trim($versions[0], 'version-x') . '/';
	}
	
}
?>