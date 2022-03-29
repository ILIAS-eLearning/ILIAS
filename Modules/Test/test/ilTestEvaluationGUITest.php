<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestEvaluationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestEvaluationGUITest extends ilTestBaseTestCase
{
    private ilTestEvaluationGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->addGlobal_lng();
        $this->addGlobal_tpl();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilias();
        $this->addGlobal_tree();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilPluginAdmin();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilObjDataCache();

        $this->testObj = new ilTestEvaluationGUI($this->createMock(ilObjTest::class));
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestEvaluationGUI::class, $this->testObj);
    }

    public function testTestAccess() : void
    {
        $testAccess_mock = $this->createMock(ilTestAccess::class);

        $this->testObj->setTestAccess($testAccess_mock);

        $this->assertEquals($testAccess_mock, $this->testObj->getTestAccess());
    }

    public function testGetHeaderNames() : void
    {
        $objTest_mock = $this->createMock(ilObjTest::class);
        $objTest_mock
            ->expects($this->any())
            ->method("getEvaluationAdditionalFields")
            ->willReturn([]);
        $this->testObj->object = $objTest_mock;

        $expectedTranslationCalls = [
            ["name", ""],
            ["login", ""],
            ["tst_reached_points", ""],
            ["tst_mark", ""],
            ["tst_answered_questions", ""],
            ["working_time", ""],
            ["detailed_evaluation", ""]
        ];

        $expectedResult = [
            "translation_name",
            "translation_login",
            "translation_tst_reached_points",
            "translation_tst_mark",
            "translation_tst_answered_questions",
            "translation_working_time",
            "translation_detailed_evaluation"
        ];

        $lng_mock = $this->createMock(ilLanguage::class);
        $lng_mock
            ->expects($this->any())
            ->method("txt")
            ->withConsecutive(...$expectedTranslationCalls)
            ->willReturnOnConsecutiveCalls(...$expectedResult);
        $this->testObj->lng = $lng_mock;

        $this->assertEquals($expectedResult, $this->testObj->getHeaderNames());
    }

    public function testGetHeaderVars() : void
    {
        $expectedResult1 = [
            "name",
            "login",
            "resultspoints",
            "resultsmarks",
            "qworkedthrough",
            "timeofwork",
            ""
        ];

        $expectedResult2 = [
            "counter",
            "resultspoints",
            "resultsmarks",
            "qworkedthrough",
            "timeofwork",
            ""
        ];

        $this->assertEquals($expectedResult1, $this->testObj->getHeaderVars());

        $objTest_mock = $this->createMock(ilObjTest::class);
        $objTest_mock
            ->expects($this->any())
            ->method("getAnonymity")
            ->willReturn(1);
        $this->testObj->object = $objTest_mock;

        $this->assertEquals($expectedResult2, $this->testObj->getHeaderVars());
    }

    public function testGetEvaluationQuestionId() : void
    {
        $this->assertEquals(20, $this->testObj->getEvaluationQuestionId(20, 0));
        $this->assertEquals(20, $this->testObj->getEvaluationQuestionId(20, -210));
        $this->assertEquals(125, $this->testObj->getEvaluationQuestionId(20, 125));
    }
}
