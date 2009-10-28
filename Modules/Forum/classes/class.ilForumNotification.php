<?php
/*
 +-----------------------------------------------------------------------------+
 | ILIAS open source                                                           |
 +-----------------------------------------------------------------------------+
 | Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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


require_once './Modules/Forum/classes/class.ilObjForum.php';


/**
 * Class ilForumNotification
 *
 * @author Nadia Krzywon <nkrzywon@databay.de>
 * @version $Id:
 *
 * @ingroup ModulesForum
 */
class ilForumNotification
{

	var $user_id;
	var $forum_id;
	var $thread_id;
	var $admin_force;
	var $user_toggle;


	var $ref_id;



	/**
	 * Constructor
	 * @access	public
	 */
	function ilForumNotification($ref_id = 0)
	{
		global $ilObjDataCache,$lng,$ilias;

		$this->lng &= $lng;
		$this->ilias &= $ilias;
		$this->ref_id = $ref_id;
		$this->forum_id = $ilObjDataCache->lookupObjId($_GET['ref_id']);

	}

	function setUserId($a_user_id)
	{
		$this->user_id = $a_user_id;
	}
	function getUserId()
	{
		return $this->user_id;
	}

	function setForumId($a_forum_id)
	{
		$this->forum_id = $a_forum_id;
	}
	function getForumId()
	{
		return $this->forum_id;
	}

	function setThreadId($a_thread_id)
	{
		$this->thread_id = $a_thread_id;
	}

	function getThreadId()
	{
		return $this->thread_id;
	}

	function setAdminForce($a_admin_force)
	{
		$this->admin_force = $a_admin_force;
	}
	function getAdminForce()
	{
		return $this->admin_force;
	}

	function setUserToggle($a_user_toggle)
	{
		$this->user_toggle = $a_user_toggle;
	}
	function getUserToggle()
	{
		return $this->user_toggle;
	}

