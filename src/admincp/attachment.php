<?php
/**
 * Copyright (c) 2010-2013  phpcom.cn - All rights reserved.
 * Our Website : www.phpcom.cn www.phpcom.net www.cnxinyun.com
 * Description : This software is the proprietary information of phpcom.cn.
 * This File   : attachment.php    2013-11-06
 */
!defined('IN_ADMINCP') && exit('Access denied');
phpcom::$G['lang']['admin'] = 'misc';

$chanid = isset(phpcom::$G['gp_chanid']) ? intval(phpcom::$G['gp_chanid']) : 2;
if(!isset(phpcom::$G['channel'][$chanid])){
    admin_message('undefined_action');
}
phpcom::$G['att']['chanid'] = $chanid;
phpcom::$G['att']['tid']    = isset(phpcom::$G['gp_tid']) ? intval(phpcom::$G['gp_tid']) : 0;
phpcom::$G['att']['module'] = phpcom::$G['channel'][$chanid]['modules'];

if($action == '') {
    $action = 'list';
}

$app = new attachMange();
$app->run($action);

class attachMange {

    public function run($action) {
        $this->chanid   = phpcom::$G['att']['chanid'];
        $this->tid      = phpcom::$G['att']['tid'];
        $this->module   = phpcom::$G['att']['module'];

        $action = ucwords(trim($action, "\r\n\0\t\x0B _"));

        $method = '';
        if(method_exists($this, $method = 'action' . $action)) {
            self::$method();
        } else if(method_exists($this, $method = 'ajax' .  $action)) {
            self::$method();
        } else {
            // echo "action:$action, method:$method";exit;
            header("status: 404 Not Found");
            header('HTTP/1.1 404 Not Found');
            exit;
        }
    }

    //------------------------------ Contorller ------------------------------
    public function actionList() {
        $this->thread     = $this->_dbGetThread();
        $this->attList    = $this->_dbGetAttList();

        $this->_viewList();
    }

    public function actionSubmit() {
        if(isset(phpcom::$G['gp_saveAll'])) {
            $this->_dbSaveAll();
        } else if(isset(phpcom::$G['gp_bDelete'])) {
            $this->_dbDelete(phpcom::$G['gp_delete']);
        }
        $this->_redirect("?m=attachment&chanid={$this->chanid}&tid={$this->tid}");

    }

    public function actionAddimg() {
        $posttime   = phpcom::$G['gp_posttime'];
        $uid        = phpcom::$G['gp_uid'];
        $chanid     = $this->chanid;

        $this->_dbPerpetuate($posttime, $uid, $chanid);

        $this->_redirect("?m=attachment&chanid={$this->chanid}&tid={$this->tid}");
    }

    public function ajaxSave() {
        if($this->_dbUpdate()) {
            $this->_ajaxReturn(true);
        } else {
            $this->_ajaxReturn(false);
        }
    }

    public function ajaxDelete() {
        $aid = intval(phpcom::$G['gp_aid']);
        if($this->_dbDelete($aid)) {
            $this->_ajaxReturn(true);
        } else {
            $this->_ajaxReturn(false);
        }
    }

    private function _redirect($url) {
        header("Status: 301 Moved Permanently");
        header("Http/1.1 301 Moved Permanently");
        header("Location: $url");
        exit;
    }

    private function _ajaxReturn($status, $data = '', $msg = '') {
        $rv = array(
            'status' => $status,
            'data' => $data,
            'msg' => $msg,
        );
        header('Expires: -1');
        header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", false);
        header('Pragma: no-cache');
        header('Content-Type:application/json; charset=' . phpcom::$G['charset']);

        exit ($this->_encode_json($rv));
    }

    private function _encode_json($data) {
        return urldecode(json_encode($this->_url_encode($data)));
    }
    private function _url_encode($str) {
        if(is_array($str)) {
            foreach($str as $k => $v) {
                $str[urlencode($k)] = $this->_url_encode($v);
            }
        } else {
            $str = urlencode($str);
        }
        return $str;
    }

