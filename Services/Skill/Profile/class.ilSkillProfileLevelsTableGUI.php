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
use ILIAS\Skill\Service\SkillAdminGUIRequest;
use ILIAS\Skill\Service\SkillInternalManagerService;

/**
 * TableGUI class for skill profile levels
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillProfileLevelsTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilSkillProfile $profile;
    protected ilSkillTreeRepository $tree_repo;
    protected SkillInternalManagerService $skill_manager;
    protected SkillAdminGUIRequest $admin_gui_request;
    protected int $requested_ref_id = 0;

    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        ilSkillProfile $a_profile
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->skill_manager = $DIC->skills()->internal()->manager();
        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();
        
        $this->profile = $a_profile;
        $this->requested_ref_id = $this->admin_gui_request->getRefId();
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setData($this->profile->getSkillLevels());
        $this->setTitle($lng->txt("skmg_target_levels"));

        $access_manager = $this->skill_manager->getTreeAccessManager($this->requested_ref_id);
        if ($access_manager->hasManageProfilesPermission()) {
            $this->addColumn("", "", "1", true);
            $this->addColumn($this->lng->txt("skmg_order"), "", "1px");
        }
        $this->addColumn($this->lng->txt("skmg_skill"));
        $this->addColumn($this->lng->txt("skmg_level"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.skill_profile_level_row.html", "Services/Skill");

        if ($access_manager->hasManageProfilesPermission()) {
            $this->addMultiCommand("confirmLevelAssignmentRemoval", $lng->txt("skmg_remove_levels"));
            if (count($this->profile->getSkillLevels()) > 0) {
                $this->addCommandButton("saveLevelOrder", $lng->txt("skmg_save_order"));
            }
        }
    }

    protected function fillRow(array $a_set) : void
    {
        $tree_id = $this->tree_repo->getTreeIdForNodeId($a_set["base_skill_id"]);
        $node_manager = $this->skill_manager->getTreeNodeManager($tree_id);
        $this->tpl->setVariable(
            "SKILL_TITLE",
            $node_manager->getWrittenPath(
                $a_set["base_skill_id"],
                $a_set["tref_id"]
            )
        );
        
        $this->tpl->setVariable("LEVEL_TITLE", ilBasicSkill::lookupLevelTitle($a_set["level_id"]));

        $access_manager = $this->skill_manager->getTreeAccessManager($this->requested_ref_id);
        if ($access_manager->hasManageProfilesPermission()) {
            $this->tpl->setCurrentBlock("checkbox");
            $this->tpl->setVariable(
                "ID",
                ((int) $a_set["base_skill_id"]) . ":" . ((int) $a_set["tref_id"]) . ":" . ((int) $a_set["level_id"]) .
                ":" . ((int) $a_set["order_nr"])
            );
            $this->tpl->setVariable("SKILL_ID", (int) $a_set["base_skill_id"]);
            $this->tpl->setVariable("TREF_ID", (int) $a_set["tref_id"]);
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock("order");
            $this->tpl->setVariable("ORDER_NR", (int) $a_set["order_nr"]);
            $this->tpl->parseCurrentBlock();
        }
    }
}
