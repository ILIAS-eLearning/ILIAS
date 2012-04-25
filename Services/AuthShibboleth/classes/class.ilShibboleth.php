<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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


/** @defgroup ServicesAuthShibboleth Services/AuthShibboleth
 */
 
include_once("Auth/Auth.php");

/**
* Class Shibboleth
*
* This class provides basic functionality for Shibboleth authentication
*
* @ingroup ServicesAuthShibboleth
*/
class ShibAuth extends Auth
{
	/**
	 * Username
	 *
	 * @var string
	 */
	var $username;
	
	/**
	 * Name to be used for session
	 *
	 * @var string
	 */
	var $_sessionName = '_authsession';
	
	/**
	 * Authentication status
	 *
	 * @var string
	 */
	var $status = '';
	
	/**
	 * Auth lifetime in seconds
	 *
	 * If this variable is set to 0, auth never expires
	 *
	 * @var  integer
	 * @see  setExpire(), checkAuth()
	 */
	var $expire = 0;
	
	/**
	 * Maximum time of idleness in seconds
	 *
	 * The difference to $expire is, that the idletime gets
	 * refreshed each time, checkAuth() is called. If this
	 * variable is set to 0, idle time is never checked.
	 *
	 * @var integer
	 * @see setIdle(), checkAuth()
	 */
	var $idle = 0;
	
	/**
	 * Is the maximum idletime over?
	 *
	 * @var boolean
	 * @see checkAuth(), drawLogin();
	 */
	var $idled = false;
	
	/**
	* Constructor
	* @access	public
	*/
	function ShibAuth($authParams, $updateUserData = false)
	{
        if ($authParams["sessionName"] != "") {
            parent::Auth("", array("sessionName" => $authParams["sessionName"]));
        }
        else {
            parent::Auth("");
        }

        $this->updateUserData = $updateUserData;


		if (!empty($authParams['sessionName'])) {
			$this->setSessionName($authParams['sessionName']);
			unset($authParams['sessionName']);
		}
		
	}
	
	/**
	 * Returns true, if the current auth mode allows redirection to e.g 
	 * to loginScreen, public section... 
	 * @return 
	 */
	public function supportsRedirects()
	{
		return true;
	} 
	
	
	
	/**
	* Deletes a role and deletes entries in object_data, rbac_pa, rbac_templates, rbac_ua, rbac_fa
	* @access	public
	* @param	integer		obj_id of role (role_id)
	* @param	integer		ref_id of role folder (ref_id)
	* @return	boolean     true on success
	*/
	function setIdle($time, $add = false)
	{
		if ($add) {
			$this->idle += $time;
		} else {
			$this->idle = $time;
		}
	}
	

	/**
	* Set the maximum expire time
	*
	* @access public
	* @param  integer time in seconds
	* @param  bool    add time to current expire time or not
	* @return void
	*/
	function setExpire($time, $add = false)
	{
		if ($add) {
			$this->expire += $time;
		} else {
			$this->expire = $time;
		}
	}


