<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Skill\Service;

/**
 * Skill internal repo service
 * @author famula@leifos.de
 */
class SkillInternalRepoService
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @return \ilBasicSkillLevelRepository
     */
    public function getLevelRepo() : \ilBasicSkillLevelRepository
    {
        return new \ilBasicSkillLevelDBRepository();
    }

    /**
     * @return \ilBasicSkillUserLevelRepository
     */
    public function getUserLevelRepo() : \ilBasicSkillUserLevelRepository
    {
        return new \ilBasicSkillUserLevelDBRepository();
    }

    /**
     * @return \ilBasicSkillTreeRepository
     */
    public function getTreeRepo() : \ilBasicSkillTreeRepository
    {
        return new \ilBasicSkillTreeDBRepository();
    }
}
