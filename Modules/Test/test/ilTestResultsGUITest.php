<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestResultsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultsGUITest extends ilTestBaseTestCase
{
    private ilTestResultsGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->addGlobal_lng();

        $this->testObj = new ilTestResultsGUI(
            $this->createMock(ilObjTest::class),
            $this->createMock(ilTestQuestionSetConfig::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestResultsGUI::class, $this->testObj);
    }

    public function testObjectiveParent() : void
    {
        $mock = $this->createMock(ilTestObjectiveOrientedContainer::class);
        $this->testObj->setObjectiveParent($mock);
        $this->assertEquals($mock, $this->testObj->getObjectiveParent());
    }

    public function testTestObj() : void
    {
        $mock = $this->createMock(ilObjTest::class);
        $this->testObj->setTestObj($mock);
        $this->assertEquals($mock, $this->testObj->getTestObj());
    }

    public function testQuestionSetConfig() : void
    {
        $mock = $this->createMock(ilTestQuestionSetConfig::class);
        $this->testObj->setQuestionSetConfig($mock);
        $this->assertEquals($mock, $this->testObj->getQuestionSetConfig());
    }

    public function testTestAccess() : void
    {
        $mock = $this->createMock(ilTestAccess::class);
        $this->testObj->setTestAccess($mock);
        $this->assertEquals($mock, $this->testObj->getTestAccess());
    }

    public function testTestSession() : void
    {
        $mock = $this->createMock(ilTestSession::class);
        $this->testObj->setTestSession($mock);
        $this->assertEquals($mock, $this->testObj->getTestSession());
    }

    public function testTestTabs() : void
    {
        $mock = $this->createMock(ilTestTabsManager::class);
        $this->testObj->setTestTabs($mock);
        $this->assertEquals($mock, $this->testObj->getTestTabs());
    }
}
