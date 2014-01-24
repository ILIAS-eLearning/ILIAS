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
 * @author  Hendrik Holtmann <holtmann@mac.com>, Alfred Kohnert <alfred.kohnert@bigfoot.com>, modifications by Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @version $Id$
*/

function Runtime(cmiItem, onCommit, onTerminate, onDebug) 
{
	// implementation of public methods
	
	// public error code property getter
	function GetLastError() 
	{
		if (logActive)
			sendLogEntry(getMsecSinceStart(),'GetLastError',"","",String(error),"");
		return String(error);
	}

	/**
	 * public error description property getter
	 * error codes and descriptions (see "SCORM Run-Time Environment
	 * Version 1.3" on www.adlnet.org)
	 * @param {string} error number must be string!
	 */	 
	function GetErrorString(param) 
	{
		if (typeof param !== 'string') 
		{
			if (logActive)
				sendLogEntry(getMsecSinceStart(),'GetErrorString',String(param),"","false",201);
			return setReturn(201, 'GetErrorString param must be empty string', '');
		}
		var e = Runtime.errors[param];
		var returnValue = e && e.message ? String(e.message).substr(0,255) : '';
		if (logActive)
			sendLogEntry(getMsecSinceStart(),'GetErrorString',String(param),"",returnValue,0);
		return returnValue;
	}

	/**
	 * public error details getter 
	 * may be useful in debugging
	 * @param {string} required; but not evaluated in this implementation
	 * @return {string} in this implementation always info for last error if any	  
	 */	 
	function GetDiagnostic(param) 
	{
		var returnValue = (error ? String(diagnostic).substr(0,255) : 'no diagnostic');
		if (param != "") returnValue = param + ': ' + returnValue;
		if (logActive)
			sendLogEntry(getMsecSinceStart(),'GetDiagnostic',String(param),"",returnValue,"");
		return returnValue;
	}

	/**
	 * Open connection to data provider 
	 * @access private, but also bound to 'this'
	 * @param {string} required; must be '' 
	 */	 
	function Initialize(param) 
	{
		//for SCORM Test Tool - function checks Values set at previous attempts
		function checkInternalValues(a_debugValues){
			function checkGetValue(cmivar){
				var a_getValues = ['comments_from_lms','completion_threshold','credit','entry','launch_data','learner_id','learner_name','max_time_allowed','mode','scaled_passing_score','time_limit_action','total_time'];
				var b_getValue=false;
				for (var i=0; i<a_getValues.length; i++){
					if(cmivar.indexOf("cmi."+a_getValues[i]) > -1) b_getValue=true;
				}
				return b_getValue;
			}

			var j=0;
			while (j < a_debugValues.length){
				if (a_debugValues[j].indexOf("completion_status") > -1){
					if (GetValueIntern(a_debugValues[j]) !="unknown") removeByElement(a_debugValues,a_debugValues[j]);
					else j++;
				}
				else if (a_debugValues[j].indexOf("success_status") > -1){
					if (GetValueIntern(a_debugValues[j]) !="unknown") removeByElement(a_debugValues,a_debugValues[j]);
					else j++;
				}
				else if (GetValueIntern(a_debugValues[j]) !="" && checkGetValue(a_debugValues[j]) == false)
					removeByElement(a_debugValues,a_debugValues[j]);
				else j++;
			}
		}
		setReturn(-1, 'Initialize(' + param + ')');
		if (param!=='') 
		{
			if (logActive)
				sendLogEntry(getMsecSinceStart(),'Initialize',param,"","false",201);
			return setReturn(201, 'param must be empty string', 'false');
		}
		switch (state) 
		{
			case NOT_INITIALIZED:
				dirty = false;
				if (cmiItem instanceof Object) 
				{
					state = RUNNING;
					//initialize values
					total_time_at_initialize=GetValueIntern("cmi.total_time");

					if (logActive) {
						sendLogEntry(getMsecSinceStart(),'Initialize',"","","true",0);
						scoDebugValues = new Array();
						for (var i=0; i<gConfig.debug_fields.length; i++){
							scoDebugValues[i] = gConfig.debug_fields[i];
						}
						scoDebugValuesTest = new Array();
						for (var i=0; i<gConfig.debug_fields_test.length; i++){
							scoDebugValuesTest[i] = gConfig.debug_fields_test[i];
						}
						if (GetValueIntern("cmi.entry") != "ab-initio") {
							checkInternalValues(scoDebugValues);
							checkInternalValues(scoDebugValuesTest);
						}
					}
					return setReturn(0, '', 'true');
				} 
				else 
				{
					if (logActive)
						sendLogEntry(getMsecSinceStart(),'Initialize',"","","false",102);
					return setReturn(102, '', 'false');
				}
				break;
			case RUNNING:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),'Initialize',"","","false",103);
				return setReturn(103, '', 'false');
			case TERMINATED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),'Initialize',"","","false",104);
				return setReturn(104, '', 'false');
		}
		if (logActive)
			sendLogEntry(getMsecSinceStart(),'Initialize',"","","false",103);
		return setReturn(103, '', 'false');
	}	

	

	/**
	 * Sending changes to data provider 
	 * @access private, but also bound to 'this'
	 * @param {string} required; must be '' 
	 */	 
	function Commit(param) 
	{
		setReturn(-1, 'Commit(' + param + ')');
		if ((typeof param == "undefined") || param == null) {
			//ToDo: check if allowed by Testsuite; else use with check values
			param = 'undefined';
			fixedFailure=true;
		}
		else if (param!=='')
		{
			if (logActive)
				sendLogEntry(getMsecSinceStart(),'Commit',param.toString(),"","false",201);
			return setReturn(201, 'param must be empty string', 'false');
		}
		switch (state) 
		{
			case NOT_INITIALIZED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),'Commit',param,"","false",142);
				return setReturn(142, '', 'false');
			case RUNNING:
				//calculating not at terminate to avoid save because many contributors of learning modules send at the end just before terminate() commit()
				if ((!cmiItem.cmi.mode || cmiItem.cmi.mode==="normal") && (typeof cmiItem.cmi.session_time!="undefined" || config.time_from_lms==true)) {
					if (config.time_from_lms==true) {
						var interval = (currentTime() - msec)/1000;
						var dur = new ADLDuration({iFormat: FORMAT_SECONDS, iValue: interval});
						cmiItem.cmi.session_time = dur.format(FORMAT_SCHEMA);
					}
					var total_time=addTimes(total_time_at_initialize,cmiItem.cmi.session_time);
					cmiItem.cmi.total_time = total_time.toString();
				}
				//auto suspend
				if (config.auto_suspend==true) cmiItem.cmi.exit="suspend";
				//store correct status in DB; returnValue1 because of IE;
				var statusValues=syncCMIADLTree();
				//statusHandler(cmiItem.scoid,"completion",statusValues[0]);
				//statusHandler(cmiItem.scoid,"success",statusValues[1]);
				var returnValue = onCommit(cmiItem);
				if (returnValue && saveOnCommit == true) {
					if (config.fourth_edition) {
						var sgo=saveSharedData(cmiItem);
					}
					returnValue = save();
				}
				if (returnValue) 
				{
					dirty = false;
					if (logActive && commitByTerminate==false)
						sendLogEntry(getMsecSinceStart(),'Commit',param,"","true",0);
					return setReturn(0, '', 'true');
				} 
				else
				{
					if (logActive)
						sendLogEntry(getMsecSinceStart(),'Commit',param,"","false",391);
					return setReturn(391, 'Persisting failed', 'false');
				}
				break;
			case TERMINATED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),'Commit',param,"","false",143);
				return setReturn(143, '', 'false');
		}
	}

	/**
	 * Close connection to data provider 
	 * @access private, but also bound to 'this'
	 * @param {string} required; must be '' 
	 */	 
	function Terminate(param) {
		setReturn(-1, 'Terminate(' + param + ')');
		if (param!=='') 
		{
			if (logActive)
				sendLogEntry(getMsecSinceStart(),'Terminate',param,"","false",201);
			return setReturn(201, 'param must be empty string', 'false');
		}
		switch (state) 
		{
			case NOT_INITIALIZED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),'Terminate',"","","false",112);
				return setReturn(112, '', 'false');
			case RUNNING:
				// TODO check for possible exceptions
				// resulting in code 111 (REQ_5.3)
				Runtime.onTerminate(cmiItem, msec); // wrapup from LMS 
				setReturn(-1, 'Terminate(' + param + ') [after wrapup]');
				saveOnCommit = true;
				commitByTerminate=true;
				var returnValue = Commit(''); // wrap up 
				commitByTerminate=false;
				saveOnCommit = true;
				state = TERMINATED;
				if (logActive) {
					sendLogEntry(getMsecSinceStart(),'Terminate',"","",returnValue,0);
					sendLogEntry(getMsecSinceStart(),'ANALYZE',"",scoDebugValues,"","");
					sendLogEntry(getMsecSinceStart(),'ANALYZETEST',"",scoDebugValuesTest,"","");
					if (summaryOnUnload == true) createSummary();
				}
				onTerminate(cmiItem); // callback
				return setReturn(0, '', returnValue);//error should not change if logActive
			case TERMINATED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),'Terminate',"","","false",113);
				return setReturn(113, '', 'false');
		}
	}
	
	/**
	 * Read data element entry 
	 * @access private, but also bound to 'this'
	 * @param {string} required; must be valid cmi element string 
	 */	 
	function GetValue(sPath) 
	{
		//log.info("GetValue: "+sPath);
		setReturn(-1, 'GetValue(' + sPath + ')');
//		state=1;
		switch (state) 
		{
			case NOT_INITIALIZED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),"GetValue",sPath,"","false",122);
				sclogdump("Not initialized","error");
				return setReturn(122, '', '');
			case RUNNING:
				if (typeof(sPath)!=='string') 
				{
					if (logActive)
						sendLogEntry(getMsecSinceStart(),"GetValue",sPath,"","false",201);
					return setReturn(201, 'must be string', '');
				}
				if (sPath==='') 
				{
					if (logActive)
						sendLogEntry(getMsecSinceStart(),"GetValue",sPath,"","false",301);
					return setReturn(301, 'cannot be empty string', '');
				}
				var r;
				if (sPath=="cmi.total_time") r=setReturn(0,'',total_time_at_initialize);
				else r=getValue(sPath, false);
				if (logActive) {
					sendLogEntry(getMsecSinceStart(),"GetValue",sPath,"",r,error);
					var a_getValues = ['comments_from_lms','completion_threshold','credit','entry','launch_data','learner_id','learner_name','max_time_allowed','mode','scaled_passing_score','time_limit_action','total_time'];
					for (var j=0; j<a_getValues.length; j++) {
						if (sPath.indexOf("cmi."+a_getValues[j])>-1){
							removeByElement(scoDebugValues,sPath);
							removeByElement(scoDebugValuesTest,sPath);
						}
					}
				}
				return error ? '' : setReturn(0, '', r); 
				// TODO wrap in TRY CATCH
			case TERMINATED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),"GetValue",sPath,"","false",123);
				return setReturn(123, '', '');
		}
	}
	
	//allows to get data even after termination
	function GetValueIntern(sPath) {
		//setReturn(-1, 'GetValueIntern(' + sPath + ')');
		
		var r = getValue(sPath, false);
		//sclogdump("ReturnInern: "+sPath + " : "+ r);
		return error ? '' : setReturn(0, '', r); 	
		
	}
	
	/**
	 * Read data element entry 
	 * @access private
	 * @param {string} required; must be valid cmi element string 
	 * @param {boolean} optional; if true all permissions are assumed as readwrite 
	 */
	function getValue(path, sudo) 
	{
		var tokens = path.split('.');
		return walk(cmiItem, Runtime.models[tokens[0]], tokens, null, sudo, {parent:[]});
	}	
	
	
	//allows to set data ignoring the status
	function SetValueIntern(sPath,sValue) {
		if (typeof sValue == "string") { //all ok
		} else if (typeof sValue == "number") {
			sValue = sValue.toString(10);
		} else { 
			sValue = "";
		}
		var r = setValue(sPath, sValue);
		return error ? '' : setReturn(0, '', r);
	}

	/**
	 * Update or create data element entry 
	 * @access private, but also bound to 'this'
	 * @param {string} required; must be valid cmi element string
	 * @param {string} required; must be valid cmi element value
	 */	  
	function SetValue(sPath, sValue) 
	{
		setReturn(-1, 'SetValue(' + sPath + ', ' + sValue + ')');
		switch (state) 
		{
			case NOT_INITIALIZED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),"SetValue",sPath,sValue,"false",132);
				return setReturn(132, '', 'false');
			case RUNNING:
				if (typeof(sPath)!=='string') 
				{
					if (logActive)
						sendLogEntry(getMsecSinceStart(),"SetValue",sPath,sValue,"false",201);
					return setReturn(201, 'must be string', 'false');
				}
				if (sPath==='') 
				{
					if (logActive)
						sendLogEntry(getMsecSinceStart(),"SetValue",sPath,sValue,"false",351);
					return setReturn(351, 'Param 1 cannot be empty string', 'false');
				}
				if ((typeof sValue == "undefined") || sValue == null) {
					if (logActive) sendLogEntry(getMsecSinceStart(),"SetValue",sPath,""+sValue,"false",406);
					return setReturn(406, 'Value cannot be undefined or null', 'false');
				}
				else if (typeof sValue == "object") {
					if (logActive) sendLogEntry(getMsecSinceStart(),"SetValue",sPath,"object: "+String(sValue),"false",406);
					return setReturn(406, 'Value cannot be an object', 'false');
				}
				else if (typeof sValue == "function") {
					if (logActive) sendLogEntry(getMsecSinceStart(),"SetValue",sPath,"function: "+sValue.toString(),"false",406);
					return setReturn(406, 'Value cannot be a function', 'false');
				}
				else if (typeof sValue == "number") {
					sValue = sValue.toString(10);
					fixedFailure=true;
				}
				else if (typeof sValue == "boolean") {
					sValue = ""+sValue;
					fixedFailure=true;
				}
				else {
					sValue = ""+sValue;
				}
				try 
				{
					var r = setValue(sPath, sValue);
					if (!error) {
						if (logActive) {
							sendLogEntry(getMsecSinceStart(),"SetValue",sPath,sValue,"true",0);
							removeByElement(scoDebugValues,sPath);
							removeByElement(scoDebugValuesTest,sPath);
							//check logik for 3rd edition
							if (sPath == "cmi.completion_status" && cmiItem.cmi.completion_threshold && cmiItem.cmi.completion_threshold>=0) {
								sendLogEntry("","INFO","completion_status_by_progress_measure",GetValueIntern("cmi.completion_status"),"","");
							}
							if (sPath == "cmi.success_status" && cmiItem.cmi.scaled_passing_score && cmiItem.cmi.scaled_passing_score>=-1) {
								sendLogEntry("","INFO","success_status_by_score_scaled",GetValueIntern("cmi.success_status"),"","");
							}
						}	
						var lastToken = sPath.substring(sPath.lastIndexOf('.') + 1);
						if(lastToken == "completion_status" || lastToken == "success_status") {
							setValue(sPath + "_SetBySco", "true");
						}
						// if (sPath == "cmi.completion_status" && cmiItem.scoid != null ) {
							// statusHandler(cmiItem.scoid,"completion",sValue);
						// }

						// if (sPath == "cmi.success_status" && cmiItem.scoid != null ) {
							// statusHandler(cmiItem.scoid,"success",sValue);
						// }
					} else {
						if (logActive)
							sendLogEntry(getMsecSinceStart(),"SetValue",sPath,sValue,"false",error);
					}	
					return error ? 'false' : 'true'; 
				} catch (e) 
				{
					if (logActive)
						sendLogEntry(getMsecSinceStart(),"SetValue",sPath,sValue,"false",351);
					return setReturn(351, 'Exception ' + e, 'false');
				}
				break;
			case TERMINATED:
				if (logActive)
					sendLogEntry(getMsecSinceStart(),"SetValue",sPath,sValue,"false",133);
				return setReturn(133, '', 'false');
		}
	}
	/**
	 * Update or create data element entry
	 * @access private
	 * @param {string} required; must be valid cmi element string 
	 * @param {string} required; must be valid cmi element value
	 * @param {boolean} optional; if true all permissions are assumed as readwrite 
	 */
	function setValue(path, value, sudo) 
	{

		var tokens = path.split('.');
		return walk(cmiItem, Runtime.models[tokens[0]], tokens, value, sudo, {parent:[]});
	}
	
	/**
	 * Synchronized walk on data instance and data model to read/replace content
	 * @access private
	 * @param {object} required; data instance node 
	 * @param {object} required; data model node
	 * @param {array} required; path of tokens to walk down ["cmi", "core", "etc"] 
	 * @param {object} optional; new value for setValue
	 * @param {boolean} optional; if true walks in superuser mode, i.e. ignore permissions 
	 * @param {object} optional; temporary data stored for use in deeper evaluations, used for some context dependencies 
	 */
	function walk(dat, def, path, value, sudo, extra) 
	{
		var setter, token, result, tdat, tdef, k, token2, tdat2, di, token3;
		setter = typeof value === "string";
		token = path.shift();
		if (!def) 
		{
			return setReturn(401, 'Unknown element: ' + token, setter ? 'false' : '');
		}

		tdat = dat[token];
		tdef = def[token];
		if (!tdef) 
		{
			return setReturn(401, 'Unknown element: ' + token, setter ? 'false' : '');
		}
		
		if (tdef.type == Function) // adl.nav.request.choice ... target=blabla 
		{
			token2 = path.shift();
			result = tdef.children.type.getValue(token2, tdef.children);
			return setReturn(0, '', result);
		}
		if (path[0] && path[0].charAt(0)==="_") 
		{
			if (path.length>1) 
			{
				return setReturn(401, 'Unknown element', setter ? 'false' : '');
			}
			if (setter) 
			{
				return setReturn(404, 'read only', 'false');
			}
			if ('_children' === path[0]) 
			{
				if (!tdef.children) 
				{
					return setReturn(301, 'Data model element does not have children', '');
				}
				result = []; 
				for (k in tdef.children) 
				{
					if(k.lastIndexOf("_SetBySco") == -1)
					result.push(k);
				}  
				return setReturn(0, '', result.join(","));
			}
			
			if ('_count' === path[0]) 
			{
				return tdef.type !== Array ? 
					setReturn(301, 'Data model element cannot have count', '') :
					setReturn(0, '', (tdat && tdat.length ? tdat.length : 0).toString());
			}
	
			if (token==="cmi" && '_version' === path[0]) 
			{
				return setReturn(0, '', '1.0');
			}
		}

		if (tdef.type == Array) // checks two tokens in one step e.g. "interactions" and "1"
		{
			token2 = path.shift() || "";
			var m = token2.match(/^([^\d]*)(0|[1-9]\d{0,8})$/);
			if (token2.length===0 || m && m[1]) 
			{
				return setReturn(401, 'Index expected', setter ? 'false' : '');
			} 
			else if (!m) 
			{
				return setReturn(setter ? 351 : 301, 'Index not an integer', setter ? 'false' : '');
			}
			token2 = Number(token2);
			tdat = tdat ? tdat : new Array();
			tdat2 = tdat[token2];
			token3 = path[0] || null;
			//alert(tdat.length+ "compared to:"+token2);
		
			if (setter)
			{
				if (token == "data" && token2 >= tdat.length && 
				 	(token3 == "store" || token3 == "id")) //adl.data special case
				{
					return setReturn(351, 'Index out of bounds', 'false');
				}
				if (token2 > tdat.length) 
				{
					return setReturn(351, 'Data model element collection set out of order', 'false');
				}
				if (tdef.maxOccur && token2+1 > tdef.maxOccur) 
				{
					if (config.checkSetValues) 
						return setReturn(301, '', 'false');
					else toleratedFailure=true;
				}
				if (tdat2 === undefined) 
				{
					tdat2 = new Object();
				}
				extra.index = token2;
				extra.parent.push(dat);
				if (tdef.unique===token3)
				{
					for (di=tdat.length; di--;) 
					{
						if (tdat[di][tdef.unique]===value) 
						{
							if (di!==token2) 
							{
								if (config.checkSetValues) {
									extra.error = {code: 351, diagnostic: "The data model element's value is already in use and is not unique"};
									break;
								}
								else toleratedFailure=true;
							}
						}
					}
				}
				result = walk(tdat2, tdef.children, path, value, sudo, extra);
				if (!error) 
				{
					tdat[token2] = tdat2;
					dat[token] = tdat;
				}
				return result;
			}
			else if (tdat2)
			{
				return walk(tdat2, tdef.children, path, value, sudo, extra);
			}
			else
			{
				if (config.checkSetValues)
					return setReturn(301, 'Data Model Collection Element Request Out Of Range', '');
				else toleratedFailure=true;
			}
		}
		
		if (tdef.type == Object)
		{
			if (typeof tdat === "undefined")
			{
				if (setter)
				{
					tdat = new Object();
					extra.parent.push(dat);
					result = walk(tdat, tdef.children, path, value, sudo, extra);
					if (!error) 
					{
						dat[token] = tdat;
					}
					return result;
				}
				else
				{
					return setReturn(tdef.children[path.pop()] ? 403 : 401, 'Not inited or defined: ' + token, '');
				}
			}
			else
			{
				if (setter) {
					extra.parent.push(dat);
				}
				return walk(tdat, tdef.children, path, value, sudo, extra);
			}
		}

		if (setter)
		{	
			if(token == "store" && dat["writeable"] != undefined && dat["writeable"] == 0) {
				return setReturn(404, 'readonly: ' + token, 'false');
			} 
			if (tdef.permission === READONLY && !sudo) 
			{
				return setReturn(404, 'readonly:' + token, 'false');
			}
			if (tdef.writeOnce && dat[token] && dat[token]!=value) 
			{
				if (config.checkSetValues)
					return setReturn(351, 'write only once', 'false');
				else toleratedFailure=true;
			}
			if (path.length)  
			{ 
				return setReturn(401, 'Unknown element', 'false');
			}
			if (tdef.dependsOn) {
				extra.parent.push(dat);
				var dep = tdef.dependsOn.split(" ");
				for (di=dep.length; di--;) 
				{
					var dj = extra.parent.length-1;
					var dp = dep[di].split(".");
					var dpar = extra.parent;
					if (dpar[dpar.length-dp.length][dp.pop()]===undefined)
					{
						return setReturn(408, 'dependency on ..' + dep[di], 'false');
					}
				}
			}
			result = tdef.type.isValid(value, tdef, extra);
			if (extra.error) 
			{
				if (config.checkSetValues) 
					return setReturn(extra.error.code, extra.error.diagnostic, 'false');
				else toleratedFailure=true;
			}
			if (!result) 
			{
				if (token=="session_time") {
					config.time_from_lms=true;
					fixedFailure=true;
				}
				if (config.checkSetValues)
					return setReturn(406, 'value not valid', 'false');
				else toleratedFailure=true;
			}
			
			if (value.indexOf("{order_matters")==0)
			{
				window.order_matters = true;
			} 

			dat[token] = value;
			dirty = true;
			return setReturn(0, '', 'true');
		}
		else // getter
		{	
			if(token == "store" && dat["readable"] != undefined && dat["readable"] == 0) {
				return setReturn(405, 'writeonly: ' + token, '');
			} 
			if (tdef.permission === WRITEONLY && !sudo) 
			{
				return setReturn(405, 'writeonly:' + token, '');
			}
			else if (path.length)  
			{ 
				return setReturn(401, 'Unknown element', '');
			}
			else if (tdef.getValueOf) 
			{
//				return setReturn(0, '', tdef.getValueOf(tdef, tdat));
				result = setReturn(0, '', tdef.getValueOf(tdef, tdat));
				if(result.error) {
					return setReturn(result.error, '', '');
				} else {
					return setReturn(0, '', result);
				}
			}
			else if (tdat===undefined || tdat===null)
			{
				if (tdef['default']) 
				{
					return setReturn(0, '', tdef['default']);
				}
				else
				{
					return setReturn(403, 'not initialized ' + token, '');
				}
			} 
			else
			{
				if (window.order_matters) 
				{
					window.order_matters = false;
				} 
				return setReturn(0, '', String(tdat));
			}
		}
	}
	
	/**
	 *	@access private
	 *	@param {number}
	 *	@param {string}  
	 *	@param {string}  
	 *	@return {string} 
	 */	 
	function setReturn(errCode, errInfo, returnValue) 
	{
		if (errCode>-1) 
		{
			top.status = [(new Date()).toLocaleTimeString(), errCode, errInfo].join(", ");
		}
		error = errCode;
		diagnostic = (typeof(errInfo)=='string') ? errInfo : '';
		return returnValue;
	}

	/**
	 * useful for transmitting Milliseconds if logActive
	 */
	function getMsecSinceStart()
	{
		return currentTime()-msec;
	}

	// private constants: API states
	var NOT_INITIALIZED = 0;
	var RUNNING = 1;
	var TERMINATED = 2;

	// private constants: permission
	var READONLY  = 1;
	var WRITEONLY = 2;
	var READWRITE = 3;

	// private properties
	var state = NOT_INITIALIZED;
	var error = 0;
	var diagnostic = '';
	var dirty = false;
	var msec = currentTime(); // if session time not set by sco, msec will used as starting time in onterminate
	var me = this; // reference to API for use in methods
	var commitByTerminate=false; //when commit ist startet by terminate, then do not send log if logActive
	var total_time_at_initialize; //to can store total_time with each commit
	
	// possible public methods
	var methods = 
	{
		'Initialize' : Initialize,
		'Terminate' : Terminate,
		'GetValue' : GetValue,
		'GetValueIntern' : GetValueIntern,		
		'SetValue' : SetValue,
		'SetValueIntern' : SetValueIntern,
		'Commit' : Commit,
		'GetLastError' : GetLastError,
		'GetErrorString' : GetErrorString,
		'GetDiagnostic' : GetDiagnostic
	};
		
	// bind public methods 
	for (var k in Runtime.methods) 
	{
		me[k] = methods[k];
	}
	
}

