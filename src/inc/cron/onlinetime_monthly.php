<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : onlinetime_monthly.php    2012-2-11
 */
!defined('IN_PHPCOM') && exit('Access denied');
DB::query("UPDATE ".DB::table('onlinetime')." SET thismonth='0'");
?>
