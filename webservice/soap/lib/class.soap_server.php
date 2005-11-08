<?php




/**
*
* soap_server allows the user to create a SOAP server
* that is capable of receiving messages and returning responses
*
* NOTE: WSDL functionality is experimental
*
* @author   Dietrich Ayala <dietrich@ganx4.com>
* @version  $Id$
* @access   public
*/
class soap_server extends nusoap_base {
	var $headers = array();			// HTTP headers of request
	var $request = '';				// HTTP request
	var $requestHeaders = '';		// SOAP headers from request (incomplete namespace resolution) (text)
	var $document = '';				// SOAP body request portion (incomplete namespace resolution) (text)
	var $requestSOAP = '';			// SOAP payload for request (text)
	var $methodURI = '';			// requested method namespace URI
	var $methodname = '';			// name of method requested
	var $methodparams = array();	// method parameters from request
	var $xml_encoding = '';			// character set encoding of incoming (request) messages
	var $SOAPAction = '';			// SOAP Action from request

	var $outgoing_headers = array();// HTTP headers of response
	var $response = '';				// HTTP response
	var $responseHeaders = '';		// SOAP headers for response (text)
	var $responseSOAP = '';			// SOAP payload for response (text)
	var $methodreturn = false;		// method return to place in response
	var $methodreturnisliteralxml = false;	// whether $methodreturn is a string of literal XML
	var $fault = false;				// SOAP fault for response
	var $result = 'successful';		// text indication of result (for debugging)

	var $operations = array();		// assoc array of operations => opData
	var $wsdl = false;				// wsdl instance
	var $externalWSDLURL = false;	// URL for WSDL
	var $debug_flag = false;		// whether to append debug to response as XML comment
	
	/**
	* constructor
    * the optional parameter is a path to a WSDL file that you'd like to bind the server instance to.
	*
    * @param mixed $wsdl file path or URL (string), or wsdl instance (object)
	* @access   public
	*/
	function soap_server($wsdl=false){

		// turn on debugging?
		global $debug;
		global $_REQUEST;
		global $_SERVER;
		global $HTTP_SERVER_VARS;

		if (isset($debug)) {
			$this->debug_flag = $debug;
		} else if (isset($_REQUEST['debug'])) {
			$this->debug_flag = $_REQUEST['debug'];
		} else if (isset($_SERVER['QUERY_STRING'])) {
			$qs = explode('&', $_SERVER['QUERY_STRING']);
			foreach ($qs as $v) {
				if (substr($v, 0, 6) == 'debug=') {
					$this->debug_flag = substr($v, 6);
				}
			}
		} else if (isset($HTTP_SERVER_VARS['QUERY_STRING'])) {
			$qs = explode('&', $HTTP_SERVER_VARS['QUERY_STRING']);
			foreach ($qs as $v) {
				if (substr($v, 0, 6) == 'debug=') {
					$this->debug_flag = substr($v, 6);
				}
			}
		}

		// wsdl
		if($wsdl){
			if (is_object($wsdl) && is_a($wsdl, 'wsdl')) {
				$this->wsdl = $wsdl;
				$this->externalWSDLURL = $this->wsdl->wsdl;
				$this->debug('Use existing wsdl instance from ' . $this->externalWSDLURL);
			} else {
				$this->debug('Create wsdl from ' . $wsdl);
				$this->wsdl = new wsdl($wsdl);
				$this->externalWSDLURL = $wsdl;
			}
			$this->debug("wsdl...\n" . $this->wsdl->debug_str);
			$this->wsdl->debug_str = '';
			if($err = $this->wsdl->getError()){
				die('WSDL ERROR: '.$err);
			}
		}
	}

