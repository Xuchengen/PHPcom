function showPlayerHtml(){
	var html = '<object id="sohuplayer" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" bgcolor="#000000" width="100%" height="'+player.height+'">';
	html += '<param name="movie" value="http://tv.sohu.com/upload/swf/20120823/Main.swf?autoplay=true&vid='+player.url+'" />';
	html += '<param name="quality" value="high" />';
	html += '<param name="wmode" value="opaque" />';
	html += '<param name="allowfullscreen" value="true" />';
	html += '<param name="allownetworking" value="internal" />';
	html += '<param name="allowscriptaccess" value="never" />';
	html += '<embed src="http://tv.sohu.com/upload/swf/20120823/Main.swf?autoplay=true&vid='+player.url+'" width="100%" height="'+player.height+'" ';
	html += 'bgcolor="#000" allowfullscreen="true" allownetworking="internal" allowscriptaccess="never" wmode="opaque" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>';
	html += '</object>';
	return html;
}

player.html = '<object id="sohuplayer" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" bgcolor="#000000" width="100%" height="'+player.height+'">';
player.html += '<param name="movie" value="http://tv.sohu.com/upload/swf/20120823/Main.swf?autoplay=0&vid='+player.url+'" />';
player.html += '<param name="quality" value="high" />';
player.html += '<param name="wmode" value="opaque" />';
player.html += '<param name="allowfullscreen" value="true" />';
player.html += '<param name="allownetworking" value="internal" />';
player.html += '<param name="allowscriptaccess" value="never" />';
player.html += '<embed src="http://tv.sohu.com/upload/swf/20120823/Main.swf?autoplay=0&vid='+player.url+'" width="100%" height="'+player.height+'" ';
player.html += 'bgcolor="#000" allowfullscreen="true" allownetworking="internal" allowscriptaccess="never" wmode="opaque" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>';
player.html += '</object>';

player.show(showPlayerHtml());