<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : MemberThreadLog.php  2013-7-8
 */
!defined('IN_PHPCOM') && exit('Access denied');

class MemberThreadLog
{
	private $uid = 0;
	private $chanid = 0;
	private $module = 'special';
	private $islogs = true;
	private $username = '' ;
	private $extratime = 68400;
	private $addedrule = array('article' => 2, 'soft' => 3, 'photo' => 2, 'ask' => 2, 'video' => 3, 'special' => 5, 'comment' => 1);
	private $updaterule = array('article' => 0, 'soft' => 1, 'photo' => 1, 'ask' => 1, 'video' => 1, 'special' => 1, 'comment' => 1);
	
	public function __construct($chanid = 0) {
		$this->uid = intval(phpcom::$G['uid']);
		$this->username = trim(phpcom::$G['username']);
		$this->chanid = $chanid;
		if($chanid > 0){
			phpcom::$G['channelid'] = $chanid;
			if(isset(phpcom::$G['channel'][$chanid]['modules'])){
				$this->module = phpcom::$G['channel'][$chanid]['modules'];
			}
		}
		$this->islogs = empty(phpcom::$setting['threadlog']) ? false : true;
		if(defined('IN_PHPCOM_BUSINESS') && IN_PHPCOM_BUSINESS){
			$this->islogs = true;
		}else{
			$this->islogs = false;
		}
	}
	
	public function getScores($type = 'added', $key = null)
	{
		$key = $key ? $key : $this->module;
		if($type == 'update' && isset($this->updaterule[$key])){
			return $this->updaterule[$key];
		}elseif(isset($this->addedrule[$key])){
			return $this->addedrule[$key];
		}
		return 0;
	}
	
	public function insert($tid)
	{
		if(!$this->islogs) return;
		$scores = $this->getScores();
		$data = array('tid' => $tid, 'uid' => $this->uid);
		$data['chanid'] = $this->chanid;
		$data['authorid'] = $this->uid;
		$data['dateline'] = time();
		$data['lastdate'] = time();
		$data['scores'] = $scores;
		$data['status'] = 1;
		DB::insert('member_thread_log', $data);
		$timestamp = strtotime(date('Ymd'));
		$extratime = $timestamp + $this->extratime;
		$tablename = ($this->module ? $this->module : 'special') . 's';
		$extracount = 0;
		if($counts = DB::fetch_first("SELECT cid, addedcount, extracount, scores, $tablename FROM " . DB::table('member_thread_count') . " WHERE dateline='$timestamp' AND uid='{$this->uid}'")){
			if(time() > $extratime || time() < $timestamp + 27000){
				$extracount = $counts['extracount'] + 1;
			}
			DB::update('member_thread_count', array(
				'lasttime' => time(),
				'addedcount' => $counts['addedcount'] + 1,
				'extracount' => $extracount,
				'scores' => $counts['scores'] + $scores,
				$tablename => $counts[$tablename] + 1,
			), array('cid' => $counts['cid']));
		}else{
			if(time() > $extratime || time() < $timestamp + 27000){
				$extracount = 1;
			}
			DB::insert('member_thread_count', array(
				'uid' => $this->uid,
				'username' => $this->username,
				'dateline' => $timestamp,
				'lasttime' => time(),
				'addedcount' => 1,
				'updatecount' => 0,
				'extracount' => $extracount,
				'scores' => $scores,
				$tablename => 1
			));
		}
	}
	
	public function update($tid, $authorid = 0, $lastdate = 0)
	{
		if(!$this->islogs) return 0;
		$scores = $this->getScores('update');
		$data = array('tid' => $tid, 'uid' => $this->uid);
		$data['chanid'] = $this->chanid;
		$data['authorid'] = $authorid ? $authorid : $this->uid;
		$data['dateline'] = time();
		$data['lastdate'] = $lastdate ? $lastdate : time();
		$data['scores'] = $scores;
		$data['status'] = 2;
		$timestamp = strtotime(date('Ymd'));
		$tablename = ($this->module ? $this->module : 'special') . 's';
		if($logs = DB::fetch_first("SELECT logid FROM " . DB::table('member_thread_log') . " WHERE tid='$tid' AND dateline>'$timestamp'")){
			return 0;
		}else{
			DB::insert('member_thread_log', $data);
			if($counts = DB::fetch_first("SELECT cid, updatecount, extracount, scores, $tablename FROM " . DB::table('member_thread_count') . " WHERE dateline='$timestamp' AND uid='{$this->uid}'")){
				DB::update('member_thread_count', array(
					'lasttime' => time(),
					'updatecount' => $counts['updatecount'] + 1,
					'scores' => $counts['scores'] + $scores,
					$tablename => $counts[$tablename] + 1,
				), array('cid' => $counts['cid']));
			}else{
				DB::insert('member_thread_count', array(
					'uid' => $this->uid,
					'username' => $this->username,
					'dateline' => $timestamp,
					'lasttime' => time(),
					'addedcount' => 0,
					'updatecount' => 1,
					'extracount' => 0,
					'scores' => $scores,
					$tablename => 1
				));
			}
		}
		return 1;
	}
	
	private function updateCount()
	{
		$timestamp = strtotime(date('Ymd'));
		if($counts = DB::fetch_first("SELECT * FROM " . DB::table('member_thread_count') . " WHERE uid='{$this->uid}' AND dateline>'$timestamp'")){
			
		}else{
			
		}
	}
}
?>