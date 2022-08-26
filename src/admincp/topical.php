<?php
/**
 * Copyright (c) 2010-2013 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : topical.php  2013-10-28
 */
! defined ( 'IN_PHPCOM' ) && exit ( 'Access denied' );
phpcom::$G ['lang'] ['admin'] = 'special';

if ($action == '') {
	$action = 'show';
}

$su = new TopicalClass ();
$su->run ( $action );

class TopicalClass {
	public function run($action) {
		$this->__init();

		$action = ucwords ( trim ( $action, "\r\n\0\t\x0B _" ) );

		$method = '';
		if (method_exists ( $this, $method = 'action' . $action )) {
			self::$method ();
		} else if (method_exists ( $this, $method = 'ajax' . $action )) {
			self::$method ();
		} else {
			// echo "action:$action, method:$method";exit;
			header ( "status: 404 Not Found" );
			header ( 'HTTP/1.1 404 Not Found' );
			exit ();
		}
	}

	private function __init() {
		$this->specid = intval(phpcom::$G['gp_specid']);
		if(!$this->_specialIsExisted($this->specid)) {
			 exit ('Access denied');
		}

		$this->classid = isset(phpcom::$G['gp_classid']) ? intval(phpcom::$G['gp_classid']) : 0;

		$this->types = array ();
		if (isset ( phpcom::$G ['gp_type'] )) {
			$types = is_array(phpcom::$G['gp_type']) ? phpcom::$G['gp_type'] : explode(',', phpcom::$G['gp_type']);
			$typeList = array('article', 'photo', 'soft', 'video');
			foreach ($types as $type) {
				$type = strtolower(trim($type));
				in_array($type, $typeList) && $this->types[] = $type;
			}
		}


		$this->tids = array();
		$this->threads = array();
		if(isset(phpcom::$G['gp_tids'])) {
			$this->tids = phpcom::$G['gp_tids'];
			$this->threads = array();
			foreach($this->tids as $tid) {
				isset(phpcom::$G['gp_dateline'][$tid]) && $this->threads[intval($tid)] = intval(phpcom::$G['gp_dateline'][$tid]);
			}
		}

		$this->keyword	= isset(phpcom::$G['gp_keyword']) ? trim(phpcom::$G ['gp_keyword']) : '';
		$this->page		= isset(phpcom::$G['gp_page']) ? intval(phpcom::$G ['gp_page']) : 1;
	}

	// ------------------------------ Contorller ------------------------------
	public function actionShow() {
		$this->_show();
	}
	public function actionList() {
		$this->_showList ();
	}
	public function actionData() {
		$this->_showData ();
	}
	public function actionAdd() {
		if ($this->threads) {
			$this->_addThreads($this->specid, $this->classid, $this->threads);
		}
		$this->_showList(true);
	}

	public function actionUpdate() {
		$btn = isset ( phpcom::$G ['gp_btn'] ) ? strtolower ( trim ( phpcom::$G ['gp_btn'] ) ) : null;
		$error = false;

		if ($this->tids) {
			switch ($btn) {
				case 'delete':
					if (!$this->_deleteThreads($this->specid, $this->classid, $this->tids)) {
						$error = adminlang('error_update_database');
					}
					break;
				case 'top':
				case 'bottom':
				default:
					$error = adminlang ('error_action');
					break;
			}
		}

		if(!$error){
			$this->_showData();
		} else {
			echo $error;
		}
	}

	// ------------------------------ View ------------------------------
	private $_pageSize = 9;

	private function _show() {
		echo '<html>';
		echo '<head>';
		echo '<style>';
		echo '* {margin: 0px; padding: 0px;}';
		echo '</style>';
		echo '</head>';
		echo '<body>';
		echo '<table width="100%" height="100%">';
		echo '    <tr>';
		echo '        <td width="50%"><iframe src="?m=topical&action=data&specid=' . $this->specid . '&classid=' . $this->classid . '" frameborder="0" width="100%" height="100%" scorlling="auto" name="class"></iframe></td>';
		echo '        <td width="50%"><iframe src="?m=topical&action=list&specid=' . $this->specid . '&classid=' . $this->classid . '" frameborder="0" width="100%" height="100%" scorlling="auto" name="list"></iframe></td>';
		echo '    </tr>';
		echo '</table>';
		echo '<body>';
		echo '</html>';
	}

