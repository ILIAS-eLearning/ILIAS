/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * This program is free software. The use and distribution terms forsetResource this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * You must not remove this notice, or any other, from this software.
 * 
 * PRELIMINARY EDITION
 * This is work in progress and therefore incomplete and buggy ...
 * 
 * Content-Type: application/x-javascript; charset=ISO-8859-1
 * Modul: SCORM2004 Player Core library 
 *  
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2007 Alfred Kohnert
 */ 



/* Time related Data Types */

function Duration (mixed) 
{
	this.value = new Date(typeof(mixed) === "number" ? mixed : Duration.parse(mixed || ""));
}

this.Duration = Duration;

Duration.prototype.set = function (obj)
{
	this.value.setTime(obj && obj.valueOf ? obj.valueOf() : obj); } ;

Duration.prototype.add = function (obj)
{
	var val = this.value.getTime() + obj && obj.valueOf ? obj.valueOf() : obj;
	this.value.setTime(val);
	return val;
};

Duration.parse = function (str) 
{
	var m = String(str).match(/^P(\d+Y)?(\d+M)?(\d+D)?(T(\d+H)?(\d+M)?((\d+)\.?(\d*)?S)?)?$/);
	if (!m) return null;
	return m[4]==="T" ? null : Date.UTC (
		(parseInt(m[1]) || 0)+1970,
		(parseInt(m[2]) || 0),
		(parseInt(m[3]) || 0)+1,
		parseInt(m[5]) || 0,
		parseInt(m[6]) || 0,
		parseInt(m[8]) || 0,
		parseInt(m[9]) || 0
	);
};

Duration.toString = function (d) 
{
	if (typeof d ==="number") d = new Date(d);
	var t, r = ['P'];
	if ((t = d.getUTCFullYear()-1970)) {r.push(t + 'Y');}
	if ((t = d.getUTCMonth())) {r.push(t + 'M');}
	if ((t = d.getUTCDate()-1)) {r.push(t + 'D');}
	r.push('T');
	if ((t = d.getUTCHours())) {r.push(t + 'H');}
	if ((t = d.getUTCMinutes())) {r.push(t + 'M');}
	if ((t = d.getUTCSeconds()+d.getUTCMilliseconds()/1000)) {r.push(t.toFixed(2)+ 'S');}
	return r.join("");
};

Duration.prototype.toString = function () 
{
	return Duration.toString(this.value);
}

Duration.prototype.valueOf = function () 
{
	return this.value.getTime();
};

function DateTime (mixed, utc) 
{
	this.value = new Date(typeof(mixed) === "number" ? mixed : DateTime.parse(mixed || "", utc));
}

this.DateTime = DateTime;

DateTime.parse = function (str, utc) 
{
	var m = String(str).match(/^\d{4}(-\d{2}(-\d{2}(T\d{2}(:\d{2}(:\d{2}(\.\d{1,2}([-+Z](\d{2}(:\d{2})?)?)?)?)?)?)?)?)?$/);
	if (!m) return null;
	var a = [
		m[0] ? Number(m[0].substr(0,4)) : 0, // yyyy
		m[1] ? Number(m[1].substr(1,2))-1 : 0, // mm
		m[2] ? Number(m[2].substr(1,2)) : 1, // dd
		m[3] ? Number(m[3].substr(1,2)) : 0, // hh
		m[4] ? Number(m[4].substr(1,2)) : 0, // mm
		m[5] ? Number(m[5].substr(1,2)) : 0, // ss
		m[6] ? Number(m[6].substr(1,2)) : 0, // ff
		m[7] ? m[7].substr(0,1) : utc ? 'Z' : '+', // z
		m[8] ? Number(m[8].substr(0,2)) : 0, // zhh
		m[9] ? Number(m[9].substr(1,2)) : 0 // zmm
	];
	var z = a[7]==='Z' ? (new Date()).getTimezoneOffset() : ((a[8] || 0)*60 + (a[9] || 0)) * (a[7]==='-' ? -1 : 1)
	var d = new Date(a[0], a[1], a[2], a[3], a[4], a[5], a[6]);	
	if (a[0]<1970 || a[0]>2038 || a[1]<0 || a[1]>12 || 
		d.getMonth()!==a[1] || d.getDate()!==a[2] || 
		a[3]>23 || a[4]>59 || a[5]>59 || 
		a[8]>23 || a[9]>59 || 
		m[7] && m[7]!=="Z" && !m[8]) return null;
	d.setTime(d.getTime()+z); 
	return d;
};

DateTime.toString = function (d, parts, prec) 
{
	function f(n) 
	{
		return (n<10 ? '0' : '') + n;
	}
	if (typeof d ==="number") d = new Date(d);
	var r = [
		d.getFullYear(),
		'-', f(d.getMonth()+1),
		'-', f(d.getDate()),
		'T', f(d.getHours()),
		':', f(d.getMinutes()),
		':', f(d.getSeconds()), 
		f((d.getMilliseconds()/1000).toFixed(prec || 3).substr(1))
	];
	return r.slice(0, 2*(parts || 7)-1).join("");
};

DateTime.prototype.toString = function (parts, prec) 
{
	return DateTime.toString(this.value, parts, prec);
};

DateTime.prototype.valueOf = function () 
{
	return this.value.getTime();
};



/* Core Data Objects for Activity Tree and Usertracking */


