<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/** 
* Auth Radius overwrites PEAR Auth to perform Radius authentication with specific ILIAS options
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesRadius
*/

include_once('Auth/Auth.php');

class ilAuthRadius extends Auth
{
	private $radius_settings = null;	
	private $rad_to_user = null;
	private $log = null;
	
	private $force_creation = false;
	
	public function __construct()
	{
		global $ilLog;
		
		$this->log = $ilLog;
		
		// Read setting of LDAP server
		$this->initSettings();
		parent::Auth('RADIUS',$this->radius_settings->toPearAuthArray(),'',false);
		
		// Set callbacks
		$this->setCallbacks();
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
	 * Called from base class after successful login
	 *
	 * @param string username
	 */
	protected function loginObserver($a_username)
	{
		$user_data = array_change_key_case($this->getAuthData(),CASE_LOWER);
		
		$user_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount("radius",$a_username);
		
		if(!$user_data['ilInternalAccount'])
		{
			if($this->radius_settings->enabledCreation())
			{
				if($this->radius_settings->isAccountMigrationEnabled() and !$this->force_creation)
				{
					$this->logout();
					$_SESSION['tmp_auth_mode'] = 'radius';
					$_SESSION['tmp_external_account'] = $a_username;
					$_SESSION['tmp_pass'] = $_POST['password'];
				
					ilUtil::redirect('ilias.php?baseClass=ilStartUpGUI&cmd=showAccountMigration');
				}
				$this->initAttributeToUser();
				$new_name = $this->radius_user->create($a_username);
				$this->setAuth($new_name);
				return true;
			}
			else
			{
				// No syncronisation allowed => create Error
				$this->status = AUTH_RADIUS_NO_ILIAS_USER;
				$this->logout();
				return false;
			}
			
		}
		else
		{
			$this->setAuth($user_data['ilInternalAccount']);
			return true;
		}
	}
	
	/** 
	 * Called from base class after failed login
	 *
	 * @param string username
	 */
	protected function failedLoginObserver()
	{
		#$this->log->write($this->logCache);
		$this->logCache = '';
	}
	
	
	private function initSettings()
	{
		include_once 'Services/Radius/classes/class.ilRadiusSettings.php';
		$this->radius_settings = ilRadiusSettings::_getInstance();
	}
	
	private function initAttributeToUser()
	{
		include_once('Services/Radius/classes/class.ilRadiusAttributeToUser.php');
		$this->radius_user = new ilRadiusAttributeToUser();
	}
	
	/** 
	 * Set callback function for PEAR Auth 
	 *
	 */
	private function setCallbacks() 
	{
		$this->setLoginCallback(array($this,'loginObserver'));
		$this->setFailedLoginCallback(array($this,'failedLoginObserver'));
	}
	
}

?>