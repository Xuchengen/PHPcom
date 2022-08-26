<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : tags.php    2011-6-16 10:56:07
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';
$navarray = array(
	array('title' => 'menu_tags', 'url' => '?m=tags', 'name' => 'edit'),
	array('title' => 'menu_tags_add', 'url' => '?action=add&m=tags', 'name' => 'add')
);
admin_header('menu_tags', $action ? $admintitle : '');
$adminhtml = phpcom_adminhtml::instance();
if ($action == 'add' || $action == 'edit') {
	$adminhtml->activetabs('global');
	$adminhtml->navtabs($navarray, $action, 'nav_tabs', 'tags');
	$tagid = isset(phpcom::$G['gp_tagid']) ? intval(phpcom::$G['gp_tagid']) : 0;
	if (!checksubmit(array('btnsubmit', 'submit'))) {
		
		$threadtags = array('tagid' => 0, 'tagname' => '', 'ishot' => 0);
		if ($action == 'edit' && $tagid) {
			$threadtags = DB::fetch_first("SELECT * FROM " . DB::table('tags') . " WHERE tagid='$tagid'");
		}
		$adminhtml->form("m=tags&action=$action&tagid=$tagid");
		$adminhtml->table_header('threadtags_' . $action, 3);
		$adminhtml->table_setting('threadtags_tagname', 'threadtags[tagname]', $threadtags['tagname'], 'text');
		$adminhtml->table_setting('threadtags_ishot', 'threadtags[ishot]', $threadtags['ishot'], 'radio');
		$adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
		$adminhtml->table_end('</form>');
	} else {
		$threadtags = striptags(phpcom::$G['gp_threadtags']);
		if (empty($threadtags['tagname'])) {
			admin_message('threadtags_tagname_invalid');
		}
		$tags = $threadtags['tagname'];
		$ishot = intval($threadtags['ishot']);
		if ($action == 'edit') {
			$result = DB::fetch_first("SELECT tagid,tagnum FROM " . DB::table('tags') . " WHERE tagid='$tagid'");
			if ($result['tagnum']) {
				DB::update('tags', array('ishot' => $ishot), array('tagid' => $tagid));
			} else {
				DB::update('tags', $threadtags, array('tagid' => $tagid));
			}
			admin_succeed('threadtags_edit_succeed', "m=tags&action=edit&tagid=$tagid");
		} else {
			insert_new_tags($tags, $ishot);
			admin_succeed('threadtags_add_succeed', 'm=tags');
		}
	}
} elseif ($action == 'del') {
	$checkboxid = isset(phpcom::$G['gp_checkboxid']) ? phpcom::$G['gp_checkboxid'] : null;
	if(!empty($checkboxid)){
		if ($checkboxids = implodeids($checkboxid)) {
			$condition = "tagid IN($checkboxids)";
			DB::delete('tags', $condition);
			DB::delete('tagdata', $condition);
		}
	}
	admin_succeed('threadtags_delete_succeed', 'm=tags');
} elseif ($action == 'insert') {
	$adminhtml->table_header('threadtags_select', 5);
	$adminhtml->table_td(array(
		array('threadtags_insert_input', FALSE, 'colspan="5" align="left"')
	), NULL, FALSE, NULL, NULL, FALSE);
	$word = isset(phpcom::$G['gp_word']) ? stripstring(phpcom::$G['gp_word']) : '';
	$condition = $queryurl = '';
	if (!empty($word)) {
		$word = str_replace('_', '\_', $word);
		$condition = " WHERE `tagname` LIKE '%$word%'";
		$queryurl = "&word=$word";
	}
	$totalrec = DB::result_first("SELECT COUNT(*) FROM " . DB::table('tags') . $condition);
	$pagenow = $page;  // 当前页
	$pagesize = 100; //intval(phpcom::$config['admincp']['pagesize']);  // 每页大小
	$pagecount = @ceil($totalrec / $pagesize);  //计算总页数
	$pagenow > $pagecount && $pagenow = 1;
	$pagestart = floor(($pagenow - 1) * $pagesize);
	$sql = DB::buildlimit("SELECT * FROM " . DB::table('tags') . "$condition ORDER BY tagid DESC", $pagesize, $pagestart);
	$query = DB::query($sql);
	$i = $n = $m = 0;
	$anchortitle = adminlang('threadtags_insert_title');
	while ($row = DB::fetch_array($query)) {
		$tagid = $row['tagid'];
		$m = ($i % 2 == 0) ? 2 : 1;
		if ($i === 0) {
			echo '<tr>';
		}
		echo '<td class="tablerow', $m, '" width="20%">';
		echo '<a class="lst" href="javascript:void(0)" onclick="tagsadd(\'', str_replace("'", "\'", $row['tagname']), '\')" title="', $anchortitle, '">', $row['tagname'], '(', $row['tagnum'], ')</a>';
		echo '</td>';
		$i++;
		if ($i % 5 == 0) {
			echo '</tr>';
			if ($i < $pagesize && $i < $totalrec) {
				echo '<tr>';
			}
		}
	}
	$ii = $i % 5;
	if ($ii > 0) {
		for ($index = 1; $index <= (5 - $ii); $index++) {
			$m = ($index % 2 == 0) ? 2 : 1;
			echo '<td class="tablerow', $m, '" width="20%">&nbsp;</td>';
		}
		echo '</tr>';
	}
	$showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=tags&action=insert&$queryurl") . '</var>';
	$adminhtml->table_td(array(
	array($showpage, TRUE, 'colspan="5" align="right" id="pagecode"')
	), NULL, FALSE, NULL, NULL, FALSE);
	$adminhtml->table_end();
	$upperlimit = adminlang('threadtags_insert_upperlimit');
	echo <<<EOT
<style type="text/css">
    html {padding-top:0;}
</style>
<script type="text/javascript">
if($('crumbnav')){
    $('crumbnav').style.display='none';
}
var tagObj=parent.document.getElementById('tagstring');
document.getElementById("hasinserted").value=tagObj.value;
function closeTagWindow(){
    parent.hideMenu('open_dialog_tags','dialog');
}
function tagsadd(tagName) {
    var tags;
    var glue = ' ';
    if (tagObj.value.length>0) {
        if(tagObj.value.indexOf(',') > -1) glue = ',';
        tags=tagObj.value.split(glue)
        if(tags.length<5){
            for (i=0;i<tags.length;i++){
                if (tags[i].toLowerCase()==tagName.toLowerCase()) {return false;}
            }
            tagObj.value+=glue+tagName;
        }else{
            return showMessage('$upperlimit');
        }
    }else{
        tagObj.value+=tagName;
    }
    document.getElementById("hasinserted").value=tagObj.value;
}
</script>
EOT;
	exit('</body></html>');
} else {
	$adminhtml->activetabs('global');
	$adminhtml->navtabs($navarray, 'edit', 'nav_tabs', 'tags');
	$adminhtml->form('m=tags', array(array('action', 'del')), 'onkeydown="return formdown()"');
	$adminhtml->table_header('threadtags', 5);
	$adminhtml->table_td(array(
	array(' ', TRUE, 'colspan="5" align="left" id="showpage"')
	), NULL, FALSE, NULL, NULL, FALSE);
	$word = isset(phpcom::$G['gp_word']) ? stripstring(phpcom::$G['gp_word']) : '';
	$condition = ' 1=1';
	$queryurl = '';
	if ($action == 'search' && $word) {
		$word = str_replace('_', '\_', $word);
		$condition = " `tagname` LIKE '%$word%'";
		$queryurl = implodeurl(array('action' => 'search', 'word' => $word), '&');
	}
	$totalrec = DB::result_first("SELECT COUNT(*) FROM " . DB::table('tags') . " WHERE $condition");
	$pagenow = $page;  // 当前页
	$pagesize = 100; //intval(phpcom::$config['admincp']['pagesize']);  // 每页大小
	$pagecount = @ceil($totalrec / $pagesize);  //计算总页数
	$pagenow > $pagecount && $pagenow = 1;
	$pagestart = floor(($pagenow - 1) * $pagesize);
	$sql = DB::buildlimit("SELECT * FROM " . DB::table('tags') . " WHERE $condition ORDER BY tagid DESC", $pagesize, $pagestart);
	$query = DB::query($sql);
	$i = $n = $m = 0;
	while ($row = DB::fetch_array($query)) {
		$tagid = $row['tagid'];
		$m = ($i % 2 == 0) ? 2 : 1;
		if ($i === 0) {
			echo '<tr>';
		}
		echo '<td class="tablerow', $m, '" width="20%">';
		echo '<input type="checkbox" class="checkbox" name="checkboxid[]" value="', $tagid, '" /> ';
		echo '<a class="lst" href="?action=edit&m=tags&tagid=', $tagid, '">', $row['tagname'], '(', $row['tagnum'], ')</a>';
		echo '</td>';
		$i++;
		if ($i % 5 == 0) {
			echo '</tr>';
			if ($i < $pagesize && $i < $totalrec) {
				echo '<tr>';
			}
		}
	}
	$ii = $i % 5;
	if ($ii > 0) {
		for ($index = 1; $index <= (5 - $ii); $index++) {
			$m = ($index % 2 == 0) ? 2 : 1;
			echo '<td class="tablerow', $m, '" width="20%">&nbsp;</td>';
		}
		echo '</tr>';
	}
	$adminhtml->table_td(array(
	array($adminhtml->checkall() . ' ' . $adminhtml->del_submit(), TRUE, 'colspan="5"')
	));
	$showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=tags$queryurl") . '</var>';
	$adminhtml->table_td(array(
	array($showpage, TRUE, 'colspan="5" align="right" id="pagecode"')
	), NULL, FALSE, NULL, NULL, FALSE);
	$adminhtml->table_end('</form>');
	$adminhtml->showpagescript();
}
admin_footer();

