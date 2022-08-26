var tipInfo = tmpItem = new Array();
var lastUserName = '', lastEmail = '', lastInviteCode = '';
function addFormEvents(formid, focus){
	var num = 0;
	var formNode = $(formid).getElementsByTagName('input');
	for(var i = 0;i < formNode.length;i++) {
		if(formNode[i].type == 'text' || formNode[i].type == 'password'){
			formNode[i].onfocus = function(){
				showInputTip(!this.id ? this.name : this.id);
			};
			if(formNode[i].id && $('tip_' + formNode[i].id)){
				tmpItem[num] = i;
				tipInfo[formNode[i].id] = $('tip_' + formNode[i].id).innerHTML;
				num++;
			}
		}
	}
	if(!num) return;
	formNode[tmpItem[0]].onblur = function () {
		checkUserName(formNode[tmpItem[0]].id);
	};
	formNode[tmpItem[0]].focus();
	formNode[tmpItem[1]].onblur = function(){
		if(formNode[tmpItem[1]].value.trim() == '') {
			showerrmsg(formNode[tmpItem[1]].id, '请填写密码');
		}else{
			showerrmsg(formNode[tmpItem[1]].id, 'succeed');
		}
		checkPassWord(formNode[tmpItem[1]].id, formNode[tmpItem[2]].id);
	};
	formNode[tmpItem[2]].onblur = function(){
		if(formNode[tmpItem[2]].value.trim() == '') {
			showerrmsg(formNode[tmpItem[2]].id, '请再次输入密码');
		}
		checkPassWord(formNode[tmpItem[1]].id, formNode[tmpItem[2]].id);
	};

	formNode[tmpItem[3]].onblur = function(){
		if(formNode[tmpItem[3]].value.trim() == '') {
			showerrmsg(formNode[tmpItem[3]].id, '请输入邮箱地址');
		}
		checkEmail(formNode[tmpItem[3]].id);
	};
	if($('tip_invitecode') && $('invitecode')){
		$('invitecode').onblur = function(){
			if($('invitecode').value.trim() == '') {
				showerrmsg('invitecode', '本站已开启邀请注册，请填写邀请码');
			}else{
				checkInviteCode('invitecode');
			}
		};
	}
}

function showInputTip(id) {
	var chktip = $('formregister').getElementsByTagName('i');
	for(var i = 0;i < chktip.length;i++){
		if(chktip[i].className == 'tipmsg'){
			chktip[i].style.display = 'none';
		}
	}
	if($('tip_' + id)) {
		$('tip_' + id).className = 'tipmsg';
		$('tip_' + id).style.display = '';
		$('tip_' + id).innerHTML = tipInfo[id];
	}
	
}

function showerrmsg(id,msg){
	if($(id)) {
		showInputTip();
		msg = !msg ? '' : msg;
		if($('tip_' + id)) {
			if(msg == 'succeed') {
				msg = '';
			}
			$('tip_' + id).style.display = '';
			$('tip_' + id).innerHTML = msg;
			$('tip_' + id).className = !msg ? 'sucmsg' : 'errmsg';
		}
	}
}

function checksubmit(obj){
	var el = obj.getElementsByTagName('i');
	for(var i = 0;i < el.length;i++){
		if(el[i].className == 'tipmsg' || el[i].className == 'sucmsg' || el[i].className == 'chksuc'){
			el[i].innerHTML = '';
		}
	}
	ajaxpost('formregister', null, null, function(s){
		if (s != 'succeed')
		{
			showMessage(s);
		}
		
	});
	return false;
}

