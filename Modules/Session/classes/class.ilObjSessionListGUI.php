<?php

declare(strict_types=1);

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
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesSession
*/
class ilObjSessionListGUI extends ilObjectListGUI
{
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;
    protected array $app_info = [];
    protected bool $subitems_enabled = false;
    protected string $title = "";

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('sess');

        parent::__construct();
    }

    public function init(): void
    {
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->copy_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->subitems_enabled = true;
        $this->type = "sess";
        $this->gui_class_name = "ilobjsessiongui";

        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        $this->enableSubstitutions($this->substitutions->isActive());

        // general commands array
        $this->commands = ilObjSessionAccess::_getCommands();
    }

    /**
     * get title
     * Overwritten since sessions prepend the date of the session
     * to the title
     */
    public function getTitle(): string
    {
        $app_info = $this->getAppointmentInfo();
        $title = strlen($this->title) ? (': ' . $this->title) : '';
        return ilSessionAppointment::_appointmentToString(
            $app_info['start'] ?? 0,
            $app_info['end'] ?? 0,
            (bool) ($app_info['fullday'] ?? false)
        ) . $title;
    }

    public function getCommandLink($a_cmd): string
    {
        $ilCtrl = $this->ctrl;

        // separate method for this line
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
        $cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->requested_ref_id);
        return $cmd_link;
    }

    /**
     * Only check cmd access for cmd 'register' and 'unregister'
     */
    public function checkCommandAccess($a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id = null): bool
    {
        if ($a_cmd != 'register' && $a_cmd != 'unregister') {
            $a_cmd = '';
        }
        return parent::checkCommandAccess($a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id);
    }

    public function getProperties(): array
    {
        $app_info = $this->getAppointmentInfo();

        $props = [];
        $session_data = new ilObjSession($this->obj_id, false);
        $part = ilSessionParticipants::getInstance($this->ref_id);

        if ($session_data->isRegistrationUserLimitEnabled()) {
            if ($part->getCountMembers() <= $session_data->getRegistrationMaxUsers()) {
                $props[] = array(
                    'alert' => false,
                    'property' => $this->lng->txt('sess_list_reg_limit_places'),
                    'value' => max(
                        0,
                        $session_data->getRegistrationMaxUsers() - $part->getCountMembers()
                    )
                );
            }
        }

        if ($this->getDetailsLevel() == ilObjectListGUI::DETAILS_MINIMAL) {
            if ($items = self::lookupAssignedMaterials($this->obj_id)) {
                $props[] = array(
                    'alert' => false,
                    'property' => $this->lng->txt('event_ass_materials_prop'),
                    'value' => count($items)
                );
            }
        }
        if ($this->getDetailsLevel() == ilObjectListGUI::DETAILS_ALL) {
            $session_data = ilObjSession::lookupSession($this->obj_id);

            if (strlen($session_data['location'])) {
                $props[] = array(
                    'alert' => false,
                    'property' => $this->lng->txt('event_location'),
                    'value' => $session_data['location']
                );
            }
            if (strlen($session_data['details'])) {
                $props[] = array(
                    'alert' => false,
                    'property' => $this->lng->txt('event_details_workflow'),
                    'value' => nl2br($session_data['details']),
                    'newline' => true
                );
            }
            $has_new_line = false;
            if (strlen($session_data['name'])) {
                $props[] = array(
                    'alert' => false,
                    'property' => $this->lng->txt('event_lecturer'),
                    'value' => $session_data['name'],
                    'newline' => true
                );
                $has_new_line = true;
            }
            if (strlen($session_data['email'])) {
                $props[] = array(
                    'alert' => false,
                    'property' => $this->lng->txt('tutor_email'),
                    'value' => $session_data['email'],
                    'newline' => $has_new_line ? false : true
                );
                $has_new_line = true;
            }
            if (strlen($session_data['phone'])) {
                $props[] = array(
                    'alert' => false,
                    'property' => $this->lng->txt('tutor_phone'),
                    'value' => $session_data['phone'],
                    'newline' => $has_new_line ? false : true
                );
                $has_new_line = true;
            }
        }

        // booking information
        $repo = ilObjSessionAccess::getBookingInfoRepo();
        if ($repo instanceof ilBookingReservationDBRepository) {
            $book_info = new ilBookingInfoListItemPropertiesAdapter($repo);
            $props = $book_info->appendProperties($this->obj_id, $props);
        }
        return $props;
    }

    protected function getAppointmentInfo(): array
    {
        if (isset($this->app_info[$this->obj_id])) {
            return $this->app_info[$this->obj_id];
        }
        return $this->app_info[$this->obj_id] = ilSessionAppointment::_lookupAppointment($this->obj_id);
    }

    protected static function lookupAssignedMaterials(int $a_sess_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT * FROM event_items ei ' .
                'JOIN tree ON item_id = child ' .
                'WHERE event_id = ' . $ilDB->quote($a_sess_id, 'integer') . ' ' .
                'AND tree > 0';
        $res = $ilDB->query($query);
        $items = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $items[] = $row->item_id;
        }
        return $items;
    }
}
