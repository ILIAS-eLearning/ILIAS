<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @author Michael Jansen <mjansen@databay.de>
* @version $Id$
*
* @ingroup ModulesForum
*/
class ilForumPost
{
    private $id = 0;
    
    private $forum_id = 0;
    
    private $thread_id = 0;
    
    private $display_user_id = 0;
    
    private $user_alias = '';
    
    private $subject = '';
    
    private $message = '';
    
    private $creation_timestamp = 0;
    
    private $modification_timestamp = 0;
    
    private $user_id_update = 0;
    
    private $censored = 0;
    
    private $censorship_comment = '';
    
    private $censorship_timestamp = 0;
    
    private $notification = 0;
    
    private $import_name = '';
    
    private $status = 1;
    
    private $tree_id = 0;
    
    private $parent_id = 0;
    
    private $lft = 0;
    
    private $rgt = 0;
    
    private $depth = 0;
    
    private $fullname = '';
    
    private $loginname = '';
    
    /**
     * @var ilForumTopic
     */
    private $objThread = null;
    
    private $db = null;
    private $lng = null;

    /**
     *  current user in a forum
     * @var bool
     */
    private $is_moderator = false;
    
    /**
     * author_id of a post is a moderator
     * @var int|null
     */
    private $is_author_moderator = null;
    
    private $post_read = false;
    
    private $pos_author_id = 0;
    
    private $post_activation_timestamp = 0;
    
    /**
     * ilForumPost constructor.
     * @param int  $a_id
     * @param bool $a_is_moderator
     * @param bool $preventImplicitRead
     */
    public function __construct($a_id = 0, $a_is_moderator = false, $preventImplicitRead = false)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->lng = $DIC->language();
        $this->id = $a_id;

