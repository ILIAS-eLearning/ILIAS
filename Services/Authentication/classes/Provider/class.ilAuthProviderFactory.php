<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Auth provider factory
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilAuthProviderFactory
{
	private $logger = null;
	
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->logger = ilLoggerFactory::getLogger('auth');
	}
	
	/**
	 * Get current logger
	 * @return \ilLogger
	 */
	public function getLogger()
	{
		return $this->logger;
	}
	
	/**
	 * Get provider
	 * @param \ilAuthCredentials $credentials
	 */
	public function getProviders(ilAuthCredentials $credentials)
	{
		// Fixed provider selection;
		if(strlen($credentials->getAuthMode()))
		{
			$this->getLogger()->debug('Returning fixed provider for auth mode: ' . $credentials->getAuthMode());
			if($this->getProvidersByAuthMode($credentials))
			{
				return array(
					$this->getProvidersByAuthMode($credentials, $credentials->getAuthMode())
				);
			}
		}
		
		// check for dynamic provider selection
		include_once './Services/Authentication/classes/class.ilAuthModeDetermination.php';
		$auth_determination = ilAuthModeDetermination::_getInstance();
		$sequence = $auth_determination->getAuthModeSequence($credentials->getUsername());
		
		$providers = array();
		foreach((array) $sequence as $position => $authmode)
		{
			$provider = $this->getProvidersByAuthMode($credentials, $authmode);
			if($provider instanceof ilAuthProviderInterface)
			{
				$providers[] = $provider;
			}
		}
		return $providers;
	}
	
	/**
	 * Get provider by auth mode
	 * @return \ilAuthProvider
	 */
	protected function getProvidersByAuthMode(ilAuthCredentials $credentials, $a_authmode)
	{
		switch((int) $a_authmode)
		{
			case AUTH_LDAP:
				$this->getLogger()->debug('Using ldap authentication');
				break;
			
			case AUTH_LOCAL:
				$this->getLogger()->debug('Using local database authentication');
				include_once './Services/Authentication/classes/Provider/class.ilAuthProviderDatabase.php';
				return new ilAuthProviderDatabase($credentials);
		}
		return null;
	}
	
}
?>