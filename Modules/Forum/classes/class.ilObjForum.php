<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ModulesForum Modules/Forum
 */

/**
 * Class ilObjForum
 * @author  Wolfgang Merkens <wmerkens@databay.de>
 * @version $Id$
 * @ingroup ModulesForum
 */
class ilObjForum extends ilObject
{
    /**
     * Forum object
     * @var        object Forum
     * @access    private
     */
    public $Forum;

    //	private $objProperties = null;

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

    private $settings;
    private $rbac;
    private $ilBench;
    private $user;
    private $logger;
    
    /**
     * Constructor
     * @access    public
     * @param    integer    $a_id                reference_id or object_id
     * @param    boolean    $a_call_by_reference treat the id as reference_id (true) or object_id (false)
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;
        $this->settings = $DIC->settings();
        $this->rbac = $DIC->rbac();
        $this->db = $DIC->database();
        $this->ilBench = $DIC['ilBench'];
        $this->user = $DIC->user();
        $this->logger = $DIC->logger()->root();
        
        $this->type = 'frm';
        parent::__construct($a_id, $a_call_by_reference);

        /*
         * this constant is used for the information if a single post is marked as new
         * All threads/posts created before this date are never marked as new
         * Default is 8 weeks
         *
         */
        $new_deadline = time() - 60 * 60 * 24 * 7 * ($this->settings->get('frm_store_new') ?
            $this->settings->get('frm_store_new') :
            8);
        define('NEW_DEADLINE', $new_deadline);

