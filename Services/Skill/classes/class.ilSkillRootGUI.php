<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Skill\Tree;

/**
 * Skill root GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_isCalledBy ilSkillRootGUI: ilObjSkillManagementGUI, ilObjSkillTreeGUI
 */
class ilSkillRootGUI extends ilSkillTreeNodeGUI
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
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilLanguage
     */
    protected $lng;


    /**
     * Constructor
     */
    public function __construct(Tree\SkillTreeNodeManager $node_manager, $a_node_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        $ilCtrl->saveParameter($this, "obj_id");
        
        parent::__construct($node_manager, $a_node_id);
    }

    /**
     * Get Node Type
     */
    public function getType()
    {
        return "skrt";
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        
        $next_class = $ilCtrl->getNextClass($this);
        $cmd = $ilCtrl->getCmd();

        switch ($next_class) {
            default:
                $ret = $this->$cmd();
                break;
        }
    }
    
    /**
     * List templates
     */
    public function listTemplates()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        
        $skmg_set = new ilSetting("skmg");
        $enable_skmg = $skmg_set->get("enable_skmg");
        if (!$enable_skmg) {
            ilUtil::sendInfo($lng->txt("skmg_skill_management_deactivated"));
        }

        $this->getParentGUI()->showTree(true, $this, "listTemplates");
        $ilTabs->activateTab("skill_templates");

        if ($this->tree_access_manager->hasManageCompetenceTemplatesPermission()) {
            ilSkillTemplateCategoryGUI::addCreationButtons();
        }

        $table = new ilSkillCatTableGUI(
            $this,
            "listTemplates",
            (int) $_GET["obj_id"],
            ilSkillCatTableGUI::MODE_SCTP
        );
        
        $tpl->setContent($table->getHTML());
    }

    /**
     * List skills
     */
    public function listSkills()
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        $skmg_set = new ilSetting("skmg");
        $enable_skmg = $skmg_set->get("enable_skmg");
        if (!$enable_skmg) {
            ilUtil::sendInfo($lng->txt("skmg_skill_management_deactivated"));
        }

        $this->getParentGUI()->showTree(false, $this, "listSkills");
        $ilTabs->activateTab("skills");

        if ($this->tree_access_manager->hasManageCompetencesPermission()) {
            ilSkillCategoryGUI::addCreationButtons();
        }

        $table = new ilSkillCatTableGUI(
            $this,
            "listSkills",
            $this->requested_obj_id,
            ilSkillCatTableGUI::MODE_SCAT
        );
        
        $tpl->setContent($table->getHTML());
    }
    
    /**
     * cancel delete
     */
    public function cancelDelete()
    {
        $ilCtrl = $this->ctrl;

        if ($_GET["tmpmode"]) {
            $ilCtrl->redirect($this, "listTemplates");
        } else {
            $ilCtrl->redirect($this, "listSkills");
        }
    }

    /**
     * Show import form
     */
    public function showImportForm()
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $ilTabs->setBackTarget(
            $lng->txt("obj_skmg"),
            $ctrl->getLinkTarget($this, "listSkills")
        );

        $ilTabs->activateTab("skills");
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
        $form->addCommandButton("importSkills", $lng->txt("import"));
        $form->addCommandButton("listSkills", $lng->txt("cancel"));

        $form->setTitle($lng->txt("skmg_import_skills"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    /**
     * Import skills
     */
    public function importSkills()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;

        $form = $this->initInputForm();
        if ($form->checkInput()) {
            $imp = new ilImport();
            $conf = $imp->getConfig("Services/Skill");
            $conf->setSkillTreeId($this->skill_tree_id);
            $imp->importEntity($_FILES["import_file"]["tmp_name"], $_FILES["import_file"]["name"], "skee", "Services/Skill");

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listSkills");
        } else {
            $ilTabs->activateTab("skills");
            $form->setValuesByPost();
            $tpl->setContent($form->getHtml());
        }
    }
}