	private function _showData() {
		$keyword 	= $this->keyword;
		$types 		= $this->types;
		$specid 	= $this->specid;
		$classid 	= $this->classid;
		$page 		= $this->page;

		$showPage = '';
		$counter = 0;
		$dataList = array ();

		$pageSize = 10;
		$counter = $this->_getDataCount ( $specid, $classid );
		$pageCount = ceil ( $counter / $pageSize );
		$page = $page > $pageCount ? $pageCount : $page;
		$page = $page <= 0 ? 1 : $page;

		if ($pageCount > 1) {
			$showPage = '<var class="morePage">' . showPage ( $page, $pageCount, $pageSize, $counter, '?m=topical&action=data&specid=' . $specid . '&classid=' . $classid , 4, false) . '</var>';
		}

		$dataList = $this->_getDataList ( $specid, $classid, $page, $pageSize );
		$title = $this->_getTitle ( $specid, $classid );

		$tbody = '';
		$odd = 1;
		foreach ( $dataList as $k => $v ) {
			$tbody .= '<tr>';
			$tbody .= '    <td class="tablerow' . $odd . '" width="5%" align="center"><input type="checkbox" name="tids[]" value="' . $v ['tid'] . '" /></td>';
			$tbody .= '    <td class="tablerow' . $odd . '" width="95%" colspan="2"><a target="_blank" class="fb" href="?m=threadlog&action=link&tid=' . $v ['tid'] . '&chanid=' . $v ['chanid'] . '" title="' . $v ['title'] . '">' . $v ['shortTitle'] . '</a></td>';
			$tbody .= '</tr>';
			$odd = $odd == 1 ? 2 : 1;
		}
		if ($tbody != '') {
			$tbody .= '<tr>';
			$tbody .= '    <td class="tablerow' . $odd . '" width="5%" align="center"><input type="checkbox" name="selectAllTids" id="selectAllTids" /></td>';
			$tbody .= '	   <td class="tablerow' . $odd . '" align="center">';
			$tbody .= '        <button class="btnsubmit" type="submit" name="btn" value="delete" style="margin:0px 10px;">' . adminlang ( 'btn_delete' ) . '</button>';
			$tbody .= '    </td>';
			$tbody .= '    <td class="tablerow' . $odd . '">&nbsp;</td>';
			$tbody .= '</tr>';
		} else {
			$tbody = '<tr><td class="taberow1" align="center">' . adminlang ( 'has_no_data' ) . '</td></tr>';
		}

		$adminhtml = phpcom_adminhtml::instance ();
		admin_header ( 'menu_special' );
		echo '<form method="post" action="?m=topical&action=update">';
		echo '<input type="hidden" name="specid" value="' . $specid . '" />';
		echo '<input type="hidden" name="classid" value="' . $classid . '" />';
		echo '<table width="100%" class="dataList">';
		echo '    <caption>' . $title . ' ' . adminlang ('article' ) . '</caption>';
		echo '    <tbody>';
		echo $tbody;
		echo '    </tbody>';
		echo '</table>';
		echo '</form>';
		echo $showPage;
		$this->_showEnd ();
	}

