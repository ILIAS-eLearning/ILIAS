<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
include_once './Services/Membership/classes/class.ilMembershipRegistrationSettings.php';

/**
* @defgroup ModulesSession Modules/Session
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesSession
*/
class ilObjSession extends ilObject
{
    const MAIL_ALLOWED_ALL = 1;
    const MAIL_ALLOWED_ADMIN = 2;

    const LOCAL_ROLE_PARTICIPANT_PREFIX = 'il_sess_participant';
    
    const CAL_REG_START = 1;
    
    protected $db;
    
    protected $location;
    protected $name;
    protected $phone;
    protected $email;
    protected $details;
    protected $registration;
    protected $event_id;
    
    protected $reg_type = ilMembershipRegistrationSettings::TYPE_NONE;
    protected $reg_limited = 0;
    protected $reg_min_users = 0;
    protected $reg_limited_users = 0;
    protected $reg_waiting_list = 0;
    protected $reg_waiting_list_autofill; // [bool]

    /**
     * @var bool
     */
    protected $show_members = false;

    /**
     * @var bool
     */
    protected $show_cannot_participate_option = true;

    /**
     * @var int
     */
    protected $mail_members = self::MAIL_ALLOWED_ADMIN;

    protected $appointments;
    protected $files = array();
    
    /**
     * @var ilLogger
     */
    protected $session_logger = null;

    /**
     * @var \ilSessionParticipants
     */
    protected $members_obj;

    private $registrationNotificationEnabled = false;
    private $notificationOption = ilSessionConstants::NOTIFICATION_INHERIT_OPTION;

    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $this->session_logger = $GLOBALS['DIC']->logger()->sess();

