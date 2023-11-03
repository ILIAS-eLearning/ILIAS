<?php

declare(strict_types=1);

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
use ILIAS\Skill\Profile;

/**
 * TableGUI class for skill profile levels
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillProfileLevelsTableGUI extends ilTable2GUI
{
    protected Profile\SkillProfile $profile;
    protected ilSkillTreeRepository $tree_repo;
    protected SkillInternalManagerService $skill_manager;
    protected SkillAdminGUIRequest $admin_gui_request;
    protected int $requested_ref_id = 0;

    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        Profile\SkillProfile $a_profile
    ) {
        global $DIC;

        $this->skill_manager = $DIC->skills()->internal()->manager();
        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();

        $this->profile = $a_profile;
        $this->requested_ref_id = $this->admin_gui_request->getRefId();
        parent::__construct($a_parent_obj, $a_parent_cmd);

        // convert skill levels to array structure, because tables can only handle arrays
        $levels = $this->skill_manager->getProfileManager()->getSkillLevels($this->profile->getId());
        $levels_array = [];
        foreach ($levels as $level) {
            $levels_array[] = [
                "profile_id" => $level->getProfileId(),
                "base_skill_id" => $level->getBaseSkillId(),
                "tref_id" => $level->getTrefId(),
                "level_id" => $level->getLevelId(),
                "order_nr" => $level->getOrderNr()
            ];
        }

        $this->setData($levels_array);
        $this->setTitle($this->lng->txt("skmg_target_levels"));

        $access_manager = $this->skill_manager->getTreeAccessManager($this->requested_ref_id);
        if ($access_manager->hasManageProfilesPermission()) {
            $this->addColumn("", "", "1", true);
            $this->addColumn($this->lng->txt("skmg_order"), "", "1px");
        }
        $this->addColumn($this->lng->txt("skmg_skill"));
        $this->addColumn($this->lng->txt("skmg_level"));
        if ($access_manager->hasManageProfilesPermission()) {
            $this->addColumn($this->lng->txt("actions"));
        }

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.skill_profile_level_row.html", "components/ILIAS/Skill");

        if ($access_manager->hasManageProfilesPermission()) {
            $this->addMultiCommand("confirmLevelAssignmentRemoval", $this->lng->txt("skmg_remove_levels"));
            if (count($this->skill_manager->getProfileManager()->getSkillLevels($this->profile->getId())) > 0) {
                $this->addCommandButton("saveLevelOrder", $this->lng->txt("skmg_save_order"));
            }
        }
    }

    protected function fillRow(array $a_set): void
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
                ($a_set["base_skill_id"]) . ":" . ($a_set["tref_id"]) . ":" . ($a_set["level_id"]) .
                ":" . ($a_set["order_nr"])
            );
            $this->tpl->setVariable("SKILL_ID", $a_set["base_skill_id"]);
            $this->tpl->setVariable("TREF_ID", $a_set["tref_id"]);
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock("order");
            $this->tpl->setVariable("ORDER_NR", $a_set["order_nr"]);
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock("cmd");
            $this->tpl->setVariable("CMD", $this->lng->txt("edit"));
            $this->ctrl->setParameter($this->parent_obj, "cskill_id", $a_set["base_skill_id"] . ":" . $a_set["tref_id"]);
            $this->tpl->setVariable("CMD_HREF", $this->ctrl->getLinkTarget($this->parent_obj, "updateLevelOfSelectedSkill"));
            $this->tpl->parseCurrentBlock();
        }
    }
}
