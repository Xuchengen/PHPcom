<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : edit.php  2013-11-7
 */
!defined('IN_PHPCOM') && exit('Access denied');
$deftable = intval(phpcom::$G['cache']['channel']['deftable']);
$tid = isset(phpcom::$G['gp_tid']) ? intval(phpcom::$G['gp_tid']) : 0;

if (!checksubmit(array('btnsubmit', 'submit'))) {
	include loadlibfile('adminthread');
	$catid = isset(phpcom::$G['gp_catid']) ? intval(phpcom::$G['gp_catid']) : 0;
	$attachs = array();
	$threads = array('tid' => 0, 'chanid' => 0, 'rootid' => 0, 'catid' => $catid, 'title' => '', 'url' => '',
			'htmlname' => '', 'highlight' => 0, 'istop' => 0, 'topline' => 0, 'focus' => 0, 'polled' => 0, 'hits' => 0,
			'digest' => 0, 'dateline' => 0, 'lastdate' => 0, 'bancomment' => 0, 'status' => 1, 'uid' => phpcom::$G['uid'],
			'subject' => '', 'summary' => '','tplname' => '', 'specname' => '', 'domain' => '', 'official' => '', 
			'demourl' => '', 'videourl' => '', 'locked' => 0);
	$contents = array('keyword' => '', 'content' => '', 'related' => '', 'tags' => '', 'pagesize' => 0,
			'logo' => '', 'banner' => '', 'background' => '', 'tidlist' => ''
	);
	$threadfields = array('isupdate' => 0, 'voteup' => 0, 'votedown' => 0,
			'credits' => phpcom::$G['cache']['channel']['defaultcredits'],
			'groupids' => phpcom::$G['cache']['channel']['defaultgroupids']);
	if ($action == 'edit' && $tid) {
		$threads = DB::fetch_first("SELECT t.*, a.* FROM " . DB::table('threads') . " t
			 LEFT JOIN " . DB::table('special_thread') . " a USING(tid)
				WHERE t.tid=$tid");
		$tableindex = $threads['tableindex'];
		if($cont = DB::fetch_first("SELECT * FROM " . DB::table('special_content', $tableindex) . " WHERE tid='$tid'")){
			$contents = $cont;
			$contents['tags'] = get_tagstr($contents['tags']);
		}
		$threadfields = DB::fetch_first("SELECT * FROM " . DB::table('thread_field') . " WHERE tid='$tid'");
		$attachs = Attachment::getAttachlist($tid, 1, 'special');
		$attachfind = $attachreplace = array();
		foreach ($attachs as $attach) {
			$attachfind[] = "/\[attach\]$attach[attachid]\[\/attach\]/i";
			$attachreplace[] = '[attachimg]' . $attach['attachid'] . '[/attachimg]';
		}
		$attachfind && $contents['content'] = preg_replace($attachfind, $attachreplace, $contents['content']);
		if(!phpcom::$G['founders'] && $threads['locked'] == 2){
			admin_message('special_founder_locked');
		}elseif(!phpcom::$G['founders'] && $threads['locked'] && $threads['uid'] != phpcom::$G['uid']){
			admin_message('special_locked_edit_denied');
		}
	}else{
		$threads['status'] = 1;
	}
	$threads['uid'] = $threads['uid'] ? $threads['uid'] : phpcom::$G['uid'];
	echo '<script type="text/javascript">';
	echo "phpcom.chanid=$chanid;phpcom.tid=$tid;";
	echo '</script>';
	$adminhtml->editor_scritp('special');
	$adminhtml->form("m=special&action=$action&chanid=$chanid", array(
			array('channelid', $chanid),
			array('tid', $tid),
			array('thumbtmpid', 0, 'thumbtmpid'),
			array('previewtmpid', 0, 'previewtmpid'),
			array('backgroundtmpid', 0, 'backgroundtmpid'),
			array('bannertmpid', 0, 'bannertmpid'),
			array('uid', $threads['uid']),
			array('posttime', TIMESTAMP)
	), ' onsubmit="return formSubmit(this);" autocomplete="off"');
	$adminhtml->table_header('special_' . $action, 4);
	$selectcategory = '<select id="catid" name="threads[catid]" class="select t50">';
	$selectcategory .= '<optgroup label="' . adminlang('select_category') . '">';
	$selectcategory .= select_category($chanid, intval($threads['catid']));
	$selectcategory .= "</optgroup></select>\r\n";
	//$classids = get_threadclassids($tid);
	//$selectcategory .= '<input type="hidden" id="classidstr" name="classidstr" value="'.$classids.'" />';
	//$selectcategory .= '<button class="button" type="button" onclick="openThreadClassWindow('.$chanid.');">' . adminlang('select_threadclass') . '</button>';
	$adminhtml->table_td(array(
			array('special_category', FALSE, 'width="10%" noWrap="noWrap"', '', TRUE),
			array($selectcategory, TRUE, 'width="42%"'),
			array('highlight', FALSE, 'width="10%" noWrap="noWrap"', '', TRUE),
			array($adminhtml->highlight_select($threads['highlight']), TRUE, 'width="38%"')
	));
	$adminhtml->table_td(array(
			array('special_title', FALSE, '', '', TRUE),
			array($adminhtml->textinput('threads[title]', $threads['title'], 70), TRUE, 'colspan="0"'),
			array('special_specname', FALSE, '', '', TRUE),
			array($adminhtml->textinput('specials[specname]', $threads['specname'], 50, null, null, 'special_specname_comments'), TRUE)
	));
	$adminhtml->table_td(array(
			array('special_subject', FALSE, '', '', TRUE),
			array($adminhtml->textinput('specials[subject]', $threads['subject'], 70), TRUE),
			array('special_htmlname', FALSE, '', '', TRUE),
			array($adminhtml->textinput('threads[htmlname]', $threads['htmlname'], 50, null, null, 'special_htmlname_comments'), TRUE)
	));
	$adminhtml->table_td(array(
			array('special_official', FALSE, '', '', TRUE),
			array($adminhtml->textinput('specials[official]', $threads['official'], 70, null, null, 'special_official_comments'), TRUE),
			array('special_demourl', FALSE, '', '', TRUE),
			array($adminhtml->textinput('specials[demourl]', $threads['demourl'], 50, null, null, 'special_demourl_comments'), TRUE)
	));
	$adminhtml->table_td(array(
			array('special_videourl', FALSE, '', '', TRUE),
			array($adminhtml->textinput('specials[videourl]', $threads['videourl'], 70, null, null, 'special_videourl_comments'), TRUE),
			array('special_domain', FALSE, '', '', TRUE),
			array($adminhtml->textinput('specials[domain]', $threads['domain'], 50, null, null, 'special_domain_comments'), TRUE)
	));
	$btntags = '<button class="button" type="button" onclick="insert_tags()">' . adminlang('intags') . '</button>';
	$btntags .= '<button class="button" type="button" onclick="hideDisplay(\'summarybody\')">' . adminlang('showsummary') . '</button>';
	$btntags .= "<button class=\"button\" type=\"button\" onclick=\"uploadingWindow('threadimage',null,$chanid,$tid);\"> " . adminlang('imageupload') . " </button>";
	if($action == 'edit'){
		$btntags .= "<button class=\"button\" type=\"button\" onclick=\"location.href='?m=attachment&chanid=$chanid&tid=$tid'\">" . adminlang('attachment') . '</button>';
	}
	$adminhtml->table_td(array(
			array('special_tags', FALSE, 'noWrap="noWrap"', '', TRUE),
			array($adminhtml->textinput('contents[tags]', $contents['tags'], 60, 'tagstring') . ' ' . $btntags, TRUE, 'colspan="3"')
	));
	echo '<tbody id="summarybody" style="display:none">';
	$adminhtml->table_td(array(
			array('special_keyword', FALSE, '', '', TRUE),
			array($adminhtml->textinput('contents[keyword]', $contents['keyword'], 70), TRUE),
			array('keyword_comments', FALSE, 'colspan="2"', '', 'tips')
	));
	$adminhtml->textarea('special_summary', $threads['summary'], 'specials[summary]', 'summary_contents', 3);
	$adminhtml->table_td(array(
			array('special_tplname', FALSE, 'noWrap="noWrap"', '', TRUE),
			array($adminhtml->textinput('specials[tplname]', $threads['tplname'], 70), TRUE),
			array('special_tplname_comments', FALSE, 'colspan="2"', '', 'tips'),
	));
	$adminhtml->table_td(array(
			array('special_url', FALSE, '', '', TRUE),
			array($adminhtml->textinput('threads[url]', $threads['url'], 70), TRUE),
			array('special_url_comments', FALSE, 'colspan="2"', '', 'tips'),
	));
	echo '</tbody>';
	$adminhtml->editor_content('special_content', $contents['content'], 'contents[content]', 'editor_content', 3, '99%', '150');
	$btnupload = " <button class=\"button\" type=\"button\" onclick=\"uploadingWindow('backgroundimage','image',$chanid,$tid);\"> " . adminlang('imageupload') . " </button>";
	$adminhtml->table_td(array(
			array('special_background', FALSE, '', '', TRUE),
			array($adminhtml->textinput('contents[background]', $contents['background'], 50, 'backgroundtmpid_file') . $btnupload, TRUE),
			array('special_banner', FALSE, '', '', TRUE),
			array($adminhtml->textinput('contents[banner]', $contents['banner'], 50, 'bannertmpid_file'), TRUE)
	));
	$appsetting = $adminhtml->checkbox(
			array('topline','focus', 'bancomment', 'audit', 'locked', 'updatenow'),
			array('threads[topline]', 'threads[focus]', 'threads[bancomment]', 'threads[status]', 'threads[locked]', 'updatenow'),
			array(intval($threads['topline']), intval($threads['focus']), intval($threads['bancomment']), intval($threads['status']), intval($threads['locked']), 0));
	$adminhtml->table_td(array(
			array('appsetting', FALSE, '', '', TRUE),
			array($appsetting, TRUE),
			array('opinion', FALSE, 'noWrap="noWrap"', '', TRUE),
			array($adminhtml->textinput('threadfields[voteup]', intval($threadfields['voteup']), 5, null, null, 'opinion_good') . ' x ' .
			$adminhtml->textinput('threadfields[votedown]', intval($threadfields['votedown']), 5, null, null, 'opinion_bad') . ' ' . 
			adminlang('hits') . ' ' . $adminhtml->textinput('threads[hits]', intval($threads['hits']), 5, null, null, 'hits') . adminlang('rand_rate_input'), TRUE)
	));
	$adminhtml->table_td(array(
			array('digest_option', FALSE, '', '', TRUE),
			array($adminhtml->radio('digests', 'threads[digest]', intval($threads['digest'])), TRUE),
			array('special_tidlist', FALSE, '', '', TRUE),
			array($adminhtml->textinput('contents[tidlist]', trim($contents['tidlist']), 50), TRUE)
	));
	$btnsubmit = $adminhtml->submit_button();
	$adminhtml->table_td(array(
			array($btnsubmit, TRUE, 'align="center" colspan="4"')
	), NULL, FALSE, NULL, NULL, FALSE);
	if($tid && $action == 'edit') {
		$adminhtml->table_end();
		echo <<<EOT
<script type="text/javascript">
var rowtypedata = ['&nbsp;','&nbsp;',
'<input name="specialclassnew[name][]" type="text" class="input t10"/>',
'<input name="specialclassnew[alias][]" type="text" class="input t10"/>',
'<input name="specialclassnew[title][]" type="text" class="input t30"/>',
'<input name="specialclassnew[about][]" type="text" class="input t30"/>',
'<input name="specialclassnew[pagesize][]" type="text" class="input t8" value="0"/>'];
</script>
EOT;
		$adminhtml->table_header('special_class');
		$adminhtml->table_td(array(
				array('deletecheckbox', FALSE, 'width="50" noWrap="noWrap"'),
				array('special_class_add_data', FALSE, 'nowrap="nowrap"'),
				array('special_class_name', FALSE),
				array('special_class_alias', FALSE),
				array('special_class_title', FALSE),
				array('special_class_about', FALSE),
				array('special_class_pagesize', FALSE)
		), '', FALSE, ' tablerow', NULL, FALSE);
		$sql = "SELECT * FROM " . DB::table('special_class') . " WHERE tid='$tid' ORDER BY classid";
		$query = DB::query($sql);
		$classid = 0;
		while ($row = DB::fetch_array($query)) {
			$classid = $row['classid'];
			$adminhtml->table_td(array(
					array('<input type="checkbox" class="checkbox" name="delete[' . $classid . ']" value="' . $classid . '" />', TRUE),
					array('<a href="javascript:void(0);" onclick="openDialog(\'?m=topical&specid=' . $tid . '&classid=' . $classid . '\', \'specialtopics\', \'' . adminlang('special_class_manage') . '\',820,400);">' . adminlang('special_class_manage') . '</a>', TRUE),
					array('<input name="specialclass[' . $classid . '][name]" title="classid: '.$classid.'" type="text" class="input t10" value="'.htmlcharsencode($row['name']).'" />', TRUE),
					array('<input name="specialclass[' . $classid . '][alias]" type="text" class="input t10" value="'.htmlcharsencode($row['alias']).'" />', TRUE),
					array('<input name="specialclass[' . $classid . '][title]" type="text" class="input t30" value="'.htmlcharsencode($row['title']).'" />', TRUE),
					array('<input name="specialclass[' . $classid . '][about]" type="text" class="input t30" value="'.htmlcharsencode($row['about']).'" />', TRUE),
					array('<input name="specialclass[' . $classid . '][pagesize]" type="text" class="input t8" value="'.intval($row['pagesize']).'" />', TRUE)
			));
		}
		if($classid == 0){
			$adminhtml->table_td(array(
					array('&nbsp;', TRUE),
					array('<a href="javascript:void(0);" onclick="openDialog(\'?m=topical&specid=' . $tid . '&classid=0\', \'specialtopics\', \'' . adminlang('special_class_manage') . '\',820,400);">' . adminlang('special_class_manage') . '</a>', TRUE),
					array('<input name="quicksid" type="text" class="input t8" />', TRUE),
					array('special_class_quick_add_comments', FALSE, 'colspan="4"')
			), 'quickadd');
		}
		//$adminhtml->table_td(array(array('special_class_tips', FALSE, 'colspan="7"')), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_td(array(
				array('special_class_add', FALSE, 'colspan="7"')
		));
	}
	$adminhtml->table_end('</form>');
	$uid = $threads['uid'] ? $threads['uid'] : phpcom::$G['uid'];
	include loadlibfile('uploadattach', 'inc/common');
}else{
	$threads = striptags(phpcom::$G['gp_threads']);
	$specials = striptags(phpcom::$G['gp_specials']);
	$threadfields = phpcom::$G['gp_threadfields'];
	$contents = phpcom::$G['gp_contents'];
	$uid = intval(phpcom::$G['uid']);
	$threadfields['groupids'] = '';
	if(isset(phpcom::$G['gp_usergroupids'])){
		$threadfields['groupids'] = trim(implodeids(phpcom::$G['gp_usergroupids'], ','), "'");
	}
	if(empty($threads['title']) && !empty($specials['specname'])){
		$threads['title'] = trim($specials['specname']);
	}
	if (empty($threads['title'])) {
		admin_message('threads_title_invalid');
	}
	if(empty($specials['specname'])){
		$specials['specname'] = $threads['title'];
	}
	$catid = intval($threads['catid']);
	if ($catid <= 0) {
		admin_message('threads_catid_invalid');
	}
	foreach(array('topline', 'focus', 'bancomment', 'digest', 'status', 'locked') as $key){
		if(isset($threads[$key])){
			$threads[$key] = intval($threads[$key]);
		}else{
			$threads[$key] = 0;
		}
	}
	$highlights = phpcom::$G['gp_highlights'];
	$threads['highlight'] = intval($highlights['font'] . $highlights['color']);
	//$contents['demourl'] = checkurlhttp($contents['demourl']);
	$isupdate = 0;
	$post = new DataAccess_PostThread($chanid);
	if ($tid && $action == 'edit') {
		if (isset(phpcom::$G['gp_updatenow']) && phpcom::$G['gp_updatenow']) {
			$isupdate = $threadfields['isupdate'] = 1;
		}
		if($thread = $post->update($tid, $threads, $threadfields, $specials, $contents, phpcom::$G['gp_backgroundtmpid'], phpcom::$G['gp_bannertmpid'])){
			$uid = $thread['uid'] ? $thread['uid'] : phpcom::$G['uid'];
			if(!empty($thread['locked']) && $thread['locked'] == 2){
				admin_message('special_founder_locked');
			}elseif(!empty($thread['locked'])){
				admin_message('special_locked_edit_denied');
			}
			update_memberlastpost();
		}else{
			admin_message('special_edit_error');
		}
	}else{
		$thread = array();
		$specials['years'] = date('Y');
		if($tid = $post->insert($thread, $threads, $threadfields, $specials, $contents)){
			update_memberlastpost('threads');
		}else{
			admin_message('special_add_error');
		}
	}
	if(isset(phpcom::$G['gp_attachnew']) || $action == 'edit') {
		$attachnew = isset(phpcom::$G['gp_attachnew']) ? phpcom::$G['gp_attachnew'] : null;
		$post->updateAttach($tid, $attachnew, $uid, $chanid, 'special');
	}
	if (!empty(phpcom::$G['gp_thumbtmpid']) || !empty(phpcom::$G['gp_previewtmpid'])) {
		$post->updateThreadImage($tid, phpcom::$G['gp_thumbtmpid'], phpcom::$G['gp_previewtmpid'], 'special');
	}
	if (!empty(phpcom::$G['gp_backgroundtmpid']) || !empty(phpcom::$G['gp_bannertmpid'])) {
		$post->deleteUploadTemp(phpcom::$G['gp_backgroundtmpid'], phpcom::$G['gp_bannertmpid']);
	}
	add_update_specialclass($tid, $chanid);
	if(isset(phpcom::$G['gp_randisrate']) && phpcom::$G['gp_randisrate']){
		$rates = rate_rand(1);
		$voters = $rates['voter'];
		$scores = $rates['total'];
		$voteup = $rates['voteup'];
		$votedown = $rates['votedown'];
		DB::query("UPDATE " . DB::table('thread_field') . " SET voters=voters+'$voters', totalscore=totalscore+'$scores', voteup=voteup+'$voteup', votedown=votedown+'$votedown' WHERE tid='$tid'");
		$hits = $voters + 10;
		DB::query("UPDATE " . DB::table('threads') . " SET hits=hits+'$hits' WHERE tid='$tid'");
	}
	if ($action == 'edit') {
		admin_succeed('special_edit_succeed', "m=special&action=list&chanid=$chanid", array('url' => "m=special&action=edit&chanid=$chanid&tid=$tid&uid=$myuid"));
	} else {
		admin_succeed('special_add_succeed', "m=special&chanid=$chanid", array('url' => "m=special&action=add&chanid=$chanid&catid=$catid&uid=$myuid"));
	}
}

