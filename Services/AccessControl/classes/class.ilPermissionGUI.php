<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/AccessControl/classes/class.ilPermission2GUI.php';

/**
* New PermissionGUI (extends from old ilPermission2GUI)
* RBAC related output
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilPermissionGUI: ilObjRoleGUI, ilRepositorySearchGUI, ilObjectPermissionStatusGUI
*
* @ingroup	ServicesAccessControl
*/
class ilPermissionGUI extends ilPermission2GUI
{
    const CMD_PERM_POSITIONS = 'permPositions';
    const CMD_SAVE_POSITIONS_PERMISSIONS = 'savePositionsPermissions';

    protected $current_obj = null;

    /**
     * Constructor
     * @param object $a_gui_obj
     * @return
     */
    public function __construct($a_gui_obj)
    {
        parent::__construct($a_gui_obj);
    }
    
    /**
     * Execute command
     * @return
     */
    public function executeCommand()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];

        // access to all functions in this class are only allowed if edit_permission is granted
        if (!$rbacsystem->checkAccess("edit_permission", $this->gui_obj->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            case "ilobjrolegui":
                $this->ctrl->setReturn($this, 'perm');
                include_once("Services/AccessControl/classes/class.ilObjRoleGUI.php");
                $this->gui_obj = new ilObjRoleGUI("", (int) $_GET["obj_id"], false, false);
                $this->gui_obj->setBackTarget($this->lng->txt("perm_settings"), $this->ctrl->getLinkTarget($this, "perm"));
                $ret = $this->ctrl->forwardCommand($this->gui_obj);
                break;

            case 'ildidactictemplategui':
                $this->ctrl->setReturn($this, 'perm');
                include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateGUI.php';
                $did = new ilDidacticTemplateGUI($this->gui_obj);
                $this->ctrl->forwardCommand($did);
                break;
            
            case 'ilrepositorysearchgui':
                // used for owner autocomplete
                include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
                $rep_search = new ilRepositorySearchGUI();
                $this->ctrl->forwardCommand($rep_search);
                break;

            case 'ilobjectpermissionstatusgui':
                $this->__initSubTabs("perminfo");
                include_once('./Services/AccessControl/classes/class.ilObjectPermissionStatusGUI.php');
                $perm_stat = new ilObjectPermissionStatusGUI($this->gui_obj->object);
                $this->ctrl->forwardCommand($perm_stat);
                break;
                
            default:
                $cmd = $this->ctrl->getCmd();
                $this->$cmd();
                break;
        }

        return true;
    }
    
    
    /**
     * Get current object
     * @return ilObject
     */
    public function getCurrentObject()
    {
        return $this->gui_obj->object;
    }

    /**
     * Called after toolbar action applyTemplateSwitch
     */
    protected function confirmTemplateSwitch()
    {
        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateGUI.php';
        $this->ctrl->setReturn($this, 'perm');
        $this->ctrl->setCmdClass('ildidactictemplategui');
        $dtpl_gui = new ilDidacticTemplateGUI($this->gui_obj);
        $this->ctrl->forwardCommand($dtpl_gui, 'confirmTemplateSwitch');
    }

    
    /**
     * show permission table
     * @return
     */
    public function perm(ilTable2GUI $table = null)
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        $ilToolbar = $DIC['ilToolbar'];

        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateGUI.php';
        $dtpl = new ilDidacticTemplateGUI($this->gui_obj);
        if ($dtpl->appendToolbarSwitch(
            $ilToolbar,
            $this->getCurrentObject()->getType(),
            $this->getCurrentObject()->getRefId()
        )) {
            $ilToolbar->addSeparator();
        }
        
        if ($objDefinition->hasLocalRoles($this->getCurrentObject()->getType()) and
            !$this->isAdministrationObject()
        ) {
            $ilToolbar->setFormAction($this->ctrl->getFormAction($this));

            if (!$this->isAdminRoleFolder()) {
                $ilToolbar->addButton($this->lng->txt('rbac_add_new_local_role'), $this->ctrl->getLinkTarget($this, 'displayAddRoleForm'));
            }
            $ilToolbar->addButton($this->lng->txt('rbac_import_role'), $this->ctrl->getLinkTarget($this, 'displayImportRoleForm'));
        }

        $this->__initSubTabs("perm");
        
        if (!$table instanceof ilTable2GUI) {
            include_once './Services/AccessControl/classes/class.ilObjectRolePermissionTableGUI.php';
            $table = new ilObjectRolePermissionTableGUI($this, 'perm', $this->getCurrentObject()->getRefId());
        }
        $table->parse();
        $this->tpl->setContent($table->getHTML());
    }

    
    
    /**
     * Check of current location is administration (main) role folder
     * @return
     */
    protected function isAdminRoleFolder()
    {
        return $this->getCurrentObject()->getRefId() == ROLE_FOLDER_ID;
    }

    protected function isAdministrationObject()
    {
        return $this->getCurrentObject()->getType() == 'adm';
    }

    /**
     * Check if node is subobject of administration folder
     * @return type
     */
    protected function isInAdministration()
    {
        return (bool) $GLOBALS['DIC']['tree']->isGrandChild(SYSTEM_FOLDER_ID, $this->getCurrentObject()->getRefId());
    }
    
    
    /**
     * Apply filter
     * @return
     */
    protected function applyFilter()
    {
        include_once './Services/AccessControl/classes/class.ilObjectRolePermissionTableGUI.php';
        $table = new ilObjectRolePermissionTableGUI($this, 'perm', $this->getCurrentObject()->getRefId());
        $table->resetOffset();
        $table->writeFilterToSession();
        return $this->perm($table);
    }
    
    /**
     * Reset filter
     * @return
     */
    protected function resetFilter()
    {
        include_once './Services/AccessControl/classes/class.ilObjectRolePermissionTableGUI.php';
        $table = new ilObjectRolePermissionTableGUI($this, 'perm', $this->getCurrentObject()->getRefId());
        $table->resetOffset();
        $table->resetFilter();
        
        return $this->perm($table);
    }
    
    /**
     * Apply filter to roles
     * @param int $a_filter_id
     * @return
     */
    public function applyRoleFilter($a_roles, $a_filter_id)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        
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
    
                $arr_global_roles = $rbacreview->getGlobalRoles();
                $arr_remove_roles = array_diff(array_keys($a_roles), $arr_global_roles);

                foreach ($arr_remove_roles as $role_id) {
                    unset($a_roles[$role_id]);
                }
                return $a_roles;

            // only local roles (all local roles in context that are not defined at ROLE_FOLDER_ID)
            case ilObjectRolePermissionTableGUI::ROLE_FILTER_LOCAL:
                $arr_global_roles = $rbacreview->getGlobalRoles();

                foreach ($arr_global_roles as $role_id) {
                    unset($a_roles[$role_id]);
                }
                
                return $a_roles;
                break;
                
            // only roles which use a local policy
            case ilObjectRolePermissionTableGUI::ROLE_FILTER_LOCAL_POLICY:
                
                $arr_local_roles = $GLOBALS['DIC']['rbacreview']->getRolesOfObject($this->getCurrentObject()->getRefId());
                $arr_remove_roles = array_diff(array_keys($a_roles), $arr_local_roles);

                foreach ($arr_remove_roles as $role_id) {
                    unset($a_roles[$role_id]);
                }

                return $a_roles;
                
            // only true local role defined at current position
            case ilObjectRolePermissionTableGUI::ROLE_FILTER_LOCAL_OBJECT:
                
                $arr_local_roles = $GLOBALS['DIC']['rbacreview']->getRolesOfObject($this->getCurrentObject()->getRefId(), true);
                $arr_remove_roles = array_diff(array_keys($a_roles), $arr_local_roles);

                foreach ($arr_remove_roles as $role_id) {
                    unset($a_roles[$role_id]);
                }

                return $a_roles;
                
            default:
                return $a_roles;
        }
    }
    
    /**
     * Save permissions
     * @return
     */
    protected function savePermissions()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $objDefinition = $DIC['objDefinition'];
        $rbacadmin = $DIC['rbacadmin'];
        
        include_once './Services/AccessControl/classes/class.ilObjectRolePermissionTableGUI.php';
        $table = new ilObjectRolePermissionTableGUI($this, 'perm', $this->getCurrentObject()->getRefId());
        
        $roles = $this->applyRoleFilter(
            $rbacreview->getParentRoleIds($this->getCurrentObject()->getRefId()),
            $table->getFilterItemByPostVar('role')->getValue()
        );
        
        // Log history
        include_once "Services/AccessControl/classes/class.ilRbacLog.php";
        $log_old = ilRbacLog::gatherFaPa($this->getCurrentObject()->getRefId(), array_keys((array) $roles));
        

        # all possible create permissions
        $possible_ops_ids = $rbacreview->getOperationsByTypeAndClass(
            $this->getCurrentObject()->getType(),
            'create'
        );
        
        # createable (activated) create permissions
        $create_types = $objDefinition->getCreatableSubObjects(
            $this->getCurrentObject()->getType()
        );
        $createable_ops_ids = ilRbacReview::lookupCreateOperationIds(array_keys((array) $create_types));

        foreach ((array) $roles as $role => $role_data) {
            if ($role_data['protected']) {
                continue;
            }

            $new_ops = array_keys((array) $_POST['perm'][$role]);
            $old_ops = $rbacreview->getRoleOperationsOnObject(
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
            
            $rbacadmin->revokePermission(
                $this->getCurrentObject()->getRefId(),
                $role
            );
            
            $rbacadmin->grantPermission(
                $role,
                array_unique($new_ops),
                $this->getCurrentObject()->getRefId()
            );
        }
        
        if (ilPermissionGUI::hasContainerCommands($this->getCurrentObject()->getType())) {
            foreach ($roles as $role) {
                // No action for local roles
                if ($role['parent'] == $this->getCurrentObject()->getRefId() and $role['assign'] == 'y') {
                    continue;
                }
                // Nothing for protected roles
                if ($role['protected']) {
                    continue;
                }
                // Stop local policy
                if (
                    $role['parent'] == $this->getCurrentObject()->getRefId() and
                    !isset($_POST['inherit'][$role['obj_id']]) and
                    !$rbacreview->isBlockedAtPosition($role['obj_id'], $this->getCurrentObject()->getRefId())
                ) {
                    ilLoggerFactory::getLogger('ac')->debug('Stop local policy for: ' . $role['obj_id']);
                    $role_obj = ilObjectFactory::getInstanceByObjId($role['obj_id']);
                    $role_obj->setParent($this->getCurrentObject()->getRefId());
                    $role_obj->delete();
                    continue;
                }
                // Add local policy
                if ($role['parent'] != $this->getCurrentObject()->getRefId() and isset($_POST['inherit'][$role['obj_id']])) {
                    ilLoggerFactory::getLogger('ac')->debug('Create local policy');
                    $rbacadmin->copyRoleTemplatePermissions(
                        $role['obj_id'],
                        $role['parent'],
                        $this->getCurrentObject()->getRefId(),
                        $role['obj_id']
                    );
                    ilLoggerFactory::getLogger('ac')->debug('Assign role to folder');
                    $rbacadmin->assignRoleToFolder($role['obj_id'], $this->getCurrentObject()->getRefId(), 'n');
                }
            }
        }
        
        // Protect permissions
        if (ilPermissionGUI::hasContainerCommands($this->getCurrentObject()->getType())) {
            foreach ($roles as $role) {
                if ($rbacreview->isAssignable($role['obj_id'], $this->getCurrentObject()->getRefId())) {
                    if (isset($_POST['protect'][$role['obj_id']]) and
                        !$rbacreview->isProtected($this->getCurrentObject()->getRefId(), $role['obj_id'])) {
                        $rbacadmin->setProtected($this->getCurrentObject()->getRefId(), $role['obj_id'], 'y');
                    } elseif (!isset($_POST['protect'][$role['obj_id']]) and
                        $rbacreview->isProtected($this->getCurrentObject()->getRefId(), $role['obj_id'])) {
                        $rbacadmin->setProtected($this->getCurrentObject()->getRefId(), $role['obj_id'], 'n');
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
            return $this->showConfirmBlockRole($blocked_info);
        }
        
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'perm');
        #$this->perm();
    }
    
    /**
     * Show block role confirmation screen
     * @param array $a_roles
     * @return
     */
    protected function showConfirmBlockRole($a_blocked_info)
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

        ilUtil::sendInfo($info);
        
        include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setHeaderText($this->lng->txt('role_confirm_block_role_header'));
        $confirm->setConfirm($this->lng->txt('role_confirm_block_role'), 'modifyBlockRoles');
        $confirm->setCancel($this->lng->txt('cancel'), 'perm');
        
        foreach ($a_blocked_info['new_blocked'] as $role_id) {
            include_once './Services/AccessControl/classes/class.ilObjRole.php';
            $confirm->addItem(
                'new_block[]',
                $role_id,
                ilObjRole::_getTranslation(ilObject::_lookupTitle($role_id)) . ' ' . $this->lng->txt('role_blocked')
            );
        }
        foreach ($a_blocked_info['new_unblocked'] as $role_id) {
            include_once './Services/AccessControl/classes/class.ilObjRole.php';
            $confirm->addItem(
                'new_unblock[]',
                $role_id,
                ilObjRole::_getTranslation(ilObject::_lookupTitle($role_id)) . ' ' . $this->lng->txt('role_unblocked')
            );
        }
        $this->tpl->setContent($confirm->getHTML());
    }
    
    protected function modifyBlockRoles()
    {
        $this->blockRoles((array) $_POST['new_block']);
        $this->unblockRoles((array) $_POST['new_unblock']);

        ilUtil::sendInfo($this->lng->txt('settings_saved'));
        $this->ctrl->redirect($this, 'perm');
    }
    
    /**
     *
     */
    protected function unblockRoles($roles)
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        
        foreach ($roles as $role) {
            // delete local policy
            ilLoggerFactory::getLogger('ac')->debug('Stop local policy for: ' . $role);
            $role_obj = ilObjectFactory::getInstanceByObjId($role);
            $role_obj->setParent($this->getCurrentObject()->getRefId());
            $role_obj->delete();
            
            $role_obj->changeExistingObjects(
                $this->getCurrentObject()->getRefId(),
                ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES,
                array('all')
            );
            
            // finally set blocked status
            $rbacadmin->setBlockedStatus(
                $role,
                $this->getCurrentObject()->getRefId(),
                false
            );
        }
    }
    
    /**
     * Block role
     * @return void
     */
    protected function blockRoles($roles)
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];
        
        foreach ($roles as $role) {
            // Set assign to 'y' only if it is a local role
            $assign = $rbacreview->isAssignable($role, $this->getCurrentObject()->getRefId()) ? 'y' : 'n';

            // Delete permissions
            $rbacadmin->revokeSubtreePermissions($this->getCurrentObject()->getRefId(), $role);
            
            // Delete template permissions
            $rbacadmin->deleteSubtreeTemplates($this->getCurrentObject()->getRefId(), $role);

            
            $rbacadmin->assignRoleToFolder(
                $role,
                $this->getCurrentObject()->getRefId(),
                $assign
            );
            
            // finally set blocked status
            $rbacadmin->setBlockedStatus(
                $role,
                $this->getCurrentObject()->getRefId(),
                true
            );
        }
    }
    
    
    /**
     * Check if container commands are possible for the current object type
     * @param object $a_type
     * @return
     */
    public static function hasContainerCommands($a_type)
    {
        global $DIC;

        $objDefinition = $DIC['objDefinition'];
        
        return $objDefinition->isContainer($a_type) and $a_type != 'root' and $a_type != 'adm' and $a_type != 'rolf';
    }

    /**
     * Show import form
     * @param ilPropertyFormGUI $form
     */
    protected function displayImportRoleForm(ilPropertyFormGUI $form = null)
    {
        $GLOBALS['DIC']['ilTabs']->clearTargets();
        
        if (!$form) {
            $form = $this->initImportForm();
        }
        $GLOBALS['DIC']['tpl']->setContent($form->getHTML());
    }
    
    /**
     * Perform import
     */
    protected function doImportRole()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        
        $form = $this->initImportForm();
        if ($form->checkInput()) {
            try {
                include_once './Services/Export/classes/class.ilImport.php';
                
                // For global roles set import id to parent of current ref_id (adm)
                $imp = new ilImport($this->getCurrentObject()->getRefId());
                $imp->getMapping()->addMapping(
                    'Services/AccessControl',
                    'rolf',
                    0,
                    $this->getCurrentObject()->getRefId()
                );

                $imp->importObject(
                    null,
                    $_FILES["importfile"]["tmp_name"],
                    $_FILES["importfile"]["name"],
                    'role'
                );
                ilUtil::sendSuccess($this->lng->txt('rbac_role_imported'), true);
                $this->ctrl->redirect($this, 'perm');
                return;
            } catch (Exception $e) {
                ilUtil::sendFailure($e->getMessage());
                $form->setValuesByPost();
                $this->displayImportRoleForm($form);
                return;
            }
        }
        $form->setValuesByPost();
        ilUtil::sendFailure($this->lng->txt('err_check_input'));
        $this->displayImportRoleForm($form);
    }
    
    /**
     * init import form
     */
    protected function initImportForm()
    {
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('rbac_import_role'));
        $form->addCommandButton('doImportRole', $this->lng->txt('import'));
        $form->addCommandButton('perm', $this->lng->txt('cancel'));
        
        $zip = new ilFileInputGUI($this->lng->txt('import_file'), 'importfile');
        $zip->setSuffixes(array('zip'));
        $form->addItem($zip);
        
        return $form;
    }
    
    /**
     * Shoew add role
     * @global type $rbacreview
     * @global type $objDefinition
     * @return ilPropertyFormGUI
     */
    protected function initRoleForm()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        $objDefinition = $DIC['objDefinition'];
        
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
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
        $pro->setValue(1);
        $form->addItem($pro);

        $pd = new ilCheckboxInputGUI($this->lng->txt('rbac_role_add_to_desktop'), 'desktop');
        $pd->setInfo($this->lng->txt('rbac_role_add_to_desktop_info'));
        $pd->setValue(1);
        $form->addItem($pd);

        
        if (!$this->isInAdministration()) {
            $rights = new ilRadioGroupInputGUI($this->lng->txt("rbac_role_rights_copy"), 'rights');
            $option = new ilRadioOption($this->lng->txt("rbac_role_rights_copy_empty"), 0);
            $rights->addOption($option);

            $parent_role_ids = $rbacreview->getParentRoleIds($this->gui_obj->object->getRefId(), true);
            $ids = array();
            foreach ($parent_role_ids as $id => $tmp) {
                $ids[] = $id;
            }

            // Sort ids
            $sorted_ids = ilUtil::_sortIds($ids, 'object_data', 'type DESC,title', 'obj_id');

            $key = 0;
            foreach ($sorted_ids as $id) {
                $par = $parent_role_ids[$id];
                if ($par["obj_id"] != SYSTEM_ROLE_ID) {
                    include_once './Services/AccessControl/classes/class.ilObjRole.php';
                    $option = new ilRadioOption(($par["type"] == 'role' ? $this->lng->txt('obj_role') : $this->lng->txt('obj_rolt')) . ": " . ilObjRole::_getTranslation($par["title"]), $par["obj_id"]);
                    $option->setInfo($par["desc"]);
                    $rights->addOption($option);
                }
                $key++;
            }
            $form->addItem($rights);
        }

        // Local policy only for containers
        if ($objDefinition->isContainer($this->getCurrentObject()->getType())) {
            $check = new ilCheckboxInputGui($this->lng->txt("rbac_role_rights_copy_change_existing"), 'existing');
            $check->setInfo($this->lng->txt('rbac_change_existing_objects_desc_new_role'));
            $form->addItem($check);
        }
    
        return $form;
    }

    /**
     * Show add role form
     */
    protected function displayAddRoleForm()
    {
        $GLOBALS['DIC']['ilTabs']->clearTargets();

        $form = $this->initRoleForm();
        $this->tpl->setContent($form->getHTML());
    }
    
    /**
     * adds a local role
     * This method is only called when choose the option 'you may add local roles'. This option
     * is displayed in the permission settings dialogue for an object
     * TODO: this will be changed
     * @access	public
     *
     */
    protected function addRole()
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $rbacreview = $DIC['rbacreview'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilErr = $DIC['ilErr'];
        $ilCtrl = $DIC['ilCtrl'];

        $form = $this->initRoleForm();
        if ($form->checkInput()) {
            $new_title = $form->getInput("title");
            
            include_once './Services/AccessControl/classes/class.ilObjRole.php';
            $role = new ilObjRole();
            $role->setTitle($new_title);
            $role->setDescription($form->getInput('desc'));
            $role->create();
            
            $GLOBALS['DIC']['rbacadmin']->assignRoleToFolder($role->getId(), $this->getCurrentObject()->getRefId());
            
            // protect
            $rbacadmin->setProtected(
                $this->getCurrentObject()->getRefId(),
                $role->getId(),
                $form->getInput('pro') ? 'y' : 'n'
            );

            // copy rights
            $right_id_to_copy = $form->getInput("rights");
            if ($right_id_to_copy) {
                $parentRoles = $rbacreview->getParentRoleIds($this->getCurrentObject()->getRefId(), true);
                $rbacadmin->copyRoleTemplatePermissions(
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
                            array('all')
                        );
                    } else {
                        $role->changeExistingObjects(
                            $this->getCurrentObject()->getRefId(),
                            ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES,
                            array('all')
                        );
                    }
                }
            }

            // add to desktop items
            if ($form->getInput("desktop")) {
                include_once 'Services/AccessControl/classes/class.ilRoleDesktopItem.php';
                $role_desk_item_obj = new ilRoleDesktopItem($role->getId());
                $role_desk_item_obj->add(
                    $this->getCurrentObject()->getRefId(),
                    ilObject::_lookupType($this->getCurrentObject()->getRefId(), true)
                );
            }

            ilUtil::sendSuccess($this->lng->txt("role_added"), true);
            $this->ctrl->redirect($this, 'perm');
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }
    
    /**
     *
     * @param type $a_blocked_info
     */
    protected function getModifiedBlockedSettings()
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        
        $blocked_info['new_blocked'] = array();
        $blocked_info['new_unblocked'] = array();
        $blocked_info['num'] = 0;
        foreach ((array) $_POST['visible_block'] as $role => $one) {
            $blocked = $rbacreview->isBlockedAtPosition($role, $this->getCurrentObject()->getRefId());
            if (isset($_POST['block'][$role]) && !$blocked) {
                $blocked_info['new_blocked'][] = $role;
                $blocked_info['num']++;
            }
            if (!isset($_POST['block'][$role]) && $blocked) {
                $blocked_info['new_unblocked'][] = $role;
                $blocked_info['num']++;
            }
        }
        return $blocked_info;
    }

    //
    // OrgUnit Position Permissions
    //


    protected function permPositions()
    {
        $perm = self::CMD_PERM_POSITIONS;
        $this->__initSubTabs($perm);

        $ref_id = $this->getCurrentObject()->getRefId();
        $table = new ilOrgUnitPermissionTableGUI($this, $perm, $ref_id);
        $table->collectData();
        $this->tpl->setContent($table->getHTML());
    }


    protected function savePositionsPermissions()
    {
        $this->__initSubTabs(self::CMD_PERM_POSITIONS);

        $positions = ilOrgUnitPosition::getArray(null, 'id');
        $ref_id = $this->getCurrentObject()->getRefId();

        // handle local sets
        foreach ($positions as $position_id) {
            if (isset($_POST['local'][$position_id])) {
                ilOrgUnitPermissionQueries::findOrCreateSetForRefId($ref_id, $position_id);
            } else {
                ilOrgUnitPermissionQueries::removeLocalSetForRefId($ref_id, $position_id);
            }
        }

        if ($_POST['position_perm']) {
            foreach ($_POST['position_perm'] as $position_id => $ops) {
                if (!isset($_POST['local'][$position_id])) {
                    continue;
                }
                $ilOrgUnitPermission = ilOrgUnitPermissionQueries::getSetForRefId($ref_id, $position_id);
                $new_ops = [];
                foreach ($ops as $op_id => $op) {
                    $new_ops[] = ilOrgUnitOperationQueries::findById($op_id);
                }
                $ilOrgUnitPermission->setOperations($new_ops);
                $ilOrgUnitPermission->save();
            }
        }
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, self::CMD_PERM_POSITIONS);
    }
}
