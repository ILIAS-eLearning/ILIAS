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

include_once('./Services/Membership/classes/class.ilParticipants.php');

/** 
* 
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
* 
* 
* @ingroup ModulesCourse 
*/

class ilCourseParticipants extends ilParticipants
{
	protected static $instances = array();
	
	/**
	 * Singleton constructor
	 *
	 * @access protected
	 * @param int obj_id of container
	 */
	protected function __construct($a_obj_id)
	{
		$this->type = 'crs';
		
		$this->NOTIFY_DISMISS_SUBSCRIBER = 1;
		$this->NOTIFY_ACCEPT_SUBSCRIBER = 2;
		$this->NOTIFY_DISMISS_MEMBER = 3;
		$this->NOTIFY_BLOCK_MEMBER = 4;
		$this->NOTIFY_UNBLOCK_MEMBER = 5;
		$this->NOTIFY_ACCEPT_USER = 6;
		$this->NOTIFY_ADMINS = 7;
		$this->NOTIFY_STATUS_CHANGED = 8;
		$this->NOTIFY_SUBSCRIPTION_REQUEST = 9;
		
		parent::__construct($a_obj_id);
	}

	/**
	 * Get singleton instance
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 */
	public static function _getInstanceByObjId($a_obj_id)
	{
		if(isset(self::$instances[$a_obj_id]) and self::$instances[$a_obj_id])
		{
			return self::$instances[$a_obj_id];
		}
		return self::$instances[$a_obj_id] = new ilCourseParticipants($a_obj_id);
	}
	
	
	

	/**
	 * Update passed status
	 *
	 * @access public
	 * @param int usr_id
	 * @param bool passed
	 * 
	 */
	public function updatePassed($a_usr_id,$a_passed)
	{
		global $ilDB;
		
		$this->participants_status[$a_usr_id]['passed'] = (int) $a_passed;

		$query = "SELECT * FROM crs_members ".
		"WHERE obj_id = ".$ilDB->quote($this->obj_id)." ".
		"AND usr_id = ".$ilDB->quote($a_usr_id);
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			$query = "UPDATE crs_members SET ".
				"passed = ".$ilDB->quote((int) $a_passed)." ".
				"WHERE obj_id = ".$ilDB->quote($this->obj_id)." ".
				"AND usr_id = ".$ilDB->quote($a_usr_id);
		}
		else
		{
			$query = "INSERT INTO crs_members SET ".
				"passed = ".$ilDB->quote((int) $a_passed).", ".
				"obj_id = ".$ilDB->quote($this->obj_id).", ".
				"usr_id = ".$ilDB->quote($a_usr_id);
			
		}
		$res = $ilDB->query($query);
		return true;
	
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

	function assignSubscriber($a_usr_id)
	{
		global $ilErr;
		
		$ilErr->setMessage("");
		if(!$this->isSubscriber($a_usr_id))
		{
			$ilErr->appendMessage($this->lng->txt("crs_user_notsubscribed"));

			return false;
		}
		if($this->isAssigned($a_usr_id))
		{
			$tmp_obj = ilObjectFactory::getInstanceByObjId($a_usr_id);
			$ilErr->appendMessage($tmp_obj->getLogin().": ".$this->lng->txt("crs_user_already_assigned"));

			return false;
		}

		if(!$tmp_obj =& ilObjectFactory::getInstanceByObjId($a_usr_id))
		{
			$ilErr->appendMessage($this->lng->txt("crs_user_not_exists"));

			return false;
		}

		$this->add($tmp_obj->getId(),IL_CRS_MEMBER);
		$this->deleteSubscriber($a_usr_id);

		return true;
	}

