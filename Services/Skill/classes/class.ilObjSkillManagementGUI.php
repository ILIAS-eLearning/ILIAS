<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skill management main GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilObjSkillManagementGUI: ilPermissionGUI, ilSkillProfileGUI, ilExportGUI
 * @ilCtrl_isCalledBy ilObjSkillManagementGUI: ilAdministrationGUI
 */
class ilObjSkillManagementGUI extends ilObjectGUI
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    protected $skill_tree;

    /**
     * Contructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->error = $DIC["ilErr"];
        $this->access = $DIC->access();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $this->tpl = $DIC["tpl"];
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();

        $this->tool_context = $DIC->globalScreen()->tool()->context();

        $this->type = 'skmg';
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('skmg');

        $this->skill_tree = new ilSkillTree();

        $ilCtrl->saveParameter($this, "obj_id");
    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;
        $ilTabs = $this->tabs;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();


        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt('no_permission'), $ilErr->WARNING);
        }

        switch ($next_class) {
            case 'ilskillrootgui':
                $skrt_gui = new ilSkillRootGUI((int) $_GET["obj_id"], $this);
                $skrt_gui->setParentGUI($this);
                $ret = $this->ctrl->forwardCommand($skrt_gui);
                break;

            case 'ilskillcategorygui':
                $this->tabs_gui->activateTab("skills");
                $scat_gui = new ilSkillCategoryGUI((int) $_GET["obj_id"]);
                $scat_gui->setParentGUI($this);
                $this->showTree(false, $scat_gui, "listItems");
                $ret = $this->ctrl->forwardCommand($scat_gui);
                break;

            case 'ilbasicskillgui':
                $this->tabs_gui->activateTab("skills");
                $skill_gui = new ilBasicSkillGUI((int) $_GET["obj_id"]);
                $skill_gui->setParentGUI($this);
                $this->showTree(false, $skill_gui, "edit");
                $ret = $this->ctrl->forwardCommand($skill_gui);
                break;

            case 'ilskilltemplatecategorygui':
                $this->tabs_gui->activateTab("skill_templates");
                $sctp_gui = new ilSkillTemplateCategoryGUI((int) $_GET["obj_id"], (int) $_GET["tref_id"]);
                $sctp_gui->setParentGUI($this);
                $this->showTree(((int) $_GET["tref_id"] == 0), $sctp_gui, "listItems");
                $ret = $this->ctrl->forwardCommand($sctp_gui);
                break;

            case 'ilbasicskilltemplategui':
                $this->tabs_gui->activateTab("skill_templates");
                $sktp_gui = new ilBasicSkillTemplateGUI((int) $_GET["obj_id"], (int) $_GET["tref_id"]);
                $sktp_gui->setParentGUI($this);
                $this->showTree(((int) $_GET["tref_id"] == 0), $sktp_gui, "edit");
                $ret = $this->ctrl->forwardCommand($sktp_gui);
                break;

            case 'ilskilltemplatereferencegui':
                $this->tabs_gui->activateTab("skills");
                $sktr_gui = new ilSkillTemplateReferenceGUI((int) $_GET["tref_id"]);
                $sktr_gui->setParentGUI($this);
                $this->showTree(false, $sktr_gui, "listItems");
                $ret = $this->ctrl->forwardCommand($sktr_gui);
                break;

            case "ilskillprofilegui":
                $ilTabs->activateTab("profiles");
                $skprof_gui = new ilSkillProfileGUI();
                $ret = $this->ctrl->forwardCommand($skprof_gui);
                break;

            case 'ilpermissiongui':
                $this->tabs_gui->activateTab('permissions');
                $perm_gui = new ilPermissionGUI($this);
                $ret = $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilexportgui":
                $this->tabs_gui->activateTab('export');
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                //$exp_gui->addFormat("html", "", $this, "exportHTML");
                $ret = $this->ctrl->forwardCommand($exp_gui);
                break;

            default:
                if (!$cmd || $cmd == 'view') {
                    $cmd = "editSkills";
                }

                if ($cmd == "showTree") {
                    $this->showTree($_GET["templates_tree"]);
                } else {
                    $this->$cmd();
                }
                break;
        }
        return true;
    }

    /**
     * Get tabs
     *
     * @access public
     *
     */
    public function getAdminTabs()
    {
        $rbacsystem = $this->rbacsystem;
        $ilAccess = $this->access;
        $lng = $this->lng;

        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "skills",
                $lng->txt("skmg_skills"),
                $this->ctrl->getLinkTarget($this, "editSkills")
            );

            $this->tabs_gui->addTab(
                "skill_templates",
                $lng->txt("skmg_skill_templates"),
                $this->ctrl->getLinkTarget($this, "editSkillTemplates")
            );

            $this->tabs_gui->addTab(
                "profiles",
                $lng->txt("skmg_skill_profiles"),
                $this->ctrl->getLinkTargetByClass("ilskillprofilegui")
            );

            $this->tabs_gui->addTab(
                "settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "editSettings")
            );

            if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
                $this->tabs_gui->addTab(
                    "export",
                    $lng->txt("export"),
                    $this->ctrl->getLinkTargetByClass("ilexportgui", "")
                );
            }

            if (DEVMODE == 1) {
                $this->tabs_gui->addTab(
                    "test",
                    "Test (DEVMODE)",
                    $this->ctrl->getLinkTarget($this, "test")
                );
            }
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTab(
                "permissions",
                $lng->txt("perm_settings"),
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', "perm")
            );
        }
    }

    /**
    * Edit news settings.
    */
    public function editSettings()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        $ilTabs->activateTab("settings");

        $skmg_set = new ilSkillManagementSettings();
        $enable_skmg = $skmg_set->isActivated();

        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        $form->setTitle($lng->txt("skmg_settings"));

        // Enable skill management
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("skmg_enable_skmg"),
            "enable_skmg"
        );
        $cb_prop->setValue("1");
        $cb_prop->setChecked($enable_skmg);
        $form->addItem($cb_prop);

        // Hide Competence Profile Data before Self-Assessment
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("skmg_hide_profile_self_eval"),
            "hide_profile_self_eval"
        );
        $cb_prop->setValue("1");
        $cb_prop->setInfo($lng->txt("skmg_hide_profile_self_eval_info"));
        $cb_prop->setChecked($skmg_set->getHideProfileBeforeSelfEval());
        $form->addItem($cb_prop);

        // Allow local assignment of global profiles
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("skmg_local_assignment_profiles"),
            "local_assignment_profiles"
        );
        $cb_prop->setValue("1");
        $cb_prop->setChecked($skmg_set->getLocalAssignmentOfProfiles());
        $form->addItem($cb_prop);

        // Allow creation of local profiles
        $cb_prop = new ilCheckboxInputGUI(
            $lng->txt("skmg_allow_local_profiles"),
            "allow_local_profiles"
        );
        $cb_prop->setValue("1");
        $cb_prop->setInfo($lng->txt("skmg_allow_local_profiles_info"));
        $cb_prop->setChecked($skmg_set->getAllowLocalProfiles());
        $form->addItem($cb_prop);

        // command buttons
        if ($this->checkPermissionBool("write")) {
            $form->addCommandButton("saveSettings", $lng->txt("save"));
        }

        $this->tpl->setContent($form->getHTML());
    }

    /**
    * Save skill management settings
    */
    public function saveSettings()
    {
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $skmg_set = new ilSkillManagementSettings();
        $skmg_set->activate((int) $_POST["enable_skmg"]);
        $skmg_set->setHideProfileBeforeSelfEval((int) $_POST["hide_profile_self_eval"]);
        $skmg_set->setLocalAssignmentOfProfiles((int) $_POST["local_assignment_profiles"]);
        $skmg_set->setAllowLocalProfiles((int) $_POST["allow_local_profiles"]);

        ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);

        $ilCtrl->redirect($this, "editSettings");
    }

    /**
     * Edit skills
     */
    public function editSkills()
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilTabs->activateTab("skills");

        $ilCtrl->setParameterByClass("ilobjskillmanagementgui", "obj_id", $this->skill_tree->getRootId());
        $ilCtrl->redirectByClass("ilskillrootgui", "listSkills");
    }


    /**
     * Save all titles of chapters/scos/pages
     */
    public function saveAllTitles($a_succ_mess = true)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (is_array($_POST["title"])) {
            foreach ($_POST["title"] as $id => $title) {
                $node_obj = ilSkillTreeNodeFactory::getInstance($id);
                if (is_object($node_obj)) {
                    // update title
                    ilSkillTreeNode::_writeTitle($id, ilUtil::stripSlashes($title));
                }
            }
            if ($a_succ_mess) {
                ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            }
        }
        $ilCtrl->redirect($this, "editSkills");
    }

    /**
     * Save all titles of chapters/scos/pages
     */
    public function saveAllTemplateTitles($a_succ_mess = true)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (is_array($_POST["title"])) {
            foreach ($_POST["title"] as $id => $title) {
                $node_obj = ilSkillTreeNodeFactory::getInstance($id);
                if (is_object($node_obj)) {
                    // update title
                    ilSkillTreeNode::_writeTitle($id, ilUtil::stripSlashes($title));
                }
            }
            if ($a_succ_mess) {
                ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            }
        }
        $ilCtrl->redirect($this, "editSkillTemplates");
    }


    /**
     * Expand all
     */
    public function expandAll($a_redirect = true)
    {
        $_GET["skexpand"] = "";
        $n_id = ($_GET["obj_id"] > 0)
            ? $_GET["obj_id"]
            : $this->skill_tree->readRootId();
        $stree = $this->skill_tree->getSubTree($this->skill_tree->getNodeData($n_id));
        $n_arr = array();
        foreach ($stree as $n) {
            $n_arr[] = $n["child"];
            $_SESSION["skexpand"] = $n_arr;
        }
        $this->saveAllTitles(false);
    }

    /**
    * Collapse all
    */
    public function collapseAll($a_redirect = true)
    {
        $_GET["skexpand"] = "";
        $n_id = ($_GET["obj_id"] > 0)
            ? $_GET["obj_id"]
            : $this->skill_tree->readRootId();
        $stree = $this->skill_tree->getSubTree($this->skill_tree->getNodeData($n_id));
        $old = $_SESSION["skexpand"];
        foreach ($stree as $n) {
            if (in_array($n["child"], $old) && $n["child"] != $n_id) {
                $k = array_search($n["child"], $old);
                unset($old[$k]);
            }
        }
        $_SESSION["skexpand"] = $old;
        $this->saveAllTitles(false);
    }

    /**
     * confirm deletion screen of skill tree nodes
     */
    public function deleteNodes($a_gui)
    {
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;

        if (!isset($_POST["id"])) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }

        $ilTabs->clearTargets();

        // check usages
        $mode = "";
        $cskill_ids = array();
        foreach ($_POST["id"] as $id) {
            if (in_array(ilSkillTreeNode::_lookupType($id), array("skll", "scat", "sktr"))) {
                if ($mode == "templates") {
                    $this->ilias->raiseError("Skill Deletion - type mismatch.", $this->ilias->error_obj->MESSAGE);
                }
                $mode = "basic";
                $skill_id = $id;
                $tref_id = 0;
                if (ilSkillTreeNode::_lookupType($id) == "sktr") {
                    $skill_id = ilSkillTemplateReference::_lookupTemplateId($id);
                    $tref_id = $id;
                }
                $cskill_ids[] = array("skill_id" => $skill_id, "tref_id" => $tref_id);
            }
            if (in_array(ilSkillTreeNode::_lookupType($id), array("sktp", "sctp"))) {
                if ($mode == "basic") {
                    $this->ilias->raiseError("Skill Deletion - type mismatch.", $this->ilias->error_obj->MESSAGE);
                }
                $mode = "templates";

                foreach (ilSkillTemplateReference::_lookupTrefIdsForTemplateId($id) as $tref_id) {
                    $cskill_ids[] = array("skill_id" => $id, "tref_id" => $tref_id);
                }
            }
            // for cats, skills and template references, get "real" usages
            // for skill and category templates check usage in references
        }

        if ($mode == "basic" || $mode == "templates") {
            $u = new ilSkillUsage();
            $usages = $u->getAllUsagesInfoOfSubtrees($cskill_ids);
            if (count($usages) > 0) {
                $html = "";
                foreach ($usages as $k => $usage) {
                    $tab = new ilSkillUsageTableGUI($this, "showUsage", $k, $usage);
                    $html .= $tab->getHTML() . "<br/><br/>";
                }
                $tpl->setContent($html);
                $ilCtrl->saveParameter($a_gui, "tmpmode");
                $ilToolbar->addButton(
                    $lng->txt("back"),
                    $ilCtrl->getLinkTarget($a_gui, "cancelDelete")
                );
                ilUtil::sendFailure($lng->txt("skmg_cannot_delete_nodes_in_use"));
                return;
            }
        } else {
            $this->ilias->raiseError("Skill Deletion - type mismatch.", $this->ilias->error_obj->MESSAGE);
        }

        // SAVE POST VALUES
        $_SESSION["saved_post"] = $_POST["id"];

        $confirmation_gui = new ilConfirmationGUI();

        $ilCtrl->setParameter($a_gui, "tmpmode", $_GET["tmpmode"]);
        $a_form_action = $this->ctrl->getFormAction($a_gui);
        $confirmation_gui->setFormAction($a_form_action);
        $confirmation_gui->setHeaderText($this->lng->txt("info_delete_sure"));

        // Add items to delete
        foreach ($_POST["id"] as $id) {
            if ($id != IL_FIRST_NODE) {
                $node_obj = ilSkillTreeNodeFactory::getInstance($id);
                $obj_title = (!in_array($node_obj->getType(), ["sktp", "sctp"]))
                    ? $node_obj->getTitle()
                    : $node_obj->getTitle() .
                    " (" .
                    $this->lng->txt("skmg_count_references") . " " .
                    count(ilSkillTemplateReference::_lookupTrefIdsForTopTemplateId($node_obj->getId())) .
                    ")";

                $confirmation_gui->addItem(
                    "id[]",
                    $node_obj->getId(),
                    $obj_title,
                    ilUtil::getImagePath("icon_" . $node_obj->getType() . ".svg")
                );
            }
        }

        $confirmation_gui->setCancel($lng->txt("cancel"), "cancelDelete");
        $confirmation_gui->setConfirm($lng->txt("confirm"), "confirmedDelete");

        $tpl->setContent($confirmation_gui->getHTML());
    }

    /**
     * cancel delete
     */
    public function cancelDelete()
    {
        $this->ctrl->redirect($this, "editSkills");
    }

    /**
     * Delete chapters/scos/pages
     */
    public function confirmedDelete()
    {
        $ilCtrl = $this->ctrl;

        // delete all selected objects
        foreach ($_POST["id"] as $id) {
            if ($id != IL_FIRST_NODE) {
                $obj = ilSkillTreeNodeFactory::getInstance($id);
                $node_data = $this->skill_tree->getNodeData($id);
                if (is_object($obj)) {
                    $obj->delete();
                }
                if ($this->skill_tree->isInTree($id)) {
                    $this->skill_tree->deleteTree($node_data);
                }
            }
        }

        // feedback
        ilUtil::sendInfo($this->lng->txt("info_deleted"), true);
    }

    //
    //
    //	Test
    //
    //

    /**
     * Test getCompletionDateForTriggerRefId
     *
     * @param
     * @return
     */
    public function test()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $this->setTestSubTabs("test");

        $ilTabs->activateTab("test");

        $this->form = new ilPropertyFormGUI();

        if ($this->rbacsystem->checkAccess('write', $_GET['ref_id'])) {
            $this->form->addCommandButton("test", $lng->txt("execute"));
        }

        $this->form->setTitle("getCompletionDateForTriggerRefId()");
        $this->form->setFormAction($ilCtrl->getFormAction($this));

        // user id
        $ti = new ilTextInputGUI("User ID(s)", "user_id");
        $ti->setMaxLength(200);
        $ti->setInfo("Separate multiple IDs by :");
        $ti->setValue($_POST["user_id"]);
        $this->form->addItem($ti);

        // ref id
        $ti = new ilTextInputGUI("Ref ID(s)", "ref_id");
        $ti->setMaxLength(200);
        $ti->setInfo("Separate multiple IDs by :");
        $ti->setValue($_POST["ref_id"]);
        $this->form->addItem($ti);

        $result = "";
        if (isset($_POST["user_id"])) {
            $user_ids = explode(":", $_POST["user_id"]);
            $ref_ids = explode(":", $_POST["ref_id"]);
            if (count($user_ids) <= 1) {
                $user_ids = $user_ids[0];
            }
            if (count($ref_ids) == 1) {
                $ref_ids = $ref_ids[0];
            } elseif (count($ref_ids) == 0) {
                $ref_ids = null;
            }

            $result = ilBasicSkill::getCompletionDateForTriggerRefId($user_ids, $ref_ids);
            $result = "<br />Result:<br />" . var_export($result, true);
        }

        $tpl->setContent($this->form->getHTML() . $result);
    }

    /**
     * Test checkUserCertificateForTriggerRefId
     *
     * @param
     * @return
     */
    public function testCert()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $this->setTestSubTabs("cert");
        $ilTabs->activateTab("test");

        $this->form = new ilPropertyFormGUI();

        if ($this->rbacsystem->checkAccess('write', $_GET['ref_id'])) {
            $this->form->addCommandButton("testCert", $lng->txt("execute"));
        }

        $this->form->setTitle("checkUserCertificateForTriggerRefId()");
        $this->form->setFormAction($ilCtrl->getFormAction($this));

        // user id
        $ti = new ilTextInputGUI("User ID(s)", "user_id");
        $ti->setMaxLength(200);
        $ti->setInfo("Separate multiple IDs by :");
        $ti->setValue($_POST["user_id"]);
        $this->form->addItem($ti);

        // ref id
        $ti = new ilTextInputGUI("Ref ID(s)", "ref_id");
        $ti->setMaxLength(200);
        $ti->setInfo("Separate multiple IDs by :");
        $ti->setValue($_POST["ref_id"]);
        $this->form->addItem($ti);

        $result = "";
        if (isset($_POST["user_id"])) {
            $user_ids = explode(":", $_POST["user_id"]);
            $ref_ids = explode(":", $_POST["ref_id"]);
            if (count($user_ids) <= 1) {
                $user_ids = $user_ids[0];
            }
            if (count($ref_ids) == 1) {
                $ref_ids = $ref_ids[0];
            } elseif (count($ref_ids) == 0) {
                $ref_ids = null;
            }

            $result = ilBasicSkill::checkUserCertificateForTriggerRefId($user_ids, $ref_ids);
            $result = "<br />Result:<br />" . var_export($result, true);
        }

        $tpl->setContent($this->form->getHTML() . $result);
    }

    /**
     * Test getTriggerOfAllCertificates
     *
     * @param
     * @return
     */
    public function testAllCert()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $this->setTestSubTabs("all_cert");
        $ilTabs->activateTab("test");

        $this->form = new ilPropertyFormGUI();

        if ($this->rbacsystem->checkAccess('write', $_GET['ref_id'])) {
            $this->form->addCommandButton("testAllCert", $lng->txt("execute"));
        }

        $this->form->setTitle("getTriggerOfAllCertificates()");
        $this->form->setFormAction($ilCtrl->getFormAction($this));

        // user id
        $ti = new ilTextInputGUI("User ID(s)", "user_id");
        $ti->setMaxLength(200);
        $ti->setInfo("Separate multiple IDs by :");
        $ti->setValue($_POST["user_id"]);
        $this->form->addItem($ti);

        $result = "";
        if (isset($_POST["user_id"])) {
            $user_ids = explode(":", $_POST["user_id"]);
            $ref_ids = explode(":", $_POST["ref_id"]);
            if (count($user_ids) <= 1) {
                $user_ids = $user_ids[0];
            }

            $result = ilBasicSkill::getTriggerOfAllCertificates($user_ids);
            $result = "<br />Result:<br />" . var_export($result, true);
        }

        $tpl->setContent($this->form->getHTML() . $result);
    }

    /**
     * Test getSkillLevelsForTrigger
     *
     * @param
     * @return
     */
    public function testLevels()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $this->setTestSubTabs("levels");
        $ilTabs->activateTab("test");

        $this->form = new ilPropertyFormGUI();

        if ($this->rbacsystem->checkAccess('write', $_GET['ref_id'])) {
            $this->form->addCommandButton("testLevels", $lng->txt("execute"));
        }

        $this->form->setTitle("getTriggerOfAllCertificates()");
        $this->form->setFormAction($ilCtrl->getFormAction($this));

        // user id
        $ti = new ilTextInputGUI("Ref ID", "ref_id");
        $ti->setMaxLength(200);
        $ti->setValue($_POST["ref_id"]);
        $this->form->addItem($ti);

        $result = "";
        if (isset($_POST["ref_id"])) {
            $result = ilBasicSkill::getSkillLevelsForTrigger($_POST["ref_id"]);
            $result = "<br />Result:<br />" . var_export($result, true);
        }

        $tpl->setContent($this->form->getHTML() . $result);
    }


    /**
     * Set test subtabs
     *
     * @param
     * @return
     */
    public function setTestSubtabs($a_act)
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $ilTabs->addSubtab(
            "test",
            "getCompletionDateForTriggerRefId",
            $ilCtrl->getLinkTarget($this, "test")
        );

        $ilTabs->addSubtab(
            "cert",
            "checkUserCertificateForTriggerRefId",
            $ilCtrl->getLinkTarget($this, "testCert")
        );

        $ilTabs->addSubtab(
            "all_cert",
            "getTriggerOfAllCertificates",
            $ilCtrl->getLinkTarget($this, "testAllCert")
        );

        $ilTabs->addSubtab(
            "levels",
            "getSkillLevelsForTrigger",
            $ilCtrl->getLinkTarget($this, "testLevels")
        );

        $ilTabs->activateSubtab($a_act);
    }

    //
    // Skill Templates
    //

    /**
     * Edit skill templates
     */
    public function editSkillTemplates()
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilTabs->activateTab("skill_templates");
        $ilCtrl->setParameterByClass("ilobjskillmanagementgui", "obj_id", $this->skill_tree->getRootId());
        $ilCtrl->redirectByClass("ilskillrootgui", "listTemplates");
    }

    //
    // Tree
    //

    /**
     * Show Editing Tree
     */
    public function showTree($a_templates, $a_gui = "", $a_gui_cmd = "")
    {
        $ilUser = $this->user;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if ($a_templates) {
            if ($_GET["obj_id"] == "" || $_GET["obj_id"] == 1) {
                return;
            }

            if ($_GET["obj_id"] > 1) {
                $path = $this->skill_tree->getPathId($_GET["obj_id"]);
                if (ilSkillTreeNode::_lookupType($path[1]) == "sktp") {
                    return;
                }
            }
        }

        $ilCtrl->setParameter($this, "templates_tree", $a_templates);

        if ($a_templates) {
            $this->tool_context->current()->addAdditionalData(ilSkillGSToolProvider::SHOW_TEMPLATE_TREE, true);
            $exp = new ilSkillTemplateTreeExplorerGUI($this, "showTree");
        } else {
            $this->tool_context->current()->addAdditionalData(ilSkillGSToolProvider::SHOW_SKILL_TREE, true);
            $exp = new ilSkillTreeExplorerGUI($this, "showTree");
        }
        if (!$exp->handleCommand()) {
            $tpl->setLeftNavContent($exp->getHTML());
        }
    }
}