	function setForumRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}
	function getForumRefId()
	{
		return $this->ref_id;
	}

	//user_id of who sets the setting to notify members
	function setUserIdNoti($a_user_id_noti)
	{
		$this->user_id_noti = $a_user_id_noti;
	}
	//user_id of who sets the setting to notify members
	function getUserIdNoti()
	{
		return $this->user_id_noti;
	}

	function isAdminForceNotification()
	{
		global $ilDB;

		$res = $ilDB->queryF('SELECT admin_force_noti FROM frm_notification
			WHERE user_id = %s AND frm_id = %s AND user_id_noti',
		array('integer', 'integer'),
		array($this->getUserId(),$this->getForumId()));

		while($row = $ilDB->fetchAssoc($res))
		{
			return $row['admin_force_noti'];
		}


	}
	function isUserToggleNotification()
	{
		global $ilDB;

		$res = $ilDB->queryF('
			SELECT user_toggle_noti FROM frm_notification 
			WHERE user_id = %s AND frm_id = %s AND user_id_noti ',
		array('integer', 'integer'),
		array($this->getUserId(),$this->getForumId()));

		while($row = $ilDB->fetchAssoc($res))
		{
			return $row['user_toggle_noti'];
		}

	}

	function insertAdminForce()
	{
		global $ilDB, $ilUser;

		$nextid = $ilDB->nextId('frm-notification');
		$res = $ilDB->manipulateF('
		INSERT INTO frm_notification
			(notification_id, user_id, frm_id, admin_force_noti, user_toggle_noti, user_id_noti) 
		VALUES(%s,%s,%s,%s,%s,%s)',
		array('integer','integer', 'integer','integer','integer','integer'),
		array($nextid,$this->getUserId(),$this->getForumId(),$this->getAdminForce(),$this->getUserToggle(),$ilUser->getId()));

			
	}
	function deleteAdminForce()
	{
		global $ilDB;

		$res = $ilDB->manipulateF('
			DELETE FROM frm_notification
			WHERE 	user_id = %s
			AND		frm_id = %s 
			AND		admin_force_noti = %s
			AND		user_id_noti' ,
		array('integer', 'integer','integer'),
		array($this->getUserId(),$this->getForumId(),1));
	}

	function insertUserToggle()
	{
		global $ilDB, $ilUser;

		$nextid = $ilDB->nextId('frm-notification');
		$res = $ilDB->manipulateF('
			INSERT INTO frm_notification
				(notification_id,user_id, frm_id, admin_force_noti, user_toggle_noti, user_id_noti) 
			VALUES(%s,%s,%s,%s,%s,%s)',
		array('integer', 'integer','integer','integer','integer'),
		array($nextid,$this->getUserId(),$this->getForumId(),1,1,$ilUser->getId()));


	}
	function deleteUserToggle()
	{
		global $ilDB, $ilUser;

		$res = $ilDB->manipulateF('
			DELETE FROM frm_notification
			WHERE 	user_id = %s
			AND		frm_id = %s 
			AND		admin_force_noti = %s 
			AND		user_toggle_noti = %s 			
			AND		user_id_noti' ,
		array('integer', 'integer','integer'),
		array($this->getUserId(),$this->getForumId(),1,1));
			
	}

	function updateUserToggle()
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

	function getCrsGrpMemberToNotify()
	{
		global $ilDB;

	}


	/* If a new member enters a Course or a Group, this function checks
	 * if this CRS/GRP contains a forum and a notification setting set by admin or moderator
	 * and inserts the new member into frm_notification
	 * */
	static function checkForumsExistsInsert($ref_id, $user_id = 0)
	{
		global $tree, $ilObjDataCache, $ilUser;
			
		include 'Modules/Forum/classes/class.ilForumProperties.php';

		$sub_tree_types = $tree->getSubTreeTypes($ref_id);

		if(in_array('frm', $sub_tree_types))
		{
			$node_data = $tree->getNodeDataByType('frm');
		}

		$forum_id = $ilObjDataCache->lookupObjId($ref_id);

		foreach($node_data as $data)
		{
			if($data['parent'] == $ref_id)
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
					$frm_noti->insertAdminForce();
				}
			}
		}
	}

	static function checkForumsExistsDelete($ref_id, $user_id = 0)
	{
		global $tree, $ilObjDataCache, $ilUser;

		$sub_tree_types = $tree->getSubTreeTypes($ref_id);

		if(in_array('frm', $sub_tree_types))
		{
			$node_data = $tree->getNodeDataByType('frm');
		}

		$forum_id = $ilObjDataCache->lookupObjId($ref_id);

		foreach($node_data as $data)
		{
			if($data['parent'] == $ref_id)
			{
				//check frm_properties if frm_noti is enabled
				$frm_noti = new ilForumNotification($data['ref_id']);
				if($user_id != 0)
				{
					$frm_noti->setUserId($user_id);
				}
				else $frm_noti->setUserId($ilUser->getId());

				$frm_noti->setAdminForce(1);
				$frm_noti->setForumId($data['obj_id']);
				$frm_noti->deleteAdminForce();
			}
		}
	}

    static function checkParentNodeTree($ref_id)
    {
    	global $tree;
    	
		$parent_ref_id = $tree->getParentId($ref_id);
		$parent_obj = ilObjectFactory::getInstanceByRefId($parent_ref_id);

		if($parent_obj->getType() == 'crs')
		{
			include_once 'Modules/Course/classes/class.ilCourseParticipants.php';
			$oParticipants = ilCourseParticipants::_getInstanceByObjId($parent_obj->getId());
			include_once 'Modules/Course/classes/class.ilObjCourseListGUI.php';
			$objListGUI = new ilObjCourseListGUI($parent_obj);
				
		}
		else if($parent_obj->getType() == 'grp')
		{
			include_once 'Modules/Group/classes/class.ilGroupParticipants.php';
			$oParticipants = ilGroupParticipants::_getInstanceByObjId($parent_obj->getId());
			include_once 'Modules/Group/classes/class.ilObjGroupListGUI.php';
			$objListGUI = new ilObjGroupListGUI($parent_obj);
		}

		$result = array();
		$moderator_ids = self::_getModerators($ref_id);
		$admin_ids = $oParticipants->getAdmins();
		$tutor_ids = $oParticipants->getTutors();
		
		$result = array_unique(array_merge($moderator_ids,$admin_ids,$tutor_ids));

		return $result;
	
    }
 
	/*************************************** LATER ****************************************/
	/*
	 *
	 * many other classes contains code relevant for forumNotification ....
	 *
	 *	TODO:  copy existing forumnotification functions here and use forumNotification Class in future !!
	 *		- update existing functions in forum classes
	 *		- (ilForum, ilForumTopic, ilObjForum, maybe cronForumNotification too	)
	 *		-
	 *
	 */
	/**
	 * get content of given user-ID
	 *
	 * @param	integer $a_user_id: user-ID
	 * @return	object	user object
	 * @access	public
	 */
	function getUser($a_user_id)
	{
		$userObj = new ilObjUser($a_user_id);

		return $userObj;
	}

	/**
	 * get all users assigned to local role il_frm_moderator_<frm_ref_id>
	 *
	 * @return	array	user_ids
	 * @access	public
	 */
	function getModerators()
	{
		global $rbacreview;

		return $this->_getModerators($this->getForumRefId());
	}

	/**
	 * get all users assigned to local role il_frm_moderator_<frm_ref_id> (static)
	 *
	 * @param	int		$a_ref_id	reference id
	 * @return	array	user_ids
	 * @access	public
	 */
	function _getModerators($a_ref_id)
	{
		global $rbacreview;

		$rolf 	   = $rbacreview->getRoleFolderOfObject($a_ref_id);
		$role_arr  = $rbacreview->getRolesOfRoleFolder($rolf["ref_id"]);

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

	/**
	 * checks whether a user is moderator of a given forum object
	 *
	 * @param	int		$a_ref_id	reference id
	 * @param	int		$a_usr_id	user id
	 * @return	bool
	 * @access	public
	 */
	function _isModerator($a_ref_id, $a_usr_id)
	{
		return in_array($a_usr_id, ilForum::_getModerators($a_ref_id));
	}

} //
function __sendMessage($a_parent_pos, $post_data = array())
{
	global $ilUser, $ilDB;

	$parent_data = $this->getOnePost($a_parent_pos);

	// only if the current user is not the owner of the parent post and the parent's notification flag is set...
	if($parent_data["notify"] && $parent_data["pos_usr_id"] != $ilUser->getId())
	{
		// SEND MESSAGE
		include_once "Services/Mail/classes/class.ilMail.php";
		include_once './Services/User/classes/class.ilObjUser.php';

		$tmp_user =& new ilObjUser($parent_data["pos_usr_id"]);

		// NONSENSE
		$this->setMDB2WhereCondition('thr_pk = %s ', array('integer'), array($parent_data["pos_thr_fk"]));

		$thread_data = $this->getOneThread();

		$tmp_mail_obj = new ilMail(ANONYMOUS_USER_ID);
		$message = $tmp_mail_obj->sendMail($tmp_user->getLogin(),"","",
		$this->__formatSubject($thread_data),
		$this->__formatMessage($thread_data, $post_data),
		array(),array("system"));

		unset($tmp_user);
		unset($tmp_mail_obj);
	}
}

