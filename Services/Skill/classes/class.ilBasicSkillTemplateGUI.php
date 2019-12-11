<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilBasicSkillTemplate.php");
include_once("./Services/Skill/classes/class.ilBasicSkillGUI.php");

/**
* Basic skill template GUI class
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ilCtrl_isCalledBy ilBasicSkillTemplateGUI: ilObjSkillManagementGUI
*
* @ingroup ServicesSkill
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

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(200);
        $ti->setSize(50);
        $ti->setRequired(true);
        $this->form->addItem($ti);
        
        // order nr
        $ni = new ilNumberInputGUI($lng->txt("skmg_order_nr"), "order_nr");
        $ni->setInfo($lng->txt("skmg_order_nr_info"));
        $ni->setMaxLength(6);
        $ni->setSize(6);
        $ni->setRequired(true);
        if ($a_mode == "create") {
            include_once("./Services/Skill/classes/class.ilSkillTree.php");
            $tree = new ilSkillTree();
            $max = $tree->getMaxOrderNr((int) $_GET["obj_id"], true);
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

        include_once("./Services/Skill/classes/class.ilSkillTree.php");
        $tree = new ilSkillTree();
        $path = $tree->getPathFull($this->node_object->getId());
        $desc = "";
        foreach ($path as $p) {
            if (in_array($p["type"], array("scat", "skll"))) {
                $desc.= $sep . $p["title"];
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
        $it->setOrderNr($this->form->getInput("order_nr"));
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
            if ($this->checkPermissionBool("write")) {
                if ($this->tref_id == 0) {
                    $ilToolbar->addButton(
                        $lng->txt("skmg_add_level"),
                        $ilCtrl->getLinkTarget($this, "addLevel")
                    );
                }
            }
        }

        include_once("./Services/Skill/classes/class.ilSkillLevelTableGUI.php");
        $table = new ilSkillLevelTableGUI((int) $_GET["obj_id"], $this, "edit", $this->tref_id, $this->isInUse());
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

        include_once("./Services/Skill/classes/class.ilSkillUsage.php");
        $usage_info = new ilSkillUsage();
        $usages = $usage_info->getAllUsagesOfTemplate($this->base_skill_id);

        $html = "";
        include_once("./Services/Skill/classes/class.ilSkillUsageTableGUI.php");
        foreach ($usages as $k => $usage) {
            $tab = new ilSkillUsageTableGUI($this, "showUsage", $k, $usage);
            $html.= $tab->getHTML() . "<br/><br/>";
        }

        $tpl->setContent($html);
    }
}
