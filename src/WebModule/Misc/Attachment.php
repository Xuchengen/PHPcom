<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : Attachment.php  2012-8-9
 */
!defined('IN_PHPCOM') && exit('Access denied');

class Misc_Attachment extends Controller_MainAbstract
{
	public function loadActionIndex()
	{
		$attachid = intval($this->request->query('aid'));
		$chanid = intval($this->request->query('chanid'));
		if (empty(phpcom::$G['channel'])) {
			phpcom_cache::load('channel');
		}
		if(!isset(phpcom::$G['channel'][$chanid]['modules'])){
			showmessage('attachment_nonexistence');
		}
		$dirname = phpcom::$G['channel'][$chanid]['modules'];
		if (!$attach = DB::fetch_first("SELECT * FROM " . DB::table('attachment') . " WHERE attachid='$attachid'")) {
			showmessage('attachment_nonexistence');
		}
		$readmode = phpcom::$config['download']['readmode'];
		$readmode = $readmode > 0 && $readmode < 5 ? $readmode : 2;
		$isimage = $attach['image'];
		
		if ($attach['image'] && $attach['thumb']) {
			$db = DB::instance();
			$db->close();
			empty(phpcom::$config['output']['gzip']) && @ob_end_clean();
			@header('Content-Disposition: inline; filename=' . generatethumbname($attach['filename']));
			@header('Content-Type: image/jpeg');
			if ($attach['remote']) {
				if (phpcom::$setting['ftp']['hideurl']) {
					$this->remoteFileAccess(generatethumbname($attach['filename']), $dirname);
				} else {
					header('location: ' . phpcom::$setting['ftp']['attachurl'] . "$dirname/" . generatethumbname($attach['attachment']));
				}
			} else {
				$this->localFileAccess(phpcom::$setting['attachdir'] . "$dirname/" . generatethumbname($attach['attachment']));
			}
			exit();
		}
		
		$filename = phpcom::$setting['attachdir'] . "$dirname/" . $attach['attachment'];
		if (!$attach['remote'] && !is_readable($filename)) {
			showmessage('attachment_nonexistence');
		}
		$db = DB::instance();
		$db->close();
		empty(phpcom::$config['output']['gzip']) && @ob_end_clean();
		$range = 0;
		if ($readmode == 4 && isset($_SERVER['HTTP_RANGE']) && !empty($_SERVER['HTTP_RANGE'])) {
			list($range) = explode('-', (str_replace('bytes=', '', $_SERVER['HTTP_RANGE'])));
		}
		if ($attach['remote'] && !phpcom::$setting['ftp']['hideurl'] && $isimage) {
			@header('location: ' . phpcom::$setting['ftp']['attachurl'] . "$dirname/" . $attach['attachment']);
		}
		$filesize = !$attach['remote'] ? filesize($filename) : $attach['filesize'];
		$attach['filename'] = '"' . (strtolower(CHARSET) == 'utf-8' && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ? urlencode($attach['filename']) : $attach['filename']) . '"';
		
		@header('Date: ' . gmdate('D, d M Y H:i:s', $attach['dateline']) . ' GMT');
		@header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $attach['dateline']) . ' GMT');
		@header('Content-Encoding: none');
		if ($isimage) {
			@header('Content-Disposition: inline; filename=' . $attach['filename']);
		} else {
			@header('Content-Disposition: attachment; filename=' . $attach['filename']);
		}
		if ($isimage) {
			@header('Content-Type: image');
		} else {
			@header('Content-Type: application/octet-stream');
		}
		@header('Content-Length: ' . $filesize);
		$xsendfile = phpcom::$config['download']['xsendfile']; //Microsoft-IIS
		if (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== FALSE) {
			$xsendfile['type'] = 0;
		}
		if ($xsendfile['type'] && empty($xsendfile['dir'])) {
			$xsendfile['dir'] = substr(phpcom::$setting['attachdir'], strlen(PHPCOM_ROOT));
		}
		if (!empty($xsendfile)) {
			$type = intval($xsendfile['type']);
			$cmd = '';
			switch ($type) {
			case 1:
				$cmd = 'X-Accel-Redirect';
				$url = $xsendfile['dir'] . "$dirname/" . $attach['attachment'];
				break;
			case 2:
				$cmd = $_SERVER['SERVER_SOFTWARE'] < 'lighttpd/1.5' ? 'X-LIGHTTPD-send-file' : 'X-Sendfile';
				$url = $filename;
				break;
			case 3:
				$cmd = 'X-Sendfile';
				$url = $filename;
				break;
			}
			if ($cmd) {
				exit(header("$cmd: $url"));
			}
		}
		if ($readmode == 4) {
			@header('Accept-Ranges: bytes');
			if (isset($_SERVER['HTTP_RANGE']) && !empty($_SERVER['HTTP_RANGE'])) {
				$rangesize = ($filesize - $range) > 0 ? ($filesize - $range) : 0;
				@header('Content-Length: ' . $rangesize);
				@header('HTTP/1.1 206 Partial Content');
				@header('Content-Range: bytes=' . $range . '-' . ($filesize - 1) . '/' . ($filesize));
			}
		}
		$attach['remote'] ? $this->remoteFileAccess($attach['attachment'], $dirname) : $this->localFileAccess($filename, $readmode, $range);
		return 0;
	}
	
	protected function localFileAccess($filename, $readmode = 2, $range = 0) {
		if ($readmode == 1 || $readmode == 3 || $readmode == 4) {
			if ($fp = @fopen($filename, 'rb')) {
				@fseek($fp, $range);
				if (function_exists('fpassthru') && ($readmode == 3 || $readmode == 4)) {
					@fpassthru($fp);
				} else {
					echo @fread($fp, filesize($filename));
				}
			}
			@fclose($fp);
		} else {
			@readfile($filename);
		}
		@flush();
		@ob_flush();
	}
	
	protected function remoteFileAccess($file, $dir = 'soft') {
		@set_time_limit(0);
		if (!@readfile(phpcom::$setting['ftp']['attachurl'] . "$dir/$file")) {
			$ftp = ftpcommand('object');
			$tmpfile = @tempnam(phpcom::$setting['attachdir'], '');
			if ($ftp->ftp_get($tmpfile, "$dir/$file", FTP_BINARY)) {
				@readfile($tmpfile);
				@unlink($tmpfile);
			} else {
				@unlink($tmpfile);
				return false;
			}
		}
		return true;
	}
}
?>