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

use ILIAS\Skill\Service\SkillAdminGUIRequest;

/**
 * TableGUI class for skill profile skill level assignment
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillLevelProfileAssignmentTableGUI extends ilTable2GUI
{
    protected int $skill_id = 0;
    protected int $tref_id = 0;
    protected ilBasicSkill $skill;
    protected SkillAdminGUIRequest $admin_gui_request;
    protected int $requested_level_id = 0;

    public function __construct($a_parent_obj, string $a_parent_cmd, string $a_cskill_id)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $parts = explode(":", $a_cskill_id);
        $this->skill_id = (int) $parts[0];
        $this->tref_id = (int) $parts[1];

        $this->requested_level_id = $this->admin_gui_request->getLevelId();

        $this->skill = new ilBasicSkill($this->skill_id);
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->skill->getLevelData());
        $this->setTitle(
            $this->skill->getTitle() . ", " .
                $lng->txt("skmg_skill_levels")
        );
        
        $this->addColumn($this->lng->txt("title"));
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.skill_level_profile_assignment_row.html", "Services/Skill");
    }

    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setCurrentBlock("cmd");
        $this->tpl->setVariable("CMD", $lng->txt("skmg_assign_level"));
        $ilCtrl->setParameter($this->parent_obj, "level_id", (int) $a_set["id"]);
        $this->tpl->setVariable("CMD_HREF", $ilCtrl->getLinkTarget(
            $this->parent_obj,
            "assignLevelToProfile"
        ));
        $ilCtrl->setParameter($this->parent_obj, "level_id", $this->requested_level_id);
        $this->tpl->parseCurrentBlock();
        
        $this->tpl->setVariable("TITLE", $a_set["title"]);
    }
}
