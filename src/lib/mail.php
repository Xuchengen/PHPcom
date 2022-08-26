<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : mail.php    2012-1-7
 */
!defined('IN_PHPCOM') && exit('Access denied');

function sendmail($toemail, $subject, $message, $from = '') {
    if (empty(phpcom::$setting['mail']['status'])) {
        return FALSE;
    }
    $message = preg_replace("/href\=\"(?!http\:\/\/)(.+?)\"/i", 'href="' . phpcom::$G['siteurl'] . '\\1"', $message);
    $s = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=' . CHARSET . '">';
    $message = "$s<title>$subject</title></head><body>$subject<br />$message</body></html>";
    $maildelimiter = phpcom::$setting['mail']['delimiter'] == 1 ? "\r\n" : (phpcom::$setting['mail']['delimiter'] == 2 ? "\r" : "\n");
    $mailusername = isset(phpcom::$setting['mail']['mailusername']) ? phpcom::$setting['mail']['mailusername'] : 1;
    if (phpcom::$setting['mail']['status'] == 3) {
        $email_from = empty($from) ? phpcom::$setting['mail']['defaultfrom'] : $from;
    } else {
        $email_from = $from == '' ? '=?' . CHARSET . '?B?' . base64_encode(phpcom::$setting['webname']) . "?= <" . phpcom::$setting['mail']['defaultfrom'] . ">" : (preg_match('/^(.+?) \<(.+?)\>$/', $from, $mats) ? '=?' . CHARSET . '?B?' . base64_encode($mats[1]) . "?= <$mats[2]>" : $from);
    }
    $email_to = preg_match('/^(.+?) \<(.+?)\>$/', $toemail, $mats) ? ($mailusername ? '=?' . CHARSET . '?B?' . base64_encode($mats[1]) . "?= <$mats[2]>" : $mats[2]) : $toemail;
    $email_subject = '=?' . CHARSET . '?B?' . base64_encode(preg_replace("/[\r|\n]/", '', '[' . phpcom::$setting['webname'] . '] ' . $subject)) . '?=';
    $email_message = chunk_split(base64_encode(str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $message))))));
    $host = $_SERVER['HTTP_HOST'];
    $version = phpcom::$setting['version'];
    $headers = "From: $email_from{$maildelimiter}X-Priority: 3{$maildelimiter}X-Mailer: $host $version {$maildelimiter}MIME-Version: 1.0{$maildelimiter}Content-type: text/html; charset=" . CHARSET . "{$maildelimiter}Content-Transfer-Encoding: base64{$maildelimiter}";
    if (phpcom::$setting['mail']['status'] == 1) {
        if (function_exists('mail') && @mail($email_to, $email_subject, $email_message, $headers)) {
            return TRUE;
        }
        return FALSE;
    } elseif (phpcom::$setting['mail']['status'] == 2) {
        $mailserver = phpcom::$setting['mail']['server'];
        $mailport = phpcom::$setting['mail']['port'];
        if (!$fp = fsockopen($mailserver, $mailport, $errno, $errstr, 30)) {
            logwriter('SMTP', "($mailserver:$mailport) CONNECT - Unable to connect to the SMTP server");
            return FALSE;
        }
        stream_set_blocking($fp, true);

        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != '220') {
            logwriter('SMTP', "$mailserver:$mailport CONNECT - $lastmessage");
            return FALSE;
        }
        fputs($fp, (phpcom::$setting['mail']['smtpauth'] ? 'EHLO' : 'HELO') . " phpcom\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 220 && substr($lastmessage, 0, 3) != 250) {
            logwriter('SMTP', "($mailserver:$mailport) HELO/EHLO - $lastmessage");
            return FALSE;
        }
        while (1) {
            if (substr($lastmessage, 3, 1) != '-' || empty($lastmessage)) {
                break;
            }
            $lastmessage = fgets($fp, 512);
        }
        if (phpcom::$setting['mail']['smtpauth']) {
            fputs($fp, "AUTH LOGIN\r\n");
            $lastmessage = fgets($fp, 512);
            if (substr($lastmessage, 0, 3) != 334) {
                logwriter('SMTP', "($mailserver:$mailport) AUTH LOGIN - $lastmessage");
                return FALSE;
            }

            fputs($fp, base64_encode(phpcom::$setting['mail']['username']) . "\r\n");
            $lastmessage = fgets($fp, 512);
            if (substr($lastmessage, 0, 3) != 334) {
                logwriter('SMTP', "($mailserver:$mailport) USERNAME - $lastmessage");
                return FALSE;
            }

            fputs($fp, base64_encode(phpcom::$setting['mail']['password']) . "\r\n");
            $lastmessage = fgets($fp, 512);
            if (substr($lastmessage, 0, 3) != 235) {
                logwriter('SMTP', "($mailserver:$mailport) PASSWORD - $lastmessage");
                return FALSE;
            }

            $email_from = phpcom::$setting['mail']['mailfrom'];
        }
        fputs($fp, "MAIL FROM: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from) . ">\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 250) {
            fputs($fp, "MAIL FROM: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $email_from) . ">\r\n");
            $lastmessage = fgets($fp, 512);
            if (substr($lastmessage, 0, 3) != 250) {
                logwriter('SMTP', "($mailserver:$mailport) MAIL FROM - $lastmessage");
                return FALSE;
            }
        }

        fputs($fp, "RCPT TO: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $toemail) . ">\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 250) {
            fputs($fp, "RCPT TO: <" . preg_replace("/.*\<(.+?)\>.*/", "\\1", $toemail) . ">\r\n");
            $lastmessage = fgets($fp, 512);
            logwriter('SMTP', "($mailserver:$mailport) RCPT TO - $lastmessage");
            return FALSE;
        }

        fputs($fp, "DATA\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 354) {
            logwriter('SMTP', "($mailserver:$mailport) DATA - $lastmessage", 0);
            return FALSE;
        }
        $headers .= 'Message-ID: <' . gmdate('YmdHs') . '.' . substr(md5($email_message . microtime()), 0, 6) . rand(100000, 999999) . '@' . $_SERVER['HTTP_HOST'] . ">{$maildelimiter}";

        fputs($fp, "Date: " . gmdate('r') . "\r\n");
        fputs($fp, "To: " . $email_to . "\r\n");
        fputs($fp, "Subject: " . $email_subject . "\r\n");
        fputs($fp, $headers . "\r\n");
        fputs($fp, "\r\n\r\n");
        fputs($fp, "$email_message\r\n.\r\n");
        $lastmessage = fgets($fp, 512);
        if (substr($lastmessage, 0, 3) != 250) {
            logwriter('SMTP', "($mailserver:$mailport) END - $lastmessage");
        }
        fputs($fp, "QUIT\r\n");
        return TRUE;
    } elseif (phpcom::$setting['mail']['status'] == 3) {
        ini_set('SMTP', phpcom::$setting['mail']['server']);
        ini_set('smtp_port', phpcom::$setting['mail']['port']);
        ini_set('sendmail_from', $email_from);

        if (function_exists('mail') && @mail($email_to, $email_subject, $email_message, $headers)) {
            return TRUE;
        }
        return FALSE;
    }
}

?>
