<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : chinese.php    2011-7-5 23:09:27
 */
!defined('IN_PHPCOM') && exit('Access denied');
define('CODETABLE_DIR', PHPCOM_PATH . './inc/table/');

class Chinese {

    var $Table = '';
    var $IconvEnabled = FALSE;
    var $ConvertBig5 = FALSE;
    var $UnicodeTable = array();
    var $Config = array(
        'SourceCharset' => '',
        'OutputCharset' => '',
        'GBtoUnicode_table' => 'gb-unicode.Table',
        'BIG5toUnicode_table' => 'big5-unicode.Table',
        'GBtoBIG5_table' => 'gb-big5.Table',
    );

    function __construct($in_charset, $out_charset, $forced = FALSE) {
        $this->Chinese($in_charset, $out_charset, $forced);
    }

    function Chinese($in_charset, $out_charset, $forced = FALSE) {
        $this->Config['SourceCharset'] = $this->_charset($in_charset);
        $this->Config['OutputCharset'] = $this->_charset($out_charset);

        if (ICONV_ENABLE && $this->Config['OutputCharset'] != 'BIG5' && !$forced) {
            $this->IconvEnabled = TRUE;
        } else {
            $this->IconvEnabled = FALSE;
            $this->OpenTable();
        }
    }

    function _charset($charset) {
        $charset = strtoupper($charset);

        if (substr($charset, 0, 2) == 'GB') {
            return 'GBK';
        } elseif (substr($charset, 0, 3) == 'BIG') {
            return 'BIG5';
        } elseif (substr($charset, 0, 3) == 'UTF') {
            return 'UTF-8';
        } elseif (substr($charset, 0, 3) == 'UNI') {
            return 'UNICODE';
        }
    }

    function _hex2bin($hexdata) {
        for ($i = 0; $i < strlen($hexdata); $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }
        return $bindata;
    }

    function OpenTable() {
        $this->UnicodeTable = array();
        if (!$this->IconvEnabled && $this->Config['OutputCharset'] == 'BIG5') {
            $this->Config['OutputCharset'] = 'GBK';
            $this->ConvertBig5 = TRUE;
        }
        if ($this->Config['SourceCharset'] == 'GBK' || $this->Config['OutputCharset'] == 'GBK') {
            $this->Table = CODETABLE_DIR . $this->Config['GBtoUnicode_table'];
        } elseif ($this->Config['SourceCharset'] == 'BIG5' || $this->Config['OutputCharset'] == 'BIG5') {
            $this->Table = CODETABLE_DIR . $this->Config['BIG5toUnicode_table'];
        }
        $fp = fopen($this->Table, 'rb');
        $tabletmp = fread($fp, filesize($this->Table));
        for ($i = 0; $i < strlen($tabletmp); $i += 4) {
            $tmp = unpack('nkey/nvalue', substr($tabletmp, $i, 4));
            if ($this->Config['OutputCharset'] == 'UTF-8') {
                $this->UnicodeTable[$tmp['key']] = '0x' . dechex($tmp['value']);
            } elseif ($this->Config['SourceCharset'] == 'UTF-8') {
                $this->UnicodeTable[$tmp['value']] = '0x' . dechex($tmp['key']);
            } elseif ($this->Config['OutputCharset'] == 'UNICODE') {
                $this->UnicodeTable[$tmp['key']] = dechex($tmp['value']);
            }
        }
    }

    function CHSUtoUTF8($c) {
        $str = '';
        if ($c < 0x80) {
            $str .= $c;
        } elseif ($c < 0x800) {
            $str .= ( 0xC0 | $c >> 6);
            $str .= ( 0x80 | $c & 0x3F);
        } elseif ($c < 0x10000) {
            $str .= ( 0xE0 | $c >> 12);
            $str .= ( 0x80 | $c >> 6 & 0x3F);
            $str .= ( 0x80 | $c & 0x3F);
        } elseif ($c < 0x200000) {
            $str .= ( 0xF0 | $c >> 18);
            $str .= ( 0x80 | $c >> 12 & 0x3F);
            $str .= ( 0x80 | $c >> 6 & 0x3F);
            $str .= ( 0x80 | $c & 0x3F);
        }
        return $str;
    }

    function GB2312toBIG5($c) {
        $f = fopen(CODETABLE_DIR . $this->Config['GBtoBIG5_table'], 'r');
        $max = strlen($c) - 1;
        for ($i = 0; $i < $max; $i++) {
            $h = ord($c[$i]);
            if ($h >= 160) {
                $l = ord($c[$i + 1]);
                if ($h == 161 && $l == 64) {
                    $gb = "  ";
                } else {
                    fseek($f, ($h - 160) * 510 + ($l - 1) * 2);
                    $gb = fread($f, 2);
                }
                $c[$i] = $gb[0];
                $c[$i + 1] = $gb[1];
                $i++;
            }
        }
        $result = $c;
        return $result;
    }

