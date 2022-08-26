<?php

/**
 *  Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 *  Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 *  Description : This software is the proprietary information of phpcom.cn.
 *  This File   : setting.php
 */
!defined('IN_ADMINCP') && exit('Access denied');
include_once PHPCOM_PATH . '/phpcom_version.php';
$setting = array();
$settype = array();
$tableformisend = FALSE;
$query = DB::query("SELECT * FROM " . DB::table('setting'));
while ($row = DB::fetch_array($query)) {
    $settype[$row['skey']] = $row['stype'];
    if ($row['stype'] == 'array') {
        $setting[$row['skey']] = unserialized($row['svalue']);
    } else {
        $setting[$row['skey']] = $row['svalue'];
    }
}
if(!isset($setting['title'])) $setting['title'] = 'PHPcom CMS';
if(!isset($setting['posted'])) $setting['posted'] = '0';
if(!isset($setting['watermark']['gravity'])) $setting['watermark']['gravity'] = 1;
if(empty($setting['qrcodeurl'])) $setting['qrcodeurl'] = 'attachment';
if(empty($setting['qrcode'])) $setting['qrcode'] = array('status' => 0, 'level' => 0, 'size' => 3, 'margin' => 5,
		'background' => '#FFFFFF', 'foreground' => '#000000', 'text' => '');
if(empty($setting['mobile'])) $setting['mobile'] = array('status' => 0, 'domain' => '', 'logo' => '', 'register' => 0, 'captcha' => 0, 'type' => 0,
		'chanid' => 1, 'pagesize' => 10, 'expires' => 0, 'from' => '', 'charset' => 'utf-8', 'thumb' => 0, 'thumbwidth' => 0);
if(empty($setting['mobile']['charset'])) $setting['mobile']['charset'] = 'utf-8';
if(empty($setting['attachsubdir'])) $setting['attachsubdir'] = 'Y/md';
if(empty($setting['absoluteurl'])) $setting['absoluteurl'] = '0';
if(empty($setting['instdir'])) $setting['instdir'] = '/';
if(empty($setting['uricheck'])) $setting['uricheck'] = 0;
if(empty($setting['hotminimum'])) $setting['hotminimum'] = 100;
if(empty($setting['latestdays'])) $setting['latestdays'] = 7;
if(empty($setting['threadlog'])) $setting['threadlog'] = 0;
if(empty($setting['app'])) $setting['app'] = array('status' => 0, 'name' => '', 'domain' => '', 'title' => '', 'static' => 0, 'search' => 0, 'cache' => 0);

