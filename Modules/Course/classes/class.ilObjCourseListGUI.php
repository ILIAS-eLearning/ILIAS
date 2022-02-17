<?php declare(strict_types=0);

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjCourseListGUI
 * @author  Alex Killing <alex.killing@gmx.de>
 * $Id$
 * @ingroup ModulesCourse
 */
class ilObjCourseListGUI extends ilObjectListGUI
{
    private ?ilCertificateObjectsForUserPreloader $certificatePreloader = null;
    private bool $conditions_ok = false;

    /**
     * @inheritDoc
     */
    public function init()
    {
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = false;
        $this->info_screen_enabled = true;
        $this->type = "crs";
        $this->gui_class_name = "ilobjcoursegui";

        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }

        // general commands array
        $this->commands = ilObjCourseAccess::_getCommands();
    }

    /**
     * @inheritdoc
     */
    public function initItem($a_ref_id, $a_obj_id, $type, $a_title = "", $a_description = "")
    {
        parent::initItem($a_ref_id, $a_obj_id, $type, $a_title, $a_description);

        $this->conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($a_ref_id, $this->obj_id);
    }

    protected function getCertificatePreloader() : ilCertificateObjectsForUserPreloader
    {
        if (null === $this->certificatePreloader) {
            $repository = new ilUserCertificateRepository();
            $this->certificatePreloader = new ilCertificateObjectsForUserPreloader($repository);
        }
        return $this->certificatePreloader;
    }

    /**
     * @inheritDoc
     */
    public function getProperties()
    {
        global $DIC;

        $props = parent::getProperties();

        // check activation
        if (
            !ilObjCourseAccess::_isActivated($this->obj_id) &&
            !ilObject::lookupOfflineStatus($this->obj_id)
        ) {
            $showRegistrationInfo = false;
            $props[] = array(
                "alert" => true,
                "property" => $this->lng->txt("status"),
                "value" => $this->lng->txt("offline")
            );
        }

        // blocked
        $members = ilCourseParticipant::_getInstanceByObjId($this->obj_id, $this->user->getId());
        if ($members->isBlocked() and $members->isAssigned()) {
            $props[] = array("alert" => true,
                             "property" => $this->lng->txt("member_status"),
                             "value" => $this->lng->txt("crs_status_blocked")
            );
        }

        // pending subscription
        if (ilCourseParticipants::_isSubscriber($this->obj_id, $this->user->getId())) {
            $props[] = array("alert" => true,
                             "property" => $this->lng->txt("member_status"),
                             "value" => $this->lng->txt("crs_status_pending")
            );
        }

        $info = ilObjCourseAccess::lookupRegistrationInfo($this->obj_id);
        if (isset($info['reg_info_list_prop'])) {
            $props[] = array(
                'alert' => false,
                'newline' => true,
                'property' => $info['reg_info_list_prop']['property'],
                'value' => $info['reg_info_list_prop']['value']
            );
        }
        if (isset($info['reg_info_list_prop_limit'])) {
            $props[] = array(
                'alert' => false,
                'newline' => false,
                'property' => $info['reg_info_list_prop_limit']['property'],
                'propertyNameVisible' => (bool) strlen($info['reg_info_list_prop_limit']['property']),
                'value' => $info['reg_info_list_prop_limit']['value']
            );
        }

        // waiting list
        if (ilCourseWaitingList::_isOnList($this->user->getId(), $this->obj_id)) {
            $props[] = array(
                "alert" => true,
                "property" => $this->lng->txt('member_status'),
                "value" => $this->lng->txt('on_waiting_list')
            );
        }

        // course period
        $info = ilObjCourseAccess::lookupPeriodInfo($this->obj_id);
        if (is_array($info)) {
            $props[] = array(
                'alert' => false,
                'newline' => true,
                'property' => $info['property'] ?? "",
                'value' => $info['value'] ?? ""
            );
        }

        // check for certificates
        $hasCertificate = $this->getCertificatePreloader()->isPreloaded($this->user->getId(), $this->obj_id);
        if (true === $hasCertificate) {
            $this->lng->loadLanguageModule('certificate');
            $cmd_link = "ilias.php?baseClass=ilRepositoryGUI&ref_id=" . $this->ref_id . "&cmd=deliverCertificate";
            $props[] = [
                'alert' => false,
                'property' => $this->lng->txt('certificate'),
                'value' => $DIC->ui()->renderer()->render(
                    $DIC->ui()->factory()->link()->standard($this->lng->txt('download_certificate'), $cmd_link)
                )
            ];
        }

        // booking information
        $repo = ilObjCourseAccess::getBookingInfoRepo();
        if (!$repo instanceof ilBookingReservationDBRepository) {
            $repo = (new ilBookingReservationDBRepositoryFactory())->getRepoWithContextObjCache([$this->obj_id]);
        }
        $book_info = new ilBookingInfoListItemPropertiesAdapter($repo);
        return $book_info->appendProperties($this->obj_id, $props);
    }

    /**
     * @inheritDoc
     */
    public function checkCommandAccess($a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id = "")
    {
        // Only check cmd access for cmd 'register' and 'unregister'
        if ($a_cmd != 'view' and $a_cmd != 'leave' and $a_cmd != 'join') {
            $a_cmd = '';
        }

        if ($a_permission == 'crs_linked') {
            return
                parent::checkCommandAccess('read', $a_cmd, $a_ref_id, $a_type, $a_obj_id) ||
                parent::checkCommandAccess('join', $a_cmd, $a_ref_id, $a_type, $a_obj_id);
        }
        return parent::checkCommandAccess($a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id);
    }
} // END class.ilObjCategoryGUI
