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
    
    private $createdate = '0000-00-00 00:00:00';
    
    private $changedate = '0000-00-00 00:00:00';
    
    private $user_id_update = 0;
    
    private $censored = 0;
    
    private $censorship_comment = '';
    
    private $censored_date = '0000-00-00 00:00:00';
    
    private $notification = 0;
    
    private $import_name = '';
    
    private $status = 1;
    
    private $tree_id = 0;
    
    private $parent_id = 0;
    
    private $lft = 0;
    
    private $rgt = 0;
    
    private $depth = 0;

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
    
    private $post_activation_date = null;
    
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
            
            $this->db->insert('frm_posts', array(
                'pos_pk' => array('integer', $this->id),
                'pos_top_fk' => array('integer', $this->forum_id),
                'pos_thr_fk' => array('integer', $this->thread_id),
                'pos_display_user_id' => array('integer', $this->display_user_id),
                'pos_usr_alias' => array('text', $this->user_alias),
                'pos_subject' => array('text', $this->subject),
                'pos_message' => array('clob', $this->message),
                'pos_date' => array('timestamp', $this->createdate),
                'pos_update' => array('timestamp', $this->createdate),
                'update_user' => array('integer', $this->user_id_update),
                'pos_cens' => array('integer', $this->censored),
                'notify' => array('integer', (int) $this->notification),
                'import_name' => array('text', (string) $this->import_name),
                'pos_status' => array('integer', (int) $this->status),
                'pos_author_id' => array('integer', (int) $this->pos_author_id),
                'is_author_moderator' => array('integer', $this->is_author_moderator),
                'pos_activation_date' => array('timestamp', $this->createdate)
            ));
            
            return true;
        }
        
        return false;
    }
    
    public function update()
    {
        if ($this->id) {
            $this->db->update(
                'frm_posts',
                array(
                    'pos_top_fk' => array('integer', $this->forum_id),
                    'pos_thr_fk' => array('integer', $this->thread_id),
                    'pos_subject' => array('text', $this->subject),
                    'pos_message' => array('clob', $this->message),
                    'pos_update' => array('timestamp', $this->changedate),
                    'update_user' => array('integer', $this->user_id_update),
                    'pos_cens' => array('integer', $this->censored),
                    'pos_cens_date' => array('timestamp', $this->censored_date),
                    'pos_cens_com' => array('text', $this->censorship_comment),
                    'notify' => array('integer', (int) $this->notification),
                    'pos_status' => array('integer', (int) $this->status)
                ),
                array(
                    'pos_pk' => array('integer', (int) $this->id)
                )
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
                $this->createdate = $row->pos_date;
                $this->changedate = $row->pos_update;
                $this->user_id_update = $row->update_user;
                $this->censored = $row->pos_cens;
                $this->censored_date = $row->pos_cens_date;
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
                $this->post_activation_date = $row->pos_activation_date;
                
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
    
    public function reload()
    {
        return $this->read();
    }

    public function activatePost()
    {
        if ($this->id) {
            $now = date("Y-m-d H:i:s");
            $this->db->update(
                'frm_posts',
                array('pos_status' => array('integer', 1),
                      'pos_activation_date' => array('timestamp', $now)),
                array('pos_pk' => array('integer', $this->id))
            );

            $this->activateParentPosts();
            $this->setPostActivationDate($now);
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
                array('integer'),
                array($this->id)
            );

            $now = date("Y-m-d H:i:s");
            while ($row = $this->db->fetchAssoc($result)) {
                $this->db->update(
                    'frm_posts',
                    array('pos_status' => array('integer', 1),
                          'pos_activation_date' => array('timestamp', $now)),
                    array('pos_pk' => array('integer', $row['pos_pk']))
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
                array('integer', 'integer', 'integer'),
                array($this->lft, $this->rgt, $this->thread_id)
            );
            
            $now = date("Y-m-d H:i:s");
            while ($row = $this->db->fetchAssoc($result)) {
                $this->db->update(
                    'frm_posts',
                    array('pos_status' => array('integer', 1),
                          'pos_activation_date' => array('timestamp', $now)),
                    array('pos_pk' => array('integer', $row['pos_pk']))
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
    public function setCreateDate($a_createdate)
    {
        $this->createdate = $a_createdate;
    }
    public function getCreateDate()
    {
        return $this->createdate;
    }
    public function setChangeDate($a_changedate)
    {
        $this->changedate = $a_changedate;
    }
    public function getChangeDate()
    {
        return $this->changedate;
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
     * @return string
     */
    public function getCensoredDate()
    {
        return $this->censored_date;
    }
    
    /**
     * @return string
     */
    public function getPostActivationDate()
    {
        return $this->post_activation_date;
    }
    
    /**
     * @param string $post_activation_date
     */
    public function setPostActivationDate($post_activation_date)
    {
        $this->post_activation_date = $post_activation_date;
    }

    /**
     * @param string $censored_date
     */
    public function setCensoredDate($censored_date)
    {
        $this->censored_date = $censored_date;
    }
    
    /**
     * @param $row
     */
    public function assignData($row)
    {
        $this->setUserAlias($row['pos_usr_alias']);
        $this->setSubject($row['pos_subject']);
        $this->setCreateDate($row['pos_date']);
        $this->setMessage($row['pos_message']);
        $this->setForumId($row['pos_top_fk']);
        $this->setThreadId($row['pos_thr_fk']);
        $this->setChangeDate($row['pos_update']);
        $this->setUpdateUserId($row['update_user']);
        $this->setCensorship($row['pos_cens']);
        $this->setCensoredDate($row['pos_cens_date']);
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
    }

    /**
     * @param int $sourceThreadId
     * @param int $targetThreadId
     * @param int[] $excludedPostIds
     */
    public static function mergePosts(int $sourceThreadId, int $targetThreadId, array $excludedPostIds = [])
    {
        global $DIC;
        $ilDB = $DIC->database();

        $conditions = ['pos_thr_fk = ' . $ilDB->quote($sourceThreadId, 'integer')];
        if ($excludedPostIds !== []) {
            $conditions[] = $ilDB->in('pos_pk', $excludedPostIds, true, 'integer');
        }

        $ilDB->manipulateF(
            'UPDATE frm_posts SET pos_thr_fk = %s WHERE ' . implode(' AND ', $conditions),
            ['integer',],
            [$targetThreadId,]
        );
    }
}
