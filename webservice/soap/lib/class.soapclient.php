<?php




/**
*
* soapclient higher level class for easy usage.
*
* usage:
*
* // instantiate client with server info
* $soapclient = new soapclient( string path [ ,boolean wsdl] );
*
* // call method, get results
* echo $soapclient->call( string methodname [ ,array parameters] );
*
* // bye bye client
* unset($soapclient);
*
* @author   Dietrich Ayala <dietrich@ganx4.com>
* @version  $Id$
* @access   public
*/
class soapclient extends nusoap_base  {

	var $username = '';
	var $password = '';
	var $authtype = '';
	var $requestHeaders = false;	// SOAP headers in request (text)
	var $responseHeaders = '';		// SOAP headers from response (incomplete namespace resolution) (text)
	var $document = '';				// SOAP body response portion (incomplete namespace resolution) (text)
	var $endpoint;
	var $error_str = false;
    var $proxyhost = '';
    var $proxyport = '';
	var $proxyusername = '';
	var $proxypassword = '';
    var $xml_encoding = '';			// character set encoding of incoming (response) messages
	var $http_encoding = false;
	var $timeout = 0;				// HTTP connection timeout
	var $response_timeout = 30;		// HTTP response timeout
	var $endpointType = '';
	var $persistentConnection = false;
	var $defaultRpcParams = false;	// This is no longer used
	var $request = '';				// HTTP request
	var $response = '';				// HTTP response
	var $responseData = '';			// SOAP payload of response
	// toggles whether the parser decodes element content w/ utf8_decode()
    var $decode_utf8 = true;
	
	/**
	* fault related variables
	*
	* @var      fault
	* @var      faultcode
	* @var      faultstring
	* @var      faultdetail
	* @access   public
	*/
	var $fault, $faultcode, $faultstring, $faultdetail;

	/**
	* constructor
	*
	* @param    mixed $endpoint SOAP server or WSDL URL (string), or wsdl instance (object)
	* @param    bool $wsdl optional, set to true if using WSDL
	* @param	int $portName optional portName in WSDL document
	* @param    string $proxyhost
	* @param    string $proxyport
	* @param	string $proxyusername
	* @param	string $proxypassword
	* @param	integer $timeout set the connection timeout
	* @param	integer $response_timeout set the response timeout
	* @access   public
	*/
	function soapclient($endpoint,$wsdl = false,$proxyhost = false,$proxyport = false,$proxyusername = false, $proxypassword = false, $timeout = 0, $response_timeout = 30){
		$this->endpoint = $endpoint;
		$this->proxyhost = $proxyhost;
		$this->proxyport = $proxyport;
		$this->proxyusername = $proxyusername;
		$this->proxypassword = $proxypassword;
		$this->timeout = $timeout;
		$this->response_timeout = $response_timeout;

		// make values
		if($wsdl){
			$this->endpointType = 'wsdl';
			if (is_object($endpoint) && is_a($endpoint, 'wsdl')) {
				$this->wsdl = $endpoint;
				$this->endpoint = $this->wsdl->wsdl;
				$this->wsdlFile = $this->endpoint;
				$this->debug('existing wsdl instance created from ' . $this->endpoint);
			} else {
				$this->wsdlFile = $this->endpoint;
				
				// instantiate wsdl object and parse wsdl file
				$this->debug('instantiating wsdl class with doc: '.$endpoint);
				$this->wsdl =& new wsdl($this->wsdlFile,$this->proxyhost,$this->proxyport,$this->proxyusername,$this->proxypassword,$this->timeout,$this->response_timeout);
			}
			$this->debug("wsdl debug...\n".$this->wsdl->debug_str);
			$this->wsdl->debug_str = '';
			// catch errors
			if($errstr = $this->wsdl->getError()){
				$this->debug('got wsdl error: '.$errstr);
				$this->setError('wsdl error: '.$errstr);
			} elseif($this->operations = $this->wsdl->getOperations()){
				$this->debug( 'got '.count($this->operations).' operations from wsdl '.$this->wsdlFile);
			} else {
				$this->debug( 'getOperations returned false');
				$this->setError('no operations defined in the WSDL document!');
			}
		}
	}

