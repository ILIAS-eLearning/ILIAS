<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ilForumPostDraft
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumPostDraft
{
    const MEDIAOBJECT_TYPE = 'frm~d:html';
    /**
     * @var int
     */
    protected $draft_id = 0;
    /**
     * @var int
     */
    protected $post_id = 0;
    /**
     * @var int
     */
    protected $forum_id = 0;
    /**
     * @var int
     */
    protected $thread_id = 0;
    /**
     * @var string
     */
    protected $post_subject = '';
    /**
     * @var string
     */
    protected $post_message = '';
    /**
     * @var string
     */
    protected $post_date = '0000-00-00 00:00:00';
    /**
     * @var string
     */
    protected $post_update = '0000-00-00 00:00:00';
    /**
     * @var int
     */
    protected $update_user_id = 0;
    /**
     * @var string
     */
    protected $post_user_alias = '';
    /**
     * @var int
     */
    protected $post_author_id = 0;
    /**
     * @var int
     */
    protected $post_display_user_id = 0;
    
    /**
     * @var int
     */
    protected $notify = 0;
    protected $post_notify = 0;
    
    /**
     * @var array
     */
    private static $instances = array();
    
    /**
     * @var array
     * @static
     */
    protected static $forum_statistics_cache = array();
    
    /**
     * @var array
     */
    protected static $drafts_settings_cache = array();

    /**
     * @param $draft ilForumPostDraft
     * @param $row   array
     */
    protected static function populateWithDatabaseRecord(ilForumPostDraft $draft, array $row)
    {
        $draft->setDraftId($row['draft_id']);
        $draft->setForumId($row['forum_id']);
        $draft->setPostAuthorId($row['post_author_id']);
        $draft->setPostDate($row['post_date']);
        $draft->setPostDisplayUserId($row['pos_display_usr_id']);
        $draft->setPostId($row['post_id']);
        $draft->setPostMessage($row['post_message']);
        $draft->setPostSubject($row['post_subject']);
        $draft->setPostUpdate($row['post_update']);
        $draft->setPostUserAlias($row['post_user_alias']);
        $draft->setThreadId($row['thread_id']);
        $draft->setUpdateUserId($row['update_user_id']);
        $draft->setNotify($row['notify']);
        $draft->setPostNotify($row['post_notify']);
    }
    
    /**
     * @return int
     */
    public function getPostNotify()
    {
        return $this->post_notify;
    }
    
    /**
     * @param int $post_notify
     */
    public function setPostNotify($post_notify)
    {
        $this->post_notify = $post_notify;
    }

    /**
     * @return int
     */
    public function getDraftId()
    {
        return $this->draft_id;
    }
    
    /**
     * @param int $draft_id
     */
    public function setDraftId($draft_id)
    {
        $this->draft_id = $draft_id;
    }
    
    /**
     * @return int
     */
    public function getPostId()
    {
        return $this->post_id;
    }
    
    /**
     * @param int $post_id
     */
    public function setPostId($post_id)
    {
        $this->post_id = $post_id;
    }
    
    /**
     * @return int
     */
    public function getForumId()
    {
        return $this->forum_id;
    }
    
    /**
     * @param int $forum_id
     */
    public function setForumId($forum_id)
    {
        $this->forum_id = $forum_id;
    }
    
    /**
     * @return int
     */
    public function getThreadId()
    {
        return $this->thread_id;
    }
    
    /**
     * @param int $thread_id
     */
    public function setThreadId($thread_id)
    {
        $this->thread_id = $thread_id;
    }
    
    /**
     * @return string
     */
    public function getPostSubject()
    {
        return $this->post_subject;
    }
    
    /**
     * @param string $post_subject
     */
    public function setPostSubject($post_subject)
    {
        $this->post_subject = $post_subject;
    }
    
    /**
     * @return string
     */
    public function getPostMessage()
    {
        return $this->post_message;
    }
    
    /**
     * @param string $post_message
     */
    public function setPostMessage($post_message)
    {
        $this->post_message = $post_message;
    }
    
    /**
     * @return string
     */
    public function getPostDate()
    {
        return $this->post_date;
    }
    
    /**
     * @param string $post_date
     */
    public function setPostDate($post_date)
    {
        $this->post_date = $post_date;
    }
    
    /**
     * @return string
     */
    public function getPostUpdate()
    {
        return $this->post_update;
    }
    
    /**
     * @param string $post_update
     */
    public function setPostUpdate($post_update)
    {
        $this->post_update = $post_update;
    }
    
    /**
     * @return int
     */
    public function getUpdateUserId()
    {
        return $this->update_user_id;
    }
    
    /**
     * @param int $update_user_id
     */
    public function setUpdateUserId($update_user_id)
    {
        $this->update_user_id = $update_user_id;
    }
    
    /**
     * @return string
     */
    public function getPostUserAlias()
    {
        return $this->post_user_alias;
    }
    
    /**
     * @param string $post_user_alias
     */
    public function setPostUserAlias($post_user_alias)
    {
        $this->post_user_alias = $post_user_alias;
    }
    
    /**
     * @return int
     */
    public function getPostAuthorId()
    {
        return $this->post_author_id;
    }
    
    /**
     * @param int $post_author_id
     */
    public function setPostAuthorId($post_author_id)
    {
        $this->post_author_id = $post_author_id;
    }
    
    /**
     * @return int
     */
    public function getPostDisplayUserId()
    {
        return $this->post_display_user_id;
    }
    
    /**
     * @param int $post_display_user_id
     */
    public function setPostDisplayUserId($post_display_user_id)
    {
        $this->post_display_user_id = $post_display_user_id;
    }
    
    /**
     * @return int
     */
    public function getNotify()
    {
        return $this->notify;
    }
    
    /**
     * @param int $notify
     */
    public function setNotify($notify)
    {
        $this->notify = $notify;
    }
    
    /**
     * ilForumPostDraft constructor.
     * @param int $user_id
     * @param int $post_id
     */
    public function __construct($user_id = 0, $post_id = 0, $draft_id = 0)
    {
        global $DIC;
        
        $this->db = $DIC->database();
        
        if ($user_id && $post_id && $draft_id) {
            $this->setPostAuthorId($user_id);
            $this->setPostId($post_id);
            $this->setDraftId($draft_id);
            $this->readDraft();
        }
    }
    
    /**
     *
     */
    protected function readDraft()
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_posts_drafts WHERE post_author_id = %s AND post_id = %s AND draft_id = %s',
            array('integer', 'integer','integer'),
            array($this->getPostAuthorId(), $this->getPostId(), $this->getDraftId())
        );
        
        while ($row = $this->db->fetchAssoc($res)) {
            self::populateWithDatabaseRecord($this, $row);
        }
    }
    
    /**
     * @param int $user_id
     */
    protected static function readDrafts($user_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $res = $ilDB->queryF(
            'SELECT * FROM frm_posts_drafts WHERE post_author_id = %s',
            array('integer'),
            array($user_id)
        );
        
        self::$instances[$user_id] = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $tmp_obj = new ilForumPostDraft();
            self::populateWithDatabaseRecord($tmp_obj, $row);
            self::$instances[$user_id][$row['thread_id']][$tmp_obj->getPostId()][] = $tmp_obj;
            self::$instances[$user_id]['draft_ids'][$tmp_obj->getDraftId()] = $tmp_obj;
        }
        unset($tmp_obj);
    }
    
    /**
     * @param int $user_id
     * @return mixed
     */
    public static function getDraftInstancesByUserId($user_id)
    {
        if (!self::$instances[$user_id]) {
            self::readDrafts($user_id);
        }
        
        return self::$instances[$user_id]['draft_ids'];
    }
    
    /**
     * @param $user_id
     * @param $thread_id
     * @return \ilForumPostDraft[]
     */
    public static function getInstancesByUserIdAndThreadId($user_id, $thread_id) : array
    {
        if (!self::$instances[$user_id]) {
            self::readDrafts($user_id);
        }

        if (isset(self::$instances[$user_id][$thread_id])) {
            return self::$instances[$user_id][$thread_id];
        }

        return [];
    }

    /**
     * @param $draft_id
     * @return self
     */
    public static function newInstanceByDraftId($draft_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT * FROM frm_posts_drafts WHERE draft_id = %s',
            array('integer'),
            array($draft_id)
        );
        
        $tmp_obj = new ilForumPostDraft();
        while ($row = $ilDB->fetchAssoc($res)) {
            self::populateWithDatabaseRecord($tmp_obj, $row);
        }
        return $tmp_obj;
    }

    /**
     * @param $history_id
     * @return ilForumPostDraft
     * @throws ilException
     */
    public static function newInstanceByHistorytId($history_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT * FROM frm_drafts_history WHERE history_id = %s',
            array('integer'),
            array($history_id)
        );

        while ($row = $ilDB->fetchAssoc($res)) {
            $tmp_obj = new ilForumPostDraft();
            self::populateWithDatabaseRecord($tmp_obj, $row);
            return $tmp_obj;
        }

        throw new ilException(sprintf("Could not find history object for id %s", $history_id));
    }

    public function saveDraft()
    {
        $draft_id = $this->db->nextId('frm_posts_drafts');
        $post_date = date("Y-m-d H:i:s");
        
        $this->db->insert('frm_posts_drafts', array(
            'draft_id' => array('integer', $draft_id),
            'post_id' => array('integer', $this->getPostId()),
            'thread_id' => array('integer', $this->getThreadId()),
            'forum_id' => array('integer', $this->getForumId()),
            'post_author_id' => array('integer', $this->getPostAuthorId()),
            'post_subject' => array('text', $this->getPostSubject()),
            'post_message' => array('clob', $this->getPostMessage()),
            'notify' => array('integer', $this->getNotify()),
            'post_notify' => array('integer', $this->getPostNotify()),
            'post_date' => array('timestamp', $post_date),
            'post_update' => array('timestamp', $post_date),
//			'update_user_id' => array('integer', $this->getUpdateUserId()),
            'post_user_alias' => array('text', $this->getPostUserAlias()),
            'pos_display_usr_id' => array('integer', $this->getPostDisplayUserId())
        ));
        $this->setDraftId($draft_id);
        return $draft_id;
    }
    
    public function updateDraft()
    {
        $this->db->update(
            'frm_posts_drafts',
            array(
            'post_subject' => array('text', $this->getPostSubject()),
            'post_message' => array('clob', $this->getPostMessage()),
            'notify' => array('integer', $this->getNotify()),
            'post_notify' => array('integer', $this->getPostNotify()),
            'post_update' => array('timestamp', date("Y-m-d H:i:s")),
            'update_user_id' => array('integer', $this->getUpdateUserId()),
            'post_user_alias' => array('text', $this->getPostUserAlias()),
            'pos_display_usr_id' => array('integer', $this->getPostDisplayUserId())
        ),
            array('draft_id' => array('integer', $this->getDraftId()))
        );
    }
    
    public function deleteDraft()
    {
        $this->db->manipulateF(
            'DELETE FROM frm_posts_drafts WHERE draft_id = %s',
            array('integer'),
            array($this->getDraftId())
        );
    }
    
    /**
     * @param $draft_id
     */
    public static function deleteMobsOfDraft($draft_id)
    {
        // delete mobs of draft
        $oldMediaObjects = ilObjMediaObject::_getMobsOfObject('frm~d:html', $draft_id);
        foreach ($oldMediaObjects as $oldMob) {
            if (ilObjMediaObject::_exists($oldMob)) {
                ilObjMediaObject::_removeUsage($oldMob, 'frm~d:html', $draft_id);
                $mob_obj = new ilObjMediaObject($oldMob);
                $mob_obj->delete();
            }
        }
    }

    /**
     * @param array $post_ids
     */
    public function deleteDraftsByPostIds(array $post_ids = array())
    {
        $draft_ids = array();
        $res = $this->db->query('SELECT draft_id FROM frm_posts_drafts WHERE ' . $this->db->in('post_id', $post_ids, false, 'integer'));
        while ($row = $this->db->fetchAssoc($res)) {
            $draft_ids[] = $row['draft_id'];
        }
        
        foreach ($draft_ids as $draft_id) {
            self::deleteMobsOfDraft($draft_id);

            // delete attachments of draft
            $objFileDataForumDrafts = new ilFileDataForumDrafts(0, $draft_id);
            $objFileDataForumDrafts->delete();
        }
        $this->db->manipulate('DELETE FROM frm_drafts_history WHERE ' . $this->db->in('draft_id', $draft_ids, false, 'integer'));
        $this->db->manipulate('DELETE FROM frm_posts_drafts WHERE ' . $this->db->in('draft_id', $draft_ids, false, 'integer'));
    }
    
    /**
     * @param array $draft_ids
     */
    public function deleteDraftsByDraftIds(array $draft_ids = array())
    {
        foreach ($draft_ids as $draft_id) {
            self::deleteMobsOfDraft($draft_id);
            
            // delete attachments of draft
            $objFileDataForumDrafts = new ilFileDataForumDrafts(0, $draft_id);
            $objFileDataForumDrafts->delete();
        }
        $this->db->manipulate('DELETE FROM frm_drafts_history WHERE ' . $this->db->in('draft_id', $draft_ids, false, 'integer'));
        $this->db->manipulate('DELETE FROM frm_posts_drafts WHERE ' . $this->db->in('draft_id', $draft_ids, false, 'integer'));
    }
    
    /**
     * @param int $user_id
     */
    public static function deleteDraftsByUserId($user_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $res = $ilDB->queryF(
            'SELECT draft_id FROM frm_posts_drafts WHERE post_author_id = %s',
            array('integer'),
            array($user_id)
        );
        
        $draft_ids = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $draft_ids[] = $row['draft_id'];
        }
        
        foreach ($draft_ids as $draft_id) {
            self::deleteMobsOfDraft($draft_id);
            
            // delete attachments of draft
            $objFileDataForumDrafts = new ilFileDataForumDrafts(0, $draft_id);
            $objFileDataForumDrafts->delete();
        }
        
        $ilDB->manipulate('DELETE FROM frm_drafts_history WHERE ' . $ilDB->in('draft_id', $draft_ids, false, 'integer'));
        $ilDB->manipulateF(
            'DELETE FROM frm_posts_drafts WHERE post_author_id = %s',
            array('integer'),
            array($user_id)
        );
    }
    
    /**
     * @return bool
     */
    public static function isSavePostDraftAllowed()
    {
        if (!isset(self::$drafts_settings_cache['save_post_drafts'])) {
            global $DIC;
            self::$drafts_settings_cache['save_post_drafts'] = (bool) $DIC->settings()->get('save_post_drafts', false);
        }
        return self::$drafts_settings_cache['save_post_drafts'];
    }
    
    /**
     * @return bool
     */
    public static function isAutoSavePostDraftAllowed()
    {
        if (!self::isSavePostDraftAllowed()) {
            // feature is globally deactivated
            return false;
        }
        if (!isset(self::$drafts_settings_cache['autosave_drafts'])) {
            global $DIC;
            
            self::$drafts_settings_cache['autosave_drafts'] = (bool) $DIC->settings()->get('autosave_drafts', false);
            self::$drafts_settings_cache['autosave_drafts_ival'] = (int) $DIC->settings()->get('autosave_drafts_ival', 30);
        }
        return self::$drafts_settings_cache['autosave_drafts'];
    }
    
    public static function lookupAutosaveInterval()
    {
        if (self::isAutoSavePostDraftAllowed()) {
            return self::$drafts_settings_cache['autosave_drafts_ival'];
        }
        return 0;
    }
    
    /**
     * @param $ref_id
     * @return mixed
     */
    public static function getDraftsStatisticsByRefId($ref_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilUser = $DIC->user();
        
        if (!isset(self::$forum_statistics_cache[$ref_id][$ilUser->getId()])) {
            $forumId = ilObjForum::lookupForumIdByRefId($ref_id);
            
            $res = $ilDB->queryF(
                '
				SELECT COUNT(draft_id) num_drafts, thread_id FROM frm_posts_drafts 
				WHERE forum_id = %s AND post_author_id = %s
				GROUP BY thread_id',
                array('integer', 'integer'),
                array($forumId, $ilUser->getId())
            );
            
            $num_drafts_total = 0;
            
            while ($row = $ilDB->fetchAssoc($res)) {
                $num_drafts_total += $row['num_drafts'];
                self::$forum_statistics_cache[$ref_id][$ilUser->getId()][$row['thread_id']] = $row['num_drafts'];
            }
            
            self::$forum_statistics_cache[$ref_id][$ilUser->getId()]['total'] = $num_drafts_total;
        }
        return self::$forum_statistics_cache[$ref_id][$ilUser->getId()];
    }
    
    /**
     * @param $source_thread_id
     * @param $target_thread_id
     */
    public static function moveDraftsByMergedThreads($source_thread_id, $target_thread_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $ilDB->update(
            'frm_posts_drafts',
            array('thread_id' => array('integer', $target_thread_id)),
            array('thread_id' => array('integer', $source_thread_id))
        );
    }
    
    /**
     * @param array $thread_ids
     * @param int $source_ref_id
     * @param int $target_ref_id
     */
    public static function moveDraftsByMovedThread($thread_ids, $source_ref_id, $target_ref_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $source_forum_id = ilObjForum::lookupForumIdByRefId($source_ref_id);
        $target_forum_id = ilObjForum::lookupForumIdByRefId($target_ref_id);
        
        $ilDB->manipulateF(
            '
			UPDATE 	frm_posts_drafts 
			SET 	forum_id = %s 
			WHERE 	forum_id = %s 
			AND ' . $ilDB->in('thread_id', $thread_ids, false, 'integer'),
            array('integer', 'integer'),
            array($target_forum_id, $source_forum_id)
        );
    }
    
    /**
     * @param $post_author_id
     * @param $forum_id
     * @return array
     */
    public static function getThreadDraftData($post_author_id, $forum_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $res = $ilDB->queryF(
            'SELECT * FROM frm_posts_drafts 
				WHERE post_author_id = %s
				AND forum_id = %s 
				AND thread_id = %s
				AND post_id = %s
				ORDER BY post_date DESC',
            array('integer', 'integer', 'integer', 'integer'),
            array($post_author_id, $forum_id, 0, 0)
        );
        $draft_data = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $tmp_obj = new self;
            self::populateWithDatabaseRecord($tmp_obj, $row);
            $draft_data[] = array('subject' => $tmp_obj->getPostSubject(), 'post_update' => $tmp_obj->getPostUpdate(), 'draft_id' => $tmp_obj->getDraftId());
        }
        return $draft_data;
    }
    
    /**
     * @param $draft_id
     */
    public static function createDraftBackup($draft_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $res = $ilDB->queryF(
            'SELECT * FROM frm_posts_drafts WHERE draft_id = %s',
            array('integer'),
            array((int) $draft_id)
        );
        
        while ($row = $ilDB->fetchAssoc($res)) {
            $tmp_obj = new self;
            self::populateWithDatabaseRecord($tmp_obj, $row);
        }
        
        $history_obj = new ilForumDraftsHistory();
        $history_obj->deleteHistoryByDraftIds(array($draft_id));
        
        $history_obj->setDraftId($draft_id);
        $history_obj->setPostSubject($tmp_obj->getPostSubject());
        $history_obj->setPostMessage($tmp_obj->getPostMessage());
        $history_obj->addDraftToHistory();
        
        ilForumUtil::moveMediaObjects(
            $tmp_obj->getPostMessage(),
            self::MEDIAOBJECT_TYPE,
            $draft_id,
            ilForumDraftsHistory::MEDIAOBJECT_TYPE,
            $history_obj->getHistoryId()
        );
    }
}
