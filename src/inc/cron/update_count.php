<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : update_count.php  2012-11-14
 */
!defined('IN_PHPCOM') && exit('Access denied');

phpcom_cache::updater('syscount');
phpcom_cache::load('syscount');

$timestamp = phpcom::$G['timestamp'];
DB::query("UPDATE " . DB::table('adverts') . " SET status='0' WHERE status='1' AND (expires>'0' AND expires<'$timestamp')");

?>