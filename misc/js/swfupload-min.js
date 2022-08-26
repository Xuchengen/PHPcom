var SWFUpload;SWFUpload==void 0&&(SWFUpload=function(a){this.initSWFUpload(a)});SWFUpload.prototype.initSWFUpload=function(a){try{this.customSettings={},this.settings=a,this.eventQueue=[],this.movieName="SWFUpload_"+SWFUpload.movieCount++,this.movieElement=null,SWFUpload.instances[this.movieName]=this,this.initSettings(),this.loadFlash(),this.displayDebugInfo()}catch(b){throw delete SWFUpload.instances[this.movieName],b;}};SWFUpload.instances={};SWFUpload.movieCount=0;SWFUpload.version="2.2.0 2009-03-25";
SWFUpload.QUEUE_ERROR={QUEUE_LIMIT_EXCEEDED:-100,FILE_EXCEEDS_SIZE_LIMIT:-110,ZERO_BYTE_FILE:-120,INVALID_FILETYPE:-130};SWFUpload.UPLOAD_ERROR={HTTP_ERROR:-200,MISSING_UPLOAD_URL:-210,IO_ERROR:-220,SECURITY_ERROR:-230,UPLOAD_LIMIT_EXCEEDED:-240,UPLOAD_FAILED:-250,SPECIFIED_FILE_ID_NOT_FOUND:-260,FILE_VALIDATION_FAILED:-270,FILE_CANCELLED:-280,UPLOAD_STOPPED:-290};SWFUpload.FILE_STATUS={QUEUED:-1,IN_PROGRESS:-2,ERROR:-3,COMPLETE:-4,CANCELLED:-5};
SWFUpload.BUTTON_ACTION={SELECT_FILE:-100,SELECT_FILES:-110,START_UPLOAD:-120};SWFUpload.CURSOR={ARROW:-1,HAND:-2};SWFUpload.WINDOW_MODE={WINDOW:"window",TRANSPARENT:"transparent",OPAQUE:"opaque"};SWFUpload.completeURL=function(a){if(typeof a!=="string"||a.match(/^https?:\/\//i)||a.match(/^\//))return a;var b=window.location.pathname.lastIndexOf("/");path=b<=0?"/":window.location.pathname.substr(0,b)+"/";return path+a};
SWFUpload.prototype.initSettings=function(){this.ensureDefault=function(a,b){this.settings[a]=this.settings[a]==void 0?b:this.settings[a]};this.ensureDefault("upload_url","");this.ensureDefault("preserve_relative_urls",!1);this.ensureDefault("file_post_name","Filedata");this.ensureDefault("post_params",{});this.ensureDefault("use_query_string",!1);this.ensureDefault("requeue_on_error",!1);this.ensureDefault("http_success",[]);this.ensureDefault("assume_success_timeout",0);this.ensureDefault("file_types",
"*.*");this.ensureDefault("file_types_description","All Files");this.ensureDefault("file_size_limit",0);this.ensureDefault("file_upload_limit",0);this.ensureDefault("file_queue_limit",0);this.ensureDefault("flash_url","swfupload.swf");this.ensureDefault("prevent_swf_caching",!0);this.ensureDefault("button_image_url","");this.ensureDefault("button_width",1);this.ensureDefault("button_height",1);this.ensureDefault("button_text","");this.ensureDefault("button_text_style","color: #000000; font-size: 16pt;");
this.ensureDefault("button_text_top_padding",0);this.ensureDefault("button_text_left_padding",0);this.ensureDefault("button_action",SWFUpload.BUTTON_ACTION.SELECT_FILES);this.ensureDefault("button_disabled",!1);this.ensureDefault("button_placeholder_id","");this.ensureDefault("button_placeholder",null);this.ensureDefault("button_cursor",SWFUpload.CURSOR.ARROW);this.ensureDefault("button_window_mode",SWFUpload.WINDOW_MODE.WINDOW);this.ensureDefault("debug",!1);this.settings.debug_enabled=this.settings.debug;
this.settings.return_upload_start_handler=this.returnUploadStart;this.ensureDefault("swfupload_loaded_handler",null);this.ensureDefault("file_dialog_start_handler",null);this.ensureDefault("file_queued_handler",null);this.ensureDefault("file_queue_error_handler",null);this.ensureDefault("file_dialog_complete_handler",null);this.ensureDefault("upload_start_handler",null);this.ensureDefault("upload_progress_handler",null);this.ensureDefault("upload_error_handler",null);this.ensureDefault("upload_success_handler",
null);this.ensureDefault("upload_complete_handler",null);this.ensureDefault("debug_handler",this.debugMessage);this.ensureDefault("custom_settings",{});this.customSettings=this.settings.custom_settings;if(this.settings.prevent_swf_caching)this.settings.flash_url=this.settings.flash_url+(this.settings.flash_url.indexOf("?")<0?"?":"&")+"preventswfcaching="+(new Date).getTime();if(!this.settings.preserve_relative_urls)this.settings.upload_url=SWFUpload.completeURL(this.settings.upload_url),this.settings.button_image_url=
SWFUpload.completeURL(this.settings.button_image_url);delete this.ensureDefault};
SWFUpload.prototype.loadFlash=function(){var a,b;if(document.getElementById(this.movieName)!==null)throw"ID "+this.movieName+" is already in use. The Flash Object could not be added";a=document.getElementById(this.settings.button_placeholder_id)||this.settings.button_placeholder;if(a==void 0)throw"Could not find the placeholder element: "+this.settings.button_placeholder_id;b=document.createElement("div");b.innerHTML=this.getFlashHTML();a.parentNode.replaceChild(b.firstChild,a);window[this.movieName]==
void 0&&(window[this.movieName]=this.getMovieElement())};
SWFUpload.prototype.getFlashHTML=function(){return['<object id="',this.movieName,'" type="application/x-shockwave-flash" data="',this.settings.flash_url,'" width="',this.settings.button_width,'" height="',this.settings.button_height,'" class="swfupload"><param name="wmode" value="',this.settings.button_window_mode,'" /><param name="movie" value="',this.settings.flash_url,'" /><param name="quality" value="high" /><param name="menu" value="false" /><param name="allowScriptAccess" value="always" />','<param name="flashvars" value="'+
this.getFlashVars()+'" />',"</object>"].join("")};
SWFUpload.prototype.getFlashVars=function(){var a=this.buildParamString(),b=this.settings.http_success.join(",");return["movieName=",encodeURIComponent(this.movieName),"&amp;uploadURL=",encodeURIComponent(this.settings.upload_url),"&amp;useQueryString=",encodeURIComponent(this.settings.use_query_string),"&amp;requeueOnError=",encodeURIComponent(this.settings.requeue_on_error),"&amp;httpSuccess=",encodeURIComponent(b),"&amp;assumeSuccessTimeout=",encodeURIComponent(this.settings.assume_success_timeout),"&amp;params=",
encodeURIComponent(a),"&amp;filePostName=",encodeURIComponent(this.settings.file_post_name),"&amp;fileTypes=",encodeURIComponent(this.settings.file_types),"&amp;fileTypesDescription=",encodeURIComponent(this.settings.file_types_description),"&amp;fileSizeLimit=",encodeURIComponent(this.settings.file_size_limit),"&amp;fileUploadLimit=",encodeURIComponent(this.settings.file_upload_limit),"&amp;fileQueueLimit=",encodeURIComponent(this.settings.file_queue_limit),"&amp;debugEnabled=",encodeURIComponent(this.settings.debug_enabled),
"&amp;buttonImageURL=",encodeURIComponent(this.settings.button_image_url),"&amp;buttonWidth=",encodeURIComponent(this.settings.button_width),"&amp;buttonHeight=",encodeURIComponent(this.settings.button_height),"&amp;buttonText=",encodeURIComponent(this.settings.button_text),"&amp;buttonTextTopPadding=",encodeURIComponent(this.settings.button_text_top_padding),"&amp;buttonTextLeftPadding=",encodeURIComponent(this.settings.button_text_left_padding),"&amp;buttonTextStyle=",encodeURIComponent(this.settings.button_text_style),
"&amp;buttonAction=",encodeURIComponent(this.settings.button_action),"&amp;buttonDisabled=",encodeURIComponent(this.settings.button_disabled),"&amp;buttonCursor=",encodeURIComponent(this.settings.button_cursor)].join("")};SWFUpload.prototype.getMovieElement=function(){if(this.movieElement==void 0)this.movieElement=document.getElementById(this.movieName);if(this.movieElement===null)throw"Could not find Flash element";return this.movieElement};
SWFUpload.prototype.buildParamString=function(){var a=this.settings.post_params,b=[];if(typeof a==="object")for(var c in a)a.hasOwnProperty(c)&&b.push(encodeURIComponent(c.toString())+"="+encodeURIComponent(a[c].toString()));return b.join("&amp;")};
SWFUpload.prototype.destroy=function(){try{this.cancelUpload(null,!1);var a=null;if((a=this.getMovieElement())&&typeof a.CallFunction==="unknown"){for(var b in a)try{typeof a[b]==="function"&&(a[b]=null)}catch(c){}try{a.parentNode.removeChild(a)}catch(d){}}window[this.movieName]=null;SWFUpload.instances[this.movieName]=null;delete SWFUpload.instances[this.movieName];this.movieName=this.eventQueue=this.customSettings=this.settings=this.movieElement=null;return!0}catch(e){return!1}};
SWFUpload.prototype.displayDebugInfo=function(){this.debug(["---SWFUpload Instance Info---\nVersion: ",SWFUpload.version,"\nMovie Name: ",this.movieName,"\nSettings:\n\tupload_url:               ",this.settings.upload_url,"\n\tflash_url:                ",this.settings.flash_url,"\n\tuse_query_string:         ",this.settings.use_query_string.toString(),"\n\trequeue_on_error:         ",this.settings.requeue_on_error.toString(),"\n\thttp_success:             ",this.settings.http_success.join(", "),"\n\tassume_success_timeout:   ",
this.settings.assume_success_timeout,"\n\tfile_post_name:           ",this.settings.file_post_name,"\n\tpost_params:              ",this.settings.post_params.toString(),"\n\tfile_types:               ",this.settings.file_types,"\n\tfile_types_description:   ",this.settings.file_types_description,"\n\tfile_size_limit:          ",this.settings.file_size_limit,"\n\tfile_upload_limit:        ",this.settings.file_upload_limit,"\n\tfile_queue_limit:         ",this.settings.file_queue_limit,"\n\tdebug:                    ",
this.settings.debug.toString(),"\n\tprevent_swf_caching:      ",this.settings.prevent_swf_caching.toString(),"\n\tbutton_placeholder_id:    ",this.settings.button_placeholder_id.toString(),"\n\tbutton_placeholder:       ",this.settings.button_placeholder?"Set":"Not Set","\n\tbutton_image_url:         ",this.settings.button_image_url.toString(),"\n\tbutton_width:             ",this.settings.button_width.toString(),"\n\tbutton_height:            ",this.settings.button_height.toString(),"\n\tbutton_text:              ",
this.settings.button_text.toString(),"\n\tbutton_text_style:        ",this.settings.button_text_style.toString(),"\n\tbutton_text_top_padding:  ",this.settings.button_text_top_padding.toString(),"\n\tbutton_text_left_padding: ",this.settings.button_text_left_padding.toString(),"\n\tbutton_action:            ",this.settings.button_action.toString(),"\n\tbutton_disabled:          ",this.settings.button_disabled.toString(),"\n\tcustom_settings:          ",this.settings.custom_settings.toString(),"\nEvent Handlers:\n\tswfupload_loaded_handler assigned:  ",
(typeof this.settings.swfupload_loaded_handler==="function").toString(),"\n\tfile_dialog_start_handler assigned: ",(typeof this.settings.file_dialog_start_handler==="function").toString(),"\n\tfile_queued_handler assigned:       ",(typeof this.settings.file_queued_handler==="function").toString(),"\n\tfile_queue_error_handler assigned:  ",(typeof this.settings.file_queue_error_handler==="function").toString(),"\n\tupload_start_handler assigned:      ",(typeof this.settings.upload_start_handler===
"function").toString(),"\n\tupload_progress_handler assigned:   ",(typeof this.settings.upload_progress_handler==="function").toString(),"\n\tupload_error_handler assigned:      ",(typeof this.settings.upload_error_handler==="function").toString(),"\n\tupload_success_handler assigned:    ",(typeof this.settings.upload_success_handler==="function").toString(),"\n\tupload_complete_handler assigned:   ",(typeof this.settings.upload_complete_handler==="function").toString(),"\n\tdebug_handler assigned:             ",
(typeof this.settings.debug_handler==="function").toString(),"\n"].join(""))};SWFUpload.prototype.addSetting=function(a,b,c){return b==void 0?this.settings[a]=c:this.settings[a]=b};SWFUpload.prototype.getSetting=function(a){if(this.settings[a]!=void 0)return this.settings[a];return""};
SWFUpload.prototype.callFlash=function(a,b){var b=b||[],c=this.getMovieElement(),d,e;try{e=c.CallFunction('<invoke name="'+a+'" returntype="javascript">'+__flash__argumentsToXML(b,0)+"</invoke>"),d=eval(e)}catch(f){throw"Call to "+a+" failed";}d!=void 0&&typeof d.post==="object"&&(d=this.unescapeFilePostParams(d));return d};SWFUpload.prototype.selectFile=function(){this.callFlash("SelectFile")};SWFUpload.prototype.selectFiles=function(){this.callFlash("SelectFiles")};
SWFUpload.prototype.startUpload=function(a){this.callFlash("StartUpload",[a])};SWFUpload.prototype.cancelUpload=function(a,b){b!==!1&&(b=!0);this.callFlash("CancelUpload",[a,b])};SWFUpload.prototype.stopUpload=function(){this.callFlash("StopUpload")};SWFUpload.prototype.getStats=function(){return this.callFlash("GetStats")};SWFUpload.prototype.setStats=function(a){this.callFlash("SetStats",[a])};
SWFUpload.prototype.getFile=function(a){return typeof a==="number"?this.callFlash("GetFileByIndex",[a]):this.callFlash("GetFile",[a])};SWFUpload.prototype.addFileParam=function(a,b,c){return this.callFlash("AddFileParam",[a,b,c])};SWFUpload.prototype.removeFileParam=function(a,b){this.callFlash("RemoveFileParam",[a,b])};SWFUpload.prototype.setUploadURL=function(a){this.settings.upload_url=a.toString();this.callFlash("SetUploadURL",[a])};
SWFUpload.prototype.setPostParams=function(a){this.settings.post_params=a;this.callFlash("SetPostParams",[a])};SWFUpload.prototype.addPostParam=function(a,b){this.settings.post_params[a]=b;this.callFlash("SetPostParams",[this.settings.post_params])};SWFUpload.prototype.removePostParam=function(a){delete this.settings.post_params[a];this.callFlash("SetPostParams",[this.settings.post_params])};
SWFUpload.prototype.setFileTypes=function(a,b){this.settings.file_types=a;this.settings.file_types_description=b;this.callFlash("SetFileTypes",[a,b])};SWFUpload.prototype.setFileSizeLimit=function(a){this.settings.file_size_limit=a;this.callFlash("SetFileSizeLimit",[a])};SWFUpload.prototype.setFileUploadLimit=function(a){this.settings.file_upload_limit=a;this.callFlash("SetFileUploadLimit",[a])};
SWFUpload.prototype.setFileQueueLimit=function(a){this.settings.file_queue_limit=a;this.callFlash("SetFileQueueLimit",[a])};SWFUpload.prototype.setFilePostName=function(a){this.settings.file_post_name=a;this.callFlash("SetFilePostName",[a])};SWFUpload.prototype.setUseQueryString=function(a){this.settings.use_query_string=a;this.callFlash("SetUseQueryString",[a])};SWFUpload.prototype.setRequeueOnError=function(a){this.settings.requeue_on_error=a;this.callFlash("SetRequeueOnError",[a])};
SWFUpload.prototype.setHTTPSuccess=function(a){typeof a==="string"&&(a=a.replace(" ","").split(","));this.settings.http_success=a;this.callFlash("SetHTTPSuccess",[a])};SWFUpload.prototype.setAssumeSuccessTimeout=function(a){this.settings.assume_success_timeout=a;this.callFlash("SetAssumeSuccessTimeout",[a])};SWFUpload.prototype.setDebugEnabled=function(a){this.settings.debug_enabled=a;this.callFlash("SetDebugEnabled",[a])};
SWFUpload.prototype.setButtonImageURL=function(a){a==void 0&&(a="");this.settings.button_image_url=a;this.callFlash("SetButtonImageURL",[a])};SWFUpload.prototype.setButtonDimensions=function(a,b){this.settings.button_width=a;this.settings.button_height=b;var c=this.getMovieElement();if(c!=void 0)c.style.width=a+"px",c.style.height=b+"px";this.callFlash("SetButtonDimensions",[a,b])};SWFUpload.prototype.setButtonText=function(a){this.settings.button_text=a;this.callFlash("SetButtonText",[a])};
SWFUpload.prototype.setButtonTextPadding=function(a,b){this.settings.button_text_top_padding=b;this.settings.button_text_left_padding=a;this.callFlash("SetButtonTextPadding",[a,b])};SWFUpload.prototype.setButtonTextStyle=function(a){this.settings.button_text_style=a;this.callFlash("SetButtonTextStyle",[a])};SWFUpload.prototype.setButtonDisabled=function(a){this.settings.button_disabled=a;this.callFlash("SetButtonDisabled",[a])};
SWFUpload.prototype.setButtonAction=function(a){this.settings.button_action=a;this.callFlash("SetButtonAction",[a])};SWFUpload.prototype.setButtonCursor=function(a){this.settings.button_cursor=a;this.callFlash("SetButtonCursor",[a])};
SWFUpload.prototype.queueEvent=function(a,b){b==void 0?b=[]:b instanceof Array||(b=[b]);var c=this;if(typeof this.settings[a]==="function")this.eventQueue.push(function(){this.settings[a].apply(this,b)}),setTimeout(function(){c.executeNextEvent()},0);else if(this.settings[a]!==null)throw"Event handler "+a+" is unknown or is not a function";};SWFUpload.prototype.executeNextEvent=function(){var a=this.eventQueue?this.eventQueue.shift():null;typeof a==="function"&&a.apply(this)};
SWFUpload.prototype.unescapeFilePostParams=function(a){var b=/[$]([0-9a-f]{4})/i,c={},d;if(a!=void 0){for(var e in a.post)if(a.post.hasOwnProperty(e)){d=e;for(var f;(f=b.exec(d))!==null;)d=d.replace(f[0],String.fromCharCode(parseInt("0x"+f[1],16)));c[d]=a.post[e]}a.post=c}return a};SWFUpload.prototype.testExternalInterface=function(){try{return this.callFlash("TestExternalInterface")}catch(a){return!1}};
SWFUpload.prototype.flashReady=function(){var a=this.getMovieElement();a?(this.cleanUp(a),this.queueEvent("swfupload_loaded_handler")):this.debug("Flash called back ready but the flash movie can't be found.")};
SWFUpload.prototype.cleanUp=function(a){try{if(this.movieElement&&typeof a.CallFunction==="unknown")for(var b in this.debug("Removing Flash functions hooks (this should only run in IE and should prevent memory leaks)"),a)try{typeof a[b]==="function"&&(a[b]=null)}catch(c){}}catch(d){}window.__flash__removeCallback=function(a,b){try{a&&(a[b]=null)}catch(c){}}};SWFUpload.prototype.fileDialogStart=function(){this.queueEvent("file_dialog_start_handler")};
SWFUpload.prototype.fileQueued=function(a){a=this.unescapeFilePostParams(a);this.queueEvent("file_queued_handler",a)};SWFUpload.prototype.fileQueueError=function(a,b,c){a=this.unescapeFilePostParams(a);this.queueEvent("file_queue_error_handler",[a,b,c])};SWFUpload.prototype.fileDialogComplete=function(a,b,c){this.queueEvent("file_dialog_complete_handler",[a,b,c])};SWFUpload.prototype.uploadStart=function(a){a=this.unescapeFilePostParams(a);this.queueEvent("return_upload_start_handler",a)};
SWFUpload.prototype.returnUploadStart=function(a){var b;if(typeof this.settings.upload_start_handler==="function")a=this.unescapeFilePostParams(a),b=this.settings.upload_start_handler.call(this,a);else if(this.settings.upload_start_handler!=void 0)throw"upload_start_handler must be a function";b===void 0&&(b=!0);this.callFlash("ReturnUploadStart",[!!b])};SWFUpload.prototype.uploadProgress=function(a,b,c){a=this.unescapeFilePostParams(a);this.queueEvent("upload_progress_handler",[a,b,c])};
SWFUpload.prototype.uploadError=function(a,b,c){a=this.unescapeFilePostParams(a);this.queueEvent("upload_error_handler",[a,b,c])};SWFUpload.prototype.uploadSuccess=function(a,b,c){a=this.unescapeFilePostParams(a);this.queueEvent("upload_success_handler",[a,b,c])};SWFUpload.prototype.uploadComplete=function(a){a=this.unescapeFilePostParams(a);this.queueEvent("upload_complete_handler",a)};SWFUpload.prototype.debug=function(a){this.queueEvent("debug_handler",a)};
SWFUpload.prototype.debugMessage=function(a){if(this.settings.debug){var b=[];if(typeof a==="object"&&typeof a.name==="string"&&typeof a.message==="string"){for(var c in a)a.hasOwnProperty(c)&&b.push(c+": "+a[c]);a=b.join("\n")||"";b=a.split("\n");a="EXCEPTION: "+b.join("\nEXCEPTION: ")}SWFUpload.Console.writeLine(a)}};SWFUpload.Console={};
SWFUpload.Console.writeLine=function(a){var b,c;try{b=document.getElementById("SWFUpload_Console");if(!b)c=document.createElement("form"),document.getElementsByTagName("body")[0].appendChild(c),b=document.createElement("textarea"),b.id="SWFUpload_Console",b.style.fontFamily="monospace",b.setAttribute("wrap","off"),b.wrap="off",b.style.overflow="auto",b.style.width="700px",b.style.height="350px",b.style.margin="5px",c.appendChild(b);b.value+=a+"\n";b.scrollTop=b.scrollHeight-b.clientHeight}catch(d){alert("Exception: "+
d.name+" Message: "+d.message)}};
if(typeof SWFUpload==="function")SWFUpload.queue={},SWFUpload.prototype.initSettings=function(a){return function(){typeof a==="function"&&a.call(this);this.queueSettings={};this.queueSettings.queue_cancelled_flag=!1;this.queueSettings.queue_upload_count=0;this.queueSettings.user_upload_complete_handler=this.settings.upload_complete_handler;this.queueSettings.user_upload_start_handler=this.settings.upload_start_handler;this.settings.upload_complete_handler=SWFUpload.queue.uploadCompleteHandler;this.settings.upload_start_handler=
SWFUpload.queue.uploadStartHandler;this.settings.queue_complete_handler=this.settings.queue_complete_handler||null}}(SWFUpload.prototype.initSettings),SWFUpload.prototype.startUpload=function(a){this.queueSettings.queue_cancelled_flag=!1;this.callFlash("StartUpload",[a])},SWFUpload.prototype.cancelQueue=function(){this.queueSettings.queue_cancelled_flag=!0;this.stopUpload();for(var a=this.getStats();a.files_queued>0;)this.cancelUpload(),a=this.getStats()},SWFUpload.queue.uploadStartHandler=function(a){var b;
typeof this.queueSettings.user_upload_start_handler==="function"&&(b=this.queueSettings.user_upload_start_handler.call(this,a));b=b===!1?!1:!0;this.queueSettings.queue_cancelled_flag=!b;return b},SWFUpload.queue.uploadCompleteHandler=function(a){var b=this.queueSettings.user_upload_complete_handler;a.filestatus===SWFUpload.FILE_STATUS.COMPLETE&&this.queueSettings.queue_upload_count++;if(typeof b==="function"?b.call(this,a)!==!1:a.filestatus!==SWFUpload.FILE_STATUS.QUEUED)this.getStats().files_queued>
0&&this.queueSettings.queue_cancelled_flag===!1?this.startUpload():(this.queueSettings.queue_cancelled_flag===!1?this.queueEvent("queue_complete_handler",[this.queueSettings.queue_upload_count]):this.queueSettings.queue_cancelled_flag=!1,this.queueSettings.queue_upload_count=0)};
function FileProgress(a,b){this.fileProgressID=a.id;this.opacity=100;this.height=0;if(this.fileProgressWrapper=document.getElementById(this.fileProgressID))this.fileProgressElement=this.fileProgressWrapper.firstChild,this.reset();else{this.fileProgressWrapper=document.createElement("div");this.fileProgressWrapper.className="progressWrapper";this.fileProgressWrapper.id=this.fileProgressID;this.fileProgressElement=document.createElement("div");this.fileProgressElement.className="progressContainer";
var c=document.createElement("a");c.className="progressCancel";c.href="#";c.style.visibility="hidden";c.appendChild(document.createTextNode(" "));var d=document.createElement("div");d.className="progressName";d.appendChild(document.createTextNode(a.name));var e=document.createElement("div");e.className="progressBarInProgress";var f=document.createElement("div");f.className="progressBarStatus";f.innerHTML="&nbsp;";this.fileProgressElement.appendChild(e);this.fileProgressElement.appendChild(c);this.fileProgressElement.appendChild(d);
this.fileProgressElement.appendChild(f);this.fileProgressWrapper.appendChild(this.fileProgressElement);document.getElementById(b).appendChild(this.fileProgressWrapper)}this.height=this.fileProgressWrapper.offsetHeight;this.setTimer(null)}FileProgress.prototype.setTimer=function(a){this.fileProgressElement.FP_TIMER=a};FileProgress.prototype.getTimer=function(){return this.fileProgressElement.FP_TIMER||null};
FileProgress.prototype.reset=function(){this.fileProgressElement.className="progressContainer";this.fileProgressElement.childNodes[3].innerHTML="&nbsp;";this.fileProgressElement.childNodes[3].className="progressBarStatus";this.fileProgressElement.childNodes[0].className="progressBarInProgress";this.fileProgressElement.childNodes[0].style.width="0%";this.appear()};
FileProgress.prototype.setProgress=function(a){this.fileProgressElement.className="progressContainer green";this.fileProgressElement.childNodes[0].className="progressBarInProgress";this.fileProgressElement.childNodes[0].style.width=a+"%";this.appear()};
FileProgress.prototype.setComplete=function(){this.fileProgressElement.className="progressContainer blue";this.fileProgressElement.childNodes[0].className="progressBarComplete";this.fileProgressElement.childNodes[0].style.width="100%";var a=this;this.setTimer(setTimeout(function(){a.disappear()},1E4))};
FileProgress.prototype.setError=function(){this.fileProgressElement.className="progressContainer red";this.fileProgressElement.childNodes[0].className="progressBarError";this.fileProgressElement.childNodes[0].style.width="";var a=this;this.setTimer(setTimeout(function(){a.disappear()},5E3))};
FileProgress.prototype.setCancelled=function(){this.fileProgressElement.className="progressContainer";this.fileProgressElement.childNodes[0].className="progressBarError";this.fileProgressElement.childNodes[0].style.width="";var a=this;this.setTimer(setTimeout(function(){a.disappear()},2E3))};FileProgress.prototype.setStatus=function(a){this.fileProgressElement.childNodes[3].innerHTML=a};
FileProgress.prototype.toggleCancel=function(a,b){this.fileProgressElement.childNodes[1].style.visibility=a?"visible":"hidden";if(b){var c=this.fileProgressID;this.fileProgressElement.childNodes[1].onclick=function(){b.cancelUpload(c);return!1}}};
FileProgress.prototype.appear=function(){this.getTimer()!==null&&(clearTimeout(this.getTimer()),this.setTimer(null));if(this.fileProgressWrapper.filters)try{this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity=100}catch(a){this.fileProgressWrapper.style.filter="progid:DXImageTransform.Microsoft.Alpha(opacity=100)"}else this.fileProgressWrapper.style.opacity=1;this.fileProgressWrapper.style.height="";this.height=this.fileProgressWrapper.offsetHeight;this.opacity=100;this.fileProgressWrapper.style.display=
""};
FileProgress.prototype.disappear=function(){if(this.opacity>0){this.opacity-=15;if(this.opacity<0)this.opacity=0;if(this.fileProgressWrapper.filters)try{this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity=this.opacity}catch(a){this.fileProgressWrapper.style.filter="progid:DXImageTransform.Microsoft.Alpha(opacity="+this.opacity+")"}else this.fileProgressWrapper.style.opacity=this.opacity/100}if(this.height>0){this.height-=4;if(this.height<0)this.height=0;this.fileProgressWrapper.style.height=this.height+
"px"}if(this.height>0||this.opacity>0){var b=this;this.setTimer(setTimeout(function(){b.disappear()},30))}else this.fileProgressWrapper.style.display="none",this.setTimer(null)};function formatBytes(a){for(var b=0;a>1024;)b++,a/=1024;a=parseFloat(a).toFixed(2);return a+["bytes","KB","MB","GB","TB"][b]}
function fileQueued(a){try{var b=new FileProgress(a,this.customSettings.progressTarget),c=formatBytes(a.size);b.setStatus('<font color="red">'+c+"</font> \u7b49\u5f85\u4e0a\u4f20......");b.toggleCancel(!0,this)}catch(d){this.debug(d)}}
function fileQueueError(a,b,c){try{if(b===SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED)alert("\u60a8\u7684\u4e0a\u4f20\u5217\u961f\u6587\u4ef6\u8fc7\u591a.\n"+(c===0?"\u5df2\u7ecf\u8fbe\u5230\u4e0a\u4f20\u9650\u5236.":"\u60a8\u53ef\u4ee5\u9009\u62e9 "+(c>1?" "+c+" \u4e2a\u6587\u4ef6.":"\u4e00\u4e2a\u6587\u4ef6.")));else{var d=new FileProgress(a,this.customSettings.progressTarget);d.setError();d.toggleCancel(!1);switch(b){case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:d.setStatus("\u4e0a\u4f20\u6587\u4ef6\u8fc7\u5927.");
this.debug("Error Code: File too big, File name: "+a.name+", File size: "+a.size+", Message: "+c);break;case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:d.setStatus("\u4e0d\u80fd\u4e0a\u4f20 0 \u5b57\u8282\u7684\u6587\u4ef6.");this.debug("Error Code: Zero byte file, File name: "+a.name+", File size: "+a.size+", Message: "+c);break;case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:d.setStatus("\u65e0\u6548\u7684\u6587\u4ef6\u7c7b\u578b.");this.debug("Error Code: Invalid File Type, File name: "+a.name+", File size: "+
a.size+", Message: "+c);break;default:a!==null&&d.setStatus("\u65e0\u6cd5\u5904\u7406\u7684\u9519\u8bef"),this.debug("Error Code: "+b+", File name: "+a.name+", File size: "+a.size+", Message: "+c)}}}catch(e){this.debug(e)}}function fileDialogComplete(a){try{if(a>0)document.getElementById(this.customSettings.uploadButtonId).disabled=!1}catch(b){this.debug(b)}}
function uploadStart(a){try{var b=new FileProgress(a,this.customSettings.progressTarget),c=formatBytes(a.size);b.setStatus('<font color="red">'+c+"</font> \u4e0a\u4f20\u5f00\u59cb......");b.toggleCancel(!0,this)}catch(d){}return!0}function uploadProgress(a,b,c){try{var d=Math.ceil(b/c*100),e=new FileProgress(a,this.customSettings.progressTarget),f=formatBytes(a.size);e.setProgress(d);e.setStatus('<font color="red">'+f+"</font> \u6b63\u5728\u4e0a\u4f20......"+d+"%")}catch(g){this.debug(g)}}
function uploadSuccess(a){try{var b=new FileProgress(a,this.customSettings.progressTarget),c=formatBytes(a.size);b.setComplete();b.setStatus('<font color="red">'+c+"</font> \u4e0a\u4f20\u6210\u529f.");b.toggleCancel(!1)}catch(d){this.debug(d)}}
function uploadError(a,b,c){try{var d=new FileProgress(a,this.customSettings.progressTarget);d.setError();d.toggleCancel(!1);switch(b){case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:d.setStatus("\u4e0a\u4f20\u9519\u8bef: "+c);this.debug("Error Code: HTTP Error, File name: "+a.name+", Message: "+c);break;case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:d.setStatus("\u4e0a\u4f20\u5931\u8d25.");this.debug("Error Code: Upload Failed, File name: "+a.name+", File size: "+a.size+", Message: "+c);break;case SWFUpload.UPLOAD_ERROR.IO_ERROR:d.setStatus("\u670d\u52a1\u5668 (IO) \u9519\u8bef");
this.debug("Error Code: IO Error, File name: "+a.name+", Message: "+c);break;case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:d.setStatus("\u5b89\u5168\u9519\u8bef");this.debug("Error Code: Security Error, File name: "+a.name+", Message: "+c);break;case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:d.setStatus("\u8d85\u8fc7\u4e0a\u4f20\u9650\u5236.");this.debug("Error Code: Upload Limit Exceeded, File name: "+a.name+", File size: "+a.size+", Message: "+c);break;case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:d.setStatus("\u672a\u901a\u8fc7\u6709\u6548\u6027\u9a8c\u8bc1\u3002\u4e0a\u4f20\u8df3\u8fc7\u3002");
this.debug("Error Code: File Validation Failed, File name: "+a.name+", File size: "+a.size+", Message: "+c);break;case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:if(this.getStats().files_queued===0)document.getElementById(this.customSettings.uploadButtonId).disabled=!0;d.setStatus("\u4e0a\u4f20\u88ab\u53d6\u6d88");d.setCancelled();break;case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:d.setStatus("\u4e0a\u4f20\u7ec8\u6b62");break;default:d.setStatus("\u65e0\u6cd5\u5904\u7406\u7684\u9519\u8bef: "+b),this.debug("Error Code: "+
b+", File name: "+a.name+", File size: "+a.size+", Message: "+c)}}catch(e){this.debug(e)}}function uploadComplete(){if(this.getStats().files_queued===0)document.getElementById(this.customSettings.uploadButtonId).disabled=!0,setTimeout(function(){SWFUploadHandler(2,"image")},1E3)}function queueComplete(a){document.getElementById("divStatus").innerHTML=a+" \u4e2a\u6587\u4ef6\u5df2\u4e0a\u4f20."};
