<?php

use ILIAS\Modules\OrgUnit\ARHelper\BaseCommands;

/**
 * Class ilOrgUnitPositionGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionGUI extends BaseCommands
{
    public const SUBTAB_SETTINGS = 'settings';
    public const SUBTAB_PERMISSIONS = 'obj_orgunit_positions';
    public const CMD_CONFIRM_DELETION = 'confirmDeletion';
    public const CMD_ASSIGN = 'assign';
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        $this->main_tpl = $DIC->ui()->mainTemplate();

        if (!ilObjOrgUnitAccess::_checkAccessPositions((int) $_GET['ref_id'])) {
            $main_tpl->setOnScreenMessage('failure', $DIC->language()->txt("permission_denied"), true);
            $DIC->ctrl()->redirectByClass(ilObjOrgUnitGUI::class);
        }
    }

    final protected function getPossibleNextClasses() : array
    {
        return array(
            ilOrgUnitDefaultPermissionGUI::class,
            ilOrgUnitUserAssignmentGUI::class,
        );
    }

    final protected function getActiveTabId() : string
    {
        return ilObjOrgUnitGUI::TAB_POSITIONS;
    }

    final protected function index() : void
    {
        self::initAuthoritiesRenderer();
        $b = ilLinkButton::getInstance();
        $b->setUrl($this->ctrl()->getLinkTarget($this, self::CMD_ADD));
        $b->setCaption('add_position');
        $this->dic()->toolbar()->addButtonInstance($b);

        $table = new ilOrgUnitPositionTableGUI($this, self::CMD_INDEX);
        $this->setContent($table->getHTML());
    }

    final protected function add() : void
    {
        $form = new ilOrgUnitPositionFormGUI($this, new ilOrgUnitPosition());
        $this->tpl()->setContent($form->getHTML());
    }

    final protected function create() : void
    {
        $form = new ilOrgUnitPositionFormGUI($this, new ilOrgUnitPosition());
        if ($form->saveObject() === true) {
            $this->main_tpl->setOnScreenMessage('success', $this->txt('msg_position_created'), true);
            $this->ctrl()->redirect($this, self::CMD_INDEX);
        }

        $this->tpl()->setContent($form->getHTML());
    }

    final protected function edit() : void
    {
        $this->addSubTabs();
        $this->activeSubTab(self::SUBTAB_SETTINGS);
        $position = $this->getPositionFromRequest();
        $form = new ilOrgUnitPositionFormGUI($this, $position);
        $form->fillForm();
        $this->tpl()->setContent($form->getHTML());
    }

    final protected function update() : void
    {
        $position = $this->getPositionFromRequest();
        $form = new ilOrgUnitPositionFormGUI($this, $position);
        $form->setValuesByPost();
        if ($form->saveObject() === true) {
            $this->main_tpl->setOnScreenMessage('success', $this->txt('msg_position_updated'), true);
            $this->ctrl()->redirect($this, self::CMD_INDEX);
        }

        $this->tpl()->setContent($form->getHTML());
    }

    final protected function assign() : void
    {
        $position = $this->getPositionFromRequest();
        if ($position->isCorePosition()) {
            $this->cancel();
        }
        $ilOrgUnitUserAssignmentQueries = ilOrgUnitUserAssignmentQueries::getInstance();
        $assignments = $ilOrgUnitUserAssignmentQueries->getUserAssignmentsOfPosition($position->getId());

        $employee_position = ilOrgUnitPosition::getCorePosition(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);

        foreach ($assignments as $assignment) {
            ilOrgUnitUserAssignment::findOrCreateAssignment($assignment->getUserId(), $employee_position->getId(),
                $assignment->getOrguId());
            $assignment->delete();
        }

        $this->main_tpl->setOnScreenMessage('success', $this->txt('msg_assignment_to_employee_done'), true);
    }

    final protected function confirmDeletion() : void
    {
        $position = $this->getPositionFromRequest();
        if ($position->isCorePosition()) {
            $this->cancel();
        }
        self::initAuthoritiesRenderer();
        $this->dic()->language()->loadLanguageModule('orgu');
        $position_string = $this->dic()->language()->txt("position") . ": ";
        $authority_string = $this->dic()->language()->txt("authorities") . ": ";
        $user_string = $this->dic()->language()->txt("user_assignments") . ": ";
        $ilOrgUnitUserAssignmentQueries = ilOrgUnitUserAssignmentQueries::getInstance();

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl()->getFormAction($this));
        $confirmation->setCancel($this->txt(self::CMD_CANCEL), self::CMD_CANCEL);
        $confirmation->setConfirm($this->txt(self::CMD_DELETE), self::CMD_DELETE);
        $confirmation->setHeaderText($this->txt('msg_confirm_deletion'));
        $confirmation->addItem(self::AR_ID, $position->getId(), $position_string
            . $position->getTitle());
        // Authorities
        $authority_string .= implode(", ", $position->getAuthorities());
        $confirmation->addItem('authorities', true, $authority_string);

        // Amount uf user-assignments
        $userIdsOfPosition = $ilOrgUnitUserAssignmentQueries->getUserIdsOfPosition($position->getId());
        $ilOrgUnitUserQueries = new ilOrgUnitUserQueries();
        $usersOfPosition = $ilOrgUnitUserQueries->findAllUsersByUserIds($userIdsOfPosition);
        $userNames = $ilOrgUnitUserQueries->getAllUserNames($usersOfPosition);

        $confirmation->addItem('users', true, $user_string . implode(', ', $userNames));

        $checkbox_assign_users = new ilCheckboxInputGUI('', 'assign_users');
        $checkbox_assign_users->setChecked(true);
        $checkbox_assign_users->setValue(1);
        $checkbox_assign_users->setOptionTitle('Assign affected users to employee role');
        $confirmation->addItem('assign_users', '', $checkbox_assign_users->render());

        $this->tpl()->setContent($confirmation->getHTML());
    }

    final protected function delete() : void
    {
        if ($_POST['assign_users']) {
            $this->assign();
        }
        $position = $this->getPositionFromRequest();
        $position->deleteWithAllDependencies();
        $this->main_tpl->setOnScreenMessage('success', $this->txt('msg_deleted'), true);
        $this->ctrl()->redirect($this, self::CMD_INDEX);
    }

    final protected function cancel() : void
    {
        $this->ctrl()->redirect($this, self::CMD_INDEX);
    }

    final protected function getARIdFromRequest() : array
    {
        $get = $this->dic()->http()->request()->getQueryParams()[self::AR_ID];
        $post = $this->dic()->http()->request()->getParsedBody()[self::AR_ID];

        return $post ? $post : $get;
    }

    final protected function getPositionFromRequest() : ilOrgUnitPosition
    {
        return ilOrgUnitPosition::find($this->getARIdFromRequest());
    }

    final public static function initAuthoritiesRenderer() : string
    {
        $lang = $GLOBALS['DIC']->language();
        $lang->loadLanguageModule('orgu');
        $lang_keys = array(
            'in',
            'scope_' . ilOrgUnitAuthority::SCOPE_SAME_ORGU,
            'scope_' . ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS,
            'over_' . ilOrgUnitAuthority::OVER_EVERYONE,
        );
        $t = array();
        foreach ($lang_keys as $key) {
            $t[$key] = $lang->txt($key);
        }

        ilOrgUnitAuthority::replaceNameRenderer(function ($id) use ($t) {
            /**
             * @var $ilOrgUnitAuthority ilOrgUnitAuthority
             */
            $ilOrgUnitAuthority = ilOrgUnitAuthority::find($id);

            switch ($ilOrgUnitAuthority->getScope()) {
                case ilOrgUnitAuthority::SCOPE_SAME_ORGU:
                case ilOrgUnitAuthority::SCOPE_ALL_ORGUS:
                case ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS:
                default:
                    $in_txt = $t["scope_" . $ilOrgUnitAuthority->getScope()];
                    break;
            }

            switch ($ilOrgUnitAuthority->getOver()) {
                case ilOrgUnitAuthority::OVER_EVERYONE:
                    $over_txt = $t["over_" . $ilOrgUnitAuthority->getOver()];
                    break;
                default:
                    $over_txt = ilOrgUnitPosition::findOrGetInstance($ilOrgUnitAuthority->getOver())
                                                 ->getTitle();
                    break;
            }

            return " " . $t["over"] . " " . $over_txt . " " . $t["in"] . " " . $in_txt;
        });
    }

    final public function addSubTabs() : void
    {
        $this->ctrl()->saveParameter($this, 'arid');
        $this->ctrl()->saveParameterByClass(ilOrgUnitDefaultPermissionGUI::class, 'arid');
        $this->pushSubTab(self::SUBTAB_SETTINGS, $this->ctrl()
                                                      ->getLinkTarget($this, self::CMD_EDIT));
        $this->pushSubTab(self::SUBTAB_PERMISSIONS, $this->ctrl()
                                                         ->getLinkTargetByClass(ilOrgUnitDefaultPermissionGUI::class,
                                                             self::CMD_INDEX));
    }
}
