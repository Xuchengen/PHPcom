function showPlayerHtml(){
	var html = '<object id="sinaplayer" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" bgcolor="#000000" width="100%" height="'+player.height+'">';
	html += '<param name="movie" value="http://vhead.blog.sina.com.cn/player/bn_topic.swf?vid='+player.url+'&clip_id=&imgurl=&auto=1&vblog=1&type=0&tabad=1" />';
	html += '<param name="quality" value="high" />';
	html += '<param name="allowfullscreen" value="true" />';
	html += '<param name="allowScriptAccess" value="sameDomain" />';
	html += '<embed src="http://vhead.blog.sina.com.cn/player/bn_topic.swf?vid='+player.url+'&clip_id=&imgurl=&auto=1&vblog=1&type=0&tabad=1" width="100%" height="'+player.height+'" ';
	html += 'bgcolor="#000" allowfullscreen="true" allowScriptAccess="sameDomain" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>';
	html += '</object>';
	return html;
}

player.html = '<object id="sinaplayer" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" bgcolor="#000000" width="100%" height="'+player.height+'">';
player.html += '<param name="movie" value="http://vhead.blog.sina.com.cn/player/bn_topic.swf?vid='+player.url+'&clip_id=&imgurl=&auto=0&vblog=1&type=0&tabad=1" />';
player.html += '<param name="quality" value="high" />';
player.html += '<param name="allowfullscreen" value="true" />';
player.html += '<param name="allowScriptAccess" value="sameDomain" />';
player.html += '<embed src="http://vhead.blog.sina.com.cn/player/bn_topic.swf?vid='+player.url+'&clip_id=&imgurl=&auto=0&vblog=1&type=0&tabad=1" width="100%" height="'+player.height+'" ';
player.html += 'bgcolor="#000" allowfullscreen="true" allowScriptAccess="sameDomain" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>';
player.html += '</object>';

player.show(showPlayerHtml());