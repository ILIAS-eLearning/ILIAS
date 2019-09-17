<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";
include_once('Services/LDAP/classes/class.ilLDAPServer.php');
include_once('Services/LDAP/classes/class.ilLDAPQuery.php');
include_once('Services/LDAP/classes/class.ilLDAPAttributeToUser.php');

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* @ingroup ServicesLDAP  
*/
class ilLDAPCronSynchronization extends ilCronJob
{
	private $current_server = null;
	private $ldap_query = null;	
	private $ldap_to_ilias = null;	
	private $counter = 0;	
	
	public function getId()
	{
		return "ldap_sync";
	}
	
	public function getTitle()
	{
		global $DIC;

		$lng = $DIC['lng'];
		
		$lng->loadLanguageModule('ldap');
		return $lng->txt('ldap_user_sync_cron');
	}
	
	public function getDescription()
	{
		global $DIC;

		$lng = $DIC['lng'];
		
		$lng->loadLanguageModule("ldap");
		return $lng->txt("ldap_user_sync_cron_info");
	}
	
	public function getDefaultScheduleType()
	{
		return self::SCHEDULE_TYPE_DAILY;
	}
	
	public function getDefaultScheduleValue()
	{
		return;
	}
	
	public function hasAutoActivation()
	{
		return false;
	}
	
	public function hasFlexibleSchedule()
	{
		return false;
	}

	public function run()
	{				
		global $DIC;

		$ilLog = $DIC['ilLog'];
		
		$status = ilCronJobResult::STATUS_NO_ACTION;						
	
		$messages = array();
		foreach(ilLDAPServer::_getCronServerIds() as $server_id)
	 	{
			try
			{
		 		$this->current_server = new ilLDAPServer($server_id);
		 		$this->current_server->doConnectionCheck();
		 		$ilLog->write("LDAP: starting user synchronization for ".$this->current_server->getName());
		 		
		 		$this->ldap_query = new ilLDAPQuery($this->current_server);
		 		$this->ldap_query->bind(IL_LDAP_BIND_DEFAULT);
		 		
		 		if(is_array($users = $this->ldap_query->fetchUsers()))
		 		{										
			 		// Deactivate ldap users that are not in the list
			 		$this->deactivateUsers($this->current_server,$users);
		 		}
			
		 		if(count($users))
		 		{											
					include_once './Services/User/classes/class.ilUserCreationContext.php';
					ilUserCreationContext::getInstance()->addContext(ilUserCreationContext::CONTEXT_LDAP);

					$offset = 0;
					$limit = 500;
					while($user_sliced = array_slice($users, $offset, $limit, true))
					{
				 		$ilLog->write("LDAP: Starting update/creation of users ...");
						$ilLog->write("LDAP: Offset: " . $offset);
						$this->ldap_to_ilias = new ilLDAPAttributeToUser($this->current_server);
						$this->ldap_to_ilias->setNewUserAuthMode($this->current_server->getAuthenticationMappingKey());
						$this->ldap_to_ilias->setUserData($user_sliced);
						$this->ldap_to_ilias->refresh();
						$ilLog->write("LDAP: Finished update/creation");
						
						$offset += $limit;

						ilCronManager::ping($this->getId());
					}					
					$this->counter++;
		 		}
				else
				{
			 		$ilLog->write("LDAP: No users for update/create. Aborting.");
				}
			}
			catch(ilLDAPQueryException $exc)
			{
				$mess = $exc->getMessage();
				$ilLog->write($mess);
				
				$messages[] = $mess;
			}
	 	}
	
		if($this->counter)
		{
			$status = ilCronJobResult::STATUS_OK;
		}			
		$result = new ilCronJobResult();
		if(sizeof($messages))
		{
			$result->setMessage(implode("\n", $messages));
		}
		$result->setStatus($status);		
		return $result;
	}
	
	/**
	 * Deactivate users that are disabled in LDAP
	 */
	private function deactivateUsers(ilLDAPServer $server,$a_ldap_users)
	{
		global $DIC;

		$ilLog = $DIC['ilLog'];
		
	 	include_once './Services/User/classes/class.ilObjUser.php';
	 	
	 	$inactive = [];
	 	foreach($ext = ilObjUser::_getExternalAccountsByAuthMode($server->getAuthenticationMappingKey(),true) as $usr_id => $external_account)
	 	{
	 		if(!array_key_exists($external_account,$a_ldap_users))
	 		{
	 			$inactive[] = $usr_id;
	 		}
	 	}
	 	if(count($inactive))
	 	{
	 		ilObjUser::_toggleActiveStatusOfUsers($inactive,false);
	 		$ilLog->write('LDAP: Found '.count($inactive).' inactive users.');
			
			$this->counter++;
	 	}
		else
		{
			$ilLog->write('LDAP: No inactive users found');
		}
	}

	public function addToExternalSettingsForm($a_form_id, array &$a_fields, $a_is_active)
	{				
		global $DIC;

		$lng = $DIC['lng'];
		
		switch($a_form_id)
		{			
			case ilAdministrationSettingsFormHandler::FORM_LDAP:										
				$a_fields["ldap_user_sync_cron"] = $a_is_active ? 
					$lng->txt("enabled") :
					$lng->txt("disabled");
				break;
		}
	}
}

?>