	/**
	* calls method, returns PHP native type
	*
	* @param    string $method SOAP server URL or path
	* @param    array $params An array, associative or simple, of the parameters
	*			              for the method call, or a string that is the XML
	*			              for the call.  For rpc style, this call will
	*			              wrap the XML in a tag named after the method, as
	*			              well as the SOAP Envelope and Body.  For document
	*			              style, this will only wrap with the Envelope and Body.
	*			              IMPORTANT: when using an array with document style,
	*			              in which case there
	*                         is really one parameter, the root of the fragment
	*                         used in the call, which encloses what programmers
	*                         normally think of parameters.  A parameter array
	*                         *must* include the wrapper.
	* @param	string $namespace optional method namespace (WSDL can override)
	* @param	string $soapAction optional SOAPAction value (WSDL can override)
	* @param	boolean $headers optional array of soapval objects for headers
	* @param	boolean $rpcParams optional no longer used
	* @param	string	$style optional (rpc|document) the style to use when serializing parameters (WSDL can override)
	* @param	string	$use optional (encoded|literal) the use when serializing parameters (WSDL can override)
	* @return	mixed
	* @access   public
	*/
	function call($operation,$params=array(),$namespace='',$soapAction='',$headers=false,$rpcParams=null,$style='rpc',$use='encoded'){
		$this->operation = $operation;
		$this->fault = false;
		$this->error_str = '';
		$this->request = '';
		$this->response = '';
		$this->responseData = '';
		$this->faultstring = '';
		$this->faultcode = '';
		$this->opData = array();
		
		$this->debug("call: $operation, $params, $namespace, $soapAction, $headers, $style, $use; endpointType: $this->endpointType");
		if ($headers) {
			$this->requestHeaders = $headers;
		}
		// serialize parameters
		if($this->endpointType == 'wsdl' && $opData = $this->getOperationData($operation)){
			// use WSDL for operation
			$this->opData = $opData;
			foreach($opData as $key => $value){
				$this->debug("$key -> $value");
			}
			if (isset($opData['soapAction'])) {
				$soapAction = $opData['soapAction'];
			}
			$this->endpoint = $opData['endpoint'];
			$namespace = isset($opData['input']['namespace']) ? $opData['input']['namespace'] :	($namespace != '' ? $namespace : 'http://testuri.org');
			$style = $opData['style'];
			$use = $opData['input']['use'];
			// add ns to ns array
			if($namespace != '' && !isset($this->wsdl->namespaces[$namespace])){
				$this->wsdl->namespaces['nu'] = $namespace;
            }
            $nsPrefix = $this->wsdl->getPrefixFromNamespace($namespace);
			// serialize payload
			if (is_string($params)) {
				$this->debug("serializing param string for WSDL operation $operation");
				$payload = $params;
			} elseif (is_array($params)) {
				$this->debug("serializing param array for WSDL operation $operation");
				$payload = $this->wsdl->serializeRPCParameters($operation,'input',$params);
			} else {
				$this->debug('params must be array or string');
				$this->setError('params must be array or string');
				return false;
			}
            $usedNamespaces = $this->wsdl->usedNamespaces;
			// Partial fix for multiple encoding styles in the same function call
			$encodingStyle = 'http://schemas.xmlsoap.org/soap/encoding/';
			if (isset($opData['output']['encodingStyle']) && $encodingStyle != $opData['output']['encodingStyle']) {
				$methodEncodingStyle = ' SOAP-ENV:encodingStyle="' . $opData['output']['encodingStyle'] . '"';
			} else {
				$methodEncodingStyle = '';
			}
			$this->debug("wsdl debug: \n".$this->wsdl->debug_str);
			$this->wsdl->debug_str = '';
			if ($errstr = $this->wsdl->getError()) {
				$this->debug('got wsdl error: '.$errstr);
				$this->setError('wsdl error: '.$errstr);
				return false;
			}
		} elseif($this->endpointType == 'wsdl') {
			// operation not in WSDL
			$this->setError( 'operation '.$operation.' not present.');
			$this->debug("operation '$operation' not present.");
			$this->debug("wsdl debug: \n".$this->wsdl->debug_str);
			$this->wsdl->debug_str = '';
			return false;
		} else {
			// no WSDL
            if($namespace == ''){
            	$namespace = 'http://testuri.org';
            }
			//$this->namespaces['ns1'] = $namespace;
			$nsPrefix = 'ns1';
			// serialize 
			$payload = '';
			if (is_string($params)) {
				$this->debug("serializing param string for operation $operation");
				$payload = $params;
			} elseif (is_array($params)) {
				$this->debug("serializing param array for operation $operation");
				foreach($params as $k => $v){
					$payload .= $this->serialize_val($v,$k,false,false,false,false,$use);
				}
			} else {
				$this->debug('params must be array or string');
				$this->setError('params must be array or string');
				return false;
			}
			$usedNamespaces = array();
			$methodEncodingStyle = '';
		}
		// wrap RPC calls with method element
		if ($style == 'rpc') {
			if ($use == 'literal') {
				$this->debug("wrapping RPC request with literal method element");
				$payload = "<$operation xmlns=\"$namespace\">" . $payload . "</$operation>";
			} else {
				$this->debug("wrapping RPC request with encoded method element");
				$payload = "<$nsPrefix:$operation$methodEncodingStyle xmlns:$nsPrefix=\"$namespace\">" .
							$payload .
							"</$nsPrefix:$operation>";
			}
		}
		// serialize envelope
		$soapmsg = $this->serializeEnvelope($payload,$this->requestHeaders,$usedNamespaces,$style,$use);
		$this->debug("endpoint: $this->endpoint, soapAction: $soapAction, namespace: $namespace, style: $style, use: $use");
		$this->debug('SOAP message length: ' . strlen($soapmsg) . ' contents: ' . substr($soapmsg, 0, 1000));
		// send
		$return = $this->send($this->getHTTPBody($soapmsg),$soapAction,$this->timeout,$this->response_timeout);
		if($errstr = $this->getError()){
			$this->debug('Error: '.$errstr);
			return false;
		} else {
			$this->return = $return;
			$this->debug('sent message successfully and got a(n) '.gettype($return).' back');
			
			// fault?
			if(is_array($return) && isset($return['faultcode'])){
				$this->debug('got fault');
				$this->setError($return['faultcode'].': '.$return['faultstring']);
				$this->fault = true;
				foreach($return as $k => $v){
					$this->$k = $v;
					$this->debug("$k = $v<br>");
				}
				return $return;
			} else {
				// array of return values
				if(is_array($return)){
					// multiple 'out' parameters
					if(sizeof($return) > 1){
						return $return;
					}
					// single 'out' parameter
					return array_shift($return);
				// nothing returned (ie, echoVoid)
				} else {
					return "";
				}
			}
		}
	}

