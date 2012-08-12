/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
* @author  Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
* @version $Id$
*/

var	iv={},
	ir=[],
	data={},
	a_toStore=[],
	Initialized=false,
	b_launched=true,
	APIcallStartTimeMS,
	as_APIcalls=[];


/* XMLHHTP functions */
function sendRequest (url, data, callback, user, password, headers) {		

	function sendAndLoad(url, data, callback, user, password, headers) {
		function createHttpRequest() {
			try 
			{
				return window.XMLHttpRequest 
					? new window.XMLHttpRequest()
					: new window.ActiveXObject('MSXML2.XMLHTTP');
			} 
			catch (e) 
			{
				throw new Error('cannot create XMLHttpRequest');
			}
		}
		function HttpResponse(xhttp) 
		{
			this.status = Number(xhttp.status);
			this.content = String(xhttp.responseText);
			this.type = String(xhttp.getResponseHeader('Content-Type'));
		}
		function onStateChange() 
		{
			if (xhttp.readyState === 4) { // COMPLETED
				if (typeof callback === 'function') {
					callback(new HttpResponse(xhttp));
				} else {
					return new HttpResponse(xhttp);
				} 
			}
		}		
		var xhttp = createHttpRequest();
		var async = !!callback;
		var post = !!data; 
		xhttp.open(post ? 'POST' : 'GET', url, async, user, password);
		if (typeof headers !== 'object') 
		{
			headers = new Object();
		}
		if (post) 
		{
			headers['Content-Type'] = 'application/x-www-form-urlencoded';
		}
		if (headers && headers instanceof Object) 
		{
			for (var k in headers) {
				xhttp.setRequestHeader(k, headers[k]);
			}
		}
		if (async) 
		{
			xhttp.onreadystatechange = onStateChange;
//				xhttp.send(data ? String(data) : '');				
			xhttp.send(data);				
		} else 
		{
			xhttp.send(data ? String(data) : '');				
			return onStateChange();
		}
	}

	if (typeof headers !== "object") {headers = {};}
	headers['Accept'] = 'text/javascript';
	headers['Accept-Charset'] = 'UTF-8';
	var r = sendAndLoad(url, data, callback, user, password, headers);
	
	if (r.content) {
		if (r.content.indexOf("login.php")>-1) {
			window.location.href = "./Modules/Scorm2004/templates/default/session_timeout.html";
		}
	}
	
	if ((r.status===200 && (/^text\/javascript;?.*/i).test(r.type)) || r.status===0)
	{
		return r.content;
	}
	else
	{
		return r.content;
	}
}

// Debugger
function showCalls(APIcall,callResult,err,dia){
	if (iv.b_debug){
		if (err==null) err="";
		if (dia==null) dia="";
		if (err!=0 && err!="") dia=getErrorStringIntern(err)+'; '+dia; //only function not in basisAPI
		var APIcallNowMS=new Date().getTime(),
			APIms,
			APIs_out='<table cellpadding=0><tr class="d"><td class="r">ms</td><td>sent to API</td><td>returns</td><td class="c">error</td><td>error string; diagnostic</td></tr>';
		APIms=""+(APIcallNowMS-APIcallStartTimeMS);
		if(callResult!="") callResult='"'+callResult+'"';
		as_APIcalls.push('<tr><td class="r">'+APIms+'</td><td><div>'+APIcall+'</div></td><td><div>'+callResult+'</div></td><td class="c">'+err+'</td><td><div>'+dia+'</div></td></tr>');
		for(var i=as_APIcalls.length-1;i>=0;i--) APIs_out+=as_APIcalls[i];
		frames.logframe.document.getElementById("APIcalls").innerHTML=APIs_out+"</table>";
	}
}

function initDebug(){
	if (iv.b_debug==true){
		var href="";
		for(var i=0; i<ir.length; i++){
			if(ir[i][1]==iv.launchId) href=ir[i][3];
		}
		as_APIcalls.push('<tr class="d"><td colspan=5>SCO: '+decodeURIComponent(iv.dataDirectory+href)+' (Id: '+iv.launchId+')</td></tr>');
		APIcallStartTimeMS=new Date().getTime();
	}
}

// send to logfile
function message(s_send){
	s_send = 'lm_'+iv.objId+': '+s_send;
	if (iv.b_messageLog) sendRequest ('./ilias.php?baseClass=ilSAHSPresentationGUI&ref_id='+iv.refId+'&cmd=logMessage', s_send);
}

