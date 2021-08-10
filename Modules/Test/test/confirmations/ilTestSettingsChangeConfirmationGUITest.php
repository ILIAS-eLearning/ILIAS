<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilTestSettingsChangeConfirmationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSettingsChangeConfirmationGUITest extends TestCase
{
    private ilTestSettingsChangeConfirmationGUI $testSettingsChangeConfirmationGUI;
    /**
     * @var ilObjTest|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    private $testObj_mock;
    /**
     * @var ilLanguage|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    private $lng_mock;

    protected function setUp() : void
    {
        $this->lng_mock = $this->createMock(ilLanguage::class);
        $this->testObj_mock = $this->createMock(ilObjTest::class);
        $this->testSettingsChangeConfirmationGUI = new ilTestSettingsChangeConfirmationGUI($this->lng_mock,  $this->testObj_mock);
    }

    protected function testSetAndGetOldQuestionSetType() : void
    {
        $expect = "testType";

        $this->testSettingsChangeConfirmationGUI->setOldQuestionSetType($expect);

        $this->assertEquals($expect, $this->testSettingsChangeConfirmationGUI->getOldQuestionSetType());
    }

    protected function testSetAndGetNewQuestionSetType() : void
    {
        $expect = "testType";

        $this->testSettingsChangeConfirmationGUI->setNewQuestionSetType($expect);

        $this->assertEquals($expect, $this->testSettingsChangeConfirmationGUI->getNewQuestionSetType());
    }

    protected function testSetAndIsQuestionLossInfoEnabled() : void
    {
        $expect = true;

        $this->testSettingsChangeConfirmationGUI->setQuestionLossInfoEnabled($expect);

        $this->assertEquals($expect, $this->testSettingsChangeConfirmationGUI->isQuestionLossInfoEnabled());
    }
}