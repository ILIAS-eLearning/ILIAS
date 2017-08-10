/* Copyright (c) 2006 Yahoo! Inc. All rights reserved. */

/**
 * @class Base class that provides the interface to the XMLHttpRequest
 * object, and responds to object requests from the
 * ConnectionManager ygConnManager.  This class is instantiated
 * directly by ygConnManager.
 * @constructor
 */
function ygConnect(){}

ygConnect.prototype =
{
  /**
   * Array of  MSFT ActiveX ids for XMLHttpRequest
   * in declining version order.
   * @private
   * @type array
   */
	_msxml_progid:[
		'MSXML2.XMLHTTP.5.0',
		'MSXML2.XMLHTTP.4.0',
		'MSXML2.XMLHTTP.3.0',
		'MSXML2.XMLHTTP',
		'Microsoft.XMLHTTP'
		],

  /**
   * Object literal of XMLHttpRequest asynchronous responses grouped
   * by transaction id and response
   * @private
   * @type Object
   */
	_async_response:{},

  /**
   * Array of HTTP header(s)
   * @private
   * @type Array
   */
	_http_header:[],

 /**
  * Enables setForm() to automatically set the HTML form values
  * as the transport data if transaction is a form POST.
  * @private
  * @type boolean
  */
	_isFormPost:false,

 /**
  * Property set from setForm() that contains the form POST body.
  * @private
  * @type string
  */
	_sFormData:null,

  /**
   * Instantiates an  XMLHttpRequest object and returns
   * an object with properties for the XMLHttpRequest instance, the
   * object pool id, and the transaction id.  If getConnObject receives
   * no arguments, then the XMLHttprequest object is returned.
   * Otherwise
   * @public
   * @param integer objectId - object id
   * @param integer transactionId - transaction id
   * @return obj
   */
	getConnObject:function(objectId,transactionId)
	{
		var obj,http;
		try
		{
			// Instantiates XMLHttpRequest in non-IE browsers and assigns to http.
			http = new XMLHttpRequest();
			//  Object literal with http and id properties
			obj = { conn:http,
					   oId:objectId,
					   tId:transactionId
					};
		}
		catch(e)
		{
			for(var i=0; i<this._msxml_progid.length; ++i){
				try
				{
					// Instantiates XMLHttpRequest for IE and assign to http.
					http = new ActiveXObject(this._msxml_progid[i]);
					//  Object literal with http and id properties
					obj = { conn:http,
							   oId:objectId,
							   tId:transactionId
							};
				}
				catch(e){}
			}
		}
		finally
		{
			// If pooling is enabled, an object id will be passed to getConnObject,
			// and the object literal is returned.  Otherwise, the XMLHttpRequest
			// object is returned.
			if(http != undefined || obj != undefined){
				var connObj = arguments.length>0? obj:http;
				return connObj;
			}
			else{
				return null;
			}
		}
	},

  /**
   * Accessor to add an ActiveX id to the existing
   * xml_progid array.
   * @public
   * @param integer id
   * @return void
   */
	setProgId:function(id)
	{
		this.msxml_progid.unshift(id);
	},

  /**
   * Faciliates synchronous requests.  A synchronous transaction will block
   * all subsequent requests, regardless of pooling status nor pooling size,
   * until the transaction is completed.  The UI will also be non-responsive during
   * the transaction.
   * @public
   * @param object obj - object id and transaction id
   * @param string sMethod - HTTP transaction method
   * @param string sUri - Qualified path of resource
   * @param boolean bXml - Transaction response in plain-text or XML.
   * @param object oPostData - User-defined content
   * @return response - Response from sUri resource.
   */
	syncRequest:function(o,sMethod,sUri,bXml,oPostData)
	{
		if(!o) {
			return;
		}
		o.conn.open(sMethod,sUri,false);

		if(this._http_header.length>0)
			this.setHeader(o);

		if(this._isFormPost){
			oPostData = this._sFormData;
			this._isFormPost = false;
		}
		else if(oPostData){
			this.initHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
		}

		oPostData?o.conn.send(oPostData):o.conn.send(null);

		try
		{
			if(o.conn.status == 200){
				response =  (bXml?o.conn.responseXML:o.conn.responseText);
			}
			else{
				response = this.connectException(o);
			}
		}
		catch(e)
		{
			response = this.connectException(e,o.tId);
		}
		finally
		{
			ygConnect.superclass.releaseObject(o);
			return response;
		}
	},

  /**
   * Faciliates asynchronous requests, and accepts a user-defined
   * callback.  Callback will receive the xmlhttprequest instance,
   * and the transaction id as arguments if the transaction status is
   * 200.  Otherwise, callback will receive an error object literal only.
   * If a callback is not defined, the response will be stored in
   * _async_response{}, indexed by transaction id.
   * @public
   * @param object obj - object id and transaction id
   * @param string sMethod - HTTP transaction method
   * @param string sUri - Qualified path of resource
   * @param boolean bXml - Transaction response in plain-text or XML.
   * @param object oCallback - User-defined callback
   * @param object oCallbackArgs - Callback arguments
   * @param object oPostData - User-defined content
   * @return void
   */
	asyncRequest:function(o,sMethod,sUri,bXml,oCallback,oCallbackArgs,oPostData)
	{
		if(!o){
			var queueObject =
							{
								method:sMethod,
								uri:sUri,
								isXml:bXml,
								callback:oCallback,
								argument:oCallbackArgs,
								data:oPostData
							}
			ygConnect.superclass.queueRequest(queueObject);
		}
		else{
			var self = this;
			o.conn.open(sMethod,sUri,true);
			if(oCallback){
				o.conn.onreadystatechange = function()
				{
					if(o.conn.readyState == 4){
						try
						{
							if(o.conn.status == 200){
								oCallback(o.conn,o.tId,oCallbackArgs);
							}
							else{
								var errorObj = self.connectException(o);
								oCallback(errorObj,oCallbackArgs);
							}
						}
						catch(e)
						{
							var errorObj = self.connectException(e,o.tId);
							oCallback(errorObj,oCallbackArgs);
						}
						finally
						{
							ygConnect.superclass.releaseObject(o);
						}
					}
				}
			}
			else{
				o.conn.onreadystatechange = function(){ self.stateChange(o,bXml) }
			}

			if(this._isFormPost){
				oPostData = this._sFormData;
				this._isFormPost = false;
			}
			else if(oPostData){
				this.initHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
				this.initHeader('Content-Length',1000);
			}

			if(this._http_header.length>0){
				this.setHeader(o);
			}

			oPostData?o.conn.send(encodeURI(oPostData)):o.conn.send(null);
		}
	},

  /**
   * Self-defined callback that creates a server response object
   * or error object, and adds it to _async_response{} if a
   * user-defined callback is not defined and passed to
   * asyncRequest().
   *
   * Either a response object literal, or an error object literal
   * will be stored in _async_response.  response Properties
   * include:
   *
   * tId - transaction id
   * status - 200
   * message - either responseXML or responseText
   *
   * See connectException() for error object literal properties.
   *
   * @privileged
   * @param object
   * @return void
   */
	stateChange:function(o,bXml)
	{
		var oResponse;
		switch(o.conn.readyState){
			case 4:
				try
				{
					if(o.conn.status == 200){
						oResponse = { tId:o.tId,
											status:o.conn.status,
											message:(bXml?o.conn.responseXML:o.conn.responseText)
										  }
					}
					else
						oResponse =  this.connectException(o);
				}
				catch(e)
				{
					oResponse = this.connectException(e,o.tId);
				}
				finally
				{
					this.setResponse(oResponse);
					ygConnect.superclass.releaseObject(o);
				}
			break;
		}
	},

  /**
   * Accessor to add an async transaction response
   * to _async_response{}
   * @privileged
   * @param object
   * @return void
   */
	setResponse:function(o)
	{
		this._async_response[o.tId] = o;
	},

  /**
   * Accessor to retrieve an async transaction response
   * from _async_response{} by transaction id.
   * @public
   * @param integer
   * @return object
   */
	getResponse:function(tId)
	{
		var oResponse =  this._async_response[tId];
		if(oResponse){
			delete this._async_response[tId];
			return oResponse;
		}
	},

  /**
   * Accessor that stores the HTTP headers for
   * each transaction.
   * @public
   * @param object id - object id and transaction id
   * @param string - HTTP header label
   * @param string - HTTP header value
   * @return void
   */
	initHeader:function(label,value)
	{
		var oHeader = [label,value];
		this._http_header.push(oHeader);
	},

  /**
   * Accessor that sets the stored HTTP headers
   * to the specific object for this transaction.
   * @privileged
   * @param object o
   * @return void
   */
	setHeader:function(o)
	{
		var oHeader = this._http_header;
		for(var i=0;i<oHeader.length;i++)
			o.conn.setRequestHeader(oHeader[i][0],oHeader[i][1]);
		oHeader.splice(0,oHeader.length);
	},

  /**
   * Accessor to get a specific HTTP header returned by the server.
   * @public
   * @param object obj - object id and transaction id
   * @param string - HTTP header label
   * @return string
   */
	getHeader:function(o,label)
	{
		return o.conn.getResponseHeader(label);
	},

  /**
   * Accessor to get a specific HTTP header returned by the server.
   * @public
   * @param object o - connection object
   * @return string
   */
	getAllHeaders:function(o)
	{
		return o.conn.getAllResponseHeaders();
	},

  /**
   * Accessor to enumerate the form label-values and
   * build an URI-encoded POST body.  Both syncRequest()
   * and asyncRequest() will automatically send the
   * POST body, with the HTTP header Content-Type of
   * application/x-www-form-urlencoded and UTF-8
   * @public
   * @param string - value of form name attribute
   * @return void
   */
	setForm:function(formName)
	{
		this._sFormData = '';
		var prevElName;
		var oForm = document.forms[formName];

		for (var i=0; i<oForm.elements.length; i++){
			oElement = oForm.elements[i];
			elName = oForm.elements[i].name;
			elValue = oForm.elements[i].value;
			switch (oElement.type)
			{
				case 'select-multiple':
					for(var j=0; j<oElement.options.length; j++){
						if(oElement.options[j].selected){
							this._sFormData += encodeURIComponent(elName) + '=' + encodeURIComponent(oElement.options[j].value) + '&';
						}
					}
					break;
				case 'radio':
				case 'checkbox':
					if(oElement.checked){
						this._sFormData += encodeURIComponent(elName) + '=' + encodeURIComponent(elValue) + '&';
					}
					break;
				case 'file':
				// stub case as XMLHttpRequest will not upload files,
				// rather it will read, as a string literal, the file path value.
					break;
				case undefined:
				// stub case for fieldset element which returns undefined.
					break;
				default:
					this._sFormData += encodeURIComponent(elName) + '=' + encodeURIComponent(elValue) + '&';
					break;
			}
		}
		this._sFormData = this._sFormData.substr(0, this._sFormData.length - 1);
		this._isFormPost = true;
		this.initHeader('Content-Type','application/x-www-form-urlencoded; charset=UTF-8');
	},

  /**
   * Accessor to terminate a transaction, if in progress.
   * @public
   * @param object obj - object id and transaction id
   * @return void
   */
	abort:function(o)
	{
		if(this.isCallInProgress(o)){
			o.conn.abort();
			ygConnect.superclass.releaseObject(o);
		}
	},

  /**
   * Accessor to check if a connection object
   * is being used in a transaction.
   * @public
   * @param object obj - object id and transaction id
   * @return boolean
   */
	isCallInProgress:function(o)
	{
		if(o)
			return o.conn.readyState != 4 && o.conn.readyState != 0;
	},

  /**
   * Accessor to create an exception object literal if
   * an error/exception is encountered during a
   * transaction.  Properties include:
   *
   * tId - transaction id
   * status - error code
   * message - error decription
   *
   * @privileged
   * @param object o
   * @param integer id
   * @return object
   */
	connectException:function(o,transactionId)
	{
		if(o.conn){
			return { tId:o.tId,
						status:o.conn.status,
						message:o.conn.statusText
				 	 }
		}
		else{
			return { tId:transactionId,
						status:o.name,
						message:o.message
				 	 }
		}
	}
}
/**
 * Singleton that a manages a pool of connection objects
 * and implements ygConnect to instantiate and return an id
 * to an available connection object in the object pool
 * @extends ygConnect
 * @constructor
 */
