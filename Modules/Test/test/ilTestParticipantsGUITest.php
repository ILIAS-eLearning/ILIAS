<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestParticipantsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantsGUITest extends ilTestBaseTestCase
{
    private ilTestParticipantsGUI $testObj;
    /**
     * @var \ILIAS\DI\Container|mixed
     */
    private $backup_dic;

    protected function setUp(): void
    {
        parent::setUp();
        global $DIC;

        $this->backup_dic = $DIC;
        $DIC = new ILIAS\DI\Container([
            'tpl' => $this->getMockBuilder(ilGlobalTemplateInterface::class)
                          ->getMock()
        ]);
        $this->testObj = new ilTestParticipantsGUI(
            $this->createMock(ilObjTest::class),
            $this->createMock(ilTestQuestionSetConfig::class)
        );
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->backup_dic;
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestParticipantsGUI::class, $this->testObj);
    }

    public function testTestObj(): void
    {
        $mock = $this->createMock(ilObjTest::class);
        $this->testObj->setTestObj($mock);
        $this->assertEquals($mock, $this->testObj->getTestObj());
    }

    public function testQuestionSetConfig(): void
    {
        $mock = $this->createMock(ilTestQuestionSetConfig::class);
        $this->testObj->setQuestionSetConfig($mock);
        $this->assertEquals($mock, $this->testObj->getQuestionSetConfig());
    }

    public function testObjectiveParent(): void
    {
        $mock = $this->createMock(ilTestObjectiveOrientedContainer::class);
        $this->testObj->setObjectiveParent($mock);
        $this->assertEquals($mock, $this->testObj->getObjectiveParent());
    }

    public function testTestAccess(): void
    {
        $mock = $this->createMock(ilTestAccess::class);
        $this->testObj->setTestAccess($mock);
        $this->assertEquals($mock, $this->testObj->getTestAccess());
    }
}
