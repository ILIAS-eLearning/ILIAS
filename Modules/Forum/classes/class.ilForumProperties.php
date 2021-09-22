<?php declare(strict_types=1);
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Michael Jansen <mjansen@databay.de>
 * @version $Id: $
 * @ingroup ModulesForum
 */
class ilForumProperties
{
    const VIEW_TREE = 1;
    const VIEW_DATE = 2;
    const VIEW_DATE_ASC = 2;
    const VIEW_DATE_DESC = 3;

    const FORUM_OVERVIEW_WITH_NEW_POSTS = 0;
    const FORUM_OVERVIEW_NO_NEW_POSTS = 1;

    const FILE_UPLOAD_GLOBALLY_ALLOWED = 0;
    const FILE_UPLOAD_INDIVIDUAL = 1;

    private int $obj_id;

    /**
     * Default view ( 1 => 'order by answers', 2 => 'order by date ascending', 3 => 'order by date descending')
     */
    private int $default_view = self::VIEW_DATE_ASC;

    private int $anonymized = 0;
    private int $statistics_enabled = 0;
    private int $post_activation_enabled = 0;

    /**
     * Global notification-type setting (CRS/GRP)
     * possible values: 'all_users', 'per_user', null (default)
     */
    private string $notification_type = 'default';

    /**
     * Activation of (CRS/GRP) forum notification by mod/admin
     */
    private int $admin_force_noti = 0;
    /**
     * Activation of allowing members to deactivate (CRS/GRP)forum notification
     */
    private int $user_toggle_noti = 0;

    /**
     * Preset subject on reply.
     * If deactivated, user is forced to enter a new subject
     */
    private int $preset_subject = 1;

    /**
     * Preset notification events for forced notification
     */
    private int $interested_events = 0;

    /**
     * Add 'Re: ' to subject on reply
     */
    private int $add_re_subject = 0;
    private int $mark_mod_posts = 0;

    /**
     * sorting type for threads (manual sorting)
     * 0 = default
     * 1 = manual
     */
    private int $thread_sorting = 0;
    private int $is_thread_rating_enabled = 0;

    /**
     * DB Object
     */
    private $db = null;

    private int $file_upload_allowed = 0;

    /**
     * @var ilForumProperties[]
     */
    private static array $instances = array();
    private bool $exists = false;
    private ?int $lp_req_num_postings = null;

    protected function __construct($a_obj_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->obj_id = $a_obj_id;
        $this->read();
    }

    private function __clone()
    {
    }

    public static function getInstance(int $a_obj_id = 0) : self
    {
        if (!isset(self::$instances[$a_obj_id]) || !(self::$instances[$a_obj_id] instanceof self)) {
            self::$instances[$a_obj_id] = new ilForumProperties($a_obj_id);
        }

        return self::$instances[$a_obj_id];
    }

    private function read() : void
    {
        if ($this->obj_id) {
            $res = $this->db->queryf(
                '
				SELECT * FROM frm_settings
				WHERE obj_id = %s',
                array('integer'),
                array($this->obj_id)
            );

            $row = $this->db->fetchObject($res);

            if (is_object($row)) {
                $this->default_view = (int) $row->default_view;
                $this->anonymized = (int) $row->anonymized;
                $this->statistics_enabled = (int) $row->statistics_enabled;
                $this->post_activation_enabled = (int) $row->post_activation;
                $this->admin_force_noti = (int) $row->admin_force_noti;
                $this->user_toggle_noti = (int) $row->user_toggle_noti;
                $this->preset_subject = (int) $row->preset_subject;
                $this->add_re_subject = (int) $row->add_re_subject;
                $this->interested_events = (int) $row->interested_events;

                $this->notification_type = $row->notification_type ?? 'default';
                $this->mark_mod_posts = (int) $row->mark_mod_posts;
                $this->thread_sorting = (int) $row->thread_sorting;
                $this->setIsThreadRatingEnabled((bool) $row->thread_rating);
                $this->file_upload_allowed = (int) $row->file_upload_allowed;
                if (is_numeric((int) $row->lp_req_num_postings)) {
                    $this->lp_req_num_postings = (int) $row->lp_req_num_postings;
                }

                $this->exists = true;
            }
        }
    }

    public function insert() : void
    {
        if ($this->obj_id && !$this->exists) {
            $this->db->insert(
                'frm_settings',
                array(
                    'obj_id' => array('integer', (int) $this->obj_id),
                    'default_view' => array('integer', (int) $this->default_view),
                    'anonymized' => array('integer', (int) $this->anonymized),
                    'statistics_enabled' => array('integer', (int) $this->statistics_enabled),
                    'post_activation' => array('integer', (int) $this->post_activation_enabled),
                    'admin_force_noti' => array('integer', (int) $this->admin_force_noti),
                    'user_toggle_noti' => array('integer', (int) $this->user_toggle_noti),
                    'preset_subject' => array('integer', (int) $this->preset_subject),
                    'add_re_subject' => array('integer', (int) $this->add_re_subject),
                    'notification_type' => array('text', $this->notification_type),
                    'mark_mod_posts' => array('integer', (int) $this->mark_mod_posts),
                    'thread_sorting' => array('integer', (int) $this->thread_sorting),
                    'thread_rating' => array('integer', (int) $this->is_thread_rating_enabled),
                    'file_upload_allowed' => array('integer', (int) $this->file_upload_allowed),
                    'lp_req_num_postings' => ['integer', (int) $this->lp_req_num_postings],
                    'interested_events' => array('integer', (int) $this->interested_events)
                )
            );

            $this->exists = true;
        }
    }

