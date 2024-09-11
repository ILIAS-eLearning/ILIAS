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

namespace ILIAS\Test\Tests\ExportImport;

use ILIAS\Test\ExportImport\ExportRandomQuestionSet;

/**
 * @author Marvin Beym <mbeym@databay.de>
 */
class ExportRandomQuestionSetTest extends \ilTestBaseTestCase
{
    private ExportRandomQuestionSet $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_ilErr();

        $this->testObj = new ExportRandomQuestionSet(
            $this->createMock(\ILIAS\Language\Language::class),
            $this->createMock(\ilDBInterface::class),
            $this->createmOck(\ilBenchmark::class),
            $this->createMock(\ILIAS\Test\Logging\TestLogger::class),
            $this->createMock(\ilTree::class),
            $DIC['component.repository'],
            $this->createMock(\ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository::class),
            $this->createMock(\ILIAS\FileDelivery\Services::class),
            $this->getTestObjMock()
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ExportRandomQuestionSet::class, $this->testObj);
    }
}
