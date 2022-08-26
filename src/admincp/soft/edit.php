<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : edit.php    2012-2-9
 */
!defined('IN_ADMINCP') && exit('Access denied');
$deftable = intval(phpcom::$G['cache']['channel']['deftable']);
$tid = isset(phpcom::$G['gp_tid']) ? intval(phpcom::$G['gp_tid']) : 0;
$specid = isset(phpcom::$G['gp_specid']) ? intval(phpcom::$G['gp_specid']) : 0;

@header('Cache-control: private, must-revalidate');
if (!checksubmit(array('btnsubmit', 'submit'))) {
	include loadlibfile('adminthread');
	$catid = isset(phpcom::$G['gp_catid']) ? intval(phpcom::$G['gp_catid']) : 0;
	$servid = 0;
	$threads = array('tid' => 0, 'chanid' => 0, 'rootid' => 0, 'catid' => $catid, 'softname' => '', 'url' => '',
			'softversion' => '', 'softlang' => '', 'softtype' => '', 'license' => '', 'star' => 3, 'softsize' => 0, 'release' => '',
			'softauth' => '', 'md5sums' => '', 'checksum' => '', 'contact' => '', 'homepage' => '', 'demourl' => '', 'softid' => 0,
			'htmlname' => '', 'highlight' => 0, 'istop' => 0, 'topline' => 0, 'focus' => 0, 'digest' => 0, 'company' => '',
			'polled' => 0, 'hits' => 0, 'dateline' => 0, 'lastdate' => 0, 'credits' => 0, 'bancomment' => 0, 'status' => 1,
			'uid' => phpcom::$G['uid'], 'subtitle' => '', 'summary' => '','tname' => '', 'author' => '', 'locked' => 0);
	$contents = array('keyword' => '', 'content' => '', 'trackback' => '', 'tags' => '');
	$threadfields = array('isupdate' => 0, 'voteup' => 0, 'votedown' => 0,
			'credits' => phpcom::$G['cache']['channel']['defaultcredits'],
			'groupids' => phpcom::$G['cache']['channel']['defaultgroupids']);
	$pollvotes = array('choices' => 1, 'pollid' => 0, 'expiration' => 0, 'polltitle' => '');
	$attachs = array();
	if ($action == 'edit' && $tid) {
		$threads = DB::fetch_first("SELECT t.*,a.* FROM " . DB::table('threads') . " t
			 LEFT JOIN " . DB::table('soft_thread') . " a USING(tid)
			 WHERE t.tid='$tid'");

		$tableindex = $threads['tableindex'];
		if($cont = DB::fetch_first("SELECT * FROM " . DB::table('soft_content', $tableindex) . " WHERE tid='$tid'")){
			$contents = $cont;
			$contents['tags'] = get_tagstr($contents['tags']);
		}
		
		$threadfields = DB::fetch_first("SELECT * FROM " . DB::table('thread_field') . " WHERE tid='$tid'");
		$attachs = Attachment::getAttachlist($tid, 1, 'soft');
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
	} elseif($action == 'quickedit') {
			$threads = DB::fetch_first("SELECT t.*,a.* FROM " . DB::table('threads') . " t
			 LEFT JOIN " . DB::table('soft_thread') . " a USING(tid)
			WHERE t.tid='$tid'");
		
			$tableindex = $threads['tableindex'];
			$contents = DB::fetch_first("SELECT * FROM " . DB::table('soft_content', $tableindex) . " WHERE tid='$tid'");
			$contents['tags'] = get_tagstr($contents['tags']);
			$threadfields = DB::fetch_first("SELECT * FROM " . DB::table('thread_field') . " WHERE tid='$tid'");
			$attachs = Attachment::getAttachlist($tid, 1, 'soft');
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
		
			$adminhtml->form("m=soft&action=savequickedit&inajax=1", array(
					array('channelid', $chanid), array('tid', $tid),
					array('thumbtmpid', 0, 'thumbtmpid'),
					array('previewtmpid', 0, 'previewtmpid'),
					array('posttime', TIMESTAMP)
			), ' onsubmit="return false;"');
		
			$adminhtml->table_header();
			$classids = get_threadclassids($tid);
			$selectcategory = '<select id="catid" name="threads[catid]" class="select t50">';
			$selectcategory .= '<optgroup label="' . adminlang('select_category') . '">';
			$selectcategory .= select_category($chanid, intval($threads['catid']));
			$selectcategory .= "</optgroup></select>\r\n";
			//$topicids = get_topicids($tid);
			$classids = get_threadclassids($tid);
			//$selectcategory .= '<input type="hidden" id="topicidstr" name="topicidstr" value="'.$topicids.'" />';
			$selectcategory .= '<input type="hidden" id="classidstr" name="classidstr" value="'.$classids.'" />';
			//$selectcategory .= '<button class="button" type="button" onclick="openTopicWindow();">' . adminlang('select_topical') . '</button>';
			$adminhtml->table_td(array(
					array('soft_category', FALSE, 'width="10%" noWrap="noWrap"', '', TRUE),
					array($selectcategory, TRUE, 'width="42%"'),
			));
			$adminhtml->table_td(array(
					array('soft_title', FALSE, 'width="20%"', '', TRUE),
					array($adminhtml->textinput('softinfo[softname]', $threads['softname'], 70, 'softname'), TRUE),
			));
			$adminhtml->table_td(array(
					array('soft_softversion', FALSE, '', '', TRUE),
					array($adminhtml->textinput('softinfo[softversion]', $threads['softversion'], 70, 'version'), TRUE),
			));
		
			$adminhtml->table_td(array(
					array('soft_subtitle', FALSE, '', '', TRUE),
					array($adminhtml->textinput('softinfo[subtitle]', $threads['subtitle'], 70), TRUE),
			));
		
			$adminhtml->table_td(array(
					array('highlight', FALSE, '', '', TRUE),
					array($adminhtml->highlight_select($threads['highlight']), TRUE),
			));
		
			$adminhtml->table_td(array(
					array('soft_runsystem', FALSE, '', '', TRUE),
					array($adminhtml->textinput('softinfo[runsystem]', $threads['runsystem'], 50, 'runsystemtext') . select_softinfo_option('runsystem'), TRUE),
			));
			$adminhtml->table_td(array(
					array('soft_demourl', FALSE, '', '', TRUE),
					array($adminhtml->textinput('softinfo[demourl]', $threads['demourl'], 50, null, null, 'soft_demourl_comments'), TRUE),
			));
		
			$adminhtml->table_td(array(
					array('soft_properties', FALSE, '', '', TRUE),
					array(select_properties('softlang', $threads['softlang']) .
							select_properties('softtype', $threads['softtype']) .
							select_properties('license', $threads['license']) .
							select_softstar('star', intval($threads['star'])), TRUE),
			));
			$adminhtml->table_td(array(
					array('soft_softsize', FALSE, '', '', TRUE),
					array($adminhtml->textinput('softinfo[softsize]', intval($threads['softsize']), 15) . ' ' .
							$adminhtml->radio(array('KB', 'MB', 'GB'), 'sizeunit', 0, FALSE), TRUE),
			));
			$adminhtml->table_td(array(
					array('soft_homepage', FALSE, '', '', TRUE),
					array($adminhtml->textinput('softinfo[homepage]', $threads['homepage'], 50), TRUE)
			));
			$adminhtml->table_td(array(
					array('soft_company', FALSE, '', '', TRUE),
					array($adminhtml->textinput('softinfo[company]', $threads['company'], 50), TRUE)
			));
			$adminhtml->table_td(array(
					array('soft_tags', FALSE, 'noWrap="noWrap"', '', TRUE),
					array($adminhtml->textinput('contents[tags]', $contents['tags'], 70, 'tagstring'), TRUE, 'colspan="3"')
			));
			$appsetting = $adminhtml->checkbox(
					array('topline','focus', 'bancomment', 'audit', 'locked', 'updatenow'),
					array('threads[topline]', 'threads[focus]', 'threads[bancomment]', 'threads[status]', 'threads[locked]', 'updatenow'),
					array(intval($threads['topline']), intval($threads['focus']), intval($threads['bancomment']), intval($threads['status']), intval($threads['locked']), 0));
			$adminhtml->table_td(array(
					array('appsetting', FALSE, '', '', TRUE),
					array($appsetting, TRUE)
			));
			$adminhtml->table_td(array(
					array('digest_option', FALSE, '', '', TRUE),
					array($adminhtml->radio('digests', 'threads[digest]', intval($threads['digest'])), TRUE)
			));
			$btnsubmit = $adminhtml->submit_button();// . '<a href="#" id="closewindow">close</a>';
			$adminhtml->table_td(array(
					array($btnsubmit, TRUE, 'align="center" colspan="2"')
			), NULL, FALSE, NULL, NULL, FALSE);
			$adminhtml->table_end('</form>');
			echo <<<EOT
<script type="text/javascript">
	jQuery.noConflict();
	jQuery(function($) {
		$('html').css('padding', '0');
		$('#crumbnav, .tab-box').hide();
		$('#closewindow').click(function(){
			alert('ok!');
			$('#fbox_dialog_quickedit', window.parent.document).remove();
		});
		$('button.btnsubmit').click(function() {
			var query = $(this).closest('form').find('input,select').serialize();
			var softname = $('#softname').val();
			var version = $('#version').val();
			var tid = $('#tid').val();
			$.post('?m=soft&action=savequickedit', query,
				function(result) {
					if(result.status) {
						$('#t'+tid, window.parent.document).text(softname+' '+version);
						$('#fbox_dialog_quickedit', window.parent.document).remove();
					} else {
						alert('error!');
					}
				},
				'json'
			);
		});
	});
</script>
EOT;
			exit;
	} else {
		$threads['runsystem'] = phpcom::$G['cache']['channel']['defrunsystem'];
		$threads['status'] = 1;
		$threads['testsoft'] = '';
	}
	echo '<script type="text/javascript">';
	echo "phpcom.chanid=$chanid;phpcom.tid=$tid;";
	echo '</script>';
	echo '<script src="misc/js/calendar.js" type="text/javascript"></script>';
	if($action != 'edit'){
	echo <<<EOT
<script type="text/javascript">
	jQuery.noConflict();
	jQuery(function($) {
    var input = $('#softname');
    var position = input.position();
    $('body').append('<div id="pop_div">test</div>');
    var pop = $('#pop_div');
    pop.css({'position':'absolute', 'width':input.width()+'px', 'border':'1px solid #999', 'background':'#fff', 'left':position.left+'px', 'top':(position.top+input.height()+2)+'px'}).hide();

    input.on('keyup focus', function() {
        var key = $(this).val().replace(/^\s\s*/, '').replace(/\s\s*$/, '');;
        if (key.length > 0) {
            $.get('?m=ajax&action=softlist&key='+key,'xml')
            .success(function(xml){
                var result = $(xml).find('root').text();
                if(result.length > 0) {
                    pop.empty().append('<ul>'+result+'</ul>').show();
                    pop.find('li').css({'line-height':'25px', 'padding':'0px 5px'});
                } else {
                    pop.hide();
                }
            });
        } else {
            pop.hide();
        }
    });
    
    $('body').click(function(e){
        if(!(e.target == input[0] || $(e.target).closest('#pop_div').length != 0)) {
            pop.hide();
        }
    });
});
</script>
EOT;
	}
	$adminhtml->editor_scritp('soft');
	$adminhtml->form("m=soft&action=$action&chanid=$chanid", array(
			array('channelid', $chanid), array('tid', $tid),
			array('thumbtmpid', 0, 'thumbtmpid'),
			array('previewtmpid', 0, 'previewtmpid'),
			array('posttime', TIMESTAMP)
	), ' onsubmit="return formSubmit(this);" autocomplete="off"');
	$adminhtml->table_header('soft_' . $action, 4);
	$selectcategory = '<select id="catid" name="threads[catid]" class="select t50">';
	$selectcategory .= '<optgroup label="' . adminlang('select_category') . '">';
	$selectcategory .= select_category($chanid, intval($threads['catid']));
	$selectcategory .= "</optgroup></select>\r\n";
	$classids = get_threadclassids($tid);
	$selectcategory .= '<input type="hidden" id="classidstr" name="classidstr" value="'.$classids.'" />';
	$select_threadclass = '<button class="button" type="button" onclick="openThreadClassWindow('.$chanid.');">' . adminlang('select_threadclass') . '</button>';
	$adminhtml->table_td(array(
			array('soft_category', FALSE, 'width="10%" noWrap="noWrap"', '', TRUE),
			array($selectcategory, TRUE, 'width="42%"'),
			array('select_threadclass', FALSE, 'width="10%" noWrap="noWrap"', '', TRUE),
			array($select_threadclass, TRUE, 'width="38%"')
	));
	$adminhtml->table_td(array(
			array('soft_title', FALSE, '', '', TRUE),
			array($adminhtml->textinput('softinfo[softname]', $threads['softname'], 70, 'softname', ':autoComplete="off"'), TRUE),
			array('soft_softversion', FALSE, '', '', TRUE),
			array($adminhtml->textinput('softinfo[softversion]', $threads['softversion'], 50), TRUE),
	));
	$adminhtml->table_td(array(
			array('soft_subtitle', FALSE, '', '', TRUE),
			array($adminhtml->textinput('softinfo[subtitle]', $threads['subtitle'], 70), TRUE),
			array('highlight', FALSE, '', '', TRUE),
			array($adminhtml->highlight_select($threads['highlight']), TRUE)
	));
	$adminhtml->table_td(array(
			array('soft_runsystem', FALSE, '', '', TRUE),
			array($adminhtml->textinput('softinfo[runsystem]', $threads['runsystem'], 50, 'runsystemtext') . select_softinfo_option('runsystem'), TRUE),
			array('soft_demourl', FALSE, '', '', TRUE),
			array($adminhtml->textinput('softinfo[demourl]', $threads['demourl'], 50, null, null, 'soft_demourl_comments'), TRUE),
	));
	$btnmoreinfo = ' <button class="button" type="button" onclick="hideDisplay(\'contactbody\')">' . adminlang('soft_more_info') . '</button>';
	$adminhtml->table_td(array(
			array('soft_properties', FALSE, '', '', TRUE),
			array(select_properties('softlang', $threads['softlang']) .
					select_properties('softtype', $threads['softtype']) .
					select_properties('license', $threads['license']) .
					select_softstar('star', intval($threads['star'])), TRUE),
			array('soft_release', FALSE, '', '', TRUE),
			array($adminhtml->textinput('softinfo[release]', $threads['release'], 15, 'softinfo_release', 'showcalendar(this.id)', 'soft_release_comments') . $btnmoreinfo, TRUE)
	));
	echo '<tbody id="contactbody" style="display:none">';
	$adminhtml->table_td(array(
			array('soft_testsoft', FALSE, '', '', TRUE),
			array(ThreadUtils::getTestsoftCheckbox($threads['testsoft']), TRUE, 'colspan="3"')
	));
	$adminhtml->table_td(array(
			array('soft_contact', FALSE, '', '', TRUE),
			array($adminhtml->textinput('softinfo[contact]', $threads['contact'], 70, null, null, 'soft_contact_comments'), TRUE),
			array('soft_author', FALSE, '', '', TRUE),
			array($adminhtml->textinput('softinfo[author]', $threads['author'], 50, null, null, 'soft_author_comments'), TRUE)
	));
	echo '</tbody>';
	$adminhtml->table_td(array(
			array('soft_softsize', FALSE, '', '', TRUE),
			array($adminhtml->textinput('softinfo[softsize]', intval($threads['softsize']), 15) . ' ' .
					$adminhtml->radio(array('KB', 'MB', 'GB'), 'sizeunit', 0, FALSE), TRUE),
			array('soft_homepage', FALSE, '', '', TRUE),
			array($adminhtml->textinput('softinfo[homepage]', $threads['homepage'], 50), TRUE)
	));
	$adminhtml->table_td(array(
			array('soft_checksum', FALSE, '', '', TRUE),
			array($adminhtml->textinput('softinfo[checksum]', $threads['checksum'], 70), TRUE),
			array('soft_company', FALSE, '', '', TRUE),
			array($adminhtml->textinput('softinfo[company]', $threads['company'], 50), TRUE)
	));
	$btntags = '<button class="button" type="button" onclick="insert_tags()">' . adminlang('intags') . '</button>';
	$btntags .= "<button class=\"button\" type=\"button\" onclick=\"uploadingWindow('threadimage',null,$chanid,$tid);\"> " . adminlang('imageupload') . " </button>";
	$btntags .= '<button class="button" type="button" onclick="hideDisplay(\'summarybody\')">' . adminlang('showsummary') . '</button>';
	if($action == 'edit'){
		$btntags .= "<button class=\"button\" type=\"button\" onclick=\"location.href='?m=attachment&chanid=$chanid&tid=$tid'\">" . adminlang('attachment') . '</button>';
	}
	$adminhtml->table_td(array(
			array('soft_tags', FALSE, 'noWrap="noWrap"', '', TRUE),
			array($adminhtml->textinput('contents[tags]', $contents['tags'], 70, 'tagstring') . ' ' . $btntags, TRUE, 'colspan="3"')
	));
	echo '<tbody id="summarybody" style="display:none">';
	$adminhtml->table_td(array(
			array('soft_keyword', FALSE, '', '', TRUE),
			array($adminhtml->textinput('contents[keyword]', $contents['keyword'], 70), TRUE),
			array('keyword_comments', FALSE, 'colspan="2"', '', 'tips'),
	));
	$adminhtml->textarea('soft_summary', $threads['summary'], 'softinfo[summary]', 'summary_contents', 3);
	$adminhtml->table_td(array(
			array('soft_htmlname', FALSE, 'noWrap="noWrap"', '', TRUE),
			array($adminhtml->textinput('threads[htmlname]', $threads['htmlname'], 70), TRUE),
			array('soft_htmlname_comments', FALSE, 'colspan="2"', '', 'tips'),
	));
	$adminhtml->table_td(array(
			array('soft_url', FALSE, '', '', TRUE),
			array($adminhtml->textinput('threads[url]', $threads['url'], 70), TRUE),
			array('soft_url_comments', FALSE, 'colspan="2"', '', 'tips'),
	));
	echo '</tbody>';
	$adminhtml->editor_content('soft_content', $contents['content'], 'contents[content]', 'editor_content', 3);

	$adminhtml->table_td(array(
			array('soft_softdown_num', FALSE, '', '', TRUE),
			array(select_down_num() . ' ' . adminlang('soft_softdown_comments'), TRUE, 'colspan="3" noWrap="noWrap"')
	));
	$downserver = get_downserver($chanid);
	$defaultsoftdown = '<div id="defaultsoftdown"><p class="downin">' . select_downserver($downserver, $servid, 'add');
	$defaultsoftdown .= $adminhtml->textinput('softdown[downurl][]', '', 60, '', '', 'soft_downurl_title') . " ";
	$defaultsoftdown .= $adminhtml->textinput('softdown[dname][]', '', 20, '', '', 'soft_dname_title');
	$defaultsoftdown .= '</p></div><div id="softdowndiv"></div>';
	$adminhtml->table_td(array(
			array('soft_softdown', FALSE, '', '', TRUE),
			array(get_softdown_edit($adminhtml, $chanid, $tid, $action, $downserver) . $defaultsoftdown, TRUE, 'colspan="3" noWrap="noWrap"')
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
	/*$adminhtml->table_td(array(
			array('permission_setting', FALSE, '', '', TRUE),
			array(usergroup_checkbox($threadfields['groupids'], $threadfields['credits'], true), TRUE, 'colspan="3"')
	));*/
	$btnsubmit = $adminhtml->submit_button();
	$adminhtml->table_td(array(
			array($btnsubmit, TRUE, 'align="center" colspan="4"')
	), NULL, FALSE, NULL, NULL, FALSE);
	$adminhtml->table_end('</form>');
	$uid = $threads['uid'] ? $threads['uid'] : phpcom::$G['uid'];
	include loadlibfile('uploadattach', 'inc/common');
} else {
	$tid = intval(phpcom::$G['gp_tid']);
	$threads = striptags(phpcom::$G['gp_threads']);
	$softinfo = striptags(phpcom::$G['gp_softinfo']);
	$threadfields = phpcom::$G['gp_threadfields'];
	$contents = phpcom::$G['gp_contents'];
	$uid = intval(phpcom::$G['uid']);
	$threads['title'] = trim($softinfo['softname'] . ' ' . $softinfo['softversion']);

	$threadfields['groupids'] = '';
	if(isset(phpcom::$G['gp_usergroupids'])){
		$threadfields['groupids'] = trim(implodeids(phpcom::$G['gp_usergroupids'], ','), "'");
	}
	$uid = phpcom::$G['uid'];
	if (empty($softinfo['softname'])) {
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
	$softinfo['homepage'] = checkurlhttp($softinfo['homepage']);
	$softsize = floatval($softinfo['softsize']);
	$sizeunit = intval(phpcom::$G['gp_sizeunit']);
	if ($sizeunit === 1) {
		$softsize *= 1024;
	} elseif ($sizeunit === 2) {
		$softsize *= 1048576;
	}
	$softinfo['softsize'] = intval($softsize);
	$testsoft = isset(phpcom::$G['gp_testsoft']) ? striptags(phpcom::$G['gp_testsoft']) : null;
	$softinfo['testsoft'] = ThreadUtils::formatTestsoft($testsoft);

	$post = new DataAccess_PostThread($chanid);
	$softinfo['runsystem'] = $post->formatRunSystem($softinfo['runsystem']);
	$softinfo['star'] = max(1, min(5, $softinfo['star']));
	$isupdate = 0;
	if ($action == 'edit') {
		$softdownedit = isset(phpcom::$G['gp_softdownedit']) ? striptags(phpcom::$G['gp_softdownedit']) : null;
		if (isset(phpcom::$G['gp_updatenow']) && phpcom::$G['gp_updatenow']) {
			$isupdate = $threadfields['isupdate'] = 1;
		}
		if($thread = $post->update($tid, $threads, $threadfields, $softinfo, $contents)){
			$uid = $thread['uid'] ? $thread['uid'] : phpcom::$G['uid'];
			if(!empty($thread['locked']) && $thread['locked'] == 2){
				admin_message('threads_founder_locked');
			}elseif(!empty($thread['locked'])){
				admin_message('threads_locked_edit_denied');
			}
			if ($softdownedit) {
				$softdownid = 0;
				foreach ($softdownedit['downurl'] as $key => $value) {
					if($softdownid = intval($softdownedit['softdownid'][$key])){
						if (empty($value)) {
							DB::delete('soft_download', "id='$softdownid'");
						}else{
							DB::update('soft_download', array(
								'servid' => intval($softdownedit['servid'][$key]),
								'dname' => trim($softdownedit['dname'][$key]),
								'downurl' => trim($value)
							), array('id' => $softdownid));
						}
					}
				}
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
		if($tid = $post->insert($thread, $threads, $threadfields, $softinfo, $contents)){
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
	//Add download
	$softdown = isset(phpcom::$G['gp_softdown']) ? striptags(phpcom::$G['gp_softdown']) : null;
	if ($softdown && $tid) {
		foreach ($softdown['downurl'] as $key => $value) {
			if ($value) {
				DB::insert('soft_download', array(
					'tid' => $tid,
					'servid' => intval($softdown['servid'][$key]),
					'dname' => trim($softdown['dname'][$key]),
					'downurl' => trim($value)
				));
			}
		}
	}
	if(isset(phpcom::$G['gp_attachnew']) || $action == 'edit') {
		$attachnew = isset(phpcom::$G['gp_attachnew']) ? phpcom::$G['gp_attachnew'] : null;
		$post->updateAttach($tid, $attachnew, $uid, $chanid, 'soft');
	}
	if (!empty(phpcom::$G['gp_thumbtmpid']) || !empty(phpcom::$G['gp_previewtmpid'])) {
		$post->updateThreadImage($tid, phpcom::$G['gp_thumbtmpid'], phpcom::$G['gp_previewtmpid'], 'soft');
	}
	if(isset(phpcom::$G['gp_randisrate']) && phpcom::$G['gp_randisrate']){
		$rates = rate_rand(1);
		$voters = $rates['voter'];
		$scores = $rates['total'];
		$voteup = $rates['voteup'];
		$votedown = $rates['votedown'];
		DB::query("UPDATE " . DB::table('thread_field') . " SET 
		voters=voters+'$voters', totalscore=totalscore+'$scores', 
		voteup=voteup+'$voteup', votedown=votedown+'$votedown' 
		WHERE tid='$tid'");
		$hits = $voters + 10;
		DB::query("UPDATE " . DB::table('threads') . " SET hits=hits+'$hits' WHERE tid='$tid'");
	}
	if ($action == 'edit') {
		admin_succeed('threads_edit_succeed', "m=soft&action=list&chanid=$chanid", array('url' => "m=soft&action=edit&chanid=$chanid&tid=$tid"));
	} else {
		admin_succeed('threads_add_succeed', "m=soft&chanid=$chanid", array('url' => "m=soft&action=add&chanid=$chanid&catid=$catid"));
	}
}

?>