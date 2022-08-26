<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Qrcode.php  2012-12-27
 */
!defined('IN_PHPCOM') && header('location: ' . phpcom::$G['siteurl'] . 'misc/images/none.gif');

class Misc_Qrcode extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$tid = intval($this->request->query('tid'));
		$chanid = intval($this->request->query('chanid'));
		$downid = intval($this->request->query('downid'));
		$type = trim($this->request->query('type'));
		$this->chandomain = $this->website . $this->instdir;
		$qrcode = &phpcom::$setting['qrcode'];
		$text = phpcom::$setting['website'];
		
		$filename = 'qrcode.png';
		if($tid && !empty($qrcode['status']) && empty($downid)){
			$sql = "SELECT t.tid,t.channelid,t.catid,t.dateline,t.htmlname,t.url,c.codename
			FROM " . DB::table('threads') . " t
			LEFT JOIN " . DB::table('category') . " c USING(catid)
						WHERE t.status='1' AND t.tid='$tid'";
			if($thread = DB::fetch_first($sql)){
				$this->chanid = $chanid = $thread['channelid'];
				phpcom::$G['channelid'] = $chanid;
				$channel = &phpcom::$G['channel'][$chanid];
				if ($channel['domain']) {
					define('DOMAIN_ENABLED', true);
					$this->chandomain = trim($channel['domain'], ' /') . '/';
				}
				$this->initialize();
				$urlargs = array('chanid' => $chanid, 'page' => 1);
				$urlargs['catdir'] = $thread['codename'];
				$urlargs['date'] = $thread['dateline'];
				if(empty($thread['url'])){
					if ($this->htmlstatus) {
						$thread['htmlname'] = $thread['htmlname'] ? trim($thread['htmlname']) : $thread['tid'];
						$urlargs['tid'] = $thread['htmlname'];
					} else {
						$urlargs['tid'] = $thread['tid'];
					}
					$urlargs['name'] = $thread['htmlname'];
					$text = geturl('threadview',$urlargs, $this->chandomain);
				}else{
					$text = trim($thread['url']);
				}
				$filename = "qrcode_$tid.png";
			}
		}elseif(!empty($qrcode['status']) && $downid){
			if($download = DB::fetch_first("SELECT servid, downurl FROM " . DB::table('soft_download') . " WHERE tid='$downid' LIMIT 1")){
				$downurl = trim($download['downurl']);
				$servurl = $redirect = '';
				if($servid = $download['servid']){
					phpcom_cache::load('downserver');
					if(isset(phpcom::$G['cache']['downserver'][$servid])){
						foreach (phpcom::$G['cache']['downserver'][$servid] as $downserv){
							if(empty($downserv['child'])){
								$servurl = trim($downserv['servurl']);
								$redirect = $downserv['redirect'];
								break;
							}
						}
					}
				}
				if($servid && $servurl){
					if($redirect){
						$downurl = $servurl;
					}elseif(!parse_url($downurl, PHP_URL_SCHEME)){
						$downurl = $servurl . $downurl;
					}
				}
				$text = $downurl;
				$filename = "qrcode_dl$downid.png";
			}
		}elseif(strcasecmp($type, 'mobile') == 0 && !empty(phpcom::$setting['mobile']['domain'])){
			$text = trim(phpcom::$setting['mobile']['domain']);
			$filename = 'qrcode_mobile.png';
		}
		empty(phpcom::$config['output']['gzip']) && @ob_end_clean();
		header('Expires: ' . gmdate('D, d M Y H:i:s', TIMESTAMP + 3600) . ' GMT');
		header('Content-Encoding: none');
		header("Content-Disposition: inline; filename=$filename");
		QRcode::png($text, false, $qrcode['level'], $qrcode['size'], $qrcode['margin']);
		return 0;
	}
}
?>