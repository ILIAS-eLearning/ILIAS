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

/**
 * Class ilTestQuestionSetConfigFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestQuestionSetConfigFactoryTest extends ilTestBaseTestCase
{
    private ilTestQuestionSetConfigFactory $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_lng();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilComponentRepository();

        $this->testObj = new ilTestQuestionSetConfigFactory(
            $DIC['tree'], // TODO: replace with proper attribute
            $DIC->database(),
            $DIC->language(),
            $DIC['ilLog'], // TODO: replace with proper attribute
            $DIC['component.repository'], // TODO: replace with proper attribute
            $this->createMock(ilObjTest::class),
            $this->createMock(\ILIAS\TestQuestionPool\QuestionInfoService::class),
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestQuestionSetConfigFactory::class, $this->testObj);
    }
}
