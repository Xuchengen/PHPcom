function checkVerifyCode(id, d){
	var verifycode = $(id).value.trim();
	if(!d) d = '';
	if(verifycode == '') return;
	var obj = $('checkCaptcha');
	obj.style.display = '';
	var img = obj.getElementsByTagName('IMG')[0];
	if(verifycode.length != 4){
		if(img){
			img.src = d + 'misc/images/common/chkerr.gif';
		}else{
			obj.className = 'chkerr';
		}
		return;
	}
	var url = 'apps/misc.php?action=captcha&inajax=1&token=captcha&check=yes&verifycode=' + (phpcom.isIE && document.charset == 'utf-8' ? encodeURIComponent(verifycode) : verifycode);
	var ajax = ajaxObject();
	ajax.setWaiting(id);
	ajax.get(url,function(s){
		if(s.substr(0, 7) == 'succeed'){
			if(img){
				img.src = d + 'misc/images/common/chksuc.gif';
			}else{
				obj.className = 'chksuc';
			}
		}else{
			if(img){
				img.src = d + 'misc/images/common/chkerr.gif';
			}else{
				obj.className = 'chkerr';
			}
		}
	});
}

function togglePageTabs(name){
	var tabName = null;
	var obj = $('tab_' + name);
	var tabs = obj.parentNode.getElementsByTagName('DD');
	for(var i = 0; i < tabs.length; i++) {
		if(tabs[i].id && tabs[i].id.substr(0, 4) == 'tab_' && tabs[i].id != obj.id) {
			tabName = 'panel_' + tabs[i].id.substr(4);
			if($(tabName)) {
				tabs[i].className = '';
				$(tabName).style.display = 'none';
			}
		}
	}
	obj.className = 'current';
	$('panel_'+name).style.display = '';
}

function toggleDisplay(id, m){
	if(m == 'hide'){
		if($(id)) $(id).style.display = 'none';
	}else{
		if($(id)) $(id).style.display = '';
	}
}

function appendBlockLink(id, tag) {
	if(!$(id)) return false;
	var a = $(id).getElementsByTagName(tag);
	var taglist = {'A':1, 'INPUT':1, 'IMG':1};
	for(var i = 0, len = a.length; i < len; i++) {
		if(a[i].className != 'tbb'){
			a[i].onmouseover = function () {
				if(this.className.indexOf(' hover') == -1) {
					this.className = this.className + ' hover';
				}
			};
			a[i].onmouseout = function () {
				this.className = this.className.replace(' hover', '');
			};
			a[i].onclick = function (e) {
				e = e ? e : window.event;
				var target = e.target || e.srcElement;
				if(!taglist[target.tagName]) {
					window.location.href = $(this.id + '_a').href;
				}
			};
		}
	}
}