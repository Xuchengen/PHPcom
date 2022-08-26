<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : robots.php  2012-11-15
 */
!defined('IN_ADMINCP') && exit('Access denied');

phpcom::$G['lang']['admin'] = 'misc';
$chanid = isset(phpcom::$G['gp_chanid']) ? intval(phpcom::$G['gp_chanid']) : 0;
admin_header('menu_robots');
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('topic');
$navarray = array(
		array('title' => 'menu_robots', 'url' => "?m=robots&chanid=$chanid", 'id' => 'robots'),
		array('title' => 'robots_add', 'url' => "?m=robots&action=add&chanid=$chanid", 'id' => 'robots_add'),
		array('title' => 'menu_robots_rule', 'url' => "?m=robots&action=rule&chanid=$chanid", 'id' => 'robots_rule'),
		array('title' => 'menu_robots_clearcache', 'url' => "?m=robots&action=clearcache&chanid=$chanid", 'id' => 'robots_clearcache')
);
$adminhtml->navtabs($navarray, in_array($action, array('add', 'rule', 'clearcache')) ? "robots_$action" : 'robots');
if ($action == 'start') {
	@ini_set('max_execution_time', 9000);
	$botid = isset(phpcom::$G['gp_botid']) ? intval(phpcom::$G['gp_botid']) : 0;
	if(!robots_cache_set($botid)){
		admin_message('undefined_action');
	}
	$robots = robots_cache_get($botid);
	$channelid = $robots['chanid'];
	if(!isset(phpcom::$G['channel'][$channelid]['modules'])){
		admin_message('robots_channel_invalid');
	}
	if(empty($robots['catid'])){
		admin_message('robots_category_invalid');
	}
	DB::update('robots', array('dateline' => time()), "botid='$botid'");
	$pageurls = robots_get_pageurls($robots);
	robots_redirect("m=robots&action=ok&chanid=$chanid&botid=$botid&pn=0");
}elseif($action == 'ok'){
	@ini_set('max_execution_time', 5000);
	$botid = isset(phpcom::$G['gp_botid']) ? intval(phpcom::$G['gp_botid']) : 0;
	$pn = isset(phpcom::$G['gp_pn']) ? intval(phpcom::$G['gp_pn']) : 0;
	if(!($robots = robots_cache_get($botid))){
		admin_message('robots_get_cache_invalid', "m=robots&chanid=$chanid");
	}
	if(empty($robots['catid'])){
		admin_message('robots_category_invalid', "m=robots&chanid=$chanid");
	}
	if(!($pageurls = urls_cache_get($botid))){
		admin_message('robots_get_pageurls_invalid', "m=robots&chanid=$chanid");
	}
	$channelid = $robots['chanid'];
	if(!isset(phpcom::$G['channel'][$channelid]['modules'])){
		admin_message('robots_channel_invalid', "m=robots&chanid=$chanid");
	}
	$channel_module = phpcom::$G['channel'][$channelid]['modules'];
	if(empty($pageurls[$pn])){
		$url = $pn ? $pageurls[$pn-1] : $pageurls[0];
		$adminhtml->table_header('robots_complete');
		$adminhtml->table_td(array(
				array("robots_complete_message", array('chanid' => $chanid), 'align="center"')
		));
		$adminhtml->table_td(array(
				array("robots_url_message", array('url' => $url), 'align="center"')
		));
		$adminhtml->table_end();
	}else{
		$downloaded = false;
		$url = trim($pageurls[$pn]);
		$pn++;
		$adminhtml->table_header('robots_in_progress');
		$adminhtml->table_td(array(
				array("robots_url_subject", FALSE, 'width="10%"', '', TRUE),
				array("robots_url_message", array('url' => $url))
		));
		$adminhtml->table_td(array(
				array("robots_progress_message", array('url' => "?m=robots&action=ok&chanid=$chanid&botid=$botid&pn=$pn"), 'colspan="2" align="center"')
		));
		$roboot_erron = 0;
		$errurl = "?m=robots&action=edit&chanid=$chanid&botid=$botid&step=3";
		if($html = HttpSpider::getContents($url, $robots['charset'])){
			$html = HttpSpider::replace($html, $robots['htmlreplace']);
			if($title = HttpSpider::substring($robots['title'], $html, true)){
				$title = HttpSpider::replace($title, $robots["titlereplace"]);
			}else{
				$title = adminlang('robots_rule_title_error', array('url' => $errurl));
				$roboot_erron = 1;
				$robots['timeout'] = 1000;
			}
			$adminhtml->table_td(array(
					array("robots_rule_title", FALSE, '', '', TRUE),
					array($title, TRUE)
			));
			if($content = HttpSpider::substring($robots['content'], $html, true)){
				$content = HttpSpider::replace($content, $robots["contentreplace"]);
				$content = HttpSpider::parseImageUrl($content, $url, true);
				$content = trim(HttpSpider::htmlToCode($content));
			}else{
				$content = adminlang('robots_rule_content_error', array('url' => $errurl));
				$roboot_erron = 2;
				$robots['timeout'] = 1000;
				$adminhtml->table_td(array(
						array("robots_rule_content", FALSE, '', '', TRUE),
						array(str_replace('[pagebreak]', '<div class="pagebreak"></div>', $content), TRUE)
				));
			}
			if(($pagingurls = paging_get_urls($html, $robots, $url))){
				if($channel_module == 'article'){
					$content .= paging_get_contents($pagingurls, $robots, $url);
				}else{
					$downloaded = true;
				}
			}
			if($channel_module == 'soft' && $robots['paging'] && $pagingurls === false){
				$downloaded = true;
				$pagingurls[0] = $url;
			}
			if(empty($roboot_erron)){
				$threads = array('hits' => 0, 'bancomment' => 0, 'digest' => 0, 'status' => 1);
				$fields = array('summary' => 'summary');
				if($channel_module == 'soft'){
					$fields['softversion'] = 'softversion';
					$fields['softtype'] = 'softtype';
					$fields['softlang'] = 'softlang';
					$fields['runsystem'] = 'runsystem';
					$fields['license'] = 'license';
					$fields['softsize'] = 'softsize';
				}else{
					$fields['author'] = 'author';
					$fields['source'] = 'source';
					$threads['title'] = trim($title);
				}
				
				$subjects = parsehtmlcontents($html, $fields, $robots, $url);
				foreach ($subjects as $key => $value){
					$adminhtml->table_td(array(
							array("robots_rule_$key", FALSE, '', '', TRUE),
							array($value, TRUE)
					));
				}
				$threadFields = array('voteup' => 0, 'votedown' => 0, 'groupids' => '');
				$hitsvote = intval($robots['hitsvote']);
				if($hitsvote > 5){
					$threads['hits'] = $hitsvote;
				}elseif($hitsvote > 1 && $hitsvote <= 5){
					$max = str_pad('99', $hitsvote, '9', STR_PAD_LEFT);
					$num = mt_rand(10, $max);
					$voteup = round($num * 0.8);
					$votedown = round($num * floatval('0.1' . substr($num, -1)));
					$threads['hits'] = $num;
					$threadFields['voteup'] = $voteup;
					$threadFields['votedown'] = $votedown;
				}
				$threads['catid'] = intval($robots['catid']);
				$threads['status'] = intval($robots['auditstatus']);
				
				if($channel_module == 'soft' && isset($subjects['softversion'])){
					$subjects['softname'] = trim($title);
					$subjects['testsoft'] = get_testsoft_default();
					$subjects['star'] = 3;
					$threads['title'] = trim($title . ' ' . trim($subjects['softversion']));
				}
				$messages = array('content' => $content, 'keyword' => '', 'tags' => '');
				if(!empty($robots['keyword'])){
					$messages['keyword'] = trim(HttpSpider::substring($robots['keyword'], $html, true));
				}
				if(!empty($robots['tags'])){
					if($tags = trim(HttpSpider::substring($robots['tags'], $html, true))){
						$tags = str_replace(array('&nbsp;', '[', ']'), ' ', $tags);
						$messages['tags'] = $tags;
					}
				}
				
				$post = new DataAccess_PostThread($channelid);
				$thread = array();
				
				$threads = addslashes_array($threads);
				$subjects = addslashes_array($subjects);
				$messages = addslashes_array($messages);
				$title = addslashes($title);
				if(empty($robots['repeated']) && DB::fetch_first("SELECT tid FROM " . DB::table('threads') . " WHERE chanid='$channelid' AND title='$title' LIMIT 1")){
					$adminhtml->table_td(array(
							array("robots_storage_repeated", FALSE, 'colspan="2" align="center"')
					));
				}else{
					if($tid = $post->insert($thread, $threads, $threadFields, $subjects, $messages)){
						$adminhtml->table_td(array(
								array("robots_storage_complete", FALSE, 'colspan="2" align="center"')
						));
						if(isset($robots['downimage']) && $robots['downimage']){
							$post->downloadContentImage($content, $tid, $thread['tableindex']);
						}
						if($downloaded && $pagingurls){
							$downurl = trim($pagingurls[0]);
							if(isset($robots['downattach']) && $robots['downattach']){
								if(!strpos($downurl, '?') && !stripos($downurl, '.htm')){
									$downurl = basename($downurl);
								}
							}
							DB::insert('soft_download', array(
							'tid' => $tid,
							'servid' => intval($robots['servid']),
							'dname' => trim(adminlang('robots_download_name')),
							'downurl' => $downurl
							));
						}
					}else{
						$adminhtml->table_td(array(
								array("robots_storage_warning", FALSE, 'colspan="2" align="center"')
						));
					}
				}
			}
		}
		$adminhtml->table_end();
		robots_redirect("m=robots&action=ok&chanid=$chanid&botid=$botid&pn=$pn", $robots['timeout']);
	}
	
}elseif($action == 'clearcache'){
	robots_cache_clear();
	admin_succeed('robots_cache_clear_succeed', "m=robots&chanid=$chanid");
}elseif($action == 'add' || $action == 'edit'){
	include loadlibfile('adminthread');
	$botid = isset(phpcom::$G['gp_botid']) ? intval(phpcom::$G['gp_botid']) : 0;
	$step = isset(phpcom::$G['gp_step']) ? intval(phpcom::$G['gp_step']) : 0;
	$ruleid = isset(phpcom::$G['gp_ruleid']) ? intval(phpcom::$G['gp_ruleid']) : 0;
	$adminhtml->tablesetmode = false;
	$robots = array('botid' => 0, 'chanid' => $chanid, 'catid' => 0, 'ruleid' => $ruleid, 'botname' => '', 'descend' => 0,
			'charset' => '', 'timeout' => 500, 'auditstatus' => 1,'topicids' => '', 'uids' => '', 'pageurl' => '',
			'demourl' => '', 'downdir' => '', 'servid' => 0, 'hitsvote' => 0, 'logenabled' => 0, 'repeated' => 1);
	if($botid && !($robots = DB::fetch_first("SELECT * FROM " . DB::table('robots') . " WHERE botid='$botid'"))){
		admin_message('undefined_action');
	}
	$botid = $robots['botid'];
	$channelid = $robots['chanid'];
	$showstepmenu = $adminhtml->edit_word('robots_setting_step_1', "m=robots&action=edit&chanid=$chanid&botid=$botid&step=1", ' | ');
	$showstepmenu .= $adminhtml->edit_word('robots_setting_step_2', "m=robots&action=edit&chanid=$chanid&botid=$botid&step=2", ' | ');
	$showstepmenu .= $adminhtml->edit_word('robots_setting_step_3', "m=robots&action=edit&chanid=$chanid&botid=$botid&step=3", ' | ');
	$showstepmenu .= $adminhtml->edit_word('robots_setting_step_4', "m=robots&action=edit&chanid=$chanid&botid=$botid&step=4&demo=1", ' | ');
	$showstepmenu .= $adminhtml->edit_word('robots_start', "m=robots&action=start&chanid=$chanid&botid=$botid");
	$channel_module = isset(phpcom::$G['channel'][$channelid]['modules']) ? phpcom::$G['channel'][$channelid]['modules'] : 'article';
	if($step == 2){
		if (checksubmit(array('submit', 'btnsubmit'))) {
			$robotdata = phpcom::$G['gp_robots'];
			if(empty($robotdata['botname'])){
				admin_message('robots_botname_invalid');
			}
			if(empty($robotdata['pageurl'])){
				admin_message('robots_pageurl_invalid');
			}
			if($action == 'edit' && $botid){
				if(isset($robotdata['chanid'])){
					unset($robotdata['chanid']);
				}
				DB::update('robots', $robotdata, "botid='$botid'");
			}else{
				if(empty($robotdata['chanid'])){
					$robotdata['chanid'] = $chanid;
				}
				if(empty($chanid) && empty($robotdata['chanid'])){
					admin_message('robots_chanid_invalid');
				}
				$robotdata['dateline'] = TIMESTAMP;
				$botid = DB::insert('robots', $robotdata, true);
			}
			$robots = DB::fetch_first("SELECT * FROM " . DB::table('robots') . " WHERE botid='$botid'");
			$botid = $robots['botid'];
			$channelid = $robots['chanid'];
			$channel_module = isset(phpcom::$G['channel'][$channelid]['modules']) ? phpcom::$G['channel'][$channelid]['modules'] : 'article';
		}
		$ruleid = $robots['ruleid'];
		if($robotsrule = DB::fetch_first("SELECT * FROM " . DB::table('robots_rule') . " WHERE ruleid='$ruleid'")){
			
		}else{
			$ruleid = 0;
			$robotsrule = array('ruleid' => 0, 'chanid' => $robots['chanid'], 'rulename' => '', 'listarea' => '',
					'listurl' => '', 'thumburl' => '', 'listurladd' => '');
		}
		$adminhtml->form("m=robots&action=$action&botid=$botid&ruleid=$ruleid&step=3&chanid=$chanid",
				array(array('robotsrule[chanid]', $robotsrule['chanid'])));
		$adminhtml->table_header('robots_setting_step_2');
		$adminhtml->table_td(array(
				array('&nbsp;', TRUE),
				array($showstepmenu, TRUE, 'colspan="2"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->vars = array('botid' => $botid, 'chanid' => $chanid);
		$adminhtml->table_setting('robots_rule_name', 'robotsrule[rulename]', trim($robotsrule['rulename']), 'text');
		$adminhtml->table_setting('robots_rule_listarea', 'robotsrule[listarea]', $robotsrule['listarea'], 'textwrap');
		$adminhtml->table_setting('robots_rule_listurl', 'robotsrule[listurl]', $robotsrule['listurl'], 'textwrap');
		$adminhtml->table_setting('robots_rule_thumburl', 'robotsrule[thumburl]', $robotsrule['thumburl'], 'textwrap');
		$adminhtml->table_setting('robots_rule_listurladd', 'robotsrule[listurladd]', trim($robotsrule['listurladd']), 'text');
		$adminhtml->table_setting('robots_rule_test', 'demo', 0, 'radio');
		$adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
		$adminhtml->table_end('</form>');
	}elseif($step == 3){
		$botdemo = isset(phpcom::$G['gp_demo']) ? intval(phpcom::$G['gp_demo']) : 0;
		$ruleid = isset(phpcom::$G['gp_ruleid']) ? intval(phpcom::$G['gp_ruleid']) : 0;
		if (checksubmit(array('submit', 'btnsubmit'))) {
			$rulesdata = trimchars(phpcom::$G['gp_robotsrule'], "\r\n");
			if(empty($rulesdata['rulename'])){
				admin_message('robots_rule_name_invalid');
			}
			if(empty($rulesdata['listarea'])){
				admin_message('robots_rule_listarea_invalid');
			}
			if(empty($rulesdata['listurl'])){
				admin_message('robots_rule_listurl_invalid');
			}
			if($ruleid){
				if(isset($rulesdata['chanid'])){
					unset($rulesdata['chanid']);
				}
				DB::update('robots_rule', $rulesdata, "ruleid='$ruleid'");
			}else{
				$rulesdata['formatcontent'] = 1;
				$rulesdata['paging'] = 0;
				$rulesdata['downimage'] = 1;
				$rulesdata['downattach'] = 0;
				$rulesdata['dateline'] = TIMESTAMP;
				$fieldArray = array('htmlreplace', 'titlereplace', 'title', 'content', 'contentreplace',
					'pagingarea', 'pagingurl', 'pagingurladd', 'downurladd', 'summary', 'keyword', 'tags',
					'author', 'source', 'softversion', 'softtype', 'softlang', 'runsystem', 'license', 'softsize'
				);
				foreach ($fieldArray as $key){
					$rulesdata[$key] = '';
				}
				$ruleid = DB::insert('robots_rule', $rulesdata, true);
				DB::update('robots', array('ruleid' => $ruleid), "botid='$botid'");
				$robots['ruleid'] = $ruleid;
			}
		}
		
		$ruleid = $robots['ruleid'];
		if(!($robotsrule = DB::fetch_first("SELECT * FROM " . DB::table('robots_rule') . " WHERE ruleid='$ruleid'"))){
			$ruleid = 0;
			$robotsrule = array('ruleid' => 0, 'chanid' => $robots['chanid'], 'rulename' => '', 'listarea' => '', 'listurl' => '', 'listurladd' => '',
					'htmlreplace' => '', 'title' => '', 'titlereplace' => '', 'content' => '', 'contentreplace' => '', 'formatcontent' => 1,
					'paging' => 0, 'pagingarea' => '', 'pagingurl' => '', 'pagingurladd' => '', 'downimage' => 1, 'downattach' => 0,
					'summary' => '', 'keyword' => '', 'tagsarea' => '', 'tags' => '', 'author' => '', 'source' => '', 'softversion' => '',
					'softtype' => '', 'softlang' => '', 'runsystem' => '', 'license' => '', 'softsize' => '');
		}
		$adminhtml->form("m=robots&action=$action&botid=$botid&ruleid=$ruleid&step=4&chanid=$chanid");
		$adminhtml->table_header('robots_setting_step_3');
		$adminhtml->table_td(array(
				array('&nbsp;', TRUE),
				array($showstepmenu, TRUE, 'colspan="2"')
		), NULL, FALSE, NULL, NULL, FALSE);
		if($botdemo && $ruleid && $robotsrule['listurl']){
			$showurl = adminlang('robots_listurl_error');
			if($urls = getpageurls($robots, $robotsrule, false)){
				$showurl = implode("\n", $urls);
			}
			$adminhtml->table_setting('robots_rule_test', 'test_wrap', $showurl, 'textwrap');
		}
		
		$adminhtml->table_setting('robots_rule_htmlreplace', 'robotsrule[htmlreplace]', $robotsrule['htmlreplace'], 'textwrap');
		$adminhtml->table_setting('robots_rule_title', 'robotsrule[title]', $robotsrule['title'], 'textwrap');
		$adminhtml->table_setting('robots_rule_titlereplace', 'robotsrule[titlereplace]', $robotsrule['titlereplace'], 'textwrap');
		$adminhtml->table_setting('robots_rule_content', 'robotsrule[content]', $robotsrule['content'], 'textwrap');
		$adminhtml->table_setting('robots_rule_contentreplace', 'robotsrule[contentreplace]', $robotsrule['contentreplace'], 'textwrap');
		$adminhtml->table_setting('robots_rule_formatcontent', 'robotsrule[formatcontent]', intval($robotsrule['formatcontent']), 'radio');
		$adminhtml->table_end();
		$adminhtml->table_header('robots_setting_other');
		$adminhtml->table_setting('robots_rule_summary', 'robotsrule[summary]', $robotsrule['summary'], 'textwrap');
		$adminhtml->table_setting('robots_rule_keyword', 'robotsrule[keyword]', $robotsrule['keyword'], 'textwrap');
		$adminhtml->table_setting('robots_rule_tags', 'robotsrule[tags]', $robotsrule['tags'], 'textwrap');
		$adminhtml->table_end();
		if($channel_module == 'soft'){
			$adminhtml->table_header('robots_setting_paging_soft');
			$adminhtml->table_setting('robots_rule_paging_soft', 'robotsrule[paging]', intval($robotsrule['paging']), 'radio');
			$adminhtml->table_setting('robots_rule_pagingarea_soft', 'robotsrule[pagingarea]', $robotsrule['pagingarea'], 'textwrap');
		}else{
			$adminhtml->table_header('robots_setting_paging');
			$adminhtml->table_setting('robots_rule_paging', 'robotsrule[paging]', intval($robotsrule['paging']), 'radio');
			$adminhtml->table_setting('robots_rule_pagingarea', 'robotsrule[pagingarea]', $robotsrule['pagingarea'], 'textwrap');
		}
		$adminhtml->table_setting('robots_rule_pagingurl', 'robotsrule[pagingurl]', $robotsrule['pagingurl'], 'textwrap');
		$adminhtml->table_setting('robots_rule_pagingurladd', 'robotsrule[pagingurladd]', trim($robotsrule['pagingurladd']), 'text');
		$adminhtml->table_end();
		if($channel_module == 'soft'){
			$adminhtml->table_header('robots_setting_soft');
			$adminhtml->table_setting('robots_rule_softversion', 'robotsrule[softversion]', $robotsrule['softversion'], 'textwrap');
			$adminhtml->table_setting('robots_rule_softtype', 'robotsrule[softtype]', $robotsrule['softtype'], 'textwrap');
			$adminhtml->table_setting('robots_rule_softlang', 'robotsrule[softlang]', $robotsrule['softlang'], 'textwrap');
			$adminhtml->table_setting('robots_rule_runsystem', 'robotsrule[runsystem]', $robotsrule['runsystem'], 'textwrap');
			$adminhtml->table_setting('robots_rule_license', 'robotsrule[license]', $robotsrule['license'], 'textwrap');
			$adminhtml->table_setting('robots_rule_softsize', 'robotsrule[softsize]', $robotsrule['softsize'], 'textwrap');
			$adminhtml->table_end();
		}else{
			$adminhtml->table_header('robots_setting_article');
			$adminhtml->table_setting('robots_rule_author', 'robotsrule[author]', $robotsrule['author'], 'textwrap');
			$adminhtml->table_setting('robots_rule_source', 'robotsrule[source]', $robotsrule['source'], 'textwrap');
			$adminhtml->table_end();
		}
		$adminhtml->table_header('robots_setting_option');
		$adminhtml->table_setting('robots_rule_downimage', 'robotsrule[downimage]', intval($robotsrule['downimage']), 'radio');
		$adminhtml->table_setting('robots_rule_downattach', 'robotsrule[downattach]', intval($robotsrule['downattach']), 'radio');
		$adminhtml->table_setting('robots_rule_test', 'demo', 0, 'radio');
		$adminhtml->table_setting('robots_demourl', 'robots[demourl]', trim($robots['demourl']), 'text');
		$adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
		$adminhtml->table_end('</form>');
	}elseif($step == 4){
		$botdemo = isset(phpcom::$G['gp_demo']) ? intval(phpcom::$G['gp_demo']) : 0;
		$ruleid = $robots['ruleid'];
		if (checksubmit(array('submit', 'btnsubmit'))) {
			if(!($robotsrule = DB::fetch_first("SELECT * FROM " . DB::table('robots_rule') . " WHERE ruleid='$ruleid'"))){
				$ruleid = 0;
			}
			$rulesdata = trimchars(phpcom::$G['gp_robotsrule'], "\r\n");
			$robotdata = trimchars(phpcom::$G['gp_robots'], "\r\n");
			if($ruleid){
				if(isset($rulesdata['chanid'])){
					unset($rulesdata['chanid']);
				}
				DB::update('robots_rule', $rulesdata, "ruleid='$ruleid'");
			}else{
				$ruleid = DB::insert('robots_rule', $rulesdata, true);
				DB::update('robots', array('ruleid' => $ruleid), "botid='$botid'");
			}
			$robots['demourl'] = $robotdata['demourl'] = trim($robotdata['demourl']);
			DB::update('robots', $robotdata, "botid='$botid'");
			if(empty($botdemo)){
				admin_succeed('robots_rule_succeed', "m=robots&chanid=$chanid");
			}
		}
		if(empty($botdemo)){
			admin_message('undefined_action');
		}
		$downloaded = false;
		$robotsrule = DB::fetch_first("SELECT * FROM " . DB::table('robots_rule') . " WHERE ruleid='$ruleid'");
		if($botdemo && $ruleid && !empty($robotsrule['listurl'])){
			if($urls = getpageurls($robots, $robotsrule, false)){
				$url = empty($robots['demourl']) ? $urls[0] : trim($robots['demourl']);
				$adminhtml->table_header('robots_setting_step_4');
				$adminhtml->table_td(array(
						array('&nbsp;', TRUE),
						array($showstepmenu, TRUE, 'colspan="2"')
				), NULL, FALSE, NULL, NULL, FALSE);
				$adminhtml->table_td(array(
						array("robots_target_url", FALSE, 'width="10%" noWrap="noWrap"', '', TRUE),
						array('<a href="javascript:void(0)" onclick="window.open(\''.$url.'\')">'.$url.'</a>', TRUE)
				));
				$errurl = "?m=robots&action=edit&chanid=$chanid&botid=$botid&step=3";
				if($html = HttpSpider::getContents($url, $robots['charset'])){
					$html = HttpSpider::replace($html, $robotsrule['htmlreplace']);
					if($title = HttpSpider::substring($robotsrule['title'], $html, true)){
						$title = HttpSpider::replace($title, $robotsrule["titlereplace"]);
					}else{
						$title = adminlang('robots_rule_title_error', array('url' => $errurl));
					}
					$adminhtml->table_td(array(
							array("robots_rule_title", FALSE, '', '', TRUE),
							array($title, TRUE)
					));
					if($contents = HttpSpider::substring($robotsrule['content'], $html, true)){
						$contents = HttpSpider::replace($contents, $robotsrule["contentreplace"]);
						$contents = trim(HttpSpider::parseImageUrl($contents, $url, false));
						$contents = HttpSpider::htmlToCode($contents, false);
					}else{
						$contents = adminlang('robots_rule_content_error', array('url' => $errurl));
					}
					$robotsrule['charset'] = $robots['charset'];
					if(($pagingurls = paging_get_urls($html, $robotsrule, $url))){
						if($channel_module == 'article'){
							$contents .= paging_get_contents($pagingurls, $robotsrule, $url, false);
						}else{
							$downloaded = true;
						}
					}
					if($channel_module == 'soft' && $robotsrule['paging']){
						if($pagingurls === false) $pagingurls[0] = $url;
						$download = '<ol style="padding-left:20px">';
						if($pagingurls){
							foreach($pagingurls as $downurl){
								$download .= "<li><a href=\"$downurl\">$downurl</a></li>";
							}
						}
						$download .= '</ol>';
						$adminhtml->table_td(array(
								array("robots_download", FALSE, '', '', TRUE),
								array($download, TRUE)
						));
					}
					$adminhtml->table_td(array(
							array("robots_rule_content", FALSE, '', '', TRUE),
							array(str_replace('[pagebreak]', '<div class="pagebreak"></div>', $contents), TRUE)
					));
					$fields = array('summary' => 'summary', 'keyword' => 'keyword', 'tags' => 'tags');
					if($channel_module == 'soft'){
						$fields['softversion'] = 'softversion';
						$fields['softtype'] = 'softtype';
						$fields['softlang'] = 'softlang';
						$fields['runsystem'] = 'runsystem';
						$fields['license'] = 'license';
						$fields['softsize'] = 'softsize';
					}else{
						$fields['author'] = 'author';
						$fields['source'] = 'source';
					}
					
					$contents = parsehtmlcontents($html, $fields, $robotsrule, $url);
					foreach ($contents as $key => $value){
						$adminhtml->table_td(array(
								array("robots_rule_$key", FALSE, '', '', TRUE),
								array($value, TRUE)
						));
					}
				}
				$adminhtml->table_end();
			}else{
				admin_message('robots_rule_listurl_error');
			}
		}else{
			admin_message('robots_rule_listurl_error');
		}
	}else{
		if (!checksubmit(array('submit', 'btnsubmit'))) {
			$adminhtml->form("m=robots&action=$action&botid=$botid&step=2&chanid=$chanid");
			$adminhtml->table_header('robots_setting_basic');
			$adminhtml->table_td(array(
					array('&nbsp;', TRUE),
					array($showstepmenu, TRUE, 'colspan="2"')
			), NULL, FALSE, NULL, NULL, FALSE);
			$adminhtml->table_setting('robots_botname', 'robots[botname]', trim($robots['botname']), 'text');
			$adminhtml->table_setting('robots_pageurl', 'robots[pageurl]', trim($robots['pageurl']), 'text');
			$adminhtml->table_setting('robots_descend', 'robots[descend]', intval($robots['descend']), 'radio');
			$adminhtml->table_setting('robots_timeout', 'robots[timeout]', intval($robots['timeout']), 'text');
			$adminhtml->table_setting('robots_charset', 'robots[charset]', trim($robots['charset']), 'text');
			$adminhtml->table_setting('robots_repeated', 'robots[repeated]', intval($robots['repeated']), 'radio');
			$select_array = array();
			if($chanid < 1 && $robots['chanid'] < 1){
				$sql = "SELECT channelid, channelname FROM " . DB::table('channel') . " WHERE modules IN('article','soft') ORDER BY sortord";
				$query = DB::query($sql);
				while ($row = DB::fetch_array($query)) {
					$select_array[$row['channelid']] = trim($row['channelname']);
				}
				$adminhtml->table_setting('robots_chanid', 'robots[chanid]', intval($robots['chanid']), 'select', '', $select_array);
			}else{
				$chanid = $robots['chanid'];
				$ruleid = $robots['ruleid'];
				$selectcategory = '<select name="robots[catid]" class="select t60">';
				$selectcategory .= select_category($robots['chanid'], intval($robots['catid']));
				$selectcategory .= '</select>';
				$adminhtml->table_setting('robots_catid', '', $selectcategory, 'value');
				$select_array[0] = adminlang('robots_rule_new');
				$query = DB::query("SELECT ruleid, rulename FROM " . DB::table('robots_rule') . " WHERE chanid='$chanid'");
				while ($row = DB::fetch_array($query)) {
					$select_array[$row['ruleid']] = trim($row['rulename']);
				}
				$adminhtml->table_setting('robots_ruleid', 'robots[ruleid]', intval($robots['ruleid']), 'select', '', $select_array);
			}
			if($channel_module == 'soft'){
				$select_array = array(0 => adminlang('robots_select_downserv'));
				$sql = "SELECT servid, servname FROM " . DB::table('downserver') . " WHERE chanid>='0' AND depth='0' ORDER BY sortord";
				$query = DB::query($sql);
				while ($row = DB::fetch_array($query)) {
					$select_array[$row['servid']] = trim($row['servname']);
				}
				//$adminhtml->table_setting('robots_downdir', 'robots[downdir]', trim($robots['downdir']), 'text');
				$adminhtml->table_setting('robots_servid', 'robots[servid]', intval($robots['servid']), 'select', '', $select_array);
			}
			$adminhtml->table_setting('robots_hitsvote', 'robots[hitsvote]', intval($robots['hitsvote']), 'text');
			$adminhtml->table_setting('robots_auditstatus', 'robots[auditstatus]', intval($robots['auditstatus']), 'radio');
			$adminhtml->table_setting('robots_logenabled', 'robots[logenabled]', intval($robots['logenabled']), 'radio');
			$adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
			$adminhtml->table_end('</form>');
		}else{
		}
	}
}elseif($action == 'rule'){
	$do = isset(phpcom::$G['gp_do']) ? trim(phpcom::$G['gp_do']) : null;
	$ruleid = isset(phpcom::$G['gp_ruleid']) ? intval(phpcom::$G['gp_ruleid']) : 0;
	$cid = isset(phpcom::$G['gp_cid']) ? intval(phpcom::$G['gp_cid']) : 0;
	$url = ADMIN_SCRIPT . "?m=robots";
	if($do == 'edit' && $ruleid){
		if($robots = DB::fetch_first("SELECT botid FROM " . DB::table('robots') . " WHERE ruleid='$ruleid' LIMIT 1")){
			$url .= "&action=edit&chanid=$chanid&botid=" . $robots['botid'];
		}else{
			$url .= "action=add&chanid=$cid&ruleid=$ruleid";
		}
		@header("Location: $url");
	}elseif($do == 'del' && $ruleid){
		if(!phpcom_admincp::permission('robots_delete')){
			admin_message('action_delete_denied');
		}
		DB::delete('robots_rule', "ruleid='$ruleid'");
		admin_succeed('robots_rule_delete_succeed', "m=robots&action=rule&chanid=$chanid");
	}else{
		if (!checksubmit(array('submit', 'btnsubmit'))) {
			$adminhtml->table_header('menu_robots_rule');
			$adminhtml->table_td(array(array('robots_rule_tips', FALSE, 'colspan="6"')), NULL, FALSE, NULL, NULL, FALSE);
			$adminhtml->table_th(array(
					array('robots_rule_name', 'width="35%" class="left"'),
					array('robots_channel', 'width="15%" class="left"'),
					array('robots_rule_paging', 'width="10%" noWrap="noWrap"'),
					array('robots_rule_downimage', 'width="10%" noWrap="noWrap"'),
					array('robots_dateline', 'width="15%" class="left"'),
					array('operation', 'width="15%" class="left"')
			));
			$condition = $chanid ? "r.chanid='$chanid'" : "r.chanid>'0'";
			$totalrec = isset(phpcom::$G['gp_count']) ? intval(phpcom::$G['gp_count']) : 0;
			!$totalrec && $totalrec = DB::result_first("SELECT COUNT(*) FROM " . DB::table('robots_rule') . " r WHERE $condition");
			$pagesize = 30;
			$pagecount = @ceil($totalrec / $pagesize);
			$pagenow = max(1, min($pagecount, intval($page)));
			$pagestart = floor(($pagenow - 1) * $pagesize);
			$sql = DB::buildlimit("SELECT r.*,c.channelname FROM " . DB::table('robots_rule') . " r
				LEFT JOIN " . DB::table('channel') . " c ON c.channelid=r.chanid
				WHERE $condition ORDER BY r.ruleid DESC", $pagesize, $pagestart);
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				$rid = $row['ruleid'];
				$edit = $adminhtml->edit_word('edit', "m=robots&action=rule&do=edit&chanid=$chanid&ruleid=$rid&cid={$row['chanid']}", ' | ');
				$edit .= $adminhtml->del_word('delete', "m=robots&action=rule&do=del&chanid=$chanid&ruleid=$rid");
				$row['paging'] = $row['paging'] ? '<span class="blue">&radic;</span>' : '<span class="red">&times;</span>';
				$row['downimage'] = $row['downimage'] ? '<span class="blue">&radic;</span>' : '<span class="red">&times;</span>';
				$row['downattach'] = $row['downattach'] ? '<span class="blue">&radic;</span>' : '<span class="red">&times;</span>';
				$adminhtml->table_td(array(
						array($row['rulename'], TRUE),
						array('<a href="?m=robots&action=rule&chanid='.$row['chanid'].'">'.$row['channelname'].'</a>', TRUE),
						array($row['paging'], TRUE, 'align="center"'),
						array($row['downimage'], TRUE, 'align="center"'),
						array('<em class="f10">'.fmdate($row['dateline']. '</em>', 'Y-m-d', 'd'), TRUE),
						array($edit, TRUE)
				));
			}
			$showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=robots&action=rule&chanid=$chanid&count=$totalrec") . '</var>';
			$adminhtml->table_td(array(
					array($showpage, TRUE, 'colspan="6" align="right" id="pagecode"')
			), NULL, FALSE, NULL, NULL, FALSE);
			$adminhtml->table_end();
		}else{
			
		}
	}
}elseif($action == 'del'){
	if(!phpcom_admincp::permission('robots_delete')){
		admin_message('action_delete_denied');
	}
	$botid = isset(phpcom::$G['gp_botid']) ? intval(phpcom::$G['gp_botid']) : 0;
	if($botid){
		DB::delete('robots', "botid='$botid'");
	}
	admin_succeed('robots_delete_succeed', "m=robots&chanid=$chanid");
}else{
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$adminhtml->table_header();
		$adminhtml->table_th(array(
				array('robots_name', 'width="25%" class="left"'),
				array('robots_channel', 'width="10%" class="left"'),
				array('robots_category', 'width="15%" class="left"'),
				array('robots_dateline', 'width="15%" class="left"'),
				array('operation', 'width="35%"')
		));
		$condition = $chanid ? "b.chanid='$chanid'" : "b.chanid>'0'";
		$totalrec = isset(phpcom::$G['gp_count']) ? intval(phpcom::$G['gp_count']) : 0;
		!$totalrec && $totalrec = DB::result_first("SELECT COUNT(*) FROM " . DB::table('robots') . " b WHERE $condition");
		$pagesize = 30;
		$pagecount = @ceil($totalrec / $pagesize);
		$pagenow = max(1, min($pagecount, intval($page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$sql = DB::buildlimit("SELECT b.*,c.catname,m.channelname FROM " . DB::table('robots') . " b
			LEFT JOIN " . DB::table('category') . " c USING(catid)
			LEFT JOIN " . DB::table('channel') . " m ON m.channelid=b.chanid
			WHERE $condition ORDER BY b.dateline DESC", $pagesize, $pagestart);
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$botid = $row['botid'];
			$edit = $adminhtml->edit_word('setting', "m=robots&action=edit&chanid=$chanid&botid=$botid", ' | ');
			$edit .= $adminhtml->edit_word('robots_start', "m=robots&action=start&chanid=$chanid&botid=$botid", ' | ');
			$edit .= $adminhtml->edit_word('robots_start_blank', "m=robots&action=start&chanid=$chanid&botid=$botid", ' | ', 1);
			$edit .= $adminhtml->edit_word('demo', "m=robots&action=edit&chanid=$chanid&botid=$botid&step=4&demo=1", ' | ');
			$edit .= $adminhtml->del_word('delete', "m=robots&action=del&chanid=$chanid&botid=$botid");
			$pageurl = trim($row['pageurl']);
			$pageurl = preg_replace("/\[(\d+)\-(\d+)\]/i", "1", $pageurl);
			$adminhtml->table_td(array(
					array('<a href="javascript:void(0)" onclick="window.open(\''.$pageurl.'\')">' . $row['botname'] . '</a>', TRUE),
					array('<a href="?m=robots&chanid='.$row['chanid'].'">'.$row['channelname'].'</a>', TRUE),
					array($row['catname'], TRUE),
					array('<em class="f10">'.fmdate($row['dateline']. '</em>', 'Y-m-d H:i', 'd'), TRUE),
					array($edit, TRUE)
			));
		}
		$showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=robots&chanid=$chanid&count=$totalrec") . '</var>';
		$adminhtml->table_td(array(
				array($showpage, TRUE, 'colspan="5" align="right" id="pagecode"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end();
		
		$dir = PHPCOM_ROOT . "/data/robots";
		if(!is_dir($dir)) @mkdir($dir);
	}else{
		
	}
}

admin_footer();

function getpageurls($robots, $robotsrule, $one = false){
	return HttpSpider::getListPageUrls($robots['pageurl'], $robots['charset'], $robotsrule['listarea'],
			$robotsrule['listurl'], $robotsrule['listurladd'], $robots['descend'], $one);
}

function robots_get_pageurls($robots, $one = false){
	$pageurls = HttpSpider::getListPageUrls($robots['pageurl'], $robots['charset'], $robots['listarea'],
			$robots['listurl'], $robots['listurladd'], $robots['descend'], $one);
	urls_cache_set($robots['botid'], $pageurls);
	return $pageurls;
}

function paging_get_urls($html, &$robots, $url = null){
	if(empty($robots['paging'])) return null;
	if($pagingarea = HttpSpider::substring($robots['pagingarea'], $html, true)){
		if($pagingarea == 'current') return false;
		if($pagingurls = HttpSpider::substring($robots['pagingurl'], $pagingarea)){
			if($pagingurls[0] == 'current') return false;
			return HttpSpider::convertListUrl($pagingurls, $url, $robots['pagingurladd']);
		}
	}
	return null;
}

function paging_get_contents($urls, &$robots, $baseurl = null, $bbcode = true){
	if(empty($urls)) return null;
	$content = '';
	foreach($urls as $url){
		if(strcasecmp($url, $baseurl) != 0){
			if($html = HttpSpider::getContents($url, trim($robots['charset']))){
				$html = HttpSpider::replace($html, $robots['htmlreplace']);
				if($contents = HttpSpider::substring($robots['content'], $html, true)){
					$contents = HttpSpider::replace($contents, $robots["contentreplace"]);
					$contents = HttpSpider::parseImageUrl($contents, $url, $bbcode);
					$contents = HttpSpider::htmlToCode($contents, $bbcode);
					$content .= '[pagebreak]'. trim($contents);
				}
			}
		}
	}
	return $content;
}

function parsehtmlcontents($html, $fields, $rules, $baseurl = ''){
	$tmp = array();
	foreach($fields as $key => $field){
		if(isset($rules[$key])){
			$contents = HttpSpider::substring($rules[$key], $html, true);
			if($key == 'title' || $key == 'content'){
				$contents = HttpSpider::replace($contents, $rules[$key."replace"]);
				$contents = HttpSpider::parseImageUrl($contents, $baseurl, false);
			}elseif($key == 'runsystem'){
				$contents = HttpSpider::formatRunSystem($contents);
			}elseif($key != 'summary'){
				$contents = robots_get_default($contents, "robots_{$key}_default");
			}
			$tmp[$field] = trim($contents);
		}
	}
	return $tmp;
}

function robots_redirect($url, $sec = 5){
	$sec = $sec > 5 ? $sec : 5;
	$url = ADMIN_SCRIPT . "?$url";
	echo <<<EOT
	<script type="text/javascript">
			function robots_redirect() {
				window.location.replace('$url');
			}
			setTimeout('robots_redirect();', $sec);
	</script>
EOT;
}

function robots_get_default($value, $defvalue = ''){
	if($defvalue && strpos($defvalue, '_')){
		$defvalue = adminlang($defvalue);
	}
	return $value ? trim($value) : $defvalue;
}

function urls_cache_get($botid){
	$file = PHPCOM_ROOT . "/data/robots/cache_{$botid}_urls.php";
	if(file_exists($file)) {
		return file_unserialize($file);
	}
	return array();
}

function urls_cache_set($botid, $data){
	$file = PHPCOM_ROOT . "/data/robots/cache_{$botid}_urls.php";
	if(file_exists($file)) {
		@unlink($file);
	}
	file_serialize($file, $data);
}

function robots_cache_get($botid){
	$file = PHPCOM_ROOT . "/data/robots/cache_{$botid}_robots.php";
	if(!file_exists($file)) {
		if(!robots_cache_set($botid)){
			return array();
		}
	}
	return file_unserialize($file);
}

function robots_cache_set($botid){
	$sql = "SELECT t1.*, t2.* FROM " . DB::table('robots') . " t1
		LEFT JOIN " . DB::table('robots_rule') . " t2 ON t2.ruleid=t1.ruleid
		WHERE t1.botid='$botid'";
	if($robots = DB::fetch_first($sql)){
		$file = PHPCOM_ROOT . "/data/robots/cache_{$botid}_robots.php";
		if(empty($robots['ruleid'])){
			return false;
		}
		return file_serialize($file, $robots);
	}
	return false;
}

function robots_cache_clear($botid = 0){
	$path = PHPCOM_ROOT . "/data/robots/";
	$path .= $botid ? "cache_{$botid}_*.php" : "*.php";
	$files = glob($path);
	foreach ($files as $file) {
		if(!is_dir($file)) {
			@unlink($file);
		}
	}
}

function get_testsoft_default(){
	if(!isset(phpcom::$G['cache']['softtest'])){
		phpcom_cache::load('softtest');
	}
	$testarray = phpcom::$G['cache']['softtest'];
	$result = '';
	foreach ($testarray as $key => $value) {
		if(!empty($value['checked'])){
			$result .= "$key,";
		}
	}
	return trim($result, ', ');
}
?>