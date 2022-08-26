<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : template.php  2012-9-14
 */
!defined('IN_ADMINCP') && exit('Access denied');

phpcom::$G['lang']['admin'] = 'misc';
$tplbasedir = realpath(PATH_TEMPLATE);
admin_header('menu_template');
$adminhtml = phpcom_adminhtml::instance();
$adminhtml->activetabs($action == 'setting' ? 'global': 'tools');
$navarray = array(
		array('title' => 'menu_template', 'url' => '?m=template', 'id' => 'template')
);
$adminhtml->navtabs($navarray, 'template');
if ($action == 'edit') {
	$dirname = isset(phpcom::$G['gp_dirname']) ? trim(phpcom::$G['gp_dirname']) : '';
	$child = isset(phpcom::$G['gp_child']) ? trim(phpcom::$G['gp_child']) : '';
	$child = empty($child) ? '' : $child;
	$file = isset(phpcom::$G['gp_file']) ? trim(phpcom::$G['gp_file']) : '';
	$basedir = realpath(rtrim("$tplbasedir/$dirname", '/\\ *.'));
	$current = realpath(rtrim("$basedir/$child", '/\\ *.'));
	$filename = realpath("$current/$file");
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$adminhtml->form("m=template&action=edit&dirname=$dirname&child=$child&file=$file");
		$adminhtml->table_header('template_edit');
		$adminhtml->table_td(array(array('template_edit_tips', FALSE)), NULL, FALSE, NULL, NULL, FALSE);
		if(strncmp($basedir, $tplbasedir, strlen($tplbasedir)) == 0 
				&& strncmp($filename, $tplbasedir, strlen($tplbasedir)) == 0
				&& file_exists($filename)){
			$url = "?m=template&action=list&dirname=$dirname&child=$child";
			$adminhtml->table_td(array(
					array("<a href=\"$url\">".adminlang('template_current_file'). $filename.'</a>', TRUE)
			));
			$file_content = htmlcharsencode(templateReader($filename));
			$adminhtml->table_td(array(
					array('<textarea name="file_content" style="width:100%;" rows="25" wrap="OFF" id="file_content">'.$file_content.'</textarea>', TRUE)
			));
			
			$adminhtml->table_td(array(
					array($adminhtml->submit_button(), TRUE, 'align="center"')
			), NULL, FALSE, NULL, NULL, FALSE);
		}
		$adminhtml->table_end('</form>');
	}else{
		if(empty(phpcom::$config['admincp']['template'])){
			admin_message('template_action_invalid');
		}else{
			if(strncmp($basedir, $tplbasedir, strlen($tplbasedir)) == 0
					&& strncmp($filename, $tplbasedir, strlen($tplbasedir)) == 0
					&& file_exists($filename)){
				$file_content = stripslashes(phpcom::$G['gp_file_content']);
				templateWriter($filename, $file_content);
			}
			admin_succeed('template_update_succeed', "m=template&action=edit&dirname=$dirname&child=$child&file=$file");
		}
	}
}elseif ($action == 'list') {
	$adminhtml->table_header('template_tips_subject', 2);
	$adminhtml->table_td(array(array('template_tips', FALSE, 'colspan="2"')), NULL, FALSE, NULL, NULL, FALSE);
	$adminhtml->table_td(array(
			array('template_file_dir', FALSE, 'width="15%"'),
			array('template_description', FALSE)
	), '', FALSE, ' tablerow', NULL, FALSE);
	
	$dirname = isset(phpcom::$G['gp_dirname']) ? trim(phpcom::$G['gp_dirname']) : '';
	$child = isset(phpcom::$G['gp_child']) ? trim(phpcom::$G['gp_child']) : '';
	$basedir = realpath(rtrim("$tplbasedir/$dirname", '/\\ *.'));
	$current = realpath(rtrim("$basedir/$child", '/\\ *.'));
	if($basedir == $current){
		$child = 0;
	}else{
		$child = strtolower(basename($current));
	}
	if(strncmp($basedir, $tplbasedir, strlen($tplbasedir)) == 0 && strncmp($current, $tplbasedir, strlen($tplbasedir)) == 0){
		if($child){
			$url = "?m=template&action=list&dirname=$dirname&child=../";
			$adminhtml->table_td(array(
					array("<a href=\"$url\">".adminlang('template_return_dir').'</a>', TRUE),
					array('&nbsp;', TRUE)
			));
		}
		$template = array('Template' => array());
		if(is_file($basedir.'/template.xml')){
			$xml = new SimpleXMLExtended("$basedir/template.xml", null, true);
			$template = $xml->toArray();
		}
		
		foreach (glob("$current/*") as $filename) {
			$basename = strtolower(basename($filename));
			$key = substr($basename, 0, strrpos($basename, '.'));
			$url = "?m=template";
			if(is_dir($filename)){
				$caption = '';
				if(isset($template['Template'][$basename]['attributes']['caption'])){
					$caption = $template['Template'][$basename]['attributes']['caption'];
				}elseif(isset($template['Template'][$basename][0]['attributes']['caption'])){
					$caption = $template['Template'][$basename][0]['attributes']['caption'];
				}
				$url .= "&action=list&dirname=$dirname&child=$basename";
			}else{
				if(!in_array(substr($basename, -3), array('css', 'htm', '.js'))){
					continue;
				}
				if($child = strtolower($child)){
					$caption = isset($template['Template'][$child]['item'][$key]) ? $template['Template'][$child]['item'][$key]['text'] : '';
				}else{
					$caption = isset($template['Template']['item'][$key]) ? $template['Template']['item'][$key]['text'] : '';
				}
				if(empty($caption) && substr($basename, -4) == '.css'){
					$caption = adminlang('template_file_css');
				}
				if(empty($caption) && substr($basename, -4) == '.htm'){
					$caption = adminlang('template_file_htm');
				}
				if(empty($caption) && substr($basename, -3) == '.js'){
					$caption = adminlang('template_file_js');
				}
				if(empty($child)) $child = '';
				$url .= "&action=edit&dirname=$dirname&child=$child&file=$basename";
			}
			$adminhtml->table_td(array(
					array("<a href=\"$url\">".$basename.'</a>', TRUE),
					array($caption, TRUE)
			));
		}
		$adminhtml->table_end();
	}else{
		admin_message('template_invalid_action');
	}
	
}elseif ($action == 'default') {
	$templatedir = isset(phpcom::$G['gp_name']) ? trim(phpcom::$G['gp_name']) : 'default';
	$templatedir = $templatedir ? $templatedir : 'default';
	DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES ('templatedir', '$templatedir', 'string')");
	phpcom_cache::updater('setting');
	templateClear(PHPCOM_ROOT . '/data/template');
	admin_succeed('template_default_succeed', 'm=template');
}else{
	if (!checksubmit(array('submit', 'btnsubmit'))) {
		$adminhtml->table_header('template_tips_subject', 5);
		$adminhtml->table_td(array(array('template_tips', FALSE, 'colspan="5"')), NULL, FALSE, NULL, NULL, FALSE);
		$adminhtml->table_td(array(
				array('template_title', FALSE, 'width="20%" noWrap="noWrap"'),
				array('template_directory', FALSE, 'width="15%"'),
				array('template_version', FALSE, 'width="10%"'),
				array('template_description', FALSE),
				array('template_operation', FALSE, 'width="15%"')
		), '', FALSE, ' tablerow', NULL, FALSE);
		
		$files = glob("$tplbasedir/*", GLOB_ONLYDIR|GLOB_NOSORT);
		foreach ($files as $filename) {
			$dirname = basename($filename);
			if(is_dir($filename) && strcasecmp($dirname, 'admin') && strcasecmp($dirname, 'images')){
				$template = array('Title' => $dirname, 'Description' => '', 'Version' => '1.0.0', 'Copyright' => '');
				if(is_file($filename.'/template.xml')){
					$xml = new SimpleXMLExtended("$filename/template.xml", null, true);
					$template = $xml->toArray();
				}
				$icon = '<span>';
				if(strcasecmp(phpcom::$setting['templatedir'], $dirname) == 0){
					$icon = ' <img src="misc/images/icons/valid.gif" /> <span style="font-weight:bold;">';
				}
				$edit = $adminhtml->edit_word('template_set_default', "m=template&action=default&name=$dirname");
				$url = "?m=template&action=list&dirname=$dirname";
				$adminhtml->table_td(array(
						array($icon . $template['Title'] . '</span>', TRUE),
						array("<a href=\"$url\">".$dirname.'</a>', TRUE),
						array($template['Version'], TRUE),
						array($template['Description'], TRUE),
						array($edit, TRUE),
				));
			}
		}
		$adminhtml->table_end();
	}else{
		
	}
}
admin_footer();

function templateReader($filename)
{
	$contents = @file_get_contents($filename);
	if(empty(phpcom::$config['template']['encoding']) && strcasecmp(CHARSET, 'utf-8') == 0){
		$contents = iconv('GBK', 'UTF-8//TRANSLIT//IGNORE', $contents);
	}
	return $contents;
}

function templateWriter($filename, $content = '', $mode = 'w')
{
	if(empty(phpcom::$config['template']['encoding']) && strcasecmp(CHARSET, 'utf-8') == 0){
		$content = iconv('UTF-8', 'GBK//TRANSLIT//IGNORE', $content);
	}
	if ($filename = trim($filename)) {
		$file = @fopen($filename, $mode);
		@fwrite($file, $content);
		@fclose($file);
	}
	if (!is_file($filename)) {
		die('Sorry,' . $filename . ' file write in failed!');
	}
}

function templateClear($dir) {
	if($d = @dir($dir)) {
		$dir = rtrim($dir, '/\ ');
		while($entry = $d->read()) {
			if ($entry !== '.' && $entry !== '..') {
				$filename = $dir.'/'.$entry;
				if(is_file($filename)) {
					@unlink($filename);
				}
			}
		}
		$d->close();
		@touch($dir.'/index.htm');
	}
}
?>