<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Authentication/classes/Provider/class.ilAuthProvider.php';
include_once './Services/Authentication/interfaces/interface.ilAuthProviderInterface.php';

/**
 * OAuth based lti authentication
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilAuthProviderLTI extends ilAuthProvider implements ilAuthProviderInterface
{
	/**
	 * Do authentication
	 * @param \ilAuthStatus $status
	 */
	public function doAuthentication(\ilAuthStatus $status)
	{
		$status->setStatus(ilAuthStatus::STATUS_AUTHENTICATED);
		$status->setAuthenticatedUserId(6);
		return true;
	}

}
?>