var PHPCOMCODE = [];
var mapSize = {};
mapSize["xx-small"] = "1"; mapSize["small"] = "3"; mapSize["medium"] = "4"; mapSize["x-small"] = "2"; mapSize["large"] = "5"; mapSize["x-large"] = "6"; mapSize["xx-large"] = "7";
mapSize["6pt"] = "1"; mapSize["6.5pt"] = "1"; mapSize["7pt"] = "1"; mapSize["8pt"] = "2"; mapSize["9pt"] = "2"; mapSize["10pt"] = "2"; mapSize["11pt"] = "2";
mapSize["12pt"] = "3"; mapSize["13pt"] = "3"; mapSize["14pt"] = "4"; mapSize["15pt"] = "4"; mapSize["16pt"] = "4"; mapSize["17pt"] = "5"; mapSize["18pt"] = "5"; mapSize["19pt"] = "5";
mapSize["20pt"] = "6"; mapSize["21pt"] = "6"; mapSize["22pt"] = "6"; mapSize["24pt"] = "6"; mapSize["25pt"] = "7"; mapSize["36pt"] = "7";
mapSize["7px"] = "1"; mapSize["9px"] = "1"; mapSize["10px"] = "1"; mapSize["11px"] = "1"; mapSize["12px"] = "1"; mapSize["13px"] = "1";
mapSize["14px"] = "2"; mapSize["15px"] = "2"; mapSize["16px"] = "2"; mapSize["17px"] = "3"; mapSize["18px"] = "3"; mapSize["19px"] = "3";
mapSize["20px"] = "4"; mapSize["21px"] = "4"; mapSize["22px"] = "4"; mapSize["23px"] = "5"; mapSize["24px"] = "5"; mapSize["25px"] = "5";
mapSize["26px"] = "6"; mapSize["27px"] = "6"; mapSize["28px"] = "6"; mapSize["36px"] = "7";

