<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : threads.php  2013-4-15
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';

$chanid = isset(phpcom::$G['gp_chanid']) ? intval(phpcom::$G['gp_chanid']) : 0;
$channelid = isset(phpcom::$G['gp_cid']) ? intval(phpcom::$G['gp_cid']) : 0;
$uid = isset(phpcom::$G['gp_uid']) ? intval(phpcom::$G['gp_uid']) : 0;
$current = $action == 'audit' ? 'menu_threads_audit' : '';

$navarray = array(
		array('title' => 'menu_threads', 'url' => "?m=threads&chanid=$chanid", 'name' => 'first'),
		array('title' => 'menu_threads_audit', 'url' => "?m=threads&action=audit&chanid=$chanid&uid=$uid", 'name' => 'audit'),
		array('title' => 'menu_threads_my', 'url' => "?m=threads&chanid=$chanid&uid=" . phpcom::$G['uid'], 'name' => 'my'),
);

admin_header('menu_threads', $current);
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('topic');
$adminhtml->navtabs($navarray, $action == 'audit' ? 'audit' : 'first', 'nav_tabs', 'threads');

if($action == "move"){
	if (!checksubmit(array('btnsubmit', 'submit'))) {
		
	}else{
		
	}
}elseif($action == 'merge'){
	if (!checksubmit(array('btnsubmit', 'submit'))) {
	
	}else{
	
	}
}elseif($action == 'setting'){
	if (!checksubmit(array('btnsubmit', 'submit'))) {
	
	}else{
	
	}
}elseif($action == 'del'){
	if (!checksubmit(array('btnsubmit', 'submit'))) {
	
	}else{
	
	}
}elseif($action == 'comment'){
	$comments = isset(phpcom::$G['gp_comments']) ? phpcom::$G['gp_comments'] : null;
	$tid = isset(phpcom::$G['gp_tid']) ? intval(phpcom::$G['gp_tid']) : 0;
	if (!$thread = DB::fetch_first("SELECT tid, chanid, uid, bancomment, comments, status 
			FROM " . DB::table('threads') . " WHERE tid='$tid'")) {
			admin_message('undefined_action');
	}
	if (!checksubmit(array('btnsubmit', 'submit'))) {
		include loadlibfile('transip');
		$url = geturl('comment', array('tid' => $tid, 'page' => 1), phpcom::$G['instdir']);
		$tableName = DB::table('session');
		$userip = DB::result_first(
				"SELECT s1.ip\n" .
				"FROM $tableName AS s1\n" .
				"JOIN ( SELECT ROUND(RAND() * (\n" .
				"	(SELECT MAX(sessionid) FROM $tableName) - (SELECT MIN(sessionid) FROM $tableName)\n" .
				"	) + (SELECT MIN(sessionid) FROM $tableName)) AS sessionid) AS s2\n" .
				"	ON s1.sessionid >= s2.sessionid\n" .
				"ORDER BY s1.sessionid\n" .
				"LIMIT 1"
		);
		$userip = empty($userip) ? phpcom::$G['clientip'] : $userip;
		$adminhtml->form("m=threads&action=comment&tid=$tid");
		$adminhtml->table_header('comment_post');
		$adminhtml->table_td(array(
				array('comment_author', FALSE, '', '', TRUE),
				array('<input class="input t60" size="60" name="comments[author]" type="text" value="guest" />', TRUE)
		));
		$adminhtml->table_td(array(
				array('comment_userip', FALSE, '', '', TRUE),
				array('<input class="input t30" size="30" name="comments[userip]" type="text" value="' . htmlcharsencode($userip) . '" /> (<span class="c2">' . translateip($userip) . "</span>)", TRUE)
		));
		$adminhtml->table_td(array(
				array('comment_dateline', FALSE, '', '', TRUE),
				array('<input class="input t30" size="30" name="comments[dateline]" type="text" value="' . fmdate(time(), 'Y-m-d H:i:s') . '" />', TRUE)
		));
		$adminhtml->table_td(array(
				array('comment_content', FALSE, '', '', TRUE),
				array('<textarea class="textarea t100" rows="9" cols="100" name="comments[content]"></textarea>', TRUE)
		));
		$adminhtml->table_td(array(
				array('comment_vote_up_down', FALSE, '', '', TRUE),
				array('<input class="input t10" name="comments[voteup]" type="text" value="0" />
						<input class="input t10" name="comments[votedown]" type="text" value="0" />', TRUE)
		));
		$btnsubmit = $adminhtml->submit_button();
		$adminhtml->table_td(array(
				array($btnsubmit, TRUE, 'align="center" colspan="2"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
		echo <<<ETO
<script type="text/javascript">
jQuery.noConflict();
jQuery(function($) {
	$("html").css("padding", "0");
	$('#crumbnav, .tab-box, #bottomtable').hide();
});
</script>
ETO;
	}else{
		$clientip = empty($comments['userip']) ? phpcom::$G['clientip'] : trim($comments['userip']);
		if(!$clientip = check_clientip($clientip)){
			$clientip = phpcom::$G['clientip'];
		}
		
		$cBody = isset(phpcom::$G['gp_comments']) ? phpcom::$G['gp_comments'] : null;
		$tid = isset(phpcom::$G['gp_tid']) ? phpcom::$G['gp_tid'] : 0;
		if($cBody && $tid) {
			$comments = array(
					'tid'		=> $tid,
					'uid'		=> 0,
					'username'	=> $cBody['author'],
					'lastdate'	=> strtotime($cBody['dateline']),
					'ip'		=> $cBody['userip'],
					'num'		=> 0
			);
			$cid = DB::insert('comments', $comments, true);
		
			$cBody['commentid']	= $cid;
			$cBody['first']		= 1;
			$cBody['authorid']	= 0;
			$cBody['dateline']	= $comments['lastdate'];
			$cBody['status']	= 1;
		
			DB::insert('comment_body', $cBody);
			DB::query("UPDATE " . DB::table('threads') . " SET comments=comments+1 WHERE tid='$tid'");
		}
		
		ob_end_clean();
		ob_start();
		ob_end_clean();
		echo '<script type="text/javascript">window.parent.document.getElementById(\'fbox_dialog_comment\').style.display = \'none\';window.parent.location.reload(true);</script>';
		exit();
	}
}else{
	if (!checksubmit(array('btnsubmit', 'submit'))) {
		$adminhtml->table_header();
		$adminhtml->table_th(array(
				array('title', 'width="60%" class="left" noWrap="noWrap"'),
				array('adminoption', 'width="15%"'),
				array('comment', 'width="5%" noWrap="noWrap"'),
				array('dateline', 'width="10%"'),
				array('count', 'width="10%"')
		));
		$adminhtml->table_td(array(
				array(' ', TRUE, 'colspan="5" align="left" id="showpage"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$word = isset(phpcom::$G['gp_word']) ? trim(phpcom::$G['gp_word']) : '';
		$catid = isset(phpcom::$G['gp_catid']) ? intval(phpcom::$G['gp_catid']) : 0;
		$rootid = isset(phpcom::$G['gp_rootid']) ? intval(phpcom::$G['gp_rootid']) : 0;
		$condition = $action == 'audit' ? "status='0'" : "status='1'";
		$queryurl = '';
		if ($catid) {
			$queryurl = "&catid=$catid";
			$condition .= " AND catid=$catid";
		}elseif($rootid){
			$queryurl = "&rootid=$rootid";
			$condition .= " AND rootid=$rootid";
		}elseif($chanid){
			$condition .= " AND chanid='$chanid'";
		}
		if ($action == 'search' && $word) {
			$word = str_replace('_', '\_', $word);
			if(preg_match('#^([0-9]+)(\.html)?$#i', $word, $matches)){
				$condition .= " AND tid='$matches[1]'";
			}elseif(preg_match('#^(http:|https:|www\.)([0-9A-Za-z_\-\.\/]+)\/([0-9]+)(\.html|\/)?$#i', $word, $matches)){
				$condition .= " AND tid='$matches[3]'";
			}elseif($word{0} == '!' || $word{0} == '^'){
				$condition .= " AND title LIKE '%". substr($word, 1) ."%'";
			}else{
				$condition .= " AND title LIKE '%$word%'";
			}
			$queryurl = implodeurl(array('action' => 'search', 'word' => $word), '&');
		}elseif($uid){
			$queryurl = "&uid=$uid";
			$condition .= " AND uid='$uid'";
		}
		$todaytime = strtotime(fmdate(TIMESTAMP, 'Ymd'));
		$page = phpcom::$G['page'];
		$count = isset(phpcom::$G['gp_count']) ? intval(phpcom::$G['gp_count']) : 0;
		!$count && $count = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " WHERE $condition");
		$pagesize = intval(phpcom::$config['admincp']['pagesize']);
		$pagecount = @ceil($count / $pagesize);
		$pagenow = max(1, min($pagecount, intval($page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$pagesql = "INNER JOIN (" . DB::buildlimit("SELECT tid FROM " . DB::table('threads') . " WHERE $condition ORDER BY dateline DESC", $pagesize, $pagestart) . ") AS p USING(tid)";
		$sql = "SELECT t.*,c.basic,c.catname,c.codename,c.prefixurl,c.prefix,f.attachment,f.attachimg
	    		FROM " . DB::table('threads') . " t $pagesql
		    	LEFT JOIN " . DB::table('category') . " c USING(catid)
				LEFT JOIN " . DB::table('thread_image') . " f USING(tid)";
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$row['highlight'] = threadhighlight($row['highlight']);
			$tid = $row['tid'];
			$channelid = $row['chanid'];
			if(!isset(phpcom::$G['channel'][$channelid]['modules'])) continue;
			$modules = phpcom::$G['channel'][$channelid]['modules'];
			$row['icons'] = 'txt.gif';
			if(!empty($row['attachimg'])){
				$row['icons'] = 'app.gif';
			}elseif ($row['image']) {
				$row['icons'] = 'thumb.gif';
			}elseif ($row['attached']) {
				$row['icons'] = 'pic.gif';
			}
			if ($row['istop']) {
				$row['icons'] = 'pin.gif';
			}elseif ($row['polled']) {
				$row['icons'] = 'vote.gif';
			}
			$row['weeknew'] = TIMESTAMP - 604800 <= $row['dateline'];
			$row['istoday'] = $row['dateline'] > $todaytime ? 1 : 0;
			if ($row['weeknew']) {
				$row['weeknew'] = '<em class="new">New</em>';
			} else {
				$row['weeknew'] = '';
			}
	
			$row['focus'] = $row['focus'] ? '<img src="misc/images/icons/focus.gif" />' : '';
			$row['topline'] = $row['topline'] ? '<img src="misc/images/icons/topline.gif" />' : '';
			$row['locked'] = $row['locked'] ? '<img src="misc/images/icons/locked.gif" />' : '';
			$row['auditicon'] = $row['status'] == 1 ? '' : '<a href="?m=article&action=audit&tid='.$tid.'&chanid='.$chanid.'"><img src="misc/images/icons/audit.gif" /></a>';
			switch ($row['digest']) {
				case 1: $row['digest'] = '<img src="misc/images/icons/digest.gif" />'; break;
				case 2: $row['digest'] = '<img src="misc/images/icons/recommend.gif" />'; break;
				case 3: $row['digest'] = '<img src="misc/images/icons/very.gif" />'; break;
				case 4: $row['digest'] = '<img src="misc/images/icons/cool.gif" />'; break;
				case 5: $row['digest'] = '<img src="misc/images/icons/green.gif" />'; break;
				default: $row['digest'] = ''; break;
			}
			$urlargs = array('chanid' => $row['chanid'], 'catdir' => $row['codename'], 'tid' => $row['tid'],
					'date' => $row['dateline'], 'cid' => $row['catid'], 'catid' => $row['catid'], 'page' => 1);
			$urlargs['prefix'] = empty($row['prefix']) ? '' : trim($row['prefix']);
			$urlargs['name'] = empty($row['htmlname']) ? '' : trim($row['htmlname']);
			if (empty(phpcom::$G['channel'][$row['chanid']]['domain']) && empty($row['prefixurl'])) {
				$row['domain'] = phpcom::$G['instdir'];
			} elseif(empty($row['prefixurl'])) {
				$row['domain'] = phpcom::$G['channel'][$row['chanid']]['domain'] . '/';
			}else{
				$row['domain'] = $row['prefixurl'] . '/';
			}
			if (empty($row['url'])) {
				$row['url'] = geturl('threadview', $urlargs, $row['domain']);
			}else{
				$row['icons'] = 'link.gif';
			}
			$row['viewurl'] = "?m=$modules&action=view&chanid=$channelid&tid=$tid";
			
			$edit = $adminhtml->edit_word('edit', "m=$modules&action=edit&chanid=$channelid&tid=$tid", ' | ');
			$edit .= $adminhtml->del_word('delete', "m=$modules&action=del&chanid=$channelid&tid=$tid");
			$adminhtml->table_td(array(
					array('<a target="_blank" href="' . $row['url'] .'"><img src="misc/images/icons/' . $row['icons'] . '" /></a> <a class="lst" href="?m=threads&chanid=' . $chanid . '&catid=' . $row['catid'] . '">' . $row['catname'] . '</a>
						&#8226; <a target="_blank" class="lst" href="' . $row['url'] .'"'. $row['highlight'] . '>' . $row['title'] . '</a> ' . $row['digest'] . $row['focus'] . $row['topline'] . $row['auditicon'] . $row['locked'], TRUE),
					array($edit, TRUE, 'align="center" noWrap="noWrap"'),
					array('<a href="javascript:void(0);" onclick="openDialog(\'?m=threads&action=comment&tid='.$row['tid'].'\', \'comment\', \'' . adminlang('threads_add_comment') . '\', 750, 360);">' . adminlang('threads_add') . "</a> ({$row['comments']})", TRUE, 'align="center" noWrap="noWrap"'),
					array('<em class="f10">' . fmdate($row['dateline'], 'dt', 'd') . '</em>', TRUE, 'align="center" noWrap="noWrap"'),
					array('<em class="f10">' . $row['hits'] . '</em>', TRUE, 'align="center" noWrap="noWrap"')
			));
		}
		$showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $count, ADMIN_SCRIPT . "?m=threads&action=$action&chanid=$chanid$queryurl") . '</var>';
		$adminhtml->table_td(array(
				array($showpage, TRUE, 'colspan="5" align="right" id="pagecode"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end();
		$adminhtml->showpagescript();
	}else{
		admin_message('undefined_action');
	}
}
admin_footer();
?>