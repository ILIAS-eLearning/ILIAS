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

use ILIAS\AccessControl\Log\Table;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Data\Factory as DataFactory;

/**
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 * @author       Sascha Hofmann <saschahofmann@gmx.de>
 * @version      $Id$
 * @ilCtrl_Calls ilPermissionGUI: ilObjRoleGUI, ilRepositorySearchGUI, ilObjectPermissionStatusGUI
 * @ingroup      ServicesAccessControl
 */
class ilPermissionGUI
{
    public const CMD_SAVE_POSITIONS_PERMISSIONS = 'savePositionsPermissions';
    private const CMD_PERM_POSITIONS = 'permPositions';
    private const TAB_POSITION_PERMISSION_SETTINGS = "position_permission_settings";

    private ilObjectGUI $current_obj;

    private ilRecommendedContentManager $recommended_content_manager;
    private ilOrgUnitPositionDBRepository $positionRepo;
    private ilOrgUnitPermissionDBRepository $permissionRepo;
    private ilOrgUnitOperationDBRepository $operationRepo;

    private ilObjectGUI $gui_obj;
    private ilErrorHandling $ilErr;
    private ilCtrl $ctrl;
    private ilLanguage $lng;
    private ilObjectDefinition $object_definition;
    private ilGlobalTemplateInterface $tpl;
    private ilUIService $ui_service;
    private ilRbacSystem $rbacsystem;
    private ilRbacReview $rbacreview;
    private ilRbacAdmin $rbacadmin;
    private ilObjectDataCache $objectDataCache;
    private ilTabsGUI $tabs;
    private GlobalHttpState $http;
    private Factory $refinery;
    private ilToolbarGUI $toolbar;
    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;
    private DataFactory $data_factory;
    private ilDBInterface $db;
    private ilObjUser $user;
    private ilTree $tree;

