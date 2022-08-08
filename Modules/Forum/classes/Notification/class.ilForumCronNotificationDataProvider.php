<?php declare(strict_types=1);

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
 * Class ilForumCronNotificationDataProvider
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilForumCronNotificationDataProvider implements ilForumNotificationMailData
{
    private ?int $notification_type = null;
    private int $ref_id;
    private int $obj_id;
    private int $forum_id;
    private string $forum_title;
    /** @var ilObjCourse|ilObjGroup|null */
    private ?ilObject $closest_container = null;
    private int $thread_id;
    private string $thread_title;
    private int $post_id;
    private string $post_title;
    private string $post_message;
    private ?string $post_date = null;
    private ?string $post_update = null;
    private bool $post_censored;
    private ?string $post_censored_date = null;
    private ?string $post_censored_comment;
    private string $pos_usr_alias;
    private int $pos_display_user_id;
    private bool $is_anonymized = false;
    private string $import_name;
    private array $attachments = [];
    /** @var int[] */
    private array $cron_recipients = [];
    private int $post_update_user_id;
    private int $pos_author_id;
    private ?string $deleted_by = '';
    private ?string $post_user_name = null;
    private ?string $update_user_name = null;
    private ilForumNotificationCache $notificationCache;

    public function __construct(array $row, int $notification_type, ilForumNotificationCache $notificationCache = null)
    {
        $this->notification_type = $notification_type;
        $this->obj_id = (int) $row['obj_id'];
        $this->ref_id = (int) $row['ref_id'];
        $this->closest_container = $row['closest_container'];
        $this->thread_id = (int) $row['thread_id'];
        $this->thread_title = $row['thr_subject'];
        $this->forum_id = (int) $row['pos_top_fk'];
        $this->forum_title = $row['top_name'];
        $this->post_id = (int) $row['pos_pk'];
        $this->post_title = $row['pos_subject'];
        $this->post_message = $row['pos_message'];
        $this->post_date = $row['pos_date'];
        $this->post_update = $row['pos_update'];
        $this->post_update_user_id = (int) $row['update_user'];
        $this->post_censored = (bool) $row['pos_cens'];
        $this->post_censored_date = $row['pos_cens_date'];
        $this->post_censored_comment = $row['pos_cens_com'];
        $this->pos_usr_alias = $row['pos_usr_alias'];
        $this->pos_display_user_id = (int) $row['pos_display_user_id'];
        $this->pos_author_id = (int) $row['pos_author_id'];
        $this->import_name = $row['import_name'];

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

    protected function read() : void
    {
        $this->readAttachments();
    }

    private function readAttachments() : void
    {
        if (ilForumProperties::isSendAttachmentsByMailEnabled()) {
            $fileDataForum = new ilFileDataForum($this->getObjId(), $this->getPostId());
            $filesOfPost = $fileDataForum->getFilesOfPost();

            foreach ($filesOfPost as $attachment) {
                $this->attachments[] = $attachment['name'];
            }
        }
    }

    public function addRecipient(int $user_id) : void
    {
        $this->cron_recipients[] = $user_id;
    }

    /**
     * @return int[]
     */
    public function getCronRecipients() : array
    {
        return $this->cron_recipients;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function getObjId() : int
    {
        return $this->obj_id;
    }

    public function getForumId() : int
    {
        return $this->forum_id;
    }

    public function closestContainer() : ?ilObject
    {
        return $this->closest_container;
    }

    public function providesClosestContainer() : bool
    {
        return $this->closest_container !== null;
    }

    public function getForumTitle() : string
    {
        return $this->forum_title;
    }

    public function getThreadId() : int
    {
        return $this->thread_id;
    }

    public function getThreadTitle() : string
    {
        return $this->thread_title;
    }

    public function getPostId() : int
    {
        return $this->post_id;
    }

    public function getPostTitle() : string
    {
        return $this->post_title;
    }

    public function getPostMessage() : string
    {
        return $this->post_message;
    }

    public function getPostDate() : string
    {
        return $this->post_date;
    }

    public function getPostUpdate() : string
    {
        return $this->post_update ?? '';
    }

    public function isPostCensored() : bool
    {
        return $this->post_censored;
    }

    public function getPostCensoredDate() : string
    {
        return $this->post_censored_date ?? '';
    }

    public function getCensorshipComment() : string
    {
        return $this->post_censored_comment;
    }

    public function getAttachments() : array
    {
        return $this->attachments;
    }

    public function setNotificationType(int $notification_type) : void
    {
        $this->notification_type = $notification_type;
    }

    public function getPosDisplayUserId() : int
    {
        return $this->pos_display_user_id;
    }

    public function getPosUserAlias() : string
    {
        return $this->pos_usr_alias;
    }

    public function getPostUpdateUserId() : int
    {
        return $this->post_update_user_id;
    }

    public function setPostUpdateUserId(int $post_update_user_id) : void
    {
        $this->post_update_user_id = $post_update_user_id;
    }

    public function setPosAuthorId(int $pos_author_id) : void
    {
        $this->pos_author_id = $pos_author_id;
    }

    public function getPosAuthorId() : int
    {
        return $this->pos_author_id;
    }

    public function isAnonymized() : bool
    {
        return $this->is_anonymized;
    }

    public function getDeletedBy() : string
    {
        return $this->deleted_by;
    }

    public function getImportName() : string
    {
        return $this->import_name;
    }

    public function getPostUserName(ilLanguage $user_lang) : string
    {
        if ($this->post_user_name === null) {
            $this->post_user_name = $this->getPublicUserInformation($this->getAuthorInformation(
                $user_lang,
                $this->getPosAuthorId(),
                $this->getPosDisplayUserId(),
                $this->getPosUserAlias(),
                $this->getImportName()
            ));
        }

        return (string) $this->post_user_name;
    }

    public function getPostUpdateUserName(ilLanguage $user_lang) : string
    {
        if ($this->update_user_name === null) {
            $this->update_user_name = $this->getPublicUserInformation($this->getAuthorInformation(
                $user_lang,
                $this->getPosAuthorId(),
                $this->getPostUpdateUserId(),
                $this->getPosUserAlias(),
                $this->getImportName()
            ));
        }

        // Fix for #25432
        if (
            $this->getPosUserAlias() && $this->getPosDisplayUserId() === 0 &&
            $this->getPosAuthorId() === $this->getPostUpdateUserId()
        ) {
            return $this->getPosUserAlias();
        }

        return (string) $this->update_user_name;
    }

    public function getPublicUserInformation(ilForumAuthorInformation $authorinfo) : string
    {
        $publicName = $authorinfo->getAuthorShortName();

        if ($authorinfo->hasSuffix()) {
            $publicName = $authorinfo->getAuthorName();
        } elseif ($authorinfo->getAuthorName() && !$this->isAnonymized()) {
            $publicName = $authorinfo->getAuthorName();
        }

        return $publicName;
    }

    private function getAuthorInformation(
        ilLanguage $lng,
        int $authorUsrId,
        int $displayUserId,
        string $usrAlias,
        string $importName
    ) : ilForumAuthorInformation {
        $cacheKey = $this->notificationCache->createKeyByValues([
            $this->notification_type,
            $lng->getLangKey(),
            $authorUsrId,
            $displayUserId,
            $usrAlias,
            $importName
        ]);

        if (false === $this->notificationCache->exists($cacheKey)) {
            $authorInformation = new ilForumAuthorInformation(
                $authorUsrId,
                $displayUserId,
                $usrAlias,
                $importName,
                [],
                $lng
            );

            $this->notificationCache->store($cacheKey, $authorInformation);
        }

        return $this->notificationCache->fetch($cacheKey);
    }
}