function __formatSubject($thread_data)
{
	return $this->lng->txt("forums_notification_subject");
}

function __formatMessage($thread_data, $post_data = array())
{
	include_once "./classes/class.ilObjectFactory.php";


	$frm_obj =& ilObjectFactory::getInstanceByRefId($this->getForumRefId());
	$title = $frm_obj->getTitle();
	unset($frm_obj);

	$message = $this->lng->txt("forum").": ".$title." -> ".$thread_data["thr_subject"]."\n\n";
	$message .= $this->lng->txt("forum_post_replied");

	$message .= "\n------------------------------------------------------------\n";
	$message .= $post_data["pos_message"];
	$message .= "\n------------------------------------------------------------\n";
	$message .= sprintf($this->lng->txt("forums_notification_show_post"), "http://".$_SERVER["HTTP_HOST"].dirname($_SERVER["PHP_SELF"])."/goto.php?target=frm_".$post_data["ref_id"]."_".$post_data["pos_thr_fk"].'&client_id='.CLIENT_ID);

	return $message;
}

function getUserData($a_id, $a_import_name = 0)
{
	global $lng, $ilDB;

	if($a_id && ilObject::_exists($a_id) && ilObjectFactory::getInstanceByObjId($a_id,false))
	{
		$res = $ilDB->queryf('
				SELECT * FROM usr_data WHERE usr_id = %s',
		array('integer'), array($a_id));
			
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$tmp_array["usr_id"] = $row->usr_id;
			$tmp_array["login"]  = $row->login;
			$tmp_array["firstname"]  = $row->firstname;
			$tmp_array["lastname"]  = $row->lastname;
			$tmp_array["public_profile"] = ilObjUser::_lookupPref($a_id, "public_profile");
			$tmp_array["create_date"]  = $row->create_date;
		}
		return $tmp_array ? $tmp_array : array();
	}
	else
	{
		$login = $a_import_name ? $a_import_name." (".$lng->txt("imported").")" : $lng->txt("unknown");

		return array("usr_id" => 0, "login" => $login, "firstname" => "", "lastname" => "");
	}
}
/**
 * Enable a user's notification about new posts in this forum
 * @param    integer	user_id	A user's ID
 * @return	bool	true
 * @access	private
 */
