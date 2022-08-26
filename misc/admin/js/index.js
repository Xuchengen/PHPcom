var IE6 = navigator.userAgent.toLowerCase().indexOf("msie 6") > -1;
var IE7 = navigator.userAgent.toLowerCase().indexOf("msie 7") > -1;

function $(id) { return (typeof id == 'string' ? document.getElementById(id) : null);}

function $T(name) { return (typeof name == 'string' ? document.getElementsByTagName(name) : null);}

function showsubmenu(sid) {
	var whichEl = document.getElementById("submenu_" + sid);
	var menuTitle = document.getElementById("menutitle_" + sid);
	if (whichEl!=null) {
		if (whichEl.style.display == "none"){
			whichEl.style.display='';
			if (menuTitle!=null)
			menuTitle.className='menu_title';
		}else{
			whichEl.style.display='none';
			if (menuTitle!=null)
			menuTitle.className='menu_title2';
		}
	}
}

function toggleMenubar(n){
	var menutoggle = document.getElementById('menutoggle');
	var menubar = document.getElementById('admin_menubar');
	var contont = document.getElementById('admin_contont');
	if(n == 1){
		menubar.style.display = 'none';
		menutoggle.className = 'menutoggle-2';
		if (!IE6 && !IE7) contont.style.left = 5+'px';
	}else{
		menubar.style.display = '';
		menutoggle.className = 'menutoggle-1';
		if (!IE6 && !IE7) contont.style.left = 160+'px';
		
	}
}

function toggleMenuTabs(obj, id){
	var navtab = document.getElementById("tabs").getElementsByTagName("a");
	for(var i= 0,len = navtab.length;i<len;++i){
		if(navtab[i].clssName !==""){
			navtab[i].className = "";
		}
	}
	obj.className = "active";
	obj.blur();
	if(id == 'help' || id == 'logout' || id == 'home') return;
	var menubars = document.getElementById("admin_menubar").getElementsByTagName("dl");
	for(var i= 0,len = menubars.length;i<len;++i){
		menubars[i].style.display = 'none';
	}
	var menuid = document.getElementById("menu_" + id);
	if(menuid){
		menuid.style.display = '';
		var menus = menuid.getElementsByTagName("li");
		for(var i= 0,len = menus.length;i<len;++i){
			if(menus[i].clssName !== ""){
				menus[i].className = "";
			}
			if(i == 0){
				menus[0].className = "current";
			}
		}
	}
}

function showAllMenus(){
	var menubars = document.getElementById("admin_menubar").getElementsByTagName("dl");
	for(var i= 0,len = menubars.length;i<len;++i){
		menubars[i].style.display = '';
	}
}
var menuTimeout = null;
function previewMenu(id){
	if(id){
		if(id == 'help' || id == 'logout' || id == 'home') return;
		menuTimeout = setTimeout(function() {
			var menubars = document.getElementById("admin_menubar").getElementsByTagName("dl");
			for(var i= 0,len = menubars.length;i<len;++i){
				menubars[i].style.display = 'none';
			}
			var menuid = document.getElementById("menu_" + id);
			if(menuid){
				menuid.style.display = '';
			}
		},1000);
	}else{
		clearTimeout(menuTimeout);
	}
	
	
}

function admincpMenuScroll(op, e){
	var obj = document.getElementById('admin_menubar');
	var scrollh = document.body.offsetHeight - 110;
	if(op == 1) {
		obj.scrollTop = obj.scrollTop - scrollh;
	} else if(op == 2) {
		obj.scrollTop = obj.scrollTop + scrollh;
	} else if(op == 3) {
		if(!e) e = window.event;
		if(e.wheelDelta <= 0 || e.detail > 0) {
			obj.scrollTop = obj.scrollTop + 20;
		} else {
			obj.scrollTop = obj.scrollTop - 20;
		}
	}
}

function admincpMenus(obj){
	var menus = document.getElementById("admin_menubar").getElementsByTagName("li");
	for(var i= 0,len = menus.length;i<len;++i){
		if(menus[i].clssName !== ""){
			menus[i].className = "";
		}
	}
	obj.className = "current";
	obj.blur();
}

function initAdmincpMenus(){
	var menus = $('admin_menubar').getElementsByTagName('li');
	for (var i = 0, len = menus.length; i<len; ++i) {
		menus[i].onmouseover = function(e){
			if(this.className == ''){
				this.className = 'active';
			}
			if(this.getElementsByTagName('span')[0])
				this.getElementsByTagName('span')[0].className = 'y';
		};
		menus[i].onmouseout = function(){
			if(this.className == 'active'){
				this.className = '';
			}else{
				this.className = this.className.replace(/active /, '');
			}
			if(this.getElementsByTagName('span')[0])
				this.getElementsByTagName('span')[0].className = 'x';
		};
		menus[i].onclick = function(){
			admincpMenus(this);
		}
		if(i == 0){
			menus[0].className = "current";
		}
	}
}

//window.onload = initAdmincpMenus;
