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

class ilModulesOrgUnitPositionTest extends TestCase
{
    public function testOrgUnitPositionConstruction(): void
    {
        $pos = new ilOrgUnitPosition();
        $this->assertInstanceOf(ilOrgUnitPosition::class, $pos);
        $this->assertEquals(0, $pos->getId());
        $this->assertEquals('', $pos->getTitle());
        $this->assertEquals('', $pos->getDescription());
        $this->assertEquals(false, $pos->isCorePosition());
        $this->assertEquals(0, $pos->getCoreIdentifier());
        $this->assertEquals('', (string) $pos);
        $this->assertEquals([], $pos->getAuthorities());
    }

    public function testOrgUnitPositionModification(): void
    {
        $pos = new ilOrgUnitPosition(666);
        $this->assertEquals(666, $pos->getId());
        $this->assertEquals('Hello world', $pos->withTitle('Hello world')->getTitle());
        $this->assertEquals('Hello world', (string) $pos->withTitle('Hello world'));
        $this->assertEquals('Hello world is a greeting', $pos->withDescription('Hello world is a greeting')->getDescription());
        $this->assertEquals(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE, $pos->withCoreIdentifier(ilOrgUnitPosition::CORE_POSITION_EMPLOYEE)->getCoreIdentifier());
        $this->assertEquals(true, $pos->withCorePosition(true)->isCorePosition());
    }
}
