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

use ILIAS\Skill\Tree\SkillTreeManager;
use ILIAS\Skill\Tree\SkillTreeFactory;

/**
 * Global skill tree
 * @author Thomas Famula <famula@leifos.de>
 */
class ilGlobalSkillTree extends ilSkillTree
{
    protected SkillTreeManager $skill_tree_manager;
    protected SkillTreeFactory $skill_tree_factory;
    protected ilSkillTreeRepository $tree_repo;

    public function __construct()
    {
        global $DIC;

        parent::__construct(0);
        $this->skill_tree_manager = $DIC->skills()->internal()->manager()->getTreeManager();
        $this->skill_tree_factory = $DIC->skills()->internal()->factory()->tree();
        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
    }

    /**
     * @return array{child: int, parent: int}
     */
    public function getNodeData(int $a_node_id, ?int $a_tree_pk = null) : array
    {
        if ($a_node_id == 0) {
            return $this->getRootNode();
        }
        return parent::getNodeData($a_node_id, $a_tree_pk);
    }

    /**
     * @return array{parent: int, depth: int, obj_id: int, child: int}
     */
    public function getRootNode() : array
    {
        $root_node = [];

        $root_node["parent"] = 0;
        $root_node["depth"] = 0;
        $root_node["obj_id"] = 0;
        $root_node["child"] = 0;

        return $root_node;
    }

    public function readRootId() : int
    {
        return 0;
    }

    /**
     * @return array{child: int, parent: int}[]
     */
    public function getChilds(int $a_node_id, string $a_order = "", string $a_direction = "ASC") : array
    {
        if ($a_node_id == 0) {
            $childs = [];
            $trees = $this->skill_tree_manager->getTrees();
            foreach ($trees as $obj_tree) {
                $tree = $this->skill_tree_factory->getTreeById($obj_tree->getId());
                $data = $tree->getNodeData($tree->readRootId());
                $data["id"] = $data["child"];
                $childs[] = $data;
            }
            return $childs;
        } else {
            return parent::getChilds($a_node_id, $a_order, $a_direction);
        }
    }
}
