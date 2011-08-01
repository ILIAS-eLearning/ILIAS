var errorCode=0,
	sco_id=0,
	diag='',
	totatTimeAtInitialize='00:00:00';
	
/*	
	b_commitTerminated=true,
	b_checkSetValues=true,
	b_storeObjectives=true,
	b_storeInteractions=true,
	b_readInteractions=false,
	c_storeSessionTime='n';//n=no store, s=session_time by sco, i=session_time by LMS
//autocommit bei ping oder ping wenn keine Daten; Zeit beachten
*/


// DataModel
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
					rx: '^browse$|^normal$|^review$',
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
//Version
//functions for mastery_score etc.

function getElementModel(s_el){
	var a_elmod=s_el.split('.');
	var o_elmod=model[a_elmod[0]];
	for (var i=1;i<a_elmod.length;i++){
		if (isNaN(a_elmod[i])) o_elmod=o_elmod[a_elmod[i]];
		else o_elmod=o_elmod['n'];
		if (typeof o_elmod == "undefined") return null;
	}
	if (typeof o_elmod['ac'] == "undefined") return null;
	return o_elmod;
}

function AddTime(first, second) {
	var sFirst = first.split(":");
	var sSecond = second.split(":");
	var cFirst = sFirst[2].split(".");
	var cSecond = sSecond[2].split(".");
	var change = 0;

	FirstCents = 0;  //Cents
	if (cFirst.length > 1) {
		FirstCents = parseInt(cFirst[1],10);
	}
	SecondCents = 0;
	if (cSecond.length > 1) {
		SecondCents = parseInt(cSecond[1],10);
	}
	var cents = FirstCents + SecondCents;
	change = Math.floor(cents / 100);
	cents = cents - (change * 100);
	if (Math.floor(cents) < 10) {
		cents = "0" + cents.toString();
	}

	var secs = parseInt(cFirst[0],10)+parseInt(cSecond[0],10)+change;  //Seconds
	change = Math.floor(secs / 60);
	secs = secs - (change * 60);
	if (Math.floor(secs) < 10) {
		secs = "0" + secs.toString();
	}

	mins = parseInt(sFirst[1],10)+parseInt(sSecond[1],10)+change;   //Minutes
	change = Math.floor(mins / 60);
	mins = mins - (change * 60);
	if (mins < 10) {
		mins = "0" + mins.toString();
	}

	hours = parseInt(sFirst[0],10)+parseInt(sSecond[0],10)+change;  //Hours
	if (hours < 10) {
		hours = "0" + hours.toString();
	}

	if (cents != '0') {
		return hours + ":" + mins + ":" + secs + '.' + cents;
	} else {
		return hours + ":" + mins + ":" + secs;
	}
}


function LMSInitialize(param){
	function setreturn(thisErrorCode,thisDiag){
		//if Log ...
		errorCode=thisErrorCode;
		diag=thisDiag;
		if(errorCode==0) return "true";
		else return "false";
	}
	if (param!=="") return setreturn(201,"param must be empty string");
	if (Initialized) return setreturn(101,"already initialized");
	Initialized=true;
	errorCode=0;
	diag='';
	sco_id=iv.launchId;
	if (getValueIntern(sco_id,'cmi.core.exit') == 'suspend'){
		setValueIntern(sco_id,'cmi.core.entry','resume',false);
//	} else {
//		save total_time
//		data[sco_id]=new Object();
	}
	var mode=iv.lesson_mode;
	if (iv.b_autoReview==true) {
		var st=getValueIntern(sco_id,'cmi.core.lesson_status');
		if (st=="completed" || st=="passed" || st=="failed") mode='review';
	}
	setValueIntern(sco_id,'cmi.core.lesson_mode',mode,false);
	if (mode == 'normal') setValueIntern(sco_id,'cmi.core.credit',iv.credit,false);
	else setValueIntern(sco_id,'cmi.core.credit','no-credit',false);
	
	var totatTimeAtInitialize=getValueInternDecoded(sco_id,'cmi.core.total_time');
	if (totatTimeAtInitialize==null) totatTimeAtInitialize=model.cmi.core.total_time.dv;
	
	return setreturn(0,"");
}

