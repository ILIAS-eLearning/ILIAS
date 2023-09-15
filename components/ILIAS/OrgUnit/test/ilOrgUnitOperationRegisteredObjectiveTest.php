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

class ilOrgUnitOperationRegisteredObjectiveTest extends TestCase
{
    protected int $next_id = 3;
    protected string $operation = 'first operation';
    protected string $description = 'description';

    protected function getMockEnviroment(
        bool $expect_insert,
        int $expected_context_id = 0
    ): Environment {
        $db = $this->createMock(ilDBInterface::class);
        if ($expect_insert) {
            $db
                ->expects(self::once())
                ->method('insert')
                ->with('il_orgu_operations', [
                    'operation_id' => ['integer', $this->next_id],
                    'operation_string' => ['text', $this->operation],
                    'description' => ['text', $this->description],
                    'list_order' => ['integer', 0],
                    'context_id' => ['integer', $expected_context_id],
                ]);
            $db
                ->expects(self::once())
                ->method('nextId')
                ->with('il_orgu_operations')
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
        bool $does_op_already_exist,
        int $context_id,
        string $operation_name
    ): ilOrgUnitOperationRegisteredObjective {
        $obj = $this
            ->getMockBuilder(
                ilOrgUnitOperationRegisteredObjective::class
            )
            ->setConstructorArgs([$operation_name, $this->description, 'context'])
            ->onlyMethods(['getContextId', 'doesOperationExistInContext'])
            ->getMock();

        $obj
            ->method('getContextId')
            ->with($this->isInstanceOf(ilDBInterface::class), 'context')
            ->willReturn($context_id);
        $obj
            ->method('doesOperationExistInContext')
            ->with($this->isInstanceOf(ilDBInterface::class), $context_id, $operation_name)
            ->willReturn($does_op_already_exist);

        return $obj;
    }

    protected function testGetHash(): void
    {
        $obj1 = $this->getMockObjective(true, 0, $this->operation);
        $obj2 = $this->getMockObjective(true, 0, 'other op');
        $this->assertNotEquals(
            $obj1->getHash(),
            $obj2->getHash()
        );
    }

    protected function testGetPreconditions(): void
    {
        $env = $this->createMock(Environment::class);
        $obj = $this->getMockObjective(true, 0, $this->operation, '');
        $this->assertContainsOnlyInstancesOf(
            ilDatabaseInitializedObjective::class,
            $obj->getPreconditions($env)
        );
    }

    public function testIsApplicable(): void
    {
        $env = $this->getMockEnviroment(false);

        $obj = $this->getMockObjective(false, 9, $this->operation);
        $this->assertTrue($obj->isApplicable($env));

        $obj = $this->getMockObjective(true, 9, $this->operation);
        $this->assertNotTrue($obj->isApplicable($env));
    }

    public function testIsApplicableInvalidContextException(): void
    {
        $env = $this->getMockEnviroment(false);

        $obj = $this->getMockObjective(false, 0, $this->operation);
        $this->expectException(Exception::class);
        $obj->isApplicable($env);
    }

    public function testAchieve(): void
    {
        $env = $this->getMockEnviroment(true, 13);
        $obj = $this->getMockObjective(false, 13, $this->operation);
        $obj->achieve($env);
    }
}
