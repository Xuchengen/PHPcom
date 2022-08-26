<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : adminhtml.php    2011-7-5 23:01:04
 */
!defined('IN_ADMINCP') && exit('Access denied');

class phpcom_adminhtml {

	var $count = 0;
	var $scriptname = '';
	var $vars = NULL;
	var $tablesetmode = TRUE;
	var $disabledtips = FALSE;

	function __construct() {
		$this->scriptname = ADMIN_SCRIPT;
	}

	public static function &instance() {
		static $_instance;
		if (empty($_instance)) {
			$_instance = new phpcom_adminhtml();
		}
		return $_instance;
	}

	function setvars($var) {
		$this->vars = $var;
	}

	function navtabs($array = array(), $active = 'first', $id = 'nav_tabs', $search = '') {
		echo '<div class="tab-box"><ul id="' . $id . '">';
		foreach ($array as $key => $value) {
			if (!is_array($value)) continue;
			$title = adminlang($value['title'], $this->vars);
			$name = (isset($value['name']) && $value['name']) ? $value['name'] : (isset($value['id']) ? $value['id'] : 'first');
			$url = (isset($value['url']) && $value['url']) ? $value['url'] : 'javascript:void(0)';
			$url = substr($url, 0, 1) == '?' ? ADMIN_SCRIPT . $url : $url;
			$actives = ($name == $active) ? ' class="active"' : '';
			$onclick = (isset($value['onclick']) && $value['onclick']) ? ' onclick="' . $value['onclick'] . '"' : '';
			$tabid = (isset($value['id']) && $value['id']) ? ' id="nav_' . $value['id'] . '"' : '';
			echo '<li' . $actives . $tabid . $onclick . ' title="' . $title . '"><a href="' . $url . '" onfocus="this.blur()">' . $title . '</a></li>';
		}
		if ($search) {
			$chanid = isset(phpcom::$G['gp_chanid']) ? intval(phpcom::$G['gp_chanid']) : phpcom::$G['channelid'];
			$word = isset(phpcom::$G['gp_word']) ? phpcom::$G['gp_word'] : '';
			echo '<li class="lastsearch"><form method="post" name="searchform" action="?m=' . $search . "&action=search&chanid=$chanid\">";
			echo '<input type="hidden" name="formtoken" value="' . formtoken() . '">';
			echo '<input type="text" class="input sh" name="word" value="', htmlcharsencode($word), '" />';
			echo '<button class="button" type="submit" name="btnsearch" value="yes">', adminlang('search'), '</button>';
			echo '</form></li>';
		}
		echo "</ul></div>\n<div style=\"clear:both\"></div>\n";
	}

	function form($action = '', $hidden = '', $attribs = '') {
		$attribs = $attribs ? $attribs : 'name="adminform" id="adminform"';
		$attribs = substr($attribs, 0, 5) == 'name=' ? $attribs : 'name="adminform" id="adminform"' . $attribs;
		$action = substr($action, 0, 5) == 'http:' ? $action : $this->scriptname . '?' . $action;
		$method = stripos($attribs, 'method=') ? '' : 'method="post" ';
		$s = '<form ' . $method . $attribs . ' action="' . $action . '">';
		$s .= '<input type="hidden" name="formtoken" value="' . formtoken() . '" />';
		if (is_array($hidden)) {
			foreach ($hidden as $value) {
				$id = $value[0] == 'posttime' ? ' id="posttime"' : ($value[0] == 'channelid' ? ' id="channelid"' : '');
				$id = empty($id) && $value[0] == 'tid' ? ' id="tid"' : $id;
				$id = empty($id) && $value[0] == 'uid' ? ' id="uid"' : $id;
				if(isset($value[2]) && $value[2]){
					$id = ' id="'.trim($value[2]).'"';
				}
				$s .= '<input type="hidden" name="' . $value[0] . '"'.$id.' value="' . $value[1] . '" />';
			}
		}
		echo "$s\n";
	}

