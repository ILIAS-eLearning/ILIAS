<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Basic skill GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_isCalledBy ilBasicSkillGUI: ilObjSkillManagementGUI
 */
class ilBasicSkillGUI extends ilSkillTreeNodeGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var \ILIAS\UI\Factory
     */
    protected $ui_fac;

    /**
     * @var \ILIAS\UI\Renderer
     */
    protected $ui_ren;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    protected $tref_id = 0;
    protected $base_skill_id;
    
    /**
     * Constructor
     */
    public function __construct($a_node_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->help = $DIC["ilHelp"];
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->ui_fac = $DIC->ui()->factory();
        $this->ui_ren = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $ilCtrl = $DIC->ctrl();

        $ilCtrl->saveParameter($this, "obj_id");
        $this->base_skill_id = $a_node_id;
        
        parent::__construct($a_node_id);
    }
    
    /**
     * Get Node Type
     */
    public function getType()
    {
        return "skll";
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;

        //$tpl->getStandardTemplate();
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();
        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }
    }

    /**
     * Show properties
     */
    public function showProperties()
    {
        $tpl = $this->tpl;
        
        $this->setTabs();
        $this->setLocator();

        $tpl->setContent("Properties");
    }

    /**
     * Save item
     */
    public function saveItem()
    {
        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $tree = new ilSkillTree();

        $it = new ilBasicSkill();
        $it->setTitle($this->form->getInput("title"));
        $it->setDescription($this->form->getInput("description"));
        $it->setOrderNr($tree->getMaxOrderNr((int) $_GET["obj_id"]) + 10);
        $it->setStatus($this->form->getInput("status"));
        $it->setSelfEvaluation($_POST["self_eval"]);
        $it->create();
        ilSkillTreeNode::putInTree($it, (int) $_GET["obj_id"], IL_LAST_NODE);
        $this->node_object = $it;
    }

    /**
     * After saving
     */
    public function afterSave()
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->setParameterByClass(
            "ilbasicskillgui",
            "obj_id",
            $this->node_object->getId()
        );
        $ilCtrl->redirectByClass("ilbasicskillgui", "edit");
    }

    /**
     * Update item
     */
    public function updateItem()
    {
        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $this->node_object->setTitle($this->form->getInput("title"));
        $this->node_object->setDescription($this->form->getInput("description"));
        $this->node_object->setSelfEvaluation($_POST["self_eval"]);
        $this->node_object->setStatus($_POST["status"]);
        $this->node_object->update();
    }

    /**
     * Edit skill
     */
    public function edit()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->setTabs("levels");

        if ($this->isInUse()) {
            ilUtil::sendInfo($lng->txt("skmg_skill_in_use"));
        } elseif ($this->checkPermissionBool("write")) {
            $ilToolbar->addButton(
                $lng->txt("skmg_add_level"),
                $ilCtrl->getLinkTarget($this, "addLevel")
            );
        }

        $table = new ilSkillLevelTableGUI($this->base_skill_id, $this, "edit", 0, $this->isInUse());
        $tpl->setContent($table->getHTML());
    }

    /**
     * Init form.
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initForm($a_mode = "edit")
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->form = new ilPropertyFormGUI();

        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(200);
        $ti->setSize(50);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($lng->txt("description"), "description");
        $ta->setRows(5);
        $this->form->addItem($ta);

        // status
        $this->addStatusInput($this->form);

        // selectable
        $cb = new ilCheckboxInputGUI($lng->txt("skmg_selectable"), "self_eval");
        $cb->setInfo($lng->txt("skmg_selectable_info"));
        $this->form->addItem($cb);

        // save and cancel commands
        if ($this->checkPermissionBool("write")) {
            if ($a_mode == "create") {
                $this->form->addCommandButton("save", $lng->txt("save"));
                $this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
                $this->form->setTitle($lng->txt("skmg_create_skll"));
            } else {
                $this->form->addCommandButton("update", $lng->txt("save"));
                $this->form->setTitle($lng->txt("skmg_edit_skll"));
            }
        }
        
        $ilCtrl->setParameter($this, "obj_id", $_GET["obj_id"]);
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }
    
    /**
     * Edit properties
     */
    public function editProperties()
    {
        $this->setTabs("properties");
        parent::editProperties();
    }
    

    //
    //
    // Skill level related methods
    //
    //

    /**
     * Add new level
     */
    public function addLevel()
    {
        $tpl = $this->tpl;

        $form = $this->initLevelForm("create");
        $tpl->setContent($this->ui_ren->render([$form]));
    }

    /**
     * Edit level
     */
    public function editLevel()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        if ($this->isInUse()) {
            ilUtil::sendInfo($lng->txt("skmg_skill_in_use"));
        }

        $form = $this->initLevelForm();
        $tpl->setContent($this->ui_ren->render([$form]));
    }

    /**
     * Save level form
     */
    public function saveLevel() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $form = $this->initLevelForm("create");
        if ($this->request->getMethod() == "POST"
            && $this->request->getQueryParams()["level_settings"] == "level_settings_config") {
            $form = $form->withRequest($this->request);
            $result = $form->getData();

            if (is_null($result)) {
                $tpl->setContent($this->ui_ren->render($form));
                return;
            }

            $this->node_object->addLevel(
                $result["section_level"]["input_ti"],
                $result["section_level"]["input_desc"]
            );

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "edit");
        }

        $tpl->setContent($this->ui_ren->render([$form]));
    }

    /**
     * Update level form
     */
    public function updateLevel() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $form = $this->initLevelForm("edit");
        if ($this->request->getMethod() == "POST"
            && $this->request->getQueryParams()["level_settings"] == "level_settings_config") {
            $form = $form->withRequest($this->request);
            $result = $form->getData();

            if (is_null($result)) {
                $tpl->setContent($this->ui_ren->render($form));
                return;
            }

            $this->node_object->writeLevelTitle(
                (int) $_GET["level_id"],
                $result["section_level"]["input_ti"]
            );

            $this->node_object->writeLevelDescription(
                (int) $_GET["level_id"],
                $result["section_level"]["input_desc"]
            );

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "edit");
        }

        $tpl->setContent($this->ui_ren->render([$form]));
    }

    /**
     * Init level form.
     *
     * @param string $a_mode form mode
     */
    public function initLevelForm($a_mode = "edit")
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $ilCtrl->saveParameter($this, "level_id");
        $this->setLevelHead();
        $ilTabs->activateTab("level_settings");

        $input_ti = $this->ui_fac->input()->field()->text($lng->txt("title"))
            ->withRequired(true);

        $input_desc = $this->ui_fac->input()->field()->textarea($lng->txt("description"));

        $ilCtrl->setParameter(
            $this,
            'level_settings',
            'level_settings_config'
        );

        if ($a_mode == "create") {
            $section_level = $this->ui_fac->input()->field()->section(
                ["input_ti" => $input_ti,
                 "input_desc" => $input_desc],
                $lng->txt("skmg_new_level")
            );
            $form_action = $ilCtrl->getFormAction($this, "saveLevel");
        } else {
            $data = $this->node_object->getLevelData((int) $_GET["level_id"]);
            $input_ti = $input_ti->withValue($data["title"]);
            $input_desc = $input_desc->withValue($data["description"]);

            $section_level = $this->ui_fac->input()->field()->section(
                ["input_ti" => $input_ti,
                 "input_desc" => $input_desc],
                $lng->txt("skmg_edit_level")
            );
            $form_action = $ilCtrl->getFormAction($this, "updateLevel");
        }

        $form = $this->ui_fac->input()->container()->form()->standard(
            $form_action,
            ["section_level" => $section_level]
        );

        return $form;
    }

    /**
     * Update level order
     */
    public function updateLevelOrder()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $order = ilUtil::stripSlashesArray($_POST["order"]);
        $this->node_object->updateLevelOrder($order);
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "edit");
    }

    /**
     * Confirm level deletion
     */
    public function confirmLevelDeletion()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $this->setTabs("levels");

        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "edit");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("skmg_really_delete_levels"));
            $cgui->setCancel($lng->txt("cancel"), "edit");
            $cgui->setConfirm($lng->txt("delete"), "deleteLevel");

            foreach ($_POST["id"] as $i) {
                $cgui->addItem("id[]", $i, ilBasicSkill::lookupLevelTitle($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete levels
     */
    public function deleteLevel()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        if (is_array($_POST["id"])) {
            foreach ($_POST["id"] as $id) {
                $this->node_object->deleteLevel((int) $id);
            }
            $this->node_object->fixLevelNumbering();
        }
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "edit");
    }

    /**
     * Set header for level
     */
    public function setLevelHead()
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilHelp = $this->help;

        // tabs
        $ilTabs->clearTargets();
        $ilHelp->setScreenIdComponent("skmg_lev");
        
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "edit")
        );

        if ($_GET["level_id"] > 0) {
            $ilTabs->addTab(
                "level_settings",
                $lng->txt("settings"),
                $ilCtrl->getLinkTarget($this, "editLevel")
            );

            $ilTabs->addTab(
                "level_resources",
                $lng->txt("skmg_resources"),
                $ilCtrl->getLinkTarget($this, "showLevelResources")
            );
        }

        // title
        if ($_GET["level_id"] > 0) {
            $tpl->setTitle($lng->txt("skmg_skill_level") . ": " .
                ilBasicSkill::lookupLevelTitle((int) $_GET["level_id"]));
        } else {
            $tpl->setTitle($lng->txt("skmg_skill_level"));
        }

        $tree = new ilSkillTree();
        $path = $tree->getPathFull($this->node_object->getId());
        $desc = "";
        $sep = "";
        foreach ($path as $p) {
            if (in_array($p["type"], array("scat", "skll"))) {
                $desc .= $sep . $p["title"];
                $sep = " > ";
            }
        }
        $tpl->setDescription($desc);
    }

    /**
     * Set header for skill
     *
     * @param string $a_tab active tab
     */
    public function setTabs($a_tab = "levels")
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilHelp = $this->help;

        $ilTabs->clearTargets();
        $ilHelp->setScreenIdComponent("skmg_skll");
        //		$ilTabs->setBackTarget($lng->txt("skmg_skill_hierarchie"),
        //			$ilCtrl->getLinkTargetByClass("ilobjskillmanagementgui", "editSkills"));

        if (is_object($this->node_object)) {

            // levels
            $ilTabs->addTab(
                "levels",
                $lng->txt("skmg_skill_levels"),
                $ilCtrl->getLinkTarget($this, 'edit')
            );
    
            // properties
            $ilTabs->addTab(
                "properties",
                $lng->txt("settings"),
                $ilCtrl->getLinkTarget($this, 'editProperties')
            );

            // usage
            $this->addUsageTab($ilTabs);

            // assigned objects
            $this->addObjectsTab($ilTabs);

            $ilCtrl->setParameterByClass(
                "ilskillrootgui",
                "obj_id",
                $this->node_object->skill_tree->getRootId()
            );
            $ilTabs->setBackTarget(
                $lng->txt("obj_skmg"),
                $ilCtrl->getLinkTargetByClass("ilskillrootgui", "listSkills")
            );
            $ilCtrl->setParameterByClass(
                "ilskillrootgui",
                "obj_id",
                $_GET["obj_id"]
            );
            
            $ilTabs->activateTab($a_tab);

            $tpl->setTitle($lng->txt("skmg_skill") . ": " .
                $this->node_object->getTitle());
        
            $this->setSkillNodeDescription();
        } else {
            $tpl->setTitle($lng->txt("skmg_skill"));
            $tpl->setDescription("");
        }
        parent::setTitleIcon();
    }

    /**
     * Select skill level trigger
     */
    public function selectLevelTrigger()
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $tree = $this->tree;
        $tpl = $this->tpl;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $this->setLevelHead();
        $ilTabs->activateTab("level_trigger");

        $exp = new ilSearchRootSelector(
            $ilCtrl->getLinkTarget($this, 'showRepositorySelection')
        );
        $exp->setExpand($_GET["search_root_expand"] ?: $tree->readRootId());
        $exp->setExpandTarget($ilCtrl->getLinkTarget($this, 'selectLevelTrigger'));
        $exp->setTargetClass(get_class($this));
        $exp->setCmd('saveLevelTrigger');
        $exp->setClickableTypes(array("crs"));

        // build html-output
        $exp->setOutput(0);
        $tpl->setContent($exp->getOutput());
    }


    /**
     * Redirect to parent (identified by current obj_id)
     */
    public function redirectToParent($a_tmp_mode = false)
    {
        $ilCtrl = $this->ctrl;
        
        $t = ilSkillTreeNode::_lookupType((int) $_GET["obj_id"]);

        switch ($t) {
            case "skrt":
                $ilCtrl->setParameterByClass("ilskillrootgui", "obj_id", (int) $_GET["obj_id"]);
                $ilCtrl->redirectByClass("ilskillrootgui", "listSkills");
                break;
        }
        
        parent::redirectToParent();
    }

    
    ////
    //// Level resources
    ////
    
    /**
     * Show level resources
     */
    public function showLevelResources()
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if ($this->checkPermissionBool("write")) {
            $ilToolbar->addButton(
                $lng->txt("skmg_add_resource"),
                $ilCtrl->getLinkTarget($this, "addLevelResource")
            );
        }

        $ilCtrl->saveParameter($this, "level_id");
        $this->setLevelHead();
        $ilTabs->activateTab("level_resources");

        $tab = new ilSkillLevelResourcesTableGUI(
            $this,
            "showLevelResources",
            $this->base_skill_id,
            $this->tref_id,
            (int) $_GET["level_id"],
            $this->checkPermissionBool("write")
        );
        
        $tpl->setContent($tab->getHTML());
    }
    
    /**
     * Add level resource
     */
    public function addLevelResource()
    {
        $ilTabs = $this->tabs;
        $tpl = $this->tpl;

        $this->setLevelHead();
        $ilTabs->activateTab("level_resources");

        $exp = new ilRepositorySelectorExplorerGUI(
            $this,
            "addLevelResource",
            $this,
            "saveLevelResource",
            "root_id"
        );
        if (!$exp->handleCommand()) {
            $tpl->setContent($exp->getHTML());
        }
    }

    /**
     * Save level resource
     */
    public function saveLevelResource()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ref_id = (int) $_GET["root_id"];

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        if ($ref_id > 0) {
            $sres = new ilSkillResources($this->base_skill_id, $this->tref_id);
            $sres->setResourceAsImparting((int) $_GET["level_id"], $ref_id);
            $sres->save();

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }

        $ilCtrl->redirect($this, "showLevelResources");
    }

    /**
     * Confirm level resources removal
     */
    public function confirmLevelResourcesRemoval()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $this->setLevelHead();
        $ilTabs->activateTab("level_resources");

        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "showLevelResources");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("skmg_confirm_level_resources_removal"));
            $cgui->setCancel($lng->txt("cancel"), "showLevelResources");
            $cgui->setConfirm($lng->txt("remove"), "removeLevelResources");
            
            foreach ($_POST["id"] as $i) {
                $title = ilObject::_lookupTitle(ilObject::_lookupObjId($i));
                $cgui->addItem("id[]", $i, $title);
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Remove level resource
     */
    public function removeLevelResources()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        if (is_array($_POST["id"])) {
            $sres = new ilSkillResources($this->base_skill_id, $this->tref_id);
            foreach ($_POST["id"] as $i) {
                $sres->setResourceAsImparting((int) $_GET["level_id"], $i, false);
                $sres->setResourceAsTrigger((int) $_GET["level_id"], $i, false);
            }
            $sres->save();
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        
        $ilCtrl->redirect($this, "showLevelResources");
    }

    /**
     * Save resource settings
     */
    public function saveResourceSettings()
    {
        $ilCtrl = $this->ctrl;

        $resources = new ilSkillResources($this->base_skill_id, $this->tref_id);

        foreach ($resources->getResourcesOfLevel((int) $_GET["level_id"]) as $r) {
            $imparting = false;
            if (is_array($_POST["suggested"]) && isset($_POST["suggested"][$r["rep_ref_id"]]) && $_POST["suggested"][$r["rep_ref_id"]]) {
                $imparting = true;
            }
            $trigger = false;
            if (is_array($_POST["trigger"]) && isset($_POST["trigger"][$r["rep_ref_id"]]) && $_POST["trigger"][$r["rep_ref_id"]]) {
                $trigger = true;
            }
            $resources->setResourceAsImparting((int) $_GET["level_id"], $r["rep_ref_id"], $imparting);
            $resources->setResourceAsTrigger((int) $_GET["level_id"], $r["rep_ref_id"], $trigger);
        }
        $resources->save();

        $ilCtrl->redirect($this, "showLevelResources");
    }
}
