<?php
/**
 * This file contains the code for an SMTP transport layer.
 *
 * This code is still a rough and untested draft.
 * TODO:
 *  switch to pear mail stuff
 *  smtp authentication
 *  smtp ssl support
 *  ability to define smtp options (encoding, from, etc.)
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
 * @author     Shane Caraveo <Shane@Caraveo.com>
 * @copyright  2003-2005 The PHP Group
 * @license    http://www.php.net/license/2_02.txt  PHP License 2.02
 * @link       http://pear.php.net/package/SOAP
 */

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
 * @package SOAP
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
     * Sends and receives SOAP data.
     *
     * @param string $msg       Outgoing POST data.
     * @param string $action    SOAP Action header data.
     * @param integer $timeout  Socket timeout, defaults to 0 or off.
     *
     * @return string  Response data, minus HTTP headers.
     * @access public
     */
    function send($msg, $options = array())
    {
        $this->incoming_payload = '';
        $this->outgoing_payload = $msg;
        if (!$this->_validateUrl()) {
            return $this->fault;
        }
        if (!$options || !isset($options['from'])) {
            return $this->_raiseSoapFault('No From: address to send message with');
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
        $headers['Message-ID'] = md5(time()) . '.soap@' . $this->host;
        $headers['To'] = $this->urlparts['path'];
        if (isset($options['soapaction'])) {
            $headers['Soapaction'] = "\"{$options['soapaction']}\"";
        }

        if (isset($options['headers']))
            $headers = array_merge($headers, $options['headers']);

        // If the content type is already set, we assume that MIME encoding is
        // already done.
        if (isset($headers['Content-Type'])) {
            $out = $msg;
        } else {
            // Do a simple inline MIME encoding.
            $headers['Content-Disposition'] = 'inline';
            $headers['Content-Type'] = "text/xml; charset=\"$this->encoding\"";
            if (isset($options['transfer-encoding'])) {
                if (strcasecmp($options['transfer-encoding'], 'quoted-printable') == 0) {
                    $headers['Content-Transfer-Encoding'] = $options['transfer-encoding'];
                    $out = $msg;
                } elseif (strcasecmp($options['transfer-encoding'],'base64') == 0) {
                    $headers['Content-Transfer-Encoding'] = 'base64';
                    $out = chunk_split(base64_encode($msg), 76, "\n");
                } else {
                    return $this->_raiseSoapFault("Invalid Transfer Encoding: {$options['transfer-encoding']}");
                }
            } else {
                // Default to base64.
                $headers['Content-Transfer-Encoding'] = 'base64';
                $out = chunk_split(base64_encode($msg));
            }
        }

        $headers['Subject'] = isset($options['subject']) ? $options['subject'] : 'SOAP Message';

        foreach ($headers as $key => $value) {
            $header_text .= "$key: $value\n";
        }
        $this->outgoing_payload = $header_text . "\r\n" . $this->outgoing_payload;

        $mailer_params = array(
            'host' => $this->host,
            'port' => $this->port,
            'username' => $this->username,
            'password' => $this->password,
            'auth' => $this->auth
        );
        $mailer =& new Mail_smtp($mailer_params);
        $result = $mailer->send($this->urlparts['path'], $headers, $out);
        if (!PEAR::isError($result)) {
            $val =& new ilBMFValue('Message-ID', 'string', $headers['Message-ID']);
        } else {
            $sval[] =& new ilBMFValue('faultcode', 'QName', 'SOAP-ENV:Client');
            $sval[] =& new ilBMFValue('faultstring', 'string', "couldn't send SMTP message to {$this->urlparts['path']}");
            $val =& new ilBMFValue('Fault', 'Struct', $sval);
        }

        $mqname =& new QName($method, $namespace);
        $methodValue =& new ilBMFValue('Response', 'Struct', array($val));

        $this->incoming_payload = $this->_makeEnvelope($methodValue,
                                                       $this->headers,
                                                       $this->encoding);

        return $this->incoming_payload;
    }

    /**
     * Sets data for HTTP authentication, creates Authorization header.
     *
     * @param string $username  Username.
     * @param string $password  Response data, minus HTTP headers.
     *
     * @access public
     */
    function setCredentials($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Validates url data passed to constructor.
     *
     * @return boolean
     * @access private
     */
    function _validateUrl()
    {
        if (!is_array($this->urlparts)) {
            $this->_raiseSoapFault("Unable to parse URL $url");
            return false;
        }
        if (!isset($this->urlparts['scheme']) ||
            strcasecmp($this->urlparts['scheme'], 'mailto') != 0) {
                $this->_raiseSoapFault("Unable to parse URL $url");
                return false;
        }
        if (!isset($this->urlparts['path'])) {
            $this->_raiseSoapFault("Unable to parse URL $url");
            return false;
        }
        return true;
    }

}