	function table_header($message = '', $vars = array(), $id = '', $classname = 'tableborder', $hidden = 0, $tid = '', $extra = null) {
		$this->count = 0;
		$vars = is_array($vars) ? $vars : array();
		$id = $id ? ' id="' . $id . '"' : '';
		$tid = $tid ? ' id="' . $tid . '"' : '';
		$class = $classname ? ' class="' . $classname . '"' : '';
		$display = $hidden === 1 || $hidden === true ? ' style="display: none"' : '';
		$s = '<table cellspacing="0" cellpadding="3" align="center" border="0"' . $class . $id . $display . '>';
		if ($message) {
			$subtitle = '';
			if (strpos($message, ' - ')) {
				$titarray = explode(' - ', $message);
				list($message, $subtitle) = $titarray;
			}
			if($this->vars){
				$vars = array_merge($vars, (array)$this->vars);
			}
			if($hidden !== 2) {
				$s .= "<caption>" . adminlang($message, $vars) . ($subtitle ? " - $subtitle" : '') . $extra . "</caption>";
			}
		}
		echo $s;
	}

	function table_th($args) {
		$this->count = 0;
		$s = '<tr>';
		foreach ($args as $value) {
			$name = $value[0];
			$attr = isset($value[1]) ? ' ' . $value[1] : '';
			$vars = isset($value[2]) ? $value[2] : $this->vars;
			if ($name) {
				$name = adminlang($name, $vars);
			}
			$s .= "<th$attr>$name</th>";
		}
		$s .= "</tr>\n";
		echo $s;
	}

	function table_td($args, $id = '', $display = FALSE, $td = '', $tr = '', $trm = TRUE) {
		$this->count++;
		$m = ($this->count % 2 == 0) ? 2 : 1;
		$class = 'tablerow' . $m . $td;
		$id = $id ? ' id="' . $id . '"' : '';
		$display = $display ? ' style="display:none;"' : '';
		$trm = $trm ? $m : 3;
		$s = "<tr$id$display class=\"tr-$trm\"$tr>";
		foreach ($args as $value) {
			$name = $value[0];
			$vars = (isset($value[1]) && $value[1]) ? $value[1] : $this->vars;
			$attr = (isset($value[2]) && $value[2]) ? ' ' . $value[2] : '';
			$str = (isset($value[3]) && $value[3]) ? $value[3] : '';
			if ($name && $vars !== TRUE) {
				$name = adminlang($name, $vars);
			}
			if (isset($value[4]) && $value[4]) {
				if($value[4] === TRUE){
					$s .= "<td class=\"$class td-h\"$attr>$str$name</td>";
				}else{
					$s .= "<td class=\"$class $value[4]\"$attr>$str$name</td>";
				}
			} else {
				$s .= "<td class=\"$class\"$attr>$str$name</td>";
			}
		}
		$s .= "</tr>\n";
		if (trim($td) == 'tablerow') {
			$this->count = 0;
		}
		echo $s;
	}

	function table_end($code = '') {
		echo "</table>$code\n";
	}

