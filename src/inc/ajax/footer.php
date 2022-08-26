<?php
/** 
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : footer.php    2011-8-6 1:21:48
 */
!defined('IN_PHPCOM') && exit('Access denied');
$contents = ob_get_contents();
ob_end_clean();
$contents = preg_replace("/([\\x01-\\x08\\x0b-\\x0c\\x0e-\\x1f])+/", ' ', $contents);
$contents = str_replace(array(chr(0), ']]>'), array(' ', ']]&gt;'), $contents);
echo $contents;
echo "]]></root>";
exit();
?>
