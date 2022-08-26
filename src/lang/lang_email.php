<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : lang_email.php    2012-1-9
 */
$lang = array(
    'hello' => '您好',
    'audit_member_invalidate' => '否决',
	'audit_member_delete' => '删除',
	'audit_member_validate' => '通过',
    'get_password_subject' => '取回密码说明',
    'get_password_message' => '
<p>{username}，这封信是由 {webname} 发送的。</p>
<p>您收到这封邮件，是由于这个邮箱地址在 {webname} 被登记为用户邮箱，
且该用户请求使用 Email 密码重置功能所致。</p>
<p>
----------------------------------------------------------------------<br />
<strong>重要！</strong><br />
----------------------------------------------------------------------</p>
<p>如果您没有提交密码重置的请求或不是 {webname} 的注册用户，请立即忽略并删除这封邮件。
只有在您确认需要重置密码的情况下，才需要继续阅读下面的内容。</p>
<p>
----------------------------------------------------------------------<br />
<strong>密码重置说明</strong><br />
----------------------------------------------------------------------</p>
</p>
您只需在提交请求后的三天内，通过点击下面的链接重置您的密码：<br />
<a href="{siteurl}member.php?action=getpasswd&amp;uid={uid}&amp;key={key}" target="_blank">{siteurl}member.php?action=getpasswd&amp;uid={uid}&amp;key={key}</a>
<br />
(如果上面不是链接形式，请将该地址手工粘贴到浏览器地址栏再访问)</p>
<p>在上面的链接所打开的页面中输入新的密码后提交，您即可使用新的密码登录网站了。您可以在用户中心随时修改您的密码。</p>
<p>本请求提交者的 IP 为 {clientip}</p>
<p>{webname} <a href="{url}" target="_blank">{siteurl}</a></p>
        ',
    'email_verify_subject' => 'Email 地址验证',
    'email_verify_message' => '
<p>{username}，这封信是由 {webname} 发送的。</p>
<p>您收到这封邮件，是由于在 {webname} 进行了新用户注册，或用户修改 Email 使用
了这个邮箱地址。如果您并没有访问过 {webname}，或没有进行上述操作，请忽略这封邮件。</p>
<br />
----------------------------------------------------------------------<br />
<strong>帐号激活说明</strong><br />
----------------------------------------------------------------------<br />
<p>请点击下面的链接激活您的帐号：<br />
<a href="{url}" target="_blank">{url}</a>
<br />
(如果上面不是链接形式，请将该地址手工粘贴到浏览器地址栏再访问)</p>
<p>感谢您的访问，祝您使用愉快！</p>
<p>{webname} <a href="{url}" target="_blank">{siteurl}</a></p>',
    'validation_member_subject' => '用户审核结果通知',
    'validation_member_message' => '
<p>{username} ，这封信是由 {webname} 发送的。</p>
<p>您收到这封邮件，是由于这个邮箱地址在 {webname} 被新用户注册时所使用，
且管理员设置了对新用户需要进行人工审核，本邮件将通知您提交申请的审核结果。</p>
----------------------------------------------------------------------<br />
<strong>注册信息与审核结果</strong><br />
----------------------------------------------------------------------<br />
用户名: {username}<br />
注册时间: {regdate}<br />
提交时间: {submitdate}<br />
注册原因: {message}<br />
<br />
审核结果: {auditresult}<br />
审核时间: {auditdate}<br />
审核管理员: {auditor}<br />
管理员留言: {remark}<br />
<br />
----------------------------------------------------------------------<br />
<strong>审核结果说明</strong><br />
----------------------------------------------------------------------<br />
<p>通过: 您的注册已通过审核，您已成为 {webname} 的正式用户。</p>
<p>否决: 您的注册信息不完整，或未满足我们对新用户的某些要求，您可以
	  根据管理员留言完善您的注册信息，然后再次提交。</p>
<p>删除：您的注册由于与我们的要求偏差较大，或本站的新注册人数已 超过预期，申请已被否决。
	  您的帐号已从数据库中删除，将无法再使用其登录或提交再次审核，请您谅解。</p>
<br />
<br />
<p>{webname} <a href="{url}" target="_blank">{siteurl}</a></p>',
);
?>
