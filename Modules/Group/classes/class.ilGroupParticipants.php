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
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesGroup
*/


class ilGroupParticipants extends ilParticipants
{
	const COMPONENT_NAME = 'Modules/Group';
	
	protected static $instances = array();

	/**
	 * Constructor
	 *
	 * @access protected
	 * @param int obj_id of container
	 */
	public function __construct($a_obj_id)
	{
		$this->type = 'grp';
		parent::__construct(self::COMPONENT_NAME,$a_obj_id);
	}
	
	/**
	 * Get singleton instance
	 *
	 * @access public
	 * @static
	 *
	 * @param int obj_id
	 * @return object ilGroupParticipants
	 */
	public static function _getInstanceByObjId($a_obj_id)
	{
		if(isset(self::$instances[$a_obj_id]) and self::$instances[$a_obj_id])
		{
			return self::$instances[$a_obj_id];
		}
		return self::$instances[$a_obj_id] = new ilGroupParticipants($a_obj_id);
	}
	
	/**
	 * Get member roles (not auto generated)
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
				case 'il_grp_a':
				case 'il_grp_m':
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

		$ilLog->write(__METHOD__.': Raise new event: Modules/Group addSubscriber');
		$ilAppEventHandler->raise(
				"Modules/Group", 
				'addSubscriber', 
				array(
					'obj_id' => $this->getObjId(),
					'usr_id' => $a_usr_id
				)
			);
	}
	
		
	
	/**
	 * Static function to check if a user is a participant of the container object
	 *
	 * @access public
	 * @param int ref_id
	 * @param int user id
	 * @static
	 */
	public static function _isParticipant($a_ref_id,$a_usr_id)
	{
		global $rbacreview,$ilObjDataCache,$ilDB,$ilLog;

		$local_roles = $rbacreview->getRolesOfRoleFolder($a_ref_id,false);
        return $rbacreview->isAssignedToAtLeastOneGivenRole($a_usr_id, $local_roles);
	}
	
	/**
	 * Send notification mail
	 * @param int $a_type
	 * @param int $a_usr_id
	 * @return 
	 */
	public function sendNotification($a_type,$a_usr_id)
	{
		include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
		switch($a_type)
		{
			case ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER:

				$mail = new ilGroupMembershipMailNotification();
				$mail->setType(ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();
				break;
			
			case ilGroupMembershipMailNotification::TYPE_DISMISS_MEMBER:

				$mail = new ilGroupMembershipMailNotification();
				$mail->setType(ilGroupMembershipMailNotification::TYPE_DISMISS_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();
				break;
				
			case ilGroupMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION:
				
				$mail = new ilGroupMembershipMailNotification();
				$mail->setType(ilGroupMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION);
				$mail->setAdditionalInformation(array('usr_id' => $a_usr_id));
				$mail->setRefId($this->ref_id);
				$mail->setRecipients($this->getNotificationRecipients());
				$mail->send();
				break;		
				
			case ilGroupMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER:
				
				$mail = new ilGroupMembershipMailNotification();
				$mail->setType(ilGroupMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();				
				break;
				
			case ilGroupMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE:
					
				$mail = new ilGroupMembershipMailNotification();
				$mail->setType(ilGroupMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE);
				$mail->setAdditionalInformation(array('usr_id' => $a_usr_id));
				$mail->setRefId($this->ref_id);
				$mail->setRecipients($this->getNotificationRecipients());
				$mail->send();
				break;

			case ilGroupMembershipMailNotification::TYPE_SUBSCRIBE_MEMBER:
				
				$mail = new ilGroupMembershipMailNotification();
				$mail->setType(ilGroupMembershipMailNotification::TYPE_SUBSCRIBE_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();				
				break;
				
			case ilGroupMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION_REQUEST:

				$mail = new ilGroupMembershipMailNotification();
				$mail->setType(ilGroupMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION_REQUEST);
				$mail->setAdditionalInformation(array('usr_id' => $a_usr_id));
				$mail->setRefId($this->ref_id);
				$mail->setRecipients($this->getNotificationRecipients());
				$mail->send();
				break;
				
			case ilGroupMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER:

				$mail = new ilGroupMembershipMailNotification();
				$mail->setType(ilGroupMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();				
				break;
				
			case ilGroupMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER:
				
				$mail = new ilGroupMembershipMailNotification();
				$mail->setType(ilGroupMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();				
				break;	
			
			case ilGroupMembershipMailNotification::TYPE_WAITING_LIST_MEMBER:
				
				include_once('./Modules/Group/classes/class.ilGroupWaitingList.php');
				$wl = new ilGroupWaitingList($this->obj_id);
				$pos = $wl->getPosition($a_usr_id);
					
				$mail = new ilGroupMembershipMailNotification();
				$mail->setType(ilGroupMembershipMailNotification::TYPE_WAITING_LIST_MEMBER);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->setAdditionalInformation(array('position' => $pos));
				$mail->send();
				break;
				
			case ilGroupMembershipMailNotification::TYPE_STATUS_CHANGED:

				$mail = new ilGroupMembershipMailNotification();
				$mail->setType(ilGroupMembershipMailNotification::TYPE_STATUS_CHANGED);	
				$mail->setRefId($this->ref_id);
				$mail->setRecipients(array($a_usr_id));
				$mail->send();				
				break;

			
		}
		return true;
	}
	
	
}
?>