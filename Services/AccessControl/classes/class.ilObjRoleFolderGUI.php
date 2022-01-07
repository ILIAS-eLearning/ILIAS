<?php declare(strict_types=1);

/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * Class ilObjRoleFolderGUI
 * @author       Stefan Meyer <meyer@leifos.com>
 * @author       Sascha Hofmann <saschahofmann@gmx.de>
 * @ilCtrl_Calls ilObjRoleFolderGUI: ilPermissionGUI
 * @ingroup      ServicesAccessControl
 */
class ilObjRoleFolderGUI extends ilObjectGUI
{
    private const COPY_ADD_PERMISSIONS = 1;
    private const COPY_CLONE_PERMISSIONS = 2;
    private const COPY_REMOVE_PERMISSIONS = 3;
    private const COPY_CHANGE_EXISTING_OBJECTS = 1;

    private ilLogger $logger;
    protected ilRbacAdmin $rbacadmin;

    protected GlobalHttpState $http;
    protected Factory $refinery;

    /**
     * Constructor
     * @access    public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        global $DIC;

        $this->logger = $DIC->logger()->ac();
        $this->rbacadmin = $DIC->rbac()->admin();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->type = "rolf";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $this->lng->loadLanguageModule('rbac');
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {

            case 'ilpermissiongui':
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                $this->ctrl->setReturn($this, "view");
                if (!$cmd) {
                    $cmd = "view";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
        return true;
    }

    protected function initCopySourceFromGET() : int
    {
        if ($this->http->wrapper()->query()->has('csource')) {
            return $this->http->wrapper()->query()->retrieve(
                'csource',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    /**
     * @return int[]
     */
    protected function initRolesFromPOST() : array
    {
        if ($this->http->wrapper()->post()->has('roles')) {
            return $this->http->wrapper()->post()->retrieve(
                'roles',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        return [];
    }

    /**
     * @return int[]
     */
    protected function initRolesStringFromPOST() : array
    {
        if ($this->http->wrapper()->post()->has('roles')) {
            $roles_string = $this->http->wrapper()->post()->retrieve(
                'roles',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
            return explode(',', $roles_string);
        }
        return [];
    }

    public function viewObject()
    {
        $this->tabs_gui->activateTab('view');

        if (!$this->rbacsystem->checkAccess('visible,read', $this->object->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->MESSAGE);
        }

        if ($this->rbacsystem->checkAccess('create_role', $this->object->getRefId())) {
            $this->ctrl->setParameter($this, 'new_type', 'role');
            $this->toolbar->addButton(
                $this->lng->txt('rolf_create_role'),
                $this->ctrl->getLinkTarget($this, 'create')
            );
        }
        if ($this->rbacsystem->checkAccess('create_rolt', $this->object->getRefId())) {
            $this->ctrl->setParameter($this, 'new_type', 'rolt');
            $this->toolbar->addButton(
                $this->lng->txt('rolf_create_rolt'),
                $this->ctrl->getLinkTarget($this, 'create')
            );
            $this->ctrl->clearParameters($this);
        }

        if (
            $this->rbacsystem->checkAccess('create_rolt', $this->object->getRefId()) ||
            $this->rbacsystem->checkAccess('create_rolt', $this->object->getRefId())
        ) {
            $this->toolbar->addButton(
                $this->lng->txt('rbac_import_role'),
                $this->ctrl->getLinkTargetByClass('ilPermissionGUI', 'displayImportRoleForm')
            );
        }

        $table = new ilRoleTableGUI($this, 'view');
        $table->init();
        $table->parse($this->object->getId());

        $this->tpl->setContent($table->getHTML());
    }

    /**
     * Search target roles
     */
    protected function roleSearchObject() : void
    {
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('rbac_back_to_overview'),
            $this->ctrl->getLinkTarget($this, 'view')
        );

        if (!$this->rbacsystem->checkAccess('visible,read', $this->object->getRefId())) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->MESSAGE);
        }

        $this->ctrl->setParameter($this, 'csource', $this->initCopySourceFromGET());
        ilUtil::sendInfo($this->lng->txt('rbac_choose_copy_targets'));

        $form = $this->initRoleSearchForm();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init role search form
     */
    protected function initRoleSearchForm() : ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('rbac_role_title'));
        $form->setFormAction($this->ctrl->getFormAction($this, 'view'));

