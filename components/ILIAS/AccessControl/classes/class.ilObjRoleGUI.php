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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Modal\Interruptive as InterruptiveModal;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;

/**
 * Class ilObjRoleGUI
 * @author       Stefan Meyer <smeyer@ilias@gmx.de>
 * @author       Sascha Hofmann <saschahofmann@gmx.de>
 * @version      $Id$
 * @ilCtrl_Calls ilObjRoleGUI: ilRepositorySearchGUI, ilExportGUI, ilRecommendedContentRoleConfigGUI
 * @ingroup      ServicesAccessControl
 */
class ilObjRoleGUI extends ilObjectGUI
{
    private const FORM_KEY_TITLE = 'title';
    private const FORM_KEY_DESCRIPTION = 'description';
    private const FORM_KEY_ILIAS_ID = 'ilias_id';
    private const FORM_KEY_ON_REGISTRATION_FORM = 'on_registration_form';
    private const FORM_KEY_ALLOW_LOCAL_USER_ASSIGNMENT = 'allow_local_user_assignment';
    private const FORM_KEY_PROTECT = 'protect';

    protected int $obj_ref_id = 0;
    protected int $obj_obj_id = 0;
    protected string $obj_obj_type = '';
    protected string $container_type = '';
    protected int $role_id = 0;
    protected ilHelpGUI $help;
    private ilLogger $logger;
    private GlobalHttpState $http;
    protected Factory $refinery;
    protected UIFactory $ui_factory;
    protected Renderer $ui_renderer;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference = false,
        bool $a_prepare_output = true
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->help = $DIC['ilHelp'];
        $this->logger = $DIC->logger()->ac();

        $this->role_id = $a_id;
        $this->http = $DIC['http'];
        $this->refinery = $DIC['refinery'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];

        // Add ref_id of object that contains role
        $this->initParentRefId();
        $this->obj_obj_id = ilObject::_lookupObjId($this->getParentRefId());
        $this->obj_obj_type = ilObject::_lookupType($this->getParentObjId());
        $this->container_type = ilObject::_lookupType(ilObject::_lookupObjId($this->obj_ref_id));

