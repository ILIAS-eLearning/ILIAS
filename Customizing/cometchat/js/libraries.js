/*
 * CometChat
 * Copyright (c) 2014 Inscripts - support@cometchat.com | http://www.cometchat.com | http://www.inscripts.com
*/
 <?php if ($windowFavicon == 1) { ?>

/**
 * @license MIT
 * @fileOverview Favico animations
 * @author Miroslav Magda, http://blog.ejci.net
 * @version 0.3.3
 */
!function(){var e=function(e){"use strict";function t(e){if(e.paused||e.ended||w)return!1;try{d.clearRect(0,0,h,s),d.drawImage(e,0,0,h,s)}catch(o){}setTimeout(t,U.duration,e),L.setIcon(c)}function o(e){var t=/^#?([a-f\d])([a-f\d])([a-f\d])$/i;e=e.replace(t,function(e,t,o,n){return t+t+o+o+n+n});var o=/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(e);return o?{r:parseInt(o[1],16),g:parseInt(o[2],16),b:parseInt(o[3],16)}:!1}function n(e,t){var o,n={};for(o in e)n[o]=e[o];for(o in t)n[o]=t[o];return n}function r(){return document.hidden||document.msHidden||document.webkitHidden||document.mozHidden}e=e?e:{};var i,a,s,h,c,d,u,l,f,g,y,w,m,x={bgColor:"#d00",textColor:"#fff",fontFamily:"sans-serif",fontStyle:"bold",type:"circle",position:"down",animation:"slide",elementId:!1};m={},m.ff=/firefox/i.test(navigator.userAgent.toLowerCase()),m.chrome=/chrome/i.test(navigator.userAgent.toLowerCase()),m.opera=/opera/i.test(navigator.userAgent.toLowerCase()),m.ie=/msie/i.test(navigator.userAgent.toLowerCase())||/trident/i.test(navigator.userAgent.toLowerCase()),m.supported=m.chrome||m.ff||m.opera;var p=[];y=function(){},l=w=!1;var v=function(){if(i=n(x,e),i.bgColor=o(i.bgColor),i.textColor=o(i.textColor),i.position=i.position.toLowerCase(),i.animation=U.types[""+i.animation]?i.animation:x.animation,"up"===i.position)for(var t=0;t<U.types[""+i.animation].length;t++){var r=U.types[""+i.animation][t];r.y=r.y<.6?r.y-.4:r.y-2*r.y+(1-r.w),U.types[""+i.animation][t]=r}i.type=C[""+i.type]?i.type:x.type;try{a=L.getIcon(),c=document.createElement("canvas"),u=document.createElement("img"),a.hasAttribute("href")?(u.setAttribute("src",a.getAttribute("href")),u.onload=function(){s=u.height>0?u.height:32,h=u.width>0?u.width:32,c.height=s,c.width=h,d=c.getContext("2d"),b.ready()}):(u.setAttribute("src",""),s=32,h=32,u.height=s,u.width=h,c.height=s,c.width=h,d=c.getContext("2d"),b.ready())}catch(l){}},b={};b.ready=function(){l=!0,b.reset(),y()},b.reset=function(){p=[],f=!1,d.clearRect(0,0,h,s),d.drawImage(u,0,0,h,s),L.setIcon(c)},b.start=function(){if(l&&!g){var e=function(){f=p[0],g=!1,p.length>0&&(p.shift(),b.start())};p.length>0&&(g=!0,f?U.run(f.options,function(){U.run(p[0].options,function(){e()},!1)},!0):U.run(p[0].options,function(){e()},!1))}};var C={},M=function(e){return e.n=Math.abs(e.n),e.x=h*e.x,e.y=s*e.y,e.w=h*e.w,e.h=s*e.h,e};C.circle=function(e){e=M(e);var t=!1;e.n>9&&e.n<100?(e.x=e.x-.4*e.w,e.w=1.4*e.w,t=!0):e.n>=100&&(e.x=e.x-.65*e.w,e.w=1.65*e.w,t=!0),d.clearRect(0,0,h,s),d.drawImage(u,0,0,h,s),d.beginPath(),d.font=i.fontStyle+" "+Math.floor(e.h*(e.n>99?.85:1))+"px "+i.fontFamily,d.textAlign="center",t?(d.moveTo(e.x+e.w/2,e.y),d.lineTo(e.x+e.w-e.h/2,e.y),d.quadraticCurveTo(e.x+e.w,e.y,e.x+e.w,e.y+e.h/2),d.lineTo(e.x+e.w,e.y+e.h-e.h/2),d.quadraticCurveTo(e.x+e.w,e.y+e.h,e.x+e.w-e.h/2,e.y+e.h),d.lineTo(e.x+e.h/2,e.y+e.h),d.quadraticCurveTo(e.x,e.y+e.h,e.x,e.y+e.h-e.h/2),d.lineTo(e.x,e.y+e.h/2),d.quadraticCurveTo(e.x,e.y,e.x+e.h/2,e.y)):d.arc(e.x+e.w/2,e.y+e.h/2,e.h/2,0,2*Math.PI),d.fillStyle="rgba("+i.bgColor.r+","+i.bgColor.g+","+i.bgColor.b+","+e.o+")",d.fill(),d.closePath(),d.beginPath(),d.stroke(),d.fillStyle="rgba("+i.textColor.r+","+i.textColor.g+","+i.textColor.b+","+e.o+")",e.n>999?d.fillText((e.n>9999?9:Math.floor(e.n/1e3))+"k+",Math.floor(e.x+e.w/2),Math.floor(e.y+e.h-.2*e.h)):d.fillText(e.n,Math.floor(e.x+e.w/2),Math.floor(e.y+e.h-.15*e.h)),d.closePath()},C.rectangle=function(e){e=M(e);var t=!1;e.n>9&&e.n<100?(e.x=e.x-.4*e.w,e.w=1.4*e.w,t=!0):e.n>=100&&(e.x=e.x-.65*e.w,e.w=1.65*e.w,t=!0),d.clearRect(0,0,h,s),d.drawImage(u,0,0,h,s),d.beginPath(),d.font="bold "+Math.floor(e.h*(e.n>99?.9:1))+"px sans-serif",d.textAlign="center",d.fillStyle="rgba("+i.bgColor.r+","+i.bgColor.g+","+i.bgColor.b+","+e.o+")",d.fillRect(e.x,e.y,e.w,e.h),d.fillStyle="rgba("+i.textColor.r+","+i.textColor.g+","+i.textColor.b+","+e.o+")",e.n>999?d.fillText((e.n>9999?9:Math.floor(e.n/1e3))+"k+",Math.floor(e.x+e.w/2),Math.floor(e.y+e.h-.2*e.h)):d.fillText(e.n,Math.floor(e.x+e.w/2),Math.floor(e.y+e.h-.15*e.h)),d.closePath()};var I=function(e,t){y=function(){try{if(e>0){if(U.types[""+t]&&(i.animation=t),p.push({type:"badge",options:{n:e}}),p.length>100)throw"Too many badges requests in queue.";b.start()}else b.reset()}catch(o){throw"Error setting badge. Message: "+o.message}},l&&y()},A=function(e){y=function(){try{var t=e.width,o=e.height,n=document.createElement("img"),r=o/s>t/h?t/h:o/s;n.setAttribute("src",e.getAttribute("src")),n.height=o/r,n.width=t/r,d.clearRect(0,0,h,s),d.drawImage(n,0,0,h,s),L.setIcon(c)}catch(i){throw"Error setting image. Message: "+i.message}},l&&y()},E=function(e){y=function(){try{if("stop"===e)return w=!0,b.reset(),w=!1,void 0;e.addEventListener("play",function(){t(this)},!1)}catch(o){throw"Error setting video. Message: "+o.message}},l&&y()},T=function(e){if(window.URL&&window.URL.createObjectURL||(window.URL=window.URL||{},window.URL.createObjectURL=function(e){return e}),m.supported){var o=!1;navigator.getUserMedia=navigator.getUserMedia||navigator.oGetUserMedia||navigator.msGetUserMedia||navigator.mozGetUserMedia||navigator.webkitGetUserMedia,y=function(){try{if("stop"===e)return w=!0,b.reset(),w=!1,void 0;o=document.createElement("video"),o.width=h,o.height=s,navigator.getUserMedia({video:!0,audio:!1},function(e){o.src=URL.createObjectURL(e),o.play(),t(o)},function(){})}catch(n){throw"Error setting webcam. Message: "+n.message}},l&&y()}},L={};L.getIcon=function(){var e=!1,t="",o=function(){for(var e=document.getElementsByTagName("head")[0].getElementsByTagName("link"),t=e.length,o=t-1;o>=0;o--)if(/icon/i.test(e[o].getAttribute("rel")))return e[o];return!1};if(i.elementId?(e=document.getElementById(i.elementId),e.setAttribute("href",e.getAttribute("src"))):(e=o(),e===!1&&(e=document.createElement("link"),e.setAttribute("rel","icon"),document.getElementsByTagName("head")[0].appendChild(e))),t=i.elementId?e.src:e.href,-1===t.indexOf(document.location.hostname))throw new Error("Error setting favicon. Favicon image is on different domain (Icon: "+t+", Domain: "+document.location.hostname+")");return e.setAttribute("type","image/png"),e},L.setIcon=function(e){var t=e.toDataURL("image/png");if(i.elementId)document.getElementById(i.elementId).setAttribute("src",t);else if(m.ff||m.opera){var o=a;a=document.createElement("link"),m.opera&&a.setAttribute("rel","icon"),a.setAttribute("rel","icon"),a.setAttribute("type","image/png"),document.getElementsByTagName("head")[0].appendChild(a),a.setAttribute("href",t),o.parentNode&&o.parentNode.removeChild(o)}else a.setAttribute("href",t)};var U={};return U.duration=40,U.types={},U.types.fade=[{x:.4,y:.4,w:.6,h:.6,o:0},{x:.4,y:.4,w:.6,h:.6,o:.1},{x:.4,y:.4,w:.6,h:.6,o:.2},{x:.4,y:.4,w:.6,h:.6,o:.3},{x:.4,y:.4,w:.6,h:.6,o:.4},{x:.4,y:.4,w:.6,h:.6,o:.5},{x:.4,y:.4,w:.6,h:.6,o:.6},{x:.4,y:.4,w:.6,h:.6,o:.7},{x:.4,y:.4,w:.6,h:.6,o:.8},{x:.4,y:.4,w:.6,h:.6,o:.9},{x:.4,y:.4,w:.6,h:.6,o:1}],U.types.none=[{x:.4,y:.4,w:.6,h:.6,o:1}],U.types.pop=[{x:1,y:1,w:0,h:0,o:1},{x:.9,y:.9,w:.1,h:.1,o:1},{x:.8,y:.8,w:.2,h:.2,o:1},{x:.7,y:.7,w:.3,h:.3,o:1},{x:.6,y:.6,w:.4,h:.4,o:1},{x:.5,y:.5,w:.5,h:.5,o:1},{x:.4,y:.4,w:.6,h:.6,o:1}],U.types.popFade=[{x:.75,y:.75,w:0,h:0,o:0},{x:.65,y:.65,w:.1,h:.1,o:.2},{x:.6,y:.6,w:.2,h:.2,o:.4},{x:.55,y:.55,w:.3,h:.3,o:.6},{x:.5,y:.5,w:.4,h:.4,o:.8},{x:.45,y:.45,w:.5,h:.5,o:.9},{x:.4,y:.4,w:.6,h:.6,o:1}],U.types.slide=[{x:.4,y:1,w:.6,h:.6,o:1},{x:.4,y:.9,w:.6,h:.6,o:1},{x:.4,y:.9,w:.6,h:.6,o:1},{x:.4,y:.8,w:.6,h:.6,o:1},{x:.4,y:.7,w:.6,h:.6,o:1},{x:.4,y:.6,w:.6,h:.6,o:1},{x:.4,y:.5,w:.6,h:.6,o:1},{x:.4,y:.4,w:.6,h:.6,o:1}],U.run=function(e,t,o,a){var s=U.types[r()?"none":i.animation];return a=o===!0?"undefined"!=typeof a?a:s.length-1:"undefined"!=typeof a?a:0,t=t?t:function(){},a<s.length&&a>=0?(C[i.type](n(e,s[a])),setTimeout(function(){o?a-=1:a+=1,U.run(e,t,o,a)},U.duration),L.setIcon(c),void 0):(t(),void 0)},v(),{badge:I,video:E,image:A,webcam:T,reset:b.reset}};"undefined"!=typeof define&&define.amd?define([],function(){return e}):"undefined"!=typeof module&&module.exports?module.exports=e:this.Favico=e}();
<?php } ?>

if(typeof(jqcc) === 'undefined') {
	jqcc = jQuery;
}
// Copyright (c) 2006 Klaus Hartl (stilbuero.de)
// http://www.opensource.org/licenses/mit-license.php

jqcc.cookie=function(a,b,c){if(typeof b!='undefined'){c=c||{};if(b===null){b='';c.expires=-1}var d='';if(c.expires&&(typeof c.expires=='number'||c.expires.toUTCString)){var e;if(typeof c.expires=='number'){e=new Date();e.setTime(e.getTime()+(c.expires*24*60*60*1000))}else{e=c.expires}d='; expires='+e.toUTCString()}var f=c.path?'; path='+(c.path):'';var g=c.domain?'; domain='+(c.domain):'';var h=c.secure?'; secure':'';document.cookie=[a,'=',encodeURIComponent(b),d,f,g,h].join('')}else{var j=null;if(document.cookie&&document.cookie!=''){var k=document.cookie.split(';');for(var i=0;i<k.length;i++){var l=jqcc.trim(k[i]);if(l.substring(0,a.length+1)==(a+'=')){j=decodeURIComponent(l.substring(a.length+1));break}}}return j}};

// SWFObject is (c) 2007 Geoff Stearns and is released under the MIT License
// http://www.opensource.org/licenses/mit-license.php

if(typeof deconcept=="undefined"){var deconcept=new Object();}if(typeof deconcept.util=="undefined"){deconcept.util=new Object();}if(typeof deconcept.SWFObjectCCUtil=="undefined"){deconcept.SWFObjectCCUtil=new Object();}deconcept.SWFObjectCC=function(_1,id,w,h,_5,c,_7,_8,_9,_a){if(!document.getElementById){return;}this.DETECT_KEY=_a?_a:"detectflash";this.skipDetect=deconcept.util.getRequestParameter(this.DETECT_KEY);this.params=new Object();this.variables=new Object();this.attributes=new Array();if(_1){this.setAttribute("swf",_1);}if(id){this.setAttribute("id",id);}if(w){this.setAttribute("width",w);}if(h){this.setAttribute("height",h);}if(_5){this.setAttribute("version",new deconcept.PlayerVersion(_5.toString().split(".")));}this.installedVer=deconcept.SWFObjectCCUtil.getPlayerVersion();if(!window.opera&&document.all&&this.installedVer.major>7){deconcept.SWFObjectCC.doPrepUnload=true;}if(c){this.addParam("bgcolor",c);}var q=_7?_7:"high";this.addParam("quality",q);this.setAttribute("useExpressInstall",false);this.setAttribute("doExpressInstall",false);var _c=(_8)?_8:window.location;this.setAttribute("xiRedirectUrl",_c);this.setAttribute("redirectUrl","");if(_9){this.setAttribute("redirectUrl",_9);}};deconcept.SWFObjectCC.prototype={useExpressInstall:function(_d){this.xiSWFPath=!_d?"expressinstall.swf":_d;this.setAttribute("useExpressInstall",true);},setAttribute:function(_e,_f){this.attributes[_e]=_f;},getAttribute:function(_10){return this.attributes[_10];},addParam:function(_11,_12){this.params[_11]=_12;},getParams:function(){return this.params;},addVariable:function(_13,_14){this.variables[_13]=_14;},getVariable:function(_15){return this.variables[_15];},getVariables:function(){return this.variables;},getVariablePairs:function(){var _16=new Array();var key;var _18=this.getVariables();for(key in _18){_16[_16.length]=key+"="+_18[key];}return _16;},getSWFHTML:function(){var _19="";if(navigator.plugins&&navigator.mimeTypes&&navigator.mimeTypes.length){if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","PlugIn");this.setAttribute("swf",this.xiSWFPath);}_19="<embed type=\"application/x-shockwave-flash\" src=\""+this.getAttribute("swf")+"\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\" style=\""+this.getAttribute("style")+"\"";_19+=" id=\""+this.getAttribute("id")+"\" name=\""+this.getAttribute("id")+"\" ";var _1a=this.getParams();for(var key in _1a){_19+=[key]+"=\""+_1a[key]+"\" ";}var _1c=this.getVariablePairs().join("&");if(_1c.length>0){_19+="flashvars=\""+_1c+"\"";}_19+="/>";}else{if(this.getAttribute("doExpressInstall")){this.addVariable("MMplayerType","ActiveX");this.setAttribute("swf",this.xiSWFPath);}_19="<object id=\""+this.getAttribute("id")+"\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" width=\""+this.getAttribute("width")+"\" height=\""+this.getAttribute("height")+"\" style=\""+this.getAttribute("style")+"\">";_19+="<param name=\"movie\" value=\""+this.getAttribute("swf")+"\" />";var _1d=this.getParams();for(var key in _1d){_19+="<param name=\""+key+"\" value=\""+_1d[key]+"\" />";}var _1f=this.getVariablePairs().join("&");if(_1f.length>0){_19+="<param name=\"flashvars\" value=\""+_1f+"\" />";}_19+="</object>";}return _19;},write:function(_20){if(this.getAttribute("useExpressInstall")){var _21=new deconcept.PlayerVersion([6,0,65]);if(this.installedVer.versionIsValid(_21)&&!this.installedVer.versionIsValid(this.getAttribute("version"))){this.setAttribute("doExpressInstall",true);this.addVariable("MMredirectURL",escape(this.getAttribute("xiRedirectUrl")));document.title=document.title.slice(0,47)+" - Flash Player Installation";this.addVariable("MMdoctitle",document.title);}}if(this.skipDetect||this.getAttribute("doExpressInstall")||this.installedVer.versionIsValid(this.getAttribute("version"))){var n=(typeof _20=="string")?document.getElementById(_20):_20;n.innerHTML=this.getSWFHTML();return true;}else{if(this.getAttribute("redirectUrl")!=""){document.location.replace(this.getAttribute("redirectUrl"));}}return false;}};deconcept.SWFObjectCCUtil.getPlayerVersion=function(){var _23=new deconcept.PlayerVersion([0,0,0]);if(navigator.plugins&&navigator.mimeTypes.length){var x=navigator.plugins["Shockwave Flash"];if(x&&x.description){_23=new deconcept.PlayerVersion(x.description.replace(/([a-zA-Z]|\s)+/,"").replace(/(\s+r|\s+b[0-9]+)/,".").split("."));}}else{if(navigator.userAgent&&navigator.userAgent.indexOf("Windows CE")>=0){var axo=1;var _26=3;while(axo){try{_26++;axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+_26);_23=new deconcept.PlayerVersion([_26,0,0]);}catch(e){axo=null;}}}else{try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");}catch(e){try{var axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");_23=new deconcept.PlayerVersion([6,0,21]);axo.AllowScriptAccess="always";}catch(e){if(_23.major==6){return _23;}}try{axo=new ActiveXObject("ShockwaveFlash.ShockwaveFlash");}catch(e){}}if(axo!=null){_23=new deconcept.PlayerVersion(axo.GetVariable("$version").split(" ")[1].split(","));}}}return _23;};deconcept.PlayerVersion=function(_29){this.major=_29[0]!=null?parseInt(_29[0]):0;this.minor=_29[1]!=null?parseInt(_29[1]):0;this.rev=_29[2]!=null?parseInt(_29[2]):0;};deconcept.PlayerVersion.prototype.versionIsValid=function(fv){if(this.major<fv.major){return false;}if(this.major>fv.major){return true;}if(this.minor<fv.minor){return false;}if(this.minor>fv.minor){return true;}if(this.rev<fv.rev){return false;}return true;};deconcept.util={getRequestParameter:function(_2b){var q=document.location.search||document.location.hash;if(_2b==null){return q;}if(q){var _2d=q.substring(1).split("&");for(var i=0;i<_2d.length;i++){if(_2d[i].substring(0,_2d[i].indexOf("="))==_2b){return _2d[i].substring((_2d[i].indexOf("=")+1));}}}return "";}};deconcept.SWFObjectCCUtil.cleanupSWFs=function(){var _2f=document.getElementsByTagName("OBJECT");for(var i=_2f.length-1;i>=0;i--){_2f[i].style.display="none";for(var x in _2f[i]){if(typeof _2f[i][x]=="function"){_2f[i][x]=function(){};}}}};if(deconcept.SWFObjectCC.doPrepUnload){if(!deconcept.unloadSet){deconcept.SWFObjectCCUtil.prepUnload=function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){};window.attachEvent("onunload",deconcept.SWFObjectCCUtil.cleanupSWFs);};window.attachEvent("onbeforeunload",deconcept.SWFObjectCCUtil.prepUnload);deconcept.unloadSet=true;}}if(!document.getElementById&&document.all){document.getElementById=function(id){return document.all[id];};}var getQueryParamValue=deconcept.util.getRequestParameter;var FlashObject=deconcept.SWFObjectCC;var SWFObjectCC=deconcept.SWFObjectCC;


