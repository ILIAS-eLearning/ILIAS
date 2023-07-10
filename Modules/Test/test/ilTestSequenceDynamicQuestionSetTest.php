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
 * Class ilTestSequenceDynamicQuestionSetTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSequenceDynamicQuestionSetTest extends ilTestBaseTestCase
{
    private ilTestSequenceDynamicQuestionSet $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $db_mock = $this->createMock(ilDBInterface::class);
        $testDynamicQuestionSet_mock = $this->createMock(ilTestDynamicQuestionSet::class);

        $this->testObj = new ilTestSequenceDynamicQuestionSet($db_mock, $testDynamicQuestionSet_mock, 17);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSequenceDynamicQuestionSet::class, $this->testObj);
    }

    public function testGetActiveId(): void
    {
        $this->assertEquals(17, $this->testObj->getActiveId());
    }

    public function testPreventCheckedQuestionsFromComingUpEnabled(): void
    {
        $this->testObj->setPreventCheckedQuestionsFromComingUpEnabled(false);
        $this->assertFalse($this->testObj->isPreventCheckedQuestionsFromComingUpEnabled());

        $this->testObj->setPreventCheckedQuestionsFromComingUpEnabled(true);
        $this->assertTrue($this->testObj->isPreventCheckedQuestionsFromComingUpEnabled());
    }

    public function testCurrentQuestionId(): void
    {
        $this->testObj->setCurrentQuestionId(5);
        $this->assertEquals(5, $this->testObj->getCurrentQuestionId());
    }
}