	private function _showList($reloadData = false) {
		$keyword 	= $this->keyword;
		$types 		= $this->types;
		$specid 	= $this->specid;
		$classid 	= $this->classid;
		$page 		= $this->page;

		$showPage 	= '';
		$counter 	= 0;
		$dataList 	= array();

		if ($keyword != '') {
			$counter = $this->_getCount($keyword, $types, $specid, $classid);
			$pageCount = ceil ( $counter / $this->_pageSize );
			$page = $page > $pageCount ? $pageCount : $page;
			$page = $page <= 0 ? 1 : $page;

			if ($counter > $this->_pageSize) {
				$url = '?m=topical';
				$url .= "&action=list";
				$url .= "&specid=$specid";
				$url .= "&classid=$classid";
				$url .= $keyword != '' ? '&keyword=' . stripcslashes ( $keyword ) : '';
				$url .= count ( $types ) ? '&type=' . implode ( ',', $types ) : '';
				$showPage = '<var class="morePage">' . showPage ( $page, $pageCount, $this->_pageSize, $counter, $url , 4, false) . '</var>';
			}

			$dataList = $this->_getList ( $keyword, $types, $specid, $classid, $page );
		}

		$tbody = '';
		$odd = 1;
		foreach ( $dataList as $k => $v ) {
			$tbody .= '<tr>';
			$tbody .= '    <td class="tablerow' . $odd . '" width="5%" align="center"><input type="checkbox" name="tids[]" value="' . $v['tid'] . '" checked="checked" /><input type="hidden" name="dateline[' . $v['tid'] . ']" value="' . $v['dateline'] . '" /></td>';
			$tbody .= '    <td class="tablerow' . $odd . '" width="95%" colspan="2"><a target="_blank" class="fb" href="?m=threadlog&action=link&tid=' . $v ['tid'] . '&chanid=' . $v ['chanid'] . '" title="' . $v ['title'] . '">' . $v ['shortTitle'] . '</a></td>';
			$tbody .= '</tr>';
			$odd = $odd == 1 ? 2 : 1;
		}
		if ($tbody != '') {
			$tbody .= '<tr>';
			$tbody .= '    <td class="tablerow' . $odd . '" width="5%" align="center"><input type="checkbox" name="selectAllTids" id="selectAllTids" checked="checked" /></td>';
			$tbody .= '    <td class="tablerow' . $odd . '" align="center"><button class="btnsubmit" type="submit" name="btnsubmit" value="yes">' . adminlang ( 'btn_add' ) . '</button></td>';
			$tbody .= '    <td class="tablerow' . $odd . '">&nbsp;</td>';
			$tbody .= '</tr>';
		} else {
			$tbody = '<tr><td class="taberow1" align="center">' . adminlang ( 'has_no_data' ) . '</td></tr>';
		}

		$adminhtml = phpcom_adminhtml::instance ();

		$classid = isset ( phpcom::$G ['gp_classid'] ) ? intval ( phpcom::$G ['gp_classid'] ) : 0;
		admin_header ( 'menu_special' );

		echo '<form method="post" action="?m=topical&action=list&specid=' . $specid . '&classid=' . $classid . '">';
		echo '<table width="100%">';
		echo '    <tbody>';
		echo '        <tr><th colspan="8">' . adminlang ('input_condition' ) . '</th></tr>';
		echo '        <tr>';
		echo '            <td class="tablerow2" width="16%" align="center"><input type="radio" checked="checked" name="type[]" id="type_article" value="article"' . (in_array ( 'article', $types ) ? ' checked="checked"' : '') . ' /> <label for="type_article">' . adminlang ( 'article' ) . '</label></td>';
		echo '            <td class="tablerow2" width="16%" align="center"><input type="radio" name="type[]" id="type_soft" value="soft"' . (in_array ( 'soft', $types ) ? ' checked="checked"' : '') . ' /> <label for="type_soft">' . adminlang ( 'soft' ) . '</label></td>';
		echo '            <td class="tablerow2" width="16%" align="center"><input type="radio" name="type[]" id="type_photo" value="photo"' . (in_array ( 'photo', $types ) ? ' checked="checked"' : '') . ' /> <label for="type_photo">' . adminlang ( 'photo' ) . '</label></td>';
		echo '            <td class="tablerow2" width="16%" align="center"><input type="radio" name="type[]" id="type_video" value="video"' . (in_array ( 'video', $types ) ? ' checked="checked"' : '') . ' /> <label for="type_video">' . adminlang ( 'video' ) . '</label></td>';
		echo '            <td class="tablerow2" width="20%" align="left"><input type="text" class="input sh t10" name="keyword" value="' . $keyword . '"></td>';
		echo '            <td class="tablerow2" width="16%" align="center"><button class="button" type="submit" name="btnsearch" value="yes" style="margin:0px;">' . adminlang ( 'search' ) . '</button></td>';
		echo '        </tr>';
		echo '        ';
		echo '    </tbody>';
		echo '</table>';
		echo '</form>';
		echo '<form method="post" action="?m=topical&action=add">';
		echo '<input type="hidden" name="specid" value="' . $specid . '" />';
		echo '<input type="hidden" name="classid" value="' . $classid . '" />';
		echo '<input type="hidden" name="keyword" value="' . stripcslashes ( $keyword ) . '" />';
		echo '<input type="hidden" name="type" value="' . implode ( ',', $types ) . '" />';
		echo '<input type="hidden" name="page" value="' . $page . '" />';
		echo '<table width="100%" class="dataList">';
		echo '    <tbody>';
		echo $tbody;
		echo '    </tbody>';
		echo '</table>';
		echo '</form>';
		echo $showPage;
		if ($reloadData) {
			echo "<script>window.parent.frames['class'].location.href='?m=topical&action=data&specid=$specid&classid=$classid';</script>";
		}
		$this->_showEnd ();
	}
	private function _showEnd() {
		echo '<script type="text/javascript">';
		echo '    jQuery.noConflict();';
		echo '    jQuery(function($) {';
		echo '        $("html").css("padding", "0");';
		echo '        $("#crumbnav, .tab-box").hide();';
		echo '        $("#selectAllTids").change(function(){';
		echo '            if($(this).prop("checked")) {';
		echo '                $("[name^=tid]").prop("checked", true);';
		echo '            } else {';
		echo '                $("[name^=tid]").prop("checked", false);';
		echo '            }';
		echo '        });';
		echo '        $("[name^=tid]").change(function() {';
		echo '            selectAll($("[name^=tid]"), $("#selectAllTids"));';
		echo '        });';
		echo '        $(".dataList tr")';
		echo '            .click(function(e) {';
		echo '                var that = $(this);';
		echo '                if($(e.target).is("td")) {';
		echo '                    var cb = that.find(":checkbox");';
		echo '                    cb.prop("checked", !cb.prop("checked")).change();';
		echo '                }';
		echo '            })';
		echo '            .mouseover(function() {';
		echo '                $(this).children("td").css("background", "#ffffcc");';
		echo '            })';
		echo '            .mouseout(function() {';
		echo '                $(this).children("td").css("background", "");';
		echo '            });';
		echo '        ';
		echo '        function selectAll(items, contorl) {';
		echo '            for(var i = 0; i < items.length; i++) {';
		echo '                if(!items[i].checked) {';
		echo '                    contorl.prop("checked", false);';
		echo '                    return;';
		echo '                }';
		echo '            }';
		echo '            contorl.prop("checked", true);';
		echo '        }';
		echo '    });';
		echo '</script>';
		echo '</body></html>';
	}

