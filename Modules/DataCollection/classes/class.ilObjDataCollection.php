<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Object/classes/class.ilObject2.php";

/**
* Class ilObjDataCollection
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjFolder.php 25528 2010-09-03 10:37:11Z smeyer $
*
* @extends ilObject2
*/
class ilObjDataCollection extends ilObject2
{	
	function initType()
	{
		$this->type = "dcl";
	}

	/*
	protected function doRead()
	{
		
	}

	protected function doCreate()
	{
		
	}
	
	protected function doDelete()
	{
		
	}
	
	protected function doUpdate()
	{
		
	}	 
	 
	static function sendNotification($a_action, $a_ref_id)
	{
		global $ilUser, $ilAccess;
		
		// recipients
		include_once "./Services/Notification/classes/class.ilNotification.php";		
		$users = ilNotification::getNotificationsForObject(ilNotification::TYPE_DATA_COLLECTION, 
			$a_ref_id);
		if(!sizeof($users))
		{
			return;
		}
		
		ilNotification::updateNotificationTime(ilNotification::TYPE_DATA_COLLECTION, $a_ref_id, $users);
		
		
		// prepare mail content
		
		...
	 
	  	
		// send mails
		
		include_once "./Services/Mail/classes/class.ilMail.php";
		include_once "./Services/User/classes/class.ilObjUser.php";
		include_once "./Services/Language/classes/class.ilLanguageFactory.php";
		include_once("./Services/User/classes/class.ilUserUtil.php");
				
		foreach(array_unique($users) as $idx => $user_id)
		{			
			// the user responsible for the action should not be notified
			if($user_id != $ilUser->getId() &&
				$ilAccess->checkAccessOfUser($user_id, 'read', '', $a_ref_id))
			{
				// use language of recipient to compose message
				$ulng = ilLanguageFactory::_getLanguageOfUser($user_id);
				$ulng->loadLanguageModule('dcl');

				$subject = "...";
				$message = "...";

				$mail_obj = new ilMail(ANONYMOUS_USER_ID);
				$mail_obj->appendInstallationSignature(true);
				$mail_obj->sendMail(ilObjUser::_lookupLogin($user_id),
					"", "", $subject, $message, array(), array("system"));
			}
			else
			{
				unset($users[$idx]);
			}
		}
	}
	*/
}

?>