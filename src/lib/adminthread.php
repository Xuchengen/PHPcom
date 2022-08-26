<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : adminthread.php    2011-7-10 21:54:59
 */
!defined('IN_PHPCOM') && exit('Access denied');

function selectChildCategory(&$array, $catid, $categorys = array(), $based = 0) {
	$option = '';
	foreach($categorys as $cid => $category){
		$option .= '<option value="' . $category['catid'] . '"';
		$option .= ( $category['catid'] == $catid) ? ' SELECTED' : '';
		$option .= ">";
		if($category['depth'] > 0){
			$option .= str_pad('', 14 * $category['depth'], " &nbsp; &nbsp;", STR_PAD_LEFT). '|- ';
		}
		$option .= "{$category['catname']}</option>";
		if(isset($array[$cid]) && empty($based)) {
			$option .= selectChildCategory($array, $catid, $array[$cid]);
		}
	}
	return $option;
}

function select_category($channelid = 0, $catid = 0, $based = 0) {
    phpcom_cache::load('category_' . $channelid);
    if(!isset(phpcom::$G['cache']['category_' . $channelid][0])) {
    	return '';
    }
    $catArray = phpcom::$G['cache']['category_' . $channelid];
    return selectChildCategory($catArray, $catid, $catArray[0], $based);
}

function get_threadclassids($tid = 0){
	if($tid = intval($tid)){
		$classids = array();
		$query = DB::query("SELECT classid FROM " . DB::table('thread_class_data') . " WHERE tid='$tid'");
		while ($row = DB::fetch_array($query)) {
			$classids[] = $row['classid'];
		}
		return implode(',', $classids);
	}
	return '';
}

function get_tagstr($tags) {
    $tagstr = '';
    if ($tags) {
        $tagarray = array_unique(explode("\t", $tags));
        $tagsnew = array();
        if (strexists($tags, ',')) {
            foreach ($tagarray as $key => $value) {
                if ($value) $tagsnew[] = substr($value, strpos($value, ',') + 1);
            }
            $tagstr = implode(',', $tagsnew);
        } else {
            foreach ($tagarray as $key => $value) {
                if ($value) $tagsnew[] = substr($value, strpos($value, ' ') + 1);
            }
            $tagstr = implode(' ', $tagsnew);
        }
    }
    return $tagstr;
}

function show_polloption($pollid) {
    $optionstr = '';
    $deleteword = adminlang('delete');
    $i = 0;
    if ($pollid) {
        $sql = DB::buildlimit("SELECT * FROM " . DB::table('polloption') . " WHERE pollid='$pollid' ORDER BY voteid ASC", 300);
        $query = DB::query($sql);
        while ($row = DB::fetch_array($query)) {
            if ($i) {
                $optionstr .= '<span><input class="input" type="text" style="margin-bottom:5px;" maxlength="60" name="voteoption[]" size="60" value="' . htmlcharsencode($row['voteoption']) . '"/>
					<input class="input" type="text" style="margin-bottom:5px;" maxlength="10" name="votes[]" size="5" value="' . intval($row['votes']) . '"/>
					<input type="hidden" name="voteids[]" value="' . intval($row['voteid']) . '"/><a href="javascript:;" style="display:none;" onclick="delvoteOption(this);return false;"> ' . $deleteword . ' </a><br/></span>';
            } else {
                $optionstr = '<span id="basevoteoption"><input class="input" type="text" style="margin-bottom:5px;" maxlength="60" name="voteoption[]" size="60" value="' . htmlcharsencode($row['voteoption']) . '"/>
					<input class="input" type="text" style="margin-bottom:5px;" maxlength="10" name="votes[]" size="5" value="' . intval($row['votes']) . '"/>
					<input type="hidden" name="voteids[]" value="' . intval($row['voteid']) . '"/><a href="javascript:;" style="display:none;" onclick="delvoteOption(this);return false;"> ' . $deleteword . ' </a><br/></span>';
            }
            $i++;
        }
        if ($i === 0) {
            $optionstr = '<span id="basevoteoption"><input class="input" type="text" style="margin-bottom:5px;" maxlength="60" name="voteoption[]" size="60"/>
				<input class="input" type="text" style="margin-bottom:5px;" maxlength="10" name="votes[]" size="5" value="0"/>
				<input type="hidden" name="voteids[]" value="0"/>
				<a href="javascript:;" style="display:none;" onclick="delvoteOption(this);return false;"> ' . $deleteword . ' </a><br/></span>';
        }
    } else {
        $optionstr = '<span id="basevoteoption"><input class="input" type="text" style="margin-bottom:5px;" maxlength="60" name="voteoption[]" size="60"/>
			<input class="input" type="text" style="margin-bottom:5px;" maxlength="10" name="votes[]" size="5" value="0"/>
			<input type="hidden" name="voteids[]" value="0"/>
			<a href="javascript:;" style="display:none;" onclick="delvoteOption(this);return false;"> ' . $deleteword . ' </a><br/></span>
			<span><input class="input" type="text" style="margin-bottom:5px;" maxlength="60" name="voteoption[]" size="60"/>
			<input class="input" type="text" style="margin-bottom:5px;" maxlength="10" name="votes[]" size="5" value="0"/>
			<input type="hidden" name="voteids[]" value="0"/>
			<a href="javascript:;" onclick="delvoteOption(this);return false;"> ' . $deleteword . ' </a><br/></span>
			<span><input class="input" type="text" style="margin-bottom:5px;" maxlength="60" name="voteoption[]" size="60"/>
			<input class="input" type="text" style="margin-bottom:5px;" maxlength="10" name="votes[]" size="5" value="0"/>
			<input type="hidden" name="voteids[]" value="0"/>
			<a href="javascript:;" onclick="delvoteOption(this);return false;"> ' . $deleteword . ' </a><br/></span>';
    }
    $optionstr .='<p id="addvote_button"><input class="button" type="button" name="btnaddvote" value="' . adminlang('addvote') . '" onclick="addvoteOption()"/></p>';
    return $optionstr;
}

?>