function Activity() 
{
	this.cmi_node_id = "$" + remoteInsertId++;
	this.hideLMSUIs = new Object();
	this.objectives = new Object();
	this.primaryObjective = new Object(); // reference to objective or self
	this.comments = new Object();
	this.interactions = new Object();
	this.rules = new Object();
}
this.Activity = Activity;
Activity.prototype = 
{
	dirty : 0,
	accesscount : 0,
	accessduration : 0,
	accessed : 0,
	activityAbsoluteDuration : 0,
	activityAbsoluteDurationLimit : 0,
	activityAttemptCount : 0,
	activityExperiencedDuration : 0,
	activityExperiencedDurationLimit : 0,
	activityProgressStatus : false,
	attemptAbsoluteDuration : 0,
	attemptAbsoluteDurationLimit : null,
	attemptCompletionAmount : 0,
	attemptCompletionStatus : false,
	attemptExperiencedDuration : 0,
	attemptExperiencedDurationLimit : 0,
	attemptLimit : 0,
	attemptProgressStatus : false,
	audio_captioning : 0,
	audio_level : 0,
	beginTimeLimit : 0,
	choice : true,
	choiceExit : true,
	completion : 0,
	completion_status : null,
	completionSetByContent : false,
	completionThreshold : null,
	constrainChoice : false,
	cp_node_id : null,
	created : 0,
	credit : 'credit',
	dataFromLMS : null,
	delivery_speed : 0,
	endTimeLimit : 0,
	exit : null,
	flow : false,
	foreignId: 0,
	forwardOnly : false,
	id : null, // item id not sequencing
	index: 0, // numerical index in activities
	isvisible : true,
	language : null,
	location : null,
	max : 0,
	measureSatisfactionIfActive : true,
	min : 0,
	modified : 0,
	objectiveMeasureWeight : 1.0,
	objectiveSetByContent : false,
	parameters : null,
	parent : null,
	preventActivation : false,
	progress_measure : null,
	randomizationTiming : 'never',
	raw : 0,
	reorderChildren : false,
	requiredForCompleted : 'always',
	requiredForIncomplete : 'always',
	requiredForNotSatisfied : 'always',
	requiredForSatisfied : 'always',
	resourceId : null,
	rollupObjectiveSatisfied : true,
	rollupProgressCompletion : true,
	scaled : 0,
	selectCount : 0,
	selectionTiming : 'never',
	session_time : 0,
	success_status : null,
	suspend_data : null,
	timeLimitAction : null,
	title : null,
	total_time : 'PT0H0M0S',
	tracked : true,
	useCurrentAttemptObjectiveInfo : true,
	useCurrentAttemptProgressInfo : true
};
 
function Interaction(cmi_node_id) 
{
	this.cmi_node_id = cmi_node_id;
	this.correct_responses = new Object();
	this.objectives = new Object();
}
this.Interaction = Interaction;
Interaction.prototype = 
{
	cmi_interaction_id : 0,
	description : null,
	id : null,
	latency : 0,
	learner_response : null,
	result : null,
	timestamp : null,
	type : null,
	weighting : 0
};

function Comment(cmi_node_id) 
{
	this.cmi_node_id = cmi_node_id;
}
this.Comment = Comment;
Comment.prototype = 
{
	cmi_comment_id : 0,
	comment : null,
	timestamp : null,
	location : null,
	sourceIsLMS : false
};

function CorrectResponse(cmi_interaction_id)  
{
	this.cmi_interaction_id = cmi_interaction_id;
}
this.CorrectResponse = CorrectResponse;
CorrectResponse.prototype = 
{
	cmi_correct_response_id : 0,
	pattern : null
};

function Objective(cmi_node_id, cmi_interaction_id)  
{
	this.cmi_interaction_id = cmi_interaction_id;
	this.cmi_node_id = cmi_node_id;
	this.mapinfos = new Object();
}
this.Objective = Objective;
Objective.prototype = 
{
	cmi_objective_id : 0,
	cp_node_id : 0,
	foreignId: 0,
	id : null,
	objectiveID : null,
	completion_status : 0,
	description : null,
	max : 0,
	min : 0,
	raw : 0,
	scaled : 0,
	progress_measure : null,
	success_status : null,
	scope : "local",
	minNormalizedMeasure : 1.0,
	primary : false,
	satisfiedByMeasure : false
};

function Mapinfo() {}
this.Mapinfo = Mapinfo;
Mapinfo.prototype = 
{
	cp_node_id : 0,
	foreignId: 0,
	readNormalizedMeasure : true,
	readSatisfiedStatus : true,
	targetObjectiveID : null,
	writeNormalizedMeasure : false,
	writeSatisfiedStatus : false
};

function Rule() 
{
	this.conditions = new Object();
}
this.Rule = Rule;
Rule.prototype = 
{
	action : null,
	childActivitySet : 'all',
	conditionCombination : null,
	cp_node_id : 0,
	foreignId: 0,
	minimumCount : 0,
	minimumPercent : 0,
	type : null
};

function Condition() {}
this.Condition = Condition;
Condition.prototype = 
{
	condition : null,
	cp_node_id : 0,
	foreignId: 0,
	measureThreshold : null,
	operator : 'noOp',
	referencedObjective : null
};

function HideLMSUI() {}
this.HideLMSUI = HideLMSUI;
HideLMSUI.prototype = 
{
	cp_node_id : 0,
	foreignId: 0,
	value : null
};


/* User Interface Objects (crossbrowser) */


function UIEvent (e, w) 
{
	if (!w) 
	{
		w = window;
	}
	this._ie = !e && w.event;
	this._event = e || w.event;
	this.keyCode = this._ie ? w.event.keyCode : this._event.which;
	this.shiftKey = this._ie ? w.event.shiftKey : this._event.shiftKey;
	this.ctrlKey = this._ie ? w.event.ctrlKey : this._event.ctrlKey;
	this.srcElement = e.target || w.event.srcElement;
	this.type = this._event.type;
}

this.UIEvent = UIEvent;

UIEvent.prototype.getIdElement = function () {
	return getAncestor(this.srcElement, 'id', true);
};	

UIEvent.prototype.getHrefElement = function () {
	return getAncestor(this.srcElement, 'href', true);
};	

UIEvent.prototype.stop = function () {
	var e = this._event;
	if (e.preventDefault) 
	{ 
		e.preventDefault(); 
		e.stopPropagation(); 
	} 
	else 
	{
		e.returnValue = false;
		e.cancelBubble = true;
	}
};


/* User Interface Methods (DOM, Events, CSS, crossbrowser) */


function attachUIEvent (obj, name, func) 
{
	if (window.Event) 
	{
		obj.addEventListener(name, func, false);
	} 
	else if (obj.attachEvent) 
	{
		obj.attachEvent('on'+name, func);
	} 
	else 
	{
		obj[name] = func;
	}
}
	
function detachUIEvent(obj, name, func) 
{
	if (window.Event) 
	{
		obj.removeEventListener(name, func, false);
	} 
	else if (obj.attachEvent) 
	{
		obj.detachEvent('on'+name, func);
	} 
	else 
	{
		obj[name] = '';
	}
}

	// CSS handling
function getCurrentStyle (elm, prop)
{
	var doc = elm.ownerDocument;
	if(elm.currentStyle) {
		return elm.currentStyle[prop];
	} else if (doc.defaultView && doc.defaultView.getComputedStyle) {
		return doc.defaultView.getComputedStyle(elm, '').getPropertyValue(fromCamelCase(prop));
	} else if (elm.style && elm.style[prop]) {
		return elm.style[prop];
	} else {
		return null;
	}
}