    //------------------------------ View ------------------------------

    private function _viewList() {
        $adminhtml = phpcom_adminhtml::instance();
        admin_header('menu_attachment');

        $list = '';

        foreach($this->attList as $att) {
            $aid = $att['attachid'];
            $list .= '<li id="att_' . $aid . '">';
            $list .= '    <div class="image"><img src="' . $att['imgUrl'] . '" id="uploadimage_' . $aid . '" /></div>';
            $list .= '    <input type="checkbox" name="delete[]" class="select" title="' . adminlang('attachment_batch_delete') . '" value="' . $aid . '" />';
            $list .= '    <a onclick="updateAttachWindow(\'image\', ' . $aid . ', ' . $this->chanid . ');return false;" href="javascript:;" class="update" title="' . adminlang('attachment_update') . '"><img src="' . phpcom::$G['siteurl'] . 'misc/images/icons/edit.gif" /></a>';
            $list .= '    <p>';
            $list .= '        <label for="sortord_' . $aid . '">' . adminlang('attachment_sortord') . '</label><input type="text" size="1" name="attachnew[' . $aid . '][sortord]" id="sortord_' . $aid . '" class="sortord" value="' . $att['sortord'] . '"/>';
            $list .= '        <input type="checkbox" name="attachnew[' . $aid . '][preview]" class="preview" id="preview_' . $aid . ($att['preview'] ? '" checked="checked"' : '"') . ' /><label for="preview_' . $aid . ($att['preview'] ? '" class="c2"' : '"') . '>' . adminlang('attachment_preview') . '</label>';
            $list .= '    </p>';
            $list .= '    <p><label for="url_' . $aid . '">' . adminlang('attachment_url') . '</label><input type="text" size="15" name="attachnew[' . $aid . '][url]" id="url_' . $aid . '" value="' . $att['url'] . '" class="url" /></p>';
            $list .= '    <p class="description"><label for="description_' . $aid . ($att['description'] ? '' : '" style="display:block;"') . '"">' . adminlang('attachment_description') . '</label><textarea name="attachnew[' . $aid . '][description]" id="description_' . $aid . '">' . $att['description'] . '</textarea></p>';
            $list .= '    <div class="buttons"><a href="javascript:void(0);" class="btn save">' . adminlang('attachment_btn_save') . '</a><a href="javascript:void(0);" class="btn warning delete">' . adminlang('attachment_btn_delete') . '</a></div>';
            $list .= '</li>';
        }

        echo '<script src="misc/js/post_thread.js" type="text/javascript"></script>' . "\n";
        echo '<form method="post" action="?m=attachment&action=submit&chanid=' . $this->chanid . '&tid=' . $this->tid . '" id="attachForm">' . "\n";
        echo '<table width="100%" cellspacing="2" cellpadding="2">' . "\n";
        echo '    <caption>' . "\n";
        echo '            ' . $this->thread['title'] . "\n";
        echo '    </caption>' . "\n";
        echo '    <tr>' . "\n";
        echo '        <td width="5%" class="tablerow2">&nbsp;</td>' . "\n";
        echo '        <td width="45%" class="tablerow2" colspan="1" align="left">' . "\n";
        echo '            <a href="?m=' . $this->module . '&chanid=' . $this->chanid . '" class="btn" style="display:inline-block;">' . adminlang('attachment_return_manage') . '</a>' . "\n";
        echo '            <a href="?m=' . $this->module . '&action=list&chanid=' . $this->chanid . '&catid=' . $this->thread['catid'] . '" class="btn" style="display:inline-block;">' . adminlang('attachment_return_list') . '</a>' . "\n";
        echo '            <a href="?m=' . $this->module . '&action=edit&chanid=' . $this->chanid . '&tid='. $this->tid . '" class="btn" style="display:inline-block;">' . adminlang('attachment_return_edit') . '</a>' . "\n";
        echo '        </td>' . "\n";
        echo '        <td width="45%" class="tablerow2" colspan="1" align="right" id="action">' . "\n";
        echo '            <input type="button" name="add_image" value="' . adminlang('attachment_add_image') . '" class="att-btn" id="add_image" />' . "\n";
        if($list) {
            echo '            <input type="submit" name="saveAll" value="' . adminlang('attachment_save_all') . '" class="att-btn" />' . "\n";
            echo '            <input type="submit" name="bDelete" value="' . adminlang('attachment_batch_delete') . '" class="att-btn bdelete disabled" disabled="disabled" />' . "\n";
        }
        echo '        </td>' . "\n";
        echo '        <td width="5%" class="tablerow2">&nbsp;</td>' . "\n";
        echo '    </tr>' . "\n";
        if(!$list) {
            echo '<tr><td colspan=3" class="tablerow1">&nbsp;</td></tr>' . "\n";
            echo '<tr><td colspan=3" class="tablerow1" align="center">' . adminlang('attachment_has_no_data') . '</td></tr>' . "\n";
            echo '<tr><td colspan=3" class="tablerow1">&nbsp;</td></tr>' . "\n";
        }
        echo '</table>' . "\n";
        if($list) {
            echo '<ul class="attList">' . "\n";
            echo $list . "\n";
            echo '</ul>' . "\n";
        }
        echo '</form>' . "\n";

        $this->_showUpload();
        $this->_showJS();
        admin_footer();
    }