phpcom::$G['lang']['admin'] = 'setting';
if (checksubmit('btnsubmit')) {
    switch ($action) {
        case "attach": save_setting_attach($setting); break;
        case "register": save_setting_register($setting); break;
        case "mail": save_setting_mail($setting); break;
        case "qrcode": save_setting_qrcode($setting); break;
        case "mobile": save_setting_mobile($setting); break;
        case "mobile": save_setting_app($setting); break;
        case "imgwmk": save_setting_watermark($setting); break;
        case "remote": save_setting_ftp($setting); break;
        case "security": save_setting_security($setting); break;
 		case "search": save_setting_search($setting); break;
        case "searchword": save_setting_searchword($setting); break;
        default: save_setting_basic($setting);
            break;
    }
} else {
    $current = '';
    $active = 'first';
    if(in_array($action, array('register', 'attach', 'imgwmk', 'remote', 'mail', 'qrcode', 'mobile', 'app'))){
        $current = 'setting_' . $action;
        $active = $action;
    }
    if ($action == 'security') {
        admin_header('menu_setting', 'menu_setting_security');
        phpcom::$G['gp_anchor'] = isset(phpcom::$G['gp_anchor']) ? trim(phpcom::$G['gp_anchor']) : '';
        $anchored = in_array(phpcom::$G['gp_anchor'], array('questionset', 'question'));
        $navarray = array(
            array(
                'title' => 'menu_setting_security_captcha',
                'id' => 'securitycaptcha',
                'name' => $anchored ? 'captcha' : 'first',
                'onclick' => 'toggle_anchor(this,\'securitysetting\')'
            ),
            array(
                'title' => 'menu_setting_security_formset',
                'id' => 'securityformset',
                'name' => 'formset',
                'onclick' => 'toggle_anchor(this,\'securitysetting\')'
            ),
            array(
                'title' => 'menu_setting_security_restriction',
                'id' => 'securityrestriction',
                'name' => 'restriction',
                'onclick' => 'toggle_anchor(this,\'securitysetting\')'
            ),
            array(
                'title' => 'menu_setting_security_accesscontrol',
                'id' => 'securityaccesscontrol',
                'name' => 'accesscontrol',
                'onclick' => 'toggle_anchor(this,\'securitysetting\')'
            ),
            array(
                'title' => 'menu_setting_security_questionset',
                'id' => 'securityquestionset',
                'name' => $anchored ? 'first' : 'questionset',
                'onclick' => 'toggle_anchor(this,\'securitysetting\')'
            )
        );
    }elseif ($action == 'search' || $action == 'searchword') {
    	admin_header('menu_setting', 'menu_setting_search');
    	$navarray = array(
    			array(
    				'title' => 'menu_setting_search',
    				'url' => '?action=search&m=setting',
    				'name' => 'search',
    			),
    			array(
    				'title' => 'menu_setting_searchword',
    					'url' => '?action=searchword&m=setting',
    					'name' => 'searchword',
    			),
    			array(
    				'title' => 'menu_setting_basic',
    				'url' => '?action=basic&m=setting',
    				'name' => 'basic',
    			),
    	);
    	$active = $action == 'searchword' ? 'searchword': 'search';
    } else {
        admin_header('menu_setting', $action ? $admintitle : '');
        $navarray = array(
            array(
                'title' => 'menu_setting_basic',
                'url' => '?m=setting&action=basic',
                'name' => 'first'
            ),
            array(
                'title' => 'menu_setting_register',
                'url' => '?m=setting&action=register',
                'name' => 'register'
            ),
            array(
                'title' => 'menu_setting_attach',
                'url' => '?m=setting&action=attach',
                'name' => 'attach'
            ),
            array(
                'title' => 'menu_setting_imgwmk',
                'url' => '?m=setting&action=imgwmk',
                'name' => 'imgwmk'
            ),
            array(
                'title' => 'menu_setting_remote',
                'url' => '?m=setting&action=remote',
                'name' => 'remote'
            ),
            array(
                'title' => 'menu_setting_mail',
                'url' => '?m=setting&action=mail',
                'name' => 'mail'
            ),
        	array(
        		'title' => 'menu_setting_qrcode',
        		'url' => '?m=setting&action=qrcode',
        		'name' => 'qrcode'
        	),
        	/*array(
        		'title' => 'menu_setting_mobile',
        		'url' => '?m=setting&action=mobile',
        		'name' => 'mobile'
        	),
        	array(
        		'title' => 'menu_setting_app',
        		'url' => '?m=setting&action=app',
        		'name' => 'app'
        	)*/
        );
    }
    $adminhtml = phpcom_adminhtml::instance();
    $adminhtml->activetabs('global');
    $adminhtml->navtabs($navarray, $active);
    if ($action == 'register') {
        $adminhtml->form('m=setting&action=register');
        $adminhtml->table_header('menu_setting_register', 3);
        $adminhtml->table_setting('setting_register_status', 'mysetting[register][status]', intval($setting['register']['status']), 'radios', array(
            '0' => 'toggleDisplay(\'inviteclosebody\',\'show\')',
            '1' => 'toggleDisplay(\'inviteclosebody\',\'hide\')',
            '2' => 'toggleDisplay(\'inviteclosebody\',\'hide\')',
            '3' => 'toggleDisplay(\'inviteclosebody\',\'hide\')'
        ));
        $adminhtml->count = 0;
        echo '<tbody id="inviteclosebody"', $setting['register']['status'] != '0' ? ' style="display:none"' : '', ">\r\n";
        $adminhtml->table_setting('setting_register_closemessage', 'mysetting[register][closemessage]', $setting['register']['closemessage'], 'textarea');
        echo '</tbody>';
        $adminhtml->table_setting('setting_register_verify', 'mysetting[register][verify]', intval($setting['register']['verify']), 'radios');
        //$adminhtml->table_setting('setting_register_sucstatus', 'mysetting[register][sucstatus]', intval($setting['register']['sucstatus']), 'radios');
        //$adminhtml->table_setting('setting_register_reward', 'mysetting[register][reward]', intval($setting['register']['reward']), 'text');
        $adminhtml->table_setting('setting_register_modname', 'mysetting[register][modname]', $setting['register']['modname'], 'text');
        $adminhtml->table_setting('setting_register_interval', 'mysetting[register][interval]', intval($setting['register']['interval']), 'text');
        $adminhtml->table_setting('setting_register_limitnum', 'mysetting[register][limitnum]', intval($setting['register']['limitnum']), 'text');
        $adminhtml->table_setting('setting_register_minname', 'mysetting[register][minname]', intval($setting['register']['minname']), 'text');
        $adminhtml->table_setting('setting_register_maxname', 'mysetting[register][maxname]', intval($setting['register']['maxname']), 'text');
        $adminhtml->table_setting('setting_register_welcomesend', 'mysetting[register][welcomesend]', intval($setting['register']['welcomesend']), 'radios', array(
            '0' => 'toggleDisplay(\'welcomebody\',\'hide\')',
            '1' => 'toggleDisplay(\'welcomebody\',\'show\')',
            '2' => 'toggleDisplay(\'welcomebody\',\'show\')'
        ));
        echo '<tbody id="welcomebody"', $setting['register']['welcomesend'] == '0' ? ' style="display:none"' : '', ">\r\n";
        $adminhtml->table_setting('setting_register_welcometitle', 'mysetting[register][welcometitle]', $setting['register']['welcometitle'], 'text');
        $adminhtml->table_setting('setting_register_welcometext', 'mysetting[register][welcometext]', $setting['register']['welcometext'], 'textarea');
        echo '</tbody>';
        $adminhtml->table_setting('setting_register_showterms', 'mysetting[register][showterms]', intval($setting['register']['showterms']), 'radios', array(
            '0' => 'toggleDisplay(\'termsbody\',\'hide\')',
            '1' => 'toggleDisplay(\'termsbody\',\'show\')'
        ));
        echo '<tbody id="termsbody"', $setting['register']['showterms'] == '0' ? ' style="display:none"' : '', ">\r\n";
        $adminhtml->table_setting('setting_register_forceterms', 'mysetting[register][forceterms]', intval($setting['register']['forceterms']), 'radio');
        $adminhtml->table_setting('setting_register_termstext', 'mysetting[register][termstext]', $setting['register']['termstext'], 'textarea');
        echo '</tbody>';
    } elseif ($action == 'search') {
    	$adminhtml->form('m=setting&action=search');
    	$adminhtml->table_header('menu_setting_search', 3);
    	$adminhtml->table_setting('setting_search_closed', 'mysetting[search][closed]', intval($setting['search']['closed']), 'radio');
    	$adminhtml->table_setting('setting_search_image', 'mysetting[search][image]', intval($setting['search']['image']), 'radios');
    	$adminhtml->table_setting('setting_search_maxresult', 'mysetting[search][maxresult]', intval($setting['search']['maxresult']), 'text');
    	$adminhtml->table_setting('setting_search_pagesize', 'mysetting[search][pagesize]', intval($setting['search']['pagesize']), 'text');
    	$adminhtml->table_setting('setting_search_timeout', 'mysetting[search][timeout]', intval($setting['search']['timeout']), 'text');
    	$adminhtml->table_setting('setting_search_lifetime', 'mysetting[search][lifetime]', intval($setting['search']['lifetime']), 'text');
    	$adminhtml->table_setting('setting_search_maxwords', 'mysetting[search][maxwords]', intval($setting['search']['maxwords']), 'text');
    	$adminhtml->table_setting('setting_search_fulltext', 'mysetting[search][fulltext]', intval($setting['search']['fulltext']), 'radio');
    	//$adminhtml->table_end();
    	//$adminhtml->table_header('setting_search_sphinx', 3);
    	echo '<tbody style="display:none">';
    	$adminhtml->table_setting('setting_search_sphinxon', 'mysetting[search][sphinxon]', intval($setting['search']['sphinxon']), 'radio');
    	$adminhtml->table_setting('setting_search_sphinxhost', 'mysetting[search][sphinxhost]', trim($setting['search']['sphinxhost']), 'text');
    	$adminhtml->table_setting('setting_search_sphinxport', 'mysetting[search][sphinxport]', trim($setting['search']['sphinxport']), 'text');
    	$adminhtml->table_setting('setting_search_sphinxtitindex', 'mysetting[search][sphinxtitindex]', trim($setting['search']['sphinxtitindex']), 'text');
    	$adminhtml->table_setting('setting_search_sphinxtxtindex', 'mysetting[search][sphinxtxtindex]', trim($setting['search']['sphinxtxtindex']), 'text');
    	$adminhtml->table_setting('setting_search_sphinxmaxtime', 'mysetting[search][sphinxmaxtime]', intval($setting['search']['sphinxmaxtime']), 'text');
    	$adminhtml->table_setting('setting_search_sphinxlimit', 'mysetting[search][sphinxlimit]', intval($setting['search']['sphinxlimit']), 'text');
    	$adminhtml->table_setting('setting_search_sphinxrank', 'mysetting[search][sphinxrank]', trim($setting['search']['sphinxrank']), 'select');
    	echo '</tbody>';
    } elseif ($action == 'searchword') {
    	$tableformisend = TRUE;
    	$adminhtml->form('m=setting&action=searchword');
    	$adminhtml->table_header('setting_search_hotword', 3);
    	$adminhtml->table_td(array(array('setting_search_hotword_tips', FALSE, 'colspan="7"')), NULL, FALSE, NULL, NULL, FALSE);
    	$adminhtml->table_th(array(
    		array('deletecheckbox', 'class="left" noWrap="noWrap"'),
    		array('order', 'class="left"'),
    		array('setting_search_hotword_word', 'class="left"'),
    		array('setting_search_hotword_tn', 'class="left"'),
    		array('setting_search_hotword_url', 'class="left"'),
    		array('setting_search_hotword_highlight', 'class="left"'),
    		array('setting_search_hotword_target', 'class="left"')
    	));
    	$targets = adminlang('targets');
    	$query = DB::query("SELECT id, word, tn, highlight, url, target, sortord FROM " . DB::table('searchword') . " ORDER BY sortord");
    	while ($row = DB::fetch_array($query)) {
    		$sid = $row['id'];
    		$target_select = "<select name=\"searchword[$sid][target]\" class=\"select\">";
    		foreach ($targets as $key => $value) {
    			$target_select .= "<option value=\"$key\"";
    			$target_select .= ( $key == $row['target']) ? ' SELECTED' : '';
    			$target_select .=">$value</option>";
    		}
    		$target_select .= '</select>';
    		$adminhtml->table_td(array(
    			array('<input type="checkbox" class="checkbox" name="delete[]" value="' . $sid . '" />', FALSE, 'noWrap="noWrap"'),
    			array($adminhtml->textinput("searchword[$sid][sortord]", $row['sortord'], 1, '', '', '', '', 'sortord'), TRUE),
    			array($adminhtml->inputedit("searchword[$sid][word]", $row['word'], 10, 'left'), TRUE),
    			array($adminhtml->textinput("searchword[$sid][tn]", $row['tn'], 5), TRUE),
    			array($adminhtml->textinput("searchword[$sid][url]", $row['url'], 35), TRUE),
    			array($adminhtml->highlight_select($row['highlight'], "searchword[$sid][highlight]", $sid), TRUE, ''),
    			array($target_select, TRUE, '')
    		));
    	}
    	
    	$target_select = '<select name="searchwordnew[target]" class="select">';
    	foreach ($targets as $key => $value) {
    		$target_select .= "<option value=\"$key\"";
    		$target_select .= ( $key == 1) ? ' SELECTED' : '';
    		$target_select .=">$value</option>";
    	}
    	$target_select .= '</select>';
    	$adminhtml->table_td(array(
    		array('add', FALSE, 'noWrap="noWrap"'),
    		array($adminhtml->textinput("searchwordnew[sortord]", '0', 1, '', '', '', '', 'sortord'), TRUE),
    		array($adminhtml->textinput("searchwordnew[word]", '', 10), TRUE),
    		array($adminhtml->textinput("searchwordnew[tn]", '', 5), TRUE),
    		array($adminhtml->textinput("searchwordnew[url]", '', 35), TRUE),
    		array($adminhtml->highlight_select(0, 'searchwordnew[highlight]'), TRUE, ''),
    		array($target_select, TRUE, '')
    	));
    	$adminhtml->table_td(array(
    			array('&nbsp;', TRUE, 'align="center"'),
    			array($adminhtml->submit_button(), TRUE, 'colspan="6"')
    	), NULL, FALSE, NULL, NULL, FALSE);
    	$adminhtml->table_end('</form>');
    	
    } elseif ($action == 'mail') {
        echo <<<EOT
<script type="text/javascript">
function changeSendMail(v){
	if(v == 2){
		\$('sendmailbody_1').style.display = '';
		\$('sendmailbody_2').style.display = '';
	}else if(v == 3){
		\$('sendmailbody_1').style.display = '';
		\$('sendmailbody_2').style.display = 'none';
	}else{
		\$('sendmailbody_1').style.display = 'none';
		\$('sendmailbody_2').style.display = 'none';
	}
}
</script>
EOT;
        $adminhtml->form('m=setting&action=mail');
        $adminhtml->table_header('menu_setting_mail', 3);
        $adminhtml->table_setting('setting_mail_status', 'mysetting[mail][status]', intval($setting['mail']['status']), 'select', "changeSendMail(this.value)");
        $adminhtml->table_setting('setting_mail_defaultfrom', 'mysetting[mail][defaultfrom]', $setting['mail']['defaultfrom'], 'text');
        echo '<tbody id="sendmailbody_1"', $setting['mail']['status'] < 2 ? ' style="display:none"' : '', ">\r\n";
        $adminhtml->table_setting('setting_mail_server', 'mysetting[mail][server]', $setting['mail']['server'], 'text');
        $adminhtml->table_setting('setting_mail_port', 'mysetting[mail][port]', $setting['mail']['port'], 'text');
        echo '<tbody>';
        echo '<tbody id="sendmailbody_2"', $setting['mail']['status'] != 2 ? ' style="display:none"' : '', ">\r\n";
        $adminhtml->table_setting('setting_mail_smtpauth', 'mysetting[mail][smtpauth]', intval($setting['mail']['smtpauth']), 'radio');
        $adminhtml->table_setting('setting_mail_username', 'mysetting[mail][username]', $setting['mail']['username'], 'text');
        $password = $setting['mail']['password'];
        if ($password) {
            $password = cutstr($password, 2, '********');
        }
        $adminhtml->table_setting('setting_mail_password', 'mysetting[mail][password]', $password, 'text');
        $adminhtml->table_setting('setting_mail_mailfrom', 'mysetting[mail][mailfrom]', $setting['mail']['mailfrom'], 'text');
        echo '<tbody>';
        $adminhtml->table_setting('setting_mail_delimiter', 'mysetting[mail][delimiter]', intval($setting['mail']['delimiter']), 'radios', '', '', '', 'chkbox2');
        $adminhtml->table_setting('setting_mail_mailusername', 'mysetting[mail][mailusername]', intval($setting['mail']['mailusername']), 'radio');
        $adminhtml->table_setting('setting_mail_silent', 'mysetting[mail][silent]', intval($setting['mail']['silent']), 'radio');
    } elseif ($action == 'qrcode') {
    	$adminhtml->form('m=setting&action=qrcode');
    	$adminhtml->table_header('menu_setting_qrcode', 3);
    	$adminhtml->table_setting('setting_qrcodeurl', 'mysetting[qrcodeurl]', trim($setting['qrcodeurl']), 'text');
    	$adminhtml->table_setting('setting_qrcode_status', 'mysetting[qrcode][status]', intval($setting['qrcode']['status']), 'radios');
    	$adminhtml->table_setting('setting_qrcode_level', 'mysetting[qrcode][level]', intval($setting['qrcode']['level']), 'select');
    	$adminhtml->table_setting('setting_qrcode_size', 'mysetting[qrcode][size]', intval($setting['qrcode']['size']), 'text');
    	$adminhtml->table_setting('setting_qrcode_margin', 'mysetting[qrcode][margin]', intval($setting['qrcode']['margin']), 'text');
    	//$adminhtml->table_setting('setting_qrcode_background', 'mysetting[qrcode][background]', trim($setting['qrcode']['background']), 'text');
    	//$adminhtml->table_setting('setting_qrcode_foreground', 'mysetting[qrcode][foreground]', trim($setting['qrcode']['foreground']), 'text');
    	$adminhtml->table_setting('setting_qrcode_text', 'mysetting[qrcode][text]', trim($setting['qrcode']['text']), 'text');
    	$adminhtml->table_setting('setting_qrcode_makefile', 'makefile', 0, 'radio');
    } elseif ($action == 'mobile') {
    	$adminhtml->form('m=setting&action=mobile');
    	$adminhtml->table_header('menu_setting_mobile', 3);
    	$adminhtml->table_setting('setting_mobile_status', 'mysetting[mobile][status]', intval($setting['mobile']['status']), 'radios');
    	$adminhtml->table_setting('setting_mobile_domain', 'mysetting[mobile][domain]', trim($setting['mobile']['domain']), 'text');
    	$adminhtml->table_setting('setting_mobile_logo', 'mysetting[mobile][logo]', trim($setting['mobile']['logo']), 'text');
    	$adminhtml->table_setting('setting_mobile_charset', 'mysetting[mobile][charset]', strtolower(trim($setting['mobile']['charset'])), 'radios');
    	$adminhtml->table_setting('setting_mobile_register', 'mysetting[mobile][register]', intval($setting['mobile']['register']), 'radio');
    	$adminhtml->table_setting('setting_mobile_captcha', 'mysetting[mobile][captcha]', intval($setting['mobile']['captcha']), 'radio');
    	$adminhtml->table_setting('setting_mobile_type', 'mysetting[mobile][type]', intval($setting['mobile']['type']), 'radios');
    	$adminhtml->table_setting('setting_mobile_chanid', 'mysetting[mobile][chanid]', intval($setting['mobile']['chanid']), 'text');
    	$adminhtml->table_setting('setting_mobile_pagesize', 'mysetting[mobile][pagesize]', intval($setting['mobile']['pagesize']), 'text');
    	$adminhtml->table_setting('setting_mobile_expires', 'mysetting[mobile][expires]', intval($setting['mobile']['expires']), 'text');
    	$adminhtml->table_setting('setting_mobile_from', 'mysetting[mobile][from]', trim($setting['mobile']['from']), 'textarea');
    } elseif ($action == 'app') {
    	$adminhtml->form('m=setting&action=app');
    	$adminhtml->table_header('menu_setting_app', 3);
    	$adminhtml->table_setting('setting_app_status', 'mysetting[app][status]', intval($setting['app']['status']), 'radios');
    	$adminhtml->table_setting('setting_app_domain', 'mysetting[app][domain]', trim($setting['app']['domain']), 'text');
    	
    } elseif ($action == 'attach') {
        $adminhtml->form('m=setting&action=attach');
        $adminhtml->table_header('menu_setting_attach', 3);
        $adminhtml->table_setting('setting_attach_uploadstatus', 'mysetting[uploadstatus]', $setting['uploadstatus'], 'select');
        $adminhtml->table_setting('setting_attach_attachdir', 'mysetting[attachdir]', trim($setting['attachdir']), 'text');
        $adminhtml->table_setting('setting_attach_attachurl', 'mysetting[attachurl]', trim($setting['attachurl']), 'text');
        $adminhtml->table_setting('setting_attach_attachsubdir', 'mysetting[attachsubdir]', trim($setting['attachsubdir']), 'text');
        $adminhtml->table_setting('setting_attach_attachmaxsize', 'mysetting[attachmaxsize]', round(intval($setting['attachmaxsize']) / 1024), 'text');
        $adminhtml->table_setting('setting_attach_allowattachext', 'mysetting[allowattachext]', $setting['allowattachext'], 'text');
        $uploadmodes = array();
        if ($setting['uploadmode'] > 1) {
            $uploadmodes[0] = 0;
            $uploadmodes[1] = 1;
        } else {
            $uploadmodes[0] = intval($setting['uploadmode']);
        }
        $adminhtml->table_setting('setting_attach_uploadmode', 'mysetting[uploadmode]', $uploadmodes, 'checkbox');
        $adminhtml->table_setting('setting_attach_imagelib', 'mysetting[imagelib]', intval($setting['imagelib']), 'radios');
        $adminhtml->table_setting('setting_attach_imageimpath', 'mysetting[imageimpath]', $setting['imageimpath'], 'text');
        $adminhtml->table_setting('setting_attach_thumbquality', 'mysetting[thumbquality]', intval($setting['thumbquality']), 'text');
    } elseif ($action == 'imgwmk') {
        $adminhtml->form('m=setting&action=imgwmk');
        $adminhtml->table_header('menu_setting_imgwmk', 3);
        $adminhtml->table_setting('setting_watermark_status', 'mysetting[watermark][status]', intval($setting['watermark']['status']), 'radios');
        $adminhtml->table_setting('setting_watermark_gravity', 'mysetting[watermark][gravity]', intval($setting['watermark']['gravity']), 'select');
        $adminhtml->table_setting('setting_watermark_type', 'mysetting[watermark][type]', $setting['watermark']['type'], 'radios', array(
            'gif' => 'toggleDisplay(\'watermarktextbody\',\'hide\')',
            'png' => 'toggleDisplay(\'watermarktextbody\',\'hide\')',
            'text' => 'toggleDisplay(\'watermarktextbody\',\'show\')'
        ));
        $adminhtml->table_setting('setting_watermark_file', 'mysetting[watermark][file]', $setting['watermark']['file'], 'text');
        $adminhtml->table_setting('setting_watermark_minsize', array('mysetting[watermark][minwidth]', 'mysetting[watermark][minheight]'), array(intval($setting['watermark']['minwidth']), intval($setting['watermark']['minheight'])), 'text2');
        $adminhtml->table_setting('setting_watermark_composite', 'mysetting[watermark][composite]', intval($setting['watermark']['composite']), 'text');
        $adminhtml->table_setting('setting_watermark_quality', 'mysetting[watermark][quality]', intval($setting['watermark']['quality']), 'text');
        echo '<tbody id="watermarktextbody"', $setting['watermark']['type'] == 'text' ? '' : ' style="display:none"', ">\r\n";
        $adminhtml->table_setting('setting_watermark_text', 'mysetting[watermark][text]', $setting['watermark']['text'], 'textarea');
        $adminhtml->table_setting('setting_watermark_fontpath', 'mysetting[watermark][fontpath]', $setting['watermark']['fontpath'], 'text');
        $adminhtml->table_setting('setting_watermark_fontsize', 'mysetting[watermark][fontsize]', intval($setting['watermark']['fontsize']), 'text');
        $adminhtml->table_setting('setting_watermark_fontcolor', 'mysetting[watermark][fontcolor]', $setting['watermark']['fontcolor'], 'textcolor');
        $adminhtml->table_setting('setting_watermark_shadowcolor', 'mysetting[watermark][shadowcolor]', $setting['watermark']['shadowcolor'], 'textcolor');
        $adminhtml->table_setting('setting_watermark_angle', 'mysetting[watermark][angle]', intval($setting['watermark']['angle']), 'text');
        $adminhtml->table_setting('setting_watermark_shadowx', 'mysetting[watermark][shadowx]', intval($setting['watermark']['shadowx']), 'text');
        $adminhtml->table_setting('setting_watermark_shadowy', 'mysetting[watermark][shadowy]', intval($setting['watermark']['shadowy']), 'text');
        $adminhtml->table_setting('setting_watermark_translatex', 'mysetting[watermark][translatex]', intval($setting['watermark']['translatex']), 'text');
        $adminhtml->table_setting('setting_watermark_translatex', 'mysetting[watermark][translatey]', intval($setting['watermark']['translatey']), 'text');
        $adminhtml->table_setting('setting_watermark_skewx', 'mysetting[watermark][skewx]', intval($setting['watermark']['skewx']), 'text');
        $adminhtml->table_setting('setting_watermark_skewy', 'mysetting[watermark][skewy]', intval($setting['watermark']['skewy']), 'text');
        echo "</tbody>\r\n";
    } elseif ($action == 'remote') {
        $adminhtml->form('m=setting&action=remote');
        $adminhtml->table_header('menu_setting_remote', 3);
        $adminhtml->table_setting('setting_ftp_on', 'mysetting[ftp][on]', intval($setting['ftp']['on']), 'radios', array(
            '0' => 'toggleDisplay(\'remoteftpbody\',\'hide\')',
            '1' => 'toggleDisplay(\'remoteftpbody\',\'show\')'
        ));
        $adminhtml->table_setting('setting_ftp_hideurl', 'mysetting[ftp][hideurl]', intval($setting['ftp']['hideurl']), 'radio');
        echo '<tbody id="remoteftpbody"', $setting['ftp']['on'] ? '' : ' style="display:none"', ">\r\n";
        $adminhtml->table_setting('setting_ftp_ssl', 'mysetting[ftp][ssl]', intval($setting['ftp']['ssl']), 'radio');
        $adminhtml->table_setting('setting_ftp_host', 'mysetting[ftp][host]', $setting['ftp']['host'], 'text');
        $adminhtml->table_setting('setting_ftp_port', 'mysetting[ftp][port]', intval($setting['ftp']['port']), 'text');
        $adminhtml->table_setting('setting_ftp_username', 'mysetting[ftp][username]', $setting['ftp']['username'], 'text');
        $password = $setting['ftp']['password'];
        if ($password) {
            $password = cutstr($password, 2, '********');
        }
        $adminhtml->table_setting('setting_ftp_password', 'mysetting[ftp][password]', $password, 'text');
        $adminhtml->table_setting('setting_ftp_pasv', 'mysetting[ftp][pasv]', intval($setting['ftp']['pasv']), 'radio');
        $adminhtml->table_setting('setting_ftp_timeout', 'mysetting[ftp][timeout]', $setting['ftp']['timeout'], 'text');
        $adminhtml->table_setting('setting_ftp_attachdir', 'mysetting[ftp][attachdir]', $setting['ftp']['attachdir'], 'text');
        echo "</tbody>\r\n";
        $adminhtml->table_setting('setting_ftp_attachurl', 'mysetting[ftp][attachurl]', $setting['ftp']['attachurl'], 'text');
        $adminhtml->table_setting('setting_ftp_minsize', 'mysetting[ftp][minsize]', round(intval($setting['ftp']['minsize']) / 1024), 'text');
        $adminhtml->table_setting('setting_ftp_allowext', 'mysetting[ftp][allowext]', trim($setting['ftp']['allowext']), 'text');
        $adminhtml->table_setting('setting_ftp_disallowext', 'mysetting[ftp][disallowext]', trim($setting['ftp']['disallowext']), 'text');
    } elseif ($action == 'security') {
        $adminhtml->form('m=setting&action=security&anchor=' . phpcom::$G['gp_anchor']);
        $adminhtml->table_header('setting_security_captcha', 3, '', 'tableborder', FALSE, 'securitysetting');
        echo '<tbody id="securitycaptcha"', $anchored ? ' style="display:none"' : '', '>';
        $adminhtml->table_setting('setting_security_captchastatus', 'mysetting[captchastatus]', $setting['captchastatus'], 'checkboxs');
        $adminhtml->table_setting('setting_security_captcha_type', 'mysetting[captcha][type]', $setting['captcha']['type'], 'radios');
        $adminhtml->table_setting('setting_security_captcha_width', 'mysetting[captcha][width]', intval($setting['captcha']['width']), 'text');
        $adminhtml->table_setting('setting_security_captcha_height', 'mysetting[captcha][height]', intval($setting['captcha']['height']), 'text');
        $adminhtml->table_setting('setting_security_captcha_background', 'mysetting[captcha][background]', intval($setting['captcha']['background']), 'radio');
        $adminhtml->table_setting('setting_security_captcha_randline', 'mysetting[captcha][randline]', intval($setting['captcha']['randline']), 'radio');
        $adminhtml->table_setting('setting_security_captcha_blurtext', 'mysetting[captcha][blurtext]', intval($setting['captcha']['blurtext']), 'radio');
        $adminhtml->table_setting('setting_security_captcha_giffont', 'mysetting[captcha][giffont]', intval($setting['captcha']['giffont']), 'radio');
        echo '</tbody>';
        echo '<tbody id="securityformset" style="display:none">';
        $adminhtml->table_setting('setting_security_formset_username', 'mysetting[formset][username]', $setting['formset']['username'], 'text');
        $adminhtml->table_setting('setting_security_formset_password', 'mysetting[formset][password]', $setting['formset']['password'], 'text');
        $adminhtml->table_setting('setting_security_formset_password2', 'mysetting[formset][password2]', $setting['formset']['password2'], 'text');
        $adminhtml->table_setting('setting_security_formset_email', 'mysetting[formset][email]', $setting['formset']['email'], 'text');
        echo '</tbody>';
        echo '<tbody id="securityrestriction" style="display:none">';
        $adminhtml->table_setting('setting_register_repeatemail', 'mysetting[repeatemail]', intval($setting['repeatemail']), 'radio');
        $adminhtml->table_setting('setting_register_allowsemail', 'mysetting[allowsemail]', trim($setting['allowsemail']), 'textarea');
        $adminhtml->table_setting('setting_register_bannedemail', 'mysetting[bannedemail]', trim($setting['bannedemail']), 'textarea');
        $adminhtml->table_setting('setting_register_banusername', 'mysetting[banusername]', trim($setting['banusername']), 'textarea');
        $adminhtml->table_setting('setting_register_allowsregip', 'mysetting[allowsregip]', trim($setting['allowsregip']), 'textarea');
        echo '</tbody>';
        echo '<tbody id="securityaccesscontrol" style="display:none">';
        $adminhtml->table_setting('setting_security_allowipaccess', 'mysetting[allowipaccess]', trim($setting['allowipaccess']), 'textarea');
        $adminhtml->table_setting('setting_security_adminipaccess', 'mysetting[adminipaccess]', trim($setting['adminipaccess']), 'textarea');
        echo '</tbody>';
        echo '<tbody id="securityquestionset"', $anchored ? '' : ' style="display:none"', '>';
        $adminhtml->tablesetmode = FALSE;
        $adminhtml->table_td(array(
            array('delete', FALSE, 'width="15%" align="center"'),
            array('question', FALSE, 'width="35%"'),
            array('answer', FALSE, 'width="35%"')
                ), '', FALSE, ' tablerow');
        $adminhtml->table_setting('setting_security_questionstatus', 'mysetting[questionstatus]', $setting['questionstatus'], 'checkboxs');
        $calculation = 0;
        $queryurl = "&action=security&anchor=questionset";
        $totalrec = DB::result_first("SELECT COUNT(*) FROM " . DB::table('questionset'));
        $pagenow = $page;
        $pagesize = 10;
        $pagecount = @ceil($totalrec / $pagesize);
        $pagenow > $pagecount && $pagenow = 1;
        $pagestart = floor(($pagenow - 1) * $pagesize);
        $sql = DB::buildlimit("SELECT * FROM " . DB::table('questionset'), $pagesize, $pagestart);
        $query = DB::query($sql);
        while ($row = DB::fetch_array($query)) {
            if ($row['type'] == 0) {
                $adminhtml->table_td(array(
                    array('<input class="checkbox" type="checkbox" name="delete[]" value="' . $row['id'] . '">', TRUE, 'align="center"'),
                    array('<input type="text" class="input" size="50" name="question[' . $row['id'] . ']" value="' . htmlcharsencode($row['question']) . '" />', TRUE),
                    array('<input type="text" class="input" size="35" name="answer[' . $row['id'] . ']" value="' . htmlcharsencode($row['answer']) . '" />', TRUE)
                ));
            } else {
                $calculation = 1;
            }
        }

        $adminhtml->table_td(array(
            array('<span><a class="add_icon" href="#" onclick="addrow(this, 0)">' . adminlang('setting_security_addnewquestion') . '</a></span>', TRUE, 'align="center"'),
            array('<label><input class="checkbox" class="checkbox" type="checkbox" name="calculation" value="calculation"' . ($calculation ? ' checked="checked"' : '') . '/> ' . adminlang('setting_security_calculation') . '</label>', TRUE),
            array('setting_security_calculation_comments', FALSE, '', '', 'tips')
        ));
        if ($pagecount > 1) {
            $showpage = '<var class="morePage">' . showpage($pagenow, $pagecount, $pagesize, $totalrec, ADMIN_SCRIPT . "?m=setting&action=security&anchor=questionset") . '</var>';
            $adminhtml->table_td(array(
                array($showpage, TRUE, 'colspan="3" align="right" id="pagecode"')
                    ), NULL, FALSE, NULL, NULL, FALSE);
        }
        echo '</tbody>';
    } else {
        $adminhtml->form('m=setting&action=save');
        $adminhtml->table_header('menu_setting_basic', 3);
        $adminhtml->table_setting('setting_basic_webname', 'mysetting[webname]', $setting['webname'], 'text');
        $adminhtml->table_setting('setting_basic_website', 'mysetting[website]', $setting['website'], 'text');
        $adminhtml->table_setting('setting_basic_defaultindex', 'mysetting[defaultindex]', $setting['defaultindex'], 'text');
        $adminhtml->table_setting('setting_basic_adminmail', 'mysetting[adminmail]', $setting['adminmail'], 'text');
        $adminhtml->table_setting('setting_basic_onlinehold', 'mysetting[onlinehold]', intval($setting['onlinehold']), 'text');
        $adminhtml->table_setting('setting_basic_onlinetime', 'mysetting[onlinetime]', intval($setting['onlinetime']), 'text');
        $adminhtml->table_setting('setting_basic_timeoffset', 'mysetting[timeoffset]', $setting['timeoffset'], 'select');
        $adminhtml->table_setting('setting_basic_timeformat', 'mysetting[timeformat]', $setting['timeformat'] == 'H:i' ? 24 : 12, 'select');
        $adminhtml->table_setting('setting_basic_dateformat', 'mysetting[dateformat]', $setting['dateformat'], 'text');
        $adminhtml->table_setting('setting_basic_imagemaxwidth', 'mysetting[imagemaxwidth]', intval($setting['imagemaxwidth']), 'text');
        $adminhtml->table_setting('setting_basic_htmlstatus', 'mysetting[htmlstatus]', intval($setting['htmlstatus']), 'radios');
        $adminhtml->table_setting('setting_basic_absoluteurl', 'mysetting[absoluteurl]', intval($setting['absoluteurl']), 'radio');
        $adminhtml->table_setting('setting_basic_uricheck', 'mysetting[uricheck]', intval($setting['uricheck']), 'radio');
        $adminhtml->table_setting('setting_basic_hotminimum', 'mysetting[hotminimum]', intval($setting['hotminimum']), 'text');
        $adminhtml->table_setting('setting_basic_latestdays', 'mysetting[latestdays]', intval($setting['latestdays']), 'text');
        $adminhtml->table_setting('setting_basic_pagesize', 'mysetting[pagesize]', intval($setting['pagesize']), 'text');
        $adminhtml->table_setting('setting_basic_pagenum', 'mysetting[pagenum]', intval($setting['pagenum']), 'text');
        $adminhtml->table_setting('setting_basic_pagestats', 'mysetting[pagestats]', intval($setting['pagestats']), 'radio');
        $adminhtml->table_setting('setting_basic_posted', 'mysetting[posted]', intval($setting['posted']), 'radios');
        $adminhtml->table_setting('setting_basic_summarys', 'mysetting[summarys]', intval($setting['summarys']), 'text');
        $adminhtml->table_setting('setting_basic_statclosed', 'mysetting[statclosed]', intval($setting['statclosed']), 'radios');
        $adminhtml->table_setting('setting_basic_threadlog', 'mysetting[threadlog]', intval($setting['threadlog']), 'radio');
        $adminhtml->table_setting('setting_basic_copyright', 'mysetting[copyright]', $setting['copyright'], 'textarea');
        $adminhtml->table_setting('setting_basic_statcode', 'mysetting[statcode]', $setting['statcode'], 'textarea');
        $adminhtml->table_setting('setting_basic_icp', 'mysetting[icp]', $setting['icp'], 'text');
        $adminhtml->table_setting('setting_basic_title', 'mysetting[title]', trim($setting['title']), 'text');
        $adminhtml->table_setting('setting_basic_keyword', 'mysetting[keyword]', trim($setting['keyword']), 'textarea');
        $adminhtml->table_setting('setting_basic_description', 'mysetting[description]', trim($setting['description']), 'textarea');
        $adminhtml->table_setting('setting_basic_siteclosed', 'mysetting[siteclosed]', intval($setting['siteclosed']), 'radio');
        $adminhtml->table_setting('setting_basic_closedreason', 'mysetting[closedreason]', $setting['closedreason'], 'textarea');
        $adminhtml->table_setting('setting_basic_colorvalue', 'mysetting[colorvalue]', $setting['colorvalue'], 'text');
    }
    if(!$tableformisend) {
	    $adminhtml->table_setting('submit', 'btnsubmit', '', 'submit');
	    $adminhtml->table_end('</form>');
    }
    
    admin_footer();
}

