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
// | Authors: Shane Caraveo <Shane@Caraveo.com>   Port to PEAR and more   |
// | Authors: Dietrich Ayala <dietrich@ganx4.com> Original Author         |
// +----------------------------------------------------------------------+
//
// $Id$
//

require_once dirname(__FILE__).'/class.ilBMFValue.php';
require_once dirname(__FILE__).'/class.ilBMFBase.php';
require_once dirname(__FILE__).'/class.ilBMFTransport.php';
require_once dirname(__FILE__).'/class.ilBMFWSDL.php';
require_once dirname(__FILE__).'/class.ilBMFFault.php';
require_once dirname(__FILE__).'/class.ilBMFParser.php';
/**
*  SOAP Client Class
* this class is the main interface for making soap requests
*
* basic usage: 
*   $soapclient = new ilBMFClient( string path [ , boolean wsdl] );
*   echo $soapclient->call( string methodname [ , array parameters] );
*
* originaly based on SOAPx4 by Dietrich Ayala http://dietrich.ganx4.com/soapx4
*
* @access   public
* @version  $Id$
* @package  ilBMF::Client
* @author   Shane Caraveo <shane@php.net> Conversion to PEAR and updates
* @author   Stig Bakken <ssb@fast.no> Conversion to PEAR
* @author   Dietrich Ayala <dietrich@ganx4.com> Original Author
*/
class ilBMFClient extends ilBMFBase
{
    /**
    * Communication endpoint.
    *
    * Currently the following transport formats are supported:
    *  - HTTP
    *  - SMTP
    * 
    * Example endpoints:
    *   http://www.example.com/soap/server.php
    *   https://www.example.com/soap/server.php
    *   mailto:soap@example.com
    *
    * @var  string
    * @see  ilBMFClient()
    */
    var $_endpoint = '';
    
    /**
    * portname
    *
    * @var string contains the SOAP PORT name that is used by the client
    */
    var $_portName = '';
    
    
    /**
    * Endpoint type 
    *
    * @var  string  e.g. wdsl
    */
    var $__endpointType = '';
    
    /**
    * wire
    *
    * @var  string  contains outoing and incoming data stream for debugging.
    */
    var $xml; // contains the received xml
    var $wire;
    var $__last_request = NULL;
    var $__last_response = NULL;
    var $__options = array('trace'=>0);
    /**
    * encoding
    *
    * @var  string  Contains the character encoding used for XML parser, etc.
    */
    var $_encoding = SOAP_DEFAULT_ENCODING;
    
    
    /**
    * headersOut
    *
    * @var  array  contains an array of ilBMFHeaders that we are sending
    */
    var $headersOut = NULL;
    /**
    * headersOut
    *
    * @var  array  contains an array headers we recieved back in the response
    */
    var $headersIn = NULL;
    
    /**
    * __proxy_params
    *
    * @var  array  contains options for HTTP_Request class (see HTTP/Request.php)
    */
    var $__proxy_params = array();
    /**
    * ilBMFClient constructor
    *
    * @param string endpoint (URL)
    * @param boolean wsdl (true if endpoint is a wsdl file)
    * @param string portName
    * @param array  contains options for HTTP_Request class (see HTTP/Request.php)
    * @access public
    */
    function ilBMFClient($endpoint, $wsdl = false, $portName = false, $proxy_params=array())
    {
        parent::ilBMFBase('Client');
        $this->_endpoint = $endpoint;
        $this->_portName = $portName;
        $this->__proxy_params = $proxy_params;
        
        // make values
        if ($wsdl) {
            $this->__endpointType = 'wsdl';
            // instantiate wsdl class
            $this->_wsdl = new ilBMFWSDL($this->_endpoint, $this->__proxy_params);
            if ($this->_wsdl->fault) {
                $this->_raiseSoapFault($this->_wsdl->fault);
            }
        }
    }
    
    /**
    * setEncoding
    *
    * set the character encoding, limited to 'UTF-8', 'US_ASCII' and 'ISO-8859-1'
    *
    * @param string encoding
    *
    * @return mixed returns NULL or ilBMFFault
    * @access public
    */
    function setEncoding($encoding)
    {
        if (in_array($encoding, $this->_encodings)) {
            $this->_encoding = $encoding;
            return NULL;
        }
        return $this->_raiseSoapFault('Invalid Encoding');
    }
    