function enableForumNotification($user_id)
{
	global $ilDB;

	if (!$this->isForumNotificationEnabled($user_id))
	{
		/* Remove all notifications of threads that belong to the forum */

		$res = $ilDB->queryf('
				SELECT frm_notification.thread_id FROM frm_data, frm_notification, frm_threads 
				WHERE frm_notification.user_id = %s
				AND frm_notification.thread_id = frm_threads.thr_pk 
				AND frm_threads.thr_top_fk = frm_data.top_pk 
				AND frm_data.top_frm_fk = %s
				GROUP BY frm_notification.thread_id',
		array('integer', 'integer'),
		array($user_id, $this->id));
			
		if (is_object($res) && $res->numRows() > 0)
		{
			$thread_data = array();
			$thread_data_types = array();

			$query = ' DELETE FROM frm_notification
							WHERE user_id = %s 
							AND thread_id IN (';

			array_push($thread_data, $user_id);
			array_push($thread_data_types, 'integer');

			$counter = 1;

			while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if($counter < $res->numRows())
				{
					$query .= '%s, ';
					array_push($thread_data, $row['thread_id']);
					array_push($thread_data_types, 'integer');
				}
					
				if($counter == $res->numRows())
				{
					$query .= '%s)';
					array_push($thread_data, $row['thread_id']);
					array_push($thread_data_types, 'integer');

				}
				$counter++;
			}

			$statement = $ilDB->manipulateF($query, $thread_data_types, $thread_data);
		}

		/* Insert forum notification */

		$nextId = $ilDB->nextId('frm_notification');
			
		$statement = $ilDB->manipulateF('
				INSERT INTO frm_notification
				( 	notification_id,
					user_id, 
					frm_id
				)
				VALUES(%s, %s, %s)',
		array('integer','integer', 'integer'),
		array($nextId, $user_id, $this->id));

	}

	return true;
}

/**
 * Disable a user's notification about new posts in this forum
 * @param    integer	user_id	A user's ID
 * @return	bool	true
 * @access	private
 */
function disableForumNotification($user_id)
{
	global $ilDB;

	$statement = $ilDB->manipulateF('
			DELETE FROM frm_notification 
			WHERE user_id = %s
			AND frm_id = %s',
	array('integer', 'integer'),
	array($user_id, $this->id));

	return true;
}

