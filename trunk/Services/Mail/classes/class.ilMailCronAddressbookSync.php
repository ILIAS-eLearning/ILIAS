<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Cron/classes/class.ilCronJob.php";

/**
 * Address book sync
 *
 * @author Nadia Ahmad <nahmad@databay.de>
 */
class ilMailCronAddressbookSync extends ilCronJob
{
	public function getId()
	{
		return "mail_address_sync";
	}
	
	public function getTitle()
	{
		global $lng;
				
		$lng->loadLanguageModule("mail");
		return $lng->txt("cron_update_addressbook");
	}
	
	public function getDescription()
	{
		global $lng;
				
		$lng->loadLanguageModule("mail");
		return $lng->txt("cron_update_addressbook_desc");
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
	
	public function hasCustomSettings() 
	{
		return false;
	}

	public function run()
	{	
		global $ilDB;

		if($ilDB->getDBType() == 'oracle')
		{
			$res1 = $ilDB->queryF('
				SELECT addressbook.addr_id, 
					   usr_data.firstname,
					   usr_data.lastname, 
					   (CASE WHEN epref.value = %s THEN usr_data.email ELSE addressbook.email END) email
				FROM addressbook
				INNER JOIN usr_data ON usr_data.login = addressbook.login
				INNER JOIN usr_pref ppref ON ppref.usr_id = usr_data.usr_id AND ppref.keyword = %s AND ppref.value != %s
				LEFT JOIN usr_pref epref ON epref.usr_id = usr_data.usr_id AND epref.keyword = %s
				WHERE addressbook.auto_update = %s',
				array('text', 'text', 'text', 'text', 'integer'),
				array('y', 'public_profile', 'n', 'public_email', 1)
			);

			$stmt = $ilDB->prepare('
				UPDATE addressbook 
				SET firstname = ?,
					lastname = ?,
					email = ?
				WHERE addr_id = ?',
				array('text','text','text', 'integer')
			);

			while($row = $ilDB->fetchAssoc($res1))
			{
				$ilDB->execute($stmt, array($row['firstname'], $row['lastname'], $row['email'], $row['addr_id']));
			}
		}
		else
		{
			$ilDB->queryF('
				UPDATE addressbook
				INNER JOIN usr_data ON usr_data.login = addressbook.login
				INNER JOIN usr_pref ppref ON ppref.usr_id = usr_data.usr_id AND ppref.keyword = %s AND ppref.value != %s
				LEFT JOIN usr_pref epref ON epref.usr_id = usr_data.usr_id AND epref.keyword = %s
				SET
				addressbook.firstname = usr_data.firstname,
				addressbook.lastname = usr_data.lastname,
				addressbook.email =  (CASE WHEN epref.value = %s THEN usr_data.email ELSE addressbook.email  END)
				WHERE addressbook.auto_update = %s',
				array('text', 'text', 'text', 'text', 'integer'),
				array('public_profile', 'n', 'public_email', 'y', 1)
			);
		}
		
		$result = new ilCronJobResult();
		$result->setStatus(ilCronJobResult::STATUS_OK);		
		return $result;
	}

	public function activationWasToggled($a_currently_active)
	{		
		global $ilSetting;
		
		// propagate cron-job setting to object setting
		$ilSetting->set('cron_upd_adrbook', (bool)$a_currently_active);
	}
}

?>