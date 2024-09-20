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

namespace ILIAS\Test\Tests\Questions\Presentation;

use ILIAS\Test\Questions\Presentation\QuestionsTable;
use ILIAS\Test\Questions\Presentation\QuestionsTableQuery;
use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Modal;
use ILIAS\Test\Questions\Properties\DatabaseRepository as QuestionsRepository;

/**
 * Class QuestionsTableTest
 */
class QuestionsTableTest extends \ilTestBaseTestCase
{
    private QuestionsTable $table_gui;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();
        $this->addGlobal_uiFactory();
        $this->addGlobal_refinery();
        $this->addGlobal_http();
        $this->addGlobal_lng();

        $obj_test = $this->getMockBuilder(\ilObjTest::class)
            ->disableOriginalConstructor()
            ->getMock();


        $commands = $this->getMockBuilder(QuestionsTableQuery::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commands->method('getRowBoundURLBuilder')
            ->willReturn(
                [
                    $this->getMockBuilder(\ILIAS\UI\URLBuilder::class)
                        ->disableOriginalConstructor()
                        ->getMock(),
                    $this->getMockBuilder(\ILIAS\UI\URLBuilderToken::class)
                        ->disableOriginalConstructor()
                        ->getMock(),

                ]
            );

        $questionrepository = $this->getMockBuilder(QuestionsRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $questionrepository->expects($this->any())
            ->method('getQuestionPropertiesWithAggregatedResultsForTest')
            ->willReturn(
                [
                    new \ILIAS\Test\Questions\Properties\Properties(
                        77,
                        new \ILIAS\TestQuestionPool\Questions\GeneralQuestionProperties(
                            $this->createMock(\ilComponentFactory::class),
                            77,
                            88,
                            null,
                            0,
                            null,
                            7,
                            '',
                            0,
                            'question one',
                            'description one',
                            '',
                            3
                        )
                    )
                ]
            );

        $title_builder = $this->createMock(\ILIAS\Test\Utilities\TitleColumnsBuilder::class);

        $this->table_gui = new QuestionsTable(
            $DIC['ui.factory'],
            $DIC['ui.renderer'],
            $DIC['tpl'],
            $DIC['http']->request(),
            $commands,
            $DIC['lng'],
            $DIC['ilCtrl'],
            $obj_test,
            $questionrepository,
            $title_builder,
            false,
            false,
            false
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(QuestionsTable::class, $this->table_gui);
    }

    public function testQuestionsTableGUIwillReturnProperTypes(): void
    {
        $this->assertInstanceOf(Table\Ordering::class, $this->table_gui->getTableComponent());
        $this->assertInstanceOf(Modal\Interruptive::class, $this->table_gui->getDeleteConfirmation([]));
    }

    public function testQuestionsTableDefinesActions(): void
    {
        $row = $this->createMock(Table\OrderingRow::class);
        $row->expects($this->exactly(8))
            ->method('withDisabledAction')
            ->willReturn($row);

        $row_builder = $this->getMockBuilder(Table\OrderingRowBuilder::class)->getMock();
        $row_builder
            ->expects($this->once())
            ->method('buildOrderingRow')
            ->willReturn($row);

        iterator_to_array($this->table_gui->getRows($row_builder, []));
    }
}
