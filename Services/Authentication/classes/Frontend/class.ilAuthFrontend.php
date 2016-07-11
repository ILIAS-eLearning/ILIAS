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
	private $credentials = null;
	
	/**
	 * Constructor
	 * @param ilSession $session
	 * @param ilAuthCredentials $credentials
	 */
	public function __construct(ilAuthCredentials $credentials)
	{
		$this->credentials = $credentials;
	}
}
?>