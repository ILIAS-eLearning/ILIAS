<?php declare(strict_types=0);

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
 * XML writer class
 * Class to simplify manual writing of xml documents.
 * It only supports writing xml sequentially, because the xml document
 * is saved in a string with no additional structure information.
 * The author is responsible for well-formedness and validity
 * of the xml document.
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 */
class ilCourseXMLWriter extends ilXmlWriter
{
    public const MODE_SOAP = 1;
    public const MODE_EXPORT = 2;

    public const EXPORT_VERSION = '8.0';

    private int $mode = self::MODE_SOAP;

    private string $xml = '';
    private ilObjCourse $course_obj;
    private bool $attach_users = true;

    protected ilSetting $setting;
    protected ilAccessHandler $access;

    public function __construct(ilObjCourse $course_obj)
    {
        global $DIC;

        $this->setting = $DIC->settings();
        $this->access = $DIC->access();

        parent::__construct();
        $this->course_obj = $course_obj;
    }

    public function setMode(int $a_mode) : void
    {
        $this->mode = $a_mode;
    }

    public function getMode() : int
    {
        return $this->mode;
    }

    public function start() : void
    {
        if ($this->getMode() == self::MODE_SOAP) {
            $this->__buildHeader();
            $this->__buildCourseStart();
            $this->__buildMetaData();
            $this->__buildAdvancedMetaData();
            if ($this->attach_users) {
                $this->__buildAdmin();
                $this->__buildTutor();
                $this->__buildMember();
            }
            $this->__buildSubscriber();
            $this->__buildWaitingList();

            $this->__buildSetting();
            ilContainerSortingSettings::_exportContainerSortingSettings($this, $this->course_obj->getId());
            ilContainer::_exportContainerSettings($this, $this->course_obj->getId());
            $this->__buildFooter();
        } elseif ($this->getMode() == self::MODE_EXPORT) {
            $this->__buildCourseStart();
            $this->__buildMetaData();
            $this->__buildAdvancedMetaData();
            $this->__buildSetting();
            ilContainerSortingSettings::_exportContainerSortingSettings($this, $this->course_obj->getId());
            ilContainer::_exportContainerSettings($this, $this->course_obj->getId());
            $this->__buildFooter();
        }
    }

    public function getXML() : string
    {
        return $this->xmlDumpMem(true);
    }

    public function modifyExportIdentifier($a_tag, $a_param, $a_value)
    {
        if ($a_tag == "Identifier" && $a_param == "Entry") {
            $a_value = "il_" . $this->setting->get('inst_id') . "_crs_" . $this->course_obj->getId();
        }

        return $a_value;
    }

    // PRIVATE
    public function __buildHeader() : void
    {
        $this->xmlSetDtdDef("<!DOCTYPE Course PUBLIC \"-//ILIAS//DTD Course//EN\" \"" . ILIAS_HTTP_PATH . "/xml/ilias_crs_5_0.dtd\">");
        $this->xmlSetGenCmt("Export of ILIAS course " . $this->course_obj->getId() . " of installation " . $this->setting->get('inst_id') . ".");
        $this->xmlHeader();
    }

    public function __buildCourseStart() : void
    {
        $attrs["exportVersion"] = self::EXPORT_VERSION;
        $attrs["id"] = "il_" . $this->setting->get('inst_id') . '_crs_' . $this->course_obj->getId();
        $attrs['showMembers'] = ($this->course_obj->getShowMembers() ? 'Yes' : 'No');
        $this->xmlStartTag("Course", $attrs);
    }

    public function __buildMetaData() : void
    {
        $md2xml = new ilMD2XML($this->course_obj->getId(), $this->course_obj->getId(), 'crs');
        $md2xml->startExport();
        $this->appendXML($md2xml->getXML());
    }

    private function __buildAdvancedMetaData() : void
    {
        ilAdvancedMDValues::_appendXMLByObjId($this, $this->course_obj->getId());
    }

    public function __buildAdmin() : void
    {
        $admins = $this->course_obj->getMembersObject()->getAdmins();
        $admins = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            ilOrgUnitOperation::OP_MANAGE_MEMBERS,
            $this->course_obj->getRefId(),
            $admins
        );

