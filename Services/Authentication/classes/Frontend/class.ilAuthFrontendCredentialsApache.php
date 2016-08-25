<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentials.php';
include_once './Services/Authentication/interfaces/interface.ilAuthCredentials.php';

/**
 * Description of class class 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilAuthFrontendCredentialsApache extends ilAuthFrontendCredentials implements ilAuthCredentials
{
	private $settings = null;
	

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		include_once './Services/Administration/classes/class.ilSetting.php';
		$this->settings = new ilSetting('apache_auth');
	}
	
	
	/**
	 * @return \ilSetting
	 */
	protected function getSettings()
	{
		return $this->settings;
	}
	
	/**
	 * Init credentials from request
	 */
	public function initFromRequest()
	{
		$this->getLogger()->dump($_SERVER, ilLogLevel::DEBUG);
		$this->getLogger()->debug($this->getSettings()->get('apache_auth_username_direct_mapping_fieldname',''));
		
		// constants APACHE_AUTH... are defined there...
		include_once './Services/AuthApache/classes/class.ilAuthApache.php';
		switch($this->getSettings()->get('apache_auth_username_config_type'))
		{
			case APACHE_AUTH_TYPE_DIRECT_MAPPING:
				if(array_key_exists($this->getSettings()->get('apache_auth_username_direct_mapping_fieldname'), $_SERVER))
				{
					$this->setUsername($_SERVER[$this->getSettings()->get('apache_auth_username_direct_mapping_fieldname','')]);
				}
				break;
				
			case APACHE_AUTH_TYPE_BY_FUNCTION:
				include_once 'Services/AuthApache/classes/custom_username_func.php';
				$this->setUsername(ApacheCustom::getUsername());
				break;
		}
	}
}
?>