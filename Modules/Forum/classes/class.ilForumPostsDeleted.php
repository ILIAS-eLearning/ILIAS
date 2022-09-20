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
 * Class ilForumPostsDeleted
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumPostsDeleted
{
    private ilDBInterface $db;
    protected int $deleted_id = 0;
    protected string $deleted_date;
    protected string $deleted_by = '';
    protected string $forum_title = '';
    protected string $thread_title = '';
    protected string $post_title = '';
    protected string $post_message = '';
    protected string $post_date = '';
    protected int $obj_id = 0;
    protected int $ref_id = 0;
    protected int $thread_id = 0;
    protected int $forum_id = 0;
    protected int $pos_display_user_id = 0;
    protected string $pos_usr_alias = '';
    protected bool $thread_deleted = false;

    public function __construct(ilForumNotificationDataProvider $provider = null)
    {
        global $DIC;

        $this->db = $DIC->database();

        if ($provider !== null) {
            if (
                $provider->objPost->getUserAlias() && $provider->objPost->getDisplayUserId() === 0 &&
                $provider->objPost->getPosAuthorId() === $DIC->user()->getId()
            ) {
                $this->setDeletedBy($provider->objPost->getUserAlias());
            } else {
                $user = $DIC->user();
                $this->setDeletedBy($user->getLogin());
            }

            $this->setDeletedDate(date('Y-m-d H:i:s'));
            $this->setForumTitle($provider->getForumTitle());
            $this->setThreadTitle($provider->getThreadTitle());
            $this->setPostTitle($provider->getPostTitle());

            if ($provider->isPostCensored()) {
                $this->setPostMessage($provider->getCensorshipComment());
            } else {
                $this->setPostMessage($provider->getPostMessage());
            }

            $this->setPostDate($provider->getPostDate());
            $this->setObjId($provider->getObjId());
            $this->setRefId($provider->getRefId());
            $this->setThreadId($provider->getThreadId());
            $this->setForumId($provider->getForumId());
            $this->setPosDisplayUserId($provider->getPosDisplayUserId());
            $this->setPosUserAlias($provider->getPosUserAlias());
        }
    }

    public function insert(): void
    {
        $next_id = $this->db->nextId('frm_posts_deleted');

        $this->db->insert('frm_posts_deleted', [
            'deleted_id' => ['integer', $next_id],
            'deleted_date' => ['timestamp', $this->getDeletedDate()],
            'deleted_by' => ['text', $this->getDeletedBy()],
            'forum_title' => ['text', $this->getForumTitle()],
            'thread_title' => ['text', $this->getThreadTitle()],
            'post_title' => ['text', $this->getPostTitle()],
            'post_message' => ['text', $this->getPostMessage()],

            'post_date' => ['timestamp', $this->getPostDate()],
            'obj_id' => ['integer', $this->getObjId()],
            'ref_id' => ['integer', $this->getRefId()],
            'thread_id' => ['integer', $this->getThreadId()],
            'forum_id' => ['integer', $this->getForumId()],
            'pos_display_user_id' => ['integer', $this->getPosDisplayUserId()],
            'pos_usr_alias' => ['text', $this->getPosUserAlias()],
            'is_thread_deleted' => ['integer', $this->isThreadDeleted()]
        ]);
    }

    public function deleteNotifiedEntries(): void
    {
        $this->db->manipulateF('DELETE FROM frm_posts_deleted WHERE deleted_id > %s', ['integer'], [0]);
    }

    public function getDeletedId(): int
    {
        return $this->deleted_id;
    }

    public function setDeletedId(int $deleted_id): void
    {
        $this->deleted_id = $deleted_id;
    }

    public function getDeletedDate(): string
    {
        return $this->deleted_date;
    }

    public function setDeletedDate(string $deleted_date): void
    {
        $this->deleted_date = $deleted_date;
    }

    public function getDeletedBy(): string
    {
        return $this->deleted_by;
    }

    public function setDeletedBy(string $deleted_by): void
    {
        $this->deleted_by = $deleted_by;
    }

    public function getForumTitle(): string
    {
        return $this->forum_title;
    }

    public function setForumTitle(string $forum_title): void
    {
        $this->forum_title = $forum_title;
    }

    public function getThreadTitle(): string
    {
        return $this->thread_title;
    }

    public function setThreadTitle(string $thread_title): void
    {
        $this->thread_title = $thread_title;
    }

    public function getPostTitle(): string
    {
        return $this->post_title;
    }

    public function setPostTitle(string $post_title): void
    {
        $this->post_title = $post_title;
    }

    public function getPostMessage(): string
    {
        return $this->post_message;
    }

    public function setPostMessage(string $post_message): void
    {
        $this->post_message = $post_message;
    }

    public function getPostDate(): string
    {
        return $this->post_date;
    }

    public function setPostDate(string $post_date): void
    {
        $this->post_date = $post_date;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setObjId(int $obj_id): void
    {
        $this->obj_id = $obj_id;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function setRefId(int $ref_id): void
    {
        $this->ref_id = $ref_id;
    }

    public function getThreadId(): int
    {
        return $this->thread_id;
    }

    public function setThreadId(int $thread_id): void
    {
        $this->thread_id = $thread_id;
    }

    public function getForumId(): int
    {
        return $this->forum_id;
    }

    public function setForumId(int $forum_id): void
    {
        $this->forum_id = $forum_id;
    }

    public function getPosDisplayUserId(): int
    {
        return $this->pos_display_user_id;
    }

    public function setPosDisplayUserId(int $pos_display_user_id): void
    {
        $this->pos_display_user_id = $pos_display_user_id;
    }

    public function getPosUserAlias(): string
    {
        return $this->pos_usr_alias;
    }

    public function setPosUserAlias(string $pos_usr_alias): void
    {
        $this->pos_usr_alias = $pos_usr_alias;
    }

    public function isThreadDeleted(): bool
    {
        return $this->thread_deleted;
    }

    public function setThreadDeleted(bool $thread_deleted): void
    {
        $this->thread_deleted = $thread_deleted;
    }
}
