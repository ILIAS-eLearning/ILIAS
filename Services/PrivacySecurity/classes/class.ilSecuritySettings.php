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
* Singleton class that stores all security settings
*
* @author Roland Küstermann <roland@kuestermann.com>
* @version $Id$
*
*
* @ingroup Services/PrivacySecurity
*/

class ilSecuritySettings
{
    public static $SECURITY_SETTINGS_ERR_CODE_AUTO_HTTPS = 1;
    public static $SECURITY_SETTINGS_ERR_CODE_HTTP_NOT_AVAILABLE = 2;
    public static $SECURITY_SETTINGS_ERR_CODE_HTTPS_NOT_AVAILABLE = 3;

	const SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MIN_LENGTH			= 4;
	const SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_LENGTH			= 5;
	const SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_AGE				= 6;
	const SECURITY_SETTINGS_ERR_CODE_INVALID_LOGIN_MAX_ATTEMPTS				= 7;
	const SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN1				= 11;
	const SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN2				= 8;
	const SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN3				= 9;
	const SECURITY_SETTINGS_ERR_CODE_PASSWORD_MAX_LENGTH_LESS_MIN_LENGTH	= 10;

    private static $instance = null;
	private $db;
	private $settings;

	private $https_enable;
	
	const DEFAULT_PASSWORD_CHARS_AND_NUMBERS_ENABLED	= true;
	const DEFAULT_PASSWORD_SPECIAL_CHARS_ENABLED		= false;
	const DEFAULT_PASSWORD_MIN_LENGTH					= 8;
	const DEFAULT_PASSWORD_MAX_LENGTH					= 0;
	const DEFAULT_PASSWORD_MAX_AGE						= 90;
	const DEFAULT_LOGIN_MAX_ATTEMPTS					= 5;

	const DEFAULT_PASSWORD_CHANGE_ON_FIRST_LOGIN_ENABLED = false;
	const DEFAULT_PREVENT_SIMULTANEOUS_LOGINS = false;

	private $password_chars_and_numbers_enabled = self::DEFAULT_PASSWORD_CHARS_AND_NUMBERS_ENABLED;
	private $password_special_chars_enabled = self::DEFAULT_PASSWORD_SPECIAL_CHARS_ENABLED;
	private $password_min_length = self::DEFAULT_PASSWORD_MIN_LENGTH;
	private $password_max_length = self::DEFAULT_PASSWORD_MAX_LENGTH;
	private $password_max_age = self::DEFAULT_PASSWORD_MAX_AGE;
	private $password_ucase_chars_num = 0;
	private $password_lcase_chars_num = 0;
	private $login_max_attempts = self::DEFAULT_LOGIN_MAX_ATTEMPTS;
	private $password_must_not_contain_loginname = false;

	private $password_change_on_first_login_enabled = self::DEFAULT_PASSWORD_CHANGE_ON_FIRST_LOGIN_ENABLED;
	private $prevent_simultaneous_logins = self::DEFAULT_PREVENT_SIMULTANEOUS_LOGINS;
	
	private $protect_admin_role = false;

	/**
	 * Private constructor: use _getInstance()
	 *
	 * @access private
	 * @param
	 *
	 */
	private function __construct()
	{

		global $ilSetting,$ilDB;

		$this->db = $ilDB;
		$this->settings = $ilSetting;

	 	$this->read();
	}

	/**
	 * Get instance of ilSecuritySettings
	 *
	 * @return ilSecuritySettings  instance
	 * @access public
	 *
	 */
	public static function _getInstance()
	{
		if(is_object(self::$instance))
		{
			return self::$instance;
		}
	 	return self::$instance = new ilSecuritySettings();
	}

	public function getSecuritySettingsRefId()
	{
		return $this->ref_id;
	}

	/**
	 * set if the passwords have to contain
	 * characters and numbers
	 *
	 * @param boolean $a_chars_and_numbers_enabled
	 *
	 */
	 public function setPasswordCharsAndNumbersEnabled($a_chars_and_numbers_enabled)
	 {
	 	$this->password_chars_and_numbers_enabled = $a_chars_and_numbers_enabled;
	 }

	/**
	 * get boolean if the passwords have to contain
	 * characters and numbers
	 *
	 * @return boolean	characters and numbers enabled
	 *
	 */
	 public function isPasswordCharsAndNumbersEnabled()
	 {
	 	return $this->password_chars_and_numbers_enabled;
	 }

	/**
	 * set if the passwords have to contain
	 * special characters
	 *
	 * @param boolean $a_password_special_chars_enabled
	 *
	 */
	 public function setPasswordSpecialCharsEnabled($a_password_special_chars_enabled)
	 {
	 	$this->password_special_chars_enabled = $a_password_special_chars_enabled;
	 }

