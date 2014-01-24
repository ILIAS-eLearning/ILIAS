<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* WebDAV utility functions
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesWebDAV
*/
class ilDAVUtils
{
	private static $instance = null;
	
	private $pwd_instruction = null;
	
	/**
	 * Singleton constructor
	 * @return 
	 */
	private function __construct()
	{
		
	}
	
	/**
	 * Get singleton instance
	 * @return object ilDAVUtils
	 */
	public static function getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilDAVUtils();
	}
	
	/**
	 * 
	 * @return 
	 */
	public function isLocalPasswordInstructionRequired()
	{
		global $ilUser;
		
		if($this->pwd_instruction !== NULL)
		{
			return $this->pwd_instruction;
		}
		include_once './Services/Authentication/classes/class.ilAuthUtils.php';
		$status = ilAuthUtils::supportsLocalPasswordValidation($ilUser->getAuthMode(true));
		if($status != ilAuthUtils::LOCAL_PWV_USER)
		{
			return $this->pwd_instruction = false;
		}
		// Check if user has local password
		return $this->pwd_instruction = (bool) !strlen($ilUser->getPasswd());
	}
	
}
?>