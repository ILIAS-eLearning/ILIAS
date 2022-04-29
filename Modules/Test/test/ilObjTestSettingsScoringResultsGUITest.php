<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjTestSettingsScoringResultsGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestSettingsScoringResultsGUITest extends ilTestBaseTestCase
{
    private ilObjTestSettingsScoringResultsGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();


        $objTestGui_mock = $this->getMockBuilder(ilObjTestGUI::class)->disableOriginalConstructor()->onlyMethods(array('getObject'))->getMock();
        $objTestGui_mock->expects($this->any())->method('getObject')->willReturn($this->createMock(ilObjTest::class));

        $this->testObj = new ilObjTestSettingsScoringResultsGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilAccessHandler::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilTree::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilPluginAdmin::class),
            $objTestGui_mock
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilObjTestSettingsScoringResultsGUI::class, $this->testObj);
    }
}
