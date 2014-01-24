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
* @version    CVS: $Id: Client.php,v 1.14 2008/09/10 18:50:31 sergiosgc Exp $
* @link       http://pear.php.net/package/XML_RPC2
*/

// }}}

// dependencies {{{
require_once 'XML/RPC2/Exception.php';
require_once 'XML/RPC2/Backend.php';
// }}}

/**
 * XML_RPC client class. Use this class to access remote methods.
 * 
 * To use this class, construct it providing the server URL and method prefix. 
 * Then, call remote methods on the new instance as if they were local.
 * 
 * Example:
 * <code>
 *  require_once 'XML_RPC2/Client.php';
 * 
 *  $client = XML_RPC2_Client('http://xmlrpc.example.com/1.0/', 'example.');
 *  $result = $client->hello('Sergio');
 *  print($result);
 * </code>
 * 
 * The above example will call the example.hello method on the xmlrpc.example.com
 * server, under the /1.0/ URI. 
 * 
 * @category   XML
 * @package    XML_RPC2
 * @author     Sergio Carvalho <sergio.carvalho@portugalmail.com>  
 * @copyright  2004-2006 Sergio Carvalho
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link       http://pear.php.net/package/XML_RPC2
 */
abstract class XML_RPC2_Client 
{
    const VERSION = '1.0.4';
    // {{{ properties
    
    /**
     * uri Field (holds the uri for the XML_RPC server)
     *
     * @var string
     */
    protected $uri = null;
    
    /**
     * proxy Field (holds the proxy server data)
     *
     * @var string
     */
    protected $proxy = null;
    
    /**
     * Holds the prefix to prepend to method names
     *
     * @var string
     */
    protected $prefix = null;
    
    /** 
     * Holds the debug flag 
     *
     * @var boolean
     */
    protected $debug = false;
    
    /**
     * Hold the encoding of the client request
     *
     * @var string
     */
    protected $encoding = 'iso-8859-1';
    
    /**
     * Hold the SSL verify flag
     *
     * @var boolean
     */
    protected $sslverify = true;
        
    // }}}
    // {{{ remoteCall___()
    
    /**
     * ugly hack flag to avoid http://bugs.php.net/bug.php?id=21949
     * 
     * see XML_RPC2_Backend_Xmlrpcext_Value::createFromNative() from more infos
     */
    protected $uglyStructHack = true;
    
    /**
     * remoteCall executes the XML-RPC call, and returns the result
     * 
     * NB : The '___' at the end of the method name is to avoid collisions with
     * XMLRPC __call() 
     *
     * @param   string      Method name
     * @param   array       Parameters
     */
    public abstract function remoteCall___($methodName, $parameters);
    
    // }}}
    // {{{ constructor
    
    /**
     * Construct a new XML_RPC2_Client.
     *
     * To create a new XML_RPC2_Client, a URI must be provided (e.g. http://xmlrpc.example.com/1.0/). 
     * Optionally, some options may be set as an associative array. Accepted keys are :
     * 'prefix', 'proxy', 'debug' => see correspondant property to get more informations
     * 
     * @param string URI for the XML-RPC server
     * @param array (optional) Associative array of options
     */
    protected function __construct($uri, $options = array())
    {
        if (!$uriParse = parse_url($uri)) {
            throw new XML_RPC2_InvalidUriException(sprintf('Client URI \'%s\' is not valid', $uri));
        }
        $this->uri = $uri;
        if (isset($options['prefix'])) {
            if (!($this->testMethodName___($options['prefix']))) {
                throw new XML_RPC2_InvalidPrefixException(sprintf('Prefix \'%s\' is not valid', $options['prefix']));
            }
            $this->prefix = $options['prefix'];
        }
        if (isset($options['proxy'])) {
            if (!$proxyParse = parse_url($options['proxy'])) {
                throw new XML_RPC2_InvalidProxyException(sprintf('Proxy URI \'%s\' is not valid', $options['proxy']));
            }
            $this->proxy = $options['proxy'];
        }
        if (isset($options['debug'])) {
            if (!(is_bool($options['debug']))) {
                throw new XML_RPC2_InvalidDebugException(sprintf('Debug \'%s\' is not valid', $options['debug']));
            }
            $this->debug = $options['debug'];
        }
        if (isset($options['encoding'])) {
            // TODO : control & exception
            $this->encoding = $options['encoding'];
        }
        if (isset($options['uglyStructHack'])) {  
            $this->uglyStructHack = $options['uglyStructHack'];
        }
        if (isset($options['sslverify'])) {
            if (!(is_bool($options['sslverify']))) {
                throw new XML_RPC2_InvalidSslverifyException(sprintf('SSL verify \'%s\' is not valid', $options['sslverify']));
            }
            $this->sslverify = $options['sslverify'];
        }
    }
    
