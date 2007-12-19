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
* soap server
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/
include_once 'Auth/Auth.php';
include_once './Services/Authentication/classes/class.ilBaseAuthentication.php';

class ilSoapAuthentication extends ilBaseAuthentication
{
	var $soap_check = true;


	function ilSoapAuthentication()
	{
		// First unset all cookie inforamtions
		unset($_COOKIE['PHPSESSID']);

		parent::ilBaseAuthentication();
		$this->__setMessageCode('Client');
	}

	function disableSoapCheck()
	{
		$this->soap_check = false;
	}

	function authenticate()
	{
		if(!$this->getClient())
		{
			$this->__setMessage('No client given');
			return false;
		}
		if(!$this->getUsername())
		{
			$this->__setMessage('No username given');
			return false;
		}
		// Read ilias ini
		if(!$this->__buildDSN())
		{
			$this->__setMessage('Error building dsn/Wrong client Id?');
			return false;
		}
		if(!$this->__setSessionSaveHandler())
		{
			return false;
		}
		if(!$this->__checkAgreement('local'))
		{
			return false;
		}
		if(!$this->__buildAuth())
		{
			return false;
		}
		if($this->soap_check and !$this->__checkSOAPEnabled())
		{
			$this->__setMessage('SOAP is not enabled in ILIAS administration for this client');
			$this->__setMessageCode('Server');

			return false;
		}


		$this->auth->start();

		if(!$this->auth->getAuth())
		{
			$this->__getAuthStatus();

			return false;
		}

		$this->setSid(session_id());

		return true;
	}
	
	/**
	 * Check if user agreement is accepted
	 *
	 * @access protected
	 * @param string auth_mode local,ldap or cas
	 * 
	 */
	protected function __checkAgreement($a_auth_mode)
	{
	 	global $ilDB;
	 	
		include_once('./Services/User/classes/class.ilObjUser.php');
		include_once('./Services/Administration/classes/class.ilSetting.php');
		
		$GLOBALS['ilSetting'] = new ilSetting();
		
		if(!$login = ilObjUser::_checkExternalAuthAccount($a_auth_mode,$this->getUsername()))
		{
			// User does not exist
			return true;
		}
		
		if(!ilObjUser::_hasAcceptedAgreement($login))
		{
			$this->__setMessage('User aggrement no accepted.');
			return false;
		}
		return true;
	}
	


	function validateSession()
	{
		if(!$this->getClient())
		{
			$this->__setMessage('No client given');
			return false;
		}
		if(!$this->getSid())
		{
			$this->__setMessage('No session id given');
			return false;
		}

		if(!$this->__buildDSN())
		{
			$this->__setMessage('Error building dsn');
			return false;
		}
		if(!$this->__checkClientEnabled())
		{
			$this->__setMessage('Client disabled.');
			return false;
		}
		
		if(!$this->__setSessionSaveHandler())
		{
			return false;
		}
		if(!$this->__buildAuth())
		{
			return false;
		}
		if($this->soap_check and !$this->__checkSOAPEnabled())
		{
			$this->__setMessage('SOAP is not enabled in ILIAS administration for this client');
			$this->__setMessageCode('Server');

			return false;
		}
		$this->auth->start();
		if(!$this->auth->getAuth())
		{
			$this->__setMessage('Session not valid');

			return false;
		}

		return true;
	}

	// PRIVATE
	function __checkSOAPEnabled()
	{
		include_once './classes/class.ilDBx.php';


		$db =& new ilDBx($this->dsn);

		$query = "SELECT * FROM settings WHERE keyword = 'soap_user_administration' AND value = 1";

		$res = $db->query($query);

		return $res->numRows() ? true : false;
	}
	
	function __checkClientEnabled()
	{
		if(is_object($this->ini) and $this->ini->readVariable('client','access'))
		{
			return true;
		}
		return false;
	}
}
?>
