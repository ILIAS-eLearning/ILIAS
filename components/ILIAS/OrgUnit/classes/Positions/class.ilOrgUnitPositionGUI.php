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

use ILIAS\components\OrgUnit\ARHelper\BaseCommands;
use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;

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
    protected \ILIAS\UI\Component\Link\Factory $link_factory;
    protected \ilOrgUnitPositionDBRepository $positionRepo;
    protected \ilOrgUnitUserAssignmentDBRepository $assignmentRepo;
    protected \ilOrgUnitPermissionDBRepository $permissionRepo;
    protected \ilObjectDefinition $objectDefinition;
    protected ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper $post;


    public function __construct()
    {
        $dic = ilOrgUnitLocalDIC::dic();
        $this->positionRepo = $dic["repo.Positions"];
        $this->assignmentRepo = $dic["repo.UserAssignments"];
        $this->permissionRepo = $dic["repo.Permissions"];

        $to_int = $dic['refinery']->kindlyTo()->int();
        $ref_id = $dic['query']->retrieve('ref_id', $to_int);
        $this->link_factory = $dic['ui.factory']->link();

        parent::__construct();

        global $DIC;
        $this->toolbar = $DIC->toolbar();
        $this->objectDefinition = $DIC["objDefinition"];

        $this->initRequest(
            $DIC->http(),
            $dic['refinery']
        );

        if (!ilObjOrgUnitAccess::_checkAccessPositions($ref_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirectByClass(ilObjOrgUnitGUI::class);
        }

        $this->post = $DIC->http()->wrapper()->post();
    }

    protected function getPossibleNextClasses(): array
    {
        return array(
            ilOrgUnitUserAssignmentGUI::class,
        );
    }

    protected function getActiveTabId(): string
    {
        return ilObjOrgUnitGUI::TAB_POSITIONS;
    }

    protected function index(): void
    {
        $url = $this->getSinglePosLinkTarget(self::CMD_ADD, 0);
        $link = $this->link_factory->standard(
            $this->lng->txt('add_position'),
            $url
        );
        $this->toolbar->addComponent($link);

        $table = $this->getTable()->withRequest($this->request);
        $this->tpl->setContent($this->ui_renderer->render($table));
    }

    protected function add(): void
    {
        $position = $this->positionRepo->create();
        $form = new ilOrgUnitPositionFormGUI($this, $position);
        $this->tpl->setContent($form->getHTML());
    }

    protected function create(): void
    {
        $this->redirectIfCancelled();
        $form = new ilOrgUnitPositionFormGUI($this, $this->positionRepo->create());
        if ($form->saveObject() === true) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_position_created'), true);
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
        $this->redirectIfCancelled();
        $position = $this->getPositionFromRequest();
        $form = new ilOrgUnitPositionFormGUI($this, $position);
        $form->setValuesByPost();
        if ($form->saveObject() === true) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_position_updated'), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        $this->tpl->setContent($form->getHTML());
    }

    protected function defaultPermissions(): void
    {
        $this->addSubTabs();
        $this->activeSubTab(self::SUBTAB_PERMISSIONS);
        $form = $this->getDefaultPermissionsForm($this->getRowIdFromQuery());
        $this->tpl->setContent($this->ui_renderer->render($form));
    }

    protected function updateDefaultPermissions(): void
    {
        $form = $this->getDefaultPermissionsForm($this->getRowIdFromQuery())
            ->withRequest($this->request);
        if ($form->getData()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_success_permission_saved'), true);
            $this->defaultPermissions();
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_success_permission_not_saved'), true);
            $this->tpl->setContent($this->ui_renderer->render($form));
        }
    }

    protected function getDefaultPermissionsForm(int $position_id): StandardForm
    {
        $sections = [];
        $sections[] = $this->ui_factory->input()->field()->section(
            [],
            $this->lng->txt("form_title_org_default_permissions_update")
        );

        $form_action = $this->getSinglePosLinkTarget('updateDefaultPermissions', $position_id);
        $permissions = $this->permissionRepo->getDefaultsForActiveContexts($position_id);
        $permission_repo = $this->permissionRepo;
        foreach ($permissions as $perm) {
            $fields = [];
            $operations = $perm->getPossibleOperations();
            foreach ($operations as $operation) {
                $fields[$operation->getOperationId()] = $this->ui_factory->input()->field()
                    ->checkbox($this->lng->txt("org_op_{$operation->getOperationString()}"))
                    ->withValue(
                        $perm->isOperationIdSelected($operation->getOperationId())
                    );
            }

            $context = $perm->getContext()->getContext();
            $sections[$perm->getId()] = $this->ui_factory->input()->field()
                ->section($fields, $this->getTitleForFormHeaderByContext($context))
                ->withAdditionalTransformation(
                    $this->refinery->custom()->transformation(
                        function ($v) use ($operations, $perm, $permission_repo) {
                            $v = array_filter($v);
                            $nu_ops = array_filter($operations, fn($o) => array_key_exists($o->getOperationId(), $v));
                            $protected = $perm->isProtected();
                            //$perm = $permission_repo->update($perm);
                            $perm = $perm->withOperations($nu_ops)->withProtected(false);
                            $permission_repo->store($perm);
                            //$perm=$perm->withProtected($protected);
                            //$permission_repo->store($perm);
                            return true;
                        }
                    )
                );
        }

        return $this->ui_factory->input()->container()->form()->standard($form_action, $sections);
    }

    protected function getTitleForFormHeaderByContext(string $context)
    {
        $lang_code = "obj_{$context}";
        if ($this->objectDefinition->isPlugin($context)) {
            return ilObjectPlugin::lookupTxtById($context, $lang_code);
        }
        return $this->lng->txt($lang_code);
    }

    protected function redirectIfCancelled()
    {
        if ($this->post->has('cmd')) {
            $cmd = $this->post->retrieve(
                'cmd',
                $this->refinery->custom()->transformation(
                    fn($v) => array_key_first($v)
                )
            );
            if ($cmd === self::CMD_CANCEL) {
                $url = $this->url_builder
                    ->withParameter($this->action_token, self::CMD_INDEX)
                    ->buildURI()
                    ->__toString();
                $this->ctrl->redirectToURL($url);
            }
        }
    }

    protected function assign(int $position_id): void
    {
        $position = $this->positionRepo->getSingle($position_id, 'id');
        if ($position->isCorePosition()) {
            $this->cancel();
        }

        $employee_position = $this->positionRepo->getSingle(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE, 'core_identifier');
        $assignments = $this->assignmentRepo->getByPosition($position->getId());
        foreach ($assignments as $assignment) {
            $this->assignmentRepo->store($assignment->withPositionId($employee_position->getId()));
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_assignment_to_employee_done'), true);
    }

    protected function confirmDeletion(): void
    {
        $position = $this->getPositionFromRequest();
        if ($position->isCorePosition()) {
            $this->cancel();
        }
        $this->lng->loadLanguageModule('orgu');
        $position_string = $this->lng->txt("position") . ": ";
        $authority_string = $this->lng->txt("authorities") . ": ";
        $user_string = $this->lng->txt("user_assignments") . ": ";

        $confirmation = new ilConfirmationGUI();
        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->setCancel($this->lng->txt(self::CMD_CANCEL), self::CMD_CANCEL);
        $confirmation->setConfirm($this->lng->txt(self::CMD_DELETE), self::CMD_DELETE);
        $confirmation->setHeaderText($this->lng->txt('msg_confirm_deletion'));
        $confirmation->addItem(self::AR_ID, (string) $position->getId(), $position_string
            . $position->getTitle());
        // Authorities
        $authority_string .= implode(", ", $this->getAuthorityDescription($position->getAuthorities()));
        $confirmation->addItem('authorities', '', $authority_string);

        // Amount uf user-assignments
        $userIdsOfPosition = $this->assignmentRepo->getUsersByPosition($position->getId());
        $ilOrgUnitUserQueries = new ilOrgUnitUserQueries();
        $usersOfPosition = $ilOrgUnitUserQueries->findAllUsersByUserIds($userIdsOfPosition);
        $userNames = $ilOrgUnitUserQueries->getAllUserNames($usersOfPosition);

        $confirmation->addItem('users', '', $user_string . implode(', ', $userNames));

        $checkbox_assign_users = new ilCheckboxInputGUI('', 'assign_users');
        $checkbox_assign_users->setChecked(true);
        $checkbox_assign_users->setValue('1');
        $checkbox_assign_users->setOptionTitle('Assign affected users to employee role');
        $confirmation->addItem('assign_users', '', $checkbox_assign_users->render());

        $this->tpl->setContent($confirmation->getHTML());
    }

    protected function delete(): void
    {
        $position_id = $this->post->retrieve(
            self::AR_ID,
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->int(),
                $this->refinery->always(null)
            ])
        );

        if ($position_id === null) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_position_delete_fail'), true);
            $this->ctrl->redirect($this, self::CMD_INDEX);
        }

        if ($this->post->has('assign_users')
            && $this->post->retrieve(
                'assign_users',
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->bool(),
                    $this->refinery->always(false)
                ])
            )
        ) {
            $this->assign($position_id);
        }
        $this->positionRepo->delete($position_id);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_deleted'), true);
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    protected function cancel(): void
    {
        $this->ctrl->redirect($this, self::CMD_INDEX);
    }

    protected function getPositionFromRequest(): ilOrgUnitPosition
    {
        $id = $this->getRowIdFromQuery();
        return $this->positionRepo->getSingle($id, 'id');
    }

    public function getSinglePosLinkTarget(string $action, ?int $pos_id = null): string
    {
        $target_id = $pos_id !== null ? [$pos_id] : [$this->getRowIdFromQuery()];
        return $this->url_builder
            ->withParameter($this->row_id_token, $target_id)
            ->withParameter($this->action_token, $action)
            ->buildURI()->__toString();
    }

    public function addSubTabs(): void
    {
        $this->pushSubTab(
            self::SUBTAB_SETTINGS,
            $this->getSinglePosLinkTarget(self::CMD_EDIT)
        );
        $this->pushSubTab(
            self::SUBTAB_PERMISSIONS,
            $this->getSinglePosLinkTarget(self::CMD_DEFAULT_PERMISSIONS)
        );
    }

    protected function getTable(): Table\Data
    {
        $columns = [
            'title' => $this->ui_factory->table()->column()->text($this->lng->txt("title")),
            'description' => $this->ui_factory->table()->column()->text($this->lng->txt("description")),
            'authorities' => $this->ui_factory->table()->column()->status($this->lng->txt("authorities"))
                ->withIsSortable(false),
        ];

        $actions = [
            'edit' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('edit'),
                $this->url_builder->withParameter($this->action_token, "edit"),
                $this->row_id_token
            ),
            'delete' => $this->ui_factory->table()->action()->single(
                $this->lng->txt('delete'),
                $this->url_builder->withParameter($this->action_token, "confirmDeletion"),
                $this->row_id_token
            ),
        ];

        return $this->ui_factory->table()
            ->data('', $columns, $this->positionRepo)
            ->withId('orgu_positions')
            ->withActions($actions);
    }

    /**
     * Returns descriptions for authorities as an array of strings
     *
     * @param ilOrgUnitAuthority[] $authorities
     */
    private function getAuthorityDescription(array $authorities): array
    {
        $lang = $this->lng;
        $lang->loadLanguageModule('orgu');
        $lang_keys = array(
            'in',
            'over',
            'scope_' . ilOrgUnitAuthority::SCOPE_SAME_ORGU,
            'scope_' . ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS,
            'over_' . ilOrgUnitAuthority::OVER_EVERYONE,
        );
        $t = [];
        foreach ($lang_keys as $key) {
            $t[$key] = $lang->txt($key);
        }

        $authority_description = [];
        foreach ($authorities as $authority) {
            switch ($authority->getOver()) {
                case ilOrgUnitAuthority::OVER_EVERYONE:
                    $over_txt = $t["over_" . $authority->getOver()];
                    break;
                default:
                    $over_txt = $this->positionRepo
                        ->getSingle($authority->getOver(), 'id')
                        ->getTitle();
                    break;
            }

            $authority_description[] = " " . $t["over"] . " " . $over_txt . " " . $t["in"] . " " . $t["scope_" . $authority->getScope()];
        }

        return $authority_description;
    }
}
