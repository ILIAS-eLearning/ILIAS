<?php

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

declare(strict_types=1);

use ILIAS\Forum\Notification\NotificationType;

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup components\ILIASForum
 */
class ilForumProperties
{
    public const VIEW_TREE = 1;
    public const VIEW_DATE = 2;
    public const VIEW_DATE_ASC = 2;
    public const VIEW_DATE_DESC = 3;
    public const FILE_UPLOAD_GLOBALLY_ALLOWED = 0;
    public const FILE_UPLOAD_INDIVIDUAL = 1;
    public const PAGE_SIZE_THREAD_OVERVIEW = 10;
    public const PAGE_NAME_THREAD_OVERVIEW = 'page';

    /** @var array<int, ilForumProperties> */
    private static array $instances = [];

    private readonly ilDBInterface $db;
    private int $default_view = self::VIEW_DATE_ASC;
    private bool $anonymized = false;
    private bool $statistics_enabled = false;
    private bool $post_activation_enabled = false;
    private NotificationType $notification_type = NotificationType::DEFAULT;
    /** Container (course/group) enforces forum notifications for members (frm_settings.container_enforces_noti). */
    private bool $container_enforces_noti = false;
    /** Member may deactivate container-driven forum notifications (frm_settings.member_may_disable_noti). */
    private bool $member_may_disable_noti = true;
    /** If deactivated, user is forced to enter a new subject on repliees */
    private bool $preset_subject = true;
    /** Preset notification events for forced notification */
    private int $interested_events = ilForumNotificationEvents::DEACTIVATED;
    /** Add 'Re: ' to subject on reply */
    private bool $add_re_subject = false;
    private bool $mark_mod_posts = false;
    private bool $is_thread_rating_enabled = false;
    private bool $file_upload_allowed = false;
    protected int $styleId = 0;
    private bool $exists = false;
    private ?int $lp_req_num_postings = null;
    private ?\ILIAS\Style\Content\Object\ObjectFacade $content_style_service = null;

    protected function __construct(private int $obj_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->read();
    }

    private function contentStyle(): \ILIAS\Style\Content\Object\ObjectFacade
    {
        global $DIC;

        if ($this->content_style_service === null) {
            $this->content_style_service = $DIC->contentStyle()->domain()->styleForObjId($this->obj_id);
        }

        return $this->content_style_service;
    }

    public static function getInstance(int $a_obj_id = 0): self
    {
        if (!isset(self::$instances[$a_obj_id])) {
            self::$instances[$a_obj_id] = new self($a_obj_id);
        }

        return self::$instances[$a_obj_id];
    }

    private function read(): void
    {
        if ($this->obj_id !== 0) {
            $res = $this->db->queryF(
                'SELECT * FROM frm_settings WHERE obj_id = %s',
                ['integer'],
                [$this->obj_id]
            );

            $row = $this->db->fetchObject($res);
            if (is_object($row)) {
                $this->exists = true;

                $this->default_view = (int) $row->default_view;
                $this->anonymized = (bool) $row->anonymized;
                $this->statistics_enabled = (bool) $row->statistics_enabled;
                $this->post_activation_enabled = (bool) $row->post_activation;
                $this->container_enforces_noti = (bool) $row->container_enforces_noti;
                $this->member_may_disable_noti = (bool) $row->member_may_disable_noti;
                $this->preset_subject = (bool) $row->preset_subject;
                $this->add_re_subject = (bool) $row->add_re_subject;
                $this->interested_events = (int) $row->interested_events;

                $this->notification_type =
                    NotificationType::tryFrom($row->notification_type ?? NotificationType::DEFAULT->value) ??
                    NotificationType::DEFAULT;
                $this->mark_mod_posts = (bool) $row->mark_mod_posts;
                $this->is_thread_rating_enabled = (bool) $row->thread_rating;
                $this->file_upload_allowed = (bool) $row->file_upload_allowed;
                if (is_numeric($row->lp_req_num_postings)) {
                    $this->lp_req_num_postings = (int) $row->lp_req_num_postings;
                }
            }
        }
    }

    public function insert(): void
    {
        if ($this->obj_id && !$this->exists) {
            $this->db->insert(
                'frm_settings',
                [
                    'obj_id' => ['integer', $this->obj_id],
                    'default_view' => ['integer', $this->default_view],
                    'anonymized' => ['integer', (int) $this->anonymized],
                    'statistics_enabled' => ['integer', (int) $this->statistics_enabled],
                    'post_activation' => ['integer', (int) $this->post_activation_enabled],
                    'container_enforces_noti' => ['integer', (int) $this->container_enforces_noti],
                    'member_may_disable_noti' => ['integer', (int) $this->member_may_disable_noti],
                    'preset_subject' => ['integer', (int) $this->preset_subject],
                    'add_re_subject' => ['integer', (int) $this->add_re_subject],
                    'notification_type' => ['text', $this->notification_type->value],
                    'mark_mod_posts' => ['integer', (int) $this->mark_mod_posts],
                    'thread_rating' => ['integer', (int) $this->is_thread_rating_enabled],
                    'file_upload_allowed' => ['integer', (int) $this->file_upload_allowed],
                    'lp_req_num_postings' => ['integer', $this->lp_req_num_postings],
                    'interested_events' => ['integer', $this->interested_events]
                ]
            );
            $this->exists = true;
        }
    }

