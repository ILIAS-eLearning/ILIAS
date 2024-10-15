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
use ILIAS\Test\Questions\Presentation\QuestionsTableActions;
use ILIAS\UI\Component\Table;
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


        $actions = $this->getMockBuilder(QuestionsTableActions::class)
            ->disableOriginalConstructor()
            ->getMock();
        $actions->expects($this->any())
            ->method('getQuestionTargetLinkBuilder')
            ->willReturn(fn(int $q): string => '');

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
            $DIC['http']->request(),
            $actions,
            $DIC['lng'],
            $obj_test,
            $questionrepository,
            $title_builder,
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(QuestionsTable::class, $this->table_gui);
    }

    public function testQuestionsTableGUIwillReturnProperTypes(): void
    {
        $this->assertInstanceOf(Table\Ordering::class, $this->table_gui->getTableComponent());
    }
}
