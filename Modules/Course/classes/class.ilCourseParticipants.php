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
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ModulesCourse 
*/

class ilCourseParticipants extends ilParticipants
{
	const COMPONENT_NAME = 'Modules/Course';
	
	protected static $instances = array();
	
	
	/**
	 * Singleton constructor
	 *
	 * @access protected
	 * @param int obj_id of container
	 */
	public function __construct($a_obj_id)
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
		
		$this->NOTIFY_REGISTERED = 10;
		$this->NOTIFY_UNSUBSCRIBE = 11;
		$this->NOTIFY_WAITING_LIST = 12; 
		
		parent::__construct(self::COMPONENT_NAME,$a_obj_id);
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
	 * Get member roles
	 * @param int $a_ref_id
	 */
	public static function getMemberRoles($a_ref_id)
	{
		global $rbacreview;

		$lrol = $rbacreview->getRolesOfRoleFolder($a_ref_id,false);

		$roles = array();
		foreach($lrol as $role)
		{
			$title = ilObject::_lookupTitle($role);
			switch(substr($title,0,8))
			{
				case 'il_crs_a':
				case 'il_crs_t':
				case 'il_crs_m':
					continue;

				default:
					$roles[$role] = $role;
			}
		}
		return $roles;
	}
	
	public function addSubscriber($a_usr_id)
	{
		global $ilAppEventHandler, $ilLog;
		
		parent::addSubscriber($a_usr_id);

		$ilLog->write(__METHOD__.': Raise new event: Modules/Course addSubscriber');
		$ilAppEventHandler->raise(
				"Modules/Course", 
				'addSubscriber', 
				array(
					'obj_id' => $this->getObjId(),
					'usr_id' => $a_usr_id
				)
			);
	}

	/**
	 * Update passed status
	 *
	 * @access public
	 * @param int $usr_id
	 * @param bool $passed
	 * @param bool $a_manual
	 * @param bool $a_no_origin 
	 */
	public function updatePassed($a_usr_id, $a_passed, $a_manual = false, $a_no_origin = false)
	{				
		$this->participants_status[$a_usr_id]['passed'] = (int) $a_passed;

		return self::_updatePassed($this->obj_id, $a_usr_id, $a_passed, $a_manual, $a_no_origin);
	}
	
	/**
	 * Update passed status (static)
	 *
	 * @access public
	 * @param int $obj_id
	 * @param int $usr_id
	 * @param bool $passed
	 * @param bool $a_manual
	 * @param bool $a_no_origin
	 */
	public static function _updatePassed($a_obj_id, $a_usr_id, $a_passed, $a_manual = false, $a_no_origin = false)
	{
		global $ilDB, $ilUser;
		
		// #11600
		$origin = -1;
		if($a_manual)
		{
			$origin = $ilUser->getId();
		}		
		
		$query = "SELECT passed FROM obj_members ".
		"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
		"AND usr_id = ".$ilDB->quote($a_usr_id,'integer');
		$res = $ilDB->query($query);
		if($res->numRows())
		{
			// #9284 - only needs updating when status has changed
			$old = $ilDB->fetchAssoc($res);			
			if((int)$old["passed"] != (int)$a_passed)
			{			
				$query = "UPDATE obj_members SET ".
					"passed = ".$ilDB->quote((int) $a_passed,'integer').", ".
					"origin = ".$ilDB->quote($origin,'integer').", ".
					"origin_ts = ".$ilDB->quote(time(),'integer')." ".
					"WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
					"AND usr_id = ".$ilDB->quote($a_usr_id,'integer');
			}
		}
		else
		{
			// when member is added we should not set any date 
			// see ilObjCourse::checkLPStatusSync()
			if($a_no_origin && !$a_passed)
			{
				$origin = 0;
				$origin_ts = 0;
			}
			else
			{
				$origin_ts = time();
			}
			
			$query = "INSERT INTO obj_members (passed,obj_id,usr_id,notification,blocked,origin,origin_ts) ".
				"VALUES ( ".
				$ilDB->quote((int) $a_passed,'integer').", ".
				$ilDB->quote($a_obj_id,'integer').", ".
				$ilDB->quote($a_usr_id,'integer').", ".
				$ilDB->quote(0,'integer').", ".
				$ilDB->quote(0,'integer').", ".
				$ilDB->quote($origin,'integer').", ".
				$ilDB->quote($origin_ts,'integer').")";					
		}
		$res = $ilDB->manipulate($query);
		return true;	
	}
	
