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
		//return true;
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

		include_once 'Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php';
		include_once 'Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordXMLWriter.php';
		
		// create advanced meta data record xml
		$record_ids = array();
		$record_types = ilAdvancedMDRecord::_getAssignableObjectTypes();
		foreach($record_types as $type) {
			$records = ilAdvancedMDRecord::_getActivatedRecordsByObjectType($type);
			foreach ($records as $record){
				$record_ids [] = $record->getRecordId();
			}			
		}
		$record_ids = array_unique($record_ids);		
		$advmwriter = new ilAdvancedMDRecordXMLWriter($record_ids);
		$advmwriter->write();		
		
		// create user defined fields record xml, simulate empty user records
		include_once ("./Services/User/classes/class.ilUserXMLWriter.php");
		$udfWriter = new ilUserXMLWriter();
		$users = array();
		$udfWriter->setObjects($users);
		$udfWriter->start();				
		 
        // todo: get information from client id, read from ini file specificied
        $client_details[] = array ("installation_id" => IL_INST_ID,
                                   "installation_version" => ILIAS_VERSION,
                                   "installation_url" => ILIAS_HTTP_PATH,
                                   "installation_description" => $ilClientIniFile->readVariable("client","description"),
									"installation_language_default" => $ilClientIniFile->readVariable("language","default"),
									"installation_session_expire" => $ilClientIniFile->readVariable("session","expire"),
									"installation_php_postmaxsize" => $this->return_bytes(ini_get("post_max_size")),
									"authentication_methods" => join(",", $auth_mode_names),
									"authentication_default_method" => $auth_mode_default,
        							"installation_udf_xml" => $udfWriter ->getXML(),
        							"installation_advmd_xml" => $advmwriter->xmlDumpMem(false)

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
	public static function return_bytes($val) {
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

	public function isFault($object) {
		switch($this->error_method)
		{
			case NUSOAP:
				return $object instanceof soap_fault;
			case PHP5:
				return $object instanceof SoapFault;
		}
		return true;
	}

	public function checkObjectAccess($ref_id, $expected_type, $permission, $returnObject = false) {
		global $rbacsystem;
		if(!is_numeric($ref_id))
		{
			return $this->__raiseError('No valid id given.',
									   'Client');
		}
		if (!ilObject::_exists($ref_id, true)) {
			return $this->__raiseError('No object for id.',
									   'Client');
		}
		
		if (ilObject::_isInTrash($ref_id)) {
			return $this->__raiseError('Client is in trash.',
									   'Client');			
		}
		
		if (ilObjectFactory::getTypeByRefId($ref_id) != $expected_type)
		{
			return $this->__raiseError('Wrong type for id.', 'Client');						
		}
		
		if (!$rbacsystem->checkAccess($permission, $ref_id, $expected_type))
		{
			return $this->__raiseError('Missing permission $permission for type $expected_type.', 'Client');
		}
		
		if ($returnObject) {
			return ilObjectFactory::getInstanceByRefId($ref_id);
		}
		
		return true;
	}

	public function getInstallationInfoXML() 
	{		
		require_once("Services/Init/classes/class.ilInitialisation.php");
	
		$init = new ilInitialisation();		
		$init->requireCommonIncludes();
		$init->initIliasIniFile();
		
		$ilias = & new ILIAS();
		$GLOBALS['ilias'] =& $ilias;
		
		
		$settings = array();
		$clientdirs = glob(ILIAS_WEB_DIR."/*",GLOB_ONLYDIR);
		require_once ("webservice/soap/classes/class.ilSoapInstallationInfoXMLWriter.php");	
		$writer = new ilSoapInstallationInfoXMLWriter ();
		$writer->start();		
		if (is_array($clientdirs))
		{		
			foreach ($clientdirs as $clientdir) 
			{				
				if (is_object($clientInfo= $this->getClientInfo($init, $clientdir)))
				{
					$writer->addClient ($clientInfo);
				}
			}
		}
		$writer->end();
		
		return $writer->getXML();
	}
	
	public function getClientInfoXML($clientid) 
	{		
		require_once("Services/Init/classes/class.ilInitialisation.php");
	
		$init = new ilInitialisation();		
		$init->requireCommonIncludes();
		$init->initIliasIniFile();
		
		$ilias = & new ILIAS();
		$GLOBALS['ilias'] =& $ilias;
		
		
		$settings = array();
		$clientdir = ILIAS_WEB_DIR."/".$clientid;
		require_once ("webservice/soap/classes/class.ilSoapInstallationInfoXMLWriter.php");
		$writer = new ilSoapInstallationInfoXMLWriter ();		
		$writer->setExportAdvancedMetaDataDefinitions (true);
		$writer->setExportUDFDefinitions (true);
		$writer->start();
		if (is_object($client = $this->getClientInfo($init, $clientdir)))
		{
			$writer->addClient($client);
		}
		else
			return $this->__raiseError("Client ID $clientid does not exist!", 'Client');
		$writer->end();
		return $writer->getXML();
	}
	
	private function getClientInfo ($init, $client_dir) 
	{
		global $ilDB;
		$ini_file = "./".$client_dir."/client.ini.php";
		
		// get settings from ini file
		require_once("classes/class.ilIniFile.php");
		
		$ilClientIniFile = new ilIniFile($ini_file);
		$ilClientIniFile->read();
		if ($ilClientIniFile->ERROR != "")
		{
			return false;
		}
		$client_id = $ilClientIniFile->readVariable('client','name');

		// build dsn of database connection and connect
		$dsn = $ilClientIniFile->readVariable("db","type")."://".$ilClientIniFile->readVariable("db", "user").
					 ":".$ilClientIniFile->readVariable("db", "pass").
					 "@".$ilClientIniFile->readVariable("db", "host").
					 "/".$ilClientIniFile->readVariable("db", "name");
		
				
		require_once "classes/class.ilDBx.php";
		$ilDB = new ilDBx($dsn);
		$GLOBALS['ilDB'] = $ilDB;

		require_once("Services/Administration/classes/class.ilSetting.php");
		$settings = new ilSetting();
		$GLOBALS["ilSetting"] = $settings;
		// workaround to determine http path of client
		define ("IL_INST_ID",  $settings->get("inst_id",0));
		$settings->access = $ilClientIniFile->readVariable("client", "access");
		$settings->description = $ilClientIniFile->readVariable("client","description");
		$settings->session = min((int) ini_get("session.gc_maxlifetime"), (int) $ilClientIniFile->readVariable("session","expire"));
		$settings->language = $ilClientIniFile->readVariable("language","default");
		$settings->clientid = pathinfo($client_dir, PATHINFO_FILENAME);
		$settings->default_show_users_online = $settings->get("show_users_online");
		$settings->default_hits_per_page = $settings->get("hits_per_page");
		$skin = $ilClientIniFile->readVariable("layout","skin");
		$style = $ilClientIniFile->readVariable("layout","style");
		$settings->default_skin_style = $skin.":".$style;
		return $settings;
	}
	
	
}
?>