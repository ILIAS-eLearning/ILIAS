<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilAuthFrontend
{
	const MIG_EXTERNAL_ACCOUNT = 'mig_ext_account';
	const MIG_TRIGGER_AUTHMODE = 'mig_trigger_auth_mode';
	const MIG_DESIRED_AUTHMODE = 'mig_desired_auth_mode';
	
	private $logger = null;
	private $credentials = null;
	private $status = null;
	private $providers = array();
	private $auth_session = null;
	
	private $authenticated = false;
	
	/**
	 * Constructor
	 * @param ilAuthSession $session
	 * @param ilAuthCredentials $credentials
	 */
	public function __construct(ilAuthSession $session, ilAuthStatus $status, ilAuthCredentials $credentials, array $providers)
	{
		$this->logger = ilLoggerFactory::getLogger('auth');
		
		$this->auth_session = $session;
		$this->credentials = $credentials;
		$this->status = $status;
		$this->providers = $providers;
	}
	
	/**
	 * Get auth session
	 * @return ilAuthSession
	 */
	public function getAuthSession()
	{
		return $this->auth_session;
	}
	
	/**
	 * Get auth credentials
	 * @return ilAuthCredentials
	 */
	public function getCredentials()
	{
		return $this->credentials;
	}
	
	/**
	 * Get providers
	 * @return ilAuthProviderInterface[] $provider
	 */
	public function getProviders()
	{
		return $this->providers;
	}
	
	/**
	 * @return \ilAuthStatus
	 */
	public function getStatus()
	{
		return $this->status;
	}
	
	/**
	 * Reset status
	 */
	public function resetStatus()
	{
		$this->getStatus()->setStatus(ilAuthStatus::STATUS_UNDEFINED);
		$this->getStatus()->setReason('');
		$this->getStatus()->setAuthenticatedUserId(0);
	}
	
	/**
	 * Get logger
	 * @return ilLogger
	 */
	public function getLogger()
	{
		return $this->logger;
	}
	
	/**
	 * Migrate Account
	 * @param ilAuthSession $session
	 * @param type $a_username
	 * @param type $a_auth_mode
	 * @param type $a_desired_authmode
	 */
	public function migrateAccount(ilAuthSession $session)
	{
		if(!$session->isAuthenticated())
		{
			$this->getLogger()->warning('Desired user account is not authenticated');
			return false;
		}
		include_once './Services/Object/classes/class.ilObjectFactory.php';
		$user_factory = new ilObjectFactory();
		$user = $user_factory->getInstanceByObjId($session->getUserId(), false);
		
		if(!$user instanceof ilObjUser)
		{
			$this->getLogger()->info('Cannot instanitate user account for account migration: ' . $session->getUserId());
			return false;
		}
		
		$user->setAuthMode(ilSession::get(static::MIG_DESIRED_AUTHMODE));
		$user->setExternalAccount(ilSession::get(static::MIG_EXTERNAL_ACCOUNT));
		$user->update();
		
		// @todo call provider and update account data, role assignment, ...
		
		return true;
	}
	
	/**
	 * Create new user account
	 */
	public function migrateAccountNew()
	{
		foreach($this->providers as $provider)
		{
			$provider->createNewAccount($this->getStatus());

			switch($this->getStatus()->getStatus())
			{
				case ilAuthStatus::STATUS_AUTHENTICATED:
					return $this->handleAuthenticationSuccess($provider);
					
			}
		}
		return $this->handleAuthenticationFail();
	}


	
	/**
	 * Try to authenticate user
	 */
	public function authenticate()
	{
		foreach($this->getProviders() as $provider)
		{
			$this->resetStatus();
			
			$this->getLogger()->debug('Trying authentication against: ' . get_class($provider));
			
			$provider->doAuthentication($this->getStatus());
			
			$this->getLogger()->debug('Authentication user id: ' . $this->getStatus()->getAuthenticatedUserId());
			
			switch($this->getStatus()->getStatus())
			{
				case ilAuthStatus::STATUS_AUTHENTICATED:
					return $this->handleAuthenticationSuccess($provider);
					
				case ilAuthStatus::STATUS_ACCOUNT_MIGRATION_REQUIRED:
					$this->getLogger()->notice("Account migration required.");
					return $this->handleAccountMigration($provider);
					
				case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
				default:
					$this->getLogger()->debug('Authentication failed against: ' . get_class($provider));
					break;
			}
		}
		return $this->handleAuthenticationFail();
	}
	
	/**
	 * Handle account migration
	 * @param ilAuthProvider $provider
	 */
	protected function handleAccountMigration(ilAuthProviderAccountMigrationInterface $provider)
	{
		$this->getLogger()->debug('Trigger auth mode: ' . $provider->getTriggerAuthMode());
		$this->getLogger()->debug('Desired auth mode: ' . $provider->getUserAuthModeName());
		$this->getLogger()->debug('External account: ' . $provider->getExternalAccountName());
		
		$this->getStatus()->setAuthenticatedUserId(ANONYMOUS_USER_ID);
		#$this->getStatus()->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
		
		ilSession::set(static::MIG_TRIGGER_AUTHMODE, $provider->getTriggerAuthMode());
		ilSession::set(static::MIG_DESIRED_AUTHMODE, $provider->getUserAuthModeName());
		ilSession::set(static::MIG_EXTERNAL_ACCOUNT, $provider->getExternalAccountName());
		
		$this->getLogger()->dump($_SESSION, ilLogLevel::DEBUG);
		
		return true;
	}
	
	/**
	 * Handle successful authentication
	 * @param ilAuthProviderInterface $provider
	 */
	protected function handleAuthenticationSuccess(ilAuthProviderInterface $provider)
	{
		include_once './Services/Object/classes/class.ilObjectFactory.php';
		$factory = new ilObjectFactory();
		$user = $factory->getInstanceByObjId($this->getStatus()->getAuthenticatedUserId(),false);
		
		if(!$user instanceof ilObjUser)
		{
			$this->getLogger()->error('Cannot instatiate user account with id: ' . $this->getStatus()->getAuthenticatedUserId());
			$this->getStatus()->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
			$this->getStatus()->setAuthenticatedUserId(0);
			$this->getStatus()->setReason('auth_err_invalid_user_account');
			return false;
		}
		// user activation
		if(!$this->checkActivation($user))
		{
			$this->getLogger()->info('Authentication failed for inactive user with id: ' . $this->getStatus()->getAuthenticatedUserId());
			$this->getStatus()->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
			$this->getStatus()->setAuthenticatedUserId(0);
			$this->getStatus()->setReason('err_inactive');
			return false;
		}
		
		// time limit
		if(!$this->checkTimeLimit($user))
		{
			$this->getLogger()->info('Authentication failed (time limit restriction) for user with id: ' . $this->getStatus()->getAuthenticatedUserId());

			if($GLOBALS['ilSettings']->get('user_reactivate_code'))
			{
				$this->getStatus()->setStatus(ilAuthStatus::STATUS_CODE_ACTIVATION_REQUIRED);
			}
			else
			{
				$this->getStatus()->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
			}
			$this->getStatus()->setAuthenticatedUserId(0);
			$this->getStatus()->setReason('time_limit_reached');
			return false;
		}
		
		// ip check
		if(!$this->checkIp($user))
		{
			$this->getLogger()->info('Authentication failed (wrong ip) for user with id: ' . $this->getStatus()->getAuthenticatedUserId());
			$this->getStatus()->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
			$this->getStatus()->setAuthenticatedUserId(0);
			$this->getStatus()->setReason('wrong_ip_detected');
			return false;
		}
		
		// check simultaneos logins
		if(!$this->checkSimultaneousLogins($user))
		{
			$this->getLogger()->info('Authentication failed: simultaneous logins forbidden for user: ' . $this->getStatus()->getAuthenticatedUserId());
			$this->getStatus()->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
			$this->getStatus()->setAuthenticatedUserId(0);
			$this->getStatus()->setReason('simultaneous_login_detected');
			return false;
		}
		
		$this->getLogger()->info('Successfully authenticated: ' . ilObjUser::_lookupLogin($this->getStatus()->getAuthenticatedUserId()));
		$this->getAuthSession()->setAuthenticated(true, $this->getStatus()->getAuthenticatedUserId());
		return true;
		
	}
	
	/**
	 * Check activation
	 * @param ilObjUser $user
	 */
	protected function checkActivation(ilObjUser $user)
	{
		return $user->getActive();
	}
	
	/**
	 * Check time limit
	 * @param ilObjUser $user
	 * @return type
	 */
	protected function checkTimeLimit(ilObjUser $user)
	{
		return $user->checkTimeLimit();
	}
	
	/**
	 * Check ip
	 */
	protected function checkIp(ilObjUser $user)
	{		
		$clientip = $user->getClientIP();
		if (trim($clientip) != "")
		{
			$clientip = preg_replace("/[^0-9.?*,:]+/","",$clientip);
			$clientip = str_replace(".","\\.",$clientip);
			$clientip = str_replace(Array("?","*",","), Array("[0-9]","[0-9]*","|"), $clientip);
			
			ilLoggerFactory::getLogger('auth')->debug('Check ip ' . $clientip . ' against ' . $_SERVER['REMOTE_ADDR']);

			if (!preg_match("/^".$clientip."$/", $_SERVER["REMOTE_ADDR"]))
			{
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Check simultaneous logins
	 * @param ilObjUser $user
	 */
	protected function checkSimultaneousLogins(ilObjUser $user)
	{
		if(
			$GLOBALS['ilSetting']->get('ps_prevent_simutanous_logins') &&
			ilObjUser::hasActiveSession($user->getId())
		)
		{
			return false;
		}
		return true;
	}

	/**
	 * Handle failed authenication
	 */
	protected function handleAuthenticationFail()
	{
		$this->getLogger()->debug('Authentication failed for all authentication methods.');
	}
	
}
?>