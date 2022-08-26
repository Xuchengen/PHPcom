<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : adverts.php  2012-10-17
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';
admin_header('menu_adverts');
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs('tools');
$navarray = array(
		array('title' => 'menu_adverts', 'url' => '?m=adverts', 'id' => 'adverts'),
		array('title' => 'menu_adcategory', 'url' => '?m=adverts&action=category', 'id' => 'adcategory')
);
$adminhtml->navtabs($navarray, $action == 'category' ? 'adcategory' : 'adverts', 'nav_tabs', 'adverts');
if ($action == 'category') {
	$do = isset(phpcom::$G['gp_do']) ? trim(phpcom::$G['gp_do']) : '';
	$cid = isset(phpcom::$G['gp_cid']) ? intval(phpcom::$G['gp_cid']) : 0;
	$adverts = array('cid' => 0, 'subject' => '', 'name' => '', 'description' => '', 'display' => 0,
			'ctype' => 0, 'maxads' => 0, 'width' => '', 'height' => '', 'buyable' => 0, 'price' => 0,
			'units' => 0, 'maxunit' => 0, 'status' => 1);
	if($cid){
		if(!$adverts = DB::fetch_first("SELECT * FROM " . DB::table('adcategory') . " WHERE cid='$cid'")){
			admin_message('undefined_action');
		}
		$cid = $adverts['cid'];
	}
	if($do == 'add' || $do == 'edit'){
		if (!checksubmit(array('submit', 'btnsubmit'))) {
			$adminhtml->tablesetmode = false;
			$adminhtml->form("m=adverts&action=category&do=$do&cid=$cid");
			$adminhtml->table_header("menu_adcategory_$do");
			$adminhtml->table_setting('adverts_subject', 'adverts[subject]', trim($adverts['subject']), 'text');
			$adminhtml->table_setting('adverts_name', 'adverts[name]', trim($adverts['name']), 'text');
			$adminhtml->table_setting('adverts_description', 'adverts[description]', $adverts['description'], 'textarea');
			$adminhtml->table_setting('adverts_display', 'adverts[display]', intval($adverts['display']), 'radios');
			$adminhtml->table_setting('adverts_ctype', 'adverts[ctype]', intval($adverts['ctype']), 'radios');
			$adminhtml->table_setting('adverts_maxads', 'adverts[maxads]', intval($adverts['maxads']), 'text');
			$adminhtml->table_setting('adverts_width_height', array('adverts[width]', 'adverts[height]'), array(trim($adverts['width']), trim($adverts['height'])), 'text2');
			$adminhtml->table_setting('adverts_status', 'adverts[status]', intval($adverts['status']), 'radios');
			echo '<tbody style="display:none;">';
			$adminhtml->table_setting('adverts_buyable', 'adverts[buyable]', intval($adverts['buyable']), 'radio');
			$adminhtml->table_setting('adverts_price', 'adverts[price]', intval($adverts['price']), 'text');
			$adminhtml->table_setting('adverts_units', 'adverts[units]', intval($adverts['units']), 'radios');
			$adminhtml->table_setting('adverts_maxunit', 'adverts[maxunit]', intval($adverts['maxunit']), 'text');
			echo '</tbody>';
			$adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
			$adminhtml->table_end('</form>');
			$adminhtml->table_header("adverts_category_caption");
			$adminhtml->table_td(array(array('adverts_category_tips', array('name' => $adverts['name']))), NULL, FALSE, NULL, NULL, FALSE);
			$adminhtml->table_end();
		}else{
			$advertising = isset(phpcom::$G['gp_adverts']) ? phpcom::$G['gp_adverts'] : null;
			$advertising['subject'] = stripstring(trim($advertising['subject']));
			$advertising['name'] = stripstring(trim($advertising['name']));
			$advertising['width'] = strip_tags(trim($advertising['width']));
			$advertising['height'] = strip_tags(trim($advertising['height']));
			$sql = "SELECT 1 FROM " . DB::table('adcategory') . " WHERE name='{$advertising['name']}' AND cid<>'$cid' LIMIT 1";
			if(DB::num_rows(DB::query($sql))){
				admin_message('adverts_category_name_exist');
			}
			if($do == 'edit' && $cid){
				DB::update('adcategory', $advertising, array('cid' => $cid));
				phpcom_cache::updater('adcategory');
				admin_succeed('adverts_category_edit_succeed', "m=adverts&action=category&do=edit&cid=$cid");
			}else{
				DB::insert('adcategory', $advertising);
				phpcom_cache::updater('adcategory');
				admin_succeed('adverts_category_add_succeed', "m=adverts&action=category");
			}
		}
	}else{
		if (!checksubmit(array('submit', 'btnsubmit'))) {
			$adminhtml->form("m=adverts&action=category");
			$adminhtml->table_header('adverts_adcategory');
			$adminhtml->table_td(array(array('adverts_tips', FALSE, 'colspan="5"')), NULL, FALSE, NULL, NULL, FALSE);
			$adminhtml->table_td(array(
					array('&nbsp;', TRUE, 'width="2%"'),
					array('adverts_subject', FALSE),
					array('adverts_name', FALSE),
					array('adverts_status', FALSE),
					array('operation', FALSE),
			), '', FALSE, ' tablerow', NULL, FALSE);
			$query = DB::query("SELECT * FROM " . DB::table('adcategory') . " ORDER BY cid");
			while ($row = DB::fetch_array($query)) {
				$cid = $row['cid'];
				$edit = $adminhtml->edit_word('edit', "m=adverts&action=category&do=edit&cid=$cid");
				$adminhtml->table_td(array(
						array('<input type="checkbox" class="checkbox" name="selected[]" value="' . $cid . '" />', TRUE),
						array('<a href="?m=adverts&cid='.$row['cid'].'">' . $row['subject'] . '</a>', TRUE),
						array($row['name'], TRUE),
						array('adverts_status_'.$row['status'], FALSE),
						array($edit, TRUE)
				));
			}
			$adminhtml->table_td(array(
					array($adminhtml->checkall('checkall', 'chkall', 'selected') . ' ' .
							$adminhtml->radio(adminlang('adverts_operation_option'), 'operation', 'on', false) . ' ' .
							$adminhtml->submit_button('submit', 'btnsubmit', 'button'), TRUE, 'colspan="5"')
			));
			$adminhtml->table_end('</form>');
		}else{
			$selected = isset(phpcom::$G['gp_selected']) ? phpcom::$G['gp_selected'] : null;
			$operation = isset(phpcom::$G['gp_operation']) ? trim(phpcom::$G['gp_operation']) : null;
			if($selected && $operation){
				if($cids = implodeids($selected)){
					if($operation == 'delete'){
						if(isset(phpcom::$G['gp_delete']) && phpcom::$G['gp_delete']){
							DB::delete('adcategory', "cid IN($cids)");
							delete_adverts($cids);
						}else{
							$extra = '';
							foreach ($selected as $id) {
								$extra .= '<input type="hidden" name="selected[]" value="' . $id . '" />';
							}
							$msgargs = array(
									'form' => TRUE,
									'submit' => TRUE,
            						'cancel' => TRUE,
									'action' => '?m=adverts&action=category&operation=delete&delete=1&submit=yes'
							);
							admin_showmessage('adverts_category_delete_message', null, $msgargs, $extra);
							exit(0);
						}
					}elseif($operation == 'off'){
						DB::update('adcategory', array('status' => 0), "cid IN($cids)");
					}else{
						DB::update('adcategory', array('status' => 1), "cid IN($cids)");
					}
					phpcom_cache::updater('adcategory');
				}
				admin_succeed('adverts_category_succeed', "m=adverts&action=category");
			}else{
				admin_message('adverts_selected_invalid', "m=adverts&action=category");
			}
			
		}
	}
}elseif ($action == 'add' || $action == 'edit') {
	$adverts = array('aid' => 0, 'cid' => 0, 'type' => 0, 'title' => '', 'word' => '', 'src' => '', 'url' => '',
			'content' => '', 'width' => '', 'height' => '', 'status' => 1, 'advertiser' => phpcom::$G['username'],
			'displayorder' => 0, 'dateline' => time(), 'expires' => '', 'highlight' => 0, 'attached' => 0, 'remote' => 0, 'thumb' => 0);
	$aid = isset(phpcom::$G['gp_aid']) ? intval(phpcom::$G['gp_aid']) : 0;
	if($aid){
		if(!$adverts = DB::fetch_first("SELECT * FROM " . DB::table('adverts') . " WHERE aid='$aid'")){
			admin_message('undefined_action');
		}
		$aid = $adverts['aid'];
	}
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		phpcom_cache::load('adcategory');
		$adcategory = &phpcom::$G['cache']['adcategory'];
		$adminhtml->tablesetmode = false;
		echo '<script src="misc/js/calendar.js" type="text/javascript"></script>';
		$adminhtml->form("m=adverts&action=$action&aid=$aid", array(array('tmpid', 0, 'tmpid')));
		$adminhtml->table_header("menu_adverts_$action");
		$adminhtml->table_setting('adverts_type', 'adverts[type]', intval($adverts['type']), 'radios');
		$adminhtml->table_setting('adverts_title', 'adverts[title]', trim($adverts['title']), 'text');
		$adminhtml->table_setting('adverts_src', 'adverts[src]', trim($adverts['src']), 'text');
		$adminhtml->table_setting('adverts_url', 'adverts[url]', trim($adverts['url']), 'text');
		$adminhtml->table_setting('adverts_content', 'adverts[content]', $adverts['content'], 'textarea');
		$adminhtml->table_setting('adverts_word', 'adverts[word]', trim($adverts['word']), 'text');
		$adminhtml->table_setting('adverts_highlight', 'adverts[highlight]', $adverts['highlight'], 'highlight');
		$adminhtml->table_setting('adverts_width_and_height', array('adverts[width]', 'adverts[height]'), array(trim($adverts['width']), trim($adverts['height'])), 'text2');
		$adminhtml->table_setting('adverts_status', 'adverts[status]', intval($adverts['status']), 'radios');
		
		$adverts['dateline'] = empty($adverts['dateline']) ? fmdate(time(), 'Y-m-d') : fmdate($adverts['dateline'], 'Y-m-d');
		$adverts['expires'] = empty($adverts['expires']) ? '' : fmdate($adverts['expires'], 'Y-m-d');
		
		$adminhtml->table_setting('adverts_dateline', array('adverts[dateline]', 'adverts[expires]'), 
				array(trim($adverts['dateline']), trim($adverts['expires'])), 'text2',
				array('showcalendar(this.id)', 'showcalendar(this.id)'), array('adverts_dateline', 'adverts_expires'));
		$adminhtml->table_setting('adverts_displayorder', 'adverts[displayorder]', intval($adverts['displayorder']), 'text');
		$selects = array();
		foreach ($adcategory as $category) {
			$selects[$category['cid']] = $category['subject'];
		}
		$adminhtml->table_setting('adverts_cid', 'adverts[cid]', intval($adverts['cid']), 'select', '', $selects);
		$adminhtml->table_setting('adverts_advertiser', 'adverts[advertiser]', trim($adverts['advertiser']), 'text');
		$adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
		$adminhtml->table_end('</form>');
	}else{
		$advertise = isset(phpcom::$G['gp_adverts']) ? phpcom::$G['gp_adverts'] : null;
		$cid = intval($advertise['cid']);
		$advertise['cid'] = $cid;
		$tmpid = intval(phpcom::$G['gp_tmpid']);
		if(empty($advertise['cid'])){
			admin_message('adverts_category_invalid');
		}
		
		$advertise['dateline'] = empty($advertise['dateline']) ? time() : strtotime($advertise['dateline']);
		$advertise['dateline'] = empty($advertise['dateline']) ? time() : $advertise['dateline'];
		$advertise['expires'] = empty($advertise['expires']) ? 0 : intval(strtotime($advertise['expires']));
		$highlights = phpcom::$G['gp_highlights'];
		$advertise['highlight'] = intval($highlights['font'] . $highlights['color']);
		$advertise['src'] = trim($advertise['src']);
		$advertise['url'] = trim($advertise['url']);
		$advertise['width'] = trim($advertise['width']);
		$advertise['height'] = trim($advertise['height']);
		$attachdir = rtrim(phpcom::$setting['attachdir'], '/\ ');
		if($adverts['attached']){
			if(empty($tmpid) && strcasecmp($adverts['src'], $advertise['src']) != 0){
				$advertise['attached'] = 0;
				$advertise['thumb'] = 0;
				$advertise['remote'] = 0;
				if($adverts['src']){
					$filename = $adverts['src'];
					@unlink("$attachdir/image/$filename");
					$adverts['thumb'] && @unlink("$attachdir/image/" . generatethumbname($filename));
				}
			}else{
				unset($advertise['src']);
			}
		}
		if($tmpid && ($tmp = Attachment::getUploadTemp($tmpid))){
			$advertise['src'] = $tmp['filename'];
			$advertise['attached'] = 1;
			$advertise['thumb'] = $tmp['thumb'];
			$advertise['remote'] = Attachment::ftpOneUpload($tmp);
			if($advertise['type'] != 1 && $advertise['type'] != 2){
				$advertise['type'] = 1;
			}
			if($adverts['attached']){
				$tmparray = array('dirname' => 'image', 'filename' => $adverts['src'],
						'thumb' => $adverts['thumb'], 'remote' => $adverts['remote']);
				Attachment::uploadUnlink($tmparray);
			}
			DB::delete('upload_temp', "tmpid='{$tmp['tmpid']}'");
		}
		
		if($action == 'edit' && $aid){
			DB::update('adverts', $advertise, array('aid' => $aid));
			admin_succeed('adverts_edit_succeed', "m=adverts&action=edit&aid=$aid");
		}else{
			if ($advertise['displayorder'] < 1) {
				$sortord = (int)DB::result_first("SELECT MAX(displayorder) FROM " . DB::table('adverts') . " WHERE cid='$cid'");
				$advertise['displayorder'] = $sortord + 1;
			}
			DB::insert('adverts', $advertise);
			admin_succeed('adverts_add_succeed', "m=adverts");
		}
	}
}else{
	$cid = isset(phpcom::$G['gp_cid']) ? intval(phpcom::$G['gp_cid']) : 0;
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$word = isset(phpcom::$G['gp_word']) ? stripstring(phpcom::$G['gp_word']) : '';
		$type = isset(phpcom::$G['gp_type']) && is_numeric(phpcom::$G['gp_type']) ? intval(phpcom::$G['gp_type']) : null;
		$status = isset(phpcom::$G['gp_status']) && is_numeric(phpcom::$G['gp_status']) ? intval(phpcom::$G['gp_status']) : null;
		
		if(!isset(phpcom::$G['cache']['adcategory'])){
			phpcom_cache::load('adcategory');
		}
		$dropselect = '<select class="select right" style="margin-right:10px;" onchange="location.href=\''.ADMIN_SCRIPT.'?m=adverts&cid=\'+this.value">';
		$dropselect .= '<option value="0">'.adminlang('adverts_all_advertising').'</option>';
		$dropselect .= '<option value="0&status=0"'.($status === 0 ? ' selected="selected"' : '').'>'.adminlang('adverts_status_close').'</option>';
		$dropselect .= '<option value="0&status=1"'.($status === 1 ? ' selected="selected"' : '').'>'.adminlang('adverts_status_start').'</option>';
		if(isset(phpcom::$G['cache']['adcategory'])){
			foreach(phpcom::$G['cache']['adcategory'] as $ads) {
				$dropselect .= '<option value="'.$ads['cid'].'"'.($ads['cid'] == $cid ? ' selected="selected"' : '').'>'.$ads['subject'].'</option>';
			}
		}
		$dropselect .= "</select>";
		$adminhtml->form("m=adverts&cid=$cid");
		$adminhtml->table_header('adverts_manage', null, '', 'tableborder', 0, '', $dropselect);
		$varname = $cid ? 'adverts_displayorder' : 'adverts_type';
		$adminhtml->table_td(array(
				array('&nbsp;', TRUE, 'width="2%"'),
				array('adverts_title', FALSE),
				array($varname, FALSE, 'width="8%"'),
				array('adverts_cid', FALSE, 'width="20%"'),
				array('adverts_startdate', FALSE, 'width="12%"'),
				array('adverts_expires', FALSE, 'width="12%"'),
				array('adverts_status', FALSE, 'width="8%"'),
				array('operation', FALSE, 'width="10%"')
		), '', FALSE, ' tablerow', NULL, FALSE);
		
		$condition = $status === null ? "a.status>='0'" :"a.status='$status'";
		$condition .= $type !== null ? " AND a.type='$type'" : '';
		$condition .= $cid ? " AND a.cid='$cid'" : '';
		$condition .= $word ? " AND a.title LIKE '%$word%'" : '';
		$queryurl = "&status=$status&type=$type&cid=$cid&word=$word";
		$closeids = array();
		$totalrec = isset(phpcom::$G['gp_count']) ? intval(phpcom::$G['gp_count']) : 0;
		!$totalrec && $totalrec = DB::result_first("SELECT COUNT(*) FROM " . DB::table('adverts') . " a WHERE $condition");
		$pagesize = intval(phpcom::$config['admincp']['pagesize']);
		$pagecount = @ceil($totalrec / $pagesize);
		$pagenow = max(1, min($pagecount, intval($page)));
		$pagestart = floor(($pagenow - 1) * $pagesize);
		$order = $cid ? 'a.displayorder ASC' : 'a.aid DESC';
		$sql = DB::buildlimit("SELECT a.*,c.subject FROM " . DB::table('adverts') . " a
			LEFT JOIN " . DB::table('adcategory') . " c USING(cid)
				WHERE $condition ORDER BY $order", $pagesize, $pagestart);
		$query = DB::query($sql);
		$adtypes = adminlang('adverts_type_option');
		while ($row = DB::fetch_array($query)) {
			$aid = $row['aid'];
			$edit = $adminhtml->edit_word('edit', "m=adverts&action=edit&aid=$aid");
			$expires = '<span class="c2">'.adminlang('adverts_permanent').'</span>';
			if(!empty($row['expires']) && $row['expires'] < TIMESTAMP){
				$expires = '<span class="c1" style="text-decoration:line-through;" title="'.fmdate($row['expires'], 'Y-m-d').'">'.adminlang('adverts_expired').'</span>';
				if($row['status'] == 1){
					$closeids[] = $row['aid'];
				}
			}elseif(!empty($row['expires'])){
				$expires = '<em class="f10 c2">'.fmdate($row['expires'], 'Y-m-d').'</em>';
			}
			$typeorder = '<a href="?m=adverts&type='.$row['type'].'">'.$adtypes[$row['type']].'</a>';
			if($cid){
				$typeorder = '<input class="input sortord" size="5" name="sortord['.$aid.']" type="text" value="'.$row['displayorder'].'" />';
			}
			$adminhtml->table_td(array(
					array('<input type="checkbox" class="checkbox" name="selected[]" value="' . $aid . '" />', TRUE),
					array($row['title'], TRUE),
					array($typeorder, TRUE),
					array('<a href="?m=adverts&cid='.$row['cid'].'">'.$row['subject'].'</a>', TRUE),
					array('<em class="f10">'.fmdate($row['dateline']. '</em>', 'Y-m-d'), TRUE),
					array($expires, TRUE),
					array('adverts_status_'.$row['status'], FALSE),
					array($edit, TRUE)
			));
		}
		
		$adminhtml->table_td(array(
				array($adminhtml->checkall('checkall', 'chkall', 'selected') . ' ' .
						$adminhtml->radio(adminlang('adverts_operation_option'), 'operation', '', false) . ' ' .
						$adminhtml->submit_button('submit', 'btnsubmit', 'button'), TRUE, 'colspan="8"')
		));
		
		$showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=adverts$queryurl") . '</var>';
		$adminhtml->table_td(array(
				array($showpage, TRUE, 'colspan="8" align="right" id="pagecode"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
		if($closeids && ($aids = implodeids($closeids))){
			DB::update('adverts', array('status' => 0), "aid IN($aids)");
		}
	}else{
		$selected = isset(phpcom::$G['gp_selected']) ? phpcom::$G['gp_selected'] : null;
		$operation = isset(phpcom::$G['gp_operation']) ? trim(phpcom::$G['gp_operation']) : null;
		if($selected && $operation){
			if($aids = implodeids($selected)){
				if($operation == 'delete'){
					if(isset(phpcom::$G['gp_delete']) && phpcom::$G['gp_delete']){
						delete_adverts(null, $aids);
					}else{
						$extra = '';
						foreach ($selected as $id) {
							$extra .= '<input type="hidden" name="selected[]" value="' . $id . '" />';
						}
						$msgargs = array(
								'form' => TRUE,
								'submit' => TRUE,
								'cancel' => TRUE,
								'action' => '?m=adverts&operation=delete&delete=1&submit=yes'
						);
						admin_showmessage('adverts_delete_message', null, $msgargs, $extra);
						exit(0);
					}
				}elseif($operation == 'off'){
					DB::update('adverts', array('status' => 0), "aid IN($aids)");
				}else{
					DB::update('adverts', array('status' => 1), "aid IN($aids)");
				}
				phpcom_cache::updater('adcategory');
			}
			admin_succeed('adverts_succeed', "m=adverts");
		}else{
			if(isset(phpcom::$G['gp_sortord'])){
				$sortords = phpcom::$G['gp_sortord'];
				if($selected){
					foreach ($selected as $aid){
						if(isset($sortords[$aid])){
							DB::update('adverts', array('displayorder' => intval($sortords[$aid])), "aid='$aid'");
						}
					}
				}else{
					foreach ($sortords as $aid => $sortord) {
						DB::update('adverts', array('displayorder' => $sortord), "aid='$aid'");
					}
				}
				admin_succeed('adverts_succeed', "m=adverts&cid=$cid");
			}else{
				admin_message('adverts_selected_invalid', "m=adverts");
			}
		}
	}
}

admin_footer();

function delete_adverts($cids, $aids = null){
	if($cids && is_array($cids)){
		$cids = implodeids($cids);
	}
	if($aids && is_array($aids)){
		$aids = implodeids($aids);
	}
	if($cids && !$aids){
		$condition = "cid IN($cids)";
	}else{
		$condition = "aid IN($aids)";
	}
	if(!$cids && !$aids) return;
	$attachdir = rtrim(phpcom::$setting['attachdir'], '/\ ');
	$query = DB::query("SELECT aid, src, attached, thumb, remote FROM " . DB::table('adverts') . " WHERE $condition");
	while ($advert = DB::fetch_array($query)) {
		if($advert['attached']){
			$advert['dirname'] = 'image';
			$advert['filename'] = $advert['src'];
			Attachment::uploadUnlink($advert);
		}
	}
	DB::delete('adverts', $condition);
}
?>