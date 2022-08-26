String.prototype.contains = function(str) {
	return (this.indexOf(str) > -1);
};
String.prototype.trim = function(s) {
	if (s) return this.trimEnd(s).trimStart(s);
	else return this.replace(/(^[ \t\n\r]*)|([ \t\n\r]*$)/g, '');
};
String.prototype.trimEnd = function(s) {
	if (this.endsWith(s)) {
		return this.substring(0, this.length - s.length);
	}
	return this;
};
String.prototype.trimStart = function(s) {
	if (this.startsWith(s)) {
		return this.slice(s.length);
	}
	return this;
};
String.prototype.startsWith = function(str) {
	return (this.indexOf(str) == 0);
};
String.prototype.endsWith = function(str) {
	return (str.length <= this.length && this.substr(this.length - str.length, str.length) == str);
};
String.prototype.hashCode = function() {
	for (var h = 31,i = 0, l = this.length; i < l;) h ^= (h << 5) + (h >> 2) + this.charCodeAt(i++);
	return h
}
String.format = function() {
	var str = arguments[0];
	for (var i = 1; i < arguments.length; i++) {
		var reg = new RegExp("\\{" + (i - 1) + "\\}", "ig");
		str = str.replace(reg, arguments[i]);
	}
	return str;
};
Array.prototype.contains = function(val) {
	for (var i = 0; i < this.length; i++) {
		if (val == this[i]) return true;
	}
	return false;
}

var phpcom = {
	browser : {
		isIE: navigator.userAgent.toLowerCase().contains("msie"),
		isIE5: navigator.userAgent.toLowerCase().contains("msie 5"),
		isIE6: navigator.userAgent.toLowerCase().contains("msie 6"),
		isIE7: navigator.userAgent.toLowerCase().contains("msie 7"),
		isIE8: navigator.userAgent.toLowerCase().contains("msie 8"),
		isIE9: navigator.userAgent.toLowerCase().contains("msie 9"),
		isGecko: navigator.userAgent.toLowerCase().contains("gecko"),
		isFirefox: (navigator.userAgent.toLowerCase().contains("firefox") || this.isGecko),
		isSafari: navigator.userAgent.toLowerCase().contains("safari"),
		isOpera: navigator.userAgent.toLowerCase().contains("opera"),
		isWebKit: navigator.userAgent.toLowerCase().contains("webkit"),
		getClientWidth: function () { return ((document.documentElement && document.documentElement.clientWidth) || document.body.clientWidth); },
		getClientHeight: function () { return ((document.documentElement && document.documentElement.clientHeight) || document.body.clientHeight); },
		getScrollTop: function () { return ((document.documentElement && document.documentElement.scrollTop) || document.body.scrollTop); },
		getScrollLeft: function () { return ((document.documentElement && document.documentElement.scrollLeft) || document.body.scrollLeft); },
		getFullHeight: function () { if (document.documentElement.clientHeight > document.documentElement.scrollHeight) return document.documentElement.clientHeight; else return document.documentElement.scrollHeight; },
		getFullWidth: function () { return document.documentElement.scrollWidth; },
		getBrowserRect: function () { var r = new Object(); r.left = this.getScrollLeft(); r.top = this.getScrollTop(); r.width = this.getClientWidth(); r.height = this.getClientHeight(); r.bottom = r.top + r.height; r.right = r.left + r.width; return r; }
	},
	cookie : {
		path: '/',
		domain: document.domain,
		prefix: 'phpcom_',
		set: function(cookieName, cookieValue, seconds) {
			var expires = new Date();
			if(cookieValue == '' || seconds < 0) {
				cookieValue = '';
				seconds = -2592000;
			}
			expires.setTime(expires.getTime() + seconds * 1000);
			document.cookie = escape(this.prefix + cookieName) + '=' + escape(cookieValue)
			+ (expires ? '; expires=' + expires.toGMTString() : '')
			+ (this.path ? '; path=' + this.path : '/')
			+ (this.domain ? '; domain=' + this.domain : '');
		},
		get: function(cookieName, nounescape) {
			cookieName = this.prefix + cookieName;
			var cookie_start = document.cookie.indexOf(cookieName);
			var cookie_end = document.cookie.indexOf(";", cookie_start);
			if(cookie_start == -1) {
				return '';
			} else {
				var cookieValue = document.cookie.substring(cookie_start + cookieName.length + 1, (cookie_end > cookie_start ? cookie_end : document.cookie.length));
				return !nounescape ? unescape(cookieValue) : cookieValue;
			}
		},
		del: function(cookieName) {
			cookieName = this.prefix + cookieName;
			var expires = new Date();
			expires.setTime (expires.getTime() - 1);
			document.cookie = cookieName + "=; expires=" + expires.toGMTString()
			+ (this.path ? '; path=' + this.path : '/')
			+ (this.domain ? '; domain=' + this.domain : '');
		}
	},
	event : {
		add : function(el, event, listener, obj) {
			el = !obj ? el : obj;
			if (el.addEventListener){
				el.addEventListener(event, listener, false);
			} else if (el.attachEvent){
				el.attachEvent('on' + event, listener);
			}
		},
		remove : function(el, event, listener, obj) {
			el = !obj ? el : obj;
			if (el.removeEventListener){
				el.removeEventListener(event, listener, false);
			} else if (el.detachEvent){
				el.detachEvent('on' + event, listener);
			}
		}
	}
};

