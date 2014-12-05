<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/Forum/classes/class.ilObjForum.php';


/**
* Class ilForumNotification
*
* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id:$
*
* @ingroup ModulesForum
*/
class ilForumNotification
{
	protected static $node_data_cache = array();
	
	private $notification_id;
	private $user_id;
	private $forum_id;
	private $thread_id;
	private $admin_force;
	private $user_toggle;
	
	private $ref_id;
	
	
	
	/**
	 * Constructor
	 * @access	public
	 */
	public function __construct($ref_id)
	{
		global $ilObjDataCache,$lng,$ilias;

		$this->lng = $lng;
		$this->ilias = $ilias;
		$this->ref_id = $ref_id;
		$this->forum_id = $ilObjDataCache->lookupObjId($ref_id);
		
	}

	public function setNotificationId($a_notification_id)
	{
		$this->notification_id = $a_notification_id;
	}
	public function getNotificationId()
	{
		return $this->notification_id;
	}
	public function setUserId($a_user_id)
	{
		$this->user_id = $a_user_id;
	}
	public function getUserId()
	{
		return $this->user_id;
	}	
	
	public function setForumId($a_forum_id)
	{
		$this->forum_id = $a_forum_id;
	}
	public function getForumId()
	{
		return $this->forum_id;
	}		
	
	public function setThreadId($a_thread_id)
	{
		$this->thread_id = $a_thread_id;
	}
	public function getThreadId()
	{
		return $this->thread_id;
	}		
	
	
	public function setAdminForce($a_admin_force)
	{
		$this->admin_force = $a_admin_force;
	}
	public function getAdminForce()
	{
		return $this->admin_force;
	}		
	
	
	public function setUserToggle($a_user_toggle)
	{
		$this->user_toggle = $a_user_toggle;
	}
	public function getUserToggle()
	{
		return $this->user_toggle;
	}		