var ygConnManager = function(){}

ygConnManager.prototype =
{
  /**
   * XmlHttp object counter
   * @private
   * @type integer
   */
	_object_count:0,

  /**
   * Array of available connection object ids
   * @private
   * @type array
   */
	_available_pool:[],

  /**
   * Array of pending transactions and parameters
   * @private
   * @type array
   */
	_request_queue:[],

  /**
   * Enables connection object pooling when set to true.
   * Default setting is true.
   * @private
   * @type boolean
   */
	_enable_pool:true,

  /**
   * Sets the maximum number of connection objects that
   * can be instantiated.  This property is not applicable
   * in synchronous requests as only one connection object
   * can be in use at anytime, and released back into the
   * pool at the end of the transaction.
   * @private
   * @type integer
   */
	_max_pool_size:2,

  /**
   * Transaction counter
   * @private
   * @type integer
   */
	_transaction_id:0,

  /**
   * Creates an instance of the ygConnect class and
   * its accessors.
   * @privileged
   * @type Object
   */
	http:new ygConnect()
}

  /**
   *  Object variable to ygConnManager
   */
var ygConn = ygConnManager.prototype;

  /**
   * Establish  Conn as superclass of ygConnect
   * @private
   */
ygConnect.superclass = ygConnManager.prototype;

  /**
   * Accessor to set object pool size.
   * @privileged
   * @param integer
   * @return void
   */
