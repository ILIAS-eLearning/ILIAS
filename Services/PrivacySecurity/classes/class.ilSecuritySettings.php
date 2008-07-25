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
	const SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN2				= 8;
	const SECURITY_SETTINGS_ERR_CODE_PASSWORD_MIN_LENGTH_MIN3				= 9;
	const SECURITY_SETTINGS_ERR_CODE_PASSWORD_MAX_LENGTH_LESS_MIN_LENGTH	= 10;


    const ACCOUNT_SECURITY_MODE_DEFAULT = 1;
    const ACCOUNT_SECURITY_MODE_CUSTOMIZED = 2;


    private static $instance = null;
	private $db;
	private $settings;

	private $https_header_enable;
	private $https_header_name;
	private $https_header_value;
	private $https_enable;

	private $account_security_mode				= self::ACCOUNT_SECURITY_MODE_DEFAULT;
	private $password_chars_and_numbers_enabled	= false;
	private $password_special_chars_enabled		= false;
	private $password_min_length				= 0;
	private $password_max_length				= 0;
	private $password_max_age					= 0;
	private $login_max_attempts					= 0;

	private $password_change_on_first_login_enabled = false;

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
	 * set the account security mode
	 *
	 * @param integer $a_mode
	 *
	 */
	 public function setAccountSecurityMode($a_mode)
	 {
	 	$this->account_security_mode = $a_mode;
	 }

	/**
	 * get the account security mode
	 *
	 * @return integer	account security mode
	 *
	 */
	 public function getAccountSecurityMode()
	 {
	 	return $this->account_security_mode;
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
	 * write access to enable automatic https detection
	 *
	 * @param boolean $varname
	 *
	 */
	public function setAutomaticHTTPSEnabled($varname)
	{
	    $this->https_header_enable = $varname;
	}

	/**
	 * set header name for automatic https detection
	 *
	 * @param string $varname
	 */
	public function setAutomaticHTTPSHeaderName($varname)
	{
	    $this->https_header_name = $varname;
	}

	/**
	 * set header value for automatic https detection
	 *
	 * @param string $varname
	 */
	public function setAutomaticHTTPSHeaderValue($varname)
	{
	    $this->https_header_value = $varname;
	}

	/**
	 * read access to header name for automatic https detection
	 *
	 * @return string  header name
	 */
	public function getAutomaticHTTPSHeaderName()
	{
	    return $this->https_header_name;
	}

	/**
	 * read access to header value for automatic https detection
	 *
	 * @return string header value
	 */
	public function getAutomaticHTTPSHeaderValue()
	{
	    return $this->https_header_value;
	}

    /**
     * read access to switch if automatic https detection is enabled
     *
     * @return boolean  true, if detection is enabled, false otherwise
     */
	public function isAutomaticHTTPSEnabled()
	{
	    return $this->https_header_enable;
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
	 * Save settings
	 *
	 *
	 */
	public function save()
	{
	 	$this->settings->set('ps_auto_https_enabled',(bool) $this->isAutomaticHTTPSEnabled());
	 	$this->settings->set('ps_auto_https_headername',(string) $this->getAutomaticHTTPSHeaderName());
	 	$this->settings->set('ps_auto_https_headervalue',(string) $this->getAutomaticHTTPSHeaderValue());
	 	$this->settings->set('https',(string) $this->isHTTPSEnabled());

		$this->settings->set('ps_account_security_mode',(int) $this->getAccountSecurityMode());
		$this->settings->set('ps_password_chars_and_numbers_enabled',(bool) $this->isPasswordCharsAndNumbersEnabled());
		$this->settings->set('ps_password_special_chars_enabled',(bool) $this->isPasswordSpecialCharsEnabled());
		$this->settings->set('ps_password_min_length',(int) $this->getPasswordMinLength());
		$this->settings->set('ps_password_max_length',(int) $this->getPasswordMaxLength());
		$this->settings->set('ps_password_max_age',(int) $this->getPasswordMaxAge());
		$this->settings->set('ps_login_max_attempts',(int) $this->getLoginMaxAttempts());

		$this->settings->set('ps_password_change_on_first_login_enabled',(bool) $this->isPasswordChangeOnFirstLoginEnabled());
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
				"WHERE tree.parent = ".$ilDB->quote(SYSTEM_FOLDER_ID)." ".
				"AND object_data.type = 'ps' ".
				"AND object_reference.ref_id = tree.child ".
				"AND object_reference.obj_id = object_data.obj_id";
		$res = $this->db->query($query);
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$this->ref_id = $row["ref_id"];

    	$this->https_header_enable = (bool) $this->settings->get('ps_auto_https_enabled',false);
		$this->https_header_name = (string) $this->settings->get('ps_auto_https_headername',"ILIAS_HTTPS_ENABLED");
		$this->https_header_value = (string) $this->settings->get('ps_auto_https_headervalue',"1");
		$this->https_enable = (boolean) $this->settings->get('https', false);

		$this->account_security_mode = (int) $this->settings->get('ps_account_security_mode',0);
		$this->password_chars_and_numbers_enabled = (bool) $this->settings->get('ps_password_chars_and_numbers_enabled',false);
		$this->password_special_chars_enabled = (bool) $this->settings->get('ps_password_special_chars_enabled',false);
		$this->password_min_length = (int) $this->settings->get('ps_password_min_length',0);
		$this->password_max_length = (int)  $this->settings->get('ps_password_max_length',0);
		$this->password_max_age = (int) $this->settings->get('ps_password_max_age',0);
		$this->login_max_attempts = (int) $this->settings->get('ps_login_max_attempts',0);

		$this->password_change_on_first_login_enabled = (bool) $this->settings->get('ps_password_change_on_first_login_enabled',false);
	}

	/**
	 * validate settings
	 *
	 * @return 0, if everything is ok, an error code otherwise
	 */
	public function validate()
	{
	    if ($this->isAutomaticHTTPSEnabled() &&
	        (strlen($this->getAutomaticHTTPSHeaderName()) == 0 ||
	         strlen($this->getAutomaticHTTPSHeaderValue()) == 0)
	        )
        {
	        return ilSecuritySettings::SECURITY_SETTINGS_ERR_CODE_AUTO_HTTPS;
	    }
        include_once './classes/class.ilHTTPS.php';

	    if ($this->isHTTPSEnabled())
	    {
			if(!ilHTTPS::_checkHTTPS())
			{
				return ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_HTTPS_NOT_AVAILABLE;
			}
	    } elseif(!ilHTTPS::_checkHTTP())
			{
			    return ilSecuritySettings::$SECURITY_SETTINGS_ERR_CODE_HTTP_NOT_AVAILABLE;
			}

		if( $this->getAccountSecurityMode() == self::ACCOUNT_SECURITY_MODE_CUSTOMIZED )
		{
			if( $this->getPasswordMinLength() < 0 )
			{
				return self::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MIN_LENGTH;
			}

			if( $this->getPasswordMaxLength() < 0 )
			{
				return self::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_LENGTH;
			}

			$password_min_length = 1;
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
			if( $this->getPasswordMinLength() > 0 && $this->getPasswordMinLength() < $password_min_length )
			{
				return $password_min_length_error_code;
			}
			if( $this->getPasswordMaxLength() > 0 && $this->getPasswordMaxLength() < $this->getPasswordMinLength() )
			{
				return self::SECURITY_SETTINGS_ERR_CODE_PASSWORD_MAX_LENGTH_LESS_MIN_LENGTH;
			}

			if( $this->getPasswordMaxAge() < 0 )
			{
				return self::SECURITY_SETTINGS_ERR_CODE_INVALID_PASSWORD_MAX_AGE;
			}

			if( $this->getLoginMaxAttempts() < 0 )
			{
				return self::SECURITY_SETTINGS_ERR_CODE_INVALID_LOGIN_MAX_ATTEMPTS;
			}
		}

		/*
		 * todo: have to check for local auth if first login password change is enabled??
		 * than: add errorcode
		 */

	    return 0;
	}


}
?>
