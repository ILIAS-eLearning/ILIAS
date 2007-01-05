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
/// Modul: ADL SCORM 1.3 Runtime API Helper Data

/**
 * IEEE 1484 11
 */

/**
 * data type definitions of API_1484_11 (see 'SCORM Run-Time Environment Version 1.3' on www.adlnet.org)
 * each data type is given by an object (not a constructor function) with implements at least
 * a isValid method to validate string input according to the restrictions of the data type
 * isValid returns a boolean
 * isValid takes the following parameters: value, min, max, pattern, context
 * where only valid is mandatory, all other optional
 * context gives a reference to the parent object of the validated attribute
 */ 

OP_SCORM_RUNTIME.prototype = 
{
	'name' : "API_1484_11",
	'errors' : 
	{
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
	},
	'methods' : 
	{
		'Initialize' : 'Initialize', 
		'Terminate' : 'Terminate', 
		'GetValue' : 'GetValue', 
		'SetValue' : 'SetValue', 
		'Commit' : 'Commit', 
		'GetLastError' : 'GetLastError', 
		'GetErrorString' : 'GetErrorString', 
		'GetDiagnostic' : 'GetDiagnostic', 
		'AddEventListener' : 'AddEventListener', 
		'RemoveEventListener' : 'RemoveEventListener'
	},
	'models' :  
	{
		'cmi' : new function() { // implements API_1484_11
	
			// private constants: permission
			var READONLY  = 1;
			var WRITEONLY = 2;
			var READWRITE = 3;
		
			var AudioCaptioningState = { isValid : function (value) {
				var valueRange = {'-1':1, '0':2, '1':3};
				return valueRange[value]>0;}
			};
			var CompletionState = { isValid : function (value) {
				var valueRange = {'completed':1, 'incomplete':2, 'not_attempted':3, 'unknown':4};
				return valueRange[value]>0;}
			};
			var CreditState = { isValid : function (value) {
				var valueRange = {'credit':1, 'no_credit':2};
				return valueRange[value]>0;}
			};
			var EntryState = { isValid : function (value) {
				var valueRange = {'ab_initio':1, 'resume':2, '':3};
				return valueRange[value]>0;}
			};
			var ExitState = { isValid : function (value) {
				var valueRange = {'timeout':1, 'suspend':2, 'logout':3, 'normal':4, '':5};
				return valueRange[value]>0;}
			};
			var InteractionType = { isValid : function (value) {
				var valueRange = {'true_false':1, 'multiple_choice':2, 'fill_in':3, 'long_fill_in':4, 'matching':5, 'performance':6, 'sequencing':7, 'likert':8, 'numeric':9, 'other':10};
				return valueRange[value]>0;}
			};
			var Interval = { isValid : function (value, min, max, pattern) {
				return /^P(\d+Y)?(\d+M)?(\d+D)?(T(\d+H)?(\d+M)?(\d+S(\.\d[1,2])?)?)?$/.test(value);}
			};
			var LangType = { isValid : function (value, min, max, pattern) {
				return /^[a-z]{2}(-[A-Z]{1,3})?$/.test(value);}
			};
			var LearnerResponseType = { isValid : function (value, min, max, pattern, context) {
				// TODO prüfen relativ zu den Möglichkeiten
				return true;}
			};
			var LocalizedString = { isValid : function (value, min, max, pattern) {
				return CharacterString.isValid(value, min, max, /$\{lang=[a-z]{2}(-[A-Z]{2,3})?\}/);}
			};
			var ModeState = { isValid : function (value) {
				var valueRange = {'browse':1, 'normal':2, 'review':3};
				return valueRange[value]>0;}
			};
			var PatternType = { isValid : function (value, min, max, pattern, context) {
				// TODO prüfen relativ zu den Möglichkeiten
				return true;}
			};
			var ResultState = { isValid : function (value) {
				var valueRange = {'correct':1, 'wrong':2, 'unanticipated':3, 'neutral':4, 'real':5};
				return valueRange[value]>0;}
			};
			var SuccessState = { isValid : function (value) {
				var valueRange = {'passed':1, 'failed':2, 'unknown':3};
				return valueRange[value]>0;}
			};
			var Time = { isValid : function (value, min, max, pattern) {
				// TODO noch sauberer machen
				return /^\d{4}-[0-1]\d-[0-3]\dT[0-2]\d:[0-5]\d:[0-5]\d(.\d)?(Z|[-+][0-2]\d:[0-5]\d)?$/.test(value);}
			};
			var TimeLimitAction = { isValid : function (value) {
				var valueRange = {'exit_message':1, 'continue_message':2, 'exit_no_message':3, 'continue_no_message':4};
				return valueRange[value]>0;}
			};
			var Uri = { isValid : function (value, min, max, pattern) {
				// TODO noch sauberer machen
				return CharacterString.isValid(value, min, max, pattern);}
			};
			var CharacterString = { isValid : function (value, min, max, pattern) {
				if (arguments.length>1 && String(value).length < min) return false;
				if (arguments.length>2 && String(value).length > max) return false;
				if (pattern && pattern.test(value)) return false;
				return true;}
			};
			var Real = { isValid : function (value, min, max, pattern) {
				if (!/^\d+(\.\d+)?$/.test(value)) return false;
				if (arguments.length>1 && Number(value) < min) return false;
				if (arguments.length>2 && Number(value) > max) return false;
				if (pattern && !pattern.test(value)) return false;
				return true;}
			};
			
			/**
			 * data model definition of API_1484_11 (see 'SCORM Run-Time Environment Version 1.3	 	 	 	 	 	 	 	 	 	 	 	 	  on www.adlnet.org)
			 * the definition is a nested ecma object with each node having the follwing properties
			 * minOccur, maxOccur, type, permission, children, min, max, pattern, default
			 */
			this.cmi = {minOccur : 1, maxOccur : 1, type : Object, permission: READWRITE,
				children : { 
					comments_from_learner : {minOccur: 0, maxOccur: 250, type: Array, permission: READWRITE, 
						children: {
							comment : {minOccur: 1, maxOccur: 1, type: LocalizedString, max: 4000, permission: READWRITE},
							date_time : {minOccur: 1, maxOccur: 1, type: Time, permission: READWRITE},
							location : {minOccur: 1, maxOccur: 1, type: CharacterString, max: 250, permission: READWRITE}
						}
					},
					comments_from_lms : {minOccur: 0, maxOccur: 250, type: Array, permission: READWRITE, 
						children: {
							comment : {minOccur: 1, maxOccur: 1, type: LocalizedString, max: 4000, permission: READWRITE},
							date_time : {minOccur: 1, maxOccur: 1, type: Time, permission: READWRITE},
							location : {minOccur: 1, maxOccur: 1, type: CharacterString, max: 250, permission: READWRITE}
						}
					},
					completion_status : {minOccur: 1, maxOccur: 1, type: CompletionState, permission: READWRITE, 'default' : 'unknown'},
					completion_threshold : {minOccur: 1, maxOccur: 1, type: Real, min: 0, max: 1, permission: READONLY},
					credit : {minOccur: 1, maxOccur: 1, type: CreditState, permission: READONLY, 'default' : 'credit'},
					entry : {minOccur: 1, maxOccur: 1, type: EntryState, permission: READONLY, 'default' : ''},
					exit : {minOccur: 1, maxOccur: 1, type: ExitState, permission: WRITEONLY, 'default' : ''},
					interactions: {minOccur: 0, maxOccur: 250, type: Array, permission: READWRITE, 
						children: {
							correct_responses: {minOccur: 0, maxOccur: 250, type: Array, permission: READWRITE, 
								children: {
									pattern : {minOccur: 1, maxOccur: 1, type: PatternType, permission: READWRITE}
								}
							},
							description:  {minOccur: 1, maxOccur: 1, type: LocalizedString, max: 250, permission: READWRITE},
							id: {minOccur: 1, maxOccur: 1, type: Uri, max: 4000, permission: READWRITE},
							latency:  {minOccur: 1, maxOccur: 1, type: Interval, permission: READWRITE},
							learner_response:  {minOccur: 1, maxOccur: 1, type: LearnerResponseType, permission: READWRITE},
							objectives: {minOccur: 0, maxOccur: 250, type: Array, permission: READWRITE, 
								children : { 
									id : {minOccur: 1, maxOccur: 1, type: Uri, max: 4000, permission: READWRITE}
								}
							},
							result:  {minOccur: 1, maxOccur: 1, type: ResultState, permission: READWRITE},
							timestamp: {minOccur: 1, maxOccur: 1, type: Time, permission: READWRITE},
							type: {minOccur: 1, maxOccur: 1, type: InteractionType, permission: READWRITE},
							weighting:  {minOccur: 1, maxOccur: 1, type: Real, permission: READWRITE}
						}
					}, 
					launch_data : {minOccur: 1, maxOccur: 1, type: CharacterString, max: 4000, permission: READONLY, 'default' : ''},
					learner_id : {minOccur: 1, maxOccur: 1, type: CharacterString, max: 4000, permission: READONLY, 'default' : ''},
					learner_name : {minOccur: 1, maxOccur: 1, type: LocalizedString, max: 250, permission: READONLY, 'default' : ''},
					learner_preference: {minOccur: 1, maxOccur: 1, type: Object, permission: READONLY, 
						children : {
							audio_level: {minOccur: 1, maxOccur: 1, type: Real, min: 0, permission: READONLY},
							language : {minOccur: 1, maxOccur: 1, type: LangType, permission: READWRITE, 'default': ''}, 
							delivery_speed : {minOccur: 1, maxOccur: 1, type: Real, min: 0, permission: READWRITE, 'default': 1}, 
							audio_captioning : {minOccur: 1, maxOccur: 1, type: AudioCaptioningState, permission: READWRITE, 'default': '0'}
						}				
					}, 
					location : {minOccur: 1, maxOccur: 1, type: CharacterString, max: 1000, permission: READWRITE, 'default' : ''},
					max_time_allowed : {minOccur: 1, maxOccur: 1, type: Interval, permission: READONLY},
					mode: {minOccur: 1, maxOccur: 1, type: ModeState, permission: READONLY, 'default' : 'normal'},
					objectives: {minOccur: 0, maxOccur: 100, type: Array, permission: READWRITE, 
						children: {
							completion_status: {minOccur: 1, maxOccur: 1, type: CompletionState, permission: READWRITE, 'default': 'unknown'},
							description:  {minOccur: 1, maxOccur: 1, type: LocalizedString, max: 250, permission: READWRITE},
							id: {minOccur: 1, maxOccur: 1, type: Uri, max: 4000, permission: READWRITE},
							score: {minOccur: 1, maxOccur: 1, type: Object, permission: READWRITE, 
								children: {
									scaled : {minOccur: 1, maxOccur: 1, type: Real, min: -1, max: 1, permission: READWRITE},
									raw : {minOccur: 1, maxOccur: 1, type: Real, permission: READWRITE},
									min : {minOccur: 1, maxOccur: 1, type: Real, permission: READWRITE},
									max : {minOccur: 1, maxOccur: 1, type: Real, permission: READWRITE}
								}
							},
							success_status: {minOccur: 1, maxOccur: 1, type: SuccessState, permission: READWRITE, 'default': 'unknown'}
						}
					},
					progress_measure : {minOccur: 1, maxOccur: 1, type: Real, min: 0, max: 1, permission: READWRITE},
					scaled_passing_score : {minOccur: 1, maxOccur: 1, type: Real, min: -1, max: 1, permission: READONLY},
					score: {minOccur: 1, maxOccur: 1, type: Object, permission: READWRITE, 
						children: {
							scaled : {minOccur: 1, maxOccur: 1, type: Real, min: -1, max: 1, permission: READWRITE},
							raw : {minOccur: 1, maxOccur: 1, type: Real, permission: READWRITE},
							min : {minOccur: 1, maxOccur: 1, type: Real, permission: READWRITE},
							max : {minOccur: 1, maxOccur: 1, type: Real, permission: READWRITE}
						}
					},
					session_time : {minOccur: 1, maxOccur: 1, type: Interval, permission: WRITEONLY},
					success_status : {minOccur: 1, maxOccur: 1, type: SuccessState, permission: READWRITE, 'default' : 'unknown'},
					suspend_data : {minOccur: 1, maxOccur: 1, type: CharacterString, max: 4000, permission: READWRITE},
					time_limit_action : {minOccur: 1, maxOccur: 1, type: TimeLimitAction, permission: READONLY},
					total_time : {minOccur: 1, maxOccur: 1, type: Interval, permission: READONLY, 'default' : 'PT0H0M0S'}
				}
			}
		} // end cmi model
	},
	
	'onTerminate' : function (getValue, setValue, msec)
	{
		function msecToDuration(ms) 
		{
			var t, d = new Date(ms), r = ['P'];
			if (t = d.getFullYear()) r.push(t + 'Y');
			if (t = d.getMonth()) r.push(t + 'M');
			if (t = d.getDate()) r.push(t + 'D');
			r.push('T');
			if (t = d.getHours()) r.push(t + 'H');
			if (t = d.getMinutes()) r.push(t + 'M');
			if (t = d.getSeconds()*1000+d.getMilliseconds()) r.push(t/1000 + 'S');
			return r.join("");
		}
		function durationToMsec(str) 
		{
			var m = String(str).match(/^P(\d+Y)?(\d+M)?(\d+D)?(T(\d+H)?(\d+M)?((\d+)(\.\d+)?S)?)?$/);
			var d = !m ? null : new Date(
				m[1] ? parseInt(m[1]) : 0,
				m[2] ? parseInt(m[2]-1) : 0,
				m[3] ? parseInt(m[3]) : 0,
				m[5] ? parseInt(m[5]) : 0,
				m[6] ? parseInt(m[6]) : 0,
				m[8] ? parseInt(m[8]) : 0,
				m[9] ? parseInt(m[9]) : 0
			);
			return d.getTime();
		}
		var credit = getValue('cmi.credit', true);
		var entry = getValue('cmi.entry', true);
		var exit = getValue('cmi.exit', true);
		var lesson_mode = getValue('cmi.mode', true);
		var lesson_status = getValue('cmi.success_status', true);
		var session_time = durationToMsec(getValue('cmi.session_time'), true);
		var total_time = durationToMsec(getValue('cmi.total_time'), true);
		var elapsed_time = (new Date()).getTime() - msec;
		if (lesson_mode == 'normal') {
			var completed = /^completed|passed|failed$/;
			setValue('cmi.exit', ((credit == 'credit' && (completed.test(lesson_status))) ? '' : 'suspend'), true);
			if (!session_time) 
			{
				session_time = elapsed_time;
				setValue('cmi.session_time', msecToDuration(session_time), true);
			}
			total_time += session_time;
			setValue('cmi.total_time', msecToDuration(total_time), true);
		}
		if (lesson_status === 'not attempted') 
		{
			setValue('cmi.success_status', 'incomplete', true);
		}
		setValue('cmi.entry', ((exit === 'suspend') ? 'resume' : ''), true);
	}

};
