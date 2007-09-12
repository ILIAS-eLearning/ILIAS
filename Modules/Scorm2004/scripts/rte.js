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
 * You must not remove this notice, or any other, from this software.
 * 
 * PRELIMINARY EDITION
 * This is work in progress and therefore incomplete and buggy ...
 * 
 * Content-Type: application/x-javascript; charset=ISO-8859-1
 * Modul: ADL SCORM 1.3 Runtime CMI Data
 *  
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2007 Alfred Kohnert
 */ 

function Runtime(cmiItem, onCommit, onTerminate, onDebug) 
{
	// implementation of public methods
	
	// public error code property getter
	function GetLastError() 
	{
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
			return setReturn(201, 'GetError param must be empty string', '');
		}
		var e = Runtime.errors[param]; 
		return e && e.message ? String(e.message).substr(0,255) : '';
	}

	/**
	 * public error details getter 
	 * may be useful in debugging
	 * @param {string} required; but not evaluated in this implementation
	 * @return {string} in this implementation always info for last error if any	  
	 */	 
	function GetDiagnostic(param) 
	{
		return param + ': ' + (error ? String(diagnostic).substr(0,255) : ' no diagnostic');
	}

	/**
	 * Open connection to data provider 
	 * @access private, but also bound to 'this'
	 * @param {string} required; must be '' 
	 */	 
	function Initialize(param) 
	{
		setReturn(-1, 'Initialize(' + param + ')');
		if (param!=='') 
		{
			return setReturn(201, 'param must be empty string', 'false');
		}
		switch (state) 
		{
			case NOT_INITIALIZED:
				dirty = false;
				if (cmiItem instanceof Object) 
				{
					state = RUNNING;
					return setReturn(0, '', 'true');
				} 
				else 
				{
					return setReturn(102, '', 'false');
				}
				break;
			case RUNNING:
				return setReturn(103, '', 'false');
			case TERMINATED:
				return setReturn(104, '', 'false');
		}
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
		if (param!=='') 
		{
			return setReturn(201, 'param must be empty string', 'false');
		}
		switch (state) 
		{
			case NOT_INITIALIZED:
				return setReturn(142, '', 'false');
			case RUNNING:
				var returnValue = onCommit(cmiItem);
				if (returnValue) 
				{
					dirty = false;
					return setReturn(0, '', 'true');
				} 
				else
				{
					return setReturn(391, 'Persisting failed', 'false');
				}
				break;
			case TERMINATED:
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
			return setReturn(201, 'param must be empty string', 'false');
		}
		switch (state) 
		{
			case NOT_INITIALIZED:
				return setReturn(112, '', 'false');
			case RUNNING:
				// TODO check for possible exceptions
				// resulting in code 111 (REQ_5.3)
				Runtime.onTerminate(cmiItem, msec); // wrapup from LMS 
				setReturn(-1, 'Terminate(' + param + ') [after wrapup]');
				var returnValue = Commit(''); // save 
				state = TERMINATED;
				onTerminate(cmiItem); // callback
				return setReturn(error, '', returnValue);
			case TERMINATED:
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
				return setReturn(122, '', '');
			case RUNNING:
				if (typeof(sPath)!=='string') 
				{
					return setReturn(201, 'must be string', '');
				}
				if (sPath==='') 
				{
					return setReturn(301, 'cannot be empty string', '');
				}
				var r = getValue(sPath, false);
				//log.info("Returned:"+ r.toString());
				sclogdump("Return: "+sPath + " : "+ r);
				return error ? '' : setReturn(0, '', r); 
				// TODO wrap in TRY CATCH
			case TERMINATED:
				return setReturn(123, '', '');
		}	
	}
	
	//allows to get data even after termination
	function GetValueIntern(sPath) {
		setReturn(-1, 'GetValueIntern(' + sPath + ')');
		
		var r = getValue(sPath, false);
		sclogdump("ReturnInern: "+sPath + " : "+ r);
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

	/**
	 * Update or create data element entry 
	 * @access private, but also bound to 'this'
	 * @param {string} required; must be valid cmi element string
	 * @param {string} required; must be valid cmi element value
	 */	  
	function SetValue(sPath, sValue) 
	{
		setReturn(-1, 'SetValue(' + sPath + ', ' + sValue + ')');
		sclogdump("Set: "+sPath+" : "+sValue);
		switch (state) 
		{
			case NOT_INITIALIZED:
				return setReturn(132, '', 'false');
			case RUNNING:
				if (typeof(sPath)!=='string') 
				{
					return setReturn(201, 'must be string', 'false');
				}
				if (sPath==='') 
				{
					return setReturn(351, 'Param 1 cannot be empty string', 'false');
				}
				// we do not test datatype for there are to many scorm editors out there
				// that do send numerics in there APIWrappers that we cast input
				// as if we were an applet or object implementation
				// so we fix these errors here
				if (typeof sValue === "number") 
				{
					sValue = sValue.toFixed(3);
				}
				else 
				{
					sValue = String(sValue);
				}
				try 
				{
					var r = setValue(sPath, sValue);
					return error ? 'false' : 'true'; 
				} catch (e) 
				{
					return setReturn(351, 'Exception ' + e, 'false');
				}
				break;
			case TERMINATED:
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
			return setReturn(401, 'Unknown element: ' + token, 'false');
		}

		tdat = dat[token];
		tdef = def[token];
		
		if (!tdef) 
		{
			return setReturn(401, 'Unknown element: ' + token, 'false');
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
				return setReturn(401, 'Unknown element', '');
			}
			if (setter) 
			{
				return setReturn(404, 'read only', false);
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
				return setReturn(401, 'Index expected');
			} 
			else if (!m) 
			{
				return setReturn(setter ? 351 : 301, 'Index not an integer');
			}
			token2 = Number(token2);
			tdat = tdat ? tdat : new Array();
			tdat2 = tdat[token2];
			token3 = path[0] || null;
						
			if (setter)
			{
				if (token2 > tdat.length) 
				{
					return setReturn(351, 'Data model element collection set out of order');
				}
				if (tdef.maxOccur && token2+1 > tdef.maxOccur) 
				{
					return setReturn(301, '');
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
								extra.error = {code: 351, diagnostic: "not unique"};
								break;
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
				return setReturn(301, 'Data Model Collection Element Request Out Of Range');
			}
		}
		
		if (tdef.type == Object)
		{
			if (typeof tdat === "undefined") // FF fails on  (tdat === undefined)
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
					return setReturn(tdef.children[path.pop()] ? 403 : 401, 'Not inited or defined: ' + token);
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
			if (tdef.writeOnce && dat[token] && dat[token]!=value) 
			{
				return setReturn(351, 'write only once');
			}
			if (tdef.permission === READONLY && !sudo) 
			{
				return setReturn(404, 'readonly:' + token);
			}
			if (path.length)  
			{ 
				return setReturn(401	, 'Unknown element');
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
						return setReturn(408, 'dependency on ..' + dep[di]);
					}
				}
			}
			result = tdef.type.isValid(value, tdef, extra);
			if (extra.error) 
			{
				return setReturn(extra.error.code, extra.error.diagnostic);
			}
			if (!result) 
			{
				return setReturn(406, 'value not valid');
			}
			
if (value.indexOf("{order_matters")==0)
{
	window.order_matters = true;
} 

			dat[token] = value;
			dirty = true;
			return setReturn(0, '');
		}
		else // getter
		{
			if (tdef.permission === WRITEONLY && !sudo) 
			{
				return setReturn(405, 'writeonly:' + token);
			}
			else if (path.length)  
			{ 
				return setReturn(401, 'Unknown element');
			}
			else if (tdef.getValueOf) 
			{
				return setReturn(0, '', tdef.getValueOf(tdef, tdat));
			}
			else if (tdat===undefined || tdat===null)
			{
				if (tdef['default']) 
				{
					return setReturn(0, '', tdef['default']);
				}
				else
				{
					return setReturn(403, 'not initialized ' + token);
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
	
	// possible public methods
	var methods = 
	{
		'Initialize' : Initialize,
		'Terminate' : Terminate,
		'GetValue' : GetValue,
		'GetValueIntern' : GetValueIntern,		
		'SetValue' : SetValue,
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
			if (!(/^-?\d{1,32}(\.\d{1,32})?$/).test(value)) 
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
					var norm  = currentAPI.GetValue("cmi.completion_threshold");
					var score = currentAPI.GetValue("cmi.progress_measure");
					if (norm==="") {
					} else if (score==="") {
						state = "unknown";
					} else if (Number(score) < Number(norm)) {
						state = "incomplete";
					} else {
						state = "completed";
					}
					currentAPI.SetValue('cmi.completion_status', state);
					return state;
				}},
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
						success_status: {type: SuccessState, permission: READWRITE, 'default': 'unknown', dependsOn: 'id'}
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
					var norm=currentAPI.GetValue("cmi.scaled_passing_score");
					var score=currentAPI.GetValue("cmi.score");
					if (norm && score) {
						score=Number(score);
						norm=Number(norm);
					   if (score>=norm) {
						state = "passed";
					  } else if (score<norm) {
						state = "failed";
					  } else {
						state = "unknown";
					  }
					} 
					currentAPI.SetValue('cmi.success_status', state);
					return state;
				}},
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
			return (/^(\{target=[^\}]+\}choice|continue|previous|exit|exitAll|abandon|abandonAll|suspendAll|_none_)$/).test(value);}
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
								}
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
	var credit = data.cmi.credit==="credit";
	var normal = !data.cmi.mode || data.cmi.mode==="normal";
	var suspended = data.cmi.exit==="suspend";
	var not_attempted = data.cmi.completion_status==="not attempted";
	var success = (/^(completed|passed|failed)$/).test(data.cmi.success_status, true);
	var session_time = new Duration(data.cmi.session_time || (currentTime() - msec));
	var total_time = new Duration(data.cmi.total_time, true);
	if (normal) 
	{
		data.cmi.session_time = session_time.toString();
		total_time.add(session_time);
		data.cmi.total_time = total_time.toString();
	}
	if (not_attempted) 
	{
		data.cmi.success_status = 'incomplete';
	}
	data.cmi.entry = (suspended ? 'resume' : '');
};

