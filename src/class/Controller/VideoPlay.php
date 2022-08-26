<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : VideoPlay.php  2012-8-20
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Controller_VideoPlay extends Controller_ThreadView
{
	protected $player;
	
	public function __construct(Web_HttpRequest $request)
	{
		parent::__construct($request);
		
		if(!isset(phpcom::$G['cache']['player'])){
			phpcom_cache::load('player');
		}
		$this->player = phpcom::$G['cache']['player'];
	}
	
	protected function fetchAllVideoAddress($tid, $domain, $catdir = '')
	{
		if($tid = intval($tid)){
			$data = array();
			$sql = "SELECT * FROM " . DB::table('video_address') . " WHERE tid='$tid' ORDER BY id";
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				if(trim($row['address'])){
					$row['address'] = $this->fetchPlayAddress($row['id'], $domain, $row, $catdir);
					$row['url'] = isset($row['address'][1]['url']) ? $row['address'][1]['url'] : '';
					$row['player'] = isset($this->player[$row['playerid']]) ? $this->player[$row['playerid']] : '';
					$row['caption'] = trim($row['caption']);
					$data[$row['id']] = $row;
				}
			}
			return $data;
		}
		return array();
	}
	
	protected function explodeAddress($string, $tid, $id = 0, $domain = '', $preurl = '', $catdir = '')
	{
		$data = array();
		if($string = trim($string)){
			$playurls = explode("\n", $string);
			$count = count($playurls);
			$i = 0;
			foreach ($playurls as $address){
				if($address = trim($address)){
					$i++;
					$data[$i]['index'] = sprintf("%02d", $i);
					$data[$i]['count'] = $count;
					$pos = strpos($address, '$$');
					if($pos !== false){
						$playurl = substr($address, $pos + 2);
						$data[$i]['title'] = substr($address, 0, $pos);
						$data[$i]['playurl'] = $preurl ? str_replace($preurl, '', $playurl) : $playurl;
					}else{
						if($count == 1){
							$data[$i]['title'] = lang('common', 'playstart_caption');
						}elseif($count == 2 && $i == 1){
							$data[$i]['title'] = lang('common', 'playfirst_caption');
						}elseif($count == 2 && $i == 2){
							$data[$i]['title'] = lang('common', 'playnext_caption');
						}else{
							$data[$i]['title'] = sprintf(lang('common', 'playurl_caption'), $data[$i]['index']);
						}
						$data[$i]['playurl'] = $preurl ? str_replace($preurl, '', $address) : $address;
					}
					$data[$i]['url'] = geturl('play', array(
							'chanid' => $this->chanid,
							'catdir' => $catdir,
							'name' => $catdir,
							'tid' => $tid,
							'id' => $id,
							'page' => $i
					), $domain);
				}
			}
		}
		return $data;
	}
	
	protected function fetchPlayAddress($id, $domain, $row = array(), $catdir = '')
	{
		$data = array();
		if($id = intval($id) && empty($row)){
			$row = DB::fetch_first("SELECT * FROM " . DB::table('video_address') . " WHERE id='$id'");
		}
		if($row && trim($row['address'])){
			$data = $this->explodeAddress($row['address'], $row['tid'], $row['id'], $domain, $this->player[$row['playerid']]['url'], $catdir);
		}
		return $data;
	}
}
?>