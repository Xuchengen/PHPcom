
function showPlayerHtml(){
	var html = '<object id="player" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" bgcolor="#000000" width="100%" height="'+player.height+'">';
	html += '<param name="movie" value="http://cache.tv.qq.com/QQPlayer.swf" />';
	html += '<param name="flashvars" value="vid='+player.url+'&amp;skin=http://imgcache.qq.com/minivideo_v1/vd/res/skins/QQPlayerSkin.swf&amp;autoplay=1&amp;gourl=http://video.qq.com/v1/videopl?v=&amp;list=1&amp;" />';
	html += '<param name="quality" value="high" />';
	html += '<param name="wmode" value="opaque" />';
	html += '<param name="allowfullscreen" value="true" />';
	html += '<param name="allowscriptaccess" value="always" />';
	html += '<embed src="http://cache.tv.qq.com/QQPlayer.swf" height="'+player.height+'" width="100%" ';
	html += 'flashvars="vid='+player.url+'&amp;skin=http://imgcache.qq.com/minivideo_v1/vd/res/skins/QQPlayerSkin.swf&amp;autoplay=1&amp;gourl=http://video.qq.com/v1/videopl?v=&amp;list=1&amp;" ';
	html += 'allowfullscreen="true" wmode="Opaque" allowscriptaccess="always" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>';
	html += '</object>';
	return html;
}

player.html = '<object type="application/x-shockwave-flash" data="http://static.video.qq.com/TPout.swf?vid='+player.url+'&auto=0" bgcolor="#000000" width="100%" height="'+player.height+'" wmode="transparent" allowfullscreen="true" quality="high">';
player.html += '<param name="movie" value="http://static.video.qq.com/TPout.swf?vid='+player.url+'&auto=0" />';
player.html += '<param name="wmode" value="transparent" />';
player.html += '<param name="quality" value="high" />';
player.html += '<param name="allowfullscreen" value="true" />';
player.html += '</object>';


player.show(showPlayerHtml());
