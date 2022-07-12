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
 ********************************************************************
 */

use ILIAS\Skill\Tree;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Component\Input\Container\Form\Form;

/**
 * Basic skill GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_isCalledBy ilBasicSkillGUI: ilObjSkillManagementGUI, ilObjSkillTreeGUI
 */
class ilBasicSkillGUI extends ilSkillTreeNodeGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected Factory $ui_fac;
    protected Renderer $ui_ren;
    protected ServerRequestInterface $request;

    protected int $tref_id = 0;
    protected int $requested_level_id = 0;
    protected int $requested_root_id = 0;

    /**
     * @var int[]
     */
    protected array $requested_level_order = [];

    /**
     * @var int[]
     */
    protected array $requested_level_ids = [];

    /**
     * @var int[]
     */
    protected array $requested_resource_ids = [];

    /**
     * @var array<int, bool>
     */
    protected array $requested_suggested = [];

    /**
     * @var array<int, bool>
     */
    protected array $requested_trigger = [];

    public function __construct(Tree\SkillTreeNodeManager $node_manager, int $a_node_id = 0)
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

        $ilCtrl->saveParameter($this, array("node_id", "level_id"));
        $this->base_skill_id = $a_node_id;
        
        parent::__construct($node_manager, $a_node_id);

        $this->requested_level_id = $this->admin_gui_request->getLevelId();
        $this->requested_root_id = $this->admin_gui_request->getRootId();
        $this->requested_level_order = $this->admin_gui_request->getOrder();
        $this->requested_level_ids = $this->admin_gui_request->getLevelIds();
        $this->requested_resource_ids = $this->admin_gui_request->getResourceIds();
        $this->requested_suggested = $this->admin_gui_request->getSuggested();
        $this->requested_trigger = $this->admin_gui_request->getTrigger();
    }

    public function getType() : string
    {
        return "skll";
    }

    public function executeCommand() : void
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

    public function showProperties() : void
    {
        $tpl = $this->tpl;
        
        $this->setTabs();

        $tpl->setContent("Properties");
    }

    public function saveItem() : void
    {
        if (!$this->tree_access_manager->hasManageCompetencesPermission()) {
            return;
        }

        $it = new ilBasicSkill();
        $it->setTitle($this->form->getInput("title"));
        $it->setDescription($this->form->getInput("description"));
        $it->setStatus($this->form->getInput("status"));
        $it->setSelfEvaluation((bool) $this->form->getInput("self_eval"));
        $it->create();
        $this->skill_tree_node_manager->putIntoTree($it, $this->requested_node_id, ilTree::POS_LAST_NODE);
        $this->node_object = $it;
    }

    public function afterSave() : void
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->setParameterByClass(
            "ilbasicskillgui",
            "node_id",
            $this->node_object->getId()
        );
        $ilCtrl->redirectByClass("ilbasicskillgui", "edit");
    }

    public function updateItem() : void
    {
        if (!$this->tree_access_manager->hasManageCompetencesPermission() && $this->getType() == "skll"
            || !$this->tree_access_manager->hasManageCompetenceTemplatesPermission() && $this->getType() == "sktp") {
            return;
        }

        $this->node_object->setTitle($this->form->getInput("title"));
        $this->node_object->setDescription($this->form->getInput("description"));
        $this->node_object->setSelfEvaluation((bool) $this->form->getInput("self_eval"));
        $this->node_object->setStatus($this->form->getInput("status"));
        $this->node_object->update();
    }

    public function edit() : void
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->setTabs("levels");

        if ($this->isInUse()) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("skmg_skill_in_use"));
        } elseif ($this->tree_access_manager->hasManageCompetencesPermission()) {
            $ilToolbar->addButton(
                $lng->txt("skmg_add_level"),
                $ilCtrl->getLinkTarget($this, "addLevel")
            );
        }

        $table = new ilSkillLevelTableGUI(
            $this->base_skill_id,
            $this,
            "edit",
            0,
            $this->isInUse(),
            $this->tree_access_manager->hasManageCompetencesPermission()
        );
        $tpl->setContent($table->getHTML());
    }

    public function initForm(string $a_mode = "edit") : void
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
        if ($this->tree_access_manager->hasManageCompetencesPermission()) {
            if ($a_mode == "create") {
                $this->form->addCommandButton("save", $lng->txt("save"));
                $this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
                $this->form->setTitle($lng->txt("skmg_create_skll"));
            } else {
                $this->form->addCommandButton("update", $lng->txt("save"));
                $this->form->setTitle($lng->txt("skmg_edit_skll"));
            }
        } else {
            foreach ($this->form->getItems() as $item) {
                $item->setDisabled(true);
            }
        }
        
        $ilCtrl->setParameter($this, "node_id", $this->requested_node_id);
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    public function editProperties() : void
    {
        $this->setTabs("properties");
        parent::editProperties();
    }
    

    //
    //
    // Skill level related methods
    //
    //

    public function addLevel() : void
    {
        $tpl = $this->tpl;

        $form = $this->initLevelForm("create");
        $tpl->setContent($this->ui_ren->render([$form]));
    }

    public function editLevel() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        if (!$this->tree_access_manager->hasManageCompetencesPermission() && $this->getType() == "skll"
            || !$this->tree_access_manager->hasManageCompetenceTemplatesPermission() && $this->getType() == "sktp") {
            return;
        }

        if ($this->isInUse()) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("skmg_skill_in_use"));
        }

        $form = $this->initLevelForm();
        $tpl->setContent($this->ui_ren->render([$form]));
    }

    public function saveLevel() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->tree_access_manager->hasManageCompetencesPermission() && $this->getType() == "skll"
            || !$this->tree_access_manager->hasManageCompetenceTemplatesPermission() && $this->getType() == "sktp") {
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

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "edit");
        }

        $tpl->setContent($this->ui_ren->render([$form]));
    }

    public function updateLevel() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        if (!$this->tree_access_manager->hasManageCompetencesPermission() && $this->getType() == "skll"
            || !$this->tree_access_manager->hasManageCompetenceTemplatesPermission() && $this->getType() == "sktp") {
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
                $this->requested_level_id,
                $result["section_level"]["input_ti"]
            );

            $this->node_object->writeLevelDescription(
                $this->requested_level_id,
                $result["section_level"]["input_desc"]
            );

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "edit");
        }

        $tpl->setContent($this->ui_ren->render([$form]));
    }

    public function initLevelForm(string $a_mode = "edit") : Form
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
            $data = $this->node_object->getLevelData($this->requested_level_id);
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

    public function updateLevelOrder() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->tree_access_manager->hasManageCompetencesPermission() && $this->getType() == "skll"
            || !$this->tree_access_manager->hasManageCompetenceTemplatesPermission() && $this->getType() == "sktp") {
            return;
        }

        $order = ilArrayUtil::stripSlashesArray($this->requested_level_order);
        $this->node_object->updateLevelOrder($order);
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "edit");
    }

    public function confirmLevelDeletion() : void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        if (!$this->tree_access_manager->hasManageCompetencesPermission() && $this->getType() == "skll"
            || !$this->tree_access_manager->hasManageCompetenceTemplatesPermission() && $this->getType() == "sktp") {
            return;
        }

        $this->setTabs("levels");

        if (empty($this->requested_level_ids)) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "edit");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("skmg_really_delete_levels"));
            $cgui->setCancel($lng->txt("cancel"), "edit");
            $cgui->setConfirm($lng->txt("delete"), "deleteLevel");

            foreach ($this->requested_level_ids as $i) {
                $cgui->addItem("id[]", $i, ilBasicSkill::lookupLevelTitle($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function deleteLevel() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if (!$this->tree_access_manager->hasManageCompetencesPermission() && $this->getType() == "skll"
            || !$this->tree_access_manager->hasManageCompetenceTemplatesPermission() && $this->getType() == "sktp") {
            return;
        }

        if (!empty($this->requested_level_ids)) {
            foreach ($this->requested_level_ids as $id) {
                $this->node_object->deleteLevel($id);
            }
            $this->node_object->fixLevelNumbering();
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "edit");
    }

    public function setLevelHead() : void
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

        if ($this->requested_level_id > 0) {
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
        if ($this->requested_level_id > 0) {
            $tpl->setTitle($lng->txt("skmg_skill_level") . ": " .
                ilBasicSkill::lookupLevelTitle($this->requested_level_id));
        } else {
            $tpl->setTitle($lng->txt("skmg_skill_level"));
        }

        $desc = $this->skill_tree_node_manager->getWrittenPath($this->node_object->getId());
        $tpl->setDescription($desc);
    }

    public function setTabs(string $a_tab = "levels") : void
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
                "node_id",
                $this->skill_tree_node_manager->getRootId()
            );
            $ilTabs->setBackTarget(
                $lng->txt("skmg_skills"),
                $ilCtrl->getLinkTargetByClass("ilskillrootgui", "listSkills")
            );
            $ilCtrl->setParameterByClass(
                "ilskillrootgui",
                "node_id",
                $this->requested_node_id
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
     * Redirect to parent (identified by current node_id)
     */
    public function redirectToParent(bool $a_tmp_mode = false) : void
    {
        $ilCtrl = $this->ctrl;
        
        $t = ilSkillTreeNode::_lookupType($this->requested_node_id);

        switch ($t) {
            case "skrt":
                $ilCtrl->setParameterByClass("ilskillrootgui", "node_id", $this->requested_node_id);
                $ilCtrl->redirectByClass("ilskillrootgui", "listSkills");
                break;
        }
        
        parent::redirectToParent();
    }

    
    ////
    //// Level resources
    ////

    public function showLevelResources() : void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if ($this->tree_access_manager->hasManageCompetencesPermission() && $this->getType() == "skll"
            || $this->tree_access_manager->hasManageCompetenceTemplatesPermission() && $this->getType() == "sktp") {
            $ilToolbar->addButton(
                $lng->txt("skmg_add_resource"),
                $ilCtrl->getLinkTarget($this, "addLevelResource")
            );
        } else {
            return;
        }

        $this->setLevelHead();
        $ilTabs->activateTab("level_resources");

        $tab = new ilSkillLevelResourcesTableGUI(
            $this,
            "showLevelResources",
            $this->base_skill_id,
            $this->tref_id,
            $this->requested_level_id,
            $this->tree_access_manager->hasManageCompetencesPermission()
        );
        
        $tpl->setContent($tab->getHTML());
    }

    public function addLevelResource() : void
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

    public function saveLevelResource() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ref_id = $this->requested_root_id;

        if ($this->tree_access_manager->hasManageCompetencesPermission() && $this->getType() == "skll"
            || $this->tree_access_manager->hasManageCompetenceTemplatesPermission() && $this->getType() == "sktp") {
            return;
        }

        if ($ref_id > 0) {
            $sres = new ilSkillResources($this->base_skill_id, $this->tref_id);
            $sres->setResourceAsImparting($this->requested_level_id, $ref_id);
            $sres->save();

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }

        $ilCtrl->redirect($this, "showLevelResources");
    }

    public function confirmLevelResourcesRemoval() : void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        if ($this->tree_access_manager->hasManageCompetencesPermission() && $this->getType() == "skll"
            || $this->tree_access_manager->hasManageCompetenceTemplatesPermission() && $this->getType() == "sktp") {
            return;
        }

        $this->setLevelHead();
        $ilTabs->activateTab("level_resources");

        if (empty($this->requested_resource_ids)) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "showLevelResources");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("skmg_confirm_level_resources_removal"));
            $cgui->setCancel($lng->txt("cancel"), "showLevelResources");
            $cgui->setConfirm($lng->txt("remove"), "removeLevelResources");
            
            foreach ($this->requested_resource_ids as $i) {
                $title = ilObject::_lookupTitle(ilObject::_lookupObjId($i));
                $cgui->addItem("id[]", $i, $title);
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }

    public function removeLevelResources() : void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        if ($this->tree_access_manager->hasManageCompetencesPermission() && $this->getType() == "skll"
            || $this->tree_access_manager->hasManageCompetenceTemplatesPermission() && $this->getType() == "sktp") {
            return;
        }

        if (!empty($this->requested_resource_ids)) {
            $sres = new ilSkillResources($this->base_skill_id, $this->tref_id);
            foreach ($this->requested_resource_ids as $i) {
                $sres->setResourceAsImparting($this->requested_level_id, $i, false);
                $sres->setResourceAsTrigger($this->requested_level_id, $i, false);
            }
            $sres->save();
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }
        
        $ilCtrl->redirect($this, "showLevelResources");
    }

    public function saveResourceSettings() : void
    {
        $ilCtrl = $this->ctrl;

        $resources = new ilSkillResources($this->base_skill_id, $this->tref_id);

        foreach ($resources->getResourcesOfLevel($this->requested_level_id) as $r) {
            $imparting = false;
            if (!empty($this->requested_suggested)
                && isset($this->requested_suggested[$r["rep_ref_id"]])
                && $this->requested_suggested[$r["rep_ref_id"]]
            ) {
                $imparting = true;
            }
            $trigger = false;
            if (!empty($this->requested_trigger)
                && isset($this->requested_trigger[$r["rep_ref_id"]])
                && $this->requested_trigger[$r["rep_ref_id"]]
            ) {
                $trigger = true;
            }
            $resources->setResourceAsImparting($this->requested_level_id, $r["rep_ref_id"], $imparting);
            $resources->setResourceAsTrigger($this->requested_level_id, $r["rep_ref_id"], $trigger);
        }
        $resources->save();

        $ilCtrl->redirect($this, "showLevelResources");
    }
}
