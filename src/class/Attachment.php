<?php
/**
 * Copyright (c) 2010-2012 phpmain.com - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : Attachment.php  2012-6-10
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Attachment
{
	protected static $tableArrayIndex = array('article' => 1, 'soft' => 2, 'photo' => 3, 'special' => 4,'video' => 5, 'temp' => 127);
	protected static $tableIndexArray = array(0 => 'temp', 1 => 'article', 2 => 'soft', 3 => 'photo', 4 => 'special', 5 => 'video', 127 => 'temp');
	protected static $allowFtpUploaded = null;
	
	public static function getAttachDir($dirname = null)
	{
		return phpcom::$setting['attachdir'] . $dirname . DIRECTORY_SEPARATOR;
	}
	
	public static function getAttachNmae($name = null)
	{
		$format = trim(phpcom::$setting['attachsubdir'], '/\ \t\r\n');
		$format = empty($format) ? $format : 'Y/md';
		if(!($child = @date($format))){
			$child = date('Y/md');
		}
		return "$child/$name";
	}
	
	public static function getRandName($ext)
	{
		$ext = preg_replace("/[^0-9a-zA-Z_\.]+/", '', $ext);
		if($pos = strrpos($ext, '.')){
			$ext = trim(strtolower(substr($ext, $pos + 1, 10)));
		}
		$filename = date('His') . '_' . str_pad(mt_rand(10000000, 99999999), 8 ,'0') . '.' . str_replace('.', '', $ext);
		return $filename;
	}
	
	public static function removeQuery($fileName)
	{
		if($pos = strpos($fileName, '?')){
			$fileName = substr($fileName, 0, $pos);
		}
		if($pos = strpos($fileName, '#')){
			$fileName = substr($fileName, 0, $pos);
		}
		return $fileName;
	}

	public static function getFileName($path)
	{
		$path = self::removeQuery($path);
		return basename($path);
	}

	public static function getExtension($fileName)
	{
		$fileName = self::removeQuery($fileName);
		$fileName = preg_replace("/[^0-9a-zA-Z_\.]+/", '', $fileName);
		if($pos = strrpos($fileName, '.')){
			return trim(addslashes(strtolower(substr($fileName, $pos + 1, 10))));
		}else{
			return '';
		}
	}
	
	public static function moveFile($filename, $destination)
	{
		$flag = false;
		if(file_exists($filename)){
			if(!is_dir(dirname($destination))){
				@mkdir(dirname($destination), 0777, true);
			}
			if(@rename($filename, $destination)){
				$flag = true;
			}elseif(@copy($filename, $destination)){
				$flag = true;
				@unlink($filename);
			}
			if ($flag) {
				@chmod($destination, 0644);
			}
		}
		return $flag;
	}
	
	public static function getAttachId($uid = 0, $chanid = 0, $tid = 0, $tableid = 127)
	{
		$uid = !$uid ? phpcom::$G['uid'] : $uid;
		$tableid = $tableid ? $tableid : 127;
		return DB::insert('attachment', array('chanid' => $chanid, 'tid' => $tid, 'uid' => $uid, 'tableid' => $tableid), true);
	}

	public static function getAttachTableByaid($aid)
	{
		$tableid = DB::result_first("SELECT tableid FROM ".DB::table('attachment')." WHERE attachid='$aid'");
		return 'attachment_'.(isset(self::$tableIndexArray[$tableid]) ? self::$tableIndexArray[$tableid] : 'temp');
	}

	public static function getAttachTableId($chanid, $module = null)
	{
		if($module && isset(self::$tableArrayIndex[$module])){
			return self::$tableArrayIndex[$module];
		}
		if(isset(phpcom::$G['channel'][$chanid])){
			$module = phpcom::$G['channel'][$chanid]['modules'];
			return isset(self::$tableArrayIndex[$module]) ? self::$tableArrayIndex[$module] : 0;
		}
		return 0;
	}

	public static function getAttachTableBytid($tid)
	{
		$tableid = DB::result_first("SELECT tableid FROM ".DB::table('attachment')." WHERE tid='$tid' LIMIT 1");
		return 'attachment_'.(isset(self::$tableIndexArray[$tableid]) ? self::$tableIndexArray[$tableid] : 'temp');
	}

	function parseAttachment($attachtids, $module = 'article') {
		$attachtids = implodeids($attachtids);
		if($attachtids){
			$query = DB::query("SELECT * FROM ".DB::table("attachment_$module")." WHERE tid IN ($attachtids)");
			while($attach = DB::fetch_array($query)) {
				$attach['ext'] = self::getExtension($attach['filename']);
			}
		}
	}

	public static function unlinks($attach)
	{
		$module = $attach['module'];
		$filename = $attach['attachment'];
		$attachdir = rtrim(phpcom::$setting['attachdir'], '/\ ');
		if (empty($attach['remote'])) {
			@unlink(phpcom::$setting['attachdir'] . "/$module/$filename");
			!empty($attach['thumb']) && @unlink("$attachdir/$module/" . generatethumbname($filename));
			!empty($attach['preview']) && @unlink("$attachdir/$module/" . generatethumbname($filename, '_small.jpg'));
		} else {
			ftpcommand('delete', "$module/$filename");
			!empty($attach['thumb']) && ftpcommand('delete', "$module/" . generatethumbname($filename));
			!empty($attach['preview']) && ftpcommand('delete', "$module/" . generatethumbname($filename, '_small.jpg'));
		}
		if (isset($attach['attachid']) && !empty($attach['attachid'])) {
			@unlink("$attachdir/image/{$attach['attachid']}_135_135.jpg");
		}
	}

	public static function uploadUnlink($tmp)
	{
		$dirname = $tmp['dirname'];
		$tmp['filename'] = isset($tmp['attachment']) ? $tmp['attachment'] : $tmp['filename'];
		if($filename = $tmp['filename']){
			$attachdir = rtrim(phpcom::$setting['attachdir'], '/\ ');
			if (empty($tmp['remote'])) {
				@unlink("$attachdir/$dirname/$filename");
				!empty($tmp['thumb']) && @unlink("$attachdir/$dirname/" . generatethumbname($filename));
			}else{
				ftpcommand('delete', "$dirname/$filename");
				!empty($tmp['thumb']) && ftpcommand('delete', "$dirname/" . generatethumbname($filename));
			}
		}
	}

	public static function getUploadTemp($tmpid, $uid = 0)
	{
		$uid = $uid ? $uid : phpcom::$G['uid'];
		if($tmpid && $uid){
			if($tmp = DB::fetch_first("SELECT tmpid, uid, dirname, filename, filesize, image, thumb, remote FROM " . DB::table("upload_temp") . " WHERE uid='$uid' AND tmpid='$tmpid'")){
				$tmp['attachment'] = $tmp['filename'];
				return $tmp;
			}
		}
		return null;
	}
	
	public static function getUploadTempData($tmpname, $uid = 0)
	{
		$uid = $uid ? $uid : phpcom::$G['uid'];
		if($tmpname && $uid){
			$tmpname = addslashes(stripslashes(trim($tmpname)));
			$condition = is_numeric($tmpname) ? "tmpid='$tmpname'" : "filename='$tmpname'";
			if($tmp = DB::fetch_first("SELECT tmpid, uid, dirname, filename, filesize, image, thumb, remote FROM " . DB::table("upload_temp") . " WHERE uid='$uid' AND $condition LIMIT 1")){
				$tmp['attachment'] = $tmp['filename'];
				return $tmp;
			}
		}
		return false;
	}
	
	public static function ftpOneUpload($attach, $chanid = 0)
	{
		if($chanid && isset(phpcom::$G['channel'][$chanid])){
			if(empty(phpcom::$G['channel'][$chanid]['remoteon'])){
				return 0;
			}
		}
		if($attach && !empty($attach['dirname'])){
			$attach['attachment'] = isset($attach['attachment']) ? $attach['attachment'] : $attach['filename'];
			$attach['filesize'] = isset($attach['filesize']) ? intval($attach['filesize']) : 0;
			$attach['preview'] = isset($attach['preview']) ? intval($attach['preview']) : 0;
			$dirname = trim($attach['dirname']);
			if(self::ftpUploadAllowed($attach['attachment'], $attach['filesize'])){
				if (ftpcommand('upload', "$dirname/" . $attach['attachment']) &&
						(empty($attach['thumb']) || ftpcommand('upload', "$dirname/" . generatethumbname($attach['attachment']))) &&
						(empty($attach['preview']) || ftpcommand('upload', "$dirname/" . generatethumbname($attach['attachment'], '_small.jpg')))) {
					$attach['module'] = $dirname;
					$attach['remote'] = 0;
					self::unlinks($attach);
					return 1;
				}
			}
		}
		return 0;
	}

	public static function ftpUploadAllowed($filename, $filesize = 0)
	{
		if(!phpcom::$setting['ftp']['on'] || !($ext = self::getExtension($filename))){
			return false;
		}
		if(self::$allowFtpUploaded === null){
			self::$allowFtpUploaded = false;
			if (empty($filesize) || !phpcom::$setting['ftp']['minsize'] || $filesize >= phpcom::$setting['ftp']['minsize']) {
				if (!phpcom::$setting['ftp']['disallowext'] || !in_array($ext, phpcom::$setting['ftp']['disallowext'])) {
					if (!phpcom::$setting['ftp']['allowext'] || in_array($ext, phpcom::$setting['ftp']['allowext'])) {
						self::$allowFtpUploaded = true;
					}
				}
			}
		}
		return self::$allowFtpUploaded;
	}

	public static function ftpUpload($attachids, $chanid = 0, $module = null)
	{
		$chanid = $chanid ? $chanid : phpcom::$G['channelid'];
		$channel = array('modules' => 'misc', 'remoteon' => phpcom::$setting['ftp']['on']);

		if(isset(phpcom::$G['channel'][$chanid])){
			$channel = phpcom::$G['channel'][$chanid];
		}
		$module = $channel['modules'];
		if ($attachids && phpcom::$setting['ftp']['on'] && $channel['remoteon']) {
			$query = DB::query("SELECT attachid, chanid, attachment, filename, filesize, thumb, preview FROM " . DB::table("attachment_$module") . " WHERE attachid IN (" . implodeids($attachids) . ")");
			$attachids = array();
			while ($attach = DB::fetch_array($query)) {
				if(self::ftpUploadAllowed($attach['attachment'], $attach['filesize'])){
					if (ftpcommand('upload', "$module/" . $attach['attachment']) &&
							(empty($attach['thumb']) || ftpcommand('upload', "$module/" . generatethumbname($attach['attachment']))) &&
							(empty($attach['preview']) || ftpcommand('upload', "$module/" . generatethumbname($attach['attachment'], '_small.jpg')))) {
						$attach['module'] = $module;
						$attach['remote'] = 0;
						self::unlinks($attach);
						$attachids[] = $attach['attachid'];
					}
				}
			}
			if($attachids) {
				DB::update("attachment_$module", array('remote' => 1), "attachid IN (".implodeids($attachids).")");
			}
		}
		return 0;
	}
	/**
	 * Update upload file  (No use)
	 *
	 * @param unknown_type $attachid
	 * @param unknown_type $chanid
	 * @param unknown_type $data
	 * @return multitype:unknown |boolean
	 */
	public static function updateUpload($attachid, $chanid, $data)
	{
		$module = phpcom::$G['channel'][$chanid]['modules'];
		if($attach = DB::fetch_first("SELECT attachid, chanid, attachment, thumb, preview, remote FROM " . DB::table("attachment_$module") . " WHERE attachid='$attachid'")){
			$attach['module'] = $module;
			$attachid = $attach['attachid'];
			self::unlinks($attach);
			unset($data['uid'], $data['chanid']);
			DB::update("attachment_$module", $data, "attachid='$attachid'");
			self::ftpUpload(array($attachid), $chanid);
			return array('module' => $attach['module'], 'data' => $data);
		}
		return FALSE;
	}

	public static function setExtensionAndSize($type = 'image', $chanid = 1)
	{
		static $imageextensions = array('jpg', 'jpeg', 'gif', 'png', 'bmp');
		$attachextensions = explode(',', phpcom::$setting['allowattachext']);
		phpcom::$G['group']['attachext'] = phpcom::$G['group']['attachext'] ? phpcom::$G['group']['attachext'] : phpcom::$setting['allowattachext'];
		$attachexts = explode(',', phpcom::$G['group']['attachext']);
		$attachexts = array_intersect($attachextensions, $attachexts);
		$type = $type ? $type : 'image';
		if ($type == 'image') {
			$attachexts = array_intersect($imageextensions, $attachexts);
		}
		$unallowable = array('php', 'do', 'asp', 'asa', 'aspx', 'asax', 'jsp', 'cer', 'cdx', 'htr', 'shtml', 'shtm');
		foreach ($attachexts as $key => $value) {
			if (in_array($value, $unallowable)) {
				unset($attachexts[$key]);
			}
		}
		if (empty(phpcom::$setting['attachmaxsize'])) {
			if (function_exists('ini_get')) {
				phpcom::$setting['attachmaxsize'] = sizetobytes(ini_get('upload_max_filesize'));
			} else {
				phpcom::$setting['attachmaxsize'] = sizetobytes(get_cfg_var('upload_max_filesize'));
			}
		}
		if (empty(phpcom::$G['group']['maxattachsize'])) {
			phpcom::$G['group']['maxattachsize'] = phpcom::$setting['attachmaxsize'];
		} else {
			phpcom::$G['group']['maxattachsize'] = min(phpcom::$setting['attachmaxsize'], phpcom::$G['group']['maxattachsize']);
		}
		phpcom::$G['group']['attachext'] = $attachexts;
		if($chanid && isset(phpcom::$G['channel'][$chanid])){
			phpcom::$G['cache']['channel'] = phpcom::$G['channel'][$chanid];
			if (empty(phpcom::$setting['uploadstatus'])) {
				phpcom::$G['group']['allowupload'] = phpcom::$G['cache']['channel']['uploadstatus'] = 0;
			} elseif (phpcom::$setting['uploadstatus'] == 2) {
				phpcom::$G['cache']['channel']['uploadstatus'] = phpcom::$G['cache']['channel']['uploadstatus'] ? 2 : 0;
				phpcom::$G['group']['allowupload'] = phpcom::$G['group']['allowupload'] ? 2 : 0;
			}
			if (phpcom::$G['member']['groupid'] != 1) {
				if (empty(phpcom::$G['cache']['channel']['uploadstatus'])) {
					phpcom::$G['group']['allowupload'] = 0;
				} elseif (phpcom::$G['cache']['channel']['uploadstatus'] == 2) {
					phpcom::$G['group']['allowupload'] = phpcom::$G['group']['allowupload'] ? 2 : 0;
				}
			}
		}
		if (phpcom::$G['group']['allowupload'] && $type == 'image') {
			phpcom::$G['group']['allowupload'] = 2;
		}
	}

	public static function getAttachtemp($posttime = 0, $uid = 0, $chanid = 0, $type = 'image')
	{
		$uid = $uid ? $uid : phpcom::$G['uid'];
		$chanid = $chanid ? $chanid : phpcom::$G['channelid'];
		$condition = $posttime > 0 ? " AND dateline>'$posttime'" : '';
		$condition .= $type == 'image' ? " AND image='1'" : "";
		$attachdata = array();
		$i = 0;
		$query = DB::query("SELECT * FROM " . DB::table('attachment_temp') . " WHERE uid='$uid' AND chanid='$chanid' $condition");
		while ($attach = DB::fetch_array($query)) {
			$i++;
			$attach['key'] = md5($attach['attachid'] . substr(md5(phpcom::$config['security']['key']), 8) . $attach['uid']);
			$attach['sortord'] = $i;
			$attach['filename'] = basename($attach['attachment']);
			$attachdata[$attach['attachid']] = $attach;
		}
		return $attachdata;
	}

	public static function getAttachlist($tid, $image = 0, $module = 'article')
	{
		$attachdata = array();
		if($tid && $module){
			$condition = $image ? " AND image='1'" : " AND image='0'";
			$query = DB::query("SELECT * FROM " . DB::table("attachment_$module") . " WHERE tid='$tid'$condition ORDER BY sortord, attachid");
			while ($attach = DB::fetch_array($query)) {
				$attach['key'] = md5($attach['attachid'] . substr(md5(phpcom::$config['security']['key']), 8) . $attach['uid']);
				$attach['filename'] = basename($attach['attachment']);
				$attachdata[$attach['attachid']] = $attach;
			}
		}
		return $attachdata;
	}
	
	public static function getTmpAttach($filename, $dirname = 'image')
	{
		$tmp_filename = PHPCOM_ROOT . '/data/tmp/' . basename($filename) . '.tmp';
		if(file_exists($tmp_filename)){
			$dest_filename = self::getAttachDir($dirname) . $filename;
			if(self::moveFile($tmp_filename, $dest_filename)){
				return true;
			}
		}
		return false;
	}
}
?>