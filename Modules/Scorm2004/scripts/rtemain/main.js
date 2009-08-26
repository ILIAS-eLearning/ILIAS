/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2007 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
 * @author  Hendrik Holtmann <holtmann@mac.com>, Alex Killing <alex.killing@gmx.de>, Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
*/


// settings for log
var log_auto_flush = false;

var log_buffer = "";

//disable logging controls

if (disable_all_logging==true) {
	elm = all("toggleLog");
	elm.innerHTML="";
}

function toggleView() {
	elm_left = all("leftView");
	elm_right= all("tdResource");
	elm_tree = all("treeView");
	elm_log = all("ilLog");
	elm_controls = all("treeControls");
	elm_toggle=all("treeToggle");
	
	if (treeView==false) {
		elm_left.style.width='25%';
		elm_right.style.width='75%';
		elm_tree.style.display='block';
		elm_log.style.display='block';
		elm_controls.style.display='block';
		elm_toggle.innerHTML=this.config.langstrings['btnhidetree'];
		treeView=true;
	} else {
		elm_left.style.width='0%';
		elm_right.style.width='100%';
		elm_tree.style.display='none';
		elm_log.style.display='none';
		elm_controls.style.display='none';
		elm_toggle.innerHTML=this.config.langstrings['btnshowtree'];

		treeView=false;
	}
}


function toggleTree() {
	elm = all("toggleTree");
	
	if (treeState==false) {
		elm.innerHTML="Collapse All";
		treeYUI.expandAll();
		treeState=true;
	} else {
		elm.innerHTML="Expand All";
		treeYUI.collapseAll();
		treeState=false;
	}
}

function toggleLog() {
	elm = all("toggleLog");
	if (logState==false) {
		elm.innerHTML="Hide Log";
		logState=true;
		onWindowResize()
	} else {
		elm.innerHTML="Show Log";
		logState=false;
		onWindowResize();
	}
}

function sclog(mess, type)
{
	if (disable_all_logging) {
		return;
	}	

	if (type=="seq" && disable_sequencer_logging==true) return;
	log_auto_flush=true;

	switch (type)
	{
		case "cmi": 
			mess='<font color="green">'+mess+'</font>';
		case "info": 
			mess='<font color="orange">'+mess+'</font>';
		case "error": 
			mess='<font color="red">'+mess+'</font>';
		case "seq": 
			mess='<font color="blue">'+mess+'</font>';	
		default: 
			mess=mess;
	}
	if (log_auto_flush)
	{
		elm = all("ilLogPre");
		if (elm) 
		{
			elm.innerHTML = elm.innerHTML + mess + '<br />';
			sclogscroll()
		}
	}
	else
	{
		log_buffer = log_buffer + mess + '<br />';
	}
}

/**
* flush log
*/
function sclogflush()
{
	return;
	elm = all("ilLogPre");
	if (elm) 
	{
		elm.innerHTML = elm.innerHTML + log_buffer;
		sclogscroll()
	}
	log_buffer = "";
}

/**
* Clear the Log
*/
function sclogclear()
{
	elm = all("ilLogPre");
	if (elm) 
	{
		elm.innerHTML = '';
	}
}

/**
* Dump a variable
*/
function sclogdump(param, type)
{
	if (disable_all_logging) {
		return;
	}
	
	
	depth = 0;
	
	var pre = ''
	for (var j=0; j < depth; j++)
	{
		pre = pre + '    ';
	}
	
	//sclog(typeof param);
	switch (typeof param)
	{
		case 'boolean':
			if(param) sclog(pre + "true (boolean)"); else sclog(pre + "false (boolean)",type);
			break;

		case 'number':
			sclog(pre + param + ' (number)',type);
			break;

		case 'string':
			sclog(pre + param + ' (string)',type);
			break;

		case 'object':
			if (param === null)
			{
				sclog(pre + 'null');
			}
			if (param instanceof Array) sclog(pre + '(Array) {',type);
			else if (param instanceof Object) sclog(pre + '(Object) {');
			for (var k in param)
			{
				//if (param.hasOwnProperty(k)) // hasOwnProperty requires Safari 1.2
				//{
					if (typeof param[k] != "function",type)
					{
						sclog(pre + '[' + k + '] => ');
						sclogdump(param[k], depth + 1);
					}
				//}
			}
			sclog(pre + '}',type);
			break;
			
		case 'function':
			// we do not show functions
			break;

		default:
			sclog(pre + "unknown: " + (typeof param),type);
			break;
		
	}
}

/**
* scroll the log div to the end
*/
function sclogscroll()
{
	var top = document.getElementById('ilLog').scrollTop;
	var height = document.getElementById('ilLog').scrollHeight;
	var offset = document.getElementById('ilLog').offsetHeight;
	
//alert ("Top: " + top + ", Height: " + height + ", Offset: " + offset);
	
	if (top < 
		(height - offset - 1))
	{
		document.getElementById('ilLog').scrollTop = height - offset + 20;
	}
}


function timeStringParse(iTime, ioArray)    
{
  var mInitArray=new Array();
  var mTempArray2= new Array(); 
  mTempArray2[0]="0";
  mTempArray2[1]="0";
  mTempArray2[2]="0";
	
  var mDate = "0";
  var mTime = "0";

  // make sure the string is not null
  if ( iTime == null )
  {
      return;
  }
     
  // make sure that the string has the right format to split
  if ( ( iTime.length == 1 ) || ( iTime.indexOf("P") == -1 ) )
  {
      return;
  }
     mInitArray = iTime.split("P");

     // T is present so split into day and time part
     // when "P" is first character in string, rest of string goes in
     // array index 1
     if ( mInitArray[1].indexOf("T") != -1 )
     {
        mTempArray2 = mInitArray[1].split("T");
        mDate =  mTempArray2[0];
        mTime =  mTempArray2[1];
     }
     else
     {
        mDate =  mInitArray[1];
     }

     // Y is present so get year
     if ( mDate.indexOf("Y") != -1 )
     {
        mInitArray = mDate.split("Y");
        tempInt = parseInt(mInitArray[0],10);
        ioArray[0] = parseInt(tempInt,10);
     }
     else
     {
        mInitArray[1] = mDate;
     }

     // M is present so get month
     if ( mDate.indexOf("M") != -1 )
     {
        mTempArray2 = mInitArray[1].split("M");
        tempInt = parseInt(mTempArray2[0],10);
        ioArray[1] = parseInt(tempInt,10);
     }
     else
     {
        if ( mInitArray.length != 2 )
        {
           mTempArray2[1] = "";
        }
        else
        {               
           mTempArray2[1] = mInitArray[1];
        }
     }

     // D is present so get day
     if ( mDate.indexOf("D") != -1 )
     {
        mInitArray = mTempArray2[1].split("D");
        tempInt = parseInt(mInitArray[0],10);
        ioArray[2] = parseInt(tempInt,10);
     }
     else
     {
        mInitArray = new Array();
		mInitArray[0]="";
		mInitArray[1]="";
		
     }

     // if string has time portion
     if ( mTime.equals!="0")
     {
        // H is present so get hour
        if ( mTime.indexOf("H") != -1 )
        {
           mInitArray =  mTime.split("H");
           tempInt = parseInt(mInitArray[0],10);
           ioArray[3] = parseInt(tempInt,10);
        }
        else
        {
           mInitArray[1] = mTime;
        }

        // M is present so get minute
        if ( mTime.indexOf("M") != -1 )
        {
           mTempArray2 = mInitArray[1].split("M");
           tempInt = parseInt(mTempArray2[0],10);
           ioArray[4] = parseInt(tempInt,10);
        }
        else
        {
           if ( mInitArray.length != 2 )
           {
              mTempArray2[1] = "";
           }
           else
           {               
              mTempArray2[1] = mInitArray[1];
           }
        }

        // S is present so get seconds
        if ( mTime.indexOf("S") != -1 )
        {
           mInitArray = mTempArray2[1].split("S");

           if ( mTime.indexOf(".") != -1)
           {
              // split requires this regular expression for "."
              mTempArray2 = mInitArray[0].split(".");

              // correct for case such as ".2"
              if ( mTempArray2[1].length == 1 )
              {
                 mTempArray2[1] = mTempArray2[1] + "0";
              }

              tempInt2 = parseInt(mTempArray2[1],10);
              ioArray[6] = parseInt(tempInt2,10);
              tempInt = parseInt(mTempArray2[0],10);
              ioArray[5] = parseInt(tempInt,10);
           }
           else
           {
              tempInt = parseInt(mInitArray[0],10);
              ioArray[5] = parseInt(tempInt,10);
           }
        }
     }

  return ioArray;
}


