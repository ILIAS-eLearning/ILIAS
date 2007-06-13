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

define("IL_CRS_ADMIN",1);
define("IL_CRS_TUTOR",3);
define("IL_CRS_MEMBER",2);

/**
* class ilobjcourse
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @extends Object
*/

class ilCourseMembers
{
	var $course_obj;
	var $ilErr;
	var $ilDB;
	var $lng;

	var $member_data;
	var $subscribers;

	function ilCourseMembers(&$course_obj)
	{
		global $ilErr,$lng,$ilDB;

		$this->STATUS_NOTIFY = 1;
		$this->STATUS_NO_NOTIFY = 2;
		$this->STATUS_BLOCKED = 3;
		$this->STATUS_UNBLOCKED = 4;

		$this->NOTIFY_DISMISS_SUBSCRIBER = 1;
		$this->NOTIFY_ACCEPT_SUBSCRIBER = 2;
		$this->NOTIFY_DISMISS_MEMBER = 3;
		$this->NOTIFY_BLOCK_MEMBER = 4;
		$this->NOTIFY_UNBLOCK_MEMBER = 5;
		$this->NOTIFY_ACCEPT_USER = 6;
		$this->NOTIFY_ADMINS = 7;
		$this->NOTIFY_STATUS_CHANGED = 8;
		$this->NOTIFY_SUBSCRIPTION_REQUEST = 9;


		$this->ROLE_ADMIN = IL_CRS_ADMIN;
		$this->ROLE_MEMBER = IL_CRS_MEMBER;
		$this->ROLE_TUTOR = IL_CRS_TUTOR;

		$this->ilErr =& $ilErr;
		$this->ilDB =& $ilDB;
		$this->db =& $ilDB;

		$this->lng =& $lng;
		$this->lng->loadLanguageModule("search");

		$this->course_obj =& $course_obj;

		$this->__purgeDeleted();

	}

	function addDesktopItem($a_usr_id)
	{
		if(ilObjUser::_isDesktopItem($a_usr_id, $this->course_obj->getRefId(),'crs'))
		{
			ilObjUser::_addDesktopItem($a_usr_id, $this->course_obj->getRefId(),'crs');
		}

		return true;
	}
	
	
	function dropDesktopItem($a_usr_id)
	{
		if(ilObjUser::_isDesktopItem($a_usr_id, $this->course_obj->getRefId(),'crs'))
		{
			ilObjUser::_dropDesktopItem($a_usr_id, $this->course_obj->getRefId(),'crs');
		}

		return true;
	}

	function add(&$user_obj,$a_role,$a_status = 0,$a_passed = 0)
	{
		global $rbacadmin;

		switch($a_role)
		{
			case $this->ROLE_MEMBER:
				if($a_status and ($a_status == $this->STATUS_BLOCKED or $a_status == $this->STATUS_UNBLOCKED))
				{
					$status = $a_status;
				}
				else if($a_status)
				{
					$this->ilErr->raiseError($this->lng->txt("crs_status_not_allowed",$this->ilErr->MESSAGE));
				}
				else
				{
					$status = $this->__getDefaultMemberStatus();
				}
				$role = $this->course_obj->getDefaultMemberRole();
				$passed = $a_passed;

				$this->addDesktopItem($user_obj->getId());
				break;

			case $this->ROLE_ADMIN:
				if($a_status and ($a_status == $this->STATUS_NOTIFY or $a_status == $this->STATUS_NO_NOTIFY))
				{
					$status = $a_status;
				}
				else if($a_status)
				{
					$this->ilErr->raiseError($this->lng->txt("crs_status_not_allowed",$this->ilErr->MESSAGE));
				}
				else
				{
					$status = $this->__getDefaultAdminStatus();
				}
				$role = $this->course_obj->getDefaultAdminRole();
				$passed = $a_passed;
				$this->addDesktopItem($user_obj->getId());
				break;

			case $this->ROLE_TUTOR:
				if($a_status and ($a_status == $this->STATUS_NOTIFY or $a_status == $this->STATUS_NO_NOTIFY))
				{
					$status = $a_status;
				}
				else if($a_status)
				{
					$this->ilErr->raiseError($this->lng->txt("crs_status_not_allowed",$this->ilErr->MESSAGE));
				}
				else
				{
					$status = $this->__getDefaultTutorStatus();
				}
				$role = $this->course_obj->getDefaultTutorRole();
				$passed = $a_passed;
				$this->addDesktopItem($user_obj->getId());
				break;

		}
		// 1. create entry
		$this->__createMemberEntry($user_obj->getId(),$a_role,$status,$passed);

		$rbacadmin->assignUser($role,$user_obj->getId());

		return true;
	}