    public function update() : void
    {
        if ($this->obj_id) {
            if (!$this->exists) {
                $this->insert();
                return;
            }

            $this->db->update(
                'frm_settings',
                array(
                    'default_view' => array('integer', $this->default_view),
                    'anonymized' => array('integer', $this->anonymized),
                    'statistics_enabled' => array('integer', $this->statistics_enabled),
                    'post_activation' => array('integer', $this->post_activation_enabled),
                    'admin_force_noti' => array('integer', $this->admin_force_noti),
                    'user_toggle_noti' => array('integer', $this->user_toggle_noti),
                    'preset_subject' => array('integer', $this->preset_subject),
                    'add_re_subject' => array('integer', $this->add_re_subject),
                    'notification_type' => array('text', $this->notification_type),
                    'mark_mod_posts' => array('integer', $this->mark_mod_posts),
                    'thread_sorting' => array('integer', $this->thread_sorting),
                    'lp_req_num_postings' => array('integer', $this->lp_req_num_postings),
                    'thread_rating' => array('integer', $this->isIsThreadRatingEnabled()),
                    'file_upload_allowed' => array('integer', $this->file_upload_allowed),
                    'interested_events' => array('integer', $this->interested_events)
                ),
                array(
                    'obj_id' => array('integer', $this->obj_id)
                )
            );
        }
    }

    public function copy($a_new_obj_id) : bool
    {
        if ($a_new_obj_id) {
            $this->db->update(
                'frm_settings',
                array(
                    'default_view' => array('integer', (int) $this->default_view),
                    'anonymized' => array('integer', (int) $this->anonymized),
                    'statistics_enabled' => array('integer', (int) $this->statistics_enabled),
                    'post_activation' => array('integer', (int) $this->post_activation_enabled),
                    'admin_force_noti' => array('integer', (int) $this->admin_force_noti),
                    'user_toggle_noti' => array('integer', (int) $this->user_toggle_noti),
                    'preset_subject' => array('integer', (int) $this->preset_subject),
                    'add_re_subject' => array('integer', (int) $this->add_re_subject),
                    'notification_type' => array('text', $this->notification_type),
                    'mark_mod_posts' => array('integer', (int) $this->mark_mod_posts),
                    'lp_req_num_postings' => array('integer', (int) $this->lp_req_num_postings),
                    'thread_sorting' => array('integer', (int) $this->thread_sorting),
                    'thread_rating' => array('integer', (int) $this->isIsThreadRatingEnabled()),
                    'file_upload_allowed' => array('integer', (int) $this->file_upload_allowed),
                    'interested_events' => array('integer', (int) $this->interested_events)
                ),
                array(
                    'obj_id' => array('integer', $a_new_obj_id)
                )
            );
            return true;
        }

        return false;
    }

    public function isIsThreadRatingEnabled() : bool
    {
        return (bool) $this->is_thread_rating_enabled == 1;
    }

    public function setIsThreadRatingEnabled($is_thread_rating_enabled) : void
    {
        $this->is_thread_rating_enabled = (int) $is_thread_rating_enabled;
    }

    public function setDefaultView($a_default_view) : void
    {
        $this->default_view = $a_default_view;
    }

    public function getDefaultView() : int
    {
        return (int) $this->default_view;
    }

    public function setStatisticsStatus($a_statistic_status) : void
    {
        $this->statistics_enabled = $a_statistic_status;
    }

    public function isStatisticEnabled() : bool
    {
        return (bool) $this->statistics_enabled;
    }

    public function setAnonymisation($a_anonymized) : void
    {
        $this->anonymized = $a_anonymized;
    }

    public function isAnonymized() : bool
    {
        return (bool) $this->anonymized;
    }

    public static function _isAnonymized($a_obj_id) : bool
    {
        global $DIC;
        $ilDB = $DIC->database();

        $result = $ilDB->queryf(
            "SELECT anonymized FROM frm_settings WHERE obj_id = %s",
            array('integer'),
            array($a_obj_id)
        );

        while ($record = $ilDB->fetchAssoc($result)) {
            return (bool) $record['anonymized'] == 1;
        }

        return false;
    }

    public function setPostActivation($a_post_activation) : void
    {
        $this->post_activation_enabled = $a_post_activation;
    }

    public function isPostActivationEnabled() : bool
    {
        return (bool) $this->post_activation_enabled;
    }