        // TODO: needs to rewrite scripts that are using Forum outside this class
        $this->Forum = new ilForum();
    }

    /**
     * @return int
     */
    public function create()
    {
        $id = parent::create();

        $properties = ilForumProperties::getInstance($this->getId());
        $properties->setDefaultView(ilForumProperties::VIEW_DATE_ASC);
        $properties->setAnonymisation(0);
        $properties->setStatisticsStatus(0);
        $properties->setPostActivation(0);
        $properties->setThreadSorting(0);
        $properties->insert();

        $this->createSettings();

        $this->saveData();

        return $id;
    }

    /**
     * @return int
     */
    public function setPermissions($a_ref_id)
    {
        parent::setPermissions($a_ref_id);

        // ...finally assign moderator role to creator of forum object
        $roles = array(ilObjForum::_lookupModeratorRole($this->getRefId()));
        $this->rbac->admin()->assignUser($roles[0], $this->getOwner(), 'n');
        $this->updateModeratorRole($roles[0]);
    }

    /**
     * @param int $role_id
     */
    public function updateModeratorRole($role_id)
    {
        $this->db->manipulate('UPDATE frm_data SET top_mods = ' . $this->db->quote($role_id, 'integer') . ' WHERE top_frm_fk = ' . $this->db->quote($this->getId(), 'integer'));
    }

    /**
     * Gets the disk usage of the object in bytes.
     * @access    public
     * @return    integer        the disk usage in bytes
     */
    public function getDiskUsage()
    {
        return ilObjForumAccess::_lookupDiskUsage($this->id);
    }

    public static function _lookupThreadSubject($a_thread_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryf(
            '
			SELECT thr_subject FROM frm_threads WHERE thr_pk = %s',
            array('integer'),
            array($a_thread_id)
        );

        while ($row = $ilDB->fetchObject($res)) {
            return $row->thr_subject;
        }
        return '';
    }

    // METHODS FOR UN-READ STATUS
    public function getCountUnread($a_usr_id, $a_thread_id = 0, $ignoreRoot = false)
    {
        $a_frm_id = $this->getId();

        $this->ilBench->start("Forum", 'getCountRead');
        if (!$a_thread_id) {
            // Get topic_id
            $res = $this->db->queryf(
                '
				SELECT top_pk FROM frm_data WHERE top_frm_fk = %s',
                array('integer'),
                array($a_frm_id)
            );


            while ($row = $this->db->fetchObject($res)) {
                $topic_id = $row->top_pk;
            }

            // Get number of posts
            $res = $this->db->queryf(
                '
				SELECT COUNT(pos_pk) num_posts
				FROM frm_posts 
				LEFT JOIN frm_posts_tree ON frm_posts_tree.pos_fk = pos_pk
				WHERE pos_top_fk = %s' . ($ignoreRoot ? ' AND parent_pos != 0 ' : ''),
                array('integer'),
                array($topic_id)
            );

            while ($row = $this->db->fetchObject($res)) {
                $num_posts = $row->num_posts;
            }

            $res = $this->db->queryf(
                '
				SELECT COUNT(post_id) count_read FROM frm_user_read
				WHERE obj_id = %s
				AND usr_id = %s',
                array('integer', 'integer'),
                array($a_frm_id, $a_usr_id)
            );

            while ($row = $this->db->fetchObject($res)) {
                $count_read = $row->count_read;
            }
            $unread = $num_posts - $count_read;

            $this->ilBench->stop("Forum", 'getCountRead');
            return $unread > 0 ? $unread : 0;
        } else {
            $res = $this->db->queryf(
                '
				SELECT COUNT(pos_pk) num_posts FROM frm_posts
				LEFT JOIN frm_posts_tree ON frm_posts_tree.pos_fk = pos_pk
				WHERE pos_thr_fk = %s' . ($ignoreRoot ? ' AND parent_pos != 0 ' : ''),
                array('integer'),
                array($a_thread_id)
            );

            $row = $this->db->fetchObject($res);
            $num_posts = $row->num_posts;

            $res = $this->db->queryf(
                '
				SELECT COUNT(post_id) count_read FROM frm_user_read 
				WHERE obj_id = %s
				AND usr_id = %s
				AND thread_id = %s',
                array('integer', 'integer', 'integer'),
                array($a_frm_id, $a_frm_id, $a_thread_id)
            );

            $row = $this->db->fetchObject($res);
            $count_read = $row->count_read;

            $unread = $num_posts - $count_read;

            $this->ilBench->stop("Forum", 'getCountRead');
            return $unread > 0 ? $unread : 0;
        }
        $this->ilBench->stop("Forum", 'getCountRead');
        return false;
    }


    public function markThreadRead($a_usr_id, $a_thread_id)
    {
        // Get all post ids
        $res = $this->db->queryf(
            '
			SELECT * FROM frm_posts WHERE pos_thr_fk = %s',
            array('integer'),
            array($a_thread_id)
        );

        while ($row = $this->db->fetchObject($res)) {
            $this->markPostRead($a_usr_id, $a_thread_id, $row->pos_pk);
        }
        return true;
    }

    public function markAllThreadsRead($a_usr_id)
    {
        $res = $this->db->queryf(
            '
			SELECT * FROM frm_data, frm_threads 
			WHERE top_frm_fk = %s
			AND top_pk = thr_top_fk',
            array('integer'),
            array($this->getId())
        );

        while ($row = $this->db->fetchObject($res)) {
            $this->markThreadRead($a_usr_id, $row->thr_pk);
        }

        return true;
    }


    public function markPostRead($a_usr_id, $a_thread_id, $a_post_id)
    {
        // CHECK IF ENTRY EXISTS
        $res = $this->db->queryf(
            '
			SELECT * FROM frm_user_read 
			WHERE usr_id = %s
			AND obj_id = %s
			AND thread_id = %s
			AND post_id = %s',
            array('integer', 'integer', 'integer', 'integer'),
            array($a_usr_id, $this->getId(), $a_thread_id, $a_post_id)
        );

        if ($this->db->numRows($res)) {
            return true;
        }

        $this->db->manipulateF(
            '
			INSERT INTO frm_user_read
			(	usr_id,
				obj_id,
				thread_id,
				post_id
			)
			VALUES (%s,%s,%s,%s)',
            array('integer', 'integer', 'integer', 'integer'),
            array($a_usr_id, $this->getId(), $a_thread_id, $a_post_id)
        );

        return true;
    }

    public function markPostUnread($a_user_id, $a_post_id)
    {
        $this->db->manipulateF(
            '
			DELETE FROM frm_user_read
			WHERE usr_id = %s
			AND post_id = %s',
            array('integer', 'integer'),
            array($a_user_id, $a_post_id)
        );
    }

    public function isRead($a_usr_id, $a_post_id)
    {
        $res = $this->db->queryf(
            '
			SELECT * FROM frm_user_read
			WHERE usr_id = %s
			AND post_id = %s',
            array('integer', 'integer'),
            array($a_usr_id, $a_post_id)
        );

        return $this->db->numRows($res) ? true : false;
    }

    public function updateLastAccess($a_usr_id, $a_thread_id)
    {
        $res = $this->db->queryf(
            '
			SELECT * FROM frm_thread_access 
			WHERE usr_id = %s
			AND obj_id = %s
			AND thread_id = %s',
            array('integer', 'integer', 'integer'),
            array($a_usr_id, $this->getId(), $a_thread_id)
        );
        $data = $this->db->fetchAssoc($res);

        $this->db->replace(
            'frm_thread_access',
            array(
                'usr_id' => array('integer', $a_usr_id),
                'obj_id' => array('integer', $this->getId()),
                'thread_id' => array('integer', $a_thread_id)
            ),
            array(
                'access_last' => array('integer', time()),
                'access_old' => array('integer', isset($data['access_old']) ? $data['access_old'] : 0),
                'access_old_ts' => array('timestamp', $data['access_old_ts'])
            )
        );

        return true;
    }

    /**
     * @static
     * @param int
     */
    public static function _updateOldAccess($a_usr_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->manipulateF(
            '
			UPDATE frm_thread_access 
			SET access_old = access_last
			WHERE usr_id = %s',
            array('integer'),
            array($a_usr_id)
        );

        $set = $ilDB->query(
            "SELECT * FROM frm_thread_access " .
                " WHERE usr_id = " . $ilDB->quote($a_usr_id, "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $ilDB->manipulate(
                "UPDATE frm_thread_access SET " .
                    " access_old_ts = " . $ilDB->quote(date('Y-m-d H:i:s', $rec["access_old"]), "timestamp") .
                    " WHERE usr_id = " . $ilDB->quote($rec["usr_id"], "integer") .
                    " AND obj_id = " . $ilDB->quote($rec["obj_id"], "integer") .
                    " AND thread_id = " . $ilDB->quote($rec["thread_id"], "integer")
            );
        }

        $new_deadline = time() - 60 * 60 * 24 * 7 * ($DIC->settings()->get('frm_store_new') ?
            $DIC->settings()->get('frm_store_new') :
            8);

        $ilDB->manipulateF(
            '
			DELETE FROM frm_thread_access WHERE access_last < %s',
            array('integer'),
            array($new_deadline)
        );
    }

    public static function _deleteUser($a_usr_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $data = array($a_usr_id);

        $ilDB->manipulateF(
            '
			DELETE FROM frm_user_read WHERE usr_id = %s',
            array('integer'),
            $data
        );

        $ilDB->manipulateF(
            '
			DELETE FROM frm_thread_access WHERE usr_id = %s',
            array('integer'),
            $data
        );

        // delete notifications of deleted user
        $ilDB->manipulateF(
            '
			DELETE FROM frm_notification WHERE user_id = %s',
            array('integer'),
            $data
        );

        return true;
    }


    public static function _deleteReadEntries($a_post_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $ilDB->manipulateF(
            '
			DELETE FROM frm_user_read WHERE post_id = %s',
            array('integer'),
            array($a_post_id)
        );

        return true;
    }

    public static function _deleteAccessEntries($a_thread_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilDB->manipulateF(
            '
			DELETE FROM frm_thread_access WHERE thread_id = %s',
            array('integer'),
            array($a_thread_id)
        );

        return true;
    }

    /**
     * update forum data
     * @access    public
     */
    public function update($a_update_user_id = 0)
    {
        if (!$a_update_user_id) {
            $a_update_user_id = $this->user->getId();
        }
        
        if (parent::update()) {
            $this->db->manipulateF(
                '
				UPDATE frm_data 
				SET top_name = %s,
					top_description = %s,
					top_update = %s,
					update_user = %s
				WHERE top_frm_fk =%s',
                array('text', 'text', 'timestamp', 'integer', 'integer'),
                array(
                    $this->getTitle(),
                    $this->getDescription(),
                    date("Y-m-d H:i:s"),
                    (int) $a_update_user_id,
                    (int) $this->getId()
                )
            );

            return true;
        }

        return false;
    }

    /**
     * Clone Object
     * @param int $a_target_id
     * @param int $a_copy_id
     * @return object
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        /** @var $new_obj ilObjForum */
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
        $this->cloneAutoGeneratedRoles($new_obj);

        ilForumProperties::getInstance($this->getId())->copy($new_obj->getId());
        $this->Forum->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($this->getId()));
        $topData = $this->Forum->getOneTopic();

        $this->db->update('frm_data', array(
            'top_name' => array('text', $topData['top_name']),
            'top_description' => array('text', $topData['top_description']),
            'top_num_posts' => array('integer', $topData['top_num_posts']),
            'top_num_threads' => array('integer', $topData['top_num_threads']),
            'top_last_post' => array('text', $topData['top_last_post']),
            'top_date' => array('timestamp', $topData['top_date']),
            'visits' => array('integer', $topData['visits']),
            'top_update' => array('timestamp', $topData['top_update']),
            'update_user' => array('integer', $topData['update_user']),
            'top_usr_id' => array('integer', $topData['top_usr_id'])
        ), array(
            'top_frm_fk' => array('integer', $new_obj->getId())
        ));

        // read options
        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $options = $cwo->getOptions($this->getRefId());

        $options['threads'] = $this->Forum->_getThreads($this->getId());

        // Generate starting threads
        $new_frm = $new_obj->Forum;
        $new_frm->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($new_obj->getId()));

        $new_frm->setForumId($new_obj->getId());
        $new_frm->setForumRefId($new_obj->getRefId());

        $new_topic = $new_frm->getOneTopic();
        foreach ($options['threads'] as $thread_id => $thread_subject) {
            $this->Forum->setMDB2WhereCondition('thr_pk = %s ', array('integer'), array($thread_id));

            $old_thread = $this->Forum->getOneThread();

            $old_post_id = $this->Forum->getFirstPostByThread($old_thread['thr_pk']);
            $old_post = $this->Forum->getOnePost($old_post_id);

            $newThread = new ilForumTopic(0, true, true);
            $newThread->setSticky($old_thread['is_sticky']);
            $newThread->setForumId($new_topic['top_pk']);
            $newThread->setThrAuthorId($old_thread['thr_author_id']);
            $newThread->setDisplayUserId($old_thread['thr_display_user_id']);
            $newThread->setSubject($old_thread['thr_subject']);
            $newThread->setUserAlias($old_thread['thr_usr_alias']);
            $newThread->setCreateDate($old_thread['thr_date']);

            $old_thread_obj = new ilForumTopic($old_thread['thr_pk']);
            $top_pos_pk = $old_thread_obj->getFirstPostId() ?: $old_post_id;
            $top_pos = new ilForumPost($top_pos_pk);

            $newPostId = $new_frm->generateThread(
                $newThread,
                $top_pos->getMessage(),
                (int) $top_pos->isNotificationEnabled(),
                0,
                1,
                (bool) ($old_thread_obj->getNumPosts() - 1)
            );

            $old_forum_files = new ilFileDataForum($this->getId(), $top_pos_pk);
            $old_forum_files->ilClone($new_obj->getId(), $newPostId);
        }

        $sourceRefId = $this->getRefId();
        $targetRefId = $new_obj->getRefId();

        if (
            $sourceRefId > 0 && $targetRefId > 0 &&
            $this->tree->getParentId($sourceRefId) === $this->tree->getParentId($targetRefId)
        ) {
            $grpRefId = $this->tree->checkForParentType($targetRefId, 'grp');
            $crsRefId = $this->tree->checkForParentType($targetRefId, 'crs');

            if ($grpRefId > 0 || $crsRefId > 0) {
                $notifications = new \ilForumNotification($targetRefId);
                $notifications->cloneFromSource((int) $sourceRefId);
            }
        }

        return $new_obj;
    }

    /**
     * Clone forum moderator role
     * @access public
     * @param object $new_obj forum object

     */
    public function cloneAutoGeneratedRoles($new_obj)
    {
        $moderator = ilObjForum::_lookupModeratorRole($this->getRefId());
        $new_moderator = ilObjForum::_lookupModeratorRole($new_obj->getRefId());

        if (!$moderator || !$new_moderator || !$this->getRefId() || !$new_obj->getRefId()) {
            $this->logger->write(__METHOD__ . ' : Error cloning auto generated role: il_frm_moderator');
        }
        $this->rbac->admin()->copyRolePermissions($moderator, $this->getRefId(), $new_obj->getRefId(), $new_moderator, true);
        $this->logger->write(__METHOD__ . ' : Finished copying of role il_frm_moderator.');

        $obj_mods = new ilForumModerators($this->getRefId());

        $old_mods = $obj_mods->getCurrentModerators();
        foreach ($old_mods as $user_id) {
            // The object owner is already member of the moderator role when this method is called
            // Since the new static caches are introduced with ILIAS 5.0, a database error occurs if we try to assign the user here.
            if ($this->getOwner() != $user_id) {
                $this->rbac->admin()->assignUser($new_moderator, $user_id);
            }
        }
    }

    /**
     * Delete forum and all related data
     * @access    public
     * @return    boolean    true if all object data were removed; false if only a references were removed
     */
    public function delete()
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // delete attachments
        $tmp_file_obj = new ilFileDataForum($this->getId());
        $tmp_file_obj->delete();
        unset($tmp_file_obj);

        $this->Forum->setMDB2WhereCondition('top_frm_fk = %s ', array('integer'), array($this->getId()));

        $topData = $this->Forum->getOneTopic();

        $threads = $this->Forum->getAllThreads($topData['top_pk']);
        foreach ($threads['items'] as $thread) {
            $thread_ids_to_delete[$thread->getId()] = $thread->getId();
        }
        
        // delete tree
        $this->db->manipulate('DELETE FROM frm_posts_tree WHERE ' . $this->db->in('thr_fk', $thread_ids_to_delete, false, 'integer'));
        
        // delete posts
        $this->db->manipulate('DELETE FROM frm_posts WHERE ' . $this->db->in('pos_thr_fk', $thread_ids_to_delete, false, 'integer'));
    
        // delete threads
        $this->db->manipulate('DELETE FROM frm_threads WHERE ' . $this->db->in('thr_pk', $thread_ids_to_delete, false, 'integer'));
        
        $obj_id = array($this->getId());
        // delete forum
        $this->db->manipulateF(
            'DELETE FROM frm_data WHERE top_frm_fk = %s',
            array('integer'),
            $obj_id
        );

        // delete settings
        $this->db->manipulateF(
            'DELETE FROM frm_settings WHERE obj_id = %s',
            array('integer'),
            $obj_id
        );

        // delete read infos
        $this->db->manipulateF(
            'DELETE FROM frm_user_read WHERE obj_id = %s',
            array('integer'),
            $obj_id
        );

        // delete thread access entries
        $this->db->manipulateF(
            'DELETE FROM frm_thread_access WHERE obj_id = %s',
            array('integer'),
            $obj_id
        );

        //delete thread notifications
        $this->db->manipulate('DELETE FROM frm_notification WHERE ' . $this->db->in('thread_id', $thread_ids_to_delete, false, 'integer'));
        
        //delete forum notifications
        $this->db->manipulateF('DELETE FROM frm_notification WHERE  frm_id = %s', array('integer'), $obj_id);
        
        // delete posts_deleted entries
        $this->db->manipulateF('DELETE FROM frm_posts_deleted WHERE obj_id = %s', array('integer'), $obj_id);
        
        //delete drafts
        $this->deleteDraftsByForumId((int) $topData['top_pk']);
            
        return true;
    }

    /**
     * @param int $forum_id
     */
    private function deleteDraftsByForumId($forum_id)
    {
        $res = $this->db->queryF(
            'SELECT draft_id FROM frm_posts_drafts WHERE forum_id = %s',
            array('integer'),
            array((int) $forum_id)
        );
        
        $draft_ids = array();
        while ($row = $this->db->fetchAssoc($res)) {
            $draft_ids[] = $row['draft_id'];
        }

        if (count($draft_ids) > 0) {
            $historyObj = new ilForumDraftsHistory();
            $historyObj->deleteHistoryByDraftIds($draft_ids);
            
            $draftObj = new ilForumPostDraft();
            $draftObj->deleteDraftsByDraftIds($draft_ids);
        }
    }
    
    /**
     * init default roles settings
     * @access    public
     * @return    array    object IDs of created local roles.
     */
    public function initDefaultRoles()
    {
        $role = ilObjRole::createDefaultRole(
            'il_frm_moderator_' . $this->getRefId(),
            "Moderator of forum obj_no." . $this->getId(),
            'il_frm_moderator',
            $this->getRefId()
        );
        return array();
    }

    /**
     * Lookup moderator role
     * @access public
     * @static
     * @param int $a_ref_id ref_id of forum

     */
    public static function _lookupModeratorRole($a_ref_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $mod_title = 'il_frm_moderator_' . $a_ref_id;

        $res = $ilDB->queryf(
            '
			SELECT * FROM object_data WHERE title = %s',
            array('text'),
            array($mod_title)
        );

        while ($row = $ilDB->fetchObject($res)) {
            return $row->obj_id;
        }
        return 0;
    }


    public function createSettings()
    {
        // news settings (public notifications yes/no)
        $default_visibility = ilNewsItem::_getDefaultVisibilityForRefId($_GET["ref_id"]);
        if ($default_visibility == "public") {
            ilBlockSetting::_write("news", "public_notifications", 1, 0, $this->getId());
        }

        return true;
    }

    public function saveData($a_roles = array())
    {
        $nextId = $this->db->nextId('frm_data');

        $top_data = array(
            'top_frm_fk' => $this->getId(),
            'top_name' => $this->getTitle(),
            'top_description' => $this->getDescription(),
            'top_num_posts' => 0,
            'top_num_threads' => 0,
            'top_last_post' => null,
            'top_mods' => !is_numeric($a_roles[0]) ? 0 : $a_roles[0],
            'top_usr_id' => $this->user->getId(),
            'top_date' => ilUtil::now()
        );

        $this->db->manipulateF(
            '
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
            )
        );
    }

    public function setThreadSorting($a_thr_pk, $a_sorting_value)
    {
        $this->db->update(
            'frm_threads',
            array('thread_sorting' => array('integer',$a_sorting_value)),
            array('thr_pk' => array('integer', $a_thr_pk))
        );
    }


    /**
     * @static
     * @param int $obj_id
     * @return int
     */
    public static function lookupForumIdByObjId($obj_id)
    {
        if (array_key_exists($obj_id, self::$obj_id_to_forum_id_cache)) {
            return (int) self::$obj_id_to_forum_id_cache[$obj_id];
        }

        self::preloadForumIdsByObjIds(array($obj_id));

        return (int) self::$obj_id_to_forum_id_cache[$obj_id];
    }

    /**
     * @static
     * @param int $ref_id
     * @return int
     */
    public static function lookupForumIdByRefId($ref_id)
    {
        if (array_key_exists($ref_id, self::$ref_id_to_forum_id_cache)) {
            return (int) self::$ref_id_to_forum_id_cache[$ref_id];
        }

        self::preloadForumIdsByRefIds(array($ref_id));

        return (int) self::$ref_id_to_forum_id_cache[$ref_id];
    }

    /**
     * @static
     * @param array $obj_ids
     */
    public static function preloadForumIdsByObjIds(array $obj_ids)
    {
        global $DIC;
        $ilDB = $DIC->database();

        if (count($obj_ids) == 1) {
            $in = " objr.obj_id = " . $ilDB->quote(current($obj_ids), 'integer') . " ";
        } else {
            $in = $ilDB->in('objr.obj_id', $obj_ids, false, 'integer');
        }
        $query = "
			SELECT frmd.top_pk, objr.ref_id, objr.obj_id
			FROM object_reference objr
			INNER JOIN frm_data frmd ON frmd.top_frm_fk = objr.obj_id
			WHERE $in 
		";
        $res = $ilDB->query($query);

        // Prepare  cache array
        foreach ($obj_ids as $obj_id) {
            self::$obj_id_to_forum_id_cache[$obj_id] = null;
        }

        while ($row = $ilDB->fetchAssoc($res)) {
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
        global $DIC;
        $ilDB = $DIC->database();
        
        if (count($ref_ids) == 1) {
            $in = " objr.ref_id = " . $ilDB->quote(current($ref_ids), 'integer') . " ";
        } else {
            $in = $ilDB->in('objr.ref_id', $ref_ids, false, 'integer');
        }
        $query = "
			SELECT frmd.top_pk, objr.ref_id, objr.obj_id
			FROM object_reference objr
			INNER JOIN frm_data frmd ON frmd.top_frm_fk = objr.obj_id
			WHERE $in 
		";
        $res = $ilDB->query($query);

        // Prepare  cache array
        foreach ($ref_ids as $ref_id) {
            self::$ref_id_to_forum_id_cache[$ref_id] = null;
        }

        while ($row = $ilDB->fetchAssoc($res)) {
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
        global $DIC;
        $ilAccess = $DIC->access();
        $ilUser = $DIC->user();
        $ilDB = $DIC->database();
        $ilSetting = $DIC->settings();

        if (isset(self::$forum_statistics_cache[$ref_id])) {
            return self::$forum_statistics_cache[$ref_id];
        }

        $statistics = array(
            'num_posts' => 0,
            'num_unread_posts' => 0,
            'num_new_posts' => 0
        );

        $forumId = self::lookupForumIdByRefId($ref_id);
        if (!$forumId) {
            self::$forum_statistics_cache[$ref_id] = $statistics;
            return self::$forum_statistics_cache[$ref_id];
        }
        
        $objProperties = ilForumProperties::getInstance(ilObject::_lookupObjectId($ref_id));
        $is_post_activation_enabled = $objProperties->isPostActivationEnabled();

        $act_clause = '';

        if ($is_post_activation_enabled && !$ilAccess->checkAccess('moderate_frm', '', $ref_id)) {
            $act_clause .= " AND (frm_posts.pos_status = " . $ilDB->quote(1, "integer") . " OR frm_posts.pos_author_id = " . $ilDB->quote($ilUser->getId(), "integer") . ") ";
        }

        $new_deadline = date('Y-m-d H:i:s', time() - 60 * 60 * 24 * 7 * ($ilSetting->get('frm_store_new') ? $ilSetting->get('frm_store_new') : 8));

        if (!$ilUser->isAnonymous()) {
            $query = "
				(SELECT COUNT(frm_posts.pos_pk) cnt
				FROM frm_posts
				INNER JOIN frm_posts_tree tree1
					ON tree1.pos_fk = frm_posts.pos_pk
					AND tree1.parent_pos != 0
				INNER JOIN frm_threads ON frm_posts.pos_thr_fk = frm_threads.thr_pk 
				WHERE frm_threads.thr_top_fk = %s $act_clause)
				
				UNION ALL
				 
				(SELECT COUNT(DISTINCT(frm_user_read.post_id)) cnt
				FROM frm_user_read
				INNER JOIN frm_posts ON frm_user_read.post_id = frm_posts.pos_pk
				INNER JOIN frm_posts_tree tree1
					ON tree1.pos_fk = frm_posts.pos_pk
					AND tree1.parent_pos != 0
				INNER JOIN frm_threads ON frm_threads.thr_pk = frm_posts.pos_thr_fk 
				WHERE frm_user_read.usr_id = %s AND frm_posts.pos_top_fk = %s $act_clause)
			";

            $types = array('integer', 'integer', 'integer');
            $values = array($forumId, $ilUser->getId(), $forumId);

            $forum_overview_setting = (int) $ilSetting::_lookupValue('frma', 'forum_overview');
            if ($forum_overview_setting == ilForumProperties::FORUM_OVERVIEW_WITH_NEW_POSTS) {
                $news_types = array('integer', 'integer', 'integer', 'timestamp', 'integer');
                $news_values = array($ilUser->getId(), $ilUser->getId(), $forumId,  $new_deadline, $ilUser->getId());

                $query .= " 
				UNION ALL
				
				(SELECT COUNT(frm_posts.pos_pk) cnt
				FROM frm_posts
				INNER JOIN frm_posts_tree tree1
					ON tree1.pos_fk = frm_posts.pos_pk
					AND tree1.parent_pos != 0
				LEFT JOIN frm_user_read ON (post_id = frm_posts.pos_pk AND frm_user_read.usr_id = %s)
				LEFT JOIN frm_thread_access ON (frm_thread_access.thread_id = frm_posts.pos_thr_fk AND frm_thread_access.usr_id = %s)
				WHERE frm_posts.pos_top_fk = %s
				AND ( (frm_posts.pos_update > frm_thread_access.access_old_ts)
						OR (frm_thread_access.access_old IS NULL AND frm_posts.pos_update > %s)
					)
				AND frm_posts.pos_author_id != %s 
				AND frm_user_read.usr_id IS NULL $act_clause)";
                
                $types = array_merge($types, $news_types);
                $values = array_merge($values, $news_values);
            }
            
            $mapping = array_keys($statistics);
            $res = $ilDB->queryF(
                $query,
                $types,
                $values
            );
            for ($i = 0; $i <= 2; $i++) {
                $row = $ilDB->fetchAssoc($res);

                $statistics[$mapping[$i]] = (int) ((is_array($row) ? $row['cnt'] : 0));

                if ($i == 1) {
                    // unread = all - read
                    $statistics[$mapping[$i]] = $statistics[$mapping[$i - 1]] - $statistics[$mapping[$i]];
                }
            }
        } else {
            $query = "
				SELECT COUNT(frm_posts.pos_pk) cnt
				FROM frm_posts
				INNER JOIN frm_posts_tree tree1
					ON tree1.pos_fk = frm_posts.pos_pk
					AND tree1.parent_pos != 0
				INNER JOIN frm_threads ON frm_posts.pos_thr_fk = frm_threads.thr_pk 
				WHERE frm_threads.thr_top_fk = %s $act_clause
			";
            $types = array('integer');
            $values = array($forumId);
            $res = $ilDB->queryF(
                $query,
                $types,
                $values
            );
            $row = $ilDB->fetchAssoc($res);

            $statistics = array(
                'num_posts' => $row['cnt'],
                'num_unread_posts' => $row['cnt'],
                'num_new_posts' => $row['cnt']
            );
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
        global $DIC;
        $ilAccess = $DIC->access();
        $ilUser = $DIC->user();
        $ilDB = $DIC->database();
        
        if (isset(self::$forum_last_post_cache[$ref_id])) {
            return self::$forum_last_post_cache[$ref_id];
        }

        $forumId = self::lookupForumIdByRefId($ref_id);
        if (!$forumId) {
            self::$forum_last_post_cache[$ref_id] = array();
            return self::$forum_last_post_cache[$ref_id];
        }

        $act_clause = '';
        if (!$ilAccess->checkAccess('moderate_frm', '', $ref_id)) {
            $act_clause .= " AND (frm_posts.pos_status = " . $ilDB->quote(1, "integer") . " OR frm_posts.pos_author_id = " . $ilDB->quote($ilUser->getId(), "integer") . ") ";
        }

        $ilDB->setLimit(1, 0);
        $query = "
			SELECT *
			FROM frm_posts 
			INNER JOIN frm_posts_tree tree1
					ON tree1.pos_fk = frm_posts.pos_pk
					AND tree1.parent_pos != 0
			WHERE pos_top_fk = %s $act_clause
			ORDER BY pos_date DESC
		";
        $res = $ilDB->queryF(
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
        global $DIC;
        $ilAccess = $DIC->access();
        $ilUser = $DIC->user();
        $ilDB = $DIC->database();

        $act_clause = '';
        $act_inner_clause = '';
        if (!$ilAccess->checkAccess('moderate_frm', '', $ref_id)) {
            $act_clause .= " AND (t1.pos_status = " . $ilDB->quote(1, "integer") . " OR t1.pos_author_id = " . $ilDB->quote($ilUser->getId(), "integer") . ") ";
            $act_inner_clause .= " AND (t3.pos_status = " . $ilDB->quote(1, "integer") . " OR t3.pos_author_id = " . $ilDB->quote($ilUser->getId(), "integer") . ") ";
        }

        $in = $ilDB->in("t1.pos_thr_fk", $thread_ids, false, 'integer');
        $inner_in = $ilDB->in("t3.pos_thr_fk", $thread_ids, false, 'integer');
 
        $query = "
			SELECT t1.pos_display_user_id, t1.update_user
			FROM frm_posts t1
			INNER JOIN frm_posts_tree tree1 ON tree1.pos_fk = t1.pos_pk AND tree1.parent_pos != 0 
			INNER JOIN (
				SELECT t3.pos_thr_fk, MAX(t3.pos_date) pos_date
				FROM frm_posts t3
				INNER JOIN frm_posts_tree tree2 ON tree2.pos_fk = t3.pos_pk AND tree2.parent_pos != 0 
				WHERE $inner_in $act_inner_clause
				GROUP BY t3.pos_thr_fk
			) t2 ON t2.pos_thr_fk = t1.pos_thr_fk AND t2.pos_date = t1.pos_date
			WHERE $in $act_clause
			GROUP BY t1.pos_thr_fk, t1.pos_display_user_id, t1.update_user
		";
        ///
        $usr_ids = array();

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            if ((int) $row['pos_display_user_id']) {
                $usr_ids[] = (int) $row['pos_display_user_id'];
            }
            if ((int) $row['update_user']) {
                $usr_ids[] = (int) $row['update_user'];
            }
        }

        return array_unique($usr_ids);
    }
    
    public static function mergeForumUserRead($merge_source_thread_id, $merge_target_thread_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $ilDB->update(
            'frm_user_read',
            array('thread_id' => array('integer', $merge_target_thread_id)),
            array('thread_id' => array('integer',$merge_source_thread_id))
        );
    }
    
    public function getNumStickyThreads() : int
    {
        $res = $this->db->query(
            'SELECT COUNT(is_sticky) num_sticky FROM frm_threads
            INNER JOIN frm_data ON top_pk = thr_top_fk
            WHERE frm_data.top_frm_fk = ' . $this->db->quote($this->getId(), 'integer') . '
            AND is_sticky = ' . $this->db->quote(1, 'integer')
        );
        if ($row = $this->db->fetchAssoc($res)) {
            return (int) $row['num_sticky'];
        }
        return 0;
    }
}
