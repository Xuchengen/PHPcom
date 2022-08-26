<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Index.php  2013-11-7
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Special_Index extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$modname = $this->request->query('channel') ? $this->request->query('channel') : $this->request->getModule();
		$modname = strtolower(stripstring($modname));
		$chanid = 4;
		if(isset(phpcom::$G['channel']['module'][$modname])){
			$chanid = intval(phpcom::$G['channel']['module'][$modname]);
			if(phpcom::$G['channel'][$chanid]['modules'] !== 'special'){
				$chanid = 4;
			}
		}
		$this->chanid = $chanid;
		phpcom::$G['channelid'] = $this->chanid;
		phpcom::$G['cache']['channel'] = &phpcom::$G['channel'][$chanid];
		if (phpcom::$G['cache']['channel']['domain']) {
			define('DOMAIN_ENABLED', true);
		}
		$this->initialize();
		$title = strip_tags(phpcom::$G['cache']['channel']['channelname']);
		$this->channelname = phpcom::$G['cache']['channel']['channelname'];
		$this->title = empty(phpcom::$G['cache']['channel']['subject']) ? $title : phpcom::$G['cache']['channel']['subject'];
		$this->keyword = strip_tags(phpcom::$G['cache']['channel']['keyword'] ? phpcom::$G['cache']['channel']['keyword'] : phpcom::$setting['keyword']);
		$this->description = strip_tags(phpcom::$G['cache']['channel']['description'] ? phpcom::$G['cache']['channel']['description'] : phpcom::$setting['description']);
		if(!empty(phpcom::$G['cache']['channel']['sitename'])){
			$this->webname = phpcom::$G['cache']['channel']['sitename'];
		}
		$tplname = 'special/index_' . phpcom::$G['cache']['channel']['codename'];
		if(!tplfile_exists($tplname)){
			$tplname = checktplname('special/index', $this->chanid);
		}
		include template($tplname);
		return 1;
	}

	public function writeToHtml($content = '')
	{
		$channel = phpcom::$G['cache']['channel'];
		if($this->checkHtmlKey()){
			if(!empty($channel['chanroot'])){
				$filename = rtrim($channel['chanroot'], '/ \\') . '/index.html';
			}else{
				$codename = $channel['type'] == 'system' ? '' : $channel['codename'];
				$htmlfile = geturl('index', array(
						'module' => 'article',
						'domain' => $channel['codename'],
						'action' => $codename,
						'channel' => $channel['codename'],
						'channelid' => $channel['channelid']
				),'');
				if(!strpos(basename($htmlfile), '.')){
					$htmlfile = trim($htmlfile, ' /') . '/index.html';
				}
				$filename = PHPCOM_ROOT . '/' . $htmlfile;
			}
			fwrite_content($filename, $content);
		}
	}
}
?>