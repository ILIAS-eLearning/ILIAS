<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id$
 * @ingroup ModulesForum
 */
class ilForumPost
{
    private int $id = 0;
    private int $forum_id = 0;
    private int $thread_id = 0;
    private int $display_user_id = 0;
    private string $user_alias = '';
    private string $subject = '';
    private string $message = '';
    private string $createdate = '0000-00-00 00:00:00';
    private ?string $changedate = '0000-00-00 00:00:00';
    private int $user_id_update = 0;
    private int $censored = 0;
    private ?string $censorship_comment = '';
    private ?string $censored_date = '0000-00-00 00:00:00';
    private int $notification = 0;
    private string $import_name = '';
    private int $status = 1;
    private int $tree_id = 0;
    private int $parent_id = 0;
    private int $lft = 0;
    private int $rgt = 0;
    private int $depth = 0;
    private ?ilForumTopic $objThread = null;
    private $db = null;
    private bool $is_moderator = false;
    private ?int $is_author_moderator = null;
    private bool $post_read = false;
    private int $pos_author_id = 0;
    private ?string $post_activation_date = null;

    public function __construct(int $a_id = 0, $a_is_moderator = false, $preventImplicitRead = false)
    {
        global $DIC;

        $this->db = $DIC->database();
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

    public function insert() : void
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
                'pos_date' => ['timestamp', $this->createdate],
                'pos_update' => ['timestamp', $this->createdate],
                'update_user' => ['integer', $this->user_id_update],
                'pos_cens' => ['integer', $this->censored],
                'notify' => ['integer', $this->notification],
                'import_name' => ['text', $this->import_name],
                'pos_status' => ['integer', $this->status],
                'pos_author_id' => ['integer', $this->pos_author_id],
                'is_author_moderator' => ['integer', $this->is_author_moderator],
                'pos_activation_date' => ['timestamp', $this->createdate]
            ]);
        }
    }

    public function update() : bool
    {
        if ($this->id) {
            $this->db->update(
                'frm_posts',
                [
                    'pos_top_fk' => ['integer', $this->forum_id],
                    'pos_thr_fk' => ['integer', $this->thread_id],
                    'pos_subject' => ['text', $this->subject],
                    'pos_message' => ['clob', $this->message],
                    'pos_update' => ['timestamp', $this->changedate],
                    'update_user' => ['integer', $this->user_id_update],
                    'pos_cens' => ['integer', $this->censored],
                    'pos_cens_date' => ['timestamp', $this->censored_date],
                    'pos_cens_com' => ['text', $this->censorship_comment],
                    'notify' => ['integer', $this->notification],
                    'pos_status' => ['integer', $this->status]
                ],
                [
                    'pos_pk' => ['integer', $this->id]
                ]
            );

            if ($this->objThread->getFirstPostId() === $this->id) {
                $this->objThread->setSubject($this->subject);
                $this->objThread->update();
                $this->objThread->reload();
            }

            return true;
        }

        return false;
    }

    private function read() : void
    {
        if ($this->id) {
            $res = $this->db->queryF(
                '
				SELECT * FROM frm_posts
				INNER JOIN frm_posts_tree ON pos_fk = pos_pk
				WHERE pos_pk = %s',
                ['integer'],
                [$this->id]
            );
            $row = $this->db->fetchObject($res);

            if (is_object($row)) {
                $this->id = (int) $row->pos_pk;
                $this->forum_id = (int) $row->pos_top_fk;
                $this->thread_id = (int) $row->pos_thr_fk;
                $this->display_user_id = (int) $row->pos_display_user_id;
                $this->user_alias = (string) $row->pos_usr_alias;
                $this->subject = (string) $row->pos_subject;
                $this->message = (string) $row->pos_message;
                $this->createdate = (string) $row->pos_date;
                $this->changedate = (string) $row->pos_update ?? '';
                $this->user_id_update = (int) $row->update_user;
                $this->censored = (int) $row->pos_cens;
                $this->censored_date = (string) $row->pos_cens_date ?? '';
                $this->censorship_comment = (string) $row->pos_cens_com ?? '';
                $this->notification = (int) $row->notify;
                $this->import_name = (string) $row->import_name;
                $this->status = (int) $row->pos_status;
                $this->tree_id = (int) $row->fpt_pk;
                $this->parent_id = (int) $row->parent_pos;
                $this->lft = (int) $row->lft;
                $this->rgt = (int) $row->rgt;
                $this->depth = (int) $row->depth;
                $this->pos_author_id = (int) $row->pos_author_id;
                $this->is_author_moderator = (int) $row->is_author_moderator;
                $this->post_activation_date = (string) $row->pos_activation_date;

                $this->objThread = new ilForumTopic($this->thread_id, $this->is_moderator);
            }
            $this->id = 0;
        }
    }

    public function isAnyParentDeactivated() : bool
    {
        if ($this->id) {
            $res = $this->db->queryF(
                '
				SELECT * FROM frm_posts_tree
				INNER JOIN frm_posts ON pos_pk = pos_fk
				WHERE pos_status = %s
				AND lft < %s AND rgt > %s
				AND thr_fk = %s',
                ['integer', 'integer', 'integer', 'integer'],
                ['0', $this->lft, $this->rgt, $this->thread_id]
            );

            return $res->numRows();
        }

        return false;
    }

    public function reload() : bool
    {
        return $this->read();
    }

    public function activatePost() : void
    {
        if ($this->id) {
            $now = date("Y-m-d H:i:s");
            $this->db->update(
                'frm_posts',
                ['pos_status' => ['integer', 1],
                 'pos_activation_date' => ['timestamp', $now]
                ],
                ['pos_pk' => ['integer', $this->id]]
            );

            $this->activateParentPosts();
            $this->setPostActivationDate($now);
            $this->setStatus(1);
        }
    }

    public function activatePostAndChildPosts() : bool
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

            $now = date("Y-m-d H:i:s");
            while ($row = $this->db->fetchAssoc($result)) {
                $this->db->update(
                    'frm_posts',
                    ['pos_status' => ['integer', 1],
                     'pos_activation_date' => ['timestamp', $now]
                    ],
                    ['pos_pk' => ['integer', $row['pos_pk']]]
                );
            }

            $this->activateParentPosts();

            return true;
        }

        return false;
    }

    public function activateParentPosts() : bool
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

            $now = date("Y-m-d H:i:s");
            while ($row = $this->db->fetchAssoc($result)) {
                $this->db->update(
                    'frm_posts',
                    ['pos_status' => ['integer', 1],
                     'pos_activation_date' => ['timestamp', $now]
                    ],
                    ['pos_pk' => ['integer', $row['pos_pk']]]
                );
            }

            return true;
        }

        return false;
    }

    public function isPostRead() : bool
    {
        return $this->getIsRead();
    }

    public function isRead($a_user_id = 0) : bool
    {
        if ($a_user_id && $this->id) {
            $res = $this->db->queryF(
                '
				SELECT * FROM frm_user_read 
			  	WHERE usr_id = %s
			 	AND post_id = %s',
                ['integer', 'integer'],
                [$a_user_id, $this->id]
            );

            return (bool) $res->numRows();
        }

        return false;
    }

    public function hasReplies() : bool
    {
        if ($this->id && $this->rgt && $this->lft) {
            $res = $this->db->queryF(
                '
				SELECT * FROM frm_posts_tree			  		 
		  	 	WHERE lft > %s AND rgt < %s
		  	  	AND thr_fk = %s',
                ['integer', 'integer', 'integer'],
                [$this->lft, $this->rgt, $this->thread_id]
            );

            return (bool) $res->numRows();
        }

        return false;
    }

    public function isOwner($a_user_id = 0) : bool
    {
        if ($this->pos_author_id && $a_user_id) {
            if ($this->pos_author_id === (int) $a_user_id) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function setId($a_id) : void
    {
        $this->id = (int) $a_id;
    }

    public function getId() : int
    {
        return (int) $this->id;
    }

    public function setForumId($a_forum_id) : void
    {
        $this->forum_id = (int) $a_forum_id;
    }

    public function getForumId() : int
    {
        return (int) $this->forum_id;
    }

    public function setThreadId($a_thread_id) : void
    {
        $this->thread_id = (int) $a_thread_id;
    }

    public function getThreadId() : int
    {
        return (int) $this->thread_id;
    }

    public function setDisplayUserId($a_user_id) : void
    {
        $this->display_user_id = (int) $a_user_id;
    }

    public function getDisplayUserId() : int
    {
        return (int) $this->display_user_id;
    }

    public function setUserAlias($a_user_alias) : void
    {
        $this->user_alias = $a_user_alias;
    }

    public function getUserAlias() : string
    {
        return $this->user_alias;
    }

    public function setSubject($a_subject) : void
    {
        $this->subject = $a_subject;
    }

    public function getSubject() : string
    {
        return $this->subject;
    }

    public function setMessage($a_message) : void
    {
        $this->message = $a_message;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

    public function setCreateDate($a_createdate) : void
    {
        $this->createdate = $a_createdate;
    }

    public function getCreateDate() : string
    {
        return $this->createdate;
    }

    public function setChangeDate($a_changedate) : void
    {
        $this->changedate = $a_changedate;
    }

    public function getChangeDate() : string
    {
        return $this->changedate;
    }

    public function setUpdateUserId($a_user_id_update) : void
    {
        $this->user_id_update = (int) $a_user_id_update;
    }

    public function getUpdateUserId() : int
    {
        return (int) $this->user_id_update;
    }

    public function setCensorship($a_censorship) : void
    {
        $this->censored = $a_censorship;
    }

    public function isCensored() : bool
    {
        return $this->censored == 1 ? true : false;
    }

    public function setCensorshipComment($a_comment) : void
    {
        $this->censorship_comment = $a_comment;
    }

    public function getCensorshipComment() : string
    {
        return $this->censorship_comment;
    }

    public function setNotification($a_notification) : void
    {
        $this->notification = $a_notification;
    }

    public function isNotificationEnabled() : bool
    {
        return $this->notification == 1 ? true : false;
    }

    public function setImportName($a_import_name) : void
    {
        $this->import_name = $a_import_name;
    }

    public function getImportName() : string
    {
        return $this->import_name;
    }

    public function setStatus($a_status) : void
    {
        $this->status = $a_status;
    }

    public function isActivated() : bool
    {
        return $this->status == 1 ? true : false;
    }

    public function setTreeId($a_tree_id) : void
    {
        $this->tree_id = (int) $a_tree_id;
    }

    public function getTreeId() : int
    {
        return $this->tree_id;
    }

    public function setParentId($a_parent_id) : void
    {
        $this->parent_id = (int) $a_parent_id;
    }

    public function setIsRead($a_is_read) : void
    {
        $this->post_read = (bool) $a_is_read;
    }

    public function getIsRead() : bool
    {
        return $this->post_read;
    }

    public function getParentId() : int
    {
        return (int) $this->parent_id;
    }

    public function setLft($a_lft) : void
    {
        $this->lft = (int) $a_lft;
    }

    public function getLft() : int
    {
        return (int) $this->lft;
    }

    public function setRgt($a_rgt) : void
    {
        $this->rgt = (int) $a_rgt;
    }

    public function getRgt() : int
    {
        return (int) $this->rgt;
    }

    public function setDepth($a_depth) : void
    {
        $this->depth = (int) $a_depth;
    }

    public function getDepth() : int
    {
        return (int) $this->depth;
    }

    public function setThread(ilForumTopic $thread) : void
    {
        $this->objThread = $thread;
    }

    public function getThread() : ?ilForumTopic
    {
        return $this->objThread;
    }

    public function setPosAuthorId(int $pos_author_id) : void
    {
        $this->pos_author_id = $pos_author_id;
    }

    public function getPosAuthorId() : int
    {
        return (int) $this->pos_author_id;
    }

    public function getIsAuthorModerator() : ?int
    {
        return $this->is_author_moderator;
    }

    public function setIsAuthorModerator($is_author_moderator) : void
    {
        $this->is_author_moderator = $is_author_moderator;
    }

    public function getCensoredDate() : string
    {
        return $this->censored_date;
    }

    public function getPostActivationDate() : ?string
    {
        return $this->post_activation_date;
    }

    public function setPostActivationDate($post_activation_date) : void
    {
        $this->post_activation_date = $post_activation_date;
    }

    public function setCensoredDate($censored_date) : void
    {
        $this->censored_date = $censored_date;
    }

    public function assignData($row) : void
    {
        $this->setUserAlias((string) $row['pos_usr_alias']);
        $this->setSubject((string) $row['pos_subject']);
        $this->setCreateDate((string) $row['pos_date']);
        $this->setMessage((string) $row['pos_message']);
        $this->setForumId((int) $row['pos_top_fk']);
        $this->setThreadId((int) $row['pos_thr_fk']);
        $this->setChangeDate((string) $row['pos_update']);
        $this->setUpdateUserId((int) $row['update_user']);
        $this->setCensorship((int) $row['pos_cens']);

        $this->setCensoredDate(isset($row['pos_cens_date']) ? (string) $row['pos_cens_date'] : '');
        $this->setCensorshipComment($row['pos_cens_com']);
        $this->setNotification((int) $row['notify']);
        $this->setImportName((string) $row['import_name']);
        $this->setStatus((int) $row['pos_status']);
        $this->setTreeId((int) $row['fpt_pk']);
        $this->setParentId((int) $row['parent_pos']);
        $this->setLft((int) $row['lft']);
        $this->setRgt((int) $row['rgt']);
        $this->setDepth((int) $row['depth']);
        $this->setIsRead((bool) (isset($row['post_read']) && (int) $row['post_read'] ?? 1));
        $this->setDisplayUserId((int) $row['pos_display_user_id']);
        $this->setPosAuthorId((int) $row['pos_author_id']);
        $this->setIsAuthorModerator((int) $row['is_author_moderator']);
    }

    public static function mergePosts(int $sourceThreadId, int $targetThreadId, array $excludedPostIds = []) : void
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

    public static function lookupNotifyByPostId(int $post_id) : int
    {
        global $DIC;
        /** @var $DIC \ILIAS\DI\Container */

        $res = $DIC->database()->queryF(
            'SELECT notify FROM frm_posts WHERE pos_pk = %s',
            ['integer'],
            [$post_id]
        );

        $row = $DIC->database()->fetchAssoc($res);
        return (int) $row['notify'];
    }

    public static function _lookupPostMessage(int $post_id) : string
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryf(
            '
			SELECT pos_message FROM frm_posts WHERE pos_pk = %s',
            ['integer'],
            [$post_id]
        );

        if ($row = $ilDB->fetchObject($res)) {
            return $row->pos_message;
        }
        return '';
    }
}