function getAncestor (elm, attr, pattern, includeSelf) 
{
	if (elm && elm.nodeType===1) 
	{
		return null;
	}
	if (!includeSelf) 
	{
		elm = elm.parentNode;
	}
	do {
		if (elm[attr]) 
		{
			if (!pattern || (pattern instanceof RegExp) ? 
				pattern.match(elm[attr]) : elm[attr]==pattern)
			{
				break;
			}
		}
		elm = elm.parentNode;
	} while (elm);
	return elm;
}

function getDesendents(elm, tagName, className, filter, depth) 
{
	function check(pattern, value) 
	{
		switch (typeof(pattern))
		{
			case 'string':
				return pattern.charAt()==="!" ^ pattern===value;
			case 'function':
				return pattern(value);
			case 'object':
				return pattern instanceof RegExp ? pattern.test(value) : pattern[value];
		}
	}
	if (elm && elm.nodeType===1) 
	{
		return null;
	}
	var children = elm.childNodes;
	var sink = [];
	for (var i=0, ni=children.length; i<ni; i++)
	{
		var child = children[i];
		if (child.nodeType!==1) {continue;}
		if (tagName && !check(tagName, child.tagName)) {continue;}
		if (className && !check(className, child.className)) {continue;}
		switch (typeof(filter))
		{
			case 'function':
				if (!filter(child)) {continue;}
				break;
			case 'object':
				for (var k in filter) {
					if (!check(filter[k], elm[k])) {continue;}
				}
				break;
		}
		sink.push(child);
		if (depth===undefined || depth) 
		{
			sink = sink.concat(getDesendents(child, tagName, className, filter, depth-1));
		}
	}
	return sink;
}

function all(id, win) 
{
	if (id && id.nodeType===1) {return id;} // already HTMLElement
	var doc  = (win ? win : window).document;
	var elm = doc.getElementById(id);
	return !elm ? null : elm.length ? elm.item(0) : elm;
}

function addClass (elm, name) 
{
	elm = all(elm);
	if (elm && !hasClass(elm, name)) 
	{
		elm.className = trim(elm.className + " " + name);
	}
}

function hasClass(elm, name) 
{
	elm = all(elm);
	return elm && (" " + elm.className + " ").indexOf(" " + name + " ")>-1;
}

function removeClass(elm, name) 
{
	elm = all(elm);
	if (elm) 
	{
		elm.className = trim((" " + elm.className + " ").replace(name, " "));
	}
}

function replaceClass(elm, oldname, newname) 
{
	elm = all(elm);
	removeClass(elm, oldname);
	addClass(elm, newname);
}

function toggleClass(elm, name, state) 
{
	elm = all(elm);
	if (state===undefined) {
		state = !hasClass(elm, name);
	}
	if (!state) {
		removeClass(elm, name);
	}
	else {
		addClass(elm, name);
	}
}

function getOuterHTML(elm)
{
	return elm.outerHTML!==undefined ? elm.outerHTML : elm.outerHTML;
}

function setOuterHTML(elm, markup)
{
	if (elm.outerHTML!==undefined) 
	{
		elm.outerHTML = markup;
	}
	else
	{
		var range = elm.ownerDocument.createRange();
		range.setStartBefore(elm);
		var fragment = range.createContextualFragment(markup);
		elm.parentNode.replaceChild(fragment, elm);
	}
}


/* Date Methods (server time sensible) */

function currentTime() 
{
	var d = new Date();
	return d.getTime() + (Date.remoteOffset || 0);
}


/* String functions */

function trim (str, norm) 
{
	var r = String(str).replace(/^\s+|\s+$/g, '');
	return norm ? r.replace(/\s+/g, ' ') : r;
}

function repeat(obj, times) 
{
	return (new Array(times+1)).join(obj);
}

// converts string input "toCamelCase" to string output "to-camel-case"
function fromCamelCase(s) 
{
	return s.charAt(0) + s.substring(1).replace(/([A-Z])/g, function(match) {return '-' + match.toLowerCase();});
}

// converts string input "to-camel-case" to string output "toCamelCase"
function toCamelCase(s) 
{
	return s.replace(/(\-\w)/g, function(match) {return match.substring(1).toUpperCase();});
}


/* Number functions */

function numberFormat(num, dec, len) 
{
	var s = num.toFixed(dec);
	if (len && s.length<len) 
	{
		while (s.length<len) 
		{
			s = '0' + s;
		}
	}
	return s;
}



/* Object functions */

function copyOf(obj, ref) 
{
	switch (typeof obj) {
		case 'object':
			var r = new obj.constructor();
			if (obj instanceof Array) // instanceof requires MSIE5+ 
			{ 
				for (var i=0, ni=obj.length; i<ni; i+=1) 
				{
					r[i] = copyOf(obj[i], ref);
				}
			} 
			else 
			{
				for (var k in obj) 
				{
					if (obj.hasOwnProperty(k)) // hasOwnProperty requires Safari 1.2
					{  
						r[k] = copyOf(obj[k], ref);
					}
				}
			}
			return r;
		case 'function':
		case 'unknown':
			// should not be copied but referenced if ref flag is set
			return ref ? obj : undefined;
		default: 
			return obj;
	}
}


/* JSON and XMLHHTP functions */

function createHttpRequest() 
{
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

function sendAndLoad(url, data, callback, user, password, headers) 
{
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
		xhttp.send(data ? String(data) : '');				
	} else 
	{
		xhttp.send(data ? String(data) : '');				
		return onStateChange();
	}
}

