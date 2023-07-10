<?php

declare(strict_types=1);

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

/**
 * Class ilAssQuestionPageCommandForwarderTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilAssQuestionPageCommandForwarderTest extends ilTestBaseTestCase
{
    private ilAssQuestionPageCommandForwarder $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilAssQuestionPageCommandForwarder();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilAssQuestionPageCommandForwarder::class, $this->testObj);
    }

    public function testTestObj(): void
    {
        $testObj = $this->createMock(ilObjTest::class);
        $this->testObj->setTestObj($testObj);

        $this->assertEquals($testObj, $this->testObj->getTestObj());
    }
}
