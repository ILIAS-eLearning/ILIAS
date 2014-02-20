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
//	if (getValueIntern(sco_id,'cmi.core.exit') == null && getValueIntern(sco_id,'cmi.core.entry') != 'resume') {
//		setValueIntern(sco_id,'cmi.core.entry',"",true);
//	}
	if (IliasCommit()==false) return setreturn(101,"LMSFinish was not successful because of failure with implicit LMSCommit");
	Initialized=false;
	IliasLaunchAfterFinish(sco_id);
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
	if (iv.b_storeInteractions==false && s_el.indexOf("cmi.interactions")>-1) b_storeDB=false;
	else if (iv.b_storeObjectives==false && s_el.indexOf("cmi.objectives")>-1) b_storeDB=false;
	if (b_scoCredit==false && (s_el.indexOf("score")>-1 || s_el.indexOf("status")>-1)) b_storeDB=false;

	var b_result=setValueIntern(sco_id,s_el,value,b_storeDB);
	if (b_result==false) return setreturn(201,"out of order");
	if (s_el=='cmi.core.session_time' && iv.c_storeSessionTime=="s"){
		var ttime = addTime(totalTimeAtInitialize, value);
		b_result=setValueIntern(sco_id,'cmi.core.total_time',ttime,true);
	}
	if (s_el=='cmi.core.exit'){
		if (value=='suspend') b_result=setValueIntern(sco_id,'cmi.core.entry',"resume",true);
		else b_result=setValueIntern(sco_id,'cmi.core.entry',"",true);
	}
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
