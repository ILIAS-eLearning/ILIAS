<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Shane Caraveo <Shane@Caraveo.com>                           |
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once dirname(__FILE__).'/../class.ilBMFBase.php';
require_once 'Net/DIME.php';
/**
*  HTTP Transport for SOAP
*
* @access public
* @version $Id$
* @package ilBMF::Transport::HTTP
* @author Shane Caraveo <shane@php.net>
*/
class ilBMFTransport_HTTP extends ilBMFBase
{
    
    /**
    * Basic Auth string
    *
    * @var  string
    */
    var $headers = array();
    
    /**
    *
    * @var  int connection timeout in seconds - 0 = none
    */
    var $timeout = 4;
    
    /**
    * Array containing urlparts - parse_url()
    * 
    * @var  mixed
    */
    var $urlparts = NULL;
    
    /**
    * Connection endpoint - URL
    *
    * @var  string
    */
    var $url = '';
    
    /**
    * Incoming payload
    *
    * @var  string
    */
    var $incoming_payload = '';
    
    /**
    * HTTP-Request User-Agent
    *
    * @var  string
    */
    var $_userAgent = SOAP_LIBRARY_NAME;

    var $encoding = SOAP_DEFAULT_ENCODING;
    
    /**
    * HTTP-Response Content-Type encoding
    *
    * we assume UTF-8 if no encoding is set
    * @var  string
    */
    var $result_encoding = 'UTF-8';
    
    var $result_content_type;
    /**
    * ilBMFTransport_HTTP Constructor
    *
    * @param string $URL    http url to soap endpoint
    *
    * @access public
    */
    function ilBMFTransport_HTTP($URL, $encoding=SOAP_DEFAULT_ENCODING)
    {
        parent::ilBMFBase('HTTP');
        $this->urlparts = @parse_url($URL);
        $this->url = $URL;
        $this->encoding = $encoding;
    }
    
    /**
    * send and receive soap data
    *
    * @param string &$msg       outgoing post data
    * @param string $action      SOAP Action header data
    * @param int $timeout  socket timeout, default 0 or off
    *
    * @return string|fault response
    * @access public
    */
    function &send(&$msg,  /*array*/ $options = NULL)
    {
        if (!$this->_validateUrl()) {
            return $this->fault;
        }
        
        if (isset($options['timeout'])) 
            $this->timeout = (int)$options['timeout'];
    
        if (strcasecmp($this->urlparts['scheme'], 'HTTP') == 0) {
            return $this->_sendHTTP($msg, $options);
        } else if (strcasecmp($this->urlparts['scheme'], 'HTTPS') == 0) {
            return $this->_sendHTTPS($msg, $options);
        }
        
        return $this->_raiseSoapFault('Invalid url scheme '.$this->url);
    }

    /**
    * set data for http authentication
    * creates Authorization header
    *
    * @param string $username   username
    * @param string $password   response data, minus http headers
    *
    * @return none
    * @access public
    */
    function setCredentials($username, $password)
    {
        $this->headers['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
    }
    
    // private members
    
    /**
    * validate url data passed to constructor
    *
    * @return boolean
    * @access private
    */
    function _validateUrl()
    {
        if ( ! is_array($this->urlparts) ) {
            $this->_raiseSoapFault("Unable to parse URL $url");
            return FALSE;
        }
        if (!isset($this->urlparts['host'])) {
            $this->_raiseSoapFault("No host in URL $url");
            return FALSE;
        }
        if (!isset($this->urlparts['port'])) {
            
            if (strcasecmp($this->urlparts['scheme'], 'HTTP') == 0)
                $this->urlparts['port'] = 80;
            else if (strcasecmp($this->urlparts['scheme'], 'HTTPS') == 0) 
                $this->urlparts['port'] = 443;
                
        }
        if (isset($this->urlparts['user'])) {
            $this->setCredentials($this->urlparts['user'], $this->urlparts['pass']);
        }
        if (!isset($this->urlparts['path']) || !$this->urlparts['path'])
            $this->urlparts['path'] = '/';
        return TRUE;
    }
    
    function _parseEncoding($headers)
    {
        $h = stristr($headers,'Content-Type');
        preg_match('/^Content-Type:\s*(.*)$/im',$h,$ct);
        $this->result_content_type = str_replace("\r","",$ct[1]);
        if (preg_match('/(.*?)(?:;\s?charset=)(.*)/i',$this->result_content_type,$m)) {
            // strip the string of \r
            $this->result_content_type = $m[1];
            if (count($m) > 2) {
                $enc = strtoupper(str_replace('"',"",$m[2]));
                if (in_array($enc, $this->_encodings)) {
                    $this->result_encoding = $enc;
                }
            }
        }
        // deal with broken servers that don't set content type on faults
        if (!$this->result_content_type) $this->result_content_type = 'text/xml';
    }
    
    /**
    * remove http headers from response
    *
    * @return boolean
    * @access private
    */
    function _parseResponse()
    {
        if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s", $this->incoming_payload, $match)) {
            #$this->response = preg_replace("/[\r|\n]/", '', $match[2]);
            $this->response = $match[2];
            // find the response error, some servers response with 500 for soap faults
            if (preg_match("/^HTTP\/1\.. (\d+).*/s",$match[1],$status) &&
                $status[1] >= 400 && $status[1] < 500) {
                    $this->_raiseSoapFault("HTTP Response $status[1] Not Found");
                    return FALSE;
            }
            $this->_parseEncoding($match[1]);
            if ($this->result_content_type == 'application/dime') {
                // XXX quick hack insertion of DIME
                $this->_decodeDIMEMessage($this->response,$this->headers,$this->attachments);
                $this->result_content_type = $this->headers['content-type'];
            } else if (stristr($this->result_content_type,'multipart/related')) {
                $this->response = $this->incoming_payload;
                $this->_decodeMimeMessage($this->response,$this->headers,$this->attachments);
            } else if ($this->result_content_type != 'text/xml') {
                $this->_raiseSoapFault($this->response);
                return FALSE;
            }
            // if no content, return false
            return strlen($this->response) > 0;
        }
        $this->_raiseSoapFault('Invalid HTTP Response');
        return FALSE;
    }
    