function addTimes(iTimeOne,iTimeTwo) {
	  var mTimeString = null;
      var multiple = 1;
      mFirstTime = new Array();
      mSecondTime = new Array();

      for (var i = 0; i < 7; i++)
      {
         mFirstTime[i] = 0;
         mSecondTime[i] = 0;
      }

      mFirstTime=timeStringParse(iTimeOne, mFirstTime); 
      mSecondTime=timeStringParse(iTimeTwo, mSecondTime);
	   // add first and second time arrays  
      for (var i = 0; i < 7; i++)
      {
	     mFirstTime[i] =parseInt(mFirstTime[i],10) + parseInt(mSecondTime[i],10);
      }
	
	// adjust seconds, minutes, hours, and days if addition
      // results in too large a number
      if ( mFirstTime[6] > 99 )
      {
         multiple = parseFloat(mFirstTime[6] / 100);
         mFirstTime[6] = mFirstTime[6] % 100;
         mFirstTime[5] = parseInt(mFirstTime[5],10) + multiple;
      }

      if ( mFirstTime[5] > 59 )
      {
         multiple = parseFloat(mFirstTime[5] / 60);
         mFirstTime[5] = mFirstTime[5] % 60;
         mFirstTime[4] =parseInt(mFirstTime[4],10)+ multiple;
      }
      if ( mFirstTime[4] > 59 )
      {
         multiple = parseFloat(mFirstTime[4] / 60);
         mFirstTime[4] = mFirstTime[4] % 60;
         mFirstTime[3] =parseInt(mFirstTime[3],10) + multiple;
      }

      if ( mFirstTime[3] > 23 )
      {
         multiple = parseFloat(mFirstTime[3] / 24);
         mFirstTime[3] = mFirstTime[3] % 24;
         mFirstTime[2] = parseInt(mFirstTime[2],10)+ multiple;
      }
	
	  // create the new timeInterval string
      mTimeString = "P";
      if ( mFirstTime[0] != 0 )
      {
         tempInt = parseInt(mFirstTime[0],10);
         mTimeString +=  tempInt.toString();
         mTimeString += "Y";
      }
      if ( mFirstTime[1] != 0 )
      {
         tempInt = parseInt(mFirstTime[1],10);
         mTimeString +=  tempInt.toString();
         mTimeString +=  "M";
      }

      if ( mFirstTime[2] != 0 )
      {
         tempInt = parseInt(mFirstTime[2],10);
         mTimeString +=  tempInt.toString();
         mTimeString += "D";
      }

      if ( ( mFirstTime[3] != 0 ) || ( mFirstTime[4] != 0 ) 
           || ( mFirstTime[5] != 0 ) || (mFirstTime[6] != 0) )
      {
         mTimeString +=  "T";
      }

      if ( mFirstTime[3] != 0 )
      {
         tempInt = parseInt(mFirstTime[3],10);
         mTimeString +=  tempInt.toString();
         mTimeString +=  "H";
      }

      if ( mFirstTime[4] != 0 )
      {
         tempInt =parseInt(mFirstTime[4],10);
         mTimeString +=  tempInt.toString();
         mTimeString += "M";
      }

      if ( mFirstTime[5] != 0 )
      {
         tempInt = parseInt(mFirstTime[5],10);
         mTimeString +=  tempInt.toString();
      }

      if ( mFirstTime[6] != 0 )
      {
         if ( mFirstTime[5] == 0 )
         {
            mTimeString += "0";
         }
         mTimeString += ".";
         if ( mFirstTime[6] < 10 )
         {
            mTimeString += "0";
         }
         tempInt2 = parseInt(mFirstTime[6],10);
         mTimeString +=  tempInt2.toString();
      }
      if ( ( mFirstTime[5] != 0 ) || ( mFirstTime[6] != 0 ) )
      {
         mTimeString += "S";
      }

      return mTimeString;
}



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
	entry : 'ab-initio',
	exit : null,
	flow : false,
	foreignId: 0,
	forwardOnly : false,
	id : null, // item id not sequencing
	index: 0, // numerical index in activities
	isvisible : true,
	language : null,
	location : null,
	max : null,
	measureSatisfactionIfActive : true,
	min : null,
	modified : 0,
	objectiveMeasureWeight : 1.0,
	objectiveSetByContent : false,
	parameters : null,
	parent : null,
	preventActivation : false,
	progress_measure : null,
	randomizationTiming : 'never',
	raw : null,
	reorderChildren : false,
	requiredForCompleted : 'always',
	requiredForIncomplete : 'always',
	requiredForNotSatisfied : 'always',
	requiredForSatisfied : 'always',
	resourceId : null,
	rollupObjectiveSatisfied : true,
	rollupProgressCompletion : true,
	scaled : null,
	scaled_passing_score: null,
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
	completion_status : null,
	description : null,
	max : null,
	min : null,
	raw : null,
	scaled : null,
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