    public function __construct(ilObjectGUI $a_gui_obj)
    {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->object_definition = $DIC['objDefinition'];
        $this->ui_service = $DIC->uiService();
        $this->objectDataCache = $DIC['ilObjDataCache'];
        $this->tpl = $DIC['tpl'];
        $this->lng = $DIC['lng'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->rbacsystem = $DIC['rbacsystem'];
        $this->rbacreview = $DIC['rbacreview'];
        $this->rbacadmin = $DIC['rbacadmin'];
        $this->tabs = $DIC['ilTabs'];
        $this->ilErr = $DIC['ilErr'];
        $this->http = $DIC['http'];
        $this->refinery = $DIC['refinery'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->db = $DIC['ilDB'];
        $this->user = $DIC['ilUser'];
        $this->tree = $DIC['tree'];

        $this->data_factory = new DataFactory();
        $this->recommended_content_manager = new ilRecommendedContentManager();

        $this->lng->loadLanguageModule('rbac');
        $this->gui_obj = $a_gui_obj;
        $this->tabs->activateTab('perm_settings');
    }

    private function getPositionRepo(): ilOrgUnitPositionDBRepository
    {
        if (!isset($this->positionRepo)) {
            $dic = ilOrgUnitLocalDIC::dic();
            $this->positionRepo = $dic["repo.Positions"];
        }

        return $this->positionRepo;
    }

    private function getPermissionRepo(): ilOrgUnitPermissionDBRepository
    {
        if (!isset($this->permissionRepo)) {
            $dic = ilOrgUnitLocalDIC::dic();
            $this->permissionRepo = $dic["repo.Permissions"];
        }

        return $this->permissionRepo;
    }

    private function getOperationRepo(): ilOrgUnitOperationDBRepository
    {
        if (!isset($this->operationRepo)) {
            $dic = ilOrgUnitLocalDIC::dic();
            $this->operationRepo = $dic["repo.Operations"];
        }

        return $this->operationRepo;
    }

    /**
     * Execute command
     *
     * @return
     */
    public function executeCommand(): void
    {
        // access to all functions in this class are only allowed if edit_permission is granted
        if (!$this->rbacsystem->checkAccess("edit_permission", $this->gui_obj->getObject()->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirect($this->gui_obj);
        }
        $next_class = $this->ctrl->getNextClass($this);
        switch ($next_class) {
            case "ilobjrolegui":

                $role_id = 0;
                if ($this->http->wrapper()->query()->has('obj_id')) {
                    $role_id = $this->http->wrapper()->query()->retrieve(
                        'obj_id',
                        $this->refinery->kindlyTo()->int()
                    );
                }
                $this->ctrl->setReturn($this, 'perm');
                $this->gui_obj = new ilObjRoleGUI("", $role_id, false, false);
                $this->ctrl->forwardCommand($this->gui_obj);
                break;

            case 'ildidactictemplategui':
                $this->ctrl->setReturn($this, 'perm');
                $did = new ilDidacticTemplateGUI($this->gui_obj);
                $this->ctrl->forwardCommand($did);
                break;

            case 'ilrepositorysearchgui':
                // used for owner autocomplete
                $rep_search = new ilRepositorySearchGUI();
                $this->ctrl->forwardCommand($rep_search);
                break;

            case 'ilobjectpermissionstatusgui':
                $this->__initSubTabs("perminfo");
                $perm_stat = new ilObjectPermissionStatusGUI($this->gui_obj->getObject());
                $this->ctrl->forwardCommand($perm_stat);
                break;

            default:
                $cmd = $this->ctrl->getCmd();
                $this->$cmd();
                break;
        }
    }

    public function getCurrentObject(): object
    {
        return $this->gui_obj->getObject();
    }

    public function perm(?ilTable2GUI $table = null): void
    {
        $dtpl = new ilDidacticTemplateGUI($this->gui_obj);
        if ($dtpl->appendToolbarSwitch(
            $this->toolbar,
            $this->getCurrentObject()->getType(),
            $this->getCurrentObject()->getRefId()
        )) {
            $this->toolbar->addSeparator();
        }

        if ($this->object_definition->hasLocalRoles($this->getCurrentObject()->getType()) && !$this->isAdministrationObject()
        ) {
            $this->toolbar->setFormAction($this->ctrl->getFormActionByClass(ilDidacticTemplateGUI::class));

            if (!$this->isAdminRoleFolder()) {
                $this->toolbar->addComponent(
                    $this->ui_factory->link()->standard(
                        $this->lng->txt('rbac_add_new_local_role'),
                        $this->ctrl->getLinkTarget($this, 'displayAddRoleForm')
                    )
                );
            }
            $this->toolbar->addComponent(
                $this->ui_factory->link()->standard(
                    $this->lng->txt('rbac_import_role'),
                    $this->ctrl->getLinkTarget($this, 'displayImportRoleForm')
                )
            );
        }
        $this->__initSubTabs("perm");

        if (!$table instanceof ilTable2GUI) {
            $table = new ilObjectRolePermissionTableGUI($this, 'perm', $this->getCurrentObject()->getRefId());
        }
        $table->parse();
        $this->tpl->setContent($table->getHTML());
    }

    private function isAdminRoleFolder(): bool
    {
        return $this->getCurrentObject()->getRefId() == ROLE_FOLDER_ID;
    }

    private function isAdministrationObject(): bool
    {
        return $this->getCurrentObject()->getType() == 'adm';
    }

    /**
     * Check if node is subobject of administration folder
     */
    private function isInAdministration(): bool
    {
        return $this->tree->isGrandChild(SYSTEM_FOLDER_ID, $this->getCurrentObject()->getRefId());
    }

    public function applyFilter(): void
    {
        $table = new ilObjectRolePermissionTableGUI($this, 'perm', $this->getCurrentObject()->getRefId());
        $table->resetOffset();
        $table->writeFilterToSession();
        $this->perm($table);
    }

    public function resetFilter(): void
    {
        $table = new ilObjectRolePermissionTableGUI($this, 'perm', $this->getCurrentObject()->getRefId());
        $table->resetOffset();
        $table->resetFilter();
        $this->perm($table);
    }

    public function applyRoleFilter(array $a_roles, int $a_filter_id): array
    {
        // Always delete administrator role from view
        if (isset($a_roles[SYSTEM_ROLE_ID])) {
            unset($a_roles[SYSTEM_ROLE_ID]);
        }

        switch ($a_filter_id) {
            // all roles in context
            case ilObjectRolePermissionTableGUI::ROLE_FILTER_ALL:
                return $a_roles;

                // only global roles
            case ilObjectRolePermissionTableGUI::ROLE_FILTER_GLOBAL:
                $arr_global_roles = $this->rbacreview->getGlobalRoles();
                $arr_remove_roles = array_diff(array_keys($a_roles), $arr_global_roles);
                foreach ($arr_remove_roles as $role_id) {
                    unset($a_roles[$role_id]);
                }
                return $a_roles;

                // only local roles (all local roles in context that are not defined at ROLE_FOLDER_ID)
            case ilObjectRolePermissionTableGUI::ROLE_FILTER_LOCAL:
                $arr_global_roles = $this->rbacreview->getGlobalRoles();
                foreach ($arr_global_roles as $role_id) {
                    unset($a_roles[$role_id]);
                }
                return $a_roles;

                // only roles which use a local policy
            case ilObjectRolePermissionTableGUI::ROLE_FILTER_LOCAL_POLICY:
                $arr_local_roles = $this->rbacreview->getRolesOfObject($this->getCurrentObject()->getRefId());
                $arr_remove_roles = array_diff(array_keys($a_roles), $arr_local_roles);
                foreach ($arr_remove_roles as $role_id) {
                    unset($a_roles[$role_id]);
                }
                return $a_roles;

                // only true local role defined at current position
            case ilObjectRolePermissionTableGUI::ROLE_FILTER_LOCAL_OBJECT:
                $arr_local_roles = $this->rbacreview->getRolesOfObject($this->getCurrentObject()->getRefId(), true);
                $arr_remove_roles = array_diff(array_keys($a_roles), $arr_local_roles);
                foreach ($arr_remove_roles as $role_id) {
                    unset($a_roles[$role_id]);
                }
                return $a_roles;

            default:
                return $a_roles;
        }
    }

    protected function savePermissions(): void
    {
        $table = new ilObjectRolePermissionTableGUI($this, 'perm', $this->getCurrentObject()->getRefId());

        $roles = $this->applyRoleFilter(
            $this->rbacreview->getParentRoleIds($this->getCurrentObject()->getRefId()),
            (int) $table->getFilterItemByPostVar('role')->getValue()
        );

        // Log history
        $log_old = ilRbacLog::gatherFaPa($this->getCurrentObject()->getRefId(), array_keys((array) $roles));

        # all possible create permissions
        $possible_ops_ids = $this->rbacreview->getOperationsByTypeAndClass(
            $this->getCurrentObject()->getType(),
            'create'
        );

        # createable (activated) create permissions
        $create_types = $this->object_definition->getCreatableSubObjects(
            $this->getCurrentObject()->getType()
        );
        $createable_ops_ids = ilRbacReview::lookupCreateOperationIds(array_keys((array) $create_types));

        $post_perm = $this->http->wrapper()->post()->has('perm')
            ? $this->http->wrapper()->post()->retrieve(
                'perm',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->dictOf(
                        $this->refinery->kindlyTo()->int()
                    )
                )
            )
            : [];

        foreach ($roles as $role => $role_data) {
            if ($role_data['protected']) {
                continue;
            }

            $new_ops = array_keys((array) ($post_perm[$role] ?? []));
            $old_ops = $this->rbacreview->getRoleOperationsOnObject(
                $role,
                $this->getCurrentObject()->getRefId()
            );

            // Add operations which were enabled and are not activated.
            foreach ($possible_ops_ids as $create_ops_id) {
                if (in_array($create_ops_id, $createable_ops_ids)) {
                    continue;
                }
                if (in_array($create_ops_id, $old_ops)) {
                    $new_ops[] = $create_ops_id;
                }
            }

            $this->rbacadmin->revokePermission(
                $this->getCurrentObject()->getRefId(),
                $role
            );

            $this->rbacadmin->grantPermission(
                $role,
                array_unique($new_ops),
                $this->getCurrentObject()->getRefId()
            );
        }

        if (ilPermissionGUI::hasContainerCommands($this->getCurrentObject()->getType())) {
            $inherit_post = $this->http->wrapper()->post()->has('inherit')
                ? $this->http->wrapper()->post()->retrieve(
                    'inherit',
                    $this->refinery->kindlyTo()->dictOf(
                        $this->refinery->kindlyTo()->bool()
                    )
                )
                : [];

            foreach ($roles as $role) {
                $obj_id = (int) $role['obj_id'];
                $parent_id = (int) $role['parent'];
                // No action for local roles
                if ($parent_id === $this->getCurrentObject()->getRefId() && $role['assign'] === 'y') {
                    continue;
                }
                // Nothing for protected roles
                if ($role['protected']) {
                    continue;
                }
                // Stop local policy
                if (
                    $parent_id === $this->getCurrentObject()->getRefId()
                    && !isset($inherit_post[$obj_id])
                    && !$this->rbacreview->isBlockedAtPosition($obj_id, $this->getCurrentObject()->getRefId())
                ) {
                    ilLoggerFactory::getLogger('ac')->debug('Stop local policy for: ' . $role['obj_id']);
                    $role_obj = ilObjectFactory::getInstanceByObjId($obj_id);
                    $role_obj->setParent($this->getCurrentObject()->getRefId());
                    $role_obj->delete();
                    continue;
                }
                // Add local policy
                if (
                    $parent_id !== $this->getCurrentObject()->getRefId()
                    && isset($inherit_post[$obj_id])
                ) {
                    ilLoggerFactory::getLogger('ac')->debug('Create local policy');
                    $this->rbacadmin->copyRoleTemplatePermissions(
                        $obj_id,
                        $parent_id,
                        $this->getCurrentObject()->getRefId(),
                        $obj_id
                    );
                    ilLoggerFactory::getLogger('ac')->debug('Assign role to folder');
                    $this->rbacadmin->assignRoleToFolder($obj_id, $this->getCurrentObject()->getRefId(), 'n');
                }
            }
        }

        // Protect permissions
        if (ilPermissionGUI::hasContainerCommands($this->getCurrentObject()->getType())) {
            $protected_post = $this->http->wrapper()->post()->has('protect')
                ? $this->http->wrapper()->post()->retrieve(
                    'protect',
                    $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->int())
                )
                : [];
            foreach ($roles as $role) {
                $obj_id = (int) $role['obj_id'];
                if ($this->rbacreview->isAssignable($obj_id, $this->getCurrentObject()->getRefId())) {
                    if (isset($protected_post[$obj_id]) &&
                        !$this->rbacreview->isProtected($this->getCurrentObject()->getRefId(), $obj_id)) {
                        $this->rbacadmin->setProtected($this->getCurrentObject()->getRefId(), $obj_id, 'y');
                    } elseif (!isset($protected_post[$obj_id]) &&
                        $this->rbacreview->isProtected($this->getCurrentObject()->getRefId(), $obj_id)) {
                        $this->rbacadmin->setProtected($this->getCurrentObject()->getRefId(), $obj_id, 'n');
                    }
                }
            }
        }

        $log_new = ilRbacLog::gatherFaPa($this->getCurrentObject()->getRefId(), array_keys((array) $roles));
        $log = ilRbacLog::diffFaPa($log_old, $log_new);
        ilRbacLog::add(ilRbacLog::EDIT_PERMISSIONS, $this->getCurrentObject()->getRefId(), $log);

        $blocked_info = $this->getModifiedBlockedSettings();
        ilLoggerFactory::getLogger('ac')->debug('Blocked settings: ' . print_r($blocked_info, true));
        if ($blocked_info['num'] > 0) {
            $this->showConfirmBlockRole($blocked_info);
            return;
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'perm');
    }

    private function showConfirmBlockRole(array $a_blocked_info): void
    {
        $info = '';
        if ($a_blocked_info['new_blocked']) {
            $info .= $this->lng->txt('role_confirm_block_role_info');
            if ($a_blocked_info['new_unblocked']) {
                $info .= '<br /><br />';
            }
        }
        if ($a_blocked_info['new_unblocked']) {
            $info .= ('<br />' . $this->lng->txt('role_confirm_unblock_role_info'));
        }

        $this->tpl->setOnScreenMessage('info', $info);

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt('role_confirm_block_role_header'));
        $confirm->setConfirm($this->lng->txt('role_confirm_block_role'), 'modifyBlockRoles');
        $confirm->setCancel($this->lng->txt('cancel'), 'perm');

        foreach ($a_blocked_info['new_blocked'] as $role_id) {
            $confirm->addItem(
                'new_block[]',
                (string) $role_id,
                ilObjRole::_getTranslation(ilObject::_lookupTitle($role_id)) . ' ' . $this->lng->txt('role_blocked')
            );
        }
        foreach ($a_blocked_info['new_unblocked'] as $role_id) {
            $confirm->addItem(
                'new_unblock[]',
                (string) $role_id,
                ilObjRole::_getTranslation(ilObject::_lookupTitle($role_id)) . ' ' . $this->lng->txt('role_unblocked')
            );
        }
        $this->tpl->setContent($confirm->getHTML());
    }

