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
 * Skill root node
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillRoot extends ilSkillTreeNode
{
    public function __construct(int $a_id = 0)
    {
        parent::__construct($a_id);
        $this->setType("skrt");
    }

    public function delete(): void
    {
        $skrt_id = $this->getId();
        $skill_tree = $this->skill_service->internal()->repo()->getTreeRepo()->getTreeForNodeId($skrt_id);
        $childs = $skill_tree->getChildsByTypeFilter(
            $skrt_id,
            ["skll", "scat", "sktp", "sctp", "sktr"]
        );
        foreach ($childs as $node) {
            switch ($node["type"]) {
                case "skll":
                    $obj = new ilBasicSkill((int) $node["obj_id"]);
                    $obj->delete();
                    break;

                case "scat":
                    $obj = new ilSkillCategory((int) $node["obj_id"]);
                    $obj->delete();
                    break;

                case "sktp":
                    $obj = new ilBasicSkillTemplate((int) $node["obj_id"]);
                    $obj->delete();
                    break;

                case "sctp":
                    $obj = new ilSkillTemplateCategory((int) $node["obj_id"]);
                    $obj->delete();
                    break;

                case "sktr":
                    $obj = new ilSkillTemplateReference((int) $node["obj_id"]);
                    $obj->delete();
                    break;
            }
        }

        parent::delete();
    }
}
