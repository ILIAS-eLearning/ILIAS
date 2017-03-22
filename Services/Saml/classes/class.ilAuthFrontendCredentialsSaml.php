<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Authentication/classes/Frontend/class.ilAuthFrontendCredentials.php';
require_once 'Services/Authentication/interfaces/interface.ilAuthCredentials.php';

/**
 * Class ilAuthFrontendCredentialsSaml
 */
class ilAuthFrontendCredentialsSaml extends ilAuthFrontendCredentials implements ilAuthCredentials
{
	/**
	 * ilAuthFrontendCredentialsSaml constructor.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Init credentials from request
	 */
	public function initFromRequest()
	{
		$this->getLogger()->dump($_SERVER, ilLogLevel::DEBUG);

		$this->setUsername('dummy');
		$this->setPassword('');
	}
}