        $this->type = "role";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $this->ctrl->saveParameter($this, ['obj_id', 'rolf_ref_id']);
        $this->lng->loadLanguageModule('rbac');
    }

    public function executeCommand(): void
    {
        $this->prepareOutput();

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->ensureRoleAccessForContext();

        switch ($next_class) {
            case 'ilrepositorysearchgui':

                if (!$this->access->checkAccess('edit_permission', '', $this->obj_ref_id)) {
                    $this->error->raiseError(
                        $this->lng->txt('permission_denied'),
                        $this->error->WARNING
                    );
                }
                $rep_search = new ilRepositorySearchGUI();
                $rep_search->setTitle($this->lng->txt('role_add_user'));
                $rep_search->setCallback($this, 'addUserObject');

                // Set tabs
                $this->tabs_gui->setTabActive('user_assignment');
                $this->ctrl->setReturn($this, 'userassignment');
                $ret = $this->ctrl->forwardCommand($rep_search);
                break;

            case 'ilexportgui':

                $this->tabs_gui->setTabActive('export');

                $eo = ilExportOptions::newInstance(ilExportOptions::allocateExportId());
                $eo->addOption(ilExportOptions::KEY_ROOT, 0, $this->object->getId(), $this->obj_ref_id);

                $exp = new ilExportGUI($this, new ilObjRole($this->object->getId()));
                $exp->addFormat('xml');
                $this->ctrl->forwardCommand($exp);
                break;

            case 'ilrecommendedcontentroleconfiggui':
                $this->tabs_gui->setTabActive('rep_recommended_content');
                $ui = new ilRecommendedContentRoleConfigGUI($this->object->getId(), $this->obj_ref_id);
                $this->ctrl->forwardCommand($ui);
                break;

            default:
                if (!$cmd) {
                    if ($this->showDefaultPermissionSettings()) {
                        $cmd = "perm";
                    } else {
                        $cmd = 'userassignment';
                    }
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
    }

    protected function getRoleId(): int
    {
        return $this->role_id;
    }

    protected function initParentRefId(): void
    {
        $this->obj_ref_id = 0;

        if ($this->http->wrapper()->query()->has('rolf_ref_id')) {
            $this->obj_ref_id = $this->http->wrapper()->query()->retrieve(
                'rolf_ref_id',
                $this->refinery->kindlyTo()->int()
            );
        } elseif ($this->http->wrapper()->query()->has('ref_id')) {
            $this->obj_ref_id = $this->http->wrapper()->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }
    }

    protected function retrieveTemplatePermissionsFromPost(): array
    {
        $template_permissions = [];
        if ($this->http->wrapper()->post()->has('template_perm')) {
            $custom_transformer = $this->refinery->custom()->transformation(
                function ($array) {
                    return $array;
                }
            );
            $template_permissions = $this->http->wrapper()->post()->retrieve(
                'template_perm',
                $custom_transformer
            );
        }
        return $template_permissions;
    }

    /**
     * Get ref id of current object (not role folder id)
     */
    public function getParentRefId(): int
    {
        return $this->obj_ref_id;
    }

    /**
     * Get obj_id of current object
     */
    public function getParentObjId(): int
    {
        return $this->obj_obj_id;
    }

    /**
     * get type of current object (not role folder)
     */
    public function getParentType(): string
    {
        return $this->obj_obj_type;
    }

    /**
     * admin and normal tabs are equal for roles
     */
    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    /**
     * Get type of role container
     */
    protected function getContainerType(): string
    {
        return $this->container_type;
    }

    /**
     * check if default permissions are shown or not
     */
    protected function showDefaultPermissionSettings(): bool
    {
        return $this->obj_definition->isContainer($this->getContainerType())
            || $this->obj_definition->isAdministrationObject($this->getContainerType());
    }

    protected function getRoleForm(bool $is_role_creation_form = false): StandardForm
    {
        if ($this->creation_mode) {
            $this->ctrl->setParameter($this, 'new_type', 'role');
        }

        $ff = $this->ui_factory->input()->field();

        $title_validation_constraint = $this->refinery->custom()->constraint(
            fn(string $v): bool => preg_match('/^il_.*$/', $v) ? false : true,
            $this->lng->txt('msg_role_reserved_prefix')
        );

        $role_is_autogenerated = false;

        if (!$is_role_creation_form) {
            $role_is_autogenerated = $this->object->isAutoGenerated();
        }

        $inputs = [
            self::FORM_KEY_TITLE => $ff->text($this->lng->txt('title'))
                ->withMaxLength(70)
                ->withRequired(true)
                ->withAdditionalTransformation($title_validation_constraint)
                ->withValue($is_role_creation_form ? '' : $this->object->getTitle())
                ->withDisabled($role_is_autogenerated),
            self::FORM_KEY_DESCRIPTION => $ff->textarea($this->lng->txt('description'))
                ->withMaxLimit(4000)
                ->withValue($is_role_creation_form ? '' : $this->object->getDescription())
                ->withDisabled($role_is_autogenerated),

        ];

        if (!$is_role_creation_form) {
            $inputs[self::FORM_KEY_ILIAS_ID] = $ff->text($this->lng->txt('ilias_id'))
                ->withDisabled(true)
                ->withValue('il_' . IL_INST_ID . '_'
                    . $this->object->getType() . '_' . $this->object->getId());
        }

        if ($this->obj_ref_id === ROLE_FOLDER_ID) {
            $inputs[self::FORM_KEY_ON_REGISTRATION_FORM] = $ff->checkbox($this->lng->txt('allow_register'))
                ->withValue($is_role_creation_form ? false : $this->object->getAllowRegister());
            $inputs[self::FORM_KEY_ALLOW_LOCAL_USER_ASSIGNMENT] = $ff->checkbox($this->lng->txt('allow_assign_users'))
                ->withValue($is_role_creation_form ? false : $this->object->getAssignUsersStatus());
        }

        $inputs[self::FORM_KEY_PROTECT] = $ff->checkbox($this->lng->txt('role_protect_permissions'))
            ->withValue(
                $is_role_creation_form
                    ? false
                    : $this->rbac_review->isProtected($this->obj_ref_id, $this->object->getId())
            );

        return $this->ui_factory->input()->container()->form()->standard(
            $this->ctrl->getFormActionByClass(
                self::class,
                $is_role_creation_form ? 'save' : 'update'
            ),
            $inputs
        )->withSubmitLabel(
            $is_role_creation_form ? $this->lng->txt('role_new') : $this->lng->txt('save')
        );
    }

    public function createObject(): void
    {
        if (!$this->rbac_system->checkAccess('create_role', $this->obj_ref_id)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass(ilRepositoryGUI::class);
        }

        $this->tabs_gui->setBackTarget(
            $this->lng->txt('cancel'),
            $this->ctrl->getParentReturnByClass(self::class)
        );

        $this->tpl->setContent(
            $this->ui_renderer->render(
                $this->ui_factory->panel()->standard(
                    $this->lng->txt('role_new'),
                    $this->getRoleForm(true)
                )
            )
        );
    }

    public function editObject(): void
    {
        if (!$this->checkAccess('write', 'edit_permission')) {
            $this->tpl->setOnScreenMessage('msg_no_perm_write', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass(ilRepositoryGUI::class);
        }
        $this->tabs_gui->activateTab('edit_properties');

        $this->buildEditPage();
    }

    /**
     * Save new role
     * @return
     */
    public function saveObject(): void
    {
        $form = $this->getRoleForm(true)->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->tabs_gui->setBackTarget(
                $this->lng->txt('cancel'),
                $this->ctrl->getParentReturnByClass(self::class)
            );
            $this->tpl->setContent(
                $this->ui_renderer->render(
                    $this->ui_factory->panel()->standard(
                        $this->lng->txt('role_new'),
                        $form
                    )
                )
            );
            return;
        }

        $role = new ilObjRole();
        $role->setTitle($data[self::FORM_KEY_TITLE]);
        $role->setDescription($data[self::FORM_KEY_DESCRIPTION]);
        $role->setAllowRegister($data[self::FORM_KEY_ON_REGISTRATION_FORM]);
        $role->toggleAssignUsersStatus($data[self::FORM_KEY_ALLOW_LOCAL_USER_ASSIGNMENT]);
        $role->create();
        $this->rbac_admin->assignRoleToFolder($role->getId(), $this->obj_ref_id, 'y');
        $this->rbac_admin->setProtected(
            $this->obj_ref_id,
            $role->getId(),
            $data[self::FORM_KEY_PROTECT] ? 'y' : 'n'
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('role_added'), true);
        $this->ctrl->setParameter($this, 'obj_id', $role->getId());
        $this->ctrl->redirect($this, 'perm');
    }

    /**
     * Save role settings
     * @return
     */
    public function updateObject(): void
    {
        $form = $this->getRoleForm()->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            $this->buildEditPage($form);
            return;
        }

        if (isset($data['title'])) {
            $this->object->setTitle($data[self::FORM_KEY_TITLE]);
        }
        if (isset($data['description'])) {
            $this->object->setDescription($data[self::FORM_KEY_DESCRIPTION]);
        }
        $this->object->setAllowRegister($data[self::FORM_KEY_ON_REGISTRATION_FORM]);
        $this->object->toggleAssignUsersStatus($data[self::FORM_KEY_ALLOW_LOCAL_USER_ASSIGNMENT]);
        $this->object->update();
        $this->rbac_admin->setProtected(
            $this->obj_ref_id,
            $this->object->getId(),
            $data[self::FORM_KEY_PROTECT] ? 'y' : 'n'
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, 'edit');
    }

    private function buildEditPage(StandardForm $form = null): void
    {
        $page_content = [];
        if ($this->object->getId() != SYSTEM_ROLE_ID) {
            $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
            if ($this->rbac_review->isDeleteable($this->object->getId(), $this->obj_ref_id)) {
                $modal = $this->buildConfirmationModal();
                $this->toolbar->addComponent(
                    $this->ui_factory->button()->standard(
                        $this->lng->txt('rbac_delete_role'),
                        $modal->getShowSignal()
                    )
                );
                $page_content[] = $modal;
            }
        }

        $page_content[] = $this->ui_factory->panel()->standard(
            $this->lng->txt('role_edit'),
            $form ?? $this->getRoleForm()
        );

        $this->tpl->setContent(
            $this->ui_renderer->render($page_content)
        );
    }

    protected function permObject(bool $a_show_admin_permissions = false): void
    {
        $this->tabs_gui->setTabActive('default_perm_settings');

        $this->setSubTabs('default_perm_settings');

        if (!$this->checkAccess('write', 'edit_permission')) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_write'), true);
            $this->ctrl->redirectByClass(ilRepositoryGUI::class);
        }

        if ($a_show_admin_permissions) {
            $this->tabs_gui->setSubTabActive('rbac_admin_permissions');
        } else {
            $this->tabs_gui->setSubTabActive('rbac_repository_permissions');
        }

        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.rbac_template_permissions.html',
            'components/ILIAS/AccessControl'
        );

        if ($this->object->getId() !== SYSTEM_ROLE_ID) {
            $this->toolbar->setFormAction($this->ctrl->getFormAction($this));
            $this->toolbar->addComponent(
                $this->ui_factory->link()->standard(
                    $this->lng->txt("adopt_perm_from_template"),
                    $this->ctrl->getLinkTarget($this, 'adoptPerm')
                )
            );
            if ($this->rbac_review->isDeleteable($this->object->getId(), $this->obj_ref_id)) {
                $modal = $this->buildConfirmationModal();
                $this->toolbar->addComponent(
                    $this->ui_factory->button()->standard(
                        $this->lng->txt('rbac_delete_role'),
                        $modal->getShowSignal()
                    )
                );
                $this->tpl->setVariable('DELETION_MODAL', $this->ui_renderer->render($modal));
            }
        }

        $this->tpl->setVariable('PERM_ACTION', $this->ctrl->getFormAction($this));

        $acc = new ilAccordionGUI();
        $acc->setBehaviour(ilAccordionGUI::FORCE_ALL_OPEN);
        $acc->setId('template_perm_' . $this->getParentRefId());

        if ($this->obj_ref_id == ROLE_FOLDER_ID) {
            if ($a_show_admin_permissions) {
                $subs = ilObjRole::getSubObjects('adm', true);
            } else {
                $subs = ilObjRole::getSubObjects('root', false);
            }
        } else {
            $subs = ilObjRole::getSubObjects($this->getParentType(), $a_show_admin_permissions);
        }

        foreach ($subs as $subtype => $def) {
            $tbl = new ilObjectRoleTemplatePermissionTableGUI(
                $this,
                'perm',
                $this->getParentRefId(),
                $this->object->getId(),
                $subtype,
                $a_show_admin_permissions
            );
            $tbl->parse();

            $acc->addItem($def['translation'], $tbl->getHTML());
        }

        $this->tpl->setVariable('ACCORDION', $acc->getHTML());

        // Add options table
        $options = new ilObjectRoleTemplateOptionsTableGUI(
            $this,
            'perm',
            $this->obj_ref_id,
            $this->object->getId(),
            $a_show_admin_permissions
        );
        if ($this->object->getId() != SYSTEM_ROLE_ID) {
            $options->addMultiCommand(
                $a_show_admin_permissions ? 'adminPermSave' : 'permSave',
                $this->lng->txt('save')
            );
        }

        $options->parse();
        $this->tpl->setVariable('OPTIONS_TABLE', $options->getHTML());
    }

    protected function adminPermObject(): void
    {
        $this->permObject(true);
    }

    protected function adminPermSaveObject(): void
    {
        $this->permSaveObject(true);
    }

    protected function adoptPermObject(): void
    {
        $output = [];
        $parent_role_ids = $this->rbac_review->getParentRoleIds($this->obj_ref_id, true);

        $this->tabs_gui->clearTargets();

        $ids = [];
        foreach (array_keys($parent_role_ids) as $id) {
            $ids[] = $id;
        }
        // Sort ids
        $sorted_ids = ilUtil::_sortIds($ids, 'object_data', 'type,title', 'obj_id');
        $key = 0;
        foreach ($sorted_ids as $id) {
            $par = $parent_role_ids[$id];
            if ($par["obj_id"] != SYSTEM_ROLE_ID && $this->object->getId() != $par["obj_id"]) {
                $output[$key]["role_id"] = $par["obj_id"];
                $output[$key]["type"] = ($par["type"] == 'role' ? $this->lng->txt('obj_role') : $this->lng->txt('obj_rolt'));
                $output[$key]["role_name"] = ilObjRole::_getTranslation($par["title"]);
                $output[$key]["role_desc"] = $par["desc"];
                $key++;
            }
        }

        $tbl = new ilRoleAdoptPermissionTableGUI($this, "adoptPerm");
        $tbl->setTitle($this->lng->txt("adopt_perm_from_template"));
        $tbl->setData($output);

        $this->tpl->setContent($tbl->getHTML());
    }

    /**
     * Show delete confirmation screen
     */
    protected function confirmDeleteRoleObject(): void
    {
        $this->tabs_gui->clearTargets();

        if (!$this->checkAccess('visible,write', 'edit_permission')) {
            $this->tpl->setOnScreenMessage('msg_no_perm_perm', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass(ilRepositoryGUI::class);
        }

        $question = $this->lng->txt('rbac_role_delete_qst');
        if ($this->rbac_review->isAssigned($this->user->getId(), $this->object->getId())) {
            $question .= ('<br />' . $this->lng->txt('rbac_role_delete_self'));
        }
        $this->tpl->setOnScreenMessage('question', $question);

        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($question);
        $confirm->setCancel($this->lng->txt('cancel'), 'perm');
        $confirm->setConfirm($this->lng->txt('rbac_delete_role'), 'performDeleteRole');

        $confirm->addItem(
            'role',
            (string) $this->object->getId(),
            ilObjRole::_getTranslation($this->object->getTitle()),
            ilUtil::getImagePath('standard/icon_role.svg')
        );

        $this->tpl->setContent($confirm->getHTML());
    }

    /**
     * Delete role
     */
    protected function performDeleteRoleObject(): void
    {
        if (!$this->checkAccess('visible,write', 'edit_permission')) {
            $this->tpl->setOnScreenMessage('msg_no_perm_perm', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass(ilRepositoryGUI::class);
        }

        $this->object->setParent($this->obj_ref_id);
        $this->object->delete();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_deleted_role'), true);

        $this->ctrl->returnToParent($this);
    }

    /**
     * save permissions
     * @access    public
     */
    public function permSaveObject(bool $a_show_admin_permissions = false): void
    {
        if (!$this->checkAccess('visible,write', 'edit_permission')) {
            $this->tpl->setOnScreenMessage('msg_no_perm_perm', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectByClass(ilRepositoryGUI::class);
        }

        // rbac log
        $rbac_log_active = ilRbacLog::isActive();
        if ($rbac_log_active) {
            $rbac_log_old = ilRbacLog::gatherTemplate($this->obj_ref_id, $this->object->getId());
        }

        // delete all template entries of enabled types
        if ($this->obj_ref_id == ROLE_FOLDER_ID) {
            if ($a_show_admin_permissions) {
                $subs = ilObjRole::getSubObjects('adm', true);
            } else {
                $subs = ilObjRole::getSubObjects('root', false);
            }
        } else {
            $subs = ilObjRole::getSubObjects($this->getParentType(), $a_show_admin_permissions);
        }

        foreach (array_keys($subs) as $subtype) {
            // Delete per object type
            $this->rbac_admin->deleteRolePermission($this->object->getId(), $this->obj_ref_id, $subtype);
        }

        $template_permissions = $this->retrieveTemplatePermissionsFromPost();
        foreach ($template_permissions as $key => $ops_array) {
            // sets new template permissions
            $this->rbac_admin->setRolePermission($this->object->getId(), $key, $ops_array, $this->obj_ref_id);
        }

        if ($rbac_log_active) {
            $rbac_log_new = ilRbacLog::gatherTemplate($this->obj_ref_id, $this->object->getId());
            $rbac_log_diff = ilRbacLog::diffTemplate($rbac_log_old, $rbac_log_new);
            ilRbacLog::add(ilRbacLog::EDIT_TEMPLATE, $this->obj_ref_id, $rbac_log_diff);
        }

        // update object data entry (to update last modification date)
        $this->object->update();

        // set protected flag
        $protected = false;
        if ($this->http->wrapper()->post()->has('protected')) {
            $protected = $this->http->wrapper()->post()->retrieve(
                'protected',
                $this->refinery->kindlyTo()->bool()
            );
        }
        if (
            $this->obj_ref_id == ROLE_FOLDER_ID ||
            $this->rbac_review->isAssignable($this->object->getId(), $this->obj_ref_id)) {
            $this->rbac_admin->setProtected($this->obj_ref_id, $this->object->getId(), ilUtil::tf2yn($protected));
        }
        $recursive = false;
        if ($this->http->wrapper()->post()->has('recursive')) {
            $recursive = $this->http->wrapper()->post()->retrieve(
                'recursive',
                $this->refinery->kindlyTo()->bool()
            );
        }
        // aka change existing object for specific object types
        $recursive_list = [];
        if ($this->http->wrapper()->post()->has('recursive_list')) {
            $recursive_list = $this->http->wrapper()->post()->retrieve(
                'recursive_list',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->string()
                )
            );
        }
        if ($a_show_admin_permissions) {
            $recursive = true;
        }

        // Redirect if Change existing objects is not chosen
        if (!$recursive && !count($recursive_list)) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"), true);
            if ($a_show_admin_permissions) {
                $this->ctrl->redirect($this, 'adminPerm');
            } else {
                $this->ctrl->redirect($this, 'perm');
            }
        }
        // New implementation
        if (
            ($recursive || count($recursive_list)) &&
            $this->isChangeExistingObjectsConfirmationRequired() &&
            !$a_show_admin_permissions
        ) {
            $this->showChangeExistingObjectsConfirmation($recursive, $recursive_list);
            return;
        }

        $start = ($this->obj_ref_id == ROLE_FOLDER_ID ? ROOT_FOLDER_ID : $this->obj_ref_id);
        if ($a_show_admin_permissions) {
            $start = $this->tree->getParentId($this->obj_ref_id);
        }

        if ($protected) {
            $this->object->changeExistingObjects(
                $start,
                ilObjRole::MODE_PROTECTED_KEEP_LOCAL_POLICIES,
                ['all'],
                []
            );
        } else {
            $this->object->changeExistingObjects(
                $start,
                ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES,
                ['all'],
                []
            );
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"), true);

        if ($a_show_admin_permissions) {
            $this->ctrl->redirect($this, 'adminPerm');
        } else {
            $this->ctrl->redirect($this, 'perm');
        }
    }

    public function adoptPermSaveObject(): void
    {
        $source = 0;
        if ($this->http->wrapper()->post()->has('adopt')) {
            $source = $this->http->wrapper()->post()->retrieve(
                'adopt',
                $this->refinery->kindlyTo()->int()
            );
        }

        if (!$source) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->adoptPermObject();
            return;
        }

        $access = $this->checkAccess('visible,write', 'edit_permission');
        if (!$access) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_perm'), true);
        }
        if ($this->object->getId() == $source) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_perm_adopted_from_itself"), true);
        } else {
            $this->rbac_admin->deleteRolePermission($this->object->getId(), $this->obj_ref_id);
            $parentRoles = $this->rbac_review->getParentRoleIds($this->obj_ref_id, true);
            $this->rbac_admin->copyRoleTemplatePermissions(
                $source,
                $parentRoles[$source]["parent"],
                $this->obj_ref_id,
                $this->object->getId(),
                false
            );

            // update object data entry (to update last modification date)
            $this->object->update();

            // send info
            $title = ilObject::_lookupTitle($source);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_perm_adopted_from1") . " '" .
                    ilObjRole::_getTranslation($title) . "'.<br/>" .
                     $this->lng->txt("msg_perm_adopted_from2"), true);
        }

        $this->ctrl->redirect($this, "perm");
    }

    /**
     * @param int[]
     */
    public function addUserObject(array $a_user_ids): void
    {
        if (!$this->checkAccess('edit_userassignment', 'edit_permission')) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_assign_user_to_role'), true);
            return;
        }
        if (!$this->rbac_review->isAssignable($this->object->getId(), $this->obj_ref_id) &&
            $this->obj_ref_id != ROLE_FOLDER_ID) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('err_role_not_assignable'), true);
            return;
        }
        if ($a_user_ids === []) {
            $this->lng->loadLanguageModule('search');
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('search_err_user_not_exist'), true);
            return;
        }

        $assigned_users_all = $this->rbac_review->assignedUsers($this->object->getId());

        // users to assign
        $assigned_users_new = array_diff($a_user_ids, array_intersect($a_user_ids, $assigned_users_all));

        // selected users all already assigned. stop
        if (count($assigned_users_new) == 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("rbac_msg_user_already_assigned"), true);
            $this->ctrl->redirect($this, 'userassignment');
        }

        // assign new users
        foreach ($assigned_users_new as $user_id) {
            if ($user_id === ANONYMOUS_USER_ID) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_anonymous_cannot_be_assigned'), true);
                return;
            }
            $this->rbac_admin->assignUser($this->object->getId(), $user_id, false);
        }

        // update object data entry (to update last modification date)
        $this->object->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_userassignment_changed"), true);
        $this->ctrl->redirect($this, 'userassignment');
    }

    public function deassignUserObject(): void
    {
        if (!$this->checkAccess('edit_userassignment', 'edit_permission')) {
            $this->ilias->raiseError(
                $this->lng->txt("msg_no_perm_assign_user_to_role"),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $selected_users = [];
        if ($this->http->wrapper()->query()->has('user_id')) {
            $selected_users = [
                $this->http->wrapper()->query()->retrieve(
                    'user_id',
                    $this->refinery->kindlyTo()->int()
                )
            ];
        }
        if ($this->http->wrapper()->post()->has('user_id')) {
            $selected_users = $this->http->wrapper()->post()->retrieve(
                'user_id',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        if (count($selected_users) === 0) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }

        // prevent unassignment of system user from system role
        if ($this->object->getId() == SYSTEM_ROLE_ID) {
            if ($admin = array_search(SYSTEM_USER_ID, $selected_users) !== false) {
                unset($selected_users[$admin]);
            }
        }

        // check for each user if the current role is his last global role before deassigning him
        $last_role = [];
        $global_roles = $this->rbac_review->getGlobalRoles();
        foreach ($selected_users as $user) {
            $assigned_roles = $this->rbac_review->assignedRoles($user);
            $assigned_global_roles = array_intersect($assigned_roles, $global_roles);

            if (count($assigned_roles) == 1 || count($assigned_global_roles) == 1 && in_array(
                $this->object->getId(),
                $assigned_global_roles
            )) {
                $userObj = new ilObjUser($user);
                $last_role[$user] = $userObj->getFullName();
                unset($userObj);
            }
        }

        // ... else perform deassignment
        foreach ($selected_users as $user) {
            if (!isset($last_role[$user])) {
                $this->rbac_admin->deassignUser($this->object->getId(), $user);
            }
        }

        // update object data entry (to update last modification date)
        $this->object->update();

        // raise error if last role was taken from a user...
        if ($last_role !== []) {
            $user_list = implode(", ", $last_role);
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_is_last_role') . ': ' . $user_list . '<br />' . $this->lng->txt('msg_min_one_role'), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_userassignment_changed"), true);
        }
        $this->ctrl->redirect($this, 'userassignment');
    }

    /**
     * display user assignment panel
     */
    public function userassignmentObject(): void
    {
        if (!$this->checkAccess('edit_userassignment', 'edit_permission')) {
            $this->tpl->setOnScreenMessage(
                $this->lng->txt("msg_no_perm_assign_user_to_role"),
            );
            return;
        }

        $this->tabs_gui->setTabActive('user_assignment');

        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.rbac_ua.html', 'components/ILIAS/AccessControl');

        $tb = new ilToolbarGUI();

        // protected admin role
        if ($this->object->getId() != SYSTEM_ROLE_ID
            || ($this->rbac_review->isAssigned($this->user->getId(), SYSTEM_ROLE_ID)
                || !ilSecuritySettings::_getInstance()->isAdminRoleProtected())) {
            // add member
            ilRepositorySearchGUI::fillAutoCompleteToolbar(
                $this,
                $tb,
                [
                    'auto_complete_name' => $this->lng->txt('user'),
                    'submit_name' => $this->lng->txt('add')
                ]
            );

            $tb->addSpacer();

            $tb->addComponent(
                $this->ui_factory->link()->standard(
                    $this->lng->txt('search_user'),
                    $this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI', 'start')
                )
            );
            $tb->addSpacer();
        }

        $tb->addComponent(
            $this->ui_factory->link()->standard(
                $this->lng->txt('role_mailto'),
                $this->ctrl->getLinkTarget($this, 'mailToRole')
            )
        );
        $this->tpl->setVariable('BUTTONS_UA', $tb->getHTML());

        $role_assignment_editable = true;
        if (
            $this->object->getId() == SYSTEM_ROLE_ID &&
            !ilSecuritySettings::_getInstance()->checkAdminRoleAccessible($this->user->getId())) {
            $role_assignment_editable = false;
        }
        $ut = new ilAssignedUsersTableGUI(
            $this,
            'userassignment',
            $this->ui_factory,
            $this->ui_renderer,
            $this->object->getId(),
            $role_assignment_editable,
            $this->getAdminMode() === self::ADMIN_MODE_SETTINGS
        );
        $this->tpl->setVariable('TABLE_UA', $ut->getHTML());
    }

    /**
     * @inheritdoc
     */
    protected function addAdminLocatorItems(bool $do_not_add_object = false): void
    {
        if ($this->getAdminMode() === self::ADMIN_MODE_SETTINGS) {
            parent::addAdminLocatorItems(true);

            $this->locator->addItem(
                $this->lng->txt('obj_' . $this->getParentType()),
                $this->ctrl->getLinkTargetByClass("ilobjrolefoldergui", 'view')
            );

            if ($this->object instanceof ilObjRole) {
                $this->locator->addItem(
                    ilObjRole::_getTranslation($this->object->getTitle()),
                    $this->ctrl->getLinkTarget($this, 'perm')
                );
            }
        } else {
            parent::addAdminLocatorItems($do_not_add_object);
        }
    }

    protected function getTabs(): void
    {
        $base_role_container = $this->rbac_review->getFoldersAssignedToRole($this->object->getId(), true);
        $activate_role_edit = false;

        // todo: activate the following (allow editing of local roles in
        // roles administration)
        if (
            in_array($this->obj_ref_id, $base_role_container) ||
            $this->getAdminMode() === self::ADMIN_MODE_SETTINGS
        ) {
            $activate_role_edit = true;
        }

        // not so nice (workaround for using tabs in repository)
        $this->tabs_gui->clearTargets();

        $this->help->setScreenIdComponent('role');
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('btn_back'),
            (string) $this->ctrl->getParentReturn($this)
        );
        if ($this->checkAccess('write', 'edit_permission') && $activate_role_edit) {
            $this->tabs_gui->addTarget(
                "edit_properties",
                $this->ctrl->getLinkTarget($this, "edit"),
                ["edit", "update"],
                get_class($this)
            );
        }
        if ($this->checkAccess('write', 'edit_permission') && $this->showDefaultPermissionSettings()) {
            $this->tabs_gui->addTarget(
                'default_perm_settings',
                $this->ctrl->getLinkTarget($this, 'perm'),
                [],
                get_class($this)
            );
        }

        if ($this->checkAccess(
            'write',
            'edit_permission'
        ) && $activate_role_edit && $this->object->getId() != ANONYMOUS_ROLE_ID) {
            $this->tabs_gui->addTarget(
                'user_assignment',
                $this->ctrl->getLinkTarget($this, 'userassignment'),
                ['deassignUser', 'userassignment', 'assignUser', 'searchUserForm', 'search'],
                get_class($this)
            );
        }

        if ($this->checkAccess(
            'write',
            'edit_permission'
        ) && $activate_role_edit && $this->object->getId() != ANONYMOUS_ROLE_ID) {
            $this->lng->loadLanguageModule('rep');
            $this->tabs_gui->addTarget(
                'rep_recommended_content',
                $this->ctrl->getLinkTargetByClass('ilrecommendedcontentroleconfiggui', '')
            );
        }
        if ($this->checkAccess('write', 'edit_permission')) {
            $this->tabs_gui->addTarget(
                'export',
                $this->ctrl->getLinkTargetByClass('ilExportGUI'),
                []
            );
        }
    }

    public function mailToRoleObject(): void
    {
        $mail_roles = (array) (ilSession::get('mail_roles') ?? []);

        $obj_ids = ilObject::_getIdsForTitle($this->object->getTitle(), $this->object->getType());
        if (count($obj_ids) > 1) {
            $mail_roles[] = '#il_role_' . $this->object->getId();
        } else {
            $mail_roles[] = (new \ilRoleMailboxAddress($this->object->getId()))->value();
        }
        ilSession::set('mail_roles', $mail_roles);
        $script = ilMailFormCall::getRedirectTarget($this, 'userassignment', [], ['type' => 'role']);
        ilUtil::redirect($script);
    }

    public function checkAccess(string $a_perm_global, string $a_perm_obj = ''): bool
    {
        $a_perm_obj = $a_perm_obj ?: $a_perm_global;

        if ($this->obj_ref_id == ROLE_FOLDER_ID) {
            return $this->rbac_system->checkAccess($a_perm_global, $this->obj_ref_id);
        } else {
            return $this->access->checkAccess($a_perm_obj, '', $this->obj_ref_id);
        }
    }

    /**
     * Check if a confirmation about further settings is required or not
     */
    protected function isChangeExistingObjectsConfirmationRequired(): bool
    {
        // Role is protected
        if ($this->rbac_review->isProtected($this->obj_ref_id, $this->object->getId())) {
            // TODO: check if recursive_list is enabled
            // and if yes: check if inheritance is broken for the relevant object types
            return count($this->rbac_review->getFoldersAssignedToRole($this->object->getId())) > 1;
        } else {
            // TODO: check if recursive_list is enabled
            // and if yes: check if inheritance is broken for the relevant object types
            return count($this->rbac_review->getFoldersAssignedToRole($this->object->getId())) > 1;
        }
    }

    /**
     * Show confirmation screen
     * @param string[] $recursive_list
     */
    protected function showChangeExistingObjectsConfirmation(bool $recursive, array $recursive_list): void
    {
        $protected = false;
        if ($this->http->wrapper()->post()->has('protected')) {
            $protected = $this->http->wrapper()->post()->retrieve(
                'protected',
                $this->refinery->kindlyTo()->bool()
            );
        }
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'changeExistingObjects'));
        $form->setTitle($this->lng->txt('rbac_change_existing_confirm_tbl'));

        $form->addCommandButton('changeExistingObjects', $this->lng->txt('change_existing_objects'));
        $form->addCommandButton('perm', $this->lng->txt('cancel'));

        $hidden = new ilHiddenInputGUI('type_filter');
        $hidden->setValue($recursive ? json_encode(['all']) : json_encode($recursive_list));
        $form->addItem($hidden);

        $rad = new ilRadioGroupInputGUI($this->lng->txt('rbac_local_policies'), 'mode');

        if ($protected) {
            $rad->setValue((string) ilObjRole::MODE_PROTECTED_DELETE_LOCAL_POLICIES);
            $keep = new ilRadioOption(
                $this->lng->txt('rbac_keep_local_policies'),
                (string) ilObjRole::MODE_PROTECTED_KEEP_LOCAL_POLICIES,
                $this->lng->txt('rbac_keep_local_policies_info')
            );
        } else {
            $rad->setValue((string) ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES);
            $keep = new ilRadioOption(
                $this->lng->txt('rbac_keep_local_policies'),
                (string) ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES,
                $this->lng->txt('rbac_unprotected_keep_local_policies_info')
            );
        }
        $rad->addOption($keep);

        if ($protected) {
            $del = new ilRadioOption(
                $this->lng->txt('rbac_delete_local_policies'),
                (string) ilObjRole::MODE_PROTECTED_DELETE_LOCAL_POLICIES,
                $this->lng->txt('rbac_delete_local_policies_info')
            );
        } else {
            $del = new ilRadioOption(
                $this->lng->txt('rbac_delete_local_policies'),
                (string) ilObjRole::MODE_UNPROTECTED_DELETE_LOCAL_POLICIES,
                $this->lng->txt('rbac_unprotected_delete_local_policies_info')
            );
        }
        $rad->addOption($del);

        $form->addItem($rad);
        $this->tpl->setContent($form->getHTML());
    }

    protected function changeExistingObjectsObject(): void
    {
        $mode = 0;
        if ($this->http->wrapper()->post()->has('mode')) {
            $mode = $this->http->wrapper()->post()->retrieve(
                'mode',
                $this->refinery->kindlyTo()->int()
            );
        }
        $start = ($this->obj_ref_id == ROLE_FOLDER_ID ? ROOT_FOLDER_ID : $this->obj_ref_id);

        $type_filter = [];
        if ($this->http->wrapper()->post()->has('type_filter')) {
            $serialized_type_filter = $this->http->wrapper()->post()->retrieve(
                'type_filter',
                $this->refinery->kindlyTo()->string()
            );
            $type_filter = json_decode(
                $serialized_type_filter,
                true
            );
        }

        $this->object->changeExistingObjects($start, $mode, $type_filter);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'perm');
    }

    protected function setSubTabs($a_tab): void
    {
        switch ($a_tab) {
            case 'default_perm_settings':
                if ($this->obj_ref_id != ROLE_FOLDER_ID) {
                    return;
                }
                $this->tabs_gui->addSubTabTarget(
                    'rbac_repository_permissions',
                    $this->ctrl->getLinkTarget($this, 'perm')
                );
                $this->tabs_gui->addSubTabTarget(
                    'rbac_admin_permissions',
                    $this->ctrl->getLinkTarget($this, 'adminPerm')
                );
        }
    }

    /**
     * Add selected users to user clipboard
     */
    protected function addToClipboardObject(): void
    {
        $users = [];
        if ($this->http->wrapper()->post()->has('user_id')) {
            $users = $this->http->wrapper()->post()->retrieve(
                'user_id',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        if (count($users) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'userassignment');
        }
        $clip = ilUserClipboard::getInstance($this->user->getId());
        $clip->add($users);
        $clip->save();

        $this->lng->loadLanguageModule('user');
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('clipboard_user_added'), true);
        $this->ctrl->redirect($this, 'userassignment');
    }

    /**
     * @inheritdoc
     */
    protected function addLocatorItems(): void
    {
        if ($this->getAdminMode() === self::ADMIN_MODE_NONE || $this->getAdminMode() === self::ADMIN_MODE_REPOSITORY) {
            $this->ctrl->setParameterByClass(
                "ilobjrolegui",
                "obj_id",
                $this->getRoleId()
            );
            $this->locator->addItem(
                ilObjRole::_getTranslation($this->object->getTitle()),
                $this->ctrl->getLinkTargetByClass(
                    [
                        "ilpermissiongui",
                        "ilobjrolegui"
                    ],
                    "perm"
                )
            );
        }
    }

    /**
     * Ensure access to role for ref_id
     * @throws ilObjectException
     */
    protected function ensureRoleAccessForContext(): bool
    {
        // creation of roles
        if (
            !$this->object->getId() ||
            $this->object->getId() == ROLE_FOLDER_ID
        ) {
            return true;
        }

        $possible_roles = [];
        try {
            $possible_roles = $this->rbac_review->getRolesOfObject(
                $this->obj_ref_id,
                false
            );
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning('Role access check failed: ' . $e);
            throw new \ilObjectException($this->lng->txt('permission_denied'));
        }

        if (!in_array($this->object->getId(), $possible_roles)) {
            $this->logger->warning('Object id: ' . $this->object->getId() . ' is not accessible for ref_id: ' . $this->obj_ref_id);
            throw new \ilObjectException($this->lng->txt('permission_denied'));
        }
        return true;
    }

    private function buildConfirmationModal(): InterruptiveModal
    {
        $message = $this->lng->txt('rbac_role_delete_qst');
        if ($this->rbac_review->isAssigned($this->user->getId(), $this->object->getId())) {
            $message .= ('<br />' . $this->lng->txt('rbac_role_delete_self'));
        }
        return $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $message,
            $this->ctrl->getFormActionByClass(self::class, 'performDeleteRole')
        )->withAffectedItems([
            $this->ui_factory->modal()->interruptiveItem()->standard(
                (string) $this->object->getId(),
                $this->object->getTitle()
            )
        ]);
    }
}
