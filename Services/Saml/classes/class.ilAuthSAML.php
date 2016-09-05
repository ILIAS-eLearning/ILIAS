<?php
// saml-patch: begin
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Saml/lib/simplesamlphp/lib/_autoload.php';

/**
 * Class ilAuthSAML
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilAuthSAML extends Auth
{
	/**
	 * ilAuthSAML constructor.
	 * @param        $a_container
	 * @param array  $a_addition_options
	 * @param string $loginFunction
	 * @param bool   $showLogin
	 */
	public function __construct($a_container,$a_addition_options = array(), $loginFunction = '', $showLogin = true)
	{
		parent::__construct($a_container, $a_addition_options, $loginFunction, $showLogin);
		if(!ilSession::get('tmp_external_account'))
		{
			$_SESSION = array(); // Used to prevent issues with an existing session (e.g. of another user) 
		}
		$this->setSessionName('_authhttp' . md5(CLIENT_ID));
		$this->initAuth();
	}

	/**
	 *
	 */
	public function assignData()
	{
		$this->username = 'dummy';
		$this->password = '';
	}

	/**
	 * @return bool
	 */
	public function supportsRedirects()
	{
		return true;
	}
}
// saml-patch: end