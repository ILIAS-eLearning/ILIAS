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
* Class ilObjGroup
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id$
*
* @extends ilObject
*/
class ilObjGroup extends ilContainer implements ilMembershipRegistrationCodes
{
    public const CAL_REG_START = 1;
    public const CAL_REG_END = 2;
    public const CAL_START = 3;
    public const CAL_END = 4;

    public const GRP_MEMBER = 1;
    public const GRP_ADMIN = 2;

    public const ERR_MISSING_TITLE = 'msg_no_title';
    public const ERR_MISSING_GROUP_TYPE = 'grp_missing_grp_type';
    public const ERR_MISSING_PASSWORD = 'grp_missing_password';
    public const ERR_WRONG_MAX_MEMBERS = 'grp_wrong_max_members';
    public const ERR_WRONG_REG_TIME_LIMIT = 'grp_wrong_reg_time_limit';
    public const ERR_MISSING_MIN_MAX_MEMBERS = 'grp_wrong_min_max_members';
    public const ERR_WRONG_MIN_MAX_MEMBERS = 'grp_max_and_min_members_invalid';
    public const ERR_WRONG_REGISTRATION_LIMITED = 'grp_err_registration_limited';

    public const MAIL_ALLOWED_ALL = 1;
    public const MAIL_ALLOWED_TUTORS = 2;

    public $SHOW_MEMBERS_ENABLED = 1;
    public $SHOW_MEMBERS_DISABLED = 0;

    private string $information = '';
    private int $group_status = 0;
    private int $group_type = ilGroupConstants::GRP_TYPE_UNKNOWN;
    private int $reg_type = ilGroupConstants::GRP_REGISTRATION_DIRECT;
    private bool $reg_unlimited = true;
    private ?ilDateTime $reg_start = null;
    private ?ilDateTime $reg_end = null;
    private string $reg_password = '';
    private bool $reg_membership_limitation = false;
    private int $reg_min_members = 0;
    private int $reg_max_members = 0;
    private bool $waiting_list = false;
    private bool $auto_fill_from_waiting = false;
    private ?ilDate $leave_end = null;
    private bool $show_members = true;
    private bool $session_limit = false;
    private int $session_prev = -1;
    private int $session_next = -1;
    private bool $start_time_indication = false;
    private ?ilDateTime $grp_start = null;
    private ?ilDateTime $grp_end = null;
    private bool $auto_notification = true;
    private string $latitude = '';
    private string $longitude = '';
    private int $locationzoom = 0;
    private bool $enablemap = false;
    private string $reg_access_code = '';
    private bool $reg_access_code_enabled = false;
    private int $view_mode = ilContainer::VIEW_DEFAULT;
    private int $mail_members = self::MAIL_ALLOWED_ALL;

    public ?ilGroupParticipants $members_obj;


    public $m_roleMemberId;

    public $m_roleAdminId;

    private ilLogger $logger;

    private string $message = '';


    /**
     * @inheritDoc
    */
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        global $DIC;

        $tree = $DIC['tree'];

        $this->tree = &$tree;

        $this->type = "grp";
        parent::__construct($a_id, $a_call_by_reference);