ygConnManager.prototype.setPoolSize = function(i)
{
	this._max_pool_size = 2;
}

  /**
   * Accessor to disable object pooling.
   * @privileged
   * @param boolean
   * @return void
   */
ygConnManager.prototype.disablePooling = function()
{
	this._enable_pool = false;
}

  /**
   * Accessor to enable object pooling.
   * @privileged
   * @param boolean
   * @return void
   */
ygConnManager.prototype.enablePooling = function()
{
	this._enable_pool = true;
}

  /**
   * Accessor that retrieves a transaction id
   * @privileged
   * @return integer
   */
ygConnManager.prototype.getTransactionId = function()
{
	return this._transaction_id;
}

  /**
   * Accessor that increments the transaction id value
   * in _transaction_id
   * @privileged
   * @return integer
   */
ygConnManager.prototype.incrObjCount = function()
{
	this._object_count++;
}

  /**
   * Accessor that increments the transaction id value
   * in _transaction_id
   * @privileged
   * @return integer
   */
ygConnManager.prototype.incrTransactionId = function()
{
	this._transaction_id++;
}

  /**
   * Accessor to set object pool size.  If pooling is enabled -
   * it is by default - getObject() will attempt to:
   *
   * 1. create a connection object if there are none available
   * in the pool, and the maximum pool size has not been reached.
   *
   * 2. reuse an available connection object from the pool if one
   * is available at the time of the transaction request.
   *
   * 3. return null if the above criteria are valid.
   *
   * If successful, getObject() will return an object literal with the
   * following properties:
   *
   * http - the ygConnect instance
   * oId: the object id
   * tId: the unique, transaction id
   *
   * If pooling is disabled, getObject() will return just the
   * xmlHttpRequest object, if object instantiation is
   * successful.  Otherwise, it will return null.
   *
   * @public
   * @return object
   */
