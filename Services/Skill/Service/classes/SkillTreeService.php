<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Service;

use ilBasicSkillTreeRepository;
use ILIAS\Skill\Tree\SkillTreeFactory;
use ILIAS\Skill\Tree\SkillTreeManager;

/**
 * Skill tree service
 * @author famula@leifos.de
 */
class SkillTreeService
{
    /**
     * @var ilBasicSkillTreeRepository
     */
    protected $tree_repo;

    /**
     * @var SkillTreeFactory
     */
    protected $tree_factory;

    /**
     * @var SkillTreeManager
     */
    protected $tree_manager;

    /**
     * Constructor
     */
    public function __construct(SkillInternalService $internal_service)
    {
        global $DIC;

        $this->tree_repo = $internal_service->repo()->getTreeRepo();
        $this->tree_factory = $internal_service->factory()->tree();
        $this->tree_manager = $internal_service->manager()->getTreeManager();
    }

    /**
     * @return \ilGlobalSkillTree
     */
    public function getGlobalSkillTree() : \ilGlobalSkillTree
    {
        $tree = $this->tree_factory->getGlobalTree();

        return $tree;
    }

    /**
     * @param int $tree_id
     * @return \ilSkillTree
     */
    public function getSkillTreeById(int $tree_id) : \ilSkillTree
    {
        $tree = $this->tree_factory->getTreeById($tree_id);

        return $tree;
    }

    /**
     * @param int $node_id
     * @return \ilSkillTree
     */
    public function getSkillTreeForNodeId(int $node_id) : \ilSkillTree
    {
        $tree = $this->tree_repo->getTreeForNodeId($node_id);

        return $tree;
    }

    /**
     * @return \ilGlobalVirtualSkillTree
     */
    public function getGlobalVirtualSkillTree() : \ilGlobalVirtualSkillTree
    {
        $vtree = $this->tree_factory->getGlobalVirtualTree();

        return $vtree;
    }

    /**
     * @param int $tree_id
     * @return \ilVirtualSkillTree
     */
    public function getVirtualSkillTreeById(int $tree_id) : \ilVirtualSkillTree
    {
        $vtree = $this->tree_factory->getVirtualTreeById($tree_id);

        return $vtree;
    }

    /**
     * @param int $node_id
     * @return \ilVirtualSkillTree
     */
    public function getVirtualSkillTreeForNodeId(int $node_id) : \ilVirtualSkillTree
    {
        $vtree = $this->tree_repo->getVirtualTreeForNodeId($node_id);

        return $vtree;
    }

    /**
     * @param int $base_skill_id base skill id
     * @param int $tref_id template reference id
     * @return array path
     */
    public function getSkillTreePath(int $base_skill_id, int $tref_id = 0) : array
    {
        $tree = $this->tree_repo->getTreeForNodeId($base_skill_id);
        $path = $tree->getSkillTreePath($base_skill_id, $tref_id);

        return $path;
    }

    /**
     * @param int $tree_id
     * @return \ilObjSkillTree
     */
    public function getObjSkillTreeById(int $tree_id) : \ilObjSkillTree
    {
        $obj_tree = $this->tree_manager->getTree($tree_id);

        return $obj_tree;
    }

    public function getObjSkillTrees() : array
    {
        $obj_trees = iterator_to_array($this->tree_manager->getTrees());

        return $obj_trees;
    }

}