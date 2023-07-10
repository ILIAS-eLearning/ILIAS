<?php

declare(strict_types=1);

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

use PHPUnit\Framework\TestCase;
use ILIAS\Skill\Profile;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillInternalProfileFactoryTest extends TestCase
{
    protected Profile\SkillProfileFactory $factory;
    protected Profile\SkillProfile $profile;
    protected Profile\SkillProfileLevel $profile_level;
    protected Profile\SkillProfileCompletion $profile_completion;
    protected Profile\SkillProfileUserAssignment $profile_user_assignment;
    protected Profile\SkillProfileRoleAssignment $profile_role_assignment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new Profile\SkillProfileFactory();

        $this->profile = $this->factory->profile(0, "", "", 0);
        $this->profile_level = $this->factory->profileLevel(0, 0, 0, 0, 0);
        $this->profile_completion = $this->factory->profileCompletion(0, 0, "", false);
        $this->profile_user_assignment = $this->factory->profileUserAssignment("", 0);
        $this->profile_role_assignment = $this->factory->profileRoleAssignment("", 0, "", "", 0);
    }

    public function testFactoryInstances(): void
    {
        $this->assertInstanceOf(Profile\SkillProfile::class, $this->profile);
        $this->assertInstanceOf(Profile\SkillProfileLevel::class, $this->profile_level);
        $this->assertInstanceOf(Profile\SkillProfileCompletion::class, $this->profile_completion);
        $this->assertInstanceOf(Profile\SkillProfileUserAssignment::class, $this->profile_user_assignment);
        $this->assertInstanceOf(Profile\SkillProfileRoleAssignment::class, $this->profile_role_assignment);
    }
}