        if (!$preventImplicitRead) {
            $this->read();
        }
    }
    
    public function __destruct()
    {
        unset($this->db);
        unset($this->objThread);
    }
    
    public function insert()
    {
        if ($this->forum_id && $this->thread_id) {
            $this->id = $this->db->nextId('frm_posts');

            $this->db->insert('frm_posts', [
                'pos_pk' => ['integer', $this->id],
                'pos_top_fk' => ['integer', $this->forum_id],
                'pos_thr_fk' => ['integer', $this->thread_id],
                'pos_display_user_id' => ['integer', $this->display_user_id],
                'pos_usr_alias' => ['text', $this->user_alias],
                'pos_subject' => ['text', $this->subject],
                'pos_message' => ['clob', $this->message],
                'pos_date' => ['integer', $this->creation_timestamp],
                'pos_update' => ['integer', $this->creation_timestamp],
                'update_user' => ['integer', $this->user_id_update],
                'pos_cens' => ['integer', $this->censored],
                'notify' => ['integer', (int) $this->notification],
                'import_name' => ['text', (string) $this->import_name],
                'pos_status' => ['integer', (int) $this->status],
                'pos_author_id' => ['integer', (int) $this->pos_author_id],
                'is_author_moderator' => ['integer', $this->is_author_moderator],
                'pos_activation_date' => ['integer', $this->creation_timestamp]
            ]);

            return true;
        }
        
        return false;
    }
    
    public function update()
    {
        if ($this->id) {
            $this->db->update(
                'frm_posts',
                [
                    'pos_top_fk' => ['integer', $this->forum_id],
                    'pos_thr_fk' => ['integer', $this->thread_id],
                    'pos_subject' => ['text', $this->subject],
                    'pos_message' => ['clob', $this->message],
                    'pos_update' => ['integer', $this->modification_timestamp],
                    'update_user' => ['integer', $this->user_id_update],
                    'pos_cens' => ['integer', $this->censored],
                    'pos_cens_date' => ['integer', $this->censorship_timestamp],
                    'pos_cens_com' => ['text', $this->censorship_comment],
                    'notify' => ['integer', (int) $this->notification],
                    'pos_status' => ['integer', (int) $this->status]
                ],
                [
                    'pos_pk' => ['integer', (int) $this->id]
                ]
            );
            
            if ($this->objThread->getFirstPostId() == $this->id) {
                $this->objThread->setSubject($this->subject);
                $this->objThread->update();
                $this->objThread->reload();
            }
            
            return true;
        }
        
        return false;
    }
    
    private function read()
    {
        if ($this->id) {
            $res = $this->db->queryF(
                '
				SELECT * FROM frm_posts
				INNER JOIN frm_posts_tree ON pos_fk = pos_pk
				WHERE pos_pk = %s',
                array('integer'),
                array($this->id)
            );
            $row = $this->db->fetchObject($res);

            if (is_object($row)) {
                $this->id = $row->pos_pk;
                $this->forum_id = $row->pos_top_fk;
                $this->thread_id = $row->pos_thr_fk;
                $this->display_user_id = $row->pos_display_user_id;
                $this->user_alias = $row->pos_usr_alias;
                $this->subject = $row->pos_subject;
                $this->message = $row->pos_message;
                $this->creation_timestamp = (int) $row->pos_date;
                $this->modification_timestamp = (int) $row->pos_update;
                $this->user_id_update = $row->update_user;
                $this->censored = $row->pos_cens;
                $this->censorship_timestamp = (int) $row->pos_cens_date;
                $this->censorship_comment = $row->pos_cens_com;
                $this->notification = $row->notify;
                $this->import_name = $row->import_name;
                $this->status = $row->pos_status;
                $this->tree_id = $row->fpt_pk;
                $this->parent_id = $row->parent_pos;
                $this->lft = $row->lft;
                $this->rgt = $row->rgt;
                $this->depth = $row->depth;
                $this->pos_author_id = $row->pos_author_id;
                $this->is_author_moderator = $row->is_author_moderator;
                $this->post_activation_timestamp = (int) $row->pos_activation_date;
                $this->getUserData();
                
                $this->objThread = new ilForumTopic($this->thread_id, $this->is_moderator);
                
                return true;
            }
            $this->id = 0;
            return false;
        }
        
        return false;
    }
    
    public function isAnyParentDeactivated()
    {
        if ($this->id) {
            $res = $this->db->queryF(
                '
				SELECT * FROM frm_posts_tree
				INNER JOIN frm_posts ON pos_pk = pos_fk
				WHERE pos_status = %s
				AND lft < %s AND rgt > %s
				AND thr_fk = %s',
                array('integer', 'integer', 'integer', 'integer'),
                array('0', $this->lft, $this->rgt, $this->thread_id)
            );
            
            return $res->numRows();
        }
        
        return false;
    }
    
    protected function buildUserRelatedData($row)
    {
        if ($row['pos_display_user_id'] && $row['pos_pk']) {
            $tmp_user = new ilObjUser();
            $tmp_user->setFirstname($row['firstname']);
            $tmp_user->setLastname($row['lastname']);
            $tmp_user->setUTitle($row['title']);
            $tmp_user->setLogin($row['login']);
            
            $this->fullname = $tmp_user->getFullname();
            $this->loginname = $tmp_user->getLogin();
        
            $this->fullname = $this->fullname ? $this->fullname : ($this->import_name ? $this->import_name : $this->lng->txt('unknown'));
        }
    }
    
    private function getUserData()
    {
        if ($this->id && $this->display_user_id) {
            require_once("Modules/Forum/classes/class.ilObjForumAccess.php");
            if (($tmp_user = ilObjForumAccess::getCachedUserInstance($this->display_user_id))) {
                $this->fullname = $tmp_user->getFullname();
                $this->loginname = $tmp_user->getLogin();
                unset($tmp_user);
            }
        
            $this->fullname = $this->fullname ? $this->fullname : ($this->import_name ? $this->import_name : $this->lng->txt('unknown'));
            
            return true;
        }
        
        return false;
    }
    
    public function reload()
    {
        return $this->read();
    }
    
    public function setFullname($a_fullname)
    {
        $this->fullname = $a_fullname;
    }
    public function getFullname()
    {
        return $this->fullname;
    }
    public function setLoginName($a_loginname)
    {
        $this->loginname = $a_loginname;
    }
    public function getLoginName()
    {
        return $this->loginname;
    }

    public function activatePost()
    {
        if ($this->id) {
            $current_timestamp = time();
            $this->db->update(
                'frm_posts',
                [
                    'pos_status' => ['integer', 1],
                    'pos_activation_date' => ['integer', $current_timestamp]
                ],
                ['pos_pk' => ['integer', $this->id]]
            );

            $this->activateParentPosts();
            $this->setPostActivationTimestamp($current_timestamp);
            $this->setStatus(1);
            return true;
        }
        
        return false;
    }
    
    public function activatePostAndChildPosts()
    {
        if ($this->id) {
            $query = "SELECT pos_pk FROM frm_posts_tree treea "
                   . "INNER JOIN frm_posts_tree treeb ON treeb.thr_fk = treea.thr_fk "
                   . "AND treeb.lft BETWEEN treea.lft AND treea.rgt "
                   . "INNER JOIN frm_posts ON pos_pk = treeb.pos_fk "
                   . "WHERE treea.pos_fk = %s";
            $result = $this->db->queryF(
                $query,
                ['integer'],
                [$this->id]
            );

            while ($row = $this->db->fetchAssoc($result)) {
                $this->db->update(
                    'frm_posts',
                    [
                        'pos_status' => ['integer', 1],
                        'pos_activation_date' => ['integer', time()]
                    ],
                    ['pos_pk' => ['integer', $row['pos_pk']]]
                );
            }
            
            $this->activateParentPosts();
                
            return true;
        }
        
        return false;
    }
    
    public function activateParentPosts()
    {
        if ($this->id) {
            $query = "SELECT pos_pk FROM frm_posts "
                   . "INNER JOIN frm_posts_tree ON pos_fk = pos_pk "
                   . "WHERE lft < %s AND rgt > %s AND thr_fk = %s";
            $result = $this->db->queryF(
                $query,
                ['integer', 'integer', 'integer'],
                [$this->lft, $this->rgt, $this->thread_id]
            );

            while ($row = $this->db->fetchAssoc($result)) {
                $this->db->update(
                    'frm_posts',
                    [
                        'pos_status' => ['integer', 1],
                        'pos_activation_date' => ['integer', time()]
                    ],
                    ['pos_pk' => ['integer', $row['pos_pk']]]
                );
            }
            
            return true;
        }
        
        return false;
    }
    
    public function isPostRead()
    {
        return $this->getIsRead();
    }
    
    public function isRead($a_user_id = 0)
    {
        if ($a_user_id && $this->id) {
            $res = $this->db->queryF(
                '
				SELECT * FROM frm_user_read 
			  	WHERE usr_id = %s
			 	AND post_id = %s',
                array('integer', 'integer'),
                array($a_user_id, $this->id)
            );
            
            return $res->numRows() ? true : false;
        }
        
        return false;
    }
    
    public function hasReplies()
    {
        if ($this->id && $this->rgt && $this->lft) {
            $res = $this->db->queryF(
                '
				SELECT * FROM frm_posts_tree			  		 
		  	 	WHERE lft > %s AND rgt < %s
		  	  	AND thr_fk = %s',
                array('integer', 'integer', 'integer'),
                array($this->lft, $this->rgt, $this->thread_id)
            );

            return $res->numRows() ? true : false;
        }
        
        return false;
    }
    
    public function isOwner($a_user_id = 0)
    {
        if ($this->pos_author_id && $a_user_id) {
            if ((int) $this->pos_author_id == (int) $a_user_id) {
                return true;
            }
            return false;
        }
        return false;
    }
    
    public function setId($a_id)
    {
        $this->id = $a_id;
    }
    public function getId()
    {
        return $this->id;
    }
    public function setForumId($a_forum_id)
    {
        $this->forum_id = $a_forum_id;
    }
    public function getForumId()
    {
        return $this->forum_id;
    }
    public function setThreadId($a_thread_id)
    {
        $this->thread_id = $a_thread_id;
    }
    public function getThreadId()
    {
        return $this->thread_id;
    }
    public function setDisplayUserId($a_user_id)
    {
        $this->display_user_id = $a_user_id;
    }
    public function getDisplayUserId()
    {
        return $this->display_user_id;
    }
    public function setUserAlias($a_user_alias)
    {
        $this->user_alias = $a_user_alias;
    }
    public function getUserAlias()
    {
        return $this->user_alias;
    }
    public function setSubject($a_subject)
    {
        $this->subject = $a_subject;
    }
    public function getSubject()
    {
        return $this->subject;
    }
    public function setMessage($a_message)
    {
        $this->message = $a_message;
    }
    public function getMessage()
    {
        return $this->message;
    }
    public function setCreationTimestamp(int $timestamp)
    {
        $this->creation_timestamp = $timestamp;
    }
    public function getCreationTimestamp() : int
    {
        return $this->creation_timestamp;
    }
    public function setModificationTimestamp(int $timestamp)
    {
        $this->modification_timestamp = $timestamp;
    }
    public function getModificationTimestamp() : int
    {
        return $this->modification_timestamp;
    }
    public function setUpdateUserId($a_user_id_update)
    {
        $this->user_id_update = $a_user_id_update;
    }
    public function getUpdateUserId()
    {
        return $this->user_id_update;
    }
    public function setCensorship($a_censorship)
    {
        $this->censored = $a_censorship;
    }
    public function isCensored()
    {
        return $this->censored == 1 ? true : false;
    }
    public function setCensorshipComment($a_comment)
    {
        $this->censorship_comment = $a_comment;
    }
    public function getCensorshipComment()
    {
        return $this->censorship_comment;
    }
    public function setNotification($a_notification)
    {
        $this->notification = $a_notification;
    }
    public function isNotificationEnabled()
    {
        return $this->notification == 1 ? true : false;
    }
    public function setImportName($a_import_name)
    {
        $this->import_name = $a_import_name;
    }
    public function getImportName()
    {
        return $this->import_name;
    }
    public function setStatus($a_status)
    {
        $this->status = $a_status;
    }
    public function isActivated()
    {
        return $this->status == 1 ? true : false;
    }
    public function setTreeId($a_tree_id)
    {
        $this->tree_id = $a_tree_id;
    }
    public function getTreeId()
    {
        return $this->tree_id;
    }
    public function setParentId($a_parent_id)
    {
        $this->parent_id = $a_parent_id;
    }
    
    public function setIsRead($a_is_read)
    {
        $this->post_read = $a_is_read;
    }
    
    public function getIsRead()
    {
        return $this->post_read;
    }
    
    public function getParentId()
    {
        return $this->parent_id;
    }
    public function setLft($a_lft)
    {
        $this->lft = $a_lft;
    }
    public function getLft()
    {
        return $this->lft;
    }
    public function setRgt($a_rgt)
    {
        $this->rgt = $a_rgt;
    }
    public function getRgt()
    {
        return $this->rgt;
    }
    public function setDepth($a_depth)
    {
        $this->depth = $a_depth;
    }
    public function getDepth()
    {
        return $this->depth;
    }
    public function setThread(ilForumTopic $thread)
    {
        $this->objThread = $thread;
    }
    public function getThread()
    {
        return $this->objThread;
    }

    /**
     * @param int $pos_author_id
     */
    public function setPosAuthorId($pos_author_id)
    {
        $this->pos_author_id = $pos_author_id;
    }

    /**
     * @return int
     */
    public function getPosAuthorId()
    {
        return $this->pos_author_id;
    }
    /**
     * @return int|null
     */
    public function getIsAuthorModerator()
    {
        return $this->is_author_moderator;
    }

    /**
     * @param int|null
     */
    public function setIsAuthorModerator($is_author_moderator)
    {
        $this->is_author_moderator = $is_author_moderator;
    }

    /**
     * @return int
     */
    public function getCensorshipTimestamp() : int
    {
        return $this->censorship_timestamp;
    }
    
    /**
     * @return int
     */
    public function getPostActivationTimestamp() : int
    {
        return $this->post_activation_timestamp;
    }
    
    /**
     * @param int $timestamp
     */
    public function setPostActivationTimestamp(int $timestamp)
    {
        $this->post_activation_timestamp = $timestamp;
    }

    /**
     * @param int $timestamp
     */
    public function setCensorshipTimestamp(int $timestamp)
    {
        $this->censorship_timestamp = $timestamp;
    }
    
    /**
     * @param $row
     */
    public function assignData($row)
    {
        $this->setUserAlias($row['pos_usr_alias']);
        $this->setSubject($row['pos_subject']);
        $this->setCreationTimestamp((int) $row['pos_date']);
        $this->setMessage($row['pos_message']);
        $this->setForumId($row['pos_top_fk']);
        $this->setThreadId($row['pos_thr_fk']);
        $this->setModificationTimestamp((int) $row['pos_update']);
        $this->setUpdateUserId($row['update_user']);
        $this->setCensorship($row['pos_cens']);
        $this->setCensorshipTimestamp((int) $row['pos_cens_date']);
        $this->setCensorshipComment($row['pos_cens_com']);
        $this->setNotification($row['notify']);
        $this->setImportName($row['import_name']);
        $this->setStatus($row['pos_status']);
        $this->setTreeId($row['fpt_pk']);
        $this->setParentId($row['parent_pos']);
        $this->setLft($row['lft']);
        $this->setRgt($row['rgt']);
        $this->setDepth($row['depth']);
        $this->setIsRead($row['post_read']);
        $this->setDisplayUserId($row['pos_display_user_id']);
        $this->setPosAuthorId($row['pos_author_id']);
        $this->setIsAuthorModerator($row['is_author_moderator']);
        $this->setPostActivationTimestamp((int) $row['pos_activation_date']);
        $this->buildUserRelatedData($row);
    }
    
    /**
     * @param $source_thread_id
     * @param $target_thread_id
     */
    public static function mergePosts($source_thread_id, $target_thread_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $ilDB->update(
            'frm_posts',
            array('pos_thr_fk' => array('integer', $target_thread_id)),
            array('pos_thr_fk' => array('integer', $source_thread_id))
        );
    }
}