	/**
	* Login function
	*
	* @access private
	* @return void
	*/
	function login()
	{
		global $ilias, $rbacadmin, $ilSetting;
		if (!empty($_SERVER[$ilias->getSetting('shib_login')]))
		{
			
			// Store user's Shibboleth sessionID for logout
			$this->session['shibboleth_session_id'] = $_SERVER['Shib-Session-ID'];
			
			// Get loginname of user, new login name is generated if user is new
			$username = $this->generateLogin();
			
			// Authorize this user
			$this->setAuth($username);
			
			$userObj = new ilObjUser();
			
			// Check wether this account exists already, if not create it
			if (!ilObjUser::getUserIdByLogin($username))
			{
				
				$newUser["firstname"] = $this->getFirstString($_SERVER[$ilias->getSetting('shib_firstname')]);
				$newUser["lastname"] = $this->getFirstString($_SERVER[$ilias->getSetting('shib_lastname')]);
				$newUser["login"] = $username;
				
				// Password must be random to prevent users from manually log in using the login data from Shibboleth users
				$newUser["passwd"] = md5(end(ilUtil::generatePasswords(1))); 
				$newUser["passwd_type"] = IL_PASSWD_MD5; 
				
				if ( 
					$ilias->getSetting('shib_update_gender')
					&& ($_SERVER[$ilias->getSetting('shib_gender')] == 'm'
					|| $_SERVER[$ilias->getSetting('shib_gender')] =='f')
					)
				{
					$newUser["gender"] = $_SERVER[$ilias->getSetting('shib_gender')];
				}
				
				// Save mapping between ILIAS user and Shibboleth uniqueID
				$newUser["ext_account"] = $_SERVER[$ilias->getSetting('shib_login')];
				
				// other data
				$newUser["title"] = $_SERVER[$ilias->getSetting('shib_title')];
				$newUser["institution"] = $_SERVER[$ilias->getSetting('shib_institution')];
				$newUser["department"] = $_SERVER[$ilias->getSetting('shib_department')];
				$newUser["street"] = $_SERVER[$ilias->getSetting('shib_street')];
				$newUser["city"] = $_SERVER[$ilias->getSetting('shib_city')];
				$newUser["zipcode"] = $_SERVER[$ilias->getSetting('shib_zipcode')];
				$newUser["country"] = $_SERVER[$ilias->getSetting('shib_country')];
				$newUser["phone_office"] = $this->getFirstString($_SERVER[$ilias->getSetting('shib_phone_office')]);
				$newUser["phone_home"] = $this->getFirstString($_SERVER[$ilias->getSetting('shib_phone_home')]);
				$newUser["phone_mobile"] = $this->getFirstString($_SERVER[$ilias->getSetting('shib_phone_mobile')]);
				$newUser["fax"] = $this->getFirstString($_SERVER[$ilias->getSetting('shib_fax')]);
				$newUser["matriculation"] = $_SERVER[$ilias->getSetting('shib_matriculation')];
				$newUser["email"] = $this->getFirstString($_SERVER[$ilias->getSetting('shib_email')]);
				$newUser["hobby"] = $_SERVER[$ilias->getSetting('shib_hobby')];
				$newUser["auth_mode"] = "shibboleth";
				
				
				// system data
				$userObj->assignData($newUser);
				$userObj->setTitle($userObj->getFullname());
				$userObj->setDescription($userObj->getEmail());
				$userObj->setLanguage($this->getFirstString($_SERVER[$ilias->getSetting('shib_language')]));
				
				// Time limit
				$userObj->setTimeLimitOwner(7);
				$userObj->setTimeLimitUnlimited(1);
				$userObj->setTimeLimitFrom(time());
				$userObj->setTimeLimitUntil(time());
				
				// Modify user data before creating the user
				// Include custom code that can be used to further modify
				// certain Shibboleth user attributes
				if (	$ilias->getSetting('shib_data_conv') 
						&& $ilias->getSetting('shib_data_conv') != ''
						&& is_readable($ilias->getSetting('shib_data_conv'))
						)
				{
					include($ilias->getSetting('shib_data_conv'));
				}

				// Create use in DB
				$userObj->create();
				$userObj->setActive(1);
				
				$userObj->updateOwner();
				
				//insert user data in table user_data
				$userObj->saveAsNew();
				
				// store acceptance of user agreement
				//$userObj->writeAccepted();

				// Default prefs
				$userObj->setPref('hits_per_page',$ilSetting->get('hits_per_page',30));
				$userObj->setPref('show_users_online',$ilSetting->get('show_users_online','y'));
				
				// setup user preferences
				$userObj->writePrefs();
				
				//set role entries
				#$rbacadmin->assignUser($ilias->getSetting('shib_user_default_role'), $userObj->getId(),true);
				// New role assignment
				include_once './Services/AuthShibboleth/classes/class.ilShibbolethRoleAssignmentRules.php';
				ilShibbolethRoleAssignmentRules::doAssignments($userObj->getId(),$_SERVER);
				
                // Authorize this user
                $this->setAuth($userObj->getLogin());
				
			}
			else
			{
				// Update user account
				$uid = $userObj->checkUserId();
			        $userObj->setId($uid);
				$userObj->read($uid);
				
				if ( 
					$ilias->getSetting('shib_update_gender')
					&& ($_SERVER[$ilias->getSetting('shib_gender')] == 'm'
					|| $_SERVER[$ilias->getSetting('shib_gender')] =='f')
					)
					$userObj->setGender($_SERVER[$ilias->getSetting('shib_gender')]);
				
				if ($ilias->getSetting('shib_update_title'))
					$userObj->setTitle($_SERVER[$ilias->getSetting('shib_title')]);
				
				$userObj->setFirstname($this->getFirstString($_SERVER[$ilias->getSetting('shib_firstname')]));
				$userObj->setLastname($this->getFirstString($_SERVER[$ilias->getSetting('shib_lastname')]));
				$userObj->setFullname();
				if ($ilias->getSetting('shib_update_institution'))
					$userObj->setInstitution($_SERVER[$ilias->getSetting('shib_institution')]);
				if ($ilias->getSetting('shib_update_department'))
					$userObj->setDepartment($_SERVER[$ilias->getSetting('shib_department')]);
				if ($ilias->getSetting('shib_update_street'))
					$userObj->setStreet($_SERVER[$ilias->getSetting('shib_street')]);
				if ($ilias->getSetting('shib_update_city'))
					$userObj->setCity($_SERVER[$ilias->getSetting('shib_city')]);
				if ($ilias->getSetting('shib_update_zipcode'))
					$userObj->setZipcode($_SERVER[$ilias->getSetting('shib_zipcode')]);
				if ($ilias->getSetting('shib_update_country'))
					$userObj->setCountry($_SERVER[$ilias->getSetting('shib_country')]);
				if ($ilias->getSetting('shib_update_phone_office'))
					$userObj->setPhoneOffice($this->getFirstString($_SERVER[$ilias->getSetting('shib_phone_office')]));
				if ($ilias->getSetting('shib_update_phone_home'))
					$userObj->setPhoneHome($this->getFirstString($_SERVER[$ilias->getSetting('shib_phone_home')]));
				if ($ilias->getSetting('shib_update_phone_mobile'))
					$userObj->setPhoneMobile($this->getFirstString($_SERVER[$ilias->getSetting('shib_phone_mobile')]));
				if ($ilias->getSetting('shib_update_fax'))
					$userObj->setFax($_SERVER[$ilias->getSetting('shib_fax')]);
				if ($ilias->getSetting('shib_update_matriculation'))
					$userObj->setMatriculation($_SERVER[$ilias->getSetting('shib_matriculation')]);
				if ($ilias->getSetting('shib_update_email'))
					$userObj->setEmail($this->getFirstString($_SERVER[$ilias->getSetting('shib_email')]));
				if ($ilias->getSetting('shib_update_hobby'))
					$userObj->setHobby($_SERVER[$ilias->getSetting('shib_hobby')]);
				
				if ($ilias->getSetting('shib_update_language'))
					$userObj->setLanguage($_SERVER[$ilias->getSetting('shib_language')]);
				
				// Include custom code that can be used to further modify
				// certain Shibboleth user attributes
				if (	$ilias->getSetting('shib_data_conv') 
						&& $ilias->getSetting('shib_data_conv') != ''
						&& is_readable($ilias->getSetting('shib_data_conv'))
						)
				{
					include($ilias->getSetting('shib_data_conv'));
				}

				
				$userObj->update();

				// Update role assignments				
				include_once './Services/AuthShibboleth/classes/class.ilShibbolethRoleAssignmentRules.php';
				ilShibbolethRoleAssignmentRules::updateAssignments($userObj->getId(),$_SERVER);
			}
			
			// we are authenticated: redirect, if possible
			if ($_GET["target"] != "")
			{
				ilUtil::redirect("goto.php?target=".$_GET["target"]."&client_id=".CLIENT_ID);
			}
		}
		else
		{
			// This should never occur unless Shibboleth is not configured properly
			$this->status = AUTH_WRONG_LOGIN;
		}
	}

