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
 * TableGUI class for skill profile levels
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillProfileLevelsTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    protected ilAccessHandler $access;
    protected ilSkillTree $tree;
    protected ilSkillProfile $profile;

    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        ilSkillProfile $a_profile,
        bool $a_write_permission = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->tree = new ilSkillTree();
        
        $this->profile = $a_profile;
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setData($this->profile->getSkillLevels());
        $this->setTitle($lng->txt("skmg_target_levels"));
        
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("skmg_order"), "", "1px");
        $this->addColumn($this->lng->txt("skmg_skill"));
        $this->addColumn($this->lng->txt("skmg_level"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.skill_profile_level_row.html", "Services/Skill");

        if ($a_write_permission) {
            $this->addMultiCommand("confirmLevelAssignmentRemoval", $lng->txt("skmg_remove_levels"));
        }
        if (count($this->profile->getSkillLevels()) > 0) {
            $this->addCommandButton("saveLevelOrder", $lng->txt("skmg_save_order"));
        }
    }

    protected function fillRow($a_set) : void
    {
        $path = $this->tree->getSkillTreePath(
            $a_set["base_skill_id"],
            $a_set["tref_id"]
        );
        $path_items = [];
        foreach ($path as $p) {
            if ($p["type"] != "skrt") {
                $path_items[] = $p["title"];
            }
        }
        $this->tpl->setVariable(
            "SKILL_TITLE",
            implode(" > ", $path_items)
        );
        
        $this->tpl->setVariable("LEVEL_TITLE", ilBasicSkill::lookupLevelTitle($a_set["level_id"]));
        
        $this->tpl->setVariable(
            "ID",
            ((int) $a_set["base_skill_id"]) . ":" . ((int) $a_set["tref_id"]) . ":" . ((int) $a_set["level_id"]) .
            ":" . ((int) $a_set["order_nr"])
        );

        $this->tpl->setVariable("SKILL_ID", (int) $a_set["base_skill_id"]);
        $this->tpl->setVariable("TREF_ID", (int) $a_set["tref_id"]);
        $this->tpl->setVariable("ORDER_NR", (int) $a_set["order_nr"]);
    }
}