function sendJSONRequest (url, data, callback, user, password, headers) 
{		
	if (typeof headers !== "object") {headers = {};}
	headers['Accept'] = 'text/javascript';
	headers['Accept-Charset'] = 'UTF-8';
	var r = sendAndLoad(url, toJSONString(data), callback, user, password, headers);
	if ((r.status===200 && (/^text\/javascript;?.*/i).test(r.type)) || r.status===0)
	{
		return parseJSONString(r.content);
	}
	else
	{
		return null;
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

function parseJSONString (s) 
{
	var re = /^("(\\.|[^"\\\n\r])*?"|[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t])+?$/;
	try 
	{
		if (re.test(s)) 
		{
			return window.eval('(' + s + ')');
		} 
	} catch (e) {}
	throw new SyntaxError('parseJSONString: ' + s.substr(0, 200));
}


/* localization methods */

function setLocalStrings(obj)
{
	extend(translate, obj);
}
	
function translate(key, params) 
{
	var value = key in translate ? translate[key] : key;
	if (typeof params === 'object') 
	{
		value = String(value).replace(/\{(\w+)\}/g, function (m) {
			return m in params ? params[m] : m;
		});
	} 
	return value; 
}
	

/* array helpers */

function keys(obj) 
{
	var r = [];
	for (var k in obj) 
	{
		r.push(k);
	}
	return r;
}

function values(obj, attr) 
{
	var r = [];
	for (var k in obj) 
	{
		r.push(attr ? obj[k][attr] : obj[k]);
	}
	return r;
}

function walkItems (root, name, func, sink, depth) 
{
	var data, subdata;
	var items = root[name];
	var arraySink = sink && sink instanceof Array;
	if (depth===undefined) 
	{
		depth = 0;
	}
	for (var k in items) 
	{
		var item = items[k];
		if (!arraySink) 
		{
			func(item, sink, depth);
		}
		if (item && item[name]) 
		{
			subdata = walkItems(item, name, func, arraySink ? [] : sink, depth+1);
		}
		if (arraySink) 
		{
			data = func(item, subdata, depth);
			if (data!==undefined && subdata!==undefined) 
			{
				data[name] = subdata;
			}
			sink.push(data);
		}
	}
	return sink;
}


/* class/prototype helpers */

function inherits (subClass, baseClass) 
{
   function inheritance() {}
   inheritance.prototype = baseClass.prototype;
   subClass.prototype = new inheritance();
   subClass.prototype.constructor = subClass;
   subClass.baseConstructor = baseClass;
   subClass.superClass = baseClass.prototype;
}
	 	
function extend(destination, source, nochain, nooverwrite) {
	for (var property in source) 
	{
		if (nochain && source.hasOwnProperty(property)) {continue;}
		if (nooverwrite && destination.hasOwnProperty(property)) {continue;}
		var value = source[property];
		destination[property] = value;
	}
	return destination;
}



/* ############### GUI ############################################ */


function onDocumentClick (e) 
{
	e = new UIEvent(e);
	var target = e.srcElement;
	if (target.tagName !== 'A' || !target.id) 
	{
		// ignore clicks on other elements than A
		// or non identified elements or disabled elements
	} 
	else if (target.id.substr(0, 3)==='nav') 
	{
		window.top.status = execNavigation(target.id.substr(3), target.id);
	} 
	else if (target.id.substr(0, 3)===ITEM_PREFIX) 
	{
		if (e.altKey) {} // for special commands
		else 
		{
			onChoice(target);	
		}
	}
	else if (typeof window[target.id + '_onclick'] === "function")
	{
		window[target.id + '_onclick'](target);
	} 
	else if (target.target==="_blank")
	{
		return;
	} 
	e.stop();
}

function setState(newState)
{
	replaceClass(document.body, guiState + 'State', newState + 'State');
	guiState = newState;
}

function setInfo (name, values) 
{
	var elm = all('infoLabel');
	var txt = translate(name, values);
	if (elm) 
	{
		window.top.document.title = elm.innerHTML = txt;
	}
} 

function setToc(tocData) 
{
	for (var k in guiViews) 
	{
		guiViews[k].create(tocData);
	}
	document.title = tocData.title;
}

function updateToc(tocState) 
{
	for (var k in guiViews) 
	{
		guiViews[k].update(tocState);
	}
}

function updateControls(controlState) 
{
	for (var k in controlState) 
	{
		toggleClass('nav'+k.charAt().toUpperCase()+k.substr(1), 'disabled', !controlState[k]);
	}
}

function setResource(id, url, base) 
{
	if (url.indexOf(":")===-1) 
	{
		url = base + url;
	}
	
	if (!top.frames[RESOURCE_NAME])
	{
		var elm = window.document.getElementById(RESOURCE_PARENT);
		if (!elm) 
		{
			return window.alert("Window Container not found");
		}
		var h = elm.clientHeight-20;
		if (self.innerHeight && navigator.userAgent.indexOf("Safari") != -1) // needed for Webkit based browsers
		{
			h = self.innerHeight-60;
		} 
		elm.innerHTML = '<iframe frameborder="0" name="' + RESOURCE_NAME + '" src="' + url +	'"  style="width: 100%; height:' + h + 'px" height="' + h + '"></iframe>';	
	} 
	else 
	{
		open(url, RESOURCE_NAME);
	} 
	
	if (guiItem) 
	{
		removeClass(guiItem, "current");
	}
	guiItem = all(ITEM_PREFIX + id);
	if (guiItem)
	{
		addClass(guiItem, "current");
	}
}

function removeResource(callback) 
{
	if (guiItem) 
	{
		removeClass(guiItem, "current");
	}
	/*
	var elm = all(RESOURCE_PARENT);
	if (elm)
	{
		elm.innerHTML = '<div>'+translate('resource_undelivered')+'</div>';
	}
	*/
	open('about:blank', RESOURCE_NAME);
	if (typeof(callback) === 'function') 
	{
		callback();
	}  
}

function onWindowResize() 
{
	var hd = document.documentElement.clientHeight;
	var hb = document.body.clientHeight;
	if (self.innerHeight && navigator.userAgent.indexOf("Safari") != -1) // needed for Webkit based browsers

	{
			hd = self.innerHeight;
	} 
	var tot = hd ? hd : hb;
	var elm = all(RESOURCE_TOP);
	var h = (tot-elm.offsetTop-4) + 'px';
	elm = all("treeView");
	if (elm) 
	{
		elm.style.height = h;
	}
	elm = all(RESOURCE_NAME);
	if (elm) 
	{
		elm.style.height = h;
	}
}

function onChoice(target) 
{
	execNavigation('Choice', target.id.substr(3));
}



/* ######### SEQUENCER GENERAL ################################################## */


function getState() 
{
	return state;
}

function getControlState() 
{
	var r = {};
	r.start = !currentAct;
	r.resumeAll = !currentAct && suspendedAct;
	r.exit = !!currentAct;
	r.exitAll = !!currentAct;
	r.abandon = !!currentAct;	
	r.abandonAll = !!currentAct;
	r.suspendAll = !!currentAct;
	r.previous = currentAct && currentAct.parent.flow &&
			!currentAct.parent.forwardOnly &&
			'TODO_PREVIOUS_NOT_RESULTING_IN_WALKING_OFF_TREE';
	r['continue'] = currentAct && currentAct.parent.flow;
			var hideLMSUI = {'previous': 0, 'continue': 0, 'exit': 0, 'exitAll': 0, 
		'abandon': 0, 'abandonAll': 0, 'suspendAll': 0};
	if (currentAct && currentAct.hideLMSUI) 
	{
		for (var i=0, ni=currentAct.hideLMSUI.length; i<ni; i++) 
		{
			r[currentAct.hideLMSUI[i].value] = false;
		}
	}
	return r;
}

function getTocData() 
{
	function func(v) {
		return {
			id : v.id, 
			href : v.href,
			isvisible : v.isvisible, 
			title : v.title 
		};
	}
	var r = func(rootAct);
	r.item = walkItems(rootAct, "item", func, []);
	return r;
}

function getTocState() 
{
	function func(v) 
	{
		var disabled = false;
		var hidden = 0; // tristate 0=visible, 1=hidden by cam, 2=hidden by sequencer
		var parseq = v.parent ? v.parent : null;
		if (!parseq) 
		{
			// nothing to do, beyond root activity 
		}
		else if (!parseq.choice || checkActivity(v)) 
		{
			disabled = true;
		} 
		else if (currentAct && v.index<currentAct.index && parseq.forwardOnly)  
		{
			return r;
		} 
		else if (currentAct && v.index>currentAct.index && parseq.stopForward)  
		{
			return r;
		} 
		// TODO check case 117.6 c and e
		if (!v.isvisible)  
		{
			hidden = 1;
		} 
		else if (v.hiddenFromChoice)  
		{
			hidden = 2;
		} 
		else if (currentAct && !currentAct.choiceExit && !'TODO IS CHILD OF CURRENT')  
		{
			hidden = 2;
		} 
		else if (!parseq) 
		{
		} 
		else if (parseq.preventActivation && !parseq.isActive)  
		{
			hidden = 2;
		} 
		else if (parseq.constrainChoice && 'TODO v is neither previous nor next to current')  
		{
			hidden = 2;
		} 
		return {id:v.id, disabled:disabled, hidden:hidden};
	}
	var r = func(rootAct);
	r.item = walkItems(rootAct, "item", func, []);
	return r;
}

function abortNavigation () 
{
	state = ABORTING; 
}

function execNavigation (type, target) 
{
	if (state) 
	{
		return false; // already processing
	}
	state = RUNNING;
	var res = exec({type:type, target:target});
	state = WAITING;
	if (res && res.exception) 
	{
		window.alert(translate(res.exception));
	}
	return !res || !res.exception;
}

function queryNavigation (type, target) 
{
	if (state) 
	{
		return false; // already processing
	}
	state = QUERYING;
	var res = exec({type:type, target:target});
	state = WAITING;
	return !res.exception;
}


/* ########### PLAYER loading and TRACKER ####################################### */

function init(config) 
{
	function camWalk(cam, act) 
	{
		function move(act, prop, newprop, id) 
		{
			var k;
			var cls = this[prop.charAt().toUpperCase()+prop.substr(1)] || Object;
			if (!act[prop]) {return;}
			while ((k = act[prop].pop()))
			{
				var subact = new cls();
				act[newprop][k[id] ? k[id] : '$'] = subact;
				for (var kk in k) 
				{
					//subact[kk] = k[kk];
					setItemValue(kk, subact, k);
				}
			}
			delete act[prop];
		}
		
		var k, i, ni, seq, v;
		seq = cam.sequencingId in seqs ? seqs[cam.sequencingId] : {};
		for (k in seq) 
		{
			setItemValue(k, act, seq);
		}
		for (k in cam) 
		{
			setItemValue(k, act, cam);
		}
		act.index = activitiesByNo.length;
		activitiesByNo.push(act);
		act.cp_node_id = act.foreignId;
		activitiesByCAM[act.foreignId] = act;
		activities[act.id] = act;
		if (cam.item) 
		{
			act.item = new Array();
			var availableChildren = [];
			for (i=0, ni=cam.item.length; i<ni; i+=1) 
			{
				var subact = new Activity();
				subact.parent = act; 
				camWalk(cam.item[i], subact);
				availableChildren.push(subact);
				act.item.push(subact);
			}
			act.availableChildren = availableChildren;
		}
		move(act, "objective", "objectives", "objectiveID");
		move(act, "hideLMSUI", "hideLMSUIs", "value");
		move(act, "rule", "rules", "foreignId");
		act.primaryObjective = act;
		for (k in act.objectives) 
		{
			move(act.objectives[k], "mapinfo", "mapinfos", "targetObjectiveID");
			for (var l in act.objectives[k].mapinfos) 
			{
				var dat = sharedObjectives[l];
				if (!dat) 
				{
					dat = new Objective();
					dat.id = l;
					dat.cmi_node_id = globalAct.cmi_node_id;
					sharedObjectives[l] = dat;
				}
			}
			// if we find a primaryObjective overwrite reference to activity as data container
			if (act.objectives[k].primary) 
			{
				act.primaryObjective = act.objectives[k];
			}
		}
	}

	this.config = config;
	
	setInfo('loading');
	setState('loading');
	
	setLocalStrings( // define these strings in host localization table
	{
		'resource_undelivered' : 'Resource unloaded. Use navigation to load a new one.'
	});
	setLocalStrings(this.config.langstrings);
	setTimeout(onWindowLoad, 0);
		
	// Step 1: load manifest data
	
	var cam = this.config.cp_data || sendJSONRequest(this.config.cp_url);

	if (!cam) return alert('Fatal: Could not load content data.')
	
	// convert seq array into seq map and decode seq data en passant
	var seqs = cam.sequencing ? cam.sequencing  : [];
	for (var i=seqs.length; i--;)
	{
		seq = seqs.pop();
		seqs[seq.id] = seq;
		delete seq.foreignId;
	}

	// resolve one step inheritance in sequencing
	for (var k in seqs) 
	{
		seq = seqs[k];
		if (seq.sequencingId) 
		{
			var baseseq = seqs[seq.sequencingId];
			for (k in baseseq) 
			{
				if (seq[k]===undefined) 
				{
					seq[k] = baseseq[k];
				}
			}
			delete seq.id;
			delete seq.sequencingId;
		}
	}

	// copy data from manifest into globalAct
	for (k in cam) 
	{
		if (typeof cam[k] !== "object") 
		{
			globalAct[k] = cam[k];
		}
	}
	
	// identifiy cp_node for saving global activity into cmi_node
	// and add global activity to list of activities
	globalAct.cp_node_id = globalAct.foreignId;
	globalAct.index = activitiesByNo.length;
	activitiesByNo.push(globalAct);
	activitiesByCAM[globalAct.foreignId] = globalAct;
	activities[globalAct.id] = globalAct;
	
	//set data from LMS
	globalAct.learner_id=this.config.learner_id;
	globalAct.learner_name=this.config.learner_name;
	
	// walk throught activities and add some helpful properties
	camWalk(cam.item, rootAct);
		
	// Step 2: load tracking data
	load();
		
}

function load()
{
	// optionally add parameters for loading level 1 or level 2 data only
	
	var cmi = this.config.cmi_data || sendJSONRequest(this.config.cmi_url);
	
	if (!cmi) return alert('FATAL: Could not load userdata!');
	
	var k, i, ni, row, act, j, nj, dat, id;
	var cmi_node_id, cmi_interaction_id;
	
	if (!remoteMapping) 
	{
		remoteMapping = cmi.schema;
		for (k in remoteMapping) 
		{
			for (i=remoteMapping[k].length; i--; )
			{
				remoteMapping[k][remoteMapping[k][i]] = i;
			}
		}
		while ((row = cmi.data['package'].pop()))
		{
			for (i=remoteMapping['package'].length; i--; )
			{
				globalAct[remoteMapping['package'][i]] = row[i];
			}
			globalAct.learner_id = globalAct.user_id;
		}
	}
	
	for (i=cmi.data.node.length; i--; )
	{
		row = cmi.data.node[i];
		act = activitiesByCAM[row[remoteMapping.node.cp_node_id]];
		for (j=remoteMapping.node.length; j--; ) 
		{
			if (row[j]===null) {continue;}
			//act[remoteMapping.node[j]] = row[j];
			setItemValue(j, act, row, remoteMapping.node[j]);
		}
		activitiesByCMI[act.cmi_node_id] = act;
	}
	for (i=cmi.data.comment.length; i--; )
	{
		row = cmi.data.comment[i];
		dat = new Comment();
		for (j=remoteMapping.comment.length; j--; ) 
		{
			//dat[remoteMapping.comment[j]] = row[j];
			setItemValue(j, dat, row, remoteMapping.comment[j]);
		}
		act = activitiesByCMI[row[remoteMapping.comment.cmi_node_id]];
		act.comments[dat.cmi_comment_id] = dat;
	}
	var interactions = {};
	for (i=cmi.data.interaction.length; i--; )
	{
		row = cmi.data.interaction[i];
		dat = new Interaction();
		for (j=remoteMapping.interaction.length; j--; ) 
		{
			//dat[remoteMapping.interaction[j]] = row[j];
			setItemValue(j, dat, row, remoteMapping.interaction[j]);
		}
		act = activitiesByCMI[row[remoteMapping.comment.cmi_node_id]];
		act.interactions[dat.id] = dat;
		interactions[dat.cmi_interaction_id] = dat;
	}
	for (i=cmi.data.correct_response.length; i--; )
	{
		row = cmi.data.correct_response[i];
		dat = new CorrectResponse();
		for (j=remoteMapping.correct_response.length; j--; ) 
		{
			//dat[remoteMapping.correct_response[j]] = row[j];
			setItemValue(j, dat, row, remoteMapping.correct_response[j]);
		}
		act = interactions[row[remoteMapping.correct_response.cmi_interaction_id]];
		act.correct_response[dat.cmi_correct_response_id] = dat;
	}
	for (i=cmi.data.objective.length; i--; )
	{
		row = cmi.data.objective[i];
		id = row[remoteMapping.objective.id];
		cmi_interaction_id = row[remoteMapping.objective.cmi_interaction_id];
		cmi_node_id = row[remoteMapping.objective.cmi_node_id];
		if (cmi_interaction_id===null) // objective to an activity or shared
		{
			act = activitiesByCMI[cmi_node_id];
			if (act && act.objectives[id]) // local objective specified in manifest
			{
				dat = act.objectives[id];
			}
			else if (act) // local objective of private use in sco
			{
				dat = new Objective();
				 act.objectives[id] = dat;
			}
			else if (sharedObjectives[id]) // shared objective
			{
				dat = sharedObjectives[id];
			}
			// copy data into internal structure
			for (j=remoteMapping.objective.length; j--; ) 
			{
				dat[remoteMapping.objective[j]] = row[j];
			}
		}
		else // objective id to an interaction
		{
			interactions[cmi_interaction_id].objectives[id] = {id:id};
		}
		dat = new Objective();
		for (j=remoteMapping.objective.length; j--; ) 
		{
			//dat[remoteMapping.objective[j]] = row[j];
			setItemValue(j, dat, row, remoteMapping.objective[j]);
		}
		act = activitiesByCMI[row[remoteMapping.objective.cmi_node_id]];
		act.objectives[dat.id] = dat;
	}
}

function save()
{
	// optionally add parameters for save level 1 or level 2 data only
	
	function walk(collection, type) 
	{
		var schem = remoteMapping[type];
		var res = result[type];
		for (var k in collection) 
		{
			var item = collection[k];
			if (item.dirty===0) {continue;}
			var data = [];
			for (var i=0, ni=schem.length; i<ni; i++) 
			{
				data.push(item[schem[i]]);
			}
			res.push(data);
			switch (k)
			{
				case 'node':
					walk(item.objectives, "objective");
					if (item.dirty!==2) {continue;}
					walk(item.comments, "comment");
					walk(item.interactions, "interaction");
					walk(item.correct_responses, "correct_response");
					break;
				case 'interaction':
					walk(item.correct_responses, "correct_response");
					walk(item.objectives, "objective");
					break;
			}
		}
	}
	
	//alert("Before save");
		
	if (save.timeout) 
	{
		window.clearTimeout(save.timeout);
	}
	var result = {};
	for (var k in remoteMapping) 
	{
		result[k] = [];
	}
	// add shared objectives
	walk (sharedObjectives, "objective");
	// add activities
	walk (activities, 'node');
	
	//alert("Before save "+result.node.length);
	if (!result.node.length) {return;} 
	
	result = this.config.cmi_url 
		? sendJSONRequest(this.config.cmi_url, result)
		: {};
	
	// set successful updated elements to clean
	var i = 0;
	for (k in result) 
	{
		i++;
		var act = activitiesByCAM[k];
		if (act) 
		{
			act.dirty = 0;
		}
	}
	
	//alert("Saved: "+i);
	
	if (typeof this.config.time === "number" && this.config.time>10) 
	{
		clearTimeout(save.timeout);
		save.timeout = window.setTimeout(save, this.config.time*1000);
	}
	return i;
}


function getAPI(cp_node_id) 
{
	function getAPISet (k, dat, api) 
	{
		if (dat!=undefined && dat!==null) 
		{
			api[k] = dat.toString();
		}
	}
	
	function getAPIWalk (model, data, api) 
	{
		var k, i;
		if (!model.children) return;
		for (k in model.children) 
		{
			var mod = model.children[k];
			var dat = data[k];
			if (mod.type===Object) 
			{
				api[k] = {};
				for (var i=mod.mapping.length; i--;)
				{
					getAPISet(mod.mapping[i], dat, api[k]);
				}
			}
			else if (mod.type===Array) 
			{
				api[k] = [];
				if (mod.mapping) 
				{
					dat = data[mod.mapping.name];
				}
				for (i in dat) 
				{
					if (mod.mapping && !mod.mapping.func(dat[i])) continue;
					var d = getAPIWalk(mod, dat[i], {});
					var idname = 'cmi_'+ k.substr(0, k.length-1) + '_id';
					d[idname] = dat[idname];
					api[k].push(d);					
				}
			}
			else 
			{
				getAPISet(k, dat, api);
			}
		}
		return api;
	}
	
	// create api data element with some starting values
	var api = {cmi:{}, adl:{}};
	
	// reference to live data
	var data = activitiesByCAM[cp_node_id];

	// start recursive process to add current cmi subelements
	getAPIWalk(Runtime.models.cmi.cmi, data, api.cmi);

	return api;
	
}

function setItemValue (key, dest, source, destkey) 
{
	if (source && source.hasOwnProperty(key)) 
	{
		var d = source[key];
		if (!isNaN(parseFloat(d))) {
			d = Number(d);
		} else if (d==="true") {
			d = true;
		} else if (d==="false") {
			d = false;
		}
		dest[destkey ? destkey : key] = d;
	}
}

function setAPI(cp_node_id, api) 
{

	function setAPIWalk (model, data, api) 
	{
		var k, i;
		if (!model.children) return;
		for (k in model.children) 
		{
			var mod = model.children[k];
			var ap = api[k];
			if (mod.type===Object) // activity.SCORE.max
			{
				for (var i=mod.mapping.length; i--;)
				{
					setItemValue(mod.mapping[i], data, ap);
				}
			}
			else if (mod.type===Array) 
			{
				var map = mod.mapping || {name : k.substr(0, k.length-1)};
				map.dbtable = map.name + "s"; 
				map.dbname = 'cmi_'+ map.name + '_id';
				map.clsname = map.name.charAt().toUpperCase() + map.name.substr(1);
				for (i in ap) 
				{
					var dat = data[map.dbtable];
					var row = ap[i];
					if (map.refunc)  
					{
						var remap = map.refunc(dat[i]);
						row[remap[0]] = remap[1];
					}
					if (!row[map.dbname]) row[map.dbname] = '$'+(remoteInsertId++);
					var id = row[mod.unique] || row[map.dbname];
					var cls = this[map.clsname] || Object;
					if (!dat[id]) 
					{
						dat[id] = new cls;
					}
					setAPIWalk(mod, dat[id], row);
				}
			}
			else 
			{
				setItemValue(k, data, api);
			}
		}
	}
		
	// reference to live data
	var data = activitiesByCAM[cp_node_id];

	// start recursive process to add current cmi subelements
	setAPIWalk(Runtime.models.cmi.cmi, data, api.cmi);
	
	data.dirty = 2;
	
	return true;
	
}


function dirtyCount() 
{
	var c = 0;
	for (var i=activities.length; i--; ) 
	{
		c += Number(activities[i].dirty); 
	}
	return c;
}


/* ########### PLAYER events ####################################### */

function onWindowLoad () 
{ 
	// Hook core events
	attachUIEvent(window, 'unload', onWindowUnload);
	attachUIEvent(document, 'click', onDocumentClick);
	
	// Show Tree and Controls
	setToc(getTocData(), this.config.package_url);
	updateToc(getTocState());
	updateControls(getControlState());
	
	// Finishing startup
	setInfo('');
	setState('playing');
	attachUIEvent(window, 'resize', onWindowResize);
	onWindowResize();
}

function onWindowUnload () 
{
	save();
}

function onItemDeliver(item) // onDeliver called from sequencing process (deliverSubProcess)
{
	var url = item.href, v;
	var tocState = getTocState();
	var controlState = getControlState();
	// create api if associated resouce is of adl:scormType=sco
	if (item.sco)
	{
		// get data in cmi-1.3 format
		var data = getAPI(item.foreignId);
		// add ADL Request namespace data
		data.adl = {nav : {request_valid: {}}};
		for (var k in controlState) 
		{
			data.adl.nav.request_valid[k.toLowerCase()] = String(controlState[k]);
		}
		// TODO walk tocState items for adl.nav.request_valid.{target=ID} = ...

		// add some global values for all sco's in package
		data.cmi.learner_name = globalAct.learner_name;
		data.cmi.learner_id = globalAct.learner_id;
		data.cmi.cp_node_id = item.foreignId;
		data.cmi.session_time = undefined;
		data.cmi.completion_threshold = item.completionThreshold;
		data.cmi.launch_data = item.dataFromLMS;
		data.cmi.time_limit_action = item.timeLimitAction;
		data.cmi.max_time_allowed = item.attemptAbsoluteDurationLimit;
	//	alert("Set: "+globalAct.user_id + globalAct.learner_name)
	alert("Item"+item.id);
		if (item.objective && (v = item.objective[0])) 
		{
			// REQ_74.3, compute scaled passing score from measure
			if (v.satisfiedByMeasure && v.minNormalizedMeasure!==undefined) 
			{
				v = v.minNormalizedMeasure;
			}
			else if (v.satisfiedByMeasure) 
			{
				v = 1.0;
			}
			else 
			{
				v = undefined;
			}
			data.cmi.scaled_passing_score = String(v);
		}
		// assign api for public use from sco
		
		//alert(toJSONString(data.cmi, ' '))
		
		currentAPI = window[Runtime.apiname] = new Runtime(data, onCommit, onTerminate);
	}
	// deliver resource (sco)
	scoStartTime = currentTime();
	setResource(item.id, item.href+"?"+item.parameters, this.config.package_url);
	// customize GUI
	updateToc(tocState);
	updateControls(controlState);
}

function onItemUndeliver(item) // onUndeliver called from sequencing process (EndAttempt)
{
	// throw away the resource
	// it may change api data in this
	removeResource();

	// throw away api, and try a API.Terminate before (will return "false" if already closed by SCO)
	if (currentAPI) 
	{
		currentAPI.Terminate("");
	}
	currentAPI = window[Runtime.apiname] = null;
	
	// set some data values 
	if (currentAct) 
	{
		currentAct.accessed = currentTime()/1000;
		if (!currentAct.dirty) currentAct.dirty = 1;
	}
	
	// customize GUI
	updateToc(getTocState());
	updateControls(getControlState());
}

// sequencer terminated
function onNavigationEnd()
{
	removeResource();
}

function onCommit(data) 
{
	//alert("oncommit");
	return setAPI(data.cmi.cp_node_id, data);
}

function onTerminate(data) 
{
	//alert("onTerminate " + data.cmi.exit);
	var navReq;
	switch (data.cmi.exit)
	{
		case "suspend":
			navReq = {type: "suspend"};
			break;
		case "logout":
		case "time-out":
			navReq = {type: "exitAll"};
			// abort ongoing navigation
			abortNavigation();
			break;
		default : // "", "normal"
			if (data.adl && data.adl.nav) 
			{
				var m = String(data.adl.nav.request).match(/^(\{target=([^\}]+)\})?(choice|continue|previous|suspendAll|exit(All)?|abandon(All)?)$/);
				if (m) 
				{
					navReq = {type: m[3].substr(0, 1).toUpperCase() + m[3].substr(1), target: m[2]};
				}
			}
			break;
	}
	if (navReq) 
	{
		// will only work if no navigation is ongoing 
		// so we delay to next script cycle
		// and use closure to retain current variable scope
		//alert('ADLNAV => '+[navReq.type, navReq.target])
		window.setTimeout( 
			new (function (type, target) {
				return function () {
					execNavigation(type, target);
				}
			})(navReq.type, navReq.target), 0);
	}
	return true;
}


