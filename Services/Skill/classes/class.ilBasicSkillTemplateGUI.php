<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Skill\Tree;

/**
 * Basic skill template GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_isCalledBy ilBasicSkillTemplateGUI: ilObjSkillManagementGUI, ilObjSkillTreeGUI
 */
class ilBasicSkillTemplateGUI extends ilBasicSkillGUI
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


    /**
     * Constructor
     */
    public function __construct(Tree\SkillTreeNodeManager $node_manager, $a_node_id = 0, $a_tref_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->tpl = $DIC["tpl"];
        $this->help = $DIC["ilHelp"];
        $this->toolbar = $DIC->toolbar();
        $ilCtrl = $DIC->ctrl();
        
        $this->tref_id = $a_tref_id;
        
        $ilCtrl->saveParameter($this, array("obj_id", "level_id"));
        
        parent::__construct($node_manager, $a_node_id);
    }

    /**
     * Get Node Type
     */
    public function getType()
    {
        return "sktp";
    }

    /**
     * Init form.
     *
     * @param string $a_mode edit mode
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

        // save and cancel commands
        if ($this->tree_access_manager->hasManageCompetenceTemplatesPermission()) {
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
        
        $ilCtrl->setParameter($this, "obj_id", $_GET["obj_id"]);
        $this->form->setFormAction($ilCtrl->getFormAction($this));
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
            $lng->txt("skmg_skill_levels"),
            $ilCtrl->getLinkTarget($this, "edit")
        );

        if ($_GET["level_id"] > 0) {
            if ($this->tref_id == 0) {
                $ilTabs->addTab(
                    "level_settings",
                    $lng->txt("settings"),
                    $ilCtrl->getLinkTarget($this, "editLevel")
                );
            } else {
                $ilTabs->addTab(
                    "level_resources",
                    $lng->txt("skmg_resources"),
                    $ilCtrl->getLinkTarget($this, "showLevelResources")
                );
            }

            /*			$ilTabs->addTab("level_trigger",
                            $lng->txt("skmg_trigger"),
                            $ilCtrl->getLinkTarget($this, "editLevelTrigger"));

                        $ilTabs->addTab("level_certificate",
                            $lng->txt("certificate"),
                            $ilCtrl->getLinkTargetByClass("ilcertificategui", "certificateEditor"));*/
        }

        // title
        if ($_GET["level_id"] > 0) {
            $tpl->setTitle($lng->txt("skmg_skill_level") . ": " .
                ilBasicSkill::lookupLevelTitle((int) $_GET["level_id"]));
        } else {
            $tpl->setTitle($lng->txt("skmg_skill_level"));
        }

        $desc = $this->skill_tree_node_manager->getWrittenPath($this->node_object->getId());
        $tpl->setDescription($desc);
        
        $tpl->setTitleIcon(
            ilSkillTreeNode::getIconPath(
                0,
                "sktp",
                "",
                false
            )
        );
    }

    /**
     * Set header for skill
     *
     * @param
     * @return
     */
    public function setTabs($a_tab = "")
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilHelp = $this->help;

        $ilTabs->clearTargets();
        $ilHelp->setScreenIdComponent("skmg_sktp");
        
        if ($this->tref_id == 0) {
            $ilTabs->setBackTarget(
                $lng->txt("skmg_skill_templates"),
                $ilCtrl->getLinkTargetByClass("ilobjskilltreegui", "editSkillTemplates")
            );
        }

        if (is_object($this->node_object)) {
            if ($this->tref_id == 0) {
                $tpl->setTitle($lng->txt("skmg_skill_template") . ": " .
                    $this->node_object->getTitle());
            } else {
                $tpl->setTitle(
                    $this->node_object->getTitle()
                );
            }
            
            // levels
            $ilTabs->addTab(
                "levels",
                $lng->txt("skmg_skill_levels"),
                $ilCtrl->getLinkTarget($this, 'edit')
            );

            // properties
            if ($this->tref_id == 0) {
                $ilTabs->addTab(
                    "properties",
                    $lng->txt("settings"),
                    $ilCtrl->getLinkTarget($this, 'editProperties')
                );
            }

            //			if ($this->tref_id > 0)
            //			{
            // usage
            $this->addUsageTab($ilTabs);
            //			}

            // assigned objects
            $this->addObjectsTab($ilTabs);

            $ilTabs->activateTab($a_tab);

            parent::setTitleIcon();
        
            $this->setSkillNodeDescription();
        } else {
            $tpl->setTitle($lng->txt("skmg_skill"));
            $tpl->setDescription("");
        }
    }

    /**
     * Save item
     */
    public function saveItem()
    {
        if (!$this->tree_access_manager->hasManageCompetenceTemplatesPermission()) {
            return;
        }

        $it = new ilBasicSkillTemplate();
        $it->setTitle($this->form->getInput("title"));
        $it->setDescription($this->form->getInput("description"));
        $it->create();
        $this->skill_tree_node_manager->putIntoTree($it, (int) $_GET["obj_id"], IL_LAST_NODE);
        $this->node_object = $it;
    }
    
    /**
     * After saving
     */
    public function afterSave()
    {
        $ilCtrl = $this->ctrl;

        if (!$this->tree_access_manager->hasManageCompetenceTemplatesPermission()) {
            return;
        }
        
        $ilCtrl->setParameterByClass(
            "ilbasicskilltemplategui",
            "obj_id",
            $this->node_object->getId()
        );
        $ilCtrl->redirectByClass("ilbasicskilltemplategui", "edit");
    }

    /**
     * Edit skill
     *
     * @param
     * @return
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
        } else {
            if ($this->tree_access_manager->hasManageCompetenceTemplatesPermission()) {
                if ($this->tref_id == 0) {
                    $ilToolbar->addButton(
                        $lng->txt("skmg_add_level"),
                        $ilCtrl->getLinkTarget($this, "addLevel")
                    );
                }
            }
        }

        $table = new ilSkillLevelTableGUI(
            (int) $_GET["obj_id"],
            $this,
            "edit",
            $this->tref_id,
            $this->isInUse(),
            $this->tree_access_manager->hasManageCompetenceTemplatesPermission());
        $tpl->setContent($table->getHTML());
    }

    /**
     * Show skill usage
     */
    public function showUsage()
    {
        $tpl = $this->tpl;


        // (a) referenced skill template in main tree
        if ($this->tref_id > 0) {
            return parent::showUsage();
        }

        // (b) skill template in templates view

        $this->setTabs("usage");

        $usage_info = new ilSkillUsage();
        $usages = $usage_info->getAllUsagesOfTemplate($this->base_skill_id);

        $html = "";
        foreach ($usages as $k => $usage) {
            $tab = new ilSkillUsageTableGUI($this, "showUsage", $k, $usage);
            $html .= $tab->getHTML() . "<br/><br/>";
        }

        $tpl->setContent($html);
    }

    /**
     * Show assigned objects
     */
    public function showObjects()
    {
        $tpl = $this->tpl;

        // (a) referenced skill template in main tree
        if ($this->tref_id > 0) {
            return parent::showObjects();
        }

        // (b) skill template in templates view

        $this->setTabs("objects");

        $usage_info = new ilSkillUsage();
        $objects = $usage_info->getAssignedObjectsForSkillTemplate($this->base_skill_id);

        $tab = new ilSkillAssignedObjectsTableGUI($this, "showObjects", $objects);

        $tpl->setContent($tab->getHTML());
    }

    /**
     * Redirect to parent (identified by current obj_id)
     */
    public function redirectToParent($a_tmp_mode = false)
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass("ilskillrootgui", "obj_id", (int) $this->requested_obj_id);
        $ilCtrl->redirectByClass("ilskillrootgui", "listTemplates");
    }
}