	// ------------------------------ Model ------------------------------
	private $_titleLength = 40;
	private function _specialIsExisted($specid) {
		$special = DB::fetch_first("SELECT tid FROM " . DB::table('special_thread') . " WHERE tid = '$specid'");
		if(!$special) {
			return false;
		} else {
			return true;
		}
	}
	private function _getDataList($specid, $classid, $page = 1, $pageSize = 0) {
		$pageSize = $pageSize ? $pageSize : $this->_pageSize;
		$startRow = ($page - 1) * $pageSize;

		$qs  = "SELECT t.tid, t.title, t.chanid\n";
		$qs .= "FROM " . DB::table('threads') . " AS t\n";
		$qs .= "INNER JOIN " . DB::table('special_data') . " AS sd USING (tid)\n";
		$qs .= "WHERE (sd.specid = '$specid') AND (sd.classid = '$classid')\n";
		$qs .= "ORDER BY sd.dateline DESC\n";
		$qs .= "LIMIT $startRow, $pageSize";
		// echo $qs;exit;

		$result = array();
		$query = DB::query($qs);
		while($row = DB::fetch_array($query)) {
			$row ['shortTitle'] = strcut($row['title'], $this->_titleLength);
			$result[] = $row;
		}

		return $result;
	}
	private function _getDataCount($specid, $classid) {
		return DB::result_first("SELECT COUNT(tid) AS counter FROM " . DB::table('special_data') . " WHERE (specid = '$specid') AND (classid = '$classid')");
	}
	private function _getList($keyword, $types, $specid, $classid, $page = 1, $pageSize = 0) {
		$pageSize = $pageSize ? $pageSize : $this->_pageSize;
		$startRow = ($page - 1) * $pageSize;

		$ids = array();
		$query = DB::query("SELECT tid FROM " . DB::table('special_data') . " WHERE (specid = '$specid') AND (classid = '$classid')");
		while($row = DB::fetch_array($query)) {
			$ids[] = $row['tid'];
		}
		$ids = implode(',', $ids);

		$sql = "SELECT t.tid, t.title, t.chanid, t.dateline\n";
		$sql .= "FROM " . DB::table ( 'threads' ) . " AS t\n";
		$sql .= "WHERE t.title LIKE '%$keyword%'\n";
		$sql .= "  AND ( 0\n";
		if (in_array ( 'article', $types )) {
			$sql .= "    OR t.tid IN (SELECT tid FROM " . DB::table ( 'article_thread' ) . ")\n";
		}
		if (in_array ( 'photo', $types )) {
			$sql .= "    OR t.tid IN (SELECT tid FROM " . DB::table ( 'photo_thread' ) . ")\n";
		}
		if (in_array ( 'soft', $types )) {
			$sql .= "    OR t.tid IN (SELECT tid FROM " . DB::table ( 'soft_thread' ) . ")\n";
		}
		if (in_array ( 'video', $types )) {
			$sql .= "    OR t.tid IN (SELECT tid FROM " . DB::table ( 'video_thread' ) . ")\n";
		}
		$sql .= "  )\n";
		if ($ids) {
			$sql .= "  AND t.tid NOT IN ($ids)\n";
		}
		$sql .= "ORDER BY t.dateline DESC\n";
		$sql .= "LIMIT $startRow, $pageSize";
		// echo $sql;exit;

		$SQL = DB::query ( $sql );
		$result = array ();
		while ( $row = DB::fetch_array ( $SQL ) ) {
			$row ['shortTitle'] = strcut ( $row ['title'], $this->_titleLength );
			$result [] = $row;
		}

		return $result;
	}
	private function _getCount($keyword, $types, $specid, $classid) {
		$ids = array();
		$query = DB::query("SELECT tid FROM " . DB::table('special_data') . " WHERE (specid = '$specid') AND (classid = '$classid')");
		while($row = DB::fetch_array($query)) {
			$ids[] = $row['tid'];
		}
		$ids = implode(',', $ids);

		$sql = "SELECT COUNT(t.tid) AS counter\n";
		$sql .= "FROM " . DB::table ( 'threads' ) . " AS t\n";
		$sql .= "WHERE t.title LIKE '%$keyword%'\n";
		$sql .= "  AND ( 0\n";
		if (in_array ( 'article', $types )) {
			$sql .= "    OR t.tid IN (SELECT tid FROM " . DB::table ( 'article_thread' ) . ")\n";
		}
		if (in_array ( 'photo', $types )) {
			$sql .= "    OR t.tid IN (SELECT tid FROM " . DB::table ( 'photo_thread' ) . ")\n";
		}
		if (in_array ( 'soft', $types )) {
			$sql .= "    OR t.tid IN (SELECT tid FROM " . DB::table ( 'soft_thread' ) . ")\n";
		}
		if (in_array ( 'video', $types )) {
			$sql .= "    OR t.tid IN (SELECT tid FROM " . DB::table ( 'video_thread' ) . ")\n";
		}
		$sql .= "  )\n";
		if ($ids) {
			$sql .= "  AND t.tid NOT IN ($ids)";
		}
		// echo $sql;exit;

		$counter = DB::result_first ( $sql );
		return $counter;
	}

	private function _addThreads($specid, $classid, $threads) {
		foreach($threads as $tid => $dateline) {
			$values[] = "($tid, $specid, $classid, $dateline)";
		}
		$values = implode(',', $values);
		DB::exec("INSERT INTO " . DB::table('special_data') . " (tid, specid, classid, dateline) VALUES " . $values);
		return true;
	}

	private function _deleteThreads($specid, $classid, $tids) {
		$tids = implode(',', $tids);
		DB::delete('special_data', "specid = $specid AND classid = $classid AND tid IN ($tids)");
		return true;
	}

	private function _getTitle($specid, $classid) {
		$specialName = DB::result_first("SELECT specname FROM " . DB::table('special_thread') . " WHERE tid = '$specid'");
		$className = DB::fetch_first("SELECT name, alias FROM " . DB::table('special_class') . " WHERE classid = '$classid'");
		return "{$specialName}" . ($className['name'] ? " - {$className['name']}" : '') . ($className['alias'] ? "({$className['alias']})" : '');
	}
}
