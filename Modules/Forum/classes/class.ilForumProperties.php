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
 * @author  Michael Jansen <mjansen@databay.de>
 * @ingroup ModulesForum
 */
class ilForumProperties
{
    public const VIEW_TREE = 1;
    public const VIEW_DATE = 2;
    public const VIEW_DATE_ASC = 2;
    public const VIEW_DATE_DESC = 3;
    public const FORUM_OVERVIEW_WITH_NEW_POSTS = 0;
    public const FORUM_OVERVIEW_NO_NEW_POSTS = 1;
    public const FILE_UPLOAD_GLOBALLY_ALLOWED = 0;
    public const FILE_UPLOAD_INDIVIDUAL = 1;
    public const THREAD_SORTING_DEFAULT = 0;
    public const THREAD_SORTING_MANUAL = 1;

    /** @var array<int, ilForumProperties> */
    private static array $instances = [];

    private ilDBInterface $db;
    private int $obj_id;
    private int $default_view = self::VIEW_DATE_ASC;
    private bool $anonymized = false;
    private bool $statistics_enabled = false;
    private bool $post_activation_enabled = false;
    /**
     * Global notification-type setting (CRS/GRP)
     * possible values: 'all_users', 'per_user', null (default)
     */
    private string $notification_type = 'default';
    /** Activation of (CRS/GRP) forum notification by mod/admin */
    private bool $admin_force_noti = false;
    /** Activation of allowing members to deactivate (CRS/GRP)forum notification */
    private bool $user_toggle_noti = false;
    /** If deactivated, user is forced to enter a new subject on repliees */
    private bool $preset_subject = true;
    /** Preset notification events for forced notification */
    private int $interested_events = ilForumNotificationEvents::DEACTIVATED;
    /** Add 'Re: ' to subject on reply */
    private bool $add_re_subject = false;
    private bool $mark_mod_posts = false;
    private int $thread_sorting = self::THREAD_SORTING_DEFAULT;
    private bool $is_thread_rating_enabled = false;
    private bool $file_upload_allowed = false;
    protected int $styleId = 0;
    private bool $exists = false;
    private ?int $lp_req_num_postings = null;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_service;

    protected function __construct(int $a_obj_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->obj_id = $a_obj_id;
        $this->read();
        $this->content_style_service = $DIC
            ->contentStyle()
            ->domain()
            ->styleForObjId($a_obj_id);
    }

    private function __clone()
    {
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
        if ($this->obj_id) {
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
                $this->admin_force_noti = (bool) $row->admin_force_noti;
                $this->user_toggle_noti = (bool) $row->user_toggle_noti;
                $this->preset_subject = (bool) $row->preset_subject;
                $this->add_re_subject = (bool) $row->add_re_subject;
                $this->interested_events = (int) $row->interested_events;

                $this->notification_type = $row->notification_type ?? 'default';
                $this->mark_mod_posts = (bool) $row->mark_mod_posts;
                $this->thread_sorting = (int) $row->thread_sorting;
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
                    'admin_force_noti' => ['integer', (int) $this->admin_force_noti],
                    'user_toggle_noti' => ['integer', (int) $this->user_toggle_noti],
                    'preset_subject' => ['integer', (int) $this->preset_subject],
                    'add_re_subject' => ['integer', (int) $this->add_re_subject],
                    'notification_type' => ['text', $this->notification_type],
                    'mark_mod_posts' => ['integer', (int) $this->mark_mod_posts],
                    'thread_sorting' => ['integer', $this->thread_sorting],
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
        if ($this->obj_id) {
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
                    'admin_force_noti' => ['integer', (int) $this->admin_force_noti],
                    'user_toggle_noti' => ['integer', (int) $this->user_toggle_noti],
                    'preset_subject' => ['integer', (int) $this->preset_subject],
                    'add_re_subject' => ['integer', (int) $this->add_re_subject],
                    'notification_type' => ['text', $this->notification_type],
                    'mark_mod_posts' => ['integer', (int) $this->mark_mod_posts],
                    'thread_sorting' => ['integer', $this->thread_sorting],
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
        if ($a_new_obj_id) {
            $this->content_style_service->cloneTo($a_new_obj_id);

            $this->db->update(
                'frm_settings',
                [
                    'default_view' => ['integer', $this->default_view],
                    'anonymized' => ['integer', (int) $this->anonymized],
                    'statistics_enabled' => ['integer', (int) $this->statistics_enabled],
                    'post_activation' => ['integer', (int) $this->post_activation_enabled],
                    'admin_force_noti' => ['integer', (int) $this->admin_force_noti],
                    'user_toggle_noti' => ['integer', (int) $this->user_toggle_noti],
                    'preset_subject' => ['integer', (int) $this->preset_subject],
                    'add_re_subject' => ['integer', (int) $this->add_re_subject],
                    'notification_type' => ['text', $this->notification_type],
                    'mark_mod_posts' => ['integer', (int) $this->mark_mod_posts],
                    'thread_sorting' => ['integer', $this->thread_sorting],
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

    public function setDefaultView($a_default_view): void
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

    public function setAdminForceNoti(bool $a_admin_force): void
    {
        $this->admin_force_noti = $a_admin_force;
    }

    public function isAdminForceNoti(): bool
    {
        return $this->admin_force_noti;
    }

    public function setUserToggleNoti(bool $a_user_toggle): void
    {
        $this->user_toggle_noti = $a_user_toggle;
    }

    public function isUserToggleNoti(): bool
    {
        return $this->user_toggle_noti;
    }

    public static function _isAdminForceNoti(int $a_obj_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT admin_force_noti FROM frm_settings WHERE obj_id = %s',
            ['integer'],
            [$a_obj_id]
        );
        if ($record = $ilDB->fetchAssoc($res)) {
            return (bool) $record['admin_force_noti'];
        }

        return false;
    }

    public static function _isUserToggleNoti(int $a_obj_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            'SELECT user_toggle_noti FROM frm_settings WHERE obj_id = %s',
            ['integer'],
            [$a_obj_id]
        );
        while ($record = $ilDB->fetchAssoc($res)) {
            return (bool) $record['user_toggle_noti'];
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

    public function setNotificationType(?string $a_notification_type): void
    {
        if ($a_notification_type === null) {
            $this->notification_type = 'default';
        } else {
            $this->notification_type = $a_notification_type;
        }
    }

    public function getNotificationType(): string
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

    public function setThreadSorting(int $a_thread_sorting): void
    {
        $this->thread_sorting = $a_thread_sorting;
    }

    public function getThreadSorting(): int
    {
        return $this->thread_sorting;
    }

    public function getUserToggleNoti(): bool
    {
        return $this->user_toggle_noti;
    }

    public function getAdminForceNoti(): bool
    {
        return $this->admin_force_noti;
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

        if ($this->getFileUploadAllowed()) {
            return true;
        }

        return false;
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
