<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillEvaluationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillEvaluationGUITest extends ilTestBaseTestCase
{
    private ilTestSkillEvaluationGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillEvaluationGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilTabsGUI::class),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSkillEvaluationGUI::class, $this->testObj);
    }

    public function testQuestionList(): void
    {
        $mock = $this->createMock(ilAssQuestionList::class);
        $this->testObj->setQuestionList($mock);
        $this->assertEquals($mock, $this->testObj->getQuestionList());
    }

    public function testObjectiveOrientedContainer(): void
    {
        $mock = $this->createMock(ilTestObjectiveOrientedContainer::class);
        $this->testObj->setObjectiveOrientedContainer($mock);
        $this->assertEquals($mock, $this->testObj->getObjectiveOrientedContainer());
    }

    public function testTestSession(): void
    {
        $mock = $this->createMock(ilTestSession::class);
        $this->testObj->setTestSession($mock);
        $this->assertEquals($mock, $this->testObj->getTestSession());
    }

    public function testNoSkillProfileOptionEnabled(): void
    {
        $this->testObj->setNoSkillProfileOptionEnabled(false);
        $this->assertFalse($this->testObj->isNoSkillProfileOptionEnabled());

        $this->testObj->setNoSkillProfileOptionEnabled(true);
        $this->assertTrue($this->testObj->isNoSkillProfileOptionEnabled());
    }

    public function testAvailableSkillProfiles(): void
    {
        $expected = ["test", "test2"];
        $this->testObj->setAvailableSkillProfiles($expected);
        $this->assertEquals($expected, $this->testObj->getAvailableSkillProfiles());
    }

    public function testAvailableSkills(): void
    {
        $expected = ["test", "test2"];
        $this->testObj->setAvailableSkills($expected);
        $this->assertEquals($expected, $this->testObj->getAvailableSkills());
    }
}
