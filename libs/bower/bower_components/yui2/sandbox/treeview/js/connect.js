/* Copyright (c) 2006 Yahoo! Inc. All rights reserved.*/
YAHOO.util.Connect={};YAHOO.util.Connect={_msxml_progid:['MSXML2.XMLHTTP.5.0','MSXML2.XMLHTTP.4.0','MSXML2.XMLHTTP.3.0','MSXML2.XMLHTTP','Microsoft.XMLHTTP'],_http_header:[],_isFormPost:false,_sFormData:null,_polling_interval:300,_transaction_id:0,setProgId:function(id)
{this.msxml_progid.unshift(id);},createXhrObject:function(transactionId)
{var obj,http;try
{http=new XMLHttpRequest();obj={conn:http,tId:transactionId};}
catch(e)
{for(var i=0;i<this._msxml_progid.length;++i){try
{http=new ActiveXObject(this._msxml_progid[i]);obj={conn:http,tId:transactionId};}
catch(e){}}}
finally
{return obj;}},getConnectionObject:function()
{var o;var tId=this._transaction_id;try
{o=this.createXhrObject(tId);if(o){this._transaction_id++;}}
catch(e){}
finally
{return o;}},asyncRequest:function(method,uri,callback,postData)
{var errorObj;var o=this.getConnectionObject();if(!o){return null;}
else{var oConn=this;o.conn.open(method,uri,true);this.handleReadyState(o,callback);if(this._isFormPost){postData=this._sFormData;this._isFormPost=false;}
else if(postData){this.initHeader('Content-Type','application/x-www-form-urlencoded');}
if(this._http_header.length>0){this.setHeader(o);}
postData?o.conn.send(postData):o.conn.send(null);return o;}},handleReadyState:function(o,callback)
{var oConn=this;var poll=window.setInterval(function(){if(o.conn.readyState==4){oConn.handleTransactionResponse(o,callback);window.clearInterval(poll);}},this._polling_interval);},handleTransactionResponse:function(o,callback)
{var httpStatus;var responseObject;try{httpStatus=o.conn.status;}
catch(e){httpStatus=13030;}
if(httpStatus==200){responseObject=this.createResponseObject(o,callback.argument);if(callback.success){if(!callback.scope){callback.success(responseObject);}
else{callback.success.apply(callback.scope,[responseObject]);}}}
else{switch(httpStatus){case 12002:case 12029:case 12030:case 12031:case 12152:case 13030:responseObject=this.createExceptionObject(o,callback.argument);if(callback.failure){if(!callback.scope){callback.failure(responseObject);}
else{callback.failure.apply(callback.scope,[responseObject]);}}
break;default:responseObject=this.createResponseObject(o,callback.argument);if(callback.failure){if(!callback.scope){callback.failure(responseObject);}
else{callback.failure.apply(callback.scope,[responseObject]);}}}}
this.releaseObject(o);},createResponseObject:function(o,callbackArg)
{var obj={};obj.tId=o.tId;obj.status=o.conn.status;obj.statusText=o.conn.statusText;obj.allResponseHeaders=o.conn.getAllResponseHeaders();obj.responseText=o.conn.responseText;obj.responseXML=o.conn.responseXML;if(callbackArg){obj.argument=callbackArg;}
return obj;},createExceptionObject:function(tId,callbackArg)
{var COMM_CODE=0;var COMM_ERROR='communication failure';var obj={};obj.tId=tId;obj.status=COMM_CODE;obj.statusText=COMM_ERROR;if(callbackArg){obj.argument=callbackArg;}
return obj;},initHeader:function(label,value)
{var oHeader=[label,value];this._http_header.push(oHeader);},setHeader:function(o)
{var oHeader=this._http_header;for(var i=0;i<oHeader.length;i++){o.conn.setRequestHeader(oHeader[i][0],oHeader[i][1]);}
oHeader.splice(0,oHeader.length);},setForm:function(formName)
{this._sFormData='';var oForm=document.forms[formName];var oElement,elName,elValue;for(var i=0;i<oForm.elements.length;i++){oElement=oForm.elements[i];elName=oForm.elements[i].name;elValue=oForm.elements[i].value;switch(oElement.type)
{case'select-multiple':for(var j=0;j<oElement.options.length;j++){if(oElement.options[j].selected){this._sFormData+=encodeURIComponent(elName)+'='+encodeURIComponent(oElement.options[j].value)+'&';}}
break;case'radio':case'checkbox':if(oElement.checked){this._sFormData+=encodeURIComponent(elName)+'='+encodeURIComponent(elValue)+'&';}
break;case'file':break;case undefined:break;default:this._sFormData+=encodeURIComponent(elName)+'='+encodeURIComponent(elValue)+'&';break;}}
this._sFormData=this._sFormData.substr(0,this._sFormData.length-1);this._isFormPost=true;this.initHeader('Content-Type','application/x-www-form-urlencoded');},abort:function(o)
{if(this.isCallInProgress(o)){o.conn.abort();this.releaseObject(o);}},isCallInProgress:function(o)
{if(o){return o.conn.readyState!=4&&o.conn.readyState!=0;}},releaseObject:function(o)
{o.conn=null;o=null;}}