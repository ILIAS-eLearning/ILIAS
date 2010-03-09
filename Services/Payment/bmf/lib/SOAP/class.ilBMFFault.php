<?php
/**
 * This file contains the ilBMFFault class, used for all error objects in this
 * package.
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

require_once('PEAR.php');

/**
 * ilBMFFault
 * PEAR::Error wrapper used to match SOAP Faults to PEAR Errors
 *
 * @package  SOAP
 * @access   public
 * @author   Shane Caraveo <Shane@Caraveo.com>   Port to PEAR and more
 * @author   Dietrich Ayala <dietrich@ganx4.com> Original Author
 */
class ilBMFFault extends PEAR_Error
{
    
    /**
     * Constructor
     * 
     * @param    string  message string for fault
     * @param    mixed   the faultcode
     * @param    mixed   see PEAR::ERROR 
     * @param    mixed   see PEAR::ERROR 
     * @param    array   the userinfo array is used to pass in the
     *                   SOAP actor and detail for the fault
     */
    function ilBMFFault($faultstring = 'unknown error', $faultcode = 'Client', $faultactor=NULL, $detail=NULL, $mode = null, $options = null)
    {
        parent::PEAR_Error($faultstring, $faultcode, $mode, $options, $detail);
        if ($faultactor) $this->error_message_prefix = $faultactor;
    }
    
    /**
     * message
     *
     * returns a SOAP_Message class that can be sent as a server response
     *
     * @return SOAP_Message 
     * @access public
     */
    function message()
    {
        $msg =& new ilBMFBase();
        $params = array();
        $params[] =& new ilBMFValue('faultcode', 'QName', 'SOAP-ENV:'.$this->code);
        $params[] =& new ilBMFValue('faultstring', 'string', $this->message);
        $params[] =& new ilBMFValue('faultactor', 'anyURI', $this->error_message_prefix);
        if (isset($this->backtrace)) {
            $params[] =& new ilBMFValue('detail', 'string', $this->backtrace);
        } else {
            $params[] =& new ilBMFValue('detail', 'string', $this->userinfo);
        }
        
        $methodValue =& new ilBMFValue('{'.SOAP_ENVELOP.'}Fault', 'Struct', $params);
        $headers = NULL;
        return $msg->_makeEnvelope($methodValue, $headers);
    }
    
    /**
     * getFault
     *
     * returns a simple native php array containing the fault data
     *
     * @return array 
     * @access public
     */
    function getFault()
    {
        global $SOAP_OBJECT_STRUCT;
        if ($SOAP_OBJECT_STRUCT) {
            $fault =& new stdClass();
            $fault->faultcode = $this->code;
            $fault->faultstring = $this->message;
            $fault->faultactor = $this->error_message_prefix;
            $fault->detail = $this->userinfo;
            return $fault;
        }
        return array(
                'faultcode' => $this->code,
                'faultstring' => $this->message,
                'faultactor' => $this->error_message_prefix,
                'detail' => $this->userinfo
            );
    }
    
    /**
     * getActor
     *
     * returns the SOAP actor for the fault
     *
     * @return string 
     * @access public
     */
    function getActor()
    {
        return $this->error_message_prefix;
    }
    
    /**
     * getDetail
     *
     * returns the fault detail
     *
     * @return string 
     * @access public
     */
    function getDetail()
    {
        return $this->userinfo;
    }
    
}
?>