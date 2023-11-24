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
 * Basic Skill Template
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilBasicSkillTemplate extends ilBasicSkill
{
    public function __construct(int $a_id = 0)
    {
        parent::__construct($a_id);
        $this->setType("sktp");
    }

    /**
     * Copy basic skill template
     */
    public function copy(): ilBasicSkillTemplate
    {
        $skill = new ilBasicSkillTemplate();
        $skill->setTitle($this->getTitle());
        $skill->setDescription($this->getDescription());
        $skill->setType($this->getType());
        $skill->setOrderNr($this->getOrderNr());
        $skill->create();

        $levels = $this->getLevelData();
        if (sizeof($levels)) {
            foreach ($levels as $item) {
                $skill->addLevel($item["title"], $item["description"]);
            }
        }
        $skill->update();

        return $skill;
    }

    public function delete(): void
    {
        $ilDB = $this->db;

        foreach (\ilSkillTemplateReference::_lookupTrefIdsForTopTemplateId($this->getId()) as $tref_id) {
            $obj = ilSkillTreeNodeFactory::getInstance($tref_id);
            $skill_tree = $this->skill_service->internal()->repo()->getTreeRepo()->getTreeForNodeId($tref_id);
            $node_data = $skill_tree->getNodeData($tref_id);
            if (is_object($obj)) {
                $obj->delete();
            }
            if ($skill_tree->isInTree($tref_id)) {
                $skill_tree->deleteTree($node_data);
            }
        }

        $ilDB->manipulate(
            "DELETE FROM skl_templ_ref WHERE "
            . " templ_id = " . $ilDB->quote($this->getId(), "integer")
        );

        parent::delete();
    }
}
