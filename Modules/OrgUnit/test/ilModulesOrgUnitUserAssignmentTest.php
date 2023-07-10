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

use PHPUnit\Framework\TestCase;

class ilModulesOrgUnitUserAssignmentTest extends TestCase
{
    public function testOrgUnitUserAssignmentConstruction(): void
    {
        $assignment = new ilOrgUnitUserAssignment();
        $this->assertInstanceOf(ilOrgUnitUserAssignment::class, $assignment);
        $this->assertEquals(0, $assignment->getId());
        $this->assertEquals(0, $assignment->getUserId());
        $this->assertEquals(0, $assignment->getPositionId());
        $this->assertEquals(0, $assignment->getOrguId());
    }

    public function testOrgUnitUserAssignmentModification(): void
    {
        $assignment = new ilOrgUnitUserAssignment(666);
        $this->assertEquals(666, $assignment->getId());
        $this->assertEquals(888, $assignment->withUserId(888)->getUserId());
        $this->assertEquals(777, $assignment->withPositionId(777)->getPositionId());
        $this->assertEquals(999, $assignment->withOrguId(999)->getOrguId());
    }
}