    /**
    * create http request, including headers, for outgoing request
    *
    * @return string outgoing_payload
    * @access private
    */
    function &_getRequest(&$msg, $options)
    {
        $action = isset($options['soapaction'])?$options['soapaction']:'';
        $fullpath = $this->urlparts['path'].
                        (isset($this->urlparts['query'])?'?'.$this->urlparts['query']:'').
                        (isset($this->urlparts['fragment'])?'#'.$this->urlparts['fragment']:'');
        if (isset($options['proxy_user'])) {
            $this->headers['Proxy-Authorization'] = 'Basic ' . base64_encode($options['proxy_user'].":".$options['proxy_pass']);
        }
        $this->headers['User-Agent'] = $this->_userAgent;
        $this->headers['Host'] = $this->urlparts['host'];
        $this->headers['Content-Type'] = "text/xml; charset=$this->encoding";
        $this->headers['Content-Length'] = strlen($msg);
        $this->headers['SOAPAction'] = "\"$action\"";
        if (isset($options['headers'])) {
            $this->headers = array_merge($this->headers, $options['headers']);
        }
        $headers = '';
        foreach ($this->headers as $k => $v) {
            $headers .= "$k: $v\r\n";
        }
        $this->outgoing_payload = 
                "POST $fullpath HTTP/1.0\r\n".
                $headers."\r\n".
                $msg;
        return $this->outgoing_payload;
    }
    
    /**
    * send outgoing request, and read/parse response
    *
    * @param string &$msg   outgoing SOAP package
    * @param string $action   SOAP Action
    *
    * @return string &$response   response data, minus http headers
    * @access private
    */
    function &_sendHTTP(&$msg, $options)
    {
        $this->_getRequest($msg, $options);
        $host = $this->urlparts['host'];
        $port = $this->urlparts['port'];
        if (isset($options['proxy_host'])) {
            $host = $options['proxy_host'];
            $port = isset($options['proxy_port'])?$options['proxy_port']:8080;
        }
        // send
        if ($this->timeout > 0) {
            $fp = fsockopen($host, $port, $this->errno, $this->errmsg, $this->timeout);
        } else {
            $fp = fsockopen($host, $port, $this->errno, $this->errmsg);
        }
        if (!$fp) {
            return $this->_raiseSoapFault("Connect Error to $host:$port");
        }
        if ($this->timeout > 0) {
            // some builds of php do not support this, silence
            // the warning
            @socket_set_timeout($fp, $this->timeout);
        }
        if (!fputs($fp, $this->outgoing_payload, strlen($this->outgoing_payload))) {
            return $this->_raiseSoapFault("Error POSTing Data to $host");
        }
        
        // get reponse
        // XXX time consumer
        while ($data = fread($fp, 32768)) {
            $this->incoming_payload .= $data;
        }

        fclose($fp);

        if (!$this->_parseResponse()) {
            return $this->fault;
        }
        return $this->response;
    }

    /**
    * send outgoing request, and read/parse response, via HTTPS
    *
    * @param string &$msg   outgoing SOAP package
    * @param string $action   SOAP Action
    *
    * @return string &$response   response data, minus http headers
    * @access private
    */
    function &_sendHTTPS(&$msg, $options)
    {
        /* NOTE This function uses the CURL functions
        *  Your php must be compiled with CURL
        */
        if (!extension_loaded('curl')) {
            return $this->_raiseSoapFault('CURL Extension is required for HTTPS');
        }
        
        $this->_getRequest($msg, $options);

        $ch = curl_init();

        // XXX don't know if this proxy stuff is right for CURL
        if (isset($options['proxy_host'])) {
            // $options['http_proxy'] == 'hostname:port'
            $host = $options['proxy_host'];
            $port = isset($options['proxy_port'])?$options['proxy_port']:8080;
            curl_setopt($ch, CURLOPT_PROXY, $host.":".$port);
        }
        if (isset($options['proxy_user'])) {
            // $options['http_proxy_userpw'] == 'username:password'
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $options['proxy_user'].':'.$options['proxy_pass']);
        }

        if ($this->timeout) {
            //curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout); //times out after 4s
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->outgoing_payload);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);

        if (isset($options['curl'])) {
	  reset($options['curl']);
	  while (list($key, $val) = each ($options['curl'])) {
	    curl_setopt($ch, $key, $val);
	  }
        }


        $this->response = curl_exec($ch);
        echo curl_error ( $ch);
        curl_close($ch);

        return $this->response;
    }
} // end ilBMFTransport_HTTP
?>
