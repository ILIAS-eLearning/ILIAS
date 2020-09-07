<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilBasicSkillTemplateGUI.php");
include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
/**
 * Skill template reference GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_isCalledBy ilSkillTemplateReferenceGUI: ilObjSkillManagementGUI
 * @ingroup ServicesSkill
 */
class ilSkillTemplateReferenceGUI extends ilBasicSkillTemplateGUI
{

    /**
     * Constructor
     */
    public function __construct($a_tref_id = 0)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->help = $DIC["ilHelp"];
        $ilCtrl = $DIC->ctrl();
        
        $ilCtrl->saveParameter($this, "obj_id");
        $ilCtrl->saveParameter($this, "tref_id");
        
        parent::__construct($a_tref_id);
        
        $this->tref_id = $a_tref_id;
        if (is_object($this->node_object)) {
            $this->base_skill_id = $this->node_object->getSkillTemplateId();
        }
    }

    /**
     * Get Node Type
     */
    public function getType()
    {
        return "sktr";
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
    public function setTabs($a_tab = "")
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

            // back link
            /*
                        $ilCtrl->setParameterByClass("ilskillrootgui", "obj_id",
                            $this->node_object->skill_tree->getRootId());
                        $ilTabs->setBackTarget($lng->txt("obj_skmg"),
                            $ilCtrl->getLinkTargetByClass("ilskillrootgui", "listSkills"));
                        $ilCtrl->setParameterByClass("ilskillrootgui", "obj_id",
                            $_GET["obj_id"]);*/
            
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

    /**
     * Insert
     *
     * @param
     * @return
     */
    public function insert()
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        
        $ilCtrl->saveParameter($this, "parent_id");
        $ilCtrl->saveParameter($this, "target");
        $this->initForm("create");
        $tpl->setContent($this->form->getHTML());
    }
    
    
    /**
     * Edit properties
     */
    public function editProperties()
    {
        $tpl = $this->tpl;

        $this->setTabs("properties");
        
        $this->initForm();
        $this->getValues();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Init form.
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initForm($a_mode = "edit")
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        // select skill template
        include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
        $tmplts = ilSkillTreeNode::getTopTemplates();
        
        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
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
            $max = $tree->getMaxOrderNr((int) $_GET["obj_id"]);
            $ni->setValue($max + 10);
        }
        $this->form->addItem($ni);

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

        if ($this->checkPermissionBool("write")) {
            if ($a_mode == "create") {
                $this->form->addCommandButton("save", $lng->txt("save"));
                $this->form->addCommandButton("cancel", $lng->txt("cancel"));
                $this->form->setTitle($lng->txt("skmg_new_sktr"));
            } else {
                $this->form->addCommandButton("updateSkillTemplateReference", $lng->txt("save"));
                $this->form->setTitle($lng->txt("skmg_edit_sktr"));
            }
        }
        
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Get current values for from
     */
    public function getValues()
    {
        $values = array();
        $values["skill_template_id"] = $this->node_object->getSkillTemplateId();
        $values["title"] = $this->node_object->getTitle();
        $values["selectable"] = $this->node_object->getSelfEvaluation();
        $values["status"] = $this->node_object->getStatus();
        $values["order_nr"] = $this->node_object->getOrderNr();
        $this->form->setValuesByArray($values);
    }

    /**
     * Save item
     */
    public function saveItem()
    {
        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $sktr = new ilSkillTemplateReference();
        $sktr->setTitle($_POST["title"]);
        $sktr->setSkillTemplateId($_POST["skill_template_id"]);
        $sktr->setSelfEvaluation($_POST["selectable"]);
        $sktr->setOrderNr($_POST["order_nr"]);
        $sktr->setStatus($_POST["status"]);
        $sktr->create();
        ilSkillTreeNode::putInTree($sktr, (int) $_GET["obj_id"], IL_LAST_NODE);
        $this->node_object = $sktr;
    }

    /**
     * After saving
     */
    public function afterSave()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass(
            "ilskilltemplatereferencegui",
            "tref_id",
            $this->node_object->getId()
        );
        $ilCtrl->setParameterByClass(
            "ilskilltemplatereferencegui",
            "obj_id",
            $this->node_object->getSkillTemplateId()
        );
        $ilCtrl->redirectByClass("ilskilltemplatereferencegui", "listItems");
    }

    /**
     * Update form
     */
    public function updateSkillTemplateReference()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        if (!$this->checkPermissionBool("write")) {
            return;
        }

        $this->initForm("edit");
        if ($this->form->checkInput()) {
            // perform update
            //			$this->node_object->setSkillTemplateId($_POST["skill_template_id"]);
            $this->node_object->setTitle($_POST["title"]);
            $this->node_object->setSelfEvaluation($_POST["selectable"]);
            $this->node_object->setOrderNr($_POST["order_nr"]);
            $this->node_object->setStatus($_POST["status"]);
            $this->node_object->update();

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editProperties");
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

    /**
     * Cancel
     *
     * @param
     * @return
     */
    public function cancel()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirectByClass("ilobjskillmanagementgui", "editSkills");
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

        $this->setTabs("content");
        
        $sk_id = $this->node_object->getSkillTemplateId();
        $obj_type = ilSkillTreeNode::_lookupType($sk_id);

        if ($obj_type == "sctp") {
            include_once("./Services/Skill/classes/class.ilSkillCatTableGUI.php");
            $table = new ilSkillCatTableGUI(
                $this,
                "listItems",
                (int) $sk_id,
                ilSkillCatTableGUI::MODE_SCTP,
                $this->node_object->getId()
            );
            $tpl->setContent($table->getHTML());
        } elseif ($obj_type == "sktp") {
            include_once("./Services/Skill/classes/class.ilSkillLevelTableGUI.php");
            $table = new ilSkillLevelTableGUI((int) $sk_id, $this, "edit", $this->node_object->getId());
            $tpl->setContent($table->getHTML());
        }
    }
}
