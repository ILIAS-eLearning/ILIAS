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
 ********************************************************************
 */

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;
use ILIAS\HTTP\Services;

/**
 * Class ilOrgUnitUserAssignmentGUI
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 * @author       dkloepfer
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @ilCtrl_Calls ilOrgUnitUserAssignmentGUI: ilRepositorySearchGUI
 */
class ilOrgUnitUserAssignmentGUI extends BaseCommands
{
    public const CMD_ASSIGNMENTS_RECURSIVE = 'assignmentsRecursive';
    public const SUBTAB_ASSIGNMENTS = 'user_assignments';
    public const SUBTAB_ASSIGNMENTS_RECURSIVE = 'user_assignments_recursive';
    private \ilGlobalTemplateInterface $main_tpl;
    private Services $http;
    private ilCtrl $ctrl;
    private ilToolbarGUI $toolbar;
    private ilAccessHandler $access;
    private ilLanguage $language;

    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->http = $DIC->http();
        $this->ctrl = $DIC->ctrl();
        $this->toolbar = $DIC->toolbar();
        $this->access = $DIC->access();
        $this->language = $DIC->language();
    }

    public function executeCommand(): void
    {
        if (!ilObjOrgUnitAccess::_checkAccessPositions((int) filter_input(
            INPUT_GET,
            "ref_id",
            FILTER_SANITIZE_NUMBER_INT
        ))) {
            $this->main_tpl->setOnScreenMessage('failure', $this->language->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilObjOrgUnitGUI::class);
        }

        $r = $this->http->request();
        switch ($this->ctrl->getNextClass()) {
            case strtolower(ilRepositorySearchGUI::class):
                switch ($this->ctrl->getCmd()) {
                    case 'addUserFromAutoComplete':
                        if ($r->getQueryParams()['addusertype'] == "staff") {
                            $this->addStaff();
                        }
                        break;
                    default:
                        $repo = new ilRepositorySearchGUI();
                        $this->ctrl->forwardCommand($repo);
                        break;
                }
                break;

            default:
                parent::executeCommand();
                break;
        }
    }

    protected function index(): void
    {
        $this->addSubTabs();
        $this->activeSubTab(self::SUBTAB_ASSIGNMENTS);

        // Header
        $types = ilOrgUnitPosition::getArray('id', 'title');
        //$types = array();
        $this->ctrl->setParameterByClass(ilRepositorySearchGUI::class, 'addusertype', 'staff');
        ilRepositorySearchGUI::fillAutoCompleteToolbar($this, $this->toolbar, array(
            'auto_complete_name' => $this->language->txt('user'),
            'user_type' => $types,
            'submit_name' => $this->language->txt('add'),
        ));

        // Tables
        $html = '';
        foreach (ilOrgUnitPosition::getActiveForPosition($this->getParentRefId()) as $ilOrgUnitPosition) {
            $ilOrgUnitUserAssignmentTableGUI = new ilOrgUnitUserAssignmentTableGUI(
                $this,
                self::CMD_INDEX,
                $ilOrgUnitPosition
            );
            $html .= $ilOrgUnitUserAssignmentTableGUI->getHTML();
        }
        $this->setContent($html);
    }

    protected function assignmentsRecursive(): void
    {
        $this->addSubTabs();
        $this->activeSubTab(self::SUBTAB_ASSIGNMENTS_RECURSIVE);
        // Tables
        $html = '';
        foreach (ilOrgUnitPosition::getActiveForPosition($this->getParentRefId()) as $ilOrgUnitPosition) {
            $ilOrgUnitRecursiveUserAssignmentTableGUI =
                new ilOrgUnitRecursiveUserAssignmentTableGUI(
                    $this,
                    self::CMD_ASSIGNMENTS_RECURSIVE,
                    $ilOrgUnitPosition
                );
            $html .= $ilOrgUnitRecursiveUserAssignmentTableGUI->getHTML();
        }
        $this->setContent($html);
    }

    protected function confirm(): void
    {
        $confirmation = $this->getConfirmationGUI();
        $confirmation->setConfirm($this->language->txt('remove_user'), self::CMD_DELETE);

        $this->setContent($confirmation->getHTML());
    }

    protected function confirmRecursive(): void
    {
        $confirmation = $this->getConfirmationGUI();
        $confirmation->setConfirm($this->language->txt('remove_user'), self::CMD_DELETE_RECURSIVE);

        $this->setContent($confirmation->getHTML());
    }

    protected function getConfirmationGUI(): ilConfirmationGUI
    {
        $this->ctrl->saveParameter($this, 'position_id');
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setCancel($this->language->txt(self::CMD_CANCEL), self::CMD_CANCEL);

        $params = $this->http->request()->getQueryParams();
        $usr_id = $params['usr_id'];
        $position_id = $params['position_id'];

        $types = ilOrgUnitPosition::getArray('id', 'title');
        $position_title = $types[$position_id];

        $confirmation->setHeaderText(sprintf($this->language->txt('msg_confirm_remove_user'), $position_title));
        $confirmation->addItem('usr_id', $usr_id, ilObjUser::_lookupLogin($usr_id));

        return $confirmation;
    }

    protected function delete(): void
    {
        $params = $this->http->request()->getQueryParams();
        $usr_id = $_POST['usr_id'];
        $position_id = $params['position_id'];

        $ua = ilOrgUnitUserAssignmentQueries::getInstance()->getAssignmentOrFail(
            $usr_id,
            $position_id,
            $this->getParentRefId()
        );
        $ua->delete();
        $this->main_tpl->setOnScreenMessage('success', $this->language->txt('remove_successful'), true);
        $this->cancel();
    }

    protected function deleteRecursive()
    {
        $r = $this->http->request();
        $assignments = ilOrgUnitUserAssignmentQueries::getInstance()
            ->getAssignmentsOfUserIdAndPosition((int) $_POST['usr_id'], (int) $r->getQueryParams()['position_id'])
        ;

        foreach ($assignments as $assignment) {
            $assignment->delete();
        }
        $this->main_tpl->setOnScreenMessage('success', $this->language->txt('remove_successful'), true);
        $this->cancel();
    }

    protected function cancel(): void
    {
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    public function addStaff(): void
    {
        if (!$this->access->checkAccess("write", "", $this->getParentRefId())) {
            $this->main_tpl->setOnScreenMessage('failure', $this->language->txt("permission_denied"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        $users = explode(',', $_POST['user_login']);
        $user_ids = array();
        foreach ($users as $user) {
            $user_id = ilObjUser::_lookupId($user);
            if ($user_id) {
                $user_ids[] = $user_id;
            }
        }

        if (!count($user_ids)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->txt("user_not_found"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        $position_id = isset($_POST['user_type']) ? $_POST['user_type'] : 0;

        if (!$position_id && !$position = ilOrgUnitPosition::find($position_id)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->language->txt("user_not_found"), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }
        foreach ($user_ids as $user_id) {
            ilOrgUnitUserAssignment::findOrCreateAssignment($user_id, $position_id, $this->getParentRefId());
        }

        $this->main_tpl->setOnScreenMessage('success', $this->language->txt("users_successfuly_added"), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    public function addSubTabs(): void
    {
        $this->pushSubTab(self::SUBTAB_ASSIGNMENTS, $this->ctrl
                                                         ->getLinkTarget($this, self::CMD_INDEX));
        $this->pushSubTab(self::SUBTAB_ASSIGNMENTS_RECURSIVE, $this->ctrl
                                                                   ->getLinkTarget(
                                                                       $this,
                                                                       self::CMD_ASSIGNMENTS_RECURSIVE
                                                                   ));
    }
}
