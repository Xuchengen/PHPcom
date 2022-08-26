<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Cron.php  2012-7-29
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Cron
{
	protected $timeOffset = 8;
	
	public function __construct() {
		if (empty(phpcom::$setting)) {
			phpcom_cache::load('setting');
		}
		if (!isset(phpcom::$G['cache']['cronruntime'])) {
			phpcom_cache::load('cronruntime');
		}
		$timeoffset = empty(phpcom::$setting['timeoffset']) ? 8 : phpcom::$setting['timeoffset'];
		if (function_exists('date_default_timezone_set')) {
			@date_default_timezone_set('Etc/GMT' . ($timeoffset > 0 ? '-' : '+') . (abs($timeoffset)));
		}
		$this->timeOffset = $timeoffset;
    }
    
	public function outputImage()
	{
		header('Content-Type: image/gif');
		echo base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
	}
	
	public static function run($cronid = 0)
	{
		$cron = new Cron();
		$cron->setTimeOffset(phpcom::$setting['timeoffset']);
		$cron->runCron($cronid);
	}
	
	public static function runOutput()
	{
		@set_time_limit(1000);
		updatesession();
		$cron = new Cron();
		$cron->setTimeOffset(phpcom::$setting['timeoffset']);
		$cron->outputImage();
		if (phpcom::$G['cache']['cronruntime'] <= TIMESTAMP) {
			$cron->runCron();
		}
	}
	
	public function setTimeOffset($offset = 8)
	{
		$this->timeOffset = $offset;
	}
	
	public function runCron($cronid = 0)
	{
		$timestamp = TIMESTAMP;
		$cronched = DB::fetch_first("SELECT * FROM " . DB::table('cron_entry') . "
				WHERE " . ($cronid ? "cronid='$cronid'" : "status>'0' AND nextruntime<='$timestamp'") . "
				ORDER BY nextruntime LIMIT 1");
		$processname = 'PHP_Cron_' . (empty($cronched) ? 'CHECKER' : $cronched['cronid']);
		if ($cronid && !empty($cronched)) {
			process::unlock($processname);
		}
		
		if (process::locked($processname, 600)) {
			return FALSE;
		}
		
		if ($cronched) {
			$cronched['filename'] = str_replace(array('..', '/', '\\'), '', $cronched['filename']);
			$taskfile = PHPCOM_PATH . '/inc/cron/' . $cronched['filename'];
			$cronched['minute'] = explode("\t", $cronched['minute']);
			$this->setNextRunTime($cronched);
			@set_time_limit(1000);
			@ignore_user_abort(TRUE);
			if (!@include $taskfile) {
				return FALSE;
			}
		}
		$this->nextTask();
		process::unlock($processname);
		return TRUE;
	}
	
	private function nextTask()
	{
		$nextrun = DB::fetch_first("SELECT nextruntime FROM " . DB::table('cron_entry') . " WHERE status='1' ORDER BY nextruntime LIMIT 1");
		if ($nextrun && isset($nextrun['nextruntime'])) {
			phpcom_cache::save('cronruntime', $nextrun['nextruntime']);
		} else {
			phpcom_cache::save('cronruntime', TIMESTAMP + 86400 * 365);
		}
		return TRUE;
	}
	
	private function setNextRunTime($cron)
	{
		if (empty($cron))
			return FALSE;
	
		list($yearnow, $monthnow, $daynow, $weekdaynow) = explode('-', gmdate('Y-m-d-w-H-i', TIMESTAMP + $this->timeOffset * 3600));
	
		if ($cron['weekday'] == -1) {
			if ($cron['day'] == -1) {
				$firstday = $daynow;
				$secondday = $daynow + 1;
			} else {
				$firstday = $cron['day'];
				$secondday = $cron['day'] + gmdate('t', TIMESTAMP + $this->timeOffset * 3600);
			}
		} else {
			$firstday = $daynow + ($cron['weekday'] - $weekdaynow);
			$secondday = $firstday + 7;
		}
	
		if ($firstday < $daynow) {
			$firstday = $secondday;
		}
	
		if ($firstday == $daynow) {
			$todaytime = $this->todayNextTask($cron);
			if ($todaytime['hour'] == -1 && $todaytime['minute'] == -1) {
				$cron['day'] = $secondday;
				$nexttime = $this->todayNextTask($cron, 0, -1);
				$cron['hour'] = $nexttime['hour'];
				$cron['minute'] = $nexttime['minute'];
			} else {
				$cron['day'] = $firstday;
				$cron['hour'] = $todaytime['hour'];
				$cron['minute'] = $todaytime['minute'];
			}
		} else {
			$cron['day'] = $firstday;
			$nexttime = $this->todayNextTask($cron, 0, -1);
			$cron['hour'] = $nexttime['hour'];
			$cron['minute'] = $nexttime['minute'];
		}
	
		$nextruntime = @gmmktime($cron['hour'], $cron['minute'] > 0 ? $cron['minute'] : 0, 0, $monthnow, $cron['day'], $yearnow) - $this->timeOffset * 3600;
	
		$crontatusadd = $nextruntime > TIMESTAMP ? '' : ", status='0'";
		$timestamp = TIMESTAMP;
		$cronid = $cron['cronid'];
		DB::query("UPDATE " . DB::table('cron_entry') . " SET lastruntime='$timestamp', nextruntime='$nextruntime' $crontatusadd WHERE cronid='$cronid'");
	
		return TRUE;
	}
	
	private function todayNextTask($cron, $hour = -2, $minute = -2)
	{
		$hour = $hour == -2 ? gmdate('H', TIMESTAMP + $this->timeOffset * 3600) : $hour;
		$minute = $minute == -2 ? gmdate('i', TIMESTAMP + $this->timeOffset * 3600) : $minute;
	
		$nexttime = array();
		if ($cron['hour'] == -1 && !$cron['minute']) {
			$nexttime['hour'] = $hour;
			$nexttime['minute'] = $minute + 1;
		} elseif ($cron['hour'] == -1 && $cron['minute'] != '') {
			$nexttime['hour'] = $hour;
			if (($nextminute = $this->nextMinute($cron['minute'], $minute)) === FALSE) {
				++$nexttime['hour'];
				$nextminute = $cron['minute'][0];
			}
			$nexttime['minute'] = $nextminute;
		} elseif ($cron['hour'] != -1 && $cron['minute'] == '') {
			if ($cron['hour'] < $hour) {
				$nexttime['hour'] = $nexttime['minute'] = -1;
			} elseif ($cron['hour'] == $hour) {
				$nexttime['hour'] = $cron['hour'];
				$nexttime['minute'] = $minute + 1;
			} else {
				$nexttime['hour'] = $cron['hour'];
				$nexttime['minute'] = 0;
			}
		} elseif ($cron['hour'] != -1 && $cron['minute'] != '') {
			$nextminute = $this->nextMinute($cron['minute'], $minute);
			if ($cron['hour'] < $hour || ($cron['hour'] == $hour && $nextminute === false)) {
				$nexttime['hour'] = -1;
				$nexttime['minute'] = -1;
			} else {
				$nexttime['hour'] = $cron['hour'];
				$nexttime['minute'] = $nextminute;
			}
		}
		return $nexttime;
	}
	
	private function nextMinute($nextminutes, $minutenow)
	{
		foreach ($nextminutes as $nextminute) {
			if ($nextminute > $minutenow) {
				return $nextminute;
			}
		}
		return FALSE;
	}
}
?>