function warning(s_send){
	s_send = 'lm_'+iv.objId+': '+s_send;
	sendRequest ('./ilias.php?baseClass=ilSAHSPresentationGUI&ref_id='+iv.refId+'&cmd=logWarning', s_send);
}

// avoid sessionTimeOut
function SchedulePing() {
	var r = sendRequest('./ilias.php?baseClass=ilSAHSPresentationGUI&cmd=pingSession&ref_id='+iv.refId);
	setTimeout("SchedulePing()", iv.pingSession*1000);
}

// launch functions
function IliasLaunch(i_l){
	if (isNaN(i_l)) return false;
	var href="",asset=0;
	for (var i=0;i<ir.length;i++){
		if (ir[i][1]==iv.launchId){
			asset=ir[i][2];
		}
		if (ir[i][1]==i_l){
			iv.launchNr=ir[i][0];
			href=ir[i][3];
		}
	}
	if (href=="") return false;

	if (asset==1 || Initialized==false){
		if (asset==1) status4tree(iv.launchId,'asset');
		else status4tree(iv.launchId,getValueIntern(iv.launchId,'cmi.core.lesson_status'),getValueIntern(iv.launchId,'cmi.core.total_time'));
		b_launched=true;
		iv.launchId=i_l;
		frames.sahs_content.document.location.replace(decodeURIComponent(iv.dataDirectory+href));
	}
	else {
		status4tree(iv.launchId,getValueIntern(iv.launchId,'cmi.core.lesson_status'),getValueIntern(iv.launchId,'cmi.core.total_time'));
		b_launched=false;
		setTimeout("API.IliasAbortSco("+iv.launchId+")",5000);
		iv.launchId=i_l;
		frames.sahs_content.document.location.replace('./Modules/ScormAicc/templates/default/dummy.html');
	}
	status4tree(iv.launchId,'running');
}

function launchNext(){
	if (iv.b_autoContinue == true){
		var i_l=0;
		for (var i=0;i<ir.length;i++){
			if (ir[i][0]==iv.launchNr && i<(ir.length-1)){
				i_l=ir[i+1][1];
			}
		}
		if (i_l>0) setTimeout("API.IliasLaunch("+i_l+")",500);
	}
}

function IliasWaitLaunch(i_l){
	if (typeof frames.sahs_content == "undefined") setTimeout("API.IliasWaitLaunch("+i_l+")",100);
	else {
		API.IliasLaunch(i_l);
		API.IliasWaitTree(i_l,0);
	}
}

function IliasWaitTree(i_l,i_counter) {
	if (i_counter<20){
		if (typeof frames.tree == "undefined" || typeof frames.tree.document.getElementsByName('scoIcon'+i_l)[0] == "undefined") 
			setTimeout("API.IliasWaitTree("+i_l+","+(i_counter+1)+")",100);
		else status4tree(i_l,'running');
	}
}

function IliasLaunchAfterFinish(i_la){
	status4tree(i_la,getValueIntern(i_la,'cmi.core.lesson_status'),getValueIntern(i_la,'cmi.core.total_time'));
	if(b_launched==false) setTimeout("API.IliasLaunch("+iv.launchId+")",1);
	else launchNext();
}

function IliasAbortSco(i_l){
	if (b_launched==true) return;
	warning('SCO '+i_l+' has not sent LMSFinish or Terminate');
	//a_toStore=[];
	Initialized=false;
	IliasLaunch(iv.launchId);
}

// status for navigation-tree
function status4tree(i_sco,s_status,s_time){
	if (typeof(frames.tree)!="undefined"){
		var ico=frames.tree.document.getElementsByName('scoIcon'+i_sco)[0];
		if(typeof(ico)!="undefined"){
			if(s_status==null || s_status=="not attempted") s_status="not_attempted";
			ico.src=decodeURIComponent(iv.img[s_status]);
			if (s_status!='asset'){
				var icotitle = iv.statusTxt.status+': '+iv.statusTxt[s_status];
				if (s_time!=null) icotitle+=' ('+s_time+')';
				ico.title = decodeURIComponent(icotitle);
			}
		}
	}
}