/**
 * Check whether a user's notification about new posts in this forum is enabled (result > 0) or not (result == 0)
 * @param    integer	user_id	A user's ID
 * @return	integer	Result
 * @access	private
 */
function isForumNotificationEnabled($user_id)
{
	global $ilDB;

	$result = $ilDB->queryf('SELECT COUNT(*) cnt FROM frm_notification WHERE user_id = %s AND frm_id = %s',
	array('integer', 'integer'), array($user_id, $this->id));
		
	while($record = $ilDB->fetchAssoc($result))
	{
		return (bool)$record['cnt'];
	}

	return false;
}

/**
 * Enable a user's notification about new posts in a thread
 * @param    integer	user_id	A user's ID
 * @param    integer	thread_id	ID of the thread
 * @return	bool	true
 * @access	private
 */
function enableThreadNotification($user_id, $thread_id)
{
	global $ilDB;

	if (!$this->isThreadNotificationEnabled($user_id, $thread_id))
	{
		$nextId = $ilDB->nextId('frm_notification');
		$statement = $ilDB->manipulateF('
				INSERT INTO frm_notification
				(	notification_id,
					user_id,
					thread_id
				)
				VALUES (%s, %s, %s)',
		array('integer', 'integer', 'integer'), array($nextId, $user_id, $thread_id));
			
	}

	return true;
}

/**
 * Check whether a user's notification about new posts in a thread is enabled (result > 0) or not (result == 0)
 * @param    integer	user_id	A user's ID
 * @param    integer	thread_id	ID of the thread
 * @return	integer	Result
 * @access	private
 */
function isThreadNotificationEnabled($user_id, $thread_id)
{
	global $ilDB;

	$result = $ilDB->queryf('
			SELECT COUNT(*) cnt FROM frm_notification 
			WHERE user_id = %s 
			AND thread_id = %s',
	array('integer', 'integer'),
	array($user_id, $thread_id));


	while($record = $ilDB->fetchAssoc($result))
	{
		return (bool)$record['cnt'];
	}

	return false;
}

function sendThreadNotifications($post_data)
{
	global $ilDB, $ilAccess;

	include_once "Services/Mail/classes/class.ilMail.php";
	include_once './Services/User/classes/class.ilObjUser.php';

	// GET THREAD DATA
	$result = $ilDB->queryf('
			SELECT thr_subject FROM frm_threads 
			WHERE thr_pk = %s',
	array('integer'), array($post_data['pos_thr_fk']));
		
	while($record = $ilDB->fetchAssoc($result))
	{
		$post_data['thr_subject'] = $record['thr_subject'];
		break;
	}

	// determine obj_id of the forum
	$obj_id = self::_lookupObjIdForForumId($post_data['pos_top_fk']);

	// GET AUTHOR OF NEW POST
	if(ilForumProperties::getInstance($obj_id)->isAnonymized())
	{
		$post_data['pos_usr_name'] = $post_data['pos_usr_alias'];
	}
	else
	{
		$post_data['pos_usr_name'] = ilObjUser::_lookupLogin($post_data['pos_usr_id']);
	}
	if($post_data['pos_usr_name'] == '')
	{
		$post_data['pos_usr_name'] = $this->lng->txt('forums_anonymous');
	}

	// GET USERS WHO WANT TO BE INFORMED ABOUT NEW POSTS
	$res = $ilDB->queryf('
			SELECT user_id FROM frm_notification 
			WHERE thread_id = %s
			AND user_id <> %s',
	array('integer', 'integer'),
	array($post_data['pos_thr_fk'], $_SESSION['AccountId']));

	// get all references of obj_id
	$frm_references = ilObject::_getAllReferences($obj_id);

	$mail_obj = new ilMail(ANONYMOUS_USER_ID);
	while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
		// do rbac check before sending notification
		$send_mail = false;
		foreach((array)$frm_references as $ref_id)
		{
			if($ilAccess->checkAccessOfUser($row['user_id'], 'read', '', $ref_id))
			{
				$send_mail = true;
				break;
			}
		}
			
		if($send_mail)
		{
			// SEND NOTIFICATIONS BY E-MAIL
			$message = $mail_obj->sendMail(ilObjUser::_lookupLogin($row["user_id"]),"","",
			$this->formatNotificationSubject($post_data),
			$this->formatNotification($post_data),
			array(),array("system"));
		}
	}
}

