<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : dbupgrade.php  2013-11-11
 */
!defined('IN_PHPCOM') && exit('Access denied');

$langQueries = include(dirname(dirname(__FILE__)) . '/lang/lang_queries.php');

$upgrade12Queries = <<< EOT
ALTER TABLE pc_channel ADD column sitename varchar(50) NOT NULL DEFAULT '' AFTER icons;
ALTER TABLE pc_category ADD column num smallint(6) NOT NULL DEFAULT '0' AFTER sortord;
ALTER TABLE pc_threads ADD column digest tinyint(1) NOT NULL DEFAULT '0' AFTER focus;
ALTER TABLE pc_threads ADD INDEX digest (digest);
EOT;

$upgrade13Queries = <<< EOT
ALTER TABLE pc_threads ADD column locked tinyint(1) NOT NULL DEFAULT '0';

ALTER TABLE pc_category CHANGE `subject` `title` varchar(100) NOT NULL DEFAULT '';

ALTER TABLE pc_members ADD column special tinyint(4) NOT NULL DEFAULT '0' AFTER adminid;
ALTER TABLE pc_members ADD INDEX special (special);

ALTER TABLE pc_special ADD column title varchar(100) NOT NULL DEFAULT '' AFTER color;
ALTER TABLE pc_special ADD column prefix varchar(50) NOT NULL DEFAULT '' AFTER `name`;

ALTER TABLE pc_topical DROP INDEX recommend;
ALTER TABLE pc_topical CHANGE recommend digest tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE pc_topical ADD INDEX digest (digest);
ALTER TABLE pc_topical ADD column chanid smallint(5) unsigned NOT NULL DEFAULT '0' AFTER specid;
ALTER TABLE pc_topical ADD column uid int(10) unsigned NOT NULL DEFAULT '0' AFTER chanid;
ALTER TABLE pc_topical ADD INDEX uid (uid);
ALTER TABLE pc_topical ADD column tid int(10) unsigned NOT NULL DEFAULT '0' AFTER uid;
ALTER TABLE pc_topical ADD column existclass tinyint(1) NOT NULL DEFAULT '0' AFTER digest;
ALTER TABLE pc_topical ADD column author varchar(50) NOT NULL DEFAULT '' AFTER keyword;
ALTER TABLE pc_topical ADD column catids varchar(255) NOT NULL DEFAULT '' AFTER keyword;
ALTER TABLE pc_topical ADD column demourl varchar(255) NOT NULL DEFAULT '' AFTER catids;
ALTER TABLE pc_topical DROP column template;

ALTER TABLE pc_article_thread ADD column `demourl` varchar(255) NOT NULL DEFAULT '' AFTER `source`;
ALTER TABLE pc_photo_thread ADD column `demourl` varchar(255) NOT NULL DEFAULT '' AFTER `source`;
ALTER TABLE pc_video_thread ADD column `demourl` varchar(255) NOT NULL DEFAULT '' AFTER `version`;
ALTER TABLE pc_soft_thread ADD column `demourl` varchar(255) NOT NULL DEFAULT '' AFTER `contact`;
ALTER TABLE pc_soft_thread ADD column `release` varchar(20) NOT NULL DEFAULT '' AFTER `contact`;
ALTER TABLE pc_soft_thread ADD column `company` varchar(50) NOT NULL DEFAULT '' AFTER `license`;
ALTER TABLE pc_soft_thread CHANGE `md5sums` `checksum` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE pc_soft_thread DROP column shasums;

ALTER TABLE pc_adminmember ADD column fullname varchar(50) NOT NULL DEFAULT '' AFTER admingid;
ALTER TABLE pc_adminmember ADD column dateline int(10) unsigned NOT NULL DEFAULT '0' AFTER admingid;
UPDATE pc_adminmember SET dateline=UNIX_TIMESTAMP();

