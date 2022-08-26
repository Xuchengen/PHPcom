function showPlayerHtml(){
	var html = '<object id="mymovie" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" bgcolor="#000000" width="100%" height="'+player.height+'">';
	html += '<param name="movie" value="http://player.ku6cdn.com/default/out/pV2.7.3.swf" />';
	html += '<param name="flashvars" value="ver=108&amp;jump=0&amp;api=1&amp;auto=1&amp;color=0&amp;deflogo=0&amp;flag=hd&amp;adss=0&amp;vid='+player.url+'&amp;type=v" />';
	html += '<param name="quality" value="high" />';
	html += '<param name="wmode" value="transparent" />';
	html += '<param name="allowfullscreen" value="true" />';
	html += '<param name="allowscriptaccess" value="always" />';
	html += '<embed src="http://player.ku6cdn.com/default/out/pV2.7.3.swf" name="mymovie" height="'+player.height+'" width="100%" ';
	html += 'flashvars="ver=108&amp;jump=0&amp;api=1&amp;auto=1&amp;color=0&amp;deflogo=0&amp;flag=hd&amp;adss=0&amp;vid='+player.url+'&amp;type=v" ';
	html += 'allowfullscreen="true" wmode="transparent" allowscriptaccess="always" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>';
	html += '</object>';
	return html;
}

player.html = showPlayerHtml();

player.show(showPlayerHtml());