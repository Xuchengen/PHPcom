SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `pc_adcategory`;
CREATE TABLE `pc_adcategory` (
  `cid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `subject` varchar(80) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `display` tinyint(1) NOT NULL DEFAULT '0',
  `ctype` tinyint(1) NOT NULL DEFAULT '0',
  `maxads` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `width` varchar(10) NOT NULL DEFAULT '',
  `height` varchar(10) NOT NULL DEFAULT '',
  `buyable` tinyint(1) NOT NULL DEFAULT '0',
  `price` mediumint(9) NOT NULL DEFAULT '0',
  `units` tinyint(1) NOT NULL DEFAULT '0',
  `maxunit` smallint(5) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cid`),
  KEY `name` (`name`),
  KEY `status` (`status`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_adminfav`;
CREATE TABLE `pc_adminfav` (
  `favid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `uid` mediumint(10) unsigned NOT NULL DEFAULT '0',
  `favtype` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `shared` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`favid`),
  KEY `adminfav_dateline` (`uid`,`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_admingroup`;
CREATE TABLE `pc_admingroup` (
  `admingid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `groupname` varchar(50) NOT NULL DEFAULT '',
  `permission` text NOT NULL,
  PRIMARY KEY (`admingid`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `pc_adminmember`;
CREATE TABLE `pc_adminmember` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `admingid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `fullname` varchar(50) NOT NULL DEFAULT '',
  `permcustom` text NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_adminmenu`;
CREATE TABLE `pc_adminmenu` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `sortord` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `category` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `sortord` (`sortord`),
  KEY `uid` (`uid`,`category`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_adminsession`;
CREATE TABLE `pc_adminsession` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `adminid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(40) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `errcount` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_adverts`;
CREATE TABLE `pc_adverts` (
  `aid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `cid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(200) NOT NULL DEFAULT '',
  `advertiser` varchar(30) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `word` varchar(200) NOT NULL DEFAULT '',
  `src` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `highlight` tinyint(1) NOT NULL DEFAULT '0',
  `attached` tinyint(1) NOT NULL DEFAULT '0',
  `remote` tinyint(1) NOT NULL DEFAULT '0',
  `thumb` tinyint(1) NOT NULL DEFAULT '0',
  `width` varchar(10) NOT NULL DEFAULT '',
  `height` varchar(10) NOT NULL DEFAULT '',
  `displayorder` smallint(5) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `expires` int(10) unsigned NOT NULL DEFAULT '0',
  `counts` int(10) unsigned NOT NULL DEFAULT '0',
  `daycounts` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`),
  KEY `uid` (`uid`),
  KEY `cid` (`cid`,`displayorder`),
  KEY `status` (`status`),
  KEY `type` (`type`),
  KEY `expires` (`expires`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_announce`;
CREATE TABLE `pc_announce` (
  `aid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL DEFAULT '',
  `content` mediumtext NOT NULL,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `author` varchar(30) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `highlight` tinyint(1) NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`),
  KEY `type` (`aid`,`type`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_article_content`;
CREATE TABLE `pc_article_content` (
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `keyword` varchar(100) NOT NULL DEFAULT '',
  `content` mediumtext NOT NULL,
  `pagesize` smallint(5) unsigned NOT NULL DEFAULT '0',
  `trackback` varchar(255) NOT NULL DEFAULT '',
  `tags` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tid`),
  UNIQUE KEY `tid` (`tid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_article_thread`;
CREATE TABLE `pc_article_thread` (
  `articleid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `rootid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `subtitle` varchar(80) NOT NULL DEFAULT '',
  `summary` varchar(255) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `author` varchar(50) NOT NULL DEFAULT '',
  `source` varchar(50) NOT NULL DEFAULT '',
  `demourl` varchar(255) NOT NULL DEFAULT '',
  `editor` varchar(30) NOT NULL DEFAULT '',
  `tableindex` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`articleid`,`tid`),
  UNIQUE KEY `tid` (`tid`),
  KEY `uid` (`uid`),
  KEY `catid` (`catid`,`dateline`),
  KEY `rootid` (`rootid`,`dateline`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `pc_attachment`;
CREATE TABLE `pc_attachment` (
  `attachid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `tableid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `downcounts` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`attachid`),
  KEY `tid` (`tid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_attachment_article`;
CREATE TABLE `pc_attachment_article` (
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

DROP TABLE IF EXISTS `pc_attachment_photo`;
CREATE TABLE `pc_attachment_photo` (
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

DROP TABLE IF EXISTS `pc_attachment_soft`;
CREATE TABLE `pc_attachment_soft` (
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

DROP TABLE IF EXISTS `pc_attachment_special`;
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

DROP TABLE IF EXISTS `pc_attachment_temp`;
CREATE TABLE `pc_attachment_temp` (
  `attachid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '1',
  `module` varchar(30) NOT NULL DEFAULT '',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `attachment` varchar(100) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `thumb` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `preview` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `image` tinyint(1) NOT NULL DEFAULT '0',
  `remote` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `width` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`attachid`),
  KEY `uid` (`uid`,`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_attachment_video`;
CREATE TABLE `pc_attachment_video` (
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

DROP TABLE IF EXISTS `pc_badwords`;
CREATE TABLE `pc_badwords` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `admin` varchar(30) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `find` varchar(255) NOT NULL,
  `replacement` varchar(255) NOT NULL,
  `pattern` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `find` (`find`),
  KEY `type` (`type`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_banned`;
CREATE TABLE `pc_banned` (
  `banid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL DEFAULT '',
  `admin` varchar(30) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `expiration` int(10) unsigned NOT NULL DEFAULT '0',
  `reason` varchar(200) NOT NULL DEFAULT '',
  PRIMARY KEY (`banid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_card`;
CREATE TABLE `pc_card` (
  `cardid` char(50) NOT NULL DEFAULT '',
  `password` char(8) NOT NULL DEFAULT '',
  `typeid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `maker` char(30) NOT NULL,
  `price` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `creditskey` char(10) NOT NULL DEFAULT '',
  `creditsval` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `groupextid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `groupdays` smallint(5) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `cleardate` int(10) unsigned NOT NULL DEFAULT '0',
  `usedate` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cardid`),
  KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_card_type`;
CREATE TABLE `pc_card_type` (
  `typeid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `typename` varchar(30) NOT NULL DEFAULT '',
  `summary` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`typeid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_category`;
CREATE TABLE `pc_category` (
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `depth` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rootid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `parentid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `child` smallint(5) unsigned NOT NULL DEFAULT '0',
  `basic` smallint(1) NOT NULL DEFAULT '0',
  `catname` varchar(50) NOT NULL DEFAULT '',
  `subname` varchar(30) NOT NULL DEFAULT '',
  `codename` varchar(50) NOT NULL DEFAULT '',
  `prefixurl` varchar(80) NOT NULL DEFAULT '',
  `prefix` varchar(80) NOT NULL DEFAULT '',
  `color` varchar(80) NOT NULL DEFAULT '',
  `icons` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `keyword` varchar(100) NOT NULL DEFAULT '',
  `toptype` tinyint(1) NOT NULL DEFAULT '0',
  `topmode` tinyint(1) NOT NULL DEFAULT '0',
  `topnum` smallint(5) unsigned NOT NULL DEFAULT '0',
  `toptitle` varchar(100) NOT NULL DEFAULT '',
  `template` varchar(50) NOT NULL DEFAULT '',
  `remote` tinyint(1) NOT NULL DEFAULT '0',
  `imageurl` varchar(100) NOT NULL DEFAULT '',
  `banner` varchar(100) NOT NULL DEFAULT '',
  `sortord` int(10) unsigned NOT NULL DEFAULT '0',
  `num` smallint(6) NOT NULL DEFAULT '0',
  `pagesize` smallint(6) NOT NULL DEFAULT '0',
  `target` tinyint(1) NOT NULL DEFAULT '0',
  `caturl` varchar(255) NOT NULL DEFAULT '',
  `counts` int(10) unsigned NOT NULL DEFAULT '0',
  `setting` text NOT NULL,
  PRIMARY KEY (`catid`),
  KEY `chanid` (`chanid`,`sortord`,`catid`),
  KEY `depth` (`depth`,`sortord`,`catid`),
  KEY `parentid` (`parentid`),
  KEY `codename` (`codename`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_channel`;
CREATE TABLE `pc_channel` (
  `channelid` smallint(5) unsigned NOT NULL DEFAULT '1',
  `parentid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sortord` smallint(5) unsigned NOT NULL DEFAULT '0',
  `type` enum('expand','default','menu','system') NOT NULL DEFAULT 'expand',
  `modules` enum('video','photo','soft','ask','menu','shop','special','article') NOT NULL DEFAULT 'menu',
  `channelname` varchar(50) NOT NULL DEFAULT '',
  `subname` varchar(30) NOT NULL DEFAULT '',
  `codename` varchar(50) NOT NULL DEFAULT '',
  `color` varchar(50) NOT NULL DEFAULT '',
  `icons` varchar(50) NOT NULL DEFAULT '',
  `sitename` varchar(50) NOT NULL DEFAULT '',
  `subject` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `keyword` varchar(255) NOT NULL DEFAULT '',
  `domain` varchar(150) NOT NULL DEFAULT '',
  `chanroot` varchar(100) NOT NULL DEFAULT '',
  `deftable` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `target` tinyint(1) NOT NULL DEFAULT '0',
  `htmlout` tinyint(1) NOT NULL DEFAULT '0',
  `closed` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `counter` int(10) unsigned NOT NULL DEFAULT '0',
  `setting` text NOT NULL,
  PRIMARY KEY (`channelid`),
  KEY `sortord` (`sortord`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_comment_body`;
CREATE TABLE `pc_comment_body` (
  `bodyid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `commentid` int(10) unsigned NOT NULL DEFAULT '0',
  `first` tinyint(1) NOT NULL DEFAULT '0',
  `authorid` int(10) unsigned NOT NULL DEFAULT '0',
  `author` varchar(30) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `userip` varchar(40) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `voteup` int(10) unsigned NOT NULL DEFAULT '0',
  `votedown` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`bodyid`),
  KEY `firstcomment` (`first`,`status`,`commentid`),
  KEY `authorid` (`authorid`,`commentid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_comments`;
CREATE TABLE `pc_comments` (
  `commentid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `username` varchar(30) NOT NULL DEFAULT '',
  `lastdate` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(40) NOT NULL DEFAULT '',
  `num` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`commentid`),
  KEY `tid` (`tid`,`lastdate`),
  KEY `commentid` (`tid`,`commentid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_credit_log`;
CREATE TABLE `pc_credit_log` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `operation` char(3) NOT NULL DEFAULT '',
  `relateid` int(10) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `money` int(10) NOT NULL DEFAULT '0',
  `prestige` int(10) NOT NULL DEFAULT '0',
  `praise` int(10) NOT NULL DEFAULT '0',
  `currency` int(11) NOT NULL DEFAULT '0',
  KEY `uid` (`uid`),
  KEY `operation` (`operation`),
  KEY `dateline` (`dateline`),
  KEY `relateid` (`relateid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_credit_rule_log`;
CREATE TABLE `pc_credit_rule_log` (
  `logid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `ruleid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `fid` int(10) unsigned NOT NULL DEFAULT '0',
  `total` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `cyclecount` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `money` int(10) NOT NULL DEFAULT '0',
  `prestige` int(10) NOT NULL DEFAULT '0',
  `praise` int(10) NOT NULL DEFAULT '0',
  `currency` int(11) NOT NULL DEFAULT '0',
  `starttime` int(10) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`logid`),
  KEY `uid` (`uid`,`ruleid`,`fid`),
  KEY `dateline` (`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_credit_rules`;
CREATE TABLE `pc_credit_rules` (
  `ruleid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `rulename` varchar(50) NOT NULL DEFAULT '',
  `operation` varchar(30) NOT NULL DEFAULT '',
  `timecycle` tinyint(1) NOT NULL DEFAULT '0',
  `intervaltime` int(10) NOT NULL DEFAULT '0',
  `rewnum` smallint(6) NOT NULL DEFAULT '0',
  `norepeat` tinyint(1) NOT NULL DEFAULT '0',
  `money` smallint(6) NOT NULL DEFAULT '0',
  `prestige` smallint(6) NOT NULL DEFAULT '0',
  `praise` smallint(6) NOT NULL DEFAULT '0',
  `currency` smallint(6) NOT NULL DEFAULT '0',
  `fids` text NOT NULL,
  PRIMARY KEY (`ruleid`),
  KEY `operation` (`operation`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `pc_cron_entry`;
CREATE TABLE `pc_cron_entry` (
  `cronid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('user','system') NOT NULL DEFAULT 'system',
  `subject` varchar(50) NOT NULL DEFAULT '',
  `filename` varchar(50) NOT NULL,
  `lastruntime` int(10) unsigned NOT NULL DEFAULT '0',
  `nextruntime` int(10) unsigned NOT NULL DEFAULT '0',
  `weekday` tinyint(3) NOT NULL DEFAULT '0',
  `day` tinyint(3) NOT NULL DEFAULT '0',
  `hour` tinyint(3) NOT NULL DEFAULT '0',
  `minute` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`cronid`),
  KEY `nextruntime` (`status`,`nextruntime`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_downserver`;
CREATE TABLE `pc_downserver` (
  `servid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `depth` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `rootid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `servname` varchar(80) NOT NULL DEFAULT '',
  `servurl` varchar(200) NOT NULL DEFAULT '',
  `color` varchar(80) NOT NULL DEFAULT '',
  `icons` varchar(50) NOT NULL DEFAULT '',
  `sortord` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `parentid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `child` smallint(5) unsigned NOT NULL DEFAULT '0',
  `downmode` tinyint(1) NOT NULL DEFAULT '0',
  `redirect` tinyint(1) NOT NULL DEFAULT '0',
  `groupid` smallint(6) NOT NULL DEFAULT '0',
  `expires` int(10) unsigned NOT NULL DEFAULT '0',
  `lastdate` int(10) unsigned NOT NULL DEFAULT '0',
  `todaydown` int(10) unsigned NOT NULL DEFAULT '0',
  `downcount` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`servid`),
  KEY `rootid` (`rootid`,`sortord`),
  KEY `chanid` (`chanid`,`sortord`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_failedlogin`;
CREATE TABLE `pc_failedlogin` (
  `ip` char(40) NOT NULL DEFAULT '',
  `username` char(30) NOT NULL DEFAULT '',
  `logincount` smallint(5) unsigned NOT NULL DEFAULT '0',
  `lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip`,`username`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_favorites`;
CREATE TABLE `pc_favorites` (
  `favid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `fid` int(10) unsigned NOT NULL DEFAULT '0',
  `ftype` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`favid`),
  KEY `uid` (`uid`,`ftype`,`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_friendlinks`;
CREATE TABLE `pc_friendlinks` (
  `linkid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `logo` varchar(255) NOT NULL DEFAULT '',
  `color` varchar(50) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `category` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `sortord` int(10) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `expires` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`linkid`),
  KEY `links_sortord` (`sortord`),
  KEY `links_closed` (`closed`,`type`,`category`)
) ENGINE=MyISAM AUTO_INCREMENT=4;

DROP TABLE IF EXISTS `pc_friendrequest`;
CREATE TABLE `pc_friendrequest` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `fuid` int(10) unsigned NOT NULL DEFAULT '0',
  `fname` varchar(30) NOT NULL DEFAULT '',
  `grouping` smallint(6) NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `note` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`,`fuid`),
  KEY `fuid` (`fuid`),
  KEY `uid` (`uid`,`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_friends`;
CREATE TABLE `pc_friends` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `fuid` int(10) unsigned NOT NULL DEFAULT '0',
  `fname` varchar(30) NOT NULL DEFAULT '',
  `grouping` smallint(6) NOT NULL DEFAULT '0',
  `num` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `note` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`,`fuid`),
  KEY `fuid` (`fuid`),
  KEY `uid` (`uid`,`num`,`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_invitecode`;
CREATE TABLE `pc_invitecode` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `code` varchar(32) NOT NULL DEFAULT '',
  `inviter` varchar(30) NOT NULL DEFAULT '',
  `invitee` varchar(30) NOT NULL DEFAULT '',
  `groupid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL,
  `usedate` int(10) unsigned NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `orderid` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `invite_uid` (`uid`),
  KEY `invite_code` (`code`),
  KEY `invite_status` (`status`,`code`,`dateline`),
  KEY `invite_orderid` (`orderid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_invitelog`;
CREATE TABLE `pc_invitelog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `username` varchar(30) NOT NULL DEFAULT '',
  `unit` varchar(15) NOT NULL DEFAULT '',
  `reward` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(40) NOT NULL DEFAULT '',
  `type` smallint(1) NOT NULL,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `inviterecord_uid` (`uid`),
  KEY `inviterecord_type` (`type`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_member_count`;
CREATE TABLE `pc_member_count` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `money` int(10) NOT NULL DEFAULT '0',
  `prestige` int(10) NOT NULL DEFAULT '0',
  `currency` int(10) NOT NULL DEFAULT '0',
  `praise` int(10) NOT NULL DEFAULT '0',
  `digests` smallint(5) unsigned NOT NULL DEFAULT '0',
  `logins` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `threads` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `friends` smallint(6) unsigned NOT NULL DEFAULT '0',
  `onlinetime` smallint(5) unsigned NOT NULL DEFAULT '0',
  `attachsize` int(10) unsigned NOT NULL DEFAULT '0',
  `todayattachs` smallint(6) unsigned NOT NULL DEFAULT '0',
  `todayattachsize` int(10) unsigned NOT NULL DEFAULT '0',
  `askings` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `answers` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_member_info`;
CREATE TABLE `pc_member_info` (
  `uid` int(10) unsigned NOT NULL,
  `realname` varchar(50) NOT NULL DEFAULT '',
  `idcard` varchar(50) NOT NULL DEFAULT '',
  `company` varchar(200) NOT NULL DEFAULT '',
  `address` varchar(200) NOT NULL DEFAULT '',
  `homepage` varchar(200) NOT NULL DEFAULT '',
  `qq` varchar(100) NOT NULL DEFAULT '',
  `msn` varchar(100) NOT NULL DEFAULT '',
  `taobao` varchar(100) NOT NULL DEFAULT '',
  `zipcode` varchar(50) NOT NULL DEFAULT '',
  `phone` varchar(200) NOT NULL DEFAULT '',
  `mobile` varchar(200) NOT NULL DEFAULT '',
  `fax` varchar(100) NOT NULL DEFAULT '',
  `usersign` varchar(255) NOT NULL DEFAULT '',
  `birthday` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_member_status`;
CREATE TABLE `pc_member_status` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `regip` varchar(40) NOT NULL DEFAULT '',
  `lastip` varchar(40) NOT NULL DEFAULT '',
  `lastvisit` int(10) unsigned NOT NULL DEFAULT '0',
  `lastactivity` int(10) unsigned NOT NULL DEFAULT '0',
  `lastpost` int(10) unsigned NOT NULL DEFAULT '0',
  `attestation` varchar(30) NOT NULL DEFAULT '',
  `groupterms` text NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM;

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

DROP TABLE IF EXISTS `pc_member_validate`;
CREATE TABLE `pc_member_validate` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `submitdate` int(10) unsigned NOT NULL DEFAULT '0',
  `auditdate` int(10) unsigned NOT NULL DEFAULT '0',
  `auditor` varchar(30) NOT NULL DEFAULT '',
  `submitnum` smallint(5) unsigned NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  `remarks` text NOT NULL,
  PRIMARY KEY (`uid`),
  KEY `status` (`status`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_members`;
CREATE TABLE `pc_members` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(60) NOT NULL DEFAULT '',
  `adminid` tinyint(1) NOT NULL DEFAULT '0',
  `special` tinyint(4) NOT NULL DEFAULT '0',
  `groupid` smallint(5) unsigned NOT NULL DEFAULT '11',
  `groupexpiry` int(10) unsigned NOT NULL DEFAULT '0',
  `groupextids` varchar(30) NOT NULL DEFAULT '',
  `face` varchar(255) NOT NULL DEFAULT '',
  `gender` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `emailstatus` tinyint(1) NOT NULL DEFAULT '0',
  `credits` int(11) NOT NULL DEFAULT '0',
  `timeoffset` varchar(5) NOT NULL DEFAULT '8',
  `allowadmin` tinyint(1) NOT NULL DEFAULT '0',
  `salt` varchar(6) NOT NULL DEFAULT '',
  `regdate` int(10) unsigned NOT NULL DEFAULT '0',
  `pmnew` smallint(5) unsigned NOT NULL DEFAULT '0',
  `prompts` smallint(5) unsigned NOT NULL DEFAULT '0',
  `qacode` varchar(8) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `username` (`username`),
  KEY `email` (`email`),
  KEY `groupid` (`groupid`),
  KEY `adminid` (`adminid`,`credits`,`uid`),
  KEY `special` (`special`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `pc_message_body`;
CREATE TABLE `pc_message_body` (
  `pmid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mid` int(10) unsigned NOT NULL DEFAULT '0',
  `authorid` int(10) unsigned NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `delstatus` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pmid`),
  KEY `pmsid` (`mid`,`authorid`,`delstatus`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_messages`;
CREATE TABLE `pc_messages` (
  `mid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `senderid` int(10) unsigned NOT NULL DEFAULT '0',
  `sender` varchar(30) NOT NULL DEFAULT '',
  `subject` varchar(100) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  `delstatus` tinyint(1) NOT NULL DEFAULT '0',
  `pmcount` smallint(5) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`mid`),
  KEY `uid` (`uid`),
  KEY `senderid` (`senderid`),
  KEY `dateline` (`dateline`,`mid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_notification`;
CREATE TABLE `pc_notification` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `flag` tinyint(1) NOT NULL DEFAULT '0',
  `notetype` varchar(20) NOT NULL DEFAULT '',
  `authorid` int(10) unsigned NOT NULL DEFAULT '0',
  `author` varchar(30) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `fromid` int(11) NOT NULL DEFAULT '0',
  `fromnum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`,`flag`,`dateline`),
  KEY `fromid` (`fromid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_onlinetime`;
CREATE TABLE `pc_onlinetime` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `thismonth` smallint(5) unsigned NOT NULL DEFAULT '0',
  `totaltime` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `lastupdate` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_persondata`;
CREATE TABLE `pc_persondata` (
  `personid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  KEY `personid` (`personid`,`tid`,`chanid`),
  KEY `name` (`name`,`tid`,`chanid`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_persons`;
CREATE TABLE `pc_persons` (
  `personid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `num` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`personid`),
  KEY `name` (`name`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_photo_content`;
CREATE TABLE `pc_photo_content` (
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `keyword` varchar(100) NOT NULL DEFAULT '',
  `content` mediumtext NOT NULL,
  `trackback` varchar(255) NOT NULL DEFAULT '',
  `download` varchar(1000) NOT NULL DEFAULT '',
  `tags` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tid`),
  UNIQUE KEY `tid` (`tid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_photo_thread`;
CREATE TABLE `pc_photo_thread` (
  `photoid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `rootid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `subtitle` varchar(80) NOT NULL DEFAULT '',
  `summary` varchar(255) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `person` varchar(100) NOT NULL DEFAULT '',
  `author` varchar(50) NOT NULL DEFAULT '',
  `source` varchar(50) NOT NULL DEFAULT '',
  `demourl` varchar(255) NOT NULL DEFAULT '',
  `editor` varchar(30) NOT NULL DEFAULT '',
  `tableindex` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`photoid`,`tid`),
  UNIQUE KEY `tid` (`tid`),
  KEY `uid` (`uid`),
  KEY `catid` (`catid`,`dateline`),
  KEY `rootid` (`rootid`,`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_polloption`;
CREATE TABLE `pc_polloption` (
  `voteid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `pollid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `voteoption` varchar(80) NOT NULL DEFAULT '',
  `votes` int(10) unsigned NOT NULL DEFAULT '0',
  `icons` varchar(150) NOT NULL DEFAULT '',
  `url` varchar(150) NOT NULL DEFAULT '',
  PRIMARY KEY (`voteid`),
  KEY `pollid` (`pollid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_pollvotes`;
CREATE TABLE `pc_pollvotes` (
  `pollid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `polltitle` varchar(150) NOT NULL DEFAULT '',
  `checkbox` tinyint(1) NOT NULL DEFAULT '0',
  `choices` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `expiration` int(10) unsigned NOT NULL DEFAULT '0',
  `voters` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`pollid`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_post_contents`;
CREATE TABLE `pc_post_contents` (
  `postid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(200) NOT NULL DEFAULT '',
  `content` mediumtext NOT NULL,
  `author` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(50) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `imageurl` varchar(200) NOT NULL DEFAULT '',
  `url` varchar(200) NOT NULL DEFAULT '',
  `topicids` varchar(255) NOT NULL DEFAULT '',
  `extras` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`postid`),
  KEY `chanid` (`chanid`),
  KEY `uid` (`uid`),
  KEY `status` (`status`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_process`;
CREATE TABLE `pc_process` (
  `processid` char(32) NOT NULL DEFAULT '',
  `expiration` int(10) unsigned DEFAULT '0',
  `extra` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`processid`),
  KEY `expiration` (`expiration`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_questionset`;
CREATE TABLE `pc_questionset` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `question` varchar(255) DEFAULT '',
  `answer` varchar(100) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_registerip`;
CREATE TABLE `pc_registerip` (
  `ip` varchar(40) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `counter` smallint(5) unsigned NOT NULL DEFAULT '0',
  KEY `ip` (`ip`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_robots`;
CREATE TABLE `pc_robots` (
  `botid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `ruleid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `botname` varchar(80) NOT NULL DEFAULT '',
  `charset` varchar(30) NOT NULL DEFAULT '',
  `descend` tinyint(1) NOT NULL DEFAULT '0',
  `timeout` mediumint(9) NOT NULL DEFAULT '0',
  `repeated` tinyint(1) NOT NULL DEFAULT '1',
  `auditstatus` tinyint(1) NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `topicids` varchar(255) NOT NULL DEFAULT '',
  `uids` varchar(255) NOT NULL DEFAULT '',
  `pageurl` varchar(255) NOT NULL DEFAULT '',
  `demourl` varchar(255) NOT NULL DEFAULT '',
  `downdir` varchar(255) NOT NULL DEFAULT '',
  `servid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `hitsvote` smallint(6) NOT NULL DEFAULT '0',
  `logenabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`botid`),
  KEY `chanid` (`chanid`),
  KEY `catid` (`catid`),
  KEY `dateline` (`dateline`),
  KEY `ruleid` (`ruleid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_robots_log`;
CREATE TABLE `pc_robots_log` (
  `logid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `botid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `title` varchar(200) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`logid`),
  KEY `botid` (`botid`,`dateline`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_robots_rule`;
CREATE TABLE `pc_robots_rule` (
  `ruleid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rulename` varchar(50) NOT NULL DEFAULT '',
  `listarea` text NOT NULL,
  `listurl` text NOT NULL,
  `thumburl` text NOT NULL,
  `listurladd` varchar(255) NOT NULL DEFAULT '',
  `htmlreplace` text NOT NULL,
  `title` text NOT NULL,
  `titlereplace` text NOT NULL,
  `content` text NOT NULL,
  `contentreplace` text NOT NULL,
  `formatcontent` tinyint(1) NOT NULL DEFAULT '0',
  `paging` tinyint(1) NOT NULL DEFAULT '0',
  `pagingarea` text NOT NULL,
  `pagingurl` text NOT NULL,
  `pagingurladd` varchar(255) NOT NULL DEFAULT '',
  `downimage` tinyint(1) NOT NULL DEFAULT '0',
  `downattach` tinyint(1) NOT NULL DEFAULT '0',
  `downurladd` varchar(255) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `summary` text NOT NULL,
  `keyword` text NOT NULL,
  `tags` text NOT NULL,
  `author` text NOT NULL,
  `source` text NOT NULL,
  `softversion` text NOT NULL,
  `softtype` text NOT NULL,
  `softlang` text NOT NULL,
  `runsystem` text NOT NULL,
  `license` text NOT NULL,
  `softsize` text NOT NULL,
  PRIMARY KEY (`ruleid`),
  KEY `chanid` (`chanid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_searchindex`;
CREATE TABLE `pc_searchindex` (
  `searchid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stype` tinyint(4) NOT NULL DEFAULT '0',
  `keyword` varchar(100) NOT NULL,
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(40) NOT NULL DEFAULT '',
  `tids` text NOT NULL,
  PRIMARY KEY (`searchid`),
  KEY `stype` (`stype`,`keyword`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_searchword`;
CREATE TABLE `pc_searchword` (
  `id` mediumint(5) unsigned NOT NULL AUTO_INCREMENT,
  `word` varchar(50) NOT NULL DEFAULT '',
  `tn` varchar(30) NOT NULL DEFAULT '',
  `highlight` tinyint(1) NOT NULL DEFAULT '0',
  `sortord` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `url` varchar(255) NOT NULL DEFAULT '',
  `target` tinyint(1) NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `word_sortord` (`sortord`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_session`;
CREATE TABLE `pc_session` (
  `sessionid` int(10) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `groupid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `username` varchar(30) NOT NULL DEFAULT '',
  `browser` varchar(150) NOT NULL DEFAULT '',
  `ip` varchar(40) NOT NULL DEFAULT '',
  `lastactivity` int(10) unsigned NOT NULL DEFAULT '0',
  `lastupdated` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`sessionid`),
  UNIQUE KEY `sessionid` (`sessionid`),
  KEY `uid` (`uid`)
) ENGINE=MEMORY;

DROP TABLE IF EXISTS `pc_setting`;
CREATE TABLE `pc_setting` (
  `skey` varchar(50) NOT NULL DEFAULT '',
  `svalue` text,
  `stype` enum('string','array') NOT NULL DEFAULT 'string',
  PRIMARY KEY (`skey`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_soft_content`;
CREATE TABLE `pc_soft_content` (
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `keyword` varchar(100) NOT NULL DEFAULT '',
  `content` mediumtext NOT NULL,
  `tags` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tid`),
  UNIQUE KEY `tid` (`tid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_soft_download`;
CREATE TABLE `pc_soft_download` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `servid` smallint(8) unsigned NOT NULL DEFAULT '0',
  `dname` varchar(150) NOT NULL DEFAULT '',
  `downurl` varchar(1000) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`),
  KEY `servid` (`servid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_soft_test`;
CREATE TABLE `pc_soft_test` (
  `testid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `caption` varchar(50) NOT NULL DEFAULT '',
  `url` varchar(200) NOT NULL DEFAULT '',
  `color` varchar(80) NOT NULL DEFAULT '',
  `icons` varchar(50) NOT NULL DEFAULT '',
  `checked` tinyint(1) NOT NULL DEFAULT '0',
  `sortord` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`testid`),
  KEY `test_sortord` (`sortord`)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS `pc_soft_thread`;
CREATE TABLE `pc_soft_thread` (
  `softid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `rootid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `softname` varchar(100) NOT NULL DEFAULT '',
  `softversion` varchar(50) NOT NULL DEFAULT '',
  `subtitle` varchar(80) NOT NULL DEFAULT '',
  `summary` varchar(255) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `softlang` varchar(30) NOT NULL DEFAULT '',
  `softtype` varchar(30) NOT NULL DEFAULT '',
  `runsystem` varchar(80) NOT NULL DEFAULT '',
  `license` varchar(30) NOT NULL DEFAULT '',
  `company` varchar(50) NOT NULL DEFAULT '',
  `homepage` varchar(100) NOT NULL DEFAULT '',
  `contact` varchar(100) NOT NULL DEFAULT '',
  `release` varchar(20) NOT NULL DEFAULT '',
  `demourl` varchar(255) NOT NULL DEFAULT '',
  `author` varchar(50) NOT NULL DEFAULT '',
  `checksum` varchar(128) NOT NULL DEFAULT '',
  `softsize` int(10) unsigned NOT NULL DEFAULT '0',
  `star` tinyint(3) unsigned NOT NULL DEFAULT '3',
  `softauth` tinyint(3) NOT NULL DEFAULT '0',
  `testsoft` varchar(150) NOT NULL DEFAULT '',
  `editor` varchar(30) NOT NULL DEFAULT '',
  `tableindex` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`softid`,`tid`),
  UNIQUE KEY `tid` (`tid`),
  KEY `uid` (`uid`),
  KEY `catid` (`catid`,`dateline`),
  KEY `rootid` (`rootid`,`dateline`),
  KEY `chanid` (`chanid`,`dateline`),
  KEY `license` (`license`,`dateline`),
  KEY `softtype` (`softtype`,`dateline`)
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

DROP TABLE IF EXISTS `pc_special_content`;
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

DROP TABLE IF EXISTS `pc_special_data`;
CREATE TABLE `pc_special_data` (
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `specid` int(10) unsigned NOT NULL DEFAULT '0',
  `classid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `specid` (`specid`,`dateline`),
  KEY `classid` (`classid`,`dateline`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_special_thread`;
CREATE TABLE `pc_special_thread` (
  `specid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `rootid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `specname` varchar(50) NOT NULL DEFAULT '',
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
  KEY `rootid` (`rootid`,`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_systemcache`;
CREATE TABLE `pc_systemcache` (
  `cachename` varchar(32) NOT NULL DEFAULT '',
  `cachevalue` mediumblob NOT NULL,
  `cachetime` int(10) unsigned NOT NULL DEFAULT '0',
  `cachetype` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`cachename`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_tagdata`;
CREATE TABLE `pc_tagdata` (
  `tagid` int(10) unsigned NOT NULL DEFAULT '0',
  `tagname` varchar(30) NOT NULL DEFAULT '',
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  KEY `tagid` (`tagid`,`chanid`),
  KEY `tagname` (`tagname`,`chanid`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_tags`;
CREATE TABLE `pc_tags` (
  `tagid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tagname` varchar(30) NOT NULL DEFAULT '',
  `tagnum` mediumint(10) unsigned NOT NULL DEFAULT '0',
  `ishot` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tagid`,`tagname`),
  KEY `tagname` (`tagname`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_thread_class`;
CREATE TABLE `pc_thread_class` (
  `classid` mediumint(8) NOT NULL AUTO_INCREMENT,
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(50) NOT NULL DEFAULT '',
  `about` varchar(255) NOT NULL DEFAULT '',
  `icon` varchar(255) NOT NULL DEFAULT '',
  `ordinal` mediumint(9) NOT NULL DEFAULT '0',
  `special` tinyint(1) NOT NULL DEFAULT '0',
  `counts` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`classid`),
  KEY `catid` (`catid`,`ordinal`,`classid`),
  KEY `chanid` (`chanid`,`ordinal`,`classid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_thread_class_data`;
CREATE TABLE `pc_thread_class_data` (
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `classid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  KEY `tid` (`tid`),
  KEY `classid` (`classid`,`dateline`),
  KEY `catid` (`catid`,`classid`,`dateline`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_thread_field`;
CREATE TABLE `pc_thread_field` (
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `isupdate` tinyint(1) NOT NULL DEFAULT '1',
  `voteup` int(10) unsigned NOT NULL DEFAULT '0',
  `votedown` int(10) unsigned NOT NULL DEFAULT '0',
  `voters` int(10) unsigned NOT NULL DEFAULT '0',
  `totalscore` int(10) unsigned NOT NULL DEFAULT '0',
  `credits` int(11) NOT NULL DEFAULT '0',
  `groupids` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`tid`),
  KEY `voteup` (`voteup`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_thread_image`;
CREATE TABLE `pc_thread_image` (
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `attachment` varchar(100) NOT NULL DEFAULT '',
  `remote` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `thumb` tinyint(1) NOT NULL DEFAULT '0',
  `preview` tinyint(1) NOT NULL DEFAULT '0',
  `attachimg` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`tid`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_threads`;
CREATE TABLE `pc_threads` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `rootid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL DEFAULT '',
  `url` varchar(150) NOT NULL DEFAULT '',
  `htmlname` varchar(50) NOT NULL DEFAULT '',
  `highlight` tinyint(1) NOT NULL DEFAULT '0',
  `istop` tinyint(1) NOT NULL DEFAULT '0',
  `topline` tinyint(1) NOT NULL DEFAULT '0',
  `focus` tinyint(1) NOT NULL DEFAULT '0',
  `digest` tinyint(1) NOT NULL DEFAULT '0',
  `polled` tinyint(1) NOT NULL DEFAULT '0',
  `attached` tinyint(1) NOT NULL DEFAULT '0',
  `image` tinyint(1) NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `weekcount` int(11) NOT NULL DEFAULT '0',
  `monthcount` int(11) NOT NULL DEFAULT '0',
  `lastweek` int(11) NOT NULL DEFAULT '0',
  `lastmonth` int(11) NOT NULL DEFAULT '0',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `lastdate` int(10) unsigned NOT NULL DEFAULT '0',
  `tableindex` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `bancomment` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `comments` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`),
  KEY `hits` (`hits`,`dateline`),
  KEY `dateline` (`dateline`),
  KEY `image` (`image`),
  KEY `topline` (`topline`),
  KEY `focus` (`focus`),
  KEY `uid` (`uid`),
  KEY `chanid` (`status`,`chanid`,`dateline`),
  KEY `catid` (`status`,`catid`,`dateline`),
  KEY `rootid` (`status`,`rootid`,`dateline`),
  KEY `lastdate` (`lastdate`),
  KEY `digest` (`digest`),
  KEY `weekcount` (`weekcount`),
  KEY `monthcount` (`monthcount`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_ucapps`;
CREATE TABLE `pc_ucapps` (
  `appid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `app_type` varchar(20) NOT NULL DEFAULT '',
  `app_name` varchar(20) NOT NULL DEFAULT '',
  `app_url` varchar(255) NOT NULL DEFAULT '',
  `authkey` varchar(255) NOT NULL DEFAULT '',
  `app_ip` varchar(40) NOT NULL DEFAULT '',
  `api_file` varchar(30) NOT NULL DEFAULT '',
  `synlogin` tinyint(1) NOT NULL DEFAULT '0',
  `syncredits` tinyint(1) NOT NULL DEFAULT '0',
  `extras` text,
  PRIMARY KEY (`appid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_upload_temp`;
CREATE TABLE `pc_upload_temp` (
  `tmpid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `dirname` varchar(50) NOT NULL DEFAULT '',
  `filename` varchar(100) NOT NULL,
  `filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `description` varchar(200) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `image` tinyint(1) NOT NULL DEFAULT '0',
  `thumb` tinyint(1) NOT NULL DEFAULT '0',
  `preview` tinyint(1) NOT NULL DEFAULT '0',
  `remote` tinyint(1) NOT NULL DEFAULT '0',
  `width` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tmpid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_urlrules`;
CREATE TABLE `pc_urlrules` (
  `ruleid` smallint(5) unsigned NOT NULL,
  `modules` varchar(30) NOT NULL DEFAULT '',
  `rulename` varchar(100) NOT NULL DEFAULT '',
  `matchurl` varchar(100) NOT NULL DEFAULT '',
  `actionurl` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(150) NOT NULL DEFAULT '',
  `staticize` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ruleid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_usergroup`;
CREATE TABLE `pc_usergroup` (
  `groupid` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `adminrid` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `type` enum('default','member','special','system') NOT NULL DEFAULT 'member',
  `grouptitle` varchar(50) NOT NULL DEFAULT '',
  `usertitle` varchar(50) NOT NULL DEFAULT '',
  `mincredits` int(10) NOT NULL DEFAULT '0',
  `maxcredits` int(10) NOT NULL DEFAULT '0',
  `stars` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `color` varchar(30) NOT NULL DEFAULT '',
  `buyable` tinyint(1) NOT NULL DEFAULT '0',
  `price` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `mindays` smallint(5) unsigned NOT NULL DEFAULT '0',
  `setting` text NOT NULL,
  PRIMARY KEY (`groupid`),
  KEY `pc_type` (`type`),
  KEY `pc_credits` (`mincredits`,`maxcredits`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_userorder`;
CREATE TABLE `pc_userorder` (
  `orderid` varchar(32) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(20) NOT NULL DEFAULT '',
  `buyer` varchar(30) NOT NULL DEFAULT '',
  `admin` varchar(30) NOT NULL DEFAULT '',
  `payapi` varchar(10) NOT NULL DEFAULT '',
  `tradeno` varchar(32) NOT NULL DEFAULT '',
  `amount` int(10) unsigned NOT NULL DEFAULT '1',
  `price` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `email` varchar(50) NOT NULL DEFAULT '',
  `ordertime` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`orderid`),
  UNIQUE KEY `orderid` (`orderid`),
  KEY `ordertime` (`ordertime`),
  KEY `uid_ordertime` (`uid`,`ordertime`),
  KEY `status_time` (`status`,`ordertime`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_video_address`;
CREATE TABLE `pc_video_address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `playerid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `caption` varchar(150) NOT NULL DEFAULT '',
  `address` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`),
  KEY `playerid` (`playerid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_video_content`;
CREATE TABLE `pc_video_content` (
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `keyword` varchar(100) NOT NULL DEFAULT '',
  `content` mediumtext NOT NULL,
  `tags` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`tid`),
  UNIQUE KEY `tid` (`tid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_video_player`;
CREATE TABLE `pc_video_player` (
  `playerid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(30) NOT NULL DEFAULT '',
  `caption` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`playerid`),
  KEY `playerid` (`playerid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pc_video_thread`;
CREATE TABLE `pc_video_thread` (
  `videoid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `chanid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `rootid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `catid` smallint(5) unsigned NOT NULL DEFAULT '0',
  `subtitle` varchar(80) NOT NULL DEFAULT '',
  `summary` varchar(255) NOT NULL DEFAULT '',
  `dateline` int(10) unsigned NOT NULL DEFAULT '0',
  `director` varchar(50) NOT NULL DEFAULT '',
  `starring` varchar(255) NOT NULL DEFAULT '',
  `years` smallint(5) unsigned NOT NULL DEFAULT '0',
  `release` varchar(50) NOT NULL DEFAULT '',
  `country` varchar(30) NOT NULL DEFAULT '',
  `dialogue` varchar(50) NOT NULL DEFAULT '',
  `version` varchar(50) NOT NULL DEFAULT '',
  `demourl` varchar(255) NOT NULL DEFAULT '',
  `quality` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `mins` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `aid` int(10) unsigned NOT NULL DEFAULT '0',
  `author` varchar(50) NOT NULL DEFAULT '',
  `editor` varchar(30) NOT NULL DEFAULT '',
  `tableindex` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`videoid`,`tid`),
  UNIQUE KEY `tid` (`tid`),
  KEY `uid` (`uid`),
  KEY `catid` (`catid`,`dateline`),
  KEY `rootid` (`rootid`,`dateline`),
  KEY `chanid` (`chanid`,`dateline`),
  KEY `years` (`years`,`dateline`)
) ENGINE=MyISAM;
