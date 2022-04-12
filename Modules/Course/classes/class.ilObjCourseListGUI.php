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
 * Class ilObjCourseListGUI
 * @author  Alex Killing <alex.killing@gmx.de>
 * $Id$
 * @ingroup ModulesCourse
 */
class ilObjCourseListGUI extends ilObjectListGUI
{
    private ?ilCertificateObjectsForUserPreloader $certificatePreloader = null;

    /**
     * @inheritDoc
     */
    public function init() : void
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
    public function initItem(
        int $ref_id,
        int $obj_id,
        string $type,
        string $title = "",
        string $description = ""
    ) : void {
        parent::initItem($ref_id, $obj_id, $type, $title, $description);
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
    public function getProperties() : array
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
        if ($members->isBlocked() && $members->isAssigned()) {
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
        if ($hasCertificate) {
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
    public function checkCommandAccess(
        string $permission,
        string $cmd,
        int $ref_id,
        string $type,
        ?int $obj_id = null
    ) : bool {
        // Only check cmd access for cmd 'register' and 'unregister'
        if ($cmd != 'view' && $cmd != 'leave' && $cmd != 'join') {
            $cmd = '';
        }

        if ($permission == 'crs_linked') {
            return
                parent::checkCommandAccess('read', $cmd, $ref_id, $type, $obj_id) ||
                parent::checkCommandAccess('join', $cmd, $ref_id, $type, $obj_id);
        }
        return parent::checkCommandAccess($permission, $cmd, $ref_id, $type, $obj_id);
    }
} // END class.ilObjCategoryGUI
