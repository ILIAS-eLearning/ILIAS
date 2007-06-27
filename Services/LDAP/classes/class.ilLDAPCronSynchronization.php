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
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* @ingroup ServicesLDAP  
*/

include_once('Services/LDAP/classes/class.ilLDAPServer.php');
include_once('Services/LDAP/classes/class.ilLDAPQuery.php');
include_once('Services/LDAP/classes/class.ilLDAPAttributeToUser.php');


class ilLDAPCronSynchronization
{
	private $current_server = null;
	private $ldap_query = null;
	private $log = null;
	
	public function __construct()
	{
		global $ilLog;
		
		$this->log = $ilLog;
	}
	
	/**
	 * Check for LDAP servers and synchronize them
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function start()
	{
	 	foreach(ilLDAPServer::_getCronServerIds() as $server_id)
	 	{
			try
			{
		 		$this->current_server = new ilLDAPServer($server_id);
		 		$this->log->write("LDAP: starting user synchronization for ".$this->current_server->getName());
		 		
		 		$this->ldap_query = new ilLDAPQuery($this->current_server);
		 		$this->ldap_query->bind(IL_LDAP_BIND_DEFAULT);
		 		
		 		if(is_array($users = $this->ldap_query->fetchUsers()))
		 		{
			 		// Deactivate ldap users that are not in the list
			 		$this->deactivateUsers($users);
		 		}
			
		 		if(count($users))
		 		{	
			 		$this->log->write("LDAP: Starting update/creation of users ...");
			 		$this->ldap_to_ilias = new ilLDAPAttributeToUser($this->current_server);
			 		$this->ldap_to_ilias->setUserData($users);
			 		$this->ldap_to_ilias->refresh();
			 		$this->log->write("LDAP: Finished update/creation");
		 		}
				else
				{
			 		$this->log->write("LDAP: No users for update/create. Aborting.");
				}
			}
			catch(ilLDAPQueryException $exc)
			{
				$this->log->write($exc->getMessage());
			}
	 	}
	}
	
	/**
	 * Deactivate users that are disabled in LDAP
	 *
	 * @access private
	 * 
	 */
	private function deactivateUsers($a_ldap_users)
	{
	 	include_once './classes/class.ilObjUser.php';
	 	
	 	foreach($ext = ilObjUser::_getExternalAccountsByAuthMode('ldap',true) as $usr_id => $external_account)
	 	{
	 		if(!array_key_exists($external_account,$a_ldap_users))
	 		{
	 			$inactive[] = $usr_id;
	 		}
	 	}
	 	if(count($inactive))
	 	{
	 		ilObjUser::_toggleActiveStatusOfUsers($inactive,false);
	 		$this->log->write('LDAP: Found '.count($inactive).' inactive users.');
	 	}
		else
		{
			$this->log->write('LDAP: No inactive users found');
		}
	}
}


?>