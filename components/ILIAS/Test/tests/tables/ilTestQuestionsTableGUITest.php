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

use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Modal;
use ILIAS\UI\Implementation\Component as C;

/**
 * Class ilTestQuestionsTableGUITest
 */
class ilTestQuestionsTableGUITest extends ilTestBaseTestCase
{
    private ilTestQuestionsTableGUI $table_gui;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();
        $this->addGlobal_uiFactory();
        $this->addGlobal_refinery();
        $this->addGlobal_http();
        $this->addGlobal_lng();


        $records = $this->getSomeRecords();
        $this->table_gui = new class ($records, $DIC) extends ilTestQuestionsTableGUI {
            public function __construct(
                protected $data,
                $DIC
            ) {
                parent::__construct(
                    $DIC['ui.factory'],
                    new ILIAS\Data\Factory(),
                    $DIC['refinery'],
                    $DIC['http'],
                    $DIC['lng'],
                    'some_table_id',
                    fn() => ''
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
            'complete' => "1",
            'lifecycle' => 'draft',
            'points' => 3,
            ],
        ];
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestQuestionsTableGUI::class, $this->table_gui);
        $this->assertInstanceOf(ilTestQuestionsTableGUI::class, $this->table_gui->withContextCorrections());
        $this->assertInstanceOf(ilTestQuestionsTableGUI::class, $this->table_gui->withQuestionEditing());
    }

    public function testQuestionsTableGUIwillReturnProperTypes(): void
    {
        $this->assertInstanceOf(Table\Ordering::class, $this->table_gui->getTable([]));
        $this->assertInstanceOf(Modal\Interruptive::class, $this->table_gui->getDeleteConfirmation([]));
        $this->assertIsArray($this->table_gui->getOrderData());
    }

    public function testQuestionsTableDefinesActions(): void
    {
        $row = $this->createMock(Table\OrderingRow::class);
        $row->expects($this->exactly(9))
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
