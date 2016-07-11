<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for auth methods (web form, http, ...)
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
interface ilAuthFrontendInterface
{
	public function login();
	
	public function logout();
	
	public function getStatus();
	
	public function isAuthenticated();
	
	public function isRemembered();
	
}
?>