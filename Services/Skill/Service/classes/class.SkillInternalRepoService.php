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

use ILIAS\Skill\Profile;

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

    public function getProfileRepo() : Profile\SkillProfileDBRepository
    {
        return new Profile\SkillProfileDBRepository();
    }

    public function getProfileLevelsRepo() : Profile\SkillProfileLevelsDBRepository
    {
        return new Profile\SkillProfileLevelsDBRepository();
    }

    public function getProfileUserRepo() : Profile\SkillProfileUserDBRepository
    {
        return new Profile\SkillProfileUserDBRepository();
    }

    public function getProfileRoleRepo() : Profile\SkillProfileRoleDBRepository
    {
        return new Profile\SkillProfileRoleDBRepository();
    }

    public function getProfileCompletionRepo() : Profile\SkillProfileCompletionDBRepository
    {
        return new Profile\SkillProfileCompletionDBRepository();
    }
}