function sendForumNotifications($post_data)
{
	global $ilDB, $ilAccess;

	include_once "Services/Mail/classes/class.ilMail.php";
	include_once './Services/User/classes/class.ilObjUser.php';

	// GET THREAD DATA
	$result = $ilDB->queryf('
			SELECT thr_subject FROM frm_threads 
			WHERE thr_pk = %s',
	array('integer'),
	array($post_data['pos_thr_fk']));
		
	while($record = $ilDB->fetchAssoc($result))
	{
		$post_data['thr_subject'] = $record['thr_subject'];
		break;
	}

	// determine obj_id of the forum
	$obj_id = self::_lookupObjIdForForumId($post_data['pos_top_fk']);

	// GET AUTHOR OF NEW POST
	if(ilForumProperties::getInstance()->isAnonymized())
	{
		$post_data['pos_usr_name'] = $post_data['pos_usr_alias'];
	}
	else
	{
		$post_data['pos_usr_name'] = ilObjUser::_lookupLogin($post_data['pos_usr_id']);
	}
	if($post_data['pos_usr_name'] == '')
	{
		$post_data['pos_usr_name'] = $this->lng->txt('forums_anonymous');
	}

	// GET USERS WHO WANT TO BE INFORMED ABOUT NEW POSTS
	$res = $ilDB->queryf('
			SELECT frm_notification.user_id FROM frm_notification, frm_data 
			WHERE frm_data.top_pk = %s
			AND frm_notification.frm_id = frm_data.top_frm_fk 
			AND frm_notification.user_id <> %s
			GROUP BY frm_notification.user_id',
	array('integer', 'integer'),
	array($post_data['pos_top_fk'], $_SESSION['AccountId']));


	// get all references of obj_id
	$frm_references = ilObject::_getAllReferences($obj_id);

	$mail_obj = new ilMail(ANONYMOUS_USER_ID);
	while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
	{
		// do rbac check before sending notification
		$send_mail = false;
		foreach((array)$frm_references as $ref_id)
		{
			if($ilAccess->checkAccessOfUser($row['user_id'], 'read', '', $ref_id))
			{
				$send_mail = true;
				break;
			}
		}
			
		if($send_mail)
		{
			// SEND NOTIFICATIONS BY E-MAIL
			$message = $mail_obj->sendMail(ilObjUser::_lookupLogin($row["user_id"]),"","",
			$this->formatNotificationSubject($post_data),
			$this->formatNotification($post_data),
			array(),array("system"));
				
		}
	}
}

function formatPostActivationNotificationSubject()
{
	return $this->lng->txt('forums_notification_subject');
}

function formatPostActivationNotification($post_data)
{
	$message = sprintf($this->lng->txt('forums_notification_intro'),
	$this->ilias->ini->readVariable('client', 'name'),
	ILIAS_HTTP_PATH.'/?client_id='.CLIENT_ID)."\n\n";

	$message .= $this->lng->txt("forum").": ".$post_data["top_name"]."\n\n";
	$message .= $this->lng->txt("thread").": ".$post_data["thr_subject"]."\n\n";
	$message .= $this->lng->txt("new_post").":\n------------------------------------------------------------\n";
	$message .= $this->lng->txt("author").": ".$post_data["pos_usr_name"]."\n";
	$message .= $this->lng->txt("date").": ".$post_data["pos_date"]."\n";
	$message .= $this->lng->txt("subject").": ".$post_data["pos_subject"]."\n\n";
	if ($post_data["pos_cens"] == 1)
	{
		$message .= $post_data["pos_cens_com"]."\n";
	}
	else
	{
		$message .= $post_data["pos_message"]."\n";
	}
	$message .= "------------------------------------------------------------\n";

	$message .= sprintf($this->lng->txt('forums_notification_show_post'), ILIAS_HTTP_PATH."/goto.php?target=frm_".$post_data["ref_id"]."_".$post_data["pos_thr_fk"]."_".$post_data["pos_pk"].'&client_id='.CLIENT_ID);


	return $message;
}