    function Convert($SourceText) {
        if ($this->Config['SourceCharset'] == $this->Config['OutputCharset']) {
            return $SourceText;
        } elseif ($this->IconvEnabled) {
            if ($this->Config['OutputCharset'] <> 'UNICODE') {
                return iconv($this->Config['SourceCharset'], $this->Config['OutputCharset'] . '//TRANSLIT', $SourceText);
            } else {
                $return = '';
                while ($SourceText != '') {
                    if (ord(substr($SourceText, 0, 1)) > 127) {
                        $return .= "&#x" . dechex($this->Utf8_Unicode(iconv($this->Config['SourceCharset'], "UTF-8", substr($SourceText, 0, 2)))) . ";";
                        $SourceText = substr($SourceText, 2, strlen($SourceText));
                    } else {
                        $return .= substr($SourceText, 0, 1);
                        $SourceText = substr($SourceText, 1, strlen($SourceText));
                    }
                }
                return $return;
            }
        } elseif ($this->Config['OutputCharset'] == 'UNICODE') {
            $utf = '';
            while ($SourceText != '') {
                if (ord(substr($SourceText, 0, 1)) > 127) {
                    if ($this->Config['SourceCharset'] == 'GBK') {
                        $utf .= '&#x' . $this->UnicodeTable[hexdec(bin2hex(substr($SourceText, 0, 2))) - 0x8080] . ';';
                    } elseif ($this->Config['SourceCharset'] == 'BIG5') {
                        $utf .= '&#x' . $this->UnicodeTable[hexdec(bin2hex(substr($SourceText, 0, 2)))] . ';';
                    }
                    $SourceText = substr($SourceText, 2, strlen($SourceText));
                } else {
                    $utf .= substr($SourceText, 0, 1);
                    $SourceText = substr($SourceText, 1, strlen($SourceText));
                }
            }
            return $utf;
        } else {
            $ret = '';
            if ($this->Config['SourceCharset'] == 'UTF-8') { 
                $out = '';
                $len = strlen($SourceText);
                $i = 0;
                while ($i < $len) {
                    $c = ord(substr($SourceText, $i++, 1));
                    switch ($c >> 4) {
                        case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7:
                            $out .= substr($SourceText, $i - 1, 1);
                            break;
                        case 12: case 13:
                            $char2 = ord(substr($SourceText, $i++, 1));
                            $char3 = $this->UnicodeTable[(($c & 0x1F) << 6) | ($char2 & 0x3F)];
                            if ($this->Config['OutputCharset'] == 'GBK') {
                                $out .= $this->_hex2bin(dechex($char3 + 0x8080));
                            } elseif ($this->Config['OutputCharset'] == 'BIG5') {
                                $out .= $this->_hex2bin($char3);
                            }
                            break;
                        case 14:
                            $char2 = ord(substr($SourceText, $i++, 1));
                            $char3 = ord(substr($SourceText, $i++, 1));
                            $char4 = $this->UnicodeTable[(($c & 0x0F) << 12) | (($char2 & 0x3F) << 6) | (($char3 & 0x3F) << 0)];
                            if ($this->Config['OutputCharset'] == 'GBK') {
                                $out .= $this->_hex2bin(dechex($char4 + 0x8080));
                            } elseif ($this->Config['OutputCharset'] == 'BIG5') {
                                $out .= $this->_hex2bin($char4);
                            }
                            break;
                    }
                }
                return !$this->ConvertBig5 ? $out : $this->GB2312toBIG5($out);
            } else {
                while ($SourceText != '') {
                    if (ord(substr($SourceText, 0, 1)) > 127) {
                        if ($this->Config['SourceCharset'] == 'BIG5') {
                            $utf8 = $this->CHSUtoUTF8(hexdec($this->UnicodeTable[hexdec(bin2hex(substr($SourceText, 0, 2)))]));
                        } elseif ($this->Config['SourceCharset'] == 'GBK') {
                            $utf8 = $this->CHSUtoUTF8(hexdec($this->UnicodeTable[hexdec(bin2hex(substr($SourceText, 0, 2))) - 0x8080]));
                        }
                        for ($i = 0; $i < strlen($utf8); $i += 3) {
                            $ret .= chr(substr($utf8, $i, 3));
                        }
                        $SourceText = substr($SourceText, 2, strlen($SourceText));
                    } else {
                        $ret .= substr($SourceText, 0, 1);
                        $SourceText = substr($SourceText, 1, strlen($SourceText));
                    }
                }
                $SourceText = '';
                return $ret;
            }
        }
    }

    function Utf8_Unicode($char) {
        switch (strlen($char)) {
            case 1:
                return ord($char);
            case 2:
                $n = (ord($char[0]) & 0x3f) << 6;
                $n += ord($char[1]) & 0x3f;
                return $n;
            case 3:
                $n = (ord($char[0]) & 0x1f) << 12;
                $n += ( ord($char[1]) & 0x3f) << 6;
                $n += ord($char[2]) & 0x3f;
                return $n;
            case 4:
                $n = (ord($char[0]) & 0x0f) << 18;
                $n += ( ord($char[1]) & 0x3f) << 12;
                $n += ( ord($char[2]) & 0x3f) << 6;
                $n += ord($char[3]) & 0x3f;
                return $n;
        }
    }

}

?>
