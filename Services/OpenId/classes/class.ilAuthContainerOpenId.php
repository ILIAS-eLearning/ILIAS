<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Auth/Container.php';

/**
 * @classDescription Pear auth container for openid 
 * 
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * 
 */
class ilAuthContainerOpenId extends Auth_Container
{
	private $settings = null;
	
	private $response_data = array();
	private $force_creation = false;
	
	/**
	 * Constructor
	 * @return 
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->initSettings();
	}
	
	/**
	 * 
	 * @return bool 
	 * @param string $a_username
	 * @param string $a_password
	 * @param bool $isChallengeResponse[optional]
	 */
	public function fetchData($a_username,$a_password,$isChallengeResponse = false)
	{
		global $ilLog;
		
		$ilLog->write(__METHOD__.': Fetch Data called');
		
		$response = $this->settings->getConsumer()->complete($this->settings->getReturnLocation());
		
		switch($response->status)
		{
			case Auth_OpenID_CANCEL:
				die("Auth cancelled");
			
			case Auth_OpenID_FAILURE:
				die("Auth failed with message: ".$response->message);
				
			case Auth_OpenID_SUCCESS:
				$openid = $response->getDisplayIdentifier();
		        $esc_identity = htmlentities($openid);
				$ilLog->write(__METHOD__.': Auth success with identity '.$esc_identity);

		        if($response->endpoint->canonicalID) 
				{
            		$escaped_canonicalID = htmlentities($response->endpoint->canonicalID);
					$ilLog->write(__METHOD__.': Auth success with canonical id: '.$esc_identity);

        		}
				include_once 'Auth/OpenID/SReg.php';
				$sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
		        $this->response_data = $sreg_resp->contents();

				$ilLog->write(__METHOD__.' auth data: '.print_r($this->response_data,true));
				return true;

		}
		return false;
	}
	
	/**
	 * Force creation of user accounts
	 *
	 * @access public
	 * @param bool force_creation
	 * 
	 */
	public function forceCreation($a_status)
	{
	 	$this->force_creation = true;
	}
	
	
	/**
	 * @see ilAuthContainerBase::loginObserver()
	 */
	public function loginObserver($a_username,$a_auth)
	{
		global $ilLog;
		
		$this->initSettings();
		$this->response_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount(
			"openid",
			$this->response_data['nickname']
		);
		if(!$this->response_data['ilInternalAccount'])
		{
			if($this->settings->isCreationEnabled())
			{
				if($this->settings->isAccountMigrationEnabled() and !$this->force_creation and !$_SESSION['force_creation'])
				{
					$a_auth->logout();
					$_SESSION['tmp_auth_mode'] = 'openid';
					$_SESSION['tmp_oid_username'] = urldecode($_GET['openid_identity']);
					$_SESSION['tmp_external_account'] = $this->response_data['nickname'];
					$_SESSION['tmp_pass'] = $_POST['password'];
					$_SESSION['tmp_roles'] = array(0 => $this->settings->getDefaultRole());
				
					ilUtil::redirect('ilias.php?baseClass=ilStartUpGUI&cmd=showAccountMigration&cmdClass=ilstartupgui');
				}
				
				include_once './Services/OpenId/classes/class.ilOpenIdAttributeToUser.php';
				$new_user = new ilOpenIdAttributeToUser();
				$new_name = $new_user->create($this->response_data['nickname'],$this->response_data);

				$a_auth->setAuth($new_name);
				return true;
			}
			else
			{
				// No syncronisation allowed => create Error
				$a_auth->status = AUTH_OPENID_NO_ILIAS_USER;
				$a_auth->logout();
				return false;
			}
			
		}
		else
		{
			$a_auth->setAuth($this->response_data['ilInternalAccount']);
			return true;
		}
		return false;
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
}
?>