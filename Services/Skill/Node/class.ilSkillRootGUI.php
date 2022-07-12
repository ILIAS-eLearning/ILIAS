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

/**
 * Skill root GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_isCalledBy ilSkillRootGUI: ilObjSkillManagementGUI, ilObjSkillTreeGUI
 */
class ilSkillRootGUI extends ilSkillTreeNodeGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;

    public function __construct(Tree\SkillTreeNodeManager $node_manager, int $a_node_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        $ilCtrl->saveParameter($this, "node_id");
        
        parent::__construct($node_manager, $a_node_id);
    }

    public function getType() : string
    {
        return "skrt";
    }

    public function executeCommand() : void
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

    public function listTemplates() : void
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;
        
        $skmg_set = new ilSetting("skmg");
        $enable_skmg = $skmg_set->get("enable_skmg");
        if (!$enable_skmg) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("skmg_skill_management_deactivated"));
        }

        $this->getParentGUI()->showTree(true, $this, "listTemplates");
        $ilTabs->activateTab("skill_templates");

        if ($this->tree_access_manager->hasManageCompetenceTemplatesPermission()) {
            ilSkillTemplateCategoryGUI::addCreationButtons();
        }

        $table = new ilSkillCatTableGUI(
            $this,
            "listTemplates",
            $this->requested_node_id,
            ilSkillCatTableGUI::MODE_SCTP
        );
        
        $tpl->setContent($table->getHTML());
    }

    public function listSkills() : void
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilTabs = $this->tabs;

        $skmg_set = new ilSetting("skmg");
        $enable_skmg = $skmg_set->get("enable_skmg");
        if (!$enable_skmg) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("skmg_skill_management_deactivated"));
        }

        $this->getParentGUI()->showTree(false, $this, "listSkills");
        $ilTabs->activateTab("skills");

        if ($this->tree_access_manager->hasManageCompetencesPermission()) {
            ilSkillCategoryGUI::addCreationButtons();
        }

        $table = new ilSkillCatTableGUI(
            $this,
            "listSkills",
            $this->requested_node_id,
            ilSkillCatTableGUI::MODE_SCAT
        );
        
        $tpl->setContent($table->getHTML());
    }

    public function cancelDelete() : void
    {
        $ilCtrl = $this->ctrl;

        if ($this->requested_tmpmode) {
            $ilCtrl->redirect($this, "listTemplates");
        } else {
            $ilCtrl->redirect($this, "listSkills");
        }
    }

    public function showImportForm() : void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ctrl = $this->ctrl;

        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ctrl->getLinkTarget($this, "listSkills")
        );

        $ilTabs->activateTab("skills");
        $tpl->setContent($this->initInputForm()->getHTML());
    }

    public function initInputForm() : ilPropertyFormGUI
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

    public function importSkills() : void
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
            $imp->importEntity($_FILES["import_file"]["tmp_name"], $_FILES["import_file"]["name"], "skmg", "Services/Skill");

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listSkills");
        } else {
            $ilTabs->activateTab("skills");
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }
}
