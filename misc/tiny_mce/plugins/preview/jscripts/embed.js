/**
 * This script contains embed functions for common plugins. This scripts are complety free to use for any purpose.
 */

function writeFlash(p) {
	writeEmbed(
		'D27CDB6E-AE6D-11cf-96B8-444553540000',
		'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0',
		'application/x-shockwave-flash',
		p
	);
}

function writeShockWave(p) {
	writeEmbed(
	'166B1BCA-3F9C-11CF-8075-444553540000',
	'http://download.macromedia.com/pub/shockwave/cabs/director/sw.cab#version=8,5,1,0',
	'application/x-director',
		p
	);
}

function writeQuickTime(p) {
	writeEmbed(
		'02BF25D5-8C17-4B23-BC80-D3488ABDDC6B',
		'http://www.apple.com/qtactivex/qtplugin.cab#version=6,0,2,0',
		'video/quicktime',
		p
	);
}

function writeRealMedia(p) {
	writeEmbed(
		'CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA',
		'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0',
		'audio/x-pn-realaudio-plugin',
		p
	);
}

function writeWindowsMedia(p) {
	p.url = p.src;
	writeEmbed(
		'6BF52A52-394A-11D3-B153-00C04F79FAA6',
		'http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701',
		'application/x-mplayer2',
		p
	);
}

function writeEmbed(cls, cb, mt, p) {
	var h = '', n;

	h += '<object classid="clsid:' + cls + '" codebase="' + cb + '"';
	h += typeof(p.id) != "undefined" ? 'id="' + p.id + '"' : '';
	h += typeof(p.name) != "undefined" ? 'name="' + p.name + '"' : '';
	h += typeof(p.width) != "undefined" ? 'width="' + p.width + '"' : '';
	h += typeof(p.height) != "undefined" ? 'height="' + p.height + '"' : '';
	h += typeof(p.align) != "undefined" ? 'align="' + p.align + '"' : '';
	h += '>';

	for (n in p)
		h += '<param name="' + n + '" value="' + p[n] + '">';

	h += '<embed type="' + mt + '"';

	for (n in p)
		h += n + '="' + p[n] + '" ';

	h += '></embed></object>';

	document.write(h);
}

