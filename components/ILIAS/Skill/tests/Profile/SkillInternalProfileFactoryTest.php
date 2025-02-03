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
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Skill\Profile;

use PHPUnit\Framework\TestCase;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillInternalProfileFactoryTest extends TestCase
{
    protected SkillProfileFactory $factory;
    protected SkillProfile $profile;
    protected SkillProfileLevel $profile_level;
    protected SkillProfileCompletion $profile_completion;
    protected SkillProfileUserAssignment $profile_user_assignment;
    protected SkillProfileRoleAssignment $profile_role_assignment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new SkillProfileFactory();

        $this->profile = $this->factory->profile(0, "", "", 0);
        $this->profile_level = $this->factory->profileLevel(0, 0, 0, 0, 0);
        $this->profile_completion = $this->factory->profileCompletion(0, 0, "", false);
        $this->profile_user_assignment = $this->factory->profileUserAssignment("", 0);
        $this->profile_role_assignment = $this->factory->profileRoleAssignment("", 0, "", "", 0);
    }

    public function testFactoryInstances(): void
    {
        $this->assertInstanceOf(SkillProfile::class, $this->profile);
        $this->assertInstanceOf(SkillProfileLevel::class, $this->profile_level);
        $this->assertInstanceOf(SkillProfileCompletion::class, $this->profile_completion);
        $this->assertInstanceOf(SkillProfileUserAssignment::class, $this->profile_user_assignment);
        $this->assertInstanceOf(SkillProfileRoleAssignment::class, $this->profile_role_assignment);
    }
}