    private function _showUpload() {
        $chanid             = intval($this->chanid);
        $tid                = intval($this->tid);
        $type               = 'image';
        $uid                = phpcom::$G['uid'];
        $hash               = md5(substr(md5(phpcom::$config['security']['key']), 8) . $uid);
        $depiction          = $type == 'image' ? 'Image Files' : 'All Files';
        $maxszie            = phpcom::$G['group']['maxattachsize'];
        $filesizelimit      = formatbytes($maxszie);
        $siteurl            = phpcom::$G['siteurl'];
        $instdir            = $siteurl;
        $charset            = phpcom::$G['charset'];
        $posttime           = time();

        Attachment::setExtensionAndSize($type, $chanid);
        if (empty(phpcom::$G['group']['attachext'])) {
            $attachextensions = '*.*';
        } else {
            $attachextensions = '*.' . implode(';*.', phpcom::$G['group']['attachext']);
        }

        include template('common/attachupload');
    }

    private function _showJS() {
        echo '<script type="text/javascript">' . "\n";
        echo 'jQuery.noConflict();' . "\n";
        echo 'jQuery(function($) {' . "\n";
        echo '    var getInfo = function(obj) {' . "\n";
        echo '        var rv = {};' . "\n";
        echo '        rv.width = obj.width();' . "\n";
        echo '        rv.width += parseInt(obj.css("padding-left"));' . "\n";
        echo '        rv.width += parseInt(obj.css("padding-right"));' . "\n";
        echo '        rv.width += parseInt(obj.css("border-left-width"));' . "\n";
        echo '        rv.width += parseInt(obj.css("border-right-width"));' . "\n";
        echo '        rv.width += "px";' . "\n";
        echo '' . "\n";
        echo '        rv.height = obj.height();' . "\n";
        echo '        rv.height += parseInt(obj.css("padding-top"));' . "\n";
        echo '        rv.height += parseInt(obj.css("padding-bottom"));' . "\n";
        echo '        rv.height += parseInt(obj.css("border-top-width"));' . "\n";
        echo '        rv.height += parseInt(obj.css("border-bottom-width"));' . "\n";
        echo '        rv.height += "px";' . "\n";
        echo '' . "\n";
        echo '        rv.top = "-" + obj.css("border-top-width");' . "\n";
        echo '        rv.left = "-" + obj.css("border-left-width");' . "\n";
        echo '' . "\n";
        echo '        rv.border_radius = obj.css("border-radius")' . "\n";
        echo '' . "\n";
        echo '        return rv;' . "\n";
        echo '    }' . "\n";
        echo '' . "\n";
        echo '    var showMask = function(obj) {' . "\n";
        echo '        if(!obj.find(".mask").length) {' . "\n";
        echo '            obj.append("<div class=\"mask\"></div>");' . "\n";
        echo '        }' . "\n";
        echo '        var info = getInfo(obj);' . "\n";
        echo '        var mask = obj.find(".mask");' . "\n";
        echo '        mask.css({"top":info.top, "left":info.left, "width":info.width, "height":info.height, "border-radius":info.border_radius})' . "\n";
        echo '            .fadeTo(100, 0.5).delay(100)' . "\n";
        echo '            .nextAll().fadeOut(100, function() { $(this).remove(); });' . "\n";
        echo '    };' . "\n";
        echo '' . "\n";
        echo '    var closeMask = function(obj) {' . "\n";
        echo '        obj.find(".mask, .msg").fadeOut(100, function(){ $(this).remove(); });' . "\n";
        echo '    }' . "\n";
        echo '' . "\n";
        echo '    var showMsg = function(obj, message) {' . "\n";
        echo '        if(!obj.find(".mask").length) {' . "\n";
        echo '            obj.append("<div class=\"mask\"></div>");' . "\n";
        echo '        }' . "\n";
        echo '        if(!obj.find(".msg").length) {' . "\n";
        echo '            obj.append("<div class=\"msg\"></div>");' . "\n";
        echo '        }' . "\n";
        echo '        var info = getInfo(obj);' . "\n";
        echo '        var mask = obj.find(".mask");' . "\n";
        echo '        var msg = obj.find(".msg");' . "\n";
        echo '        if(msg.hasClass("confirm")) {' . "\n";
        echo '            msg.removeClass("confirm");' . "\n";
        echo '        }' . "\n";
        echo '        msg.empty().html("<p>"+message+"</p");' . "\n";
        echo '        mask.css({"top":info.top, "left":info.left, "width":info.width, "height":info.height})' . "\n";
        echo '            .fadeTo(100, 0.5).delay(1000);' . "\n";
        echo '        msg.fadeIn(100).delay(1000);' . "\n";
        echo '        msg.css("margin-top", (msg.height() / -2) + "px");' . "\n";
        echo '    }' . "\n";
        echo '' . "\n";
        echo '    var showConfirm = function(obj, message, btn1, btn2, fun) {' . "\n";
        echo '        var clickCancel = function(e) { closeMask(e.data); }' . "\n";
        echo '' . "\n";
        echo '        if(btn1 == undefined) btn1 = "OK";' . "\n";
        echo '        if(btn2 == undefined) btn2 = "Cancel";' . "\n";
        echo '        if(fun == undefined) fun = clickCancel;' . "\n";
        echo '' . "\n";
        echo '        if(!obj.find(".mask").length) {' . "\n";
        echo '            obj.append("<div class=\"mask\"></div>");' . "\n";
        echo '        }' . "\n";
        echo '        if(!obj.find(".msg").length) {' . "\n";
        echo '            obj.append("<div class=\"msg confirm\"></div>");' . "\n";
        echo '        }' . "\n";
        echo '        var info = getInfo(obj);' . "\n";
        echo '        var mask = obj.find(".mask");' . "\n";
        echo '        var msg = obj.find(".msg");' . "\n";
        echo '        if(!msg.hasClass("confirm")) {' . "\n";
        echo '            msg.addClass("confirm");' . "\n";
        echo '        }' . "\n";
        echo '        msg.empty().html("<p>"+message+"</p")' . "\n";
        echo '            .append("<div class=\"buttons\"><a href=\"javascript:void(0);\" class=\"btn warning\">" + btn1 + "</a><a href=\"javascript:void(0);\" class=\"btn cancel\">" + btn2 + "</a></div>");' . "\n";
        echo '        mask.css({"top":info.top, "left":info.left, "width":info.width, "height":info.height}).fadeTo(100, 0.5);' . "\n";
        echo '        obj.find(".msg .btn:eq(0)").click(obj, fun);' . "\n";
        echo '        obj.find(".msg .btn:eq(1)").click(obj, clickCancel);' . "\n";
        echo '        msg.fadeIn(100);' . "\n";
        echo '    }' . "\n";
        echo '' . "\n";
        echo '    $(".image img")' . "\n";
        echo '        .each(function() {' . "\n";
        echo '            var that = $(this);' . "\n";
        echo '            if(that.height()) {' . "\n";
        echo '                that.css("margin-top", ((160 - that.height()) / 2) + "px" ).show();' . "\n";
        echo '            }' . "\n";
        echo '        })' . "\n";
        echo '        .load(function() {' . "\n";
        echo '            var that = $(this);' . "\n";
        echo '            that.css("margin-top", ((160 - that.height()) / 2) + "px" ).show();' . "\n";
        echo '        });' . "\n";
        echo '' . "\n";
        echo '    $(".attList li")' . "\n";
        echo '        .mouseover(function(e) {' . "\n";
        echo '            var target = $(e.target);' . "\n";
        echo '            if(!target.is(".mask, .msg, .msg *"))' . "\n";
        echo '                $(this).addClass("hover");' . "\n";
        echo '        })' . "\n";
        echo '        .mouseout(function() { $(this).removeClass("hover");});' . "\n";
        echo '' . "\n";
        echo '    $(".attList input.select").change(function(){' . "\n";
        echo '        var that = $(this);' . "\n";
        echo '        var parent = that.closest("li");' . "\n";
        echo '        if(that.prop("checked")) {' . "\n";
        echo '            if(!parent.hasClass("selected")) {' . "\n";
        echo '                parent.addClass("selected");' . "\n";
        echo '            }' . "\n";
        echo '        } else {' . "\n";
        echo '            if(parent.hasClass("selected")) {' . "\n";
        echo '                parent.removeClass("selected");' . "\n";
        echo '            }' . "\n";
        echo '        }' . "\n";
        echo '        if($("input.select:checked").length) {' . "\n";
        echo '            $(".bdelete").removeAttr("disabled").removeClass("disabled");' . "\n";
        echo '        } else {' . "\n";
        echo '            $(".bdelete").attr("disabled", "disabled").addClass("disabled");' . "\n";
        echo '        }' . "\n";
        echo '    });' . "\n";
        echo '' . "\n";
        echo '    $(".preview").change(function() {' . "\n";
        echo '        var that = $(this);' . "\n";
        echo '        if(that.prop("checked")) {' . "\n";
        echo '            that.next().addClass("c2");' . "\n";
        echo '        } else {' . "\n";
        echo '            that.next().removeClass("c2");' . "\n";
        echo '        }' . "\n";
        echo '    })' . "\n";
        echo '' . "\n";
        echo '    $(".attList .save").click(function() {' . "\n";
        echo '        var li = $(this).closest("li");' . "\n";
        echo '        showMask(li);' . "\n";
        echo '' . "\n";
        echo '        var url = "?m=attachment&action=save&chanid=' . $this->chanid . '";' . "\n";
        echo '        var aid = parseInt(li.attr("id").substr(4));' . "\n";
        echo '        var preview = li.find("#preview_" + aid).prop("checked") ? 1 : 0;' . "\n";
        echo '        var description = li.find("#description_" + aid).is(".empty") ? "" : li.find("#description_" + aid).val();' . "\n";
        echo '' . "\n";
        echo '        $.post(url, {' . "\n";
        echo '                "aid": aid,' . "\n";
        echo '                "sortord": li.find("#sortord_" + aid).val(),' . "\n";
        echo '                "preview": preview,' . "\n";
        echo '                "url": li.find("#url_" + aid).val(),' . "\n";
        echo '                "description": description' . "\n";
        echo '            }, function(result) {' . "\n";
        echo '                if(!result.status) {' . "\n";
        echo '                    showMsg(li, "' . adminlang('attachment_save_failed') . '");' . "\n";
        echo '                }' . "\n";
        echo '                closeMask(li)' . "\n";
        echo '            }, "json"' . "\n";
        echo '        );' . "\n";
        echo '    });' . "\n";
        echo '' . "\n";
        echo '    $(".attList .delete").click(function() {' . "\n";
        echo '        var that = $(this);' . "\n";
        echo '        var li = that.closest("li");' . "\n";
        echo '        showConfirm(li, "' . adminlang('attachment_confirm_delete') . '", "' . adminlang('attachment_btn_delete') . '", "' . adminlang('attachment_btn_cancel') . '", function(){' . "\n";
        echo '            showMask(li);' . "\n";
        echo '            var url = "?m=attachment&action=delete&chanid=' . $this->chanid . '";' . "\n";
        echo '            var aid = parseInt(li.attr("id").substr(4));' . "\n";
        echo '            $.post(url, {"aid": aid}, function(result) {' . "\n";
        echo '                if(result.status)  {' . "\n";
        echo '                    closeMask(li);' . "\n";
        echo '                    li.fadeOut(100, function(){' . "\n";
        echo '                        this.remove();' . "\n";
        echo '                        if($("input.select:checked").length) {' . "\n";
        echo '                            $(".bdelete").removeAttr("disabled").removeClass("disabled");' . "\n";
        echo '                        } else {' . "\n";
        echo '                             $(".bdelete").attr("disabled", "disabled").addClass("disabled");' . "\n";
        echo '                        }' . "\n";
        echo '                    });' . "\n";
        echo '                } else {' . "\n";
        echo '                    showMsg(li, "' . adminlang('attachment_delete_failed') . '");' . "\n";
        echo '                    closeMask(li);' . "\n";
        echo '                }' . "\n";
        echo '            });' . "\n";
        echo '        });' . "\n";
        echo '    });' . "\n";
        echo '' . "\n";
        echo '    $("textarea")' . "\n";
        echo '        .keydown(function() {' . "\n";
        echo '            $(this).prev().hide();' . "\n";
        echo '        })' . "\n";
        echo '        .keyup(function() {' . "\n";
        echo '            if($(this).val()) {' . "\n";
        echo '                $(this).prev().hide();' . "\n";
        echo '            } else {' . "\n";
        echo '                $(this).prev().show();' . "\n";
        echo '            }' . "\n";
        echo '        });' . "\n";
        echo '' . "\n";
        echo '    $("[name=bDelete]").click(function(e) {' . "\n";
        echo '        if(this.submit == undefined) {' . "\n";
        echo '            this.submit = false;' . "\n";
        echo '        }' . "\n";
        echo '        if(!this.submit) {' . "\n";
        echo '            var btn = this;' . "\n";
        echo '            $("body").append("<div id=\"full_mask\"></div><div id=\"full_msg\"><p>' . adminlang('attachment_confirm_bdelete') . '</p><p><input type=\"button\" value=\"' . adminlang('attachment_btn_delete') . '\" class=\"att-btn bdelete\" /><input type=\"button\" value=\"' . adminlang('attachment_btn_cancel') . '\" class=\"att-btn\" /></p></div>");' . "\n";
        echo '            $("#full_mask").css({' . "\n";
        echo '                    "top": "0px",' . "\n";
        echo '                    "left": "0px",' . "\n";
        echo '                    "width": $(document).width() + "px",' . "\n";
        echo '                    "height": $(document).height() + "px"' . "\n";
        echo '                }).fadeTo(100, 0.5);' . "\n";
        echo '            var msg = $("#full_msg");' . "\n";
        echo '            msg.fadeIn(100);' . "\n";
        echo '            msg.css({' . "\n";
        echo '                "top": (($(window).height() - msg.height()) / 2) + "px",' . "\n";
        echo '                "left": (($(document).width() - msg.width()) / 2) + "px"' . "\n";
        echo '            });' . "\n";
        echo '            $("#full_msg :button:eq(0)").click(function() {' . "\n";
        echo '                $("#full_msg").fadeOut(100, function() { this.remove(); });' . "\n";
        echo '                btn.submit = true;' . "\n";
        echo '                $("[name=bDelete]").click();' . "\n";
        echo '            });' . "\n";
        echo '            $("#full_msg :button:eq(1)").click(function() { $("#full_mask, #full_msg").remove(); });' . "\n";
        echo '        }' . "\n";
        echo '        return this.submit;' . "\n";
        echo '    });' . "\n";
        echo '' . "\n";
        echo '    $("[name=saveAll]").click(function() {' . "\n";
        echo '        $("body").append("<div id=\"full_mask\"></div>");' . "\n";
        echo '        $("#full_mask").css({' . "\n";
        echo '            "top": "0px",' . "\n";
        echo '            "left": "0px",' . "\n";
        echo '            "width": $(document).width() + "px",' . "\n";
        echo '            "height": $(document).height() + "px"' . "\n";
        echo '        }).fadeTo(100, 0.5);' . "\n";
        echo '        return true;' . "\n";
        echo '    });' . "\n";
        echo '' . "\n";
        echo '    $("#add_image").click(function() {' . "\n";
        echo '        $("body").append("<div id=\"full_mask\"></div>");' . "\n";
        echo '        $("#full_mask").css({' . "\n";
        echo '                "top": "0px",' . "\n";
        echo '                "left": "0px",' . "\n";
        echo '                "width": $(document).width() + "px",' . "\n";
        echo '                "height": $(document).height() + "px"' . "\n";
        echo '            }).fadeTo(100, 0.5, function() {' . "\n";
        echo '                var top = ($(window).height() - 325) / 2;' . "\n";
        echo '                var left = ($(window).width() - 580) / 2;' . "\n";
        echo '                $("#fbox_uploadattach").css({"top": top + "px", "left": left + "px", "z-index": 1001}).show();' . "\n";
        echo '            });' . "\n";
        echo '    });' . "\n";
        echo '' . "\n";
        echo '    $("li.y a").click(function() {' . "\n";
        echo '        $("#full_mask").remove();' . "\n";
        echo '    })' . "\n";
        echo '});' . "\n";
        echo '</script>' . "\n";
    }

