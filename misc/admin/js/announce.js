function checkPost(form){
	if(!form) form=document.forms[0];
	var l = getContentLength();
	
	if (l == -1) {
		if (form.editor_content.value.trim() == '') {
			alert("���ݲ���Ϊ��!");
			form.editor_content.focus();
			return false;
		}
	}else{
		if (l == 0){
			alert("���ݲ���Ϊ��!");
			return false;
		}
	}

	if (form.post_title.value.trim() == '') {
		alert("���ⲻ��Ϊ��!");
		form.post_title.focus();
		return false;
	}

	if (form.post_author.value.trim() == '') {
		alert("���߲���Ϊ��!");
		form.post_author.focus();
		return false;
	}
	return true;
}
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
		theme_advanced_buttons1 : "code,|,bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,link,unlink,selectall,removeformat,|,forecolor,backcolor,fontsizeselect",
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		theme_advanced_buttons4 : "",

		theme_advanced_fonts: "����=����;����=����;����=����;����=����;����=����;��Բ=��Բ;Arial=arial,helvetica,sans-serif;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Tahoma=tahoma,arial,helvetica,sans-serif;Times New Roman=times new roman,times;Verdana=verdana,geneva", //��������
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
		alert ("��󳤶�Ϊ 65534 �ֽڣ���ǰ����Ϊ" + iLength + "�ֽڣ�");
		return false;
	}else{
		return iLength
	}
}