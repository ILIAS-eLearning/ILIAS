<?php declare(strict_types=1);
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumCronNotificationDataProvider
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumCronNotificationDataProvider implements ilForumNotificationMailData
{
    /**
     * @var int|null
     */
    public ?int $notification_type = null;

    /**
     * @var int $ref_id
     */
    protected int $ref_id = 0;

    /**
     * @var int $obj_id
     */
    protected $obj_id = 0;

    /**
     * @var int
     */
    protected $forum_id = 0;

    /**
     * @var string $forum_title
     */
    protected $forum_title = '';

    /**
     * @var int
     */
    protected $thread_id = 0;

    /**
     * @var string $thread_title
     */
    protected $thread_title = '';

    /**
     * @var int
     */
    protected $post_id = 0;
    /**
     * @var string
     */
    protected $post_title = '';
    /**
     * @var string
     */
    protected $post_message = '';
    /**
     * @var null
     */
    protected $post_date = null;
    /**
     * @var null
     */
    protected $post_update = null;

    /**
     * @var bool
     */
    protected $post_censored = false;
    /**
     * @var null
     */
    protected $post_censored_date = null;
    /**
     * @var string
     */
    protected $post_censored_comment = '';

    /**
     * @var string
     */
    protected $pos_usr_alias = '';
    /**
     * @var int
     */
    protected $pos_display_user_id = 0;

    /**
     * @var bool
     */
    protected $is_anonymized = false;
    
    /**
     * @var int|string
     */
    protected $import_name = '';
    
    /**
     * @var array $attachments
     */
    protected $attachments = array();

    /**
     * @var array $cron_recipients user_ids
     */
    protected $cron_recipients = array();

    /**
     * @var int
     */
    public $post_update_user_id = 0;
    
    /**
     * @var int
     */
    public $pos_author_id = 0;

    /**
     * @var string
     */
    public $deleted_by = '';

    /**
     * @var \ilForumAuthorInformation[]
     */
    protected static $authorInformationCache = array();


    /** @var string|null $post_user_name */
    private $post_user_name = null;

    /** @var string|null */
    private $update_user_name = null;

    /** @var ilForumNotificationCache */
    private $notificationCache;
    
    /**
     * @param ilForumNotificationCache|null $notificationCache
     */
    public function __construct($row, int $notification_type, ilForumNotificationCache $notificationCache = null)
    {
        $this->notification_type = $notification_type;
        $this->obj_id = $row['obj_id'];
        $this->ref_id = $row['ref_id'];

        $this->thread_id = $row['thread_id'];
        $this->thread_title = $row['thr_subject'];

        $this->forum_id = $row['pos_top_fk'];
        $this->forum_title = $row['top_name'];

        $this->post_id = $row['pos_pk'];
        $this->post_title = $row['pos_subject'];
        $this->post_message = $row['pos_message'];
        $this->post_date = $row['pos_date'];
        $this->post_update = $row['pos_update'];
        $this->post_update_user_id = $row['update_user'];

        $this->post_censored = $row['pos_cens'];
        $this->post_censored_date = $row['pos_cens_date'];
        $this->post_censored_comment = $row['pos_cens_com'];

        $this->pos_usr_alias = $row['pos_usr_alias'];
        $this->pos_display_user_id = $row['pos_display_user_id'];
        $this->pos_author_id = $row['pos_author_id'];

        $this->import_name = strlen($row['import_name']) ? $row['import_name'] : '';

        if ($notificationCache === null) {
            $notificationCache = new ilForumNotificationCache();
        }
        $this->notificationCache = $notificationCache;

        if (isset($row['deleted_by'])) {
            //cron context
            $this->deleted_by = $row['deleted_by'];
        } else {
            //  fallback
            global $DIC;
            $this->deleted_by = $DIC->user()->getLogin();
        }

        $this->read();
    }

    protected function read()
    {
        $this->readAttachments();
    }

    private function readAttachments()
    {
        if (ilForumProperties::isSendAttachmentsByMailEnabled()) {
            // get attachments
            $fileDataForum = new ilFileDataForum($this->getObjId(), $this->getPostId());
            $filesOfPost = $fileDataForum->getFilesOfPost();

            foreach ($filesOfPost as $attachment) {
                $this->attachments[] = $attachment['name'];
            }
        }
    }

    public function addRecipient(int $user_id)
    {
        $this->cron_recipients[] = $user_id;
    }

    public function getCronRecipients(): array
    {
        return $this->cron_recipients;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getForumId(): int
    {
        return $this->forum_id;
    }

    public function getForumTitle(): string
    {
        return $this->forum_title;
    }

    public function getThreadId(): int
    {
        return $this->thread_id;
    }

    public function getThreadTitle(): string
    {
        return $this->thread_title;
    }

    public function getPostId(): int
    {
        return $this->post_id;
    }

    public function getPostTitle(): string
    {
        return $this->post_title;
    }

    public function getPostMessage(): string
    {
        return $this->post_message;
    }

    public function getPostDate(): ?string
    {
        return $this->post_date;
    }

    public function getPostUpdate(): ?string
    {
        return $this->post_update;
    }

    public function getPostCensored(): bool|string
    {
        return $this->post_censored;
    }

    public function getPostCensoredDate(): ?string
    {
        return $this->post_censored_date;
    }

    public function getCensorshipComment(): string
    {
        return $this->post_censored_comment;
    }

    /**
     * @return array file names
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function setNotificationType(int $notification_type)
    {
        $this->notification_type = $notification_type;
    }

    public function getPosDisplayUserId(): int
    {
        return $this->pos_display_user_id;
    }

    public function getPosUserAlias(): string
    {
        return $this->pos_usr_alias;
    }

    public function getPostUpdateUserId(): int
    {
        return $this->post_update_user_id;
    }

    public function setPostUpdateUserId(int $post_update_user_id)
    {
        $this->post_update_user_id = $post_update_user_id;
    }

    public function setPosAuthorId(int $pos_author_id)
    {
        $this->pos_author_id = $pos_author_id;
    }
    public function getPosAuthorId() : int
    {
        return $this->pos_author_id;
    }

    public function isAnonymized(): bool
    {
        return $this->is_anonymized;
    }

    public function getDeletedBy(): int|string
    {
        return $this->deleted_by;
    }

    /**
     * @return string
     */
    public function getImportName()
    {
        return $this->import_name;
    }

    /**
     * @inheritdoc
     */
    public function getPostUserName(\ilLanguage $user_lang)
    {
        if ($this->post_user_name === null) {
            $this->post_user_name = $this->getPublicUserInformation(self::getAuthorInformation(
                $user_lang,
                $this->getPosAuthorId(),
                $this->getPosDisplayUserId(),
                $this->getPosUserAlias(),
                (string) $this->getImportName()
            ));
        }

        return $this->post_user_name;
    }
    
    public function getPostUpdateUserName(\ilLanguage $user_lang) : string
    {
        if ($this->update_user_name === null) {
            $this->update_user_name = $this->getPublicUserInformation(self::getAuthorInformation(
                $user_lang,
                $this->getPosAuthorId(),
                $this->getPostUpdateUserId(),
                $this->getPosUserAlias(),
                (string) $this->getImportName()
            ));
        }

        // Fix for #25432
        if ($this->getPosUserAlias() && $this->getPosDisplayUserId() == 0
            && $this->getPosAuthorId() == $this->getPostUpdateUserId()) {
            return (string) $this->getPosUserAlias();
        }

        return (string) $this->update_user_name;
    }
    
    /**
     * @param ilForumAuthorInformation $authorinfo
     * @return string
     */
    public function getPublicUserInformation(ilForumAuthorInformation $authorinfo)
    {
        $publicName = $authorinfo->getAuthorShortName();

        if ($authorinfo->hasSuffix()) {
            $publicName = $authorinfo->getAuthorName();
        } elseif ($authorinfo->getAuthorName() && !$this->isAnonymized()) {
            $publicName = $authorinfo->getAuthorName();
        }

        return $publicName;
    }

    /**
     * @param ilLanguage $lng
     * @param            $authorUsrId
     * @param            $displayUserId
     * @param            $usrAlias
     * @param            $importName
     * @return \ilForumAuthorInformation
     */
    private function getAuthorInformation(
        \ilLanguage $lng,
        int $authorUsrId,
        int $displayUserId,
        string $usrAlias,
        string $importName
    ) {
        $cacheKey = $this->notificationCache->createKeyByValues(array(
            $this->notification_type,
            $lng->getLangKey(),
            $authorUsrId,
            $displayUserId,
            $usrAlias,
            $importName
        ));

        if (false === $this->notificationCache->exists($cacheKey)) {
            $authorInformation = new ilForumAuthorInformation(
                $authorUsrId,
                $displayUserId,
                $usrAlias,
                $importName,
                array(),
                $lng
            );

            $this->notificationCache->store($cacheKey, $authorInformation);
        }

        return $this->notificationCache->fetch($cacheKey);
    }
}
