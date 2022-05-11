<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\PersonalWorkspace\StandardGUIRequest;
use ILIAS\PersonalWorkspace\WorkspaceSessionRepository;

/**
 * Class ilObjWorkspaceFolderGUI
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilObjWorkspaceFolderGUI: ilCommonActionDispatcherGUI, ilObjectOwnershipManagementGUI
 */
class ilObjWorkspaceFolderGUI extends ilObject2GUI
{
    protected ilHelpGUI $help;
    protected ilTabsGUI $tabs;
    protected \ILIAS\DI\UIServices $ui;
    protected ilWorkspaceFolderUserSettings $user_folder_settings;
    protected int $requested_sortation;
    protected ilLogger $wsp_log;
    protected StandardGUIRequest $std_request;
    protected WorkspaceSessionRepository $session_repo;

    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC;
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $this->lng = $DIC->language();
        $this->help = $DIC["ilHelp"];
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->ui = $DIC->ui();
        $this->session_repo = new WorkspaceSessionRepository();

        $this->std_request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->wsp_log = ilLoggerFactory::getLogger("pwsp");

        $this->user_folder_settings = new ilWorkspaceFolderUserSettings(
            $this->user->getId(),
            new ilWorkspaceFolderUserSettingsRepository($this->user->getId())
        );

        $this->requested_sortation = $this->std_request->getSortation();

