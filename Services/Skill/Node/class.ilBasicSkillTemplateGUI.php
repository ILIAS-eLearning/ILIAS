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
 * Basic skill template GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_isCalledBy ilBasicSkillTemplateGUI: ilObjSkillManagementGUI, ilObjSkillTreeGUI
 */
class ilBasicSkillTemplateGUI extends ilBasicSkillGUI
{
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilTabsGUI $tabs;
    protected ilGlobalTemplateInterface $tpl;
    protected ilHelpGUI $help;
    protected ilToolbarGUI $toolbar;

    public function __construct(Tree\SkillTreeNodeManager $node_manager, int $a_node_id = 0, int $a_tref_id = 0)
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
        
        $ilCtrl->saveParameter($this, array("node_id", "level_id"));
        
        parent::__construct($node_manager, $a_node_id);
    }

    public function getType() : string
    {
        return "sktp";
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

    public function setTabs(string $a_tab = "levels") : void
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

    public function saveItem() : void
    {
        if (!$this->tree_access_manager->hasManageCompetenceTemplatesPermission()) {
            return;
        }

        $it = new ilBasicSkillTemplate();
        $it->setTitle($this->form->getInput("title"));
        $it->setDescription($this->form->getInput("description"));
        $it->create();
        $this->skill_tree_node_manager->putIntoTree($it, $this->requested_node_id, ilTree::POS_LAST_NODE);
        $this->node_object = $it;
    }

    public function afterSave() : void
    {
        $ilCtrl = $this->ctrl;

        if (!$this->tree_access_manager->hasManageCompetenceTemplatesPermission()) {
            return;
        }
        
        $ilCtrl->setParameterByClass(
            "ilbasicskilltemplategui",
            "node_id",
            $this->node_object->getId()
        );
        $ilCtrl->redirectByClass("ilbasicskilltemplategui", "edit");
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
        } elseif ($this->tree_access_manager->hasManageCompetenceTemplatesPermission()) {
            if ($this->tref_id == 0) {
                $ilToolbar->addButton(
                    $lng->txt("skmg_add_level"),
                    $ilCtrl->getLinkTarget($this, "addLevel")
                );
            }
        }

        $table = new ilSkillLevelTableGUI(
            $this->requested_node_id,
            $this,
            "edit",
            $this->tref_id,
            $this->isInUse(),
            $this->tree_access_manager->hasManageCompetenceTemplatesPermission()
        );
        $tpl->setContent($table->getHTML());
    }

    public function showUsage() : void
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

    public function showObjects() : void
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

    public function redirectToParent(bool $a_tmp_mode = false) : void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass("ilskillrootgui", "node_id", $this->requested_node_id);
        $ilCtrl->redirectByClass("ilskillrootgui", "listTemplates");
    }
}