	function table_setting($setname, $varname, $value, $type = 'text', $onclick = '', $id = '', $checkboxvalue = '', $classname = '') {
		$this->count++;
		$s = '';
		$m = ($this->count % 2 == 0) ? 2 : 1;
		$colspanstr = $this->disabledtips ? '' : ' colspan="2"';
		if ($type == 'submit') {
			$varname = $varname ? $varname : 'btnsubmit';
			$setname = $setname ? $setname : 'submit';
			echo '<tr>';
			if (!$this->tablesetmode) {
				echo '<td class="tablerow' . $m . '">&nbsp;</td>';
			}
			echo '<td' . $colspanstr . ' class="tablerow' . $m . '"><button class="btnsubmit" type="submit" name="' . $varname . '" value="yes">' . adminlang($setname) . '</button></td></tr>';
			echo "\n";
			return;
		}
		if (strpos($setname, '!') === 0) {
			$setname = substr($setname, 1);
			$settingcomments = '&nbsp;';
		} else {
			if (!$this->disabledtips) {
				$settingcomments = adminlang($setname . '_comments', $this->vars);
			}
		}
		if ($this->disabledtips) {
			$settingcomments = 'none';
		}
		$settingtitle = adminlang($setname, $this->vars);
		if ($this->tablesetmode && ($settingtitle && $settingtitle != 'none')) {
			$s = '<tr><th' . $colspanstr . ' class="th' . $m . '">';
			if ($checkboxvalue) {
				$s .= '<input class="checkbox" type="checkbox" name="checkboxgroup[]" value="' . $checkboxvalue . '" /><label>&nbsp;';
			}
			$s .= "$settingtitle:" . ($checkboxvalue ? '</label>' : '') . "</th></tr>";
		}

		$s .= '<tr>';
		if (!$this->tablesetmode) {
			if ($checkboxvalue) {
				$s .= '<td align="center" class="tablerow' . $m . '"><input class="checkbox" type="checkbox" name="checkboxgroup[]" value="' . $checkboxvalue . '" /></td>';
			}
			$s .= '<td align="right" width="15%" class="tablerow' . $m . ' nowrap"><strong>' . $settingtitle . ':</strong></td>';
		}
		if ($type == 'editor') {
			$s .= '<td class="tablerow' . $m . ' formrow"' . $colspanstr . '>';
		} else {
			$s .= '<td class="tablerow' . $m . ' formrow">';
		}
		if ($type == 'radio' || $type == 'radio2') {
			$classname = $classname ? $classname : 'radiostyle';
			$s .= '<ul onmouseover="alterStyle(this);" class="' . $classname . '"><li';
			$s .= ( intval($value) == 0) ? ' class="checked"' : '';
			$s .= '><input class="radio" type="radio" name="' . $varname . '" value="0"';
			$s .= ( intval($value) == 0) ? ' checked' : '';
			$s .= ' /><label>&nbsp;' . adminlang('no') . '</label></li><li';
			$s .= ( intval($value) == 1) ? ' class="checked"' : '';
			$s .= '><input class="radio" type="radio" name="' . $varname . '" value="1"';
			$s .= ( intval($value) == 1) ? ' checked' : '';
			$s .= ' /><label>&nbsp;' . adminlang('yes') . '</label></li>';
			if ($type == 'radio2') {
				$s .= '<li';
				$s .= ( intval($value) == 2) ? ' class="checked"' : '';
				$s .= '><input class="radio" type="radio" name="' . $varname . '" value="2"';
				$s .= ( intval($value) == 2) ? ' checked' : '';
				$s .= ' /><label>&nbsp;' . adminlang($setname . '_radio') . '</label></li>';
			}
			$s .= '</ul>';
		} elseif ($type == 'radios') {
			$radiolist = adminlang($setname . '_option');
			$onclicks = array();
			if ($onclick && is_array($onclick)) {
				$onclicks = $onclick;
			}
			$classname = $classname ? $classname : 'radiostyle';
			$s .= '<ul onmouseover="alterStyle(this);" class="' . $classname . '">';
			if (is_array($radiolist)) {
				foreach ($radiolist as $k => $v) {
					$s .= '<li';
					$s .= ( $value == $k) ? ' class="checked"' : '';
					$s .= '><input class="radio" type="radio" name="' . $varname . '" value="' . $k . '"';
					$s .= $onclicks ? ' onclick="' . $onclicks[$k] . '"' : '';
					$s .= ( $value == $k) ? ' checked' : '';
					$s .= ' /><label>&nbsp;' . $v . '</label></li>';
				}
			}
			$s .= '</ul>';
		} elseif ($type == 'text' || $type == 'password') {
			$classname = $classname ? $classname : 't60';
			$s .= '<input class="input ' . $classname . '" size="60" name="' . $varname . '" type="' . $type . '"';
			$s .= $id ? ' id="' . $id . '"' : '';
			$s .= $onclick ? ' onclick="' . $onclick . '"' : '';
			$s .= ' value="' . htmlcharsencode($value) . '" />';
		} elseif ($type == 'text2') {
			if (is_array($varname) && is_array($value)) {
				$extrastring1 = $extrastring2 = '';
				if(is_array($id)){
					$extrastring1 = ' id="'.$id[0].'"';
					$extrastring2 = ' id="'.$id[1].'"';
				}
				if(is_array($onclick)){
					$extrastring1 .= ' onclick="'.$onclick[0].'"';
					$extrastring2 .= ' onclick="'.$onclick[1].'"';
				}
				$s .= '<input class="input" size="20" name="' . $varname[0] . '" type="text" value="' . htmlcharsencode($value[0]) . '"'.$extrastring1.' />';
				$s .= ' x <input class="input" size="20" name="' . $varname[1] . '" type="text" value="' . htmlcharsencode($value[1]) . '"'.$extrastring2.' />';
			}
		} elseif ($type == 'textcolor' || $type == 'text_color') {
			if (is_array($varname) && is_array($value)) {
				$color_name = $varname[1];
				$color_value = $value[1];
			} else {
				$color_name = $varname;
				$color_value = $value;
			}
			$cid = 'color' . $this->count;
			$colorinput = '<input id="' . $cid . '_v" class="input t35" name="' . $color_name . '" type="text" value="' . htmlcharsencode($color_value) . '" onchange="previewColorValue(\'' . $cid . '\')" />';
			$colorinput .= '<button type="button" id="' . $cid . '" onclick="' . $cid . '_frame.location=\'misc/admin/getcolor.htm?' . $cid . '|' . $cid . '_v\';menu.show({\'ctrlid\':\'' . $cid . '\'})" class="btncolor" style="background-color: ' . ($color_value ? htmlcharsencode($color_value) : '') . '">&nbsp;</button>';
			$colorinput .= '<span id="' . $cid . '_menu" style="display: none"><iframe name="' . $cid . '_frame" src="about:blank" frameborder="0" width="210" height="148" scrolling="no"></iframe></span>';
			if (is_array($varname) && is_array($value)) {
				$s .= '<input class="input t50" size="50" name="' . $varname[0] . '" type="text" value="' . htmlcharsencode($value[0]) . '" />';
				$settingcomments = $colorinput;
			} else {
				$s .= $colorinput;
			}
		} elseif ($type == 'textarea' || $type == 'textwrap') {
			$classname = $classname ? $classname : 'textarea';
			$wrapoff = $type == 'textwrap' ? ' wrap="OFF"' : '';
			$s .= '<textarea'.$wrapoff.' rows="5" ondblclick="textareaResize(this, 1)" onkeyup="textareaResize(this, 0)" name="' . $varname . '" id="' . $varname . '" cols="60" class="' . $classname . '">' . htmlcharsencode($value) . '</textarea>';
		} elseif ($type == 'select') {
			if (is_array($id)) {
				$selectoption = $id;
			} else {
				$selectoption = adminlang($setname . '_option');
			}
			$value = htmlcharsencode($value);
			$classname = $classname ? $classname : 'select t60';
			$onclick = $onclick ? ' onChange="' . $onclick . '"' : '';
			$s .= '<select name="' . $varname . '" class="' . $classname . '"' . $onclick . '>';
			if (is_array($selectoption)) {
				foreach ($selectoption as $k => $v) {
					$s .= strpos($k, 'optgroup') === 0 ? "<optgroup label=\"$v\">" : '';
					if (strpos($k, 'optgroup') !== 0 && strpos($k, 'optgroup') !== 1) {
						$s .= '<option value="' . $k . '"';
						$s .= ( $k == $value) ? ' SELECTED' : '';
						$s .= '>' . $v . '</option>';
					}
					$s .= strpos($k, 'optgroup') === 1 ? '</optgroup>' : '';
				}
			}
			$s .= '</select>';
		} elseif ($type == 'checkbox') {
			$checkboxlist = adminlang($setname . '_option');
			$classname = $classname ? $classname : 'checkboxstyle';
			$s .= '<ul onmouseover="alterStyle(this);" class="' . $classname . '">';
			if (is_array($checkboxlist)) {
				$i = 0;
				foreach ($checkboxlist as $k => $v) {
					$s .= '<li';
					$s .= in_array($k, $value) ? ' class="checked"' : '';
					$s .= '><input class="checkbox" type="checkbox" name="' . $varname . '[]" value="' . $k . '"';
					$s .= in_array($k, $value) ? ' checked' : '';
					$s .= ' /><label>&nbsp;' . $v . '</label></li>';
					$i++;
				}
			}
			$s .= '</ul>';
		} elseif ($type == 'checkboxs') {
			$checkboxlist = adminlang($setname . '_option');
			$classname = $classname ? $classname : 'checkboxstyle';
			$s .= '<ul onmouseover="alterStyle(this);" class="' . $classname . '">';
			if (is_array($checkboxlist)) {
				$i = 0;
				foreach ($checkboxlist as $k => $v) {
					$s .= '<li';
					$s .= (isset($value[$k]) && $value[$k]) ? ' class="checked"' : '';
					$s .= '><input class="checkbox" type="checkbox" name="' . $varname . '[' . $k . ']" value="1"';
					$s .= (isset($value[$k]) && $value[$k]) ? ' checked="checked"' : '';
					$s .= ' /><label>&nbsp;' . $v . '</label></li>';
					$i++;
				}
			}
			$s .= '</ul>';
		} elseif ($type == 'highlight'){
			$s .= $this->highlight_select($value);
		} elseif ($type == 'editor') {
			$id = $id ? $id : 'editor_content';
			$s .= '<textarea id="' . $id . '" name="' . $varname . '" style="width:468px;height:200px;visibility:hidden">' . htmlcharsencode($value, ENT_QUOTES) . '</textarea>';
			$settingcomments = 'none';
		} elseif ($type == 'value' && !is_array($value)) {
			$s .= $value;
		} else {
			$s .= adminlang($varname, $value);
		}
		$s .= '</td>';
		if ($settingcomments != 'none') {
			$s .= '<td class="tablerow' . $m . ' tips">' . $settingcomments . '</td>';
		}
		echo "$s</tr>\n";
	}