function save_setting_basic($setting) {
    $mysetting = phpcom::$G['gp_mysetting'];
    $upcached = FALSE;
    $savesetting = array();
    $url = trim($mysetting['website'], ' /\\');
    if(empty($url)){
    	$url = "http://" . $_SERVER['SERVER_NAME'];
    }else{
	    if(!parse_url($url, PHP_URL_SCHEME)){
	    	$url = "http://$url";
	    }
    }
    $mysetting['instdir'] = substr(phpcom::$G['PHP_SELF'], 0, strrpos(phpcom::$G['PHP_SELF'], '/') + 1);
    $mysetting['version'] = PHPCOM_VERSION;
    $mysetting['website'] = $url;
    $mysetting['colorvalue'] = trim($mysetting['colorvalue'], ', ');
    $mysetting['timeformat'] = $mysetting['timeformat'] == 24 ? 'H:i' : 'h:i A';
    $mysetting['hotminimum'] = intval($mysetting['hotminimum']) > 0 ? intval($mysetting['hotminimum']) : 100;
    $mysetting['latestdays'] = intval($mysetting['latestdays']) > 0 ? intval($mysetting['latestdays']) : 7;
    foreach ($mysetting as $key => $value) {
        if ($setting[$key] != $value) {
            $$key = $value;
            $upcached = TRUE;
            $savesetting[] = "('$key', '$value', 'string')";
        }
    }
    
    if ($savesetting) {
        DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES " . implode(',', $savesetting));
    }
    if ($upcached) {
        phpcom_cache::updater('setting');
    }
    admin_succeed('setting_succeed', 'm=setting');
}

