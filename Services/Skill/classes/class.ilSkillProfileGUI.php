<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Skill profile GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilSkillProfileGUI: ilRepositorySearchGUI
 */
class ilSkillProfileGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    protected $profile = null;
    /**
     * @var ilAccessHandler
     */
    public $access;
    /**
     * @var int
     */
    public $ref_id;
    /**
     * @var bool
     */
    public $local_context = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC["tpl"];
        $this->help = $DIC["ilHelp"];
        $this->toolbar = $DIC->toolbar();
        $ilCtrl = $DIC->ctrl();
        $ilAccess = $DIC->access();
        
        $ilCtrl->saveParameter($this, ["sprof_id", "local_context"]);
        $this->access = $ilAccess;
        $this->ref_id = (int) $_GET["ref_id"];

        if ((int) $_GET["sprof_id"] > 0) {
            $this->id = (int) $_GET["sprof_id"];
        }
        
        if ($this->id > 0) {
            $this->profile = new ilSkillProfile($this->id);
            if ($this->profile->getRefId() > 0 && (bool) $_GET["local_context"]) {
                $this->local_context = true;
            }
        }
    }

    /**
     * Check permission pool
     *
     * @param string $a_perm
     * @return bool
     */
    public function checkPermissionBool($a_perm)
    {
        return $this->access->checkAccess($a_perm, "", $this->ref_id);
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $cmd = $ilCtrl->getCmd("listProfiles");
        $next_class = $ilCtrl->getNextClass();
        switch ($next_class) {
            case 'ilrepositorysearchgui':
                $user_search = new ilRepositorySearchGUI();
                $user_search->setTitle($lng->txt('skmg_add_user_to_profile'));
                $user_search->setCallback($this, 'assignUser');
                $user_search->setRoleCallback($this, 'assignRole');

                // Set tabs
                //$this->tabs_gui->setTabActive('user_assignment');
                $ilCtrl->setReturn($this, 'showUsers');
                $ret = $ilCtrl->forwardCommand($user_search);
                break;
            
            default:
                if (in_array($cmd, array("listProfiles", "create", "edit", "save", "update",
                    "confirmDeleteProfiles", "deleteProfiles", "showLevels", "assignLevel",
                    "assignLevelSelectSkill", "assignLevelToProfile",
                    "confirmLevelAssignmentRemoval", "removeLevelAssignments",
                    "showUsers", "assignUser", "assignRole",
                    "confirmUserRemoval", "removeUsers", "exportProfiles", "showImportForm",
                    "importProfiles", "saveLevelOrder", "createLocal", "saveLocal",
                    "listLocalProfiles", "showLevelsWithLocalContext", "showObjects"))) {
                    $this->$cmd();
                }
                break;
        }
    }
    
    /**
     * Set tabs
     *
     * @param
     * @return
     */
    public function setTabs($a_active)
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilHelp = $this->help;
        
        $tpl->setTitle($lng->txt("skmg_profile") . ": " .
            $this->profile->getTitle());
        $tpl->setDescription("");
        
        $ilTabs->clearTargets();
        $ilHelp->setScreenIdComponent("skmg_prof");
        
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "")
        );

        // levels
        $ilTabs->addTab(
            "levels",
            $lng->txt("skmg_assigned_skill_levels"),
            $ilCtrl->getLinkTarget($this, "showLevels")
        );

        // users
        $ilTabs->addTab(
            "users",
            $lng->txt("skmg_assigned_users"),
            $ilCtrl->getLinkTarget($this, "showUsers")
        );

        // objects
        $ilTabs->addTab(
            "objects",
            $lng->txt("skmg_assigned_objects"),
            $ilCtrl->getLinkTarget($this, "showObjects")
        );
        
        // settings
        $ilTabs->addTab(
            "settings",
            $lng->txt("settings"),
            $ilCtrl->getLinkTarget($this, "edit")
        );

        $ilTabs->activateTab($a_active);
    }
    
    
    /**
     * List profiles
     */
    public function listProfiles()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if ($this->checkPermissionBool("write")) {
            $ilToolbar->addButton(
                $lng->txt("skmg_add_profile"),
                $ilCtrl->getLinkTarget($this, "create")
            );

            $ilToolbar->addButton(
                $lng->txt("import"),
                $ilCtrl->getLinkTarget($this, "showImportForm")
            );
        }

        $tab = new ilSkillProfileTableGUI($this, "listProfiles", $this->checkPermissionBool("write"));
        
        $tpl->setContent($tab->getHTML());
    }

    public function listLocalProfiles()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirectByClass("ilcontskilladmingui", "listProfiles");
    }
    
    /**
     * Create
     */
    public function create()
    {
        $tpl = $this->tpl;
        
        $form = $this->initProfileForm("create");
        $tpl->setContent($form->getHTML());
    }

    public function createLocal()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $tabs = $this->tabs;

        $tabs->clearTargets();
        $tabs->setBackTarget(
            $lng->txt("back_to_course"),
            $ctrl->getLinkTargetByClass("ilcontskilladmingui", "listProfiles")
        );

        $form = $this->initProfileForm("createLocal");
        $tpl->setContent($form->getHTML());
    }
    
    /**
     * Edit
     */
    public function edit()
    {
        $tpl = $this->tpl;
        
        $this->setTabs("settings");
        $form = $this->initProfileForm("edit");
        $tpl->setContent($form->getHTML());
    }
    
    
    /**
     * Init profile form.
     *
     * @param string $a_mode edit mode
     */
    public function initProfileForm($a_mode = "edit")
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();
        
        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(200);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);
        
        // description
        $desc = new ilTextAreaInputGUI($lng->txt("description"), "description");
        $desc->setCols(40);
        $desc->setRows(4);
        $form->addItem($desc);
    
        // save and cancel commands
        if ($this->checkPermissionBool("write")) {
            if ($a_mode == "create") {
                $form->addCommandButton("save", $lng->txt("save"));
                $form->addCommandButton("listProfiles", $lng->txt("cancel"));
                $form->setTitle($lng->txt("skmg_add_profile"));
            } elseif ($a_mode == "createLocal") {
                $form->addCommandButton("saveLocal", $lng->txt("save"));
                $form->addCommandButton("listLocalProfiles", $lng->txt("cancel"));
                $form->setTitle($lng->txt("skmg_add_local_profile"));
            } else {
                // set values
                $ti->setValue($this->profile->getTitle());
                $desc->setValue($this->profile->getDescription());

                $form->addCommandButton("update", $lng->txt("save"));
                $form->addCommandButton("listProfiles", $lng->txt("cancel"));
                $form->setTitle($lng->txt("skmg_edit_profile"));
            }
        }

        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }
    
    /**
     * Save profile form
     */
    public function save()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $form = $this->initProfileForm("create");
        if ($form->checkInput()) {
            $prof = new ilSkillProfile();
            $prof->setTitle($form->getInput("title"));
            $prof->setDescription($form->getInput("description"));
            $prof->create();
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listProfiles");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHtml());
        }
    }

    public function saveLocal()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $form = $this->initProfileForm("createLocal");
        if ($form->checkInput()) {
            $prof = new ilSkillProfile();
            $prof->setTitle($form->getInput("title"));
            $prof->setDescription($form->getInput("description"));
            $prof->setRefId($this->ref_id);
            $prof->create();
            $prof->addRoleToProfile(ilParticipants::getDefaultMemberRole($this->ref_id));
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirectByClass("ilcontskilladmingui", "listProfiles");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHtml());
        }
    }
    
    /**
     * Update
     */
    public function update()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $form = $this->initProfileForm("edit");
        if ($form->checkInput()) {
            $this->profile->setTitle($form->getInput("title"));
            $this->profile->setDescription($form->getInput("description"));
            $this->profile->update();
            
            ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listProfiles");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHtml());
        }
    }
    
    /**
     * Confirm profile deletion
     */
    public function confirmDeleteProfiles()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
            
        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listProfiles");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("skmg_delete_profiles"));
            $cgui->setCancel($lng->txt("cancel"), "listProfiles");
            $cgui->setConfirm($lng->txt("delete"), "deleteProfiles");
            
            foreach ($_POST["id"] as $i) {
                $cgui->addItem("id[]", $i, ilSkillProfile::lookupTitle($i));
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete profiles
     */
    public function deleteProfiles()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $i) {
                $prof = new ilSkillProfile($i);
                $prof->delete();
            }
            ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
        }
        
        $ilCtrl->redirect($this, "listProfiles");
    }
    
    ////
    //// skill profile levels
    ////
    
    /**
     * Show skill levels
     *
     * @param
     * @return
     */
    public function showLevels()
    {
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilToolbar = $this->toolbar;
        
        $this->setTabs("levels");

        if ($this->checkPermissionBool("write")) {
            $ilToolbar->addButton(
                $lng->txt("skmg_assign_level"),
                $ilCtrl->getLinkTarget($this, "assignLevel")
            );
        }
        
        $tab = new ilSkillProfileLevelsTableGUI(
            $this,
            "showLevels",
            $this->profile,
            $this->checkPermissionBool("write")
        );
        $tpl->setContent($tab->getHTML());
    }

    public function showLevelsWithLocalContext()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ctrl = $this->ctrl;
        $tabs = $this->tabs;
        $toolbar = $this->toolbar;

        $tabs->clearTargets();
        $tabs->setBackTarget(
            $lng->txt("back_to_course"),
            $ctrl->getLinkTargetByClass("ilcontskilladmingui", "listProfiles")
        );

        if ($this->checkPermissionBool("write")) {
            $toolbar->addButton(
                $lng->txt("skmg_assign_level"),
                $ctrl->getLinkTarget($this, "assignLevel")
            );
        }

        $tab = new ilSkillProfileLevelsTableGUI(
            $this,
            "showLevelsWithLocalContext",
            $this->profile,
            $this->checkPermissionBool("write")
        );
        $tpl->setContent($tab->getHTML());
    }
    
    /**
     * Assign Level
     */
    public function assignLevel()
    {
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $local = $this->local_context;
        
        $tpl->setTitle($lng->txt("skmg_profile") . ": " .
            $this->profile->getTitle());
        $tpl->setDescription("");

        //$this->setTabs("levels");
        
        ilUtil::sendInfo($lng->txt("skmg_select_skill_level_assign"));
        
        $ilTabs->clearTargets();
        if ($local) {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "showLevelsWithLocalContext")
            );
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "showLevels")
            );
        }


        $exp = new ilSkillSelectorGUI($this, "assignLevel", $this, "assignLevelSelectSkill", "cskill_id");
        if (!$exp->handleCommand()) {
            $tpl->setContent($exp->getHTML());
        }
    }
    
    /**
     * Output level table for profile assignment
     */
    public function assignLevelSelectSkill()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $local = $this->local_context;

        $ilCtrl->saveParameter($this, "cskill_id");
        
        $tpl->setTitle($lng->txt("skmg_profile") . ": " .
            $this->profile->getTitle());
        $tpl->setDescription("");

        $ilTabs->clearTargets();
        if ($local) {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "showLevelsWithLocalContext")
            );
        } else {
            $ilTabs->setBackTarget(
                $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "showLevels")
            );
        }

        $tab = new ilSkillLevelProfileAssignmentTableGUI(
            $this,
            "assignLevelSelectSkill",
            $_GET["cskill_id"]
        );
        $tpl->setContent($tab->getHTML());
    }
    
    /**
     * Assign level to profile
     */
    public function assignLevelToProfile()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $local = $this->local_context;

        if (!$this->checkPermissionBool("write")) {
            return;
        }


        $parts = explode(":", $_GET["cskill_id"]);

        $this->profile->addSkillLevel(
            (int) $parts[0],
            (int) $parts[1],
            (int) $_GET["level_id"],
            (int) $this->profile->getMaxLevelOrderNr() + 10
        );
        $this->profile->update();
        
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        if ($local) {
            $ilCtrl->redirect($this, "showLevelsWithLocalContext");
        }
        $ilCtrl->redirect($this, "showLevels");
    }
    
    /**
     * Confirm level assignment removal
     */
    public function confirmLevelAssignmentRemoval()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $tabs = $this->tabs;
        $local = $this->local_context;

        if ($local) {
            $tabs->clearTargets();
        } else {
            $this->setTabs("levels");
        }
            
        if (!is_array($_POST["ass_id"]) || count($_POST["ass_id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            if ($local) {
                $ilCtrl->redirect($this, "showLevelsWithLocalContext");
            }
            $ilCtrl->redirect($this, "showLevels");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("skmg_confirm_remove_level_ass"));
            if ($local) {
                $cgui->setCancel($lng->txt("cancel"), "showLevelsWithLocalContext");
            } else {
                $cgui->setCancel($lng->txt("cancel"), "showLevels");
            }
            $cgui->setConfirm($lng->txt("remove"), "removeLevelAssignments");
            
            foreach ($_POST["ass_id"] as $i) {
                $id_arr = explode(":", $i);
                $cgui->addItem(
                    "ass_id[]",
                    $i,
                    ilBasicSkill::_lookupTitle($id_arr[0]) . ": " .
                    ilBasicSkill::lookupLevelTitle($id_arr[2])
                );
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Remove level assignment
     *
     * @param
     * @return
     */
    public function removeLevelAssignments()
    {
        $ilCtrl = $this->ctrl;
        $local = $this->local_context;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        if (is_array($_POST["ass_id"])) {
            foreach ($_POST["ass_id"] as $i) {
                $id_arr = explode(":", $i);
                $this->profile->removeSkillLevel($id_arr[0], $id_arr[1], $id_arr[2], $id_arr[3]);
            }
            $this->profile->update();
            $this->profile->fixSkillOrderNumbering();
        }

        if ($local) {
            $ilCtrl->redirect($this, "showLevelsWithLocalContext");
        }
        $ilCtrl->redirect($this, "showLevels");
    }

    /**
     * Save level order
     */
    public function saveLevelOrder()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $local = $this->local_context;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $order = ilUtil::stripSlashesArray($_POST["order"]);
        $this->profile->updateSkillOrder($order);

        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        if ($local) {
            $ilCtrl->redirect($this, "showLevelsWithLocalContext");
        }
        $ilCtrl->redirect($this, "showLevels");
    }
    
    /**
     * Show users
     */
    public function showUsers()
    {
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        
        // add member
        if ($this->checkPermissionBool("write") && !$this->profile->getRefId() > 0) {
            ilRepositorySearchGUI::fillAutoCompleteToolbar(
                $this,
                $ilToolbar,
                array(
                    'auto_complete_name' => $lng->txt('user'),
                    'submit_name' => $lng->txt('skmg_assign_user')
                )
            );

            $ilToolbar->addSeparator();

            $button = ilLinkButton::getInstance();
            $button->setCaption("skmg_add_assignment");
            $button->setUrl($this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI', 'start'));
            $ilToolbar->addButtonInstance($button);
        }

        $this->setTabs("users");
        
        $tab = new ilSkillProfileUserTableGUI(
            $this,
            "showUsers",
            $this->profile,
            $this->checkPermissionBool("write")
        );
        $tpl->setContent($tab->getHTML());
    }

    /**
     * Assign user
     *
     * @param
     * @return
     */
    public function assignUser()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        // user assignment with toolbar
        $user_id = ilObjUser::_lookupId(ilUtil::stripSlashes($_POST["user_login"]));
        if ($user_id > 0) {
            $this->profile->addUserToProfile($user_id);
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }

        // user assignment with ilRepositorySearchGUI
        $users = $_POST['user'];
        if (is_array($users)) {
            foreach ($users as $id) {
                if ($id > 0) {
                    $this->profile->addUserToProfile($id);
                }
            }
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
    
        $ilCtrl->redirect($this, "showUsers");
    }

    /**
     * Assign role
     */
    public function assignRole(array $role_ids)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $success = false;
        foreach ($role_ids as $id) {
            if ($id > 0) {
                $this->profile->addRoleToProfile($id);
                $success = true;
            }
        }
        if ($success) {
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }

        $ilCtrl->redirect($this, "showUsers");
    }
    
    /**
     * Confirm user removal
     */
    public function confirmUserRemoval()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $this->setTabs("users");

        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "showUsers");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("skmg_confirm_user_removal"));
            $cgui->setCancel($lng->txt("cancel"), "showUsers");
            $cgui->setConfirm($lng->txt("remove"), "removeUsers");

            foreach ($_POST["id"] as $i) {
                $type = ilObject::_lookupType($i);

                switch ($type) {
                    case 'usr':
                        $usr_name = ilUserUtil::getNamePresentation($i);
                        $cgui->addItem(
                            "id[]",
                            $i,
                            $usr_name
                        );
                        break;

                    case 'role':
                        $role_name = ilObjRole::_lookupTitle($i);
                        $cgui->addItem(
                            "id[]",
                            $i,
                            $role_name
                        );
                        break;

                    default:
                        echo 'not defined';
                }
            }

            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Remove users
     */
    public function removeUsers()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $i) {
                $type = ilObject::_lookupType($i);
                switch ($type) {
                    case 'usr':
                        $this->profile->removeUserFromProfile((int) $i);
                        break;

                    case 'role':
                        $this->profile->removeRoleFromProfile((int) $i);
                        break;

                    default:
                        echo 'not deleted';
                }
            }
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "showUsers");
    }

    /**
     * Show assigned objects
     */
    public function showObjects()
    {
        $tpl = $this->tpl;

        $this->setTabs("objects");

        $usage_info = new ilSkillUsage();
        $objects = $usage_info->getAssignedObjectsForSkillProfile($this->profile->getId());

        $tab = new ilSkillAssignedObjectsTableGUI(
            $this,
            "showObjects",
            $objects
        );
        $tpl->setContent($tab->getHTML());
    }

    /**
     * Export profiles
     *
     * @param
     * @return
     */
    public function exportProfiles()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "");
        }

        $exp = new ilExport();
        $conf = $exp->getConfig("Services/Skill");
        $conf->setMode(ilSkillExportConfig::MODE_PROFILES);
        $conf->setSelectedProfiles($_POST["id"]);
        $exp->exportObject("skmg", ilObject::_lookupObjId((int) $_GET["ref_id"]));

        //ilExport::_createExportDirectory(0, "xml", "");
        //$export_dir = ilExport::_getExportDirectory($a_id, "xml", $a_type);
        //$exp->exportEntity("skprof", $_POST["id"], "", "Services/Skill", $a_title, $a_export_dir, "skprof");

        $ilCtrl->redirectByClass(array("iladministrationgui", "ilobjskillmanagementgui", "ilexportgui"), "");
    }

    /**
     * Show import form
     */
    public function showImportForm()
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        $tpl->setContent($this->initInputForm()->getHTML());
    }

    /**
     * Init input form.
     */
    public function initInputForm()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();

        $fi = new ilFileInputGUI($lng->txt("skmg_input_file"), "import_file");
        $fi->setSuffixes(array("zip"));
        $fi->setRequired(true);
        $form->addItem($fi);

        // save and cancel commands
        $form->addCommandButton("importProfiles", $lng->txt("import"));
        $form->addCommandButton("", $lng->txt("cancel"));

        $form->setTitle($lng->txt("import"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    /**
     * Import profiles
     */
    public function importProfiles()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = $this->initInputForm();
        if ($form->checkInput()) {
            $imp = new ilImport();
            $imp->importEntity($_FILES["import_file"]["tmp_name"], $_FILES["import_file"]["name"], "skmg", "Services/Skill");

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHtml());
        }
    }
}
