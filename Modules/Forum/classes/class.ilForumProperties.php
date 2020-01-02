<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* @author Michael Jansen <mjansen@databay.de>
* @version $Id: $
*
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

    const FILE_UPLOAD_GLOBALLY_ALLOWED  = 0;
    const FILE_UPLOAD_INDIVIDUAL        = 1;

    /**
     * Object id of current forum
     * @access	private
     */
    private $obj_id;
    
    /**
     * Default view ( 1 => 'order by answers', 2 => 'order by date ascending', 3 => 'order by date descending')
     * @access	private
     */
    private $default_view = self::VIEW_TREE;
    
    /**
     * Defines if a forum is anonymized or not
     * @access	private
     */
    private $anonymized = 0; //false;
    
    /**
     * Defines if a forum can show ranking statistics
     * @access private
     */
    private $statistics_enabled = 0; //false;
    
    /**
     * Activation of new posts
     * @access	private
     */
    private $post_activation_enabled = 0; //false;

    /**
     * Global notification-type setting (CRS/GRP)
     * possible values: 'all_users', 'per_user', null (default)
     *
     * @access	private
     *
     */

    private $notification_type = null;

    /**
     * Activation of (CRS/GRP) forum notification by mod/admin
     * @access	private
     */
    private $admin_force_noti = false;
    /**
     * Activation of allowing members to deactivate (CRS/GRP)forum notification
     * @access	private
     */
    private $user_toggle_noti = false;

    /**
     * Preset subject on reply.
     * If deactivated, user is forced to enter a new subject
     *
     * @access	private
     */
    private $preset_subject = 1;

    /**
     * Add 'Re: ' to subject on reply
     *
     * @access	private
     */
    private $add_re_subject = 0;

    private $mark_mod_posts = 0;

    /**
     * sorting type for threads (manual sorting)
     * 0 = default
     * 1 = manual
     *
     * @access private
     */
    private $thread_sorting = 0;

    /**
     * @var bool
     */
    private $is_thread_rating_enabled = false;
    
    /**
     * DB Object
     * @access	private
     */
    private $db = null;

    /**
     * @var int
     */
    private $file_upload_allowed = 0;

    /**
     * @var ilForumProperties[]
     */
    private static $instances = array();
    
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

    /**
     * @param int $a_obj_id
     * @return ilForumProperties
     */
    public static function getInstance($a_obj_id = 0)
    {
        if (!self::$instances[$a_obj_id]) {
            self::$instances[$a_obj_id] = new ilForumProperties($a_obj_id);
        }
        
        return self::$instances[$a_obj_id];
    }
    
    private function read()
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
                $this->default_view = $row->default_view;
                $this->anonymized = $row->anonymized ;// == 1 ? true : false;
                $this->statistics_enabled = $row->statistics_enabled ;// == 1 ? true : false;
                $this->post_activation_enabled = $row->post_activation ;// == 1 ? true : false;
                $this->admin_force_noti = $row->admin_force_noti == 1 ? true : false;
                $this->user_toggle_noti = $row->user_toggle_noti == 1 ? true : false;
                $this->preset_subject = $row->preset_subject;
                $this->add_re_subject = $row->add_re_subject;

                $this->notification_type = $row->notification_type == null ? 'default': $row->notification_type;
                $this->mark_mod_posts = $row->mark_mod_posts == 1 ? true : false;
                $this->thread_sorting = $row->thread_sorting == 1? true : false;
                $this->setIsThreadRatingEnabled((bool) $row->thread_rating);
                $this->file_upload_allowed = $row->file_upload_allowed == 1 ? true : false;

                return true;
            }
            
            return false;
        }
        
        return false;
    }
    
    public function insert()
    {
        if ($this->obj_id) {
            $this->db->insert(
                'frm_settings',
                array(
                    'obj_id'              => array('integer', $this->obj_id),
                    'default_view'        => array('integer', $this->default_view),
                    'anonymized'          => array('integer', $this->anonymized),
                    'statistics_enabled'  => array('integer', $this->statistics_enabled),
                    'post_activation'     => array('integer', $this->post_activation_enabled),
                    'admin_force_noti'    => array('integer', $this->admin_force_noti),
                    'user_toggle_noti'    => array('integer', $this->user_toggle_noti),
                    'preset_subject'      => array('integer', $this->preset_subject),
                    'add_re_subject'      => array('integer', $this->add_re_subject),
                    'notification_type'   => array('text', $this->notification_type),
                    'mark_mod_posts'      => array('integer', $this->mark_mod_posts),
                    'thread_sorting'      => array('integer', $this->thread_sorting),
                    'thread_rating'       => array('integer', $this->isIsThreadRatingEnabled()),
                    'file_upload_allowed' => array('integer', $this->file_upload_allowed)
                )
            );

            return true;
        }
        
        return false;
    }
    
    public function update()
    {
        if ($this->obj_id) {
            $this->db->update(
                'frm_settings',
                array(
                    'default_view'        => array('integer', $this->default_view),
                    'anonymized'          => array('integer', $this->anonymized),
                    'statistics_enabled'  => array('integer', $this->statistics_enabled),
                    'post_activation'     => array('integer', $this->post_activation_enabled),
                    'admin_force_noti'    => array('integer', $this->admin_force_noti),
                    'user_toggle_noti'    => array('integer', $this->user_toggle_noti),
                    'preset_subject'      => array('integer', $this->preset_subject),
                    'add_re_subject'      => array('integer', $this->add_re_subject),
                    'notification_type'   => array('text', $this->notification_type),
                    'mark_mod_posts'      => array('integer', $this->mark_mod_posts),
                    'thread_sorting'      => array('integer', $this->thread_sorting),
                    'thread_rating'       => array('integer', $this->isIsThreadRatingEnabled()),
                    'file_upload_allowed' => array('integer', $this->file_upload_allowed)
                ),
                array(
                    'obj_id' => array('integer', $this->obj_id)
                )
            );
            return true;
        }
        return false;
    }
    
    public function copy($a_new_obj_id)
    {
        if ($a_new_obj_id) {
            $this->db->update(
                'frm_settings',
                array(
                    'default_view'        => array('integer', $this->default_view),
                    'anonymized'          => array('integer', $this->anonymized),
                    'statistics_enabled'  => array('integer', $this->statistics_enabled),
                    'post_activation'     => array('integer', $this->post_activation_enabled),
                    'admin_force_noti'    => array('integer', $this->admin_force_noti),
                    'user_toggle_noti'    => array('integer', $this->user_toggle_noti),
                    'preset_subject'      => array('integer', $this->preset_subject),
                    'add_re_subject'      => array('integer', $this->add_re_subject),
                    'notification_type'   => array('text', $this->notification_type),
                    'mark_mod_posts'      => array('integer', $this->mark_mod_posts),
                    'thread_sorting'      => array('integer', $this->thread_sorting),
                    'thread_rating'       => array('integer', $this->isIsThreadRatingEnabled()),
                    'file_upload_allowed' => array('integer', $this->file_upload_allowed)
                ),
                array(
                    'obj_id' => array('integer', $a_new_obj_id)
                )
            );
            return true;
        }
        
        return false;
    }

    /**
     * @return boolean
     */
    public function isIsThreadRatingEnabled()
    {
        return (bool) $this->is_thread_rating_enabled;
    }

    /**
     * @param boolean $is_thread_rating_enabled
     */
    public function setIsThreadRatingEnabled($is_thread_rating_enabled)
    {
        $this->is_thread_rating_enabled = (bool) $is_thread_rating_enabled;
    }
    
    public function setDefaultView($a_default_view)
    {
        $this->default_view = $a_default_view;
    }
    public function getDefaultView()
    {
        return $this->default_view;
    }
    public function setStatisticsStatus($a_statistic_status)
    {
        $this->statistics_enabled = $a_statistic_status;
    }
    public function isStatisticEnabled()
    {
        return $this->statistics_enabled;
    }
    public function setAnonymisation($a_anonymized)
    {
        $this->anonymized = $a_anonymized;
    }
    public function isAnonymized()
    {
        return $this->anonymized;
    }
    public static function _isAnonymized($a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC->database();
        
        $result = $ilDB->queryf(
            "SELECT anonymized FROM frm_settings WHERE obj_id = %s",
            array('integer'),
            array($a_obj_id)
        );

        while ($record = $ilDB->fetchAssoc($result)) {
            return $record['anonymized'];
        }
        
        return 0;
    }
    
    public function setPostActivation($a_post_activation)
    {
        $this->post_activation_enabled = $a_post_activation;
    }
    public function isPostActivationEnabled()
    {
        return $this->post_activation_enabled;
    }
    public function setObjId($a_obj_id = 0)
    {
        $this->obj_id = $a_obj_id;
        $this->read();
    }
    public function getObjId()
    {
        return $this->obj_id;
    }

    public function setAdminForceNoti($a_admin_force)
    {
        $this->admin_force_noti = $a_admin_force;
    }

    public function isAdminForceNoti()
    {
        return $this->admin_force_noti;
    }

    public function setUserToggleNoti($a_user_toggle)
    {
        $this->user_toggle_noti = $a_user_toggle;
    }

    public function isUserToggleNoti()
    {
        return $this->user_toggle_noti;
    }

    public static function _isAdminForceNoti($a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            "SELECT admin_force_noti FROM frm_settings WHERE obj_id = %s",
            array('integer'),
            array($a_obj_id)
        );
        while ($record = $ilDB->fetchAssoc($res)) {
            return $record['admin_force_noti'];
        }

        return 0;
    }

    public static function _isUserToggleNoti($a_obj_id)
    {
        global $DIC;
        $ilDB = $DIC->database();

        $res = $ilDB->queryF(
            "SELECT user_toggle_noti FROM frm_settings WHERE obj_id = %s",
            array('integer'),
            array($a_obj_id)
        );
        while ($record = $ilDB->fetchAssoc($res)) {
            return $record['user_toggle_noti'];
        }
        return 0;
    }

    public function setPresetSubject($a_preset_subject)
    {
        $this->preset_subject = $a_preset_subject;
    }
    public function getPresetSubject()
    {
        return $this->preset_subject;
    }
    public function setAddReSubject($a_add_re_subject)
    {
        $this->add_re_subject = $a_add_re_subject;
    }
    public function getAddReSubject()
    {
        return $this->add_re_subject;
    }

    public function setNotificationType($a_notification_type)
    {
        if ($a_notification_type == null) {
            $this->notification_type = 'default';
        } else {
            $this->notification_type = $a_notification_type;
        }
    }
    public function getNotificationType()
    {
        return $this->notification_type;
    }

    public function getSubjectSetting()
    {
        if ($this->getPresetSubject() == 0
        && $this->getAddReSubject() == 0) {
            return "empty_subject";
        } elseif ($this->getPresetSubject() == 1) {
            return "preset_subject";
        } elseif ($this->getAddReSubject() == 1) {
            return "add_re_to_subject";
        } else {
            return "preset_subject";
        }
    }
    public function setSubjectSetting($a_subject_setting)
    {
        if ($a_subject_setting == 'empty_subject') {
            $this->setPresetSubject(0);
            $this->setAddReSubject(0);
        } elseif ($a_subject_setting == 'preset_subject') {
            $this->setPresetSubject(1);
            $this->setAddReSubject(0);
        } elseif ($a_subject_setting == 'add_re_to_subject') {
            $this->setPresetSubject(0);
            $this->setAddReSubject(1);
        }
    }

    public function setMarkModeratorPosts($a_mod_post)
    {
        $this->mark_mod_posts = $a_mod_post;
    }

    public function getMarkModeratorPosts()
    {
        return $this->mark_mod_posts;
    }

    public function setThreadSorting($a_thread_sorting)
    {
        $this->thread_sorting = $a_thread_sorting;
    }
    public function getThreadSorting()
    {
        return $this->thread_sorting;
    }
    
    /**
     * @return mixed
     */
    public function getUserToggleNoti()
    {
        return $this->user_toggle_noti;
    }

    /**
     * @return mixed
     */
    public function getAdminForceNoti()
    {
        return $this->admin_force_noti;
    }

    /**
     * @param int $allowed
     * @throws InvalidArgumentException
     */
    public function setFileUploadAllowed($allowed)
    {
        $this->file_upload_allowed = $allowed;
    }

    /**
     * @return int
     */
    public function getFileUploadAllowed()
    {
        return $this->file_upload_allowed;
    }

    /**
     * @return bool
     */
    public function isFileUploadAllowed()
    {
        if (self::isFileUploadGloballyAllowed()) {
            return true;
        }

        if ((bool) $this->getFileUploadAllowed()) {
            return true;
        }
        
        return false;
    }

    /**
     * @return bool
     */
    public static function isFileUploadGloballyAllowed()
    {
        global $DIC;
        return $DIC->settings()->get('file_upload_allowed_fora', self::FILE_UPLOAD_GLOBALLY_ALLOWED) == self::FILE_UPLOAD_GLOBALLY_ALLOWED;
    }
    
    /**
     * @return bool
     */
    public static function isSendAttachmentsByMailEnabled()
    {
        global $DIC;
        return $DIC->settings()->get('send_attachments_by_mail') == true ? true : false;
    }
}
