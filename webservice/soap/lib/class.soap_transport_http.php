<?php




/**
* transport class for sending/receiving data via HTTP and HTTPS
* NOTE: PHP must be compiled with the CURL extension for HTTPS support
*
* @author   Dietrich Ayala <dietrich@ganx4.com>
* @version  $Id$
* @access public
*/
class soap_transport_http extends nusoap_base {

	var $url = '';
	var $uri = '';
	var $scheme = '';
	var $host = '';
	var $port = '';
	var $path = '';
	var $request_method = 'POST';
	var $protocol_version = '1.0';
	var $encoding = '';
	var $outgoing_headers = array();
	var $incoming_headers = array();
	var $outgoing_payload = '';
	var $incoming_payload = '';
	var $useSOAPAction = true;
	var $persistentConnection = false;
	var $ch = false;	// cURL handle
	var $username;
	var $password;
	
	/**
	* constructor
	*/
	function soap_transport_http($url){
		$this->url = $url;
		
		$u = parse_url($url);
		foreach($u as $k => $v){
			$this->debug("$k = $v");
			$this->$k = $v;
		}
		
		// add any GET params to path
		if(isset($u['query']) && $u['query'] != ''){
            $this->path .= '?' . $u['query'];
		}
		
		// set default port
		if(!isset($u['port'])){
			if($u['scheme'] == 'https'){
				$this->port = 443;
			} else {
				$this->port = 80;
			}
		}
		
		$this->uri = $this->path;
		
		// build headers
		ereg('\$Revisio' . 'n: ([^ ]+)', $this->revision, $rev);
		$this->outgoing_headers['User-Agent'] = $this->title.'/'.$this->version.' ('.$rev[1].')';
		if (!isset($u['port'])) {
			$this->outgoing_headers['Host'] = $this->host;
		} else {
			$this->outgoing_headers['Host'] = $this->host.':'.$this->port;
		}
		
		if (isset($u['user']) && $u['user'] != '') {
			$this->setCredentials($u['user'], isset($u['pass']) ? $u['pass'] : '');
		}
	}
	