	function editor_content($key, $value = '', $name = 'content', $id = 'editor_content', $colspan = 0, $width = '99%', $height = '300px') {
		$this->count++;
		$m = ($this->count % 2 == 0) ? 2 : 1;
		$id = $id ? $id : 'editor_content';
		$colspan = $colspan ? ' colspan="' . $colspan . '"' : '';
		$style = ($width ? "width:$width;" : 'width:99%;') . ($height ? "height:$height;" : 'height:300px;');
		$s = '<tr id="contentEditorBox">';
		$s .= '<td class="tablerow' . $m . ' td-h">' . adminlang($key, $this->vars) . '</td>';
		$s .= '<td' . $colspan . ' class="tablerow' . $m . '"><textarea id="' . $id . '" name="' . $name . '" style="'.$style.'visibility:hidden;">' . htmlcharsencode($value, ENT_QUOTES) . '</textarea>';
		$s .= "</td></tr>\n";
		echo $s;
	}

	function textarea($key, $value = '', $name = 'content', $id = '', $colspan = 0, $varname = '', $string = '', $style = '') {
		$this->count++;
		$m = ($this->count % 2 == 0) ? 2 : 1;
		$colspan = $colspan ? ' colspan="' . $colspan . '"' : '';
		$id = $id ? $id : $name;
		$style = empty($style) ? ' ondblclick="textareaResize(this, 1)" onkeyup="textareaResize(this, 0)" style="width:374px"' : " style=\"$style\"";
		$s = '<tr class="tr-' . $m . '">';
		$s .= '<td class="tablerow' . $m . ' td-h">' . adminlang($key, $this->vars) . '</td>';
		$s .= '<td' . $colspan . ' class="tablerow' . $m . ' formrow"><textarea id="' . $id . '" name="' . $name . '" rows="6" cols="60" class="textarea"'.$style.'>' . htmlcharsencode($value, ENT_QUOTES) . '</textarea>';
		if ($id == 'summary_content') {
			$s .= '<p><input class="button" type="button" name="btndescrlen" value="' . adminlang('summary_strlen') . '" onclick="showDescrLength(\'summary_content\')"/> ';
			$s .= '<input class="button" type="button" name="btnhidedescr" value="' . adminlang('hidesummary') . '" onclick="toggleDisplay(\'summarybody\',\'hide\')"/></p>';
		}
		if ($varname && !$colspan) {
			$s .= '<td class="tablerow' . $m . ' td-h">' . adminlang($varname, $this->vars) . '</td>';
			$s .= '<td class="tablerow' . $m . '">' . $string . '</td>';
		}
		$s .= "</td></tr>\n";
		echo $s;
	}