	/**
	 * Get info about passed status
	 * 
	 * @param int $a_usr_id
	 * @return array (user_id, timestamp) 	 
	 */
	function getPassedInfo($a_usr_id)
	{
		global $ilDB;
		
		$sql = "SELECT origin, origin_ts".
			" FROM obj_members".
			" WHERE obj_id = ".$ilDB->quote($this->obj_id, "integer").
			" AND usr_id = ".$ilDB->quote($a_usr_id, "integer");
		$set = $ilDB->query($sql);
		$row = $ilDB->fetchAssoc($set);		
		if($row["origin"])
		{
			return array("user_id" => $row["origin"],
				"timestamp" => new ilDateTime($row["origin_ts"], IL_CAL_UNIX));		
		}
	}		
	
	// Subscription
	function sendNotification($a_type, $a_usr_id)
	{
		include_once './Modules/Course/classes/class.ilCourseMembershipMailNotification.php';
		
		global $ilObjDataCache,$ilUser;
	
		switch($a_type)
		{
			case $this->NOTIFY_DISMISS_SUBSCRIBER:
				$mail = new ilCourseMembershipMailNotification();
				$mail->setType(ilCourseMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();				
				break;
				
			case $this->NOTIFY_ACCEPT_SUBSCRIBER:
				$mail = new ilCourseMembershipMailNotification();
				$mail->setType(ilCourseMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();				
				break;				

			case $this->NOTIFY_DISMISS_MEMBER:
				$mail = new ilCourseMembershipMailNotification();
				$mail->setType(ilCourseMembershipMailNotification::TYPE_DISMISS_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();
				break;

			case $this->NOTIFY_BLOCK_MEMBER:
				$mail = new ilCourseMembershipMailNotification();
				$mail->setType(ilCourseMembershipMailNotification::TYPE_BLOCKED_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();
				break;
				
			case $this->NOTIFY_UNBLOCK_MEMBER:
				$mail = new ilCourseMembershipMailNotification();
				$mail->setType(ilCourseMembershipMailNotification::TYPE_UNBLOCKED_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();
				break;

			case $this->NOTIFY_ACCEPT_USER:
				$mail = new ilCourseMembershipMailNotification();
				$mail->setType(ilCourseMembershipMailNotification::TYPE_ADMISSION_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();				
				break;

			case $this->NOTIFY_STATUS_CHANGED:
				$mail = new ilCourseMembershipMailNotification();
				$mail->setType(ilCourseMembershipMailNotification::TYPE_STATUS_CHANGED);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();				
				break;
				
			case $this->NOTIFY_UNSUBSCRIBE:
				$mail = new ilCourseMembershipMailNotification();
				$mail->setType(ilCourseMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();				
				break;
				
			case $this->NOTIFY_REGISTERED:
				$mail = new ilCourseMembershipMailNotification();
				$mail->setType(ilCourseMembershipMailNotification::TYPE_SUBSCRIBE_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();				
				break;

			case $this->NOTIFY_WAITING_LIST:
				include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
				$wl = new ilCourseWaitingList($this->obj_id);
				$pos = $wl->getPosition($a_usr_id);
					
				$mail = new ilCourseMembershipMailNotification();
				$mail->setType(ilCourseMembershipMailNotification::TYPE_WAITING_LIST_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->setAdditionalInformation(array('position' => $pos));
				$mail->send();
				break;

			case $this->NOTIFY_SUBSCRIPTION_REQUEST:
				$this->sendSubscriptionRequestToAdmins($a_usr_id);
				break;

			case $this->NOTIFY_ADMINS:
				$this->sendNotificationToAdmins($a_usr_id);
				return true;
				break;
		}
		return true;
	}
	
	function sendUnsubscribeNotificationToAdmins($a_usr_id)
	{
		global $ilDB,$ilObjDataCache;
		
		include_once './Modules/Course/classes/class.ilCourseMembershipMailNotification.php';
		$mail = new ilCourseMembershipMailNotification();
		$mail->setType(ilCourseMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE);
		$mail->setAdditionalInformation(array('usr_id' => $a_usr_id));
		$mail->setRefId($this->ref_id);
		$mail->setRecipients($this->getNotificationRecipients());
		$mail->send();
		return true;
	}
	
	
	public function sendSubscriptionRequestToAdmins($a_usr_id)
	{
		global $ilDB,$ilObjDataCache;
		
		include_once './Modules/Course/classes/class.ilCourseMembershipMailNotification.php';
		$mail = new ilCourseMembershipMailNotification();
		$mail->setType(ilCourseMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION_REQUEST);
		$mail->setAdditionalInformation(array('usr_id' => $a_usr_id));
		$mail->setRefId($this->ref_id);
		$mail->setRecipients($this->getNotificationRecipients());
		$mail->send();
		return true;
	}
	

	public function sendNotificationToAdmins($a_usr_id)
	{
		global $ilDB,$ilObjDataCache;
		
		include_once './Modules/Course/classes/class.ilCourseMembershipMailNotification.php';
		$mail = new ilCourseMembershipMailNotification();
		$mail->setType(ilCourseMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION);
		$mail->setAdditionalInformation(array('usr_id' => $a_usr_id));
		$mail->setRefId($this->ref_id);
		$mail->setRecipients($this->getNotificationRecipients());
		$mail->send();			
		return true;
	}
	
	
	function __buildStatusBody(&$user_obj)
	{
		global $ilDB;

		$body = $this->lng->txt('crs_status_changed_body')."\n";
		$body .= $this->lng->txt('login').': '.$user_obj->getLogin()."\n";
		$body .= $this->lng->txt('role').': ';

		if($this->isAdmin($user_obj->getId()))
		{
			$body .= $this->lng->txt('crs_admin')."\n";
		}
		if($this->isTutor($user_obj->getId()))
		{
			$body .= $this->lng->txt('crs_tutor')."\n";
		}
		if($this->isMember($user_obj->getId()))
		{
			$body .= $this->lng->txt('crs_member')."\n";
		}
		$body .= $this->lng->txt('status').': ';
		
		if($this->isNotificationEnabled($user_obj->getId()))
		{
			$body .= $this->lng->txt("crs_notify")."\n";
		}
		else
		{
			$body .= $this->lng->txt("crs_no_notify")."\n";
		}
		if($this->isBlocked($user_obj->getId()))
		{
			$body .= $this->lng->txt("crs_blocked")."\n";
		}
		else
		{
			$body .= $this->lng->txt("crs_unblocked")."\n";
		}
		$passed = $this->hasPassed($user_obj->getId()) ? $this->lng->txt('yes') : $this->lng->txt('no');
		$body .= $this->lng->txt('crs_passed').': '.$passed."\n";

		return $body;
	}
	
	public static function getDateTimeOfPassed($a_obj_id, $a_usr_id)
	{
		global $ilDB;
		
		$sql = "SELECT origin_ts FROM obj_members".
			" WHERE usr_id = ".$ilDB->quote($a_usr_id, "integer").
			" AND obj_id = ".$ilDB->quote($a_obj_id, "integer").
			" AND passed = ".$ilDB->quote(1, "integer");
		$res = $ilDB->query($sql);
		$res = $ilDB->fetchAssoc($res);
		if($res["origin_ts"])
		{
			return date("Y-m-d H:i:s", $res["origin_ts"]);
		}				
	}	
	
	public static function getPassedUsersForObjects(array $a_obj_ids, array $a_usr_ids)
	{
		global $ilDB;
		
		$res = array();
		
		$sql = "SELECT usr_id,obj_id FROM obj_members".
			" WHERE ".$ilDB->in("usr_id", $a_usr_ids, "", "integer").
			" AND ".$ilDB->in("obj_id", $a_obj_ids, "", "integer").
			" AND passed = ".$ilDB->quote(1, "integer");		
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row;
		}				
		
		return $res;
	}
}

?>