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

namespace ILIAS\Test\Tests\Questions;

use ILIAS\Test\Questions\QuestionsTable;
use ILIAS\Test\Questions\QuestionsTableQuery;

use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Modal;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

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

        $records = $this->getSomeRecords();
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

        $questionrepository = new class () extends GeneralQuestionPropertiesRepository {
            public function __construct()
            {
            }
        };

        $title_builder = $this->createMock(\ILIAS\Test\Utilities\TitleColumnsBuilder::class);

        $this->table_gui = new class (
            $records,
            $DIC,
            $obj_test,
            $commands,
            $questionrepository,
            $title_builder
        ) extends QuestionsTable {
            public function __construct(
                protected $data,
                $DIC,
                $obj_test,
                $commands,
                $questionrepository,
                $title_builder
            ) {
                parent::__construct(
                    $DIC['ui.factory'],
                    $DIC['ui.renderer'],
                    $DIC['tpl'],
                    $DIC['http']->request(),
                    $commands,
                    $DIC['lng'],
                    $DIC['ilCtrl'],
                    $obj_test,
                    $questionrepository,
                    $title_builder
                );
            }
            public function _getBinding()
            {
                return $this->getBinding($this->data);
            }
        };
    }
    protected function getSomeRecords(): array
    {
        return [
            [
            'question_id' => 77,
            'orig_obj_fi' => 88,
            'title' => 'question one',
            'desc' => 'description one',
            'type_tag' => 'assOrderingQuestion',
            'complete' => '1',
            'lifecycle' => 'draft',
            'points' => 3,
            ],
        ];
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(QuestionsTable::class, $this->table_gui);
        $this->assertInstanceOf(QuestionsTable::class, $this->table_gui->withContextCorrections());
        $this->assertInstanceOf(QuestionsTable::class, $this->table_gui->withQuestionEditing());
    }

    public function testQuestionsTableGUIwillReturnProperTypes(): void
    {
        $this->assertInstanceOf(Table\Ordering::class, $this->table_gui->getTableComponent([]));
        $this->assertInstanceOf(Modal\Interruptive::class, $this->table_gui->getDeleteConfirmation([]));
    }

    public function testQuestionsTableDefinesActions(): void
    {
        $row = $this->createMock(Table\OrderingRow::class);
        $row->expects($this->exactly(7))
            ->method('withDisabledAction')
            ->willReturn($row);

        $row_builder = $this->getMockBuilder(Table\OrderingRowBuilder::class)->getMock();
        $row_builder
            ->expects($this->once())
            ->method('buildOrderingRow')
            ->willReturn($row);

        iterator_to_array($this->table_gui->_getBinding()->getRows($row_builder, []));

    }
}
