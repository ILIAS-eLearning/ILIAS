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


/**
* soap server
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package ilias
*/
include_once 'Auth/Auth.php';

class ilSoapAuthentication
{
	/*
	 * session id
	 * @var string
	 */
	var $sid = '';

	/*
	 * username
	 * @var string
	 */
	var $username = '';

	/*
	 * password
	 * @var string
	 */
	var $password = '';


	/*
	 * client id
	 * @var string
	 */
	var $client = '';

	function ilSoapAuthentication()
	{
		$this->__setMessage('');
		$this->__setMessageCode('Client');
	}


	// Set/Get
	function setClient($a_client)
	{
		$this->client = $a_client;
		$_COOKIE['ilClientId'] = $a_client;
	}
	function getClient()
	{
		return $this->client;
	}
	function setUsername($a_username)
	{
		$this->username = $a_username;
		$_POST['username'] = $a_username;
	}
	function getUsername()
	{
		return $this->username;
	}
	function setPassword($a_password)
	{
		$this->password = $a_password;
		$_POST['password'] = $a_password;
	}
	function getPassword()
	{
		return $this->password;
	}
	function setSid($a_sid)
	{
		$this->sid = $a_sid;
		$_COOKIE['PHPSESSID'] = $this->sid;
	}
	function getSid()
	{
		return $this->sid;
	}

	function getMessage()
	{
		return $this->message;
	}
	function getMessageCode()
	{
		return $this->message_code;
	}
	function __setMessage($a_message)
	{
		$this->message = $a_message;
	}
	function __setMessageCode($a_message_code)
	{
		$this->message_code = $a_message_code;
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
		if(!$this->__buildAuth())
		{
			return false;
		}
		if(!$this->__setSessionSaveHandler())
		{
			return false;
		}
		if(!$this->__checkSOAPEnabled())
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

	function start()
	{
		if(!$this->getSid())
		{
			$this->__setMessage('No session id given');
			return false;
		}

		$this->auth->start();

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
		
		if(!$this->__buildAuth())
		{
			return false;
		}
		if(!$this->__setSessionSaveHandler())
		{
			return false;
		}
		if(!$this->__checkSOAPEnabled())
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

	function logout()
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
		if(!$this->__buildAuth())
		{
			return false;
		}
		if(!$this->__setSessionSaveHandler())
		{
			return false;
		}
		
		// And finally logout
		$this->auth->start();
		$this->auth->logout();
		session_destroy();

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


	function __buildAuth()
	{
		include_once './classes/class.ilIniFile.php';

		// get ilias ini file
		$this->ilias_ini =& new ilIniFile('./ilias.ini.php');
		$this->ilias_ini->read();

		if(!@file_exists("./".$this->ilias_ini->readVariable('clients','path')."/".$this->getClient()."/client.ini.php"))
		{
			$this->__setMessageCode('Client');
			$this->__setMessage('Client does not exist');

			return false;
		}
		
		$this->ini =& new ilIniFile("./".$this->ilias_ini->readVariable('clients','path')."/".$this->getClient()."/client.ini.php");
		$this->ini->read();
		
		$this->dsn = $this->ini->readVariable("db","type").
					 "://".$this->ini->readVariable("db", "user").
					 ":".$this->ini->readVariable("db", "pass").
					 "@".$this->ini->readVariable("db", "host").
					 "/".$this->ini->readVariable("db", "name");


		$this->auth_params = array(
			'dsn'		  => $this->dsn,
			'table'       => $this->ini->readVariable("auth", "table"),
			'usernamecol' => $this->ini->readVariable("auth", "usercol"),
			'passwordcol' => $this->ini->readVariable("auth", "passcol")
			);

		$this->auth = new Auth("DB", $this->auth_params,"",false);

		return true;
	}

	function __setSessionSaveHandler()
	{
		include_once './include/inc.db_session_handler.php';
		include_once './classes/class.ilErrorHandling.php';
		include_once './classes/class.ilDBx.php';

		
		$GLOBALS['ilDB'] =& new ilDBx($this->dsn);

		if(ini_get('session.save_handler') != 'user')
		{
			ini_set("session.save_handler", "user");
		}
		if(!db_set_save_handler())
		{
			$this->__setMessageCode('Server');
			$this->__setMessage('Cannot set session handler');

			return false;
		}

		return true;
	}

	function __getAuthStatus()
	{
		switch($this->auth->getStatus())
		{
			case AUTH_EXPIRED:
				$this->__setMessageCode('Server');
				$this->__setMessage('Session expired');

				return false;

			case AUTH_IDLED:
				$this->__setMessageCode('Server');
				$this->__setMessage('Session idled');
				
				return false;
				
			case AUTH_WRONG_LOGIN:
			default:
				$this->__setMessageCode('Client');
				$this->__setMessage('Session idled');

				return false;
				
				
		}
	}
}
?>
