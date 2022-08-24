<?php

declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestResultsToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestResultsToolbarGUITest extends ilTestBaseTestCase
{
    private ilTestResultsToolbarGUI $toolbarGUI;

    protected function setUp(): void
    {
        parent::setUp();

        $ctrl_mock = $this->createMock(ilCtrl::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $mainTpl_mock = $this->createMock(ilGlobalPageTemplate::class);

        $this->setGlobalVariable("lng", $lng_mock);

        $this->toolbarGUI = new ilTestResultsToolbarGUI(
            $ctrl_mock,
            $mainTpl_mock,
            $lng_mock,
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestResultsToolbarGUI::class, $this->toolbarGUI);
    }
}