/**
 * Copyright (c) 2007-2009 Ariel Flesler - aflesler(at)gmail(dot)com
 * http://flesler.blogspot.com/2007/10/jqccscrollto.html
 */

(function($){var h=$.scrollToCC=function(a,b,c){$(window).scrollToCC(a,b,c)};h.defaults={axis:'xy',duration:parseFloat($.fn.jqcc)>=1.3?0:1};h.window=function(a){return $(window)._scrollable()};$.fn._scrollable=function(){return this.map(function(){var a=this,isWin=!a.nodeName||$.inArray(a.nodeName.toLowerCase(),['iframe','#document','html','body'])!=-1;if(!isWin)return a;var b=(a.contentWindow||a).document||a.ownerDocument||a;return $.browser.safari||b.compatMode=='BackCompat'?b.body:b.documentElement})};$.fn.scrollToCC=function(e,f,g){if(typeof f=='object'){g=f;f=0}if(typeof g=='function')g={onAfter:g};if(e=='max')e=9e9;g=$.extend({},h.defaults,g);f=f||g.speed||g.duration;g.queue=g.queue&&g.axis.length>1;if(g.queue)f/=2;g.offset=both(g.offset);g.over=both(g.over);return this._scrollable().each(function(){var d=this,$elem=$(d),targ=e,toff,attr={},win=$elem.is('html,body');switch(typeof targ){case'number':case'string':if((/^([+-]=)?\d+(\.\d+)?(px|%)?$/.test(targ)) || (targ.charAt(0)=='-' && targ.charAt(1)!='=') ){targ=both(targ);break}targ=$(targ,this);case'object':if(targ.is||targ.style)toff=(targ=$(targ)).offset()}$.each(g.axis.split(''),function(i,a){var b=a=='x'?'Left':'Top',pos=b.toLowerCase(),key='scroll'+b,old=d[key],max=h.max(d,a);if(toff){attr[key]=toff[pos]+(win?0:old-$elem.offset()[pos]);if(g.margin){attr[key]-=parseInt(targ.css('margin'+b))||0;attr[key]-=parseInt(targ.css('border'+b+'Width'))||0}attr[key]+=g.offset[pos]||0;if(g.over[pos])attr[key]+=targ[a=='x'?'width':'height']()*g.over[pos]}else{var c=targ[pos];attr[key]=c.slice&&c.slice(-1)=='%'?parseFloat(c)/100*max:c}if(/^\d+$/.test(attr[key]))attr[key]=attr[key]<=0?0:Math.min(attr[key],max);if(!i&&g.queue){if(old!=attr[key])animate(g.onAfterFirst);delete attr[key]}});animate(g.onAfter);function animate(a){$elem.animate(attr,f,g.easing,a&&function(){a.call(this,e,g)})}}).end()};h.max=function(a,b){var c=b=='x'?'Width':'Height',scroll='scroll'+c;if(!$(a).is('html,body'))return a[scroll]-$(a)[c.toLowerCase()]();var d='client'+c,html=a.ownerDocument.documentElement,body=a.ownerDocument.body;return Math.max(html[scroll],body[scroll])-Math.min(html[d],body[d])};function both(a){return typeof a=='object'?a:{top:a,left:a}}})(jqcc);

