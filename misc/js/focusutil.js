var focusUtil = {
	absPosition: function(o, I) {
		var l = o.offsetLeft,
		O = o.offsetTop,
		i = o;
		while (i.id != document.body & i.id != document.documentElement & i != I) {
			i = i.offsetParent;
			l += i.offsetLeft;
			O += i.offsetTop
		};
		return {
			left: l,
			top: O
		}
	}
};
function FocusPic(FocusImgID, BigPicID, NumberID, TitleID, width, height) {
	this.Data = [];
	this.TimeOut = 5000;
	var isIE = navigator.appVersion.indexOf("MSIE") != -1 ? true: false;
	this.width = width;
	this.height = height;
	this.titleHeight = 25;
	this.selectedIndex = 0;
	var TimeOutObj;
	if (!FocusPic.childs) {
		FocusPic.childs = []
	};
	this.showTime = null;
	this.showSum = 10;
	this.ID = FocusPic.childs.push(this) - 1;
	this._listbg = null;
	this.listCode = '<span class="NumberItem" src="[$pic]" onclick="FocusPic.childs[[$thisId]].select([$num])">[$numtoShow]</span>';
	this.Add = function(jsnObj) {
		if (jsnObj) {
			this.Data.push(jsnObj)
		}
	};
	this.TimeOutBegin = function() {
		clearInterval(TimeOutObj);
		TimeOutObj = setInterval("FocusPic.childs[" + this.ID + "].next()", this.TimeOut)
	};
	this.TimeOutEnd = function() {
		clearInterval(TimeOutObj)
	};
	this.select = function(num, noAction) {
		if (num > this.Data.length - 1) {
			return
		};
		if (num == this.selectedIndex) {
			return
		};
		this.TimeOutBegin();
		if (BigPicID) {
			if (this.$(BigPicID)) {
				var aObj = this.$(BigPicID).getElementsByTagName("a")[0];
				aObj.href = this.Data[num].url;
				if (this.aImgY) {
					this.aImgY.style.display = 'none';
					this.aImg.style.zIndex = 0
				};
				this.aImgY = this.$('F' + this.ID + 'BF' + this.selectedIndex);
				this.aImg = this.$('F' + this.ID + 'BF' + num);
				clearTimeout(this.showTime);
				this.showSum = 5;
				if (!noAction) {
					var appleMobileCheck = /\((iPad|iPhone|iPod)/i;
					if (appleMobileCheck.test(navigator.userAgent)) {
						if (this.aImgY) {
							this.aImgY.style.display = 'none'
						};
						this.aImg.style.display = 'block';
						this.aImg.style.zIndex = 0;
						this.aImg.style.opacity = 1;
						this.aImgY = null
					} else {
						this.showTime = setTimeout("FocusPic.childs[" + this.ID + "].show()", 30)
					}
				} else {
					if (isIE) {
						this.aImg.style.filter = "alpha(opacity=100)"
					} else {
						this.aImg.style.opacity = 1
					}
				}
			}
		};
		if (TitleID) {
			if (this.$(TitleID)) {
				this.$(TitleID).innerHTML = "<a href=\"" + this.Data[num].url + "\" target=\"_blank\">" + this.Data[num].title + "</a>"
			}
		};
		if (NumberID) {
			if (this.$(NumberID) && FocusImgID && this.$(FocusImgID)) {
				var sImg = this.$(NumberID).getElementsByTagName("span"),
				i;
				for (i = 0; i < sImg.length; i++) {
					if (i == num || num == (i - this.Data.length)) {
						sImg[i].className = "NumberItemOn";
						if (this._listbg) {
							this._listbg.style.right = 1 + (sImg.length - i - 1) * (16 + 2) + "px"
						} else {
							this._listbg = document.createElement("div");
							if (this._listbg) {
								this._listbg.className = "NumberItemBg";
								this._listbg.style.bottom = this.titleHeight + "px";
								this.$(FocusImgID).appendChild(this._listbg)
							}
						}
					} else {
						sImg[i].className = "NumberItem"
					}
				}
			}
		};
		this.selectedIndex = num;
		if (this.onchange) {
			this.onchange()
		}
	};
	this.show = function() {
		this.showSum--;
		if (this.aImgY) {
			this.aImgY.style.display = 'block'
		};
		this.aImg.style.display = 'block';
		if (isIE) {
			this.aImg.style.filter = "alpha(opacity=0)";
			this.aImg.style.filter = "alpha(opacity=" + (10 - this.showSum) * 10 + ")"
		} else {
			this.aImg.style.opacity = 0;
			this.aImg.style.opacity = (10 - this.showSum) * 0.1
		};
		if (this.showSum <= 0) {
			if (this.aImgY) {
				this.aImgY.style.display = 'none'
			};
			this.aImg.style.zIndex = 0;
			this.aImgY = null
		} else {
			this.aImg.style.zIndex = 2;
			this.showTime = setTimeout("FocusPic.childs[" + this.ID + "].show()", 30)
		}
	};
	this.next = function() {
		var temp = this.selectedIndex;
		temp++;
		if (temp >= this.Data.length) {
			temp = 0
		};
		this.select(temp)
	};
	this.pre = function() {
		var temp = this.selectedIndex;
		temp--;
		if (temp < 0) {
			temp = this.Data.length - 1
		};
		this.select(temp)
	};
	this.begin = function() {
		this.selectedIndex = -1;
		var i, temp = "";
		if (FocusImgID) {
			if (this.$(FocusImgID)) {
				var topObj = this.$(FocusImgID);
				topObj.style.width = this.width + "px";
				topObj.style.height = this.height + "px"
			}
		};
		if (TitleID) {
			if (this.$(TitleID)) {
				this.$(TitleID).style.width = this.width + "px";
				if (this.titleHeight <= 0) {
					this.titleHeight = 0
				};
				this.$(TitleID).style.height = this.titleHeight + "px";
				this.$(TitleID).style.lineHeight = this.titleHeight + "px";
				var _titlebg = document.createElement("div");
				if (_titlebg) {
					_titlebg.className = "TitileBg";
					_titlebg.style.width = this.width + "px";
					_titlebg.style.height = this.titleHeight + "px";
					if (FocusImgID && this.$(FocusImgID)) {
						this.$(FocusImgID).appendChild(_titlebg)
					}
				}
			}
		};
		if (BigPicID) {
			if (this.$(BigPicID)) {
				var aObj = this.$(BigPicID).getElementsByTagName("a")[0];
				aObj.style.zoom = 1;
				this.$(BigPicID).style.height = this.height + "px";
				for (i = 0; i < this.Data.length; i++) {
					temp += '<img src="' + this.Data[i].pic + '" id="F' + this.ID + 'BF' + i + '" style="display:' + (i == 0 ? 'block': 'none') + '" galleryimg="no"' + (this.width ? ' width="' + this.width + '"': '') + (this.height ? ' height="' + this.height + '"': '') + ' alt="' + this.Data[i].title + '" />'
				};
				aObj.innerHTML = temp;
				var imgObjs = aObj.getElementsByTagName("img"),
				XY = focusUtil.absPosition(imgObjs[0], this.$(BigPicID));
				for (i = 0; i < imgObjs.length; i++) {
					imgObjs[i].style.position = "absolute";
					imgObjs[i].style.top = XY.top + "px";
					imgObjs[i].style.left = XY.left + "px";
					imgObjs[i].style.width = this.width + "px";
					imgObjs[i].style.height = this.height + "px"
				}
			}
		};
		if (NumberID) {
			if (this.$(NumberID)) {
				tempHTML = "";
				for (i = 0; i < this.Data.length; i++) {
					temp = this.listCode;
					temp = temp.replace(/\[\$thisId\]/ig, this.ID);
					temp = temp.replace(/\[\$num\]/ig, i);
					temp = temp.replace(/\[\$numtoShow\]/ig, i + 1);
					temp = temp.replace(/\[\$title\]/ig, this.Data[i].title);
					tempHTML += temp
				};
				this.$(NumberID).innerHTML = tempHTML;
				this.$(NumberID).style.bottom = this.titleHeight + 2 + "px";
				var sImg = this.$(NumberID).getElementsByTagName("span"),
				i;
				this._listbg = document.createElement("div");
				if (this._listbg) {
					this._listbg.className = "NumberItemBg";
					this._listbg.style.bottom = this.titleHeight + "px";
					this.$(FocusImgID).appendChild(this._listbg)
				}
			}
		};
		this.TimeOutBegin();
		this.select(0, true)
	};
	this.$ = function(objName) {
		if (document.getElementById) {
			return eval('document.getElementById("' + objName + '")')
		} else {
			return eval('document.all.' + objName)
		}
	}
};
