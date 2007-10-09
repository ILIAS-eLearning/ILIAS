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
* 
*
* @author Michael Jansen <mjansen@databay.de>
*
* @package ilias
*/

class ilCronMailNotification
{
	function ilCronMailNotification()
	{
		global $ilLog,$ilDB;

		$this->log =& $ilLog;
		$this->db =& $ilDB;
	}

	function sendMails($res)
	{
		global $ilias, $rbacsystem;

	}

	function sendNotifications()
	{
		global $ilias;
		
		include_once "Services/Mail/classes/class.ilMail.php";
		include_once './Services/User/classes/class.ilObjUser.php';
		include_once "./classes/class.ilLanguage.php";

		$query = "SELECT mail.* " 
				 ."FROM mail_options "
				 ."INNER JOIN mail ON mail.user_id = mail_options.user_id "
				 ."INNER JOIN mail_obj_data ON mail_obj_data.obj_id = mail.folder_id "
				 ."WHERE 1 "
				 ."AND cronjob_notification = '1' "
				 ."AND send_time >= '" . date("Y-m-d H:i:s", time() - 60 * 60 * 24). "' "
				 ."AND mail_obj_data.type = 'inbox' "
				 ."AND m_status = 'unread' "
				 ." ";
				 		
		$res = $this->db->query($query);
		
		$users = array();
		
		$user_id = 0;
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($user_id == 0 || $row['user_id'] != $user_id) $user_id = $row['user_id'];

			$users[$user_id][] = $row;				 
		}
		
		$numRows = 0;
		foreach ($users as $user_id => $mail_data)
		{
			$tmp_mail_obj = new ilMail($user_id);			
			
			include_once "Services/Mail/classes/class.ilMimeMail.php";

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
		
		$this->log->write(__METHOD__.': Send '.$numRows.' messages.');	

		return true;
	}
}
?>
