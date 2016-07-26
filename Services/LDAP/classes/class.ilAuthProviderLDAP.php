<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/Provider/class.ilAuthProvider.php';
include_once './Services/Authentication/interfaces/interface.ilAuthProviderInterface.php';

/**
 * Description of class class 
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilAuthProviderLDAP extends ilAuthProvider implements ilAuthProviderInterface
{
	private $server = null;
	
	/**
	 * Constructor
	 * @param \ilAuthCredentials $credentials
	 */
	public function __construct(\ilAuthCredentials $credentials, $a_server_id = 0)
	{
		parent::__construct($credentials);
		$this->initServer($a_server_id);
	}
	
	/**
	 * Get server
	 * @return \ilLDAPServer
	 */
	public function getServer()
	{
		return $this->server;
	}
	
	
	/**
	 * Do authentication
	 * @param \ilAuthStatus $status
	 */
	public function doAuthentication(\ilAuthStatus $status)
	{
		try 
		{
			// bind 
			include_once './Services/LDAP/classes/class.ilLDAPQuery.php';
			$query = new ilLDAPQuery($this->getServer());
			$query->bind(IL_LDAP_BIND_DEFAULT);			
		}
		catch(ilLDAPQueryException $e)
		{
			$this->getLogger()->warning('Cannot bind to LDAP server... '. $e->getMessage());
			$this->handleAuthenticationFail($status, 'err_wrong_login');
			return false;
		}
		try 
		{
			// fetch user
			$users = $query->fetchUser($this->getCredentials()->getUsername());
			if(!$users)
			{
				$this->handleAuthenticationFail($status, 'err_wrong_login');
				return false;
			}
			if(!array_key_exists($this->getCredentials()->getUsername(), $users))
			{
				$this->handleAuthenticationFail($status, 'err_wrong_login');
			}
		} 
		catch (ilLDAPQueryException $e) {
			$this->getLogger()->warning('Cannot fetch LDAP user data... '. $e->getMessage());
			$this->handleAuthenticationFail($status, 'err_ldap_exception');
			return false;
		}
		try 
		{
			$query->bind(IL_LDAP_BIND_AUTH, $users[$this->getCredentials()->getUsername()]['dn'], $this->getCredentials()->getPassword());
			$status->setAuthenticatedUserId(6);
			$status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
			return true;
		} 
		catch (ilLDAPQueryException $e) {
			$this->handleAuthenticationFail($status, 'err_wrong_login');
			return false;
		}
		
		$this->handleAuthenticationFail($status, 'err_wrong_login');
		return false;
	}
	
	/**
	 * Handle failed authentication
	 * @param string $a_reason
	 */
	protected function handleAuthenticationFail(ilAuthStatus $status, $a_reason)
	{
		$status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
		$status->setReason($a_reason);
		return false;
		
	}

	
	/**
	 * Init Server
	 */
	protected function initServer($a_server_id)
	{
		include_once './Services/LDAP/classes/class.ilLDAPServer.php';
		$this->server = new ilLDAPServer($a_server_id);
	}
}
?>