	function textinput($name, $value, $size = 35, $id = '', $onclick = '', $title = '', $type = 'text', $class = '') {
		$id = $id ? " id=\"$id\"" : '';
		//$onclick = $onclick ? " onclick=\"$onclick\"" : '';
		if(!empty($onclick)){
			$onclick = $onclick{0} == ':' ? " " . substr($onclick, 1) : " onclick=\"$onclick\"";
		}
		$title = $title ? ' title="' . adminlang($title) . '"' : '';
		$type = $type ? $type : 'text';
		$cname = $size ? " $size" : '';
		if(is_numeric($size)){
			$cname = $size ? " t$size" : '';
		}
		$class = $class ? " $class" : '';
		$comment = '';
		if(strpos($name, '|')){
			list($name, $comment) = explode('|', $name);
		}
		$comments = $comment ? ' title="'.adminlang($comment).'"' : '';
		$s = '<input class="input' . $cname . $class . '"' . $title . ' type="' . $type . '" size="' . $size . '" name="' . $name . '"' . $id . $onclick . ' value="' . htmlcharsencode($value) . '"'.$comments.' />';
		return $s;
	}

	function inputedit($name, $value, $size = 35, $textalign = 'left') {
		$textalign = $textalign == 'left' ? " style=\"text-align:$textalign\"" : '';
		$title = ' title="' . adminlang('input_edit_tips') . '"';
		$s = '<input class="noput t' . $size . '"' . $title . $textalign . ' type="text" size="' . $size . '" name="' . $name . '" value="' . htmlcharsencode($value) . "\" onblur=\"this.className='noput t$size'\" onfocus=\"this.className='input t$size'\"/>";
		return $s;
	}

