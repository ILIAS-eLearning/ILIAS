/*extern ActiveXObject, __flash_unloadHandler, __flash_savedUnloadHandler */
/*!
 * SWFObject v1.5: Flash Player detection and embed - http://blog.deconcept.com/swfobject/
 *
 * SWFObject is (c) 2007 Geoff Stearns and is released under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 * @namespace YAHOO
 */

YAHOO.namespace("deconcept"); 
	
YAHOO.deconcept = YAHOO.deconcept || {};

if(typeof YAHOO.deconcept.util == "undefined" || !YAHOO.deconcept.util)
{
	YAHOO.deconcept.util = {};
}

if(typeof YAHOO.deconcept.SWFObjectUtil == "undefined" || !YAHOO.deconcept.SWFObjectUtil)
{
	YAHOO.deconcept.SWFObjectUtil = {};
}

YAHOO.deconcept.SWFObject = function(swf, id, w, h, ver, c, quality, xiRedirectUrl, redirectUrl, detectKey)
{
	if(!document.getElementById) { return; }
	this.DETECT_KEY = detectKey ? detectKey : 'detectflash';
	this.skipDetect = YAHOO.deconcept.util.getRequestParameter(this.DETECT_KEY);
	this.params = {};
	this.variables = {};
	this.attributes = [];
	if(swf) { this.setAttribute('swf', swf); }
	if(id) { this.setAttribute('id', id); }
	if(w) { this.setAttribute('width', w); }
	if(h) { this.setAttribute('height', h); }
	if(ver) { this.setAttribute('version', new YAHOO.deconcept.PlayerVersion(ver.toString().split("."))); }
	this.installedVer = YAHOO.deconcept.SWFObjectUtil.getPlayerVersion();
	if (!window.opera && document.all && this.installedVer.major > 7)
	{
		// only add the onunload cleanup if the Flash Player version supports External Interface and we are in IE
		YAHOO.deconcept.SWFObject.doPrepUnload = true;
	}
	if(c)
	{
		this.addParam('bgcolor', c);
	}
	var q = quality ? quality : 'high';
	this.addParam('quality', q);
	this.setAttribute('useExpressInstall', false);
	this.setAttribute('doExpressInstall', false);
	var xir = (xiRedirectUrl) ? xiRedirectUrl : window.location;
	this.setAttribute('xiRedirectUrl', xir);
	this.setAttribute('redirectUrl', '');
	if(redirectUrl)
	{
		this.setAttribute('redirectUrl', redirectUrl);
	}
};

