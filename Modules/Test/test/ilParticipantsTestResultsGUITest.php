<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilParticipantsTestResultsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilParticipantsTestResultsGUITest extends ilTestBaseTestCase
{
    private ilParticipantsTestResultsGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();
        global $DIC;
        $DIC['tpl'] = $this->getMockBuilder(ilGlobalTemplateInterface::class)->getMock();
        $DIC['logger'] = $this->getMockBuilder(\ILIAS\DI\LoggingServices::class)->disableOriginalConstructor()->getMock();
        $DIC['http'] = $this->getMockBuilder(\ILIAS\HTTP\Services::class)->disableOriginalConstructor()->getMock();
        $DIC['refinery'] = $this->getMockBuilder(ILIAS\Refinery\Factory::class)->disableOriginalConstructor()->getMock();
        $this->testObj = new ilParticipantsTestResultsGUI();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilParticipantsTestResultsGUI::class, $this->testObj);
    }

    public function testTestObj() : void
    {
        $objTest_mock = $this->createMock(ilObjTest::class);

        $this->assertNull($this->testObj->getTestObj());

        $this->testObj->setTestObj($objTest_mock);
        $this->assertEquals($objTest_mock, $this->testObj->getTestObj());
    }

    public function testQuestionSetConfig() : void
    {
        $testQuestionSetConfig_mock = $this->createMock(ilTestQuestionSetConfig::class);

        $this->assertNull($this->testObj->getQuestionSetConfig());

        $this->testObj->setQuestionSetConfig($testQuestionSetConfig_mock);
        $this->assertEquals($testQuestionSetConfig_mock, $this->testObj->getQuestionSetConfig());
    }

    public function testTestAccess() : void
    {
        $testAccess_mock = $this->createMock(ilTestAccess::class);

        $this->assertNull($this->testObj->getTestAccess());

        $this->testObj->setTestAccess($testAccess_mock);
        $this->assertEquals($testAccess_mock, $this->testObj->getTestAccess());
    }

    public function testObjectiveParent() : void
    {
        $objectiveParent_mock = $this->createMock(ilTestObjectiveOrientedContainer::class);

        $this->assertNull($this->testObj->getObjectiveParent());

        $this->testObj->setObjectiveParent($objectiveParent_mock);
        $this->assertEquals($objectiveParent_mock, $this->testObj->getObjectiveParent());
    }
}
