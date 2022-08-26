
function playslide(htmlName, elementId, handleId, pauseTime, currentClassName, width, height, mouseover, autonav, imgTagName) {
	try {
		if (typeof slidePlayer == 'undefined')
			slidePlayer = {};
	} catch (e) {
		slidePlayer = {};
	}
	mouseover = isUndefined(mouseover) ? 1 : mouseover;
	autonav = isUndefined(autonav) ? 1 : autonav;
	appendSlideData(htmlName, elementId, width, height, imgTagName);
	var elementObj = document.getElementById(elementId);
	var handler = document.getElementById(handleId);
	slidePlayer[elementId] = new PHPcomSlideUtil(elementObj, handler, pauseTime, currentClassName, mouseover, autonav);

	elementObj.onmouseover = function() {
		slidePlayer[elementId].pause();
	}
	elementObj.onmouseout = function() {
		slidePlayer[elementId].play();
	};
	return slidePlayer;
}

function appendSlideData(htmlName, focusName, width, height, imgTagName){
	var htmlData = $(htmlName);
	var focusObj = $(focusName);
	if(focusObj.getAttribute('loaded')) return;
	else focusObj.setAttribute('loaded', 1);
	width = isUndefined(width) ? '268' : width;
	height = isUndefined(height) ? '240' : height;
	imgTagName = isUndefined(imgTagName) ? 'DD' : imgTagName;
	imgTagName = imgTagName ? imgTagName : 'IMG';
	imgTagName = imgTagName.toUpperCase();
	if(typeof htmlData.getElementsByTagName(imgTagName)[0] == 'undefined'){
		if(imgTagName == 'DD') imgTagName = 'IMG';
		else imgTagName = 'DD';
	}
	var pics = htmlData.getElementsByTagName(imgTagName);
	var title,pic,url,a,p,node,imgNode;
	for (var i=0;i<pics.length;i++){
		title = htmlData.getElementsByTagName('a')[i].innerText || htmlData.getElementsByTagName('a')[i].textContent;
		if(imgTagName == 'IMG') pic = htmlData.getElementsByTagName(imgTagName)[i].src;
		else pic = htmlData.getElementsByTagName(imgTagName)[i].innerText || htmlData.getElementsByTagName(imgTagName)[i].textContent;
		url = htmlData.getElementsByTagName('a')[i].href;
		node = document.createElement('li');
		node.style.display = 'none';
		a = document.createElement('a');
		a.href = url; a.target = '_blank';
		imgnode = document.createElement('img');
		imgnode.src = pic; imgnode.width = width; imgnode.height = height;
		a.appendChild(imgnode);
		node.appendChild(a);
		p = document.createElement('p');
		p.style.width = width + 'px';
		a = document.createElement('a');
		a.href = url; a.target = '_blank'; a.innerHTML = title;
		p.appendChild(a);
		node.appendChild(p);
		focusObj.appendChild(node);
	}
}

function PHPcomSlideUtil(elementObj, handler, pauseTime, currentClassName, mouseover, autonav){
	this.id = elementObj.id;
	this.timer;
	this.curScreen = 0;
	this.elementObj = elementObj;
	this.pauseTime = (undefined == pauseTime) ? 3000 : pauseTime * 1000;
	this.currentClassName = (currentClassName == undefined) ? 'sel' : currentClassName;
	this.pics = elementObj.getElementsByTagName('li');
	var outputNav = function(o){
		var s = '';
		for (var i = 1; i <= o.length; i++) {
			s += '<li><a href="javascript:;">'+i+'</a></li>';
		}
		handler.innerHTML = s;
	}
	if(autonav){
		outputNav(this.pics);
	}
	this.handlers = handler.getElementsByTagName('li');
	if(this.handlers.length ==0 && this.pics.length > 0){
		outputNav(this.pics);
	}
	this.maxScreen = this.pics.length > this.handlers.length ? this.handlers.length : this.pics.length;
	for (i=0;i<this.handlers.length;i++) {
		this.handlers[i].setAttribute("index", i);
		var id=this.id;
		if(mouseover){
			this.handlers[i].onmouseover=function(){
				var u = this.getAttribute('index');
				slidePlayer[id].go(u).pause();
			}
		}else{
			this.handlers[i].onclick=function(){
				var u = this.getAttribute('index');
				slidePlayer[id].go(u).pause();
			}
		}
		this.handlers[i].onmouseout=function(){
			slidePlayer[id].play();
		}
	}
	this.go();
	if (pauseTime)
		this.play();
}

PHPcomSlideUtil.prototype.pause = function(){
	this.pauseTime && clearInterval(this.timer);
	this.timer = null;
};
PHPcomSlideUtil.prototype.play = function(){
	if (!this.timer&&this.pauseTime) this.timer = setInterval('slidePlayer.'+this.id+'.go()', this.pauseTime);
};
PHPcomSlideUtil.prototype.go = function(t){
	this.curScreen = t===undefined ? this.curScreen : t;
	this.curScreen %= this.maxScreen;
	for (i=0;i<this.maxScreen;i++) {
		if (i == this.curScreen) {
			this.handlers[i].className = this.currentClassName;
			this.pics[i].style.display = '';
		} else {
			this.handlers[i].className = '';
			this.pics[i].style.display = "none" ;
		}
	}
	++this.curScreen;
	return this;
}