/*
 jqcc.fullscreen 1.1.4
 https://github.com/kayahr/jqcc-fullscreen-plugin
 Copyright (C) 2012 Klaus Reimer <k@ailis.de>
 Licensed under the MIT license
 (See http://www.opensource.org/licenses/mit-license)
*/
function d(b){var c,a;if(!this.length)return this;c=this[0];c.ownerDocument?a=c.ownerDocument:(a=c,c=a.documentElement);if(null==b){if(!a.cancelFullScreen&&!a.webkitCancelFullScreen&&!a.mozCancelFullScreen)return null;b=!!a.fullScreen||!!a.webkitIsFullScreen||!!a.mozFullScreen;return!b?b:a.fullScreenElement||a.webkitCurrentFullScreenElement||a.mozFullScreenElement||b}b?(b=c.requestFullScreen||c.webkitRequestFullScreen||c.mozRequestFullScreen)&&(Element.ALLOW_KEYBOARD_INPUT?b.call(c,Element.ALLOW_KEYBOARD_INPUT):
b.call(c)):(b=a.cancelFullScreen||a.webkitCancelFullScreen||a.mozCancelFullScreen)&&b.call(a);return this}jqcc.fn.fullScreen=d;jqcc.fn.toggleFullScreen=function(){return d.call(this,!d.call(this))};var e,f,g;e=document;e.webkitCancelFullScreen?(f="webkitfullscreenchange",g="webkitfullscreenerror"):e.mozCancelFullScreen?(f="mozfullscreenchange",g="mozfullscreenerror"):(f="fullscreenchange",g="fullscreenerror");jqcc(document).bind(f,function(){jqcc(document).trigger(new jqcc.Event("fullscreenchange"))});
jqcc(document).bind(g,function(){jqcc(document).trigger(new jqcc.Event("fullscreenerror"))});

