<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObject.php';
require_once 'Modules/Forum/classes/class.ilForum.php';
require_once 'Modules/Forum/classes/class.ilFileDataForum.php';
require_once 'Modules/Forum/classes/class.ilForumProperties.php';

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
	public $Forum;
	
	private $objProperties = null;

	/**
	 * @var array
	 * @static
	 */
	protected static $obj_id_to_forum_id_cache = array();

	/**
	 * @var array
	 * @static
	 */
	protected static $ref_id_to_forum_id_cache = array();

	/**
	 * @var array
	 * @static
	 */
	protected static $forum_statistics_cache = array();

	/**
	 * @var array
	 * @static
	 */
	protected static $forum_last_post_cache = array();
	
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	public function __construct($a_id = 0,$a_call_by_reference = true)
	{
		$this->type = 'frm';
		parent::__construct($a_id, $a_call_by_reference);

		/*
		 * this constant is used for the information if a single post is marked as new
		 * All threads/posts created before this date are never marked as new
		 * Default is 8 weeks
		 *
		 */
		$new_deadline = time() - 60 * 60 * 24 * 7 * ($this->ilias->getSetting('frm_store_new') ?
													 $this->ilias->getSetting('frm_store_new') : 
													 8);
		define('NEW_DEADLINE', $new_deadline);
		
		// TODO: needs to rewrite scripts that are using Forum outside this class
		$this->Forum = new ilForum();
	}

	/**
	* Gets the disk usage of the object in bytes.
    *
	* @access	public
	* @return	integer		the disk usage in bytes
	*/
	function getDiskUsage()
	{
	    require_once("./Modules/File/classes/class.ilObjFileAccess.php");
		return ilObjForumAccess::_lookupDiskUsage($this->id);
	}

	function _lookupThreadSubject($a_thread_id)
	{
		global $ilDB;

		$res = $ilDB->queryf('
			SELECT thr_subject FROM frm_threads WHERE thr_pk = %s',
			array('integer'), array($a_thread_id));
		
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
			$res = $ilDB->queryf('
				SELECT top_pk FROM frm_data WHERE top_frm_fk = %s',
				array('integer'), array($a_frm_id));
			
			
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$topic_id = $row->top_pk;
			}

			// Get number of posts
			$res = $ilDB->queryf('
				SELECT COUNT(pos_pk) num_posts FROM frm_posts 
				WHERE pos_top_fk = %s',
				array('integer'), array($topic_id));
	
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$num_posts = $row->num_posts;
			}

			$res = $ilDB->queryf('
				SELECT COUNT(post_id) count_read FROM frm_user_read
				WHERE obj_id = %s
				AND usr_id = %s',
				array('integer', 'integer'), array($a_frm_id, $a_usr_id));
			
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
			$res = $ilDB->queryf('
				SELECT COUNT(pos_pk) num_posts FROM frm_posts
				WHERE pos_thr_fk = %s',
				array('integer'), array($a_thread_id));
			
			
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$num_posts = $row->num_posts;
			}

			$res = $ilDB->queryf('
				SELECT COUNT(post_id) count_read FROM frm_user_read 
				WHERE obj_id = %s
				AND usr_id = %s
				AND thread_id = %s',
				array('integer', 'integer', 'integer'), array($a_frm_id, $a_frm_id, $a_thread_id));
				
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
		$res = $ilDB->queryf('
			SELECT * FROM frm_posts WHERE pos_thr_fk = %s',
			array('integer'), array($a_thread_id));
		
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->markPostRead($a_usr_id,$a_thread_id,$row->pos_pk);
		}
		return true;
	}

	function markAllThreadsRead($a_usr_id)
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT * FROM frm_data, frm_threads 
			WHERE top_frm_fk = %s
			AND top_pk = thr_top_fk',
			array('integer'), array($this->getId()));
		
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
		$res = $ilDB->queryf('
			SELECT * FROM frm_user_read 
			WHERE usr_id = %s
			AND obj_id = %s
			AND thread_id = %s
			AND post_id = %s',
			array('integer', 'integer', 'integer', 'integer'),
			array($a_usr_id, $this->getId(), $a_thread_id, $a_post_id));
		
		if($res->numRows())
		{
			return true;
		}

		$res = $ilDB->manipulateF('
			INSERT INTO frm_user_read
			(	usr_id,
				obj_id,
				thread_id,
				post_id
			)
			VALUES (%s,%s,%s,%s)',
			array('integer', 'integer', 'integer', 'integer'),
			array($a_usr_id, $this->getId(), $a_thread_id, $a_post_id));
		
		return true;
	}

	public function markPostUnread($a_user_id, $a_post_id)
	{
		global $ilDB;

		$res = $ilDB->manipulateF('
			DELETE FROM frm_user_read
			WHERE usr_id = %s
			AND post_id = %s',
			array('integer','integer'),
			array($a_user_id, $a_post_id));
	}
	function isRead($a_usr_id,$a_post_id)
	{
		global $ilDB;
		
		$res = $ilDB->queryf('
			SELECT * FROM frm_user_read
			WHERE usr_id = %s
			AND post_id = %s',
			array('integer', 'integer'),
			array($a_usr_id, $a_post_id));
		
		return $ilDB->numRows($res) ? true : false;
	}

	function updateLastAccess($a_usr_id,$a_thread_id)
	{
		global $ilDB;
	
		$res = $ilDB->queryf('
			SELECT * FROM frm_thread_access 
			WHERE usr_id = %s
			AND obj_id = %s
			AND thread_id = %s',
			array('integer', 'integer', 'integer'),
			array($a_usr_id, $this->getId(), $a_thread_id));
		
		if($res->numRows())
		{
			$ilDB->manipulateF('
				UPDATE frm_thread_access 
				SET access_last = %s
				WHERE usr_id = %s
				AND obj_id = %s
				AND thread_id = %s',
				array('integer', 'integer', 'integer', 'integer'),
				array(time(), $a_usr_id, $this->getId(), $a_thread_id));

		}
		else
		{
			$ilDB->manipulateF('
				INSERT INTO frm_thread_access 
				(	access_last,
					access_old,
				 	usr_id,
				 	obj_id,
				 	thread_id)
				VALUES (%s,%s,%s,%s,%s)',
				array('integer', 'integer', 'integer', 'integer', 'integer'),
				array(time(), '0', $a_usr_id, $this->getId(), $a_thread_id));
				
		}			

		return true;
	}

	/**
	 * @static
	 * @param int
	 */
	public static function _updateOldAccess($a_usr_id)
	{
		global $ilDB, $ilias;

		$ilDB->manipulateF('
			UPDATE frm_thread_access 
			SET access_old = access_last
			WHERE usr_id = %s',
			array('integer'), array($a_usr_id));

		$set = $ilDB->query("SELECT * FROM frm_thread_access " .
				" WHERE usr_id = " . $ilDB->quote($a_usr_id, "integer")
		);
		while($rec = $ilDB->fetchAssoc($set))
		{
			$ilDB->manipulate("UPDATE frm_thread_access SET " .
					" access_old_ts = " . $ilDB->quote(date('Y-m-d H:i:s', $rec["access_old"]), "timestamp") .
					" WHERE usr_id = " . $ilDB->quote($rec["usr_id"], "integer") .
					" AND obj_id = " . $ilDB->quote($rec["obj_id"], "integer") .
					" AND thread_id = " . $ilDB->quote($rec["thread_id"], "integer")
			);
		}

		$new_deadline = time() - 60 * 60 * 24 * 7 * ($ilias->getSetting('frm_store_new') ?
			$ilias->getSetting('frm_store_new') :
			8);

		$ilDB->manipulateF('
			DELETE FROM frm_thread_access WHERE access_last < %s',
			array('integer'), array($new_deadline));
	}

	function _deleteUser($a_usr_id)
	{

		global $ilDB;

		$data = array($a_usr_id);
		
		$res = $ilDB->manipulateF('
			DELETE FROM frm_user_read WHERE usr_id = %s',
			array('integer'), $data
		);
		
		$res = $ilDB->manipulateF('
			DELETE FROM frm_thread_access WHERE usr_id = %s',
			array('integer'), $data
		);
		
		return true;
	}


	function _deleteReadEntries($a_post_id)
	{
		global $ilDB;

		$statement = $ilDB->manipulateF('
			DELETE FROM frm_user_read WHERE post_id = %s',
			array('integer'), array($a_post_id));
		
		return true;
	}

	function _deleteAccessEntries($a_thread_id)
	{
		global $ilDB;

		$statement = $ilDB->manipulateF('
			DELETE FROM frm_thread_access WHERE thread_id = %s',
			array('integer'), array($a_thread_id));
			
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
			
			$statement = $ilDB->manipulateF('
				UPDATE frm_data 
				SET top_name = %s,
					top_description = %s,
					top_update = %s,
					update_user = %s
				WHERE top_frm_fk =%s',
				array('text', 'text', 'timestamp', 'integer', 'integer'),
				array(	$this->getTitle(),
							$this->getDescription(), 
							date("Y-m-d H:i:s"), 
							(int)$_SESSION["AccountId"],
							(int)$this->getId()
			));
			
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

		ilForumProperties::getInstance($this->getId())->copy($new_obj->getId());
		$this->Forum->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($this->getId()));
		
		$topData = $this->Forum->getOneTopic();

		$nextId = $ilDB->nextId('frm_data');

		$statement = $ilDB->insert('frm_data', array(
			'top_pk'		=> array('integer', $nextId),
			'top_frm_fk'	=> array('integer', $new_obj->getId()),
			'top_name'		=> array('text', $topData['top_name']),
			'top_description' => array('text', $topData['top_description']),
			'top_num_posts' => array('integer', $topData['top_num_posts']),
			'top_num_threads' => array('integer', $topData['top_num_threads']),
			'top_last_post' => array('text', $topData['top_last_post']),
			'top_mods'		=> array('integer', !is_numeric($topData['top_mods']) ? 0 : $topData['top_mods']),
			'top_date'		=> array('timestamp', $topData['top_date']),
			'visits'		=> array('integer', $topData['visits']),
			'top_update'	=> array('timestamp', $topData['top_update']),
			'update_user'	=> array('integer', $topData['update_user']),
			'top_usr_id'	=> array('integer', $topData['top_usr_id'])
		));
		
		// read options
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');

		$cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
		$options = $cwo->getOptions($this->getRefId());

		$options['threads'] = $this->Forum->_getThreads($this->getId());

		// Generate starting threads
		include_once('Modules/Forum/classes/class.ilFileDataForum.php');
		
		$new_frm = $new_obj->Forum;
		$new_frm->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($new_obj->getId()));
				
		$new_frm->setForumId($new_obj->getId());
		$new_frm->setForumRefId($new_obj->getRefId());
	
		$new_topic = $new_frm->getOneTopic();
		foreach($options['threads'] as $thread_id=>$thread_subject)
		{
			$this->Forum->setMDB2WhereCondition('thr_pk = %s ', array('integer'), array($thread_id));			
			
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

		include_once './Modules/Forum/classes/class.ilForumModerators.php';
		$obj_mods = new ilForumModerators($this->getRefId());
		
		$old_mods = array();
		$old_mods = $obj_mods->getCurrentModerators();

		foreach($old_mods as $user_id)
		{
			$rbacadmin->assignUser($new_moderator, $user_id);
		}
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

		$this->Forum->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($this->getId()));
		
		$topData = $this->Forum->getOneTopic();	
		
		$threads = $this->Forum->getAllThreads($topData['top_pk']);
		foreach ($threads['items'] as $thread)
		{
			$data = array($thread->getId());

			// delete tree
			$statement = $ilDB->manipulateF('
				DELETE FROM frm_posts_tree WHERE thr_fk = %s',
				array('integer'), $data);
								
			// delete posts
			$statement = $ilDB->manipulateF('
				DELETE FROM frm_posts WHERE pos_thr_fk = %s',
				array('integer'), $data);
			
			// delete threads
			$statement = $ilDB->manipulateF('
				DELETE FROM frm_threads WHERE thr_pk = %s',
				array('integer'), $data);
		
		}

		$data = array($this->getId());
		// delete forum
		$statement = $ilDB->manipulateF('
			DELETE FROM frm_data WHERE top_frm_fk = %s',
			array('integer'), $data);

		// delete settings
		$statement = $ilDB->manipulateF('
			DELETE FROM frm_settings WHERE obj_id = %s',
			array('integer'), $data);
		
		// delete read infos
		$statement = $ilDB->manipulateF('
			DELETE FROM frm_user_read WHERE obj_id = %s',
			array('integer'), $data);
		
		// delete thread access entries
		$statement = $ilDB->manipulateF('
			DELETE FROM frm_thread_access WHERE obj_id = %s',
			array('integer'), $data);
		
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
		$statement = $ilDB->queryf('
			SELECT obj_id FROM object_data 
			WHERE type = %s 
			AND title = %s',
			array('text', 'text'),
			array('rolt', 'il_frm_moderator'));
		
		$res = $statement->fetchRow(DB_FETCHMODE_OBJECT);
		
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

		$res = $ilDB->queryf('
			SELECT * FROM object_data WHERE title = %s',
			array('text'), array($mod_title));
		
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
	
	public function saveData($a_roles = array())
	{
		global $ilUser, $ilDB;
		
		$nextId = $ilDB->nextId('frm_data');
		
		$top_data = array(
            'top_frm_fk'   		=> $this->getId(),
			'top_name'   		=> $this->getTitle(),
            'top_description' 	=> $this->getDescription(),
            'top_num_posts'     => 0,
            'top_num_threads'   => 0,
            'top_last_post'     => NULL,
			'top_mods'      	=> !is_numeric($a_roles[0]) ? 0 : $a_roles[0],
			'top_usr_id'      	=> $ilUser->getId(),
            'top_date' 			=> ilUtil::now()
        );       
        
        $statement = $ilDB->manipulateF('
        	INSERT INTO frm_data 
        	( 
        	 	top_pk,
        		top_frm_fk, 
        		top_name,
        		top_description,
        		top_num_posts,
        		top_num_threads,
        		top_last_post,
        		top_mods,
        		top_date,
        		top_usr_id
        	)
        	VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)',
        	array('integer', 'integer', 'text', 'text', 'integer', 'integer', 'text', 'integer', 'timestamp', 'integer'),
        	array(
	        	$nextId,
	        	$top_data['top_frm_fk'],
	        	$top_data['top_name'],
	        	$top_data['top_description'],
	        	$top_data['top_num_posts'],
				$top_data['top_num_threads'],
				$top_data['top_last_post'],
				$top_data['top_mods'],
				$top_data['top_date'],
				$top_data['top_usr_id']
		));
	}

	/**
	 * @static
	 * @param int $obj_id
	 * @return int
	 */
	public static function lookupForumIdByObjId($obj_id)
	{
		if(array_key_exists($obj_id, self::$obj_id_to_forum_id_cache))
		{
			return (int)self::$obj_id_to_forum_id_cache[$obj_id];
		}

		self::preloadForumIdsByObjIds(array($obj_id));

		return (int)self::$obj_id_to_forum_id_cache[$obj_id];
	}

	/**
	 * @static
	 * @param int $ref_id
	 * @return int
	 */
	public static function lookupForumIdByRefId($ref_id)
	{
		if(array_key_exists($ref_id, self::$ref_id_to_forum_id_cache))
		{
			return (int)self::$ref_id_to_forum_id_cache[$ref_id];
		}

		self::preloadForumIdsByRefIds(array($ref_id));

		return (int)self::$ref_id_to_forum_id_cache[$ref_id];
	}

	/**
	 * @static
	 * @param array $obj_ids
	 */
	public static function preloadForumIdsByObjIds(array $obj_ids)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		if(count($obj_ids) == 1)
		{
			$in = " objr.obj_id = " . $ilDB->quote(current($obj_ids), 'integer') . " ";
		}
		else
		{
			$in = $ilDB->in('objr.obj_id', $obj_ids, false, 'integer');
		}
		$query = "
			SELECT frmd.top_pk, objr.ref_id, objr.obj_id
			FROM object_reference objr
			INNER JOIN frm_data frmd ON frmd.top_frm_fk = objr.obj_id
			WHERE $in 
		";
		$res   = $ilDB->query($query);

		// Prepare  cache array
		foreach($obj_ids as $obj_id)
		{
			self::$obj_id_to_forum_id_cache[$obj_id] = null;
		}

		while($row = $ilDB->fetchAssoc($res))
		{
			self::$obj_id_to_forum_id_cache[$row['obj_id']] = $row['top_pk'];
			self::$ref_id_to_forum_id_cache[$row['ref_id']] = $row['top_pk'];
		}
	}

	/**
	 * @static
	 * @param array $ref_ids
	 */
	public static function preloadForumIdsByRefIds(array $ref_ids)
	{
		/**
		 * @var $ilDB ilDB
		 */
		global $ilDB;

		if(count($ref_ids) == 1)
		{
			$in = " objr.ref_id = " . $ilDB->quote(current($ref_ids), 'integer') . " ";
		}
		else
		{
			$in = $ilDB->in('objr.ref_id', $ref_ids, false, 'integer');
		}
		$query = "
			SELECT frmd.top_pk, objr.ref_id, objr.obj_id
			FROM object_reference objr
			INNER JOIN frm_data frmd ON frmd.top_frm_fk = objr.obj_id
			WHERE $in 
		";
		$res   = $ilDB->query($query);

		// Prepare  cache array
		foreach($ref_ids as $ref_id)
		{
			self::$ref_id_to_forum_id_cache[$ref_id] = null;
		}

		while($row = $ilDB->fetchAssoc($res))
		{
			self::$obj_id_to_forum_id_cache[$row['obj_id']] = $row['top_pk'];
			self::$ref_id_to_forum_id_cache[$row['ref_id']] = $row['top_pk'];
		}
	}

	/**
	 * @static
	 * @param int $ref_id
	 * @return array
	 */
	public static function lookupStatisticsByRefId($ref_id)
	{
		/**
		 * @var $ilAccess  ilAccessHandler
		 * @var $ilUser	ilObjUser
		 * @var $ilDB	  ilDB
		 * @var $ilSetting ilSetting
		 */
		global $ilAccess, $ilUser, $ilDB, $ilSetting;

		if(isset(self::$forum_statistics_cache[$ref_id]))
		{
			return self::$forum_statistics_cache[$ref_id];
		}

		$statistics = array(
			'num_posts'		=> 0,
			'num_unread_posts' => 0,
			'num_new_posts'	=> 0
		);

		$forumId = self::lookupForumIdByRefId($ref_id);
		if(!$forumId)
		{
			self::$forum_statistics_cache[$ref_id] = $statistics;
			return self::$forum_statistics_cache[$ref_id];
		}

		$act_clause = '';
		if(!$ilAccess->checkAccess('moderate_frm', '', $ref_id))
		{
			$act_clause .= " AND (frm_posts.pos_status = " . $ilDB->quote(1, "integer") . " OR frm_posts.pos_usr_id = " . $ilDB->quote($ilUser->getId(), "integer") . ") ";
		}

		$new_deadline = date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 7 * ($ilSetting->get('frm_store_new') ? $ilSetting->get('frm_store_new') : 8));

		$query = "
			(SELECT COUNT(frm_posts.pos_pk) cnt
			FROM frm_posts
			INNER JOIN frm_threads ON frm_posts.pos_thr_fk = frm_threads.thr_pk 
			WHERE frm_threads.thr_top_fk = %s $act_clause)
			
			UNION ALL
			 
			(SELECT COUNT(frm_user_read.post_id) cnt
			FROM frm_user_read
			INNER JOIN frm_posts ON frm_user_read.post_id = frm_posts.pos_pk
			INNER JOIN frm_threads ON frm_threads.thr_pk = frm_posts.pos_thr_fk 
			WHERE frm_user_read.usr_id = %s AND frm_posts.pos_top_fk = %s $act_clause)
			
			UNION ALL
			
			(SELECT COUNT(frm_posts.pos_pk) cnt
			FROM frm_posts
			LEFT JOIN frm_user_read ON (post_id = frm_posts.pos_pk AND frm_user_read.usr_id = %s)
			LEFT JOIN frm_thread_access ON (frm_thread_access.thread_id = frm_posts.pos_thr_fk AND frm_thread_access.usr_id = %s)
			WHERE frm_posts.pos_top_fk = %s
			AND ((frm_posts.pos_date > frm_thread_access.access_old_ts OR frm_posts.pos_update > frm_thread_access.access_old_ts)
				OR (frm_thread_access.access_old IS NULL AND (frm_posts.pos_date > %s OR frm_posts.pos_update > %s)))
			AND frm_posts.pos_usr_id != %s 
			AND frm_user_read.usr_id IS NULL $act_clause)
		";

		$types  = array('integer', 'integer', 'integer', 'integer', 'integer', 'integer', 'timestamp', 'timestamp', 'integer');
		$values = array($forumId, $ilUser->getId(), $forumId, $ilUser->getId(), $ilUser->getId(), $forumId, $new_deadline, $new_deadline, $ilUser->getId());

		$mapping = array_keys($statistics);
		$res     = $ilDB->queryF(
			$query,
			$types,
			$values
		);
		for($i = 0; $i <= 2; $i++)
		{
			$row = $ilDB->fetchAssoc($res);

			$statistics[$mapping[$i]] = (int)$row['cnt'];

			if($i == 1)
			{
				// unread = all - read
				$statistics[$mapping[$i]] = $statistics[$mapping[$i - 1]] - $statistics[$mapping[$i]];
			}
		}

		self::$forum_statistics_cache[$ref_id] = $statistics;

		return self::$forum_statistics_cache[$ref_id];
	}

	/**
	 * @static
	 * @param int $ref_id
	 * @return array
	 */
	public static function lookupLastPostByRefId($ref_id)
	{
		/**
		 * @var $ilAccess	   ilAccessHandler
		 * @var $ilUser		 ilObjUser
		 * @var $ilDB		   ilDB
		 */
		global $ilAccess, $ilUser, $ilDB;

		if(isset(self::$forum_last_post_cache[$ref_id]))
		{
			return self::$forum_last_post_cache[$ref_id];
		}

		$forumId = self::lookupForumIdByRefId($ref_id);
		if(!$forumId)
		{
			self::$forum_last_post_cache[$ref_id] = array();
			return self::$forum_last_post_cache[$ref_id];
		}

		$act_clause = '';
		if(!$ilAccess->checkAccess('moderate_frm', '', $ref_id))
		{
			$act_clause .= " AND (frm_posts.pos_status = " . $ilDB->quote(1, "integer") . " OR frm_posts.pos_usr_id = " . $ilDB->quote($ilUser->getId(), "integer") . ") ";
		}

		$ilDB->setLimit(1, 0);
		$query = "
			SELECT *
			FROM frm_posts 
			WHERE pos_top_fk = %s $act_clause
			ORDER BY pos_date DESC
		";
		$res   = $ilDB->queryF(
			$query,
			array('integer'),
			array($forumId)
		);

		$data = $ilDB->fetchAssoc($res);

		self::$forum_last_post_cache[$ref_id] = is_array($data) ? $data : array();

		return self::$forum_last_post_cache[$ref_id];
	}

	/**
	 * @static
	 * @param int   $ref_id
	 * @param array $thread_ids
	 * @return array
	 */
	public static function getUserIdsOfLastPostsByRefIdAndThreadIds($ref_id, array $thread_ids)
	{
		/**
		 * @var $ilUser   ilObjUser
		 * @var $ilAccess ilAccessHandler
		 * @var $ilDB     ilDB
		 */
		global $ilUser, $ilAccess, $ilDB;

		$act_clause       = '';
		$act_inner_clause = '';
		if(!$ilAccess->checkAccess('moderate_frm', '', $ref_id))
		{
			$act_clause .= " AND (t1.pos_status = " . $ilDB->quote(1, "integer") . " OR t1.pos_usr_id = " . $ilDB->quote($ilUser->getId(), "integer") . ") ";
			$act_inner_clause .= " AND (t3.pos_status = " . $ilDB->quote(1, "integer") . " OR t3.pos_usr_id = " . $ilDB->quote($ilUser->getId(), "integer") . ") ";
		}

		$in       = $ilDB->in("t1.pos_thr_fk", $thread_ids, false, 'integer');
		$inner_in = $ilDB->in("t3.pos_thr_fk", $thread_ids, false, 'integer');

		$query = "
			SELECT t1.pos_usr_id, t1.update_user
			FROM frm_posts t1
			INNER JOIN (
				SELECT t3.pos_thr_fk, MAX(t3.pos_date) pos_date
				FROM frm_posts t3
				WHERE $inner_in $act_inner_clause
				GROUP BY t3.pos_thr_fk
			) t2 ON t2.pos_thr_fk = t1.pos_thr_fk AND t2.pos_date = t1.pos_date
			WHERE $in $act_clause
		";

		$usr_ids = array();

		$res = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($res))
		{
			if((int)$row['pos_usr_id'])
			{
				$usr_ids[] = (int)$row['pos_usr_id'];
			}
			if((int)$row['update_user'])
			{
				$usr_ids[] = (int)$row['update_user'];
			}
		}

		return array_unique($usr_ids);
	}
}