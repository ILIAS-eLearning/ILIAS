/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * Copyright (c) 2005-2007 Alfred Kohnert.
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * You must not remove this notice, or any other, from this software.
 * 
 * Note: This code derives from other work by the original author that has been
 * published under Common Public License (CPL 1.0). Please send mail for more
 * information to <alfred.kohnert@bigfoot.com>.
 * 
 * Derived from OpenPALMS Humbaba
 * An ECMAScript/JSON Implementation of the ADL-RTE Version API.
 */ 

/// PRELIMINARY EDITION 
/// This is work in progress and therefore incomplete and buggy ... 

/// Content-Type: application/x-javascript; charset=ISO-8859-1		
/// Modul: ADL SCORM 1.2 Runtime API Helper Data

	/**
	 * Wrapping up on explicit or implcit termination	
	 * explicit call by SCO through API.Terminate()
	 * implicit call by "the user navigates away"	
	 * This should be part of DELIVER/UNDELIVER process
	 *	@access private
	 */	 

	
	/**
	 * Wrapping up on explicit or implcit termination	
	 * explicit call by SCO through API.Terminate()
	 * implicit call by "the user navigates away"	
	 * This should be part of DELIVER/UNDELIVER process
	 *	@access private
	 */	 

OP_SCORM_RUNTIME.prototype =  
{
	"name" : "API",
	'errors' : 
	{
		// error codes and descriptions (see "SCORM Run-Time Environment Version 1.2" on www.adlnet.org)
		// TODO set correct mappings
		  0 : {code: 0, message: 'No error'},
		101 : {code: 101, message: 'General Exeption'},
		102 : {code: 102, message: 'General Initialization Failure'},
		103 : {code: 103, message: 'Already Initialized'},
		104 : {code: 104, message: 'Content Instance Terminated'},
		111 : {code: 111, message: 'General Termination Failure'},
		112 : {code: 112, message: 'Termination Before Initialization'},
		113 : {code: 113, message: 'Termination After Termination'},
		122 : {code: 122, message: 'Retrieve Data Before Initialization'},
		123 : {code: 123, message: 'Retrieve Data After Termination'},
		132 : {code: 132, message: 'Store Data Before Initialization'},
		133 : {code: 133, message: 'Store Data After Termination'},
		142 : {code: 142, message: 'Commit Before Initialization'},
		143 : {code: 143, message: 'Commit After Termination'},
		201 : {code: 201, message: 'General Argument Error'}, 
		301 : {code: 301, message: 'General Get Failure'},
		351 : {code: 351, message: 'General Set Failure'}, 
		391 : {code: 391, message: 'General Commit Failure'},
		401 : {code: 401, message: 'Undefined Data Model Element'},
		402 : {code: 402, message: 'Unimplemented Data Model Element'},
		403 : {code: 403, message: 'Data Model Element Value Not Initialized'},
		404 : {code: 404, message: 'Data Model Element Is Read Only'},
		405 : {code: 405, message: 'Data Model Element Is Write Only'},
		406 : {code: 406, message: 'Data Model Element Type Mismatch'},
		407 : {code: 407, message: 'Data Model Element Value Out Of Range'},
		408 : {code: 408, message: 'Data Model Dependency Not Established'},
		/*
			  0: {code: 0, message: "No error"},
			101: {code: 101, message: "General exeption"},
			201: {code: 201, message: "Invalid argument error"},
			202: {code: 202, message: "Element cannot have children"},
			203: {code: 203, message: "Element not an array - cannot have a count"},
			301: {code: 301, message: "Not initialized"},
			401: {code: 401, message: "Not implemented error"},
			402: {code: 402, message: "Invalid set value}, element is a keyword",
			403: {code: 403, message: "Element is read only"},
			404: {code: 404, message: "Element is write only"},
			405: {code: 405, message: "Incorrect data type"}
		*/
	},
	
	'methods' :
	{
		'LMSInitialize' : 'Initialize', 
		'LMSFinish' : 'Terminate', 
		'LMSGetValue' : 'GetValue', 
		'LMSSetValue' : 'SetValue', 
		'LMSCommit' : 'Commit', 
		'LMSGetLastError' : 'GetLastError', 
		'LMSGetErrorString' : 'GetErrorString', 
		'LMSGetDiagnostic' : 'GetDiagnostic', 
		'LMSAddEventListener' : 'AddEventListener', 
		'LMSRemoveEventListener' : 'RemoveEventListener'
	},
	
	'models': 
	{
		'cmi' : new function() {// implements API (SCORM 1.2)
	
			// private constants: permission
			var READONLY  = 1;
			var WRITEONLY = 2;
			var READWRITE = 3;
		
			var AudioType = { isValid : function (value) {
				return (/^\-1|1?\d?\d$/).test(value);}
			};
			var CharacterString = { isValid : 
				function (value, min, max, pattern) {
					if (arguments.length>1 && String(value).length < min) {
						return false;
					} else if (arguments.length>2 && String(value).length > max) {
						return false;
					} else if (pattern && pattern.test(value)) {
						return false;
					} else {
						return true;
					}
				}
			};
			var CreditState = { isValid : function (value) {
				return (/^(no\-)?credit$/).test(value);}
			};
			var EntryState = { isValid : function (value) {
				return (/^ab-initio|resume|$/).test(value);}
			};
			var Decimal = { isValid : function (value) {
				return (/^1?\d?\d(\.\d+)?$/).test(value);}
			};
			var DecimalOrBlank = { isValid : function (value) {
				return value==="" || Decimal.isValid(value);}
			};
			var ExitState = { isValid : function (value) {
				return (/^time\-out|suspend|logout|$/).test(value);}
			};
			var IdentifierType = { isValid : function (value) {
				return (/^[-_A-Za-z0-9]{0,255}$/).test(value);}
			};
			var InteractionType = { isValid : function (value) {
				return (/^true-false|choice|fill\-in|matching|performance|sequencing|likert|numeric$/).test(value);}
			};
			var IntervalType = { isValid : function (value) {
				return (/^\d{2,4}:\d\d:\d\d(\.\d{1,2})?$/).test(value);}
			};
			var LanguageType = { isValid : function (value) {
				return (/^.{0,255}$/).test(value);}
			};
			var LessonModeState = { isValid : function (value) {
				return (/^browse|normal|review$/).test(value);}
			};
			var StudentResponseType = { isValid : function (value, min, max, pattern, path, data) {
				// e.g. cmi.interactions.3.student_response
				if (path[path.length-2]==='interactions') {
					var p = path.slice(0, -1).concat('type').join('.');
					switch (data[p]) {
						case 'true-false': return value.test(/^0|1|t|f$/);
						case 'choice': return value.test(/^\{[0-9a-z](,[0-9a-z])*\}|[0-9a-z](,[0-9a-z])*$/);
						case 'fill-in': return CharacterString.isValid(value, 0, 255);
						case 'matching': return value.test(/^\{[0-9a-z]\.[0-9a-z](,[0-9a-z]\.[0-9a-z])*\}|[0-9a-z]\.[0-9a-z](,[0-9a-z]\.[0-9a-z])*$/);
						case 'performance': return value.test(/^0|1|t|f$/);
						case 'sequencing': return value.test(/^[0-9a-z](,[0-9a-z])+$/);
						case 'likert': return value.test(/^[0-9a-z]$/);
						case 'numeric': return value.test(/^-?\d+(\.\d+)$/); // no scientific numbers, no localized numbers
					}
				}
				return false;}
			};
			var ResultState = { isValid : function (value) {
				return (/^correct|wrong|unanticipated|neutral$/).test(value) || Decimal.isValid(value);}
			};
			var SpeedType = { isValid : function (value) {
				return (/^\-?1?\d?\d$/).test(value);}
			};
			var SuccessState = { isValid : function (value) {
				return (/^passed|failed|completed|incomplete|browsed|not attempted$/).test(value);}
			};
			var TextType = { isValid : function (value) {
				return (/^\-1|0|1$/).test(value);}
			};
			var TimeType = { isValid : function (value, min, max, pattern) {
				return (/^\d\d:\d\d:\d\d(\.\d)?$/).test(value);}
			};
			var TimeLimitAction = { isValid : function (value) {
				return (/^exit,message|exit,no message|continue,message|continue,no message$/).test(value);}
			};
		
			// data model definition of API (see "SCORM Run-Time Environment Version 1.2" on www.adlnet.org)
			// the definition is a nested ecma object with each node having the following properties
			// minOccur, maxOccur, type, permission, children, min, max, pattern, default
			this.cmi = {minOccur : 1, maxOccur : 1, type : Object, permission: READWRITE,
				children : {
					core: {minOccur: 1, maxOccur: 1, type: Object, permission: READONLY,
						children : {
							credit : {minOccur: 1, maxOccur: 1, type: CreditState, permission: READONLY, "default" : "credit"},
							entry : {minOccur: 1, maxOccur: 1, type: EntryState, permission: READONLY, "default" : ""},
							exit : {minOccur: 1, maxOccur: 1, type: ExitState, permission: WRITEONLY, "default" : ""},
							lesson_location : {minOccur: 1, maxOccur: 1, type: CharacterString, max: 255, permission: READWRITE, "default" : ""},
							lesson_mode: {minOccur: 1, maxOccur: 1, type: LessonModeState, permission: READONLY, "default" : "normal"},
							lesson_status : {minOccur: 1, maxOccur: 1, type: SuccessState, permission: READWRITE, "default" : "not attempted"},
							score: {minOccur: 1, maxOccur: 1, type: Object, permission: READWRITE,
								children: {
									raw : {minOccur: 1, maxOccur: 1, type: DecimalOrBlank, permission: READWRITE},
									min : {minOccur: 1, maxOccur: 1, type: DecimalOrBlank, permission: READWRITE},
									max : {minOccur: 1, maxOccur: 1, type: DecimalOrBlank, permission: READWRITE}
								}
							},
							session_time : {minOccur: 1, maxOccur: 1, type: IntervalType, permission: WRITEONLY},
							student_id : {minOccur: 1, maxOccur: 1, type: InteractionType, permission: READONLY, "default" : ""},
							student_name : {minOccur: 1, maxOccur: 1, type: CharacterString, max: 255, permission: READONLY, "default" : ""},
							total_time : {minOccur: 1, maxOccur: 1, type: IntervalType, permission: READONLY, "default" : "00:00:00"}
						}
					},
					comments : {minOccur: 0, maxOccur: 1, type: CharacterString, max: 4096, permission: READWRITE},
					comments_from_lms : {minOccur: 0, maxOccur: 1, type: CharacterString, max: 4096, permission: READWRITE},
					interactions: {minOccur: 0, maxOccur: 250, type: Array, permission: READWRITE,
						children: {
							correct_responses: {minOccur: 0, maxOccur: 250, type: Array, permission: READWRITE,
								children: {
									pattern : {minOccur: 1, maxOccur: 1, type: StudentResponseType, permission: READWRITE}
								}
							},
							id: {minOccur: 1, maxOccur: 1, type: IdentifierType, permission: READWRITE},
							latency:  {minOccur: 1, maxOccur: 1, type: IntervalType, permission: READWRITE},
							student_response:  {minOccur: 1, maxOccur: 1, type: StudentResponseType, permission: READWRITE},
							objectives: {minOccur: 0, maxOccur: 250, type: Array, permission: READWRITE,
								children : {
									id : {minOccur: 1, maxOccur: 1, type: IdentifierType, permission: READWRITE}
								}
							},
							result:  {minOccur: 1, maxOccur: 1, type: ResultState, permission: READWRITE},
							time:  {minOccur: 1, maxOccur: 1, type: TimeType, permission: WRITEONLY},
							type: {minOccur: 1, maxOccur: 1, type: InteractionType, permission: READWRITE},
							weighting:  {minOccur: 1, maxOccur: 1, type: Decimal, permission: READWRITE}
						}
					},
					launch_data : {minOccur: 1, maxOccur: 1, type: CharacterString, max: 4096, permission: READONLY, "default" : ""},
					objectives: {minOccur: 0, maxOccur: 100, type: Array, permission: READWRITE,
						children: {
							id: {minOccur: 1, maxOccur: 1, type: IdentifierType, permission: READWRITE},
							score: {minOccur: 1, maxOccur: 1, type: Object, permission: READWRITE,
								children: {
									raw : {minOccur: 1, maxOccur: 1, type: DecimalOrBlank, permission: READWRITE},
									min : {minOccur: 1, maxOccur: 1, type: DecimalOrBlank, permission: READWRITE},
									max : {minOccur: 1, maxOccur: 1, type: DecimalOrBlank, permission: READWRITE}
								}
							},
							status: {minOccur: 1, maxOccur: 1, type: SuccessState, permission: READWRITE, "default": "unknown"}
						}
					},
					student_data: {minOccur: 1, maxOccur: 1, type: Object, permission: READONLY,
						children : {
							mastery_score : {minOccur: 1, maxOccur: 1, type: Decimal, permission: READONLY},
							max_time_allowed : {minOccur: 1, maxOccur: 1, type: IntervalType, permission: READONLY},
							time_limit_action : {minOccur: 1, maxOccur: 1, type: TimeLimitAction, permission: READONLY}
						}
					},
					student_preference: {minOccur: 1, maxOccur: 1, type: Object, permission: READONLY,
						children : {
							audio: {minOccur: 1, maxOccur: 1, type: AudioType, permission: READONLY},
							language : {minOccur: 1, maxOccur: 1, type: LanguageType, permission: READWRITE, "default": ""},
							speed : {minOccur: 1, maxOccur: 1, type: SpeedType, permission: READWRITE, "default": 1},
							text : {minOccur: 1, maxOccur: 1, type: TextType, permission: READWRITE, "default": "0"}
						}
					},
					suspend_data : {minOccur: 1, maxOccur: 1, type: CharacterString, max: 4096, permission: READWRITE}
				}
			};
			// added for extensibility
			this.models = {'cmi':this.cmi};
		} // end cmi model
	}, // end models
	
	'onTerminate' : function (getValue, setValue, msec)
	{
		function fmt(v, n) 
		{
			v = Math.floor(v).toString(); 
			return '0000'.substring(0, n-v.length) + v;
		}
		function msecToTime(ms) 
		{
			var r = [(ms%86400000)/3600000, (ms%3600000)/60000, (ms%60000)/1000, ms%1000];
			return fmt(r[0], 4) + ':' + fmt(r[1], 2) + ':' + fmt(r[2], 2) + '.' + fmt(r[3], 2);
		}
		function timeToMsec(str) {
			var m = String(str).match(/(\d{2,4}):(\d{2}):(\d{2})(\.\d{1,2})?/);
			if (!m) 
			{
				m = [null, 0, 0, 0, 0];
			}
			return 3600000*m[1] + 60000*m[2] + 1000*m[3] + 1000*m[4];
		}
		var credit = getValue("cmi.core.credit", true);
		var entry = getValue("cmi.core.entry", true);
		var exit = getValue("cmi.core.exit", true);
		var lesson_mode = getValue("cmi.core.lesson_mode", true);
		var lesson_status = getValue("cmi.core.lesson_status", true);
		var session_time = timeToMsec(getValue("cmi.core.session_time"), true);
		var total_time = timeToMsec(getValue("cmi.core.total_time"), true);
		var elapsed_time = (new Date()).getTime() - msec;
		if (lesson_mode == "normal") {
			var completed = /^completed|passed|failed$/;
			setValue("cmi.core.exit", ((credit == "credit" && (completed.test(lesson_status))) ? "" : "suspend"), true);
			if (!session_time) {
				session_time = elapsed_time;
				setValue("cmi.core.session_time", msecToTime(session_time), true);
			}
			total_time += session_time;
			setValue("cmi.core.total_time", msecToTime(total_time), true);
		}
		if (lesson_status === "not attempted") {
			setValue("cmi.core.lesson_status", "incomplete", true);
		}
		setValue("cmi.core.entry", ((exit === "suspend") ? "resume" : ""), true);
	}

};
