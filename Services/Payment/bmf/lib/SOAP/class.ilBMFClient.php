<?php
/**
 * This file contains the code for the SOAP client.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 2.02 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is available at
 * through the world-wide-web at http://www.php.net/license/2_02.txt.  If you
 * did not receive a copy of the PHP license and are unable to obtain it
 * through the world-wide-web, please send a note to license@php.net so we can
 * mail you a copy immediately.
 *
 * @category   Web Services
 * @package    SOAP
 * @author     Dietrich Ayala <dietrich@ganx4.com> Original Author
 * @author     Shane Caraveo <Shane@Caraveo.com>   Port to PEAR and more
 * @author     Chuck Hagenbuch <chuck@horde.org>   Maintenance
 * @author     Jan Schneider <jan@horde.org>       Maintenance
 * @copyright  2003-2005 The PHP Group
 * @license    http://www.php.net/license/2_02.txt  PHP License 2.02
 * @link       http://pear.php.net/package/SOAP
 */

require_once dirname(__FILE__).'/class.ilBMFValue.php';
require_once dirname(__FILE__).'/class.ilBMFBase.php';
require_once dirname(__FILE__).'/class.ilBMFTransport.php';
require_once dirname(__FILE__).'/class.ilBMFWSDL.php';
require_once dirname(__FILE__).'/class.ilBMFFault.php';
require_once dirname(__FILE__).'/class.ilBMFParser.php';

// Arnaud: the following code was taken from DataObject and adapted to suit

// this will be horrifically slow!!!!
// NOTE: Overload SEGFAULTS ON PHP4 + Zend Optimizer
// these two are BC/FC handlers for call in PHP4/5