Runtime.prototype.version = "1.0";

Runtime.apiname = "API_1484_11";

Runtime.errors = 
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
};

Runtime.methods = 
{
	'Initialize' : 'Initialize', 
	'Terminate' : 'Terminate', 
	'GetValue' : 'GetValue', 
	'GetValueIntern' : 'GetValueIntern', 
	'SetValue' : 'SetValue', 
	'SetValueIntern' : 'SetValueIntern',
	'Commit' : 'Commit', 
	'GetLastError' : 'GetLastError', 
	'GetErrorString' : 'GetErrorString', 
	'GetDiagnostic' : 'GetDiagnostic'
};

Runtime.models = 
{
	'cmi' : new function() { // implements API_1484_11

		// private constants: permission
		var READONLY  = 1;
		var WRITEONLY = 2;
		var READWRITE = 3;
		
		function getDelimiter (str, typ, extra) 
		{
			var redelim = new RegExp("^({(" + typ + ")=([^}]*)})?([\\s\\S]*)$");
			var rebool = /^(true|false)$/;
			var m = str.match(redelim);
			if (m[2] && (m[2]==="lang" && !LangType.isValid(m[3]) || m[2]!=="lang" && !BooleanType.isValid(m[3]))) 
			{
				extra.error = {code: 406, diagnostic: typ + ' not recognized: ' + m[3]};
			}
			return m[4]; 
		}

		var AudioCaptioningState = { isValid : function (value) {
			return (/^-1|0|1$/).test(value);
		}};
		
		var BooleanType = { isValid : function (value) {
			return (/^(true|false)$/).test(value);
		}};
		
		var CompletionState = { isValid : function (value) {
			var valueRange = {'completed':1, 'incomplete':2, 'not attempted':3, 'unknown':4};
			return valueRange[value]>0;}
		};
		
		var CreditState = { isValid : function (value) {
			var valueRange = {'credit':1, 'no-credit':2};
			return valueRange[value]>0;}
		};
		
		var EntryState = { isValid : function (value) {
			var valueRange = {'ab-initio':1, 'resume':2, '':3};
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
			return Duration.parse(value)!==null;
		}};
		
		var LangType = { isValid : function (value) { // general type
			var relang = /^(aa|ab|af|ak|sq|am|ar|an|hy|as|av|ae|ay|az|ba|bm|eu|be|bn|bh|bi|bo|bs|br|bg|my|ca|cs|ch|ce|zh|cu|cv|kw|co|cr|cy|cs|da|de|dv|nl|dz|el|en|eo|et|eu|ee|fo|fa|fj|fi|fr|fr|fy|ff|ka|de|gd|ga|gl|gv|el|gn|gu|ht|ha|he|hz|hi|ho|hr|hu|hy|ig|is|io|ii|iu|ie|ia|id|ik|is|it|jv|ja|kl|kn|ks|ka|kr|kk|km|ki|rw|ky|kv|kg|ko|kj|ku|lo|la|lv|li|ln|lt|lb|lu|lg|mk|mh|ml|mi|mr|ms|mk|mg|mt|mo|mn|mi|ms|my|na|nv|nr|nd|ng|ne|nl|nn|nb|no|ny|oc|oj|or|om|os|pa|fa|pi|pl|pt|ps|qu|rm|ro|ro|rn|ru|sg|sa|sr|hr|si|sk|sk|sl|se|sm|sn|sd|so|st|es|sq|sc|sr|ss|su|sw|sv|ty|ta|tt|te|tg|tl|th|bo|ti|to|tn|ts|tk|tr|tw|ug|uk|ur|uz|ve|vi|vo|cy|wa|wo|xh|yi|yo|za|zh|zu|aar|abk|ace|ach|ada|ady|afa|afh|afr|ain|aka|akk|alb|ale|alg|alt|amh|ang|anp|apa|ara|arc|arg|arm|arn|arp|art|arw|asm|ast|ath|aus|ava|ave|awa|aym|aze|bad|bai|bak|bal|bam|ban|baq|bas|bat|bej|bel|bem|ben|ber|bho|bih|bik|bin|bis|bla|bnt|bod|bos|bra|bre|btk|bua|bug|bul|bur|byn|cad|cai|car|cat|cau|ceb|cel|ces|cha|chb|che|chg|chi|chk|chm|chn|cho|chp|chr|chu|chv|chy|cmc|cop|cor|cos|cpe|cpf|cpp|cre|crh|crp|csb|cus|cym|cze|dak|dan|dar|day|del|den|deu|dgr|din|div|doi|dra|dsb|dua|dum|dut|dyu|dzo|efi|egy|eka|ell|elx|eng|enm|enm|epo|est|eus|ewe|ewo|fan|fao|fas|fat|fij|fil|fin|fiu|fon|fra|fre|frm|fro|frr|frs|fry|ful|fur|gaa|gay|gba|gem|geo|ger|gez|gil|gla|gle|glg|glv|gmh|goh|gon|gor|got|grb|grc|gre|grn|gsw|guj|gwi|hai|hat|hau|haw|heb|her|hil|him|hin|hit|hmn|hmo|hrv|hsb|hun|hup|hye|iba|ibo|ice|ido|iii|ijo|iku|ile|ilo|ina|inc|ind|ine|inh|ipk|ira|iro|isl|ita|jav|jbo|jpn|jpr|jrb|kaa|kab|kac|kal|kam|kan|kar|kas|kat|kau|kaw|kaz|kbd|kha|khi|khm|kho|kik|kin|kir|kmb|kok|kom|kon|kor|kos|kpe|krc|krl|kro|kru|kua|kum|kur|kut|lad|lah|lam|lao|lat|lav|lez|lim|lin|lit|lol|loz|ltz|lua|lub|lug|lui|lun|luo|lus|mac|mad|mag|mah|mai|mak|mal|man|mao|map|mar|mas|may|mdf|mdr|men|mga|mic|min|mis|mkd|mkh|mlg|mlt|mnc|mni|mno|moh|mol|mon|mos|mri|msa|mul|mun|mus|mwl|mwr|mya|myn|myv|nah|nai|nap|nau|nav|nbl|nde|ndo|nds|nep|new|nia|nic|niu|nld|nno|nob|nog|non|nor|nqo|nso|nub|nwc|nya|nym|nyn|nyo|nzi|oci|oji|ori|orm|osa|oss|ota|oto|paa|pag|pal|pam|pan|pap|pau|peo|per|phi|phn|pli|pol|pon|por|pra|pro|pus|que|raj|rap|rar|roa|roh|rom|ron|rum|run|rup|rus|sad|sag|sah|sai|sal|sam|san|sas|sat|scc|scn|sco|scr|sel|sem|sga|sgn|shn|sid|sin|sio|sit|sla|slk|slo|slv|sma|sme|smi|smj|smn|smo|sms|sna|snd|snk|sog|som|son|sot|spa|sqi|srd|srn|srp|srr|ssa|ssw|suk|sun|sus|sux|swa|swe|syr|tah|tai|tam|tat|tel|tem|ter|tet|tgk|tgl|tha|tib|tig|tir|tiv|tkl|tlh|tli|tmh|tog|ton|tpi|tsi|tsn|tso|tuk|tum|tup|tur|tut|tvl|twi|tyv|udm|uga|uig|ukr|umb|und|urd|uzb|vai|ven|vie|vol|vot|wak|wal|war|was|wel|wen|wln|wol|xal|xho|yao|yap|yid|yor|ypk|zap|zen|zha|zho|znd|zul|zun|zxx|zza|i|x)(-([a-z]{2}|[a-z0-9][-a-z0-9]{2,7}))?$/i;
			return relang.test(value);
		}};
		
		var LanguageType = { isValid : function (value) { // preferences.language
			return value==="" || LangType.isValid(value);
		}};
		
		var ShortIdentifierType = { isValid : function (value) {
			// parsable as Uri with restricted set of characters and not empty
			var reuri = /^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/; 
			var rechars = /^[-~\.\:\/\?#\[\]@\!\$&'\(\)\*+,;=\w]{1,}$/; 
			return reuri.test(value) && rechars.test(value) && value.indexOf("[.]")===-1 &&  value.indexOf("[,]")===-1;  
		}};
			
		var LocalizedString = { isValid : function (value, definition, extra) {
			var val = getDelimiter(value, 'lang', extra);
			return CharacterString.isValid(val, definition, {max : definition.max ? definition.max+20 : undefined});}
		};
		
		var ModeState = { isValid : function (value) {
			var valueRange = {'browse':1, 'normal':2, 'review':3};
			return valueRange[value]>0;}
		};
		
		var ResponseType = { isValid : function (value, definition, extra) {
			
			var val, i;
			var parents = extra.parent;
			var ispattern = !parents[parents.length-1].id;
			var correct_responses = parents[parents.length-2].correct_responses || [];
			var parent = parents[parents.length-(ispattern+1)];
			var keys = {}; // hashtable to detect violations to uniqueness
			
			// check if patterns count exceeds SPM
			// cannot be checked above, because it's dependency on type  
			if (correct_responses.length) 
			{
				if (extra.index >= {
					'true-false' : 1,
					'choice' : 10,
					'fill-in' : 5,
					'long-fill-in' : 5,
					'likert' : 1,
					'matching' : 5,
					'performance' : 5,
					'sequencing' : 5,
					'numeric' : 1,
					'other' : 1			
				}[parent.type]) 
				{
					extra.error = {code: 351, diagnostic: 'array size exceeded in ' + parent.type + ' response'};
					return false;
				}
			}
						
			switch (parent.type)
			{
			
				case 'true-false':
					return BooleanType.isValid(value);

				case 'choice':
					val = value.split("[,]");
					if (val.length>36) 
					{
						extra.error = {code: 351};
					}
					if (val.length===1 && !val[0]) 
					{
						return true;
					}
					for (i=val.length; i--;) 
					{						
						if (keys[val[i]] || !ShortIdentifierType.isValid(val[i])) 
						{
							return false;
						}
						keys[val[i]] = true;
					}
					if (correct_responses) 
					{
						for (i=correct_responses.length; i--;) 
						{
							if (extra.index!==i && correct_responses[i].pattern===value) 
							{
								extra.error = {code: 351};
							}
						}
					}
					return !extra.error;
					
				case 'fill-in':
					val = value;
					val = getDelimiter(val, 'case_matters', extra);
					val = getDelimiter(val, 'order_matters', extra);
					// the case matter delimiter may appear before or after the order matters delimiter,  so do it again
					val = getDelimiter(val, 'case_matters', extra); 
					val = val.split("[,]");
					if (val.length > 36) 
					{
						extra.error = {code: 351}; 
					}
					for (i=val.length; i--;) 
					{
						if (extra.error || !LocalizedString.isValid(val[i], {min: 0, max: 250}, extra)) 
						{
							return false;
						}
					} 						
					return true;
					
				case 'long-fill-in':
					val = getDelimiter(value, 'case_matters', extra);
					val = getDelimiter(val, 'lang', extra).data;
					return !extra.error && (/^.{0,4000}$/).test(val);
					
				case 'likert':
					return ShortIdentifierType.isValid(value); 
					
				case 'matching':
					val = value.split("[,]");
					if (val.length>36) {
						extra.error = {code: 351};
					}
					for (i=val.length; i--;) 
					{
						val[i] = val[i].split("[.]");
						if (val[i].length!==2 ||
							!ShortIdentifierType.isValid(val[i][0]) ||
							!ShortIdentifierType.isValid(val[i][1])) 
						{
							return false;
						} 
					}
					return !extra.error;
					
				case 'performance':
					val = getDelimiter(value, 'order_matters', extra);
					val = val.split("[,]");
					if (val.length>250) 
					{
						extra.error = {code: 351};
					}
					for (i=val.length; i--;) 
					{
						val[i] = val[i].split("[.]");
						if (val[i].length!==2 || val[i][0]!=="" && !ShortIdentifierType.isValid(val[i][0])) 
						{
							return false;
						} 
					}
					return !extra.error;

				case 'sequencing':
					val = value.split("[,]");
					if (val.length>36) 
					{
						extra.error = {code: 351};
					}
					for (i=val.length; i--;) 
					{
						if (!ShortIdentifierType.isValid(val[i])) 
						{
							return false;
						}
					}
					if (correct_responses) 
					{
						for (i=correct_responses.length; i--;) 
						{
							if (extra.index===i && correct_responses[i].pattern===value) 
							{
								extra.error = {code: 351};
							}
						}
					}
					return !extra.error;

				case 'numeric':
					if (!ispattern) 
					{
						return RealType.isValid(value, {}, {});
					}
					else 
					{
						val = value.split("[:]");
						val[0] = !val[0] ? Number.NEGATIVE_INFINITY :
							RealType.isValid(val[0], {}, {}) ? parseFloat(val[0]) : NaN;
						val[1] = !val[1] ? Number.POSITIVE_INFINITY : 
							RealType.isValid(val[1], {}, {}) ? parseFloat(val[1]) : NaN;
						return !isNaN(val[0]) && !isNaN(val[1]) && val[0]<=val[1]; 
					}

				case 'other':
					return value.length <= 4000;
					
			} // end type switch

		}};
		
		var ResultState = { isValid : function (value) {
			var valueRange = {'correct':1, 'incorrect':2, 'unanticipated':3, 'neutral':4};
			return valueRange[value]>0 || RealType.isValid(value, {}, {});}
		};
		
		var SuccessState = { isValid : function (value) {
			var valueRange = {'passed':1, 'failed':2, 'unknown':3};
			return valueRange[value]>0;}
		};
		
		var Time = { isValid : function (value) {
			return DateTime.parse(value)!==null;
		}};
		
		var TimeLimitAction = { isValid : function (value) {
			var valueRange = {'exit,message':1, 'continue,message':2, 'exit,no message':3, 'continue,no message':4};
			return valueRange[value]>0;
		}};
		
		var Uri = { isValid : function (value, definition, extra) {
			var re_uri = /^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/;
			var re_char = /[\s]/;
			var re_urn = /^urn:[a-z0-9][-a-z-0-9]{1,31}:.+$/;
			var m = value.match(re_uri);
			return Boolean(m && m[0] && !re_char.test(m[0]) && m[0].length<=4000 && (m[2]!=="urn" || re_urn.test(m[0])));
		}};
		
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
		
		var RealType = { isValid : function (value, definition, extra) {
			var pattern = extra.pattern ?  extra.pattern : definition.pattern;
			var min = definition && typeof definition.min === "number" ? definition.min :  Number.NEGATIVE_INFINITY;
			var max = definition && typeof definition.max === "number" ? definition.max :  Number.POSITIVE_INFINITY;
			if (!(/^-?\d{0,32}(\.\d{1,32})?$/).test(value) || value == '') 
			{
				return false;
			} 
			else if (Number(value) < min || Number(value) > max) 
			{
				extra.error = {code: 407};
				return false;
			} 
			else if (pattern && !pattern.test(value)) 
			{
				return false;
			} 
			else 
			{
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
					},
					mapping : {
						name: 'comment', 
						func: function (d) {return !d.sourceIsLMS;}, 
						refunc: function (d) {return ['sourceIsLMS', 0];}}
				},
				comments_from_lms : {maxOccur: 250, type: Array, permission: READONLY, 
					children: {
						comment : {type: LocalizedString, max: 4000, permission: READONLY},
						timestamp: {type: Time, permission: READONLY},
						location : {type: CharacterString, max: 250, permission: READONLY}
					},
					mapping : {	
						name: 'comment', 
						func: function (d) {return d.sourceIsLMS;}, 
						refunc: function (d) {return ['sourceIsLMS', 1];}}
				},
				completion_status : {type: CompletionState, permission: READWRITE, 'default' : 'unknown', getValueOf : function (tdef, tdat) {
					// special case see Chap. 4.2.4.1
					var state = tdat===undefined ? tdef['default'] : String(tdat);
					var norm=currentAPI.GetValueIntern("cmi.completion_threshold");
					var score=currentAPI.GetValueIntern("cmi.progress_measure");
					if (norm) {
						norm=parseFloat(norm);
						if (norm && score) {
							score=parseFloat(score);
							if (score>=norm) {
								state = "completed";
							} else if (score<norm) {
								state = "incomplete";
							}
						} else {
							state="unknown";
						}
					}
					if (state=="undefined" || state=="" || state == null || state == "null") {
						state = "unknown";
					}
					currentAPI.SetValueIntern("cmi.completion_status",state);
					return state;
				}},
				completion_status_SetBySco : {type: BooleanType, permission: READWRITE, 'default': 'false'},
				completion_threshold : {type: RealType, min: 0, max: 1, permission: READONLY},
				credit : {type: CreditState, permission: READONLY, 'default' : 'credit'},
				entry : {type: EntryState, permission: READONLY, 'default' : 'ab-initio'},
				exit : {type: ExitState, permission: WRITEONLY, 'default' : ''},
				interactions: {maxOccur: 250, type: Array, permission: READWRITE, 
					// unique: 'id' // compare REQ_64.3.5 and REQ_100-5.3 for a funny example of inconsistent specification
					children: {
						correct_responses: {maxOccur: 250, type: Array, permission: READWRITE, 
							children: {
								pattern : {type: ResponseType, permission: READWRITE, dependsOn: '.id .type'}
							}
						},
						description:  {type: LocalizedString, max: 250, permission: READWRITE, dependsOn: 'id'},
						id: {type: Uri, max: 4000, permission: READWRITE, minOccur: 1},
						latency:  {type: Interval, permission: READWRITE, dependsOn: 'id'},
						learner_response:  {type: ResponseType, permission: READWRITE, dependsOn: 'id type'},
						objectives: {maxOccur: 250, type: Array, permission: READWRITE, unique: 'id',
							children : { 
								id : {type: Uri, max: 4000, permission: READWRITE, dependsOn: 'interactions.id'}
							}
						},
						result:  {type: ResultState, permission: READWRITE, dependsOn: 'id'},
						timestamp: {type: Time, permission: READWRITE, dependsOn: 'id'},
						type: {type: InteractionType, permission: READWRITE, dependsOn: 'id'},
						weighting:  {type: RealType, permission: READWRITE, dependsOn: 'id'}
					}
				}, 
				launch_data : {type: CharacterString, max: 4000, permission: READONLY, 'default' : ''},
				learner_id : {type: CharacterString, max: 4000, permission: READONLY, 'default' : ''},
				learner_name : {type: LocalizedString, max: 250, permission: READONLY, 'default' : ''},
				learner_preference: {type: Object, permission: READONLY, 
					children : {
						audio_level: {type: RealType, min: 0.0, permission: READWRITE, "default": '1'},
						language : {type: LanguageType, permission: READWRITE, 'default': ''}, 
						delivery_speed : {type: RealType, min: 0, permission: READWRITE, 'default': '1'}, 
						audio_captioning : {type: AudioCaptioningState, permission: READWRITE, 'default': '0'}
					},
					mapping : ['audio_level', 'language', 'delivery_speed', 'audio_captioning']
				}, 
				location : {type: CharacterString, max: 1000, permission: READWRITE, 'default' : ''},
				max_time_allowed : {type: Interval, permission: READONLY},
				mode: {type: ModeState, permission: READONLY, 'default' : 'normal'},
				objectives: {maxOccur: 100, type: Array, permission: READWRITE, unique: 'id', 
					children: {
						completion_status: {type: CompletionState, permission: READWRITE, 'default': 'unknown', dependsOn: 'id'},
						completion_status_SetBySco : {type: BooleanType, permission: READWRITE, 'default': 'false'},
						description:  {type: LocalizedString, max: 250, permission: READWRITE, dependsOn: 'id'},
						id: {type: Uri, max: 4000, permission: READWRITE, writeOnce: true},
						progress_measure : {type: RealType, min: 0, max: 1, permission: READWRITE},
						score: {type: Object, permission: READWRITE, 
							children: {
								scaled : {type: RealType, min: -1, max: 1, permission: READWRITE, dependsOn: 'objectives.id'},
								raw : {type: RealType, permission: READWRITE, dependsOn: 'objectives.id'},
								min : {type: RealType, permission: READWRITE, dependsOn: 'objectives.id'},
								max : {type: RealType, permission: READWRITE, dependsOn: 'objectives.id'}
							},
							mapping : ['scaled', 'raw', 'min', 'max']
						},
						success_status: {type: SuccessState, permission: READWRITE, 'default': 'unknown', dependsOn: 'id'},
						success_status_SetBySco : {type: BooleanType, permission: READWRITE, 'default' : 'false'}
					},
					mapping : {
						name: 'objective', 
						func: function (d) {return d.objectiveID || d.cmi_node_id;}
					}
				},
				progress_measure : {type: RealType, min: 0, max: 1, permission: READWRITE},
				scaled_passing_score : {type: RealType, min: -1, max: 1, permission: READONLY},
				score: {type: Object, permission: READWRITE, 
					children: {
						scaled : {type: RealType, min: -1, max: 1, permission: READWRITE},
						raw : {type: RealType, permission: READWRITE},
						min : {type: RealType, permission: READWRITE},
						max : {type: RealType, permission: READWRITE}
					},
					mapping : ['scaled', 'raw', 'min', 'max']
				},
				session_time : {type: Interval, permission: WRITEONLY},
				success_status : {type: SuccessState, permission: READWRITE, 'default' : 'unknown', getValueOf : function (tdef, tdat) {
					var state = tdat===undefined ? tdef['default'] : String(tdat);
					var norm=currentAPI.GetValueIntern("cmi.scaled_passing_score");
					var score=currentAPI.GetValueIntern("cmi.score.scaled");
					if (norm) {
						norm=parseFloat(norm);
						if (norm && score) {
							score=parseFloat(score);
					   		if (score>=norm) {
								state = "passed";
					  		} else if (score<norm) {
								state = "failed";
					  		} 
						} else {
							state="unknown";
						}
					}
					currentAPI.SetValueIntern("cmi.success_status",state);
					return state;
				}},
				suspend_data : {type: CharacterString, max: 64000, permission: READWRITE},
				time_limit_action : {type: TimeLimitAction, permission: READONLY, "default": "continue,no message"},
				total_time : {type: Interval, permission: READONLY, 'default' : 'PT0H0M0S'},
				//Not part of CMI, but used to determine whether the success_status has been set by the sco
				success_status_SetBySco: {type: BooleanType, permission: READWRITE, 'default': 'false'}
			} 

		};
	}, // end cmi model
	
	'adl' : new function() { // implements ADL Extensions to API_1484_11
		// private constants: permission
		var READONLY  = 1;
		var WRITEONLY = 2;
		var READWRITE = 3;
	
		
		var Uri = { isValid : function (value, definition, extra) {
			var re_uri = /^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/;
			var re_char = /[\s]/;
			var re_urn = /^urn:[a-z0-9][-a-z-0-9]{1,31}:.+$/;
			var m = value.match(re_uri);
			return Boolean(m && m[0] && !re_char.test(m[0]) && m[0].length<=4000 && (m[2]!=="urn" || re_urn.test(m[0])));
		}};
		
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
		var NavRequest = { isValid : function (value, min, max, pattern) {
			return (/^(\{target=[^\}]+\}(choice|jump)|continue|previous|exit|exitAll|abandon|abandonAll|suspendAll|_none_)$/).test(value);}
		};
		var NavState = { isValid : function (value, min, max, pattern) {
			return (/^(true|false|unknown)$/).test(value);}
		};
		var NavTarget = {
			isValid : function (value, min, max, pattern) {
				return (/^(true|false|unknown)$/).test(value);
			},
			getValue : function (param, def) {
				var m = String(param).match(/^\{target=([^\}]+)\}$/); 
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
								},
								'jump' : {type: Function, permission: READONLY,
									children : {
										type: NavTarget, permission: READONLY, 'default': 'unknown'
									}
								}
							}//end children
						}
					}
				},
				data : {type: Array, permission: READWRITE, unique: 'id', 
							children : {
						id: {type: Uri, max: 4000, permission: READONLY, writeOnce: true, minOccur: 1},
						store: {type: CharacterString, max: 64000, permission: READWRITE, dependsOn : 'id',
						 	 getValueOf : function(tdef, tdat) {
								if(tdat == '' || tdat == null || tdat === "undefined") {
									return {error: 403};
								}
								return tdat;
							 }
						 }
					}
				}
			}
		};
	}
};  // end adl model

Runtime.onTerminate = function (data, msec) /// or user walks away
{
	// added to synchronize the new data. it might update the navigation
	//syncCMIADLTree();
	
	if (all("treeView")!=null) {
		updateNav(true);
	}
};

