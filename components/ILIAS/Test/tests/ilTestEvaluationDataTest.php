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
 * @deprecated 11; Result/EvaluationData will be refined.
 */
class ilTestEvaluationDataTest extends ilTestBaseTestCase
{
    private ilTestEvaluationData $testObj;

    protected function setUp(): void
    {
        parent::setUp();
        $user_data = [
            new ilTestEvaluationUserData(0),
            new ilTestEvaluationUserData(1),
        ];
        $this->testObj = new ilTestEvaluationData($user_data);
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

        $this->assertEquals($expected[2150], $this->testObj->getQuestionTitle(2150));
    }

    public function testEvaluationFactory(): void
    {
        $records = [];
        $records[] = [
            "active_id" => 7 ,
            "question_fi" => 9,
            "result_points" => 1.2,
            "answered" => true,
            "manual" => 1,
            "original_id" => null,
            "questiontitle" => "some title",
            "qpl_maxpoints" => 2.4,
            "submitted" => true,
            "last_finished_pass" => 1,
            "pass" => 1,
            "points" => 10.3,
            "maxpoints" => 32,
            "questioncount" => 32,
            "answeredquestions" => 1,
            "workingtime" => 28,
            "tstamp" => 1731941437,
            "obligations_answered" => true,
            "exam_id" => "I0_T355_A7_P1",
            "usr_id" => 6,
            "firstname" => "root",
            "lastname" => "user",
            "title" => "",
            "login" => "root",
            "finalized_by" => null
        ];
        $records[] = ['first_access' => '2024-12-11 17:54:26'];
        $records[] = null;

        $test_obj = $this->createMock(ilObjTest::class);
        $test_obj
            ->expects($this->once())
            ->method('getPassScoring');
        $test_obj
            ->expects($this->once())
            ->method('getAccessFilteredParticipantList')
            ->willReturn(null);
        $test_obj
            ->expects($this->once())
            ->method('getTestParticipants')
            ->willReturn([7]);
        $test_obj
            ->expects($this->once())
            ->method('getVisitingTimeOfParticipant')
            ->willReturn(
                [
                    'first_access' => new \DateTimeImmutable(),
                    'last_access' => new \DateTimeImmutable()
                ]
            );

        $db = $this->createMock(ilDBInterface::class);
        $db
            ->expects($this->exactly(3))
            ->method('fetchAssoc')
            ->willReturnCallback(
                function ($res) use (&$records) {
                    return array_shift($records);
                }
            );

        $factory = new ilTestEvaluationFactory($db, $test_obj);
        $data = $factory->getEvaluationData();
        $this->assertInstanceOf(ilTestEvaluationData::class, $data);

        $this->assertEquals(
            [7],
            $data->getParticipantIds()
        );
        $this->assertInstanceOf(
            ilTestEvaluationUserData::class,
            $data->getParticipant(7)
        );
    }
}
