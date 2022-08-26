<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : lang_article.php    2011-5-12 0:42:15
 */
$lang = array(
	'article_admin' => '{name}管理',
	'article_view' => '浏览{name}',
	'article_tips' => '<ol class="tipslis"><li>添加{name}前必需先添加相关栏目，点击上面菜单中的<span class="c2">栏目管理</span>新建栏目</li>
	<li>点击下面的栏目名添加新的{name}，由于增删或移动{name}，统计可能会出现偏差，您需要<span class="c1">更新栏目统计</span>进行修复</li></ol>',
	'article_not_found_category' => '<p>您现在还没有创建栏目不能添加{name}，<a href="?m=category&action=add&nav=article&chanid={chanid}">点这里创建新栏目</a></p>',
	'article_category_all' => '<span class="btntxt"><a href="?m=category&nav=article&chanid={chanid}">{name}栏目导航</a></span>
	<span class="btntxt"><a href="?m=article&action=list&chanid={chanid}">所有{name}列表</a></span> <span class="btntxt"><a href="?m=robots&chanid={chanid}">内容采集</a></span> 
	<span class="btntxt"><a href="?m=channel&action=edit&chanid={chanid}">频道设置</a></span> <span class="btntxt"><a href="?m=article&action=upcount&chanid={chanid}">更新栏目统计</a></span>',
	'article_browse_list' => '<span class="btntxt"><a href="?m=article&action=list&chanid={chanid}">浏览所有列表</a></span>',
	'article_add' => '添加{name}',
	'article_edit' => '编辑{name}',
	'article_audit' => '审核{name}',
	'article_title' => '{name}标题',
	'article_title_comments' => '标题',
	'article_category' => '所属栏目',
	'article_category_comments' => '所属栏目',
	'article_topical' => '所属专题',
	'article_topical_comments' => '所属专题',
	'article_subtitle' => '副标题',
	'article_url' => '外部链接',
	'article_url_comments' => '外部链接 URL，不设置留空',
	'article_demourl' => '演示地址',
	'article_demourl_comments' => '演示地址以 http:// 开头，不设置留空',
	'article_codename' => '英文名',
	'article_istop' => '置顶',
	'article_isbast' => '推荐',
	'article_content' => '{name}内容',
	'article_summary' => '<span onclick="showDescrLength(\'summary_contents\')">内容摘要</span>',
	'article_keyword' => '关键字',
	'article_author' => '{name}作者',
	'article_source' => '引用来源',
	'article_select_author' => '选择{name}作者',
	'article_select_source' => '选择{name}来源',
	'article_author_source' => '{name}作者/来源',
	'article_trackback' => '引用网址',
	'article_trackback_comments' => ' <span class="red">引用URL</span>',
	'article_tags' => '{name}Tags',
	'article_editor' => '责任编辑',
	'article_htmlname' => 'HTML文件',
	'article_htmlname_comments' => '用于生成HTML的文件名，不设置请留空。',
	'article_tname' => '模板名',
	'article_tname_comments' => '手工建立相对应的模板；如：article/threadview_模板名.htm',
);