        $search = new ilTextInputGUI($this->lng->txt('title'), 'title');
        $search->setRequired(true);
        $search->setSize(30);
        $search->setMaxLength(255);
        $form->addItem($search);

        $form->addCommandButton('roleSearchForm', $this->lng->txt('search'));
        return $form;
    }

    /**
     * Parse search query
     */
    protected function roleSearchFormObject() : void
    {
        ilSession::set('rolf_search_query', '');
        $this->ctrl->setParameter($this, 'csource', $this->initCopySourceFromGET());

        $form = $this->initRoleSearchForm();
        if ($form->checkInput()) {
            ilSession::set('rolf_search_query', $form->getInput('title'));
            $this->roleSearchListObject();
            return;
        }

        ilUtil::sendFailure($this->lng->txt('msg_no_search_string'), true);
        $form->setValuesByPost();
        $this->ctrl->redirect($this, 'roleSearch');
    }

    /**
     * List roles
     */
    protected function roleSearchListObject() : void
    {
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('rbac_back_to_overview'),
            $this->ctrl->getLinkTarget($this, 'view')
        );

        $this->ctrl->setParameter($this, 'csource', $this->initCopySourceFromGET());

        if (strlen(ilSession::get('rolf_search_query'))) {
            ilUtil::sendInfo($this->lng->txt('rbac_select_copy_targets'));
            $table = new ilRoleTableGUI($this, 'roleSearchList');
            $table->setType(ilRoleTableGUI::TYPE_SEARCH);
            $table->setRoleTitleFilter(ilSession::get('rolf_search_query'));
            $table->init();
            $table->parse($this->object->getId());
            $this->tpl->setContent($table->getHTML());
            return;
        }

        ilUtil::sendFailure($this->lng->txt('msg_no_search_string'), true);
        $this->ctrl->redirect($this, 'roleSearch');
    }

    /**
     * Choose option for copying roles/role templates
     */
    protected function chooseCopyBehaviourObject(?ilPropertyFormGUI $form = null) : void
    {
        $copy_source = $this->initCopySourceFromGET();

        $this->ctrl->saveParameter($this, 'csource');
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('rbac_back_to_overview'),
            $this->ctrl->getLinkTarget($this, 'view')
        );
        if (!$form instanceof \ilPropertyFormGUI) {
            $form = $this->initCopyBehaviourForm();
        }
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Show copy behaviour form
     */
    protected function initCopyBehaviourForm() : ilPropertyFormGUI
    {
        // not only for role templates; add/remove permissions is also applicable for roles
        $full_featured = true;

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('rbac_copy_behaviour'));
        $form->setFormAction($this->ctrl->getFormAction($this, 'chooseCopyBehaviour'));

        $copy_type = new \ilRadioGroupInputGUI(
            $this->lng->txt('rbac_form_copy_roles_adjust_type'),
            'type'
        );
        $copy_type->setRequired(true);
        $copy_type->setValue((string) self::COPY_CLONE_PERMISSIONS);

        if ($full_featured) {
            $add = new \ilRadioOption(
                $this->lng->txt('rbac_form_copy_roles_adjust_type_add'),
                (string) self::COPY_ADD_PERMISSIONS,
                $this->lng->txt('rbac_form_copy_roles_adjust_type_add_info')
            );
            $copy_type->addOption($add);

            $ce_type_add = new \ilRadioGroupInputGUI(
                '',
                'add_ce_type'
            );
            $ce_type_add->setRequired(true);
            $ce_add_yes = new \ilRadioOption(
                $this->lng->txt('rbac_form_copy_roles_ce_add_yes'),
                (string) self::COPY_CHANGE_EXISTING_OBJECTS,
                $this->lng->txt('rbac_form_copy_roles_ce_add_yes_info')
            );
            $ce_type_add->addOption($ce_add_yes);
            $ce_add_no = new \ilRadioOption(
                $this->lng->txt('rbac_form_copy_roles_ce_add_no'),
                (string) 0,
                $this->lng->txt('rbac_form_copy_roles_ce_add_no_info')
            );
            $ce_type_add->addOption($ce_add_no);
            $add->addSubItem($ce_type_add);
        }
        $clone = new \ilRadioOption(
            $this->lng->txt('rbac_form_copy_roles_adjust_type_clone'),
            (string) self::COPY_CLONE_PERMISSIONS,
            $this->lng->txt('rbac_form_copy_roles_adjust_type_clone_info')
        );
        $copy_type->addOption($clone);

        $ce_type_clone = new \ilRadioGroupInputGUI(
            '',
            'clone_ce_type'
        );
        $ce_type_clone->setRequired(true);
        $ce_clone_yes = new \ilRadioOption(
            $this->lng->txt('rbac_form_copy_roles_ce_clone_yes'),
            (string) self::COPY_CHANGE_EXISTING_OBJECTS,
            $this->lng->txt('rbac_form_copy_roles_ce_clone_yes_info')
        );
        $ce_type_clone->addOption($ce_clone_yes);
        $ce_clone_no = new \ilRadioOption(
            $this->lng->txt('rbac_form_copy_roles_ce_clone_no'),
            (string) 0,
            $this->lng->txt('rbac_form_copy_roles_ce_clone_no_info')
        );
        $ce_type_clone->addOption($ce_clone_no);
        $clone->addSubItem($ce_type_clone);

        if ($full_featured) {
            $remove = new \ilRadioOption(
                $this->lng->txt('rbac_form_copy_roles_adjust_type_remove'),
                (string) self::COPY_REMOVE_PERMISSIONS,
                $this->lng->txt('rbac_form_copy_roles_adjust_type_remove_info')
            );
            $copy_type->addOption($remove);
            $ce_type_remove = new \ilRadioGroupInputGUI(
                '',
                'remove_ce_type'
            );
            $ce_type_remove->setRequired(true);
            $ce_remove_yes = new \ilRadioOption(
                $this->lng->txt('rbac_form_copy_roles_ce_remove_yes'),
                (string) self::COPY_CHANGE_EXISTING_OBJECTS,
                $this->lng->txt('rbac_form_copy_roles_ce_remove_yes_info')
            );
            $ce_type_remove->addOption($ce_remove_yes);
            $ce_remove_no = new \ilRadioOption(
                $this->lng->txt('rbac_form_copy_roles_ce_remove_no'),
                (string) 0,
                $this->lng->txt('rbac_form_copy_roles_ce_remove_no_info')
            );
            $ce_type_remove->addOption($ce_remove_no);
            $remove->addSubItem($ce_type_remove);
        }

        $form->addItem($copy_type);

        $roles = new ilHiddenInputGUI('roles');
        $roles->setValue(implode(',', $this->initRolesFromPOST()));
        $form->addItem($roles);

        $form->addCommandButton('roleSearchList', $this->lng->txt('back'));
        $form->addCommandButton('adjustRole', $this->lng->txt('rbac_form_copy_roles_adjust_button'));
        return $form;
    }

    /**
     * Copy role
     */
    protected function adjustRoleObject() : void
    {
        $this->checkPermission('write');

        $roles = $this->initRolesStringFromPOST();
        $source = $this->initCopySourceFromGET();

        $form = $this->initCopyBehaviourForm();
        if ($form->checkInput()) {
            $adjustment_type = $form->getInput('type');
            foreach ((array) $roles as $role_id) {
                if ($role_id != $source) {
                    $start_obj = $this->rbacreview->getRoleFolderOfRole($role_id);
                    $this->logger->debug('Start object: ' . $start_obj);

                    switch ($adjustment_type) {
                        case self::COPY_ADD_PERMISSIONS:
                            $change_existing = (bool) $form->getInput('add_ce_type');
                            $this->doAddRolePermissions(
                                $source,
                                $role_id
                            );
                            if ($change_existing) {
                                $this->doChangeExistingObjects(
                                    $start_obj,
                                    $role_id,
                                    \ilObjRole::MODE_ADD_OPERATIONS,
                                    $source
                                );
                            }
                            break;
                        case self::COPY_CLONE_PERMISSIONS:
                            $change_existing = (bool) $form->getInput('clone_ce_type');
                            $this->doCopyRole(
                                $source,
                                $role_id
                            );
                            if ($change_existing) {
                                $this->doChangeExistingObjects(
                                    $start_obj,
                                    $role_id,
                                    \ilObjRole::MODE_READ_OPERATIONS,
                                    $source
                                );
                            }
                            break;
                        case self::COPY_REMOVE_PERMISSIONS:
                            $change_existing = (bool) $form->getInput('remove_ce_type');
                            $this->doRemoveRolePermissions(
                                $source,
                                $role_id
                            );
                            if ($change_existing) {
                                $this->doChangeExistingObjects(
                                    $start_obj,
                                    $role_id,
                                    \ilObjRole::MODE_REMOVE_OPERATIONS,
                                    $source
                                );
                            }
                            break;
                    }
                }
            }
            ilUtil::sendSuccess($this->lng->txt('rbac_copy_finished'), true);
            $this->ctrl->redirect($this, 'view');
        }
    }

    /**
     * do add role permission
     */
    protected function doAddRolePermissions(int $source, int $target) : void
    {
        $source_definition = $this->rbacreview->getRoleFolderOfRole($source);
        $this->rbacadmin->copyRolePermissionUnion(
            $source,
            $source_definition,
            $target,
            $this->rbacreview->getRoleFolderOfRole($target),
            $target,
            $this->rbacreview->getRoleFolderOfRole($target)
        );
    }

    /**
     * Remove role permissions
     */
    protected function removeRolePermissionsObject() : void
    {
        // Finally copy role/rolt
        $roles = $this->initRolesStringFromPOST();
        $source = $this->initCopySourceFromGET();

        $form = $this->initCopyBehaviourForm();
        if ($form->checkInput()) {
            foreach ((array) $roles as $role_id) {
                if ($role_id != $source) {
                    $this->doRemoveRolePermissions($source, $role_id);
                }
            }
            ilUtil::sendSuccess($this->lng->txt('rbac_copy_finished'), true);
            $this->ctrl->redirect($this, 'view');
        }
    }

    /**
     * do add role permission
     */
    protected function doRemoveRolePermissions(int $source, int $target) : void
    {
        $this->logger->debug('Remove permission source: ' . $source);
        $this->logger->debug('Remove permission target: ' . $target);
        $source_obj = $this->rbacreview->getRoleFolderOfRole($source);
        $this->rbacadmin->copyRolePermissionSubtract(
            $source,
            $source_obj,
            $target,
            $this->rbacreview->getRoleFolderOfRole($target)
        );
    }

    /**
     * Perform copy of role
     */
    protected function doCopyRole(int $source, int $target) : void
    {
        $target_obj = $this->rbacreview->getRoleFolderOfRole($target);
        $source_obj = $this->rbacreview->getRoleFolderOfRole($source);
        // Copy role template permissions
        $this->rbacadmin->copyRoleTemplatePermissions(
            $source,
            $source_obj,
            $target_obj,
            $target
        );
    }

    /**
     * Do change existing objects
     */
    protected function doChangeExistingObjects(
        int $a_start_obj,
        int $a_target_role,
        int $a_operation_mode,
        int $a_source_role
    ) : void {
        if (!$a_start_obj) {
            $this->logger->warning('Missing parameter start object.');
            $this->logger->logStack(\ilLogLevel::WARNING);
            throw new InvalidArgumentException('Missing parameter: start object');
        }
        // the mode is unchanged and read out from the target object
        $target_ref_id = $this->rbacreview->getRoleFolderOfRole($a_target_role);
        if ($this->rbacreview->isProtected($target_ref_id, $a_target_role)) {
            $mode = \ilObjRole::MODE_PROTECTED_KEEP_LOCAL_POLICIES;
        } else {
            $mode = \ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES;
        }

        $operation_stack = [];
        if ($a_operation_mode !== \ilObjRole::MODE_READ_OPERATIONS) {
            $operation_stack[] = $this->rbacreview->getAllOperationsOfRole($a_source_role, $a_start_obj);
        }

        $this->logger->debug('Current operation stack');
        $this->logger->dump($operation_stack, ilLogLevel::DEBUG);

        $role = new ilObjRole($a_target_role);
        $role->changeExistingObjects(
            $a_start_obj,
            $mode,
            array('all'),
            [],
            $a_operation_mode,
            $operation_stack
        );
    }

    /**
     * Apply role filter
     */
    protected function applyFilterObject() : void
    {
        $table = new ilRoleTableGUI($this, 'view');
        $table->init();
        $table->resetOffset();
        $table->writeFilterToSession();

        $this->viewObject();
    }

    /**
     * Reset role filter
     */
    public function resetFilterObject() : void
    {
        $table = new ilRoleTableGUI($this, 'view');
        $table->init();
        $table->resetOffset();
        $table->resetFilter();

        $this->viewObject();
    }

    /**
     * Confirm deletion of roles
     */
    protected function confirmDeleteObject() : void
    {
        $roles = $this->initRolesFromPOST();
        if (!count($roles)) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $this->ctrl->redirect($this, 'view');
        }

        $question = $this->lng->txt('rbac_role_delete_qst');

        $confirm = new ilConfirmationGUI();
        $confirm->setHeaderText($question);
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt("info_delete_sure"));
        $confirm->setConfirm($this->lng->txt('delete'), 'deleteRole');
        $confirm->setCancel($this->lng->txt('cancel'), 'cancel');

        foreach ($roles as $role_id) {
            $confirm->addItem(
                'roles[]',
                (string) $role_id,
                ilObjRole::_getTranslation(ilObject::_lookupTitle($role_id))
            );
        }
        $this->tpl->setContent($confirm->getHTML());
    }

    /**
     * Delete roles
     */
    protected function deleteRoleObject() : void
    {
        if (!$this->rbacsystem->checkAccess('delete', $this->object->getRefId())) {
            $this->ilErr->raiseError(
                $this->lng->txt('msg_no_perm_delete'),
                $this->ilErr->MESSAGE
            );
        }

        foreach ($this->initRolesFromPOST() as $id) {
            // instatiate correct object class (role or rolt)
            $obj = ilObjectFactory::getInstanceByObjId($id, false);

            if ($obj->getType() == "role") {
                $rolf_arr = $this->rbacreview->getFoldersAssignedToRole($obj->getId(), true);
                $obj->setParent($rolf_arr[0]);
            }

            $obj->delete();
        }

        // set correct return location if rolefolder is removed
        ilUtil::sendSuccess($this->lng->txt("msg_deleted_roles_rolts"), true);
        $this->ctrl->redirect($this, 'view');
    }

    /**
     * Add role folder tabs
     * @param ilTabsGUI $tabs_gui
     * @global ilLanguage $lng
     * @global ilTree $tree
     */
    public function getAdminTabs()
    {
        if ($this->checkPermissionBool("visible,read")) {
            $this->tabs_gui->addTarget(
                "view",
                $this->ctrl->getLinkTarget($this, "view"),
                array("", "view"),
                get_class($this)
            );

            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "editSettings"),
                array("editSettings"),
                get_class($this)
            );
        }

        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(
                    array(get_class($this), 'ilpermissiongui'),
                    "perm"
                ),
                "",
                "ilpermissiongui"
            );
        }
    }

    public function editSettingsObject(ilPropertyFormGUI $a_form = null) : void
    {
        if (!$a_form) {
            $a_form = $this->initSettingsForm();
        }

        $this->tpl->setContent($a_form->getHTML());
    }

    public function saveSettingsObject() : void
    {
        global $DIC;

        $user = $DIC->user();

        if (!$this->checkPermissionBool("write")) {
            $this->ilErr->raiseError($this->lng->txt('permission_denied'), $this->ilErr->MESSAGE);
        }

        $form = $this->initSettingsForm();
        if ($form->checkInput()) {
            $privacy = ilPrivacySettings::getInstance();
            $privacy->enableRbacLog((bool) $form->getInput('rbac_log'));
            $privacy->setRbacLogAge((int) $form->getInput('rbac_log_age'));
            $privacy->save();

            if ($this->rbacreview->isAssigned($user->getId(), SYSTEM_ROLE_ID)) {
                $security = ilSecuritySettings::_getInstance();
                $security->protectedAdminRole((bool) $form->getInput('admin_role'));
                $security->save();
            }
            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
            $this->ctrl->redirect($this, "editSettings");
        }

        $form->setValuesByPost();
        $this->editSettingsObject($form);
    }

    protected function initSettingsForm() : ilPropertyFormGUI
    {
        global $DIC;

        $user = $DIC->user();

        $this->lng->loadLanguageModule('ps');

        $privacy = ilPrivacySettings::getInstance();
        $security = ilSecuritySettings::_getInstance();

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, "saveSettings"));
        $form->setTitle($this->lng->txt('settings'));

        // protected admin
        $admin = new ilCheckboxInputGUI($GLOBALS['DIC']['lng']->txt('adm_adm_role_protect'), 'admin_role');
        $admin->setDisabled(!$this->rbacreview->isAssigned($user->getId(), SYSTEM_ROLE_ID));
        $admin->setInfo($this->lng->txt('adm_adm_role_protect_info'));
        $admin->setChecked((bool) $security->isAdminRoleProtected());
        $admin->setValue((string) 1);
        $form->addItem($admin);

        $check = new ilCheckboxInputGui($this->lng->txt('rbac_log'), 'rbac_log');
        $check->setInfo($this->lng->txt('rbac_log_info'));
        $check->setChecked($privacy->enabledRbacLog());
        $form->addItem($check);

        $age = new ilNumberInputGUI($this->lng->txt('rbac_log_age'), 'rbac_log_age');
        $age->setInfo($this->lng->txt('rbac_log_age_info'));
        $age->setValue((string) $privacy->getRbacLogAge());
        $age->setMinValue(1);
        $age->setMaxValue(24);
        $age->setSize(2);
        $age->setMaxLength(2);
        $check->addSubItem($age);

        $form->addCommandButton('saveSettings', $this->lng->txt('save'));

        return $form;
    }

    public function addToExternalSettingsForm(int $a_form_id) : array
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_SECURITY:

                $security = ilSecuritySettings::_getInstance();

                $fields = array('adm_adm_role_protect' => array($security->isAdminRoleProtected(),
                                                                ilAdministrationSettingsFormHandler::VALUE_BOOL
                )
                );

                return array(array("editSettings", $fields));

            case ilAdministrationSettingsFormHandler::FORM_PRIVACY:

                $privacy = ilPrivacySettings::getInstance();

                $subitems = null;
                if ((bool) $privacy->enabledRbacLog()) {
                    $subitems = array('rbac_log_age' => $privacy->getRbacLogAge());
                }
                $fields = array('rbac_log' => array($privacy->enabledRbacLog(),
                                                    ilAdministrationSettingsFormHandler::VALUE_BOOL,
                                                    $subitems
                )
                );

                return array(array("editSettings", $fields));
        }
        return [];
    }
} // END class.ilObjRoleFolderGUI
