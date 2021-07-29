<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Skill tree
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilGlobalSkillTree extends ilSkillTree
{
    /**
     * @var \ILIAS\Skill\Tree\SkillTreeManager
     */
    protected $skill_tree_manager;

    /**
     * @var \ILIAS\Skill\Tree\SkillTreeFactory
     */
    protected $skill_tree_factory;

    /**
     * @var ilBasicSkillTreeRepository
     */
    protected $tree_repo;

    public function __construct()
    {
        global $DIC;

        parent::__construct(0);
        $this->skill_tree_manager = $DIC->skills()->internal()->manager()->getTreeManager();
        $this->skill_tree_factory = $DIC->skills()->internal()->factory()->tree();
        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
    }

    public function getNodeData($a_node_id, $a_tree_pk = null)
    {
        if ($a_node_id == 0) {
            return $this->getRootNode();
        }
        return parent::getNodeData($a_node_id, $a_tree_pk);
    }

    /**
     * Get root node
     *
     * @return array root node array
     */
    public function getRootNode()
    {
        $root_node = [];

        $root_node["parent"] = 0;
        $root_node["depth"] = 0;
        $root_node["obj_id"] = 0;
        $root_node["child"] = 0;

        return $root_node;
    }

    /**
     * @inheritdoc
     */
    public function readRootId()
    {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public function getChilds($a_parent_id, $a_order = "", $a_direction = "ASC")
    {
        if (is_null($a_parent_id) || $a_parent_id == 0) {
            $childs = [];
            $trees = $this->skill_tree_manager->getTrees();
            foreach ($trees as $obj_tree) {
                $tree = $this->skill_tree_factory->getTreeById($obj_tree->getId());
                $data = $tree->getNodeData($tree->readRootId());
                $data["id"] = $data["child"];
                $childs[] = $data;
            }
            return $childs;
        }
        else {
            $tree_id = $this->tree_repo->getTreeIdForNodeId($a_parent_id);
            $this->tree = $this->skill_tree_factory->getTreeById($tree_id);
            return parent::getChilds($a_parent_id, $a_order, $a_direction);
        }
    }
}
