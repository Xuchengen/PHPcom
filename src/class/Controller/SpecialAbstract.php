<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : SpecialAbstract.php  2012-8-11
 */
!defined('IN_PHPCOM') && exit('Access denied');

abstract class Controller_SpecialAbstract extends Controller_MainAbstract
{
	protected $specialid = 0;
	protected $cacheSpecial = array();
	
	public function __construct(Web_HttpRequest $request)
	{
		parent::__construct($request);
		phpcom_cache::load('special');
		phpcom_cache::load('specialclass');
		$this->iscaptcha = intval(phpcom::$setting['captchastatus'][4]);
		$this->cacheSpecial = phpcom::$G['cache']['special'];
		$this->keyword = lang('common', 'special_keyword');
		$this->description = lang('common', 'special_description');
	}
	
	protected function getSpecialData($parentid = 0, $limit = 0)
	{
		$data = array();
		if(isset($this->cacheSpecial[$parentid])){
			$i = 0;
			foreach($this->cacheSpecial[$parentid] as $special){
				$i++;
				$special['index'] = $i;
				$special['alt'] = $i % 2 == 0 ? 2 : 1;
				$special['color'] = $special['color'] ? ' style="color: ' . $special['color'] . '"' : '';
				$special['title'] = trim($special['subject']);
				$special['domain'] = $special['domain'] ? trim($special['domain'], "/\\ \t") . '/' : $this->domain;
				$special['url'] = geturl('index', array(
						'sid' => $special['specid'],
						'name' => $special['name'],
						'action' => $special['name']
						), $special['domain'], 'special');
				$data[$special['specid']] = $special;
				if($limit && $i >= $limit){
					break;
				}
			}
		}
		return $data;
	}
}
?>