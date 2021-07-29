<?php
/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilGlobalVirtualSkillTree
 */
class ilGlobalVirtualSkillTree extends ilVirtualSkillTree
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected static $order_node_data = null;
    protected $include_drafts = false;
    protected $drafts = array();
    protected $include_outdated = false;
    protected $outdated = array();
    protected $root_node_processed = false;

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

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        parent::__construct(0);
        $this->skill_tree_manager = $DIC->skills()->internal()->manager()->getTreeManager();
        $this->skill_tree_factory = $DIC->skills()->internal()->factory()->tree();
        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
    }

    /**
     * Get root node
     *
     * @return array root node array
     */
    public function getRootNode()
    {
        $root_id = 0;
        $root_node = $this->tree->getNodeData($root_id);

        $root_node["parent"] = 0;
        $root_node["depth"] = 0;
        $root_node["obj_id"] = 0;

        return $root_node;
    }

    /**
     * Get childs of node
     *
     * @param string $a_parent_id parent id
     * @return array childs
     */
    public function getChildsOfNode($a_parent_id)
    {
        if (is_null($a_parent_id)) {
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
            return parent::getChildsOfNode($a_parent_id);
        }
    }

    /**
     * Get sub tree
     *
     * @param string $a_cskill_id cskill id
     * @param bool $a_only_basic return only basic skills (and basic skill templates)
     * @return array node array
     */
    public function getSubTreeForTreeId($a_tree_id)
    {
        $result = array();
        $node = $this->getNode($a_tree_id);
        $result[] = $node;
        $this->__getSubTreeRec($a_tree_id, $result, false);

        return $result;
    }
}
