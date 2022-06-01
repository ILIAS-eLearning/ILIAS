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

/**
 * Class ilObjCourse
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 */
class ilObjCourse extends ilContainer implements ilMembershipRegistrationCodes
{
    public const CAL_REG_START = 1;
    public const CAL_REG_END = 2;
    public const CAL_ACTIVATION_START = 3;
    public const CAL_ACTIVATION_END = 4;
    public const CAL_COURSE_START = 5;
    public const CAL_COURSE_END = 6;
    public const CAL_COURSE_TIMING_START = 7;
    public const CAL_COURSE_TIMING_END = 8;

    public const STATUS_DETERMINATION_LP = 1;
    public const STATUS_DETERMINATION_MANUAL = 2;

    private string $contact_consultation = '';
    private string $contact_phone = '';
    private string $contact_email = '';
    private string $contact_name = '';
    private string $contact_responsibility = '';
    private int $subscription_limitation_type = 0;
    private int $subscription_start = 0;
    private int $subscription_end = 0;
    private int $subscription_type = 0;
    private string $subscription_password = '';
    private int $view_mode = 0;
    private bool $waiting_list = false;
    private bool $subscription_membership_limitation = false;
    private int $subscription_max_members = 0;
    private bool $abo = true;
    private bool $show_members = true;
    private string $message = '';
    private bool $course_start_time_indication = true;
    private ?ilCourseWaitingList $waiting_list_obj = null;
    private string $important = '';
    private string $syllabus = '';
    private ilLogger $course_logger;
    private string $latitude = '';
    private string $longitude = '';
    private int $locationzoom = 0;
    private bool $enablemap = false;

    private int $session_limit = 0;
    private int $session_prev = -1;
    private int $session_next = -1;

    private string $reg_access_code = '';
    private bool $reg_access_code_enabled = false;
    private int $status_dt = 0;

    private int $mail_members = ilCourseConstants::MAIL_ALLOWED_ALL;

    private bool $crs_start_time_indication = false;

    private ?ilDateTime $crs_start = null;
    private ?ilDateTime $crs_end = null;
    private ?ilDate $leave_end = null;
    private int $min_members = 0;
    private bool $auto_fill_from_waiting = false;
    private bool $member_export = false;
    private int $timing_mode = ilCourseConstants::IL_CRS_VIEW_TIMING_ABSOLUTE;
    private bool $auto_notification = true;
    private ?string $target_group = null;
    private int $activation_start = 0;
    private int $activation_end = 0;
    private bool $activation_visibility = false;

    private ?ilCourseParticipant $member_obj = null;
    private ?ilCourseParticipants $members_obj = null;

    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        global $DIC;

        $this->setStatusDetermination(self::STATUS_DETERMINATION_LP);

