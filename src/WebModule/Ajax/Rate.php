<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Rate.php  2012-8-17
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Ajax_Rate extends Controller_AjaxAbstract
{
	public function loadActionIndex()
	{
		$type = trim($this->request->query('type'));
		$tid = intval($this->request->query('tid'));
		$score = intval($this->request->query('score'));
		$voters = $total = $scores = 0;
		$chanid = $this->chanid;
		$percent = '0%';
		
		if($score > 0 && $score < 6){
			$score *= 2;
			$sql = "SELECT t.tid, t.chanid, f.voters, f.totalscore
					FROM " . DB::table('threads') . " t
					INNER JOIN " . DB::table('thread_field') . " f ON f.tid=t.tid WHERE t.tid='$tid'";
			if($thread = DB::fetch_first($sql)){
				$tid = $thread['tid'];
				$voters = $thread['voters'];
				$total = $thread['totalscore'];
				$this->chanid = $chanid = $thread['chanid'];
				$ratekey = "rating_$tid";
				if (empty(phpcom::$G['cookie'][$ratekey]) && $total < 0x77359400) {
					$voters++;
					$total += $score;
					DB::query("UPDATE " . DB::table('thread_field') . " SET voters=voters+1, totalscore=totalscore+'$score' WHERE tid='$tid'");
					phpcom::setcookie($ratekey, encryptstring(TIMESTAMP), 86400);
				}else{
					showmessage('rating_have_complete', NULL, NULL, array('showdialog' => TRUE));
				}
				$scores = $voters ? $total  / $voters : 0;
				$scores = $scores < 10 ? sprintf( "%01.1f ", $scores) : 10;
				$percent = $scores ? ($scores * 10) . '%' : '0%';
			}
		}
		
		include template('ajax/rate');
		$this->loadAjaxFooter();
		return 0;
	}
}
?>