function parseJSONString (s) 
{
/*	var re = /^("(\\.|[^"\\\n\r])*?"|[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t])+?$/;
	try 
		{
		if (re.test(s)) 
		{
			return window.eval('(' + s + ')');
		} 
	} catch (e) {}
	throw new SyntaxError('parseJSONString: ' + s.substr(0, 200));*/
	if (s.length>1) {
		return window.eval('(' + s + ')');
	} else {
		return null;
	}	
	
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
	var data=null, subdata=null;
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
function launchTarget(target) {
	onItemUndeliver();
	
	mlaunch = msequencer.navigateStr(target);
   
	if (mlaunch.mSeqNonContent == null) {
		//throw away API from previous sco and sync CMI and ADLTree
		onItemDeliver(activities[mlaunch.mActivityID]);
	} else {
	  //call specialpage
	  	loadPage(gConfig.specialpage_url+"&page="+mlaunch.mSeqNonContent);
	}

}
function launchNavType(navType) {
	
	
	//if suspendAll set cmi.exit to suspend for active SCO
	if (navType=='SuspendAll') {
		pubAPI.cmi.exit="suspend";
		pubAPI.cmi.entry="resume";		
		//sync
		activities[msequencer.mSeqTree.mCurActivity.mActivityID].exit="suspend";
		activities[msequencer.mSeqTree.mCurActivity.mActivityID].entry="resume";
		
   	}

	//throw away API from previous sco and sync CMI and ADLTree, no api...SCO has to care for termination
	onItemUndeliver();
	
	mlaunch = new ADLLaunch();
	if (navType==='Start') {
		mlaunch = msequencer.navigate(NAV_START);
	}
		
	if (navType==='ResumeAll') {
		mlaunch = msequencer.navigate(NAV_RESUMEALL);
	}
	
	if (navType==='Exit') {
		mlaunch = msequencer.navigate(NAV_EXIT);
	}
	
	if (navType==='ExitAll') {
		mlaunch = msequencer.navigate(NAV_EXITALL);
	}
	
	if (navType==='Abandon') {
		mlaunch = msequencer.navigate(NAV_ABANDON);
	}
	
	if (navType==='AbandonAll') {
		mlaunch = msequencer.navigate(NAV_ABANDONALL);
	}
	
	if (navType==='SuspendAll') {
		mlaunch = msequencer.navigate(NAV_SUSPENDALL);
								
		if (typeof headers !== "object") {headers = {};}
		headers['Accept'] = 'text/javascript';
		headers['Accept-Charset'] = 'UTF-8';
		
		//look for tracking
		var acts=msequencer.mSeqTree.mActivityMap;
		var curtracking = new Object();
		var tracking = new Object();
		var states = new Object();
		var root = new Object();
		//save root elements
		for (var element in msequencer) {
			if (!(msequencer[element] instanceof Object)) {
				root[element]=msequencer[element];
			}
		}
		
		for (var element in acts) {
			curtracking[element]=acts[element].mCurTracking;
			tracking[element]=acts[element].mTracking;
			//iterate over other properties
			if (!states[element]) {states[element]=new Object();}
			for (subelement in acts[element]) {
				if (!(acts[element][subelement] instanceof Object) && !(acts[element][subelement] instanceof Array) ) {
					states[element][subelement]=acts[element][subelement];
				}
			}
		}
		
		
		var validreq=msequencer.mSeqTree.mValidReq;
		
		//just IDs
		var lastleaf=msequencer.mSeqTree.mLastLeaf;
		var firstcandidate=msequencer.mSeqTree.mFirstCandidate.mActivityID;
		var suspendall=msequencer.mSeqTree.mSuspendAll.mActivityID;
		var curactivity=msequencer.mSeqTree.mCurActivity.mActivityID;
	
		var suspendedTree= new Object();
		suspendedTree['mCurTracking']=curtracking;
		suspendedTree['mTracking']=tracking;
		suspendedTree['States']=states;
		suspendedTree['mCurActivity']=null;
		suspendedTree['mValidReq']=validreq;
		suspendedTree['mLastLeaf']=lastleaf;
		suspendedTree['mFirstCandidate']=firstcandidate;
		suspendedTree['mSuspendAll']=suspendall;
		suspendedTree['root']=root;
		
		var r = sendAndLoad(this.config.suspend_url, toJSONString(suspendedTree), null, null, null, headers);
	}
	
	if (navType==='Previous') {
		mlaunch = msequencer.navigate(NAV_PREVIOUS);
	}
	
	if (navType==='Continue') {
		mlaunch = msequencer.navigate(NAV_CONTINUE);		
	}
		if (mlaunch.mActivityID) {
			onItemDeliver(activities[mlaunch.mActivityID]);
		} else {
	  		//call specialpage
	  		loadPage(gConfig.specialpage_url+"&page="+mlaunch.mSeqNonContent);
		}
		
}


function onDocumentClick (e) 
{
	e = new UIEvent(e);
	var target = e.srcElement;
	
	
	//integration of ADL Sqeuencer
	
	if (target.tagName !== 'A' || !target.id ||  target.className.match(/disabled/) )
	{
		// ignore clicks on other elements than A
		// or non identified elements or disabled elements (non active Activities)
	} 
	
	//handle eventes like Contine, Previous, Exit...
	else if (target.id.substr(0, 3)==='nav') 
	{
		var navType=target.id.substr(3);
		launchNavType(navType);	
	} 
	
	//SCO selected by user directly (itm is used as ITEM_PREFIX)
	else if (target.id.substr(0, 3)===ITEM_PREFIX) 
	{
		if (e.altKey) {} // for special commands
		else 
		{
			mlaunch = msequencer.navigateStr( target.id.substr(3));
           
 			if (mlaunch.mSeqNonContent == null) {
				//alert(activities[mlaunch.mActivityID]);	
				//throw away API from previous sco and sync CMI and ADLTree
				onItemUndeliver();
				onItemDeliver(activities[mlaunch.mActivityID]);
			//	setTimeout("updateNav()",2000);  //temporary fix for timing problems
			} else {
			  //call specialpage
			  	loadPage(gConfig.specialpage_url+"&page="+mlaunch.mSeqNonContent);
			}
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

// set style class for current state
function setState(newState)
{
	replaceClass(document.body, guiState + 'State', newState + 'State');
	guiState = newState;
}

function loadPage(src) {
	
		//deactivate all controls, if session has ended
		if (mlaunch.mSeqNonContent!="_TOC_" && mlaunch.mSeqNonContent!="_SEQABANDON_" && mlaunch.mSeqNonContent!="_SEQABANDONALL_" ) {
			toggleClass('navContinue', 'disabled', true);
			toggleClass('navExit', 'disabled', true);
			toggleClass('navPrevious', 'disabled', true);
			toggleClass('navResumeAll', 'disabled', true);
			toggleClass('navExitAll', 'disabled', true);
			toggleClass('navStart', 'disabled',true);
			toggleClass('navSuspendAll', 'disabled', true);
			toggleClass('treeToggle', 'disabled', true);
		}
		
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
		
		RESOURCE_NAME= "SPECIALPAGE";
		var resContainer = window.document.getElementById("res");
		resContainer.src=src;
		resContainer.name=RESOURCE_NAME;
		onWindowResize();	
		save_global_objectives();
		
		if (treeView==true && mlaunch.mSeqNonContent!="_TOC_" && mlaunch.mSeqNonContent!="_SEQABANDON_" && mlaunch.mSeqNonContent!="_SEQABANDONALL_") {
			toggleView();
		}
		
}


// set info label
function setInfo (name, values) 
{
	var elm = all('infoLabel');
	var txt = translate(name, values);
	if (elm) 
	{
		window.top.document.title = elm.innerHTML = txt;
	}
} 

function setToc() 
{	
	var tree=new Array();
	buildNavTree(rootAct,"item",tree);
}


function updateControls(controlState) 
{
	
	if (mlaunch!=null) {
		toggleClass('navContinue', 'disabled', (mlaunch.mNavState.mContinue==false || typeof(activities[mlaunch.mActivityID].hideLMSUIs['continue'])=="object"));
		toggleClass('navExit', 'disabled', (mlaunch.mNavState.mContinueExit==false || typeof(activities[mlaunch.mActivityID].hideLMSUIs['exit'])=="object"));
		toggleClass('navPrevious', 'disabled', (mlaunch.mNavState.mPrevious==false || typeof(activities[mlaunch.mActivityID].hideLMSUIs['previous'])=="object"));
		toggleClass('navResumeAll', 'disabled', mlaunch.mNavState.mResume==false );
		if (mlaunch.mActivityID) {
			toggleClass('navExitAll', 'disabled', typeof(activities[mlaunch.mActivityID].hideLMSUIs['exitAll'])=="object");
		}	
		toggleClass('navStart', 'disabled', mlaunch.mNavState.mStart==false);
		toggleClass('navSuspendAll', 'disabled', (mlaunch.mNavState.mSuspend==false || typeof(activities[mlaunch.mActivityID].hideLMSUIs['suspendAll'])=="object"));
	}	
}


function setResource(id, url, base) 
{
	
	url= base + url;

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
		var resContainer = window.document.getElementById("res");
		resContainer.src=url;
		resContainer.name=RESOURCE_NAME;
	} 
	else 
	{			
		open(url, RESOURCE_NAME);
	} 
	
	if (guiItem) 
	{
		removeClass(guiItem, "current");
		removeClass(guiItem, "running");
		
	}
	guiItem = all(ITEM_PREFIX + id);
	if (guiItem)
	{
		removeClass(guiItem,"not_attempted",1);
		removeClass(guiItem,"incomplete",1);
		removeClass(guiItem,"completed",1);
		removeClass(guiItem,"failed",1);
		removeClass(guiItem,"passed",1);
		addClass(guiItem, "current");
		addClass(guiItem,"running");
	}
	onWindowResize();
	//reset
	adlnavreq=false;
	sclogdump("Launched: "+id,"info");
	sclogflush();
}

function removeResource(callback) 
{
	if (guiItem) 
	{
		removeClass(guiItem, "current");
	}
	var resContainer = window.document.getElementById("res");
	resContainer.src="about:blank";
	resContainer.name=RESOURCE_NAME;
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
	var hh = (tot-elm.offsetTop-4);
	var h = (tot-elm.offsetTop-4) + 'px';
	elm = all("treeView");
	var factor=1;
	if (logState==true) {factor=0.7;}
	if (elm) 
	{
		//elm.style.height = h;
		elm.style.height = (hh*factor -30 ) + "px";
	}
	elm = all("ilLog");
	
	
	if (elm) 
	{
		if (logState==true) {
			elm.style.height = (hh*0.3) + "px";
		} else {
			elm.style.height ="0px";
		}	
	}
	
	elm = all("res");
	if (elm) 
	{
		elm.style.height = h;
	}
}


function buildNavTree(rootAct,name,tree){
	var tocView = all('treeView');
	
	//create the TreeView instance:
	treeYUI = new YAHOO.widget.TreeView(tocView);
		
	//get a reusable reference to the root node:
	var root = treeYUI.getRoot();
		
	//root node only when in TOC
	if (mlaunch.mNavState.mChoice!=null) {
		var id=rootAct.id;
		if (rootAct.isvisible==true && typeof(mlaunch.mNavState.mChoice[id])=="object") {	
			//display the rootNode
			var rootNode = new YAHOO.widget.TextNode(rootAct.title, root, true);
			rootNode.href="#this";
			rootNode.target="_self";
			rootNode.labelElId=ITEM_PREFIX + rootAct.id;
		}	
	}
	
	build(rootAct,rootNode);
	
	function build(rootAct,attach){
		if (rootAct.item) {
			for (var i=0;i<rootAct.item.length;i++) {
				//only include if visible
				var id=rootAct.item[i].id;
				if (mlaunch.mNavState.mChoice!=null) {
					if (rootAct.item[i].isvisible==true && typeof(mlaunch.mNavState.mChoice[id])=="object") { 
						var sub = new YAHOO.widget.TextNode({label:rootAct.item[i].title, id:ITEM_PREFIX + rootAct.item[i].id}, attach, true);
						sub.href="#this";
						sub.target="_self";
						sub.labelElId=ITEM_PREFIX + rootAct.item[i].id;
					}	
				}
				//further childs
				if(rootAct.item[i].item) {
					build(rootAct.item[i],sub);
				}
			}
		}	
	}
	
	treeYUI.draw();
	treeYUI.expandAll();
}




function abortNavigation () 
{
	state = ABORTING; 
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
	gConfig=config;
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

	// Step 2: load adlActivityTree
	var adlAct = this.config.adlact_data || sendJSONRequest(this.config.adlact_url);
	
	if (!adlAct) {
		
		return alert('Fatal: Could not load ADLActivityTree.');
		
	} else {	
		
		var tree;
		
		adlTree = buildADLtree(adlAct,tree);
		//set parents
	    adlTree= setParents(adlTree);
		//assign Tree
		//scope equals courseID 
		var actTree = new SeqActivityTree(this.config.course_id,this.config.learner_id,this.config.scope,adlTree);

		actTree.setDepths();
		actTree.setTreeCount();		
		
		actTree.scanObjectives();
		actTree.buildActivityMap();
		
		msequencer.setActivityTree(actTree);	
		
	}		
	
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
	globalAct.auto_review = this.config.auto_review;
	
	// walk throught activities and add some helpful properties
	camWalk(cam.item, rootAct);
		

	// Step 2: load tracking data
	load();
	
	
	//load global objectives
	
	loadGlobalObj();
	
	
	//get suspend data
	suspendData =  sendJSONRequest(this.config.get_suspend_url);
	var wasSuspended=false;
	var wasFirstSession;
	if (suspendData) {
		if (suspendData!=null) {
			wasSuspended=true;
		}
	}
	
	if (wasSuspended==true) {
		wasSuspended = true;
        wasFirstSession = false;
		for (var element in suspendData.mTracking) {
			msequencer.mSeqTree.mActivityMap[element].mTracking=suspendData.mTracking[element];
		}
		
		//cur
		var cur=suspendData.mCurActivity;
		msequencer.mSeqTree.mCurActivity=null;
		
		var first=suspendData.mFirstCandidate;
		msequencer.mSeqTree.mFirstCandidate=null;
		
		msequencer.mSeqTree.mLastLeaf=suspendData.mLastLeaf;
		var suspendAll=suspendData.mSuspendAll;
		msequencer.mSeqTree.mSuspendAll=msequencer.mSeqTree.mActivityMap[suspendAll];
		var valid=suspendData.mValidReq;
		msequencer.mSeqTree.mValidReq=valid;
		
		
		
		//set root
		for (var element in suspendData.root) {
			msequencer[element]=suspendData.root[element];
		}
		//set states
		for (var element in suspendData.States) {
			//collect data
			var source=suspendData.States[element];
			for (var subelement in source) {
				msequencer.mSeqTree.mActivityMap[element][subelement]=source[subelement];
			}
		}
		
		//set curtracking
		//lets scan first an assign then
		var tempCur=new Object();
		for (var element in suspendData.mCurTracking) {
			tempCur[element]=new ADLTracking;
			for (var subelement in suspendData.mCurTracking[element]) {	
				if (subelement!="mObjectives") {
					tempCur[element][subelement]=suspendData.mCurTracking[element][subelement];
				} else {
					for (var obj in suspendData.mCurTracking[element]["mObjectives"] ) {
						tempCur[element]["mObjectives"][obj]=new SeqObjectiveTracking();
						//iterate over prop
						for (var prop in suspendData.mCurTracking[element]["mObjectives"][obj]) {
							tempCur[element]["mObjectives"][obj][prop]=suspendData.mCurTracking[element]["mObjectives"][obj][prop];
						}
					}
				}
			}
		}
		
		for (var element in tempCur) {
			//collect data
				msequencer.mSeqTree.mActivityMap[element]["mCurTracking"]=tempCur[element];
		}
		
	}	
	
	if (wasSuspended==true) {
	 	mlaunch = msequencer.navigate(NAV_RESUMEALL);
	} else {
		//do a fake launch to check if TOC choice should be displayed
		mlaunch = msequencer.navigate(NAV_NONE);
	
		if (mlaunch.mNavState.mStart) {
			//launch first activity //assume course has not be launched before
			mlaunch = msequencer.navigate(NAV_START);
		} 
	}
	
	var tolaunch=null;
	var count=0;
	
	for (var myitem in mlaunch.mNavState.mChoice) {
		if (mlaunch.mNavState.mChoice[myitem].mInChoice==true && mlaunch.mNavState.mChoice[myitem].mIsSelectable==true && mlaunch.mNavState.mChoice[myitem].mIsEnabled==true) {
			tolaunch=mlaunch.mNavState.mChoice[myitem].mID;
			count=count+1;
		}
	}
	if (count==1 || this.config.hide_navig == 1) {
		toggleView();  //hide tree
	}
		
	if (mlaunch.mSeqNonContent == null) {
		onItemDeliver(activities[mlaunch.mActivityID]);
	} else {
		if (count==1 && tolaunch!=null) {
			launchTarget(tolaunch);
		} else {
			loadPage(gConfig.specialpage_url+"&page="+mlaunch.mSeqNonContent);	
		}
	}
	
	updateControls();
	updateNav();
	if (this.config.session_ping>0)
	{
		setTimeout("pingSession()", this.config.session_ping*1000);
	}
	
}

function pingSession() 
{
	var r = sendJSONRequest(this.config.ping_url);
	//repeat timer
	setTimeout("pingSession()", this.config.session_ping*1000);
}


function loadGlobalObj() {
	var globalObj =  this.config.globalobj_data || sendJSONRequest(this.config.get_gobjective_url);
	if (globalObj) {
		if (globalObj.satisfied){adl_seq_utilities.satisfied=globalObj.satisfied;}
		if (globalObj.measure) {adl_seq_utilities.measure=globalObj.measure;}
		if (globalObj.status) {adl_seq_utilities.status=globalObj.status;}
	}
}

function buildADLtree(act, unused){
	var obj=new Object;
	var obj1, res, res2;
	for(var index in act) {
		var value;
   		if ((index.substr(0,1) == "_") ) {
			//create new object
			obj = eval("new "+index.substr(1)+"()");
			obj1 = buildADLtree(act[index],null);
			for(var i in obj1){
				obj[i]=obj1[i];
			}
		} else if ((act[index] instanceof Array)) {
			var toset=new Array();
			var temp=act[index];
			for (var i=0;i<temp.length;i++) {
				res=buildADLtree(temp[i],null);
				toset.push(res);
			}
			if (index!="mActiveChildren") {
				obj[index] = toset;
			}
			//keep trees in sync
			if (index=="mChildren") {
				obj["mActiveChildren"]=toset;
			}
		} else if ((act[index] instanceof Object)){
			//handle object
			res2=buildADLtree(act[index],null);
			obj[index] = res2;
		} else if (!(act[index] instanceof Array) && !(index.substr(0,1) == "_")){
			value = act[index];
			//set learner id and course id
			if (index == "mLearnerID") {value = this.config.learner_id;}
			if (index == "mScopeID") {value = this.config.scope;}
			obj[index] = value;
		}
    }
	return obj;
}


function setParents(obj) {
	for(var index in obj) { 
		if (index == "mChildren") {
			var temp=obj[index];	
			if (temp instanceof Array) {
				if (temp.length>0) {
					for (var i=0;i<temp.length;i++) {
						// get the object
						temp[i]['mParent']=obj;
						//check for further childs in array
					    var ch=setParents(temp[i]);
						temp[i]=ch;
					}
				}
			}	
		}
	
	}
	return obj;
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
	for (i=0;i<cmi.data.comment.length; i++)
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
	    act = activitiesByCMI[row[remoteMapping.interaction.cmi_node_id]];
	    act.interactions[dat.cmi_interaction_id] = dat;
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
	for (i=0;i<cmi.data.objective.length; i++)
	{
		row = cmi.data.objective[i];
		id = row[remoteMapping.objective.id];
		cmi_interaction_id = row[remoteMapping.objective.cmi_interaction_id];
		cmi_node_id = row[remoteMapping.objective.cmi_node_id];
		if (cmi_interaction_id===null || cmi_interaction_id==0) // objective to an activity or shared
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
			if (item.dirty===0)  {continue;}
			if (item.options) {
				if (item.options.notracking === true) 
				{
					continue;
				}
			}
			var data = [];
			for (var i=0, ni=schem.length; i<ni; i++) 
			{
				data.push(item[schem[i]]);
			}
			res.push(data);
			
			for (z in collection[k])			
			{
				if (z == 'interactions' || z == 'comments' || z == "objectives" || z == "correct_responses")
				{
					
					for (y in collection[k][z])
					{
					
						collection[k][z][y]['cmi_node_id']=collection[k]['cmi_node_id'];
					}
					walk(collection[k][z],z.substr(0,z.length-1));
				}
			}
			if (item.dirty!==2 && type=="node") {continue;}
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
	setTimeout("updateNav(true)",1000);
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
			var dat;
			if (data!=null) {
				//special mapping for comments
				if (k == "comments_from_learner" || k == "comments_from_lms") {
					dat = data['comments'];
				} else {
					dat = data[k];
				}
			} else {
				dat=null;
			}
			if (mod.type===Object) 
			{
				api[k] = {};
				
				for (var i=mod.mapping.length; i--;)
				{
					//TODO include in recursion
					if (k=="score") {
							var d=new Object();
							d['scaled']=data['scaled'];
							d['raw']=data['raw'];
							d['min']=data['min'];
							d['max']=data['max'];
							
							api[k]=d;
					
					} else {
						getAPISet(mod.mapping[i], dat, api[k]);
				    }
				}
			}
			else if (mod.type===Array) 
			{
				
				api[k] = [];
				
			//	sclogdump(dat);
				if (mod.mapping) 
				{
				//TODO-this needs a fix!!!
					//dat = data[mod.mapping.name];
				}
				for (i in dat) 
				{
					if (mod.mapping && !mod.mapping.func(dat[i])) continue;
					var d = getAPIWalk(mod, dat[i], {});
					var idname;
					if (k == "comments_from_learner" || k == "comments_from_lms") {
						idname = "cmi_comment_id";
					} else {
						idname = 'cmi_'+ k.substr(0, k.length-1) + '_id';						
					}
					//TODO include in recursion
					if (dat[i]['scaled']) {
						d['score']['scaled']=dat[i]['scaled'];
					}
					if (dat[i]['max']) {
						d['score']['max']=dat[i]['max'];
					}
					if (dat[i]['min']) {
						d['score']['min']=dat[i]['min'];
					}
					if (dat[i]['raw']) {
						d['score']['raw']=dat[i]['raw'];
					}
					if (dat[i]['objectiveID']) {
						d['id'] = dat[i]['objectiveID'];
					} else {
						d[idname] = dat[i][idname];
					}	
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

	//set data=null to null if not suspend
	
	// start recursive process to add current cmi subelements
	getAPIWalk(Runtime.models.cmi.cmi, data, api.cmi);

	return api;
	
}

function setItemValue (key, dest, source, destkey) 
{
	if (source && source.hasOwnProperty(key)) 
	{
		var d = source[key];
		var temp=d;
		if (!isNaN(parseFloat(d))) {
			d = Number(d);
		} else if (d==="true") {
			d = true;
		} else if (d==="false") {
			d = false;
		}
		//special handling for titles - no conversion
		if (key == "title") {
			d=temp;
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
	//dummy - most code removed, cause of sync problems with init method
	//finishing 
	// Hook core events
	attachUIEvent(window, 'unload', onWindowUnload);
	attachUIEvent(document, 'click', onDocumentClick);
	
	/*
	// Show Tree and Controls
	setToc(getTocData(), this.config.package_url);
	*/
	
	// Finishing startup
	setInfo('');
	setState('playing');
	attachUIEvent(window, 'resize', onWindowResize);
	onWindowResize();
}

function onWindowUnload () 
{
	//alert("onWindowUnload")
	onItemUndeliver(true);
	save_global_objectives();
	save();
}

function onItemDeliver(item) // onDeliver called from sequencing process (deliverSubProcess)
{
//	alert("OnItemDeliver")
	var url = item.href, v;
	// create api if associated resouce is of adl:scormType=sco
	if (item.sco)
	{
		
		// get data in cmi-1.3 format
		var data = getAPI(item.foreignId);
		
		// add ADL Request namespace data
		data.adl = {nav : {request_valid: {}}};
		
		var validRequests=msequencer.mSeqTree.getValidRequests();
		
		//we only set Continue, Previous and Choice according to specification
		data.adl.nav.request_valid['continue']=String(validRequests['mContinue']);
		data.adl.nav.request_valid['previous']=String(validRequests['mPrevious']);
		var choice=validRequests['mChoice'];
		
		for (var k in choice) {
			//TODO set target
			//data.adl.nav.request_valid['choice'].{k}=true;
			//data.adl.nav.request_valid['choice'];
		}
		
		// add some global values for all sco's in package
		data.cmi.learner_name = globalAct.learner_name;
		data.cmi.learner_id = globalAct.learner_id;
		data.cmi.cp_node_id = item.foreignId;
		data.cmi.session_time = undefined;
		data.cmi.completion_threshold = item.completionThreshold;
		data.cmi.launch_data = item.dataFromLMS;
		data.cmi.time_limit_action = item.timeLimitAction;
		data.cmi.max_time_allowed = item.attemptAbsoluteDurationLimit;

		if (item.objectives) 
		{
			for (k in item.objectives) {
				v=item.objectives[k];
				if (v.primary==true) {
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
						v = null;
					}
					data.cmi.scaled_passing_score = v;
					break; //we found the unique primary objective..so stop
				}	
			}
		}
		
		// assign api for public use from sco- only for debug
		pubAPI=data;
		
		currentAPI = window[Runtime.apiname] = new Runtime(data, onCommit, onTerminate);
	}
	// deliver resource (sco)
	// customize GUI
	
	syncSharedCMI(item);
		
	scoStartTime = currentTime();
	
	//support for auto-review
	item.options = new Object();
	item.options.notracking = false;
	if (globalAct.auto_review) {
		if (item.completion_status == 'completed' || item.success_status == 'passed') {
			pubAPI.mode = "review";
			item.options.notracking = true;
		}
	}
	
	//clear existing completion status in case of 2nd attempt
	if (currentAPI) {
		
		if ((item.exit=="normal" || item.exit=="" || item.exit=="time-out" || item.exit=="logout") && (item.exit!="suspend" && item.entry!="resume") ) {
			//provide us with a clean data set
			//pubAPI.cmi=Runtime.models.cmi;
			//explicitly set some entries
    		err = currentAPI.SetValueIntern("cmi.completion_status","unknown");
    		err = currentAPI.SetValueIntern("cmi.success_status","unknown");
			err = currentAPI.SetValueIntern("cmi.entry","ab-initio");
			pubAPI.cmi.entry="ab-initio";
			pubAPI.cmi.suspend_data = null;
			//pubAPI.cmi.interactions = null;
			//pubAPI.cmi.comments = null;
			pubAPI.cmi.total_time="PT0H0M0S";
		} 
		
		//set resume manually if suspendALL happened before
		if (item.exit=="suspend") {
			pubAPI.cmi.entry="resume";
			//clean suspend
			pubAPI.cmi.exit="";
		}
		
		//previous session has ended
		if (item.exit=="time-out" || item.exit=="logout") {
			//session has ended, reset times to defaults
			err = currentAPI.SetValueIntern("cmi.session_time","PT0H0M0S");
			//accessthrough pubAPI
			pubAPI.cmi.total_time="PT0H0M0S";
		}
	}
	updateControls();
	setResource(item.id, item.href+"?"+item.parameters, this.config.package_url);
}

function test() {
	alert("HALLO");
}

function syncSharedCMI(item) {
	var mStatusVector = msequencer.getObjStatusSet(item.id);
    var mObjStatus = new ADLObjStatus();
	var obj;
	var err
	//for first attempt
	
	if( mStatusVector != null ) {		
		for(i = 0; i < mStatusVector.length; i++ ) {
		    var idx=-1;
			mObjStatus = mStatusVector[i];
			// Set the objectives id
			//Get existing objectives
			var objCount = currentAPI.GetValueIntern("cmi.objectives._count");
            for( var j = 0; j < objCount; j++ ) {
				var obj = "cmi.objectives." + j + ".id";
				var nr = currentAPI.GetValueIntern(obj);
				if (nr==mObjStatus.mObjID) {
					idx=j;
					break;
				}
				
			}
			if (idx!=-1) {
				// Set the objectives success status
	        	obj = "cmi.objectives." + idx + ".success_status";

				if( mObjStatus.mStatus.toLowerCase()=="satisfied" )
	        	{
		       		err = currentAPI.SetValueIntern(obj,"passed");
	        	}
	          	else if( mObjStatus.mStatus.toLowerCase()=="notsatisfied")
	        	{
       				err = currentAPI.SetValueIntern(obj,"failed");
	        	}
				// Set the objectives scaled score
	        	obj = "cmi.objectives." + idx + ".score.scaled";
				if( mObjStatus.mHasMeasure==true && mObjStatus.mMeasure!=0 ) {
					err = currentAPI.SetValueIntern(obj,mObjStatus.mMeasure);
				}
	      	}
		}	
		
	}	
	//
	
	
}


function syncCMIADLTree(){
	//get global status
	
	var mPRIMARY_OBJ_ID = null;
	var masteryStatus = null;
	var sessionTime = null;
	var entry = null;
	var normalScore=-1.0;
	var completionStatus=null;
	var SCOEntry=null; 
	var suspended = false;
    
	
	// Get the current completion_status
	
	SCOEntry = currentAPI.GetValueIntern("cmi.exit");
	
		
    completionStatus = currentAPI.GetValueIntern("cmi.completion_status");
	
	if (completionStatus == "not attempted") {
		completionStatus = "incomplete";
	}
	
	// Get the current success_status
    masteryStatus = currentAPI.GetValueIntern("cmi.success_status");
	//alert(masteryStatus);
    // Get the current entry
	SCOEntry = currentAPI.GetValueIntern("cmi.entry");
	
	// Get the current scaled score
    score = currentAPI.GetValueIntern("cmi.score.scaled");

    // Get the current session time
    sessionTime = currentAPI.GetValueIntern("cmi.session_time");

	//get current activity
	var act = msequencer.mSeqTree.getActivity(mlaunch.mActivityID);
	
	var primaryObjID = null;
    var foundPrimaryObj = false;
    var setPrimaryObjSuccess = false;
    var setPrimaryObjScore = false;
	
	objs = act.getObjectives();
	if( objs != null ) {
		for(var j = 0; j < objs.length; j++ ) {
			obj = objs[j];
			if( obj.mContributesToRollup==true ) {
				if( obj.mObjID != null ){
                      primaryObjID = obj.mObjID;
         		}
                break;
			}
		}
	}
	
	//get Objective List
	var numObjs= currentAPI.GetValueIntern("cmi.objectives._count");
    for( var i = 0; i < numObjs; i++ ) {
		var obj = "cmi.objectives." + i + ".id";
        var objID= currentAPI.GetValueIntern(obj);
		if( primaryObjID != null && objID==primaryObjID )
	    {
	        foundPrimaryObj = true;	
	    } else {
		    foundPrimaryObj = false;
	    
		}
		obj = "cmi.objectives." + i + ".success_status";
        objMS= currentAPI.GetValueIntern(obj);
		if( objMS=="passed" ) {
            msequencer.setAttemptObjSatisfied(mlaunch.mActivityID, objID, "satisfied");
            if( foundPrimaryObj==true )
            {
               setPrimaryObjSuccess = true;
            }
        }
		else if( objMS=="failed" )
        {
             msequencer.setAttemptObjSatisfied(mlaunch.mActivityID, objID, "notSatisfied");

             if( foundPrimaryObj==true)
             {
                setPrimaryObjSuccess = true;
             }
        }
		else
        {
           	msequencer.setAttemptObjSatisfied(mlaunch.mActivityID, objID, "unknown");
        }

		obj = "cmi.objectives." + i + ".score.scaled";
        objScore= currentAPI.GetValueIntern(obj);
        if( objScore!="" && objScore!="unknown" && objScore!=null ) {
			normalScore = objScore; 
			msequencer.setAttemptObjMeasure(mlaunch.mActivityID, objID, normalScore);
			if( foundPrimaryObj==true ){
 				setPrimaryObjScore = true;
			}
			
		}     
		else
         {
            msequencer.clearAttemptObjMeasure(mlaunch.mActivityID, objID);
         }   
   	}
	
	// Report the completion status
    msequencer.setAttemptProgressStatus(mlaunch.mActivityID, completionStatus);
    
	if (SCOEntry=="resume" ) {
         msequencer.reportSuspension(mlaunch.mActivityID, true);
		 // preserve session state
    } else {
         msequencer.reportSuspension(mlaunch.mActivityID, false);
		//clear state data for this attempt

    }	
	
	// Report the success status
      if( masteryStatus=="passed" )
      {
		
         msequencer.setAttemptObjSatisfied(mlaunch.mActivityID, mPRIMARY_OBJ_ID, "satisfied");
      }
      else if( masteryStatus=="failed" )
      {
         msequencer.setAttemptObjSatisfied(mlaunch.mActivityID, mPRIMARY_OBJ_ID, "notSatisfied");
      }
      else
      {
          if( setPrimaryObjSuccess==false )
          {
            msequencer.setAttemptObjSatisfied(mlaunch.mActivityID, mPRIMARY_OBJ_ID, "unknown");
          }
      }

	  // Report the measure
	  if( score!="" && score!="unknown") {
			normalScore = score;
        	msequencer.setAttemptObjMeasure(mlaunch.mActivityID, mPRIMARY_OBJ_ID, normalScore);
       }

	   else{
            if( setPrimaryObjScore==false )
            {
               msequencer.clearAttemptObjMeasure(mlaunch.mActivityID, mPRIMARY_OBJ_ID);
            }
       }
	}

function onItemUndeliver(noControls) // onUndeliver called from sequencing process (EndAttempt)
{
//	alert("onItemUndeliver")
	
	// customize GUI
	if (noControls!=true) {
		updateNav();
		updateControls();
	}	
	// throw away the resource
	// it may change api data in this
	removeResource(undeliverFinish);
}

function undeliverFinish(){
//	alert("undeliverFinish yes")
	
	// throw away api, and try a API.Terminate before (will return "false" if already closed by SCO)
	if (currentAPI) 
	{
		syncCMIADLTree();		
		var stat = pubAPI.cmi.exit;
		
		save_global_objectives();
		//sync dynamic objectives to ActivityTree
		//syncDynObjectives();
		save();
		if (state!=2) {
			//currentAPI.Terminate("");
		}
			
	}
	currentAPI = window[Runtime.apiname] = null;
	// set some data values 
	if (currentAct) 
	{
		currentAct.accessed = currentTime()/1000;
		if (!currentAct.dirty) currentAct.dirty = 1;
	}
}

function syncDynObjectives(){
	var objectives=pubAPI.cmi.objectives;
	var act=activities[mlaunch.mActivityID].objectives;
	for (var i=0;i<objectives.length;i++) {
	  if (objectives[i].id) {
		var id=objectives[i].id;
		var obj=objectives[i];
		//check for property
		if (!act.id) {
			act[id]=new Objective();
			act[id]['objectiveID']=id;
			act[id]['id']=id;
			
			//iterate over obj properties
			for (var element in obj) {
				if (element!="id" && element!="cmi_objective_id") {
					if (element!="score") {
						act[id][element]=obj[element];
					}
					//if score then step deeper
					if (element=="score") {
						for (var subelement in obj[element]) {
							act[id][subelement]=obj[element][subelement];
						}
					}
				}	
			}	
		}
	}
  }
}


function save_global_objectives() {
	if (adl_seq_utilities.measure!=null || adl_seq_utilities.satisfied!=null || adl_seq_utilities.status!=null  ) {
		result = this.config.gobjective_url 
			? sendJSONRequest(this.config.gobjective_url, this.adl_seq_utilities)
			: {};
	}
}

// sequencer terminated
function onNavigationEnd()
{
	removeResource();
}

function onCommit(data) 
{
	return setAPI(data.cmi.cp_node_id, data);
}

function onTerminate(data) 
{
	var navReq;
	switch (data.cmi.exit)
	{
		case "suspend":
			navReq = {type: "suspend"};
			break;
		case "logout":  //depcracated
			navReq = {type: "ExitAll"};
		case "time-out":
			navReq = {type: "ExitAll"};
			//learner atttempt has ended
		default : // "", "normal"
			break;
	}
	
	if (data.adl && data.adl.nav) 
	{
		var m = String(data.adl.nav.request).match(/^(\{target=([^\}]+)\})?(choice|continue|previous|suspendAll|exit(All)?|abandon(All)?)$/);
		if (m) 
		{
			navReq = {type: m[3].substr(0, 1).toUpperCase() + m[3].substr(1), target: m[2]};
		}
	}
	if (navReq) 
	{
		// will only work if no navigation is ongoing 
		// so we delay to next script cycle
		// and use closure to retain current variable scope
		//alert('ADLNAV => '+[navReq.type, navReq.target])
	//	alert(navReq.type, navReq.target);

		if (navReq.type!="suspend") {
			adlnavreq=true; 
			//TODO fix for Unix
			if (navReq.type=="Choice") {
				launchTarget(navReq.target);
			} else {
				launchNavType(navReq.type);
			}	
		}
	/*	window.setTimeout( 
			new (function (type, target) {
				return function () {
					execNavigation(type, target);
				}
			})(navReq.type, navReq.target), 0);
		*/	
	}	
	updateNav(true);
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


function updateNav(ignore) {
	//check for tree
	if (!all("treeView")) {
		return;
	}
	//first set it
	if (ignore!=true) {
		setToc();
	}	
	var tree=msequencer.mSeqTree.mActivityMap;
	var disable;
	for (i in tree) {
		var disable=true;
		var test=null;
		if (mlaunch.mNavState.mChoice!=null) {
			test=mlaunch.mNavState.mChoice[i];
		}	
		if (test) {
			if (test['mIsSelectable']==true && test['mIsEnabled']==true ) { 
				disable=false;
			} else {
				disable=true;
			}
		}
		if (guiItem && ignore==true) {
			if (guiItem.id ==ITEM_PREFIX + tree[i].mActivityID)
			{
				continue;
			}
		}
		var elm = all(ITEM_PREFIX + tree[i].mActivityID);
		if (!elm) {return;}
		toggleClass(elm, 'disabled', disable); 
		//search for the node to change
		//set icons
		if (activities[tree[i].mActivityID].sco && activities[tree[i].mActivityID].href) {
			
		
			var node_stat_completion=activities[tree[i].mActivityID].completion_status;
			//not attempted
			if (node_stat_completion==null || node_stat_completion=="not attempted") {
				toggleClass(elm,"not_attempted",1);
			}
		
			//incomplete
			if (node_stat_completion=="unknown" || node_stat_completion=="incomplete") {
				removeClass(elm,"not_attempted",1);
				toggleClass(elm,"incomplete",1);	
			}
			
			//just in case-support not required due to spec
			if (node_stat_completion=="browsed") {
					removeClass(elm,"not_attempted",1);
					toggleClass(elm,"browsed",1);
			}
			
			//completed
			if (node_stat_completion=="completed") {
				removeClass(elm,"not_attempted",1);
				removeClass(elm,"incomplete",1);
				removeClass(elm,"browsed",1);
				toggleClass(elm,"completed",1);	
			}
			
			//overwrite if we have information on success (interaction sco) - ignore success=unknown
			
			var node_stat_success=activities[tree[i].mActivityID].success_status;
			if (node_stat_success=="passed" || node_stat_success=="failed" ) {
				
				//passed
				if (node_stat_success=="passed") {
					removeClass(elm,"failed",1);
					toggleClass(elm,"passed",1);
				//failed
				} else {
					removeClass(elm,"passed",1);
					toggleClass(elm,"failed",1);
				}
			}
		} else {
			if (elm && activities[tree[i].mActivityID].href) {
				toggleClass(elm,"asset",1);
			}	
		}
		
		//toggleClass(elm.parentNode, 'hidden', item.hidden);
	}
}

function isIE(versionNumber) {
var detect = navigator.userAgent.toLowerCase();
if(!(navigator && navigator.userAgent && navigator.userAgent.toLowerCase)) {
  	        return false;
  	    } else {
  	        if(detect.indexOf('msie') + 1) {
  	            // browser is internet explorer
  	            var ver = function() {
  	                // http://msdn.microsoft.com/workshop/author/dhtml/overview/browserdetection.asp
  	                // Returns the version of Internet Explorer or a -1
  	                // (indicating the use of another browser).
  	                var rv = -1; // Return value assumes failure
  	                if (navigator.appName == 'Microsoft Internet Explorer') {
  	                    var ua = navigator.userAgent;
  	                    var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
  	                    if (re.exec(ua) != null) {
  	                        rv = parseFloat( RegExp.$1 );
  	                    }
  	                }
  	                return rv;
  	            };
  	            var valid = true;
  	            // if the version can be found and the version is less than our version number it is invalid
  	            if ((ver > -1) && (ver < versionNumber)) {
  	                valid = false;
  	            }
  	            return valid;
  	        } else {
  	            return false
  	        }
  	    }
}

function pausecomp(millis) 
{
	var date = new Date();
	var curDate = null;

	do { curDate = new Date(); } 
	while(curDate-date < millis);
}

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

//integration of ADL Sequencer
var msequencer=new ADLSequencer();
var mlaunch=null;
var adlnavreq=null;
var treeYUI=null;
var logState=false;
var treeState=true;


// GUI constants
var ITEM_PREFIX = "itm";
var RESOURCE_PARENT = "tdResource";
var RESOURCE_NAME = "frmResource";
var RESOURCE_TOP = "mainTable";

// GUI Variables 
var guiItem;
var guiState; // loading, playing, paused, stopped, buffering
var gConfig;

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
var SCOEntryedAct = null;

// SCO related Variables
var currentAPI; // reference to API during runtime of a SCO
var scoStartTime = null;

var treeView=true;

//remove later
var pubAPI=null;
// Public interface
window.scorm_init = init;

