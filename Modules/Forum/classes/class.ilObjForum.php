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

require_once './Modules/Forum/classes/class.ilForum.php';
require_once './classes/class.ilObject.php';
require_once './Modules/Forum/classes/class.ilFileDataForum.php';
require_once './Modules/Forum/classes/class.ilForumProperties.php';

/** @defgroup ModulesForum Modules/Forum
 */

/**
* Class ilObjForum
*
* @author Wolfgang Merkens <wmerkens@databay.de> 
* @version $Id$
*
* @ingroup ModulesForum
*/
class ilObjForum extends ilObject
{
	/**
	* Forum object
	* @var		object Forum
	* @access	private
	*/
	var $Forum;
	
	private $objProperties = null;
	
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjForum($a_id = 0,$a_call_by_reference = true)
	{
		global $ilias;

		/*
		 * this constant is used for the information if a single post is marked as new
		 * All threads/posts created before this date are never marked as new
		 * Default is 8 weeks
		 *
		 */
		$new_deadline = time() - 60 * 60 * 24 * 7 * ($ilias->getSetting('frm_store_new') ? 
													 $ilias->getSetting('frm_store_new') : 
													 8);
		define('NEW_DEADLINE',$new_deadline);
	
		$this->type = "frm";
		$this->ilObject($a_id,$a_call_by_reference);
		
		// TODO: needs to rewrite scripts that are using Forum outside this class
		$this->Forum =& new ilForum();
	}
	
	function read($a_force_db = false)
	{
		parent::read($a_force_db);
	}