function save_setting_register($setting) {
    $mysetting = phpcom::$G['gp_mysetting'];
    $upcached = FALSE;
    $settingvalue = '';
    if ($mysetting['register']) {
        $key = 'register';
        $value = serialize($mysetting['register']);
        $settingvalue = "('$key', '$value', 'array')";
        $upcached = TRUE;
    }
    if ($settingvalue) {
        DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES $settingvalue");
    }

    if ($upcached) {
        phpcom_cache::updater('setting');
    }
    admin_succeed('setting_succeed', 'm=setting&action=register');
}

function save_setting_mail($setting) {
    $mysetting = phpcom::$G['gp_mysetting'];
    $upcached = FALSE;
    $settingvalue = '';
    if (!empty($mysetting['mail'])) {
        $password = $mysetting['mail']['password'];
        if ($password && strpos($password, '********') && strlen($password) == 10) {
            $mysetting['mail']['password'] = $setting['mail']['password'];
        }
        $key = 'mail';
        $value = serialize($mysetting['mail']);
        $settingvalue = "('$key', '$value', 'array')";
        $upcached = TRUE;
    }
    if ($settingvalue) {
        DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES $settingvalue");
    }

    if ($upcached) {
        phpcom_cache::updater('setting');
    }
    admin_succeed('setting_succeed', 'm=setting&action=mail');
}

