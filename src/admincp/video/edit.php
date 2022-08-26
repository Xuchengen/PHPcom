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
			'htmlname' => '', 'highlight' => 0, 'istop' => 0, 'topline' => 0, 'focus' => 0, 'digest' => 0, 'demourl' => '',
			'polled' => 0, 'hits' => 0, 'dateline' => 0, 'lastdate' => 0, 'credits' => 0, 'bancomment' => 0, 'locked' => 0,
			'status' => 1, 'uid' => phpcom::$G['uid'], 'subtitle' => '', 'summary' => '','tname' => '', 'years' => date('Y'),
			'dialogue' => '', 'version' => '', 'quality' => -1, 'director' => '', 'starring' => '', 'release' => '', 'mins' => 0);
	$contents = array('keyword' => '', 'content' => '', 'tags' => '');
	$threadfields = array('isupdate' => 0, 'voteup' => 0, 'votedown' => 0,
			'credits' => phpcom::$G['cache']['channel']['defaultcredits'],
			'groupids' => phpcom::$G['cache']['channel']['defaultgroupids']);

	if ($action == 'edit' && $tid) {
		$threads = DB::fetch_first("SELECT t.*, a.* FROM " . DB::table('threads') . " t
			 LEFT JOIN " . DB::table('video_thread') . " a USING(tid)
			 WHERE t.tid=$tid");
		$tableindex = $threads['tableindex'];
		if($cont = DB::fetch_first("SELECT * FROM " . DB::table('video_content', $tableindex) . " WHERE tid='$tid'")){
			$contents = $cont;
			$contents['tags'] = get_tagstr($contents['tags']);
		}
		$threadfields = DB::fetch_first("SELECT * FROM " . DB::table('thread_field') . " WHERE tid='$tid'");
		$attachs = Attachment::getAttachlist($tid, 1, 'video');
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
		$country = explode(',', phpcom::$G['cache']['channel']['country']);
		$dialogue = explode(',', phpcom::$G['cache']['channel']['dialogue']);
		$threads['country'] = $country[0];
		$threads['dialogue'] = $dialogue[0];
		$threads['status'] = 1;
	}
	$threads['uid'] = $threads['uid'] ? $threads['uid'] : phpcom::$G['uid'];
	echo '<script type="text/javascript">';
	echo "phpcom.chanid=$chanid;phpcom.tid=$tid;";
	echo '</script>';
	echo '<script src="misc/js/calendar.js" type="text/javascript"></script>';
	$adminhtml->editor_scritp('video');
	$adminhtml->form("m=video&action=$action&chanid=$chanid", array(
			array('channelid', $chanid), array('tid', $tid),
			array('thumbtmpid', 0, 'thumbtmpid'),
			array('previewtmpid', 0, 'previewtmpid'),
			array('uid', $threads['uid']), array('posttime', TIMESTAMP)
	), ' onsubmit="return formSubmit(this);" autocomplete="off"');
	$adminhtml->table_header('video_' . $action, 4);
	$selectcategory = '<select id="catid" name="threads[catid]" class="select t50">';
	$selectcategory .= '<optgroup label="' . adminlang('select_category') . '">';
	$selectcategory .= select_category($chanid, intval($threads['catid']));
	$selectcategory .= '</optgroup></select>';
	$classids = get_threadclassids($tid);
	$selectcategory .= '<input type="hidden" id="classidstr" name="classidstr" value="'.$classids.'" />';
	$selectcategory .= '<button class="button" type="button" onclick="openThreadClassWindow('.$chanid.');">' . adminlang('select_threadclass') . '</button>';
	$adminhtml->table_td(array(
			array('video_category', FALSE, 'width="10%" noWrap="noWrap"', '', TRUE),
			array($selectcategory, TRUE, 'width="42%"'),
			array('highlight', FALSE, 'width="10%" noWrap="noWrap"', '', TRUE),
			array($adminhtml->highlight_select($threads['highlight']), TRUE, 'width="38%"')
	));

	$adminhtml->table_td(array(
			array('video_title', FALSE, '', '', TRUE),
			array($adminhtml->textinput('threads[title]', $threads['title'], 70), TRUE, 'colspan="0"'),
			array('video_version', FALSE, '', '', TRUE),
			array($adminhtml->textinput('videos[version]', $threads['version'], '15', 'versiontext') .
					select_video_version('version', 'versiontext', $threads['version']), TRUE, 'noWrap="noWrap"')
	));
	$adminhtml->table_td(array(
			array('video_subtitle', FALSE, '', '', TRUE),
			array($adminhtml->textinput('videos[subtitle]', $threads['subtitle'], 70), TRUE),
			array('video_demourl', FALSE, '', '', TRUE),
			array($adminhtml->textinput('videos[demourl]', $threads['demourl'], 50, null, null, 'video_demourl_comments'), TRUE)
	));
	$adminhtml->table_td(array(
			array('video_starring', FALSE, '', '', TRUE),
			array($adminhtml->textinput('videos[starring]|video_starring_comments', $threads['starring'], 70) . ' ' .
					'', TRUE, 'colspan="0"'),
			array('video_country_dialogue', FALSE, '', '', TRUE),
			array(select_country_dialogue('country', $threads['country']) .
					select_country_dialogue('dialogue', $threads['dialogue']) . select_video_year($threads['years']), TRUE, 'noWrap="noWrap"')
	));
	$adminhtml->table_td(array(
			array('video_director', FALSE, '', '', TRUE),
			array($adminhtml->textinput('videos[director]', $threads['director'], 30) .
					select_video_quality($threads['quality']), TRUE, 'colspan="0"'),
			array('video_release', FALSE, '', '', TRUE),
			array('<input class="input t10" size="10" id="video_release" onclick="showcalendar(this.id)" name="videos[release]" type="text" value="' . htmlcharsencode(trim($threads['release'])) . '" /> ' .
					' <strong>'. adminlang('video_mins') . '</strong> '. $adminhtml->textinput('videos[mins]|video_mins_comments', intval($threads['mins']), 5), TRUE)
	));
	$addresses = get_play_address($threads['tid']);
	$playerval = phpcom::$G['cache']['channel']['defaultplayer'];
	$i = 0;
	foreach ($addresses as $id => $address){
		if($i == 0){
			echo '<tbody id="play_address_body">';
		}
		$adminhtml->table_td(array(
				array('video_play_address', FALSE, '', '', TRUE),
				array('<p>'.select_video_player($address['playvarname'], $address['playerid']) . ' '.adminlang('video_play_address_add_comments') .
						'<input class="input t60" size="60" name="'.$address['captionvarname'].'" type="text" value="' . htmlcharsencode(trim($address['caption'])) . '" /></p>
						<p><textarea title="'.adminlang('video_play_address_comments').'" rows="5" cols="100" name="'.$address['addrvarname'].
						'" class="textarea" wrap="off" style="width:98%;height:120px;font-size:14px;margin-top:5px;">'.htmlcharsencode($address['address']).'</textarea></p>
						<p></p>', TRUE, 'colspan="3"')
		));
		if($i == 0){
			echo '</tbody>';
		}
		$i++;
	}
	echo '<tbody id="play_address_add"></tbody>';
	$btntags = '<button class="button" type="button" onclick="insert_tags()">' . adminlang('intags') . '</button>';
	$btntags .= "<button class=\"button\" type=\"button\" onclick=\"uploadingWindow('threadimage',null,$chanid,$tid);\"> " . adminlang('imageupload') . " </button>";
	$btntags .= '<button class="button" type="button" onclick="hideDisplay(\'summarybody\')">' . adminlang('showsummary') . '</button>';
	if($action == 'edit'){
		$btntags .= "<button class=\"button\" type=\"button\" onclick=\"location.href='?m=attachment&chanid=$chanid&tid=$tid'\">" . adminlang('attachment') . '</button>';
	}
	$adminhtml->table_td(array(
			array('video_tags', FALSE, 'noWrap="noWrap"', '', TRUE),
			array($adminhtml->textinput('contents[tags]', $contents['tags'], 60, 'tagstring') . ' ' . $btntags, TRUE, 'colspan="3"')
	));
	echo '<tbody id="summarybody" style="display:none">';
	$adminhtml->table_td(array(
			array('video_keyword', FALSE, '', '', TRUE),
			array($adminhtml->textinput('contents[keyword]', $contents['keyword'], 70), TRUE),
			array('keyword_comments', FALSE, 'colspan="2"', '', 'tips')
	));
	$adminhtml->textarea('video_summary', $threads['summary'], 'videos[summary]', 'summary_contents', 3);
	$adminhtml->table_td(array(
			array('video_htmlname', FALSE, 'noWrap="noWrap"', '', TRUE),
			array($adminhtml->textinput('threads[htmlname]', $threads['htmlname'], 70), TRUE),
			array('video_htmlname_comments', FALSE, 'colspan="2"', '', 'tips'),
	));
	$adminhtml->table_td(array(
			array('video_url', FALSE, '', '', TRUE),
			array($adminhtml->textinput('threads[url]', $threads['url'], 70), TRUE),
			array('video_url_comments', FALSE, 'colspan="2"', '', 'tips'),
	));
	echo '</tbody>';
	$adminhtml->editor_content('video_content', $contents['content'], 'contents[content]', 'editor_content', 3);
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
	$threads = striptags(phpcom::$G['gp_threads']);
	$videos = striptags(phpcom::$G['gp_videos']);
	$threadfields = phpcom::$G['gp_threadfields'];
	$contents = phpcom::$G['gp_contents'];
	$uid = intval(phpcom::$G['uid']);
	$aid = 0;

	$threadfields['groupids'] = '';
	if(isset(phpcom::$G['gp_usergroupids'])){
		$threadfields['groupids'] = trim(implodeids(phpcom::$G['gp_usergroupids'], ','), "'");
	}

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
	$isupdate = 0;
	$post = new DataAccess_PostThread($chanid);
	if ($action == 'edit') {
		if (isset(phpcom::$G['gp_updatenow']) && phpcom::$G['gp_updatenow']) {
			$isupdate = $threadfields['isupdate'] = 1;
		}
		if($thread = $post->update($tid, $threads, $threadfields, $videos, $contents)){
			$uid = $thread['uid'] ? $thread['uid'] : phpcom::$G['uid'];
			if(!empty($thread['locked']) && $thread['locked'] == 2){
				admin_message('threads_founder_locked');
			}elseif(!empty($thread['locked'])){
				admin_message('threads_locked_edit_denied');
			}
			$aid = empty($thread['aid']) ? 0 : $thread['aid'];

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
		if($tid = $post->insert($thread, $threads, $threadfields, $videos, $contents)){
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
	//Update and add play address
	if(isset(phpcom::$G['gp_addressnew'])) {
		$addressnew = phpcom::$G['gp_addressnew'];
		$playernew = phpcom::$G['gp_playernew'];
		$captionnew = phpcom::$G['gp_captionnew'];
		foreach ($addressnew as $key => $address){
			if($address = addresstrim($address)){
				DB::insert('video_address', array(
				'tid' => $tid,
				'playerid' => intval($playernew[$key]),
				'caption' => trim(strip_tags($captionnew[$key])),
				'address' => $address)
				);
			}
		}
	}

	if(isset(phpcom::$G['gp_address'])) {
		$addresses = phpcom::$G['gp_address'];
		$players = phpcom::$G['gp_player'];
		$caption = phpcom::$G['gp_caption'];
		foreach($addresses as $id => $address){
			if($address = addresstrim($address)){
				DB::update('video_address', array(
				'playerid' => intval($players[$id]),
				'caption' => trim(strip_tags($caption[$id])),
				'address' => $address
				), "tid='$tid' AND id='$id'");
			}else{
				DB::delete('video_address', "tid='$tid' AND id='$id'");
			}
		}
	}
	
	if($tmp = DB::fetch_first("SELECT id FROM " . DB::table('video_address') . " WHERE tid='$tid' LIMIT 1")){
		if($tmp['id'] != $aid){
			DB::update('video_thread', array('aid' => $tmp['id']), "tid='$tid'");
		}
	}elseif($action == 'edit' && $aid){
		DB::update('video_thread', array('aid' => 0), "tid='$tid'");
	}
	
	if(isset(phpcom::$G['gp_attachnew']) || $action == 'edit') {
		$attachnew = isset(phpcom::$G['gp_attachnew']) ? phpcom::$G['gp_attachnew'] : null;
		$post->updateAttach($tid, $attachnew, $uid, $chanid, 'video');
	}
	if (!empty(phpcom::$G['gp_thumbtmpid']) || !empty(phpcom::$G['gp_previewtmpid'])) {
		$post->updateThreadImage($tid, phpcom::$G['gp_thumbtmpid'], phpcom::$G['gp_previewtmpid'], 'video');
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
		admin_succeed('threads_edit_succeed', "m=video&action=list&chanid=$chanid", array('url' => "m=video&action=edit&chanid=$chanid&tid=$tid"));
	} else {
		admin_succeed('threads_add_succeed', "m=video&chanid=$chanid", array('url' => "m=video&action=add&chanid=$chanid&catid=$catid"));
	}
}

function addresstrim($address){
	if($address = strip_tags(trim($address))){
		$array = explode("\n", $address);
		$data = array();
		foreach($array as $value){
			if($value = trim($value)){
				$data[] = $value;
			}
		}
		return $data ? implode("\n", $data) : '';
	}
	return '';
}

function get_play_address($tid){
	$defaultdata = array(array('id' => 0, 'tid' => 0, 'playerid' => phpcom::$G['cache']['channel']['defaultplayer'], 'caption' => '',
			'address' => '', 'addrvarname' => 'addressnew[]', 'playvarname' => 'playernew[]', 'captionvarname' => 'captionnew[]'));
	if($tid = intval($tid)){
		$data = array();
		$sql = "SELECT * FROM " . DB::table('video_address') . " WHERE tid='$tid' ORDER BY id";
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$row['addrvarname'] = "address[{$row['id']}]";
			$row['playvarname'] = "player[{$row['id']}]";
			$row['captionvarname'] = "caption[{$row['id']}]";
			$data[$row['id']] = $row;
		}
		return $data ? $data : $defaultdata;
	}
	return $defaultdata;
}
?>