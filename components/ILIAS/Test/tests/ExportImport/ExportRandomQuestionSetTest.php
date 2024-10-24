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

namespace ILIAS\Test\Tests;

use ILIAS\Test\ExportImport\ExportRandomQuestionSet;

/**
 * @author Marvin Beym <mbeym@databay.de>
 */
class ExportRandomQuestionSetTest extends \ilTestBaseTestCase
{
    private ilTestExportRandomQuestionSet $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_ilBench();
        $this->addGlobal_ilErr();
        $this->addGlobal_tree();

        $objTest = $this->getTestObjMock();
        $test_logger = $this->createMock(\ILIAS\Test\Logging\TestLogger::class);
        $this->testObj = new ExportRandomQuestionSet(
            $objTest,
            $DIC['lng'],
            $test_logger,
            $DIC['tree'],
            $DIC['component.repository'],
            $this->createMock(\ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ExportRandomQuestionSet::class, $this->testObj);
    }
}
