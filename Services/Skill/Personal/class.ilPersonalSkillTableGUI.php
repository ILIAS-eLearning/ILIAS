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

/**
 * TableGUI class for personal skills
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilPersonalSkillTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilObjUser $user;

    public function __construct($a_parent_obj, string $a_parent_cmd)
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

    protected function fillRow(array $a_set) : void
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

        $this->tpl->setVariable("SKL_NODE_ID", $a_set["skill_node_id"]);
        $this->tpl->setVariable("TITLE", $a_set["title"]);
    }
}