    //------------------------------ Model ------------------------------

    private function _dbGetThread($tid = 0) {
        $tid = $tid ? $tid : $this->tid;

        $qs  = "SELECT c.catname, t.title, t.catid\n";
        $qs .= "FROM " . DB::table('threads') . " AS t\n";
        $qs .= "JOIN " . DB::table('category') . " AS c USING(catid)\n";
        $qs .= "WHERE t.tid = {$tid}\n";


        return DB::fetch_first($qs);
    }

    private function _dbGetAttList($tid = 0) {
        $tid = $tid ? $tid : $this->tid;
        $result = array_values(Attachment::getAttachlist($tid, 1, $this->module));
        foreach($result as $k => $v) {
            $result[$k]['imgUrl'] = phpcom::$G['siteurl'] . getattachimgurl($v['attachid'], 160, 160, 1, 'geom', 1, $this->chanid);
        }
        return $result;
    }

    private function _dbUpdate() {
        $data = $this->_getSubmitData();
        $aid = $data['attachid'];
        unset($data['attachid']);

        if(DB::update('attachment_' . $this->module, $data, "attachid = '$aid'") === false) {
            return false;
        } else {
            return true;
        }
    }

    private function _dbDelete($aid) {
        if(is_array($aid)) {
            $condition = "attachid IN (" . implode(',', $aid) . ")";
        } else {
            $condition = "attachid = '$aid'";
        }

        $SQL = DB::query("SELECT * FROM " . DB::table('attachment_' . $this->module) . " WHERE $condition");
        while($row = DB::fetch_array($SQL)) {
            $row['module'] = $this->module;
            Attachment::unlinks($row);
        }

        if(DB::delete('attachment_' . $this->module, $condition) === false) {
            return false;
        }

        if(DB::delete('attachment', $condition) === false) {
            return false;
        }

        return true;
    }

