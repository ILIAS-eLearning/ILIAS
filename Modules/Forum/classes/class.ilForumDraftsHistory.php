<?php declare(strict_types=1);
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumDraftHistory
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumDraftsHistory
{
    const MEDIAOBJECT_TYPE = 'frm~h:html';

    protected int $history_id = 0;
    protected int $draft_id = 0;
    protected string $post_subject = '';
    protected string $post_message = '';
    protected string $draft_date = '0000-00-00 00:00:00';

    public $db;

    public function getHistoryId() : int
    {
        return $this->history_id;
    }

    public function setHistoryId(int $history_id) : void
    {
        $this->history_id = $history_id;
    }

    public function getDraftId() : int
    {
        return $this->draft_id;
    }

    public function setDraftId(int $draft_id) : void
    {
        $this->draft_id = $draft_id;
    }

    public function getPostSubject() : string
    {
        return $this->post_subject;
    }

    public function setPostSubject(string $post_subject) : void
    {
        $this->post_subject = $post_subject;
    }

    public function getPostMessage() : string
    {
        return $this->post_message;
    }

    public function setPostMessage(string $post_message) : void
    {
        $this->post_message = $post_message;
    }

    public function getDraftDate() : string
    {
        return $this->draft_date;
    }

    public function setDraftDate(string $draft_date) : void
    {
        $this->draft_date = $draft_date;
    }

    public function __construct(int $history_id = 0)
    {
        global $DIC;
        $this->db = $DIC->database();

        if (isset($history_id) && $history_id > 0) {
            $this->readByHistoryId($history_id);
        }
    }

    private function readByHistoryId($history_id) : void
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_drafts_history WHERE history_id = %s',
            array('integer'),
            array((int) $history_id)
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $this->setHistoryId((int) $row['history_id']);
            $this->setDraftId((int) $row['draft_id']);
            $this->setPostMessage($row['post_message']);
            $this->setPostSubject($row['post_subject']);
            $this->setDraftDate($row['draft_date']);
        }
    }

    public static function getInstancesByDraftId($draft_id) : array
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

    protected static function populateWithDatabaseRecord(
        ilForumDraftsHistory $history_draft,
        array $row
    ) : ilForumDraftsHistory {
        $history_draft->setHistoryId((int) $row['history_id']);
        $history_draft->setDraftId((int) $row['draft_id']);
        $history_draft->setPostMessage($row['post_message']);
        $history_draft->setPostSubject($row['post_subject']);
        $history_draft->setDraftDate($row['draft_date']);

        return $history_draft;
    }

    public function delete() : void
    {
        $this->db->manipulatef(
            'DELETE FROM frm_drafts_history WHERE history_id = %s',
            array('integer'),
            array($this->getHistoryId())
        );
    }

    public function getFirstAutosaveByDraftId($draft_id) : void
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_drafts_history WHERE draft_id = %s 
			ORDER BY history_id ASC',
            array('integer'),
            array((int) $draft_id)
        );

        if ($row = $this->db->fetchAssoc($res)) {
            $this->setHistoryId((int) $row['history_id']);
            $this->setDraftId((int) $row['draft_id']);
            $this->setPostSubject($row['post_subject']);
            $this->setPostMessage($row['post_message']);
        }
    }

    public function getLastAutosaveByDraftId($draft_id) : void
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_drafts_history WHERE draft_id = %s 
			ORDER BY history_id DESC',
            array('integer'),
            array((int) $draft_id)
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $this->setHistoryId((int) $row['history_id']);
            $this->setDraftId((int) $row['draft_id']);
            $this->setPostSubject($row['post_subject']);
            $this->setPostMessage($row['post_message']);
        }
    }

    public function addDraftToHistory() : void
    {
        $next_id = $this->db->nextId('frm_drafts_history');
        $this->db->insert(
            'frm_drafts_history',
            array('history_id' => array('integer', $next_id),
                  'draft_id' => array('integer', $this->getDraftId()),
                  'post_subject' => array('text', $this->getPostSubject()),
                  'post_message' => array('text', $this->getPostMessage()),
                  'draft_date' => array('timestamp', date("Y-m-d H:i:s"))
            )
        );
        $this->setHistoryId($next_id);
    }

    public function addMobsToDraftsHistory($message) : void
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

    public function deleteMobs() : void
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

    public function rollbackAutosave() : ilForumPostDraft
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

    public function deleteHistoryByPostIds(array $post_ids = array()) : array
    {
        $draft_ids = array();
        if (count($post_ids) > 0) {
            $res = $this->db->query('
			SELECT frm_drafts_history.history_id, frm_drafts_history.draft_id 
			FROM frm_posts_drafts  
 			INNER JOIN frm_drafts_history ON frm_posts_drafts.draft_id
 			WHERE ' . $this->db->in('post_id', $post_ids, false, 'integer'));

            while ($row = $this->db->fetchAssoc($res)) {
                $draft_ids[] = (int) $row['draft_id'];
            }

            $this->deleteHistoryByDraftIds($draft_ids);
        }
        return $draft_ids;
    }

    public function deleteHistoryByDraftIds($draft_ids = array()) : void
    {
        if (count($draft_ids) > 0) {
            $res = $this->db->query('SELECT history_id FROM frm_drafts_history 
 					WHERE ' . $this->db->in('draft_id', $draft_ids, false, 'integer'));

            while ($row = $this->db->fetchAssoc($res)) {
                $this->setHistoryId((int) $row['history_id']);
                $this->deleteMobs();
            }

            $this->db->manipulate('DELETE FROM frm_drafts_history WHERE '
                . $this->db->in('draft_id', $draft_ids, false, 'integer'));
        }
    }
}