var cc_zindex = 0;

if(typeof closeCCPopup === "undefined") {
	var type = "<?php echo $type; ?>";
	var name = "<?php echo $name; ?>";
	var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
	var eventer = window[eventMethod];
	var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

// Listen to message from child window

	switch(type){
	    case "extension":
	    break;
	    case "module":
	    	switch(name){
	    		case "chatrooms":
	    			eventer(messageEvent,function(e) {
				        if(typeof(e.data) != 'undefined'){
				        	if(e.data.indexOf('CC^CONTROL_')!== -1){
				                var controlparameters = e.data.split('CC^CONTROL_')[1];
				                controlparameters = JSON.parse(controlparameters);
				                if(controlparameters.name == 'cometchat' && controlparameters.method == 'processcontrolmessage'){
				                	/* Chatroom ProcessControlMessage Call */
				                	var message = jqcc[controlparameters.name][controlparameters.method](controlparameters.item);
				                	/* Return post Message incase of Chat History plugin */
								    var returnparameters = {"message":message, "item":controlparameters.item, "processcontrolmessageResponse":1};
								    e.source.postMessage(returnparameters,'*');
				                } else if(controlparameters.name == "cometchat" && controlparameters.method == "setInternalVariable"){
				                	/* This will send setInternalVariable  call from Chatroom to main CometChat to set a variable for A/V Chat calls..  */
				                	var controlparameters = {"type":"plugins", "name":"cometchat", "method":"setInternalVariable", "params":{"type":controlparameters.params.type, "grp":controlparameters.params.grp, "value":controlparameters.params.value}};
			                            controlparameters = JSON.stringify(controlparameters);
				                	if(typeof(parent) != 'undefined'){
			                            parent.postMessage('CC^CONTROL_'+controlparameters,'*');
				                	}else{
				                		window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
				                	}
				                } else if(controlparameters.name == "cometchat" && controlparameters.method == "chatWith") {
				                	/* chatWith call of Chatroom loadChatroomPro option to main CometChat. */
				                	var controlparameters = {"type":"modules", "name":"cometchat", "method":"chatWith", "params":{"uid":controlparameters.params.uid,"chatroommode":"0"}};
		        					controlparameters = JSON.stringify(controlparameters);
		        					window.opener.postMessage('CC^CONTROL_'+controlparameters,'*');
				                } else if(controlparameters.name == "cometchat" && (controlparameters.method == "kickChatroomUser" || controlparameters.method == "banChatroomUser")){
				                	/* Chatroom Kick/Ban API calls. */
				                		jqcc[controlparameters.name][controlparameters.method](controlparameters.params.uid,0);
				                } else if(controlparameters.method == "checkChatroomPass") {
				                	/* Call to checkChatroomPass API of chatroom incase of password protected chatrooms. */
				                	jqcc[controlparameters.name][controlparameters.method](controlparameters.params.id, controlparameters.params.name, controlparameters.params.silent, controlparameters.params.password);
				                } else if(controlparameters.type == "plugins" && controlparameters.method == "closeCCPopup"){
				                	/* Chatroom plugins closeCCPopup call. */
				                	closeCCPopup(controlparameters.name);
		                		} else if(controlparameters.method == "checkCometChat"){
		                			/* This will set checkBarEnabled=1 if CometChat bar is present with embedded chatroom. */
		                			jqcc.cometchat.setChatroomVars('checkBarEnabled',controlparameters.params.enabled);
		                		} else if(controlparameters.type == "module" && controlparameters.name == "chatrooms" && controlparameters.method == "resizeCCPopup") {
		                			/* ResizeCCPopup call for all popups in Chatrooms */
				                	window[controlparameters.method](controlparameters.params.id, controlparameters.params.height, controlparameters.params.width);
				                } else if(controlparameters.type == "themes" && controlparameters.method == "loggedout") {
				                	/* Run chatroom heartbeat after logout from Social Login. This will also Logout user from Chatrooms. */
				                	jqcc.cometchat.chatroomHeartbeat();
				                } else {
				                	/* All remaining calls of Chatrooms API's. */
		                			jqcc[controlparameters.name][controlparameters.method](controlparameters.params);
				                }
				            }
				        }
				    },false);
	    		break;
	    	}
		break;
	    case "plugin":
	    	switch(name){
	    		case "chathistory":
	    			eventer(messageEvent,function(e) {
					    if(typeof(e.data)!= 'undefined' && e.data.processcontrolmessageResponse == 1){
					    	/* This will append Processed messages on Chathistory plugin popup. */
					    	jqcc("#"+e.data.item.id).find('.chatmessage.chatmessage_short').html(e.data.message);
					        jqcc("#"+e.data.item.id).find('.chatmessage.chatnowrap').html(e.data.message);
					    }
					},false);
	    		break;
	    	}
	    break;
	    default:
	    	eventer(messageEvent,function(e) {
		        if(typeof(e.data) != 'undefined'){
		        	if(e.data.indexOf('ccmobile_reinitializeauth')!== -1){
		                jqcc.ccmobiletab.reinitialize();
		            }else if(e.data.indexOf('cc_reinitializeauth')!== -1){
		            	if(typeof(jqcc.cometchat) != 'undefined') {
			                jqcc.cometchat.reinitialize();
			                jqcc('#cometchat_userstab').click();
			                jqcc('#cometchat_auth_popup').removeClass('cometchat_tabopen');
			                jqcc('#cometchat_optionsbutton').removeClass('cometchat_tabclick');
			                if(jqcc('#cometchat_trayicon_chatrooms_iframe').length > 0){
			                	jqcc('#cometchat_trayicon_chatrooms_iframe').attr('src', jqcc('#cometchat_trayicon_chatrooms_iframe').attr('src'));
			                }
			            }
			            if(jqcc('#cometchat_chatrooms_iframe').length > 0){
		                	jqcc('#cometchat_chatrooms_iframe').attr('src', jqcc('#cometchat_chatrooms_iframe').attr('src'));
		                }
		            }else if(e.data.indexOf('alert')!== -1){
		                if(typeof(e.data.split('^')[1]) != 'undefined'){
		                    alert(e.data.split('^')[1]);
		                }
		            }else if(e.data.indexOf('webrtcNoti')!== -1){
		                if(typeof(e.data.split('^')[1]) != 'undefined' && e.data.split('^')[1] == 'add'){
		                    if(typeof(e.data.split('^')[2]) != 'undefined' && e.data.split('^')[2] == 'chrome'){
		                        jqcc(document).find('body').prepend('<div id="webrtcArrow" onclick="this.remove();" style="position:fixed;width:100%;height: 100%;margin: 0px;top: 0;left: 0;background: rgba(0,0,0,0.6);z-index: 90000000;text-align: center;"><img src="'+baseUrl+'images/notifyarrow.png"></div>');
		                    }else{
		                        jqcc(document).find('body').prepend('<div id="webrtcArrow" onclick="this.remove();" style="position:fixed;width:100%;height: 100%;margin: 0px;top: 0;left: 0;background: rgba(0,0,0,0.6);z-index: 90000000;text-align: center;"></div>');
		                    }
		                }
		                if(typeof(e.data.split('^')[1]) != 'undefined' && e.data.split('^')[1] == 'remove'){
		                    jqcc(document).find("#webrtcArrow").remove();
		                }
		            }else if(e.data.indexOf('CC^CONTROL_')!== -1){
		            	var controlparameters = e.data.slice(11);
		                controlparameters = JSON.parse(controlparameters);
		                if(controlparameters.type == "module" && controlparameters.name == "chatrooms" && controlparameters.method == "resizeCCPopup") {
		                	/* resizeCCPopup call for all CometChat popups */
		                	window[controlparameters.method](controlparameters.params.id, controlparameters.params.height, controlparameters.params.width);
		                } else if(controlparameters.method == "closeCCPopup"){
		                	/* closeCCPopup call for all CometChat popups */
		                	closeCCPopup(controlparameters.name);
		                } else if(controlparameters.type == "plugins" && controlparameters.name == "cometchat" && controlparameters.method == "processcontrolmessage"){
		                	/* call to Chathistory processControlMessage function. */
		                	var message = jqcc[controlparameters.name][controlparameters.method](controlparameters.item);
		                	/* Processed messages will be sent back to ChatHistory plugin window. */
		                	var returnparameters = {"message":message, "item":controlparameters.item, "processcontrolmessageResponse":1};
						    e.source.postMessage(returnparameters,'*');
		                } else if(controlparameters.type == "plugins" && controlparameters.name == "cometchat" && controlparameters.method == "setInternalVariable"){
		                	/* CometChat setInternalVariable call to set A/V chat, Broadcast plugins variables. */
		                	jqcc[controlparameters.name][controlparameters.method](controlparameters.params.type+'_'+controlparameters.params.grp,controlparameters.params.value);
		                } else if(controlparameters.type == "modules" && controlparameters.name == "cometchat" && controlparameters.method == "addMessage") {
		                	/* Broadcast message module addMessage API call. */
		                	jqcc[controlparameters.name][controlparameters.method](controlparameters.params.from, controlparameters.params.message, controlparameters.params.messageId, controlparameters.params.nopopup);
		                } else if(controlparameters.type == "modules" && controlparameters.name == "share" && controlparameters.method == "setTitle") {
		                	/* setTitle API call of Transliterate plugin and Share Module. */
		                	var parenttitle = document.title;
					        var parenturl = document.location.href;
					        var addthis_share =
					        {
					            url:parenturl,
					            title:parenttitle,
					            templates: {
					                twitter: '{{title}}: {{url}}'
					            }
					        }
		                } else if(controlparameters.type == "modules" && controlparameters.method == "closeModule") {
		                	/* closeModule calls for Theme Changer and Translate Page modules. */
		                	if(controlparameters.name == "themechanger"){
		                		location.reload();
							} else if(controlparameters.name == "translate2") {
								jqcc('#MSTTExitLink').click();
							}
		                	jqcc.cometchat.closeModule(controlparameters.name);
		                } else if(controlparameters.type == "modules" && controlparameters.name == "translatepage") {
		                	/* Translate Page module function calls */
		                	if(typeof(controlparameters.params.lang) == 'undefined'){
		                		/* Call to addLanguageCode function */
		                		window[controlparameters.method]();
		                	} else {
		                		/* Call to changeLanguage function */
		                		window[controlparameters.method](controlparameters.params.lang);
		                	}
		                } else if(controlparameters.method == "checkChatroomPass") {
		                	/* Call to checkChatroomPass API of chatroom incase of password protected chatrooms for Synergy theme. */
		                	jqcc[controlparameters.name][controlparameters.method](controlparameters.params.id, controlparameters.params.name, controlparameters.params.silent, controlparameters.params.password);
		                } else if(controlparameters.name == "core") {
		                	/* LoadCCPopup function call */
		                	if(typeof(jqcc.cometchat) == "undefined"){
		                		/* In case of embedded Synergy theme without CometChat bar, return postMessage will be sent to Synergy iFrame which will open all plugins and modules in window mode. */
		                		var returnparameters = {"type":"modules", "name":"core", "method":"loadCCPopup", "params":{"url": controlparameters.params.url, "name":controlparameters.params.name, "properties":controlparameters.params.properties, "width":controlparameters.params.width, "height":controlparameters.params.height, "title":controlparameters.params.title, "force":null, "allowmaximize":null, "allowresize":null, "allowpopout":null, "windowMode":1}};
		                        returnparameters = JSON.stringify(returnparameters);
		                        jqcc('#cometchat_synergy_iframe')[0].contentWindow.postMessage('CC^CONTROL_'+returnparameters,'*');
		                	} else {
		                		/* LoadCCPopup function call for Synergy theme. */
		                		loadCCPopup(controlparameters.params.url, controlparameters.params.name, controlparameters.params.properties, controlparameters.params.width, controlparameters.params.height, controlparameters.params.title, controlparameters.params.force, controlparameters.params.allowmaximize, controlparameters.params.allowresize, controlparameters.params.allowpopout, controlparameters.params.windowMode);
		                	}
		                } else if(controlparameters.type == "modules" && controlparameters.name == "cometchat" && controlparameters.method == "lightbox") {
		                	/* jqcc.cometchat.lightbox API call in Embedded Synergy theme for all Modules. */
		                	if(typeof(jqcc[controlparameters.name]) == "undefined"){
		                		/* In case of embedded Synergy theme without CometChat bar, return postMessage will be sent to Synergy iFrame which will open lightbox in window mode. */
		                		/* This call is handled below in else block. */
		                		var controlparameters = {"type":"modules", "name":"cometchat", "method":"lightbox", "params":{"moduleName":controlparameters.params.moduleName, "windowMode":"1"}};
		                        controlparameters = JSON.stringify(controlparameters);
		                        jqcc('#cometchat_synergy_iframe')[0].contentWindow.postMessage('CC^CONTROL_'+controlparameters,'*');
		                	} else {
		                		if(typeof(controlparameters.params.windowMode)=="undefined"){
		                			/* Call to lightbox API call if CometChat bar is present with embedded synergy. */
		                			jqcc[controlparameters.name][controlparameters.method](controlparameters.params.moduleName);
		                		}else{
		                			/* Call to lightbox API call if CometChat bar is not present with embedded synergy. */
		                			/* This will open all modules in window mode. */
		                			jqcc[controlparameters.name][controlparameters.method](controlparameters.params.moduleName,controlparameters.params.windowMode);
		                		}
		                	}
		                } else if(controlparameters.type == "modules" && controlparameters.name == "cometchat" && typeof(controlparameters.params.allowed) == 'undefined') {
		                	/* controlparameters.params.allowed is used for Kick/Ban chatroom calls. */
		                		if(controlparameters.method == "chatWith"){
		                			/* ChatWith (Private Chat) Call in Chatrooms */
		                			if(typeof(jqcc[controlparameters.name]) == 'undefined'){
		                				/* Incase of Embedded chatrooms with CometChat disabled return post message will be sent to chatroom window with extra parameter i.e; enabled=0. */
		                				var returnparameters = {"type":"modules", "name":"cometchat", "method":"checkCometChat", "params":{"enabled":"0"}};
	                   					returnparameters = JSON.stringify(returnparameters);
	                   					e.source.postMessage('CC^CONTROL_'+returnparameters,'*');
		                			} else {
		                				/* Incase of Embedded chatrooms with CometChat disabled return post message will be sent to chatroom window with extra parameter i.e; enabled=1. */
		                				var returnparameters = {"type":"modules", "name":"cometchat", "method":"checkCometChat", "params":{"enabled":"1"}};
	                   					returnparameters = JSON.stringify(returnparameters);
	                   					e.source.postMessage('CC^CONTROL_'+returnparameters,'*');
	                   					if(typeof(jqcc[controlparameters.name][controlparameters.method])!="undefined")
	                   					/* Call to ChatWith Function is CometChat bar is enabled. */
		                				jqcc[controlparameters.name][controlparameters.method](controlparameters.params.uid);
		                			}
		                		} else {
		                			/* LoadCCPopup calls for Chatrooms. */
		                			if(typeof(jqcc.cometchat) == 'undefined' || typeof(controlparameters.params.windowMode) != "undefined"){
                                        if(typeof(controlparameters.params.synergy) != "undefined"){
                                        	/* Incase of Embedded Synergy without CometChat Bar, it will send postmessage to synergy iFrame with windowMode=1 to open loadChatroomPro in windowMode.*/
                                        	/* This is handled in below else block.*/
                                            var returnparameters = {"type":"modules", "name":"cometchat", "method":"unbanChatroomUser", "params":{"url":controlparameters.params.url, "action":controlparameters.params.action, "lang":controlparameters.params.lang, "windowMode":1}};
                                            returnparameters = JSON.stringify(returnparameters);
                                            jqcc('#cometchat_synergy_iframe')[0].contentWindow.postMessage('CC^CONTROL_'+returnparameters,'*');
                                        } else {
                                        	/* Above postMessage Call to synergy theme to open loadChatroomPro in window mode. */
                                            loadCCPopup(controlparameters.params.url, controlparameters.params.action,"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=1, width=400,height=200",400,200,controlparameters.params.lang,null,null,null,null,1);
                                        }
                                    } else {
                                    	/* LoadCCPopup call of Chatrooms with CometChat bar enabled. */
                                        loadCCPopup(controlparameters.params.url, controlparameters.params.action,"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=1, width=400,height=200",400,200,controlparameters.params.lang);
                                    }
		                		}
		                } else if(controlparameters.type == "functions" && controlparameters.name == "socialauth") {
		                	/* Social Login call for Embedded Chatroom */
	                		if(jqcc('#cometchat_optionsbutton').length == 1){
	                			/* If CometChat bar is present, Social auth login popup of the bar will be opened. */
	                			jqcc('#cometchat_optionsbutton').click();
	                		} else if (jqcc('.cometchat_optionsimages_ccauth').length == 1) {
	                			jqcc('.cometchat_optionsimages_ccauth').click();
	                		} else {
	                			/* If CometChat bar is not present, Social Login popup will be opened in Window Mode to login to Embedded chatroom. */
	                			loadCCPopup(controlparameters.params.url, controlparameters.name,"status=0,toolbar=0,menubar=0,directories=0,resizable=0,location=0,status=0,scrollbars=0, width=420,height=250",300,200,'<?php echo $language[77];?>',null,null,null,null,1);
							}
		                } else if(controlparameters.name == "cometchat" && (controlparameters.method == "kickChatroomUser" || controlparameters.method == "banChatroomUser")){
		                	/* Chatroom Kick/Ban users call. */
				            if(typeof(jqcc[controlparameters.name][controlparameters.method])=="undefined"){
				            	/* In case of embedded chatroom with CometChat bar disabled, a return post message will be sent to Chatroom iFrame which will call the API. */
                                var returnparameters = {"type":controlparameters.type, "name":controlparameters.name, "method":controlparameters.method, "params":controlparameters.params};
                                returnparameters = JSON.stringify(returnparameters);
                                jqcc('#cometchat_trayicon_chatrooms_iframe, #cometchat_chatrooms_iframe')[0].contentWindow.postMessage('CC^CONTROL_'+returnparameters,'*');
                            }else{
                            	/* Direct Kick/Ban API call for Synergy theme. */
                                jqcc[controlparameters.name][controlparameters.method](controlparameters.params.uid,0);
                            }
				        } else if(controlparameters.type == "themes" && controlparameters.method == "loggedout") {
				        	/* Logout from Embedded Chatroom incase of Social Login. */
		                	if(typeof(jqcc.cometchat)!='undefined'){
		                		/* If CometChat bar is not enabled, it will only logout from embedded chatroom iFrame. */
		                		jqcc[jqcc.cometchat.getSettings().theme].loggedOut();
                                jqcc.cometchat.setThemeVariable('loggedout', 1);
                                clearTimeout(jqcc.cometchat.getCcvariable().heartbeatTimer);
		                	} else {
		                		/* If CometChat bar is enabled, it will send postMessage to Chatroom as Chatroom methods are not present in main CometChat. */
		                		var returnparameters = {"type":controlparameters.type, "name":controlparameters.name, "method":controlparameters.method, "params":controlparameters.params};
	                   			returnparameters = JSON.stringify(returnparameters);
                        		e.source.postMessage('CC^CONTROL_'+returnparameters,'*');
		                	}
						} else {
		                    if(controlparameters.params.chatroommode == 1 && controlparameters.method != "init" && typeof(jqcc.cometchat) != 'undefined' && jqcc.cometchat.getSettings().theme != 'synergy'){
		                    	/* All themes chatroom calls except init calls and CometChat bar is enabled.*/
		                    	if(typeof(jqcc('#cometchat_trayicon_chatrooms_iframe, #cometchat_chatrooms_iframe, #cometchat_synergy_iframe')[0].contentWindow) != 'undefined'){
		                            jqcc('#cometchat_trayicon_chatrooms_iframe, #cometchat_chatrooms_iframe, #cometchat_synergy_iframe')[0].contentWindow.postMessage(e.data,'*');
		                        }
		                   	} else {
		                   		if(typeof(jqcc[controlparameters.name]) == 'undefined'){
		                   			/* If CometChat bar is disabled it will ask Chatroom to open Popups in window mode.*/
		                   			var returnparameters = {"type":"plugins", "name":controlparameters.name, "method":controlparameters.method, "params":controlparameters.params};
		                   			returnparameters.params.windowMode = "1";
                            		returnparameters = JSON.stringify(returnparameters);
                            		e.source.postMessage('CC^CONTROL_'+returnparameters,'*');
		                   		} else {
		                   			/* All direct API calls of CometChat.*/
		                   			jqcc[controlparameters.name][controlparameters.method](controlparameters.params);
		                   		}
		                    }
		                }
		            }
		        }
		    },false);
		break;
	}
}