    private function _dbSaveAll() {
        if(!isset(phpcom::$G['gp_attachnew']))
            return true;

        $data = array();
        foreach(phpcom::$G['gp_attachnew'] as $aid => $data) {
            $data['preview'] = isset($data['preview']) ? 1 : 0;
            DB::update('attachment_' . $this->module, $data, "attachid = '$aid'");
        }
    }

    private function _dbPerpetuate($posttime, $uid, $chanid) {
        $tempAtt = Attachment::getAttachtemp($posttime, $uid, $chanid);
        $sortord = DB::fetch_first("SELECT sortord FROM " . DB::table('attachment_' . $this->module) . " WHERE tid = {$this->tid} ORDER BY sortord DESC");
        $sortord = !$sortord ? 0 : intval($sortord['sortord']);
        $newAtt = array();
        $ids = array();
        foreach($tempAtt as $k => $att) {
            $newAtt['attachid']       = $att['attachid'];
            $newAtt['chanid']         = $att['chanid'];
            $newAtt['tid']            = $this->tid;
            $newAtt['uid']            = $att['uid'];
            $newAtt['sortord']        = $att['sortord'] + $sortord;
            $newAtt['filesize']       = $att['filesize'];
            $newAtt['attachment']     = $att['attachment'];
            $newAtt['description']    = '';
            $newAtt['url']            = '';
            $newAtt['dateline']       = $att['dateline'];
            $newAtt['thumb']          = $att['thumb'];
            $newAtt['preview']        = $att['preview'];
            $newAtt['image']          = $att['image'];
            $newAtt['remote']         = $att['remote'];
            $newAtt['width']          = $att['width'];
            DB::insert('attachment_' . $this->module, $newAtt);

            $ids[]                 = $att['attachid'];
        }
        $condition = 'attachid IN (' .implode(',', $ids) . ')';
        DB::delete('attachment_temp', $condition);
        DB::update('attachment', array('tid' => $this->tid, 'tableid' => $this->chanid), $condition);
    }