	/**
	* processes request and returns response
	*
	* @param    string $data usually is the value of $HTTP_RAW_POST_DATA
	* @access   public
	*/
	function service($data){
		global $QUERY_STRING;
		if(isset($_SERVER['QUERY_STRING'])){
			$qs = $_SERVER['QUERY_STRING'];
		} elseif(isset($GLOBALS['QUERY_STRING'])){
			$qs = $GLOBALS['QUERY_STRING'];
		} elseif(isset($QUERY_STRING) && $QUERY_STRING != ''){
			$qs = $QUERY_STRING;
		}

		if(isset($qs) && ereg('wsdl', $qs) ){
			// This is a request for WSDL
			if($this->externalWSDLURL){
              if (strpos($this->externalWSDLURL,"://")!==false) { // assume URL
				header('Location: '.$this->externalWSDLURL);
              } else { // assume file
                header("Content-Type: text/xml\r\n");
                $fp = fopen($this->externalWSDLURL, 'r');
                fpassthru($fp);
              }
			} else {
				header("Content-Type: text/xml; charset=ISO-8859-1\r\n");
				print $this->wsdl->serialize();
			}
		} elseif($data == '' && $this->wsdl){
			// print web interface
			print $this->webDescription();
		} else {
			// handle the request
			$this->parse_request($data);
			if (! $this->fault) {
				$this->invoke_method();
			}
			if (! $this->fault) {
				$this->serialize_return();
			}
			$this->send_response();
		}
	}