	/**
	* get available data pertaining to an operation
	*
	* @param    string $operation operation name
	* @return	array array of data pertaining to the operation
	* @access   public
	*/
	function getOperationData($operation){
		if(isset($this->operations[$operation])){
			return $this->operations[$operation];
		}
		$this->debug("No data for operation: $operation");
	}

    /**
    * send the SOAP message
    *
    * Note: if the operation has multiple return values
    * the return value of this method will be an array
    * of those values.
    *
	* @param    string $msg a SOAPx4 soapmsg object
	* @param    string $soapaction SOAPAction value
	* @param    integer $timeout set connection timeout in seconds
	* @param	integer $response_timeout set response timeout in seconds
	* @return	mixed native PHP types.
	* @access   private
	*/
	function send($msg, $soapaction = '', $timeout=0, $response_timeout=30) {
		// detect transport
		switch(true){
			// http(s)
			case ereg('^http',$this->endpoint):
				$this->debug('transporting via HTTP');
				if($this->persistentConnection == true && is_object($this->persistentConnection)){
					$http =& $this->persistentConnection;
				} else {
					$http = new soap_transport_http($this->endpoint);
					if ($this->persistentConnection) {
						$http->usePersistentConnection();
					}
				}
				$http->setContentType($this->getHTTPContentType(), $this->getHTTPContentTypeCharset());
				$http->setSOAPAction($soapaction);
				if($this->proxyhost && $this->proxyport){
					$http->setProxy($this->proxyhost,$this->proxyport,$this->proxyusername,$this->proxypassword);
				}
                if($this->username != '' && $this->password != '') {
					$http->setCredentials($this->username, $this->password, $this->authtype);
				}
				if($this->http_encoding != ''){
					$http->setEncoding($this->http_encoding);
				}
				$this->debug('sending message, length: '.strlen($msg));
				if(ereg('^http:',$this->endpoint)){
				//if(strpos($this->endpoint,'http:')){
					$this->responseData = $http->send($msg,$timeout,$response_timeout);
				} elseif(ereg('^https',$this->endpoint)){
				//} elseif(strpos($this->endpoint,'https:')){
					//if(phpversion() == '4.3.0-dev'){
						//$response = $http->send($msg,$timeout,$response_timeout);
                   		//$this->request = $http->outgoing_payload;
						//$this->response = $http->incoming_payload;
					//} else
					if (extension_loaded('curl')) {
						$this->responseData = $http->sendHTTPS($msg,$timeout,$response_timeout);
					} else {
						$this->setError('CURL Extension, or OpenSSL extension w/ PHP version >= 4.3 is required for HTTPS');
					}								
				} else {
					$this->setError('no http/s in endpoint url');
				}
				$this->request = $http->outgoing_payload;
				$this->response = $http->incoming_payload;
				$this->debug("transport debug data...\n".$http->debug_str);
				
				// save transport object if using persistent connections
				if ($this->persistentConnection) {
					$http->debug_str = '';
					if (!is_object($this->persistentConnection)) {
						$this->persistentConnection = $http;
					}
				}
				
				if($err = $http->getError()){
					$this->setError('HTTP Error: '.$err);
					return false;
				} elseif($this->getError()){
					return false;
				} else {
					$this->debug('got response, length: '. strlen($this->responseData).' type: '.$http->incoming_headers['content-type']);
					return $this->parseResponse($http->incoming_headers, $this->responseData);
				}
			break;
			default:
				$this->setError('no transport found, or selected transport is not yet supported!');
			return false;
			break;
		}
	}

