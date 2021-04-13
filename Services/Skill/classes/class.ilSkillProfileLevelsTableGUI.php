<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_profile, $a_write_permission = false)
    {
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
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $path = $this->tree->getSkillTreePath(
            $a_set["base_skill_id"],
            $a_set["tref_id"]
        );
        $path_items = array();
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
