<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface of auth credentials
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
interface ilAuthCredentials
{
	/**
	 * Set username
	 */
	public function setUsername($a_name);
	
	/**
	 * Get username
	 */
	public function getUsername();
	
	/**
	 * Set password
	 */
	public function setPassword($a_password);
	
	/**
	 * Get password
	 */
	public function getPassword();
	
}
?>