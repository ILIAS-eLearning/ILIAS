<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Service;

use ILIAS\Skill\Access\SkillAccess;
use ILIAS\Skill\Tree;

/**
 * Skill internal manager service
 * @author famula@leifos.de
 */
class SkillInternalManagerService
{
    /**
     * @var int ref id of skill management administration node
     */
    protected $skmg_ref_id;

    /**
     * @var \ilTree
     */
    protected $repository_tree;

    /**
     * @var Tree\SkillTreeFactory
     */
    protected $skill_tree_factory;

    /**
     * @var \ilRbacSystem
     */
    protected $rbac_system;

    /**
     * @var int
     */
    protected $usr_id;

    /**
     * Constructor
     */
    public function __construct(
        int $skmg_ref_id,
        \ilTree $repository_tree,
        Tree\SkillTreeFactory $skill_tree_factory,
        \ilRbacSystem $rbac_system,
        int $usr_id
    ) {
        $this->skmg_ref_id = $skmg_ref_id;
        $this->repository_tree = $repository_tree;
        $this->skill_tree_factory = $skill_tree_factory;
        $this->rbac_system = $rbac_system;
        $this->usr_id = $usr_id;
    }

    /**
     * @return SkillLevelManager
     */
    public function getLevelManager() : SkillLevelManager
    {
        return new SkillLevelManager();
    }

    /**
     * @return SkillUserLevelManager
     */
    public function getUserLevelManager() : SkillUserLevelManager
    {
        return new SkillUserLevelManager();
    }

    /**
     * @return Tree\SkillTreeManager
     */
    public function getTreeManager() : Tree\SkillTreeManager
    {
        return new Tree\SkillTreeManager(
            $this->skmg_ref_id,
            $this->repository_tree,
            $this->skill_tree_factory
        );
    }

    /**
     * Manages nodes in a skill tree
     * @return Tree\SkillTreeNodeManager
     */
    public function getTreeNodeManager(int $tree_id) : Tree\SkillTreeNodeManager
    {
        return new Tree\SkillTreeNodeManager(
            $tree_id,
            $this->skill_tree_factory
        );
    }

    public function getAccessManager(int $obj_skill_tree_ref_id) : SkillAccess
    {
        return new SkillAccess($this->rbac_system, $obj_skill_tree_ref_id, $this->usr_id);
    }

}