function $(id) { return (typeof id == 'string' ? document.getElementById(id) : null);}

function $T(name) { return (typeof name == 'string' ? document.getElementsByTagName(name) : null);}

function display(id) {
	$(id).style.display = $(id).style.display == '' ? 'none' : '';
}

function isUndefined(variable) {
    return typeof variable == 'undefined' || variable == null ? true: false;
}

function in_array(needle, haystack) {
	if(typeof needle == 'string' || typeof needle == 'number') {
		for(var i in haystack) {
			if(haystack[i] == needle) {
					return true;
			}
		}
	}
	return false;
}

function checkall(form,chkname) {
	var chkname = chkname ? chkname : 'chkall';
	var count = 0;
	for(var i = 0; i < form.elements.length; i++) {
		var e = form.elements[i];
		if(e.name && e.name != chkname) {
			e.checked = form.elements[chkname].checked;
			if(e.checked) {
				count++;
			}
		}
	}
	return count;
}

function formatsize(s) {
    var u = ["B", "KB", "MB", "GB", "TB"];
    var uc = 0;
    while (s > 1024) { uc++; s = s / 1024; }
    s = parseFloat(s).toFixed(2);
    
    return  s + u[uc];
}

function doCopyEx(id){
	var obj = $(id);
	var code = obj.value;
	code = code == null || code == "" ? obj.innerHTML : code;
	if (phpcom.browser.isIE){
		window.clipboardData.clearData();
		clipboardData.setData('Text', code);
	}
	else{
		return setClipboard(code);
	}
}

function runCodeEx(id)  {
	var obj = $(id);
	var code = obj.value;
	code = code == null || code == "" ? obj.innerHTML : code;
	if (code){
		var win=window.open('','','');
		win.opener = null 
		win.document.write(code);  
		win.document.close();
	}
}

function saveAsCodeEx(id) {
	var obj = $(id);
	var code = obj.value;
	code = code == null || code == "" ? obj.innerHTML : code;
	var win = window.open('', '_blank', 'top=1000');
	win.document.open('text/html', 'replace');
	win.document.charset="GB2312";
	win.document.write(code);
	win.document.execCommand('saveas', false, 'code.htm');
	win.close();
}

function copyBlockCode(id) {
	var obj = $(id);
	var text;
	if (phpcom.browser.isIE){
		text = obj.innerText.replace(/\r\n\r\n/g, '\r\n');
	}else{
		var lis = obj.getElementsByTagName('li'), ar = [];
		for(var i=0,l=lis.length; i<l; i++){
			ar.push(lis[i].textContent);
		}
		text = ar.join("\r\n");
	}
	text = text.replace(/[\xA0]/g, ' ');
	return setClipboard(text);
}

function setClipboard(value) {
	if (window.clipboardData) {
		window.clipboardData.setData("Text",value);
	} else {
		function openClipWin(){
			window.clip = new ZeroClipboard.Client();
			clip.setHandCursor( true );
			clip.addEventListener('complete', function (client, text) {
				alert('复制成功!');
			});
			clip.setText(value);
			var msg = '<div onclick="phpcom.dialog.close(\'dialog_alert\');" id="clipboard_container" class="clipboard" style="position:relative"><div id="clipboard_button" style="text-align:center;cursor:pointer;">点这里复制到剪贴板</div>';
			showDialog(msg, 'info','复制代码');
			clip.glue('clipboard_button', 'clipboard_container');
		}
		if (!window.clip){
			var script = document.createElement('script');
			script.src = '/static/js/ZeroClipboard.js';
			script.onload = function(){
				ZeroClipboard.setMoviePath('/static/js/ZeroClipboard.swf');
				openClipWin();
			};
			document.body.appendChild(script);
		}else{
			openClipWin();
		}
	}
	return false;
}

function preg_replace(search, replace, str, regswitch) {
	var regswitch = !regswitch ? 'ig' : regswitch;
	var len = search.length;
	for(var i = 0; i < len; i++) {
		re = new RegExp(search[i], regswitch);
		str = str.replace(re, typeof replace == 'string' ? replace : (replace[i] ? replace[i] : replace[0]));
	}
	return str;
}

function htmlspecialchars(s){
	return preg_replace(['&', '<', '>', '"'], ['&amp;', '&lt;', '&gt;', '&quot;'], s);
}

function thumbimg(obj, method){

}

function zoomimg(obj){

}

function ajaxInnerHTML(){
}
//forum.php?mod=misc&action=votepoll&fid=2&tid=11&pollsubmit=yes&quickforward=yes&pollanswers[]=1
//http://www.discuz.cm/forum.php?mod=misc&action=votepoll&fid=2&tid=11&pollsubmit=yes&quickforward=yes&inajax=1