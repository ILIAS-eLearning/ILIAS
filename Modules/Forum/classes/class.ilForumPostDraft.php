<?php declare(strict_types=1);

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
 * Class ilForumPostDraft
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumPostDraft
{
    public const MEDIAOBJECT_TYPE = 'frm~d:html';

    /** @var array<int, array{draft_ids: array<int, self>}|array<int, array<int, self>>> */
    private static array $instances = [];
    private static array $forum_statistics_cache = [];
    private static array $drafts_settings_cache = [];

    private ilDBInterface $db;
    private int $draft_id = 0;
    private int $post_id = 0;
    private int $forum_id = 0;
    private int $thread_id = 0;
    private string $post_subject = '';
    private string $post_message = '';
    private string $post_date = '0000-00-00 00:00:00';
    private string $post_update = '0000-00-00 00:00:00';
    private int $update_user_id = 0;
    private string $post_user_alias = '';
    private int $post_author_id = 0;
    private int $post_display_user_id = 0;
    private bool $notify = false;
    private bool $post_notify = false;

    public function __construct(int $user_id = 0, int $post_id = 0, int $draft_id = 0)
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

    protected static function populateWithDatabaseRecord(ilForumPostDraft $draft, array $row) : void
    {
        $draft->setDraftId((int) $row['draft_id']);
        $draft->setForumId((int) $row['forum_id']);
        $draft->setThreadId((int) $row['thread_id']);
        $draft->setPostId((int) $row['post_id']);
        $draft->setPostAuthorId((int) $row['post_author_id']);
        $draft->setPostDisplayUserId((int) $row['pos_display_usr_id']);
        $draft->setUpdateUserId((int) $row['update_user_id']);
        $draft->setPostSubject((string) $row['post_subject']);
        $draft->setPostMessage((string) $row['post_message']);
        $draft->setPostDate((string) $row['post_date']);
        $draft->setPostUpdate((string) $row['post_update']);
        $draft->setPostUserAlias((string) $row['post_user_alias']);
        $draft->setNotificationStatus((bool) $row['notify']);
        $draft->setPostNotificationStatus((bool) $row['post_notify']);
    }

    public function isPostNotificationEnabled() : bool
    {
        return $this->post_notify;
    }

    public function setPostNotificationStatus(bool $post_notify) : void
    {
        $this->post_notify = $post_notify;
    }

    public function isNotificationEnabled() : bool
    {
        return $this->notify;
    }

    public function setNotificationStatus(bool $notify) : void
    {
        $this->notify = $notify;
    }

    public function getDraftId() : int
    {
        return $this->draft_id;
    }

    public function setDraftId(int $draft_id) : void
    {
        $this->draft_id = $draft_id;
    }

    public function getPostId() : int
    {
        return $this->post_id;
    }

    public function setPostId(int $post_id) : void
    {
        $this->post_id = $post_id;
    }

    public function getForumId() : int
    {
        return $this->forum_id;
    }

    public function setForumId(int $forum_id) : void
    {
        $this->forum_id = $forum_id;
    }

    public function getThreadId() : int
    {
        return $this->thread_id;
    }

    public function setThreadId(int $thread_id) : void
    {
        $this->thread_id = $thread_id;
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

    public function getPostDate() : string
    {
        return $this->post_date;
    }

    public function setPostDate(string $post_date) : void
    {
        $this->post_date = $post_date;
    }

    public function getPostUpdate() : string
    {
        return $this->post_update;
    }

    public function setPostUpdate(string $post_update) : void
    {
        $this->post_update = $post_update;
    }

    public function getUpdateUserId() : int
    {
        return $this->update_user_id;
    }

    public function setUpdateUserId(int $update_user_id) : void
    {
        $this->update_user_id = $update_user_id;
    }

    public function getPostUserAlias() : string
    {
        return $this->post_user_alias;
    }

    public function setPostUserAlias(string $post_user_alias) : void
    {
        $this->post_user_alias = $post_user_alias;
    }

    public function getPostAuthorId() : int
    {
        return $this->post_author_id;
    }

    public function setPostAuthorId(int $post_author_id) : void
    {
        $this->post_author_id = $post_author_id;
    }

    public function getPostDisplayUserId() : int
    {
        return $this->post_display_user_id;
    }

    public function setPostDisplayUserId(int $post_display_user_id) : void
    {
        $this->post_display_user_id = $post_display_user_id;
    }

    protected function readDraft() : void
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_posts_drafts WHERE post_author_id = %s AND draft_id = %s',
            ['integer', 'integer'],
            [$this->getPostAuthorId(), $this->getDraftId()]
        );

        if ($row = $this->db->fetchAssoc($res)) {
            self::populateWithDatabaseRecord($this, $row);
        }
    }

    protected static function readDrafts(int $user_id) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT * FROM frm_posts_drafts WHERE post_author_id = %s',
            ['integer'],
            [$user_id]
        );

        self::$instances[$user_id] = [
            'draft_ids' => [],
        ];

        while ($row = $ilDB->fetchAssoc($res)) {
            $tmp_obj = new ilForumPostDraft();
            self::populateWithDatabaseRecord($tmp_obj, $row);
            self::$instances[$user_id][$row['thread_id']][$tmp_obj->getPostId()][] = $tmp_obj;
            self::$instances[$user_id]['draft_ids'][$tmp_obj->getDraftId()] = $tmp_obj;
        }
    }

    /**
     * @return ilForumPostDraft[]|array<int, ilForumPostDraft[]>
     */
    public static function getSortedDrafts(
        int $usrId,
        int $threadId,
        int $sorting = ilForumProperties::VIEW_DATE_ASC
    ) : array {
        global $DIC;
        $ilDB = $DIC->database();

        $drafts = [];

        $orderColumn = ' ';
        $orderDirection = ' ';

        if ($sorting !== ilForumProperties::VIEW_TREE) {
            $orderColumn = ' ORDER BY post_date ';
            $orderDirection = 'ASC';
            if ($sorting === ilForumProperties::VIEW_DATE_DESC) {
                $orderDirection = 'DESC';
            }
        }

        $res = $ilDB->queryF(
            'SELECT * FROM frm_posts_drafts WHERE post_author_id = %s AND thread_id = %s' .
            $orderColumn . $orderDirection,
            ['integer', 'integer'],
            [$usrId, $threadId]
        );

        while ($row = $ilDB->fetchAssoc($res)) {
            $draft = new ilForumPostDraft();
            self::populateWithDatabaseRecord($draft, $row);
            $drafts[] = $draft;
            self::$instances[$usrId][$threadId][$draft->getPostId()][] = $draft;
        }

        if (ilForumProperties::VIEW_TREE === $sorting) {
            return self::$instances[$usrId][$threadId] ?? [];
        }

        return $drafts;
    }

    /**
     * @return ilForumPostDraft[]
     */
    public static function getDraftInstancesByUserId(int $user_id) : array
    {
        if (!isset(self::$instances[$user_id])) {
            self::readDrafts($user_id);
        }

        return self::$instances[$user_id]['draft_ids'];
    }

    public static function newInstanceByDraftId(int $draft_id) : ilForumPostDraft
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT * FROM frm_posts_drafts WHERE draft_id = %s',
            ['integer'],
            [$draft_id]
        );

        $tmp_obj = new ilForumPostDraft();
        while ($row = $ilDB->fetchAssoc($res)) {
            self::populateWithDatabaseRecord($tmp_obj, $row);
        }
        return $tmp_obj;
    }

    public function saveDraft() : int
    {
        $draft_id = $this->db->nextId('frm_posts_drafts');
        $post_date = date("Y-m-d H:i:s");

        $this->db->insert('frm_posts_drafts', [
            'draft_id' => ['integer', $draft_id],
            'post_id' => ['integer', $this->getPostId()],
            'thread_id' => ['integer', $this->getThreadId()],
            'forum_id' => ['integer', $this->getForumId()],
            'post_author_id' => ['integer', $this->getPostAuthorId()],
            'post_subject' => ['text', $this->getPostSubject()],
            'post_message' => ['clob', $this->getPostMessage()],
            'notify' => ['integer', (int) $this->isNotificationEnabled()],
            'post_notify' => ['integer', (int) $this->isPostNotificationEnabled()],
            'post_date' => ['timestamp', $post_date],
            'post_update' => ['timestamp', $post_date],
            'post_user_alias' => ['text', $this->getPostUserAlias()],
            'pos_display_usr_id' => ['integer', $this->getPostDisplayUserId()]
        ]);
        $this->setDraftId($draft_id);
        return $draft_id;
    }

    public function updateDraft() : void
    {
        $this->db->update(
            'frm_posts_drafts',
            [
                'post_subject' => ['text', $this->getPostSubject()],
                'post_message' => ['clob', $this->getPostMessage()],
                'post_user_alias' => ['text', $this->getPostUserAlias()],
                'post_update' => ['timestamp', date("Y-m-d H:i:s")],
                'update_user_id' => ['integer', $this->getUpdateUserId()],
            ],
            ['draft_id' => ['integer', $this->getDraftId()]]
        );
    }

    public function deleteDraft() : void
    {
        $this->db->manipulateF(
            'DELETE FROM frm_posts_drafts WHERE draft_id = %s',
            ['integer'],
            [$this->getDraftId()]
        );
    }

    public static function deleteMobsOfDraft(int $draft_id) : void
    {
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
     * @param int[] $post_ids
     */
    public function deleteDraftsByPostIds(array $post_ids = []) : void
    {
        $draft_ids = [];
        $res = $this->db->query('SELECT draft_id FROM frm_posts_drafts WHERE ' . $this->db->in(
            'post_id',
            $post_ids,
            false,
            'integer'
        ));
        while ($row = $this->db->fetchAssoc($res)) {
            $draft_ids[] = (int) $row['draft_id'];
        }

        foreach ($draft_ids as $draft_id) {
            self::deleteMobsOfDraft($draft_id);

            $objFileDataForumDrafts = new ilFileDataForumDrafts(0, $draft_id);
            $objFileDataForumDrafts->delete();
        }
        $this->db->manipulate('DELETE FROM frm_drafts_history WHERE ' . $this->db->in(
            'draft_id',
            $draft_ids,
            false,
            'integer'
        ));
        $this->db->manipulate('DELETE FROM frm_posts_drafts WHERE ' . $this->db->in(
            'draft_id',
            $draft_ids,
            false,
            'integer'
        ));
    }

    /**
     * @param int[] $draft_ids
     */
    public function deleteDraftsByDraftIds(array $draft_ids = []) : void
    {
        foreach ($draft_ids as $draft_id) {
            self::deleteMobsOfDraft($draft_id);

            $objFileDataForumDrafts = new ilFileDataForumDrafts(0, $draft_id);
            $objFileDataForumDrafts->delete();
        }
        $this->db->manipulate('DELETE FROM frm_drafts_history WHERE ' . $this->db->in(
            'draft_id',
            $draft_ids,
            false,
            'integer'
        ));
        $this->db->manipulate('DELETE FROM frm_posts_drafts WHERE ' . $this->db->in(
            'draft_id',
            $draft_ids,
            false,
            'integer'
        ));
    }

    public static function deleteDraftsByUserId(int $user_id) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT draft_id FROM frm_posts_drafts WHERE post_author_id = %s',
            ['integer'],
            [$user_id]
        );

        $draft_ids = [];
        while ($row = $ilDB->fetchAssoc($res)) {
            $draft_ids[] = (int) $row['draft_id'];
        }

        foreach ($draft_ids as $draft_id) {
            self::deleteMobsOfDraft($draft_id);

            $objFileDataForumDrafts = new ilFileDataForumDrafts(0, $draft_id);
            $objFileDataForumDrafts->delete();
        }

        $ilDB->manipulate('DELETE FROM frm_drafts_history WHERE ' . $ilDB->in(
            'draft_id',
            $draft_ids,
            false,
            'integer'
        ));
        $ilDB->manipulateF(
            'DELETE FROM frm_posts_drafts WHERE post_author_id = %s',
            ['integer'],
            [$user_id]
        );
    }

    public static function isSavePostDraftAllowed() : bool
    {
        if (!isset(self::$drafts_settings_cache['save_post_drafts'])) {
            global $DIC;
            self::$drafts_settings_cache['save_post_drafts'] = (bool) $DIC->settings()->get('save_post_drafts', '0');
        }

        return self::$drafts_settings_cache['save_post_drafts'];
    }

    public static function isAutoSavePostDraftAllowed() : bool
    {
        if (!self::isSavePostDraftAllowed()) {
            return false;
        }

        if (!isset(self::$drafts_settings_cache['autosave_drafts'])) {
            global $DIC;

            self::$drafts_settings_cache['autosave_drafts'] = (bool) $DIC->settings()->get('autosave_drafts', '0');
            self::$drafts_settings_cache['autosave_drafts_ival'] = (int) $DIC->settings()->get(
                'autosave_drafts_ival',
                '30'
            );
        }

        return self::$drafts_settings_cache['autosave_drafts'];
    }

    public static function lookupAutosaveInterval() : int
    {
        if (self::isAutoSavePostDraftAllowed()) {
            return (int) self::$drafts_settings_cache['autosave_drafts_ival'];
        }
        return 0;
    }

    public static function getDraftsStatisticsByRefId(int $ref_id) : array
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
                ['integer', 'integer'],
                [$forumId, $ilUser->getId()]
            );

            $num_drafts_total = 0;

            while ($row = $ilDB->fetchAssoc($res)) {
                $num_drafts_total += $row['num_drafts'];
                self::$forum_statistics_cache[$ref_id][$ilUser->getId()][(int) $row['thread_id']] = (int) $row['num_drafts'];
            }

            self::$forum_statistics_cache[$ref_id][$ilUser->getId()]['total'] = $num_drafts_total;
        }
        return self::$forum_statistics_cache[$ref_id][$ilUser->getId()];
    }

    public static function getThreadDraftData(int $post_author_id, int $forum_id) : array
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
            ['integer', 'integer', 'integer', 'integer'],
            [$post_author_id, $forum_id, 0, 0]
        );
        $draft_data = [];
        while ($row = $ilDB->fetchAssoc($res)) {
            $tmp_obj = new self();
            self::populateWithDatabaseRecord($tmp_obj, $row);
            $draft_data[] = ['subject' => $tmp_obj->getPostSubject(),
                             'post_update' => $tmp_obj->getPostUpdate(),
                             'draft_id' => $tmp_obj->getDraftId()
            ];
        }
        return $draft_data;
    }

    public static function createDraftBackup(int $draft_id) : void
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT * FROM frm_posts_drafts WHERE draft_id = %s',
            ['integer'],
            [$draft_id]
        );

        $tmp_obj = new self();
        while ($row = $ilDB->fetchAssoc($res)) {
            self::populateWithDatabaseRecord($tmp_obj, $row);
        }

        $history_obj = new ilForumDraftsHistory();
        $history_obj->deleteHistoryByDraftIds([$draft_id]);

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