	function autoFillSubscribers()
	{
		$this->__readSubscribers();

		$counter = 0;
		foreach($this->subscribers as $subscriber)
		{
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
			" VALUES (".$ilDB->quote($a_usr_id).",".$ilDB->quote($this->obj_id).",".$ilDB->quote(time()).")";
		$res = $this->ilDB->query($query);

		return true;
	}

	function updateSubscriptionTime($a_usr_id,$a_subtime)
	{
		global $ilDB;

		$query = "UPDATE crs_subscribers ".
			"SET sub_time = ".$ilDB->quote($a_subtime)." ".
			"WHERE usr_id = ".$ilDB->quote($a_usr_id)." ".
			"AND obj_id = ".$ilDB->quote($this->obj_id)." ";

		$this->db->query($query);

		return true;
	}

	function deleteSubscriber($a_usr_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_subscribers ".
			"WHERE usr_id = ".$a_usr_id." ".
			"AND obj_id = ".$ilDB->quote($this->obj_id)." ";

		$res = $ilDB->query($query);

		return true;
	}

	function deleteSubscribers($a_usr_ids)
	{
		global $ilErr;
		
		if(!is_array($a_usr_ids) or !count($a_usr_ids))
		{
			$ilErr->setMessage('');
			$ilErr->appendMessage($this->lng->txt("no_usr_ids_given"));

			return false;
		}
		foreach($a_usr_ids as $id)
		{
			if(!$this->deleteSubscriber($id))
			{
				$ilErr->appendMessage($this->lng->txt("error_delete_subscriber"));

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
			"AND obj_id = ".$ilDB->quote($this->obj_id)."";

		$res = $ilDB->query($query);

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
	function __readSubscribers()
	{
		global $ilDB;

		$this->subscribers = array();

		$query = "SELECT usr_id FROM crs_subscribers ".
			"WHERE obj_id = ".$ilDB->quote($this->obj_id)." ".
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
			"WHERE obj_id = ".$ilDB->quote($this->obj_id)." ".
			"AND usr_id = ".$ilDB->quote($a_usr_id)."";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$data["time"] = $row->sub_time;
			$data["usr_id"] = $row->usr_id;
		}
		return $data ? $data : array();
	}
	
	
	// Subscription
	function sendNotification($a_type, $a_usr_id)
	{
		global $ilObjDataCache,$ilUser;
	
		$tmp_user =& ilObjectFactory::getInstanceByObjId($a_usr_id,false);

		$link = ("\n\n".$this->lng->txt('crs_mail_permanent_link'));
		$link .= ("\n\n".ILIAS_HTTP_PATH."/goto.php?target=crs_".$this->course_ref_id."&client_id=".CLIENT_ID);

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
		$subject = sprintf($subject,$ilObjDataCache->lookupTitle($this->obj_id));
		$body = sprintf($body,$ilObjDataCache->lookupTitle($this->obj_id));

		include_once("Services/Mail/classes/class.ilMail.php");
		$mail = new ilMail($ilUser->getId());
		$mail->sendMail($tmp_user->getLogin(),'','',$subject,$body,array(),array('system'));

		unset($tmp_user);
		return true;
	}
	
	function sendUnsubscribeNotificationToAdmins($a_usr_id)
	{
		global $ilDB,$ilObjDataCache;

		if(!ilObjCourse::_isSubscriptionNotificationEnabled($this->obj_id))
		{
			return true;
		}

		include_once("Services/Mail/classes/class.ilFormatMail.php");

		$mail =& new ilFormatMail($a_usr_id);
		$subject = sprintf($this->lng->txt("crs_cancel_subscription"),$ilObjDataCache->lookupTitle($this->obj_id));
		$body = sprintf($this->lng->txt("crs_cancel_subscription_body"),$ilObjDataCache->lookupTitle($this->obj_id));
		$body .= ("\n\n".$this->lng->txt('crs_mail_permanent_link'));
		$body .= ("\n\n".ILIAS_HTTP_PATH."/goto.php?target=crs_".$this->course_ref_id."&client_id=".CLIENT_ID);
		

		foreach($this->getNotificationRecipients() as $usr_id)
		{
			$tmp_user =& ilObjectFactory::getInstanceByObjId($usr_id,false);
			$message = $mail->sendMail($tmp_user->getLogin(),'','',$subject,$body,array(),array('system'));
			unset($tmp_user);
		}
		return true;
	}
	
	
	function sendSubscriptionRequestToAdmins($a_usr_id)
	{
		global $ilDB,$ilObjDataCache,$ilUser;

		if(!ilObjCourse::_isSubscriptionNotificationEnabled($this->obj_id))
		{
			return true;
		}

		include_once("Services/Mail/classes/class.ilMail.php");

		$mail = new ilMail($ilUser->getId());
		$subject = sprintf($this->lng->txt("crs_new_subscription_request"),$ilObjDataCache->lookupTitle($this->obj_id));
		$body = sprintf($this->lng->txt("crs_new_subscription_request_body"),$ilObjDataCache->lookupTitle($this->obj_id));
		$body .= ("\n\n".$this->lng->txt('crs_new_subscription_request_body2'));
		$body .= ("\n\n".ILIAS_HTTP_PATH."/goto.php?target=crs_".$this->course_ref_id."&client_id=".CLIENT_ID);

		foreach($this->getNotificationRecipients() as $usr_id)
		{
			$tmp_user =& ilObjectFactory::getInstanceByObjId($usr_id,false);
			$message = $mail->sendMail($tmp_user->getLogin(),'','',$subject,$body,array(),array('system'));
		}
		return true;
	}
	

	function sendNotificationToAdmins($a_usr_id)
	{
		global $ilDB,$ilObjDataCache;

		if(!ilObjCourse::_isSubscriptionNotificationEnabled($this->obj_id))
		{
			return true;
		}

		include_once("Services/Mail/classes/class.ilFormatMail.php");

		$mail =& new ilFormatMail($a_usr_id);
		$subject = sprintf($this->lng->txt("crs_new_subscription"),$ilObjDataCache->lookupTitle($this->obj_id));
		$body = sprintf($this->lng->txt("crs_new_subscription_body"),$ilObjDataCache->lookupTitle($this->obj_id));
		$body .= ("\n\n".ILIAS_HTTP_PATH."/goto.php?target=crs_".$this->course_ref_id."&client_id=".CLIENT_ID);

		$query = "SELECT usr_id FROM crs_members ".
			"WHERE notification = '1' ".
			"AND obj_id = ".$ilDB->quote($this->obj_id)."";

		$res = $this->ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if($this->isAdmin($row->usr_id) or $this->isTutor($row->usr_id))
			{
				$tmp_user =& ilObjectFactory::getInstanceByObjId($row->usr_id,false);
				$message = $mail->sendMail($tmp_user->getLogin(),'','',$subject,$body,array(),array('system'));
				unset($tmp_user);
			}
		}
		unset($mail);

		return true;
	}
	
	function __buildStatusBody(&$user_obj)
	{
		global $ilDB;

		$body = $this->lng->txt('crs_status_changed_body').':<br />';
		$body .= $this->lng->txt('login').': '.$user_obj->getLogin().'<br />';
		$body .= $this->lng->txt('role').': ';

		if($this->isAdmin($user_obj->getId()))
		{
			$body .= $this->lng->txt('crs_member').'<br />';
		}
		if($this->isTutor($user_obj->getId()))
		{
			$body .= $this->lng->txt('crs_tutor').'<br />';
		}
		if($this->isMember($user_obj->getId()))
		{
			$body .= $this->lng->txt('crs_member').'<br />';
		}
		$body .= $this->lng->txt('status').': ';
		
		if($this->isNotificationEnabled($user_obj->getId()))
		{
			$body .= $this->lng->txt("crs_notify").'<br />';
		}
		else
		{
			$body .= $this->lng->txt("crs_no_notify").'<br />';
		}
		if($this->isBlocked($user_obj->getId()))
		{
			$body .= $this->lng->txt("crs_blocked").'<br />';
		}
		else
		{
			$body .= $this->lng->txt("crs_unblocked").'<br />';
		}
		$passed = $this->hasPassed($user_obj->getId()) ? $this->lng->txt('yes') : $this->lng->txt('no');
		$body .= $this->lng->txt('crs_passed').': '.$passed.'<br />';

		return $body;
	}
	
	
}
?>