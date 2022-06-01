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
 * Skill template reference GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_isCalledBy ilSkillTemplateReferenceGUI: ilObjSkillManagementGUI, ilObjSkillTreeGUI
 */
class ilSkillTemplateReferenceGUI extends ilBasicSkillTemplateGUI
{
    public function __construct(Tree\SkillTreeNodeManager $node_manager, int $a_tref_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->help = $DIC["ilHelp"];
        $ilCtrl = $DIC->ctrl();
        
        $ilCtrl->saveParameter($this, "node_id");
        $ilCtrl->saveParameter($this, "tref_id");
        
        parent::__construct($node_manager, $a_tref_id);
        
        $this->tref_id = $a_tref_id;
        if (is_object($this->node_object)) {
            $this->base_skill_id = $this->node_object->getSkillTemplateId();
        }
    }

    public function getType() : string
    {
        return "sktr";
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

    public function setTabs($a_tab = "levels") : void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilHelp = $this->help;

        $ilTabs->clearTargets();
        $ilHelp->setScreenIdComponent("skmg_sktr");

        if (is_object($this->node_object)) {
            $sk_id = $this->node_object->getSkillTemplateId();
            $obj_type = ilSkillTreeNode::_lookupType($sk_id);

            if ($obj_type == "sctp") {
                // content
                $ilTabs->addTab(
                    "content",
                    $lng->txt("content"),
                    $ilCtrl->getLinkTarget($this, 'listItems')
                );
            } else {
                // content
                $ilTabs->addTab(
                    "content",
                    $lng->txt("skmg_skill_levels"),
                    $ilCtrl->getLinkTarget($this, 'listItems')
                );
            }
    
            // properties
            $ilTabs->addTab(
                "properties",
                $lng->txt("settings"),
                $ilCtrl->getLinkTarget($this, 'editProperties')
            );

            // usage
            $this->addUsageTab($ilTabs);

            // assigned objects
            if ($obj_type != "sctp") {
                $this->addObjectsTab($ilTabs);
            }

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

            $tid = ilSkillTemplateReference::_lookupTemplateId($this->node_object->getId());
            $add = " (" . ilSkillTreeNode::_lookupTitle($tid) . ")";
    
            parent::setTitleIcon();
            $tpl->setTitle(
                $lng->txt("skmg_sktr") . ": " . $this->node_object->getTitle() . $add
            );
            $this->setSkillNodeDescription();
            
            $ilTabs->activateTab($a_tab);
        }
    }

