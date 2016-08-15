<?php
  /*
   +-----------------------------------------------------------------------------+
   | ILIAS open source                                                           |
   +-----------------------------------------------------------------------------+
   | Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
   * @author Stefan Meyer <meyer@leifos.com>
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
	protected $soap_check = true;
	
	
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


	/**
	 * Constructor
	 * @param bool $use_nusoap
	 */
	public function __construct($use_nusoap = true)
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
		#echo ("SOAP: using soap mode ".IL_SOAPMODE == IL_SOAPMODE_NUSOAP ? "NUSOAP": "PHP5");
		$this->__initAuthenticationObject();
	}

	// PROTECTED
	function __checkSession($sid)
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;
		
		list($sid,$client) = $this->__explodeSid($sid);
		
		if(!strlen($sid))
		{
			$this->__setMessage('No session id given');
			$this->__setMessageCode('Client');
			return false;	
		}
		if(!$client)
		{
			$this->__setMessage('No client given');
			$this->__setMessageCode('Client');
			return false;	
		}
		
		if(!$GLOBALS['DIC']['ilAuthSession']->isAuthenticated())
		{
			$this->__setMessage('Session invalid');
			$this->__setMessageCode('Client');
			return false;
		}

		if($ilUser->hasToAcceptTermsOfService())
		{
			$this->__setMessage('User agreement no accepted.');
			$this->__setMessageCode('Server');
			return false;
		}

		if($this->soap_check)
		{
			$set = new ilSetting();
			$this->__setMessage('SOAP is not enabled in ILIAS administration for this client');
			$this->__setMessageCode('Server');
			return ($set->get("soap_user_administration") == 1);
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
		include_once('./Services/Init/classes/class.ilErrorHandling.php');

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
	
	public function __setMessageCode($a_code)
	{
		$this->message_code = $a_code;
	}
	
	public function __getMessageCode()
	{
		return $this->message_code;
	}

	/**
	 * Init authentication
	 * @param string $sid
	 */
	public function initAuth($sid)
	{
		list($sid,$client) = $this->__explodeSid($sid);
		define('CLIENT_ID',$client);
		$_COOKIE['ilClientId'] = $client;
		$_COOKIE['PHPSESSID'] = $sid;
	}

	public function initIlias()
	{		
		if(ilContext::getType() == ilContext::CONTEXT_SOAP)
		{
			try
			{
				require_once("Services/Init/classes/class.ilInitialisation.php");
				ilInitialisation::initILIAS();
			}
			catch(Exception $e)
			{				
				// #10608				
				// no need to do anything here, see __checkSession() below
			}
		}
	}


	function __initAuthenticationObject($a_auth_mode = AUTH_LOCAL)
	{
		include_once './Services/Authentication/classes/class.ilAuthFactory.php';
		ilAuthFactory::setContext(ilAuthFactory::CONTEXT_SOAP);
	}


	function __raiseError($a_message,$a_code)
	{
		#echo $a_message, $a_code;
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
	function getNIC($sid) 
	{
		$this->initAuth($sid);
		$this->initIlias();

	    if(!$this->__checkSession($sid))
		{
			return $this->__raiseError($this->__getMessage(),$this->__getMessageCode());
		}

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

	/**
	 * check access for ref id: expected type, permission, return object instance if returnobject is true
	 *
	 * @param int $ref_id
	 * @param string or array $expected_type
	 * @param string $permission
	 * @param boolean $returnObject
	 * @return Object or type
	 */
	public function checkObjectAccess($ref_id, $expected_type, $permission, $returnObject = false) {
		global $rbacsystem;
		if(!is_numeric($ref_id))
		{
			return $this->__raiseError('No valid id given.',
									   'Client');
		}
		if (!ilObject::_exists($ref_id, true)) {
			return $this->__raiseError('No object for id.',
									   'CLIENT_OBJECT_NOT_FOUND');
		}
		
		if (ilObject::_isInTrash($ref_id)) {
			return $this->__raiseError('Object is already trashed.',
									   'CLIENT_OBJECT_DELETED');			
		}
		
		$type = ilObjectFactory::getTypeByRefId($ref_id);
		if ((is_array($expected_type) && !in_array($type, $expected_type)) 
		    || 
		    (!is_array($expected_type) && $type != $expected_type)
		    )
		{
			return $this->__raiseError("Wrong type $type for id. Expected: ".(is_array($expected_type) ? join (",",$expected_type) : $expected_type), 'CLIENT_OBJECT_WRONG_TYPE');						
		}
		
		if (!$rbacsystem->checkAccess($permission, $ref_id, $type))
		{
			return $this->__raiseError('Missing permission $permission for type $type.', 'CLIENT_OBJECT_WRONG_PERMISSION');
		}
		
		if ($returnObject) {
			return ilObjectFactory::getInstanceByRefId($ref_id);
		}
		
		return $type;
	}

	public function getInstallationInfoXML() 
	{		
		include_once "Services/Context/classes/class.ilContext.php";
		ilContext::init(ilContext::CONTEXT_SOAP_WITHOUT_CLIENT);
		
		require_once("Services/Init/classes/class.ilInitialisation.php");
		ilInitialisation::initILIAS();
				
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
		include_once "Services/Context/classes/class.ilContext.php";
		ilContext::init(ilContext::CONTEXT_SOAP_WITHOUT_CLIENT);
		
		require_once("Services/Init/classes/class.ilInitialisation.php");
		ilInitialisation::initILIAS();	
		
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
		require_once("./Services/Init/classes/class.ilIniFile.php");
		
		$ilClientIniFile = new ilIniFile($ini_file);
		$ilClientIniFile->read();
		if ($ilClientIniFile->ERROR != "")
		{
			return false;
		}
		$client_id = $ilClientIniFile->readVariable('client','name');
		if ($ilClientIniFile->variableExists('client', 'expose'))
		{
		    $client_expose = $ilClientIniFile->readVariable('client','expose');
		    if ($client_expose == "0")
		        return false;
		}

		// build dsn of database connection and connect
		require_once("./Services/Database/classes/class.ilDBWrapperFactory.php");
		$ilDB = ilDBWrapperFactory::getWrapper($ilClientIniFile->readVariable("db","type"),
			$ilClientIniFile->readVariable("db","inactive_mysqli"));
		$ilDB->initFromIniFile($ilClientIniFile);			
		if ($ilDB->connect(true)) 
		{
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
			$settings->clientid = basename($client_dir); //pathinfo($client_dir, PATHINFO_FILENAME);
			$settings->default_show_users_online = $settings->get("show_users_online");
			$settings->default_hits_per_page = $settings->get("hits_per_page");
			$skin = $ilClientIniFile->readVariable("layout","skin");
			$style = $ilClientIniFile->readVariable("layout","style");
			$settings->default_skin_style = $skin.":".$style;
			return $settings;		
		}
		return null;
	}
	
	
}
?>