        $this->db = $ilDB;
        $this->type = "sess";
        parent::__construct($a_id, $a_call_by_reference);
    }
    
    /**
     * lookup registration enabled
     *
     * @access public
     * @param
     * @return
     * @static
     */
    public static function _lookupRegistrationEnabled($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT reg_type FROM event " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->reg_type != ilMembershipRegistrationSettings::TYPE_NONE;
        }
        return false;
    }
    
    /**
     * Get session data
     * @param object $a_obj_id
     * @return
     */
    public static function lookupSession($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT * FROM event " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id);
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $data['location'] = $row->location ? $row->location : '';
            $data['details'] = $row->details ? $row->details : '';
            $data['name'] = $row->tutor_name ? $row->tutor_name : '';
            $data['email'] = $row->tutor_email ? $row->tutor_email : '';
            $data['phone'] = $row->tutor_phone ? $row->tutor_phone : '';
        }
        return (array) $data;
    }
    
    /**
     * get title
     * (overwritten from base class)
     *
     * @access public
     * @return
     */
    public function getPresentationTitle()
    {
        $date = new ilDate($this->getFirstAppointment()->getStart()->getUnixTime(), IL_CAL_UNIX);
        if ($this->getTitle()) {
            return ilDatePresentation::formatDate($this->getFirstAppointment()->getStart()) . ': ' . $this->getTitle();
        } else {
            return ilDatePresentation::formatDate($date);
        }
    }

    /**
     * @return string
     */
    public function getPresentationTitleAppointmentPeriod()
    {
        $title = '';
        if ($this->getTitle()) {
            $title = ': ' . $this->getTitle();
        }
        return ilDatePresentation::formatPeriod(
            $this->getFirstAppointment()->getStart(),
            $this->getFirstAppointment()->getEnd()
        ) . $title;
    }

    /**
     * Create local session participant role
     */
    public function initDefaultRoles()
    {
        include_once './Services/AccessControl/classes/class.ilObjRole.php';
        $role = ilObjRole::createDefaultRole(
            self::LOCAL_ROLE_PARTICIPANT_PREFIX . '_' . $this->getRefId(),
            'Participant of session obj_no.' . $this->getId(),
            self::LOCAL_ROLE_PARTICIPANT_PREFIX,
            $this->getRefId()
        );
        
        if (!$role instanceof ilObjRole) {
            $this->session_logger->warning('Could not create default session role.');
            $this->session_logger->logStack(ilLogLevel::WARNING);
        }
        return array();
    }
    
    /**
     * sget event id
     *
     * @access public
     * @return
     */
    public function getEventId()
    {
        return $this->event_id;
    }
    
    /**
     * set location
     *
     * @access public
     * @param string location
     */
    public function setLocation($a_location)
    {
        $this->location = $a_location;
    }
    
    /**
     * get location
     *
     * @access public
     * @return string location
     */
    public function getLocation()
    {
        return $this->location;
    }
    
    /**
     * set name
     *
     * @access public
     * @param string name
     */
    public function setName($a_name)
    {
        $this->name = $a_name;
    }
    
    /**
     * get name
     *
     * @access public
     * @return string name
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * set phone
     *
     * @access public
     * @param string phone
     */
    public function setPhone($a_phone)
    {
        $this->phone = $a_phone;
    }
    
    /**
     * get phone
     *
     * @access public
     * @return string phone
     */
    public function getPhone()
    {
        return $this->phone;
    }
    
    /**
     * set email
     *
     * @access public
     * @param string email
     * @return
     */
    public function setEmail($a_email)
    {
        $this->email = $a_email;
    }
    
    /**
     * get email
     *
     * @access public
     * @return string email
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * check if there any tutor settings
     *
     * @access public
     */
    public function hasTutorSettings()
    {
        return strlen($this->getName()) or
            strlen($this->getEmail()) or
            strlen($this->getPhone());
    }
    
    
    /**
     * set details
     *
     * @access public
     * @param string details
     */
    public function setDetails($a_details)
    {
        $this->details = $a_details;
    }
    
    /**
     * get details
     *
     * @access public
     * @return string details
     */
    public function getDetails()
    {
        return $this->details;
    }
    
    public function setRegistrationType($a_type)
    {
        $this->reg_type = $a_type;
    }
    
    public function getRegistrationType()
    {
        return $this->reg_type;
    }
    
    public function isRegistrationUserLimitEnabled()
    {
        return $this->reg_limited;
    }
    
    public function enableRegistrationUserLimit($a_limit)
    {
        $this->reg_limited = $a_limit;
    }
    
    public function getRegistrationMinUsers()
    {
        return $this->reg_min_users;
    }
    
    public function setRegistrationMinUsers($a_users)
    {
        $this->reg_min_users = $a_users;
    }
    
    public function getRegistrationMaxUsers()
    {
        return $this->reg_limited_users;
    }
    
    public function setRegistrationMaxUsers($a_users)
    {
        $this->reg_limited_users = $a_users;
    }
    
    public function isRegistrationWaitingListEnabled()
    {
        return $this->reg_waiting_list;
    }
    
    public function enableRegistrationWaitingList($a_stat)
    {
        $this->reg_waiting_list = $a_stat;
    }
    
    public function setWaitingListAutoFill($a_value)
    {
        $this->reg_waiting_list_autofill = (bool) $a_value;
    }
    
    public function hasWaitingListAutoFill()
    {
        return (bool) $this->reg_waiting_list_autofill;
    }

    /**
     * Show members gallery
     * @param $a_status
     */
    public function setShowMembers($a_status)
    {
        $this->show_members = (bool) $a_status;
    }

    /**
     * Member gallery enabled
     * @return bool
     */
    public function getShowMembers()
    {
        return (bool) $this->show_members;
    }

    /**
     * @return bool
     */
    public function isRegistrationNotificationEnabled()
    {
        return (bool) $this->registrationNotificationEnabled;
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setRegistrationNotificationEnabled($value)
    {
        return $this->registrationNotificationEnabled = $value;
    }

    /**
     * @return string
     */
    public function getRegistrationNotificationOption()
    {
        return $this->notificationOption;
    }

    /**
     * @param $value
     */
    public function setRegistrationNotificationOption($value)
    {
        $this->notificationOption = $value;
    }

    /**
     * is registration enabled
     *
     * @access public
     * @return
     */
    public function enabledRegistration()
    {
        return $this->reg_type != ilMembershipRegistrationSettings::TYPE_NONE;
    }

    /**
     * @return bool
     */
    public function enabledRegistrationForUsers() : bool
    {
        return
            $this->reg_type != ilMembershipRegistrationSettings::TYPE_NONE &&
            $this->reg_type != ilMembershipRegistrationSettings::TYPE_TUTOR;
    }

    public function isCannotParticipateOptionEnabled() : bool
    {
        return $this->show_cannot_participate_option;
    }

    /**
     * @param bool $status
     */
    public function enableCannotParticipateOption(bool $status) : void
    {
        $this->show_cannot_participate_option = $status;
    }
    
    /**
     * get appointments
     *
     * @access public
     * @return array
     */
    public function getAppointments()
    {
        return $this->appointments ? $this->appointments : array();
    }
    
    /**
     * add appointment
     *
     * @access public
     * @param object ilSessionAppointment
     * @return
     */
    public function addAppointment($appointment)
    {
        $this->appointments[] = $appointment;
    }
    
    /**
     * set appointments
     *
     * @access public
     * @param array ilSessionAppointments
     * @return
     */
    public function setAppointments($appointments)
    {
        $this->appointments = $appointments;
    }

    /**
     * get first appointment
     *
     * @access public
     * @return  ilSessionAppointment
     */
    public function getFirstAppointment()
    {
        return is_object($this->appointments[0]) ? $this->appointments[0] : ($this->appointments[0] = new ilSessionAppointment());
    }
    
    /**
     * get files
     *
     * @access public
     * @param
     * @return
     */
    public function getFiles()
    {
        return $this->files ? $this->files : array();
    }


    /**
     * Set mail to members type
     * @param int $a_type
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


    /**
     * validate
     *
     * @access public
     * @param
     * @return bool
     */
    public function validate()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        
        // #17114
        if ($this->isRegistrationUserLimitEnabled() &&
            !$this->getRegistrationMaxUsers()) {
            $ilErr->appendMessage($this->lng->txt("sess_max_members_needed"));
            return false;
        }
        
        return true;
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
        /**
         * @var ilObjSession
         */
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);

        $dtpl = ilDidacticTemplateObjSettings::lookupTemplateId($this->getRefId());
        $new_obj->applyDidacticTemplate($dtpl);

        $this->read();
        
        $this->cloneSettings($new_obj);
        $this->cloneMetaData($new_obj);
        
        
        // Clone appointment
        $new_app = $this->getFirstAppointment()->cloneObject($new_obj->getId());
        $new_obj->setAppointments(array($new_app));
        $new_obj->update(true);

        // Clone session files
        foreach ($this->files as $file) {
            $file->cloneFiles($new_obj->getEventId());
        }
        
        // Raise update forn new appointments
        
        
    
        // Copy learning progress settings
        include_once('Services/Tracking/classes/class.ilLPObjSettings.php');
        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($new_obj->getId());
        unset($obj_settings);
        
        return $new_obj;
    }
    
    /**
     * clone settings
     *
     * @access public
     * @param ilObjSession
     * @return
     */
    public function cloneSettings(ilObjSession $new_obj)
    {
        ilContainer::_writeContainerSetting(
            $new_obj->getId(),
            ilObjectServiceSettingsGUI::CUSTOM_METADATA,
            ilContainer::_lookupContainerSetting(
                $this->getId(),
                ilObjectServiceSettingsGUI::CUSTOM_METADATA
            )
        );

        // @var ilObjSession $new_obj
        $new_obj->setLocation($this->getLocation());
        $new_obj->setName($this->getName());
        $new_obj->setPhone($this->getPhone());
        $new_obj->setEmail($this->getEmail());
        $new_obj->setDetails($this->getDetails());
        
        $new_obj->setRegistrationType($this->getRegistrationType());
        $new_obj->enableRegistrationUserLimit($this->isRegistrationUserLimitEnabled());
        $new_obj->enableRegistrationWaitingList($this->isRegistrationWaitingListEnabled());
        $new_obj->setWaitingListAutoFill($this->hasWaitingListAutoFill());
        $new_obj->setRegistrationMinUsers($this->getRegistrationMinUsers());
        $new_obj->setRegistrationMaxUsers($this->getRegistrationMaxUsers());
        $new_obj->setShowMembers($this->getShowMembers());
        $new_obj->setMailToMembersType($this->getMailToMembersType());

        $new_obj->setRegistrationNotificationEnabled($this->isRegistrationNotificationEnabled());
        $new_obj->setRegistrationNotificationOption($this->getRegistrationNotificationOption());
        $new_obj->enableCannotParticipateOption($this->isCannotParticipateOptionEnabled());

        $new_obj->update(true);
        
        return true;
    }
    
    /**
     * Clone dependencies
     *
     * @param int target id ref_id of new session
     * @param int copy_id
     * @return
     */
    public function cloneDependencies($a_target_id, $a_copy_id)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        parent::cloneDependencies($a_target_id, $a_copy_id);

        $target_obj_id = $ilObjDataCache->lookupObjId($a_target_id);
        
        include_once('./Modules/Session/classes/class.ilEventItems.php');
        $session_materials = new ilEventItems($target_obj_id);
        $session_materials->cloneItems($this->getId(), $a_copy_id);

        return true;
    }
    
    
    
    /**
     * create new session
     *
     * @access public
     */
    public function create($a_skip_meta_data = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        global $DIC;

        $ilAppEventHandler = $DIC['ilAppEventHandler'];
    
        parent::create();
        
        if (!$a_skip_meta_data) {
            $this->createMetaData();
        }

        $next_id = $ilDB->nextId('event');
        $query = "INSERT INTO event (event_id,obj_id,location,tutor_name,tutor_phone,tutor_email,details,registration, " .
            'reg_type, reg_limit_users, reg_limited, reg_waiting_list, reg_min_users, reg_auto_wait,show_members,mail_members,
			reg_notification, notification_opt, show_cannot_part) ' .
            "VALUES( " .
            $ilDB->quote($next_id, 'integer') . ", " .
            $this->db->quote($this->getId(), 'integer') . ", " .
            $this->db->quote($this->getLocation(), 'text') . "," .
            $this->db->quote($this->getName(), 'text') . ", " .
            $this->db->quote($this->getPhone(), 'text') . ", " .
            $this->db->quote($this->getEmail(), 'text') . ", " .
            $this->db->quote($this->getDetails(), 'text') . "," .
            $this->db->quote($this->enabledRegistrationForUsers(), 'integer') . ", " .
            $this->db->quote($this->getRegistrationType(), 'integer') . ', ' .
            $this->db->quote($this->getRegistrationMaxUsers(), 'integer') . ', ' .
            $this->db->quote($this->isRegistrationUserLimitEnabled(), 'integer') . ', ' .
            $this->db->quote($this->isRegistrationWaitingListEnabled(), 'integer') . ', ' .
            $this->db->quote($this->getRegistrationMinUsers(), 'integer') . ', ' .
            $this->db->quote($this->hasWaitingListAutoFill(), 'integer') . ', ' .
            $this->db->quote($this->getShowMembers(), 'integer') . ', ' .
            $this->db->quote($this->getMailToMembersType(), 'integer') . ',' .
            $this->db->quote($this->isRegistrationNotificationEnabled(), 'integer') . ', ' .
            $this->db->quote($this->getRegistrationNotificationOption(), 'text') . ', ' .
            $this->db->quote($this->isCannotParticipateOptionEnabled(), ilDBConstants::T_INTEGER) . ' ' .
            ")";
        $res = $ilDB->manipulate($query);
        $this->event_id = $next_id;
        
        $ilAppEventHandler->raise(
            'Modules/Session',
            'create',
            array('object' => $this,
                'obj_id' => $this->getId(),
                'appointments' => $this->prepareCalendarAppointments('create'))
        );

        return $this->getId();
    }
    
    /**
     * update object
     *
     * @access public
     * @param
     * @return bool success
     */
    public function update($a_skip_meta_update = false)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        global $DIC;

        $ilAppEventHandler = $DIC['ilAppEventHandler'];

        if (!parent::update()) {
            return false;
        }
        if (!$a_skip_meta_update) {
            $this->updateMetaData();
        }

        $query = "UPDATE event SET " .
            "location = " . $this->db->quote($this->getLocation(), 'text') . "," .
            "tutor_name = " . $this->db->quote($this->getName(), 'text') . ", " .
            "tutor_phone = " . $this->db->quote($this->getPhone(), 'text') . ", " .
            "tutor_email = " . $this->db->quote($this->getEmail(), 'text') . ", " .
            "details = " . $this->db->quote($this->getDetails(), 'text') . ", " .
            "registration = " . $this->db->quote($this->enabledRegistrationForUsers(), 'integer') . ", " .
            "reg_type = " . $this->db->quote($this->getRegistrationType(), 'integer') . ", " .
            "reg_limited = " . $this->db->quote($this->isRegistrationUserLimitEnabled(), 'integer') . ", " .
            "reg_limit_users = " . $this->db->quote($this->getRegistrationMaxUsers(), 'integer') . ", " .
            "reg_min_users = " . $this->db->quote($this->getRegistrationMinUsers(), 'integer') . ", " .
            "reg_waiting_list = " . $this->db->quote($this->isRegistrationWaitingListEnabled(), 'integer') . ", " .
            "reg_auto_wait = " . $this->db->quote($this->hasWaitingListAutoFill(), 'integer') . ", " .
            'show_members = ' . $this->db->quote($this->getShowMembers(), 'integer') . ', ' .
            'mail_members = ' . $this->db->quote($this->getMailToMembersType(), 'integer') . ', ' .
            "reg_auto_wait = " . $this->db->quote($this->hasWaitingListAutoFill(), 'integer') . ", " .
            "reg_notification = " . $this->db->quote($this->isRegistrationNotificationEnabled(), 'integer') . ", " .
            "notification_opt = " . $this->db->quote($this->getRegistrationNotificationOption(), 'text') . ", " .
            'show_cannot_part = ' . $this->db->quote($this->isCannotParticipateOptionEnabled(), ilDBConstants::T_INTEGER) . ' ' .
            "WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);
        
        $ilAppEventHandler->raise(
            'Modules/Session',
            'update',
            array('object' => $this,
                'obj_id' => $this->getId(),
                'appointments' => $this->prepareCalendarAppointments('update'))
        );
        return true;
    }
    
    /**
     * delete session and all related data
     *
     * @access public
     * @return bool
     */
    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        global $DIC;

        $ilAppEventHandler = $DIC['ilAppEventHandler'];
        
        if (!parent::delete()) {
            return false;
        }
        
        // delete meta data
        $this->deleteMetaData();
        
        $query = "DELETE FROM event " .
            "WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);
        
        include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
        ilSessionAppointment::_deleteBySession($this->getId());
        
        include_once('./Modules/Session/classes/class.ilEventItems.php');
        ilEventItems::_delete($this->getId());
        
        include_once('./Modules/Session/classes/class.ilEventParticipants.php');
        ilEventParticipants::_deleteByEvent($this->getId());
        
        foreach ($this->getFiles() as $file) {
            $file->delete();
        }
        
        $ilAppEventHandler->raise(
            'Modules/Session',
            'delete',
            array('object' => $this,
                'obj_id' => $this->getId(),
                'appointments' => $this->prepareCalendarAppointments('delete'))
        );
        
        
        return true;
    }
    
    /**
     * read session data
     *
     * @access public
     * @param
     * @return
     */
    public function read()
    {
        parent::read();
        
        $query = "SELECT * FROM event WHERE " .
            "obj_id = " . $this->db->quote($this->getId(), 'integer') . " ";
        $res = $this->db->query($query);
        
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setLocation($row->location);
            $this->setName($row->tutor_name);
            $this->setPhone($row->tutor_phone);
            $this->setEmail($row->tutor_email);
            $this->setDetails($row->details);
            $this->setRegistrationType($row->reg_type);
            $this->enableRegistrationUserLimit($row->reg_limited);
            $this->enableRegistrationWaitingList($row->reg_waiting_list);
            $this->setWaitingListAutoFill($row->reg_auto_wait);
            $this->setRegistrationMaxUsers($row->reg_limit_users);
            $this->setRegistrationMinUsers($row->reg_min_users);
            $this->setShowMembers((bool) $row->show_members);
            $this->setMailToMembersType((int) $row->mail_members);
            $this->setRegistrationNotificationEnabled($row->reg_notification);
            $this->setRegistrationNotificationOption($row->notification_opt);
            $this->enableCannotParticipateOption((bool) $row->show_cannot_part);
            $this->event_id = $row->event_id;
        }

        $this->initAppointments();
        $this->initFiles();
    }
    
    /**
     * init appointments
     *
     * @access protected
     * @param
     * @return
     */
    protected function initAppointments()
    {
        // get assigned appointments
        include_once('./Modules/Session/classes/class.ilSessionAppointment.php');
        $this->appointments = ilSessionAppointment::_readAppointmentsBySession($this->getId());
    }
    
    /**
     * init files
     *
     * @access protected
     * @param
     * @return
     */
    public function initFiles()
    {
        include_once('./Modules/Session/classes/class.ilSessionFile.php');
        /**
         * BT 31608: ilSessionFile is an unused remnant of a previous refactoring.
         *  It will be removed in R9, and as a minimally invasive performance
         *  fix disabled in R7 and R8.
         */
        //$this->files = ilSessionFile::_readFilesByEvent($this->getEventId());
    }
    
    
    /**
     * Prepare calendar appointments
     *
     * @access public
     * @param string mode UPDATE|CREATE|DELETE
     * @return
     */
    public function prepareCalendarAppointments($a_mode = 'create')
    {
        include_once('./Services/Calendar/classes/class.ilCalendarAppointmentTemplate.php');
        
        switch ($a_mode) {
            case 'create':
            case 'update':

                $app = new ilCalendarAppointmentTemplate(self::CAL_REG_START);
                $app->setTranslationType(IL_CAL_TRANSLATION_NONE);
                $app->setTitle($this->getTitle() ? $this->getTitle() : $this->lng->txt('obj_sess'));
                $app->setDescription($this->getLongDescription());
                $app->setLocation($this->getLocation());
                
                $sess_app = $this->getFirstAppointment();
                $app->setFullday($sess_app->isFullday());
                $app->setStart($sess_app->getStart());
                $app->setEnd($sess_app->getEnd());
                $apps[] = $app;

                return $apps;
                
            case 'delete':
                // Nothing to do: The category and all assigned appointments will be deleted.
                return array();
        }
    }
    
    /**
     * Handle auto fill for session members
     */
    public function handleAutoFill()
    {
        if (
            !$this->isRegistrationWaitingListEnabled() ||
            !$this->hasWaitingListAutoFill()
        ) {
            $this->session_logger->debug('Waiting list or auto fill is disabled.');
            return true;
        }
        
        $parts = ilSessionParticipants::_getInstanceByObjId($this->getId());
        $current = $parts->getCountParticipants();
        $max = $this->getRegistrationMaxUsers();
        
        if ($max <= $current) {
            $this->session_logger->debug('Maximum number of participants not reached.');
            $this->session_logger->debug('Maximum number of members: ' . $max);
            $this->session_logger->debug('Current number of members: ' . $current);
            return true;
        }
        
        $session_waiting_list = new ilSessionWaitingList($this->getId());
        foreach ($session_waiting_list->getUserIds() as $user_id) {
            $user = ilObjectFactory::getInstanceByObjId($user_id);
            if (!$user instanceof ilObjUser) {
                $this->session_logger->warning('Found invalid user id on waiting list: ' . $user_id);
                continue;
            }
            if (in_array($user_id, $parts->getParticipants())) {
                $this->session_logger->notice('User on waiting list already session member: ' . $user_id);
                continue;
            }
            
            if ($this->enabledRegistrationForUsers()) {
                $this->session_logger->debug('Registration enabled: register user');
                $parts->register($user_id);
                $parts->sendNotification(
                    ilSessionMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER,
                    $user_id
                );
            } else {
                $this->session_logger->debug('Registration disabled: set user status to participated.');
                $parts->getEventParticipants()->updateParticipation($user_id, true);
                $parts->sendNotification(
                    ilSessionMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER,
                    $user_id
                );
            }
            
            $session_waiting_list->removeFromList($user_id);
            
            $current++;
            if ($current >= $max) {
                break;
            }
        }
    }
    

    /**
     * init participants object
     *
     *
     * @access protected
     * @return
     */
    protected function initParticipants()
    {
        $this->members_obj = ilSessionParticipants::_getInstanceByObjId($this->getId());
    }
    
    /**
     * Get members objects
     *
     * @return  \ilSessionParticipants
     */
    public function getMembersObject()
    {
        if (!$this->members_obj instanceof ilSessionParticipants) {
            $this->initParticipants();
        }
        return $this->members_obj;
    }

    /**
     * ALways disabled
     * @return bool
     */
    public function getEnableMap()
    {
        return false;
    }
}