	function update($a_usr_id,$a_role,$a_status,$a_passed)
	{
		global $rbacadmin,$rbacreview,$ilDB;

		$this->__read($a_usr_id);

		switch($a_role)
		{
			case $this->ROLE_ADMIN:
				if($a_status != $this->STATUS_NOTIFY or $a_status != $this->STATUS_NO_NOTIFY)
				{
					$this->ilErr->raiseError($this->lng->txt("crs_status_not_allowed",$this->ilErr->MESSAGE));
				}
				break;

			case $this->ROLE_TUTOR:
				if($a_status != $this->STATUS_NOTIFY or $a_status != $this->STATUS_NO_NOTIFY)
				{
					$this->ilErr->raiseError($this->lng->txt("crs_status_not_allowed",$this->ilErr->MESSAGE));
				}
				break;

			case $this->ROLE_MEMBER:
				if($a_status != $this->STATUS_BLOCKED or $a_status != $this->STATUS_UNBLOCKED)
				{
					$this->ilErr->raiseError($this->lng->txt("crs_status_not_allowed",$this->ilErr->MESSAGE));
				}
		//		$this->addDesktopItem($a_usr_id);

			default:
				$this->ilErr->raiseError($this->lng->txt("crs_role_not_allowed",$this->ilErr->MESSAGE));
				break;
		}

		// UPDATE RBAC ROLES
		$hadRoleBefore = false;
		// deassign old roles
		switch($this->member_data["role"])
		{
			case $this->ROLE_ADMIN:
				$rbacadmin->deassignUser($this->course_obj->getDefaultAdminRole(),$a_usr_id);
				break;

			case $this->ROLE_TUTOR:
				$rbacadmin->deassignUser($this->course_obj->getDefaultTutorRole(),$a_usr_id);
				break;

			case $this->ROLE_MEMBER:
				$hadRoleBefore = $rbacreview->isAssigned($a_usr_id, $this->course_obj->getDefaultMemberRole());
				$rbacadmin->deassignUser($this->course_obj->getDefaultMemberRole(),$a_usr_id);
				break;
		}
		// assign new role
		switch($a_role)
		{
			case $this->ROLE_ADMIN:
				$rbacadmin->assignUser($this->course_obj->getDefaultAdminRole(),$a_usr_id);
				break;

			case $this->ROLE_TUTOR:
				$rbacadmin->assignUser($this->course_obj->getDefaultTutorRole(),$a_usr_id);
				break;

			case $this->ROLE_MEMBER:
				if($a_status != $this->STATUS_BLOCKED)
				{
					$rbacadmin->assignUser($this->course_obj->getDefaultMemberRole(),$a_usr_id);
				}
				if (!$hadRoleBefore)
					$this->addDesktopItem($a_usr_id);
				break;
		}


		$query = "UPDATE crs_members ".
			"SET role = ".$ilDB->quote($a_role).", ".
			"status = ".$ilDB->quote($a_status).", ".
			"passed = ".$ilDB->quote($a_passed)." ".
			"WHERE obj_id = ".$ilDB->quote($this->course_obj->getId())." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)."";
		$res = $this->ilDB->query($query);

		return true;
	}