function checkUserName(id){
	showerrmsg(id);
	var username = $(id).value.trim();
	if($('tip_' + id).className == 'errmsg' && (username == '' || username == lastUserName)) {
		return;
	} else {
		lastUserName = username;
	}
	if(username.match(/<|>|#|%|\/|\\|\||\*|\$|,|;|'|"/ig)) {
		showerrmsg(id, '用户名包含敏感字符“<>#%/\|*$;”');
		return;
	}
	var unlen = username.replace(/[^\x00-\xff]/g, "**").length;
	if(unlen < usernameminlen || unlen > usernamemaxlen) {
		showerrmsg(id, unlen < usernameminlen ? '用户名小于 '+ usernameminlen +' 个字符' : '用户名超过 '+ usernamemaxlen +' 个字符');
		return;
	}
	var url = 'apps/misc.php?action=ajax&inajax=1&token=register&check=username&username=' + (phpcom.isIE && document.charset == 'utf-8' ? encodeURIComponent(username) : username);
	var ajax = ajaxObject();
	ajax.setWaiting('tip_' + id);
	ajax.get(url,function(s){
		showerrmsg(id, s);
	});
}

function checkPassWord(id1,id2){
	if(!$(id1).value && !$(id2).value) {
		return;
	}
	showerrmsg(id2);
	if($(id1).value != $(id2).value) {
		showerrmsg(id2, '两次输入的密码不一致');
	} else {
		showerrmsg(id2, 'succeed');
	}
}

function checkEmail(id) {
	showerrmsg(id);
	var email = $(id).value.trim();
	if($('tip_' + id).className == 'errmsg' && (email == '' || email == lastEmail)) {
		return;
	} else {
		lastEmail = email;
	}
	var reg=/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
	if(!reg.test(email)){
		showerrmsg(id, '无效的 Email 地址');
		return;
	}
	if(email.match(/<|>|#|%|\/|\\|\||\*|\$|,|;|'|"/ig)) {
		showerrmsg(id, 'Email 包含敏感字符“<>#%/\|*$;”');
		return;
	}
	var url = 'apps/misc.php?action=ajax&inajax=1&token=register&check=email&email=' + email;
	var ajax = ajaxObject();
	ajax.setWaiting('tip_' + id);
	ajax.get(url,function(s){
		showerrmsg(id, s);
	});
}

function checkInviteCode(id){
	showerrmsg(id);
	var invitecode = $(id).value.trim();
	if($('tip_' + id).className == 'errmsg' && (invitecode == '' || invitecode == lastInviteCode)) {
		return;
	} else {
		lastInviteCode = invitecode;
	}
	if(invitecode.match(/<|>|#|%|\/|\\|\||\*|\$|,|;|'|"/ig)) {
		showerrmsg(id, '邀请码包含敏感字符“<>#%/\|*$;”');
		return;
	}
	var url = 'apps/misc.php?action=ajax&inajax=1&token=register&check=invitecode&invitecode=' + invitecode;
	var ajax = ajaxObject();
	ajax.setWaiting('tip_' + id);
	ajax.get(url,function(s){
		showerrmsg(id, s);
	});
}

function checkVerifyCode(id){
	var verifycode = $(id).value.trim();
	if(verifycode == '') return;
	var obj = $('checkCaptcha');
	obj.style.display = '';
	if(verifycode.length != 4){
		obj.className = 'chkerr';
		return;
	}
	var url = 'apps/misc.php?action=captcha&inajax=1&token=captcha&check=yes&verifycode=' + (phpcom.isIE && document.charset == 'utf-8' ? encodeURIComponent(verifycode) : verifycode);
	var ajax = ajaxObject();
	ajax.setWaiting(id);
	ajax.get(url,function(s){
		if(s.substr(0, 7) == 'succeed'){
			obj.className = 'chksuc';
		}else{
			obj.className = 'chkerr';
		}
	});
}

function checkQuestionAnswer(id){
	var answer = $(id).value.trim();
	if(answer == '') return;
	var url = 'apps/misc.php?action=questions&inajax=1&token=questions&check=yes&answer=' + (phpcom.isIE && document.charset == 'utf-8' ? encodeURIComponent(answer) : answer);
	var ajax = ajaxObject();
	ajax.setWaiting(id);
	ajax.get(url,function(s){
		var obj = $('checkQuestions');
		obj.style.display = '';
		if(s.substr(0, 7) == 'succeed'){
			obj.className = 'chksuc';
		}else{
			obj.className = 'chkerr';
		}
	});
}

function showTerms(mask){
	showWindow('apps/ajax.php?action=terms','readterms','get',1,mask);
	$('registerterms').checked = true;
}

function checkedTerms(){
	$('registerterms').checked = true;
        hideMenu('show_window_readterms','window');
}