	/**
	 * get boolean if the passwords have to contain
	 * special characters
	 *
	 * @return boolean	password special chars enabled
	 *
	 */
	 public function isPasswordSpecialCharsEnabled()
	 {
	 	return $this->password_special_chars_enabled;
	 }

	/**
	 * set the minimum length for passwords
	 *
	 * @param integer $a_password_min_length
	 */
	public function setPasswordMinLength($a_password_min_length)
	{
	    $this->password_min_length = $a_password_min_length;
	}

	/**
	 * get the minimum length for passwords
	 *
	 * @return integer  password min length
	 */
	public function getPasswordMinLength()
	{
	    return $this->password_min_length;
	}

	/**
	 * set the maximum length for passwords
	 *
	 * @param integer $a_password_max_length
	 */
	public function setPasswordMaxLength($a_password_max_length)
	{
	    $this->password_max_length = $a_password_max_length;
	}

	/**
	 * get the maximum length for passwords
	 *
	 * @return integer  password max length
	 */
	public function getPasswordMaxLength()
	{
	    return $this->password_max_length;
	}

	/**
	 * set the maximum password age
	 *
	 * @param integer $a_password_max_age
	 */
	public function setPasswordMaxAge($a_password_max_age)
	{
	    $this->password_max_age = $a_password_max_age;
	}

	/**
	 * get the maximum password age
	 *
	 * @return integer  password max age
	 */
	public function getPasswordMaxAge()
	{
	    return $this->password_max_age;
	}

	/**
	 * set the maximum count of login attempts
	 *
	 * @param integer $a_login_max_attempts
	 */
	public function setLoginMaxAttempts($a_login_max_attempts)
	{
	    $this->login_max_attempts = $a_login_max_attempts;
	}

	/**
	 * get the maximum count of login attempts
	 *
	 * @return integer  password max login attempts
	 */
	public function getLoginMaxAttempts()
	{
	    return $this->login_max_attempts;
	}

	/**
	 * Enable https for certain scripts
	 *
	 * @param boolean $value
	 */
	public function setHTTPSEnabled ($value)
	{
		$this->https_enable = $value;
	}

	/**
	 * read access to https enabled property
	 *
	 * @return boolean  true, if enabled, false otherwise
	 */
	public function isHTTPSEnabled ()
	{
		return $this->https_enable;
	}
	
	/**
	 * set if the passwords have to be changed by users
	 * on first login
	 *
	 * @param boolean $a_password_change_on_first_login_enabled
	 *
	 */
	 public function setPasswordChangeOnFirstLoginEnabled($a_password_change_on_first_login_enabled)
	 {
	 	$this->password_change_on_first_login_enabled = $a_password_change_on_first_login_enabled;
	 }

	/**
	 * get boolean if the passwords have to be changed by users
	 * on first login
	 *
	 * @return boolean	password change on first login enabled
	 *
	 */
	 public function isPasswordChangeOnFirstLoginEnabled()
	 {
	 	return $this->password_change_on_first_login_enabled;
	 }
	 
	 /**
	  * Check if admin role is protected
	  * @return type
	  */
	 public function isAdminRoleProtected()
	 {
		 return (bool) $this->protect_admin_role;
	 }
	 
	 /**
	  * Set admin role protection status
	  * @param type $a_stat
	  */
	 public function protectedAdminRole($a_stat)
	 {
		 $this->protect_admin_role = $a_stat;
	 }
	 
	 /**
	  * Check if the administrator role is accessible for a specific user
	  * @param int $a_usr_id
	  */
	 public function checkAdminRoleAccessible($a_usr_id)
	 {
		 global $rbacreview;
		 
		 if(!$this->isAdminRoleProtected())
		 {
			 return true;
		 }
		 if($rbacreview->isAssigned($a_usr_id,SYSTEM_ROLE_ID))
		 {
			 return true;
		 }
		 return false;
	 }

