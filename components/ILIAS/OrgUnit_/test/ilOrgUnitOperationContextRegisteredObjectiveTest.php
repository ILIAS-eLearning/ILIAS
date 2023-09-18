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
 *********************************************************************/

use PHPUnit\Framework\TestCase;
use ILIAS\Setup\Environment;

class ilOrgUnitOperationContextRegisteredObjectiveTest extends TestCase
{
    protected int $next_id = 3;
    protected string $context = 'first context';

    protected function getMockEnviroment(
        bool $expect_insert,
        int $expected_parent_id = 0
    ): Environment {
        $db = $this->createMock(ilDBInterface::class);
        if ($expect_insert) {
            $db
                ->expects(self::once())
                ->method('insert')
                ->with('il_orgu_op_contexts', [
                    'id' => ['integer', $this->next_id],
                    'context' => ['text', $this->context],
                    'parent_context_id' => ['integer', $expected_parent_id]
                ]);
            $db
                ->expects(self::once())
                ->method('nextId')
                ->with('il_orgu_op_contexts')
                ->willReturn($this->next_id);
        }

        $env = $this->createMock(Environment::class);
        $env
            ->method('getResource')
            ->with(Environment::RESOURCE_DATABASE)
            ->willReturn($db);

        return $env;
    }

    protected function getMockObjective(
        int $existing_context_id,
        int $parent_id,
        string $context_name,
        ?string $parent_context = null
    ): ilOrgUnitOperationContextRegisteredObjective {
        $obj = $this
            ->getMockBuilder(
                ilOrgUnitOperationContextRegisteredObjective::class
            )
            ->setConstructorArgs([$context_name, $parent_context])
            ->onlyMethods(['getContextId'])
            ->getMock();

        $obj
            ->method('getContextId')
            ->withConsecutive(
                [$this->isInstanceOf(ilDBInterface::class), $context_name],
                [$this->isInstanceOf(ilDBInterface::class), $parent_context]
            )
            ->willReturnOnConsecutiveCalls($existing_context_id, $parent_id);

        return $obj;
    }

    protected function testGetHash(): void
    {
        $obj1 = $this->getMockObjective(0, 0, $this->context);
        $obj2 = $this->getMockObjective(0, 0, 'other context');
        $this->assertNotEquals(
            $obj1->getHash(),
            $obj2->getHash()
        );
    }

    protected function testGetPreconditions(): void
    {
        $env = $this->createMock(Environment::class);
        $obj = $this->getMockObjective(0, 0, $this->context);
        $this->assertContainsOnlyInstancesOf(
            ilDatabaseInitializedObjective::class,
            $obj->getPreconditions($env)
        );
    }

    public function testIsApplicable(): void
    {
        $env = $this->getMockEnviroment(false);

        $obj = $this->getMockObjective(
            0,
            0,
            $this->context
        );
        $this->assertTrue($obj->isApplicable($env));

        $obj = $this->getMockObjective(
            7,
            0,
            $this->context
        );
        $this->assertNotTrue($obj->isApplicable($env));

        $obj = $this->getMockObjective(
            0,
            9,
            $this->context,
            'parent'
        );
        $this->assertTrue($obj->isApplicable($env));

        $obj = $this->getMockObjective(
            7,
            9,
            $this->context,
            'parent'
        );
        $this->assertNotTrue($obj->isApplicable($env));
    }

    public function testIsApplicableInvalidParentException(): void
    {
        $env = $this->getMockEnviroment(false);

        $obj = $this->getMockObjective(
            0,
            0,
            $this->context,
            'parent'
        );
        $this->expectException(Exception::class);
        $obj->isApplicable($env);
    }

    public function testAchieve(): void
    {
        $env = $this->getMockEnviroment(true, 13);
        $obj = $this->getMockObjective(
            0,
            13,
            $this->context,
            'parent'
        );
        $obj->achieve($env);
    }
}
