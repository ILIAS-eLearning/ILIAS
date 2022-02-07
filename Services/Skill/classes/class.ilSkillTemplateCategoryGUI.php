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
 * Skill template category GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_isCalledBy ilSkillTemplateCategoryGUI: ilObjSkillManagementGUI, ilObjSkillTreeGUI
 */
class ilSkillTemplateCategoryGUI extends ilSkillTreeNodeGUI
{
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilLanguage $lng;
    protected ilHelpGUI $help;

    protected int $tref_id = 0;

    public function __construct(Tree\SkillTreeNodeManager $node_manager, int $a_node_id = 0, int $a_tref_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->help = $DIC["ilHelp"];
        $ilCtrl = $DIC->ctrl();
        
        $ilCtrl->saveParameter($this, "node_id");
        $this->tref_id = $a_tref_id;
        
        parent::__construct($node_manager, $a_node_id);
    }

    public function getType() : string
    {
        return "sctp";
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
                "node_id",
                $this->skill_tree_node_manager->getRootId()
            );
            $ilTabs->setBackTarget(
                $lng->txt("skmg_skill_templates"),
                $ilCtrl->getLinkTargetByClass("ilskillrootgui", "listTemplates")
            );
            $ilCtrl->setParameterByClass(
                "ilskillrootgui",
                "node_id",
                $this->requested_node_id
            );
        }
 
        parent::setTitleIcon();
        $tpl->setTitle(
            $lng->txt("skmg_sctp") . ": " . $this->node_object->getTitle()
        );
        $this->setSkillNodeDescription();
        
        $ilTabs->activateTab($a_tab);
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

        // save and cancel commands
        if ($this->tree_access_manager->hasManageCompetenceTemplatesPermission()) {
            if ($a_mode == "create") {
                $this->form->addCommandButton("save", $lng->txt("save"));
                $this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
                $this->form->setTitle($lng->txt("skmg_create_" . $this->getType()));
            } else {
                $this->form->addCommandButton("update", $lng->txt("save"));
                $this->form->setTitle($lng->txt("skmg_edit_" . $this->getType()));
            }
        } else {
            foreach ($this->form->getItems() as $item) {
                $item->setDisabled(true);
            }
        }

        $ilCtrl->setParameter($this, "node_id", $this->requested_node_id);
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    public function listItems() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        if ($this->isInUse()) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("skmg_skill_in_use"));
        }

        if ($this->tree_access_manager->hasManageCompetenceTemplatesPermission()) {
            if ($this->tref_id == 0) {
                self::addCreationButtons();
            }
        }

        $this->setTabs("content");

        $table = new ilSkillCatTableGUI(
            $this,
            "listItems",
            $this->requested_node_id,
            ilSkillCatTableGUI::MODE_SCTP,
            $this->tref_id
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
        
        $ilCtrl->setParameterByClass("ilobjskillmanagementgui", "tmpmode", 1);
        
        $ilCtrl->setParameterByClass(
            "ilbasicskilltemplategui",
            "node_id",
            $requested_node_id
        );
        $ilToolbar->addButton(
            $lng->txt("skmg_create_skill_template"),
            $ilCtrl->getLinkTargetByClass("ilbasicskilltemplategui", "create")
        );
        $ilCtrl->setParameterByClass(
            "ilskilltemplatecategorygui",
            "node_id",
            $requested_node_id
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

    public function editProperties() : void
    {
        $this->setTabs("properties");
        parent::editProperties();
    }

    public function saveItem() : void
    {
        if (!$this->tree_access_manager->hasManageCompetenceTemplatesPermission()) {
            return;
        }

        $it = new ilSkillTemplateCategory();
        $it->setTitle($this->form->getInput("title"));
        $it->setDescription($this->form->getInput("description"));
        $it->create();
        $this->skill_tree_node_manager->putIntoTree($it, $this->requested_node_id, ilTree::POS_LAST_NODE);
    }

    public function updateItem() : void
    {
        if (!$this->tree_access_manager->hasManageCompetenceTemplatesPermission()) {
            return;
        }

        $this->node_object->setTitle($this->form->getInput("title"));
        $this->node_object->setDescription($this->form->getInput("description"));
        $this->node_object->setOrderNr($this->form->getInput("order_nr"));
        $this->node_object->setSelfEvaluation((bool) $this->form->getInput("self_eval"));
        $this->node_object->update();
    }

    public function afterSave() : void
    {
        $this->redirectToParent(true);
    }

    public function showUsage() : void
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
        $usages = $usage_info->getAllUsagesOfTemplate($this->requested_node_id);

        $html = "";
        foreach ($usages as $k => $usage) {
            $tab = new ilSkillUsageTableGUI($this, "showUsage", $k, $usage);
            $html .= $tab->getHTML() . "<br/><br/>";
        }

        $tpl->setContent($html);
    }

    public function redirectToParent(bool $a_tmp_mode = false) : void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass("ilskillrootgui", "node_id", $this->requested_node_id);
        $ilCtrl->redirectByClass("ilskillrootgui", "listTemplates");
    }
}
