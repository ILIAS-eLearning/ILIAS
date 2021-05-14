<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

namespace ILIAS\Skill\Service;

use \ILIAS\Skill\Tree;

/**
 * Skill internal repo service
 * @author Alexander Killing <killing@leifos.de>
 */
class SkillInternalFactoryService
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @return Tree\SkillTreeFactory
     */
    public function tree() : Tree\SkillTreeFactory
    {
        return new Tree\SkillTreeFactory();
    }
}