ALTER TABLE pc_article_content DROP column articleid;
ALTER TABLE pc_soft_content DROP column softid;
ALTER TABLE pc_soft_download DROP column softid;
ALTER TABLE pc_photo_content DROP column photoid;
ALTER TABLE pc_video_content DROP column videoid;
ALTER TABLE pc_video_address DROP column videoid;

DROP TABLE IF EXISTS `pc_member_thread_count`;
CREATE TABLE `pc_member_thread_count` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `username` varchar(30) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `lasttime` int(10) unsigned NOT NULL DEFAULT '0',
  `addedcount` mediumint(9) NOT NULL DEFAULT '0',
  `updatecount` mediumint(9) NOT NULL DEFAULT '0',
  `extracount` mediumint(9) NOT NULL DEFAULT '0',
  `scores` mediumint(9) NOT NULL DEFAULT '0',
  `articles` mediumint(9) NOT NULL DEFAULT '0',
  `softs` mediumint(9) NOT NULL DEFAULT '0',
  `photos` mediumint(9) NOT NULL DEFAULT '0',
  `videos` mediumint(9) NOT NULL DEFAULT '0',
  `specials` mediumint(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `dateline` (`dateline`,`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_member_thread_log`;
CREATE TABLE `pc_member_thread_log` (
  `logid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `authorid` int(10) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `lastdate` int(10) unsigned NOT NULL DEFAULT '0',
  `scores` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`logid`),
  KEY `uid` (`uid`,`dateline`),
  KEY `tid` (`tid`,`dateline`)
) ENGINE=MyISAM;

EOT;

$upgradeLatestQueries = <<< EOT
$langQueries;
ALTER TABLE `pc_attachment_article` DROP column `filename`;
ALTER TABLE `pc_attachment_article` ADD column `url` varchar(150) NOT NULL DEFAULT '' AFTER `description`;
ALTER TABLE `pc_attachment_soft` DROP column `filename`;
ALTER TABLE `pc_attachment_soft` ADD column `url` varchar(150) NOT NULL DEFAULT '' AFTER `description`;
ALTER TABLE `pc_attachment_photo` DROP column `filename`;
ALTER TABLE `pc_attachment_photo` ADD column `url` varchar(150) NOT NULL DEFAULT '' AFTER `description`;
ALTER TABLE `pc_attachment_video` DROP column `filename`;
ALTER TABLE `pc_attachment_video` ADD column `url` varchar(150) NOT NULL DEFAULT '' AFTER `description`;
ALTER TABLE `pc_attachment_temp` DROP column `filename`;

ALTER TABLE `pc_threads` ADD column `weekcount` int(11) NOT NULL DEFAULT '0' AFTER `hits`;
ALTER TABLE `pc_threads` ADD column `monthcount` int(11) NOT NULL DEFAULT '0' AFTER `weekcount`;
ALTER TABLE `pc_threads` ADD column `lastweek` int(11) NOT NULL DEFAULT '0' AFTER `monthcount`;
ALTER TABLE `pc_threads` ADD column `lastmonth` int(11) NOT NULL DEFAULT '0' AFTER `lastweek`;
ALTER TABLE `pc_threads` ADD INDEX `weekcount` (`weekcount`);
ALTER TABLE `pc_threads` ADD INDEX `monthcount` (`monthcount`);

ALTER TABLE `pc_category` ADD column `toptype` tinyint(1) NOT NULL DEFAULT '0' AFTER `keyword`;
ALTER TABLE `pc_category` ADD column `topmode` tinyint(1) NOT NULL DEFAULT '0' AFTER `toptype`;
ALTER TABLE `pc_category` ADD column `topnum` smallint(5) unsigned NOT NULL DEFAULT '0' AFTER `topmode`;
ALTER TABLE `pc_category` ADD column `toptitle` varchar(100) NOT NULL DEFAULT '' AFTER `topnum`;

ALTER TABLE `pc_attachment_article` DROP INDEX `tid`;
ALTER TABLE `pc_attachment_photo` DROP INDEX `tid`;
ALTER TABLE `pc_attachment_soft` DROP INDEX `tid`;
ALTER TABLE `pc_attachment_video` DROP INDEX `tid`;

ALTER TABLE `pc_attachment_article` ADD INDEX `tid` (`tid`,`image`,`sortord`);
ALTER TABLE `pc_attachment_photo` ADD INDEX `tid` (`tid`,`image`,`sortord`);
ALTER TABLE `pc_attachment_soft` ADD INDEX `tid` (`tid`,`image`,`sortord`);
ALTER TABLE `pc_attachment_video` ADD INDEX `tid` (`tid`,`image`,`sortord`);

ALTER TABLE `pc_topic_data` DROP INDEX `catid`;
ALTER TABLE `pc_topic_data` DROP INDEX `topicid`;
ALTER TABLE `pc_topic_data` CHANGE `topicid` `specid` int(10) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `pc_topic_data` CHANGE `catid` `classid` mediumint(8) unsigned NOT NULL DEFAULT '0';
ALTER TABLE `pc_topic_data` ADD INDEX `specid` (`specid`,`dateline`);
ALTER TABLE `pc_topic_data` ADD INDEX `classid` (`classid`,`dateline`);
ALTER TABLE `pc_topic_data` RENAME TO `pc_special_data`;

ALTER TABLE pc_topical ADD column catid smallint(5) unsigned NOT NULL DEFAULT '0' AFTER chanid;
		
CREATE TABLE `pc_attachment_special` (
  `attachid` int(10) unsigned NOT NULL DEFAULT '0',
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `sortord` smallint(5) unsigned NOT NULL DEFAULT '0',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `attachment` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(150) NOT NULL DEFAULT '',
  `url` varchar(150) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `thumb` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `preview` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `image` tinyint(1) NOT NULL DEFAULT '0',
  `remote` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `width` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`attachid`),
  KEY `uid` (`uid`),
  KEY `tid` (`tid`,`image`,`sortord`)
) ENGINE=MyISAM;

CREATE TABLE `pc_special_thread` (
  `specid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `rootid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `specname` varchar(80) NOT NULL DEFAULT '',
  `subject` varchar(100) NOT NULL DEFAULT '',
  `summary` varchar(255) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `years` smallint(5) unsigned NOT NULL DEFAULT '2013',
  `editor` varchar(30) NOT NULL DEFAULT '',
  `official` varchar(255) NOT NULL DEFAULT '',
  `demourl` varchar(255) NOT NULL DEFAULT '',
  `videourl` varchar(255) NOT NULL DEFAULT '',
  `domain` varchar(100) NOT NULL DEFAULT '',
  `tplname` varchar(50) NOT NULL DEFAULT '',
  `existclass` tinyint(1) NOT NULL DEFAULT '0',
  `tableindex` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`specid`,`tid`),
  UNIQUE KEY `tid` (`tid`),
  KEY `uid` (`uid`),
  KEY `catid` (`catid`,`dateline`),
  KEY `rootid` (`rootid`,`dateline`),
  KEY `years` (`years`)
) ENGINE=MyISAM;

CREATE TABLE `pc_special_content` (
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `keyword` varchar(100) NOT NULL DEFAULT '',
  `content` mediumtext NOT NULL,
  `message` mediumtext NOT NULL,
  `banner` varchar(100) NOT NULL DEFAULT '',
  `background` varchar(100) NOT NULL DEFAULT '',
  `tidlist` varchar(255) NOT NULL DEFAULT '',
  `tags` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tid`),
  UNIQUE KEY `tid` (`tid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_special_class`;
CREATE TABLE `pc_special_class` (
  `classid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '4',
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `alias` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(100) NOT NULL DEFAULT '',
  `about` varchar(255) NOT NULL DEFAULT '',
  `pagesize` smallint(6) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`classid`),
  KEY `tid` (`tid`,`alias`)
) ENGINE=MyISAM;

EOT;

$upgradeQueries = array(
		array('comment' => adminlang('db_query_user'), 'action' => null, 'query' => ''),
		array('comment' => adminlang('db_query_clear_userlog'), 'action' => 'exec', 'query' => 'TRUNCATE {tablepre}credit_log'),
		array('comment' => adminlang('db_query_clear_session'), 'action' => 'exec', 'query' => 'TRUNCATE {tablepre}session'),
		array('comment' => adminlang('db_query_upgrade'), 'action' => null, 'query' => ''),
		array('comment' => adminlang('db_query_upgrade_current'), 'action' => 'upgrade', 'query' => $upgradeLatestQueries),
		array('comment' => adminlang('db_query_upgrade13_current'), 'action' => 'upgrade13', 'query' => $upgrade13Queries),
		array('comment' => adminlang('db_query_upgrade12_current'), 'action' => 'upgrade12', 'query' => $upgrade12Queries),
);

function execUpgradeQueries($index){
	global $upgradeQueries;
	if(!isset($upgradeQueries[$index])){
		return false;
	}
	$tablepre = phpcom::$config['db'][1]['tablepre'];
	$dbcharset = phpcom::$config['db'][1]['charset'];
	$dbengine = empty(phpcom::$config['db']['engine']) ? 'MyISAM' : trim(phpcom::$config['db']['engine']);
	$dbengine = stricmp($dbengine, array('MyISAM', 'InnoDB', 'Aria'), true, 'MyISAM');
	$action = $upgradeQueries[$index]['action'];
	$affected_rows = 0;
	switch ($action) {
		case "upgrade12":
			if(!DB::column_exists('threads', 'digest')){
				$querysql = stripcslashes($upgradeQueries[$index]['query']);
				execDbQueries($querysql, $affected_rows);
				$index--;
			}else{
				break;
			}
		case "upgrade13":
			if(!DB::column_exists('topic_data', 'catid')){
				DB::query("ALTER TABLE " . DB::table('topic_data') . " ADD column catid smallint(5) unsigned NOT NULL DEFAULT '0' AFTER topicid");
				DB::query("ALTER TABLE " . DB::table('topic_data') . " ADD INDEX catid (catid,topicid,dateline)");
				//DB::query("UPDATE " . DB::table('threads') . " t INNER JOIN " . DB::table('topic_data') . " d ON d.tid=t.tid SET d.catid=t.catid");
			}
			if(DB::column_exists('threads', 'recommend')){
				DB::query("UPDATE " . DB::table('threads') . " SET digest='2' WHERE recommend='1'");
				DB::query("ALTER TABLE " . DB::table('threads') . " DROP column recommend");
			}
			if(!DB::table_exists('member_thread_count')){
				$querysql = stripcslashes($upgradeQueries[$index]['query']);
				execDbQueries($querysql, $affected_rows);
				$index--;
			}else{
				break;
			}
		case "upgrade":
			if(!DB::table_exists('attachment_special')){
				$querysql = stripcslashes($upgradeQueries[$index]['query']);
				execDbQueries($querysql, $affected_rows);
				DB::beginTransaction();
				importoldspecialcategory();
				importoldtopical();
				urlrules_update();
				if(DB::index_exists('attachment_article', 'sortord')){
					DB::query("ALTER TABLE `" . DB::table('attachment_article') . "` DROP INDEX `sortord`");
					DB::query("ALTER TABLE `" . DB::table('attachment_soft') . "` DROP INDEX `sortord`");
					DB::query("ALTER TABLE `" . DB::table('attachment_photo') . "` DROP INDEX `sortord`");
					DB::query("ALTER TABLE `" . DB::table('attachment_video') . "` DROP INDEX `sortord`");
				}
				upgrade_version();
				DB::commit();
			}
			return $affected_rows;
			break;
		case "query":
			$querysql = stripcslashes($upgradeQueries[$index]['query']);
			execDbQueries($querysql, $affected_rows);
			return $affected_rows;
			break;
		case "exec":
			$querysql = str_replace(array('{tablepre}', ' pc_', ' `pc_'), array($tablepre, " $tablepre", " `$tablepre"), $upgradeQueries[$index]['query']);
			try {
				DB::query($querysql);
				return intval(DB::affected_rows());
			} catch (Exception $e) {
				return false;
			}
			break;
		default:
			return false;
	}
	return true;
}

function upgrade_version() {
	include_once PHPCOM_PATH . '/phpcom_version.php';
	$version = PHPCOM_VERSION;
	DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES ('version', '$version', 'string')");
}

function execDbQueries($sql, &$affected_rows) {
	$tablepre = phpcom::$config['db'][1]['tablepre'];
	$dbcharset = phpcom::$config['db'][1]['charset'];
	$dbengine = empty(phpcom::$config['db']['engine']) ? 'MyISAM' : trim(phpcom::$config['db']['engine']);
	$dbengine = stricmp($dbengine, array('MyISAM', 'InnoDB', 'Aria'), true, 'MyISAM');
	
	$querysql = str_replace(array('{tablepre}', ' pc_', ' `pc_'), array($tablepre, " $tablepre", " `$tablepre"), $sql);
	$sqlquery = sqlsplit($querysql);
	DB::query("SET NAMES '$dbcharset';\n\n");
	DB::beginTransaction();
	foreach($sqlquery as $sql) {
		if($sql = trim($sql)) {
			$sql = synchtablestruct($sql, $dbcharset, $dbengine);
			try {
				DB::query($sql);
				if($sqlerror = DB::error()) {
					break;
				} else {
					$affected_rows += intval(DB::affected_rows());
				}
			} catch (Exception $e) {
				continue;
			}
		}
	}
	DB::commit();
}

function getlatestcatid() {
	$catid = (int)DB::result_first("SELECT MAX(catid) FROM " . DB::table('category'));
	return $catid + 1;
}

function importoldspecialcategory(){
	$catid = getlatestcatid();
	$i = 1;
	$sql = "SELECT * FROM " . DB::table('special') . " ORDER BY sortord";
	$query = DB::query($sql);
	while ($row = DB::fetch_array($query)) {
		$data = array('catid' => $catid, 'chanid' => 4, 'depth' => 0, 'rootid' => $catid);
		$data['parentid'] = 0;
		$data['child'] = 0;
		$data['basic'] = 0;
		$data['sortord'] = $i;
		$data['catname'] = addslashes($row['subject']);
		$data['subname'] = addslashes($row['subject']);
		$data['codename'] = addslashes($row['name']);
		$data['prefix'] = addslashes($row['prefix']);
		$data['color'] = addslashes($row['color']);
		$data['title'] = addslashes($row['title']);
		$data['description'] = addslashes($row['description']);
		$data['keyword'] = addslashes($row['keyword']);
		$data['counts'] = 0;
		$data['setting'] = '';
		DB::insert('category', $data);
		DB::update('topical', array('catid' => $catid), "specid='{$row['specid']}'");
		++$catid;
		++$i;
	}
	DB::query("DROP TABLE IF EXISTS " . DB::table('special'));
	return true;
}

function importoldtopical(){
	$sql = "SELECT * FROM " . DB::table('topical');
	$query = DB::query($sql);
	while ($row = DB::fetch_array($query)) {
		$thread = array('chanid' => 4);
		$thread['catid'] = $row['catid'];
		$thread['uid'] = $row['uid'];
		$thread['title'] = addslashes($row['title']);
		$thread['htmlname'] = addslashes($row['codename']);
		$thread['dateline'] = $row['dateline'];
		$thread['hits'] = $row['hits'];
		$thread['attached'] = 0;
		$thread['image'] = $row['image'];
		$thread['topline'] = $row['topline'];
		$thread['focus'] = $row['focus'];
		$thread['digest'] = $row['digest'];
		$thread['bancomment'] = $row['bancomment'];
		
		$special = array('specid' => $row['topicid']);
		$special['specname'] = addslashes($row['subname']);
		$special['subject'] = addslashes($row['subject']);
		$special['summary'] = addslashes($row['description']);
		$special['editor'] = addslashes($row['author']);
		$special['demourl'] = addslashes($row['demourl']);
		//$special['videourl'] = addslashes($row['demourl']);
		$special['tplname'] = addslashes($row['tplname']);
		
		$messages = array();
		$messages['keyword'] = addslashes($row['keyword']);
		$messages['content'] = trim(addslashes($row['message']));
		$messages['message'] = '';
		$messages['banner'] = addslashes($row['banner']);
		$messages['background'] = addslashes($row['background']);
		$messages['tidlist'] = addslashes($row['tid']);
		
		$images = array('remote' => $row['remote'], 'thumb' => $row['thumb']);
		$images['attachment'] = addslashes($row['imageurl']);
		
		insertspecialdata($thread, $special, $messages, $images);
	}
	
	DB::query("UPDATE ".DB::table('special_data')." t 
			INNER JOIN ".DB::table('special_thread')." d ON t.specid=d.specid SET t.specid=d.tid, t.classid='0';");
}

function insertspecialdata($thread, $special, $messages = array(), $images = array()){
	$fields = array();
	if(!empty($thread['catid'])){
		$thread['catid'] = intval($thread['catid']);
		$thread['rootid'] = $thread['catid'];
		$special['catid'] = $thread['catid'];
		$special['rootid'] = $thread['rootid'];
	}else{
		return 0;
	}
	$special['chanid'] = $thread['chanid'];
	$special['uid'] = $thread['uid'];
	$special['dateline'] = $thread['dateline'];
	$special['years'] = '2013';
	$thread['lastdate'] = $thread['dateline'];
	$thread['tableindex'] = 0;
	$special['tableindex'] = 0;
	$thread['status'] = 1;
	
	if($tid = DB::insert('threads', $thread, TRUE)){
		$special['tid'] = $tid;
		$fields['tid'] = $tid;
		$messages['tid'] = $tid;
		$fields['isupdate'] = 1;
		DB::insert('thread_field', $fields);

		if(DB::insert('special_thread', $special)){
			DB::insert('special_content', $messages);
			
			if($thread['image'] == 1){
				$images['tid'] = $tid;
				DB::insert('thread_image', $images);
			}
		}
	}
}

function urlrules_update() {
	
	DB::query("DROP TABLE IF EXISTS " . DB::table('topical'));
	DB::query("DROP TABLE IF EXISTS " . DB::table('topic_data'));
	DB::query("DROP TABLE IF EXISTS " . DB::table('topic_comment'));
	DB::query("DROP TABLE IF EXISTS " . DB::table('topic_attachment'));
	DB::query("DROP TABLE IF EXISTS " . DB::table('topic_class'));
	
	$datalist = array();
	$query = DB::query("SELECT * FROM " . DB::table('urlrules') . " WHERE 1=1 ORDER BY ruleid ASC");
	while ($row = DB::fetch_array($query)) {
		$datalist[$row['modules']][$row['rulename']] = $row;
	}
	DB::query("TRUNCATE TABLE " . DB::table('urlrules'));
	$i = 0;
	foreach ($datalist['article'] as $data){
		$i++;
		$data['ruleid'] = $i;
		DB::insert('urlrules', $data);
	}
	
	foreach ($datalist['soft'] as $data){
		$i++;
		$data['ruleid'] = $i;
		DB::insert('urlrules', $data);
	}
	
	foreach ($datalist['photo'] as $data){
		$i++;
		$data['ruleid'] = $i;
		DB::insert('urlrules', $data);
	}
	
	foreach ($datalist['video'] as $data){
		$i++;
		$data['ruleid'] = $i;
		DB::insert('urlrules', $data);
	}
	
	foreach ($datalist['special'] as $data){
		$i++;
		$data['ruleid'] = $i;
		DB::insert('urlrules', $data);
	}
	
	foreach ($datalist['main'] as $data){
		$i++;
		$data['ruleid'] = $i;
		DB::insert('urlrules', $data);
	}
}
?>