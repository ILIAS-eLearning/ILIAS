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
 * Class ilObjForum
 * @author  Wolfgang Merkens <wmerkens@databay.de>
 * @ingroup ModulesForum
 */
class ilObjForum extends ilObject
{
    public const NEWS_NEW_CONSIDERATION_WEEKS = 8;

    public ilForum $Forum;
    /** @var array<int, int>  */
    private static array $obj_id_to_forum_id_cache = [];
    /** @var array<int, int>  */
    private static array $ref_id_to_forum_id_cache = [];
    /** @var array<int, array{num_posts: int, num_unread_posts: int, num_new_posts: int}>  */
    private static array $forum_statistics_cache = [];
    /** @var array<int, array|null>  */
    private static array $forum_last_post_cache = [];
    private \ILIAS\DI\RBACServices $rbac;
    private ilLogger $logger;

    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        global $DIC;

        $this->type = 'frm';
        parent::__construct($a_id, $a_call_by_reference);

        $this->rbac = $DIC->rbac();
        $this->logger = $DIC->logger()->root();

        $settings = $DIC->settings();
        $weeks = self::NEWS_NEW_CONSIDERATION_WEEKS;
        if ($settings->get('frm_store_new')) {
            $weeks = (int) $settings->get('frm_store_new');
        }
        $new_deadline = time() - 60 * 60 * 24 * 7 * $weeks;
        $settings->set('frm_new_deadline', (string) $new_deadline);

