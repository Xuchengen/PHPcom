<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : list.php  2012-8-15
 */
!defined('IN_PHPCOM') && exit('Access denied');
$page = phpcom::$G['page'];
$catid = isset(phpcom::$G['gp_catid']) ? intval(phpcom::$G['gp_catid']) : 0;
$rootid = isset(phpcom::$G['gp_rootid']) ? intval(phpcom::$G['gp_rootid']) : 0;

if (!checksubmit(array('btnsubmit', 'submit_button'), 1)) {
	$adminhtml->form("m=video&action=list&chanid=$chanid&page=$page", null, 'onkeydown="return formdown()"');
	$adminhtml->table_header();
	$adminhtml->table_th(array(
			array('', 'width="2%" align="center" noWrap="noWrap"'),
			array('video_title', 'width="53%" class="left" noWrap="noWrap"'),
			array('operator', 'width="10%" class="left"'),
			array('adminoption', 'width="15%"'),
			array('dateline', 'width="10%"'),
			array('count', 'width="10%"')
	));
	$adminhtml->table_td(array(
			array(' ', TRUE, 'colspan="6" align="left" id="showpage"')
	), NULL, FALSE, NULL, NULL, FALSE);
	$word = isset(phpcom::$G['gp_word']) ? trim(phpcom::$G['gp_word']) : '';
    $condition = $status == 2 ? "t.status>='0'" : "t.status='$status'";
	$queryurl = '';
	if ($catid) {
		$queryurl = "&catid=$catid&status=$status";
		$condition .= " AND t.catid=$catid";
	}elseif($rootid){
		$queryurl = "&rootid=$rootid&status=$status";
		$condition .= " AND t.rootid=$rootid";
	}else{
		$condition .= " AND t.chanid='$chanid'";
	}
	if ($action == 'search' && $word) {
		$word = str_replace('_', '\_', $word);
		if(1 === preg_match('#^([0-9]+)(\.html)?$#i', $word, $matches)){
			$condition .= " AND t.tid='$matches[1]'";
		}elseif(preg_match('#^(http:|https:|www\.)([0-9A-Za-z_\-\.\/]+)\/([0-9]+)(\.html|\/)?$#i', $word, $matches)){
			$condition .= " AND t.tid='$matches[3]'";
		}elseif($word{0} == '!' || $word{0} == '^'){
			$condition .= " AND t.title LIKE '%". substr($word, 1) ."%'";
		}else{
			$condition .= " AND t.title LIKE '%$word%'";
		}
		$queryurl = implodeurl(array('action' => 'search', 'word' => $word), '&');
	}

	$todaytime = strtotime(fmdate(TIMESTAMP, 'Ymd'));
	$totalrec = isset(phpcom::$G['gp_count']) ? intval(phpcom::$G['gp_count']) : 0;
	!$totalrec && $totalrec = DB::result_first("SELECT COUNT(*) FROM " . DB::table('threads') . " t WHERE $condition");
	$pagesize = intval(phpcom::$config['admincp']['pagesize']);
	$pagecount = @ceil($totalrec / $pagesize);
	$pagenow = max(1, min($pagecount, intval($page)));
	$pagestart = floor(($pagenow - 1) * $pagesize);
	$sql = DB::buildlimit("SELECT t.*,c.basic,c.catname,c.codename,c.prefixurl,c.prefix,c.color,v.director,v.version,v.editor 
			FROM " . DB::table('threads') . " t
			LEFT JOIN " . DB::table('category') . " c USING(catid)
			LEFT JOIN " . DB::table('video_thread') . " v USING(tid)
			WHERE $condition ORDER BY t.dateline DESC", $pagesize, $pagestart);
	$query = DB::query($sql);
	while ($row = DB::fetch_array($query)) {
		$tid = $row['tid'];
		$edit = $adminhtml->edit_word('edit', "m=video&action=edit&chanid=$chanid&tid=$tid", ' | ');
		$edit .= $adminhtml->edit_word('attachment', "m=attachment&chanid=$chanid&tid=$tid");

		$row['color'] = $row['color'] ? ' style="color:' . $row['color'] . '"' : '';
		if ($row['highlight']) {
			$string = sprintf('%02d', $row['highlight']);
			$row['highlight'] = ' style="';
			$row['highlight'] .= $string[0] ? phpcom::$setting['fontvalue'][$string[0]] : '';
			$row['highlight'] .= $string[1] ? 'color: ' . phpcom::$setting['colorvalue'][$string[1]] : '';
			$row['highlight'] .= '"';
		} else {
			$row['highlight'] = '';
		}
		$row['icons'] = 'txt.gif';
		if ($row['image']) {
			$row['icons'] = 'thumb.gif';
		}elseif ($row['attached']) {
			$row['icons'] = 'pic.gif';
		}
		if ($row['polled']) {
			$row['icons'] = 'vote.gif';
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
		if ($row['istop']) {
			$row['icons'] = 'pin.gif';
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
		$row['auditicon'] = $row['status'] == 1 ? '' : '<a href="?m=video&action=audit&tid='.$tid.'&chanid='.$chanid.'"><img src="misc/images/icons/audit.gif" /></a>';
		switch ($row['digest']) {
			case 1: $row['digest'] = '<img src="misc/images/icons/digest.gif" />'; break;
			case 2: $row['digest'] = '<img src="misc/images/icons/recommend.gif" />'; break;
			case 3: $row['digest'] = '<img src="misc/images/icons/very.gif" />'; break;
			case 4: $row['digest'] = '<img src="misc/images/icons/cool.gif" />'; break;
			case 5: $row['digest'] = '<img src="misc/images/icons/green.gif" />'; break;
			default: $row['digest'] = ''; break;
		}
		$adminhtml->table_td(array(
				array('<input type="checkbox" class="checkbox" name="checkboxes[]" value="' . $tid . '" />', TRUE),
				array('<img src="misc/images/icons/' . $row['icons'] . '" /> <a class="lst" href="?m=video&action=list&chanid=' . $chanid . '&catid=' . $row['catid'] . '"' . $row['color'] . '>' . $row['catname'] . '</a>
					&#8226; <a class="lst" href="' . $row['url'] .'" target="_blank"'. $row['highlight'] . '>' . trim($row['title'] . ' ' . $row['version']) . '</a> ' . $row['digest'] . $row['focus'] . $row['topline'] . $row['auditicon'] . $row['locked'], TRUE),
				array('<span class="c2">' . $row['editor'] . '</span>', TRUE, 'noWrap="noWrap"'),
				array($edit, TRUE, 'align="center" noWrap="noWrap"'),
				array('<em class="f10">' . fmdate($row['dateline'], 'dt', 'd') . '</em>', TRUE, 'align="center" noWrap="noWrap"'),
				array('<em class="f10">' . $row['hits'] . '</em>', TRUE, 'align="center" noWrap="noWrap"')
		));
	}
	$adminhtml->table_td(array(
			array($adminhtml->checkall('checkall', 'chkall', 'checkboxes') . ' ' .
					$adminhtml->radio(adminlang('thread_operation_option'), 'operation', 'digest', false) . ' ' .
					$adminhtml->submit_button('submit', 'btnsubmit', 'button'), TRUE, 'colspan="6"')
	), NULL, FALSE, NULL, NULL, FALSE);
	$showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=video&action=list&chanid=$chanid$queryurl") . '</var>';
	$adminhtml->table_td(array(
			array($showpage, TRUE, 'colspan="6" align="right" id="pagecode"')
	), NULL, FALSE, NULL, NULL, FALSE);
	$adminhtml->table_end('</form>');
	$adminhtml->showpagescript();
} else {
	if(!phpcom_admincp::permission('thread_delete')){
		admin_message('action_delete_denied');
	}
	$returnurl = "m=video&action=list&chanid=$chanid&page=$page";
	$operation = isset(phpcom::$G['gp_operation']) ? trim((string)phpcom::$G['gp_operation']) : null;
	$checkboxes = isset(phpcom::$G['gp_checkboxes']) ? phpcom::$G['gp_checkboxes'] : null;
	if(empty($operation) || empty($checkboxes)){
		admin_message('thread_bulk_operation_invalid');
	}
	if(strcasecmp($operation, 'digest') === 0){
		if($tids = implodeids($checkboxes)){
			DB::update('threads', array('digest' => 1), "tid IN($tids)");
		}
		admin_succeed('thread_bulk_operation_succeed', $returnurl);
	}elseif(strcasecmp($operation, 'recommend') === 0){
		if($tids = implodeids($checkboxes)){
			DB::update('threads', array('digest' => 2), "tid IN($tids)");
		}
		admin_succeed('thread_bulk_operation_succeed', $returnurl);
	}elseif(strcasecmp($operation, 'cancel') === 0){
		if($tids = implodeids($checkboxes)){
			DB::update('threads', array('digest' => 0), "tid IN($tids)");
		}
		admin_succeed('thread_bulk_operation_succeed', $returnurl);
	}elseif(strcasecmp($operation, 'delete') === 0){
		$deleted = isset(phpcom::$G['gp_delete']) ? trim((string)phpcom::$G['gp_delete']) : null;
		if(empty($deleted)){
			$extra = '<input type="hidden" name="operation" value="delete" />';
			foreach ($checkboxes as $tid) {
				$extra .= "<input type=\"hidden\" name=\"checkboxes[]\" value=\"$tid\" />";
			}
			admin_showmessage('thread_delete_message', null, array(
			'form' => TRUE,
			'submit' => TRUE,
			'cancel' => TRUE,
			'action' => "?$returnurl&submit=yes&delete=yes"
			), $extra);
		}else{
			include_once loadlibfile('delete');
			if (delete_video_thread($checkboxes)) {
				phpcom_cache::updater('syscount', $chanid);
				admin_succeed('thread_bulk_delete_succeed', $returnurl);
			} else {
				admin_message('threads_delete_failed', $returnurl);
			}
		}
	}elseif(strcasecmp($operation, 'move') === 0){
		$moved = isset(phpcom::$G['gp_move']) ? trim((string)phpcom::$G['gp_move']) : null;
		$channelid = isset(phpcom::$G['gp_channelid']) ? intval((string)phpcom::$G['gp_channelid']) : $chanid;
		if(empty($moved)){
			$adminhtml->form("$returnurl&submit=yes&move=yes", array(
					array('operation', 'move'),
					array('posttime', TIMESTAMP)
			), 'name="from_move"');
			foreach ($checkboxes as $tid) {
				echo "<input type=\"hidden\" name=\"checkboxes[]\" value=\"$tid\" />";
			}
			$adminhtml->table_header('bulk_move');
			$adminhtml->table_td(array(
					array("bulk_move_tips", NULL, 'colspan="3"')
			), NULL, FALSE, NULL, NULL, FALSE);
			$channel_select = '<select name="channelid" class="select t50" size="1" style="width:280px;"';
			$channel_select .= " onchange=\"from_move.action='?$returnurl&submit=yes';from_move.submit()\">";
			$sql = "SELECT channelid, channelname FROM " . DB::table('channel') . " WHERE modules='video'";
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				$channel_select .= "<option value=\"{$row['channelid']}\"";
				$channel_select .= ( $row['channelid'] == $channelid) ? ' SELECTED' : '';
				$channel_select .= ">== {$row['channelname']} ==</option>";
			}
			$channel_select .= '</select>';
			$adminhtml->table_td(array(
					array("select_channel", FALSE, '', '', TRUE),
					array($channel_select, TRUE),
					array("move_channel_tips", FALSE, '', '', 'tips')
			));
			$category_select = '<select name="catid" class="select t50" size="20" style="width:280px;height:300px;">';
			$category_select .= '<option value="0">-=' . adminlang("select_category") . '=-</option>';
			$category_select .= category_select_option($channelid);
			$category_select .= '</select>';
			$adminhtml->table_td(array(
					array("select_category", FALSE, '', '', TRUE),
					array($category_select, TRUE),
					array("move_category_tips", FALSE, '', '', 'tips')
			));

			$btnsubmit = $adminhtml->submit_button();
			$adminhtml->table_td(array(
					array(' &nbsp;', TRUE),
					array($btnsubmit, TRUE, 'colspan="2"')
			), NULL, FALSE, NULL, NULL, FALSE);
			$adminhtml->table_end('</form>');
		}else{
			$catid = isset(phpcom::$G['gp_catid']) ? intval((string)phpcom::$G['gp_catid']) : 0;
			if($rootid = get_rootid($catid, $channelid)){
				$num = $none = 0;
				foreach ($checkboxes as $tid){
					if(threadmove($tid, $catid, $rootid, $channelid, 'video')){
						$num++;
					}else{
						$none++;
					}
				}
				admin_succeed('thread_bulk_move_succeed', $returnurl, array('num' => $num, 'none' => $none));
			}else{
				admin_message('thread_bulk_operation_invalid');
			}
		}
	}else{
		admin_message('thread_bulk_operation_invalid');
	}
}
?>