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
	 * do authentication
	 * @return bool
	 */
	public function doAuthentication()
	{
		include_once './Services/User/classes/class.ilUserPasswordManager.php';

		/**
		 * @var $user ilObjUser
		 */
		$user = ilObjectFactory::getInstanceByObjId(ilObjUser::_loginExists($this->getCredentials()->getUsername()));
		
		if($user instanceof ilObjUser)
		{
			if(ilUserPasswordManager::getInstance()->verifyPassword($user, $this->getCredentials()->getPassword()))
			{
				$this->setAuthenticationStatus(self::STATUS_AUTHENTICATION_SUCCESS);
				$this->setAuthenticatedUserId($user->getId());
				return true;
			}
		}
		$this->setAuthenticationStatus(self::STATUS_AUTHENTICATION_FAILED);
		return false;
	}



}
?>