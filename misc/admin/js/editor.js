if(typeof tinyMCE == "object") {
	tinyMCE.init({
		// General options runcode
		//id : textareas,
		language : "zh-cn",
		mode : "exact",
		elements : "editor_content",
		theme : "advanced",

		plugins : "remoteupload,blockcode,noneditable,safari,pagebreak,table,save,advhr,advimage,advlink,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,nonbreaking,xhtmlxtras,wordcount",
		// Theme options
		//theme_advanced_buttons1 : "preview,code,|,bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,blockquote,selectall,removeformat,|,forecolor,backcolor,fontselect,fontsizeselect",
		//theme_advanced_buttons2 : "",
		theme_advanced_buttons1 : "code,newdocument,preview,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,outdent,indent,|,forecolor,backcolor,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "undo,redo,|,cut,copy,paste,pastetext,pasteword,|,search,link,unlink,anchor,|,table,blockcode,blockquote,remoteupload,image,media,attachimage,attachment,|,selectall,removeformat,advhr,pagebreak,|,fullscreen,help",
		theme_advanced_buttons3 : "",
		theme_advanced_buttons4 : "",

		theme_advanced_fonts: "宋体=宋体;黑体=黑体;仿宋=仿宋;楷体=楷体;隶书=隶书;幼圆=幼圆;Arial=arial,helvetica,sans-serif;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Tahoma=tahoma,arial,helvetica,sans-serif;Times New Roman=times new roman,times;Verdana=verdana,geneva", //设置字体
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		remove_linebreaks : false,
		convert_urls : false,
		relative_urls : true
		//preformatted : true

	});
}


function getContentLength(){
	var iLength = -1;
	if(typeof tinyMCE == "object") {
		iLength = tinyMCE.get('editor_content').getContent().length;
	}
	
	if (iLength > 65534)
	{
		alert ("最大长度为 65534 字节，当前长度为" + iLength + "字节！");
		return false;
	}else{
		return iLength
	}
}