function insert_new_tags($tags, $ishot = 0) {
	$tags = stripstring($tags);
	if (!$tags) {
		return '';
	}
	$tagarray = array();
	$tags = str_replace(array(chr(0xa3) . chr(0xac), chr(0xa1) . chr(0x41), chr(0xef) . chr(0xbc) . chr(0x8c)), ',', $tags);
	if (strexists($tags, ',')) {
		$tagarray = array_unique(explode(',', $tags));
	} else {
		$tags = str_replace(array(chr(0xa1) . chr(0xa1), chr(0xa1) . chr(0x40), chr(0xe3) . chr(0x80) . chr(0x80)), ' ', $tags);
		$tagarray = array_unique(explode(' ', $tags));
	}
	$count = 0;
	foreach ($tagarray as $tagname) {
		$tagname = trim($tagname);
		if (preg_match('/^([\x7f-\xff_-]|\w|\s){2,20}$/', $tagname)) {
			$result = DB::fetch_first("SELECT tagid FROM " . DB::table('tags') . " WHERE tagname='$tagname'");
			if (!empty($result['tagid'])) {
				DB::query("INSERT INTO " . DB::table('tags') . " (tagname, tagnum, ishot) VALUES ('$tagname', '0', '$ishot')");
			}
			$count++;
			if ($count > 4) {
				unset($tagarray);
				break;
			}
		}
	}
}

?>
