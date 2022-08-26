function showPlayerHtml(){
	var html = '<object id="player" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" bgcolor="#000000" width="100%" height="'+player.height+'">';
	html += '<param name="movie" value="http://static.youku.com/v1.0.0242/v/swf/qplayer.swf" />';
	html += '<param name="flashvars" value="isShowRelatedVideo=false&showAd=0&show_pre=1&show_next=1&VideoIDS='+player.url+'&isAutoPlay=true&isDebug=false&UserID=&winType=interior&playMovie=true&MMControl=false&MMout=false&RecordCode=1001,1002,1003,1004,1005,1006,2001,3001,3002,3003,3004,3005,3007,3008,9999" />';
	html += '<param name="quality" value="high" />';
	html += '<param name="allowfullscreen" value="true" />';
	html += '<embed src="http://static.youku.com/v1.0.0242/v/swf/qplayer.swf" id="movie_player" name="movie_player" bgcolor="#000000" quality="high" allowfullscreen="true" ';
	html += 'flashvars="isShowRelatedVideo=false&showAd=0&show_pre=1&show_next=1&VideoIDS='+player.url+'&isAutoPlay=true&isDebug=false&UserID=&winType=interior&playMovie=true&MMControl=false&MMout=false&RecordCode=1001,1002,1003,1004,1005,1006,2001,3001,3002,3003,3004,3005,3007,3008,9999" ';
	html += 'type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" width="100%" height="'+player.height+'" />';
	html += '</object>';
	return html;
}

player.html = '<object type="application/x-shockwave-flash" data="http://player.youku.com/player.php/sid/'+player.url+'/v.swf"  width="100%" height="'+player.height+'" wmode="transparent" allowfullscreen="true" quality="high">';
player.html += '<param name="movie" value="http://player.youku.com/player.php/sid/'+player.url+'=/v.swf" />';
player.html += '<param name="wmode" value="transparent" />';
player.html += '<param name="quality" value="high" />';
player.html += '<param name="allowfullscreen" value="true" />';
player.html += '</object>';

player.show(showPlayerHtml());