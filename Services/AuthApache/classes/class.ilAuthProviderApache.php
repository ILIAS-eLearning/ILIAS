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
class ilAuthProviderApache extends ilAuthProvider implements ilAuthProviderInterface, ilAuthProviderAccountMigrationInterface
{
	private $settings = null;
	
	private $migration_account = '';
	
	
	/**
	 * Constructor
	 * @param \ilAuthCredentials $credentials
	 */
	public function __construct(\ilAuthCredentials $credentials)
	{
		parent::__construct($credentials);
		
		include_once './Services/Administration/classes/class.ilSetting.php';
		$this->settings = new ilSetting('apache_auth');
	}
	
	/**
	 * Get setings
	 * @return \ilSetting
	 */
	protected function getSettings()
	{
		return $this->settings;
	}
	
	/**
	 * Do apache auth
	 * @param \ilAuthStatus $status
	 */
	public function doAuthentication(\ilAuthStatus $status)
	{
		if(!$this->getSettings()->get('apache_enable_auth'))
		{
			$this->getLogger()->info('Apache auth disabled.');
			$this->handleAuthenticationFail($status, 'apache_auth_err_disabled');
			return false;
		}
		
		if(
			!$this->getSettings()->get('apache_auth_indicator_name') ||
			!$this->getSettings()->get('apache_auth_indicator_value')
			)
		{
			$this->getLogger()->warning('Apache auth indicator match failure.');
			$this->handleAuthenticationFail($status, 'apache_auth_err_indicator_match_failure');
			return false;
		}
		include_once './Services/Utilities/classes/class.ilUtil.php';
		if(!ilUtil::isLogin($this->getCredentials()->getUsername()))
		{
			$this->getLogger()->warning('Invalid login name given: ' . $this->getCredentials()->getUsername());
			$this->handleAuthenticationFail($status, 'apache_auth_err_invalid_login');
			return false;
		}
		
		if(!strlen($this->getCredentials()->getUsername()))
		{
			$this->getLogger()->info('No username given');
			$this->handleAuthenticationFail($status, 'err_wrong_login');
			return false;
		}
		
		$login = ilObjUser::_checkExternalAuthAccount('apache', $this->getCredentials()->getUsername());
		$usr_id = ilObjUser::_lookupId($login);
		if(!$usr_id)
		{
			$this->getLogger()->info('Cannot find user id for external account: ' . $this->getCredentials()->getUsername());
			$this->handleAuthenticationFail($status, 'err_wrong_login');
			return false;
		}
		
		$status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
		$status->setAuthenticatedUserId($usr_id);
		return true;
	}

	/**
	 * Migrate existing account
	 * Maybe ldap sync has to be performed here
	 * @param int $a_usr_id
	 */
	public function migrateAccount($a_usr_id)
	{
		
	}

	/**
	 * Create new account for account migration
	 * @param \ilAuthStatus $status
	 */
	public function createNewAccount(\ilAuthStatus $status)
	{
		
	}


	/**
	 * Return the login name for auth type apache
	 */
	public function getExternalAccountName()
	{
		
	}

	/**
	 * Get auth mode of current authentication type
	 */
	public function getTriggerAuthMode()
	{
		return AUTH_APACHE;
	}

	/**
	 * apache or ldap_1 ?
	 */
	public function getUserAuthModeName()
	{
		
	}


}
?>