<?php if ($lightboxWindows == 1): ?>

var cc_dragobj = new Object();

function loadCCPopup(url,name,properties,width,height,title,force,allowmaximize,allowresize,allowpopout,windowmode) {

	if(typeof(windowmode) != "undefined" && windowmode == 1) {
		var queryStringSeparator='&';
		if(url.indexOf('?')<0){
			queryStringSeparator='?';
		}
		if(url.indexOf('basedata') <= 0){
			var basedata = '';
			if(typeof(jqcc.cometchat) != 'undefined' && typeof(jqcc.cometchat.getBaseData) != 'undefined'){
				basedata = jqcc.cometchat.getBaseData();
			}
			url += queryStringSeparator+'basedata='+basedata+'&popoutmode=1';
		} else {
			url += queryStringSeparator+'&popoutmode=1';
		}
		var w = window.open(url,name,properties);
		w.focus();
	} else {
		theme = '<?php echo $theme; ?>';
		url += '&cc_theme='+theme;
		if (jqcc('#cometchat_container_'+name).length > 0) {
			alert ('<?php echo $language[38];?>');

			setTimeout(function() {
				cc_zindex += 1;
				jqcc('#cometchat_container_'+name).css('z-index',100001+cc_zindex);
			}, 100);
			return;
		}

		var top = ((jqcc(window).height() - height) / 2) ;
		var left = ((jqcc(window).width() - width) / 2) + jqcc(window).scrollLeft();

		if (top < 0) { top = 0; }
		if (left < 0) { left = 0; }

		var queryStringSeparator='&';
		if(url.indexOf('?')<0){
			queryStringSeparator='?';
		}
		url += queryStringSeparator+'basedata='+jqcc.cometchat.getBaseData()+'&embed=web&popoutmode=null';
		if (jqcc(document).fullScreen() !== null && allowmaximize == 1) {
			displaymaxicon='style="display:inline-block;"';
		} else {
			displaymaxicon='style="display:none;"';
		}

		if (allowpopout == 1) {
			displaypopicon='style="display:inline-block;"';
		} else {
			displaypopicon='style="display:none;"';
		}

        jqcc("body").append('<div id="cometchat_container_'+name+'" class="cometchat_container" style="left:'+left+'px;top:'+top+'px;width:'+width+'px;"><div class="cometchat_container_title"  onmousedown="dragStart(event, \'cometchat_container_'+name+'\')"><span>'+title+'</span><div class="cometchat_closebox cometchat_tooltip" title="<?php echo $language[76];?>" id="cometchat_closebox_'+name+'" style="font-weight: normal;">×</div><div '+displaymaxicon+' class="cometchat_maxwindow cometchat_tooltip" title="Maximize Popup" id="cometchat_maxwindow_'+name+'"></div><div '+displaypopicon+' class="cometchat_popwindow cometchat_tooltip" title="Popout" id="cometchat_popwindow_'+name+'"></div><div style="clear:both"></div></div><div class="cometchat_container_body" style="height:'+(height)+'px;width:'+(width-2)+'px;"><div class="cometchat_loading"></div><iframe class="cometchat_iframe" id="cometchat_trayicon_'+name+'_iframe" width="'+(width-2)+'" height="'+(height)+'"  allowtransparency="true" frameborder="0"  scrolling="no" src="'+url+'" allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true"></iframe><div class="cometchat_overlay" style="width:'+(width-2)+'px;height:'+(height)+'px;"></div><div style="clear:both"></div></div></div>');
		setTimeout(function() {
			cc_zindex += 1;
			jqcc('#cometchat_container_'+name).css('z-index',100001+cc_zindex);
		}, 100);

		if (force == true) {
			if (navigator.appVersion.indexOf("MSIE") == -1) {
				window.onbeforeunload = function() {return '<?php echo $language[39];?>'};
			}
		}

	    var cometchat_container = jqcc('#cometchat_container_'+name);
		cometchat_container.find('.cometchat_closebox').click(function() {
			cometchat_container.remove();
	        jqcc("#cometchat_tooltip").css('display', 'none');
			window.onbeforeunload = null;
		});

		if (jqcc(document).fullScreen() !== null && allowmaximize ==1) {
			cometchat_container.find('.cometchat_iframe').addClass('cometchat_iframe_'+name);
				cometchat_container.find('.cometchat_maxwindow').click(function() {
				jqcc('.cometchat_iframe_'+name).toggleFullScreen(true);
				if (name =='whiteboard') {
					jqcc('#cometchat_container_whiteboard').find('.cometchat_iframe').contents().find('#whiteboard').width(screen.width);
					jqcc('#cometchat_container_whiteboard').find('.cometchat_iframe').contents().find('#whiteboard').height(screen.height);
				}
				jqcc("#cometchat_tooltip").css('display', 'none');
			});
		}

		if (allowpopout == 1) {
			cometchat_container.find('.cometchat_popwindow').click(function() {
				window.open(url+"&popoutmode=1",name,'width='+width+',height='+height+' scrollbars=yes, resizable=yes');
				jqcc.cometchat.setInternalVariable('avchatpopoutcalled','1');
				cometchat_container.remove();
	            jqcc("#cometchat_tooltip").css('display', 'none');
			});
		}

		cometchat_container.click(function() {
			cc_zindex += 1;
			jqcc(this).css('z-index',100001+cc_zindex);
		});
	}
}

