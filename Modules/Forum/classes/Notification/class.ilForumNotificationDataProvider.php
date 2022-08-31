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
 * Class ilForumNotificationDataProvider
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumNotificationDataProvider implements ilForumNotificationMailData
{
    protected int $obj_id = 0;
    protected ?string $post_user_name = null;
    protected ?string $update_user_name = null;
    public int $pos_author_id = 0;
    protected int $forum_id = 0;
    /** @var ilObjGroup|ilObjCourse */
    protected ?ilObject $closest_container = null;
    protected string $forum_title = '';
    protected string $thread_title = '';
    /** @var array<string, string> */
    protected array $attachments = [];
    private ilDBInterface $db;
    private ilAccessHandler $access;
    private ilObjUser $user;
    private ilTree $tree;
    protected bool $is_anonymized = false;

    public function __construct(
        public ilForumPost $objPost,
        protected int $ref_id,
        private ilForumNotificationCache $notificationCache
    ) {
        global $DIC;
        $this->db = $DIC->database();
        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $this->tree = $DIC->repositoryTree();
        $this->obj_id = ilObject::_lookupObjId($ref_id);
        $this->read();
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getThreadId(): int
    {
        return $this->objPost->getThreadId();
    }

    public function getPostId(): int
    {
        return $this->objPost->getId();
    }

    public function getForumId(): int
    {
        return $this->forum_id;
    }

    public function closestContainer(): ?ilObject
    {
        return $this->closest_container;
    }

    public function providesClosestContainer(): bool
    {
        return $this->closest_container !== null;
    }

    public function getForumTitle(): string
    {
        return $this->forum_title;
    }

    public function getThreadTitle(): string
    {
        return $this->thread_title;
    }

    public function getPostTitle(): string
    {
        return $this->objPost->getSubject();
    }

    public function getPostMessage(): string
    {
        return $this->objPost->getMessage();
    }

    public function getPosDisplayUserId(): int
    {
        return $this->objPost->getDisplayUserId();
    }

    public function getPostDate(): string
    {
        return $this->objPost->getCreateDate();
    }

    public function getPostUpdate(): string
    {
        return $this->objPost->getChangeDate();
    }

    public function isPostCensored(): bool
    {
        return $this->objPost->isCensored();
    }

    public function getPostCensoredDate(): string
    {
        return $this->objPost->getCensoredDate();
    }

    public function getCensorshipComment(): string
    {
        return $this->objPost->getCensorshipComment();
    }

    /**
     * @return array<string, string>
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function getPosUserAlias(): string
    {
        return $this->objPost->getUserAlias();
    }

    public function isAnonymized(): bool
    {
        return $this->is_anonymized;
    }

    public function getImportName(): string
    {
        return $this->objPost->getImportName();
    }

    public function getPostUpdateUserId(): int
    {
        return $this->objPost->getUpdateUserId();
    }

    public function getPostUserName(ilLanguage $user_lang): string
    {
        if ($this->post_user_name === null) {
            $authorinfo = new ilForumAuthorInformation(
                $this->getPosAuthorId(),
                $this->getPosDisplayUserId(),
                $this->getPosUserAlias(),
                $this->getImportName(),
                [],
                $user_lang
            );
            $this->post_user_name = $this->getPublicUserInformation($authorinfo);
        }

        return $this->post_user_name;
    }

    public function getPostUpdateUserName(ilLanguage $user_lang): string
    {
        if ($this->update_user_name === null) {
            $authorinfo = new ilForumAuthorInformation(
                $this->getPosAuthorId(),
                $this->getPostUpdateUserId(),
                $this->getPosUserAlias(),
                $this->getImportName(),
                [],
                $user_lang
            );
            $this->update_user_name = $this->getPublicUserInformation($authorinfo);
        }

        // Possible Fix for #25432
        if ($this->objPost->getUserAlias() && $this->objPost->getDisplayUserId() === 0
            && $this->objPost->getPosAuthorId() === $this->objPost->getUpdateUserId()) {
            return $this->objPost->getUserAlias();
        }

        return $this->update_user_name;
    }

    public function getPublicUserInformation(ilForumAuthorInformation $authorinfo): string
    {
        if ($authorinfo->hasSuffix()) {
            $public_name = $authorinfo->getAuthorName();
        } else {
            $public_name = $authorinfo->getAuthorShortName();

            if ($authorinfo->getAuthorName() && !$this->isAnonymized()) {
                $public_name = $authorinfo->getAuthorName();
            }
        }

        return $public_name;
    }

    protected function read(): void
    {
        $this->readForumData();
        $this->readThreadTitle();
        $this->readAttachments();
    }

    private function readThreadTitle(): void
    {
        $cacheKey = $this->notificationCache->createKeyByValues([
            'thread_title',
            $this->getObjId()
        ]);

        if (!$this->notificationCache->exists($cacheKey)) {
            $result = $this->db->queryF(
                '
				SELECT thr_subject FROM frm_threads 
				WHERE thr_pk = %s',
                ['integer'],
                [$this->objPost->getThreadId()]
            );

            $row = $this->db->fetchAssoc($result);
            $this->notificationCache->store($cacheKey, $row);
        }

        $row = $this->notificationCache->fetch($cacheKey);
        $this->thread_title = $row['thr_subject'];
    }

    private function readForumData(): void
    {
        $cacheKey = $this->notificationCache->createKeyByValues([
            'forum_data',
            $this->getObjId()
        ]);

        if (!$this->notificationCache->exists($cacheKey)) {
            $result = $this->db->queryF(
                '
				SELECT top_pk, top_name, frm_settings.anonymized FROM frm_data
				INNER JOIN frm_settings ON top_frm_fk = frm_settings.obj_id 
				WHERE top_frm_fk = %s',
                ['integer'],
                [$this->getObjId()]
            );

            $row = $this->db->fetchAssoc($result);

            $container = $this->determineClosestContainer($this->getRefId());
            if ($container instanceof ilObjCourse || $container instanceof ilObjGroup) {
                $row['closest_container'] = $container;
            }

            $this->notificationCache->store($cacheKey, $row);
        }

        $row = $row ?? $this->notificationCache->fetch($cacheKey);
        $this->forum_id = (int) $row['top_pk'];
        $this->forum_title = (string) $row['top_name'];
        $this->closest_container = $row['closest_container'] ?? null;

        $this->is_anonymized = (bool) $row['anonymized'];
    }

    public function determineClosestContainer(int $frm_ref_id): ?ilObject
    {
        $cacheKey = $this->notificationCache->createKeyByValues([
            'forum_container',
            $frm_ref_id
        ]);

        if (!$this->notificationCache->exists($cacheKey)) {
            $ref_id = $this->tree->checkForParentType($frm_ref_id, 'crs');
            if ($ref_id <= 0) {
                $ref_id = $this->tree->checkForParentType($frm_ref_id, 'grp');
            }

            if ($ref_id > 0) {
                $container = ilObjectFactory::getInstanceByRefId($ref_id);
                $this->notificationCache->store($cacheKey, $container);
                return $container;
            }
        }

        return null;
    }

    private function readAttachments(): void
    {
        if (ilForumProperties::isSendAttachmentsByMailEnabled()) {
            $fileDataForum = new ilFileDataForum($this->getObjId(), $this->objPost->getId());
            $filesOfPost = $fileDataForum->getFilesOfPost();

            $fileDataMail = new ilFileDataMail(ANONYMOUS_USER_ID);

            foreach ($filesOfPost as $attachment) {
                $this->attachments[$attachment['path']] = $attachment['name'];
                $fileDataMail->copyAttachmentFile($attachment['path'], $attachment['name']);
            }
        }
    }

    /**
     * @return int[]
     */
    public function getForumNotificationRecipients(int $notification_type): array
    {
        $event_type = $this->getEventType($notification_type);
        $cacheKey = $this->notificationCache->createKeyByValues([
            'forum',
            $notification_type,
            $this->getForumId(),
            $this->user->getId()
        ]);

        if (!$this->notificationCache->exists($cacheKey)) {
            $condition = ' ';
            if ($event_type === 0) {
                $condition = ' OR frm_notification.interested_events >= ' . $this->db->quote(0, 'integer');
            }

            $res = $this->db->queryF(
                '
			SELECT frm_notification.user_id FROM frm_notification, frm_data 
			WHERE frm_data.top_pk = %s
			AND frm_notification.frm_id = frm_data.top_frm_fk 
			AND frm_notification.user_id != %s
			AND (frm_notification.interested_events & %s ' . $condition . ')
			GROUP BY frm_notification.user_id ',
                ['integer', 'integer', 'integer'],
                [$this->getForumId(), $this->user->getId(), $event_type]
            );

            $rcps = $this->createRecipientArray($res);
            $this->notificationCache->store($cacheKey, $rcps);
        }

        $rcps = $this->notificationCache->fetch($cacheKey);

        return array_unique($rcps);
    }

    /**
     * @return int[]
     */
    public function getThreadNotificationRecipients(int $notification_type): array
    {
        if ($this->getThreadId() === 0) {
            return [];
        }

        $event_type = $this->getEventType($notification_type);
        $cacheKey = $this->notificationCache->createKeyByValues([
            'thread',
            $notification_type,
            $this->getThreadId(),
            $this->user->getId()
        ]);

        if (!$this->notificationCache->exists($cacheKey)) {
            $condition = ' ';
            if ($event_type === 0) {
                $condition = ' OR interested_events >= ' . $this->db->quote(0, 'integer');
            }

            $res = $this->db->queryF(
                '
				SELECT frm_notification.user_id
				FROM frm_notification
				INNER JOIN frm_threads ON frm_threads.thr_pk = frm_notification.thread_id
				WHERE frm_notification.thread_id = %s
				AND frm_notification.user_id != %s
				AND (frm_notification.interested_events & %s ' . $condition . ')',
                ['integer', 'integer', 'integer'],
                [$this->getThreadId(), $this->user->getId(), $event_type]
            );

            $usrIds = $this->createRecipientArray($res);
            $this->notificationCache->store($cacheKey, $usrIds);
        }

        return (array) $this->notificationCache->fetch($cacheKey);
    }

    /**
     * @return int[]
     */
    public function getPostAnsweredRecipients(): array
    {
        $cacheKey = $this->notificationCache->createKeyByValues([
            'post_answered',
            $this->objPost->getParentId()
        ]);

        if (!$this->notificationCache->exists($cacheKey)) {
            $parent_objPost = new ilForumPost($this->objPost->getParentId());

            $this->notificationCache->store($cacheKey, $parent_objPost);
        }

        /** @var ilForumPost $parent_objPost */
        $parent_objPost = $this->notificationCache->fetch($cacheKey);
        $rcps = [];
        $rcps[] = $parent_objPost->getPosAuthorId();

        return $rcps;
    }

    /**
     * @return int[]
     */
    public function getPostActivationRecipients(): array
    {
        $cacheKey = $this->notificationCache->createKeyByValues([
            'post_activation',
            $this->getRefId()
        ]);

        if (!$this->notificationCache->exists($cacheKey)) {
            // get moderators to notify about needed activation
            $rcps = ilForum::_getModerators($this->getRefId());
            $this->notificationCache->store($cacheKey, $rcps);
        }

        $rcps = $this->notificationCache->fetch($cacheKey);

        return (array) $rcps;
    }

    public function getPosAuthorId(): int
    {
        return $this->pos_author_id;
    }

    /**
     * @return int[]
     */
    private function getRefIdsByObjId(int $objId): array
    {
        $cacheKey = $this->notificationCache->createKeyByValues([
            'refs_by_obj_id',
            $objId
        ]);

        if (!$this->notificationCache->exists($cacheKey)) {
            $this->notificationCache->store($cacheKey, ilObject::_getAllReferences($objId));
        }

        return $this->notificationCache->fetch($cacheKey);
    }

    /**
     * @return int[]
     */
    private function createRecipientArray(ilDBStatement $statement): array
    {
        $refIds = $this->getRefIdsByObjId($this->getObjId());

        $usrIds = [];
        while ($row = $this->db->fetchAssoc($statement)) {
            foreach ($refIds as $refId) {
                if ($this->access->checkAccessOfUser((int) $row['user_id'], 'read', '', $refId)) {
                    $usrIds[] = (int) $row['user_id'];
                }
            }
        }

        return $usrIds;
    }

    public function getDeletedBy(): string
    {
        if ($this->objPost->getUserAlias() && $this->objPost->getDisplayUserId() === 0
            && $this->objPost->getPosAuthorId() === $this->user->getId()) {
            return $this->objPost->getUserAlias();
        }

        return $this->user->getLogin();
    }

    private function getEventType(int $notification_type): int
    {
        return match ($notification_type) {
            ilForumMailNotification::TYPE_POST_UPDATED => ilForumNotificationEvents::UPDATED,
            ilForumMailNotification::TYPE_POST_CENSORED => ilForumNotificationEvents::CENSORED,
            ilForumMailNotification::TYPE_POST_UNCENSORED => ilForumNotificationEvents::UNCENSORED,
            ilForumMailNotification::TYPE_POST_DELETED => ilForumNotificationEvents::POST_DELETED,
            ilForumMailNotification::TYPE_THREAD_DELETED => ilForumNotificationEvents::THREAD_DELETED,
            default => 0,
        };
    }
}
