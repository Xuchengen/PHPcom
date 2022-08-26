tinyMCEPopup.requireLangPack();

var BlockCodeDialog = {
	init : function() {
		this.resize();
		document.getElementById('content').value = this.getContent();
	},

	insert : function() {
		var h = tinyMCEPopup.dom.encode(document.getElementById('content').value);
		var t = document.getElementById('codeType');
		if (t.checked) {
			if (h) h = '<pre class="htmlcodeStyle">' + h + '</pre><p>&nbsp;</p>';
		}else{
			if (h) h = '<pre class="blockcodeStyle">' + h + '</pre><p>&nbsp;</p>';
		}
		tinyMCEPopup.editor.execCommand('mceInsertBlockCode', false, {content : h});
		tinyMCEPopup.close();
	},
	
	getContent : function(){
		var s = tinyMCEPopup.editor.selection.getContent({format : 'text'});
		s = s.replace(/\s+$/g, ''); //Çå³ý½áÎ²»»ÐÐ
		return s;
	},

	resize : function() {
		var vp = tinyMCEPopup.dom.getViewPort(window), el;

		el = document.getElementById('content');

		el.style.width  = (vp.w - 20) + 'px';
		el.style.height = (vp.h - 90) + 'px';
	}
};

tinyMCEPopup.onInit.add(BlockCodeDialog.init, BlockCodeDialog);

function setWrap(val) {
	var v, n, s = document.getElementById('content');

	s.wrap = val;

	if (!tinymce.isIE) {
		v = s.value;
		n = s.cloneNode(false);
		n.setAttribute("wrap", val);
		s.parentNode.replaceChild(n, s);
		n.value = v;
	}
}

function toggleWordWrap(elm) {
	if (elm.checked)
		setWrap('soft');
	else
		setWrap('off');
}