/* ############# GLOBAL CONSTANTS AND VARIABLES ################################ */


var apiIndents = // for mapping internal to api representaiton
{
	'cmi' : 
	{
		'score' : ['raw', 'min', 'max', 'scaled'],
		'learner_preference' : ['audio_captioning', 'audio_level', 'delivery_speed', 'language']
	},
	'objective' : 
	{
		'score' : ['raw', 'min', 'max', 'scaled']
	}
};	


var guiViews = // for different table of content views in gui
{
	treeView : 
	{
		create : function (tocData) 
		{
			function func(item, sink, depth) 
			{
				var elm = sink[depth].appendChild(sink[depth].ownerDocument.createElement('A'));
				elm.id = ITEM_PREFIX + item.id;
				elm.className = (item.href ? 'content' : 'block') + (item.isvisible ? '' : ' invisible');
				elm.href = "#";
				elm.innerHTML = item.title;
				if (item.item) 
				{
					sink[depth+1] = sink[depth].appendChild(elm.ownerDocument.createElement('DIV'));
				}
			}
			var tocView = all('treeView');
			tocView.innerHTML = '';
			func(tocData, {0: tocView}, 0);
			walkItems(tocData, "item", func, {1: tocView.lastChild}, 1);
		},
		update : function (tocState) 
		{
			function walk(items) 
			{
				for (var i=0, ni=items.length; i<ni; i+=1) 
				{
					var item = items[i];
					var elm = all(ITEM_PREFIX + item.id);
					if (elm) 
					{
						toggleClass(elm, 'disabled', item.disabled); 
						toggleClass(elm.parentNode, 'hidden', item.hidden); 
					} 
					if (item.item) 
					{
						walk(item.item); // RECURSION
					}
				}
			}
			walk([tocState]);
		}
	}
};


