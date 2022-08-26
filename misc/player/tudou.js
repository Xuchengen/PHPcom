function showPlayerHtml(){
	var html = '<object id="TDPlayer" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" bgcolor="#000000" width="100%" height="'+player.height+'">';
	html += '<param name="movie" value="http://js.tudouui.com/bin/douwan/douwanPlayer_3.swf?iid='+player.url+'&amp;autostart=false&amp;autoPlay=false" />';
	html += '<param name="quality" value="high" />';
	html += '<param name="allowfullscreen" value="true" />';
	html += '<param name="allowScriptAccess" value="never" />';
	html += '<param name="allowNetworking" value="internal" />';
	html += '<embed src="http://js.tudouui.com/bin/douwan/douwanPlayer_3.swf?iid='+player.url+'&amp;autostart=false&amp;autoPlay=false" width="100%" height="'+player.height+'" ';
	html += 'bgcolor="#000" allowfullscreen="true" allowScriptAccess="never" allowNetworking="internal" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>';
	html += '</object>';
	return html;
}

function showPlayerCode(){
	var url = 'http://js.tudouui.com/bin/player2/olc_8.swf?iid='+player.url+'&snap_pic=';
	var html = '<object data="'+url+'" type="application/x-shockwave-flash" width="100%" height="'+player.height+'">';
	html += '<param name="data" value="'+url+'" />';
	html += '<param name="src" value="'+url+'" />';
	html += '<param name="wmode" value="opaque" />';
	html += '<param name="allowfullscreen" value="true" />';
	html += '</object>';
	return html;
}

player.html = showPlayerCode();

player.show(showPlayerHtml());