    /**
    * addHeader
    *
    * To add headers to the envelop, you use this function, sending it a
    * ilBMFHeader class instance.
    *
    * @param ilBMFHeader a soap value to send as a header
    *
    * @access public
    */
    function addHeader(&$soap_value)
    {
        # add a new header to the message
        if (is_a($soap_value,'ibmfheader')) {
            $this->headersOut[] = $soap_value;
        } else if (gettype($soap_value) == 'array') {
            // name, value, namespace, mustunderstand, actor
            $h = new ilBMFHeader($soap_value[0], NULL, $soap_value[1], $soap_value[2], $soap_value[3]);
            $this->headersOut[] = $h;
        } else {
            $this->_raiseSoapFault("Don't understand the header info you provided.  Must be array or ilBMFHeader.");
        }
    }

    /**
    * ilBMFClient::call
    *
    * the namespace parameter is overloaded to accept an array of
    * options that can contain data necessary for various transports
    * if it is used as an array, it MAY contain a namespace value and a
    * soapaction value.  If it is overloaded, the soapaction parameter is
    * ignored and MUST be placed in the options array.  This is done
    * to provide backwards compatibility with current clients, but
    * may be removed in the future.
    *
    * @param string method
    * @param array  params
    * @param array options (hash with namespace, soapaction, timeout, from, subject, etc.)
    *
    * The options parameter can have a variety of values added.  The currently supported
    * values are:
    *   namespace
    *   soapaction
    *   timeout (http socket timeout)
    *   from (smtp)
    *   transfer-encoding (smtp, sets the Content-Transfer-Encoding header)
    *   subject (smtp, subject header)
    *   headers (smtp, array-hash of extra smtp headers)
    *
    * @return array of results
    * @access public
    */
    function call($method, $params = array(), $namespace = false, $soapAction = false, $paramtype = array())
    {
        $soap_data = $this->__generate($method, $params, $namespace, $soapAction, $paramtype);
        if (PEAR::isError($soap_data)) {
            return $this->_raiseSoapFault($soap_data);
        }
        $soap_transport = new ilBMFTransport($this->_endpoint, $this->_encoding);
        if ($soap_transport->fault) {
            return $this->_raiseSoapFault($soap_transport->fault);
        }
        // send the message

        $transport_options = array_merge($this->__proxy_params, $this->__options);
            //echo "$soap_data";
        $this->xml = $soap_transport->send($soap_data, $transport_options);

        // save the wire information for debugging
#$this->__options["trace"] = 1;
        if ($this->__options['trace'] > 0) {
            $this->__last_request =& $soap_transport->transport->outgoing_payload;
            $this->__last_response =& $soap_transport->transport->incoming_payload;
            $this->wire = $this->__get_wire();
#			var_dump("<pre>",$this->wire,"</pre>");
        }
        if ($soap_transport->fault) {
            return $this->_raiseSoapFault($this->xml);
        }

        $this->__attachments =& $soap_transport->transport->attachments;
        $this->__result_encoding = $soap_transport->result_encoding;
        unset($soap_transport);
        
        if (isset($this->__options['result']) && $this->__options['result'] != 'parse') return $this->xml;
        
        return $this->__parse($this->xml, $this->__result_encoding,$this->__attachments);
    }
    
    /**
    * ilBMFClient::__call
    *
    * Overload extension support
    * if the overload extension is loaded, you can call the client class
    * with a soap method name
    * $soap = new ilBMFClient(....);
    * $value = $soap->getStockQuote('MSFT');
    *
    * @param string method
    * @param array  args
    * @param string retur_value
    *
    * @return boolean
    * @access public
    */
    function __call($method, $args, &$return_value)
    {
        if ($this->_wsdl) $this->_wsdl->matchMethod($method);
        $return_value = $this->call($method, $args);
        return TRUE;
    }
    
    function &__getlastrequest()
    {
        return $this->__last_request;
    }
    
    function &__getlastresponse()
    {
        return $this->__last_response;
    }

    function __use($use)
    {
        $this->__options['use'] = $use;
    }
    
    function __style($style)
    {
        $this->__options['style'] = $style;
    }
    
    function __trace($level)
    {
        $this->__options['trace'] = $level;
    }
    
