tinyMCE.init({
	// General options runcode
	//id : textareas,
	language : "zh-cn",
	mode : "exact",
	elements : "editor_content",
	theme : "advanced",

	plugins : "remoteupload,blockcode,noneditable,safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,nonbreaking,xhtmlxtras,wordcount",
	// Theme options
	//theme_advanced_buttons1 : "preview,code,|,bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,blockquote,selectall,removeformat,|,forecolor,backcolor,fontselect,fontsizeselect",
	//theme_advanced_buttons2 : "",
	theme_advanced_buttons1 : "code,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,outdent,indent,|,forecolor,backcolor,fontselect,fontsizeselect",
	theme_advanced_buttons2 : "undo,redo,|,cut,copy,paste,pastetext,pasteword,|,search,link,unlink,anchor,|,table,blockcode,runcode,blockquote,remoteupload,image,media,|,selectall,removeformat,advhr,pagebreak,|,preview,fullscreen",
	theme_advanced_buttons3 : "",
	theme_advanced_buttons4 : "",

	theme_advanced_fonts: "宋体=宋体;黑体=黑体;仿宋=仿宋;楷体=楷体;隶书=隶书;幼圆=幼圆;Arial=arial,helvetica,sans-serif;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Tahoma=tahoma,arial,helvetica,sans-serif;Times New Roman=times new roman,times;Verdana=verdana,geneva", //设置字体
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true,
	theme_advanced_styles : "Code=codeStyle;Quote=quoteStyle",
	remove_linebreaks : false,
	convert_urls : false,
	relative_urls : true

});

/*KE.show({
	id : 'content2',
	cssPath : './index.css',
	urlType : 'absolute',
	filterMode : true,
	allowUpload : false,
	//shadowMode : false,
	//autoSetDataMode: false,
	allowPreviewEmoticons : false,
	allowPreview : true,
	allowFileManager : true,
	afterCreate : function(id) {
		KE.event.ctrl(document, 13, function() {
			KE.util.setData(id);
			document.forms['example'].submit();
		});
		KE.event.ctrl(KE.g[id].iframeDoc, 13, function() {
			KE.util.setData(id);
			document.forms['example'].submit();
		});
	}
	items : [
				'source','plainpaste', 'wordpaste', '|', 'fontname', 'fontsize', '|', 'textcolor', 'bgcolor', 'bold', 'italic', 'underline',
				'removeformat', 'selectall', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
				'insertunorderedlist', '|', 'advtable', 'image', 'link', 'unlink','|','fullscreen']
});*/



