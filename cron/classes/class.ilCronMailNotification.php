<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
*
* @author Michael Jansen <mjansen@databay.de>
* @version $Id:
* @package ilias
*/
class ilCronMailNotification
{	
	public function sendNotifications()
	{
		global $ilias, $ilDB, $ilLog;
		
		include_once 'Services/Mail/classes/class.ilMail.php';
		include_once 'Services/User/classes/class.ilObjUser.php';
		include_once 'Services/Language/classes/class.ilLanguage.php';
		include_once 'Services/Mail/classes/class.ilMimeMail.php';

		$res = $ilDB->queryF('SELECT mail.* FROM mail_options
						INNER JOIN mail ON mail.user_id = mail_options.user_id 
						INNER JOIN mail_obj_data ON mail_obj_data.obj_id = mail.folder_id
						WHERE cronjob_notification = %s 
						AND send_time >= %s
						AND mail_obj_data.m_type = %s
						AND m_status = %s',
			array('integer', 'timestamp', 'text', 'text'),
			array(1, date('Y-m-d H:i:s', time() - 60 * 60 * 24), 'inbox', 'unread')
		);
				
		$users = array();
		
		$user_id = 0;
		while($row = $ilDB->fetchAssoc($res))
		{
			if($user_id == 0 || $row['user_id'] != $user_id) $user_id = $row['user_id'];
			$users[$user_id][] = $row;				 
		}
		
		$numRows = 0;
		foreach($users as $user_id => $mail_data)
		{
			$tmp_mail_obj = new ilMail($user_id);		

			$mmail = new ilMimeMail();
			$mmail->autoCheck(false);
			$mmail->From('noreply');
			$mmail->To(ilObjUser::_lookupEmail($user_id));					
			$mmail->Subject($tmp_mail_obj->formatNotificationSubject());
			$mmail->Body($tmp_mail_obj->formatNotificationMessage($user_id, $mail_data));			
			$mmail->Send();
			
			unset($tmp_mail_obj);
			
			++$numRows;
		}
		
		$ilLog->write(__METHOD__.': Send '.$numRows.' messages.');	

		return true;
	}
}
?>
