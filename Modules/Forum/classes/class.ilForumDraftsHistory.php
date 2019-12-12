<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Class ilForumDraftHistory
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumDraftsHistory
{
    const MEDIAOBJECT_TYPE = 'frm~h:html';
    
    /**
     * @var int
     */
    protected $history_id = 0;
    /**
     * @var int
     */
    protected $draft_id = 0;
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
    protected $draft_date = '0000-00-00 00:00:00';
    
    public $db;
    
    /**
     * @return int
     */
    public function getHistoryId()
    {
        return $this->history_id;
    }
    
    /**
     * @param int $history_id
     */
    public function setHistoryId($history_id)
    {
        $this->history_id = $history_id;
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
    public function getDraftDate()
    {
        return $this->draft_date;
    }
    
    /**
     * @param string $draft_date
     */
    public function setDraftDate($draft_date)
    {
        $this->draft_date = $draft_date;
    }
    
    /**
     * ilForumDraftsHistory constructor.
     * @param int $history_id
     */
    public function __construct($history_id = 0)
    {
        global $DIC;
        $this->db = $DIC->database();
        
        if (isset($history_id) && $history_id > 0) {
            $this->readByHistoryId($history_id);
        }
    }

    /**
     * @param $history_id
     * @return ilForumDraftsHistory
     */
    private function readByHistoryId($history_id)
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_drafts_history WHERE history_id = %s',
            array('integer'),
            array((int) $history_id)
        );
        
        while ($row = $this->db->fetchAssoc($res)) {
            $this->setHistoryId($row['history_id']);
            $this->setDraftId($row['draft_id']);
            $this->setPostMessage($row['post_message']);
            $this->setPostSubject($row['post_subject']);
            $this->setDraftDate($row['draft_date']);
        }
    }
    
    public static function getInstancesByDraftId($draft_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $res = $ilDB->queryF(
            'SELECT * FROM frm_drafts_history WHERE draft_id = %s ORDER BY draft_date DESC',
            array('integer'),
            array((int) $draft_id)
        );
        $instances = array();
        while ($row = $ilDB->fetchAssoc($res)) {
            $tmp_obj = new self;
            $tmp_obj = self::populateWithDatabaseRecord($tmp_obj, $row);
        
            $instances[] = $tmp_obj;
        }
        unset($tmp_obj);
        return $instances;
    }
    
    /**
     * @param ilForumDraftsHistory $history_draft
     * @param array                $row
     * @return ilForumDraftsHistory
     */
    protected static function populateWithDatabaseRecord(ilForumDraftsHistory $history_draft, array $row)
    {
        $history_draft->setHistoryId($row['history_id']);
        $history_draft->setDraftId($row['draft_id']);
        $history_draft->setPostMessage($row['post_message']);
        $history_draft->setPostSubject($row['post_subject']);
        $history_draft->setDraftDate($row['draft_date']);
        
        return $history_draft;
    }
    
    public function delete()
    {
        $this->db->manipulatef(
            'DELETE FROM frm_drafts_history WHERE history_id = %s',
            array('integer'),
            array($this->getHistoryId())
        );
    }
    
    /**
     * @param $draft_id
     */
    public function getFirstAutosaveByDraftId($draft_id)
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_drafts_history WHERE draft_id = %s 
			ORDER BY history_id ASC',
            array('integer'),
            array((int) $draft_id)
        );
        
        if ($row = $this->db->fetchAssoc($res)) {
            $this->setHistoryId($row['history_id']);
            $this->setDraftId($row['draft_id']);
            $this->setPostSubject($row['post_subject']);
            $this->setPostMessage($row['post_message']);
        }
    }
    /**
     * @param $draft_id
     */
    public function getLastAutosaveByDraftId($draft_id)
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_drafts_history WHERE draft_id = %s 
			ORDER BY history_id DESC',
            array('integer'),
            array($draft_id)
        );
        
        while ($row = $this->db->fetchAssoc($res)) {
            $this->setHistoryId($row['history_id']);
            $this->setDraftId($row['draft_id']);
            $this->setPostSubject($row['post_subject']);
            $this->setPostMessage($row['post_message']);
        }
    }
    
    public function addDraftToHistory()
    {
        $next_id = $this->db->nextId('frm_drafts_history');
        $this->db->insert(
            'frm_drafts_history',
            array('history_id'   => array('integer', $next_id),
                  'draft_id'     => array('integer', $this->getDraftId()),
                  'post_subject' => array('text', $this->getPostSubject()),
                  'post_message' => array('text', $this->getPostMessage()),
                  'draft_date'   => array('timestamp', date("Y-m-d H:i:s"))
            )
        );
        $this->setHistoryId($next_id);
    }
    
    public function addMobsToDraftsHistory($message)
    {
        // copy temporary media objects (frm~)
        $mediaObjects = ilRTE::_getMediaObjects($this->getPostMessage(), 0);
        
        $myMediaObjects = ilObjMediaObject::_getMobsOfObject('frm~h:html', $this->getHistoryId());
        foreach ($mediaObjects as $mob) {
            foreach ($myMediaObjects as $myMob) {
                if ($mob == $myMob) {
                    // change usage
                    ilObjMediaObject::_removeUsage($mob, 'frm~h:html', $this->getHistoryId());
                    break;
                }
            }
            ilObjMediaObject::_saveUsage($mob, 'frm~h:html', $this->getHistoryId());
        }
    }
    
    public function deleteMobs()
    {
        // delete mobs of draft history
        $oldMediaObjects = ilObjMediaObject::_getMobsOfObject('frm~h:html', $this->getHistoryId());
        foreach ($oldMediaObjects as $oldMob) {
            if (ilObjMediaObject::_exists($oldMob)) {
                ilObjMediaObject::_removeUsage($oldMob, 'frm~h:html', $this->getHistoryId());
                $mob_obj = new ilObjMediaObject($oldMob);
                $mob_obj->delete();
            }
        }
    }
    
    public function rollbackAutosave()
    {
        $draft = ilForumPostDraft::newInstanceByDraftId($this->getDraftId());
        $draft->setPostSubject($this->getPostSubject());
        $draft->setPostMessage($this->getPostMessage());
        
        ilForumUtil::moveMediaObjects(
            $this->getPostMessage(),
            self::MEDIAOBJECT_TYPE,
            $this->getHistoryId(),
            ilForumPostDraft::MEDIAOBJECT_TYPE,
            $draft->getDraftId()
        );
        
        $draft->updateDraft();
        $this->deleteHistoryByDraftIds(array($draft->getDraftId()));
        
        return $draft;
    }
    
    /**
     * @param array $post_ids
     */
    public function deleteHistoryByPostIds($post_ids = array())
    {
        $draft_ids = array();
        if (count($post_ids) > 0) {
            $res  = $this->db->query('
			SELECT frm_drafts_history.history_id, frm_drafts_history.draft_id 
			FROM frm_posts_drafts  
 			INNER JOIN frm_drafts_history ON frm_posts_drafts.draft_id
 			WHERE ' . $this->db->in('post_id', $post_ids, false, 'integer'));
            
            while ($row = $this->db->fetchAssoc($res)) {
                $draft_ids[] = $row['draft_id'];
            }
            
            $this->deleteHistoryByDraftIds($draft_ids);
        }
        return $draft_ids;
    }
    
    public function deleteHistoryByDraftIds($draft_ids = array())
    {
        if (count($draft_ids) > 0) {
            $res  = $this->db->query('SELECT history_id FROM frm_drafts_history 
 					WHERE ' . $this->db->in('draft_id', $draft_ids, false, 'integer'));
            
            while ($row = $this->db->fetchAssoc($res)) {
                $this->setHistoryId($row['history_id']);
                $this->deleteMobs();
            }
            
            $this->db->manipulate('DELETE FROM frm_drafts_history WHERE '
                    . $this->db->in('draft_id', $draft_ids, false, 'integer'));
        }
    }
}
