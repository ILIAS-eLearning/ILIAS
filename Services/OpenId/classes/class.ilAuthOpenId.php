<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @classDescription Open ID auth class
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 */
class ilAuthOpenId extends Auth
{
	private $settings = null;
	
	/**
	 * Contructor
	 * @return 
	 * @param object $a_container
	 * @param object $a_addition_options[optional]
	 */
	public function __construct($a_container,$a_addition_options = array())
	{
		parent::__construct(
			$a_container,
			$a_addition_options,
			array($this,'callProvider'),
			true);
		$this->setSessionName("_authhttp".md5(CLIENT_ID));
		
		$this->initAuth();
		$this->initSettings();
		
		if(isset($_GET['oid_check_status']))
		{
			$_POST['username'] = 'dummy';
			$_POST['password'] = 'dummy';
		}
		
	}

	/**
	 * Returns true, if the current auth mode allows redirection to e.g 
	 * to loginScreen, public section...
	 * 
	 * @todo check if redirects are possible
	 * 
	 * @return 
	 */
	public function supportsRedirects()
	{
		return true;
	}
	
	/**
	 * Auth login function
	 * Redirects to openid provider
	 * @param object $username
	 * @param object $status
	 * @param object $auth
	 * @return 
	 */
	public function callProvider($username,$status,$auth)
	{
		global $ilCtrl;
		
		$username = $_POST['oid_username'];
		
		if(!$this->parseUsername($username,$auth))
		{
			return false;
		}
		
		$consumer = $this->settings->getConsumer();
		$oid_auth = $consumer->begin($username);
		
		if (!$oid_auth) 
		{
    		$auth->status = AUTH_WRONG_LOGIN;
			return false;
		}

		include_once 'Auth/OpenID/SReg.php';
	    $sreg_req = Auth_OpenID_SRegRequest::build(
			// Required
			array('nickname'),
			// Optional
			array(
				'fullname',
				'dob',
				'email',
				'gender',
				'postcode',
				'language',
				'timezone'
			)
		);

	 	if ($sreg_req)
		{
			$oid_auth->addExtension($sreg_req);
		}

		// TODO: Switch openid v. 1,2
		$url = $oid_auth->redirectURL(ILIAS_HTTP_PATH,$this->settings->getReturnLocation());
		ilUtil::redirect($url);
	}
	
	/**
	 * Init open id settings
	 * @return 
	 */
	protected function initSettings()
	{
		include_once './Services/OpenId/classes/class.ilOpenIdSettings.php';
		$this->settings = ilOpenIdSettings::getInstance();
		$this->settings->initConsumer();
	}
	
	/**
	 * Parse username
	 * @return 
	 */
	protected function parseUsername(&$username,$auth)
	{
		if($_POST['oid_provider'])
		{
			include_once './Services/OpenId/classes/class.ilOpenIdProviders.php';
			try 
			{
				$url = ilOpenIdProviders::getInstance()->getProviderById($_POST['oid_provider'])->getURL();
				$username = sprintf($url,(string) $username);
				$GLOBALS['ilLog']->write(__METHOD__.': Using '.$username.' for authentication');
				return true;
			}
			catch(UnexpectedValueException $e)
			{
				$GLOBALS['ilLog']->write(__METHOD__.': Unknown provider id given: '.$username);
				$auth->status = AUTH_WRONG_LOGIN;
				return false;
			}
		}
		if($this->settings->forcedProviderSelection())
		{
			$auth->status = AUTH_WRONG_LOGIN;
			return false;
		}
		$GLOBALS['ilLog']->write(__METHOD__.': Trying openid url: '.$username);
		return true;
	}
}
?>