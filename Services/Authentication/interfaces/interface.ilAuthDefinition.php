<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of interface
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
interface ilAuthDefinition
{

	/**
	 * Get auth container instance
	 * 
	 * @return Auth_Container
	 */
	public function getContainer($a_auth_id);
	
	
	/**
	 * Get an authentication id.
	 * For plugins the auth must be greater than 1000 and unique
	 * 
	 * @see constants in ilAuthUtils
	 * @return array
	 */
	public function getAuthIds();
	
	
	/**
	 * Get the auth id by an auth mode name.
	 * the auth mode name is stored for each user in table usr_data
	 * 
	 * @see ilAuthUtils::_getAuthMode()
	 * @return int
	 */
	public function getAuthIdByName($a_auth_name);
	
	/**
	 * Get auth name by auth id
	 * @param type $a_auth_id
	 * @return string
	 */
	public function getAuthName($a_auth_id);
	
	/**
	 * Check if auth mode is active
	 * @return bool
	 */
	public function isAuthActive($a_auth_id);
	
	/**
	 * Check whther authentication supports sequenced authentication
	 * @see ilAuthContainerMultiple
	 */
	public function supportsMultiCheck($a_auth_id);
	
	/**
	 * Check if an external account name is required for this authentication method
	 * Normally this should return true
	 * 
	 * @return bool
	 */
	public function isExternalAccountNameRequired($a_auth_id);
	
	/**
	 * Check if authentication method allows password modifications
	 */
	public function isPasswordModificationAllowed($a_auth_id);
	
	/**
	 * Get local password validation type
	 * One of 
	 * ilAuthUtils::LOCAL_PWV_FULL
	 * ilAuthUtils::LOCAL_PWV_NO
	 * ilAuthUtils::LOCAL_PWV_USER
	 * 
	 * @return int
	 */
	public function getLocalPasswordValidationType($a_auth_id);
	
	/**
	 * Get an array of options for "multiple auth mode" selection 
	 * array(
	 *	AUTH_ID => array( 'txt' => NAME)
	 * )
	 * @param type $a_auth_id
	 * @return array
	 */
	public function getMultipleAuthModeOptions($a_auth_id);
}
?>
