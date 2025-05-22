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

use ILIAS\Data\Factory;
use ILIAS\Test\Questions\Presentation\QuestionsOfAttemptTable;

class QuestionsOfAttemptTableTest extends \ilTestBaseTestCase
{
    private QuestionsOfAttemptTable $table;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_lng();
        $this->addGlobal_http();
        $this->addGlobal_ilUser();

        $test_obj_mock = $this->getTestObjMock();
        $parent_gui_mock = $this->createMock(\ilTestPlayerAbstractGUI::class);

        $this->table = new QuestionsOfAttemptTable(
            $DIC['lng'],
            $DIC['ilCtrl'],
            $DIC['ui.factory'],
            $this->createMock(Factory::class),
            $DIC['http'],
            $parent_gui_mock,
            $test_obj_mock,
            []
        );

    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(QuestionsOfAttemptTable::class, $this->table);
    }

}
