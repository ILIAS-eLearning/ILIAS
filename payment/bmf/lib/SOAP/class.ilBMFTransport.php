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

require_once dirname(__FILE__).'/class.ilBMFBase.php';

/**
* SOAP Transport Layer
*
* This layer can use different protocols dependant on the endpoint url provided
* no knowlege of the SOAP protocol is available at this level
* no knowlege of the transport protocols is available at this level
*
* @access   public
* @version  $Id$
* @package  ilBMF::Transport
* @author   Shane Caraveo <shane@php.net>
*/
class ilBMFTransport extends ilBMFBase
{

    /**
    * Transport object - build using the constructor as a factory
    * 
    * @var  object  ilBMFTransport_SMTP|HTTP
    */
    var $transport = NULL;
    
    var $encoding = SOAP_DEFAULT_ENCODING;
    var $result_encoding = SOAP_DEFAULT_ENCODING;
    /**
    * ilBMF::Transport constructor
    *
    * @param string $url   soap endpoint url
    *
    * @access public
    */
    function ilBMFTransport($url, $encoding = SOAP_DEFAULT_ENCODING)
    {
        parent::ilBMFBase('TRANSPORT');

        $urlparts = @parse_url($url);
        $this->encoding = $encoding;
        
        if (strcasecmp($urlparts['scheme'], 'http') == 0 || strcasecmp($urlparts['scheme'], 'https') == 0) {
            include_once(dirname(__FILE__).'/Transport/class.ilBMFTransport_HTTP.php');
            $this->transport = new ilBMFTransport_HTTP($url, $encoding);
            return;
        } else if (strcasecmp($urlparts['scheme'], 'mailto') == 0) {
            include_once(dirname(__FILE__).'/Transport/lass.ilBMFTransport_SMTP.php');
            $this->transport = new ilBMFTransport_SMTP($url, $encoding);
            return;
        }
        $this->_raiseSoapFault("No Transport for {$urlparts['scheme']}");
    }
    
    /**
    * send a soap package, get a soap response
    *
    * @param string &$soap_data   soap data to be sent (in xml)
    * @param string $action SOAP Action
    * @param int $timeout protocol timeout in seconds
    *
    * @return string &$response   soap response (in xml)
    * @access public
    */
    function &send(&$soap_data, /*array*/ $options = NULL)
    {
        if (!$this->transport) {
            return $this->fault;
        }
        
        $response = $this->transport->send($soap_data, $options);
        if (PEAR::isError($response)) {
            return $this->_raiseSoapFault($response);
        }
        $this->result_encoding = $this->transport->result_encoding;
        #echo "\n OUTGOING: ".$this->transport->outgoing_payload."\n\n";
        #echo "\n INCOMING: ".preg_replace("/></",">\n<!--CRLF added-->",$this->transport->incoming_payload)."\n\n";
        return $response;
    }

} // end ilBMFTransport
?>