	function connect($connection_timeout=0,$response_timeout=30){
	  	// For PHP 4.3 with OpenSSL, change https scheme to ssl, then treat like
	  	// "regular" socket.
	  	// TODO: disabled for now because OpenSSL must be *compiled* in (not just
	  	//       loaded), and until PHP5 stream_get_wrappers is not available.
//	  	if ($this->scheme == 'https') {
//		  	if (version_compare(phpversion(), '4.3.0') >= 0) {
//		  		if (extension_loaded('openssl')) {
//		  			$this->scheme = 'ssl';
//		  			$this->debug('Using SSL over OpenSSL');
//		  		}
//		  	}
//		}
		$this->debug("connect connection_timeout $connection_timeout, response_timeout $response_timeout, scheme $this->scheme, host $this->host, port $this->port");
	  if ($this->scheme == 'http' || $this->scheme == 'ssl') {
		// use persistent connection
		if($this->persistentConnection && isset($this->fp) && is_resource($this->fp)){
			if (!feof($this->fp)) {
				$this->debug('Re-use persistent connection');
				return true;
			}
			fclose($this->fp);
			$this->debug('Closed persistent connection at EOF');
		}

		// munge host if using OpenSSL
		if ($this->scheme == 'ssl') {
			$host = 'ssl://' . $this->host;
		} else {
			$host = $this->host;
		}
		$this->debug('calling fsockopen with host ' . $host);

		// open socket
		if($connection_timeout > 0){
			$this->fp = @fsockopen( $host, $this->port, $this->errno, $this->error_str, $connection_timeout);
		} else {
			$this->fp = @fsockopen( $host, $this->port, $this->errno, $this->error_str);
		}
		
		// test pointer
		if(!$this->fp) {
			$msg = 'Couldn\'t open socket connection to server ' . $this->url;
			if ($this->errno) {
				$msg .= ', Error ('.$this->errno.'): '.$this->error_str;
			} else {
				$msg .= ' prior to connect().  This is often a problem looking up the host name.';
			}
			$this->debug($msg);
			$this->setError($msg);
			return false;
		}
		
		// set response timeout
		socket_set_timeout( $this->fp, $response_timeout);

		$this->debug('socket connected');
		return true;
	  } else if ($this->scheme == 'https') {
		if (!extension_loaded('curl')) {
			$this->setError('CURL Extension, or OpenSSL extension w/ PHP version >= 4.3 is required for HTTPS');
			return false;
		}
		$this->debug('connect using https');
		// init CURL
		$this->ch = curl_init();
		// set url
		$hostURL = ($this->port != '') ? "https://$this->host:$this->port" : "https://$this->host";
		// add path
		$hostURL .= $this->path;
		curl_setopt($this->ch, CURLOPT_URL, $hostURL);
		// ask for headers in the response output
		curl_setopt($this->ch, CURLOPT_HEADER, 1);
		// ask for the response output as the return value
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		// encode
		// We manage this ourselves through headers and encoding
//		if(function_exists('gzuncompress')){
//			curl_setopt($this->ch, CURLOPT_ENCODING, 'deflate');
//		}
		// persistent connection
		if ($this->persistentConnection) {
			// The way we send data, we cannot use persistent connections, since
			// there will be some "junk" at the end of our request.
			//curl_setopt($this->ch, CURL_HTTP_VERSION_1_1, true);
			$this->persistentConnection = false;
			$this->outgoing_headers['Connection'] = 'close';
		}
		// set timeout (NOTE: cURL does not have separate connection and response timeouts)
		if ($connection_timeout != 0) {
			curl_setopt($this->ch, CURLOPT_TIMEOUT, $connection_timeout);
		}

		// recent versions of cURL turn on peer/host checking by default,
		// while PHP binaries are not compiled with a default location for the
		// CA cert bundle, so disable peer/host checking.
//curl_setopt($this->ch, CURLOPT_CAINFO, 'f:\php-4.3.2-win32\extensions\curl-ca-bundle.crt');		
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);

		/*
			TODO: support client certificates (thanks Tobias Boes)
        curl_setopt($this->ch, CURLOPT_CAINFO, '$pathToPemFiles/rootca.pem');
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($this->ch, CURLOPT_SSLCERT, '$pathToPemFiles/mycert.pem');
        curl_setopt($this->ch, CURLOPT_SSLKEY, '$pathToPemFiles/mykey.pem');
		*/
		$this->debug('cURL connection set up');
		return true;
	  } else {
		$this->setError('Unknown scheme ' . $this->scheme);
		$this->debug('Unknown scheme ' . $this->scheme);
		return false;
	  }
	}
	
	/**
	* send the SOAP message via HTTP
	*
	* @param    string $data message data
	* @param    integer $timeout set connection timeout in seconds
	* @param	integer $response_timeout set response timeout in seconds
	* @return	string data
	* @access   public
	*/
	function send($data, $timeout=0, $response_timeout=30) {
		
		$this->debug('entered send() with data of length: '.strlen($data));

		$this->tryagain = true;
		$tries = 0;
		while ($this->tryagain) {
			$this->tryagain = false;
			if ($tries++ < 2) {
				// make connnection
				if (!$this->connect($timeout, $response_timeout)){
					return false;
				}
				
				// send request
				if (!$this->sendRequest($data)){
					return false;
				}
				
				// get response
				$respdata = $this->getResponse();
			} else {
				$this->setError('Too many tries to get an OK response');
			}
		}		
		$this->debug('end of send()');
		return $respdata;
	}


	/**
	* send the SOAP message via HTTPS 1.0 using CURL
	*
	* @param    string $msg message data
	* @param    integer $timeout set connection timeout in seconds
	* @param	integer $response_timeout set response timeout in seconds
	* @return	string data
	* @access   public
	*/
	function sendHTTPS($data, $timeout=0, $response_timeout=30) {
		return $this->send($data, $timeout, $response_timeout);
	}
	
	/**
	* if authenticating, set user credentials here
	*
	* @param    string $username
	* @param    string $password
	* @param	string $authtype
	* @param	array $digestRequest
	* @access   public
	*/
	function setCredentials($username, $password, $authtype = 'basic', $digestRequest = array()) {
		global $_SERVER;

		$this->debug("Set credentials for authtype $authtype");
		// cf. RFC 2617
		if ($authtype == 'basic') {
			$this->outgoing_headers['Authorization'] = 'Basic '.base64_encode($username.':'.$password);
		} elseif ($authtype == 'digest') {
			if (isset($digestRequest['nonce'])) {
				$digestRequest['nc'] = isset($digestRequest['nc']) ? $digestRequest['nc']++ : 1;
				
				// calculate the Digest hashes (calculate code based on digest implementation found at: http://www.rassoc.com/gregr/weblog/stories/2002/07/09/webServicesSecurityHttpDigestAuthenticationWithoutActiveDirectory.html)
	
				// A1 = unq(username-value) ":" unq(realm-value) ":" passwd
				$A1 = $username. ':' . $digestRequest['realm'] . ':' . $password;
	
				// H(A1) = MD5(A1)
				$HA1 = md5($A1);
	
				// A2 = Method ":" digest-uri-value
				$A2 = 'POST:' . $this->uri;
	
				// H(A2)
				$HA2 =  md5($A2);
	
				// KD(secret, data) = H(concat(secret, ":", data))
				// if qop == auth:
				// request-digest  = <"> < KD ( H(A1),     unq(nonce-value)
				//                              ":" nc-value
				//                              ":" unq(cnonce-value)
				//                              ":" unq(qop-value)
				//                              ":" H(A2)
				//                            ) <">
				// if qop is missing,
				// request-digest  = <"> < KD ( H(A1), unq(nonce-value) ":" H(A2) ) > <">
	
				$unhashedDigest = '';
				$nonce = isset($digestRequest['nonce']) ? $digestRequest['nonce'] : '';
				$cnonce = $nonce;
				if ($digestRequest['qop'] != '') {
					$unhashedDigest = $HA1 . ':' . $nonce . ':' . sprintf("%08d", $digestRequest['nc']) . ':' . $cnonce . ':' . $digestRequest['qop'] . ':' . $HA2;
				} else {
					$unhashedDigest = $HA1 . ':' . $nonce . ':' . $HA2;
				}
	
				$hashedDigest = md5($unhashedDigest);
	
				$this->outgoing_headers['Authorization'] = 'Digest username="' . $username . '", realm="' . $digestRequest['realm'] . '", nonce="' . $nonce . '", uri="' . $this->uri . '", cnonce="' . $cnonce . '", nc=' . sprintf("%08x", $digestRequest['nc']) . ', qop="' . $digestRequest['qop'] . '", response="' . $hashedDigest . '"';
			}
		}
		$this->username = $username;
		$this->password = $password;
		$this->authtype = $authtype;
		$this->digestRequest = $digestRequest;
		
		if (isset($this->outgoing_headers['Authorization'])) {
			$this->debug('Authorization header set: ' . substr($this->outgoing_headers['Authorization'], 0, 12) . '...');
		} else {
			$this->debug('Authorization header not set');
		}
	}
	
	/**
	* set the soapaction value
	*
	* @param    string $soapaction
	* @access   public
	*/
	function setSOAPAction($soapaction) {
		$this->outgoing_headers['SOAPAction'] = '"' . $soapaction . '"';
	}
	
	/**
	* use http encoding
	*
	* @param    string $enc encoding style. supported values: gzip, deflate, or both
	* @access   public
	*/
	function setEncoding($enc='gzip, deflate'){
		$this->protocol_version = '1.1';
		$this->outgoing_headers['Accept-Encoding'] = $enc;
		$this->outgoing_headers['Connection'] = 'close';
		$this->persistentConnection = false;
		set_magic_quotes_runtime(0);
		// deprecated
		$this->encoding = $enc;
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
	function setProxy($proxyhost, $proxyport, $proxyusername = '', $proxypassword = '') {
		$this->uri = $this->url;
		$this->host = $proxyhost;
		$this->port = $proxyport;
		if ($proxyusername != '' && $proxypassword != '') {
			$this->outgoing_headers['Proxy-Authorization'] = ' Basic '.base64_encode($proxyusername.':'.$proxypassword);
		}
	}
	
	/**
	* decode a string that is encoded w/ "chunked' transfer encoding
 	* as defined in RFC2068 19.4.6
	*
	* @param    string $buffer
	* @param    string $lb
	* @returns	string
	* @access   public
	*/
	function decodeChunked($buffer, $lb){
		// length := 0
		$length = 0;
		$new = '';
		
		// read chunk-size, chunk-extension (if any) and CRLF
		// get the position of the linebreak
		$chunkend = strpos($buffer, $lb);
		if ($chunkend == FALSE) {
			$this->debug('no linebreak found in decodeChunked');
			return $new;
		}
		$temp = substr($buffer,0,$chunkend);
		$chunk_size = hexdec( trim($temp) );
		$chunkstart = $chunkend + strlen($lb);
		// while (chunk-size > 0) {
		while ($chunk_size > 0) {
			$this->debug("chunkstart: $chunkstart chunk_size: $chunk_size");
			$chunkend = strpos( $buffer, $lb, $chunkstart + $chunk_size);
		  	
			// Just in case we got a broken connection
		  	if ($chunkend == FALSE) {
		  	    $chunk = substr($buffer,$chunkstart);
				// append chunk-data to entity-body
		    	$new .= $chunk;
		  	    $length += strlen($chunk);
		  	    break;
			}
			
		  	// read chunk-data and CRLF
		  	$chunk = substr($buffer,$chunkstart,$chunkend-$chunkstart);
		  	// append chunk-data to entity-body
		  	$new .= $chunk;
		  	// length := length + chunk-size
		  	$length += strlen($chunk);
		  	// read chunk-size and CRLF
		  	$chunkstart = $chunkend + strlen($lb);
			
		  	$chunkend = strpos($buffer, $lb, $chunkstart) + strlen($lb);
			if ($chunkend == FALSE) {
				break; //Just in case we got a broken connection
			}
			$temp = substr($buffer,$chunkstart,$chunkend-$chunkstart);
			$chunk_size = hexdec( trim($temp) );
			$chunkstart = $chunkend;
		}
		return $new;
	}
	
	/*
	 *	Writes payload, including HTTP headers, to $this->outgoing_payload.
	 */
	function buildPayload($data) {
		// add content-length header
		$this->outgoing_headers['Content-Length'] = strlen($data);
		
		// start building outgoing payload:
		$this->outgoing_payload = "$this->request_method $this->uri HTTP/$this->protocol_version\r\n";

		// loop thru headers, serializing
		foreach($this->outgoing_headers as $k => $v){
			$this->outgoing_payload .= $k.': '.$v."\r\n";
		}
		
		// header/body separator
		$this->outgoing_payload .= "\r\n";
		
		// add data
		$this->outgoing_payload .= $data;
	}

	function sendRequest($data){
		// build payload
		$this->buildPayload($data);

	  if ($this->scheme == 'http' || $this->scheme == 'ssl') {
		// send payload
		if(!fputs($this->fp, $this->outgoing_payload, strlen($this->outgoing_payload))) {
			$this->setError('couldn\'t write message data to socket');
			$this->debug('couldn\'t write message data to socket');
			return false;
		}
		$this->debug('wrote data to socket, length = ' . strlen($this->outgoing_payload));
		return true;
	  } else if ($this->scheme == 'https') {
		// set payload
		// TODO: cURL does say this should only be the verb, and in fact it
		// turns out that the URI and HTTP version are appended to this, which
		// some servers refuse to work with
		//curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $this->outgoing_payload);
		foreach($this->outgoing_headers as $k => $v){
			$curl_headers[] = "$k: $v";
		}
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $curl_headers);
		if ($this->request_method == "POST") {
	  		curl_setopt($this->ch, CURLOPT_POST, 1);
	  		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
	  	} else {
	  	}
		$this->debug('set cURL payload');
		return true;
	  }
	}

	function getResponse(){
		$this->incoming_payload = '';
	    
	  if ($this->scheme == 'http' || $this->scheme == 'ssl') {
	    // loop until headers have been retrieved
	    $data = '';
	    while (!isset($lb)){

			// We might EOF during header read.
			if(feof($this->fp)) {
				$this->incoming_payload = $data;
				$this->debug('found no headers before EOF after length ' . strlen($data));
				$this->debug("received before EOF:\n" . $data);
				$this->setError('server failed to send headers');
				return false;
			}

			$tmp = fgets($this->fp, 256);
			$tmplen = strlen($tmp);
			$this->debug("read line of $tmplen bytes: " . trim($tmp));

			if ($tmplen == 0) {
				$this->incoming_payload = $data;
				$this->debug('socket read of headers timed out after length ' . strlen($data));
				$this->debug("read before timeout:\n" . $data);
				$this->setError('socket read of headers timed out');
				return false;
			}

			$data .= $tmp;
			$pos = strpos($data,"\r\n\r\n");
			if($pos > 1){
				$lb = "\r\n";
			} else {
				$pos = strpos($data,"\n\n");
				if($pos > 1){
					$lb = "\n";
				}
			}
			// remove 100 header
			if(isset($lb) && ereg('^HTTP/1.1 100',$data)){
				unset($lb);
				$data = '';
			}//
		}
		// store header data
		$this->incoming_payload .= $data;
		$this->debug('found end of headers after length ' . strlen($data));
		// process headers
		$header_data = trim(substr($data,0,$pos));
		$header_array = explode($lb,$header_data);
		$this->incoming_headers = array();
		foreach($header_array as $header_line){
			$arr = explode(':',$header_line, 2);
			if(count($arr) > 1){
				$header_name = strtolower(trim($arr[0]));
				$this->incoming_headers[$header_name] = trim($arr[1]);
			} else if (isset($header_name)) {
				$this->incoming_headers[$header_name] .= $lb . ' ' . $header_line;
			}
		}
		
		// loop until msg has been received
		if (isset($this->incoming_headers['content-length'])) {
			$content_length = $this->incoming_headers['content-length'];
			$chunked = false;
			$this->debug("want to read content of length $content_length");
		} else {
			$content_length =  2147483647;
			if (isset($this->incoming_headers['transfer-encoding']) && strtolower($this->incoming_headers['transfer-encoding']) == 'chunked') {
				$chunked = true;
				$this->debug("want to read chunked content");
			} else {
				$chunked = false;
				$this->debug("want to read content to EOF");
			}
		}
		$data = '';
		do {
			if ($chunked) {
				$tmp = fgets($this->fp, 256);
				$tmplen = strlen($tmp);
				$this->debug("read chunk line of $tmplen bytes");
				if ($tmplen == 0) {
					$this->incoming_payload = $data;
					$this->debug('socket read of chunk length timed out after length ' . strlen($data));
					$this->debug("read before timeout:\n" . $data);
					$this->setError('socket read of chunk length timed out');
					return false;
				}
				$content_length = hexdec(trim($tmp));
				$this->debug("chunk length $content_length");
			}
			$strlen = 0;
		    while (($strlen < $content_length) && (!feof($this->fp))) {
		    	$readlen = min(8192, $content_length - $strlen);
				$tmp = fread($this->fp, $readlen);
				$tmplen = strlen($tmp);
				$this->debug("read buffer of $tmplen bytes");
				if (($tmplen == 0) && (!feof($this->fp))) {
					$this->incoming_payload = $data;
					$this->debug('socket read of body timed out after length ' . strlen($data));
					$this->debug("read before timeout:\n" . $data);
					$this->setError('socket read of body timed out');
					return false;
				}
				$strlen += $tmplen;
				$data .= $tmp;
			}
			if ($chunked && ($content_length > 0)) {
				$tmp = fgets($this->fp, 256);
				$tmplen = strlen($tmp);
				$this->debug("read chunk terminator of $tmplen bytes");
				if ($tmplen == 0) {
					$this->incoming_payload = $data;
					$this->debug('socket read of chunk terminator timed out after length ' . strlen($data));
					$this->debug("read before timeout:\n" . $data);
					$this->setError('socket read of chunk terminator timed out');
					return false;
				}
			}
		} while ($chunked && ($content_length > 0) && (!feof($this->fp)));
		if (feof($this->fp)) {
			$this->debug('read to EOF');
		}
		$this->debug('read body of length ' . strlen($data));
		$this->incoming_payload .= $data;
		$this->debug('received a total of '.strlen($this->incoming_payload).' bytes of data from server');
		
		// close filepointer
		if(
			(isset($this->incoming_headers['connection']) && strtolower($this->incoming_headers['connection']) == 'close') || 
			(! $this->persistentConnection) || feof($this->fp)){
			fclose($this->fp);
			$this->fp = false;
			$this->debug('closed socket');
		}
		
		// connection was closed unexpectedly
		if($this->incoming_payload == ''){
			$this->setError('no response from server');
			return false;
		}
		
		// decode transfer-encoding
//		if(isset($this->incoming_headers['transfer-encoding']) && strtolower($this->incoming_headers['transfer-encoding']) == 'chunked'){
//			if(!$data = $this->decodeChunked($data, $lb)){
//				$this->setError('Decoding of chunked data failed');
//				return false;
//			}
			//print "<pre>\nde-chunked:\n---------------\n$data\n\n---------------\n</pre>";
			// set decoded payload
//			$this->incoming_payload = $header_data.$lb.$lb.$data;
//		}
	
	  } else if ($this->scheme == 'https') {
		// send and receive
		$this->debug('send and receive with cURL');
		$this->incoming_payload = curl_exec($this->ch);
		$data = $this->incoming_payload;

        $cErr = curl_error($this->ch);
		if ($cErr != '') {
        	$err = 'cURL ERROR: '.curl_errno($this->ch).': '.$cErr.'<br>';
			foreach(curl_getinfo($this->ch) as $k => $v){
				$err .= "$k: $v<br>";
			}
			$this->debug($err);
			$this->setError($err);
			curl_close($this->ch);
	    	return false;
		} else {
			//echo '<pre>';
			//var_dump(curl_getinfo($this->ch));
			//echo '</pre>';
		}
		// close curl
		$this->debug('No cURL error, closing cURL');
		curl_close($this->ch);
		
		// remove 100 header
		if (ereg('^HTTP/1.1 100',$data)) {
			if ($pos = strpos($data,"\r\n\r\n")) {
				$data = ltrim(substr($data,$pos));
			} elseif($pos = strpos($data,"\n\n") ) {
				$data = ltrim(substr($data,$pos));
			}
		}
		
		// separate content from HTTP headers
		if ($pos = strpos($data,"\r\n\r\n")) {
			$lb = "\r\n";
		} elseif( $pos = strpos($data,"\n\n")) {
			$lb = "\n";
		} else {
			$this->debug('no proper separation of headers and document');
			$this->setError('no proper separation of headers and document');
			return false;
		}
		$header_data = trim(substr($data,0,$pos));
		$header_array = explode($lb,$header_data);
		$data = ltrim(substr($data,$pos));
		$this->debug('found proper separation of headers and document');
		$this->debug('cleaned data, stringlen: '.strlen($data));
		// clean headers
		foreach ($header_array as $header_line) {
			$arr = explode(':',$header_line,2);
			if (count($arr) > 1) {
				$this->incoming_headers[strtolower(trim($arr[0]))] = trim($arr[1]);
			}
		}
	  }

 		// see if we need to resend the request with http digest authentication
 		if (isset($this->incoming_headers['www-authenticate']) && strstr($header_array[0], '401 Unauthorized')) {
 			$this->debug('Got 401 Unauthorized with WWW-Authenticate: ' . $this->incoming_headers['www-authenticate']);
 			if (substr("Digest ", $this->incoming_headers['www-authenticate'])) {
 				$this->debug('Server wants digest authentication');
 				// remove "Digest " from our elements
 				$digestString = str_replace('Digest ', '', $this->incoming_headers['www-authenticate']);
 				
 				// parse elements into array
 				$digestElements = explode(',', $digestString);
 				foreach ($digestElements as $val) {
 					$tempElement = explode('=', trim($val));
 					$digestRequest[$tempElement[0]] = str_replace("\"", '', $tempElement[1]);
 				}

				// should have (at least) qop, realm, nonce
 				if (isset($digestRequest['nonce'])) {
 					$this->setCredentials($this->username, $this->password, 'digest', $digestRequest);
 					$this->tryagain = true;
 					return false;
 				}
 			}
			$this->debug('HTTP authentication failed');
			$this->setError('HTTP authentication failed');
			return false;
 		}
		
		// decode content-encoding
		if(isset($this->incoming_headers['content-encoding']) && $this->incoming_headers['content-encoding'] != ''){
			if(strtolower($this->incoming_headers['content-encoding']) == 'deflate' || strtolower($this->incoming_headers['content-encoding']) == 'gzip'){
    			// if decoding works, use it. else assume data wasn't gzencoded
    			if(function_exists('gzuncompress')){
					//$timer->setMarker('starting decoding of gzip/deflated content');
					if($this->incoming_headers['content-encoding'] == 'deflate' && $degzdata = @gzuncompress($data)){
    					$data = $degzdata;
					} elseif($this->incoming_headers['content-encoding'] == 'gzip' && $degzdata = gzinflate(substr($data, 10))){	// do our best
						$data = $degzdata;
					} else {
						$this->setError('Errors occurred when trying to decode the data');
					}
					//$timer->setMarker('finished decoding of gzip/deflated content');
					//print "<xmp>\nde-inflated:\n---------------\n$data\n-------------\n</xmp>";
					// set decoded payload
					$this->incoming_payload = $header_data.$lb.$lb.$data;
    			} else {
					$this->setError('The server sent deflated data. Your php install must have the Zlib extension compiled in to support this.');
				}
			}
		}
		
		if(strlen($data) == 0){
			$this->debug('no data after headers!');
			$this->setError('no data present after HTTP headers');
			return false;
		}
		
		return $data;
	}

	function setContentType($type, $charset = false) {
		$this->outgoing_headers['Content-Type'] = $type . ($charset ? '; charset=' . $charset : '');
	}

	function usePersistentConnection(){
		if (isset($this->outgoing_headers['Accept-Encoding'])) {
			return false;
		}
		$this->protocol_version = '1.1';
		$this->persistentConnection = true;
		$this->outgoing_headers['Connection'] = 'Keep-Alive';
		return true;
	}
}


?>