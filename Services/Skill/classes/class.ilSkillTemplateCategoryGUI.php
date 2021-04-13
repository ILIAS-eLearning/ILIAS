<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skill template category GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_isCalledBy ilSkillTemplateCategoryGUI: ilObjSkillManagementGUI
 */
class ilSkillTemplateCategoryGUI extends ilSkillTreeNodeGUI
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
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var int
     */
    protected $tref_id;


    /**
     * Constructor
     */
    public function __construct($a_node_id = 0, $a_tref_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->help = $DIC["ilHelp"];
        $ilCtrl = $DIC->ctrl();
        
        $ilCtrl->saveParameter($this, "obj_id");
        $this->tref_id = $a_tref_id;
        
        parent::__construct($a_node_id);
    }

    /**
     * Get Node Type
     */
    public function getType()
    {
        return "sctp";
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        
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
     * output tabs
     */
    public function setTabs($a_tab)
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilHelp = $this->help;

        $ilTabs->clearTargets();
        $ilHelp->setScreenIdComponent("skmg_sctp");
        
        // content
        $ilTabs->addTab(
            "content",
            $lng->txt("content"),
            $ilCtrl->getLinkTarget($this, 'listItems')
        );


        // properties
        if ($this->tref_id == 0) {
            $ilTabs->addTab(
                "properties",
                $lng->txt("settings"),
                $ilCtrl->getLinkTarget($this, 'editProperties')
            );
        }

        // usage
        $this->addUsageTab($ilTabs);

        // back link
        if ($this->tref_id == 0) {
            $ilCtrl->setParameterByClass(
                "ilskillrootgui",
                "obj_id",
                $this->node_object->skill_tree->getRootId()
            );
            $ilTabs->setBackTarget(
                $lng->txt("skmg_skill_templates"),
                $ilCtrl->getLinkTargetByClass("ilskillrootgui", "listTemplates")
            );
            $ilCtrl->setParameterByClass(
                "ilskillrootgui",
                "obj_id",
                $_GET["obj_id"]
            );
        }
 
        parent::setTitleIcon();
        $tpl->setTitle(
            $lng->txt("skmg_sctp") . ": " . $this->node_object->getTitle()
        );
        $this->setSkillNodeDescription();
        
        $ilTabs->activateTab($a_tab);
    }

    /**
     * Init form.
     *
     * @param string $a_mode edit mode
     */
    public function initForm($a_mode = "edit")
    {
        parent::initForm($a_mode);
        if ($a_mode == "create") {
            $ni = $this->form->getItemByPostVar("order_nr");
            $tree = new ilSkillTree();
            $max = $tree->getMaxOrderNr((int) $_GET["obj_id"], true);
            $ni->setValue($max + 10);
        }
    }

    /**
     * List items
     */
    public function listItems()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        if ($this->isInUse()) {
            ilUtil::sendInfo($lng->txt("skmg_skill_in_use"));
        }

        if ($this->checkPermissionBool("write")) {
            if ($this->tref_id == 0) {
                self::addCreationButtons();
            }
        }

        $this->setTabs("content");

        $table = new ilSkillCatTableGUI(
            $this,
            "listItems",
            (int) $_GET["obj_id"],
            ilSkillCatTableGUI::MODE_SCTP,
            $this->tref_id
        );
        
        $tpl->setContent($table->getHTML());
    }
    
    /**
     * Add creation buttons
     */
    public static function addCreationButtons()
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilToolbar = $DIC->toolbar();
        $ilUser = $DIC->user();
        
        $ilCtrl->setParameterByClass("ilobjskillmanagementgui", "tmpmode", 1);
        
        $ilCtrl->setParameterByClass(
            "ilbasicskilltemplategui",
            "obj_id",
            (int) $_GET["obj_id"]
        );
        $ilToolbar->addButton(
            $lng->txt("skmg_create_skill_template"),
            $ilCtrl->getLinkTargetByClass("ilbasicskilltemplategui", "create")
        );
        $ilCtrl->setParameterByClass(
            "ilskilltemplatecategorygui",
            "obj_id",
            (int) $_GET["obj_id"]
        );
        $ilToolbar->addButton(
            $lng->txt("skmg_create_skill_template_category"),
            $ilCtrl->getLinkTargetByClass("ilskilltemplatecategorygui", "create")
        );
        
        // skill templates from clipboard
        $sep = false;
        if ($ilUser->clipboardHasObjectsOfType("sktp")) {
            $ilToolbar->addSeparator();
            $sep = true;
            $ilToolbar->addButton(
                $lng->txt("skmg_insert_skill_template_from_clip"),
                $ilCtrl->getLinkTargetByClass("ilskilltemplatecategorygui", "insertSkillTemplateClip")
            );
        }

        // template categories from clipboard
        if ($ilUser->clipboardHasObjectsOfType("sctp")) {
            if (!$sep) {
                $ilToolbar->addSeparator();
                $sep = true;
            }
            $ilToolbar->addButton(
                $lng->txt("skmg_insert_template_category_from_clip"),
                $ilCtrl->getLinkTargetByClass("ilskilltemplatecategorygui", "insertTemplateCategoryClip")
            );
        }
    }
    
    /**
     * Edit properties
     */
    public function editProperties()
    {
        $this->setTabs("properties");
        parent::editProperties();
    }
    
    
    /**
     * Save item
     */
    public function saveItem()
    {
        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $it = new ilSkillTemplateCategory();
        $it->setTitle($this->form->getInput("title"));
        $it->setDescription($this->form->getInput("description"));
        $it->setOrderNr($this->form->getInput("order_nr"));
        $it->create();
        ilSkillTreeNode::putInTree($it, (int) $_GET["obj_id"], IL_LAST_NODE);
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
        $this->node_object->setOrderNr($this->form->getInput("order_nr"));
        $this->node_object->setSelfEvaluation($_POST["self_eval"]);
        $this->node_object->update();
    }

    /**
     * After saving
     */
    public function afterSave()
    {
        $this->redirectToParent(true);
    }

    /**
     * Show skill usage
     */
    public function showUsage()
    {
        $tpl = $this->tpl;

        // (a) referenced skill template category in main tree
        if ($this->tref_id > 0) {
            parent::showUsage();
            return;
        }

        // (b) skill template category in templates view

        $this->setTabs("usage");

        $usage_info = new ilSkillUsage();
        $usages = $usage_info->getAllUsagesOfTemplate((int) $_GET["obj_id"]);

        $html = "";
        foreach ($usages as $k => $usage) {
            $tab = new ilSkillUsageTableGUI($this, "showUsage", $k, $usage);
            $html .= $tab->getHTML() . "<br/><br/>";
        }

        $tpl->setContent($html);
    }
}
