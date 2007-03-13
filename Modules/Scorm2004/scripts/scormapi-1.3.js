/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * Note: This code derives from other work by the original author that has been
 * published under Common Public License (CPL 1.0). Please send mail for more
 * information to <alfred.kohnert@bigfoot.com>.
 * 
 * You must not remove this notice, or any other, from this software.
 * 
 * PRELIMINARY EDITION
 * This is work in progress and therefore incomplete and buggy ...
 * 
 * Derived from OpenPALMS Humbaba
 * An ECMAScript/JSON Implementation of the ADL-RTE Version API.
 * 
 * Content-Type: application/x-javascript; charset=ISO-8859-1
 * Modul: ADL SCORM 1.3 Runtime API Helper Data
 *  
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2005-2007 Alfred Kohnert
 */ 


/**
 * IEEE 1484 11
 */

/**
 * data type definitions of API_1484_11 (see 'SCORM Run-Time Environment Version 1.3' on www.adlnet.org)
 * each data type is given by an object (not a constructor function) with implements at least
 * a isValid method to validate string input according to the restrictions of the data type
 * isValid returns a boolean
 * isValid takes the following parameters: value, min, max, pattern, extra
 * where only valid is mandatory, all other optional
 * extra gives an associative array for reading and writing contextual values used for subelements
 */ 

