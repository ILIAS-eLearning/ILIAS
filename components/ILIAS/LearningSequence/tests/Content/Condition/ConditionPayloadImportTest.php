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

use ILIAS\DI\Container;
use ILIAS\LearningSequence\Content\Condition\AbstractCondition;
use ILIAS\LearningSequence\Content\Condition\InputCondition\PointsInputCondition\PointsInputCondition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConditionPayloadImportTest extends TestCase
{
    private ilDBInterface&MockObject $db;

    protected function setUp(): void
    {
        $this->db = $this->createMock(ilDBInterface::class);
        $this->db->method('tableExists')->willReturn(true);
        $this->db->method('tableColumnExists')->willReturnCallback(
            static fn(string $table, string $column): bool => $column !== 'item_ref_id'
        );
    }

    private function getCondition(): AbstractCondition
    {
        $dic = $this->createStub(Container::class);
        $dic->method('database')->willReturn($this->db);

        $condition = (new ReflectionClass(PointsInputCondition::class))
            ->newInstanceWithoutConstructor();
        $property = new ReflectionProperty(AbstractCondition::class, 'dic');
        $property->setValue($condition, $dic);
        $condition->setImportMapping([151 => 251]);

        return $condition;
    }

    public function testPayloadForForeignTableIsRejected(): void
    {
        $this->db->expects($this->never())->method('insert');
        $this->expectException(RuntimeException::class);

        $this->getCondition()->import([
            [
                'table' => 'conditions',
                'fields' => [],
                'rows' => [
                    [
                        'target_ref_id' => '1',
                        'trigger_ref_id' => '2',
                        'operator' => 'passed',
                    ],
                ],
            ],
        ], 42);
    }

    public function testOnlyDeclaredColumnsAreImportedWithDeclaredTypes(): void
    {
        $this->db->expects($this->once())
            ->method('insert')
            ->with(
                'lso_c_points_input',
                [
                    'condition_id' => ['integer', 42],
                    'points' => ['integer', '7'],
                ]
            );

        $this->getCondition()->import([
            [
                'table' => 'lso_c_points_input',
                'fields' => [
                    'points' => ['type' => 'text'],
                    'injected' => ['type' => 'text'],
                ],
                'rows' => [
                    [
                        'condition_id' => '1',
                        'points' => '7',
                        'injected' => 'value',
                    ],
                ],
            ],
        ], 42);
    }
}
