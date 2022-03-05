<?php

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;

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

    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();
    }

    public function executeCommand(): void
    {
        if (!ilObjOrgUnitAccess::_checkAccessPositions((int) filter_input(INPUT_GET, "ref_id",
            FILTER_SANITIZE_NUMBER_INT))) {
            $this->main_tpl->setOnScreenMessage('failure', $this->lng()->txt("permission_denied"), true);
            $this->ctrl()->redirectByClass(ilObjOrgUnitGUI::class);
        }

        $r = $this->http()->request();
        switch ($this->ctrl()->getNextClass()) {
            case strtolower(ilRepositorySearchGUI::class):
                switch ($this->ctrl()->getCmd()) {
                    case 'addUserFromAutoComplete':
                        if ($r->getQueryParams()['addusertype'] == "staff") {
                            $this->addStaff();
                        }
                        break;
                    default:
                        $repo = new ilRepositorySearchGUI();
                        $this->ctrl()->forwardCommand($repo);
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
        $this->ctrl()->setParameterByClass(ilRepositorySearchGUI::class, 'addusertype', 'staff');
        ilRepositorySearchGUI::fillAutoCompleteToolbar($this, $this->dic()->toolbar(), array(
            'auto_complete_name' => $this->txt('user'),
            'user_type' => $types,
            'submit_name' => $this->txt('add'),
        ));

        // Tables
        $html = '';
        foreach (ilOrgUnitPosition::getActiveForPosition($this->getParentRefId()) as $ilOrgUnitPosition) {
            $ilOrgUnitUserAssignmentTableGUI = new ilOrgUnitUserAssignmentTableGUI($this, self::CMD_INDEX,
                $ilOrgUnitPosition);
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
        $this->ctrl()->saveParameter($this, 'position_id');
        $r = $this->http()->request();
        $ilOrgUnitPosition = ilOrgUnitPosition::findOrFail($r->getQueryParams()['position_id']);
        /**
         * @var $ilOrgUnitPosition ilOrgUnitPosition
         */
        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl()->getFormAction($this));
        $confirmation->setCancel($this->txt(self::CMD_CANCEL), self::CMD_CANCEL);
        $confirmation->setConfirm($this->txt('remove_user'), self::CMD_DELETE);
        $confirmation->setHeaderText(sprintf($this->txt('msg_confirm_remove_user'), $ilOrgUnitPosition->getTitle()));
        $confirmation->addItem('usr_id', $r->getQueryParams()['usr_id'],
            ilObjUser::_lookupLogin($r->getQueryParams()['usr_id']));

        $this->setContent($confirmation->getHTML());
    }

    protected function delete(): void
    {
        $r = $this->http()->request();
        $ua = ilOrgUnitUserAssignmentQueries::getInstance()
                                            ->getAssignmentOrFail($_POST['usr_id'], $r->getQueryParams()['position_id'],
                                                $this->getParentRefId());
        $ua->delete();
        $this->main_tpl->setOnScreenMessage('success', $this->txt('remove_successful'), true);
        $this->cancel();
    }

    protected function cancel(): void
    {
        $this->ctrl()->redirect($this, self::CMD_INDEX);
    }

    public function addStaff(): void
    {
        if (!$this->dic()->access()->checkAccess("write", "", $this->getParentRefId())) {
            $this->main_tpl->setOnScreenMessage('failure', $this->txt("permission_denied"), true);
            $this->ctrl()->redirect($this, self::CMD_INDEX);
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
            $this->ctrl()->redirect($this, self::CMD_INDEX);
        }

        $position_id = isset($_POST['user_type']) ? $_POST['user_type'] : 0;

        if (!$position_id && !$position = ilOrgUnitPosition::find($position_id)) {
            $this->main_tpl->setOnScreenMessage('failure', $this->txt("user_not_found"), true);
            $this->ctrl()->redirect($this, self::CMD_INDEX);
        }
        foreach ($user_ids as $user_id) {
            ilOrgUnitUserAssignment::findOrCreateAssignment($user_id, $position_id, $this->getParentRefId());
        }

        $this->main_tpl->setOnScreenMessage('success', $this->txt("users_successfuly_added"), true);
        $this->ctrl()->redirect($this, self::CMD_INDEX);
    }

    public function addSubTabs(): void
    {
        $this->pushSubTab(self::SUBTAB_ASSIGNMENTS, $this->ctrl()
                                                         ->getLinkTarget($this, self::CMD_INDEX));
        $this->pushSubTab(self::SUBTAB_ASSIGNMENTS_RECURSIVE, $this->ctrl()
                                                                   ->getLinkTarget($this,
                                                                       self::CMD_ASSIGNMENTS_RECURSIVE));
    }
}