	public function setForumRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}
	public function getForumRefId()
	{
		return $this->ref_id;
	}
	
	//user_id of who sets the setting to notify members 
	public function setUserIdNoti($a_user_id_noti)
	{
		$this->user_id_noti = $a_user_id_noti;
	}
	//user_id of who sets the setting to notify members 
	public function getUserIdNoti()
	{
		return $this->user_id_noti;
	}
	
	public function isAdminForceNotification()
	{
		global $ilDB;

		$res = $ilDB->queryF('
			SELECT admin_force_noti FROM frm_notification
			WHERE user_id = %s 
			AND frm_id = %s
			AND user_id_noti > %s ',
		array('integer','integer', 'integer'),
		array($this->getUserId(), $this->getForumId(), 0));
			
		while($row = $ilDB->fetchAssoc($res))
		{
			return $row['admin_force_noti'];
		}
	}
	public function isUserToggleNotification()
	{
		global $ilDB;
	
		$res = $ilDB->queryF('
			SELECT user_toggle_noti FROM frm_notification
			WHERE user_id = %s 
			AND frm_id = %s
			AND user_id_noti > %s',
		array('integer', 'integer', 'integer'),
		array($this->getUserId(), $this->getForumId(), 0 ));
				
		while($row = $ilDB->fetchAssoc($res))
		{
			return $row['user_toggle_noti'];
		}
				
	}
	
	public function insertAdminForce()
	{
		global $ilDB, $ilUser;

		$next_id = $ilDB->nextId('frm_notification');
		$res = $ilDB->manipulateF('
			INSERT INTO frm_notification
				(notification_id, user_id, frm_id, admin_force_noti, user_toggle_noti, user_id_noti)
			VALUES(%s,%s,%s,%s,%s,%s)',
		array('integer', 'integer', 'integer', 'integer', 'integer', 'integer'),
		array($next_id, $this->getUserId(), $this->getForumId(), $this->getAdminForce(), $this->getUserToggle(), $ilUser->getId()));

	}
	public function deleteAdminForce()
	{
		global $ilDB;
		
		$res = $ilDB->manipulateF('
			DELETE FROM frm_notification
			WHERE 	user_id = %s
			AND		frm_id = %s 
			AND		admin_force_noti = %s 
			AND		user_id_noti > %s' ,
			array('integer', 'integer','integer', 'integer'),
			array($this->getUserId(), $this->getForumId(), 1, 0));
	}

	public function deleteUserToggle()
	{
		global $ilDB, $ilUser;

		$res = $ilDB->manipulateF('
			DELETE FROM frm_notification
			WHERE 	user_id = %s
			AND		frm_id = %s 
			AND		admin_force_noti = %s 
			AND		user_toggle_noti = %s			
			AND		user_id_noti > %s' ,
			array('integer', 'integer','integer','integer', 'integer'),
			array($this->getUserId(),$this->getForumId(),1,1, 0 )); 
		
	}
	
	public function updateUserToggle()
	{
		global $ilDB;
		
		$res = $ilDB->manipulateF('
			UPDATE frm_notification 
			SET user_toggle_noti = %s
			WHERE user_id = %s
			AND frm_id = %s
			AND admin_force_noti = %s',
		array('integer','integer','integer','integer'),
 		array($this->getUserToggle(), $this->getUserId(),$this->getForumId(), 1));	
	}
	
	/* If a new member enters a Course or a Group, this function checks
	 * if this CRS/GRP contains a forum and a notification setting set by admin or moderator
	 * and inserts the new member into frm_notification   
	 * */
	public static function checkForumsExistsInsert($ref_id, $user_id = 0)
	{
		global $ilUser;
			
		include_once 'Modules/Forum/classes/class.ilForumProperties.php';
		
		$node_data = self::getCachedNodeData($ref_id);
		
		foreach($node_data as $data)
		{
			//check frm_properties if frm_noti is enabled
			$frm_noti = new ilForumNotification($data['ref_id']);
			if($user_id != 0)
			{
				$frm_noti->setUserId($user_id);
			}
			else $frm_noti->setUserId($ilUser->getId());
					
			$admin_force = ilForumProperties::_isAdminForceNoti($data['obj_id']);
			$frm_noti->setAdminForce($admin_force);
	
			$user_toggle = ilForumProperties::_isUserToggleNoti($data['obj_id']);
			if($user_toggle) $frm_noti->setAdminForce(1);
			
			if($admin_force == 1 || $user_toggle == 1)
			{
				$frm_noti->setUserToggle($user_toggle);
				$frm_noti->setForumId($data['obj_id']);
				if($frm_noti->existsNotification() == false)
				{
					$frm_noti->insertAdminForce();	
				}
			}
		}
	}
	
	public static function checkForumsExistsDelete($ref_id, $user_id = 0)
	{
		global $ilUser;

		$node_data = self::getCachedNodeData($ref_id);
		
		include_once 'Modules/Forum/classes/class.ilForumModerators.php';
		
		foreach($node_data as $data)
		{
			//check frm_properties if frm_noti is enabled
			$frm_noti = new ilForumNotification($data['ref_id']);
			$objFrmMods = new ilForumModerators($data['ref_id']);
			$moderator_ids = $objFrmMods->getCurrentModerators();
			
			if($user_id != 0)
			{
				$frm_noti->setUserId($user_id);
			}
			else $frm_noti->setUserId($ilUser->getId());

			$frm_noti->setForumId($data['obj_id']);
			if(!in_array($frm_noti->getUserId(), $moderator_ids))
			{
				$frm_noti->deleteAdminForce();	
			}
		}
	}

	/**
	 * @param $ref_id
	 */
	public static function getCachedNodeData($ref_id)
	{
		if(!array_key_exists($ref_id, self::$node_data_cache))
		{
			global $tree;
			self::$node_data_cache[$ref_id] = $tree->getChildsByType($ref_id, 'frm');
		}
		
		return self::$node_data_cache[$ref_id];
	}
	
	public static function _isParentNodeGrpCrs($a_ref_id)
	{
		global $tree;
    	
		$parent_ref_id = $tree->getParentId($a_ref_id);
		$parent_obj = ilObjectFactory::getInstanceByRefId($parent_ref_id);

		if($parent_obj->getType() == 'crs' || $parent_obj->getType() == 'grp')
		return $parent_obj->getType();
		else return false;		
	}   	
	
	
 	public static function _clearForcedForumNotifications($a_parameter)
 	{
 		global  $ilDB, $ilObjDataCache;
		 
		if(!$a_parameter['tree'] == 'tree')
		{
			return;
		}
		
		$ref_id = $a_parameter['source_id'];
		$is_parent = self::_isParentNodeGrpCrs($ref_id);
		
		if($is_parent)
		{
			$forum_id = $ilObjDataCache->lookupObjId($ref_id);

			$ilDB->manipulateF('
				DELETE FROM frm_notification 
				WHERE frm_id = %s 
				AND admin_force_noti = %s',
			array('integer','integer'),
			array($forum_id, 1)); 	
		}
 	}
	
	public static function checkParentNodeTree($ref_id)
    {
    	global $tree;
    	
		$parent_ref_id = $tree->getParentId($ref_id);
		$parent_obj = ilObjectFactory::getInstanceByRefId($parent_ref_id);

		if($parent_obj->getType() == 'crs')
		{
			include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
			$oParticipants = ilCourseParticipants::_getInstanceByObjId($parent_obj->getId());				
		}
		else if($parent_obj->getType() == 'grp')
		{
			include_once 'Modules/Group/classes/class.ilGroupParticipants.php';
			$oParticipants = ilGroupParticipants::_getInstanceByObjId($parent_obj->getId());
		}
		
		$result = array();
		if($parent_obj->getType() == 'crs' || $parent_obj->getType() == 'grp')
		{
			$moderator_ids = self::_getModerators($ref_id);
			$admin_ids = $oParticipants->getAdmins();
			$tutor_ids = $oParticipants->getTutors();
			
			$result = array_unique(array_merge($moderator_ids,$admin_ids,$tutor_ids));
		}
		return $result;
   }
	
	/**
	* get all users assigned to local role il_frm_moderator_<frm_ref_id> (static)
	*
	* @param	int		$a_ref_id	reference id
	* @return	array	user_ids
	* @access	public
	*/
	public function _getModerators($a_ref_id)
	{
		global $rbacreview;

		$role_arr  = $rbacreview->getRolesOfRoleFolder($a_ref_id);

		foreach ($role_arr as $role_id)
		{
			//$roleObj = $this->ilias->obj_factory->getInstanceByObjId($role_id);
			$title = ilObject::_lookupTitle($role_id);
			if ($title == "il_frm_moderator_".$a_ref_id)			
			{
				#return $rbacreview->assignedUsers($roleObj->getId());
				return $title = $rbacreview->assignedUsers($role_id);
			}
		}

		return array();
	}

	public function update()
	{
		global $ilDB;

		$res = $ilDB->manipulateF('
			UPDATE frm_notification
			SET admin_force_noti = %s,
				user_toggle_noti = %s
				WHERE user_id = %s
				AND frm_id = %s',
			array('integer','integer','integer','integer'),
			array($this->getAdminForce(), $this->getUserToggle(), $this->getUserId(), $this->getForumId()));
	}

	public function deleteNotificationAllUsers()
	{
		global $ilDB;

		$res = $ilDB->manipulateF('
			DELETE FROM frm_notification
			WHERE frm_id = %s
			AND user_id_noti > %s',
			array('integer', 'integer'),
			array($this->getForumId(), 0));
	}

	public function read()
	{
		global $ilDB;
		$result = array();
	
		$query = $ilDB->queryF('
			SELECT * FROM frm_notification WHERE
			frm_id = %s',
			array('integer'),
			array($this->getForumId()));

		while($row = $ilDB->fetchAssoc($query))
		{
			$result[$row['user_id']] = $row;
		}
		return $result;
	}
	
	public static function mergeThreadNotificiations($merge_source_thread_id, $merge_target_thread_id)
	{
		// check notifications etc..
		global $ilDB;

		$res = $ilDB->queryF('SELECT notification_id, user_id FROM frm_notification 
		WHERE frm_id = %s 
		AND  thread_id = %s
		 ORDER BY user_id ASC',
			array('integer', 'integer'), array(0, $merge_source_thread_id));

		$res_2 = $ilDB->queryF('SELECT user_id FROM frm_notification 
		WHERE frm_id = %s 
		AND  thread_id = %s
		 ORDER BY user_id ASC',
			array('integer', 'integer'), array(0, $merge_target_thread_id));

		$users_already_notified = $ilDB->fetchAssoc($res_2);
		while($row = $ilDB->fetchAssoc($res))
		{
			if(in_array($row['user_id'], $users_already_notified))
			{
				// delete source notification because already exists for target_id
				$ilDB->manipulatef('DELETE FROM frm_notification WHERE notification_id = %s',
					array('integer'), array($row['notification_id']));
			}
			else
			{
				// update source notification
				$ilDB->update('frm_notification',
					array('thread_id' => array('integer', $merge_target_thread_id)),
					array('thread_id' => array('integer', $merge_source_thread_id)
					));
			}
		}
	}

	/**
	 * @return bool
	 */
	public function existsNotification()
	{
		global $ilDB;

		$res = $ilDB->queryF('
			SELECT * FROM frm_notification 
			WHERE user_id = %s
			AND frm_id = %s 
			AND admin_force_noti = %s',
			array('integer', 'integer', 'integer'),
			array($this->getUserId(), $this->getForumId(), $this->getAdminForce()));

		if($row = $ilDB->numRows($res) > 0)
		{
			return true;
		}
		return false;
	}
	
} // END class.ilForumNotification
