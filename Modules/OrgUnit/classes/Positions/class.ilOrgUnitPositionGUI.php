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

/**
 * Class ilOrgUnitPositionGUI
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilOrgUnitPositionGUI extends BaseCommands
{
    use ILIAS\Repository\BaseGUIRequest;

    public const SUBTAB_SETTINGS = 'settings';
    public const SUBTAB_PERMISSIONS = 'obj_orgunit_positions';
    public const CMD_CONFIRM_DELETION = 'confirmDeletion';
    public const CMD_ASSIGN = 'assign';
    protected ilToolbarGUI $toolbar;
    private \ilGlobalTemplateInterface $main_tpl;
    private ilCtrl $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private ilLanguage $language;

    public function __construct()
    {
        global $DIC;

        parent::__construct();

        $main_tpl = $DIC->ui()->mainTemplate();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->toolbar = $DIC->toolbar();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->language = $DIC->language();
        $this->initRequest(
            $DIC->http(),
            $DIC['refinery']
        );


        if (!ilObjOrgUnitAccess::_checkAccessPositions((int) $_GET['ref_id'])) {
            $main_tpl->setOnScreenMessage('failure', $this->language->txt("permission_denied"), true);
            $DIC->ctrl->redirectByClass(ilObjOrgUnitGUI::class);
        }
    }

    protected function getPossibleNextClasses(): array
    {
        return array(
            ilOrgUnitDefaultPermissionGUI::class,
            ilOrgUnitUserAssignmentGUI::class,
        );
    }

    protected function getActiveTabId(): string
    {
        return ilObjOrgUnitGUI::TAB_POSITIONS;
    }

    protected function index(): void
    {
        self::initAuthoritiesRenderer();
        $b = ilLinkButton::getInstance();
        $b->setUrl($this->ctrl->getLinkTarget($this, self::CMD_ADD));
        $b->setCaption('add_position');
        $this->toolbar->addButtonInstance($b);

        $table = new ilOrgUnitPositionTableGUI($this, self::CMD_INDEX);
        $this->setContent($table->getHTML());
    }

    protected function add(): void
    {
        $form = new ilOrgUnitPositionFormGUI($this, new ilOrgUnitPosition());
        $this->tpl->setContent($form->getHTML());
    }

    protected function create(): void
    {
        $form = new ilOrgUnitPositionFormGUI($this, new ilOrgUnitPosition());
        if ($form->saveObject() === true) {
            $this->main_tpl->setOnScreenMessage('success', $this->language->txt('msg_position_created'), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function edit(): void
    {
        $this->addSubTabs();
        $this->activeSubTab(self::SUBTAB_SETTINGS);
        $position = $this->getPositionFromRequest();
        $form = new ilOrgUnitPositionFormGUI($this, $position);
        $form->fillForm();
        $this->tpl->setContent($form->getHTML());
    }

    protected function update(): void
    {
        $position = $this->getPositionFromRequest();
        $form = new ilOrgUnitPositionFormGUI($this, $position);
        $form->setValuesByPost();
        if ($form->saveObject() === true) {
            $this->main_tpl->setOnScreenMessage('success', $this->language->txt('msg_position_updated'), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function assign(): void
    {
        $position = $this->getPositionFromRequest();
        if ($position->isCorePosition()) {
            $this->cancel();
        }
        $ilOrgUnitUserAssignmentQueries = ilOrgUnitUserAssignmentQueries::getInstance();
        $assignments = $ilOrgUnitUserAssignmentQueries->getUserAssignmentsOfPosition($position->getId());

        $employee_position = ilOrgUnitPosition::getCorePosition(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE);

        foreach ($assignments as $assignment) {
            ilOrgUnitUserAssignment::findOrCreateAssignment(
                $assignment->getUserId(),
                $employee_position->getId(),
                $assignment->getOrguId()
            );
            $assignment->delete();
        }

        $this->main_tpl->setOnScreenMessage('success', $this->language->txt('msg_assignment_to_employee_done'), true);
    }

    protected function confirmDeletion(): void
    {
        $position = $this->getPositionFromRequest();
        if ($position->isCorePosition()) {
            $this->cancel();
        }
        self::initAuthoritiesRenderer();
        $this->dic()->language()->loadLanguageModule('orgu');
        $position_string = $this->language->txt("position") . ": ";
        $authority_string = $$this->language->txt("authorities") . ": ";
        $user_string = $this->language->txt("user_assignments") . ": ";
        $ilOrgUnitUserAssignmentQueries = ilOrgUnitUserAssignmentQueries::getInstance();

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setCancel($this->language->txt(self::CMD_CANCEL), self::CMD_CANCEL);
        $confirmation->setConfirm($this->language->txt(self::CMD_DELETE), self::CMD_DELETE);
        $confirmation->setHeaderText($this->language->txt('msg_confirm_deletion'));
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

        $this->tpl->setContent($confirmation->getHTML());
    }

    protected function delete(): void
    {
        if ($_POST['assign_users']) {
            $this->assign();
        }
        $position = $this->getPositionFromRequest();
        $position->deleteWithAllDependencies();
        $this->main_tpl->setOnScreenMessage('success', $this->language->txt('msg_deleted'), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    protected function cancel(): void
    {
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    protected function getPositionFromRequest(): ?ActiveRecord
    {
        return ilOrgUnitPosition::find($this->str(self::AR_ID));
    }

    public static function initAuthoritiesRenderer(): string
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
        return "";
    }

    public function addSubTabs(): void
    {
        $this->ctrl->saveParameter($this, 'arid');
        $this->ctrl->saveParameterByClass(ilOrgUnitDefaultPermissionGUI::class, 'arid');
        $this->pushSubTab(self::SUBTAB_SETTINGS, $this->ctrl
                                                      ->getLinkTarget($this, self::CMD_EDIT));
        $this->pushSubTab(self::SUBTAB_PERMISSIONS, $this->ctrl
                                                         ->getLinkTargetByClass(
                                                             ilOrgUnitDefaultPermissionGUI::class,
                                                             self::CMD_INDEX
                                                         ));
    }
}
