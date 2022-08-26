<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : lang_photo.php  2012-8-15
 */
!defined('IN_PHPCOM') && exit('Access denied');
$lang = array(
		'photo_admin' => '{name}管理',
		'photo_view' => '浏览{name}',
		'photo_tips' => '<ol class="tipslis"><li>添加{name}前必需先添加相关栏目，点击上面菜单中的<span class="c2">栏目管理</span>新建栏目</li>
		<li>点击下面的栏目名添加新的{name}，由于增删或移动{name}，统计可能会出现偏差，您需要<span class="c1">更新栏目统计</span>进行修复</li></ol>',
		'photo_not_found_category' => '<p>您现在还没有创建栏目不能添加{name}，<a href="?m=category&action=add&nav=photo&chanid={chanid}">点这里创建新栏目</a></p>',
		'photo_category_all' => '<span class="btntxt"><a href="?m=category&nav=photo&chanid={chanid}">{name}栏目导航</a></span> <span class="btntxt"><a href="?m=photo&action=list&chanid={chanid}">所有{name}列表</a></span> 
		<span class="btntxt"><a href="?m=channel&action=edit&chanid={chanid}">频道设置</a></span> <span class="btntxt"><a href="?m=photo&action=upcount&chanid={chanid}">更新栏目统计</a></span>',
		'photo_browse_list' => '<span class="btntxt"><a href="?m=photo&action=list&chanid={chanid}">浏览所有列表</a></span>',
		'photo_add' => '添加{name}',
		'photo_edit' => '编辑{name}',
		'photo_audit' => '审核{name}',
		'photo_title' => '{name}标题',
		'photo_title_comments' => '标题',
		'photo_category' => '所属栏目',
		'photo_category_comments' => '所属栏目',
		'photo_topical' => '所属专题',
		'photo_topical_comments' => '所属专题',
		'photo_subtitle' => '副标题',
		'photo_url' => '外部链接',
		'photo_url_comments' => '外部链接 URL，不设置留空',
		'photo_demourl' => '演示地址',
		'photo_demourl_comments' => '演示地址以 http:// 开头，不设置留空',
		'photo_codename' => '英文名',
		'photo_istop' => '置顶',
		'photo_isbast' => '推荐',
		'photo_content' => '{name}内容',
		'photo_summary' => '<span onclick="showDescrLength(\'summary_contents\')">内容摘要</span>',
		'photo_keyword' => '关键字',
		'photo_person' => '相关人物',
		'photo_person_comments' => '多个人物可使用半角逗号,空格或 / 分隔',
		'photo_author' => '{name}作者',
		'photo_source' => '引用来源',
		'photo_select_author' => '选择{name}作者',
		'photo_select_source' => '选择{name}来源',
		'photo_author_source' => '{name}作者/来源',
		'photo_trackback' => '引用网址',
		'photo_trackback_comments' => ' <span class="red">引用URL</span>',
		'photo_tags' => '{name}Tags',
		'photo_editor' => '责任编辑',
		'photo_htmlname' => 'HTML文件',
		'photo_htmlname_comments' => '用于生成HTML的文件名，不用后缀，不设置请留空。',
		'photo_tname' => '模板名',
		'photo_tname_comments' => '手工建立相对应的模板；如：photo/threadview_模板名.htm',
);