    private function _getSubmitData() {
        $fields = array(
            'attachid' => 'int',
            'preview' => 'int',
            'sortord' => 'int',
            'url' => 'varchar',
            'description' => 'varchar',
        );
        $map = array(
            'attachid' => 'aid',
        );

        $data = array();
        foreach ($fields as $key => $value) {
            $postKey = isset($map[$key]) ? $map[$key] : $key;
            if(isset($_POST[$postKey])){
                switch($value) {
                    case 'int':
                        // $data[$key] = intval($_POST[$postKey]);
                        $data[$key] = intval(phpcom::$G['gp_'. $postKey]);
                        break;
                    case 'text':
                        // $data[$key] = trim($_POST[$postKey], "\r\n");
                        $data[$key] = mb_convert_encoding(trim(phpcom::$G['gp_'. $postKey], "\r\n"), phpcom::$G['charset'], 'utf-8');
                        break;
                    case 'varchar':
                        // $data[$key] = trim($_POST[$postKey]);
                        $data[$key] = mb_convert_encoding(trim(phpcom::$G['gp_'. $postKey]), phpcom::$G['charset'], 'utf-8');
                        break;
                    case 'timestamp':
                        // $data[$key] = (strcasecmp($_POST[$postKey], 'server') === 0) ? phpcom::$G['timestamp'] : strtotime($_POST[$postKey]);
                        $data[$key] = (strcasecmp(phpcom::$G['gp_'. $postKey], 'server') === 0) ? phpcom::$G['gp_'. $postKey] : strtotime(phpcom::$G['gp_'. $postKey]);
                        break;
                    default:
                        // $data[$key] = $_POST[$postKey];
                        $data[$key] = phpcom::$G['gp_'. $postKey];
                        break;
                }
            }
        }
        return $data;
    }
}