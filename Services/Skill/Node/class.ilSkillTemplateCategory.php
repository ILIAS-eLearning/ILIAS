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
 * Skill Template Category
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillTemplateCategory extends ilSkillTreeNode
{
    public function __construct(int $a_id = 0)
    {
        parent::__construct($a_id);
        $this->setType("sctp");
    }

    public function copy(): ilSkillTemplateCategory
    {
        $sctp = new ilSkillTemplateCategory();
        $sctp->setTitle($this->getTitle());
        $sctp->setDescription($this->getDescription());
        $sctp->setType($this->getType());
        $sctp->setOrderNr($this->getOrderNr());
        $sctp->create();

        return $sctp;
    }

    public function delete(): void
    {
        $ilDB = $this->db;

        $sctp_id = $this->getId();
        $skill_tree = $this->skill_service->internal()->repo()->getTreeRepo()->getTreeForNodeId($sctp_id);
        $childs = $skill_tree->getChildsByTypeFilter(
            $sctp_id,
            ["sktp", "sctp"]
        );
        foreach ($childs as $node) {
            switch ($node["type"]) {
                case "sktp":
                    $obj = new ilBasicSkillTemplate((int) $node["obj_id"]);
                    $obj->delete();
                    break;

                case "sctp":
                    $obj = new ilSkillTemplateCategory((int) $node["obj_id"]);
                    $obj->delete();
                    break;
            }
        }

        foreach (\ilSkillTemplateReference::_lookupTrefIdsForTopTemplateId($sctp_id) as $tref_id) {
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