	/**
	* Logout function
	*
	* This function clears any auth tokens in the currently
	* active session and executes the logout callback function,
	* if any
	*
	* @access public
	* @return void
	*/
	function logout()
	{
		parent::logout();
	}
	
	
	/**
	* Automatically generates the username/screenname of a Shibboleth user or returns
	* the user's already existing username
	*
	* @access private
	* @return String Generated username
	*/
	function generateLogin()
	{
		global $ilias, $ilDB;
		
		$shibID = $_SERVER[$ilias->getSetting('shib_login')];
		$lastname = $this->getFirstString($_SERVER[$ilias->getSetting('shib_lastname')]);
		$firstname = $this->getFirstString($_SERVER[$ilias->getSetting('shib_firstname')]);
		
		if (trim($shibID) == "")
		{
			return;
		}

		//***********************************************//
		// For backwards compatibility with previous versions
		// We use the passwd field as mapping attribute for Shibboleth users
		// because they don't need a password
		$ilias->db->query("UPDATE usr_data SET auth_mode='shibboleth', passwd=".$ilDB->quote(md5(end(ilUtil::generatePasswords(1)))).", ext_account=".$ilDB->quote($shibID)." WHERE passwd=".$ilDB->quote($shibID));
		//***********************************************//
		
		// Let's see if user already is registered
		$local_user = ilObjUser::_checkExternalAuthAccount("shibboleth", $shibID);
		if ($local_user)
		{
			return $local_user;
		}
		
		// Let's see if user already is registered but authenticates by ldap
		$local_user = ilObjUser::_checkExternalAuthAccount("ldap", $shibID);
		if ($local_user)
		{
			return $local_user;
		}
                
		// User doesn't seem to exist yet
		
		// Generate new username
		// This can be overruled by the data conversion API but you have
		// to do it yourself in that case
		
		// Generate the username out of the first character of firstname and the
		// first word in lastname (adding the second one if the login is too short,
		// avoiding meaningless last names like 'von' or 'd' and eliminating
		// non-ASCII-characters, spaces, dashes etc.
		
		$ln_arr=preg_split("/[ '-;]/", $lastname);
		$login=substr($this->toAscii($firstname),0,1) . "." . $this->toAscii($ln_arr[0]); 
		if (strlen($login) < 6) $login .= $this->toAscii($ln_arr[1]);
		$prefix = strtolower($login);
		
		// If the user name didn't contain any ASCII characters, assign the
		// name 'shibboleth' followed by a number, starting with 1.
		if (strlen($prefix) == 0) {
				$prefix = 'shibboleth';
				$number = 1;
		}
		else
		{
			// Try if the login name is not already taken
			if (!ilObjUser::getUserIdByLogin($prefix))
			{
				return $prefix;
			}
			
			// If the login name is in use, append a number, starting with 2.
			$number = 2;
		}        
		
		// Append a number, if the username is already taken
		while (ilObjUser::getUserIdByLogin($prefix.$number))
		{
			$number++;
		}
		
		return $prefix.$number;
	}
	
