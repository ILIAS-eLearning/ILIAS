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

namespace ILIAS\Skill\Profile;

use PHPUnit\Framework\TestCase;

/**
 * @author Thomas Famula <famula@leifos.de>
 */
class SkillProfileTest extends TestCase
{
    protected SkillProfile $profile;
    protected SkillProfileLevel $profile_level;
    protected SkillProfileCompletion $profile_completion;
    protected SkillProfileUserAssignment $profile_user_assignment;
    protected SkillProfileRoleAssignment $profile_role_assignment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->profile = new SkillProfile(
            11,
            "My profile",
            "This is my profile",
            12,
            "my_profile_image_id"
        );
        $this->profile_level = new SkillProfileLevel(
            21,
            22,
            23,
            24,
            25
        );
        $this->profile_completion = new SkillProfileCompletion(
            31,
            32,
            "1999-01-01 12:12:12",
            true
        );
        $this->profile_user_assignment = new SkillProfileUserAssignment(
            "My user",
            41
        );
        $this->profile_role_assignment = new SkillProfileRoleAssignment(
            "My role",
            51,
            "My object",
            "My object type",
            52
        );
    }

    public function testProfileProperties(): void
    {
        $p = $this->profile;

        $this->assertEquals(
            11,
            $p->getId()
        );
        $this->assertEquals(
            "My profile",
            $p->getTitle()
        );
        $this->assertEquals(
            "This is my profile",
            $p->getDescription()
        );
        $this->assertEquals(
            12,
            $p->getSkillTreeId()
        );
        $this->assertEquals(
            "my_profile_image_id",
            $p->getImageId()
        );
        $this->assertEquals(
            0,
            $p->getRefId()
        );
    }

    public function testProfileLevelProperties(): void
    {
        $pl = $this->profile_level;

        $this->assertEquals(
            21,
            $pl->getProfileId()
        );
        $this->assertEquals(
            22,
            $pl->getBaseSkillId()
        );
        $this->assertEquals(
            23,
            $pl->getTrefId()
        );
        $this->assertEquals(
            24,
            $pl->getLevelId()
        );
        $this->assertEquals(
            25,
            $pl->getOrderNr()
        );
    }

    public function testProfileCompletionProperties(): void
    {
        $pc = $this->profile_completion;

        $this->assertEquals(
            31,
            $pc->getProfileId()
        );
        $this->assertEquals(
            32,
            $pc->getUserId()
        );
        $this->assertEquals(
            "1999-01-01 12:12:12",
            $pc->getDate()
        );
        $this->assertEquals(
            true,
            $pc->getFulfilled()
        );
    }

    public function testProfileUserAssignmentProperties(): void
    {
        $pu = $this->profile_user_assignment;

        $this->assertEquals(
            "user",
            $pu->getType()
        );
        $this->assertEquals(
            "My user",
            $pu->getName()
        );
        $this->assertEquals(
            41,
            $pu->getId()
        );
    }

    public function testProfileRoleAssignmentProperties(): void
    {
        $pr = $this->profile_role_assignment;

        $this->assertEquals(
            "role",
            $pr->getType()
        );
        $this->assertEquals(
            "My role",
            $pr->getName()
        );
        $this->assertEquals(
            51,
            $pr->getId()
        );
        $this->assertEquals(
            "My object",
            $pr->getObjTitle()
        );
        $this->assertEquals(
            "My object type",
            $pr->getObjType()
        );
        $this->assertEquals(
            52,
            $pr->getObjId()
        );
    }

    public function testProfileAssignmentInstanceOfInterface(): void
    {
        $pu = $this->profile_user_assignment;
        $pr = $this->profile_role_assignment;

        $this->assertInstanceOf(SkillProfileAssignmentInterface::class, $pu);
        $this->assertInstanceOf(SkillProfileAssignmentInterface::class, $pr);
    }
}
