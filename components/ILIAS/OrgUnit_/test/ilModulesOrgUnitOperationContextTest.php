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

class ilModulesOrgUnitOperationContextTest extends TestCase
{
    public function testOrgUnitOperationContextConstruction(): void
    {
        $context = new ilOrgUnitOperationContext();
        $this->assertInstanceOf(ilOrgUnitOperationContext::class, $context);
        $this->assertEquals(0, $context->getId());
        $this->assertEquals(ilOrgUnitOperationContext::CONTEXT_OBJECT, $context->getContext());
        $this->assertEquals(0, $context->getParentContextId());
        $this->assertEquals([ilOrgUnitOperationContext::CONTEXT_OBJECT], $context->getPathNames());
        $this->assertEquals([0], $context->getPathIds());
    }

    public function testOrgUnitOperationContextModification(): void
    {
        $context = new ilOrgUnitOperationContext(666);
        $this->assertEquals(666, $context->getId());
        $this->assertEquals(
            ilOrgUnitOperationContext::CONTEXT_CRS,
            $context->withContext(ilOrgUnitOperationContext::CONTEXT_CRS)->getContext()
        );
        $this->assertEquals(777, $context->withParentContextId(777)->getParentContextId());
        $this->assertEquals(
            [ilOrgUnitOperationContext::CONTEXT_OBJECT,ilOrgUnitOperationContext::CONTEXT_CRS],
            $context->withPathNames([ilOrgUnitOperationContext::CONTEXT_OBJECT,ilOrgUnitOperationContext::CONTEXT_CRS])
                ->getPathNames()
        );
        $this->assertEquals([999,888], $context->withPathIds([999,888])->getPathIds());
    }
}