// Server related Variables
var remoteMapping = null; // mapping of userdata from client to server representation
var remoteInsertId = 0; // pseudo IDs for newly generated data rows (will be prefixed by "$")

var globalAct = new Activity(); // pseudo activity utilizing manifest/package wide data
var rootAct = new Activity(); // organization node, root of activity tree
var activities = new Object(); // activities by item string identifier 
var activitiesByCAM = new Object(); // activities by cp_node_id
var activitiesByCMI = new Object(); // activities by cmi_node_id
var activitiesByNo = new Array(); // activities by numerical index
var sharedObjectives = new Object(); // global objectives by objective identifier

// GUI constants
var ITEM_PREFIX = "itm";
var RESOURCE_PARENT = "tdResource";
var RESOURCE_NAME = "frmResource";
var RESOURCE_TOP = "mainTable";

// GUI Variables 
var guiItem;
var guiState; // loading, playing, paused, stopped, buffering

// SEQUENCER Constants: States
var RUNNING = 1; // already executing some navigation command
var WAITING = 0; // not active
var QUERYING = -1; // running without data modification
var ABORTING = -2; // running to end after abort

// SEQUENCER Constants: Rule RegExps for use in condition evaluation
var EXIT_ACTIONS = /^exit$/i;
var POST_ACTIONS = /^exitParent|exitAll|retry|retryAll|continue|previous$/i;
var SKIPPED_ACTIONS = /^skip$/i;
var STOP_FORWARD_TRAVERSAL_ACTIONS = /^stopForwardTraversal$/i;
var HIDDEN_FROM_CHOICE_ACTIONS = /^hiddenFromChoice$/i;
var DISABLED_ACTIONS = /^disabled$/i;

// SEQUENCER Variables
var state = WAITING; 
var currentAct = null;
var suspendedAct = null;

// SCO related Variables
var currentAPI; // reference to API during runtime of a SCO
var scoStartTime = null;

// Public interface
window.scorm_init = init;

