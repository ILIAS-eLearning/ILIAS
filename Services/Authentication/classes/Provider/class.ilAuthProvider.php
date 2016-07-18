<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Base class for authentication providers (radius, ldap, apache, ...)
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
abstract class ilAuthProvider
{
	const STATUS_UNDEFINED = 0;
	const STATUS_AUTHENTICATION_SUCCESS = 1;
	const STATUS_AUTHENTICATION_FAILED = 2;
	const STATUS_MIGRATION = 3;
	
	
	private $logger = null;

	private $credentials = null;
	
	private $status = self::STATUS_UNDEFINED;
	private $user_id = 0;
	/**
	 * Constructor
	 */
	public function __construct(ilAuthCredentials $credentials)
	{
		$this->logger = ilLoggerFactory::getLogger('auth');
		$this->credentials = $credentials;
	}
	
	/**
	 * Get logger
	 * @return \ilLogger $logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}
	
	/**
	 * @return \ilAuthCredentials $credentials
	 */
	public function getCredentials()
	{
		return $this->credentials;
	}


	/**
	 * Get user id of 
	 * @return int $user_id
	 */
	public function getAuthenticatedUserId()
	{
		return $this->user_id;
	}
	
	/**
	 * Set id of authenticated user
	 * @param int $a_id
	 */
	public function setAuthenticatedUserId($a_id)
	{
		$this->user_id = $a_id;
	}


	/**
	 * Set auth status
	 * @param type $a_status
	 */
	public function setAuthenticationStatus($a_status)
	{
		$this->status = $a_status;
	}
	
	
	/**
	 * Get authentication status
	 * @return int
	 */
	public function getAuthenticationStatus()
	{
		return $this->status;
	}
}
?>