	/**
	 * Save settings
	 *
	 *
	 */
	public function save()
	{
		$this->settings->set('https',(int) $this->isHTTPSEnabled());
		
		$this->settings->set('ps_password_chars_and_numbers_enabled',(bool) $this->isPasswordCharsAndNumbersEnabled());
		$this->settings->set('ps_password_special_chars_enabled',(bool) $this->isPasswordSpecialCharsEnabled());
		$this->settings->set('ps_password_min_length',(int) $this->getPasswordMinLength());
		$this->settings->set('ps_password_max_length',(int) $this->getPasswordMaxLength());
		$this->settings->set('ps_password_max_age',(int) $this->getPasswordMaxAge());
		$this->settings->set('ps_login_max_attempts',(int) $this->getLoginMaxAttempts());
		$this->settings->set('ps_password_uppercase_chars_num', (int) $this->getPasswordNumberOfUppercaseChars());
		$this->settings->set('ps_password_lowercase_chars_num', (int) $this->getPasswordNumberOfLowercaseChars());
		$this->settings->set('ps_password_must_not_contain_loginame', (int) $this->getPasswordMustNotContainLoginnameStatus());

		$this->settings->set('ps_password_change_on_first_login_enabled',(bool) $this->isPasswordChangeOnFirstLoginEnabled());
		$this->settings->set('ps_prevent_simultaneous_logins', (int)$this->isPreventionOfSimultaneousLoginsEnabled());
		$this->settings->set('ps_protect_admin', (int) $this->isAdminRoleProtected());
	}
	/**
	 * read settings
	 *
	 * @access private
	 * @param
	 *
	 */
	private function read()
	{
		global $ilDB;

	    $query = "SELECT object_reference.ref_id FROM object_reference,tree,object_data ".
				"WHERE tree.parent = ".$ilDB->quote(SYSTEM_FOLDER_ID,'integer')." ".
				"AND object_data.type = 'ps' ".
				"AND object_reference.ref_id = tree.child ".
				"AND object_reference.obj_id = object_data.obj_id";
		$res = $this->db->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$this->ref_id = $row["ref_id"];

		$this->https_enable = (boolean) $this->settings->get('https', false);

		$this->password_chars_and_numbers_enabled = (bool) $this->settings->get('ps_password_chars_and_numbers_enabled', self::DEFAULT_PASSWORD_CHARS_AND_NUMBERS_ENABLED);
		$this->password_special_chars_enabled = (bool) $this->settings->get('ps_password_special_chars_enabled', self::DEFAULT_PASSWORD_SPECIAL_CHARS_ENABLED);
		$this->password_min_length = (int) $this->settings->get('ps_password_min_length', self::DEFAULT_PASSWORD_MIN_LENGTH);
		$this->password_max_length = (int)  $this->settings->get('ps_password_max_length', self::DEFAULT_PASSWORD_MAX_LENGTH);
		$this->password_max_age = (int) $this->settings->get('ps_password_max_age', self::DEFAULT_PASSWORD_MAX_AGE);
		$this->login_max_attempts = (int) $this->settings->get('ps_login_max_attempts', self::DEFAULT_LOGIN_MAX_ATTEMPTS);
		$this->password_ucase_chars_num = (int) $this->settings->get('ps_password_uppercase_chars_num', 0);
		$this->password_lcase_chars_num = (int) $this->settings->get('ps_password_lowercase_chars_num', 0);
		$this->password_must_not_contain_loginname = $this->settings->get('ps_password_must_not_contain_loginame', 0) == '1' ? true : false;

		$this->password_change_on_first_login_enabled = (bool) $this->settings->get('ps_password_change_on_first_login_enabled', self::DEFAULT_PASSWORD_CHANGE_ON_FIRST_LOGIN_ENABLED);
		$this->prevent_simultaneous_logins = (bool) $this->settings->get('ps_prevent_simultaneous_logins', self::DEFAULT_PREVENT_SIMULTANEOUS_LOGINS);
		
		$this->protect_admin_role = (bool) $this->settings->get('ps_protect_admin',$this->protect_admin_role);
	}

