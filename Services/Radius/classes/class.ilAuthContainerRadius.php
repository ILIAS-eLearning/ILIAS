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

include_once('Auth/Container/RADIUS.php');

/** 
* @classDescription Overwritten Pear class AuthContainerRadius
* This class is overwritten to support to perform Radius authentication with
* specific ILIAS options.
*
* Usage note:
*
* FIXME - Class ilAuthRadius contains duplicates of the code of this class in the
*       functions loginObserver, and failedLoginObserver. If you do changes in
*       these functions, you MUST do corresponding changes in ilAuthRadius as well.
*       In a future revision of ILIAS, the class ilAuthRadius should be removed.
* DONE: smeyer
*       
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ServicesRadius
*/
class ilAuthContainerRadius extends Auth_Container_Radius
{
	private $radius_settings = null;
	private $rad_to_user = null;
	private $log = null;
	private $force_creation = false;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param array An associative array of pear parameters
	 * 
	 */
	public function __construct()
	{
		$this->initSettings();
		
		// Convert password to latin1
		if($this->radius_settings->getCharset() == ilRadiusSettings::RADIUS_CHARSET_LATIN1)
		{
			#$_POST['username'] = utf8_decode($_POST['username']);
			#$_POST['password'] = utf8_decode($_POST['password']);
			$this->log->write(__METHOD__.': Decoded username and password to latin1.');
		}

		parent::__construct($this->radius_settings->toPearAuthArray());

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
	public function loginObserver($a_username,$a_auth)
	{
		$user_data = array_change_key_case($a_auth->getAuthData(),CASE_LOWER);
		$user_data['ilInternalAccount'] = ilObjUser::_checkExternalAuthAccount("radius",$a_username);
		
		if(!$user_data['ilInternalAccount'])
		{
			if($this->radius_settings->enabledCreation())
			{
				if($this->radius_settings->isAccountMigrationEnabled() and !$this->force_creation)
				{
					$a_auth->logout();
					$_SESSION['tmp_auth_mode'] = 'radius';
					$_SESSION['tmp_external_account'] = $a_username;
					$_SESSION['tmp_pass'] = $_POST['password'];
					$_SESSION['tmp_roles'] = array(0 => $this->radius_settings->getDefaultRole());
				
					ilUtil::redirect('ilias.php?baseClass=ilStartUpGUI&cmd=showAccountMigration&cmdClass=ilstartupgui');
				}
				$this->initRADIUSAttributeToUser();
				$new_name = $this->radius_user->create($a_username);
				$a_auth->setAuth($new_name);
				return true;
			}
			else
			{
				// No syncronisation allowed => create Error
				$a_auth->status = AUTH_RADIUS_NO_ILIAS_USER;
				$a_auth->logout();
				return false;
			}
			
		}
		else
		{
			$a_auth->setAuth($user_data['ilInternalAccount']);
			return true;
		}
	}
	
	/**
	 * Init radius settings
	 * @return void 
	 */
	private function initSettings()
	{
		include_once 'Services/Radius/classes/class.ilRadiusSettings.php';
		$this->radius_settings = ilRadiusSettings::_getInstance();
	}
	
	
	/**
	 * Init RADIUS attribute mapping
	 *
	 * @access private
	 * 
	 */
	private function initRADIUSAttributeToUser()
	{
		include_once('Services/Radius/classes/class.ilRadiusAttributeToUser.php');
		$this->radius_user = new ilRadiusAttributeToUser();
	}
}

?>