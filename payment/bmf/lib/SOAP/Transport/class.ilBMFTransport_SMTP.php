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
// Status: rough draft, untested
//
// TODO:
//  switch to pear mail stuff
//  smtp authentication
//  smtp ssl support
//  ability to define smtp options (encoding, from, etc.)
//

require_once dirname(__FILE__).'/../class.ilBMFBase.php';
require_once 'Mail/smtp.php';

/**
*  SMTP Transport for SOAP
*
* implements SOAP-SMTP as defined at
* http://www.pocketsoap.com/specs/smtpbinding/
*
* TODO: use PEAR smtp and Mime classes
*
* @access public
* @version $Id$
* @package ilBMF::Transport::SMTP
* @author Shane Caraveo <shane@php.net>
*/
class ilBMFTransport_SMTP extends ilBMFBase
{
    var $credentials = '';
    var $timeout = 4; // connect timeout
    var $urlparts = NULL;
    var $url = '';
    var $incoming_payload = '';
    var $_userAgent = SOAP_LIBRARY_NAME;
    var $encoding = SOAP_DEFAULT_ENCODING;
    var $host = '127.0.0.1';
    var $port = 25;
    var $auth = NULL;
    /**
    * ilBMFTransport_SMTP Constructor
    *
    * @param string $URL    mailto:address
    *
    * @access public
    */
    function ilBMFTransport_SMTP($URL, $encoding='US-ASCII')
    {
        parent::ilBMFBase('SMTP');
        $this->encoding = $encoding;
        $this->urlparts = @parse_url($URL);
        $this->url = $URL;
    }
    
    /**
    * send and receive soap data
    *
    * @param string &$msg       outgoing post data
    * @param string $action      SOAP Action header data
    * @param int $timeout  socket timeout, default 0 or off
    *
    * @return string &$response   response data, minus http headers
    * @access public
    */
    function send(&$msg,  /*array*/ $options = NULL)
    {
        $this->outgoing_payload = &$msg;
        if (!$this->_validateUrl()) {
            return $this->fault;
        }
        if (!$options || !array_key_exists('from',$options)) {
            return $this->_raiseSoapFault("No FROM address to send message with");
        }
        
        if (isset($options['host'])) $this->host = $options['host'];
        if (isset($options['port'])) $this->port = $options['port'];
        if (isset($options['auth'])) $this->auth = $options['auth'];
        if (isset($options['username'])) $this->username = $options['username'];
        if (isset($options['password'])) $this->password = $options['password'];
        
        $headers = array();
        $headers['From'] = $options['from'];
        $headers['X-Mailer'] = $this->_userAgent;
        $headers['MIME-Version'] = '1.0';
        $headers['Message-ID'] = md5(time()).'.soap@'.$this->host;
        $headers['To'] = $this->urlparts['path'];
        if (array_key_exists('soapaction', $options)) {
            $headers['Soapaction'] = "\"{$options['soapaction']}\"";
        }
        
        if (isset($options['headers']))
            $headers = array_merge($headers, $options['headers']);
        
        // if the content type is already set, we assume that Mime encoding
        // is already done
        if (isset($headers['Content-Type'])) {
            $out = $msg;
        } else {
            // do a simple inline Mime encoding
            $headers['Content-Disposition'] = 'inline';
            $headers['Content-Type'] = "text/xml; charset=\"$this->encoding\"";
            if (array_key_exists('transfer-encoding', $options)) {
                if (strcasecmp($options['transfer-encoding'],'quoted-printable')==0) {
                    $headers['Content-Transfer-Encoding'] = $options['transfer-encoding'];
                    $out = &$msg;
                } else if (strcasecmp($options['transfer-encoding'],'base64')==0) {
                    $headers['Content-Transfer-Encoding'] = 'base64';
                    $out = chunk_split(base64_encode($msg),76,"\n");
                } else {
                    return $this->_raiseSoapFault("Invalid Transfer Encoding: {$options['transfer-encoding']}");
                }
            } else {
                // default to base64
                $headers['Content-Transfer-Encoding'] = 'base64';
                $out = chunk_split(base64_encode($msg));
            }
        }
        
        $headers['Subject'] = array_key_exists('subject', $options) ? $options['subject'] : 'SOAP Message';
        
        foreach ($headers as $key => $value) {
            $header_text .= "$key: $value\n";
        }
        $this->outgoing_payload = $header_text."\r\n".$this->outgoing_payload;
        # we want to return a proper XML message
        
        $mailer_params = array(
            'host' => $this->host,
            'port' => $this->port,
            'username' => $this->username,
            'password' => $this->password,
            'auth' => $this->auth
        );
        $mailer = new Mail_smtp($mailer_params);
        $result = $mailer->send($this->urlparts['path'], $headers, $out);
        #$result = mail($this->urlparts['path'], $headers['Subject'], $out, $header_text);

        if (!PEAR::isError($result)) {
            $val = new ilBMFValue('Message-ID','string',$headers['Message-ID']);
        } else {
            $val = new ilBMFValue('Fault','Struct',array(
                new ilBMFValue('faultcode','QName','SOAP-ENV:Client'),
                new ilBMFValue('faultstring','string',"couldn't send SMTP message to {$this->urlparts['path']}")
                ));
        }

        $mqname = new QName($method, $namespace);
        $methodValue = new ilBMFValue('Response', 'Struct', array($val));
        $return_msg = $this->_makeEnvelope($methodValue, $this->headers, $this->encoding);

        $this->incoming_payload = $return_msg;

        return $this->incoming_payload;
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
        $this->username = $username;
        $this->password = $password;
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
        if (!isset($this->urlparts['scheme']) ||
            strcasecmp($this->urlparts['scheme'], 'mailto') != 0) {
                $this->_raiseSoapFault("Unable to parse URL $url");
                return FALSE;
        }
        if (!isset($this->urlparts['path'])) {
            $this->_raiseSoapFault("Unable to parse URL $url");
            return FALSE;
        }
        return TRUE;
    }
    
} // end ilBMFTransport_HTTP
?>