	function textcolor($name, $value = '', $size = '20', $id = 'color') {
		$cid = $id . $this->count;
		$cname = $size ? " t$size" : '';
		$s = '<input id="' . $cid . '_v" class="input' . $cname . '" size="' . $size . '" name="' . $name . '" type="text" value="' . htmlcharsencode($value) . '" onchange="previewColorValue(\'' . $cid . '\')" />';
		$s .= '<button type="button" id="' . $cid . '" onclick="' . $cid . '_frame.location=\'misc/admin/getcolor.htm?' . $cid . '|' . $cid . '_v\';menu.show({\'ctrlid\':\'' . $cid . '\'})" class="btncolor" style="background-color: ' . ($value ? htmlcharsencode($value) : '') . '">&nbsp;</button>';
		$s .= '<span id="' . $cid . '_menu" style="display: none"><iframe name="' . $cid . '_frame" src="about:blank" frameborder="0" width="210" height="148" scrolling="no"></iframe></span>';
		return $s;
	}

	function radio($params, $name, $checked = 0, $li = TRUE) {
		$s = $li ? '<ul onmouseover="alterStyle(this);" class="radiostyle">' : '<span onmouseover="alterStyle(this, \'em\');" class="spanradio">';
		$listart = $li ? '<li%s>' : '<em%s>';
		$liend = $li ? '</li>' : '</em>';
		if(is_string($params)) $params = adminlang($params);
		foreach ($params as $key => $value) {
			$s .= sprintf($listart, $checked == $key ? ' class="checked"' : '');
			$s .= '<input class="radio" type="radio" name="' . $name . '" value="' . $key . '"';
			$s .= $checked == $key ? ' checked' : '';
			$s .= ' /><label>&nbsp;' . adminlang($value) . '&nbsp;</label>' . $liend;
		}
		$s .= $li ? '</ul>' : '</span>';
		return $s;
	}

