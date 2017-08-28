function iliasApi() { 
var SOP=true; 
// ====== start basisAPI.js ========== 
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

function toJSONString (v, tab) {
	tab = tab ? tab : "";
	var nl = tab ? "\n" : "";
	function fmt(n) {
		return (n < 10 ? '0' : '') + n;
	}
	function esc(s) {
		var c = {'\b': '\\b', '\t': '\\t', '\n': '\\n', '\f': '\\f', '\r': '\\r', '"' : '\\"', '\\': '\\\\'};
		return '"' + s.replace(/[\x00-\x1f\\"]/g, function (m) {
			var r = c[m];
			if (r) {
				return r;
			} else {
				r = m.charAt(0);
				return "\\u00" + (r < 16 ? '0' : '') + r.toString(16);
			}
		}) + '"';
	}
	switch (typeof v) {
	case 'string':
		return esc(v);
	case 'number':
		return isFinite(v) ? String(v) : 'null';
	case 'boolean':
		return String(v);
	case 'object':
		if (v===null) {
			return 'null';
		} else if (v instanceof Date) {
			return '"' + v.getValue(v) + '"'; // msec not ISO
		} else if (v instanceof Array) {
			var ra = new Array();
			for (var i=0, ni=v.length; i<ni; i+=1) {
				ra.push(v[i]===undefined ? 'null' : toJSONString(v[i], tab.charAt(0) + tab));
			}
			return '[' + nl + tab + ra.join(',' + nl + tab) + nl + tab + ']';
		} else {
			var ro = new Array();
			for (var k in v) {	
				if (v.hasOwnProperty && v.hasOwnProperty(k)) {
					ro.push(esc(String(k)) + ':' + toJSONString(v[k], tab.charAt(0) + tab));
				}
			}
			return '{' + nl + tab + ro.join(',' + nl + tab) + nl + tab + '}';
		}
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
	}
}

// send to logfile
function message(s_send){
	s_send = 'lm_'+iv.objId+': '+s_send;
	if (iv.b_messageLog) sendRequest ('./ilias.php?baseClass=ilSAHSPresentationGUI&ref_id='+iv.refId+'&cmd=logMessage', s_send);
}

function warning(s_send){
	s_send = 'lm_'+iv.objId+': '+s_send;
	//sendRequest ('./ilias.php?baseClass=ilSAHSPresentationGUI&ref_id='+iv.refId+'&cmd=logWarning', s_send);
}

// avoid sessionTimeOut
function SchedulePing() {
	var r = sendRequest('./ilias.php?baseClass=ilSAHSPresentationGUI&cmd=pingSession&ref_id='+iv.refId);
	setTimeout("API.SchedulePing()", iv.pingSession*1000);
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
		if (href.substring(0,4)!="http") {
			href=iv.dataDirectory+href;
			href=href.replace("//","/"); // for relative dataDirectory to avoid double slashes
		}
		frames.sahs_content.document.location.replace(decodeURIComponent(href));
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
		if(typeof API != "undefined") {
			API.IliasLaunch(i_l);
			API.IliasWaitTree(i_l,0);
		} else {
			IliasLaunch(i_l);
			IliasWaitTree(i_l,0);
		}
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
	} else {
		var ico=document.getElementById('scoIcon'+i_sco);
	}
	if(typeof(ico)!="undefined" && ico!=null){
		if(s_status==null || s_status=="not attempted") s_status="not_attempted";
		ico.src=decodeURIComponent(iv.img[s_status]);
		if (s_status!='asset'){
			var icotitle = iv.statusTxt.status+': '+iv.statusTxt[s_status];
			if (s_time!=null) icotitle+=' ('+s_time+')';
			ico.title = decodeURIComponent(icotitle);
		}
	}
}

