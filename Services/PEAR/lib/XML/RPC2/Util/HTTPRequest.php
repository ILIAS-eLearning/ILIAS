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
* @version    CVS: $Id: HTTPRequest.php,v 1.8 2008/09/10 18:50:31 sergiosgc Exp $
* @link       http://pear.php.net/package/XML_RPC2
*/

// }}}

// dependencies {{{
require_once 'XML/RPC2/Exception.php';
require_once 'XML/RPC2/Client.php';
// }}}

/**
 * XML_RPC utility HTTP request class. This class mimics a subset of PEAR's HTTP_Request
 * and is to be refactored out of the package once HTTP_Request releases an E_STRICT version.
 * 
 * @category   XML
 * @package    XML_RPC2
 * @author     Sergio Carvalho <sergio.carvalho@portugalmail.com>  
 * @copyright  2004-2006 Sergio Carvalho
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link       http://pear.php.net/package/XML_RPC2
 */
class XML_RPC2_Util_HTTPRequest
{

    // {{{ properties
    
    /**
     * proxy field
     *
     * @var string
     */
    private $_proxy = null;
    
    /**
     * proxyauth field
     *
     * @var string
     */
    private $_proxyAuth = null;
    
    /**
     * postData field 
     *
     * @var string
     */
    private $_postData;
               
    /**
     * uri field 
     *
     * @var array
     */
    private $_uri;
    
    /**
     * encoding for the request
     *
     * @var string
     */
    private $_encoding='iso-8859-1';
    
    /**
     * SSL verify flag
     *
     * @var boolean
     */
    private $_sslverify=true;
    
    // }}}
    // {{{ getBody()

    /**
     * body field getter
     *
     * @return string body value
     */
    public function getBody() 
    {
        return $this->_body;
    }
            
    // }}}
    // {{{ setPostData()
    
    /**
     * postData field setter
     *
     * @param string postData value
     */
    public function setPostData($value) 
    {
        $this->_postData = $value;
    }
    
    // }}}
    // {{{ constructor
    
    /**
    * Constructor
    *
    * Sets up the object
    * @param    string  The uri to fetch/access
    * @param    array   Associative array of parameters which can have the following keys:
    * <ul>
    *   <li>proxy          - Proxy (string)</li>
    *   <li>encoding       - The request encoding (string)</li>
    * </ul>
    * @access public
    */
    public function __construct($uri = '', $params = array())
    {
        if (!preg_match('/(https?:\/\/)(.*)/', $uri)) throw new XML_RPC2_Exception('Unable to parse URI');
        $this->_uri = $uri;
        if (isset($params['encoding'])) {
            $this->_encoding = $params['encoding'];
        }
        if (isset($params['proxy'])) {
            $proxy = $params['proxy'];
            $elements = parse_url($proxy);
            if (is_array($elements)) {
                if ((isset($elements['scheme'])) and (isset($elements['host']))) { 
                    $this->_proxy = $elements['scheme'] . '://' . $elements['host'];
                }
                if (isset($elements['port'])) {
                    $this->_proxy = $this->_proxy . ':' . $elements['port'];
                }
                if ((isset($elements['user'])) and (isset($elements['pass']))) {
                    $this->_proxyAuth = $elements['user'] . ':' . $elements['pass'];
                }
            }
        }
        if (isset($params['sslverify'])) {
            $this->_sslverify = $params['sslverify'];
        }
    }
    
    // }}}
    // {{{ sendRequest()
    
    /**
    * Sends the request
    *
    * @access public
    * @return mixed  PEAR error on error, true otherwise
    */
    public function sendRequest()
    {
        if (!function_exists('curl_init') &&
            !( // TODO Use PEAR::loadExtension once PEAR passes PHP5 unit tests (E_STRICT compliance, namely)
              @dl('php_curl' . PHP_SHLIB_SUFFIX)    || @dl('curl' . PHP_SHLIB_SUFFIX)
             )) {
            throw new XML_RPC2_CurlException('cURI extension is not present and load failed');
        }
        if ($ch = curl_init()) {
            if (
                (is_null($this->_proxy)     || curl_setopt($ch, CURLOPT_PROXY, $this->_proxy)) &&
                (is_null($this->_proxyAuth) || curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->_proxyAuth)) &&
                curl_setopt($ch, CURLOPT_URL, $this->_uri) &&
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE) &&
                curl_setopt($ch, CURLOPT_POST, 1) &&
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->_sslverify) &&
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml; charset='.$this->_encoding, 'User-Agent: PEAR_XML_RCP2/' . XML_RPC2_Client::VERSION)) &&
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_postData)
            ) {
                $result = curl_exec($ch);
                if (($errno = curl_errno($ch)) != 0) {
                    throw new XML_RPC2_CurlException("Curl returned non-null errno $errno:" . curl_error($ch));
                }
                $info = curl_getinfo($ch);
                if ($info['http_code'] != 200) {
                    throw new XML_RPC2_ReceivedInvalidStatusCodeException('Curl returned non 200 HTTP code: ' . $info['http_code'] . '. Response body:' . $result);
                }
            } else {
                throw new XML_RPC2_CurlException('Unable to setup curl');
            }
        } else {
            throw new XML_RPC2_CurlException('Unable to init curl');
        }
        $this->_body = $result;        
        return true;
    }
    
    // }}}

}

?>