	function checkbox($varname, $name, $value, $defval = 1) {
		$s = '<ul onmouseover="alterStyle(this);" class="checkboxstyle">';
		if (is_array($varname)) {
			foreach ($varname as $key => $val) {
				$s .= '<li';
				$s .= $value[$key] == $defval ? ' class="checked"' : '';
				$s .= '><input class="checkbox" type="checkbox" name="' . $name[$key] . '" value="' . $defval . '"';
				$s .= $value[$key] == $defval ? ' checked' : '';
				$s .= ' /><label>' . adminlang($val) . '</label></li>';
			}
		} else {
			$s .= '<li';
			$s .= $value == $defval ? ' class="checked"' : '';
			$s .= '><input class="checkbox" type="checkbox" name="' . $name . '" value="' . $defval . '"';
			$s .= ( $value == $defval) ? ' checked' : '';
			$s .= ' /><label>&nbsp;' . adminlang($varname) . '</label></li>';
		}
		$s .= '</ul>';
		return $s;
	}

	function select($varname, $name, $value = 0, $extstr = '') {
		$class = stripos($extstr, 'class=') !== FALSE ? '' : 'class="select" ';
		$s = '<select name="' . $name . '" ' . $class . $extstr . '>';
		if (!is_array($varname)) {
			$varname = adminlang($varname);
		}
		foreach ($varname as $key => $val) {
			$s .= strpos($key, 'optgroup') === 0 ? "<optgroup label=\"$val\">" : '';
			if (strpos($key, 'optgroup') !== 0 && strpos($key, 'optgroup') !== 1) {
				$s .= '<option value="' . $key . '"';
				$s .= ($value == $key) ? ' SELECTED' : '';
				$s .= ">$val</option>";
			}
			$s .= strpos($key, 'optgroup') === 1 ? '</optgroup>' : '';
		}
		$s .= '</select>';
		return $s;
	}

	function editor_scritp($name) {
		echo '<script src="misc/tiny_mce/tiny_mce.js" type="text/javascript"></script>' . "\n";
		if ($name && $name != 'small') {
			echo '<script src="misc/admin/js/' . $name . '.js" type="text/javascript" charset="' . CHARSET . '"></script>' . "\n";
		}
		echo '<script src="misc/js/post_thread.js" type="text/javascript" charset="' . CHARSET . '"></script>' . "\n";
	}

	function showpagescript($showid = 'showpage', $codeid = 'pagecode') {
		echo '<script type="text/javascript">';
		echo 'document.getElementById("' . $showid . '").innerHTML = document.getElementById("' . $codeid . '").innerHTML;';
		echo "</script>\n";
	}

	function script($src) {
		if (is_array($src)) {
			foreach ($src as $value) {
				$value = substr($value, 0, 5) == 'http:' ? $value : 'misc/' . $value;
				echo '<script src="' . $value . '" type="text/javascript" charset="' . CHARSET . '"></script>' . "\n";
			}
		} else {
			$src = substr($src, 0, 5) == 'http:' ? $src : 'misc/' . $src;
			echo '<script src="' . $src . '" type="text/javascript" charset="' . CHARSET . '"></script>' . "\n";
		}
	}

	function del_word($name, $url, $separator = '', $confirm = 1) {
		$name = $name ? $name : 'delete';
		$url = ADMIN_SCRIPT . '?' . $url;
		$strconfirm = $confirm ? ' onclick="return confirm(\'' . adminlang($name . '_tips') . '\')"' : '';
		$s = '<a href="' . $url . '"'.$strconfirm.'><strong>' . adminlang($name) . '</strong></a>' . $separator;
		return $s;
	}

	function edit_word($name, $url, $separator = '', $target = null) {
		$name = $name ? $name : 'edit';
		$url = ADMIN_SCRIPT . '?' . $url;
		$target = $target ? ' target="_blank"' : '';
		$s = '<a href="' . $url . '"'.$target.'><strong>' . adminlang($name) . '</strong></a>' . $separator;
		return $s;
	}

	function submit_button($key = 'submit', $name = 'btnsubmit', $class = 'btnsubmit', $onclick = '') {
		$key = $key ? $key : 'submit';
		$name = $name ? $name : 'btnsubmit';
		$class = $class ? $class : 'btnsubmit';
		$onclick = $key == 'delete' && !$onclick ? "{if(confirm('" . adminlang('del_tips') . "')){return true;}return false;}" : $onclick;
		$onclick = $onclick ? " onclick=\"$onclick\"" : '';
		$s = "<button class=\"$class\" type=\"submit\" name=\"$name\"$onclick value=\"yes\">" . adminlang($key) . '</button>';
		return $s;
	}

