<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/interfaces/interface.ilAuthFrontendInterface.php';
include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontend.php';

/**
 * Auth class for form based authentication
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilAuthStandardFormFrontend extends ilAuthFrontend implements ilAuthFrontendInterface
{
	/**
	 * Constructor
	 * @param ilSession $session
	 * @param ilAuthCredentials $credentials
	 */
	public function __construct(ilAuthSession $session, ilAuthCredentials $credentials)
	{
		parent::__construct($session, $credentials);
	}
	
	public function authenticate()
	{
		if($this->getCredentials()->getUsername() != 'root')
		{
			$this->setAuthenticated(false);
			$this->getAuthSession()->setAuthenticated(false, 0);
			$this->getAuthSession()->setUserId(0);
			#$this->getAuthSession()->regenerateId();
			
		}
		
		$this->getLogger()->info('Logged in as '. $this->getCredentials()->getUsername());
		$this->setAuthenticated(true);
		$this->getAuthSession()->setAuthenticated(true, 6);
		$this->getAuthSession()->regenerateId();
		
		return false;
	}

}
?>