<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Service;

/**
 * Skill internal manager service
 * @author famula@leifos.de
 */
class SkillInternalManagerService
{
    /**
     * Constructor
     */
    public function __construct()
    {
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
     * @return SkillTreeManager
     */
    public function getTreeManager() : SkillTreeManager
    {
        return new SkillTreeManager();
    }
}