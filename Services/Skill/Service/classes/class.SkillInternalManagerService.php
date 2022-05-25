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

use ILIAS\Skill\Access\SkillManagementAccess;
use ILIAS\Skill\Access\SkillTreeAccess;
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
    protected int $skmg_ref_id = 0;
    protected \ilTree $repository_tree;
    protected Tree\SkillTreeFactory $skill_tree_factory;
    protected \ilRbacSystem $rbac_system;
    protected int $usr_id = 0;

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

    public function getLevelManager() : SkillLevelManager
    {
        return new SkillLevelManager();
    }

    public function getUserLevelManager() : SkillUserLevelManager
    {
        return new SkillUserLevelManager();
    }

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
     */
    public function getTreeNodeManager(int $tree_id) : Tree\SkillTreeNodeManager
    {
        return new Tree\SkillTreeNodeManager(
            $tree_id,
            $this->skill_tree_factory
        );
    }

    public function getTreeAccessManager(int $obj_ref_id) : SkillTreeAccess
    {
        return new SkillTreeAccess($this->rbac_system, $obj_ref_id, $this->usr_id);
    }

    public function getManagementAccessManager(int $skmg_ref_id) : SkillManagementAccess
    {
        return new SkillManagementAccess($this->rbac_system, $skmg_ref_id, $this->usr_id);
    }

    public function getProfileManager() : \ilSkillProfileManager
    {
        return new \ilSkillProfileManager();
    }

    public function getProfileCompletionManager() : \ilSkillProfileCompletionManager
    {
        return new \ilSkillProfileCompletionManager($this->getProfileManager());
    }
}