if (!class_exists('ilBMFClient_Overload')) {
    if (substr(phpversion(), 0, 1) == 5) {
        class ilBMFClient_Overload extends ilBMFBase {
            function __call($method, $args)
            {
                $return = null;
                $this->_call($method, $args, $return);
                return $return;
            }
        }
    } else {
        if (!function_exists('clone')) {
            eval('function clone($t) { return $t; }');
        }
        eval('
            class ilBMFClient_Overload extends ilBMFBase {
                function __call($method, $args, &$return)
                {
                    return $this->_call($method, $args, $return);
                }
            }');
    }
}

/**
 * SOAP Client Class
 *
 * This class is the main interface for making soap requests.
 *
 * basic usage:<code>
 *   $soapclient = new ilBMFClient( string path [ , boolean wsdl] );
 *   echo $soapclient->call( string methodname [ , array parameters] );
 * </code>
 *
 * Originally based on SOAPx4 by Dietrich Ayala
 * http://dietrich.ganx4.com/soapx4
 *
 * @access   public
 * @package  SOAP
 * @author   Shane Caraveo <shane@php.net> Conversion to PEAR and updates
 * @author   Stig Bakken <ssb@fast.no> Conversion to PEAR
 * @author   Dietrich Ayala <dietrich@ganx4.com> Original Author
 */
class ilBMFClient extends ilBMFClient_Overload
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
     * @see  ilBMFClient()
     * @var $_endpoint string
     */
    var $_endpoint = '';

    /**
     * The SOAP PORT name that is used by the client.
     *
     * @var $_portName string
     */
    var $_portName = '';

    /**
     * Endpoint type e.g. 'wdsl'.
     *
     * @var $__endpointType string
     */
    var $__endpointType = '';

    /**
     * The received xml.
     *
     * @var $xml string
     */
    var $xml;

    /**
     * The outgoing and incoming data stream for debugging.
     *
     * @var $wire string
     */
    var $wire;
    var $__last_request = null;
    var $__last_response = null;

    /**
     * Options.
     *
     * @var $__options array
     */
    var $__options = array('trace'=>0);

    /**
     * The character encoding used for XML parser, etc.
     *
     * @var $_encoding string
     */
    var $_encoding = SOAP_DEFAULT_ENCODING;

    /**
     * The array of SOAP_Headers that we are sending.
     *
     * @var $headersOut array
     */
    var $headersOut = null;

    /**
     * The headers we recieved back in the response.
     *
     * @var $headersIn array
     */
    var $headersIn = null;

    /**
     * Options for the HTTP_Request class (see HTTP/Request.php).
     *
     * @var $__proxy_params array
     */
    var $__proxy_params = array();

    var $_soap_transport = null;

    /**
     * Constructor.
     *
     * @access public
     *
     * @param string $endpoint     An URL.
     * @param boolean $wsdl        Whether the endpoint is a WSDL file.
     * @param string $portName
     * @param array $proxy_params  Options for the HTTP_Request class (see
     *                             HTTP/Request.php)
     */
    function ilBMFClient($endpoint, $wsdl = false, $portName = false,
                         $proxy_params = array())
    {
        parent::ilBMFBase('Client');

        $this->_endpoint = $endpoint;
        $this->_portName = $portName;
        $this->__proxy_params = $proxy_params;

        // This hack should perhaps be removed as it might cause unexpected
        // behaviour.
        $wsdl = $wsdl
            ? $wsdl
            : strtolower(substr($endpoint, -4)) == 'wsdl';

        // make values
        if ($wsdl) {
            $this->__endpointType = 'wsdl';
            // instantiate wsdl class
            $this->_wsdl =& new ilBMFWSDL($this->_endpoint,
                                          $this->__proxy_params);
            if ($this->_wsdl->fault) {
                $this->_raiseSoapFault($this->_wsdl->fault);
            }
        }
    }

    function _reset()
    {
        $this->xml = null;
        $this->wire = null;
        $this->__last_request = null;
        $this->__last_response = null;
        $this->headersIn = null;
        $this->headersOut = null;
    }

    /**
     * Sets the character encoding.
     *
     * Limited to 'UTF-8', 'US_ASCII' and 'ISO-8859-1'.
     *
     * @access public
     *
     * @param string encoding
     *
     * @return mixed  ilBMFFault on error.
     */
    function setEncoding($encoding)
    {
        if (in_array($encoding, $this->_encodings)) {
            $this->_encoding = $encoding;
            return;
        }
        return $this->_raiseSoapFault('Invalid Encoding');
    }

    /**
     * Adds a header to the envelope.
     *
     * @access public
     *
     * @param SOAP_Header $soap_value  A SOAP_Header or an array with the
     *                                 elements 'name', 'namespace',
     *                                 'mustunderstand', and 'actor' to send
     *                                 as a header.
     */
    function addHeader(&$soap_value)
    {
        // Add a new header to the message.
        if (is_a($soap_value, 'ilBMFHeader')) {
            $this->headersOut[] =& $soap_value;
        } elseif (is_array($soap_value)) {
            // name, value, namespace, mustunderstand, actor
            $this->headersOut[] =& new ilBMFHeader($soap_value[0],
                                                   null,
                                                   $soap_value[1],
                                                   $soap_value[2],
                                                   $soap_value[3]);;
        } else {
            $this->_raiseSoapFault('Invalid parameter provided to addHeader().  Must be an array or a SOAP_Header.');
        }
    }

    /**
     * Calls a method on the SOAP endpoint.
     *
     * The namespace parameter is overloaded to accept an array of options
     * that can contain data necessary for various transports if it is used as
     * an array, it MAY contain a namespace value and a soapaction value.  If
     * it is overloaded, the soapaction parameter is ignored and MUST be
     * placed in the options array.  This is done to provide backwards
     * compatibility with current clients, but may be removed in the future.
     * The currently supported values are:<pre>
     *   namespace
     *   soapaction
     *   timeout (HTTP socket timeout)
     *   transfer-encoding (SMTP, Content-Transfer-Encoding: header)
     *   from (SMTP, From: header)
     *   subject (SMTP, Subject: header)
     *   headers (SMTP, hash of extra SMTP headers)
     * </pre>
     *
     * @access public
     *
     * @param string $method           The method to call.
     * @param array $params            The method parameters.
     * @param string|array $namespace  Namespace or hash with options.
     * @param string $soapAction
     *
     * @return mixed  The method result or a ilBMFFault on error.
     */
    function &call($method, &$params, $namespace = false, $soapAction = false)
    {
        $this->headersIn = null;
        $this->__last_request = null;
        $this->__last_response = null;
        $this->wire = null;
        $this->xml = null;

        $soap_data =& $this->__generate($method, $params, $namespace, $soapAction);
        if (PEAR::isError($soap_data)) {
            $fault =& $this->_raiseSoapFault($soap_data);
            return $fault;
        }

        // __generate() may have changed the endpoint if the WSDL has more
        // than one service, so we need to see if we need to generate a new
        // transport to hook to a different URI.  Since the transport protocol
        // can also change, we need to get an entirely new object.  This could
        // probably be optimized.
        if (!$this->_soap_transport ||
            $this->_endpoint != $this->_soap_transport->url) {
            $this->_soap_transport =& ilBMFTransport::getTransport($this->_endpoint);
            if (PEAR::isError($this->_soap_transport)) {
                $fault =& $this->_soap_transport;
                $this->_soap_transport = null;
                $fault =& $this->_raiseSoapFault($fault);
                return $fault;
            }
        }
        $this->_soap_transport->encoding = $this->_encoding;

        // Send the message.
        $transport_options = array_merge_recursive($this->__proxy_params,
                                                   $this->__options);
        $this->xml = $this->_soap_transport->send($soap_data, $transport_options);

        // Save the wire information for debugging.
        if ($this->__options['trace'] > 0) {
            $this->__last_request =& $this->_soap_transport->outgoing_payload;
            $this->__last_response =& $this->_soap_transport->incoming_payload;
            $this->wire = $this->__get_wire();
        }
        if ($this->_soap_transport->fault) {
            $fault =& $this->_raiseSoapFault($this->xml);
            return $fault;
        }

        $this->__attachments =& $this->_soap_transport->attachments;
        $this->__result_encoding = $this->_soap_transport->result_encoding;

        if (isset($this->__options['result']) &&
            $this->__options['result'] != 'parse') {
            return $this->xml;
        }

        $result = &$this->__parse($this->xml, $this->__result_encoding, $this->__attachments);

        return $result;
    }

    /**
     * Sets an option to use with the transport layers.
     *
     * For example:
     * <code>
     * $soapclient->setOpt('curl', CURLOPT_VERBOSE, 1)
     * </code>
     * to pass a specific option to curl if using an SSL connection.
     *
     * @access public
     *
     * @param string $category  Category to which the option applies or option
     *                          name.
     * @param string $option    An option name if $category is a category name,
     *                          an option value if $category is an option name.
     * @param string $value     An option value if $category is a category
     *                          name.
     */
    function setOpt($category, $option, $value = null)
    {
        if (!is_null($value)) {
            if (!isset($this->__options[$category])) {
                $this->__options[$category] = array();
            }
            $this->__options[$category][$option] = $value;
        } else {
            $this->__options[$category] = $option;
        }
    }

    /**
     * Call method supporting the overload extension.
     *
     * If the overload extension is loaded, you can call the client class with
     * a soap method name:
     * <code>
     * $soap = new ilBMFClient(....);
     * $value = $soap->getStockQuote('MSFT');
     * </code>
     *
     * @access public
     *
     * @param string $method        The method to call.
     * @param array $params         The method parameters.
     * @param string $return_value  Will get the method's return value
     *                              assigned.
     *
     * @return boolean  Always true.
     */
    function _call($method, $params, &$return_value)
    {
        // Overloading lowercases the method name, we need to look into the
        // wsdl and try to find the correct method name to get the correct
        // case for the call.
        if ($this->_wsdl) {
            $this->_wsdl->matchMethod($method);
        }

        $return_value =& $this->call($method, $params);

        return true;
    }

    function &__getlastrequest()
    {
        $request =& $this->__last_request;
        return $request;
    }

    function &__getlastresponse()
    {
        $response =& $this->__last_response;
        return $response;
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

    function &__generate($method, &$params, $namespace = false,
                         $soapAction = false)
    {
        $this->fault = null;
        $this->__options['input']='parse';
        $this->__options['result']='parse';
        $this->__options['parameters'] = false;

        if ($params && gettype($params) != 'array') {
            $params = array($params);
        }

        if (gettype($namespace) == 'array') {
            foreach ($namespace as $optname => $opt) {
                $this->__options[strtolower($optname)] = $opt;
            }
            if (isset($this->__options['namespace'])) {
                $namespace = $this->__options['namespace'];
            } else {
                $namespace = false;
            }
        } else {
            // We'll place $soapAction into our array for usage in the
            // transport.
            $this->__options['soapaction'] = $soapAction;
            $this->__options['namespace'] = $namespace;
        }

        if ($this->__endpointType == 'wsdl') {
            $this->_setSchemaVersion($this->_wsdl->xsd);

            // Get port name.
            if (!$this->_portName) {
                $this->_portName = $this->_wsdl->getPortName($method);
            }
            if (PEAR::isError($this->_portName)) {
                $fault =& $this->_raiseSoapFault($this->_portName);
                return $fault;
            }

            // Get endpoint.
            $this->_endpoint = $this->_wsdl->getEndpoint($this->_portName);
            if (PEAR::isError($this->_endpoint)) {
                $fault =& $this->_raiseSoapFault($this->_endpoint);
                return $fault;
            }

            // Get operation data.
            $opData = $this->_wsdl->getOperationData($this->_portName, $method);

            if (PEAR::isError($opData)) {
                $fault =& $this->_raiseSoapFault($opData);
                return $fault;
            }
            $namespace = $opData['namespace'];
            $this->__options['style'] = $opData['style'];
            $this->__options['use'] = $opData['input']['use'];
            $this->__options['soapaction'] = $opData['soapAction'];

            // Set input parameters.
            if ($this->__options['input'] == 'parse') {
                $this->__options['parameters'] = $opData['parameters'];
                $nparams = array();
                if (isset($opData['input']['parts']) &&
                    count($opData['input']['parts'])) {
                    $i = 0;
                    foreach ($opData['input']['parts'] as $name => $part) {
                        $xmlns = '';
                        $attrs = array();
                        // Is the name a complex type?
                        if (isset($part['element'])) {
                            $xmlns = $this->_wsdl->namespaces[$part['namespace']];
                            $part = $this->_wsdl->elements[$part['namespace']][$part['type']];
                            $name = $part['name'];
                        }
                        if (isset($params[$name]) ||
                            $this->_wsdl->getDataHandler($name, $part['namespace'])) {
                            $nparams[$name] =& $params[$name];
                        } else {
                            // We now force an associative array for
                            // parameters if using WSDL.
                            $fault =& $this->_raiseSoapFault("The named parameter $name is not in the call parameters.");
                            return $fault;
                        }
                        if (gettype($nparams[$name]) != 'object' ||
                            !is_a($nparams[$name], 'ilBMFValue')) {
                            // Type is likely a qname, split it apart, and get
                            // the type namespace from WSDL.
                            $qname =& new QName($part['type']);
                            if ($qname->ns) {
                                $type_namespace = $this->_wsdl->namespaces[$qname->ns];
                            } elseif (isset($part['namespace'])) {
                                $type_namespace = $this->_wsdl->namespaces[$part['namespace']];
                            } else {
                                $type_namespace = null;
                            }
                            $qname->namespace = $type_namespace;
                            $type = $qname->name;
                            $pqname = $name;
                            if ($xmlns) {
                                $pqname = '{' . $xmlns . '}' . $name;
                            }
                            $nparams[$name] =& new ilBMFValue($pqname,
                                                              $qname->fqn(),
                                                              $nparams[$name],
                                                              $attrs);
                        } else {
                            // WSDL fixups to the SOAP value.
                        }
                    }
                }
                $params =& $nparams;
                unset($nparams);
            }
        } else {
            $this->_setSchemaVersion(SOAP_XML_SCHEMA_VERSION);
        }

        // Serialize the message.
        $this->_section5 = (!isset($this->__options['use']) ||
                            $this->__options['use'] != 'literal');

        if (!isset($this->__options['style']) ||
            $this->__options['style'] == 'rpc') {
            $this->__options['style'] = 'rpc';
            $this->docparams = true;
            $mqname =& new QName($method, $namespace);
            $methodValue =& new ilBMFValue($mqname->fqn(), 'Struct', $params);
            $soap_msg = $this->_makeEnvelope($methodValue,
                                             $this->headersOut,
                                             $this->_encoding,
                                             $this->__options);
        } else {
            if (!$params) {
                $mqname =& new QName($method, $namespace);
                $mynull = null;
                $params =& new ilBMFValue($mqname->fqn(), 'Struct', $mynull);
            } elseif ($this->__options['input'] == 'parse') {
                if (is_array($params)) {
                    $nparams = array();
                    $keys = array_keys($params);
                    foreach ($keys as $k) {
                        if (gettype($params[$k]) != 'object') {
                            $nparams[] =& new ilBMFValue($k,
                                                         false,
                                                         $params[$k]);
                        } else {
                            $nparams[] =& $params[$k];
                        }
                    }
                    $params =& $nparams;
                }
                if ($this->__options['parameters']) {
                    $mqname =& new QName($method, $namespace);
                    $params =& new ilBMFValue($mqname->fqn(),
                                              'Struct',
                                              $params);
                }
            }
            $soap_msg = $this->_makeEnvelope($params,
                                             $this->headersOut,
                                             $this->_encoding,
                                             $this->__options);
        }
        unset($this->headersOut);

        if (PEAR::isError($soap_msg)) {
            $fault =& $this->_raiseSoapFault($soap_msg);
            return $fault;
        }

        // Handle MIME or DIME encoding.
        // TODO: DIME encoding should move to the transport, do it here for
        // now and for ease of getting it done.
        if (count($this->__attachments)) {
            if ((isset($this->__options['attachments']) &&
                 $this->__options['attachments'] == 'Mime') ||
                isset($this->__options['Mime'])) {
                $soap_msg =& $this->_makeMimeMessage($soap_msg,
                                                     $this->_encoding);
            } else {
                // default is dime
                $soap_msg =& $this->_makeDIMEMessage($soap_msg,
                                                     $this->_encoding);
                $this->__options['headers']['Content-Type'] = 'application/dime';
            }
            if (PEAR::isError($soap_msg)) {
                $fault =& $this->_raiseSoapFault($soap_msg);
                return $fault;
            }
        }

        // Instantiate client.
        if (is_array($soap_msg)) {
            $soap_data =& $soap_msg['body'];
            if (count($soap_msg['headers'])) {
                if (isset($this->__options['headers'])) {
                    $this->__options['headers'] = array_merge($this->__options['headers'], $soap_msg['headers']);
                } else {
                    $this->__options['headers'] = $soap_msg['headers'];
                }
            }
        } else {
            $soap_data =& $soap_msg;
        }

        return $soap_data;
    }

    function &__parse(&$response, $encoding, &$attachments)
    {
        // Parse the response.
        $response =& new ilBMFParser($response, $encoding, $attachments);
        if ($response->fault) {
            $fault =& $this->_raiseSoapFault($response->fault);
            return $fault;
        }

        // Return array of parameters.
        $return =& $response->getResponse();
        $headers =& $response->getHeaders();
        if ($headers) {
            $this->headersIn =& $this->__decodeResponse($headers, false);
        }

        $decoded = &$this->__decodeResponse($return);
        return $decoded;
    }

    function &__decodeResponse(&$response, $shift = true)
    {
        if (!$response) {
            $decoded = null;
            return $decoded;
        }

        // Check for valid response.
        if (PEAR::isError($response)) {
            $fault =& $this->_raiseSoapFault($response);
            return $fault;
        } elseif (!is_a($response, 'ilbmfvalue')) {
            $fault =& $this->_raiseSoapFault("Didn't get ilBMFValue object back from client");
            return $fault;
        }

        // Decode to native php datatype.
        $returnArray =& $this->_decode($response);

        // Fault?
        if (PEAR::isError($returnArray)) {
            $fault =& $this->_raiseSoapFault($returnArray);
            return $fault;
        }

        if (is_object($returnArray) &&
            strcasecmp(get_class($returnArray), 'stdClass') == 0) {
            $returnArray = get_object_vars($returnArray);
        }
        if (is_array($returnArray)) {
            if (isset($returnArray['faultcode']) ||
                isset($returnArray['SOAP-ENV:faultcode'])) {
                $faultcode = $faultstring = $faultdetail = $faultactor = '';
                foreach ($returnArray as $k => $v) {
                    if (stristr($k, 'faultcode')) $faultcode = $v;
                    if (stristr($k, 'faultstring')) $faultstring = $v;
                    if (stristr($k, 'detail')) $faultdetail = $v;
                    if (stristr($k, 'faultactor')) $faultactor = $v;
                }
                $fault =& $this->_raiseSoapFault($faultstring, $faultdetail, $faultactor, $faultcode);
                return $fault;
            }
            // Return array of return values.
            if ($shift && count($returnArray) == 1) {
                $decoded = array_shift($returnArray);
                return $decoded;
            }
            return $returnArray;
        }
        return $returnArray;
    }

    function __get_wire()
    {
        if ($this->__options['trace'] > 0 &&
            ($this->__last_request || $this->__last_response)) {
            return "OUTGOING:\n\n" .
                $this->__last_request .
                "\n\nINCOMING\n\n" .
                preg_replace("/></",">\r\n<", $this->__last_response);
        }

        return null;
    }

}
