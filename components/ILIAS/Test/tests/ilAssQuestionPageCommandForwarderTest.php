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
 * Class ilAssQuestionPageCommandForwarderTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilAssQuestionPageCommandForwarderTest extends ilTestBaseTestCase
{
    private ilAssQuestionPageCommandForwarder $testObj;
    private ilObjTest $test;

    protected function setUp(): void
    {
        parent::setUp();

        $this->test = $this->createMock(ilObjTest::class);
        $this->testObj = new ilAssQuestionPageCommandForwarder(
            $this->test,
            $this->createMock(ilLanguage::class),
            $this->createMock(ilCtrlInterface::class),
            $this->createMock(ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository::class),
            $this->createMock(\ILIAS\Test\RequestDataCollector::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilAssQuestionPageCommandForwarder::class, $this->testObj);
    }

    public function testTestObj(): void
    {
        $this->assertEquals($this->test, $this->testObj->getTestObj());
    }
}