function sendPostActivationNotification($post_data)
{
	global $ilDB, $ilUser;

	if (is_array($moderators = $this->getModerators()))
	{
		// GET THREAD DATA
		$result = $ilDB->queryf('
				SELECT thr_subject FROM frm_threads 
				WHERE thr_pk = %s',
		array('integer'),
		array($post_data['pos_thr_fk']));
		 
		while($record = $ilDB->fetchAssoc($result))
		{
			$post_data['thr_subject'] = $record['thr_subject'];
			break;
		}

		// GET AUTHOR OF NEW POST
		$post_data["pos_usr_name"] = ilObjUser::_lookupLogin($post_data["pos_usr_id"]);
			
		$subject = $this->formatPostActivationNotificationSubject();
		$message = $this->formatPostActivationNotification($post_data);

		$mail_obj = new ilMail(ANONYMOUS_USER_ID);
		foreach ($moderators as $moderator)
		{
			$message = $mail_obj->sendMail(ilObjUser::_lookupLogin($moderator), '', '',
			$subject,
			$message,
			array(), array("system"));
		}
	}
}

function formatNotificationSubject($post_data)
{
	return $this->lng->txt("forums_notification_subject").' '.$post_data['top_name'];
}

function formatNotification($post_data, $cron = 0)
{
	global $ilIliasIniFile;

	if ($cron == 1)
	{
		$message = sprintf($this->lng->txt("forums_notification_intro"),
		$this->ilias->ini->readVariable("client","name"),
		$ilIliasIniFile->readVariable("server","http_path").'/?client_id='.CLIENT_ID)."\n\n";
	}
	else
	{
		$message = sprintf($this->lng->txt("forums_notification_intro"),
		$this->ilias->ini->readVariable("client","name"),
		ILIAS_HTTP_PATH.'/?client_id='.CLIENT_ID)."\n\n";
	}
	$message .= $this->lng->txt("forum").": ".$post_data["top_name"]."\n\n";
	$message .= $this->lng->txt("thread").": ".$post_data["thr_subject"]."\n\n";
	$message .= $this->lng->txt("new_post").":\n------------------------------------------------------------\n";
	$message .= $this->lng->txt("author").": ".$post_data["pos_usr_name"]."\n";
	$message .= $this->lng->txt("date").": ".$post_data["pos_date"]."\n";
	$message .= $this->lng->txt("subject").": ".$post_data["pos_subject"]."\n\n";
	if ($post_data["pos_cens"] == 1)
	{
		$message .= $post_data["pos_cens_com"]."\n";
	}
	else
	{
		$message .= $post_data["pos_message"]."\n";
	}
	$message .= "------------------------------------------------------------\n";
	if ($cron == 1)
	{
		$message .= sprintf($this->lng->txt("forums_notification_show_post"), $ilIliasIniFile->readVariable("server","http_path")."/goto.php?target=frm_".$post_data["ref_id"]."_".$post_data["pos_thr_fk"]."_".$post_data["pos_pk"].'&client_id='.CLIENT_ID);
	}
	else
	{
		$message .= sprintf($this->lng->txt("forums_notification_show_post"), ILIAS_HTTP_PATH."/goto.php?target=frm_".$post_data["ref_id"]."_".$post_data["pos_thr_fk"]."_".$post_data["pos_pk"].'&client_id='.CLIENT_ID);
	}

	return $message;
}

