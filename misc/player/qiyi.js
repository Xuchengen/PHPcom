function showPlayerHtml(){
	var html ='<object id=flvplayer1 classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="100%" height="'+player.height+'" align="middle">';
	html +='<param name="Movie" VALUE="http://www.qiyi.com/player/20110218183154/qiyi_player.swf" />';
	html +='<param name="allowFullScreen" value="true" />';
	html +='<param name="wmode" value="transparent" />';
	html +='<param name="AllowScriptAccess" value="always" />';
	html +='<param name="quality" value="high" />';
	html +='<param name="FlashVars" value="flag=0&vid='+player.url+'" />';
	html +='<embed src="http://www.qiyi.com/player/20110218183154/qiyi_player.swf" ';
	html +='FlashVars="flag=0&vid='+player.url+'" ';
	html +='allowfullscreen="true" wmode="transparent" quality="high" bgcolor="#000" width="100%" height="'+player.height+'" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />';
	html +='</object>';
	return html;
}

player.html = showPlayerHtml();

player.show(showPlayerHtml());