	function _lookupThreadSubject($a_thread_id)
	{
		global $ilDB;

		$statement = $ilDB->prepare('
			SELECT thr_subject FROM frm_threads WHERE thr_pk = ?',
			array('integer')
		);
		
		$data = array($a_thread_id);
		$res = $ilDB->execute($statement, $data);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->thr_subject;
		}
		return '';
	}
		
	// METHODS FOR UN-READ STATUS
	function getCountUnread($a_usr_id,$a_thread_id = 0)
	{
		return $this->_getCountUnread($this->getId(),$a_usr_id,$a_thread_id);
	}

	function _getCountUnread($a_frm_id, $a_usr_id,$a_thread_id = 0)
	{
		global $ilBench, $ilDB;

		$ilBench->start("Forum",'getCountRead');
		if(!$a_thread_id)
		{
			// Get topic_id
			$statement = $this->ilias->db->prepare('
				SELECT top_pk FROM frm_data WHERE top_frm_fk = ?',
				array('integer')
			);

			$data = array($a_frm_id);
			
			$res = $this->ilias->db->execute($statement, $data);
			
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$topic_id = $row->top_pk;
			}

			// Get number of posts
/*			$statement = $this->ilias->db->prepare('
				SELECT COUNT(pos_pk) as num_posts FROM frm_posts 
				WHERE pos_top_fk = ?',
				array('integer')
			);
*/
			$statement = $this->ilias->db->prepare('
				SELECT COUNT(pos_pk) num_posts FROM frm_posts 
				WHERE pos_top_fk = ?',
				array('integer')
			);
			
			$data = array($topic_id);
			
			$res = $this->ilias->db->execute($statement, $data);
			
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$num_posts = $row->num_posts;
			}

			$statement = $this->ilias->db->prepare('
				SELECT COUNT(post_id) count_read FROM frm_user_read
				WHERE obj_id = ?
				AND usr_id = ?',
				array('integer', 'integer')
			);
			
			$data = array($a_frm_id, $a_usr_id);
			$res = $this->ilias->db->execute($statement, $data);		
			
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$count_read = $row->count_read;
			}
			$unread = $num_posts - $count_read;

			$ilBench->stop("Forum",'getCountRead');
			return $unread > 0 ? $unread : 0;
		}
		else
		{
			$statement = $this->ilias->db->prepare('
				SELECT COUNT(pos_pk) num_posts FROM frm_posts
				WHERE pos_thr_fk = ?',
				array('integer')
			);
			
			$data = array($a_thread_id);
			
			$res = $this->ilias->db->execute($statement, $data);
			
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$num_posts = $row->num_posts;
			}

			$statement = $this->ilias->db->prepare('
				SELECT COUNT(post_id) count_read FROM frm_user_read 
				WHERE obj_id = ?
				AND usr_id = ?
				AND thread_id = ?',
				array('integer', 'integer', 'integer')
			);
			
			$data = array($a_frm_id, $a_frm_id, $a_thread_id);
			
			$res = $this->ilias->db->execute($statement, $data);
						
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$count_read = $row->count_read;
			}
			$unread = $num_posts - $count_read;

			$ilBench->stop("Forum",'getCountRead');
			return $unread > 0 ? $unread : 0;
		}
		$ilBench->stop("Forum",'getCountRead');
		return false;
	}


	function markThreadRead($a_usr_id,$a_thread_id)
	{
		global $ilDB;
		
		// Get all post ids
		$statement = $this->ilias->db->prepare('
			SELECT * FROM frm_posts WHERE pos_thr_fk = ?',
			array('integer')
		);
		
		$data = array($a_thread_id);
		
		$res = $this->ilias->db->execute($statement, $data);		
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->markPostRead($a_usr_id,$a_thread_id,$row->pos_pk);
		}
		return true;
	}

	function markAllThreadsRead($a_usr_id)
	{
		global $ilDB;
		
		$statement = $this->ilias->db->prepare('
			SELECT * FROM frm_data, frm_threads 
			WHERE top_frm_fk = ?
			AND top_pk = thr_top_fk',
			array('integer')
		);
		
		$data = array($this->getId());
		
		$res = $this->ilias->db->execute($statement, $data);		
				
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->markThreadRead($a_usr_id,$row->thr_pk);
		}

		return true;
	}
		

	function markPostRead($a_usr_id,$a_thread_id,$a_post_id)
	{
		global $ilDB;
		
		// CHECK IF ENTRY EXISTS
		$statement = $this->ilias->db->prepare('
			SELECT * FROM frm_user_read 
			WHERE usr_id = ?
			AND obj_id = ?
			AND thread_id = ?
			AND post_id = ?',
			array('integer', 'integer', 'integer', 'integer')
		);
		
		$data = array($a_usr_id, $this->getId(), $a_thread_id, $a_post_id);
		
		$res = $this->ilias->db->execute($statement, $data);		
		
		if($res->numRows())
		{
			return true;
		}

		$statement = $this->ilias->db->prepareManip('
			INSERT INTO frm_user_read
			SET usr_id = ?,
				obj_id = ?,
				thread_id = ?,
				post_id = ?',
			array('integer', 'integer', 'integer', 'integer')
		);
		
		$data = array($a_usr_id, $this->getId(), $a_thread_id, $a_post_id);
		
		$res = $this->ilias->db->execute($statement, $data);
				
		return true;
	}

	function isRead($a_usr_id,$a_post_id)
	{
		global $ilDB;
		
		$statement = $this->ilias->db->prepare('
			SELECT * FROM frm_user_read
			WHERE usr_id = ?
			AND post_id = ?',
			array('integer', 'integer')
		);
		
		$data = array($a_usr_id, $a_post_id);
		$res = $this->ilias->db->execute($statement, $data);
		
		return $res->numRows() ? true : false;
	}


	// METHODS FOR NEW STATUS
	function getCountNew($a_usr_id,$a_thread_id = 0)
	{
		global $ilBench, $ilDB;

		$ilBench->start('Forum','getCountNew');
		if($a_thread_id)
		{
			$num = $this->__getCountNew($a_usr_id,$a_thread_id);
			$ilBench->stop('Forum','getCountNew');

			return $num;
		}
		else
		{
			$counter = 0;

			// Get threads
			$statement = $this->ilias->db->prepare('
				SELECT DISTINCT(pos_thr_fk) FROM frm_posts,frm_data
				WHERE top_pk = pos_top_fk 
				AND top_frm_fk = ?',
				array('integer')
			);
			
			$data = array($this->getId());
			
			$res = $this->ilias->db->execute($statement, $data);			
			
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$counter += $this->__getCountNew($a_usr_id,$row->pos_thr_fk);
			}
			$ilBench->stop('Forum','getCountNew');
			return $counter;
		}
		return 0;
	}


	function __getCountNew($a_usr_id,$a_thread_id = 0)
	{
		global $ilDB;
		
		$counter = 0;
		
		$timest = $this->__getLastThreadAccess($a_usr_id,$a_thread_id);

		// CHECK FOR NEW
		$statement = $this->ilias->db->prepare('
			SELECT pos_pk FROM frm_posts
			WHERE pos_thr_fk = ?
			AND ( pos_date > ? OR pos_update > ?)
			AND pos_usr_id != ?',
			array('integer', 'timestamp', 'timestamp', 'integer')
		);
		
		$data = array($a_thread_id, date('Y-m-d H:i:s',$timest), date('Y-m-d H:i:s',$timest), $a_usr_id);
				
		$res = $this->ilias->db->execute($statement, $data);					
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(!$this->isRead($a_usr_id,$row->pos_pk))
			{
				++$counter;
			}
		}
		return $counter;
	}

	function isNew($a_usr_id,$a_thread_id,$a_post_id)
	{
		global $ilDB;
		
		if($this->isRead($a_usr_id,$a_post_id))
		{
			return false;
		}
		$timest = $this->__getLastThreadAccess($a_usr_id,$a_thread_id);
		
		$statement = $this->ilias->db->prepare('
			SELECT * FROM frm_posts 
			WHERE pos_pk = ?
			AND (pos_date > ? OR pos_update > ?)
			AND pos_usr_id != ?',
			array('integer', 'timestamp', 'timestamp', 'integer')
		);
		$data = array($a_post_id, date('Y-m-d H:i:s',$timest), date('Y-m-d H:i:s',$timest), $a_usr_id);		
		
		$res = $this->ilias->db->execute($statement, $data);					
		return $res->numRows() ? true : false;
	}

	function updateLastAccess($a_usr_id,$a_thread_id)
	{
		global $ilDB;
	
		$statement = $this->ilias->db->prepare('
			SELECT * FROM frm_thread_access 
			WHERE usr_id = ?
			AND obj_id = ?
			AND thread_id = ?',
			array('integer', 'integer', 'integer')
		);
		
		$data = array($a_usr_id, $this->getId(), $a_thread_id);
		
		$res = $this->ilias->db->execute($statement, $data);					
				
		if($res->numRows())
		{
			$statement = $this->ilias->db->prepareManip('
				UPDATE frm_thread_access 
				SET access_last = ?
				WHERE usr_id = ?
				AND obj_id = ?
				AND thread_id = ?',
				array('timestamp', 'integer', 'integer', 'integer')
			);

			$data = array(time(), $a_usr_id, $this->getId(), $a_thread_id);
			$res = $this->ilias->db->execute($statement, $data);						
			
		}
		else
		{
			$statement = $this->ilias->db->prepareManip('
				INSERT INTO frm_thread_access 
				SET access_last = ?,
					access_old = ?,
				 	usr_id = ?,
				 	obj_id = ?,
				 	thread_id = ?',
				array('timestamp', 'integer', 'integer', 'integer', 'integer')
			);

			$data = array(time(), '0', $a_usr_id, $this->getId(), $a_thread_id);
			$res = $this->ilias->db->execute($statement, $data);				
		}			

		return true;
	}

	// STATIC
	function _updateOldAccess($a_usr_id)
	{
		global $ilDB, $ilias;

		$statement = $ilDB->prepareManip('
			UPDATE frm_thread_access 
			SET access_old = access_last
			WHERE usr_id = ?',
			array('integer')
		);
		
		$data = array($a_usr_id);
		
		$res = $ilDB->execute($statement, $data);				
					
		// Delete old entries

		$new_deadline = time() - 60 * 60 * 24 * 7 * ($ilias->getSetting('frm_store_new') ?
													 $ilias->getSetting('frm_store_new') : 
													 8);

			$statement = $ilDB->prepareManip('
			DELETE FROM frm_thread_access WHERE access_last < ?',
			array('timestamp')
		);

		$data = array($new_deadline);
		$res = $ilDB->execute($statement, $data);					
		
		return true;
	}

	function _deleteUser($a_usr_id)
	{

		global $ilDB;

		$data = array($a_usr_id);
		
		$statement = $ilDB->prepareManip('
			DELETE FROM frm_user_read WHERE usr_id = ?',
			array('integer')
		);
		$res = $ilDB->execute($statement, $data);	
		
		$statement = $ilDB->prepareManip('
			DELETE FROM frm_thread_access WHERE usr_id = ?',
			array('integer')
		);
		$res = $ilDB->execute($statement, $data);			
		
		
		return true;
	}


	function _deleteReadEntries($a_post_id)
	{
		global $ilDB;

		$statement = $ilDB->prepareManip('
			DELETE FROM frm_user_read WHERE post_id = ?',
			array('integer')
		);
		
		$data = array($a_post_id);
		
		$res = $ilDB->execute($statement, $data);	
		
		return true;
	}

	function _deleteAccessEntries($a_thread_id)
	{
		global $ilDB;

		$statement = $ilDB->prepareManip('
			DELETE FROM frm_thread_access WHERE thread_id = ?',
			array('integer')
		);
		
		$data = array($a_thread_id);
		
		$res = $ilDB->execute($statement, $data);	

		return true;
	}
	
	/**
	* update forum data
	*
	* @access	public
	*/
	function update()
	{
		global $ilDB;
		
		if (parent::update())
		{
			$statement = $this->ilias->db->prepareManip('
				UPDATE frm_data 
				SET top_name = ?,
					top_description = ?,
					top_update = ?,
					update_user = ?,
				WHERE top_frm_fk =?',
				array('text', 'text', 'timestamp', 'integer', 'integer')
			);
				 
			$data = array(	$this->getTitle(),
							$this->getDescription(), 
							date("Y-m-d H:i:s"), 
							(int)$_SESSION["AccountId"],
							(int)$this->getId()
			);
			
			$res = $this->ilias->db->execute($statement, $data);		
					
			return true;
		}

		return false;
	}
	
	/**
	 * Clone Object
	 *
	 * @access public
	 * @param int source_id
	 * @apram int copy id
	 * 
	 */
	public function cloneObject($a_target_id, $a_copy_id = 0)
	{
		global $ilDB,$ilUser;
		
	 	$new_obj = parent::cloneObject($a_target_id, $a_copy_id);
	 	$this->cloneAutoGeneratedRoles($new_obj);
	 	
	 	ilForumProperties::getInstance($a_target_id)->copy($new_obj->getId());

		$this->Forum->setMDB2WhereCondition('top_frm_fk = ? ', array('integer'), array($this->getId()));
		
		$topData = $this->Forum->getOneTopic();

		$statement = $ilDB->prepareManip('
			INSERT INTO frm_data 
			SET top_frm_fk = ?,
				top_name = ?,				
				top_description = ?,
				top_num_posts = ?,
				top_num_threads = ?,
				top_last_post = ?,
				top_mods = ?,
				top_date = ?,
				visits = ?,
				top_update = ?,
				update_user = ?,
				top_usr_id = ?',
				array(	'integer', 
						'text', 
						'text', 
						'integer', 
						'integer',
						'text', 
						'integer', 
						'timestamp',
						'integer', 
						'timestamp',
						'integer', 
						'integer'
				)
		);
		
		$data = array(	$new_obj->getId(),
						$topData['top_name'],
						$topData['top_description'],
						'0',
						'0',
						'',
						ilObjForum::_lookupModeratorRole($new_obj->getRefId()),
						date('Y-m-d H:i:s', time()),
						'0',
						date('Y-m-d H:i:s', time()),
						'0',
						$ilUser->getId()
		);
		
		$ilDB->execute($statement, $data);
		// read options
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		$cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
		$options = $cwo->getOptions($this->getRefId());

		// Generate starting threads
		if(!is_array($options['threads']))
		{
			return $new_obj;
		}
		
		include_once('Modules/Forum/classes/class.ilFileDataForum.php');
		
		$new_frm = $new_obj->Forum;

		$new_frm->setMDB2WhereCondition('top_frm_fk = ? ', array('integer'), array($new_obj->getId()));
				
		$new_frm->setForumId($new_obj->getId());
		$new_frm->setForumRefId($new_obj->getRefId());
		$new_topic = $new_frm->getOneTopic();
		foreach($options['threads'] as $thread_id)
		{

			$this->Forum->setMDB2WhereCondition('thr_pk = ? ', array('integer'), array($thread_id));			
			
			$old_thread = $this->Forum->getOneThread();
			
			
			
			$old_post_id = $this->Forum->getFirstPostByThread($old_thread['thr_pk']);
			$old_post = $this->Forum->getOnePost($old_post_id);

			// Now create new thread and first post
			$new_post = $new_frm->generateThread($new_topic['top_pk'],
				$old_thread['thr_usr_id'],
				$old_thread['thr_subject'],
				ilForum::_lookupPostMessage($old_post_id),
				$old_post['notify'],
				0,
				$old_thread['thr_usr_alias'],
				$old_thread['thr_date']);
			// Copy attachments
			$old_forum_files = new ilFileDataForum($this->getId(),$old_post_id);
			$old_forum_files->ilClone($new_obj->getId(),$new_post);
		}
		
		return $new_obj;
	}
	
	/**
	 * Clone forum moderator role 
	 *
	 * @access public
	 * @param object forum object
	 * 
	 */
	public function cloneAutoGeneratedRoles($new_obj)
	{
		global $ilLog,$rbacadmin,$rbacreview;
		
		$moderator = ilObjForum::_lookupModeratorRole($this->getRefId());
		$new_moderator = ilObjForum::_lookupModeratorRole($new_obj->getRefId());
	 	$source_rolf = $rbacreview->getRoleFolderIdOfObject($this->getRefId());
	 	$target_rolf = $rbacreview->getRoleFolderIdOfObject($new_obj->getRefId());
	 	
		if(!$moderator || !$new_moderator || !$source_rolf || !$target_rolf)
		{
			$ilLog->write(__METHOD__.' : Error cloning auto generated role: il_frm_moderator');
		}
	 	$rbacadmin->copyRolePermissions($moderator,$source_rolf,$target_rolf,$new_moderator,true);
		$ilLog->write(__METHOD__.' : Finished copying of role il_frm_moderator.');
	}	

	/**
	* Delete forum and all related data	
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		global $ilDB;
				
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}
		
		// delete attachments
		$tmp_file_obj =& new ilFileDataForum($this->getId());
		$tmp_file_obj->delete();
		unset($tmp_file_obj);

		$this->Forum->setMDB2WhereCondition('top_frm_fk = ? ', array('integer'), array($this->getId()));
		
		$topData = $this->Forum->getOneTopic();	
		
		$threads = $this->Forum->getAllThreads($topData['top_pk']);
		foreach ($threads as $thread)
		{
			$data = array($thread->getId());
			
			// delete tree
			$statement = $this->ilias->db->prepareManip('
				DELETE FROM frm_posts_tree WHERE thr_fk = ?',
				array('integer'));
			$this->ilias->db->execute($statement, $data);
								
			// delete posts
			$statement = $this->ilias->db->prepareManip('
				DELETE FROM frm_posts WHERE pos_thr_fk = ?',
				array('integer'));
			$this->ilias->db->execute($statement, $data);
			
			// delete threads
			$statement = $this->ilias->db->prepareManip('
				DELETE FROM frm_threads WHERE thr_pk = ?',
				array('integer'));
			$this->ilias->db->execute($statement, $data);
			
		}

		$data = array($this->getId());
		
		// delete forum
		$statement = $this->ilias->db->prepareManip('
			DELETE FROM frm_data WHERE top_frm_fk =?',
			array('integer'));
		$this->ilias->db->execute($statement, $data);

		// delete settings
		$statement = $this->ilias->db->prepareManip('
			DELETE FROM frm_settings WHERE obj_id =?',
			array('integer'));
		$this->ilias->db->execute($statement, $data);
		
		// delete read infos
		$statement = $this->ilias->db->prepareManip('
			DELETE FROM frm_user_read WHERE obj_id =?',
			array('integer'));
		$this->ilias->db->execute($statement, $data);
		
		// delete thread access entries
		$statement = $this->ilias->db->prepareManip('
			DELETE FROM frm_thread_access WHERE obj_id =?',
			array('integer'));
		$this->ilias->db->execute($statement, $data);
		
		
		return true;
	}

	/**
	* init default roles settings
	* @access	public
	* @return	array	object IDs of created local roles.
	*/
	function initDefaultRoles()
	{
		global $rbacadmin,$rbacreview,$ilDB;

		// Create a local role folder
		$rolf_obj = $this->createRoleFolder();

		// CREATE Moderator role
		$role_obj = $rolf_obj->createRole("il_frm_moderator_".$this->getRefId(),"Moderator of forum obj_no.".$this->getId());
		$roles[] = $role_obj->getId();
		
		// SET PERMISSION TEMPLATE OF NEW LOCAL ADMIN ROLE
		$statement = $this->ilias->db->prepare('
			SELECT obj_id FROM object_data 
			WHERE type = ? 
			AND title = ?',
			array('text', 'text')
		);
		
		$data = array('rolt', 'il_frm_moderator');
		$sql_res = $this->ilias->db->execute($statement, $data);
		$res = $sql_res->fetchRow(DB_FETCHMODE_OBJECT);
		
		$rbacadmin->copyRoleTemplatePermissions($res->obj_id,ROLE_FOLDER_ID,$rolf_obj->getRefId(),$role_obj->getId());

		// SET OBJECT PERMISSIONS OF COURSE OBJECT
		$ops = $rbacreview->getOperationsOfRole($role_obj->getId(),"frm",$rolf_obj->getRefId());
		$rbacadmin->grantPermission($role_obj->getId(),$ops,$this->getRefId());

		return $roles ? $roles : array();
	}
	
	/**
	 * Lookup moderator role
	 *
	 * @access public
	 * @static
	 * @param int ref_id of forum
	 * 
	 */
	public static function _lookupModeratorRole($a_ref_id)
	{
		global $ilDB;
		
		$mod_title = 'il_frm_moderator_'.$a_ref_id;

		$statement = $ilDB->prepare('
			SELECT * FROM object_data WHERE title = ?',
			array('text')
		);

		$data = array($mod_title);
		$res = $ilDB->execute($statement, $data);
		
	 	while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
	 	{
	 		return $row->obj_id;
	 	}
	 	return 0;
	}


	function createSettings()
	{		
		global $ilDB;
		
		// news settings (public notifications yes/no)
		include_once("./Services/News/classes/class.ilNewsItem.php");
		$default_visibility = ilNewsItem::_getDefaultVisibilityForRefId($_GET["ref_id"]);
		if ($default_visibility == "public")
		{
			ilBlockSetting::_write("news", "public_notifications", 1, 0, $this->getId());
		}

		return true;
	}	

	function __getLastThreadAccess($a_usr_id,$a_thread_id)
	{
		global $ilDB;

		$statement = $this->ilias->db->prepare('
			SELECT * FROM frm_thread_access 
			WHERE thread_id = ?
			AND usr_id = ?',
			array('integer', 'integer')
		);

		$data = array($a_thread_id, $a_usr_id);
		
		$res = $this->ilias->db->execute($statement, $data);
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$last_access = $row->access_old;
		}
		if(!$last_access)
		{
			// Set last access according to administration setting
			$last_access = NEW_DEADLINE;
		}
		return $last_access;
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
		
/*		$query = $ilDB->prepare("SELECT COUNT(*) AS cnt FROM frm_notification WHERE user_id = ? AND thread_id = ?",
		         	array("integer", "integer"));
*/
		$query = $ilDB->prepare("SELECT COUNT(*) cnt FROM frm_notification WHERE user_id = ? AND thread_id = ?",
		         	array("integer", "integer"));
		
		$result = $ilDB->execute($query, array($user_id, $thread_id));		
		while($record = $ilDB->fetchAssoc($result))
		{
			return (bool)$record['cnt'];
		}
		
		return false;
	}
} // END class.ilObjForum
?>