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
 *********************************************************************/

declare(strict_types=1);

use ILIAS\User\UserGUIRequest;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;

/**
 * Class ilUserStartingPointGUI
 *
 * @author Jesús López <lopez@leifos.com>
 * @ilCtrl_Calls ilUserStartingPointGUI:
 */
class ilUserStartingPointGUI
{
    private ilLogger $log;
    private ilLanguage $lng;
    private ilSetting $settings;
    private ilGlobalTemplateInterface $tpl;
    private ilToolbarGUI $toolbar;
    private ilTabsGUI $tabs;
    private ilCtrl $ctrl;
    private ilTree $tree;
    private ilObjUser $user;
    private ilDBInterface $db;
    private ilRbacReview $rbac_review;
    private ilRbacSystem $rbac_system;
    private UIFactory $ui_factory;
    private Renderer $ui_renderer;
    private UserGUIRequest $user_request;
    private ilUserStartingPointRepository $starting_point_repository;

    private int $parent_ref_id;

    public function __construct(int $a_parent_ref_id)
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->log = ilLoggerFactory::getLogger("user");
        $this->lng = $DIC['lng'];
        $this->settings = $DIC['ilSetting'];
        $this->tpl = $DIC['tpl'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->tabs = $DIC['ilTabs'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->tree = $DIC['tree'];
        $this->user = $DIC['ilUser'];
        $this->db = $DIC['ilDB'];
        $this->rbac_review = $DIC['rbacreview'];
        $this->rbac_system = $DIC['rbacsystem'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->user_request = new UserGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->starting_point_repository = new ilUserStartingPointRepository(
            $this->user,
            $this->db,
            $this->tree,
            $this->rbac_review,
            $this->settings
        );

        $this->parent_ref_id = $a_parent_ref_id;

        $this->lng->loadLanguageModule("administration");
        $this->lng->loadLanguageModule("dateplaner");
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        if ($cmd == 'roleStartingPointform' || !$cmd) {
            $cmd = 'initRoleStartingPointForm';
        }

        $this->$cmd();
    }

    /**
     * table form to set up starting points depends of user roles
     */
    public function startingPoints(): void
    {
        $this->toolbar->addComponent(
            $this->ui_factory->link()->standard(
                $this->lng->txt('create_starting_point'),
                $this->ctrl->getLinkTarget($this, "roleStartingPointform")
            )
        );

        $tbl = new ilUserRoleStartingPointTableGUI(
            $this,
            $this->starting_point_repository,
            $this->rbac_review,
            $this->ui_factory,
            $this->ui_renderer,
        );

        $this->tpl->setContent($tbl->getHTML());
    }

    public function initUserStartingPointForm(ilPropertyFormGUI $form = null): void
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getUserStartingPointForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    public function initRoleStartingPointForm(ilPropertyFormGUI $form = null): void
    {
        if (!($form instanceof ilPropertyFormGUI)) {
            $form = $this->getRoleStartingPointForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    protected function getUserStartingPointForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();

        // starting point: personal
        $startp = new ilCheckboxInputGUI($this->lng->txt('user_chooses_starting_page'), 'usr_start_pers');
        $startp->setInfo($this->lng->txt('adm_user_starting_point_personal_info'));
        $startp->setChecked($this->starting_point_repository->isPersonalStartingPointEnabled());

        $form->addItem($startp);

        $form->addCommandButton('saveUserStartingPoint', $this->lng->txt('save'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    /**
     * @return ilPropertyFormGUI
     */
    protected function getRoleStartingPointForm(): ilPropertyFormGUI
    {
        if (!$this->rbac_system->checkAccess('write', $this->parent_ref_id)) {
            $this->error->raiseError(
                $this->lng->txt('msg_no_perm_read'),
                $this->error->FATAL
            );
        }
        $form = new ilPropertyFormGUI();
        $this->ctrl->saveParameter($this, ['spid']);

        $starting_point_id = $this->user_request->getStartingPointId();
        $starting_point = $this->getCurrentStartingPointOrNullForStartingPointForm($starting_point_id);
        $starting_point_type = $this->getCurrentTypeForStartingPointForm($starting_point);
        $req_role_id = $this->user_request->getRoleId();

        foreach ($this->getFormTypeSpecificStartingPointFormParts($starting_point_id, $req_role_id) as $input) {
            $form->addItem(
                $input
            );
        }

        $si = $this->getStartingPointSelectionInput($starting_point);
        $si->setValue((string) $starting_point_type);
        $form->addItem($si);

        // save and cancel commands
        $form->addCommandButton('saveStartingPoint', $this->lng->txt('save'));
        $form->addCommandButton('startingPoints', $this->lng->txt('cancel'));

        $form->setTitle($this->lng->txt('starting_point_settings'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    private function getCurrentStartingPointOrNullForStartingPointForm(?int $starting_point_id): ?ilUserStartingPoint
    {
        if ($starting_point_id === null) {
            return null;
        }

        return $this->starting_point_repository->getStartingPointById(
            $starting_point_id
        );
    }

    private function getCurrentTypeForStartingPointForm(?ilUserStartingPoint $starting_point): ?int
    {
        if ($starting_point === null) {
            return null;
        }

        return $starting_point->getStartingPointType();
    }

    private function getFormTypeSpecificStartingPointFormParts(?int $spoint_id, ?int $req_role_id): Generator
    {   //edit no default
        if ($spoint_id === null) {
            yield $this->getCreateFormSpecificInputs();
        } else {
            yield from $this->getEditFormSpecificInputs($spoint_id, $req_role_id);
        }
    }

    private function getCreateFormSpecificInputs(): ilRadioGroupInputGUI
    {
        $roles = $this->starting_point_repository->getGlobalRolesWithoutStartingPoint();


        // role type
        $radg = new ilRadioGroupInputGUI($this->lng->txt('role'), 'role_type');
        $radg->setValue('1');
        if ($roles !== []) {
            $radg->setValue('0');
            $op1 = new ilRadioOption($this->lng->txt('user_global_role'), '0');
            $radg->addOption($op1);

            $role_options = [];
            foreach ($roles as $role) {
                $role_options[$role['id']] = $role['title'];
            }
            $si_roles = new ilSelectInputGUI($this->lng->txt('roles_without_starting_point'), 'role');
            $si_roles->setOptions($role_options);
            $op1->addSubItem($si_roles);
        }

        $op2 = new ilRadioOption($this->lng->txt('user_local_role'), '1');
        $radg->addOption($op2);
        $role_search = new ilRoleAutoCompleteInputGUI('', 'role_search', $this, 'addRoleAutoCompleteObject');
        $role_search->setSize(40);
        $op2->addSubItem($role_search);
        return $radg;
    }

    private function getEditFormSpecificInputs(int $spoint_id, int $req_role_id): array
    {
        $title = $this->lng->txt('default');
        if ($spoint_id !== $this->starting_point_repository->getDefaultStartingPointID()) {
            $role = new ilObjRole($req_role_id);
            $title = $role->getTitle();
        }

        $inputs = [];
        $inputs[0] = new ilNonEditableValueGUI($this->lng->txt('editing_this_role'), 'role_disabled');
        $inputs[0]->setValue($title);

        $inputs[1] = new ilHiddenInputGUI('role');
        $inputs[1]->setValue((string) $req_role_id);

        $inputs[2] = new ilHiddenInputGUI('start_point_id');
        $inputs[2]->setValue((string) $spoint_id);

        return $inputs;
    }

    private function getStartingPointSelectionInput(?ilUserStartingPoint $st_point): ilRadioGroupInputGUI
    {
        $si = new ilRadioGroupInputGUI($this->lng->txt('adm_user_starting_point'), 'start_point');
        $si->setRequired(true);
        $si->setInfo($this->lng->txt('adm_user_starting_point_info'));
        $valid = array_keys($this->starting_point_repository->getPossibleStartingPoints());
        foreach ($this->starting_point_repository->getPossibleStartingPoints(true) as $value => $caption) {
            $si->addOption(
                $this->getStartingPointSelectionOption($value, $caption, $st_point, $valid)
            );
        }

        return $si;
    }

    private function getStartingPointSelectionOption(
        int $value,
        string $caption,
        ?ilUserStartingPoint $st_point,
        array $valid
    ): ilRadioOption {
        $opt = new ilRadioOption($this->lng->txt($caption), (string) $value);

        if ($value === ilUserStartingPointRepository::START_PD_CALENDAR) {
            $opt->addSubItem(
                $this->getCalenderSubInputs($st_point)
            );
        }

        if ($value === ilUserStartingPointRepository::START_REPOSITORY_OBJ) {
            $opt->addSubItem(
                $this->getRepositoryObjectInput($st_point)
            );
        }

        if (!in_array($value, $valid)) {
            $opt->setInfo($this->lng->txt('adm_user_starting_point_invalid_info'));
        }

        return $opt;
    }

    private function getCalenderSubInputs(?ilUserStartingPoint $st_point): ilRadioGroupInputGUI
    {
        $default_cal_view = new ilRadioGroupInputGUI($this->lng->txt('cal_def_view'), 'user_calendar_view');
        $default_cal_view->setRequired(true);

        $day = new ilRadioOption($this->lng->txt('day'), (string) ilCalendarSettings::DEFAULT_CAL_DAY);
        $default_cal_view->addOption($day);
        $week = new ilRadioOption($this->lng->txt('week'), (string) ilCalendarSettings::DEFAULT_CAL_WEEK);
        $default_cal_view->addOption($week);
        $month = new ilRadioOption($this->lng->txt('month'), (string) ilCalendarSettings::DEFAULT_CAL_MONTH);
        $default_cal_view->addOption($month);

        $list = new ilRadioOption($this->lng->txt('cal_list'), (string) ilCalendarSettings::DEFAULT_CAL_LIST);

        $cal_periods = new ilSelectInputGUI($this->lng->txt('cal_list'), 'user_cal_period');
        $cal_periods->setOptions([
            ilCalendarAgendaListGUI::PERIOD_DAY => '1 ' . $this->lng->txt('day'),
            ilCalendarAgendaListGUI::PERIOD_WEEK => '1 ' . $this->lng->txt('week'),
            ilCalendarAgendaListGUI::PERIOD_MONTH => '1 ' . $this->lng->txt('month'),
            ilCalendarAgendaListGUI::PERIOD_HALF_YEAR => '6 ' . $this->lng->txt('months')
        ]);
        $cal_periods->setRequired(true);

        if ($st_point !== null) {
            $default_cal_view->setValue((string) $st_point->getCalendarView());
            $cal_periods->setValue((string) $st_point->getCalendarPeriod());
        }

        $list->addSubItem($cal_periods);
        $default_cal_view->addOption($list);

        return $default_cal_view;
    }

    private function getRepositoryObjectInput(?ilUserStartingPoint $st_point): ilTextInputGUI
    {
        $repobj_id = new ilTextInputGUI($this->lng->txt('adm_user_starting_point_ref_id'), 'start_object');
        $repobj_id->setRequired(true);
        $repobj_id->setSize(5);

        if ($st_point !== null) {
            $start_ref_id = $st_point->getStartingObject();
            $repobj_id->setValue($start_ref_id);
        }

        if (isset($start_ref_id)) {
            $start_obj_id = ilObject::_lookupObjId($start_ref_id);
            if ($start_obj_id) {
                $repobj_id->setInfo($this->lng->txt('obj_' . ilObject::_lookupType($start_obj_id)) .
                    ': ' . ilObject::_lookupTitle($start_obj_id));
            }
        }

        return $repobj_id;
    }

    public function addRoleAutoCompleteObject(): void
    {
        ilRoleAutoCompleteInputGUI::echoAutoCompleteList();
    }

    protected function saveUserStartingPoint(): void
    {
        if (!$this->rbac_system->checkAccess('write', $this->parent_ref_id)) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->FATAL);
        }

        $form = $this->getUserStartingPointForm();

        if ($form->checkInput()) {
            $this->starting_point_repository->togglePersonalStartingPointActivation((bool) $form->getInput('usr_start_pers'));
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, 'startingPoints');
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_error'), true);
        $this->ctrl->redirect($this, 'startingPoints');
    }

    /**
     * store starting point from the form
     */
    protected function saveStartingPoint(): void
    {
        if (!$this->rbac_system->checkAccess('write', $this->parent_ref_id)) {
            $this->error->raiseError($this->lng->txt('msg_no_perm_read'), $this->error->FATAL);
        }

        $start_point_id = $this->user_request->getStartingPointId();

        //add from form
        $form = $this->getRoleStartingPointForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
            return;
        }

        $starting_point = $this->starting_point_repository->getStartingPointById(
            $start_point_id
        );


        $role_id = $this->user_request->getRoleId();

        if ($form->getInput('role_type') === '1'
            && ($role_id === null || $role_id < 1)) {
            $parser = new ilQueryParser($form->getInput('role_search'));

            // TODO: Handle minWordLength
            $parser->setMinWordLength(1);
            $parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
            $parser->parse();

            $object_search = new ilLikeObjectSearch($parser);
            $object_search->setFilter(['role']);
            $res = $object_search->performSearch();

            $entries = $res->getEntries();

            if ($entries === []) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('obj_ref_id_not_exist'), true);
                $form->setValuesByPost();
                $this->tpl->setContent($form->getHTML());
                return;
            }

            if (count($entries) > 1) {
                $this->showRoleSelection(
                    $form->getInput('role'),
                    $form->getInput('role_search'),
                    $form->getInput('start_point'),
                    $form->getInput('start_object')
                );
                return;
            }

            if (count($entries) === 1) {
                $role = current($entries);
                $role_id = $role['obj_id'];
            }
        }

        if ($role_id === 0) {
            $role_id = $form->getInput('role');
        }

        if ($role_id !== 0) {
            $starting_point->setRuleTypeRoleBased();
            $rules = ['role_id' => $role_id];
            $starting_point->setRuleOptions(serialize($rules));
        }

        $starting_point->setStartingPointType((int) $form->getInput('start_point'));

        $obj_id = (int) $form->getInput('start_object');
        $cal_view = (int) $form->getInput('user_calendar_view');
        $cal_period = (int) $form->getInput('user_cal_period');


        if ($starting_point->getStartingPointType() === ilUserStartingPointRepository::START_REPOSITORY_OBJ
            && (ilObject::_lookupObjId($obj_id) === 0 || $this->tree->isDeleted($obj_id))) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('obj_ref_id_not_exist'), true);
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
            return;
        }
        $starting_point->setStartingObject($obj_id);

        if ($starting_point->getStartingPointType() === ilUserStartingPointRepository::START_PD_CALENDAR) {
            $starting_point->setCalendarView($cal_view);
            $starting_point->setCalendarPeriod($cal_period);
        }

        if ($start_point_id !== null) {
            $this->starting_point_repository->update($starting_point);
        } else {
            $this->starting_point_repository->save($starting_point);
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect($this, 'startingPoints');
    }

    private function showRoleSelection(
        string $role,
        string $role_search,
        string $start_point,
        string $start_object
    ): void {
        $parser = new ilQueryParser($role_search);
        $parser->setMinWordLength(1);
        $parser->setCombination(ilQueryParser::QP_COMBINATION_AND);
        $parser->parse();

        $object_search = new ilLikeObjectSearch($parser);
        $object_search->setFilter(['role']);
        $res = $object_search->performSearch();

        $entries = $res->getEntries();

        $table = new ilRoleSelectionTableGUI($this, 'saveStartingPoint');
        $table->setLimit(9999);
        $table->disable('sort');
        $table->addHiddenInput('role_search', $role_search);
        $table->addHiddenInput('start_point', $start_point);
        $table->addHiddenInput('start_object', $start_object);
        $table->addHiddenInput('role', $role);
        $table->addHiddenInput('role_type', '1');
        $table->setTitle($this->lng->txt('user_role_selection'));
        $table->addMultiCommand('saveStartingPoint', $this->lng->txt('user_choose_role'));
        $table->parse($entries);

        $this->tpl->setContent($table->getHTML());
    }

    public function saveOrder(): void
    {
        if (!$this->rbac_system->checkAccess('write', $this->parent_ref_id)) {
            throw new ilPermissionException($this->lng->txt('msg_no_perm_read'));
        }

        $positions = $this->user_request->getPositions();
        if (count($positions) > 0) {
            $this->starting_point_repository->saveOrder($positions);
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect($this, 'startingPoints');
    }

    protected function deleteStartingPoint(): void
    {
        if (!$this->rbac_system->checkAccess('write', $this->parent_ref_id)) {
            throw new ilPermissionException($this->lng->txt('msg_no_perm_read'));
        }

        $spoint_id = $this->user_request->getStartingPointId();
        $req_role_id = $this->user_request->getRoleId();

        if ($req_role_id && is_numeric($spoint_id)) {
            $sp = $this->starting_point_repository->getStartingPointById(
                $spoint_id
            );
            $this->starting_point_repository->delete($sp->getId());
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_spoint_not_modified'), true);
        }
        $this->ctrl->redirect($this, 'startingPoints');
    }
}
