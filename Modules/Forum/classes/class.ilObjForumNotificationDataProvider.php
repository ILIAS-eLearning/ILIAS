<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjForumNotificationDataProvider
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilObjForumNotificationDataProvider implements ilForumNotificationMailData
{
    /** @var int $ref_id */
    protected $ref_id = 0;
    /** @var int $obj_id */
    protected $obj_id = 0;
    /** @var string|null $post_user_name */
    protected $post_user_name = null;
    /** @var string|null $update_user_name */
    protected $update_user_name = null;
    /** @var int */
    public $pos_author_id = 0;
    /** @var int */
    protected $forum_id = 0;
    /** @var string $forum_title */
    protected $forum_title = '';
    /** @var string $thread_title */
    protected $thread_title = '';
    /** @var array $attachments */
    protected $attachments = [];
    /** @var ilForumPost */
    public $objPost;
    private $db;
    private $access;
    private $user;

    /**
     * @var bool
     */
    protected $is_anonymized = false;

    /** @var ilForumNotificationCache */
    private $notificationCache;

    /**
     * @param ilForumPost $objPost
     * @param int $ref_id
     * @param ilForumNotificationCache $notificationCache
     */
    public function __construct(ilForumPost $objPost, $ref_id, \ilForumNotificationCache $notificationCache)
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->access = $DIC->access();
        $this->user = $DIC->user();

        $this->notificationCache = $notificationCache;

        $this->objPost = $objPost;
        $this->ref_id  = $ref_id;
        $this->obj_id  = ilObject::_lookupObjId($ref_id);
        $this->read();
    }

    /**
     * @return int
     */
    public function getRefId()
    {
        return $this->ref_id;
    }

    /**
     * @return int
     */
    public function getObjId()
    {
        return $this->obj_id;
    }

    /**
     * @return int
     */
    public function getThreadId()
    {
        return $this->objPost->getThreadId();
    }

    /**
     * @return int
     */
    public function getPostId()
    {
        return $this->objPost->getId();
    }

    /**
     * @return int
     */
    public function getForumId()
    {
        return $this->forum_id;
    }

    /**
     * @return string frm_data.top_name
     */
    public function getForumTitle()
    {
        return $this->forum_title;
    }

    /**
     * @return string frm_threads.thr_subject
     */
    public function getThreadTitle()
    {
        return $this->thread_title;
    }

    /**
     * @return string frm_posts.pos_subject
     */
    public function getPostTitle()
    {
        return $this->objPost->getSubject();
    }

    /**
     * @return string frm_posts.pos_message
     */
    public function getPostMessage()
    {
        return $this->objPost->getMessage();
    }

    /**
     * @return string frm_posts.pos_display_user_id
     */
    public function getPosDisplayUserId()
    {
        return $this->objPost->getDisplayUserId();
    }

    /**
     * @return string frm_posts.pos_date
     */
    public function getPostDate()
    {
        return $this->objPost->getCreateDate();
    }

    /**
     * @return string frm_posts.pos_update
     */
    public function getPostUpdate()
    {
        return $this->objPost->getChangeDate();
    }
    /**
     * @return bool frm_posts.pos_cens
     */
    public function getPostCensored()
    {
        return $this->objPost->isCensored();
    }

    /**
     * @return string frm_posts.pos_cens_date
     */
    public function getPostCensoredDate()
    {
        return $this->objPost->getCensoredDate();
    }

    public function getCensorshipComment()
    {
        return $this->objPost->getCensorshipComment();
    }

    /**
     * @return array file names
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @return string frm_posts.pos_usr_alias
     */
    public function getPosUserAlias()
    {
        return $this->objPost->getUserAlias();
    }

    /**
     * @return bool
     */
    public function isAnonymized()
    {
        return $this->is_anonymized;
    }
    /**
     * @return string
     */
    public function getImportName()
    {
        return $this->objPost->getImportName();
    }

    /**
     * @inheritdoc
     */
    public function getPostUpdateUserId()
    {
        return $this->objPost->getUpdateUserId();
    }

    /**
     * @inheritdoc
     */
    public function getPostUserName(\ilLanguage $user_lang)
    {
        if ($this->post_user_name === null) {
            $authorinfo           = new ilForumAuthorInformation(
                $this->getPosAuthorId(),
                $this->getPosDisplayUserId(),
                $this->getPosUserAlias(),
                $this->getImportName(),
                array(),
                $user_lang
            );
            $this->post_user_name = $this->getPublicUserInformation($authorinfo);
        }

        return (string) $this->post_user_name;
    }

    /**
     * @inheritdoc
     */
    public function getPostUpdateUserName(\ilLanguage $user_lang)
    {
        if ($this->update_user_name === null) {
            $authorinfo             = new ilForumAuthorInformation(
                $this->getPosAuthorId(),
                $this->getPostUpdateUserId(),
                $this->getPosUserAlias(),
                $this->getImportName(),
                array(),
                $user_lang
            );
            $this->update_user_name = $this->getPublicUserInformation($authorinfo);
        }

        // Possible Fix for #25432
        if ($this->objPost->getUserAlias() && $this->objPost->getDisplayUserId() == 0
            && $this->objPost->getPosAuthorId() == $this->objPost->getUpdateUserId()) {
            return (string) $this->objPost->getUserAlias();
        }
        
        return (string) $this->update_user_name;
    }
    
    /**
     * @param ilForumAuthorInformation $authorinfo
     * @return string
     */
    public function getPublicUserInformation(ilForumAuthorInformation $authorinfo)
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
    
    /**
     *
     */
    protected function read()
    {
        $this->readForumData();
        $this->readThreadTitle();
        $this->readAttachments();
    }

    /**
     *
     */
    private function readThreadTitle()
    {
        $cacheKey = $this->notificationCache->createKeyByValues(array(
            'thread_title',
            $this->getObjId()
        ));

        if (false === $this->notificationCache->exists($cacheKey)) {
            $result = $this->db->queryf(
                '
				SELECT thr_subject FROM frm_threads 
				WHERE thr_pk = %s',
                array('integer'),
                array($this->objPost->getThreadId())
            );

            $row = $this->db->fetchAssoc($result);
            $this->notificationCache->store($cacheKey, $row);
        }

        $row = $this->notificationCache->fetch($cacheKey);
        $this->thread_title = $row['thr_subject'];
    }

    /**
     *
     */
    private function readForumData()
    {
        $cacheKey = $this->notificationCache->createKeyByValues(array(
            'forum_data',
            $this->getObjId()
        ));

        if (false === $this->notificationCache->exists($cacheKey)) {
            $result = $this->db->queryf(
                '
				SELECT top_pk, top_name, frm_settings.anonymized FROM frm_data
				INNER JOIN frm_settings ON top_frm_fk = frm_settings.obj_id 
				WHERE top_frm_fk = %s',
                array('integer'),
                array($this->getObjId()
            )
            );

            $row = $this->db->fetchAssoc($result);

            $this->notificationCache->store($cacheKey, $row);
        }

        $row = $this->notificationCache->fetch($cacheKey);
        $this->forum_id    = $row['top_pk'];
        $this->forum_title = $row['top_name'];
        $this->is_anonymized = (bool) $row['anonymized'];
    }

    /**
     *
     */
    private function readAttachments()
    {
        if (ilForumProperties::isSendAttachmentsByMailEnabled()) {
            $fileDataForum = new ilFileDataForum($this->getObjId(), $this->objPost->getId());
            $filesOfPost   = $fileDataForum->getFilesOfPost();
            
            $fileDataMail = new ilFileDataMail(ANONYMOUS_USER_ID);
            
            foreach ($filesOfPost as $attachment) {
                $this->attachments[$attachment['path']] = $attachment['name'];
                $fileDataMail->copyAttachmentFile($attachment['path'], $attachment['name']);
            }
        }
    }

    /**
     * @return array
     */
    public function getForumNotificationRecipients()
    {
        $cacheKey = $this->notificationCache->createKeyByValues(array(
            'forum',
            $this->getForumId(),
            $this->user->getId()
        ));

        if (false === $this->notificationCache->exists($cacheKey)) {
            $res = $this->db->queryf(
                '
			SELECT frm_notification.user_id FROM frm_notification, frm_data 
			WHERE frm_data.top_pk = %s
			AND frm_notification.frm_id = frm_data.top_frm_fk 
			AND frm_notification.user_id != %s
			GROUP BY frm_notification.user_id',
                array('integer', 'integer'),
                array($this->getForumId(), $this->user->getId())
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
    public function getThreadNotificationRecipients()
    {
        if (!$this->getThreadId()) {
            return [];
        }

        $cacheKey = $this->notificationCache->createKeyByValues(array(
            'thread',
            $this->getThreadId(),
            $this->user->getId()
        ));

        if (false === $this->notificationCache->exists($cacheKey)) {
            $res = $this->db->queryF(
                '
				SELECT frm_notification.user_id
				FROM frm_notification
				INNER JOIN frm_threads ON frm_threads.thr_pk = frm_notification.thread_id
				WHERE frm_notification.thread_id = %s
				AND frm_notification.user_id != %s',
                array('integer', 'integer'),
                array($this->getThreadId(), $this->user->getId())
            );

            $usrIds = $this->createRecipientArray($res);
            $this->notificationCache->store($cacheKey, $usrIds);
        }

        $usrIds = $this->notificationCache->fetch($cacheKey);

        return $usrIds;
    }

    /**
     * @return array
     */
    public function getPostAnsweredRecipients()
    {
        $cacheKey = $this->notificationCache->createKeyByValues(array(
            'post_answered',
            $this->objPost->getParentId()
        ));

        if (false === $this->notificationCache->exists($cacheKey)) {
            $parent_objPost = new ilForumPost($this->objPost->getParentId());

            $this->notificationCache->store($cacheKey, $parent_objPost);
        }

        $parent_objPost = $this->notificationCache->fetch($cacheKey);
        $rcps = array();
        $rcps[] = $parent_objPost->getPosAuthorId();

        return $rcps;
    }

    /**
     * @return array
     */
    public function getPostActivationRecipients()
    {
        $cacheKey = $this->notificationCache->createKeyByValues(array(
            'post_activation',
            $this->getRefId()
        ));

        if (false === $this->notificationCache->exists($cacheKey)) {
            // get moderators to notify about needed activation
            $rcps = ilForum::_getModerators($this->getRefId());
            $this->notificationCache->store($cacheKey, $rcps);
        }

        $rcps = $this->notificationCache->fetch($cacheKey);

        return (array) $rcps;
    }
    
    /**
     * @param $pos_author_id
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
     * @param int $objId
     * @return int[]
     */
    private function getRefIdsByObjId(int $objId) : array
    {
        $cacheKey = $this->notificationCache->createKeyByValues([
            'refs_by_obj_id',
            $objId
        ]);

        if (!$this->notificationCache->exists($cacheKey)) {
            $this->notificationCache->store($cacheKey, (array) ilObject::_getAllReferences($objId));
        }

        return $this->notificationCache->fetch($cacheKey);
    }

    /**
     * @param \ilPDOStatement $statement - statement to be executed by the database
     *                                     needs to a 'user_id' as result
     * @return int[]
     */
    private function createRecipientArray(\ilPDOStatement $statement) : array
    {
        $refIds = $this->getRefIdsByObjId((int) $this->getObjId());

        $usrIds = [];
        while ($row = $this->db->fetchAssoc($statement)) {
            foreach ($refIds as $refId) {
                if ($this->access->checkAccessOfUser($row['user_id'], 'read', '', $refId)) {
                    $usrIds[] = (int) $row['user_id'];
                }
            }
        }

        return $usrIds;
    }

    /**
     * @return string
     */
    public function getDeletedBy()
    {
        global $DIC;

        if ($this->shouldUsePseudonym()) {
            return (string) $this->objPost->getUserAlias();
        }
        return $DIC->user()->getLogin();
    }

    /**
     * @return bool
     */
    private function shouldUsePseudonym()
    {
        global $DIC;

        if ($this->objPost->getUserAlias() && $this->objPost->getDisplayUserId() == 0
        && $this->objPost->getPosAuthorId() == $DIC->user()->getId()) {
            return true;
        }
        return false;
    }
}
