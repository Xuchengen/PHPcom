<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : lang_main.php    2011-4-3 1:39:32
 */
$lang = array(
    'index_info_title' => '系统信息',
    'index_info_license' => '本程序由 <font color="red">PHPcom</font> 授权给 <font color="green">{sitename}</font> 使用，当前使用版本为 <font color="blue">PHPcom CMS {version} - {charset}</font>',
    'index_info_server' => '服务器信息：{servername} (IP：{serverip})',
    'index_info_database' => '当前的数据库：{currentdb} {dbversion} (大小：<span id="showdbsize"><span class="loading-16">&nbsp;</span></span>)<script>ajaxget("{ADMINSCRIPT}?m=ajax&action=dbsize","showdbsize")</script>',
	'index_info_dbsize' => '(大小：{dbsize})',
    'index_info_serversoft' => '服务器软件：{serversoft}',
    'index_info_phpversion' => '当前PHP版本：PHP {phpversion}',
    'index_info_serveros' => '服务器系统：{serveros}',
    'index_info_physicalpath' => '站点物理路径：{physicalpath}',
    'index_info_maxpostsize' => 'POST 大小：{maxpostsize}',
    'index_info_maxupsize' => '上传文件大小：{maxupsize}',
    'index_info_servertime' => '服务器时间：{servertime}',
    'index_info_maxexectime' => 'PHP运行时间：{maxexectime}',
    'index_info_tips' => '<font color="red">数据定期备份：</font>请注意定期做好数据备份，数据的定期备份可最大限度的保障您网站数据的安全',
    'index_info_sponsors' => '<b>相关链接：</b> <a href="http://www.cnxinyun.com" target="_blank">官方主站</a>&nbsp; &nbsp;<a href="http://www.phpcom.cn" target="_blank">PHPcom 产品</a>&nbsp; &nbsp;
        <a href="http://www.55idc.com" target="_blank">五五互联</a>&nbsp; &nbsp;<a href="http://www.e399.com" target="_blank">软件下载</a>&nbsp; &nbsp;<a href="http://www.cnxinyun.com" target="_blank">新云网络</a>&nbsp; &nbsp;
        <a href="http://www.phpcom.net" target="_blank">官方论坛</a>&nbsp; &nbsp;',
    'index_safety_tips' => '安全提示',
    'index_safety_tips_comments' => '<ol class="tipslis"><li>强烈建议您将 data/config.php 文件属性设置为644（linux/unix）或只读权限（WinNT） </li>
        <li>强烈建议您将 admin.php 文件重新命名，然后修改 data/config.php （[admincp][script]） 定义修改后的文件名</li>
        <li>请注意定期做好数据备份，数据的定期备份可最大限度的保障您网站数据的安全</li></ol>',
    'index_phpcom_info' => 'PHPcom 信息',
    'index_phpcom_copyright' => '<strong>版权所有</strong>',
    'index_phpcom_copyright_comments' => '<a href="http://www.cnxinyun.com" target="_blank">武汉新云网络</a>',
    'index_phpcom_licence' => '<strong>许可协议</strong>',
    'index_phpcom_licence_comments' => '<ol class="tipslis"><li>本软件为共享软件，未经商业授权，不得将本软件用于商业用途(企业网站或以盈利为目的经营性网站)，否则我们将保留追究的权力。</li>
        <li>用户自由选择是否使用本软件,在使用中出现任何问题和由此造成的一切损失 PHPcom 官方将不承担任何责任；</li>
        <li>利用 PHPcom 构建网站的任何信息内容以及导致的任何版权纠纷和法律争议及后果，PHPcom 官方不承担任何责任；</li>
        <li>所有用户均可查看 PHPcom 的全部源代码，您可以对本系统进行修改和美化，但必须保留完整的版权信息;</li>
        <li>本软件受中华人民共和国《著作权法》《计算机软件保护条例》等相关法律、法规保护，软件作者保留一切权利。</li></ol>',
    'index_phpcom_contact' => '<strong>联系方式</strong>',
    'index_phpcom_contact_comments' => 'E-mail：cnxinyun@163.com&nbsp; &nbsp;QQ：94022511&nbsp; &nbsp;94022589<br/>电话：027-85777659&nbsp; &nbsp;手机：013971626572<br/>
        <strong>说明：</strong><span class="c1">以上联系方式，只用于商业授权咨询，不提供技术支持。</span>',
    'index_phpcom_home' => '<strong>官方主页</strong>',
    'index_phpcom_home_comments' => '<a href="http://www.phpcom.cn" target="_blank">www.phpcom.cn</a>&nbsp; &nbsp;
        <a href="http://www.phpcom.net" target="_blank">www.phpcom.net</a>&nbsp; &nbsp;
        <a href="http://www.cnxinyun.com" target="_blank">www.cnxinyun.com</a>&nbsp; &nbsp;',
    'index_show_admininfo' => '您好，{group} <a href="{ADMINSCRIPT}?m=admingroup" target="mainFrame"><strong style="color:#ff8800">{admin}</strong></a> [<a href="?action=logout">退出</a>]',
    'index_pending_matters' => '<strong>待处理事项：</strong><span id="pending_matters"><span class="loading-16">&nbsp;</span></span><script>ajaxget("{ADMINSCRIPT}?m=ajax&action=matters","pending_matters")</script>',
    'pending_audit_members' => '<a class="act ac2" href="{ADMINSCRIPT}?m=members&action=audit">等待审核的会员数</a>(<em class="c6">{num}</em>) &nbsp; ',
    'recycle_threads' => '<a class="act ac2" href="{ADMINSCRIPT}?m=recycle">回收站中的主题数</a>(<em class="c6">{num}</em>)',
    'admingroup_tips' => '<ol class="tipslis"><li>在这里可以制定多种管理团队职务分配给网站管理团队的各个成员，让他们管理网站的不同事务</li><li>注：“创始人(站长)”拥有所以权限，要增加“创始人”请手工修改“data/config.php”[admincp][founder]</li></ol>',
    'admingroup_username' => '成员用户',
    'admingroup_usergname_comment' => '设置当前管理团队成员的职务',
    'admingroup_dateline' => '日期',
    'admingroup_fullname' => '姓名',
    'admingroup_fullname_comment' => '设置当前用户的真实姓名,不设置留空',
    'admingroup_fullname_input' => ' <strong>姓名(可选)：</strong><input class="input t15" name="fullname" type="text" value="{name}" title="设置当前用户的真实姓名,不设置留空" />',
    'admingroup_usergroup' => '管理组',
    'admingroup_group_perms' => '编辑团队职务权限 - {name}',
    'admingroup_member_perms' => '编辑团队成员 - {name} 权限',
    'admingroup_perm_setting' => '基本权限',
    'admingroup_perm_all' => '<span title="设置成员拥有全部权限(创始人特定权限除外)">拥有全部权限</span>',
    'admingroup_perm_allowpost' => '<span title="设置成员能用 POST 方式提交信息，否则只能浏览后台信息，不能提交信息。">开启 POST 权限</span>',
    'admingroup_group_switch' => '切换团队职务',
);
