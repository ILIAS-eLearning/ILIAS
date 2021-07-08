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
     * Get tree by skill id
     * @param int $id
     * @return \ilSkillTree
     */
    public function getById(int $id) : \ilSkillTree
    {
        return new \ilSkillTree($id);
    }

}