YAHOO.deconcept.SWFObject.prototype =
{
	useExpressInstall: function(path)
	{
		this.xiSWFPath = !path ? "expressinstall.swf" : path;
		this.setAttribute('useExpressInstall', true);
	},
	setAttribute: function(name, value){
		this.attributes[name] = value;
	},
	getAttribute: function(name){
		return this.attributes[name];
	},
	addParam: function(name, value){
		this.params[name] = value;
	},
	getParams: function(){
		return this.params;
	},
	addVariable: function(name, value){
		this.variables[name] = value;
	},
	getVariable: function(name){
		return this.variables[name];
	},
	getVariables: function(){
		return this.variables;
	},
	getVariablePairs: function(){
		var variablePairs = [];
		var key;
		var variables = this.getVariables();
		for(key in variables)
		{
			if(variables.hasOwnProperty(key))
			{
				variablePairs[variablePairs.length] = YAHOO.lang.escapeHTML(key || '') +"="+ YAHOO.lang.escapeHTML(encodeURIComponent(variables[key]  || ''));
			}
		}
		return variablePairs;
	},
	getSWFHTML: function() {
		var swfNode = "";
		var params = {};
		var key = "";
		var pairs = "";
		if (navigator.plugins && navigator.mimeTypes && navigator.mimeTypes.length) { // netscape plugin architecture
			if (this.getAttribute("doExpressInstall")) {
				this.addVariable("MMplayerType", "PlugIn");
				this.setAttribute('swf', this.xiSWFPath);
			}
			swfNode = '<embed type="application/x-shockwave-flash" src="'+ YAHOO.lang.escapeHTML(this.getAttribute('swf') || '') +'" width="'+ YAHOO.lang.escapeHTML(this.getAttribute('width') || '') +'" height="'+ YAHOO.lang.escapeHTML(this.getAttribute('height') || '') +'" style="'+ YAHOO.lang.escapeHTML(this.getAttribute('style') || '') +'"';
			swfNode += ' id="'+ YAHOO.lang.escapeHTML(this.getAttribute('id') || '') +'" name="'+ YAHOO.lang.escapeHTML(this.getAttribute('id') || '') +'" ';
			params = this.getParams();
			for(key in params)
			{
				if(params.hasOwnProperty(key))
				{
					swfNode += YAHOO.lang.escapeHTML(key || '') +'="'+ YAHOO.lang.escapeHTML(params[key] || '') +'" ';
				}
			}
			pairs = this.getVariablePairs().join("&");
			if (pairs.length > 0){ swfNode += 'flashvars="'+ pairs +'"'; }
			swfNode += '/>';
		} else { // PC IE
			if (this.getAttribute("doExpressInstall")) {
				this.addVariable("MMplayerType", "ActiveX");
				this.setAttribute('swf', this.xiSWFPath);
			}
			swfNode = '<object id="'+ YAHOO.lang.escapeHTML(this.getAttribute('id') || '') +'" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="'+ YAHOO.lang.escapeHTML(this.getAttribute('width') || '') +'" height="'+ YAHOO.lang.escapeHTML(this.getAttribute('height') || '') +'" style="'+ YAHOO.lang.escapeHTML(this.getAttribute('style') || '') +'">';
			swfNode += '<param name="movie" value="'+ YAHOO.lang.escapeHTML(this.getAttribute('swf') || '') +'" />';
			params = this.getParams();
			for(key in params)
			{
				if(params.hasOwnProperty(key))
				{
					swfNode += '<param name="'+ YAHOO.lang.escapeHTML(key || '') +'" value="'+ YAHOO.lang.escapeHTML(params[key] || '') +'" />';
				}
			}
			pairs = this.getVariablePairs().join("&");
			if(pairs.length > 0) {swfNode += '<param name="flashvars" value="'+ pairs +'" />';}
			swfNode += "</object>";
		}
		return swfNode;
	},
	write: function(elementId)
	{
		if(this.getAttribute('useExpressInstall')) {
			// check to see if we need to do an express install
			var expressInstallReqVer = new YAHOO.deconcept.PlayerVersion([6,0,65]);
			if (this.installedVer.versionIsValid(expressInstallReqVer) && !this.installedVer.versionIsValid(this.getAttribute('version'))) {
				this.setAttribute('doExpressInstall', true);
				this.addVariable("MMredirectURL", escape(this.getAttribute('xiRedirectUrl')));
				document.title = document.title.slice(0, 47) + " - Flash Player Installation";
				this.addVariable("MMdoctitle", document.title);
			}
		}
		if(this.skipDetect || this.getAttribute('doExpressInstall') || this.installedVer.versionIsValid(this.getAttribute('version')))
		{
			var n = (typeof elementId == 'string') ? document.getElementById(elementId) : elementId;
			n.innerHTML = this.getSWFHTML();
			return true;
		}
		else
		{
			if(this.getAttribute('redirectUrl') !== "")
			{
				document.location.replace(this.getAttribute('redirectUrl'));
			}
		}
		return false;
	}
};

