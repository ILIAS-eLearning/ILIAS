<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestParticipantsTimeExtensionGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantsTimeExtensionGUITest extends ilTestBaseTestCase
{
    private ilTestParticipantsTimeExtensionGUI $testObj;
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
        $this->testObj = new ilTestParticipantsTimeExtensionGUI($this->createMock(ilObjTest::class));
    }

    protected function tearDown(): void
    {
        global $DIC;
        $DIC = $this->backup_dic;
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestParticipantsTimeExtensionGUI::class, $this->testObj);
    }

    public function testTestObj(): void
    {
        $mock = $this->createMock(ilObjTest::class);
        $this->testObj->setTestObj($mock);
        $this->assertEquals($mock, $this->testObj->getTestObj());
    }
}
