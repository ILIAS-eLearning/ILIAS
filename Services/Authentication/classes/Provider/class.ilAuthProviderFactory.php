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
	public function getProvider(ilAuthCredentials $credentials)
	{
		// Fixed provider selection;
		if(strlen($credentials->getAuthMode()))
		{
			$this->getLogger()->debug('Returning fixed provider for auth mode: ' . $credentials->getAuthMode());
			return $this->getProviderByAuthMode($credentials->getAuthMode());
		}
		
	}
	
}
?>