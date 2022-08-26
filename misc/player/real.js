function showPlayerHtml(){
	var html = '<object id="realPlayer" classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="100%" height="'+(player.height-60)+'">';
	html += '<param name="CONTROLS" value="ImageWindow" />';
	html += '<param name="CONSOLE" value="Clip1" />';
	html += '<param name="AUTOSTART" value="-1" />';
	html += '<param name="src" value="'+player.url+'"></object><br>';
	html += '<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="100%" height="60">';
	html += '<param name="CONTROLS" value="ControlPanel,StatusBar" />';
	html += '<param name="CONSOLE" value="Clip1" /></object>';
	html += '<div style="text-align:center; margin-top:50px;">';
	html += '<a href="#" onClick="document.realPlayer.SetFullScreen();">点击这里全屏收看 按ESC键退出</a>';
	html += '</div>';
	return html;
}

player.html = '<object id="realPlayer" classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="100%" height="'+(player.height-60)+'">';
player.html += '<param name="CONTROLS" value="ImageWindow" />';
player.html += '<param name="CONSOLE" value="Clip1" />';
player.html += '<param name="AUTOSTART" value="0" />';
player.html += '<param name="src" value="'+player.url+'"></object><br>';
player.html += '<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="100%" height="60">';
player.html += '<param name="CONTROLS" value="ControlPanel,StatusBar" />';
player.html += '<param name="CONSOLE" value="Clip1" /></object>';

player.show(showPlayerHtml());