function add_update_specialclass($tid, $chanid = 4){
	$specialclassnew = isset(phpcom::$G['gp_specialclassnew']) ? phpcom::$G['gp_specialclassnew'] : null;
	$specialclass = isset(phpcom::$G['gp_specialclass']) ? phpcom::$G['gp_specialclass'] : null;
	$delete = isset(phpcom::$G['gp_delete']) ? phpcom::$G['gp_delete'] : null;
	$quicksid = isset(phpcom::$G['gp_quicksid']) ? intval(phpcom::$G['gp_quicksid']) : 0;
	$existclass = false;
	if($quicksid && $tid){
		if(!DB::fetch_first("SELECT tid FROM " . DB::table('special_class') . " WHERE tid='$tid'")){
			$datalist = array();
			$query = DB::query("SELECT classid, name, alias, pagesize, type FROM " . DB::table('special_class') . " WHERE tid='$quicksid' ORDER BY classid");
			while ($row = DB::fetch_array($query)) {
				$specialclass = $specialclassnew = null;
				$datalist[] = array(
						'chanid' => $chanid,
						'tid' => $tid,
						'name' => addslashes($row['name']),
						'alias' => addslashes($row['alias']),
						'title' => '',
						'about' => '',
						'pagesize' => $row['pagesize'],
						'type' => $row['type']
				);
			}
			DB::free_result($query);
			$classids = array();
			foreach ($datalist as $data){
				$classids[] = DB::insert('special_class', $data, true);
				$existclass = true;
			}
			if(!empty($classids[0])){
				DB::update('special_data', array('classid' => $classids[0]), "specid='$tid' AND classid='0'");
			}
		}
	}
	if(!empty($specialclass)){
		foreach ($specialclass as $classid => $classes) {
			if($delete && isset($delete[$classid]) && $delete[$classid] == $classid){
				DB::delete('special_class', "classid='$classid'");
				DB::delete('special_data', "classid='$classid'");
			}else{
				$existclass = true;
				if(isset($classes['tid'])){
					unset($classes['tid']);
				}
				if(empty($classes['name'])){
					unset($classes['name']);
				}else{
					$classes['name'] = trim($classes['name']);
				}
				if(empty($classes['alias'])){
					unset($classes['alias']);
				}else{
					$classes['alias'] = trim(strip_tags($classes['alias']));
				}
				$classes['title'] = trim(strip_tags($classes['title']));
				$classes['about'] = trim(strip_tags($classes['about']));
				if(isset($classes['pagesize'])){
					$classes['pagesize'] = intval($classes['pagesize']);
				}
				if(isset($classes['type'])){
					$classes['type'] = intval($classes['type']);
				}
				DB::update('special_class', $classes, "classid='$classid'");
			}
		}
	}

	if(!empty($specialclassnew) && isset($specialclassnew['name']) && is_array($specialclassnew['name'])){
		$classids = array();
		foreach ($specialclassnew['name'] as $key => $value) {
			if(!empty($value) && !empty($specialclassnew['alias'][$key])){
				$classids[] = DB::insert('special_class', array(
				'chanid' => $chanid,
				'tid' => $tid,
				'name' => trim($value),
				'alias' => trim(strip_tags($specialclassnew['alias'][$key])),
				'title' => trim(strip_tags($specialclassnew['title'][$key])),
				'about' => trim(strip_tags($specialclassnew['about'][$key])),
				'pagesize' => isset($specialclassnew['pagesize'][$key]) ? intval($specialclassnew['pagesize'][$key]) : 0,
				'type' => isset($specialclassnew['type'][$key]) ? intval($specialclassnew['type'][$key]) : 0,
				), true);
				$existclass = true;
			}
		}
		if(!empty($classids[0])){
			DB::update('special_data', array('classid' => $classids[0]), "specid='$tid' AND classid='0'");
		}
	}

	if($existclass){
		DB::update('special_thread', array('existclass' => 1), "tid='$tid'");
	}
}
?>