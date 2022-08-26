//function $(id) {
//	return document.getElementById(id);
//}
function fontStyle() {
	this.demoObj = null;
	this.colorObj = null;
	this.familyObj = null;
	this.weightObj = null;
	this.valueObj = null;
	this.styleObj = $("title_style");
	this.testObj = $("demo_style");
	this.inited = false;
}

fontStyle.prototype = {
	init : function() {
		//alert(this.inited);
		if(!this.inited) {
			this.demoObj = $('demofontstyle');
			this.colorObj = $('style_fontcolor');
			this.familyObj = $('style_fontfamily');
			this.weightObj = $('style_fontweight');
			this.valueObj = $('style_fontvalue');
			this.styleObj = $("title_style");
			this.testObj = $("demo_style");
			this.inited = true;
		}
	},
	close : function() {
		//parent.phpcom.dialog.close('dialog_window');
		//phpcom.dialog.close('dialog_alert');
		menu.hide('style_menu','dialog');
	},
	ok : function() {
		this.init();
		var s = this.valueObj.value;
		if (s && s != null) {
			this.styleObj.value = s;
			this.testObj.innerHTML = '<span style="' + s + '">设置标题样式 ABC123</span>';
		}
		this.close();
	},
	setcolor : function() {
		this.init();
		this.demoObj.style.color = this.colorObj.value;
		this.valueObj.value = this.output();
	},
	setfamily : function() {
		this.init();
		this.demoObj.style.fontFamily = this.familyObj.value;
		this.valueObj.value = this.output();
	},
	setfont : function() {
		this.init();
		var styleName = this.weightObj.value
		switch (styleName) {
		case "W":
			this.demoObj.style.fontWeight = "";		//加粗
			this.demoObj.style.textDecoration = "";	//下线
			this.demoObj.style.fontStyle = "";			//斜体
			break;
		case "B":
			this.demoObj.style.fontWeight = "bold";	//加粗
			this.demoObj.style.textDecoration = "";	//下线
			this.demoObj.style.fontStyle = "";			//斜体
			break;
		case "U":
			this.demoObj.style.fontWeight = "";		//加粗
			this.demoObj.style.textDecoration = " underline"; //下线
			this.demoObj.style.fontStyle = "";			//斜体
			break;
		case "I":
			this.demoObj.style.fontWeight = "";		//加粗
			this.demoObj.style.textDecoration = "";	//下线
			this.demoObj.style.fontStyle = "italic";	//斜体
			break;
		case "BU":
			this.demoObj.style.fontWeight = "bold";	//加粗
			this.demoObj.style.textDecoration = " underline"; //下线
			this.demoObj.style.fontStyle = "";			//斜体
			break;
		case "BI":
			this.demoObj.style.fontWeight = "bold";	//加粗
			this.demoObj.style.textDecoration = "";	//下线
			this.demoObj.style.fontStyle = "italic";	//斜体
			break;
		case "UI":
			this.demoObj.style.fontWeight = "";		//加粗
			this.demoObj.style.textDecoration = " underline"; //下线
			this.demoObj.style.fontStyle = "italic";	//斜体
			break;
		case "BUI":
			this.demoObj.style.fontWeight = "bold";	//加粗
			this.demoObj.style.textDecoration = " underline"; //下线
			this.demoObj.style.fontStyle = "italic";	//斜体
			break;
		}
		this.valueObj.value = this.output();
	},
	output : function() {
		var stylebold = 'font-weight:' + this.demoObj.style.fontWeight + ';';
		if (!this.demoObj.style.fontWeight) stylebold = "";
		var styleitalic = 'font-style:' + this.demoObj.style.fontStyle + ';';
		if (!this.demoObj.style.fontStyle) styleitalic = "";
		var styleunderline = 'text-decoration:' + this.demoObj.style.textDecoration + ';';
		if (!this.demoObj.style.textDecoration) styleunderline = "";
		var stylefamily = 'font-family:' + this.demoObj.style.fontFamily + ';';
		if (!this.demoObj.style.fontFamily) stylefamily = "";
		var stylecolor = 'color:' + this.demoObj.style.color + ';';
		if (!this.demoObj.style.color) stylecolor = "";
		var stylesize = 'font-size:' + this.demoObj.style.fontSize + ';';
		if (!this.demoObj.style.fontSize) stylesize = "";
		return stylebold + styleitalic + styleunderline + stylefamily + stylecolor + stylesize;
	},
	demostyle : function() {
		var o = fontstyle;
		o.init();
		var s = o.testObj.getElementsByTagName('SPAN')[0];
		if(s){
			if(o.demoObj){
				o.demoObj.style.color = s.style.color;
				o.demoObj.style.fontFamily = s.style.fontFamily;
				o.demoObj.style.fontWeight = s.style.fontWeight;
				o.demoObj.style.textDecoration = s.style.textDecoration;
				o.demoObj.style.fontStyle = s.style.fontStyle;
				o.valueObj.value = o.styleObj.value;
			}else{
				o.inited=false;
			}
		}
	}
}

var fontstyle = new fontStyle();
