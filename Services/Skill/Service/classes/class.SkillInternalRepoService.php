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

/**
 * Skill internal repo service
 * @author famula@leifos.de
 */
class SkillInternalRepoService
{
    protected SkillInternalFactoryService $factory;

    public function __construct(SkillInternalFactoryService $factory)
    {
        $this->factory = $factory;
    }

    public function getLevelRepo() : \ilSkillLevelRepository
    {
        return new \ilSkillLevelDBRepository($this->getTreeRepo());
    }

    public function getUserLevelRepo() : \ilSkillUserLevelRepository
    {
        return new \ilSkillUserLevelDBRepository();
    }

    public function getTreeRepo() : \ilSkillTreeRepository
    {
        return new \ilSkillTreeDBRepository($this->factory->tree());
    }

    public function getProfileRepo() : \ilSkillProfileDBRepository
    {
        return new \ilSkillProfileDBRepository();
    }

    public function getProfileLevelsRepo() : \ilSkillProfileLevelsDBRepository
    {
        return new \ilSkillProfileLevelsDBRepository();
    }

    public function getProfileUserRepo() : \ilSkillProfileUserDBRepository
    {
        return new \ilSkillProfileUserDBRepository();
    }

    public function getProfileRoleRepo() : \ilSkillProfileRoleDBRepository
    {
        return new \ilSkillProfileRoleDBRepository();
    }

    public function getProfileCompletionRepo() : \ilSkillProfileCompletionRepository
    {
        return new \ilSkillProfileCompletionRepository();
    }
}