	function deleteAllEntries()
	{
		global $ilDB;

		$query = "DELETE FROM crs_members ".
			"WHERE obj_id = ".$ilDB->quote($this->course_obj->getId())." ";

		$this->ilDB->query($query);

		$query = "DELETE FROM crs_subscribers ".
			"WHERE obj_id = ".$ilDB->quote($this->course_obj->getId())."";

		$this->ilDB->query($query);

		return true;
	}

	function deleteMembers($a_usr_ids)
	{
		if(!is_array($a_usr_ids) or !count($a_usr_ids))
		{
			$this->course_obj->setMessage("");
			$this->course_obj->appendMessage($this->lng->txt("no_usr_ids_given"));

			return false;
		}
		foreach($a_usr_ids as $id)
		{
			if(!$this->delete($id))
			{
				$this->course_obj->appendMessage($this->lng->txt("error_delete_member"));

				return false;
			}
		}
		return true;
	}

	function delete($a_usr_id)
	{
		global $rbacadmin,$ilDB;

		if(!$this->__read($a_usr_id))
		{
			return true;
		}

		switch($this->member_data["role"])
		{
			case $this->ROLE_ADMIN:
				$role = $this->course_obj->getDefaultAdminRole();
				break;


			case $this->ROLE_TUTOR:
				$role = $this->course_obj->getDefaultTutorRole();
				break;

			case $this->ROLE_MEMBER:
				$role = $this->course_obj->getDefaultMemberRole();
				break;
		}

		$this->dropDesktopItem($a_usr_id);
		$rbacadmin->deassignUser($role,$a_usr_id);


		$query = "DELETE FROM crs_members ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND obj_id = ".$ilDB->quote($this->course_obj->getId())."";

		$res = $this->ilDB->query($query);

		return true;
	}


	/*
	 * Delete user entries called from ilObjUser->delete()
	 */
	function _deleteUser($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_members WHERE usr_id = ".$ilDB->quote($a_usr_id)."";
		$ilDB->query($query);

		$query = "DELETE FROM crs_subscribers WHERE usr_id = ".$ilDB->quote($a_usr_id)."";
		$ilDB->query($query);

		include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
		ilCourseWaitingList::_deleteUser($a_usr_id);
	}

	function getAssignedUsers()
	{
		// ALL MEMBERS AND ADMINS
		return array_merge($this->getMembers(),$this->getAdmins(),$this->getTutors());
	}
	function getUserData($a_usr_id)
	{
		if(!$this->__read($a_usr_id))
		{
			return $a_usr_id;
		}
		return $this->member_data;
	}

	function getCountMembers()
	{
		return count($this->getMembers(false));
	}