    public function update(): void
    {
        if ($this->obj_id !== 0) {
            if (!$this->exists) {
                $this->insert();
                return;
            }

            $this->db->update(
                'frm_settings',
                [
                    'default_view' => ['integer', $this->default_view],
                    'anonymized' => ['integer', (int) $this->anonymized],
                    'statistics_enabled' => ['integer', (int) $this->statistics_enabled],
                    'post_activation' => ['integer', (int) $this->post_activation_enabled],
                    'container_enforces_noti' => ['integer', (int) $this->container_enforces_noti],
                    'member_may_disable_noti' => ['integer', (int) $this->member_may_disable_noti],
                    'preset_subject' => ['integer', (int) $this->preset_subject],
                    'add_re_subject' => ['integer', (int) $this->add_re_subject],
                    'notification_type' => ['text', $this->notification_type->value],
                    'mark_mod_posts' => ['integer', (int) $this->mark_mod_posts],
                    'thread_rating' => ['integer', (int) $this->is_thread_rating_enabled],
                    'file_upload_allowed' => ['integer', (int) $this->file_upload_allowed],
                    'lp_req_num_postings' => ['integer', (int) $this->lp_req_num_postings],
                    'interested_events' => ['integer', $this->interested_events]
                ],
                [
                    'obj_id' => ['integer', $this->obj_id]
                ]
            );
        }
    }

    public function copy(int $a_new_obj_id): bool
    {
        if ($a_new_obj_id !== 0) {
            $this->contentStyle()->cloneTo($a_new_obj_id);

            $this->db->update(
                'frm_settings',
                [
                    'default_view' => ['integer', $this->default_view],
                    'anonymized' => ['integer', (int) $this->anonymized],
                    'statistics_enabled' => ['integer', (int) $this->statistics_enabled],
                    'post_activation' => ['integer', (int) $this->post_activation_enabled],
                    'container_enforces_noti' => ['integer', (int) $this->container_enforces_noti],
                    'member_may_disable_noti' => ['integer', (int) $this->member_may_disable_noti],
                    'preset_subject' => ['integer', (int) $this->preset_subject],
                    'add_re_subject' => ['integer', (int) $this->add_re_subject],
                    'notification_type' => ['text', $this->notification_type->value],
                    'mark_mod_posts' => ['integer', (int) $this->mark_mod_posts],
                    'thread_rating' => ['integer', (int) $this->is_thread_rating_enabled],
                    'file_upload_allowed' => ['integer', (int) $this->file_upload_allowed],
                    'lp_req_num_postings' => ['integer', $this->lp_req_num_postings],
                    'interested_events' => ['integer', $this->interested_events]
                ],
                [
                    'obj_id' => ['integer', $a_new_obj_id]
                ]
            );

            return true;
        }

        return false;
    }

    public function isIsThreadRatingEnabled(): bool
    {
        return $this->is_thread_rating_enabled;
    }

    public function setIsThreadRatingEnabled(bool $is_thread_rating_enabled): void
    {
        $this->is_thread_rating_enabled = $is_thread_rating_enabled;
    }

    public function setDefaultView(int $a_default_view): void
    {
        $this->default_view = $a_default_view;
    }

    public function getDefaultView(): int
    {
        return $this->default_view;
    }

    public function setStatisticsStatus(bool $a_statistic_status): void
    {
        $this->statistics_enabled = $a_statistic_status;
    }

    public function isStatisticEnabled(): bool
    {
        return $this->statistics_enabled;
    }

    public function setAnonymisation(bool $a_anonymized): void
    {
        $this->anonymized = $a_anonymized;
    }

    public function isAnonymized(): bool
    {
        return $this->anonymized;
    }

    public static function _isAnonymized(int $a_obj_id): bool
    {
        global $DIC;
        $ilDB = $DIC->database();

        $result = $ilDB->queryF(
            'SELECT anonymized FROM frm_settings WHERE obj_id = %s',
            ['integer'],
            [$a_obj_id]
        );

        while ($record = $ilDB->fetchAssoc($result)) {
            return (bool) $record['anonymized'];
        }

        return false;
    }

