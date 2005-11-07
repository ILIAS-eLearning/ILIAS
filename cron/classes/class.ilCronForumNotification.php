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
* @author Jens Conze <jc@databay.de>
* @version $Id$
*
* @package ilias
*/

class ilCronForumNotification
{
	function ilCronForumNotification()
	{
		global $ilLog,$ilDB;

		$this->log =& $ilLog;
		$this->db =& $ilDB;
	}

	function sendNotifications()
	{
		global $ilias, $ilUser, $rbacsystem, $lng;

		include_once "./classes/class.ilObjForum.php";
		include_once "./classes/class.ilMail.php";
		include_once "./classes/class.ilObjUser.php";
		include_once "./classes/class.ilLanguage.php";

		$forumObj = new ilObjForum($_GET["ref_id"]);
		$frm =& $forumObj->Forum;

		if(!($lastDate = $ilias->getSetting("cron_forum_notification_last_date")))
		{
			$lastDate = "0000-00-00 00:00:00";
		}

		$q = "SELECT frm_threads.thr_subject AS thr_subject, frm_data.top_name AS top_name, frm_data.top_frm_fk AS obj_id, frm_notification.user_id AS user_id, frm_posts.* FROM frm_notification, frm_posts, frm_threads, frm_data WHERE ";
		$q .= "frm_posts.pos_date >= '" . $lastDate . "' AND ";
		$q .= "frm_posts.pos_thr_fk = frm_threads.thr_pk AND ";
		$q .= "frm_threads.thr_pk = frm_notification.thread_id AND ";
		$q .= "frm_data.top_pk >= frm_threads.thr_top_fk ";
		$q .= "ORDER BY frm_posts.pos_date ASC";
		$res = $this->db->query($q);
		if (!DB::isError($res) &&
			is_object($res) &&
			$res->numRows() > 0)
		{
			while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if ($row["pos_usr_id"] != $row["user_id"])
				{
					// GET AUTHOR OF NEW POST
					$tmp_user =& new ilObjUser($row["pos_usr_id"]);
					$row["pos_usr_name"] = $tmp_user->getLogin();

					// GET USER WHO WANTS TO BE INFORMED ABOUT NEW POSTS
					$tmp_user =& new ilObjUser($row["user_id"]);
	
					if (is_array($obj_data = ilObject::_getAllReferences($row["obj_id"])))
					{
						foreach($obj_data as $ref_id)
						{
							if ($rbacsystem->checkAccessOfUser($row["user_id"], "read", $ref_id))
							{
								$row["ref_id"] = $ref_id;
								break;
							}
						}
					}
	
					if ($row["ref_id"] != "")
					{
						// SEND NOTIFICATIONS BY E-MAIL
						$lng =& new ilLanguage($tmp_user->getLanguage());
						$lng->loadLanguageModule("forum");
						$frm->setLanguage($lng);
						$tmp_mail_obj = new ilMail($row["pos_usr_id"]);
						$message = $tmp_mail_obj->sendMail($tmp_user->getLogin(),"","",
														   $frm->formatNotificationSubject(),
														   $frm->formatNotification($row, 1),
														   array(),array("normal"));
						unset($tmp_mail_obj);
					}
	
					unset($tmp_user);
				}
			}
		}

		$ilias->setSetting("cron_forum_notification_last_date", date("Y-m-d H:i:s"));

		return true;
	}
}
?>