	/**
	* Cleans and returns first of potential many values (multi-valued attributes)
	*
	* @access private
	* @param string A Shibboleth attribute or other string
	* @return string First value of attribute
	*/
	function getFirstString($string){
	
		$list = split( ';', $string);
		$clean_string = rtrim($list[0]);
		
		return $clean_string;
		
	}
	
	/**
	* Replaces any non-ASCII character by its linguistically most logical substitution
	*
	* @access private
	* @param string A Shibboleth attribute or other string
	* @return string ascii-version of attribute
	*/
	function toAscii($string) {
		require_once('include/Unicode/UtfNormal.php');

		// Normalize to NFKD. 
		// This separates letters from combining marks.
		// See http://unicode.org/reports/tr15
		$string = UtfNormal::toNFKD($string);

		// Replace german usages of diaeresis by appending an e
		$string = preg_replace('/([aouAOU])\\xcc\\x88/','\\1e', $string);

		// Replace the combined ae character by separated a and e
		$string = preg_replace('/\\xc3\\x86/','AE', $string);
		$string = preg_replace('/\\xc3\\xa6/','ae', $string);

		// Replace the combined thorn character by th
		$string = preg_replace('/\\xc3\\x9e/','TH', $string);
		$string = preg_replace('/\\xc3\\xbe/','th', $string);

		// Replace the letter eth by d
		$string = preg_replace('/\\xc3\\x90/','D', $string);
		$string = preg_replace('/\\xc4\\x91/','d', $string);
		$string = preg_replace('/\\xc4\\x90/','D', $string);

		// Replace the combined ss character
		$string = preg_replace('/\\xc3\\x9f/','ss', $string);

		// Get rid of everything except the characters a to z and the hyphen
		$string = preg_replace('/[^a-zA-Z\-]/i','', $string);

		return $string;
	}

} // END class.ilShibAuth
?>
