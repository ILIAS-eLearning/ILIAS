
var	iv={},
	ir=[],
	data={},
	a_toStore=[],
	Initialized=false,
	b_launched=true;


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

function message(s_send){
	s_send = 'lm_'+iv.objId+': '+s_send;
	if (iv.b_messageLog) sendRequest ('./ilias.php?baseClass=ilSAHSPresentationGUI&ref_id='+iv.refId+'&cmd=logMessage', s_send);
}

function warning(s_send){
	s_send = 'lm_'+iv.objId+': '+s_send;
	sendRequest ('./ilias.php?baseClass=ilSAHSPresentationGUI&ref_id='+iv.refId+'&cmd=logWarning', s_send);
}

function SchedulePing() {
	var r = sendRequest('./ilias.php?baseClass=ilSAHSPresentationGUI&cmd=pingSession&ref_id='+iv.refId);
	setTimeout("SchedulePing()", iv.pingSession*1000);
}

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
	if (i_l!=iv.launchId && asset==1) status4tree(iv.launchId,'asset');
	if (asset==1 || Initialized==false){
		b_launched=true;
		frames.sahs_content.document.location.replace(decodeURIComponent(iv.dataDirectory+href));
	}
	else {
		b_launched=false;
		frames.sahs_content.document.location.replace('./Modules/ScormAicc/templates/default/dummy.html');
		setTimeout("API.IliasAbortSco("+iv.launchId+")",3000);
	}
	iv.launchId=i_l;
	status4tree(iv.launchId,'running');
}

function IliasLaunchAfterFinish(){
	if(b_launched==false) IliasLaunch(iv.launchId);
}
function IliasAbortSco(i_l){
	if (b_launched==true) return;
	warning('SCO '+i_l+' has not sent LMSFinish or Terminate');
	//a_toStore=[];
	Initialized=false;
	IliasLaunch(iv.launchId);
}

function launchNext(){
	if (b_autoContinue == true){
		var i_l=0;
		for (var i=0;i<ir.length;i++){
			if (ir[i][0]==iv.launchNr && i<(ir.length-1)){
				i_l=ir[i+1][1];
			}
		}
		if (i_l>0) IliasLaunch(i_l);
	}
}

function status4tree(i_i,s_status,s_time){
	if (typeof(frames.tree)!="undefined"){
		var ico=frames.tree.document.getElementsByName('scoIcon'+i_i)[0];
		if(typeof(ico)!="undefined"){
			ico.src=decodeURIComponent(iv.img[s_status]);
			if (s_status!='asset'){
				var icotitle = iv.statusTxt.status+': '+iv.statusTxt[s_status];
				if (s_time!=null) icotitle+=' ('+s_time+')';
				ico.title = decodeURIComponent(icotitle);
			}
		}
	}
}

function IliasCommit() {
	if (a_toStore.length==0){
		message("Nothing to do.");
		return true;
	}
	var s_s="",a_tmp,s_v;
	for (var i=0; i<a_toStore.length; i++){
		a_tmp=a_toStore[i].split(';');
		s_v=getValueIntern(a_tmp[0],a_tmp[1]);
		if (s_v != null){
			s_s+="&S["+i+"]="+a_tmp[0]+"&L["+i+"]="+a_tmp[1]+"&R["+i+"]="+s_v;
		}
	}
	a_toStore=[];
	try {
		sendRequest ("./Modules/ScormAICC/sahs_server.php?cmd=storeJsApi&ref_id="+iv.refId, s_s);
		return true;
	} catch (e) {
		alert ("Ilias cmi storage failed.");
	}
	return false;
}

function getValueIntern(tsco,s_el){
	var a_el=s_el.split('.');
	if (typeof data[tsco] == "undefined") return null;
	var o_el=data[tsco];
	for (var i=0;i<a_el.length;i++){
		o_el=o_el[""+a_el[i]];
		if (typeof o_el == "undefined") return null;
	}
	return ""+o_el;
}

function getValueInternDecoded(tsco,s_el){
	return decodeURIComponent(getValueIntern(tsco,s_el));
}

function setValueIntern(tsco,s_el,s_value,b_fromSCO){
	//create data-elements
	var a_el = s_el.split('.');
	if (typeof data[tsco] == "undefined") data[tsco]=new Object();
	var o_el=data[tsco];
	for (var i=0;i<a_el.length-1;i++){
		if (typeof o_el[a_el[i]] == "undefined") o_el[a_el[i]]=new Object();
		o_el=o_el[a_el[i]];
		if(!isNaN(a_el[i+1])) { //set check counter 
			if (typeof o_el['_count'] == "undefined") {
				o_el['_count']=new Number();
				o_el['_count']=0;
			}
			if(b_fromSCO){
				if (a_el[i+1] == o_el['_count']) o_el['_count']++;
				if (a_el[i+1] > o_el['_count']) return false;
			} else {
				if (a_el[i+1] >= o_el['_count']) o_el['_count']=a_el[i+1]+1;
			}
		}
	}
	var s2s=a_el[a_el.length-1];
	//store
	if(b_fromSCO) s_value=encodeURIComponent(s_value);
	if (typeof o_el[s2s] == "undefined"){
		o_el[s2s] = new String();
		if(b_fromSCO) a_toStore.push(tsco+';'+s_el);
	}
	else if (b_fromSCO && o_el[s2s] != s_value){
		var b_toStore=true;
		for (i=0;i<a_toStore.length;i++){
			if (a_toStore[i] == tsco+';'+s_el) b_toStore=false;
		}
		if (b_toStore) a_toStore.push(tsco+';'+s_el);
	}
	o_el[s2s]=s_value;
	//change if necessary
	return true;
}

function IliasWaitLaunch(i_l){
	if (typeof frames.sahs_content == "undefined") setTimeout("API.IliasWaitLaunch("+i_l+")",100);
	else API.IliasLaunch(i_l);
}

function basisInit() {
	iv=IliasScormVars;
	ir=IliasScormResources;
	var s_w="";
	for (var i=0; i<IliasScormData.length; i++) {
		if (setValueIntern(IliasScormData[i][0],IliasScormData[i][1],IliasScormData[i][2],false) == false)
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

this.IliasLaunch=IliasLaunch;
this.IliasAbortSco=IliasAbortSco;
this.IliasWaitLaunch=IliasWaitLaunch;
basisInit();