/* ---- detection functions ---- */
YAHOO.deconcept.SWFObjectUtil.getPlayerVersion = function()
{
	var axo = null;
	var PlayerVersion = new YAHOO.deconcept.PlayerVersion([0,0,0]);
	if(navigator.plugins && navigator.mimeTypes.length)
	{
		var x = navigator.plugins["Shockwave Flash"];
		if(x && x.description)
		{
			PlayerVersion = new YAHOO.deconcept.PlayerVersion(x.description.replace(/([a-zA-Z]|\s)+/, "").replace(/(\s+r|\s+b[0-9]+)/, ".").split("."));
		}
	}
	else if (navigator.userAgent && navigator.userAgent.indexOf("Windows CE") >= 0)
	{ // if Windows CE
		var counter = 3;
		while(axo)
		{
			try
			{
				counter++;
				axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash."+ counter);
//				document.write("player v: "+ counter);
				PlayerVersion = new YAHOO.deconcept.PlayerVersion([counter,0,0]);
			}
			catch(e)
			{
				axo = null;
			}
		}
	}
	else
	{ // Win IE (non mobile)
		// do minor version lookup in IE, but avoid fp6 crashing issues
		// see http://blog.deconcept.com/2006/01/11/getvariable-setvariable-crash-internet-explorer-flash-6/
		try
		{
			axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");
		}
		catch(e)
		{
			try
			{
				axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");
				PlayerVersion = new YAHOO.deconcept.PlayerVersion([6,0,21]);
				axo.AllowScriptAccess = "always"; // error if player version < 6.0.47 (thanks to Michael Williams @ Adobe for this code)
			}
			catch(e)
			{
				if(PlayerVersion.major == 6)
				{
					return PlayerVersion;
				}
			}
			try
			{
				axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
			}
			catch(e) {}
		}
		
		if(axo !== null)
		{
			PlayerVersion = new YAHOO.deconcept.PlayerVersion(axo.GetVariable("$version").split(" ")[1].split(","));
		}
	}
	return PlayerVersion;
};

YAHOO.deconcept.PlayerVersion = function(arrVersion)
{
	this.major = arrVersion[0] !== null ? parseInt(arrVersion[0], 0) : 0;
	this.minor = arrVersion[1] !== null ? parseInt(arrVersion[1], 0) : 0;
	this.rev = arrVersion[2] !== null ? parseInt(arrVersion[2], 0) : 0;
};

YAHOO.deconcept.PlayerVersion.prototype.versionIsValid = function(fv)
{
	if(this.major < fv.major)
	{
		return false;
	}
	if(this.major > fv.major)
	{
		return true;
	}
	if(this.minor < fv.minor)
	{
		return false;
	}
	if(this.minor > fv.minor)
	{
		return true;
	}
	if(this.rev < fv.rev)
	{
		return false;
	}
	return true;
};

/* ---- get value of query string param ---- */
YAHOO.deconcept.util =
{
	getRequestParameter: function(param)
	{
		var q = document.location.search || document.location.hash;
		if(param === null) { return q; }
		if(q)
		{
			var pairs = q.substring(1).split("&");
			for(var i=0; i < pairs.length; i++)
			{
				if (pairs[i].substring(0, pairs[i].indexOf("=")) == param)
				{
					return pairs[i].substring((pairs[i].indexOf("=") + 1));
				}
			}
		}
		return "";
	}
};

/* fix for video streaming bug */
YAHOO.deconcept.SWFObjectUtil.cleanupSWFs = function()
{
	var objects = document.getElementsByTagName("OBJECT");
	for(var i = objects.length - 1; i >= 0; i--)
	{
		objects[i].style.display = 'none';
		for(var x in objects[i])
		{
			if(typeof objects[i][x] == 'function')
			{
				objects[i][x] = function(){};
			}
		}
	}
};

// fixes bug in some fp9 versions see http://blog.deconcept.com/2006/07/28/swfobject-143-released/
if(YAHOO.deconcept.SWFObject.doPrepUnload)
{
	if(!YAHOO.deconcept.unloadSet)
	{
		YAHOO.deconcept.SWFObjectUtil.prepUnload = function()
		{
			__flash_unloadHandler = function(){};
			__flash_savedUnloadHandler = function(){};
			window.attachEvent("onunload", YAHOO.deconcept.SWFObjectUtil.cleanupSWFs);
		};
		window.attachEvent("onbeforeunload", YAHOO.deconcept.SWFObjectUtil.prepUnload);
		YAHOO.deconcept.unloadSet = true;
	}
}

/* add document.getElementById if needed (mobile IE < 5) */
if(!document.getElementById && document.all)
{
	document.getElementById = function(id) { return document.all[id]; };
}
