<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : category.php    2011-5-31 17:44:51
 */
if(!defined('IN_PHPCOM') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
phpcom::$G['lang']['admin'] = 'misc';
$chanid = isset(phpcom::$G['gp_chanid']) ? intval(phpcom::$G['gp_chanid']) : $chanid;
phpcom::$G['channelid'] = $chanid;
phpcom::$G['cache']['channel'] = &phpcom::$G['channel'][$chanid];
$module = 'article';
$subname = '';
if(isset(phpcom::$G['cache']['channel']['modules'])){
	$module = phpcom::$G['cache']['channel']['modules'];
	$subname = phpcom::$G['cache']['channel']['subname'];
}
admin_header('menu_category', in_array($action, array('edit', 'edit', 'addsub')) ? "menu_category_$action" : '');
$navarray = array(
		array('title' => 'menu_category', 'url' => "?m=category&chanid=$chanid", 'name' => 'first'),
		array('title' => 'menu_category_add', 'url' => "?m=category&action=add&chanid=$chanid", 'name' => 'add'),
		array('title' => 'menu_content_admin', 'url' => "?m=$module&chanid=$chanid", 'name' => 'content'),
		array('title' => 'menu_thread_class', 'url' => "?m=category&action=class&chanid=$chanid&more=1", 'name' => 'class')
);

$adminhtml = phpcom_adminhtml::instance();
$adminhtml->vars = array('name' => $subname, 'chanid' => $chanid, 'module' => $module);
if($action != 'select'){
	$adminhtml->activetabs('topic');
	$adminhtml->navtabs($navarray, stricmp($action, array('first', 'add', 'class'), true, 'first'));
}

if($chanid) phpcom_cache::load("thread_class");

if ($action == 'addsub') {
	$catid = isset(phpcom::$G['gp_catid']) ? intval(phpcom::$G['gp_catid']) : 0;
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$adminhtml->table_header('category_addsub', 6);
		$adminhtml->table_td(array(
				array('category_comments', FALSE, ' colspan="6"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end();
		$adminhtml->count = 0;
		$adminhtml->form("m=category&action=addsub&catid=$catid&chanid=$chanid");
		$adminhtml->table_header();
		$adminhtml->table_th(array(
				array('category_order', 'width="5%" align="center" noWrap="noWrap"'),
				array('category_name', 'width="18" class="left"'),
				array('category_subname', 'width="15%" class="left"'),
				array('category_codename', 'width="15%" class="left"'),
				array('category_description', 'width="30%" class="left"'),
				array('category_keyword', 'width="17%" class="left"')
		));
		$newcatid = makenewcatid() - 1;
		$sortord = (int)DB::result_first("SELECT MAX(sortord) FROM " . DB::table('category') . " WHERE chanid='$chanid' AND parentid='$catid'");
		for ($i = 1; $i < 11; $i++) {
			$codename = $newcatid + $i;
			$adminhtml->table_td(array(
					array('<input class="input sortord" size="1" name="categorys['. $i .'][sortord]" type="text" value="' . ($sortord + $i) . '" />', TRUE, 'align="center"'),
					array('<input class="input" size="20" name="categorys['. $i .'][catname]" type="text" />', TRUE),
					array('<input class="input" size="15" name="categorys['. $i .'][subname]" type="text" />', TRUE),
					array('<input class="input" size="15" name="categorys['. $i .'][codename]" type="text" value="sort' . sprintf('%03d', $codename) . '" />', TRUE),
					array('<input class="input" size="35" name="categorys['. $i .'][description]" type="text" />', TRUE),
					array('<input class="input" size="20" name="categorys['. $i .'][keyword]" type="text" />', TRUE)
			));
		}
		$btnsubmit = $adminhtml->submit_button();
		$adminhtml->table_td(array(
				array($btnsubmit, TRUE, 'align="center" colspan="6"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
	}else{
		$categorys = striptags(phpcom::$G['gp_categorys']);
		$depth = $parentid = $rootid = $addcomplete = 0;
		$chanid = $chanid;
		if($cat = DB::fetch_first("SELECT catid, chanid, depth, rootid FROM " . DB::table('category') . " WHERE catid='$catid'")){
			$depth = $cat['depth'] + 1;
			$parentid = $cat['catid'];
			$rootid = $cat['rootid'];
			$chanid = $cat['chanid'];
		}else{
			admin_message('category_add_failed');
		}
		if ($depth > 3)  admin_message('category_depth');
		$newcatid = makenewcatid();
		foreach ($categorys as $data) {
			if(!empty($data['catname']) && !empty($data['codename'])){
				$data['catid'] = $newcatid++;
				$data['chanid'] = $chanid;
				$data['parentid'] = $parentid;
				$data['depth'] = $depth;
				$data['rootid'] = $rootid;
				$data['child'] = $data['target'] = 0;
				$data['setting'] = '';
				if(empty($data['subname'])) $data['subname'] = $data['catname'];
				DB::insert('category', $data);
				$addcomplete = 1;
			}
		}
		if($addcomplete && $catid){
			$child = intval(DB::result_first("SELECT COUNT(*) FROM " . DB::table('category') . " WHERE parentid='$catid'"));
			DB::update('category', array('child' => $child), "catid='$catid'");
		}
		update_categorys_cache($chanid);
		@header("Location: " . ADMIN_SCRIPT . "?m=category&chanid=$chanid");
	}
}elseif ($action == 'class') {
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		echo <<<EOT
<script type="text/javascript">
var rowtypedata = ['&nbsp;',
'<input name="threadclassnew[ordinal][]" type="text" class="input sortord" value="0"/>',
'<input name="threadclassnew[name][]" type="text" class="input t15"/>',
'<input name="threadclassnew[alias][]" type="text" class="input t15"/>',
'<input name="threadclassnew[about][]" type="text" class="input t30"/>',
'<input name="threadclassnew[icon][]" type="text" class="input t30"/>'];
</script>
EOT;
		$adminhtml->form("m=category&action=class&chanid=$chanid");
		$adminhtml->table_header('thread_class');
		$adminhtml->table_td(array(
				array('deletecheckbox', FALSE, 'width="5%" noWrap="noWrap"'),
				array('thread_class_ordinal', FALSE, 'width="5%"'),
				array('thread_class_name', FALSE, 'width="15%"'),
				array('thread_class_alias', FALSE, 'width="15%"'),
				array('thread_class_about', FALSE, 'width="30%"'),
				array('thread_class_icon', FALSE, 'width="30%"')
		), '', FALSE, ' tablerow', NULL, FALSE);
		$sql = "SELECT * FROM " . DB::table('thread_class') . " WHERE chanid='$chanid' AND catid='0' ORDER BY ordinal, classid";
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$classid = $row['classid'];
			$adminhtml->table_td(array(
					array('<input type="checkbox" class="checkbox" name="delete[' . $classid . ']" value="' . $classid . '" />', TRUE),
					array('<input name="threadclass[' . $classid . '][ordinal]" title="classid: '.$classid.'" type="text" class="input sortord" value="'.$row['ordinal'].'" />', TRUE),
					array('<input name="threadclass[' . $classid . '][name]" title="classid: '.$classid.'" type="text" class="input t15" value="'.htmlcharsencode($row['name']).'" />', TRUE),
					array('<input name="threadclass[' . $classid . '][alias]" type="text" class="input t15" value="'.htmlcharsencode($row['alias']).'" />', TRUE),
					array('<input name="threadclass[' . $classid . '][about]" type="text" class="input t30" value="'.htmlcharsencode($row['about']).'" />', TRUE),
					array('<input name="threadclass[' . $classid . '][icon]" type="text" class="input t30" value="'.htmlcharsencode($row['icon']).'" />', TRUE)
			));
		}
		$adminhtml->table_td(array(
				array('thread_class_add', FALSE, 'colspan="6"')
		));
		$btnsubmit = $adminhtml->submit_button();
		$adminhtml->table_td(array(
				array($btnsubmit, TRUE, 'align="center" colspan="6"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
	}else{
		add_update_threadclass(0, $chanid);
		admin_succeed('thread_class_succeed', "m=category&action=class&chanid=$chanid");
	}
}elseif ($action == 'add' || $action == 'edit') {
	$catid = isset(phpcom::$G['gp_catid']) ? intval(phpcom::$G['gp_catid']) : 0;
	$category = array('catid' => 0, 'chanid' => $chanid, 'depth' => 0, 'rootid' => 0, 'catname' => '', 'subname' => '', 'codename' => '',
			'prefixurl' => '', 'prefix' => '', 'color' => '', 'icons' => '', 'title' => '', 'description' => '','keyword' => '', 
			'toptitle' => '', 'topnum' => 0, 'toptype' => 0, 'topmode' => 0, 'basic' => 0, 'template' => '', 'banner' => '',  
			'sortord' => 0, 'num' => 0, 'pagesize' => 0, 'parentid' => 0, 'child' => 0, 'target' => 0, 'caturl' => '');
	if ($action == 'edit' && $catid) {
		if(!($category = DB::fetch_first("SELECT * FROM " . DB::table('category') . " WHERE catid='$catid'"))){
			admin_message('undefined_action');
		}
	}
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$chanid = intval($category['chanid']);
		$adminhtml->form("m=category&action=$action&catid=$catid&chanid=$chanid", array(
				array('imagetmpid', 0, 'imagetmpid')
		));
		$adminhtml->table_header('tips');
		$adminhtml->table_td(array(
				array('category_comments', FALSE, 'colspan="3"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_th(array(
				array("category_$action", 'class="left" colspan="3"')
		));

		$adminhtml->table_td(array(
				array('category_name', FALSE, 'width="12%"', '', TRUE),
				array('<input class="input t70" size="60" name="categorys[catname]" type="text" value="' . htmlcharsencode($category['catname']) . '" />', TRUE, 'width="38%"'),
				array($adminhtml->textcolor('categorys[color]', $category['color']), TRUE, 'width="60%"')
		));
		$othercheckbox = " <button class=\"button\" type=\"button\" onclick=\"uploadingWindow('catimage','image',$chanid,$catid);\"> " . adminlang('imageupload') . " </button>";
		$adminhtml->table_td(array(
				array('category_subname', FALSE, '', '', TRUE),
				array('<input class="input t30" size="30" name="categorys[subname]" type="text" value="' . htmlcharsencode($category['subname']) . '" />' . $othercheckbox, TRUE),
				array('category_subname_tips', FALSE, '', '', 'tips')
		));
		$apptochild = '';
		if($action == 'edit'){
			$apptochild = '&nbsp; <label class="c1"><input type="checkbox" name="codenametochild" class="checkbox" value="1" /> '.adminlang('category_applied_child').'</label>';
		}
		$adminhtml->table_td(array(
				array('category_codename', FALSE, '', '', TRUE),
				array('<input class="input t30" size="30" name="categorys[codename]" type="text" value="' . htmlcharsencode($category['codename']) . '" /> ' . $apptochild, TRUE),
				array('category_codename_tips', FALSE, '', '', 'tips')
		));
		$adminhtml->table_td(array(
				array('category_basic', FALSE, '', '', TRUE),
				array($adminhtml->radio(array('category_basic_default', 'category_basic_primary'), 'categorys[basic]', intval($category['basic'])), TRUE),
				array('category_basic_tips', FALSE, '', '', 'tips')
		));
		$adminhtml->table_td(array(
				array('category_title', FALSE, '', '', TRUE),
				array('<input class="input t70" size="60" name="categorys[title]" type="text" value="' . htmlcharsencode($category['title']) . '" />', TRUE),
				array('category_title_tips', FALSE, '', '', 'tips')
		));
		$adminhtml->table_td(array(
				array('category_description', FALSE, '', '', TRUE),
				array('<textarea rows="6" title="%s" name="categorys[description]" class="textarea" style="width:375px;">' . htmlcharsencode($category['description']) . '</textarea>', TRUE),
				array('category_description_tips', FALSE, '', '', 'tips')
		));
		$adminhtml->table_td(array(
				array('category_keyword', FALSE, '', '', TRUE),
				array('<input class="input t70" size="60" name="categorys[keyword]" type="text" value="' . htmlcharsencode($category['keyword']) . '" />', TRUE),
				array('category_keyword_tips', FALSE, '', '', 'tips')
		));
		$appliedtoall = '';
		if(defined('IN_PHPCOM_BUSINESS') && IN_PHPCOM_BUSINESS){
			if($action == 'edit'){
				$appliedtoall = '&nbsp; <label class="c1"><input type="checkbox" name="topappliedall" class="checkbox" value="1" /> '.adminlang('category_applied_all').'</label>';
			}
			$adminhtml->table_td(array(
					array('category_topmode', FALSE, '', '', TRUE),
					array($adminhtml->radio(array('category_topmode_default', 'category_topmode_list'), 'categorys[topmode]', intval($category['topmode'])) . " $appliedtoall", TRUE),
					array('category_topmode_tips', FALSE, '', '', 'tips')
			));
			$adminhtml->table_td(array(
					array('category_toptype', FALSE, '', '', TRUE),
					array($adminhtml->radio(array('category_toptype_week', 'category_toptype_month', 'category_toptype_star'), 'categorys[toptype]', intval($category['toptype'])), TRUE),
					array('category_toptype_tips', FALSE, '', '', 'tips')
			));
			$adminhtml->table_td(array(
					array('category_toptitle', FALSE, '', '', TRUE),
					array('<input class="input t70" size="60" name="categorys[toptitle]" type="text" value="' . htmlcharsencode($category['toptitle']) . '" />', TRUE),
					array('category_toptitle_tips', FALSE, '', '', 'tips')
			));
			if($action == 'edit'){
				$apptochild = '&nbsp; <label class="c3"><input type="checkbox" name="topnumtochild" class="checkbox" value="1" /> '.adminlang('category_applied_child').'</label>';
			}
			$adminhtml->table_td(array(
					array('category_topnum', FALSE, '', '', TRUE),
					array('<input class="input t30" size="30" name="categorys[topnum]" type="text" value="' . intval($category['topnum']) . '" /> ' . $apptochild, TRUE),
					array('category_topnum_tips', FALSE, '', '', 'tips')
			));
		}
		if($action == 'edit'){
			$apptochild = '&nbsp; <label class="c1"><input type="checkbox" name="prefixurltochild" class="checkbox" value="1" /> '.adminlang('category_applied_child').'</label>';
		}
		$adminhtml->table_td(array(
				array('category_prefixurl', FALSE, '', '', TRUE),
				array('<input class="input t30" size="30" name="categorys[prefixurl]" type="text" value="' . htmlcharsencode($category['prefixurl']) . '" /> ' . $apptochild, TRUE),
				array('category_prefixurl_tips', FALSE, '', '', 'tips')
		));
		$adminhtml->table_td(array(
				array('category_caturl', FALSE, '', '', TRUE),
				array('<input class="input t70" size="60" name="categorys[caturl]" type="text" value="' . htmlcharsencode($category['caturl']) . '" />', TRUE),
				array('category_caturl_tips', FALSE, '', '', 'tips')
		));
		$adminhtml->table_td(array(
				array('category_banner', FALSE, '', '', TRUE),
				array('<input class="input t70" size="60" name="categorys[banner]" type="text" value="' . htmlcharsencode($category['banner']) . '" />', TRUE),
				array('category_banner_tips', FALSE, '', '', 'tips')
		));
		$apptochild = '';
		if($action == 'edit'){
			$apptochild = '&nbsp; <label class="c1"><input type="checkbox" name="apptochild" class="checkbox" value="1" /> '.adminlang('category_applied_child').'</label>';
		}
		$adminhtml->table_td(array(
				array('category_template', FALSE, '', '', TRUE),
				array('<input class="input t30" size="30" name="categorys[template]" type="text" value="' . htmlcharsencode($category['template']) . '" /> ' . $apptochild, TRUE),
				array('category_template_tips', FALSE, '', '', 'tips')
		));
		if($action == 'edit'){
			$apptochild = '&nbsp; <label class="c2"><input type="checkbox" name="prefixtochild" class="checkbox" value="1" /> '.adminlang('category_applied_child').'</label>';
		}
		$adminhtml->table_td(array(
				array('category_prefix', FALSE, '', '', TRUE),
				array('<input class="input t30" size="30" name="categorys[prefix]" type="text" value="' . htmlcharsencode($category['prefix']) . '" /> ' . $apptochild, TRUE),
				array('category_prefix_tips', FALSE, '', '', 'tips')
		));
		if($action == 'edit'){
			$apptochild = '&nbsp; <label class="c1"><input type="checkbox" name="pagesizetochild" class="checkbox" value="1" /> '.adminlang('category_applied_child').'</label>';
		}
		$adminhtml->table_td(array(
				array('category_pagesize', FALSE, '', '', TRUE),
				array('<input class="input t30" size="30" name="categorys[pagesize]" type="text" value="' . intval($category['pagesize']) . '" /> ' . $apptochild, TRUE),
				array('category_pagesize_tips', FALSE, '', '', 'tips')
		));
		$target = adminlang('targets');
		$target_select = '<select name="categorys[target]" class="select t30">';
		foreach ($target as $key => $value) {
			$target_select .= "<option value=\"$key\"";
			$target_select .= ( $key == $category['target']) ? ' SELECTED' : '';
			$target_select .=">$value</option>";
		}
		$target_select .= '</select>';
		$adminhtml->table_td(array(
				array('category_target', FALSE, '', '', TRUE),
				array($target_select, TRUE),
				array('category_target_tips', FALSE, '', '', 'tips')
		));
		$parentid_select = '<select name="categorys[parentid]" class="select t70">';
		$parentid_select .= '<option value="0">' . adminlang('category_select_default') . '</option>';
		$parentid_select .= category_select_option($chanid, intval($category['parentid']));
		$parentid_select .= '</select>';
		$adminhtml->table_td(array(
				array('category_parent', FALSE, '', '', TRUE),
				array($parentid_select, TRUE),
				array('category_parent_tips', FALSE, '', '', 'tips')
		));
		$adminhtml->table_td(array(
				array('category_order', FALSE, '', '', TRUE),
				array('<input class="input sortord" size="1" name="categorys[sortord]" type="text" value="' . intval($category['sortord']) . '" />' . 
					' <input class="input sortord" size="1" name="categorys[num]" type="text" value="' . intval($category['num']) . '" />', TRUE),
				array('category_order_tips', FALSE, '', '', 'tips')
		));
		$btnsubmit = $adminhtml->submit_button();
		$adminhtml->table_td(array(
				array($btnsubmit, TRUE, 'align="center" colspan="6"')
		), NULL, FALSE, NULL, NULL, FALSE);
		if(empty($category['depth']) && $catid && $chanid != 4){
			$adminhtml->table_end();

			echo <<<EOT
<script type="text/javascript">
var rowtypedata = ['&nbsp;',
'<input name="threadclassnew[ordinal][]" type="text" class="input sortord" value="0"/>',
'<input name="threadclassnew[name][]" type="text" class="input t15"/>',
'<input name="threadclassnew[alias][]" type="text" class="input t15"/>',
'<input name="threadclassnew[about][]" type="text" class="input t30"/>',
'<input name="threadclassnew[icon][]" type="text" class="input t30"/>'];
</script>
EOT;
			$adminhtml->table_header('thread_class');
			$adminhtml->table_td(array(
					array('deletecheckbox', FALSE, 'width="5%" noWrap="noWrap"'),
					array('thread_class_ordinal', FALSE, 'width="5%"'),
					array('thread_class_name', FALSE, 'width="15%"'),
					array('thread_class_alias', FALSE, 'width="15%"'),
					array('thread_class_about', FALSE, 'width="30%"'),
					array('thread_class_icon', FALSE, 'width="30%"')
			), '', FALSE, ' tablerow', NULL, FALSE);
			$sql = "SELECT * FROM " . DB::table('thread_class') . " WHERE catid='$catid' ORDER BY ordinal, classid";
			$query = DB::query($sql);
			while ($row = DB::fetch_array($query)) {
				$classid = $row['classid'];
				$adminhtml->table_td(array(
						array('<input type="checkbox" class="checkbox" name="delete[' . $classid . ']" value="' . $classid . '" />', TRUE),
						array('<input name="threadclass[' . $classid . '][ordinal]" title="classid: '.$classid.'" type="text" class="input sortord" value="'.$row['ordinal'].'" />', TRUE),
						array('<input name="threadclass[' . $classid . '][name]" title="classid: '.$classid.'" type="text" class="input t15" value="'.htmlcharsencode($row['name']).'" />', TRUE),
						array('<input name="threadclass[' . $classid . '][alias]" type="text" class="input t15" value="'.htmlcharsencode($row['alias']).'" />', TRUE),
						array('<input name="threadclass[' . $classid . '][about]" type="text" class="input t30" value="'.htmlcharsencode($row['about']).'" />', TRUE),
						array('<input name="threadclass[' . $classid . '][icon]" type="text" class="input t30" value="'.htmlcharsencode($row['icon']).'" />', TRUE)
				));
			}
			$adminhtml->table_td(array(
					array('thread_class_add', FALSE, 'colspan="6"')
			));
		}
		$adminhtml->table_end('</form>');
	}else{
		$categorys = striptags(phpcom::$G['gp_categorys']);
		$categorys['chanid'] = $chanid;
		if (empty($categorys['catname'])) admin_message('category_name');
		if (empty($categorys['codename'])) admin_message('category_codename');
		if (empty($categorys['subname'])) {
			$categorys['subname'] = $categorys['catname'];
		}
		$categorys['parentid'] = intval($categorys['parentid']);
		$categorys['target'] = intval($categorys['target']);
		$categorys['pagesize'] = empty($categorys['pagesize']) ? 0 : intval($categorys['pagesize']);
		$categorys['num'] = empty($categorys['num']) ? 0 : intval($categorys['num']);
		$categorys['prefix'] = empty($categorys['prefix']) ? '' : trim($categorys['prefix']);
		$prefixurl = empty($categorys['prefixurl']) ? '' : rtrim(trim($categorys['prefixurl']), '/\\');
		if($prefixurl && !parse_url($prefixurl, PHP_URL_SCHEME)){
			$prefixurl = "http://$prefixurl";
		}
		$categorys['prefixurl'] = $prefixurl;
		$categorys['title'] = trim(strip_tags($categorys['title']));
		$categorys['description'] = trim(strip_tags($categorys['description']));
		$categorys['keyword'] = trim(strip_tags($categorys['keyword']));
		if(defined('IN_PHPCOM_BUSINESS') && IN_PHPCOM_BUSINESS){
			$categorys['toptitle'] = empty($categorys['toptitle']) ? '' : trim(strip_tags($categorys['toptitle']));
			$categorys['topnum'] = empty($categorys['topnum']) ? 0 : intval($categorys['topnum']);
			$categorys['toptype'] = empty($categorys['toptype']) ? 0 : intval($categorys['toptype']);
			$categorys['topmode'] = empty($categorys['topmode']) ? 0 : intval($categorys['topmode']);
		}
		$rootid = $child = 0;
		if ($action == 'edit') {
			if ($categorys['parentid'] == $catid) {
				admin_message('category_update_failed');
			}
			$rootid = $category['rootid'];
			$child = $category['child'];
			//如果要移动分类，检测分类能否被移动
			if ($category['parentid'] != $categorys['parentid']) {
				//此分类包含子分类，抛出错误
				if ($category['child']) {
					admin_message('category_moved_failed');
				}else{
					if(DB::fetch_first("SELECT classid FROM " . DB::table('thread_class') . " WHERE catid='$catid'")){
						admin_message('category_exist_thread_class');
					}
				}
				//获取目标分类信息
				if ($categorys['parentid']) {
					$parentrow = DB::fetch_first("SELECT catid,depth,rootid FROM " . DB::table('category') . " WHERE catid='{$categorys['parentid']}'");
					if ($parentrow) {
						if ($parentrow['depth'] > 2) {
							admin_error('errors');
						}
						$rootid = $parentrow['rootid'];
						$categorys['depth'] = $parentrow['depth'] + 1;
						$categorys['rootid'] = $rootid;
					} else {
						//如果获取不到目标分类信息，抛出错误
						admin_error('errors');
					}
				} else {
					//如果移动为一级分类，设置分类 depth=0
					$categorys['depth'] = 0;
					$categorys['rootid'] = $catid;
					$categorys['child'] = 0;
					$rootid = $catid;
				}
			}
			//获取当前分类的所属分类ID
			$parentid = (int)DB::result_first("SELECT parentid FROM " . DB::table('category') . " WHERE catid='$catid'");
			//更新分类信息
			DB::update('category', $categorys, array('catid' => $catid));
			if ($parentid) {
				//更新当前分类的子分类信息
				$child = (int)DB::result_first("SELECT COUNT(*) FROM " . DB::table('category') . " WHERE parentid='$parentid'");
				DB::update('category', array('child' => $child), "catid='$parentid'");
			}
			$parentid = intval($categorys['parentid']);
			//判断是否更新所属分类；如果移动的分类为一级分类，无需更新操作
			if ($parentid && $category['parentid'] != $parentid) {
				$child = (int)DB::result_first("SELECT COUNT(*) FROM " . DB::table('category') . " WHERE parentid='$parentid'");
				DB::update('category', array('child' => $child), "catid='$parentid'");
			}
			if(defined('IN_PHPCOM_BUSINESS') && IN_PHPCOM_BUSINESS){
				if(!empty(phpcom::$G['gp_topappliedall'])){
					$topdata = array();
					$topdata['toptitle'] = empty($categorys['toptitle']) ? '' : trim(strip_tags($categorys['toptitle']));
					$topdata['topnum'] = empty($categorys['topnum']) ? 0 : intval($categorys['topnum']);
					$topdata['toptype'] = empty($categorys['toptype']) ? 0 : intval($categorys['toptype']);
					$topdata['topmode'] = empty($categorys['topmode']) ? 0 : intval($categorys['topmode']);
					DB::update('category', $topdata, "chanid='$chanid'");
				}
			}
			if($child){
				$childarray = array();
				if(!empty(phpcom::$G['gp_apptochild'])){
					$childarray['template'] = trim($categorys['template']);
				}
				if(!empty(phpcom::$G['gp_codenametochild'])){
					$childarray['codename'] = trim($categorys['codename']);
				}
				if(!empty(phpcom::$G['gp_prefixurltochild'])){
					$childarray['prefixurl'] = trim($categorys['prefixurl']);
				}
				if(!empty(phpcom::$G['gp_prefixtochild'])){
					$childarray['prefix'] = trim($categorys['prefix']);
				}
				if(!empty(phpcom::$G['gp_pagesizetochild'])){
					$childarray['pagesize'] = intval($categorys['pagesize']);
				}
				if(!empty(phpcom::$G['gp_topnumtochild'])){
					$childarray['topnum'] = trim($categorys['topnum']);
				}
				if(!empty($childarray)){
					DB::update('category', $childarray, "parentid='$catid'");
				}
			}
			updatecategoryimage($catid);
			add_update_threadclass($catid, $chanid);
			update_categorys_cache($chanid);
			if($category['rootid'] != $rootid && $category['chanid'] != 4){
				DB::update('threads', array('rootid' => $rootid), "catid='$catid'");
				DB::update("{$module}_thread", array('rootid' => $rootid), "catid='$catid'");
			}
			admin_succeed('category_succeed', "m=category&action=edit&chanid=$chanid&catid=$catid");
		} elseif ($action == 'add') {
			//增加新的分类
			$parentid = intval($categorys['parentid']);
			$rootid = $catid = makenewcatid();
			$categorys['catid'] = $catid;
			$categorys['imageurl'] = '';
			//查询所属分类信息
			if ($parentid) {
				$row = DB::fetch_first("SELECT catid,depth,rootid,child FROM " . DB::table('category') . " WHERE catid='$parentid'");
				$categorys['depth'] = $row['depth'] + 1;
				$categorys['rootid'] = $row['rootid'];
				$rootid = $row['rootid'];
				//如果选择的所属分类超过限制3，抛出错误信息
				if ($row['depth'] > 2) {
					admin_message('category_insert_failed');
				}
			} else {
				//如果新增的是一级分类，设置默认信息
				$categorys['depth'] = 0;
				$categorys['rootid'] = $categorys['catid'];
				$categorys['parentid'] = 0;
				$categorys['child'] = 0;
			}
			//计算排序
			if ($categorys['sortord'] < 1) {
				$sortord = (int)DB::result_first("SELECT MAX(sortord) FROM " . DB::table('category') . " WHERE parentid='$parentid'");
				$categorys['sortord'] = $sortord + 1;
			}
			$categorys['setting'] = '';
			//新增分类数据
			DB::insert('category', $categorys);
			//更新所属分类信息
			if ($parentid) {
				$child = (int)DB::result_first("SELECT COUNT(*) FROM " . DB::table('category') . " WHERE parentid='$parentid'");
				DB::update('category', array('child' => $child), 'catid=' . $parentid);
			}
			updatecategoryimage($catid);
		}
		update_categorys_cache($chanid);
		admin_succeed('category_succeed', "m=category&chanid=$chanid");
	}
}elseif ($action == 'select') {
	$catid = isset(phpcom::$G['gp_catid']) ? intval(phpcom::$G['gp_catid']) : null;
	$adminhtml->table_header('thread_class_select', 5);
	$adminhtml->table_td(array(
			array('thread_class_select_input', FALSE, 'colspan="5" align="left"')
	), NULL, FALSE, NULL, NULL, FALSE);
	$condition = " catid>'0' AND";
	if($catid && ($category = DB::fetch_first("SELECT rootid FROM " . DB::table('category') . " WHERE catid='$catid'"))){
		$condition = "catid='{$category['rootid']}' AND";
	}
	$query1 = DB::query("SELECT * FROM " . DB::table('thread_class') . " WHERE catid='0' AND chanid='$chanid' ORDER BY ordinal, classid");
	$query = DB::query("SELECT * FROM " . DB::table('thread_class') . " WHERE $condition chanid='$chanid' ORDER BY ordinal, classid");
	$i = $n = $m = 0;
	while (($row = DB::fetch_array($query1)) || ($row = DB::fetch_array($query))) {
		$classid = $row['classid'];
		$m = ($i % 2 == 0) ? 2 : 1;
		if ($i === 0) {
			echo '<tr>';
		}
		echo '<td class="tablerow', $m, '" width="20%" title="'.$row['name'].'">';
		echo '<div class="item"><label class="txt">';
		echo '<input class="checkbox" type="checkbox" id="checkbox_'.$classid.'" name="classidnew[]" onclick="topicalAdd(this, \'classidstr\')" value="'.$classid.'" />';
		echo "&nbsp;{$row['name']}</label></div>";
		echo '</td>';
		$i++;
		if ($i % 5 == 0) {
			echo '</tr><tr>';
		}
	}
	if($ii = $i % 5){
		for ($index = 1; $index <= (5 - $ii); $index++) {
			$m = ($index % 2 == 0) ? 2 : 1;
			echo '<td class="tablerow', $m, '" width="20%">&nbsp;</td>';
		}
		echo '</tr>';
	}
	$adminhtml->table_end();
	echo <<<EOT
<style type="text/css">
    html {padding-top:0;}
</style>
<script type="text/javascript">
if($('crumbnav')){
    $('crumbnav').style.display='none';
}
initTopicSelect('classidstr');
</script>
EOT;
	exit('</body></html>');
}elseif ($action == 'del') {
	$catid = isset(phpcom::$G['gp_catid']) ? intval(phpcom::$G['gp_catid']) : 0;
	delete_category($catid, $chanid);
}elseif ($action == 'firstadd') {
	if (checksubmit(array('submit', 'btnsubmit'))) {
		$categorys = striptags(phpcom::$G['gp_categorys']);
		if (empty($categorys['catname'])) admin_message('category_name');
		if (empty($categorys['codename'])) admin_message('category_codename');
		if (empty($categorys['subname'])) {
			$categorys['subname'] = trim($categorys['catname']);
		}
		$categorys['catid'] = makenewcatid();

		if ($categorys['sortord'] < 1) {
			$sortord = (int) DB::result_first("SELECT MAX(sortord) FROM " . DB::table('category') . " WHERE chanid='$chanid' AND depth='0'");
			$categorys['sortord'] = $sortord + 1;
		}
		$categorys['chanid'] = $chanid;
		$categorys['depth'] = 0;
		$categorys['rootid'] = $categorys['catid'];
		$categorys['target'] = $categorys['child'] = $categorys['parentid'] = 0;
		$categorys['setting'] = '';
		DB::insert('category', $categorys);
		update_categorys_cache($chanid);
		phpcom::header("Location: " . ADMIN_SCRIPT . "?m=category&chanid=$chanid");
	}
}elseif($action == 'merge' || $action == 'shift'){
	$catid = isset(phpcom::$G['gp_catid']) ? intval(phpcom::$G['gp_catid']) : 0;
	if(!$category = DB::fetch_first("SELECT * FROM " . DB::table('category') . " WHERE catid='$catid'")){
		admin_message('undefined_action');
	}
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$multiple = strcasecmp($action, 'shift') === 0 ? '' : ' multiple="multiple"';
		$adminhtml->form("m=category&action=$action&chanid=$chanid&catid=$catid");
		$adminhtml->table_header("category_{$action}_title", array('catname' => $category['catname']));
		$adminhtml->table_td(array(
				array("category_{$action}_tips", array('catname' => $category['catname']), 'colspan="3"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$category_select = '<select name="mergecat[]" class="select t70" size="20"' . $multiple . ' style="width:280px;height:300px;">';
		$category_select .= '<option value="0">-=' . adminlang("category_{$action}_select") . '=-</option>';
		$category_select .= category_select_option($chanid);
		$category_select .= '</select>';
		$adminhtml->table_td(array(
				array("category_{$action}_select", FALSE, '', '', TRUE),
				array($category_select, TRUE),
				array("category_{$action}_select_tips", FALSE, '', '', 'tips')
		));
		$adminhtml->table_td(array(
				array('category_merge_delete', FALSE, '', '', TRUE),
				array($adminhtml->radio(array('category_merge_delete_no', 'category_merge_delete_data', 'category_merge_delete_yes'), 'deleted', 0), TRUE),
				array('category_merge_delete_comments', FALSE, '', '', 'tips')
		));
		$btnsubmit = $adminhtml->submit_button();
		$adminhtml->table_td(array(
				array(' &nbsp;', TRUE),
				array($btnsubmit, TRUE, 'colspan="2"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
	}else{
		$mergecat = isset(phpcom::$G['gp_mergecat']) ? phpcom::$G['gp_mergecat'] : null;
		$deleted = isset(phpcom::$G['gp_deleted']) ? intval(phpcom::$G['gp_deleted']) : 0;
		$mergeflag = false;
		if(is_array($mergecat)){
			foreach ($mergecat as $mergeid){
				if($mergeid <= 0) continue;
				if(strcasecmp($action, 'shift') === 0){
					if(merge_category($catid, $mergeid, $module, $deleted)){
						$mergeflag = true;
					}
				}else{
					if(merge_category($mergeid, $catid, $module, $deleted)){
						$mergeflag = true;
					}
				}
			}
		}
		if($mergeflag){
			admin_succeed("category_{$action}_succeed", "m=category&chanid=$chanid");
		}else{
			admin_succeed("category_merge_failed", "m=category&action=$action&catid=$catid&chanid=$chanid");
		}
	}
}else{
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		phpcom::$G['gp_more'] = isset(phpcom::$G['gp_more']) ? trim(phpcom::$G['gp_more']) : null;
		$adminhtml->form("m=category&action=firstadd&chanid=$chanid", null, 'name="addform"');
		$adminhtml->table_header('shortcut_operate');
		$adminhtml->table_td(array(
				array('category_tips', FALSE, '')
		), NULL, FALSE, NULL, NULL, FALSE);
		$s = '<b>' . adminlang('category_add1') . '</b> ';
		$s .= adminlang('ordinal');
		$s .= ' <input class="input sortord" size="1" name="categorys[sortord]" type="text" value="0" /> ';
		$s .= adminlang('category_name');
		$s .= ' <input class="input" size="30" name="categorys[catname]" type="text" /> ';
		$s .= adminlang('category_subname');
		$s .= ' <input class="input" size="15" name="categorys[subname]" type="text" value="" /> ';
		$s .= adminlang('category_codename');
		$s .= ' <input class="input" size="15" name="categorys[codename]" type="text" value="sort001" /> ';
		$s .= ' <input type="hidden" name="categorys[chanid]" value="' . $chanid . '" /> ';
		$s .= ' <input type="hidden" name="categorys[parentid]" value="0" /> ';
		$s .=$adminhtml->submit_button(null, null, 'button');
		$adminhtml->table_td(array(
				array($s, TRUE, '')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');

		$adminhtml->form("m=category&chanid=$chanid&more=" . phpcom::$G['gp_more'], null, 'name="adminform" id="adminform"');
		$adminhtml->table_header('category_toggle', array('chanid' => $chanid));
		$classtitle = adminlang('order') . ' | ' . adminlang('catname');
		if(!empty(phpcom::$G['gp_more'])){
			$classtitle .= ' | ' . adminlang('category_codename') . ' | ' . adminlang('category_pagesize') . ' | ' . adminlang('category_prefix') . ' | ' . adminlang('category_prefixurl');
		}
		if(empty(phpcom::$G['gp_more'])){
			$adminhtml->table_td(array(
					array('catid', FALSE, 'width="5%" align="center" noWrap="noWrap"'),
					array($classtitle, TRUE, 'width="60%"'),
					array('operation', FALSE, 'width="35%"')
			), '', FALSE, ' tablerow');
		}else{
			$adminhtml->table_td(array(
					array('catid', FALSE, 'width="5%" align="center" noWrap="noWrap"'),
					array($classtitle, TRUE, 'width="80%"'),
					array('operation', FALSE, 'width="15%" noWrap="noWrap"')
			), '', FALSE, ' tablerow');
		}
		$count = 0;
		$catarray = array();
		$addsubcategory = adminlang('category_add_subcategory');
		$sql = "SELECT * FROM " . DB::table('category') . " WHERE chanid='$chanid' ORDER BY sortord, catid";
		$query = DB::query($sql);
		while ($row = DB::fetch_array($query)) {
			$catarray[$row['parentid']][$row['catid']] = $row;
			$count++;
		}
		$lastrow = 0;
		if(!isset($catarray[0])){
			$catarray[0] = array();
		}
		foreach ($catarray[0] as $key => $row) {
			showcategory($adminhtml, $row, $chanid, $addsubcategory, $count);
			echo "<tbody id=\"groupbody_{$row['catid']}\">\r\n";
			if(isset($catarray[$row['catid']])) {
				foreach ($catarray[$row['catid']] as $key => $row) {
					showcategory($adminhtml, $row, $chanid, $addsubcategory, $count);
					if(isset($catarray[$row['catid']])){
						foreach ($catarray[$row['catid']] as $key => $row) {
							showcategory($adminhtml, $row, $chanid, $addsubcategory, $count);
							if(isset($catarray[$row['catid']])){
								foreach ($catarray[$row['catid']] as $key => $row) {
									showcategory($adminhtml, $row, $chanid, '', $count);
								}
							}
						}
					}
				}
			}
			echo '</tbody>';
		}
		$btnsubmit = $adminhtml->submit_button();
		$adminhtml->table_td(array(
				array($btnsubmit, TRUE, 'align="center" colspan="3"')
		), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_end('</form>');
	}else{
		$more = isset(phpcom::$G['gp_more']) ? phpcom::$G['gp_more'] : '';
		$categorys = isset(phpcom::$G['gp_categorys']) ? striptags(phpcom::$G['gp_categorys']) : array();

		foreach ($categorys as $catid => $category) {
			if(!empty($category['catname'])){
				$category['sortord'] = intval($category['sortord']);
				if(isset($category['prefixurl'])){
					$prefixurl = rtrim(trim($category['prefixurl']), "/\\");
					if($prefixurl && !parse_url($prefixurl, PHP_URL_SCHEME)){
						$prefixurl = "http://$prefixurl";
					}
					$category['prefixurl'] = $prefixurl;
				}
				if(isset($category['pagesize'])) $category['pagesize'] = intval($category['pagesize']);
				DB::update('category', $category, "catid='$catid'");
			}
		}
		update_categorys_cache($chanid);
		admin_succeed('update_succeed', "m=category&more=$more&chanid=$chanid");
	}
}
admin_footer();

function merge_category($catid, $mergeid = 0, $module = 'article', $delete = 0){
	if(empty($catid) || empty($mergeid) || empty($module)) return false;
	if($mergeid == $catid) return false;
	if($category = DB::fetch_first("SELECT catid, chanid, rootid, depth, child FROM " . DB::table('category') . " WHERE catid='$mergeid'")){
		$rootid = $category['rootid'];
		DB::update('threads', array('rootid' => $rootid, 'catid' => $mergeid), "catid='$catid'");
		DB::update("{$module}_thread", array('rootid' => $rootid, 'catid' => $mergeid), "catid='$catid'");
		DB::update('topic_data', array('catid' => $mergeid), "catid='$catid'");
		if($delete){
			DB::delete('thread_class_data', "catid='$catid'");
			if($delete === 2 && empty($category['child'])){
				if(phpcom_admincp::permission('category_delete')){
					DB::delete('category', array('catid' => $catid));
					delete_thtead_class(0, $catid);
				}
			}
		}
		return true;
	}
	return false;
}

function delete_thtead_class($classid, $catid = false){
	if($classid <= 0 && (false === $catid || null === $catid)) return false;
	$condition = empty($classid) ? "t1.catid='$catid'" : "t1.classid='$classid'";
	
	DB::query("DELETE t1, t2 FROM " . DB::table('thread_class') . " AS t1
			LEFT JOIN " . DB::table('thread_class_data') . " AS t2 ON t2.classid=t1.classid
			WHERE $condition");
	return true;
}

function add_update_threadclass($catid = 0, $chanid = 0){
	$threadclassnew = isset(phpcom::$G['gp_threadclassnew']) ? phpcom::$G['gp_threadclassnew'] : null;
	$threadclass = isset(phpcom::$G['gp_threadclass']) ? phpcom::$G['gp_threadclass'] : null;
	$delete = isset(phpcom::$G['gp_delete']) ? phpcom::$G['gp_delete'] : null;
	$updatecache = false;
	if(!empty($threadclass)){
		$updatecache = true;
		foreach ($threadclass as $classid => $classes) {
			if($delete && isset($delete[$classid]) && $delete[$classid] == $classid){
				//DB::delete('thread_class', "classid='$classid'");
				//DB::delete('thread_class_data', "classid='$classid'");
				delete_thtead_class($classid);
			}else{
				if(empty($classes['name'])){
					unset($classes['name']);
				}else{
					$classes['name'] = trim($classes['name']);
				}
				$classes['alias'] = trim($classes['alias']);
				$classes['about'] = trim($classes['about']);
				$classes['icon'] = trim($classes['icon']);
				$classes['ordinal'] = intval($classes['ordinal']);
				DB::update('thread_class', $classes, "classid='$classid'");
			}
		}
	}
	if(!empty($threadclassnew) && isset($threadclassnew['name'])){
		foreach ($threadclassnew['name'] as $key => $value) {
			if(!empty($value)){
				DB::insert('thread_class', array(
				'chanid' => $chanid,
				'catid' => $catid,
				'name' => trim($value),
				'alias' => trim($threadclassnew['alias'][$key]),
				'about' => trim($threadclassnew['about'][$key]),
				'icon' => trim($threadclassnew['icon'][$key]),
				'ordinal' => intval($threadclassnew['ordinal'][$key])
				));
				$updatecache = true;
			}
		}
	}
	if($updatecache){
		phpcom_cache::updater('thread_class', $chanid);
	}
}

function updatecategoryimage($catid) {
	if($catid && !empty(phpcom::$G['gp_imagetmpid'])){
		$tmpid = intval(phpcom::$G['gp_imagetmpid']);
		$images = array();
		$flag = false;
		if($tmpid > 0 && ($tmp = Attachment::getUploadTemp($tmpid))){
			if(!empty($tmp['attachment']) && phpcom::$G['uid'] == $tmp['uid']){
				$images['imageurl'] = $tmp['attachment'];
				$images['remote'] = Attachment::ftpOneUpload($tmp);
				DB::delete('upload_temp', "tmpid='{$tmp['tmpid']}'");
				$flag = true;
			}
		}else{
			$flag = ($tmpid == -1);
		}

		if($img = DB::fetch_first("SELECT catid,remote,imageurl FROM " . DB::table('category') . " WHERE catid='$catid' LIMIT 1")){
			if(!empty($images)){
				DB::update('category', $images, "catid='$catid'");
			}
			$unlinks = array('dirname' => 'image');
			if($flag && !empty($img['imageurl'])){
				$unlinks['attachment'] = trim($img['imageurl']);
				$unlinks['thumb'] = 0;
				$unlinks['remote'] = $img['remote'];
				Attachment::uploadUnlink($unlinks);
			}
		}

	}
}

function showcategory($adminhtml, $row, $chanid, $addsubcategory='', $max=0) {
	static $index = 0;
	$index++;
	$varname = "categorys[{$row['catid']}]";
	$editurl = "m=category&action=edit&catid={$row['catid']}&chanid=$chanid";
	$edit = '<input type="hidden" name="catid[]" value="' . $row['catid'] . '" />';
	$edit .= $adminhtml->edit_word('edit', $editurl, ' | ');
	$edit .= $adminhtml->edit_word('merge', "m=category&action=merge&catid={$row['catid']}&chanid=$chanid", ' | ');
	$edit .= $adminhtml->edit_word('shift', "m=category&action=shift&catid={$row['catid']}&chanid=$chanid", ' | ');
	$edit .= $adminhtml->del_word('delete', "m=category&action=del&catid={$row['catid']}&chanid=$chanid");
	$add_subcategory = $addsubcategory ? '</span>&nbsp;<i class="add-link"><b>+</b> <a href="?action=addsub&m=category&catid=' . $row['catid'] . '&chanid=' . $chanid . '" id="addlinks_' . $row['catid'] . '" style="display:none;" class="links">' . $addsubcategory . '</a></i>' : '</span>';
	if(!empty(phpcom::$G['gp_more'])){
		$add_subcategory = ' <input class="input" size="15" name="'.$varname.'[codename]" type="text" value="' . htmlcharsencode($row['codename']) . '" />';
		$add_subcategory .= ' <input class="input sortord" size="1" name="'.$varname.'[pagesize]" type="text" value="' . intval($row['pagesize']) . '" />';
		$add_subcategory .= ' <input class="input" size="15" name="'.$varname.'[prefix]" type="text" value="' . htmlcharsencode($row['prefix']) . '" />';
		$add_subcategory .= ' <input class="input" size="30" name="'.$varname.'[prefixurl]" type="text" value="' . htmlcharsencode($row['prefixurl']) . '" /></span>';
	}
	$row['color'] = $row['color'] ? ' style="color:' . $row['color'] . '"' : '';
	if ($row['depth'] == 1) {
		$depth = $index >= $max ? '<i class="tdline1 tdlast"></i>' : '<i class="tdline1"></i>';
	} elseif ($row['depth'] == 2) {
		$depth = $index >= $max ? '<i class="tdline2 tdlast"></i>' : '<i class="tdline2"></i>';
	} elseif ($row['depth'] == 3) {
		$depth = $index >= $max ? '<i class="tdline3 tdlast"></i>' : '<i class="tdline3"></i>';
	} else {
		$depth = "<i id=\"toggle_{$row['catid']}\" class=\"toggle hide-icon\" onclick=\"toggle_display('{$row['catid']}')\"></i>";
	}
	$adminhtml->table_td(array(
			array('<em class="tiny"><a href="?'.$editurl.'"' . $row['color'] . '>' . $row['catid'] . '</a></em>', TRUE, 'align="center"'),
			array($depth . '<span class="span-input"><input class="input sortord" size="1" name="'.$varname.'[sortord]" type="text" value="' . $row['sortord'] . '" />
					<input class="input" size="15" name="'.$varname.'[catname]" type="text" value="' . htmlcharsencode($row['catname']) . '" />' . $add_subcategory, TRUE),
			array($edit, TRUE)
	), '', FALSE, ' tdborder', $row['depth'] == 3 ? '' : ' onmouseover="toggleDisplay(\'addlinks_' . $row['catid'] . '\',\'show\')" onmouseout="toggleDisplay(\'addlinks_' . $row['catid'] . '\',\'hide\')"');
}

function delete_category($catid, $chanid = 0) {
	if(!phpcom_admincp::permission('category_delete')){
		admin_message('action_delete_denied');
	}
	if(empty($catid)){
		admin_succeed('delete_succeed', "m=category&chanid=$chanid");
		return;
	}
	//检测父级分类是否存在，0=不存在
	$parentid = (int)DB::result_first("SELECT COUNT(*) FROM " . DB::table('category') . " WHERE parentid='$catid'");
	//如果存在父级分类，抛出错误信息
	if ($parentid) {
		admin_message('category_delete_failed');
	}
	if(DB::result_first("SELECT tid FROM " . DB::table('threads') . " WHERE catid='$catid' OR rootid='$catid' LIMIT 1")){
		admin_message('category_delete_exist_thread');
	}
	//获取要删除分类的父级分类ID
	$parentid = (int)DB::result_first("SELECT parentid FROM " . DB::table('category') . " WHERE catid='$catid'");
	//开始删除操作
	if ($catid) {
		DB::delete('category', array('catid' => $catid));
		delete_thtead_class(0, $catid);
	}
	//如果 $parentid=0，删除的是一级分类，就不用更新
	//如果 $parentid>0，更新父级分类数据
	if ($parentid) {
		$child = (int) DB::result_first("SELECT COUNT(*) FROM " . DB::table('category') . " WHERE parentid='$parentid'");
		DB::update('category', array('child' => $child), 'catid=' . $parentid);
	}
	
	update_categorys_cache($chanid);
	admin_succeed('delete_succeed', "m=category&chanid=$chanid");
}

function update_categorys_cache($chanid) {
	phpcom_cache::updater('category', $chanid);
}

function makenewcatid() {
	$catid = (int) DB::result_first("SELECT MAX(catid) FROM " . DB::table('category') . " WHERE 1=1");
	return $catid + 1;
}
?>
