<?php
/**
 * Copyright (c) 2010-2012 phpmain.com - All rights reserved.
 * Our Website : www.phpmain.com www.phpmain.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPMain.
 * This File   : cleanup_searchindex.php  2012-3-22
 */
!defined('IN_PHPCOM') && exit('Access denied');
DB::query("TRUNCATE ".DB::table('searchindex'));
?>