	/**
	* processes SOAP message returned from server
	*
	* @param	array	$headers	The HTTP headers
	* @param	string	$data		unprocessed response data from server
	* @return	mixed	value of the message, decoded into a PHP type
	* @access   protected
	*/
    function parseResponse($headers, $data) {
		$this->debug('Entering parseResponse() for data of length ' . strlen($data) . ' and type ' . $headers['content-type']);
		if (!strstr($headers['content-type'], 'text/xml')) {
			$this->setError('Response not of type text/xml');
			return false;
		}
		if (strpos($headers['content-type'], '=')) {
			$enc = str_replace('"', '', substr(strstr($headers["content-type"], '='), 1));
			$this->debug('Got response encoding: ' . $enc);
			if(eregi('^(ISO-8859-1|US-ASCII|UTF-8)$',$enc)){
				$this->xml_encoding = strtoupper($enc);
			} else {
				$this->xml_encoding = 'US-ASCII';
			}
		} else {
			// should be US-ASCII, but for XML, let's be pragmatic and admit UTF-8 is most common
			$this->xml_encoding = 'UTF-8';
		}
		$this->debug('Use encoding: ' . $this->xml_encoding . ' when creating soap_parser');
		$parser = new soap_parser($data,$this->xml_encoding,$this->operation,$this->decode_utf8);
		// add parser debug data to our debug
		$this->debug($parser->debug_str);
		// if parse errors
		if($errstr = $parser->getError()){
			$this->setError( $errstr);
			// destroy the parser object
			unset($parser);
			return false;
		} else {
			// get SOAP headers
			$this->responseHeaders = $parser->getHeaders();
			// get decoded message
			$return = $parser->get_response();
            // add document for doclit support
            $this->document = $parser->document;
			// destroy the parser object
			unset($parser);
			// return decode message
			return $return;
		}
	 }

	/**
	* set the SOAP headers
	*
	* @param	$headers string XML
	* @access   public
	*/
	function setHeaders($headers){
		$this->requestHeaders = $headers;
	}

	/**
	* get the response headers
	*
	* @return	mixed object SOAPx4 soapval object or empty if no headers
	* @access   public
	*/
	function getHeaders(){
	    if($this->responseHeaders != '') {
			return $this->responseHeaders;
	    }
	}

	/**
	* set proxy info here
	*
	* @param    string $proxyhost
	* @param    string $proxyport
	* @param	string $proxyusername
	* @param	string $proxypassword
	* @access   public
	*/
	function setHTTPProxy($proxyhost, $proxyport, $proxyusername = '', $proxypassword = '') {
		$this->proxyhost = $proxyhost;
		$this->proxyport = $proxyport;
		$this->proxyusername = $proxyusername;
		$this->proxypassword = $proxypassword;
	}

	/**
	* if authenticating, set user credentials here
	*
	* @param    string $username
	* @param    string $password
	* @param	string $authtype (basic|digest)
	* @access   public
	*/
	function setCredentials($username, $password, $authtype = 'basic') {
		$this->username = $username;
		$this->password = $password;
		$this->authtype = $authtype;
	}
	