function save_setting_qrcode($setting) {
	$mysetting = phpcom::$G['gp_mysetting'];
	$qrcodeurl = trim($mysetting['qrcodeurl'], "/ '\r\n\t\\");
	$settingvalue = "('qrcodeurl', '$qrcodeurl', 'string')";
	if (!empty($mysetting['qrcode'])) {
		$mysetting['qrcode']['level'] = intval($mysetting['qrcode']['level']);
		$mysetting['qrcode']['size'] = intval($mysetting['qrcode']['size']);
		$mysetting['qrcode']['margin'] = intval($mysetting['qrcode']['margin']);
		$mysetting['qrcode']['text'] = trim($mysetting['qrcode']['text']);
		if(empty($mysetting['qrcode']['background'])) $mysetting['qrcode']['background'] = '#FFFFFF';
		if(empty($mysetting['qrcode']['foreground'])) $mysetting['qrcode']['foreground'] = '#000000';
		$mysetting['qrcode']['background'] = hexrgbcolor($mysetting['qrcode']['background'], '#FFFFFF');
		$mysetting['qrcode']['foreground'] = hexrgbcolor($mysetting['qrcode']['foreground'], '#000000');
		$value = serialize($mysetting['qrcode']);
		$settingvalue .= ",('qrcode', '$value', 'array')";
	}
	DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES $settingvalue");
	phpcom_cache::updater('setting');
	if(!empty(phpcom::$G['gp_makefile'])){
		$text = trim($mysetting['qrcode']['text']);
		if(empty($text)) $text = trim(phpcom::$setting['website']);
		$file = realpath('./data/cache') . '/qrcode.png';
		QRcode::png($text, $file, $mysetting['qrcode']['level'], $mysetting['qrcode']['size'], $mysetting['qrcode']['margin']);
	}
	admin_succeed('setting_succeed', 'm=setting&action=qrcode');
}

