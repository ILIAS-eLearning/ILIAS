YAHOO.util.Color=function(){var a="0",b=YAHOO.lang.isArray,c=YAHOO.lang.isNumber;return{real2dec:function(d){return Math.min(255,Math.round(d*256));},hsv2rgb:function(l,y,w){if(b(l)){return this.hsv2rgb.call(this,l[0],l[1],l[2]);}var d,m,u,k=Math.floor((l/60)%6),n=(l/60)-k,j=w*(1-y),e=w*(1-n*y),x=w*(1-(1-n)*y),o;switch(k){case 0:d=w;m=x;u=j;break;case 1:d=e;m=w;u=j;break;case 2:d=j;m=w;u=x;break;case 3:d=j;m=e;u=w;break;case 4:d=x;m=j;u=w;break;case 5:d=w;m=j;u=e;break;}o=this.real2dec;return[o(d),o(m),o(u)];},rgb2hsv:function(d,j,k){if(b(d)){return this.rgb2hsv.apply(this,d);}d/=255;j/=255;k/=255;var i,n,e=Math.min(Math.min(d,j),k),l=Math.max(Math.max(d,j),k),m=l-e,f;switch(l){case e:i=0;break;case d:i=60*(j-k)/m;if(j<k){i+=360;}break;case j:i=(60*(k-d)/m)+120;break;case k:i=(60*(d-j)/m)+240;break;}n=(l===0)?0:1-(e/l);f=[Math.round(i),n,l];return f;},rgb2hex:function(h,e,d){if(b(h)){return this.rgb2hex.apply(this,h);}var i=this.dec2hex;return i(h)+i(e)+i(d);},dec2hex:function(d){d=parseInt(d,10)|0;d=(d>255||d<0)?0:d;return(a+d.toString(16)).slice(-2).toUpperCase();},hex2dec:function(d){return parseInt(d,16);},hex2rgb:function(d){var e=this.hex2dec;return[e(d.slice(0,2)),e(d.slice(2,4)),e(d.slice(4,6))];},websafe:function(h,e,d){if(b(h)){return this.websafe.apply(this,h);}var i=function(f){if(c(f)){f=Math.min(Math.max(0,f),255);var g,j;for(g=0;g<256;g=g+51){j=g+51;if(f>=g&&f<=j){return(f-g>25)?j:g;}}}return f;};return[i(h),i(e),i(d)];}};}();(function(){var k=0,g=YAHOO.util,d=YAHOO.lang,e=YAHOO.widget.Slider,c=g.Color,f=g.Dom,j=g.Event,a=d.substitute,i="yui-picker";function h(l,b){k=k+1;b=b||{};if(arguments.length===1&&!YAHOO.lang.isString(l)&&!l.nodeName){b=l;l=b.element||null;}if(!l&&!b.element){l=this._createHostElement(b);}h.superclass.constructor.call(this,l,b);this.initPicker();}YAHOO.extend(h,YAHOO.util.Element,{ID:{R:i+"-r",R_HEX:i+"-rhex",G:i+"-g",G_HEX:i+"-ghex",B:i+"-b",B_HEX:i+"-bhex",H:i+"-h",S:i+"-s",V:i+"-v",PICKER_BG:i+"-bg",PICKER_THUMB:i+"-thumb",HUE_BG:i+"-hue-bg",HUE_THUMB:i+"-hue-thumb",HEX:i+"-hex",SWATCH:i+"-swatch",WEBSAFE_SWATCH:i+"-websafe-swatch",CONTROLS:i+"-controls",RGB_CONTROLS:i+"-rgb-controls",HSV_CONTROLS:i+"-hsv-controls",HEX_CONTROLS:i+"-hex-controls",HEX_SUMMARY:i+"-hex-summary",CONTROLS_LABEL:i+"-controls-label"},TXT:{ILLEGAL_HEX:"Illegal hex value entered",SHOW_CONTROLS:"Show color details",HIDE_CONTROLS:"Hide color details",CURRENT_COLOR:"Currently selected color: {rgb}",CLOSEST_WEBSAFE:"Closest websafe color: {rgb}. Click to select.",R:"R",G:"G",B:"B",H:"H",S:"S",V:"V",HEX:"#",DEG:"\u00B0",PERCENT:"%"},IMAGE:{PICKER_THUMB:"../../build/colorpicker/assets/picker_thumb.png",HUE_THUMB:"../../build/colorpicker/assets/hue_thumb.png"},DEFAULT:{PICKER_SIZE:180},OPT:{HUE:"hue",SATURATION:"saturation",VALUE:"value",RED:"red",GREEN:"green",BLUE:"blue",HSV:"hsv",RGB:"rgb",WEBSAFE:"websafe",HEX:"hex",PICKER_SIZE:"pickersize",SHOW_CONTROLS:"showcontrols",SHOW_RGB_CONTROLS:"showrgbcontrols",SHOW_HSV_CONTROLS:"showhsvcontrols",SHOW_HEX_CONTROLS:"showhexcontrols",SHOW_HEX_SUMMARY:"showhexsummary",SHOW_WEBSAFE:"showwebsafe",CONTAINER:"container",IDS:"ids",ELEMENTS:"elements",TXT:"txt",IMAGES:"images",ANIMATE:"animate"},skipAnim:true,_createHostElement:function(){var b=document.createElement("div");if(this.CSS.BASE){b.className=this.CSS.BASE;}return b;},_updateHueSlider:function(){var b=this.get(this.OPT.PICKER_SIZE),l=this.get(this.OPT.HUE);l=b-Math.round(l/360*b);if(l===b){l=0;}this.hueSlider.setValue(l,this.skipAnim);},_updatePickerSlider:function(){var l=this.get(this.OPT.PICKER_SIZE),m=this.get(this.OPT.SATURATION),b=this.get(this.OPT.VALUE);m=Math.round(m*l/100);b=Math.round(l-(b*l/100));this.pickerSlider.setRegionValue(m,b,this.skipAnim);},_updateSliders:function(){this._updateHueSlider();this._updatePickerSlider();},setValue:function(l,b){b=(b)||false;this.set(this.OPT.RGB,l,b);this._updateSliders();},hueSlider:null,pickerSlider:null,_getH:function(){var b=this.get(this.OPT.PICKER_SIZE),l=(b-this.hueSlider.getValue())/b;l=Math.round(l*360);return(l===360)?0:l;},_getS:function(){return this.pickerSlider.getXValue()/this.get(this.OPT.PICKER_SIZE);},_getV:function(){var b=this.get(this.OPT.PICKER_SIZE);return(b-this.pickerSlider.getYValue())/b;},_updateSwatch:function(){var m=this.get(this.OPT.RGB),o=this.get(this.OPT.WEBSAFE),n=this.getElement(this.ID.SWATCH),l=m.join(","),b=this.get(this.OPT.TXT);f.setStyle(n,"background-color","rgb("+l+")");n.title=a(b.CURRENT_COLOR,{"rgb":"#"+this.get(this.OPT.HEX)});n=this.getElement(this.ID.WEBSAFE_SWATCH);l=o.join(",");f.setStyle(n,"background-color","rgb("+l+")");n.title=a(b.CLOSEST_WEBSAFE,{"rgb":"#"+c.rgb2hex(o)});},_getValuesFromSliders:function(){this.set(this.OPT.RGB,c.hsv2rgb(this._getH(),this._getS(),this._getV()));},_updateFormFields:function(){this.getElement(this.ID.H).value=this.get(this.OPT.HUE);this.getElement(this.ID.S).value=this.get(this.OPT.SATURATION);this.getElement(this.ID.V).value=this.get(this.OPT.VALUE);this.getElement(this.ID.R).value=this.get(this.OPT.RED);this.getElement(this.ID.R_HEX).innerHTML=c.dec2hex(this.get(this.OPT.RED));this.getElement(this.ID.G).value=this.get(this.OPT.GREEN);this.getElement(this.ID.G_HEX).innerHTML=c.dec2hex(this.get(this.OPT.GREEN));this.getElement(this.ID.B).value=this.get(this.OPT.BLUE);this.getElement(this.ID.B_HEX).innerHTML=c.dec2hex(this.get(this.OPT.BLUE));this.getElement(this.ID.HEX).value=this.get(this.OPT.HEX);},_onHueSliderChange:function(n){var l=this._getH(),b=c.hsv2rgb(l,1,1),m="rgb("+b.join(",")+")";this.set(this.OPT.HUE,l,true);f.setStyle(this.getElement(this.ID.PICKER_BG),"background-color",m);if(this.hueSlider.valueChangeSource!==e.SOURCE_SET_VALUE){this._getValuesFromSliders();}this._updateFormFields();this._updateSwatch();},_onPickerSliderChange:function(m){var l=this._getS(),b=this._getV();this.set(this.OPT.SATURATION,Math.round(l*100),true);this.set(this.OPT.VALUE,Math.round(b*100),true);if(this.pickerSlider.valueChangeSource!==e.SOURCE_SET_VALUE){this._getValuesFromSliders();
}this._updateFormFields();this._updateSwatch();},_getCommand:function(b){var l=j.getCharCode(b);if(l===38){return 3;}else{if(l===13){return 6;}else{if(l===40){return 4;}else{if(l>=48&&l<=57){return 1;}else{if(l>=97&&l<=102){return 2;}else{if(l>=65&&l<=70){return 2;}else{if("8, 9, 13, 27, 37, 39".indexOf(l)>-1||b.ctrlKey||b.metaKey){return 5;}else{return 0;}}}}}}}},_useFieldValue:function(l,b,n){var m=b.value;if(n!==this.OPT.HEX){m=parseInt(m,10);}if(m!==this.get(n)){this.set(n,m);}},_rgbFieldKeypress:function(m,b,o){var n=this._getCommand(m),l=(m.shiftKey)?10:1;switch(n){case 6:this._useFieldValue.apply(this,arguments);break;case 3:this.set(o,Math.min(this.get(o)+l,255));this._updateFormFields();break;case 4:this.set(o,Math.max(this.get(o)-l,0));this._updateFormFields();break;default:}},_hexFieldKeypress:function(l,b,n){var m=this._getCommand(l);if(m===6){this._useFieldValue.apply(this,arguments);}},_hexOnly:function(l,b){var m=this._getCommand(l);switch(m){case 6:case 5:case 1:break;case 2:if(b!==true){break;}default:j.stopEvent(l);return false;}},_numbersOnly:function(b){return this._hexOnly(b,true);},getElement:function(b){return this.get(this.OPT.ELEMENTS)[this.get(this.OPT.IDS)[b]];},_createElements:function(){var n,m,q,o,l,b=this.get(this.OPT.IDS),r=this.get(this.OPT.TXT),t=this.get(this.OPT.IMAGES),s=function(p,v){var w=document.createElement(p);if(v){d.augmentObject(w,v,true);}return w;},u=function(p,v){var w=d.merge({autocomplete:"off",value:"0",size:3,maxlength:3},v);w.name=w.id;return new s(p,w);};l=this.get("element");n=new s("div",{id:b[this.ID.PICKER_BG],className:"yui-picker-bg",tabIndex:-1,hideFocus:true});m=new s("div",{id:b[this.ID.PICKER_THUMB],className:"yui-picker-thumb"});q=new s("img",{src:t.PICKER_THUMB});m.appendChild(q);n.appendChild(m);l.appendChild(n);n=new s("div",{id:b[this.ID.HUE_BG],className:"yui-picker-hue-bg",tabIndex:-1,hideFocus:true});m=new s("div",{id:b[this.ID.HUE_THUMB],className:"yui-picker-hue-thumb"});q=new s("img",{src:t.HUE_THUMB});m.appendChild(q);n.appendChild(m);l.appendChild(n);n=new s("div",{id:b[this.ID.CONTROLS],className:"yui-picker-controls"});l.appendChild(n);l=n;n=new s("div",{className:"hd"});m=new s("a",{id:b[this.ID.CONTROLS_LABEL],href:"#"});n.appendChild(m);l.appendChild(n);n=new s("div",{className:"bd"});l.appendChild(n);l=n;n=new s("ul",{id:b[this.ID.RGB_CONTROLS],className:"yui-picker-rgb-controls"});m=new s("li");m.appendChild(document.createTextNode(r.R+" "));o=new u("input",{id:b[this.ID.R],className:"yui-picker-r"});m.appendChild(o);n.appendChild(m);m=new s("li");m.appendChild(document.createTextNode(r.G+" "));o=new u("input",{id:b[this.ID.G],className:"yui-picker-g"});m.appendChild(o);n.appendChild(m);m=new s("li");m.appendChild(document.createTextNode(r.B+" "));o=new u("input",{id:b[this.ID.B],className:"yui-picker-b"});m.appendChild(o);n.appendChild(m);l.appendChild(n);n=new s("ul",{id:b[this.ID.HSV_CONTROLS],className:"yui-picker-hsv-controls"});m=new s("li");m.appendChild(document.createTextNode(r.H+" "));o=new u("input",{id:b[this.ID.H],className:"yui-picker-h"});m.appendChild(o);m.appendChild(document.createTextNode(" "+r.DEG));n.appendChild(m);m=new s("li");m.appendChild(document.createTextNode(r.S+" "));o=new u("input",{id:b[this.ID.S],className:"yui-picker-s"});m.appendChild(o);m.appendChild(document.createTextNode(" "+r.PERCENT));n.appendChild(m);m=new s("li");m.appendChild(document.createTextNode(r.V+" "));o=new u("input",{id:b[this.ID.V],className:"yui-picker-v"});m.appendChild(o);m.appendChild(document.createTextNode(" "+r.PERCENT));n.appendChild(m);l.appendChild(n);n=new s("ul",{id:b[this.ID.HEX_SUMMARY],className:"yui-picker-hex_summary"});m=new s("li",{id:b[this.ID.R_HEX]});n.appendChild(m);m=new s("li",{id:b[this.ID.G_HEX]});n.appendChild(m);m=new s("li",{id:b[this.ID.B_HEX]});n.appendChild(m);l.appendChild(n);n=new s("div",{id:b[this.ID.HEX_CONTROLS],className:"yui-picker-hex-controls"});n.appendChild(document.createTextNode(r.HEX+" "));m=new u("input",{id:b[this.ID.HEX],className:"yui-picker-hex",size:6,maxlength:6});n.appendChild(m);l.appendChild(n);l=this.get("element");n=new s("div",{id:b[this.ID.SWATCH],className:"yui-picker-swatch"});l.appendChild(n);n=new s("div",{id:b[this.ID.WEBSAFE_SWATCH],className:"yui-picker-websafe-swatch"});l.appendChild(n);},_attachRGBHSV:function(l,b){j.on(this.getElement(l),"keydown",function(n,m){m._rgbFieldKeypress(n,this,b);},this);j.on(this.getElement(l),"keypress",this._numbersOnly,this,true);j.on(this.getElement(l),"blur",function(n,m){m._useFieldValue(n,this,b);},this);},_updateRGB:function(){var b=[this.get(this.OPT.RED),this.get(this.OPT.GREEN),this.get(this.OPT.BLUE)];this.set(this.OPT.RGB,b);this._updateSliders();},_initElements:function(){var p=this.OPT,n=this.get(p.IDS),l=this.get(p.ELEMENTS),b,m,q;for(b in this.ID){if(d.hasOwnProperty(this.ID,b)){n[this.ID[b]]=n[b];}}m=f.get(n[this.ID.PICKER_BG]);if(!m){this._createElements();}else{}for(b in n){if(d.hasOwnProperty(n,b)){m=f.get(n[b]);q=f.generateId(m);n[b]=q;n[n[b]]=q;l[q]=m;}}},initPicker:function(){this._initSliders();this._bindUI();this.syncUI(true);},_initSliders:function(){var b=this.ID,l=this.get(this.OPT.PICKER_SIZE);this.hueSlider=e.getVertSlider(this.getElement(b.HUE_BG),this.getElement(b.HUE_THUMB),0,l);this.pickerSlider=e.getSliderRegion(this.getElement(b.PICKER_BG),this.getElement(b.PICKER_THUMB),0,l,0,l);this.set(this.OPT.ANIMATE,this.get(this.OPT.ANIMATE));},_bindUI:function(){var b=this.ID,l=this.OPT;this.hueSlider.subscribe("change",this._onHueSliderChange,this,true);this.pickerSlider.subscribe("change",this._onPickerSliderChange,this,true);j.on(this.getElement(b.WEBSAFE_SWATCH),"click",function(m){this.setValue(this.get(l.WEBSAFE));},this,true);j.on(this.getElement(b.CONTROLS_LABEL),"click",function(m){this.set(l.SHOW_CONTROLS,!this.get(l.SHOW_CONTROLS));j.preventDefault(m);},this,true);this._attachRGBHSV(b.R,l.RED);this._attachRGBHSV(b.G,l.GREEN);this._attachRGBHSV(b.B,l.BLUE);this._attachRGBHSV(b.H,l.HUE);
this._attachRGBHSV(b.S,l.SATURATION);this._attachRGBHSV(b.V,l.VALUE);j.on(this.getElement(b.HEX),"keydown",function(n,m){m._hexFieldKeypress(n,this,l.HEX);},this);j.on(this.getElement(this.ID.HEX),"keypress",this._hexOnly,this,true);j.on(this.getElement(this.ID.HEX),"blur",function(n,m){m._useFieldValue(n,this,l.HEX);},this);},syncUI:function(b){this.skipAnim=b;this._updateRGB();this.skipAnim=false;},_updateRGBFromHSV:function(){var l=[this.get(this.OPT.HUE),this.get(this.OPT.SATURATION)/100,this.get(this.OPT.VALUE)/100],b=c.hsv2rgb(l);this.set(this.OPT.RGB,b);this._updateSliders();},_updateHex:function(){var o=this.get(this.OPT.HEX),b=o.length,p,n,m;if(b===3){p=o.split("");for(n=0;n<b;n=n+1){p[n]=p[n]+p[n];}o=p.join("");}if(o.length!==6){return false;}m=c.hex2rgb(o);this.setValue(m);},_hideShowEl:function(m,b){var l=(d.isString(m)?this.getElement(m):m);f.setStyle(l,"display",(b)?"":"none");},initAttributes:function(b){b=b||{};h.superclass.initAttributes.call(this,b);this.setAttributeConfig(this.OPT.PICKER_SIZE,{value:b.size||this.DEFAULT.PICKER_SIZE});this.setAttributeConfig(this.OPT.HUE,{value:b.hue||0,validator:d.isNumber});this.setAttributeConfig(this.OPT.SATURATION,{value:b.saturation||0,validator:d.isNumber});this.setAttributeConfig(this.OPT.VALUE,{value:d.isNumber(b.value)?b.value:100,validator:d.isNumber});this.setAttributeConfig(this.OPT.RED,{value:d.isNumber(b.red)?b.red:255,validator:d.isNumber});this.setAttributeConfig(this.OPT.GREEN,{value:d.isNumber(b.green)?b.green:255,validator:d.isNumber});this.setAttributeConfig(this.OPT.BLUE,{value:d.isNumber(b.blue)?b.blue:255,validator:d.isNumber});this.setAttributeConfig(this.OPT.HEX,{value:b.hex||"FFFFFF",validator:d.isString});this.setAttributeConfig(this.OPT.RGB,{value:b.rgb||[255,255,255],method:function(o){this.set(this.OPT.RED,o[0],true);this.set(this.OPT.GREEN,o[1],true);this.set(this.OPT.BLUE,o[2],true);var q=c.websafe(o),p=c.rgb2hex(o),n=c.rgb2hsv(o);this.set(this.OPT.WEBSAFE,q,true);this.set(this.OPT.HEX,p,true);if(n[1]){this.set(this.OPT.HUE,n[0],true);}this.set(this.OPT.SATURATION,Math.round(n[1]*100),true);this.set(this.OPT.VALUE,Math.round(n[2]*100),true);},readonly:true});this.setAttributeConfig(this.OPT.CONTAINER,{value:null,method:function(n){if(n){n.showEvent.subscribe(function(){this.pickerSlider.focus();},this,true);}}});this.setAttributeConfig(this.OPT.WEBSAFE,{value:b.websafe||[255,255,255]});var m=b.ids||d.merge({},this.ID),l;if(!b.ids&&k>1){for(l in m){if(d.hasOwnProperty(m,l)){m[l]=m[l]+k;}}}this.setAttributeConfig(this.OPT.IDS,{value:m,writeonce:true});this.setAttributeConfig(this.OPT.TXT,{value:b.txt||this.TXT,writeonce:true});this.setAttributeConfig(this.OPT.IMAGES,{value:b.images||this.IMAGE,writeonce:true});this.setAttributeConfig(this.OPT.ELEMENTS,{value:{},readonly:true});this.setAttributeConfig(this.OPT.SHOW_CONTROLS,{value:d.isBoolean(b.showcontrols)?b.showcontrols:true,method:function(n){var o=f.getElementsByClassName("bd","div",this.getElement(this.ID.CONTROLS))[0];this._hideShowEl(o,n);this.getElement(this.ID.CONTROLS_LABEL).innerHTML=(n)?this.get(this.OPT.TXT).HIDE_CONTROLS:this.get(this.OPT.TXT).SHOW_CONTROLS;}});this.setAttributeConfig(this.OPT.SHOW_RGB_CONTROLS,{value:d.isBoolean(b.showrgbcontrols)?b.showrgbcontrols:true,method:function(n){this._hideShowEl(this.ID.RGB_CONTROLS,n);}});this.setAttributeConfig(this.OPT.SHOW_HSV_CONTROLS,{value:d.isBoolean(b.showhsvcontrols)?b.showhsvcontrols:false,method:function(n){this._hideShowEl(this.ID.HSV_CONTROLS,n);if(n&&this.get(this.OPT.SHOW_HEX_SUMMARY)){this.set(this.OPT.SHOW_HEX_SUMMARY,false);}}});this.setAttributeConfig(this.OPT.SHOW_HEX_CONTROLS,{value:d.isBoolean(b.showhexcontrols)?b.showhexcontrols:false,method:function(n){this._hideShowEl(this.ID.HEX_CONTROLS,n);}});this.setAttributeConfig(this.OPT.SHOW_WEBSAFE,{value:d.isBoolean(b.showwebsafe)?b.showwebsafe:true,method:function(n){this._hideShowEl(this.ID.WEBSAFE_SWATCH,n);}});this.setAttributeConfig(this.OPT.SHOW_HEX_SUMMARY,{value:d.isBoolean(b.showhexsummary)?b.showhexsummary:true,method:function(n){this._hideShowEl(this.ID.HEX_SUMMARY,n);if(n&&this.get(this.OPT.SHOW_HSV_CONTROLS)){this.set(this.OPT.SHOW_HSV_CONTROLS,false);}}});this.setAttributeConfig(this.OPT.ANIMATE,{value:d.isBoolean(b.animate)?b.animate:true,method:function(n){if(this.pickerSlider){this.pickerSlider.animate=n;this.hueSlider.animate=n;}}});this.on(this.OPT.HUE+"Change",this._updateRGBFromHSV,this,true);this.on(this.OPT.SATURATION+"Change",this._updateRGBFromHSV,this,true);this.on(this.OPT.VALUE+"Change",this._updateRGBFromHSV,this,true);this.on(this.OPT.RED+"Change",this._updateRGB,this,true);this.on(this.OPT.GREEN+"Change",this._updateRGB,this,true);this.on(this.OPT.BLUE+"Change",this._updateRGB,this,true);this.on(this.OPT.HEX+"Change",this._updateHex,this,true);this._initElements();}});YAHOO.widget.ColorPicker=h;})();YAHOO.register("colorpicker",YAHOO.widget.ColorPicker,{version:"@VERSION@",build:"@BUILD@"});