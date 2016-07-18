<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Standard interface for auth provider implementations
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
interface ilAuthProviderInterface
{
	/**
	 * Do authentication
	 * @return bool
	 */
	public function doAuthentication();
	
}
?>