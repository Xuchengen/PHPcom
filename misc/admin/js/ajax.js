function contentWindowObject() {
	var o = new Object();
	o.responseText = null;
	o.xmlhttp = false;
	o.recallback = null;
	var divEl = document.createElement('div');
	divEl.style.display = 'none';
	var frameName = 'ajaxframe' + new Date().getTime();
	divEl.innerHTML = '<iframe src="about:blank" id="' + frameName + '" name="' + frameName + '"></iframe>';
	document.body.appendChild(divEl);
	o.iframe = divEl.firstChild;
	
	o.post = function(url, data, recall) {
		o.recallback = recall;
		if (typeof data == 'string' && data != '') {
			var form = document.createElement('form');
			form.name	 = 'ajaxform';
			form.target = frameName;
			form.method = 'post';
			form.action = url;
			var args = data.split("&");
			for (var i = 0; i < args.length; i++) {
				if (args[i]) {
					var arg	 = args[i];
					var el	 = document.createElement('input');
					el.type  = 'hidden';
					el.name  = arg.substr(0,arg.indexOf('='));
					el.value = arg.substr(arg.indexOf('=') + 1);
					form.appendChild(el);
				}
			}
			document.body.insertBefore(form,document.body.childNodes[0]);
			form.submit();
			document.body.removeChild(form);
		}else if (typeof data == 'object') {
			var action = data.getAttribute('action');
			if (typeof action == 'object') {
				var node = action.parentNode;
				node.removeChild(action);
				data.setAttribute('action', url);
				node.appendChild(action);
			} else {
				data.setAttribute('action', url);
			}
			data.target = frameName;
			data.submit();
		}else{
			self.frames[frameName].location.replace(url);
		}
	};
	o.loadEvent = function() {
		if (o.iframe.attachEvent) {
			o.iframe.detachEvent('onload',o.load);
			o.iframe.attachEvent('onload',o.load);
		} else {
			o.iframe.addEventListener('load',o.load,true);
		}
	};
	o.load = function() {
		try{
			o.responseText = o.iframe.contentWindow.document.documentElement.textContent;
			if (typeof o.responseText == 'undefined'){ //IE
				o.responseText = (typeof o.iframe.contentWindow.document.XMLDocument != 'undefined') ? o.iframe.contentWindow.document.XMLDocument.text : null;
			}
			if(!o.responseText){
				var txt = o.iframe.contentWindow.document.documentElement.innerText;
				var rules = /<!\[CDATA\[([\s\S]+)\]\]>/.exec(txt);
				if(rules && rules[1]){
					o.responseText = rules[1];
				}
			}
		}catch(e){
			o.responseText = (typeof o.iframe.contentWindow.document.XMLDocument != 'undefined') ? o.iframe.contentWindow.document.XMLDocument.text : null;
		}
		if (o.iframe.detachEvent) {
			o.iframe.detachEvent('onload',o.load);
		} else {
			o.iframe.removeEventListener('load',o.load,true);
		}
		if (typeof o.recallback == "function") {
			o.recallback();
		}
	};
	o.clearhistroy = function() {
		self.frames[frameName].location.replace('about:blank');
	};
	return o;
}

function xmlHttpObject() {
	var o = new Object();
	o.responseText = null;
	o.createXMLHttpRequest = function() {
		var request = false;
		if(window.XMLHttpRequest) {
			request = new XMLHttpRequest();
			if(request.overrideMimeType) {
				request.overrideMimeType('text/xml');
			}
		} else if(window.ActiveXObject) {
			var versions = ['Microsoft.XMLHTTP', 'MSXML.XMLHTTP', 'Microsoft.XMLHTTP', 'Msxml2.XMLHTTP.7.0', 'Msxml2.XMLHTTP.6.0', 'Msxml2.XMLHTTP.5.0', 'Msxml2.XMLHTTP.4.0', 'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP'];
			for(var i=0; i<versions.length; i++) {
				try {
					request = new ActiveXObject(versions[i]);
					if(request) {
						return request;
					}
				} catch(e) {}
			}
		}
		return request;
	};
	o.xmlhttp = o.createXMLHttpRequest();
	o.recallback = null;
	o.post = function(url, data, recall) {
		if (typeof recall != "function") recall = this.processCallBack;
		o.xmlhttp.onreadystatechange = recall;
		o.xmlhttp.open('POST', url);
		o.xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		o.xmlhttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
		o.xmlhttp.send(data);
	};
	o.get = function(url, recall) {
		if (typeof recall != "function") recall = this.processCallBack;
		o.xmlhttp.onreadystatechange = recall;
		if(window.XMLHttpRequest) {
			setTimeout(function(){
			o.xmlhttp.open('GET', url);
			o.xmlhttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			o.xmlhttp.send(null);}, 100);
		} else {
			setTimeout(function(){
			o.xmlhttp.open("GET", url, true);
			o.xmlhttp.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
			o.xmlhttp.send();}, 100);
		}
	};
	o.processCallBack = function() {
		if (typeof o.recallback == "function") {
			o.recallback();
		}
	};
	o.clearhistroy = function() {
		delete o.xmlhttp;
	};
	return o;
}

function XMLHttp() {
	this.request	= null;
	this.recall		= null;
	this.time		= null;
	this.timeout	= null;
	this.args		= new Array();
	this.last		= 0;
	this.reqType	= 'XMLHTTP';
	this.isIE		= (navigator.userAgent.toLowerCase().indexOf("msie") > -1);
	this.isWebKit	= (navigator.userAgent.toLowerCase().indexOf("webkit") > -1);
}

