<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/Provider/class.ilAuthProvider.php';
include_once './Services/Authentication/interfaces/interface.ilAuthProviderInterface.php';
include_once './Services/Authentication/interfaces/interface.ilAuthProviderAccountMigrationInterface.php';

/**
 * Description of class class 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilAuthProviderRadius extends ilAuthProvider implements ilAuthProviderInterface, ilAuthProviderAccountMigrationInterface
{
	/**
	 * @var ilRadiusSettings
	 */
	private $settings = null;
	
	private $external_account = '';
	
	
	public function __construct(\ilAuthCredentials $credentials)
	{
		parent::__construct($credentials);
		
		include_once './Services/Radius/classes/class.ilRadiusSettings.php';
		$this->settings = ilRadiusSettings::_getInstance();
	}
	
	
	/**
	 * create new account
	 * @param \ilAuthStatus $status
	 */
	public function createNewAccount(\ilAuthStatus $status)
	{
		
	}

	/**
	 * do authentication
	 * @param \ilAuthStatus $status
	 */
	public function doAuthentication(\ilAuthStatus $status)
	{
		$radius = radius_auth_open();
		
		$server = radius_add_server(
			$radius,
			$this->settings->getServersAsString(),
			$this->settings->getPort(),
			$this->settings->getSecret(),
			5,
			3
		);
		
		$this->getLogger()->debug('Using: ' . $this->settings->getServersAsString().':'. $this->settings->getPort());
		
		radius_create_request($radius, RADIUS_ACCESS_REQUEST);
		radius_put_attr($radius, RADIUS_USER_NAME, $this->getCredentials()->getUsername());
		radius_put_attr($radius, RADIUS_USER_PASSWORD, $this->getCredentials()->getPassword());

		$this->getLogger()->debug('username: ' . $this->getCredentials()->getUsername());

		$result = radius_send_request($radius);
		
		switch($result)
		{
			case RADIUS_ACCESS_ACCEPT:
				$this->getLogger()->info('Radius authentication successful.');
				$status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
				
				$local_login = ilObjUser::_checkExternalAuthAccount('radius',$this->getCredentials()->getUsername());
				$status->setAuthenticatedUserId(ilObjUser::_lookupId($local_login));
				return true;
				
			case RADIUS_ACCESS_REJECT:
				$this->getLogger()->info('Radius authentication failed with message: ' . radius_strerror($radius));
				$this->handleAuthenticationFail($status, 'err_wrong_login');
				return false;
				
			case RADIUS_ACCESS_CHALLENGE:
				$this->getLogger()->info('Radius authentication failed (access challenge): ' . radius_strerror($radius));
				$this->handleAuthenticationFail($status, 'err_wrong_login');
				return false;
				
				default:
					$this->getLogger()->info('Radius authentication failed with message: ' . radius_strerror($radius));
				$this->handleAuthenticationFail($status, 'err_wrong_login');
				return false;
		}
	}

	/**
	 * get external account name
	 * @return string Get external account for accoun migration
	 */
	public function getExternalAccountName()
	{
		return $this->external_account;
	}

	/**
	 * get trigger auth mode
	 * @return string
	 */
	public function getTriggerAuthMode()
	{
		return AUTH_RADIUS;
	}

	/**
	 * get user auth mode name
	 * @return string
	 */
	public function getUserAuthModeName()
	{
		return 'radius';
	}

	/**
	 * Migrate existing account to radius authentication
	 * @param type $a_usr_id
	 */
	public function migrateAccount($a_usr_id)
	{
		
	}

}
?>