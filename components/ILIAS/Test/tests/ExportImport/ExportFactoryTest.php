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

use ILIAS\Test\ExportImport\Factory;

use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

/**
 * @author Marvin Beym <mbeym@databay.de>
 */
class ExportFactoryTest extends ilTestBaseTestCase
{
    private Factory $testObj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->addGlobal_ilBench();

        $this->testObj = new Factory(
            $this->getTestObjMock(),
            $this->createMock(ilLanguage::class),
            $this->createMock(ILIAS\Test\Logging\TestLogger::class),
            $this->createMock(ilTree::class),
            $this->createMock(ilComponentRepository::class),
            $this->createMock(GeneralQuestionPropertiesRepository::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(Factory::class, $this->testObj);
    }
}