// store data
function IliasCommit() {
	if (a_toStore.length==0){
		message("Nothing to do.");
		return true;
	}
	var s_s="",a_tmp,s_v;
	for (var i=0; i<a_toStore.length; i++){
		a_tmp=a_toStore[i].split(';');
		s_v=getValueIntern(a_tmp[0],a_tmp[1],true); 
		if (s_v != null){
			s_s+="&S["+i+"]="+a_tmp[0]+"&L["+i+"]="+a_tmp[1]+"&R["+i+"]="+s_v;
		}
	}
	a_toStore=[];
	try {
		var ret=sendRequest ("./Modules/ScormAicc/sahs_server.php?cmd=storeJsApi&ref_id="+iv.refId, s_s);
		if (ret!="ok") return false;
		return true;
	} catch (e) {
		warning("Ilias cmi storage failed.");
	}
	return false;
}

// get data
function getValueIntern(i_sco,s_el,b_noDecode){
	var s_sco=""+i_sco,
		a_el=s_el.split('.');
	if (typeof data[s_sco] == "undefined") return null;
	var o_el=data[s_sco];
	for (var i=0;i<a_el.length;i++){
		o_el=o_el[""+a_el[i]];
		if (typeof o_el == "undefined") return null;
	}
	if(b_noDecode!=true) return decodeURIComponent(""+o_el);
	return ""+o_el;
}

// set data
function setValueIntern(i_sco,s_el,s_value,b_store,b_noEncode){
	//create data-elements
	var s_sco=""+i_sco,
		a_el = s_el.split('.');
	if (typeof data[s_sco] == "undefined") data[s_sco]=new Object();
	var o_el=data[s_sco];
	for (var i=0;i<a_el.length-1;i++){
		if (typeof o_el[a_el[i]] == "undefined") o_el[a_el[i]]=new Object();
		o_el=o_el[a_el[i]];
		if(!isNaN(a_el[i+1])) { //set check counter 
			if (typeof o_el['_count'] == "undefined") {
				o_el['_count']=new Number();
				o_el['_count']=0;
			}
			if(b_store){
				if (a_el[i+1] == o_el['_count']) o_el['_count']++;
				if (a_el[i+1] > o_el['_count']) return false;
			} else {
				if (a_el[i+1] >= o_el['_count']) o_el['_count']=a_el[i+1]+1;
			}
		}
	}
	var s2s=a_el[a_el.length-1];
	//store
	if (typeof o_el[s2s] == "undefined") o_el[s2s] = new String();
	if (b_noEncode!=true) s_value=encodeURIComponent(s_value);
	o_el[s2s]=s_value;
	if (b_store){
		for (var i=0;i<a_toStore.length;i++){
			if (a_toStore[i] == s_sco+';'+s_el) b_store=false;
		}
		if (b_store) a_toStore.push(s_sco+';'+s_el);
	}
	return true;
}

// done at start
function basisInit() {
	iv=IliasScormVars;
	ir=IliasScormResources;
	var s_w="";
	for (var i=0; i<IliasScormData.length; i++) {
		if (setValueIntern(IliasScormData[i][0],IliasScormData[i][1],IliasScormData[i][2],false,true) == false)
			s_w+='; sco_id:'+IliasScormData[i][0]+', element:'+IliasScormData[i][1];
	}
	if (s_w != "") warning('Failure read previous data:'+s_w.substr(1));
	
	try{
		delete IliasScormVars;
		delete IliasScormData;
		delete IliasScormResources;
	} catch (e) {
		IliasScormVars={};
		IliasScormData=[];
		IliasScormResources=[];
	}

	if (iv.pingSession>0) SchedulePing();
	if (iv.launchId!=0) IliasWaitLaunch(iv.launchId);
}

//done at end
function onWindowUnload () {
	var s_unload="";
	if (iv.b_autoLastVisited==true) s_unload="last_visited="+iv.launchId;
	sendRequest ("./Modules/ScormAicc/sahs_server.php?cmd=scorm12PlayerUnload&ref_id="+iv.refId, s_unload);
}

this.IliasLaunch=IliasLaunch;
this.IliasAbortSco=IliasAbortSco;
this.IliasWaitLaunch=IliasWaitLaunch;
this.IliasWaitTree=IliasWaitTree;
basisInit();

if(window.addEventListener) window.addEventListener('unload',onWindowUnload);
else if(window.attachEvent) window.attachEvent('onunload',onWindowUnload);//IE<9
else window['onunload']=onWindowUnload;