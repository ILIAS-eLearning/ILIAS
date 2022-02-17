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
 * Skill category GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_isCalledBy ilSkillCategoryGUI: ilObjSkillManagementGUI, ilObjSkillTreeGUI
 */
class ilSkillCategoryGUI extends ilSkillTreeNodeGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilLanguage $lng;
    protected ilHelpGUI $help;

    public function __construct(Tree\SkillTreeNodeManager $node_manager, int $a_node_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->help = $DIC["ilHelp"];
        $ilCtrl = $DIC->ctrl();
        
        $ilCtrl->saveParameter($this, "node_id");
        
        parent::__construct($node_manager, $a_node_id);
    }

    public function getType() : string
    {
        return "scat";
    }

    public function executeCommand() : void
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

    public function setTabs(string $a_tab) : void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilHelp = $this->help;

        $ilTabs->clearTargets();
        $ilHelp->setScreenIdComponent("skmg_scat");
        
        // content
        $ilTabs->addTab(
            "content",
            $lng->txt("content"),
            $ilCtrl->getLinkTarget($this, 'listItems')
        );

        // properties
        $ilTabs->addTab(
            "properties",
            $lng->txt("settings"),
            $ilCtrl->getLinkTarget($this, 'editProperties')
        );

        // usage
        $this->addUsageTab($ilTabs);

        // back link
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

             
        parent::setTitleIcon();
        $tpl->setTitle(
            $lng->txt("scat") . ": " . $this->node_object->getTitle()
        );
        $this->setSkillNodeDescription();
        
        $ilTabs->activateTab($a_tab);
    }

    public function editProperties() : void
    {
        $tpl = $this->tpl;
        
        $this->setTabs("properties");
        parent::editProperties();
    }

    public function edit() : void
    {
        $tpl = $this->tpl;

        $this->initForm();
        $this->getValues();
        $tpl->setContent($this->form->getHTML());
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
                $this->form->setTitle($lng->txt("skmg_create_skill_category"));
            } else {
                $this->form->addCommandButton("update", $lng->txt("save"));
                $this->form->setTitle($lng->txt("skmg_edit_scat"));
            }
        } else {
            foreach ($this->form->getItems() as $item) {
                $item->setDisabled(true);
            }
        }
        
        $ilCtrl->setParameter($this, "node_id", $this->requested_node_id);
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    public function saveItem() : void
    {
        if (!$this->tree_access_manager->hasManageCompetencesPermission()) {
            return;
        }

        $it = new ilSkillCategory();
        $it->setTitle($this->form->getInput("title"));
        $it->setDescription($this->form->getInput("description"));
        $it->setSelfEvaluation((bool) $this->form->getInput("self_eval"));
        $it->setStatus($this->form->getInput("status"));
        $it->create();
        $this->skill_tree_node_manager->putIntoTree($it, $this->requested_node_id, ilTree::POS_LAST_NODE);
    }

    /**
     * Get current values for from
     */
    public function getValues() : void
    {
        $values = [];
        $values["title"] = $this->node_object->getTitle();
        $values["description"] = $this->node_object->getDescription();
        $values["self_eval"] = $this->node_object->getSelfEvaluation();
        $values["status"] = $this->node_object->getStatus();
        $this->form->setValuesByArray($values);
    }

    public function updateItem() : void
    {
        if (!$this->tree_access_manager->hasManageCompetencesPermission()) {
            return;
        }

        $this->node_object->setTitle($this->form->getInput("title"));
        $this->node_object->setDescription($this->form->getInput("description"));
        $this->node_object->setSelfEvaluation((bool) $this->form->getInput("self_eval"));
        $this->node_object->setStatus($this->form->getInput("status"));
        $this->node_object->update();
    }

    public function listItems() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        if ($this->isInUse()) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("skmg_skill_in_use"));
        }

        if ($this->tree_access_manager->hasManageCompetencesPermission()) {
            self::addCreationButtons();
        }
        $this->setTabs("content");

        $table = new ilSkillCatTableGUI(
            $this,
            "listItems",
            $this->requested_node_id,
            ilSkillCatTableGUI::MODE_SCAT
        );
        
        $tpl->setContent($table->getHTML());
    }

    public static function addCreationButtons() : void
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilToolbar = $DIC->toolbar();
        $ilUser = $DIC->user();
        $admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();

        $requested_node_id = $admin_gui_request->getNodeId();

        // skill
        $ilCtrl->setParameterByClass(
            "ilbasicskillgui",
            "node_id",
            $requested_node_id
        );
        $ilToolbar->addButton(
            $lng->txt("skmg_create_skll"),
            $ilCtrl->getLinkTargetByClass("ilbasicskillgui", "create")
        );

        // skill category
        $ilCtrl->setParameterByClass(
            "ilskillcategorygui",
            "node_id",
            $requested_node_id
        );
        $ilToolbar->addButton(
            $lng->txt("skmg_create_skill_category"),
            $ilCtrl->getLinkTargetByClass("ilskillcategorygui", "create")
        );
        
        // skill template reference
        $ilCtrl->setParameterByClass(
            "ilskilltemplatereferencegui",
            "node_id",
            $requested_node_id
        );
        $ilToolbar->addButton(
            $lng->txt("skmg_create_skill_template_reference"),
            $ilCtrl->getLinkTargetByClass("ilskilltemplatereferencegui", "create")
        );
        
        // skills from clipboard
        $sep = false;
        if ($ilUser->clipboardHasObjectsOfType("skll")) {
            $ilToolbar->addSeparator();
            $sep = true;
            $ilToolbar->addButton(
                $lng->txt("skmg_insert_basic_skill_from_clip"),
                $ilCtrl->getLinkTargetByClass("ilskillcategorygui", "insertBasicSkillClip")
            );
        }

        // skills from clipboard
        if ($ilUser->clipboardHasObjectsOfType("scat")) {
            if (!$sep) {
                $ilToolbar->addSeparator();
                $sep = true;
            }
            $ilToolbar->addButton(
                $lng->txt("skmg_insert_skill_category_from_clip"),
                $ilCtrl->getLinkTargetByClass("ilskillcategorygui", "insertSkillCategoryClip")
            );
        }

        // skills from clipboard
        if ($ilUser->clipboardHasObjectsOfType("sktr")) {
            if (!$sep) {
                $ilToolbar->addSeparator();
                $sep = true;
            }
            $ilToolbar->addButton(
                $lng->txt("skmg_insert_skill_template_reference_from_clip"),
                $ilCtrl->getLinkTargetByClass("ilskillcategorygui", "insertTemplateReferenceClip")
            );
        }

        // skill template reference
        $ilToolbar->addButton(
            $lng->txt("skmg_import_skills"),
            $ilCtrl->getLinkTargetByClass("ilskillrootgui", "showImportForm")
        );
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
}
