
function showPlayerHtml(){
	var html = '<object id="pptvplayer" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" bgcolor="#000000" width="100%" height="'+player.height+'">';
	html += '<param name="movie" value="http://player.pptv.com/v/'+player.url+'.swf" />';
	html += '<param name="quality" value="high" />';
	html += '<param name="wmode" value="window" />';
	html += '<param name="allowfullscreen" value="true" />';
	html += '<param name="allowscriptaccess" value="always" />';
	html += '<param name="allownetworking" value="all" />';
	html += '<param name="play" value="true" />';
	html += '<embed src="http://player.pptv.com/v/'+player.url+'.swf" width="100%" height="'+player.height+'" ';
	html += 'allowfullscreen="true" wmode="window" allowscriptaccess="always" allownetworking="all" play="true" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>';
	html += '</object>';
	return html;
}

player.html = showPlayerHtml();

player.show(showPlayerHtml());