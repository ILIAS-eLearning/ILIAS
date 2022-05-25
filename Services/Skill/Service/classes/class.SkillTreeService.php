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

namespace ILIAS\Skill\Service;

use ilSkillTreeRepository;
use ILIAS\Skill\Tree\SkillTreeFactory;
use ILIAS\Skill\Tree\SkillTreeManager;

/**
 * Skill tree service
 * @author famula@leifos.de
 */
class SkillTreeService
{
    protected ilSkillTreeRepository $tree_repo;
    protected SkillTreeFactory $tree_factory;
    protected SkillTreeManager $tree_manager;

    public function __construct(SkillInternalService $internal_service)
    {
        $this->tree_repo = $internal_service->repo()->getTreeRepo();
        $this->tree_factory = $internal_service->factory()->tree();
        $this->tree_manager = $internal_service->manager()->getTreeManager();
    }

    public function getGlobalSkillTree() : \ilGlobalSkillTree
    {
        $tree = $this->tree_factory->getGlobalTree();

        return $tree;
    }

    public function getSkillTreeById(int $tree_id) : \ilSkillTree
    {
        $tree = $this->tree_factory->getTreeById($tree_id);

        return $tree;
    }

    public function getSkillTreeForNodeId(int $node_id) : \ilSkillTree
    {
        $tree = $this->tree_repo->getTreeForNodeId($node_id);

        return $tree;
    }

    public function getGlobalVirtualSkillTree() : \ilGlobalVirtualSkillTree
    {
        $vtree = $this->tree_factory->getGlobalVirtualTree();

        return $vtree;
    }

    public function getVirtualSkillTreeById(int $tree_id) : \ilVirtualSkillTree
    {
        $vtree = $this->tree_factory->getVirtualTreeById($tree_id);

        return $vtree;
    }

    public function getVirtualSkillTreeForNodeId(int $node_id) : \ilVirtualSkillTree
    {
        $vtree = $this->tree_repo->getVirtualTreeForNodeId($node_id);

        return $vtree;
    }

    /**
     * @return array{skill_id: int, child: int, tref_id: int, parent: int}[]
     */
    public function getSkillTreePath(int $base_skill_id, int $tref_id = 0) : array
    {
        $tree = $this->tree_repo->getTreeForNodeId($base_skill_id);
        $path = $tree->getSkillTreePath($base_skill_id, $tref_id);

        return $path;
    }

    public function getObjSkillTreeById(int $tree_id) : \ilObjSkillTree
    {
        $obj_tree = $this->tree_manager->getTree($tree_id);

        return $obj_tree;
    }

    /**
     * @return \ilObjSkillTree[]
     */
    public function getObjSkillTrees() : array
    {
        $obj_trees = iterator_to_array($this->tree_manager->getTrees());

        return $obj_trees;
    }
}
