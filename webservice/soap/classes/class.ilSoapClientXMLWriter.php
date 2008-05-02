<?php

include_once "./classes/class.ilXmlWriter.php";

class ilSoapClientXMLWriter extends ilXmlWriter
{
	/**
	 * array of ilSetting Objects
	 *
	 * @var array
	 */
	private $settings;

	/**
	* constructor
	* @param	string	xml version
	* @param	string	output encoding
	* @param	string	input encoding
	* @access	public
	*/
	function ilSoapClientXMLWriter()
	{
		parent::ilXmlWriter();
	}
	
	/**
	 * write access to property settings
	 *
	 * @param array $settings is an array of ilSetting Objects 
	 */
	public function setSettings($settings) {
		$this->settings = $settings;	
	}

	public function start()
	{
		$this->__buildHeader();
		$this->__buildInstallationInfo();
		if (is_array($this->settings)) 
		{
			foreach ($this->settings as $setting)
				$this->__buildSetting ($setting);
		}
		$this->__buildFooter();		
	}

	public function getXML()
	{
		return $this->xmlDumpMem(FALSE);
	}
	
	private function __buildHeader()
	{
		// we have to build the http path here since this request is client independent!
		if($_SERVER["HTTPS"] == "on")
		{
			$protocol = 'https://';
		}
		else
		{
			$protocol = 'http://';
		}
		$host = $_SERVER['HTTP_HOST'];

		$path = pathinfo($_SERVER['REQUEST_URI']);
		if(!$path['extension'])
		{
			$uri = $_SERVER['REQUEST_URI'];
		}
		else
		{
			$uri = dirname($_SERVER['REQUEST_URI']);
		}
		$httppath = ilUtil::removeTrailingPathSeparators($protocol.$host.$uri);		
		$this->xmlSetDtdDef("<!DOCTYPE Clients PUBLIC \"-//ILIAS//DTD Group//EN\" \"".$httppath."/xml/ilias_client_3_10.dtd\">");  
		$this->xmlSetGenCmt("Export of ILIAS clients.");
		$this->xmlHeader();
		$this->xmlStartTag("Clients", $attrs);	
		
		return true;
	}

	private function __buildFooter()
	{
		$this->xmlEndTag('Clients');
	}
	
	/**
	 * create client tag
	 *
	 * @param ilSetting $setting
	 */
	private function __buildSetting($setting) {
		$auth_modes = ilAuthUtils::_getActiveAuthModes();
		$auth_mode_default =  strtoupper(ilAuthUtils::_getAuthModeName(array_shift($auth_modes)));
		$auth_mode_names = array();
		foreach ($auth_modes as $mode) {
			$auth_mode_names[] = strtoupper(ilAuthUtils::_getAuthModeName($mode));
		}
		
		
		$this->xmlStartTag("Client", 
			array(
				"inst_id" => $setting->get("inst_id"),
				"enabled" => $setting->access == 1 ? "TRUE" : "FALSE",
				"path" => $setting->httpPath			
			));
		$this->xmlElement ("Id", null, $setting->get("inst_name"));
		$this->xmlElement ("Description", null, $setting->description);
		$this->xmlElement ("Institution", null, $setting->get("inst_institution"));
		$this->xmlStartTag("Administrator");
		$this->xmlElement ("Firstname", null, $setting->get("admin_firstname"));
		$this->xmlElement ("Lastname", null, $setting->get("admin_lastname"));
		$this->xmlElement ("Title", null, $setting->get("admin_title"));
		$this->xmlElement ("Institution", null, $setting->get("admin_institution"));
		$this->xmlElement ("Position", null, $setting->get("admin_position"));
		$this->xmlElement ("Email", null, $setting->get("admin_email"));
		$this->xmlElement ("Street ", null, $setting->get("admin_street"));
		$this->xmlElement ("ZipCode ", null, $setting->get("admin_zipcode"));
		$this->xmlElement ("City", null, $setting->get("admin_city"));
		$this->xmlElement ("Country", null, $setting->get("admin_country"));
		$this->xmlElement ("Phone", null, $setting->get("admin_phone"));		
		$this->xmlEndTag("Administrator");
		$this->xmlStartTag("Settings");
		$this->xmlElement("Setting", array("key" => "error_recipient"), $setting->get("error_recipient"));		
		$this->xmlElement("Setting", array("key" => "feedback_recipient"), $setting->get("feedback_recipient"));
		$this->xmlElement("Setting", array("key" => "session_expiration"), $setting->session);
		$this->xmlElement("Setting", array("key" => "soap_enabled"), $setting->get("soap_user_administration"));
		$this->xmlElement("Setting", array("key" => "default_language"), $setting->language);
		$this->xmlElement("Setting", array("key" => "authentication_methods"), join(",", $auth_mode_names));
		$this->xmlElement("Setting", array("key" => "authentication_default_method"), $auth_mode_default);
		
		$this->xmlEndTag("Settings");
		$this->xmlEndTag("Client");
	}
	
	private function __buildInstallationInfo() 
	{
		$this->xmlStartTag("Installation",
			array (
				"version" => ILIAS_VERSION,
			));
		$this->xmlStartTag("Settings");
		$this->xmlElement("Setting", array("key" => "default_client"), $GLOBALS['ilIliasIniFile']->readVariable("clients","default"));
		$this->xmlElement("Setting", array("key" => "post_max_size"), ilSoapAdministration::return_bytes(ini_get("post_max_size")));
		$this->xmlElement("Setting", array("key" => "upload_max_filesize"), ilSoapAdministration::return_bytes(ini_get("upload_max_filesize")));
		$this->xmlEndTag("Settings");			
		$this->xmlEndTag("Installation");
	}
}

?>