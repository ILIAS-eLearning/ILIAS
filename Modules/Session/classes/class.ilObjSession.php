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
 ********************************************************************
 */

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
    public const MAIL_ALLOWED_ALL = 1;
    public const MAIL_ALLOWED_ADMIN = 2;
    public const LOCAL_ROLE_PARTICIPANT_PREFIX = 'il_sess_participant';
    public const CAL_REG_START = 1;

    protected ilLogger $session_logger;
    protected ilObjectDataCache $obj_data_cache;
    protected ilAppEventHandler $event_handler;
    protected string $location = "";
    protected string $name = "";
    protected string $phone = "";
    protected string $email = "";
    protected string $details = "";
    protected int $event_id = 0;
    protected int $reg_type = ilMembershipRegistrationSettings::TYPE_NONE;
    protected int $reg_limited = 0;
    protected int $reg_min_users = 0;
    protected int $reg_limited_users = 0;
    protected bool $reg_waiting_list = false;
    protected bool $reg_waiting_list_autofill = false;
    protected bool $show_members = false;
    protected bool $show_cannot_participate_option = true;
    protected int $mail_members = self::MAIL_ALLOWED_ADMIN;
    protected array $appointments = [];
    protected array $files = [];
    protected ?ilSessionParticipants $members_obj = null;
    protected bool $registrationNotificationEnabled = false;
    protected string $notificationOption = ilSessionConstants::NOTIFICATION_INHERIT_OPTION;

    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        global $DIC;

        $this->session_logger = $DIC->logger()->root();
        $this->obj_data_cache = $DIC['ilObjDataCache'];
        $this->event_handler = $DIC->event();

        $this->type = "sess";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public static function _lookupRegistrationEnabled(int $a_obj_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT reg_type FROM event " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->reg_type != ilMembershipRegistrationSettings::TYPE_NONE;
        }
        return false;
    }

    public static function lookupSession(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $query = "SELECT * FROM event " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);
        $data = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $data['location'] = $row->location ?: '';
            $data['details'] = $row->details ?: '';
            $data['name'] = $row->tutor_name ?: '';
            $data['email'] = $row->tutor_email ?: '';
            $data['phone'] = $row->tutor_phone ?: '';
        }
        return $data;
    }

    public function getPresentationTitle() : string
    {
        $date = new ilDate($this->getFirstAppointment()->getStart()->getUnixTime(), IL_CAL_UNIX);
        if ($this->getTitle()) {
            return ilDatePresentation::formatDate($this->getFirstAppointment()->getStart()) . ': ' . $this->getTitle();
        } else {
            return ilDatePresentation::formatDate($date);
        }
    }

    public function getPresentationTitleAppointmentPeriod() : string
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
    public function initDefaultRoles() : void
    {
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
    }

    public function getEventId() : int
    {
        return $this->event_id;
    }

    public function setLocation(string $a_location) : void
    {
        $this->location = $a_location;
    }

    public function getLocation() : string
    {
        return $this->location;
    }

    public function setName(string $a_name) : void
    {
        $this->name = $a_name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setPhone(string $a_phone) : void
    {
        $this->phone = $a_phone;
    }

    public function getPhone() : string
    {
        return $this->phone;
    }

    public function setEmail(string $a_email) : void
    {
        $this->email = $a_email;
    }

    public function getEmail() : string
    {
        return $this->email;
    }

    public function hasTutorSettings() : bool
    {
        return strlen($this->getName()) ||
            strlen($this->getEmail()) ||
            strlen($this->getPhone());
    }

    public function setDetails(string $a_details) : void
    {
        $this->details = $a_details;
    }

    public function getDetails() : string
    {
        return $this->details;
    }
    
    public function setRegistrationType(int $a_type) : void
    {
        $this->reg_type = $a_type;
    }
    
    public function getRegistrationType() : int
    {
        return $this->reg_type;
    }
    
    public function isRegistrationUserLimitEnabled() : int
    {
        return $this->reg_limited;
    }
    
    public function enableRegistrationUserLimit(int $a_limit) : void
    {
        $this->reg_limited = $a_limit;
    }
    
    public function getRegistrationMinUsers() : int
    {
        return $this->reg_min_users;
    }
    
    public function setRegistrationMinUsers(int $a_users) : void
    {
        $this->reg_min_users = $a_users;
    }
    
    public function getRegistrationMaxUsers() : int
    {
        return $this->reg_limited_users;
    }
    
    public function setRegistrationMaxUsers(int $a_users) : void
    {
        $this->reg_limited_users = $a_users;
    }
    
    public function isRegistrationWaitingListEnabled() : bool
    {
        return $this->reg_waiting_list;
    }
    
    public function enableRegistrationWaitingList(bool $a_stat) : void
    {
        $this->reg_waiting_list = $a_stat;
    }
    
    public function setWaitingListAutoFill(bool $a_value) : void
    {
        $this->reg_waiting_list_autofill = $a_value;
    }
    
    public function hasWaitingListAutoFill() : bool
    {
        return $this->reg_waiting_list_autofill;
    }

    public function setShowMembers(bool $a_status) : void
    {
        $this->show_members = $a_status;
    }

    public function getShowMembers() : bool
    {
        return $this->show_members;
    }

    public function isRegistrationNotificationEnabled() : bool
    {
        return $this->registrationNotificationEnabled;
    }

    public function setRegistrationNotificationEnabled(bool $value) : void
    {
        $this->registrationNotificationEnabled = $value;
    }

    public function getRegistrationNotificationOption() : string
    {
        return $this->notificationOption;
    }

    public function setRegistrationNotificationOption(string $value) : void
    {
        $this->notificationOption = $value;
    }

    public function enabledRegistration() : bool
    {
        return $this->reg_type != ilMembershipRegistrationSettings::TYPE_NONE;
    }

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

    public function enableCannotParticipateOption(bool $status) : void
    {
        $this->show_cannot_participate_option = $status;
    }

    public function getAppointments() : array
    {
        return $this->appointments;
    }

    public function addAppointment(ilSessionAppointment $appointment) : void
    {
        $this->appointments[] = $appointment;
    }
    
    /**
     * @param ilSessionAppointment[]
     */
    public function setAppointments(array $appointments) : void
    {
        $this->appointments = $appointments;
    }

    public function getFirstAppointment() : ilSessionAppointment
    {
        $app = $this->appointments[0] ?? null;
        return is_object($app) ? $app : ($this->appointments[0] = new ilSessionAppointment());
    }

    public function getFiles() : array
    {
        return $this->files;
    }

    public function setMailToMembersType(int $a_type) : void
    {
        $this->mail_members = $a_type;
    }

    public function getMailToMembersType() : int
    {
        return $this->mail_members;
    }

    public function validate() : bool
    {
        $ilErr = $this->error;
        
        // #17114
        if ($this->isRegistrationUserLimitEnabled() &&
            !$this->getRegistrationMaxUsers()) {
            $ilErr->appendMessage($this->lng->txt("sess_max_members_needed"));
            return false;
        }
        
        return true;
    }

    public function cloneObject(int $a_target_id, int $a_copy_id = 0, bool $a_omit_tree = false) : ?ilObjSession
    {
        /**
         * @var ilObjSession $new_obj
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
        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($new_obj->getId());
        unset($obj_settings);
        
        return $new_obj;
    }

    public function cloneSettings(ilObjSession $new_obj) : bool
    {
        ilContainer::_writeContainerSetting(
            $new_obj->getId(),
            ilObjectServiceSettingsGUI::CUSTOM_METADATA,
            ilContainer::_lookupContainerSetting(
                $this->getId(),
                ilObjectServiceSettingsGUI::CUSTOM_METADATA
            )
        );

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

    public function cloneDependencies($a_target_id, $a_copy_id) : bool
    {
        $ilObjDataCache = $this->obj_data_cache;
        
        parent::cloneDependencies($a_target_id, $a_copy_id);

        $target_obj_id = $ilObjDataCache->lookupObjId($a_target_id);

        $session_materials = new ilEventItems($target_obj_id);
        $session_materials->cloneItems($this->getId(), $a_copy_id);

        return true;
    }

    public function create(bool $a_skip_meta_data = false) : int
    {
        $ilDB = $this->db;
        $ilAppEventHandler = $this->event_handler;
    
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
            $this->db->quote((int) $this->enabledRegistrationForUsers(), 'integer') . ", " .
            $this->db->quote($this->getRegistrationType(), 'integer') . ', ' .
            $this->db->quote($this->getRegistrationMaxUsers(), 'integer') . ', ' .
            $this->db->quote($this->isRegistrationUserLimitEnabled(), 'integer') . ', ' .
            $this->db->quote((int) $this->isRegistrationWaitingListEnabled(), 'integer') . ', ' .
            $this->db->quote($this->getRegistrationMinUsers(), 'integer') . ', ' .
            $this->db->quote((int) $this->hasWaitingListAutoFill(), 'integer') . ', ' .
            $this->db->quote((int) $this->getShowMembers(), 'integer') . ', ' .
            $this->db->quote($this->getMailToMembersType(), 'integer') . ',' .
            $this->db->quote((int) $this->isRegistrationNotificationEnabled(), 'integer') . ', ' .
            $this->db->quote($this->getRegistrationNotificationOption(), 'text') . ', ' .
            $this->db->quote((int) $this->isCannotParticipateOptionEnabled(), ilDBConstants::T_INTEGER) . ' ' .
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

    public function update(bool $a_skip_meta_update = false) : bool
    {
        $ilDB = $this->db;
        $ilAppEventHandler = $this->event_handler;

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
            "registration = " . $this->db->quote((int) $this->enabledRegistrationForUsers(), 'integer') . ", " .
            "reg_type = " . $this->db->quote($this->getRegistrationType(), 'integer') . ", " .
            "reg_limited = " . $this->db->quote($this->isRegistrationUserLimitEnabled(), 'integer') . ", " .
            "reg_limit_users = " . $this->db->quote($this->getRegistrationMaxUsers(), 'integer') . ", " .
            "reg_min_users = " . $this->db->quote($this->getRegistrationMinUsers(), 'integer') . ", " .
            "reg_waiting_list = " . $this->db->quote((int) $this->isRegistrationWaitingListEnabled(), 'integer') . ", " .
            "reg_auto_wait = " . $this->db->quote((int) $this->hasWaitingListAutoFill(), 'integer') . ", " .
            'show_members = ' . $this->db->quote((int) $this->getShowMembers(), 'integer') . ', ' .
            'mail_members = ' . $this->db->quote($this->getMailToMembersType(), 'integer') . ', ' .
            "reg_auto_wait = " . $this->db->quote((int) $this->hasWaitingListAutoFill(), 'integer') . ", " .
            "reg_notification = " . $this->db->quote((int) $this->isRegistrationNotificationEnabled(), 'integer') . ", " .
            "notification_opt = " . $this->db->quote($this->getRegistrationNotificationOption(), 'text') . ", " .
            'show_cannot_part = ' . $this->db->quote((int) $this->isCannotParticipateOptionEnabled(), ilDBConstants::T_INTEGER) . ' ' .
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

    public function delete() : bool
    {
        $ilDB = $this->db;
        $ilAppEventHandler = $this->event_handler;
        
        if (!parent::delete()) {
            return false;
        }
        
        // delete meta data
        $this->deleteMetaData();
        
        $query = "DELETE FROM event " .
            "WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . " ";
        $res = $ilDB->manipulate($query);

        ilSessionAppointment::_deleteBySession($this->getId());
        ilEventItems::_delete($this->getId());
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

    public function read() : void
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
            $this->setRegistrationType((int) $row->reg_type);
            $this->enableRegistrationUserLimit((int) $row->reg_limited);
            $this->enableRegistrationWaitingList((bool) $row->reg_waiting_list);
            $this->setWaitingListAutoFill((bool) $row->reg_auto_wait);
            $this->setRegistrationMaxUsers((int) $row->reg_limit_users);
            $this->setRegistrationMinUsers((int) $row->reg_min_users);
            $this->setShowMembers((bool) $row->show_members);
            $this->setMailToMembersType((int) $row->mail_members);
            $this->setRegistrationNotificationEnabled((bool) $row->reg_notification);
            $this->setRegistrationNotificationOption($row->notification_opt);
            $this->enableCannotParticipateOption((bool) $row->show_cannot_part);
            $this->event_id = (int) $row->event_id;
        }

        $this->initAppointments();
        $this->initFiles();
    }

    protected function initAppointments() : void
    {
        // get assigned appointments
        $this->appointments = ilSessionAppointment::_readAppointmentsBySession($this->getId());
    }

    public function initFiles() : void
    {
        $this->files = ilSessionFile::_readFilesByEvent($this->getEventId());
    }
    
    
    /**
     * @param string $a_mode UPDATE|CREATE|DELETE
     * @return ilCalendarAppointmentTemplate[]|array
     */
    public function prepareCalendarAppointments(string $a_mode = 'create') : array
    {
        switch ($a_mode) {
            case 'create':
            case 'update':

                $app = new ilCalendarAppointmentTemplate(self::CAL_REG_START);
                $app->setTranslationType(ilCalendarEntry::TRANSLATION_NONE);
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
                return [];
        }

        return [];
    }

    public function handleAutoFill() : bool
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
            } else {
                $this->session_logger->debug('Registration disabled: set user status to participated.');
                $parts->getEventParticipants()->updateParticipation($user_id, true);
            }
            $parts->sendNotification(
                ilSessionMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER,
                $user_id
            );

            $session_waiting_list->removeFromList($user_id);
            
            $current++;
            if ($current >= $max) {
                break;
            }
        }

        return true;
    }

    protected function initParticipants() : void
    {
        $this->members_obj = ilSessionParticipants::_getInstanceByObjId($this->getId());
    }

    public function getMembersObject() : ilSessionParticipants
    {
        if (!$this->members_obj instanceof ilSessionParticipants) {
            $this->initParticipants();
        }
        return $this->members_obj;
    }

    public function getEnableMap() : bool
    {
        return false;
    }
}