ygConnManager.prototype.getObject = function()
{
	var o;
	var oId;
	var tId = this.getTransactionId();

	// The try/catch/finally block is necessary to trap for IE7 Beta2
	// when the user disables native XMLHttpRequest support.
	// Any window.XMLHttpRequest references will automatically
	// throw a "Not Implemented" JavaScript error.
	if(this._enable_pool){
		try
		{		// Native XMLHttpRequest objects are created and destroyed
			// per request.
			if(window.XMLHttpRequest){
				oId = this._object_count;
				o = this.http.getConnObject(oId,tId);
				if(o){
					this.incrTransactionId();
				}
				return o;
			}
			// If XMLHttpRequest is instantiated via ActiveX, the object
			// will be stored in an object pool upon completion of the
			// transaction.
			else if(window.ActiveXObject){
				return this.getActiveXObject();
			}
		}
		// IE7 Beta 2 will throw a "Not Implemented" error if the native
		// XMLHttpRequest setting is disabled.  This catch block will
		// revert to the ActiveX branch.
		catch(e)
		{
			return this.getActiveXObject();
		}
	}
	else{
		return this.http.getConnObject();
	}
}

ygConnManager.prototype.getActiveXObject = function()
{
	var o;
	var oId;
	var tId = this.getTransactionId();

	if(this._object_count < this._max_pool_size && this._available_pool.length == 0){
		oId = this._object_count;
		o = this.http.getConnObject(oId,tId);
		if(o){
			this.incrObjCount();
			this.incrTransactionId();
		}
	}
	else if(this._available_pool.length > 0){
		// return an available object from the connection pool
		o = this.getAvailableObject();
		if(o){
			o.tId = tId;
			this.incrTransactionId();
		}
	}
	return o;
}

  /**
   * Returns the id of the first available connection object
   * id in the available object pool _available_object.
   * @privileged
   * @return integer id
   */
ygConnManager.prototype.getAvailableObject = function()
{
	 return this._available_pool.shift();
}

  /**
   * Releases connection object id back to available object
   * pool _available_object as the transaction is completed.
   * @privileged
   * @param integer id
   * @return void
   */
ygConnManager.prototype.releaseObject = function(o)
{
	try
	{
		if(window.XMLHttpRequest){
			//dereference the connection object
			o = null;
		}
		else{
			// Can't assign null to the onreadystatechange
			// event, so instead, it's bound to an empty
			// function.  This, combined with the abort()
			// of the connection after transaction will
			// mitigate memory leakage associated with
			// the ActiveX object.
			o.conn.onreadystatechange = detachStateListener;
			// Return the connection object to the pool
			// for reuse.
			this._available_pool.push(o);
		}
	}
	catch(e)
	{
		o.conn.onreadystatechange = detachStateListener;
		this._available_pool.push(o);
	}
	finally
	{
		this.checkRequestQueue();
	}
}

ygConnManager.prototype.checkRequestQueue = function()
{
	if(this._request_queue.length>0){
		var o = this.getObject();
		var q = this._request_queue.shift();
		this.http.asyncRequest(o,q.method,q.uri,q.isXml,q.callback,q.argument,q.data);
	}
}

ygConnManager.prototype.queueRequest = function(o)
{
	this._request_queue.push(o);
}

function detachStateListener(){ return null; }
