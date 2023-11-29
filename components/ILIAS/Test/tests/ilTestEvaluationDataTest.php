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
 * Class ilTestEvaluationDataTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestEvaluationDataTest extends ilTestBaseTestCase
{
    private ilTestEvaluationData $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        global $DIC;
        $this->addGlobal_ilDB();

        $this->testObj = new ilTestEvaluationData($DIC['ilDB']);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestEvaluationData::class, $this->testObj);
    }

    public function test__sleep(): void
    {
        $expected = ['question_titles', 'participants', 'statistics', 'arr_filter', 'datasets', 'test'];

        $this->assertEquals($expected, $this->testObj->__sleep());
    }

    public function testAccessFilteredParticipantList(): void
    {
        $value_mock = $this->createMock(ilTestParticipantList::class);
        $this->testObj->setAccessFilteredParticipantList($value_mock);

        $this->assertEquals($value_mock, $this->testObj->getAccessFilteredParticipantList());
    }

    public function testTest(): void
    {
        $value_mock = $this->createMock(ilObjTest::class);
        $this->testObj->setTest($value_mock);

        $this->assertEquals($value_mock, $this->testObj->getTest());
    }

    public function testDatasets(): void
    {
        $this->testObj->setDatasets(20);

        $this->assertEquals(20, $this->testObj->getDatasets());
    }

    public function testQuestionTitle(): void
    {
        $expected = [
            120 => 'abcd',
            121 => 'Hello',
            2150 => 'World',
        ];

        foreach ($expected as $questionId => $questionTitle) {
            $this->testObj->addQuestionTitle($questionId, $questionTitle);

            $this->assertEquals($questionTitle, $this->testObj->getQuestionTitle($questionId));
        }

        $this->assertEquals($expected, $this->testObj->getQuestionTitles());
    }
}
