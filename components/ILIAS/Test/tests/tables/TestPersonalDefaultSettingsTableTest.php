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

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Test\Table\TestPersonalDefaultSettingsTable;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Implementation\Component\Table\DataRow;
use ILIAS\UI\Implementation\Component\Table\DataRowBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\Data\Factory as DataFactory;

class TestPersonalDefaultSettingsTableTest extends ilTestBaseTestCase
{
    private const PARENT_OBJ_ID = 1;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilComponentRepository();
        $this->setGlobalVariable('rbacsystem', $this->createMock(ilRbacSystem::class));
        $this->addGlobal_ilLoggerFactory();
        $this->addGlobal_ilUser();
    }

    public function testImplementsDataRetrievalInterface(): void
    {
        $this->assertInstanceOf(DataRetrieval::class, $this->instantiateTable());
    }

    /**
     * @dataProvider getRowsDataProvider
     */
    public function testGetRows(
        Range $range,
        Order $order,
        array $fixture_data,
        int $expected_row_count,
        array $expected_row_ids,
        array $visible_column_ids = [],
        array $filter = [],
        array $additional_parameters = []
    ): void {
        $table = $this->instantiateTable($fixture_data);
        $row_builder = new DataRowBuilder();
        $rows = $table->getRows(
            $row_builder,
            $visible_column_ids,
            $range,
            $order,
            $filter,
            $additional_parameters
        );

        $this->assertIsIterable($rows);
        $rows = iterator_to_array($rows);
        $this->assertCount($expected_row_count, $rows);
        $this->assertEquals($expected_row_ids, array_map(static fn(DataRow $row) => $row->getId(), $rows));
    }

    public static function getRowsDataProvider(): iterable
    {
        $data_factory = new DataFactory();

        $ranges = [
            ['range' => $data_factory->range(0, 0), 'expected_row_count' => 0],
            ['range' => $data_factory->range(0, 10), 'expected_row_count' => 4],
            ['range' => $data_factory->range(0, 4), 'expected_row_count' => 4],
            ['range' => $data_factory->range(2, 1), 'expected_row_count' => 1],
            ['range' => $data_factory->range(0, 2), 'expected_row_count' => 2],
            ['range' => $data_factory->range(2, 2), 'expected_row_count' => 2],
            ['range' => $data_factory->range(1, 2), 'expected_row_count' => 2],
            ['range' => $data_factory->range(1, 3), 'expected_row_count' => 3],
            ['range' => $data_factory->range(0, 3), 'expected_row_count' => 3],
            ['range' => $data_factory->range(5, 7), 'expected_row_count' => 0] // @todo: See how to handle overflow
        ];

        foreach(self::getExpectedSortableColumns() as $column => $expected_sorted_ids) {
            foreach($ranges as $range) {
                foreach([Order::ASC, Order::DESC] as $direction) {
                    $range_start = $range['range']->getStart();
                    $range_length = $range['range']->getLength();

                    $expected_row_ids = $direction === Order::ASC ? $expected_sorted_ids : array_reverse($expected_sorted_ids);
                    $expected_row_ids = array_slice($expected_row_ids, $range_start, $range_length);
                    yield sprintf('order_by_%s_%s_range_%d_%d', $column, $direction, $range_start, $range_length) => [
                        'range' => $range['range'],
                        'order' => $data_factory->order($column, $direction),
                        'fixture_data' => self::getQuestionFixtureData(),
                        'expected_row_count' => $range['expected_row_count'],
                        'expected_row_ids' => $expected_row_ids
                    ];
                }
            }
        }
    }

    private static function getExpectedSortableColumns(): array
    {
        return [
            'name' => [3, 1, 2, 4],
            'tstamp' => [1, 3, 4, 2]
        ];
    }

    private static function getQuestionFixtureData(): array
    {
        return [
            1 => [
                'test_defaults_id' => 1,
                'tstamp' => time(),
                'name' => 'Text 1'
            ],
            2 => [
                'test_defaults_id' => 2,
                'tstamp' => strtotime('+4 day'),
                'name' => 'Text 2'
            ],
            3 => [
                'test_defaults_id' => 3,
                'tstamp' => strtotime('+1 day'),
                'name' => 'AText 3'
            ],
            4 => [
                'test_defaults_id' => 4,
                'tstamp' => strtotime('+3 day'),
                'name' => 'Text 4'
            ]
        ];
    }


    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    private function instantiateTable(array $data = []): TestPersonalDefaultSettingsTable&MockObject
    {
        $gui_mock = $this->createMock(TestPersonalDefaultSettingsGUI::class);
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock
            ->method('getLinkTarget')
            ->willReturnCallback(function () {
                return 'testLink';
            });

        return $this->getMockBuilder(TestPersonalDefaultSettingsTable::class)
            ->setConstructorArgs([
                $this->dic->language(),
                $this->dic->ui()->factory(),
                $this->dic->ctrl(),
                $gui_mock,
                self::PARENT_OBJ_ID,
                $data
            ])
            ->onlyMethods([])
            ->getMock();
    }
}
