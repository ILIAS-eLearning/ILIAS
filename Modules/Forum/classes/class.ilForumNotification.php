<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilForumNotification
*
* @author Nadia Matuschek <nmatuschek@databay.de>
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
    private $db;
    private $user;
    
    
    /**
     * Constructor
     * @access	public
     */
    public function __construct($ref_id)
    {
        global $DIC;
        
        $this->lng = $DIC->language();
        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->ref_id = $ref_id;
        $this->forum_id = $DIC['ilObjDataCache']->lookupObjId($ref_id);
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
        $res = $this->db->queryF(
            '
			SELECT admin_force_noti FROM frm_notification
			WHERE user_id = %s 
			AND frm_id = %s
			AND user_id_noti > %s ',
            array('integer','integer', 'integer'),
            array($this->getUserId(), $this->getForumId(), 0)
        );
            
        while ($row = $this->db->fetchAssoc($res)) {
            return $row['admin_force_noti'];
        }
    }
    
    public function isUserToggleNotification()
    {
        $res = $this->db->queryF(
            '
			SELECT user_toggle_noti FROM frm_notification
			WHERE user_id = %s 
			AND frm_id = %s
			AND user_id_noti > %s',
            array('integer', 'integer', 'integer'),
            array($this->getUserId(), $this->getForumId(), 0 )
        );
                
        while ($row = $this->db->fetchAssoc($res)) {
            return $row['user_toggle_noti'];
        }
    }
    
    public function insertAdminForce()
    {
        $next_id = $this->db->nextId('frm_notification');
        $this->db->manipulateF(
            '
			INSERT INTO frm_notification
				(notification_id, user_id, frm_id, admin_force_noti, user_toggle_noti, user_id_noti)
			VALUES(%s,%s,%s,%s,%s,%s)',
            array('integer', 'integer', 'integer', 'integer', 'integer', 'integer'),
            array($next_id, $this->getUserId(), $this->getForumId(), $this->getAdminForce(), $this->getUserToggle(), $this->user->getId())
        );
    }
    
    public function deleteAdminForce()
    {
        $this->db->manipulateF(
            '
			DELETE FROM frm_notification
			WHERE 	user_id = %s
			AND		frm_id = %s 
			AND		admin_force_noti = %s 
			AND		user_id_noti > %s' ,
            array('integer', 'integer','integer', 'integer'),
            array($this->getUserId(), $this->getForumId(), 1, 0)
        );
    }

    public function deleteUserToggle()
    {
        $this->db->manipulateF(
            '
			DELETE FROM frm_notification
			WHERE 	user_id = %s
			AND		frm_id = %s 
			AND		admin_force_noti = %s 
			AND		user_toggle_noti = %s			
			AND		user_id_noti > %s' ,
            array('integer', 'integer','integer','integer', 'integer'),
            array($this->getUserId(),$this->getForumId(),1,1, 0 )
        );
    }
    
    public function updateUserToggle()
    {
        $this->db->manipulateF(
            '
			UPDATE frm_notification 
			SET user_toggle_noti = %s
			WHERE user_id = %s
			AND frm_id = %s
			AND admin_force_noti = %s',
            array('integer','integer','integer','integer'),
            array($this->getUserToggle(), $this->getUserId(),$this->getForumId(), 1)
        );
    }
    
    /* If a new member enters a Course or a Group, this function checks
     * if this CRS/GRP contains a forum and a notification setting set by admin or moderator
     * and inserts the new member into frm_notification
     * */
    public static function checkForumsExistsInsert($ref_id, $user_id = 0)
    {
        global $DIC;
        $ilUser = $DIC->user();

        $node_data = self::getCachedNodeData($ref_id);
        
        foreach ($node_data as $data) {
            //check frm_properties if frm_noti is enabled
            $frm_noti = new ilForumNotification($data['ref_id']);
            if ($user_id != 0) {
                $frm_noti->setUserId($user_id);
            } else {
                $frm_noti->setUserId($ilUser->getId());
            }
                    
            $admin_force = ilForumProperties::_isAdminForceNoti($data['obj_id']);
            $frm_noti->setAdminForce($admin_force);
    
            $user_toggle = ilForumProperties::_isUserToggleNoti($data['obj_id']);
            if ($user_toggle) {
                $frm_noti->setAdminForce(1);
            }
            
            if ($admin_force == 1 || $user_toggle == 1) {
                $frm_noti->setUserToggle($user_toggle);
                $frm_noti->setForumId($data['obj_id']);
                if ($frm_noti->existsNotification() == false) {
                    $frm_noti->insertAdminForce();
                }
            }
        }
    }
    
    public static function checkForumsExistsDelete($ref_id, $user_id = 0)
    {
        global $DIC;
        $ilUser = $DIC->user();

        $node_data = self::getCachedNodeData($ref_id);

        foreach ($node_data as $data) {
            //check frm_properties if frm_noti is enabled
            $frm_noti = new ilForumNotification($data['ref_id']);
            $objFrmMods = new ilForumModerators($data['ref_id']);
            $moderator_ids = $objFrmMods->getCurrentModerators();
            
            if ($user_id != 0) {
                $frm_noti->setUserId($user_id);
            } else {
                $frm_noti->setUserId($ilUser->getId());
            }

            $frm_noti->setForumId($data['obj_id']);
            if (!in_array($frm_noti->getUserId(), $moderator_ids)) {
                $frm_noti->deleteAdminForce();
            }
        }
    }

    /**
     * @param $ref_id
     * @return mixed
     */
    public static function getCachedNodeData($ref_id)
    {
        if (!array_key_exists($ref_id, self::$node_data_cache)) {
            global $DIC;
            self::$node_data_cache[$ref_id] = $DIC->repositoryTree()->getChildsByType($ref_id, 'frm');
        }
        
        return self::$node_data_cache[$ref_id];
    }
    
    /**
     * @param $a_ref_id
     * @return bool
     */
    public static function _isParentNodeGrpCrs($a_ref_id)
    {
        global $DIC;
        
        $parent_ref_id = $DIC->repositoryTree()->getParentId($a_ref_id);
        $parent_obj = ilObjectFactory::getInstanceByRefId($parent_ref_id);

        if ($parent_obj->getType() == 'crs' || $parent_obj->getType() == 'grp') {
            return $parent_obj->getType();
        } else {
            return false;
        }
    }
    
    /**
     * @param $a_parameter
     */
    public static function _clearForcedForumNotifications($a_parameter)
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilObjDataCache = $DIC['ilObjDataCache'];
         
        if (!$a_parameter['tree'] == 'tree') {
            return;
        }
        
        $ref_id = $a_parameter['source_id'];
        $is_parent = self::_isParentNodeGrpCrs($ref_id);
        
        if ($is_parent) {
            $forum_id = $ilObjDataCache->lookupObjId($ref_id);

            $ilDB->manipulateF(
                '
				DELETE FROM frm_notification 
				WHERE frm_id = %s 
				AND admin_force_noti = %s',
                array('integer','integer'),
                array($forum_id, 1)
            );
        }
    }
    
    /**
     * @param $ref_id
     * @return array
     */
    public static function checkParentNodeTree($ref_id)
    {
        global $DIC;
        $tree = $DIC->repositoryTree();
        
        $parent_ref_id = $tree->getParentId($ref_id);
        $parent_obj = ilObjectFactory::getInstanceByRefId($parent_ref_id);

        if ($parent_obj->getType() == 'crs') {
            $oParticipants = ilCourseParticipants::_getInstanceByObjId($parent_obj->getId());
        } elseif ($parent_obj->getType() == 'grp') {
            $oParticipants = ilGroupParticipants::_getInstanceByObjId($parent_obj->getId());
        }
        
        $result = array();
        if ($parent_obj->getType() == 'crs' || $parent_obj->getType() == 'grp') {
            $moderator_ids = ilForum::_getModerators($ref_id);
            $admin_ids = $oParticipants->getAdmins();
            $tutor_ids = $oParticipants->getTutors();
            
            $result = array_unique(array_merge($moderator_ids, $admin_ids, $tutor_ids));
        }
        return $result;
    }

    public function update()
    {
        $this->db->manipulateF(
            '
			UPDATE frm_notification
			SET admin_force_noti = %s,
				user_toggle_noti = %s
				WHERE user_id = %s
				AND frm_id = %s',
            array('integer','integer','integer','integer'),
            array($this->getAdminForce(), $this->getUserToggle(), $this->getUserId(), $this->getForumId())
        );
    }

    public function deleteNotificationAllUsers()
    {
        $this->db->manipulateF(
            '
			DELETE FROM frm_notification
			WHERE frm_id = %s
			AND user_id_noti > %s',
            array('integer', 'integer'),
            array($this->getForumId(), 0)
        );
    }

    public function read()
    {
        $result = array();
    
        $query = $this->db->queryF(
            '
			SELECT * FROM frm_notification WHERE
			frm_id = %s',
            array('integer'),
            array($this->getForumId())
        );

        while ($row = $this->db->fetchAssoc($query)) {
            $result[$row['user_id']] = $row;
        }
        return $result;
    }
    
    /**
     * @param $merge_source_thread_id
     * @param $merge_target_thread_id
     */
    public static function mergeThreadNotificiations($merge_source_thread_id, $merge_target_thread_id)
    {
        // check notifications etc..
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT notification_id, user_id FROM frm_notification 
		WHERE frm_id = %s 
		AND  thread_id = %s
		 ORDER BY user_id ASC',
            array('integer', 'integer'),
            array(0, $merge_source_thread_id)
        );

        $res_2 = $ilDB->queryF(
            'SELECT user_id FROM frm_notification 
		WHERE frm_id = %s 
		AND  thread_id = %s
		 ORDER BY user_id ASC',
            array('integer', 'integer'),
            array(0, $merge_target_thread_id)
        );

        $users_already_notified = $ilDB->fetchAssoc($res_2);
        while ($row = $ilDB->fetchAssoc($res)) {
            if (in_array($row['user_id'], $users_already_notified)) {
                // delete source notification because already exists for target_id
                $ilDB->manipulatef(
                    'DELETE FROM frm_notification WHERE notification_id = %s',
                    array('integer'),
                    array($row['notification_id'])
                );
            } else {
                // update source notification
                $ilDB->update(
                    'frm_notification',
                    array('thread_id' => array('integer', $merge_target_thread_id)),
                    array('thread_id' => array('integer', $merge_source_thread_id)
                    )
                );
            }
        }
    }

    /**
     * @return bool
     */
    public function existsNotification()
    {
        $res = $this->db->queryF(
            '
			SELECT * FROM frm_notification 
			WHERE user_id = %s
			AND frm_id = %s 
			AND admin_force_noti = %s',
            array('integer', 'integer', 'integer'),
            array($this->getUserId(), $this->getForumId(), $this->getAdminForce())
        );

        if ($row = $this->db->numRows($res) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param int $sourceRefId
     */
    public function cloneFromSource(int $sourceRefId)
    {
        $sourceNotificationSettings = new self($sourceRefId);
        $records = $sourceNotificationSettings->read();

        foreach ($records as $usrId => $row) {
            $this->setUserId($usrId);
            $this->setAdminForce($row['admin_force_noti']);
            $this->setUserToggle($row['user_toggle_noti']);
            $this->setUserIdNoti($row['user_id_noti']);

            $this->insertAdminForce();
        }
    }
}
