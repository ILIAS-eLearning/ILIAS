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
	private $logger = null;
	private $credentials = null;
	private $auth_session = null;
	
	private $authenticated = false;
	
	/**
	 * Constructor
	 * @param ilAuthSession $session
	 * @param ilAuthCredentials $credentials
	 */
	public function __construct(ilAuthSession $session, ilAuthCredentials $credentials)
	{
		$this->logger = ilLoggerFactory::getLogger('auth');
		
		$this->auth_session = $session;
		$this->credentials = $credentials;
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
	 * Get logger
	 * @return ilLogger
	 */
	public function getLogger()
	{
		return $this->logger;
	}
	
	/**
	 * Check if is authentication was successful
	 * @return boolean
	 */
	public function isAuthenticated()
	{
		return $this->authenticated;
	}
	
	/**
	 * Set authentication status
	 * @param bool $a_status
	 */
	public function setAuthenticated($a_status)
	{
		$this->authenticated = $a_status;
	}
}
?>