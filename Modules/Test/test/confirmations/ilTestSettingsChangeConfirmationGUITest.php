<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTestSettingsChangeConfirmationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSettingsChangeConfirmationGUITest extends ilTestBaseTestCase
{
    private ilTestSettingsChangeConfirmationGUI $testSettingsChangeConfirmationGUI;
    /**
     * @var ilObjTest|MockObject
     */
    private $testObj_mock;
    /**
     * @var ilLanguage|MockObject
     */
    private $lng_mock;
    /**
     * @var \ILIAS\DI\Container|mixed
     */
    private $backup_dic;

    protected function setUp() : void
    {
        parent::setUp();
        global $DIC;
        
        $this->backup_dic = $DIC;
        $DIC = new ILIAS\DI\Container([
            'tpl' => $this->getMockBuilder(ilGlobalTemplateInterface::class)
                          ->getMock()
        ]);
        $this->lng_mock = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $this->testObj_mock = $this->getMockBuilder(ilObjTest::class)->disableOriginalConstructor()->getMock();
    
        $this->setGlobalVariable('lng', $this->lng_mock);
    
        $this->testSettingsChangeConfirmationGUI = new ilTestSettingsChangeConfirmationGUI(
            $this->testObj_mock
        );
    }
    
    protected function tearDown() : void
    {
        global $DIC;
        $DIC = $this->backup_dic;
    }

    public function testSetAndGetOldQuestionSetType() : void
    {
        $expect = "testType";

        $this->testSettingsChangeConfirmationGUI->setOldQuestionSetType($expect);

        $this->assertEquals($expect, $this->testSettingsChangeConfirmationGUI->getOldQuestionSetType());
    }

    public function testSetAndGetNewQuestionSetType() : void
    {
        $expect = "testType";

        $this->testSettingsChangeConfirmationGUI->setNewQuestionSetType($expect);

        $this->assertEquals($expect, $this->testSettingsChangeConfirmationGUI->getNewQuestionSetType());
    }

    public function testSetAndIsQuestionLossInfoEnabled() : void
    {
        $expect = true;

        $this->testSettingsChangeConfirmationGUI->setQuestionLossInfoEnabled($expect);

        $this->assertEquals($expect, $this->testSettingsChangeConfirmationGUI->isQuestionLossInfoEnabled());
    }
}
