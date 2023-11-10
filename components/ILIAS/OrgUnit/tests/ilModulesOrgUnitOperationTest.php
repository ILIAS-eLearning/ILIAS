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

class ilModulesOrgUnitOperationTest extends TestCase
{
    public function testOrgUnitOperationConstruction(): void
    {
        $operation = new ilOrgUnitOperation();
        $this->assertInstanceOf(ilOrgUnitOperation::class, $operation);
        $this->assertEquals(0, $operation->getOperationId());
        $this->assertEquals('', $operation->getOperationString());
        $this->assertEquals('', $operation->getDescription());
        $this->assertEquals(0, $operation->getContextId());
        $this->assertEquals(0, $operation->getListOrder());
    }

    public function testOrgUnitOperationModification(): void
    {
        $operation = new ilOrgUnitOperation(666);
        $this->assertEquals(666, $operation->getOperationId());
        $this->assertEquals(
            ilOrgUnitOperation::OP_MANAGE_MEMBERS,
            $operation->withOperationString(ilOrgUnitOperation::OP_MANAGE_MEMBERS)->getOperationString()
        );
        $this->assertEquals('This is a test', $operation->withDescription('This is a test')->getDescription());
        $this->assertEquals(777, $operation->withContextId(777)->getContextId());
        $this->assertEquals(5, $operation->withListOrder(5)->getListOrder());
    }
}
