<?php

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Tree;

/**
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class SkillTreeFactory
{
    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * Get global tree
     * @return \ilGlobalSkillTree
     */
    public function getGlobalTree() : \ilGlobalSkillTree
    {
        return new \ilGlobalSkillTree();
    }

    /**
     * Get tree by tree id
     * @param int $id
     * @return \ilSkillTree
     */
    public function getTreeById(int $id) : \ilSkillTree
    {
        return new \ilSkillTree($id);
    }

    /**
     * Get global tree
     * @return \ilGlobalVirtualSkillTree
     */
    public function getGlobalVirtualTree() : \ilGlobalVirtualSkillTree
    {
        return new \ilGlobalVirtualSkillTree();
    }

    /**
     * Get virtual tree by tree id
     * @param int $id
     * @return \ilVirtualSkillTree
     */
    public function getVirtualTreeById(int $id) : \ilVirtualSkillTree
    {
        return new \ilVirtualSkillTree($id);
    }

}