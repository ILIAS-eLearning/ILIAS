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
use ILIAS\DI\Container;

class ilModulesOrgUnitAuthorityTest extends TestCase
{
    public function testOrgUnitAuthorityConstruction(): void
    {
        $auth = new ilOrgUnitAuthority();
        $this->assertInstanceOf(ilOrgUnitAuthority::class, $auth);
        $this->assertEquals(0, $auth->getId());
        $this->assertEquals(ilOrgUnitAuthority::OVER_EVERYONE, $auth->getOver());
        $this->assertEquals(ilOrgUnitAuthority::SCOPE_SAME_ORGU, $auth->getScope());
        $this->assertEquals(0, $auth->getPositionId());
        $this->assertEquals('0', (string) $auth);
    }

    public function testOrgUnitAuthorityModification(): void
    {
        $auth = new ilOrgUnitAuthority(666);
        $this->assertEquals(666, $auth->getId());
        $this->assertEquals('666', (string) $auth);
        $this->assertEquals(555, $auth->withOver(555)->getOver());
        $this->assertEquals(
            ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS,
            $auth->withScope(ilOrgUnitAuthority::SCOPE_SUBSEQUENT_ORGUS)->getScope()
        );
        $this->assertEquals(333, $auth->withPositionId(333)->getPositionId());
    }

    public function testOrgUnitAuthorityInvalidScope(): void
    {
        $this->expectException(\ilException::class);
        $auth = (new ilOrgUnitAuthority())
            ->withScope(444);
    }
}
