function showPlayerHtml(){
	var html ='<object classid="clsid:D27CDB6E-AE6D-11CF-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" border="0" width="100%" height="'+player.height+'" >';
	html += '<param name="movie" value="'+player.url+'" />';
	html += '<param name="quality" value="High" />';
	html += '<param name="wmode" value="Opaque" />';
	html += '<param name="allowfullscreen" value="true" />';
	html += '<embed src="'+player.url+'" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" quality="High" wmode="Opaque" allowscriptaccess="always" width="100%" height="'+player.height+'" />';
	html += '</object>';	
	return html;
}

player.html = showPlayerHtml();

player.show(showPlayerHtml());