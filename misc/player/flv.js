function showPlayerHtml(){
	var html ='<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="100%" height="'+player.height+'">';
	html +='<param name="movie" value="'+player.domain+'misc/player/flv.swf" />';
	html +='<param name="quality" value="high" />';
	html +='<param name="menu" value="false" />';
	html +='<param name="wmode" value="opaque" />';
	html +='<param name="allowFullScreen" value="true" />';
	html +='<param name="FlashVars" value="vcastr_file='+player.url+'&vcastr_title=adn&IsAutoPlay=1" />';
	html +='<embed src="'+player.domain+'misc/player/flv.swf" allowFullScreen="true" FlashVars="vcastr_file='+player.url+'&vcastr_title=www.feifeicms.com&IsAutoPlay=1" menu="false" quality="high" width="100%" height="'+player.height+'" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />';
	html +='</object>';	
	return html;
}

player.html = showPlayerHtml();

player.show(showPlayerHtml());