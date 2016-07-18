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
			return $this->getProvidersByAuthMode($credentials);
		}
		
	}
	
	/**
	 * Get provider by auth mode
	 * @return \ilAuthProvider
	 */
	protected function getProvidersByAuthMode(ilAuthCredentials $credentials)
	{
		switch((int) $credentials->getAuthMode())
		{
			case AUTH_LDAP:
				$this->getLogger()->debug('Using ldap authentication');
				break;
			
			case AUTH_LOCAL:
				$this->getLogger()->debug('Using local database authentication');
				include_once './Services/Authentication/classes/Provider/class.ilAuthProviderDatabase.php';
				return array(
					new ilAuthProviderDatabase($credentials)
				);
		}
		return array();
	}
	
}
?>