        $this->lng->loadLanguageModule("cntr");
    }

    public function getType() : string
    {
        return "wfld";
    }

    protected function setTabs(bool $a_show_settings = true) : void
    {
        $lng = $this->lng;
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent("wfld");
        
        $this->ctrl->setParameter($this, "wsp_id", $this->node_id);

        $this->tabs_gui->addTab(
            "wsp",
            $lng->txt("wsp_tab_personal"),
            $this->ctrl->getLinkTarget($this, "")
        );
        
        $this->ctrl->setParameterByClass(
            "ilObjWorkspaceRootFolderGUI",
            "wsp_id",
            $this->getAccessHandler()->getTree()->getRootId()
        );
        
        $this->tabs_gui->addTab(
            "share",
            $lng->txt("wsp_tab_shared"),
            $this->ctrl->getLinkTargetByClass("ilObjWorkspaceRootFolderGUI", "shareFilter")
        );
        
        $this->tabs_gui->addTab(
            "ownership",
            $lng->txt("wsp_tab_ownership"),
            $this->ctrl->getLinkTargetByClass(array("ilObjWorkspaceRootFolderGUI", "ilObjectOwnershipManagementGUI"), "listObjects")
        );
        
        if (!$this->ctrl->getNextClass($this)) {
            if (stristr($this->ctrl->getCmd(), "share")) {
                $this->tabs_gui->activateTab("share");
            } else {
                $this->tabs_gui->activateTab("wsp");
                $this->addContentSubTabs($a_show_settings);
            }
        }
    }

    public function isActiveAdministrationPanel() : bool
    {
        return (bool) ilSession::get("il_wsp_admin_panel");
    }

    public function setAdministrationPanel(bool $active) : void
    {
        ilSession::set("il_wsp_admin_panel", $active);
    }

    protected function addContentSubTabs(bool $a_show_settings) : void
    {
        $tabs = $this->tabs;
        $ctrl = $this->ctrl;
        $lng = $this->lng;

        if ($this->checkPermissionBool("read")) {
            $tabs->addSubTab("content", $lng->txt("view"), $ctrl->getLinkTarget($this, "disableAdminPanel"));
            $tabs->addSubTab("manage", $lng->txt("cntr_manage"), $ctrl->getLinkTarget($this, "enableAdminPanel"));
        }

        if ($this->checkPermissionBool("write") && $a_show_settings) {
            $this->tabs_gui->addSubTab(
                "settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "edit")
            );
        }

        if ($this->isActiveAdministrationPanel()) {
            $tabs->activateSubTab("manage");
        } else {
            $tabs->activateSubTab("content");
        }
    }

    protected function enableAdminPanel() : void
    {
        $this->setAdministrationPanel(true);
        $this->ctrl->redirect($this, "");
    }

    protected function disableAdminPanel() : void
    {
        $this->setAdministrationPanel(false);
        $this->ctrl->redirect($this, "");
    }

    public function executeCommand() : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            
            case "ilobjectownershipmanagementgui":
                $this->prepareOutput();
                $this->tabs_gui->activateTab("ownership");
                $gui = new ilObjectOwnershipManagementGUI();
                $this->ctrl->forwardCommand($gui);
                break;
            
            default:
                $this->prepareOutput();
                if ($this->type != "wsrt") {
                    $this->addHeaderAction();
                }
                if (!$cmd) {
                    $cmd = "render";
                }
                $this->$cmd();
                break;
        }
    }
    
    protected function initCreationForms($a_new_type) : array
    {
        $forms = array(
            self::CFORM_NEW => $this->initCreateForm($a_new_type)
            );

        return $forms;
    }

    public function render() : void
    {
        $tpl = $this->tpl;
        $ilUser = $this->user;
        $ilCtrl = $this->ctrl;

        //$this->addContentSubTabs();
        $this->showAdministrationPanel();

        $this->session_repo->clearClipboard();
        
        // add new item
        $gui = new ilObjectAddNewItemGUI($this->node_id);
        $gui->setMode(ilObjectDefinition::MODE_WORKSPACE);
        $gui->setCreationUrl($ilCtrl->getLinkTarget($this, "create"));
        $gui->render();
    
        ilObjectListGUI::prepareJsLinks(
            "",
            "",
            $this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "iltagginggui"), "", "", true, false)
        );
        
        $gui = new ilWorkspaceContentGUI(
            $this,
            $this->node_id,
            $this->isActiveAdministrationPanel(),
            $this->getAccessHandler(),
            $this->ui,
            $this->lng,
            $this->user,
            $this->objDefinition,
            $this->ctrl,
            $this->user_folder_settings
        );
        $tpl->setContent($gui->render());

        $exp = new ilWorkspaceExplorerGUI($ilUser->getId(), $this, "render", $this, "", "wsp_id");
        $exp->setTypeWhiteList(array("wsrt", "wfld"));
        $exp->setSelectableTypes(array("wsrt", "wfld"));
        $exp->setLinkToNodeClass(true);
        $exp->setActivateHighlighting(true);
        if ($exp->handleCommand()) {
            return;
        }
        $left = $exp->getHTML();

        $tpl->setLeftNavContent($left);
    }
    
    public function edit() : void
    {
        parent::edit();
      
        $this->tabs_gui->activateTab("wsp");
        $this->tabs_gui->activateSubTab("settings");
    }
    
    public function update() : void
    {
        parent::update();
        
        $this->tabs_gui->activateTab("wsp");
        $this->tabs_gui->activateSubTab("settings");
    }

    public function cut() : void
    {
        $item_ids = $this->std_request->getItemIds();
        if (count($item_ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this);
        }

        // check permission
        $no_cut = array();
        $repo_switch_allowed = true;
        foreach ($item_ids as $item_id) {
            foreach ($this->tree->getSubTree($this->tree->getNodeData($item_id)) as $node) {
                if (ilObject::_lookupType($node["obj_id"]) != "file") {
                    $repo_switch_allowed = false;
                }
                if (!$this->checkPermissionBool("delete", "", "", $node["wsp_id"])) {
                    $obj = ilObjectFactory::getInstanceByObjId($node["obj_id"]);
                    $no_cut[$node["wsp_id"]] = $obj->getTitle();
                    unset($obj);
                }
            }
        }
        if (count($no_cut)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("msg_no_perm_cut") . " " . implode(',', $no_cut), true);
            $this->ctrl->redirect($this);
        }

        // remember source node
        $this->session_repo->setClipboardSourceIds($item_ids);
        $this->session_repo->setClipboardCmd('cut');

        $this->showMoveIntoObjectTree($repo_switch_allowed);
    }
        
    public function cut_for_repository() : void
    {
        $this->session_repo->setClipboardWsp2Repo(true);
        $this->cut();
    }
    
    public function cut_for_workspace() : void
    {
        $this->session_repo->setClipboardWsp2Repo(false);
        $this->cut();
    }

    public function copy() : void
    {
        $ilUser = $this->user;

        $item_ids = $this->std_request->getItemIds();
        if (count($item_ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this);
        }

        // on cancel or fail we return to parent node
        $this->ctrl->setParameter($this, "wsp_id", $this->node_id);

        $repo_switch_allowed = true;
        foreach ($item_ids as $item_id) {
            $node = $this->tree->getNodeData($item_id);
            if (ilObject::_lookupType($node["obj_id"]) != "file") {
                $repo_switch_allowed = false;
            }
            $current_node = $item_id;
            $owner = $this->tree->lookupOwner($current_node);
            if ($owner != $ilUser->getId()) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
                $this->ctrl->redirect($this);
            }
        }

        // remember source node
        $this->session_repo->setClipboardSourceIds($item_ids);
        $this->session_repo->setClipboardCmd('copy');

        $this->showMoveIntoObjectTree($repo_switch_allowed);
    }
    
    public function copyShared() : void
    {
        $ids = $this->std_request->getItemIds();
        if (count($ids) != 1) {
            $this->ctrl->redirect($this, "share");
        }
        
        $current_node = current($ids);
        $handler = $this->getAccessHandler();
        // see ilSharedRessourceGUI::hasAccess()
        if ($handler->checkAccess("read", "", $current_node)) {
            // remember source node
            $this->session_repo->setClipboardSourceIds([$current_node]);
            $this->session_repo->setClipboardCmd('copy');
            $this->session_repo->setClipboardShared(true);
            $this->showMoveIntoObjectTree();
            return;
        } else {
            $perms = $handler->getPermissions($current_node);
            if (in_array(ilWorkspaceAccessGUI::PERMISSION_ALL_PASSWORD, $perms)) {
                $this->passwordForm($current_node);
                return;
            }
        }
        
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
        $this->ctrl->redirect($this, "share");
    }
        
    public function copy_to_repository() : void
    {
        $this->session_repo->setClipboardWsp2Repo(true);
        $this->copy();
    }

    public function copy_to_workspace() : void
    {
        $this->session_repo->setClipboardWsp2Repo(false);
        $this->copy();
    }

    public function showMoveIntoObjectTree(bool $repo_switch_allowed = false) : void
    {
        $ilTabs = $this->tabs;
        $tree = $this->tree;
        
        $ilTabs->clearTargets();

        if (!$this->session_repo->getClipboardShared()) {
            $ilTabs->setBackTarget(
                $this->lng->txt('back'),
                $this->ctrl->getLinkTarget($this)
            );
        } else {
            $ilTabs->setBackTarget(
                $this->lng->txt('back'),
                $this->ctrl->getLinkTarget($this, 'share')
            );
        }
        
        $mode = $this->session_repo->getClipboardCmd();

        $this->tpl->setOnScreenMessage('info', $this->lng->txt('msg_' . $mode . '_clipboard'));
        
        $this->tpl->addBlockFile(
            'ADM_CONTENT',
            'adm_content',
            'tpl.paste_into_multiple_objects.html',
            "Services/Object"
        );
        
        // move/copy in personal workspace
        if (!$this->session_repo->getClipboardWsp2Repo()) {
            $exp = new ilWorkspaceExplorerGUI($this->user->getId(), $this, "showMoveIntoObjectTree", $this, "");
            $exp->setTypeWhiteList(array("wsrt", "wfld"));
            $exp->setSelectableTypes(array("wsrt", "wfld"));
            $exp->setSelectMode("node", false);
            if ($exp->handleCommand()) {
                return;
            }
            $this->tpl->setVariable('OBJECT_TREE', $exp->getHTML());

            // switch to repo?
            if ($repo_switch_allowed) {
                $switch_cmd = ($mode == "cut")
                    ? "cut_for_repository"
                    : "copy_to_repository";
                $this->tpl->setCurrentBlock("switch_button");
                $this->tpl->setVariable('CMD_SWITCH', $switch_cmd);
                $this->tpl->setVariable('TXT_SWITCH', $this->lng->txt('wsp_switch_to_repo_tree'));
                $this->tpl->parseCurrentBlock();

                foreach ($this->std_request->getItemIds() as $id) {
                    $this->tpl->setCurrentBlock("hidden");
                    $this->tpl->setVariable('VALUE', $id);
                    $this->tpl->parseCurrentBlock();
                }
            }
        }
        // move/copy to repository
        else {
            $exp = new ilPasteIntoMultipleItemsExplorer(
                ilPasteIntoMultipleItemsExplorer::SEL_TYPE_RADIO,
                '',
                'paste_' . $mode . '_repexpand'
            );
            $exp->setTargetGet('ref_id');
            
            if ($this->std_request->getPasteExpand($mode) == '') {
                $expanded = $tree->readRootId();
            } else {
                $expanded = $this->std_request->getPasteExpand($mode);
            }
            $exp->setCheckedItems(array($this->std_request->getNode()));
            $exp->setExpandTarget($this->ctrl->getLinkTarget($this, 'showMoveIntoObjectTree'));
            $exp->setPostVar('node');
            $exp->setExpand($expanded);
            $exp->setOutput(0);
            $this->tpl->setVariable('OBJECT_TREE', $exp->getOutput());

            if (in_array($mode, ["copy", "cut"])) {
                $switch_cmd = ($mode == "cut")
                    ? "cut_for_workspace"
                    : "copy_to_workspace";
                $this->tpl->setCurrentBlock("switch_button");
                $this->tpl->setVariable('CMD_SWITCH', $switch_cmd);
                $this->tpl->setVariable('TXT_SWITCH', $this->lng->txt('wsp_switch_to_wsp_tree'));
                $this->tpl->parseCurrentBlock();

                foreach ($this->std_request->getItemIds() as $id) {
                    $this->tpl->setCurrentBlock("hidden");
                    $this->tpl->setVariable('VALUE', $id);
                    $this->tpl->parseCurrentBlock();
                }
            }
        }
        

        unset($exp);

        $this->tpl->setVariable('FORM_TARGET', '_top');
        $this->tpl->setVariable(
            'FORM_ACTION',
            $this->ctrl->getFormAction($this, 'performPasteIntoMultipleObjects')
        );

        $this->tpl->setVariable('CMD_SUBMIT', 'performPasteIntoMultipleObjects');
        $this->tpl->setVariable('TXT_SUBMIT', $this->lng->txt('paste'));
    }

    public function performPasteIntoMultipleObjects() : void
    {
        $ilUser = $this->user;
        $owner = 0;
        $mode = $this->session_repo->getClipboardCmd();
        $source_node_ids = $this->session_repo->getClipboardSourceIds();
        $target_node_id = $this->std_request->getNode();

        if (!is_array($source_node_ids) || count($source_node_ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_at_least_one_object'), true);
            $this->ctrl->redirect($this);
        }
        if (!$target_node_id) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_at_least_one_object'), true);
            $this->ctrl->redirect($this, "showMoveIntoObjectTree");
        }

        if (!$this->session_repo->getClipboardWsp2Repo()) {
            $target_obj_id = $this->tree->lookupObjectId($target_node_id);
        } else {
            $target_obj_id = ilObject::_lookupObjId($target_node_id);
        }
        $target_object = ilObjectFactory::getInstanceByObjId($target_obj_id);

        $fail = array();
        foreach ($source_node_ids as $source_node_id) {
            // object instances
            $source_obj_id = $this->tree->lookupObjectId($source_node_id);
            $source_object = ilObjectFactory::getInstanceByObjId($source_obj_id);


            // sanity checks
            if ($source_node_id == $target_node_id) {
                $fail[] = sprintf(
                    $this->lng->txt('msg_obj_exists_in_folder'),
                    $source_object->getTitle(),
                    $target_object->getTitle()
                );
            }

            if (!in_array($source_object->getType(), array_keys($target_object->getPossibleSubObjects()))) {
                $fail[] = sprintf(
                    $this->lng->txt('msg_obj_may_not_contain_objects_of_type'),
                    $target_object->getTitle(),
                    $source_object->getType()
                );
            }

            // if object is shared permission to copy has been checked above
            $owner = $this->tree->lookupOwner($source_node_id);
            if ($mode == "copy" && $ilUser->getId() == $owner && !$this->checkPermissionBool('copy', '', '', $source_node_id)) {
                $fail[] = $this->lng->txt('permission_denied');
            }

            if (!$this->session_repo->getClipboardWsp2Repo()) {
                if ($mode == "cut" && $this->tree->isGrandChild($source_node_id, $target_node_id)) {
                    $fail[] = sprintf(
                        $this->lng->txt('msg_paste_object_not_in_itself'),
                        $source_object->getTitle()
                    );
                }
            }

            if ($this->session_repo->getClipboardWsp2Repo() == true) {        // see #22959
                global $ilAccess;
                if (!$ilAccess->checkAccess("create", "", $target_node_id, $source_object->getType())) {
                    $fail[] = sprintf(
                        $this->lng->txt('msg_no_perm_paste_object_in_folder'),
                        $source_object->getTitle(),
                        $target_object->getTitle()
                    );
                }
            } else {
                if (!$this->checkPermissionBool('create', '', $source_object->getType(), $target_node_id)) {
                    $fail[] = sprintf(
                        $this->lng->txt('msg_no_perm_paste_object_in_folder'),
                        $source_object->getTitle(),
                        $target_object->getTitle()
                    );
                }
            }
        }

        if (sizeof($fail)) {
            $this->tpl->setOnScreenMessage('failure', implode("<br />", $fail), true);
            $this->ctrl->redirect($this);
        }

        foreach ($source_node_ids as $source_node_id) {
            $source_tree = $this->tree;
            if ($ilUser->getId() != $owner && $mode == "copy") {
                $source_tree = new ilWorkspaceTree($owner);
            }
            $node_data = $source_tree->getNodeData($source_node_id);
            $source_object = ilObjectFactory::getInstanceByObjId($node_data["obj_id"]);

            // move the node
            if ($mode == "cut") {
                if (!$this->session_repo->getClipboardWsp2Repo()) {
                    $this->tree->moveTree($source_node_id, $target_node_id);
                } else {
                    $parent_id = $this->tree->getParentId($source_node_id);

                    // remove from personal workspace
                    $this->getAccessHandler()->removePermission($source_node_id);
                    $this->tree->deleteReference($source_node_id);
                    $source_node = $this->tree->getNodeData($source_node_id);
                    $this->tree->deleteTree($source_node);

                    // add to repository
                    $source_object->createReference();
                    $source_object->putInTree($target_node_id);
                    $source_object->setPermissions($target_node_id);

                    $source_node_id = $parent_id;
                }
            } // copy the node
            elseif ($mode == "copy") {
                $copy_id = ilCopyWizardOptions::_allocateCopyId();
                $wizard_options = ilCopyWizardOptions::_getInstance($copy_id);
                $this->wsp_log->debug("Copy ID: " . $copy_id . ", Source Node: " . $source_node_id
                    . ", source object: " . $source_object->getId());
                if (!$this->session_repo->getClipboardWsp2Repo()) {
                    $wizard_options->disableTreeCopy();
                }
                $wizard_options->saveOwner($ilUser->getId());
                $wizard_options->saveRoot($source_node_id);
                $wizard_options->read();

                $new_obj = $source_object->cloneObject($target_node_id, $copy_id);
                // insert into workspace tree
                if ($new_obj && !$this->session_repo->getClipboardWsp2Repo()) {
                    $this->wsp_log->debug("New Obj ID: " . $new_obj->getId());
                    $new_obj_node_id = $this->tree->insertObject($target_node_id, $new_obj->getId());
                    $this->getAccessHandler()->setPermissions($target_node_id, $new_obj_node_id);
                }

                $wizard_options->deleteAll();
            }
        }
        
        // redirect to target if not repository
        if (!$this->session_repo->getClipboardWsp2Repo()) {
            $redirect_node = $target_node_id;
        } else {
            // reload current folder
            $redirect_node = $this->node_id;
        }
        
        $this->session_repo->clearClipboard();
        
        // #17746
        if ($mode == 'cut') {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_cut_copied'), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_cloned'), true);
        }
        
        $this->ctrl->setParameter($this, "wsp_id", $redirect_node);
        $this->ctrl->redirect($this);
    }
    
    public function shareFilter() : void
    {
        $this->share(false);
    }
    
    public function share(bool $a_load_data = true) : void
    {
        $tpl = $this->tpl;
    
        $tbl = new ilWorkspaceShareTableGUI($this, "share", $this->getAccessHandler(), $this->node_id, $a_load_data);
        $tpl->setContent($tbl->getHTML());
    }
    
    public function applyShareFilter() : void
    {
        $tbl = new ilWorkspaceShareTableGUI($this, "share", $this->getAccessHandler(), $this->node_id);
        $tbl->resetOffset();
        $tbl->writeFilterToSession();
        
        $this->share();
    }
    
    public function resetShareFilter() : void
    {
        $tbl = new ilWorkspaceShareTableGUI($this, "share", $this->getAccessHandler(), $this->node_id);
        $tbl->resetOffset();
        $tbl->resetFilter();
        
        $this->shareFilter();
    }
    
    protected function passwordForm(int $a_node_id, ?ilPropertyFormGUI $form = null) : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        
        $tpl->setTitle($lng->txt("wsp_password_protected_resource"));
        $tpl->setDescription($lng->txt("wsp_password_protected_resource_info"));
        
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "share")
        );
        
        if (!$form) {
            $form = $this->initPasswordForm($a_node_id);
        }
    
        $tpl->setContent($form->getHTML());
    }
    
    protected function initPasswordForm(int $a_node_id) : ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
                        
        $this->ctrl->setParameter($this, "item_ref_id", $a_node_id);
        
        $object_data = $this->getAccessHandler()->getObjectDataFromNode($a_node_id);
        
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "checkPassword"));
        $form->setTitle($lng->txt("wsp_password_for") . ": " . $object_data["title"]);
        
        $password = new ilPasswordInputGUI($lng->txt("password"), "password");
        $password->setRetype(false);
        $password->setRequired(true);
        $password->setSkipSyntaxCheck(true);
        $form->addItem($password);
        
        $form->addCommandButton("checkPassword", $lng->txt("submit"));
        $form->addCommandButton("share", $lng->txt("cancel"));
        
        return $form;
    }
    
    protected function checkPassword() : void
    {
        $lng = $this->lng;

        $ids = $this->std_request->getItemIds();
        if (count($ids) != 1) {
            $this->ctrl->redirect($this, "share");
        }
        $node_id = current($ids);
         
        $form = $this->initPasswordForm($node_id);
        if ($form->checkInput()) {
            $password = ilWorkspaceAccessHandler::getSharedNodePassword($node_id);
            $input = md5($form->getInput("password"));
            if ($input == $password) {
                // we save password and start over
                ilWorkspaceAccessHandler::keepSharedSessionPassword($node_id, $input);
                
                $this->ctrl->setParameter($this, "item_ref_id", $node_id);
                $this->ctrl->redirect($this, "copyShared");
            } else {
                $item = $form->getItemByPostVar("password");
                $item->setAlert($lng->txt("wsp_invalid_password"));
                $this->tpl->setOnScreenMessage('failure', $lng->txt("form_input_not_valid"));
            }
        }
        
        $form->setValuesByPost();
        $this->passwordForm($node_id, $form);
    }
    
    public static function _goto(string $a_target) : void
    {
        global $DIC;
        $ctrl = $DIC->ctrl();
        $id = explode("_", $a_target);
        $ctrl->setParameterByClass(
            "ilsharedresourceGUI",
            "wsp_id",
            $id[0]
        );
        $ctrl->redirectByClass("ilsharedresourceGUI");
    }

    public function listSharedResourcesOfOtherUser() : void
    {
        $tbl = new ilWorkspaceShareTableGUI($this, "share", $this->getAccessHandler(), $this->node_id);
        $tbl->resetOffset();
        $tbl->resetFilter();
        $tbl->writeFilterToSession();
        $this->share();
    }

    protected function deleteConfirmation() : void
    {
        global $DIC;

        $tpl = $DIC["tpl"];
        $lng = $DIC["lng"];

        $item_ids = $this->std_request->getItemIds();

        if (count($item_ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "");
        }

        // on cancel or fail we return to parent node
        //$parent_node = $this->tree->getParentId($node_id);
        //$this->ctrl->setParameter($this, "wsp_id", $parent_node);

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($lng->txt("info_delete_sure") . "<br/>" .
            $lng->txt("info_delete_warning_no_trash"));

        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($lng->txt("cancel"), "cancelDeletion");
        $cgui->setConfirm($lng->txt("confirm"), "confirmedDelete");

        foreach ($item_ids as $node_id) {
            $children = $this->tree->getSubTree($this->tree->getNodeData($node_id));
            foreach ($children as $child) {
                $node_id = $child["wsp_id"];
                $obj_id = $this->tree->lookupObjectId($node_id);
                $type = ilObject::_lookupType($obj_id);
                $title = call_user_func(array(ilObjectFactory::getClassByType($type),'_lookupTitle'), $obj_id);

                // if anything fails, abort the whole process
                if (!$this->checkPermissionBool("delete", "", "", $node_id)) {
                    $this->tpl->setOnScreenMessage('failure', $lng->txt("msg_no_perm_delete") . " " . $title, true);
                    $this->ctrl->redirect($this);
                }

                $cgui->addItem(
                    "id[]",
                    $node_id,
                    $title,
                    ilObject::_getIcon($obj_id, "small", $type),
                    $lng->txt("icon") . " " . $lng->txt("obj_" . $type)
                );
            }
        }

        $tpl->setContent($cgui->getHTML());
    }

    public function cancelDeletion()
    {
        $this->session_repo->clearClipboard();
        parent::cancelDelete();
    }


    //
    // admin panel
    //

    public function showAdministrationPanel() : void
    {
        global $DIC;

        $lng = $this->lng;

        $main_tpl = $DIC->ui()->mainTemplate();

        $lng->loadLanguageModule('cntr');

        if (!$this->session_repo->isClipboardEmpty()) {
            // #11545
            $main_tpl->setPageFormAction($this->ctrl->getFormAction($this));

            $toolbar = new ilToolbarGUI();
            $this->ctrl->setParameter($this, "type", "");
            $this->ctrl->setParameter($this, "item_ref_id", "");

            $toolbar->addFormButton(
                $this->lng->txt('paste_clipboard_items'),
                'paste'
            );

            $toolbar->addFormButton(
                $this->lng->txt('clear_clipboard'),
                'clear'
            );

            $main_tpl->addAdminPanelToolbar($toolbar, true, false);
        } elseif ($this->isActiveAdministrationPanel()) {
            // #11545
            $main_tpl->setPageFormAction($this->ctrl->getFormAction($this));

            $toolbar = new ilToolbarGUI();
            $this->ctrl->setParameter($this, "type", "");
            $this->ctrl->setParameter($this, "item_ref_id", "");

            if ($this->object->gotItems($this->node_id)) {
                $toolbar->setLeadingImage(
                    ilUtil::getImagePath("arrow_upright.svg"),
                    $lng->txt("actions")
                );
                $toolbar->addFormButton(
                    $this->lng->txt('delete_selected_items'),
                    'delete'
                );
                $toolbar->addFormButton(
                    $this->lng->txt('move_selected_items'),
                    'cut'
                );
                $toolbar->addFormButton(
                    $this->lng->txt('copy_selected_items'),
                    'copy'
                );
                $toolbar->addFormButton(
                    $this->lng->txt('download_selected_items'),
                    'download'
                );
                // add download button if multi download enabled
            }

            $main_tpl->addAdminPanelToolbar(
                $toolbar,
                $this->object->gotItems($this->node_id) && $this->session_repo->isClipboardEmpty(),
                ($this->object->gotItems($this->node_id) && $this->session_repo->isClipboardEmpty())
            );

            // form action needed, see http://www.ilias.de/mantis/view.php?id=9630
            if ($this->object->gotItems($this->node_id)) {
                $main_tpl->setPageFormAction($this->ctrl->getFormAction($this));
            }
        }
    }


    /**
     * Set sortation
     */
    protected function setSortation() : void
    {
        $this->user_folder_settings->updateSortation($this->object->getId(), $this->requested_sortation);
        $this->ctrl->redirect($this, "");
    }

    public function download() : void
    {
        // This variable determines whether the task has been initiated by a folder's action drop-down to prevent a folder
        // duplicate inside the zip.
        $initiated_by_folder_action = false;

        $ids = $this->std_request->getItemIds();

        if (count($ids) == 0) {
            $this->ctrl->redirect($this, "");
        }

        $download_job = new ilDownloadWorkspaceFolderBackgroundTask($GLOBALS['DIC']->user()->getId(), $ids, $initiated_by_folder_action);

        $download_job->setBucketTitle($this->getBucketTitle());
        if ($download_job->run()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_bt_download_started'), true);
        }
        $this->ctrl->redirect($this);
    }

    public function getBucketTitle() : string
    {
        return ilFileUtils::getASCIIFilename($this->object->getTitle());
    }
}