        $this->type = "crs";
        $this->course_logger = $DIC->logger()->crs();
        parent::__construct($a_id, $a_call_by_reference);
    }

    public static function lookupShowMembersEnabled(int $a_obj_id) : bool
    {
        $query = 'SELECT show_members FROM crs_settings ' .
            'WHERE obj_id = ' . $GLOBALS['DIC']['ilDB']->quote($a_obj_id, 'integer');
        $res = $GLOBALS['DIC']['ilDB']->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->show_members;
        }
        return false;
    }

    public function getShowMembersExport() : bool
    {
        return $this->member_export;
    }

    public function setShowMembersExport(bool $a_mem_export) : void
    {
        $this->member_export = $a_mem_export;
    }

    public function getRegistrationAccessCode() : string
    {
        return $this->reg_access_code;
    }

    public function setRegistrationAccessCode(string $a_code) : void
    {
        $this->reg_access_code = $a_code;
    }

    public function isRegistrationAccessCodeEnabled() : bool
    {
        return $this->reg_access_code_enabled;
    }

    public function enableRegistrationAccessCode(bool $a_status) : void
    {
        $this->reg_access_code_enabled = $a_status;
    }

    public function getImportantInformation() : string
    {
        return $this->important;
    }

    public function setImportantInformation(string $a_info) : void
    {
        $this->important = $a_info;
    }

    public function getSyllabus() : string
    {
        return $this->syllabus;
    }

    public function setSyllabus(string $a_syllabus) : void
    {
        $this->syllabus = $a_syllabus;
    }

    public function getTargetGroup() : ?string
    {
        return $this->target_group;
    }

    public function setTargetGroup(?string $a_tg) : void
    {
        $this->target_group = $a_tg;
    }

    public function getContactName() : string
    {
        return $this->contact_name;
    }

    public function setContactName(string $a_cn) : void
    {
        $this->contact_name = $a_cn;
    }

    public function getContactConsultation() : string
    {
        return $this->contact_consultation;
    }

    public function setContactConsultation(string $a_value) : void
    {
        $this->contact_consultation = $a_value;
    }

    public function getContactPhone() : string
    {
        return $this->contact_phone;
    }

    public function setContactPhone(string $a_value) : void
    {
        $this->contact_phone = $a_value;
    }

    public function getContactEmail() : string
    {
        return $this->contact_email;
    }

    public function setContactEmail(string $a_value) : void
    {
        $this->contact_email = $a_value;
    }

    public function getContactResponsibility() : string
    {
        return $this->contact_responsibility;
    }

    public function setContactResponsibility(string $a_value) : void
    {
        $this->contact_responsibility = $a_value;
    }

    public function getActivationUnlimitedStatus() : bool
    {
        return !$this->getActivationStart() || !$this->getActivationEnd();
    }

    public function getActivationStart() : int
    {
        return $this->activation_start;
    }

    public function setActivationStart(int $a_value) : void
    {
        $this->activation_start = $a_value;
    }

    public function getActivationEnd() : int
    {
        return $this->activation_end;
    }

    public function setActivationEnd(int $a_value) : void
    {
        $this->activation_end = $a_value;
    }

    public function setActivationVisibility(bool $a_value) : void
    {
        $this->activation_visibility = $a_value;
    }

    public function getActivationVisibility() : bool
    {
        return $this->activation_visibility;
    }

    public function getSubscriptionLimitationType() : int
    {
        return $this->subscription_limitation_type;
    }

    public function setSubscriptionLimitationType(int $a_type) : void
    {
        $this->subscription_limitation_type = $a_type;
    }

    public function getSubscriptionUnlimitedStatus() : bool
    {
        return $this->subscription_limitation_type == ilCourseConstants::IL_CRS_SUBSCRIPTION_UNLIMITED;
    }

    public function getSubscriptionStart() : int
    {
        return $this->subscription_start;
    }

    public function setSubscriptionStart(int $a_value) : void
    {
        $this->subscription_start = $a_value;
    }

    public function getSubscriptionEnd() : int
    {
        return $this->subscription_end;
    }

    public function setSubscriptionEnd(int $a_value) : void
    {
        $this->subscription_end = $a_value;
    }

    public function getSubscriptionType() : int
    {
        return $this->subscription_type ?: ilCourseConstants::IL_CRS_SUBSCRIPTION_DIRECT;
    }

    public function setSubscriptionType(int $a_value) : void
    {
        $this->subscription_type = $a_value;
    }

    public function getSubscriptionPassword() : string
    {
        return $this->subscription_password;
    }

    public function setSubscriptionPassword(string $a_value) : void
    {
        $this->subscription_password = $a_value;
    }

    public function enabledObjectiveView() : bool
    {
        return $this->view_mode == ilCourseConstants::IL_CRS_VIEW_OBJECTIVE;
    }

    public function enabledWaitingList() : bool
    {
        return $this->waiting_list;
    }

    public function enableWaitingList(bool $a_status) : void
    {
        $this->waiting_list = $a_status;
    }

    public function inSubscriptionTime() : bool
    {
        if ($this->getSubscriptionUnlimitedStatus()) {
            return true;
        }
        if (time() > $this->getSubscriptionStart() and time() < $this->getSubscriptionEnd()) {
            return true;
        }
        return false;
    }

    public function enableSessionLimit(int $a_status) : void
    {
        $this->session_limit = $a_status;
    }

    public function isSessionLimitEnabled() : bool
    {
        return (bool) $this->session_limit;
    }

    public function enableSubscriptionMembershipLimitation(bool $a_status) : void
    {
        $this->subscription_membership_limitation = $a_status;
    }

    public function setNumberOfPreviousSessions(int $a_num) : void
    {
        $this->session_prev = $a_num;
    }

    public function getNumberOfPreviousSessions() : int
    {
        return $this->session_prev;
    }

    public function setNumberOfNextSessions(int $a_num) : void
    {
        $this->session_next = $a_num;
    }

    public function getNumberOfNextSessions() : int
    {
        return $this->session_next;
    }

    public function isSubscriptionMembershipLimited() : bool
    {
        return $this->subscription_membership_limitation;
    }

    public function getSubscriptionMaxMembers() : int
    {
        return $this->subscription_max_members;
    }

    public function setSubscriptionMaxMembers(int $a_value) : void
    {
        $this->subscription_max_members = $a_value;
    }

    public static function _isSubscriptionNotificationEnabled(int $a_course_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT * FROM crs_settings " .
            "WHERE obj_id = " . $ilDB->quote($a_course_id, 'integer') . " " .
            "AND sub_notify = 1";
        $res = $ilDB->query($query);
        return (bool) $res->numRows();
    }

    public function getSubItems(
        bool $a_admin_panel_enabled = false,
        bool $a_include_side_block = false,
        int $a_get_single = 0,
        \ilContainerUserFilter $container_user_filter = null
    ) : array {
        // Caching
        if (isset($this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block])) {
            return $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block];
        }

        // Results are stored in $this->items
        parent::getSubItems($a_admin_panel_enabled, $a_include_side_block, $a_get_single);
        $this->items = ilContainerSessionsContentGUI::prepareSessionPresentationLimitation(
            $this->items,
            $this,
            $a_admin_panel_enabled,
            $a_include_side_block
        );
        return $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block];
    }

    public function getSubscriptionNotify() : bool
    {
        return true;
    }

    public function setViewMode(int $a_mode) : void
    {
        $this->view_mode = $a_mode;
    }

    public function getViewMode() : int
    {
        return $this->view_mode;
    }

    public static function lookupTimingMode(int $a_obj_id) : int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT timing_mode FROM crs_settings ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->timing_mode;
        }
        return ilCourseConstants::IL_CRS_VIEW_TIMING_ABSOLUTE;
    }

    public function setTimingMode(int $a_mode) : void
    {
        $this->timing_mode = $a_mode;
    }

    public function getTimingMode() : int
    {
        return $this->timing_mode;
    }

    public static function _lookupViewMode(int $a_id) : int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT view_mode FROM crs_settings WHERE obj_id = " . $ilDB->quote($a_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->view_mode;
        }
        return false;
    }

    public static function _lookupAboStatus(int $a_id) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT abo FROM crs_settings WHERE obj_id = " . $ilDB->quote($a_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->abo;
        }
        return false;
    }

    public function setAboStatus(bool $a_status) : void
    {
        $this->abo = $a_status;
    }

    public function getAboStatus() : bool
    {
        return $this->abo;
    }

    public function setShowMembers(bool $a_status) : void
    {
        $this->show_members = $a_status;
    }

    public function getShowMembers() : bool
    {
        return $this->show_members;
    }

    public function setMailToMembersType(int $a_type) : void
    {
        $this->mail_members = $a_type;
    }

    public function getMailToMembersType() : int
    {
        return $this->mail_members;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

    public function setMessage(string $a_message) : void
    {
        $this->message = $a_message;
    }

    public function appendMessage(string $a_message) : void
    {
        if ($this->getMessage()) {
            $this->message .= "<br /> ";
        }
        $this->message .= $a_message;
    }

    public function isActivated() : bool
    {
        if ($this->getOfflineStatus()) {
            return false;
        }
        if ($this->getActivationUnlimitedStatus()) {
            return true;
        }
        if (time() < $this->getActivationStart() or
            time() > $this->getActivationEnd()) {
            return false;
        }
        return true;
    }

    /**
     * Is activated. Method is in Access class, since it is needed by Access/ListGUI.
     */
    public static function _isActivated(int $a_obj_id) : bool
    {
        return ilObjCourseAccess::_isActivated($a_obj_id);
    }

    /**
     * Registration enabled? Method is in Access class, since it is needed by Access/ListGUI.
     */
    public static function _registrationEnabled(int $a_obj_id) : bool
    {
        return ilObjCourseAccess::_registrationEnabled($a_obj_id);
    }

    public function read() : void
    {
        parent::read();
        $this->setOrderType(ilContainerSortingSettings::_lookupSortMode($this->getId()));
        $this->__readSettings();
    }

    public function create($a_upload = false) : int
    {
        global $DIC;

        $ilAppEventHandler = $DIC['ilAppEventHandler'];

        $id = parent::create($a_upload);

        if (!$a_upload) {
            $this->createMetaData();
        }
        $this->__createDefaultSettings();
        $this->app_event_handler->raise(
            'Modules/Course',
            'create',
            array('object' => $this,
                  'obj_id' => $this->getId(),
                  'appointments' => $this->prepareAppointments('create')
            )
        );

        return $id;
    }

    public function setLatitude(string $a_latitude) : void
    {
        $this->latitude = $a_latitude;
    }

    public function getLatitude() : string
    {
        return $this->latitude;
    }

    public function setLongitude(string $a_longitude) : void
    {
        $this->longitude = $a_longitude;
    }

    public function getLongitude() : string
    {
        return $this->longitude;
    }

    public function setLocationZoom(int $a_locationzoom) : void
    {
        $this->locationzoom = $a_locationzoom;
    }

    public function getLocationZoom() : int
    {
        return $this->locationzoom;
    }

    public function setEnableCourseMap(bool $a_enablemap) : void
    {
        $this->enablemap = $a_enablemap;
    }

    public function getEnableMap() : bool
    {
        return $this->getEnableCourseMap();
    }

    public function getEnableCourseMap() : bool
    {
        return $this->enablemap;
    }

    public function setCoursePeriod(?ilDateTime $start = null, ?ilDateTime $end = null) : void
    {
        if (
            ($start instanceof \ilDate && !$end instanceof ilDate) ||
            ($end instanceof \ilDate && !$start instanceof ilDate)
        ) {
            throw new InvalidArgumentException('Different date types not supported.');
        }

        if ($start instanceof \ilDate) {
            $this->toggleCourseStartTimeIndication(false);
        } else {
            $this->toggleCourseStartTimeIndication(true);
        }
        $this->setCourseStart($start);
        $this->setCourseEnd($end);
    }

    protected function toggleCourseStartTimeIndication(bool $time_indication) : void
    {
        $this->course_start_time_indication = $time_indication;
    }

    public function getCourseStartTimeIndication() : bool
    {
        return $this->course_start_time_indication;
    }

    protected function setCourseStart(?ilDateTime $a_value = null) : void
    {
        $this->crs_start = $a_value;
    }

    public function getCourseStart() : ?ilDateTime
    {
        return $this->crs_start;
    }

    protected function setCourseEnd(?ilDateTime $a_value = null) : void
    {
        $this->crs_end = $a_value;
    }

    public function getCourseEnd() : ?ilDateTime
    {
        return $this->crs_end;
    }

    public function setCancellationEnd(?ilDate $a_value = null) : void
    {
        $this->leave_end = $a_value;
    }

    public function getCancellationEnd() : ?ilDate
    {
        return $this->leave_end;
    }

    public function setSubscriptionMinMembers(int $a_value) : void
    {
        if ($a_value !== null) {
            $a_value = $a_value;
        }
        $this->min_members = $a_value;
    }

    public function getSubscriptionMinMembers() : int
    {
        return $this->min_members;
    }

    public function setWaitingListAutoFill(bool $a_value) : void
    {
        $this->auto_fill_from_waiting = $a_value;
    }

    public function hasWaitingListAutoFill() : bool
    {
        return $this->auto_fill_from_waiting;
    }

    /**
     * Clone course (no member data)
     *
     * @access public
     * @param int target ref_id
     * @param int copy id
     *
     */
    public function cloneObject(int $a_target_id, int $a_copy_id = 0, bool $a_omit_tree = false) : ?ilObject
    {
        global $DIC;

        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);

        $this->cloneAutoGeneratedRoles($new_obj);
        $this->cloneMetaData($new_obj);

        $new_obj->getMemberObject()->add($this->user->getId(), ilParticipants::IL_CRS_ADMIN);
        $new_obj->getMemberObject()->updateContact($this->user->getId(), 1);

        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        if ($cwo->isRootNode($this->getRefId())) {
            $this->setOfflineStatus(true);
        }

        $this->cloneSettings($new_obj);
        ilCourseDefinedFieldDefinition::_clone($this->getId(), $new_obj->getId());
        ilCourseFile::_cloneFiles($this->getId(), $new_obj->getId());
        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($new_obj->getId());
        unset($obj_settings);
        $pathFactory = new ilCertificatePathFactory();
        $templateRepository = new ilCertificateTemplateDatabaseRepository($this->db);

        $cloneAction = new ilCertificateCloneAction(
            $this->db,
            $pathFactory,
            $templateRepository,
            $DIC->filesystem()->web(),
            null,
            new ilCertificateObjectHelper()
        );

        $cloneAction->cloneCertificate($this, $new_obj);
        $book_service = new ilBookingService();
        $book_service->cloneSettings($this->getId(), $new_obj->getId());
        return $new_obj;
    }

    /**
     * @inheritDoc
     */
    public function cloneDependencies(int $a_target_id, int $a_copy_id) : bool
    {
        parent::cloneDependencies($a_target_id, $a_copy_id);

        // Clone course start objects
        $start = new ilContainerStartObjects($this->getRefId(), $this->getId());
        $start->cloneDependencies($a_target_id, $a_copy_id);

        // Clone course item settings
        ilObjectActivation::cloneDependencies($this->getRefId(), $a_target_id, $a_copy_id);

        // clone objective settings
        ilLOSettings::cloneSettings($a_copy_id, $this->getId(), ilObject::_lookupObjId($a_target_id));

        // Clone course learning objectives
        $crs_objective = new ilCourseObjective($this);
        $crs_objective->ilClone($a_target_id, $a_copy_id);

        // clone membership limitation
        foreach (\ilObjCourseGrouping::_getGroupings($this->getId()) as $grouping_id) {
            $this->course_logger->info('Handling grouping id: ' . $grouping_id);
            $grouping = new \ilObjCourseGrouping($grouping_id);
            $grouping->cloneGrouping($a_target_id, $a_copy_id);
        }
        return true;
    }

    /**
     * Clone automatic genrated roles (permissions and template permissions)
     */
    public function cloneAutoGeneratedRoles(ilObject $new_obj) : void
    {
        $admin = $this->getDefaultAdminRole();
        $new_admin = $new_obj->getDefaultAdminRole();

        if (!$admin || !$new_admin || !$this->getRefId() || !$new_obj->getRefId()) {
            $this->course_logger->debug('Error cloning auto generated role: il_crs_admin');
        }
        $this->rbac_admin->copyRolePermissions($admin, $this->getRefId(), $new_obj->getRefId(), $new_admin, true);
        $this->course_logger->debug('Finished copying of role crs_admin.');

        $tutor = $this->getDefaultTutorRole();
        $new_tutor = $new_obj->getDefaultTutorRole();
        if (!$tutor || !$new_tutor) {
            $this->course_logger->info('Error cloning auto generated role: il_crs_tutor');
        }
        $this->rbac_admin->copyRolePermissions($tutor, $this->getRefId(), $new_obj->getRefId(), $new_tutor, true);
        $this->course_logger->info('Finished copying of role crs_tutor.');

        $member = $this->getDefaultMemberRole();
        $new_member = $new_obj->getDefaultMemberRole();
        if (!$member || !$new_member) {
            $this->course_logger->debug('Error cloning auto generated role: il_crs_member');
        }
        $this->rbac_admin->copyRolePermissions($member, $this->getRefId(), $new_obj->getRefId(), $new_member, true);
        $this->course_logger->debug('Finished copying of role crs_member.');
    }

    public function validate() : bool
    {
        $this->setMessage('');

        if (($this->getSubscriptionLimitationType() == ilCourseConstants::IL_CRS_SUBSCRIPTION_LIMITED) and
            $this->getSubscriptionStart() > $this->getSubscriptionEnd()) {
            $this->appendMessage($this->lng->txt("subscription_times_not_valid"));
        }
        if ($this->getSubscriptionType() == ilCourseConstants::IL_CRS_SUBSCRIPTION_PASSWORD and !$this->getSubscriptionPassword()) {
            $this->appendMessage($this->lng->txt("crs_password_required"));
        }
        if ($this->isSubscriptionMembershipLimited()) {
            if ($this->getSubscriptionMinMembers() <= 0 && $this->getSubscriptionMaxMembers() <= 0) {
                $this->appendMessage($this->lng->txt("crs_max_and_min_members_needed"));
            }
            if ($this->getSubscriptionMaxMembers() <= 0 && $this->enabledWaitingList()) {
                $this->appendMessage($this->lng->txt("crs_max_members_needed"));
            }
            if ($this->getSubscriptionMaxMembers() > 0 && $this->getSubscriptionMinMembers() > $this->getSubscriptionMaxMembers()) {
                $this->appendMessage($this->lng->txt("crs_max_and_min_members_invalid"));
            }
        }
        if (!$this->getTitle() || !$this->getStatusDetermination()) {
            $this->appendMessage($this->lng->txt('err_check_input'));
        }
        if (($this->getCourseStart() && !$this->getCourseEnd()) ||
            (!$this->getCourseStart() && $this->getCourseEnd()) ||
            ($this->getCourseStart() && $this->getCourseEnd() && $this->getCourseStart()->get(IL_CAL_UNIX) > $this->getCourseEnd()->get(IL_CAL_UNIX))) {
            $this->appendMessage($this->lng->txt("crs_course_period_not_valid"));
        }

        return strlen($this->getMessage()) > 0;
    }

    public function validateInfoSettings() : bool
    {
        $error = false;
        if ($this->getContactEmail()) {
            $emails = explode(",", $this->getContactEmail());

            foreach ($emails as $email) {
                $email = trim($email);
                if (!ilUtil::is_email($email) && !ilObjUser::getUserIdByLogin($email)) {
                    $this->error->appendMessage($this->lng->txt('contact_email_not_valid') . " '" . $email . "'");
                    $error = true;
                }
            }
        }
        return !$error;
    }

    public function hasContactData() : bool
    {
        return strlen($this->getContactName()) || strlen($this->getContactResponsibility()) || strlen($this->getContactEmail()) || strlen($this->getContactPhone()) || strlen($this->getContactConsultation());
    }

    /**
    * delete course and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete() : bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // delete meta data
        $this->deleteMetaData();

        // put here course specific stuff

        $this->__deleteSettings();

        ilCourseParticipants::_deleteAllEntries($this->getId());

        ilCourseObjective::_deleteAll($this->getId());

        ilObjCourseGrouping::_deleteAll($this->getId());

        ilCourseFile::_deleteByCourse($this->getId());

        ilCourseDefinedFieldDefinition::_deleteByContainer($this->getId());

        $this->app_event_handler->raise(
            'Modules/Course',
            'delete',
            array('object' => $this,
                  'obj_id' => $this->getId(),
                  'appointments' => $this->prepareAppointments('delete')
            )
        );
        return true;
    }

    /**
     * @inheritDoc
     */
    public function update() : bool
    {
        $sorting = new ilContainerSortingSettings($this->getId());
        $sorting->setSortMode($this->getOrderType());
        $sorting->update();

        $this->updateMetaData();
        $this->updateSettings();
        parent::update();

        $this->app_event_handler->raise(
            'Modules/Course',
            'update',
            array('object' => $this,
                  'obj_id' => $this->getId(),
                  'appointments' => $this->prepareAppointments('update')
            )
        );
        return true;
    }

    /**
     */
    public function updateSettings() : void
    {
        $query = "SELECT * FROM crs_settings WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . " ";
        $res = $this->db->query($query);

        if (!$res->numRows()) {
            $this->__createDefaultSettings();
        }

        $query = "UPDATE crs_settings SET " .
            "syllabus = " . $this->db->quote($this->getSyllabus(), 'text') . ", " .
            "contact_name = " . $this->db->quote($this->getContactName(), 'text') . ", " .
            "contact_responsibility = " . $this->db->quote($this->getContactResponsibility(), 'text') . ", " .
            "contact_phone = " . $this->db->quote($this->getContactPhone(), 'text') . ", " .
            "contact_email = " . $this->db->quote($this->getContactEmail(), 'text') . ", " .
            "contact_consultation = " . $this->db->quote($this->getContactConsultation(), 'text') . ", " .
            "activation_type = " . $this->db->quote(!$this->getOfflineStatus(), 'integer') . ", " .
            "sub_limitation_type = " . $this->db->quote($this->getSubscriptionLimitationType(), 'integer') . ", " .
            "sub_start = " . $this->db->quote($this->getSubscriptionStart(), 'integer') . ", " .
            "sub_end = " . $this->db->quote($this->getSubscriptionEnd(), 'integer') . ", " .
            "sub_type = " . $this->db->quote($this->getSubscriptionType(), 'integer') . ", " .
            "sub_password = " . $this->db->quote($this->getSubscriptionPassword(), 'text') . ", " .
            "sub_mem_limit = " . $this->db->quote((int) $this->isSubscriptionMembershipLimited(), 'integer') . ", " .
            "sub_max_members = " . $this->db->quote($this->getSubscriptionMaxMembers(), 'integer') . ", " .
            "sub_notify = " . $this->db->quote($this->getSubscriptionNotify(), 'integer') . ", " .
            "view_mode = " . $this->db->quote($this->getViewMode(), 'integer') . ", " .
            'timing_mode = ' . $this->db->quote($this->getTimingMode(), 'integer') . ', ' .
            "abo = " . $this->db->quote($this->getAboStatus(), 'integer') . ", " .
            "waiting_list = " . $this->db->quote($this->enabledWaitingList(), 'integer') . ", " .
            "important = " . $this->db->quote($this->getImportantInformation(), 'text') . ", " .
            'target_group = ' . $this->db->quote($this->getTargetGroup(), \ilDBConstants::T_TEXT) . ', ' .
            "show_members = " . $this->db->quote($this->getShowMembers(), 'integer') . ", " .
            "show_members_export = " . $this->db->quote($this->getShowMembersExport(), 'integer') . ", " .
            "latitude = " . $this->db->quote($this->getLatitude(), 'text') . ", " .
            "longitude = " . $this->db->quote($this->getLongitude(), 'text') . ", " .
            "location_zoom = " . $this->db->quote($this->getLocationZoom(), 'integer') . ", " .
            "enable_course_map = " . $this->db->quote((int) $this->getEnableCourseMap(), 'integer') . ", " .
            'session_limit = ' . $this->db->quote($this->isSessionLimitEnabled(), 'integer') . ', ' .
            'session_prev = ' . $this->db->quote($this->getNumberOfPreviousSessions(), 'integer') . ', ' .
            'session_next = ' . $this->db->quote($this->getNumberOfNextSessions(), 'integer') . ', ' .
            'reg_ac_enabled = ' . $this->db->quote($this->isRegistrationAccessCodeEnabled(), 'integer') . ', ' .
            'reg_ac = ' . $this->db->quote($this->getRegistrationAccessCode(), 'text') . ', ' .
            'auto_notification = ' . $this->db->quote((int) $this->getAutoNotification(), 'integer') . ', ' .
            'status_dt = ' . $this->db->quote($this->getStatusDetermination(), ilDBConstants::T_INTEGER) . ', ' .
            'mail_members_type = ' . $this->db->quote($this->getMailToMembersType(), 'integer') . ', ' .
            'period_start = ' . $this->db->quote(
                \ilCalendarUtil::convertDateToUtcDBTimestamp($this->getCourseStart()),
                \ilDBConstants::T_TIMESTAMP
            ) . ', ' .
            'period_end = ' . $this->db->quote(
                \ilCalendarUtil::convertDateToUtcDBTimestamp($this->getCourseEnd()),
                \ilDBConstants::T_TIMESTAMP
            ) . ', ' .
            'period_time_indication = ' . $this->db->quote(
                $this->getCourseStartTimeIndication() ? 1 : 0,
                \ilDBConstants::T_INTEGER
            ) . ', ' .
            'auto_wait = ' . $this->db->quote((int) $this->hasWaitingListAutoFill(), 'integer') . ', ' .
            'leave_end = ' . $this->db->quote(
                ($this->getCancellationEnd() && !$this->getCancellationEnd()->isNull()) ? $this->getCancellationEnd()->get(IL_CAL_UNIX) : null,
                'integer'
            ) . ', ' .
            'min_members = ' . $this->db->quote($this->getSubscriptionMinMembers(), 'integer') . '  ' .
            "WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . "";

        $res = $this->db->manipulate($query);

        // moved activation to ilObjectActivation
        if ($this->ref_id ?? false) {
            ilObjectActivation::getItem($this->ref_id);

            $item = new ilObjectActivation();
            if (!$this->getActivationStart() || !$this->getActivationEnd()) {
                $item->setTimingType(ilObjectActivation::TIMINGS_DEACTIVATED);
            } else {
                $item->setTimingType(ilObjectActivation::TIMINGS_ACTIVATION);
                $item->setTimingStart($this->getActivationStart());
                $item->setTimingEnd($this->getActivationEnd());
                $item->toggleVisible($this->getActivationVisibility());
            }
            $item->update($this->ref_id);
        }
    }

    public function cloneSettings(ilObject $new_obj) : void
    {
        $new_obj->setSyllabus($this->getSyllabus());
        $new_obj->setContactName($this->getContactName());
        $new_obj->setContactResponsibility($this->getContactResponsibility());
        $new_obj->setContactPhone($this->getContactPhone());
        $new_obj->setContactEmail($this->getContactEmail());
        $new_obj->setContactConsultation($this->getContactConsultation());
        $new_obj->setOfflineStatus($this->getOfflineStatus()); // #9914
        $new_obj->setActivationStart($this->getActivationStart());
        $new_obj->setActivationEnd($this->getActivationEnd());
        $new_obj->setActivationVisibility($this->getActivationVisibility());
        $new_obj->setSubscriptionLimitationType($this->getSubscriptionLimitationType());
        $new_obj->setSubscriptionStart($this->getSubscriptionStart());
        $new_obj->setSubscriptionEnd($this->getSubscriptionEnd());
        $new_obj->setSubscriptionType($this->getSubscriptionType());
        $new_obj->setSubscriptionPassword($this->getSubscriptionPassword());
        $new_obj->enableSubscriptionMembershipLimitation($this->isSubscriptionMembershipLimited());
        $new_obj->setSubscriptionMaxMembers($this->getSubscriptionMaxMembers());
        $new_obj->setViewMode($this->getViewMode());
        $new_obj->setTimingMode($this->getTimingMode());
        $new_obj->setOrderType($this->getOrderType());
        $new_obj->setAboStatus($this->getAboStatus());
        $new_obj->enableWaitingList($this->enabledWaitingList());
        $new_obj->setImportantInformation($this->getImportantInformation());
        $new_obj->setTargetGroup($this->getTargetGroup());
        $new_obj->setShowMembers($this->getShowMembers());
        // patch mem_exp
        $new_obj->setShowMembersExport($this->getShowMembersExport());
        // patch mem_exp
        $new_obj->enableSessionLimit($this->isSessionLimitEnabled());
        $new_obj->setNumberOfPreviousSessions($this->getNumberOfPreviousSessions());
        $new_obj->setNumberOfNextSessions($this->getNumberOfNextSessions());
        $new_obj->setAutoNotification($this->getAutoNotification());
        $new_obj->enableRegistrationAccessCode($this->isRegistrationAccessCodeEnabled());
        $new_obj->setRegistrationAccessCode(ilMembershipRegistrationCodeUtils::generateCode());
        $new_obj->setStatusDetermination($this->getStatusDetermination());
        $new_obj->setMailToMembersType($this->getMailToMembersType());
        $new_obj->setCoursePeriod(
            $this->getCourseStart(),
            $this->getCourseEnd()
        );
        $new_obj->setCancellationEnd($this->getCancellationEnd());
        $new_obj->setWaitingListAutoFill($this->hasWaitingListAutoFill());
        $new_obj->setSubscriptionMinMembers($this->getSubscriptionMinMembers());

        // #10271
        $new_obj->setEnableCourseMap($this->getEnableCourseMap());
        $new_obj->setLatitude($this->getLatitude());
        $new_obj->setLongitude($this->getLongitude());
        $new_obj->setLocationZoom($this->getLocationZoom());

        $new_obj->update();
    }

    public function __createDefaultSettings() : void
    {
        $this->setRegistrationAccessCode(ilMembershipRegistrationCodeUtils::generateCode());

        $query = "INSERT INTO crs_settings (obj_id,syllabus,contact_name,contact_responsibility," .
            "contact_phone,contact_email,contact_consultation," .
            "sub_limitation_type,sub_start,sub_end,sub_type,sub_password,sub_mem_limit," .
            "sub_max_members,sub_notify,view_mode,timing_mode,abo," .
            "latitude,longitude,location_zoom,enable_course_map,waiting_list,show_members,show_members_export, " .
            "session_limit,session_prev,session_next, reg_ac_enabled, reg_ac, auto_notification, status_dt,mail_members_type) " .
            "VALUES( " .
            $this->db->quote($this->getId(), 'integer') . ", " .
            $this->db->quote($this->getSyllabus(), 'text') . ", " .
            $this->db->quote($this->getContactName(), 'text') . ", " .
            $this->db->quote($this->getContactResponsibility(), 'text') . ", " .
            $this->db->quote($this->getContactPhone(), 'text') . ", " .
            $this->db->quote($this->getContactEmail(), 'text') . ", " .
            $this->db->quote($this->getContactConsultation(), 'text') . ", " .
            $this->db->quote(ilCourseConstants::IL_CRS_SUBSCRIPTION_DEACTIVATED, 'integer') . ", " .
            $this->db->quote($this->getSubscriptionStart(), 'integer') . ", " .
            $this->db->quote($this->getSubscriptionEnd(), 'integer') . ", " .
            $this->db->quote(ilCourseConstants::IL_CRS_SUBSCRIPTION_DIRECT, 'integer') . ", " .
            $this->db->quote($this->getSubscriptionPassword(), 'text') . ", " .
            "0, " .
            $this->db->quote($this->getSubscriptionMaxMembers(), 'integer') . ", " .
            "1, " .
            "0, " .
            $this->db->quote(ilCourseConstants::IL_CRS_VIEW_TIMING_ABSOLUTE, 'integer') . ', ' .
            $this->db->quote($this->getAboStatus(), 'integer') . ", " .
            $this->db->quote($this->getLatitude(), 'text') . ", " .
            $this->db->quote($this->getLongitude(), 'text') . ", " .
            $this->db->quote($this->getLocationZoom(), 'integer') . ", " .
            $this->db->quote($this->getEnableCourseMap(), 'integer') . ", " .
            #"objective_view = '0', ".
            "1, " .
            "1," .
            '0,' .
            $this->db->quote($this->isSessionLimitEnabled(), 'integer') . ', ' .
            $this->db->quote($this->getNumberOfPreviousSessions(), 'integer') . ', ' .
            $this->db->quote($this->getNumberOfPreviousSessions(), 'integer') . ', ' .
            $this->db->quote($this->isRegistrationAccessCodeEnabled(), 'integer') . ', ' .
            $this->db->quote($this->getRegistrationAccessCode(), 'text') . ', ' .
            $this->db->quote((int) $this->getAutoNotification(), 'integer') . ', ' .
            $this->db->quote($this->getStatusDetermination(), 'integer') . ', ' .
            $this->db->quote($this->getMailToMembersType(), 'integer') . ' ' .
            ")";

        $res = $this->db->manipulate($query);
        $this->__readSettings();

        $sorting = new ilContainerSortingSettings($this->getId());
        $sorting->setSortMode(ilContainer::SORT_MANUAL);
        $sorting->update();
    }

    public function __readSettings() : void
    {
        $query = "SELECT * FROM crs_settings WHERE obj_id = " . $this->db->quote($this->getId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setSyllabus((string) $row->syllabus);
            $this->setTargetGroup($row->target_group);
            $this->setContactName((string) $row->contact_name);
            $this->setContactResponsibility((string) $row->contact_responsibility);
            $this->setContactPhone((string) $row->contact_phone);
            $this->setContactEmail((string) $row->contact_email);
            $this->setContactConsultation((string) $row->contact_consultation);
            $this->setOfflineStatus(!(bool) $row->activation_type); // see below
            $this->setSubscriptionLimitationType((int) $row->sub_limitation_type);
            $this->setSubscriptionStart((int) $row->sub_start);
            $this->setSubscriptionEnd((int) $row->sub_end);
            $this->setSubscriptionType((int) $row->sub_type);
            $this->setSubscriptionPassword((string) $row->sub_password);
            $this->enableSubscriptionMembershipLimitation((bool) $row->sub_mem_limit);
            $this->setSubscriptionMaxMembers((int) $row->sub_max_members);
            $this->setViewMode((int) $row->view_mode);
            $this->setTimingMode((int) $row->timing_mode);
            $this->setAboStatus((bool) $row->abo);
            $this->enableWaitingList((bool) $row->waiting_list);
            $this->setImportantInformation((string) $row->important);
            $this->setShowMembers((bool) $row->show_members);

            if (\ilPrivacySettings::getInstance()->participantsListInCoursesEnabled()) {
                $this->setShowMembersExport((bool) $row->show_members_export);
            } else {
                $this->setShowMembersExport(false);
            }
            $this->setLatitude((string) $row->latitude);
            $this->setLongitude((string) $row->longitude);
            $this->setLocationZoom((int) $row->location_zoom);
            $this->setEnableCourseMap((bool) $row->enable_course_map);
            $this->enableSessionLimit((int) $row->session_limit);
            $this->setNumberOfPreviousSessions((int) $row->session_prev);
            $this->setNumberOfNextSessions((int) $row->session_next);
            $this->enableRegistrationAccessCode((bool) $row->reg_ac_enabled);
            $this->setRegistrationAccessCode((string) $row->reg_ac);
            $this->setAutoNotification((bool) $row->auto_notification);
            $this->setStatusDetermination((int) $row->status_dt);
            $this->setMailToMembersType((int) $row->mail_members_type);

            if ($row->period_time_indication) {
                $this->setCoursePeriod(
                    new \ilDateTime($row->period_start, IL_CAL_DATETIME, \ilTimeZone::UTC),
                    new \ilDateTime($row->period_end, IL_CAL_DATETIME, \ilTimeZone::UTC)
                );
            } elseif (!is_null($row->period_start) && !is_null($row->period_end)) {
                $this->setCoursePeriod(
                    new \ilDate($row->period_start, IL_CAL_DATE),
                    new \ilDate($row->period_end, IL_CAL_DATE)
                );
            }
            $this->toggleCourseStartTimeIndication((bool) $row->period_time_indication);
            $this->setCancellationEnd($row->leave_end ? new ilDate($row->leave_end, IL_CAL_UNIX) : null);
            $this->setWaitingListAutoFill((bool) $row->auto_wait);
            $this->setSubscriptionMinMembers((int) $row->min_members);
        }

        // moved activation to ilObjectActivation
        if ($this->ref_id ?? false) {
            $activation = ilObjectActivation::getItem($this->ref_id);
            switch ($activation["timing_type"]) {
                case ilObjectActivation::TIMINGS_ACTIVATION:
                    $this->setActivationStart((int) $activation["timing_start"]);
                    $this->setActivationEnd((int) $activation["timing_end"]);
                    $this->setActivationVisibility((bool) $activation["visible"]);
                    break;
            }
        }
    }

    public function initWaitingList() : void
    {
        if (!$this->waiting_list_obj instanceof ilCourseWaitingList) {
            $this->waiting_list_obj = new ilCourseWaitingList($this->getId());
        }
    }

    protected function initCourseMemberObject() : void
    {
        $this->member_obj = ilCourseParticipant::_getInstanceByObjId($this->getId(), $this->user->getId());
    }

    protected function initCourseMembersObject() : void
    {
        $this->members_obj = ilCourseParticipants::_getInstanceByObjId($this->getId());
    }

    public function getMemberObject() : ilCourseParticipant
    {
        if (!$this->member_obj instanceof ilCourseParticipant) {
            $this->initCourseMemberObject();
        }
        return $this->member_obj;
    }

    public function getMembersObject() : ilCourseParticipants
    {
        if (!$this->members_obj instanceof ilCourseParticipants) {
            $this->initCourseMembersObject();
        }
        return $this->members_obj;
    }


    // RBAC METHODS
    public function initDefaultRoles() : void
    {
        ilObjRole::createDefaultRole(
            'il_crs_admin_' . $this->getRefId(),
            "Admin of crs obj_no." . $this->getId(),
            'il_crs_admin',
            $this->getRefId()
        );
        ilObjRole::createDefaultRole(
            'il_crs_tutor_' . $this->getRefId(),
            "Tutor of crs obj_no." . $this->getId(),
            'il_crs_tutor',
            $this->getRefId()
        );
        ilObjRole::createDefaultRole(
            'il_crs_member_' . $this->getRefId(),
            "Member of crs obj_no." . $this->getId(),
            'il_crs_member',
            $this->getRefId()
        );
    }

    /**
     * This method is called before "initDefaultRoles".
     * Therefore now local course roles are created.
     * Grants permissions on the course object for all parent roles.
     * Each permission is granted by computing the intersection of the
     * template il_crs_non_member and the permission template of the parent role.
     */
    public function setParentRolePermissions(int $a_parent_ref) : bool
    {
        $parent_roles = $this->rbac_review->getParentRoleIds($a_parent_ref);
        foreach ($parent_roles as $parent_role) {
            $this->rbac_admin->initIntersectionPermissions(
                $this->getRefId(),
                $parent_role['obj_id'],
                $parent_role['parent'],
                $this->__getCrsNonMemberTemplateId(),
                ROLE_FOLDER_ID
            );
        }
        return true;
    }

    public function __getCrsNonMemberTemplateId() : int
    {
        $q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_crs_non_member'";
        $res = $this->ilias->db->query($q);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        return (int) $row["obj_id"];
    }

    public static function lookupCourseNonMemberTemplatesId() : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT obj_id FROM object_data WHERE type = ' . $ilDB->quote(
            'rolt',
            'text'
        ) . ' AND title = ' . $ilDB->quote('il_crs_non_member', 'text');
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        return (int) $row['obj_id'];
    }

    /**
     * get ALL local roles of course, also those created and defined afterwards
     * only fetch data once from database. info is stored in object variable
     * @access    public
     * @return    array [title|id] of roles...
     */
    public function getLocalCourseRoles($a_translate = false) : array
    {
        if (empty($this->local_roles)) {
            $this->local_roles = array();
            $role_arr = $this->rbac_review->getRolesOfRoleFolder($this->getRefId());

            foreach ($role_arr as $role_id) {
                if ($this->rbac_review->isAssignable($role_id, $this->getRefId()) == true) {
                    $role_Obj = ilObjectFactory::getInstanceByObjId($role_id);
                    if ($a_translate) {
                        $role_name = ilObjRole::_getTranslation($role_Obj->getTitle());
                    } else {
                        $role_name = $role_Obj->getTitle();
                    }
                    $this->local_roles[$role_name] = $role_Obj->getId();
                }
            }
        }
        return $this->local_roles;
    }

    /**
     * get default course roles, returns the defaultlike create roles
     * il_crs_tutor, il_crs_admin and il_crs_member
     * Returns the obj_ids of course specific roles in an associative
     *           array.
     *            key=descripiton of the role (i.e. "il_crs_tutor", "il_crs_admin", "il_crs_member".
     *            value=obj_id of the role
     */
    public function getDefaultCourseRoles(string $a_crs_id = "") : array
    {
        if (strlen($a_crs_id) > 0) {
            $crs_id = $a_crs_id;
        } else {
            $crs_id = $this->getRefId();
        }

        $role_arr = $this->rbac_review->getRolesOfRoleFolder($crs_id);

        $arr_crsDefaultRoles = [];
        foreach ($role_arr as $role_id) {
            $role_Obj = &$this->ilias->obj_factory->getInstanceByObjId($role_id);

            $crs_Member = "il_crs_member_" . $crs_id;
            $crs_Admin = "il_crs_admin_" . $crs_id;
            $crs_Tutor = "il_crs_tutor_" . $crs_id;

            if (strcmp($role_Obj->getTitle(), $crs_Member) == 0) {
                $arr_crsDefaultRoles["crs_member_role"] = $role_Obj->getId();
            }

            if (strcmp($role_Obj->getTitle(), $crs_Admin) == 0) {
                $arr_crsDefaultRoles["crs_admin_role"] = $role_Obj->getId();
            }

            if (strcmp($role_Obj->getTitle(), $crs_Tutor) == 0) {
                $arr_crsDefaultRoles["crs_tutor_role"] = $role_Obj->getId();
            }
        }

        return $arr_crsDefaultRoles;
    }

    /**
     * @return int[]
     */
    public function __getLocalRoles() : array
    {
        return $this->rbac_review->getRolesOfRoleFolder($this->getRefId(), false);
    }

    public function __deleteSettings() : void
    {
        $query = "DELETE FROM crs_settings " .
            "WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . " ";
        $this->db->manipulate($query);
    }

    public function getDefaultMemberRole() : int
    {
        $local_roles = $this->__getLocalRoles();
        foreach ($local_roles as $role_id) {
            $title = ilObject::_lookupTitle($role_id);
            if (substr($title, 0, 8) == 'il_crs_m') {
                return $role_id;
            }
        }
        return 0;
    }

    public function getDefaultTutorRole() : int
    {
        $local_roles = $this->__getLocalRoles();
        foreach ($local_roles as $role_id) {
            if ($tmp_role = ilObjectFactory::getInstanceByObjId($role_id, false)) {
                if (!strcmp($tmp_role->getTitle(), "il_crs_tutor_" . $this->getRefId())) {
                    return $role_id;
                }
            }
        }
        return 0;
    }

    public function getDefaultAdminRole() : int
    {
        $local_roles = $this->__getLocalRoles();
        foreach ($local_roles as $role_id) {
            if ($tmp_role = ilObjectFactory::getInstanceByObjId($role_id, false)) {
                if (!strcmp($tmp_role->getTitle(), "il_crs_admin_" . $this->getRefId())) {
                    return $role_id;
                }
            }
        }
        return 0;
    }

    public static function _deleteUser(int $a_usr_id) : void
    {
        ilCourseLMHistory::_deleteUser($a_usr_id);
        ilCourseParticipants::_deleteUser($a_usr_id);
        ilLOUserResults::deleteResultsForUser($a_usr_id);
    }

    protected function doMDUpdateListener(string $a_element) : void
    {
        switch ($a_element) {
            case 'General':
                // Update ecs content
                $ecs = new ilECSCourseSettings($this);
                $ecs->handleContentUpdate();
                break;
        }
    }

    public function addAdditionalSubItemInformation(array &$object) : void
    {
        ilObjectActivation::addAdditionalSubItemInformation($object);
    }

    /**
     * Prepare calendar appointments
     */
    protected function prepareAppointments(string $a_mode = 'create') : array
    {
        switch ($a_mode) {
            case 'create':
            case 'update':
                $apps = [];
                if (!$this->getActivationUnlimitedStatus() && !$this->getOfflineStatus()) {
                    $app = new ilCalendarAppointmentTemplate(self::CAL_ACTIVATION_START);
                    $app->setTitle($this->getTitle());
                    $app->setSubtitle('crs_cal_activation_start');
                    $app->setTranslationType(ilCalendarEntry::TRANSLATION_SYSTEM);
                    $app->setDescription($this->getLongDescription());
                    $app->setStart(new ilDateTime($this->getActivationStart(), IL_CAL_UNIX));
                    $apps[] = $app;

                    $app = new ilCalendarAppointmentTemplate(self::CAL_ACTIVATION_END);
                    $app->setTitle($this->getTitle());
                    $app->setSubtitle('crs_cal_activation_end');
                    $app->setTranslationType(ilCalendarEntry::TRANSLATION_SYSTEM);
                    $app->setDescription($this->getLongDescription());
                    $app->setStart(new ilDateTime($this->getActivationEnd(), IL_CAL_UNIX));
                    $apps[] = $app;
                }
                if ($this->getSubscriptionLimitationType() == ilCourseConstants::IL_CRS_SUBSCRIPTION_LIMITED) {
                    $app = new ilCalendarAppointmentTemplate(self::CAL_REG_START);
                    $app->setTitle($this->getTitle());
                    $app->setSubtitle('crs_cal_reg_start');
                    $app->setTranslationType(ilCalendarEntry::TRANSLATION_SYSTEM);
                    $app->setDescription($this->getLongDescription());
                    $app->setStart(new ilDateTime($this->getSubscriptionStart(), IL_CAL_UNIX));
                    $apps[] = $app;

                    $app = new ilCalendarAppointmentTemplate(self::CAL_REG_END);
                    $app->setTitle($this->getTitle());
                    $app->setSubtitle('crs_cal_reg_end');
                    $app->setTranslationType(ilCalendarEntry::TRANSLATION_SYSTEM);
                    $app->setDescription($this->getLongDescription());
                    $app->setStart(new ilDateTime($this->getSubscriptionEnd(), IL_CAL_UNIX));
                    $apps[] = $app;
                }
                if ($this->getCourseStart() && $this->getCourseEnd()) {
                    $app = new ilCalendarAppointmentTemplate(self::CAL_COURSE_START);
                    $app->setTitle($this->getTitle());
                    $app->setSubtitle('crs_cal_start');
                    $app->setTranslationType(ilCalendarEntry::TRANSLATION_SYSTEM);
                    $app->setDescription($this->getLongDescription());
                    $app->setStart($this->getCourseStart());
                    $app->setFullday(!$this->getCourseStartTimeIndication());
                    $apps[] = $app;

                    $app = new ilCalendarAppointmentTemplate(self::CAL_COURSE_END);
                    $app->setTitle($this->getTitle());
                    $app->setSubtitle('crs_cal_end');
                    $app->setTranslationType(ilCalendarEntry::TRANSLATION_SYSTEM);
                    $app->setDescription($this->getLongDescription());
                    $app->setStart($this->getCourseEnd());
                    $app->setFullday(!$this->getCourseStartTimeIndication());
                    $apps[] = $app;
                }
                if (
                    $this->getViewMode() == ilCourseConstants::IL_CRS_VIEW_TIMING
                ) {
                    $active = ilObjectActivation::getTimingsItems($this->getRefId());
                    foreach ($active as $null => $item) {
                        if ($item['timing_type'] == ilObjectActivation::TIMINGS_PRESETTING) {
                            // create calendar entry for fixed types
                            $app = new ilCalendarAppointmentTemplate(self::CAL_COURSE_TIMING_START);
                            $app->setContextInfo($item['ref_id']);
                            $app->setTitle($item['title']);
                            $app->setSubtitle('cal_crs_timing_start');
                            $app->setTranslationType(ilCalendarEntry::TRANSLATION_SYSTEM);
                            $app->setStart(new ilDate($item['suggestion_start'], IL_CAL_UNIX));
                            $app->setFullday(true);
                            $apps[] = $app;

                            $app = new ilCalendarAppointmentTemplate(self::CAL_COURSE_TIMING_END);
                            $app->setContextInfo($item['ref_id']);
                            $app->setTitle($item['title']);
                            $app->setSubtitle('cal_crs_timing_end');
                            $app->setTranslationType(ilCalendarEntry::TRANSLATION_SYSTEM);
                            $app->setStart(new ilDate($item['suggestion_end'], IL_CAL_UNIX));
                            $app->setFullday(true);
                            $apps[] = $app;
                        }
                    }
                }
                return $apps;

            case 'delete':
                // Nothing to do: The category and all assigned appointments will be deleted.
                return array();
        }
        return [];
    }

    /**
     * @see interface.ilMembershipRegistrationCodes
     * @return int[]
     */
    public static function lookupObjectsByCode(string $a_code) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT obj_id FROM crs_settings " .
            "WHERE reg_ac_enabled = " . $ilDB->quote(1, 'integer') . " " .
            "AND reg_ac = " . $ilDB->quote($a_code, 'text');
        $res = $ilDB->query($query);

        $obj_ids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = (int) $row->obj_id;
        }
        return $obj_ids;
    }

    /**
     * @throws ilMembershipRegistrationException
     * @see ilMembershipRegistrationCodes::register()
     */
    public function register(
        int $a_user_id,
        int $a_role = ilCourseConstants::CRS_MEMBER,
        bool $a_force_registration = false
    ) : void {
        if ($this->getMembersObject()->isAssigned($a_user_id)) {
            return;
        }

        if (!$a_force_registration) {
            // offline
            if (ilObjCourseAccess::_isOffline($this->getId())) {
                throw new ilMembershipRegistrationException(
                    "Can't register to course, course is offline.",
                    ilMembershipRegistrationException::REGISTRATION_INVALID_OFFLINE
                );
            }
            // activation
            if (!ilObjCourseAccess::_isActivated($this->getId())) {
                throw new ilMembershipRegistrationException(
                    "Can't register to course, course is not activated.",
                    ilMembershipRegistrationException::REGISTRATION_INVALID_AVAILABILITY
                );
            }

            if ($this->getSubscriptionLimitationType() == ilCourseConstants::IL_CRS_SUBSCRIPTION_DEACTIVATED) {
                if (!ilObjCourseAccess::_usingRegistrationCode()) {
                    throw new ilMembershipRegistrationException(
                        'Cant registrate to course ' . $this->getId() .
                        ', course subscription is deactivated.',
                        ilMembershipRegistrationException::REGISTRATION_CODE_DISABLED
                    );
                }
            }

            // Time Limitation
            if ($this->getSubscriptionLimitationType() == ilCourseConstants::IL_CRS_SUBSCRIPTION_LIMITED) {
                if (!$this->inSubscriptionTime()) {
                    throw new ilMembershipRegistrationException(
                        'Cant registrate to course ' . $this->getId() .
                        ', course is out of registration time.',
                        ilMembershipRegistrationException::OUT_OF_REGISTRATION_PERIOD
                    );
                }
            }

            // Max members
            if ($this->isSubscriptionMembershipLimited()) {
                $free = max(0, $this->getSubscriptionMaxMembers() - $this->getMembersObject()->getCountMembers());
                $waiting_list = new ilCourseWaitingList($this->getId());
                if ($this->enabledWaitingList() && (!$free || $waiting_list->getCountUsers())) {
                    $waiting_list->addToList($a_user_id);
                    $this->lng->loadLanguageModule("crs");
                    $info = sprintf(
                        $this->lng->txt('crs_added_to_list'),
                        $waiting_list->getPosition($a_user_id)
                    );
                    $participants = ilCourseParticipants::_getInstanceByObjId($this->getId());
                    $participants->sendNotification(
                        ilCourseMembershipMailNotification::TYPE_WAITING_LIST_MEMBER,
                        $a_user_id
                    );

                    throw new ilMembershipRegistrationException(
                        $info,
                        ilMembershipRegistrationException::ADDED_TO_WAITINGLIST
                    );
                }

                if (!$this->enabledWaitingList() && !$free) {
                    throw new ilMembershipRegistrationException('Cant registrate to course ' . $this->getId() .
                        ', membership is limited.', ilMembershipRegistrationException::OBJECT_IS_FULL);
                }
            }
        }

        $this->getMembersObject()->add($a_user_id, $a_role);
        $this->getMembersObject()->sendNotification(ilCourseMembershipMailNotification::TYPE_ADMISSION_MEMBER, $a_user_id);
        $this->getMembersObject()->sendNotification(ilCourseMembershipMailNotification::TYPE_NOTIFICATION_ADMINS, $a_user_id);
        ilForumNotification::checkForumsExistsInsert($this->getRefId(), $a_user_id);
    }

    /**
     * Returns automatic notification status from
     * $this->auto_notification
     */
    public function getAutoNotification() : bool
    {
        return $this->auto_notification;
    }

    /**
     * Sets automatic notification status in $this->auto_notification,
     * using given $status.
     */
    public function setAutoNotification(bool $value) : void
    {
        $this->auto_notification = $value;
    }

    /**
     * Set status determination mode
     */
    public function setStatusDetermination(int $a_value) : void
    {
        $a_value = $a_value;

        // #13905
        if ($a_value == self::STATUS_DETERMINATION_LP) {
            if (!ilObjUserTracking::_enabledLearningProgress()) {
                $a_value = self::STATUS_DETERMINATION_MANUAL;
            }
        }

        $this->status_dt = $a_value;
    }

    /**
     * Get status determination mode
     */
    public function getStatusDetermination() : int
    {
        return $this->status_dt;
    }

    public function syncMembersStatusWithLP() : void
    {
        foreach ($this->getMembersObject()->getParticipants() as $user_id) {
            // #15529 - force raise on sync
            ilLPStatusWrapper::_updateStatus($this->getId(), $user_id, null, false, true);
        }
    }

    /**
     * sync course status from lp
     * as lp data is not deleted on course exit new members may already have lp completed
     */
    public function checkLPStatusSync(int $a_member_id) : void
    {
        // #11113
        if (ilObjUserTracking::_enabledLearningProgress() &&
            $this->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP) {
            // #13811 - we need to suppress creation if status entry
            $has_completed = (ilLPStatus::_lookupStatus(
                $this->getId(),
                $a_member_id,
                false
            ) == ilLPStatus::LP_STATUS_COMPLETED_NUM);
            $this->getMembersObject()->updatePassed($a_member_id, $has_completed, false, true);
        }
    }

    public function getOrderType() : int
    {
        if ($this->enabledObjectiveView()) {
            return ilContainer::SORT_MANUAL;
        }
        return parent::getOrderType();
    }

    /**
     * Handle course auto fill
     */
    public function handleAutoFill() : void
    {
        if (
            !$this->enabledWaitingList() || !$this->hasWaitingListAutoFill()
        ) {
            $this->course_logger->debug('Waiting list or auto fill disabled.');
            return;
        }

        $max = $this->getSubscriptionMaxMembers();
        $now = ilCourseParticipants::lookupNumberOfMembers($this->getRefId());

        $this->course_logger->debug('Max members: ' . $max);
        $this->course_logger->debug('Current members: ' . $now);

        if ($max <= $now) {
            return;
        }

        // see assignFromWaitingListObject()
        $waiting_list = new ilCourseWaitingList($this->getId());

        foreach ($waiting_list->getUserIds() as $user_id) {
            if (!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id, false)) {
                $this->course_logger->warning('Cannot create user instance for id: ' . $user_id);
                continue;
            }
            if ($this->getMembersObject()->isAssigned($user_id)) {
                $this->course_logger->warning('User is already assigned to course. uid: ' . $user_id . ' course_id: ' . $this->getRefId());
                continue;
            }
            $this->getMembersObject()->add($user_id, ilParticipants::IL_CRS_MEMBER);
            $this->getMembersObject()->sendNotification(ilCourseMembershipMailNotification::TYPE_ADMISSION_MEMBER, $user_id, true);
            $waiting_list->removeFromList($user_id);
            $this->checkLPStatusSync($user_id);

            $this->course_logger->info('Assigned user from waiting list to course: ' . $this->getTitle());
            $now++;
            if ($now >= $max) {
                break;
            }
        }
    }

    public static function mayLeave(int $a_course_id, int $a_user_id = 0, &$a_date = null) : bool
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilDB = $DIC['ilDB'];

        if (!$a_user_id) {
            $a_user_id = $ilUser->getId();
        }

        $set = $ilDB->query("SELECT leave_end" .
            " FROM crs_settings" .
            " WHERE obj_id = " . $ilDB->quote($a_course_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        if ($row && $row["leave_end"]) {
            // timestamp to date
            $limit = date("Ymd", $row["leave_end"]);
            if ($limit < date("Ymd")) {
                $a_date = new ilDate(date("Y-m-d", $row["leave_end"]), IL_CAL_DATE);
                return false;
            }
        }
        return true;
    }

    public static function findCoursesWithNotEnoughMembers() : array
    {
        $ilDB = $GLOBALS['DIC']->database();
        $tree = $GLOBALS['DIC']->repositoryTree();

        $res = array();

        $before = new ilDateTime(time(), IL_CAL_UNIX);
        $before->increment(IL_CAL_DAY, -1);
        $now = $before->get(IL_CAL_UNIX);

        $set = $ilDB->query("SELECT obj_id, min_members" .
            " FROM crs_settings" .
            " WHERE min_members > " . $ilDB->quote(0, "integer") .
            " AND sub_mem_limit = " . $ilDB->quote(1, "integer") . // #17206
            " AND ((leave_end IS NOT NULL" .
            " AND leave_end < " . $ilDB->quote($now, "text") . ")" .
            " OR (leave_end IS NULL" .
            " AND sub_end IS NOT NULL" .
            " AND sub_end < " . $ilDB->quote($now, "text") . "))" .
            " AND (period_start IS NULL OR period_start > " . $ilDB->quote($now, "integer") . ")");
        while ($row = $ilDB->fetchAssoc($set)) {
            $refs = ilObject::_getAllReferences((int) $row['obj_id']);
            $ref = end($refs);

            if ($tree->isDeleted($ref)) {
                continue;
            }

            $part = new ilCourseParticipants((int) $row["obj_id"]);
            $reci = $part->getNotificationRecipients();
            if ($reci !== []) {
                $missing = (int) $row["min_members"] - $part->getCountMembers();
                if ($missing > 0) {
                    $res[(int) $row["obj_id"]] = array($missing, $reci);
                }
            }
        }

        return $res;
    }
} //END class.ilObjCourse
