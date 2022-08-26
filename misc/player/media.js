function showPlayerHtml(){
	var html = '<object classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="100%" height="'+player.height+'" id="mdediaplayer">';
	html += '<param name="URL" value="'+player.url+'" />';
	html += '<param name="stretchToFit" value="-1" />';
	html += '<embed filename="'+player.url+'" ShowStatusBar="1" type="application/x-mplayer2" width="100%" height="'+player.height+'" />';
	html += '</object>';
	html += '<div align="right" style="margin-right:30px;margin-top:-30px"><input style="padding:2px 5px;" type="submit" value="È«ÆÁ¹Û¿´" onclick="mediaFullScreen()" /></div>';	
	return html;
}

function mediaFullScreen(){
     if(mdediaplayer.playstate==3){
     	mdediaplayer.fullScreen=true;
	 }
}

player.html = '<object classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="100%" height="'+player.height+'" id="mediaplayer">';
player.html += '<param name="URL" value="'+player.url+'" />';
player.html += '<param name="stretchToFit" value="-1" />';
player.html += '<param name="autoStart" value="0" />';
player.html += '<embed filename="'+player.url+'" ShowStatusBar="1" type="application/x-mplayer2" width="100%" height="'+player.height+'" />';
player.html += '</object>';

player.show(showPlayerHtml());