OP_SCORM_RUNTIME.prototype = 
{
	'name' : "API_1484_11",
	'errors' : 
	{
		  0 : {code:   0, message: 'No error'},
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
		408 : {code: 408, message: 'Data Model Dependency Not Established'}
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
				return (/^-1|0|1$/).test(value);}
			};
			
			var CompletionState = { isValid : function (value) {
				var valueRange = {'completed':1, 'incomplete':2, 'not_attempted':3, 'unknown':4};
				return valueRange[value]>0;}
			};
			
			var CreditState = { isValid : function (value) {
				var valueRange = {'credit':1, 'no-credit':2};
				return valueRange[value]>0;}
			};
			
			var EntryState = { isValid : function (value) {
				var valueRange = {'ab_initio':1, 'resume':2, '':3};
				return valueRange[value]>0;}
			};
			
			var ExitState = { isValid : function (value) {
				var valueRange = {'time-out':1, 'suspend':2, 'logout':3, 'normal':4, '':5};
				return valueRange[value]>0;}
			};
			
			var InteractionType = { isValid : function (value) {
				var valueRange = {'true-false':1, 'choice':2, 'fill-in':3, 'long-fill-in':4, 'matching':5, 'performance':6, 'sequencing':7, 'likert':8, 'numeric':9, 'other':10};
				return valueRange[value]>0;
			}};
			
			var Interval = { isValid : function (value) {
				return (/^P(\d+Y)?(\d+M)?(\d+D)?(T(\d+H)?(\d+M)?(\d+(\.\d{1,2})?S)?)?$/).test(value);
			}};
			
			var LangType = { isValid : function (value) {
				return (/^[a-z]{1,3}(-{2,8})?$/).test(value);}
			};
			
			var LearnerResponseType = { isValid : function (value, definition, extra) {
				var r;
				var par = extra.parent[extra.parent.length-1];
				var key = {};
				var val;
				var short_identifier_type = /^.{1,250}$/; 
				var xshort_identifier_type = /^.{0,250}$/; // check this where id may be empty, alas
				var i;
				switch (par.type)
				{
				case 'true-false':
					r = (/^true|false$/).test(value);
					break; 
				case 'choice':
					val = value.split("[,]");
					if (val.length>36) {
						extra.error = {code: 351};
					}
					for (i=val.length; i--;) {
						if (key[val[i]] && !short_identifier_type.test(val[i])) {
							r = false;
							break; 
						}
						key[val[i]] = true; 
					}
					r = true;
					break; 
				case 'fill-in':
					val = value.split("[,]");
					if (val.length>10) {
						extra.error = {code: 351};
					}
					for (i=val.length; i--;) {
						val[i] = val[i].split(/^\{lang=\w{1-3}(-\w{2-8})?\}/).pop();
						if (extra.error || !(/^.{0,250}$/).test(val[i])) {
							r = false;
							break;
						}
					} 
					r = true;
					break;
				case 'long-fill-in':
					val = value.split(/^\{lang=\w{1-3}(-\w{2-8})?\}/).pop();
					r = (/^.{0,4000}$/).test(val);
					break; 
				case 'likert':
					r = short_identifier_type.test(value); 
					break; 
				case 'matching':
					val = value.split("[,]");
					if (val.length>36) {
						extra.error = {code: 351};
					}
					for (i=val.length; i--;) 
					{
						val[i] = val[i].split("[.]");
						if (val[i].length===1 ||
							!short_identifier_type.test(val[i][0]) ||
							!short_identifier_type.test(val[i][1])) {
								r = false;
								break;
						} 
					}
					r = !extra.error;
					break; 
				case 'performance':
					val = val.split(/^\{order_matters=(true|false)\}/).pop();
					val = val.split("[,]");
					if (val.length>250) 
					{
						extra.error = {code: 351};
					}
					for (i=val.length; i--;) 
					{
						val[i] = val[i].split("[.]");
						if (val[i].length===1 || !short_identifier_type.test(val[i][0])) 
						{
							r = false;
							break;
						} 
					}
					return !extra.error;
					break; 
				case 'sequencing':
					val = value.split("[,]");
					if (val.length>36) 
					{
						extra.error = {code: 351};
					}
					for (i=val.length; i--;) 
					{
						if (key[val[i]]!==undefined || short_identifier_type.test(val[i])) 
						{
							r = false;
							break;
						}
					}
					r = !extra.error;
					break; 
				case 'numeric':
					r = /^\d{1,10}(\.\d{1,7})$/;
					r = r.test(value);
					break; 
				case 'other':
					r = (/^.{0,4000}$/).test(value);
					break; 
				} // end switch
				return r;
			}};
			
			var LocalizedString = { isValid : function (value, definition) {
				// Note that LocalizedString is badly defined by IEEE/ADL for there is no escaping for lang delimiters strings
				// that are meant as strings and not as delimiter. Either lang delimiter should be mandatory or an escaping rule 
				// should be specified. So the following expression is TRUE for ALL strings.
				// SPM for LocalizedString should be incremented by at least 19 chars to add space for optional delimiter.
				var extra = {};
				if (definition.max) {
					extra.max = definition.max+19;
				}
				extra.pattern = /^(\{lang=([a-z]{2,3}|i|x)(-([a-z]{2}|[a-z]{3,8}))?\})?/i;				
				return CharacterString.isValid(value, definition, extra);}
			};
			
			var ModeState = { isValid : function (value) {
				var valueRange = {'browse':1, 'normal':2, 'review':3};
				return valueRange[value]>0;}
			};
			
			var PatternType = { isValid : function (value, definition, extra) {
				var r;
				var dat = extra.parent[extra.parent.length-1];
				var par = extra.parent[extra.parent.length-2];
				var sib = par.correct_responses;
				var len = sib ? sib.length : 0;
				var key = {};
				var val, i;
				if (len && sib[0] != extra.parent[extra.parent.length-1]) 
				{
					len++;
				}
				var short_identifier_type = /^.{1,250}$/; 
				switch (par.type)
				{
				case 'true-false':
					if (len>1) 
					{
						extra.error = {code: 351};
					}
					r = !extra.error && (/^true|false$/).test(value); 
					break;
				case 'choice':
					if (len>10) 
					{
						extra.error = {code: 351};
					}
					val = value.split("[,]");
					if (val.length>36) 
					{
						extra.error = {code: 351};
					}
					for (i=val.length; i--;) 
					{						
						if (key[val[i]] || !short_identifier_type.test(val[i])) {
							r = false;
							break;
						}
						key[val[i]] = true;
					}
					if (sib) 
					{
						for (i=sib.length; i--;) 
						{
							if (sib[i].pattern===value) 
							{
								extra.error = {code: 351};
							}
						}
					}
					r = !extra.error;
					break;
				case 'fill-in':
					if (len>5) 
					{
						extra.error = {code: 351};
					}
					val = value.split("[,]");
					if (val.length>36) 
					{
						extra.error = {code: 351}; // this not given in specs but inferred
					}
					for (i=val.length; i--;) 
					{
						val[i] = val[i].split(/^\{case_matters=(true|false)\}/).pop();
						val[i] = val[i].split(/^\{order_matters=(true|false)\}/).pop();
						val[i] = val[i].split(/^\{lang=\w{1-3}(-\w{2-8})?\}/).pop();
						if (extra.error || !(/^.{0,250}$/).test(val[i])) 
						{
							r = false;
							break;
						}
					} 
					r = true;
					break;
				case 'long-fill-in':
					if (len>5) 
					{
						extra.error = {code: 351};
					}
					val = value.split(/^\{case_matters=(true|false)\}/).pop();
					val = val.split(/^\{lang=\w{1-3}(-\w{2-8})?\}/).pop();
					r = !extra.error && (/^.{0,4000}$/).test(val);
					break;
				case 'likert':
					if (len>1) 
					{
						extra.error = {code: 351};
					}
					r = !extra.error && short_identifier_type.test(value); 
					break;
				case 'matching':
					if (len>5) 
					{
						extra.error = {code: 351};
					}
					val = value.split("[,]");
					if (val.length>36) 
					{
						extra.error = {code: 351};
					}
					for (i=val.length; i--;) 
					{
						val[i] = val[i].split("[.]");
						if (val[i].length===1 ||
							!short_identifier_type.test(val[i][0])	||
							!short_identifier_type.test(val[i][1])) {
							r = false;
							break;
						} 
					}
					r = !extra.error;
					break;
				case 'performance':
					if (len>5) 
					{
						extra.error = {code: 351};
					}
					val = val.split(/^\{order_matters=(true|false)\}/).pop();
					val = val.split("[,]");
					if (val.length>125) 
					{
						extra.error = {code: 351};
					}
					for (i=val.length; i--;) 
					{
						val[i] = val[i].split("[.]");
						if (val[i].length===1 || !short_identifier_type.test(val[i][0])) 
						{
							r = false;
							break;
						} 
					}
					r = !extra.error;
					break;
				case 'sequencing':
					if (len>5) 
					{
						extra.error = {code: 351};
					}
					val = val[i].split("[,]");
					if (val.length>36) 
					{
						extra.error = {code: 351};
					}
					for (var j=val.length; j--;) 
					{
						if (key[val[j]]!==undefined || short_identifier_type.test(val[j])) 
						{
							r = false;
							break;
						}
						key[val[j]] = true;
					}
					if (sib) 
					{
						for (i=sib.length; i--;) 
						{
							if (sib[i].pattern===value) 
							{
								extra.error = {code: 351};
							}
						}
					}
					r = !extra.error;
					break;
				case 'numeric':
					if (len>1) {
						extra.error = {code: 351};
					}
					val = value.split("[:]");
					r = /^\d{1,10}(\.\d{1,7})$/;
					r = !extra.error && val.length===2 && (r.test(val[0]) || r.test(val[1]));
					break;
				case 'other':
					if (len>1) 
					{
						extra.error = {code: 351};
					}
					r = !extra.error && (/^.{0,4000}$/).test(val);
					break;
				} // end switch
				return r;
			}};
			
			var ResultState = { isValid : function (value) {
				var valueRange = {'correct':1, 'incorrect':2, 'unanticipated':3, 'neutral':4, 'real':5};
				return valueRange[value]>0;}
			};
			
			var SuccessState = { isValid : function (value) {
				var valueRange = {'passed':1, 'failed':2, 'unknown':3};
				return valueRange[value]>0;}
			};
			
			var Time = { isValid : function (value) {
				// in deviation from ADL this parses timezone even when no seconds or minutes given
				// as in YYYY[-MM[-DD[Thh[:mm[:ss[.s]]][TZD]]]] for better support of ISO format
				var r = /^\d{4}(-\d{2}(-\d{2}(T\d{2}(:\d{2}(:\d{2}(\.\d{1,2})?)?)?(Z|[-+](\d\d):(\d\d))?)?)?)?$/;				
				var m = String(value).match(r);
				return m && 
					(!m[1] || m[1].substr(1,2)<13) && // month 
					(!m[2] || m[2].substr(1,2)<32) && // day 
					(!m[3] || m[3].substr(1,2)<25) && // hours
					(!m[4] || m[4].substr(1,2)<60) && // minutes
					(!m[5] || m[5].substr(1,2)<60) && // seconds
					(!m[8] || m[8].substr(1,2)<25) && // zone hours
					(!m[9] || m[9].substr(1,2)<60); // zone minutes
			}};
			
			var TimeLimitAction = { isValid : function (value) {
				var valueRange = {'exit,message':1, 'continue,message':2, 'exit,no message':3, 'continue,no message':4};
				return valueRange[value]>0;}
			};
			
			var Uri = { isValid : function (value, definition, extra) {
				extra.pattern = /.{1,}/;
				return CharacterString.isValid(value, definition, extra);}
			};
			
			var CharacterString = { isValid : function (value, definition, extra) {
				var min = extra.min ? extra.min : definition.min;
				var max = extra.max ? extra.max : definition.max;
				var pattern = extra.pattern ? extra.pattern : definition.pattern;
				if ((min && String(value).length < min) || (max && String(value).length > max)) {
					extra.error = {code: 407};
					return false;
				} else if (pattern && !pattern.test(value)) {
					return false;
				} else {
					return true;
				}
			}};
			
			var Real = { isValid : function (value, definition, extra) {
				var pattern = extra.pattern ?  extra.pattern : definition.pattern;
				if (!(/^-?\d{1,10}(\.\d{1,7})?$/).test(value)) {
					return false;
				} else if (arguments.length>1 && Number(value) < definition.min) {
					return false;
				} else if (arguments.length>2 && Number(value) > definition.max) {
					return false;
				} else if (pattern && !pattern.test(value)) {
					return false;
				} else {
					return true;
				}
			}};
			
			/**
			 * data model definition of API_1484_11 (see 'SCORM Run-Time Environment Version 1.3 on www.adlnet.org)
			 * the definition is a nested ECMA object with each node having the follwing properties
			 * maxOccur, type, permission, children, min, max, pattern, default
			 */
			this.cmi = {maxOccur : 1, type : Object, permission: READWRITE,
				children : { 
					comments_from_learner : {maxOccur: 250, type: Array, permission: READWRITE, 
						children: {
							comment : {type: LocalizedString, max: 4000, permission: READWRITE},
							timestamp : {type: Time, permission: READWRITE},
							location : {type: CharacterString, max: 250, permission: READWRITE}
						}
					},
					comments_from_lms : {maxOccur: 250, type: Array, permission: READONLY, 
						children: {
							comment : {type: LocalizedString, max: 4000, permission: READONLY},
							timestamp: {type: Time, permission: READONLY},
							location : {type: CharacterString, max: 250, permission: READONLY}
						}
					},
					completion_status : {type: CompletionState, permission: READWRITE, 'default' : 'unknown'},
					completion_threshold : {type: Real, min: 0, max: 1, permission: READONLY},
					credit : {type: CreditState, permission: READONLY, 'default' : 'credit'},
					entry : {type: EntryState, permission: READONLY, 'default' : 'ab-initio'},
					exit : {type: ExitState, permission: WRITEONLY, 'default' : ''},
					interactions: {maxOccur: 250, type: Array, permission: READWRITE, unique: 'id', 
						children: {
							correct_responses: {maxOccur: 250, type: Array, permission: READWRITE, 
								children: {
									pattern : {type: PatternType, permission: READWRITE, dependsOn: '.id .type'}
								}
							},
							description:  {type: LocalizedString, max: 250, permission: READWRITE, dependsOn: 'id'},
							id: {type: Uri, max: 4000, permission: READWRITE, minOccur: 1},
							latency:  {type: Interval, permission: READWRITE, dependsOn: 'id'},
							learner_response:  {type: LearnerResponseType, permission: READWRITE, dependsOn: 'id type'},
							objectives: {maxOccur: 250, type: Array, permission: READWRITE, unique: 'id',
								children : { 
									id : {type: Uri, max: 4000, permission: READWRITE, dependsOn: 'interactions.id'}
								}
							},
							result:  {type: ResultState, permission: READWRITE, dependsOn: 'id'},
							timestamp: {type: Time, permission: READWRITE, dependsOn: 'id'},
							type: {type: InteractionType, permission: READWRITE},
							weighting:  {type: Real, permission: READWRITE, dependsOn: 'id'}
						}
					}, 
					launch_data : {type: CharacterString, max: 4000, permission: READONLY, 'default' : ''},
					learner_id : {type: CharacterString, max: 4000, permission: READONLY, 'default' : ''},
					learner_name : {type: LocalizedString, max: 250, permission: READONLY, 'default' : ''},
					learner_preference: {type: Object, permission: READONLY, 
						children : {
							audio_level: {type: Real, min: 0.000000001, permission: READWRITE, "default": '1'},
							language : {type: LangType, permission: READWRITE, 'default': ''}, 
							delivery_speed : {type: Real, min: 0, permission: READWRITE, 'default': '1'}, 
							audio_captioning : {type: AudioCaptioningState, permission: READWRITE, 'default': '0'}
						}				
					}, 
					location : {type: CharacterString, max: 1000, permission: READWRITE, 'default' : ''},
					max_time_allowed : {type: Interval, permission: READONLY},
					mode: {type: ModeState, permission: READONLY, 'default' : 'normal'},
					objectives: {maxOccur: 100, type: Array, permission: READWRITE, unique: 'id', 
						children: {
							completion_status: {type: CompletionState, permission: READWRITE, 'default': 'unknown', dependsOn: 'id'},
							description:  {type: LocalizedString, max: 250, permission: READWRITE, dependsOn: 'id'},
							id: {type: Uri, max: 4000, permission: READWRITE},
							progress_measure : {type: Real, min: 0, max: 1, permission: READWRITE},
							score: {type: Object, permission: READWRITE, 
								children: {
									scaled : {type: Real, min: -1, max: 1, permission: READWRITE, dependsOn: 'objectives.id'},
									raw : {type: Real, permission: READWRITE, dependsOn: 'objectives.id'},
									min : {type: Real, permission: READWRITE, dependsOn: 'objectives.id'},
									max : {type: Real, permission: READWRITE, dependsOn: 'objectives.id'}
								}
							},
							success_status: {type: SuccessState, permission: READWRITE, 'default': 'unknown', dependsOn: 'id'}
						}
					},
					progress_measure : {type: Real, min: 0, max: 1, permission: READWRITE},
					scaled_passing_score : {type: Real, min: -1, max: 1, permission: READONLY},
					score: {type: Object, permission: READWRITE, 
						children: {
							scaled : {type: Real, min: -1, max: 1, permission: READWRITE},
							raw : {type: Real, permission: READWRITE},
							min : {type: Real, permission: READWRITE},
							max : {type: Real, permission: READWRITE}
						}
					},
					session_time : {type: Interval, permission: WRITEONLY},
					success_status : {type: SuccessState, permission: READWRITE, 'default' : 'unknown'},
					suspend_data : {type: CharacterString, max: 64000, permission: READWRITE},
					time_limit_action : {type: TimeLimitAction, permission: READONLY, "default": "continue,no message"},
					total_time : {type: Interval, permission: READONLY, 'default' : 'PT0H0M0S'}
				}
			};
		}, // end cmi model
		
		'adl' : new function() { // implements ADL Extensions to API_1484_11
			// private constants: permission
			var READONLY  = 1;
			var WRITEONLY = 2;
			var READWRITE = 3;
		
			var NavRequest = { isValid : function (value, min, max, pattern) {
				return (/^\{target=[^\}]+\}choice|continue|previous|exit|exitAll|abandon|abandonAll|_none_$/).test(value);}
			};
			var NavState = { isValid : function (value, min, max, pattern) {
				return (/^true|false|unknown$/).test(value);}
			};
			var NavTarget = {
				isValid : function (value, min, max, pattern) {
					return (/^true|false|unknown$/).test(value);
				},
				getValue : function (param, def) {
					var m = param.match(/^\{target=([^\}]+)\}$/); 
					if (m && m[1]) {/* id identified, lookup in activity tree */}
					return def['default'];
				}
			};
			this.adl = {maxOccur : 1, type : Object, permission: READWRITE,
				children : {
					nav : {maxOccur : 1, type : Object, permission: READWRITE,
						children : { 
							request : {type: NavRequest, permission: READWRITE, 'default': '_none_'},
							request_valid : {type: Object, permission: READONLY,
								children : {
									'continue' : {type: NavState, permission: READONLY, 'default': 'unknown'},
									'previous' : {type: NavState, permission: READONLY, 'default': 'unknown'},
									// "adl.nav.request_valid.choice.{target=intro}"
									'choice' : {type: Function, permission: READONLY,
										children : {
											type: NavTarget, permission: READONLY, 'default': 'unknown'
										}
									}
								}
							}
						}
					}
				}
			};
		}
	},  // end adl model
	
	'onTerminate' : function (getValue, setValue, msec)
	{
		function msecToDuration(ms) 
		{
			var t, d = new Date(ms), r = ['P'];
			if ((t = d.getFullYear())) {r.push(t + 'Y');}
			if ((t = d.getMonth())) {r.push(t + 'M');}
			if ((t = d.getDate())) {r.push(t + 'D');}
			r.push('T');
			if ((t = d.getHours())) {r.push(t + 'H');}
			if ((t = d.getMinutes())) {r.push(t + 'M');}
			if ((t = d.getSeconds()*1000+d.getMilliseconds())) {r.push(t/1000 + 'S');}
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
				m[9] ? parseInt(m[9]) : 0);
			return d ? d.getTime() : 0;
		}
		var credit = getValue('cmi.credit', true);
		var entry = getValue('cmi.entry', true);
		var exit = getValue('cmi.exit', true);
		var lesson_mode = getValue('cmi.mode', true);
		var lesson_status = getValue('cmi.success_status', true);
		var session_time = durationToMsec(getValue('cmi.session_time', true));
		var total_time = durationToMsec(getValue('cmi.total_time', true));
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
