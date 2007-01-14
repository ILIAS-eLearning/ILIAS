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
 * Modul: Player Client To Server Communication Methods
 * Description: Transport javascript objects in serialized form to and from a 
 * server. Keep code base clean and small.
 *  
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2007 Alfred Kohnert
 */ 

var Remoting = new function () {

	this.toJSONString = toJSONString; // serialize javascript object into json string 
	this.parseJSONString = parseJSONString; // deserialize json string into javascript object
	this.sendAndLoad = sendAndLoad; // send data to http server and receive response per xmlhttp  
	this.sendJSONRequest = sendJSONRequest; // send js object to server and receive js result 
	this.copyOf = copyOf;

	/**
	 * recursivly copying contents into a new object of the same type
	 * ignoring functions and prototype values
	 * @param object Object to be copied
	 * @param boolean Optional parameter setting whether functions will be copyied by reference or ignored
	 * @return A copy of an object, resolving all references to literals.
	 */	
	function copyOf(obj, ref) {
		switch (typeof obj) {
		case 'object':
			var r = new obj.constructor();
			if (obj instanceof Array) { // instanceof requires MSIE5+
				for (var i=0, ni=obj.length; i<ni; i+=1) {
					r[i] = copyOf(obj[i], ref);
				}
			} else {
				for (var k in obj) {
					if (obj.hasOwnProperty(k)) {  // hasOwnProperty requires Safari?
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
	
	function sendAndLoad(url, data, callback, user, password, headers) {
	
		function HttpResponse(x) {
			this.status = Number(x.status);
			this.content = String(x.responseText);
			this.type = String(x.getResponseHeader('Content-Type'));
		}
		
		function onStateChange() {
			if (xhttp.readyState === 4) { // COMPLETED
				if (typeof callback === 'function') {
					callback(new HttpResponse(xhttp));
				} else {
					return new HttpResponse(xhttp);
				} 
			}
		}
		
		var xhttp = getXMLHttpRequest();
		if (!xhttp) throw 'XMLHttpRequest';
		var async = !!callback;
		var post = !!data; 
		xhttp.open(post ? 'POST' : 'GET', url, async, user, password);
		if (typeof headers !== 'object') {
			headers = new Object();
		}
		if (post) {
			headers['Content-Type'] = 'application/x-www-form-urlencoded';
		}
		if (headers && headers instanceof Object) {
			for (var k in headers) {
				xhttp.setRequestHeader(k, headers[k]);
			}
		}
		if (async) {
			xhttp.onreadystatechange = onStateChange;
			xhttp.send(data ? String(data) : '');				
		} else {
			xhttp.send(data ? String(data) : '');				
			return onStateChange();
		}
	}

	function sendJSONRequest (url, data, callback, user, password, headers) {		
		var r = sendAndLoad(url, toJSONString(data), callback, user, password, {
			'Accept': 'text/json', 
			'Accept-Charset' : 'UTF-8'
		});
		return ((r.status===200 && (/^text\/json;?.*/i).test(r.type)) || r.status===0) 
			? parseJSONString(r.content) 
			: null;
	}

	function getXMLHttpRequest () {
		if (window.XMLHttpRequest) {
			var xhttp = new window.XMLHttpRequest(); 
		} else if (window.ActiveXObject) {
			var progIDs = {'MSXML2.XMLHTTP' : ['.6.0', '.4.0', ''], 'Microsoft.XMLHTTP' : ''};
			try {
				for (var k in progIDs) {
					if (progIDs[k] instanceof Array) {
						for (var i=0, ni=progIDs[k].length; i<ni && !xhttp; i+=1) {
							xhttp = new ActiveXObject(k + progIDs[k][i]);
						}
					} else if (!xhttp) {
						xhttp = new ActiveXObject(k + progIDs[k]);
					}
				}
			} catch (e) {}
		}
		return xhttp;
	}
		
	function toJSONString (v) { 	
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
				return '"' + v.getFullYear() + '-' + fmt(v.getMonth() + 1) + 
					'-' + fmt(v.getDate()) + 'T' + fmt(v.getHours()) + ':' +
	            fmt(v.getMinutes()) + ':' + fmt(v.getSeconds() + 
					v.getMilliseconds()/1000).substr(0, 6) + '"';
			} else if (v instanceof Array) {
				var ra = new Array();
				for (var i=0, ni=v.length; i<ni; i+=1) {
					ra.push(toJSONString(v[i]));
				}
				return '[' + ra.join(', ') + ']';
			} else if (v.constructor && v.constructor.toString().indexOf("[")!==0) {
				// not checking the constructor would be much faster but what if 
				// native and recursive objects like "window" are considered ? 
				var ro = new Array();
				for (var k in v) {	
					if (v.hasOwnProperty && v.hasOwnProperty(k)) {
						ro.push(esc(String(k)) + ': ' + toJSONString(v[k]));
					}
				}
				return '{' + ro.join(', ') + '}';
			} else {
				return 'null';
			}
		}
	}
	
	function parseJSONString (s) {
		try {
			if (/^("(\\.|[^"\\\n\r])*?"|[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t])+?$/.test(s)) {
				return eval('(' + s + ')');
			}
		} catch (e) {}
		throw new SyntaxError('parseJSONString');
	}
	
};
