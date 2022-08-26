<?php
/**
 * Copyright (c) 2010-2012 PHPcom - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of PHPcom.
 * This File   : lang_video.php  2012-8-15
 */
!defined('IN_PHPCOM') && exit('Access denied');

$lang = array(
		'video_admin' => '{name}管理',
		'video_view' => '浏览{name}',
		'video_tips' => '<ol class="tipslis"><li>添加{name}前必需先添加相关栏目，点击上面菜单中的<span class="c2">栏目管理</span>新建栏目</li>
		<li>点击下面的栏目名添加新的{name}，由于增删或移动{name}，统计可能会出现偏差，您需要<span class="c1">更新栏目统计</span>进行修复</li></ol>',
		'video_not_found_category' => '<p>您现在还没有创建栏目不能添加{name}，<a href="?m=category&action=add&nav=video&chanid={chanid}">点这里创建新栏目</a></p>',
		'video_category_all' => '<span class="btntxt"><a href="?m=category&nav=video&chanid={chanid}">{name}栏目导航</a></span> <span class="btntxt"><a href="?m=video&action=list&chanid={chanid}">所有{name}列表</a></span> 
		<span class="btntxt"><a href="?m=channel&action=edit&chanid={chanid}">频道设置</a></span> <span class="btntxt"><a href="?m=video&action=upcount&chanid={chanid}">更新栏目统计</a></span>',
		'video_browse_list' => '<span class="btntxt"><a href="?m=video&action=list&chanid={chanid}">浏览所有列表</a></span>',
		'video_add' => '添加{name}',
		'video_edit' => '编辑{name}',
		'video_audit' => '审核{name}',
		'video_title' => '{name}标题',
		'video_title_comments' => '标题',
		'video_category' => '所属栏目',
		'video_category_comments' => '所属栏目',
		'video_topical' => '所属专题',
		'video_topical_comments' => '所属专题',
		'video_subtitle' => '副标题',
		'video_url' => '外部链接',
		'video_url_comments' => '外部链接 URL，不设置留空',
		'video_demourl' => '演示地址',
		'video_demourl_comments' => '演示地址以 http:// 开头，不设置留空',
		'video_codename' => '英文名',
		'video_director' => '影片导演',
		'video_starring' => '领衔主演',
		'video_starring_comments' => '可使用半角逗号,空格或 / 分隔',
		'video_years' => '年 代',
		'video_release' => '上映日期',
		'video_country' => '国  家',
		'video_language' => '语 言',
		'video_dialogue' => '对白',
		'video_version' => '{name}版本',
		'video_quality' => '{name}质量',
		'video_mins' => '时长',
		'video_mins_comments' => '设置影片播放时间，单位分钟',
		'video_mins_unit' => '分钟',
		'video_istop' => '置顶',
		'video_isbast' => '推荐',
		'video_content' => '剧情介绍',
		'video_summary' => '<span onclick="showDescrLength(\'summary_contents\')">{name}摘要</span>',
		'video_keyword' => '关键字',
		'video_author' => '{name}作者',
		'video_country_dialogue' => '地区对白',
		'video_country_language' => '地区语言',
		'video_select_years' => '选择年代',
		'video_select_country' => '选择{name}出产地',
		'video_select_dialogue' => '选择{name}对白',
		'video_select_language' => '选择{name}语言',
		'video_select_version' => '选择{name}版本',
		'video_select_quality' => '选择{name}质量',
		'video_play_address' => '播放地址',
		'video_play_address_comments' => '多个播放地址请用回车换行隔开，自定义分集名请用“$$”隔开，留空则删除当前播放地址',
		'video_play_address_add' => '增加地址',
		'video_play_address_add_comments' => '<strong onclick="addPlayAddress()" class="add_icon c1 f14 cp">增加一组播放地址</strong> <strong>播放说明：</strong>',
		'video_tags' => '{name}Tags',
		'video_editor' => '责任编辑',
		'video_htmlname' => 'HTML文件',
		'video_htmlname_comments' => '用于生成HTML的文件名，不设置请留空。',
		'video_tname' => '模板名',
		'video_tname_comments' => '手工建立相对应的模板；如：video/threadview_模板名.htm',
		'video_player' => '视频播放器设置',
		'video_player_tips' => '<ol class="tipslis"><li>为了方便管理，英文标识名必需唯一且英文或数字和下划线的组合</li>
		<li>播放器说明可以根据自己的喜好来设置，不要超过 255 个字符</li><li>播放器 URL 为第三方视频网站的播放器 URL</li></ol>',
		'video_player_id' => 'ID',
		'video_player_subject' => '播放器名称',
		'video_player_name' => '英文标识名',
		'video_player_caption' => '播放器说明',
		'video_player_url' => 'URL',
		'video_player_status' => '状态',
		'video_player_type' => '播放类型',
);