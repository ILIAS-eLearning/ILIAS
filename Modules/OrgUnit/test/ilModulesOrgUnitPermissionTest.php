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

class ilModulesOrgUnitPermissionTest extends TestCase
{
    public function testOrgUnitPermissionConstruction(): void
    {
        $permission = new ilOrgUnitPermission();
        $this->assertInstanceOf(ilOrgUnitPermission::class, $permission);
        $this->assertEquals(0, $permission->getId());
        $this->assertEquals(ilOrgUnitPermission::PARENT_TEMPLATE, $permission->getParentId());
        $this->assertEquals(0, $permission->getContextId());
        $this->assertEquals(0, $permission->getPositionId());
        $this->assertEquals([], $permission->getOperations());
        $this->assertEquals([], $permission->getPossibleOperations());
        $this->assertEquals([], $permission->getSelectedOperationIds());
        $this->assertEquals(null, $permission->getContext());
        $this->assertEquals(false, $permission->isProtected());
        $this->assertEquals(true, $permission->isTemplate());
        $this->assertEquals(false, $permission->isOperationIdSelected(1));
    }

    public function testOrgUnitPermissionModification(): void
    {
        $mock_operation = $this->createMock(ilOrgUnitOperation::class);
        $mock_context = $this->createMock(ilOrgUnitOperationContext::class);

        $permission = new ilOrgUnitPermission(666);
        $this->assertEquals(666, $permission->getId());
        $this->assertEquals(777, $permission->withParentId(777)->getParentId());
        $this->assertEquals(888, $permission->withContextId(888)->getContextId());
        $this->assertEquals(999, $permission->withPositionId(999)->getPositionId());
        $this->assertEquals([$mock_operation], $permission->withOperations([$mock_operation])->getOperations());
        $this->assertEquals([$mock_operation], $permission->withPossibleOperations([$mock_operation])->getPossibleOperations());
        $this->assertEquals([1,2], $permission->withSelectedOperationIds([1,2])->getSelectedOperationIds());
        $this->assertEquals($mock_context, $permission->withContext($mock_context)->getContext());
        $this->assertEquals(true, $permission->withProtected(true)->isProtected());
        $this->assertEquals(false, $permission->withParentId(777)->isTemplate());
        $this->assertEquals(true, $permission->withSelectedOperationIds([1,2])->isOperationIdSelected(1));
    }
}
