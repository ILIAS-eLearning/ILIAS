<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Handles cron (cli) request
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilCronStartUp
{
	private $client = '';
	private $username = '';
	private $password = '';
	
	/**
	 * Constructor
	 */
	public function __construct($a_client_id, $a_login, $a_password)
	{
		$this->client = $a_client_id;
		$this->username = $a_login;
		$this->password = $a_password;
	}
	
	/** 
	 * Init ILIAS
	 */
	public function initIlias()
	{
		include_once './Services/Context/classes/class.ilContext.php';
		ilContext::init(ilContext::CONTEXT_CRON);
		
		// define client
		// @see mantis 20371
		$_GET['client_id'] = $this->client;
		
		include_once './include/inc.header.php';
	}
	
	

	/**
	 * Start authentication
	 * @return bool
	 * 
	 * @throws ilCronException if authentication failed.
	 */
	public function authenticate()
	{
		include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentials.php';
		$credentials = new ilAuthFrontendCredentials();
		$credentials->setUsername($this->username);
		$credentials->setPassword($this->password);
		
		include_once './Services/Authentication/classes/Provider/class.ilAuthProviderFactory.php';
		$provider_factory = new ilAuthProviderFactory();
		$providers = $provider_factory->getProviders($credentials);
			
		include_once './Services/Authentication/classes/class.ilAuthStatus.php';
		$status = ilAuthStatus::getInstance();
			
		include_once './Services/Authentication/classes/Frontend/class.ilAuthFrontendFactory.php';
		$frontend_factory = new ilAuthFrontendFactory();
		$frontend_factory->setContext(ilAuthFrontendFactory::CONTEXT_CLI);
		$frontend = $frontend_factory->getFrontend(
			$GLOBALS['DIC']['ilAuthSession'],
			$status,
			$credentials,
			$providers
		);
			
		$frontend->authenticate();
			
		switch($status->getStatus())
		{
			case ilAuthStatus::STATUS_AUTHENTICATED:
				ilLoggerFactory::getLogger('auth')->debug('Authentication successful; Redirecting to starting page.');
				return true;
				

			default:
			case ilAuthStatus::STATUS_AUTHENTICATION_FAILED:
				include_once './Services/Cron/exceptions/class.ilCronException.php';
				throw new ilCronException($status->getTranslatedReason());
		}				
		return true;
	}

	/**
	 * Closes the current auth session
	 */
	public function logout()
	{
		ilSession::setClosingContext(ilSession::SESSION_CLOSE_USER);
		if (isset($GLOBALS['DIC']['ilAuthSession'])) {
			$GLOBALS['DIC']['ilAuthSession']->logout();
		}
	}
}