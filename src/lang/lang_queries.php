<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : lang_queries.php  2013-11-18
 */
return <<< EOT
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS `pc_focus_image`;
UPDATE pc_channel SET modules='special', channelname='专题频道', subname='专题', codename='special', closed='0' WHERE channelid='4';
UPDATE pc_urlrules SET rulename='category', matchurl='{chandir}/{catdir}/index.html', actionurl='index.php?special/list-{catid}.html', description='专题栏目首页' WHERE modules='special' AND rulename='index';
UPDATE pc_urlrules SET rulename='threadlist', matchurl='{chandir}/{catdir}/list-{catid}-{page}.html', actionurl='index.php?special/list-{catid}-{page}.html', description='专题栏目列表页' WHERE modules='special' AND rulename='topic';
UPDATE pc_urlrules SET rulename='threadview', matchurl='{chandir}/{catdir}/view-{tid}.html', actionurl='index.php?special/view-{tid}.html', description='专题内容页面' WHERE modules='special' AND rulename='list';
UPDATE pc_urlrules SET rulename='topiclist', matchurl='{chandir}/{name}-{alias}.html', actionurl='index.php?special/topiclist-{tid}-{alias}.html', description='专题分类列表页' WHERE modules='special' AND rulename='comment';
INSERT INTO pc_urlrules VALUES ('41','special','toplist','{chandir}/toplist-{catid}-{type}.html','index.php?special/toplist-{catid}-{type}.html','专题排行榜','1');
INSERT INTO pc_urlrules VALUES ('42','article','toplist','{chandir}/toplist-{catid}-{type}.html','index.php?article/toplist-{catid}-{type}.html','文章排行榜','1');
INSERT INTO pc_urlrules VALUES ('43','soft','toplist','{chandir}/toplist-{catid}-{type}.html','index.php?soft/toplist-{catid}-{type}.html','软件排行榜','1');
INSERT INTO pc_urlrules VALUES ('44','photo','toplist','{chandir}/toplist-{catid}-{type}.html','index.php?photo/toplist-{catid}-{type}.html','图片排行榜','1');
INSERT INTO pc_urlrules VALUES ('45','video','toplist','{chandir}/toplist-{catid}-{type}.html','index.php?video/toplist-{catid}-{type}.html','视频排行榜','1');

EOT;
?>