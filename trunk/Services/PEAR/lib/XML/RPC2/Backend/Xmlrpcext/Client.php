<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */

// LICENSE AGREEMENT. If folded, press za here to unfold and read license {{{ 

/**
* +-----------------------------------------------------------------------------+
* | Copyright (c) 2004-2006 Sergio Goncalves Carvalho                                |
* +-----------------------------------------------------------------------------+
* | This file is part of XML_RPC2.                                              |
* |                                                                             |
* | XML_RPC2 is free software; you can redistribute it and/or modify            |
* | it under the terms of the GNU Lesser General Public License as published by |
* | the Free Software Foundation; either version 2.1 of the License, or         |
* | (at your option) any later version.                                         |
* |                                                                             |
* | XML_RPC2 is distributed in the hope that it will be useful,                 |
* | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
* | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
* | GNU Lesser General Public License for more details.                         |
* |                                                                             |
* | You should have received a copy of the GNU Lesser General Public License    |
* | along with XML_RPC2; if not, write to the Free Software                     |
* | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA                    |
* | 02111-1307 USA                                                              |
* +-----------------------------------------------------------------------------+
* | Author: Sergio Carvalho <sergio.carvalho@portugalmail.com>                  |
* +-----------------------------------------------------------------------------+
*
* @category   XML
* @package    XML_RPC2
* @author     Sergio Carvalho <sergio.carvalho@portugalmail.com>  
* @copyright  2004-2006 Sergio Carvalho
* @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
* @version    CVS: $Id: Client.php,v 1.16 2007/12/05 17:00:30 sergiosgc Exp $
* @link       http://pear.php.net/package/XML_RPC2
*/

// }}}

// dependencies {{{
require_once 'XML/RPC2/Exception.php';
require_once 'XML/RPC2/Client.php';
require_once 'XML/RPC2/Util/HTTPRequest.php';
//}}}

/**
 * XML_RPC client backend class. This backend class uses the XMLRPCext extension to execute the call.
 *
 * @category   XML
 * @package    XML_RPC2
 * @author     Sergio Carvalho <sergio.carvalho@portugalmail.com>  
 * @copyright  2004-2006 Sergio Carvalho
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link       http://pear.php.net/package/XML_RPC2 
 */
class XML_RPC2_Backend_Xmlrpcext_Client extends XML_RPC2_Client
{
    
    // {{{ constructor
    
    /**
     * Construct a new XML_RPC2_Client PHP Backend.
     *
     * A URI must be provided (e.g. http://xmlrpc.example.com/1.0/). 
     * Optionally, some options may be set.
     *
     * @param string URI for the XML-RPC server
     * @param array (optional) Associative array of options
     */
    public function __construct($uri, $options = array())
    {
        parent::__construct($uri, $options);
    }
    
    // }}}
    // {{{ remoteCall()
    
    /**
     * remoteCall executes the XML-RPC call, and returns the result
     *
     * NB : The '___' at the end of the method name is to avoid collisions with
     * XMLRPC __call() 
     *
     * @param   string      Method name
     * @param   array       Parameters
     */
    public function remoteCall___($methodName, $parameters)
    {
		$tmp = xmlrpc_encode_request($this->prefix . $methodName, $parameters, array('encoding' => $this->encoding));
        if ($this->uglyStructHack) {
	        // ugly hack because of http://bugs.php.net/bug.php?id=21949
	        // see XML_RPC2_Backend_Xmlrpcext_Value::createFromNative() from more infos
	        $request = preg_replace('~<name>xml_rpc2_ugly_struct_hack_(.*)</name>~', '<name>\1</name>', $tmp);
        } else {
            $request = $tmp;
        }
        $uri = $this->uri;
        $options = array(
            'encoding' => $this->encoding,
            'proxy' => $this->proxy,
            'sslverify' => $this->sslverify
        );
        $httpRequest = new XML_RPC2_Util_HTTPRequest($uri, $options);
        $httpRequest->setPostData($request);
        $httpRequest->sendRequest();
		$body = $httpRequest->getBody();
        if ($this->debug) {
            $this->displayDebugInformations___($request, $body);
        }
        $result = xmlrpc_decode($body, $this->encoding);
        /* Commented due to change in behaviour from xmlrpc_decode. It does not return faults now
        if ($result === false || is_null($result)) {
            if ($this->debug) {
                print "XML_RPC2_Exception : unable to decode response !";
            }
            throw new XML_RPC2_Exception('Unable to decode response');
        }
        */
        if (xmlrpc_is_fault($result)) {
            if ($this->debug) {
                print "XML_RPC2_FaultException(${result['faultString']}, ${result['faultCode']})";
            }
            throw new XML_RPC2_FaultException($result['faultString'], $result['faultCode']);
        }
        if ($this->debug) {
            $this->displayDebugInformations2___($result);
        }
        return $result;
    }
    
    // }}}
    
}

?>
