<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for personal skills
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilPersonalSkillTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        $ilUser = $DIC->user();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        include_once("./Services/Skill/classes/class.ilPersonalSkill.php");
        $this->setData(ilPersonalSkill::getSelectedUserSkills($ilUser->getId()));
        $this->setTitle($lng->txt("skills"));
        
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("skmg_materials"));
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.personal_skill_row.html", "Services/Skill");

        $this->addMultiCommand("confirmSkillRemove", $lng->txt("skmg_remove_skills"));
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        // assign materials
        $ilCtrl->setParameterByClass("ilpersonalskillsgui", "skill_id", $a_set["skill_node_id"]);
        $this->tpl->setCurrentBlock("cmd");
        $this->tpl->setVariable("TXT_CMD", $lng->txt("skmg_assign_materials"));
        $this->tpl->setVariable(
            "HREF_CMD",
            $ilCtrl->getLinkTargetByClass("ilpersonalskillsgui", "assignMaterials")
        );
        $this->tpl->parseCurrentBlock();
        $ilCtrl->setParameterByClass("ilpersonalskillsgui", "skill_id", "");
        
        //var_dump($a_set);
        $this->tpl->setVariable("SKL_NODE_ID", $a_set["skill_node_id"]);
        $this->tpl->setVariable("TITLE", $a_set["title"]);
    }
}