        foreach ($admins as $id) {
            $attr['id'] = 'il_' . $this->setting->get('inst_id') . '_usr_' . $id;
            $attr['notification'] = ($this->course_obj->getMembersObject()->isNotificationEnabled($id)) ? 'Yes' : 'No';
            $attr['passed'] = $this->course_obj->getMembersObject()->hasPassed($id) ? 'Yes' : 'No';
            $attr['contact'] = $this->course_obj->getMembersObject()->isContact($id) ? 'Yes' : 'No';

            $this->xmlStartTag('Admin', $attr);
            $this->xmlEndTag('Admin');
        }
    }

    public function __buildTutor() : void
    {
        $tutors = $this->course_obj->getMembersObject()->getTutors();
        $tutors = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            ilOrgUnitOperation::OP_MANAGE_MEMBERS,
            $this->course_obj->getRefId(),
            $tutors
        );
        foreach ($tutors as $id) {
            $attr['id'] = 'il_' . $this->setting->get('inst_id') . '_usr_' . $id;
            $attr['notification'] = ($this->course_obj->getMembersObject()->isNotificationEnabled($id)) ? 'Yes' : 'No';
            $attr['passed'] = $this->course_obj->getMembersObject()->hasPassed($id) ? 'Yes' : 'No';
            $attr['contact'] = $this->course_obj->getMembersObject()->isContact($id) ? 'Yes' : 'No';

            $this->xmlStartTag('Tutor', $attr);
            $this->xmlEndTag('Tutor');
        }
    }

    public function __buildMember() : void
    {
        $members = $this->course_obj->getMembersObject()->getMembers();
        $members = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            ilOrgUnitOperation::OP_MANAGE_MEMBERS,
            $this->course_obj->getRefId(),
            $members
        );
        foreach ($members as $id) {
            $attr['id'] = 'il_' . $this->setting->get('inst_id') . '_usr_' . $id;
            $attr['blocked'] = ($this->course_obj->getMembersObject()->isBlocked($id)) ? 'Yes' : 'No';
            $attr['passed'] = $this->course_obj->getMembersObject()->hasPassed($id) ? 'Yes' : 'No';

            $this->xmlStartTag('Member', $attr);
            $this->xmlEndTag('Member');
        }
    }

    public function __buildSubscriber() : void
    {
        $subs = $this->course_obj->getMembersObject()->getSubscribers();
        $subs = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
            'manage_members',
            ilOrgUnitOperation::OP_MANAGE_MEMBERS,
            $this->course_obj->getRefId(),
            $subs
        );

        foreach ($subs as $id) {
            $data = $this->course_obj->getMembersObject()->getSubscriberData($id);

            $attr['id'] = 'il_' . $this->setting->get('inst_id') . '_usr_' . $id;
            $attr['subscriptionTime'] = $data['time'];

            $this->xmlStartTag('Subscriber', $attr);
            $this->xmlEndTag('Subscriber');
        }
    }

    public function __buildWaitingList() : void
    {
        $waiting_list = new ilCourseWaitingList($this->course_obj->getId());
        $wait = $waiting_list->getAllUsers();
        foreach ($wait as $data) {
            $is_accessible = $GLOBALS['DIC']->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
                'manage_members',
                ilOrgUnitOperation::OP_MANAGE_MEMBERS,
                $this->course_obj->getRefId(),
                [$data['usr_id']]
            );
            if (count($is_accessible) === 0) {
                continue;
            }

            $attr['id'] = 'il_' . $this->setting->get('inst_id') . '_usr_' . $data['usr_id'];
            $attr['position'] = $data['position'];
            $attr['subscriptionTime'] = $data['time'];

            $this->xmlStartTag('WaitingList', $attr);
            $this->xmlEndTag('WaitingList');
        }
    }

    public function __buildSetting() : void
    {
        $this->xmlStartTag('Settings');

        // Availability
        $this->xmlStartTag('Availability');
        if ($this->course_obj->getOfflineStatus()) {
            $this->xmlElement('NotAvailable');
        } elseif ($this->course_obj->getActivationUnlimitedStatus()) {
            $this->xmlElement('Unlimited');
        } else {
            $this->xmlStartTag('TemporarilyAvailable');
            $this->xmlElement('Start', null, $this->course_obj->getActivationStart());
            $this->xmlElement('End', null, $this->course_obj->getActivationEnd());
            $this->xmlEndTag('TemporarilyAvailable');
        }
        $this->xmlEndTag('Availability');

        // Syllabus
        $this->xmlElement('Syllabus', null, $this->course_obj->getSyllabus());
        $this->xmlElement('ImportantInformation', null, $this->course_obj->getImportantInformation());
        $this->xmlElement('TargetGroup', null, $this->course_obj->getTargetGroup());

        // Contact
        $this->xmlStartTag('Contact');
        $this->xmlElement('Name', null, $this->course_obj->getContactName());
        $this->xmlElement('Responsibility', null, $this->course_obj->getContactResponsibility());
        $this->xmlElement('Phone', null, $this->course_obj->getContactPhone());
        $this->xmlElement('Email', null, $this->course_obj->getContactEmail());
        $this->xmlElement('Consultation', null, $this->course_obj->getContactConsultation());
        $this->xmlEndTag('Contact');

        // Registration
        $attr = array();

        if ($this->course_obj->getSubscriptionType() == ilCourseConstants::IL_CRS_SUBSCRIPTION_CONFIRMATION) {
            $attr['registrationType'] = 'Confirmation';
        } elseif ($this->course_obj->getSubscriptionType() == ilCourseConstants::IL_CRS_SUBSCRIPTION_DIRECT) {
            $attr['registrationType'] = 'Direct';
        } else {
            $attr['registrationType'] = 'Password';
        }

        $attr['maxMembers'] = $this->course_obj->isSubscriptionMembershipLimited() ?
            $this->course_obj->getSubscriptionMaxMembers() : 0;
        $attr['notification'] = $this->course_obj->getSubscriptionNotify() ? 'Yes' : 'No';
        $attr['waitingList'] = $this->course_obj->enabledWaitingList() ? 'Yes' : 'No';

        $this->xmlStartTag('Registration', $attr);

        if ($this->course_obj->getSubscriptionLimitationType() == ilCourseConstants::IL_CRS_SUBSCRIPTION_DEACTIVATED) {
            $this->xmlElement('Disabled');
        } elseif ($this->course_obj->getSubscriptionLimitationType() == ilCourseConstants::IL_CRS_SUBSCRIPTION_UNLIMITED) {
            $this->xmlElement('Unlimited');
        } else {
            $this->xmlStartTag('TemporarilyAvailable');
            $this->xmlElement('Start', null, $this->course_obj->getSubscriptionStart());
            $this->xmlElement('End', null, $this->course_obj->getSubscriptionEnd());
            $this->xmlEndTag('TemporarilyAvailable');
        }
        if (strlen($pwd = $this->course_obj->getSubscriptionPassword())) {
            $this->xmlElement('Password', null, $pwd);
        }
        $this->xmlEndTag('Registration');

        $this->xmlStartTag('Period', ['withTime' => $this->course_obj->getCourseStartTimeIndication() ? 1 : 0]);
        $this->xmlElement(
            'Start',
            null,
            $this->course_obj->getCourseStart()
                ? $this->course_obj->getCourseStart()->get(IL_CAL_UNIX)
                : null
        );
        $this->xmlElement(
            'End',
            null,
            $this->course_obj->getCourseEnd()
                ? $this->course_obj->getCourseEnd()->get(IL_CAL_UNIX)
                : null
        );
        $this->xmlEndTag('Period');
        $this->xmlElement('WaitingListAutoFill', null, (int) $this->course_obj->hasWaitingListAutoFill());
        $this->xmlElement(
            'CancellationEnd',
            null,
            ($this->course_obj->getCancellationEnd() && !$this->course_obj->getCancellationEnd()->isNull()) ? $this->course_obj->getCancellationEnd()->get(IL_CAL_UNIX) : null
        );
        $this->xmlElement('MinMembers', null, $this->course_obj->getSubscriptionMinMembers());

        $this->xmlElement('ViewMode', null, $this->course_obj->getViewMode());
        if ($this->course_obj->getViewMode() == ilCourseConstants::IL_CRS_VIEW_TIMING) {
            $this->xmlElement('TimingMode', null, $this->course_obj->getTimingMode());
        }

        $this->xmlElement(
            'SessionLimit',
            [
                'active' => $this->course_obj->isSessionLimitEnabled() ? 1 : 0,
                'previous' => $this->course_obj->getNumberOfPreviousSessions(),
                'next' => $this->course_obj->getNumberOfNextSessions()
            ]
        );

        $this->xmlElement(
            'WelcomeMail',
            [
                'status' => $this->course_obj->getAutoNotification() ? 1 : 0
            ]
        );

        $this->xmlEndTag('Settings');
    }

    public function __buildFooter() : void
    {
        $this->xmlEndTag('Course');
    }

    public function setAttachUsers($value) : void
    {
        $this->attach_users = (bool) $value;
    }
}