	function getMembers($a_all = true)
	{
		global $ilDB;

		$query = "SELECT cs.usr_id FROM crs_members as cs ".
			"WHERE obj_id = ".$ilDB->quote($this->course_obj->getId())." ".
			"AND role = ".$ilDB->quote($this->ROLE_MEMBER)." ";

		if(!$a_all)
		{
			$query .= " AND status = ".$ilDB->quote($this->STATUS_UNBLOCKED)." ";
		}

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$usr_ids[] = $row->usr_id;
		}
		return $usr_ids ? $usr_ids : array();
	}
	function getAdmins()
	{
		global $ilDB;

		$query = "SELECT cs.usr_id FROM crs_members as cs ".
			"WHERE obj_id = ".$ilDB->quote($this->course_obj->getId())." ".
			"AND role = ".$ilDB->quote($this->ROLE_ADMIN)."";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$usr_ids[] = $row->usr_id;
		}
		return $usr_ids ? $usr_ids : array();
	}
	function getTutors()
	{
		global $ilDB;

		$query = "SELECT cs.usr_id FROM crs_members as cs ".
			"WHERE obj_id = ".$ilDB->quote($this->course_obj->getId())." ".
			"AND role = ".$ilDB->quote($this->ROLE_TUTOR)."";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$usr_ids[] = $row->usr_id;
		}
		return $usr_ids ? $usr_ids : array();
	}

	function isAdmin($a_usr_id)
	{
		$this->__read($a_usr_id);

		return $this->member_data["role"] == $this->ROLE_ADMIN ? true : false;
	}
	function isMember($a_usr_id)
	{
		$this->__read($a_usr_id);

		return $this->member_data["role"] == $this->ROLE_MEMBER ? true : false;
	}
	function isTutor($a_usr_id)
	{
		$this->__read($a_usr_id);

		return $this->member_data["role"] == $this->ROLE_TUTOR ? true : false;
	}
	function isAssigned($a_usr_id)
	{
		return $this->isAdmin($a_usr_id) || $this->isMember($a_usr_id) || $this->isTutor($a_usr_id);
	}
	function isBlocked($a_usr_id)
	{
		$this->__read($a_usr_id);

		return $this->member_data["status"] == $this->STATUS_BLOCKED ? true : false;
	}
	/**
	 * Static version of isBlocked() to avoid instantiation of course object
	 *
	 * @param int id of user
	 * @return boolean
	 */
	function _isBlocked($a_obj_id,$a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_members ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id)."";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->status == 3 ? true : false;
		}
		return false;
	}


	function hasAccess($a_usr_id)
	{
		global $rbacsystem;

		#if($rbacsystem->checkAccess('write',$this->course_obj->getRefId()))
		#{
		#	return true;
		#}

		return $this->isAssigned($a_usr_id) && !$this->isBlocked($a_usr_id) ? true : false;
	}

	function getCountPassed()
	{
		global $ilDB;

		$query = "SELECT * FROM crs_members ".
			"WHERE obj_id = ".$ilDB->quote($this->course_obj->getId())." ".
			"AND passed = 1";

		$res = $this->ilDB->query($query);

		return $res->numRows() ? $res->numRows() : 0;
	}

	function checkLastAdmin($a_usr_ids)
	{
		foreach($this->getAdmins() as $admin_id)
		{
			if(!in_array($admin_id,$a_usr_ids))
			{
				return true;
			}
		}
		return false;
	}

	// METHODS FOR NEW REGISTRATIONS
	function getSubscribers()
	{
		$this->__readSubscribers();

		return $this->subscribers;
	}

	function getCountSubscribers()
	{
		return count($this->getSubscribers());
	}

	function getSubscriberData($a_usr_id)
	{
		return $this->__readSubscriberData($a_usr_id);
	}



	function assignSubscribers($a_usr_ids)
	{
		if(!is_array($a_usr_ids) or !count($a_usr_ids))
		{
			return false;
		}
		foreach($a_usr_ids as $id)
		{
			if(!$this->assignSubscriber($id))
			{
				return false;
			}
		}
		return true;
	}

	function assignSubscriber($a_usr_id,$a_role = 0,$a_status = 0)
	{
		$a_role = $a_role ? $a_role : $this->ROLE_MEMBER;
		$a_status = $a_status ? $a_status : $this->STATUS_UNBLOCKED;

		$this->course_obj->setMessage("");


		if(!$this->isSubscriber($a_usr_id))
		{
			$this->course_obj->appendMessage($this->lng->txt("crs_user_notsubscribed"));

			return false;
		}
		if($this->isAssigned($a_usr_id))
		{
			$tmp_obj = ilObjectFactory::getInstanceByObjId($a_usr_id);
			$this->course_obj->appendMessage($tmp_obj->getLogin().": ".$this->lng->txt("crs_user_already_assigned"));

			return false;
		}

		if(!$tmp_obj =& ilObjectFactory::getInstanceByObjId($a_usr_id))
		{
			$this->course_obj->appendMessage($this->lng->txt("crs_user_not_exists"));

			return false;
		}

		$this->add($tmp_obj,$a_role,$a_status);
		$this->deleteSubscriber($a_usr_id);

		return true;
	}

	function autoFillSubscribers()
	{
		$this->__readSubscribers();

		$counter = 0;
		foreach($this->subscribers as $subscriber)
		{
			if($this->course_obj->getSubscriptionMaxMembers() and
			   $this->course_obj->getSubscriptionMaxMembers() <= $this->getCountMembers())
			{
				return $counter;
			}
			if(!$this->assignSubscriber($subscriber))
			{
				continue;
			}
			else
			{
				$this->sendNotification($this->NOTIFY_ACCEPT_SUBSCRIBER,$subscriber);
			}
			++$counter;
		}

		return $counter;
	}

	function addSubscriber($a_usr_id)
	{
		global $ilDB;

		$query = "INSERT INTO crs_subscribers ".
			" VALUES (".$ilDB->quote($a_usr_id).",".$ilDB->quote($this->course_obj->getId()).",".$ilDB->quote(time()).")";

		$res = $this->ilDB->query($query);

		return true;
	}

	function updateSubscriptionTime($a_usr_id,$a_subtime)
	{
		global $ilDB;

		$query = "UPDATE crs_subscribers ".
			"SET sub_time = ".$ilDB->quote($a_subtime)." ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND obj_id = ".$ilDB->quote($this->course_obj->getId())." ";

		$this->db->query($query);

		return true;
	}

	function deleteSubscriber($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_subscribers ".
			"WHERE usr_id = ".$a_usr_id." ".
			"AND obj_id = ".$ilDB->quote($this->course_obj->getId())." ";

		$res = $this->ilDB->query($query);

		return true;
	}

	function deleteSubscribers($a_usr_ids)
	{
		if(!is_array($a_usr_ids) or !count($a_usr_ids))
		{
			$this->course_obj->setMessage("");
			$this->course_obj->appendMessage($this->lng->txt("no_usr_ids_given"));

			return false;
		}
		foreach($a_usr_ids as $id)
		{
			if(!$this->deleteSubscriber($id))
			{
				$this->course_obj->appendMessage($this->lng->txt("error_delete_subscriber"));

				return false;
			}
		}
		return true;
	}
	function isSubscriber($a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_subscribers ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND obj_id = ".$ilDB->quote($this->course_obj->getId())."";

		$res = $this->ilDB->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return true;
		}
		return false;
	}

	/*
	 * Static method
	 */
	function _isSubscriber($a_obj_id,$a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_subscribers ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND obj_id = ".$ilDB->quote($a_obj_id)."";

		$res = $ilDB->query($query);

		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return true;
		}
		return false;
	}

	function sendNotification($a_type, $a_usr_id)
	{
		$tmp_user =& ilObjectFactory::getInstanceByObjId($a_usr_id,false);

		$link = ("\n\n".$this->lng->txt('crs_mail_permanent_link'));
		$link .= ("\n\n".ILIAS_HTTP_PATH."/goto.php?target=crs_".$this->course_obj->getRefId()."&client_id=".CLIENT_ID);


		switch($a_type)
		{
			case $this->NOTIFY_DISMISS_SUBSCRIBER:
				$subject = $this->lng->txt("crs_reject_subscriber");
				$body = $this->lng->txt("crs_reject_subscriber_body");
				break;

			case $this->NOTIFY_ACCEPT_SUBSCRIBER:
				$subject = $this->lng->txt("crs_accept_subscriber");
				$body = $this->lng->txt("crs_accept_subscriber_body");
				$body .= $link;
				break;
			case $this->NOTIFY_DISMISS_MEMBER:
				$subject = $this->lng->txt("crs_dismiss_member");
				$body = $this->lng->txt("crs_dismiss_member_body");
				break;
			case $this->NOTIFY_BLOCK_MEMBER:
				$subject = $this->lng->txt("crs_blocked_member");
				$body = $this->lng->txt("crs_blocked_member_body");
				break;
			case $this->NOTIFY_UNBLOCK_MEMBER:
				$subject = $this->lng->txt("crs_unblocked_member");
				$body = $this->lng->txt("crs_unblocked_member_body");
				$body .= $link;
				break;
			case $this->NOTIFY_ACCEPT_USER:
				$subject = $this->lng->txt("crs_added_member");
				$body = $this->lng->txt("crs_added_member_body");
				$body .= $link;
				break;
			case $this->NOTIFY_STATUS_CHANGED:
				$subject = $this->lng->txt("crs_status_changed");
				$body = $this->__buildStatusBody($tmp_user);
				$body .= $link;
				break;

			case $this->NOTIFY_SUBSCRIPTION_REQUEST:
				$this->sendSubscriptionRequestToAdmins($a_usr_id);
				return true;
				break;

			case $this->NOTIFY_ADMINS:
				$this->sendNotificationToAdmins($a_usr_id);

				return true;
				break;
		}
		$subject = sprintf($subject, $this->course_obj->getTitle());
		$body = sprintf($body, $this->course_obj->getTitle());

		include_once("Services/Mail/classes/class.ilFormatMail.php");

		$mail = new ilFormatMail($_SESSION["AccountId"]);
		$mail->sendMail($tmp_user->getLogin(),'','',$subject,$body,array(),array('system'));

		unset($tmp_user);
		return true;
	}

	function sendNotificationToAdmins($a_usr_id)
	{
		global $ilDB;

		if(!$this->course_obj->getSubscriptionNotify())
		{
			return true;
		}


		include_once("Services/Mail/classes/class.ilFormatMail.php");

		$mail =& new ilFormatMail($a_usr_id);
		$subject = sprintf($this->lng->txt("crs_new_subscription"),$this->course_obj->getTitle());
		$body = sprintf($this->lng->txt("crs_new_subscription_body"),$this->course_obj->getTitle());

		$query = "SELECT usr_id FROM crs_members ".
			"WHERE status = ".$ilDB->quote($this->STATUS_NOTIFY)." ".
			"AND obj_id = ".$ilDB->quote($this->course_obj->getId())."";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$tmp_user =& ilObjectFactory::getInstanceByObjId($row->usr_id,false);

			$message = $mail->sendMail($tmp_user->getLogin(),'','',$subject,$body,array(),array('normal'));
			unset($tmp_user);
		}
		unset($mail);

		return true;
	}
	function sendSubscriptionRequestToAdmins($a_usr_id)
	{
		global $ilDB;

		if(!$this->course_obj->getSubscriptionNotify())
		{
			return true;
		}


		include_once("Services/Mail/classes/class.ilFormatMail.php");

		$mail =& new ilFormatMail($a_usr_id);
		$subject = sprintf($this->lng->txt("crs_new_subscription_request"),$this->course_obj->getTitle());
		$body = sprintf($this->lng->txt("crs_new_subscription_request_body"),$this->course_obj->getTitle());
		$body .= ("\n\n".$this->lng->txt('crs_new_subscription_request_body2'));
		$body .= ("\n\n".ILIAS_HTTP_PATH."/goto.php?target=crs_".$this->course_obj->getRefId()."&client_id=".CLIENT_ID);

		$query = "SELECT usr_id FROM crs_members ".
			"WHERE status = ".$ilDB->quote($this->STATUS_NOTIFY)." ".
			"AND obj_id = ".$ilDB->quote($this->course_obj->getId())."";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$tmp_user =& ilObjectFactory::getInstanceByObjId($row->usr_id,false);
			$message = $mail->sendMail($tmp_user->getLogin(),'','',$subject,$body,array(),array('normal'));
			unset($tmp_user);
		}
		unset($mail);

		return true;
	}
	function sendUnsubscribeNotificationToAdmins($a_usr_id)
	{
		global $ilDB;

		if(!$this->course_obj->getSubscriptionNotify())
		{
			return true;
		}

		include_once("Services/Mail/classes/class.ilFormatMail.php");

		$mail =& new ilFormatMail($a_usr_id);
		$subject = sprintf($this->lng->txt("crs_cancel_subscription"), $this->course_obj->getTitle());
		$body = sprintf($this->lng->txt("crs_cancel_subscription_body"), $this->course_obj->getTitle());

		$query = "SELECT usr_id FROM crs_members ".
			"WHERE status = ".$ilDB->quote($this->STATUS_NOTIFY)." ".
			"AND obj_id = ".$ilDB->quote($this->course_obj->getId())."";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$tmp_user =& ilObjectFactory::getInstanceByObjId($row->usr_id,false);

			$message = $mail->sendMail($tmp_user->getLogin(),'','',$subject,$body,array(),array('normal'));
			unset($tmp_user);
		}
		unset($mail);

		return true;
	}

	// PRIVATE METHODS
	function __getDefaultAdminStatus()
	{
		return $this->STATUS_NOTIFY;
	}
	function __getDefaultMemberStatus()
	{
		return $this->STATUS_UNBLOCKED;
	}
	function __getDefaultTutorStatus()
	{
		return $this->STATUS_NO_NOTIFY;
	}

	function __createMemberEntry($a_usr_id,$a_role,$a_status,$a_passed)
	{
		global $ilDB;

		$query = "INSERT INTO crs_members ".
			"SET usr_id = ".$ilDB->quote($a_usr_id).", ".
			"obj_id = ".$ilDB->quote($this->course_obj->getId()).", ".
			"status = ".$ilDB->quote($a_status).", ".
			"role = ".$ilDB->quote($a_role).", ".
			"passed = ".$ilDB->quote($a_passed)."";

		$res = $this->ilDB->query($query);

		return true;
	}

	function __read($a_usr_id)
	{
		global $ilDB;

		if(!ilObjUser::_lookupLogin($a_usr_id))
		{
			return false;
		}

		$query = "SELECT * FROM crs_members ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND obj_id =".$ilDB->quote($this->course_obj->getId())."";

		$res = $this->ilDB->query($query);

		$this->member_data = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->member_data["usr_id"]	= $row->usr_id;
			$this->member_data["role"]		= $row->role;
			$this->member_data["status"]	= $row->status;
			$this->member_data['passed']	= $row->passed;
		}
		return true;
	}


	function __readSubscribers()
	{
		global $ilDB;

		$this->subscribers = array();

		$query = "SELECT usr_id FROM crs_subscribers ".
			"WHERE obj_id = ".$ilDB->quote($this->course_obj->getId())." ".
			"ORDER BY sub_time ";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			// DELETE SUBSCRIPTION IF USER HAS BEEN DELETED
			if(!ilObjectFactory::getInstanceByObjId($row->usr_id,false))
			{
				$this->deleteSubscriber($row->usr_id);
			}
			$this->subscribers[] = $row->usr_id;
		}
		return true;
	}

	function __readSubscriberData($a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_subscribers ".
			"WHERE obj_id = ".$ilDB->quote($this->course_obj->getId())." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)."";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$data["time"] = $row->sub_time;
			$data["usr_id"] = $row->usr_id;
		}
		return $data ? $data : array();
	}

	function _hasPassed($a_obj_id,$a_usr_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_members ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND passed = 1";


		$res = $ilDB->query($query);

		return $res->numRows() ? true : false;
	}

	function _setPassed($a_obj_id,$a_usr_id)
	{
		global $ilDB;

		if(!ilCourseMembers::_hasPassed($a_obj_id,$a_usr_id))
		{
			$query = "UPDATE crs_members ".
				"SET passed = 1 WHERE usr_id = ".$ilDB->quote($a_usr_id)." ".
				"AND obj_id = ".$ilDB->quote($a_obj_id)."";

			$ilDB->query($query);

			return true;
		}
		return false;
	}

	function _updatePassed($a_obj_id,$a_usr_id,$a_passed)
	{
		global $ilDB;

		$query = "UPDATE crs_members ".
			"SET passed = ".$ilDB->quote($a_passed)." ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)." ";

		$ilDB->query($query);
	}

	function _updateStatus($a_obj_id,$a_usr_id,$a_status)
	{
		global $ilDB;

		$query = "UPDATE crs_members ".
			"SET status = ".$ilDB->quote($a_status)." ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)."";

		$ilDB->query($query);
	}


	function __buildStatusBody(&$user_obj)
	{
		global $ilDB;

		$this->__read($user_obj->getId());

		$body = $this->lng->txt('crs_status_changed_body').':<br />';
		$body .= $this->lng->txt('login').': '.$user_obj->getLogin().'<br />';
		$body .= $this->lng->txt('role').': ';

		switch($this->member_data['role'])
		{
			case $this->ROLE_MEMBER:
				$body .= $this->lng->txt('crs_member').'<br />';
				break;

			case $this->ROLE_TUTOR:
				$body .= $this->lng->txt('crs_tutor').'<br />';
				break;

			case $this->ROLE_ADMIN:
				$body .= $this->lng->txt('crs_admin').'<br />';
				break;
		}
		$body .= $this->lng->txt('status').': ';
		switch($this->member_data['status'])
		{
			case $this->STATUS_NOTIFY:
				$body .= $this->lng->txt("crs_notify").'<br />';
				break;

			case $this->STATUS_NO_NOTIFY:
				$body .= $this->lng->txt("crs_no_notify").'<br />';
				break;

			case $this->STATUS_BLOCKED:
				$body .= $this->lng->txt("crs_blocked").'<br />';
				break;

			case $this->STATUS_UNBLOCKED:
				$body .= $this->lng->txt("crs_unblocked").'<br />';
				break;
		}
		$passed = $this->member_data['passed'] ? $this->lng->txt('yes') : $this->lng->txt('no');
		$body .= $this->lng->txt('crs_passed').': '.$passed.'<br />';

		return $body;
	}

	/**
	* check Membership by given field
	* @param int usr_id
	* @param int obj_id
	* @param string field (login,email or matriculation)
	* @access	public
	*/

	function _isMember($a_usr_id,$a_course_id,$a_field = '')
	{
		global $ilUser,$ilDB;

		// get specific user data
		$tmp_user =& ilObjectFactory::getInstanceByObjId($a_usr_id);
		switch($a_field)
		{
			case 'login':
				$and = "AND login = '".$tmp_user->getLogin()."' ";
				break;
			case 'email':
				$and = "AND email = '".$tmp_user->getEmail()."' ";
				break;
			case 'matriculation':
				$and = "AND matriculation = '".$tmp_user->getMatriculation()."' ";
				break;

			default:
				$and = "AND cm.usr_id = '".$a_usr_id."'";
				break;
		}

		// check if entry exists
		$query = "SELECT * FROM crs_members as cm, usr_data as ud ".
			"WHERE cm.usr_id = ud.usr_id ".
			"AND cm.obj_id = ".$ilDB->quote($a_course_id)." ".
			$and;


		$res = $ilDB->query($query);

		return $res->numRows() ? true : false;
	}

	function _getMembers($a_obj_id)
	{
		global $ilDB;
		// In the moment all users that have entries in crs_members

		$query = "SELECT DISTINCT(crs_members.usr_id) as usr_id FROM crs_members JOIN usr_data ".
			"WHERE crs_members.usr_id = usr_data.usr_id ".
			"AND obj_id = ".$ilDB->quote($a_obj_id)."";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$usr_ids[] = $row->usr_id;
		}

		return $usr_ids ? $usr_ids : array();
	}

	function __purgeDeleted()
	{
		global $ilDB;

		$query = "SELECT cs.usr_id as id FROM crs_members as cs ".
			"LEFT JOIN usr_data as ud ".
			"ON cs.usr_id = ud.usr_id ".
			"WHERE obj_id = ".$ilDB->quote($this->course_obj->getId())." ".
			"AND ud.usr_id IS NULL";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$query = "DELETE FROM crs_members ".
				"WHERE usr_id = ".$ilDB->quote($row->id)."";
			$ilDB->query($query);
		}
	}


}
?>