    function &__generate($method, $params = array(), $namespace = false, $soapAction = false, $paramtype = array() )
    {
        $this->fault = null;
        $this->__options['input']='parse';
        $this->__options['result']='parse';
        $this->__options['parameters'] = false;
        if ($params && gettype($params) != 'array') {
            $params = array($params);
        }
        if (gettype($namespace) == 'array')
        {
            foreach ($namespace as $optname=>$opt)
            {
                $this->__options[strtolower($optname)]=$opt;
            }
            if (isset($this->__options['namespace']))
               $namespace = $this->__options['namespace'];
            else
                $namespace = false;
        }
        else
        {
            // we'll place soapaction into our array for usage in the transport
            $this->__options['soapaction'] = $soapAction;
            $this->__options['namespace'] = $namespace;
        }
        
        if ($this->__endpointType == 'wsdl') {
            $this->_setSchemaVersion($this->_wsdl->xsd);
            // get portName
            if (!$this->_portName) {
                $this->_portName = $this->_wsdl->getPortName($method);
            }
            if (PEAR::isError($this->_portName)) {
                return $this->_raiseSoapFault($this->_portName);
            }

            // get endpoint
            $this->_endpoint = $this->_wsdl->getEndpoint($this->_portName);
            if (PEAR::isError($this->_endpoint)) {
                return $this->_raiseSoapFault($this->_endpoint);
            }

            // get operation data
            $opData = $this->_wsdl->getOperationData($this->_portName, $method);
            
            if (PEAR::isError($opData)) {
                return $this->_raiseSoapFault($opData);
            }
            $namespace = $opData['namespace'];
            $this->__options['style'] = $opData['style'];
            $this->__options['use'] = $opData['input']['use'];
            $this->__options['soapaction'] = $opData['soapAction'];

            // set input params
            if ($this->__options['input'] == 'parse') {
            $this->__options['parameters'] = $opData['parameters'];
            $nparams = array();
            if (isset($opData['input']['parts']) && count($opData['input']['parts']) > 0) {
                $i = 0;
                reset($params);
                foreach ($opData['input']['parts'] as $name => $part) {
                    $xmlns = '';
                    $attrs = array();
                    // is the name actually a complex type?
                    if (isset($part['element'])) {
                        $xmlns = $this->_wsdl->namespaces[$part['namespace']];
                        $part = $this->_wsdl->elements[$part['namespace']][$part['type']];
                        $name = $part['name'];
                    }
                    if (isset($params[$name])) {
                        $nparams[$name] = $params[$name];
                    } else {
                        # we now force an associative array for parameters if using wsdl
                        return $this->_raiseSoapFault("The named parameter $name is not in the call parameters.");
                    }
                    if (gettype($nparams[$name]) != 'object' ||
                        !is_a($nparams[$name],'ilbmfvalue')) {
                        // type is a qname likely, split it apart, and get the type namespace from wsdl
                        $qname = new QName($part['type']);
                        if ($qname->ns) 
                            $type_namespace = $this->_wsdl->namespaces[$qname->ns];
                        else if (isset($part['namespace']))
                            $type_namespace = $this->_wsdl->namespaces[$part['namespace']];
                        else
                            $type_namespace = NULL;
                        $qname->namespace = $type_namespace;
                        $type = $qname->name;
                        $pqname = $name;
                        if ($xmlns) $pqname = '{'.$xmlns.'}'.$name;
                        $nparams[$name] = new ilBMFValue($pqname, $qname->fqn(), $nparams[$name],$attrs);
                    } else {
                        // wsdl fixups to the soap value
                    }
                }
            }
            $params = $nparams;
            }
        } else {
            $this->_setSchemaVersion(SOAP_XML_SCHEMA_VERSION);
        }
        
        // serialize the message
        $this->_section5 = TRUE; // assume we encode with section 5
        if (isset($this->__options['use']) && $this->__options['use']=='literal') $this->_section5 = FALSE;
        
        if (!isset($this->__options['style']) || $this->__options['style'] == 'rpc') {
            $this->__options['style'] = 'rpc';
            $this->docparams = true;
            $mqname = new QName($method, $namespace);
            $methodValue = new ilBMFValue($mqname->fqn(), 'Struct', $params);
            $soap_msg = $this->_makeEnvelope($methodValue, $this->headersOut, $this->_encoding,$this->__options);
        } else {
            if (!$params) {
                $mqname = new QName($method, $namespace);
                $params = new ilBMFValue($mqname->fqn(), 'Struct', NULL);
            } elseif ($this->__options['input'] == 'parse') {
                if (is_array($params)) {
                    $nparams = array();
                    foreach ($params as $n => $v) {
                        if (gettype($v) != 'object') {
                            $nparams[] = new ilBMFValue($n, false, $v);
                        } else {
                            $nparams[] = $v;
                        }
                    }
                    $params = $nparams;
                }
                if ($this->__options['parameters']) {
                    $mqname = new QName($method, $namespace);
                    $params = new ilBMFValue($mqname->fqn(), 'Struct', $params);
                }
            }
            $soap_msg = $this->_makeEnvelope($params, $this->headersOut, $this->_encoding,$this->__options);
        }

        if (PEAR::isError($soap_msg)) {
            return $this->_raiseSoapFault($soap_msg);
        }
        
        // handle Mime or DIME encoding
        // XXX DIME Encoding should move to the transport, do it here for now
        // and for ease of getting it done
        if (count($this->__attachments)) {
            if ((isset($this->__options['attachments']) && $this->__options['attachments'] == 'Mime') || isset($this->__options['Mime'])) {
                $soap_msg = $this->_makeMimeMessage($soap_msg, $this->_encoding);
            } else {
                // default is dime
                $soap_msg = $this->_makeDIMEMessage($soap_msg, $this->_encoding);
                $this->__options['headers']['Content-Type'] = 'application/dime';
            }
            if (PEAR::isError($soap_msg)) {
                return $this->_raiseSoapFault($soap_msg);
            }
        }
        
        // instantiate client
        if (is_array($soap_msg)) {
            $soap_data = $soap_msg['body'];
            if (count($soap_msg['headers'])) {
                if (isset($this->__options['headers'])) {
                    $this->__options['headers'] = array_merge($this->__options['headers'],$soap_msg['headers']);
                } else {
                    $this->__options['headers'] = $soap_msg['headers'];
                }
            }
        } else {
            $soap_data = $soap_msg;
        }
        return $soap_data;
    }

