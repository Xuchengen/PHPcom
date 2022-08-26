<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Digg.php  2012-8-5
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Ajax_Digg extends Controller_AjaxAbstract
{
	public function loadActionIndex()
	{
		$type = trim($this->request->query('type'));
		$tid = intval($this->request->query('tid'));
		$do = trim($this->request->query('do'));
		$score = intval($this->request->query('score'));
		$voteup = $votedown = $total = 0;
		$percentup = $percentdown = $percent = '0.00%';
		$voters = $total = $scores = $chanid = 0;
		
		$thread = DB::fetch_first("SELECT tid,voteup,votedown FROM " . DB::table('thread_field') . " WHERE tid='$tid'");
		if(!$thread){
			DB::query("SELECT tid FROM " . DB::table('threads') . " WHERE tid='$tid' limit 1");
			if(DB::affected_rows()){
				DB::insert('thread_field', array('tid' => $tid, 'voteup' => 0, 'votedown' => 0));
				$thread = DB::fetch_first("SELECT tid,voteup,votedown FROM " . DB::table('thread_field') . " WHERE tid='$tid'");
			}
		}
		if($thread){
			$tid = $thread['tid'];
			$voteup = $thread['voteup'];
			$votedown = $thread['votedown'];
			$diggkey = "diggbury_$tid";
			if (in_array($do, array('up', 'good', 'digg'))) {
				if (empty(phpcom::$G['cookie'][$diggkey]) && $voteup < 0x3B9ACA00) {
					$voteup++;
					DB::query("UPDATE " . DB::table('thread_field') . " SET voteup=voteup+1 WHERE tid='$tid'");
					phpcom::setcookie($diggkey, encryptstring(TIMESTAMP), 86400);
				}else{
					showmessage('digg_have_complete', NULL, NULL, array('showdialog' => TRUE));
				}
			} elseif (in_array($do, array('down', 'bad', 'bury'))) {
				if (empty(phpcom::$G['cookie'][$diggkey]) && $votedown < 0x3B9ACA00) {
					$votedown++;
					DB::query("UPDATE " . DB::table('thread_field') . " SET votedown=votedown+1 WHERE tid='$tid'");
					phpcom::setcookie($diggkey, encryptstring(TIMESTAMP), 86400);
				}else{
					showmessage('digg_have_complete', NULL, NULL, array('showdialog' => TRUE));
				}
			}
			$total = $voteup + $votedown;
			$percentup = ($voteup ? round(($voteup / $total) * 100, 2) : '0.00') . '%'; //sprintf('%.2f%%', ($voteup / $total) * 100);
			$percentdown = ($votedown ? round(($votedown / $total) * 100, 2) : '0.00') . '%';
			if($score > 0 && $score < 6){
				$score *= 2;
				$sql = "SELECT t.tid, t.chanid, f.voters, f.totalscore
					FROM " . DB::table('threads') . " t
					INNER JOIN " . DB::table('thread_field') . " f ON f.tid=t.tid WHERE t.tid='$tid'";
				if($thread = DB::fetch_first($sql)){
					$tid = $thread['tid'];
					$voters = $thread['voters'];
					$totalscore = $thread['totalscore'];
					$this->chanid = $chanid = $thread['chanid'];
					$ratekey = "rating_$tid";
					if (empty(phpcom::$G['cookie'][$ratekey]) && $totalscore < 0x77359400) {
						$voters++;
						$totalscore += $score;
						DB::query("UPDATE " . DB::table('thread_field') . " SET voters=voters+1, totalscore=totalscore+'$score' WHERE tid='$tid'");
						phpcom::setcookie($ratekey, encryptstring(TIMESTAMP), 86400);
					}else{
						showmessage('rating_have_complete', NULL, NULL, array('showdialog' => TRUE));
					}
					$scores = $voters ? $totalscore  / $voters : 0;
					$scores = $scores < 10 ? sprintf( "%01.1f ", $scores) : 10;
					$percent = $scores ? ($scores * 10) . '%' : '0%';
				}
			}
		}
		include template('ajax/digg');
		$this->loadAjaxFooter();
		return 0;
	}
}
?>