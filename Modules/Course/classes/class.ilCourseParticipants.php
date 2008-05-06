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