function LMSCommit(param) {
	function setreturn(thisErrorCode,thisDiag){
		//if Log ...
		errorCode=thisErrorCode;
		diag=thisDiag;
		if(errorCode==0) return "true";
		else return "false";
	}
	if (param!=="") return setreturn(201,"param must be empty string");
	if (!Initialized) return setreturn(301,"");
	if (IliasCommit()==false) return setreturn(101,"LMSCommit was not successful");
	else return setreturn(0,"");
}


function LMSFinish(param){
	function setreturn(thisErrorCode,thisDiag){
		//if Log ...
		errorCode=thisErrorCode;
		diag=thisDiag;
		if(errorCode==0) return "true";
		else return "false";
	}
	if (param!=="") return setreturn(201, "param must be empty string");
	if (!Initialized) return setreturn(301,"");
	if (IliasCommit()==false) return setreturn(101,"LMSFinish was not successful because of failure with implicit LMSCommit");
	Initialized=false;
	IliasLaunchAfterFinish();
	return setreturn(0,"");
}

function LMSGetLastError() {
	return ""+errorCode;
}

function LMSGetErrorString(ec){
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
	// showCalls("LMSGetErrorString("+ec+")",s_error,"");
	return s_error;
}

function LMSGetDiagnostic(param){
	if (param==""){
		if (diag=="") return 'no additional info for last error with error code '+errorCode;
		else return 'additional info for last error with error code '+errorCode+': '+diag;
	} else {
		return 'no additional info for error code '+param;
	}
}


function LMSGetValue(s_el){
	function setreturn(thisErrorCode,thisDiag,value){
		//if Log ...
		errorCode=thisErrorCode;
		diag=thisDiag;
		if(errorCode==0) return value;
		else return "";
	}
	var value="";
	s_el=""+s_el;
	if (!Initialized) return setreturn(301,"");
	if (s_el=="" || s_el==null) return setreturn(201,"");
	//check if model exists
	var o_elmod=getElementModel(s_el);
	if (o_elmod==null){
		var a_el=s_el.split('.');
		if (a_el[a_el.length-1]=="_children") return setreturn(202,"element cannot have children");
		if (a_el[a_el.length-1]=="_count") return setreturn(203,"element cannot have count");
		return setreturn(201,"element not exists");
	}
	//check if writeable
	if (o_elmod['ac'] == "w") return setreturn(404,"element is WriteOnly");
	if (s_el=='cmi.core.total_time') value=totatTimeAtInitialize;
	else value=getValueIntern(sco_id,s_el);
	if (value != null) return setreturn(0,"",decodeURIComponent(value));
	if (typeof o_elmod['dv'] == "undefined") return setreturn(0,"not set","");
	else return setreturn(0,"",o_elmod['dv']);
	return setreturn(101,"");
}

function LMSSetValue(s_el,value){
	function setreturn(thisErrorCode,thisDiag){
		//if Log ...
		errorCode=thisErrorCode;
		diag=thisDiag;
		if(errorCode==0) return "true";
		else return "false";
	}
	s_el=""+s_el;
	if (value==null) value="";
	value=""+value;
	if (!Initialized) return setreturn(301,"");
	if (s_el=="" || s_el==null) return setreturn(201,"");
	//check if model exists
	var o_elmod=getElementModel(s_el);
	if (o_elmod==null) return setreturn(201,"");
	//check if writeable
	if (o_elmod['ac'] == "r") return setreturn(201,"");//falsch writerror
	//Format-/Range-Checker
	if(iv.b_checkSetValues){
		var trx = new RegExp(o_elmod['rx']);
		if (value.match(trx) == null) return setreturn(201,"");//falsch? writerror
	}
	//store
	var b_result=setValueIntern(sco_id,s_el,value,true);
	if (b_result==false) return setreturn(201,"out of order");
	if (s_el=='cmi.core.session_time'){
		var ttime = AddTime(totatTimeAtInitialize, value);
		b_result=setValueIntern(sco_id,'cmi.core.total_time',ttime,true);
	}
	return setreturn(0,"");
}

function init(){
	model.cmi.core.student_id.dv=""+iv.studentId;
	model.cmi.core.student_name.dv=iv.studentName;
	model.cmi.core.student_login.dv=iv.studentLogin;
	model.cmi.core.student_ou.dv=iv.studentOu;
	model.cmi.core.credit=iv.credit;
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