	/**
	* parses HTTP request headers.
	*
	* The following fields are set by this function (when successful)
	*
	* headers
	* request
	* xml_encoding
	* SOAPAction
	*
	* @access   private
	*/
	function parse_http_headers() {
		global $HTTP_SERVER_VARS;
		global $_SERVER;

		$this->request = '';
		if(function_exists('getallheaders')){
			$this->headers = getallheaders();
			foreach($this->headers as $k=>$v){
				$this->request .= "$k: $v\r\n";
				$this->debug("$k: $v");
			}
			// get SOAPAction header
			if(isset($this->headers['SOAPAction'])){
				$this->SOAPAction = str_replace('"','',$this->headers['SOAPAction']);
			}
			// get the character encoding of the incoming request
			if(strpos($this->headers['Content-Type'],'=')){
				$enc = str_replace('"','',substr(strstr($this->headers["Content-Type"],'='),1));
				if(eregi('^(ISO-8859-1|US-ASCII|UTF-8)$',$enc)){
					$this->xml_encoding = strtoupper($enc);
				} else {
					$this->xml_encoding = 'US-ASCII';
				}
			} else {
				// should be US-ASCII, but for XML, let's be pragmatic and admit UTF-8 is most common
				$this->xml_encoding = 'UTF-8';
			}
		} elseif(isset($_SERVER) && is_array($_SERVER)){
			foreach ($_SERVER as $k => $v) {
				if (substr($k, 0, 5) == 'HTTP_') {
					$k = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($k, 5)))));
				} else {
					$k = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $k))));
				}
				if ($k == 'Soapaction') {
					// get SOAPAction header
					$k = 'SOAPAction';
					$v = str_replace('"', '', $v);
					$v = str_replace('\\', '', $v);
					$this->SOAPAction = $v;
				} else if ($k == 'Content-Type') {
					// get the character encoding of the incoming request
					if (strpos($v, '=')) {
						$enc = substr(strstr($v, '='), 1);
						$enc = str_replace('"', '', $enc);
						$enc = str_replace('\\', '', $enc);
						if (eregi('^(ISO-8859-1|US-ASCII|UTF-8)$', $enc)) {
							$this->xml_encoding = strtoupper($enc);
						} else {
							$this->xml_encoding = 'US-ASCII';
						}
					} else {
						// should be US-ASCII, but for XML, let's be pragmatic and admit UTF-8 is most common
						$this->xml_encoding = 'UTF-8';
					}
				}
				$this->headers[$k] = $v;
				$this->request .= "$k: $v\r\n";
				$this->debug("$k: $v");
			}
		} elseif (is_array($HTTP_SERVER_VARS)) {
			foreach ($HTTP_SERVER_VARS as $k => $v) {
				if (substr($k, 0, 5) == 'HTTP_') {
					$k = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($k, 5)))));
					if ($k == 'Soapaction') {
						// get SOAPAction header
						$k = 'SOAPAction';
						$v = str_replace('"', '', $v);
						$v = str_replace('\\', '', $v);
						$this->SOAPAction = $v;
					} else if ($k == 'Content-Type') {
						// get the character encoding of the incoming request
						if (strpos($v, '=')) {
							$enc = substr(strstr($v, '='), 1);
							$enc = str_replace('"', '', $enc);
							$enc = str_replace('\\', '', $enc);
							if (eregi('^(ISO-8859-1|US-ASCII|UTF-8)$', $enc)) {
								$this->xml_encoding = strtoupper($enc);
							} else {
								$this->xml_encoding = 'US-ASCII';
							}
						} else {
							// should be US-ASCII, but for XML, let's be pragmatic and admit UTF-8 is most common
							$this->xml_encoding = 'UTF-8';
						}
					}
					$this->headers[$k] = $v;
					$this->request .= "$k: $v\r\n";
					$this->debug("$k: $v");
				}
			}
		}
	}

	/**
	* parses a request
	*
	* The following fields are set by this function (when successful)
	*
	* headers
	* request
	* xml_encoding
	* SOAPAction
	* request
	* requestSOAP
	* methodURI
	* methodname
	* methodparams
	* requestHeaders
	* document
	*
	* This sets the fault field on error
	*
	* @param    string $data XML string
	* @access   private
	*/
	function parse_request($data='') {
		$this->debug('entering parse_request() on '.date('H:i Y-m-d'));
		$this->parse_http_headers();
		$this->debug('got character encoding: '.$this->xml_encoding);
		// uncompress if necessary
		if (isset($this->headers['Content-Encoding']) && $this->headers['Content-Encoding'] != '') {
			$this->debug('got content encoding: ' . $this->headers['Content-Encoding']);
			if ($this->headers['Content-Encoding'] == 'deflate' || $this->headers['Content-Encoding'] == 'gzip') {
		    	// if decoding works, use it. else assume data wasn't gzencoded
				if (function_exists('gzuncompress')) {
					if ($this->headers['Content-Encoding'] == 'deflate' && $degzdata = @gzuncompress($data)) {
						$data = $degzdata;
					} elseif ($this->headers['Content-Encoding'] == 'gzip' && $degzdata = gzinflate(substr($data, 10))) {
						$data = $degzdata;
					} else {
						$this->fault('Server', 'Errors occurred when trying to decode the data');
						return;
					}
				} else {
					$this->fault('Server', 'This Server does not support compressed data');
					return;
				}
			}
		}
		$this->request .= "\r\n".$data;
		$this->requestSOAP = $data;
		// parse response, get soap parser obj
		$parser = new soap_parser($data,$this->xml_encoding);
		// parser debug
		$this->debug("parser debug: \n".$parser->debug_str);
		// if fault occurred during message parsing
		if($err = $parser->getError()){
			$this->result = 'fault: error in msg parsing: '.$err;
			$this->fault('Server',"error in msg parsing:\n".$err);
		// else successfully parsed request into soapval object
		} else {
			// get/set methodname
			$this->methodURI = $parser->root_struct_namespace;
			$this->methodname = $parser->root_struct_name;
			$this->debug('method name: '.$this->methodname);
			$this->debug('calling parser->get_response()');
			$this->methodparams = $parser->get_response();
			// get SOAP headers
			$this->requestHeaders = $parser->getHeaders();
            // add document for doclit support
            $this->document = $parser->document;
		}
		$this->debug('leaving parse_request() on '.date('H:i Y-m-d'));
	}

	/**
	* invokes a PHP function for the requested SOAP method
	*
	* The following fields are set by this function (when successful)
	*
	* methodreturn
	*
	* Note that the PHP function that is called may also set the following
	* fields to affect the response sent to the client
	*
	* responseHeaders
	* outgoing_headers
	*
	* This sets the fault field on error
	*
	* @access   private
	*/
	function invoke_method() {
		$this->debug('entering invoke_method');
		// does method exist?
		if(!function_exists($this->methodname)){
			// "method not found" fault here
			$this->debug("method '$this->methodname' not found!");
			$this->result = 'fault: method not found';
			$this->fault('Server',"method '$this->methodname' not defined in service");
			return;
		}
		if($this->wsdl){
			if(!$this->opData = $this->wsdl->getOperationData($this->methodname)){
			//if(
		    	$this->fault('Server',"Operation '$this->methodname' is not defined in the WSDL for this service");
				return;
		    }
		    $this->debug('opData is ' . $this->varDump($this->opData));
		}
		$this->debug("method '$this->methodname' exists");
		// evaluate message, getting back parameters
		// verify that request parameters match the method's signature
		if(! $this->verify_method($this->methodname,$this->methodparams)){
			// debug
			$this->debug('ERROR: request not verified against method signature');
			$this->result = 'fault: request failed validation against method signature';
			// return fault
			$this->fault('Server',"Operation '$this->methodname' not defined in service.");
			return;
		}

		// if there are parameters to pass
        $this->debug('params var dump '.$this->varDump($this->methodparams));
		if($this->methodparams){
			$this->debug("calling '$this->methodname' with params");
			if (! function_exists('call_user_func_array')) {
				$this->debug('calling method using eval()');
				$funcCall = $this->methodname.'(';
				foreach($this->methodparams as $param) {
					$funcCall .= "\"$param\",";
				}
				$funcCall = substr($funcCall, 0, -1).')';
				$this->debug('function call:<br>'.$funcCall);
				@eval("\$this->methodreturn = $funcCall;");
			} else {
				$this->debug('calling method using call_user_func_array()');
				$this->methodreturn = call_user_func_array("$this->methodname",$this->methodparams);
			}
		} else {
			// call method w/ no parameters
			$this->debug("calling $this->methodname w/ no params");
			$m = $this->methodname;
			$this->methodreturn = @$m();
		}
        $this->debug('methodreturn var dump'.$this->varDump($this->methodreturn));
		$this->debug("leaving invoke_method: called method $this->methodname, received $this->methodreturn of type ".gettype($this->methodreturn));
	}

	/**
	* serializes the return value from a PHP function into a full SOAP Envelope
	*
	* The following fields are set by this function (when successful)
	*
	* responseSOAP
	*
	* This sets the fault field on error
	*
	* @access   private
	*/
	function serialize_return() {
		$this->debug("Entering serialize_return");
		// if we got nothing back. this might be ok (echoVoid)
		if(isset($this->methodreturn) && ($this->methodreturn != '' || is_bool($this->methodreturn))) {
			// if fault
			if(get_class($this->methodreturn) == 'soap_fault'){
				$this->debug('got a fault object from method');
				$this->fault = $this->methodreturn;
				return;
			} elseif ($this->methodreturnisliteralxml) {
				$return_val = $this->methodreturn;
			// returned value(s)
			} else {
				$this->debug('got a(n) '.gettype($this->methodreturn).' from method');
				$this->debug('serializing return value');
				if($this->wsdl){
					// weak attempt at supporting multiple output params
					if(sizeof($this->opData['output']['parts']) > 1){
				    	$opParams = $this->methodreturn;
				    } else {
				    	// TODO: is this really necessary?
				    	$opParams = array($this->methodreturn);
				    }
				    $return_val = $this->wsdl->serializeRPCParameters($this->methodname,'output',$opParams);
					if($errstr = $this->wsdl->getError()){
						$this->debug('got wsdl error: '.$errstr);
						$this->fault('Server', 'got wsdl error: '.$errstr);
						return;
					}
				} else {
				    $return_val = $this->serialize_val($this->methodreturn, 'return');
				}
			}
			$this->debug('return val: '.$this->varDump($return_val));
		} else {
			$return_val = '';
			$this->debug('got no response from method');
		}
		$this->debug('serializing response');
		if ($this->wsdl) {
			if ($this->opData['style'] == 'rpc') {
				$payload = '<ns1:'.$this->methodname.'Response xmlns:ns1="'.$this->methodURI.'">'.$return_val.'</ns1:'.$this->methodname."Response>";
			} else {
				$payload = $return_val;
			}
		} else {
			$payload = '<ns1:'.$this->methodname.'Response xmlns:ns1="'.$this->methodURI.'">'.$return_val.'</ns1:'.$this->methodname."Response>";
		}
		$this->result = 'successful';
		if($this->wsdl){
			//if($this->debug_flag){
            	$this->debug("WSDL debug data:\n".$this->wsdl->debug_str);
            //	}
			// Added: In case we use a WSDL, return a serialized env. WITH the usedNamespaces.
			$this->responseSOAP = $this->serializeEnvelope($payload,$this->responseHeaders,$this->wsdl->usedNamespaces,$this->opData['style']);
		} else {
			$this->responseSOAP = $this->serializeEnvelope($payload,$this->responseHeaders);
		}
		$this->debug("Leaving serialize_return");
	}

	/**
	* sends an HTTP response
	*
	* The following fields are set by this function (when successful)
	*
	* outgoing_headers
	* response
	*
	* @access   private
	*/
	function send_response() {
		$this->debug('Enter send_response');
		if ($this->fault) {
			$payload = $this->fault->serialize();
			$this->outgoing_headers[] = "HTTP/1.0 500 Internal Server Error";
			$this->outgoing_headers[] = "Status: 500 Internal Server Error";
		} else {
			$payload = $this->responseSOAP;
			// Some combinations of PHP+Web server allow the Status
			// to come through as a header.  Since OK is the default
			// just do nothing.
			// $this->outgoing_headers[] = "HTTP/1.0 200 OK";
			// $this->outgoing_headers[] = "Status: 200 OK";
		}
        // add debug data if in debug mode
		if(isset($this->debug_flag) && $this->debug_flag){
			while (strpos($this->debug_str, '--')) {
				$this->debug_str = str_replace('--', '- -', $this->debug_str);
			}
        	$payload .= "<!--\n" . $this->debug_str . "\n-->";
        }
		$this->outgoing_headers[] = "Server: $this->title Server v$this->version";
		ereg('\$Revisio' . 'n: ([^ ]+)', $this->revision, $rev);
		$this->outgoing_headers[] = "X-SOAP-Server: $this->title/$this->version (".$rev[1].")";
		// Let the Web server decide about this
		//$this->outgoing_headers[] = "Connection: Close\r\n";
		$this->outgoing_headers[] = "Content-Type: text/xml; charset=$this->soap_defencoding";
		//begin code to compress payload - by John
		if (strlen($payload) > 1024 && isset($this->headers) && isset($this->headers['Accept-Encoding'])) {	
		   if (strstr($this->headers['Accept-Encoding'], 'deflate')) {
				if (function_exists('gzcompress')) {
					if (isset($this->debug_flag) && $this->debug_flag) {
						$payload .= "<!-- Content being deflated -->";
					}
					$this->outgoing_headers[] = "Content-Encoding: deflate";
					$payload = gzcompress($payload);
				} else {
					if (isset($this->debug_flag) && $this->debug_flag) {
						$payload .= "<!-- Content will not be deflated: no gzcompress -->";
					}
				}
		   } else if (strstr($this->headers['Accept-Encoding'], 'gzip')) {
				if (function_exists('gzencode')) {
					if (isset($this->debug_flag) && $this->debug_flag) {
						$payload .= "<!-- Content being gzipped -->";
					}
					$this->outgoing_headers[] = "Content-Encoding: gzip";
					$payload = gzencode($payload);
				} else {
					if (isset($this->debug_flag) && $this->debug_flag) {
						$payload .= "<!-- Content will not be gzipped: no gzencode -->";
					}
				}
			}
		}
		//end code
		$this->outgoing_headers[] = "Content-Length: ".strlen($payload);
		reset($this->outgoing_headers);
		foreach($this->outgoing_headers as $hdr){
			header($hdr, false);
		}
		$this->response = join("\r\n",$this->outgoing_headers)."\r\n".$payload;
		print $payload;
	}

	/**
	* takes the value that was created by parsing the request
	* and compares to the method's signature, if available.
	*
	* @param	mixed
	* @return	boolean
	* @access   private
	*/
	function verify_method($operation,$request){
		if(isset($this->wsdl) && is_object($this->wsdl)){
			if($this->wsdl->getOperationData($operation)){
				return true;
			}
	    } elseif(isset($this->operations[$operation])){
			return true;
		}
		return false;
	}

	/**
	* add a method to the dispatch map
	*
	* @param    string $methodname
	* @param    string $in array of input values
	* @param    string $out array of output values
	* @access   public
	*/
	function add_to_map($methodname,$in,$out){
			$this->operations[$methodname] = array('name' => $methodname,'in' => $in,'out' => $out);
	}

	/**
	* register a service with the server
	*
	* @param    string $methodname
	* @param    string $in assoc array of input values: key = param name, value = param type
	* @param    string $out assoc array of output values: key = param name, value = param type
	* @param	string $namespace
	* @param	string $soapaction
	* @param	string $style optional (rpc|document)
	* @param	string $use optional (encoded|literal)
	* @param	string $documentation optional Description to include in WSDL
	* @access   public
	*/
	function register($name,$in=false,$out=false,$namespace=false,$soapaction=false,$style=false,$use=false,$documentation=''){
		if($this->externalWSDLURL){
			die('You cannot bind to an external WSDL file, and register methods outside of it! Please choose either WSDL or no WSDL.');
		}
	    if(false == $in) {
		}
		if(false == $out) {
		}
		if(false == $namespace) {
		}
		if(false == $soapaction) {
			$SERVER_NAME = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $GLOBALS['SERVER_NAME'];
			$SCRIPT_NAME = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : $GLOBALS['SCRIPT_NAME'];
			$soapaction = "http://$SERVER_NAME$SCRIPT_NAME/$name";
		}
		if(false == $style) {
			$style = "rpc";
		}
		if(false == $use) {
			$use = "encoded";
		}
		
		$this->operations[$name] = array(
	    'name' => $name,
	    'in' => $in,
	    'out' => $out,
	    'namespace' => $namespace,
	    'soapaction' => $soapaction,
	    'style' => $style);
        if($this->wsdl){
        	$this->wsdl->addOperation($name,$in,$out,$namespace,$soapaction,$style,$use,$documentation);
	    }
		return true;
	}

	/**
	* create a fault. this also acts as a flag to the server that a fault has occured.
	*
	* @param	string faultcode
	* @param	string faultstring
	* @param	string faultactor
	* @param	string faultdetail
	* @access   public
	*/
	function fault($faultcode,$faultstring,$faultactor='',$faultdetail=''){
		$this->fault = new soap_fault($faultcode,$faultactor,$faultstring,$faultdetail);
	}

    /**
    * prints html description of services
    *
    * @access private
    */
    function webDescription(){
		$b = '
		<html><head><title>NuSOAP: '.$this->wsdl->serviceName.'</title>
		<style type="text/css">
		    body    { font-family: arial; color: #000000; background-color: #ffffff; margin: 0px 0px 0px 0px; }
		    p       { font-family: arial; color: #000000; margin-top: 0px; margin-bottom: 12px; }
		    pre { background-color: silver; padding: 5px; font-family: Courier New; font-size: x-small; color: #000000;}
		    ul      { margin-top: 10px; margin-left: 20px; }
		    li      { list-style-type: none; margin-top: 10px; color: #000000; }
		    .content{
			margin-left: 0px; padding-bottom: 2em; }
		    .nav {
			padding-top: 10px; padding-bottom: 10px; padding-left: 15px; font-size: .70em;
			margin-top: 10px; margin-left: 0px; color: #000000;
			background-color: #ccccff; width: 20%; margin-left: 20px; margin-top: 20px; }
		    .title {
			font-family: arial; font-size: 26px; color: #ffffff;
			background-color: #999999; width: 105%; margin-left: 0px;
			padding-top: 10px; padding-bottom: 10px; padding-left: 15px;}
		    .hidden {
			position: absolute; visibility: hidden; z-index: 200; left: 250px; top: 100px;
			font-family: arial; overflow: hidden; width: 600;
			padding: 20px; font-size: 10px; background-color: #999999;
			layer-background-color:#FFFFFF; }
		    a,a:active  { color: charcoal; font-weight: bold; }
		    a:visited   { color: #666666; font-weight: bold; }
		    a:hover     { color: cc3300; font-weight: bold; }
		</style>
		<script language="JavaScript" type="text/javascript">
		<!--
		// POP-UP CAPTIONS...
		function lib_bwcheck(){ //Browsercheck (needed)
		    this.ver=navigator.appVersion
		    this.agent=navigator.userAgent
		    this.dom=document.getElementById?1:0
		    this.opera5=this.agent.indexOf("Opera 5")>-1
		    this.ie5=(this.ver.indexOf("MSIE 5")>-1 && this.dom && !this.opera5)?1:0;
		    this.ie6=(this.ver.indexOf("MSIE 6")>-1 && this.dom && !this.opera5)?1:0;
		    this.ie4=(document.all && !this.dom && !this.opera5)?1:0;
		    this.ie=this.ie4||this.ie5||this.ie6
		    this.mac=this.agent.indexOf("Mac")>-1
		    this.ns6=(this.dom && parseInt(this.ver) >= 5) ?1:0;
		    this.ns4=(document.layers && !this.dom)?1:0;
		    this.bw=(this.ie6 || this.ie5 || this.ie4 || this.ns4 || this.ns6 || this.opera5)
		    return this
		}
		var bw = new lib_bwcheck()
		//Makes crossbrowser object.
		function makeObj(obj){
		    this.evnt=bw.dom? document.getElementById(obj):bw.ie4?document.all[obj]:bw.ns4?document.layers[obj]:0;
		    if(!this.evnt) return false
		    this.css=bw.dom||bw.ie4?this.evnt.style:bw.ns4?this.evnt:0;
		    this.wref=bw.dom||bw.ie4?this.evnt:bw.ns4?this.css.document:0;
		    this.writeIt=b_writeIt;
		    return this
		}
		// A unit of measure that will be added when setting the position of a layer.
		//var px = bw.ns4||window.opera?"":"px";
		function b_writeIt(text){
		    if (bw.ns4){this.wref.write(text);this.wref.close()}
		    else this.wref.innerHTML = text
		}
		//Shows the messages
		var oDesc;
		function popup(divid){
		    if(oDesc = new makeObj(divid)){
			oDesc.css.visibility = "visible"
		    }
		}
		function popout(){ // Hides message
		    if(oDesc) oDesc.css.visibility = "hidden"
		}
		//-->
		</script>
		</head>
		<body>
		<div class=content>
			<br><br>
			<div class=title>'.$this->wsdl->serviceName.'</div>
			<div class=nav>
				<p>View the <a href="'.(isset($GLOBALS['PHP_SELF']) ? $GLOBALS['PHP_SELF'] : $_SERVER['PHP_SELF']).'?wsdl">WSDL</a> for the service.
				Click on an operation name to view it&apos;s details.</p>
				<ul>';
				foreach($this->wsdl->getOperations() as $op => $data){
				    $b .= "<li><a href='#' onclick=\"popup('$op')\">$op</a></li>";
				    // create hidden div
				    $b .= "<div id='$op' class='hidden'>
				    <a href='#' onclick='popout()'><font color='#ffffff'>Close</font></a><br><br>";
				    foreach($data as $donnie => $marie){ // loop through opdata
						if($donnie == 'input' || $donnie == 'output'){ // show input/output data
						    $b .= "<font color='white'>".ucfirst($donnie).':</font><br>';
						    foreach($marie as $captain => $tenille){ // loop through data
								if($captain == 'parts'){ // loop thru parts
								    $b .= "&nbsp;&nbsp;$captain:<br>";
					                //if(is_array($tenille)){
								    	foreach($tenille as $joanie => $chachi){
											$b .= "&nbsp;&nbsp;&nbsp;&nbsp;$joanie: $chachi<br>";
								    	}
					        		//}
								} else {
								    $b .= "&nbsp;&nbsp;$captain: $tenille<br>";
								}
						    }
						} else {
						    $b .= "<font color='white'>".ucfirst($donnie).":</font> $marie<br>";
						}
				    }
					$b .= '</div>';
				}
				$b .= '
				<ul>
			</div>
		</div></body></html>';
		return $b;
    }

    /**
    * sets up wsdl object
    * this acts as a flag to enable internal WSDL generation
    *
    * @param string $serviceName, name of the service
    * @param string $namespace optional tns namespace
    * @param string $endpoint optional URL of service endpoint
    * @param string $style optional (rpc|document) WSDL style (also specified by operation)
    * @param string $transport optional SOAP transport
    * @param string $schemaTargetNamespace optional targetNamespace for service schema
    */
    function configureWSDL($serviceName,$namespace = false,$endpoint = false,$style='rpc', $transport = 'http://schemas.xmlsoap.org/soap/http', $schemaTargetNamespace = false)
    {
		$SERVER_NAME = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $GLOBALS['SERVER_NAME'];
		$SERVER_PORT = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : $GLOBALS['SERVER_PORT'];
		if ($SERVER_PORT == 80) {
			$SERVER_PORT = '';
		} else {
			$SERVER_PORT = ':' . $SERVER_PORT;
		}
		$SCRIPT_NAME = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : $GLOBALS['SCRIPT_NAME'];
        if(false == $namespace) {
            $namespace = "http://$SERVER_NAME/soap/$serviceName";
        }
        
        if(false == $endpoint) {
        	if (isset($_SERVER['HTTPS'])) {
        		$HTTPS = $_SERVER['HTTPS'];
        	} elseif (isset($GLOBALS['HTTPS'])) {
        		$HTTPS = $GLOBALS['HTTPS'];
        	} else {
        		$HTTPS = '0';
        	}
        	if ($HTTPS == '1' || $HTTPS == 'on') {
        		$SCHEME = 'https';
        	} else {
        		$SCHEME = 'http';
        	}
            $endpoint = "$SCHEME://$SERVER_NAME$SERVER_PORT$SCRIPT_NAME";
        }
        
        if(false == $schemaTargetNamespace) {
            $schemaTargetNamespace = $namespace;
        }
        
		$this->wsdl = new wsdl;
		$this->wsdl->serviceName = $serviceName;
        $this->wsdl->endpoint = $endpoint;
		$this->wsdl->namespaces['tns'] = $namespace;
		$this->wsdl->namespaces['soap'] = 'http://schemas.xmlsoap.org/wsdl/soap/';
		$this->wsdl->namespaces['wsdl'] = 'http://schemas.xmlsoap.org/wsdl/';
		if ($schemaTargetNamespace != $namespace) {
			$this->wsdl->namespaces['types'] = $schemaTargetNamespace;
		}
        $this->wsdl->schemas[$schemaTargetNamespace][0] = new xmlschema('', '', $this->wsdl->namespaces);
        $this->wsdl->schemas[$schemaTargetNamespace][0]->schemaTargetNamespace = $schemaTargetNamespace;
        $this->wsdl->schemas[$schemaTargetNamespace][0]->imports['http://schemas.xmlsoap.org/soap/encoding/'][0] = array('location' => '', 'loaded' => true);
        $this->wsdl->schemas[$schemaTargetNamespace][0]->imports['http://schemas.xmlsoap.org/wsdl/'][0] = array('location' => '', 'loaded' => true);
        $this->wsdl->bindings[$serviceName.'Binding'] = array(
        	'name'=>$serviceName.'Binding',
            'style'=>$style,
            'transport'=>$transport,
            'portType'=>$serviceName.'PortType');
        $this->wsdl->ports[$serviceName.'Port'] = array(
        	'binding'=>$serviceName.'Binding',
            'location'=>$endpoint,
            'bindingType'=>'http://schemas.xmlsoap.org/wsdl/soap/');
    }
}




?>