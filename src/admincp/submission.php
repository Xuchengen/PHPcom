<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : submission.php  2012-12-14
 */
!defined('IN_ADMINCP') && exit('Access denied');

phpcom::$G['lang']['admin'] = 'misc';
admin_header('menu_submission');
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('topic');

$navarray = array(
		array('title' => 'menu_submission', 'url' => "?m=submission", 'id' => 'submission')
);
$adminhtml->navtabs($navarray, 'submission');
if($action == 'view'){
	$postid = isset(phpcom::$G['gp_postid']) ? intval(phpcom::$G['gp_postid']) : 0;
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		if(!($submission = DB::fetch_first("SELECT * FROM " . DB::table('post_contents') . " WHERE postid='$postid'"))){
			admin_message('undefined_action');
		}
		if(empty($submission['extras']) || !($extras = @unserialize($submission['extras']))){
			admin_message('undefined_action');
		}
		echo '<script type="text/javascript">loadscript("misc/js/shCore.js");';
		echo 'loadscript("misc/js/shLang.js","' . $charset . '");loadcss("misc/css/shCoreDefault.css");</script>';
		$adminhtml->form("m=submission&action=view&postid=$postid");
		$adminhtml->table_header('submission_view');
		$adminhtml->table_td(array(
				array('<font size="4">' . $submission['title'] . '</font>', TRUE, 'align="center" colspan="2"')
		), '', FALSE, '', '', FALSE);
		$dateline = adminlang('date') . ' ' . fmdate($submission['dateline'], 'Y-m-d H:i:s');
		$adminhtml->table_td(array(
				array($dateline . ' ' . $submission['author'], TRUE, 'align="center" colspan="2"')
		), '', FALSE, '', '', FALSE);
		$content = bbcode::bbcode2html($submission['content']);
		$adminhtml->table_td(array(
				array($content, TRUE, 'colspan="2"', '', 'textcontent')
		), '', FALSE, '', '', FALSE);
		
		if(isset($extras['download']) && !empty($extras['download'])){
			$downloads = $extras['download'];
			$download = '<ol style="padding-left:20px">';
			foreach ($downloads as $down){
				$download .= "<li>{$down['dname']} <a href=\"{$down['downurl']}\">{$down['downurl']}</a></li>";
			}
			$download .= "</ol>";
			$adminhtml->table_td(array(
					array('submission_download', FALSE, 'width="100"'),
					array($download, TRUE)
			), '', FALSE, '', '', FALSE);
		}
		
		$subjects = $extras['subject'];
		$subject = '<ol style="padding-left:20px">';
		foreach ($subjects as $value){
			$subject .= "<li>$value</li>";
		}
		$subject .= "</ol>";
		$adminhtml->table_td(array(
				array($subject, TRUE, 'colspan="2"')
		), '', FALSE, '', '', FALSE);
		
		if(!empty($submission['imageurl'])){
			$adminhtml->table_td(array(
					array('submission_imageurl', FALSE, 'width="100"'),
					array('<a href="'. $submission['imageurl'] . '" target="_blank">'. $submission['imageurl'] . '</a>', TRUE)
			), '', FALSE, '', '', FALSE);
		}
		if(!empty($submission['url'])){
			$adminhtml->table_td(array(
					array('submission_updateurl', FALSE, 'width="100"'),
					array('<a href="'. $submission['url'] . '" target="_blank">'. $submission['url'] . '</a>', TRUE)
			), '', FALSE, '', '', FALSE);
		}
		if(empty($submission['status'])){
			$delete = adminlang('submission_delete_checkbox');
			$adminhtml->table_td(array(
					array('&nbsp;', FALSE, 'width="100"'),
					array($adminhtml->submit_button('submission_import') . ' ' . $delete, TRUE)
			), NULL, FALSE, NULL, NULL, FALSE);
		}
		$adminhtml->table_end('</form>');
	}else{
		$postid = isset(phpcom::$G['gp_postid']) ? intval(phpcom::$G['gp_postid']) : 0;
		$delete = isset(phpcom::$G['gp_delete']) ? intval(phpcom::$G['gp_delete']) : 0;
		if(submission_import_data($postid)){
			if($delete){
				DB::delete('post_contents', "postid='$postid'");
			}
			admin_succeed('submission_import_succeed', "m=submission");
		}else{
			admin_message('submission_import_invalid');
		}
	}
}elseif($action == 'del'){
	$postid = isset(phpcom::$G['gp_postid']) ? intval(phpcom::$G['gp_postid']) : 0;
	if($postid){
		DB::delete('post_contents', "postid='$postid'");
	}
	admin_succeed('delete_succeed', "m=submission");
}elseif($action == 'import'){
	$postid = isset(phpcom::$G['gp_postid']) ? intval(phpcom::$G['gp_postid']) : 0;
	if(submission_import_data($postid)){
		admin_succeed('submission_import_succeed', "m=submission");
	}else{
		admin_message('submission_import_invalid');
	}
}else{
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$adminhtml->form('m=submission');
		$adminhtml->table_header('menu_submission');
		$adminhtml->table_td(array(
				array('submission_tips', FALSE, 'colspan="6"')
		));
		$adminhtml->table_th(array(
				array('&nbsp;', 'width="2%" align="center" noWrap="noWrap"'),
				array('submission_title', 'width="53%" class="left"'),
				array('submission_dateline', 'width="15%" class="left"'),
				array('submission_author', 'width="15%" class="left"'),
				array('submission_status', 'width="5%" class="left"'),
				array('operation', 'width="10%"')
		));
		$condition = '1=1';
		$totalrec = isset(phpcom::$G['gp_count']) ? intval(phpcom::$G['gp_count']) : 0;
		!$totalrec && $totalrec = DB::result_first("SELECT COUNT(*) FROM " . DB::table('post_contents') . " WHERE $condition");
		$pagesize = 30;
		$pagecount = @ceil($totalrec / $pagesize);
		$pagenow = max(1, min($pagecount, intval($page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$sql = DB::buildlimit("SELECT * FROM " . DB::table('post_contents') . "
				WHERE $condition ORDER BY postid DESC", $pagesize, $pagestart);
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$postid = $row['postid'];
			$edit = $adminhtml->edit_word('submission_import', "m=submission&action=import&postid=$postid", ' | ');
			$edit .= $adminhtml->del_word('delete', "m=submission&action=del&postid=$postid");
			$row['status'] = $row['status'] ? '<span class="blue">&radic;</span>' : '<span class="red">&times;</span>';
			$adminhtml->table_td(array(
					array('<input type="checkbox" class="checkbox" name="selected[]" value="' . $postid . '" />', TRUE),
					array('<a href="?m=submission&action=view&postid='.$postid.'">'.$row['title']. '</a>', TRUE),
					array('<em class="f10">'.fmdate($row['dateline']. '</em>', 'Y-m-d H:i', 'd'), TRUE),
					array('<a href="member.php?action=home&uid='.$row['uid'].'" target="_balnk">'.$row['author']. '</a>', TRUE),
					array($row['status'], TRUE),
					array($edit, TRUE)
			));
		}
		
		$adminhtml->table_td(array(
				array($adminhtml->checkall('checkall', 'chkall', 'selected') . ' ' .
						$adminhtml->radio(adminlang('submission_operation_option'), 'operation', 'delete', false) . ' ' .
						$adminhtml->submit_button('submit', 'btnsubmit', 'button'), TRUE, 'colspan="6"')
		));
		
		$showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=submission") . '</var>';
		$adminhtml->table_td(array(
				array($showpage, TRUE, 'colspan="6" align="right" id="pagecode"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
	}else{
		$selected = isset(phpcom::$G['gp_selected']) ? phpcom::$G['gp_selected'] : null;
		$operation = isset(phpcom::$G['gp_operation']) ? trim(phpcom::$G['gp_operation']) : null;
		if($selected && $operation && ($postids = implodeids($selected))){
			if($operation == 'delete'){
				DB::delete('post_contents', "postid IN($postids)");
				admin_succeed('delete_succeed', "m=submission");
			}else{
				foreach ($selected as $postid){
					submission_import_data($postid);
				}
				if($operation != 'import'){
					DB::delete('post_contents', "postid IN($postids)");
				}
				admin_succeed('submission_operation_import_succeed', "m=submission");
			}
		}
		admin_succeed('operation_succeed', "m=submission");
	}
}
admin_footer();

function submission_import_data($postid = 0)
{
	if(empty($postid)) return false;
	if($submission = DB::fetch_first("SELECT * FROM " . DB::table('post_contents') . " WHERE postid='$postid'")){
		if($submission['status']) return false;
		$thread_data = array('chanid' => $submission['chanid'], 'catid' => $submission['catid'],
				'title' => $submission['title'], 'status' => 1);
		if(!empty($submission['extras']) && ($extras = @unserialize($submission['extras']))){
			$subjects = $extras['subject'];
			$messages = $extras['message'];
			$messages['content'] = $submission['content'];
			if($submission['uid']){
				$thread_data['uid'] = $submission['uid'];
			}
			$thread_data = addslashes_array($thread_data);
			$subjects = addslashes_array($subjects);
			$messages = addslashes_array($messages);
			$post = new DataAccess_PostThread($submission['chanid']);
			$thread = array();
			if($tid = $post->insert($thread, $thread_data, array(), $subjects, $messages)){
				if(isset($extras['download']) && !empty($extras['download'][0]) && isset($thread['softid'])){
					$download = $extras['download'][0];
					DB::insert('soft_download', array(
					'softid' => $thread['softid'],
					'tid' => $tid,
					'servid' => intval($download['servid']),
					'dname' => trim(addslashes($download['dname'])),
					'downurl' => trim(addslashes($download['downurl']))
					));
				}
			}
			
			DB::update('post_contents', array('status' => 1), "postid='$postid'");
			
			return true;
		}
	}
	return false;
}

?>
