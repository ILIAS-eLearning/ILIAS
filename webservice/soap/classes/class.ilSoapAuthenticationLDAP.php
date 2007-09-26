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
* this class authenticates via LDAP for a soap request
*
* @author Roland Küstermann <rkuestermann@mps.de>
* @version $Id: class.ilSoapAuthenticationCAS.php 11747 2006-08-02 10:31:57 +0200 (Mi, 02 Aug 2006) akill $
*
* @package ilias
*/

include_once './webservice/soap/classes/class.ilSoapAuthentication.php';

class ilSoapAuthenticationLDAP extends ilSOAPAuthentication
{
	function ilSoapAuthenticationLDAP()
	{
		parent::ilSOAPAuthentication();
	}



	function __buildAuth()
	{
		global $ilAuth;
		$_POST['auth_mode'] = AUTH_LDAP;
		
		include_once("./Services/Init/classes/class.ilInitialisation.php");
		$init = new ilInitialisation();
		$init->initILIAS("soap");		
		$this->auth = $ilAuth;
		return true;
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

		if(!$this->__buildAuth())
		{
			return false;
		}

		if(!$this->auth->getAuth())
		{
			$this->__getAuthStatus();

			return false;
		}

		$this->setSid(session_id());

		return true;
	}

}
?>