function save_setting_mobile($setting) {
	$mysetting = phpcom::$G['gp_mysetting'];
	if (!empty($mysetting['mobile'])) {
		$mysetting['mobile']['domain'] = trim($mysetting['mobile']['domain'], "/ '\r\n\t\\");
		$mysetting['mobile']['logo'] = trim($mysetting['mobile']['logo'], "/ '\r\n\t\\");
		$mysetting['mobile']['chanid'] = intval($mysetting['mobile']['chanid']);
		$mysetting['mobile']['pagesize'] = intval($mysetting['mobile']['pagesize']);
		$mysetting['mobile']['expires'] = intval($mysetting['mobile']['expires']);
		$mysetting['mobile']['from'] = trim($mysetting['mobile']['from']);
		if(empty($mysetting['mobile']['charset'])) $mysetting['mobile']['charset'] = 'utf-8';
		if(empty($mysetting['mobile']['thumb'])) $mysetting['mobile']['thumb'] = 0;
		if(empty($mysetting['mobile']['thumbwidth'])) $mysetting['mobile']['thumbwidth'] = 0;
		$mysetting['mobile']['thumb'] = intval($mysetting['mobile']['thumb']);
		$mysetting['mobile']['thumbwidth'] = intval($mysetting['mobile']['thumbwidth']);
		$value = serialize($mysetting['mobile']);
		$settingvalue = "('mobile', '$value', 'array')";
		DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES $settingvalue");
		phpcom_cache::updater('setting');
	}
	admin_succeed('setting_succeed', 'm=setting&action=mobile');
}