    public function setObjId($a_obj_id = 0) : void
    {
        $this->obj_id = (int) $a_obj_id;
        $this->read();
    }

    public function getObjId() : int
    {
        return (int) $this->obj_id;
    }

    public function setAdminForceNoti($a_admin_force) : void
    {
        $this->admin_force_noti = $a_admin_force;
    }

    public function isAdminForceNoti() : int
    {
        return $this->admin_force_noti;
    }

    public function setUserToggleNoti($a_user_toggle) : void
    {
        $this->user_toggle_noti = $a_user_toggle;
    }

    public function isUserToggleNoti() : bool
    {
        return (bool) $this->user_toggle_noti;
    }

    public static function _isAdminForceNoti($a_obj_id) : int
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            "SELECT admin_force_noti FROM frm_settings WHERE obj_id = %s",
            array('integer'),
            array($a_obj_id)
        );
        if ($record = $ilDB->fetchAssoc($res)) {
            return (int) $record['admin_force_noti'];
        }

        return 0;
    }

    public static function _isUserToggleNoti($a_obj_id) : int
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            "SELECT user_toggle_noti FROM frm_settings WHERE obj_id = %s",
            array('integer'),
            array($a_obj_id)
        );
        while ($record = $ilDB->fetchAssoc($res)) {
            return (int) $record['user_toggle_noti'];
        }
        return 0;
    }

    public function setPresetSubject($a_preset_subject) : void
    {
        $this->preset_subject = $a_preset_subject;
    }

    public function getPresetSubject() : int
    {
        return $this->preset_subject;
    }

    public function setAddReSubject($a_add_re_subject) : void
    {
        $this->add_re_subject = $a_add_re_subject;
    }

    public function getAddReSubject() : int
    {
        return $this->add_re_subject;
    }

    public function setNotificationType($a_notification_type) : void
    {
        if ($a_notification_type == null) {
            $this->notification_type = 'default';
        } else {
            $this->notification_type = $a_notification_type;
        }
    }

    public function getNotificationType() : string
    {
        return $this->notification_type;
    }

    public function getSubjectSetting() : string
    {
        if ($this->getPresetSubject() == 0
            && $this->getAddReSubject() == 0) {
            return "empty_subject";
        } else {
            if ($this->getPresetSubject() == 1) {
                return "preset_subject";
            } else {
                if ($this->getAddReSubject() == 1) {
                    return "add_re_to_subject";
                } else {
                    return "preset_subject";
                }
            }
        }
    }

    public function setSubjectSetting($a_subject_setting) : void
    {
        if ($a_subject_setting == 'empty_subject') {
            $this->setPresetSubject(0);
            $this->setAddReSubject(0);
        } else {
            if ($a_subject_setting == 'preset_subject') {
                $this->setPresetSubject(1);
                $this->setAddReSubject(0);
            } else {
                if ($a_subject_setting == 'add_re_to_subject') {
                    $this->setPresetSubject(0);
                    $this->setAddReSubject(1);
                }
            }
        }
    }

    public function setMarkModeratorPosts($a_mod_post) : void
    {
        $this->mark_mod_posts = $a_mod_post;
    }

    public function getMarkModeratorPosts() : int
    {
        return $this->mark_mod_posts;
    }

    public function setThreadSorting($a_thread_sorting) : void
    {
        $this->thread_sorting = $a_thread_sorting;
    }

    public function getThreadSorting() : int
    {
        return $this->thread_sorting;
    }

    public function getUserToggleNoti() : int
    {
        return $this->user_toggle_noti;
    }

    public function getAdminForceNoti() : int
    {
        return $this->admin_force_noti;
    }

    public function setFileUploadAllowed(bool $allowed) : void
    {
        $this->file_upload_allowed = (int) $allowed;
    }

    public function getFileUploadAllowed() : int
    {
        return (int) $this->file_upload_allowed;
    }

    public function isFileUploadAllowed() : bool
    {
        if (self::isFileUploadGloballyAllowed()) {
            return true;
        }

        if ((bool) $this->getFileUploadAllowed()) {
            return true;
        }

        return false;
    }

    public static function isFileUploadGloballyAllowed() : bool
    {
        global $DIC;
        return $DIC->settings()->get(
                'file_upload_allowed_fora',
                self::FILE_UPLOAD_GLOBALLY_ALLOWED
            ) == self::FILE_UPLOAD_GLOBALLY_ALLOWED;
    }

    public static function isSendAttachmentsByMailEnabled() : bool
    {
        global $DIC;
        return $DIC->settings()->get('send_attachments_by_mail') == true;
    }

    public function getInterestedEvents() : int
    {
        return $this->interested_events;
    }

    public function setInterestedEvents(int $interested_events) : void
    {
        $this->interested_events = $interested_events;
    }

    public function getLpReqNumPostings() : ?int
    {
        return $this->lp_req_num_postings;
    }

    public function setLpReqNumPostings(?int $lp_req_num_postings) : void
    {
        $this->lp_req_num_postings = $lp_req_num_postings;
    }
}