        $this->Forum = new ilForum();
    }

    public function create(): int
    {
        $id = parent::create();

        $properties = ilForumProperties::getInstance($this->getId());
        $properties->setDefaultView(ilForumProperties::VIEW_DATE_ASC);
        $properties->setAnonymisation(false);
        $properties->setStatisticsStatus(false);
        $properties->setPostActivation(false);
        $properties->setThreadSorting(0);
        $properties->insert();

        $this->createSettings();

        $this->setOfflineStatus(true);
        $this->update();
        $this->saveData();

        return $id;
    }

    public function setPermissions(int $parent_ref_id): void
    {
        parent::setPermissions($parent_ref_id);

        $roles = [self::_lookupModeratorRole($this->getRefId())];
        $this->rbac->admin()->assignUser($roles[0], $this->getOwner());
        $this->updateModeratorRole($roles[0]);
    }

    public function updateModeratorRole(int $role_id): void
    {
        $this->db->manipulate('UPDATE frm_data SET top_mods = ' . $this->db->quote(
            $role_id,
            'integer'
        ) . ' WHERE top_frm_fk = ' . $this->db->quote($this->getId(), 'integer'));
    }

    public static function _lookupThreadSubject(int $a_thread_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = $ilDB->queryF('SELECT thr_subject FROM frm_threads WHERE thr_pk = %s', ['integer'], [$a_thread_id]);
        while ($row = $ilDB->fetchObject($res)) {
            return $row->thr_subject ?? '';
        }

        return '';
    }

    public function getCountUnread(int $a_usr_id, int $a_thread_id = 0, bool $ignoreRoot = false): int
    {
        $a_frm_id = $this->getId();
        $topic_id = 0;
        $num_posts = 0;
        $count_read = 0;

        if ($a_thread_id === 0) {
            $res = $this->db->queryF('SELECT top_pk FROM frm_data WHERE top_frm_fk = %s', ['integer'], [$a_frm_id]);
            while ($row = $this->db->fetchObject($res)) {
                $topic_id = (int) $row->top_pk;
            }

            $res = $this->db->queryF(
                '
				SELECT COUNT(pos_pk) num_posts
				FROM frm_posts 
				LEFT JOIN frm_posts_tree ON frm_posts_tree.pos_fk = pos_pk
				WHERE pos_top_fk = %s' . ($ignoreRoot ? ' AND parent_pos != 0 ' : ''),
                ['integer'],
                [$topic_id]
            );

            while ($row = $this->db->fetchObject($res)) {
                $num_posts = (int) $row->num_posts;
            }

            $res = $this->db->queryF(
                'SELECT COUNT(post_id) count_read FROM frm_user_read WHERE obj_id = %s AND usr_id = %s',
                ['integer', 'integer'],
                [$a_frm_id, $a_usr_id]
            );

            while ($row = $this->db->fetchObject($res)) {
                $count_read = (int) $row->count_read;
            }
        } else {
            $res = $this->db->queryF(
                '
				SELECT COUNT(pos_pk) num_posts FROM frm_posts
				LEFT JOIN frm_posts_tree ON frm_posts_tree.pos_fk = pos_pk
				WHERE pos_thr_fk = %s' . ($ignoreRoot ? ' AND parent_pos != 0 ' : ''),
                ['integer'],
                [$a_thread_id]
            );

            $row = $this->db->fetchObject($res);
            $num_posts = (int) $row->num_posts;

            $res = $this->db->queryF(
                '
				SELECT COUNT(post_id) count_read FROM frm_user_read 
				WHERE obj_id = %s
				AND usr_id = %s
				AND thread_id = %s',
                ['integer', 'integer', 'integer'],
                [$a_frm_id, $a_frm_id, $a_thread_id]
            );

            $row = $this->db->fetchObject($res);
            $count_read = (int) $row->count_read;
        }
        $unread = $num_posts - $count_read;

        return max($unread, 0);
    }

    public function markThreadRead(int $a_usr_id, int $a_thread_id): bool
    {
        $res = $this->db->queryF('SELECT pos_pk FROM frm_posts WHERE pos_thr_fk = %s', ['integer'], [$a_thread_id]);
        while ($row = $this->db->fetchObject($res)) {
            $this->markPostRead($a_usr_id, $a_thread_id, (int) $row->pos_pk);
        }

        return true;
    }

    public function markAllThreadsRead(int $a_usr_id): void
    {
        $res = $this->db->queryF(
            'SELECT thr_pk FROM frm_data, frm_threads WHERE top_frm_fk = %s AND top_pk = thr_top_fk',
            ['integer'],
            [$this->getId()]
        );

        while ($row = $this->db->fetchObject($res)) {
            $this->markThreadRead($a_usr_id, (int) $row->thr_pk);
        }
    }

    public function markPostRead(int $a_usr_id, int $a_thread_id, int $a_post_id): void
    {
        $res = $this->db->queryF(
            '
			SELECT thread_id FROM frm_user_read 
			WHERE usr_id = %s
			AND obj_id = %s
			AND thread_id = %s
			AND post_id = %s',
            ['integer', 'integer', 'integer', 'integer'],
            [$a_usr_id, $this->getId(), $a_thread_id, $a_post_id]
        );

        if ($this->db->numRows($res) === 0) {
            $this->db->manipulateF(
                '
                INSERT INTO frm_user_read
                (	usr_id,
                    obj_id,
                    thread_id,
                    post_id
                )
                VALUES (%s,%s,%s,%s)',
                ['integer', 'integer', 'integer', 'integer'],
                [$a_usr_id, $this->getId(), $a_thread_id, $a_post_id]
            );
        }
    }

    public function markPostUnread(int $a_user_id, int $a_post_id): void
    {
        $this->db->manipulateF(
            'DELETE FROM frm_user_read WHERE usr_id = %s AND post_id = %s',
            ['integer', 'integer'],
            [$a_user_id, $a_post_id]
        );
    }

    public function isRead($a_usr_id, $a_post_id): bool
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_user_read WHERE usr_id = %s AND post_id = %s',
            ['integer', 'integer'],
            [$a_usr_id, $a_post_id]
        );

        return (bool) $this->db->numRows($res);
    }

    public function updateLastAccess(int $a_usr_id, int $a_thread_id): void
    {
        $res = $this->db->queryF(
            'SELECT * FROM frm_thread_access WHERE usr_id = %s AND obj_id = %s AND thread_id = %s',
            ['integer', 'integer', 'integer'],
            [$a_usr_id, $this->getId(), $a_thread_id]
        );
        $data = $this->db->fetchAssoc($res);

        if (is_array($data)) {
            $this->db->replace(
                'frm_thread_access',
                [
                    'usr_id' => ['integer', $a_usr_id],
                    'obj_id' => ['integer', $this->getId()],
                    'thread_id' => ['integer', $a_thread_id]
                ],
                [
                    'access_last' => ['integer', time()],
                    'access_old' => ['integer', (int) ($data['access_old'] ?? 0)],
                    'access_old_ts' => ['timestamp', $data['access_old_ts']]
                ]
            );
        }
    }

    public static function _updateOldAccess(int $a_usr_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $ilDB->manipulateF(
            'UPDATE frm_thread_access SET access_old = access_last WHERE usr_id = %s',
            ['integer'],
            [$a_usr_id]
        );

        $res = $ilDB->query(
            'SELECT * FROM frm_thread_access WHERE usr_id = ' . $ilDB->quote($a_usr_id, 'integer')
        );
        while ($row = $ilDB->fetchAssoc($res)) {
            $ilDB->manipulate(
                "UPDATE frm_thread_access SET " .
                " access_old_ts = " . $ilDB->quote(date('Y-m-d H:i:s', (int) $row["access_old"]), "timestamp") .
                " WHERE usr_id = " . $ilDB->quote((int) $row["usr_id"], "integer") .
                " AND obj_id = " . $ilDB->quote((int) $row["obj_id"], "integer") .
                " AND thread_id = " . $ilDB->quote((int) $row["thread_id"], "integer")
            );
        }

        $weeks = self::NEWS_NEW_CONSIDERATION_WEEKS;
        if ($DIC->settings()->get('frm_store_new')) {
            $weeks = (int) $DIC->settings()->get('frm_store_new');
        }
        $new_deadline = time() - 60 * 60 * 24 * 7 * $weeks;

        $ilDB->manipulateF('DELETE FROM frm_thread_access WHERE access_last < %s', ['integer'], [$new_deadline]);
    }

    public static function _deleteUser(int $a_usr_id): void
    {
        global $DIC;

        $data = [$a_usr_id];

        $DIC->database()->manipulateF('DELETE FROM frm_user_read WHERE usr_id = %s', ['integer'], $data);
        $DIC->database()->manipulateF('DELETE FROM frm_thread_access WHERE usr_id = %s', ['integer'], $data);
        $DIC->database()->manipulateF('DELETE FROM frm_notification WHERE user_id = %s', ['integer'], $data);
    }

    public static function _deleteReadEntries(int $a_post_id): void
    {
        global $DIC;

        $DIC->database()->manipulateF('DELETE FROM frm_user_read WHERE post_id = %s', ['integer'], [$a_post_id]);
    }

    public static function _deleteAccessEntries(int $a_thread_id): void
    {
        global $DIC;

        $DIC->database()->manipulateF('DELETE FROM frm_thread_access WHERE thread_id = %s', ['integer'], [$a_thread_id]);
    }

    public function updateMoficationUserId(int $usr_id): void
    {
        $this->db->manipulateF(
            'UPDATE frm_data SET update_user = %s WHERE top_frm_fk = %s',
            ['integer', 'integer'],
            [$usr_id, $this->getId()],
        );
    }

    public function update(): bool
    {
        if (parent::update()) {
            $this->db->manipulateF(
                'UPDATE frm_data SET top_name = %s, top_description = %s, top_update = %s, update_user = %s WHERE top_frm_fk = %s',
                ['text', 'text', 'timestamp', 'integer', 'integer'],
                [
                    $this->getTitle(),
                    $this->getDescription(),
                    date("Y-m-d H:i:s"),
                    $this->user->getId(),
                    $this->getId()
                ]
            );

            return true;
        }

        return false;
    }

    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false): ?ilObject
    {
        /** @var ilObjForum $new_obj */
        $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);
        $this->cloneAutoGeneratedRoles($new_obj);

        ilForumProperties::getInstance($this->getId())->copy($new_obj->getId());
        $this->Forum->setMDB2WhereCondition('top_frm_fk = %s ', ['integer'], [$this->getId()]);
        $topData = $this->Forum->getOneTopic();

        $this->db->update('frm_data', [
            'top_name' => ['text', $topData->getTopName()],
            'top_description' => ['text', $topData->getTopDescription()],
            'top_num_posts' => ['integer', $topData->getTopNumPosts()],
            'top_num_threads' => ['integer', $topData->getTopNumThreads()],
            'top_last_post' => ['text', $topData->getTopLastPost()],
            'top_date' => ['timestamp', $topData->getTopDate()],
            'visits' => ['integer', $topData->getVisits()],
            'top_update' => ['timestamp', $topData->getTopUpdate()],
            'update_user' => ['integer', $topData->getUpdateUser()],
            'top_usr_id' => ['integer', $topData->getTopUsrId()]
        ], [
            'top_frm_fk' => ['integer', $new_obj->getId()]
        ]);

        $cwo = ilCopyWizardOptions::_getInstance($copy_id);
        $options = $cwo->getOptions($this->getRefId());

        $options['threads'] = $this->Forum::getSortedThreadSubjects($this->getId());

        $new_frm = $new_obj->Forum;
        $new_frm->setMDB2WhereCondition('top_frm_fk = %s ', ['integer'], [$new_obj->getId()]);

        $new_frm->setForumId($new_obj->getId());
        $new_frm->setForumRefId($new_obj->getRefId());

        $new_topic = $new_frm->getOneTopic();
        foreach (array_keys($options['threads']) as $thread_id) {
            $this->Forum->setMDB2WhereCondition('thr_pk = %s ', ['integer'], [$thread_id]);

            $old_thread = $this->Forum->getOneThread();

            $old_post_id = $this->Forum->getRootPostIdByThread($old_thread->getId());

            $newThread = new ilForumTopic(0, true, true);
            $newThread->setSticky($old_thread->isSticky());
            $newThread->setForumId($new_topic->getTopPk());
            $newThread->setThrAuthorId($old_thread->getThrAuthorId());
            $newThread->setDisplayUserId($old_thread->getDisplayUserId());
            $newThread->setSubject($old_thread->getSubject());
            $newThread->setUserAlias($old_thread->getUserAlias());
            $newThread->setCreateDate($old_thread->getCreateDate());

            try {
                $top_pos = $old_thread->getFirstVisiblePostNode();
            } catch (OutOfBoundsException) {
                $top_pos = new ilForumPost($old_post_id);
            }

            $newPostId = $new_frm->generateThread(
                $newThread,
                $top_pos->getMessage(),
                $top_pos->isNotificationEnabled(),
                false,
                true,
                (bool) ($old_thread->getNumPosts() - 1)
            );

            $old_forum_files = new ilFileDataForum($this->getId(), $old_post_id);
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
                $notifications = new ilForumNotification($targetRefId);
                $notifications->cloneFromSource($sourceRefId);
            }
        }

        if (ilForumPage::_exists($this->getType(), $this->getId())) {
            $translations = ilContentPagePage::lookupTranslations($this->getType(), $this->getId());
            foreach ($translations as $language) {
                $originalPageObject = new ilForumPage($this->getId(), 0, $language);
                $copiedXML = $originalPageObject->copyXmlContent();

                $duplicatePageObject = new ilForumPage();
                $duplicatePageObject->setId($new_obj->getId());
                $duplicatePageObject->setParentId($new_obj->getId());
                $duplicatePageObject->setLanguage($language);
                $duplicatePageObject->setXMLContent($copiedXML);
                $duplicatePageObject->createFromXML();
            }
        }

        $cwo = ilCopyWizardOptions::_getInstance($copy_id);
        //copy online status if object is not the root copy object
        if (!$cwo->isRootNode($this->getRefId())) {
            $new_obj->setOfflineStatus($this->getOfflineStatus());
        } else {
            $new_obj->setOfflineStatus(true);
        }
        $new_obj->update();

        return $new_obj;
    }

    public function cloneAutoGeneratedRoles(self $new_obj): void
    {
        $src_moderator_role_id = self::_lookupModeratorRole($this->getRefId());
        $new_moderator_role_id = self::_lookupModeratorRole($new_obj->getRefId());

        if (
            0 === $src_moderator_role_id ||
            0 === $new_moderator_role_id ||
            0 === $this->getRefId() ||
            0 === $new_obj->getRefId()
        ) {
            $this->logger->write(__METHOD__ . ' : Error cloning auto generated role: il_frm_moderator');
        }

        $this->rbac->admin()->copyRolePermissions(
            $src_moderator_role_id,
            $this->getRefId(),
            $new_obj->getRefId(),
            $new_moderator_role_id,
            true
        );

        $this->logger->write(__METHOD__ . ' : Finished copying of role il_frm_moderator.');

        $moderators = new ilForumModerators($this->getRefId());
        $src_moderator_usr_ids = $moderators->getCurrentModerators();
        foreach ($src_moderator_usr_ids as $usr_id) {
            // The object owner is already member of the moderator role when this method is called
            // Since the new static caches are introduced with ILIAS 5.0, a database error occurs if we try to assign the user here.
            if ($this->getOwner() !== $usr_id) {
                $this->rbac->admin()->assignUser($new_moderator_role_id, $usr_id);
            }
        }
    }

    public function delete(): bool
    {
        if (!parent::delete()) {
            return false;
        }

        if (ilForumPage::_exists($this->getType(), $this->getId())) {
            $originalPageObject = new ilForumPage($this->getId());
            $originalPageObject->delete();
        }

        $tmp_file_obj = new ilFileDataForum($this->getId());
        $tmp_file_obj->delete();

        $this->Forum->setMDB2WhereCondition('top_frm_fk = %s ', ['integer'], [$this->getId()]);

        $topData = $this->Forum->getOneTopic();

        $threads = $this->Forum->getAllThreads($topData->getTopPk());
        $thread_ids_to_delete = [];
        foreach ($threads['items'] as $thread) {
            $thread_ids_to_delete[$thread->getId()] = $thread->getId();
        }

        $this->db->manipulate('DELETE FROM frm_posts_tree WHERE ' . $this->db->in(
            'thr_fk',
            $thread_ids_to_delete,
            false,
            'integer'
        ));
        $this->db->manipulate('DELETE FROM frm_posts WHERE ' . $this->db->in(
            'pos_thr_fk',
            $thread_ids_to_delete,
            false,
            'integer'
        ));
        $this->db->manipulate('DELETE FROM frm_threads WHERE ' . $this->db->in(
            'thr_pk',
            $thread_ids_to_delete,
            false,
            'integer'
        ));

        $obj_id = [$this->getId()];

        $this->db->manipulateF('DELETE FROM frm_data WHERE top_frm_fk = %s', ['integer'], $obj_id);
        $this->db->manipulateF('DELETE FROM frm_settings WHERE obj_id = %s', ['integer'], $obj_id);
        $this->db->manipulateF('DELETE FROM frm_user_read WHERE obj_id = %s', ['integer'], $obj_id);
        $this->db->manipulateF('DELETE FROM frm_thread_access WHERE obj_id = %s', ['integer'], $obj_id);
        $this->db->manipulate('DELETE FROM frm_notification WHERE ' . $this->db->in(
            'thread_id',
            $thread_ids_to_delete,
            false,
            'integer'
        ));
        $this->db->manipulateF('DELETE FROM frm_notification WHERE  frm_id = %s', ['integer'], $obj_id);
        $this->db->manipulateF('DELETE FROM frm_posts_deleted WHERE obj_id = %s', ['integer'], $obj_id);
        $this->deleteDraftsByForumId($topData->getTopPk());

        return true;
    }

    private function deleteDraftsByForumId(int $forum_id): void
    {
        $res = $this->db->queryF(
            'SELECT draft_id FROM frm_posts_drafts WHERE forum_id = %s',
            ['integer'],
            [$forum_id]
        );

        $draft_ids = [];
        while ($row = $this->db->fetchAssoc($res)) {
            $draft_ids[] = (int) $row['draft_id'];
        }

        if ($draft_ids !== []) {
            $historyObj = new ilForumDraftsHistory();
            $historyObj->deleteHistoryByDraftIds($draft_ids);

            $draftObj = new ilForumPostDraft();
            $draftObj->deleteDraftsByDraftIds($draft_ids);
        }
    }

    public function initDefaultRoles(): void
    {
        ilObjRole::createDefaultRole(
            'il_frm_moderator_' . $this->getRefId(),
            "Moderator of forum obj_no." . $this->getId(),
            'il_frm_moderator',
            $this->getRefId()
        );
    }

    public static function _lookupModeratorRole(int $a_ref_id): int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $mod_title = 'il_frm_moderator_' . $a_ref_id;

        $res = $ilDB->queryF('SELECT obj_id FROM object_data WHERE title = %s', ['text'], [$mod_title]);
        while ($row = $ilDB->fetchObject($res)) {
            return (int) $row->obj_id;
        }

        return 0;
    }

    public function createSettings(): void
    {
        global $DIC;

        $ref_id = 0;
        if ($DIC->http()->wrapper()->query()->has('ref_id')) {
            $ref_id = $DIC->http()->wrapper()->query()->retrieve(
                'ref_id',
                $DIC->refinery()->kindlyTo()->int()
            );
        }

        // news settings (public notifications yes/no)
        $default_visibility = ilNewsItem::_getDefaultVisibilityForRefId($ref_id);
        if ($default_visibility === 'public') {
            ilBlockSetting::_write('news', 'public_notifications', '1', 0, $this->getId());
        }
    }

    public function saveData(): void
    {
        $nextId = $this->db->nextId('frm_data');

        $top_data = [
            'top_frm_fk' => $this->getId(),
            'top_name' => $this->getTitle(),
            'top_description' => $this->getDescription(),
            'top_num_posts' => 0,
            'top_num_threads' => 0,
            'top_last_post' => null,
            'top_mods' => 0,
            'top_usr_id' => $this->user->getId(),
            'top_date' => ilUtil::now()
        ];

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
            [
                'integer',
                'integer',
                'text',
                'text',
                'integer',
                'integer',
                'text',
                'integer',
                'timestamp',
                'integer'
            ],
            [
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
            ]
        );
    }

    public function setThreadSorting(int $a_thr_pk, int $a_sorting_value): void
    {
        $this->db->update(
            'frm_threads',
            ['thread_sorting' => ['integer', $a_sorting_value]],
            ['thr_pk' => ['integer', $a_thr_pk]]
        );
    }

    public static function lookupForumIdByObjId(int $obj_id): int
    {
        if (array_key_exists($obj_id, self::$obj_id_to_forum_id_cache)) {
            return self::$obj_id_to_forum_id_cache[$obj_id];
        }

        self::preloadForumIdsByObjIds([$obj_id]);

        return self::$obj_id_to_forum_id_cache[$obj_id];
    }

    public static function lookupForumIdByRefId(int $ref_id): int
    {
        if (array_key_exists($ref_id, self::$ref_id_to_forum_id_cache)) {
            return self::$ref_id_to_forum_id_cache[$ref_id];
        }

        self::preloadForumIdsByRefIds([$ref_id]);

        return self::$ref_id_to_forum_id_cache[$ref_id];
    }

    /**
     * @param int[] $obj_ids
     */
    public static function preloadForumIdsByObjIds(array $obj_ids): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (count($obj_ids) === 1) {
            $in = ' objr.obj_id = ' . $ilDB->quote(current($obj_ids), 'integer') . ' ';
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
            self::$obj_id_to_forum_id_cache[$obj_id] = 0;
        }

        while ($row = $ilDB->fetchAssoc($res)) {
            self::$obj_id_to_forum_id_cache[(int) $row['obj_id']] = (int) $row['top_pk'];
            self::$ref_id_to_forum_id_cache[(int) $row['ref_id']] = (int) $row['top_pk'];
        }
    }

    /**
     * @param int[] $ref_ids
     */
    public static function preloadForumIdsByRefIds(array $ref_ids): void
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (count($ref_ids) === 1) {
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
            self::$ref_id_to_forum_id_cache[$ref_id] = 0;
        }

        while ($row = $ilDB->fetchAssoc($res)) {
            self::$obj_id_to_forum_id_cache[(int) $row['obj_id']] = (int) $row['top_pk'];
            self::$ref_id_to_forum_id_cache[(int) $row['ref_id']] = (int) $row['top_pk'];
        }
    }

    /**
     * @return array{num_posts: int, num_unread_posts: int, num_new_posts: int}
     */
    public static function lookupStatisticsByRefId(int $ref_id): array
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilUser = $DIC->user();
        $ilDB = $DIC->database();
        $ilSetting = $DIC->settings();

        if (isset(self::$forum_statistics_cache[$ref_id])) {
            return self::$forum_statistics_cache[$ref_id];
        }

        $statistics = [
            'num_posts' => 0,
            'num_unread_posts' => 0,
            'num_new_posts' => 0
        ];

        $forumId = self::lookupForumIdByRefId($ref_id);
        if ($forumId === 0) {
            self::$forum_statistics_cache[$ref_id] = $statistics;
            return self::$forum_statistics_cache[$ref_id];
        }

        $objProperties = ilForumProperties::getInstance(ilObject::_lookupObjectId($ref_id));
        $is_post_activation_enabled = $objProperties->isPostActivationEnabled();

        $act_clause = '';

        if ($is_post_activation_enabled && !$ilAccess->checkAccess('moderate_frm', '', $ref_id)) {
            $act_clause .= ' AND (frm_posts.pos_status = ' . $ilDB->quote(
                1,
                'integer'
            ) . ' OR frm_posts.pos_author_id = ' . $ilDB->quote($ilUser->getId(), 'integer') . ') ';
        }

        $weeks = self::NEWS_NEW_CONSIDERATION_WEEKS;
        if ($ilSetting->get('frm_store_new')) {
            $weeks = (int) $ilSetting->get('frm_store_new');
        }
        $new_deadline = time() - 60 * 60 * 24 * 7 * $weeks;

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

            $types = ['integer', 'integer', 'integer'];
            $values = [$forumId, $ilUser->getId(), $forumId];

            $forum_overview_setting = (int) ilSetting::_lookupValue('frma', 'forum_overview');
            if ($forum_overview_setting === ilForumProperties::FORUM_OVERVIEW_WITH_NEW_POSTS) {
                $news_types = ['integer', 'integer', 'integer', 'timestamp', 'integer'];
                $news_values = [$ilUser->getId(), $ilUser->getId(), $forumId, $new_deadline, $ilUser->getId()];

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

                if ($i === 1) {
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
            $types = ['integer'];
            $values = [$forumId];
            $res = $ilDB->queryF(
                $query,
                $types,
                $values
            );
            $row = $ilDB->fetchAssoc($res);

            $statistics = [
                'num_posts' => (int) $row['cnt'],
                'num_unread_posts' => (int) $row['cnt'],
                'num_new_posts' => (int) $row['cnt']
            ];
        }

        self::$forum_statistics_cache[$ref_id] = $statistics;

        return self::$forum_statistics_cache[$ref_id];
    }

    public static function lookupLastPostByRefId(int $ref_id): ?array
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilUser = $DIC->user();
        $ilDB = $DIC->database();

        if (array_key_exists($ref_id, self::$forum_last_post_cache)) {
            return self::$forum_last_post_cache[$ref_id];
        }

        $forumId = self::lookupForumIdByRefId($ref_id);
        if ($forumId === 0) {
            self::$forum_last_post_cache[$ref_id] = [];
            return self::$forum_last_post_cache[$ref_id];
        }

        $act_clause = '';
        if (!$ilAccess->checkAccess('moderate_frm', '', $ref_id)) {
            $act_clause .= ' AND (frm_posts.pos_status = ' . $ilDB->quote(
                1,
                'integer'
            ) . ' OR frm_posts.pos_author_id = ' . $ilDB->quote($ilUser->getId(), 'integer') . ') ';
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
            ['integer'],
            [$forumId]
        );

        $data = $ilDB->fetchAssoc($res);
        if (!is_array($data) || empty($data)) {
            self::$forum_last_post_cache[$ref_id] = null;
            return self::$forum_last_post_cache[$ref_id];
        }

        $casted_data = [];
        $casted_data['pos_pk'] = (int) $data['pos_pk'];
        $casted_data['pos_top_fk'] = (int) $data['pos_top_fk'];
        $casted_data['pos_thr_fk'] = (int) $data['pos_thr_fk'];
        $casted_data['pos_usr_alias'] = (string) $data['pos_usr_alias'];
        $casted_data['pos_subject'] = (string) $data['pos_subject'];
        $casted_data['pos_date'] = (string) $data['pos_date'];
        $casted_data['pos_update'] = (string) $data['pos_update'];
        $casted_data['update_user'] = (int) $data['update_user'];
        $casted_data['pos_cens'] = (int) $data['pos_cens'];
        $casted_data['pos_cens_com'] = (string) $data['pos_cens_com'];
        $casted_data['notify'] = (int) $data['notify'];
        $casted_data['import_name'] = (string) $data['import_name'];
        $casted_data['pos_status'] = (int) $data['pos_status'];
        $casted_data['pos_message'] = (string) $data['pos_message'];
        $casted_data['pos_author_id'] = (int) $data['pos_author_id'];
        $casted_data['pos_display_user_id'] = (int) $data['pos_display_user_id'];
        $casted_data['is_author_moderator'] = (int) $data['is_author_moderator'];
        $casted_data['pos_cens_date'] = (string) $data['pos_cens_date'];
        $casted_data['pos_activation_date'] = (string) $data['pos_activation_date'];

        self::$forum_last_post_cache[$ref_id] = $casted_data;

        return self::$forum_last_post_cache[$ref_id];
    }

    /**
     * @param int[] $thread_ids
     * @return int[]
     */
    public static function getUserIdsOfLastPostsByRefIdAndThreadIds(int $ref_id, array $thread_ids): array
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilUser = $DIC->user();
        $ilDB = $DIC->database();

        $act_clause = '';
        $act_inner_clause = '';
        if (!$ilAccess->checkAccess('moderate_frm', '', $ref_id)) {
            $act_clause .= " AND (t1.pos_status = " . $ilDB->quote(
                1,
                "integer"
            ) . " OR t1.pos_author_id = " . $ilDB->quote($ilUser->getId(), "integer") . ") ";
            $act_inner_clause .= " AND (t3.pos_status = " . $ilDB->quote(
                1,
                "integer"
            ) . " OR t3.pos_author_id = " . $ilDB->quote($ilUser->getId(), "integer") . ") ";
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

        $usr_ids = [];

        $res = $ilDB->query($query);
        while ($row = $ilDB->fetchAssoc($res)) {
            if ((int) $row['pos_display_user_id'] !== 0) {
                $usr_ids[] = (int) $row['pos_display_user_id'];
            }
            if ((int) $row['update_user'] !== 0) {
                $usr_ids[] = (int) $row['update_user'];
            }
        }

        return array_unique($usr_ids);
    }

    public static function mergeForumUserRead(int $merge_source_thread_id, int $merge_target_thread_id): void
    {
        global $DIC;

        $DIC->database()->update(
            'frm_user_read',
            ['thread_id' => ['integer', $merge_target_thread_id]],
            ['thread_id' => ['integer', $merge_source_thread_id]]
        );
    }

    public function getNumStickyThreads(): int
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

    /**
     * @return int[]
     */
    public function getPageObjIds(): array
    {
        $pageObjIds = [];

        $sql = 'SELECT DISTINCT page_id FROM page_object WHERE parent_id = %s AND parent_type = %s';
        $res = $this->db->queryF(
            $sql,
            ['integer', 'text'],
            [$this->getId(), $this->getType()]
        );

        while ($row = $this->db->fetchAssoc($res)) {
            $pageObjIds[] = (int) $row['page_id'];
        }

        return $pageObjIds;
    }
}