var closeCCPopup = closeCCPopup || function (id) {
	jqcc('#cometchat_container_'+id).remove();
}

function resizeCCPopup(id,width,height) {
	jqcc('#cometchat_container_'+id).css('width',width+2+'px').find('.cometchat_container_body').css({'height':height, 'width':width});
	jqcc('#cometchat_container_'+id).find('.cometchat_iframe').attr({'height':height, 'width':width});
}

function getID(id) { return document.getElementById(id); }

function dragStart(a,b){
	cc_zindex += 1;jqcc('#'+b).css('z-index',100001+cc_zindex);
	jqcc('#'+b).find('.cometchat_overlay').css('display','block');var x,y;cc_dragobj.elNode=getID(b);try{x=window.event.clientX+document.documentElement.scrollLeft+document.body.scrollLeft;y=window.event.clientY+document.documentElement.scrollTop+document.body.scrollTop}catch(e){x=a.clientX+window.scrollX;y=a.clientY+window.scrollY}cc_dragobj.cursorStartX=x;cc_dragobj.cursorStartY=y;cc_dragobj.elStartLeft=parseInt(cc_dragobj.elNode.style.left,10);cc_dragobj.elStartTop=parseInt(cc_dragobj.elNode.style.top,10);if(isNaN(cc_dragobj.elStartLeft))cc_dragobj.elStartLeft=0;if(isNaN(cc_dragobj.elStartTop))cc_dragobj.elStartTop=0;try{document.attachEvent("onmousemove",dragGo);document.attachEvent("onmouseup",dragStop);window.event.cancelBubble=true;window.event.returnValue=false}catch(e){document.addEventListener("mousemove",dragGo,true);document.addEventListener("mouseup",dragStop,true);a.preventDefault()}}

