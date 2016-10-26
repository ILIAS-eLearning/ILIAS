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
class ilAuthProviderDatabase extends ilAuthProvider implements ilAuthProviderInterface
{

	
	/**
	 * Do authentication
	 * @return bool
	 */
	public function doAuthentication(ilAuthStatus $status)
	{
		include_once './Services/User/classes/class.ilUserPasswordManager.php';

		/**
		 * @var $user ilObjUser
		 */
		$user = ilObjectFactory::getInstanceByObjId(ilObjUser::_loginExists($this->getCredentials()->getUsername()),false);
		
		$this->getLogger()->debug('Trying to authenticate user: '. $this->getCredentials()->getUsername());
		if($user instanceof ilObjUser)
		{
			if(ilUserPasswordManager::getInstance()->verifyPassword($user, $this->getCredentials()->getPassword()))
			{
				$this->getLogger()->debug('Successfully authenticated user: ' . $this->getCredentials()->getUsername());
				$status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
				$status->setAuthenticatedUserId($user->getId());
				return true;
				
			}
		}
		$status->setStatus(ilAuthStatus::STATUS_AUTHENTICATION_FAILED);
		$status->setReason('err_wrong_login');
		return false;
	}



}
?>