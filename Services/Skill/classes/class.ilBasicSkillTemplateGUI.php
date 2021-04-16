<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Basic skill template GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_isCalledBy ilBasicSkillTemplateGUI: ilObjSkillManagementGUI
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
    public function __construct($a_node_id = 0, $a_tref_id = 0)
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
        
        parent::__construct($a_node_id);
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
        
        // order nr
        $ni = new ilNumberInputGUI($lng->txt("skmg_order_nr"), "order_nr");
        $ni->setInfo($lng->txt("skmg_order_nr_info"));
        $ni->setMaxLength(6);
        $ni->setSize(6);
        $ni->setRequired(true);
        if ($a_mode == "create") {
            $tree = new ilSkillTree();
            $max = $tree->getMaxOrderNr($this->requested_obj_id, true);
            $ni->setValue($max + 10);
        }
        $this->form->addItem($ni);

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
        
        $ilCtrl->setParameter($this, "obj_id", $this->requested_obj_id);
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

        if ($this->requested_level_id > 0) {
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
        }

        // title
        if ($this->requested_level_id > 0) {
            $tpl->setTitle($lng->txt("skmg_skill_level") . ": " .
                ilBasicSkill::lookupLevelTitle($this->requested_level_id));
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
                $ilCtrl->getLinkTargetByClass("ilobjskillmanagementgui", "editSkillTemplates")
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
        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $it = new ilBasicSkillTemplate();
        $it->setTitle($this->form->getInput("title"));
        $it->setDescription($this->form->getInput("description"));
        $it->setOrderNr($this->form->getInput("order_nr"));
        $it->create();
        ilSkillTreeNode::putInTree($it, $this->requested_obj_id, IL_LAST_NODE);
        $this->node_object = $it;
    }
    
    /**
     * After saving
     */
    public function afterSave()
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->setParameterByClass(
            "ilbasicskilltemplategui",
            "obj_id",
            $this->node_object->getId()
        );
        $ilCtrl->redirectByClass("ilbasicskilltemplategui", "edit");
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
            if ($this->tref_id == 0) {
                $ilToolbar->addButton(
                    $lng->txt("skmg_add_level"),
                    $ilCtrl->getLinkTarget($this, "addLevel")
                );
            }
        }

        $table = new ilSkillLevelTableGUI($this->requested_obj_id, $this, "edit", $this->tref_id, $this->isInUse());
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
            parent::showUsage();
            return;
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
            parent::showObjects();
            return;
        }

        // (b) skill template in templates view

        $this->setTabs("objects");

        $usage_info = new ilSkillUsage();
        $objects = $usage_info->getAssignedObjectsForSkillTemplate($this->base_skill_id);

        $tab = new ilSkillAssignedObjectsTableGUI($this, "showObjects", $objects);

        $tpl->setContent($tab->getHTML());
    }
}