function save_setting_app($setting) {
	$mysetting = phpcom::$G['gp_mysetting'];
	if (!empty($mysetting['app'])) {
		$mysetting['app']['status'] = intval($mysetting['app']['status']);
		$mysetting['app']['domain'] = trim($mysetting['app']['domain'], "/ '\r\n\t\\");
		$value = serialize($mysetting['app']);
		$settingvalue = "('app', '$value', 'array')";
		DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES $settingvalue");
	}
	admin_succeed('setting_succeed', 'm=setting&action=app');
}

function save_setting_watermark($setting) {
    $mysetting = phpcom::$G['gp_mysetting'];
    $upcached = FALSE;
    $settingvalue = '';
    if ($mysetting['watermark']) {
    	if(empty($mysetting['watermark']['status'])) $mysetting['watermark']['status'] = 0;
    	if(empty($mysetting['watermark']['gravity'])) $mysetting['watermark']['gravity'] = 0;
    	if(empty($mysetting['watermark']['type'])) $mysetting['watermark']['type'] = 0;
        $key = 'watermark';
        $value = serialize($mysetting['watermark']);
        $settingvalue = "('$key', '$value', 'array')";
        $upcached = TRUE;
    }
    if ($settingvalue) {
        DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES $settingvalue");
    }

    if ($upcached) {
        phpcom_cache::updater('setting');
    }
    admin_succeed('setting_succeed', 'm=setting&action=imgwmk');
}

function save_setting_attach($setting) {
    $mysetting = phpcom::$G['gp_mysetting'];
    $upcached = FALSE;
    $settingvalue = array();
    if (intval($mysetting['attachmaxsize'])) {
        $mysetting['attachmaxsize'] = intval($mysetting['attachmaxsize']) * 1024;
    } else {
        $mysetting['attachmaxsize'] = 0;
    }
    if ($mysetting['allowattachext']) {
        $mysetting['allowattachext'] = trim(str_replace(array(';', '|', ' ', "\t"), array(',', ',', '', ''), $mysetting['allowattachext']), "\r\n,");
        $mysetting['allowattachext'] = strtolower($mysetting['allowattachext']);
    }
    if (count($mysetting['uploadmode']) == 1) {
        $mysetting['uploadmode'] = $mysetting['uploadmode'][0];
    } else {
        $mysetting['uploadmode'] = count($mysetting['uploadmode']);
    }
    if ($mysetting['attachdir']) {
        $mysetting['attachdir'] = rtrim(trim($mysetting['attachdir']), '/\|');
    }
    if ($mysetting['attachurl']) {
        $mysetting['attachurl'] = rtrim(trim($mysetting['attachurl']), '/\|');
    }
    if(empty($mysetting['attachsubdir'])){
    	$mysetting['attachsubdir'] = 'Y/md';
    }else{
    	$attachsubdir = str_replace(array('\\', '//'), '/', $mysetting['attachsubdir']);
    	$attachsubdir = preg_replace('/[^0-9a-zA-Z_\-\/]+/','',$attachsubdir);
    	$mysetting['attachsubdir'] = trim($attachsubdir, '/\ \t\r\n');
    	if(empty($mysetting['attachsubdir'])) $mysetting['attachsubdir'] = 'Y/md';
    }
    if ($mysetting['imageimpath']) {
        $mysetting['imageimpath'] = rtrim(trim($mysetting['imageimpath']), '/\|');
    }
    foreach ($mysetting as $key => $value) {
        if ($setting[$key] != $value) {
            $$key = $value;
            $upcached = TRUE;
            $settingvalue[] = "('$key', '$value', 'string')";
        }
    }

    if ($settingvalue) {
        DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES " . implode(',', $settingvalue));
    }
    if ($upcached) {
        phpcom_cache::updater('setting');
    }
    admin_succeed('setting_succeed', 'm=setting&action=attach');
}

function save_setting_ftp($setting) {
    $mysetting = phpcom::$G['gp_mysetting'];
    $upcached = FALSE;
    $mysetting['ftp']['ftpssl'] = $mysetting['ftp']['ssl'];
    $password = $mysetting['ftp']['password'];
    if ($password && strpos($password, '********')) {
        $mysetting['ftp']['password'] = $setting['ftp']['password'];
    }
    if ($mysetting['ftp']['minsize']) {
        $mysetting['ftp']['minsize'] *= 1024;
    } else {
        $mysetting['ftp']['minsize'] = 0;
    }
    if ($mysetting['ftp']['attachdir']) {
        $mysetting['ftp']['attachdir'] = rtrim(trim($mysetting['ftp']['attachdir']), '/\|');
    }
    if ($mysetting['ftp']['attachurl']) {
        $mysetting['ftp']['attachurl'] = rtrim(trim($mysetting['ftp']['attachurl']), '/\|');
    }
    if ($mysetting['ftp']['allowext']) {
        $mysetting['ftp']['allowext'] = trim(str_replace(array(';', '|', ' ', "\t"), array(',', ',', '', ''), $mysetting['ftp']['allowext']), "\r\n,");
        $mysetting['ftp']['allowext'] = strtolower($mysetting['ftp']['allowext']);
    }
    if ($mysetting['ftp']['disallowext']) {
        $mysetting['ftp']['disallowext'] = trim(str_replace(array(';', '|', ' ', "\t"), array(',', ',', '', ''), $mysetting['ftp']['disallowext']), "\r\n,");
        $mysetting['ftp']['disallowext'] = strtolower($mysetting['ftp']['disallowext']);
    }
    $settingvalue = '';
    if ($mysetting['ftp']) {
        $key = 'ftp';
        $value = serialize($mysetting['ftp']);
        $settingvalue = "('$key', '$value', 'array')";
        $upcached = TRUE;
    }
    if ($settingvalue) {
        DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES $settingvalue");
    }

    if ($upcached) {
        phpcom_cache::updater('setting');
    }
    admin_succeed('setting_succeed', 'm=setting&action=remote');
}

