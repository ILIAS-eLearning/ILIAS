<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/interfaces/interface.ilAuthFrontendInterface.php';

/**
 * Auth class for form based authentication
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilAuthPasswordFrontend extends ilAuthFrontend implements ilAuthFrontendInterface
{
	/**
	 * Constructor
	 * @param ilSession $session
	 * @param ilAuthCredentials $credentials
	 */
	public function __construct(ilAuthCredentials $credentials)
	{
		parent::__construct($session, $credentials);
	}
	
	
	
	public function getStatus()
	{
		
	}

	public function isAuthenticated()
	{
		
	}

	public function isRemembered()
	{
		
	}

	public function login()
	{
		
	}

	public function logout()
	{
		
	}
}
?>