	function button($key = 'button', $name = 'B1', $onclick = '', $class = 'button') {
		$key = $key ? $key : 'button';
		$name = $name ? $name : 'B1';
		$class = $class ? $class : 'button';
		$onclick = $onclick ? " onclick=\"$onclick\"" : '';
		$s = "<button class=\"$class\" type=\"button\" name=\"$name\"$onclick value=\"yes\">" . adminlang($key) . '</button>';
		return $s;
	}

	function del_submit($name = 'del_tips') {
		$name = $name ? $name : 'deltips';
		$s = '<button class="button" type="submit" name="btnsubmit" value="yes" onclick="{if(confirm(\'' . adminlang($name) . '\')){return true;}return false;}">' . adminlang('delete') . '</button>';
		return $s;
	}

	function checkall($key = 'checkall', $name = 'chkall', $prefix = '', $checked = FALSE) {
		$key = $key ? $key : 'checkall';
		$name = $name ? $name : 'chkall';
		$checked = $checked ? ' checked="checked"' : '';
		$prefix = $prefix ? ",'$prefix'" : '';
		$s = '<label><input type="checkbox" name="' . $name . '" class="checkbox" value="1" onclick="checkall(this.form,this.name' . $prefix . ')"' . $checked . ' /> ' . adminlang($key) . '</label>';
		return $s;
	}

	function activetabs($id = 'main') {
		echo "<script type=\"text/javascript\">activeTabs('$id');</script>";
	}

	function highlight_select($value, $name = 'highlights', $id = 0) {
		$colorvalue = phpcom::$setting['colorvalue'];
		$value = intval($value);
		$string = sprintf('%02d', $value);
		$color = $colorvalue[$string[1]];
		$selectstyle = $color ? " style=\"background-color:$color;color:$color;border:;\"" : '';
		$s = '<span class="fontselect"><select title="' . adminlang('highlight_comments') . '" name="'.$name.'[color]"' . $selectstyle . ' onchange="changeSelectColor(this)">';
		$colorvalue[0] = adminlang('select_color');
		foreach ($colorvalue as $key => $value) {
			$s .= '<option value="' . $key . '"';
			$s .= $key ? " style=\"background-color:$value;color:$value;\"" : ' style="background-color:#ffffff;color:#000000;"';
			$s .= ( $key == $string[1]) ? ' SELECTED' : '';
			$value = strtoupper($value);
			$s .= ">$value</option>\n";
		}
		$s .= "</select></span>\r\n";
		$string[0] = strval(intval($string[0]));
		$styles = array();
		$styles[0] = in_array($string[0], array(1, 4, 5, 7));
		$styles[1] = in_array($string[0], array(2, 4, 6, 7));
		$styles[2] = in_array($string[0], array(3, 5, 6, 7));
		$btncnt_b = $styles[0] ? ' btncnt' : '';
		$btncnt_i = $styles[1] ? ' btncnt' : '';
		$btncnt_u = $styles[2] ? ' btncnt' : '';
		$styleid = $id ? "highlight_style_$id" : 'highlight_style';
		$s .= '<input type="hidden" id="'.$styleid.'" name="'.$name.'[font]" value="' . $string[0] . '" />';
		$s .= '<a class="fontbtn_b' . $btncnt_b . '" title="' . adminlang('bold') . '" onclick="toggle_highlightstyle(this, 1, \''.$styleid.'\')" href="javascript:void(0);">B</a>';
		$s .= '<a class="fontbtn_i' . $btncnt_i . '" title="' . adminlang('italic') . '" onclick="toggle_highlightstyle(this, 2, \''.$styleid.'\')" href="javascript:void(0);">I</a>';
		$s .= '<a class="fontbtn_u' . $btncnt_u . '" title="' . adminlang('underline') . '" onclick="toggle_highlightstyle(this, 3, \''.$styleid.'\')" href="javascript:void(0);">U</a>';
		//$s .= $stylestr;
		return $s;
	}

}

?>