        $this->logger = $DIC->logger()->grp();
    }

    public static function lookupGroupTye(int $a_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT grp_type FROM grp_settings " .
            "WHERE obj_id = " . $ilDB->quote($a_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->grp_type;
        }
        return ilGroupConstants::GRP_TYPE_UNKNOWN;
    }

    public function setInformation(string $a_information) : void
    {
        $this->information = $a_information;
    }

    public function getInformation() : string
    {
        return $this->information;
    }

    public function setGroupType(int $a_type) : void
    {
        $this->group_type = $a_type;
    }

    public function getGroupType() : int
    {
        return $this->group_type;
    }

    public function setRegistrationType(int $a_type) : void
    {
        $this->reg_type = $a_type;
    }

    public function getRegistrationType() : int
    {
        return $this->reg_type;
    }

    public function isRegistrationEnabled() : bool
    {
        return $this->getRegistrationType() != ilGroupConstants::GRP_REGISTRATION_DEACTIVATED;
    }

    public function enableUnlimitedRegistration(bool $a_status) : void
    {
        $this->reg_unlimited = $a_status;
    }

    public function isRegistrationUnlimited() : bool
    {
        return $this->reg_unlimited;
    }

    public function setRegistrationStart(?ilDateTime $a_start) : void
    {
        $this->reg_start = $a_start;
    }

    public function getRegistrationStart() : ?ilDateTime
    {
        return $this->reg_start;
    }


    public function setRegistrationEnd(?ilDateTime $a_end) : void
    {
        $this->reg_end = $a_end;
    }

    public function getRegistrationEnd() : ?ilDateTime
    {
        return $this->reg_end;
    }

    public function setPassword(string $a_pass) : void
    {
        $this->reg_password = $a_pass;
    }

    public function getPassword() : string
    {
        return $this->reg_password;
    }

    public function enableMembershipLimitation(bool $a_status) : void
    {
        $this->reg_membership_limitation = $a_status;
    }

    public function isMembershipLimited() : bool
    {
        return $this->reg_membership_limitation;
    }

    public function setMinMembers(int $a_max) : void
    {
        $this->reg_min_members = $a_max;
    }

    public function getMinMembers() : int
    {
        return $this->reg_min_members;
    }

    public function setMaxMembers(int $a_max) : void
    {
        $this->reg_max_members = $a_max;
    }

    public function getMaxMembers() : int
    {
        return $this->reg_max_members;
    }

    public function enableWaitingList(bool $a_status) : void
    {
        $this->waiting_list = $a_status;
    }

    public function isWaitingListEnabled() : bool
    {
        return $this->waiting_list;
    }

    public function setWaitingListAutoFill(bool $a_value) : void
    {
        $this->auto_fill_from_waiting = $a_value;
    }

    public function hasWaitingListAutoFill() : bool
    {
        return $this->auto_fill_from_waiting;
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

    public function setEnableGroupMap(bool $a_enablemap) : void
    {
        $this->enablemap = $a_enablemap;
    }

    public function getEnableMap() : bool
    {
        return $this->getEnableGroupMap();
    }

    public function getEnableGroupMap() : bool
    {
        return $this->enablemap;
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

    public function setMailToMembersType(int $a_type) : void
    {
        $this->mail_members = $a_type;
    }

    public function getMailToMembersType() : int
    {
        return $this->mail_members;
    }

    public function setCancellationEnd(?ilDate $a_value) : void
    {
        $this->leave_end = $a_value;
    }

    public function getCancellationEnd() : ?ilDate
    {
        return $this->leave_end;
    }

    public function setShowMembers(bool $a_status) : void
    {
        $this->show_members = $a_status;
    }
    public function getShowMembers() : bool
    {
        return $this->show_members;
    }

    public function setAutoNotification(bool $a_status) : void
    {
        $this->auto_notification = $a_status;
    }

    public function getAutoNotification() : bool
    {
        return $this->auto_notification;
    }

    public function setPeriod(?\ilDateTime $start = null, ?\ilDateTime $end = null) : void
    {
        if (
            ($start instanceof \ilDate && !$end instanceof ilDate) ||
            ($end instanceof \ilDate && !$start instanceof ilDate)
        ) {
            throw new InvalidArgumentException('Different date types not supported.');
        }

        if ($start instanceof \ilDate) {
            $this->toggleStartTimeIndication(false);
        } else {
            $this->toggleStartTimeIndication(true);
        }
        $this->setStart($start);
        $this->setEnd($end);
    }

    protected function toggleStartTimeIndication(bool $time_indication) : void
    {
        $this->start_time_indication = $time_indication;
    }

    public function getStartTimeIndication() : bool
    {
        return $this->start_time_indication;
    }


    protected function setStart(ilDateTime $a_value = null) : void
    {
        $this->grp_start = $a_value;
    }

    public function getStart() : ?\ilDateTime
    {
        return $this->grp_start;
    }

    protected function setEnd(ilDateTime $a_value = null) : void
    {
        $this->grp_end = $a_value;
    }

    public function getEnd() : ?\ilDateTime
    {
        return $this->grp_end;
    }

    public function enableSessionLimit(bool $a_status) : void
    {
        $this->session_limit = $a_status;
    }

    public function isSessionLimitEnabled() : bool
    {
        return $this->session_limit;
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


    /**
     * validate group settings
     */
    public function validate() : bool
    {
        if (!$this->getTitle()) {
            $this->title = '';
            $this->error->appendMessage($this->lng->txt(self::ERR_MISSING_TITLE));
        }
        if ($this->getRegistrationType() == ilGroupConstants::GRP_REGISTRATION_PASSWORD and !strlen($this->getPassword())) {
            $this->error->appendMessage($this->lng->txt(self::ERR_MISSING_PASSWORD));
        }
        if ($this->isMembershipLimited()) {
            if ($this->getMinMembers() <= 0 && $this->getMaxMembers() <= 0) {
                $this->error->appendMessage($this->lng->txt(self::ERR_MISSING_MIN_MAX_MEMBERS));
            }
            if ($this->getMaxMembers() <= 0 && $this->isWaitingListEnabled()) {
                $this->error->appendMessage($this->lng->txt(self::ERR_WRONG_MAX_MEMBERS));
            }
            if ($this->getMaxMembers() > 0 && $this->getMinMembers() > $this->getMaxMembers()) {
                $this->error->appendMessage($this->lng->txt(self::ERR_WRONG_MIN_MAX_MEMBERS));
            }
        }
        if (
            ($this->getRegistrationStart() && !$this->getRegistrationEnd()) ||
            (!$this->getRegistrationStart() && $this->getRegistrationEnd()) ||
            $this->getRegistrationEnd() <= $this->getRegistrationStart()
        ) {
            $this->error->appendMessage($this->lng->txt((self::ERR_WRONG_REGISTRATION_LIMITED)));
        }
        return strlen($this->error->getMessage()) == 0;
    }




    /**
    * @inheritDoc
    */
    public function create() : int
    {
        if (!parent::create()) {
            return 0;
        }
        $this->createMetaData();

        $query = "INSERT INTO grp_settings (obj_id,information,grp_type,registration_type,registration_enabled," .
            "registration_unlimited,registration_start,registration_end,registration_password,registration_mem_limit," .
            "registration_max_members,waiting_list,latitude,longitude,location_zoom,enablemap,reg_ac_enabled,reg_ac,view_mode,mail_members_type," .
            "leave_end,registration_min_members,auto_wait, grp_start, grp_end, auto_notification, session_limit, session_prev, session_next) " .
            "VALUES(" .
            $this->db->quote($this->getId(), 'integer') . ", " .
            $this->db->quote($this->getInformation(), 'text') . ", " .
            $this->db->quote($this->getGroupType(), 'integer') . ", " .
            $this->db->quote($this->getRegistrationType(), 'integer') . ", " .
            $this->db->quote(($this->isRegistrationEnabled() ? 1 : 0), 'integer') . ", " .
            $this->db->quote(($this->isRegistrationUnlimited() ? 1 : 0), 'integer') . ", " .
            $this->db->quote(($this->getRegistrationStart() && !$this->getRegistrationStart()->isNull()) ? $this->getRegistrationStart()->get(IL_CAL_DATETIME, '') : null, 'timestamp') . ", " .
            $this->db->quote(($this->getRegistrationEnd() && !$this->getRegistrationEnd()->isNull()) ? $this->getRegistrationEnd()->get(IL_CAL_DATETIME, '') : null, 'timestamp') . ", " .
            $this->db->quote($this->getPassword(), 'text') . ", " .
            $this->db->quote((int) $this->isMembershipLimited(), 'integer') . ", " .
            $this->db->quote($this->getMaxMembers(), 'integer') . ", " .
            $this->db->quote($this->isWaitingListEnabled() ? 1 : 0, 'integer') . ", " .
            $this->db->quote($this->getLatitude(), 'text') . ", " .
            $this->db->quote($this->getLongitude(), 'text') . ", " .
            $this->db->quote($this->getLocationZoom(), 'integer') . ", " .
            $this->db->quote((int) $this->getEnableGroupMap(), 'integer') . ", " .
            $this->db->quote($this->isRegistrationAccessCodeEnabled(), 'integer') . ', ' .
            $this->db->quote($this->getRegistrationAccessCode(), 'text') . ', ' .
            $this->db->quote($this->view_mode, 'integer') . ', ' .
            $this->db->quote($this->getMailToMembersType(), 'integer') . ', ' .
            $this->db->quote(($this->getCancellationEnd() && !$this->getCancellationEnd()->isNull()) ? $this->getCancellationEnd()->get(IL_CAL_UNIX) : null, 'integer') . ', ' .
            $this->db->quote($this->getMinMembers(), 'integer') . ', ' .
            $this->db->quote($this->hasWaitingListAutoFill(), 'integer') . ', ' .
            $this->db->quote($this->getStart() instanceof ilDate ? $this->getStart()->get(IL_CAL_UNIX) : null, 'integer') . ', ' .
            $this->db->quote($this->getEnd() instanceof ilDate ? $this->getEnd()->get(IL_CAL_UNIX) : null, 'integer') . ', ' .
            $this->db->quote($this->getAutoNotification(), \ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote($this->isSessionLimitEnabled(), ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote($this->getNumberOfPreviousSessions(), ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote($this->getNumberOfNextSessions(), ilDBConstants::T_INTEGER) .
            ')';
        $res = $this->db->manipulate($query);

        $this->app_event_handler->raise(
            'Modules/Group',
            'create',
            array('object' => $this,
                'obj_id' => $this->getId(),
                'appointments' => $this->prepareAppointments('create'))
        );
        return $this->getId();
    }

    /**
     * @inheritDoc
    */
    public function update() : bool
    {
        if (!parent::update()) {
            return false;
        }

        $query = "UPDATE grp_settings " .
            "SET information = " . $this->db->quote($this->getInformation(), 'text') . ", " .
            "grp_type = " . $this->db->quote($this->getGroupType(), 'integer') . ", " .
            "registration_type = " . $this->db->quote($this->getRegistrationType(), 'integer') . ", " .
            "registration_enabled = " . $this->db->quote($this->isRegistrationEnabled() ? 1 : 0, 'integer') . ", " .
            "registration_unlimited = " . $this->db->quote($this->isRegistrationUnlimited() ? 1 : 0, 'integer') . ", " .
            "registration_start = " . $this->db->quote(($this->getRegistrationStart() && !$this->getRegistrationStart()->isNull()) ? $this->getRegistrationStart()->get(IL_CAL_DATETIME, '') : null, 'timestamp') . ", " .
            "registration_end = " . $this->db->quote(($this->getRegistrationEnd() && !$this->getRegistrationEnd()->isNull()) ? $this->getRegistrationEnd()->get(IL_CAL_DATETIME, '') : null, 'timestamp') . ", " .
            "registration_password = " . $this->db->quote($this->getPassword(), 'text') . ", " .
//			"registration_membership_limited = ".$this->db->quote((int) $this->isMembershipLimited() ,'integer').", ".
            "registration_mem_limit = " . $this->db->quote((int) $this->isMembershipLimited(), 'integer') . ", " .
            "registration_max_members = " . $this->db->quote($this->getMaxMembers(), 'integer') . ", " .
            "waiting_list = " . $this->db->quote($this->isWaitingListEnabled() ? 1 : 0, 'integer') . ", " .
            "latitude = " . $this->db->quote($this->getLatitude(), 'text') . ", " .
            "longitude = " . $this->db->quote($this->getLongitude(), 'text') . ", " .
            "location_zoom = " . $this->db->quote($this->getLocationZoom(), 'integer') . ", " .
            "enablemap = " . $this->db->quote((int) $this->getEnableGroupMap(), 'integer') . ", " .
            'reg_ac_enabled = ' . $this->db->quote($this->isRegistrationAccessCodeEnabled(), 'integer') . ', ' .
            'reg_ac = ' . $this->db->quote($this->getRegistrationAccessCode(), 'text') . ', ' .
            'view_mode = ' . $this->db->quote($this->view_mode, 'integer') . ', ' .
            'mail_members_type = ' . $this->db->quote($this->getMailToMembersType(), 'integer') . ', ' .
            'leave_end = ' . $this->db->quote(($this->getCancellationEnd() && !$this->getCancellationEnd()->isNull()) ? $this->getCancellationEnd()->get(IL_CAL_UNIX) : null, 'integer') . ', ' .
            "registration_min_members = " . $this->db->quote($this->getMinMembers(), 'integer') . ", " .
            "auto_wait = " . $this->db->quote($this->hasWaitingListAutoFill(), 'integer') . ", " .
            "show_members = " . $this->db->quote((int) $this->getShowMembers(), 'integer') . ", " .
            'period_start = ' . $this->db->quote(\ilCalendarUtil::convertDateToUtcDBTimestamp($this->getStart()), \ilDBConstants::T_TIMESTAMP) . ', ' .
            'period_end = ' . $this->db->quote(\ilCalendarUtil::convertDateToUtcDBTimestamp($this->getEnd()), \ilDBConstants::T_TIMESTAMP) . ', ' .
            'period_time_indication = ' . $this->db->quote($this->getStartTimeIndication() ? 1 : 0, \ilDBConstants::T_INTEGER) . ', ' .
            'auto_notification = ' . $this->db->quote($this->getAutoNotification(), \ilDBConstants::T_INTEGER) . ', ' .
            'session_limit = ' . $this->db->quote($this->isSessionLimitEnabled(), ilDBConstants::T_INTEGER) . ', ' .
            'session_prev = ' . $this->db->quote($this->getNumberOfPreviousSessions(), ilDBConstants::T_INTEGER) . ', ' .
            'session_next = ' . $this->db->quote($this->getNumberOfNextSessions(), ilDBConstants::T_INTEGER) . ' ' .
            "WHERE obj_id = " . $this->db->quote($this->getId(), 'integer');
        $res = $this->db->manipulate($query);

        $this->app_event_handler->raise(
            'Modules/Group',
            'update',
            array('object' => $this,
                'obj_id' => $this->getId(),
                'appointments' => $this->prepareAppointments('update'))
        );
        return true;
    }

    /**
     * @inheritDoc
    */
    public function delete() : bool
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        $query = "DELETE FROM grp_settings " .
            "WHERE obj_id = " . $this->db->quote($this->getId(), 'integer');
        $res = $this->db->manipulate($query);

        ilGroupParticipants::_deleteAllEntries($this->getId());

        $this->app_event_handler->raise(
            'Modules/Group',
            'delete',
            array('object' => $this,
                'obj_id' => $this->getId(),
                'appointments' => $this->prepareAppointments('delete'))
        );
        return true;
    }


    /**
    * @inheritDoc
    */
    public function read() : void
    {
        parent::read();

        $query = "SELECT * FROM grp_settings " .
            "WHERE obj_id = " . $this->db->quote($this->getId(), 'integer');

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setInformation((string) $row->information);
            $this->setGroupType((int) $row->grp_type);
            $this->setRegistrationType((int) $row->registration_type);
            $this->enableUnlimitedRegistration((bool) $row->registration_unlimited);
            $this->setRegistrationStart(new ilDateTime($row->registration_start, IL_CAL_DATETIME));
            $this->setRegistrationEnd(new ilDateTime($row->registration_end, IL_CAL_DATETIME));
            $this->setPassword((string) $row->registration_password);
            $this->enableMembershipLimitation((bool) $row->registration_mem_limit);
            $this->setMaxMembers((int) $row->registration_max_members);
            $this->enableWaitingList((bool) $row->waiting_list);
            $this->setLatitude((string) $row->latitude);
            $this->setLongitude((string) $row->longitude);
            $this->setLocationZoom((int) $row->location_zoom);
            $this->setEnableGroupMap((bool) $row->enablemap);
            $this->enableRegistrationAccessCode((bool) $row->reg_ac_enabled);
            $this->setRegistrationAccessCode($row->reg_ac);
            $this->setViewMode((int) $row->view_mode);
            $this->setMailToMembersType((int) $row->mail_members_type);
            $this->setCancellationEnd($row->leave_end ? new ilDate((int) $row->leave_end, IL_CAL_UNIX) : null);
            $this->setMinMembers((int) $row->registration_min_members);
            $this->setWaitingListAutoFill((bool) $row->auto_wait);
            $this->setShowMembers((bool) $row->show_members);
            $this->setAutoNotification((bool) $row->auto_notification);
            if ($row->period_time_indication) {
                $this->setPeriod(
                    new \ilDateTime($row->period_start, IL_CAL_DATETIME, \ilTimeZone::UTC),
                    new \ilDateTime($row->period_end, IL_CAL_DATETIME, \ilTimeZone::UTC)
                );
            } elseif (!is_null($row->period_start) && !is_null($row->period_end)) {
                $this->setPeriod(
                    new \ilDate($row->period_start, IL_CAL_DATE),
                    new \ilDate($row->period_end, IL_CAL_DATE)
                );
            }
            $this->toggleStartTimeIndication((bool) $row->period_time_indication);
            $this->enableSessionLimit((bool) $row->session_limit);
            $this->setNumberOfPreviousSessions((int) $row->session_prev);
            $this->setNumberOfNextSessions((int) $row->session_next);
        }
        $this->initParticipants();

        // Inherit order type from parent course (if exists)
        $this->setOrderType(ilContainerSortingSettings::_lookupSortMode($this->getId()));
    }

    /**
     * @inheritDoc
     */
    public function cloneObject(int $a_target_id, int $a_copy_id = 0, bool $a_omit_tree = false) : ?ilObject
    {
        /**
         * @var ilObjGroup $new_obj
         */
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);

        // current template
        $current_template = ilDidacticTemplateObjSettings::lookupTemplateId($this->getRefId());
        $new_obj->applyDidacticTemplate($current_template);
        $this->cloneAutoGeneratedRoles($new_obj);
        $this->cloneMetaData($new_obj);

        $new_obj->setRegistrationType($this->getRegistrationType());
        $new_obj->setInformation($this->getInformation());
        $new_obj->setRegistrationStart($this->getRegistrationStart());
        $new_obj->setRegistrationEnd($this->getRegistrationEnd());
        $new_obj->enableUnlimitedRegistration($this->isRegistrationUnlimited());
        $new_obj->setPassword($this->getPassword());
        $new_obj->enableMembershipLimitation($this->isMembershipLimited());
        $new_obj->setMaxMembers($this->getMaxMembers());
        $new_obj->enableWaitingList($this->isWaitingListEnabled());
        $new_obj->setShowMembers($this->getShowMembers());

        // map
        $new_obj->setLatitude($this->getLatitude());
        $new_obj->setLongitude($this->getLongitude());
        $new_obj->setLocationZoom($this->getLocationZoom());
        $new_obj->setEnableGroupMap($this->getEnableGroupMap());
        $new_obj->enableRegistrationAccessCode($this->isRegistrationAccessCodeEnabled());
        $new_obj->setRegistrationAccessCode(ilMembershipRegistrationCodeUtils::generateCode());

        $new_obj->setViewMode($this->view_mode);
        $new_obj->setMailToMembersType($this->getMailToMembersType());

        $new_obj->setCancellationEnd($this->getCancellationEnd());
        $new_obj->setMinMembers($this->getMinMembers());
        $new_obj->setWaitingListAutoFill($this->hasWaitingListAutoFill());
        $new_obj->setPeriod($this->getStart(), $this->getEnd());
        $new_obj->setAutoNotification($this->getAutoNotification());
        $new_obj->enableSessionLimit($this->isSessionLimitEnabled());
        $new_obj->setNumberOfPreviousSessions($this->getNumberOfPreviousSessions());
        $new_obj->setNumberOfNextSessions($this->getNumberOfNextSessions());
        $new_obj->update();

        // #13008 - Group Defined Fields
        ilCourseDefinedFieldDefinition::_clone($this->getId(), $new_obj->getId());

        // Assign user as admin
        $part = ilGroupParticipants::_getInstanceByObjId($new_obj->getId());
        $part->add($this->user->getId(), ilParticipants::IL_GRP_ADMIN);
        $part->updateNotification($this->user->getId(), (bool) $this->setting->get('mail_grp_admin_notification', "1"));
        $part->updateContact($this->user->getId(), true);

        // Copy learning progress settings
        $obj_settings = new ilLPObjSettings($this->getId());
        $obj_settings->cloneSettings($new_obj->getId());
        unset($obj_settings);
        return $new_obj;
    }

    /**
     * @inheritDoc
     */
    public function cloneDependencies(int $a_target_id, int $a_copy_id) : bool
    {
        parent::cloneDependencies($a_target_id, $a_copy_id);

        ilObjectActivation::cloneDependencies($this->getRefId(), $a_target_id, $a_copy_id);

        // clone membership limitation
        foreach (\ilObjCourseGrouping::_getGroupings($this->getId()) as $grouping_id) {
            $this->logger->info('Handling grouping id: ' . $grouping_id);
            $grouping = new \ilObjCourseGrouping($grouping_id);
            $grouping->cloneGrouping($a_target_id, $a_copy_id);
        }
        return true;
    }

    /**
     * Clone group admin and member role permissions
     */
    public function cloneAutoGeneratedRoles(ilObjGroup $new_obj) : void
    {
        $admin = $this->getDefaultAdminRole();
        $new_admin = $new_obj->getDefaultAdminRole();
        if (!$admin || !$new_admin || !$this->getRefId() || !$new_obj->getRefId()) {
            $this->logger->warning('Error cloning auto generated rol: il_grp_admin');
        }
        $this->rbac_admin->copyRolePermissions($admin, $this->getRefId(), $new_obj->getRefId(), $new_admin, true);
        $this->logger->info('Finished copying of role il_grp_admin.');

        $member = $this->getDefaultMemberRole();
        $new_member = $new_obj->getDefaultMemberRole();
        if (!$member || !$new_member) {
            $this->logger->warning('Error cloning auto generated rol: il_grp_member');
        }
        $this->rbac_admin->copyRolePermissions($member, $this->getRefId(), $new_obj->getRefId(), $new_member, true);
        $this->logger->info('Finished copying of role il_grp_member.');
    }


    /**
    * returns object id of created default member role
    */
    public function getDefaultMemberRole() : int
    {
        $local_group_Roles = $this->getLocalGroupRoles();
        return $local_group_Roles["il_grp_member_" . $this->getRefId()];
    }

    /**
    * returns object id of created default adminstrator role
    */
    public function getDefaultAdminRole() : int
    {
        $local_group_Roles = $this->getLocalGroupRoles();
        return $local_group_Roles["il_grp_admin_" . $this->getRefId()];
    }

    public function leaveGroup() : int
    {
        $member_ids = $this->getGroupMemberIds();
        if (count($member_ids) <= 1 || !in_array($this->user->getId(), $member_ids)) {
            return 2;
        } elseif (!$this->isAdmin($this->user->getId())) {
            $this->leave($this->user->getId());
            $this->recommended_content_manager->removeObjectRecommendation($this->user->getId(), $this->getRefId());
            return 0;
        } elseif (count($this->getGroupAdminIds()) == 1) {
            return 1;
        }
        return 1;
    }

    /**
    * deassign member from group role
    */
    public function leave(int $a_user_id) : bool
    {
        $arr_groupRoles = $this->getMemberRoles($a_user_id);
        foreach ($arr_groupRoles as $groupRole) {
            $this->rbac_admin->deassignUser($groupRole, $a_user_id);
        }
        return true;
    }

    /**
    * get all group Member ids regardless of role
    * @return    array array of users (obj_ids) that are assigned to
    * the groupspecific roles (grp_member,grp_admin)
    */
    public function getGroupMemberIds() : array
    {
        $usr_arr = array();
        $rol = $this->getLocalGroupRoles();
        $mem_arr = [];
        foreach ($rol as $value) {
            foreach ($this->rbac_review->assignedUsers($value) as $member_id) {
                array_push($usr_arr, $member_id);
            }
        }
        return array_unique($usr_arr);
    }

    /**
    * get all group Members regardless of group role.
    * fetch all users data in one shot to improve performance
    */
    public function getGroupMemberData(array $a_mem_ids, int $active = 1) : array
    {
        $usr_arr = array();
        $q = "SELECT login,firstname,lastname,title,usr_id,last_login " .
             "FROM usr_data " .
             "WHERE usr_id IN (" . implode(',', ilArrayUtil::quoteArray($a_mem_ids)) . ") ";

        if (is_numeric($active) && $active > -1) {
            $q .= "AND active = '$active'";
        }

        $q .= 'ORDER BY lastname,firstname';

        $r = $this->db->query($q);
        $mem_arr = [];
        while ($row = $r->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $mem_arr[] = array("id" => (int) $row->usr_id,
                                "login" => $row->login,
                                "firstname" => $row->firstname,
                                "lastname" => $row->lastname,
                                "last_login" => $row->last_login
                                );
        }

        return $mem_arr;
    }

    public function getCountMembers() : int
    {
        return count($this->getGroupMemberIds());
    }

    public function getGroupAdminIds(int $a_grpId = 0) : array
    {
        if (!empty($a_grpId)) {
            $grp_id = $a_grpId;
        } else {
            $grp_id = $this->getRefId();
        }

        $usr_arr = array();
        $roles = $this->getDefaultGroupRoles();

        foreach ($this->rbac_review->assignedUsers($this->getDefaultAdminRole()) as $member_id) {
            array_push($usr_arr, $member_id);
        }
        return $usr_arr;
    }

    /**
    * get default group roles, returns the defaultlike create roles il_grp_member, il_grp_admin
    * @param array the obj_ids of group specific roles(il_grp_member,il_grp_admin)
    */
    protected function getDefaultGroupRoles() : array
    {
        $grp_id = $this->getRefId();
        $role_arr = $this->rbac_review->getRolesOfRoleFolder($grp_id);
        $arr_grpDefaultRoles = [];
        foreach ($role_arr as $role_id) {
            $role = ilObjectFactory::getInstanceByObjId($role_id, false);
            $grp_Member = "il_grp_member_" . $grp_id;
            $grp_Admin = "il_grp_admin_" . $grp_id;

            if (strcmp($role->getTitle(), $grp_Member) == 0) {
                $arr_grpDefaultRoles["grp_member_role"] = $role->getId();
            }

            if (strcmp($role->getTitle(), $grp_Admin) == 0) {
                $arr_grpDefaultRoles["grp_admin_role"] = $role->getId();
            }
        }
        return $arr_grpDefaultRoles;
    }

    /**
    * get ALL local roles of group, also those created and defined afterwards
    * only fetch data once from database. info is stored in object variable
    * @return array [title|id] of roles...
    */
    public function getLocalGroupRoles(bool $a_translate = false) : array
    {
        if (empty($this->local_roles)) {
            $this->local_roles = array();
            $role_arr = $this->rbac_review->getRolesOfRoleFolder($this->getRefId());

            foreach ($role_arr as $role_id) {
                if ($this->rbac_review->isAssignable($role_id, $this->getRefId()) == true) {
                    $role = ilObjectFactory::getInstanceByObjId($role_id, false);
                    if ($a_translate) {
                        $role_name = ilObjRole::_getTranslation($role->getTitle());
                    } else {
                        $role_name = $role->getTitle();
                    }
                    $this->local_roles[$role_name] = $role->getId();
                }
            }
        }
        return $this->local_roles;
    }

    /**
    * get group status closed template
    */
    public function getGrpStatusClosedTemplateId() : int
    {
        $q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_status_closed'";
        $res = $this->ilias->db->query($q);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

        return (int) $row["obj_id"];
    }

    /**
    * get group status open template
    */
    public function getGrpStatusOpenTemplateId() : int
    {
        $q = "SELECT obj_id FROM object_data WHERE type='rolt' AND title='il_grp_status_open'";
        $res = $this->ilias->db->query($q);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

        return (int) $row["obj_id"];
    }

    public static function lookupGroupStatusTemplateId(int $a_obj_id) : int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $type = self::lookupGroupTye($a_obj_id);
        if ($type == ilGroupConstants::GRP_TYPE_CLOSED) {
            $query = 'SELECT obj_id FROM object_data WHERE type = ' . $ilDB->quote('rolt', 'text') . ' AND title = ' . $ilDB->quote('il_grp_status_closed', 'text');
        } else {
            $query = 'SELECT obj_id FROM object_data WHERE type = ' . $ilDB->quote('rolt', 'text') . ' AND title = ' . $ilDB->quote('il_grp_status_open', 'text');
        }
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);
        return $row['obj_id'] ? (int) $row['obj_id'] : 0;
    }



    /**
     * Change group type
     * Revokes permissions of all parent non-protected roles
     * and initiates these roles with the according il_grp_(open|closed) template.
     */
    public function updateGroupType(
        int $a_group_type = ilGroupConstants::GRP_TYPE_OPEN
    ) : void {
        if ($a_group_type == ilGroupConstants::GRP_TYPE_OPEN) {
            $this->applyDidacticTemplate(0);
            return;
        }
        $templates = ilDidacticTemplateSettings::getInstanceByObjectType($this->getType())->getTemplates();
        foreach ($templates as $template) {
            // the closed template
            if ($template->isAutoGenerated()) {
                $this->logger->info('Appying default closed template');
                $this->applyDidacticTemplate($template->getId());
                return;
            }
        }
        $this->logger->warning('No closed didactic template available.');
    }


    public function setGroupStatus(int $a_status) : void
    {
        $this->group_status = $a_status;
    }

    /**
     * get group status
     */
    public function getGroupStatus() : int
    {
        return $this->group_status;
    }

    /**
     * Read group type
     * @return int
    */
    public function readGroupStatus() : int
    {
        $tpl_id = ilDidacticTemplateObjSettings::lookupTemplateId($this->getRefId());
        if (!$tpl_id) {
            return ilGroupConstants::GRP_TYPE_OPEN;
        }
        return ilGroupConstants::GRP_TYPE_CLOSED;
    }

    public function getMemberRoles(int $a_user_id) : array
    {
        return array_intersect(
            $this->rbac_review->assignedRoles($a_user_id),
            $this->getLocalGroupRoles()
        );
    }

    public function isAdmin(int $a_userId) : bool
    {
        $grp_Roles = $this->getDefaultGroupRoles();
        if (in_array($a_userId, $this->rbac_review->assignedUsers($grp_Roles["grp_admin_role"]))) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * @inheritDoc
    */
    public function initDefaultRoles() : void
    {
        $role = ilObjRole::createDefaultRole(
            'il_grp_admin_' . $this->getRefId(),
            "Groupadmin group obj_no." . $this->getId(),
            'il_grp_admin',
            $this->getRefId()
        );
        $this->m_roleAdminId = $role->getId();

        $role = ilObjRole::createDefaultRole(
            'il_grp_member_' . $this->getRefId(),
            "Groupmember of group obj_no." . $this->getId(),
            'il_grp_member',
            $this->getRefId()
        );
        $this->m_roleMemberId = $role->getId();
    }

    /**
     * This method is called before "initDefaultRoles".
     * Therefore no local group roles are created.
     *
     * Grants permissions on the group object for all parent roles.
     * Each permission is granted by computing the intersection of the
     * template il_grp_status and the permission template of the parent role.
     */
    public function setParentRolePermissions(int $a_parent_ref) : bool
    {
        $parent_roles = $this->rbac_review->getParentRoleIds($a_parent_ref);
        foreach ($parent_roles as $parent_role) {
            if ($parent_role['parent'] == $this->getRefId()) {
                continue;
            }
            if ($this->rbac_review->isProtected((int) $parent_role['parent'], (int) $parent_role['rol_id'])) {
                $operations = $this->rbac_review->getOperationsOfRole(
                    (int) $parent_role['obj_id'],
                    $this->getType(),
                    (int) $parent_role['parent']
                );
                $this->rbac_admin->grantPermission(
                    (int) $parent_role['obj_id'],
                    $operations,
                    $this->getRefId()
                );
                continue;
            }

            $this->rbac_admin->initIntersectionPermissions(
                $this->getRefId(),
                (int) $parent_role['obj_id'],
                (int) $parent_role['parent'],
                $this->getGrpStatusOpenTemplateId(),
                ROLE_FOLDER_ID
            );
        }
        return true;
    }


    /**
     * @inheritDoc
     */
    public function applyDidacticTemplate(int $a_tpl_id) : void
    {
        parent::applyDidacticTemplate($a_tpl_id);

        if (!$a_tpl_id) {
            // init default type
            $this->setParentRolePermissions($this->getRefId());
        }
    }


    public static function _lookupIdByTitle(string $a_title) : int
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT * FROM object_data WHERE title = " .
            $ilDB->quote($a_title, 'text') . " AND type = 'grp'";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->obj_id;
        }
        return 0;
    }


    public function _isMember(int $a_user_id, int $a_ref_id, string $a_field = '') : bool
    {
        $local_roles = $this->rbac_review->getRolesOfRoleFolder($a_ref_id, false);
        $user_roles = $this->rbac_review->assignedRoles($a_user_id);

        // Used for membership limitations -> check membership by given field
        if ($a_field) {
            $tmp_user = ilObjectFactory::getInstanceByObjId($a_user_id);
            if (!$tmp_user instanceof ilObjUser) {
                throw new DomainException('Invalid user id given: ' . $a_user_id);
            }
            switch ($a_field) {
                case 'login':
                    $and = "AND login = '" . $tmp_user->getLogin() . "' ";
                    break;
                case 'email':
                    $and = "AND email = '" . $tmp_user->getEmail() . "' ";
                    break;
                case 'matriculation':
                    $and = "AND matriculation = '" . $tmp_user->getMatriculation() . "' ";
                    break;

                default:
                    $and = "AND usr_id = '" . $a_user_id . "'";
                    break;
            }
            if (!$members = ilObjGroup::_getMembers(ilObject::_lookupObjId($a_ref_id))) {
                return false;
            }
            $query = "SELECT * FROM usr_data as ud " .
                "WHERE usr_id IN (" . implode(",", ilArrayUtil::quoteArray($members)) . ") " .
                $and;
            $res = $this->db->query($query);
            return (bool) $res->numRows();
        }

        if (!array_intersect($local_roles, $user_roles)) {
            return false;
        }

        return true;
    }

    public function _getMembers(int $a_obj_id) : array
    {
        // get reference
        $ref_ids = ilObject::_getAllReferences($a_obj_id);
        $ref_id = current($ref_ids);

        $local_roles = $this->rbac_review->getRolesOfRoleFolder($ref_id, false);

        $users = array();
        foreach ($local_roles as $role_id) {
            $users = array_merge($users, $this->rbac_review->assignedUsers($role_id));
        }
        return array_unique($users);
    }

    /**
     * Get effective container view mode
     * @return int
     */
    public function getViewMode() : int
    {
        $tree = $this->tree;

        // default: by type
        $view = self::lookupViewMode($this->getId());

        if ($view != ilContainer::VIEW_INHERIT) {
            return $view;
        }

        $container_ref_id = $tree->checkForParentType($this->ref_id, 'crs');
        if ($container_ref_id) {
            $view_mode = ilObjCourseAccess::_lookupViewMode(ilObject::_lookupObjId($container_ref_id));
            // these three are available...
            if (
                $view_mode == ilContainer::VIEW_SESSIONS ||
                $view_mode == ilContainer::VIEW_BY_TYPE ||
                $view_mode == ilContainer::VIEW_SIMPLE) {
                return $view_mode;
            }
        }
        return ilContainer::VIEW_DEFAULT;
    }


    public function setViewMode(int $a_view_mode) : void
    {
        $this->view_mode = $a_view_mode;
    }

    public static function lookupViewMode($a_obj_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT view_mode FROM grp_settings ' .
            'WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);

        $view_mode = null;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $view_mode = (int) $row->view_mode;
        }
        return $view_mode;
    }

    public static function translateViewMode(int $a_obj_id, int $a_view_mode, ?int $a_ref_id = null) : int
    {
        global $DIC;

        $tree = $DIC['tree'];

        if (!$a_view_mode) {
            $a_view_mode = ilContainer::VIEW_DEFAULT;
        }

        // view mode is inherit => check for parent course
        if ($a_view_mode == ilContainer::VIEW_INHERIT) {
            if (!$a_ref_id) {
                $ref = ilObject::_getAllReferences($a_obj_id);
                $a_ref_id = end($ref);
            }

            $crs_ref = $tree->checkForParentType($a_ref_id, 'crs');
            if (!$crs_ref) {
                return ilContainer::VIEW_DEFAULT;
            }

            $view_mode = ilObjCourse::_lookupViewMode(ilObject::_lookupObjId($crs_ref));

            // validate course view mode
            if (!in_array($view_mode, array(ilContainer::VIEW_SESSIONS,
                ilContainer::VIEW_BY_TYPE, ilContainer::VIEW_SIMPLE))) {
                return ilContainer::VIEW_DEFAULT;
            }

            return $view_mode;
        }

        return $a_view_mode;
    }

    /**
    * Add additional information to sub item, e.g. used in
    * courses for timings information etc.
    */
    public function addAdditionalSubItemInformation(array &$object) : void
    {
        ilObjectActivation::addAdditionalSubItemInformation($object);
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

    /**
     * Prepare calendar appointments
     * @return ilCalendarAppointmentTemplate[]
     */
    protected function prepareAppointments($a_mode = 'create') : array
    {
        switch ($a_mode) {
            case 'create':
            case 'update':

                $apps = array();
                if ($this->getStart() && $this->getEnd()) {
                    $app = new ilCalendarAppointmentTemplate(self::CAL_START);
                    $app->setTitle($this->getTitle());
                    $app->setSubtitle('grp_cal_start');
                    $app->setTranslationType(ilCalendarEntry::TRANSLATION_SYSTEM);
                    $app->setDescription($this->getLongDescription());
                    $app->setStart($this->getStart());
                    $app->setFullday(!$this->getStartTimeIndication());
                    $apps[] = $app;

                    $app = new ilCalendarAppointmentTemplate(self::CAL_END);
                    $app->setTitle($this->getTitle());
                    $app->setSubtitle('grp_cal_end');
                    $app->setTranslationType(ilCalendarEntry::TRANSLATION_SYSTEM);
                    $app->setDescription($this->getLongDescription());
                    $app->setStart($this->getEnd());
                    $app->setFullday(!$this->getStartTimeIndication());
                    $apps[] = $app;
                }
                if ($this->isRegistrationUnlimited()) {
                    return $apps;
                }

                $app = new ilCalendarAppointmentTemplate(self::CAL_REG_START);
                $app->setTitle($this->getTitle());
                $app->setSubtitle('grp_cal_reg_start');
                $app->setTranslationType(ilCalendarEntry::TRANSLATION_SYSTEM);
                $app->setDescription($this->getLongDescription());
                $app->setStart($this->getRegistrationStart());
                $apps[] = $app;

                $app = new ilCalendarAppointmentTemplate(self::CAL_REG_END);
                $app->setTitle($this->getTitle());
                $app->setSubtitle('grp_cal_reg_end');
                $app->setTranslationType(ilCalendarEntry::TRANSLATION_SYSTEM);
                $app->setDescription($this->getLongDescription());
                $app->setStart($this->getRegistrationEnd());
                $apps[] = $app;


                return $apps;

            case 'delete':
                // Nothing to do: The category and all assigned appointments will be deleted.
                return array();
        }
        return [];
    }


    protected function initParticipants() : void
    {
        $this->members_obj = ilGroupParticipants::_getInstanceByObjId($this->getId());
    }

    public function getMembersObject() : ilGroupParticipants
    {
        // #17886
        if (!$this->members_obj instanceof ilGroupParticipants) {
            $this->initParticipants();
        }
        return $this->members_obj;
    }

    /**
     * @return int[]
     *@see interface.ilMembershipRegistrationCodes
          */
    public static function lookupObjectsByCode(string $a_code) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT obj_id FROM grp_settings " .
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
     * @see ilMembershipRegistrationCodes::register()
     * @inheritDoc
     */
    public function register(
        int $a_user_id,
        int $a_role = ilParticipants::IL_GRP_MEMBER,
        bool $a_force_registration = false
    ) : void {
        $part = ilGroupParticipants::_getInstanceByObjId($this->getId());

        if ($part->isAssigned($a_user_id)) {
            return;
        }

        if (!$a_force_registration) {
            // Availability
            if (!$this->isRegistrationEnabled()) {
                if (!ilObjGroupAccess::_usingRegistrationCode()) {
                    throw new ilMembershipRegistrationException('Cannot registrate to group ' . $this->getId() .
                        ', group subscription is deactivated.', ilMembershipRegistrationException::REGISTRATION_CODE_DISABLED);
                }
            }

            // Time Limitation
            if (!$this->isRegistrationUnlimited()) {
                $start = $this->getRegistrationStart();
                $end = $this->getRegistrationEnd();
                $time = new ilDateTime(time(), IL_CAL_UNIX);

                if (!(ilDateTime::_after($time, $start) and ilDateTime::_before($time, $end))) {
                    throw new ilMembershipRegistrationException('Cannot registrate to group ' . $this->getId() .
                    ', group is out of registration time.', ilMembershipRegistrationException::OUT_OF_REGISTRATION_PERIOD);
                }
            }

            // Max members
            if ($this->isMembershipLimited()) {
                $free = max(0, $this->getMaxMembers() - $part->getCountMembers());
                $waiting_list = new ilGroupWaitingList($this->getId());
                if ($this->isWaitingListEnabled() and (!$free or $waiting_list->getCountUsers())) {
                    $this->lng->loadLanguageModule("grp");
                    $waiting_list->addToList($a_user_id);

                    $info = sprintf(
                        $this->lng->txt('grp_added_to_list'),
                        $this->getTitle(),
                        $waiting_list->getPosition($a_user_id)
                    );

                    $participants = ilGroupParticipants::_getInstanceByObjId($this->getId());
                    $participants->sendNotification(ilGroupMembershipMailNotification::TYPE_WAITING_LIST_MEMBER, $a_user_id);

                    throw new ilMembershipRegistrationException($info, ilMembershipRegistrationException::ADDED_TO_WAITINGLIST);
                }

                if (!$free or $waiting_list->getCountUsers()) {
                    throw new ilMembershipRegistrationException('Cannot registrate to group ' . $this->getId() .
                        ', membership is limited.', ilMembershipRegistrationException::OBJECT_IS_FULL);
                }
            }
        }

        $part->add($a_user_id, $a_role);
        $part->sendNotification(ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER, $a_user_id);
        $part->sendNotification(ilGroupMembershipMailNotification::TYPE_NOTIFICATION_REGISTRATION, $a_user_id);
    }

    public function handleAutoFill() : void
    {
        if ($this->isWaitingListEnabled() &&
            $this->hasWaitingListAutoFill()) {
            $max = $this->getMaxMembers();
            $now = ilGroupParticipants::lookupNumberOfMembers($this->getRefId());
            if ($max > $now) {
                // see assignFromWaitingListObject()
                $waiting_list = new ilGroupWaitingList($this->getId());

                foreach ($waiting_list->getUserIds() as $user_id) {
                    if (!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id, false)) {
                        continue;
                    }
                    if ($this->getMembersObject()->isAssigned($user_id)) {
                        continue;
                    }
                    $this->getMembersObject()->add($user_id, ilParticipants::IL_GRP_MEMBER); // #18213
                    $this->getMembersObject()->sendNotification(ilGroupMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER, $user_id, true);
                    $waiting_list->removeFromList($user_id);

                    $now++;
                    if ($now >= $max) {
                        break;
                    }
                }
            }
        }
    }

    public static function mayLeave(int $a_group_id, int $a_user_id = null, ?ilDate &$a_date = null) : bool
    {
        global $DIC;

        $ilUser = $DIC->user();
        $ilDB = $DIC->database();

        if (!$a_user_id) {
            $a_user_id = $ilUser->getId();
        }

        $set = $ilDB->query("SELECT leave_end" .
            " FROM grp_settings" .
            " WHERE obj_id = " . $ilDB->quote($a_group_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        if ($row && isset($row["leave_end"]) && is_numeric($row["leave_end"])) {
            // timestamp to date
            $limit = date("Ymd", (int) $row["leave_end"]);
            if ($limit < date("Ymd")) {
                $a_date = new ilDate(date("Y-m-d", (int) $row["leave_end"]), IL_CAL_DATE);
                return false;
            }
        }
        return true;
    }

    public static function findGroupsWithNotEnoughMembers() : array
    {
        global $DIC;
        $ilDB = $DIC->database();
        $tree = $DIC->repositoryTree();

        $res = array();
        $before = new ilDateTime(time(), IL_CAL_UNIX);
        $before->increment(IL_CAL_DAY, -1);
        $now_date = $before->get(IL_CAL_DATETIME);
        $now = $before->get(IL_CAL_UNIX);

        $set = $ilDB->query($q = "SELECT obj_id, registration_min_members" .
            " FROM grp_settings" .
            " WHERE registration_min_members > " . $ilDB->quote(0, "integer") .
            " AND registration_mem_limit = " . $ilDB->quote(1, "integer") . // #17206
            " AND ((leave_end IS NOT NULL" .
                " AND leave_end < " . $ilDB->quote($now, "integer") . ")" .
                " OR (leave_end IS NULL" .
                " AND registration_end IS NOT NULL" .
                " AND registration_end < " . $ilDB->quote($now_date, "text") . "))" .
            " AND (period_start IS NULL OR period_start > " . $ilDB->quote($now, "integer") . ")");
        while ($row = $ilDB->fetchAssoc($set)) {
            $refs = ilObject::_getAllReferences((int) $row['obj_id']);
            $ref = end($refs);

            if ($tree->isDeleted($ref)) {
                continue;
            }

            $part = new ilGroupParticipants($row["obj_id"]);
            $reci = $part->getNotificationRecipients();
            if (sizeof($reci)) {
                $missing = (int) $row["registration_min_members"] - $part->getCountMembers();
                if ($missing > 0) {
                    $res[(int) $row["obj_id"]] = array($missing, $reci);
                }
            }
        }
        return $res;
    }

    public static function lookupShowMembersEnabled(int $a_obj_id) : bool
    {
        global $DIC;
        $ilDB = $DIC->database();

        $query = 'SELECT show_members FROM grp_settings'
            . ' WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);
        if ($ilDB->numRows($res) == 0) {
            return false;
        }
        $row = $ilDB->fetchAssoc($res);
        return (bool) $row['show_members'];
    }


    /**
     * @inheritDoc
     */
    public function getSubItems(
        bool $a_admin_panel_enabled = false,
        bool $a_include_side_block = false,
        int $a_get_single = 0,
        \ilContainerUserFilter $container_user_filter = null
    ) : array {
        // Caching
        if (
            isset($this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block]) &&
            is_array($this->items[(int) $a_admin_panel_enabled][(int) $a_include_side_block])
        ) {
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
} //END class.ilObjGroup