(function() {
	tinymce.PluginManager.requireLangPack('blockcode');
	tinymce.create("tinymce.plugins.BlockCodePlugin", {
		init: function(ed, url) {
			var t = this, dialect = ed.getParam('bbcode_dialect', 'punubb').toLowerCase();

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = t['_' + dialect + '_bbcode2html'](o.content);
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.set)
					o.content = t['_' + dialect + '_bbcode2html'](o.content);

				if (o.get)
					o.content = t['_' + dialect + '_html2bbcode'](o.content);
			});

			var d = this;
			d.editor = ed;
			ed.addCommand("mceBlockCode", function(e) {
				ed.windowManager.open({
					file: url + "/blockcode.htm",
					width: ed.getParam("blockcode_popup_width", 500),
					height: ed.getParam("blockcode_popup_height", 450),
					inline: 1
				},
				{
					plugin_url: url
				})
			});
			ed.addCommand("mceInsertBlockCode", d._insertBlockCode, d);
			ed.addButton("blockcode", {
				title: "blockcode.desc",
				cmd: "mceBlockCode",
				image: url + '/img/code.gif'
			});
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('blockcode', n.nodeName == 'PRE');
			});
			// Insert quote code
			ed.addCommand('mceQuoteCode', function() {
				var sel = ed.selection.getContent();
				sel = sel.replace(/\[(\/?)(quote|code|html)\]/ig, '');
				sel = sel.replace(/^<(p)(?:\s+[^>]+)?>(.*?)<\/\1>/ig, "$2\r\n");
				sel = sel.replace(/<(p)(?:\s+[^>]+)?>(.*?)<\/\1>/ig, "$2\r\n");
				sel = sel.replace(/<(div)(?:\s+[^>]+)?>(.*?)<\/\1>/ig, "\r\n$2");
				sel = sel.replace(/<br\s*?\/?>/ig, "\r\n");
				sel = sel.replace(/<[^<>]+?>/g, ''); //Remove all HTML tags
				sel = sel.replace(/(\r\n|\n|\r)/ig, '<br />');
				if(sel)
					var str = '<div class="blockcodeStyle">' + sel + '</div>';
				ed.execCommand('mceInsertContent', false, str);
			});
			ed.addButton("quotecode", {
				title: "\u63D2\u5165\u4EE3\u7801",
				cmd: "mceQuoteCode",
				image: url + '/img/code1.gif'
			});
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('quotecode', n.nodeName == 'DIV');
			});
			//Insert download or thread
			ed.addButton("downloads", {
				title: "\u63D2\u5165\u4E0B\u8F7D\u6807\u7B7E",
				cmd: "mceInsertDownload",
				image: url + '/img/download.gif'
			});
			ed.addCommand('mceInsertDownload', function() {
				ed.execCommand('mceInsertContent', false, '<p>[download][/download]</p>');
			});
			ed.addButton("threads", {
				title: "\u63D2\u5165\u4E3B\u9898\u6807\u7B7E",
				cmd: "mceInsertThread",
				image: url + '/img/thread.gif'
			});
			ed.addCommand('mceInsertThread', function() {
				ed.execCommand('mceInsertContent', false, '<p>[thread][/thread]</p>');
			});
			//Insert atten or h3
			ed.addButton("h3code", {
				title: "\u63D2\u5165 H3",
				cmd: "mceHead3Code",
				image: url + '/img/h3.gif'
			});
			ed.addCommand('mceHead3Code', function() {
				var sel = ed.selection.getContent({ 'format' : 'text' });
				//ed.selection.getNode().innerText
				var str = '<h3>' + sel + '</h3>';
				ed.execCommand('mceInsertContent', false, str);
			});
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('h3code', n.nodeName == 'H3');
			});
			ed.addButton("attencode", {
				title: "\u63D2\u5165\u63D0\u793A\u4FE1\u606F",
				cmd: "mceAttenCode",
				image: url + '/img/atten.gif'
			});
			ed.addCommand('mceAttenCode', function() {
				var sel = ed.selection.getContent();
				sel = sel.replace(/\[(\/?)(quote|code|html)\]/ig, '');
				sel = sel.replace(/^<(p)(?:\s+[^>]+)?>(.*?)<\/\1>/ig, "$2<br/>");
				sel = sel.replace(/<(p)(?:\s+[^>]+)?>(.*?)<\/\1>/ig, "$2<br/>");
				sel = sel.replace(/<(div)(?:\s+[^>]+)?>(.*?)<\/\1>/ig, "<br/>$2");
				var str = '<div class="attenStyle">' + sel + '</div>';
				ed.execCommand('mceInsertContent', false, d._punubb_bbcode2html(str));
			});
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('attencode', n.nodeName == 'DIV');
			});
			//image
			ed.addButton("attachimage", {
				title: "\u6DFB\u52A0\u56FE\u7247",
				cmd: "mceAttachImage",
				image: url + '/img/image.gif'
			});
			ed.addCommand('mceAttachImage', function() {
				showAttachWindow('apps/ajax.php?mod=uploadattach&type=image&tid='+phpcom.tid+'&chanid='+phpcom.chanid,'image');
			});
			ed.addButton("attachment", {
				title: "\u6DFB\u52A0\u9644\u4EF6",
				cmd: "mceAttachment",
				image: url + '/img/attach.gif'
			});
			ed.addCommand('mceAttachment', function() {
				showAttachWindow('apps/ajax.php?mod=uploadattach&type=attach&tid='+phpcom.tid+'&chanid='+phpcom.chanid,'attach');
			});
		},
		getInfo: function() {
			return {
				longname: "BlockCode plugin",
				author: "Webenvoy",
				authorurl: "http://www.newasp.net",
				infourl: "http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/blockcode",
				version: tinymce.majorVersion + "." + tinymce.minorVersion
			}
		},
		_insertBlockCode: function(ui, v) {
			var t = this, ed = t.editor, h;

			h = v.content;

			ed.execCommand('mceInsertContent', false, h);
			ed.addVisual();
		},
		_insertRunCode: function(ui, v) {
			var t = this, ed = t.editor, h;

			h = v.content;

			ed.execCommand('mceInsertContent', false, h);
			ed.addVisual();
		},

		// HTML -> BBCode
		_punubb_html2bbcode: function(s) {
			var i = 0;
			s = tinymce.trim(s);

			function rep(re, str) {
				s = s.replace(re, str);
			};
			rep(/<style.*?>.*?<\/style>/ig, '');			//Remove style
			rep(/<script[^>]*?>.*?<\/script>/ig, '');		//Remove script
			rep(/<noscript.*?>.*?<\/noscript>/ig, '');		//Remove noscript

			PHPCOMCODE['count'] = -1;
			PHPCOMCODE['html'] = [];
			PHPCOMCODE['cnum'] = -1;
			PHPCOMCODE['code'] = [];
			PHPCOMCODE['hnum'] = -1;
			PHPCOMCODE['hcode'] = [];
			rep(/<div\s+class=\"blockcodeStyle\">([\s\S]+?)<\/div>/ig, function(all, code) {
				code = code.replace(/( &nbsp; &nbsp; &nbsp; &nbsp;)/ig, "\t");
				code = code.replace(/&nbsp;/ig, " ");
				code = code.replace(/<br\s*?\/?>/ig, "\n");
				code = code.replace(/<p(?: [^>]+)?>([\s\S]+?)<\/p>/ig, "$1\r\n");
				code = code.replace(/<div(?: [^>]+)?>([\s\S]+?)<\/p>/ig, "$1\r\n");
				code = code.replace(/<[^<>]+?>/ig, '');
				code = code.replace(/<br\s*?\/?>$/g, '');
				code = code.replace(/\s+$/g, '');
				return '<pre class="blockcodeStyle">' + code + '<\/pre>';
			});
			rep(/<pre\s+class=\"blockcodeStyle\">([\s\S]+?)<\/pre>/ig, function(all, code) {
				PHPCOMCODE['cnum']++;
				code = code.replace(/\$/g, "$$$$");
				code = code.replace(/( &nbsp; &nbsp; &nbsp; &nbsp;)/ig, "\t");
				code = code.replace(/&nbsp;/ig, " ");
				code = code.replace(/<br\s*?\/?>/ig, "\n");
				code = code.replace(/<p(?: [^>]+)?>([\s\S]+?)<\/p>/ig, "$1\r\n");
				code = code.replace(/<div(?: [^>]+)?>([\s\S]+?)<\/p>/ig, "$1\r\n");
				code = code.replace(/<[^<>]+?>/ig, '');
				code = code.replace(/<br\s*?\/?>$/g, '');
				code = code.replace(/\s+$/g, '');
				PHPCOMCODE['code'][PHPCOMCODE['cnum']] = "[code]\r\n" + code + "\r\n[/code]";
				return "[____PHPCOM_BLOCKCODE_" + PHPCOMCODE['cnum'] + "____]";
			});
			rep(/<pre\s+class=\"highlightStyle (\w+)\">/ig, '<pre class="brush: $1;">');
			rep(/<pre\s+class=\"(applescript|actionscript3|as3|coldfusion|cf|cpp|c|csharp|c-sharp|c#|css|delphi|pascal|pas|diff|patch|erlang|erl)\">/ig, '<pre class="brush: $1;">');
			rep(/<pre\s+class=\"(groovy|java|javafx|jfx|js|jscript|javascript|perl|pl|text|plain|php|powershell|ps|python|py|ruby|rails|ror|rb)\">/ig, '<pre class="brush: $1;">');
			rep(/<pre\s+class=\"(sass|scss|scala|sql|vbnet|vb|xml|xhtml|xslt|html)\">/ig, '<pre class="brush: $1;">');
			rep(/<pre\s+class=\"brush:\s*(\w+);\">([\s\S]+?)<\/pre>/ig, function(all, attr, code) {
				PHPCOMCODE['hnum']++;
				code = code.replace(/\$/g, "$$$$");
				code = code.replace(/( &nbsp; &nbsp; &nbsp; &nbsp;)/ig, "\t");
				code = code.replace(/&nbsp;/ig, " ");
				code = code.replace(/<br\s*?\/?>/ig, "\n");
				code = code.replace(/<p(?: [^>]+)?>([\s\S]+?)<\/p>/ig, "$1\r\n");
				code = code.replace(/<div(?: [^>]+)?>([\s\S]+?)<\/p>/ig, "$1\r\n");
				code = code.replace(/<[^<>]+?>/ig, '');
				code = code.replace(/<br\s*?\/?>$/g, '');
				code = code.replace(/\s+$/g, '');
				PHPCOMCODE['hcode'][PHPCOMCODE['hnum']] = "[code=" + attr + "]\r\n" + code + "\r\n[/code]";
				return "[____PHPCOM_HIGHLIGHTCODE_" + PHPCOMCODE['hnum'] + "____]";
			});
			rep(/<pre\s+class=\"htmlcodeStyle\">([\s\S]+?)<\/pre>/ig, function(all, code) {
				PHPCOMCODE['count']++;
				code = code.replace(/\$/g, "$$$$");
				code = code.replace(/( &nbsp; &nbsp; &nbsp; &nbsp;)/ig, "\t");
				code = code.replace(/&nbsp;/ig, ' ');
				code = code.replace(/<br\s*?\/?>/ig, "\r\n");
				code = code.replace(/\s+$/g, '');
				PHPCOMCODE['html'][PHPCOMCODE['count']] = "[html]\r\n" + code + "\r\n[/html]";
				return "[____PHPCOM_HTMLCODE_" + PHPCOMCODE['count'] + "____]";
			});
			rep(/<(\/?)(b|u|i|s)(\s+[^>]+)?>/ig, '[$1$2]');
			rep(/<(\/?)strong(\s+[^>]+)?>/ig, '[$1b]');
			rep(/<(\/?)em(\s+[^>]+)?>/ig, '[$1i]');
			rep(/<font color=\"([#\w]+?)\">(.*?)<\/font>/ig, '[color=$1]$2[/color]');
			rep(/<font size=\"(\d{1,2}?)\">(.*?)<\/font>/ig, '[size=$1]$2[/size]');
			rep(/<font face=\"([^\"\<]+?)\">(.*?)<\/font>/ig, '[font=$1]$2[/font]');
			//rep(/<p style=\"padding-left:\s+([^\"\<]+?)\">(.*?)<\/p>/ig, '[indent]$2[/indent]');
			var matches = null, re = null, bbendtag;
			var fCount = 0;
			while (s.match(/<span(?:\s+[^>]+)?\s+style=\"([^\"\<]+?)\">(.*?)<\/span>/gi)) {
				fCount++;
				if (fCount >= 200) break;
				rep(/<span\s+style=\"([^\"\<]+?)\">(.*?)<\/span>/gi, function(all, attr, text) {
					bbendtag = '';
					attr = attr.replace(/\'/g, "");
					re = /background-color\s*:\s*(.*?);/i;
					matches = re.exec(attr);
					if(matches != null){
						text = '[bgcolor=' + matches[1] + ']' + text;
						attr = attr.replace(/background-color\s*:\s*(.*?);/ig, '');
						bbendtag += '[/bgcolor]';
					}
					re = /font-family\s*:\s*(.*?);/i;
					matches = re.exec(attr);
					if(matches != null){
						text = '[font=' + matches[1] + ']' + text;
						bbendtag += '[/font]';
					}
					re = /color\s*:\s*(.*?);/i;
					matches = re.exec(attr);
					if(matches != null){
						text = '[color=' + matches[1] + ']' + text;
						bbendtag += '[/color]';
					}
					re = /font-size\s*:\s*(.*?);/i;
					matches = re.exec(attr);
					if(matches != null){
						var size = mapSize[matches[1].toLowerCase()];
						if(size) { 
							text = '[size=' + size + ']' + text;
							bbendtag += '[/size]';
						}
					}
					if (attr.match(/^font-style\s*:\s*italic\s*;/i)){
						text = '[i]' + text;
						bbendtag += '[/i]';
					}
					if (attr.match(/^text-decoration\s*:\s*underline\s*;/i)){
						text = '[u]' + text;
						bbendtag += '[/u]';
					}
					if (attr.match(/^text-decoration\s*:\s*line-through\s*;/i)){
						text = '[s]' + text;
						bbendtag += '[/s]';
					}
					if (attr.match(/^font-weight\s*:\s*(bold|700)\s*;/i)){
						text = '[b]' + text;
						bbendtag += '[/b]';
					}
					return text + bbendtag;
				});
			}
			rep(/<a(?:\s+[^>]+)?\s+href=\"\s*([^\"]+?)\s*\"[^>]*>([\s\S]+?)<\/a>/ig, function(all, url, text) {
				var tag = 'url', str;
				if (url.match(/^mailto:/i)) {
					tag = 'email';
					url = url.replace(/mailto:(.+?)/i, '$1');
				}
				str = '[' + tag;
				if (url != text) str += '=' + url;
				return str + ']' + text + '[/' + tag + ']';
			});
			rep(/<img(\s+[^>]+?)\/?>(?:\s*<\/img>)?/ig, function(all, attr) {
				var url = attr.match(/\s+src=\"([^\"]+?)\"/i);
				var w = attr.match(/width\s*[=:]\s*\"?(\d+)/i);
				var h = attr.match(/height\s*[=:]\s*\"?(\d+)/i);
				var p = attr.match(/\s+class=\"([^\"]+?)\"/i);
				var aid = attr.match(/\s+id=\"editor_attachimg_(\d+)\"/i);
				//var a = attr.match(/\s+alt=\"([^\"]+?)\"/i);
				if (!url) return "";
				if (attr.indexOf('class="mcePageBreak') != -1 && url[1].indexOf('trans.gif') != -1) return all;
				if(aid){
					return '[attachimg]'+aid[1]+'[/attachimg]'
				}else{
					var str = '[img';
					//if (a) str += '=' + a[1].replace(/(\[|\])/g, ' ');
					//else if (w && h) str += '=' + w[1] + ',' + h[1];
					if (w && h) str += '=' + w[1] + ',' + h[1];
					str += ']' + url[1];
					return str + '[/img]';
				}
			});
			rep(/<blockquote[^>]*>([\s\S]+?)<\/blockquote>/ig, "[quote]$1[/quote]");
			rep(/<span\s+class=\"codeStyle\">([\s\S]+?)<\/span>/gi, "[code]$1[/code]");
			rep(/<span\s+class=\"quoteStyle\">([\s\S]+?)<\/span>/gi, "[quote]$1[/quote]");
			rep(/<em\s+class=\"codeStyle\">([\s\S]+?)<\/em>/gi, "[code][i]$1[/i][/code]");
			rep(/<em\s+class=\"quoteStyle\">([\s\S]+?)<\/em>/gi, "[quote][i]$1[/i][/quote]");
			rep(/<span>([\s\S]+?)<\/span>/gi, "$1");
			rep(/<ignore_js_op>([\s\S]+?)<\/ignore_js_op>/gi, "$1");
			rep(/(&lt;ignore_js_op&gt;|&lt;\/ignore_js_op&gt;)/gi, "");
			rep(/\[size=2\]([\s\S]+?)\[\/size\]/gi, "$1");
			rep(/<p\s+align=\"(left|center|right|justify)\">(.*?)<\/p>/ig, "[align=$1]$2[/align]");
			rep(/<div\s+align=\"(left|center|right|justify)\">(.*?)<\/div>/ig, "[align=$1]$2[/align]");
			rep(/<p(?:\s+[^>]+)?\s+style=\"text-align\s*:\s*(left|center|right|justify);?\">(.*?)<\/p>/ig, "[align=$1]$2[/align]");
			rep(/<div(?:\s+[^>]+)?\s+style=\"text-align\s*:\s*(left|center|right|justify);?\">(.*?)<\/div>/ig, "[align=$1]$2[/align]");
			//rep(/(\r|\n)/g, '');
			//rep(/<br\s*?\/?>$/g, "\n");
			rep(/((&nbsp;){8,8}|( &nbsp;){4,4}|(&nbsp; ){4,4})/g, "\t");
			rep(/&nbsp;/ig, " ");
			//rep(/^\s+/g, '');
			rep(/\s+$/g, '');
			for(i = 0; i <= PHPCOMCODE['count']; i++) {
				rep("[____PHPCOM_HTMLCODE_" + i + "____]", PHPCOMCODE['html'][i]);
			}
			for(i = 0; i <= PHPCOMCODE['hnum']; i++) {
				rep("[____PHPCOM_HIGHLIGHTCODE_" + i + "____]", PHPCOMCODE['hcode'][i]);
			}
			for(i = 0; i <= PHPCOMCODE['cnum']; i++) {
				rep("[____PHPCOM_BLOCKCODE_" + i + "____]", PHPCOMCODE['code'][i]);
			}
			rep(/^<p> <\/p>/g, '');
			rep(/<p> <\/p>$/g, '');
			rep(/\0/g, '');
			return s;
		},
		
		// BBCode -> HTML
		_punubb_bbcode2html: function(s) {
			var i = 0;
			s = tinymce.trim(s);

			function rep(re, str) {
				s = s.replace(re, str);
			};
			function clearcode(str) {
				str= str.replace(/\[url\]\[\/url\]/ig, '', str);
				str= str.replace(/\[url=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.|mailto:)?([^\s\[\"']+?)\]\[\/url\]/ig, '', str);
				str= str.replace(/\[email\]\[\/email\]/ig, '', str);
				str= str.replace(/\[email=(.[^\[]*)\]\[\/email\]/ig, '', str);
				str= str.replace(/\[color=([^\[\<]+?)\]\[\/color\]/ig, '', str);
				str= str.replace(/\[size=(\d+?)\]\[\/size\]/ig, '', str);
				str= str.replace(/\[size=(\d+(\.\d+)?(px|pt)+?)\]\[\/size\]/ig, '', str);
				str= str.replace(/\[font=([^\[\<]+?)\]\[\/font\]/ig, '', str);
				str= str.replace(/\[align=([^\[\<]+?)\]\[\/align\]/ig, '', str);
				str= str.replace(/\[p=(\d{1,2}), (\d{1,2}), (left|center|right)\]\[\/p\]/ig, '', str);
				str= str.replace(/\[float=([^\[\<]+?)\]\[\/float\]/ig, '', str);
				str= str.replace(/\[quote\]\[\/quote\]/ig, '', str);
				str= str.replace(/\[code\]\[\/code\]/ig, '', str);
				str= str.replace(/\[table\]\[\/table\]/ig, '', str);
				str= str.replace(/\[free\]\[\/free\]/ig, '', str);
				str= str.replace(/\[b\]\[\/b]/ig, '', str);
				str= str.replace(/\[u\]\[\/u]/ig, '', str);
				str= str.replace(/\[i\]\[\/i]/ig, '', str);
				str= str.replace(/\[s\]\[\/s]/ig, '', str);
				return str;
			};
			PHPCOMCODE['count'] = -1;
			PHPCOMCODE['html'] = [];
			PHPCOMCODE['cnum'] = -1;
			PHPCOMCODE['code'] = [];
			PHPCOMCODE['hnum'] = -1;
			PHPCOMCODE['hcode'] = [];
			s = clearcode(s);
			rep(/\[code\]([\s\S]*?)\[\/code\]/ig, function(all, code) {
				PHPCOMCODE['cnum']++;
				code = code.replace(/\$/g, "$$$$");
				code = code.replace(/<br\s*?\/?>$/g, "\r\n");
				code = code.replace(/<[^<>]+?>/g, '');
				code = code.replace(/^(<br\s*?\/?>)?(.*)/ig, '$2');
				PHPCOMCODE['code'][PHPCOMCODE['cnum']] = '<pre class="blockcodeStyle">' + code + '</pre>';
				return "[____PHPCOM_BLOCKCODE_" + PHPCOMCODE['cnum'] + "____]";
			});
			rep(/\[code=(\w+)\]([\s\S]*?)\[\/code\]/ig, function(all, attr, code) {
				PHPCOMCODE['hnum']++;
				code = code.replace(/\$/g, "$$$$");
				code = code.replace(/<br\s*?\/?>$/g, "\r\n");
				code = code.replace(/<[^<>]+?>/g, '');
				code = code.replace(/^(<br\s*?\/?>)?(.*)/ig, '$2');
				code = code.replace(/\s+$/g, '');
				PHPCOMCODE['hcode'][PHPCOMCODE['hnum']] = '<pre class="highlightStyle ' + attr + '">' + code + '</pre>';
				return "[____PHPCOM_HIGHLIGHTCODE_" + PHPCOMCODE['hnum'] + "____]";
			});
			rep(/\[html\]([\s\S]*?)\[\/html\]/ig, function(all, code) {
				PHPCOMCODE['count']++;
				code = code.replace(/\$/g, "$$$$");
				code = code.replace(/( &nbsp; &nbsp; &nbsp; &nbsp;)/ig, "\t");
				code = code.replace(/&nbsp;/ig, ' ');
				code = code.replace(/<br\s*?\/?>/ig, "\r\n");
				code = code.replace(/<!--\s+pagebreak\s+-->/ig, '');
				code = code.replace(/<p(?: [^>]+)?>([\s\S]+?)<\/p>/ig, "$1\r\n");
				code = code.replace(/<div(?: [^>]+)?>([\s\S]+?)<\/p>/ig, "$1\r\n");
				code = code.replace(/<code(?: [^>]+)?>([\s\S]+?)<\/code>/ig, '$1');
				code = code.replace(/<[^<>]+?>/g, '');
				code = code.replace(/\s+$/g, '');
				PHPCOMCODE['html'][PHPCOMCODE['count']] = '<pre class="htmlcodeStyle">' + code + '</pre>';
				return "[____PHPCOM_HTMLCODE_" + PHPCOMCODE['count'] + "____]";
			});
			rep(/\[(\/?)(b|u|i|s|em|sup|sub)\]/ig, '<$1$2>');
			rep(/\[\/(size|font|color)\]/ig, '</font>');
			rep(/\[\/bgcolor\]/ig, '</span>');
			rep(/\[size=([^\]]+?)\]/ig, '<font size="$1">');
			rep(/\[color=([^\]]+?)\]/ig, '<font color="$1">');
			rep(/\[font=([^\]]+?)\]/ig, '<font face="$1">');
			rep(/\[bgcolor=([^\]]+?)\]/ig, '<span style="background-color: $1;">');
			rep(/\[size=([^\]]+?)\]/ig, function(all, attr) {
				var size = mapSize[attr.toLowerCase()];
				if (!size)  size = 2;
				return '<font size="' + size + '">';
			});
			rep(/\[\/align\]/ig, '</p>');
			rep(/\[align\s*=\s*(left|center|right|justify)\]/ig, '<p style="text-align: $1;">');
			rep(/\[attachimg\]\s*(\d+)\s*\[\/attachimg\]/ig, function(all, aid) {
				var obj = $('uploadimage_' + aid);
				if(!obj) return '';
				return '<img width="300" src="'+obj.src.replace(/&thumb=yes/ig, '')+'" id="editor_attachimg_'+aid+'" style="max-width:600px" />';
			});
			rep(/\[img\]\s*([\s\S]+?)\s*\[\/img\]/ig, '<img src="$1" />');
			rep(/\[img\s*=\s*(\d+),(\d+)\s*\]\s*([\s\S]+?)\s*\[\/img\]/ig, '<img src="$3" width="$1" height="$2" />');
			//rep(/\[img\s*=\s*([^\]<>]+?)\s*\]\s*([\s\S]+?)\s*\[\/img\]/ig, '<img alt="$1" src="$2" border="0" />');
			rep(/\[url\]\s*([\s\S]+?)\s*\[\/url\]/ig, '<a href="$1" target="_blank">$1</a>');
			rep(/\[url\s*=\s*([^\]\s]+?)\s*\]\s*([\s\S]+?)\s*\[\/url\]/ig, '<a href="$1" target="_blank">$2</a>');
			rep(/\[email\]\s*([\s\S]+?)\s*\[\/email\]/ig, '<a href="mailto:$1">$1</a>');
			rep(/\[email\s*=\s*([^\]\s]+?)\s*\]\s*([\s\S]+?)\s*\[\/email\]/ig, '<a href="mailto:$1">$2</a>');
			rep(/\[quote\]([\s\S]*?)\[\/quote\]/ig, '<blockquote class="blockquoteStyle">$1</blockquote>');
			rep(/\[indent\]([\s\S]*?)\[\/indent\]/ig, '<p style="padding-left: 30px;">$1</p>');
			rep(/<ignore_js_op>([\s\S]+?)<\/ignore_js_op>/gi, "$1");
			rep(/(&lt;ignore_js_op&gt;|&lt;\/ignore_js_op&gt;)/gi, "");
			rep(/<div\s+style\s*=\s*\"page-break-after\s*:\s*always\s*?(;)?\s*\"><span\s+style\s*=\s*\"display\s*:\s*none\s*?(;)?\s*\">(.*?)<\/span><\/div>/ig, '<!-- pagebreak -->');
			rep(/\[page_break\]/ig, '<!-- pagebreak -->');
			rep(/\[pagebreak\]/ig, '<!-- pagebreak -->');
			rep(/\t/g, ' &nbsp; &nbsp; &nbsp; &nbsp;');
			rep(/'  '/g, ' &nbsp;');
			//rep(/\<p>([\s\S]+?)<\/p>/ig, function(all, text) {
			//	return '<p>'+tinymce.trim(text)+'</p>';
			//});
			//rep(/<[^<>]+?>/ig, function(code) {return code.replace(/&nbsp;/ig, ' ');});
			for(i = 0; i <= PHPCOMCODE['count']; i++) {
				rep("\[____PHPCOM_HTMLCODE_" + i + "____\]", PHPCOMCODE['html'][i]);
			}
			for(i = 0; i <= PHPCOMCODE['hnum']; i++) {
				rep("\[____PHPCOM_HIGHLIGHTCODE_" + i + "____\]", PHPCOMCODE['hcode'][i]);
			}
			for(i = 0; i <= PHPCOMCODE['cnum']; i++) {
				rep("\[____PHPCOM_BLOCKCODE_" + i + "____\]", PHPCOMCODE['code'][i]);
			}
			rep(/^<p> <\/p>/g, '');
			rep(/<p> <\/p>$/g, '');
			rep(/\0/g, '');
			s = clearcode(s);
			return s + '<p>&nbsp;</p>';
		}
	});

	tinymce.PluginManager.add("blockcode", tinymce.plugins.BlockCodePlugin)
})();