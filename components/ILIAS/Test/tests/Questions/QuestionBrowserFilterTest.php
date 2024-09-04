<?php

namespace Questions;

use ilAssQuestionLifecycle;
use ILIAS\Test\Questions\QuestionsBrowserFilter;
use ILIAS\Test\Questions\QuestionsBrowserTable;
use ILIAS\Test\Tests\Questions\ilUIFilterService;
use ILIAS\Test\Tests\Questions\ilUIService;
use ilRbacSystem;
use ilTestBaseTestCase;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Taxonomy\DomainService as TaxonomyDomainService;
use ILIAS\UI\Component\Input\Container\Filter\FilterInput;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use ILIAS\UI\Component\Input\Field\Select;
use ILIAS\UI\Component\Input\Field\Text;
use ILIAS\UI\Component\Table\Data;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRow;
use ILIAS\UI\Implementation\Component\Table\DataRowBuilder;
use ilTestQuestionBrowserTableGUI;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuestionBrowserFilterTest extends ilTestBaseTestCase
{
    private const PARENT_OBJ_ID = 1;
    private const REQUEST_REF_ID = 2;

    private TaxonomyDomainService $taxonomy;
    private ilTestQuestionBrowserTableGUI $gui;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilComponentRepository();
        $this->setGlobalVariable('rbacsystem', $this->createMock(ilRbacSystem::class));

        $this->taxonomy = $this->createMock(TaxonomyDomainService::class);
        $this->gui = $this->createMock(ilTestQuestionBrowserTableGUI::class);
    }

    public function testGetFilter(): void
    {
        $ui_service = $this->createMock(\ilUIService::class);
        $ui_filter_service = $this->createMock(\ilUIFilterService::class);

        $ui_service
            ->expects($this->once())
            ->method('filter')
            ->willReturn($ui_filter_service);

        $ui_filter_service->expects($this->once())
            ->method('standard')
            ->willReturnCallback(function (
                string $filter_id,
                string $base_action,
                array $inputs,
                array $is_input_initially_rendered,
                bool $is_activated,
                bool $is_expanded
            ) {
                if (!$this instanceof TestCase) {
                    throw new LogicException('The trait ' . __CLASS__ . ' can only be used in a test case');
                }

                $this->assertEquals(ilTestQuestionBrowserTableGUI::CMD_BROWSE_QUESTIONS, $base_action);
                $this->assertNotEmpty($inputs);
                $this->assertNotEmpty($is_input_initially_rendered);
                $this->assertTrue($is_activated);
                $this->assertTrue($is_expanded);

                $this->assertContainsOnlyInstancesOf(FilterInput::class, $inputs);
                $this->assertContainsOnly('bool', $is_input_initially_rendered);

                $expected_filter = $this->getExpectedFilter();
                foreach ($expected_filter as $name => $options) {
                    $this->assertArrayHasKey($name, $inputs);
                    $this->assertArrayHasKey($name, $is_input_initially_rendered);
                    $this->assertInstanceOf($options[0], $inputs[$name]);
                    $this->assertEquals($options[1], $is_input_initially_rendered[$name]);
                }
                return $this->createMock(Filter::class);
            });

        $table = $this->instantiateFilter();
        $filter = $table->getFilter($ui_service, ilTestQuestionBrowserTableGUI::CMD_BROWSE_QUESTIONS);

        $this->assertInstanceOf(Filter::class, $filter);
    }

    protected function getExpectedFilter(): array
    {
        return [
            'title' => [Text::class, true],
            'description' => [Text::class, false],
            'type' => [Select::class, true],
            'author' => [Text::class, false],
            'lifecycle' => [Select::class, false],
            'parent_title' => [Text::class, true],
            'taxonomy_title' => [Text::class, false],
            'taxonomy_node_title' => [Text::class, false],
            'feedback' => [Select::class, false],
            'hints' => [Select::class, false],
        ];
    }

    protected function getExpectedSortableColumns(): array
    {
        return [
            'title' => [1, 2, 3, 4],
            'description' => [1, 4, 3, 2],
            'ttype' => [2, 3, 1, 4],
            'points' => [3, 4, 1, 2],
            'author' => [2, 3, 1, 4],
            'lifecycle' => [3, 1, 2, 4],
            'parent_title' => [3, 4, 1, 2],
            'taxonomies' => [2, 3, 4, 1],
            'feedback' => [1, 2, 3, 4],
            'hints' => [1, 2, 3, 4],
            'created' => [1, 2, 3, 4],
            'tstamp' => [1, 2, 3, 4], // @todo: Issue: This sould be updated but postOrder is called "to early"
        ];
    }

    protected function provideGetRowsTestData(): iterable
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
            ['range' => $data_factory->range(5, 7), 'expected_row_count' => 0], // @todo: See how to handle overflow
        ];

        $testCases = [];

        foreach ($this->getExpectedSortableColumns() as $column => $expected_sorted_ids) {
            foreach ($ranges as $range) {
                foreach ([Order::ASC, Order::DESC] as $direction) {
                    $range_start = $range['range']->getStart();
                    $range_length = $range['range']->getLength();

                    $expected_row_ids = $direction === Order::ASC ? $expected_sorted_ids : array_reverse($expected_sorted_ids);
                    $expected_row_ids = array_slice($expected_row_ids, $range_start, $range_length);

                    yield sprintf('order_by_%s_%s_range_%d_%d', $column, $direction, $range_start, $range_length) => [
                        'range' => $range['range'],
                        'order' => $data_factory->order($column, $direction),
                        'fixture_data' => $this->getQuestionFixtureData(),
                        'expected_row_count' => $range['expected_row_count'],
                        'expected_row_ids' => $expected_row_ids
                    ];
                }
            }
        }

        return $testCases;
    }

    /**
     * @return array<array-key, array<string, scalar>>
     */
    private function getQuestionFixtureData(): array
    {
        return [
            1 => [
                'question_id' => 1,
                'title' => 'How does photosynthesis work?',
                'description' => 'Explain the process of photosynthesis and its significance.',
                'author' => 'John Doe',
                'taxonomies' => ['Biology', 'Botany'],
                'ttype' => 'Short answer',
                'feedback' => false,
                'hints' => false,
                'comments' => 0,
                'complete' => 1,
                'points' => 4,
                'parent_title' => 'Pool 2',
                'lifecycle' => ilAssQuestionLifecycle::FINAL,
                'obj_fi' => 5,
                'created' => time(),
                'tstamp' => time(),
            ],
            2 => [
                'question_id' => 2,
                'title' => 'What is the boiling point of water?',
                'description' => 'State the temperature at which water boils.',
                'author' => 'Emily Johnson',
                'taxonomies' => ['Chemistry'],
                'ttype' => 'Fill in the blank',
                'feedback' => false,
                'hints' => false,
                'comments' => 0,
                'complete' => 1,
                'points' => 6,
                'parent_title' => 'Pool 3',
                'lifecycle' => ilAssQuestionLifecycle::FINAL,
                'obj_fi' => 5,
                'created' => time(),
                'tstamp' => time(),
            ],
            3 => [
                'question_id' => 3,
                'title' => 'What is the capital of France?',
                'description' => 'Provide the name of the capital city of France.',
                'author' => 'Jane Smith',
                'taxonomies' => ['Geography'],
                'ttype' => 'Multiple choice',
                'feedback' => false,
                'hints' => true,
                'comments' => 0,
                'complete' => 0,
                'points' => 1,
                'parent_title' => 'Pool 1',
                'lifecycle' => ilAssQuestionLifecycle::DRAFT,
                'obj_fi' => 3,
                'created' => time(),
                'tstamp' => time(),
            ],
            4 => [
                'question_id' => 4,
                'title' => 'Who wrote "Romeo and Juliet"?',
                'description' => 'Identify the author of the famous play "Romeo and Juliet".',
                'author' => 'Michael Brown',
                'taxonomies' => ['Literature'],
                'ttype' => 'True/false',
                'feedback' => true,
                'hints' => true,
                'comments' => 0,
                'complete' => 1,
                'points' => 3,
                'parent_title' => 'Pool 1',
                'lifecycle' => ilAssQuestionLifecycle::FINAL,
                'obj_fi' => 3,
                'created' => time(),
                'tstamp' => time(),
            ]
        ];
    }

    private function instantiateFilter(array $fixture_data = []): QuestionsBrowserFilter&MockObject
    {
        $mock = $this->getMockBuilder(QuestionsBrowserFilter::class)
            ->setConstructorArgs([
                $this->dic->database(),
                $this->dic->language(),
                $this->dic->refinery(),
                $this->dic['component.repository'],
                $this->dic->notes(),
                $this->dic->ui()->factory(),
                $this->dic->ui()->renderer(),
                new DataFactory(),
                $this->taxonomy,
                $this->dic->ctrl(),
                $this->gui,
                self::PARENT_OBJ_ID,
                self::REQUEST_REF_ID
            ])
            ->onlyMethods(['load', 'getQuestionDataArray'])
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('getQuestionData')
            ->willReturn($fixture_data);

        return $mock;
    }
}