function dragGo(a){var x,y;try{x=window.event.clientX+document.documentElement.scrollLeft+document.body.scrollLeft;y=window.event.clientY+document.documentElement.scrollTop+document.body.scrollTop}catch(e){x=a.clientX+window.scrollX;y=a.clientY+window.scrollY}var b=(cc_dragobj.elStartLeft+x-cc_dragobj.cursorStartX);var c=(cc_dragobj.elStartTop+y-cc_dragobj.cursorStartY);if(b>0){cc_dragobj.elNode.style.left=b+"px"}else{cc_dragobj.elNode.style.left="1px"}if(c>0){cc_dragobj.elNode.style.top=c+"px"}else{cc_dragobj.elNode.style.top="1px"}try{window.event.cancelBubble=true;window.event.returnValue=false}catch(e){a.preventDefault()}}

function dragStop(event){jqcc('.cometchat_overlay').css('display','none');try{document.detachEvent("onmousemove",dragGo);document.detachEvent("onmouseup",dragStop)}catch(e){document.removeEventListener("mousemove",dragGo,true);document.removeEventListener("mouseup",dragStop,true)}}

<?php else:?>

function loadCCPopup(url,name,properties,width,height,title,force,allowmaximize,allowresize,allowpopout) {
	var queryStringSeparator='&';
	if(url.indexOf('?')<0){
		queryStringSeparator='?';
	}
	url += queryStringSeparator+'&popoutmode=1';
	theme = '<?php echo $theme; ?>';
	url += '&cc_theme='+theme;

	if(url.indexOf('basedata') <= 0){
		var basedata = '';
		if(typeof(jqcc.cometchat) != 'undefined' && typeof(jqcc.cometchat.getBaseData) != 'undefined'){
			basedata = jqcc.cometchat.getBaseData();
		}
		url += queryStringSeparator+'basedata='+basedata+'&popoutmode=1';
	}

	var w = window.open(url,name,properties);
	w.focus();
}

