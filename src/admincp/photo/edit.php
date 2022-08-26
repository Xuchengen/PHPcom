<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : edit.php  2012-8-15
 */
!defined('IN_PHPCOM') && exit('Access denied');

$deftable = intval(phpcom::$G['cache']['channel']['deftable']);
$tid = isset(phpcom::$G['gp_tid']) ? intval(phpcom::$G['gp_tid']) : 0;
$specid = isset(phpcom::$G['gp_specid']) ? intval(phpcom::$G['gp_specid']) : 0;

if (!checksubmit(array('btnsubmit', 'submit'))) {
	include loadlibfile('adminthread');
	$catid = isset(phpcom::$G['gp_catid']) ? intval(phpcom::$G['gp_catid']) : 0;
	$attachs = array();
	$threads = array('tid' => 0, 'chanid' => 0, 'rootid' => 0, 'catid' => $catid, 'title' => '', 'url' => '',
			'htmlname' => '', 'highlight' => 0, 'istop' => 0, 'topline' => 0, 'focus' => 0, 'polled' => 0, 'hits' => 0,
			'digest' => 0, 'dateline' => 0, 'lastdate' => 0, 'credits' => 0, 'bancomment' => 0, 'status' => 1, 'uid' => phpcom::$G['uid'],
			'subtitle' => '', 'summary' => '','tname' => '','person' => '' ,'author' => '', 'source' => '', 'demourl' => '', 'locked' => 0);
	$contents = array('keyword' => '', 'content' => '', 'trackback' => '', 'tags' => '');
	$threadfields = array('isupdate' => 0, 'voteup' => 0, 'votedown' => 0,
			'credits' => phpcom::$G['cache']['channel']['defaultcredits'],
			'groupids' => phpcom::$G['cache']['channel']['defaultgroupids']);

	if ($action == 'edit' && $tid) {
		$threads = DB::fetch_first("SELECT t.*, a.* FROM " . DB::table('threads') . " t
			 LEFT JOIN " . DB::table('photo_thread') . " a USING(tid)
			 WHERE t.tid=$tid");
		$tableindex = $threads['tableindex'];
		if($cont = DB::fetch_first("SELECT * FROM " . DB::table('photo_content', $tableindex) . " WHERE tid='$tid'")){
			$contents = $cont;
			$contents['tags'] = get_tagstr($contents['tags']);
		}
		$threadfields = DB::fetch_first("SELECT * FROM " . DB::table('thread_field') . " WHERE tid='$tid'");

		$attachs = Attachment::getAttachlist($tid, 1, 'photo');
		$attachfind = $attachreplace = array();
		foreach ($attachs as $attach) {
			$attachfind[] = "/\[attach\]$attach[attachid]\[\/attach\]/i";
			$attachreplace[] = '[attachimg]' . $attach['attachid'] . '[/attachimg]';
		}
		$attachfind && $contents['content'] = preg_replace($attachfind, $attachreplace, $contents['content']);
		if(!phpcom::$G['founders'] && $threads['locked'] == 2){
			admin_message('threads_founder_locked');
		}elseif(!phpcom::$G['founders'] && $threads['locked'] && $threads['uid'] != phpcom::$G['uid']){
			admin_message('threads_locked_edit_denied');
		}
	} else {
		$sources = explode(',', phpcom::$G['cache']['channel']['source']);
		$threads['source'] = $sources[0];
		$threads['status'] = 1;
	}
	$threads['uid'] = $threads['uid'] ? $threads['uid'] : phpcom::$G['uid'];
	echo '<script type="text/javascript">';
	echo "phpcom.chanid=$chanid;phpcom.tid=$tid;";
	echo '</script>';
	$adminhtml->editor_scritp('photo');
	$adminhtml->form("m=photo&action=$action&chanid=$chanid" , array(
			array('channelid', $chanid), array('tid', $tid),
			array('thumbtmpid', 0, 'thumbtmpid'),
			array('previewtmpid', 0, 'previewtmpid'),
			array('uid', $threads['uid']), array('posttime', TIMESTAMP)
	), ' onsubmit="return formSubmit(this);" autocomplete="off"');
	$adminhtml->table_header('photo_' . $action, 4);
	$selectcategory = '<select id="catid" name="threads[catid]" class="select t50">';
	$selectcategory .= '<optgroup label="' . adminlang('select_category') . '">';
	$selectcategory .= select_category($chanid, intval($threads['catid']));
	$selectcategory .= '</optgroup></select>';
	$classids = get_threadclassids($tid);
	$selectcategory .= '<input type="hidden" id="classidstr" name="classidstr" value="'.$classids.'" />';
	$select_threadclass = '<button class="button" type="button" onclick="openThreadClassWindow('.$chanid.');">' . adminlang('select_threadclass') . '</button>';
	$adminhtml->table_td(array(
			array('photo_category', FALSE, 'width="10%" noWrap="noWrap"', '', TRUE),
			array($selectcategory, TRUE, 'width="42%"'),
			array('select_threadclass', FALSE, 'width="10%" noWrap="noWrap"', '', TRUE),
			array($select_threadclass, TRUE, 'width="38%"')
	));

	$adminhtml->table_td(array(
			array('photo_title', FALSE, '', '', TRUE),
			array($adminhtml->textinput('threads[title]', $threads['title'], 70), TRUE, 'colspan="0"'),
			array('highlight', FALSE, '', '', TRUE),
			array($adminhtml->highlight_select($threads['highlight']), TRUE),
	));
	$adminhtml->table_td(array(
			array('photo_subtitle', FALSE, '', '', TRUE),
			array($adminhtml->textinput('photos[subtitle]', $threads['subtitle'], 70), TRUE),
			array('photo_demourl', FALSE, '', '', TRUE),
			array($adminhtml->textinput('photos[demourl]', $threads['demourl'], 50, null, null, 'photo_demourl_comments'), TRUE)
	));
	$trackback = '<br/><div id="trackbackdiv" style="margin-top:5px;display:none">' . $adminhtml->textinput('contents[trackback]', $contents['trackback'], '35', 'trackbacktext') . adminlang('photo_trackback_comments') . '</div>';
	$adminhtml->table_td(array(
			array('photo_person', FALSE, '', '', TRUE),
			array($adminhtml->textinput('photos[person]', $threads['person'], '70'), TRUE, 'noWrap="noWrap"'),
			array('photo_source', FALSE, '', '', TRUE),
			array($adminhtml->textinput('photos[source]', $threads['source'], '15', 'sourcetext', "hideDisplay('trackbackdiv')") .
					select_author_source('source', 'sourcetext', $threads['source']) . $trackback, TRUE, 'noWrap="noWrap"')
	));
	$btntags = '<button class="button" type="button" onclick="insert_tags()">' . adminlang('intags') . '</button>';
	$btntags .= "<button class=\"button\" type=\"button\" onclick=\"uploadingWindow('threadimage',null,$chanid,$tid);\"> " . adminlang('imageupload') . " </button>";
	$btntags .= '<button class="button" type="button" onclick="hideDisplay(\'summarybody\')">' . adminlang('showsummary') . '</button>';
	if($action == 'edit'){
		$btntags .= "<button class=\"button\" type=\"button\" onclick=\"location.href='?m=attachment&chanid=$chanid&tid=$tid'\">" . adminlang('attachment') . '</button>';
	}
	$adminhtml->table_td(array(
			array('photo_tags', FALSE, 'noWrap="noWrap"', '', TRUE),
			array($adminhtml->textinput('contents[tags]', $contents['tags'], 60, 'tagstring') . ' ' . $btntags, TRUE, 'colspan="3"')
	));
	echo '<tbody id="summarybody" style="display:none">';
	$adminhtml->table_td(array(
			array('photo_keyword', FALSE, '', '', TRUE),
			array($adminhtml->textinput('contents[keyword]', $contents['keyword'], 70), TRUE),
			array('keyword_comments', FALSE, 'colspan="2"', '', 'tips')
	));
	$adminhtml->textarea('photo_summary', $threads['summary'], 'photos[summary]', 'summary_contents', 3);
	$adminhtml->table_td(array(
			array('photo_htmlname', FALSE, 'noWrap="noWrap"', '', TRUE),
			array($adminhtml->textinput('threads[htmlname]', $threads['htmlname'], 70), TRUE),
			array('photo_htmlname_comments', FALSE, 'colspan="2"', '', 'tips'),
	));
	$adminhtml->table_td(array(
			array('photo_url', FALSE, '', '', TRUE),
			array($adminhtml->textinput('threads[url]', $threads['url'], 70), TRUE),
			array('photo_url_comments', FALSE, 'colspan="2"', '', 'tips'),
	));
	echo '</tbody>';
	$adminhtml->editor_content('photo_content', $contents['content'], 'contents[content]', 'editor_content', 3, '', '200px');
	$appsetting = $adminhtml->checkbox(
			array('topline','focus', 'bancomment', 'audit', 'locked', 'updatenow'),
			array('threads[topline]', 'threads[focus]', 'threads[bancomment]', 'threads[status]', 'threads[locked]', 'updatenow'),
			array(intval($threads['topline']), intval($threads['focus']), intval($threads['bancomment']), intval($threads['status']), intval($threads['locked']), 0));
	$adminhtml->table_td(array(
			array('appsetting', FALSE, '', '', TRUE),
			array($appsetting, TRUE),
			array('opinion', FALSE, 'noWrap="noWrap"', '', TRUE),
			array($adminhtml->textinput('threadfields[voteup]', intval($threadfields['voteup']), 5, null, null, 'opinion_good') . ' x ' .
				$adminhtml->textinput('threadfields[votedown]', intval($threadfields['votedown']), 5, null, null, 'opinion_bad'), TRUE)
	));
	$adminhtml->table_td(array(
			array('digest_option', FALSE, '', '', TRUE),
			array($adminhtml->radio('digests', 'threads[digest]', intval($threads['digest'])), TRUE),
			array('hits', FALSE, '', '', TRUE),
			array($adminhtml->textinput('threads[hits]', intval($threads['hits']), 5) . adminlang('rand_rate_input'), TRUE)
	));
	if($specialradio = get_specialclass_radio($tid, $specid)){
		$adminhtml->table_td(array(
				array('select_special', FALSE, '', '', TRUE),
				array($specialradio, TRUE, 'colspan="3"')
		));
	}
	$btnsubmit = $adminhtml->submit_button();
	$adminhtml->table_td(array(
			array($btnsubmit, TRUE, 'align="center" colspan="4"')
	), NULL, FALSE, NULL, NULL, FALSE);
	$adminhtml->table_end('</form>');
	$uid = $threads['uid'] ? $threads['uid'] : phpcom::$G['uid'];
	include loadlibfile('uploadattach', 'inc/common');
} else {
	$threads = striptags(phpcom::$G['gp_threads']);
	$photos = striptags(phpcom::$G['gp_photos']);
	$threadfields = phpcom::$G['gp_threadfields'];
	$contents = phpcom::$G['gp_contents'];
	$uid = intval(phpcom::$G['uid']);
	if (empty($threads['title'])) {
		admin_message('threads_title_invalid');
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
	$contents['trackback'] = checkurlhttp($contents['trackback']);
	$isupdate = 0;
	$post = new DataAccess_PostThread($chanid);
	if ($action == 'edit') {
		if (isset(phpcom::$G['gp_updatenow']) && phpcom::$G['gp_updatenow']) {
			$isupdate = $threadfields['isupdate'] = 1;
		}
		if($thread = $post->update($tid, $threads, $threadfields, $photos, $contents)){
			$uid = $thread['uid'] ? $thread['uid'] : phpcom::$G['uid'];
			if(!empty($thread['locked']) && $thread['locked'] == 2){
				admin_message('threads_founder_locked');
			}elseif(!empty($thread['locked'])){
				admin_message('threads_locked_edit_denied');
			}
			if (isset(phpcom::$G['gp_special'])) {
				$post->updateSpecialData($tid, phpcom::$G['gp_special'], $thread['dateline'], $isupdate);
			}
			if (isset(phpcom::$G['gp_classidstr'])) {
				$classidstr = trim(phpcom::$G['gp_classidstr'], ', ');
				if(isset(phpcom::$G['gp_classids']) && phpcom::$G['gp_classids']){
					$classidstr .= ',' . implode(',', phpcom::$G['gp_classids']);
				}
				$post->updateThreadClass($classidstr, $tid, $thread['catid'], $thread['dateline'], $isupdate, $thread['status']);
			}
			update_memberlastpost();
		}else{
        	admin_message('threads_edit_error');
        }
	} else {
		$threads['polled'] = 0;
		$thread = array();
		if($tid = $post->insert($thread, $threads, $threadfields, $photos, $contents)){
			if (isset(phpcom::$G['gp_special'])) {
				$post->updateSpecialData($tid, phpcom::$G['gp_special'], $thread['dateline'], 1);
			}
			if (isset(phpcom::$G['gp_classidstr'])) {
				$classidstr = trim(phpcom::$G['gp_classidstr'], ', ');
				if(isset(phpcom::$G['gp_classids']) && phpcom::$G['gp_classids']){
					$classidstr .= ',' . implode(',', phpcom::$G['gp_classids']);
				}
				$post->insertThreadClass($classidstr, $tid, $thread['catid'], $thread['dateline'], $thread['status']);
			}
			update_memberlastpost('threads');
		}else{
			admin_message('threads_add_error');
		}
	}
	if(isset(phpcom::$G['gp_attachnew']) || $action == 'edit') {
		$attachnew = isset(phpcom::$G['gp_attachnew']) ? phpcom::$G['gp_attachnew'] : null;
		$post->updateAttach($tid, $attachnew, $uid, $chanid, 'photo');
	}
	if (!empty(phpcom::$G['gp_thumbtmpid']) || !empty(phpcom::$G['gp_previewtmpid'])) {
		$post->updateThreadImage($tid, phpcom::$G['gp_thumbtmpid'], phpcom::$G['gp_previewtmpid'], 'photo');
	}
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
		admin_succeed('threads_edit_succeed', "m=photo&action=list&chanid=$chanid", array('url' => "m=photo&action=edit&chanid=$chanid&tid=$tid"));
	} else {
		admin_succeed('threads_add_succeed', "m=photo&chanid=$chanid", array('url' => "m=photo&action=add&chanid=$chanid&catid=$catid"));
	}
}
?>