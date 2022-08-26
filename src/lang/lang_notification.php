<?php

/**
 * Copyright (c) 2010-2011  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : lang_notification.php    2012-1-11
 */
$lang = array(
    'type_system' => '系统',
	'type_thread' => '主题',
	'type_task' => '任务',
    'type_credit' => '积分',
    'mail_to_user' => '有新的通知',
    'add_funds_complete' => '您提交的积分充值请求已完成，相应数额的积分已存入您的积分账户
<p class="summary">订单号：<span>{orderid}</span></p><p class="summary">支出：<span>人民币 {price} 元</span></p><p class="summary">收入：<span>{details}</span></p>',
    'system_notice' => '{subject}<p class="summary">{message}</p>',
	'system_adv_expiration' => '您站点的以下广告将于 {day} 天后到期，请及时处理：<br />{advs}',
    'member_audit_invalidate' => '您的账号未能通过管理员的审核，请<a href="member.php?action=profile">重新提交注册信息</a>。<br />管理员留言: <b>{remark}</b>',
	'member_audit_validate' => '您的账号已通过审核。<br />管理员留言: <b>{remark}</b>',
	'member_audit_invalidate_no_remark' => '您的账号未能通过管理员的审核，请<a href="member.php?action=profile">重新提交注册信息</a>。',
	'member_audit_validate_no_remark' => '您的账号已通过审核。',
    'user_usergroup' => '您的用户组升级为 {usergroup} &nbsp; <a href="member.php?action=usergroup" target="_blank">看看我能做什么 &rsaquo;</a>',
);
?>