	/**
	* use HTTP encoding
	*
	* @param    string $enc
	* @access   public
	*/
	function setHTTPEncoding($enc='gzip, deflate'){
		$this->http_encoding = $enc;
	}
	
	/**
	* use HTTP persistent connections if possible
	*
	* @access   public
	*/
	function useHTTPPersistentConnection(){
		$this->persistentConnection = true;
	}
	
	/**
	* gets the default RPC parameter setting.
	* If true, default is that call params are like RPC even for document style.
	* Each call() can override this value.
	*
	* This is no longer used.
	*
	* @access public
	* @deprecated
	*/
	function getDefaultRpcParams() {
		return $this->defaultRpcParams;
	}

	/**
	* sets the default RPC parameter setting.
	* If true, default is that call params are like RPC even for document style
	* Each call() can override this value.
	*
	* @param    boolean $rpcParams
	* @access public
	*/
	function setDefaultRpcParams($rpcParams) {
		$this->defaultRpcParams = $rpcParams;
	}
	
	/**
	* dynamically creates proxy class, allowing user to directly call methods from wsdl
	*
	* @return   object soap_proxy object
	* @access   public
	*/
	function getProxy(){
		$evalStr = '';
		foreach($this->operations as $operation => $opData){
			if($operation != ''){
				// create param string
				$paramStr = '';
				if(sizeof($opData['input']['parts']) > 0){
					foreach($opData['input']['parts'] as $name => $type){
						$paramStr .= "\$$name,";
					}
					$paramStr = substr($paramStr,0,strlen($paramStr)-1);
				}
				$opData['namespace'] = !isset($opData['namespace']) ? 'http://testuri.com' : $opData['namespace'];
				$evalStr .= "function $operation ($paramStr){
					// load params into array
					\$params = array($paramStr);
					return \$this->call('$operation',\$params,'".$opData['namespace']."','".(isset($opData['soapAction']) ? $opData['soapAction'] : '')."');
				}";
				unset($paramStr);
			}
		}
		$r = rand();
		$evalStr = 'class soap_proxy_'.$r.' extends soapclient {
				'.$evalStr.'
			}';
		//print "proxy class:<pre>$evalStr</pre>";
		// eval the class
		eval($evalStr);
		// instantiate proxy object
		eval("\$proxy = new soap_proxy_$r('');");
		// transfer current wsdl data to the proxy thereby avoiding parsing the wsdl twice
		$proxy->endpointType = 'wsdl';
		$proxy->wsdlFile = $this->wsdlFile;
		$proxy->wsdl = $this->wsdl;
		$proxy->operations = $this->operations;
		$proxy->defaultRpcParams = $this->defaultRpcParams;
		// transfer other state
		$proxy->username = $this->username;
		$proxy->password = $this->password;
		$proxy->proxyhost = $this->proxyhost;
		$proxy->proxyport = $this->proxyport;
		$proxy->proxyusername = $this->proxyusername;
		$proxy->proxypassword = $this->proxypassword;
		$proxy->timeout = $this->timeout;
		$proxy->response_timeout = $this->response_timeout;
		$proxy->http_encoding = $this->http_encoding;
		$proxy->persistentConnection = $this->persistentConnection;
		return $proxy;
	}

	/**
	* gets the HTTP body for the current request.
	*
	* @param string $soapmsg The SOAP payload
	* @return string The HTTP body, which includes the SOAP payload
	* @access protected
	*/
	function getHTTPBody($soapmsg) {
		return $soapmsg;
	}
	
	/**
	* gets the HTTP content type for the current request.
	*
	* Note: getHTTPBody must be called before this.
	*
	* @return string the HTTP content type for the current request.
	* @access protected
	*/
	function getHTTPContentType() {
		return 'text/xml';
	}
	
	/**
	* gets the HTTP content type charset for the current request.
	* returns false for non-text content types.
	*
	* Note: getHTTPBody must be called before this.
	*
	* @return string the HTTP content type charset for the current request.
	* @access protected
	*/
	function getHTTPContentTypeCharset() {
		return $this->soap_defencoding;
	}

	/*
	* whether or not parser should decode utf8 element content
    *
    * @return   always returns true
    * @access   public
    */
    function decodeUTF8($bool){
		$this->decode_utf8 = $bool;
		return true;
    }
}
?>