// store data
function IliasCommit() {
	if (a_toStore.length==0){
		message("Nothing to do.");
		return true;
	}
	var s_s="",a_tmp,s_v,a_cmiTmp,i_numCompleted=0,b_statusFailed=false;
	var LP_STATUS_IN_PROGRESS_NUM=1, LP_STATUS_COMPLETED_NUM=2,LP_STATUS_FAILED_NUM=3;
	var o_data={
		"cmi":[],
		"saved_global_status":iv.status.saved_global_status,
		"now_global_status":1,
		"percentageCompleted":0,
		"lp_mode":iv.status.lp_mode,
		"hash":iv.status.hash,
		"p":iv.status.p,
		"totalTimeCentisec":0
		};
	for (var i=0; i<iv.status.scos.length;i++) {
		s_v=getValueIntern(iv.status.scos[i],"cmi.core.lesson_status",true);
		if (s_v=="completed" || s_v=="passed") i_numCompleted++;
		if (s_v=="failed") b_statusFailed=true;
	}
	if (iv.status.lp_mode == 6) { //distinct scos selected
		if (b_statusFailed == true) o_data.now_global_status = LP_STATUS_FAILED_NUM;
		else if (iv.status.scos.length == i_numCompleted) o_data.now_global_status = LP_STATUS_COMPLETED_NUM;
		o_data.percentageCompleted=Math.round(i_numCompleted*100/iv.status.scos.length);
	}
	for (var i=0; i<ir.length; i++) {
		o_data.totalTimeCentisec+=timestr2hsec(getValueIntern(ir[i][1],"cmi.core.total_time",false));
	}
	for (var i=0; i<a_toStore.length; i++){
		a_tmp=a_toStore[i].split(';');
		s_v=getValueIntern(a_tmp[0],a_tmp[1],false);
		if (s_v != null){
//			s_s+="&S["+i+"]="+a_tmp[0]+"&L["+i+"]="+a_tmp[1]+"&R["+i+"]="+s_v;
			a_cmiTmp=[a_tmp[0],a_tmp[1],s_v];
			o_data.cmi.push(a_cmiTmp);
		}
	}
	a_toStore=[];
	try {
		var ret="";
		if (typeof SOP!="undefined" && SOP==true) ret=saveRequest(o_data);
		else {
		//	s_s=JSON.stringify(o_data);
			s_s=toJSONString(o_data);
			if(typeof iv.b_sessionDeactivated!="undefined" && iv.b_sessionDeactivated==true) {
				ret=sendRequest ("./storeScorm.php?package_id="+iv.objId+"&ref_id="+iv.refId+"&client_id="+iv.clientId+"&do=store", s_s);
			} else {
				ret=sendRequest ("./Modules/ScormAicc/sahs_server.php?cmd=storeJsApi&package_id="+iv.objId+"&ref_id="+iv.refId, s_s);
			}
		}
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

function tree() {
	var s_out="",it,asset=0,spacerwidth=0;
	for (var i=0; i<ist.length; i++) {
		it=ist[i];
		s_out+='<table cellpadding="1" cellspacing="0" border="0"><tr><td nowrap valign="top" align="left">';
		if (it[3]=="sor") s_out+='</td><td align="left"><b>'+decodeURIComponent(it[2])+'</b>';
		else {
			spacerwidth=0;
			for(var z=0;z<=it[1];z++) {
				spacerwidth+=10;
			}
			for(var z=0;z<ir.length;z++) {
				if(it[0]==ir[z][1]) asset=ir[z][2];
			}
			if(asset==1) {
				s_out+='<img class="spacer" src="'+decodeURIComponent(iv.img.asset)+'" id=""';
			} else {
				s_out+='<img class="spacer" src="'+decodeURIComponent(iv.img.not_attempted)+'" id="scoIcon'+it[0]+'"';
			}
			s_out+=' alt="" title="" border="0" style="margin-left:'+spacerwidth+'px"/></td>'
			+'<td align="left"><a href="javascript:void(0);" onclick="API.IliasLaunch('+it[0]+');return false;">'+decodeURIComponent(it[2])+'</a>';
		}
		s_out+='</td></tr></table>';
		document.getElementById("treeView").innerHTML=s_out;
	}
	for (var i=0;i<ir.length;i++) {
		if(ir[i][2]!=1) {
			status4tree(ir[i][1],getValueIntern(ir[i][1],'cmi.core.lesson_status'),getValueIntern(ir[i][1],'cmi.core.total_time'));
		}
	}
}


// done at start
function basisInit() {
	iv=IliasScormVars;
	ir=IliasScormResources;
	ist=IliasScormTree;
	var s_w="";
	for (var i=0; i<IliasScormData.length; i++) {
		if (setValueIntern(IliasScormData[i][0],IliasScormData[i][1],IliasScormData[i][2],false,true) == false)
			s_w+='; sco_id:'+IliasScormData[i][0]+', element:'+IliasScormData[i][1];
	}
	var ipar=[];
	for (var i=0; i<iv.a_itemParameter.length; i++) {
		ipar=iv.a_itemParameter[i];
		if(ipar[3]!=null) setValueIntern(ipar[0],"cmi.student_data.max_time_allowed",ipar[3],false,true);
		if(ipar[4]!=null) setValueIntern(ipar[0],"cmi.student_data.time_limit_action",ipar[4],false,true);
		if(ipar[5]!=null) setValueIntern(ipar[0],"cmi.launch_data",ipar[5],false,true);
		if(ipar[6]!=null) {
			if(iv.i_lessonMasteryScore!="") ipar[6]=iv.i_lessonMasteryScore;
			setValueIntern(ipar[0],"cmi.student_data.mastery_score",ipar[6],false,true);
		}
	}
	if (s_w != "") warning('Failure read previous data:'+s_w.substr(1));
	if (typeof SOP!="undefined" && SOP==true && ir.length>1) tree();
	try{
		delete IliasScormVars;
		delete IliasScormData;
		delete IliasScormResources;
		delete IliasScormTree;
	} catch (e) {
		IliasScormVars={};
		IliasScormData=[];
		IliasScormResources=[];
		IliasScormTree=[];
	}

	if (iv.pingSession>0) SchedulePing();
	if (iv.launchId!=0) IliasWaitLaunch(iv.launchId);
}

//done at end
function onWindowUnload () {
	if (typeof SOP!="undefined" && SOP==true){
		var result = {};
		result["hash"]=iv.status.hash;
		result["p"]=iv.status.p;
		result["last"]="";
		if (iv.b_autoLastVisited==true) result["last"]=iv.launchId;
		result=scormPlayerUnload(result);
	} else {
		var s_unload="";
		if (iv.b_autoLastVisited==true) s_unload="last_visited="+iv.launchId;
		if(typeof iv.b_sessionDeactivated!="undefined" && iv.b_sessionDeactivated==true) {
			sendRequest ("./storeScorm.php?package_id="+iv.objId+"&ref_id="+iv.refId+"&client_id="+iv.clientId+"&hash="+iv.status.hash+"&p="+iv.status.p+"&do=unload", s_unload);
		} else {
			sendRequest ("./Modules/ScormAicc/sahs_server.php?cmd=scorm12PlayerUnload&package_id="+iv.objId+"&ref_id="+iv.refId+"&p="+iv.status.p, s_unload);
		}
	}
}

this.IliasLaunch=IliasLaunch;
this.IliasAbortSco=IliasAbortSco;
this.IliasWaitLaunch=IliasWaitLaunch;
this.IliasWaitTree=IliasWaitTree;
this.SchedulePing=SchedulePing;
basisInit();

if (typeof SOP!="undefined" && SOP==true) {
	window.addEventListener('beforeunload',onWindowUnload);
} else {
	if(window.addEventListener) window.addEventListener('unload',onWindowUnload);
	else if(window.attachEvent) window.attachEvent('onunload',onWindowUnload);//IE<9
	else window['onunload']=onWindowUnload;
}// ====== end basisAPI.js ========== 
// ====== start SCORM1_2standard.js ========== 
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
* @author  Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
* @version $Id$
*/

var errorCode=0,
	sco_id=0,
	diag='',
	entryAtInitialize='',
	totalTimeAtInitialize='',
	b_scoCredit=true;

// DataModel
// rx = regular Expression; ac = accessibility; dv = default value
var rx={
	CMIString255:	'^[\\u0000-\\uffff]{0,255}$',
	CMIString4096:	'^[\\u0000-\\uffff]{0,4096}$',
	CMIIdentifier:	'^[\\u0021-\\u007E]{0,255}$',
	CMIDecimal:		'^100(\\.0+)?$|^\\d?\\d(\\.\\d+)?$', //definition only for mastery_score and weighting
	DecimalOrBlank:	'^100(\\.0+)?$|^\\d?\\d(\\.\\d+)?$|^$',
	CMITimespan:	'^([0-9]{2,4}):([0-9]{2}):([0-9]{2})(\.[0-9]{1,2})?$',
	CMITime:		'^([0-2]{1}[0-9]{1}):([0-5]{1}[0-9]{1}):([0-5]{1}[0-9]{1})(\.[0-9]{1,2})?$'
	};

var model = {
		cmi:{
			_children:{
				ac: 'r',
				dv: 'core,suspend_data,launch_data,comments,objectives,student_data,student_preference,interactions'
			},
			_version:{
				ac: 'r',
				dv: '3.4'
			},
			core:{
				_children:{
					ac: 'r',
					dv: 'student_id,student_name,lesson_location,credit,lesson_status,entry,score,total_time,lesson_mode,exit,session_time'
				},
				credit:{
					rx: '^(no-)?credit$',
					ac: 'r', 
					dv: 'no-credit'
				},
				entry:{
					rx: '^ab-initio$|^resume$',
					ac: 'r',
					dv: 'ab-initio'
				},
				exit:{
					rx: '^time-out$|^suspend$|^logout$|^$',
					ac: 'w'
				},
				lesson_location:{
					rx: rx.CMIString255,
					ac: 'rw',
					dv: ''
				},
				lesson_mode:{
					rx: '^browse$|^normal$|^review$',// specs 3-31: "Right now there is no SCORM defined way to signify that learning content can be taken in dfferent modes. Implementation will therfore be LMS specific."
					ac: 'r',
					dv: 'browse'
				},
				lesson_status:{
					rx: '^passed$|^failed$|^completed$|^incomplete$|^browsed$',
					ac: 'rw',
					dv: 'not attempted'
				},
				score:{
					_children:{
						ac: 'r',
						dv: 'raw,min,max'
					},
					raw:{
						rx: rx.DecimalOrBlank,
						ac: 'rw',
						dv: ''
					},
					min:{
						rx: rx.DecimalOrBlank,
						ac: 'rw',
						dv: ''
					},
					max:{
						rx: rx.DecimalOrBlank,
						ac: 'rw',
						dv: ''
					}
				},
				session_time:{
					rx: rx.CMITimespan,
					ac: 'w'
				},
				student_id:{
					type: rx.CMIIdentifier,
					ac: 'r',
					dv: ''
				},
				student_name:{
					rx: rx.CMIString255,
					ac: 'r',
					dv: ''
				},
				student_login:{ //ILIAS specific
					ac: 'r',
					dv: ''
				},
				student_ou:{ //ILIAS specific
					ac: 'r',
					dv: ''
				},
				total_time:{
					rx: rx.CMITimespan,
					ac: 'r',
					dv: "00:00:00"
				}
			},
			comments:{
				rx: rx.CMIString4096,
				ac: 'rw',
				dv: ''
			},
			comments_from_lms:{
				rx: rx.CMIString4096, 
				ac: 'r',
				dv: ''
			},
			interactions:{
				_children:{
					ac: 'r',
					dv: 'id,correct_responses,latency,student_response,objectives,result,time,type,weighting'
				},
				_count:{
					ac: 'r',
					dv: '0'
				},
				n:{
					id:{
						rx: rx.CMIIdentifier,
						ac: 'w'
					},
					correct_responses:{
						_count:{
							ac: 'r',
							dv: '0'
						},
						n:{
							pattern:{
								rx: rx.CMIString255,//could be done if interaction_type is set before 
								ac: 'w'
							}
						}
					},
					latency:{
						rx: rx.CMITimespan,
						ac: 'w'
					},
					student_response:{
						rx: rx.CMIString255,//no exact check possible if interaction_type is not set
						ac: 'w'
					},
					objectives:{
						_count:{
							ac: 'r',
							dv: '0'
						},
						n:{
							id:{
								rx: rx.CMIIdentifier,
								ac: 'w'
							}
						}
					},
					result:{
						rx: '^correct$|^wrong$|^unanticipated$|^neutral$|^-?\\d+(\\.\\d+)?$',//'^correct$|^wrong$|^unanticipated$|^neutral$|^([0-9]{0,3})?(\.[0-9]{1,2})?$',
						ac: 'w'
					},
					time:{
						rx: rx.CMITime,
						ac: 'w'
					},
					type:{
						rx: '^true-false$|^choice$|^fill-in$|^matching$|^performance$|^sequencing$|^likert$|^numeric$',
						ac: 'w'
					},
					weighting:{
						rx: rx.CMIDecimal,
						ac: 'w'
					}
				}
			},
			launch_data:{
				rx: rx.CMIString4096,
				ac: 'r',
				dv: ''
			},
			objectives:{
				_children:{
					ac: 'r',
					dv: 'id,score,status'
				},
				_count:{
					ac: 'r',
					dv: '0'
				},
				n:{
					id:{
						rx: rx.CMIIdentifier,
						ac: 'rw'
					},
					score:{
						_children:{
							ac: 'r',
							dv: 'raw,min,max'
						},
						raw:{
							rx: rx.DecimalOrBlank,
							ac: 'rw',
							dv: ''
						},
						min:{
							rx: rx.DecimalOrBlank,
							ac: 'rw',
							dv: ''
						},
						max:{
							rx: rx.DecimalOrBlank,
							ac: 'rw',
							dv: ''
						}
					},
					status:{
						rx: '^passed$|^completed$|^failed$|^incomplete$|^browsed$|^not attempted$',
						ac: 'rw',
						dv: 'not attempted'
					}
				}
			},
			student_data:{
				_children:{
					ac: 'r',
					dv: 'mastery_score,max_time_allowed,time_limit_action'
				},
				mastery_score:{
					rx: rx.CMIDecimal,
					ac: 'r',
					dv: ''
				},
				max_time_allowed:{
					rx: rx.CMITimespan,
					ac: 'r',
					dv: ''
				},
				time_limit_action:{
					rx: '^exit,message$|^exit,no message$|^continue,message$|^continue,no message$',
					ac: 'r',
					dv: 'continue,no message'
				}
			},
			student_preference:{
				_children:{
					ac: 'r',
					dv: 'audio,language,speed,text'
				},
				audio:{
					rx: '^-1$|^100$|^\\d?\\d$',
					ac: 'rw',
					dv: '0'
				},
				language:{
					rx: rx.CMIString255,
					ac: 'rw',
					dv: ''
				},
				speed:{
					rx: '^-?(100|\\d?\\d)$',
					ac: 'rw',
					dv: '0'
				},
				text:{
					rx: '^-1$|^0$|^1$',
					ac: 'rw',
					dv: '0'
				}
			},
			suspend_data:{
				rx: rx.CMIString4096,
				ac: 'rw',
				dv: ''
			}
		}
	};

function getElementModel(s_el){
	var a_elmod=s_el.split('.');
	var o_elmod=model[a_elmod[0]];
	if (typeof o_elmod == "undefined") return null;
	for (var i=1;i<a_elmod.length;i++){
		if (isNaN(a_elmod[i])) o_elmod=o_elmod[a_elmod[i]];
		else o_elmod=o_elmod['n'];
		if (typeof o_elmod == "undefined") return null;
	}
	if (typeof o_elmod['ac'] == "undefined") return null;
	return o_elmod;
}

function addTime(s_a,s_b) {
	var i_hs=timestr2hsec(s_a)+timestr2hsec(s_b);
	return hsec2timestr(i_hs);
}

function timestr2hsec(st) {
	if(st=="" || typeof st=="undefined" || st==null) return 0;
	var a1=st.split(":");
	var a2=a1[2].split(".");
	var it=360000*parseInt(a1[0],10) + 6000*parseInt(a1[1],10) + 100*parseInt(a2[0],10);
	if (a2.length>1) {
		if(a2[1].length==1) it+=10*parseInt(a2[1],10);
		else it+=parseInt(a2[1],10);
	}
	return it;
}

function hsec2timestr(ts){
	function fmt(ix){
		var sx=Math.floor(ix).toString();
		if(ix<10) sx="0"+sx;
		return sx;
	}
	var ic=ts%100;
	var is=(ts%6000)/100;
	var im=(ts%360000)/6000;
	var ih=ts/360000;
	if(ih>9999) ih=9999;
	if(ic == 0) return fmt(ih)+":"+fmt(im)+":"+fmt(is);
	return fmt(ih)+":"+fmt(im)+":"+fmt(is)+"."+fmt(ic);
}

function LMSInitialize(param){
	function setreturn(thisErrorCode,thisDiag){
		errorCode=thisErrorCode;
		diag=thisDiag;
		var s_return="false";
		if(errorCode==0) s_return="true";
		showCalls('LMSInitialize("'+param+'")',s_return,errorCode,diag);
		return s_return;
	}
	if (param!=="") return setreturn(201,"param must be empty string");
	if (Initialized) return setreturn(101,"already initialized");
	Initialized=true;
	errorCode=0;
	diag='';
	sco_id=iv.launchId;
	//to avoid additional commits at LMSFinish, values for elements entry and total_time are stored separatly
	totalTimeAtInitialize=getValueIntern(sco_id,'cmi.core.total_time');
	if (totalTimeAtInitialize==null) totalTimeAtInitialize=model.cmi.core.total_time.dv;

	entryAtInitialize="";
	if (getValueIntern(sco_id,'cmi.core.entry') == null && getValueIntern(sco_id,'cmi.core.exit') == null) {
		entryAtInitialize=model.cmi.core.entry.dv;
		setValueIntern(sco_id,'cmi.core.entry',"",true);
	}
	if (getValueIntern(sco_id,'cmi.core.exit') == 'suspend'){
		entryAtInitialize='resume';
		setValueIntern(sco_id,'cmi.core.entry','resume',true);
//	} else { //some other than 1.2
//		save total_time ...
//		data[sco_id]=new Object();
	}

	var mode=iv.lesson_mode;
	if (iv.b_autoReview==true) {
		var st=getValueIntern(sco_id,'cmi.core.lesson_status');
		if (st=="completed" || st=="passed" || getValueIntern(sco_id,'cmi.core.lesson_mode')=="review") {
			mode='review';
			entryAtInitialize=""; //specs 3-26
			setValueIntern(sco_id,'cmi.core.entry',"",true);
		}
	}
	setValueIntern(sco_id,'cmi.core.lesson_mode',mode,true);

	b_scoCredit=false;
	if (mode == 'normal') {
		setValueIntern(sco_id,'cmi.core.credit',iv.credit,true);
		if (iv.credit == 'credit') b_scoCredit=true;
	} else {
		setValueIntern(sco_id,'cmi.core.credit','no-credit',true);
	}
	
	if(iv.b_readInteractions==false) {
		var o_i=data[""+sco_id];
		o_i=o_i["cmi"];
		o_i["interactions"]=new Object();
	}

	APIcallStartTimeMS=new Date().getTime();

	initDebug();

	return setreturn(0,"");
}

function LMSCommit(param) {
	function setreturn(thisErrorCode,thisDiag){
		errorCode=thisErrorCode;
		diag=thisDiag;
		var s_return="false";
		if(errorCode==0) s_return="true";
		showCalls('LMSCommit("'+param+'")',s_return,errorCode,diag);
		return s_return;
	}
	if (param!=="") return setreturn(201,"param must be empty string");
	if (!Initialized) return setreturn(301,"");
	if (iv.c_storeSessionTime == "i") {
		var timeNowMS=new Date().getTime();
		var i_sessionTimeHsec=Math.round((timeNowMS-APIcallStartTimeMS)/10);
		var s_sessionTime=hsec2timestr(i_sessionTimeHsec);
		var b_result=setValueIntern(sco_id,'cmi.core.session_time',s_sessionTime,true);
		var ttime = addTime(totalTimeAtInitialize, s_sessionTime);
		b_result=setValueIntern(sco_id,'cmi.core.total_time',ttime,true);
	}
	if (IliasCommit()==false) return setreturn(101,"LMSCommit was not successful");
	else return setreturn(0,"");
}

function LMSFinish(param){
	function setreturn(thisErrorCode,thisDiag){
		errorCode=thisErrorCode;
		diag=thisDiag;
		var s_return="false";
		if(errorCode==0) s_return="true";
		showCalls('LMSFinish("'+param+'")',s_return,errorCode,diag);
		return s_return;
	}
	if (param!=="") return setreturn(201, "param must be empty string");
	if (!Initialized) return setreturn(301,"");
	// rte3-25
	if (getValueIntern(sco_id,'cmi.core.lesson_status')==null || getValueIntern(sco_id,'cmi.core.lesson_status')=="not attempted")
		b_result=setValueIntern(sco_id,'cmi.core.lesson_status','completed',true,true);
	if (getValueIntern(sco_id,'cmi.core.lesson_status')=='completed' && getValueIntern(sco_id,'cmi.student_data.mastery_score')!=null && getValueIntern(sco_id,'cmi.core.score.raw')!=null) {
		if (parseFloat(getValueIntern(sco_id,'cmi.core.score.raw')) < parseFloat(getValueIntern(sco_id,'cmi.student_data.mastery_score'))) {
			b_result=setValueIntern(sco_id,'cmi.core.lesson_status','failed',true,true);
		}
		if (parseFloat(getValueIntern(sco_id,'cmi.core.score.raw')) >= parseFloat(getValueIntern(sco_id,'cmi.student_data.mastery_score'))) {
			b_result=setValueIntern(sco_id,'cmi.core.lesson_status','passed',true,true);
		}
	}
	if (IliasCommit()==false) return setreturn(101,"LMSFinish was not successful because of failure with implicit LMSCommit");
	Initialized=false;
	IliasLaunchAfterFinish(sco_id);

	//With Fix for InternetExplorer to avoid searching API in a non-available opener after closing tab
	var windowOpenerLoc;
	try{windowOpenerLoc=window.opener.location;}catch(e){}
	window.opener=null;
	try{windowOpenerLoc.reload();} catch(e){}

	return setreturn(0,"");
}

function LMSGetLastError() {
	showCalls('LMSGetLastError()',""+errorCode);
	return ""+errorCode;
}

function getErrorStringIntern(ec){
	var s_error="";
	ec=""+ec;
	if (ec!=""){
		s_error='error';
		switch(ec){
			case "0"	: s_error = 'No Error';break;
			case "101"	: s_error = 'General Exception';break;
			case "201"	: s_error = 'Invalid argument error';break;
			case "202"	: s_error = 'Element cannot have children';break;
			case "203"	: s_error = 'Element not an array - Cannot have count';break;
			case "301"	: s_error = 'Not initialized';break;
			case "401"	: s_error = 'Not implemented error';break;
			case "402"	: s_error = 'Invalid set value, element is a keyword';break;
			case "403"	: s_error = 'Element is read only';break;
			case "404"	: s_error = 'Element is write only';break;
			case "405"	: s_error = 'Incorrect Data Type';break;
		}
	}
	return s_error;
}
function LMSGetErrorString(ec){
	s_err=getErrorStringIntern(ec);
	showCalls('LMSGetErrorString("'+ec+'")',s_err);
	return s_err;
}

function LMSGetDiagnostic(param){
	var s_return="";
	if (param==""){
		if (diag=="") s_return='no additional info for last error with error code '+errorCode;
		else s_return='additional info for last error with error code '+errorCode+': '+diag;
	} else {
		s_return='no additional info for error code '+param;
	}
	showCalls('LMSGetDiagnostic("'+param+'")',s_return);
	return s_return;
}

function LMSGetValue(s_el){
	function setreturn(thisErrorCode,thisDiag,value){
		errorCode=thisErrorCode;
		diag=thisDiag;
		var s_return="";
		if(errorCode==0) s_return=value;
		showCalls('LMSGetValue("'+s_el+'")',s_return,errorCode,diag);
		return s_return;
	}
	var value="";
	s_el=""+s_el;
	if (!Initialized) return setreturn(301,"");
	if (s_el=="" || s_el==null) return setreturn(201,"");
	//check if model exists
	var o_elmod=getElementModel(s_el);
	if (o_elmod==null){
		var a_el=s_el.split('.');
		if (a_el[a_el.length-1]=="_children") return setreturn(202,"");
		if (a_el[a_el.length-1]=="_count") return setreturn(203,"");
		return setreturn(201,"element not exists");
	}
	//check if writeable
	if (o_elmod['ac'] == "w") return setreturn(404,"");
	if (s_el=='cmi.core.total_time') value=totalTimeAtInitialize;
	else if (s_el=='cmi.core.entry') value=entryAtInitialize;
	else value=getValueIntern(sco_id,s_el);
	if (value != null) return setreturn(0,"",value);
	if (typeof o_elmod['dv'] == "undefined" || o_elmod['dv'] == null) return setreturn(0,"not set","");
	else return setreturn(0,"",decodeURIComponent(o_elmod['dv']));
	return setreturn(101,"");
}

function LMSSetValue(s_el,value){
	function setreturn(thisErrorCode,thisDiag){
		errorCode=thisErrorCode;
		diag=thisDiag;
		var s_return="false",s_v='"';
		if(errorCode==0) s_return="true";
		if(errorCode==405) s_v='';
		showCalls('LMSSetValue("'+s_el+'",'+s_v+value+s_v+')',s_return,errorCode,diag);
		return s_return;
	}
	//check value
	if (typeof value == "undefined") return setreturn(405,"Value cannot be type undefined");
	else if (value==null) return setreturn(405,"Value cannot be null");
	else if (typeof value == "object") return setreturn(405,"Value cannot be an object");
	else if (typeof value == "function") return setreturn(405,"Value cannot be a function");
	else if (typeof value == "number") value = value.toString(10);
	value=""+value;
	//check state
	if (!Initialized) return setreturn(301,"");
	//check element
	if (s_el=="" || s_el==null) return setreturn(201,"LMSSetValue without element");
	if (typeof s_el != "string") return setreturn(201,"element of LMSSetValue must be type string");
	//check if keyword
	if (s_el.indexOf('_children')>-1 || s_el.indexOf('_count')>-1) return setreturn(402,"");
	//check if model exists
	var o_elmod=getElementModel(s_el);
	if (o_elmod==null) return setreturn(201,"");
	//check if writeable
	if (o_elmod['ac'] == "r") return setreturn(403,"");
	//Format-/Range-Checker
	if(iv.b_checkSetValues){
		var trx = new RegExp(o_elmod['rx']);
		if (value.match(trx) == null) return setreturn(405,"");
	}
	//store
	var b_storeDB=true;
	var b_result=true;
	if (s_el=='cmi.core.session_time' && iv.c_storeSessionTime=="s"){
		var ttime = addTime(totalTimeAtInitialize, value);
		b_result=setValueIntern(sco_id,'cmi.core.total_time',ttime,true);
	}
	if (s_el=='cmi.core.exit'){
		if (value=='suspend') b_result=setValueIntern(sco_id,'cmi.core.entry',"resume",true);
		else b_result=setValueIntern(sco_id,'cmi.core.entry',"",true);
	}
	//since 5.2
	if (iv.lesson_mode == 'browse'){
		b_storeDB=false;
	} else {
		if (b_scoCredit==false && (s_el.indexOf("score")>-1 || s_el.indexOf("status")>-1)) return setreturn(0,"");
	}

	if (iv.b_storeInteractions==false && s_el.indexOf("cmi.interactions")>-1) b_storeDB=false;
	else if (iv.b_storeObjectives==false && s_el.indexOf("cmi.objectives")>-1) b_storeDB=false;
	b_result=setValueIntern(sco_id,s_el,value,b_storeDB);
	if (b_result==false) return setreturn(201,"out of order");
	return setreturn(0,"");
}

function init(){
	model.cmi.core.student_id.dv=""+iv.studentId;
	model.cmi.core.student_name.dv=iv.studentName;
	model.cmi.core.student_login.dv=iv.studentLogin;
	model.cmi.core.student_ou.dv=iv.studentOu;
	model.cmi.core.credit.dv=iv.credit;
}

this.LMSInitialize=LMSInitialize;
this.LMSFinish=LMSFinish;
this.LMSGetValue=LMSGetValue;
this.LMSSetValue=LMSSetValue;
this.LMSCommit=LMSCommit;
this.LMSGetLastError=LMSGetLastError;
this.LMSGetErrorString=LMSGetErrorString;
this.LMSGetDiagnostic=LMSGetDiagnostic;
init();
// ====== end SCORM1_2standard.js ========== 
// ====== start SopAddendum.js ========== 
// ====== end SopAddendum.js ========== 
} 
