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
     * @var null
     */
    protected $deleted_date = null;
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
     * @var string
     */
    protected $post_date = '';

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
            $this->setDeletedBy($provider->getDeletedBy());
            
            $this->setDeletedDate(date('Y-m-d H:i:s'));
            $this->setForumTitle($provider->getForumTitle());
            $this->setThreadTitle($provider->getThreadTitle());
            $this->setPostTitle($provider->getPostTitle());
            
            if ($provider->getPostCensored() == 1) {
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

    /**
     *
     */
    public function insert()
    {
        $next_id = $this->db->nextId('frm_posts_deleted');

        $this->db->insert('frm_posts_deleted', array(
            'deleted_id'   => array('integer', $next_id),
            'deleted_date' => array('timestamp', $this->getDeletedDate()),
            'deleted_by'   => array('text', $this->getDeletedBy()),
            'forum_title'  => array('text', $this->getForumTitle()),
            'thread_title' => array('text', $this->getThreadTitle()),
            'post_title'   => array('text', $this->getPostTitle()),
            'post_message' => array('text', $this->getPostMessage()),

            'post_date'    => array('timestamp', $this->getPostDate()),
            'obj_id'       => array('integer', $this->getObjId()),
            'ref_id'       => array('integer', $this->getRefId()),
            'thread_id'    => array('integer', $this->getThreadId()),
            'forum_id'	   => array('integer', $this->getForumId()),
            'pos_display_user_id' => array('integer', $this->getPosDisplayUserId()),
            'pos_usr_alias'		=> array('text', $this->getPosUserAlias()),
            'is_thread_deleted'	=> array('integer', $this->isThreadDeleted())
        ));
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
     * @return null
     */
    public function getDeletedDate()
    {
        return $this->deleted_date;
    }

    /**
     * @param null $deleted_date
     */
    public function setDeletedDate($deleted_date)
    {
        $this->deleted_date = $deleted_date;
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
