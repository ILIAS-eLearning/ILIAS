<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of the GPL-3.0
 * along with ILIAS, see the file LICENSE in the ILIAS root directory.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use PHPUnit\Framework\TestCase;

class ilObjPortfolioGUIPermissionTest extends TestCase
{
    public function testRequiresRepositoryCreatePermissionForOtherTypes(): void
    {
        $access = $this->createMock(ilAccessHandler::class);
        $access->expects($this->once())
            ->method('checkAccess')
            ->with('create_cat', '', 123)
            ->willReturn(false);

        $gui = (new ReflectionClass(ilObjPortfolioGUI::class))->newInstanceWithoutConstructor();
        $this->setProperty($gui, 'access_handler', $access);

        $this->assertFalse($this->checkCreatePermission($gui, 'cat', 123));
    }

    public function testAllowsPortfolioCreation(): void
    {
        $gui = (new ReflectionClass(ilObjPortfolioGUI::class))->newInstanceWithoutConstructor();

        $this->assertTrue($this->checkCreatePermission($gui, 'prtf'));
    }

    private function checkCreatePermission(ilObjPortfolioGUI $gui, string $type, ?int $node_id = null): bool
    {
        $method = new ReflectionMethod(ilObjPortfolioGUI::class, 'checkPermissionBool');
        return $method->invoke($gui, 'create', '', $type, $node_id);
    }

    private function setProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new ReflectionProperty(ilObject2GUI::class, $property);
        $reflection->setValue($object, $value);
    }
}
