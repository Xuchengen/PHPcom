(function() {
	//Load the language file.
	tinymce.PluginManager.requireLangPack('remoteupload');
	tinymce.create("tinymce.plugins.RemoteUploadPlugin", {
		init: function(ed, url) {
			var d = this;
			d.editor = ed;
			ed.addCommand("mceRemoteUpload",
				function(e) {
					ed.windowManager.open({
						file: url + "/remoteupload.htm",
						width: ed.getParam("remoteupload_popup_width", 620),
						height: ed.getParam("remoteupload_popup_height", 450),
						inline: 1
					},
					{
						plugin_url: url
					})
			});
			ed.addButton("remoteupload", {
				title: 'remoteupload.desc',
				cmd: 'mceRemoteUpload',
				image: url + '/img/picsave.gif'
			});
			
		},
		getInfo: function() {
			return {
				longname: "RemoteUpload plugin",
				author: "Webenvoy",
				authorurl: "http://www.newasp.net",
				infourl: "http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/remoteupload",
				version: tinymce.majorVersion + "." + tinymce.minorVersion
			}
		}
	});

	tinymce.PluginManager.add("remoteupload", tinymce.plugins.RemoteUploadPlugin)
})();