function save_setting_search($setting){
	$mysetting = phpcom::$G['gp_mysetting'];
	$value = serialize($mysetting['search']);
	$settingvalue = "('search', '$value', 'array')";
	DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES $settingvalue");
	phpcom_cache::updater('setting');
	admin_succeed('setting_succeed', 'm=setting&action=search');
}

function save_setting_searchword($setting) {
	$delete = isset(phpcom::$G['gp_delete']) ? phpcom::$G['gp_delete'] : '';
	$searchwords = isset(phpcom::$G['gp_searchword']) ? striptags(phpcom::$G['gp_searchword']) : '';
	if ($delete) {
		$deleteids = implodeids($delete);
		DB::query("DELETE FROM " . DB::table('searchword') . " WHERE id IN ($deleteids)");
		foreach ($delete as $value) {
			unset($searchwords[$value]);
		}
		unset($delete);
	}
	
	if(isset($searchwords) && $searchwords) {
		foreach($searchwords as $id => $searchvalue){
			if($id && $searchvalue['word']){
				$data = array();
				$data['word'] = $searchvalue['word'];
				$data['tn'] = $searchvalue['tn'];
				$data['url'] = $searchvalue['url'];
				$data['target'] = intval($searchvalue['target']);
				$data['sortord'] = intval($searchvalue['sortord']);
				$highlights = $searchvalue['highlight'];
				$data['highlight'] = intval($highlights['font'] . $highlights['color']);
				DB::update('searchword', $data, "id='$id'");
			}
		}
	}
	$searchwordnew = striptags(phpcom::$G['gp_searchwordnew']);
	if(!empty($searchwordnew['word'])) {
		$data = array();
		$data['word'] = $searchwordnew['word'];
		$data['tn'] = $searchwordnew['tn'];
		$data['url'] = $searchwordnew['url'];
		$data['target'] = intval($searchwordnew['target']);
		$data['sortord'] = intval($searchwordnew['sortord']);
		$data['dateline'] = TIMESTAMP;
		$highlights = $searchwordnew['highlight'];
		$data['highlight'] = intval($highlights['font'] . $highlights['color']);
		DB::insert('searchword', $data);
	}
	phpcom_cache::updater('searchword');
	admin_succeed('setting_succeed', 'm=setting&action=searchword');
}

function save_setting_security($setting) {
    $mysetting = phpcom::$G['gp_mysetting'];
    $upcached = FALSE;
    $savesetting = array();
    if ($mysetting['captcha']) {
        $key = 'captcha';
        $mysetting['captcha']['width'] = intval($mysetting['captcha']['width']);
        $mysetting['captcha']['height'] = intval($mysetting['captcha']['height']);
        $value = serialize($mysetting['captcha']);
        $savesetting[] = "('$key', '$value', 'array')";
        $upcached = TRUE;
    }

    $key = 'captchastatus';
    $captchastatus = array();
    for ($index = 0; $index < 8; $index++) {
    	if(isset($mysetting[$key][$index])){
        	$captchastatus[$index] = intval($mysetting[$key][$index]);
    	}else{
    		$captchastatus[$index] = 0;
    	}
    }
    $value = serialize($captchastatus);
    $savesetting[] = "('$key', '$value', 'array')";
    $upcached = TRUE;

    if ($mysetting['formset']) {
        $key = 'formset';
        if (!preg_match('/^[A-z]\w+?$/', $mysetting['formset']['username'])) {
            $mysetting['formset']['username'] = 'username';
        }
        if (!preg_match('/^[A-z]\w+?$/', $mysetting['formset']['password'])) {
            $mysetting['formset']['password'] = 'password';
        }
        if (!preg_match('/^[A-z]\w+?$/', $mysetting['formset']['password2'])) {
            $mysetting['formset']['password2'] = 'password2';
        }
        if (!preg_match('/^[A-z]\w+?$/', $mysetting['formset']['email'])) {
            $mysetting['formset']['email'] = 'email';
        }
        $value = serialize($mysetting['formset']);
        $savesetting[] = "('$key', '$value', 'array')";
        $upcached = TRUE;
    }
    
    $questionstatus = array();
    for ($index = 0; $index < 8; $index++) {
    	if(isset($mysetting['questionstatus'][$index])){
    		$questionstatus[$index] = intval($mysetting['questionstatus'][$index]);
    	}else{
    		$questionstatus[$index] = 0;
    	}
    }
    $value = serialize($questionstatus);
    $savesetting[] = "('questionstatus', '$value', 'array')";
    $upcached = TRUE;

    $restriction = array(
        'repeatemail' => intval($mysetting['repeatemail']),
        'allowsemail' => trim($mysetting['allowsemail']),
        'bannedemail' => trim($mysetting['bannedemail']),
        'banusername' => trim($mysetting['banusername']),
        'allowsregip' => trim($mysetting['allowsregip']),
        'allowipaccess' => trim($mysetting['allowipaccess']),
        'adminipaccess' => trim($mysetting['adminipaccess'])
    );
    foreach ($restriction as $k => $v) {
        if ($setting[$k] != $v) {
            $$k = $v;
            $savesetting[] = "('$k', '$v', 'string')";
        }
    }

    if ($savesetting) {
        DB::query("REPLACE INTO " . DB::table('setting') . " (`skey`, `svalue`, `stype`) VALUES " . implode(',', $savesetting));
    }
    if ($upcached) {
        phpcom_cache::updater('setting');
    }
    if (isset(phpcom::$G['gp_delete']) && is_array(phpcom::$G['gp_delete'])) {
        DB::delete('questionset', 'id IN(' . implodeids(phpcom::$G['gp_delete']) . ')');
    }
    if (isset(phpcom::$G['gp_question']) && is_array(phpcom::$G['gp_question'])) {
        foreach (phpcom::$G['gp_question'] as $key => $qtn) {
            $qtn = striptags($qtn);
            $ans = strcut(striptags(phpcom::$G['gp_answer'][$key]), 50);
            DB::update('questionset', array('question' => $qtn, 'answer' => $ans), "id='$key'");
        }
    }
    if (!empty(phpcom::$G['gp_calculation'])) {
        if (!DB::exists('questionset', "type='1'")) {
            DB::insert('questionset', array('type' => '1', 'question' => 'calculation'));
        }
    } else {
        DB::delete('questionset', "type='1'");
    }
    if (isset(phpcom::$G['gp_newquestion']) && is_array(phpcom::$G['gp_newquestion'])) {
        foreach (phpcom::$G['gp_newquestion'] as $key => $qtn) {
            $qtn = striptags($qtn);
            $ans = strcut(striptags(phpcom::$G['gp_newanswer'][$key]), 50);
            if ($qtn !== '' && $ans !== '') {
                DB::insert('questionset', array('type' => 0, 'question' => $qtn, 'answer' => $ans));
            }
        }
    }
    phpcom_cache::updater('questionset');
    admin_succeed('setting_succeed', 'm=setting&action=security&anchor=' . phpcom::$G['gp_anchor']);
}

?>