    function &__parse($response, $encoding, $attachments)
    {
        // parse the response
        $this->response = new ilBMFParser($response, $encoding, $attachments);
        if ($this->response->fault) {
            return $this->_raiseSoapFault($this->response->fault);
        }
        // return array of parameters
        $return = $this->response->getResponse();
        $headers = $this->response->getHeaders();
        if ($headers) {
            $this->headersIn = $this->__decodeResponse($headers,false);
        }
        
        return $this->__decodeResponse($return); 
    }

    function &__decodeResponse($response,$shift=true) 
    {
        if (!$response) return NULL;
        // check for valid response
        if (PEAR::isError($response)) {
            return $this->_raiseSoapFault($response);
        } else if (!is_a($response,'ilbmfvalue')) {
            return $this->_raiseSoapFault("didn't get ilBMFValue object back from client");
        }

        // decode to native php datatype
        $returnArray = $this->_decode($response);
        // fault?
        if (PEAR::isError($returnArray)) {
            return $this->_raiseSoapFault($returnArray);
        }
        if (is_object($returnArray) && get_class($returnArray) == 'stdClass') {
            $returnArray = get_object_vars($returnArray);
        }

        if (is_array($returnArray)) {
            if (isset($returnArray['faultcode']) || isset($returnArray['SOAP-ENV:faultcode'])) {
                $faultcode = $faultstring = $faultdetail = $faultactor = '';
                foreach ($returnArray as $k => $v) {
                    if (stristr($k,'faultcode')) $faultcode = $v;
                    if (stristr($k,'faultstring')) $faultstring = $v;
                    if (stristr($k,'detail')) $faultdetail = $v;
                    if (stristr($k,'faultactor')) $faultactor = $v;
                }
                return $this->_raiseSoapFault($faultstring, $faultdetail, $faultactor, $faultcode);
            }
            // return array of return values
            if ($shift && count($returnArray) == 1) {
                return array_shift($returnArray);
            }
            return $returnArray;
        }
        return $returnArray;
    }
    
    function __get_wire()
    {
        if ($this->__options['trace'] > 0) {
            return "OUTGOING:\n\n".
            $this->__last_request.
            "\n\nINCOMING\n\n".
            preg_replace("/></",">\r\n<",$this->__last_response);
        }
        return NULL;
    }
}

#if (extension_loaded('overload')) {
#    overload('ilBMFClient');
#}
?>