XMLHttp.prototype = {
	send : function(url,data,callback) {
		if (this.request == null || this.reqType != 'XMLHTTP') {
			this.request = xmlHttpObject();
			this.reqType = 'XMLHTTP';
		}
		this.request.responseText = '';
		var timenow	= new Date().getTime();
		url	+= (url.indexOf("?") >= 0) ? "&t=" + timenow : "?t=" + timenow;
		url = url.replace(/\&inajax\=1/g, '')+'&inajax=1';
		this.request.post(url,data);
		this.recall = callback;
		if (typeof this.recall == "function") {
			this.request.recallback = this.recallback;
		}
	},
	recallback : function() {
		if(ajax.request.xmlhttp.readyState == 4 && ajax.request.xmlhttp.status == 200) {
			ajax.request.responseText = ajax.request.xmlhttp.responseXML.lastChild.firstChild.nodeValue;
			if (typeof(ajax.recall) == 'function') {
				ajax.recall();
			}
		}
	},
	get : function(url,data,callback) {
		if (this.request == null || this.reqType != 'XMLHTTP') {
			this.request = xmlHttpObject();
			this.reqType = 'XMLHTTP';
		}
		this.request.responseText = '';
		var timenow	= new Date().getTime();
		url	+= (url.indexOf("?") >= 0) ? "&t=" + timenow : "?t=" + timenow;
		url = url.replace(/\&inajax\=1/g, '')+'&inajax=1';
		this.request.get(url);
		this.recall = callback;
		if (typeof this.recall == "function") {
			this.request.recallback = this.recallback;
		}
	},
	post : function(url,data,callback) {
		if (this.request == null || this.reqType == 'XMLHTTP') {
			this.request = contentWindowObject();
			this.reqType = 'contentWindow';
		}
		this.request.responseText = '';
		var timenow	= new Date().getTime();
		if (timenow - this.last < 1500) {
			clearTimeout(this.timeout);
			this.timeout = setTimeout(function(){ajax.post(url,data,callback)},1500 + this.last - timenow);
			return;
		}
		this.last = timenow;
		url	+= (url.indexOf("?") >= 0) ? "&t=" + timenow : "?t=" + timenow;
		if (typeof checkhash != 'undefined') url += '&chkhash=' + checkhash;
		url = url.replace(/\&inajax\=1/g, '')+'&inajax=1';
		this.request.post(url,data);
		this.recall = callback;
		if (typeof this.recall == "function") {
			this.request.recallback = this.load;
			this.request.loadEvent();
		}
		
	},
	load : function() {
		if (typeof(ajax.recall) == 'function') {
			ajax.recall();
		}
		ajax.request.clearhistroy();
	}
}

var ajax = new XMLHttp();

function openWindow(url,id){
	setTimeout(function(){ajax.get(url,'',function(){
		return showDialog(parsescript(ajax.request.responseText),'info','设置标题样式');
	});},100);
}

function styleWindow(url, id){
	var divEl = $('style_menu');
	if (!divEl){
		divEl = document.createElement('div');
		divEl.style.display = 'none';
		divEl.id = 'style_menu';
		try {document.body.insertBefore(divEl, null);}catch(e){}
		ajax.get(url,'',function(){
			var s = menu.initTable(parsescript(ajax.request.responseText),'style_menu','设置标题样式');
			divEl.innerHTML=s;
			menu.show({'ctrlid':id,'menuid':'style_menu','type':'dialog','duration':3,'drag':'floatctrl_drag'});
		});
	}else{
		menu.show({'ctrlid':id,'menuid':'style_menu','type':'dialog','duration':3,'drag':'floatctrl_drag'});
	}
}

var evalscripts=[];
function parsescript(html) {
	if(html.indexOf('<script') == -1) return html;
	html = html.replace(/<script(.*?)>([^\x00]*?)<\/script>/ig, function(all, attr, text) {
		var src=charset='',reload=false;
		if (attr.match(/\s*src\s*=\"([^\"]+?)\"/i)) {
			src = RegExp.$1;
			text = '';
			if (attr.match(/\s*charset\s*=\"([^\"]+?)\"/i)) {
				charset = RegExp.$1;
			}
			reload = attr.match(/\s*reload\s*=\"1\"/i)
		}
		appendscript(src, text, reload, charset);
		return '';
	});
	return html;
}

function appendscript(src, text, reload, charset) {
	var id = 'Ajax'+(src + text).hashCode();
	if(!reload && in_array(id, evalscripts)) return;
	if(reload && $(id)) $(id).parentNode.removeChild($(id));
	evalscripts.push(id);
	var header = document.getElementsByTagName("head")[0];
	var sNode = document.createElement("script");
	sNode.type = "text/javascript";
	sNode.id = id;
	try {
		if(src) {
			sNode.src = src;
			if(charset) sNode.charset = charset;
			sNode.onloadDone = false;
			sNode.onload = function () {
				sNode.onloadDone = true;
			};
			sNode.onreadystatechange = function () {
				if((sNode.readyState == 'loaded' || sNode.readyState == 'complete') && !sNode.onloadDone) {
					sNode.onloadDone = true;
				}
			};
		} else if(text){
			sNode.text = text;
		}
		header.appendChild(sNode);
	} catch(e) {}
	return true;
}

function ajaxpost(){

}