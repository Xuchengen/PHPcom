tinyMCEPopup.requireLangPack();
var RemoteUploadDialog = {
	init : function() {
		this.resize();
		if (tinymce.isGecko)
			document.body.spellcheck = tinyMCEPopup.editor.getParam("gecko_spellcheck");
		
		var temp = document.createElement("div");
		temp.innerHTML = this.ubbcode2Html(tinyMCEPopup.editor.getContent({source_view : true}));
		var innerTxt="";
		var imgs = temp.getElementsByTagName("img");
		temp = null;
		if (imgs.length>0)
		{
			var remoteList = new Array();
			var localList = new Array();
			var flag,i,j,k;
			for(i=0;i<imgs.length;i++)
			{
				if(i==0){
					remoteList[i] = imgs[i].src;
				}else{
					flag=false;
					for (var j=0; j<remoteList.length; j++)
					{
						if(remoteList[j]==imgs[i].src){flag=true;break;}
					}
					if(!flag) {remoteList[i]=imgs[i].src;}
				}
			}
			
			for (var k=0; k<remoteList.length; k++)
			{
				if(!(typeof remoteList[k] == "undefined"))
					innerTxt += pad((k+1),2)+"<input name=\"imgs\" type=\"checkbox\" value=\""+remoteList[k]+"\""+(this.IsRemotePic(remoteList[k])?" checked":"")+" onclick=\"btnStat()\" />"+remoteList[k]+"<br />";
			}
		}else{
			innerTxt+='\u6CA1\u6709\u627E\u5230\u56FE\u7247\u8D44\u6599';$("btnSaveRemote").disabled=true;
		}
		$("Anylysis").innerHTML = innerTxt;
		btnStat();

	},
	
	IsRemotePic : function(ss){
		if(typeof ss=="undefined" || ss==null) return false;
		var local=window.location.protocol+"//"+window.location.host;
		if(ss.substr(0,local.length)!=local){return true;}else{return false;}
	},

	insert : function() {
		tinyMCEPopup.close();
	},

	resize : function() {
		var vp = tinyMCEPopup.dom.getViewPort(window);
	},
	
	ubbcode2Html : function(s) {
		s = tinymce.trim(s);

		function rep(re, str) {
			s = s.replace(re, str);
		};

		rep(/\[img\]\s*([\s\S]+?)\s*\[\/img\]/ig, '<img src="$1" />');
		rep(/\[img\s*=\s*(\d+),(\d+)\s*\]\s*([\s\S]+?)\s*\[\/img\]/ig, '<img src="$3" />');

		return s;
	}
};

tinyMCEPopup.onInit.add(RemoteUploadDialog.init, RemoteUploadDialog);

function $(element){
	if (arguments.length>1) {
		for (var i=0, elements=[], length=arguments.length; i<length; i++)
			elements.push($(arguments[i]));
		return elements;
	}
	if (typeof element =='string') element=document.getElementById(element);
	return element;
}

function pad(num, n) {
    var len = num.toString().length;
    while(len < n) {
        num = "0" + num;
        len++;
    }
    return num;
}

String.prototype.Format = function() {
	if (arguments.length == 0)
		return "";
	if (arguments.length == 1)
		return arguments[0];
	var reg = /{(\d+)?}/g;
	var args = arguments;
	var result = arguments[0].replace(reg, function($0, $1) { return args[parseInt($1) + 1]; })
	return result;
}

if(!document.all){
    XMLDocument.prototype.loadXML = function(xmlString) {
        var childNodes = this.childNodes;
        for (var i = childNodes.length - 1; i >= 0; i--)
            this.removeChild(childNodes[i]);

        var dp = new DOMParser();
        var newDOM = dp.parseFromString(xmlString, "text/xml");
        var newElt = this.importNode(newDOM.documentElement, true);
        this.appendChild(newElt);
    }
}

// check for XPath implementation
if(document.implementation.hasFeature("XPath", "3.0")) {
	// prototying the XMLDocument
	XMLDocument.prototype.selectNodes = function(xpath, contextNode) {
		var xpe = new XPathEvaluator();
		var xmlDomContext = contextNode ? contextNode : this;
		var nsResolver = xpe.createNSResolver( xmlDomContext.ownerDocument == null ? xmlDomContext.documentElement : xmlDomContext.ownerDocument.documentElement); 
		var result = xpe.evaluate(xpath, xmlDomContext, nsResolver, 0, null); 
		var found = []; 
		var res; 
		while   (res = result.iterateNext()) 
			found.push(res); 
		return found;
	}
	// prototying the Element
	Element.prototype.selectNodes = function (xpath, contextNode) {
		var oEvaluator = new XpathEvaluator();
		var elementContext = contextNode ? contextNode : this;
		var oResult = oEvaluator.evalue(xpath, elementContext, null, XPathResult.ORDERED_ITERATOR_TYPE, null);
		var aNodes = new Array;

		if (oResult != null) {
		var oElement = oResult.iterateNext();
			while(oElement) {
				aNodes.puth(oElement);
				oResult.iterateNext();
			}
		}
		return aNodes;
	}
}

// check for XPath implementation
if(document.implementation.hasFeature("XPath", "3.0")) {
	// prototying the XMLDocument
	XMLDocument.prototype.selectSingleNode = function(xpath, contextNode) {
		var xpe = new XPathEvaluator();
		var xmlDomContext = contextNode ? contextNode : this;
		var nsResolver = xpe.createNSResolver( xmlDomContext.ownerDocument == null ? xmlDomContext.documentElement : xmlDomContext.ownerDocument.documentElement); 
		var results = xpe.evaluate(xpath,xmlDomContext,nsResolver,XPathResult.FIRST_ORDERED_NODE_TYPE, null); 
		return results.singleNodeValue;
	}
	// prototying the Element
	Element.prototype.selectSingleNode = function (xpath, contextNode) {
		var elementContext = contextNode ? contextNode : this;
		if(elementContext.ownerDocument.selectSingleNode)
		{
			return elementContext.ownerDocument.selectSingleNode(xpath, elementContext);
		}
		else{return "";}
	}
}