    public function insert() : void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        
        $ilCtrl->saveParameter($this, "parent_id");
        $ilCtrl->saveParameter($this, "target");
        $this->initForm("create");
        $tpl->setContent($this->form->getHTML());
    }

    public function editProperties() : void
    {
        $tpl = $this->tpl;

        $this->setTabs("properties");
        
        $this->initForm();
        $this->getValues();
        $tpl->setContent($this->form->getHTML());
    }

    public function initForm(string $a_mode = "edit") : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        //TODO: Refactoring to UI Form when non-editable input is available

        $this->form = new ilPropertyFormGUI();

        // select skill template
        $tmplts = $this->skill_tree_node_manager->getTopTemplates();
        
        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // description
        if ($a_mode == "edit") {
            $desc = ilSkillTreeNode::_lookupDescription($this->node_object->getSkillTemplateId());
            if (!empty($desc)) {
                $ne = new ilNonEditableValueGUI($lng->txt("description"), "template_description");
                $ne->setValue($desc);
                $ne->setInfo($lng->txt("skmg_description_info"));
                $this->form->addItem($ne);
            }
        }

        // template
        $options = array(
            "" => $lng->txt("please_select"),
            );
        foreach ($tmplts as $tmplt) {
            $options[$tmplt["child"]] = $tmplt["title"];
        }
        if ($a_mode != "edit") {
            $si = new ilSelectInputGUI($lng->txt("skmg_skill_template"), "skill_template_id");
            $si->setOptions($options);
            $si->setRequired(true);
            $this->form->addItem($si);
        } else {
            $ne = new ilNonEditableValueGUI($lng->txt("skmg_skill_template"), "");
            $ne->setValue($options[$this->node_object->getSkillTemplateId()]);
            $this->form->addItem($ne);
        }

        // status
        $this->addStatusInput($this->form);

        // selectable
        $cb = new ilCheckboxInputGUI($lng->txt("skmg_selectable"), "selectable");
        $cb->setInfo($lng->txt("skmg_selectable_info"));
        $this->form->addItem($cb);

        if ($this->tree_access_manager->hasManageCompetencesPermission()) {
            if ($a_mode == "create") {
                $this->form->addCommandButton("save", $lng->txt("save"));
                $this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
                $this->form->setTitle($lng->txt("skmg_new_sktr"));
            } else {
                $this->form->addCommandButton("updateSkillTemplateReference", $lng->txt("save"));
                $this->form->setTitle($lng->txt("skmg_edit_sktr"));
            }
        } else {
            foreach ($this->form->getItems() as $item) {
                $item->setDisabled(true);
            }
        }
        
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    public function getValues() : void
    {
        $values = [];
        $values["skill_template_id"] = $this->node_object->getSkillTemplateId();
        $values["title"] = $this->node_object->getTitle();
        $values["description"] = $this->node_object->getDescription();
        $values["selectable"] = $this->node_object->getSelfEvaluation();
        $values["status"] = $this->node_object->getStatus();
        $this->form->setValuesByArray($values);
    }

    public function saveItem() : void
    {
        if (!$this->tree_access_manager->hasManageCompetencesPermission()) {
            return;
        }

        $sktr = new ilSkillTemplateReference();
        $sktr->setTitle($this->form->getInput("title"));
        $sktr->setDescription($this->form->getInput("description"));
        $sktr->setSkillTemplateId($this->form->getInput("skill_template_id"));
        $sktr->setSelfEvaluation((bool) $this->form->getInput("selectable"));
        $sktr->setStatus($this->form->getInput("status"));
        $sktr->create();
        $this->skill_tree_node_manager->putIntoTree($sktr, $this->requested_node_id, ilTree::POS_LAST_NODE);
        $this->node_object = $sktr;
    }

    public function afterSave() : void
    {
        $ilCtrl = $this->ctrl;

        if (!$this->tree_access_manager->hasManageCompetencesPermission()) {
            return;
        }

        $ilCtrl->setParameterByClass(
            "ilskilltemplatereferencegui",
            "tref_id",
            $this->node_object->getId()
        );
        $ilCtrl->setParameterByClass(
            "ilskilltemplatereferencegui",
            "node_id",
            $this->node_object->getSkillTemplateId()
        );
        $ilCtrl->redirectByClass("ilskilltemplatereferencegui", "listItems");
    }

    public function updateSkillTemplateReference() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        if (!$this->tree_access_manager->hasManageCompetencesPermission()) {
            return;
        }

        $this->initForm("edit");
        if ($this->form->checkInput()) {
            // perform update
            $this->node_object->setTitle($this->form->getInput("title"));
            $this->node_object->setDescription($this->form->getInput("description"));
            $this->node_object->setSelfEvaluation((bool) $this->form->getInput("selectable"));
            $this->node_object->setStatus($this->form->getInput("status"));
            $this->node_object->update();

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editProperties");
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHTML());
    }

    public function listItems() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        if ($this->isInUse()) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("skmg_skill_in_use"));
        }

        $this->setTabs("content");
        
        $sk_id = $this->node_object->getSkillTemplateId();
        $obj_type = ilSkillTreeNode::_lookupType($sk_id);

        if ($obj_type == "sctp") {
            $table = new ilSkillCatTableGUI(
                $this,
                "listItems",
                (int) $sk_id,
                ilSkillCatTableGUI::MODE_SCTP,
                $this->node_object->getId()
            );
            $tpl->setContent($table->getHTML());
        } elseif ($obj_type == "sktp") {
            $table = new ilSkillLevelTableGUI(
                (int) $sk_id,
                $this,
                "edit",
                $this->node_object->getId(),
                false,
                $this->tree_access_manager->hasManageCompetencesPermission()
            );
            $tpl->setContent($table->getHTML());
        }
    }

    public function showObjects() : void
    {
        $tpl = $this->tpl;

        $this->setTabs("objects");

        $usage_info = new ilSkillUsage();
        $objects = $usage_info->getAssignedObjectsForSkill($this->base_skill_id, $this->tref_id);

        $tab = new ilSkillAssignedObjectsTableGUI($this, "showObjects", $objects);

        $tpl->setContent($tab->getHTML());
    }

    public function redirectToParent(bool $a_tmp_mode = false) : void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass("ilskillrootgui", "node_id", $this->requested_node_id);
        $ilCtrl->redirectByClass("ilskillrootgui", "listSkills");
    }
}
