<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class ilForumDraftHistory
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumDraftsHistory
{
    public const MEDIAOBJECT_TYPE = 'frm~h:html';

    private ilDBInterface $db;
    private int $history_id = 0;
    private int $draft_id = 0;
    private string $post_subject = '';
    private string $post_message = '';
    protected string $draft_date = '0000-00-00 00:00:00';

    public function getHistoryId(): int
    {
        return $this->history_id;
    }

    public function setHistoryId(int $history_id): void
    {
        $this->history_id = $history_id;
    }

    public function getDraftId(): int
    {
        return $this->draft_id;
    }

    public function setDraftId(int $draft_id): void
    {
        $this->draft_id = $draft_id;
    }

    public function getPostSubject(): string
    {
        return $this->post_subject;
    }

    public function setPostSubject(string $post_subject): void
    {
        $this->post_subject = $post_subject;
    }

    public function getPostMessage(): string
    {
        return $this->post_message;
    }

    public function setPostMessage(string $post_message): void
    {
        $this->post_message = $post_message;
    }

    public function getDraftDate(): string
    {
        return $this->draft_date;
    }

    public function setDraftDate(string $draft_date): void
    {
        $this->draft_date = $draft_date;
    }

    public function __construct(int $history_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();

        if ($history_id > 0) {
            $this->readByHistoryId($history_id);
        }
    }

    private function readByHistoryId(int $history_id): void
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_drafts_history WHERE history_id = %s',
            ['integer'],
            [$history_id]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $this->setHistoryId((int) $row['history_id']);
            $this->setDraftId((int) $row['draft_id']);
            $this->setPostMessage($row['post_message']);
            $this->setPostSubject($row['post_subject']);
            $this->setDraftDate($row['draft_date']);
        }
    }

    /**
     * @return ilForumDraftsHistory[]
     */
    public static function getInstancesByDraftId(int $draft_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT * FROM frm_drafts_history WHERE draft_id = %s ORDER BY draft_date DESC',
            ['integer'],
            [$draft_id]
        );
        $instances = [];
        while ($row = $ilDB->fetchAssoc($res)) {
            $draftHistory = new self();
            $draftHistory = self::populateWithDatabaseRecord($draftHistory, $row);

            $instances[] = $draftHistory;
        }

        return $instances;
    }

    protected static function populateWithDatabaseRecord(
        ilForumDraftsHistory $history_draft,
        array $row
    ): ilForumDraftsHistory {
        $history_draft->setHistoryId((int) $row['history_id']);
        $history_draft->setDraftId((int) $row['draft_id']);
        $history_draft->setPostMessage($row['post_message']);
        $history_draft->setPostSubject($row['post_subject']);
        $history_draft->setDraftDate($row['draft_date']);

        return $history_draft;
    }

    public function delete(): void
    {
        $this->db->manipulateF(
            'DELETE FROM frm_drafts_history WHERE history_id = %s',
            ['integer'],
            [$this->getHistoryId()]
        );
    }

    public function getFirstAutosaveByDraftId(int $draft_id): void
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_drafts_history WHERE draft_id = %s ORDER BY history_id ASC',
            ['integer'],
            [$draft_id]
        );

        if ($row = $this->db->fetchAssoc($res)) {
            $this->setHistoryId((int) $row['history_id']);
            $this->setDraftId((int) $row['draft_id']);
            $this->setPostSubject($row['post_subject']);
            $this->setPostMessage($row['post_message']);
        }
    }

    public function getLastAutosaveByDraftId(int $draft_id): void
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_drafts_history WHERE draft_id = %s ORDER BY history_id DESC',
            ['integer'],
            [$draft_id]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $this->setHistoryId((int) $row['history_id']);
            $this->setDraftId((int) $row['draft_id']);
            $this->setPostSubject($row['post_subject']);
            $this->setPostMessage($row['post_message']);
        }
    }

    public function addDraftToHistory(): void
    {
        $next_id = $this->db->nextId('frm_drafts_history');
        $this->db->insert(
            'frm_drafts_history',
            [
                'history_id' => ['integer', $next_id],
                'draft_id' => ['integer', $this->getDraftId()],
                'post_subject' => ['text', $this->getPostSubject()],
                'post_message' => ['text', $this->getPostMessage()],
                'draft_date' => ['timestamp', date("Y-m-d H:i:s")]
            ]
        );
        $this->setHistoryId($next_id);
    }

    public function deleteMobs(): void
    {
        $oldMediaObjects = ilObjMediaObject::_getMobsOfObject('frm~h:html', $this->getHistoryId());
        foreach ($oldMediaObjects as $oldMob) {
            if (ilObjMediaObject::_exists($oldMob)) {
                ilObjMediaObject::_removeUsage($oldMob, 'frm~h:html', $this->getHistoryId());
                $mob_obj = new ilObjMediaObject($oldMob);
                $mob_obj->delete();
            }
        }
    }

    public function rollbackAutosave(): ilForumPostDraft
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
        $this->deleteHistoryByDraftIds([$draft->getDraftId()]);

        return $draft;
    }

    /**
     * @param int[] $post_ids
     * @return int[] A list of deleted draft ids
     */
    public function deleteHistoryByPostIds(array $post_ids = []): array
    {
        $draft_ids = [];
        if ($post_ids !== []) {
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

    /**
     * @param int[] $draft_ids
     */
    public function deleteHistoryByDraftIds(array $draft_ids = []): void
    {
        if ($draft_ids !== []) {
            $res = $this->db->query(
                'SELECT history_id FROM frm_drafts_history  WHERE ' . $this->db->in('draft_id', $draft_ids, false, 'integer')
            );

            while ($row = $this->db->fetchAssoc($res)) {
                $this->setHistoryId((int) $row['history_id']);
                $this->deleteMobs();
            }

            $this->db->manipulate(
                'DELETE FROM frm_drafts_history WHERE ' . $this->db->in('draft_id', $draft_ids, false, 'integer')
            );
        }
    }
}