<?php endif;?>

function getTimeDisplay(ts) {
	if((ts+"").length == 10){
		ts = ts*1000;
	}
	var time = new Date(ts);
	var ap = "";
	var hour = time.getHours();
	var minute = time.getMinutes();
	var date = time.getDate();
	var month = time.getMonth();
	var year = time.getFullYear();
	var armyTime = 0;
	if(typeof(jqcc.cometchat.getSettings) == 'undefined'){
		armyTime = jqcc.cometchat.getChatroomVars('armyTime');
	} else {
		armyTime = jqcc.cometchat.getSettings()['armyTime'];
	}
	if(armyTime!=1){
		ap = hour>11 ? "PM" : "AM";
		hour = hour==0 ? 12 : hour>12 ? hour-12 : hour;
	}else{
		hour = hour<10 ? "0"+hour : hour;
	}
	minute = minute<10 ? "0"+minute : minute;
	var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	var type = 'th';
	if(date==1||date==21||date==31){
		type = 'st';
	}else if(date==2||date==22){
		type = 'nd';
	}else if(date==3||date==23){
		type = 'rd';
	}
	return {ap:ap,hour:hour,minute:minute,date:date,month:months[month],year:year,type:type};
}

function attachPlaceholder(element){
    jqcc(element).find('[placeholder]').focus(function() {
        var input = jqcc(this);
        if (input.val() == input.attr('placeholder')) {
            input.val('');
            input.removeClass('placeholder');
        }
        }).blur(function() {
        var input = jqcc(this);
        if (input.val() == '') {
            input.addClass('placeholder');
            input.val(input.attr('placeholder'));
        }
    }).blur();

    jqcc(element).find('[placeholder]').parents('form').submit(function() {
        jqcc(this).find('[placeholder]').each(function() {
            var input = jqcc(this);
            if (input.val() == input.attr('placeholder')) {
                input.val('');
            }
        });
    });
}