    private function modifyBlockRoles(): void
    {
        $this->blockRoles(
            $this->http->wrapper()->post()->has('new_block')
                ? $this->http->wrapper()->post()->retrieve(
                    'new_block',
                    $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->int())
                )
                : []
        );
        $this->unblockRoles($this->http->wrapper()->post()->has('new_unblock')
            ? $this->http->wrapper()->post()->retrieve(
                'new_unblock',
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->int())
            )
            : []);

        $this->tpl->setOnScreenMessage('info', $this->lng->txt('settings_saved'));
        $this->ctrl->redirect($this, 'perm');
    }

    private function unblockRoles(array $roles): void
    {
        foreach ($roles as $role) {
            // delete local policy
            ilLoggerFactory::getLogger('ac')->debug('Stop local policy for: ' . $role);
            $role_obj = ilObjectFactory::getInstanceByObjId($role);
            $role_obj->setParent($this->getCurrentObject()->getRefId());
            $role_obj->delete();

            $role_obj->changeExistingObjects(
                $this->getCurrentObject()->getRefId(),
                ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES,
                ['all']
            );

            // finally set blocked status
            $this->rbacadmin->setBlockedStatus(
                $role,
                $this->getCurrentObject()->getRefId(),
                false
            );
        }
    }

    private function blockRoles(array $roles): void
    {
        foreach ($roles as $role) {
            // Set assign to 'y' only if it is a local role
            $assign = $this->rbacreview->isAssignable($role, $this->getCurrentObject()->getRefId()) ? 'y' : 'n';

            // Delete permissions
            $this->rbacadmin->revokeSubtreePermissions($this->getCurrentObject()->getRefId(), $role);

            // Delete template permissions
            $this->rbacadmin->deleteSubtreeTemplates($this->getCurrentObject()->getRefId(), $role);

            $this->rbacadmin->assignRoleToFolder(
                $role,
                $this->getCurrentObject()->getRefId(),
                $assign
            );

            // finally set blocked status
            $this->rbacadmin->setBlockedStatus(
                $role,
                $this->getCurrentObject()->getRefId(),
                true
            );
        }
    }

    public static function hasContainerCommands(string $a_type): bool
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        return $objDefinition->isContainer($a_type) && $a_type != 'root' && $a_type != 'adm' && $a_type != 'rolf';
    }

    private function displayImportRoleForm(?ilPropertyFormGUI $form = null): void
    {
        $this->tabs->clearTargets();

        if (!$form) {
            $form = $this->initImportForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    private function doImportRole(): void
    {
        $form = $this->initImportForm();
        if ($form->checkInput()) {
            try {
                // For global roles set import id to parent of current ref_id (adm)
                $imp = new ilImport($this->getCurrentObject()->getRefId());
                $imp->getMapping()->addMapping(
                    'components/ILIAS/AccessControl',
                    'rolf',
                    '0',
                    (string) $this->getCurrentObject()->getRefId()
                );

                $imp->importObject(
                    null,
                    $_FILES["importfile"]["tmp_name"],
                    $_FILES["importfile"]["name"],
                    'role'
                );
                $this->tpl->setOnScreenMessage('success', $this->lng->txt('rbac_role_imported'), true);
                $this->ctrl->redirect($this, 'perm');
                return;
            } catch (Exception $e) {
                $this->tpl->setOnScreenMessage('failure', $e->getMessage());
                $form->setValuesByPost();
                $this->displayImportRoleForm($form);
                return;
            }
        }
        $form->setValuesByPost();
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_check_input'));
        $this->displayImportRoleForm($form);
    }

    private function initImportForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('rbac_import_role'));
        $form->addCommandButton('doImportRole', $this->lng->txt('import'));
        $form->addCommandButton('perm', $this->lng->txt('cancel'));

        $zip = new ilFileInputGUI($this->lng->txt('import_file'), 'importfile');
        $zip->setRequired(true);
        $zip->setSuffixes(['zip']);
        $form->addItem($zip);

        return $form;
    }

    private function initRoleForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('role_new'));
        $form->addCommandButton('addrole', $this->lng->txt('role_new'));
        $form->addCommandButton('perm', $this->lng->txt('cancel'));

        $title = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $title->setValidationRegexp('/^(?!il_).*$/');
        $title->setValidationFailureMessage($this->lng->txt('msg_role_reserved_prefix'));
        $title->setSize(40);
        $title->setMaxLength(70);
        $title->setRequired(true);
        $form->addItem($title);

        $desc = new ilTextAreaInputGUI($this->lng->txt('description'), 'desc');
        $desc->setCols(40);
        $desc->setRows(3);
        $form->addItem($desc);

        $pro = new ilCheckboxInputGUI($this->lng->txt('role_protect_permissions'), 'pro');
        $pro->setInfo($this->lng->txt('role_protect_permissions_desc'));
        $pro->setValue("1");
        $form->addItem($pro);

        $pd = new ilCheckboxInputGUI($this->lng->txt('rbac_add_recommended_content'), 'desktop');
        $pd->setInfo(
            str_replace(
                "%1",
                $this->getCurrentObject()->getTitle(),
                $this->lng->txt('rbac_add_recommended_content_info')
            )
        );
        $pd->setValue((string) 1);
        $form->addItem($pd);

        if (!$this->isInAdministration()) {
            $rights = new ilRadioGroupInputGUI($this->lng->txt("rbac_role_rights_copy"), 'rights');
            $option = new ilRadioOption($this->lng->txt("rbac_role_rights_copy_empty"), (string) 0);
            $rights->addOption($option);

            $parent_role_ids = $this->rbacreview->getParentRoleIds($this->gui_obj->getObject()->getRefId(), true);
            $ids = [];
            foreach (array_keys($parent_role_ids) as $id) {
                $ids[] = $id;
            }

            // Sort ids
            $sorted_ids = ilUtil::_sortIds($ids, 'object_data', 'type DESC,title', 'obj_id');

            $key = 0;
            foreach ($sorted_ids as $id) {
                $par = $parent_role_ids[$id];
                if ($par["obj_id"] != SYSTEM_ROLE_ID) {
                    $option = new ilRadioOption(
                        ($par["type"] == 'role' ? $this->lng->txt('obj_role') : $this->lng->txt(
                            'obj_rolt'
                        )) . ": " . ilObjRole::_getTranslation($par["title"]),
                        (string) $par["obj_id"]
                    );
                    $option->setInfo($par["desc"] ?? '');
                    $rights->addOption($option);
                }
                $key++;
            }
            $form->addItem($rights);
        }

        // Local policy only for containers
        if ($this->object_definition->isContainer($this->getCurrentObject()->getType())) {
            $check = new ilCheckboxInputGUI($this->lng->txt("rbac_role_rights_copy_change_existing"), 'existing');
            $check->setInfo($this->lng->txt('rbac_change_existing_objects_desc_new_role'));
            $form->addItem($check);
        }
        return $form;
    }

    public function displayAddRoleForm(): void
    {
        $this->tabs->clearTargets();
        $form = $this->initRoleForm();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * adds a local role
     * This method is only called when choose the option 'you may add local roles'. This option
     * is displayed in the permission settings dialogue for an object
     * TODO: change this bahaviour
     */
    public function addRole(): void
    {
        $form = $this->initRoleForm();
        if ($form->checkInput()) {
            $new_title = $form->getInput("title");

            $role = new ilObjRole();
            $role->setTitle($new_title);
            $role->setDescription($form->getInput('desc'));
            $role->create();

            $this->rbacadmin->assignRoleToFolder($role->getId(), $this->getCurrentObject()->getRefId());

            // protect
            $this->rbacadmin->setProtected(
                $this->getCurrentObject()->getRefId(),
                $role->getId(),
                $form->getInput('pro') ? 'y' : 'n'
            );

            // copy rights
            $right_id_to_copy = (int) $form->getInput("rights");
            if ($right_id_to_copy) {
                $parentRoles = $this->rbacreview->getParentRoleIds($this->getCurrentObject()->getRefId(), true);
                $this->rbacadmin->copyRoleTemplatePermissions(
                    $right_id_to_copy,
                    $parentRoles[$right_id_to_copy]["parent"],
                    $this->getCurrentObject()->getRefId(),
                    $role->getId(),
                    false
                );

                if ($form->getInput('existing')) {
                    if ($form->getInput('pro')) {
                        $role->changeExistingObjects(
                            $this->getCurrentObject()->getRefId(),
                            ilObjRole::MODE_PROTECTED_KEEP_LOCAL_POLICIES,
                            ['all']
                        );
                    } else {
                        $role->changeExistingObjects(
                            $this->getCurrentObject()->getRefId(),
                            ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES,
                            ['all']
                        );
                    }
                }
            }

            // add to desktop items
            if ($form->getInput("desktop")) {
                $this->recommended_content_manager->addRoleRecommendation(
                    $role->getId(),
                    $this->getCurrentObject()->getRefId()
                );
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("role_added"), true);
            $this->ctrl->redirect($this, 'perm');
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }

    private function getModifiedBlockedSettings(): array
    {
        $blocked_info['new_blocked'] = [];
        $blocked_info['new_unblocked'] = [];
        $blocked_info['num'] = 0;
        $visible_block = $this->http->wrapper()->post()->has('visible_block')
            ? $this->http->wrapper()->post()->retrieve(
                'visible_block',
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->int())
            )
            : [];
        $block_post = $this->http->wrapper()->post()->has('block')
            ? $this->http->wrapper()->post()->retrieve(
                'block',
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->int())
            )
            : [];


        foreach ($visible_block as $role => $one) {
            $blocked = $this->rbacreview->isBlockedAtPosition($role, $this->getCurrentObject()->getRefId());
            if (isset($block_post[$role]) && !$blocked) {
                $blocked_info['new_blocked'][] = $role;
                $blocked_info['num']++;
            }
            if (!isset($block_post[$role]) && $blocked) {
                $blocked_info['new_unblocked'][] = $role;
                $blocked_info['num']++;
            }
        }
        return $blocked_info;
    }

    public function permPositions(): void
    {
        $perm = self::CMD_PERM_POSITIONS;
        $this->__initSubTabs($perm);

        $ref_id = $this->getCurrentObject()->getRefId();
        $table = new ilOrgUnitPermissionTableGUI($this, $perm, $ref_id);
        $table->collectData();
        $this->tpl->setContent($table->getHTML());
    }

    public function savePositionsPermissions(): void
    {
        $this->__initSubTabs(self::CMD_PERM_POSITIONS);

        $positions = $this->getPositionRepo()->getArray(null, 'id');
        $ref_id = $this->getCurrentObject()->getRefId();

        // handle local sets
        $local_post = $this->http->wrapper()->post()->has('local')
            ? $this->http->wrapper()->post()->retrieve(
                'local',
                $this->refinery->kindlyTo()->dictOf($this->refinery->kindlyTo()->int())
            )
            : [];

        foreach ($positions as $position_id) {
            if (isset($local_post[$position_id])) {
                $this->getPermissionRepo()->get($ref_id, $position_id);
            } else {
                $this->getPermissionRepo()->delete($ref_id, $position_id);
            }
        }

        $position_perm_post = $this->http->wrapper()->post()->has('position_perm')
            ? $this->http->wrapper()->post()->retrieve(
                'position_perm',
                $this->refinery->kindlyTo()->dictOf(
                    $this->refinery->kindlyTo()->dictOf(
                        $this->refinery->kindlyTo()->int()
                    )
                )
            )
            : [];

        if ($position_perm_post) { // TODO: saving an empty (enabled) set is not working, as the POST variable is empty for that set
            foreach ($position_perm_post as $position_id => $ops) {
                if (!isset($local_post[$position_id])) {
                    continue;
                }
                $ilOrgUnitPermission = $this->getPermissionRepo()->getLocalorDefault($ref_id, $position_id);
                if (!$ilOrgUnitPermission->isTemplate()) {
                    $new_ops = [];
                    foreach ($ops as $op_id => $op) {
                        $new_ops[] = $this->getOperationRepo()->getById($op_id);
                    }
                    $ilOrgUnitPermission = $ilOrgUnitPermission->withOperations($new_ops);
                    $ilOrgUnitPermission = $this->getPermissionRepo()->store($ilOrgUnitPermission);
                }
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::CMD_PERM_POSITIONS);
    }

    public function owner(): void
    {
        $this->__initSubTabs('owner');

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "owner"));
        $form->setTitle($this->lng->txt("info_owner_of_object"));

        $login = new ilTextInputGUI($this->lng->txt("login"), "owner");
        $login->setDataSource($this->ctrl->getLinkTargetByClass([get_class($this),
                                                                      'ilRepositorySearchGUI'
        ], 'doUserAutoComplete', '', true));
        $login->setRequired(true);
        $login->setSize(50);
        $login->setInfo($this->lng->txt("chown_warning"));
        $login->setValue(ilObjUser::_lookupLogin($this->gui_obj->getObject()->getOwner()));
        $form->addItem($login);
        $form->addCommandButton("changeOwner", $this->lng->txt("change_owner"));
        $this->tpl->setContent($form->getHTML());
    }

    public function changeOwner(): void
    {
        $owner = '';
        if ($this->http->wrapper()->post()->has('owner')) {
            $owner = $this->http->wrapper()->post()->retrieve(
                'owner',
                $this->refinery->kindlyTo()->string()
            );
        }
        if (!$user_id = ilObjUser::_lookupId($owner)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('user_not_known'));
            $this->owner();
            return;
        }

        // no need to change?
        if ($user_id != $this->gui_obj->getObject()->getOwner()) {
            $this->gui_obj->getObject()->setOwner($user_id);
            $this->gui_obj->getObject()->updateOwner();
            $this->objectDataCache->deleteCachedEntry($this->gui_obj->getObject()->getId());

            if (ilRbacLog::isActive()) {
                ilRbacLog::add(ilRbacLog::CHANGE_OWNER, $this->gui_obj->getObject()->getRefId(), [$user_id]);
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('owner_updated'), true);

        if (!$this->rbacsystem->checkAccess("edit_permission", $this->gui_obj->getObject()->getRefId())) {
            $this->ctrl->redirect($this->gui_obj);
            return;
        }
        $this->ctrl->redirect($this, 'owner');
    }

    private function __initSubTabs(string $a_cmd): void
    {
        $perm = $a_cmd === 'perm';
        $perm_positions = $a_cmd === ilPermissionGUI::CMD_PERM_POSITIONS;
        $info = $a_cmd === 'perminfo';
        $owner = $a_cmd === 'owner';
        $log = $a_cmd === 'log';

        $this->tabs->addSubTabTarget(
            "permission_settings",
            $this->ctrl->getLinkTarget($this, "perm"),
            "",
            "",
            "",
            $perm
        );

        if (ilOrgUnitGlobalSettings::getInstance()->isPositionAccessActiveForObject($this->gui_obj->getObject()->getId())) {
            $this->tabs->addSubTabTarget(
                self::TAB_POSITION_PERMISSION_SETTINGS,
                $this->ctrl->getLinkTarget($this, ilPermissionGUI::CMD_PERM_POSITIONS),
                "",
                "",
                "",
                $perm_positions
            );
        }

        $this->tabs->addSubTabTarget(
            "info_status_info",
            $this->ctrl->getLinkTargetByClass([get_class($this), "ilobjectpermissionstatusgui"], "perminfo"),
            "",
            "",
            "",
            $info
        );
        $this->tabs->addSubTabTarget(
            "owner",
            $this->ctrl->getLinkTarget($this, "owner"),
            "",
            "",
            "",
            $owner
        );

        if (ilRbacLog::isActive()) {
            $this->tabs->addSubTabTarget(
                "rbac_log",
                $this->ctrl->getLinkTarget($this, 'log'),
                "",
                "",
                "",
                $log
            );
        }
    }

    public function log(): void
    {
        if (!ilRbacLog::isActive()) {
            $this->ctrl->redirect($this, 'perm');
        }

        $this->__initSubTabs('log');

        $table = new Table(
            new ilRbacLog($this->db),
            $this->ui_factory,
            $this->data_factory,
            $this->lng,
            $this->ctrl,
            $this->ui_service,
            $this->object_definition,
            $this->http->request(),
            $this->rbacreview,
            $this->user,
            $this->gui_obj
        );
        $this->tpl->setContent($this->ui_renderer->render(
            $table->getTableAndFilter()
        ));
    }
}