    // }}}
    // {{{ create()
    
    /**
     * Factory method to select, create and return a XML_RPC2_Client backend
     *
     * To create a new XML_RPC2_Client, a URI must be provided (e.g. http://xmlrpc.example.com/1.0/). 
     * 
     * Optionally, some options may be set.
     *
     * @param string URI for the XML-RPC server
     * @param array (optional) associative array of options (see constructor)
     */
    public static function create($uri, $options = array())
    {
        if (isset($options['backend'])) {
            XML_RPC2_Backend::setBackend($options['backend']);
        }
        $backend = XML_RPC2_Backend::getClientClassname();
        return new $backend($uri, $options);
    }
    
    // }}}
    // {{{ __call()

    /**
     * __call Catchall. This method catches remote method calls and provides for remote forwarding.
     *
     * If the parameters are native types, this method will use XML_RPC_Value::createFromNative to 
     * convert it into an XML-RPC type. Whenever a parameter is already an instance of XML_RPC_Value
     * it will be used as provided. It follows that, in situations when XML_RPC_Value::createFromNative
     * proves inacurate -- as when encoding DateTime values -- you should present an instance of 
     * XML_RPC_Value in lieu of the native parameter.
     *
     * @param   string      Method name
     * @param   array       Parameters
     * @return  mixed       The call result, already decoded into native types
     */
    public function __call($methodName, $parameters)
    {
        $args = array($methodName, $parameters);
        return @call_user_func_array(array($this, 'remoteCall___'), $args);
    }
   
    // }}}
    // {{{ displayDebugInformations___()
	
    /**
     * Display debug informations
     *
     * NB : The '___' at the end of the method name is to avoid collisions with
     * XMLRPC __call() 
     * 
     * @param string $request XML client request
     * @param string $body XML server response
     */
    protected function displayDebugInformations___($request, $body) 
    {
        print '<pre>';
        print "***** Request *****\n";
        print htmlspecialchars($request);
        print "***** End Of request *****\n\n";
        print "***** Server response *****\n";
        print htmlspecialchars($body);
        print "\n***** End of server response *****\n\n";
    }
    
    // }}}
    // {{{ displayDebugInformations2___()
	
    /**
     * Display debug informations (part 2)
     *
     * NB : The '___' at the end of the method name is to avoid collisions with
     * XMLRPC __call() 
     * 
     * @param mixed $result decoded server response
     */
    protected function displayDebugInformations2___($result)
    {
        print "***** Decoded result *****\n";
        print_r($result);
        print "\n***** End of decoded result *****";
        print '</pre>';
    }
    
    // }}}
    // {{{ testMethodName___()
    
    /**
     * Return true is the given method name is ok with XML/RPC spec. 
     *
     * NB : The '___' at the end of the method name is to avoid collisions with
     * XMLRPC __call() 
     * 
     * @param string $methodName method name
     * @return boolean true if ok
     */
    protected function testMethodName___($methodName)
    {
        return (preg_match('~^[a-zA-Z0-9_.:/]*$~', $methodName)); 
    }
        
}

?>
