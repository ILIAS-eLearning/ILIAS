<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumPostsDeleted
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumPostsDeleted
{
    /**
     * @var int
     */
    protected $deleted_id = 0;
    /**
     * @var int
     */
    protected $deletion_timestamp = 0;
    /**
     * @var string
     */
    protected $deleted_by = '';

    /**
     * @var string
     */
    protected $forum_title = '';
    /**
     * @var string
     */
    protected $thread_title = '';

    /**
     * @var string
     */
    protected $post_title = '';
    /**
     * @var string
     */
    protected $post_message = '';

    /**
     * @var int
     */
    protected $post_creation_timestamp = 0;

    /**
     * @var int
     */
    protected $obj_id = 0;
    /**
     * @var int
     */
    protected $ref_id = 0;
    /**
     * @var int
     */
    protected $thread_id = 0;

    /**
     * @var int
     */
    protected $forum_id = 0;

    /**
     * @var int
     */
    protected $pos_display_user_id = 0;
    /**
     * @var string
     */
    protected $pos_usr_alias = '';

    /**
     * @var bool
     */
    protected $thread_deleted = false;
    
    private $user;
    private $db;

    /**
     * @param ilObjForumNotificationDataProvider|NULL $provider
     */
    public function __construct(ilObjForumNotificationDataProvider $provider = null)
    {
        global $DIC;
        $this->user = $DIC->user();
        $this->db = $DIC->database();
        
        if (is_object($provider)) {
            if ($provider->objPost->getUserAlias() && $provider->objPost->getDisplayUserId() == 0
                && $provider->objPost->getPosAuthorId() == $DIC->user()->getId()) {
                $this->setDeletedBy($provider->objPost->getUserAlias());
            } else {
                $this->setDeletedBy($this->user->getLogin());
            }
            
            $this->setDeletionTimestamp(time());
            $this->setForumTitle($provider->getForumTitle());
            $this->setThreadTitle($provider->getThreadTitle());
            $this->setPostTitle($provider->getPostTitle());
            
            if ($provider->getPostCensored() == 1) {
                $this->setPostMessage($provider->getCensorshipComment());
            } else {
                $this->setPostMessage($provider->getPostMessage());
            }
            
            $this->setPostCreationTimestamp($provider->getPostCreationTimestamp());
            $this->setObjId($provider->getObjId());
            $this->setRefId($provider->getRefId());
            $this->setThreadId($provider->getThreadId());
            $this->setForumId($provider->getForumId());
            $this->setPosDisplayUserId($provider->getPosDisplayUserId());
            $this->setPosUserAlias($provider->getPosUserAlias());
        }
    }

    /**
     *
     */
    public function insert()
    {
        $next_id = $this->db->nextId('frm_posts_deleted');

        $this->db->insert('frm_posts_deleted', [
            'deleted_id' => ['integer', $next_id],
            'deleted_date' => ['integer', $this->getDeletionTimestamp()],
            'deleted_by' => ['text', $this->getDeletedBy()],
            'forum_title' => ['text', $this->getForumTitle()],
            'thread_title' => ['text', $this->getThreadTitle()],
            'post_title' => ['text', $this->getPostTitle()],
            'post_message' => ['text', $this->getPostMessage()],
            'post_date' => ['integer', $this->getPostCreationTimestamp()],
            'obj_id' => ['integer', $this->getObjId()],
            'ref_id' => ['integer', $this->getRefId()],
            'thread_id' => ['integer', $this->getThreadId()],
            'forum_id' => ['integer', $this->getForumId()],
            'pos_display_user_id' => ['integer', $this->getPosDisplayUserId()],
            'pos_usr_alias' => ['text', $this->getPosUserAlias()],
            'is_thread_deleted' => ['integer', $this->isThreadDeleted()]
        ]);
    }

    /**
     *
     */
    public function deleteNotifiedEntries()
    {
        $this->db->manipulateF('DELETE FROM frm_posts_deleted WHERE deleted_id > %s', array('integer'), array(0));
    }


    //----------------// SETTER & GETTER //----------------//
    /**
     * @return int
     */
    public function getDeletedId()
    {
        return $this->deleted_id;
    }

    /**
     * @param int $deleted_id
     */
    public function setDeletedId($deleted_id)
    {
        $this->deleted_id = $deleted_id;
    }

    /**
     * @return int
     */
    public function getDeletionTimestamp() : int
    {
        return $this->deletion_timestamp;
    }

    /**
     * @param int $timestamp
     */
    public function setDeletionTimestamp(int $timestamp)
    {
        $this->deletion_timestamp = $timestamp;
    }

    /**
     * @return string
     */
    public function getDeletedBy()
    {
        return $this->deleted_by;
    }

    /**
     * @param string $deleted_by
     */
    public function setDeletedBy($deleted_by)
    {
        $this->deleted_by = $deleted_by;
    }

    /**
     * @return string
     */
    public function getForumTitle()
    {
        return $this->forum_title;
    }

    /**
     * @param string $forum_title
     */
    public function setForumTitle($forum_title)
    {
        $this->forum_title = $forum_title;
    }

    /**
     * @return string
     */
    public function getThreadTitle()
    {
        return $this->thread_title;
    }

    /**
     * @param string $thread_title
     */
    public function setThreadTitle($thread_title)
    {
        $this->thread_title = $thread_title;
    }

    /**
     * @return string
     */
    public function getPostTitle()
    {
        return $this->post_title;
    }

    /**
     * @param string $post_title
     */
    public function setPostTitle($post_title)
    {
        $this->post_title = $post_title;
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
     * @return int
     */
    public function getPostCreationTimestamp(): int
    {
        return $this->post_creation_timestamp;
    }

    /**
     * @param int $timestamp
     */
    public function setPostCreationTimestamp(int $timestamp)
    {
        $this->post_creation_timestamp = $timestamp;
    }

    /**
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * @param int $obj_id
     */
    public function setObjId($obj_id)
    {
        $this->obj_id = $obj_id;
    }

    /**
     * @return int
     */
    public function getRefId()
    {
        return $this->ref_id;
    }

    /**
     * @param int $ref_id
     */
    public function setRefId($ref_id)
    {
        $this->ref_id = $ref_id;
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
    public function getPosDisplayUserId()
    {
        return $this->pos_display_user_id;
    }

    /**
     * @param int $pos_display_user_id
     */
    public function setPosDisplayUserId($pos_display_user_id)
    {
        $this->pos_display_user_id = $pos_display_user_id;
    }

    /**
     * @return string
     */
    public function getPosUserAlias()
    {
        return $this->pos_usr_alias;
    }

    /**
     * @param string $pos_usr_alias
     */
    public function setPosUserAlias($pos_usr_alias)
    {
        $this->pos_usr_alias = $pos_usr_alias;
    }

    /**
     * @return boolean
     */
    public function isThreadDeleted()
    {
        return $this->thread_deleted;
    }

    /**
     * @param boolean $thread_deleted
     */
    public function setThreadDeleted($thread_deleted)
    {
        $this->thread_deleted = $thread_deleted;
    }
}
