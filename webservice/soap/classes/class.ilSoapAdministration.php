<?php
  /*
   +-----------------------------------------------------------------------------+
   | ILIAS open source                                                           |
   +-----------------------------------------------------------------------------+
   | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
   |                                                                             |
   | This program is free software; you can redistribute it and/or               |
   | modify it under the terms of the GNU General Public License                 |
   | as published by the Free Software Foundation; either version 2              |
   | of the License, or (at your option) any later version.                      |
   |                                                                             |
   | This program is distributed in the hope that it will be useful,             |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
   | GNU General Public License for more details.                                |
   |                                                                             |
   | You should have received a copy of the GNU General Public License           |
   | along with this program; if not, write to the Free Software                 |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
   +-----------------------------------------------------------------------------+
  */


  /**
   * soap server
   * Base class for all SOAP registered methods. E.g ilSoapUserAdministration
   *
   * @author Stefan Meyer <smeyer@databay.de>
   * @version $Id$
   *
   * @package ilias
   */

include_once './webservice/soap/lib/nusoap.php';
include_once ("./Services/Authentication/classes/class.ilAuthUtils.php");		// to get auth mode constants

define ('SOAP_CLIENT_ERROR', 1);
define ('SOAP_SERVER_ERROR', 2);

class ilSoapAdministration
{
	/*
	 * object which handles php's authentication
	 * @var object
	 */
	var $sauth = null;

	/*
	 * Defines type of error handling (PHP5 || NUSOAP)
	 * @var object
	 */
	var $error_method = null;


	function ilSoapAdministration($use_nusoap = true)
	{
		define('USER_FOLDER_ID',7);
		define('NUSOAP',1);
		define('PHP5',2);

		if(IL_SOAPMODE == IL_SOAPMODE_NUSOAP)
		{
			$this->error_method = NUSOAP;
		} 
		else
		{ 
			$this->error_method = PHP5;
		}
		$this->__initAuthenticationObject();

	}

	// PROTECTED
	function __checkSession($sid)
	{
		list($sid,$client) = $this->__explodeSid($sid);

		$this->sauth->setClient($client);
		$this->sauth->setSid($sid);

		if(!$this->sauth->validateSession())
		{
			return false;
		}
		return true;
	}

	/**
	 * Overwrite error handler
	 *
	 * @access public
	 * @param
	 *
	 */
	public function initErrorWriter()
	{
		include_once('classes/class.ilErrorHandling.php');

	 	set_error_handler(array('ilErrorHandling','_ilErrorWriter'),E_ALL);
	}


	function __explodeSid($sid)
	{
		$exploded = explode('::',$sid);

		return is_array($exploded) ? $exploded : array('sid' => '','client' => '');
	}


	function __setMessage($a_str)
	{
		$this->message = $a_str;
	}
	function __getMessage()
	{
		return $this->message;
	}
	function __appendMessage($a_str)
	{
		$this->message .= isset($this->message) ? ' ' : '';
		$this->message .= $a_str;
	}


	function __initAuthenticationObject($a_auth_mode = AUTH_LOCAL)
	{
		switch($a_auth_mode)
		{
			case AUTH_CAS:
				include_once './webservice/soap/classes/class.ilSoapAuthenticationCAS.php';
				return $this->sauth = new ilSoapAuthenticationCAS();
			case AUTH_LDAP:
				include_once './webservice/soap/classes/class.ilSoapAuthenticationLDAP.php';
				return $this->sauth = new ilSoapAuthenticationLDAP();

			default:
				include_once './webservice/soap/classes/class.ilSoapAuthentication.php';
				return $this->sauth = new ilSoapAuthentication();
		}
	}


	function __raiseError($a_message,$a_code)
	{
		switch($this->error_method)
		{
			case NUSOAP:
				return new soap_fault($a_code,'',$a_message);
			case PHP5:
				return new SoapFault($a_code, $a_message);
		}
	}

	/**
	 * get client information from current as xml result set
	 *
	 * @param string $sid  current session id
	 *
	 * @return XMLResultSet containing columns installation_id, installation_version, installation_url, installation_description, installation_default_language
	 */
	function getNIC($sid) {
	    if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->sauth->getMessage(),$this->sauth->getMessageCode());
		}

		// Include main header
		include_once './include/inc.header.php';
		global $rbacsystem, $rbacreview, $ilLog, $rbacadmin,$ilSetting, $ilClientIniFile;

		if (!is_object($ilClientIniFile)) {
		    return $this->__raiseError("Client ini is not initialized","Server");
		}

		$auth_modes = ilAuthUtils::_getActiveAuthModes();
		$auth_mode_default =  strtoupper(ilAuthUtils::_getAuthModeName(array_shift($auth_modes)));
		$auth_mode_names = array();
		foreach ($auth_modes as $mode) {
			$auth_mode_names[] = strtoupper(ilAuthUtils::_getAuthModeName($mode));
		}

        // todo: get information from client id, read from ini file specificied
        $client_details[] = array ("installation_id" => IL_INST_ID,
                                   "installation_version" => ILIAS_VERSION,
                                   "installation_url" => ILIAS_HTTP_PATH,
                                   "installation_description" => $ilClientIniFile->readVariable("client","description"),
									"installation_language_default" => $ilClientIniFile->readVariable("language","default"),
									"installation_session_expire" => $ilClientIniFile->readVariable("session","expire"),
									"installation_php_postmaxsize" => $this->return_bytes(ini_get("post_max_size")),
									"authentication_methods" => join(",", $auth_mode_names),
									"authentication_default_method" => $auth_mode_default

																		);

        // store into xml result set
		include_once './webservice/soap/classes/class.ilXMLResultSet.php';


        $xmlResult = new ilXMLResultSet();
        $xmlResult->addArray($client_details, true);

        // create writer and return xml
		include_once './webservice/soap/classes/class.ilXMLResultSetWriter.php';
        $xmlResultWriter = new ilXMLResultSetWriter($xmlResult);
        $xmlResultWriter->start();
        return $xmlResultWriter->getXML();
	}

	/**
	*	calculate bytes from K,M,G modifiers
		e.g: 8M = 8 * 1024 * 1024 bytes
	*/
	private function return_bytes($val) {
		$val = trim($val);
		$last = strtolower($val{strlen($val)-1});
		switch($last) {
		// The 'G' modifier is available since PHP 5.1.0
		case 'g':
			$val *= 1024;
		case 'm':
			$val *= 1024;
		case 'k':
			$val *= 1024;
		}
		return $val;
	}

}
?>