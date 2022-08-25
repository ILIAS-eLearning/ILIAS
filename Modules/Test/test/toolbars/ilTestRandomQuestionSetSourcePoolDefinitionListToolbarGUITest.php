<?php

declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUITest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI $toolbarGUI;

    protected function setUp(): void
    {
        parent::setUp();

        $ctrl_mock = $this->createMock(ilCtrl::class);
        $lng_mock = $this->createMock(ilLanguage::class);

        $questionSetConfigGui_mock = $this->createMock(ilTestRandomQuestionSetConfigGUI::class);
        $questionSetConfig_mock = $this->createMock(ilTestRandomQuestionSetConfig::class);

        $this->setGlobalVariable("lng", $lng_mock);

        $this->toolbarGUI = new ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI(
            $ctrl_mock,
            $lng_mock,
            $questionSetConfigGui_mock,
            $questionSetConfig_mock
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI::class, $this->toolbarGUI);
    }
}
