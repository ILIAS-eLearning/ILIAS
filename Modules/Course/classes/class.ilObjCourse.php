<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without ceven the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

require_once "./Services/Container/classes/class.ilContainer.php";
include_once './Modules/Course/classes/class.ilCourseConstants.php';
include_once './Services/Membership/interfaces/interface.ilMembershipRegistrationCodes.php';

/**
* Class ilObjCourse
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/
class ilObjCourse extends ilContainer implements ilMembershipRegistrationCodes
{
    /**
     * @var ilLogger
     */
    protected $course_logger = null;
    

    const CAL_REG_START = 1;
    const CAL_REG_END = 2;
    const CAL_ACTIVATION_START = 3;
    const CAL_ACTIVATION_END = 4;
    const CAL_COURSE_START = 5;
    const CAL_COURSE_END = 6;
    const CAL_COURSE_TIMING_START = 7;
    const CAL_COURSE_TIMING_END = 8;

    
    const STATUS_DETERMINATION_LP = 1;
    const STATUS_DETERMINATION_MANUAL = 2;

    private $member_obj = null;
    private $members_obj = null;
    public $archives_obj;
    
    private $latitude = '';
    private $longitude = '';
    private $locationzoom = 0;
    private $enablemap = 0;
    
    private $session_limit = 0;
    private $session_prev = -1;
    private $session_next = -1;
    
    private $reg_access_code = '';
    private $reg_access_code_enabled = false;
    private $status_dt = null;
    
    private $mail_members = ilCourseConstants::MAIL_ALLOWED_ALL;

    protected $crs_start; // [ilDate]
    protected $crs_end; // [ilDate]
    protected $leave_end; // [ilDate]
    protected $min_members; // [int]
    protected $auto_fill_from_waiting; // [bool]

    /**
     * @var bool
     */
    protected $member_export = false;

    /**
     * @var int
     */
    private $timing_mode = ilCourseConstants::IL_CRS_VIEW_TIMING_ABSOLUTE;

    /**
     * @var boolean
     * @access private
     *
     */
    private $auto_notification = true;


    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        
        #define("ILIAS_MODULE","course");
        #define("KEEP_IMAGE_PATH",1);

        $this->SUBSCRIPTION_DEACTIVATED = 1;
        $this->SUBSCRIPTION_CONFIRMATION = 2;
        $this->SUBSCRIPTION_DIRECT = 3;
        $this->SUBSCRIPTION_PASSWORD = 4;
        $this->SUBSCRIPTION_AUTOSUBSCRIPTION = 5;
        $this->ARCHIVE_DISABLED = 1;
        $this->ARCHIVE_READ = 2;
        $this->ARCHIVE_DOWNLOAD = 3;
        $this->ABO_ENABLED = 1;
        $this->ABO_DISABLED = 0;
        $this->SHOW_MEMBERS_ENABLED = 1;
        $this->SHOW_MEMBERS_DISABLED = 0;
        $this->setStatusDetermination(self::STATUS_DETERMINATION_LP);

        $this->type = "crs";
        
        $this->course_logger = $GLOBALS['DIC']->logger()->crs();

        parent::__construct($a_id, $a_call_by_reference);
    }
    
    /**
     * Check if show member is enabled
     * @param int $a_obj_id
     * @return bool
     */
    public static function lookupShowMembersEnabled($a_obj_id)
    {
        $query = 'SELECT show_members FROM crs_settings ' .
                'WHERE obj_id = ' . $GLOBALS['DIC']['ilDB']->quote($a_obj_id, 'integer');
        $res = $GLOBALS['DIC']['ilDB']->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->show_members;
        }
        return false;
    }
    
    public function getShowMembersExport()
    {
        return $this->member_export;
    }
    
    public function setShowMembersExport($a_mem_export)
    {
        $this->member_export = $a_mem_export;
    }

    /**
     * get access code
     * @return
     */
    public function getRegistrationAccessCode()
    {
        return $this->reg_access_code;
    }
    
    /**
     * Set refistration access code
     * @param string $a_code
     * @return
     */
    public function setRegistrationAccessCode($a_code)
    {
        $this->reg_access_code = $a_code;
    }
    
    /**
     * Check if access code is enabled
     * @return
     */
    public function isRegistrationAccessCodeEnabled()
    {
        return (bool) $this->reg_access_code_enabled;
    }
    
    /**
     * En/disable registration access code
     * @param object $a_status
     * @return
     */
    public function enableRegistrationAccessCode($a_status)
    {
        $this->reg_access_code_enabled = $a_status;
    }

    public function getImportantInformation()
    {
        return $this->important;
    }
    public function setImportantInformation($a_info)
    {
        $this->important = $a_info;
    }
    public function getSyllabus()
    {
        return $this->syllabus;
    }
    public function setSyllabus($a_syllabus)
    {
        $this->syllabus = $a_syllabus;
    }
    public function getContactName()
    {
        return $this->contact_name;
    }
    public function setContactName($a_cn)
    {
        $this->contact_name = $a_cn;
    }
    public function getContactConsultation()
    {
        return $this->contact_consultation;
    }
    public function setContactConsultation($a_value)
    {
        $this->contact_consultation = $a_value;
    }
    public function getContactPhone()
    {
        return $this->contact_phone;
    }
    public function setContactPhone($a_value)
    {
        $this->contact_phone = $a_value;
    }
    public function getContactEmail()
    {
        return $this->contact_email;
    }
    public function setContactEmail($a_value)
    {
        $this->contact_email = $a_value;
    }
    public function getContactResponsibility()
    {
        return $this->contact_responsibility;
    }
    public function setContactResponsibility($a_value)
    {
        $this->contact_responsibility = $a_value;
    }
    /**
     * get activation unlimited no start or no end
     *
     * @return bool
     */
    public function getActivationUnlimitedStatus()
    {
        return !$this->getActivationStart() || !$this->getActivationEnd();
    }
    public function getActivationStart()
    {
        return $this->activation_start;
    }
    public function setActivationStart($a_value)
    {
        $this->activation_start = $a_value;
    }
    public function getActivationEnd()
    {
        return $this->activation_end;
    }
    public function setActivationEnd($a_value)
    {
        $this->activation_end = $a_value;
    }
    public function setActivationVisibility($a_value)
    {
        $this->activation_visibility = (bool) $a_value;
    }
    public function getActivationVisibility()
    {
        return $this->activation_visibility;
    }

    public function getSubscriptionLimitationType()
    {
        return $this->subscription_limitation_type;
    }
    public function setSubscriptionLimitationType($a_type)
    {
        $this->subscription_limitation_type = $a_type;
    }
    public function getSubscriptionUnlimitedStatus()
    {
        return $this->subscription_limitation_type == IL_CRS_SUBSCRIPTION_UNLIMITED;
    }
    public function getSubscriptionStart()
    {
        return $this->subscription_start;
    }
    public function setSubscriptionStart($a_value)
    {
        $this->subscription_start = $a_value;
    }
    public function getSubscriptionEnd()
    {
        return $this->subscription_end;
    }
    public function setSubscriptionEnd($a_value)
    {
        $this->subscription_end = $a_value;
    }
    public function getSubscriptionType()
    {
        return $this->subscription_type ? $this->subscription_type : IL_CRS_SUBSCRIPTION_DIRECT;
        #return $this->subscription_type ? $this->subscription_type : $this->SUBSCRIPTION_DEACTIVATED;
    }
    public function setSubscriptionType($a_value)
    {
        $this->subscription_type = $a_value;
    }
    public function getSubscriptionPassword()
    {
        return $this->subscription_password;
    }
    public function setSubscriptionPassword($a_value)
    {
        $this->subscription_password = $a_value;
    }
    public function enabledObjectiveView()
    {
        return $this->view_mode == IL_CRS_VIEW_OBJECTIVE;
    }

    public function enabledWaitingList()
    {
        return (bool) $this->waiting_list;
    }

    public function enableWaitingList($a_status)
    {
        $this->waiting_list = (bool) $a_status;
    }

    public function inSubscriptionTime()
    {
        if ($this->getSubscriptionUnlimitedStatus()) {
            return true;
        }
        if (time() > $this->getSubscriptionStart() and time() < $this->getSubscriptionEnd()) {
            return true;
        }
        return false;
    }
    
    /**
     * en/disable limited number of sessions
     * @return
     * @param object $a_status
     */
    public function enableSessionLimit($a_status)
    {
        $this->session_limit = $a_status;
    }
    
    public function isSessionLimitEnabled()
    {
        return (bool) $this->session_limit;
    }
    
    /**
     * enable max members
     *
     * @access public
     * @param bool status
     * @return
     */
    public function enableSubscriptionMembershipLimitation($a_status)
    {
        $this->subscription_membership_limitation = $a_status;
    }

    /**
     * Set number of previous sessions
     * @return
     * @param int $a_num
     */
    public function setNumberOfPreviousSessions($a_num)
    {
        $this->session_prev = $a_num;
    }
    
    /**
     * Set number of previous sessions
     * @return
     */
    public function getNumberOfPreviousSessions()
    {
        return $this->session_prev;
    }
    
    /**
     * Set number of previous sessions
     * @return
     * @param int $a_num
     */
    public function setNumberOfNextSessions($a_num)
    {
        $this->session_next = $a_num;
    }
    
    /**
     * Set number of previous sessions
     * @return
     */
    public function getNumberOfNextSessions()
    {
        return $this->session_next;
    }
    /**
     * is membership limited
     *
     * @access public
     * @param
     * @return
     */
    public function isSubscriptionMembershipLimited()
    {
        return (bool) $this->subscription_membership_limitation;
    }

    public function getSubscriptionMaxMembers()
    {
        return $this->subscription_max_members;
    }
    public function setSubscriptionMaxMembers($a_value)
    {
        $this->subscription_max_members = $a_value;
    }
    
    /**
     * Check if subscription notification is enabled
     *
     * @access public
     * @static
     *
     * @param int course_id
     */
    public static function _isSubscriptionNotificationEnabled($a_course_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM crs_settings " .
            "WHERE obj_id = " . $ilDB->quote($a_course_id, 'integer') . " " .
            "AND sub_notify = 1";
        $res = $ilDB->query($query);
        return $res->numRows() ? true : false;
    }
    
    /**
     * Get subitems of container
     * @param bool $a_admin_panel_enabled[optional]
     * @param bool $a_include_side_block[optional]
     * @return array
     */
    public function getSubItems($a_admin_panel_enabled = false, $a_include_side_block = false, $a_get_single = 0)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $access = $DIC->access();

        // Caching
        if (is_array($this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block])) {
            return $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block];
        }
        
        // Results are stored in $this->items
        parent::getSubItems($a_admin_panel_enabled, $a_include_side_block, $a_get_single);
        
        $limit_sess = false;
        if (!$a_admin_panel_enabled &&
            !$a_include_side_block &&
            $this->items['sess'] &&
            is_array($this->items['sess']) &&
            $this->isSessionLimitEnabled() &&
            $this->getViewMode() == ilContainer::VIEW_SESSIONS) { // #16686
            $limit_sess = true;
        }
        
        if (!$limit_sess) {
            return $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block];
        }
                
        
        // do session limit
    
        // @todo move to gui class
        if (isset($_GET['crs_prev_sess'])) {
            $ilUser->writePref('crs_sess_show_prev_' . $this->getId(), (string) (int) $_GET['crs_prev_sess']);
        }
        if (isset($_GET['crs_next_sess'])) {
            $ilUser->writePref('crs_sess_show_next_' . $this->getId(), (string) (int) $_GET['crs_next_sess']);
        }

        $session_rbac_checked = [];
        foreach ($this->items['sess'] as $session_tree_info) {
            if ($access->checkAccess('visible', '', $session_tree_info['ref_id'])) {
                $session_rbac_checked[] = $session_tree_info;
            }
        }
        $sessions = ilUtil::sortArray($session_rbac_checked, 'start', 'ASC', true, false);
        //$sessions = ilUtil::sortArray($this->items['sess'],'start','ASC',true,false);
        $today = new ilDate(date('Ymd', time()), IL_CAL_DATE);
        $previous = $current = $next = array();
        foreach ($sessions as $key => $item) {
            $start = new ilDateTime($item['start'], IL_CAL_UNIX);
            $end = new ilDateTime($item['end'], IL_CAL_UNIX);
            
            if (ilDateTime::_within($today, $start, $end, IL_CAL_DAY)) {
                $current[] = $item;
            } elseif (ilDateTime::_before($start, $today, IL_CAL_DAY)) {
                $previous[] = $item;
            } elseif (ilDateTime::_after($start, $today, IL_CAL_DAY)) {
                $next[] = $item;
            }
        }
        $num_previous_remove = max(
            count($previous) - $this->getNumberOfPreviousSessions(),
            0
        );
        while ($num_previous_remove--) {
            if (!$ilUser->getPref('crs_sess_show_prev_' . $this->getId())) {
                array_shift($previous);
            }
            $this->items['sess_link']['prev']['value'] = 1;
        }
        
        $num_next_remove = max(
            count($next) - $this->getNumberOfNextSessions(),
            0
        );
        while ($num_next_remove--) {
            if (!$ilUser->getPref('crs_sess_show_next_' . $this->getId())) {
                array_pop($next);
            }
            // @fixme
            $this->items['sess_link']['next']['value'] = 1;
        }
        
        $sessions = array_merge($previous, $current, $next);
        $this->items['sess'] = $sessions;
        
        // #15389 - see ilContainer::getSubItems()
        include_once('Services/Container/classes/class.ilContainerSorting.php');
        $sort = ilContainerSorting::_getInstance($this->getId());
        $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block] = $sort->sortItems($this->items);
        
        return $this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block];
    }
    
    public function getSubscriptionNotify()
    {
        return true;
        return $this->subscription_notify ? true : false;
    }
    public function setSubscriptionNotify($a_value)
    {
        $this->subscription_notify = $a_value ? true : false;
    }

    public function setViewMode($a_mode)
    {
        $this->view_mode = $a_mode;
    }
    public function getViewMode()
    {
        return $this->view_mode;
    }

    /**
     * @param $a_obj_id
     * @return int
     */
    public static function lookupTimingMode($a_obj_id)
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

    /**
     * @param int $a_mode
     */
    public function setTimingMode($a_mode)
    {
        $this->timing_mode = $a_mode;
    }

    /**
     * @return int
     */
    public function getTimingMode()
    {
        return $this->timing_mode;
    }


    /**
     * lookup view mode of container
     * @param int $a_id
     * @return mixed int | bool
     */
    public static function _lookupViewMode($a_id)
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

    public static function _lookupAboStatus($a_id)
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

    public function getArchiveStart()
    {
        return $this->archive_start ? $this->archive_start : time();
    }
    public function setArchiveStart($a_value)
    {
        $this->archive_start = $a_value;
    }
    public function getArchiveEnd()
    {
        return $this->archive_end ? $this->archive_end : mktime(0, 0, 0, 12, 12, date("Y", time()) + 2);
    }
    public function setArchiveEnd($a_value)
    {
        $this->archive_end = $a_value;
    }
    public function getArchiveType()
    {
        return $this->archive_type ? IL_CRS_ARCHIVE_DOWNLOAD : IL_CRS_ARCHIVE_NONE;
    }
    public function setArchiveType($a_value)
    {
        $this->archive_type = $a_value;
    }
    public function setAboStatus($a_status)
    {
        $this->abo = $a_status;
    }
    public function getAboStatus()
    {
        return $this->abo;
    }
    public function setShowMembers($a_status)
    {
        $this->show_members = $a_status;
    }
    public function getShowMembers()
    {
        return $this->show_members;
    }
    
    /**
     * Set mail to members type
     * @see ilCourseConstants
     * @param type $a_type
     */
    public function setMailToMembersType($a_type)
    {
        $this->mail_members = $a_type;
    }
    
    /**
     * Get mail to members type
     * @return int
     */
    public function getMailToMembersType()
    {
        return $this->mail_members;
    }

    public function getMessage()
    {
        return $this->message;
    }
    public function setMessage($a_message)
    {
        $this->message = $a_message;
    }
    public function appendMessage($a_message)
    {
        if ($this->getMessage()) {
            $this->message .= "<br /> ";
        }
        $this->message .= $a_message;
    }

    /**
     * Check if course is active and not offline
     * @return bool
     */
    public function isActivated()
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
     *
     * @param int id of user
     * @return boolean
     */
    public static function _isActivated($a_obj_id)
    {
        include_once("./Modules/Course/classes/class.ilObjCourseAccess.php");
        return ilObjCourseAccess::_isActivated($a_obj_id);
    }

    /**
     * Registration enabled? Method is in Access class, since it is needed by Access/ListGUI.
     *
     * @param int id of user
     * @return boolean
     */
    public static function _registrationEnabled($a_obj_id)
    {
        include_once("./Modules/Course/classes/class.ilObjCourseAccess.php");
        return ilObjCourseAccess::_registrationEnabled($a_obj_id);
    }


    public function allowAbo()
    {
        return $this->ABO == $this->ABO_ENABLED;
    }

    /**
     *
     */
    public function read()
    {
        parent::read();

        include_once('./Services/Container/classes/class.ilContainerSortingSettings.php');
        $this->setOrderType(ilContainerSortingSettings::_lookupSortMode($this->getId()));

        $this->__readSettings();
    }
    public function create($a_upload = false)
    {
        global $DIC;

        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        
        parent::create($a_upload);

        if (!$a_upload) {
            $this->createMetaData();
        }
        $this->__createDefaultSettings();
        
        $ilAppEventHandler->raise(
            'Modules/Course',
            'create',
            array('object' => $this,
                'obj_id' => $this->getId(),
                'appointments' => $this->prepareAppointments('create'))
        );
    }
    
    /**
    * Set Latitude.
    *
    * @param	string	$a_latitude	Latitude
    */
    public function setLatitude($a_latitude)
    {
        $this->latitude = $a_latitude;
    }

    /**
    * Get Latitude.
    *
    * @return	string	Latitude
    */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
    * Set Longitude.
    *
    * @param	string	$a_longitude	Longitude
    */
    public function setLongitude($a_longitude)
    {
        $this->longitude = $a_longitude;
    }

    /**
    * Get Longitude.
    *
    * @return	string	Longitude
    */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
    * Set LocationZoom.
    *
    * @param	int	$a_locationzoom	LocationZoom
    */
    public function setLocationZoom($a_locationzoom)
    {
        $this->locationzoom = $a_locationzoom;
    }

    /**
    * Get LocationZoom.
    *
    * @return	int	LocationZoom
    */
    public function getLocationZoom()
    {
        return $this->locationzoom;
    }

    /**
    * Set Enable Course Map.
    *
    * @param	boolean	$a_enablemap	Enable Course Map
    */
    public function setEnableCourseMap($a_enablemap)
    {
        $this->enablemap = $a_enablemap;
    }
    
    /**
     * Type independent wrapper
     * @return type
     */
    public function getEnableMap()
    {
        return $this->getEnableCourseMap();
    }

    /**
    * Get Enable Course Map.
    *
    * @return	boolean	Enable Course Map
    */
    public function getEnableCourseMap()
    {
        return $this->enablemap;
    }
    
    public function setCourseStart(ilDate $a_value = null)
    {
        $this->crs_start = $a_value;
    }
    
    public function getCourseStart()
    {
        return $this->crs_start;
    }
    
    public function setCourseEnd(ilDate $a_value = null)
    {
        $this->crs_end = $a_value;
    }
    
    public function getCourseEnd()
    {
        return $this->crs_end;
    }
    
    public function setCancellationEnd(ilDate $a_value = null)
    {
        $this->leave_end = $a_value;
    }
    
    public function getCancellationEnd()
    {
        return $this->leave_end;
    }
    
    public function setSubscriptionMinMembers($a_value)
    {
        if ($a_value !== null) {
            $a_value = (int) $a_value;
        }
        $this->min_members = $a_value;
    }
    
    public function getSubscriptionMinMembers()
    {
        return $this->min_members;
    }
    
    public function setWaitingListAutoFill($a_value)
    {
        $this->auto_fill_from_waiting = (bool) $a_value;
    }
    
    public function hasWaitingListAutoFill()
    {
        return (bool) $this->auto_fill_from_waiting;
    }
    
    /**
     * Clone course (no member data)
     *
     * @access public
     * @param int target ref_id
     * @param int copy id
     *
     */
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];
        $certificateLogger = $DIC->logger()->cert();


        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);

        $this->cloneAutoGeneratedRoles($new_obj);
        $this->cloneMetaData($new_obj);

        // Assign admin
        $new_obj->getMemberObject()->add($ilUser->getId(), IL_CRS_ADMIN);
        // cognos-blu-patch: begin
        $new_obj->getMemberObject()->updateContact($ilUser->getId(), 1);
        // cognos-blu-patch: end
        
            
        // #14596
        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        if ($cwo->isRootNode($this->getRefId())) {
            $this->setOfflineStatus(true);
        }
        
        // Copy settings
        $this->cloneSettings($new_obj);
    
        // Course Defined Fields
        include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
        ilCourseDefinedFieldDefinition::_clone($this->getId(), $new_obj->getId());
        
        // Clone course files
        include_once('Modules/Course/classes/class.ilCourseFile.php');
        ilCourseFile::_cloneFiles($this->getId(), $new_obj->getId());
        
        // Copy learning progress settings
        include_once('Services/Tracking/classes/class.ilLPObjSettings.php');
        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($new_obj->getId());
        unset($obj_settings);
        
        // clone certificate (#11085)
        $factory = new ilCertificateFactory();
        $templateRepository = new ilCertificateTemplateRepository($ilDB);

        $cloneAction = new ilCertificateCloneAction(
            $ilDB,
            $factory,
            $templateRepository,
            $DIC->filesystem()->web(),
            $certificateLogger,
            new ilCertificateObjectHelper()
        );

        $cloneAction->cloneCertificate($this, $new_obj);

        return $new_obj;
    }

    /**
     * Clone object dependencies (start objects, preconditions)
     *
     * @access public
     * @param int target ref id of new course
     * @param int copy id
     *
     */
    public function cloneDependencies($a_target_id, $a_copy_id)
    {
        parent::cloneDependencies($a_target_id, $a_copy_id);
        
        // Clone course start objects
        include_once('Services/Container/classes/class.ilContainerStartObjects.php');
        $start = new ilContainerStartObjects($this->getRefId(), $this->getId());
        $start->cloneDependencies($a_target_id, $a_copy_id);

        // Clone course item settings
        include_once('Services/Object/classes/class.ilObjectActivation.php');
        ilObjectActivation::cloneDependencies($this->getRefId(), $a_target_id, $a_copy_id);
        
        // clone objective settings
        include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
        ilLOSettings::cloneSettings($a_copy_id, $this->getId(), ilObject::_lookupObjId($a_target_id));

        // Clone course learning objectives
        include_once('Modules/Course/classes/class.ilCourseObjective.php');
        $crs_objective = new ilCourseObjective($this);
        $crs_objective->ilClone($a_target_id, $a_copy_id);

        // clone membership limitation
        foreach (\ilObjCourseGrouping::_getGroupings($this->getId()) as $grouping_id) {
            \ilLoggerFactory::getLogger('crs')->info('Handling grouping id: ' . $grouping_id);
            $grouping = new \ilObjCourseGrouping($grouping_id);
            $grouping->cloneGrouping($a_target_id, $a_copy_id);
        }

        return true;
    }
    
    /**
     * Clone automatic genrated roles (permissions and template permissions)
     *
     * @access public
     * @param object new course object
     *
     */
    public function cloneAutoGeneratedRoles($new_obj)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];
        
        $admin = $this->getDefaultAdminRole();
        $new_admin = $new_obj->getDefaultAdminRole();
        
        if (!$admin || !$new_admin || !$this->getRefId() || !$new_obj->getRefId()) {
            $ilLog->write(__METHOD__ . ' : Error cloning auto generated role: il_crs_admin');
        }
        $rbacadmin->copyRolePermissions($admin, $this->getRefId(), $new_obj->getRefId(), $new_admin, true);
        $ilLog->write(__METHOD__ . ' : Finished copying of role crs_admin.');
        
        $tutor = $this->getDefaultTutorRole();
        $new_tutor = $new_obj->getDefaultTutorRole();
        if (!$tutor || !$new_tutor) {
            $ilLog->write(__METHOD__ . ' : Error cloning auto generated role: il_crs_tutor');
        }
        $rbacadmin->copyRolePermissions($tutor, $this->getRefId(), $new_obj->getRefId(), $new_tutor, true);
        $ilLog->write(__METHOD__ . ' : Finished copying of role crs_tutor.');
        
        $member = $this->getDefaultMemberRole();
        $new_member = $new_obj->getDefaultMemberRole();
        if (!$member || !$new_member) {
            $ilLog->write(__METHOD__ . ' : Error cloning auto generated role: il_crs_member');
        }
        $rbacadmin->copyRolePermissions($member, $this->getRefId(), $new_obj->getRefId(), $new_member, true);
        $ilLog->write(__METHOD__ . ' : Finished copying of role crs_member.');
        
        return true;
    }
    

    public function validate()
    {
        $this->setMessage('');

        if (($this->getSubscriptionLimitationType() == IL_CRS_SUBSCRIPTION_LIMITED) and
           $this->getSubscriptionStart() > $this->getSubscriptionEnd()) {
            $this->appendMessage($this->lng->txt("subscription_times_not_valid"));
        }
        if ($this->getSubscriptionType() == IL_CRS_SUBSCRIPTION_PASSWORD and !$this->getSubscriptionPassword()) {
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
        
        // :TODO: checkInput() is not used properly
        if (($this->getCourseStart() && !$this->getCourseEnd()) ||
            (!$this->getCourseStart() && $this->getCourseEnd()) ||
            ($this->getCourseStart() && $this->getCourseEnd() && $this->getCourseStart()->get(IL_CAL_UNIX) > $this->getCourseEnd()->get(IL_CAL_UNIX))) {
            $this->appendMessage($this->lng->txt("crs_course_period_not_valid"));
        }

        return $this->getMessage() ? false : true;
    }

    public function validateInfoSettings()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $error = false;
        if ($this->getContactEmail()) {
            $emails = explode(",", $this->getContactEmail());
            
            foreach ($emails as $email) {
                $email = trim($email);
                if (!(ilUtil::is_email($email) or ilObjUser::getUserIdByLogin($email))) {
                    $ilErr->appendMessage($this->lng->txt('contact_email_not_valid') . " '" . $email . "'");
                    $error = true;
                }
            }
        }
        return !$error;
    }

    public function hasContactData()
    {
        return strlen($this->getContactName()) or
            strlen($this->getContactResponsibility()) or
            strlen($this->getContactEmail()) or
            strlen($this->getContactPhone()) or
            strlen($this->getContactConsultation());
    }
            

    /**
    * delete course and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        global $DIC;

        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // delete meta data
        $this->deleteMetaData();

        // put here course specific stuff

        $this->__deleteSettings();

        include_once('Modules/Course/classes/class.ilCourseParticipants.php');
        ilCourseParticipants::_deleteAllEntries($this->getId());

        include_once './Modules/Course/classes/class.ilCourseObjective.php';
        ilCourseObjective::_deleteAll($this->getId());

        include_once './Modules/Course/classes/class.ilObjCourseGrouping.php';
        ilObjCourseGrouping::_deleteAll($this->getId());

        include_once './Modules/Course/classes/class.ilCourseFile.php';
        ilCourseFile::_deleteByCourse($this->getId());
        
        include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
        ilCourseDefinedFieldDefinition::_deleteByContainer($this->getId());
        
        $ilAppEventHandler->raise(
            'Modules/Course',
            'delete',
            array('object' => $this,
                'obj_id' => $this->getId(),
                'appointments' => $this->prepareAppointments('delete'))
        );
        
        
        return true;
    }


    /**
     * update complete object
     */
    public function update()
    {
        global $DIC;

        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        $ilLog = $DIC->logger()->crs();

        include_once('./Services/Container/classes/class.ilContainerSortingSettings.php');
        $sorting = new ilContainerSortingSettings($this->getId());
        $sorting->setSortMode($this->getOrderType());
        $sorting->update();

        $this->updateMetaData();
        $this->updateSettings();
        parent::update();

        $ilAppEventHandler->raise(
            'Modules/Course',
            'update',
            array('object' => $this,
                'obj_id' => $this->getId(),
                'appointments' => $this->prepareAppointments('update'))
        );
    }

    public function updateSettings()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // Due to a bug 3.5.alpha maybe no settings exist. => create default settings

        $query = "SELECT * FROM crs_settings WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer') . " ";
        $res = $ilDB->query($query);

        if (!$res->numRows()) {
            $this->__createDefaultSettings();
        }
        
        $query = "UPDATE crs_settings SET " .
            "syllabus = " . $ilDB->quote($this->getSyllabus(), 'text') . ", " .
            "contact_name = " . $ilDB->quote($this->getContactName(), 'text') . ", " .
            "contact_responsibility = " . $ilDB->quote($this->getContactResponsibility(), 'text') . ", " .
            "contact_phone = " . $ilDB->quote($this->getContactPhone(), 'text') . ", " .
            "contact_email = " . $ilDB->quote($this->getContactEmail(), 'text') . ", " .
            "contact_consultation = " . $ilDB->quote($this->getContactConsultation(), 'text') . ", " .
            "activation_type = " . $ilDB->quote(!$this->getOfflineStatus(), 'integer') . ", " .
            "sub_limitation_type = " . $ilDB->quote($this->getSubscriptionLimitationType(), 'integer') . ", " .
            "sub_start = " . $ilDB->quote($this->getSubscriptionStart(), 'integer') . ", " .
            "sub_end = " . $ilDB->quote($this->getSubscriptionEnd(), 'integer') . ", " .
            "sub_type = " . $ilDB->quote($this->getSubscriptionType(), 'integer') . ", " .
            "sub_password = " . $ilDB->quote($this->getSubscriptionPassword(), 'text') . ", " .
            "sub_mem_limit = " . $ilDB->quote((int) $this->isSubscriptionMembershipLimited(), 'integer') . ", " .
            "sub_max_members = " . $ilDB->quote($this->getSubscriptionMaxMembers(), 'integer') . ", " .
            "sub_notify = " . $ilDB->quote($this->getSubscriptionNotify(), 'integer') . ", " .
            "view_mode = " . $ilDB->quote($this->getViewMode(), 'integer') . ", " .
            'timing_mode = ' . $ilDB->quote($this->getTimingMode(), 'integer') . ', ' .
            "abo = " . $ilDB->quote($this->getAboStatus(), 'integer') . ", " .
            "waiting_list = " . $ilDB->quote($this->enabledWaitingList(), 'integer') . ", " .
            "important = " . $ilDB->quote($this->getImportantInformation(), 'text') . ", " .
            "show_members = " . $ilDB->quote($this->getShowMembers(), 'integer') . ", " .
            "show_members_export = " . $ilDB->quote($this->getShowMembersExport(), 'integer') . ", " .
            "latitude = " . $ilDB->quote($this->getLatitude(), 'text') . ", " .
            "longitude = " . $ilDB->quote($this->getLongitude(), 'text') . ", " .
            "location_zoom = " . $ilDB->quote($this->getLocationZoom(), 'integer') . ", " .
            "enable_course_map = " . $ilDB->quote((int) $this->getEnableCourseMap(), 'integer') . ", " .
            'session_limit = ' . $ilDB->quote($this->isSessionLimitEnabled(), 'integer') . ', ' .
            'session_prev = ' . $ilDB->quote($this->getNumberOfPreviousSessions(), 'integer') . ', ' .
            'session_next = ' . $ilDB->quote($this->getNumberOfNextSessions(), 'integer') . ', ' .
            'reg_ac_enabled = ' . $ilDB->quote($this->isRegistrationAccessCodeEnabled(), 'integer') . ', ' .
            'reg_ac = ' . $ilDB->quote($this->getRegistrationAccessCode(), 'text') . ', ' .
            'auto_notification = ' . $ilDB->quote((int) $this->getAutoNotification(), 'integer') . ', ' .
            'status_dt = ' . $ilDB->quote((int) $this->getStatusDetermination()) . ', ' .
            'mail_members_type = ' . $ilDB->quote((int) $this->getMailToMembersType(), 'integer') . ', ' .
            'crs_start = ' . $ilDB->quote(($this->getCourseStart() && !$this->getCourseStart()->isNull()) ? $this->getCourseStart()->get(IL_CAL_UNIX) : null, 'integer') . ', ' .
            'crs_end = ' . $ilDB->quote(($this->getCourseEnd() && !$this->getCourseEnd()->isNull()) ? $this->getCourseEnd()->get(IL_CAL_UNIX) : null, 'integer') . ', ' .
            'auto_wait = ' . $ilDB->quote((int) $this->hasWaitingListAutoFill(), 'integer') . ', ' .
            'leave_end = ' . $ilDB->quote(($this->getCancellationEnd() && !$this->getCancellationEnd()->isNull()) ? $this->getCancellationEnd()->get(IL_CAL_UNIX) : null, 'integer') . ', ' .
            'min_members = ' . $ilDB->quote((int) $this->getSubscriptionMinMembers(), 'integer') . '  ' .
            "WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer') . "";
                
        $res = $ilDB->manipulate($query);
        
        // moved activation to ilObjectActivation
        if ($this->ref_id) {
            include_once "./Services/Object/classes/class.ilObjectActivation.php";
            ilObjectActivation::getItem($this->ref_id);
            
            $item = new ilObjectActivation;
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
    
    /**
     * Clone entries in settings table
     *
     * @access public
     * @param ilObjCourse new course object
     *
     */
    public function cloneSettings($new_obj)
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
        $new_obj->setSubscriptionNotify($this->getSubscriptionNotify());
        $new_obj->setViewMode($this->getViewMode());
        $new_obj->setTimingMode($this->getTimingMode());
        $new_obj->setOrderType($this->getOrderType());
        $new_obj->setAboStatus($this->getAboStatus());
        $new_obj->enableWaitingList($this->enabledWaitingList());
        $new_obj->setImportantInformation($this->getImportantInformation());
        $new_obj->setShowMembers($this->getShowMembers());
        // patch mem_exp
        $new_obj->setShowMembersExport($this->getShowMembersExport());
        // patch mem_exp
        $new_obj->enableSessionLimit($this->isSessionLimitEnabled());
        $new_obj->setNumberOfPreviousSessions($this->getNumberOfPreviousSessions());
        $new_obj->setNumberOfNextSessions($this->getNumberOfNextSessions());
        $new_obj->setAutoNotification($this->getAutoNotification());
        $new_obj->enableRegistrationAccessCode($this->isRegistrationAccessCodeEnabled());
        include_once './Services/Membership/classes/class.ilMembershipRegistrationCodeUtils.php';
        $new_obj->setRegistrationAccessCode(ilMembershipRegistrationCodeUtils::generateCode());
        $new_obj->setStatusDetermination($this->getStatusDetermination());
        $new_obj->setMailToMembersType($this->getMailToMembersType());
        $new_obj->setCourseStart($this->getCourseStart());
        $new_obj->setCourseEnd($this->getCourseEnd());
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

    public function __createDefaultSettings()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        include_once './Services/Membership/classes/class.ilMembershipRegistrationCodeUtils.php';
        $this->setRegistrationAccessCode(ilMembershipRegistrationCodeUtils::generateCode());

        $query = "INSERT INTO crs_settings (obj_id,syllabus,contact_name,contact_responsibility," .
            "contact_phone,contact_email,contact_consultation," .
            "sub_limitation_type,sub_start,sub_end,sub_type,sub_password,sub_mem_limit," .
            "sub_max_members,sub_notify,view_mode,timing_mode,abo," .
            "latitude,longitude,location_zoom,enable_course_map,waiting_list,show_members,show_members_export, " .
            "session_limit,session_prev,session_next, reg_ac_enabled, reg_ac, auto_notification, status_dt,mail_members_type) " .
            "VALUES( " .
            $ilDB->quote($this->getId(), 'integer') . ", " .
            $ilDB->quote($this->getSyllabus(), 'text') . ", " .
            $ilDB->quote($this->getContactName(), 'text') . ", " .
            $ilDB->quote($this->getContactResponsibility(), 'text') . ", " .
            $ilDB->quote($this->getContactPhone(), 'text') . ", " .
            $ilDB->quote($this->getContactEmail(), 'text') . ", " .
            $ilDB->quote($this->getContactConsultation(), 'text') . ", " .
            $ilDB->quote(IL_CRS_SUBSCRIPTION_DEACTIVATED, 'integer') . ", " .
            $ilDB->quote($this->getSubscriptionStart(), 'integer') . ", " .
            $ilDB->quote($this->getSubscriptionEnd(), 'integer') . ", " .
            $ilDB->quote(IL_CRS_SUBSCRIPTION_DIRECT, 'integer') . ", " .
            $ilDB->quote($this->getSubscriptionPassword(), 'text') . ", " .
            "0, " .
            $ilDB->quote($this->getSubscriptionMaxMembers(), 'integer') . ", " .
            "1, " .
            "0, " .
            $ilDB->quote(IL_CRS_VIEW_TIMING_ABSOLUTE, 'integer') . ', ' .
            $ilDB->quote($this->ABO_ENABLED, 'integer') . ", " .
            $ilDB->quote($this->getLatitude(), 'text') . ", " .
            $ilDB->quote($this->getLongitude(), 'text') . ", " .
            $ilDB->quote($this->getLocationZoom(), 'integer') . ", " .
            $ilDB->quote($this->getEnableCourseMap(), 'integer') . ", " .
            #"objective_view = '0', ".
            "1, " .
            "1," .
            '0,' .
            $ilDB->quote($this->isSessionLimitEnabled(), 'integer') . ', ' .
            $ilDB->quote($this->getNumberOfPreviousSessions(), 'integer') . ', ' .
            $ilDB->quote($this->getNumberOfPreviousSessions(), 'integer') . ', ' .
            $ilDB->quote($this->isRegistrationAccessCodeEnabled(), 'integer') . ', ' .
            $ilDB->quote($this->getRegistrationAccessCode(), 'text') . ', ' .
            $ilDB->quote((int) $this->getAutoNotification(), 'integer') . ', ' .
            $ilDB->quote((int) $this->getStatusDetermination(), 'integer') . ', ' .
            $ilDB->quote((int) $this->getMailToMembersType(), 'integer') . ' ' .
            ")";
            
        $res = $ilDB->manipulate($query);
        $this->__readSettings();

        include_once('./Services/Container/classes/class.ilContainerSortingSettings.php');
        $sorting = new ilContainerSortingSettings($this->getId());
        $sorting->setSortMode(ilContainer::SORT_MANUAL);
        $sorting->update();
    }
    

    public function __readSettings()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM crs_settings WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer') . "";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setSyllabus($row->syllabus);
            $this->setContactName($row->contact_name);
            $this->setContactResponsibility($row->contact_responsibility);
            $this->setContactPhone($row->contact_phone);
            $this->setContactEmail($row->contact_email);
            $this->setContactConsultation($row->contact_consultation);
            $this->setOfflineStatus(!(bool) $row->activation_type); // see below
            $this->setSubscriptionLimitationType($row->sub_limitation_type);
            $this->setSubscriptionStart($row->sub_start);
            $this->setSubscriptionEnd($row->sub_end);
            $this->setSubscriptionType($row->sub_type);
            $this->setSubscriptionPassword($row->sub_password);
            $this->enableSubscriptionMembershipLimitation($row->sub_mem_limit);
            $this->setSubscriptionMaxMembers($row->sub_max_members);
            $this->setSubscriptionNotify($row->sub_notify);
            $this->setViewMode($row->view_mode);
            $this->setTimingMode((int) $row->timing_mode);
            $this->setAboStatus($row->abo);
            $this->enableWaitingList($row->waiting_list);
            $this->setImportantInformation($row->important);
            $this->setShowMembers($row->show_members);

            if (\ilPrivacySettings::_getInstance()->participantsListInCoursesEnabled()) {
                $this->setShowMembersExport($row->show_members_export);
            } else {
                $this->setShowMembersExport(false);
            }
            $this->setLatitude($row->latitude);
            $this->setLongitude($row->longitude);
            $this->setLocationZoom($row->location_zoom);
            $this->setEnableCourseMap($row->enable_course_map);
            $this->enableSessionLimit($row->session_limit);
            $this->setNumberOfPreviousSessions($row->session_prev);
            $this->setNumberOfNextSessions($row->session_next);
            $this->enableRegistrationAccessCode($row->reg_ac_enabled);
            $this->setRegistrationAccessCode($row->reg_ac);
            $this->setAutoNotification($row->auto_notification == 1 ? true : false);
            $this->setStatusDetermination((int) $row->status_dt);
            $this->setMailToMembersType($row->mail_members_type);
            $this->setCourseStart($row->crs_start ? new ilDate($row->crs_start, IL_CAL_UNIX) : null);
            $this->setCourseEnd($row->crs_end ? new ilDate($row->crs_end, IL_CAL_UNIX) : null);
            $this->setCancellationEnd($row->leave_end ? new ilDate($row->leave_end, IL_CAL_UNIX) : null);
            $this->setWaitingListAutoFill($row->auto_wait);
            $this->setSubscriptionMinMembers($row->min_members ? $row->min_members : null);
        }
        
        // moved activation to ilObjectActivation
        if ($this->ref_id) {
            include_once "./Services/Object/classes/class.ilObjectActivation.php";
            $activation = ilObjectActivation::getItem($this->ref_id);
            switch ($activation["timing_type"]) {
                case ilObjectActivation::TIMINGS_ACTIVATION:
                    $this->setActivationStart($activation["timing_start"]);
                    $this->setActivationEnd($activation["timing_end"]);
                    $this->setActivationVisibility($activation["visible"]);
                    break;
        }
        }
        return true;
    }

    public function initWaitingList()
    {
        include_once "./Modules/Course/classes/class.ilCourseWaitingList.php";

        if (!is_object($this->waiting_list_obj)) {
            $this->waiting_list_obj = new ilCourseWaitingList($this->getId());
        }
        return true;
    }
        

    /**
     * Init course member object
     * @global ilObjUser $ilUser
     * @return <type>
     */
    protected function initCourseMemberObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        include_once "./Modules/Course/classes/class.ilCourseParticipant.php";
        $this->member_obj = ilCourseParticipant::_getInstanceByObjId($this->getId(), $ilUser->getId());
        return true;
    }

    /**
     * Init course member object
     * @global ilObjUser $ilUser
     * @return <type>
     */
    protected function initCourseMembersObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        include_once "./Modules/Course/classes/class.ilCourseParticipants.php";
        $this->members_obj = ilCourseParticipants::_getInstanceByObjId($this->getId());
        return true;
    }

    /**
     * Get course member object
     * @return ilCourseParticipant
     */
    public function getMemberObject()
    {
        if (!$this->member_obj instanceof ilCourseParticipant) {
            $this->initCourseMemberObject();
        }
        return $this->member_obj;
    }

    /**
     * @return ilCourseParticipants
     */
    public function getMembersObject()
    {
        if (!$this->members_obj instanceof ilCourseParticipants) {
            $this->initCourseMembersObject();
        }
        return $this->members_obj;
    }



    // RBAC METHODS
    public function initDefaultRoles()
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];
        $ilDB = $DIC['ilDB'];

        include_once './Services/AccessControl/classes/class.ilObjRole.php';
        $role = ilObjRole::createDefaultRole(
            'il_crs_admin_' . $this->getRefId(),
            "Admin of crs obj_no." . $this->getId(),
            'il_crs_admin',
            $this->getRefId()
        );
        $role = ilObjRole::createDefaultRole(
            'il_crs_tutor_' . $this->getRefId(),
            "Tutor of crs obj_no." . $this->getId(),
            'il_crs_tutor',
            $this->getRefId()
        );
        $role = ilObjRole::createDefaultRole(
            'il_crs_member_' . $this->getRefId(),
            "Member of crs obj_no." . $this->getId(),
            'il_crs_member',
            $this->getRefId()
        );
        
        return array();
    }
    
    /**
     * This method is called before "initDefaultRoles".
     * Therefore now local course roles are created.
     *
     * Grants permissions on the course object for all parent roles.
     * Each permission is granted by computing the intersection of the
     * template il_crs_non_member and the permission template of the parent role.
     * @param type $a_parent_ref
     */
    public function setParentRolePermissions($a_parent_ref)
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];
        
        $parent_roles = $rbacreview->getParentRoleIds($a_parent_ref);
        foreach ((array) $parent_roles as $parent_role) {
            $rbacadmin->initIntersectionPermissions(
                $this->getRefId(),
                $parent_role['obj_id'],
                $parent_role['parent'],
                $this->__getCrsNonMemberTemplateId(),
                ROLE_FOLDER_ID
            );
        }
    }

    /**
    * get course non-member template
    * @access	private
    * @param	return obj_id of roletemplate containing permissionsettings for
    *           non-member roles of a course.
    */
    public function __getCrsNonMemberTemplateId()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_crs_non_member'";
        $res = $this->ilias->db->query($q);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

        return $row["obj_id"];
    }

    /**
     * Lookup course non member id
     * @return int
     */
    public static function lookupCourseNonMemberTemplatesId()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = 'SELECT obj_id FROM object_data WHERE type = ' . $ilDB->quote('rolt', 'text') . ' AND title = ' . $ilDB->quote('il_crs_non_member', 'text');
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        
        return isset($row['obj_id']) ? $row['obj_id'] : 0;
    }
    
    /**
    * get ALL local roles of course, also those created and defined afterwards
    * only fetch data once from database. info is stored in object variable
    * @access	public
    * @return	return array [title|id] of roles...
    */
    public function getLocalCourseRoles($a_translate = false)
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];

        if (empty($this->local_roles)) {
            $this->local_roles = array();
            $role_arr = $rbacreview->getRolesOfRoleFolder($this->getRefId());

            foreach ($role_arr as $role_id) {
                if ($rbacreview->isAssignable($role_id, $this->getRefId()) == true) {
                    $role_Obj = $this->ilias->obj_factory->getInstanceByObjId($role_id);

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
    * @access	public
    * @param 	returns the obj_ids of course specific roles in an associative
    *           array.
    *			key=descripiton of the role (i.e. "il_crs_tutor", "il_crs_admin", "il_crs_member".
    *			value=obj_id of the role
    */
    public function getDefaultCourseRoles($a_crs_id = "")
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];

        if (strlen($a_crs_id) > 0) {
            $crs_id = $a_crs_id;
        } else {
            $crs_id = $this->getRefId();
        }

        $role_arr = $rbacreview->getRolesOfRoleFolder($crs_id);

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
    
    public function __getLocalRoles()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];

        // GET role_objects of predefined roles
        
        return $rbacreview->getRolesOfRoleFolder($this->getRefId(), false);
    }

    public function __deleteSettings()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM crs_settings " .
            "WHERE obj_id = " . $ilDB->quote($this->getId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        return true;
    }
    
    
    public function getDefaultMemberRole()
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
    public function getDefaultTutorRole()
    {
        $local_roles = $this->__getLocalRoles();

        foreach ($local_roles as $role_id) {
            if ($tmp_role = &ilObjectFactory::getInstanceByObjId($role_id, false)) {
                if (!strcmp($tmp_role->getTitle(), "il_crs_tutor_" . $this->getRefId())) {
                    return $role_id;
                }
            }
        }
        return false;
    }
    public function getDefaultAdminRole()
    {
        $local_roles = $this->__getLocalRoles();

        foreach ($local_roles as $role_id) {
            if ($tmp_role = &ilObjectFactory::getInstanceByObjId($role_id, false)) {
                if (!strcmp($tmp_role->getTitle(), "il_crs_admin_" . $this->getRefId())) {
                    return $role_id;
                }
            }
        }
        return false;
    }

    public static function _deleteUser($a_usr_id)
    {
        // Delete all user related data
        // delete lm_history
        include_once './Modules/Course/classes/class.ilCourseLMHistory.php';
        ilCourseLMHistory::_deleteUser($a_usr_id);

        include_once './Modules/Course/classes/class.ilCourseParticipants.php';
        ilCourseParticipants::_deleteUser($a_usr_id);

        // Course objectives
        include_once "Modules/Course/classes/Objectives/class.ilLOUserResults.php";
        ilLOUserResults::deleteResultsForUser($a_usr_id);
    }
    
    /**
     * Overwriten Metadata update listener for ECS functionalities
     *
     * @access public
     *
     */
    public function MDUpdateListener($a_element)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];

        parent::MDUpdateListener($a_element);

        switch ($a_element) {
            case 'General':
                // Update ecs content
                include_once 'Modules/Course/classes/class.ilECSCourseSettings.php';
                $ecs = new ilECSCourseSettings($this);
                $ecs->handleContentUpdate();
                break;
                
            default:
                return true;
        }
    }
    
    /**
    * Add additional information to sub item, e.g. used in
    * courses for timings information etc.
    */
    public function addAdditionalSubItemInformation(&$a_item_data)
    {
        include_once './Services/Object/classes/class.ilObjectActivation.php';
        ilObjectActivation::addAdditionalSubItemInformation($a_item_data);
    }
    
    /**
     * Prepare calendar appointments
     *
     * @access protected
     * @param string mode UPDATE|CREATE|DELETE
     * @return
     */
    protected function prepareAppointments($a_mode = 'create')
    {
        include_once('./Services/Calendar/classes/class.ilCalendarAppointmentTemplate.php');
        include_once('./Services/Calendar/classes/class.ilDateTime.php');
        
        switch ($a_mode) {
            case 'create':
            case 'update':
                if (!$this->getActivationUnlimitedStatus() and !$this->getOfflineStatus()) {
                    $app = new ilCalendarAppointmentTemplate(self::CAL_ACTIVATION_START);
                    $app->setTitle($this->getTitle());
                    $app->setSubtitle('crs_cal_activation_start');
                    $app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
                    $app->setDescription($this->getLongDescription());
                    $app->setStart(new ilDateTime($this->getActivationStart(), IL_CAL_UNIX));
                    $apps[] = $app;

                    $app = new ilCalendarAppointmentTemplate(self::CAL_ACTIVATION_END);
                    $app->setTitle($this->getTitle());
                    $app->setSubtitle('crs_cal_activation_end');
                    $app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
                    $app->setDescription($this->getLongDescription());
                    $app->setStart(new ilDateTime($this->getActivationEnd(), IL_CAL_UNIX));
                    $apps[] = $app;
                }
                if ($this->getSubscriptionLimitationType() == IL_CRS_SUBSCRIPTION_LIMITED) {
                    $app = new ilCalendarAppointmentTemplate(self::CAL_REG_START);
                    $app->setTitle($this->getTitle());
                    $app->setSubtitle('crs_cal_reg_start');
                    $app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
                    $app->setDescription($this->getLongDescription());
                    $app->setStart(new ilDateTime($this->getSubscriptionStart(), IL_CAL_UNIX));
                    $apps[] = $app;

                    $app = new ilCalendarAppointmentTemplate(self::CAL_REG_END);
                    $app->setTitle($this->getTitle());
                    $app->setSubtitle('crs_cal_reg_end');
                    $app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
                    $app->setDescription($this->getLongDescription());
                    $app->setStart(new ilDateTime($this->getSubscriptionEnd(), IL_CAL_UNIX));
                    $apps[] = $app;
                }
                if ($this->getCourseStart() && $this->getCourseEnd()) {
                    $app = new ilCalendarAppointmentTemplate(self::CAL_COURSE_START);
                    $app->setTitle($this->getTitle());
                    $app->setSubtitle('crs_cal_start');
                    $app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
                    $app->setDescription($this->getLongDescription());
                    $app->setStart($this->getCourseStart());
                    $app->setFullday(true);
                    $apps[] = $app;

                    $app = new ilCalendarAppointmentTemplate(self::CAL_COURSE_END);
                    $app->setTitle($this->getTitle());
                    $app->setSubtitle('crs_cal_end');
                    $app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
                    $app->setDescription($this->getLongDescription());
                    $app->setStart($this->getCourseEnd());
                    $app->setFullday(true);
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
                            $app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
                            $app->setStart(new ilDate($item['suggestion_start'], IL_CAL_UNIX));
                            $app->setFullday(true);
                            $apps[] = $app;

                            $app = new ilCalendarAppointmentTemplate(self::CAL_COURSE_TIMING_END);
                            $app->setContextInfo($item['ref_id']);
                            $app->setTitle($item['title']);
                            $app->setSubtitle('cal_crs_timing_end');
                            $app->setTranslationType(IL_CAL_TRANSLATION_SYSTEM);
                            $app->setStart(new ilDate($item['suggestion_end'], IL_CAL_UNIX));
                            $app->setFullday(true);
                            $apps[] = $app;
                        }
                    }
                }
                return $apps ? $apps : array();
                
            case 'delete':
                // Nothing to do: The category and all assigned appointments will be deleted.
                return array();
        }
    }
    
    ###### Interface ilMembershipRegistrationCodes
    /**
     * @see interface.ilMembershipRegistrationCodes
     * @return array obj ids
     */
    public static function lookupObjectsByCode($a_code)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT obj_id FROM crs_settings " .
            "WHERE reg_ac_enabled = " . $ilDB->quote(1, 'integer') . " " .
            "AND reg_ac = " . $ilDB->quote($a_code, 'text');
        $res = $ilDB->query($query);
        
        $obj_ids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $obj_ids[] = $row->obj_id;
        }
        return $obj_ids;
    }
    
    /**
     * @see ilMembershipRegistrationCodes::register()
     * @param int user_id
     * @param int role
     * @param bool force registration and do not check registration constraints.
     * @throws ilMembershipRegistrationException
     */
    public function register($a_user_id, $a_role = ilCourseConstants::CRS_MEMBER, $a_force_registration = false)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $tree = $DIC['tree'];
        include_once './Services/Membership/exceptions/class.ilMembershipRegistrationException.php';
        include_once "./Modules/Course/classes/class.ilCourseParticipants.php";
        $part = ilCourseParticipants::_getInstanceByObjId($this->getId());

        if ($part->isAssigned($a_user_id)) {
            return true;
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
            
            if ($this->getSubscriptionLimitationType() == IL_CRS_SUBSCRIPTION_DEACTIVATED) {
                if (!ilObjCourseAccess::_usingRegistrationCode()) {
                    throw new ilMembershipRegistrationException('Cant registrate to course ' . $this->getId() .
                        ', course subscription is deactivated.', ilMembershipRegistrationException::REGISTRATION_CODE_DISABLED);
                }
            }

            // Time Limitation
            if ($this->getSubscriptionLimitationType() == IL_CRS_SUBSCRIPTION_LIMITED) {
                if (!$this->inSubscriptionTime()) {
                    throw new ilMembershipRegistrationException('Cant registrate to course ' . $this->getId() .
                        ', course is out of registration time.', ilMembershipRegistrationException::OUT_OF_REGISTRATION_PERIOD);
                }
            }

            // Max members
            if ($this->isSubscriptionMembershipLimited()) {
                $free = max(0, $this->getSubscriptionMaxMembers() - $part->getCountMembers());
                include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
                $waiting_list = new ilCourseWaitingList($this->getId());
                if ($this->enabledWaitingList() and (!$free or $waiting_list->getCountUsers())) {
                    $waiting_list->addToList($a_user_id);
                    $this->lng->loadLanguageModule("crs");
                    $info = sprintf(
                        $this->lng->txt('crs_added_to_list'),
                        $waiting_list->getPosition($a_user_id)
                    );
                    include_once('./Modules/Course/classes/class.ilCourseParticipants.php');
                    $participants = ilCourseParticipants::_getInstanceByObjId($this->getId());
                    $participants->sendNotification($participants->NOTIFY_WAITING_LIST, $a_user_id);

                    throw new ilMembershipRegistrationException($info, ilMembershipRegistrationException::ADDED_TO_WAITINGLIST);
                }

                if (!$this->enabledWaitingList() && !$free) {
                    throw new ilMembershipRegistrationException('Cant registrate to course ' . $this->getId() .
                        ', membership is limited.', ilMembershipRegistrationException::OBJECT_IS_FULL);
                }
            }
        }
        
        $part->add($a_user_id, $a_role);
        $part->sendNotification($part->NOTIFY_ACCEPT_USER, $a_user_id);
        $part->sendNotification($part->NOTIFY_ADMINS, $a_user_id);
        
        
        include_once './Modules/Forum/classes/class.ilForumNotification.php';
        ilForumNotification::checkForumsExistsInsert($this->getRefId(), $a_user_id);
        
        return true;
    }

    /**
     * Returns automatic notification status from
     * $this->auto_notification
     *
     * @return boolean
     */
    public function getAutoNotification()
    {
        return $this->auto_notification;
    }


    /**
     * Sets automatic notification status in $this->auto_notification,
     * using given $status.
     *
     * @param mixed boolean
     */
    public function setAutoNotification($value)
    {
        $this->auto_notification = $value;
    }
    
    /**
     * Set status determination mode
     *
     * @param int $a_value
     */
    public function setStatusDetermination($a_value)
    {
        $a_value = (int) $a_value;
        
        // #13905
        if ($a_value == self::STATUS_DETERMINATION_LP) {
            include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
            if (!ilObjUserTracking::_enabledLearningProgress()) {
                $a_value = self::STATUS_DETERMINATION_MANUAL;
            }
        }
        
        $this->status_dt = $a_value;
    }
    
    /**
     * Get status determination mode
     *
     * @return int
     */
    public function getStatusDetermination()
    {
        return $this->status_dt;
    }
        
    /**
     * Set course status for all members by lp status
     */
    public function syncMembersStatusWithLP()
    {
        include_once "Services/Tracking/classes/class.ilLPStatusWrapper.php";
        foreach ($this->getMembersObject()->getParticipants() as $user_id) {
            // #15529 - force raise on sync
            ilLPStatusWrapper::_updateStatus($this->getId(), $user_id, null, false, true);
        }
    }
            
    /**
     * sync course status from lp
     *
     * as lp data is not deleted on course exit new members may already have lp completed
     *
     * @param int $a_member_id
     */
    public function checkLPStatusSync($a_member_id)
    {
        // #11113
        include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
        if (ilObjUserTracking::_enabledLearningProgress() &&
            $this->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP) {
            include_once("Services/Tracking/classes/class.ilLPStatus.php");
            // #13811 - we need to suppress creation if status entry
            $has_completed = (ilLPStatus::_lookupStatus($this->getId(), $a_member_id, false) == ilLPStatus::LP_STATUS_COMPLETED_NUM);
            $this->getMembersObject()->updatePassed($a_member_id, $has_completed, false, true);
        }
    }
    
    public function getOrderType()
    {
        if ($this->enabledObjectiveView()) {
            return ilContainer::SORT_MANUAL;
        }
        return parent::getOrderType();
    }
    
    /**
     * Handle course auto fill
     */
    public function handleAutoFill()
    {
        if (
            !$this->enabledWaitingList() or
            !$this->hasWaitingListAutoFill()
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
        include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
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
            $this->getMembersObject()->add($user_id, IL_CRS_MEMBER);
            $this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_ACCEPT_USER, $user_id, true);
            $waiting_list->removeFromList($user_id);
            $this->checkLPStatusSync($user_id);
            
            $this->course_logger->info('Assigned user from waiting list to course: ' . $this->getTitle());
            $now++;
            if ($now >= $max) {
                break;
            }
        }
    }
    
    public static function mayLeave($a_course_id, $a_user_id = null, &$a_date = null)
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
    
    /**
     * Minimum members check
     * @global type $ilDB
     * @return array
     */
    public static function findCoursesWithNotEnoughMembers()
    {
        $ilDB = $GLOBALS['DIC']->database();
        $tree = $GLOBALS['DIC']->repositoryTree();
        
        $res = array();

        $before = new ilDateTime(time(), IL_CAL_UNIX);
        $before->increment(IL_CAL_DAY, -1);
        $now = $before->get(IL_CAL_UNIX);

        include_once "Modules/Course/classes/class.ilCourseParticipants.php";
        
        $set = $ilDB->query("SELECT obj_id, min_members" .
            " FROM crs_settings" .
            " WHERE min_members > " . $ilDB->quote(0, "integer") .
            " AND sub_mem_limit = " . $ilDB->quote(1, "integer") . // #17206
            " AND ((leave_end IS NOT NULL" .
                " AND leave_end < " . $ilDB->quote($now, "text") . ")" .
                " OR (leave_end IS NULL" .
                " AND sub_end IS NOT NULL" .
                " AND sub_end < " . $ilDB->quote($now, "text") . "))" .
            " AND (crs_start IS NULL OR crs_start > " . $ilDB->quote($now, "integer") . ")");
        while ($row = $ilDB->fetchAssoc($set)) {
            $refs = ilObject::_getAllReferences($row['obj_id']);
            $ref = end($refs);
            
            if ($tree->isDeleted($ref)) {
                continue;
            }
            
            $part = new ilCourseParticipants($row["obj_id"]);
            $reci = $part->getNotificationRecipients();
            if (sizeof($reci)) {
                $missing = (int) $row["min_members"] - $part->getCountMembers();
                if ($missing > 0) {
                    $res[$row["obj_id"]] = array($missing, $reci);
                }
            }
        }
        
        return $res;
    }
} //END class.ilObjCourse
