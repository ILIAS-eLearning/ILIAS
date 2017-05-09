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

include_once 'Auth/Container/MDB2.php';

/** 
* Authentication against ILIAS database
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
*
* @ingroup ServicesDatabase
*/
class ilAuthContainerMDB2 extends Auth_Container_MDB2
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $ilClientIniFile, $ilDB, $ilIliasIniFile;

		$options['dsn']			= $ilDB->getDSN();
		$options['table']		= $ilClientIniFile->readVariable('auth', 'table');
		$options['usernamecol']	= $ilClientIniFile->readVariable('auth', 'usercol');
		$options['passwordcol']	= $ilClientIniFile->readVariable('auth', 'passcol');

		// studip mode: check against submitted md5 password for ilSoapUserAdministration::login()
		// todo: check whether we should put this to another place
		if (isset($_POST['password']) && preg_match('/^[a-f0-9]{32,32}$/i', $_POST['password']))
		{
			if ($ilIliasIniFile->readVariable('server', 'studip'))
			{
				$options['cryptType'] = 'none';
			}
		}

		parent::__construct($options);
	}
	
	
	/**
	 * Static function removes Microsoft domain name from username
	 */
	public static function toUsernameWithoutDomain($username)
	{
		// Remove all characters including the last slash or the last backslash
		// in the username
		$pos = strrpos($username, '/');
		$pos2 = strrpos($username, '\\');
		if ($pos === false || $pos < $pos2) 
		{
			$pos = $pos2;
		}
		if ($pos !== false)
		{
			$username = substr($username, $pos + 1);
		}
		return $username;
	}
	
	
	/** 
	 * Called from fetchData after successful login.
	 *
	 * @param string username
	 */
	public function loginObserver($a_username,$a_auth)
	{
		$usr_id = ilObjUser::_lookupId($a_username);
		$auth_mode = ilObjUser::_lookupAuthMode($usr_id);
		$auth_id = ilAuthUtils::_getAuthMode($auth_mode);

		$GLOBALS['ilLog']->write(__METHOD__.': auth id =  ' . $auth_id);
		
		switch($auth_id)
		{
			case AUTH_APACHE:
			case AUTH_LOCAL:
				return true;
				
			default:
				if(ilAuthUtils::isPasswordModificationEnabled($auth_id))
				{
					return true;
				}
		}
		

		$a_auth->status = AUTH_WRONG_LOGIN;
		$a_auth->logout();
		
		return false;
	}

	/**
	 * @return bool
	 */
	public function supportsCaptchaVerification()
	{
		return true;
	}

	/**
	 * @param    string $raw
	 * @param    string $encoded
	 * @param           string    string    $cryptType
	 * @return    bool
	 */
	public function verifyPassword($raw, $encoded, $crypt_type = 'md5')
	{
		$this->log(__METHOD__ . ' called.', AUTH_LOG_DEBUG);

		if(in_array($crypt_type, array('none', '')))
		{
			return parent::verifyPassword($raw, $encoded, $crypt_type);
		}

		require_once 'Services/User/classes/class.ilUserPasswordManager.php';
		$crypt_type = ilUserPasswordManager::getInstance()->getEncoderName();

		if(ilUserPasswordManager::getInstance()->isEncodingTypeSupported($crypt_type))
		{
			/**
			 * @var $user ilObjUser
			 */
			$user = ilObjectFactory::getInstanceByObjId(ilObjUser::_loginExists($this->_auth_obj->username));
			$user->setPasswd($encoded, IL_PASSWD_CRYPTED);

			return ilUserPasswordManager::getInstance()->verifyPassword($user, $raw);
		}

		// Fall through: Let pear verify the password
		return parent::verifyPassword($raw, $encoded, $crypt_type);
	}
}