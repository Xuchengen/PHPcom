<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Counts.php  2012-10-8
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Counts
{
	protected $request;
	
	public function __construct(Web_HttpRequest $request = null) {
		if (empty(phpcom::$setting)) {
			phpcom_cache::load('setting');
		}
		$todaytime = strtotime(date('Ymd'));
		$weektime = strtotime("last Sunday") + 86400;
		$monthtime = mktime(0, 0, 0, date("m"), 1, date("Y"));
		
		if (!isset(phpcom::$G['cache']['lastruntime'])) {
			phpcom_cache::load('lastruntime');
			if(empty(phpcom::$G['cache']['lastruntime']['today'])){
				$this->updateTimeCahce();
			}
		}
		
		if(!isset(phpcom::$G['cache']['lastruntime']['today'])){
			$this->updateTimeCahce();
		}
		
		if(phpcom::$G['cache']['lastruntime']['week'] != $weektime){
			DB::query("UPDATE " . DB::table('threads') . " SET lastweek=weekcount, weekcount='0'");
		}
		
		if(phpcom::$G['cache']['lastruntime']['month'] != $monthtime){
			DB::query("UPDATE " . DB::table('threads') . " SET lastmonth=monthcount, monthcount='0'");
		}
		
		if(phpcom::$G['cache']['lastruntime']['today'] != $todaytime){
			$this->updateTimeCahce();
		}
		$this->request = $request;
	}
	
	public function updateTimeCahce()
	{
		$lastruntime = array(
				'today' => strtotime(date('Ymd')),
				'week' => strtotime("last Sunday") + 86400,
				'month' => mktime(0, 0, 0, date("m"), 1, date("Y")),
				'year' => mktime(0, 0, 0, 1, 1, date("Y"))
		);
		phpcom_cache::save('lastruntime', $lastruntime);
		phpcom::$G['cache']['lastruntime'] = $lastruntime;
	}
	
	public function outputImage()
	{
		header('Content-Type: image/gif');
		echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
	}
	
	public static function getInstance()
	{
		static $_instance = null;
		if($_instance == null){
			$_instance = new Counts(new Web_HttpRequest());
		}
		return $_instance;
	}
	
	public static function runOutput()
	{
		$counts = self::getInstance();
		if($image = $counts->request->query('image')){
			$counts->outputImage();
		}
		if($tid = $counts->request->query('tid')){
			if(in_array(intval(phpcom::$setting['statclosed']), array(0, 1), true)){
				$counts->thread($tid);
			}
		}
		/*$data = array(
			'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'],
			'HTTP_REFERER' => $_SERVER['HTTP_REFERER']
		);*/
	}
	
	public function thread($tid, $statclosed = false)
	{
		if(($tid = intval($tid)) && !$statclosed){
			$query = DB::query("SELECT tid FROM " . DB::table('threads') . " WHERE tid='$tid'");
			if($thread = DB::fetch_array($query)){
				$tid = $thread['tid'];
				DB::query("UPDATE " . DB::table('threads') . " SET hits=hits+'1', lastdate='".TIMESTAMP."', weekcount=weekcount+'1', monthcount=monthcount+'1' WHERE tid='$tid'");
			}
		}
	}
	
	public function topical($topicid)
	{
		if($topicid = intval($topicid)){
			DB::query("UPDATE " . DB::table('topical') . " SET hits=hits+1 WHERE topicid='$topicid'");
		}
	}
	
	public function isSearch()
	{
		$searchmap = array('baidu' => 'baidu.', 'google' => 'google.');
	}
	
	public function writeline($data, $filename = 'tmp.txt')
	{
		$filename = PHPCOM_ROOT . "/data/$filename";
		if($fp = @fopen($filename, 'a')){
			@flock($fp, 2);
			$data = is_array($data) ? $data : array($data);
			foreach ($data as $k => $v) {
				$v = trim($v);
				if(null !== $v && $v !== ''){
					if(is_numeric($k))
						fwrite($fp, "$v\r\n");
					else
						fwrite($fp, "$k: $v\r\n");
				}
			}
			fclose($fp);
		}
	}
}
?>