var PHPCOMCODE = [];
function bbcode2Html(s) {
	var i, mapSize = { 'xx-small': 1, '8pt': 1, 'x-small': 2, '10pt': 2, 'small': 3, '12pt': 3, 'medium': 4, '14pt': 4, 'large': 5, '18pt': 5, 'x-large': 6, '24pt': 6, 'xx-large': 7, '36pt': 7 };
	s = s.replace(/(<!--\[CDATA\[|\]\]-->)/g, '\n');
	s = s.replace(/^[\r\n]*|[\r\n]*$/g, '');
	s = s.replace(/^\s*(\/\/\s*<!--|\/\/\s*<!\[CDATA\[|<!--|<!\[CDATA\[)[\r\n]*/g, '');
	s = s.replace(/\s*(\/\/\s*\]\]>|\/\/\s*-->|\]\]>|-->|\]\]-->)\s*$/g, '');

	function rep(re, str) {
		s = s.replace(re, str);
	};
	
	PHPCOMCODE['count'] = -1;
	PHPCOMCODE['html'] = [];
	PHPCOMCODE['cnum'] = -1;
	PHPCOMCODE['code'] = [];
	PHPCOMCODE['hnum'] = -1;
	PHPCOMCODE['hcode'] = [];
	rep(/\[code\]([\s\S]*?)\[\/code\]/ig, function(all, code) {
		PHPCOMCODE['cnum']++;
		code = code.replace(/\$/g, "$$$$");
		code = code.replace(/(\r\n|\n|\r)/ig, '<br />');
		code = code.replace(/<!--\s*pagebreak\s*-->/ig, '');
		code = code.replace(/\t/ig, ' &nbsp; &nbsp; &nbsp; &nbsp;');
		code = code.replace(/'   '/ig, '&nbsp; &nbsp; &nbsp;');
		code = code.replace(/'  '/ig, '&nbsp;&nbsp;');
		code = code.replace(/^(<br\s*?\/?>)?(.+?)(<br\s*?\/?>)$/, '$2');
		code = code.replace(/<br\s*?\/?>/ig, '</li><li>');
		code = code.replace(/^(<br\s*?\/?>)?(.*)/ig, '$2');
		PHPCOMCODE['code'][PHPCOMCODE['cnum']] = '<div class="blockcodeStyle"><ol><li>' + code + '</li></ol></div>';
		return "[____PHPCOM_BLOCKCODE_" + PHPCOMCODE['cnum'] + "____]";
	});
	rep(/\[code=(\w+)\]([\s\S]*?)\[\/code\]/ig, function(all, attr, code) {
		PHPCOMCODE['hnum']++;
		code = code.replace(/\$/g, "$$$$");
		code = code.replace(/( &nbsp; &nbsp; &nbsp; &nbsp;)/ig, "\t");
		code = code.replace(/&nbsp;/ig, ' ');
		code = code.replace(/<br\s*?\/?>$/g, "\r\n");
		code = code.replace(/<!--\s*pagebreak\s*-->/ig, '');
		code = code.replace(/<[^<>]+?>/g, '');
		code = code.replace(/^(<br\s*?\/?>)?(.*)/ig, '$2');
		PHPCOMCODE['hcode'][PHPCOMCODE['hnum']] = '<pre class="brush: ' + attr + ';">' + code + '</pre>';
		return "[____PHPCOM_HIGHLIGHTCODE_" + PHPCOMCODE['hnum'] + "____]";
	});
	rep(/\[html\]([\s\S]*?)\[\/html\]/ig, function(all, code) {
		PHPCOMCODE['count']++;
		code = code.replace(/\$/g, "$$$$");
		code = code.replace(/( &nbsp; &nbsp; &nbsp; &nbsp;)/ig, "\t");
		code = code.replace(/&nbsp;/ig, ' ');
		code = code.replace(/<br\s*?\/?>/ig, "\r\n");
		code = code.replace(/<!--\s*pagebreak\s*-->/ig, '');
		code = code.replace(/<p(?: [^>]+)?>([\s\S]+?)<\/p>/ig, "$1\r\n");
		code = code.replace(/<div(?: [^>]+)?>([\s\S]+?)<\/p>/ig, "$1\r\n");
		code = code.replace(/<code(?: [^>]+)?>([\s\S]+?)<\/code>/ig, '$1');
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
	
	for (i = 0; i < 3; i++) rep(/\[align\s*=\s*([^\]]+?)\](((?!\[align(?:\s+[^\]]+)?\])[\s\S])*?)\[\/align\]/ig, '<p style="text-align: $1;">$2</p>');

	rep(/\[img\]\s*([\s\S]+?)\s*\[\/img\]/ig, '<img src="$1" border="0" />');
	rep(/\[img\s*=\s*(\d+),(\d+)\s*\]\s*([\s\S]+?)\s*\[\/img\]/ig, '<img src="$3" width="$1" height="$2" border="0" />');
	//rep(/\[img\s*=\s*([^\]<>]+?)\s*\]\s*([\s\S]+?)\s*\[\/img\]/ig, '<img alt="$1" src="$2" border="0" />');
	rep(/\[url\]\s*([\s\S]+?)\s*\[\/url\]/ig, '<a href="$1" target="_blank">$1</a>');
	rep(/\[url\s*=\s*([^\]\s]+?)\s*\]\s*([\s\S]+?)\s*\[\/url\]/ig, '<a href="$1" target="_blank">$2</a>');
	rep(/\[email\]\s*([\s\S]+?)\s*\[\/email\]/ig, '<a href="mailto:$1">$1</a>');
	rep(/\[email\s*=\s*([^\]\s]+?)\s*\]\s*([\s\S]+?)\s*\[\/email\]/ig, '<a href="mailto:$1">$2</a>');
	rep(/\[quote\]([\s\S]*?)\[\/quote\]/ig, '<blockquote class="blockquoteStyle">$1</blockquote>');
	rep(/\[indent\]([\s\S]*?)\[\/indent\]/ig, '<p style="padding-left: 30px;">$1</p>');
	rep(/<div\s+style\s*=\s*\"page-break-after\s*:\s*always\s*?(;)?\s*\"><span\s+style\s*=\s*\"display\s*:\s*none\s*?(;)?\s*\">(.*?)<\/span><\/div>/ig, '<!-- pagebreak -->');
	rep(/\[page_break\]/ig, '<!-- pagebreak -->');
	rep(/\t/g, '&nbsp; &nbsp; &nbsp; &nbsp; ');
	rep(/'   '/g, '&nbsp; &nbsp;');
	rep(/'  '/g, '&nbsp;&nbsp;');
	for(i = 0; i <= PHPCOMCODE['count']; i++) {
		rep("[____PHPCOM_HTMLCODE_" + i + "____]", PHPCOMCODE['html'][i]);
	}
	for(i = 0; i <= PHPCOMCODE['hnum']; i++) {
		rep("[____PHPCOM_HIGHLIGHTCODE_" + i + "____]", PHPCOMCODE['hcode'][i]);
	}
	for(i = 0; i <= PHPCOMCODE['cnum']; i++) {
		rep("[____PHPCOM_BLOCKCODE_" + i + "____]", PHPCOMCODE['code'][i]);
	}
	rep(/^<p>&nbsp;<\/p>/g, '');
	rep(/<p>&nbsp;<\/p>$/g, '');
	rep(/<div\s+style\s*=\s*\"page-break-after\s*:\s*always\s*?(;)?\s*\"><span\s+style\s*=\s*\"display\s*:\s*none\s*?(;)?\s*\">(.*?)<\/span><\/div>/ig, '<!-- pagebreak -->');
	rep(/\[page_break\]/ig, '<!-- pagebreak -->');
	rep(/\0/g, '');
	return s;
}
