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

use ILIAS\Skill\Tree\SkillTreeManager;
use ILIAS\Skill\Tree\SkillTreeFactory;

/**
 * Global virtual skill tree
 * @author Thomas Famula <famula@leifos.de>
 */
class ilGlobalVirtualSkillTree extends ilVirtualSkillTree
{
    protected bool $root_node_processed = false;
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
     * @return array{id: int, parent: int, depth: int, obj_id: int}
     */
    public function getRootNode(): array
    {
        $root_id = 0;
        $root_node = $this->tree->getNodeData($root_id);

        $root_node["id"] = 0;
        $root_node["parent"] = 0;
        $root_node["depth"] = 0;
        $root_node["obj_id"] = 0;

        return $root_node;
    }

    /**
     * @return array{id: int, child: int, parent: int}[]
     */
    public function getChildsOfNode(string $a_parent_id): array
    {
        if ($a_parent_id === "0") {
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
            $parent_id_parts = explode(":", $a_parent_id);
            $parent_skl_tree_id = (int) $parent_id_parts[0];
            $tree_id = $this->tree_repo->getTreeIdForNodeId($parent_skl_tree_id);
            $this->tree = $this->skill_tree_factory->getTreeById($tree_id);
            return parent::getChildsOfNode($a_parent_id);
        }
    }

    /**
     * @return {cskill_id: string, id: string, skill_id: string, tref_id: string, parent: string, type: string}[]
     */
    public function getSubTreeForTreeId(string $a_tree_id): array
    {
        return array_merge(
            [$this->getNode($a_tree_id)],
            $this->__getSubTreeRec($a_tree_id, false)
        );
    }
}