	/**
	 * validate settings
	 *
	 * @return 0, if everything is ok, an error code otherwise
	 */
	public function validate(ilPropertyFormGUI $a_form = null)
	{		
		$code = null;
		
		if ($a_form)
		{
			include_once "Services/PrivacySecurity/classes/class.ilObjPrivacySecurityGUI.php";		
		}

		include_once './Services/Http/classes/class.ilHTTPS.php';

		if ($this->isHTTPSEnabled())
		{
			if(!ilHTTPS::_checkHTTPS())
			{
				$code = ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_HTTPS_NOT_AVAILABLE;
				if(!$a_form)
				{
					return $code;
				}
				else
				{
					$a_form->getItemByPostVar('https_enabled')
						   ->setAlert(ilObjPrivacySecurityGUI::getErrorMessage($code));
				}
			}
		}
		
		if( $this->getPasswordMinLength() < 0 )
		{
			$code = self::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MIN_LENGTH;
			if(!$a_form)
			{
				return $code;
			}
			else
			{		
				$a_form->getItemByPostVar('password_min_length')
						->setAlert(ilObjPrivacySecurityGUI::getErrorMessage($code));
			}
		}

		if( $this->getPasswordMaxLength() < 0 )
		{
			$code = self::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_LENGTH;
			if(!$a_form)
			{
				return $code;
			}
			else
			{		
				$a_form->getItemByPostVar('password_max_length')
						->setAlert(ilObjPrivacySecurityGUI::getErrorMessage($code));
			}
		}

		$password_min_length = 1;

		if($this->getPasswordNumberOfUppercaseChars() > 0 || $this->getPasswordNumberOfLowercaseChars() > 0)
		{
			$password_min_length = 0;
			if($this->getPasswordNumberOfUppercaseChars() > 0)
			{
				$password_min_length += $this->getPasswordNumberOfUppercaseChars();
			}
			if($this->getPasswordNumberOfLowercaseChars() > 0)
			{
				$password_min_length += $this->getPasswordNumberOfLowercaseChars();
			}
			$password_min_length_error_code = self::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN1;
		}

		if( $this->isPasswordCharsAndNumbersEnabled() )
		{
			$password_min_length++;
			$password_min_length_error_code = self::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN2;

			if( $this->isPasswordSpecialCharsEnabled() )
			{
				$password_min_length++;
				$password_min_length_error_code = self::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN3;
			}
		}
		else if($password_min_length > 1 && $this->isPasswordSpecialCharsEnabled())
		{
			$password_min_length++;
			$password_min_length_error_code = self::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN3;
		}

		if( $this->getPasswordMinLength() > 0 && $this->getPasswordMinLength() < $password_min_length )
		{				
			$code = $password_min_length_error_code;
			if(!$a_form)
			{
				return $code;
			}
			else
			{	
				$a_form->getItemByPostVar('password_min_length')
						->setAlert(sprintf(ilObjPrivacySecurityGUI::getErrorMessage($code), $password_min_length));
			}
		}
		if( $this->getPasswordMaxLength() > 0 && $this->getPasswordMaxLength() < $this->getPasswordMinLength() )
		{
			$code = self::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MAX_LENGTH_LESS_MIN_LENGTH;
			if(!$a_form)
			{
				return $code;
			}
			else
			{	
				$a_form->getItemByPostVar('password_max_length')
						->setAlert(ilObjPrivacySecurityGUI::getErrorMessage($code));
			}
		}

		if( $this->getPasswordMaxAge() < 0 )
		{
			$code = self::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_AGE;
			if(!$a_form)
			{
				return $code;
			}
			else
			{	
				$a_form->getItemByPostVar('password_max_age')
						->setAlert(ilObjPrivacySecurityGUI::getErrorMessage($code));
			}
		}

		if( $this->getLoginMaxAttempts() < 0 )
		{
			$code = self::SECURITY_SETTINGS_ERR_CODE_INVALID_LOGIN_MAX_ATTEMPTS;
			if(!$a_form)
			{
				return $code;
			}
			else
			{	
				$a_form->getItemByPostVar('login_max_attempts')
						->setAlert(ilObjPrivacySecurityGUI::getErrorMessage($code));
			}
		}		

		/*
		 * todo: have to check for local auth if first login password change is enabled??
		 * than: add errorcode
		 */

		if(!$a_form)
		{
			return 0;
		}
		else
		{
			return !(bool)$code;
		}
	}

	/**
	 * Prevention of simultaneous logins with the same account
	 *
	 * @return boolean  true, if prevention of simultaneous logins with the same account is enabled, false otherwise
	 */
    public function isPreventionOfSimultaneousLoginsEnabled()
    {
    	return (bool)$this->prevent_simultaneous_logins;
    }
    
	/**
	 * Enable/Disable prevention of simultaneous logins with the same account
	 *
	 * @param boolean $value
	 */
    public function setPreventionOfSimultaneousLogins($value)
    {
    	$this->prevent_simultaneous_logins = (bool)$value;
    }

	/**
	 * Set number of uppercase characters required
	 * @param    integer
	 */
	public function setPasswordNumberOfUppercaseChars($password_ucase_chars_num)
	{
		$this->password_ucase_chars_num = $password_ucase_chars_num;
	}

	/**
	 * Returns number of uppercase characters required
	 * @return    integer
	 */
	public function getPasswordNumberOfUppercaseChars()
	{
		return $this->password_ucase_chars_num;
	}

	/**
	 * Set number of lowercase characters required
	 * @param    integer
	 */
	public function setPasswordNumberOfLowercaseChars($password_lcase_chars_num)
	{
		$this->password_lcase_chars_num = $password_lcase_chars_num;
	}

	/**
	 * Returns number of lowercase characters required
	 * @return    integer
	 */
	public function getPasswordNumberOfLowercaseChars()
	{
		return $this->password_lcase_chars_num;
	}

	/**
	 * Set whether the password must not contain the loginname or not
	 * @param	boolean
	 */
	public function setPasswordMustNotContainLoginnameStatus($status)
	{
		$this->password_must_not_contain_loginname = $status;
	}

	/**
	 * Return whether the password must not contain the loginname or not
	 * @param	boolean
	 */
	public function getPasswordMustNotContainLoginnameStatus()
	{
		return $this->password_must_not_contain_loginname;
	}
}
?>
