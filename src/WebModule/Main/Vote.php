<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Vote.php  2012-9-17
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Main_Vote extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$tid = intval($this->request->getPost('tid'));
		$pollid = intval($this->request->getPost('pollid', $this->request->getQuery(0)));
		$action = trim($this->request->query('action'));
		$type = trim($this->request->query('type', $this->request->getQuery(1)));
		$pollanswers = striptags($this->request->post('pollanswers'));
		$isvote = false;
		$polltitle = $polltype = $expires = $pollurl = $url = $summary = '';
		$checkbox = $voters = $choices = $expiration = 0;
		$polloptions = $thread = $content = array();
		if ($pollid) {
			$pollkey = "pollvotes_$pollid";
			if(!empty(phpcom::$G['cookie'][$pollkey])){
				$isvote = true;
			}
			$sql = "SELECT * FROM " . DB::table('pollvotes') . " WHERE pollid='$pollid'";
			if($poll = DB::fetch_first($sql)){
				$tid = $poll['tid'];
				$pollid = $poll['pollid'];
				$polltitle = $poll['polltitle'];
				$checkbox = $poll['checkbox'];
				$choices = $checkbox ? $poll['choices'] : 1;
				$voters = $poll['voters'];
				if ($poll['checkbox']) {
					$polltype = lang('common', 'checkbox');
				} else {
					$polltype = lang('common', 'radio');
				}
				if ($poll['expiration']) {
					$expiration = fmdate($poll['expiration']);
					$expires = lang('common', 'pollexpiration') . $expiration;
				} else {
					$expiration = 0;
					$expires = '';
				}
				$this->title = $poll['polltitle'];
				$this->description = $poll['polltitle'];
				$this->keyword = $poll['polltitle'];
				if ($pollanswers) {
					if(($voteids = implodeids($pollanswers, "','", $choices)) && !$isvote){
						$isvote = true;
						$voters++;
						DB::query("UPDATE " . DB::table('polloption') . " SET votes=votes+1 WHERE voteid IN ($voteids)", 'UNBUFFERED');
						DB::query("UPDATE " . DB::table('pollvotes') . " SET voters=voters+1 WHERE pollid='$pollid'", 'UNBUFFERED');
						phpcom::setcookie($pollkey, encryptstring(TIMESTAMP), 86400);
					}else{
						showmessage('pollvotes_have_complete', NULL, NULL, array('showdialog' => TRUE));
					}
				}
				$polloptions = $this->polloptions($pollid, $checkbox);

				$sql = "SELECT t.*,c.* FROM " . DB::table('threads') . " t
					LEFT JOIN " . DB::table('category') . " c USING(catid)
					WHERE t.status='1' AND t.tid='$tid'";
				if($tid && ($thread = DB::fetch_first($sql))){
					$chanid = $thread['chanid'];
					$urlargs = array('chanid' => $chanid, 'catdir' => $thread['codename'], 'tid' => $thread['tid'],
							'date' => $thread['dateline'], 'cid' => $thread['catid'], 'catid' => $thread['catid'], 'page' => 1);
					if (phpcom::$G['channel'][$thread['chanid']]['domain']) {
						define('DOMAIN_ENABLED', true);
						$this->initialize();
						$thread['domain'] = phpcom::$G['channel'][$thread['chanid']]['domain'] . '/';
					} else {
						$thread['domain'] = $this->domain;
					}
					$urlargs['prefix'] = empty($thread['prefix']) ? '' : trim($thread['prefix']);
					$urlargs['name'] = empty($thread['htmlname']) ? '' : trim($thread['htmlname']);
					$thread['url'] = geturl('threadview', $urlargs, $thread['domain']);
					$url = $thread['url'];
						
					$tableName = $chanid && isset(phpcom::$G['channel'][$chanid]['tablename']) ? phpcom::$G['channel'][$chanid]['tablename'] : null;
					if($tableName){
						if($content = DB::fetch_first("SELECT * FROM " . DB::table($tableName) . " WHERE tid='$tid'")){
							$summary = $content['summary'];
						}
					}
				}

				$pollurl = geturl('vote', array('pid' => $pollid, 'tid' => $tid, 'vid' => $pollid), $this->domain, 'main');
			}
		}
		include template('vote');
		return 1;
	}
}
?>