    public function setPostActivation(bool $a_post_activation): void
    {
        $this->post_activation_enabled = $a_post_activation;
    }

    public function isPostActivationEnabled(): bool
    {
        return $this->post_activation_enabled;
    }

    public function setObjId(int $a_obj_id): void
    {
        $this->obj_id = $a_obj_id;
        $this->read();
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setContainerEnforcingForumNotification(bool $container_enforces): void
    {
        $this->container_enforces_noti = $container_enforces;
    }

    public function isContainerEnforcingForumNotification(): bool
    {
        return $this->container_enforces_noti;
    }

    public function setMemberMayDeactivateForumNotification(bool $member_may_disable): void
    {
        $this->member_may_disable_noti = $member_may_disable;
    }

    public function isMemberMayDeactivateForumNotification(): bool
    {
        return $this->member_may_disable_noti;
    }

    public static function _isContainerEnforcingForumNotification(int $a_obj_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT container_enforces_noti FROM frm_settings WHERE obj_id = %s',
            ['integer'],
            [$a_obj_id]
        );
        if ($record = $ilDB->fetchAssoc($res)) {
            return (bool) $record['container_enforces_noti'];
        }

        return false;
    }

    public static function _isMemberMayDeactivateForumNotification(int $a_obj_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT member_may_disable_noti FROM frm_settings WHERE obj_id = %s',
            ['integer'],
            [$a_obj_id]
        );
        while ($record = $ilDB->fetchAssoc($res)) {
            return (bool) $record['member_may_disable_noti'];
        }

        return false;
    }

    public function setPresetSubject(bool $a_preset_subject): void
    {
        $this->preset_subject = $a_preset_subject;
    }

    public function isSubjectPreset(): bool
    {
        return $this->preset_subject;
    }

    public function setAddReSubject(bool $a_add_re_subject): void
    {
        $this->add_re_subject = $a_add_re_subject;
    }

    public function isSubjectAdded(): bool
    {
        return $this->add_re_subject;
    }

    public function setNotificationType(NotificationType $a_notification_type): void
    {
        $this->notification_type = $a_notification_type;
    }

    public function getNotificationType(): NotificationType
    {
        return $this->notification_type;
    }

    public function getSubjectSetting(): string
    {
        if (!$this->isSubjectPreset() && !$this->isSubjectAdded()) {
            return "empty_subject";
        }

        if ($this->isSubjectPreset()) {
            return "preset_subject";
        }

        if ($this->isSubjectAdded()) {
            return "add_re_to_subject";
        }

        return "preset_subject";
    }

    public function setSubjectSetting($a_subject_setting): void
    {
        if ($a_subject_setting === 'empty_subject') {
            $this->setPresetSubject(false);
            $this->setAddReSubject(false);
        } elseif ($a_subject_setting === 'preset_subject') {
            $this->setPresetSubject(true);
            $this->setAddReSubject(false);
        } elseif ($a_subject_setting === 'add_re_to_subject') {
            $this->setPresetSubject(false);
            $this->setAddReSubject(true);
        }
    }

    public function setMarkModeratorPosts(bool $a_mod_post): void
    {
        $this->mark_mod_posts = $a_mod_post;
    }

    public function getMarkModeratorPosts(): bool
    {
        return $this->mark_mod_posts;
    }

    public function getMemberMayDeactivateForumNotification(): bool
    {
        return $this->member_may_disable_noti;
    }

    public function getContainerEnforcesForumNotification(): bool
    {
        return $this->container_enforces_noti;
    }

    public function setFileUploadAllowed(bool $allowed): void
    {
        $this->file_upload_allowed = $allowed;
    }

    public function getFileUploadAllowed(): bool
    {
        return $this->file_upload_allowed;
    }

    public function isFileUploadAllowed(): bool
    {
        if (self::isFileUploadGloballyAllowed()) {
            return true;
        }

        return $this->getFileUploadAllowed();
    }

    public static function isFileUploadGloballyAllowed(): bool
    {
        global $DIC;

        return (
            (int) $DIC->settings()->get('file_upload_allowed_fora') === self::FILE_UPLOAD_GLOBALLY_ALLOWED
        );
    }

    public static function isSendAttachmentsByMailEnabled(): bool
    {
        global $DIC;

        return (bool) $DIC->settings()->get('send_attachments_by_mail');
    }

    public function getInterestedEvents(): int
    {
        return $this->interested_events;
    }

    public function setInterestedEvents(int $interested_events): void
    {
        $this->interested_events = $interested_events;
    }

    public function getLpReqNumPostings(): ?int
    {
        return $this->lp_req_num_postings;
    }

    public function setLpReqNumPostings(?int $lp_req_num_postings): void
    {
        $this->lp_req_num_postings = $lp_req_num_postings;
    }
}
